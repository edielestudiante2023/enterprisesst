<?php

namespace App\Controllers;

use App\Models\PendientesModel;
use App\Models\ClientModel; // Para obtener información del cliente
use CodeIgniter\Controller;

class ClientePendientesController extends Controller
{
    // Listar todos los pendientes del cliente
    public function listPendientesCliente($id_cliente)
{
    $pendientesModel = new PendientesModel();
    $clientModel = new ClientModel();

    // Verificación de la existencia del cliente
    $client = $clientModel->find($id_cliente);
    
    if (!$client) {
        return redirect()->to('/dashboardclient')->with('error', 'Cliente no encontrado.');
    }

    // Obtener los pendientes del cliente
    $pendientes = $pendientesModel->where('id_cliente', $id_cliente)->findAll();

    // Agregar el nombre del cliente a cada pendiente
    foreach ($pendientes as &$pendiente) {
        $pendiente['nombre_cliente'] = $client['nombre_cliente'];
    }

    $data['pendientes'] = $pendientes;

    return view('client/list_pendientes', $data); // Cargar la vista con los datos
}

}
