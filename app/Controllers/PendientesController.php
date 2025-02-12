<?php

namespace App\Controllers;

use App\Models\PendientesModel;
use App\Models\ClientModel; // Para obtener información del cliente
use CodeIgniter\Controller;

class PendientesController extends Controller
{

    public function listPendientesAjax()
    {
        return view('consultant/list_pendientes_ajax');
    }

    // API: Retorna la lista de clientes en formato JSON
    public function getClientes()
    {
        $clientModel = new ClientModel();
        $clientes = $clientModel->findAll();
        $data = [];
        foreach ($clientes as $cliente) {
            $data[] = [
                'id'     => $cliente['id_cliente'],
                'nombre' => $cliente['nombre_cliente']
            ];
        }
        return $this->response->setJSON($data);
    }

    // API: Retorna la lista de pendientes filtrada por el parámetro 'cliente'
    public function getPendientesAjax()
    {
        $clienteID = $this->request->getGet('cliente');
        $pendientesModel = new PendientesModel();
        $clientModel = new ClientModel();

        if (empty($clienteID)) {
            return $this->response->setJSON([]);
        }

        $pendientes = $pendientesModel->where('id_cliente', $clienteID)->findAll();

        // Enriquecer cada pendiente con el nombre del cliente
        foreach ($pendientes as &$pendiente) {
            $cliente = $clientModel->find($pendiente['id_cliente']);
            $pendiente['nombre_cliente'] = $cliente['nombre_cliente'] ?? 'Cliente desconocido';
            // Generar botones de acciones
            $pendiente['acciones'] = '<a href="' . base_url('editPendiente/' . $pendiente['id_pendientes']) . '" class="btn btn-warning btn-sm">Editar</a> ' .
                '<a href="' . base_url('deletePendiente/' . $pendiente['id_pendientes']) . '" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Estás seguro de eliminar este pendiente?\');">Eliminar</a>';
        }

        return $this->response->setJSON($pendientes);
    }

    // API: Actualiza un campo específico de un pendiente (para inline editing)
    public function updatePendiente()
    {
        $id = $this->request->getPost('id');
        $field = $this->request->getPost('field');
        $value = $this->request->getPost('value');

        // Definir los campos permitidos para actualización
        $allowedFields = ['tarea_actividad', 'fecha_cierre', 'estado', 'estado_avance', 'evidencia_para_cerrarla', 'fecha_asignacion'];
        if (!in_array($field, $allowedFields)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Campo no permitido']);
        }

        $model = new PendientesModel();
        $pendiente = $model->find($id);
        if (!$pendiente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pendiente no encontrado']);
        }

        $updateData = [$field => $value];

        // Si el campo afecta el cálculo de 'conteo_dias'
        $fechaAsignacion = strtotime($pendiente['fecha_asignacion']);
        $estado = ($field === 'estado') ? $value : $pendiente['estado'];
        $fechaCierre = ($field === 'fecha_cierre') ? $value : $pendiente['fecha_cierre'];

        if ($estado === 'ABIERTA') {
            $updateData['conteo_dias'] = (int) floor((time() - $fechaAsignacion) / (60 * 60 * 24));
        } elseif ($estado === 'CERRADA' && !empty($fechaCierre)) {
            $updateData['conteo_dias'] = (int) floor((strtotime($fechaCierre) - $fechaAsignacion) / (60 * 60 * 24));
        } else {
            $updateData['conteo_dias'] = 0;
        }

        if ($model->update($id, $updateData)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Registro actualizado correctamente',
                'updatedValue' => $updateData['conteo_dias']
            ]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al actualizar el registro']);
        }
    }
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
            $fechaAsignacion = strtotime($pendiente['fecha_asignacion']);
            if ($pendiente['estado'] === 'ABIERTA') {
                $pendiente['conteo_dias'] = (int) floor((time() - $fechaAsignacion) / (60 * 60 * 24));
            } elseif ($pendiente['estado'] === 'CERRADA' && !empty($pendiente['fecha_cierre'])) {
                $fechaCierre = strtotime($pendiente['fecha_cierre']);
                $pendiente['conteo_dias'] = (int) floor(($fechaCierre - $fechaAsignacion) / (60 * 60 * 24));
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

        // Recogemos los datos del formulario sin 'created_at'
        $data = [
            'id_cliente' => $this->request->getPost('id_cliente'),
            'fecha_asignacion' => $this->request->getPost('fecha_asignacion'), // Nueva columna
            'responsable' => $this->request->getPost('responsable'),
            'tarea_actividad' => $this->request->getPost('tarea_actividad'),
            'fecha_cierre' => $this->request->getPost('fecha_cierre'),
            'estado' => $this->request->getPost('estado'),
            'estado_avance' => $this->request->getPost('estado_avance'),
            'evidencia_para_cerrarla' => $this->request->getPost('evidencia_para_cerrarla'),
            // 'created_at' se manejará automáticamente por el modelo
        ];

        // Validar que la fecha de cierre no sea menor que la fecha de asignación
        if ($data['fecha_cierre'] && strtotime($data['fecha_cierre']) < strtotime($data['fecha_asignacion'])) {
            return redirect()->back()->with('msg', 'Error: La fecha de cierre no puede ser anterior a la fecha de asignación.')->withInput();
        }

        // Validar que si hay fecha de cierre, el estado no puede ser ABIERTA
        /* if (!empty($data['fecha_cierre']) && $data['estado'] === 'ABIERTA') {
            return redirect()->back()->with('msg', 'Error: No se puede establecer el estado como ABIERTA si ya hay una fecha de cierre.')->withInput();
        } */

        // Insertar el nuevo pendiente sin 'conteo_dias'
        if ($pendientesModel->insert($data)) {
            // Obtener el ID del registro insertado
            $insertedId = $pendientesModel->getInsertID();

            // Obtener el registro recién insertado para obtener 'fecha_asignacion'
            $pendiente = $pendientesModel->find($insertedId);
            if ($pendiente) {
                // Calcular 'conteo_dias' basado en el estado
                if ($pendiente['estado'] === 'ABIERTA') {
                    $conteo_dias = (int) floor((time() - strtotime($pendiente['fecha_asignacion'])) / (60 * 60 * 24));
                } elseif ($pendiente['estado'] === 'CERRADA' && !empty($pendiente['fecha_cierre'])) {
                    $conteo_dias = (int) floor((strtotime($pendiente['fecha_cierre']) - strtotime($pendiente['fecha_asignacion'])) / (60 * 60 * 24));
                } else {
                    $conteo_dias = 0;
                }

                // Actualizar 'conteo_dias'
                $pendientesModel->update($insertedId, ['conteo_dias' => $conteo_dias]);

                return redirect()->to('/listPendientes')->with('msg', 'Pendiente agregado exitosamente');
            } else {
                // Si no se pudo recuperar el registro, eliminar la inserción y mostrar error
                $pendientesModel->delete($insertedId);
                return redirect()->back()->with('msg', 'Error al agregar pendiente: No se pudo recuperar el registro insertado.')->withInput();
            }
        } else {
            // Obtener y mostrar los errores de validación
            $errors = $pendientesModel->errors();
            $errorMessage = 'Error al agregar pendiente.';
            if (!empty($errors)) {
                $errorMessage .= ' ' . implode(' ', array_map(function ($msg) {
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
            'fecha_asignacion' => $this->request->getPost('fecha_asignacion'), // Asegurarse de que esta columna no cambie si no es necesario
            'responsable' => $this->request->getPost('responsable'),
            'tarea_actividad' => $this->request->getPost('tarea_actividad'),
            'fecha_cierre' => $this->request->getPost('fecha_cierre'),
            'fecha_asignacion' => $this->request->getPost('fecha_asignacion'),
            'estado' => $this->request->getPost('estado'),
            'estado_avance' => $this->request->getPost('estado_avance'),
            'evidencia_para_cerrarla' => $this->request->getPost('evidencia_para_cerrarla'),
            // 'updated_at' se manejará automáticamente por el modelo
        ];

        // Obtener el pendiente actual para obtener 'fecha_asignacion'
        $pendienteActual = $pendientesModel->find($id);
        if (!$pendienteActual) {
            return redirect()->to('/listPendientes')->with('msg', 'Pendiente no encontrado.');
        }
        $fechaAsignacion = strtotime($pendienteActual['fecha_asignacion']);

        // Validar que la fecha de cierre no sea menor que la fecha de asignación
        if ($data['fecha_cierre'] && strtotime($data['fecha_cierre']) < $fechaAsignacion) {
            return redirect()->back()->with('msg', 'Error: La fecha de cierre no puede ser anterior a la fecha de asignación.')->withInput();
        }

        // Validar que si hay fecha de cierre, el estado no puede ser ABIERTA
        /* if (!empty($data['fecha_cierre']) && $data['estado'] === 'ABIERTA') {
            return redirect()->back()->with('msg', 'Error: No se puede establecer el estado como ABIERTA si ya hay una fecha de cierre.')->withInput();
        } */

        // Calcular 'conteo_dias' basado en el estado
        if ($data['estado'] === 'ABIERTA') {
            $conteo_dias = (int) floor((time() - $fechaAsignacion) / (60 * 60 * 24));
        } elseif ($data['estado'] === 'CERRADA' && !empty($data['fecha_cierre'])) {
            $conteo_dias = (int) floor((strtotime($data['fecha_cierre']) - $fechaAsignacion) / (60 * 60 * 24));
        } else {
            $conteo_dias = 0;
        }

        // Actualizar 'conteo_dias' en los datos a actualizar
        $data['conteo_dias'] = $conteo_dias;

        // Actualizar el pendiente
        if ($pendientesModel->update($id, $data)) {
            return redirect()->to('/listPendientes')->with('msg', 'Pendiente actualizado exitosamente');
        } else {
            // Obtener y mostrar los errores de validación
            $errors = $pendientesModel->errors();
            $errorMessage = 'Error al actualizar pendiente.';
            if (!empty($errors)) {
                $errorMessage .= ' ' . implode(' ', array_map(function ($msg) {
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


    public function recalcularConteoDias()
    {
        // Verificar si la solicitud es una solicitud AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $model = new PendientesModel();
        $pendientes = $model->findAll();

        $updatedCount = 0;
        foreach ($pendientes as $pendiente) {
            $fechaAsignacion = strtotime($pendiente['fecha_asignacion']);
            $fechaCierre = !empty($pendiente['fecha_cierre']) ? strtotime($pendiente['fecha_cierre']) : null;
            $estado = $pendiente['estado'];

            if ($estado === 'ABIERTA') {
                $conteo_dias = (int) floor((time() - $fechaAsignacion) / (60 * 60 * 24));
            } elseif ($estado === 'CERRADA' && $fechaCierre) {
                $conteo_dias = (int) floor(($fechaCierre - $fechaAsignacion) / (60 * 60 * 24));
            } else {
                $conteo_dias = 0;
            }

            // Actualizar solo si el valor ha cambiado
            if ($pendiente['conteo_dias'] != $conteo_dias) {
                $model->update($pendiente['id_pendientes'], ['conteo_dias' => $conteo_dias]);
                $updatedCount++;
            }
        }

        // Obtener todos los pendientes actualizados con el nombre del cliente
        $pendientesActualizados = $model->getPendientesWithCliente();

        return $this->response->setJSON([
            'success' => true,
            'message' => "Conteo de días actualizado para {$updatedCount} registros.",
            'pendientes' => $pendientesActualizados
        ]);
    }
}
