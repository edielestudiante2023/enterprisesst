<?php

namespace App\Controllers;

use App\Models\InspeccionEppModel;
use App\Models\HallazgoEppModel;
use App\Models\MiembroComiteModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;
use Dompdf\Dompdf;

/**
 * Inspección de EPP — flujo del miembro (PWA).
 * Transversal a TODOS los comités (no solo COPASST).
 */
class MiembroInspeccionEppController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;

    protected InspeccionEppModel $inspeccionModel;
    protected HallazgoEppModel $hallazgoModel;
    protected MiembroComiteModel $miembroModel;

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionEppModel();
        $this->hallazgoModel = new HallazgoEppModel();
        $this->miembroModel = new MiembroComiteModel();
    }

    /**
     * Devuelve el miembro logueado SIN filtro COPASST — cualquier comité activo basta.
     */
    private function getMiembroAny(): ?array
    {
        $session = session();
        $email = $session->get('email_miembro');
        $idCliente = $session->get('user_id');
        if (!$email || !$idCliente) return null;

        $miembro = $this->miembroModel->getByEmailYCliente($email, $idCliente);
        if (!$miembro) return null;

        $comites = $this->miembroModel->getComitesPorEmail($email, $idCliente);
        if (empty($comites)) return null;

        $miembro['id_cliente'] = $idCliente;
        return $miembro;
    }

    public function list()
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard')->with('error', 'No tienes acceso');

        $inspecciones = $this->inspeccionModel
            ->select('tbl_inspeccion_epp.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_epp.id_consultor', 'left')
            ->where('tbl_inspeccion_epp.id_cliente', $miembro['id_cliente'])
            ->orderBy('tbl_inspeccion_epp.fecha_inspeccion', 'DESC')
            ->findAll();

        foreach ($inspecciones as &$insp) {
            $insp['total_hallazgos'] = $this->hallazgoModel->where('id_inspeccion', $insp['id'])->countAllResults(false);
            if ($insp['creado_por_tipo'] === 'miembro' && $insp['id_miembro']) {
                $m = $this->miembroModel->find($insp['id_miembro']);
                $insp['nombre_creador'] = $m['nombre_completo'] ?? 'Miembro';
            } else {
                $insp['nombre_creador'] = $insp['nombre_consultor'] ?? 'Consultor';
            }
        }

        $clienteModel = new ClientModel();
        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/inspeccion_epp_list', [
                'inspecciones' => $inspecciones,
                'cliente' => $clienteModel->find($miembro['id_cliente']),
                'miembro' => $miembro,
            ]),
            'title'   => 'Inspecciones de EPP',
            'miembro' => $miembro,
        ]);
    }

    public function create()
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $clienteModel = new ClientModel();
        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/inspeccion_epp/form', [
                'title'      => 'Nueva Inspección de EPP',
                'inspeccion' => null,
                'hallazgos'  => [],
                'cliente'    => $clienteModel->find($miembro['id_cliente']),
                'miembro'    => $miembro,
                'idCliente'  => $miembro['id_cliente'],
                'contexto'   => 'miembro',
            ]),
            'title'   => 'Nueva Inspección EPP',
            'miembro' => $miembro,
        ]);
    }

    public function store()
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $isAutosave = $this->isAutosaveRequest();
        if (!$isAutosave) {
            if (!$this->validate(['fecha_inspeccion' => 'required|valid_date'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $this->inspeccionModel->insert([
            'id_cliente'       => $miembro['id_cliente'],
            'id_consultor'     => null,
            'id_miembro'       => $miembro['id_miembro'],
            'creado_por_tipo'  => 'miembro',
            'fecha_inspeccion' => $this->request->getPost('fecha_inspeccion'),
            'observaciones'    => $this->request->getPost('observaciones'),
            'estado'           => 'borrador',
        ]);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        $detailIds = $this->saveHallazgos($idInspeccion);

        if ($isAutosave) return $this->autosaveJsonSuccess($idInspeccion, ['detail_ids' => $detailIds]);
        return redirect()->to('/miembro/inspecciones/inspeccion-epp/edit/' . $idInspeccion)
            ->with('msg', 'Inspección guardada como borrador');
    }

    public function edit($id)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/inspecciones/inspeccion-epp')->with('error', 'Inspección no encontrada');
        }
        if ($inspeccion['estado'] !== 'borrador') {
            return redirect()->to('/miembro/inspecciones/inspeccion-epp/view/' . $id);
        }
        if ($inspeccion['creado_por_tipo'] === 'miembro' && (int)$inspeccion['id_miembro'] !== (int)$miembro['id_miembro']) {
            return redirect()->to('/miembro/inspecciones/inspeccion-epp')->with('error', 'Solo puedes editar tus propias inspecciones');
        }

        $clienteModel = new ClientModel();
        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/inspeccion_epp/form', [
                'title'      => 'Editar Inspección de EPP',
                'inspeccion' => $inspeccion,
                'hallazgos'  => $this->hallazgoModel->getByInspeccion($id),
                'cliente'    => $clienteModel->find($miembro['id_cliente']),
                'miembro'    => $miembro,
                'idCliente'  => $miembro['id_cliente'],
                'contexto'   => 'miembro',
            ]),
            'title'   => 'Editar Inspección EPP',
            'miembro' => $miembro,
        ]);
    }

    public function update($id)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$miembro['id_cliente'] || $inspeccion['estado'] !== 'borrador') {
            if ($this->isAutosaveRequest()) return $this->autosaveJsonError('No editable', 404);
            return redirect()->to('/miembro/inspecciones/inspeccion-epp')->with('error', 'No se puede editar');
        }

        $this->inspeccionModel->update($id, [
            'fecha_inspeccion' => $this->request->getPost('fecha_inspeccion'),
            'observaciones'    => $this->request->getPost('observaciones'),
        ]);

        $detailIds = $this->saveHallazgos($id);

        if ($this->request->getPost('finalizar')) return $this->finalizar($id);
        if ($this->isAutosaveRequest()) return $this->autosaveJsonSuccess((int)$id, ['detail_ids' => $detailIds]);

        return redirect()->to('/miembro/inspecciones/inspeccion-epp/edit/' . $id)
            ->with('msg', 'Inspección actualizada');
    }

    public function view($id)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/inspecciones/inspeccion-epp')->with('error', 'Inspección no encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $realizadoPor = null;
        if ($inspeccion['creado_por_tipo'] === 'miembro' && $inspeccion['id_miembro']) {
            $m = $this->miembroModel->find($inspeccion['id_miembro']);
            $realizadoPor = $m['nombre_completo'] ?? 'Miembro';
        }

        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/inspeccion_epp_view', [
                'title'        => 'Ver Inspección de EPP',
                'inspeccion'   => $inspeccion,
                'cliente'      => $clientModel->find($inspeccion['id_cliente']),
                'consultor'    => $inspeccion['id_consultor'] ? $consultantModel->find($inspeccion['id_consultor']) : null,
                'realizadoPor' => $realizadoPor,
                'hallazgos'    => $this->hallazgoModel->getByInspeccion($id),
            ]),
            'title'   => 'Ver Inspección EPP',
            'miembro' => $miembro,
        ]);
    }

    public function finalizar($id)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/inspecciones/inspeccion-epp')->with('error', 'Inspección no encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) return redirect()->back()->with('error', 'Error al generar PDF');

        $this->inspeccionModel->update($id, ['estado' => 'completo', 'ruta_pdf' => $pdfPath]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to('/miembro/inspecciones/inspeccion-epp/view/' . $id)
            ->with('msg', 'Inspección finalizada y PDF generado.');
    }

    public function generatePdf($id)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/inspecciones/inspeccion-epp');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);

        $fullPath = FCPATH . $pdfPath;
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="inspeccion_epp_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    // ===== PRIVADOS =====

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
            if (!empty($h['imagen']) && file_exists(FCPATH . $h['imagen'])) {
                $h['imagen_base64'] = $this->fotoABase64ParaPdf(FCPATH . $h['imagen']);
            }
            $h['correccion_base64'] = '';
            if (!empty($h['imagen_correccion']) && file_exists(FCPATH . $h['imagen_correccion'])) {
                $h['correccion_base64'] = $this->fotoABase64ParaPdf(FCPATH . $h['imagen_correccion']);
            }
        }
        unset($h);

        $html = view('inspecciones/inspeccion_epp/pdf', [
            'inspeccion' => $inspeccion,
            'cliente'    => $cliente,
            'consultor'  => $consultor,
            'hallazgos'  => $hallazgos,
            'logoBase64' => $logoBase64,
        ]);

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
            'observaciones'   => 'Generado por miembro. insp_epp_id:' . $inspeccion['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) return $reporteModel->update($existente['id_reporte'], $data);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
