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
 * Comando para procesar notificaciones diarias y semanales de actas
 *
 * Ejecutar con: php spark actas:notificaciones
 *
 * Modo diario (sin flags):
 * 1. Recordatorios de firma pendiente
 * 2. Alertas de actas faltantes del mes (solo entre dia 25 y fin de mes)
 * 3. Alertas de tareas proximas a vencer (7 dias)
 * 4. Enviar notificaciones pendientes en cola
 *
 * Modo semanal (--resumen):
 * - Email consolidado por comite con tareas vencidas
 * - TO: presidente + secretario | CC: resto miembros + consultor
 *
 * CRON recomendado:
 * 0 7 * * * cd /ruta && php spark actas:notificaciones >> writable/logs/cron-actas.log 2>&1
 * 0 8 * * 1 cd /ruta && php spark actas:notificaciones --resumen >> writable/logs/cron-actas-resumen.log 2>&1
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

        if ($soloResumen) {
            $this->enviarConsolidadoSemanalVencidas();
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
     * Generar alertas de actas faltantes del mes (entre dia 25 y fin de mes)
     */
    protected function generarAlertasActasFaltantes(): void
    {
        $diaActual = (int) date('j');

        if ($diaActual < 25) {
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
     * Generar alertas de tareas proximas a vencer (las vencidas se manejan en el cron semanal --resumen)
     */
    protected function generarAlertasTareasVencidas(): void
    {
        CLI::write('[TAREAS] Verificando tareas proximas a vencer...', 'light_gray');

        // Marcar tareas vencidas automaticamente (cambia estado pero NO notifica aqui)
        $this->compromisosModel->marcarVencidos();

        // Tareas proximas a vencer (7 dias, notificar cada 3 dias)
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
     * Enviar email consolidado semanal por comite con tareas vencidas
     * TO: presidente + secretario | CC: resto miembros + consultor del cliente
     */
    protected function enviarConsolidadoSemanalVencidas(): void
    {
        CLI::write('[CONSOLIDADO SEMANAL] Generando emails de tareas vencidas por comite...', 'light_gray');

        // Marcar vencidos por si el cron diario aun no corrio hoy
        $this->compromisosModel->marcarVencidos();

        $miembroModel = new \App\Models\MiembroComiteModel();
        $consultorModel = new \App\Models\ConsultantModel();
        $clientes = $this->clienteModel->where('estado', 'activo')->findAll();

        foreach ($clientes as $cliente) {
            $comites = $this->comiteModel->getByCliente($cliente['id_cliente']);
            if (empty($comites)) continue;

            // Consultor del cliente (CC)
            $emailConsultor = null;
            $nombreConsultor = null;
            if (!empty($cliente['id_consultor'])) {
                $consultor = $consultorModel->find($cliente['id_consultor']);
                if ($consultor && !empty($consultor['correo_consultor'])) {
                    $emailConsultor = $consultor['correo_consultor'];
                    $nombreConsultor = $consultor['nombre_consultor'] ?? '';
                }
            }

            foreach ($comites as $comite) {
                // Compromisos vencidos del comite
                $vencidos = $this->compromisosModel
                    ->select('tbl_acta_compromisos.*, tbl_actas.numero_acta')
                    ->join('tbl_actas', 'tbl_actas.id_acta = tbl_acta_compromisos.id_acta', 'left')
                    ->where('tbl_acta_compromisos.id_comite', $comite['id_comite'])
                    ->whereIn('tbl_acta_compromisos.estado', ['pendiente', 'en_proceso', 'vencido'])
                    ->where('tbl_acta_compromisos.fecha_vencimiento <', date('Y-m-d'))
                    ->orderBy('tbl_acta_compromisos.fecha_vencimiento', 'ASC')
                    ->findAll();

                if (empty($vencidos)) continue;

                // Miembros del comite
                $miembros = $miembroModel->getActivosPorComite($comite['id_comite']);
                if (empty($miembros)) {
                    CLI::write("  [SKIP] Comite {$comite['codigo']} sin miembros - {$cliente['nombre_cliente']}", 'yellow');
                    continue;
                }

                $presidente = $miembroModel->getPresidente($comite['id_comite']);
                $secretario = $miembroModel->getSecretario($comite['id_comite']);

                // TO: presidente + secretario (con fallback al primer miembro si faltan ambos)
                $toList = [];
                if (!empty($presidente['email'])) {
                    $toList[] = ['email' => $presidente['email'], 'name' => $presidente['nombre_completo']];
                }
                if (!empty($secretario['email']) && (!$presidente || $secretario['email'] !== $presidente['email'])) {
                    $toList[] = ['email' => $secretario['email'], 'name' => $secretario['nombre_completo']];
                }
                if (empty($toList)) {
                    foreach ($miembros as $m) {
                        if (!empty($m['email'])) {
                            $toList[] = ['email' => $m['email'], 'name' => $m['nombre_completo']];
                            break;
                        }
                    }
                }
                if (empty($toList)) {
                    CLI::write("  [SKIP] Comite {$comite['codigo']} sin emails validos", 'yellow');
                    continue;
                }

                // CC: resto de miembros (excluyendo los que ya estan en TO) + consultor
                $emailsTo = array_column($toList, 'email');
                $ccList = [];
                foreach ($miembros as $m) {
                    if (!empty($m['email']) && !in_array($m['email'], $emailsTo) && !in_array($m['email'], array_column($ccList, 'email'))) {
                        $ccList[] = ['email' => $m['email'], 'name' => $m['nombre_completo']];
                    }
                }
                if (!empty($emailConsultor) && !in_array($emailConsultor, $emailsTo) && !in_array($emailConsultor, array_column($ccList, 'email'))) {
                    $ccList[] = ['email' => $emailConsultor, 'name' => $nombreConsultor];
                }

                // Construir cuerpo
                $tipoComite = $comite['codigo'] ?? 'COMITE';
                $asunto = "[{$tipoComite}] Tareas vencidas - {$cliente['nombre_cliente']}";
                $cuerpo = $this->construirCuerpoConsolidado($comite, $cliente, $vencidos);

                // Enviar
                $resultado = $this->enviarEmailDirecto($toList, $ccList, $asunto, $cuerpo, 'tarea_vencida');

                if ($resultado) {
                    // Marcar las tareas como notificadas
                    foreach ($vencidos as $v) {
                        $this->compromisosModel->update($v['id_compromiso'], [
                            'ultima_notificacion_at' => date('Y-m-d H:i:s'),
                            'total_notificaciones' => ($v['total_notificaciones'] ?? 0) + 1
                        ]);
                    }
                    $this->enviados++;
                    $destinatariosLog = implode(',', $emailsTo);
                    CLI::write("  [OK] {$tipoComite} {$cliente['nombre_cliente']} -> {$destinatariosLog} (" . count($vencidos) . " vencidas)", 'green');
                } else {
                    $this->errores++;
                    CLI::write("  [ERROR] {$tipoComite} {$cliente['nombre_cliente']}", 'red');
                }
            }
        }
    }

    /**
     * Construir HTML del cuerpo del email consolidado
     */
    protected function construirCuerpoConsolidado(array $comite, array $cliente, array $vencidos): string
    {
        $tipoComite = $comite['tipo_nombre'] ?? $comite['codigo'] ?? 'Comite';
        $hoy = date('Y-m-d');

        $filas = '';
        foreach ($vencidos as $v) {
            $diasVencido = (int) ((strtotime($hoy) - strtotime($v['fecha_vencimiento'])) / 86400);
            $fechaVenc = date('d/m/Y', strtotime($v['fecha_vencimiento']));
            $responsable = htmlspecialchars($v['responsable_nombre'] ?? '-', ENT_QUOTES, 'UTF-8');
            $descripcion = htmlspecialchars($v['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
            $numActa = htmlspecialchars($v['numero_acta'] ?? '-', ENT_QUOTES, 'UTF-8');

            $filas .= "<tr>"
                . "<td style=\"padding:8px;border:1px solid #e5e7eb;\">{$numActa}</td>"
                . "<td style=\"padding:8px;border:1px solid #e5e7eb;\">{$descripcion}</td>"
                . "<td style=\"padding:8px;border:1px solid #e5e7eb;\">{$responsable}</td>"
                . "<td style=\"padding:8px;border:1px solid #e5e7eb;text-align:center;\">{$fechaVenc}</td>"
                . "<td style=\"padding:8px;border:1px solid #e5e7eb;text-align:center;color:#dc2626;font-weight:bold;\">{$diasVencido} dias</td>"
                . "</tr>";
        }

        $total = count($vencidos);
        $tipoComiteHtml = htmlspecialchars($tipoComite, ENT_QUOTES, 'UTF-8');
        $clienteHtml = htmlspecialchars($cliente['nombre_cliente'], ENT_QUOTES, 'UTF-8');

        return "<p>Comite: <strong>{$tipoComiteHtml}</strong></p>"
            . "<p>Empresa: <strong>{$clienteHtml}</strong></p>"
            . "<p>El siguiente listado contiene <strong>{$total} compromiso(s) vencido(s)</strong> que requieren gestion:</p>"
            . "<table style=\"width:100%;border-collapse:collapse;font-size:13px;margin-top:10px;\">"
            . "<thead><tr style=\"background:#f3f4f6;\">"
            . "<th style=\"padding:8px;border:1px solid #e5e7eb;text-align:left;\">Acta</th>"
            . "<th style=\"padding:8px;border:1px solid #e5e7eb;text-align:left;\">Compromiso</th>"
            . "<th style=\"padding:8px;border:1px solid #e5e7eb;text-align:left;\">Responsable</th>"
            . "<th style=\"padding:8px;border:1px solid #e5e7eb;\">Vencimiento</th>"
            . "<th style=\"padding:8px;border:1px solid #e5e7eb;\">Dias vencido</th>"
            . "</tr></thead><tbody>{$filas}</tbody></table>"
            . "<p style=\"margin-top:15px;\">Por favor coordinen el cierre de estas actividades con los responsables indicados.</p>";
    }

    /**
     * Enviar email directo via SendGrid con multiples TO y CC (no usa la cola)
     * @param array $toList lista de [email, name]
     * @param array $ccList lista de [email, name]
     */
    protected function enviarEmailDirecto(array $toList, array $ccList, string $asunto, string $cuerpoHtml, string $tipo): bool
    {
        if ($this->modoTest) {
            $tos = implode(',', array_column($toList, 'email'));
            CLI::write("    [TEST] -> {$tos}: {$asunto}", 'light_gray');
            return true;
        }

        if (empty($this->sendgridApiKey)) {
            CLI::write("    [WARN] SENDGRID_API_KEY no configurada en .env", 'yellow');
            return false;
        }

        try {
            $personalization = [
                'to' => $toList,
                'subject' => $asunto
            ];
            if (!empty($ccList)) {
                $personalization['cc'] = $ccList;
            }

            $nombrePrincipal = $toList[0]['name'] ?? '';
            $emailData = [
                'personalizations' => [$personalization],
                'from' => [
                    'email' => $this->sendgridFromEmail,
                    'name' => $this->sendgridFromName
                ],
                'content' => [[
                    'type' => 'text/html',
                    'value' => $this->generarHtmlEmail($nombrePrincipal, $asunto, $cuerpoHtml, $tipo)
                ]],
                'tracking_settings' => [
                    'click_tracking' => ['enable' => false, 'enable_text' => false]
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
