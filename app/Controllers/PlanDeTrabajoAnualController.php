<?php

namespace App\Controllers;

use App\Models\PtaclienteModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class PlanDeTrabajoAnualController extends Controller
{
    /**
     * API: Retorna la lista de clientes en formato JSON.
     * Se espera que el campo 'id_cliente' y 'nombre_cliente' existan en la tabla de clientes.
     */
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

    /**
     * API: Retorna la lista de actividades filtradas por cliente (para uso con DataTables vía AJAX).
     * Se espera recibir por GET el parámetro 'cliente' con el ID del cliente.
     */
    public function getActividadesAjax()
    {
        $ptaModel = new PtaclienteModel();
        $clienteID = $this->request->getGet('cliente');

        if (!$clienteID) {
            // Retornamos estructura vacía, como espera DataTables (data: [])
            return $this->response->setJSON(["data" => []]);
        }

        $builder = $ptaModel->builder();
        $builder->select('tbl_pta_cliente.*, c.nombre_cliente');
        $builder->join('tbl_clientes as c', 'c.id_cliente = tbl_pta_cliente.id_cliente', 'left');
        $builder->where('tbl_pta_cliente.id_cliente', $clienteID);
        $data = $builder->get()->getResultArray();

        // La vista de DataTables configurada en la vista (dataSrc: 'data') espera el JSON con key "data"
        return $this->response->setJSON(["data" => $data]);
    }

    /**
     * API: Actualiza un campo específico de un plan de trabajo anual (útil para la edición inline).
     */
    public function updatePlanDeTrabajo()
    {
        $ptaModel = new PtaclienteModel();

        $id    = $this->request->getPost('id');
        $field = $this->request->getPost('field');
        $value = $this->request->getPost('value');

        // Definir los campos permitidos para actualización
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

        // Validación específica para el campo "estado_actividad"
        if ($field === 'estado_actividad') {
            $allowedStates = ['ABIERTA', 'CERRADA', 'GESTIONANDO'];
            if (!in_array(strtoupper($value), $allowedStates)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Estado no permitido']);
            }
        }

        $updateData = [$field => $value];

        // Si se actualiza la 'fecha_propuesta', recalculamos la semana
        if ($field === 'fecha_propuesta') {
            $week = date('W', strtotime($value));
            $updateData['semana'] = $week;
        }

        if ($ptaModel->update($id, $updateData)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Registro actualizado']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'No se pudo actualizar el registro']);
        }
    }

    /**
     * Retorna la vista que contiene la tabla de actividades (para cargarla vía AJAX).
     * Esta vista debe estar preparada para realizar las llamadas a las API definidas.
     */
    public function listPlanDeTrabajoAnualAjax()
    {
        return view('consultant/list_plantrabajoanual_ajax');
    }

    /*--------------------------------------------------
      MÉTODOS PARA OPERACIONES TRADICIONALES (FORMULARIOS)
    ---------------------------------------------------*/

    /**
     * Lista todos los planes de trabajo anual (vista tradicional).
     */
    public function listPlanDeTrabajoAnual()
    {
        $ptaModel    = new PtaclienteModel();
        $clientModel = new ClientModel();

        $planes = $ptaModel->asArray()->findAll();
        $actividades = [];

        foreach ($planes as $plan) {
            // Si no se ha definido la semana y hay fecha propuesta, se calcula y se guarda.
            if (empty($plan['semana']) && !empty($plan['fecha_propuesta'])) {
                $plan['semana'] = date('W', strtotime($plan['fecha_propuesta']));
                $ptaModel->update($plan['id_ptacliente'], ['semana' => $plan['semana']]);
            }

            $cliente = $clientModel->asArray()->find($plan['id_cliente']);
            $actividades[] = [
                'id_ptacliente'                        => $plan['id_ptacliente'],
                'nombre_cliente'                       => $cliente ? $cliente['nombre_cliente'] : 'Cliente no encontrado',
                'tipo_servicio'                        => $plan['tipo_servicio'],
                'phva_plandetrabajo'                   => $plan['phva_plandetrabajo'],
                'numeral_plandetrabajo'                => $plan['numeral_plandetrabajo'],
                'actividad_plandetrabajo'              => $plan['actividad_plandetrabajo'],
                'responsable_sugerido_plandetrabajo'     => $plan['responsable_sugerido_plandetrabajo'],
                'fecha_propuesta'                      => $plan['fecha_propuesta'],
                'fecha_cierre'                         => $plan['fecha_cierre'],
                'responsable_definido_paralaactividad' => $plan['responsable_definido_paralaactividad'],
                'estado_actividad'                     => $plan['estado_actividad'],
                'porcentaje_avance'                    => $plan['porcentaje_avance'],
                'semana'                               => $plan['semana'],
                'observaciones'                        => $plan['observaciones'],
                'created_at'                           => $plan['created_at'],
                'updated_at'                           => $plan['updated_at'],
            ];
        }

        $data['actividades'] = $actividades;
        return view('consultant/listplantrabajoanual', $data);
    }

    /**
     * Muestra el formulario para agregar un nuevo plan de trabajo anual.
     */
    public function addPlanDeTrabajoAnual()
    {
        $clientModel = new ClientModel();
        $data['clientes'] = $clientModel->findAll();

        return view('consultant/add_plantrabajoanual', $data);
    }

    /**
     * Procesa el formulario para agregar un nuevo plan de trabajo anual.
     */
    public function addPlanDeTrabajoAnualPost()
    {
        $ptaModel = new PtaclienteModel();
        $fecha_propuesta = $this->request->getPost('fecha_propuesta');

        $data = [
            'id_cliente'                         => $this->request->getPost('id_cliente'),
            'tipo_servicio'                      => $this->request->getPost('tipo_servicio'),
            'phva_plandetrabajo'                 => $this->request->getPost('phva_plandetrabajo'),
            'numeral_plandetrabajo'              => $this->request->getPost('numeral_plandetrabajo'),
            'actividad_plandetrabajo'            => $this->request->getPost('actividad_plandetrabajo'),
            'responsable_sugerido_plandetrabajo'   => $this->request->getPost('responsable_sugerido_plandetrabajo'),
            'fecha_propuesta'                    => $fecha_propuesta,
            'fecha_cierre'                       => $this->request->getPost('fecha_cierre'),
            'responsable_definido_paralaactividad' => $this->request->getPost('responsable_definido_paralaactividad'),
            'estado_actividad'                   => $this->request->getPost('estado_actividad'),
            'porcentaje_avance'                  => $this->request->getPost('porcentaje_avance') !== '' ? (float)$this->request->getPost('porcentaje_avance') : 0.00,
            'semana'                             => !empty($fecha_propuesta) ? (int)date('W', strtotime($fecha_propuesta)) : 1,
            'observaciones'                      => $this->request->getPost('observaciones'),
        ];

        if ($ptaModel->insert($data)) {
            return redirect()->to('/listPlanDeTrabajoAnual')->with('msg', 'Plan de trabajo anual agregado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar plan de trabajo anual');
        }
    }

    /**
     * Muestra el formulario para editar un plan de trabajo anual.
     */
    public function editPlanDeTrabajoAnual($id)
    {
        $ptaModel    = new PtaclienteModel();
        $clientModel = new ClientModel();

        $data['plan'] = $ptaModel->find($id);
        $data['clientes'] = $clientModel->findAll();

        if (!$data['plan']) {
            return redirect()->to('/listPlanDeTrabajoAnual')->with('msg', 'Plan de trabajo no encontrado');
        }

        return view('consultant/edit_plantrabajoanual', $data);
    }

    /**
     * Procesa el formulario de edición de un plan de trabajo anual.
     */
    public function editPlanDeTrabajoAnualPost($id)
    {
        $ptaModel = new PtaclienteModel();
        $fecha_propuesta = $this->request->getPost('fecha_propuesta');

        $data = [
            'id_cliente'                         => $this->request->getPost('id_cliente'),
            'tipo_servicio'                      => $this->request->getPost('tipo_servicio'),
            'phva_plandetrabajo'                 => $this->request->getPost('phva_plandetrabajo'),
            'numeral_plandetrabajo'              => $this->request->getPost('numeral_plandetrabajo'),
            'actividad_plandetrabajo'            => $this->request->getPost('actividad_plandetrabajo'),
            'responsable_sugerido_plandetrabajo'   => $this->request->getPost('responsable_sugerido_plandetrabajo'),
            'fecha_propuesta'                    => $fecha_propuesta,
            'fecha_cierre'                       => $this->request->getPost('fecha_cierre'),
            'responsable_definido_paralaactividad' => $this->request->getPost('responsable_definido_paralaactividad'),
            'estado_actividad'                   => $this->request->getPost('estado_actividad'),
            'porcentaje_avance'                  => $this->request->getPost('porcentaje_avance'),
            'semana'                             => !empty($fecha_propuesta) ? date('W', strtotime($fecha_propuesta)) : null,
            'observaciones'                      => $this->request->getPost('observaciones'),
        ];

        if ($ptaModel->update($id, $data)) {
            return redirect()->to('/listPlanDeTrabajoAnual')->with('msg', 'Plan de trabajo anual actualizado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar plan de trabajo anual');
        }
    }

    /**
     * Elimina un plan de trabajo anual según su ID.
     */
    public function deletePlanDeTrabajoAnual($id)
    {
        $ptaModel = new PtaclienteModel();
        $plan = $ptaModel->find($id);
        if (!$plan) {
            return redirect()->to('/listPlanDeTrabajoAnual')->with('msg', 'El plan de trabajo no existe');
        }

        if ($ptaModel->delete($id)) {
            return redirect()->to('/listPlanDeTrabajoAnual')->with('msg', 'Plan de trabajo anual eliminado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al eliminar el plan de trabajo anual');
        }
    }
}
