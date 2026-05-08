<?php

namespace App\Controllers;

use App\Libraries\DocumentosSSTTypes\DocumentoSSTFactory;
use App\Libraries\SocializadorService;
use App\Models\ClientModel;
use App\Models\SocializacionModel;
use CodeIgniter\Controller;

/**
 * Controlador para socializaciones de comites (miembros y cronograma).
 *
 * Flujo general (mismo para los 6 tipos):
 *   1. GET  /comites-elecciones/proceso/{id}/socializar/miembros (o cronograma)
 *      -> Vista con form: capturar datos faltantes (periodo, fechas) + upload CSV + asunto/cuerpo email.
 *   2. POST /comites-elecciones/proceso/{id}/socializar/miembros/enviar
 *      -> Genera PDF principal, envia a destinatarios, genera PDF evidencia,
 *         guarda 2 docs en tbl_documentos_sst y la metadata en tbl_socializaciones.
 *   3. GET  /comites-elecciones/socializaciones/plantilla-csv
 *      -> Descarga la plantilla CSV (UTF-8 con BOM).
 *   4. GET  /comites-elecciones/proceso/{id}/socializaciones
 *      -> Lista historial de socializaciones del proceso.
 */
class SocializacionesController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Descarga la plantilla CSV (UTF-8 con BOM, sample de 2 filas).
     */
    public function plantillaCsv()
    {
        $svc = new SocializadorService();
        $contenido = $svc->getPlantillaCsvContenido();

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="plantilla_socializacion_emails.csv"')
            ->setBody($contenido);
    }

    /**
     * Vista del formulario para socializar MIEMBROS de un comite.
     */
    public function formularioMiembros(int $idProceso)
    {
        $proceso = $this->db->table('tbl_procesos_electorales')->where('id_proceso', $idProceso)->get()->getRowArray();
        if (!$proceso) return redirect()->to('/comites-elecciones/dashboard')->with('error', 'Proceso no encontrado');

        $cliente = (new ClientModel())->find($proceso['id_cliente']);
        $tipoComite = strtoupper($proceso['tipo_comite']);
        $tipoDoc = 'socializacion_miembros_' . strtolower($tipoComite);

        // Validar que tengamos clase registrada para este tipo
        try {
            $tipoObj = DocumentoSSTFactory::crear($tipoDoc);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', "Tipo de comite no soportado: {$tipoComite}");
        }

        // Cargar candidatos electos con foto
        $miembros = $this->cargarMiembrosElectos($idProceso);

        // Mensaje preset y email preset
        $nombreEmpresa = $cliente['nombre_cliente'] ?? '';
        $mensajeComite = $tipoObj->getMensajeComite($nombreEmpresa);
        $emailPreset = $this->presetEmailMiembros($tipoComite, $nombreEmpresa, $proceso['anio'] ?? date('Y'));

        // Historial
        $historial = (new SocializacionModel())
            ->where('id_proceso', $idProceso)
            ->where('tipo_socializacion', 'miembros')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('comites_elecciones/socializacion/formulario_miembros', [
            'proceso'        => $proceso,
            'cliente'        => $cliente,
            'tipoComite'     => $tipoComite,
            'tipoDoc'        => $tipoDoc,
            'miembros'       => $miembros,
            'mensajeComite'  => $mensajeComite,
            'asuntoPreset'   => $emailPreset['asunto'],
            'cuerpoPreset'   => $emailPreset['cuerpo'],
            'historial'      => $historial,
            'codigoFt'       => $tipoObj->getCodigoBase(),
            // Periodo viene SIEMPRE de la conformacion del comite, nunca manual
            'periodoInicio'  => $proceso['fecha_inicio_periodo'] ?? null,
            'periodoFin'     => $proceso['fecha_fin_periodo'] ?? null,
        ]);
    }

    /**
     * Procesa el envio: genera PDF + envia + evidencia + persiste.
     */
    public function enviarMiembros(int $idProceso)
    {
        $proceso = $this->db->table('tbl_procesos_electorales')->where('id_proceso', $idProceso)->get()->getRowArray();
        if (!$proceso) return redirect()->to('/comites-elecciones/dashboard')->with('error', 'Proceso no encontrado');

        $cliente = (new ClientModel())->find($proceso['id_cliente']);
        $tipoComite = strtoupper($proceso['tipo_comite']);
        $tipoDoc = 'socializacion_miembros_' . strtolower($tipoComite);
        $tipoObj = DocumentoSSTFactory::crear($tipoDoc);

        $svc = new SocializadorService();

        // 1) Capturar datos del form. El periodo NO viene del form: se lee del proceso
        // electoral (tbl_procesos_electorales.fecha_inicio_periodo / fecha_fin_periodo).
        $periodoInicio = $proceso['fecha_inicio_periodo'] ?? null;
        $periodoFin    = $proceso['fecha_fin_periodo']    ?? null;
        $asunto        = trim((string) $this->request->getPost('asunto')) ?: $this->presetEmailMiembros($tipoComite, $cliente['nombre_cliente'], $proceso['anio'])['asunto'];
        $cuerpoHtml    = trim((string) $this->request->getPost('cuerpo')) ?: $this->presetEmailMiembros($tipoComite, $cliente['nombre_cliente'], $proceso['anio'])['cuerpo'];

        // 2) Parsear CSV
        $csvFile = $this->request->getFile('csv_emails');
        if (!$csvFile || !$csvFile->isValid()) {
            return redirect()->back()->withInput()->with('error', 'Debes adjuntar el archivo CSV con los emails.');
        }
        $tmpPath = $csvFile->getTempName();
        $parseo  = $svc->parsearCsvEmails($tmpPath);
        if (empty($parseo['rows'])) {
            return redirect()->back()->withInput()->with('error', 'El CSV no contiene emails validos. Errores: ' . implode('; ', $parseo['errores']));
        }
        $destinatarios = $parseo['rows'];

        // 3) Cargar miembros y generar PDF principal
        $miembrosFront = $this->cargarMiembrosElectos($idProceso);
        $logoBase64 = $this->imgToBase64(FCPATH . ($cliente['logo'] ?? ''));

        $htmlPdf = view('comites_elecciones/socializacion/miembros_pdf', [
            'cliente'         => $cliente,
            'logoBase64'      => $logoBase64,
            'tipoComiteCorto' => $tipoComite,
            'periodoInicio'   => $periodoInicio,
            'periodoFin'      => $periodoFin,
            'mensajeComite'   => $tipoObj->getMensajeComite($cliente['nombre_cliente']),
            'empleador'       => array_values(array_filter($miembrosFront, fn($m) => ($m['representacion'] ?? '') === 'empleador')),
            'trabajadores'    => array_values(array_filter($miembrosFront, fn($m) => ($m['representacion'] ?? '') === 'trabajador')),
            'codigoFt'        => $tipoObj->getCodigoBase(),
        ]);
        $rutaPdfRel = $svc->generarPdfDesdeHtml($htmlPdf, "miembros_{$tipoComite}_{$cliente['id_cliente']}");

        // 4) Enviar por email (con CC al consultor del cliente)
        $cc = $this->consultorDelCliente((int) $cliente['id_cliente']);
        $resultado = $svc->enviarPdfPorEmail($rutaPdfRel, $destinatarios, $asunto, $cuerpoHtml, $cliente['nombre_cliente'] ?? 'EnterpriseSST', $cc);

        // 5) PDF de evidencia
        $rutaEvidenciaRel = $svc->generarPdfEvidencia(
            $rutaPdfRel, $asunto, $cuerpoHtml, $resultado,
            $cliente['nombre_cliente'] ?? '', "Miembros {$tipoComite}"
        );

        // 6) Persistir snapshot + 2 docs en tbl_documentos_sst + 1 en tbl_socializaciones
        $snapshot = $tipoObj->buildContenidoSnapshot([
            'periodo_inicio' => $periodoInicio,
            'periodo_fin'    => $periodoFin,
            'mensaje_comite' => $tipoObj->getMensajeComite($cliente['nombre_cliente']),
            'miembros'       => array_map(fn($m) => [
                'nombre' => $m['nombre'], 'rol' => $m['rol_comite'] ?? '',
                'cargo' => $m['cargo'] ?? '', 'representacion' => $m['representacion'] ?? '',
            ], $miembrosFront),
            'id_cliente'     => $cliente['id_cliente'],
            'nombre_cliente' => $cliente['nombre_cliente'],
            'destinatarios'  => $resultado['detalle'] ?? [],
            'enviados_ok'    => $resultado['ok'],
            'fallidos'       => $resultado['fallidos'],
        ]);

        $idDocPrincipal = $svc->guardarEnReportlist(
            $cliente['id_cliente'], $tipoDoc,
            "Socializacion Miembros {$tipoComite} - " . ($cliente['nombre_cliente'] ?? ''),
            $tipoObj->getCodigoBase(),
            $rutaPdfRel, $snapshot,
            "Generado desde proceso electoral ID {$idProceso}"
        );
        $idDocEvidencia = $svc->guardarEnReportlist(
            $cliente['id_cliente'], $tipoDoc . '_evidencia',
            "Evidencia Socializacion Miembros {$tipoComite}",
            $tipoObj->getCodigoBase() . '-EVD',
            $rutaEvidenciaRel, null,
            "Evidencia de envio (id_doc_principal={$idDocPrincipal})"
        );

        $estado = $resultado['fallidos'] === 0 ? 'enviado'
                : ($resultado['ok'] === 0 ? 'fallido' : 'parcial');

        $idSoc = $svc->guardarSocializacion([
            'id_cliente'             => $cliente['id_cliente'],
            'id_comite'              => null,
            'id_proceso'             => $idProceso,
            'tipo_socializacion'     => 'miembros',
            'tipo_comite'            => $tipoComite,
            'id_documento_sst'       => $idDocPrincipal,
            'id_documento_evidencia' => $idDocEvidencia,
            'asunto_email'           => $asunto,
            'cuerpo_email'           => $cuerpoHtml,
            'destinatarios_json'     => json_encode($resultado['detalle'], JSON_UNESCAPED_UNICODE),
            'total_destinatarios'    => count($destinatarios),
            'enviados_ok'            => $resultado['ok'],
            'fallidos'               => $resultado['fallidos'],
            'estado'                 => $estado,
            'contenido_snapshot'     => $snapshot,
            'created_by'             => session()->get('id_usuario'),
        ]);

        $msg = "Socializacion enviada. {$resultado['ok']} OK / {$resultado['fallidos']} fallidos.";
        return redirect()->to("/comites-elecciones/proceso/{$idProceso}/socializar/miembros")
            ->with($resultado['fallidos'] > 0 ? 'warning' : 'success', $msg);
    }

    /**
     * Form para CRONOGRAMA.
     */
    public function formularioCronograma(int $idProceso)
    {
        $proceso = $this->db->table('tbl_procesos_electorales')->where('id_proceso', $idProceso)->get()->getRowArray();
        if (!$proceso) return redirect()->to('/comites-elecciones/dashboard')->with('error', 'Proceso no encontrado');

        $cliente = (new ClientModel())->find($proceso['id_cliente']);
        $tipoComite = strtoupper($proceso['tipo_comite']);
        $tipoDoc = 'socializacion_cronograma_' . strtolower($tipoComite);

        try {
            $tipoObj = DocumentoSSTFactory::crear($tipoDoc);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', "Tipo de comite no soportado: {$tipoComite}");
        }

        $anio = (int) ($proceso['anio'] ?? date('Y'));
        $emailPreset = $this->presetEmailCronograma($tipoComite, $cliente['nombre_cliente'], $anio);

        $historial = (new SocializacionModel())
            ->where('id_proceso', $idProceso)
            ->where('tipo_socializacion', 'cronograma')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('comites_elecciones/socializacion/formulario_cronograma', [
            'proceso'         => $proceso,
            'cliente'         => $cliente,
            'tipoComite'      => $tipoComite,
            'tipoDoc'         => $tipoDoc,
            'anio'            => $anio,
            'mensajeCronograma' => $tipoObj->getMensajeCronograma($cliente['nombre_cliente'], $anio),
            'asuntoPreset'    => $emailPreset['asunto'],
            'cuerpoPreset'    => $emailPreset['cuerpo'],
            'historial'       => $historial,
            'codigoFt'        => $tipoObj->getCodigoBase(),
        ]);
    }

    /**
     * Procesa el envio del cronograma.
     */
    public function enviarCronograma(int $idProceso)
    {
        $proceso = $this->db->table('tbl_procesos_electorales')->where('id_proceso', $idProceso)->get()->getRowArray();
        if (!$proceso) return redirect()->to('/comites-elecciones/dashboard')->with('error', 'Proceso no encontrado');

        $cliente = (new ClientModel())->find($proceso['id_cliente']);
        $tipoComite = strtoupper($proceso['tipo_comite']);
        $tipoDoc = 'socializacion_cronograma_' . strtolower($tipoComite);
        $tipoObj = DocumentoSSTFactory::crear($tipoDoc);

        $svc = new SocializadorService();
        $anio = (int) ($proceso['anio'] ?? date('Y'));

        // 1) Capturar form: array de reuniones (fecha[], hora[]) + asunto/cuerpo email
        $fechas = $this->request->getPost('fecha') ?? [];
        $horas  = $this->request->getPost('hora')  ?? [];
        $reuniones = [];
        foreach ($fechas as $i => $f) {
            $f = trim($f); $h = trim($horas[$i] ?? '');
            if ($f === '') continue;
            $reuniones[] = ['fecha' => $f, 'hora' => $h];
        }
        if (empty($reuniones)) {
            return redirect()->back()->withInput()->with('error', 'Debes registrar al menos 1 fecha de reunion.');
        }

        // Ordenar por fecha
        usort($reuniones, fn($a, $b) => strcmp($a['fecha'], $b['fecha']));

        $asunto     = trim((string) $this->request->getPost('asunto')) ?: $this->presetEmailCronograma($tipoComite, $cliente['nombre_cliente'], $anio)['asunto'];
        $cuerpoHtml = trim((string) $this->request->getPost('cuerpo')) ?: $this->presetEmailCronograma($tipoComite, $cliente['nombre_cliente'], $anio)['cuerpo'];

        // 2) CSV
        $csvFile = $this->request->getFile('csv_emails');
        if (!$csvFile || !$csvFile->isValid()) {
            return redirect()->back()->withInput()->with('error', 'Debes adjuntar el archivo CSV con los emails.');
        }
        $parseo = $svc->parsearCsvEmails($csvFile->getTempName());
        if (empty($parseo['rows'])) {
            return redirect()->back()->withInput()->with('error', 'CSV sin emails validos. Errores: ' . implode('; ', $parseo['errores']));
        }
        $destinatarios = $parseo['rows'];

        // 3) PDF
        $logoBase64 = $this->imgToBase64(FCPATH . ($cliente['logo'] ?? ''));
        $htmlPdf = view('comites_elecciones/socializacion/cronograma_pdf', [
            'cliente'         => $cliente,
            'logoBase64'      => $logoBase64,
            'tipoComiteCorto' => $tipoComite,
            'anio'            => $anio,
            'reuniones'       => $reuniones,
            'mensajeCronograma' => $tipoObj->getMensajeCronograma($cliente['nombre_cliente'], $anio),
            'codigoFt'        => $tipoObj->getCodigoBase(),
        ]);
        $rutaPdfRel = $svc->generarPdfDesdeHtml($htmlPdf, "cronograma_{$tipoComite}_{$cliente['id_cliente']}_{$anio}");

        // 4) Enviar (CC consultor del cliente)
        $cc = $this->consultorDelCliente((int) $cliente['id_cliente']);
        $resultado = $svc->enviarPdfPorEmail($rutaPdfRel, $destinatarios, $asunto, $cuerpoHtml, $cliente['nombre_cliente'] ?? 'EnterpriseSST', $cc);

        // 5) Evidencia
        $rutaEvidenciaRel = $svc->generarPdfEvidencia(
            $rutaPdfRel, $asunto, $cuerpoHtml, $resultado,
            $cliente['nombre_cliente'] ?? '', "Cronograma {$tipoComite} {$anio}"
        );

        // 6) Persistir
        $snapshot = $tipoObj->buildContenidoSnapshot([
            'anio'            => $anio,
            'reuniones'       => $reuniones,
            'id_cliente'      => $cliente['id_cliente'],
            'nombre_cliente'  => $cliente['nombre_cliente'],
            'destinatarios'   => $resultado['detalle'] ?? [],
            'enviados_ok'     => $resultado['ok'],
            'fallidos'        => $resultado['fallidos'],
        ]);

        $idDocPrincipal = $svc->guardarEnReportlist(
            $cliente['id_cliente'], $tipoDoc,
            "Cronograma {$tipoComite} {$anio} - " . ($cliente['nombre_cliente'] ?? ''),
            $tipoObj->getCodigoBase(), $rutaPdfRel, $snapshot,
            "Generado desde proceso electoral ID {$idProceso}"
        );
        $idDocEvidencia = $svc->guardarEnReportlist(
            $cliente['id_cliente'], $tipoDoc . '_evidencia',
            "Evidencia Cronograma {$tipoComite} {$anio}",
            $tipoObj->getCodigoBase() . '-EVD',
            $rutaEvidenciaRel, null,
            "Evidencia de envio (id_doc_principal={$idDocPrincipal})"
        );

        $estado = $resultado['fallidos'] === 0 ? 'enviado'
                : ($resultado['ok'] === 0 ? 'fallido' : 'parcial');

        $svc->guardarSocializacion([
            'id_cliente'             => $cliente['id_cliente'],
            'id_proceso'             => $idProceso,
            'tipo_socializacion'     => 'cronograma',
            'tipo_comite'            => $tipoComite,
            'id_documento_sst'       => $idDocPrincipal,
            'id_documento_evidencia' => $idDocEvidencia,
            'asunto_email'           => $asunto,
            'cuerpo_email'           => $cuerpoHtml,
            'destinatarios_json'     => json_encode($resultado['detalle'], JSON_UNESCAPED_UNICODE),
            'total_destinatarios'    => count($destinatarios),
            'enviados_ok'            => $resultado['ok'],
            'fallidos'               => $resultado['fallidos'],
            'estado'                 => $estado,
            'contenido_snapshot'     => $snapshot,
            'created_by'             => session()->get('id_usuario'),
        ]);

        $msg = "Socializacion del cronograma enviada. {$resultado['ok']} OK / {$resultado['fallidos']} fallidos.";
        return redirect()->to("/comites-elecciones/proceso/{$idProceso}/socializar/cronograma")
            ->with($resultado['fallidos'] > 0 ? 'warning' : 'success', $msg);
    }

    // =========================================================================
    // Helpers privados
    // =========================================================================

    /**
     * Carga los miembros del proceso (electos+designados+aprobados) con foto en base64.
     * Reproduce la misma logica de obtenerDatosActa() en ComitesEleccionesController:
     * - trabajadores: representacion=trabajador, estado IN (elegido, aprobado)
     * - empleador:    representacion=empleador,  estado IN (designado, aprobado)
     */
    private function cargarMiembrosElectos(int $idProceso): array
    {
        // Trabajadores electos/aprobados (ordenados por votos)
        $trabajadores = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->where('representacion', 'trabajador')
            ->whereIn('estado', ['elegido', 'aprobado'])
            ->orderBy('votos_obtenidos', 'DESC')
            ->orderBy('apellidos', 'ASC')
            ->get()->getResultArray();

        // Empleador designados/aprobados (principales antes que suplentes)
        $empleador = $this->db->table('tbl_candidatos_comite')
            ->where('id_proceso', $idProceso)
            ->where('representacion', 'empleador')
            ->whereIn('estado', ['designado', 'aprobado'])
            ->orderBy('tipo_plaza', 'ASC')
            ->orderBy('apellidos', 'ASC')
            ->get()->getResultArray();

        $miembros = [];
        foreach (array_merge($empleador, $trabajadores) as $c) {
            $tipoPlaza = strtolower($c['tipo_plaza'] ?? '');
            $miembros[] = [
                'nombre'         => trim(($c['nombres'] ?? '') . ' ' . ($c['apellidos'] ?? '')),
                'cargo'          => $c['cargo'] ?? '',
                'rol_comite'     => $tipoPlaza === 'suplente' ? 'Suplente' : 'Principal',
                'representacion' => $c['representacion'] ?? '',
                'tipo_plaza'     => $tipoPlaza,
                'foto_base64'    => !empty($c['foto']) ? $this->imgToBase64(FCPATH . $c['foto']) : '',
            ];
        }
        return $miembros;
    }

    /**
     * Devuelve ['email','nombre'] del consultor responsable del cliente, o null si no se puede determinar.
     * El consultor va en CC en cada email de socializacion (ver tbl_clientes.id_consultor -> tbl_consultor).
     */
    private function consultorDelCliente(int $idCliente): ?array
    {
        $row = $this->db->table('tbl_clientes c')
            ->select('co.id_consultor, co.nombre_consultor, co.correo_consultor')
            ->join('tbl_consultor co', 'co.id_consultor = c.id_consultor', 'left')
            ->where('c.id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$row || empty($row['correo_consultor'])) return null;

        return [
            'email'  => trim((string) $row['correo_consultor']),
            'nombre' => trim((string) ($row['nombre_consultor'] ?? '')),
        ];
    }

    private function imgToBase64(string $rutaAbs): string
    {
        if (!is_file($rutaAbs)) return '';
        $contenido = @file_get_contents($rutaAbs);
        if ($contenido === false) return '';
        $mime = function_exists('mime_content_type') ? @mime_content_type($rutaAbs) : 'image/jpeg';
        return 'data:' . ($mime ?: 'image/jpeg') . ';base64,' . base64_encode($contenido);
    }

    private function presetEmailMiembros(string $tipoComite, string $nombreEmpresa, int $anio): array
    {
        $asuntoMap = [
            'COPASST' => "Conformacion del COPASST {$nombreEmpresa} {$anio}",
            'COCOLAB' => "Conformacion del Comite de Convivencia Laboral {$nombreEmpresa} {$anio}",
            'BRIGADA' => "Brigada de Emergencias {$nombreEmpresa} {$anio} - Integrantes",
        ];
        $cuerpoMap = [
            'COPASST' => "Estimado(a) colaborador(a),<br><br>Te compartimos la conformacion oficial del Comite Paritario de Seguridad y Salud en el Trabajo (COPASST) de <strong>{$nombreEmpresa}</strong>. En el documento adjunto encontraras los miembros electos por los trabajadores y los designados por el empleador, con sus respectivos roles dentro del comite.<br><br>Para inquietudes o sugerencias, comunicate con el comite a traves de los correos indicados en el documento.<br><br>Saludos cordiales,<br>{$nombreEmpresa}",
            'COCOLAB' => "Estimado(a) colaborador(a),<br><br>Te compartimos la conformacion oficial del Comite de Convivencia Laboral de <strong>{$nombreEmpresa}</strong>, conforme a la Resolucion 652 de 2012 (modificada por la Resolucion 3461 de 2025) y la Ley 1010 de 2006.<br><br>Si vives o eres testigo de una situacion de acoso laboral o conducta abusiva, este comite es el canal seguro y confidencial para presentar tu queja. Encontraras en el documento adjunto a los miembros disponibles para escucharte.<br><br>Saludos cordiales,<br>{$nombreEmpresa}",
            'BRIGADA' => "Estimado(a) colaborador(a),<br><br>Te compartimos la conformacion de la Brigada de Emergencias de <strong>{$nombreEmpresa}</strong>. En el documento adjunto encontraras a los integrantes capacitados para responder ante incendios, evacuaciones, primeros auxilios y rescate.<br><br>Conoce a los brigadistas de tu sede o area: ellos lideraran la evacuacion ante una emergencia. En caso de eventualidad, sigue sus instrucciones y dirigete al punto de encuentro.<br><br>Saludos cordiales,<br>{$nombreEmpresa}",
        ];
        return [
            'asunto' => $asuntoMap[$tipoComite] ?? "Socializacion {$tipoComite} {$nombreEmpresa}",
            'cuerpo' => $cuerpoMap[$tipoComite] ?? "Estimado(a) colaborador(a),<br><br>Te compartimos el documento adjunto.<br><br>Saludos,<br>{$nombreEmpresa}",
        ];
    }

    private function presetEmailCronograma(string $tipoComite, string $nombreEmpresa, int $anio): array
    {
        $asunto = "Cronograma de Reuniones {$tipoComite} {$anio} - {$nombreEmpresa}";
        $cuerpo = "Estimado(a) colaborador(a),<br><br>Te compartimos el cronograma de reuniones del <strong>{$tipoComite}</strong> de <strong>{$nombreEmpresa}</strong> para el ano {$anio}.<br><br>Estas fechas te permitiran anticiparte para llevar solicitudes, recomendaciones o temas que desees que sean tratados en el comite. Tu participacion fortalece la cultura del autocuidado y la mejora continua en seguridad y salud en el trabajo.<br><br>Saludos cordiales,<br>{$nombreEmpresa}";

        if ($tipoComite === 'COCOLAB') {
            $cuerpo = "Estimado(a) colaborador(a),<br><br>Te compartimos el cronograma de reuniones del <strong>Comite de Convivencia Laboral</strong> de <strong>{$nombreEmpresa}</strong> para el ano {$anio}.<br><br>El comite sesiona conforme a la Resolucion 652/2012 (modificada por la Res. 3461/2025) y la Ley 1010 de 2006. Si necesitas presentar una queja relacionada con acoso laboral, puedes comunicarte con cualquier miembro del comite en cualquier momento - tu solicitud sera atendida con la confidencialidad que la norma exige.<br><br>Saludos cordiales,<br>{$nombreEmpresa}";
        }
        return ['asunto' => $asunto, 'cuerpo' => $cuerpo];
    }
}
