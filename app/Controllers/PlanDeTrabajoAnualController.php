<?php

namespace App\Controllers;

use App\Models\PtaclienteModel;
use App\Models\ClientModel;
use App\Models\InventarioActividadesArrayModel;

use CodeIgniter\Controller;

class PlanDeTrabajoAnualController extends Controller
{
    // Listar todos los planes de trabajo anual
    public function listPlanDeTrabajoAnual()
    {
        $ptaModel = new PtaclienteModel();
        $clientModel = new ClientModel();
        $inventarioModel = new InventarioActividadesArrayModel();

        // Obtenemos todos los planes de trabajo como arrays
        $planes = $ptaModel->asArray()->findAll();

        $actividades = [];
        foreach ($planes as $plan) {
            // Si 'semana' está vacío pero hay una 'fecha_propuesta', se recalcula la semana
            if (empty($plan['semana']) && !empty($plan['fecha_propuesta'])) {
                $plan['semana'] = date('W', strtotime($plan['fecha_propuesta']));
                // Opcional: Actualizar el registro en la base de datos con la semana calculada
                $ptaModel->update($plan['id_ptacliente'], ['semana' => $plan['semana']]);
            }

            // Obtener nombre del cliente como array
            $cliente = $clientModel->asArray()->find($plan['id_cliente']);
            $actividad = [
                'id_ptacliente' => $plan['id_ptacliente'],
                'nombre_cliente' => $cliente ? $cliente['nombre_cliente'] : 'Cliente no encontrado',
                'id_plandetrabajo' => $plan['id_plandetrabajo'],
                'phva_plandetrabajo' => $plan['phva_plandetrabajo'],
                'numeral_plandetrabajo' => $plan['numeral_plandetrabajo'],
                'actividad_plandetrabajo' => $plan['actividad_plandetrabajo'],
                'responsable_sugerido_plandetrabajo' => $plan['responsable_sugerido_plandetrabajo'],
                'fecha_propuesta' => $plan['fecha_propuesta'],
                'fecha_cierre' => $plan['fecha_cierre'],
                'responsable_definido_paralaactividad' => $plan['responsable_definido_paralaactividad'],
                'estado_actividad' => $plan['estado_actividad'],
                'porcentaje_avance' => $plan['porcentaje_avance'],
                'semana' => $plan['semana'],
                'observaciones' => $plan['observaciones'],
                'created_at' => $plan['created_at'],
                'updated_at' => $plan['updated_at'],
            ];

            // Obtener datos adicionales de la actividad como array
            $actividadInfo = $inventarioModel->asArray()->find($plan['id_plandetrabajo']);
            if ($actividadInfo) {
                $actividad['actividad_plandetrabajo'] = $actividadInfo['actividad_plandetrabajo'];
                $actividad['phva_plandetrabajo'] = $actividadInfo['phva_plandetrabajo'];
                $actividad['numeral_plandetrabajo'] = $actividadInfo['numeral_plandetrabajo'];
            }

            $actividades[] = $actividad;
        }

        $data['actividades'] = $actividades;
        return view('consultant/listplantrabajoanual', $data);
    }


    // Mostrar formulario para agregar nuevo plan de trabajo anual
    public function addPlanDeTrabajoAnual()
    {
        $clientModel = new ClientModel();
        $inventarioModel = new InventarioActividadesArrayModel();

        // Obtener clientes y actividades del inventario para los selects del formulario
        $data['clientes'] = $clientModel->findAll();
        $data['actividades'] = $inventarioModel->findAll();

        return view('consultant/add_plantrabajoanual', $data);
    }

    // Guardar nuevo plan de trabajo anual
    public function addPlanDeTrabajoAnualPost()
    {
        $ptaModel = new PtaclienteModel();

        // Recogemos los datos del formulario
        $fecha_propuesta = $this->request->getPost('fecha_propuesta');

        $data = [
            'id_cliente' => $this->request->getPost('id_cliente'),
            'id_plandetrabajo' => $this->request->getPost('id_plandetrabajo'),
            'phva_plandetrabajo' => $this->request->getPost('phva_plandetrabajo'),
            'numeral_plandetrabajo' => $this->request->getPost('numeral_plandetrabajo'),
            'actividad_plandetrabajo' => $this->request->getPost('actividad_plandetrabajo'),
            'responsable_sugerido_plandetrabajo' => $this->request->getPost('responsable_sugerido_plandetrabajo'),
            'fecha_propuesta' => $fecha_propuesta,
            'fecha_cierre' => $this->request->getPost('fecha_cierre'),
            'responsable_definido_paralaactividad' => $this->request->getPost('responsable_definido_paralaactividad'),
            'estado_actividad' => $this->request->getPost('estado_actividad'),
            'porcentaje_avance' => $this->request->getPost('porcentaje_avance'),
            'semana' => date('W', strtotime($fecha_propuesta)), // Calcular la semana
            'observaciones' => $this->request->getPost('observaciones'),
        ];

        log_message('debug', 'Datos a insertar: ' . print_r($data, true));

        if ($ptaModel->insert($data)) {
            return redirect()->to('/listPlanDeTrabajoAnual')->with('msg', 'Plan de trabajo anual agregado exitosamente');
        } else {
            $errors = $ptaModel->errors(); // Captura los errores de validación
            log_message('error', 'Errores al agregar plan de trabajo: ' . print_r($errors, true));
            return redirect()->back()->with('msg', 'Error al agregar plan de trabajo anual');
        }
    }

    // Mostrar formulario para editar plan de trabajo anual
    public function editPlanDeTrabajoAnual($id)
    {
        $ptaModel = new PtaclienteModel();
        $clientModel = new ClientModel();
        $inventarioModel = new InventarioActividadesArrayModel();

        // Obtener el plan que se va a editar
        $data['plan'] = $ptaModel->find($id);

        // Obtener listas de clientes y actividades para los selects del formulario
        $data['clientes'] = $clientModel->findAll();
        $data['actividades'] = $inventarioModel->findAll();

        return view('consultant/edit_plantrabajoanual', $data);
    }

    // Actualizar plan de trabajo anual
    public function editPlanDeTrabajoAnualPost($id)
    {
        $ptaModel = new PtaclienteModel();

        // Recogemos los datos del formulario
        $fecha_propuesta = $this->request->getPost('fecha_propuesta');

        $data = [
            'id_cliente' => $this->request->getPost('id_cliente'),
            'id_plandetrabajo' => $this->request->getPost('id_plandetrabajo'),
            'phva_plandetrabajo' => $this->request->getPost('phva_plandetrabajo'),
            'numeral_plandetrabajo' => $this->request->getPost('numeral_plandetrabajo'),
            'actividad_plandetrabajo' => $this->request->getPost('actividad_plandetrabajo'),
            'responsable_sugerido_plandetrabajo' => $this->request->getPost('responsable_sugerido_plandetrabajo'),
            'fecha_propuesta' => $fecha_propuesta,
            'fecha_cierre' => $this->request->getPost('fecha_cierre'),
            'responsable_definido_paralaactividad' => $this->request->getPost('responsable_definido_paralaactividad'),
            'estado_actividad' => $this->request->getPost('estado_actividad'),
            'porcentaje_avance' => $this->request->getPost('porcentaje_avance'),
            'semana' => date('W', strtotime($fecha_propuesta)), // Calcular la semana
            'observaciones' => $this->request->getPost('observaciones'),
        ];

        log_message('debug', 'Datos recibidos del formulario: ' . print_r($this->request->getPost(), true));

        if ($ptaModel->update($id, $data)) {
            return redirect()->to('/listPlanDeTrabajoAnual')->with('msg', 'Plan de trabajo anual actualizado exitosamente');
        } else {
            $errors = $ptaModel->errors(); // Captura los errores de validación
            log_message('error', 'Errores al actualizar plan de trabajo: ' . print_r($errors, true));
            return redirect()->back()->with('msg', 'Error al actualizar plan de trabajo anual');
        }
    }

    // Eliminar plan de trabajo anual
    public function deletePlanDeTrabajoAnual($id)
    {
        $ptaModel = new PtaclienteModel();

        // Verificar que el ID sea válido
        if ($id) {
            if ($ptaModel->delete($id)) {
                return redirect()->to('/listPlanDeTrabajoAnual')->with('msg', 'Plan de trabajo anual eliminado exitosamente');
            } else {
                return redirect()->back()->with('msg', 'Error al eliminar el plan de trabajo anual');
            }
        } else {
            return redirect()->back()->with('msg', 'ID inválido o no proporcionado');
        }
    }

    public function updatePlanDeTrabajo()
    {
        $id = $this->request->getPost('id');
        $field = $this->request->getPost('field');
        $value = $this->request->getPost('value');

        $allowedFields = [
            'fecha_cierre',
            'responsable_definido_paralaactividad',
            'responsable_sugerido_plandetrabajo',
            'estado_actividad',
            'porcentaje_avance',
            'observaciones',
            'fecha_propuesta'
        ];

        if (!in_array($field, $allowedFields)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Campo no permitido']);
        }

        if ($field === 'estado_actividad') {
            $allowedStates = ['ABIERTA', 'CERRADA', 'GESTIONANDO'];
            if (!in_array(strtoupper($value), $allowedStates)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Estado no permitido']);
            }
        }

        $ptaModel = new PtaclienteModel();
        $updateData = [$field => $value];

        // Si se actualiza la 'fecha_propuesta', recalcula la semana
        if ($field === 'fecha_propuesta') {
            $week = date('W', strtotime($value));
            $updateData['semana'] = $week;
            log_message('debug', "Fecha propuesta: $value, Semana calculada: $week");
        }

        if ($ptaModel->update($id, $updateData)) {
            // Tras la actualización, verificamos si falta la semana y la fecha propuesta existe
            $plan = $ptaModel->find($id);
            if (empty($plan['semana']) && !empty($plan['fecha_propuesta'])) {
                $week = date('W', strtotime($plan['fecha_propuesta']));
                $ptaModel->update($id, ['semana' => $week]);
                log_message('debug', "Semana recalculada para ID $id: $week");
            }
            return $this->response->setJSON(['success' => true, 'message' => 'Registro actualizado']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'No se pudo actualizar el registro']);
        }
    }
}
