<?php

namespace App\Controllers;

use App\Models\MatrizCycloidModel;
use CodeIgniter\Controller;

class MatrizCycloidController extends Controller
{
    protected $matrizModel;

    public function __construct()
    {
        // Instancia del modelo
        $this->matrizModel = new MatrizCycloidModel();
    }

    // Método para listar todas las matrices Cycloid
    public function listMatricesCycloid()
    {
        $data['matrices'] = $this->matrizModel->findAll();
        return view('consultant/listMatricesCycloid', $data); // Vista para listar todas las matrices
    }

    // Método para mostrar el formulario de creación de una nueva matriz Cycloid
    public function addMatrizCycloid()
    {
        return view('consultant/addMatrizCycloid'); // Vista para el formulario de creación
    }

    // Método para guardar los datos de una nueva matriz Cycloid
    public function addMatrizCycloidPost()
    {
        $this->matrizModel->save([
            'titulo_matriz'   => $this->request->getPost('titulo_matriz'),
            'Tipo_documento'  => $this->request->getPost('Tipo_documento'),
            'enlace'          => $this->request->getPost('enlace'),
            'observaciones'   => $this->request->getPost('observaciones'),
        ]);

        return redirect()->to('/listMatricesCycloid')->with('success', 'Matriz Cycloid creada exitosamente.');
    }

    // Método para mostrar el formulario de edición de una matriz Cycloid existente
    public function editMatrizCycloid($id)
    {
        $data['matriz'] = $this->matrizModel->find($id);

        if (!$data['matriz']) {
            return redirect()->to('/listMatricesCycloid')->with('error', 'Matriz Cycloid no encontrada.');
        }

        return view('consultant/editMatrizCycloid', $data); // Vista para editar la matriz
    }

    // Método para actualizar los datos de una matriz Cycloid
    public function editMatrizCycloidPost($id)
    {
        $this->matrizModel->update($id, [
            'titulo_matriz'   => $this->request->getPost('titulo_matriz'),
            'Tipo_documento'  => $this->request->getPost('Tipo_documento'),
            'enlace'          => $this->request->getPost('enlace'),
            'observaciones'   => $this->request->getPost('observaciones'),
        ]);

        return redirect()->to('/listMatricesCycloid')->with('success', 'Matriz Cycloid actualizada exitosamente.');
    }

    // Método para eliminar una matriz Cycloid
    public function deleteMatrizCycloid($id)
    {
        $this->matrizModel->delete($id);
        return redirect()->to('/listMatricesCycloid')->with('success', 'Matriz Cycloid eliminada exitosamente.');
    }
}
