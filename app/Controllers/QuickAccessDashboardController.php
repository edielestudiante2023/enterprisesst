<?php

namespace App\Controllers;

use App\Models\ClientModel;
use CodeIgniter\Controller;

class QuickAccessDashboardController extends Controller
{
    public function index()
    {
        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();

        $data = [
            'clients' => $clients
        ];

        return view('consultant/quick_access_dashboard', $data);
    }
}
