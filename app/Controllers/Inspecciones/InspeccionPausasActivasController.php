<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\PausaActivaModel;
use App\Models\PausaActivaRegistroModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

class InspeccionPausasActivasController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;

    protected PausaActivaModel $pausaModel;
    protected PausaActivaRegistroModel $registroModel;

    public function __construct()
    {
        $this->pausaModel = new PausaActivaModel();
        $this->registroModel = new PausaActivaRegistroModel();
    }

    public function list()
    {
        $inspecciones = $this->pausaModel
            ->select('tbl_pausas_activas.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_pausas_activas.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_pausas_activas.id_consultor', 'left')
            ->orderBy('tbl_pausas_activas.fecha_actividad', 'DESC')
            ->findAll();

        foreach ($inspecciones as &$insp) {
            $insp['total_registros'] = $this->registroModel->where('id_pausa', $insp['id'])->countAllResults(false);
        }

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/pausas_activas/list', ['inspecciones' => $inspecciones]),
            'title' => 'Pausas Activas',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title' => 'Nueva Pausa Activa',
            'inspeccion' => null,
            'registros' => [],
            'idCliente' => $idCliente,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/pausas_activas/form', $data),
            'title' => 'Nueva Pausa Activa',
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_actividad' => 'required|valid_date'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $data = [
            'id_cliente'      => $this->request->getPost('id_cliente'),
            'id_consultor'    => $userId,
            'creado_por_tipo' => 'consultor',
            'fecha_actividad' => $this->request->getPost('fecha_actividad'),
            'observaciones'   => $this->request->getPost('observaciones'),
            'estado'          => 'borrador',
        ];

        $this->pausaModel->insert($data);
        $id = $this->pausaModel->getInsertID();
        $detailIds = $this->saveRegistros($id);

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($id, ['detail_ids' => $detailIds]);
        }

        return redirect()->to('/inspecciones/pausas-activas/edit/' . $id)
            ->with('msg', 'Pausa activa guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->pausaModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/pausas-activas')->with('error', 'No encontrada');
        }

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/pausas_activas/form', [
                'title' => 'Editar Pausa Activa',
                'inspeccion' => $inspeccion,
                'registros' => $this->registroModel->getByPausa($id),
                'idCliente' => $inspeccion['id_cliente'],
            ]),
            'title' => 'Editar Pausa Activa',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->pausaModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) return $this->autosaveJsonError('No encontrada', 404);
            return redirect()->to('/inspecciones/pausas-activas')->with('error', 'No encontrada');
        }

        $this->pausaModel->update($id, [
            'id_cliente'      => $this->request->getPost('id_cliente'),
            'fecha_actividad' => $this->request->getPost('fecha_actividad'),
            'observaciones'   => $this->request->getPost('observaciones'),
        ]);

        $detailIds = $this->saveRegistros($id);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id, ['detail_ids' => $detailIds]);
        }

        return redirect()->to('/inspecciones/pausas-activas/edit/' . $id)->with('msg', 'Actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->pausaModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/pausas-activas')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/pausas_activas/view', [
                'title' => 'Ver Pausa Activa',
                'inspeccion' => $inspeccion,
                'cliente' => $clientModel->find($inspeccion['id_cliente']),
                'consultor' => $inspeccion['id_consultor'] ? $consultantModel->find($inspeccion['id_consultor']) : null,
                'realizadoPor' => $this->getNombreCreador($inspeccion),
                'registros' => $this->registroModel->getByPausa($id),
            ]),
            'title' => 'Ver Pausa Activa',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->pausaModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/pausas-activas')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->pausaModel->update($id, ['estado' => 'completo', 'ruta_pdf' => $pdfPath]);

        $inspeccion = $this->pausaModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        $emailResult = InspeccionEmailNotifier::enviar(
            (int)$inspeccion['id_cliente'],
            (int)($inspeccion['id_consultor'] ?? 0),
            'PAUSAS ACTIVAS',
            $inspeccion['fecha_actividad'],
            $pdfPath,
            (int)$inspeccion['id'],
            'PausasActivas'
        );

        $msg = 'Pausa activa finalizada y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        }

        return redirect()->to('/inspecciones/pausas-activas/view/' . $id)->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->pausaModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/pausas-activas')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->pausaModel->update($id, ['ruta_pdf' => $pdfPath]);

        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="pausas_activas_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->pausaModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/pausas-activas')->with('error', 'No encontrada');
        }

        $registros = $this->registroModel->getByPausa($id);
        foreach ($registros as $r) {
            if (!empty($r['imagen']) && file_exists(FCPATH . $r['imagen'])) {
                unlink(FCPATH . $r['imagen']);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->pausaModel->delete($id);
        return redirect()->to('/inspecciones/pausas-activas')->with('msg', 'Eliminada');
    }

    // ===== PRIVADOS =====

    private function getNombreCreador(array $inspeccion): ?string
    {
        if ($inspeccion['creado_por_tipo'] === 'miembro' && $inspeccion['id_miembro']) {
            $m = (new \App\Models\MiembroComiteModel())->find($inspeccion['id_miembro']);
            return $m['nombre_completo'] ?? 'Miembro COPASST';
        }
        return null;
    }

    private function saveRegistros(int $idPausa): array
    {
        $tipos = $this->request->getPost('registro_tipo') ?? [];
        $registroIds = $this->request->getPost('registro_id') ?? [];

        $existentes = [];
        foreach ($this->registroModel->getByPausa($idPausa) as $r) {
            $existentes[$r['id']] = $r;
        }

        $this->registroModel->deleteByPausa($idPausa);

        $dir = FCPATH . 'uploads/inspecciones/pausas_activas/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $files = $this->request->getFiles();
        $newIds = [];

        foreach ($tipos as $i => $tipo) {
            if (empty(trim($tipo))) continue;

            $existenteId = $registroIds[$i] ?? null;
            $existente = $existenteId ? ($existentes[$existenteId] ?? null) : null;

            $imagenPath = $existente['imagen'] ?? null;
            if (isset($files['registro_imagen'][$i]) && $files['registro_imagen'][$i]->isValid() && !$files['registro_imagen'][$i]->hasMoved()) {
                $file = $files['registro_imagen'][$i];
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $this->comprimirImagen($dir . $fileName);
                $imagenPath = 'uploads/inspecciones/pausas_activas/' . $fileName;
            }

            $this->registroModel->insert([
                'id_pausa'   => $idPausa,
                'tipo_pausa' => trim($tipo),
                'imagen'     => $imagenPath,
                'orden'      => $i + 1,
            ]);
            $newIds[] = $this->registroModel->getInsertID();
        }

        return $newIds;
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->pausaModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $inspeccion['id_consultor'] ? $consultantModel->find($inspeccion['id_consultor']) : null;
        $registros = $this->registroModel->getByPausa($id);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        foreach ($registros as &$r) {
            $r['imagen_base64'] = '';
            if (!empty($r['imagen'])) {
                $fotoPath = FCPATH . $r['imagen'];
                if (file_exists($fotoPath)) {
                    $r['imagen_base64'] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
        }

        $data = [
            'inspeccion'   => $inspeccion,
            'cliente'      => $cliente,
            'consultor'    => $consultor,
            'realizadoPor' => $this->getNombreCreador($inspeccion),
            'registros'    => $registros,
            'logoBase64'   => $logoBase64,
        ];

        $html = view('inspecciones/pausas_activas/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/pausas_activas/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'pausas_activas_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 10)
            ->like('observaciones', 'pausas_activas_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $fileName = 'pausas_activas_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        copy(FCPATH . $pdfPath, $destDir . '/' . $fileName);

        $data = [
            'titulo_reporte'  => 'PAUSAS ACTIVAS - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_actividad'],
            'id_detailreport' => 10,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado desde modulo pausas activas. pausas_activas_id:' . $inspeccion['id'],
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
