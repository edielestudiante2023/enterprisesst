<?php

namespace App\Controllers;

use App\Models\DocumentVersionModel;
use App\Models\ClientModel;

class ClientDocumentController extends BaseController
{
    // Mostrar la política más reciente para el cliente en sesión
    public function viewPolicy($policyTypeId)
    {
        // Obtener el ID del cliente desde la sesión
        $session = session();
        $clientId = $session->get('user_id'); // ID del cliente

        $documentVersionModel = new DocumentVersionModel();
        
        // Obtener la versión más reciente de la política específica para el cliente
        $latestVersion = $documentVersionModel->where('client_id', $clientId)
                                              ->where('policy_type_id', $policyTypeId)
                                              ->orderBy('created_at', 'DESC')
                                              ->first();

        if (!$latestVersion) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontraron versiones para esta política.');
        }

        $data = [
            'version_number' => $latestVersion['version_number'],
            'version_date' => $latestVersion['created_at']
        ];

        return view('client/sgsst/1planear/no_alcohol_drogas', $data);
    }
}
