<?php

namespace App\Controllers;

use App\Models\DocumentVersionModel;
use App\Models\ClientModel;
use App\Models\PolicyTypeModel;

class VersionController extends BaseController
{
    // Método para listar las versiones de un cliente y tipo de política
    public function listVersions()
    {
        $versionModel = new DocumentVersionModel();
        $clientModel = new ClientModel();
        $policyTypeModel = new PolicyTypeModel();

        // Obtener todas las versiones
        $versions = $versionModel->findAll();

        // Recorremos las versiones y añadimos los datos del cliente y la política
        foreach ($versions as &$version) {
            // Obtener el nombre del cliente
            $client = $clientModel->find($version['client_id']);
            $version['nombre_cliente'] = $client['nombre_cliente'];

            // Obtener el nombre de la política
            $policyType = $policyTypeModel->find($version['policy_type_id']);
            $version['type_name'] = $policyType['type_name'];
        }

        $data = ['versions' => $versions];
        return view('consultant/list_versions', $data);
    }



    // Método para agregar una nueva versión
    public function addVersion()
    {
        $clientModel = new ClientModel();
        $policyTypeModel = new PolicyTypeModel();

        $data = [
            'clients' => $clientModel->findAll(),
            'policyTypes' => $policyTypeModel->findAll(),
        ];

        return view('consultant/add_version', $data);
    }

    // Método para manejar el formulario de agregar nueva versión
    /* public function addVersionPost()
    {
        $versionModel = new DocumentVersionModel();
        $versionModel->insert([
            'client_id' => $this->request->getPost('client_id'),
            'policy_type_id' => $this->request->getPost('policy_type_id'),
            'version_number' => $this->request->getPost('version_number')
        ]);

        return redirect()->to('/listVersions')->with('message', 'Nueva versión añadida exitosamente.');
    } */

    public function addVersionPost()
{
    $versionModel = new DocumentVersionModel();

    $data = [
        'client_id' => $this->request->getPost('client_id'),
        'policy_type_id' => $this->request->getPost('policy_type_id'),
        'version_number' => $this->request->getPost('version_number'),
        'document_type' => $this->request->getPost('document_type'),  // En inglés
        'acronym' => $this->request->getPost('acronym'),
        'location' => $this->request->getPost('location'),
        'status' => $this->request->getPost('status'),
        'change_control' => $this->request->getPost('change_control')
    ];

    $versionModel->save($data);

    return redirect()->to('/listVersions')->with('message', 'Version added successfully.');
}

    public function editVersion($id)
{
    $versionModel = new DocumentVersionModel();
    $clientModel = new ClientModel();
    $policyTypeModel = new PolicyTypeModel();

    // Obtener la versión específica
    $version = $versionModel->find($id);

    if (!$version) {
        return redirect()->to('/listVersions')->with('error', 'No se encontró la versión.');
    }

    // Obtener todos los clientes y tipos de políticas para los dropdowns
    $clients = $clientModel->findAll();
    $policyTypes = $policyTypeModel->findAll();

    $data = [
        'version' => $version,
        'clients' => $clients,
        'policyTypes' => $policyTypes
    ];
    
    return view('consultant/edit_version', $data);
}



public function editVersionPost($id)
{
    $versionModel = new DocumentVersionModel();

    // Recibir todos los campos enviados por el formulario
    $data = [
        'client_id' => $this->request->getPost('client_id'),
        'policy_type_id' => $this->request->getPost('policy_type_id'),
        'version_number' => $this->request->getPost('version_number'),
        'document_type' => $this->request->getPost('document_type'), // Añadir document_type
        'acronym' => $this->request->getPost('acronym'),             // Añadir acronym
        'location' => $this->request->getPost('location'),           // Añadir location
        'status' => $this->request->getPost('status'),               // Añadir status
        'change_control' => $this->request->getPost('change_control')// Añadir change_control
    ];

    // Actualizar la versión en la base de datos
    $versionModel->update($id, $data);

    return redirect()->to('/listVersions')->with('message', 'Versión actualizada exitosamente.');
}


public function deleteVersion($id)
{
    $versionModel = new DocumentVersionModel();

    if ($versionModel->find($id)) {
        $versionModel->delete($id);
        return redirect()->to('/listVersions')->with('message', 'Versión eliminada exitosamente.');
    } else {
        return redirect()->to('/listVersions')->with('error', 'No se pudo encontrar la versión.');
    }
}


}
