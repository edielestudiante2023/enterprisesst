<?php

namespace App\Controllers;

use App\Models\CronogcapacitacionModel;
use App\Models\ClientModel;
use App\Models\CapacitacionModel;
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
        $cronogModel = new CronogcapacitacionModel();
        $clientModel = new ClientModel();
        $capacitacionModel = new CapacitacionModel();

        if (empty($clienteID)) {
            return $this->response->setJSON([]);
        }

        $cronogramas = $cronogModel->where('id_cliente', $clienteID)->findAll();

        // Enriquecer cada registro con datos del cliente y capacitación
        foreach ($cronogramas as &$cronograma) {
            $cliente = $clientModel->find($cronograma['id_cliente']);
            $cronograma['nombre_cliente'] = $cliente['nombre_cliente'] ?? 'Cliente no encontrado';

            $capacitacion = $capacitacionModel->find($cronograma['id_capacitacion']);
            $cronograma['nombre_capacitacion'] = $capacitacion['capacitacion'] ?? 'Capacitación no encontrada';
            $cronograma['objetivo_capacitacion'] = $capacitacion['objetivo_capacitacion'] ?? 'Objetivo no disponible';

            // Generar botones de acciones
            $cronograma['acciones'] = '<a href="' . base_url('/editcronogCapacitacion/' . $cronograma['id_cronograma_capacitacion']) . '" class="btn btn-warning btn-sm">Editar</a> ' .
                '<a href="' . base_url('/deletecronogCapacitacion/' . $cronograma['id_cronograma_capacitacion']) . '" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Estás seguro de eliminar este cronograma?\');">Eliminar</a>';
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
                    'nombre_del_capacitador' => $training['nombre_del_capacitador'] ?? 'CYCLOID TALENT',
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

            // Obtener email del cliente
            $emailCliente = $cliente['correo_cliente'] ?? null;

            // Obtener email del consultor asignado
            $emailConsultor = null;
            $nombreConsultor = null;
            if (!empty($cliente['id_consultor'])) {
                $consultorModel = new \App\Models\ConsultantModel();
                $consultor = $consultorModel->find($cliente['id_consultor']);
                if ($consultor) {
                    $emailConsultor = $consultor['correo_consultor'] ?? null;
                    $nombreConsultor = $consultor['nombre_consultor'] ?? null;
                }
            }

            // Validar que al menos uno tenga email
            if (empty($emailCliente) && empty($emailConsultor)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se encontraron emails configurados para el cliente ni el consultor'
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

            if (!empty($emailCliente) && filter_var($emailCliente, FILTER_VALIDATE_EMAIL)) {
                $destinatarios[] = $emailCliente;
                $emailsEnviados[] = $emailCliente . ' (Cliente)';
            }

            if (!empty($emailConsultor) && filter_var($emailConsultor, FILTER_VALIDATE_EMAIL)) {
                // Si el consultor no está ya en destinatarios
                if (!in_array($emailConsultor, $destinatarios)) {
                    $destinatarios[] = $emailConsultor;
                    $emailsEnviados[] = $emailConsultor . ' (Consultor: ' . $nombreConsultor . ')';
                }
            }

            if (empty($destinatarios)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No hay emails válidos para enviar'
                ]);
            }

            // Generar contenido HTML del email
            $htmlContent = $this->generarHtmlCronograma($cliente, $cronogramas, '', $targetYear);

            // Preparar email con SendGrid
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "EnterpriseSST - Cycloid Talent");
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
                <p><strong>Cycloid Talent SAS</strong> - Sistema de Gestión SST</p>
                <p>Este es un correo automático generado por el sistema. Por favor no responda a este mensaje.</p>
                <p>© ' . date('Y') . ' Cycloid Talent SAS - NIT: 901.653.912</p>
            </div>
        </body>
        </html>';

        return $html;
    }

}
