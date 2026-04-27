<?php

namespace App\Controllers;

use App\Models\CronogcapacitacionModel;
use App\Models\ClientModel;
use App\Models\CapacitacionModel;
use App\Models\ResponsableSSTModel;
use CodeIgniter\Controller;

class CronogcapacitacionController extends Controller
{

    public function listcronogCapacitacionAjax()
    {
        return view('consultant/list_cronogramas_ajax');
    }

    // API: Retorna la lista de clientes en formato JSON (igual que en otros módulos)
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

    // API: Retorna la lista de cronogramas filtrada por el parámetro 'cliente'
    public function getCronogramasAjax()
    {
        $clienteID = $this->request->getGet('cliente');
        $anio = $this->request->getGet('anio');
        $cronogModel = new CronogcapacitacionModel();
        $clientModel = new ClientModel();
        $capacitacionModel = new CapacitacionModel();

        if (empty($clienteID)) {
            return $this->response->setJSON([]);
        }

        if ($anio === null || $anio === '') {
            $anio = date('Y');
        }

        $cronogModel->where('id_cliente', $clienteID);

        if ($anio !== 'todos') {
            $cronogModel->where('YEAR(fecha_programada)', (int) $anio);
        }

        $cronogramas = $cronogModel
            ->orderBy('fecha_programada', 'ASC')
            ->findAll();

        // Enriquecer cada registro con datos del cliente y capacitación
        foreach ($cronogramas as &$cronograma) {
            $cliente = $clientModel->find($cronograma['id_cliente']);
            $cronograma['nombre_cliente'] = $cliente['nombre_cliente'] ?? 'Cliente no encontrado';

            $capacitacion = $capacitacionModel->find($cronograma['id_capacitacion']);
            $cronograma['nombre_capacitacion'] = $capacitacion['capacitacion'] ?? 'Capacitación no encontrada';
            $cronograma['objetivo_capacitacion'] = $capacitacion['objetivo_capacitacion'] ?? 'Objetivo no disponible';

            // Generar botones de acciones
            $cronograma['acciones'] = '<div class="action-group">'
                . '<a href="' . base_url('/editcronogCapacitacion/' . $cronograma['id_cronograma_capacitacion']) . '" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-pen"></i></a>'
                . '<button type="button" class="btn-action btn-action-delete btn-delete-single" data-id="' . $cronograma['id_cronograma_capacitacion'] . '" title="Eliminar"><i class="fas fa-trash"></i></button>'
                . '</div>';
        }

        return $this->response->setJSON($cronogramas);
    }

    // API: Actualiza campos específicos del cronograma de capacitación (para edición inline)
    public function updatecronogCapacitacion()
    {
        log_message('debug', 'Datos recibidos: ' . print_r($this->request->getPost(), true));
        $id = $this->request->getPost('id');
        $field = $this->request->getPost('field');
        $value = $this->request->getPost('value');

        $allowedFields = [
            'fecha_programada',
            'fecha_de_realizacion',
            'estado',
            'perfil_de_asistentes',
            'modalidad',
            'nombre_del_capacitador',
            'horas_de_duracion_de_la_capacitacion',
            'indicador_de_realizacion_de_la_capacitacion',
            'numero_de_asistentes_a_capacitacion',
            'numero_total_de_personas_programadas',
            'porcentaje_cobertura',
            'numero_de_personas_evaluadas',
            'promedio_de_calificaciones',
            'observaciones'
        ];

        if ($field === 'modalidad') {
            $value = $this->normalizarModalidad($value);
        }

        if (!in_array($field, $allowedFields)) {
            log_message('error', 'Campo no permitido: ' . $field);
            return $this->response->setJSON(['success' => false, 'message' => 'Campo no permitido']);
        }

        $cronogModel = new CronogcapacitacionModel();

        // Si se actualiza alguno de los campos que afectan el porcentaje, recalcúlalo
        if (in_array($field, ['numero_de_asistentes_a_capacitacion', 'numero_total_de_personas_programadas'])) {
            // Obtén el registro actual para el otro valor
            $registro = $cronogModel->find($id);
            if ($field == 'numero_de_asistentes_a_capacitacion') {
                $numero_asistentes = $value;
                $numero_total_programados = $registro['numero_total_de_personas_programadas'];
            } else {
                $numero_asistentes = $registro['numero_de_asistentes_a_capacitacion'];
                $numero_total_programados = $value;
            }
            $porcentaje_cobertura = ($numero_total_programados > 0)
                ? number_format(($numero_asistentes / $numero_total_programados) * 100, 2)
                : 0;

            // Actualiza el campo modificado y el porcentaje en conjunto
            $updateData = [
                $field => $value,
                'porcentaje_cobertura' => $porcentaje_cobertura . '%'
            ];
        } else {
            $updateData = [$field => $value];
        }

    if ($cronogModel->update($id, $updateData)) {
        log_message('debug', 'Registro actualizado correctamente');
        return $this->response->setJSON([
            'success' => true, 
            'message' => 'Registro actualizado correctamente',
            'newValue' => isset($porcentaje_cobertura) ? $porcentaje_cobertura . '%' : $value
        ]);

            log_message('debug', 'Registro actualizado correctamente');
            return $this->response->setJSON(['success' => true, 'message' => 'Registro actualizado correctamente']);
        } else {
            log_message('error', 'Error al actualizar el registro');
            return $this->response->setJSON(['success' => false, 'message' => 'No se pudo actualizar el registro']);
        }
    }

    // Listar todos los cronogramas de capacitación
    public function listcronogCapacitacion()
    {
        $cronogModel = new CronogcapacitacionModel();
        $clientModel = new ClientModel();
        $capacitacionModel = new CapacitacionModel();

        // Obtenemos todos los cronogramas
        $cronogramas = $cronogModel->findAll();

        // Iteramos los cronogramas para obtener los datos relacionados (nombre del cliente y capacitación)
        foreach ($cronogramas as &$cronograma) {
            // Obtenemos el nombre del cliente
            $cliente = $clientModel->find($cronograma['id_cliente']);
            $cronograma['nombre_cliente'] = $cliente['nombre_cliente'] ?? 'Cliente no encontrado';

            // Obtenemos el nombre de la capacitación y el objetivo
            $capacitacion = $capacitacionModel->find($cronograma['id_capacitacion']);
            $cronograma['nombre_capacitacion'] = $capacitacion['capacitacion'] ?? 'Capacitación no encontrada';
            $cronograma['objetivo_capacitacion'] = $capacitacion['objetivo_capacitacion'] ?? 'Objetivo no disponible';
        }

        // Pasamos los datos a la vista
        $data['cronogramas'] = $cronogramas;
        return view('consultant/list_cronogramas', $data);
    }

    // Mostrar formulario para agregar nuevo cronograma de capacitación
    public function addcronogCapacitacion()
    {
        $capacitacionModel = new CapacitacionModel();
        $clienteModel = new ClientModel();

        // Obtener capacitaciones y clientes
        $capacitaciones = $capacitacionModel->findAll();
        $clientes = $clienteModel->findAll();

        // Preparar los datos para la vista
        $data = [
            'capacitaciones' => $capacitaciones,
            'clientes' => $clientes,
        ];

        return view('consultant/add_cronograma', $data);
    }

    // Guardar nuevo cronograma de capacitación
    public function addcronogCapacitacionPost()
    {
        $cronogModel = new CronogcapacitacionModel();

        // Depuración: Mostrar los valores recibidos
        log_message('debug', 'Datos POST recibidos: ' . print_r($this->request->getPost(), true));

        // Capturar el valor de id_capacitacion
        $id_capacitacion = $this->request->getPost('id_capacitacion');

        // Si `id_capacitacion` está vacío, detener el proceso
        if (empty($id_capacitacion)) {
            return redirect()->back()->with('msg', 'Error: No seleccionaste una capacitación.');
        }

        // Preparar los datos para la inserción
        $data = [
            'id_capacitacion' => $id_capacitacion,
            'id_cliente' => $this->request->getPost('id_cliente'),
            'fecha_programada' => $this->request->getPost('fecha_programada'),
            'fecha_de_realizacion' => $this->request->getPost('fecha_de_realizacion'),
            'estado' => $this->request->getPost('estado'),
            'perfil_de_asistentes' => $this->request->getPost('perfil_de_asistentes'),
            'modalidad' => $this->normalizarModalidad($this->request->getPost('modalidad')),
            'nombre_del_capacitador' => $this->request->getPost('nombre_del_capacitador'),
            'horas_de_duracion_de_la_capacitacion' => $this->request->getPost('horas_de_duracion_de_la_capacitacion'),
            'indicador_de_realizacion_de_la_capacitacion' => $this->request->getPost('indicador_de_realizacion_de_la_capacitacion'),
            'numero_de_asistentes_a_capacitacion' => $this->request->getPost('numero_de_asistentes_a_capacitacion'),
            'numero_total_de_personas_programadas' => $this->request->getPost('numero_total_de_personas_programadas'),
            'porcentaje_cobertura' => $this->request->getPost('porcentaje_cobertura'),
            'numero_de_personas_evaluadas' => $this->request->getPost('numero_de_personas_evaluadas'),
            'promedio_de_calificaciones' => $this->request->getPost('promedio_de_calificaciones'),
            'observaciones' => $this->request->getPost('observaciones'),
        ];

        // Intentar insertar el nuevo cronograma
        if ($cronogModel->insert($data)) {
            return redirect()->to('/listcronogCapacitacion')->with('msg', 'Cronograma agregado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar cronograma.');
        }
    }

    // Mostrar formulario para editar cronograma de capacitación
    public function editcronogCapacitacion($id)
    {
        $cronogModel = new CronogcapacitacionModel();
        $clientModel = new ClientModel();
        $capacitacionModel = new CapacitacionModel();

        // Obtener el cronograma que se va a editar
        $cronograma = $cronogModel->find($id);
        if (!$cronograma) {
            return redirect()->to('/listcronogCapacitacion')->with('msg', 'Cronograma no encontrado.');
        }

        // Obtener listas de clientes y capacitaciones para los selects del formulario
        $clientes = $clientModel->findAll();
        $capacitaciones = $capacitacionModel->findAll();

        // Preparar los datos para la vista
        $data = [
            'cronograma' => $cronograma,
            'clientes' => $clientes,
            'capacitaciones' => $capacitaciones,
        ];

        return view('consultant/edit_cronograma', $data);
    }

    // Actualizar cronograma de capacitación
    public function editcronogCapacitacionPost($id)
    {
        $cronogModel = new CronogcapacitacionModel();

        $numero_asistentes = $this->request->getPost('numero_de_asistentes_a_capacitacion');
        $numero_total_programados = $this->request->getPost('numero_total_de_personas_programadas');

        // Calcular el porcentaje de cobertura
        $porcentaje_cobertura = ($numero_total_programados > 0)
            ? number_format(($numero_asistentes / $numero_total_programados) * 100, 2)
            : 0;

        $data = [
            'id_capacitacion' => $this->request->getPost('id_capacitacion'),
            'id_cliente' => $this->request->getPost('id_cliente'),
            'fecha_programada' => $this->request->getPost('fecha_programada'),
            'fecha_de_realizacion' => $this->request->getPost('fecha_de_realizacion'),
            'estado' => $this->request->getPost('estado'),
            'perfil_de_asistentes' => $this->request->getPost('perfil_de_asistentes'),
            'modalidad' => $this->normalizarModalidad($this->request->getPost('modalidad')),
            'nombre_del_capacitador' => $this->request->getPost('nombre_del_capacitador'),
            'horas_de_duracion_de_la_capacitacion' => $this->request->getPost('horas_de_duracion_de_la_capacitacion'),
            'indicador_de_realizacion_de_la_capacitacion' => $this->request->getPost('indicador_de_realizacion_de_la_capacitacion'),
            'numero_de_asistentes_a_capacitacion' => $numero_asistentes,
            'numero_total_de_personas_programadas' => $numero_total_programados,
            'porcentaje_cobertura' => $porcentaje_cobertura . '%', // Agregar el símbolo de porcentaje
            'numero_de_personas_evaluadas' => $this->request->getPost('numero_de_personas_evaluadas'),
            'promedio_de_calificaciones' => $this->request->getPost('promedio_de_calificaciones'),
            'observaciones' => $this->request->getPost('observaciones'),
        ];

        if ($cronogModel->update($id, $data)) {
            return redirect()->to('/listcronogCapacitacion')->with('msg', 'Cronograma actualizado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar cronograma');
        }
    }

    // Eliminar cronograma de capacitación
    public function deletecronogCapacitacion($id)
    {
        $cronogModel = new CronogcapacitacionModel();

        if ($cronogModel->delete($id)) {
            return redirect()->to('/listcronogCapacitacion')->with('msg', 'Cronograma eliminado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al eliminar el cronograma');
        }
    }

    public function deletecronogCapacitacionAjax($id)
    {
        $cronogModel = new CronogcapacitacionModel();

        if ($cronogModel->delete($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Cronograma eliminado exitosamente']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al eliminar el cronograma']);
        }
    }

    public function deleteMultiplecronogCapacitacion()
    {
        $ids = $this->request->getPost('ids');

        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se recibieron IDs para eliminar']);
        }

        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return $this->response->setJSON(['success' => false, 'message' => 'IDs inválidos']);
        }

        $cronogModel = new CronogcapacitacionModel();
        $cronogModel->whereIn('id_cronograma_capacitacion', $ids)->delete();
        $deleted = $cronogModel->db->affectedRows();

        return $this->response->setJSON([
            'success'  => true,
            'deleted'  => $deleted,
            'message'  => $deleted . ' registro(s) eliminado(s) exitosamente'
        ]);
    }

    /**
     * Actualiza la fecha programada según el mes seleccionado
     * Mantiene el año de la fecha existente o usa el año actual
     */
    public function updateDateByMonth()
    {
        $id = $this->request->getPost('id');
        $month = (int) $this->request->getPost('month');

        if (!$id || $month < 1 || $month > 12) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parámetros inválidos'
            ]);
        }

        $cronogModel = new CronogcapacitacionModel();
        $training = $cronogModel->find($id);

        if (!$training) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cronograma no encontrado'
            ]);
        }

        // Obtener el año de la fecha existente o el actual
        $year = date('Y');
        if (!empty($training['fecha_programada'])) {
            $existingDate = new \DateTime($training['fecha_programada']);
            $year = $existingDate->format('Y');
        }

        // Calcular último día del mes (maneja años bisiestos)
        $lastDayDate = new \DateTime("$year-$month-01");
        $lastDayDate->modify('last day of this month');
        $lastDay = (int) $lastDayDate->format('d');

        // Crear la nueva fecha con el último día del mes
        $newDate = sprintf('%04d-%02d-%02d', $year, $month, $lastDay);

        // Actualizar en BD
        if ($cronogModel->update($id, ['fecha_programada' => $newDate])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Fecha actualizada correctamente',
                'newDate' => $newDate
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al actualizar la fecha'
            ]);
        }
    }

    /**
     * Lista de clientes para el modal de generación automática
     */
    public function getClients()
    {
        $clientModel = new ClientModel();
        $clientes = $clientModel->orderBy('nombre_cliente', 'ASC')->findAll();

        $data = [];
        foreach ($clientes as $cliente) {
            $data[] = [
                'id' => $cliente['id_cliente'],
                'nombre' => $cliente['nombre_cliente']
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'clients' => $data
        ]);
    }

    /**
     * Obtiene el contrato del cliente para determinar el tipo de servicio
     */
    public function getClientContract()
    {
        $idCliente = $this->request->getGet('id_cliente');

        if (empty($idCliente)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de cliente requerido'
            ]);
        }

        $contractModel = new \App\Models\ContractModel();

        // Priorizar contrato activo
        $contract = $contractModel->where('id_cliente', $idCliente)
                                  ->where('estado', 'activo')
                                  ->orderBy('fecha_fin', 'DESC')
                                  ->first();

        // Si no hay activo, buscar el más reciente
        if (!$contract) {
            $contract = $contractModel->where('id_cliente', $idCliente)
                                      ->orderBy('created_at', 'DESC')
                                      ->first();
        }

        if ($contract) {
            return $this->response->setJSON([
                'success' => true,
                'contract' => [
                    'id_contrato' => $contract['id_contrato'],
                    'numero_contrato' => $contract['numero_contrato'] ?? 'Sin número',
                    'fecha_inicio' => $contract['fecha_inicio'],
                    'fecha_fin' => $contract['fecha_fin'],
                    'estado' => $contract['estado'],
                    'frecuencia_visitas' => $contract['frecuencia_visitas'] ?? 'mensual'
                ]
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se encontró contrato para este cliente'
            ]);
        }
    }

    /**
     * Genera cronograma automático basado en el tipo de servicio
     */
    public function generate()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $serviceType = strtolower($this->request->getPost('service_type') ?? 'mensual');

        if (empty($idCliente)) {
            return redirect()->back()->with('msg', 'Error: Debe seleccionar un cliente.');
        }

        try {
            // Instanciar librería
            $trainingLibrary = new \App\Libraries\TrainingLibrary();

            // Obtener capacitaciones filtradas por tipo de servicio
            $trainings = $trainingLibrary->getTrainings((int)$idCliente, $serviceType);

            if (empty($trainings)) {
                return redirect()->to('/listcronogCapacitacion')->with('msg', 'No se encontraron capacitaciones para el tipo de servicio: ' . $serviceType);
            }

            // Obtener el modelo de capacitaciones para buscar IDs
            $capacitacionModel = new CapacitacionModel();
            $cronogModel = new CronogcapacitacionModel();

            $insertedCount = 0;
            $errors = [];

            // Insertar cada capacitación en la BD
            foreach ($trainings as $training) {
                // Buscar el id_capacitacion por nombre
                $capacitacion = $capacitacionModel->like('capacitacion', $training['nombre_capacitacion'], 'both')
                                                   ->first();

                if (!$capacitacion) {
                    // Si no existe, crear la capacitación
                    $capacitacionId = $capacitacionModel->insert([
                        'capacitacion' => $training['nombre_capacitacion'],
                        'objetivo_capacitacion' => $training['objetivo_capacitacion'] ?? 'Objetivo por definir',
                        'observaciones' => ''
                    ]);

                    if (!$capacitacionId) {
                        $errors[] = "No se pudo crear la capacitación: " . $training['nombre_capacitacion'];
                        continue;
                    }
                } else {
                    $capacitacionId = $capacitacion['id_capacitacion'];
                }

                // Preparar datos para insertar en cronograma
                $cronogramaData = [
                    'id_capacitacion' => $capacitacionId,
                    'id_cliente' => $idCliente,
                    'fecha_programada' => $training['fecha_programada'] ?? date('Y-m-d'),
                    'fecha_de_realizacion' => null,
                    'estado' => $training['estado'] ?? 'PROGRAMADA',
                    'perfil_de_asistentes' => $training['perfil_de_asistentes'] ?? 'TODOS',
                    'nombre_del_capacitador' => $training['nombre_del_capacitador'] ?? 'ASESOR SST',
                    'horas_de_duracion_de_la_capacitacion' => $training['horas_de_duracion_de_la_capacitacion'] ?? 1,
                    'indicador_de_realizacion_de_la_capacitacion' => 'SIN CALIFICAR',
                    'numero_de_asistentes_a_capacitacion' => 0,
                    'numero_total_de_personas_programadas' => 0,
                    'porcentaje_cobertura' => '0%',
                    'numero_de_personas_evaluadas' => 0,
                    'promedio_de_calificaciones' => 0,
                    'observaciones' => ''
                ];

                if ($cronogModel->insert($cronogramaData)) {
                    $insertedCount++;
                } else {
                    $errors[] = "Error al insertar: " . $training['nombre_capacitacion'];
                }
            }

            $message = "Se generaron {$insertedCount} cronogramas de capacitación para el cliente.";
            if (!empty($errors)) {
                $message .= " Errores: " . implode(', ', array_slice($errors, 0, 3));
            }

            return redirect()->to('/listcronogCapacitacion')->with('msg', $message);

        } catch (\Exception $e) {
            log_message('error', 'Error al generar cronograma: ' . $e->getMessage());
            return redirect()->back()->with('msg', 'Error al generar cronograma: ' . $e->getMessage());
        }
    }

    /**
     * Envía email de socialización del cronograma de capacitación
     * Envía automáticamente al cliente y al consultor asignado
     */
    public function socializarEmail()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $selectedYear = $this->request->getPost('year'); // Año seleccionado desde el frontend

        if (empty($idCliente)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de cliente requerido'
            ]);
        }

        try {
            // Obtener datos del cliente
            $clientModel = new ClientModel();
            $cliente = $clientModel->find($idCliente);

            if (!$cliente) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ]);
            }

            // Obtener destinatarios desde tbl_cliente_responsables_sst
            $responsableModel = new ResponsableSSTModel();
            $repLegal = $responsableModel->getRepresentanteLegal($idCliente);
            $delegadoSST = $responsableModel->getResponsableSGSST($idCliente);

            // Validar que al menos uno tenga email
            $tieneEmailRepLegal = $repLegal && !empty($repLegal['email']) && filter_var($repLegal['email'], FILTER_VALIDATE_EMAIL);
            $tieneEmailDelegado = $delegadoSST && !empty($delegadoSST['email']) && filter_var($delegadoSST['email'], FILTER_VALIDATE_EMAIL);

            if (!$tieneEmailRepLegal && !$tieneEmailDelegado) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se encontraron emails válidos en Responsables SST. Registre el email del Representante Legal o del Responsable SG-SST en el módulo Responsables SST.'
                ]);
            }

            // Obtener cronogramas del cliente del año seleccionado (o actual si no se especifica)
            $cronogModel = new CronogcapacitacionModel();
            $capacitacionModel = new CapacitacionModel();

            // Usar año seleccionado o año actual por defecto
            $targetYear = !empty($selectedYear) && is_numeric($selectedYear) ? (int)$selectedYear : (int)date('Y');
            $startOfYear = $targetYear . '-01-01';
            $endOfYear = $targetYear . '-12-31';

            $cronogramas = $cronogModel->where('id_cliente', $idCliente)
                                       ->where('fecha_programada >=', $startOfYear)
                                       ->where('fecha_programada <=', $endOfYear)
                                       ->orderBy('fecha_programada', 'ASC')
                                       ->findAll();

            if (empty($cronogramas)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No hay cronogramas de capacitación para este cliente en el año ' . $targetYear
                ]);
            }

            // Enriquecer cronogramas con nombre de capacitación
            foreach ($cronogramas as &$cron) {
                $cap = $capacitacionModel->find($cron['id_capacitacion']);
                $cron['nombre_capacitacion'] = $cap['capacitacion'] ?? 'Sin nombre';
            }

            // Preparar destinatarios
            $destinatarios = [];
            $emailsEnviados = [];

            if ($tieneEmailRepLegal) {
                $destinatarios[] = $repLegal['email'];
                $emailsEnviados[] = $repLegal['email'] . ' (Representante Legal: ' . ($repLegal['nombre_completo'] ?? '') . ')';
            }

            if ($tieneEmailDelegado) {
                if (!in_array($delegadoSST['email'], $destinatarios)) {
                    $destinatarios[] = $delegadoSST['email'];
                    $emailsEnviados[] = $delegadoSST['email'] . ' (Responsable SG-SST: ' . ($delegadoSST['nombre_completo'] ?? '') . ')';
                }
            }

            // Generar contenido HTML del email
            $htmlContent = $this->generarHtmlCronograma($cliente, $cronogramas, '', $targetYear);

            // Preparar email con SendGrid
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "EnterpriseSST");
            $email->setSubject('Cronograma de Capacitación SST ' . $targetYear . ' - ' . $cliente['nombre_cliente']);

            // Agregar destinatarios
            foreach ($destinatarios as $destinatario) {
                $email->addTo($destinatario);
            }

            $email->addContent("text/html", $htmlContent);

            // Enviar con SendGrid
            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', "Email de socialización enviado a " . implode(', ', $destinatarios) . " para cliente ID: {$idCliente}");

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Email enviado correctamente con ' . count($cronogramas) . ' capacitaciones del año ' . $targetYear,
                    'emailsEnviados' => $emailsEnviados,
                    'totalCapacitaciones' => count($cronogramas),
                    'cliente' => $cliente['nombre_cliente'],
                    'year' => $targetYear
                ]);
            } else {
                log_message('error', 'Error al enviar email de socialización. Status: ' . $response->statusCode() . ' Body: ' . $response->body());

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al enviar el email. Código: ' . $response->statusCode()
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error en socializarEmail: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Genera el HTML del cronograma para el email
     */
    private function generarHtmlCronograma($cliente, $cronogramas, $mensajeAdicional = '', $year = null)
    {
        $year = $year ?? date('Y');

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: linear-gradient(135deg, #1a5276, #2e86ab); color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th { background-color: #1a5276; color: white; padding: 10px; text-align: left; }
                td { padding: 8px; border-bottom: 1px solid #ddd; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .status-programada { color: #0d6efd; font-weight: bold; }
                .status-ejecutada { color: #198754; font-weight: bold; }
                .status-cancelada { color: #dc3545; font-weight: bold; }
                .footer { background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; }
                .mensaje { background-color: #e7f3ff; padding: 15px; border-left: 4px solid #0d6efd; margin: 15px 0; }
                .year-badge { background-color: #f0ad4e; color: #333; padding: 5px 15px; border-radius: 20px; font-weight: bold; display: inline-block; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Cronograma de Capacitación SST</h1>
                <h2>' . esc($cliente['nombre_cliente']) . '</h2>
                <span class="year-badge">Año ' . $year . '</span>
            </div>
            <div class="content">';

        if (!empty($mensajeAdicional)) {
            $html .= '<div class="mensaje"><strong>Mensaje:</strong><br>' . nl2br(esc($mensajeAdicional)) . '</div>';
        }

        $html .= '
                <p>A continuación se presenta el cronograma de capacitaciones programadas para el año <strong>' . $year . '</strong>:</p>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Capacitación</th>
                            <th>Fecha Programada</th>
                            <th>Estado</th>
                            <th>Perfil Asistentes</th>
                            <th>Capacitador</th>
                        </tr>
                    </thead>
                    <tbody>';

        $i = 1;
        foreach ($cronogramas as $cron) {
            $statusClass = 'status-programada';
            if (strtoupper($cron['estado']) === 'EJECUTADA') {
                $statusClass = 'status-ejecutada';
            } elseif (strpos(strtoupper($cron['estado']), 'CANCELADA') !== false) {
                $statusClass = 'status-cancelada';
            }

            $html .= '
                        <tr>
                            <td>' . $i++ . '</td>
                            <td>' . esc($cron['nombre_capacitacion']) . '</td>
                            <td>' . esc($cron['fecha_programada'] ?? '-') . '</td>
                            <td class="' . $statusClass . '">' . esc($cron['estado'] ?? '-') . '</td>
                            <td>' . esc($cron['perfil_de_asistentes'] ?? '-') . '</td>
                            <td>' . esc($cron['nombre_del_capacitador'] ?? '-') . '</td>
                        </tr>';
        }

        $html .= '
                    </tbody>
                </table>
                <p><strong>Total de capacitaciones:</strong> ' . count($cronogramas) . '</p>
            </div>
            <div class="footer">
                <p><strong>EnterpriseSST</strong> - Sistema de Gestión SST</p>
                <p>Este es un correo automático generado por el sistema. Por favor no responda a este mensaje.</p>
                <p>© ' . date('Y') . ' EnterpriseSST - NIT: 901.653.912</p>
            </div>
        </body>
        </html>';

        return $html;
    }

    /**
     * Genera una capacitación con IA y la agrega al cronograma
     */
    public function generarConIA()
    {
        $tema = trim($this->request->getPost('tema') ?? '');
        $idCliente = $this->request->getPost('id_cliente');
        $fechaProgramada = $this->request->getPost('fecha_programada');
        $modalidad = $this->normalizarModalidad($this->request->getPost('modalidad'));

        if (empty($tema) || empty($idCliente) || empty($fechaProgramada)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Todos los campos son obligatorios: tema, cliente y fecha'
            ]);
        }

        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'API Key de OpenAI no configurada'
            ]);
        }

        try {
            $resultado = $this->llamarOpenAICapacitacion($tema, $apiKey, $modalidad);

            if (!$resultado['success']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $resultado['error'] ?? 'Error al llamar a la IA'
                ]);
            }

            $datos = json_decode($resultado['contenido'], true);
            if (!$datos || empty($datos['capacitacion'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'La IA no generó datos válidos. Intenta con otro tema.'
                ]);
            }

            $capacitacionModel = new CapacitacionModel();
            $cronogModel = new CronogcapacitacionModel();

            // Buscar si ya existe la capacitación
            $existente = $capacitacionModel->like('capacitacion', $datos['capacitacion'], 'both')->first();

            if ($existente) {
                $capacitacionId = $existente['id_capacitacion'];
            } else {
                $capacitacionId = $capacitacionModel->insert([
                    'capacitacion' => $datos['capacitacion'],
                    'objetivo_capacitacion' => $datos['objetivo_capacitacion'] ?? 'Objetivo por definir',
                    'observaciones' => 'Generada con IA'
                ]);

                if (!$capacitacionId) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Error al crear la capacitación en BD'
                    ]);
                }
            }

            // Crear cronograma
            $cronogramaData = [
                'id_capacitacion' => $capacitacionId,
                'id_cliente' => $idCliente,
                'fecha_programada' => $fechaProgramada,
                'fecha_de_realizacion' => null,
                'estado' => 'PROGRAMADA',
                'perfil_de_asistentes' => $datos['perfil_de_asistentes'] ?? 'TODOS',
                'modalidad' => $modalidad,
                'nombre_del_capacitador' => 'ASESOR SST',
                'horas_de_duracion_de_la_capacitacion' => $datos['horas_sugeridas'] ?? 1,
                'indicador_de_realizacion_de_la_capacitacion' => 'SIN CALIFICAR',
                'numero_de_asistentes_a_capacitacion' => 0,
                'numero_total_de_personas_programadas' => 0,
                'porcentaje_cobertura' => '0%',
                'numero_de_personas_evaluadas' => 0,
                'promedio_de_calificaciones' => 0,
                'observaciones' => ''
            ];

            if ($cronogModel->insert($cronogramaData)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Capacitación creada exitosamente con IA',
                    'data' => [
                        'capacitacion' => $datos['capacitacion'],
                        'objetivo' => $datos['objetivo_capacitacion'] ?? '',
                        'perfil' => $datos['perfil_de_asistentes'] ?? 'TODOS',
                        'modalidad' => $modalidad,
                        'horas' => $datos['horas_sugeridas'] ?? 1,
                        'nueva' => !$existente
                    ]
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al crear el cronograma en BD'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error en generarConIA: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Llama a OpenAI para generar datos de capacitación SST
     */
    private function llamarOpenAICapacitacion(string $tema, string $apiKey, string $modalidad = 'PRESENCIAL'): array
    {
        $systemPrompt = 'Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia. Genera una capacitación SST basada en el tema y la modalidad proporcionados. Responde SOLO en formato JSON válido sin markdown ni bloques de código.';

        $hintModalidad = match ($modalidad) {
            'VIRTUAL'    => 'Modalidad: VIRTUAL (sesion en linea sincronica). Sugiere horas mas cortas (1-2h) y enfasis en componente teorico/audiovisual.',
            'MIXTA'      => 'Modalidad: MIXTA (parte virtual + parte presencial). Sugiere horas intermedias (2-3h) combinando teoria virtual con practica presencial.',
            default      => 'Modalidad: PRESENCIAL (presencial en sitio del cliente). Permite practica directa, simulacros y demostraciones; horas estandar (2-4h).'
        };

        $userPrompt = "Genera una capacitación SST sobre el tema: \"{$tema}\".
{$hintModalidad}
Responde con este JSON exacto:
{
  \"capacitacion\": \"nombre formal de la capacitación en SST\",
  \"objetivo_capacitacion\": \"objetivo detallado de la capacitación (2-3 oraciones)\",
  \"perfil_de_asistentes\": \"TODOS\",
  \"horas_sugeridas\": 2
}
Para perfil_de_asistentes usa uno de: TODOS, BRIGADA, MIEMBROS_COPASST, RESPONSABLE_SST, SUPERVISORES, TRABAJADORES_RIESGOS_CRITICOS, CONTRATISTAS.";

        $data = [
            'model' => env('OPENAI_MODEL', 'gpt-4o'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 500
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'CURL error en generarConIA: ' . $error);
            return ['success' => false, 'error' => "Error de conexión: {$error}"];
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => $result['error']['message'] ?? 'Error HTTP ' . $httpCode];
        }

        if (isset($result['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'contenido' => trim($result['choices'][0]['message']['content'])
            ];
        }

        return ['success' => false, 'error' => 'Respuesta inesperada de OpenAI'];
    }

    /**
     * Normaliza el valor recibido para la columna modalidad.
     * Garantiza que sea uno de: VIRTUAL | PRESENCIAL | MIXTA. Default PRESENCIAL.
     */
    private function normalizarModalidad($valor): string
    {
        $v = strtoupper(trim((string) ($valor ?? '')));
        if (in_array($v, ['VIRTUAL', 'PRESENCIAL', 'MIXTA'], true)) {
            return $v;
        }
        return 'PRESENCIAL';
    }

    // ============================================================
    // ENVIAR CAPACITACIONES SELECCIONADAS A tbl_pta_cliente CON IA
    // ============================================================

    /**
     * Recibe ids de cronograma + id_cliente. Valida mismo cliente y llama a la IA
     * (gpt-4o-mini) para sugerir campos PTA. Devuelve un array de sugerencias
     * (una por capacitacion) con: id_cronograma_capacitacion, tipo_servicio, phva,
     * numeral, actividad, responsable_sugerido, fecha_propuesta, observaciones.
     * El estado_actividad se fija siempre como ABIERTA en la insercion (no se incluye aqui).
     */
    public function sugerirPta()
    {
        $ids = $this->request->getPost('ids');
        $idCliente = $this->request->getPost('id_cliente');

        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se recibieron ids.']);
        }
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ids invalidos.']);
        }
        if (empty($idCliente)) {
            return $this->response->setJSON(['success' => false, 'message' => 'id_cliente requerido.']);
        }

        $cronogModel = new CronogcapacitacionModel();
        $capacitacionModel = new CapacitacionModel();
        $clientModel = new ClientModel();

        $cronogs = $cronogModel->whereIn('id_cronograma_capacitacion', $ids)->findAll();
        if (empty($cronogs)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se encontraron cronogramas.']);
        }

        // Defensa en profundidad: todas las filas deben ser del mismo cliente
        $clientesUnicos = array_unique(array_map(static fn($c) => (string) $c['id_cliente'], $cronogs));
        if (count($clientesUnicos) > 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Las capacitaciones seleccionadas pertenecen a clientes distintos.'
            ]);
        }
        if ((string) $clientesUnicos[0] !== (string) $idCliente) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El cliente declarado no coincide con el de las filas.'
            ]);
        }

        $cliente = $clientModel->find($idCliente);
        $razonSocial = $cliente['nombre_cliente'] ?? 'La empresa';

        // Enriquecer con datos de la capacitacion
        $items = [];
        foreach ($cronogs as $c) {
            $cap = $capacitacionModel->find($c['id_capacitacion']);
            $items[] = [
                'id_cronograma_capacitacion' => (int) $c['id_cronograma_capacitacion'],
                'fecha_programada' => $c['fecha_programada'] ?? '',
                'estado' => $c['estado'] ?? '',
                'perfil_de_asistentes' => $c['perfil_de_asistentes'] ?? '',
                'modalidad' => $c['modalidad'] ?? 'PRESENCIAL',
                'horas' => $c['horas_de_duracion_de_la_capacitacion'] ?? '',
                'capacitador' => $c['nombre_del_capacitador'] ?? '',
                'capacitacion' => $cap['capacitacion'] ?? 'Capacitacion sin nombre',
                'objetivo' => $cap['objetivo_capacitacion'] ?? ''
            ];
        }

        $resp = $this->llamarIASugerirPta($razonSocial, $items);
        if (!$resp['success']) {
            return $this->response->setJSON(['success' => false, 'message' => $resp['error'] ?? 'Error IA']);
        }

        // Normalizar: garantizar que cada item tenga sugerencia (con fallback)
        $sugerenciasIA = $resp['sugerencias'];
        $byId = [];
        foreach ($sugerenciasIA as $s) {
            if (isset($s['id_cronograma_capacitacion'])) {
                $byId[(int) $s['id_cronograma_capacitacion']] = $s;
            }
        }

        $out = [];
        foreach ($items as $it) {
            $sug = $byId[$it['id_cronograma_capacitacion']] ?? [];
            $obsBase = trim(sprintf(
                'Capacitador: %s. Perfil: %s. Modalidad: %s. Duracion: %s h.',
                $it['capacitador'] !== '' ? $it['capacitador'] : 'N/A',
                $it['perfil_de_asistentes'] !== '' ? $it['perfil_de_asistentes'] : 'N/A',
                $it['modalidad'] !== '' ? $it['modalidad'] : 'PRESENCIAL',
                $it['horas'] !== '' ? $it['horas'] : 'N/A'
            ));
            $obsIA = trim((string) ($sug['observaciones'] ?? ''));
            $out[] = [
                'id_cronograma_capacitacion' => $it['id_cronograma_capacitacion'],
                'tipo_servicio' => $sug['tipo_servicio'] ?? 'CAPACITACION',
                'phva' => in_array(($sug['phva'] ?? 'H'), ['P','H','V','A'], true) ? $sug['phva'] : 'H',
                'numeral' => (string) ($sug['numeral'] ?? ''),
                'actividad' => (string) ($sug['actividad'] ?? ('Realizar capacitacion: ' . $it['capacitacion'])),
                'responsable_sugerido' => (string) ($sug['responsable_sugerido'] ?? 'Responsable del SG-SST'),
                'fecha_propuesta' => $it['fecha_programada'], // siempre del cronograma
                'observaciones' => trim($obsBase . ($obsIA !== '' ? ' ' . $obsIA : ''))
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'sugerencias' => $out,
            'tokens' => $resp['tokens'] ?? 0
        ]);
    }

    /**
     * Llama a OpenAI gpt-4o-mini con un prompt especifico para mapear capacitaciones
     * a campos del PTA. Devuelve un array con clave 'sugerencias' (una por id).
     */
    protected function llamarIASugerirPta(string $razonSocial, array $items): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'OPENAI_API_KEY no configurada'];
        }
        $model = env('OPENAI_MODEL', 'gpt-4o-mini');

        $systemPrompt = "Eres un asistente experto en SST (Colombia) que mapea capacitaciones programadas a actividades del Plan de Trabajo Anual (PTA), siguiendo Resolucion 0312/2019 y Decreto 1072/2015.\n"
            . "Para cada capacitacion recibida devuelve un objeto con: id_cronograma_capacitacion, tipo_servicio, phva, numeral, actividad, responsable_sugerido, observaciones.\n"
            . "Reglas:\n"
            . "1. tipo_servicio: nombre del programa SST al que pertenece (ej: CAPACITACION, PROGRAMA RIESGO BIOLOGICO, BRIGADA EMERGENCIAS, etc.). Por defecto 'CAPACITACION'.\n"
            . "2. phva: uno de P, H, V, A. Capacitaciones tipicas son H (Hacer); evaluacion de capacitacion seria V; planeacion de capacitacion seria P.\n"
            . "3. numeral: numeral de Resolucion 0312/2019 o articulo de Decreto 1072/2015 que mejor aplique (ej: '7.2', '6.1.2').\n"
            . "4. actividad: redaccion profesional de 2-3 lineas describiendo la accion en el PTA.\n"
            . "5. responsable_sugerido: ej Responsable del SG-SST, COPASST, ARL, Brigada, Capacitador externo.\n"
            . "6. observaciones: 1 linea breve adicional si aporta valor (puede quedar vacia). Considera la modalidad (VIRTUAL/PRESENCIAL/MIXTA) si es relevante para la actividad.\n"
            . "Responde SOLO con JSON valido en un objeto raiz {\"sugerencias\": [ ... ]}, sin markdown ni texto extra.";

        $itemsJson = json_encode(array_map(static function ($it) {
            return [
                'id_cronograma_capacitacion' => $it['id_cronograma_capacitacion'],
                'capacitacion' => $it['capacitacion'],
                'objetivo' => $it['objetivo'],
                'fecha_programada' => $it['fecha_programada'],
                'estado_cronograma' => $it['estado'],
                'perfil_asistentes' => $it['perfil_de_asistentes'],
                'modalidad' => $it['modalidad'],
                'horas' => $it['horas']
            ];
        }, $items), JSON_UNESCAPED_UNICODE);

        $userPrompt = "EMPRESA: {$razonSocial}\n"
            . "CAPACITACIONES (JSON):\n{$itemsJson}\n\n"
            . "Devuelve SOLO: {\"sugerencias\":[{\"id_cronograma_capacitacion\":N,\"tipo_servicio\":\"...\",\"phva\":\"P|H|V|A\",\"numeral\":\"...\",\"actividad\":\"...\",\"responsable_sugerido\":\"...\",\"observaciones\":\"...\"}, ...]}";

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.4,
            'max_tokens' => 1500,
            'response_format' => ['type' => 'json_object']
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['success' => false, 'error' => "Conexion: {$err}"];
        }
        $result = json_decode($response, true);
        if ($httpCode !== 200) {
            return ['success' => false, 'error' => $result['error']['message'] ?? ('HTTP ' . $httpCode)];
        }
        $contenido = $result['choices'][0]['message']['content'] ?? null;
        if (!$contenido) {
            return ['success' => false, 'error' => 'Respuesta vacia'];
        }
        $contenido = trim($contenido);
        $contenido = preg_replace('/^```(?:json)?\s*/i', '', $contenido);
        $contenido = preg_replace('/\s*```$/', '', $contenido);
        $parsed = json_decode($contenido, true);
        if (!is_array($parsed) || !isset($parsed['sugerencias']) || !is_array($parsed['sugerencias'])) {
            return ['success' => false, 'error' => 'JSON IA invalido', 'raw' => $contenido];
        }

        return [
            'success' => true,
            'sugerencias' => $parsed['sugerencias'],
            'tokens' => $result['usage']['total_tokens'] ?? 0
        ];
    }

    /**
     * Inserta las filas confirmadas en tbl_pta_cliente. Idempotencia por marca
     * "[ORIGEN: Cronograma #X]" en observaciones: si ya existe un registro para
     * el mismo cliente con esa marca, se omite. Estado siempre ABIERTA.
     */
    public function insertarEnPta()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $filasJson = $this->request->getPost('filas');

        if (empty($idCliente)) {
            return $this->response->setJSON(['success' => false, 'message' => 'id_cliente requerido.']);
        }
        $filas = json_decode((string) $filasJson, true);
        if (!is_array($filas) || empty($filas)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Filas invalidas.']);
        }

        $ptaModel = new \App\Models\PtaClienteNuevaModel();

        $insertadas = 0;
        $omitidas = 0;
        $idsCreados = [];
        $errores = [];

        foreach ($filas as $f) {
            $idCronog = (int) ($f['id_cronograma_capacitacion'] ?? 0);
            if ($idCronog <= 0) {
                $omitidas++;
                continue;
            }

            $marca = '[ORIGEN: Cronograma #' . $idCronog . ']';

            // Idempotencia: ya existe registro de este cronograma para este cliente?
            $existe = $ptaModel
                ->where('id_cliente', $idCliente)
                ->like('observaciones', $marca)
                ->first();
            if ($existe) {
                $omitidas++;
                continue;
            }

            $obs = trim((string) ($f['observaciones'] ?? ''));
            $obs = $obs === '' ? $marca : $obs . ' ' . $marca;

            $phva = strtoupper((string) ($f['phva'] ?? 'H'));
            if (!in_array($phva, ['P','H','V','A'], true)) {
                $phva = 'H';
            }

            $fechaProp = (string) ($f['fecha_propuesta'] ?? '');
            if ($fechaProp === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaProp)) {
                // fallback: traer fecha del cronograma
                $cron = (new CronogcapacitacionModel())->find($idCronog);
                $fechaProp = $cron['fecha_programada'] ?? date('Y-m-d');
            }

            $data = [
                'id_cliente' => $idCliente,
                'tipo_servicio' => trim((string) ($f['tipo_servicio'] ?? 'CAPACITACION')),
                'phva_plandetrabajo' => $phva,
                'numeral_plandetrabajo' => trim((string) ($f['numeral'] ?? '')),
                'actividad_plandetrabajo' => trim((string) ($f['actividad'] ?? '')),
                'responsable_sugerido_plandetrabajo' => trim((string) ($f['responsable_sugerido'] ?? '')),
                'fecha_propuesta' => $fechaProp,
                'estado_actividad' => 'ABIERTA',
                'observaciones' => $obs
            ];

            try {
                $newId = $ptaModel->insert($data, true);
                if ($newId) {
                    $insertadas++;
                    $idsCreados[] = $newId;
                } else {
                    $errores[] = 'No se pudo insertar cronograma #' . $idCronog;
                }
            } catch (\Throwable $e) {
                $errores[] = 'Error #' . $idCronog . ': ' . $e->getMessage();
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'insertadas' => $insertadas,
            'omitidas_duplicadas' => $omitidas,
            'ids_creados' => $idsCreados,
            'errores' => $errores
        ]);
    }

}
