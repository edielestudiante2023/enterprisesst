<?php

namespace App\Controllers;

use App\Models\CronogcapacitacionModel;
use App\Models\ClientModel;
use App\Models\CapacitacionModel;
use CodeIgniter\Controller;

class CronogramaCapacitacionController extends Controller
{
    // Mostrar cronogramas del cliente
    public function listCronogramasCliente($id_cliente)
    {
        $cronogcapacitacionModel = new CronogcapacitacionModel();
        $clientModel = new ClientModel(); // Modelo para los clientes
        $capacitacionModel = new CapacitacionModel(); // Modelo para las capacitaciones
    
        // Obtener cronogramas filtrados por el id_cliente de la sesión activa
        $cronogramas = $cronogcapacitacionModel->where('id_cliente', $id_cliente)->findAll();

        // Obtener nombres de cliente y capacitaciones
        foreach ($cronogramas as &$cronograma) {
            $cronograma['nombre_cliente'] = $clientModel->find($cronograma['id_cliente'])['nombre_cliente'] ?? 'No disponible';
            $cronograma['nombre_capacitacion'] = $capacitacionModel->find($cronograma['id_capacitacion'])['capacitacion'] ?? 'No disponible';

        }

        // Envío de datos a la vista
        return view('client/list_cronogramas', [
            'cronogramas' => $cronogramas
        ]);
    }
}
