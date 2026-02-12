<?php
namespace App\Controllers;

use SendGrid\Mail\Mail;
require __DIR__ . '/../../vendor/autoload.php';

class EmailController
{
    public function sendTestEmail()
    {
        // Configurar los datos del correo
        $email = new Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent"); // Remitente actualizado
        $email->setSubject("Prueba de envío con SendGrid"); // Asunto del correo
        $email->addTo("edison.cuervo@cycloidtalent.com", "Edison Cuervo"); // Destinatario actualizado
        $email->addContent(
            "text/plain",
            "Hola Edison, este es un mensaje de prueba enviado utilizando SendGrid desde el correo oficial de Cycloid Talent."
        );
        $email->addContent(
            "text/html",
            "<p><strong>Hola Edison,</strong></p>
             <p>Este es un mensaje de prueba enviado utilizando SendGrid desde el correo oficial de Cycloid Talent.</p>
             <p>Gracias por confiar en Cycloid Talent.</p>"
        );

        // Crear instancia de SendGrid utilizando la clave API
        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));

        try {
            $response = $sendgrid->send($email);

            // Mostrar información sobre la respuesta de SendGrid
            echo "Código de respuesta: " . $response->statusCode() . "\n";
            echo "Cuerpo de la respuesta: " . $response->body() . "\n";
            echo "Encabezados de la respuesta:\n";
            print_r($response->headers());
        } catch (\Exception $e) {
            echo 'Excepción capturada: ' . $e->getMessage() . "\n";
        }
    }
}

// Instanciar y ejecutar la función
$controller = new EmailController();
$controller->sendTestEmail();
