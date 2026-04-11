<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\PendientesModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\UserModel;

/**
 * Comando para enviar resumen quincenal de pendientes ABIERTOS
 *
 * Ejecutar con: php spark pendientes:resumen
 *
 * CRON recomendado (7:00 AM los dias 1 y 16 de cada mes):
 * 0 7 1,16 * * cd /ruta/al/proyecto && php spark pendientes:resumen >> writable/logs/cron-pendientes.log 2>&1
 */
class ResumenPendientes extends BaseCommand
{
    protected $group       = 'Pendientes';
    protected $name        = 'pendientes:resumen';
    protected $description = 'Envia resumen quincenal de pendientes ABIERTOS a cada consultor';
    protected $usage       = 'pendientes:resumen [opciones]';
    protected $arguments   = [];
    protected $options     = [
        '--test' => 'Modo de prueba (no envia emails reales)',
    ];

    protected $modoTest = false;
    protected $enviados = 0;
    protected $errores  = 0;

    protected $sendgridApiKey;
    protected $sendgridFromEmail;
    protected $sendgridFromName;

    public function run(array $params)
    {
        $this->sendgridApiKey   = getenv('SENDGRID_API_KEY') ?: '';
        $this->sendgridFromEmail = getenv('SENDGRID_FROM_EMAIL') ?: 'notificacion.cycloidtalent@cycloidtalent.com';
        $this->sendgridFromName  = getenv('SENDGRID_FROM_NAME') ?: 'Enterprise SST';

        $this->modoTest = CLI::getOption('test') !== null;

        if ($this->modoTest) {
            CLI::write('MODO DE PRUEBA - No se enviaran emails reales', 'yellow');
        }

        CLI::write('===========================================', 'cyan');
        CLI::write(' Resumen Quincenal de Pendientes - ' . date('d/m/Y H:i'), 'cyan');
        CLI::write('===========================================', 'cyan');
        CLI::newLine();

        $pendientesModel = new PendientesModel();
        $clientModel     = new ClientModel();
        $consultorModel  = new ConsultantModel();

        // Obtener todos los pendientes ABIERTOS con datos del cliente
        $pendientes = $pendientesModel
            ->select('tbl_pendientes.*, tbl_clientes.nombre_cliente, tbl_clientes.id_consultor, tbl_clientes.correo_cliente, tbl_clientes.id_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_pendientes.id_cliente')
            ->where('tbl_pendientes.estado', 'ABIERTA')
            ->orderBy('tbl_clientes.nombre_cliente', 'ASC')
            ->orderBy('tbl_pendientes.fecha_asignacion', 'ASC')
            ->findAll();

        if (empty($pendientes)) {
            CLI::write('No hay pendientes ABIERTOS. Nada que enviar.', 'green');
            return;
        }

        CLI::write("Total pendientes ABIERTOS: " . count($pendientes), 'light_gray');

        // =============================================
        // FASE 1: Email individual por usuario CLIENTE
        // (tipo_usuario = 'client', NO 'miembro')
        // =============================================
        CLI::newLine();
        CLI::write('[CLIENTES] Enviando emails individuales...', 'light_gray');

        // Agrupar pendientes por id_cliente
        $pendientesPorCliente = [];
        foreach ($pendientes as $p) {
            $idCliente = $p['id_cliente'] ?? 0;
            if (!$idCliente) continue;
            $pendientesPorCliente[$idCliente][] = $p;
        }

        // Obtener usuarios tipo 'client' activos desde tbl_usuarios
        $userModel = new UserModel();
        $usuariosCliente = $userModel
            ->where('tipo_usuario', 'client')
            ->where('estado', 'activo')
            ->findAll();

        foreach ($usuariosCliente as $usuario) {
            $idCliente = $usuario['id_entidad'] ?? 0;

            // Solo si ese cliente tiene pendientes ABIERTOS
            if (!$idCliente || !isset($pendientesPorCliente[$idCliente])) {
                continue;
            }

            $emailUsuario = $usuario['email'] ?? '';
            if (empty($emailUsuario)) {
                CLI::write("  [SKIP] {$usuario['nombre_completo']} - sin email", 'yellow');
                continue;
            }

            $itemsCliente  = $pendientesPorCliente[$idCliente];
            $totalCliente  = count($itemsCliente);
            $nombreUsuario = $usuario['nombre_completo'];

            $asunto = "Pendientes ABIERTOS: {$totalCliente} tarea(s) requieren su atencion - " . date('d/m/Y');
            $html   = $this->generarHtmlCliente($nombreUsuario, $itemsCliente);

            $ok = $this->enviarEmail($emailUsuario, $nombreUsuario, $asunto, $html);

            if ($ok) {
                $this->enviados++;
                CLI::write("  [OK] {$emailUsuario} - {$totalCliente} pendiente(s)", 'green');
            } else {
                $this->errores++;
                CLI::write("  [ERROR] {$emailUsuario}", 'red');
            }
        }

        // =============================================
        // FASE 2: Email consolidado por CONSULTOR
        // =============================================
        CLI::newLine();
        CLI::write('[CONSULTORES] Enviando emails consolidados...', 'light_gray');

        $porConsultor = [];
        foreach ($pendientes as $p) {
            $idConsultor = $p['id_consultor'] ?? 0;
            if (!$idConsultor) continue;

            if (!isset($porConsultor[$idConsultor])) {
                $porConsultor[$idConsultor] = [];
            }
            $porConsultor[$idConsultor][] = $p;
        }

        foreach ($porConsultor as $idConsultor => $pendientesConsultor) {
            $consultor = $consultorModel->find($idConsultor);
            if (!$consultor || empty($consultor['correo_consultor'])) {
                CLI::write("  [SKIP] Consultor #{$idConsultor} sin email", 'yellow');
                continue;
            }

            $nombre = $consultor['nombre_consultor'];
            $email  = $consultor['correo_consultor'];
            $total  = count($pendientesConsultor);

            $asunto = "Resumen Quincenal: {$total} pendiente(s) ABIERTO(s) - " . date('d/m/Y');
            $html   = $this->generarHtmlResumen($nombre, $pendientesConsultor);

            $ok = $this->enviarEmail($email, $nombre, $asunto, $html);

            if ($ok) {
                $this->enviados++;
                CLI::write("  [OK] {$email} - {$total} pendiente(s)", 'green');
            } else {
                $this->errores++;
                CLI::write("  [ERROR] {$email}", 'red');
            }
        }

        // Resumen final
        CLI::newLine();
        CLI::write('===========================================', 'cyan');
        CLI::write(" Enviados: {$this->enviados} | Errores: {$this->errores}", 'cyan');
        CLI::write('===========================================', 'cyan');
    }

    /**
     * Genera el HTML del email individual para un CLIENTE
     */
    protected function generarHtmlCliente(string $nombreCliente, array $pendientes): string
    {
        $totalPendientes = count($pendientes);
        $fecha = date('d/m/Y');

        $tablaHtml = "
        <table style='width: 100%; border-collapse: collapse; font-size: 13px; margin: 15px 0;'>
            <thead>
                <tr style='background: #1c2437; color: white;'>
                    <th style='padding: 8px; text-align: left;'>Tarea / Actividad</th>
                    <th style='padding: 8px; text-align: center; width: 90px;'>Responsable</th>
                    <th style='padding: 8px; text-align: center; width: 85px;'>Asignada</th>
                    <th style='padding: 8px; text-align: center; width: 60px;'>Dias</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($pendientes as $i => $item) {
            $bg = $i % 2 === 0 ? '#ffffff' : '#f9fafb';
            $tarea = htmlspecialchars($item['tarea_actividad'] ?? '');
            $responsable = htmlspecialchars($item['responsable'] ?? '-');
            $fechaAsig = !empty($item['fecha_asignacion'])
                ? date('d/m/Y', strtotime($item['fecha_asignacion']))
                : '-';
            $dias = intval($item['conteo_dias'] ?? 0);

            if ($dias > 90) {
                $colorDias = '#DC2626'; $bgDias = '#FEE2E2';
            } elseif ($dias > 60) {
                $colorDias = '#D97706'; $bgDias = '#FEF3C7';
            } elseif ($dias > 30) {
                $colorDias = '#2563EB'; $bgDias = '#DBEAFE';
            } else {
                $colorDias = '#059669'; $bgDias = '#D1FAE5';
            }

            $tablaHtml .= "
                <tr style='background: {$bg};'>
                    <td style='padding: 8px; border-bottom: 1px solid #e5e7eb;'>{$tarea}</td>
                    <td style='padding: 8px; text-align: center; border-bottom: 1px solid #e5e7eb;'>{$responsable}</td>
                    <td style='padding: 8px; text-align: center; border-bottom: 1px solid #e5e7eb;'>{$fechaAsig}</td>
                    <td style='padding: 8px; text-align: center; border-bottom: 1px solid #e5e7eb; background: {$bgDias}; color: {$colorDias}; font-weight: bold;'>{$dias}</td>
                </tr>";
        }

        $tablaHtml .= "
            </tbody>
        </table>";

        $nombreClienteEsc = htmlspecialchars($nombreCliente);

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f3f4f6;">
    <div style="max-width: 700px; margin: 0 auto; padding: 20px;">
        <div style="background: linear-gradient(135deg, #1c2437 0%, #2d3748 100%); padding: 25px; text-align: center; border-radius: 12px 12px 0 0;">
            <h2 style="margin: 0; color: #bd9751; font-size: 20px;">Pendientes Abiertos</h2>
            <p style="margin: 8px 0 0; color: rgba(255,255,255,0.7); font-size: 13px;">{$fecha}</p>
        </div>
        <div style="background: white; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <p style="margin-top: 0;">Estimado(a) <strong>{$nombreClienteEsc}</strong>,</p>
            <p>Le informamos que tiene <strong style="color: #DC2626; font-size: 18px;">{$totalPendientes}</strong> pendiente(s) con estado <strong>ABIERTA</strong> que requieren su atencion:</p>

            {$tablaHtml}

            <p style="margin-top: 20px; color: #4B5563; font-size: 13px;">Le solicitamos gestionar las tareas listadas anteriormente. Si alguna ya fue resuelta, por favor informe a su consultor para actualizar el estado.</p>

            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;">
            <p style="margin-bottom: 0; color: #6B7280; font-size: 13px;">
                Saludos,<br>
                <strong>Enterprise SST - EnterpriseSST</strong>
            </p>
        </div>
        <div style="text-align: center; margin-top: 15px; color: #9CA3AF; font-size: 11px;">
            <p>Este recordatorio se envia los dias 1 y 16 de cada mes.<br>Por favor no responda a este correo.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Genera el HTML del email consolidado con tabla de pendientes agrupados por cliente (para CONSULTOR)
     */
    protected function generarHtmlResumen(string $nombre, array $pendientes): string
    {
        // Agrupar por cliente
        $porCliente = [];
        foreach ($pendientes as $p) {
            $nombreCliente = $p['nombre_cliente'] ?? 'Sin cliente';
            if (!isset($porCliente[$nombreCliente])) {
                $porCliente[$nombreCliente] = [];
            }
            $porCliente[$nombreCliente][] = $p;
        }

        $totalPendientes = count($pendientes);
        $fecha = date('d/m/Y');

        // Calcular dias promedio
        $diasTotal = 0;
        $count = 0;
        foreach ($pendientes as $p) {
            if (!empty($p['conteo_dias'])) {
                $diasTotal += intval($p['conteo_dias']);
                $count++;
            }
        }
        $promedio = $count > 0 ? round($diasTotal / $count) : 0;

        // Contar por urgencia
        $criticos = 0; // > 90 dias
        $altos    = 0; // 60-90 dias
        $medios   = 0; // 30-60 dias
        $bajos    = 0; // < 30 dias

        foreach ($pendientes as $p) {
            $dias = intval($p['conteo_dias'] ?? 0);
            if ($dias > 90) $criticos++;
            elseif ($dias > 60) $altos++;
            elseif ($dias > 30) $medios++;
            else $bajos++;
        }

        // Cards de resumen
        $cardsHtml = "
        <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
            <tr>
                <td style='width: 25%; text-align: center; padding: 12px; background: #FEE2E2; border-radius: 8px;'>
                    <div style='font-size: 24px; font-weight: bold; color: #DC2626;'>{$criticos}</div>
                    <div style='font-size: 11px; color: #991B1B;'>Criticos (&gt;90d)</div>
                </td>
                <td style='width: 4px;'></td>
                <td style='width: 25%; text-align: center; padding: 12px; background: #FEF3C7; border-radius: 8px;'>
                    <div style='font-size: 24px; font-weight: bold; color: #D97706;'>{$altos}</div>
                    <div style='font-size: 11px; color: #92400E;'>Altos (60-90d)</div>
                </td>
                <td style='width: 4px;'></td>
                <td style='width: 25%; text-align: center; padding: 12px; background: #DBEAFE; border-radius: 8px;'>
                    <div style='font-size: 24px; font-weight: bold; color: #2563EB;'>{$medios}</div>
                    <div style='font-size: 11px; color: #1E40AF;'>Medios (30-60d)</div>
                </td>
                <td style='width: 4px;'></td>
                <td style='width: 25%; text-align: center; padding: 12px; background: #D1FAE5; border-radius: 8px;'>
                    <div style='font-size: 24px; font-weight: bold; color: #059669;'>{$bajos}</div>
                    <div style='font-size: 11px; color: #065F46;'>Bajos (&lt;30d)</div>
                </td>
            </tr>
        </table>";

        // Tabla de pendientes por cliente
        $tablasHtml = '';
        foreach ($porCliente as $nombreCliente => $items) {
            $cantCliente = count($items);
            $nombreClienteEsc = htmlspecialchars($nombreCliente);

            $tablasHtml .= "
            <div style='margin: 20px 0;'>
                <h3 style='margin: 0 0 8px; color: #1c2437; font-size: 15px; border-bottom: 2px solid #bd9751; padding-bottom: 5px;'>
                    {$nombreClienteEsc} ({$cantCliente})
                </h3>
                <table style='width: 100%; border-collapse: collapse; font-size: 13px;'>
                    <thead>
                        <tr style='background: #1c2437; color: white;'>
                            <th style='padding: 8px; text-align: left;'>Tarea / Actividad</th>
                            <th style='padding: 8px; text-align: center; width: 90px;'>Responsable</th>
                            <th style='padding: 8px; text-align: center; width: 85px;'>Asignada</th>
                            <th style='padding: 8px; text-align: center; width: 60px;'>Dias</th>
                        </tr>
                    </thead>
                    <tbody>";

            foreach ($items as $i => $item) {
                $bg = $i % 2 === 0 ? '#ffffff' : '#f9fafb';
                $tarea = htmlspecialchars($item['tarea_actividad'] ?? '');
                $responsable = htmlspecialchars($item['responsable'] ?? '-');
                $fechaAsig = !empty($item['fecha_asignacion'])
                    ? date('d/m/Y', strtotime($item['fecha_asignacion']))
                    : '-';
                $dias = intval($item['conteo_dias'] ?? 0);

                // Color segun dias
                if ($dias > 90) {
                    $colorDias = '#DC2626';
                    $bgDias = '#FEE2E2';
                } elseif ($dias > 60) {
                    $colorDias = '#D97706';
                    $bgDias = '#FEF3C7';
                } elseif ($dias > 30) {
                    $colorDias = '#2563EB';
                    $bgDias = '#DBEAFE';
                } else {
                    $colorDias = '#059669';
                    $bgDias = '#D1FAE5';
                }

                $tablasHtml .= "
                        <tr style='background: {$bg};'>
                            <td style='padding: 8px; border-bottom: 1px solid #e5e7eb;'>{$tarea}</td>
                            <td style='padding: 8px; text-align: center; border-bottom: 1px solid #e5e7eb;'>{$responsable}</td>
                            <td style='padding: 8px; text-align: center; border-bottom: 1px solid #e5e7eb;'>{$fechaAsig}</td>
                            <td style='padding: 8px; text-align: center; border-bottom: 1px solid #e5e7eb; background: {$bgDias}; color: {$colorDias}; font-weight: bold;'>{$dias}</td>
                        </tr>";
            }

            $tablasHtml .= "
                    </tbody>
                </table>
            </div>";
        }

        $urlDashboard = 'https://dashboard.cycloidtalent.com/listPendientes';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f3f4f6;">
    <div style="max-width: 700px; margin: 0 auto; padding: 20px;">
        <div style="background: linear-gradient(135deg, #1c2437 0%, #2d3748 100%); padding: 25px; text-align: center; border-radius: 12px 12px 0 0;">
            <h2 style="margin: 0; color: #bd9751; font-size: 20px;">Resumen Quincenal de Pendientes</h2>
            <p style="margin: 8px 0 0; color: rgba(255,255,255,0.7); font-size: 13px;">{$fecha}</p>
        </div>
        <div style="background: white; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <p style="margin-top: 0;">Hola <strong>{$nombre}</strong>,</p>
            <p>Tienes <strong style="color: #DC2626; font-size: 18px;">{$totalPendientes}</strong> pendiente(s) con estado <strong>ABIERTA</strong> en tu cartera de clientes. Promedio de antiguedad: <strong>{$promedio} dias</strong>.</p>

            {$cardsHtml}

            {$tablasHtml}

            <div style="text-align: center; margin: 25px 0 10px;">
                <a href="{$urlDashboard}" style="display: inline-block; background: #bd9751; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 14px;">Ver Tablero Completo</a>
            </div>

            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;">
            <p style="margin-bottom: 0; color: #6B7280; font-size: 13px;">
                Saludos,<br>
                <strong>Enterprise SST - EnterpriseSST</strong>
            </p>
        </div>
        <div style="text-align: center; margin-top: 15px; color: #9CA3AF; font-size: 11px;">
            <p>Este resumen se envia los dias 1 y 16 de cada mes.<br>Por favor no responda a este correo.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Enviar email usando SendGrid API (via cURL)
     */
    protected function enviarEmail(string $email, string $nombre, string $asunto, string $htmlBody): bool
    {
        if ($this->modoTest) {
            CLI::write("  [TEST] -> {$email}: {$asunto}", 'light_gray');
            return true;
        }

        if (empty($this->sendgridApiKey)) {
            CLI::write("  [WARN] SENDGRID_API_KEY no configurada en .env", 'yellow');
            return false;
        }

        try {
            $emailData = [
                'personalizations' => [[
                    'to' => [['email' => $email, 'name' => $nombre]],
                    'subject' => $asunto
                ]],
                'from' => [
                    'email' => $this->sendgridFromEmail,
                    'name'  => $this->sendgridFromName
                ],
                'content' => [[
                    'type'  => 'text/html',
                    'value' => $htmlBody
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
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($emailData),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
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
                CLI::write("  [CURL] {$curlError}", 'red');
                return false;
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                CLI::write("  [SENDGRID] HTTP {$httpCode}: {$response}", 'red');
                return false;
            }

            return true;

        } catch (\Exception $e) {
            CLI::write("  [EXCEPTION] " . $e->getMessage(), 'red');
            return false;
        }
    }
}
