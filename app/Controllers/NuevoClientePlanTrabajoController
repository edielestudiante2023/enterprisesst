<?php

namespace App\Controllers;

use App\Models\PtaclienteModel;
use App\Models\ClientModel;
use App\Models\InventarioActividadesArrayModel;

class NuevoClientePlanTrabajoController extends BaseController
{
    public function nuevoListPlanTrabajoCliente($id)
    {
        $ptaModel = new PtaclienteModel();
        $clientModel = new ClientModel();
        $actividadesModel = new InventarioActividadesArrayModel();

        // Obtenemos los planes de trabajo relacionados con el ID proporcionado
        $planes = $ptaModel->asArray()->where('id_cliente', $id)->findAll();

        foreach ($planes as &$plan) {
            // Obtener información adicional del cliente y la actividad
            $cliente = $clientModel->asArray()->find($plan['id_cliente']);
            $actividad = $actividadesModel->asArray()->find($plan['id_plandetrabajo']);

            $plan['nombre_cliente'] = $cliente ? $cliente['nombre_cliente'] : 'No disponible';
            $plan['nombre_actividad'] = $actividad ? $actividad['actividad_plandetrabajo'] : 'No disponible';
            $plan['numeral_actividad'] = $actividad ? $actividad['numeral_plandetrabajo'] : 'No disponible';
        }

        // Pasar los planes a la vista
        return view('client/list_plan_trabajo', [
            'planes' => $planes,
        ]);
    }
}
