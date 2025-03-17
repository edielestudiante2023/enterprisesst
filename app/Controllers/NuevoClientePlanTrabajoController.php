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

        // Obtener el cliente primero
        $cliente = $clientModel->find($id);
        $nombre_cliente = $cliente ? $cliente['nombre_cliente'] : 'No disponible';

        // Obtenemos los planes de trabajo relacionados con el ID proporcionado
        $planes = $ptaModel->asArray()->where('id_cliente', $id)->findAll();

        foreach ($planes as &$plan) {
            // Intentamos obtener información de la actividad
            $actividad = $actividadesModel->asArray()
                ->where('actividad_plandetrabajo', $plan['actividad_plandetrabajo'])
                ->first();

            // Formatear fechas a dd-mm-yyyy
            if (!empty($plan['fecha_propuesta'])) {
                $fecha = new \DateTime($plan['fecha_propuesta']);
                $plan['fecha_propuesta'] = $fecha->format('d-m-Y');
            }
            if (!empty($plan['fecha_cierre'])) {
                $fecha = new \DateTime($plan['fecha_cierre']);
                $plan['fecha_cierre'] = $fecha->format('d-m-Y');
            }

            // Asignamos la información a la variable del plan
            $plan['nombre_cliente'] = $nombre_cliente;
            $plan['nombre_actividad'] = $actividad ? $actividad['actividad_plandetrabajo'] : $plan['actividad_plandetrabajo'];
            $plan['numeral_actividad'] = $actividad ? $actividad['numeral_plandetrabajo'] : 'No disponible';
        }

        // Ordenar los planes por estado, fecha y PHVA
        usort($planes, function($a, $b) {
            // Primero por estado
            $cmp = strcmp($a['estado_actividad'], $b['estado_actividad']);
            if ($cmp !== 0) return $cmp;
            
            // Luego por fecha propuesta
            $date1 = strtotime($a['fecha_propuesta']);
            $date2 = strtotime($b['fecha_propuesta']);
            if ($date1 !== $date2) return $date1 - $date2;
            
            // Finalmente por PHVA
            return strcmp($a['phva_plandetrabajo'], $b['phva_plandetrabajo']);
        });

        // Pasar los planes y el nombre del cliente a la vista
        return view('client/list_plan_trabajo', [
            'planes' => $planes,
            'nombre_cliente' => $nombre_cliente
        ]);
    }
}
