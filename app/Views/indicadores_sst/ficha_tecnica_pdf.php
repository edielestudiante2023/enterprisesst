<?php
/**
 * Ficha Tecnica de Indicador SST - Plantilla DomPDF
 *
 * Variables recibidas:
 * - $indicador     array con campos del indicador
 * - $periodos      array de ['periodo','label','numerador','denominador','resultado','cumple_meta']
 * - $acumulado     array con numerador, denominador, resultado
 * - $anio          int
 * - $cliente       array con nombre_cliente, nit_cliente
 * - $consultor     array con nombre_consultor o primer_nombre+primer_apellido, cargo, cedula_consultor, licencia_sst
 * - $consecutivo   int
 * - $logoBase64    string base64
 * - $firmaConsultorBase64  string base64
 * - $orientacion   'portrait' o 'landscape'
 * - $chartBase64   string base64 (puede estar vacio)
 * - $repLegalNombre string nombre del representante legal
 */

// Datos del consultor
$consultorNombre = $consultor['nombre_consultor']
    ?? trim(($consultor['primer_nombre'] ?? '') . ' ' . ($consultor['primer_apellido'] ?? ''))
    ?: '';
$consultorCargo = 'Consultor SST';
$consultorCedula = $consultor['cedula_consultor'] ?? '';
$consultorLicencia = $consultor['licencia_sst'] ?? $consultor['numero_licencia'] ?? '';

// Representante legal (usa variable del controller)
$repLegalNombre = $repLegalNombre
    ?? $contexto['representante_legal_nombre']
    ?? $cliente['nombre_rep_legal']
    ?? $cliente['representante_legal']
    ?? '';

// Firmantes dinamicos segun contexto
$estandares = $contexto['estandares_aplicables'] ?? 60;
$requiereDelegado = !empty($contexto['requiere_delegado_sst']);
$delegadoNombre = $contexto['delegado_sst_nombre'] ?? '';
$delegadoCargo = $contexto['delegado_sst_cargo'] ?? 'Delegado SST';

// Codigo del documento
$codigoDoc = 'FT-IND-' . str_pad($consecutivo ?? 1, 3, '0', STR_PAD_LEFT);

// Periodicidad para ajustar estilos de tabla
$periodicidad = $indicador['periodicidad'] ?? 'trimestral';
$esMensual = ($periodicidad === 'mensual');
$fontSize = $esMensual ? '8pt' : '9pt';
$cellPadding = $esMensual ? '3px 4px' : '5px 8px';

// Tipo indicador legible
$tiposIndicador = [
    'estructura' => 'Estructura',
    'proceso'    => 'Proceso',
    'resultado'  => 'Resultado',
];
$tipoLabel = $tiposIndicador[$indicador['tipo_indicador'] ?? ''] ?? ($indicador['tipo_indicador'] ?? 'N/A');

// PHVA legible
$fasesPhva = [
    'planear'   => 'PLANEAR',
    'hacer'     => 'HACER',
    'verificar' => 'VERIFICAR',
    'actuar'    => 'ACTUAR',
];
$phvaLabel = $fasesPhva[$indicador['phva'] ?? ''] ?? ($indicador['phva'] ?? 'N/A');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha Tecnica - <?= esc($indicador['nombre_indicador'] ?? 'Indicador SST') ?></title>
    <style>
        @page {
            size: letter <?= $orientacion ?? 'portrait' ?>;
            margin: 2cm 1.5cm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.15;
        }

        /* Encabezado formal */
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
            width: 100px;
            padding: 8px;
            text-align: center;
            background-color: #ffffff;
        }

        .encabezado-logo img {
            max-width: 80px;
            max-height: 50px;
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
            width: 130px;
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

        /* Barra de titulo de seccion */
        .seccion-barra {
            background-color: #0d6efd;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 10pt;
            margin-top: 12px;
        }

        /* Tablas de contenido */
        table.tabla-contenido {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 9pt;
        }

        table.tabla-contenido th,
        table.tabla-contenido td {
            border: 1px solid #999;
            padding: 5px 8px;
        }

        table.tabla-contenido th {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        /* Tabla info 2 columnas */
        table.tabla-info {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        table.tabla-info th,
        table.tabla-info td {
            border: 1px solid #999;
            padding: 5px 8px;
            font-size: 9pt;
        }

        table.tabla-info th {
            background-color: #e9ecef;
            color: #333;
            font-weight: bold;
            width: 30%;
            text-align: left;
        }

        table.tabla-info td {
            width: 70%;
        }

        /* Pie de documento */
        .pie-documento {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- ============================================== -->
    <!-- ENCABEZADO FORMAL                              -->
    <!-- ============================================== -->
    <table class="encabezado-formal" cellpadding="0" cellspacing="0">
        <tr>
            <!-- Logo -->
            <td class="encabezado-logo" rowspan="2" style="width:100px;" valign="middle" align="center">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" alt="Logo" style="max-width:80px;max-height:50px;">
                <?php else: ?>
                    <div style="font-size: 8pt;">
                        <strong><?= esc($cliente['nombre_cliente']) ?></strong>
                    </div>
                <?php endif; ?>
            </td>
            <!-- Titulo sistema -->
            <td class="encabezado-titulo-central" valign="middle">
                <div class="sistema">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
            </td>
            <!-- Info documento -->
            <td class="encabezado-info" rowspan="2" style="width:130px;" valign="middle">
                <table class="encabezado-info-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="label">Codigo:</td>
                        <td><?= esc($codigoDoc) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Version:</td>
                        <td>001</td>
                    </tr>
                    <tr>
                        <td class="label">Fecha:</td>
                        <td><?= date('d/m/Y') ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <!-- Nombre del documento -->
            <td class="encabezado-titulo-central" valign="middle">
                <div class="nombre-doc">FICHA TECNICA DE INDICADOR</div>
            </td>
        </tr>
    </table>

    <!-- ============================================== -->
    <!-- SECCION 1: INFORMACION DEL INDICADOR           -->
    <!-- ============================================== -->
    <div class="seccion-barra">1. INFORMACION DEL INDICADOR</div>
    <table class="tabla-info" style="margin-top: 0;">
        <tr>
            <th>Nombre del Indicador</th>
            <td><strong><?= esc($indicador['nombre_indicador'] ?? '') ?></strong></td>
        </tr>
        <tr>
            <th>Empresa</th>
            <td><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?></td>
        </tr>
        <tr>
            <th>Tipo de Indicador</th>
            <td><?= esc($tipoLabel) ?></td>
        </tr>
        <tr>
            <th>Ciclo PHVA</th>
            <td><?= esc($phvaLabel) ?></td>
        </tr>
        <tr>
            <th>Numeral Res. 0312</th>
            <td><?= esc($indicador['numeral_resolucion'] ?? 'N/A') ?></td>
        </tr>
        <tr>
            <th>Definicion</th>
            <td><?= esc($indicador['definicion'] ?? 'N/A') ?></td>
        </tr>
        <tr>
            <th>Interpretacion</th>
            <td><?= esc($indicador['interpretacion'] ?? 'N/A') ?></td>
        </tr>
        <tr>
            <th>Formula</th>
            <td><?= esc($indicador['formula'] ?? 'N/A') ?></td>
        </tr>
        <tr>
            <th>Meta</th>
            <td><?= esc(($indicador['meta'] ?? 'N/A') . ' ' . ($indicador['unidad_medida'] ?? '')) ?></td>
        </tr>
        <tr>
            <th>Periodicidad</th>
            <td><?= esc(ucfirst($periodicidad)) ?></td>
        </tr>
        <tr>
            <th>Origen de Datos</th>
            <td><?= esc($indicador['origen_datos'] ?? 'N/A') ?></td>
        </tr>
        <tr>
            <th>Cargo Responsable</th>
            <td><?= esc($indicador['cargo_responsable'] ?? 'N/A') ?></td>
        </tr>
        <tr>
            <th>Cargos que deben conocer el resultado</th>
            <td><?= esc($indicador['cargos_conocer_resultado'] ?? 'N/A') ?></td>
        </tr>
    </table>

    <!-- ============================================== -->
    <!-- SECCION 2: MEDICION                            -->
    <!-- ============================================== -->
    <div class="seccion-barra">2. MEDICION - AÑO <?= esc($anio) ?></div>
    <table style="width: 100%; border-collapse: collapse; margin-top: 0; font-size: <?= $fontSize ?>;">
        <tr>
            <!-- Columna Componente -->
            <th style="border: 1px solid #999; padding: <?= $cellPadding ?>; background-color: #0d6efd; color: white; font-weight: bold; text-align: center; width: 90px;">Componente</th>
            <!-- Columnas de periodos -->
            <?php foreach ($periodos as $p): ?>
                <th style="border: 1px solid #999; padding: <?= $cellPadding ?>; background-color: #0d6efd; color: white; font-weight: bold; text-align: center;"><?= esc($p['label']) ?></th>
            <?php endforeach; ?>
            <!-- Columna acumulado -->
            <th style="border: 1px solid #999; padding: <?= $cellPadding ?>; background-color: #0d6efd; color: white; font-weight: bold; text-align: center;">ACUM</th>
        </tr>
        <!-- Fila Numerador -->
        <tr>
            <td style="border: 1px solid #999; padding: <?= $cellPadding ?>; background-color: #e9ecef; font-weight: bold;">Numerador</td>
            <?php foreach ($periodos as $p): ?>
                <td style="border: 1px solid #999; padding: <?= $cellPadding ?>; text-align: center;">
                    <?= $p['numerador'] !== null ? esc(number_format((float)$p['numerador'], 1)) : '-' ?>
                </td>
            <?php endforeach; ?>
            <td style="border: 1px solid #999; padding: <?= $cellPadding ?>; text-align: center; font-weight: bold;">
                <?= $acumulado['numerador'] !== null ? esc(number_format((float)$acumulado['numerador'], 1)) : '-' ?>
            </td>
        </tr>
        <!-- Fila Denominador -->
        <tr>
            <td style="border: 1px solid #999; padding: <?= $cellPadding ?>; background-color: #e9ecef; font-weight: bold;">Denominador</td>
            <?php foreach ($periodos as $p): ?>
                <td style="border: 1px solid #999; padding: <?= $cellPadding ?>; text-align: center;">
                    <?= $p['denominador'] !== null ? esc(number_format((float)$p['denominador'], 1)) : '-' ?>
                </td>
            <?php endforeach; ?>
            <td style="border: 1px solid #999; padding: <?= $cellPadding ?>; text-align: center; font-weight: bold;">
                <?= $acumulado['denominador'] !== null ? esc(number_format((float)$acumulado['denominador'], 1)) : '-' ?>
            </td>
        </tr>
        <!-- Fila Resultado (con colores) -->
        <tr>
            <td style="border: 1px solid #999; padding: <?= $cellPadding ?>; background-color: #e9ecef; font-weight: bold;">Resultado</td>
            <?php foreach ($periodos as $p): ?>
                <?php
                $bgColor = '';
                if ($p['resultado'] !== null && $p['cumple_meta'] !== null) {
                    $bgColor = ($p['cumple_meta'] == 1) ? 'background-color: #d4edda;' : 'background-color: #f8d7da;';
                }
                ?>
                <td style="border: 1px solid #999; padding: <?= $cellPadding ?>; text-align: center; font-weight: bold; <?= $bgColor ?>">
                    <?= $p['resultado'] !== null ? esc(number_format((float)$p['resultado'], 2)) : '-' ?>
                </td>
            <?php endforeach; ?>
            <td style="border: 1px solid #999; padding: <?= $cellPadding ?>; text-align: center; font-weight: bold;">
                <?= $acumulado['resultado'] !== null ? esc(number_format((float)$acumulado['resultado'], 2)) : '-' ?>
            </td>
        </tr>
        <!-- Fila Meta -->
        <tr>
            <td style="border: 1px solid #999; padding: <?= $cellPadding ?>; background-color: #e9ecef; font-weight: bold;">Meta</td>
            <?php $meta = $indicador['meta'] ?? null; ?>
            <?php foreach ($periodos as $p): ?>
                <td style="border: 1px solid #999; padding: <?= $cellPadding ?>; text-align: center; color: #666;">
                    <?= $meta !== null ? esc(number_format((float)$meta, 1)) : '-' ?>
                </td>
            <?php endforeach; ?>
            <td style="border: 1px solid #999; padding: <?= $cellPadding ?>; text-align: center; font-weight: bold; color: #666;">
                <?= $meta !== null ? esc(number_format((float)$meta, 1)) : '-' ?>
            </td>
        </tr>
    </table>

    <!-- ============================================== -->
    <!-- SECCION 3: GRAFICA                             -->
    <!-- ============================================== -->
    <div class="seccion-barra">3. GRAFICA</div>
    <div style="text-align: center; padding: 10px 0;">
        <?php if (!empty($chartBase64)): ?>
            <img src="<?= $chartBase64 ?>" alt="Grafica del indicador" style="max-width: 100%; max-height: 280px;">
        <?php else: ?>
            <div style="padding: 30px; color: #999; font-style: italic; border: 1px dashed #ccc; margin: 10px 0;">
                Grafica disponible en vista web
            </div>
        <?php endif; ?>
    </div>

    <!-- ============================================== -->
    <!-- SECCION 4: ANALISIS DE DATOS                   -->
    <!-- ============================================== -->
    <div class="seccion-barra">4. ANALISIS DE DATOS</div>
    <div style="padding: 8px 12px; border: 1px solid #999; border-top: none; font-size: 9pt; text-align: justify; line-height: 1.3;">
        <?php if (!empty($indicador['analisis_datos'])): ?>
            <?= esc($indicador['analisis_datos']) ?>
        <?php else: ?>
            <span style="color: #999; font-style: italic;">Pendiente de analisis</span>
        <?php endif; ?>
    </div>

    <!-- ============================================== -->
    <!-- SECCION 5: SEGUIMIENTO                         -->
    <!-- ============================================== -->
    <div class="seccion-barra">5. SEGUIMIENTO</div>
    <table class="tabla-info" style="margin-top: 0;">
        <tr>
            <th style="width: 30%;">Requiere Plan de Accion</th>
            <td>
                <?php
                $requierePlan = $indicador['requiere_plan_accion'] ?? null;
                if ($requierePlan === null || $requierePlan === '') {
                    echo 'N/A';
                } elseif ($requierePlan == 1) {
                    echo '<strong style="color: #dc3545;">SI</strong>';
                } else {
                    echo '<strong style="color: #198754;">NO</strong>';
                }
                ?>
            </td>
        </tr>
        <tr>
            <th>Numero de Accion</th>
            <td><?= esc($indicador['numero_accion'] ?? 'N/A') ?></td>
        </tr>
        <tr>
            <th>Acciones de Mejora</th>
            <td><?= !empty($indicador['acciones_mejora']) ? esc($indicador['acciones_mejora']) : 'N/A' ?></td>
        </tr>
        <tr>
            <th>Observaciones</th>
            <td><?= !empty($indicador['observaciones']) ? esc($indicador['observaciones']) : 'N/A' ?></td>
        </tr>
    </table>

    <!-- ============================================== -->
    <!-- SECCION: FIRMAS DE APROBACION - Dinamico       -->
    <!-- ============================================== -->
    <div style="margin-top: 25px;">
        <div style="background-color: #198754; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
            FIRMAS DE APROBACION
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <?php if ($requiereDelegado): ?>
            <!-- 3 firmantes -->
            <tr>
                <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Elaboro / Consultor SST</th>
                <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Reviso / Delegado SST</th>
                <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Aprobo / Representante Legal</th>
            </tr>
            <tr>
                <td style="vertical-align: top; padding: 12px; height: 100px;">
                    <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Nombre:</strong> <?= !empty($consultorNombre) ? esc($consultorNombre) : '________________________' ?></div>
                    <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Cargo:</strong> <?= esc($consultorCargo) ?></div>
                    <?php if (!empty($consultorLicencia)): ?>
                        <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Licencia SST:</strong> <?= esc($consultorLicencia) ?></div>
                    <?php endif; ?>
                </td>
                <td style="vertical-align: top; padding: 12px; height: 100px;">
                    <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Nombre:</strong> <?= !empty($delegadoNombre) ? esc($delegadoNombre) : '________________________' ?></div>
                    <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Cargo:</strong> <?= esc($delegadoCargo) ?></div>
                </td>
                <td style="vertical-align: top; padding: 12px; height: 100px;">
                    <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Nombre:</strong> <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '________________________' ?></div>
                    <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Cargo:</strong> Representante Legal</div>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 12px; text-align: center; vertical-align: bottom;">
                    <?php if (!empty($firmaConsultorBase64)): ?>
                        <img src="<?= $firmaConsultorBase64 ?>" alt="Firma Consultor" style="max-height: 56px; max-width: 168px;"><br>
                    <?php endif; ?>
                    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                        <small style="color: #666;">Firma</small>
                    </div>
                </td>
                <td style="padding: 10px 12px; text-align: center; vertical-align: bottom;">
                    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                        <small style="color: #666;">Firma</small>
                    </div>
                </td>
                <td style="padding: 10px 12px; text-align: center; vertical-align: bottom;">
                    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                        <small style="color: #666;">Firma</small>
                    </div>
                </td>
            </tr>
            <?php else: ?>
            <!-- 2 firmantes -->
            <tr>
                <th style="width: 50%; background-color: #e9ecef; color: #333;">Elaboro / Consultor SST</th>
                <th style="width: 50%; background-color: #e9ecef; color: #333;">Aprobo / Representante Legal</th>
            </tr>
            <tr>
                <td style="vertical-align: top; padding: 12px; height: 100px;">
                    <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Nombre:</strong> <?= !empty($consultorNombre) ? esc($consultorNombre) : '________________________' ?></div>
                    <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Cargo:</strong> <?= esc($consultorCargo) ?></div>
                    <?php if (!empty($consultorCedula)): ?>
                        <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Documento:</strong> <?= esc($consultorCedula) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($consultorLicencia)): ?>
                        <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Licencia SST:</strong> <?= esc($consultorLicencia) ?></div>
                    <?php endif; ?>
                </td>
                <td style="vertical-align: top; padding: 12px; height: 100px;">
                    <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Nombre:</strong> <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '________________________' ?></div>
                    <div style="margin-bottom: 5px; font-size: 9pt;"><strong>Cargo:</strong> Representante Legal</div>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 12px; text-align: center; vertical-align: bottom;">
                    <?php if (!empty($firmaConsultorBase64)): ?>
                        <img src="<?= $firmaConsultorBase64 ?>" alt="Firma Consultor" style="max-height: 56px; max-width: 168px;"><br>
                    <?php endif; ?>
                    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                        <small style="color: #666;">Firma</small>
                    </div>
                </td>
                <td style="padding: 10px 12px; text-align: center; vertical-align: bottom;">
                    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                        <small style="color: #666;">Firma</small>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- ============================================== -->
    <!-- SECCION: CONTROL DE CAMBIOS - con $versiones   -->
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
                <td>Elaboracion inicial de la ficha tecnica del indicador</td>
                <td style="text-align: center;"><?= date('d/m/Y') ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Pie de documento -->
    <div class="pie-documento">
        <p>Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
        <p><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?></p>
    </div>
</body>
</html>
