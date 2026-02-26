<?php

namespace App\Controllers;

use App\Models\VencimientosMantenimientoModel;
use App\Models\ClientModel;
use App\Models\MantenimientoModel;

class VencimientosClienteController extends BaseController
{
    public function listVencimientosCliente($id_cliente)
    {
        $vencimientosModel = new VencimientosMantenimientoModel();
        $clientModel = new ClientModel();
        $mantenimientoModel = new MantenimientoModel();

        // Obtener vencimientos del cliente
        $vencimientos = $vencimientosModel->where('id_cliente', $id_cliente)->findAll();

        // Obtener nombre del cliente
        $cliente = $clientModel->find($id_cliente);

        // Agregar detalles del mantenimiento a cada vencimiento
        foreach ($vencimientos as &$vencimiento) {
            $mantenimiento = $mantenimientoModel->find($vencimiento['id_mantenimiento']);
            $vencimiento['detalle_mantenimiento'] = $mantenimiento['detalle_mantenimiento'] ?? 'No especificado';
        }

        // Enviar datos a la vista
        return view('client/list_vencimientos', [
            'vencimientos' => $vencimientos,
            'cliente' => $cliente['nombre_cliente'] ?? 'Cliente no encontrado',
        ]);
    }
}
