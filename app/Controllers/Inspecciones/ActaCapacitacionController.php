<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ActaCapacitacionModel;
use App\Models\ActaCapacitacionAsistenteModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Models\MiembroComiteModel;
use App\Traits\AutosaveJsonTrait;
use Dompdf\Dompdf;

/**
 * Acta de Capacitación — flujo del consultor + endpoints públicos (firma remota).
 */
class ActaCapacitacionController extends BaseController
{
    use AutosaveJsonTrait;

    protected ActaCapacitacionModel $actaModel;
    protected ActaCapacitacionAsistenteModel $asistenteModel;

    public function __construct()
    {
        $this->actaModel = new ActaCapacitacionModel();
        $this->asistenteModel = new ActaCapacitacionAsistenteModel();
    }

    public function list()
    {
        $actas = $this->actaModel
            ->select('tbl_acta_capacitacion.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_acta_capacitacion.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_acta_capacitacion.id_consultor', 'left')
            ->orderBy('tbl_acta_capacitacion.fecha_capacitacion', 'DESC')
            ->findAll();

        foreach ($actas as &$a) {
            $a['total_asistentes'] = $this->asistenteModel
                ->where('id_acta_capacitacion', $a['id'])->countAllResults(false);
            $a['total_firmados'] = $this->asistenteModel
                ->where('id_acta_capacitacion', $a['id'])
                ->where('firma_path IS NOT NULL', null, false)->countAllResults(false);
        }

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_capacitacion/list', ['actas' => $actas]),
            'title'   => 'Actas de Capacitación',
        ]);
    }

    public function create($idCliente = null)
    {
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_capacitacion/form', [
                'title'      => 'Nueva Acta de Capacitación',
                'acta'       => null,
                'asistentes' => [],
                'idCliente'  => $idCliente,
                'contexto'   => 'consultor',
            ]),
            'title' => 'Nueva Acta de Capacitación',
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            if (!$this->validate([
                'id_cliente'         => 'required|integer',
                'fecha_capacitacion' => 'required|valid_date',
                'tema'               => 'required|min_length[3]',
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $idComite = $this->request->getPost('id_comite');
        $this->actaModel->insert([
            'id_cliente'           => $this->request->getPost('id_cliente'),
            'id_comite'            => $idComite ? (int)$idComite : null,
            'creado_por_tipo'      => 'consultor',
            'id_consultor'         => $userId,
            'tema'                 => $this->request->getPost('tema'),
            'fecha_capacitacion'   => $this->request->getPost('fecha_capacitacion'),
            'hora_inicio'          => $this->request->getPost('hora_inicio') ?: null,
            'hora_fin'             => $this->request->getPost('hora_fin') ?: null,
            'dictada_por'          => $this->request->getPost('dictada_por') ?: 'ARL',
            'nombre_capacitador'   => $this->request->getPost('nombre_capacitador'),
            'entidad_capacitadora' => $this->request->getPost('entidad_capacitadora'),
            'modalidad'            => $this->request->getPost('modalidad') ?: 'virtual',
            'enlace_grabacion'     => $this->request->getPost('enlace_grabacion'),
            'objetivos'            => $this->request->getPost('objetivos'),
            'contenido'            => $this->request->getPost('contenido'),
            'observaciones'        => $this->request->getPost('observaciones'),
            'estado'               => 'borrador',
        ]);
        $idActa = $this->actaModel->getInsertID();

        $this->saveAsistentes($idActa);

        if ($isAutosave) return $this->autosaveJsonSuccess($idActa);
        return redirect()->to('/inspecciones/acta-capacitacion/edit/' . $idActa)
            ->with('msg', 'Guardada como borrador');
    }

    public function edit($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) return redirect()->to('/inspecciones/acta-capacitacion')->with('error', 'No encontrada');
        if ($acta['estado'] === 'completo') return redirect()->to('/inspecciones/acta-capacitacion/view/' . $id);

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_capacitacion/form', [
                'title'      => 'Editar Acta de Capacitación',
                'acta'       => $acta,
                'asistentes' => $this->asistenteModel->getByActa((int)$id),
                'idCliente'  => $acta['id_cliente'],
                'contexto'   => 'consultor',
            ]),
            'title' => 'Editar Acta de Capacitación',
        ]);
    }

    public function update($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            if ($this->isAutosaveRequest()) return $this->autosaveJsonError('No encontrada', 404);
            return redirect()->to('/inspecciones/acta-capacitacion');
        }
        if ($acta['estado'] === 'completo') {
            if ($this->isAutosaveRequest()) return $this->autosaveJsonError('No editable', 400);
            return redirect()->to('/inspecciones/acta-capacitacion/view/' . $id);
        }

        $idComite = $this->request->getPost('id_comite');
        $this->actaModel->update($id, [
            'id_cliente'           => $this->request->getPost('id_cliente'),
            'id_comite'            => $idComite ? (int)$idComite : null,
            'tema'                 => $this->request->getPost('tema'),
            'fecha_capacitacion'   => $this->request->getPost('fecha_capacitacion'),
            'hora_inicio'          => $this->request->getPost('hora_inicio') ?: null,
            'hora_fin'             => $this->request->getPost('hora_fin') ?: null,
            'dictada_por'          => $this->request->getPost('dictada_por') ?: 'ARL',
            'nombre_capacitador'   => $this->request->getPost('nombre_capacitador'),
            'entidad_capacitadora' => $this->request->getPost('entidad_capacitadora'),
            'modalidad'            => $this->request->getPost('modalidad') ?: 'virtual',
            'enlace_grabacion'     => $this->request->getPost('enlace_grabacion'),
            'objetivos'            => $this->request->getPost('objetivos'),
            'contenido'            => $this->request->getPost('contenido'),
            'observaciones'        => $this->request->getPost('observaciones'),
        ]);

        $this->saveAsistentes((int)$id);

        if ($this->request->getPost('finalizar')) return $this->finalizar($id);
        if ($this->isAutosaveRequest()) return $this->autosaveJsonSuccess((int)$id);

        return redirect()->to('/inspecciones/acta-capacitacion/edit/' . $id)->with('msg', 'Actualizada');
    }

    public function view($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) return redirect()->to('/inspecciones/acta-capacitacion');

        $clienteModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $miembroModel = new MiembroComiteModel();

        $realizadoPor = null;
        if ($acta['creado_por_tipo'] === 'miembro' && $acta['id_miembro']) {
            $m = $miembroModel->find($acta['id_miembro']);
            $realizadoPor = $m['nombre_completo'] ?? 'Miembro';
        }

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_capacitacion/view', [
                'acta'         => $acta,
                'cliente'      => $clienteModel->find($acta['id_cliente']),
                'consultor'    => $acta['id_consultor'] ? $consultantModel->find($acta['id_consultor']) : null,
                'realizadoPor' => $realizadoPor,
                'asistentes'   => $this->asistenteModel->getByActa((int)$id),
                'contexto'     => 'consultor',
            ]),
            'title' => 'Ver Acta de Capacitación',
        ]);
    }

    /**
     * AJAX (auth): genera token de firma remota para un asistente.
     */
    public function generarTokenFirma(int $idAsistente)
    {
        $asistente = $this->asistenteModel->find($idAsistente);
        if (!$asistente) return $this->response->setJSON(['success' => false, 'error' => 'Asistente no encontrado']);

        $acta = $this->actaModel->find($asistente['id_acta_capacitacion']);
        if (!$acta) return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        if (!empty($asistente['firma_path'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Este asistente ya firmó']);
        }

        $token = bin2hex(random_bytes(32));
        $this->asistenteModel->update($idAsistente, [
            'token_firma'      => $token,
            'token_expiracion' => date('Y-m-d H:i:s', strtotime('+7 days')),
        ]);

        $url = base_url("acta-capacitacion/firmar-remoto/{$token}");
        return $this->response->setJSON(['success' => true, 'url' => $url, 'nombre' => $asistente['nombre_completo']]);
    }

    public function finalizar($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) return redirect()->to('/inspecciones/acta-capacitacion');

        $pdfPath = $this->generarPdfInterno((int)$id);
        if (!$pdfPath) return redirect()->back()->with('error', 'Error al generar PDF');

        $this->actaModel->update($id, ['estado' => 'completo', 'ruta_pdf' => $pdfPath]);
        $acta = $this->actaModel->find($id);
        $this->uploadToReportes($acta, $pdfPath);

        return redirect()->to('/inspecciones/acta-capacitacion/view/' . $id)->with('msg', 'Acta finalizada.');
    }

    public function generatePdf($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) return redirect()->to('/inspecciones/acta-capacitacion');

        $pdfPath = $this->generarPdfInterno((int)$id);
        $fullPath = FCPATH . $pdfPath;

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="acta_capacitacion_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) return redirect()->to('/inspecciones/acta-capacitacion');
        if ($acta['estado'] === 'completo') {
            return redirect()->back()->with('error', 'No se pueden borrar actas finalizadas');
        }
        $this->actaModel->delete($id);
        return redirect()->to('/inspecciones/acta-capacitacion')->with('msg', 'Acta eliminada');
    }

    // ============================================================
    // ENDPOINTS PÚBLICOS (sin auth — token es la autenticación)
    // ============================================================

    /**
     * Página pública: canvas de firma para el asistente.
     */
    public function firmarRemoto(string $token)
    {
        $asistente = $this->asistenteModel->getByToken($token);
        if (!$asistente) {
            return view('inspecciones/acta_capacitacion/firma_remota_error', [
                'mensaje' => 'Este enlace no es válido o ya fue usado.'
            ]);
        }
        if ($asistente['token_expiracion'] && strtotime($asistente['token_expiracion']) < time()) {
            return view('inspecciones/acta_capacitacion/firma_remota_error', [
                'mensaje' => 'Este enlace ha expirado (7 días). Pida uno nuevo al organizador.'
            ]);
        }
        if (!empty($asistente['firma_path'])) {
            return view('inspecciones/acta_capacitacion/firma_remota_error', [
                'mensaje' => 'Esta firma ya fue registrada.'
            ]);
        }

        $acta = $this->actaModel->find($asistente['id_acta_capacitacion']);
        if (!$acta) {
            return view('inspecciones/acta_capacitacion/firma_remota_error', [
                'mensaje' => 'Acta no encontrada.'
            ]);
        }

        $cliente = (new ClientModel())->find($acta['id_cliente']);
        $todosAsistentes = $this->asistenteModel->getByActa((int)$acta['id']);

        return view('inspecciones/acta_capacitacion/firma_remota', [
            'token'      => $token,
            'acta'       => $acta,
            'cliente'    => $cliente,
            'asistente'  => $asistente,
            'asistentes' => $todosAsistentes,
        ]);
    }

    /**
     * AJAX público: recibe y guarda la firma remota.
     */
    public function procesarFirmaRemota()
    {
        $token       = $this->request->getPost('token');
        $firmaBase64 = $this->request->getPost('firma_imagen');

        if (!$token || !$firmaBase64) {
            return $this->response->setJSON(['success' => false, 'error' => 'Datos incompletos']);
        }

        $asistente = $this->asistenteModel->getByToken($token);
        if (!$asistente) {
            return $this->response->setJSON(['success' => false, 'error' => 'Enlace inválido']);
        }
        if ($asistente['token_expiracion'] && strtotime($asistente['token_expiracion']) < time()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Enlace expirado']);
        }
        if (!empty($asistente['firma_path'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Ya firmado']);
        }

        $firmaData    = explode(',', $firmaBase64);
        $firmaDecoded = base64_decode(end($firmaData));
        if ($firmaDecoded === false) {
            return $this->response->setJSON(['success' => false, 'error' => 'Firma inválida']);
        }

        $dir = FCPATH . 'uploads/inspecciones/firmas_capacitacion/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $nombreArchivo = 'firma_cap_' . $asistente['id'] . '_' . time() . '.png';
        file_put_contents($dir . $nombreArchivo, $firmaDecoded);

        $this->asistenteModel->update($asistente['id'], [
            'firma_path'       => 'uploads/inspecciones/firmas_capacitacion/' . $nombreArchivo,
            'firmado_at'       => date('Y-m-d H:i:s'),
            'token_firma'      => null,
            'token_expiracion' => null,
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    // ===== PRIVADOS =====

    private function saveAsistentes(int $idActa): void
    {
        $ids       = $this->request->getPost('asistente_id') ?? [];
        $nombres   = $this->request->getPost('asistente_nombre') ?? [];
        $tiposDoc  = $this->request->getPost('asistente_tipo_doc') ?? [];
        $numsDoc   = $this->request->getPost('asistente_num_doc') ?? [];
        $cargos    = $this->request->getPost('asistente_cargo') ?? [];
        $areas     = $this->request->getPost('asistente_area') ?? [];
        $emails    = $this->request->getPost('asistente_email') ?? [];
        $celulares = $this->request->getPost('asistente_celular') ?? [];

        $existentes = [];
        foreach ($this->asistenteModel->getByActa($idActa) as $a) {
            $existentes[$a['id']] = $a;
        }
        $idsRecibidos = [];

        foreach ($nombres as $i => $nombre) {
            if (empty(trim($nombre))) continue;

            $existenteId = isset($ids[$i]) && $ids[$i] !== '' ? (int)$ids[$i] : null;
            $payload = [
                'id_acta_capacitacion' => $idActa,
                'nombre_completo'      => trim($nombre),
                'tipo_documento'       => $tiposDoc[$i] ?? 'CC',
                'numero_documento'     => $numsDoc[$i] ?? null,
                'cargo'                => $cargos[$i] ?? null,
                'area_dependencia'     => $areas[$i] ?? null,
                'email'                => $emails[$i] ?? null,
                'celular'              => $celulares[$i] ?? null,
                'orden'                => $i + 1,
            ];

            if ($existenteId && isset($existentes[$existenteId])) {
                $this->asistenteModel->update($existenteId, $payload);
                $idsRecibidos[] = $existenteId;
            } else {
                $this->asistenteModel->insert($payload);
                $idsRecibidos[] = $this->asistenteModel->getInsertID();
            }
        }

        foreach (array_keys($existentes) as $idExistente) {
            if (!in_array($idExistente, $idsRecibidos, true)) {
                $this->asistenteModel->delete($idExistente);
            }
        }
    }

    private function generarPdfInterno(int $id): ?string
    {
        $acta = $this->actaModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $miembroModel = new MiembroComiteModel();
        $cliente = $clientModel->find($acta['id_cliente']);
        $consultor = $acta['id_consultor'] ? $consultantModel->find($acta['id_consultor']) : null;
        $asistentes = $this->asistenteModel->getByActa($id);

        $realizadoPor = null;
        if ($acta['creado_por_tipo'] === 'miembro' && $acta['id_miembro']) {
            $m = $miembroModel->find($acta['id_miembro']);
            $realizadoPor = $m['nombre_completo'] ?? 'Miembro';
        }

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        foreach ($asistentes as &$a) {
            $a['firma_base64'] = '';
            if (!empty($a['firma_path']) && file_exists(FCPATH . $a['firma_path'])) {
                $a['firma_base64'] = 'data:image/png;base64,' . base64_encode(file_get_contents(FCPATH . $a['firma_path']));
            }
        }
        unset($a);

        $html = view('inspecciones/acta_capacitacion/pdf', [
            'acta'         => $acta,
            'cliente'      => $cliente,
            'consultor'    => $consultor,
            'realizadoPor' => $realizadoPor,
            'asistentes'   => $asistentes,
            'logoBase64'   => $logoBase64,
        ]);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/actas_capacitacion/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) mkdir(FCPATH . $pdfDir, 0755, true);

        $pdfFileName = 'acta_capacitacion_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        if (!empty($acta['ruta_pdf']) && file_exists(FCPATH . $acta['ruta_pdf'])) {
            unlink(FCPATH . $acta['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());
        return $pdfPath;
    }

    private function uploadToReportes(array $acta, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($acta['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'] ?? '';
        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $fileName = 'acta_capacitacion_' . $acta['id'] . '_' . date('Ymd_His') . '.pdf';
        copy(FCPATH . $pdfPath, $destDir . '/' . $fileName);

        $data = [
            'titulo_reporte'  => 'ACTA DE CAPACITACION - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $acta['fecha_capacitacion'],
            'id_detailreport' => 6,
            'id_report_type'  => 4,
            'id_cliente'      => $acta['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado por consultor. acta_capacitacion_id:' . $acta['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $existente = $reporteModel->where('id_cliente', $acta['id_cliente'])
            ->where('id_report_type', 4)
            ->where('id_detailreport', 6)
            ->like('observaciones', 'acta_capacitacion_id:' . $acta['id'])
            ->first();

        if ($existente) return $reporteModel->update($existente['id_reporte'], $data);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
