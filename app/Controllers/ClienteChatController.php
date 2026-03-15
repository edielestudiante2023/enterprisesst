<?php

namespace App\Controllers;

use App\Services\AgenteChatService;

/**
 * Chat Otto para clientes — solo lectura (SELECT).
 * El cliente solo puede consultar datos de su propia empresa.
 */
class ClienteChatController extends BaseController
{
    protected AgenteChatService $service;

    public function __construct()
    {
        $this->service = new AgenteChatService();
    }

    // ─── Vista principal ──────────────────────────────────────────
    public function index()
    {
        if (session()->get('role') !== 'client') {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado');
        }

        // Obtener nombre de la empresa del cliente
        $idCliente = session()->get('id_cliente') ?? session()->get('user_id');
        $db = \Config\Database::connect();
        $nombreEmpresa = $db->table('tbl_cliente')
            ->select('nombre_empresa')
            ->where('id_cliente', $idCliente)
            ->get()->getRow()?->nombre_empresa ?? 'tu empresa';

        return view('cliente_chat/index', [
            'title'         => 'Otto - Asistente Virtual',
            'nombre_empresa' => $nombreEmpresa,
        ]);
    }

    // ─── API: enviar mensaje (solo SELECT) ────────────────────────
    public function enviarMensaje()
    {
        if (session()->get('role') !== 'client') {
            return $this->response->setJSON(['success' => false, 'mensaje' => 'No autorizado'])->setStatusCode(403);
        }

        $input     = $this->request->getJSON(true);
        $mensaje   = trim($input['mensaje'] ?? '');
        $historial = $input['historial'] ?? [];

        if (empty($mensaje)) {
            return $this->response->setJSON(['success' => false, 'mensaje' => 'Mensaje vacío']);
        }

        $idCliente     = session()->get('id_cliente') ?? session()->get('user_id');
        $db            = \Config\Database::connect();
        $nombreEmpresa = $db->table('tbl_cliente')
            ->select('nombre_empresa')
            ->where('id_cliente', $idCliente)
            ->get()->getRow()?->nombre_empresa ?? 'cliente';

        $usuario = [
            'id'             => session()->get('id_usuario') ?? $idCliente,
            'rol'            => 'client',
            'id_cliente'     => $idCliente,
            'nombre_empresa' => $nombreEmpresa,
            'sesion_chat'    => $input['sesion_chat'] ?? '',
        ];

        // soloLectura = true: rechaza cualquier escritura
        $resultado = $this->service->procesarMensaje($mensaje, $historial, $usuario, true);
        return $this->response->setJSON($resultado);
    }
}
