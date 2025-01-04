<?php

namespace App\Controllers;

use App\Models\CapacitacionModel;
use CodeIgniter\Controller;

class CapacitacionController extends Controller
{
    // Listar todas las capacitaciones
    public function listCapacitaciones()
    {
        $model = new CapacitacionModel();
        $data['capacitaciones'] = $model->findAll(); // Obtiene todas las capacitaciones

        return view('consultant/list_capacitaciones', $data);
    }

    // Mostrar el formulario para añadir una nueva capacitación
    public function addCapacitacion()
    {
        return view('consultant/add_capacitacion');
    }

    // Guardar una nueva capacitación en la base de datos
    public function addCapacitacionPost()
    {
        $model = new CapacitacionModel();

        $data = [
            'capacitacion' => $this->request->getVar('capacitacion'),
            'objetivo_capacitacion' => $this->request->getVar('objetivo_capacitacion'),
            'observaciones' => $this->request->getVar('observaciones'),
        ];

        if ($model->insert($data)) {
            return redirect()->to('/listCapacitaciones')->with('msg', 'Capacitación agregada exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar capacitación');
        }
    }

    // Mostrar el formulario para editar una capacitación existente
    public function editCapacitacion($id)
    {
        $model = new CapacitacionModel();
        $data['capacitacion'] = $model->find($id); // Encuentra la capacitación por su ID

        return view('consultant/edit_capacitacion', $data);
    }

    // Actualizar una capacitación existente en la base de datos
    public function editCapacitacionPost($id)
    {
        $model = new CapacitacionModel();

        $data = [
            'capacitacion' => $this->request->getVar('capacitacion'),
            'objetivo_capacitacion' => $this->request->getVar('objetivo_capacitacion'),
            'observaciones' => $this->request->getVar('observaciones'),
        ];

        if ($model->update($id, $data)) {
            return redirect()->to('/listCapacitaciones')->with('msg', 'Capacitación actualizada exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar capacitación');
        }
    }

    // Eliminar una capacitación
    public function deleteCapacitacion($id)
    {
        $model = new CapacitacionModel();
        $model->delete($id); // Elimina la capacitación por su ID

        return redirect()->to('/listCapacitaciones')->with('msg', 'Capacitación eliminada exitosamente');
    }
}
