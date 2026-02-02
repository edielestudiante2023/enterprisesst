<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Aprobacion - Presupuesto SST</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation" style="width: 600px; border-collapse: collapse; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1a5f7a 0%, #2c3e50 100%); padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Solicitud de Aprobacion</h1>
                            <p style="color: #e0e0e0; margin: 10px 0 0 0; font-size: 14px;">Presupuesto de Recursos SST</p>
                        </td>
                    </tr>

                    <!-- Saludo -->
                    <tr>
                        <td style="padding: 30px 40px 20px 40px;">
                            <p style="margin: 0; font-size: 16px; color: #333;">
                                Estimado(a) <strong><?php echo esc($nombre); ?></strong>,
                            </p>
                            <p style="margin: 15px 0 0 0; font-size: 14px; color: #666; line-height: 1.6;">
                                <?php echo esc($mensaje ?? 'Se requiere su aprobacion y firma digital del presupuesto de recursos para el Sistema de Gestion de Seguridad y Salud en el Trabajo.'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Info del Documento -->
                    <tr>
                        <td style="padding: 0 40px 20px 40px;">
                            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8f9fa; border-radius: 6px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table role="presentation" style="width: 100%; border-collapse: collapse;">
                                            <tr>
                                                <td style="padding: 5px 0; font-size: 14px; color: #666;">Empresa:</td>
                                                <td style="padding: 5px 0; font-size: 14px; color: #333; font-weight: bold;"><?php echo esc($cliente['nombre_cliente']); ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 5px 0; font-size: 14px; color: #666;">NIT:</td>
                                                <td style="padding: 5px 0; font-size: 14px; color: #333;"><?php echo esc($cliente['nit_cliente'] ?? 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 5px 0; font-size: 14px; color: #666;">Periodo:</td>
                                                <td style="padding: 5px 0; font-size: 14px; color: #333;">Ano <?php echo esc($presupuesto['anio']); ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 5px 0; font-size: 14px; color: #666;">Codigo:</td>
                                                <td style="padding: 5px 0; font-size: 14px; color: #333;"><?= esc($codigoDocumento ?? 'FT-SST-001') ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Resumen del Presupuesto -->
                    <tr>
                        <td style="padding: 0 40px 20px 40px;">
                            <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #1a5f7a;">Resumen del Presupuesto</h3>
                            <table role="presentation" style="width: 100%; border-collapse: collapse; border: 1px solid #dee2e6; border-radius: 6px;">
                                <tr style="background-color: #1a5f7a;">
                                    <th style="padding: 12px; text-align: left; color: #fff; font-size: 13px;">Categoria</th>
                                    <th style="padding: 12px; text-align: right; color: #fff; font-size: 13px;">Presupuestado</th>
                                </tr>
                                <?php if (!empty($items)): ?>
                                    <?php
                                    $categorias = [];
                                    foreach ($items as $item) {
                                        $cat = $item['categoria_codigo'] ?? 'Otros';
                                        if (!isset($categorias[$cat])) {
                                            $categorias[$cat] = [
                                                'nombre' => $item['categoria_nombre'] ?? $cat,
                                                'total' => 0
                                            ];
                                        }
                                        $categorias[$cat]['total'] += floatval($item['total_presupuestado'] ?? 0);
                                    }
                                    $bgColor = true;
                                    foreach ($categorias as $codigo => $cat):
                                    ?>
                                    <tr style="background-color: <?php echo $bgColor ? '#ffffff' : '#f8f9fa'; ?>;">
                                        <td style="padding: 10px 12px; font-size: 13px; color: #333; border-bottom: 1px solid #dee2e6;">
                                            <?php echo esc($codigo); ?>. <?php echo esc($cat['nombre']); ?>
                                        </td>
                                        <td style="padding: 10px 12px; font-size: 13px; color: #333; text-align: right; border-bottom: 1px solid #dee2e6; font-family: 'Courier New', monospace;">
                                            $<?php echo number_format($cat['total'], 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                    <?php $bgColor = !$bgColor; endforeach; ?>
                                <?php endif; ?>
                                <tr style="background-color: #1a5f7a;">
                                    <td style="padding: 12px; font-size: 14px; color: #fff; font-weight: bold;">TOTAL PRESUPUESTO</td>
                                    <td style="padding: 12px; font-size: 14px; color: #fff; text-align: right; font-weight: bold; font-family: 'Courier New', monospace;">
                                        $<?php echo number_format($totales['general_presupuestado'] ?? 0, 0, ',', '.'); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Boton de Accion -->
                    <tr>
                        <td style="padding: 10px 40px 30px 40px; text-align: center;">
                            <?php if (!$esCopia): ?>
                            <p style="margin: 0 0 20px 0; font-size: 14px; color: #666;">
                                Haga clic en el siguiente boton para revisar y aprobar el presupuesto:
                            </p>
                            <a href="<?php echo esc($urlFirma); ?>"
                               style="display: inline-block; background-color: #28a745; color: #ffffff; padding: 14px 40px; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: bold;">
                                Revisar y Aprobar
                            </a>
                            <p style="margin: 20px 0 0 0; font-size: 12px; color: #999;">
                                O copie este enlace en su navegador:<br>
                                <span style="color: #1a5f7a; word-break: break-all;"><?php echo esc($urlFirma); ?></span>
                            </p>
                            <?php else: ?>
                            <div style="background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 15px;">
                                <p style="margin: 0; font-size: 14px; color: #856404;">
                                    <strong>Nota:</strong> Este es un correo informativo. El Representante Legal debe aprobar este presupuesto.
                                </p>
                            </div>
                            <?php if (!empty($urlDashboard)): ?>
                            <p style="margin: 20px 0 0 0; font-size: 13px; color: #666;">
                                Puede consultar el estado en: <a href="<?php echo esc($urlDashboard); ?>" style="color: #1a5f7a;"><?php echo esc($urlDashboard); ?></a>
                            </p>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Advertencia de expiracion -->
                    <?php if (!$esCopia): ?>
                    <tr>
                        <td style="padding: 0 40px 30px 40px;">
                            <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 6px; padding: 15px;">
                                <p style="margin: 0; font-size: 13px; color: #721c24;">
                                    <strong>Importante:</strong> Este enlace expira en 7 dias. Despues de ese tiempo debera solicitar un nuevo enlace.
                                </p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 25px 40px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0; font-size: 12px; color: #999;">
                                Este correo fue enviado automaticamente por el Sistema de Gestion SST.
                            </p>
                            <p style="margin: 10px 0 0 0; font-size: 12px; color: #999;">
                                <?php echo esc($cliente['nombre_cliente']); ?> - <?php echo date('Y'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
