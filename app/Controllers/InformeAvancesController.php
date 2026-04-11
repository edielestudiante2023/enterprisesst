<?php

namespace App\Controllers;

use App\Models\InformeAvancesModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Models\ActaVisitaModel;
use App\Libraries\MetricasInformeService;
use App\Services\IADocumentacionService;
use Dompdf\Dompdf;

class InformeAvancesController extends BaseController
{
    protected InformeAvancesModel $informeModel;

    public function __construct()
    {
        $this->informeModel = new InformeAvancesModel();
    }

    // ─── LIST ───
    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        $informes = $this->informeModel->getAll();
        $pendientes = $this->informeModel->getAllPendientes();

        return view('informe_avances/list', [
            'informes'   => $informes,
            'pendientes' => $pendientes,
            'role'       => $role,
        ]);
    }

    // ─── CREATE ───
    public function create($idCliente = null)
    {
        $data = [
            'informe'      => null,
            'id_cliente'   => $idCliente,
            'mode'         => 'create',
            'vencimientos' => $idCliente ? $this->getVencimientosCliente((int) $idCliente) : [],
        ];

        return view('informe_avances/form', $data);
    }

    // ─── STORE ───
    public function store()
    {
        $userId = session()->get('user_id');

        $data = $this->getInformePostData();
        $data['id_consultor'] = $userId;
        $data['estado'] = 'borrador';
        $data['anio'] = $this->request->getPost('anio') ?: date('Y', strtotime($data['fecha_hasta']));

        // Subir imágenes de soportes
        for ($i = 1; $i <= 4; $i++) {
            $data["soporte_{$i}_imagen"] = $this->uploadFoto("soporte_{$i}_imagen", 'uploads/informe-avances/soportes/');
        }
        // Subir screenshots opcionales
        foreach (['img_cumplimiento_estandares', 'img_indicador_plan_trabajo', 'img_indicador_capacitacion'] as $campo) {
            $uploaded = $this->uploadFoto($campo, 'uploads/informe-avances/screenshots/');
            if ($uploaded) {
                $data[$campo] = $uploaded;
            }
        }

        $this->informeModel->insert($data);
        $id = $this->informeModel->getInsertID();

        return redirect()->to('/informe-avances/edit/' . $id)
            ->with('msg', 'Informe guardado como borrador');
    }

    // ─── EDIT ───
    public function edit($id)
    {
        $informe = $this->informeModel->find($id);
        if (!$informe) {
            return redirect()->to('/informe-avances')->with('error', 'Informe no encontrado');
        }

        return view('informe_avances/form', [
            'informe'      => $informe,
            'id_cliente'   => $informe['id_cliente'],
            'mode'         => 'edit',
            'vencimientos' => $this->getVencimientosCliente((int) $informe['id_cliente']),
        ]);
    }

    // ─── UPDATE ───
    public function update($id)
    {
        $informe = $this->informeModel->find($id);
        if (!$informe) {
            return redirect()->to('/informe-avances')->with('error', 'No se puede editar');
        }

        $data = $this->getInformePostData();
        $data['anio'] = $this->request->getPost('anio') ?: date('Y', strtotime($data['fecha_hasta']));

        // Subir imágenes de soportes (solo si hay archivo nuevo)
        for ($i = 1; $i <= 4; $i++) {
            $uploaded = $this->uploadFoto("soporte_{$i}_imagen", 'uploads/informe-avances/soportes/');
            if ($uploaded) {
                // Eliminar anterior
                if (!empty($informe["soporte_{$i}_imagen"]) && file_exists(FCPATH . $informe["soporte_{$i}_imagen"])) {
                    unlink(FCPATH . $informe["soporte_{$i}_imagen"]);
                }
                $data["soporte_{$i}_imagen"] = $uploaded;
            }
        }
        // Screenshots opcionales
        foreach (['img_cumplimiento_estandares', 'img_indicador_plan_trabajo', 'img_indicador_capacitacion'] as $campo) {
            $uploaded = $this->uploadFoto($campo, 'uploads/informe-avances/screenshots/');
            if ($uploaded) {
                if (!empty($informe[$campo]) && file_exists(FCPATH . $informe[$campo])) {
                    unlink(FCPATH . $informe[$campo]);
                }
                $data[$campo] = $uploaded;
            }
        }

        $this->informeModel->update($id, $data);

        // Si el informe ya estaba completo, regenerar PDF y re-subir a reportes
        $informe = $this->informeModel->find($id);
        if ($informe['estado'] === 'completo') {
            $pdfPath = $this->generarPdfInterno($id);
            if ($pdfPath) {
                $this->informeModel->update($id, ['ruta_pdf' => $pdfPath]);
                $informe = $this->informeModel->find($id);
                $this->uploadToReportes($informe, $pdfPath);
            }
        }

        return redirect()->to('/informe-avances/edit/' . $id)
            ->with('msg', 'Informe actualizado');
    }

    // ─── VIEW (read-only) ───
    public function view($id)
    {
        $informe = $this->informeModel->find($id);
        if (!$informe) {
            return redirect()->to('/informe-avances')->with('error', 'Informe no encontrado');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($informe['id_cliente']);
        $consultor = $consultantModel->find($informe['id_consultor']);

        $idCliente = (int) $informe['id_cliente'];

        $metricasService = new MetricasInformeService();
        $documentosCargados = $metricasService->getDocumentosCargados(
            $idCliente,
            $informe['fecha_desde'] ?? date('Y') . '-01-01',
            $informe['fecha_hasta'] ?? date('Y-m-d')
        );

        return view('informe_avances/view', [
            'informe'              => $informe,
            'cliente'              => $cliente,
            'consultor'            => $consultor,
            'vencimientos'         => $this->getVencimientosCliente($idCliente),
            'historialEstandares'  => $this->getHistorialEstandaresCliente($idCliente, (int) $informe['anio']),
            'historialPlan'        => $this->getHistorialPlanCliente($idCliente, (int) $informe['anio']),
            'documentosCargados'   => $documentosCargados,
        ]);
    }

    // ─── FINALIZAR ───
    public function finalizar($id)
    {
        $informe = $this->informeModel->find($id);
        if (!$informe) {
            return redirect()->to('/informe-avances')->with('error', 'Informe no encontrado');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->informeModel->update($id, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $informe = $this->informeModel->find($id);
        $this->uploadToReportes($informe, $pdfPath);

        return redirect()->to('/informe-avances/view/' . $id)
            ->with('msg', 'Informe finalizado y PDF generado');
    }

    // ─── GENERATE PDF (servir) ───
    public function generatePdf($id)
    {
        $informe = $this->informeModel->find($id);
        if (!$informe || empty($informe['ruta_pdf'])) {
            return redirect()->back()->with('error', 'PDF no disponible');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        // Actualizar ruta y re-subir a reportes
        $this->informeModel->update($id, ['ruta_pdf' => $pdfPath]);
        $informe = $this->informeModel->find($id);
        $this->uploadToReportes($informe, $pdfPath);

        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'Archivo PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="informe_avances_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    // ─── REGENERAR PDF (informe ya finalizado) ───
    public function regenerarPdf($id)
    {
        $informe = $this->informeModel->find($id);
        if (!$informe || ($informe['estado'] ?? '') !== 'completo') {
            return redirect()->to('/informe-avances')->with('error', 'Solo se puede regenerar un informe finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->informeModel->update($id, ['ruta_pdf' => $pdfPath]);
        $informe = $this->informeModel->find($id);
        $this->uploadToReportes($informe, $pdfPath);

        return redirect()->to('/informe-avances/view/' . $id)->with('msg', 'PDF regenerado exitosamente.');
    }

    // ─── DELETE ───
    public function delete($id)
    {
        $informe = $this->informeModel->find($id);
        if (!$informe) {
            return redirect()->to('/informe-avances')->with('error', 'No encontrado');
        }

        // Eliminar archivos
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($informe["soporte_{$i}_imagen"]) && file_exists(FCPATH . $informe["soporte_{$i}_imagen"])) {
                unlink(FCPATH . $informe["soporte_{$i}_imagen"]);
            }
        }
        foreach (['img_cumplimiento_estandares', 'img_indicador_plan_trabajo', 'img_indicador_capacitacion'] as $campo) {
            if (!empty($informe[$campo]) && file_exists(FCPATH . $informe[$campo])) {
                unlink(FCPATH . $informe[$campo]);
            }
        }
        if (!empty($informe['ruta_pdf']) && file_exists(FCPATH . $informe['ruta_pdf'])) {
            unlink(FCPATH . $informe['ruta_pdf']);
        }

        $this->informeModel->delete($id);

        return redirect()->to('/informe-avances')->with('msg', 'Informe eliminado');
    }

    // ─── AJAX: Calcular métricas ───
    public function calcularMetricas($idCliente)
    {
        $service = new MetricasInformeService();

        $anio = (int) ($this->request->getGet('anio') ?: date('Y'));
        $fechaDesde = $this->request->getGet('fecha_desde') ?: ($service->getFechaDesde($idCliente, $anio) ?: "{$anio}-01-01");
        $fechaHasta = $this->request->getGet('fecha_hasta') ?: date('Y-m-d');

        $metricas = $service->calcularTodas($idCliente, $fechaDesde, $fechaHasta, $anio);

        return $this->response->setJSON([
            'success' => true,
            'data'    => $metricas,
        ]);
    }

    // ─── AJAX: Snapshot (liquidar) un cliente individual ───
    public function liquidarSnapshot($idCliente)
    {
        log_message('info', '[liquidarSnapshot] INICIO - idCliente: ' . $idCliente);
        $id = (int) $idCliente;
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $mesActual = date('Y-m');

        try {
            // Eliminar snapshots previos de este cliente en el mes actual
            $inicioMes = $mesActual . '-01 00:00:00';
            $finMes = date('Y-m-t', strtotime($inicioMes)) . ' 23:59:59';

            $db->query("DELETE FROM historial_resumen_estandares WHERE id_cliente = ? AND fecha_extraccion >= ? AND fecha_extraccion <= ?", [$id, $inicioMes, $finMes]);
            $delEst = $db->affectedRows();

            $db->query("DELETE FROM historial_resumen_plan_trabajo WHERE id_cliente = ? AND fecha_extraccion >= ? AND fecha_extraccion <= ?", [$id, $inicioMes, $finMes]);
            $delPlan = $db->affectedRows();

            // Insertar snapshot fresco desde las vistas
            $db->query("INSERT INTO historial_resumen_estandares (id_cliente, nombre_cliente, estandares, nombre_consultor, correo_consultor, total_valor, total_puntaje, porcentaje_cumplimiento, fecha_extraccion) SELECT id_cliente, nombre_cliente, estandares, nombre_consultor, correo_consultor, total_valor, total_puntaje, porcentaje_cumplimiento, ? FROM resumen_estandares_cliente WHERE id_cliente = ?", [$now, $id]);
            $insEst = $db->affectedRows();

            $db->query("INSERT INTO historial_resumen_plan_trabajo (id_cliente, nombre_cliente, estandares, nombre_consultor, correo_consultor, total_actividades, actividades_abiertas, porcentaje_abiertas, fecha_extraccion) SELECT id_cliente, nombre_cliente, estandares, nombre_consultor, correo_consultor, total_actividades, actividades_abiertas, porcentaje_abiertas, ? FROM resumen_mensual_plan_trabajo WHERE id_cliente = ?", [$now, $id]);
            $insPlan = $db->affectedRows();

            return $this->response->setJSON([
                'success' => true,
                'mensaje' => "Snapshot liquidado para cliente {$id}",
                'estandares' => ['eliminados' => $delEst, 'insertados' => $insEst],
                'plan' => ['eliminados' => $delPlan, 'insertados' => $insPlan],
                'fecha' => $now,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()])->setStatusCode(500);
        }
    }

    // ─── AJAX: Historial evolución de un cliente ───
    public function apiHistorial($idCliente)
    {
        $id = (int) $idCliente;
        $anio = (int) ($this->request->getGet('anio') ?: date('Y'));
        return $this->response->setJSON([
            'success'   => true,
            'estandares' => $this->getHistorialEstandaresCliente($id, $anio),
            'plan'       => $this->getHistorialPlanCliente($id, $anio),
        ]);
    }

    // ─── AJAX: Vencimientos de un cliente ───
    public function apiVencimientos($idCliente)
    {
        return $this->response->setJSON([
            'success' => true,
            'data'    => $this->getVencimientosCliente((int) $idCliente),
        ]);
    }

    // ─── AJAX: Generar resumen con IA ───
    public function generarResumen()
    {
        $idCliente = (int) $this->request->getPost('id_cliente');
        $fechaDesde = $this->request->getPost('fecha_desde');
        $fechaHasta = $this->request->getPost('fecha_hasta');
        $anio = (int) ($this->request->getPost('anio') ?: date('Y'));

        if (!$idCliente || !$fechaDesde || !$fechaHasta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Datos incompletos']);
        }

        $service = new MetricasInformeService();
        $metricas = $service->calcularTodas($idCliente, $fechaDesde, $fechaHasta, $anio);
        $actividades = $service->recopilarActividadesPeriodo($idCliente, $fechaDesde, $fechaHasta);

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($idCliente);
        $nombreCliente = $cliente['nombre_cliente'] ?? 'Cliente';

        $prompt = $this->buildResumenPrompt($nombreCliente, $fechaDesde, $fechaHasta, $actividades, $metricas);

        try {
            $iaService = new IADocumentacionService();
            $resumen = $iaService->generarContenido($prompt, 2000);
            return $this->response->setJSON(['success' => true, 'resumen' => $resumen]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── AJAX: API Clientes (para Select2) ───
    public function getClientes()
    {
        log_message('info', '[InformeAvances::getClientes] Endpoint llamado. Session: ' . (session()->get('isLoggedIn') ? 'SI' : 'NO'));

        $clientModel = new ClientModel();

        $clientes = $clientModel->select('id_cliente, nombre_cliente, nit_cliente')
            ->where('estado', 'activo')
            ->orderBy('nombre_cliente', 'ASC')
            ->findAll();

        log_message('info', '[InformeAvances::getClientes] Clientes encontrados: ' . count($clientes));

        return $this->response->setJSON($clientes);
    }

    // ─── API: Clientes que tuvieron visita en el periodo (para OpenClaw) ───
    public function getClientesConVisita()
    {
        $actaModel = new ActaVisitaModel();

        // Periodo: desde ultimo informe global o ultimos 3 meses por defecto
        $fechaDesde = $this->request->getGet('fecha_desde') ?: date('Y-m-d', strtotime('-3 months'));
        $fechaHasta = $this->request->getGet('fecha_hasta') ?: date('Y-m-d');

        $clientes = $actaModel->select('
                tbl_clientes.id_cliente,
                tbl_clientes.nombre_cliente,
                tbl_clientes.nit_cliente,
                tbl_clientes.correo_cliente,
                COUNT(tbl_acta_visita.id) as total_visitas,
                MAX(tbl_acta_visita.fecha_visita) as ultima_visita
            ')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_acta_visita.id_cliente')
            ->where('tbl_clientes.estado', 'activo')
            ->where('tbl_acta_visita.fecha_visita >=', $fechaDesde)
            ->where('tbl_acta_visita.fecha_visita <=', $fechaHasta)
            ->groupBy('tbl_clientes.id_cliente')
            ->orderBy('tbl_clientes.nombre_cliente', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'success'     => true,
            'periodo'     => ['desde' => $fechaDesde, 'hasta' => $fechaHasta],
            'total'       => count($clientes),
            'clientes'    => $clientes,
        ]);
    }

    // ─── PRIVATE: Recoger datos del POST ───
    private function getInformePostData(): array
    {
        return [
            'id_cliente'                   => $this->request->getPost('id_cliente'),
            'fecha_desde'                  => $this->request->getPost('fecha_desde'),
            'fecha_hasta'                  => $this->request->getPost('fecha_hasta'),
            'puntaje_anterior'             => $this->request->getPost('puntaje_anterior'),
            'puntaje_actual'               => $this->request->getPost('puntaje_actual'),
            'diferencia_neta'              => $this->request->getPost('diferencia_neta'),
            'estado_avance'                => $this->request->getPost('estado_avance'),
            'indicador_plan_trabajo'       => $this->request->getPost('indicador_plan_trabajo'),
            'indicador_capacitacion'       => $this->request->getPost('indicador_capacitacion'),
            'resumen_avance'               => $this->request->getPost('resumen_avance'),
            'observaciones'                => $this->request->getPost('observaciones'),
            'actividades_abiertas'         => $this->request->getPost('actividades_abiertas'),
            'actividades_cerradas_periodo' => $this->request->getPost('actividades_cerradas_periodo'),
            'actividades_no_cerradas_pta'  => $this->request->getPost('actividades_no_cerradas_pta'),
            'enlace_dashboard'             => $this->request->getPost('enlace_dashboard'),
            'acta_visita_url'              => $this->request->getPost('acta_visita_url'),
            'metricas_desglose_json'       => $this->request->getPost('metricas_desglose_json'),
            'soporte_1_texto'              => $this->request->getPost('soporte_1_texto'),
            'soporte_2_texto'              => $this->request->getPost('soporte_2_texto'),
            'soporte_3_texto'              => $this->request->getPost('soporte_3_texto'),
            'soporte_4_texto'              => $this->request->getPost('soporte_4_texto'),
        ];
    }

    // ─── PRIVATE: Vencimientos de mantenimiento del cliente ───
    private function getVencimientosCliente(int $idCliente): array
    {
        $vencModel = new \App\Models\VencimientosMantenimientoModel();
        $en30dias = date('Y-m-d', strtotime('+30 days'));
        return $vencModel
            ->select('tbl_vencimientos_mantenimientos.*, tbl_mantenimientos.detalle_mantenimiento')
            ->join('tbl_mantenimientos', 'tbl_mantenimientos.id_mantenimiento = tbl_vencimientos_mantenimientos.id_mantenimiento', 'left')
            ->where('tbl_vencimientos_mantenimientos.id_cliente', $idCliente)
            ->where('tbl_vencimientos_mantenimientos.estado_actividad', 'sin ejecutar')
            ->where('tbl_vencimientos_mantenimientos.fecha_vencimiento <=', $en30dias)
            ->orderBy('tbl_vencimientos_mantenimientos.fecha_vencimiento', 'ASC')
            ->findAll();
    }

    // ─── PRIVATE: Upload foto ───
    private function uploadFoto(string $campo, string $dir): ?string
    {
        $file = $this->request->getFile($campo);
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        if (!is_dir(FCPATH . $dir)) {
            mkdir(FCPATH . $dir, 0755, true);
        }

        $newName = $campo . '_' . time() . '_' . $file->getRandomName();
        $file->move(FCPATH . $dir, $newName);

        return $dir . $newName;
    }

    // ─── PRIVATE: Generar PDF interno ───
    private function generarPdfInterno(int $id): ?string
    {
        $informe = $this->informeModel->find($id);
        if (!$informe) return null;

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($informe['id_cliente']);
        $consultor = $consultantModel->find($informe['id_consultor']);

        // Logo en base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        // Soportes a base64
        $soportesBase64 = [];
        for ($i = 1; $i <= 4; $i++) {
            $soportesBase64[$i] = '';
            $imgField = "soporte_{$i}_imagen";
            if (!empty($informe[$imgField])) {
                $imgPath = FCPATH . $informe[$imgField];
                if (file_exists($imgPath)) {
                    $mime = mime_content_type($imgPath);
                    $soportesBase64[$i] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($imgPath));
                }
            }
        }

        // Decodificar desgloses JSON para gráficas del PDF
        $desglose = [];
        if (!empty($informe['metricas_desglose_json'])) {
            $desglose = json_decode($informe['metricas_desglose_json'], true) ?: [];
        }

        // Vencimientos de mantenimientos del cliente (vencidos + próximos 30 días, sin ejecutar)
        $vencimientos = $this->getVencimientosCliente((int) $informe['id_cliente']);

        $idCliente = (int) $informe['id_cliente'];
        $anioInforme = (int) $informe['anio'];
        $quickChartEstandares = $this->buildQuickChartUrl(
            $this->getHistorialEstandaresCliente($idCliente, $anioInforme),
            'porcentaje_cumplimiento',
            '% Cumplimiento Estandares',
            '#667eea'
        );
        $quickChartPlan = $this->buildQuickChartUrl(
            $this->getHistorialPlanCliente($idCliente, $anioInforme),
            'porcentaje_abiertas',
            '% Actividades Abiertas',
            '#4facfe'
        );

        $data = [
            'informe'               => $informe,
            'cliente'               => $cliente,
            'consultor'             => $consultor,
            'logoBase64'            => $logoBase64,
            'soportesBase64'        => $soportesBase64,
            'desglose'              => $desglose,
            'vencimientos'          => $vencimientos,
            'quickChartEstandares'  => $quickChartEstandares,
            'quickChartPlan'        => $quickChartPlan,
        ];

        $html = view('informe_avances/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/informe-avances/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'informe_avances_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        // Eliminar PDF anterior
        if (!empty($informe['ruta_pdf']) && file_exists(FCPATH . $informe['ruta_pdf'])) {
            unlink(FCPATH . $informe['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
    }

    // ─── PRIVATE: Upload to reportes ───
    private function uploadToReportes(array $informe, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($informe['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'];

        $existente = $reporteModel
            ->where('id_cliente', $informe['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 37)
            ->like('observaciones', 'inf_avance_id:' . $informe['id'])
            ->first();

        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $periodo = date('M-Y', strtotime($informe['fecha_desde'])) . '_' . date('M-Y', strtotime($informe['fecha_hasta']));
        $fileName = 'informe_avances_' . $informe['id'] . '_' . $periodo . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INFORME DE AVANCES - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $periodo,
            'id_detailreport' => 37,
            'id_report_type'  => 6,
            'id_cliente'      => $informe['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente. inf_avance_id:' . $informe['id'],
            'enlace'          => base_url(UPLOADS_URL_PREFIX . '/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            $reporteModel->update($existente['id_reporte'], $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $reporteModel->save($data);
        }

        // Enviar email al cliente y consultores
        $this->sendReportEmail($cliente, $data['titulo_reporte'], $data['enlace']);

        return true;
    }

    // ─── PRIVATE: Email notificación al subir reporte ───
    private function sendReportEmail(array $cliente, string $tituloReporte, string $enlace): void
    {
        if (env('DISABLE_REPORT_EMAILS', false)) {
            log_message('info', 'Email desactivado (DISABLE_REPORT_EMAILS). Informe: ' . $tituloReporte);
            return;
        }

        if (!filter_var($enlace, FILTER_VALIDATE_URL)) {
            log_message('error', 'Enlace inválido para email informe: ' . $enlace);
            return;
        }

        $nombreCliente = $cliente['nombre_cliente'] ?? 'Cliente';
        $destinatarios = [];

        // Cliente
        if (!empty($cliente['correo_cliente'])) {
            $destinatarios[$cliente['correo_cliente']] = $nombreCliente;
        }

        // Consultor interno
        if (!empty($cliente['id_consultor'])) {
            $consultorModel = new ConsultantModel();
            $consultor = $consultorModel->find($cliente['id_consultor']);
            if ($consultor && !empty($consultor['correo_consultor'])) {
                $destinatarios[$consultor['correo_consultor']] = $consultor['nombre_consultor'];
            }
        }

        // Consultor externo
        if (!empty($cliente['email_consultor_externo'])) {
            $destinatarios[$cliente['email_consultor_externo']] = $cliente['consultor_externo'] ?? 'Consultor Externo';
        }

        if (empty($destinatarios)) {
            log_message('warning', 'No hay destinatarios para email de informe avances');
            return;
        }

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
        $email->setSubject("Informe de Avances SG-SST - " . $nombreCliente);

        foreach ($destinatarios as $correo => $nombre) {
            $email->addTo($correo, $nombre);
        }

        $emailContent = "
        <h3>Estimado/a {$nombreCliente}</h3>
        <p style='text-align: justify;'>Nos complace informarle que hemos generado el <strong>{$tituloReporte}</strong> en su plataforma Enterprisesst. Este informe evidencia los avances de nuestra gestión en Seguridad y Salud en el Trabajo (SG-SST).</p>
        <p style='text-align: justify;'>El documento ya está disponible para su consulta en la sección de documentos. Acceda a su plataforma siguiendo el enlace:</p>
        <p style='text-align: center;'>
            <a href='" . base_url() . "' target='_blank' style='display: inline-block; padding: 15px 25px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 25px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);'>
                Ir a Enterprisesst
            </a>
        </p>
        <p style='text-align: justify;'>En <strong>Cycloid Talent</strong>, nos distinguimos por ser aliados estratégicos en la administración del SG-SST. Nuestro compromiso es ofrecerle soluciones innovadoras y personalizadas que potencien la seguridad y el bienestar en su copropiedad.</p>
        <p style='text-align: justify; font-size: 1.1em; font-weight: bold;'>Gracias por confiar en Cycloid Talent, donde su tranquilidad y éxito son nuestra prioridad.</p>
        ";

        $email->addContent("text/html", $emailContent);

        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        try {
            $response = $sendgrid->send($email);
            log_message('info', 'Email informe avances enviado a: ' . implode(', ', array_keys($destinatarios)) . ' - Status: ' . $response->statusCode());
        } catch (\Exception $e) {
            log_message('error', 'Error email informe avances: ' . $e->getMessage());
        }
    }

    // ─── PRIVATE: Construir prompt IA para resumen ───
    private function buildResumenPrompt(string $nombreCliente, string $desde, string $hasta, array $actividades, array $metricas, array $historialEst = [], array $historialPlan = [], array $vencimientos = [], array $capacitaciones = [], array $ptaPeriodo = []): string
    {
        $actividadesTexto = empty($actividades) ? 'No se registraron actividades en el periodo.' : implode("\n", $actividades);

        $estado = $metricas['estado_avance'] ?? 'ESTABLE';
        $puntajeActual = $metricas['puntaje_actual'] ?? 0;
        $puntajeAnterior = $metricas['puntaje_anterior'] ?? 39.75;
        $diferencia = $metricas['diferencia_neta'] ?? 0;

        // Desgloses por pilar
        $desgloseTexto = $this->formatDesgloseForPrompt($metricas);

        // Documentos cargados en el periodo
        $docs = $metricas['documentos_cargados_raw'] ?? [];
        $documentosTexto = '';
        if (!empty($docs)) {
            $documentosTexto = "DOCUMENTOS CARGADOS A LA PLATAFORMA EN EL PERIODO (" . count($docs) . " documentos):\n";
            foreach ($docs as $doc) {
                $fecha = substr($doc['created_at'] ?? '', 0, 10);
                $tipo = $doc['detail_report'] ?? 'Sin tipo';
                $cat = $doc['report_type'] ?? 'Sin categoría';
                $titulo = $doc['titulo_reporte'] ?? 'Sin título';
                $documentosTexto .= "- [{$fecha}] {$titulo} (Tipo: {$tipo}, Categoría: {$cat})\n";
            }
        } else {
            $documentosTexto = "DOCUMENTOS CARGADOS A LA PLATAFORMA EN EL PERIODO: Ninguno.";
        }

        // Evolución histórica
        $mesesNombres = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun',
                         '07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
        $evolucionTexto = '';
        if (!empty($historialEst)) {
            $evolucionTexto .= "EVOLUCION HISTORICA DE CALIFICACION ESTANDARES (mes a mes):\n";
            foreach ($historialEst as $h) {
                $parts = explode('-', $h['mes']);
                $mesNombre = ($mesesNombres[$parts[1] ?? ''] ?? $parts[1] ?? '') . ' ' . ($parts[0] ?? '');
                $evolucionTexto .= "- {$mesNombre}: {$h['promedio']} de 100\n";
            }
            $evolucionTexto .= "\n";
        }
        if (!empty($historialPlan)) {
            $evolucionTexto .= "EVOLUCION HISTORICA DE ACTIVIDADES ABIERTAS PTA (mes a mes):\n";
            foreach ($historialPlan as $h) {
                $parts = explode('-', $h['mes']);
                $mesNombre = ($mesesNombres[$parts[1] ?? ''] ?? $parts[1] ?? '') . ' ' . ($parts[0] ?? '');
                $evolucionTexto .= "- {$mesNombre}: {$h['promedio']} actividades abiertas\n";
            }
            $evolucionTexto .= "\n";
        }

        // Vencimientos
        $vencimientosTexto = '';
        if (!empty($vencimientos)) {
            $hoy = date('Y-m-d');
            $vencidos = 0;
            $proximos = 0;
            $vencimientosTexto = "ELEMENTOS CON VENCIMIENTO PROXIMO O VENCIDO (" . count($vencimientos) . " elementos):\n";
            foreach ($vencimientos as $v) {
                $estado_v = ($v['fecha_vencimiento'] <= $hoy) ? 'VENCIDO' : 'PROXIMO';
                if ($estado_v === 'VENCIDO') $vencidos++; else $proximos++;
                $vencimientosTexto .= "- {$v['detalle_mantenimiento']}: vence {$v['fecha_vencimiento']} ({$estado_v})\n";
            }
            $vencimientosTexto .= "Resumen: {$vencidos} vencidos, {$proximos} próximos a vencer.\n";
        }

        // Capacitaciones ejecutadas
        $capacitacionesTexto = '';
        if (!empty($capacitaciones)) {
            $capacitacionesTexto = "CAPACITACIONES EJECUTADAS EN EL PERIODO (" . count($capacitaciones) . "):\n";
            foreach ($capacitaciones as $cap) {
                $fecha = $cap['fecha_de_realizacion'] ?: $cap['fecha_programada'];
                $asistentes = $cap['numero_de_asistentes_a_capacitacion'] ?: '?';
                $programados = $cap['numero_total_de_personas_programadas'] ?: '?';
                $cobertura = $cap['porcentaje_cobertura'] ? $cap['porcentaje_cobertura'] . '%' : 'N/A';
                $calificacion = $cap['promedio_de_calificaciones'] ?: 'N/A';
                $horas = $cap['horas_de_duracion_de_la_capacitacion'] ?: '?';
                $capacitacionesTexto .= "- [{$fecha}] {$cap['nombre_capacitacion']} — {$horas}h, {$asistentes}/{$programados} asistentes (cobertura: {$cobertura}), calificación promedio: {$calificacion}\n";
            }
        } else {
            $capacitacionesTexto = "CAPACITACIONES EJECUTADAS EN EL PERIODO: Ninguna.";
        }

        // Compromisos PTA del periodo evaluado
        $ptaPeriodoTexto = '';
        if (!empty($ptaPeriodo) && $ptaPeriodo['total_periodo'] > 0) {
            $ptaPeriodoTexto = "COMPROMISOS PTA PROGRAMADOS PARA ESTE PERIODO ({$desde} a {$hasta}):\n";
            $ptaPeriodoTexto .= "- Total programados: {$ptaPeriodo['total_periodo']}\n";
            $ptaPeriodoTexto .= "- Cerrados: {$ptaPeriodo['cerradas_periodo']}\n";
            $ptaPeriodoTexto .= "- Aún abiertos: {$ptaPeriodo['abiertas_periodo']}\n";
            $pctCump = $ptaPeriodo['total_periodo'] > 0 ? round(($ptaPeriodo['cerradas_periodo'] / $ptaPeriodo['total_periodo']) * 100, 1) : 0;
            $ptaPeriodoTexto .= "- Cumplimiento del periodo: {$pctCump}%\n";
        } else {
            $ptaPeriodoTexto = "COMPROMISOS PTA PROGRAMADOS PARA ESTE PERIODO: No hay actividades con fecha propuesta en este rango.";
        }

        // Actividades PTA no cerradas
        $noCerradasPta = $metricas['actividades_no_cerradas_pta'] ?? '';
        $noCerradasTexto = '';
        if (!empty($noCerradasPta)) {
            $noCerradasTexto = "ACTIVIDADES PTA NO CERRADAS EN EL PERIODO:\n{$noCerradasPta}";
        } else {
            $noCerradasTexto = "ACTIVIDADES PTA NO CERRADAS EN EL PERIODO: Todas las actividades programadas fueron cerradas.";
        }

        // --- 6 MÓDULOS ADICIONALES ---

        // Firma electrónica
        $firmaData = $metricas['firma_electronica'] ?? [];
        $firmaTexto = '';
        if (!empty($firmaData) && ($firmaData['total_solicitudes'] ?? 0) > 0) {
            $t = $firmaData;
            $firmaTexto = "FIRMA ELECTRONICA EN EL PERIODO ({$t['total_solicitudes']} solicitudes):\n";
            $firmaTexto .= "- Firmados: {$t['firmados']}\n";
            $firmaTexto .= "- Pendientes: {$t['pendientes']}\n";
            $firmaTexto .= "- Expirados: {$t['expirados']}\n";
            $firmaTexto .= "- Tasa de firma: {$t['tasa_firma']}%\n";
        } else {
            $firmaTexto = "FIRMA ELECTRONICA EN EL PERIODO: Sin solicitudes.";
        }

        // Documentos SST
        $docsSst = $metricas['documentos_sst'] ?? [];
        $docsSstTexto = '';
        if (!empty($docsSst) && ($docsSst['total_creados'] ?? 0) > 0) {
            $docsSstTexto = "DOCUMENTOS SST CREADOS EN EL PERIODO ({$docsSst['total_creados']} documentos):\n";
            foreach (($docsSst['documentos'] ?? []) as $doc) {
                $tipo = str_replace('_', ' ', $doc['tipo_documento'] ?? '');
                $fecha = substr($doc['created_at'] ?? '', 0, 10);
                $docsSstTexto .= "- [{$fecha}] {$tipo}\n";
            }
        } else {
            $docsSstTexto = "DOCUMENTOS SST CREADOS EN EL PERIODO: Ninguno.";
        }

        // Indicadores SST
        $indData = $metricas['indicadores_sst'] ?? [];
        $indicadoresTexto = '';
        if (!empty($indData) && ($indData['total_activos'] ?? 0) > 0) {
            $indicadoresTexto = "INDICADORES SST ({$indData['total_activos']} activos):\n";
            $indicadoresTexto .= "- Mediciones en el periodo: {$indData['medidos_periodo']}\n";
            $indicadoresTexto .= "- Cumplen meta: {$indData['cumplen_meta']}\n";
            $indicadoresTexto .= "- Porcentaje cumplimiento: {$indData['pct_cumplimiento']}%\n";
        } else {
            $indicadoresTexto = "INDICADORES SST: Sin indicadores activos.";
        }

        // Acciones correctivas
        $accData = $metricas['acciones_correctivas'] ?? [];
        $accionesTexto = '';
        if (!empty($accData) && ($accData['acciones_total'] ?? 0) > 0) {
            $kpis = $accData['kpis'] ?? [];
            $accionesTexto = "ACCIONES CORRECTIVAS (anual):\n";
            $accionesTexto .= "- Hallazgos totales: {$accData['hallazgos_total']} (abiertos: {$accData['hallazgos_abiertos']}, cerrados: {$accData['hallazgos_cerrados']})\n";
            $accionesTexto .= "- Acciones totales: {$accData['acciones_total']}, vencidas: {$accData['acciones_vencidas']}\n";
            $accionesTexto .= "- Cierre a tiempo: {$kpis['cierre_a_tiempo']}% (meta 85%)\n";
            $accionesTexto .= "- Efectividad: {$kpis['efectividad']}% (meta 80%)\n";
            $accionesTexto .= "- Días promedio cierre: {$kpis['dias_promedio']} (meta 30)\n";
        } else {
            $accionesTexto = "ACCIONES CORRECTIVAS: Sin acciones registradas.";
        }

        // Actas de comité
        $actasData = $metricas['actas_comite'] ?? [];
        $actasTexto = '';
        if (!empty($actasData) && !empty($actasData['por_comite'])) {
            $actasTexto = "ACTAS DE COMITE ({$actasData['reuniones_periodo']} reuniones en el periodo):\n";
            foreach ($actasData['por_comite'] as $pc) {
                $actasTexto .= "- {$pc['tipo_comite']}: {$pc['total_anio']} reuniones en el año de {$pc['esperadas']} esperadas ({$pc['cumplimiento']}% cumplimiento)\n";
            }
            $comp = $actasData['compromisos'] ?? [];
            if (($comp['total'] ?? 0) > 0) {
                $actasTexto .= "Compromisos: {$comp['total']} total, {$comp['cumplidos']} cumplidos, {$comp['pendientes']} pendientes, {$comp['vencidos']} vencidos ({$comp['pct']}% cumplimiento)\n";
            }
        } else {
            $actasTexto = "ACTAS DE COMITE: Sin reuniones registradas.";
        }

        // Inspecciones
        $inspData = $metricas['inspecciones'] ?? [];
        $inspeccionesTexto = '';
        if (!empty($inspData) && ($inspData['total_inspecciones'] ?? 0) > 0) {
            $inspeccionesTexto = "INSPECCIONES EN EL PERIODO ({$inspData['total_inspecciones']} inspecciones, {$inspData['total_completadas']} completadas):\n";
            foreach ($inspData['por_tipo'] as $tipo => $v) {
                if ($v['total'] > 0) {
                    $inspeccionesTexto .= "- {$tipo}: {$v['total']} ({$v['completadas']} completadas)\n";
                }
            }
            $hall = $inspData['hallazgos'] ?? [];
            if (($hall['total'] ?? 0) > 0) {
                $inspeccionesTexto .= "Hallazgos locativos: {$hall['total']} encontrados, {$hall['corregidos']} corregidos, {$hall['pendientes']} pendientes\n";
            }
        } else {
            $inspeccionesTexto = "INSPECCIONES EN EL PERIODO: Ninguna.";
        }

        return <<<PROMPT
Eres un consultor comercial y experto en Seguridad y Salud en el Trabajo (SG-SST) en Colombia. Tu objetivo es redactar un informe que transmita confianza al cliente, resaltando los logros y el valor del servicio de consultoría.

Genera un resumen ejecutivo de avance del SG-SST para el cliente "{$nombreCliente}" correspondiente al periodo {$desde} a {$hasta}.

DATOS DEL PERIODO:
- Calificación estándares mínimos actual: {$puntajeActual} de 100
- Calificación periodo anterior: {$puntajeAnterior} de 100
- Diferencia neta: {$diferencia} puntos
- Estado de avance: {$estado}

ACTIVIDADES CERRADAS EN EL PERIODO:
{$actividadesTexto}

{$documentosTexto}

{$capacitacionesTexto}

{$ptaPeriodoTexto}

{$noCerradasTexto}

{$evolucionTexto}
{$vencimientosTexto}

{$firmaTexto}

{$docsSstTexto}

{$indicadoresTexto}

{$accionesTexto}

{$actasTexto}

{$inspeccionesTexto}

ESTILO Y TONO:
1. Tono positivo, comercial y orientado a resultados. El informe lo lee el cliente y debe sentir que su inversión en consultoría SST genera valor.
2. IMPORTANTE: El resumen debe hablar EXCLUSIVAMENTE de lo que ocurrió en el periodo evaluado ({$desde} a {$hasta}). No menciones totales anuales ni actividades fuera de este periodo.
3. Resalta PRIMERO los logros del periodo: actividades cerradas, documentos generados, capacitaciones ejecutadas, avance en calificación.
4. Usa los "COMPROMISOS PTA PROGRAMADOS PARA ESTE PERIODO" para hablar de lo que se debía hacer vs lo que se hizo. No mezcles con el total anual.
5. Presenta las actividades pendientes del periodo como "próximos pasos" u "oportunidades de mejora", NUNCA como problemas.
6. Si hay avance en la calificación, celébralo. Si no hay avance, enfócate en las actividades realizadas.
7. Menciona los documentos cargados como evidencia tangible del trabajo realizado.
8. Si hay capacitaciones ejecutadas, destácalas mencionando nombre, asistentes y cobertura como logros de formación.
9. Si hay evolución histórica, menciona la tendencia positiva mes a mes.
10. Si hay vencimientos próximos o vencidos, menciónalos como un tema que requiere coordinación, sin alarmar.
11. Máximo 4 párrafos, concisos y contundentes.
12. Prosa continua, sin viñetas ni listas.
13. Tercera persona, profesional pero cercano.
14. No incluyas saludos ni despedidas.
15. La calificación de estándares es un puntaje sobre 100, NO un porcentaje. No uses "%" para referirte a ella.
16. No menciones ciclos PHVA con números crudos ni fórmulas. Si mencionas un ciclo, solo indica si va bien o necesita atención.
17. Si hay firmas electrónicas completadas, menciónalas como avance en la formalización documental.
18. Si hay indicadores SST medidos con cumplimiento de meta, resáltalos. Si hay indicadores sin medir, menciónalos como próximo paso.
19. Si hay actas de comité e inspecciones realizadas, destácalas como evidencia de participación y cumplimiento normativo.
20. Si hay acciones correctivas cerradas con alta efectividad, celébralo. Si hay vencidas, preséntalo como tema de seguimiento coordinado.
PROMPT;
    }

    // ─── PRIVATE: Formatear desgloses para el prompt de IA ───
    private function formatDesgloseForPrompt(array $metricas): string
    {
        $lines = [];

        // Desglose Estándares por ciclo PHVA
        $est = $metricas['desglose_estandares'] ?? [];
        if (!empty($est)) {
            $lines[] = 'DESGLOSE CALIFICACION POR CICLO PHVA (puntaje logrado / puntaje maximo):';
            foreach ($est as $e) {
                $ciclo = $e['ciclo'] ?? 'Sin ciclo';
                $logrado = floatval($e['total_valor'] ?? 0);
                $maximo = floatval($e['total_posible'] ?? 0);
                $pct = $maximo > 0 ? round(($logrado / $maximo) * 100, 1) : 0;
                $lines[] = "- {$ciclo}: {$logrado} de {$maximo} ({$pct}%)";
            }
            $lines[] = '';
        }

        // Pendientes por estado
        $pend = $metricas['desglose_pendientes'] ?? [];
        if (!empty($pend)) {
            $lines[] = 'ESTADO DE COMPROMISOS/PENDIENTES:';
            foreach ($pend as $p) {
                $det = "{$p['cantidad']} {$p['estado']}";
                if (!empty($p['promedio_dias']) && floatval($p['promedio_dias']) > 0) {
                    $det .= ' (promedio ' . round(floatval($p['promedio_dias']), 1) . ' días)';
                }
                $lines[] = "- {$det}";
            }
        }

        return implode("\n", $lines);
    }

    // ─── ENVIAR INFORME POR EMAIL (SendGrid) ───
    public function enviar($id)
    {
        $informe = $this->informeModel->find($id);
        if (!$informe) {
            return $this->response->setJSON(['success' => false, 'error' => 'Informe no encontrado']);
        }

        if ($informe['estado'] !== 'completo' || empty($informe['ruta_pdf'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'El informe debe estar finalizado con PDF generado']);
        }

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($informe['id_cliente']);
        if (!$cliente || empty($cliente['correo_cliente'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Cliente sin correo electronico configurado']);
        }

        $pdfPath = FCPATH . $informe['ruta_pdf'];
        if (!file_exists($pdfPath)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Archivo PDF no encontrado en disco']);
        }

        $sendgridApiKey = getenv('SENDGRID_API_KEY');
        if (!$sendgridApiKey) {
            return $this->response->setJSON(['success' => false, 'error' => 'SENDGRID_API_KEY no configurada']);
        }

        $periodo = date('d/m/Y', strtotime($informe['fecha_desde'])) . ' - ' . date('d/m/Y', strtotime($informe['fecha_hasta']));
        $nombreCliente = $cliente['nombre_cliente'] ?? 'Cliente';
        $estadoAvance = $informe['estado_avance'] ?? 'ESTABLE';
        $puntaje = number_format($informe['puntaje_actual'] ?? 0, 1);

        $subject = "Informe de Avances SG-SST - {$nombreCliente} - {$periodo}";

        $htmlContent = "
        <div style='font-family: Segoe UI, Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #1c2437; padding: 20px; text-align: center;'>
                <h1 style='color: #bd9751; margin: 0; font-size: 20px;'>INFORME DE AVANCES SG-SST</h1>
            </div>
            <div style='padding: 25px; background: #f8f9fa;'>
                <p>Estimado(a) equipo de <strong>{$nombreCliente}</strong>,</p>
                <p>Adjunto encontrara el Informe de Avances del Sistema de Gestion de Seguridad y Salud en el Trabajo correspondiente al periodo <strong>{$periodo}</strong>.</p>
                <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Cumplimiento Estandares:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd; text-align: center; font-weight: bold; color: #bd9751;'>{$puntaje}%</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Estado de Avance:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd; text-align: center; font-weight: bold;'>{$estadoAvance}</td>
                    </tr>
                </table>
                <p>Por favor revise el documento adjunto para mayor detalle.</p>
                <p style='color: #666; font-size: 12px; margin-top: 30px;'>Este correo fue generado automaticamente por el SG-SST de Cycloid Talent.</p>
            </div>
        </div>";

        // Enviar con SendGrid SDK
        require_once ROOTPATH . 'vendor/autoload.php';

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent - SG-SST");
        $email->setSubject($subject);
        $email->addTo($cliente['correo_cliente'], $nombreCliente);
        $email->addContent("text/html", $htmlContent);

        // Adjuntar PDF
        $pdfContent = base64_encode(file_get_contents($pdfPath));
        $pdfFilename = 'Informe_Avances_' . str_replace(' ', '_', $nombreCliente) . '_' . date('Y-m', strtotime($informe['fecha_hasta'])) . '.pdf';
        $email->addAttachment($pdfContent, 'application/pdf', $pdfFilename, 'attachment');

        try {
            $sendgrid = new \SendGrid($sendgridApiKey);
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', "InformeAvances: Email enviado a {$cliente['correo_cliente']} para informe #{$id}");
                return $this->response->setJSON([
                    'success' => true,
                    'message' => "Informe enviado a {$cliente['correo_cliente']}",
                    'destinatario' => $cliente['correo_cliente'],
                ]);
            } else {
                log_message('error', "InformeAvances: Error SendGrid. Status: {$response->statusCode()}. Body: {$response->body()}");
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Error al enviar email. Status: ' . $response->statusCode(),
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'InformeAvances: Exception SendGrid: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    // ─── API: Flujo completo (crear + finalizar + enviar) para OpenClaw ───
    public function apiGenerarYEnviar($idCliente)
    {
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'error' => 'Cliente no encontrado']);
        }

        $service = new MetricasInformeService();

        // Año PHVA: por parámetro o año actual
        $anio = (int) ($this->request->getGet('anio') ?: $this->request->getPost('anio') ?: date('Y'));

        // Calcular fechas y métricas
        $fechaDesde = $service->getFechaDesde($idCliente, $anio) ?: "{$anio}-01-01";
        $fechaHasta = date('Y-m-d');

        // Validar que el cliente tuvo al menos una visita en el periodo
        $actaModel = new ActaVisitaModel();
        $visitasEnPeriodo = $actaModel
            ->where('id_cliente', $idCliente)
            ->where('fecha_visita >=', $fechaDesde)
            ->where('fecha_visita <=', $fechaHasta)
            ->countAllResults();

        if ($visitasEnPeriodo === 0) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'No se puede generar informe: el cliente no tiene actas de visita en el periodo ' . $fechaDesde . ' a ' . $fechaHasta,
                'cliente' => $cliente['nombre_cliente'],
                'periodo' => ['desde' => $fechaDesde, 'hasta' => $fechaHasta],
            ]);
        }
        $metricas = $service->calcularTodas($idCliente, $fechaDesde, $fechaHasta, $anio);

        // Generar resumen IA
        $resumen = '';
        try {
            $actividades = $service->recopilarActividadesPeriodo($idCliente, $fechaDesde, $fechaHasta);
            $historialEst = $this->getHistorialEstandaresCliente($idCliente, $anio);
            $historialPlan = $this->getHistorialPlanCliente($idCliente, $anio);
            $vencimientos = $this->getVencimientosCliente($idCliente);
            $capacitaciones = $service->getCapacitacionesEjecutadas($idCliente, $fechaDesde, $fechaHasta);
            $ptaPeriodo = $service->getDesglosePtaPeriodo($idCliente, $fechaDesde, $fechaHasta);
            $iaService = new IADocumentacionService();
            $prompt = $this->buildResumenPrompt($cliente['nombre_cliente'], $fechaDesde, $fechaHasta, $actividades, $metricas, $historialEst, $historialPlan, $vencimientos, $capacitaciones, $ptaPeriodo);
            $resumen = $iaService->generarContenido($prompt, 2000);
        } catch (\Exception $e) {
            $resumen = 'Resumen no disponible: ' . $e->getMessage();
        }

        // Crear informe
        $data = [
            'id_cliente'                   => $idCliente,
            'id_consultor'                 => $cliente['id_consultor'] ?? 1,
            'fecha_desde'                  => $fechaDesde,
            'fecha_hasta'                  => $fechaHasta,
            'anio'                         => $anio,
            'puntaje_anterior'             => $metricas['puntaje_anterior'],
            'puntaje_actual'               => $metricas['puntaje_actual'],
            'diferencia_neta'              => $metricas['diferencia_neta'],
            'estado_avance'                => $metricas['estado_avance'],
            'indicador_plan_trabajo'       => $metricas['indicador_plan_trabajo'],
            'indicador_capacitacion'       => $metricas['indicador_capacitacion'],
            'resumen_avance'               => $resumen,
            'actividades_abiertas'         => $metricas['actividades_abiertas'],
            'actividades_cerradas_periodo' => $metricas['actividades_cerradas_periodo'],
            'actividades_no_cerradas_pta'  => $metricas['actividades_no_cerradas_pta'] ?? '',
            'enlace_dashboard'             => $metricas['enlace_dashboard'],
            'metricas_desglose_json'       => json_encode([
                'desglose_estandares'   => $metricas['desglose_estandares'] ?? [],
                'desglose_plan_trabajo' => $metricas['desglose_plan_trabajo'] ?? [],
                'desglose_capacitacion' => $metricas['desglose_capacitacion'] ?? [],
                'desglose_pendientes'   => $metricas['desglose_pendientes'] ?? [],
                'firma_electronica'     => $metricas['firma_electronica'] ?? [],
                'documentos_sst'        => $metricas['documentos_sst'] ?? [],
                'indicadores_sst'       => $metricas['indicadores_sst'] ?? [],
                'acciones_correctivas'  => $metricas['acciones_correctivas'] ?? [],
                'actas_comite'          => $metricas['actas_comite'] ?? [],
                'inspecciones'          => $metricas['inspecciones'] ?? [],
            ], JSON_UNESCAPED_UNICODE),
            'estado'                       => 'borrador',
        ];

        $this->informeModel->insert($data);
        $informeId = $this->informeModel->getInsertID();

        // Finalizar (generar PDF)
        $pdfPath = $this->generarPdfInterno($informeId);
        if (!$pdfPath) {
            return $this->response->setJSON(['success' => false, 'error' => 'Error generando PDF', 'informe_id' => $informeId]);
        }

        $this->informeModel->update($informeId, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $informe = $this->informeModel->find($informeId);
        $this->uploadToReportes($informe, $pdfPath);

        // Enviar por email
        $envioResult = $this->enviarInterno($informe, $cliente, $pdfPath);

        return $this->response->setJSON([
            'success'     => true,
            'informe_id'  => $informeId,
            'pdf_url'     => base_url($pdfPath),
            'email'       => $envioResult,
        ]);
    }

    // ─── PRIVATE: Envío interno (reutilizable) ───
    private function enviarInterno(array $informe, array $cliente, string $pdfPath): array
    {
        if (empty($cliente['correo_cliente'])) {
            return ['success' => false, 'error' => 'Cliente sin correo'];
        }

        $sendgridApiKey = getenv('SENDGRID_API_KEY');
        if (!$sendgridApiKey) {
            return ['success' => false, 'error' => 'SENDGRID_API_KEY no configurada'];
        }

        $fullPdfPath = FCPATH . $pdfPath;
        if (!file_exists($fullPdfPath)) {
            return ['success' => false, 'error' => 'PDF no encontrado'];
        }

        $periodo = date('d/m/Y', strtotime($informe['fecha_desde'])) . ' - ' . date('d/m/Y', strtotime($informe['fecha_hasta']));
        $nombreCliente = $cliente['nombre_cliente'] ?? 'Cliente';
        $puntaje = number_format($informe['puntaje_actual'] ?? 0, 1);

        $htmlContent = "
        <div style='font-family: Segoe UI, Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #1c2437; padding: 20px; text-align: center;'>
                <h1 style='color: #bd9751; margin: 0; font-size: 20px;'>INFORME DE AVANCES SG-SST</h1>
            </div>
            <div style='padding: 25px; background: #f8f9fa;'>
                <p>Estimado(a) equipo de <strong>{$nombreCliente}</strong>,</p>
                <p>Adjunto el Informe de Avances del SG-SST periodo <strong>{$periodo}</strong>.</p>
                <p><strong>Cumplimiento:</strong> {$puntaje}% | <strong>Estado:</strong> {$informe['estado_avance']}</p>
                <p style='color: #666; font-size: 12px; margin-top: 20px;'>Generado automaticamente - Cycloid Talent SG-SST</p>
            </div>
        </div>";

        require_once ROOTPATH . 'vendor/autoload.php';

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent - SG-SST");
        $email->setSubject("Informe de Avances SG-SST - {$nombreCliente} - {$periodo}");
        $email->addTo($cliente['correo_cliente'], $nombreCliente);
        $email->addContent("text/html", $htmlContent);
        $email->addAttachment(
            base64_encode(file_get_contents($fullPdfPath)),
            'application/pdf',
            'Informe_Avances_' . str_replace(' ', '_', $nombreCliente) . '.pdf',
            'attachment'
        );

        try {
            $sendgrid = new \SendGrid($sendgridApiKey);
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                return ['success' => true, 'destinatario' => $cliente['correo_cliente']];
            }
            return ['success' => false, 'error' => 'SendGrid status: ' . $response->statusCode()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─── HISTORIAL ESTANDARES por cliente (agrupado por mes) ───
    private function getHistorialEstandaresCliente(int $idCliente, int $anio = 0): array
    {
        $model = new \App\Models\HistorialEstandaresModel();
        $builder = $model->where('id_cliente', $idCliente);
        if ($anio > 0) {
            $builder->where('YEAR(fecha_extraccion)', $anio);
        }
        $registros = $builder->orderBy('fecha_extraccion', 'ASC')->findAll();

        return $this->agruparHistorialPorMes($registros, 'porcentaje_cumplimiento');
    }

    // ─── HISTORIAL PLAN DE TRABAJO por cliente (agrupado por mes) ───
    private function getHistorialPlanCliente(int $idCliente, int $anio = 0): array
    {
        $model = new \App\Models\HistorialPlanTrabajoModel();
        $builder = $model->where('id_cliente', $idCliente);
        if ($anio > 0) {
            $builder->where('YEAR(fecha_extraccion)', $anio);
        }
        $registros = $builder->orderBy('fecha_extraccion', 'ASC')->findAll();

        return $this->agruparHistorialPorMes($registros, 'actividades_abiertas');
    }

    // ─── Agrupar registros por mes, usando el ÚLTIMO valor del mes ───
    private function agruparHistorialPorMes(array $registros, string $campo): array
    {
        $grouped = [];
        foreach ($registros as $r) {
            $mes = substr($r['fecha_extraccion'] ?? '', 0, 7); // 'YYYY-MM'
            if (!$mes) continue;
            // Siempre sobrescribe: como vienen ordenados ASC, el último gana
            $grouped[$mes] = floatval($r[$campo] ?? 0);
        }
        ksort($grouped);

        $result = [];
        foreach ($grouped as $mes => $valor) {
            $result[] = [
                'mes'      => $mes,
                'promedio' => round($valor, 2),
            ];
        }
        return $result;
    }

    // ─── Construir URL de QuickChart.io para gráfica de línea ───
    private function buildQuickChartUrl(array $historial, string $campo, string $label, string $color): string
    {
        if (empty($historial)) return '';

        $mesesNombres = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun',
                         '07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];

        $labels = [];
        $values = [];
        foreach ($historial as $item) {
            [$anio, $mo] = explode('-', $item['mes']);
            $labels[] = ($mesesNombres[$mo] ?? $mo) . ' ' . substr($anio, 2);
            $values[] = $item['promedio'];
        }

        $config = [
            'type' => 'line',
            'data' => [
                'labels'   => $labels,
                'datasets' => [[
                    'label'           => $label,
                    'data'            => $values,
                    'borderColor'     => $color,
                    'backgroundColor' => $color . '22',
                    'fill'            => true,
                    'tension'         => 0.3,
                    'pointRadius'     => 4,
                    'pointBackgroundColor' => $color,
                ]],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales'  => [
                    'y' => ['min' => 0, 'suggestedMax' => 100,
                            'ticks' => ['font' => ['size' => 9]]],
                    'x' => ['ticks' => ['font' => ['size' => 9]]],
                ],
            ],
        ];

        return 'https://quickchart.io/chart?width=380&height=160&c=' . urlencode(json_encode($config));
    }
}
