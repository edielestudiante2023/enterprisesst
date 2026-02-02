<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ActaNotificacionModel;
use App\Models\ActaModel;
use App\Models\ActaAsistenteModel;
use App\Models\ActaCompromisoModel;
use App\Models\ComiteModel;
use App\Models\ClientModel;

/**
 * Comando para procesar notificaciones de actas
 *
 * Ejecutar con: php spark actas:notificaciones
 *
 * Tareas:
 * 1. Enviar notificaciones pendientes (firma, tareas, etc.)
 * 2. Generar alertas de actas faltantes del mes
 * 3. Generar alertas de tareas vencidas
 * 4. Enviar resumen semanal a consultores
 *
 * Para CRON (ejecutar cada hora):
 * 0 * * * * cd /ruta/al/proyecto && php spark actas:notificaciones >> /var/log/actas-notif.log 2>&1
 */
class ProcesarNotificacionesActas extends BaseCommand
{
    protected $group = 'Actas';
    protected $name = 'actas:notificaciones';
    protected $description = 'Procesa y envia notificaciones de actas (firmas, tareas, alertas)';
    protected $usage = 'actas:notificaciones [opciones]';
    protected $arguments = [];
    protected $options = [
        '--enviar' => 'Enviar notificaciones pendientes',
        '--alertas' => 'Generar alertas de actas y tareas',
        '--resumen' => 'Enviar resumen semanal (solo lunes)',
        '--todo' => 'Ejecutar todas las tareas',
        '--test' => 'Modo de prueba (no envia emails)'
    ];

    protected $notificacionModel;
    protected $actaModel;
    protected $asistentesModel;
    protected $compromisosModel;
    protected $comiteModel;
    protected $clienteModel;

    protected $modoTest = false;
    protected $enviados = 0;
    protected $errores = 0;

    public function run(array $params)
    {
        $this->notificacionModel = new ActaNotificacionModel();
        $this->actaModel = new ActaModel();
        $this->asistentesModel = new ActaAsistenteModel();
        $this->compromisosModel = new ActaCompromisoModel();
        $this->comiteModel = new ComiteModel();
        $this->clienteModel = new ClientModel();

        $this->modoTest = CLI::getOption('test') !== null;

        if ($this->modoTest) {
            CLI::write('MODO DE PRUEBA - No se enviaran emails reales', 'yellow');
        }

        $todo = CLI::getOption('todo') !== null;
        $ejecutarEnviar = $todo || CLI::getOption('enviar') !== null;
        $ejecutarAlertas = $todo || CLI::getOption('alertas') !== null;
        $ejecutarResumen = $todo || CLI::getOption('resumen') !== null;

        // Si no se especifica ninguna opcion, ejecutar todo
        if (!$ejecutarEnviar && !$ejecutarAlertas && !$ejecutarResumen) {
            $ejecutarEnviar = true;
            $ejecutarAlertas = true;
            $ejecutarResumen = true;
        }

        CLI::write('===========================================', 'cyan');
        CLI::write(' Procesador de Notificaciones de Actas', 'cyan');
        CLI::write('===========================================', 'cyan');
        CLI::newLine();

        // 1. Enviar notificaciones pendientes
        if ($ejecutarEnviar) {
            $this->enviarNotificacionesPendientes();
        }

        // 2. Generar alertas
        if ($ejecutarAlertas) {
            $this->generarAlertasActasFaltantes();
            $this->generarAlertasTareasVencidas();
            $this->generarRecordatoriosFirma();
        }

        // 3. Resumen semanal (solo lunes)
        if ($ejecutarResumen && date('N') == 1) {
            $this->enviarResumenSemanal();
        }

        // Resumen final
        CLI::newLine();
        CLI::write('===========================================', 'cyan');
        CLI::write(" Completado: {$this->enviados} enviados, {$this->errores} errores", 'cyan');
        CLI::write('===========================================', 'cyan');
    }

    /**
     * Enviar notificaciones pendientes de la cola
     */
    protected function enviarNotificacionesPendientes(): void
    {
        CLI::write('Procesando notificaciones pendientes...', 'light_gray');

        $pendientes = $this->notificacionModel->getPendientesEnvio(100);

        if (empty($pendientes)) {
            CLI::write('  No hay notificaciones pendientes', 'light_gray');
            return;
        }

        CLI::write("  Encontradas: " . count($pendientes), 'light_gray');

        foreach ($pendientes as $notif) {
            $resultado = $this->enviarEmail(
                $notif['destinatario_email'],
                $notif['destinatario_nombre'],
                $notif['asunto'],
                $notif['cuerpo'],
                $notif['tipo']
            );

            if ($resultado) {
                $this->notificacionModel->marcarEnviada($notif['id_notificacion']);
                $this->enviados++;
                CLI::write("  [OK] {$notif['tipo']} -> {$notif['destinatario_email']}", 'green');
            } else {
                $this->notificacionModel->marcarFallida($notif['id_notificacion'], 'Error de envio');
                $this->errores++;
                CLI::write("  [ERROR] {$notif['tipo']} -> {$notif['destinatario_email']}", 'red');
            }
        }
    }

    /**
     * Generar alertas de actas faltantes del mes
     */
    protected function generarAlertasActasFaltantes(): void
    {
        $diaActual = (int) date('j');
        $diaMes = 10; // Dia limite por defecto

        // Solo generar alertas despues del dia 10
        if ($diaActual < $diaMes) {
            return;
        }

        CLI::write('Verificando actas faltantes del mes...', 'light_gray');

        $clientes = $this->clienteModel->where('estado', 'activo')->findAll();

        foreach ($clientes as $cliente) {
            $sinActa = $this->actaModel->getComitesSinActaMes($cliente['id_cliente']);

            foreach ($sinActa as $comite) {
                // Verificar si ya se envio alerta este mes
                $yaEnviada = $this->notificacionModel
                    ->where('id_cliente', $cliente['id_cliente'])
                    ->where('tipo', 'alerta_sin_acta')
                    ->where('MONTH(created_at)', date('m'))
                    ->where('YEAR(created_at)', date('Y'))
                    ->first();

                if ($yaEnviada) {
                    continue;
                }

                // Obtener consultor del cliente
                if (!empty($cliente['id_consultor'])) {
                    $consultorModel = new \App\Models\ConsultantModel();
                    $consultor = $consultorModel->find($cliente['id_consultor']);

                    if ($consultor && !empty($consultor['correo_consultor'])) {
                        $this->notificacionModel->programarAlertaSinActa(
                            $cliente['id_cliente'],
                            $consultor['correo_consultor'],
                            $consultor['nombre_consultor'],
                            $comite['codigo'],
                            (int) date('m'),
                            (int) date('Y')
                        );

                        CLI::write("  [ALERTA] Sin acta {$comite['codigo']} - {$cliente['nombre_cliente']}", 'yellow');
                    }
                }
            }
        }
    }

    /**
     * Generar alertas de tareas vencidas
     */
    protected function generarAlertasTareasVencidas(): void
    {
        CLI::write('Verificando tareas vencidas...', 'light_gray');

        // Marcar tareas vencidas automaticamente
        $this->compromisosModel->marcarVencidos();

        // Obtener tareas vencidas sin notificacion reciente
        $vencidas = $this->compromisosModel
            ->select('tbl_acta_compromisos.*, tbl_cliente.nombre_cliente')
            ->join('tbl_cliente', 'tbl_cliente.id_cliente = tbl_acta_compromisos.id_cliente')
            ->where('tbl_acta_compromisos.estado', 'vencido')
            ->where('(tbl_acta_compromisos.ultima_notificacion_at IS NULL OR tbl_acta_compromisos.ultima_notificacion_at < DATE_SUB(NOW(), INTERVAL 7 DAY))')
            ->findAll();

        foreach ($vencidas as $tarea) {
            if (!empty($tarea['responsable_email'])) {
                $this->notificacionModel->programar([
                    'id_cliente' => $tarea['id_cliente'],
                    'tipo' => 'tarea_vencida',
                    'id_compromiso' => $tarea['id_compromiso'],
                    'destinatario_email' => $tarea['responsable_email'],
                    'destinatario_nombre' => $tarea['responsable_nombre'],
                    'destinatario_tipo' => 'responsable',
                    'asunto' => "URGENTE: Tarea vencida desde " . date('d/m/Y', strtotime($tarea['fecha_vencimiento'])),
                    'cuerpo' => "La tarea '{$tarea['descripcion']}' esta vencida. Por favor actualice su estado."
                ]);

                $this->compromisosModel->update($tarea['id_compromiso'], [
                    'ultima_notificacion_at' => date('Y-m-d H:i:s'),
                    'total_notificaciones' => ($tarea['total_notificaciones'] ?? 0) + 1
                ]);

                CLI::write("  [VENCIDA] {$tarea['responsable_email']} - {$tarea['nombre_cliente']}", 'red');
            }
        }

        // Alertas de tareas proximas a vencer
        $proximasVencer = $this->compromisosModel->getProximosAVencer(7);

        foreach ($proximasVencer as $tarea) {
            // Solo notificar si no se ha notificado en los ultimos 3 dias
            if (!empty($tarea['ultima_notificacion_at']) &&
                strtotime($tarea['ultima_notificacion_at']) > strtotime('-3 days')) {
                continue;
            }

            if (!empty($tarea['responsable_email'])) {
                $this->notificacionModel->programarTareaPorVencer(
                    $tarea['id_compromiso'],
                    $tarea['responsable_email'],
                    $tarea['responsable_nombre'],
                    $tarea['id_cliente'],
                    $tarea['descripcion'],
                    date('d/m/Y', strtotime($tarea['fecha_vencimiento']))
                );

                $this->compromisosModel->update($tarea['id_compromiso'], [
                    'ultima_notificacion_at' => date('Y-m-d H:i:s')
                ]);

                CLI::write("  [POR VENCER] {$tarea['responsable_email']}", 'yellow');
            }
        }
    }

    /**
     * Generar recordatorios de firma pendiente
     */
    protected function generarRecordatoriosFirma(): void
    {
        CLI::write('Verificando firmas pendientes...', 'light_gray');

        $pendientes = $this->asistentesModel->getPendientesRecordatorio(48);

        foreach ($pendientes as $asistente) {
            if (empty($asistente['email'])) {
                continue;
            }

            $acta = $this->actaModel->find($asistente['id_acta']);

            $this->notificacionModel->programarRecordatorioFirma(
                $asistente['id_acta'],
                $asistente['id_asistente'],
                $asistente['email'],
                $asistente['nombre_completo'],
                $acta['id_cliente'],
                $asistente['numero_acta']
            );

            $this->asistentesModel->update($asistente['id_asistente'], [
                'recordatorio_enviado_at' => date('Y-m-d H:i:s')
            ]);

            CLI::write("  [RECORDATORIO FIRMA] {$asistente['email']}", 'cyan');
        }
    }

    /**
     * Enviar resumen semanal a consultores (solo lunes)
     */
    protected function enviarResumenSemanal(): void
    {
        CLI::write('Generando resumenes semanales...', 'light_gray');

        // Obtener consultores activos
        $consultorModel = new \App\Models\ConsultantModel();
        $consultores = $consultorModel->findAll();

        foreach ($consultores as $consultor) {
            if (empty($consultor['correo_consultor'])) {
                continue;
            }

            // Obtener clientes del consultor
            $clientes = $this->clienteModel
                ->where('id_consultor', $consultor['id_consultor'])
                ->where('estado', 'activo')
                ->findAll();

            if (empty($clientes)) {
                continue;
            }

            $resumenTotal = [
                'actas_pendientes' => 0,
                'tareas_vencidas' => 0,
                'tareas_por_vencer' => 0
            ];

            foreach ($clientes as $cliente) {
                $resumenTotal['actas_pendientes'] += count($this->actaModel->getPendientesFirma($cliente['id_cliente']));
                $resumenTotal['tareas_vencidas'] += count($this->compromisosModel->getVencidos($cliente['id_cliente']));
                $resumenTotal['tareas_por_vencer'] += count($this->compromisosModel->getProximosAVencer(7, $cliente['id_cliente']));
            }

            // Solo enviar si hay algo que reportar
            if ($resumenTotal['actas_pendientes'] > 0 || $resumenTotal['tareas_vencidas'] > 0) {
                $this->notificacionModel->programarResumenSemanal(
                    $clientes[0]['id_cliente'], // Usar primer cliente como referencia
                    $consultor['correo_consultor'],
                    $consultor['nombre_consultor'],
                    $resumenTotal
                );

                CLI::write("  [RESUMEN] {$consultor['correo_consultor']}", 'light_blue');
            }
        }
    }

    /**
     * Enviar email usando SendGrid
     */
    protected function enviarEmail(string $email, string $nombre, string $asunto, string $cuerpo, string $tipo): bool
    {
        if ($this->modoTest) {
            CLI::write("    [TEST] Email a {$email}: {$asunto}", 'light_gray');
            return true;
        }

        try {
            $apiKey = getenv('SENDGRID_API_KEY') ?: '';

            if (empty($apiKey)) {
                CLI::write("    [WARN] SENDGRID_API_KEY no configurada", 'yellow');
                return false;
            }

            $emailData = [
                'personalizations' => [[
                    'to' => [['email' => $email, 'name' => $nombre]],
                    'subject' => $asunto
                ]],
                'from' => [
                    'email' => 'notificaciones@enterprisesst.com',
                    'name' => 'Enterprise SST'
                ],
                'content' => [[
                    'type' => 'text/html',
                    'value' => $this->generarHtmlEmail($nombre, $asunto, $cuerpo, $tipo)
                ]]
            ];

            $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($emailData),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json'
                ]
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $httpCode >= 200 && $httpCode < 300;

        } catch (\Exception $e) {
            CLI::write("    [ERROR] " . $e->getMessage(), 'red');
            return false;
        }
    }

    /**
     * Generar HTML del email
     */
    protected function generarHtmlEmail(string $nombre, string $asunto, string $cuerpo, string $tipo): string
    {
        $color = match($tipo) {
            'firma_solicitada', 'firma_recordatorio' => '#667eea',
            'tarea_vencida' => '#dc3545',
            'tarea_por_vencer' => '#ffc107',
            'alerta_sin_acta' => '#fd7e14',
            'resumen_semanal' => '#17a2b8',
            default => '#6c757d'
        };

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: {$color}; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
        .footer { text-align: center; margin-top: 20px; color: #6c757d; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">{$asunto}</h2>
        </div>
        <div class="content">
            <p>Hola {$nombre},</p>
            <p>{$cuerpo}</p>
            <p>Saludos,<br>Enterprise SST</p>
        </div>
        <div class="footer">
            <p>Este es un mensaje automatico. Por favor no responda a este correo.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
