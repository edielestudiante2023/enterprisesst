<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionEppModel;
use App\Models\HallazgoEppModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

/**
 * Inspección de EPP (Elementos de Protección Personal)
 *
 * Espejo de InspeccionLocativaController pero orientado a EPP. Cada hallazgo
 * incluye campos extra: tipo_epp (texto libre) y trabajador_area (texto libre).
 */
class InspeccionEppController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;

    protected InspeccionEppModel $inspeccionModel;
    protected HallazgoEppModel $hallazgoModel;

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionEppModel();
        $this->hallazgoModel = new HallazgoEppModel();
    }

    public function list()
    {
        $inspecciones = $this->inspeccionModel
            ->select('tbl_inspeccion_epp.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_epp.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_epp.id_consultor', 'left')
            ->orderBy('tbl_inspeccion_epp.fecha_inspeccion', 'DESC')
            ->findAll();

        foreach ($inspecciones as &$insp) {
            $insp['total_hallazgos'] = $this->hallazgoModel->where('id_inspeccion', $insp['id'])->countAllResults(false);
        }

        $data = [
            'title'        => 'Inspecciones de EPP',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/inspeccion_epp/list', $data),
            'title'   => 'Inspecciones EPP',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Inspección de EPP',
            'inspeccion' => null,
            'idCliente'  => $idCliente,
            'hallazgos'  => [],
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/inspeccion_epp/form', $data),
            'title'   => 'Nueva Inspección EPP',
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            $rules = [
                'id_cliente'       => 'required|integer',
                'fecha_inspeccion' => 'required|valid_date',
            ];
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $inspeccionData = [
            'id_cliente'       => $this->request->getPost('id_cliente'),
            'id_consultor'     => $userId,
            'creado_por_tipo'  => 'consultor',
            'fecha_inspeccion' => $this->request->getPost('fecha_inspeccion'),
            'observaciones'    => $this->request->getPost('observaciones'),
            'estado'           => 'borrador',
        ];

        $this->inspeccionModel->insert($inspeccionData);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        $detailIds = $this->saveHallazgos($idInspeccion);

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idInspeccion, ['detail_ids' => $detailIds]);
        }

        return redirect()->to('/inspecciones/inspeccion-epp/edit/' . $idInspeccion)
            ->with('msg', 'Inspección guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/inspeccion-epp')->with('error', 'Inspección no encontrada');
        }

        $data = [
            'title'      => 'Editar Inspección de EPP',
            'inspeccion' => $inspeccion,
            'idCliente'  => $inspeccion['id_cliente'],
            'hallazgos'  => $this->hallazgoModel->getByInspeccion($id),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/inspeccion_epp/form', $data),
            'title'   => 'Editar Inspección EPP',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) return $this->autosaveJsonError('No encontrada', 404);
            return redirect()->to('/inspecciones/inspeccion-epp')->with('error', 'No se puede editar esta inspección');
        }

        $inspeccionData = [
            'id_cliente'       => $this->request->getPost('id_cliente'),
            'fecha_inspeccion' => $this->request->getPost('fecha_inspeccion'),
            'observaciones'    => $this->request->getPost('observaciones'),
        ];

        $this->inspeccionModel->update($id, $inspeccionData);
        $detailIds = $this->saveHallazgos($id);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }
        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id, ['detail_ids' => $detailIds]);
        }

        return redirect()->to('/inspecciones/inspeccion-epp/edit/' . $id)
            ->with('msg', 'Inspección actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/inspeccion-epp')->with('error', 'Inspección no encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'      => 'Ver Inspección de EPP',
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'hallazgos'  => $this->hallazgoModel->getByInspeccion($id),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/inspeccion_epp/view', $data),
            'title'   => 'Ver Inspección EPP',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/inspeccion-epp')->with('error', 'Inspección no encontrada');
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

        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) ($inspeccion['id_consultor'] ?? 0),
            'INSPECCIÓN DE EPP',
            $inspeccion['fecha_inspeccion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'InspeccionEpp'
        );
        $msg = 'Inspección finalizada y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to('/inspecciones/inspeccion-epp/view/' . $id)->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/inspeccion-epp')->with('error', 'Inspección no encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);

        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="inspeccion_epp_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/inspeccion-epp')->with('error', 'Inspección no encontrada');
        }
        $hallazgos = $this->hallazgoModel->getByInspeccion($id);
        foreach ($hallazgos as $h) {
            if (!empty($h['imagen']) && file_exists(FCPATH . $h['imagen'])) unlink(FCPATH . $h['imagen']);
            if (!empty($h['imagen_correccion']) && file_exists(FCPATH . $h['imagen_correccion'])) unlink(FCPATH . $h['imagen_correccion']);
        }
        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }
        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/inspeccion-epp')->with('msg', 'Inspección eliminada');
    }

    public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/inspeccion-epp')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/inspeccion-epp/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/inspeccion-epp/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) ($inspeccion['id_consultor'] ?? 0),
            'INSPECCIÓN DE EPP',
            $inspeccion['fecha_inspeccion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'InspeccionEpp'
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/inspeccion-epp/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/inspeccion-epp/view/{$id}")->with('error', $result['error']);
    }

    // ===== MÉTODOS PRIVADOS =====

    private function saveHallazgos(int $idInspeccion): array
    {
        $tiposEpp        = $this->request->getPost('hallazgo_tipo_epp') ?? [];
        $trabajadores    = $this->request->getPost('hallazgo_trabajador_area') ?? [];
        $descripciones   = $this->request->getPost('hallazgo_descripcion') ?? [];
        $estados         = $this->request->getPost('hallazgo_estado') ?? [];
        $observaciones   = $this->request->getPost('hallazgo_observaciones') ?? [];
        $hallazgoIds     = $this->request->getPost('hallazgo_id') ?? [];

        $existentes = [];
        $existentesPorOrden = [];
        foreach ($this->hallazgoModel->getByInspeccion($idInspeccion) as $h) {
            $existentes[$h['id']] = $h;
            $existentesPorOrden[(int)$h['orden']] = $h;
        }

        $this->hallazgoModel->deleteByInspeccion($idInspeccion);

        $dir = FCPATH . 'uploads/inspecciones/epp/hallazgos/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $files = $this->request->getFiles();
        $newIds = [];

        foreach ($descripciones as $i => $descripcion) {
            if (empty(trim($descripcion))) continue;

            $existenteId = $hallazgoIds[$i] ?? null;
            $existente = $existenteId ? ($existentes[$existenteId] ?? null) : null;
            if (!$existente) $existente = $existentesPorOrden[$i + 1] ?? null;

            $imagenPath = $existente['imagen'] ?? null;
            if (isset($files['hallazgo_imagen'][$i]) && $files['hallazgo_imagen'][$i]->isValid() && !$files['hallazgo_imagen'][$i]->hasMoved()) {
                $file = $files['hallazgo_imagen'][$i];
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $this->comprimirImagen($dir . $fileName);
                $imagenPath = 'uploads/inspecciones/epp/hallazgos/' . $fileName;
            }

            $correccionPath = $existente['imagen_correccion'] ?? null;
            if (isset($files['hallazgo_correccion'][$i]) && $files['hallazgo_correccion'][$i]->isValid() && !$files['hallazgo_correccion'][$i]->hasMoved()) {
                $file = $files['hallazgo_correccion'][$i];
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $this->comprimirImagen($dir . $fileName);
                $correccionPath = 'uploads/inspecciones/epp/hallazgos/' . $fileName;
            }

            $this->hallazgoModel->insert([
                'id_inspeccion'     => $idInspeccion,
                'tipo_epp'          => trim((string)($tiposEpp[$i] ?? '')) ?: null,
                'trabajador_area'   => trim((string)($trabajadores[$i] ?? '')) ?: null,
                'descripcion'       => trim($descripcion),
                'imagen'            => $imagenPath,
                'imagen_correccion' => $correccionPath,
                'fecha_hallazgo'    => $existente['fecha_hallazgo'] ?? date('Y-m-d'),
                'fecha_correccion'  => !empty($correccionPath) ? date('Y-m-d') : ($existente['fecha_correccion'] ?? null),
                'estado'            => $estados[$i] ?? 'ABIERTO',
                'observaciones'     => $observaciones[$i] ?? null,
                'orden'             => $i + 1,
            ]);
            $newIds[] = $this->hallazgoModel->getInsertID();
        }

        return $newIds;
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $inspeccion['id_consultor'] ? $consultantModel->find($inspeccion['id_consultor']) : null;
        $hallazgos = $this->hallazgoModel->getByInspeccion($id);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        foreach ($hallazgos as &$h) {
            $h['imagen_base64'] = '';
            if (!empty($h['imagen'])) {
                $fotoPath = FCPATH . $h['imagen'];
                if (file_exists($fotoPath)) {
                    $h['imagen_base64'] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
            $h['correccion_base64'] = '';
            if (!empty($h['imagen_correccion'])) {
                $fotoPath = FCPATH . $h['imagen_correccion'];
                if (file_exists($fotoPath)) {
                    $h['correccion_base64'] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
        }
        unset($h);

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => $cliente,
            'consultor'  => $consultor,
            'hallazgos'  => $hallazgos,
            'logoBase64' => $logoBase64,
        ];

        $html = view('inspecciones/inspeccion_epp/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/epp/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) mkdir(FCPATH . $pdfDir, 0755, true);

        $pdfFileName = 'inspeccion_epp_' . $id . '_' . date('Ymd_His') . '.pdf';
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

        $nitCliente = $cliente['nit_cliente'] ?? '';
        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $fileName = 'inspeccion_epp_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        copy(FCPATH . $pdfPath, $destDir . '/' . $fileName);

        $existente = $reporteModel
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('id_report_type', 24)
            ->where('id_detailreport', 19)
            ->like('observaciones', 'insp_epp_id:' . $inspeccion['id'])
            ->first();

        $data = [
            'titulo_reporte'  => 'INSPECCION DE EPP - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 19,
            'id_report_type'  => 24,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones de EPP. insp_epp_id:' . $inspeccion['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
