<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\EntregaDotacionModel;
use App\Models\EntregaDotacionAsistenteModel;
use App\Models\EntregaDotacionItemModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Traits\AutosaveJsonTrait;
use Dompdf\Dompdf;

/**
 * Entrega de Dotación / EPP — flujo del consultor + endpoints públicos.
 *
 * Caso de uso:
 *  - El consultor llega al cliente con los EPP solicitados (ej. 20 operarios).
 *  - Digita una sola vez el contexto general + por cada operario los items que recibe.
 *  - Cada operario escanea un QR, llena sus datos y firma el recibido.
 *  - Al finalizar se genera UN PDF POR CADA OPERARIO firmado y se sube cada uno
 *    al reportList individualmente.
 */
class EntregaDotacionController extends BaseController
{
    use AutosaveJsonTrait;

    protected EntregaDotacionModel $entregaModel;
    protected EntregaDotacionAsistenteModel $asistenteModel;
    protected EntregaDotacionItemModel $itemModel;

    public function __construct()
    {
        $this->entregaModel = new EntregaDotacionModel();
        $this->asistenteModel = new EntregaDotacionAsistenteModel();
        $this->itemModel = new EntregaDotacionItemModel();
    }

    public function list()
    {
        $entregas = $this->entregaModel
            ->select('tbl_entrega_dotacion.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_entrega_dotacion.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_entrega_dotacion.id_consultor', 'left')
            ->orderBy('tbl_entrega_dotacion.fecha_entrega', 'DESC')
            ->findAll();

        foreach ($entregas as &$e) {
            $e['total_asistentes'] = $this->asistenteModel
                ->where('id_entrega_dotacion', $e['id'])->countAllResults(false);
            $e['total_firmados'] = $this->asistenteModel
                ->where('id_entrega_dotacion', $e['id'])
                ->where('firma_path IS NOT NULL', null, false)->countAllResults(false);
        }

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/entrega_dotacion/list', ['entregas' => $entregas]),
            'title'   => 'Entregas de Dotación',
        ]);
    }

    public function create($idCliente = null)
    {
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/entrega_dotacion/form', [
                'title'      => 'Nueva Entrega de Dotación',
                'entrega'    => null,
                'asistentes' => [],
                'idCliente'  => $idCliente,
                'contexto'   => 'consultor',
            ]),
            'title' => 'Nueva Entrega de Dotación',
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            if (!$this->validate([
                'id_cliente'    => 'required|integer',
                'fecha_entrega' => 'required|valid_date',
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $this->entregaModel->insert([
            'id_cliente'          => $this->request->getPost('id_cliente'),
            'creado_por_tipo'     => 'consultor',
            'id_consultor'        => $userId,
            'fecha_entrega'       => $this->request->getPost('fecha_entrega'),
            'hora'                => $this->request->getPost('hora') ?: null,
            'lugar'               => $this->request->getPost('lugar'),
            'responsable_entrega' => $this->request->getPost('responsable_entrega'),
            'tipo_dotacion'       => $this->request->getPost('tipo_dotacion'),
            'observaciones'       => $this->request->getPost('observaciones'),
            'estado'              => 'borrador',
        ]);
        $idEntrega = $this->entregaModel->getInsertID();

        if ($isAutosave) return $this->autosaveJsonSuccess($idEntrega);
        return redirect()->to('/inspecciones/entrega-dotacion/edit/' . $idEntrega)
            ->with('msg', 'Guardado como borrador');
    }

    public function edit($id)
    {
        $entrega = $this->entregaModel->find($id);
        if (!$entrega) return redirect()->to('/inspecciones/entrega-dotacion')->with('error', 'No encontrado');
        if ($entrega['estado'] === 'completo') return redirect()->to('/inspecciones/entrega-dotacion/view/' . $id);

        $asistentes = $this->asistenteModel->getByEntrega((int)$id);
        // Inyectar items por cada asistente
        foreach ($asistentes as &$a) {
            $a['items'] = $this->itemModel->getByAsistente((int)$a['id']);
        }
        unset($a);

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/entrega_dotacion/form', [
                'title'      => 'Editar Entrega de Dotación',
                'entrega'    => $entrega,
                'asistentes' => $asistentes,
                'idCliente'  => $entrega['id_cliente'],
                'contexto'   => 'consultor',
            ]),
            'title' => 'Editar Entrega de Dotación',
        ]);
    }

    public function update($id)
    {
        $entrega = $this->entregaModel->find($id);
        if (!$entrega) {
            if ($this->isAutosaveRequest()) return $this->autosaveJsonError('No encontrado', 404);
            return redirect()->to('/inspecciones/entrega-dotacion');
        }
        if ($entrega['estado'] === 'completo') {
            if ($this->isAutosaveRequest()) return $this->autosaveJsonError('No editable', 400);
            return redirect()->to('/inspecciones/entrega-dotacion/view/' . $id);
        }

        $this->entregaModel->update($id, [
            'id_cliente'          => $this->request->getPost('id_cliente'),
            'fecha_entrega'       => $this->request->getPost('fecha_entrega'),
            'hora'                => $this->request->getPost('hora') ?: null,
            'lugar'               => $this->request->getPost('lugar'),
            'responsable_entrega' => $this->request->getPost('responsable_entrega'),
            'tipo_dotacion'       => $this->request->getPost('tipo_dotacion'),
            'observaciones'       => $this->request->getPost('observaciones'),
        ]);

        if ($this->request->getPost('finalizar')) return $this->finalizar($id);
        if ($this->isAutosaveRequest()) return $this->autosaveJsonSuccess((int)$id);

        return redirect()->to('/inspecciones/entrega-dotacion/edit/' . $id)->with('msg', 'Actualizado');
    }

    public function view($id)
    {
        $entrega = $this->entregaModel->find($id);
        if (!$entrega) return redirect()->to('/inspecciones/entrega-dotacion');

        $clienteModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $asistentes = $this->asistenteModel->getByEntrega((int)$id);
        foreach ($asistentes as &$a) {
            $a['items'] = $this->itemModel->getByAsistente((int)$a['id']);
        }
        unset($a);

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/entrega_dotacion/view', [
                'entrega'    => $entrega,
                'cliente'    => $clienteModel->find($entrega['id_cliente']),
                'consultor'  => $entrega['id_consultor'] ? $consultantModel->find($entrega['id_consultor']) : null,
                'asistentes' => $asistentes,
                'contexto'   => 'consultor',
            ]),
            'title' => 'Ver Entrega de Dotación',
        ]);
    }

    /**
     * AJAX (auth): genera token de firma remota para un asistente.
     */
    public function generarTokenFirma(int $idAsistente)
    {
        $asistente = $this->asistenteModel->find($idAsistente);
        if (!$asistente) return $this->response->setJSON(['success' => false, 'error' => 'Asistente no encontrado']);

        $entrega = $this->entregaModel->find($asistente['id_entrega_dotacion']);
        if (!$entrega) return $this->response->setJSON(['success' => false, 'error' => 'Entrega no encontrada']);
        if (!empty($asistente['firma_path'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Este operario ya firmó']);
        }

        $token = bin2hex(random_bytes(32));
        $this->asistenteModel->update($idAsistente, [
            'token_firma'      => $token,
            'token_expiracion' => date('Y-m-d H:i:s', strtotime('+7 days')),
        ]);

        $url = base_url("entrega-dotacion/firmar-remoto/{$token}");
        return $this->response->setJSON(['success' => true, 'url' => $url, 'nombre' => $asistente['nombre_completo']]);
    }

    /**
     * AJAX: guarda/actualiza UN asistente individual junto con SUS items.
     * Espera arrays paralelos: item_descripcion[], item_cantidad[], item_talla[], item_marca[].
     */
    public function saveAsistente(int $idEntrega)
    {
        $entrega = $this->entregaModel->find($idEntrega);
        if (!$entrega) return $this->response->setJSON(['success' => false, 'error' => 'Entrega no encontrada']);
        if ($entrega['estado'] === 'completo') {
            return $this->response->setJSON(['success' => false, 'error' => 'Entrega ya finalizada']);
        }

        $nombre = trim((string)$this->request->getPost('nombre_completo'));
        if ($nombre === '') {
            return $this->response->setJSON(['success' => false, 'error' => 'Nombre requerido']);
        }

        $payload = [
            'id_entrega_dotacion' => $idEntrega,
            'nombre_completo'     => $nombre,
            'tipo_documento'      => $this->request->getPost('tipo_documento') ?: 'CC',
            'numero_documento'    => $this->request->getPost('numero_documento') ?: null,
            'cargo'               => $this->request->getPost('cargo') ?: null,
            'area_dependencia'    => $this->request->getPost('area_dependencia') ?: null,
            'email'               => $this->request->getPost('email') ?: null,
            'celular'             => $this->request->getPost('celular') ?: null,
            'orden'               => (int)($this->request->getPost('orden') ?: 1),
        ];

        $idAsistente = $this->request->getPost('id_asistente');
        if ($idAsistente && ($existente = $this->asistenteModel->find((int)$idAsistente))
            && (int)$existente['id_entrega_dotacion'] === $idEntrega) {
            $this->asistenteModel->update((int)$idAsistente, $payload);
            $id = (int)$idAsistente;
        } else {
            $this->asistenteModel->insert($payload);
            $id = (int)$this->asistenteModel->getInsertID();
        }

        // Reemplazar items: borrar todos los actuales del asistente e insertar los nuevos
        $this->itemModel->deleteByAsistente($id);
        $descs    = $this->request->getPost('item_descripcion') ?? [];
        $cants    = $this->request->getPost('item_cantidad') ?? [];
        $tallas   = $this->request->getPost('item_talla') ?? [];
        $marcas   = $this->request->getPost('item_marca') ?? [];
        foreach ($descs as $i => $desc) {
            $desc = trim((string)$desc);
            if ($desc === '') continue;
            $this->itemModel->insert([
                'id_entrega_dotacion_asistente' => $id,
                'descripcion' => $desc,
                'cantidad'    => trim((string)($cants[$i] ?? '1')) ?: '1',
                'talla'       => trim((string)($tallas[$i] ?? '')) ?: null,
                'marca'       => trim((string)($marcas[$i] ?? '')) ?: null,
                'orden'       => $i + 1,
            ]);
        }

        $asistente = $this->asistenteModel->find($id);
        $asistente['items'] = $this->itemModel->getByAsistente($id);
        return $this->response->setJSON([
            'success'   => true,
            'id'        => $id,
            'asistente' => $asistente,
        ]);
    }

    /**
     * AJAX: genera token y envía email con enlace de firma.
     */
    public function enviarEmailFirma(int $idAsistente)
    {
        $asistente = $this->asistenteModel->find($idAsistente);
        if (!$asistente) return $this->response->setJSON(['success' => false, 'error' => 'Asistente no encontrado']);

        $entrega = $this->entregaModel->find($asistente['id_entrega_dotacion']);
        if (!$entrega) return $this->response->setJSON(['success' => false, 'error' => 'Entrega no encontrada']);
        if (!empty($asistente['firma_path'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Este operario ya firmó']);
        }
        if (empty($asistente['email'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Este operario no tiene email registrado']);
        }

        $token = $asistente['token_firma'];
        $vigente = $token && $asistente['token_expiracion'] && strtotime($asistente['token_expiracion']) > time();
        if (!$vigente) {
            $token = bin2hex(random_bytes(32));
            $this->asistenteModel->update($idAsistente, [
                'token_firma'      => $token,
                'token_expiracion' => date('Y-m-d H:i:s', strtotime('+7 days')),
            ]);
        }

        $cliente = (new ClientModel())->find($entrega['id_cliente']);
        $ok = $this->enviarEmailFirmaEntregaDotacion($asistente, $token, $entrega, $cliente);

        return $this->response->setJSON([
            'success' => $ok,
            'email'   => $asistente['email'],
            'error'   => $ok ? null : 'No se pudo enviar el email',
        ]);
    }

    private function enviarEmailFirmaEntregaDotacion(array $asistente, string $token, array $entrega, ?array $cliente): bool
    {
        $urlFirma = base_url("entrega-dotacion/firmar-remoto/{$token}");
        $tipo = esc($entrega['tipo_dotacion'] ?? 'EPP');
        $fecha = date('d/m/Y', strtotime($entrega['fecha_entrega']));
        $nombreCliente = esc($cliente['nombre_cliente'] ?? '');
        $nombre = esc($asistente['nombre_completo']);

        $mensaje = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0;'>Firma de Recibido - Entrega de Dotación</h2>
            </div>
            <div style='padding: 30px; background: #f8f9fa;'>
                <p>Estimado/a <strong>{$nombre}</strong>,</p>
                <p>Se requiere su firma electrónica para confirmar el recibido de los elementos entregados:</p>
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #bd9751;'>
                    <p style='margin: 5px 0;'><strong>Empresa:</strong> {$nombreCliente}</p>
                    <p style='margin: 5px 0;'><strong>Tipo de dotación:</strong> {$tipo}</p>
                    <p style='margin: 5px 0;'><strong>Fecha de entrega:</strong> {$fecha}</p>
                </div>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$urlFirma}' style='background: #bd9751; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 16px; display: inline-block;'>
                        Firmar Recibido
                    </a>
                </div>
                <p style='color: #666; font-size: 12px;'>O copie este enlace en su navegador:</p>
                <p style='word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 4px; font-size: 12px;'>{$urlFirma}</p>
                <hr style='border: none; border-top: 1px solid #dee2e6; margin: 20px 0;'>
                <p style='color: #666; font-size: 11px;'>
                    <strong>Importante:</strong> Este enlace es personal e intransferible. No lo comparta con nadie.<br>
                    El enlace expirará en 7 días.
                </p>
            </div>
            <div style='background: #1e3a5f; padding: 15px; text-align: center;'>
                <p style='color: #94a3b8; font-size: 11px; margin: 0;'>EnterpriseSST - Sistema de Gestión de Seguridad y Salud en el Trabajo</p>
            </div>
        </div>";

        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "EnterpriseSST");
            $email->setSubject("Firma requerida: Entrega de Dotación - {$nombreCliente}");
            $email->addTo($asistente['email'], $asistente['nombre_completo']);
            $email->addContent("text/html", $mensaje);
            $sg = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sg->send($email);
            return $response->statusCode() >= 200 && $response->statusCode() < 300;
        } catch (\Exception $e) {
            log_message('error', 'Error email firma entrega dotacion: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Finaliza la entrega: genera UN PDF POR CADA OPERARIO firmado y sube cada
     * uno como reporte individual al reportList.
     */
    public function finalizar($id)
    {
        $entrega = $this->entregaModel->find($id);
        if (!$entrega) return redirect()->to('/inspecciones/entrega-dotacion');

        $resultado = $this->generarPdfsIndividuales((int)$id);
        if ($resultado['errores'] > 0 && $resultado['generados'] === 0) {
            return redirect()->back()->with('error', 'Error al generar PDFs');
        }

        $this->entregaModel->update($id, ['estado' => 'completo']);

        return redirect()->to('/inspecciones/entrega-dotacion/view/' . $id)
            ->with('msg', "Entrega finalizada. PDFs generados: {$resultado['generados']}.");
    }

    /**
     * Genera el PDF individual del operario y descarga (o muestra inline).
     */
    public function generatePdfOperario($idAsistente)
    {
        $asistente = $this->asistenteModel->find($idAsistente);
        if (!$asistente) return redirect()->to('/inspecciones/entrega-dotacion');

        $entrega = $this->entregaModel->find($asistente['id_entrega_dotacion']);
        if (!$entrega) return redirect()->to('/inspecciones/entrega-dotacion');

        $pdfPath = $this->generarPdfUnOperario((int)$idAsistente);
        if (!$pdfPath) return redirect()->back()->with('error', 'No se pudo generar PDF');

        $fullPath = FCPATH . $pdfPath;
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="entrega_dotacion_' . $idAsistente . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $entrega = $this->entregaModel->find($id);
        if (!$entrega) return redirect()->to('/inspecciones/entrega-dotacion');
        if ($entrega['estado'] === 'completo') {
            return redirect()->back()->with('error', 'No se pueden borrar entregas finalizadas');
        }
        $this->entregaModel->delete($id);
        return redirect()->to('/inspecciones/entrega-dotacion')->with('msg', 'Entrega eliminada');
    }

    // ============================================================
    // ENDPOINTS PÚBLICOS (sin auth — token es la autenticación)
    // ============================================================

    public function firmarRemoto(string $token)
    {
        $asistente = $this->asistenteModel->getByToken($token);
        if (!$asistente) {
            return view('inspecciones/entrega_dotacion/firma_remota_error', [
                'mensaje' => 'Este enlace no es válido o ya fue usado.'
            ]);
        }
        if ($asistente['token_expiracion'] && strtotime($asistente['token_expiracion']) < time()) {
            return view('inspecciones/entrega_dotacion/firma_remota_error', [
                'mensaje' => 'Este enlace ha expirado (7 días). Pida uno nuevo al organizador.'
            ]);
        }
        if (!empty($asistente['firma_path'])) {
            return view('inspecciones/entrega_dotacion/firma_remota_error', [
                'mensaje' => 'Esta firma ya fue registrada.'
            ]);
        }

        $entrega = $this->entregaModel->find($asistente['id_entrega_dotacion']);
        if (!$entrega) {
            return view('inspecciones/entrega_dotacion/firma_remota_error', [
                'mensaje' => 'Entrega no encontrada.'
            ]);
        }

        $cliente = (new ClientModel())->find($entrega['id_cliente']);
        $items = $this->itemModel->getByAsistente((int)$asistente['id']);

        return view('inspecciones/entrega_dotacion/firma_remota', [
            'token'     => $token,
            'entrega'   => $entrega,
            'cliente'   => $cliente,
            'asistente' => $asistente,
            'items'     => $items,
        ]);
    }

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

        $dir = FCPATH . 'uploads/inspecciones/firmas_entrega_dotacion/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $nombreArchivo = 'firma_ed_' . $asistente['id'] . '_' . time() . '.png';
        file_put_contents($dir . $nombreArchivo, $firmaDecoded);

        $this->asistenteModel->update($asistente['id'], [
            'firma_path'       => 'uploads/inspecciones/firmas_entrega_dotacion/' . $nombreArchivo,
            'firmado_at'       => date('Y-m-d H:i:s'),
            'token_firma'      => null,
            'token_expiracion' => null,
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * AJAX (auth): genera o reutiliza el token de auto-inscripcion de la entrega.
     */
    public function generarTokenInscripcion(int $idEntrega)
    {
        $entrega = $this->entregaModel->find($idEntrega);
        if (!$entrega) {
            return $this->response->setJSON(['success' => false, 'error' => 'Entrega no encontrada']);
        }
        if ($entrega['estado'] === 'completo') {
            return $this->response->setJSON(['success' => false, 'error' => 'Entrega finalizada, no acepta inscripciones']);
        }

        $token = $entrega['token_inscripcion'] ?? null;
        $regenerar = $this->request->getPost('regenerar') === '1';
        if (!$token || $regenerar) {
            $token = bin2hex(random_bytes(24));
            $this->entregaModel->update($idEntrega, ['token_inscripcion' => $token]);
        }

        $url = base_url("entrega-dotacion/inscripcion/{$token}");
        return $this->response->setJSON([
            'success' => true,
            'token'   => $token,
            'url'     => $url,
            'qr_svg'  => $this->generarQrSvg($url),
        ]);
    }

    private function generarQrSvg(string $url): string
    {
        if (class_exists('\\chillerlan\\QRCode\\QRCode')) {
            try {
                $opts = new \chillerlan\QRCode\QROptions([
                    'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_MARKUP_SVG,
                    'eccLevel'   => \chillerlan\QRCode\QRCode::ECC_M,
                    'scale'      => 8,
                    'imageBase64'=> false,
                ]);
                return (new \chillerlan\QRCode\QRCode($opts))->render($url);
            } catch (\Throwable $e) {
                log_message('error', 'QR local fallo, fallback a api externa: ' . $e->getMessage());
            }
        }
        $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=' . urlencode($url);
        return '<img src="' . esc($apiUrl) . '" alt="QR" style="width:100%;height:auto;">';
    }

    /**
     * Vista publica: form de auto-inscripcion del operario (incluye items).
     */
    public function inscripcion(string $token)
    {
        $entrega = $this->entregaModel->findByTokenInscripcion($token);
        if (!$entrega) {
            return view('inspecciones/entrega_dotacion/inscripcion_error', [
                'mensaje' => 'Este enlace no es valido. Solicita uno nuevo al organizador.'
            ]);
        }
        if ($entrega['estado'] === 'completo') {
            return view('inspecciones/entrega_dotacion/inscripcion_error', [
                'mensaje' => 'Esta entrega ya fue cerrada y no acepta nuevas inscripciones.'
            ]);
        }

        $cliente = (new ClientModel())->find($entrega['id_cliente']);
        return view('inspecciones/entrega_dotacion/inscripcion_publica', [
            'token'   => $token,
            'entrega' => $entrega,
            'cliente' => $cliente,
        ]);
    }

    /**
     * POST publico: procesa la inscripcion del operario + sus items + redirige al canvas de firma.
     */
    public function procesarInscripcion()
    {
        $token   = trim((string)$this->request->getPost('token'));
        $nombre  = trim((string)$this->request->getPost('nombre_completo'));
        $tipoDoc = trim((string)$this->request->getPost('tipo_documento')) ?: 'CC';
        $numDoc  = trim((string)$this->request->getPost('numero_documento'));
        $cargo   = trim((string)$this->request->getPost('cargo'));
        $area    = trim((string)$this->request->getPost('area_dependencia'));
        $email   = trim((string)$this->request->getPost('email'));
        $celular = trim((string)$this->request->getPost('celular'));

        if (!$token || !$nombre || !$numDoc) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Nombre completo y numero de documento son obligatorios.',
            ]);
        }

        $entrega = $this->entregaModel->findByTokenInscripcion($token);
        if (!$entrega) {
            return $this->response->setJSON(['success' => false, 'error' => 'Enlace invalido o expirado.']);
        }
        if ($entrega['estado'] === 'completo') {
            return $this->response->setJSON(['success' => false, 'error' => 'Esta entrega ya fue cerrada.']);
        }

        // Anti-duplicado
        $existe = $this->asistenteModel
            ->where('id_entrega_dotacion', $entrega['id'])
            ->where('numero_documento', $numDoc)
            ->first();
        if ($existe) {
            return $this->response->setJSON([
                'success'   => false,
                'duplicado' => true,
                'error'     => 'Ya hay un operario registrado con este numero de documento.',
            ]);
        }

        $ultimo = $this->asistenteModel
            ->select('MAX(orden) AS max_orden')
            ->where('id_entrega_dotacion', $entrega['id'])
            ->first();
        $orden = isset($ultimo['max_orden']) ? ((int)$ultimo['max_orden']) + 1 : 1;

        $this->asistenteModel->insert([
            'id_entrega_dotacion' => $entrega['id'],
            'nombre_completo'     => $nombre,
            'tipo_documento'      => $tipoDoc,
            'numero_documento'    => $numDoc,
            'cargo'               => $cargo ?: null,
            'area_dependencia'    => $area ?: null,
            'email'               => $email ?: null,
            'celular'             => $celular ?: null,
            'orden'               => $orden,
        ]);
        $idAsistente = (int)$this->asistenteModel->getInsertID();

        // Items que el operario digitó al inscribirse
        $descs    = $this->request->getPost('item_descripcion') ?? [];
        $cants    = $this->request->getPost('item_cantidad') ?? [];
        $tallas   = $this->request->getPost('item_talla') ?? [];
        $marcas   = $this->request->getPost('item_marca') ?? [];
        foreach ($descs as $i => $desc) {
            $desc = trim((string)$desc);
            if ($desc === '') continue;
            $this->itemModel->insert([
                'id_entrega_dotacion_asistente' => $idAsistente,
                'descripcion' => $desc,
                'cantidad'    => trim((string)($cants[$i] ?? '1')) ?: '1',
                'talla'       => trim((string)($tallas[$i] ?? '')) ?: null,
                'marca'       => trim((string)($marcas[$i] ?? '')) ?: null,
                'orden'       => $i + 1,
            ]);
        }

        // Token de firma
        $tokenFirma = bin2hex(random_bytes(32));
        $this->asistenteModel->update($idAsistente, [
            'token_firma'      => $tokenFirma,
            'token_expiracion' => date('Y-m-d H:i:s', strtotime('+7 days')),
        ]);

        return $this->response->setJSON([
            'success'      => true,
            'id_asistente' => $idAsistente,
            'url_firmar'   => base_url("entrega-dotacion/firmar-remoto/{$tokenFirma}"),
        ]);
    }

    public function getAsistentesStatus(int $idEntrega)
    {
        $entrega = $this->entregaModel->find($idEntrega);
        if (!$entrega) {
            return $this->response->setJSON(['success' => false, 'error' => 'Entrega no encontrada']);
        }

        $asistentes = $this->asistenteModel->getByEntrega($idEntrega);
        $resumen = [];
        $firmados = 0;
        foreach ($asistentes as $a) {
            $tieneFirma = !empty($a['firma_path']);
            if ($tieneFirma) $firmados++;
            $resumen[] = [
                'id'              => (int)$a['id'],
                'nombre_completo' => $a['nombre_completo'],
                'firmado'         => $tieneFirma,
                'firmado_at'      => $a['firmado_at'] ?? null,
                'enlace_enviado'  => !$tieneFirma && !empty($a['token_firma']),
            ];
        }

        $total = count($resumen);
        return $this->response->setJSON([
            'success'    => true,
            'total'      => $total,
            'firmados'   => $firmados,
            'pct'        => $total > 0 ? (int) round($firmados * 100 / $total) : 0,
            'asistentes' => $resumen,
        ]);
    }

    public function deleteAsistente(int $idEntrega, int $idAsistente)
    {
        $entrega = $this->entregaModel->find($idEntrega);
        if (!$entrega) {
            return $this->response->setJSON(['success' => false, 'error' => 'Entrega no encontrada']);
        }
        if ($entrega['estado'] === 'completo') {
            return $this->response->setJSON(['success' => false, 'error' => 'Entrega finalizada, no se puede modificar']);
        }

        $asistente = $this->asistenteModel->find($idAsistente);
        if (!$asistente || (int)$asistente['id_entrega_dotacion'] !== $idEntrega) {
            return $this->response->setJSON(['success' => false, 'error' => 'Operario no encontrado en esta entrega']);
        }
        if (!empty($asistente['firma_path']) || !empty($asistente['firmado_at'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'No se puede eliminar: este operario ya firmo']);
        }

        $this->asistenteModel->delete($idAsistente);
        return $this->response->setJSON(['success' => true]);
    }

    // ============================================================
    // GENERACIÓN DE PDFs
    // ============================================================

    /**
     * Genera el PDF de UN solo operario y lo guarda en disco.
     * Retorna el path relativo (uploads/...) o null si falla.
     */
    private function generarPdfUnOperario(int $idAsistente): ?string
    {
        $asistente = $this->asistenteModel->find($idAsistente);
        if (!$asistente) return null;

        $entrega = $this->entregaModel->find($asistente['id_entrega_dotacion']);
        if (!$entrega) return null;

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($entrega['id_cliente']);
        $consultor = $entrega['id_consultor'] ? $consultantModel->find($entrega['id_consultor']) : null;
        $items = $this->itemModel->getByAsistente($idAsistente);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $firmaBase64 = '';
        if (!empty($asistente['firma_path']) && file_exists(FCPATH . $asistente['firma_path'])) {
            $firmaBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(FCPATH . $asistente['firma_path']));
        }

        $html = view('inspecciones/entrega_dotacion/pdf', [
            'entrega'     => $entrega,
            'cliente'     => $cliente,
            'consultor'   => $consultor,
            'asistente'   => $asistente,
            'items'       => $items,
            'logoBase64'  => $logoBase64,
            'firmaBase64' => $firmaBase64,
        ]);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/entregas_dotacion/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) mkdir(FCPATH . $pdfDir, 0755, true);

        $pdfFileName = 'entrega_dotacion_' . $entrega['id'] . '_op' . $idAsistente . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        if (!empty($asistente['ruta_pdf']) && file_exists(FCPATH . $asistente['ruta_pdf'])) {
            unlink(FCPATH . $asistente['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());
        $this->asistenteModel->update($idAsistente, ['ruta_pdf' => $pdfPath]);

        return $pdfPath;
    }

    /**
     * Genera N PDFs (uno por cada operario firmado) y sube cada uno al reportList.
     * Retorna ['generados' => int, 'omitidos' => int, 'errores' => int].
     */
    private function generarPdfsIndividuales(int $idEntrega): array
    {
        $entrega = $this->entregaModel->find($idEntrega);
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($entrega['id_cliente']);
        $asistentes = $this->asistenteModel->getByEntrega($idEntrega);

        $generados = 0;
        $omitidos  = 0;
        $errores   = 0;

        foreach ($asistentes as $a) {
            // Solo generar PDF de los que firmaron
            if (empty($a['firma_path'])) {
                $omitidos++;
                continue;
            }

            $pdfPath = $this->generarPdfUnOperario((int)$a['id']);
            if (!$pdfPath) {
                $errores++;
                continue;
            }

            $this->uploadPdfToReportes($entrega, $a, $pdfPath, $cliente);
            $generados++;
        }

        return [
            'generados' => $generados,
            'omitidos'  => $omitidos,
            'errores'   => $errores,
        ];
    }

    /**
     * Sube UN PDF individual al reportList (uno por operario).
     */
    private function uploadPdfToReportes(array $entrega, array $asistente, string $pdfPath, ?array $cliente): bool
    {
        if (!$cliente) return false;

        $reporteModel = new ReporteModel();
        $nitCliente = $cliente['nit_cliente'] ?? '';
        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $fileName = 'entrega_dotacion_' . $entrega['id'] . '_op' . $asistente['id'] . '_' . date('Ymd_His') . '.pdf';
        copy(FCPATH . $pdfPath, $destDir . '/' . $fileName);

        $tituloOperario = $asistente['nombre_completo'];
        $data = [
            'titulo_reporte'  => 'ENTREGA DE DOTACION - ' . $tituloOperario . ' - ' . $entrega['fecha_entrega'],
            'id_detailreport' => 19, // Soporte de Gestión
            'id_report_type'  => 24, // Inspecciones
            'id_cliente'      => $entrega['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado por consultor. entrega_dotacion_id:' . $entrega['id'] . ' op:' . $asistente['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $existente = $reporteModel->where('id_cliente', $entrega['id_cliente'])
            ->where('id_report_type', 24)
            ->where('id_detailreport', 19)
            ->like('observaciones', 'entrega_dotacion_id:' . $entrega['id'] . ' op:' . $asistente['id'])
            ->first();

        if ($existente) return $reporteModel->update($existente['id_reporte'], $data);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
