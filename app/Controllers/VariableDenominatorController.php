<?php

namespace App\Controllers;

use App\Models\VariableDenominatorModel;
use CodeIgniter\Controller;

class VariableDenominatorController extends Controller
{
    // Listar todas las variables denominador
    public function listDenominatorVariables()
    {
        $denominatorModel = new VariableDenominatorModel();
        $denominatorVariables = $denominatorModel->findAll(); // Obtener todas las variables denominador

        return view('consultant/list_denominator_variables', ['denominatorVariables' => $denominatorVariables]);
    }

    // Mostrar el formulario para añadir una nueva variable denominador
    public function addDenominatorVariable()
    {
        return view('consultant/add_denominator_variable');
    }

    // Procesar el formulario para añadir una nueva variable denominador
    public function addDenominatorVariablePost()
    {
        $denominatorModel = new VariableDenominatorModel();

        $data = [
            'denominator_variable_text' => $this->request->getVar('denominator_variable_text'),
            'denominator_variable_data' => $this->request->getVar('denominator_variable_data')
        ];

        if ($denominatorModel->save($data)) {
            return redirect()->to('/listDenominatorVariables')->with('msg', 'Variable Denominador agregada exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar la Variable Denominador');
        }
    }

    // Mostrar el formulario para editar una variable denominador
    public function editDenominatorVariable($id)
    {
        $denominatorModel = new VariableDenominatorModel();
        $denominatorVariable = $denominatorModel->find($id);

        if (!$denominatorVariable) {
            return redirect()->to('/listDenominatorVariables')->with('msg', 'Variable Denominador no encontrada');
        }

        return view('consultant/edit_denominator_variable', ['denominatorVariable' => $denominatorVariable]);
    }

    // Procesar el formulario para actualizar una variable denominador
    public function editDenominatorVariablePost($id)
    {
        $denominatorModel = new VariableDenominatorModel();

        $data = [
            'denominator_variable_text' => $this->request->getVar('denominator_variable_text'),
            'denominator_variable_data' => $this->request->getVar('denominator_variable_data')
        ];

        if ($denominatorModel->update($id, $data)) {
            return redirect()->to('/listDenominatorVariables')->with('msg', 'Variable Denominador actualizada exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar la Variable Denominador');
        }
    }

    // Eliminar una variable denominador
    public function deleteDenominatorVariable($id)
    {
        $denominatorModel = new VariableDenominatorModel();
        $denominatorModel->delete($id);

        return redirect()->to('/listDenominatorVariables')->with('msg', 'Variable Denominador eliminada exitosamente');
    }
}
