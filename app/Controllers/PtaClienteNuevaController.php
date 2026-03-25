<?php

namespace App\Controllers;

use App\Models\PtaClienteNuevaModel;
use App\Models\PlanModel;
use App\Models\ClientModel;
use App\Models\ClienteContextoSstModel;
use App\Models\ContractModel;
use App\Services\PtaAuditService;
use App\Services\PtaIAService;
use App\Services\PtaTransicionesService;
use App\Libraries\WorkPlanLibrary;
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
        $anio        = $request->getGet('anio');
        $estado      = $request->getGet('estado');
        $anioActual  = (int) date('Y');

        if ($anio === null || $anio === '') {
            $anio = (string) $anioActual;
        }

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
                // Sin fechas: filtrar por año seleccionado, salvo "todos"
                $ptaModel->where('id_cliente', $cliente);
                if ($anio !== 'todos') {
                    $ptaModel->where('YEAR(fecha_propuesta)', (int) $anio);
                }
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
            'anio'        => $anio,
            'estado'      => $estado,
        ];

        $data = [
            'clients'     => $clients,
            'records'     => $records,
            'filters'     => $filters,
            'anioActual'  => $anioActual,
            'aniosFiltro' => ['todos', '2025', '2026', '2027', '2028', '2029', '2030'],
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
            'anio'        => $this->request->getPost('filter_anio'),
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
            'anio'        => $this->request->getPost('filter_anio'),
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
        if (empty($id) || $id == 0) {
            return redirect()->to('/pta-cliente-nueva/list')
                ->with('error', 'ID no válido para eliminar.');
        }

        $ptaModel = new PtaClienteNuevaModel();
        $datosAnteriores = $ptaModel->find($id);

        if ($datosAnteriores) {
            try {
                PtaAuditService::logDelete($id, $datosAnteriores, __METHOD__);
            } catch (\Exception $e) {
                log_message('error', 'Audit failed in delete: ' . $e->getMessage());
            }
        }

        $ptaModel->where('id_ptacliente', $id)->delete();

        $filters = $this->request->getGet();

        return redirect()->to('/pta-cliente-nueva/list?' . http_build_query($filters))
            ->with('message', 'Registro eliminado correctamente.');
    }

    /**
     * Elimina múltiples registros vía AJAX.
     */
    public function deleteMultiplePtaClienteNuevaModel()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
        }

        $ids = $this->request->getPost('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No se proporcionaron IDs']);
        }

        $ptaModel = new PtaClienteNuevaModel();
        $deleted = 0;

        try {
            foreach ($ids as $id) {
                $id = (int) $id;
                if ($id <= 0) continue;

                $datosAnteriores = $ptaModel->find($id);
                if ($datosAnteriores) {
                    PtaAuditService::logDelete($id, $datosAnteriores, __METHOD__);
                    $ptaModel->where('id_ptacliente', $id)->delete();
                    $deleted++;
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $deleted . ' registro(s) eliminado(s) correctamente.',
                'deleted' => $deleted
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Corrige CERRADA sin fecha_cierre asignando fecha_propuesta
     */
    public function fixCerradasSinFecha()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Método no permitido']);
        }

        $idCliente = (int) ($this->request->getPost('id_cliente') ?? 0);

        try {
            $db = \Config\Database::connect();

            $whereClause = "estado_actividad = 'CERRADA' AND (fecha_cierre IS NULL OR YEAR(fecha_cierre) = 0)";
            if ($idCliente > 0) {
                $whereClause .= " AND id_cliente = " . $db->escape($idCliente);
            }

            $count = (int) $db->query("SELECT COUNT(*) as total FROM tbl_pta_cliente WHERE {$whereClause}")->getRow()->total;

            $db->query("UPDATE tbl_pta_cliente SET fecha_cierre = fecha_propuesta WHERE {$whereClause}");

            return $this->response->setJSON([
                'success' => true,
                'message' => "Se corrigieron {$count} actividades CERRADA sin fecha de cierre.",
                'fixed' => $count
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Elimina todas las actividades ABIERTA de un cliente
     */
    public function deleteAbiertas()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Método no permitido']);
        }

        $idCliente = $this->request->getPost('id_cliente');
        if (empty($idCliente)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no especificado']);
        }

        try {
            $db = \Config\Database::connect();

            $count = $db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('estado_actividad', 'ABIERTA')
                ->countAllResults();

            $db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('estado_actividad', 'ABIERTA')
                ->delete();

            return $this->response->setJSON([
                'success' => true,
                'message' => "Se eliminaron {$count} actividades ABIERTA del cliente.",
                'deleted' => $count
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Regenera plan desde CSV sin duplicar actividades existentes
     */
    public function regenerarPlan()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Método no permitido']);
        }

        $idCliente = $this->request->getPost('id_cliente');
        $year = (int) $this->request->getPost('year');
        $serviceType = strtolower($this->request->getPost('service_type') ?? '');

        if (empty($idCliente) || empty($year) || empty($serviceType)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        }

        try {
            $db = \Config\Database::connect();
            $workPlanLibrary = new WorkPlanLibrary();
            $activities = $workPlanLibrary->getActivities((int)$idCliente, $year, $serviceType);

            if (empty($activities)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No se encontraron actividades para esta combinación']);
            }

            $currentYear = date('Y');
            $existingActivities = $db->table('tbl_pta_cliente')
                ->select('actividad_plandetrabajo')
                ->where('id_cliente', $idCliente)
                ->where("YEAR(fecha_propuesta)", $currentYear)
                ->get()
                ->getResultArray();
            $existingSet = array_column($existingActivities, 'actividad_plandetrabajo');

            $planModel = new PlanModel();
            $inserted = 0;
            $skipped = 0;

            foreach ($activities as $activity) {
                if (in_array($activity['actividad_plandetrabajo'], $existingSet)) {
                    $skipped++;
                    continue;
                }
                $planModel->insert($activity);
                $inserted++;
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Plan regenerado: {$inserted} actividades insertadas, {$skipped} omitidas (ya existen en {$currentYear}).",
                'inserted' => $inserted,
                'skipped' => $skipped
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Busca actividades del inventario CSV por texto parcial
     */
    public function searchActivities()
    {
        $query = $this->request->getPost('query');
        if (empty($query) || strlen($query) < 3) {
            return $this->response->setJSON(['success' => false, 'results' => [], 'message' => 'Ingrese al menos 3 caracteres']);
        }

        try {
            $csvPath = ROOTPATH . 'PTA2026.csv';
            if (!file_exists($csvPath)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Archivo CSV no encontrado']);
            }

            $results = [];
            $handle = fopen($csvPath, 'r');
            $header = fgetcsv($handle, 1000, ';');

            while (($row = fgetcsv($handle, 1000, ';')) !== false) {
                if (count($row) < 11) continue;
                $actividad = trim($row[2] ?? '');
                if (empty($actividad)) continue;

                if (stripos($actividad, $query) !== false) {
                    $results[] = [
                        'phva' => trim($row[0] ?? ''),
                        'numeral' => trim($row[1] ?? ''),
                        'actividad' => $actividad,
                        'responsable' => trim($row[10] ?? 'CONSULTOR CYCLOID'),
                    ];
                }
            }
            fclose($handle);

            return $this->response->setJSON(['success' => true, 'results' => $results]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Genera opciones de actividad con IA (OpenAI API)
     */
    public function generateAiActivity()
    {
        $description = $this->request->getPost('description');
        $context = $this->request->getPost('context') ?? '';

        if (empty($description)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Descripción requerida']);
        }

        try {
            $apiKey = getenv('OPENAI_API_KEY');
            if (empty($apiKey)) {
                return $this->response->setJSON(['success' => false, 'message' => 'API key de OpenAI no configurada']);
            }

            $systemPrompt = "Eres un consultor externo experto en Seguridad y Salud en el Trabajo (SST) bajo la normativa colombiana (Decreto 1072 de 2015, Resolución 0312 de 2019). "
                . "Tu especialidad es asesorar PROPIEDADES HORIZONTALES (conjuntos residenciales y edificios) en Colombia. "
                . "Contexto clave: las copropiedades NO tienen empleados directos — su objeto social es sin ánimo de lucro. "
                . "Los trabajadores son de empresas contratistas: aseadoras, vigilantes y toderos. "
                . "El SG-SST se orienta a VERIFICAR que estos proveedores cumplan con sus obligaciones. "
                . "Tu tarea es proponer actividades REALISTAS para el Plan de Trabajo Anual del SG-SST. "
                . "Responde SOLO con un JSON array de exactamente 3 opciones. Cada opción: phva (PLANEAR, HACER, VERIFICAR o ACTUAR), numeral (del estándar mínimo Resolución 0312), actividad (descripción profesional concisa). "
                . "Ejemplo: [{\"phva\":\"VERIFICAR\",\"numeral\":\"4.1.1\",\"actividad\":\"Verificar afiliación a ARL y EPS del personal de aseo contratado\"}]";

            $userMessage = "Necesito actividades de SST sobre: " . $description;
            if (!empty($context)) {
                $userMessage .= "\n\nContexto adicional: " . $context;
            }

            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey,
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => 'gpt-4o-mini',
                    'max_tokens' => 1024,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                ]),
                CURLOPT_TIMEOUT => 30,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                log_message('error', "OpenAI API error {$httpCode}: {$response}");
                return $this->response->setJSON(['success' => false, 'message' => 'Error al consultar la IA. Código: ' . $httpCode]);
            }

            $data = json_decode($response, true);
            $text = $data['choices'][0]['message']['content'] ?? '';

            preg_match('/\[.*\]/s', $text, $matches);
            $options = json_decode($matches[0] ?? '[]', true);

            if (empty($options)) {
                return $this->response->setJSON(['success' => false, 'message' => 'La IA no generó opciones válidas. Intente reformular.']);
            }

            return $this->response->setJSON(['success' => true, 'options' => $options]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Inserta una actividad creada con IA o del inventario
     */
    public function insertAiActivity()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $phva = $this->request->getPost('phva');
        $numeral = $this->request->getPost('numeral');
        $actividad = $this->request->getPost('actividad');

        if (empty($idCliente) || empty($actividad)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente y actividad son requeridos']);
        }

        try {
            $planModel = new PlanModel();
            $data = [
                'id_cliente' => $idCliente,
                'phva_plandetrabajo' => $phva ?? '',
                'numeral_plandetrabajo' => $numeral ?? '',
                'actividad_plandetrabajo' => $actividad,
                'responsable_sugerido_plandetrabajo' => 'CONSULTOR CYCLOID',
                'observaciones' => '',
                'fecha_propuesta' => date('Y-m-d'),
                'estado_actividad' => 'ABIERTA',
                'porcentaje_avance' => 0,
            ];

            if ($planModel->insert($data)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Actividad insertada correctamente',
                    'id' => $planModel->getInsertID()
                ]);
            }

            return $this->response->setJSON(['success' => false, 'message' => 'Error al insertar la actividad']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
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

        $datosAnteriores = $ptaModel->find($id);

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
                    $fechaCierreActual = $postData['fecha_cierre'] ?? $datosAnteriores['fecha_cierre'] ?? '';
                    if (empty($fechaCierreActual)) {
                        $postData['fecha_cierre'] = $datosAnteriores['fecha_propuesta'] ?? date('Y-m-d');
                    }
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

        // Auditoría
        try {
            PtaAuditService::logMultiple(
                $id,
                $datosAnteriores,
                $postData,
                __METHOD__,
                $datosAnteriores['id_cliente'] ?? null
            );
        } catch (\Exception $e) {
            log_message('error', 'Audit failed in editinginline: ' . $e->getMessage());
        }

        // Transición si el estado cambió desde ABIERTA
        if (isset($postData['estado_actividad']) && ($datosAnteriores['estado_actividad'] ?? '') !== $postData['estado_actividad']) {
            PtaTransicionesService::registrar(
                (int) $id,
                (int) ($datosAnteriores['id_cliente'] ?? 0),
                $datosAnteriores['estado_actividad'] ?? '',
                $postData['estado_actividad']
            );
        }

        $response = [
            'status'  => 'success',
            'message' => 'Registro actualizado inline correctamente.'
        ];

        if (isset($postData['porcentaje_avance'])) {
            $response['porcentaje_avance'] = $postData['porcentaje_avance'];
        }

        if (isset($postData['fecha_cierre'])) {
            $response['fecha_cierre'] = $postData['fecha_cierre'];
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

    /**
     * Completa campos de una actividad PTA usando IA.
     * Recibe POST AJAX: id_cliente + descripcion
     */
    public function completarConIA()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no válida']);
        }

        $idCliente = (int) $this->request->getPost('id_cliente');
        $descripcion = trim($this->request->getPost('descripcion') ?? '');

        if (!$idCliente || !$descripcion) {
            return $this->response->setJSON(['success' => false, 'error' => 'Cliente y descripción son requeridos']);
        }

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'error' => 'Cliente no encontrado']);
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente) ?? [];

        $iaService = new PtaIAService();
        $resultado = $iaService->completarActividad($descripcion, $cliente, $contexto);

        if (!$resultado['success']) {
            return $this->response->setJSON(['success' => false, 'error' => $resultado['error'] ?? 'Error al consultar IA']);
        }

        $campos = $resultado['campos'];

        // Calcular fecha_propuesta como último día del mes sugerido
        $mes = (int) ($campos['mes_sugerido'] ?? date('n'));
        $anio = date('Y');
        $fecha = new \DateTime("{$anio}-{$mes}-01");
        $fecha->modify('last day of this month');

        return $this->response->setJSON([
            'success' => true,
            'campos' => [
                'phva_plandetrabajo' => $campos['phva'] ?? '',
                'numeral_plandetrabajo' => $campos['numeral'] ?? '',
                'actividad_plandetrabajo' => $campos['actividad'] ?? '',
                'responsable_sugerido_plandetrabajo' => $campos['responsable_sugerido'] ?? '',
                'fecha_propuesta' => $fecha->format('Y-m-d'),
            ]
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
            } elseif (!empty($filters['anio']) && $filters['anio'] !== 'todos') {
                $ptaModel->where('YEAR(fecha_propuesta)', (int) $filters['anio']);
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
