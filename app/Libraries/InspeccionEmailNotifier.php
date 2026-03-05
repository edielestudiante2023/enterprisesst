<?php

namespace App\Libraries;

use App\Models\ClientModel;
use App\Models\ConsultantModel;

class InspeccionEmailNotifier
{
    /**
     * Envía notificación por email con PDF adjunto al cliente, consultor y consultor externo.
     *
     * @param int    $idCliente       ID del cliente
     * @param int    $idConsultor     ID del consultor
     * @param string $nombreDocumento Título del documento (ej: "ACTA DE VISITA")
     * @param string $fecha           Fecha formateada Y-m-d del registro
     * @param string $pdfPath         Ruta relativa del PDF (desde FCPATH)
     * @param int    $idRegistro      ID del registro (para logs y nombre del adjunto)
     * @param string $logPrefix       Prefijo para logs (ej: "ActaVisita")
     * @param string $responsable     Nombre del responsable (opcional)
     */
    public static function enviar(
        int $idCliente,
        int $idConsultor,
        string $nombreDocumento,
        string $fecha,
        string $pdfPath,
        int $idRegistro,
        string $logPrefix = 'Inspeccion',
        string $responsable = '',
        array $extraAttachments = []
    ): array {
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $cliente = $clientModel->find($idCliente);
        if (!$cliente) return ['success' => false, 'error' => 'Cliente no encontrado'];

        $consultor = $consultantModel->find($idConsultor);

        $sendgridApiKey = getenv('SENDGRID_API_KEY');
        if (!$sendgridApiKey) {
            return ['success' => false, 'error' => 'SENDGRID_API_KEY no configurada'];
        }

        $nombreCliente   = $cliente['nombre_cliente'] ?? 'Cliente';
        $correoCliente   = $cliente['correo_cliente'] ?? '';
        $correoConsultor = $consultor['correo_consultor'] ?? '';
        $nombreConsultor = $consultor['nombre_consultor'] ?? 'Consultor';
        $consultorExterno      = $cliente['consultor_externo'] ?? '';
        $emailConsultorExterno = $cliente['email_consultor_externo'] ?? '';

        if (!$correoCliente && !$correoConsultor && !$emailConsultorExterno) {
            return ['success' => false, 'error' => 'No hay correos destinatarios configurados'];
        }

        $fechaFormateada = date('d/m/Y', strtotime($fecha));
        $subject = "{$nombreDocumento} - {$nombreCliente} - {$fechaFormateada}";

        // Fila de responsable (solo si se proporcionó)
        $filaResponsable = '';
        if ($responsable) {
            $responsableEsc = htmlspecialchars($responsable);
            $filaResponsable = "
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Responsable:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'>{$responsableEsc}</td>
                    </tr>";
        }

        $docEsc = htmlspecialchars($nombreDocumento);

        $htmlContent = "
        <div style='font-family: Segoe UI, Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #1c2437; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: #bd9751; margin: 0; font-size: 20px;'>{$docEsc}</h1>
            </div>
            <div style='padding: 25px; background: #f8f9fa; border-radius: 0 0 10px 10px;'>
                <p>En su plataforma <strong>EnterpriseSST</strong> se ha creado el nuevo documento <strong>{$docEsc}</strong>.</p>
                <p>Encuentra el documento adjunto en formato PDF.</p>
                <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Cliente:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'>{$nombreCliente}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Fecha:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'>{$fechaFormateada}</td>
                    </tr>{$filaResponsable}
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Consultor:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'>{$nombreConsultor}</td>
                    </tr>
                </table>
                <p>Para acceder al recurso, ingrese a su aplicativo en la sección de documentos.</p>
                <p style='color: #999; font-size: 11px; margin-top: 30px;'>Generado por SG-SST Cycloid Talent.</p>
            </div>
        </div>";

        // Leer PDF para adjuntar
        $pdfFullPath = FCPATH . $pdfPath;
        if (!file_exists($pdfFullPath)) {
            return ['success' => false, 'error' => 'Archivo PDF no encontrado en disco'];
        }
        $pdfContent = file_get_contents($pdfFullPath);

        require_once ROOTPATH . 'vendor/autoload.php';

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent - SG-SST");
        $email->setSubject($subject);

        if ($correoCliente) {
            $email->addTo($correoCliente, $nombreCliente);
        }
        if ($correoConsultor) {
            $email->addTo($correoConsultor, $nombreConsultor);
        }
        if ($emailConsultorExterno) {
            $email->addTo($emailConsultorExterno, $consultorExterno ?: 'Consultor Externo');
        }

        $email->addContent("text/html", $htmlContent);

        // Nombre del adjunto: limpiar el nombre del documento
        $safeNombre = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $nombreDocumento));
        $email->addAttachment(
            base64_encode($pdfContent),
            'application/pdf',
            $safeNombre . '_' . $idRegistro . '.pdf',
            'attachment'
        );

        // Adjuntos adicionales
        foreach ($extraAttachments as $extra) {
            $extraPath = FCPATH . $extra['path'];
            if (file_exists($extraPath)) {
                $email->addAttachment(
                    base64_encode(file_get_contents($extraPath)),
                    'application/pdf',
                    $extra['filename'],
                    'attachment'
                );
            }
        }

        $sendgrid = new \SendGrid($sendgridApiKey);

        try {
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                $destinatarios = array_filter([$correoCliente, $correoConsultor, $emailConsultorExterno]);
                log_message('info', "{$logPrefix} #{$idRegistro}: Email enviado a " . implode(', ', $destinatarios));
                return ['success' => true, 'message' => 'Email enviado a: ' . implode(', ', $destinatarios)];
            } else {
                log_message('error', "{$logPrefix} #{$idRegistro}: Error SendGrid. Status: {$response->statusCode()}. Body: {$response->body()}");
                return ['success' => false, 'error' => 'Error al enviar email. Status: ' . $response->statusCode()];
            }
        } catch (\Exception $e) {
            log_message('error', "{$logPrefix} #{$idRegistro}: Exception SendGrid: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
}
