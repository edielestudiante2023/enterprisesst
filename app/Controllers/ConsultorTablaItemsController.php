<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\DashboardItemModel;

class ConsultorTablaItemsController extends Controller
{
    public function index()
    {
        $session = session();
        $model = new DashboardItemModel();

        $data['items'] = $model->where('orden >=', 1)
            ->where('orden <=', 5)
            ->findAll();

        // Obtener datos del usuario en sesión
        $userModel = new \App\Models\UserModel();
        $data['usuario'] = null;

        if ($session->get('id_usuario')) {
            $data['usuario'] = $userModel->find($session->get('id_usuario'));
        }

        return view('consultant/dashboard', $data);
    }
}
