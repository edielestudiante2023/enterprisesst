<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ClientPoliciesModel; // Usaremos este modelo para client_policies
use App\Models\DocumentVersionModel; // Usaremos este modelo para client_policies
use App\Models\PolicyTypeModel; // Usaremos este modelo para client_policies

use Dompdf\Dompdf;

use CodeIgniter\Controller;

class PzmanconvivencialaboralController extends Controller
{



    public function manconvivenciaLaboral()
    {
        // Obtener el ID del cliente desde la sesión
        $session = session();
        $clientId = $session->get('user_id'); // Asegúrate de que este ID es el del cliente

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $clientPoliciesModel = new ClientPoliciesModel();
        $policyTypeModel = new PolicyTypeModel();
        $versionModel = new DocumentVersionModel();

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
        $policyTypeId = 14; // Supongamos que el ID de la política de alcohol y drogas es 1
        $clientPolicy = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('id', 'DESC')
            ->first();
        if (!$clientPolicy) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento para este cliente.');
        }
        $policyTypeId2 = 15;
        $clientPolicy2 = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId2)
            ->orderBy('id', 'DESC')
            ->first();
        if (!$clientPolicy2) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento para este cliente (Política 2).');
        }

        $policyTypeId3 = 16;
        $clientPolicy3 = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId3)
            ->orderBy('id', 'DESC')
            ->first();
        if (!$clientPolicy3) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento para este cliente (Política 3).');
        }

       /*  $policyTypeId4 = 17;
        $clientPolicy4 = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId4)
            ->orderBy('id', 'DESC')
            ->first();
        if (!$clientPolicy4) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento para este cliente (Política 4).');
        }

        $policyTypeId5 = 18;
        $clientPolicy5 = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId5)
            ->orderBy('id', 'DESC')
            ->first();
        if (!$clientPolicy5) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento para este cliente (Política 5).');
        } */

        // Obtener el tipo de política
        $policyType = $policyTypeModel->find($policyTypeId);
        $policyType2 = $policyTypeModel->find($policyTypeId2);
        $policyType3 = $policyTypeModel->find($policyTypeId3);
        /* $policyType4 = $policyTypeModel->find($policyTypeId4);
        $policyType5 = $policyTypeModel->find($policyTypeId5); */
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


        // Pasar los datos a la vista
        $data = [
            'client' => $client,
            'consultant' => $consultant,
            'clientPolicy' => $clientPolicy,
            'clientPolicy2' => $clientPolicy2,
            'clientPolicy3' => $clientPolicy3,
            /* 'clientPolicy4' => $clientPolicy4,
            'clientPolicy5' => $clientPolicy5, */
            'policyType' => $policyType,
            'policyType2' => $policyType2,
            'policyType3' => $policyType3,
            /* 'policyType4' => $policyType4,
            'policyType5' => $policyType5, */
            'latestVersion' => $latestVersion,
            'allVersions' => $allVersions,  // Pasamos todas las versiones al footer
        ];

        return view('client/sgsst/1planear/p1_1_12manconvivencialaboral', $data);
    }

    public function generatePdf_manconvivenciaLaboral()
    {
        // Instanciar Dompdf
        $dompdf = new Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);

        // Obtener los mismos datos que en la función policyNoAlcoholDrogas
        $session = session();
        $clientId = $session->get('user_id');

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $clientPoliciesModel = new ClientPoliciesModel();
        $policyTypeModel = new PolicyTypeModel();
        $versionModel = new DocumentVersionModel();

        // Obtener los datos necesarios
        $client = $clientModel->find($clientId);
        $consultant = $consultantModel->find($client['id_consultor']);
        $policyTypeId = 14; // Supongamos que el ID de la política de alcohol y drogas es 1
        $clientPolicy = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('id', 'DESC')
            ->first();
        $policyTypeId2 = 15;
        $clientPolicy2 = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId2)
            ->orderBy('id', 'DESC')
            ->first();
        if (!$clientPolicy2) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento para este cliente (Política 2).');
        }

        $policyTypeId3 = 16;
        $clientPolicy3 = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId3)
            ->orderBy('id', 'DESC')
            ->first();
        if (!$clientPolicy3) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento para este cliente (Política 3).');
        }

        /* $policyTypeId4 = 17;
        $clientPolicy4 = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId4)
            ->orderBy('id', 'DESC')
            ->first();
        if (!$clientPolicy4) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento para este cliente (Política 4).');
        }

        $policyTypeId5 = 18;
        $clientPolicy5 = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId5)
            ->orderBy('id', 'DESC')
            ->first();
        if (!$clientPolicy5) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento para este cliente (Política 5).');
        } */


        $policyType = $policyTypeModel->find($policyTypeId);
        $policyType2 = $policyTypeModel->find($policyTypeId2);
        $policyType3 = $policyTypeModel->find($policyTypeId3);
        /* $policyType4 = $policyTypeModel->find($policyTypeId4);
        $policyType5 = $policyTypeModel->find($policyTypeId5); */




        $latestVersion = $versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->first();
        $allVersions = $versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Preparar los datos para la vista
        $data = [
            'client' => $client,
            'consultant' => $consultant,
            'clientPolicy' => $clientPolicy,
            'clientPolicy2' => $clientPolicy2,
            'clientPolicy3' => $clientPolicy3,
            /* 'clientPolicy4' => $clientPolicy4,
            'clientPolicy5' => $clientPolicy5, */
            'policyType' => $policyType,
            'policyType2' => $policyType2,
            'policyType3' => $policyType3,
            /* 'policyType4' => $policyType4,
            'policyType5' => $policyType5, */
            'latestVersion' => $latestVersion,
            'allVersions' => $allVersions,  // Pasamos todas las versiones al footer
        ];

        // Cargar la vista y pasar los datos
        $html = view('client/sgsst/1planear/p1_1_12manconvivencialaboral', $data);

        // Cargar el HTML en Dompdf
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A3', 'portrait');
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('isRemoteEnabled', true); // si usas imágenes externas
        $dompdf->render();

        // Enviar el PDF al navegador para descargar
        $dompdf->stream('manual_cocolab.pdf', ['Attachment' => false]);
    }
}
