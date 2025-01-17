<?php

namespace App\Controllers;

use App\Models\DashboardItemModel;

class CustomDashboardController extends BaseController
{
    public function index()
    {
        // Instanciar el modelo y obtener los datos
        $model = new DashboardItemModel();
        $data['items'] = $model->findAll();

        // Cargar la vista principal del dashboard y pasarle los datos
        return view('consultant/admindashboard', $data);
    }
}
