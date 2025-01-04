<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class ClientDashboardController extends Controller
{
    public function index()
    {
        // Verificar si el usuario está autenticado y es consultor
        if (!session()->get('isLoggedIn') || session()->get('rol') !== 'consultant') {
            return redirect()->to('/login');
        }

        // Puedes cargar datos específicos para el consultor aquí
        return view('client/dashboard');
    }
}
