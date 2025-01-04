<?php

namespace App\Controllers;

use App\Models\ObjectivesPolicyModel;
use CodeIgniter\Controller;

class ObjectivesPolicyController extends Controller
{
    // Listar todos los objetivos de política
    public function listObjectives()
    {
        $objectivesModel = new ObjectivesPolicyModel();
        $objectives = $objectivesModel->findAll(); // Obtener todos los objetivos

        return view('consultant/list_objectives', ['objectives' => $objectives]);
    }

    // Mostrar el formulario para añadir un nuevo objetivo de política
    public function addObjective()
    {
        return view('consultant/add_objective');
    }

    // Procesar el formulario para añadir un nuevo objetivo de política
    public function addObjectivePost()
    {
        $objectivesModel = new ObjectivesPolicyModel();

        $data = [
            'name_objectives' => $this->request->getVar('name_objectives'),
            'comments_objectives' => $this->request->getVar('comments_objectives')
        ];

        if ($objectivesModel->save($data)) {
            return redirect()->to('/listObjectives')->with('msg', 'Objetivo de Política agregado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar el Objetivo de Política');
        }
    }

    // Mostrar el formulario para editar un objetivo de política
    public function editObjective($id)
    {
        $objectivesModel = new ObjectivesPolicyModel();
        $objective = $objectivesModel->find($id);

        if (!$objective) {
            return redirect()->to('/listObjectives')->with('msg', 'Objetivo no encontrado');
        }

        return view('consultant/edit_objective', ['objective' => $objective]);
    }

    // Procesar el formulario para actualizar un objetivo de política
    public function editObjectivePost($id)
    {
        $objectivesModel = new ObjectivesPolicyModel();

        $data = [
            'name_objectives' => $this->request->getVar('name_objectives'),
            'comments_objectives' => $this->request->getVar('comments_objectives')
        ];

        if ($objectivesModel->update($id, $data)) {
            return redirect()->to('/listObjectives')->with('msg', 'Objetivo actualizado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar el Objetivo');
        }
    }

    // Eliminar un objetivo de política
    public function deleteObjective($id)
    {
        $objectivesModel = new ObjectivesPolicyModel();
        $objectivesModel->delete($id);

        return redirect()->to('/listObjectives')->with('msg', 'Objetivo eliminado exitosamente');
    }
}
