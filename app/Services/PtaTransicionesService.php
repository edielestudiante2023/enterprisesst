<?php

namespace App\Services;

use App\Models\PtaTransicionesModel;

class PtaTransicionesService
{
    public static function registrar(
        int $idPtaCliente,
        int $idCliente,
        string $estadoAnterior,
        string $estadoNuevo
    ): bool {
        if ($estadoAnterior === '' || $estadoAnterior === null) {
            $estadoAnterior = 'ABIERTA';
        }

        if ($estadoAnterior !== 'ABIERTA' || $estadoNuevo === 'ABIERTA') {
            return false;
        }

        try {
            $session = session();
            $idUsuario     = $session->get('id_usuario') ?? $session->get('user_id') ?? 0;
            $nombreUsuario = $session->get('nombre') ?? $session->get('nombre_usuario') ?? $session->get('username') ?? 'Sistema';

            $model = new PtaTransicionesModel();
            return (bool) $model->insert([
                'id_ptacliente'    => $idPtaCliente,
                'id_cliente'       => $idCliente,
                'estado_anterior'  => $estadoAnterior,
                'estado_nuevo'     => $estadoNuevo,
                'id_usuario'       => $idUsuario,
                'nombre_usuario'   => $nombreUsuario,
                'fecha_transicion' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'PtaTransicionesService::registrar error: ' . $e->getMessage());
            return false;
        }
    }
}
