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
        if (session()->get('role') !== 'client') {
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
        $idCliente = (int) (session()->get('id_cliente') ?? session()->get('user_id') ?? 0);

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
