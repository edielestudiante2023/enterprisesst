<?php

namespace App\Controllers;

use App\Services\AgenteChatService;

/**
 * Chat Otto para clientes — triple capa de seguridad.
 *
 * CAPA 1 (DB)     : Conexión 'readonly' — empresas_readonly solo tiene
 *                   GRANT SELECT sobre v_* y catálogos. Físicamente no puede escribir.
 * CAPA 2 (App)    : queryContainsClientScope() valida que el SQL incluya
 *                   el id_cliente del cliente logueado antes de ejecutar.
 * CAPA 3 (Prompt) : System prompt exige WHERE id_cliente = {$idCliente}
 *                   en todo SELECT.
 */
class ClienteChatController extends BaseController
{
    protected AgenteChatService $service;
    protected int $idCliente;
    protected string $nombreEmpresa;

    public function __construct()
    {
        // Capa 1: conexión readonly
        $this->service = new AgenteChatService('readonly');
    }

    // ─── Vista principal ──────────────────────────────────────────
    public function index()
    {
        $role = session()->get('role');

        // Consultor/admin puede previsualizar el chat de un cliente específico vía ?id_cliente=X
        // Sin ese parámetro no tiene contexto — redirigir al dashboard
        // Ref: aprendizaje #8 — consultor sin id_cliente → id_cliente=0 → guardrail bloquea todo
        if (in_array($role, ['consultant', 'admin'])) {
            $idParam = (int) $this->request->getGet('id_cliente');
            if (!$idParam) {
                return redirect()->to('/consultor/dashboard')
                    ->with('error', 'Selecciona un cliente para abrir su chat con Otto.');
            }
            // Almacenar temporalmente en sesión para la previsuación
            session()->set('preview_id_cliente', $idParam);
        }

        if (!in_array($role, ['client', 'consultant', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado');
        }

        [$idCliente, $nombreEmpresa] = $this->resolverCliente();

        return view('cliente_chat/index', [
            'title'          => 'Otto - Asistente Virtual',
            'nombre_empresa' => $nombreEmpresa,
        ]);
    }

    // ─── API: enviar mensaje ──────────────────────────────────────
    public function enviarMensaje()
    {
        if (session()->get('role') !== 'client') {
            return $this->response
                ->setJSON(['success' => false, 'mensaje' => 'No autorizado', 'tipo' => 'error'])
                ->setStatusCode(403);
        }

        $input     = $this->request->getJSON(true);
        $mensaje   = trim($input['mensaje'] ?? '');
        $historial = $input['historial'] ?? [];

        if (empty($mensaje)) {
            return $this->response->setJSON(['success' => false, 'mensaje' => 'Mensaje vacío', 'tipo' => 'error']);
        }

        [$idCliente, $nombreEmpresa] = $this->resolverCliente();

        $usuario = [
            'id'             => session()->get('id_usuario') ?? $idCliente,
            'rol'            => 'client',
            'id_cliente'     => $idCliente,
            'nombre_empresa' => $nombreEmpresa,
            'sesion_chat'    => $input['sesion_chat'] ?? '',
        ];

        // soloLectura = true → AgenteChatService valida SQL y usa buildSystemPromptCliente()
        $resultado = $this->service->procesarMensaje($mensaje, $historial, $usuario, true);

        // Capa 2: si el service retornó SQL a ejecutar (tipo resultado), verificar scope
        if (($resultado['tipo'] ?? '') === 'resultado' && !empty($resultado['sql'])) {
            if (!$this->queryContainsClientScope($resultado['sql'], $idCliente)) {
                return $this->response->setJSON([
                    'success' => false,
                    'mensaje' => 'Solo puedo mostrarte información de tu empresa. Intenta reformular la pregunta.',
                    'tipo'    => 'rechazado',
                ]);
            }
        }

        return $this->response->setJSON($resultado);
    }

    // ─── API: finalizar sesión y enviar email resumen ─────────────
    public function endSession()
    {
        $session = session();
        if (!in_array($session->get('role'), ['client', 'consultant', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado'])->setStatusCode(401);
        }

        $input   = $this->request->getJSON(true) ?? $this->request->getPost();
        $history = $input['history'] ?? [];

        if (empty($history)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Sin conversación que enviar']);
        }

        // Obtener nombre y email del usuario desde la DB
        $idUsuario = (int) $session->get('id_usuario');
        $db        = \Config\Database::connect('default');
        $u         = $db->table('tbl_usuarios')
            ->select('nombre_completo, email')
            ->where('id_usuario', $idUsuario)
            ->get()->getRow();

        $userName  = $u?->nombre_completo ?? 'Cliente';
        $userEmail = $u?->email ?? '';
        $now       = date('d/m/Y H:i');

        $transcriptHtml = '';
        foreach ($history as $msg) {
            $role    = ($msg['role'] ?? '') === 'user' ? $userName : 'Otto';
            $content = nl2br(htmlspecialchars($msg['content'] ?? ''));
            $bg      = ($msg['role'] ?? '') === 'user' ? '#f0f4ff' : '#f9f9f9';
            $align   = ($msg['role'] ?? '') === 'user' ? 'right' : 'left';
            $transcriptHtml .= "
            <tr>
                <td style='padding:8px 12px; background:{$bg}; text-align:{$align}; border-bottom:1px solid #eee;'>
                    <strong style='color:#1c2437;'>{$role}:</strong><br>
                    <span style='color:#333; font-size:14px;'>{$content}</span>
                </td>
            </tr>";
        }

        $html = "
        <div style='font-family:Arial,sans-serif;max-width:700px;margin:0 auto;padding:20px;'>
            <div style='text-align:center;margin-bottom:24px;'>
                <h2 style='color:#1c2437;margin:0;'>Resumen de sesión con Otto</h2>
                <p style='color:#bd9751;font-size:13px;margin:4px 0 0;'>Empresas SST · {$now}</p>
            </div>
            <p style='color:#333;'>El cliente <strong>{$userName}</strong> tuvo la siguiente conversación con Otto:</p>
            <table width='100%' cellpadding='0' cellspacing='0' style='border:1px solid #ddd;border-radius:8px;overflow:hidden;'>
                {$transcriptHtml}
            </table>
            <hr style='border:none;border-top:1px solid #e0e0e0;margin:24px 0;'>
            <p style='color:#999;font-size:12px;text-align:center;'>
                Cycloid Talent SAS · <a href='https://cycloidtalent.com' style='color:#bd9751;'>www.cycloidtalent.com</a>
            </p>
        </div>";

        try {
            $mail = new \SendGrid\Mail\Mail();
            $mail->setFrom('notificacion.cycloidtalent@cycloidtalent.com', 'Otto · Cycloid Talent');
            $mail->setSubject("Resumen sesión Otto · {$userName} · {$now}");
            if ($userEmail) {
                $mail->addTo($userEmail, $userName);
            }
            $mail->addCc('otto.chat@cycloidtalent.com', 'Otto Chat Log');
            $mail->addContent('text/html', $html);

            $sg       = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sg->send($mail);
            $sent     = $response->statusCode() >= 200 && $response->statusCode() < 300;

            return $this->response->setJSON(['success' => $sent]);
        } catch (\Throwable $e) {
            log_message('error', 'ClienteChatController::endSession email error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── Capa 2: validar que el SQL incluya el id_cliente ─────────
    protected function queryContainsClientScope(string $sql, int $idCliente): bool
    {
        // Verifica que el SQL mencione el id_cliente del cliente logueado
        // ya sea como literal numérico o como parte de una cláusula WHERE/AND
        return (bool) preg_match(
            '/\bid_cliente\s*=\s*' . $idCliente . '\b/i',
            $sql
        );
    }

    // ─── Helper: obtener id y nombre del cliente logueado ─────────
    protected function resolverCliente(): array
    {
        $role = session()->get('role');
        if (in_array($role, ['consultant', 'admin'])) {
            $idCliente = (int) (session()->get('preview_id_cliente') ?? 0);
        } else {
            $idCliente = (int) (session()->get('id_cliente') ?? session()->get('user_id') ?? 0);
        }

        // Usar conexión default para leer datos del cliente (readonly no tiene acceso a tbl_cliente)
        $db            = \Config\Database::connect('default');
        $row           = $db->table('tbl_clientes')
            ->select('nombre_cliente')
            ->where('id_cliente', $idCliente)
            ->get()->getRow();
        $nombreEmpresa = $row?->nombre_cliente ?? 'tu empresa';

        return [$idCliente, $nombreEmpresa];
    }
}
