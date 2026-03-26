<?php

namespace App\Libraries;

use SendGrid;
use SendGrid\Mail\Mail;
use SendGrid\Mail\TrackingSettings;
use SendGrid\Mail\ClickTracking;

class SendGridMailer
{
    /**
     * Enviar email via SendGrid API con click tracking desactivado.
     *
     * @param Mail $mail  Objeto Mail ya configurado (from, to, subject, content, attachments, etc.)
     * @return \SendGrid\Response
     * @throws \Exception  Si SENDGRID_API_KEY no esta configurada o falla el envio
     */
    public static function send(Mail $mail): \SendGrid\Response
    {
        // Desactivar click tracking para que SendGrid no reescriba los enlaces
        $trackingSettings = new TrackingSettings();
        $clickTracking = new ClickTracking();
        $clickTracking->setEnable(false);
        $clickTracking->setEnableText(false);
        $trackingSettings->setClickTracking($clickTracking);
        $mail->setTrackingSettings($trackingSettings);

        $apiKey = getenv('SENDGRID_API_KEY');
        if (empty($apiKey)) {
            throw new \RuntimeException('SENDGRID_API_KEY no configurada en .env');
        }

        $sendgrid = new SendGrid($apiKey);

        return $sendgrid->send($mail);
    }
}
