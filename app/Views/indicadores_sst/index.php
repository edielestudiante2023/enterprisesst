<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indicadores SST - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <style>
        .categoria-card {
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }
        .categoria-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .categoria-card.active {
            border-color: var(--bs-primary);
        }
        .categoria-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            min-width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .categoria-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .indicador-card {
            border-left: 4px solid;
        }
        /* Acordeones */
        .accordion-categoria .accordion-button {
            padding: 0.75rem 1rem;
        }
        .accordion-categoria .accordion-button:not(.collapsed) {
            background-color: var(--bs-light);
        }
        .accordion-categoria .accordion-body {
            padding: 0.5rem;
            max-height: 400px;
            overflow-y: auto;
        }
        .indicador-mini {
            border-left: 3px solid;
            margin-bottom: 0.5rem;
            background: #fff;
            border-radius: 0.25rem;
            transition: all 0.2s;
        }
        .indicador-mini:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .indicador-header {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .indicador-header .chevron {
            transition: transform 0.2s;
        }
        .indicador-mini.expanded .indicador-header .chevron {
            transform: rotate(180deg);
        }
        .indicador-detalles {
            display: none;
            padding: 0.5rem 0.75rem;
            padding-top: 0;
            border-top: 1px dashed #dee2e6;
            margin-top: 0.5rem;
        }
        .indicador-mini.expanded .indicador-detalles {
            display: block;
        }
        .detalle-item {
            margin-bottom: 0.35rem;
        }
        .detalle-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.7rem;
            text-transform: uppercase;
        }
        /* Filtros de estado */
        .filtro-estado {
            cursor: pointer;
            transition: all 0.2s;
            user-select: none;
        }
        .filtro-estado:hover {
            transform: scale(1.05);
        }
        .filtro-estado.active {
            box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.3);
        }
        /* Tabla */
        #tablaIndicadores_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }
        .estado-badge {
            min-width: 80px;
            display: inline-block;
            text-align: center;
        }
        /* Categorías compactas */
        .categoria-card {
            min-height: auto;
        }
        .categoria-card .card-body {
            padding: 0.5rem !important;
        }
        .categoria-card .categoria-icon {
            font-size: 1.2rem;
            margin-bottom: 0.25rem;
        }
        .categoria-card .card-title {
            font-size: 0.7rem;
        }
        .categoria-card.empty {
            opacity: 0.5;
        }
        .categoria-card.empty:hover {
            opacity: 0.8;
        }
        .categoria-badge {
            top: -6px;
            right: -6px;
            min-width: 20px;
            height: 20px;
            font-size: 0.65rem;
        }
        /* Panel compacto */
        .card-compact .card-body {
            padding: 0.75rem;
        }
        .card-compact .card-header {
            padding: 0.5rem 0.75rem;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>">
                <i class="bi bi-graph-up me-2"></i>Indicadores SST
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header compacto -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="bi bi-speedometer2 me-2 text-primary"></i>Indicadores del SG-SST
            </h5>
            <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/crear') ?><?= $categoriaFiltro ? '?categoria=' . $categoriaFiltro : '' ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Nuevo Indicador
            </a>
        </div>

        <!-- Alertas -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Navegacion por Categorías - Compacta -->
        <div class="d-flex flex-wrap gap-2 mb-3">
            <!-- Tarjeta "Todos" -->
            <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>" class="text-decoration-none">
                <div class="card categoria-card text-center <?= !$categoriaFiltro ? 'active' : '' ?>" style="min-width: 80px;">
                    <div class="card-body position-relative">
                        <span class="categoria-badge bg-primary text-white"><?= $verificacion['total'] ?></span>
                        <div class="categoria-icon text-primary">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                        </div>
                        <div class="card-title mb-0">Todos</div>
                    </div>
                </div>
            </a>

            <?php foreach ($categorias as $catKey => $catInfo): ?>
                <?php
                $stats = $resumenCategorias[$catKey] ?? null;
                $count = $stats['total'] ?? 0;
                $isEmpty = $count === 0;
                ?>
                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '?categoria=' . $catKey) ?>" class="text-decoration-none">
                    <div class="card categoria-card text-center <?= $categoriaFiltro === $catKey ? 'active' : '' ?> <?= $isEmpty ? 'empty' : '' ?>" style="min-width: 80px;">
                        <div class="card-body position-relative">
                            <?php if ($count > 0): ?>
                                <span class="categoria-badge bg-<?= $catInfo['color'] ?> text-white"><?= $count ?></span>
                            <?php endif; ?>
                            <div class="categoria-icon text-<?= $catInfo['color'] ?>">
                                <i class="bi <?= $catInfo['icono'] ?>"></i>
                            </div>
                            <div class="card-title mb-0"><?= esc($catInfo['nombre']) ?></div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <!-- Panel izquierdo: Resumen compacto -->
            <div class="col-lg-3 col-md-4">
                <!-- Info del cliente - Compacta -->
                <div class="card border-0 shadow-sm mb-2 card-compact">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-0"><?= esc($cliente['nombre_cliente']) ?></h6>
                                <small class="text-muted">NIT: <?= esc($cliente['nit_cliente']) ?></small>
                            </div>
                            <span class="badge bg-<?= $estandares <= 7 ? 'info' : ($estandares <= 21 ? 'warning' : 'danger') ?>">
                                <?= $estandares ?> Est.
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Resumen de cumplimiento - Compacto -->
                <div class="card border-0 shadow-sm mb-2 card-compact">
                    <div class="card-header bg-white py-2">
                        <small class="fw-bold"><i class="bi bi-speedometer me-1"></i>Estado General</small>
                    </div>
                    <div class="card-body">
                        <?php if ($verificacion['total'] > 0): ?>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">Medidos</span>
                                    <span class="fw-bold"><?= $verificacion['porcentaje_medicion'] ?>%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-info" style="width: <?= $verificacion['porcentaje_medicion'] ?>%"></div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">Cumplimiento</span>
                                    <span class="fw-bold"><?= $verificacion['porcentaje_cumplimiento'] ?>%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?= $verificacion['porcentaje_cumplimiento'] >= 80 ? 'success' : ($verificacion['porcentaje_cumplimiento'] >= 50 ? 'warning' : 'danger') ?>"
                                         style="width: <?= $verificacion['porcentaje_cumplimiento'] ?>%"></div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between text-center pt-2 border-top">
                                <div>
                                    <span class="h6 text-success mb-0"><?= $verificacion['cumplen'] ?></span>
                                    <br><small class="text-muted" style="font-size:0.65rem">Cumple</small>
                                </div>
                                <div>
                                    <span class="h6 text-danger mb-0"><?= $verificacion['no_cumplen'] ?></span>
                                    <br><small class="text-muted" style="font-size:0.65rem">No cumple</small>
                                </div>
                                <div>
                                    <span class="h6 text-secondary mb-0"><?= $verificacion['sin_medir'] ?></span>
                                    <br><small class="text-muted" style="font-size:0.65rem">Pendiente</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0 small">
                                <i class="bi bi-info-circle me-1"></i>Sin indicadores
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Resumen por Categoría - Compacto -->
                <?php if (!empty($resumenCategorias)): ?>
                    <div class="card border-0 shadow-sm mb-2 card-compact">
                        <div class="card-header bg-white py-2">
                            <small class="fw-bold"><i class="bi bi-pie-chart me-1"></i>Por Programa</small>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush small">
                                <?php foreach ($resumenCategorias as $catKey => $stats): ?>
                                    <?php $catInfo = $categorias[$catKey] ?? ['nombre' => ucfirst($catKey), 'icono' => 'bi-folder', 'color' => 'secondary']; ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                        <span>
                                            <i class="bi <?= $catInfo['icono'] ?> text-<?= $catInfo['color'] ?> me-1"></i>
                                            <?= esc($catInfo['nombre']) ?>
                                        </span>
                                        <span class="badge bg-<?= $catInfo['color'] ?>"><?= $stats['total'] ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Sugerencias de IA - Compacto -->
                <?php if (!empty($sugerencias['sugeridos'])): ?>
                    <div class="card border-0 shadow-sm card-compact">
                        <div class="card-header bg-white py-2">
                            <small class="fw-bold"><i class="bi bi-lightbulb me-1"></i>Sugeridos</small>
                        </div>
                        <div class="card-body">
                            <?php foreach (array_slice($sugerencias['sugeridos'], 0, 3) as $sug): ?>
                                <div class="d-flex align-items-start mb-1">
                                    <i class="bi bi-plus-circle text-primary me-1 small"></i>
                                    <small><?= esc($sug['nombre']) ?></small>
                                </div>
                            <?php endforeach; ?>
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 mt-2" id="btnGenerarSugeridos">
                                <i class="bi bi-magic me-1"></i>Crear sugeridos
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Panel derecho: Acordeones + Tabla -->
            <div class="col-lg-9 col-md-8">
                <?php
                // Preparar datos
                $categoriasAMostrar = $categoriaFiltro ? [$categoriaFiltro => $indicadoresPorCategoria[$categoriaFiltro] ?? []] : $indicadoresPorCategoria;
                $hayIndicadores = false;
                $todosIndicadores = []; // Para la tabla
                $periodicidades = [
                    'mensual' => 'Mensual',
                    'trimestral' => 'Trimestral',
                    'semestral' => 'Semestral',
                    'anual' => 'Anual'
                ];
                ?>

                <!-- Filtros por estado -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <span class="text-muted small me-2"><i class="bi bi-funnel me-1"></i>Filtrar:</span>
                            <span class="badge bg-light text-dark border filtro-estado active" data-filtro="todos">
                                <i class="bi bi-grid-3x3-gap me-1"></i>Todos
                                <span class="badge bg-secondary ms-1"><?= $verificacion['total'] ?></span>
                            </span>
                            <span class="badge bg-success filtro-estado" data-filtro="cumple">
                                <i class="bi bi-check-circle me-1"></i>Cumple
                                <span class="badge bg-light text-success ms-1"><?= $verificacion['cumplen'] ?></span>
                            </span>
                            <span class="badge bg-danger filtro-estado" data-filtro="no_cumple">
                                <i class="bi bi-x-circle me-1"></i>No cumple
                                <span class="badge bg-light text-danger ms-1"><?= $verificacion['no_cumplen'] ?></span>
                            </span>
                            <span class="badge bg-secondary filtro-estado" data-filtro="sin_medir">
                                <i class="bi bi-hourglass me-1"></i>Sin medir
                                <span class="badge bg-light text-secondary ms-1"><?= $verificacion['sin_medir'] ?></span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Acordeones por categoría -->
                <div class="accordion accordion-categoria mb-4" id="accordionCategorias">
                    <?php $index = 0; ?>
                    <?php foreach ($categoriasAMostrar as $catKey => $indicadores): ?>
                        <?php
                        if (empty($indicadores)) continue;
                        $hayIndicadores = true;
                        $catInfo = $categorias[$catKey] ?? ['nombre' => ucfirst($catKey), 'icono' => 'bi-folder', 'color' => 'secondary', 'descripcion' => ''];

                        // Calcular estadísticas de la categoría
                        $catCumplen = 0;
                        $catNoCumplen = 0;
                        $catSinMedir = 0;
                        foreach ($indicadores as $ind) {
                            $todosIndicadores[] = array_merge($ind, ['categoria_key' => $catKey, 'categoria_nombre' => $catInfo['nombre'], 'categoria_color' => $catInfo['color']]);
                            if ($ind['cumple_meta'] === null) $catSinMedir++;
                            elseif ($ind['cumple_meta'] == 1) $catCumplen++;
                            else $catNoCumplen++;
                        }
                        ?>
                        <div class="accordion-item border-0 shadow-sm mb-2">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapse<?= $catKey ?>">
                                    <i class="bi <?= $catInfo['icono'] ?> text-<?= $catInfo['color'] ?> me-2"></i>
                                    <span class="flex-grow-1">
                                        <?= esc($catInfo['nombre']) ?>
                                        <span class="badge bg-<?= $catInfo['color'] ?> ms-2"><?= count($indicadores) ?></span>
                                    </span>
                                    <span class="me-3 small">
                                        <?php if ($catCumplen > 0): ?><span class="badge bg-success me-1"><?= $catCumplen ?></span><?php endif; ?>
                                        <?php if ($catNoCumplen > 0): ?><span class="badge bg-danger me-1"><?= $catNoCumplen ?></span><?php endif; ?>
                                        <?php if ($catSinMedir > 0): ?><span class="badge bg-secondary"><?= $catSinMedir ?></span><?php endif; ?>
                                    </span>
                                </button>
                            </h2>
                            <div id="collapse<?= $catKey ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>"
                                 data-bs-parent="#accordionCategorias">
                                <div class="accordion-body">
                                    <?php foreach ($indicadores as $ind): ?>
                                        <?php
                                        $estadoClass = $ind['cumple_meta'] === null ? 'secondary' : ($ind['cumple_meta'] == 1 ? 'success' : 'danger');
                                        $estadoTexto = $ind['cumple_meta'] === null ? 'Sin medir' : ($ind['cumple_meta'] == 1 ? 'Cumple' : 'No cumple');
                                        $estadoFiltro = $ind['cumple_meta'] === null ? 'sin_medir' : ($ind['cumple_meta'] == 1 ? 'cumple' : 'no_cumple');
                                        ?>
                                        <div class="indicador-mini border-<?= $catInfo['color'] ?> indicador-filtrable"
                                             data-estado="<?= $estadoFiltro ?>">
                                            <!-- Header clickeable -->
                                            <div class="indicador-header" onclick="this.parentElement.classList.toggle('expanded')">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-chevron-down chevron text-muted small"></i>
                                                        <strong class="small"><?= esc($ind['nombre_indicador']) ?></strong>
                                                    </div>
                                                    <div class="d-flex gap-2 mt-1 flex-wrap ms-4">
                                                        <span class="badge bg-light text-dark border small">
                                                            Meta: <?= $ind['meta'] !== null ? $ind['meta'] . esc($ind['unidad_medida']) : '-' ?>
                                                        </span>
                                                        <span class="badge bg-<?= $estadoClass ?> small">
                                                            <?= $estadoTexto ?>
                                                        </span>
                                                        <?php if ($ind['valor_resultado'] !== null): ?>
                                                            <span class="badge bg-info text-white small">
                                                                <?= number_format($ind['valor_resultado'], 1) ?><?= esc($ind['unidad_medida']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="btn-group btn-group-sm ms-2" onclick="event.stopPropagation()">
                                                    <button type="button" class="btn btn-outline-success btn-sm btn-medir"
                                                            data-id="<?= $ind['id_indicador'] ?>"
                                                            data-nombre="<?= esc($ind['nombre_indicador']) ?>"
                                                            title="Medir">
                                                        <i class="bi bi-speedometer2"></i>
                                                    </button>
                                                    <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/editar/' . $ind['id_indicador']) ?>"
                                                       class="btn btn-outline-primary btn-sm" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar"
                                                            data-id="<?= $ind['id_indicador'] ?>"
                                                            data-nombre="<?= esc($ind['nombre_indicador']) ?>"
                                                            title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <!-- Detalles expandibles -->
                                            <div class="indicador-detalles">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <?php if (!empty($ind['formula'])): ?>
                                                            <div class="detalle-item">
                                                                <div class="detalle-label"><i class="bi bi-calculator me-1"></i>Formula</div>
                                                                <div class="small"><?= esc($ind['formula']) ?></div>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($ind['definicion'])): ?>
                                                            <div class="detalle-item">
                                                                <div class="detalle-label"><i class="bi bi-info-circle me-1"></i>Definicion</div>
                                                                <div class="small text-muted"><?= esc($ind['definicion']) ?></div>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($ind['interpretacion'])): ?>
                                                            <div class="detalle-item">
                                                                <div class="detalle-label"><i class="bi bi-lightbulb me-1"></i>Interpretacion</div>
                                                                <div class="small text-muted"><?= esc($ind['interpretacion']) ?></div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="detalle-item">
                                                            <div class="detalle-label">Tipo</div>
                                                            <span class="badge bg-<?= $ind['tipo_indicador'] === 'estructura' ? 'info' : ($ind['tipo_indicador'] === 'proceso' ? 'warning' : 'success') ?>">
                                                                <?= ucfirst($ind['tipo_indicador'] ?? 'proceso') ?>
                                                            </span>
                                                        </div>
                                                        <div class="detalle-item">
                                                            <div class="detalle-label">PHVA</div>
                                                            <span class="badge bg-secondary"><?= strtoupper($ind['phva'] ?? 'V') ?></span>
                                                        </div>
                                                        <div class="detalle-item">
                                                            <div class="detalle-label">Periodicidad</div>
                                                            <span class="small"><?= $periodicidades[$ind['periodicidad']] ?? ucfirst($ind['periodicidad'] ?? '-') ?></span>
                                                        </div>
                                                        <?php if (!empty($ind['fecha_medicion'])): ?>
                                                            <div class="detalle-item">
                                                                <div class="detalle-label">Ultima medicion</div>
                                                                <span class="small"><?= date('d/m/Y', strtotime($ind['fecha_medicion'])) ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="text-end mt-2">
                                        <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/crear?categoria=' . $catKey) ?>"
                                           class="btn btn-sm btn-outline-<?= $catInfo['color'] ?>">
                                            <i class="bi bi-plus-lg me-1"></i>Agregar a <?= esc($catInfo['nombre']) ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $index++; ?>
                    <?php endforeach; ?>
                </div>

                <?php if (!$hayIndicadores): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-4">
                            <?php if ($categoriaFiltro): ?>
                                <?php $catInfo = $categorias[$categoriaFiltro] ?? ['nombre' => ucfirst($categoriaFiltro), 'icono' => 'bi-folder', 'color' => 'secondary']; ?>
                                <i class="bi <?= $catInfo['icono'] ?> text-<?= $catInfo['color'] ?> opacity-50" style="font-size: 2.5rem;"></i>
                                <h6 class="mt-3 mb-1">Sin indicadores en <?= esc($catInfo['nombre']) ?></h6>
                                <p class="text-muted small mb-3">Esta categoría aún no tiene indicadores configurados</p>
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-grid-3x3-gap me-1"></i>Ver todos
                                    </a>
                                    <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/crear?categoria=' . $categoriaFiltro) ?>" class="btn btn-<?= $catInfo['color'] ?> btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i>Crear indicador
                                    </a>
                                </div>
                            <?php else: ?>
                                <i class="bi bi-speedometer2 text-muted opacity-50" style="font-size: 2.5rem;"></i>
                                <h6 class="mt-3 mb-1">Sin indicadores configurados</h6>
                                <p class="text-muted small mb-3">Agregue indicadores manualmente o use las sugerencias</p>
                                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/crear') ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i>Agregar Indicador
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tabla consolidada con DataTables -->
                <?php if ($hayIndicadores): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bi bi-table me-2"></i>Tabla de Indicadores
                        </h6>
                        <span class="badge bg-primary"><?= count($todosIndicadores) ?> registros</span>
                    </div>
                    <div class="card-body">
                        <table id="tablaIndicadores" class="table table-hover table-sm w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Indicador</th>
                                    <th>Categoria</th>
                                    <th>Meta</th>
                                    <th>Resultado</th>
                                    <th>Estado</th>
                                    <th>Periodicidad</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todosIndicadores as $ind): ?>
                                    <?php
                                    $estadoClass = $ind['cumple_meta'] === null ? 'secondary' : ($ind['cumple_meta'] == 1 ? 'success' : 'danger');
                                    $estadoTexto = $ind['cumple_meta'] === null ? 'Sin medir' : ($ind['cumple_meta'] == 1 ? 'Cumple' : 'No cumple');
                                    $estadoFiltro = $ind['cumple_meta'] === null ? 'sin_medir' : ($ind['cumple_meta'] == 1 ? 'cumple' : 'no_cumple');
                                    ?>
                                    <tr class="fila-indicador" data-estado="<?= $estadoFiltro ?>">
                                        <td>
                                            <strong><?= esc($ind['nombre_indicador']) ?></strong>
                                            <?php if (!empty($ind['formula'])): ?>
                                                <br><small class="text-muted"><?= esc(substr($ind['formula'], 0, 50)) ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $ind['categoria_color'] ?>">
                                                <?= esc($ind['categoria_nombre']) ?>
                                            </span>
                                        </td>
                                        <td><?= $ind['meta'] !== null ? $ind['meta'] . esc($ind['unidad_medida']) : '-' ?></td>
                                        <td>
                                            <?php if ($ind['valor_resultado'] !== null): ?>
                                                <?= number_format($ind['valor_resultado'], 1) ?><?= esc($ind['unidad_medida']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $estadoClass ?> estado-badge"><?= $estadoTexto ?></span>
                                        </td>
                                        <td><?= $periodicidades[$ind['periodicidad']] ?? ucfirst($ind['periodicidad'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-success btn-medir"
                                                        data-id="<?= $ind['id_indicador'] ?>"
                                                        data-nombre="<?= esc($ind['nombre_indicador']) ?>"
                                                        title="Medir">
                                                    <i class="bi bi-speedometer2"></i>
                                                </button>
                                                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/editar/' . $ind['id_indicador']) ?>"
                                                   class="btn btn-outline-primary" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-eliminar"
                                                        data-id="<?= $ind['id_indicador'] ?>"
                                                        data-nombre="<?= esc($ind['nombre_indicador']) ?>"
                                                        title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal registrar medición -->
    <div class="modal fade" id="modalMedicion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Medicion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formMedicion">
                    <div class="modal-body">
                        <p class="text-muted mb-3">Indicador: <strong id="nombreIndicadorMedicion"></strong></p>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Numerador</label>
                                <input type="number" name="valor_numerador" class="form-control" step="0.01"
                                       placeholder="Ej: 4 ejecutadas">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Denominador</label>
                                <input type="number" name="valor_denominador" class="form-control" step="0.01"
                                       placeholder="Ej: 4 programadas">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha de Medicion</label>
                                <input type="date" name="fecha_medicion" class="form-control"
                                       value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Periodo</label>
                                <input type="text" name="periodo" class="form-control"
                                       value="<?= date('Y-m') ?>" placeholder="Ej: 2024-Q1">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2"
                                      placeholder="Notas sobre esta medicion..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-1"></i>Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal confirmar eliminación -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar eliminacion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Esta seguro que desea eliminar el indicador <strong id="nombreEliminar"></strong>?</p>
                    <p class="text-muted small">Se eliminaran tambien todas las mediciones asociadas.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formEliminar" method="POST" style="display: inline;">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let indicadorActual = null;
        let filtroActual = 'todos';

        // Inicializar DataTables
        const tabla = $('#tablaIndicadores').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ indicadores",
                infoEmpty: "Sin registros",
                infoFiltered: "(filtrado de _MAX_ total)",
                paginate: {
                    first: "Primero",
                    last: "Ultimo",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            },
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
            order: [[4, 'asc']], // Ordenar por estado
            columnDefs: [
                { orderable: false, targets: [6] } // No ordenar columna acciones
            ]
        });

        // Filtros por estado
        document.querySelectorAll('.filtro-estado').forEach(filtro => {
            filtro.addEventListener('click', function() {
                // Actualizar clases active
                document.querySelectorAll('.filtro-estado').forEach(f => f.classList.remove('active'));
                this.classList.add('active');

                filtroActual = this.dataset.filtro;
                aplicarFiltroEstado(filtroActual);
            });
        });

        function aplicarFiltroEstado(filtro) {
            // Filtrar acordeones
            document.querySelectorAll('.indicador-filtrable').forEach(item => {
                if (filtro === 'todos' || item.dataset.estado === filtro) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });

            // Filtrar tabla DataTables
            if (filtro === 'todos') {
                tabla.search('').columns().search('').draw();
            } else {
                const textoFiltro = filtro === 'cumple' ? 'Cumple' :
                                   filtro === 'no_cumple' ? 'No cumple' : 'Sin medir';
                tabla.column(4).search('^' + textoFiltro + '$', true, false).draw();
            }
        }

        // Registrar medición
        document.querySelectorAll('.btn-medir').forEach(btn => {
            btn.addEventListener('click', function() {
                indicadorActual = this.dataset.id;
                document.getElementById('nombreIndicadorMedicion').textContent = this.dataset.nombre;
                new bootstrap.Modal(document.getElementById('modalMedicion')).show();
            });
        });

        document.getElementById('formMedicion').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/medir') ?>/' + indicadorActual, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error al registrar medicion');
                }
            });
        });

        // Eliminar indicador
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const nombre = this.dataset.nombre;

                document.getElementById('nombreEliminar').textContent = nombre;
                document.getElementById('formEliminar').action =
                    '<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/eliminar') ?>/' + id;

                new bootstrap.Modal(document.getElementById('modalEliminar')).show();
            });
        });

        // Generar indicadores sugeridos
        const btnGenerar = document.getElementById('btnGenerarSugeridos');
        if (btnGenerar) {
            btnGenerar.addEventListener('click', function() {
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generando...';

                fetch('<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/generar-sugeridos') ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error al generar indicadores');
                        this.disabled = false;
                        this.innerHTML = '<i class="bi bi-magic me-1"></i>Crear indicadores sugeridos';
                    }
                });
            });
        }
    });
    </script>
</body>
</html>
