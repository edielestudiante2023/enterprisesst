<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;">
            <h1 style="color: white; margin: 0; font-size: 22px;">Contrato de Prestacion de Servicios SST</h1>
            <p style="color: rgba(255,255,255,0.8); margin: 10px 0 0;">Solicitud de Firma Digital</p>
        </div>

        <!-- Body -->
        <div style="padding: 30px;">
            <p style="color: #333; font-size: 16px;">
                Estimado(a) <strong><?= esc($nombreFirmante) ?></strong>,
            </p>

            <p style="color: #555;">
                <?= esc($mensaje) ?>
            </p>

            <!-- Resumen del contrato -->
            <div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <h3 style="color: #667eea; margin-top: 0; font-size: 16px;">Resumen del Contrato</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #666; font-weight: bold; width: 40%;">Numero:</td>
                        <td style="padding: 8px 0; color: #333;"><?= esc($contrato['numero_contrato']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666; font-weight: bold;">Contratante:</td>
                        <td style="padding: 8px 0; color: #333;"><?= esc($contrato['nombre_cliente']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666; font-weight: bold;">Contratista:</td>
                        <td style="padding: 8px 0; color: #333;">CYCLOID TALENT S.A.S.</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666; font-weight: bold;">Vigencia:</td>
                        <td style="padding: 8px 0; color: #333;">
                            <?= date('d/m/Y', strtotime($contrato['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($contrato['fecha_fin'])) ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666; font-weight: bold;">Valor:</td>
                        <td style="padding: 8px 0; color: #333;">$<?= number_format($contrato['valor_contrato'], 0, ',', '.') ?> COP</td>
                    </tr>
                </table>
            </div>

            <!-- Boton CTA -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="<?= esc($urlFirma) ?>"
                   style="display: inline-block; background: linear-gradient(135deg, #28a745 0%, #218838 100%); color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: bold;">
                    Revisar y Firmar Contrato
                </a>
            </div>

            <div style="background: #fff3cd; border-radius: 8px; padding: 15px; margin: 20px 0;">
                <p style="color: #856404; margin: 0; font-size: 13px;">
                    <strong>Importante:</strong> Este enlace es personal e intransferible. Tiene validez de 7 dias a partir de la fecha de envio.
                </p>
            </div>

            <?php if (!empty($esCopia)): ?>
            <div style="background: #d1ecf1; border-radius: 8px; padding: 15px; margin: 20px 0;">
                <p style="color: #0c5460; margin: 0; font-size: 13px;">
                    <strong>Nota:</strong> Este correo es una copia informativa. La firma debe ser realizada por el Representante Legal del cliente.
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div style="background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eee;">
            <p style="color: #999; font-size: 12px; margin: 0;">
                Enterprise SST - Sistema de Gestion de Seguridad y Salud en el Trabajo<br>
                Este es un mensaje automatico, por favor no responda a este correo.<br>
                Enviado el <?= date('d/m/Y H:i:s') ?>
            </p>
        </div>
    </div>
</body>
</html>
