<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\RegistroAsistenciaModel;
use App\Models\RegistroAsistenciaAsistenteModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Libraries\InspeccionEmailNotifier;
use Dompdf\Dompdf;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;
use App\Traits\PreventDuplicateBorradorTrait;

class RegistroAsistenciaController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    use PreventDuplicateBorradorTrait;

    protected RegistroAsistenciaModel $inspeccionModel;

    public const TIPOS_REUNION = [
        'capacitacion'          => 'Capacitacion',
        'charla'                => 'Charla',
        'socializacion'         => 'Socializacion',
        'reunion_general'       => 'Reunion General',
        'comite'                => 'Comite',
        'brigada'               => 'Brigada',
        'simulacro'             => 'Simulacro',
        'induccion_reinduccion' => 'Induccion / Reinduccion',
        'otro'                  => 'Otro',
    ];

    public function __construct()
    {
        $this->inspeccionModel = new RegistroAsistenciaModel();
    }

    // ── LIST ──

    public function list()
    {
        $inspecciones = $this->inspeccionModel
            ->select('tbl_registro_asistencia.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_registro_asistencia.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_registro_asistencia.id_consultor', 'left')
            ->orderBy('tbl_registro_asistencia.fecha_sesion', 'DESC')
            ->findAll();

        $data = [
            'title'         => 'Registro de Asistencia',
            'inspecciones'  => $inspecciones,
            'tiposReunion'  => self::TIPOS_REUNION,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/registro_asistencia/list', $data),
            'title'   => 'Registro Asistencia',
        ]);
    }

    // ── CREATE ──

    public function create($idCliente = null)
    {
        $data = [
            'title'        => 'Nuevo Registro de Asistencia',
            'inspeccion'   => null,
            'asistentes'   => [],
            'idCliente'    => $idCliente,
            'tiposReunion' => self::TIPOS_REUNION,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/registro_asistencia/form', $data),
            'title'   => 'Nueva Asistencia',
        ]);
    }

    // ── STORE ──

    public function store()
    {
        $existing = $this->reuseExistingBorrador($this->inspeccionModel, 'fecha_sesion', '/inspecciones/registro-asistencia/edit/');
        if ($existing) return $existing;

        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_sesion' => 'required|valid_date'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $data = $this->getPostData();
        $data['id_consultor'] = $userId;
        $data['estado'] = 'borrador';

        $this->inspeccionModel->insert($data);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idInspeccion);
        }

        if ($this->request->getPost('accion') === 'borrador') {
            return redirect()->to('/inspecciones/registro-asistencia')
                ->with('msg', 'Borrador guardado.');
        }

        return redirect()->to('/inspecciones/registro-asistencia/registrar/' . $idInspeccion)
            ->with('msg', 'Registro creado. Registra los asistentes uno a uno.');
    }

    // ── EDIT ──

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/registro-asistencia')->with('error', 'Registro no encontrado');
        }
        $asistenteModel = new RegistroAsistenciaAsistenteModel();

        $data = [
            'title'        => 'Editar Registro de Asistencia',
            'inspeccion'   => $inspeccion,
            'asistentes'   => $asistenteModel->getByAsistencia($id),
            'idCliente'    => $inspeccion['id_cliente'],
            'tiposReunion' => self::TIPOS_REUNION,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/registro_asistencia/form', $data),
            'title'   => 'Editar Asistencia',
        ]);
    }

    // ── UPDATE ──

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
            return redirect()->to('/inspecciones/registro-asistencia')->with('error', 'No se puede editar');
        }

        $data = $this->getPostData();
        $this->inspeccionModel->update($id, $data);

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id);
        }

        if ($this->request->getPost('accion') === 'borrador') {
            return redirect()->to('/inspecciones/registro-asistencia')
                ->with('msg', 'Borrador guardado.');
        }

        return redirect()->to('/inspecciones/registro-asistencia/registrar/' . $id)
            ->with('msg', 'Asistencia actualizada.');
    }

    // ── VIEW ──

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/registro-asistencia')->with('error', 'No encontrado');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $asistenteModel = new RegistroAsistenciaAsistenteModel();

        $data = [
            'title'        => 'Ver Registro de Asistencia',
            'inspeccion'   => $inspeccion,
            'cliente'      => $clientModel->find($inspeccion['id_cliente']),
            'consultor'    => $consultantModel->find($inspeccion['id_consultor']),
            'asistentes'   => $asistenteModel->getByAsistencia($id),
            'tiposReunion' => self::TIPOS_REUNION,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/registro_asistencia/view', $data),
            'title'   => 'Ver Asistencia',
        ]);
    }

    // ── REGISTRAR (vista mobile-first para agregar asistentes) ──

    public function registrar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/registro-asistencia')->with('error', 'No encontrado');
        }
        $asistenteModel = new RegistroAsistenciaAsistenteModel();
        $data = [
            'title'      => 'Registrar Asistentes',
            'inspeccion' => $inspeccion,
            'asistentes' => $asistenteModel->getByAsistencia($id),
        ];
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/registro_asistencia/registrar', $data),
            'title'   => 'Registrar Asistentes',
        ]);
    }

    // ── AJAX: guardar asistente con firma ──

    public function storeAsistente($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return $this->response->setJSON(['success' => false, 'error' => 'No encontrado']);
        }

        $nombre = trim($this->request->getPost('nombre') ?? '');
        if (!$nombre) {
            return $this->response->setJSON(['success' => false, 'error' => 'Nombre requerido']);
        }

        $firmaBase64 = $this->request->getPost('firma');
        $firmaPath = '';
        if (!empty($firmaBase64)) {
            $firmaData = str_replace('data:image/png;base64,', '', $firmaBase64);
            $firmaData = str_replace(' ', '+', $firmaData);
            $decoded = base64_decode($firmaData);
            $dir = 'uploads/inspecciones/registro-asistencia/firmas/';
            if (!is_dir(FCPATH . $dir)) {
                mkdir(FCPATH . $dir, 0755, true);
            }
            $fileName = 'firma_' . $id . '_' . time() . '_' . mt_rand(100, 999) . '.png';
            file_put_contents(FCPATH . $dir . $fileName, $decoded);
            $firmaPath = $dir . $fileName;
        }

        $asistenteModel = new RegistroAsistenciaAsistenteModel();
        $asistenteModel->insert([
            'id_asistencia' => $id,
            'nombre'        => $nombre,
            'cedula'        => $this->request->getPost('cedula') ?? '',
            'cargo'         => $this->request->getPost('cargo') ?? '',
            'firma'         => $firmaPath,
        ]);
        $idAsistente = $asistenteModel->getInsertID();
        $total = $asistenteModel->where('id_asistencia', $id)->countAllResults();

        return $this->response->setJSON([
            'success'      => true,
            'id_asistente' => $idAsistente,
            'total'        => $total,
            'csrf_hash'    => csrf_hash(),
        ]);
    }

    // ── AJAX: eliminar asistente ──

    public function deleteAsistente($idAsistente)
    {
        $asistenteModel = new RegistroAsistenciaAsistenteModel();
        $asistente = $asistenteModel->find($idAsistente);
        if (!$asistente) {
            return $this->response->setJSON(['success' => false]);
        }
        if (!empty($asistente['firma']) && file_exists(FCPATH . $asistente['firma'])) {
            unlink(FCPATH . $asistente['firma']);
        }
        $idAsistencia = $asistente['id_asistencia'];
        $asistenteModel->delete($idAsistente);
        $total = $asistenteModel->where('id_asistencia', $idAsistencia)->countAllResults();

        return $this->response->setJSON([
            'success'   => true,
            'total'     => $total,
            'csrf_hash' => csrf_hash(),
        ]);
    }

    // ── FIRMAS ──

    public function firmas($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/registro-asistencia')->with('error', 'No encontrado');
        }

        $asistenteModel = new RegistroAsistenciaAsistenteModel();

        $data = [
            'title'      => 'Firmas Asistencia',
            'inspeccion' => $inspeccion,
            'asistentes' => $asistenteModel->getByAsistencia($id),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/registro_asistencia/firmas', $data),
            'title'   => 'Firmas Asistencia',
        ]);
    }

    // ── AJAX: guardar firma individual ──

    public function guardarFirma($idAsistente)
    {
        $asistenteModel = new RegistroAsistenciaAsistenteModel();
        $asistente = $asistenteModel->find($idAsistente);
        if (!$asistente) {
            return $this->response->setJSON(['success' => false, 'error' => 'No encontrado']);
        }

        $firmaData = $this->request->getPost('firma');
        if (empty($firmaData)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Firma vacia']);
        }

        $firmaData = str_replace('data:image/png;base64,', '', $firmaData);
        $firmaData = str_replace(' ', '+', $firmaData);
        $decoded = base64_decode($firmaData);

        $dir = 'uploads/inspecciones/registro-asistencia/firmas/';
        if (!is_dir(FCPATH . $dir)) {
            mkdir(FCPATH . $dir, 0755, true);
        }

        if (!empty($asistente['firma']) && file_exists(FCPATH . $asistente['firma'])) {
            unlink(FCPATH . $asistente['firma']);
        }

        $fileName = 'firma_' . $idAsistente . '_' . time() . '.png';
        file_put_contents(FCPATH . $dir . $fileName, $decoded);

        $asistenteModel->update($idAsistente, ['firma' => $dir . $fileName]);

        return $this->response->setJSON(['success' => true, 'firma_url' => '/' . $dir . $fileName]);
    }

    // ── FINALIZAR ──

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/registro-asistencia')->with('error', 'No encontrado');
        }

        $asistenteModel = new RegistroAsistenciaAsistenteModel();
        $asistentes = $asistenteModel->getByAsistencia($id);
        if (empty($asistentes)) {
            return redirect()->to('/inspecciones/registro-asistencia/edit/' . $id)->with('error', 'Debe agregar al menos un asistente antes de finalizar.');
        }
        $sinFirma = array_filter($asistentes, fn($a) => empty($a['firma']));
        if (!empty($sinFirma)) {
            return redirect()->to('/inspecciones/registro-asistencia/firmas/' . $id)->with('error', 'Todos los asistentes deben firmar antes de finalizar. Faltan ' . count($sinFirma) . ' firma(s).');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->inspeccionModel->update($id, [
            'estado'              => 'completo',
            'ruta_pdf_asistencia' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);

        $this->uploadToReportes($inspeccion, $pdfPath);

        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'REGISTRO DE ASISTENCIA',
            $inspeccion['fecha_sesion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'RegistroAsistencia',
            $inspeccion['capacitador'] ?? ''
        );

        $msg = 'Finalizado y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to('/inspecciones/registro-asistencia/view/' . $id)
            ->with('msg', $msg);
    }

    // ── PDF ──

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/registro-asistencia')->with('error', 'No encontrado');
        }

        if (!empty($inspeccion['ruta_pdf_asistencia']) && file_exists(FCPATH . $inspeccion['ruta_pdf_asistencia'])) {
            $fullPath = FCPATH . $inspeccion['ruta_pdf_asistencia'];
        } else {
            $pdfPath = $this->generarPdfInterno($id);
            $this->inspeccionModel->update($id, ['ruta_pdf_asistencia' => $pdfPath]);
            $fullPath = FCPATH . $pdfPath;
        }

        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="registro_asistencia_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    // ── REGENERAR PDF ──

    public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/registro-asistencia')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf_asistencia' => $pdfPath]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/registro-asistencia/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    // ── DELETE ──

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/registro-asistencia')->with('error', 'No encontrado');
        }

        $asistenteModel = new RegistroAsistenciaAsistenteModel();
        $asistentes = $asistenteModel->getByAsistencia($id);
        foreach ($asistentes as $a) {
            if (!empty($a['firma']) && file_exists(FCPATH . $a['firma'])) {
                unlink(FCPATH . $a['firma']);
            }
        }
        $asistenteModel->where('id_asistencia', $id)->delete();

        if (!empty($inspeccion['ruta_pdf_asistencia']) && file_exists(FCPATH . $inspeccion['ruta_pdf_asistencia'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf_asistencia']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/registro-asistencia')->with('msg', 'Registro eliminado');
    }

    // ── EMAIL ──

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf_asistencia'])) {
            return redirect()->to("/inspecciones/registro-asistencia/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'REGISTRO DE ASISTENCIA',
            $inspeccion['fecha_sesion'],
            $inspeccion['ruta_pdf_asistencia'],
            (int) $inspeccion['id'],
            'RegistroAsistencia',
            $inspeccion['capacitador'] ?? ''
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/registro-asistencia/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/registro-asistencia/view/{$id}")->with('error', $result['error']);
    }

    // ── GENERAR OBJETIVO CON IA ──

    public function generarObjetivo()
    {
        $tema = trim($this->request->getJSON(true)['tema'] ?? '');

        if (!$tema) {
            return $this->response->setJSON(['error' => 'Tema vacio.'])->setStatusCode(400);
        }

        $apiKey = env('OPENAI_API_KEY');
        if (!$apiKey) {
            return $this->response->setJSON(['error' => 'API key no configurada.'])->setStatusCode(500);
        }

        $prompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) en Colombia. Redacta el objetivo de la siguiente sesion: «{$tema}».

El objetivo debe:
- Ser claro, concreto y profesional
- Estar en infinitivo (Capacitar, Sensibilizar, Fortalecer, Instruir, etc.)
- Tener maximo 3 oraciones
- No incluir titulos ni numeracion, solo el texto del objetivo";

        $payload = json_encode([
            'model'       => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages'    => [['role' => 'user', 'content' => $prompt]],
            'max_tokens'  => 200,
            'temperature' => 0.6,
        ]);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_TIMEOUT => 20,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$response || $httpCode !== 200) {
            log_message('error', 'RegistroAsistencia generarObjetivo OpenAI HTTP ' . $httpCode . ': ' . $response);
            return $this->response->setJSON(['error' => 'Error al contactar la IA. Intenta de nuevo.'])->setStatusCode(500);
        }

        $data = json_decode($response, true);
        $objetivo = trim($data['choices'][0]['message']['content'] ?? '');

        if (!$objetivo) {
            return $this->response->setJSON(['error' => 'La IA no devolvio respuesta.'])->setStatusCode(500);
        }

        return $this->response->setJSON(['objetivo' => $objetivo]);
    }

    // ===== PRIVATE METHODS =====

    private function getPostData(): array
    {
        return [
            'id_cliente'    => $this->request->getPost('id_cliente'),
            'fecha_sesion'  => $this->request->getPost('fecha_sesion'),
            'tema'          => $this->request->getPost('tema'),
            'lugar'         => $this->request->getPost('lugar'),
            'objetivo'      => $this->request->getPost('objetivo'),
            'capacitador'   => $this->request->getPost('capacitador'),
            'tipo_reunion'  => $this->request->getPost('tipo_reunion'),
            'material'      => $this->request->getPost('material'),
            'tiempo_horas'  => $this->request->getPost('tiempo_horas'),
            'observaciones' => $this->request->getPost('observaciones'),
        ];
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $asistenteModel = new RegistroAsistenciaAsistenteModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
            }
        }

        $asistentes = $asistenteModel->getByAsistencia($id);
        foreach ($asistentes as &$a) {
            $a['firma_base64'] = '';
            if (!empty($a['firma'])) {
                $firmaPath = FCPATH . $a['firma'];
                if (file_exists($firmaPath)) {
                    $mime = mime_content_type($firmaPath);
                    $a['firma_base64'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($firmaPath));
                }
            }
        }
        unset($a);

        $data = [
            'inspeccion'    => $inspeccion,
            'cliente'       => $cliente,
            'consultor'     => $consultor,
            'asistentes'    => $asistentes,
            'tiposReunion'  => self::TIPOS_REUNION,
            'logoBase64'    => $logoBase64,
        ];

        $pdfDir = 'uploads/inspecciones/registro-asistencia/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $html = view('inspecciones/registro_asistencia/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfFileName = 'registro_asistencia_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        if (!empty($inspeccion['ruta_pdf_asistencia']) && file_exists(FCPATH . $inspeccion['ruta_pdf_asistencia'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf_asistencia']);
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
            ->where('id_detailreport', 14)
            ->like('observaciones', 'reg_asist_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = basename($pdfPath);
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'REGISTRO ASISTENCIA - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_sesion'],
            'id_detailreport' => 14,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. reg_asist_id:' . $inspeccion['id'],
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
