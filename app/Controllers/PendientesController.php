<?php

namespace App\Controllers;

use App\Models\PendientesModel;
use App\Models\ClientModel; // Para obtener información del cliente
use CodeIgniter\Controller;

class PendientesController extends Controller
{
    // Listar todos los pendientes
    public function listPendientes()
    {
        $pendientesModel = new PendientesModel();
        $clientModel = new ClientModel();

        // Obtener todos los pendientes
        $pendientes = $pendientesModel->findAll();

        // Añadir el nombre del cliente y calcular conteo_dias dinámicamente
        foreach ($pendientes as &$pendiente) {
            $cliente = $clientModel->find($pendiente['id_cliente']);
            $pendiente['nombre_cliente'] = $cliente['nombre_cliente'] ?? 'Cliente desconocido';

            // Calcular conteo_dias dinámicamente
            $createdAt = strtotime($pendiente['created_at']);
            if ($pendiente['estado'] === 'ABIERTA') {
                $pendiente['conteo_dias'] = (int) floor((time() - $createdAt) / (60 * 60 * 24));
            } elseif ($pendiente['estado'] === 'CERRADA' && !empty($pendiente['fecha_cierre'])) {
                $fechaCierre = strtotime($pendiente['fecha_cierre']);
                $pendiente['conteo_dias'] = (int) floor(($fechaCierre - $createdAt) / (60 * 60 * 24));
            } else {
                $pendiente['conteo_dias'] = 0; // Valor por defecto si no se cumple ninguna condición
            }
        }

        $data['pendientes'] = $pendientes;

        return view('consultant/list_pendientes', $data);
    }

    // Mostrar formulario para agregar nuevo pendiente
    public function addPendiente()
    {
        $clientModel = new ClientModel();
        $data['clientes'] = $clientModel->findAll(); // Obtener todos los clientes

        return view('consultant/add_pendiente', $data); // Cargar la vista del formulario
    }

    // Guardar nuevo pendiente
    public function addPendientePost()
    {
        $pendientesModel = new PendientesModel();

        // Recogemos los datos del formulario
        $data = [
            'id_cliente' => $this->request->getPost('id_cliente'),
            'responsable' => $this->request->getPost('responsable'),
            'tarea_actividad' => $this->request->getPost('tarea_actividad'),
            'fecha_cierre' => $this->request->getPost('fecha_cierre'),
            'estado' => $this->request->getPost('estado'),
            'estado_avance' => $this->request->getPost('estado_avance'),
            'evidencia_para_cerrarla' => $this->request->getPost('evidencia_para_cerrarla'),
            // No asignar manualmente 'created_at'; el modelo lo gestiona automáticamente
        ];

        // Validar que la fecha de cierre no sea menor que la fecha de creación
        // Obtén la fecha de creación desde el modelo (se asigna automáticamente)
        // Dado que 'useTimestamps' está habilitado, 'created_at' se asigna en el modelo
        // Por lo tanto, necesitas insertar primero para obtener 'created_at'
        // Sin embargo, para mantener la validación antes de insertar, utilizaremos la fecha actual
        $createdAt = time(); // Timestamp actual

        if ($data['fecha_cierre'] && strtotime($data['fecha_cierre']) < $createdAt) {
            return redirect()->back()->with('msg', 'Error: La fecha de cierre no puede ser anterior a la fecha de creación.')->withInput();
        }

        // Validar que si hay fecha de cierre, el estado no puede ser ABIERTA
        if (!empty($data['fecha_cierre']) && $data['estado'] === 'ABIERTA') {
            return redirect()->back()->with('msg', 'Error: No se puede establecer el estado como ABIERTA si ya hay una fecha de cierre.')->withInput();
        }

        // Calcular conteo_dias
        if ($data['estado'] === 'ABIERTA') {
            $data['conteo_dias'] = (int) floor((time() - $createdAt) / (60 * 60 * 24));
        } elseif ($data['estado'] === 'CERRADA' && !empty($data['fecha_cierre'])) {
            $fechaCierre = strtotime($data['fecha_cierre']);
            $data['conteo_dias'] = (int) floor(($fechaCierre - $createdAt) / (60 * 60 * 24));
        } else {
            $data['conteo_dias'] = 0; // Valor por defecto si no se cumple ninguna condición
        }

        // Insertar el nuevo pendiente
        if ($pendientesModel->insert($data)) {
            return redirect()->to('/listPendientes')->with('msg', 'Pendiente agregado exitosamente');
        } else {
            // Obtener y mostrar los errores de validación
            $errors = $pendientesModel->errors();
            $errorMessage = 'Error al agregar pendiente.';
            if (!empty($errors)) {
                $errorMessage .= ' ' . implode(' ', array_map(function($msg) {
                    return is_array($msg) ? implode(', ', $msg) : $msg;
                }, $errors));
            }
            return redirect()->back()->with('msg', $errorMessage)->withInput();
        }
    }

    // Mostrar formulario para editar un pendiente
    public function editPendiente($id)
    {
        $pendientesModel = new PendientesModel();
        $clientModel = new ClientModel();

        $data['pendiente'] = $pendientesModel->find($id); // Obtener el pendiente que se va a editar
        $data['clientes'] = $clientModel->findAll(); // Obtener todos los clientes

        if (!$data['pendiente']) {
            return redirect()->to('/listPendientes')->with('msg', 'Pendiente no encontrado.');
        }

        return view('consultant/edit_pendiente', $data); // Cargar la vista del formulario
    }

    // Actualizar pendiente
    public function editPendientePost($id)
    {
        $pendientesModel = new PendientesModel();

        // Recogemos los datos del formulario
        $data = [
            'id_cliente' => $this->request->getPost('id_cliente'),
            'responsable' => $this->request->getPost('responsable'),
            'tarea_actividad' => $this->request->getPost('tarea_actividad'),
            'fecha_cierre' => $this->request->getPost('fecha_cierre'),
            'estado' => $this->request->getPost('estado'),
            'estado_avance' => $this->request->getPost('estado_avance'),
            'evidencia_para_cerrarla' => $this->request->getPost('evidencia_para_cerrarla'),
            // No asignar manualmente 'updated_at'; el modelo lo gestiona automáticamente
        ];

        // Obtener el pendiente actual para obtener 'created_at'
        $pendienteActual = $pendientesModel->find($id);
        if (!$pendienteActual) {
            return redirect()->to('/listPendientes')->with('msg', 'Pendiente no encontrado.');
        }
        $createdAt = strtotime($pendienteActual['created_at']);

        // Validar que la fecha de cierre no sea menor que la fecha de creación
        if ($data['fecha_cierre'] && strtotime($data['fecha_cierre']) < $createdAt) {
            return redirect()->back()->with('msg', 'Error: La fecha de cierre no puede ser anterior a la fecha de creación.')->withInput();
        }

        // Validar que si hay fecha de cierre, el estado no puede ser ABIERTA
        if (!empty($data['fecha_cierre']) && $data['estado'] === 'ABIERTA') {
            return redirect()->back()->with('msg', 'Error: No se puede establecer el estado como ABIERTA si ya hay una fecha de cierre.')->withInput();
        }

        // Calcular conteo_dias
        if ($data['estado'] === 'ABIERTA') {
            $data['conteo_dias'] = (int) floor((time() - $createdAt) / (60 * 60 * 24));
        } elseif ($data['estado'] === 'CERRADA' && !empty($data['fecha_cierre'])) {
            $fechaCierre = strtotime($data['fecha_cierre']);
            $data['conteo_dias'] = (int) floor(($fechaCierre - $createdAt) / (60 * 60 * 24));
        } else {
            $data['conteo_dias'] = 0; // Valor por defecto si no se cumple ninguna condición
        }

        // Actualizar el pendiente
        if ($pendientesModel->update($id, $data)) {
            return redirect()->to('/listPendientes')->with('msg', 'Pendiente actualizado exitosamente');
        } else {
            // Obtener y mostrar los errores de validación
            $errors = $pendientesModel->errors();
            $errorMessage = 'Error al actualizar pendiente.';
            if (!empty($errors)) {
                $errorMessage .= ' ' . implode(' ', array_map(function($msg) {
                    return is_array($msg) ? implode(', ', $msg) : $msg;
                }, $errors));
            }
            return redirect()->back()->with('msg', $errorMessage)->withInput();
        }
    }

    // Eliminar pendiente
    public function deletePendiente($id)
    {
        $pendientesModel = new PendientesModel();

        if ($pendientesModel->delete($id)) {
            return redirect()->to('/listPendientes')->with('msg', 'Pendiente eliminado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al eliminar pendiente');
        }
    }

    // Actualizar campo específico del pendiente
    public function updatePendiente()
    {
        $id = $this->request->getPost('id');
        $field = $this->request->getPost('field');
        $value = $this->request->getPost('value');

        // Definir los campos permitidos para actualización
        $allowedFields = ['tarea_actividad', 'fecha_cierre', 'estado', 'evidencia_para_cerrarla', 'estado_avance'];

        if (!in_array($field, $allowedFields)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Campo no permitido']);
        }

        $model = new PendientesModel();
        $pendiente = $model->find($id);
        if (!$pendiente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pendiente no encontrado']);
        }

        $updateData = [$field => $value];

        // Recalcular "conteo_dias" si se actualiza "fecha_cierre" o "estado"
        if (in_array($field, ['fecha_cierre', 'estado'])) {
            $createdAt = strtotime($pendiente['created_at']);
            $estado = ($field === 'estado') ? $value : $pendiente['estado'];
            $fechaCierre = ($field === 'fecha_cierre') ? $value : $pendiente['fecha_cierre'];

            if ($estado === 'ABIERTA') {
                $updateData['conteo_dias'] = (int) floor((time() - $createdAt) / (60 * 60 * 24));
            } elseif ($estado === 'CERRADA' && !empty($fechaCierre)) {
                $fechaCierreTimestamp = strtotime($fechaCierre);
                $updateData['conteo_dias'] = (int) floor(($fechaCierreTimestamp - $createdAt) / (60 * 60 * 24));
            } else {
                $updateData['conteo_dias'] = 0; // Valor por defecto si no se cumple ninguna condición
            }
        }

        // Validaciones adicionales si se actualizan campos relacionados
        if ($field === 'fecha_cierre' && $value && strtotime($value) < strtotime($pendiente['created_at'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'La fecha de cierre no puede ser anterior a la fecha de creación.']);
        }

        if ($field === 'estado' && $value === 'ABIERTA' && !empty($pendiente['fecha_cierre'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede establecer el estado como ABIERTA si ya hay una fecha de cierre.']);
        }

        if ($model->update($id, $updateData)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Actualizado correctamente']);
        } else {
            $errors = $model->errors();
            $errorMessage = 'Error al actualizar: ';
            if (!empty($errors)) {
                $errorMessage .= implode(' ', array_map(function($msg) {
                    return is_array($msg) ? implode(', ', $msg) : $msg;
                }, $errors));
            } else {
                $errorMessage .= 'No se pudo actualizar el pendiente.';
            }
            return $this->response->setJSON([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }
}
