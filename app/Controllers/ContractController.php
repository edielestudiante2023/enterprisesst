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
            'clausula_cuarta_duracion' => $this->request->getPost('clausula_cuarta_duracion')
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
            'clausula_cuarta_duracion' => $this->request->getPost('clausula_cuarta_duracion')
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
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Método no permitido'])->setStatusCode(405);
        }

        $json = $this->request->getJSON(true);

        $idCliente = $json['id_cliente'] ?? null;
        $plazoEjecucion = $json['plazo_ejecucion'] ?? '';
        $duracionContrato = $json['duracion_contrato'] ?? '';
        $porcentajeAnticipo = $json['porcentaje_anticipo'] ?? '';
        $condicionesPago = $json['condiciones_pago'] ?? '';
        $terminacionAnticipada = $json['terminacion_anticipada'] ?? '';
        $obligacionesEspeciales = $json['obligaciones_especiales'] ?? '';
        $contextoAdicional = $json['contexto_adicional'] ?? '';
        $textoActual = $json['texto_actual'] ?? '';
        $modoRefinamiento = $json['modo_refinamiento'] ?? false;

        // Obtener datos del cliente si se seleccionó
        $infoCliente = '';
        if ($idCliente) {
            $client = $this->clientModel->find($idCliente);
            if ($client) {
                $infoCliente = "Datos del cliente: {$client['nombre_cliente']}, NIT: {$client['nit_cliente']}, " .
                    "Actividad Económica: " . ($client['codigo_actividad_economica'] ?? 'No especificada') . ", " .
                    "Ciudad: " . ($client['ciudad_cliente'] ?? 'No especificada') . ".";
            }
        }

        // Construir el prompt según el modo
        if ($modoRefinamiento && !empty($textoActual)) {
            $prompt = "Eres un abogado experto en contratos de prestación de servicios en SST (Seguridad y Salud en el Trabajo) en Colombia.\n\n" .
                "Tienes el siguiente texto de Cláusula Cuarta de un contrato:\n\n" .
                "--- TEXTO ACTUAL ---\n{$textoActual}\n--- FIN TEXTO ---\n\n" .
                "El usuario quiere que apliques las siguientes modificaciones o mejoras:\n{$contextoAdicional}\n\n" .
                ($infoCliente ? $infoCliente . "\n\n" : "") .
                "Reescribe la cláusula completa aplicando los cambios solicitados. Mantén el formato legal formal. " .
                "Responde SOLO con el texto de la cláusula, sin explicaciones ni comentarios adicionales.";
        } else {
            $acuerdos = [];
            if ($plazoEjecucion) $acuerdos[] = "Plazo de ejecución: {$plazoEjecucion}";
            if ($duracionContrato) $acuerdos[] = "Duración del contrato: {$duracionContrato}";
            if ($porcentajeAnticipo) $acuerdos[] = "Porcentaje de anticipo: {$porcentajeAnticipo}";
            if ($condicionesPago) $acuerdos[] = "Condiciones de pago: {$condicionesPago}";
            if ($terminacionAnticipada) $acuerdos[] = "Condiciones de terminación anticipada: {$terminacionAnticipada}";
            if ($obligacionesEspeciales) $acuerdos[] = "Obligaciones especiales: {$obligacionesEspeciales}";
            if ($contextoAdicional) $acuerdos[] = "Contexto adicional: {$contextoAdicional}";

            $acuerdosTexto = implode("\n", $acuerdos);

            $prompt = "Eres un abogado experto en contratos de prestación de servicios en SST (Seguridad y Salud en el Trabajo) en Colombia.\n\n" .
                "Genera la CLÁUSULA CUARTA (Duración y Plazo de Ejecución) de un contrato de prestación de servicios con los siguientes acuerdos contractuales:\n\n" .
                ($infoCliente ? $infoCliente . "\n\n" : "") .
                $acuerdosTexto . "\n\n" .
                "La cláusula debe incluir:\n" .
                "1. CUARTA-PLAZO DE EJECUCIÓN: con el plazo en días calendario y condiciones de inicio\n" .
                "2. CUARTA-DURACIÓN: con la duración del contrato y fechas si aplica\n" .
                "3. PARÁGRAFO PRIMERO: sobre terminación anticipada y reconocimiento de honorarios causados\n" .
                "4. PARÁGRAFO SEGUNDO: indicando que NO opera prórroga automática\n" .
                "5. Parágrafos adicionales según las obligaciones especiales acordadas\n\n" .
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
}
