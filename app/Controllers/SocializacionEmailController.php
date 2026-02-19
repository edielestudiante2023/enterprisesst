<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ContractModel;
use App\Models\PtaClienteNuevaModel;
use App\Models\EvaluationModel;
use App\Models\ResponsableSSTModel;

class SocializacionEmailController extends BaseController
{
    /**
     * Envía email de socialización del Plan de Trabajo Anual
     * Envía automáticamente al cliente y al consultor asignado
     */
    public function sendPlanTrabajo()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $selectedYear = $this->request->getPost('year'); // Año seleccionado desde el frontend

        if (empty($idCliente)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de cliente no proporcionado'
            ]);
        }

        try {
            // Cargar modelos
            $clientModel = new ClientModel();
            $consultantModel = new ConsultantModel();
            $contractModel = new ContractModel();
            $ptaModel = new PtaClienteNuevaModel();

            // Obtener datos del cliente
            $cliente = $clientModel->find($idCliente);
            if (!$cliente) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ]);
            }

            // Obtener consultor asignado
            $consultor = null;
            $nombreConsultor = 'Consultor SST';
            if (!empty($cliente['id_consultor'])) {
                $consultor = $consultantModel->find($cliente['id_consultor']);
                if ($consultor) {
                    $nombreConsultor = $consultor['nombre_consultor'] ?? 'Consultor SST';
                }
            }

            // Obtener contrato (primero activo, sino el más reciente)
            $contrato = $contractModel->where('id_cliente', $idCliente)
                ->where('estado', 'activo')
                ->orderBy('created_at', 'DESC')
                ->first();

            if (!$contrato) {
                $contrato = $contractModel->where('id_cliente', $idCliente)
                    ->orderBy('created_at', 'DESC')
                    ->first();
            }

            // Usar año seleccionado o año actual por defecto
            $targetYear = !empty($selectedYear) && is_numeric($selectedYear) ? (int)$selectedYear : (int)date('Y');

            // Obtener actividades del año seleccionado
            $actividades = $ptaModel->where('id_cliente', $idCliente)
                ->where("YEAR(fecha_propuesta)", $targetYear)
                ->orderBy('fecha_propuesta', 'ASC')
                ->findAll();

            if (empty($actividades)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "No hay actividades registradas para el año {$targetYear}"
                ]);
            }

            // Obtener destinatarios desde tbl_cliente_responsables_sst
            $responsableModel = new ResponsableSSTModel();
            $repLegal = $responsableModel->getRepresentanteLegal($idCliente);
            $delegadoSST = $responsableModel->getResponsableSGSST($idCliente);

            $tieneEmailRepLegal = $repLegal && !empty($repLegal['email']) && filter_var($repLegal['email'], FILTER_VALIDATE_EMAIL);
            $tieneEmailDelegado = $delegadoSST && !empty($delegadoSST['email']) && filter_var($delegadoSST['email'], FILTER_VALIDATE_EMAIL);

            if (!$tieneEmailRepLegal && !$tieneEmailDelegado) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se encontraron emails válidos en Responsables SST. Registre el email del Representante Legal o del Responsable SG-SST en el módulo Responsables SST.'
                ]);
            }

            // Construir HTML del email
            $htmlContent = $this->buildEmailContent($cliente, $consultor, $contrato, $actividades, $targetYear);

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

            // Preparar email con SendGrid
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "EnterpriseSST - Cycloid Talent");
            $email->setSubject("Plan de Trabajo Anual SG-SST {$targetYear} - {$cliente['nombre_cliente']}");

            // Agregar destinatarios
            foreach ($destinatarios as $destinatario) {
                $email->addTo($destinatario);
            }

            $email->addContent("text/html", $htmlContent);

            // Enviar con SendGrid
            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', "Email de socialización PTA enviado a " . implode(', ', $destinatarios) . " para cliente ID: {$idCliente}");

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Email enviado correctamente con ' . count($actividades) . ' actividades del año ' . $targetYear,
                    'emailsEnviados' => $emailsEnviados,
                    'totalActividades' => count($actividades),
                    'cliente' => $cliente['nombre_cliente'],
                    'year' => $targetYear
                ]);
            } else {
                log_message('error', 'Error al enviar email de socialización PTA. Status: ' . $response->statusCode() . ' Body: ' . $response->body());

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al enviar el email. Código: ' . $response->statusCode()
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error en sendPlanTrabajo: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Construye el contenido HTML del email
     */
    private function buildEmailContent($cliente, $consultor, $contrato, $actividades, $anio)
    {
        $nombreConsultor = $consultor ? ($consultor['nombre_consultor'] ?? 'Consultor SST') : 'Consultor SST';
        $frecuenciaVisitas = $contrato ? ($contrato['frecuencia_visitas'] ?? 'No definida') : 'No definida';
        $fechaActual = date('d/m/Y');

        // Contar por estado
        $conteoEstados = ['ABIERTA' => 0, 'CERRADA' => 0, 'GESTIONANDO' => 0, 'CERRADA SIN EJECUCIÓN' => 0];
        foreach ($actividades as $act) {
            $estado = $act['estado_actividad'] ?? 'ABIERTA';
            if (isset($conteoEstados[$estado])) {
                $conteoEstados[$estado]++;
            }
        }
        $totalActividades = count($actividades);

        // Construir HTML
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .info-box { background-color: #f8f9fc; border-left: 4px solid #4e73df; padding: 15px; margin: 15px 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
                th { background-color: #4e73df; color: white; padding: 10px; text-align: left; }
                td { padding: 8px; border-bottom: 1px solid #ddd; }
                tr:nth-child(even) { background-color: #f8f9fc; }
                .footer { background-color: #f8f9fc; padding: 20px; text-align: center; margin-top: 30px; }
                .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
                .badge-abierta { background-color: #007bff; color: white; }
                .badge-cerrada { background-color: #28a745; color: white; }
                .badge-gestionando { background-color: #ffc107; color: black; }
                .badge-sin-ejecucion { background-color: #343a40; color: white; }
                .summary-table td { text-align: center; padding: 15px; }
                .year-badge { background-color: #f0ad4e; color: #333; padding: 5px 15px; border-radius: 20px; font-weight: bold; display: inline-block; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Plan de Trabajo Anual SG-SST</h1>
                <h2>" . esc($cliente['nombre_cliente']) . "</h2>
                <span class='year-badge'>Año {$anio}</span>
                <p style='margin-top: 10px; font-size: 14px;'>Socialización según Decreto 1072 de 2015</p>
            </div>

            <div class='content'>
                <div class='info-box'>
                    <p><strong>Cliente:</strong> " . esc($cliente['nombre_cliente']) . "</p>
                    <p><strong>NIT:</strong> " . esc($cliente['nit_cliente'] ?? 'No registrado') . "</p>
                    <p><strong>Consultor Asignado:</strong> {$nombreConsultor}</p>
                    <p><strong>Frecuencia de Visitas:</strong> {$frecuenciaVisitas}</p>
                    <p><strong>Fecha de Socialización:</strong> {$fechaActual}</p>
                </div>

                <h2>Resumen de Actividades</h2>
                <table class='summary-table'>
                    <tr>
                        <td style='background-color: #cce5ff;'><strong>Abiertas</strong><br><span style='font-size: 24px; font-weight: bold;'>{$conteoEstados['ABIERTA']}</span></td>
                        <td style='background-color: #d4edda;'><strong>Cerradas</strong><br><span style='font-size: 24px; font-weight: bold;'>{$conteoEstados['CERRADA']}</span></td>
                        <td style='background-color: #fff3cd;'><strong>Gestionando</strong><br><span style='font-size: 24px; font-weight: bold;'>{$conteoEstados['GESTIONANDO']}</span></td>
                        <td style='background-color: #e2e3e5;'><strong>Total</strong><br><span style='font-size: 24px; font-weight: bold;'>{$totalActividades}</span></td>
                    </tr>
                </table>

                <h2>Detalle del Plan de Trabajo</h2>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>PHVA</th>
                            <th>Numeral</th>
                            <th>Actividad</th>
                            <th>Responsable</th>
                            <th>Fecha Propuesta</th>
                            <th>Estado</th>
                            <th>Avance</th>
                        </tr>
                    </thead>
                    <tbody>";

        $i = 1;
        foreach ($actividades as $act) {
            $estado = $act['estado_actividad'] ?? 'ABIERTA';
            $badgeClass = 'badge-abierta';
            if ($estado === 'CERRADA') $badgeClass = 'badge-cerrada';
            elseif ($estado === 'GESTIONANDO') $badgeClass = 'badge-gestionando';
            elseif ($estado === 'CERRADA SIN EJECUCIÓN') $badgeClass = 'badge-sin-ejecucion';

            $fechaPropuesta = !empty($act['fecha_propuesta']) ? date('d/m/Y', strtotime($act['fecha_propuesta'])) : 'N/A';
            $porcentaje = $act['porcentaje_avance'] ?? '0';

            $html .= "
                        <tr>
                            <td>{$i}</td>
                            <td>" . esc($act['phva_plandetrabajo'] ?? '') . "</td>
                            <td>" . esc($act['numeral_plandetrabajo'] ?? '') . "</td>
                            <td>" . esc($act['actividad_plandetrabajo'] ?? '') . "</td>
                            <td>" . esc($act['responsable_sugerido_plandetrabajo'] ?? '') . "</td>
                            <td>{$fechaPropuesta}</td>
                            <td><span class='badge {$badgeClass}'>{$estado}</span></td>
                            <td>{$porcentaje}%</td>
                        </tr>";
            $i++;
        }

        $html .= "
                    </tbody>
                </table>

                <p style='margin-top: 20px;'><strong>Total de actividades:</strong> {$totalActividades}</p>

                <div class='footer'>
                    <p><strong>Cycloid Talent SAS</strong></p>
                    <p>Este documento hace parte de la socialización del Plan de Trabajo Anual del SG-SST.</p>
                    <p style='font-size: 11px; color: #666;'>Generado automáticamente por EnterpriseSST</p>
                </div>
            </div>
        </body>
        </html>";

        return $html;
    }

    /**
     * Envía email de socialización de la Evaluación de Estándares Mínimos SG-SST
     */
    public function sendEvaluacionEstandares()
    {
        $idCliente = $this->request->getPost('id_cliente');

        if (!$idCliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID de cliente no proporcionado']);
        }

        try {
            $clientModel = new ClientModel();
            $consultantModel = new ConsultantModel();
            $contractModel = new ContractModel();
            $evaluationModel = new EvaluationModel();

            $cliente = $clientModel->find($idCliente);
            if (!$cliente) {
                return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
            }

            $consultor = null;
            if (!empty($cliente['id_consultor'])) {
                $consultor = $consultantModel->find($cliente['id_consultor']);
            }

            $contrato = $contractModel->where('id_cliente', $idCliente)
                ->where('estado', 'activo')
                ->orderBy('created_at', 'DESC')
                ->first();

            if (!$contrato) {
                $contrato = $contractModel->where('id_cliente', $idCliente)
                    ->orderBy('created_at', 'DESC')
                    ->first();
            }

            // Obtener evaluaciones del cliente
            $anioActual = date('Y');
            $evaluaciones = $evaluationModel->where('id_cliente', $idCliente)
                ->orderBy('ciclo', 'ASC')
                ->orderBy('estandar', 'ASC')
                ->findAll();

            if (empty($evaluaciones)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No hay evaluaciones registradas para este cliente']);
            }

            // Calcular indicadores
            $indicadores = $this->calcularIndicadoresEvaluacion($evaluaciones);

            // Obtener destinatarios desde tbl_cliente_responsables_sst
            $responsableModel = new ResponsableSSTModel();
            $repLegal = $responsableModel->getRepresentanteLegal($idCliente);
            $respSGSST = $responsableModel->getResponsableSGSST($idCliente);

            $destinatarios = [];
            $emailsEnviados = [];

            if ($repLegal && !empty($repLegal['email']) && filter_var($repLegal['email'], FILTER_VALIDATE_EMAIL)) {
                $destinatarios[] = $repLegal['email'];
                $emailsEnviados[] = $repLegal['email'] . ' (Representante Legal)';
            }

            if ($respSGSST && !empty($respSGSST['email']) && filter_var($respSGSST['email'], FILTER_VALIDATE_EMAIL)) {
                if (!in_array($respSGSST['email'], $destinatarios)) {
                    $destinatarios[] = $respSGSST['email'];
                    $emailsEnviados[] = $respSGSST['email'] . ' (Responsable SG-SST)';
                }
            }

            if ($consultor && !empty($consultor['correo_consultor']) && filter_var($consultor['correo_consultor'], FILTER_VALIDATE_EMAIL)) {
                if (!in_array($consultor['correo_consultor'], $destinatarios)) {
                    $destinatarios[] = $consultor['correo_consultor'];
                    $emailsEnviados[] = $consultor['correo_consultor'] . ' (Consultor)';
                }
            }

            if (empty($destinatarios)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se encontraron emails válidos en Responsables SST. Registre el email del Representante Legal o del Responsable SG-SST en el módulo Responsables SST.'
                ]);
            }

            $htmlContent = $this->buildEvaluacionEmailContent($cliente, $consultor, $contrato, $evaluaciones, $indicadores, $anioActual);

            // Preparar email con SendGrid
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "EnterpriseSST - Cycloid Talent");
            $email->setSubject("Evaluación de Estándares Mínimos SG-SST {$anioActual} - {$cliente['nombre_cliente']}");

            foreach ($destinatarios as $destinatario) {
                $email->addTo($destinatario);
            }

            $email->addContent("text/html", $htmlContent);

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', "Email de socialización Evaluación Estándares Mínimos enviado a " . implode(', ', $destinatarios) . " para cliente ID: {$idCliente}");

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Email enviado correctamente con ' . count($evaluaciones) . ' items evaluados',
                    'emailsEnviados' => $emailsEnviados,
                    'indicador' => $indicadores['indicador_general'] . '%',
                    'cliente' => $cliente['nombre_cliente']
                ]);
            } else {
                log_message('error', 'Error al enviar email de socialización Evaluación. Status: ' . $response->statusCode() . ' Body: ' . $response->body());

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al enviar el email. Código: ' . $response->statusCode()
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error en sendEvaluacionEstandares: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Calcula indicadores para la evaluación de estándares mínimos
     */
    private function calcularIndicadoresEvaluacion($evaluaciones)
    {
        $sumPuntaje = 0;
        $sumValor = 0;
        $countCumple = 0;
        $countNoCumple = 0;
        $countNoAplica = 0;

        foreach ($evaluaciones as $ev) {
            $sumPuntaje += floatval($ev['puntaje_cuantitativo'] ?? 0);
            $sumValor += floatval($ev['valor'] ?? 0);

            $evaluacion = $ev['evaluacion_inicial'] ?? '';
            if ($evaluacion === 'CUMPLE TOTALMENTE') $countCumple++;
            elseif ($evaluacion === 'NO CUMPLE') $countNoCumple++;
            elseif ($evaluacion === 'NO APLICA') $countNoAplica++;
        }

        $indicadorGeneral = $sumValor > 0 ? round(($sumPuntaje / $sumValor) * 100, 2) : 0;

        return [
            'sum_puntaje' => $sumPuntaje,
            'sum_valor' => $sumValor,
            'indicador_general' => $indicadorGeneral,
            'count_cumple' => $countCumple,
            'count_no_cumple' => $countNoCumple,
            'count_no_aplica' => $countNoAplica,
            'total_items' => count($evaluaciones)
        ];
    }

    /**
     * Construye el contenido HTML del email de evaluación de estándares mínimos
     */
    private function buildEvaluacionEmailContent($cliente, $consultor, $contrato, $evaluaciones, $indicadores, $anio)
    {
        $nombreConsultor = $consultor ? ($consultor['nombre_consultor'] ?? 'Consultor SST') : 'Consultor SST';
        $frecuenciaVisitas = $contrato ? ($contrato['frecuencia_visitas'] ?? 'No definida') : 'No definida';
        $fechaActual = date('d/m/Y');

        // Agrupar por estándar
        $porEstandar = [];
        foreach ($evaluaciones as $ev) {
            $estandar = $ev['estandar'] ?? 'Sin categoría';
            if (!isset($porEstandar[$estandar])) {
                $porEstandar[$estandar] = ['total' => 0, 'cumple' => 0, 'no_cumple' => 0, 'no_aplica' => 0];
            }
            $porEstandar[$estandar]['total']++;
            $evaluacion = $ev['evaluacion_inicial'] ?? '';
            if ($evaluacion === 'CUMPLE TOTALMENTE') $porEstandar[$estandar]['cumple']++;
            elseif ($evaluacion === 'NO CUMPLE') $porEstandar[$estandar]['no_cumple']++;
            elseif ($evaluacion === 'NO APLICA') $porEstandar[$estandar]['no_aplica']++;
        }

        $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><style>
            body{font-family:Arial,sans-serif;line-height:1.6;color:#333}
            .header{background:linear-gradient(135deg,#f6c23e 0%,#dda20a 100%);color:#333;padding:20px;text-align:center}
            .content{padding:20px}
            .info-box{background-color:#f8f9fc;border-left:4px solid #f6c23e;padding:15px;margin:15px 0}
            .indicator-box{background-color:#e8f4f8;border:2px solid #4e73df;border-radius:10px;padding:20px;margin:20px 0;text-align:center}
            .indicator-value{font-size:48px;font-weight:bold;color:#4e73df}
            table{width:100%;border-collapse:collapse;margin-top:20px;font-size:12px}
            th{background-color:#f6c23e;color:#333;padding:10px;text-align:left}
            td{padding:8px;border-bottom:1px solid #ddd}
            tr:nth-child(even){background-color:#f8f9fc}
            .footer{background-color:#f8f9fc;padding:20px;text-align:center;margin-top:30px}
            .badge{display:inline-block;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:bold}
            .badge-cumple{background-color:#28a745;color:white}
            .badge-no-cumple{background-color:#dc3545;color:white}
            .badge-no-aplica{background-color:#6c757d;color:white}
        </style></head><body>
        <div class='header'><h1>Evaluación de Estándares Mínimos SG-SST - {$anio}</h1><p>Socialización según Decreto 1072 de 2015 y Resolución 0312 de 2019</p></div>
        <div class='content'>
            <div class='info-box'>
                <p><strong>Cliente:</strong> " . esc($cliente['nombre_cliente']) . "</p>
                <p><strong>NIT:</strong> " . esc($cliente['nit_cliente'] ?? 'No registrado') . "</p>
                <p><strong>Consultor:</strong> {$nombreConsultor}</p>
                <p><strong>Frecuencia:</strong> {$frecuenciaVisitas}</p>
                <p><strong>Fecha:</strong> {$fechaActual}</p>
            </div>
            <div class='indicator-box'>
                <h2>Indicador de Cumplimiento General</h2>
                <div class='indicator-value'>{$indicadores['indicador_general']}%</div>
                <p>Puntaje: {$indicadores['sum_puntaje']} / {$indicadores['sum_valor']}</p>
            </div>
            <h2>Resumen</h2>
            <table><tr>
                <td style='text-align:center;background-color:#d4edda'><strong>Cumple</strong><br><span style='font-size:24px'>{$indicadores['count_cumple']}</span></td>
                <td style='text-align:center;background-color:#f8d7da'><strong>No Cumple</strong><br><span style='font-size:24px'>{$indicadores['count_no_cumple']}</span></td>
                <td style='text-align:center;background-color:#e2e3e5'><strong>No Aplica</strong><br><span style='font-size:24px'>{$indicadores['count_no_aplica']}</span></td>
                <td style='text-align:center;background-color:#cce5ff'><strong>Total</strong><br><span style='font-size:24px'>{$indicadores['total_items']}</span></td>
            </tr></table>
            <h2>Por Estándar</h2>
            <table><thead><tr><th>Estándar</th><th>Cumple</th><th>No Cumple</th><th>No Aplica</th><th>Total</th></tr></thead><tbody>";

        foreach ($porEstandar as $estandar => $datos) {
            $html .= "<tr>
                <td>" . esc($estandar) . "</td>
                <td style='text-align:center;color:#28a745'><strong>{$datos['cumple']}</strong></td>
                <td style='text-align:center;color:#dc3545'><strong>{$datos['no_cumple']}</strong></td>
                <td style='text-align:center;color:#6c757d'><strong>{$datos['no_aplica']}</strong></td>
                <td style='text-align:center'><strong>{$datos['total']}</strong></td>
            </tr>";
        }

        $html .= "</tbody></table>
            <h2>Detalle</h2>
            <table><thead><tr><th>Ciclo</th><th>Estándar</th><th>Item</th><th>Evaluación</th><th>Valor</th><th>Puntaje</th></tr></thead><tbody>";

        foreach ($evaluaciones as $ev) {
            $evaluacion = $ev['evaluacion_inicial'] ?? '-';
            $badgeClass = 'badge-no-aplica';
            if ($evaluacion === 'CUMPLE TOTALMENTE') $badgeClass = 'badge-cumple';
            elseif ($evaluacion === 'NO CUMPLE') $badgeClass = 'badge-no-cumple';

            $html .= "<tr>
                <td>" . esc($ev['ciclo'] ?? '') . "</td>
                <td>" . esc($ev['estandar'] ?? '') . "</td>
                <td>" . esc($ev['item_del_estandar'] ?? '') . "</td>
                <td><span class='badge {$badgeClass}'>{$evaluacion}</span></td>
                <td>" . esc($ev['valor'] ?? 0) . "</td>
                <td>" . esc($ev['puntaje_cuantitativo'] ?? 0) . "</td>
            </tr>";
        }

        $html .= "</tbody></table>
            <div class='footer'>
                <p><strong>Cycloid Talent SAS</strong></p>
                <p>NIT: 901.653.912</p>
                <p style='font-size: 11px; color: #666;'>Generado automáticamente por EnterpriseSST</p>
            </div>
        </div></body></html>";

        return $html;
    }
}
