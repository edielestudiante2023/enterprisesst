<?php

namespace App\Controllers;

use App\Libraries\DocumentosSSTTypes\DocumentoSSTFactory;
use App\Libraries\SocializadorService;
use App\Models\ClientModel;
use CodeIgniter\Controller;

/**
 * Acuerdo de Confidencialidad para Comite de Convivencia Laboral (COCOLAB).
 *
 * Documento legal individual firmado por cada miembro activo del COCOLAB.
 * Reutiliza el sistema de firma electronica via token de tbl_doc_firma_solicitudes
 * (mismo que el acta de constitucion).
 *
 * Solo aplica a procesos de tipo 'COCOLAB' en estado 'completado'.
 */
class AcuerdoConfidencialidadController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Vista del PDF (preview en navegador, inline).
     */
    public function generar(int $idProceso)
    {
        $data = $this->obtenerDatos($idProceso);
        if (is_string($data)) return redirect()->back()->with('error', $data);

        $html = view('comites_elecciones/acuerdo_confidencialidad_pdf', $data);

        $dompdf = $this->dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = "Acuerdo_Confidencialidad_COCOLAB_{$data['proceso']['anio']}.pdf";
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Descargar PDF.
     */
    public function descargar(int $idProceso)
    {
        $data = $this->obtenerDatos($idProceso);
        if (is_string($data)) return redirect()->back()->with('error', $data);

        $html = view('comites_elecciones/acuerdo_confidencialidad_pdf', $data);
        $dompdf = $this->dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = "Acuerdo_Confidencialidad_COCOLAB_{$data['proceso']['anio']}.pdf";
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Vista para seleccionar a quienes enviar el enlace de firma.
     */
    public function solicitarFirmas(int $idProceso)
    {
        $data = $this->obtenerDatos($idProceso);
        if (is_string($data)) return redirect()->back()->with('error', $data);

        // Obtener (o crear) el documento en tbl_documentos_sst
        $documento = $this->obtenerOCrearDocumento($data);

        // Cargar solicitudes existentes para mostrar estado
        $solicitudes = $this->db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $documento['id_documento'])
            ->get()->getResultArray();
        $solicitudesPorTipo = [];
        foreach ($solicitudes as $s) $solicitudesPorTipo[$s['firmante_tipo']] = $s;

        return view('comites_elecciones/acuerdo_confidencialidad_solicitar_firmas', array_merge($data, [
            'documento'          => $documento,
            'solicitudesPorTipo' => $solicitudesPorTipo,
        ]));
    }

    /**
     * Procesa el POST: crea solicitudes en tbl_doc_firma_solicitudes y manda emails.
     */
    public function crearSolicitudes()
    {
        $idProceso = (int) $this->request->getPost('id_proceso');
        $miembrosSeleccionados = $this->request->getPost('miembros') ?? [];
        if ($idProceso <= 0 || empty($miembrosSeleccionados)) {
            return redirect()->back()->with('error', 'Selecciona al menos un miembro para enviar el acuerdo.');
        }

        $data = $this->obtenerDatos($idProceso);
        if (is_string($data)) return redirect()->back()->with('error', $data);

        $documento = $this->obtenerOCrearDocumento($data);
        $idDocumento = (int) $documento['id_documento'];

        // Mapa idMiembro -> miembro
        $mapa = [];
        foreach ($data['miembros'] as $m) {
            if (!empty($m['id_miembro'])) $mapa[(string) $m['id_miembro']] = $m;
        }

        $maxOrden = $this->db->table('tbl_doc_firma_solicitudes')
            ->selectMax('orden_firma')->where('id_documento', $idDocumento)
            ->get()->getRow();
        $orden = ($maxOrden && $maxOrden->orden_firma) ? (int) $maxOrden->orden_firma + 1 : 1;

        $creadas = 0; $omitidas = 0; $sinEmail = 0;
        $emailsOk = 0; $emailsFallidos = 0; $erroresEmail = [];

        foreach ($miembrosSeleccionados as $idMiembroRaw) {
            $idMiembro = (string) $idMiembroRaw;
            $m = $mapa[$idMiembro] ?? null;
            if (!$m) { $omitidas++; continue; }

            $email = trim((string)($m['email'] ?? ''));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $sinEmail++;
                continue;
            }

            $tipoFirma = 'miembro_' . $idMiembro;

            // Anti-duplicado: si ya existe pendiente, omitir; si firmada, omitir
            $existe = $this->db->table('tbl_doc_firma_solicitudes')
                ->where('id_documento', $idDocumento)
                ->where('firmante_tipo', $tipoFirma)
                ->whereIn('estado', ['pendiente', 'firmado'])
                ->get()->getRowArray();
            if ($existe) { $omitidas++; continue; }

            $token = bin2hex(random_bytes(32));
            $solicitud = [
                'id_documento'      => $idDocumento,
                'firmante_tipo'     => $tipoFirma,
                'firmante_email'    => $email,
                'firmante_nombre'   => $m['nombre'] ?? '',
                'firmante_cargo'    => $m['cargo'] ?? 'Miembro Comite de Convivencia',
                'firmante_documento'=> $m['cedula'] ?? '',
                'orden_firma'       => $orden++,
                'estado'            => 'pendiente',
                'token'             => $token,
                'fecha_expiracion'  => date('Y-m-d H:i:s', strtotime('+30 days')),
                'created_at'        => date('Y-m-d H:i:s'),
            ];
            $this->db->table('tbl_doc_firma_solicitudes')->insert($solicitud);
            $idSolicitud = (int) $this->db->insertID();
            $creadas++;

            // Capturar resultado real del envio para reportar al usuario
            $resEmail = $this->enviarEmailFirma($solicitud, $documento, $data);
            if ($resEmail['ok']) {
                $emailsOk++;
                $this->db->table('tbl_doc_firma_audit_log')->insert([
                    'id_solicitud' => $idSolicitud,
                    'evento'       => 'correo_enviado',
                    'fecha_hora'   => date('Y-m-d H:i:s'),
                    'ip_address'   => $this->request->getIPAddress(),
                    'detalles'     => json_encode(['via' => 'acuerdo_confidencialidad']),
                ]);
            } else {
                $emailsFallidos++;
                $erroresEmail[] = $email . ': ' . ($resEmail['error'] ?? 'desconocido');
                $this->db->table('tbl_doc_firma_audit_log')->insert([
                    'id_solicitud' => $idSolicitud,
                    'evento'       => 'correo_fallo',
                    'fecha_hora'   => date('Y-m-d H:i:s'),
                    'ip_address'   => $this->request->getIPAddress(),
                    'detalles'     => json_encode(['via' => 'acuerdo_confidencialidad', 'error' => $resEmail['error']]),
                ]);
            }
        }

        $this->db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->update(['estado' => 'pendiente_firma', 'updated_at' => date('Y-m-d H:i:s')]);

        $partes = ["{$creadas} solicitud(es) creadas"];
        if ($emailsOk > 0)        $partes[] = "{$emailsOk} email(s) enviado(s) OK";
        if ($emailsFallidos > 0)  $partes[] = "{$emailsFallidos} email(s) fallido(s)";
        if ($omitidas > 0)        $partes[] = "{$omitidas} omitida(s) (ya existian)";
        if ($sinEmail > 0)        $partes[] = "{$sinEmail} sin email valido";

        $tipoFlash = ($emailsFallidos > 0 || $sinEmail > 0) ? 'warning' : ($creadas > 0 ? 'success' : 'warning');
        $msg = implode(' . ', $partes);
        if (!empty($erroresEmail)) {
            $msg .= ' | Detalle de fallos: ' . implode('; ', array_slice($erroresEmail, 0, 3));
            if (count($erroresEmail) > 3) $msg .= ' (y ' . (count($erroresEmail) - 3) . ' mas)';
        }

        return redirect()->to("/comites-elecciones/proceso/{$idProceso}/acuerdo-confidencialidad/firmas")
            ->with($tipoFlash, $msg);
    }

    /**
     * Sube el PDF actual al reportList del cliente (tbl_reporte).
     */
    public function subirAReportList(int $idProceso)
    {
        $data = $this->obtenerDatos($idProceso);
        if (is_string($data)) return redirect()->back()->with('error', $data);

        $html = view('comites_elecciones/acuerdo_confidencialidad_pdf', $data);
        $svc = new SocializadorService();

        // Generar PDF y guardarlo en uploads/socializaciones/ (reutilizamos la carpeta)
        $rutaPdfRel = $svc->generarPdfDesdeHtml($html, "acuerdo_confidencialidad_cocolab_{$data['cliente']['id_cliente']}_{$data['proceso']['anio']}");

        $idReporte = $svc->subirAReportes(
            (int) $data['cliente']['id_cliente'],
            $rutaPdfRel,
            "Acuerdo de Confidencialidad COCOLAB {$data['proceso']['anio']} - " . ($data['cliente']['nombre_cliente'] ?? ''),
            2,  // id_report_type=2 (Comite de Convivencia)
            5,  // id_detailreport=5 (Acta de Conformacion)
            "acuerdo_confidencialidad_cocolab_proc{$idProceso}"
        );

        return redirect()->to("/comites-elecciones/proceso/{$idProceso}/acuerdo-confidencialidad/firmas")
            ->with($idReporte > 0 ? 'success' : 'error',
                   $idReporte > 0 ? "Subido al reportList (id_reporte={$idReporte})" : "Error subiendo al reportList");
    }

    // =========================================================================
    // Helpers privados
    // =========================================================================

    /**
     * Carga el proceso, valida que sea COCOLAB completado, arma el array de datos para la vista.
     * Devuelve string con mensaje de error o array con datos.
     */
    private function obtenerDatos(int $idProceso)
    {
        $proceso = $this->db->table('tbl_procesos_electorales')
            ->where('id_proceso', $idProceso)
            ->get()->getRowArray();
        if (!$proceso) return 'Proceso no encontrado';
        if (strtoupper($proceso['tipo_comite']) !== 'COCOLAB') {
            return 'El acuerdo de confidencialidad solo aplica al Comite de Convivencia (COCOLAB).';
        }

        $cliente = (new ClientModel())->find($proceso['id_cliente']);
        if (!$cliente) return 'Cliente no encontrado';

        // Cargar miembros (electos trabajadores + designados empleador)
        $rows = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->whereIn('estado', ['elegido', 'aprobado', 'designado'])
            ->orderBy('representacion', 'ASC')
            ->orderBy('tipo_plaza', 'ASC')
            ->orderBy('apellidos', 'ASC')
            ->get()->getResultArray();

        $miembros = [];
        foreach ($rows as $c) {
            // Resolver id_miembro: usar id_candidato si no hay tabla puente activa
            $idMiembro = $c['id_miembro'] ?? $c['id_candidato'] ?? null;
            // El campo real en tbl_candidatos_comite es 'documento_identidad'.
            // (Fallback a 'documento' o 'cedula' por compatibilidad con otras tablas)
            $documento = $c['documento_identidad'] ?? $c['documento'] ?? $c['cedula'] ?? '';
            $miembros[] = [
                'id_miembro'      => $idMiembro,
                'id_candidato'    => $c['id_candidato'] ?? null,
                'nombre'          => trim(($c['nombres'] ?? '') . ' ' . ($c['apellidos'] ?? '')),
                'cedula'          => $documento,
                'cargo'           => $c['cargo'] ?? 'Miembro Comite de Convivencia',
                'representacion'  => $c['representacion'] ?? '',
                'tipo_plaza'      => $c['tipo_plaza'] ?? '',
                'email'           => $c['email'] ?? '',
            ];
        }

        // Estado de firmas (si existe documento)
        $tipoDoc = 'acuerdo_confidencialidad_cocolab';
        $documento = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $cliente['id_cliente'])
            ->where('tipo_documento', $tipoDoc)
            ->where('anio', $proceso['anio'])
            ->get()->getRowArray();

        $firmasElectronicas = [];
        if ($documento) {
            $firmaModel = new \App\Models\DocFirmaModel();
            $firmasElectronicas = $firmaModel->obtenerFirmasElectronicasValidadas(
                $documento['id_documento'], [], $cliente
            );
        }

        return [
            'proceso'             => $proceso,
            'cliente'             => $cliente,
            'miembros'            => $miembros,
            'firmasElectronicas'  => $firmasElectronicas,
            'codigoFt'            => 'FT-SST-018',
            'documento'           => $documento,
        ];
    }

    /**
     * Obtiene o crea la fila en tbl_documentos_sst para este acuerdo.
     */
    private function obtenerOCrearDocumento(array $data): array
    {
        $tipoDoc = 'acuerdo_confidencialidad_cocolab';
        $idCliente = (int) $data['cliente']['id_cliente'];
        $anio = (int) $data['proceso']['anio'];

        $existente = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', $tipoDoc)
            ->where('anio', $anio)
            ->get()->getRowArray();
        if ($existente) return $existente;

        // Snapshot via Factory
        $tipoObj = DocumentoSSTFactory::crear($tipoDoc);
        $contenido = method_exists($tipoObj, 'buildContenidoSnapshot')
            ? $tipoObj->buildContenidoSnapshot([
                'id_cliente'     => $idCliente,
                'nombre_cliente' => $data['cliente']['nombre_cliente'] ?? '',
                'razon_social'   => $data['cliente']['razon_social'] ?? null,
                'id_proceso'     => (int) $data['proceso']['id_proceso'],
                'miembros'       => $data['miembros'],
                'firmados'       => 0,
                'pendientes'     => count($data['miembros']),
            ])
            : null;

        $this->db->table('tbl_documentos_sst')->insert([
            'id_cliente'     => $idCliente,
            'tipo_documento' => $tipoDoc,
            'titulo'         => 'Acuerdo de Confidencialidad COCOLAB ' . $anio,
            'codigo'         => 'FT-SST-018',
            'anio'           => $anio,
            'contenido'      => $contenido,
            'version'        => 1,
            'estado'         => 'generado',
            'observaciones'  => "Generado desde proceso electoral ID {$data['proceso']['id_proceso']}",
            'created_at'     => date('Y-m-d H:i:s'),
            'created_by'     => session()->get('id_usuario') ?? session()->get('id_consultor') ?? null,
        ]);
        $idDoc = (int) $this->db->insertID();

        return $this->db->table('tbl_documentos_sst')->where('id_documento', $idDoc)->get()->getRowArray();
    }

    /**
     * Envia el correo de firma. Devuelve ['ok' => bool, 'error' => string|null]
     * para que crearSolicitudes pueda reportar exito/fallo per-destinatario.
     */
    private function enviarEmailFirma(array $solicitud, array $documento, array $data): array
    {
        $apiKey = getenv('SENDGRID_API_KEY');
        if (empty($apiKey) || $apiKey === 'SG.xxxxxx') {
            log_message('error', '[AcuerdoConfidencialidad] SENDGRID_API_KEY no configurada o es placeholder');
            return ['ok' => false, 'error' => 'SENDGRID_API_KEY no configurada'];
        }

        $linkFirma = base_url('firmar/' . $solicitud['token']);
        $nombreEmpresa = $data['cliente']['nombre_cliente'] ?? '';

        $cuerpo = "<div style='font-family:Arial,sans-serif; max-width:600px; margin:auto;'>"
            . "<div style='background:#1c2437; color:#fff; padding:18px; text-align:center;'>"
            . "<h2 style='margin:0;'>Acuerdo de Confidencialidad</h2>"
            . "<p style='margin:6px 0 0; opacity:0.9;'>Comite de Convivencia Laboral</p>"
            . "</div>"
            . "<div style='background:#f8f9fa; padding:24px;'>"
            . "<p>Estimado(a) <strong>" . esc($solicitud['firmante_nombre']) . "</strong>,</p>"
            . "<p>Como miembro del Comite de Convivencia Laboral de <strong>" . esc($nombreEmpresa) . "</strong>, "
            . "se requiere tu firma electronica en el <strong>Acuerdo de Confidencialidad</strong>, "
            . "documento legal obligatorio segun la Ley 1010/2006 y Resolucion 652/2012 (modificada por Res. 3461/2025).</p>"
            . "<p>Este acuerdo certifica tu compromiso de mantener la confidencialidad de los casos y "
            . "documentos que se gestionen en el comite.</p>"
            . "<div style='text-align:center; margin:28px 0;'>"
            . "<a href='" . esc($linkFirma) . "' style='display:inline-block; padding:14px 28px; "
            . "background:#bd9751; color:#fff; text-decoration:none; border-radius:6px; font-weight:bold;'>"
            . "Leer y Firmar el Acuerdo</a>"
            . "</div>"
            . "<p style='color:#666; font-size:13px;'>Este enlace expira en 30 dias. Si no lo abres a tiempo, "
            . "el comite puede solicitar una renovacion.</p>"
            . "<p style='color:#666; font-size:11px; margin-top:18px;'>Si el boton no funciona, copia este enlace: "
            . esc($linkFirma) . "</p>"
            . "</div></div>";

        try {
            $sendgrid = new \SendGrid($apiKey);
            $mail = new \SendGrid\Mail\Mail();
            $mail->setFrom('notificacion.cycloidtalent@cycloidtalent.com', $nombreEmpresa . ' - Comite de Convivencia');
            $mail->setSubject("Acuerdo de Confidencialidad COCOLAB - {$nombreEmpresa}");
            $mail->addTo($solicitud['firmante_email'], $solicitud['firmante_nombre']);
            $mail->addContent('text/html', $cuerpo);
            $resp = $sendgrid->send($mail);
            $code = $resp->statusCode();
            $body = (string) $resp->body();

            if ($code >= 200 && $code < 300) {
                log_message('info', "[AcuerdoConfidencialidad] correo enviado OK a {$solicitud['firmante_email']} - status {$code}");
                return ['ok' => true, 'error' => null];
            }

            log_message('error', "[AcuerdoConfidencialidad] sendgrid rechazo para {$solicitud['firmante_email']} - status {$code} - body: " . substr($body, 0, 500));
            return ['ok' => false, 'error' => "SendGrid HTTP {$code}: " . substr($body, 0, 200)];
        } catch (\Throwable $e) {
            log_message('error', '[AcuerdoConfidencialidad] sendgrid exception para ' . $solicitud['firmante_email'] . ': ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function dompdf(): \Dompdf\Dompdf
    {
        $opts = new \Dompdf\Options();
        $opts->set('isRemoteEnabled', true);
        $opts->set('isHtml5ParserEnabled', true);
        $opts->set('defaultFont', 'Helvetica');
        return new \Dompdf\Dompdf($opts);
    }
}
