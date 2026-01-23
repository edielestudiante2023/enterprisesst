<?php

namespace App\Controllers;

use App\Models\DashboardItemModel;

class CustomDashboardController extends BaseController
{
    public function index()
    {
        $session = session();

        // Instanciar el modelo y obtener los datos
        $model = new DashboardItemModel();
        $data['items'] = $model->findAll();

        // Obtener datos del usuario en sesión
        $userModel = new \App\Models\UserModel();
        $data['usuario'] = null;

        if ($session->get('id_usuario')) {
            $data['usuario'] = $userModel->find($session->get('id_usuario'));
        }

        // Cargar la vista principal del dashboard y pasarle los datos
        return view('consultant/admindashboard', $data);
    }
}
