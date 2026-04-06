<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionExtintoresModel;
use App\Models\ExtintorDetalleModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Models\MantenimientoModel;
use App\Models\VencimientosMantenimientoModel;
use Dompdf\Dompdf;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\InspeccionVersionTrait;
use App\Traits\ImagenCompresionTrait;

class InspeccionExtintoresController extends BaseController
{
    use AutosaveJsonTrait;
    use InspeccionVersionTrait;
    use ImagenCompresionTrait;
    protected InspeccionExtintoresModel $inspeccionModel;
    protected ExtintorDetalleModel $detalleModel;

    /**
     * Los 12 criterios de inspección por extintor.
     * Cada uno con sus opciones válidas y valor por defecto.
     */
    public const CRITERIOS = [
        'pintura_cilindro'     => ['label' => 'Pintura de cilindro',     'opciones' => ['BUENO', 'REGULAR', 'MALO'],         'default' => 'BUENO'],
        'golpes_extintor'      => ['label' => 'Golpes en el extintor',   'opciones' => ['NO', 'SI'],                          'default' => 'NO'],
        'autoadhesivo'         => ['label' => 'Auto-adhesivo fecha/tipo','opciones' => ['BUENO', 'REGULAR', 'MALO', 'NO TIENE'], 'default' => 'BUENO'],
        'manija_transporte'    => ['label' => 'Manija de transporte',    'opciones' => ['BUENO', 'REGULAR', 'MALO'],         'default' => 'BUENO'],
        'palanca_accionamiento'=> ['label' => 'Palanca de accionamiento','opciones' => ['BUENO', 'REGULAR', 'MALO'],         'default' => 'BUENO'],
        'presion'              => ['label' => 'Presion',                 'opciones' => ['CARGADO', 'DESCARGADO', 'NO APLICA'], 'default' => 'CARGADO'],
        'manometro'            => ['label' => 'Manometro',               'opciones' => ['BUENO', 'REGULAR', 'MALO', 'NO APLICA'], 'default' => 'BUENO'],
        'boquilla'             => ['label' => 'Boquilla',                'opciones' => ['BUENO', 'REGULAR', 'MALO', 'NO TIENE'], 'default' => 'BUENO'],
        'manguera'             => ['label' => 'Manguera',                'opciones' => ['BUENO', 'REGULAR', 'MALO', 'NO APLICA'], 'default' => 'NO APLICA'],
        'ring_seguridad'       => ['label' => 'Ring o aro de seguridad', 'opciones' => ['BUENO', 'REGULAR', 'MALO'],         'default' => 'BUENO'],
        'senalizacion'         => ['label' => 'Senalizacion',            'opciones' => ['BUENO', 'REGULAR', 'NO TIENE'],     'default' => 'BUENO'],
        'soporte'              => ['label' => 'Soporte',                 'opciones' => ['BUENO', 'REGULAR', 'MALO'],         'default' => 'BUENO'],
    ];

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionExtintoresModel();
        $this->detalleModel = new ExtintorDetalleModel();
    }

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        $inspecciones = $this->inspeccionModel
            ->select('tbl_inspeccion_extintores.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_extintores.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_extintores.id_consultor', 'left')
            ->orderBy('tbl_inspeccion_extintores.fecha_inspeccion', 'DESC')
            ->findAll();

        // Contar extintores por inspección
        foreach ($inspecciones as &$insp) {
            $insp['total_detalles'] = $this->detalleModel->where('id_inspeccion', $insp['id'])->countAllResults(false);
        }

        $data = [
            'title'        => 'Inspeccion de Extintores',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/extintores/list', $data),
            'title'   => 'Extintores',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Inspeccion de Extintores',
            'inspeccion'  => null,
            'idCliente'  => $idCliente,
            'extintores' => [],
            'criterios'  => self::CRITERIOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/extintores/form', $data),
            'title'   => 'Nueva Extintores',
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_inspeccion' => 'required|valid_date'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $inspeccionData = [
            'id_cliente'                    => $this->request->getPost('id_cliente'),
            'id_consultor'                  => $userId,
            'fecha_inspeccion'              => $this->request->getPost('fecha_inspeccion'),
            'fecha_vencimiento_global'      => $this->request->getPost('fecha_vencimiento_global') ?: null,
            'numero_extintores_totales'     => (int)$this->request->getPost('numero_extintores_totales'),
            'cantidad_abc'                  => (int)$this->request->getPost('cantidad_abc'),
            'cantidad_co2'                  => (int)$this->request->getPost('cantidad_co2'),
            'cantidad_solkaflam'            => (int)$this->request->getPost('cantidad_solkaflam'),
            'cantidad_agua'                 => (int)$this->request->getPost('cantidad_agua'),
            'capacidad_libras'              => $this->request->getPost('capacidad_libras'),
            'recomendaciones_generales'     => $this->request->getPost('recomendaciones_generales'),
            'estado'                        => 'borrador',
        ];

        $this->inspeccionModel->insert($inspeccionData);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        $detailIds = $this->saveExtintores($idInspeccion);

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idInspeccion, ['detail_ids' => $detailIds]);
        }

        return redirect()->to('/inspecciones/extintores/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/extintores')->with('error', 'Inspeccion no encontrada');
        }

        $data = [
            'title'      => 'Editar Inspeccion de Extintores',
            'inspeccion'  => $inspeccion,
            'idCliente'  => $inspeccion['id_cliente'],
            'extintores' => $this->detalleModel->getByInspeccion($id),
            'criterios'  => self::CRITERIOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/extintores/form', $data),
            'title'   => 'Editar Extintores',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
            return redirect()->to('/inspecciones/extintores')->with('error', 'No se puede editar');
        }

        $this->inspeccionModel->update($id, [
            'id_cliente'                    => $this->request->getPost('id_cliente'),
            'fecha_inspeccion'              => $this->request->getPost('fecha_inspeccion'),
            'fecha_vencimiento_global'      => $this->request->getPost('fecha_vencimiento_global') ?: null,
            'numero_extintores_totales'     => (int)$this->request->getPost('numero_extintores_totales'),
            'cantidad_abc'                  => (int)$this->request->getPost('cantidad_abc'),
            'cantidad_co2'                  => (int)$this->request->getPost('cantidad_co2'),
            'cantidad_solkaflam'            => (int)$this->request->getPost('cantidad_solkaflam'),
            'cantidad_agua'                 => (int)$this->request->getPost('cantidad_agua'),
            'capacidad_libras'              => $this->request->getPost('capacidad_libras'),
            'recomendaciones_generales'     => $this->request->getPost('recomendaciones_generales'),
        ]);

        $detailIds = $this->saveExtintores($id);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id, ['detail_ids' => $detailIds]);
        }

        return redirect()->to('/inspecciones/extintores/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/extintores')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'      => 'Ver Inspeccion de Extintores',
            'inspeccion'  => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'extintores' => $this->detalleModel->getByInspeccion($id),
            'criterios'  => self::CRITERIOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/extintores/view', $data),
            'title'   => 'Ver Extintores',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/extintores')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->inspeccionModel->update($id, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        try {
            $this->syncVencimiento($inspeccion);
        } catch (\Throwable $e) {
            log_message('warning', 'syncVencimiento extintores failed: ' . $e->getMessage());
        }

        // Registrar versión en sistema documental SST
        try {
            $idDoc = $this->registrarVersionDocumento(
                (int)$inspeccion['id_cliente'],
                'inspeccion_extintores',
                'Inspecciones de Extintores',
                json_encode($inspeccion),
                "Inspección realizada el {$inspeccion['fecha_inspeccion']}",
                'IE-' . $inspeccion['id_cliente'],
                (int)date('Y', strtotime($inspeccion['fecha_inspeccion']))
            );
            if ($idDoc) {
                $this->inspeccionModel->update($id, ['id_documento_sst' => $idDoc]);
            }
        } catch (\Throwable $e) {
            log_message('warning', 'registrarVersionDocumento extintores failed: ' . $e->getMessage());
        }

        // Enviar email con PDF adjunto
        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCIÓN EXTINTORES',
            $inspeccion['fecha_inspeccion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'InspeccionExtintores'
        );
        $msg = 'Inspección finalizada y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to('/inspecciones/extintores/view/' . $id)
            ->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/extintores')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="extintores_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/extintores')->with('error', 'No encontrada');
        }

        // Eliminar fotos de extintores
        $extintores = $this->detalleModel->getByInspeccion($id);
        foreach ($extintores as $ext) {
            if (!empty($ext['foto']) && file_exists(FCPATH . $ext['foto'])) {
                unlink(FCPATH . $ext['foto']);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/extintores')->with('msg', 'Inspeccion eliminada');
    }

    public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/extintores')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->inspeccionModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/extintores/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/extintores/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCIÓN EXTINTORES',
            $inspeccion['fecha_inspeccion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'InspeccionExtintores'
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/extintores/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/extintores/view/{$id}")->with('error', $result['error']);
    }

    // ===== MÉTODOS PRIVADOS =====

    private function saveExtintores(int $idInspeccion): array
    {
        $pinturas = $this->request->getPost('ext_pintura_cilindro') ?? [];
        $extIds = $this->request->getPost('ext_id') ?? [];

        // Obtener existentes para preservar fotos (indexados por ID y por orden)
        $existentes = [];
        $existentesPorOrden = [];
        foreach ($this->detalleModel->getByInspeccion($idInspeccion) as $ext) {
            $existentes[$ext['id']] = $ext;
            $existentesPorOrden[(int)$ext['orden']] = $ext;
        }

        $this->detalleModel->deleteByInspeccion($idInspeccion);

        $dir = FCPATH . 'uploads/inspecciones/extintores/fotos/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $files = $this->request->getFiles();
        $newIds = [];

        foreach ($pinturas as $i => $pintura) {
            $existenteId = $extIds[$i] ?? null;
            $existente = $existenteId ? ($existentes[$existenteId] ?? null) : null;
            // Fallback por posición: si el ID está desactualizado (race condition autosave)
            if (!$existente) {
                $existente = $existentesPorOrden[$i + 1] ?? null;
            }

            // Foto
            $fotoPath = $existente['foto'] ?? null;
            if (isset($files['ext_foto'][$i]) && $files['ext_foto'][$i]->isValid() && !$files['ext_foto'][$i]->hasMoved()) {
                $file = $files['ext_foto'][$i];
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $this->comprimirImagen($dir . $fileName);
                $fotoPath = 'uploads/inspecciones/extintores/fotos/' . $fileName;
            }

            $this->detalleModel->insert([
                'id_inspeccion'        => $idInspeccion,
                'pintura_cilindro'     => $pintura ?? 'BUENO',
                'golpes_extintor'      => ($this->request->getPost('ext_golpes_extintor') ?? [])[$i] ?? 'NO',
                'autoadhesivo'         => ($this->request->getPost('ext_autoadhesivo') ?? [])[$i] ?? 'BUENO',
                'manija_transporte'    => ($this->request->getPost('ext_manija_transporte') ?? [])[$i] ?? 'BUENO',
                'palanca_accionamiento'=> ($this->request->getPost('ext_palanca_accionamiento') ?? [])[$i] ?? 'BUENO',
                'presion'              => ($this->request->getPost('ext_presion') ?? [])[$i] ?? 'CARGADO',
                'manometro'            => ($this->request->getPost('ext_manometro') ?? [])[$i] ?? 'BUENO',
                'boquilla'             => ($this->request->getPost('ext_boquilla') ?? [])[$i] ?? 'BUENO',
                'manguera'             => ($this->request->getPost('ext_manguera') ?? [])[$i] ?? 'NO APLICA',
                'ring_seguridad'       => ($this->request->getPost('ext_ring_seguridad') ?? [])[$i] ?? 'BUENO',
                'senalizacion'         => ($this->request->getPost('ext_senalizacion') ?? [])[$i] ?? 'BUENO',
                'soporte'              => ($this->request->getPost('ext_soporte') ?? [])[$i] ?? 'BUENO',
                'fecha_vencimiento'    => ($this->request->getPost('ext_fecha_vencimiento') ?? [])[$i] ?: null,
                'observaciones'        => ($this->request->getPost('ext_observaciones') ?? [])[$i] ?? null,
                'foto'                 => $fotoPath,
                'orden'                => $i + 1,
            ]);
            $newIds[] = $this->detalleModel->getInsertID();
        }

        return $newIds;
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);
        $extintores = $this->detalleModel->getByInspeccion($id);

        // Logo en base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        // Fotos de extintores a base64 comprimido
        foreach ($extintores as &$ext) {
            $ext['foto_base64'] = '';
            if (!empty($ext['foto'])) {
                $fotoPath = FCPATH . $ext['foto'];
                if (file_exists($fotoPath)) {
                    $ext['foto_base64'] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
        }

        $data = [
            'inspeccion'  => $inspeccion,
            'cliente'     => $cliente,
            'consultor'   => $consultor,
            'extintores'  => $extintores,
            'criterios'   => self::CRITERIOS,
            'logoBase64'  => $logoBase64,
        ];

        $html = view('inspecciones/extintores/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/extintores/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'extintores_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
    }

    private function uploadToReportes(array $inspeccion, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'];

        $existente = $reporteModel
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 12)
            ->like('observaciones', 'insp_ext_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'extintores_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION EXTINTORES - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 12,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_ext_id:' . $inspeccion['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }

    /**
     * Sincroniza fecha_vencimiento_global con tbl_vencimientos_mantenimientos.
     * UPSERT: actualiza si existe un vencimiento "sin ejecutar" para el mismo cliente+tipo,
     * crea uno nuevo si no existe.
     */
    private function syncVencimiento(array $inspeccion): void
    {
        $fechaVencimiento = $inspeccion['fecha_vencimiento_global'] ?? null;
        if (empty($fechaVencimiento)) {
            return;
        }

        $mantenimientoModel = new MantenimientoModel();
        $vencimientoModel = new VencimientosMantenimientoModel();

        // Buscar tipo de mantenimiento que contenga "extintor"
        $mantenimiento = $mantenimientoModel
            ->like('detalle_mantenimiento', 'extintor', 'both')
            ->first();

        if (!$mantenimiento) {
            log_message('warning', "syncVencimiento: No se encontro tipo de mantenimiento con keyword 'extintor' en tbl_mantenimientos");
            return;
        }

        $idMantenimiento = $mantenimiento['id_mantenimiento'];

        // Buscar vencimiento existente (sin ejecutar) para este cliente + tipo
        $existente = $vencimientoModel
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('id_mantenimiento', $idMantenimiento)
            ->where('estado_actividad', 'sin ejecutar')
            ->first();

        if ($existente) {
            $vencimientoModel->update($existente['id_vencimientos_mmttos'], [
                'fecha_vencimiento' => $fechaVencimiento,
                'id_consultor'      => $inspeccion['id_consultor'],
                'observaciones'     => 'Actualizado desde inspeccion extintores #' . $inspeccion['id'],
            ]);
        } else {
            $vencimientoModel->insert([
                'id_mantenimiento'  => $idMantenimiento,
                'id_cliente'        => $inspeccion['id_cliente'],
                'id_consultor'      => $inspeccion['id_consultor'],
                'fecha_vencimiento' => $fechaVencimiento,
                'estado_actividad'  => 'sin ejecutar',
                'observaciones'     => 'Auto-generado desde inspeccion extintores #' . $inspeccion['id'],
            ]);
        }
    }
}
