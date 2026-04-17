<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\PendientesModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\UserModel;

/**
 * Comando para auto-clasificar pendientes ABIERTOS como SIN RESPUESTA DEL CLIENTE
 * cuando han pasado 90 dias despues de su fecha de plazo (fecha_cierre) sin gestion.
 *
 * Ejecutar con: php spark pendientes:clasificar-sin-respuesta
 *
 * CRON recomendado (6:00 AM todos los dias):
 * 0 6 * * * cd /ruta/al/proyecto && php spark pendientes:clasificar-sin-respuesta >> writable/logs/cron-sin-respuesta.log 2>&1
 */
class ClasificarSinRespuesta extends BaseCommand
{
    protected $group       = 'Pendientes';
    protected $name        = 'pendientes:clasificar-sin-respuesta';
    protected $description = 'Clasifica como SIN RESPUESTA DEL CLIENTE los pendientes ABIERTOS con 90+ dias despues de su plazo';
    protected $usage       = 'pendientes:clasificar-sin-respuesta [opciones]';
    protected $arguments   = [];
    protected $options     = [
        '--test' => 'Modo de prueba (no actualiza BD ni envia emails)',
        '--dias' => 'Dias despues del plazo para clasificar (default: 90)',
    ];

    protected $modoTest = false;
    protected $clasificados = 0;
    protected $emailsEnviados = 0;
    protected $emailsError = 0;

    protected $sendgridApiKey;
    protected $sendgridFromEmail;
    protected $sendgridFromName;

    public function run(array $params)
    {
        $this->sendgridApiKey    = getenv('SENDGRID_API_KEY') ?: '';
        $this->sendgridFromEmail = getenv('SENDGRID_FROM_EMAIL') ?: 'notificacion.cycloidtalent@cycloidtalent.com';
        $this->sendgridFromName  = getenv('SENDGRID_FROM_NAME') ?: 'Enterprise SST';

        $this->modoTest = CLI::getOption('test') !== null;
        $diasLimite = (int) (CLI::getOption('dias') ?? 90);

        if ($this->modoTest) {
            CLI::write('MODO DE PRUEBA - No se actualizara BD ni se enviaran emails', 'yellow');
        }

        CLI::write('=====================================================', 'cyan');
        CLI::write(' Clasificacion SIN RESPUESTA DEL CLIENTE - ' . date('d/m/Y H:i'), 'cyan');
        CLI::write(" Umbral: {$diasLimite} dias despues del plazo", 'cyan');
        CLI::write('=====================================================', 'cyan');
        CLI::newLine();

        $db = \Config\Database::connect();

        // Buscar pendientes ABIERTOS cuyo plazo (fecha_cierre) + 90 dias ya paso
        $query = $db->query("
            SELECT p.*, c.nombre_cliente, c.id_consultor, c.correo_cliente
            FROM tbl_pendientes p
            JOIN tbl_clientes c ON c.id_cliente = p.id_cliente
            WHERE p.estado = 'ABIERTA'
              AND p.fecha_cierre IS NOT NULL
              AND CAST(p.fecha_cierre AS CHAR) <> '0000-00-00'
              AND p.fecha_cierre >= '2000-01-01'
              AND DATEDIFF(CURDATE(), p.fecha_cierre) > ?
            ORDER BY c.nombre_cliente, p.fecha_cierre ASC
        ", [$diasLimite]);

        $pendientes = $query->getResultArray();

        if (empty($pendientes)) {
            CLI::write('No hay pendientes que clasificar. Todo OK.', 'green');
            return;
        }

        CLI::write("Pendientes a clasificar: " . count($pendientes), 'light_gray');
        CLI::newLine();

        $pendientesModel = new PendientesModel();
        $consultorModel  = new ConsultantModel();
        $userModel       = new UserModel();

        // Agrupar por cliente para enviar un solo email por cliente
        $porCliente = [];
        $porConsultor = [];

        foreach ($pendientes as $p) {
            $idCliente   = $p['id_cliente'];
            $idConsultor = $p['id_consultor'];

            // Actualizar en BD
            if (!$this->modoTest) {
                $pendientesModel->update($p['id_pendientes'], [
                    'estado'            => 'SIN RESPUESTA DEL CLIENTE',
                    'fecha_cierre_real' => date('Y-m-d'),
                ]);
                $this->clasificados++;
            }

            $diasVencido = (int) ((time() - strtotime($p['fecha_cierre'])) / 86400);
            $p['dias_vencido'] = $diasVencido;

            CLI::write("  [CLASIFICADO] #{$p['id_pendientes']} - {$p['nombre_cliente']} - {$p['tarea_actividad']} ({$diasVencido} dias vencido)", 'yellow');

            $porCliente[$idCliente][] = $p;

            if ($idConsultor) {
                $porConsultor[$idConsultor][] = $p;
            }
        }

        // --- Emails a CLIENTES ---
        CLI::newLine();
        CLI::write('[CLIENTES] Enviando notificaciones...', 'light_gray');

        foreach ($porCliente as $idCliente => $items) {
            // Buscar usuarios tipo client activos de este cliente
            $usuarios = $userModel
                ->where('tipo_usuario', 'client')
                ->where('estado', 'activo')
                ->where('id_entidad', $idCliente)
                ->findAll();

            foreach ($usuarios as $usuario) {
                $email = $usuario['email'] ?? '';
                if (empty($email)) continue;

                $asunto = 'Actividades clasificadas SIN RESPUESTA DEL CLIENTE - ' . date('d/m/Y');
                $html   = $this->generarHtmlCliente($usuario['nombre_completo'], $items);
                $ok     = $this->enviarEmail($email, $usuario['nombre_completo'], $asunto, $html);

                if ($ok) {
                    $this->emailsEnviados++;
                    CLI::write("  [OK] {$email} - " . count($items) . " actividad(es)", 'green');
                } else {
                    $this->emailsError++;
                    CLI::write("  [ERROR] {$email}", 'red');
                }
            }
        }

        // --- Emails a CONSULTORES ---
        CLI::newLine();
        CLI::write('[CONSULTORES] Enviando notificaciones...', 'light_gray');

        foreach ($porConsultor as $idConsultor => $items) {
            $consultor = $consultorModel->find($idConsultor);
            if (!$consultor || empty($consultor['correo_consultor'])) {
                CLI::write("  [SKIP] Consultor #{$idConsultor} sin email", 'yellow');
                continue;
            }

            $email  = $consultor['correo_consultor'];
            $nombre = $consultor['nombre_consultor'];
            $asunto = 'Actividades clasificadas SIN RESPUESTA DEL CLIENTE - ' . date('d/m/Y');
            $html   = $this->generarHtmlConsultor($nombre, $items);
            $ok     = $this->enviarEmail($email, $nombre, $asunto, $html);

            if ($ok) {
                $this->emailsEnviados++;
                CLI::write("  [OK] {$email} - " . count($items) . " actividad(es)", 'green');
            } else {
                $this->emailsError++;
                CLI::write("  [ERROR] {$email}", 'red');
            }
        }

        // Resumen final
        CLI::newLine();
        CLI::write('=====================================================', 'cyan');
        CLI::write(" Clasificados: {$this->clasificados}", 'cyan');
        CLI::write(" Emails enviados: {$this->emailsEnviados} | Errores: {$this->emailsError}", 'cyan');
        CLI::write('=====================================================', 'cyan');
    }

    /**
     * HTML para email al CLIENTE
     */
    protected function generarHtmlCliente(string $nombreCliente, array $pendientes): string
    {
        $total = count($pendientes);
        $fecha = date('d/m/Y');
        $tablaHtml = $this->generarTabla($pendientes);
        $nombreEsc = htmlspecialchars($nombreCliente);

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f3f4f6;">
    <div style="max-width: 700px; margin: 0 auto; padding: 20px;">
        <div style="background: linear-gradient(135deg, #1c2437 0%, #2d3748 100%); padding: 25px; text-align: center; border-radius: 12px 12px 0 0;">
            <h2 style="margin: 0; color: #ffc107; font-size: 20px;">Actividades Clasificadas: SIN RESPUESTA DEL CLIENTE</h2>
            <p style="margin: 8px 0 0; color: rgba(255,255,255,0.7); font-size: 13px;">{$fecha}</p>
        </div>
        <div style="background: white; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <p style="margin-top: 0;">Estimado(a) <strong>{$nombreEsc}</strong>,</p>
            <p>Le informamos que <strong style="color: #D97706; font-size: 18px;">{$total}</strong> actividad(es) pendiente(s) han sido clasificadas como <strong style="color: #D97706;">SIN RESPUESTA DEL CLIENTE</strong> debido a que superaron los 90 dias despues de su fecha de plazo sin evidencia de gestion.</p>

            {$tablaHtml}

            <div style="background: #FEF3C7; border-left: 4px solid #D97706; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0;">
                <p style="margin: 0; font-size: 13px; color: #92400E;">
                    <strong>Importante:</strong> Si usted cuenta con soportes que corroboren la gestion correspondiente de alguna de estas actividades, le solicitamos remitirlos via email a su consultor asignado para actualizar el estado.
                </p>
            </div>

            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;">
            <p style="margin-bottom: 0; color: #6B7280; font-size: 13px;">
                Saludos,<br>
                <strong>Enterprise SST - EnterpriseSST</strong>
            </p>
        </div>
        <div style="text-align: center; margin-top: 15px; color: #9CA3AF; font-size: 11px;">
            <p>Este es un mensaje automatico del sistema de gestion SST.<br>Por favor no responda a este correo.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * HTML para email al CONSULTOR
     */
    protected function generarHtmlConsultor(string $nombreConsultor, array $pendientes): string
    {
        $total = count($pendientes);
        $fecha = date('d/m/Y');

        // Agrupar por cliente
        $porCliente = [];
        foreach ($pendientes as $p) {
            $nombreCliente = $p['nombre_cliente'] ?? 'Sin cliente';
            $porCliente[$nombreCliente][] = $p;
        }

        $tablasHtml = '';
        foreach ($porCliente as $nombreCliente => $items) {
            $cantCliente = count($items);
            $nombreClienteEsc = htmlspecialchars($nombreCliente);
            $tablasHtml .= "<h3 style='margin: 20px 0 8px; color: #1c2437; font-size: 15px; border-bottom: 2px solid #D97706; padding-bottom: 5px;'>{$nombreClienteEsc} ({$cantCliente})</h3>";
            $tablasHtml .= $this->generarTabla($items);
        }

        $nombreEsc = htmlspecialchars($nombreConsultor);

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f3f4f6;">
    <div style="max-width: 700px; margin: 0 auto; padding: 20px;">
        <div style="background: linear-gradient(135deg, #1c2437 0%, #2d3748 100%); padding: 25px; text-align: center; border-radius: 12px 12px 0 0;">
            <h2 style="margin: 0; color: #ffc107; font-size: 20px;">Actividades Clasificadas: SIN RESPUESTA DEL CLIENTE</h2>
            <p style="margin: 8px 0 0; color: rgba(255,255,255,0.7); font-size: 13px;">{$fecha}</p>
        </div>
        <div style="background: white; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <p style="margin-top: 0;">Hola <strong>{$nombreEsc}</strong>,</p>
            <p>Se han clasificado <strong style="color: #D97706; font-size: 18px;">{$total}</strong> actividad(es) como <strong style="color: #D97706;">SIN RESPUESTA DEL CLIENTE</strong> por superar 90 dias despues de su fecha de plazo sin gestion.</p>

            {$tablasHtml}

            <div style="background: #FEF3C7; border-left: 4px solid #D97706; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0;">
                <p style="margin: 0; font-size: 13px; color: #92400E;">
                    <strong>Nota:</strong> Se ha notificado al cliente para que remita soportes si los tiene. Si el cliente presenta evidencia, puede reabrir la actividad desde el modulo de pendientes.
                </p>
            </div>

            <div style="text-align: center; margin: 25px 0 10px;">
                <a href="https://dashboard.cycloidtalent.com/listPendientes" style="display: inline-block; background: #bd9751; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 14px;">Ver Pendientes</a>
            </div>

            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;">
            <p style="margin-bottom: 0; color: #6B7280; font-size: 13px;">
                Saludos,<br>
                <strong>Enterprise SST - EnterpriseSST</strong>
            </p>
        </div>
        <div style="text-align: center; margin-top: 15px; color: #9CA3AF; font-size: 11px;">
            <p>Este es un mensaje automatico del sistema de gestion SST.<br>Por favor no responda a este correo.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Genera tabla HTML reutilizable
     */
    protected function generarTabla(array $pendientes): string
    {
        $html = "
        <table style='width: 100%; border-collapse: collapse; font-size: 13px; margin: 10px 0;'>
            <thead>
                <tr style='background: #1c2437; color: white;'>
                    <th style='padding: 8px; text-align: left;'>Tarea / Actividad</th>
                    <th style='padding: 8px; text-align: center; width: 90px;'>Responsable</th>
                    <th style='padding: 8px; text-align: center; width: 85px;'>Plazo</th>
                    <th style='padding: 8px; text-align: center; width: 70px;'>Dias Vencido</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($pendientes as $i => $item) {
            $bg = $i % 2 === 0 ? '#ffffff' : '#f9fafb';
            $tarea = htmlspecialchars($item['tarea_actividad'] ?? '');
            $responsable = htmlspecialchars($item['responsable'] ?? '-');
            $plazo = !empty($item['fecha_cierre']) ? date('d/m/Y', strtotime($item['fecha_cierre'])) : '-';
            $diasVencido = $item['dias_vencido'] ?? 0;

            $html .= "
                <tr style='background: {$bg};'>
                    <td style='padding: 8px; border-bottom: 1px solid #e5e7eb;'>{$tarea}</td>
                    <td style='padding: 8px; text-align: center; border-bottom: 1px solid #e5e7eb;'>{$responsable}</td>
                    <td style='padding: 8px; text-align: center; border-bottom: 1px solid #e5e7eb;'>{$plazo}</td>
                    <td style='padding: 8px; text-align: center; border-bottom: 1px solid #e5e7eb; background: #FEE2E2; color: #DC2626; font-weight: bold;'>{$diasVencido}</td>
                </tr>";
        }

        $html .= "</tbody></table>";
        return $html;
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
