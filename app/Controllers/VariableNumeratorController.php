<?php

namespace App\Controllers;

use App\Models\VariableNumeratorModel;
use CodeIgniter\Controller;

class VariableNumeratorController extends Controller
{
    // Listar todas las variables numerador
    public function listNumeratorVariables()
    {
        $numeratorModel = new VariableNumeratorModel();
        $numeratorVariables = $numeratorModel->findAll(); // Obtener todas las variables numerador

        return view('consultant/list_numerator_variables', ['numeratorVariables' => $numeratorVariables]);
    }

    // Mostrar el formulario para añadir una nueva variable numerador
    public function addNumeratorVariable()
    {
        return view('consultant/add_numerator_variable');
    }

    // Procesar el formulario para añadir una nueva variable numerador
    public function addNumeratorVariablePost()
    {
        $numeratorModel = new VariableNumeratorModel();

        $data = [
            'numerator_variable_text' => $this->request->getVar('numerator_variable_text'),
            'numerator_variable_data' => $this->request->getVar('numerator_variable_data')
        ];

        if ($numeratorModel->save($data)) {
            return redirect()->to('/listNumeratorVariables')->with('msg', 'Variable Numerador agregada exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar la Variable Numerador');
        }
    }

    // Mostrar el formulario para editar una variable numerador
    public function editNumeratorVariable($id)
    {
        $numeratorModel = new VariableNumeratorModel();
        $numeratorVariable = $numeratorModel->find($id);

        if (!$numeratorVariable) {
            return redirect()->to('/listNumeratorVariables')->with('msg', 'Variable Numerador no encontrada');
        }

        return view('consultant/edit_numerator_variable', ['numeratorVariable' => $numeratorVariable]);
    }

    // Procesar el formulario para actualizar una variable numerador
    public function editNumeratorVariablePost($id)
    {
        $numeratorModel = new VariableNumeratorModel();

        $data = [
            'numerator_variable_text' => $this->request->getVar('numerator_variable_text'),
            'numerator_variable_data' => $this->request->getVar('numerator_variable_data')
        ];

        if ($numeratorModel->update($id, $data)) {
            return redirect()->to('/listNumeratorVariables')->with('msg', 'Variable Numerador actualizada exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar la Variable Numerador');
        }
    }

    // Eliminar una variable numerador
    public function deleteNumeratorVariable($id)
    {
        $numeratorModel = new VariableNumeratorModel();
        $numeratorModel->delete($id);

        return redirect()->to('/listNumeratorVariables')->with('msg', 'Variable Numerador eliminada exitosamente');
    }
}

