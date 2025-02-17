<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\DashboardItemModel;

class ConsultorTablaItemsController extends Controller
{
    public function index()
    {
        $model = new DashboardItemModel();
        $data['items'] = $model->where('orden >=', 1)
            ->where('orden <=', 5)
            ->findAll();

        // Depuración temporal: imprime el array de items y detiene la ejecución.
        // echo '<pre>'; print_r($data['items']); exit;

        return view('consultant/dashboard', $data);
    }
}
