<?php

namespace App\Controllers;

use App\Models\EvaluationModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class EvaluationController extends Controller
{
    // Listar las evaluaciones
    public function listEvaluaciones()
    {
        $evaluationModel = new EvaluationModel();
        $clientModel = new ClientModel();

        // Obtener todas las evaluaciones
        $evaluaciones = $evaluationModel->findAll();

        // Obtener los datos de los clientes para incluir los nombres
        $clients = $clientModel->findAll();

        // Crear un array asociando los id_cliente con los nombres de cliente
        $clientsMap = [];
        foreach ($clients as $client) {
            $clientsMap[$client['id_cliente']] = $client['nombre_cliente'];
        }

        // Agregar los nombres de los clientes a las evaluaciones
        foreach ($evaluaciones as &$evaluacion) {
            $evaluacion['nombre_cliente'] = $clientsMap[$evaluacion['id_cliente']] ?? 'Cliente no encontrado';
        }

        return view('consultant/list_evaluaciones', ['evaluaciones' => $evaluaciones]);
    }

    // Mostrar el formulario para añadir una nueva evaluación
    public function addEvaluacion()
    {
        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();

        return view('consultant/add_evaluacion', ['clients' => $clients]);
    }

    // Guardar una nueva evaluación en la base de datos
    public function addEvaluacionPost()
    {
        $model = new EvaluationModel();

        $valor = $this->request->getVar('valor');
        $evaluacion_inicial = $this->request->getVar('evaluacion_inicial');

        // Lógica del CASE para puntaje_cuantitativo
        $puntaje_cuantitativo = 0;
        if ($evaluacion_inicial == 'CUMPLE TOTALMENTE' || $evaluacion_inicial == 'NO APLICA') {
            $puntaje_cuantitativo = $valor;
        }

        $data = [
            'id_cliente' => $this->request->getVar('id_cliente'),
            'ciclo' => $this->request->getVar('ciclo'),
            'estandar' => $this->request->getVar('estandar'),
            'detalle_estandar' => $this->request->getVar('detalle_estandar'),
            'estandares_minimos' => $this->request->getVar('estandares_minimos'),
            'numeral' => $this->request->getVar('numeral'),
            'numerales_del_cliente' => $this->request->getVar('numerales_del_cliente'),
            'siete' => $this->request->getVar('siete'),
            'veintiun' => $this->request->getVar('veintiun'),
            'sesenta' => $this->request->getVar('sesenta'),
            'item_del_estandar' => $this->request->getVar('item_del_estandar'),
            'evaluacion_inicial' => $evaluacion_inicial,
            'valor' => $valor,
            'puntaje_cuantitativo' => $puntaje_cuantitativo,
            'item' => $this->request->getVar('item'),
            'criterio' => $this->request->getVar('criterio'),
            'modo_de_verificacion' => $this->request->getVar('modo_de_verificacion'),
            'calificacion' => $this->request->getVar('calificacion'),
            'nivel_de_evaluacion' => $this->request->getVar('nivel_de_evaluacion'),
            'observaciones' => $this->request->getVar('observaciones'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($model->insert($data)) {
            return redirect()->to('/listEvaluaciones')->with('msg', 'Evaluación agregada exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar evaluación');
        }
    }

    // Editar una evaluación existente
    public function editEvaluacion($id)
    {
        $evaluationModel = new EvaluationModel();
        $clientModel = new ClientModel();

        $data['evaluacion'] = $evaluationModel->find($id);
        $data['clients'] = $clientModel->findAll();

        return view('consultant/edit_evaluacion', $data);
    }

    // Actualizar una evaluación existente
    public function editEvaluacionPost($id)
    {
        $model = new EvaluationModel();

        $valor = $this->request->getVar('valor');
        $evaluacion_inicial = $this->request->getVar('evaluacion_inicial');

        // Lógica del CASE para puntaje_cuantitativo
        $puntaje_cuantitativo = 0;
        if ($evaluacion_inicial == 'CUMPLE TOTALMENTE' || $evaluacion_inicial == 'NO APLICA') {
            $puntaje_cuantitativo = $valor;
        }

        $data = [
            'id_cliente' => $this->request->getVar('id_cliente'),
            'ciclo' => $this->request->getVar('ciclo'),
            'estandar' => $this->request->getVar('estandar'),
            'detalle_estandar' => $this->request->getVar('detalle_estandar'),
            'estandares_minimos' => $this->request->getVar('estandares_minimos'),
            'numeral' => $this->request->getVar('numeral'),
            'numerales_del_cliente' => $this->request->getVar('numerales_del_cliente'),
            'siete' => $this->request->getVar('siete'),
            'veintiun' => $this->request->getVar('veintiun'),
            'sesenta' => $this->request->getVar('sesenta'),
            'item_del_estandar' => $this->request->getVar('item_del_estandar'),
            'evaluacion_inicial' => $evaluacion_inicial,
            'valor' => $valor,
            'puntaje_cuantitativo' => $puntaje_cuantitativo,
            'item' => $this->request->getVar('item'),
            'criterio' => $this->request->getVar('criterio'),
            'modo_de_verificacion' => $this->request->getVar('modo_de_verificacion'),
            'calificacion' => $this->request->getVar('calificacion'),
            'nivel_de_evaluacion' => $this->request->getVar('nivel_de_evaluacion'),
            'observaciones' => $this->request->getVar('observaciones'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($model->update($id, $data)) {
            return redirect()->to('/listEvaluaciones')->with('msg', 'Evaluación actualizada exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar evaluación');
        }
    }

    // Eliminar una evaluación
    public function deleteEvaluacion($id)
    {
        $model = new EvaluationModel();
        $model->delete($id);

        return redirect()->to('/listEvaluaciones')->with('msg', 'Evaluación eliminada exitosamente');
    }

    // Función para actualización en línea
    public function updateEvaluacion()
    {
        $id = $this->request->getPost('id');
        $field = $this->request->getPost('field');
        $value = $this->request->getPost('value');

        $allowedFields = ['evaluacion_inicial', 'observaciones'];
        if (!in_array($field, $allowedFields)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Campo no permitido']);
        }

        $model = new EvaluationModel();
        $updateData = [$field => $value];

        // Si se actualiza "evaluacion_inicial", recalcular puntaje_cuantitativo
        if ($field === 'evaluacion_inicial') {
            $evaluation = $model->find($id);
            $valor = isset($evaluation['valor']) ? $evaluation['valor'] : 0;
            $puntaje_cuantitativo = in_array($value, ['CUMPLE TOTALMENTE', 'NO APLICA']) ? $valor : 0;
            $updateData['puntaje_cuantitativo'] = $puntaje_cuantitativo;
        }

        if ($model->update($id, $updateData)) {
            // Obtener el registro actualizado para recuperar el nuevo puntaje_cuantitativo
            $updatedRecord = $model->find($id);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Registro actualizado correctamente',
                'puntaje_cuantitativo' => $updatedRecord['puntaje_cuantitativo']
            ]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al actualizar el registro']);
        }
    }
}
