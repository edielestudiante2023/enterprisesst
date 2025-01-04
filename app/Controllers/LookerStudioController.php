<?php

namespace App\Controllers;

use App\Models\LookerStudioModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class LookerStudioController extends Controller
{
    public function list()
    {
        $model = new LookerStudioModel();
        $clientModel = new ClientModel();
        $data['lookerStudios'] = $model->findAll();
        $data['clients'] = $clientModel->findAll();
        return view('consultant/list_lookerstudio_capacitaciones', $data);
    }

    public function add()
    {
        $clientModel = new ClientModel();
        $data['clients'] = $clientModel->findAll();
        return view('consultant/add_lookerstudio_capacitaciones', $data);
    }

    public function addPost()
    {
        $model = new LookerStudioModel();
        $data = [
            'tipodedashboard' => $this->request->getPost('tipodedashboard'),
            'enlace' => $this->request->getPost('enlace'),
            'id_cliente' => $this->request->getPost('id_cliente'),
        ];
        $model->save($data);
        return redirect()->to('/lookerstudio/list')->with('msg', 'Dashboard agregado exitosamente');
    }

    public function edit($id)
    {
        $model = new LookerStudioModel();
        $clientModel = new ClientModel();
        $data['lookerStudio'] = $model->find($id);
        $data['clients'] = $clientModel->findAll();
        return view('consultant/edit_lookerstudio_capacitaciones', $data);
    }

    public function editPost($id)
    {
        $model = new LookerStudioModel();
        $data = [
            'tipodedashboard' => $this->request->getPost('tipodedashboard'),
            'enlace' => $this->request->getPost('enlace'),
            'id_cliente' => $this->request->getPost('id_cliente'),
        ];
        $model->update($id, $data);
        return redirect()->to('/lookerstudio/list')->with('msg', 'Dashboard actualizado exitosamente');
    }

    public function delete($id)
    {
        $model = new LookerStudioModel();
        $model->delete($id);
        return redirect()->to('/lookerstudio/list')->with('msg', 'Dashboard eliminado exitosamente');
    }

    
}
