<?php
/**
 * Matriz de Objetivos y Metas del SG-SST - Plantilla DomPDF (Landscape)
 *
 * Variables recibidas:
 * - $cliente       array con id_cliente, nombre_cliente, nit_cliente, logo
 * - $indicadores   array de indicadores, cada uno con: nombre_indicador, tipo_indicador, categoria,
 *                  formula, meta, unidad_medida, periodicidad, cumple_meta, id_indicador,
 *                  y periodos_data array de ['label' => 'Trim I', 'resultado' => float|null, 'cumple' => 1|0|null]
 * - $anio          int anno
 * - $logoBase64    string base64
 */

$fechaActual = date('d/m/Y');

// Agrupar indicadores por tipo_indicador
$grupos = [
    'estructura' => [],
    'proceso'    => [],
    'resultado'  => [],
];

foreach ($indicadores as $ind) {
    $tipo = strtolower($ind['tipo_indicador'] ?? 'proceso');
    if (!isset($grupos[$tipo])) {
        $grupos[$tipo] = [];
    }
    $grupos[$tipo][] = $ind;
}

$tipoConfig = [
    'estructura' => ['label' => 'ESTRUCTURA', 'letter' => 'E'],
    'proceso'    => ['label' => 'PROCESO',    'letter' => 'P'],
    'resultado'  => ['label' => 'RESULTADO',  'letter' => 'R'],
];

// Obtener labels de periodos del indicador con MÁS periodos (mensual=12 > trimestral=4 > semestral=2 > anual=1)
$periodLabels = [];
foreach ($indicadores as $ind) {
    if (!empty($ind['periodos_data']) && count($ind['periodos_data']) > count($periodLabels)) {
        $periodLabels = array_map(fn($pd) => $pd['label'] ?? '', $ind['periodos_data']);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Matriz Objetivos y Metas SG-SST - <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
    <style>
        @page {
            size: letter landscape;
            margin: 1.5cm 1cm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 8pt;
            color: #333;
            line-height: 1.2;
        }

        /* ============================== */
        /* ENCABEZADO FORMAL              */
        /* ============================== */
        .encabezado-formal {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .encabezado-formal td {
            border: 1px solid #333;
            vertical-align: middle;
        }

        .encabezado-logo {
            width: 80px;
            padding: 6px;
            text-align: center;
            background-color: #ffffff;
        }

        .encabezado-logo img {
            max-width: 70px;
            max-height: 45px;
        }

        .encabezado-titulo-central {
            text-align: center;
            padding: 0;
        }

        .encabezado-titulo-central .sistema {
            font-size: 8pt;
            font-weight: bold;
            padding: 5px 8px;
            border-bottom: 1px solid #333;
        }

        .encabezado-titulo-central .nombre-doc {
            font-size: 9pt;
            font-weight: bold;
            padding: 5px 8px;
        }

        .encabezado-info {
            width: 120px;
            padding: 0;
        }

        .encabezado-info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .encabezado-info-table td {
            border: none;
            border-bottom: 1px solid #333;
            padding: 2px 5px;
            font-size: 7pt;
        }

        .encabezado-info-table tr:last-child td {
            border-bottom: none;
        }

        .encabezado-info-table .label {
            font-weight: bold;
        }

        /* ============================== */
        /* MAIN TABLE                     */
        /* ============================== */
        .tabla-matriz {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        .tabla-matriz th {
            background-color: #0d6efd;
            color: #fff;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding: 4px 3px;
            border: 1px solid #999;
            font-size: 7pt;
        }

        .tabla-matriz td {
            border: 1px solid #999;
            padding: 3px 4px;
            vertical-align: middle;
            font-size: 8pt;
        }

        /* Group header row */
        .grupo-header td {
            background-color: #e9ecef;
            font-weight: bold;
            font-size: 8pt;
            padding: 4px 6px;
            border: 1px solid #999;
            border-top: 2px solid #adb5bd;
        }

        /* Resultado cells */
        .celda-cumple {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
            text-align: center;
        }

        .celda-no-cumple {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
            text-align: center;
        }

        .celda-sin-dato {
            background-color: #f8f9fa;
            color: #6c757d;
            text-align: center;
        }

        /* Cumple badges */
        .badge-si {
            background-color: #198754;
            color: #fff;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }

        .badge-no {
            background-color: #dc3545;
            color: #fff;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }

        .badge-na {
            background-color: #6c757d;
            color: #fff;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }

        /* Tipo badges */
        .tipo-e {
            background-color: #0dcaf0;
            color: #000;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 7pt;
            font-weight: bold;
        }

        .tipo-p {
            background-color: #ffc107;
            color: #000;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 7pt;
            font-weight: bold;
        }

        .tipo-r {
            background-color: #198754;
            color: #fff;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 7pt;
            font-weight: bold;
        }

        /* ============================== */
        /* LEGEND                         */
        /* ============================== */
        .leyenda {
            margin-top: 8px;
            font-size: 7pt;
        }

        .leyenda-color {
            display: inline-block;
            width: 12px;
            height: 8px;
            border: 1px solid #999;
            vertical-align: middle;
            margin-right: 3px;
        }

        /* ============================== */
        /* CONTROL DE CAMBIOS             */
        /* ============================== */
        .seccion-barra {
            background-color: #0d6efd;
            color: white;
            padding: 5px 8px;
            font-weight: bold;
            font-size: 8pt;
            margin-top: 12px;
        }

        .tabla-cambios {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        .tabla-cambios th,
        .tabla-cambios td {
            border: 1px solid #999;
            padding: 3px 6px;
        }

        .tabla-cambios th {
            background-color: #e9ecef;
            color: #333;
            font-weight: bold;
            text-align: center;
        }

        /* ============================== */
        /* FOOTER                         */
        /* ============================== */
        .pie-documento {
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 7pt;
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
            <td class="encabezado-logo" rowspan="2" style="width: 80px;" valign="middle" align="center">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" alt="Logo" style="max-width: 70px; max-height: 45px;">
                <?php else: ?>
                    <div style="font-size: 7pt;">
                        <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                    </div>
                <?php endif; ?>
            </td>
            <!-- Titulo sistema -->
            <td class="encabezado-titulo-central" valign="middle">
                <div class="sistema">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
            </td>
            <!-- Info documento -->
            <td class="encabezado-info" rowspan="2" style="width: 120px;" valign="middle">
                <table class="encabezado-info-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="label">Codigo:</td>
                        <td>MA-SST-OBJ</td>
                    </tr>
                    <tr>
                        <td class="label">Version:</td>
                        <td>001</td>
                    </tr>
                    <tr>
                        <td class="label">Fecha:</td>
                        <td><?= $fechaActual ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <!-- Nombre del documento -->
            <td class="encabezado-titulo-central" valign="middle">
                <div class="nombre-doc">MATRIZ DE OBJETIVOS Y METAS DEL SG-SST</div>
            </td>
        </tr>
    </table>

    <!-- Subtitulo con empresa y periodo -->
    <div style="text-align: center; font-size: 8pt; margin-bottom: 8px;">
        <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
        <?php if (!empty($cliente['nit_cliente'])): ?>
            - NIT: <?= esc($cliente['nit_cliente']) ?>
        <?php endif; ?>
        &nbsp;&nbsp;|&nbsp;&nbsp;Periodo: <strong><?= esc($anio) ?></strong>
    </div>

    <!-- ============================================== -->
    <!-- TABLA PRINCIPAL                                -->
    <!-- ============================================== -->
    <table class="tabla-matriz" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 25px;">N&deg;</th>
                <th style="text-align: left;">Indicador</th>
                <th style="width: 40px;">Tipo</th>
                <th style="width: 50px;">Meta</th>
                <th style="width: 50px;">Period.</th>
                <?php foreach ($periodLabels as $label): ?>
                    <th><?= esc($label) ?></th>
                <?php endforeach; ?>
                <th style="width: 40px;">Cumple</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $consecutivo = 0;
            foreach ($tipoConfig as $tipoKey => $tipoCfg):
                if (empty($grupos[$tipoKey])) continue;
            ?>
                <!-- Group header -->
                <tr class="grupo-header">
                    <td colspan="<?= 5 + count($periodLabels) + 1 ?>">
                        <?= esc($tipoCfg['label']) ?>
                        (<?= count($grupos[$tipoKey]) ?> indicadores)
                    </td>
                </tr>
                <?php foreach ($grupos[$tipoKey] as $ind):
                    $consecutivo++;
                    $cumpleGlobal = $ind['cumple_meta'] ?? null;

                    // Tipo badge class
                    $tipoBadgeClass = 'tipo-' . strtolower($tipoCfg['letter']);
                ?>
                <tr>
                    <!-- N -->
                    <td style="text-align: center; font-weight: bold;"><?= $consecutivo ?></td>
                    <!-- Indicador -->
                    <td style="text-align: left;">
                        <?= esc($ind['nombre_indicador'] ?? '') ?>
                        <?php if (!empty($ind['formula'])): ?>
                            <br><span style="font-size: 7pt; color: #666;"><?= esc($ind['formula']) ?></span>
                        <?php endif; ?>
                    </td>
                    <!-- Tipo -->
                    <td style="text-align: center;">
                        <span class="<?= $tipoBadgeClass ?>"><?= esc($tipoCfg['letter']) ?></span>
                    </td>
                    <!-- Meta -->
                    <td style="text-align: center;">
                        <?= esc($ind['meta'] ?? '-') ?>
                        <?php if (!empty($ind['unidad_medida'])): ?>
                            <span style="font-size: 6pt; color: #666;"><?= esc($ind['unidad_medida']) ?></span>
                        <?php endif; ?>
                    </td>
                    <!-- Periodicidad -->
                    <td style="text-align: center; font-size: 7pt;">
                        <?= esc(ucfirst($ind['periodicidad'] ?? 'N/A')) ?>
                    </td>
                    <!-- Period columns -->
                    <?php
                    $periodosData = $ind['periodos_data'] ?? [];
                    foreach ($periodLabels as $idx => $label):
                        $pd = $periodosData[$idx] ?? null;
                        $resultado = $pd['resultado'] ?? null;
                        $cumplePeriodo = $pd['cumple'] ?? null;

                        if ($resultado === null):
                    ?>
                        <td class="celda-sin-dato">-</td>
                    <?php elseif ($cumplePeriodo !== null && (int)$cumplePeriodo === 1): ?>
                        <td class="celda-cumple"><?= number_format((float)$resultado, 1) ?></td>
                    <?php elseif ($cumplePeriodo !== null && (int)$cumplePeriodo === 0): ?>
                        <td class="celda-no-cumple"><?= number_format((float)$resultado, 1) ?></td>
                    <?php else: ?>
                        <td style="text-align: center;"><?= number_format((float)$resultado, 1) ?></td>
                    <?php
                        endif;
                    endforeach;
                    ?>
                    <!-- Cumple global -->
                    <td style="text-align: center;">
                        <?php if ($cumpleGlobal !== null && $cumpleGlobal !== '' && (int)$cumpleGlobal === 1): ?>
                            <span class="badge-si">SI</span>
                        <?php elseif ($cumpleGlobal !== null && $cumpleGlobal !== '' && (int)$cumpleGlobal === 0): ?>
                            <span class="badge-no">NO</span>
                        <?php else: ?>
                            <span class="badge-na">N/A</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <?php if (empty($indicadores)): ?>
            <tr>
                <td colspan="<?= 5 + count($periodLabels) + 1 ?>" style="text-align: center; padding: 15px; color: #666;">
                    No hay indicadores registrados para el periodo <?= esc($anio) ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Leyenda -->
    <div class="leyenda">
        <strong>Leyenda:</strong>&nbsp;&nbsp;
        <span class="leyenda-color" style="background-color: #d4edda;"></span> Cumple meta&nbsp;&nbsp;&nbsp;
        <span class="leyenda-color" style="background-color: #f8d7da;"></span> No cumple meta&nbsp;&nbsp;&nbsp;
        <span class="leyenda-color" style="background-color: #f8f9fa;"></span> Sin datos&nbsp;&nbsp;&nbsp;
        <span class="tipo-e">E</span> Estructura&nbsp;&nbsp;
        <span class="tipo-p">P</span> Proceso&nbsp;&nbsp;
        <span class="tipo-r">R</span> Resultado
    </div>

    <!-- ============================================== -->
    <!-- CONTROL DE CAMBIOS                             -->
    <!-- ============================================== -->
    <div class="seccion-barra">CONTROL DE CAMBIOS</div>
    <table class="tabla-cambios" cellpadding="0" cellspacing="0">
        <tr>
            <th style="width: 70px;">Version</th>
            <th>Descripcion del Cambio</th>
            <th style="width: 80px;">Fecha</th>
        </tr>
        <tr>
            <td style="text-align: center; font-weight: bold;">1.0</td>
            <td>Elaboracion inicial de la Matriz de Objetivos y Metas del SG-SST</td>
            <td style="text-align: center;"><?= $fechaActual ?></td>
        </tr>
    </table>

    <!-- Pie de documento -->
    <div class="pie-documento">
        <p style="margin: 0;"><?= esc($cliente['nombre_cliente'] ?? '') ?> - NIT: <?= esc($cliente['nit_cliente'] ?? '') ?></p>
        <p style="margin: 2px 0 0 0;">Documento generado el <?= $fechaActual ?> - Sistema de Gestion de Seguridad y Salud en el Trabajo</p>
    </div>

</body>
</html>
