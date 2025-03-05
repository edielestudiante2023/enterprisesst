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
        $cronogramas = $cronogcapacitacionModel
            ->select('cronogcapacitacion.*, clients.nombre_cliente, capacitaciones.capacitacion')
            ->join('clients', 'clients.id_cliente = cronogcapacitacion.id_cliente')
            ->join('capacitaciones', 'capacitaciones.id_capacitacion = cronogcapacitacion.id_capacitacion')
            ->where('cronogcapacitacion.id_cliente', $id_cliente)
            ->findAll();

        // Obtener nombres de cliente y capacitaciones
        foreach ($cronogramas as &$cronograma) {
            $cronograma['nombre_cliente'] = $cronograma['nombre_cliente'] ?? 'No disponible';
            $cronograma['nombre_capacitacion'] = $cronograma['capacitacion'] ?? 'No disponible';
        }

        // Envío de datos a la vista
        return view('client/list_cronogramas', [
            'cronogramas' => $cronogramas
        ]);
    }

    // Método para obtener cronogramas con detalles para DataTables
    public function getCronogramasAjax()
    {
        $request = \Config\Services::request();
        $id_cliente = $request->getPost('id_cliente'); // Assuming the client ID is sent in the request

        // Fetch cronogramas with details
        $cronogcapacitacionModel = new CronogcapacitacionModel();
        $data = $cronogcapacitacionModel->getCronogramasWithDetails($id_cliente);

        // Prepare response for DataTables
        $response = [
            "draw" => intval($request->getPost('draw')),
            "recordsTotal" => count($data),
            "recordsFiltered" => count($data), // Adjust this if you implement filtering
            "data" => $data
        ];

        return $this->response->setJSON($response);
    }
}
