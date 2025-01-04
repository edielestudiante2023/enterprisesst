<?php

namespace App\Controllers;

use App\Models\DataOwnerModel;
use CodeIgniter\Controller;

class DataOwnerController extends Controller
{
    // Listar todos los propietarios de datos
    public function listDataOwners()
    {
        $dataOwnerModel = new DataOwnerModel();
        $dataOwners = $dataOwnerModel->findAll(); // Obtener todos los propietarios de datos

        return view('consultant/list_data_owners', ['dataOwners' => $dataOwners]);
    }

    // Mostrar el formulario para añadir un nuevo propietario de datos
    public function addDataOwner()
    {
        return view('consultant/add_data_owner');
    }

    // Procesar el formulario para añadir un nuevo propietario de datos
    public function addDataOwnerPost()
    {
        $dataOwnerModel = new DataOwnerModel();

        $data = [
            'data_owner' => $this->request->getVar('data_owner'),
            'comment_data_owner' => $this->request->getVar('comment_data_owner')
        ];

        if ($dataOwnerModel->save($data)) {
            return redirect()->to('/listDataOwners')->with('msg', 'Propietario de Datos agregado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar el Propietario de Datos');
        }
    }

    // Mostrar el formulario para editar un propietario de datos
    public function editDataOwner($id)
    {
        $dataOwnerModel = new DataOwnerModel();
        $dataOwner = $dataOwnerModel->find($id);

        if (!$dataOwner) {
            return redirect()->to('/listDataOwners')->with('msg', 'Propietario de Datos no encontrado');
        }

        return view('consultant/edit_data_owner', ['dataOwner' => $dataOwner]);
    }

    // Procesar el formulario para actualizar un propietario de datos
    public function editDataOwnerPost($id)
    {
        $dataOwnerModel = new DataOwnerModel();

        $data = [
            'data_owner' => $this->request->getVar('data_owner'),
            'comment_data_owner' => $this->request->getVar('comment_data_owner')
        ];

        if ($dataOwnerModel->update($id, $data)) {
            return redirect()->to('/listDataOwners')->with('msg', 'Propietario de Datos actualizado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar el Propietario de Datos');
        }
    }

    // Eliminar un propietario de datos
    public function deleteDataOwner($id)
    {
        $dataOwnerModel = new DataOwnerModel();
        $dataOwnerModel->delete($id);

        return redirect()->to('/listDataOwners')->with('msg', 'Propietario de Datos eliminado exitosamente');
    }
}
