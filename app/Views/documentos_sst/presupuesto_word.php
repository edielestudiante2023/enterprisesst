<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
            <w:DoNotOptimizeForBrowser/>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page {
            size: Letter portrait;
            margin: 2cm 2cm 2cm 2cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #333333;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }

        /* Encabezado */
        .header-table {
            margin-bottom: 20px;
        }
        .header-table td {
            border: 1px solid #000000;
            padding: 8px;
            vertical-align: middle;
        }
        .header-logo {
            width: 18%;
            text-align: center;
        }
        .header-titulo {
            text-align: center;
            background-color: #1a5f7a;
            color: #ffffff;
        }
        .header-titulo-top {
            border-bottom: 1px solid #000;
        }
        .header-codigo {
            width: 25%;
            padding: 0;
        }
        .codigo-table {
            width: 100%;
            border-collapse: collapse;
        }
        .codigo-table td {
            border: none;
            border-bottom: 1px solid #333;
            padding: 4px 8px;
            font-size: 8pt;
        }
        .codigo-table tr:last-child td {
            border-bottom: none;
        }
        .codigo-table .label {
            font-weight: bold;
            width: 50%;
        }
        h1 {
            font-size: 11pt;
            margin: 5px 0;
            color: #ffffff;
        }
        h2 {
            font-size: 10pt;
            margin: 5px 0;
            font-weight: normal;
            color: #ffffff;
        }

        /* Info empresa */
        .info-empresa {
            margin-bottom: 20px;
            padding: 12px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .info-empresa p {
            margin: 4px 0;
        }

        /* Titulo seccion */
        .seccion-titulo {
            background-color: #1a5f7a;
            color: white;
            padding: 8px 12px;
            margin: 20px 0 10px 0;
            font-weight: bold;
            font-size: 11pt;
        }

        /* Tabla de items */
        .items-table {
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #2c3e50;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 10pt;
            border: 1px solid #333;
        }
        .items-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            font-size: 9pt;
        }
        .categoria-row td {
            background-color: #e9ecef;
            font-weight: bold;
            color: #1a5f7a;
        }
        .subtotal-row td {
            background-color: #d4edda;
            font-weight: bold;
        }
        .total-row td {
            background-color: #1a5f7a;
            color: white;
            font-weight: bold;
            font-size: 11pt;
        }
        .monto {
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        /* Resumen */
        .resumen-table {
            border: 2px solid #1a5f7a;
            margin: 20px 0;
        }
        .resumen-table th {
            background-color: #1a5f7a;
            color: white;
            padding: 10px;
            text-align: left;
        }
        .resumen-table td {
            padding: 8px 12px;
            border-bottom: 1px dotted #ccc;
        }
        .resumen-total td {
            border-top: 2px solid #1a5f7a;
            font-size: 12pt;
            font-weight: bold;
        }

        /* Firmas */
        .firmas-table {
            margin-top: 50px;
        }
        .firmas-table td {
            width: 50%;
            text-align: center;
            padding: 20px 30px;
            vertical-align: bottom;
        }
        .firma-box {
            border: 1px solid #ccc;
            padding: 15px;
            min-height: 100px;
            margin-bottom: 10px;
            background-color: #fafafa;
        }
        .firma-linea {
            border-top: 1px solid #000;
            padding-top: 8px;
            font-weight: bold;
            font-size: 9pt;
        }
        .firma-nombre {
            font-size: 9pt;
            color: #666;
            margin-top: 5px;
        }

        /* Estado */
        .estado-aprobado {
            background-color: #d4edda;
            color: #155724;
            padding: 4px 12px;
            font-weight: bold;
        }
        .estado-pendiente {
            background-color: #fff3cd;
            color: #856404;
            padding: 4px 12px;
            font-weight: bold;
        }
        .estado-borrador {
            background-color: #e2e3e5;
            color: #383d41;
            padding: 4px 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Encabezado Estándar Word -->
    <table width="100%" border="1" cellpadding="0" cellspacing="0" style="border-collapse:collapse; border:1px solid #333; margin-bottom:15px;">
        <tr>
            <td width="80" rowspan="2" align="center" valign="middle" bgcolor="#FFFFFF" style="border:1px solid #333; padding:5px; background-color:#ffffff;">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" width="70" height="45" alt="Logo">
                <?php else: ?>
                    <b style="font-size:8pt;"><?= esc($cliente['nombre_cliente']) ?></b>
                <?php endif; ?>
            </td>
            <td align="center" valign="middle" style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
                SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO
            </td>
            <td width="120" rowspan="2" valign="middle" style="border:1px solid #333; padding:0; font-size:8pt;">
                <table width="100%" cellpadding="2" cellspacing="0" style="border-collapse:collapse;">
                    <tr><td style="border-bottom:1px solid #333;"><b>Codigo:</b></td><td style="border-bottom:1px solid #333;"><?= $codigoDocumento ?? 'FT-SST-001' ?></td></tr>
                    <tr><td style="border-bottom:1px solid #333;"><b>Version:</b></td><td style="border-bottom:1px solid #333;"><?= $versionDocumento ?? '001' ?></td></tr>
                    <tr><td><b>Vigencia:</b></td><td><?= date('d/m/Y') ?></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle" style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
                <?= esc(strtoupper($tituloDocumento ?? 'ASIGNACION DE RECURSOS PARA EL SG-SST')) ?>
            </td>
        </tr>
    </table>

    <!-- Informacion de la empresa -->
    <table width="100%" cellpadding="8" cellspacing="0" style="margin-bottom:15px; background-color:#f8f9fa; border:1px solid #dee2e6;">
        <tr>
            <td style="font-size:10pt;">
                <p style="margin:4px 0;"><b>Empresa:</b> <?= esc($cliente['nombre_cliente']) ?></p>
                <p style="margin:4px 0;"><b>NIT:</b> <?= esc($cliente['nit_cliente'] ?? 'N/A') ?></p>
                <p style="margin:4px 0;"><b>Periodo:</b> Ano <?= $anio ?></p>
                <?php
                $estado = trim($presupuesto['estado'] ?? '');
                $estado = empty($estado) ? 'borrador' : $estado;
                $estadoTexto = match($estado) {
                    'aprobado' => 'APROBADO',
                    'pendiente_firma' => 'PENDIENTE DE APROBACION',
                    default => 'BORRADOR'
                };
                ?>
                <p style="margin:4px 0;"><b>Estado:</b> <?= $estadoTexto ?>
                    <?php if ($estado === 'aprobado' && !empty($presupuesto['fecha_aprobacion'])): ?>
                        - <?= date('d/m/Y', strtotime($presupuesto['fecha_aprobacion'])) ?>
                    <?php endif; ?>
                </p>
            </td>
        </tr>
    </table>

    <!-- Tabla de Items -->
    <p style="background-color:#1a5f7a; color:#ffffff; padding:8px 12px; margin:15px 0 10px 0; font-weight:bold; font-size:11pt;">
        DETALLE DE ASIGNACION DE RECURSOS
    </p>

    <table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse; margin-bottom:15px;">
        <tr bgcolor="#2c3e50" style="background-color:#2c3e50;">
            <th width="8%" style="border:1px solid #333; color:#ffffff; text-align:left; font-size:9pt;">Item</th>
            <th width="52%" style="border:1px solid #333; color:#ffffff; text-align:left; font-size:9pt;">Actividad / Descripcion</th>
            <th width="20%" style="border:1px solid #333; color:#ffffff; text-align:right; font-size:9pt;">Presupuestado</th>
            <th width="20%" style="border:1px solid #333; color:#ffffff; text-align:right; font-size:9pt;">Ejecutado</th>
        </tr>
        <?php foreach ($itemsPorCategoria as $codigoCat => $categoria): ?>
            <!-- Categoria -->
            <tr bgcolor="#e9ecef" style="background-color:#e9ecef;">
                <td colspan="4" style="border:1px solid #dee2e6; font-weight:bold; color:#1a5f7a; font-size:9pt;"><?= $codigoCat ?>. <?= esc($categoria['nombre']) ?></td>
            </tr>

            <!-- Items -->
            <?php foreach ($categoria['items'] as $item): ?>
            <tr>
                <td style="border:1px solid #dee2e6; font-size:9pt;"><?= esc($item['codigo_item']) ?></td>
                <td style="border:1px solid #dee2e6; padding-left:15px; font-size:9pt;">
                    <?= esc($item['actividad']) ?>
                    <?php if (!empty($item['descripcion'])): ?>
                        <br><span style="color:#666; font-size:8pt;"><?= esc($item['descripcion']) ?></span>
                    <?php endif; ?>
                </td>
                <td style="border:1px solid #dee2e6; text-align:right; font-family:'Courier New',monospace; font-size:9pt;">$<?= number_format($item['total_presupuestado'], 0, ',', '.') ?></td>
                <td style="border:1px solid #dee2e6; text-align:right; font-family:'Courier New',monospace; font-size:9pt;">$<?= number_format($item['total_ejecutado'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>

            <!-- Subtotal categoria -->
            <?php $totCat = $totales['por_categoria'][$codigoCat] ?? ['presupuestado' => 0, 'ejecutado' => 0]; ?>
            <tr bgcolor="#d4edda" style="background-color:#d4edda;">
                <td colspan="2" style="border:1px solid #dee2e6; text-align:right; font-weight:bold; font-size:9pt;">Subtotal <?= $codigoCat ?>:</td>
                <td style="border:1px solid #dee2e6; text-align:right; font-weight:bold; font-family:'Courier New',monospace; font-size:9pt;">$<?= number_format($totCat['presupuestado'], 0, ',', '.') ?></td>
                <td style="border:1px solid #dee2e6; text-align:right; font-weight:bold; font-family:'Courier New',monospace; font-size:9pt;">$<?= number_format($totCat['ejecutado'], 0, ',', '.') ?></td>
            </tr>
        <?php endforeach; ?>

        <!-- Total General -->
        <tr bgcolor="#1a5f7a" style="background-color:#1a5f7a;">
            <td colspan="2" style="border:1px solid #333; text-align:right; color:#ffffff; font-weight:bold; font-size:10pt;">TOTAL GENERAL:</td>
            <td style="border:1px solid #333; text-align:right; color:#ffffff; font-weight:bold; font-family:'Courier New',monospace; font-size:10pt;">$<?= number_format($totales['general_presupuestado'], 0, ',', '.') ?></td>
            <td style="border:1px solid #333; text-align:right; color:#ffffff; font-weight:bold; font-family:'Courier New',monospace; font-size:10pt;">$<?= number_format($totales['general_ejecutado'], 0, ',', '.') ?></td>
        </tr>
    </table>

    <!-- Resumen -->
    <table width="100%" border="2" cellpadding="8" cellspacing="0" style="border-collapse:collapse; border:2px solid #1a5f7a; margin:15px 0;">
        <tr bgcolor="#1a5f7a" style="background-color:#1a5f7a;">
            <th colspan="2" style="color:#ffffff; text-align:left; font-size:10pt;">RESUMEN PRESUPUESTO <?= $anio ?></th>
        </tr>
        <?php foreach ($itemsPorCategoria as $codigoCat => $categoria):
            $totCat = $totales['por_categoria'][$codigoCat] ?? ['presupuestado' => 0];
        ?>
        <tr>
            <td width="70%" style="border-bottom:1px dotted #ccc; font-size:9pt;"><?= $codigoCat ?>. <?= esc($categoria['nombre']) ?></td>
            <td width="30%" style="border-bottom:1px dotted #ccc; text-align:right; font-weight:bold; font-family:'Courier New',monospace; font-size:9pt;">$<?= number_format($totCat['presupuestado'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
        <tr style="border-top:2px solid #1a5f7a;">
            <td style="font-weight:bold; font-size:11pt;">TOTAL PRESUPUESTO APROBADO</td>
            <td style="text-align:right; font-weight:bold; color:#1a5f7a; font-family:'Courier New',monospace; font-size:11pt;">$<?= number_format($totales['general_presupuestado'], 0, ',', '.') ?></td>
        </tr>
    </table>

    <!-- Firmas de Aprobacion -->
    <p style="text-align:center; font-weight:bold; margin-top:40px; font-size:11pt; color:#1a5f7a;">
        FIRMAS DE APROBACION DEL PRESUPUESTO
    </p>

    <?php
    // Firmas electrónicas del sistema unificado
    $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
    $firmaDelegado = ($firmasElectronicas ?? [])['delegado_sst'] ?? null;
    ?>

    <table width="100%" cellpadding="15" cellspacing="0" style="margin-top:20px;">
        <tr>
            <td width="50%" align="center" valign="bottom" style="padding:20px;">
                <table width="200" cellpadding="10" cellspacing="0" style="border:1px solid #ccc; background-color:#fafafa; min-height:80px;">
                    <tr>
                        <td align="center" height="60">
                            <?php if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])): ?>
                                <!-- Firma electrónica del sistema unificado -->
                                <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" width="120" height="50">
                            <?php elseif (!empty($presupuesto['firma_imagen'])): ?>
                                <!-- Firma legacy del presupuesto -->
                                <img src="<?= base_url('uploads/' . $presupuesto['firma_imagen']) ?>" width="120" height="50">
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                <p style="border-top:1px solid #000; padding-top:8px; font-weight:bold; font-size:9pt; margin-top:10px;">REPRESENTANTE LEGAL</p>
                <?php if ($firmaRepLegal): ?>
                    <p style="font-size:9pt; color:#666; margin:3px 0;"><?= esc($firmaRepLegal['solicitud']['firmante_nombre'] ?? $contexto['representante_legal_nombre'] ?? '') ?></p>
                    <?php if (!empty($firmaRepLegal['solicitud']['firmante_documento'])): ?>
                        <p style="font-size:9pt; color:#666; margin:3px 0;">C.C. <?= esc($firmaRepLegal['solicitud']['firmante_documento']) ?></p>
                    <?php endif; ?>
                    <p style="font-size:8pt; color:#999; margin:3px 0;">Fecha: <?= date('d/m/Y H:i', strtotime($firmaRepLegal['evidencia']['fecha_firma'] ?? $firmaRepLegal['solicitud']['updated_at'])) ?></p>
                <?php elseif (!empty($presupuesto['firmado_por'])): ?>
                    <p style="font-size:9pt; color:#666; margin:3px 0;"><?= esc($presupuesto['firmado_por']) ?></p>
                    <?php if (!empty($presupuesto['cedula_firmante'])): ?>
                        <p style="font-size:9pt; color:#666; margin:3px 0;">C.C. <?= esc($presupuesto['cedula_firmante']) ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="font-size:9pt; color:#666; margin:3px 0;"><?= esc($contexto['representante_legal_nombre'] ?? $cliente['representante_legal'] ?? '') ?></p>
                <?php endif; ?>
            </td>
            <td width="50%" align="center" valign="bottom" style="padding:20px;">
                <table width="200" cellpadding="10" cellspacing="0" style="border:1px solid #ccc; background-color:#fafafa; min-height:80px;">
                    <tr>
                        <td align="center" height="60">
                            <?php if ($firmaDelegado && !empty($firmaDelegado['evidencia']['firma_imagen'])): ?>
                                <!-- Firma electrónica del sistema unificado -->
                                <img src="<?= $firmaDelegado['evidencia']['firma_imagen'] ?>" width="120" height="50">
                            <?php elseif (!empty($presupuesto['firma_delegado_imagen'])): ?>
                                <!-- Firma legacy del presupuesto -->
                                <img src="<?= base_url('uploads/' . $presupuesto['firma_delegado_imagen']) ?>" width="120" height="50">
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                <p style="border-top:1px solid #000; padding-top:8px; font-weight:bold; font-size:9pt; margin-top:10px;">RESPONSABLE DEL SG-SST</p>
                <?php if ($firmaDelegado): ?>
                    <p style="font-size:9pt; color:#666; margin:3px 0;"><?= esc($firmaDelegado['solicitud']['firmante_nombre'] ?? $contexto['responsable_sst_nombre'] ?? $contexto['delegado_sst_nombre'] ?? '') ?></p>
                    <?php if (!empty($firmaDelegado['solicitud']['firmante_documento'])): ?>
                        <p style="font-size:9pt; color:#666; margin:3px 0;">C.C. <?= esc($firmaDelegado['solicitud']['firmante_documento']) ?></p>
                    <?php endif; ?>
                    <p style="font-size:8pt; color:#999; margin:3px 0;">Fecha: <?= date('d/m/Y H:i', strtotime($firmaDelegado['evidencia']['fecha_firma'] ?? $firmaDelegado['solicitud']['updated_at'])) ?></p>
                <?php elseif (!empty($presupuesto['firmado_delegado_por'])): ?>
                    <p style="font-size:9pt; color:#666; margin:3px 0;"><?= esc($presupuesto['firmado_delegado_por']) ?></p>
                <?php else: ?>
                    <p style="font-size:9pt; color:#666; margin:3px 0;"><?= esc($contexto['responsable_sst_nombre'] ?? $contexto['delegado_sst_nombre'] ?? '') ?></p>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- Control de Cambios -->
    <p style="background-color:#1a5f7a; color:#ffffff; padding:8px 12px; margin:30px 0 0 0; font-weight:bold; font-size:10pt;">
        CONTROL DE CAMBIOS
    </p>
    <table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;">
        <tr bgcolor="#f8f9fa" style="background-color:#f8f9fa;">
            <th width="15%" style="border:1px solid #dee2e6; text-align:center; font-size:9pt; font-weight:bold;">Version</th>
            <th width="20%" style="border:1px solid #dee2e6; text-align:center; font-size:9pt; font-weight:bold;">Fecha</th>
            <th width="65%" style="border:1px solid #dee2e6; text-align:left; font-size:9pt; font-weight:bold;">Descripcion del Cambio</th>
        </tr>
        <?php if (!empty($versiones)): ?>
            <?php foreach ($versiones as $version): ?>
                <tr>
                    <td style="border:1px solid #dee2e6; text-align:center; font-size:9pt;"><?= esc($version['version']) ?></td>
                    <td style="border:1px solid #dee2e6; text-align:center; font-size:9pt;"><?= date('d/m/Y', strtotime($version['fecha_autorizacion'] ?? $version['created_at'])) ?></td>
                    <td style="border:1px solid #dee2e6; text-align:left; font-size:9pt;"><?= esc($version['descripcion_cambio'] ?? 'Creacion del documento') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td style="border:1px solid #dee2e6; text-align:center; font-size:9pt;"><?= $versionDocumento ?? '001' ?></td>
                <td style="border:1px solid #dee2e6; text-align:center; font-size:9pt;"><?= date('d/m/Y') ?></td>
                <td style="border:1px solid #dee2e6; text-align:left; font-size:9pt;">Creacion del documento</td>
            </tr>
        <?php endif; ?>
    </table>

    <!-- Footer -->
    <p style="text-align:center; font-size:8pt; color:#666; margin-top:30px; border-top:1px solid #ddd; padding-top:10px;">
        <?= $codigoDocumento ?? 'FT-SST-001' ?> | <?= $tituloDocumento ?? 'Asignacion de Recursos para el SG-SST' ?> | <?= esc($cliente['nombre_cliente']) ?> | Ano <?= $anio ?>
    </p>
</body>
</html>
