<?php

namespace App\Controllers;

use App\Models\ListaAsistenciaModel;
use App\Models\ListaAsistenciaAsistenteModel;
use App\Models\MiembroComiteModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Traits\AutosaveJsonTrait;
use Dompdf\Dompdf;

/**
 * Lista de Asistencia — flujo del miembro (PWA).
 * Transversal a TODOS los comités (no solo COPASST).
 */
class MiembroListaAsistenciaController extends BaseController
{
    use AutosaveJsonTrait;

    protected ListaAsistenciaModel $listaModel;
    protected ListaAsistenciaAsistenteModel $asistenteModel;
    protected MiembroComiteModel $miembroModel;

    public function __construct()
    {
        $this->listaModel = new ListaAsistenciaModel();
        $this->asistenteModel = new ListaAsistenciaAsistenteModel();
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

        $listas = $this->listaModel
            ->select('tbl_lista_asistencia.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_lista_asistencia.id_consultor', 'left')
            ->where('tbl_lista_asistencia.id_cliente', $miembro['id_cliente'])
            ->orderBy('tbl_lista_asistencia.fecha_actividad', 'DESC')
            ->findAll();

        foreach ($listas as &$l) {
            $l['total_asistentes'] = $this->asistenteModel
                ->where('id_lista_asistencia', $l['id'])->countAllResults(false);
            $l['total_firmados'] = $this->asistenteModel
                ->where('id_lista_asistencia', $l['id'])
                ->where('firma_path IS NOT NULL', null, false)->countAllResults(false);
            if ($l['creado_por_tipo'] === 'miembro' && $l['id_miembro']) {
                $m = $this->miembroModel->find($l['id_miembro']);
                $l['nombre_creador'] = $m['nombre_completo'] ?? 'Miembro';
            } else {
                $l['nombre_creador'] = $l['nombre_consultor'] ?? 'Consultor';
            }
        }

        $clienteModel = new ClientModel();
        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/lista_asistencia_list', [
                'listas'  => $listas,
                'cliente' => $clienteModel->find($miembro['id_cliente']),
                'miembro' => $miembro,
            ]),
            'title'   => 'Listas de Asistencia',
            'miembro' => $miembro,
        ]);
    }

    public function create()
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $clienteModel = new ClientModel();
        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/lista_asistencia/form', [
                'title'      => 'Nueva Lista de Asistencia',
                'lista'      => null,
                'asistentes' => [],
                'cliente'    => $clienteModel->find($miembro['id_cliente']),
                'miembro'    => $miembro,
                'idCliente'  => $miembro['id_cliente'],
                'contexto'   => 'miembro',
            ]),
            'title'   => 'Nueva Lista de Asistencia',
            'miembro' => $miembro,
        ]);
    }

    public function store()
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $isAutosave = $this->isAutosaveRequest();
        if (!$isAutosave) {
            if (!$this->validate(['fecha_actividad' => 'required|valid_date', 'motivo' => 'required|min_length[3]'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $idComite = $this->request->getPost('id_comite');
        $this->listaModel->insert([
            'id_cliente'       => $miembro['id_cliente'],
            'id_comite'        => $idComite ? (int)$idComite : null,
            'creado_por_tipo'  => 'miembro',
            'id_miembro'       => $miembro['id_miembro'],
            'motivo'           => $this->request->getPost('motivo'),
            'fecha_actividad'  => $this->request->getPost('fecha_actividad'),
            'hora_inicio'      => $this->request->getPost('hora_inicio') ?: null,
            'hora_fin'         => $this->request->getPost('hora_fin') ?: null,
            'modalidad'        => $this->request->getPost('modalidad') ?: 'presencial',
            'convocada_por'    => $this->request->getPost('convocada_por'),
            'lugar'            => $this->request->getPost('lugar'),
            'agenda'           => $this->request->getPost('agenda'),
            'enlace_grabacion' => $this->request->getPost('enlace_grabacion'),
            'observaciones'    => $this->request->getPost('observaciones'),
            'estado'           => 'borrador',
        ]);
        $idLista = $this->listaModel->getInsertID();

        $this->saveAsistentes($idLista);

        if ($isAutosave) return $this->autosaveJsonSuccess($idLista);
        return redirect()->to('/miembro/lista-asistencia/edit/' . $idLista)
            ->with('msg', 'Guardado como borrador');
    }

    public function edit($id)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $lista = $this->listaModel->find($id);
        if (!$lista || (int)$lista['id_cliente'] !== (int)$miembro['id_cliente'] || $lista['estado'] === 'completo') {
            return redirect()->to('/miembro/lista-asistencia')->with('error', 'No encontrado o no editable');
        }
        if ($lista['creado_por_tipo'] === 'miembro' && (int)$lista['id_miembro'] !== (int)$miembro['id_miembro']) {
            return redirect()->to('/miembro/lista-asistencia')->with('error', 'Solo puedes editar tus propias listas');
        }

        $clienteModel = new ClientModel();
        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/lista_asistencia/form', [
                'title'      => 'Editar Lista de Asistencia',
                'lista'      => $lista,
                'asistentes' => $this->asistenteModel->getByLista((int)$id),
                'cliente'    => $clienteModel->find($miembro['id_cliente']),
                'miembro'    => $miembro,
                'idCliente'  => $miembro['id_cliente'],
                'contexto'   => 'miembro',
            ]),
            'title'   => 'Editar Lista de Asistencia',
            'miembro' => $miembro,
        ]);
    }

    public function update($id)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $lista = $this->listaModel->find($id);
        if (!$lista || (int)$lista['id_cliente'] !== (int)$miembro['id_cliente'] || $lista['estado'] === 'completo') {
            if ($this->isAutosaveRequest()) return $this->autosaveJsonError('No editable', 404);
            return redirect()->to('/miembro/lista-asistencia');
        }

        $idComite = $this->request->getPost('id_comite');
        $this->listaModel->update($id, [
            'id_comite'        => $idComite ? (int)$idComite : null,
            'motivo'           => $this->request->getPost('motivo'),
            'fecha_actividad'  => $this->request->getPost('fecha_actividad'),
            'hora_inicio'      => $this->request->getPost('hora_inicio') ?: null,
            'hora_fin'         => $this->request->getPost('hora_fin') ?: null,
            'modalidad'        => $this->request->getPost('modalidad') ?: 'presencial',
            'convocada_por'    => $this->request->getPost('convocada_por'),
            'lugar'            => $this->request->getPost('lugar'),
            'agenda'           => $this->request->getPost('agenda'),
            'enlace_grabacion' => $this->request->getPost('enlace_grabacion'),
            'observaciones'    => $this->request->getPost('observaciones'),
        ]);

        $this->saveAsistentes((int)$id);

        if ($this->request->getPost('finalizar')) return $this->finalizar($id);
        if ($this->isAutosaveRequest()) return $this->autosaveJsonSuccess((int)$id);

        return redirect()->to('/miembro/lista-asistencia/edit/' . $id)->with('msg', 'Actualizado');
    }

    public function view($id)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $lista = $this->listaModel->find($id);
        if (!$lista || (int)$lista['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/lista-asistencia');
        }

        $clienteModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $realizadoPor = null;
        if ($lista['creado_por_tipo'] === 'miembro' && $lista['id_miembro']) {
            $m = $this->miembroModel->find($lista['id_miembro']);
            $realizadoPor = $m['nombre_completo'] ?? 'Miembro';
        }

        return view('inspecciones/miembro/layout_pwa_miembro', [
            'content' => view('inspecciones/miembro/lista_asistencia_view', [
                'lista'        => $lista,
                'cliente'      => $clienteModel->find($lista['id_cliente']),
                'consultor'    => $lista['id_consultor'] ? $consultantModel->find($lista['id_consultor']) : null,
                'realizadoPor' => $realizadoPor,
                'asistentes'   => $this->asistenteModel->getByLista((int)$id),
                'contexto'     => 'miembro',
            ]),
            'title'   => 'Ver Lista de Asistencia',
            'miembro' => $miembro,
        ]);
    }

    public function generarTokenFirma(int $idAsistente)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return $this->response->setJSON(['success' => false, 'error' => 'No autenticado']);

        $asistente = $this->asistenteModel->find($idAsistente);
        if (!$asistente) return $this->response->setJSON(['success' => false, 'error' => 'Asistente no encontrado']);

        $lista = $this->listaModel->find($asistente['id_lista_asistencia']);
        if (!$lista || (int)$lista['id_cliente'] !== (int)$miembro['id_cliente']) {
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

        $url = base_url("lista-asistencia/firmar-remoto/{$token}");
        return $this->response->setJSON(['success' => true, 'url' => $url, 'nombre' => $asistente['nombre_completo']]);
    }

    public function saveAsistente(int $idLista)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return $this->response->setJSON(['success' => false, 'error' => 'No autenticado']);

        $lista = $this->listaModel->find($idLista);
        if (!$lista || (int)$lista['id_cliente'] !== (int)$miembro['id_cliente']) {
            return $this->response->setJSON(['success' => false, 'error' => 'Sin acceso a la lista']);
        }
        if ($lista['estado'] === 'completo') {
            return $this->response->setJSON(['success' => false, 'error' => 'Lista ya finalizada']);
        }

        $nombre = trim((string)$this->request->getPost('nombre_completo'));
        if ($nombre === '') {
            return $this->response->setJSON(['success' => false, 'error' => 'Nombre requerido']);
        }

        $payload = [
            'id_lista_asistencia' => $idLista,
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
            && (int)$existente['id_lista_asistencia'] === $idLista) {
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

    public function enviarEmailFirma(int $idAsistente)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return $this->response->setJSON(['success' => false, 'error' => 'No autenticado']);

        $asistente = $this->asistenteModel->find($idAsistente);
        if (!$asistente) return $this->response->setJSON(['success' => false, 'error' => 'Asistente no encontrado']);

        $lista = $this->listaModel->find($asistente['id_lista_asistencia']);
        if (!$lista || (int)$lista['id_cliente'] !== (int)$miembro['id_cliente']) {
            return $this->response->setJSON(['success' => false, 'error' => 'Sin acceso']);
        }
        if (!empty($asistente['firma_path'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Este asistente ya firmó']);
        }
        if (empty($asistente['email'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Este asistente no tiene email registrado']);
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

        $cliente = (new ClientModel())->find($lista['id_cliente']);
        $ok = $this->enviarEmailFirmaListaAsistencia($asistente, $token, $lista, $cliente);

        return $this->response->setJSON([
            'success' => $ok,
            'email'   => $asistente['email'],
            'error'   => $ok ? null : 'No se pudo enviar el email',
        ]);
    }

    private function enviarEmailFirmaListaAsistencia(array $asistente, string $token, array $lista, ?array $cliente): bool
    {
        $urlFirma = base_url("lista-asistencia/firmar-remoto/{$token}");
        $motivo = esc($lista['motivo'] ?? '');
        $fecha = date('d/m/Y', strtotime($lista['fecha_actividad']));
        $modalidad = ucfirst($lista['modalidad'] ?? 'presencial');
        $nombreCliente = esc($cliente['nombre_cliente'] ?? '');
        $nombre = esc($asistente['nombre_completo']);

        $mensaje = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0;'>Solicitud de Firma - Lista de Asistencia</h2>
            </div>
            <div style='padding: 30px; background: #f8f9fa;'>
                <p>Estimado/a <strong>{$nombre}</strong>,</p>
                <p>Se requiere su firma electrónica para confirmar la asistencia a la siguiente convocatoria:</p>
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #bd9751;'>
                    <p style='margin: 5px 0;'><strong>Empresa:</strong> {$nombreCliente}</p>
                    <p style='margin: 5px 0;'><strong>Motivo:</strong> {$motivo}</p>
                    <p style='margin: 5px 0;'><strong>Fecha:</strong> {$fecha}</p>
                    <p style='margin: 5px 0;'><strong>Modalidad:</strong> {$modalidad}</p>
                </div>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$urlFirma}' style='background: #bd9751; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 16px; display: inline-block;'>
                        Firmar Lista de Asistencia
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
            $email->setSubject("Firma requerida: {$motivo} - {$nombreCliente}");
            $email->addTo($asistente['email'], $asistente['nombre_completo']);
            $email->addContent("text/html", $mensaje);
            $sg = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sg->send($email);
            return $response->statusCode() >= 200 && $response->statusCode() < 300;
        } catch (\Exception $e) {
            log_message('error', 'Error email firma lista asistencia: ' . $e->getMessage());
            return false;
        }
    }

    public function finalizar($id)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $lista = $this->listaModel->find($id);
        if (!$lista || (int)$lista['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/lista-asistencia');
        }

        $pdfPath = $this->generarPdfInterno((int)$id);
        if (!$pdfPath) return redirect()->back()->with('error', 'Error al generar PDF');

        $this->listaModel->update($id, ['estado' => 'completo', 'ruta_pdf' => $pdfPath]);

        $lista = $this->listaModel->find($id);
        $this->uploadToReportes($lista, $pdfPath);

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($miembro['id_cliente']);
        if (!empty($cliente['id_consultor'])) {
            $this->notificarConsultor($cliente, $miembro, $lista);
        }

        return redirect()->to('/miembro/lista-asistencia/view/' . $id)->with('msg', 'Lista finalizada.');
    }

    public function generatePdf($id)
    {
        $miembro = $this->getMiembroAny();
        if (!$miembro) return redirect()->to('/miembro/dashboard');

        $lista = $this->listaModel->find($id);
        if (!$lista || (int)$lista['id_cliente'] !== (int)$miembro['id_cliente']) {
            return redirect()->to('/miembro/lista-asistencia');
        }

        $pdfPath = $this->generarPdfInterno((int)$id);
        $fullPath = FCPATH . $pdfPath;

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="lista_asistencia_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    // ===== PRIVADOS =====

    private function saveAsistentes(int $idLista): void
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
        foreach ($this->asistenteModel->getByLista($idLista) as $a) {
            $existentes[$a['id']] = $a;
        }

        foreach ($nombres as $i => $nombre) {
            if (empty(trim($nombre))) continue;

            $existenteId = isset($ids[$i]) && $ids[$i] !== '' ? (int)$ids[$i] : null;
            $payload = [
                'id_lista_asistencia' => $idLista,
                'nombre_completo'     => trim($nombre),
                'tipo_documento'      => $tiposDoc[$i] ?? 'CC',
                'numero_documento'    => $numsDoc[$i] ?? null,
                'cargo'               => $cargos[$i] ?? null,
                'area_dependencia'    => $areas[$i] ?? null,
                'email'               => $emails[$i] ?? null,
                'celular'             => $celulares[$i] ?? null,
                'orden'               => $i + 1,
            ];

            if ($existenteId && isset($existentes[$existenteId])) {
                $this->asistenteModel->update($existenteId, $payload);
            } else {
                $this->asistenteModel->insert($payload);
            }
        }
    }

    public function generarTokenInscripcion(int $idLista)
    {
        $lista = $this->listaModel->find($idLista);
        if (!$lista) {
            return $this->response->setJSON(['success' => false, 'error' => 'Lista no encontrada']);
        }
        if ($lista['estado'] === 'completo') {
            return $this->response->setJSON(['success' => false, 'error' => 'Lista finalizada, no acepta inscripciones']);
        }

        $token = $lista['token_inscripcion'] ?? null;
        $regenerar = $this->request->getPost('regenerar') === '1';
        if (!$token || $regenerar) {
            $token = bin2hex(random_bytes(24));
            $this->listaModel->update($idLista, ['token_inscripcion' => $token]);
        }

        $url = base_url("lista-asistencia/inscripcion/{$token}");
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

    public function getAsistentesStatus(int $idLista)
    {
        $lista = $this->listaModel->find($idLista);
        if (!$lista) {
            return $this->response->setJSON(['success' => false, 'error' => 'Lista no encontrada']);
        }
        $asistentes = $this->asistenteModel->getByLista($idLista);
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

    public function deleteAsistente(int $idLista, int $idAsistente)
    {
        $lista = $this->listaModel->find($idLista);
        if (!$lista) {
            return $this->response->setJSON(['success' => false, 'error' => 'Lista no encontrada']);
        }
        if ($lista['estado'] === 'completo') {
            return $this->response->setJSON(['success' => false, 'error' => 'Lista finalizada']);
        }

        $asistente = $this->asistenteModel->find($idAsistente);
        if (!$asistente || (int)$asistente['id_lista_asistencia'] !== $idLista) {
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
        $lista = $this->listaModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($lista['id_cliente']);
        $consultor = $lista['id_consultor'] ? $consultantModel->find($lista['id_consultor']) : null;
        $asistentes = $this->asistenteModel->getByLista($id);

        $realizadoPor = null;
        if ($lista['creado_por_tipo'] === 'miembro' && $lista['id_miembro']) {
            $m = $this->miembroModel->find($lista['id_miembro']);
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

        $html = view('inspecciones/lista_asistencia/pdf', [
            'lista'        => $lista,
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

        $pdfDir = 'uploads/inspecciones/listas_asistencia/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) mkdir(FCPATH . $pdfDir, 0755, true);

        $pdfFileName = 'lista_asistencia_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        if (!empty($lista['ruta_pdf']) && file_exists(FCPATH . $lista['ruta_pdf'])) {
            unlink(FCPATH . $lista['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());
        return $pdfPath;
    }

    private function uploadToReportes(array $lista, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($lista['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'] ?? '';
        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $fileName = 'lista_asistencia_' . $lista['id'] . '_' . date('Ymd_His') . '.pdf';
        copy(FCPATH . $pdfPath, $destDir . '/' . $fileName);

        $data = [
            'titulo_reporte'  => 'LISTA DE ASISTENCIA - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $lista['fecha_actividad'],
            'id_detailreport' => 6,
            'id_report_type'  => 4,
            'id_cliente'      => $lista['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado por miembro. lista_asistencia_id:' . $lista['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $existente = $reporteModel->where('id_cliente', $lista['id_cliente'])
            ->where('id_report_type', 4)
            ->where('id_detailreport', 6)
            ->like('observaciones', 'lista_asistencia_id:' . $lista['id'])
            ->first();

        if ($existente) return $reporteModel->update($existente['id_reporte'], $data);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }

    private function notificarConsultor(array $cliente, array $miembro, array $lista): bool
    {
        $consultor = (new ConsultantModel())->find($cliente['id_consultor']);
        if (!$consultor || empty($consultor['correo_consultor'])) return false;

        $fecha = date('d/m/Y', strtotime($lista['fecha_actividad']));
        $motivo = esc($lista['motivo'] ?? '');
        $modalidad = ucfirst($lista['modalidad'] ?? 'presencial');
        $convocadaPor = esc($lista['convocada_por'] ?? '-');
        $lugar = esc($lista['lugar'] ?? '-');

        $mensaje = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0;'>Nueva Lista de Asistencia</h2>
            </div>
            <div style='padding: 30px; background: #f8f9fa;'>
                <p>Estimado/a <strong>{$consultor['nombre_consultor']}</strong>,</p>
                <p>Un miembro del comité ha registrado una lista de asistencia:</p>
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #bd9751;'>
                    <p style='margin: 5px 0;'><strong>Cliente:</strong> {$cliente['nombre_cliente']}</p>
                    <p style='margin: 5px 0;'><strong>Motivo:</strong> {$motivo}</p>
                    <p style='margin: 5px 0;'><strong>Fecha:</strong> {$fecha}</p>
                    <p style='margin: 5px 0;'><strong>Modalidad:</strong> {$modalidad}</p>
                    <p style='margin: 5px 0;'><strong>Convocada por:</strong> {$convocadaPor}</p>
                    <p style='margin: 5px 0;'><strong>Lugar:</strong> {$lugar}</p>
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
            $email->setSubject("Lista de Asistencia - {$cliente['nombre_cliente']} - {$fecha}");
            $email->addTo($consultor['correo_consultor'], $consultor['nombre_consultor']);
            $email->addContent("text/html", $mensaje);
            $response = (new \SendGrid(getenv('SENDGRID_API_KEY')))->send($email);
            return $response->statusCode() >= 200 && $response->statusCode() < 300;
        } catch (\Exception $e) {
            log_message('error', 'Error notificando consultor lista asistencia: ' . $e->getMessage());
            return false;
        }
    }
}
