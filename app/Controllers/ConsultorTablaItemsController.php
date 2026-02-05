<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\DashboardItemModel;
use App\Models\ClientModel;

class ConsultorTablaItemsController extends Controller
{
    public function index()
    {
        $session = session();
        $model = new DashboardItemModel();
        $clientModel = new ClientModel();

        $data['items'] = $model->where('orden >=', 1)
            ->where('orden <=', 5)
            ->findAll();

        // Obtener todos los clientes activos para los selectores
        $data['clientes'] = $clientModel->where('estado', 'activo')->findAll();

        // Obtener datos del usuario en sesión
        $userModel = new \App\Models\UserModel();
        $data['usuario'] = null;

        if ($session->get('id_usuario')) {
            $data['usuario'] = $userModel->find($session->get('id_usuario'));
        }

        return view('consultant/dashboard', $data);
    }
}
