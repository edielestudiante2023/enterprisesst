<?php
/**
 * Matriz de Objetivos y Metas del SG-SST
 * Vista web Bootstrap 5
 *
 * Variables recibidas:
 * - $cliente       array con id_cliente, nombre_cliente, nit_cliente, logo
 * - $indicadores   array de indicadores, cada uno con: nombre_indicador, tipo_indicador, categoria,
 *                  formula, meta, unidad_medida, periodicidad, cumple_meta, id_indicador,
 *                  y periodos_data array de ['label' => 'Trim I', 'resultado' => float|null, 'cumple' => 1|0|null]
 * - $anio          int anno
 */

$idCliente   = $cliente['id_cliente'] ?? 0;
$fechaActual = date('d/m/Y');

// Agrupar indicadores por tipo_indicador
$grupos = [
    'estructura' => [],
    'proceso'    => [],
    'resultado'  => [],
];
$totalIndicadores = 0;
$totalMedidos     = 0;
$totalCumplen     = 0;
$totalNoCumplen   = 0;

foreach ($indicadores as $ind) {
    $tipo = strtolower($ind['tipo_indicador'] ?? 'proceso');
    if (!isset($grupos[$tipo])) {
        $grupos[$tipo] = [];
    }
    $grupos[$tipo][] = $ind;
    $totalIndicadores++;

    $cumple = $ind['cumple_meta'] ?? null;
    if ($cumple !== null && $cumple !== '') {
        $totalMedidos++;
        if ((int)$cumple === 1) {
            $totalCumplen++;
        } else {
            $totalNoCumplen++;
        }
    }
}

$tipoConfig = [
    'estructura' => ['label' => 'Estructura', 'badge' => 'bg-info',              'letter' => 'E', 'icon' => 'bi-building-gear'],
    'proceso'    => ['label' => 'Proceso',    'badge' => 'bg-warning text-dark', 'letter' => 'P', 'icon' => 'bi-gear-wide-connected'],
    'resultado'  => ['label' => 'Resultado',  'badge' => 'bg-success',           'letter' => 'R', 'icon' => 'bi-trophy'],
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matriz de Objetivos y Metas - <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Summary cards */
        .stat-card {
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .stat-card .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1;
        }
        .stat-card .stat-label {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        /* Group header row */
        .grupo-header td {
            background-color: #e9ecef;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 8px 12px !important;
            border-top: 2px solid #adb5bd;
        }

        /* Table cells */
        .tabla-matriz th {
            background-color: #0d6efd;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
            padding: 8px 6px;
            white-space: nowrap;
        }
        .tabla-matriz td {
            font-size: 0.8rem;
            vertical-align: middle;
            padding: 6px 8px;
        }

        /* Resultado cells */
        .celda-cumple {
            background-color: #d4edda !important;
            color: #155724;
            font-weight: 600;
            text-align: center;
        }
        .celda-no-cumple {
            background-color: #f8d7da !important;
            color: #721c24;
            font-weight: 600;
            text-align: center;
        }
        .celda-sin-dato {
            background-color: #f8f9fa;
            color: #6c757d;
            text-align: center;
        }

        /* Tipo badges */
        .tipo-badge-sm {
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Legend */
        .leyenda-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            margin-right: 1rem;
        }
        .leyenda-color {
            width: 16px;
            height: 16px;
            border-radius: 3px;
            display: inline-block;
        }

        /* Print */
        @media print {
            body { background: #fff; }
            .toolbar-print-hide { display: none !important; }
            .container-fluid { padding: 0 !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body>

<!-- ========================================== -->
<!-- TOOLBAR                                     -->
<!-- ========================================== -->
<div class="toolbar-print-hide bg-white border-bottom shadow-sm sticky-top">
    <div class="container-fluid py-2">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <!-- Left: Back button -->
            <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url('indicadores-sst/' . esc($idCliente) . '/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Dashboard
                </a>
                <span class="text-muted small d-none d-md-inline">
                    <i class="bi bi-building me-1"></i><?= esc($cliente['nombre_cliente'] ?? '') ?>
                </span>
            </div>

            <!-- Center: Title -->
            <div class="text-center flex-grow-1">
                <strong class="text-primary">
                    <i class="bi bi-table me-1"></i>Matriz de Objetivos y Metas
                </strong>
            </div>

            <!-- Right: Actions -->
            <div class="d-flex align-items-center gap-2">
                <!-- Year selector -->
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-calendar3 me-1"></i><?= esc($anio) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php for ($y = 2035; $y >= 2025; $y--): ?>
                        <li>
                            <a class="dropdown-item <?= ($y == $anio) ? 'active' : '' ?>"
                               href="<?= base_url('indicadores-sst/' . esc($idCliente) . '/matriz-objetivos-metas?anio=' . $y) ?>">
                                <?= $y ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </div>

                <!-- Export PDF -->
                <a href="<?= base_url('indicadores-sst/' . esc($idCliente) . '/matriz-objetivos-metas/pdf?anio=' . esc($anio)) ?>"
                   class="btn btn-danger btn-sm" title="Exportar PDF">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MAIN CONTENT                                -->
<!-- ========================================== -->
<div class="container-fluid py-4">

    <!-- Header Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center py-3">
            <h5 class="fw-bold text-primary mb-1">
                <i class="bi bi-bullseye me-2"></i>MATRIZ DE OBJETIVOS Y METAS DEL SG-SST
            </h5>
            <p class="mb-0 text-muted">
                <?= esc($cliente['nombre_cliente'] ?? '') ?>
                <?php if (!empty($cliente['nit_cliente'])): ?>
                    - NIT: <?= esc($cliente['nit_cliente']) ?>
                <?php endif; ?>
                | <strong>Periodo:</strong> <?= esc($anio) ?>
            </p>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card bg-white">
                <div class="stat-number text-primary"><?= $totalIndicadores ?></div>
                <div class="stat-label">Total Indicadores</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card bg-white">
                <div class="stat-number text-info"><?= $totalMedidos ?></div>
                <div class="stat-label">Medidos</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card bg-white">
                <div class="stat-number text-success"><?= $totalCumplen ?></div>
                <div class="stat-label">Cumplen Meta</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card bg-white">
                <div class="stat-number text-danger"><?= $totalNoCumplen ?></div>
                <div class="stat-label">No Cumplen Meta</div>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover tabla-matriz mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;">N&deg;</th>
                            <th class="text-start" style="min-width: 200px;">Indicador</th>
                            <th style="width: 50px;">Tipo</th>
                            <th style="width: 70px;">Meta</th>
                            <th style="width: 80px;">Periodicidad</th>
                            <?php foreach ($periodLabels as $label): ?>
                                <th style="min-width: 65px;"><?= esc($label) ?></th>
                            <?php endforeach; ?>
                            <th style="width: 55px;">Cumple</th>
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
                                    <i class="bi <?= esc($tipoCfg['icon']) ?> me-1"></i>
                                    <?= esc($tipoCfg['label']) ?>
                                    <span class="badge bg-secondary ms-1"><?= count($grupos[$tipoKey]) ?></span>
                                </td>
                            </tr>
                            <?php foreach ($grupos[$tipoKey] as $ind):
                                $consecutivo++;
                                $cumpleGlobal = $ind['cumple_meta'] ?? null;
                            ?>
                            <tr>
                                <!-- N -->
                                <td class="text-center fw-semibold"><?= $consecutivo ?></td>
                                <!-- Indicador -->
                                <td>
                                    <a href="<?= base_url('indicadores-sst/' . esc($idCliente) . '/ficha-tecnica/' . esc($ind['id_indicador'])) ?>"
                                       class="fw-semibold text-decoration-none" title="Ver Ficha Técnica" target="_blank">
                                        <?= esc($ind['nombre_indicador'] ?? '') ?>
                                        <i class="bi bi-box-arrow-up-right" style="font-size: 0.6rem;"></i>
                                    </a>
                                    <?php if (!empty($ind['formula'])): ?>
                                        <br><small class="text-muted"><?= esc($ind['formula']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <!-- Tipo badge -->
                                <td class="text-center">
                                    <span class="badge <?= esc($tipoCfg['badge']) ?> tipo-badge-sm">
                                        <?= esc($tipoCfg['letter']) ?>
                                    </span>
                                </td>
                                <!-- Meta -->
                                <td class="text-center">
                                    <?= esc($ind['meta'] ?? '-') ?>
                                    <?php if (!empty($ind['unidad_medida'])): ?>
                                        <small class="text-muted"><?= esc($ind['unidad_medida']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <!-- Periodicidad -->
                                <td class="text-center">
                                    <small><?= esc(ucfirst($ind['periodicidad'] ?? 'N/A')) ?></small>
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
                                    <td class="text-center"><?= number_format((float)$resultado, 1) ?></td>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                                <!-- Cumple global -->
                                <td class="text-center">
                                    <?php if ($cumpleGlobal !== null && $cumpleGlobal !== '' && (int)$cumpleGlobal === 1): ?>
                                        <span class="badge bg-success">SI</span>
                                    <?php elseif ($cumpleGlobal !== null && $cumpleGlobal !== '' && (int)$cumpleGlobal === 0): ?>
                                        <span class="badge bg-danger">NO</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>

                        <?php if ($totalIndicadores === 0): ?>
                        <tr>
                            <td colspan="<?= 5 + count($periodLabels) + 1 ?>" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle me-1"></i>No hay indicadores registrados para el periodo <?= esc($anio) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <h6 class="fw-bold small mb-2"><i class="bi bi-info-circle me-1"></i>Leyenda</h6>
            <div>
                <span class="leyenda-item">
                    <span class="leyenda-color" style="background-color: #d4edda; border: 1px solid #c3e6cb;"></span>
                    Resultado cumple la meta
                </span>
                <span class="leyenda-item">
                    <span class="leyenda-color" style="background-color: #f8d7da; border: 1px solid #f5c6cb;"></span>
                    Resultado NO cumple la meta
                </span>
                <span class="leyenda-item">
                    <span class="leyenda-color" style="background-color: #f8f9fa; border: 1px solid #dee2e6;"></span>
                    Sin datos / No medido
                </span>
                <span class="leyenda-item">
                    <span class="badge bg-info tipo-badge-sm">E</span>
                    Estructura
                </span>
                <span class="leyenda-item">
                    <span class="badge bg-warning text-dark tipo-badge-sm">P</span>
                    Proceso
                </span>
                <span class="leyenda-item">
                    <span class="badge bg-success tipo-badge-sm">R</span>
                    Resultado
                </span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center text-muted small py-3">
        <p class="mb-1">Documento generado el <?= $fechaActual ?> - Sistema de Gestion de Seguridad y Salud en el Trabajo</p>
        <p class="mb-0"><?= esc($cliente['nombre_cliente'] ?? '') ?> - NIT: <?= esc($cliente['nit_cliente'] ?? '') ?></p>
    </div>

</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
