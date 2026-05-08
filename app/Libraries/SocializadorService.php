<?php

namespace App\Libraries;

use App\Models\SocializacionModel;
use Dompdf\Dompdf;

/**
 * Servicio reutilizable para socializaciones de comites.
 *
 * Encapsula:
 *  - Parseo robusto de CSV (UTF-8 con BOM, separadores: , ; | \t)
 *  - Generacion de PDF desde HTML (Dompdf)
 *  - Envio de emails con adjunto via SendGrid (con o sin reintento)
 *  - Generacion de PDF de evidencia (lista de destinatarios + asunto + cuerpo)
 *  - Persistencia en tbl_socializaciones + tbl_documentos_sst
 *
 * Uso:
 *   $svc = new SocializadorService();
 *   $emails = $svc->parsearCsvEmails($pathArchivo);          // [['email','nombre'], ...]
 *   $pdfPath = $svc->generarPdfDesdeHtml($html, $nombre);   // ruta absoluta del PDF guardado
 *   $resultado = $svc->enviarPdfPorEmail($pdfPath, $emails, $asunto, $cuerpoHtml, $nombreEmpresa);
 *   $evidenciaPath = $svc->generarPdfEvidencia($pdfPath, $asunto, $cuerpoHtml, $resultado, $nombreEmpresa);
 *   $idSoc = $svc->guardarSocializacion($metadata);
 */
class SocializadorService
{
    /** @var string Carpeta absoluta donde se guardan los PDFs generados */
    private string $uploadDir;

    public function __construct()
    {
        $this->uploadDir = FCPATH . 'uploads/socializaciones';
        if (!is_dir($this->uploadDir)) {
            @mkdir($this->uploadDir, 0755, true);
        }
    }

    // =========================================================================
    // 1) CSV parser
    // =========================================================================

    /**
     * Parsea un CSV con columnas nombre,email (orden flexible) tolerando varios
     * separadores y BOM UTF-8. Devuelve array de [['nombre','email'], ...].
     *
     * Reglas:
     *  - Detecta automaticamente separador (`,` `;` `|` `\t`).
     *  - La primera linea es header (debe contener 'email' y 'nombre' en algun orden).
     *  - Si no hay header valido, asume orden: columna 0 = nombre, columna 1 = email.
     *  - Filas con email invalido se omiten silenciosamente (se reportan en errores).
     *  - Deduplica por email (lower-trim).
     *
     * @return array{rows: array<array{nombre:string,email:string}>, errores: array<string>}
     */
    public function parsearCsvEmails(string $rutaArchivo): array
    {
        $errores = [];
        $rows = [];

        if (!is_file($rutaArchivo)) {
            return ['rows' => [], 'errores' => ['Archivo no encontrado: ' . $rutaArchivo]];
        }

        $contenido = file_get_contents($rutaArchivo);
        if ($contenido === false || $contenido === '') {
            return ['rows' => [], 'errores' => ['Archivo vacio o ilegible']];
        }

        // Quitar BOM UTF-8
        $contenido = preg_replace('/^\xEF\xBB\xBF/', '', $contenido);

        // Normalizar saltos de linea
        $contenido = str_replace(["\r\n", "\r"], "\n", $contenido);
        $lineas = explode("\n", $contenido);

        // Detectar separador en la primera linea no vacia
        $separador = $this->detectarSeparador($lineas);

        $headerProcesado = false;
        $colNombre = -1;
        $colEmail  = -1;
        $vistos    = []; // dedupe por email

        foreach ($lineas as $i => $linea) {
            $linea = trim($linea);
            if ($linea === '') continue;

            $cols = str_getcsv($linea, $separador);
            $cols = array_map('trim', $cols);

            // Procesar header
            if (!$headerProcesado) {
                $headerProcesado = true;
                $colsLow = array_map('mb_strtolower', $cols);
                $colNombre = array_search('nombre', $colsLow, true);
                $colEmail  = array_search('email', $colsLow, true);
                if ($colEmail === false) {
                    $colEmail = array_search('correo', $colsLow, true);
                }

                if ($colEmail !== false && $colNombre !== false) {
                    continue; // header valido, saltar a datos
                }

                // No hay header valido: asumir orden por defecto y procesar esta linea como dato
                $colNombre = 0;
                $colEmail  = 1;
            }

            $nombre = $cols[$colNombre] ?? '';
            $email  = $cols[$colEmail] ?? '';
            $email  = mb_strtolower(trim($email));

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = "Linea " . ($i + 1) . ": email invalido o vacio ('{$email}')";
                continue;
            }

            if (isset($vistos[$email])) {
                continue; // duplicado, omitir silenciosamente
            }
            $vistos[$email] = true;

            $rows[] = ['nombre' => $nombre, 'email' => $email];
        }

        return ['rows' => $rows, 'errores' => $errores];
    }

    private function detectarSeparador(array $lineas): string
    {
        foreach ($lineas as $linea) {
            $linea = trim($linea);
            if ($linea === '') continue;
            // Primer separador con mas de 1 ocurrencia gana, en orden de probabilidad
            foreach ([',', ';', '|', "\t"] as $sep) {
                if (substr_count($linea, $sep) >= 1) return $sep;
            }
            return ','; // fallback
        }
        return ',';
    }

    // =========================================================================
    // 2) PDF desde HTML (Dompdf)
    // =========================================================================

    /**
     * Renderiza HTML a PDF y lo guarda en uploads/socializaciones.
     * Devuelve la ruta RELATIVA al FCPATH (para guardar en BD).
     */
    public function generarPdfDesdeHtml(string $html, string $nombreBase, string $orientacion = 'portrait'): string
    {
        $dompdf = new Dompdf([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'Helvetica',
        ]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', $orientacion);
        $dompdf->render();
        $pdfContent = $dompdf->output();

        $nombreSeguro = preg_replace('/[^a-z0-9_\-]/i', '_', $nombreBase);
        $filename = $nombreSeguro . '_' . date('Ymd_His') . '_' . substr(bin2hex(random_bytes(3)), 0, 6) . '.pdf';
        $rutaAbs = $this->uploadDir . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($rutaAbs, $pdfContent);

        return 'uploads/socializaciones/' . $filename;
    }

    // =========================================================================
    // 3) Envio de email con adjunto (SendGrid)
    // =========================================================================

    /**
     * Envia el PDF a una lista de destinatarios.
     * Devuelve un array detallado con resultado por destinatario.
     *
     * @param string $pdfRutaRelativa  Relativa a FCPATH (como devuelve generarPdfDesdeHtml)
     * @param array  $destinatarios    [['email','nombre'], ...]
     * @param string $asunto
     * @param string $cuerpoHtml       HTML del cuerpo del email
     * @param string $nombreEmpresa    Para el From friendly name
     * @param array|null $cc           Opcional: ['email','nombre'] del consultor a copiar en cada envio
     * @return array{ok:int, fallidos:int, detalle:array<array{email,nombre,status,error}>, cc:?array}
     */
    public function enviarPdfPorEmail(
        string $pdfRutaRelativa,
        array $destinatarios,
        string $asunto,
        string $cuerpoHtml,
        string $nombreEmpresa,
        ?array $cc = null
    ): array {
        $apiKey = getenv('SENDGRID_API_KEY');
        if (empty($apiKey) || $apiKey === 'SG.xxxxxx') {
            // Sin SendGrid configurado: marcamos todos como fallidos pero NO bloqueamos.
            $detalle = array_map(fn($d) => [
                'email' => $d['email'], 'nombre' => $d['nombre'] ?? '',
                'status' => 'fallido', 'error' => 'SENDGRID_API_KEY no configurada',
            ], $destinatarios);
            return ['ok' => 0, 'fallidos' => count($destinatarios), 'detalle' => $detalle, 'cc' => $cc];
        }

        // Validar email de CC (si viene mal lo dejamos en null sin romper)
        if ($cc !== null) {
            $ccEmail = trim((string)($cc['email'] ?? ''));
            if ($ccEmail === '' || !filter_var($ccEmail, FILTER_VALIDATE_EMAIL)) {
                $cc = null;
            }
        }

        $rutaAbs = FCPATH . $pdfRutaRelativa;
        if (!is_file($rutaAbs)) {
            $detalle = array_map(fn($d) => [
                'email' => $d['email'], 'nombre' => $d['nombre'] ?? '',
                'status' => 'fallido', 'error' => 'PDF adjunto no encontrado',
            ], $destinatarios);
            return ['ok' => 0, 'fallidos' => count($destinatarios), 'detalle' => $detalle];
        }
        $pdfBase64 = base64_encode(file_get_contents($rutaAbs));
        $pdfNombre = basename($rutaAbs);

        $sendgrid = new \SendGrid($apiKey);
        $detalle = [];
        $ok = 0;
        $fallidos = 0;

        foreach ($destinatarios as $d) {
            $email = $d['email'];
            $nombre = $d['nombre'] ?? '';

            try {
                $mail = new \SendGrid\Mail\Mail();
                $mail->setFrom('notificacion.cycloidtalent@cycloidtalent.com', $nombreEmpresa . ' - SST');
                $mail->setSubject($asunto);
                $mail->addTo($email, $nombre ?: $email);
                if ($cc !== null && strcasecmp($cc['email'], $email) !== 0) {
                    // CC al consultor en cada envio (omitir si por casualidad coincide con el destinatario)
                    $mail->addCc($cc['email'], $cc['nombre'] ?? $cc['email']);
                }
                $mail->addContent('text/html', $cuerpoHtml);

                $attachment = new \SendGrid\Mail\Attachment();
                $attachment->setContent($pdfBase64);
                $attachment->setType('application/pdf');
                $attachment->setFilename($pdfNombre);
                $attachment->setDisposition('attachment');
                $mail->addAttachment($attachment);

                $resp = $sendgrid->send($mail);
                $code = $resp->statusCode();

                if ($code >= 200 && $code < 300) {
                    $detalle[] = ['email' => $email, 'nombre' => $nombre, 'status' => 'ok', 'error' => null];
                    $ok++;
                } else {
                    $detalle[] = ['email' => $email, 'nombre' => $nombre, 'status' => 'fallido', 'error' => 'HTTP ' . $code];
                    $fallidos++;
                }
            } catch (\Throwable $e) {
                $detalle[] = ['email' => $email, 'nombre' => $nombre, 'status' => 'fallido', 'error' => $e->getMessage()];
                $fallidos++;
            }

            usleep(100000); // 0.1s entre envios para no saturar SendGrid
        }

        return ['ok' => $ok, 'fallidos' => $fallidos, 'detalle' => $detalle, 'cc' => $cc];
    }

    // =========================================================================
    // 4) PDF de evidencia
    // =========================================================================

    /**
     * Genera un PDF que evidencia el envio: nombre del adjunto, asunto, cuerpo,
     * y tabla con el listado de emails destinatarios y su status.
     * Devuelve ruta relativa.
     */
    public function generarPdfEvidencia(
        string $pdfAdjuntoRutaRelativa,
        string $asunto,
        string $cuerpoHtml,
        array $resultadoEnvio,
        string $nombreEmpresa,
        string $tipoSocializacionLabel
    ): string {
        $nombreAdjunto = basename($pdfAdjuntoRutaRelativa);
        $detalle = $resultadoEnvio['detalle'] ?? [];
        $ok = (int) ($resultadoEnvio['ok'] ?? 0);
        $fallidos = (int) ($resultadoEnvio['fallidos'] ?? 0);
        $total = $ok + $fallidos;

        $filasHtml = '';
        foreach ($detalle as $d) {
            $statusBadge = $d['status'] === 'ok'
                ? '<span style="color:#0a6e2e;font-weight:bold;">OK</span>'
                : '<span style="color:#a40000;font-weight:bold;">FALLIDO</span>';
            $errorTxt = !empty($d['error']) ? esc($d['error']) : '';
            $filasHtml .= '<tr>'
                . '<td>' . esc($d['nombre'] ?? '') . '</td>'
                . '<td>' . esc($d['email']) . '</td>'
                . '<td>' . $statusBadge . '</td>'
                . '<td style="font-size:9px;">' . $errorTxt . '</td>'
                . '</tr>';
        }

        $fecha = date('d/m/Y H:i');
        $ccInfo = $resultadoEnvio['cc'] ?? null;
        $ccLinea = '';
        if ($ccInfo && !empty($ccInfo['email'])) {
            $ccLinea = '<div><strong>Con copia (CC) en cada envio:</strong> '
                     . esc(($ccInfo['nombre'] ?? '') . ' &lt;' . $ccInfo['email'] . '&gt;') . '</div>';
        }
        $html = "<!DOCTYPE html><html><head><meta charset='utf-8'>
        <style>
            body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1c2437; }
            h1 { font-size: 18px; color: #1c2437; margin: 0 0 8px 0; }
            h2 { font-size: 13px; color: #bd9751; margin: 16px 0 6px 0; border-bottom: 1px solid #ddd; padding-bottom: 3px; }
            .meta { background: #f5f5f5; padding: 12px; border-radius: 6px; margin-bottom: 14px; }
            .meta div { margin-bottom: 4px; }
            .stats { display: table; width: 100%; margin: 12px 0; }
            .stat-cell { display: table-cell; padding: 8px; text-align: center; background:#f8f9fa; border:1px solid #ddd; }
            .stat-cell .num { font-size: 20px; font-weight: bold; }
            table.dest { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 10px; }
            table.dest th, table.dest td { border: 1px solid #ccc; padding: 5px 7px; text-align: left; vertical-align: top; }
            table.dest th { background: #1c2437; color: #fff; }
            .cuerpo-preview { border: 1px solid #ddd; padding: 12px; background: #fafafa; border-radius: 4px; }
        </style></head><body>
        <h1>Evidencia de Socializacion</h1>
        <p style='color:#6c757d;font-size:11px;'>Generado el {$fecha} - {$nombreEmpresa}</p>

        <div class='meta'>
            <div><strong>Tipo:</strong> {$tipoSocializacionLabel}</div>
            <div><strong>Documento adjunto:</strong> {$nombreAdjunto}</div>
            <div><strong>Asunto del correo:</strong> " . esc($asunto) . "</div>
            {$ccLinea}
        </div>

        <div class='stats'>
            <div class='stat-cell'><div class='num'>{$total}</div><div>Total destinatarios</div></div>
            <div class='stat-cell' style='background:#e8f5e9;'><div class='num'>{$ok}</div><div>Enviados OK</div></div>
            <div class='stat-cell' style='background:#fce4ec;'><div class='num'>{$fallidos}</div><div>Fallidos</div></div>
        </div>

        <h2>Cuerpo del correo enviado</h2>
        <div class='cuerpo-preview'>{$cuerpoHtml}</div>

        <h2>Listado de destinatarios</h2>
        <table class='dest'>
            <thead><tr><th>Nombre</th><th>Email</th><th>Estado</th><th>Error</th></tr></thead>
            <tbody>{$filasHtml}</tbody>
        </table>

        <p style='margin-top:20px;font-size:9px;color:#6c757d;'>
            Documento generado automaticamente por EnterpriseSST como evidencia del proceso de socializacion.
        </p>
        </body></html>";

        return $this->generarPdfDesdeHtml($html, 'evidencia_' . $tipoSocializacionLabel);
    }

    // =========================================================================
    // 5) Persistir en tbl_documentos_sst + tbl_socializaciones
    // =========================================================================

    /**
     * Inserta un registro en tbl_documentos_sst con el PDF asociado.
     * Devuelve el id_documento creado.
     */
    public function guardarEnReportlist(
        int $idCliente,
        string $tipoDocumento,
        string $titulo,
        string $codigo,
        string $rutaPdfRelativa,
        ?string $contenidoSnapshotJson = null,
        ?string $observaciones = null
    ): int {
        $db = \Config\Database::connect();
        $db->table('tbl_documentos_sst')->insert([
            'id_cliente'      => $idCliente,
            'tipo_documento'  => $tipoDocumento,
            'titulo'          => $titulo,
            'codigo'          => $codigo,
            'anio'            => (int) date('Y'),
            'contenido'       => $contenidoSnapshotJson,
            'archivo_pdf'     => $rutaPdfRelativa,
            'version'         => 1,
            'estado'          => 'generado',
            'observaciones'   => $observaciones,
            'created_at'      => date('Y-m-d H:i:s'),
            'created_by'      => session()->get('id_usuario') ?? session()->get('id_consultor') ?? null,
        ]);
        return (int) $db->insertID();
    }

    /**
     * Persiste el registro completo de la socializacion.
     */
    public function guardarSocializacion(array $datos): int
    {
        $model = new SocializacionModel();
        return (int) $model->insert($datos, true);
    }

    /**
     * Devuelve la cabecera del CSV plantilla (BOM UTF-8 + columnas + 2 filas de ejemplo).
     */
    public function getPlantillaCsvContenido(): string
    {
        $bom = "\xEF\xBB\xBF";
        $contenido = $bom . "nombre,email\n"
                  . "Juan Perez,juan.perez@empresa.com\n"
                  . "Maria Lopez,maria.lopez@empresa.com\n";
        return $contenido;
    }
}
