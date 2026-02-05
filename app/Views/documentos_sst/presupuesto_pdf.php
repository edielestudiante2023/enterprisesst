<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $codigoDocumento ?? 'FT-SST-001' ?> Presupuesto SST <?= $anio ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.15;
            color: #333;
            padding: 20px 30px;
        }

        /* ============================================
           ENCABEZADO FORMAL - ESTILOS ESTÁNDAR
           ============================================ */
        .encabezado-formal {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .encabezado-formal td {
            border: 1px solid #333;
            vertical-align: middle;
        }
        .encabezado-logo {
            width: 120px;
            padding: 8px;
            text-align: center;
            background-color: #ffffff;
        }
        .encabezado-logo img {
            max-width: 100px;
            max-height: 60px;
            background-color: #ffffff;
        }
        .encabezado-titulo-central {
            text-align: center;
            padding: 0;
        }
        .encabezado-titulo-central .sistema {
            font-size: 10pt;
            font-weight: bold;
            padding: 6px 10px;
            border-bottom: 1px solid #333;
        }
        .encabezado-titulo-central .nombre-doc {
            font-size: 10pt;
            font-weight: bold;
            padding: 6px 10px;
        }
        .encabezado-info {
            width: 140px;
            padding: 0;
        }
        .encabezado-info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .encabezado-info-table td {
            border: none;
            border-bottom: 1px solid #333;
            padding: 3px 6px;
            font-size: 8pt;
        }
        .encabezado-info-table tr:last-child td {
            border-bottom: none;
        }
        .encabezado-info-table .label {
            font-weight: bold;
        }

        /* Info empresa */
        .info-empresa {
            margin-bottom: 20px;
            padding: 12px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
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
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #2c3e50;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 10pt;
        }
        .items-table th.monto {
            text-align: right;
            width: 120px;
        }
        .items-table td {
            border-bottom: 1px solid #dee2e6;
            padding: 8px;
            font-size: 9pt;
        }
        .items-table .categoria {
            background-color: #e9ecef;
            font-weight: bold;
            padding: 10px 8px;
            color: #1a5f7a;
        }
        .items-table .item-row td {
            padding-left: 20px;
        }
        .items-table .subtotal {
            background-color: #d4edda;
            font-weight: bold;
        }
        .items-table .total {
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
        .resumen-box {
            border: 2px solid #1a5f7a;
            padding: 15px;
            margin: 20px 0;
            background-color: #f8f9fa;
        }
        .resumen-box h3 {
            color: #1a5f7a;
            margin-bottom: 10px;
            font-size: 12pt;
        }
        .resumen-item {
            display: table;
            width: 100%;
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }
        .resumen-label {
            display: table-cell;
            width: 70%;
        }
        .resumen-valor {
            display: table-cell;
            width: 30%;
            text-align: right;
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        .resumen-total {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #1a5f7a;
            font-size: 14pt;
            font-weight: bold;
        }
        .resumen-total .resumen-valor {
            color: #1a5f7a;
            font-size: 14pt;
        }

        /* Firmas */
        .firmas-container {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        .firmas-titulo {
            text-align: center;
            font-weight: bold;
            margin-bottom: 30px;
            font-size: 11pt;
            color: #1a5f7a;
        }
        .firmas {
            width: 100%;
            margin-top: 20px;
        }
        .firmas td {
            width: 50%;
            text-align: center;
            padding: 20px 30px;
            vertical-align: bottom;
        }
        .firma-box {
            border: 1px solid #ccc;
            padding: 15px;
            min-height: 120px;
            margin-bottom: 10px;
            background-color: #fafafa;
        }
        .firma-imagen {
            max-height: 60px;
            max-width: 150px;
        }
        .firma-linea {
            border-top: 1px solid #000;
            margin-top: 60px;
            padding-top: 8px;
            font-weight: bold;
            font-size: 9pt;
        }
        .firma-nombre {
            font-size: 9pt;
            color: #666;
            margin-top: 5px;
        }
        .firma-fecha {
            font-size: 8pt;
            color: #999;
            margin-top: 3px;
        }

        /* Estado */
        .estado-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
        }
        .estado-aprobado {
            background-color: #d4edda;
            color: #155724;
        }
        .estado-pendiente {
            background-color: #fff3cd;
            color: #856404;
        }
        .estado-borrador {
            background-color: #e2e3e5;
            color: #383d41;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 30px;
            right: 30px;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Encabezado Formal Estándar -->
    <table class="encabezado-formal" cellpadding="0" cellspacing="0">
        <tr>
            <td class="encabezado-logo" rowspan="2">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" style="max-width:100px; max-height:60px;">
                <?php else: ?>
                    <div style="font-size:8pt; font-weight:bold;"><?= esc($cliente['nombre_cliente']) ?></div>
                <?php endif; ?>
            </td>
            <td class="encabezado-titulo-central">
                <div class="sistema">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
            </td>
            <td class="encabezado-info" rowspan="2">
                <table class="encabezado-info-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="label">Codigo:</td>
                        <td><?= $codigoDocumento ?? 'FT-SST-001' ?></td>
                    </tr>
                    <tr>
                        <td class="label">Version:</td>
                        <td><?= $versionDocumento ?? '001' ?></td>
                    </tr>
                    <tr>
                        <td class="label">Vigencia:</td>
                        <td><?= date('d/m/Y') ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="encabezado-titulo-central">
                <div class="nombre-doc"><?= esc(strtoupper($tituloDocumento ?? 'ASIGNACION DE RECURSOS PARA EL SG-SST')) ?></div>
            </td>
        </tr>
    </table>

    <!-- Informacion de la empresa -->
    <div class="info-empresa">
        <p><strong>Empresa:</strong> <?= esc($cliente['nombre_cliente']) ?></p>
        <p><strong>NIT:</strong> <?= esc($cliente['nit_cliente'] ?? 'N/A') ?></p>
        <p><strong>Periodo:</strong> Año <?= $anio ?></p>
        <p>
            <strong>Estado:</strong>
            <?php
            $estado = $presupuesto['estado'] ?? 'borrador';
            $estadoClass = match($estado) {
                'aprobado' => 'estado-aprobado',
                'pendiente_firma' => 'estado-pendiente',
                default => 'estado-borrador'
            };
            $estadoTexto = match($estado) {
                'aprobado' => 'APROBADO',
                'pendiente_firma' => 'PENDIENTE DE APROBACION',
                default => 'BORRADOR'
            };
            ?>
            <span class="estado-badge <?= $estadoClass ?>"><?= $estadoTexto ?></span>
            <?php if ($estado === 'aprobado' && !empty($presupuesto['fecha_aprobacion'])): ?>
                - <?= date('d/m/Y', strtotime($presupuesto['fecha_aprobacion'])) ?>
            <?php endif; ?>
        </p>
    </div>

    <!-- Tabla de Items -->
    <div class="seccion-titulo">DETALLE DE ASIGNACION DE RECURSOS</div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 8%;">Item</th>
                <th style="width: 52%;">Actividad / Descripcion</th>
                <th class="monto">Presupuestado</th>
                <th class="monto">Ejecutado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itemsPorCategoria as $codigoCat => $categoria): ?>
                <!-- Categoria -->
                <tr class="categoria">
                    <td colspan="4"><?= $codigoCat ?>. <?= esc($categoria['nombre']) ?></td>
                </tr>

                <!-- Items -->
                <?php foreach ($categoria['items'] as $item): ?>
                <tr class="item-row">
                    <td><?= esc($item['codigo_item']) ?></td>
                    <td>
                        <?= esc($item['actividad']) ?>
                        <?php if (!empty($item['descripcion'])): ?>
                            <br><small style="color: #666;"><?= esc($item['descripcion']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="monto">$<?= number_format($item['total_presupuestado'], 0, ',', '.') ?></td>
                    <td class="monto">$<?= number_format($item['total_ejecutado'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>

                <!-- Subtotal categoria -->
                <?php $totCat = $totales['por_categoria'][$codigoCat] ?? ['presupuestado' => 0, 'ejecutado' => 0]; ?>
                <tr class="subtotal">
                    <td colspan="2" style="text-align: right;">Subtotal <?= $codigoCat ?>:</td>
                    <td class="monto">$<?= number_format($totCat['presupuestado'], 0, ',', '.') ?></td>
                    <td class="monto">$<?= number_format($totCat['ejecutado'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>

            <!-- Total General -->
            <tr class="total">
                <td colspan="2" style="text-align: right;">TOTAL GENERAL:</td>
                <td class="monto">$<?= number_format($totales['general_presupuestado'], 0, ',', '.') ?></td>
                <td class="monto">$<?= number_format($totales['general_ejecutado'], 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Resumen -->
    <div class="resumen-box">
        <h3>RESUMEN PRESUPUESTO <?= $anio ?></h3>
        <?php foreach ($itemsPorCategoria as $codigoCat => $categoria):
            $totCat = $totales['por_categoria'][$codigoCat] ?? ['presupuestado' => 0];
        ?>
        <div class="resumen-item">
            <span class="resumen-label"><?= $codigoCat ?>. <?= esc($categoria['nombre']) ?></span>
            <span class="resumen-valor">$<?= number_format($totCat['presupuestado'], 0, ',', '.') ?></span>
        </div>
        <?php endforeach; ?>
        <div class="resumen-item resumen-total">
            <span class="resumen-label">TOTAL PRESUPUESTO APROBADO</span>
            <span class="resumen-valor">$<?= number_format($totales['general_presupuestado'], 0, ',', '.') ?></span>
        </div>
    </div>

    <!-- Firmas de Aprobacion -->
    <div class="firmas-container">
        <div class="firmas-titulo">FIRMAS DE APROBACION DEL PRESUPUESTO</div>

        <?php
        // Firmas electrónicas del sistema unificado
        $firmaConsultorElectronica = ($firmasElectronicas ?? [])['consultor_sst'] ?? null;
        $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
        $firmaDelegado = ($firmasElectronicas ?? [])['delegado_sst'] ?? null;
        // Datos del consultor
        $consultorNombre = $consultor['nombre_consultor'] ?? '';
        $consultorLicencia = $consultor['numero_licencia'] ?? '';
        // Firma física del consultor (del perfil)
        $firmaConsultorFisica = $consultor['firma_consultor'] ?? '';
        // Para PDF, convertir firma física a base64 si existe
        $firmaConsultorBase64 = '';
        if (!empty($firmaConsultorFisica)) {
            $rutaFirma = FCPATH . 'uploads/' . $firmaConsultorFisica;
            if (file_exists($rutaFirma)) {
                $firmaConsultorBase64 = 'data:image/' . pathinfo($rutaFirma, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($rutaFirma));
            }
        }
        ?>

        <table class="firmas" style="width: 100%;">
            <tr>
                <!-- ELABORÓ: Consultor SST -->
                <td style="width: 33.33%;">
                    <div class="firma-box">
                        <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                            <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" class="firma-imagen">
                        <?php elseif (!empty($firmaConsultorBase64)): ?>
                            <img src="<?= $firmaConsultorBase64 ?>" class="firma-imagen">
                        <?php endif; ?>
                    </div>
                    <div class="firma-linea">ELABORÓ</div>
                    <div class="firma-nombre"><?= esc($consultorNombre) ?></div>
                    <?php if (!empty($consultorLicencia)): ?>
                        <div class="firma-nombre" style="font-size: 7pt;">Lic. SST: <?= esc($consultorLicencia) ?></div>
                    <?php endif; ?>
                    <div class="firma-nombre" style="font-size: 7pt; color: #666;">Consultor SST</div>
                </td>
                <!-- APROBÓ: Representante Legal -->
                <td style="width: 33.33%;">
                    <div class="firma-box">
                        <?php if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])): ?>
                            <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" class="firma-imagen">
                        <?php elseif (!empty($presupuesto['firma_imagen'])): ?>
                            <img src="<?= base_url('uploads/' . $presupuesto['firma_imagen']) ?>" class="firma-imagen">
                        <?php endif; ?>
                    </div>
                    <div class="firma-linea">APROBÓ</div>
                    <?php if ($firmaRepLegal): ?>
                        <div class="firma-nombre"><?= esc($firmaRepLegal['solicitud']['firmante_nombre'] ?? $contexto['representante_legal_nombre'] ?? '') ?></div>
                        <?php if (!empty($firmaRepLegal['solicitud']['firmante_documento'])): ?>
                            <div class="firma-nombre">C.C. <?= esc($firmaRepLegal['solicitud']['firmante_documento']) ?></div>
                        <?php endif; ?>
                        <div class="firma-fecha">Fecha: <?= date('d/m/Y H:i', strtotime($firmaRepLegal['evidencia']['fecha_firma'] ?? $firmaRepLegal['solicitud']['updated_at'])) ?></div>
                    <?php elseif (!empty($presupuesto['firmado_por'])): ?>
                        <div class="firma-nombre"><?= esc($presupuesto['firmado_por']) ?></div>
                        <?php if (!empty($presupuesto['cedula_firmante'])): ?>
                            <div class="firma-nombre">C.C. <?= esc($presupuesto['cedula_firmante']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($presupuesto['fecha_aprobacion'])): ?>
                            <div class="firma-fecha">Fecha: <?= date('d/m/Y H:i', strtotime($presupuesto['fecha_aprobacion'])) ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="firma-nombre"><?= esc($contexto['representante_legal_nombre'] ?? $cliente['representante_legal'] ?? '') ?></div>
                    <?php endif; ?>
                    <div class="firma-nombre" style="font-size: 7pt; color: #666;">Representante Legal</div>
                </td>
                <!-- REVISÓ: Responsable SG-SST -->
                <td style="width: 33.33%;">
                    <div class="firma-box">
                        <?php if ($firmaDelegado && !empty($firmaDelegado['evidencia']['firma_imagen'])): ?>
                            <img src="<?= $firmaDelegado['evidencia']['firma_imagen'] ?>" class="firma-imagen">
                        <?php elseif (!empty($presupuesto['firma_delegado_imagen'])): ?>
                            <img src="<?= base_url('uploads/' . $presupuesto['firma_delegado_imagen']) ?>" class="firma-imagen">
                        <?php endif; ?>
                    </div>
                    <div class="firma-linea">REVISÓ</div>
                    <?php if ($firmaDelegado): ?>
                        <div class="firma-nombre"><?= esc($firmaDelegado['solicitud']['firmante_nombre'] ?? $contexto['responsable_sst_nombre'] ?? $contexto['delegado_sst_nombre'] ?? '') ?></div>
                        <?php if (!empty($firmaDelegado['solicitud']['firmante_documento'])): ?>
                            <div class="firma-nombre">C.C. <?= esc($firmaDelegado['solicitud']['firmante_documento']) ?></div>
                        <?php endif; ?>
                        <div class="firma-fecha">Fecha: <?= date('d/m/Y H:i', strtotime($firmaDelegado['evidencia']['fecha_firma'] ?? $firmaDelegado['solicitud']['updated_at'])) ?></div>
                    <?php elseif (!empty($presupuesto['firmado_delegado_por'])): ?>
                        <div class="firma-nombre"><?= esc($presupuesto['firmado_delegado_por']) ?></div>
                        <?php if (!empty($presupuesto['fecha_aprobacion_delegado'])): ?>
                            <div class="firma-fecha">Fecha: <?= date('d/m/Y H:i', strtotime($presupuesto['fecha_aprobacion_delegado'])) ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="firma-nombre"><?= esc($contexto['responsable_sst_nombre'] ?? $contexto['delegado_sst_nombre'] ?? '') ?></div>
                    <?php endif; ?>
                    <div class="firma-nombre" style="font-size: 7pt; color: #666;">Responsable SG-SST</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ============================================== -->
    <!-- SECCION: CONTROL DE CAMBIOS -->
    <!-- ============================================== -->
    <div class="seccion" style="margin-top: 25px;">
        <div style="background-color: #0d6efd; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
            CONTROL DE CAMBIOS
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <th style="width: 80px; background-color: #e9ecef; color: #333;">Version</th>
                <th style="background-color: #e9ecef; color: #333;">Descripcion del Cambio</th>
                <th style="width: 90px; background-color: #e9ecef; color: #333;">Fecha</th>
            </tr>
            <?php if (!empty($versiones)): ?>
                <?php foreach ($versiones as $idx => $ver): ?>
                <tr style="<?= $idx % 2 === 0 ? '' : 'background-color: #f8f9fa;' ?>">
                    <td style="text-align: center; font-weight: bold;"><?= esc($ver['version_texto']) ?></td>
                    <td><?= esc($ver['descripcion_cambio']) ?></td>
                    <td style="text-align: center;"><?= date('d/m/Y', strtotime($ver['fecha_autorizacion'])) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td style="text-align: center; font-weight: bold;">1.0</td>
                    <td>Elaboracion inicial del presupuesto SST</td>
                    <td style="text-align: center;"><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <?= $codigoDocumento ?? 'FT-SST-001' ?> | <?= $tituloDocumento ?? 'Asignacion de Recursos para el SG-SST' ?> | <?= esc($cliente['nombre_cliente']) ?> | Año <?= $anio ?>
    </div>
</body>
</html>
