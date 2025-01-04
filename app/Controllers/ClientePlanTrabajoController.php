<?php


namespace App\Controllers;

use App\Models\PtaclienteModel;
use App\Models\ClientModel;
use App\Models\InventarioActividadesArrayModel; // Usamos el nuevo modelo
use CodeIgniter\Controller;

class ClientePlanTrabajoController extends Controller
{
    public function listPlanTrabajoCliente($id_cliente)
    {
        $ptaModel = new PtaclienteModel();
        $clientModel = new ClientModel();
        $actividadesModel = new InventarioActividadesArrayModel(); // Usamos el nuevo modelo que devuelve arrays

        // Obtener planes de trabajo por cliente
        $planes = $ptaModel->where('id_cliente', $id_cliente)->findAll();

        // Obtener nombres de cliente y actividad
        foreach ($planes as &$plan) {
            // Acceso como array
            $cliente = $clientModel->find($plan['id_cliente']);
            $actividad = $actividadesModel->find($plan['id_plandetrabajo']); // Buscar actividad usando el ID del plan de trabajo

            // Acceso a los valores del cliente y la actividad como arrays
            $plan['nombre_cliente'] = $cliente ? $cliente['nombre_cliente'] : 'No disponible';
            $plan['nombre_actividad'] = $actividad ? $actividad['actividad_plandetrabajo'] : 'No disponible';
            $plan['numeral_actividad'] = $actividad ? $actividad['numeral_plandetrabajo'] : 'No disponible'; // Añadimos el numeral también
        }

        // Enviar los planes a la vista
        return view('client/list_plan_trabajo', [
            'planes' => $planes
        ]);
    }
}
