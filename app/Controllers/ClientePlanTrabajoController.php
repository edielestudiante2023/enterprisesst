<?php

namespace App\Controllers;

use App\Models\PtaclienteModel;
use App\Models\ClientModel;
use App\Models\InventarioActividadesArrayModel;
use CodeIgniter\Controller;

class ClientePlanTrabajoController extends Controller
{
    public function listPlanTrabajoCliente($id_cliente)
    {
        $ptaModel = new PtaclienteModel();
        $clientModel = new ClientModel();
        $actividadesModel = new InventarioActividadesArrayModel();

        // Obtener el cliente primero
        $cliente = $clientModel->find($id_cliente);
        $nombre_cliente = $cliente ? $cliente['nombre_cliente'] : 'No disponible';

        // Obtener planes de trabajo por cliente
        $planes = $ptaModel->where('id_cliente', $id_cliente)->findAll();

        // Obtener nombres de actividad
        foreach ($planes as &$plan) {
            $actividad = $actividadesModel->find($plan['id_plandetrabajo']);
            $plan['nombre_actividad'] = $actividad ? $actividad['actividad_plandetrabajo'] : 'No disponible';
            $plan['numeral_actividad'] = $actividad ? $actividad['numeral_plandetrabajo'] : 'No disponible';
            $plan['nombre_cliente'] = $nombre_cliente; // Add client name to each plan
        }

        // Enviar los planes y el nombre del cliente a la vista
        return view('client/list_plan_trabajo', [
            'planes' => $planes,
            'nombre_cliente' => $nombre_cliente // Pass client name directly
        ]);
    }
}
