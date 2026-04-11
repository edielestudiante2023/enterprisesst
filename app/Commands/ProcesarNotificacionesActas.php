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
 * Comando para procesar notificaciones diarias de actas
 *
 * Ejecutar con: php spark actas:notificaciones
 *
 * Tareas (en orden):
 * 1. Generar recordatorios de firma pendiente (diarios)
 * 2. Generar alertas de actas faltantes del mes
 * 3. Generar alertas de tareas vencidas/por vencer
 * 4. Enviar TODAS las notificaciones pendientes (incluye las recién generadas)
 * 5. Resumen semanal a consultores (solo lunes)
 *
 * CRON recomendado (7:00 AM diario):
 * 0 7 * * * cd /ruta/al/proyecto && php spark actas:notificaciones >> writable/logs/cron-actas.log 2>&1
 */
class ProcesarNotificacionesActas extends BaseCommand
{
    protected $group = 'Actas';
    protected $name = 'actas:notificaciones';
    protected $description = 'Procesa y envia notificaciones diarias de actas (firmas, tareas, alertas)';
    protected $usage = 'actas:notificaciones [opciones]';
    protected $arguments = [];
    protected $options = [
        '--enviar' => 'Solo enviar notificaciones pendientes (no genera nuevas)',
        '--alertas' => 'Solo generar alertas de actas y tareas',
        '--firmas' => 'Solo generar recordatorios de firma',
        '--resumen' => 'Solo enviar resumen semanal (ignora dia)',
        '--todo' => 'Ejecutar todas las tareas',
        '--test' => 'Modo de prueba (no envia emails reales)'
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
    protected $generados = 0;

    // Config SendGrid desde .env
    protected $sendgridApiKey;
    protected $sendgridFromEmail;
    protected $sendgridFromName;

    public function run(array $params)
    {
        $this->notificacionModel = new ActaNotificacionModel();
        $this->actaModel = new ActaModel();
        $this->asistentesModel = new ActaAsistenteModel();
        $this->compromisosModel = new ActaCompromisoModel();
        $this->comiteModel = new ComiteModel();
        $this->clienteModel = new ClientModel();

        // Cargar config SendGrid desde .env
        $this->sendgridApiKey = getenv('SENDGRID_API_KEY') ?: '';
        $this->sendgridFromEmail = getenv('SENDGRID_FROM_EMAIL') ?: 'notificacion.cycloidtalent@cycloidtalent.com';
        $this->sendgridFromName = getenv('SENDGRID_FROM_NAME') ?: 'Enterprise SST';

        $this->modoTest = CLI::getOption('test') !== null;

        if ($this->modoTest) {
            CLI::write('MODO DE PRUEBA - No se enviaran emails reales', 'yellow');
        }

        // Determinar qué ejecutar
        $soloEnviar = CLI::getOption('enviar') !== null;
        $soloAlertas = CLI::getOption('alertas') !== null;
        $soloFirmas = CLI::getOption('firmas') !== null;
        $soloResumen = CLI::getOption('resumen') !== null;
        $todo = CLI::getOption('todo') !== null || (!$soloEnviar && !$soloAlertas && !$soloFirmas && !$soloResumen);

        CLI::write('===========================================', 'cyan');
        CLI::write(' Notificaciones de Actas - ' . date('d/m/Y H:i'), 'cyan');
        CLI::write('===========================================', 'cyan');
        CLI::newLine();

        // FASE 1: GENERAR notificaciones (encolar en tbl_actas_notificaciones)
        if ($todo || $soloFirmas) {
            $this->generarRecordatoriosFirma();
        }

        if ($todo || $soloAlertas) {
            $this->generarAlertasActasFaltantes();
            $this->generarAlertasTareasVencidas();
        }

        if ($todo || $soloResumen) {
            // Resumen semanal solo lunes (o forzar con --resumen)
            if ($soloResumen || date('N') == 1) {
                $this->enviarResumenSemanal();
            }
        }

        // FASE 2: ENVIAR todas las notificaciones pendientes (incluye las recién generadas)
        if ($todo || $soloEnviar || $soloFirmas || $soloAlertas) {
            $this->enviarNotificacionesPendientes();
        }

        // Resumen final
        CLI::newLine();
        CLI::write('===========================================', 'cyan');
        CLI::write(" Generados: {$this->generados} | Enviados: {$this->enviados} | Errores: {$this->errores}", 'cyan');
        CLI::write('===========================================', 'cyan');
    }

    /**
     * Generar recordatorios de firma pendiente (diarios, cada 24h)
     */
    protected function generarRecordatoriosFirma(): void
    {
        CLI::write('[FIRMAS] Verificando firmas pendientes...', 'light_gray');

        $pendientes = $this->asistentesModel->getPendientesRecordatorio(24);

        if (empty($pendientes)) {
            CLI::write('  No hay firmas pendientes de recordatorio', 'light_gray');
            return;
        }

        CLI::write("  Encontrados: " . count($pendientes) . " asistente(s) sin firmar", 'light_gray');

        foreach ($pendientes as $asistente) {
            if (empty($asistente['email'])) {
                CLI::write("  [SKIP] {$asistente['nombre_completo']} - sin email", 'yellow');
                continue;
            }

            $acta = $this->actaModel->find($asistente['id_acta']);
            if (!$acta) continue;

            // Calcular días desde la primera notificación
            $diasPendiente = 0;
            if (!empty($asistente['notificacion_enviada_at'])) {
                $diasPendiente = (int) ceil((time() - strtotime($asistente['notificacion_enviada_at'])) / 86400);
            }

            $this->notificacionModel->programarRecordatorioFirma(
                $asistente['id_acta'],
                $asistente['id_asistente'],
                $asistente['email'],
                $asistente['nombre_completo'],
                $acta['id_cliente'],
                $asistente['numero_acta'],
                $asistente['token_firma'] ?? '',
                $diasPendiente
            );

            $this->asistentesModel->update($asistente['id_asistente'], [
                'recordatorio_enviado_at' => date('Y-m-d H:i:s')
            ]);

            $this->generados++;
            CLI::write("  [GENERADO] {$asistente['email']} - Acta {$asistente['numero_acta']} (dia {$diasPendiente})", 'cyan');
        }
    }

    /**
     * Generar alertas de actas faltantes del mes (después del día 10)
     */
    protected function generarAlertasActasFaltantes(): void
    {
        $diaActual = (int) date('j');

        if ($diaActual < 10) {
            return;
        }

        CLI::write('[ALERTAS] Verificando actas faltantes del mes...', 'light_gray');

        $clientes = $this->clienteModel->where('estado', 'activo')->findAll();

        foreach ($clientes as $cliente) {
            $sinActa = $this->actaModel->getComitesSinActaMes($cliente['id_cliente']);

            foreach ($sinActa as $comite) {
                // No duplicar alerta del mismo mes
                $yaEnviada = $this->notificacionModel
                    ->where('id_cliente', $cliente['id_cliente'])
                    ->where('tipo', 'alerta_sin_acta')
                    ->where('MONTH(created_at)', date('m'))
                    ->where('YEAR(created_at)', date('Y'))
                    ->first();

                if ($yaEnviada) continue;

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

                        $this->generados++;
                        CLI::write("  [GENERADO] Sin acta {$comite['codigo']} - {$cliente['nombre_cliente']}", 'yellow');
                    }
                }
            }
        }
    }

    /**
     * Generar alertas de tareas vencidas y próximas a vencer
     */
    protected function generarAlertasTareasVencidas(): void
    {
        CLI::write('[TAREAS] Verificando tareas vencidas...', 'light_gray');

        // Marcar tareas vencidas automáticamente
        $this->compromisosModel->marcarVencidos();

        // Tareas vencidas sin notificación reciente (cada 7 días)
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

                $this->generados++;
                CLI::write("  [GENERADO] Vencida: {$tarea['responsable_email']} - {$tarea['nombre_cliente']}", 'red');
            }
        }

        // Tareas próximas a vencer (7 días, notificar cada 3 días)
        $proximasVencer = $this->compromisosModel->getProximosAVencer(7);

        foreach ($proximasVencer as $tarea) {
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

                $this->generados++;
                CLI::write("  [GENERADO] Por vencer: {$tarea['responsable_email']}", 'yellow');
            }
        }
    }

    /**
     * Enviar TODAS las notificaciones pendientes de la cola
     * Se ejecuta DESPUÉS de generar, para incluir las recién creadas
     */
    protected function enviarNotificacionesPendientes(): void
    {
        CLI::newLine();
        CLI::write('[ENVIO] Procesando cola de notificaciones...', 'light_gray');

        $pendientes = $this->notificacionModel->getPendientesEnvio(100);

        if (empty($pendientes)) {
            CLI::write('  Cola vacía - nada que enviar', 'light_gray');
            return;
        }

        CLI::write("  En cola: " . count($pendientes) . " notificación(es)", 'light_gray');

        // Cache de consultores por cliente para no repetir queries
        $cacheConsultores = [];

        foreach ($pendientes as $notif) {
            // Obtener CC del consultor para notificaciones de compromisos
            $ccEmail = null;
            if (in_array($notif['tipo'], ['tarea_vencida', 'tarea_por_vencer']) && !empty($notif['id_cliente'])) {
                if (!array_key_exists($notif['id_cliente'], $cacheConsultores)) {
                    $cliente = $this->clienteModel->find($notif['id_cliente']);
                    $cacheConsultores[$notif['id_cliente']] = null;
                    if (!empty($cliente['id_consultor'])) {
                        $consultor = (new \App\Models\ConsultantModel())->find($cliente['id_consultor']);
                        $cacheConsultores[$notif['id_cliente']] = $consultor['correo_consultor'] ?? null;
                    }
                }
                $ccEmail = $cacheConsultores[$notif['id_cliente']];
            }

            $resultado = $this->enviarEmail(
                $notif['destinatario_email'],
                $notif['destinatario_nombre'],
                $notif['asunto'],
                $notif['cuerpo'],
                $notif['tipo'],
                $ccEmail
            );

            if ($resultado) {
                $this->notificacionModel->marcarEnviada($notif['id_notificacion']);
                $this->enviados++;
                CLI::write("  [OK] {$notif['tipo']} -> {$notif['destinatario_email']}", 'green');
            } else {
                $this->notificacionModel->marcarFallida($notif['id_notificacion'], 'Error de envio SendGrid');
                $this->errores++;
                CLI::write("  [ERROR] {$notif['tipo']} -> {$notif['destinatario_email']}", 'red');
            }
        }
    }

    /**
     * Enviar resumen semanal a consultores (solo lunes o con --resumen)
     */
    protected function enviarResumenSemanal(): void
    {
        CLI::write('[RESUMEN] Generando resumenes semanales...', 'light_gray');

        $consultorModel = new \App\Models\ConsultantModel();
        $consultores = $consultorModel->findAll();

        foreach ($consultores as $consultor) {
            if (empty($consultor['correo_consultor'])) continue;

            $clientes = $this->clienteModel
                ->where('id_consultor', $consultor['id_consultor'])
                ->where('estado', 'activo')
                ->findAll();

            if (empty($clientes)) continue;

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

            if ($resumenTotal['actas_pendientes'] > 0 || $resumenTotal['tareas_vencidas'] > 0) {
                $this->notificacionModel->programarResumenSemanal(
                    $clientes[0]['id_cliente'],
                    $consultor['correo_consultor'],
                    $consultor['nombre_consultor'],
                    $resumenTotal
                );

                $this->generados++;
                CLI::write("  [GENERADO] Resumen: {$consultor['correo_consultor']}", 'light_blue');
            }
        }
    }

    /**
     * Enviar email usando SendGrid API
     */
    protected function enviarEmail(string $email, string $nombre, string $asunto, string $cuerpo, string $tipo, ?string $ccEmail = null): bool
    {
        if ($this->modoTest) {
            CLI::write("    [TEST] -> {$email}: {$asunto}", 'light_gray');
            return true;
        }

        if (empty($this->sendgridApiKey)) {
            CLI::write("    [WARN] SENDGRID_API_KEY no configurada en .env", 'yellow');
            return false;
        }

        try {
            $personalization = [
                'to' => [['email' => $email, 'name' => $nombre]],
                'subject' => $asunto
            ];
            if (!empty($ccEmail) && $ccEmail !== $email) {
                $personalization['cc'] = [['email' => $ccEmail]];
            }

            $emailData = [
                'personalizations' => [$personalization],
                'from' => [
                    'email' => $this->sendgridFromEmail,
                    'name' => $this->sendgridFromName
                ],
                'content' => [[
                    'type' => 'text/html',
                    'value' => $this->generarHtmlEmail($nombre, $asunto, $cuerpo, $tipo)
                ]],
                'tracking_settings' => [
                    'click_tracking' => [
                        'enable' => false,
                        'enable_text' => false
                    ]
                ]
            ];

            $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($emailData),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->sendgridApiKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 30
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if (!empty($curlError)) {
                CLI::write("    [CURL] {$curlError}", 'red');
                return false;
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                CLI::write("    [SENDGRID] HTTP {$httpCode}: {$response}", 'red');
                return false;
            }

            return true;

        } catch (\Exception $e) {
            CLI::write("    [EXCEPTION] " . $e->getMessage(), 'red');
            return false;
        }
    }

    /**
     * Generar HTML del email con diseño profesional
     */
    protected function generarHtmlEmail(string $nombre, string $asunto, string $cuerpo, string $tipo): string
    {
        $color = match($tipo) {
            'firma_solicitada', 'firma_recordatorio' => '#3B82F6',
            'firma_completada' => '#10B981',
            'acta_firmada_completa' => '#059669',
            'tarea_vencida' => '#EF4444',
            'tarea_por_vencer' => '#F59E0B',
            'tarea_asignada' => '#8B5CF6',
            'alerta_sin_acta' => '#F97316',
            'resumen_semanal' => '#0EA5E9',
            default => '#6B7280'
        };

        $iconoTipo = match($tipo) {
            'firma_solicitada', 'firma_recordatorio' => '✍️',
            'firma_completada', 'acta_firmada_completa' => '✅',
            'tarea_vencida' => '🚨',
            'tarea_por_vencer' => '⏰',
            'tarea_asignada' => '📋',
            'alerta_sin_acta' => '⚠️',
            'resumen_semanal' => '📊',
            default => '📬'
        };

        $fecha = date('d/m/Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f3f4f6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: linear-gradient(135deg, {$color} 0%, {$color}dd 100%); padding: 25px; text-align: center; border-radius: 12px 12px 0 0;">
            <div style="font-size: 32px; margin-bottom: 8px;">{$iconoTipo}</div>
            <h2 style="margin: 0; color: white; font-size: 18px;">{$asunto}</h2>
            <p style="margin: 5px 0 0; color: rgba(255,255,255,0.8); font-size: 12px;">{$fecha}</p>
        </div>
        <div style="background: white; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <p style="margin-top: 0;">Hola <strong>{$nombre}</strong>,</p>
            <div style="margin: 20px 0;">{$cuerpo}</div>
            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;">
            <p style="margin-bottom: 0; color: #6B7280; font-size: 13px;">
                Saludos,<br>
                <strong>Enterprise SST - EnterpriseSST</strong>
            </p>
        </div>
        <div style="text-align: center; margin-top: 15px; color: #9CA3AF; font-size: 11px;">
            <p>Este es un mensaje automático del sistema de gestión SST.<br>Por favor no responda a este correo.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
