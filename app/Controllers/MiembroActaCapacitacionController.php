<?php

namespace App\Controllers;

use App\Models\ActaCapacitacionModel;
use App\Models\ActaCapacitacionAsistenteModel;
use App\Models\MiembroComiteModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Traits\AutosaveJsonTrait;
use Dompdf\Dompdf;

/**
 * Acta de Capacitación — flujo del miembro (PWA).
 * Transversal a TODOS los comités (no solo COPASST).
 */
class MiembroActaCapacitacionController extends BaseController
{
    use AutosaveJsonTrait;

    protected ActaCapacitacionModel $actaModel;
    protected ActaCapacitacionAsistenteModel $asistenteModel;
    protected MiembroComiteModel $miembroModel;

    public function __construct()
    {
        $this->actaModel = new ActaCapacitacionModel();
        $this->asistenteModel = new ActaCapacitacionAsistenteModel();
        $this->miembroModel = new MiembroComiteModel();
    }

    /**
     * Devuelve el miembro logueado SIN filtro COPASST — cualquier comité activo basta.
     */
    private function getMiembroAny(): ?array
    {
        $session   = session();
        $email     = $session->get('email_miembro');
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

        $actas = $this->actaModel
            ->select('tbl_acta_capacitacion.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_acta_capacitacion.id_consultor', 'left')
            ->where('tbl_acta_capacitacion.id_cliente', $miembro['id_cliente'])
            ->orderBy('tbl_acta_capacitacion.fecha_capacitacion', 'DESC')
            ->findAll();

        foreach ($actas as &$a) {
            $a['total_asistentes'] = $this->asistenteModel
                ->where('id_acta_capacitacion', $a['id'])->countAllResults(false);
            $a['total_firmados'] = $this->asistenteModel
                ->where('id_acta_capacitacion', $a['id'])
                ->where('firma_path IS NOT NULL', null, false)->countAllResults(false);
            if ($a['creado_por_tipo'] === 'miembro' && $a['id_miembro']) {
                $m = $this->miembroModel->find($a['id_miembro']);
                $a['nombre_creador'] = $m['nombre_completo'] ?? 'Miembro';
            } else {
                $a['nombre_creador'] = $a['nombre_consultor'] ?? 'Consultor';
            }
        }

        $clienteModel = new ClientModel();
        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/acta_capacitacion_list', [
                'actas'   => $actas,
                'cliente' => $clienteModel->find($miembro['id_cliente']),
                'miembro' => $miembro,
            ]),
            'title'   => 'Actas de Capacitación',
            'miembro' => $miembro,
        ]);
    }

    public function create()
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $clienteModel = new ClientModel();
        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/acta_capacitacion/form', [
                'title'      => 'Nueva Acta de Capacitación',
                'acta'       => null,
                'asistentes' => [],
                'cliente'    => $clienteModel->find($miembro['id_cliente']),
                'miembro'    => $miembro,
                'idCliente'  => $miembro['id_cliente'],
                'contexto'   => 'miembro',
            ]),
            'title'   => 'Nueva Acta de Capacitación',
            'miembro' => $miembro,
        ]);
    }

    public function store()
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $isAutosave = $this->isAutosaveRequest();
        if (!$isAutosave) {
            if (!$this->validate(['fecha_capacitacion' => 'required|valid_date', 'tema' => 'required|min_length[3]'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $idComite = $this->request->getPost('id_comite');
        $this->actaModel->insert([
            'id_cliente'           => $miembro['id_cliente'],
            'id_comite'            => $idComite ? (int)$idComite : null,
            'creado_por_tipo'      => 'miembro',
            'id_miembro'           => $miembro['id_miembro'],
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
        return redirect()->to('/miembro/acta-capacitacion/edit/' . $idActa)
            ->with('msg', 'Guardada como borrador');
    }

    public function edit($id)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $acta = $this->actaModel->find($id);
        if (!$acta || (int)$acta['id_cliente'] !== (int)$miembro['id_cliente'] || $acta['estado'] === 'completo') {
            return redirect()->to('/miembro/acta-capacitacion')->with('error', 'No encontrada o no editable');
        }
        if ($acta['creado_por_tipo'] === 'miembro' && (int)$acta['id_miembro'] !== (int)$miembro['id_miembro']) {
            return redirect()->to('/miembro/acta-capacitacion')->with('error', 'Solo puedes editar tus propias actas');
        }

        $clienteModel = new ClientModel();
        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/acta_capacitacion/form', [
                'title'      => 'Editar Acta de Capacitación',
                'acta'       => $acta,
                'asistentes' => $this->asistenteModel->getByActa((int)$id),
                'cliente'    => $clienteModel->find($miembro['id_cliente']),
                'miembro'    => $miembro,
                'idCliente'  => $miembro['id_cliente'],
                'contexto'   => 'miembro',
            ]),
            'title'   => 'Editar Acta de Capacitación',
            'miembro' => $miembro,
        ]);
    }

    public function update($id)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $acta = $this->actaModel->find($id);
        if (!$acta || (int)$acta['id_cliente'] !== (int)$miembro['id_cliente'] || $acta['estado'] === 'completo') {
            if ($this->isAutosaveRequest()) return $this->autosaveJsonError('No editable', 404);
            return redirect()->to('/miembro/acta-capacitacion');
        }

        $idComite = $this->request->getPost('id_comite');
        $this->actaModel->update($id, [
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

        return redirect()->to('/miembro/acta-capacitacion/edit/' . $id)->with('msg', 'Actualizada');
    }

    public function view($id)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $acta = $this->actaModel->find($id);
        if (!$acta || (int)$acta['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/acta-capacitacion');
        }

        $clienteModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $realizadoPor = null;
        if ($acta['creado_por_tipo'] === 'miembro' && $acta['id_miembro']) {
            $m = $this->miembroModel->find($acta['id_miembro']);
            $realizadoPor = $m['nombre_completo'] ?? 'Miembro';
        }

        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/acta_capacitacion_view', [
                'acta'         => $acta,
                'cliente'      => $clienteModel->find($acta['id_cliente']),
                'consultor'    => $acta['id_consultor'] ? $consultantModel->find($acta['id_consultor']) : null,
                'realizadoPor' => $realizadoPor,
                'asistentes'   => $this->asistenteModel->getByActa((int)$id),
                'contexto'     => 'miembro',
            ]),
            'title'   => 'Ver Acta de Capacitación',
            'miembro' => $miembro,
        ]);
    }

    /**
     * Genera token de firma remota para un asistente y devuelve la URL para WhatsApp.
     */
    public function generarTokenFirma(int $idAsistente)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return $this->response->setJSON(['success' => false, 'error' => 'No autenticado']);

        $asistente = $this->asistenteModel->find($idAsistente);
        if (!$asistente) return $this->response->setJSON(['success' => false, 'error' => 'Asistente no encontrado']);

        $acta = $this->actaModel->find($asistente['id_acta_capacitacion']);
        if (!$acta || (int)$acta['id_cliente'] !== (int)$miembro['id_cliente']) {
            return $this->response->setJSON(['success' => false, 'error' => 'Sin acceso']);
        }
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

    /**
     * AJAX: guarda/actualiza UN asistente individual (sin tocar el resto del form).
     */
    public function saveAsistente(int $idActa)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return $this->response->setJSON(['success' => false, 'error' => 'No autenticado']);

        $acta = $this->actaModel->find($idActa);
        if (!$acta || (int)$acta['id_cliente'] !== (int)$miembro['id_cliente']) {
            return $this->response->setJSON(['success' => false, 'error' => 'Sin acceso al acta']);
        }
        if ($acta['estado'] === 'completo') {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta ya finalizada']);
        }

        $nombre = trim((string)$this->request->getPost('nombre_completo'));
        if ($nombre === '') {
            return $this->response->setJSON(['success' => false, 'error' => 'Nombre requerido']);
        }

        $payload = [
            'id_acta_capacitacion' => $idActa,
            'nombre_completo'      => $nombre,
            'tipo_documento'       => $this->request->getPost('tipo_documento') ?: 'CC',
            'numero_documento'     => $this->request->getPost('numero_documento') ?: null,
            'cargo'                => $this->request->getPost('cargo') ?: null,
            'area_dependencia'     => $this->request->getPost('area_dependencia') ?: null,
            'email'                => $this->request->getPost('email') ?: null,
            'celular'              => $this->request->getPost('celular') ?: null,
            'orden'                => (int)($this->request->getPost('orden') ?: 1),
        ];

        $idAsistente = $this->request->getPost('id_asistente');
        if ($idAsistente && ($existente = $this->asistenteModel->find((int)$idAsistente))
            && (int)$existente['id_acta_capacitacion'] === $idActa) {
            $this->asistenteModel->update((int)$idAsistente, $payload);
            $id = (int)$idAsistente;
        } else {
            $this->asistenteModel->insert($payload);
            $id = (int)$this->asistenteModel->getInsertID();
        }

        $asistente = $this->asistenteModel->find($id);
        return $this->response->setJSON([
            'success'    => true,
            'id'         => $id,
            'asistente'  => $asistente,
        ]);
    }

    /**
     * AJAX: genera token (si no existe) y envía email con el enlace de firma al asistente.
     */
    public function enviarEmailFirma(int $idAsistente)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return $this->response->setJSON(['success' => false, 'error' => 'No autenticado']);

        $asistente = $this->asistenteModel->find($idAsistente);
        if (!$asistente) return $this->response->setJSON(['success' => false, 'error' => 'Asistente no encontrado']);

        $acta = $this->actaModel->find($asistente['id_acta_capacitacion']);
        if (!$acta || (int)$acta['id_cliente'] !== (int)$miembro['id_cliente']) {
            return $this->response->setJSON(['success' => false, 'error' => 'Sin acceso']);
        }
        if (!empty($asistente['firma_path'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Este asistente ya firmó']);
        }
        if (empty($asistente['email'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Este asistente no tiene email registrado']);
        }

        // Reutiliza token vigente o genera uno nuevo
        $token = $asistente['token_firma'];
        $vigente = $token && $asistente['token_expiracion'] && strtotime($asistente['token_expiracion']) > time();
        if (!$vigente) {
            $token = bin2hex(random_bytes(32));
            $this->asistenteModel->update($idAsistente, [
                'token_firma'      => $token,
                'token_expiracion' => date('Y-m-d H:i:s', strtotime('+7 days')),
            ]);
        }

        $cliente = (new ClientModel())->find($acta['id_cliente']);
        $ok = $this->enviarEmailFirmaCapacitacion($asistente, $token, $acta, $cliente);

        return $this->response->setJSON([
            'success' => $ok,
            'email'   => $asistente['email'],
            'error'   => $ok ? null : 'No se pudo enviar el email',
        ]);
    }

    private function enviarEmailFirmaCapacitacion(array $asistente, string $token, array $acta, ?array $cliente): bool
    {
        $urlFirma = base_url("acta-capacitacion/firmar-remoto/{$token}");
        $tema = esc($acta['tema'] ?? '');
        $fecha = date('d/m/Y', strtotime($acta['fecha_capacitacion']));
        $modalidad = ucfirst($acta['modalidad'] ?? 'virtual');
        $nombreCliente = esc($cliente['nombre_cliente'] ?? '');
        $nombre = esc($asistente['nombre_completo']);

        $mensaje = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0;'>Solicitud de Firma - Acta de Capacitación</h2>
            </div>
            <div style='padding: 30px; background: #f8f9fa;'>
                <p>Estimado/a <strong>{$nombre}</strong>,</p>
                <p>Se requiere su firma electrónica para confirmar la asistencia a la siguiente capacitación:</p>
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #bd9751;'>
                    <p style='margin: 5px 0;'><strong>Empresa:</strong> {$nombreCliente}</p>
                    <p style='margin: 5px 0;'><strong>Tema:</strong> {$tema}</p>
                    <p style='margin: 5px 0;'><strong>Fecha:</strong> {$fecha}</p>
                    <p style='margin: 5px 0;'><strong>Modalidad:</strong> {$modalidad}</p>
                </div>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$urlFirma}' style='background: #bd9751; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 16px; display: inline-block;'>
                        Firmar Acta de Capacitación
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
            $email->setSubject("Firma requerida: Capacitación - {$tema} - {$nombreCliente}");
            $email->addTo($asistente['email'], $asistente['nombre_completo']);
            $email->addContent("text/html", $mensaje);
            $sg = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sg->send($email);
            return $response->statusCode() >= 200 && $response->statusCode() < 300;
        } catch (\Exception $e) {
            log_message('error', 'Error email firma capacitacion: ' . $e->getMessage());
            return false;
        }
    }

    public function finalizar($id)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $acta = $this->actaModel->find($id);
        if (!$acta || (int)$acta['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/acta-capacitacion');
        }

        $pdfPath = $this->generarPdfInterno((int)$id);
        if (!$pdfPath) return redirect()->back()->with('error', 'Error al generar PDF');

        $this->actaModel->update($id, ['estado' => 'completo', 'ruta_pdf' => $pdfPath]);

        $acta = $this->actaModel->find($id);
        $this->uploadToReportes($acta, $pdfPath);

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($miembro['id_cliente']);
        if (!empty($cliente['id_consultor'])) {
            $this->notificarConsultor($cliente, $miembro, $acta);
        }

        return redirect()->to('/miembro/acta-capacitacion/view/' . $id)->with('msg', 'Acta finalizada.');
    }

    public function generatePdf($id)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $acta = $this->actaModel->find($id);
        if (!$acta || (int)$acta['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/acta-capacitacion');
        }

        $pdfPath = $this->generarPdfInterno((int)$id);
        $fullPath = FCPATH . $pdfPath;

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="acta_capacitacion_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    // ===== PRIVADOS =====

    private function saveAsistentes(int $idActa): void
    {
        // NO-DESTRUCTIVO: solo INSERT/UPDATE. La eliminacion se hace via
        // endpoint dedicado deleteAsistente() con confirmacion del usuario.
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
            } else {
                $this->asistenteModel->insert($payload);
            }
        }
    }

    /**
     * AJAX (auth miembro): genera o reutiliza token de auto-inscripcion del acta.
     */
    public function generarTokenInscripcion(int $idActa)
    {
        $acta = $this->actaModel->find($idActa);
        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        }
        if ($acta['estado'] === 'completo') {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta finalizada, no acepta inscripciones']);
        }

        $token = $acta['token_inscripcion'] ?? null;
        $regenerar = $this->request->getPost('regenerar') === '1';
        if (!$token || $regenerar) {
            $token = bin2hex(random_bytes(24));
            $this->actaModel->update($idActa, ['token_inscripcion' => $token]);
        }

        $url = base_url("acta-capacitacion/inscripcion/{$token}");
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
                log_message('error', 'QR local fallo, fallback: ' . $e->getMessage());
            }
        }
        $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=' . urlencode($url);
        return '<img src="' . esc($apiUrl) . '" alt="QR" style="width:100%;height:auto;">';
    }

    /**
     * AJAX: estado actual de asistentes del acta para refrescar la vista.
     */
    public function getAsistentesStatus(int $idActa)
    {
        $acta = $this->actaModel->find($idActa);
        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        }
        $asistentes = $this->asistenteModel->getByActa($idActa);
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
            'success'  => true,
            'total'    => $total,
            'firmados' => $firmados,
            'pct'      => $total > 0 ? (int) round($firmados * 100 / $total) : 0,
            'asistentes' => $resumen,
        ]);
    }

    /**
     * AJAX: elimina UN asistente especifico. Bloquea si ya firmo.
     */
    public function deleteAsistente(int $idActa, int $idAsistente)
    {
        $acta = $this->actaModel->find($idActa);
        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        }
        if ($acta['estado'] === 'completo') {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta finalizada']);
        }

        $asistente = $this->asistenteModel->find($idAsistente);
        if (!$asistente || (int)$asistente['id_acta_capacitacion'] !== $idActa) {
            return $this->response->setJSON(['success' => false, 'error' => 'Asistente no encontrado']);
        }
        if (!empty($asistente['firma_path']) || !empty($asistente['firmado_at'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'No se puede eliminar: ya firmo']);
        }

        $this->asistenteModel->delete($idAsistente);
        return $this->response->setJSON(['success' => true]);
    }

    private function generarPdfInterno(int $id): ?string
    {
        $acta = $this->actaModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($acta['id_cliente']);
        $consultor = $acta['id_consultor'] ? $consultantModel->find($acta['id_consultor']) : null;
        $asistentes = $this->asistenteModel->getByActa($id);

        $realizadoPor = null;
        if ($acta['creado_por_tipo'] === 'miembro' && $acta['id_miembro']) {
            $m = $this->miembroModel->find($acta['id_miembro']);
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
            'observaciones'   => 'Generado por miembro. acta_capacitacion_id:' . $acta['id'],
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

    private function notificarConsultor(array $cliente, array $miembro, array $acta): bool
    {
        $consultor = (new ConsultantModel())->find($cliente['id_consultor']);
        if (!$consultor || empty($consultor['correo_consultor'])) return false;

        $fecha = date('d/m/Y', strtotime($acta['fecha_capacitacion']));
        $tema = esc($acta['tema'] ?? '');
        $modalidad = ucfirst($acta['modalidad'] ?? 'virtual');
        $dictadaPor = $acta['dictada_por'] ?? 'ARL';
        $entidad = esc($acta['entidad_capacitadora'] ?? '-');

        $mensaje = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0;'>Nueva Acta de Capacitación</h2>
            </div>
            <div style='padding: 30px; background: #f8f9fa;'>
                <p>Estimado/a <strong>{$consultor['nombre_consultor']}</strong>,</p>
                <p>Un miembro del comité ha registrado un acta de capacitación:</p>
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #bd9751;'>
                    <p style='margin: 5px 0;'><strong>Cliente:</strong> {$cliente['nombre_cliente']}</p>
                    <p style='margin: 5px 0;'><strong>Tema:</strong> {$tema}</p>
                    <p style='margin: 5px 0;'><strong>Fecha:</strong> {$fecha}</p>
                    <p style='margin: 5px 0;'><strong>Modalidad:</strong> {$modalidad}</p>
                    <p style='margin: 5px 0;'><strong>Dictada por:</strong> {$dictadaPor} ({$entidad})</p>
                    <p style='margin: 5px 0;'><strong>Registrada por:</strong> {$miembro['nombre_completo']}</p>
                </div>
                <p>El PDF ha sido generado y registrado en el sistema de reportes.</p>
            </div>
            <div style='background: #1e3a5f; padding: 15px; text-align: center;'>
                <p style='color: #94a3b8; font-size: 11px; margin: 0;'>EnterpriseSST - Sistema de Gestión de Seguridad y Salud en el Trabajo</p>
            </div>
        </div>";

        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "EnterpriseSST");
            $email->setSubject("Acta Capacitación - {$cliente['nombre_cliente']} - {$fecha}");
            $email->addTo($consultor['correo_consultor'], $consultor['nombre_consultor']);
            $email->addContent("text/html", $mensaje);
            $response = (new \SendGrid(getenv('SENDGRID_API_KEY')))->send($email);
            return $response->statusCode() >= 200 && $response->statusCode() < 300;
        } catch (\Exception $e) {
            log_message('error', 'Error notificando consultor acta capacitacion: ' . $e->getMessage());
            return false;
        }
    }
}
