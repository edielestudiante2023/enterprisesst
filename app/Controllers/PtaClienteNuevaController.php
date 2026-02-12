<?php

namespace App\Controllers;

use App\Models\PtaClienteNuevaModel;
use App\Models\ClientModel;
use App\Services\PtaAuditService;
use CodeIgniter\Controller;

class PtaClienteNuevaController extends Controller
{

    public function listPtaClienteNuevaModel()
    {
        $clientModel = new ClientModel();
        $clients     = $clientModel->findAll();

        $request     = service('request');
        $cliente     = $request->getGet('cliente');
        $fecha_desde = $request->getGet('fecha_desde');
        $fecha_hasta = $request->getGet('fecha_hasta');
        $estado      = $request->getGet('estado');

        $records = null;

        // Si se ha enviado al menos un cliente, realizar la consulta
        if (!empty($cliente)) {
            $ptaModel = new PtaClienteNuevaModel();

            // Si tiene fechas específicas, usar rango de fechas
            if (!empty($fecha_desde) && !empty($fecha_hasta)) {
                // Primero verificar si hay datos en un rango más amplio
                $extendedStart = date('Y-m-d', strtotime($fecha_desde . ' -30 days'));
                $extendedEnd = date('Y-m-d', strtotime($fecha_hasta . ' +30 days'));

                $checkExtended = $ptaModel->where('id_cliente', $cliente)
                                        ->where('fecha_propuesta >=', $extendedStart)
                                        ->where('fecha_propuesta <=', $extendedEnd)
                                        ->countAllResults(false);

                // Realizar la consulta con el rango original
                $ptaModel->where('id_cliente', $cliente);
                $ptaModel->where('fecha_propuesta >=', $fecha_desde);
                $ptaModel->where('fecha_propuesta <=', $fecha_hasta);
            } else {
                // Sin fechas: mostrar TODOS los registros del cliente
                $ptaModel->where('id_cliente', $cliente);
                $checkExtended = 0; // No aplicable en este caso
            }

            // Aplicar filtro de estado si se proporciona
            if (!empty($estado)) {
                $ptaModel->where('estado_actividad', $estado);
            }

            // Obtener TODOS los registros sin paginación - DataTables manejará la paginación en el cliente
            $records = $ptaModel->findAll();

            // Mensajes según el resultado
            if (!empty($fecha_desde) && !empty($fecha_hasta)) {
                // Si no hay registros en el rango actual pero sí en el rango extendido
                if (empty($records) && $checkExtended > 0) {
                    session()->setFlashdata('warning', 'No se encontraron registros en el rango de fechas seleccionado. Intente ampliar el rango de fechas para ver más resultados.');
                } 
                // Si no hay registros en ningún rango
                elseif (empty($records)) {
                    session()->setFlashdata('info', 'No se encontraron registros, por defecto prueba con rango 1 ene a 31 dic. Si en definitiva no cargan datos Por favor, comuníquese con su backoffice para verificar la información.');
                }
            } else {
                // Sin fechas: mensaje diferente si no hay registros
                if (empty($records)) {
                    session()->setFlashdata('info', 'No se encontraron registros para este cliente. Por favor, comuníquese con su backoffice para verificar la información.');
                } else {
                    session()->setFlashdata('success', 'Mostrando todos los registros del cliente seleccionado (' . count($records) . ' registros encontrados).');
                }
            }

            // Mapear el nombre del cliente a cada registro
            $clientsArray = [];
            foreach ($clients as $clientData) {
                $clientsArray[$clientData['id_cliente']] = $clientData['nombre_cliente'];
            }
            foreach ($records as &$record) {
                $idCliente = $record['id_cliente'];
                $record['nombre_cliente'] = isset($clientsArray[$idCliente]) ? $clientsArray[$idCliente] : 'N/A';
            }
        }

        $filters = [
            'cliente'     => $cliente,
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta,
            'estado'      => $estado,
        ];

        $data = [
            'clients' => $clients,
            'records' => $records,
            'filters' => $filters,
        ];

        return view('consultant/list_pta_cliente_nueva', $data);
    }

    /**
     * Muestra el formulario para agregar un nuevo registro.
     */
    public function addPtaClienteNuevaModel()
    {
        $clientModel = new ClientModel();
        $clients     = $clientModel->findAll();
        // Obtener filtros desde GET para pasarlos a la vista
        $filters = $this->request->getGet();

        $data = [
            'clients' => $clients,
            'filters' => $filters,
        ];
        return view('consultant/add_pta_cliente_nueva', $data);
    }

    /**
     * Procesa el formulario para agregar un nuevo registro.
     */
    public function addpostPtaClienteNuevaModel()
    {
        $ptaModel = new PtaClienteNuevaModel();
        $data = $this->request->getPost();
        $ptaModel->insert($data);

        // Recuperar filtros enviados desde el formulario (campos ocultos)
        $filters = [
            'cliente'     => $this->request->getPost('filter_cliente'),
            'fecha_desde' => $this->request->getPost('filter_fecha_desde'),
            'fecha_hasta' => $this->request->getPost('filter_fecha_hasta'),
            'estado'      => $this->request->getPost('filter_estado'),
        ];

        return redirect()->to('/pta-cliente-nueva/list?' . http_build_query($filters))
            ->with('message', 'Registro agregado correctamente.');
    }

    /**
     * Muestra el formulario para editar un registro.
     */
    public function editPtaClienteNuevaModel($id = null)
    {
        $ptaModel    = new PtaClienteNuevaModel();
        $clientModel = new ClientModel();

        $record = $ptaModel->find($id);
        if (!$record) {
            return redirect()->back()->with('error', 'Registro no encontrado.');
        }

        $clients = $clientModel->findAll();
        // Obtener filtros desde GET
        $filters = service('request')->getGet();

        $data = [
            'record'  => $record,
            'clients' => $clients,
            'filters' => $filters,
        ];
        return view('consultant/edit_pta_cliente_nueva', $data);
    }

    /**
     * Procesa el formulario para editar un registro.
     */
    public function editpostPtaClienteNuevaModel($id = null)
    {
        $ptaModel = new PtaClienteNuevaModel();

        // Recoger datos del formulario
        $data = $this->request->getPost();
        $ptaModel->update($id, $data);

        // Recuperar filtros enviados desde campos ocultos
        $filters = [
            'cliente'     => $this->request->getPost('filter_cliente'),
            'fecha_desde' => $this->request->getPost('filter_fecha_desde'),
            'fecha_hasta' => $this->request->getPost('filter_fecha_hasta'),
            'estado'      => $this->request->getPost('filter_estado'),
        ];

        return redirect()->to('/pta-cliente-nueva/list?' . http_build_query($filters))
            ->with('message', 'Registro actualizado correctamente.');
    }

    /**
     * Elimina un registro.
     */
    public function deletePtaClienteNuevaModel($id = null)
    {
        // Verificar que se pase un ID válido
        if (empty($id) || $id == 0) {
            return redirect()->to('/pta-cliente-nueva/list')
                ->with('error', 'ID no válido para eliminar.');
        }

        $ptaModel = new PtaClienteNuevaModel();
        $ptaModel->where('id_ptacliente', $id)->delete();

        // Recuperar filtros desde GET para mantenerlos
        $filters = $this->request->getGet();

        return redirect()->to('/pta-cliente-nueva/list?' . http_build_query($filters))
            ->with('message', 'Registro eliminado correctamente.');
    }

    /**
     * Actualiza un registro mediante edición inline.
     * Se permiten editar todas las columnas excepto:
     *  - id_ptacliente
     *  - responsable_definido_paralaactividad
     *  - semana
     *  - created_at
     *  - updated_at
     *
     * Se espera recibir vía POST el ID y el campo modificado.
     */
    public function editinginlinePtaClienteNuevaModel()
    {
        $ptaModel = new PtaClienteNuevaModel();
        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'ID es requerido.'
            ]);
        }
        $postData = $this->request->getPost();
        $disallowed = [
            'id_ptacliente',
            'responsable_definido_paralaactividad',
            'semana',
            'created_at',
            'updated_at'
        ];
        foreach ($disallowed as $field) {
            if (isset($postData[$field])) {
                unset($postData[$field]);
            }
        }
        
        // Auto-calcular porcentaje basado en el estado
        if (isset($postData['estado_actividad'])) {
            $estado = $postData['estado_actividad'];
            switch ($estado) {
                case 'CERRADA':
                    $postData['porcentaje_avance'] = 100;
                    break;
                case 'GESTIONANDO':
                    $postData['porcentaje_avance'] = 50;
                    break;
                case 'ABIERTA':
                    $postData['porcentaje_avance'] = 0;
                    break;
            }
        }
        
        $ptaModel->update($id, $postData);
        
        // Retornar también el porcentaje actualizado para actualizar la vista
        $response = [
            'status'  => 'success',
            'message' => 'Registro actualizado inline correctamente.'
        ];
        
        if (isset($postData['porcentaje_avance'])) {
            $response['porcentaje_avance'] = $postData['porcentaje_avance'];
        }
        
        return $this->response->setJSON($response);
    }

    /**
     * Actualiza el porcentaje de avance a 100 para registros cerrados
     */
    public function updateCerradas()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request method']);
        }

        $ids = $this->request->getPost('ids');
        if (empty($ids)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No IDs provided']);
        }

        $ptaModel = new PtaClienteNuevaModel();
        $data = ['porcentaje_avance' => 100];
        
        try {
            foreach ($ids as $id) {
                $ptaModel->update($id, $data);
            }
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Todos los cerrados quedaron calificados con 100'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error updating records: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * GESTIÓN RÁPIDA: Actualiza fecha_propuesta al último día del mes seleccionado.
     * Recibe vía AJAX: id (ID de la actividad) y month (1-12).
     */
    public function updateDateByMonth()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solicitud no válida'
            ]);
        }

        $id    = $this->request->getPost('id');
        $month = (int) $this->request->getPost('month');

        if (!$id || $month < 1 || $month > 12) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parámetros inválidos'
            ]);
        }

        $model    = new PtaClienteNuevaModel();
        $activity = $model->find($id);

        if (!$activity) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Actividad no encontrada'
            ]);
        }

        // Determinar el año: usar el de la fecha existente o el año actual
        if (!empty($activity['fecha_propuesta'])) {
            $year = date('Y', strtotime($activity['fecha_propuesta']));
        } else {
            $year = date('Y');
        }

        // Calcular último día del mes (maneja bisiestos automáticamente)
        $lastDayDate = new \DateTime("{$year}-{$month}-01");
        $lastDayDate->modify('last day of this month');
        $newDate = $lastDayDate->format('Y-m-d');

        $oldDate = $activity['fecha_propuesta'];

        $model->update($id, ['fecha_propuesta' => $newDate]);

        // Registrar en auditoría (no bloquea la operación principal si falla)
        try {
            PtaAuditService::log(
                (int) $id,
                'UPDATE',
                'fecha_propuesta',
                $oldDate,
                $newDate,
                'updateDateByMonth',
                (int) $activity['id_cliente']
            );
        } catch (\Exception $e) {
            log_message('error', 'Audit log failed in updateDateByMonth: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'success'   => true,
            'message'   => 'Fecha actualizada correctamente',
            'newDate'   => $newDate,
            'formatted' => $lastDayDate->format('d/m/Y')
        ]);
    }

    public function exportExcelPtaClienteNuevaModel()
    {
        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();
        $filters = $this->request->getGet();
        $ptaModel = new PtaClienteNuevaModel();

        // Aplicar los mismos filtros que en listPtaClienteNuevaModel
        if (!empty($filters['cliente'])) {
            $ptaModel->where('id_cliente', $filters['cliente']);

            // Si tiene fechas específicas, usar rango de fechas
            if (!empty($filters['fecha_desde']) && !empty($filters['fecha_hasta'])) {
                $ptaModel->where('fecha_propuesta >=', $filters['fecha_desde']);
                $ptaModel->where('fecha_propuesta <=', $filters['fecha_hasta']);
            }

            // Aplicar filtro de estado si se proporciona
            if (!empty($filters['estado'])) {
                $ptaModel->where('estado_actividad', $filters['estado']);
            }
        }

        $records = $ptaModel->findAll();

        // Mapear el nombre del cliente
        $clientsArray = [];
        foreach ($clients as $clientData) {
            $clientsArray[$clientData['id_cliente']] = $clientData['nombre_cliente'];
        }
        foreach ($records as &$record) {
            $idCliente = $record['id_cliente'];
            $record['nombre_cliente'] = isset($clientsArray[$idCliente]) ? $clientsArray[$idCliente] : 'N/A';
        }

        // Preparar la descarga como Excel (CSV)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="pta_cliente_nueva.xls"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        // Encabezado (omitiendo columnas ocultas y "Tipo Servicio")
        $header = ['ID', 'Cliente', 'PHVA', 'Numeral Plan Trabajo', 'Actividad', 'Responsable Sugerido', 'Fecha Propuesta', 'Fecha Cierre', 'Estado Actividad', 'Porcentaje Avance', 'Observaciones'];
        fputcsv($output, $header, "\t");
        foreach ($records as $row) {
            $data = [
                $row['id_ptacliente'],
                $row['nombre_cliente'],
                $row['phva_plandetrabajo'],
                $row['numeral_plandetrabajo'],
                $row['actividad_plandetrabajo'],
                $row['responsable_sugerido_plandetrabajo'],
                $row['fecha_propuesta'],
                $row['fecha_cierre'],
                $row['estado_actividad'],
                $row['porcentaje_avance'],
                $row['observaciones']
            ];
            fputcsv($output, $data, "\t");
        }
        fclose($output);
        exit;
    }
}
