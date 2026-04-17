<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 100px 60px 70px 70px;
    }
    body {
        font-family: Helvetica, Arial, sans-serif;
        font-size: 10px;
        color: #333;
        line-height: 1.4;
    }
    table {
        border-collapse: collapse;
        width: 100%;
    }
    .header-table {
        width: 100%;
        border: 1px solid #333;
        margin-bottom: 15px;
    }
    .header-table td {
        border: 1px solid #333;
        padding: 5px 8px;
        vertical-align: middle;
    }
    .header-logo img {
        max-width: 90px;
        max-height: 55px;
    }
    .header-title {
        text-align: center;
        font-weight: bold;
        font-size: 9px;
        line-height: 1.3;
    }
    .header-code {
        text-align: center;
        font-size: 8px;
        width: 120px;
    }
    .titulo-informe {
        text-align: center;
        font-size: 14px;
        font-weight: bold;
        color: #1c2437;
        margin: 15px 0 5px;
    }
    .subtitulo {
        text-align: center;
        font-size: 11px;
        font-weight: bold;
        color: #bd9751;
        margin-bottom: 3px;
    }
    .periodo-text {
        text-align: center;
        font-size: 10px;
        color: #555;
        margin-bottom: 15px;
    }
    .section-title {
        background: #1c2437;
        color: #fff;
        font-weight: bold;
        font-size: 10px;
        padding: 6px 10px;
        margin-top: 15px;
        margin-bottom: 8px;
    }
    .info-table {
        width: 100%;
        margin-bottom: 12px;
    }
    .info-table td {
        padding: 5px 8px;
        border: 1px solid #ddd;
        font-size: 9px;
    }
    .info-table .label-cell {
        background: #f0f0f0;
        font-weight: bold;
        width: 30%;
        color: #1c2437;
    }
    .metricas-table {
        width: 100%;
        margin-bottom: 12px;
    }
    .metricas-table td {
        padding: 8px;
        border: 1px solid #ddd;
        text-align: center;
        font-size: 10px;
    }
    .metricas-table .metric-value {
        font-size: 18px;
        font-weight: bold;
        color: #1c2437;
    }
    .metricas-table .metric-label {
        font-size: 8px;
        color: #777;
        text-transform: uppercase;
    }
    /* Progress bar via table */
    .progress-bar-table {
        width: 100%;
        height: 18px;
        border: 1px solid #ccc;
        overflow: hidden;
    }
    .progress-bar-table td {
        padding: 0;
        height: 18px;
        border: none;
    }
    .progress-fill {
        background: #17a2b8;
        color: #fff;
        font-size: 8px;
        font-weight: bold;
        text-align: center;
        line-height: 18px;
    }
    .progress-fill-green {
        background: #28a745;
    }
    .progress-empty {
        background: #e9ecef;
    }
    .estado-badge {
        display: inline-block;
        padding: 4px 12px;
        font-weight: bold;
        font-size: 10px;
        color: #fff;
    }
    .estado-significativo { background: #28a745; }
    .estado-moderado { background: #17a2b8; }
    .estado-estable { background: #ffc107; color: #333; }
    .estado-reinicio { background: #dc3545; }
    .content-text {
        font-size: 9px;
        line-height: 1.5;
        padding: 8px;
        border: 1px solid #eee;
        background: #fafafa;
        margin-bottom: 10px;
    }
    .actividades-table {
        width: 100%;
        margin-bottom: 10px;
    }
    .actividades-table th {
        background: #1c2437;
        color: #fff;
        font-size: 8px;
        padding: 4px 6px;
        text-align: left;
    }
    .actividades-table td {
        font-size: 8px;
        padding: 3px 6px;
        border: 1px solid #ddd;
    }
    .soporte-img {
        max-width: 280px;
        max-height: 200px;
    }
    .page-break {
        page-break-before: always;
    }
    .text-gold { color: #bd9751; }
    .text-green { color: #28a745; }
    .text-red { color: #dc3545; }
    .text-center { text-align: center; }
    .small { font-size: 8px; }
    .footer-text {
        font-size: 7px;
        color: #999;
        text-align: center;
        margin-top: 20px;
    }
</style>
</head>
<body>

<!-- HEADER CORPORATIVO -->
<table class="header-table">
    <tr>
        <td class="header-logo" rowspan="2" style="width: 100px; text-align: center;">
            <?php if (!empty($logoBase64)): ?>
                <img src="<?= $logoBase64 ?>">
            <?php else: ?>
                <span style="font-size:8px; color:#999;">Sin logo</span>
            <?php endif; ?>
        </td>
        <td class="header-title">
            SISTEMA DE GESTION DE SEGURIDAD<br>Y SALUD EN EL TRABAJO SG-SST
        </td>
        <td class="header-code">
            Codigo: FT-SST-205<br>
            Version: 001
        </td>
    </tr>
    <tr>
        <td class="header-title" style="font-size: 11px; color: #1c2437;">
            INFORME DE AVANCES
        </td>
        <td class="header-code">
            Pagina: 1 de 1
        </td>
    </tr>
</table>

<!-- TITULO -->
<div class="titulo-informe">INFORME DE AVANCES</div>
<div class="subtitulo"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>
<div class="periodo-text">
    Periodo: <?= date('d/m/Y', strtotime($informe['fecha_desde'])) ?> - <?= date('d/m/Y', strtotime($informe['fecha_hasta'])) ?>
    &nbsp;|&nbsp; Anio: <?= esc($informe['anio']) ?>
</div>


<!-- RESUMEN DE AVANCE -->
<?php if (!empty($informe['resumen_avance'])): ?>
<div class="section-title">RESUMEN DE AVANCE DEL PERIODO</div>
<div class="content-text"><?= nl2br(esc($informe['resumen_avance'])) ?></div>
<?php endif; ?>

<!-- INDICADORES POR PILAR SG-SST (2x2) -->
<div class="section-title">INDICADORES POR PILAR SG-SST</div>

<?php
    $chartImages = $desglose['chart_images'] ?? [];
    $desEst = $desglose['desglose_estandares'] ?? [];
    $desPlan = $desglose['desglose_plan_trabajo'] ?? [];
    $desCap = $desglose['desglose_capacitacion'] ?? [];
    $desPend = $desglose['desglose_pendientes'] ?? [];

    $puntajeActual = floatval($informe['puntaje_actual'] ?? 0);
    $puntajeAnterior = floatval($informe['puntaje_anterior'] ?? 39.75);
    $dif = floatval($informe['diferencia_neta'] ?? 0);
    $ea = $informe['estado_avance'] ?? 'ESTABLE';
    $eaClass = match(true) {
        str_contains($ea, 'SIGNIFICATIVO') => 'estado-significativo',
        str_contains($ea, 'MODERADO')      => 'estado-moderado',
        str_contains($ea, 'ESTABLE')       => 'estado-estable',
        default                            => 'estado-reinicio',
    };
    $planPct = floatval($informe['indicador_plan_trabajo'] ?? 0);
    $capPct = floatval($informe['indicador_capacitacion'] ?? 0);

    // Helper: stacked bar fallback for DOMPDF
    function renderStackedBar(array $items, array $colors, string $valueKey = 'cantidad'): string {
        $total = 0;
        foreach ($items as $item) { $total += floatval($item[$valueKey] ?? 0); }
        if ($total == 0) return '<span style="font-size:8px;color:#999;">Sin datos</span>';
        $html = '<table style="width:100%;border-collapse:collapse;height:14px;"><tr>';
        $i = 0;
        foreach ($items as $item) {
            $val = floatval($item[$valueKey] ?? 0);
            $pct = ($val / $total) * 100;
            if ($pct < 1) { $i++; continue; }
            $color = $colors[$i % count($colors)] ?? '#6c757d';
            $html .= '<td style="width:' . round($pct, 1) . '%;background:' . $color . ';height:14px;"></td>';
            $i++;
        }
        $html .= '</tr></table>';
        // Legend
        $html .= '<div style="margin-top:3px;">';
        $i = 0;
        foreach ($items as $item) {
            $label = $item['ciclo'] ?? $item['estado_actividad'] ?? $item['estado'] ?? '';
            $color = $colors[$i % count($colors)] ?? '#6c757d';
            $val = floatval($item[$valueKey] ?? 0);
            $html .= '<span style="display:inline-block;width:8px;height:8px;background:' . $color . ';margin-right:2px;"></span>';
            $html .= '<span style="font-size:7px;margin-right:8px;">' . esc($label) . ' (' . number_format($val, ($valueKey === 'cantidad' ? 0 : 1)) . ')</span>';
            $i++;
        }
        $html .= '</div>';
        return $html;
    }

    $colorsPHVA = ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];
    $colorsEstado = ['#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d', '#6f42c1'];
?>

<!-- PILAR 1: Estandares Minimos -->
<table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
    <tr>
        <td style="border:1px solid #ddd; padding:8px;">
            <div style="font-size:9px;font-weight:bold;text-transform:uppercase;color:#36A2EB;margin-bottom:5px;">ESTANDARES MINIMOS (Res. 0312)</div>
            <table style="width:100%"><tr>
                <td style="width:25%;text-align:center;border:none;padding:4px;">
                    <?php if (!empty($chartImages['chartEstandares'])): ?>
                        <img src="<?= $chartImages['chartEstandares'] ?>" style="width:110px;height:110px;">
                    <?php else: ?>
                        <?= renderStackedBar($desEst, $colorsPHVA, 'total_valor') ?>
                    <?php endif; ?>
                </td>
                <td style="width:25%;border:none;padding:4px;text-align:center;">
                    <div style="font-size:26px;font-weight:bold;color:#1c2437;"><?= number_format($puntajeActual, 1) ?>%</div>
                    <div style="font-size:8px;color:#777;">Anterior: <?= number_format($puntajeAnterior, 1) ?>%</div>
                    <div style="font-size:10px;color:<?= $dif > 0 ? '#28a745' : ($dif < 0 ? '#dc3545' : '#6c757d') ?>;font-weight:bold;">
                        <?= $dif > 0 ? '+' : '' ?><?= number_format($dif, 1) ?> pp
                    </div>
                </td>
                <td style="width:50%;border:none;padding:4px;">
                    <span class="estado-badge <?= $eaClass ?>" style="font-size:8px;padding:3px 10px;"><?= esc($ea) ?></span>
                    <?php if (!empty($desEst)): ?>
                    <div style="margin-top:6px;">
                        <?php foreach ($desEst as $idx => $item): ?>
                            <span style="display:inline-block;width:8px;height:8px;background:<?= $colorsPHVA[$idx % count($colorsPHVA)] ?>;margin-right:2px;"></span>
                            <span style="font-size:7px;margin-right:6px;"><?= esc($item['ciclo'] ?? '') ?> (<?= number_format(floatval($item['total_valor'] ?? 0), 1) ?>)</span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </td>
            </tr></table>
        </td>
    </tr>
</table>

<!-- PILAR 2: Plan de Trabajo -->
<table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
    <tr>
        <td style="border:1px solid #ddd; padding:8px;">
            <div style="font-size:9px;font-weight:bold;text-transform:uppercase;color:#28a745;margin-bottom:5px;">PLAN DE TRABAJO ANUAL</div>
            <table style="width:100%"><tr>
                <td style="width:25%;text-align:center;border:none;padding:4px;">
                    <?php if (!empty($chartImages['chartPlanTrabajo'])): ?>
                        <img src="<?= $chartImages['chartPlanTrabajo'] ?>" style="width:110px;height:110px;">
                    <?php else: ?>
                        <?= renderStackedBar($desPlan, $colorsEstado) ?>
                    <?php endif; ?>
                </td>
                <td style="width:25%;border:none;padding:4px;text-align:center;">
                    <div style="font-size:26px;font-weight:bold;color:#1c2437;"><?= number_format($planPct, 1) ?>%</div>
                    <div style="font-size:8px;color:#777;">Actividades cerradas</div>
                </td>
                <td style="width:50%;border:none;padding:4px;">
                    <?php
                        $totalPlan = 0; $cerradasPlan = 0;
                        foreach ($desPlan as $p) {
                            $totalPlan += intval($p['cantidad']);
                            if ($p['estado_actividad'] === 'CERRADA') $cerradasPlan = intval($p['cantidad']);
                        }
                    ?>
                    <div style="font-size:10px;font-weight:bold;margin-bottom:4px;"><?= $cerradasPlan ?> de <?= $totalPlan ?> actividades</div>
                    <?php if (!empty($desPlan)): ?>
                    <div>
                        <?php foreach ($desPlan as $idx => $item): ?>
                            <span style="display:inline-block;width:8px;height:8px;background:<?= $colorsEstado[$idx % count($colorsEstado)] ?>;margin-right:2px;"></span>
                            <span style="font-size:7px;margin-right:6px;"><?= esc($item['estado_actividad'] ?? '') ?> (<?= intval($item['cantidad']) ?>)</span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </td>
            </tr></table>
        </td>
    </tr>
</table>

<!-- PILAR 3: Capacitacion -->
<table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
    <tr>
        <td style="border:1px solid #ddd; padding:8px;">
            <div style="font-size:9px;font-weight:bold;text-transform:uppercase;color:#ffc107;margin-bottom:5px;">PROGRAMA DE CAPACITACION</div>
            <table style="width:100%"><tr>
                <td style="width:25%;text-align:center;border:none;padding:4px;">
                    <?php if (!empty($chartImages['chartCapacitacion'])): ?>
                        <img src="<?= $chartImages['chartCapacitacion'] ?>" style="width:110px;height:110px;">
                    <?php else: ?>
                        <?= renderStackedBar($desCap, $colorsEstado) ?>
                    <?php endif; ?>
                </td>
                <td style="width:25%;border:none;padding:4px;text-align:center;">
                    <?php
                        $totalCap = 0; $ejecutadas = 0;
                        foreach ($desCap as $c) {
                            $totalCap += intval($c['cantidad']);
                            if ($c['estado'] === 'EJECUTADA') $ejecutadas = intval($c['cantidad']);
                        }
                    ?>
                    <div style="font-size:26px;font-weight:bold;color:#1c2437;"><?= number_format($capPct, 1) ?>%</div>
                    <div style="font-size:8px;color:#777;">Capacitaciones ejecutadas</div>
                </td>
                <td style="width:50%;border:none;padding:4px;">
                    <div style="font-size:10px;font-weight:bold;margin-bottom:4px;"><?= $ejecutadas ?> de <?= $totalCap ?> capacitaciones</div>
                    <?php if (!empty($desCap)): ?>
                    <div>
                        <?php foreach ($desCap as $idx => $item): ?>
                            <span style="display:inline-block;width:8px;height:8px;background:<?= $colorsEstado[$idx % count($colorsEstado)] ?>;margin-right:2px;"></span>
                            <span style="font-size:7px;margin-right:6px;"><?= esc($item['estado'] ?? '') ?> (<?= intval($item['cantidad']) ?>)</span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </td>
            </tr></table>
        </td>
    </tr>
</table>

<!-- PILAR 4: Pendientes -->
<table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
    <tr>
        <td style="border:1px solid #ddd; padding:8px;">
            <div style="font-size:9px;font-weight:bold;text-transform:uppercase;color:#dc3545;margin-bottom:5px;">COMPROMISOS / PENDIENTES</div>
            <table style="width:100%"><tr>
                <td style="width:25%;text-align:center;border:none;padding:4px;">
                    <?php if (!empty($chartImages['chartPendientes'])): ?>
                        <img src="<?= $chartImages['chartPendientes'] ?>" style="width:110px;height:110px;">
                    <?php else: ?>
                        <?= renderStackedBar($desPend, $colorsEstado) ?>
                    <?php endif; ?>
                </td>
                <td style="width:25%;border:none;padding:4px;text-align:center;">
                    <?php
                        $abiertos = 0; $totalPend = 0; $promDias = 0;
                        foreach ($desPend as $p) {
                            $totalPend += intval($p['cantidad']);
                            if ($p['estado'] === 'ABIERTA') {
                                $abiertos = intval($p['cantidad']);
                                $promDias = floatval($p['promedio_dias'] ?? 0);
                            }
                        }
                    ?>
                    <div style="font-size:26px;font-weight:bold;color:#1c2437;"><?= $abiertos ?> / <?= $totalPend ?></div>
                    <div style="font-size:8px;color:#777;">Abiertos / Total</div>
                </td>
                <td style="width:50%;border:none;padding:4px;">
                    <?php if ($promDias > 0): ?>
                    <div style="font-size:10px;font-weight:bold;margin-bottom:4px;">Promedio: <?= number_format($promDias, 0) ?> dias sin cerrar</div>
                    <?php endif; ?>
                    <?php if (!empty($desPend)): ?>
                    <div>
                        <?php foreach ($desPend as $idx => $item): ?>
                            <span style="display:inline-block;width:8px;height:8px;background:<?= $colorsEstado[$idx % count($colorsEstado)] ?>;margin-right:2px;"></span>
                            <span style="font-size:7px;margin-right:6px;"><?= esc($item['estado'] ?? '') ?> (<?= intval($item['cantidad']) ?>)</span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </td>
            </tr></table>
        </td>
    </tr>
</table>

<!-- 6 MODULOS ADICIONALES -->
<?php
    $feData = $desglose['firma_electronica'] ?? [];
    $dsData = $desglose['documentos_sst'] ?? [];
    $indData = $desglose['indicadores_sst'] ?? [];
    $acData = $desglose['acciones_correctivas'] ?? [];
    $atData = $desglose['actas_comite'] ?? [];
    $insData = $desglose['inspecciones'] ?? [];
?>

<?php if (!empty($dsData) && ($dsData['total_creados'] ?? 0) > 0): ?>
<div class="section-title">DOCUMENTOS SST CREADOS</div>
<table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
    <tr style="background:#f8f9fa;">
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; font-weight:bold;">Tipo</td>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; font-weight:bold; text-align:center;">Fecha</td>
    </tr>
    <?php foreach (($dsData['documentos'] ?? []) as $doc): ?>
    <tr>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px;"><?= esc(str_replace('_', ' ', $doc['tipo_documento'] ?? '')) ?></td>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; text-align:center;"><?= substr($doc['created_at'] ?? '', 0, 10) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<?php if (!empty($indData) && ($indData['total_activos'] ?? 0) > 0): ?>
<?php $medidos = $indData['medidos_periodo'] ?? 0; $totalInd = $indData['total_activos']; ?>
<div class="section-title">INDICADORES SST</div>
<div style="background:#e8f4fd;padding:6px 10px;border-radius:4px;font-size:8px;margin-bottom:6px;">
    <?php if ($medidos > 0): ?>
        Se midieron <strong><?= $medidos ?></strong> de <strong><?= $totalInd ?></strong> indicadores.<?= $medidos < $totalInd ? ' Los indicadores pendientes requieren seguimiento.' : ' Todos los indicadores fueron medidos.' ?>
    <?php else: ?>
        Ningun indicador fue medido en el periodo. Se requiere programar mediciones para los <strong><?= $totalInd ?></strong> indicadores activos.
    <?php endif; ?>
</div>
<?php if (!empty($indData['mediciones'])): ?>
<table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
    <tr style="background:#f8f9fa;">
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; font-weight:bold;">Indicador</td>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; font-weight:bold; text-align:center;">Resultado</td>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; font-weight:bold; text-align:center;">Meta</td>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; font-weight:bold; text-align:center;">Cumple</td>
    </tr>
    <?php foreach ($indData['mediciones'] as $m): ?>
    <tr>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px;"><?= esc($m['nombre_indicador'] ?? '') ?></td>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; text-align:center;"><?= $m['valor_resultado'] ?? '-' ?> <?= esc($m['unidad_medida'] ?? '') ?></td>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; text-align:center;"><?= $m['meta'] ?? '-' ?></td>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; text-align:center;"><?= intval($m['cumple_meta'] ?? 0) === 1 ? '&#10004;' : '&#10008;' ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php endif; ?>

<?php if (!empty($acData) && ($acData['acciones_total'] ?? 0) > 0): ?>
<?php $kp = $acData['kpis'] ?? []; ?>
<div class="section-title">ACCIONES CORRECTIVAS</div>
<table style="width:100%; border-collapse:collapse; margin-bottom:4px;">
    <tr>
        <td style="border:1px solid #ddd; padding:6px; width:25%; text-align:center;"><div style="font-size:16px;font-weight:bold;"><?= $kp['cierre_a_tiempo'] ?? 0 ?>%</div><div style="font-size:7px;color:#666;">Cierre a tiempo (85%)</div></td>
        <td style="border:1px solid #ddd; padding:6px; width:25%; text-align:center;"><div style="font-size:16px;font-weight:bold;"><?= $kp['efectividad'] ?? 0 ?>%</div><div style="font-size:7px;color:#666;">Efectividad (80%)</div></td>
        <td style="border:1px solid #ddd; padding:6px; width:25%; text-align:center;"><div style="font-size:16px;font-weight:bold;"><?= $kp['dias_promedio'] ?? 0 ?></div><div style="font-size:7px;color:#666;">Dias promedio (30)</div></td>
        <td style="border:1px solid #ddd; padding:6px; width:25%; text-align:center;"><div style="font-size:16px;font-weight:bold;"><?= $kp['reincidencia'] ?? 0 ?>%</div><div style="font-size:7px;color:#666;">Reincidencia (&le;10%)</div></td>
    </tr>
</table>
<table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
    <tr>
        <td style="border:1px solid #ddd; padding:6px; width:33%; text-align:center;"><strong><?= $acData['hallazgos_total'] ?? 0 ?></strong><div style="font-size:7px;color:#666;">Hallazgos</div></td>
        <td style="border:1px solid #ddd; padding:6px; width:33%; text-align:center;"><strong><?= $acData['acciones_total'] ?? 0 ?></strong><div style="font-size:7px;color:#666;">Acciones</div></td>
        <td style="border:1px solid #ddd; padding:6px; width:33%; text-align:center;"><strong style="color:#dc3545;"><?= $acData['acciones_vencidas'] ?? 0 ?></strong><div style="font-size:7px;color:#666;">Vencidas</div></td>
    </tr>
</table>
<?php endif; ?>

<?php if (!empty($atData) && (($atData['reuniones_periodo'] ?? 0) > 0 || ($atData['compromisos']['total'] ?? 0) > 0)): ?>
<div class="section-title">ACTAS DE COMITE</div>
<table style="width:100%; border-collapse:collapse; margin-bottom:4px;">
    <tr style="background:#f8f9fa;">
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; font-weight:bold;">Comite</td>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; font-weight:bold; text-align:center;">Reuniones</td>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; font-weight:bold; text-align:center;">Esperadas</td>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; font-weight:bold; text-align:center;">Cumplimiento</td>
    </tr>
    <?php foreach (($atData['por_comite'] ?? []) as $c): ?>
    <tr>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px;"><?= esc($c['tipo_comite']) ?></td>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; text-align:center;"><?= $c['total_anio'] ?></td>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; text-align:center;"><?= $c['esperadas'] ?></td>
        <td style="border:1px solid #ddd; padding:4px; font-size:7px; text-align:center;"><?= $c['cumplimiento'] ?>%</td>
    </tr>
    <?php endforeach; ?>
</table>
<?php $comp = $atData['compromisos'] ?? []; if (($comp['total'] ?? 0) > 0): ?>
<table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
    <tr>
        <td style="border:1px solid #ddd; padding:6px; width:25%; text-align:center;"><strong><?= $comp['total'] ?></strong><div style="font-size:7px;color:#666;">Total</div></td>
        <td style="border:1px solid #ddd; padding:6px; width:25%; text-align:center;"><strong style="color:#198754;"><?= $comp['cumplidos'] ?? 0 ?></strong><div style="font-size:7px;color:#666;">Cumplidos</div></td>
        <td style="border:1px solid #ddd; padding:6px; width:25%; text-align:center;"><strong style="color:#ffc107;"><?= $comp['pendientes'] ?? 0 ?></strong><div style="font-size:7px;color:#666;">Pendientes</div></td>
        <td style="border:1px solid #ddd; padding:6px; width:25%; text-align:center;"><strong style="color:#dc3545;"><?= $comp['vencidos'] ?? 0 ?></strong><div style="font-size:7px;color:#666;">Vencidos</div></td>
    </tr>
</table>
<?php endif; ?>
<?php endif; ?>

<?php if (!empty($insData) && ($insData['total_inspecciones'] ?? 0) > 0): ?>
<div class="section-title">INSPECCIONES</div>
<table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
    <tr>
        <?php
        $nombres = ['locativa'=>'Locativa','extintores'=>'Extintores','botiquin'=>'Botiquin','senalizacion'=>'Senalizacion'];
        $coloresInsp = ['locativa'=>'#0d6efd','extintores'=>'#dc3545','botiquin'=>'#198754','senalizacion'=>'#ffc107'];
        foreach ($nombres as $k => $n):
            $v = $insData['por_tipo'][$k] ?? ['total'=>0,'completadas'=>0];
            if ($v['total'] > 0):
        ?>
        <td style="border:1px solid #ddd; padding:6px; text-align:center; border-left:3px solid <?= $coloresInsp[$k] ?>;">
            <div style="font-size:16px;font-weight:bold;"><?= $v['completadas'] ?>/<?= $v['total'] ?></div>
            <div style="font-size:7px;color:#666;"><?= $n ?></div>
        </td>
        <?php endif; endforeach; ?>
    </tr>
</table>
<?php $hall = $insData['hallazgos'] ?? []; if (($hall['total'] ?? 0) > 0): ?>
<div style="background:#e8f4fd;padding:6px 10px;border-radius:4px;font-size:8px;margin-bottom:8px;">
    Hallazgos locativos: <strong><?= $hall['total'] ?></strong> encontrados, <strong><?= $hall['corregidos'] ?? 0 ?></strong> corregidos, <strong><?= $hall['pendientes'] ?? 0 ?></strong> pendientes
</div>
<?php endif; ?>
<?php endif; ?>

<!-- EVOLUCION HISTORICA DEL CLIENTE -->
<?php if (!empty($quickChartEstandares) || !empty($quickChartPlan)): ?>
<div class="section-title">EVOLUCION HISTORICA DEL CLIENTE</div>
<table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
    <tr>
        <?php if (!empty($quickChartEstandares)): ?>
        <td style="width:50%; padding:8px; border:1px solid #ddd; text-align:center; vertical-align:top;">
            <div style="font-size:8px; font-weight:bold; color:#667eea; text-transform:uppercase; margin-bottom:4px;">
                % Cumplimiento Estandares Minimos
            </div>
            <img src="<?= $quickChartEstandares ?>" style="max-width:340px; max-height:160px;">
        </td>
        <?php endif; ?>
        <?php if (!empty($quickChartPlan)): ?>
        <td style="width:50%; padding:8px; border:1px solid #ddd; text-align:center; vertical-align:top;">
            <div style="font-size:8px; font-weight:bold; color:#4facfe; text-transform:uppercase; margin-bottom:4px;">
                % Actividades Abiertas Plan de Trabajo
            </div>
            <img src="<?= $quickChartPlan ?>" style="max-width:340px; max-height:160px;">
        </td>
        <?php endif; ?>
    </tr>
</table>
<?php endif; ?>

<!-- ACTIVIDADES CERRADAS EN EL PERIODO -->
<?php if (!empty($informe['actividades_cerradas_periodo'])): ?>
<div class="section-title">ACTIVIDADES PLAN DE TRABAJO ANUAL CERRADAS EN EL PERIODO</div>
<div class="content-text"><?= nl2br(esc($informe['actividades_cerradas_periodo'])) ?></div>
<?php endif; ?>

<!-- ACTIVIDADES PLAN DE TRABAJO ANUAL NO CERRADAS EN EL PERIODO -->
<?php if (!empty($informe['actividades_no_cerradas_pta'])): ?>
<div class="section-title">ACTIVIDADES PLAN DE TRABAJO ANUAL NO CERRADAS EN EL PERIODO</div>
<div class="content-text"><?= nl2br(esc($informe['actividades_no_cerradas_pta'])) ?></div>
<?php endif; ?>

<!-- ACTIVIDADES ABIERTAS -->
<?php if (!empty($informe['actividades_abiertas'])): ?>
<div class="section-title">ACTIVIDADES Y COMPROMISOS ABIERTOS</div>
<div class="content-text"><?= nl2br(esc($informe['actividades_abiertas'])) ?></div>
<?php endif; ?>

<!-- DETALLE COMPROMISOS / PENDIENTES DEL PERIODO -->
<?php
    $dpp = $desglose['desglose_pendientes_periodo'] ?? [];
    $pdfCerrados = $dpp['cerrados'] ?? [];
    $pdfVencidos = $dpp['vencidos'] ?? [];
    $pdfSR       = $dpp['sin_respuesta'] ?? [];
    $pdfTotalCerr = $dpp['total_cerrados'] ?? 0;
    $pdfATiempo   = $dpp['a_tiempo'] ?? 0;
    $pdfFueraPlazo = $dpp['fuera_plazo'] ?? 0;
    $pdfTotalVenc = $dpp['total_vencidos'] ?? 0;
    $pdfTotalSR   = $dpp['total_sin_respuesta'] ?? 0;
?>
<?php if ($pdfTotalCerr > 0 || $pdfTotalVenc > 0 || $pdfTotalSR > 0): ?>
<div class="section-title">COMPROMISOS / PENDIENTES DEL PERIODO</div>
<div class="content-text" style="font-size:9px; margin-bottom:8px;">
    <?php if ($pdfTotalCerr > 0): ?>
        <strong style="color:#059669;">Cerrados: <?= $pdfTotalCerr ?></strong> (<?= $pdfATiempo ?> a tiempo, <?= $pdfFueraPlazo ?> fuera de plazo) &nbsp;
    <?php endif; ?>
    <?php if ($pdfTotalVenc > 0): ?>
        <strong style="color:#DC2626;">Vencidos: <?= $pdfTotalVenc ?></strong> &nbsp;
    <?php endif; ?>
    <?php if ($pdfTotalSR > 0): ?>
        <strong style="color:#D97706;">Sin Respuesta: <?= $pdfTotalSR ?></strong>
    <?php endif; ?>
</div>

<?php if (!empty($pdfCerrados)): ?>
<table style="width:100%;border-collapse:collapse;font-size:8px;margin-bottom:8px;">
    <thead><tr style="background:#1c2437;color:white;">
        <th style="padding:4px;text-align:left;">Actividad</th>
        <th style="padding:4px;text-align:center;width:70px;">Responsable</th>
        <th style="padding:4px;text-align:center;width:60px;">Plazo</th>
        <th style="padding:4px;text-align:center;width:60px;">Cierre Real</th>
        <th style="padding:4px;text-align:center;width:50px;">A Tiempo</th>
    </tr></thead>
    <tbody>
    <?php foreach ($pdfCerrados as $i => $c): ?>
    <?php if ($c['estado'] !== 'SIN RESPUESTA DEL CLIENTE'): ?>
    <tr style="background:<?= $i % 2 === 0 ? '#fff' : '#f9fafb' ?>;">
        <td style="padding:3px;border-bottom:1px solid #e5e7eb;"><?= esc($c['tarea_actividad']) ?></td>
        <td style="padding:3px;text-align:center;border-bottom:1px solid #e5e7eb;"><?= esc($c['responsable'] ?? '-') ?></td>
        <td style="padding:3px;text-align:center;border-bottom:1px solid #e5e7eb;"><?= !empty($c['fecha_cierre']) && $c['fecha_cierre'] >= '2000-01-01' ? date('d/m/Y', strtotime($c['fecha_cierre'])) : '-' ?></td>
        <td style="padding:3px;text-align:center;border-bottom:1px solid #e5e7eb;"><?= !empty($c['fecha_cierre_real']) ? date('d/m/Y', strtotime($c['fecha_cierre_real'])) : '-' ?></td>
        <td style="padding:3px;text-align:center;border-bottom:1px solid #e5e7eb;color:<?= $c['a_tiempo'] === 1 ? '#059669' : '#DC2626' ?>;font-weight:bold;"><?= $c['a_tiempo'] === 1 ? 'Si' : ($c['a_tiempo'] === 0 ? 'No' : '-') ?></td>
    </tr>
    <?php endif; ?>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php if (!empty($pdfVencidos)): ?>
<div style="font-size:9px;font-weight:bold;color:#DC2626;margin:6px 0 3px;">Vencidos sin Gestión</div>
<table style="width:100%;border-collapse:collapse;font-size:8px;margin-bottom:8px;">
    <thead><tr style="background:#FEE2E2;">
        <th style="padding:4px;text-align:left;">Actividad</th>
        <th style="padding:4px;text-align:center;width:70px;">Responsable</th>
        <th style="padding:4px;text-align:center;width:60px;">Plazo</th>
        <th style="padding:4px;text-align:center;width:50px;">Días Vencido</th>
    </tr></thead>
    <tbody>
    <?php foreach ($pdfVencidos as $i => $v): ?>
    <tr style="background:<?= $i % 2 === 0 ? '#fff' : '#f9fafb' ?>;">
        <td style="padding:3px;border-bottom:1px solid #e5e7eb;"><?= esc($v['tarea_actividad']) ?></td>
        <td style="padding:3px;text-align:center;border-bottom:1px solid #e5e7eb;"><?= esc($v['responsable'] ?? '-') ?></td>
        <td style="padding:3px;text-align:center;border-bottom:1px solid #e5e7eb;"><?= date('d/m/Y', strtotime($v['fecha_cierre'])) ?></td>
        <td style="padding:3px;text-align:center;border-bottom:1px solid #e5e7eb;color:#DC2626;font-weight:bold;"><?= $v['dias_vencido'] ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php if (!empty($pdfSR)): ?>
<div style="font-size:9px;font-weight:bold;color:#D97706;margin:6px 0 3px;">Sin Respuesta del Cliente</div>
<table style="width:100%;border-collapse:collapse;font-size:8px;margin-bottom:8px;">
    <thead><tr style="background:#FEF3C7;">
        <th style="padding:4px;text-align:left;">Actividad</th>
        <th style="padding:4px;text-align:center;width:70px;">Responsable</th>
        <th style="padding:4px;text-align:center;width:60px;">Plazo</th>
        <th style="padding:4px;text-align:center;width:60px;">Clasificado</th>
    </tr></thead>
    <tbody>
    <?php foreach ($pdfSR as $i => $sr): ?>
    <tr style="background:<?= $i % 2 === 0 ? '#fff' : '#f9fafb' ?>;">
        <td style="padding:3px;border-bottom:1px solid #e5e7eb;"><?= esc($sr['tarea_actividad']) ?></td>
        <td style="padding:3px;text-align:center;border-bottom:1px solid #e5e7eb;"><?= esc($sr['responsable'] ?? '-') ?></td>
        <td style="padding:3px;text-align:center;border-bottom:1px solid #e5e7eb;"><?= !empty($sr['fecha_cierre']) && $sr['fecha_cierre'] >= '2000-01-01' ? date('d/m/Y', strtotime($sr['fecha_cierre'])) : '-' ?></td>
        <td style="padding:3px;text-align:center;border-bottom:1px solid #e5e7eb;"><?= !empty($sr['fecha_cierre_real']) ? date('d/m/Y', strtotime($sr['fecha_cierre_real'])) : '-' ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php endif; ?>

<!-- OBSERVACIONES -->
<?php if (!empty($informe['observaciones'])): ?>
<div class="section-title">OBSERVACIONES</div>
<div class="content-text"><?= nl2br(esc($informe['observaciones'])) ?></div>
<?php endif; ?>

<!-- VENCIMIENTOS DE MANTENIMIENTOS -->
<?php if (!empty($vencimientos)): ?>
<div class="section-title">ELEMENTOS CON VENCIMIENTO PROXIMO O VENCIDO</div>
<table class="actividades-table">
    <tr>
        <th style="width:5%;">#</th>
        <th style="width:35%;">Elemento</th>
        <th style="width:18%;">Fecha Vencimiento</th>
        <th style="width:14%;">Estado</th>
        <th style="width:28%;">Observaciones</th>
    </tr>
    <?php
        $hoy = date('Y-m-d');
        foreach ($vencimientos as $idx => $v):
            $vencido = ($v['fecha_vencimiento'] <= $hoy);
            $rowColor = $vencido ? '#fce4e4' : '#fff8e1';
            $estadoTexto = $vencido ? 'VENCIDO' : 'PROXIMO';
            $estadoColor = $vencido ? '#dc3545' : '#ffc107';
    ?>
    <tr style="background:<?= $rowColor ?>;">
        <td style="text-align:center;"><?= $idx + 1 ?></td>
        <td><?= esc($v['detalle_mantenimiento'] ?? 'N/A') ?></td>
        <td style="text-align:center;"><?= date('d/m/Y', strtotime($v['fecha_vencimiento'])) ?></td>
        <td style="text-align:center;">
            <span style="color:#fff;background:<?= $estadoColor ?>;padding:2px 6px;font-weight:bold;font-size:7px;"><?= $estadoTexto ?></span>
        </td>
        <td><?= esc($v['observaciones'] ?? '') ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<div style="font-size:7px;color:#999;margin-bottom:10px;">
    Total: <?= count($vencimientos) ?> elemento(s) &mdash;
    <?php
        $cantVencidos = 0;
        foreach ($vencimientos as $v) { if ($v['fecha_vencimiento'] <= $hoy) $cantVencidos++; }
    ?>
    <span style="color:#dc3545;font-weight:bold;"><?= $cantVencidos ?> vencido(s)</span>,
    <span style="color:#e6a700;font-weight:bold;"><?= count($vencimientos) - $cantVencidos ?> proximo(s) a vencer</span>
</div>
<?php endif; ?>

<!-- SOPORTES -->
<?php
    $haySoportes = false;
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($informe["soporte_{$i}_texto"]) || !empty($soportesBase64[$i])) { $haySoportes = true; break; }
    }
?>
<?php if ($haySoportes): ?>
<div class="page-break"></div>
<div class="section-title">SOPORTES</div>

<table class="info-table">
    <?php for ($i = 1; $i <= 4; $i++): ?>
        <?php if (!empty($informe["soporte_{$i}_texto"]) || !empty($soportesBase64[$i])): ?>
        <tr>
            <td class="label-cell">Soporte <?= $i ?></td>
            <td>
                <?php if (!empty($informe["soporte_{$i}_texto"])): ?>
                    <strong><?= esc($informe["soporte_{$i}_texto"]) ?></strong><br>
                <?php endif; ?>
                <?php if (!empty($soportesBase64[$i])): ?>
                    <img src="<?= $soportesBase64[$i] ?>" class="soporte-img">
                <?php endif; ?>
            </td>
        </tr>
        <?php endif; ?>
    <?php endfor; ?>
</table>
<?php endif; ?>

<!-- FOOTER -->
<div class="footer-text">
    Documento generado automaticamente por el SG-SST | <?= date('d/m/Y H:i') ?>
    <?php if (!empty($consultor['nombre_consultor'])): ?>
    | Consultor: <?= esc($consultor['nombre_consultor']) ?>
    <?php endif; ?>
</div>

</body>
</html>
