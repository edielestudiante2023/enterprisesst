<?php 
namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ClientPoliciesModel; // Usaremos este modelo para client_policies
use App\Models\DocumentVersionModel; // Usaremos este modelo para client_policies
use App\Models\PolicyTypeModel; // Usaremos este modelo para client_policies

use Dompdf\Dompdf;

use CodeIgniter\Controller;

class Prueba1Controller extends Controller
{
    public function responsableDelSGSST()
    {
        // Obtener el ID del cliente desde la sesión
        $session = session();
        $clientId = $session->get('user_id'); // Asegúrate de que este ID es el del cliente

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        // Obtener los datos del cliente
        $client = $clientModel->find($clientId);

        // Verificar si se obtuvo correctamente el cliente
        if (!$client) {
            return redirect()->to('/dashboardclient')->with('error', 'No se pudo encontrar la información del cliente');
        }

        // Verificar si el cliente tiene un consultor asignado
        if (empty($client['id_consultor'])) {
            return redirect()->to('/dashboardclient')->with('error', 'El cliente no tiene un consultor asignado');
        }

        // Obtener los datos del consultor relacionado con el cliente
        $consultant = $consultantModel->find($client['id_consultor']);

        if (!$consultant) {
            return redirect()->to('/dashboardclient')->with('error', 'No se pudo encontrar la información del consultor');
        }

        $data = [
            'client' => $client,
            'consultant' => $consultant,
        ];

        return view('client/sgsst/1planear/responsabledelsgsst', $data);
    }



    public function prueba1()
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
    $policyTypeId = 4; // Supongamos que el ID de la política de alcohol y drogas es 1
    $clientPolicy = $clientPoliciesModel->where('client_id', $clientId)
                                        ->where('policy_type_id', $policyTypeId)
                                        ->first();
    if (!$clientPolicy) {
        return redirect()->to('/dashboardclient')->with('error', 'No se encontró la política de No Alcohol, Drogas y Tabaco para este cliente.');
    }

    // Obtener el tipo de política
    $policyType = $policyTypeModel->find($policyTypeId);

    // Obtener la versión más reciente del documento
    $latestVersion = $versionModel->where('client_id', $clientId)
                                  ->where('policy_type_id', $policyTypeId)
                                  ->orderBy('created_at', 'DESC')
                                  ->first();

    // Obtener todas las versiones del documento
    $allVersions = $versionModel->where('client_id', $clientId)
                                ->where('policy_type_id', $policyTypeId)
                                ->orderBy('created_at', 'DESC')
                                ->findAll();

    // Pasar los datos a la vista
    $data = [
        'client' => $client,
        'consultant' => $consultant,
        'clientPolicy' => $clientPolicy,
        'policyType' => $policyType,
        'latestVersion' => $latestVersion,
        'allVersions' => $allVersions,  // Pasamos todas las versiones al footer
    ];

    return view('client/sgsst/1planear/prueba1', $data);
}

public function generatePdfNoAlcoholDrogas()
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
    $policyTypeId = 1; // Supongamos que el ID de la política de alcohol y drogas es 1
    $clientPolicy = $clientPoliciesModel->where('client_id', $clientId)
                                        ->where('policy_type_id', $policyTypeId)
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

    // Preparar los datos para la vista
    $data = [
        'client' => $client,
        'consultant' => $consultant,
        'clientPolicy' => $clientPolicy,
        'policyType' => $policyType,
        'latestVersion' => $latestVersion,
        'allVersions' => $allVersions,  // Pasamos todas las versiones al footer
    ];

    // Cargar la vista y pasar los datos
    $html = view('client/sgsst/1planear/prueba1', $data);

    // Cargar el HTML en Dompdf
    $dompdf->loadHtml($html);

    // Configurar el tamaño del papel y la orientación
    $dompdf->setPaper('A4', 'portrait');

    // Renderizar el PDF
    $dompdf->render();

    // Enviar el PDF al navegador para descargar
    $dompdf->stream('prueba1.pdf', ['Attachment' => false]);
}

}



?>