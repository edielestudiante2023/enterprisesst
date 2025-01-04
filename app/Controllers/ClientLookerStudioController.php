<?php

namespace App\Controllers;

use App\Models\LookerStudioModel;
use CodeIgniter\Controller;

class ClientLookerStudioController extends Controller
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
        $lookerStudioModel = new LookerStudioModel();
        $lookerStudios = $lookerStudioModel->where('id_cliente', $clientId)->orderBy('id_looker', 'DESC')->findAll();

        // Pasar los datos a la vista
        $data = [
            'lookerStudios' => $lookerStudios
        ];

        return view('client/lista_lookerstudio', $data);
    }
}
