<?php

namespace App\Controllers;

use App\Models\PruebaModel;
use CodeIgniter\Controller;

class PruebaController extends Controller
{
    public function index()
    {
        return view('consultant/prueba_form');
    }

    public function save()
    {
        $pruebaModel = new PruebaModel();

        $data = [
            'nombre_prueba' => $this->request->getPost('nombre_prueba'),
        ];

        if ($pruebaModel->save($data)) {
            return redirect()->to('/prueba_form')->with('msg', 'Registro agregado exitosamente');
        } else {
            return redirect()->to('/prueba_form')->with('msg', 'Error al agregar registro');
        }
    }
}

?>
