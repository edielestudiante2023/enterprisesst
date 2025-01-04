<?php

namespace App\Controllers;

use App\Models\MatrizModel;
use CodeIgniter\Controller;

class ClientMatrices extends Controller
{
    public function index()
    {
        // Obtener el ID del cliente desde la sesión
        $session = session();
        $clientId = $session->get('user_id');

        // Verificar que el ID del cliente sea válido
        if (is_null($clientId)) {
            return redirect()->to('/login')->with('error', 'Sesión inválida. Inicia sesión nuevamente.');
        }

        // Instanciar el modelo y recuperar los dashboards del cliente
        $matrizModel = new MatrizModel();
        $matrices = $matrizModel->where('id_cliente', $clientId)->orderBy('id_matriz', 'DESC')->findAll();

        // Pasar los datos a la vista
        $data = [
            'matrices' => $matrices
        ];

        return view('client/lista_matrices', $data);
    }
}
