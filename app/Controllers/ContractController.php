<?php

namespace App\Controllers;

use App\Models\ContractModel;
use App\Models\ClientModel;
use App\Libraries\ContractLibrary;
use App\Libraries\ContractPDFGenerator;
use CodeIgniter\Controller;
use SendGrid\Mail\Mail;

class ContractController extends Controller
{
    protected $contractModel;
    protected $clientModel;
    protected $contractLibrary;

    public function __construct()
    {
        $this->contractModel = new ContractModel();
        $this->clientModel = new ClientModel();
        $this->contractLibrary = new ContractLibrary();
        helper('contract');
    }

    /**
     * Lista todos los contratos con filtros
     */
    public function index()
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        // Filtros
        $estado = $this->request->getGet('estado');
        $tipo = $this->request->getGet('tipo');
        $idCliente = $this->request->getGet('id_cliente');

        $builder = $this->contractModel->builder();
        $builder->select('tbl_contratos.*, tbl_clientes.nombre_cliente, tbl_clientes.nit_cliente')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente');

        // Filtrar por consultor si es consultor
        if ($role === 'consultor') {
            $builder->where('tbl_clientes.id_consultor', $idConsultor);
        }

        // Aplicar filtros
        if ($estado) {
            $builder->where('tbl_contratos.estado', $estado);
        }

        if ($tipo) {
            $builder->where('tbl_contratos.tipo_contrato', $tipo);
        }

        if ($idCliente) {
            $builder->where('tbl_contratos.id_cliente', $idCliente);
        }

        $contracts = $builder->orderBy('tbl_contratos.created_at', 'DESC')->get()->getResultArray();

        // Obtener estadísticas
        $stats = $this->contractLibrary->getContractStats($role === 'consultor' ? $idConsultor : null);

        // Obtener lista de clientes para el filtro
        $clients = $role === 'consultor'
            ? $this->clientModel->where('id_consultor', $idConsultor)->findAll()
            : $this->clientModel->findAll();

        $data = [
            'contracts' => $contracts,
            'stats' => $stats,
            'clients' => $clients,
            'filters' => [
                'estado' => $estado,
                'tipo' => $tipo,
                'id_cliente' => $idCliente
            ]
        ];

        return view('contracts/list', $data);
    }

    /**
     * Ver detalles de un contrato
     */
    public function view($idContrato)
    {
        $contract = $this->contractLibrary->getContractWithClient($idContrato);

        if (!$contract) {
            return redirect()->to('/contracts')->with('error', 'Contrato no encontrado');
        }

        // Verificar permisos
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        if ($role === 'consultor') {
            $client = $this->clientModel->find($contract['id_cliente']);
            if ($client['id_consultor'] != $idConsultor) {
                return redirect()->to('/contracts')->with('error', 'No tiene permisos para ver este contrato');
            }
        }

        // Obtener historial del cliente
        $history = $this->contractLibrary->getClientContractHistory($contract['id_cliente']);

        $data = [
            'contract' => $contract,
            'history' => $history
        ];

        return view('contracts/view', $data);
    }

    /**
     * Formulario para crear un nuevo contrato
     */
    public function create($idCliente = null)
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        // Obtener clientes según el rol
        if ($idCliente) {
            $client = $this->clientModel->find($idCliente);

            // Verificar permisos
            if ($role === 'consultor' && $client['id_consultor'] != $idConsultor) {
                return redirect()->to('/contracts')->with('error', 'No tiene permisos');
            }

            $clients = [$client];
        } else {
            $clients = $role === 'consultor'
                ? $this->clientModel->where('id_consultor', $idConsultor)->orderBy('nombre_cliente', 'ASC')->findAll()
                : $this->clientModel->orderBy('nombre_cliente', 'ASC')->findAll();
        }

        $data = [
            'clients' => $clients,
            'selected_client' => $idCliente
        ];

        return view('contracts/create', $data);
    }

    /**
     * Procesar la creación de un nuevo contrato
     */
    public function store()
    {
        $data = [
            'id_cliente' => $this->request->getPost('id_cliente'),
            'fecha_inicio' => $this->request->getPost('fecha_inicio'),
            'fecha_fin' => $this->request->getPost('fecha_fin'),
            'valor_contrato' => $this->request->getPost('valor_contrato'),
            'valor_mensual' => $this->request->getPost('valor_mensual'),
            'numero_cuotas' => $this->request->getPost('numero_cuotas'),
            'frecuencia_visitas' => $this->request->getPost('frecuencia_visitas'),
            'tipo_contrato' => $this->request->getPost('tipo_contrato'),
            'estado' => $this->request->getPost('estado') ?: 'activo',
            'observaciones' => $this->request->getPost('observaciones'),
            'clausula_cuarta_duracion' => $this->request->getPost('clausula_cuarta_duracion'),
            'clausula_primera_objeto' => $this->request->getPost('clausula_primera_objeto')
        ];

        // Validar que no se superpongan fechas
        $validation = $this->contractLibrary->canCreateContract(
            $data['id_cliente'],
            $data['fecha_inicio'],
            $data['fecha_fin']
        );

        if (!$validation['can_create']) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', $validation['message']);
        }

        // Crear el contrato
        $result = $this->contractLibrary->createContract($data);

        if ($result['success']) {
            return redirect()->to('/contracts/view/' . $result['contract_id'])
                           ->with('success', $result['message']);
        }

        return redirect()->back()
                       ->withInput()
                       ->with('error', $result['message']);
    }

    /**
     * Formulario para renovar un contrato
     */
    public function renew($idContrato)
    {
        $contract = $this->contractModel->find($idContrato);

        if (!$contract) {
            return redirect()->to('/contracts')->with('error', 'Contrato no encontrado');
        }

        // Verificar permisos
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        if ($role === 'consultor') {
            $client = $this->clientModel->find($contract['id_cliente']);
            if ($client['id_consultor'] != $idConsultor) {
                return redirect()->to('/contracts')->with('error', 'No tiene permisos');
            }
        }

        $client = $this->clientModel->find($contract['id_cliente']);

        $data = [
            'contract' => $contract,
            'client' => $client
        ];

        return view('contracts/renew', $data);
    }

    /**
     * Procesar la renovación de un contrato
     */
    public function processRenewal()
    {
        $idContrato = $this->request->getPost('id_contrato');
        $fechaFin = $this->request->getPost('fecha_fin');
        $valorContrato = $this->request->getPost('valor_contrato');
        $observaciones = $this->request->getPost('observaciones');

        $result = $this->contractLibrary->renewContract($idContrato, $fechaFin, $valorContrato, $observaciones);

        if ($result['success']) {
            return redirect()->to('/contracts/view/' . $result['contract_id'])
                           ->with('success', $result['message']);
        }

        return redirect()->back()
                       ->withInput()
                       ->with('error', $result['message']);
    }

    /**
     * Cancelar un contrato
     */
    public function cancel($idContrato)
    {
        if ($this->request->getMethod() === 'post') {
            $motivo = $this->request->getPost('motivo');

            $result = $this->contractLibrary->cancelContract($idContrato, $motivo);

            if ($result['success']) {
                return redirect()->to('/contracts')
                               ->with('success', $result['message']);
            }

            return redirect()->back()
                           ->with('error', $result['message']);
        }

        $contract = $this->contractLibrary->getContractWithClient($idContrato);

        if (!$contract) {
            return redirect()->to('/contracts')->with('error', 'Contrato no encontrado');
        }

        return view('contracts/cancel', ['contract' => $contract]);
    }

    /**
     * Ver historial de contratos de un cliente
     */
    public function clientHistory($idCliente)
    {
        $client = $this->clientModel->find($idCliente);

        if (!$client) {
            return redirect()->to('/contracts')->with('error', 'Cliente no encontrado');
        }

        // Verificar permisos
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        if ($role === 'consultor' && $client['id_consultor'] != $idConsultor) {
            return redirect()->to('/contracts')->with('error', 'No tiene permisos');
        }

        $history = $this->contractLibrary->getClientContractHistory($idCliente);

        $data = [
            'client' => $client,
            'history' => $history
        ];

        return view('contracts/client_history', $data);
    }

    /**
     * Dashboard de alertas de contratos
     */
    public function alerts()
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        $alerts = $this->contractLibrary->getContractAlerts(
            $role === 'consultor' ? $idConsultor : null,
            30
        );

        $data = [
            'alerts' => $alerts
        ];

        return view('contracts/alerts', $data);
    }

    /**
     * Ejecutar mantenimiento de contratos (cron job)
     */
    public function maintenance()
    {
        // Verificar que sea llamado desde CLI o con token de seguridad
        if (!is_cli()) {
            $token = $this->request->getGet('token');
            $expectedToken = env('CRON_TOKEN', 'changeme');

            if ($token !== $expectedToken) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No autorizado'
                ])->setStatusCode(401);
            }
        }

        $result = $this->contractLibrary->runMaintenance();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Mantenimiento ejecutado',
            'data' => $result
        ]);
    }

    /**
     * API: Obtener contrato activo de un cliente
     */
    public function getActiveContract($idCliente)
    {
        $contract = $this->contractModel->getActiveContract($idCliente);

        return $this->response->setJSON([
            'success' => true,
            'data' => $contract
        ]);
    }

    /**
     * API: Obtener estadísticas de contratos
     */
    public function getStats()
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        $stats = $this->contractLibrary->getContractStats($role === 'consultor' ? $idConsultor : null);

        return $this->response->setJSON([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Muestra el formulario para editar datos antes de generar el contrato
     */
    public function editContractData($idContrato)
    {
        $contract = $this->contractLibrary->getContractWithClient($idContrato);

        if (!$contract) {
            return redirect()->to('/contracts')->with('error', 'Contrato no encontrado');
        }

        // Verificar permisos
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        if ($role === 'consultor') {
            $client = $this->clientModel->find($contract['id_cliente']);
            if ($client['id_consultor'] != $idConsultor) {
                return redirect()->to('/contracts')->with('error', 'No tiene permisos');
            }
        }

        // Obtener lista de consultores para el select
        $consultorModel = new \App\Models\ConsultantModel();
        $consultores = $consultorModel->findAll();

        $data = [
            'contract' => $contract,
            'consultores' => $consultores
        ];

        return view('contracts/edit_contract_data', $data);
    }

    /**
     * Guarda los datos del contrato y genera el PDF
     */
    public function saveAndGeneratePDF($idContrato)
    {
        // Obtener datos del formulario
        $data = [
            'fecha_inicio' => $this->request->getPost('fecha_inicio'),
            'fecha_fin' => $this->request->getPost('fecha_fin'),
            'valor_contrato' => $this->request->getPost('valor_contrato'),
            'valor_mensual' => $this->request->getPost('valor_mensual'),
            'numero_cuotas' => $this->request->getPost('numero_cuotas'),
            'frecuencia_visitas' => $this->request->getPost('frecuencia_visitas'),
            'nombre_rep_legal_cliente' => $this->request->getPost('nombre_rep_legal_cliente'),
            'cedula_rep_legal_cliente' => $this->request->getPost('cedula_rep_legal_cliente'),
            'direccion_cliente' => $this->request->getPost('direccion_cliente'),
            'telefono_cliente' => $this->request->getPost('telefono_cliente'),
            'email_cliente' => $this->request->getPost('email_cliente'),
            'nombre_rep_legal_contratista' => $this->request->getPost('nombre_rep_legal_contratista'),
            'cedula_rep_legal_contratista' => $this->request->getPost('cedula_rep_legal_contratista'),
            'email_contratista' => $this->request->getPost('email_contratista'),
            'id_consultor_responsable' => $this->request->getPost('id_consultor_responsable'),
            'nombre_responsable_sgsst' => $this->request->getPost('nombre_responsable_sgsst'),
            'cedula_responsable_sgsst' => $this->request->getPost('cedula_responsable_sgsst'),
            'licencia_responsable_sgsst' => $this->request->getPost('licencia_responsable_sgsst'),
            'email_responsable_sgsst' => $this->request->getPost('email_responsable_sgsst'),
            'banco' => $this->request->getPost('banco'),
            'tipo_cuenta' => $this->request->getPost('tipo_cuenta'),
            'cuenta_bancaria' => $this->request->getPost('cuenta_bancaria'),
            'clausula_cuarta_duracion' => $this->request->getPost('clausula_cuarta_duracion'),
            'clausula_primera_objeto' => $this->request->getPost('clausula_primera_objeto')
        ];

        // Actualizar el contrato con los nuevos datos
        if (!$this->contractModel->update($idContrato, $data)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al guardar los datos del contrato');
        }

        // Obtener el contrato actualizado con datos del cliente
        $contract = $this->contractLibrary->getContractWithClient($idContrato);

        try {
            // 1. Generar el PDF
            $pdfGenerator = new ContractPDFGenerator();
            $pdfGenerator->generateContract($contract);

            // 2. Crear directorio si no existe
            $uploadDir = FCPATH . 'uploads/contratos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // 3. Guardar el PDF
            $fileName = 'contrato_' . $contract['numero_contrato'] . '_' . date('Ymd_His') . '.pdf';
            $filePath = $uploadDir . $fileName;
            $pdfGenerator->save($filePath);

            // 4. Actualizar base de datos con información de generación
            $this->contractModel->update($idContrato, [
                'contrato_generado' => 1,
                'fecha_generacion_contrato' => date('Y-m-d H:i:s'),
                'ruta_pdf_contrato' => 'uploads/contratos/' . $fileName
            ]);

            // 5. Enviar email con SendGrid
            $emailSent = $this->sendContractEmail($contract, $filePath, $fileName);

            if ($emailSent) {
                $this->contractModel->update($idContrato, [
                    'contrato_enviado' => 1,
                    'fecha_envio_contrato' => date('Y-m-d H:i:s'),
                    'email_envio_contrato' => 'diana.cuestas@cycloidtalent.com'
                ]);

                return redirect()->to('/contracts/view/' . $idContrato)
                               ->with('success', 'Contrato generado y enviado exitosamente a diana.cuestas@cycloidtalent.com');
            } else {
                return redirect()->to('/contracts/view/' . $idContrato)
                               ->with('warning', 'Contrato generado correctamente, pero hubo un error al enviar el email. Puede descargarlo manualmente.');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error generando contrato PDF: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Envía el contrato por email usando SendGrid
     */
    private function sendContractEmail($contract, $filePath, $fileName)
    {
        try {
            $email = new Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
            $email->setSubject("Nuevo Contrato Generado - " . $contract['numero_contrato']);
            $email->addTo("diana.cuestas@cycloidtalent.com", "Diana Cuestas");

            // Cuerpo del email en HTML
            $htmlContent = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #667eea;'>Contrato Generado Exitosamente</h2>

                    <p>Se ha generado un nuevo contrato con los siguientes datos:</p>

                    <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                        <tr>
                            <td style='padding: 8px; background-color: #f5f5f5; font-weight: bold;'>Número de Contrato:</td>
                            <td style='padding: 8px;'>" . htmlspecialchars($contract['numero_contrato']) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; background-color: #f5f5f5; font-weight: bold;'>Cliente:</td>
                            <td style='padding: 8px;'>" . htmlspecialchars($contract['nombre_cliente']) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; background-color: #f5f5f5; font-weight: bold;'>NIT:</td>
                            <td style='padding: 8px;'>" . htmlspecialchars($contract['nit_cliente']) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; background-color: #f5f5f5; font-weight: bold;'>Fecha de Inicio:</td>
                            <td style='padding: 8px;'>" . date('d/m/Y', strtotime($contract['fecha_inicio'])) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; background-color: #f5f5f5; font-weight: bold;'>Fecha de Finalización:</td>
                            <td style='padding: 8px;'>" . date('d/m/Y', strtotime($contract['fecha_fin'])) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; background-color: #f5f5f5; font-weight: bold;'>Valor del Contrato:</td>
                            <td style='padding: 8px;'>$" . number_format($contract['valor_contrato'], 0, ',', '.') . " COP</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; background-color: #f5f5f5; font-weight: bold;'>Responsable SG-SST:</td>
                            <td style='padding: 8px;'>" . htmlspecialchars($contract['nombre_responsable_sgsst']) . "</td>
                        </tr>
                    </table>

                    <p>El contrato PDF se encuentra adjunto a este correo.</p>

                    <p style='color: #666; font-size: 12px; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px;'>
                        Este es un mensaje automático del sistema de gestión de contratos de Cycloid Talent.<br>
                        Generado el " . date('d/m/Y H:i:s') . "
                    </p>
                </div>
            ";

            $email->addContent("text/html", $htmlContent);

            // Adjuntar el PDF
            $fileData = base64_encode(file_get_contents($filePath));
            $email->addAttachment($fileData, "application/pdf", $fileName, "attachment");

            // Enviar con SendGrid
            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);

            // Verificar que se envió correctamente (código 202 = aceptado)
            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', 'Contrato enviado por email exitosamente. Código: ' . $response->statusCode());
                return true;
            } else {
                log_message('error', 'Error al enviar email. Código: ' . $response->statusCode() . ' Body: ' . $response->body());
                return false;
            }

        } catch (\Exception $e) {
            log_message('error', 'Excepción al enviar email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Genera la Cláusula Cuarta con IA usando los acuerdos contractuales
     */
    public function generarClausulaIA()
    {
        $json = $this->request->getJSON(true);

        $idCliente = $json['id_cliente'] ?? null;
        $plazoEjecucion = $json['plazo_ejecucion'] ?? '';
        $duracionContrato = $json['duracion_contrato'] ?? '';
        $fechaInicio = $json['fecha_inicio'] ?? '';
        $fechaFin = $json['fecha_fin'] ?? '';
        $porcentajeAnticipo = $json['porcentaje_anticipo'] ?? '';
        $condicionesPago = $json['condiciones_pago'] ?? '';
        $terminacionAnticipada = $json['terminacion_anticipada'] ?? '';
        $obligacionesEspeciales = $json['obligaciones_especiales'] ?? '';
        $contextoAdicional = $json['contexto_adicional'] ?? '';
        $textoActual = $json['texto_actual'] ?? '';
        $modoRefinamiento = $json['modo_refinamiento'] ?? false;

        // Formatear fechas a formato largo español (ej: "1 de marzo de 2025")
        $fechaInicioFormateada = '';
        $fechaFinFormateada = '';
        if ($fechaInicio) {
            $fechaInicioFormateada = $this->formatearFechaLarga($fechaInicio);
        }
        if ($fechaFin) {
            $fechaFinFormateada = $this->formatearFechaLarga($fechaFin);
        }

        // Obtener datos del cliente si se seleccionó
        $infoCliente = '';
        if ($idCliente) {
            $client = $this->clientModel->find($idCliente);
            if ($client) {
                $infoCliente = "Datos del cliente (EL CONTRATANTE): {$client['nombre_cliente']}, NIT: {$client['nit_cliente']}, " .
                    "Actividad Económica: " . ($client['codigo_actividad_economica'] ?? 'No especificada') . ", " .
                    "Ciudad: " . ($client['ciudad_cliente'] ?? 'No especificada') . ".";
            }
        }

        // Fechas para el prompt
        $infoFechas = '';
        if ($fechaInicioFormateada && $fechaFinFormateada) {
            $infoFechas = "Fecha de inicio del contrato: {$fechaInicioFormateada}\nFecha de finalización del contrato: {$fechaFinFormateada}";
        } elseif ($fechaInicioFormateada) {
            $infoFechas = "Fecha de inicio del contrato: {$fechaInicioFormateada}";
        }

        // Construir el prompt según el modo
        if ($modoRefinamiento && !empty($textoActual)) {
            $prompt = "Eres un abogado experto en contratos de prestación de servicios de diseño e implementación del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST) en Colombia.\n\n" .
                "Tienes el siguiente texto de Cláusula Cuarta de un contrato:\n\n" .
                "--- TEXTO ACTUAL ---\n{$textoActual}\n--- FIN TEXTO ---\n\n" .
                "El usuario quiere que apliques las siguientes modificaciones o mejoras:\n{$contextoAdicional}\n\n" .
                ($infoCliente ? $infoCliente . "\n\n" : "") .
                ($infoFechas ? $infoFechas . "\n\n" : "") .
                "REGLAS OBLIGATORIAS:\n" .
                "- Las partes SIEMPRE en mayúsculas sostenidas: EL CONTRATANTE y EL CONTRATISTA\n" .
                "- Usar las fechas reales proporcionadas en formato largo (ej: 1 de marzo de 2025). NUNCA placeholders como [FECHA]\n" .
                "- Referenciar que el objeto del contrato es la prestación de servicios de SST\n\n" .
                "Reescribe la cláusula completa aplicando los cambios solicitados. Mantén el formato legal formal. " .
                "Responde SOLO con el texto de la cláusula, sin explicaciones ni comentarios adicionales.";
        } else {
            $acuerdos = [];
            if ($plazoEjecucion) $acuerdos[] = "Plazo de ejecución: {$plazoEjecucion}";
            if ($duracionContrato) $acuerdos[] = "Duración del contrato: {$duracionContrato}";
            if ($fechaInicioFormateada) $acuerdos[] = "Fecha de inicio: {$fechaInicioFormateada}";
            if ($fechaFinFormateada) $acuerdos[] = "Fecha de finalización: {$fechaFinFormateada}";
            if ($porcentajeAnticipo) $acuerdos[] = "Porcentaje de anticipo: {$porcentajeAnticipo}";
            if ($condicionesPago) $acuerdos[] = "Condiciones de pago: {$condicionesPago}";
            if ($terminacionAnticipada) $acuerdos[] = "Condiciones de terminación anticipada: {$terminacionAnticipada}";
            if ($obligacionesEspeciales) $acuerdos[] = "Obligaciones especiales: {$obligacionesEspeciales}";
            if ($contextoAdicional) $acuerdos[] = "Contexto adicional: {$contextoAdicional}";

            $acuerdosTexto = implode("\n", $acuerdos);

            $prompt = "Eres un abogado experto en contratos de prestación de servicios de diseño e implementación del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST) en Colombia.\n\n" .
                "Genera la CLÁUSULA CUARTA (Duración y Plazo de Ejecución) de un contrato de prestación de servicios SST con los siguientes acuerdos contractuales:\n\n" .
                ($infoCliente ? $infoCliente . "\n\n" : "") .
                $acuerdosTexto . "\n\n" .
                "REGLAS OBLIGATORIAS:\n" .
                "- Las partes SIEMPRE en mayúsculas sostenidas: EL CONTRATANTE y EL CONTRATISTA\n" .
                "- Usar las fechas reales proporcionadas en formato largo español (ej: 1 de marzo de 2025). NUNCA usar placeholders como [FECHA DE INICIO] o [FECHA]\n" .
                "- La duración debe ser coherente con las fechas de inicio y fin proporcionadas\n" .
                "- Mencionar que el objeto es la prestación de servicios de diseño e implementación del SG-SST\n" .
                "- Si hay anticipo, incluir un parágrafo sobre las condiciones del anticipo\n\n" .
                "La cláusula debe incluir:\n" .
                "1. CUARTA-PLAZO DE EJECUCIÓN: EL CONTRATISTA ejecutará en [plazo] días calendario, contados a partir de [fecha inicio real]\n" .
                "2. CUARTA-DURACIÓN: El contrato tendrá duración de [duración], desde [fecha inicio real] hasta [fecha fin real]\n" .
                "3. PARÁGRAFO PRIMERO: Terminación anticipada, solo se reconocen honorarios causados por actividades ejecutadas\n" .
                "4. PARÁGRAFO SEGUNDO: NO opera prórroga automática, requiere acuerdo escrito entre las partes\n" .
                "5. Parágrafos adicionales según las obligaciones especiales y anticipo acordados\n\n" .
                "Usa lenguaje jurídico formal colombiano. Responde SOLO con el texto de la cláusula, sin explicaciones ni comentarios adicionales.";
        }

        try {
            $iaService = new \App\Services\IADocumentacionService();
            $textoGenerado = $iaService->generarContenido($prompt, 1500);

            return $this->response->setJSON([
                'success' => true,
                'texto' => trim($textoGenerado)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error generando cláusula con IA: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar con IA: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Genera la Cláusula Primera (Objeto del Contrato) con IA
     */
    public function generarClausula1IA()
    {
        $json = $this->request->getJSON(true);

        $idCliente = $json['id_cliente'] ?? null;
        $descripcionServicio = $json['descripcion_servicio'] ?? 'Diseño e implementación del SG-SST';
        $tipoConsultor = $json['tipo_consultor'] ?? 'externo'; // interno | externo
        $nombreCoordinador = $json['nombre_coordinador'] ?? '';
        $cedulaCoordinador = $json['cedula_coordinador'] ?? '';
        $licenciaCoordinador = $json['licencia_coordinador'] ?? '';
        $contextoAdicional = $json['contexto_adicional'] ?? '';
        $textoActual = $json['texto_actual'] ?? '';
        $modoRefinamiento = $json['modo_refinamiento'] ?? false;

        // Obtener datos del cliente
        $infoCliente = '';
        if ($idCliente) {
            $client = $this->clientModel->find($idCliente);
            if ($client) {
                $infoCliente = "Datos del cliente (EL CONTRATANTE): {$client['nombre_cliente']}, NIT: {$client['nit_cliente']}, " .
                    "Actividad Económica: " . ($client['codigo_actividad_economica'] ?? $client['actividad_economica_principal'] ?? 'No especificada') . ", " .
                    "Ciudad: " . ($client['ciudad_cliente'] ?? 'No especificada') . ", " .
                    "Nivel de riesgo ARL: " . ($client['nivel_riesgo_arl'] ?? 'No especificado') . ".";
            }
        }

        // Info del coordinador SST
        $infoCoordinador = '';
        if ($nombreCoordinador) {
            $infoCoordinador = "Profesional SST asignado: {$nombreCoordinador}";
            if ($cedulaCoordinador) $infoCoordinador .= ", cédula {$cedulaCoordinador}";
            if ($licenciaCoordinador) $infoCoordinador .= ", licencia SST N° {$licenciaCoordinador}";
            $infoCoordinador .= ".";
        }

        // Construir prompt
        if ($modoRefinamiento && !empty($textoActual)) {
            $prompt = "Eres un abogado experto en contratos de prestación de servicios de diseño e implementación del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST) en Colombia.\n\n" .
                "Tienes el siguiente texto de Cláusula Primera (Objeto del Contrato):\n\n" .
                "--- TEXTO ACTUAL ---\n{$textoActual}\n--- FIN TEXTO ---\n\n" .
                "El usuario quiere que apliques las siguientes modificaciones o mejoras:\n{$contextoAdicional}\n\n" .
                ($infoCliente ? $infoCliente . "\n\n" : "") .
                ($infoCoordinador ? $infoCoordinador . "\n\n" : "") .
                "REGLAS OBLIGATORIAS:\n" .
                "- Las partes SIEMPRE en mayúsculas sostenidas: EL CONTRATANTE y EL CONTRATISTA\n" .
                "- El profesional SST debe nombrarse como responsable técnico y coordinador del servicio\n" .
                "- Mencionar la plataforma EnterpriseSST como herramienta de gestión\n" .
                "- Referenciar la Resolución 0312 de 2019 y estándares mínimos del SG-SST\n\n" .
                "Reescribe la cláusula completa aplicando los cambios solicitados. Mantén el formato legal formal. " .
                "Responde SOLO con el texto de la cláusula, sin explicaciones ni comentarios adicionales.";
        } else {
            $datosContexto = [];
            $datosContexto[] = "Descripción del servicio: {$descripcionServicio}";
            $datosContexto[] = "Tipo de consultor: {$tipoConsultor}";
            if ($infoCoordinador) $datosContexto[] = $infoCoordinador;
            if ($contextoAdicional) $datosContexto[] = "Contexto adicional: {$contextoAdicional}";

            $contextoTexto = implode("\n", $datosContexto);

            // Sección condicional para consultor externo
            $seccionExterna = '';
            if ($tipoConsultor === 'externo') {
                $seccionExterna = "\n\nIMPORTANTE - CONSULTOR EXTERNO:\n" .
                    "Como el consultor es EXTERNO, la cláusula DEBE incluir:\n" .
                    "- Un párrafo explícito de DELEGACIÓN DE VISITAS: las visitas presenciales podrán ser realizadas por otros profesionales del equipo de EL CONTRATISTA, siempre bajo la coordinación y responsabilidad del profesional SST asignado.\n" .
                    "- Indicar que el profesional SST asignado actúa como responsable técnico y coordinador del servicio, supervisando todas las actividades.\n" .
                    "- Que EL CONTRATISTA garantiza que los profesionales delegados cuentan con la formación y licencia requerida en SST.";
            }

            $prompt = "Eres un abogado experto en contratos de prestación de servicios de diseño e implementación del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST) en Colombia.\n\n" .
                "Genera la CLÁUSULA PRIMERA (Objeto del Contrato) de un contrato de prestación de servicios SST con los siguientes datos:\n\n" .
                ($infoCliente ? $infoCliente . "\n\n" : "") .
                $contextoTexto . "\n\n" .
                $seccionExterna .
                "\n\nREGLAS OBLIGATORIAS:\n" .
                "- Las partes SIEMPRE en mayúsculas sostenidas: EL CONTRATANTE y EL CONTRATISTA\n" .
                "- Nombrar al profesional SST como 'responsable técnico y coordinador del servicio' con nombre completo, cédula y licencia\n" .
                "- Mencionar la plataforma EnterpriseSST como herramienta principal de gestión documental y seguimiento del SG-SST\n" .
                "- Referenciar la Resolución 0312 de 2019 y los estándares mínimos del SG-SST\n" .
                "- Incluir servicios detallados: diseño documental, implementación, supervisión, capacitación, medidas preventivas y correctivas\n" .
                "- NUNCA usar placeholders como [NOMBRE] o [FECHA]. Usar los datos reales proporcionados\n\n" .
                "ESTRUCTURA ESPERADA:\n" .
                "1. PRIMERA-OBJETO DEL CONTRATO: descripción general del servicio de consultoría SST\n" .
                "2. Descripción de la plataforma EnterpriseSST y su rol en la gestión\n" .
                "3. Asignación del profesional SST como responsable técnico\n" .
                "4. Detalle de los servicios incluidos (supervisión, capacitación, documentación, etc.)\n" .
                ($tipoConsultor === 'externo' ? "5. Párrafo de delegación de visitas presenciales\n" : "") .
                "\nUsa lenguaje jurídico formal colombiano. Responde SOLO con el texto de la cláusula, sin explicaciones ni comentarios adicionales.";
        }

        try {
            $iaService = new \App\Services\IADocumentacionService();
            $textoGenerado = $iaService->generarContenido($prompt, 1500);

            return $this->response->setJSON([
                'success' => true,
                'texto' => trim($textoGenerado)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error generando Cláusula Primera con IA: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar con IA: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Formatea una fecha ISO (YYYY-MM-DD) a formato largo español (ej: "1 de marzo de 2025")
     */
    private function formatearFechaLarga(string $fecha): string
    {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];

        $timestamp = strtotime($fecha);
        if (!$timestamp) return $fecha;

        $dia = (int) date('j', $timestamp);
        $mes = (int) date('n', $timestamp);
        $anio = date('Y', $timestamp);

        return "{$dia} de {$meses[$mes]} de {$anio}";
    }

    /**
     * Descarga el PDF del contrato generado
     */
    public function downloadPDF($idContrato)
    {
        $contract = $this->contractModel->find($idContrato);

        if (!$contract || !$contract['ruta_pdf_contrato']) {
            return redirect()->to('/contracts/view/' . $idContrato)
                           ->with('error', 'PDF no disponible');
        }

        // Verificar permisos
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        if ($role === 'consultor') {
            $client = $this->clientModel->find($contract['id_cliente']);
            if ($client['id_consultor'] != $idConsultor) {
                return redirect()->to('/contracts')->with('error', 'No tiene permisos');
            }
        }

        $filePath = FCPATH . $contract['ruta_pdf_contrato'];

        if (!file_exists($filePath)) {
            return redirect()->to('/contracts/view/' . $idContrato)
                           ->with('error', 'El archivo PDF no existe en el servidor');
        }

        // Descargar el archivo
        return $this->response->download($filePath, null)->setFileName(basename($filePath));
    }

    /**
     * Diagnóstico temporal de firmas para contrato (ELIMINAR después de verificar)
     */
    public function diagnosticoFirmas($idContrato)
    {
        $contract = $this->contractLibrary->getContractWithClient($idContrato);

        if (!$contract) {
            return $this->response->setJSON(['error' => 'Contrato no encontrado']);
        }

        $firmaRepLegal = $contract['firma_representante_legal'] ?? null;
        $firmaConsultor = $contract['firma_consultor'] ?? null;

        $diagnostico = [
            'fcpath' => FCPATH,
            'rootpath' => ROOTPATH,
            'id_contrato' => $idContrato,
            'firma_representante_legal_db' => $firmaRepLegal ?: '(VACIO)',
            'firma_consultor_db' => $firmaConsultor ?: '(VACIO)',
            'firma_dianita' => [
                'ruta' => FCPATH . 'img/FIRMA_DIANITA.jpg',
                'existe' => file_exists(FCPATH . 'img/FIRMA_DIANITA.jpg'),
            ],
            'firma_rep_legal' => [
                'ruta_uploads' => $firmaRepLegal ? FCPATH . 'uploads/' . $firmaRepLegal : '(n/a)',
                'existe_uploads' => $firmaRepLegal ? file_exists(FCPATH . 'uploads/' . $firmaRepLegal) : false,
            ],
            'firma_consultor' => [
                'ruta_uploads' => $firmaConsultor ? FCPATH . 'uploads/' . $firmaConsultor : '(n/a)',
                'existe_uploads' => $firmaConsultor ? file_exists(FCPATH . 'uploads/' . $firmaConsultor) : false,
            ],
            'uploads_dir_exists' => is_dir(FCPATH . 'uploads'),
        ];

        return $this->response->setJSON($diagnostico);
    }

    // =========================================================================
    // FIRMA DIGITAL DE CONTRATOS (Sistema independiente)
    // =========================================================================

    /**
     * Envía solicitud de firma digital al representante legal del cliente
     */
    public function enviarFirma()
    {
        $idContrato = $this->request->getPost('id_contrato');

        $contract = $this->contractLibrary->getContractWithClient($idContrato);
        if (!$contract) {
            return redirect()->to('/contracts')->with('error', 'Contrato no encontrado');
        }

        // Validar que tenga PDF generado
        if (empty($contract['contrato_generado'])) {
            return redirect()->to('/contracts/view/' . $idContrato)
                ->with('error', 'Debe generar el PDF del contrato antes de enviarlo a firmar');
        }

        // Validar que no esté ya firmado
        if (($contract['estado_firma'] ?? '') === 'firmado') {
            return redirect()->to('/contracts/view/' . $idContrato)
                ->with('error', 'Este contrato ya fue firmado');
        }

        // Validar email del cliente
        $emailCliente = $contract['email_cliente'] ?? '';
        if (empty($emailCliente)) {
            return redirect()->to('/contracts/view/' . $idContrato)
                ->with('error', 'El contrato no tiene email del representante legal del cliente');
        }

        // Generar token
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+7 days'));

        // Actualizar contrato
        $this->contractModel->update($idContrato, [
            'token_firma' => $token,
            'token_firma_expiracion' => $expiracion,
            'estado_firma' => 'pendiente_firma'
        ]);

        // URL de firma
        $urlFirma = base_url("contrato/firmar/{$token}");
        $nombreFirmante = $contract['nombre_rep_legal_cliente'] ?? 'Representante Legal';

        // Enviar email principal al representante legal del cliente
        $enviado = $this->enviarEmailFirmaContrato(
            $emailCliente,
            $nombreFirmante,
            $contract,
            $urlFirma,
            'Se requiere su firma digital para el contrato de prestacion de servicios SST.',
            false
        );

        if (!$enviado) {
            // Revertir si falla el envío
            $this->contractModel->update($idContrato, [
                'token_firma' => null,
                'token_firma_expiracion' => null,
                'estado_firma' => 'sin_enviar'
            ]);
            return redirect()->to('/contracts/view/' . $idContrato)
                ->with('error', 'Error al enviar el correo. Verifique la configuracion de SendGrid.');
        }

        // Enviar copia informativa al responsable SG-SST si tiene email
        $emailResponsable = $contract['email_responsable_sgsst'] ?? '';
        if (!empty($emailResponsable) && $emailResponsable !== $emailCliente) {
            $this->enviarEmailFirmaContrato(
                $emailResponsable,
                $contract['nombre_responsable_sgsst'] ?? 'Responsable SST',
                $contract,
                $urlFirma,
                'El Representante Legal debe firmar este contrato. Se le envia copia informativa.',
                true
            );
        }

        return redirect()->to('/contracts/view/' . $idContrato)
            ->with('success', 'Solicitud de firma enviada correctamente a ' . $emailCliente);
    }

    /**
     * Página pública de firma del contrato (sin auth)
     */
    public function paginaFirmaContrato($token)
    {
        $db = \Config\Database::connect();

        $contrato = $db->table('tbl_contratos')
            ->select('tbl_contratos.*, tbl_clientes.nombre_cliente, tbl_clientes.nit_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente')
            ->where('tbl_contratos.token_firma', $token)
            ->get()->getRowArray();

        if (!$contrato) {
            return view('contracts/firma_error_contrato', [
                'mensaje' => 'El enlace de firma no es valido o ya fue utilizado.'
            ]);
        }

        // Verificar estado
        $estadoFirma = $contrato['estado_firma'] ?? '';
        if ($estadoFirma === 'firmado') {
            return view('contracts/firma_error_contrato', [
                'mensaje' => 'Este contrato ya fue firmado anteriormente.'
            ]);
        }

        if ($estadoFirma !== 'pendiente_firma') {
            return view('contracts/firma_error_contrato', [
                'mensaje' => 'Este contrato no esta disponible para firma.'
            ]);
        }

        // Verificar expiración
        if (!empty($contrato['token_firma_expiracion']) && strtotime($contrato['token_firma_expiracion']) < time()) {
            return view('contracts/firma_error_contrato', [
                'mensaje' => 'El enlace de firma ha expirado. Solicite un nuevo enlace.'
            ]);
        }

        return view('contracts/contrato_firma', [
            'contrato' => $contrato,
            'token' => $token
        ]);
    }

    /**
     * Procesar firma digital del contrato (público, sin auth)
     */
    public function procesarFirmaContrato()
    {
        $token = $this->request->getPost('token');
        $firmaNombre = $this->request->getPost('firma_nombre');
        $firmaCedula = $this->request->getPost('firma_cedula');
        $firmaImagen = $this->request->getPost('firma_imagen');

        $db = \Config\Database::connect();

        // Validar token
        $contrato = $db->table('tbl_contratos')
            ->where('token_firma', $token)
            ->where('estado_firma', 'pendiente_firma')
            ->get()->getRowArray();

        if (!$contrato) {
            return $this->response->setJSON(['success' => false, 'message' => 'Token no valido']);
        }

        // Verificar expiración
        if (!empty($contrato['token_firma_expiracion']) && strtotime($contrato['token_firma_expiracion']) < time()) {
            return $this->response->setJSON(['success' => false, 'message' => 'El enlace ha expirado']);
        }

        // Guardar imagen de firma
        $rutaFirma = null;
        if ($firmaImagen) {
            $firmaData = explode(',', $firmaImagen);
            $firmaDecoded = base64_decode(end($firmaData));
            $nombreArchivo = 'firma_contrato_' . $contrato['id_contrato'] . '_' . time() . '.png';
            $rutaFirma = 'uploads/firmas/' . $nombreArchivo;

            if (!is_dir(FCPATH . 'uploads/firmas')) {
                mkdir(FCPATH . 'uploads/firmas', 0755, true);
            }

            file_put_contents(FCPATH . $rutaFirma, $firmaDecoded);
        }

        // Actualizar contrato
        $db->table('tbl_contratos')
            ->where('id_contrato', $contrato['id_contrato'])
            ->update([
                'estado_firma' => 'firmado',
                'firma_cliente_nombre' => $firmaNombre,
                'firma_cliente_cedula' => $firmaCedula,
                'firma_cliente_imagen' => $rutaFirma,
                'firma_cliente_ip' => $this->request->getIPAddress(),
                'firma_cliente_fecha' => date('Y-m-d H:i:s'),
                'token_firma' => null,
                'token_firma_expiracion' => null
            ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Contrato firmado correctamente'
        ]);
    }

    /**
     * Estado de firma de un contrato.
     * GET normal → renderiza vista HTML.
     * AJAX → retorna JSON.
     */
    public function estadoFirma($idContrato)
    {
        $contract = $this->contractLibrary->getContractWithClient($idContrato);

        if (!$contract) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Contrato no encontrado']);
            }
            return redirect()->to('/contracts')->with('error', 'Contrato no encontrado');
        }

        // AJAX → JSON (compatibilidad con llamadas existentes)
        if ($this->request->isAJAX()) {
            $data = [
                'success' => true,
                'estado_firma' => $contract['estado_firma'] ?? 'sin_enviar',
            ];
            if (($contract['estado_firma'] ?? '') === 'firmado') {
                $data['firma'] = [
                    'nombre' => $contract['firma_cliente_nombre'],
                    'cedula' => $contract['firma_cliente_cedula'],
                    'fecha' => $contract['firma_cliente_fecha'],
                    'ip' => $contract['firma_cliente_ip'],
                ];
            }
            return $this->response->setJSON($data);
        }

        // GET normal → vista HTML
        return view('contracts/estado_firma', [
            'contract' => $contract,
        ]);
    }

    /**
     * Envía email de solicitud de firma de contrato via SendGrid
     */
    private function enviarEmailFirmaContrato($email, $nombreFirmante, $contrato, $urlFirma, $mensaje, $esCopia = false)
    {
        $apiKey = env('SENDGRID_API_KEY');
        if (empty($apiKey)) {
            log_message('error', 'SENDGRID_API_KEY no configurada');
            return false;
        }

        // Renderizar template de email
        $htmlEmail = view('contracts/email_contrato_firma', [
            'nombreFirmante' => $nombreFirmante,
            'contrato' => $contrato,
            'urlFirma' => $urlFirma,
            'mensaje' => $mensaje,
            'esCopia' => $esCopia
        ]);

        $subject = $esCopia
            ? "[Copia] Solicitud de Firma: Contrato SST - {$contrato['nombre_cliente']}"
            : "Solicitud de Firma: Contrato SST - {$contrato['nombre_cliente']}";

        $fromEmail = env('SENDGRID_FROM_EMAIL', 'notificacion.cycloidtalent@cycloidtalent.com');
        $fromName = env('SENDGRID_FROM_NAME', 'Enterprise SST');

        $data = [
            'personalizations' => [
                [
                    'to' => [['email' => $email, 'name' => $nombreFirmante]],
                    'subject' => $subject
                ]
            ],
            'from' => [
                'email' => $fromEmail,
                'name' => $fromName
            ],
            'content' => [
                ['type' => 'text/html', 'value' => $htmlEmail]
            ]
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            log_message('error', "SendGrid Error (contrato firma) - HTTP {$httpCode}: {$response} | cURL: {$curlError}");
        }

        return $httpCode >= 200 && $httpCode < 300;
    }

    /**
     * Reenviar solicitud de firma de contrato.
     * Soporta email alternativo via POST AJAX.
     */
    public function reenviarFirmaContrato()
    {
        $idContrato = $this->request->getPost('id_contrato');
        $emailAlternativo = trim($this->request->getPost('email_alternativo') ?? '');
        $isAjax = $this->request->isAJAX();

        $contract = $this->contractLibrary->getContractWithClient($idContrato);
        if (!$contract) {
            if ($isAjax) return $this->response->setJSON(['success' => false, 'mensaje' => 'Contrato no encontrado']);
            return redirect()->back()->with('error', 'Contrato no encontrado');
        }

        if (($contract['estado_firma'] ?? '') !== 'pendiente_firma') {
            if ($isAjax) return $this->response->setJSON(['success' => false, 'mensaje' => 'El contrato no está en estado pendiente de firma']);
            return redirect()->back()->with('error', 'El contrato no está en estado pendiente de firma');
        }

        // Generar nuevo token
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+7 days'));

        $this->contractModel->update($idContrato, [
            'token_firma' => $token,
            'token_firma_expiracion' => $expiracion
        ]);

        $urlFirma = base_url("contrato/firmar/{$token}");

        // Determinar email destino
        $emailDestino = $contract['email_cliente'] ?? '';
        $nombreFirmante = $contract['nombre_rep_legal_cliente'] ?? 'Representante Legal';

        if (!empty($emailAlternativo) && filter_var($emailAlternativo, FILTER_VALIDATE_EMAIL)) {
            $emailDestino = $emailAlternativo;
        }

        if (empty($emailDestino)) {
            if ($isAjax) return $this->response->setJSON(['success' => false, 'mensaje' => 'No hay email destino']);
            return redirect()->back()->with('error', 'No hay email destino');
        }

        $enviado = $this->enviarEmailFirmaContrato(
            $emailDestino,
            $nombreFirmante,
            $contract,
            $urlFirma,
            'Se requiere su firma digital para el contrato de prestacion de servicios SST (REENVÍO).',
            false
        );

        if ($isAjax) {
            return $this->response->setJSON([
                'success' => $enviado,
                'mensaje' => $enviado
                    ? "Solicitud enviada a {$emailDestino}"
                    : 'Error al enviar el email'
            ]);
        }

        return redirect()->to('/contracts/view/' . $idContrato)
            ->with($enviado ? 'success' : 'error',
                   $enviado ? "Solicitud reenviada a {$emailDestino}" : 'Error al reenviar');
    }

    /**
     * Cancelar firma pendiente de un contrato.
     * Invalida el token y resetea el estado de firma a sin_enviar.
     */
    public function cancelarFirmaContrato()
    {
        $isAjax = $this->request->isAJAX();
        $idContrato = $this->request->getPost('id_contrato');

        $contract = $this->contractModel->find($idContrato);
        if (!$contract) {
            if ($isAjax) return $this->response->setJSON(['success' => false, 'mensaje' => 'Contrato no encontrado']);
            return redirect()->back()->with('error', 'Contrato no encontrado');
        }

        if (($contract['estado_firma'] ?? '') === 'firmado') {
            if ($isAjax) return $this->response->setJSON(['success' => false, 'mensaje' => 'El contrato ya fue firmado, no se puede cancelar']);
            return redirect()->back()->with('error', 'El contrato ya fue firmado, no se puede cancelar');
        }

        $this->contractModel->update($idContrato, [
            'estado_firma'          => 'sin_enviar',
            'token_firma'           => null,
            'token_firma_expiracion' => null,
        ]);

        $msg = 'Solicitud de firma cancelada para el contrato ' . ($contract['numero_contrato'] ?? '');
        if ($isAjax) return $this->response->setJSON(['success' => true, 'mensaje' => $msg]);
        return redirect()->to('/contracts/estado-firma/' . $idContrato)->with('success', $msg);
    }
}
