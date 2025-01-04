<?php

namespace App\Controllers;

use App\Models\MatrizModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class MatricesController extends Controller
{
    public function list()
    {
        $model = new MatrizModel();
        $clientModel = new ClientModel();
        $data['matrices'] = $model->findAll();
        $data['clients'] = $clientModel->findAll();
        return view('consultant/list_matrices', $data);
    }

    public function add()
    {
        $clientModel = new ClientModel();
        $data['clients'] = $clientModel->findAll();
        return view('consultant/add_matrices', $data);
    }

    public function addPost()
    {
        $model = new MatrizModel();
        $data = [
            'tipo' => $this->request->getPost('tipo'),
            'descripcion' => $this->request->getPost('descripcion'),
            'observaciones' => $this->request->getPost('observaciones'),
            'enlace' => $this->request->getPost('enlace'),
            'id_cliente' => $this->request->getPost('id_cliente'),
        ];
        $model->save($data);
        return redirect()->to('/matrices/list')->with('msg', 'Dashboard agregado exitosamente');
    }

    public function edit($id)
    {
        $model = new MatrizModel();
        $clientModel = new ClientModel();
        $data['matrices'] = $model->find($id);
        $data['clients'] = $clientModel->findAll();
        return view('consultant/edit_matrices', $data);
    }

    public function editPost($id)
    {
        $model = new MatrizModel();
        $data = [
            'tipo' => $this->request->getPost('tipo'),
            'descripcion' => $this->request->getPost('descripcion'),
            'observaciones' => $this->request->getPost('observaciones'),
            'enlace' => $this->request->getPost('enlace'),
            'id_cliente' => $this->request->getPost('id_cliente'),
        ];
        $model->update($id, $data);
        return redirect()->to('/matrices/list')->with('msg', 'Dashboard actualizado exitosamente');
    }

    public function delete($id)
    {
        $model = new MatrizModel();
        $model->delete($id);
        return redirect()->to('/matrices/list')->with('msg', 'Dashboard eliminado exitosamente');
    }

    
}
