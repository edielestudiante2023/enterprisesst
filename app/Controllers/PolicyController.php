<?php

namespace App\Controllers;

use App\Models\ClientPoliciesModel;
use App\Models\ClientModel;
use App\Models\PolicyTypeModel;
use CodeIgniter\Controller;

class PolicyController extends Controller
{
    public function listPolicies()
{
    $clientModel = new ClientModel();
    $clients = $clientModel->findAll();

    $policyModel = new ClientPoliciesModel();
    $policies = $policyModel->findAll();

    $policyTypeModel = new PolicyTypeModel();
    $policyTypes = $policyTypeModel->findAll();

    return view('consultant/list_policies', [
        'clients' => $clients,
        'policies' => $policies,
        'policyTypes' => $policyTypes
    ]);
}


    public function addPolicy()
    {
        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();

        $policyTypeModel = new PolicyTypeModel();
        $policyTypes = $policyTypeModel->findAll();

  

        return view('consultant/add_policy', [
            'clients' => $clients,
            'policyTypes' => $policyTypes
        ]);
    }

    public function addPolicyPost()
    {
        $model = new ClientPoliciesModel();

        $data = [
            'client_id' => $this->request->getVar('client_id'),
            'policy_type_id' => $this->request->getVar('policy_type_id'),
            'policy_content' => $this->request->getVar('policy_content')
        ];

        if ($model->save($data)) {
            return redirect()->to('/listPolicies')->with('msg', 'Política agregada exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar política');
        }
    }

    public function editPolicy($id)
    {
        $model = new ClientPoliciesModel();
        $policy = $model->find($id);

        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();

        $policyTypeModel = new PolicyTypeModel();
        $policyTypes = $policyTypeModel->findAll();

        return view('consultant/edit_policy', [
            'policy' => $policy,
            'clients' => $clients,
            'policyTypes' => $policyTypes
        ]);
    }

    public function editPolicyPost($id)
    {
        $model = new ClientPoliciesModel();

        $data = [
            'client_id' => $this->request->getVar('client_id'),
            'policy_type_id' => $this->request->getVar('policy_type_id'),
            'policy_content' => $this->request->getVar('policy_content')
        ];

        if ($model->update($id, $data)) {
            return redirect()->to('/listPolicies')->with('msg', 'Contenido actualizado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar Contenido');
        }
    }

    public function deletePolicy($id)
    {
        $model = new ClientPoliciesModel();
        $model->delete($id);

        return redirect()->to('/listPolicies')->with('msg', 'Política eliminada exitosamente');
    }

      public function listPolicyTypes()
    {
        $model = new PolicyTypeModel();
        $policyTypes = $model->findAll();
        return view('consultant/list_policy_types', ['policyTypes' => $policyTypes]);
    }

    // Mostrar formulario para añadir un nuevo tipo de política
    public function addPolicyType()
    {
        return view('consultant/add_policy_type');
    }

    // Guardar un nuevo tipo de política
    public function addPolicyTypePost()
    {
        $model = new PolicyTypeModel();
        $data = [
            'type_name' => $this->request->getPost('type_name'),
            'description' => $this->request->getPost('description'),
        ];
        $model->save($data);
        return redirect()->to('/listPolicyTypes')->with('msg', 'Tipo de política añadido con éxito');
    }

    // Mostrar formulario para editar un tipo de política existente
    public function editPolicyType($id)
    {
        $model = new PolicyTypeModel();
        $policyType = $model->find($id);
        return view('consultant/edit_policy_type', ['policyType' => $policyType]);
    }

    // Actualizar un tipo de política existente
    public function editPolicyTypePost($id)
    {
        $model = new PolicyTypeModel();
        $data = [
            'type_name' => $this->request->getPost('type_name'),
            'description' => $this->request->getPost('description'),
        ];
        $model->update($id, $data);
        return redirect()->to('/listPolicyTypes')->with('msg', 'Tipo de política actualizado con éxito');
    }

    // Eliminar un tipo de política
    public function deletePolicyType($id)
    {
        $model = new PolicyTypeModel();
        $model->delete($id);
        return redirect()->to('/listPolicyTypes')->with('msg', 'Tipo de política eliminado con éxito');
    }
}


