<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionLocativaModel;
use App\Models\HallazgoLocativoModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class InspeccionLocativaController extends BaseController
{
    protected InspeccionLocativaModel $inspeccionModel;
    protected HallazgoLocativoModel $hallazgoModel;

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionLocativaModel();
        $this->hallazgoModel = new HallazgoLocativoModel();
    }

    /**
     * Listado de inspecciones del consultor
     */
    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->inspeccionModel
                ->select('tbl_inspeccion_locativa.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_locativa.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_locativa.id_consultor', 'left')
                ->orderBy('tbl_inspeccion_locativa.fecha_inspeccion', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->inspeccionModel->getByConsultor($userId);
        }

        // Contar hallazgos por inspección
        foreach ($inspecciones as &$insp) {
            $insp['total_hallazgos'] = $this->hallazgoModel->where('id_inspeccion', $insp['id'])->countAllResults(false);
        }

        $data = [
            'title'        => 'Inspecciones Locativas',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/inspeccion_locativa/list', $data),
            'title'   => 'Locativas',
        ]);
    }

    /**
     * Formulario de creación
     */
    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Inspección Locativa',
            'inspeccion'  => null,
            'idCliente'  => $idCliente,
            'hallazgos'  => [],
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/inspeccion_locativa/form', $data),
            'title'   => 'Nueva Inspección Locativa',
        ]);
    }

    /**
     * Guardar nueva inspección (borrador)
     */
    public function store()
    {
        $userId = session()->get('user_id');

        $rules = [
            'id_cliente'       => 'required|integer',
            'fecha_inspeccion'  => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $inspeccionData = [
            'id_cliente'       => $this->request->getPost('id_cliente'),
            'id_consultor'     => $userId,
            'fecha_inspeccion'  => $this->request->getPost('fecha_inspeccion'),
            'observaciones'    => $this->request->getPost('observaciones'),
            'estado'           => 'borrador',
        ];

        $this->inspeccionModel->insert($inspeccionData);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        $this->saveHallazgos($idInspeccion);

        return redirect()->to('/inspecciones/inspeccion-locativa/edit/' . $idInspeccion)
            ->with('msg', 'Inspección guardada como borrador');
    }

    /**
     * Formulario de edición
     */
    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/inspeccion-locativa')->with('error', 'Inspección no encontrada');
        }

        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/inspeccion-locativa/view/' . $id);
        }

        $data = [
            'title'      => 'Editar Inspección Locativa',
            'inspeccion'  => $inspeccion,
            'idCliente'  => $inspeccion['id_cliente'],
            'hallazgos'  => $this->hallazgoModel->getByInspeccion($id),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/inspeccion_locativa/form', $data),
            'title'   => 'Editar Inspección',
        ]);
    }

    /**
     * Actualizar inspección existente
     */
    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/inspeccion-locativa')->with('error', 'No se puede editar esta inspección');
        }

        $inspeccionData = [
            'id_cliente'       => $this->request->getPost('id_cliente'),
            'fecha_inspeccion'  => $this->request->getPost('fecha_inspeccion'),
            'observaciones'    => $this->request->getPost('observaciones'),
        ];

        $this->inspeccionModel->update($id, $inspeccionData);
        $this->saveHallazgos($id);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        return redirect()->to('/inspecciones/inspeccion-locativa/edit/' . $id)
            ->with('msg', 'Inspección actualizada');
    }

    /**
     * Vista de solo lectura
     */
    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/inspeccion-locativa')->with('error', 'Inspección no encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'      => 'Ver Inspección Locativa',
            'inspeccion'  => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'hallazgos'  => $this->hallazgoModel->getByInspeccion($id),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/inspeccion_locativa/view', $data),
            'title'   => 'Ver Inspección',
        ]);
    }

    /**
     * Finalizar: genera PDF + upload a reportes + estado completo
     */
    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/inspeccion-locativa')->with('error', 'Inspección no encontrada');
        }

        // Generar PDF
        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        // Actualizar estado y ruta PDF
        $this->inspeccionModel->update($id, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        // Subir a reportes
        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to('/inspecciones/inspeccion-locativa/view/' . $id)
            ->with('msg', 'Inspección finalizada y PDF generado');
    }

    /**
     * Servir PDF inline
     */
    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/inspeccion-locativa')->with('error', 'Inspección no encontrada');
        }

        // Siempre regenerar desde el template
        $pdfPath = $this->generarPdfInterno($id);

        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="inspeccion_locativa_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    /**
     * Eliminar inspección (solo borradores)
     */
    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/inspeccion-locativa')->with('error', 'Inspección no encontrada');
        }

        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/inspeccion-locativa')->with('error', 'No se puede eliminar una inspección completa');
        }

        // Eliminar fotos de hallazgos del disco
        $hallazgos = $this->hallazgoModel->getByInspeccion($id);
        foreach ($hallazgos as $h) {
            if (!empty($h['imagen']) && file_exists(FCPATH . $h['imagen'])) {
                unlink(FCPATH . $h['imagen']);
            }
            if (!empty($h['imagen_correccion']) && file_exists(FCPATH . $h['imagen_correccion'])) {
                unlink(FCPATH . $h['imagen_correccion']);
            }
        }

        // Eliminar PDF del disco
        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        // Eliminar (hallazgos se borran por CASCADE)
        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/inspeccion-locativa')->with('msg', 'Inspección eliminada');
    }

    // ===== MÉTODOS PRIVADOS =====

    /**
     * Guardar hallazgos desde POST con fotos
     */
    private function saveHallazgos(int $idInspeccion): void
    {
        $descripciones = $this->request->getPost('hallazgo_descripcion') ?? [];
        $estados = $this->request->getPost('hallazgo_estado') ?? [];
        $observaciones = $this->request->getPost('hallazgo_observaciones') ?? [];
        $hallazgoIds = $this->request->getPost('hallazgo_id') ?? [];

        // Obtener hallazgos existentes para preservar fotos no resubidas
        $existentes = [];
        foreach ($this->hallazgoModel->getByInspeccion($idInspeccion) as $h) {
            $existentes[$h['id']] = $h;
        }

        // Eliminar hallazgos anteriores
        $this->hallazgoModel->deleteByInspeccion($idInspeccion);

        $dir = FCPATH . 'uploads/inspecciones/locativas/hallazgos/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $files = $this->request->getFiles();

        foreach ($descripciones as $i => $descripcion) {
            if (empty(trim($descripcion))) {
                continue;
            }

            $existenteId = $hallazgoIds[$i] ?? null;
            $existente = $existenteId ? ($existentes[$existenteId] ?? null) : null;

            // Foto del hallazgo
            $imagenPath = $existente['imagen'] ?? null;
            if (isset($files['hallazgo_imagen'][$i]) && $files['hallazgo_imagen'][$i]->isValid() && !$files['hallazgo_imagen'][$i]->hasMoved()) {
                $file = $files['hallazgo_imagen'][$i];
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $imagenPath = 'uploads/inspecciones/locativas/hallazgos/' . $fileName;
            }

            // Foto de corrección
            $correccionPath = $existente['imagen_correccion'] ?? null;
            if (isset($files['hallazgo_correccion'][$i]) && $files['hallazgo_correccion'][$i]->isValid() && !$files['hallazgo_correccion'][$i]->hasMoved()) {
                $file = $files['hallazgo_correccion'][$i];
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $correccionPath = 'uploads/inspecciones/locativas/hallazgos/' . $fileName;
            }

            $this->hallazgoModel->insert([
                'id_inspeccion'     => $idInspeccion,
                'descripcion'       => trim($descripcion),
                'imagen'            => $imagenPath,
                'imagen_correccion' => $correccionPath,
                'fecha_hallazgo'    => $existente['fecha_hallazgo'] ?? date('Y-m-d'),
                'fecha_correccion'  => !empty($correccionPath) ? date('Y-m-d') : ($existente['fecha_correccion'] ?? null),
                'estado'            => $estados[$i] ?? 'ABIERTO',
                'observaciones'     => $observaciones[$i] ?? null,
                'orden'             => $i + 1,
            ]);
        }
    }

    /**
     * Generar PDF con DOMPDF
     */
    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);
        $hallazgos = $this->hallazgoModel->getByInspeccion($id);

        // Logo del cliente en base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        // Convertir fotos de hallazgos a base64
        foreach ($hallazgos as &$h) {
            $h['imagen_base64'] = '';
            if (!empty($h['imagen'])) {
                $fotoPath = FCPATH . $h['imagen'];
                if (file_exists($fotoPath)) {
                    $mime = mime_content_type($fotoPath);
                    $h['imagen_base64'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fotoPath));
                }
            }
            $h['correccion_base64'] = '';
            if (!empty($h['imagen_correccion'])) {
                $fotoPath = FCPATH . $h['imagen_correccion'];
                if (file_exists($fotoPath)) {
                    $mime = mime_content_type($fotoPath);
                    $h['correccion_base64'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fotoPath));
                }
            }
        }

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => $cliente,
            'consultor'  => $consultor,
            'hallazgos'  => $hallazgos,
            'logoBase64' => $logoBase64,
        ];

        $html = view('inspecciones/inspeccion_locativa/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Guardar PDF
        $pdfDir = 'uploads/inspecciones/locativas/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'inspeccion_locativa_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        // Eliminar PDF anterior
        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
    }

    /**
     * Registra el PDF en tbl_reporte
     */
    private function uploadToReportes(array $inspeccion, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();

        $cliente = $clientModel->find($inspeccion['id_cliente']);
        if (!$cliente) {
            return false;
        }

        $nitCliente = $cliente['nit_cliente'];

        // Verificar si ya existe reporte para esta inspección
        $existente = $reporteModel
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 10)
            ->like('observaciones', 'insp_locativa_id:' . $inspeccion['id'])
            ->first();

        // Copiar a uploads/{nit_cliente}/
        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'inspeccion_locativa_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION LOCATIVA - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 10,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_locativa_id:' . $inspeccion['id'],
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
