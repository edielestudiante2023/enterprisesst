<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ClientPoliciesModel;
use App\Models\DocumentVersionModel;
use App\Models\PolicyTypeModel;
use App\Models\VigiaModel; // Importamos el modelo de Vigias

use Dompdf\Dompdf;

use CodeIgniter\Controller;

class PzvigiaController extends Controller
{
    public function asignacionVigia()
    {
        // Obtener el ID del cliente desde la sesión
        $session = session();
        $clientId = $session->get('user_id'); // Asegúrate de que este ID es el del cliente

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $clientPoliciesModel = new ClientPoliciesModel();
        $policyTypeModel = new PolicyTypeModel();
        $versionModel = new DocumentVersionModel();
        $vigiaModel = new VigiaModel(); // Instanciar el modelo de Vigias

        // Obtener los datos del cliente
        $client = $clientModel->find($clientId);
        if (!$client) {
            return redirect()->to('/dashboardclient')->with('error', 'No se pudo encontrar la información del cliente');
        }

        // Obtener los datos del consultor relacionado con el cliente
        $consultant = $consultantModel->find($client['id_consultor']);
        if (!$consultant) {
            return redirect()->to('/dashboardclient')->with('error', 'No se pudo encontrar la información del consultor');
        }

        // Obtener la política de alcohol y drogas del cliente
        $policyTypeId = 5; // ID de la política correspondiente
        $clientPolicy = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('id', 'DESC')
            ->first();
        if (!$clientPolicy) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento para este cliente.');
        }

        // Obtener el tipo de política
        $policyType = $policyTypeModel->find($policyTypeId);

        // Obtener la versión más reciente del documento
        $latestVersion = $versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$latestVersion) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró un versionamiento para este documento de este cliente.');
        }

        // Obtener todas las versiones del documento
        $allVersions = $versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        if (!$allVersions) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró un versionamiento para este documento de este cliente.');
        }

        // Obtener el vigía más reciente relacionado con el cliente (si existe)
        $latestVigia = $vigiaModel->where('id_cliente', $clientId)
            ->orderBy('created_at', 'ASC')
            ->first();

        // Preparar los datos a enviar a la vista (latestVigia puede ser null)
        $data = [
            'client'        => $client,
            'consultant'    => $consultant,
            'clientPolicy'  => $clientPolicy,
            'policyType'    => $policyType,
            'latestVersion' => $latestVersion,
            'allVersions'   => $allVersions,
            'latestVigia'   => $latestVigia
        ];

        return view('client/sgsst/1planear/p1_1_3vigia', $data);
    }

    public function generatePdf_asignacionVigia()
    {
        // Instanciar Dompdf
        $dompdf = new Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);

        // Obtener los mismos datos que en la función asignacionVigia
        $session = session();
        $clientId = $session->get('user_id');

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $clientPoliciesModel = new ClientPoliciesModel();
        $policyTypeModel = new PolicyTypeModel();
        $versionModel = new DocumentVersionModel();
        $vigiaModel = new VigiaModel();

        // Obtener los datos necesarios
        $client = $clientModel->find($clientId);
        $consultant = $consultantModel->find($client['id_consultor']);
        $policyTypeId = 5;
        $clientPolicy = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('id', 'DESC')
            ->first();
        $policyType = $policyTypeModel->find($policyTypeId);
        $latestVersion = $versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->first();
        $allVersions = $versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Obtener el vigía más reciente relacionado con el cliente (si existe)
        $latestVigia = $vigiaModel->where('id_cliente', $clientId)
            ->orderBy('created_at', 'DESC')
            ->first();

        // Preparar los datos para la vista
        $data = [
            'client'        => $client,
            'consultant'    => $consultant,
            'clientPolicy'  => $clientPolicy,
            'policyType'    => $policyType,
            'latestVersion' => $latestVersion,
            'allVersions'   => $allVersions,
            'latestVigia'   => $latestVigia
        ];

        // Cargar la vista y pasar los datos
        $html = view('client/sgsst/1planear/p1_1_3vigia', $data);

        // Cargar el HTML en Dompdf
        $dompdf->loadHtml($html);

        // Configurar el tamaño del papel y la orientación
        $dompdf->setPaper('A4', 'portrait');

        // Renderizar el PDF
        $dompdf->render();

        // Enviar el PDF al navegador para descargar
        $dompdf->stream('asignacion_vigia.pdf', ['Attachment' => false]);
    }
}
