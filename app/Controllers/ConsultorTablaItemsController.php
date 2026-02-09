<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\DashboardItemModel;
use App\Models\ClientModel;
use App\Models\AccesoModel;
use App\Models\EstandarModel;
use App\Models\EstandarAccesoModel;

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

    /**
     * Vista selector de clientes para consultor/admin
     */
    public function selectorCliente()
    {
        $session = session();
        $role = $session->get('role');

        if (!in_array($role, ['consultant', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $data['clientes'] = $clientModel->where('estado', 'activo')->findAll();

        return view('consultant/selector_vista_cliente', $data);
    }

    /**
     * Ver el dashboard de un cliente como consultor/admin
     */
    public function vistaCliente($idCliente)
    {
        $session = session();
        $role = $session->get('role');

        if (!in_array($role, ['consultant', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($idCliente);

        if (!$client) {
            return redirect()->back()->with('error', 'Cliente no encontrado.');
        }

        // Replicar la lógica de ClientController::dashboard()
        $accesos = [];
        $estandarNombre = $client['estandares'] ?? null;

        if ($estandarNombre) {
            $estandarModel = new EstandarModel();
            $estandar = $estandarModel->where('nombre', $estandarNombre)->first();

            if ($estandar) {
                $estandarAccesoModel = new EstandarAccesoModel();
                $accesosData = $estandarAccesoModel->where('id_estandar', $estandar['id_estandar'])->findAll();

                if (!empty($accesosData)) {
                    $accesoModel = new AccesoModel();
                    $accesos = $accesoModel
                        ->whereIn('id_acceso', array_column($accesosData, 'id_acceso'))
                        ->findAll();

                    $orden = ["Planear", "Hacer", "Verificar", "Actuar", "Indicadores"];
                    usort($accesos, function ($a, $b) use ($orden) {
                        return array_search($a['dimension'], $orden) - array_search($b['dimension'], $orden);
                    });
                }
            }
        }

        return view('client/dashboard', [
            'accesos' => $accesos,
            'client'  => $client,
            'vistaConsultor' => true,
        ]);
    }
}
