<?php

namespace App\Controllers;

use App\Models\EvaluationModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class ClienteEvaluacionController extends Controller
{
    public function listEvaluacionesCliente($id_cliente)
    {
        $evaluationModel = new EvaluationModel();
        $clientModel = new ClientModel();

        $client = $clientModel->find($id_cliente);
        if (!$client) {
            return redirect()->to('/dashboardclient')->with('error', 'Cliente no encontrado.');
        }

        $evaluaciones = $evaluationModel->where('id_cliente', $id_cliente)->findAll();

        return view('client/list_evaluaciones', [
            'evaluaciones' => $evaluaciones,
            'client' => $client
        ]);
    }
}
