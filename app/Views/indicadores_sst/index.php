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
        /* Toast stack */
        .toast-container { z-index: 9999; }
        .toast { min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,.15); margin-bottom: 8px; }
        /* Modal IA indicadores */
        .ia-card { border: 1px solid #dee2e6; border-radius: 0.5rem; margin-bottom: 0.75rem; transition: all 0.2s; }
        .ia-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .ia-card.ya-existe { opacity: 0.6; border-color: #ffc107; }
        .ia-card .card-header-ia { padding: 0.5rem 0.75rem; background: #f8f9fa; border-bottom: 1px solid #dee2e6; border-radius: 0.5rem 0.5rem 0 0; }
        .ia-card .card-body-ia { padding: 0.75rem; }
        .ia-card .form-control-sm, .ia-card .form-select-sm { font-size: 0.8rem; }
        .ia-card .nombre-input { font-weight: 600; border: none; background: transparent; width: 100%; font-size: 0.9rem; }
        .ia-card .nombre-input:focus { background: #fff; border: 1px solid #86b7fe; outline: 0; box-shadow: 0 0 0 0.15rem rgba(13,110,253,.25); border-radius: 0.25rem; }
        .collapsible-section { display: none; padding-top: 0.5rem; border-top: 1px dashed #dee2e6; margin-top: 0.5rem; }
        .collapsible-section.show { display: block; }
        .brecha-badge { font-size: 0.7rem; margin: 2px; }
        .ia-spinner { display: none; }
        .ia-spinner.active { display: inline-block; }
        /* Colores custom para categorias que no tienen clase Bootstrap bg-* */
        .bg-orange { background-color: #fd7e14 !important; color: #fff !important; }
        .text-orange { color: #fd7e14 !important; }
        .bg-purple { background-color: #6f42c1 !important; color: #fff !important; }
        .text-purple { color: #6f42c1 !important; }
        .bg-teal { background-color: #20c997 !important; color: #fff !important; }
        .text-teal { color: #20c997 !important; }
        .border-orange { border-color: #fd7e14 !important; }
        .border-purple { border-color: #6f42c1 !important; }
        .border-teal { border-color: #20c997 !important; }
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
                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/dashboard') ?>" class="btn btn-warning btn-sm me-1">
                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                </a>
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
            <div class="d-flex gap-2">
                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/dashboard') ?>" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-speedometer2 me-1"></i>Ver Dashboard
                </a>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="verificarYGenerarIA()">
                    <i class="bi bi-robot me-1"></i>Generar con IA
                </button>
                <button type="button" class="btn btn-outline-success btn-sm" onclick="verificarYGenerarActividades()">
                    <i class="bi bi-list-task me-1"></i>Generar Actividades con IA
                </button>
                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/crear') ?><?= $categoriaFiltro ? '?categoria=' . $categoriaFiltro : '' ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Nuevo Indicador
                </a>
            </div>
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

                <!-- Generador IA de Indicadores -->
                <div class="card border-0 shadow-sm card-compact">
                    <div class="card-header bg-white py-2">
                        <small class="fw-bold"><i class="bi bi-robot me-1 text-primary"></i>Generador IA</small>
                    </div>
                    <div class="card-body">
                        <div id="panelBrechas" class="mb-2">
                            <small class="text-muted"><i class="bi bi-hourglass-split me-1"></i>Cargando analisis...</small>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1 fw-semibold">Instrucciones (opcional)</label>
                            <textarea id="instruccionesIA" class="form-control form-control-sm" rows="2"
                                      placeholder="Ej: Enfocarse en riesgo psicosocial, priorizar indicadores de resultado..."></textarea>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm w-100" onclick="verificarYGenerarIA()">
                            <i class="bi bi-robot me-1"></i>Generar con IA
                        </button>
                    </div>
                </div>

                <!-- Actividades desde Indicadores (Ingenieria Inversa) -->
                <div class="card border-0 shadow-sm card-compact mt-2">
                    <div class="card-header bg-white py-2">
                        <small class="fw-bold"><i class="bi bi-list-task me-1 text-success"></i>Actividades desde Indicadores</small>
                    </div>
                    <div class="card-body">
                        <div id="panelHuerfanos" class="mb-2">
                            <small class="text-muted"><i class="bi bi-hourglass-split me-1"></i>Cargando analisis...</small>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1 fw-semibold">Instrucciones (opcional)</label>
                            <textarea id="instruccionesActividadesIA" class="form-control form-control-sm" rows="2"
                                      placeholder="Ej: Priorizar actividades de emergencias, incluir simulacros trimestrales..."></textarea>
                        </div>
                        <button type="button" class="btn btn-success btn-sm w-100" onclick="verificarYGenerarActividades()">
                            <i class="bi bi-list-task me-1"></i>Generar Actividades con IA
                        </button>
                    </div>
                </div>
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
                                                    <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/ficha-tecnica/' . $ind['id_indicador']) ?>"
                                                       class="btn btn-outline-info btn-sm" title="Ficha Técnica" target="_blank">
                                                        <i class="bi bi-file-earmark-text"></i>
                                                    </a>
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
                                                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/ficha-tecnica/' . $ind['id_indicador']) ?>"
                                                   class="btn btn-outline-info" title="Ficha Técnica" target="_blank">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </a>
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

    <!-- Modal Generador IA de Indicadores -->
    <div class="modal fade" id="modalIndicadoresIA" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-robot me-2"></i>Indicadores Sugeridos por IA
                        <span class="badge bg-light text-primary ms-2" id="contadorIA">0</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="bodyIndicadoresIA">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Generando indicadores...</p>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <span class="text-muted small" id="resumenSeleccion">0 seleccionados de 0</span>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" id="btnGuardarSeleccionados" onclick="guardarIndicadoresSeleccionados()">
                            <i class="bi bi-save me-1"></i>Guardar Seleccionados (<span id="numSeleccionados">0</span>)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Generador IA de Actividades desde Indicadores -->
    <div class="modal fade" id="modalActividadesIA" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-list-task me-2"></i>Actividades Sugeridas por IA
                        <span class="badge bg-light text-success ms-2" id="contadorActIA">0</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="bodyActividadesIA">
                    <div class="text-center py-5">
                        <div class="spinner-border text-success" role="status"></div>
                        <p class="mt-2 text-muted">Generando actividades...</p>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <span class="text-muted small" id="resumenSeleccionAct">0 seleccionadas de 0</span>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" id="btnGuardarActividades" onclick="guardarActividadesSeleccionadas()">
                            <i class="bi bi-save me-1"></i>Guardar en Plan de Trabajo (<span id="numSeleccionadasAct">0</span>)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast stack -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastStack"></div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        // Cargar brechas al inicio
        cargarBrechas();
    });

    // ==========================================
    // GENERADOR IA DE INDICADORES
    // ==========================================

    const BASE_URL = '<?= base_url() ?>';
    const ID_CLIENTE = <?= $cliente['id_cliente'] ?>;
    let verificacionConfirmada = false;
    let datosContextoCache = null;
    let indicadoresGenerados = [];
    let categoriasSeleccionadasIA = null;

    // --- Toast System ---
    function mostrarToast(tipo, titulo, mensaje) {
        const stack = document.getElementById('toastStack');
        const id = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5);
        const ahora = new Date().toLocaleTimeString('es-CO', {hour:'2-digit', minute:'2-digit', second:'2-digit'});

        const configs = {
            success:  { bg: 'bg-success',  icon: 'bi-check-circle-fill', delay: 6000,  autohide: true },
            error:    { bg: 'bg-danger',   icon: 'bi-x-circle-fill',     delay: 8000,  autohide: true },
            warning:  { bg: 'bg-warning',  icon: 'bi-exclamation-triangle-fill', delay: 6000, autohide: true },
            info:     { bg: 'bg-info',     icon: 'bi-info-circle-fill',  delay: 5000,  autohide: true },
            ia:       { bg: 'bg-primary',  icon: 'bi-robot',             delay: 5000,  autohide: true },
            progress: { bg: 'bg-primary',  icon: '',                     delay: 60000, autohide: false }
        };
        const cfg = configs[tipo] || configs.info;
        const spinnerHTML = tipo === 'progress'
            ? '<span class="spinner-border spinner-border-sm text-white"></span>'
            : `<i class="bi ${cfg.icon}"></i>`;

        const html = `
            <div id="${id}" class="toast" role="alert" data-bs-autohide="${cfg.autohide}" data-bs-delay="${cfg.delay}">
                <div class="toast-header ${cfg.bg} text-white">
                    ${spinnerHTML}
                    <strong class="me-auto ms-2">${titulo}</strong>
                    <small>${ahora}</small>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">${mensaje}</div>
            </div>`;

        stack.insertAdjacentHTML('beforeend', html);
        const el = document.getElementById(id);
        const instance = new bootstrap.Toast(el);
        el.addEventListener('hidden.bs.toast', () => el.remove());
        instance.show();
        return { id, element: el, instance };
    }

    function cerrarToast(ref) {
        if (ref && ref.instance) ref.instance.hide();
    }

    // --- Cargar brechas al inicio ---
    async function cargarBrechas() {
        try {
            const resp = await fetch(`${BASE_URL}/indicadores-sst/${ID_CLIENTE}/ia/contexto`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await resp.json();
            if (data.success) {
                datosContextoCache = data.data;
                renderizarPanelBrechas(data.data.brechas);
            }
        } catch (e) {
            document.getElementById('panelBrechas').innerHTML =
                '<small class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>Error al cargar analisis</small>';
        }
    }

    function renderizarPanelBrechas(brechas) {
        if (!brechas) return;
        const panel = document.getElementById('panelBrechas');
        let html = '';

        if (brechas.categorias_vacias && brechas.categorias_vacias.length > 0) {
            html += '<div class="mb-1"><small class="text-danger fw-semibold"><i class="bi bi-exclamation-circle me-1"></i>' +
                    brechas.categorias_vacias.length + ' categorias vacias</small></div>';
            html += '<div class="mb-2">';
            brechas.categorias_vacias.forEach(c => {
                html += `<span class="badge bg-danger-subtle text-danger brecha-badge">${c}</span>`;
            });
            html += '</div>';
        }

        const escasasCount = brechas.categorias_escasas ? Object.keys(brechas.categorias_escasas).length : 0;
        if (escasasCount > 0) {
            html += '<div class="mb-1"><small class="text-warning fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i>' +
                    escasasCount + ' categorias escasas</small></div>';
        }

        const totalExist = brechas.total_existentes || 0;
        const totalCatsPanel = Object.keys(<?= json_encode($categorias) ?>).filter(k => k !== 'otro').length;
        const vaciasCount = (brechas.categorias_vacias || []).length;
        const catsConInd = totalCatsPanel - vaciasCount;
        const pct = Math.round((catsConInd / totalCatsPanel) * 100);
        const barColor = pct >= 80 ? 'bg-success' : pct >= 50 ? 'bg-warning' : 'bg-danger';

        html += `<div class="mt-2">
            <div class="d-flex justify-content-between small mb-1">
                <span class="text-muted">Cobertura</span>
                <span class="fw-bold">${catsConInd}/${totalCatsPanel} categorias</span>
            </div>
            <div class="progress" style="height:5px;">
                <div class="progress-bar ${barColor}" style="width:${pct}%"></div>
            </div>
            <div class="text-end mt-1"><small class="text-muted">${totalExist} indicadores total</small></div>
        </div>`;

        panel.innerHTML = html;
    }

    // --- SweetAlert de contexto ---
    async function verificarYGenerarIA() {
        if (verificacionConfirmada) {
            return previewIndicadoresIA();
        }

        const progreso = mostrarToast('progress', 'Cargando...', 'Obteniendo contexto del cliente...');

        try {
            let data;
            if (datosContextoCache) {
                data = datosContextoCache;
            } else {
                const resp = await fetch(`${BASE_URL}/indicadores-sst/${ID_CLIENTE}/ia/contexto`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await resp.json();
                if (!json.success) throw new Error(json.message || 'Error al obtener contexto');
                data = json.data;
                datosContextoCache = data;
            }

            cerrarToast(progreso);

            // data IS the context (flat object with empresa, nit, brechas, etc.)
            const ctx = data;
            const brechas = data.brechas || {};

            // Build SweetAlert HTML with inline styles (no BS classes inside Swal)
            let htmlContent = '<div style="text-align:left; font-size:0.9rem;">';

            // Client info
            htmlContent += '<div style="background:#f0f7ff; padding:10px; border-radius:8px; margin-bottom:12px;">';
            htmlContent += `<strong>${ctx.empresa || 'Cliente'}</strong><br>`;
            htmlContent += `<span style="color:#666;">NIT: ${ctx.nit || '-'} | `;
            htmlContent += `Sector: ${ctx.actividad_economica || '-'} | `;
            htmlContent += `Riesgo: ${ctx.nivel_riesgo || '-'} | `;
            htmlContent += `Trabajadores: ${ctx.total_trabajadores || '-'} | `;
            htmlContent += `Estandares: ${ctx.estandares_aplicables || '-'}</span></div>`;

            // Distribution
            if (brechas.distribucion_tipo) {
                const dist = brechas.distribucion_tipo;
                htmlContent += '<div style="background:#f8f9fa; padding:8px; border-radius:8px; margin-bottom:10px;">';
                htmlContent += '<strong>Distribucion actual:</strong> ';
                htmlContent += `Estructura: ${dist.estructura || 0} | Proceso: ${dist.proceso || 0} | Resultado: ${dist.resultado || 0}`;
                if (brechas.recomendacion_tipo) {
                    htmlContent += `<br><small style="color:#666;">${brechas.recomendacion_tipo}</small>`;
                }
                htmlContent += '</div>';
            }

            // Legal gaps
            if (brechas.legales_faltantes && brechas.legales_faltantes.length > 0) {
                htmlContent += '<div style="background:#f8d7da; padding:8px; border-radius:8px; margin-bottom:10px;">';
                htmlContent += `<strong style="color:#721c24;">${brechas.legales_faltantes.length} indicadores legales sin cobertura</strong>`;
                htmlContent += '</div>';
            }

            // Category selector with checkboxes
            const categoriasInfo = <?= json_encode($categorias) ?>;
            const resumenCat = ctx.resumen_categorias || {};
            const vacias = brechas.categorias_vacias || [];
            const totalCats = Object.keys(categoriasInfo).filter(k => k !== 'otro').length;
            const catsConIndicadores = totalCats - vacias.length;

            htmlContent += `<div style="background:#d1e7dd; padding:8px; border-radius:8px; margin-bottom:12px;">`;
            htmlContent += `<strong>${brechas.total_existentes || 0} indicadores</strong> en ${catsConIndicadores} de ${totalCats} categorias`;
            if (vacias.length > 0) {
                htmlContent += ` — <span style="color:#dc3545; font-weight:600;">${vacias.length} sin cobertura</span>`;
            }
            htmlContent += `</div>`;
            const escasas = brechas.categorias_escasas || {};

            htmlContent += '<div style="background:#fff; border:1px solid #dee2e6; border-radius:8px; padding:10px; margin-bottom:8px;">';
            htmlContent += '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">';
            htmlContent += '<strong style="font-size:0.9rem;">Generar indicadores para:</strong>';
            htmlContent += '<div>';
            htmlContent += '<a href="#" onclick="document.querySelectorAll(\'.swal-cat-chk\').forEach(c=>c.checked=true);return false;" style="font-size:0.75rem; margin-right:8px;">Todas</a>';
            htmlContent += '<a href="#" onclick="document.querySelectorAll(\'.swal-cat-chk\').forEach(c=>c.checked=false);return false;" style="font-size:0.75rem;">Ninguna</a>';
            htmlContent += '</div></div>';
            htmlContent += '<div style="display:grid; grid-template-columns:1fr 1fr; gap:4px 12px; max-height:200px; overflow-y:auto;">';

            Object.keys(categoriasInfo).forEach(key => {
                if (key === 'otro') return;
                const info = categoriasInfo[key];
                const count = resumenCat[key]?.total || 0;
                const esVacia = vacias.includes(key);
                const esEscasa = escasas.hasOwnProperty(key);
                const preChecked = esVacia || esEscasa ? 'checked' : '';
                const label = info.nombre || key;

                let badge = '';
                if (esVacia) {
                    badge = '<span style="background:#dc3545; color:#fff; padding:1px 6px; border-radius:10px; font-size:0.65rem; margin-left:4px;">0</span>';
                } else if (esEscasa) {
                    badge = `<span style="background:#ffc107; color:#000; padding:1px 6px; border-radius:10px; font-size:0.65rem; margin-left:4px;">${count}</span>`;
                } else if (count > 0) {
                    badge = `<span style="background:#198754; color:#fff; padding:1px 6px; border-radius:10px; font-size:0.65rem; margin-left:4px;">${count}</span>`;
                }

                htmlContent += `<label style="display:flex; align-items:center; gap:6px; padding:3px 4px; border-radius:4px; cursor:pointer; font-size:0.8rem;${esVacia ? ' background:#fff3cd;' : ''}">`;
                htmlContent += `<input type="checkbox" class="swal-cat-chk" value="${key}" ${preChecked} style="margin:0;">`;
                htmlContent += `<span>${label}</span>${badge}`;
                htmlContent += `</label>`;
            });

            htmlContent += '</div></div>';
            htmlContent += '</div>';

            const result = await Swal.fire({
                title: 'Contexto del Cliente',
                html: htmlContent,
                icon: 'info',
                width: 700,
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-robot"></i> Generar Indicadores',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#0d6efd',
                preConfirm: () => {
                    const checks = document.querySelectorAll('.swal-cat-chk:checked');
                    if (checks.length === 0) {
                        Swal.showValidationMessage('Seleccione al menos una categoria');
                        return false;
                    }
                    return Array.from(checks).map(c => c.value);
                }
            });

            if (result.isConfirmed) {
                verificacionConfirmada = true;
                categoriasSeleccionadasIA = result.value;
                previewIndicadoresIA();
            }
        } catch (e) {
            cerrarToast(progreso);
            mostrarToast('error', 'Error', e.message || 'No se pudo obtener el contexto del cliente');
        }
    }

    // --- Preview indicadores IA ---
    async function previewIndicadoresIA() {
        const instrucciones = document.getElementById('instruccionesIA').value.trim();
        const modal = new bootstrap.Modal(document.getElementById('modalIndicadoresIA'));
        modal.show();

        // Reset modal body
        document.getElementById('bodyIndicadoresIA').innerHTML =
            '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Generando indicadores con IA...</p></div>';

        const progreso = mostrarToast('progress', 'Generando...', 'La IA esta analizando brechas y generando indicadores...');

        try {
            const resp = await fetch(`${BASE_URL}/indicadores-sst/${ID_CLIENTE}/ia/preview`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    instrucciones,
                    categorias: categoriasSeleccionadasIA || null
                })
            });
            const data = await resp.json();
            cerrarToast(progreso);

            if (!data.success) {
                throw new Error(data.message || 'Error al generar indicadores');
            }

            indicadoresGenerados = data.data.indicadores || [];
            renderizarModalIndicadores(data.data);
            mostrarToast('ia', 'Indicadores Generados', `Se generaron ${indicadoresGenerados.length} indicadores sugeridos.`);
        } catch (e) {
            cerrarToast(progreso);
            document.getElementById('bodyIndicadoresIA').innerHTML =
                `<div class="text-center py-5 text-danger"><i class="bi bi-exclamation-circle" style="font-size:2rem;"></i><p class="mt-2">${e.message}</p>
                <button class="btn btn-outline-primary btn-sm" onclick="previewIndicadoresIA()"><i class="bi bi-arrow-clockwise me-1"></i>Reintentar</button></div>`;
            mostrarToast('error', 'Error al Generar', e.message);
        }
    }

    // --- Renderizar modal con cards editables ---
    function renderizarModalIndicadores(data) {
        const body = document.getElementById('bodyIndicadoresIA');
        const indicadores = data.indicadores || [];

        if (indicadores.length === 0) {
            body.innerHTML = '<div class="text-center py-4 text-muted"><i class="bi bi-emoji-neutral" style="font-size:2rem;"></i><p class="mt-2">No se generaron indicadores. Intente con otras instrucciones.</p></div>';
            return;
        }

        // Summary
        let html = '<div class="alert alert-info py-2 small mb-3">';
        html += `<i class="bi bi-info-circle me-1"></i>`;
        html += `<strong>${indicadores.length}</strong> indicadores sugeridos. `;
        const yaExisten = indicadores.filter(i => i.ya_existe).length;
        if (yaExisten > 0) {
            html += `<span class="text-warning fw-semibold">${yaExisten} similares a existentes</span> (opacos, desmarcados). `;
        }
        html += 'Revise, edite y seleccione los que desea guardar.</div>';

        const categoriasDisponibles = <?= json_encode(array_keys($categorias)) ?>;

        // Cards
        indicadores.forEach((ind, idx) => {
            const yaExiste = ind.ya_existe || false;
            const checked = ind.seleccionado !== false && !yaExiste ? 'checked' : '';

            html += `<div class="ia-card ${yaExiste ? 'ya-existe' : ''}" id="iaCard${idx}">`;
            html += `<div class="card-header-ia d-flex align-items-center gap-2">`;
            html += `<input type="checkbox" class="form-check-input ia-checkbox" data-idx="${idx}" ${checked} onchange="actualizarContador()">`;
            html += `<input type="text" class="nombre-input flex-grow-1" id="nombre_${idx}" value="${escHTML(ind.nombre || '')}" placeholder="Nombre del indicador">`;
            html += `<span class="badge bg-${ind.tipo === 'estructura' ? 'info' : ind.tipo === 'proceso' ? 'warning' : 'success'}">${ucFirst(ind.tipo || 'proceso')}</span>`;
            html += `<span class="badge bg-secondary">${ucFirst(ind.categoria || '')}</span>`;
            if (yaExiste) html += `<span class="badge bg-warning text-dark">Ya existe</span>`;
            html += `</div>`;

            html += `<div class="card-body-ia">`;

            // Formula
            html += `<div class="mb-2">`;
            html += `<label class="form-label small mb-0 fw-semibold">Formula</label>`;
            html += `<textarea class="form-control form-control-sm" id="formula_${idx}" rows="1">${escHTML(ind.formula || '')}</textarea>`;
            html += `</div>`;

            // Row: meta, unidad, periodicidad, phva, tipo, categoria
            html += `<div class="row g-2 mb-2">`;
            html += `<div class="col-2"><label class="form-label small mb-0">Meta</label><input type="number" class="form-control form-control-sm" id="meta_${idx}" value="${ind.meta || 100}" step="0.1"></div>`;
            html += `<div class="col-2"><label class="form-label small mb-0">Unidad</label><input type="text" class="form-control form-control-sm" id="unidad_${idx}" value="${escHTML(ind.unidad || '%')}"></div>`;
            html += `<div class="col-2"><label class="form-label small mb-0">Periodicidad</label>`;
            html += `<select class="form-select form-select-sm" id="periodicidad_${idx}">`;
            ['mensual','trimestral','semestral','anual'].forEach(p => {
                html += `<option value="${p}" ${(ind.periodicidad || 'trimestral') === p ? 'selected' : ''}>${ucFirst(p)}</option>`;
            });
            html += `</select></div>`;
            html += `<div class="col-2"><label class="form-label small mb-0">PHVA</label>`;
            html += `<select class="form-select form-select-sm" id="phva_${idx}">`;
            ['planear','hacer','verificar','actuar'].forEach(p => {
                html += `<option value="${p}" ${(ind.phva || 'verificar') === p ? 'selected' : ''}>${ucFirst(p)}</option>`;
            });
            html += `</select></div>`;
            html += `<div class="col-2"><label class="form-label small mb-0">Tipo</label>`;
            html += `<select class="form-select form-select-sm" id="tipo_${idx}">`;
            ['estructura','proceso','resultado'].forEach(t => {
                html += `<option value="${t}" ${(ind.tipo || 'proceso') === t ? 'selected' : ''}>${ucFirst(t)}</option>`;
            });
            html += `</select></div>`;
            html += `<div class="col-2"><label class="form-label small mb-0">Categoria</label>`;
            html += `<select class="form-select form-select-sm" id="categoria_${idx}">`;
            categoriasDisponibles.forEach(c => {
                html += `<option value="${c}" ${(ind.categoria || '') === c ? 'selected' : ''}>${c}</option>`;
            });
            html += `</select></div>`;
            html += `</div>`;

            // Description
            html += `<div class="mb-2">`;
            html += `<label class="form-label small mb-0 fw-semibold">Descripcion</label>`;
            html += `<textarea class="form-control form-control-sm" id="descripcion_${idx}" rows="1">${escHTML(ind.descripcion || '')}</textarea>`;
            html += `</div>`;

            // Collapsible: Ficha Tecnica
            html += `<div class="d-flex gap-2 mb-1">`;
            html += `<button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleSection('ficha_${idx}')"><i class="bi bi-file-earmark-text me-1"></i>Ficha Tecnica</button>`;
            html += `<button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleSection('ia_${idx}')"><i class="bi bi-robot me-1"></i>Mejorar con IA</button>`;
            html += `</div>`;

            // Ficha tecnica section
            html += `<div class="collapsible-section" id="ficha_${idx}">`;
            html += `<div class="mb-2"><label class="form-label small mb-0">Definicion</label>`;
            html += `<textarea class="form-control form-control-sm" id="definicion_${idx}" rows="2">${escHTML(ind.definicion || '')}</textarea></div>`;
            html += `<div class="mb-2"><label class="form-label small mb-0">Interpretacion</label>`;
            html += `<textarea class="form-control form-control-sm" id="interpretacion_${idx}" rows="2">${escHTML(ind.interpretacion || '')}</textarea></div>`;
            html += `<div class="mb-2"><label class="form-label small mb-0">Origen de datos</label>`;
            html += `<textarea class="form-control form-control-sm" id="origen_datos_${idx}" rows="1">${escHTML(ind.origen_datos || '')}</textarea></div>`;
            html += `<div class="row g-2">`;
            html += `<div class="col-6"><label class="form-label small mb-0">Cargo responsable</label>`;
            html += `<input type="text" class="form-control form-control-sm" id="cargo_responsable_${idx}" value="${escHTML(ind.cargo_responsable || '')}"></div>`;
            html += `<div class="col-6"><label class="form-label small mb-0">Cargos que conocen resultado</label>`;
            html += `<input type="text" class="form-control form-control-sm" id="cargos_conocer_${idx}" value="${escHTML(ind.cargos_conocer_resultado || '')}"></div>`;
            html += `</div></div>`;

            // IA section
            html += `<div class="collapsible-section" id="ia_${idx}">`;
            html += `<div class="mb-2"><label class="form-label small mb-0">Instrucciones para mejorar</label>`;
            html += `<textarea class="form-control form-control-sm" id="instrIA_${idx}" rows="2" placeholder="Ej: Hacerlo mas especifico para el sector construccion..."></textarea></div>`;
            html += `<button type="button" class="btn btn-primary btn-sm" onclick="regenerarIndicadorConIA(${idx})">`;
            html += `<span class="ia-spinner" id="spinnerRegen_${idx}"><span class="spinner-border spinner-border-sm me-1"></span></span>`;
            html += `<i class="bi bi-arrow-clockwise me-1"></i>Regenerar este indicador</button>`;
            html += `</div>`;

            html += `</div></div>`;
        });

        body.innerHTML = html;
        actualizarContador();
    }

    // --- Toggle collapsible sections ---
    function toggleSection(id) {
        const el = document.getElementById(id);
        if (el) el.classList.toggle('show');
    }

    // --- Update selection counter ---
    function actualizarContador() {
        const checks = document.querySelectorAll('.ia-checkbox');
        const total = checks.length;
        const selected = document.querySelectorAll('.ia-checkbox:checked').length;

        document.getElementById('contadorIA').textContent = total;
        document.getElementById('numSeleccionados').textContent = selected;
        document.getElementById('resumenSeleccion').textContent = `${selected} seleccionados de ${total}`;
        document.getElementById('btnGuardarSeleccionados').disabled = selected === 0;
    }

    // --- Read indicator data from DOM ---
    function getIndicadorData(idx) {
        return {
            nombre: document.getElementById(`nombre_${idx}`).value.trim(),
            tipo: document.getElementById(`tipo_${idx}`).value,
            categoria: document.getElementById(`categoria_${idx}`).value,
            formula: document.getElementById(`formula_${idx}`).value.trim(),
            meta: parseFloat(document.getElementById(`meta_${idx}`).value) || 100,
            unidad: document.getElementById(`unidad_${idx}`).value.trim() || '%',
            periodicidad: document.getElementById(`periodicidad_${idx}`).value,
            phva: document.getElementById(`phva_${idx}`).value,
            descripcion: document.getElementById(`descripcion_${idx}`).value.trim(),
            definicion: document.getElementById(`definicion_${idx}`)?.value.trim() || '',
            interpretacion: document.getElementById(`interpretacion_${idx}`)?.value.trim() || '',
            origen_datos: document.getElementById(`origen_datos_${idx}`)?.value.trim() || '',
            cargo_responsable: document.getElementById(`cargo_responsable_${idx}`)?.value.trim() || '',
            cargos_conocer_resultado: document.getElementById(`cargos_conocer_${idx}`)?.value.trim() || ''
        };
    }

    // --- Regenerate single indicator with IA ---
    async function regenerarIndicadorConIA(idx) {
        const spinner = document.getElementById(`spinnerRegen_${idx}`);
        const instrucciones = document.getElementById(`instrIA_${idx}`).value.trim();
        const indicadorActual = getIndicadorData(idx);

        spinner.classList.add('active');

        try {
            const resp = await fetch(`${BASE_URL}/indicadores-sst/${ID_CLIENTE}/ia/regenerar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ indicador: indicadorActual, instrucciones })
            });
            const data = await resp.json();
            spinner.classList.remove('active');

            if (!data.success) throw new Error(data.message || 'Error al regenerar');

            const nuevo = data.data;
            // Update fields
            if (nuevo.nombre) document.getElementById(`nombre_${idx}`).value = nuevo.nombre;
            if (nuevo.formula) document.getElementById(`formula_${idx}`).value = nuevo.formula;
            if (nuevo.meta !== undefined) document.getElementById(`meta_${idx}`).value = nuevo.meta;
            if (nuevo.unidad) document.getElementById(`unidad_${idx}`).value = nuevo.unidad;
            if (nuevo.periodicidad) document.getElementById(`periodicidad_${idx}`).value = nuevo.periodicidad;
            if (nuevo.phva) document.getElementById(`phva_${idx}`).value = nuevo.phva;
            if (nuevo.tipo) document.getElementById(`tipo_${idx}`).value = nuevo.tipo;
            if (nuevo.categoria) document.getElementById(`categoria_${idx}`).value = nuevo.categoria;
            if (nuevo.descripcion) document.getElementById(`descripcion_${idx}`).value = nuevo.descripcion;
            if (nuevo.definicion) document.getElementById(`definicion_${idx}`).value = nuevo.definicion;
            if (nuevo.interpretacion) document.getElementById(`interpretacion_${idx}`).value = nuevo.interpretacion;
            if (nuevo.origen_datos) document.getElementById(`origen_datos_${idx}`).value = nuevo.origen_datos;
            if (nuevo.cargo_responsable) document.getElementById(`cargo_responsable_${idx}`).value = nuevo.cargo_responsable;
            if (nuevo.cargos_conocer_resultado) document.getElementById(`cargos_conocer_${idx}`).value = nuevo.cargos_conocer_resultado;

            mostrarToast('ia', 'Indicador Mejorado', `"${nuevo.nombre || indicadorActual.nombre}" regenerado con IA.`);
        } catch (e) {
            spinner.classList.remove('active');
            mostrarToast('error', 'Error al Regenerar', e.message);
        }
    }

    // --- Save selected indicators ---
    async function guardarIndicadoresSeleccionados() {
        const checks = document.querySelectorAll('.ia-checkbox:checked');
        if (checks.length === 0) {
            mostrarToast('warning', 'Sin seleccion', 'Seleccione al menos un indicador para guardar.');
            return;
        }

        const btn = document.getElementById('btnGuardarSeleccionados');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

        const indicadores = [];
        checks.forEach(chk => {
            const idx = parseInt(chk.dataset.idx);
            indicadores.push(getIndicadorData(idx));
        });

        const progreso = mostrarToast('progress', 'Guardando...', `Guardando ${indicadores.length} indicadores en la base de datos...`);

        try {
            const resp = await fetch(`${BASE_URL}/indicadores-sst/${ID_CLIENTE}/ia/guardar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ indicadores })
            });
            const data = await resp.json();
            cerrarToast(progreso);

            if (!data.success) throw new Error(data.message || 'Error al guardar');

            const res = data.data;
            if (res.errores && res.errores.length > 0) {
                mostrarToast('warning', 'Guardado Parcial',
                    `${res.creados} creados, ${res.existentes} ya existian. Errores: ${res.errores.length}`);
            } else {
                mostrarToast('success', 'Indicadores Guardados',
                    `${res.creados} indicadores creados exitosamente.${res.existentes > 0 ? ` (${res.existentes} ya existian)` : ''}`);
            }

            // Close modal and reload
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('modalIndicadoresIA'))?.hide();
                location.reload();
            }, 1500);
        } catch (e) {
            cerrarToast(progreso);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save me-1"></i>Guardar Seleccionados (<span id="numSeleccionados">' + checks.length + '</span>)';
            mostrarToast('error', 'Error al Guardar', e.message);
        }
    }

    // --- Helpers ---
    function escHTML(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function ucFirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // ==========================================
    // GENERADOR IA DE ACTIVIDADES DESDE INDICADORES
    // (Ingenieria Inversa: Indicadores → Actividades PTA)
    // ==========================================

    let verificacionActividadesConfirmada = false;
    let datosContextoActividadesCache = null;
    let actividadesGeneradas = [];
    let categoriasSeleccionadasActIA = null;

    // --- Cargar panel de indicadores huerfanos al inicio ---
    async function cargarHuerfanos() {
        try {
            const resp = await fetch(`${BASE_URL}/indicadores-sst/${ID_CLIENTE}/actividades-ia/contexto`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await resp.json();
            if (data.success) {
                datosContextoActividadesCache = data.data;
                renderizarPanelHuerfanos(data.data);
            }
        } catch (e) {
            document.getElementById('panelHuerfanos').innerHTML =
                '<small class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>Error al cargar analisis</small>';
        }
    }

    function renderizarPanelHuerfanos(data) {
        const panel = document.getElementById('panelHuerfanos');
        const huerfanos = data.indicadores_huerfanos || [];
        const totalInd = data.total_indicadores || 0;
        const totalPTA = data.total_pta || 0;

        let html = '';

        if (huerfanos.length > 0) {
            html += `<div class="mb-1"><small class="text-warning fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i>${huerfanos.length} indicadores sin actividades PTA</small></div>`;
            // Agrupar por categoria
            const porCat = {};
            huerfanos.forEach(h => {
                if (!porCat[h.categoria]) porCat[h.categoria] = 0;
                porCat[h.categoria]++;
            });
            html += '<div class="mb-2">';
            Object.entries(porCat).forEach(([cat, count]) => {
                html += `<span class="badge bg-warning-subtle text-warning me-1 mb-1">${cat}: ${count}</span>`;
            });
            html += '</div>';
        } else if (totalInd > 0) {
            html += '<div class="mb-1"><small class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>Todos los indicadores tienen actividades asociadas</small></div>';
        } else {
            html += '<div class="mb-1"><small class="text-muted"><i class="bi bi-info-circle me-1"></i>Sin indicadores creados aun</small></div>';
        }

        html += `<div class="mt-1"><small class="text-muted">${totalInd} indicadores | ${totalPTA} actividades PTA</small></div>`;

        panel.innerHTML = html;
    }

    // --- SweetAlert de contexto para actividades ---
    async function verificarYGenerarActividades() {
        if (verificacionActividadesConfirmada) {
            return previewActividadesIA();
        }

        const progreso = mostrarToast('progress', 'Cargando...', 'Obteniendo contexto para actividades...');

        try {
            let data;
            if (datosContextoActividadesCache) {
                data = datosContextoActividadesCache;
            } else {
                const resp = await fetch(`${BASE_URL}/indicadores-sst/${ID_CLIENTE}/actividades-ia/contexto`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await resp.json();
                if (!json.success) throw new Error(json.message || 'Error al obtener contexto');
                data = json.data;
                datosContextoActividadesCache = data;
            }

            cerrarToast(progreso);

            const ctx = data;
            const huerfanos = data.indicadores_huerfanos || [];
            const indicadoresPorCat = data.indicadores_por_categoria || {};

            // Build SweetAlert HTML
            let htmlContent = '<div style="text-align:left; font-size:0.9rem;">';

            // Client info
            htmlContent += '<div style="background:#f0f7ff; padding:10px; border-radius:8px; margin-bottom:12px;">';
            htmlContent += `<strong>${ctx.empresa || 'Cliente'}</strong><br>`;
            htmlContent += `<span style="color:#666;">Sector: ${ctx.actividad_economica || '-'} | `;
            htmlContent += `Riesgo: ${ctx.nivel_riesgo || '-'} | `;
            htmlContent += `Trabajadores: ${ctx.total_trabajadores || '-'} | `;
            htmlContent += `Estandares: ${ctx.estandares_aplicables || '-'}</span></div>`;

            // Summary indicators vs PTA
            htmlContent += '<div style="background:#d1e7dd; padding:8px; border-radius:8px; margin-bottom:10px;">';
            htmlContent += `<strong>${ctx.total_indicadores || 0}</strong> indicadores totales | `;
            htmlContent += `<strong>${ctx.total_pta || 0}</strong> actividades en PTA`;
            if (huerfanos.length > 0) {
                htmlContent += ` | <span style="color:#dc3545; font-weight:600;">${huerfanos.length} indicadores sin actividades</span>`;
            }
            htmlContent += '</div>';

            // Orphan indicators detail
            if (huerfanos.length > 0) {
                const porCat = {};
                huerfanos.forEach(h => {
                    if (!porCat[h.categoria]) porCat[h.categoria] = [];
                    porCat[h.categoria].push(h.nombre);
                });

                htmlContent += '<div style="background:#fff3cd; padding:8px; border-radius:8px; margin-bottom:10px;">';
                htmlContent += '<strong style="color:#856404;">Indicadores sin actividades PTA:</strong><br>';
                Object.entries(porCat).forEach(([cat, nombres]) => {
                    htmlContent += `<div style="margin-top:4px;"><span style="background:#ffc107; color:#000; padding:1px 6px; border-radius:10px; font-size:0.7rem;">${cat} (${nombres.length})</span> `;
                    htmlContent += nombres.slice(0, 3).map(n => `<small>${n}</small>`).join(', ');
                    if (nombres.length > 3) htmlContent += ` <small style="color:#666;">+${nombres.length - 3} mas</small>`;
                    htmlContent += '</div>';
                });
                htmlContent += '</div>';
            }

            // Category selector — only categories that HAVE indicators
            const categoriasConInd = Object.keys(indicadoresPorCat).filter(k => k !== 'otro' && indicadoresPorCat[k].length > 0);

            if (categoriasConInd.length > 0) {
                htmlContent += '<div style="background:#fff; border:1px solid #dee2e6; border-radius:8px; padding:10px; margin-bottom:8px;">';
                htmlContent += '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">';
                htmlContent += '<strong style="font-size:0.9rem;">Generar actividades para:</strong>';
                htmlContent += '<div>';
                htmlContent += '<a href="#" onclick="document.querySelectorAll(\'.swal-act-chk\').forEach(c=>c.checked=true);return false;" style="font-size:0.75rem; margin-right:8px;">Todas</a>';
                htmlContent += '<a href="#" onclick="document.querySelectorAll(\'.swal-act-chk\').forEach(c=>c.checked=false);return false;" style="font-size:0.75rem;">Ninguna</a>';
                htmlContent += '</div></div>';
                htmlContent += '<div style="display:grid; grid-template-columns:1fr 1fr; gap:4px 12px; max-height:200px; overflow-y:auto;">';

                // Pre-check orphan categories
                const catsHuerfanas = [...new Set(huerfanos.map(h => h.categoria))];

                categoriasConInd.forEach(key => {
                    const count = indicadoresPorCat[key].length;
                    const esHuerfana = catsHuerfanas.includes(key);
                    const preChecked = esHuerfana ? 'checked' : '';

                    let badge = '';
                    if (esHuerfana) {
                        badge = `<span style="background:#dc3545; color:#fff; padding:1px 6px; border-radius:10px; font-size:0.65rem; margin-left:4px;">${count}</span>`;
                    } else {
                        badge = `<span style="background:#198754; color:#fff; padding:1px 6px; border-radius:10px; font-size:0.65rem; margin-left:4px;">${count}</span>`;
                    }

                    htmlContent += `<label style="display:flex; align-items:center; gap:6px; padding:3px 4px; border-radius:4px; cursor:pointer; font-size:0.8rem;${esHuerfana ? ' background:#fff3cd;' : ''}">`;
                    htmlContent += `<input type="checkbox" class="swal-act-chk" value="${key}" ${preChecked} style="margin:0;">`;
                    htmlContent += `<span>${key}</span>${badge}`;
                    htmlContent += `</label>`;
                });

                htmlContent += '</div></div>';
            }

            htmlContent += '</div>';

            const result = await Swal.fire({
                title: 'Generar Actividades desde Indicadores',
                html: htmlContent,
                icon: 'question',
                width: 700,
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-list-task"></i> Generar Actividades',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#198754',
                preConfirm: () => {
                    const checks = document.querySelectorAll('.swal-act-chk:checked');
                    if (checks.length === 0) {
                        Swal.showValidationMessage('Seleccione al menos una categoria');
                        return false;
                    }
                    return Array.from(checks).map(c => c.value);
                }
            });

            if (result.isConfirmed) {
                verificacionActividadesConfirmada = true;
                categoriasSeleccionadasActIA = result.value;
                previewActividadesIA();
            }
        } catch (e) {
            cerrarToast(progreso);
            mostrarToast('error', 'Error', e.message || 'No se pudo obtener el contexto');
        }
    }

    // --- Preview actividades IA ---
    async function previewActividadesIA() {
        const instrucciones = document.getElementById('instruccionesActividadesIA').value.trim();
        const modal = new bootstrap.Modal(document.getElementById('modalActividadesIA'));
        modal.show();

        // Reset modal body
        document.getElementById('bodyActividadesIA').innerHTML =
            '<div class="text-center py-5"><div class="spinner-border text-success"></div><p class="mt-2 text-muted">Generando actividades con IA...</p></div>';

        const progreso = mostrarToast('progress', 'Generando...', 'La IA esta generando actividades desde los indicadores...');

        try {
            const resp = await fetch(`${BASE_URL}/indicadores-sst/${ID_CLIENTE}/actividades-ia/preview`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    instrucciones,
                    categorias: categoriasSeleccionadasActIA || null
                })
            });
            const data = await resp.json();
            cerrarToast(progreso);

            if (!data.success) {
                throw new Error(data.message || 'Error al generar actividades');
            }

            actividadesGeneradas = data.data.actividades || [];
            renderizarModalActividades(data.data);
            mostrarToast('ia', 'Actividades Generadas', `Se generaron ${actividadesGeneradas.length} actividades sugeridas.`);
        } catch (e) {
            cerrarToast(progreso);
            document.getElementById('bodyActividadesIA').innerHTML =
                `<div class="text-center py-5 text-danger"><i class="bi bi-exclamation-circle" style="font-size:2rem;"></i><p class="mt-2">${e.message}</p>
                <button class="btn btn-outline-success btn-sm" onclick="previewActividadesIA()"><i class="bi bi-arrow-clockwise me-1"></i>Reintentar</button></div>`;
            mostrarToast('error', 'Error al Generar', e.message);
        }
    }

    // --- Renderizar modal con cards editables de actividades ---
    function renderizarModalActividades(data) {
        const body = document.getElementById('bodyActividadesIA');
        const actividades = data.actividades || [];

        if (actividades.length === 0) {
            body.innerHTML = '<div class="text-center py-4 text-muted"><i class="bi bi-emoji-neutral" style="font-size:2rem;"></i><p class="mt-2">No se generaron actividades. Intente con otras instrucciones.</p></div>';
            return;
        }

        // PHVA colors
        const phvaColors = { 'PLANEAR': 'primary', 'HACER': 'success', 'VERIFICAR': 'warning', 'ACTUAR': 'danger' };
        const meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        // Summary
        let html = '<div class="alert alert-info py-2 small mb-3">';
        html += `<i class="bi bi-info-circle me-1"></i>`;
        html += `<strong>${actividades.length}</strong> actividades sugeridas. `;
        if (data.indicadores_cubiertos > 0) {
            html += `Cubren <strong>${data.indicadores_cubiertos}</strong> indicadores. `;
        }
        const yaExisten = actividades.filter(a => a.ya_existe).length;
        if (yaExisten > 0) {
            html += `<span class="text-warning fw-semibold">${yaExisten} similares a existentes en PTA</span> (opacos, desmarcadas). `;
        }
        html += 'Revise, edite y seleccione las que desea guardar en el Plan de Trabajo.</div>';

        // Cards
        actividades.forEach((act, idx) => {
            const yaExiste = act.ya_existe || false;
            const checked = act.seleccionado !== false && !yaExiste ? 'checked' : '';
            const phvaColor = phvaColors[(act.phva || 'HACER').toUpperCase()] || 'secondary';

            html += `<div class="ia-card ${yaExiste ? 'ya-existe' : ''}" id="actCard${idx}">`;
            html += `<div class="card-header-ia d-flex align-items-center gap-2">`;
            html += `<input type="checkbox" class="form-check-input act-checkbox" data-idx="${idx}" ${checked} onchange="actualizarContadorActividades()">`;
            html += `<input type="text" class="nombre-input flex-grow-1" id="actNombre_${idx}" value="${escHTML(act.actividad || '')}" placeholder="Nombre de la actividad">`;
            html += `<span class="badge bg-${phvaColor}">${(act.phva || 'HACER').toUpperCase()}</span>`;
            html += `<span class="badge bg-secondary">${escHTML(act.categoria_origen || '')}</span>`;
            if (yaExiste) html += `<span class="badge bg-warning text-dark">Ya existe en PTA</span>`;
            html += `</div>`;

            html += `<div class="card-body-ia">`;

            // Description
            html += `<div class="mb-2">`;
            html += `<label class="form-label small mb-0 fw-semibold">Descripcion</label>`;
            html += `<textarea class="form-control form-control-sm" id="actDesc_${idx}" rows="2">${escHTML(act.descripcion || '')}</textarea>`;
            html += `</div>`;

            // Row: responsable, numeral, periodicidad, phva, mes_inicio, meta
            html += `<div class="row g-2 mb-2">`;
            html += `<div class="col-3"><label class="form-label small mb-0">Responsable</label><input type="text" class="form-control form-control-sm" id="actResp_${idx}" value="${escHTML(act.responsable || 'Responsable SST')}"></div>`;
            html += `<div class="col-2"><label class="form-label small mb-0">Numeral</label><input type="text" class="form-control form-control-sm" id="actNumeral_${idx}" value="${escHTML(act.numeral || '')}"></div>`;
            html += `<div class="col-2"><label class="form-label small mb-0">Periodicidad</label>`;
            html += `<select class="form-select form-select-sm" id="actPeriod_${idx}">`;
            ['mensual','bimestral','trimestral','semestral','anual'].forEach(p => {
                html += `<option value="${p}" ${(act.periodicidad || 'trimestral') === p ? 'selected' : ''}>${ucFirst(p)}</option>`;
            });
            html += `</select></div>`;
            html += `<div class="col-2"><label class="form-label small mb-0">PHVA</label>`;
            html += `<select class="form-select form-select-sm" id="actPhva_${idx}" onchange="actualizarBadgePHVA(${idx})">`;
            ['PLANEAR','HACER','VERIFICAR','ACTUAR'].forEach(p => {
                html += `<option value="${p}" ${(act.phva || 'HACER').toUpperCase() === p ? 'selected' : ''}>${p}</option>`;
            });
            html += `</select></div>`;
            html += `<div class="col-1"><label class="form-label small mb-0">Mes</label>`;
            html += `<select class="form-select form-select-sm" id="actMes_${idx}">`;
            for (let m = 1; m <= 12; m++) {
                html += `<option value="${m}" ${(act.mes_inicio || 1) == m ? 'selected' : ''}>${m}</option>`;
            }
            html += `</select></div>`;
            html += `<div class="col-2"><label class="form-label small mb-0">Meta</label><input type="text" class="form-control form-control-sm" id="actMeta_${idx}" value="${escHTML(act.meta || '')}"></div>`;
            html += `</div>`;

            // Collapsible buttons
            html += `<div class="d-flex gap-2 mb-1">`;
            html += `<button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleSection('actIA_${idx}')"><i class="bi bi-robot me-1"></i>Mejorar con IA</button>`;
            html += `</div>`;

            // IA section
            html += `<div class="collapsible-section" id="actIA_${idx}">`;
            html += `<div class="mb-2"><label class="form-label small mb-0">Instrucciones para mejorar</label>`;
            html += `<textarea class="form-control form-control-sm" id="actInstrIA_${idx}" rows="2" placeholder="Ej: Hacerla mas especifica para el sector salud, cambiar periodicidad..."></textarea></div>`;
            html += `<button type="button" class="btn btn-primary btn-sm" onclick="regenerarActividadConIA(${idx})">`;
            html += `<span class="ia-spinner" id="spinnerRegenAct_${idx}"><span class="spinner-border spinner-border-sm me-1"></span></span>`;
            html += `<i class="bi bi-arrow-clockwise me-1"></i>Regenerar esta actividad</button>`;
            html += `</div>`;

            html += `</div></div>`;
        });

        body.innerHTML = html;
        actualizarContadorActividades();
    }

    // --- Update PHVA badge color on change ---
    function actualizarBadgePHVA(idx) {
        const sel = document.getElementById(`actPhva_${idx}`);
        const card = document.getElementById(`actCard${idx}`);
        if (!sel || !card) return;
        const phvaColors = { 'PLANEAR': 'primary', 'HACER': 'success', 'VERIFICAR': 'warning', 'ACTUAR': 'danger' };
        const badges = card.querySelectorAll('.card-header-ia .badge');
        badges.forEach(b => {
            if (['PLANEAR','HACER','VERIFICAR','ACTUAR'].includes(b.textContent.trim())) {
                b.className = `badge bg-${phvaColors[sel.value] || 'secondary'}`;
                b.textContent = sel.value;
            }
        });
    }

    // --- Selection counter for activities ---
    function actualizarContadorActividades() {
        const checks = document.querySelectorAll('.act-checkbox');
        const total = checks.length;
        const selected = document.querySelectorAll('.act-checkbox:checked').length;

        document.getElementById('contadorActIA').textContent = total;
        document.getElementById('numSeleccionadasAct').textContent = selected;
        document.getElementById('resumenSeleccionAct').textContent = `${selected} seleccionadas de ${total}`;
        document.getElementById('btnGuardarActividades').disabled = selected === 0;
    }

    // --- Read activity data from DOM ---
    function getActividadData(idx) {
        return {
            actividad: document.getElementById(`actNombre_${idx}`).value.trim(),
            descripcion: document.getElementById(`actDesc_${idx}`).value.trim(),
            responsable: document.getElementById(`actResp_${idx}`).value.trim() || 'Responsable SST',
            numeral: document.getElementById(`actNumeral_${idx}`).value.trim(),
            periodicidad: document.getElementById(`actPeriod_${idx}`).value,
            phva: document.getElementById(`actPhva_${idx}`).value,
            mes_inicio: parseInt(document.getElementById(`actMes_${idx}`).value) || 1,
            meta: document.getElementById(`actMeta_${idx}`).value.trim(),
            categoria_origen: actividadesGeneradas[idx]?.categoria_origen || 'otro'
        };
    }

    // --- Regenerate single activity with IA ---
    async function regenerarActividadConIA(idx) {
        const spinner = document.getElementById(`spinnerRegenAct_${idx}`);
        const instrucciones = document.getElementById(`actInstrIA_${idx}`).value.trim();
        const actividadActual = getActividadData(idx);

        if (!instrucciones) {
            mostrarToast('warning', 'Sin instrucciones', 'Escriba instrucciones para mejorar la actividad.');
            return;
        }

        spinner.classList.add('active');

        try {
            const resp = await fetch(`${BASE_URL}/indicadores-sst/${ID_CLIENTE}/actividades-ia/regenerar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ actividad: actividadActual, instrucciones })
            });
            const data = await resp.json();
            spinner.classList.remove('active');

            if (!data.success) throw new Error(data.message || 'Error al regenerar');

            const nuevo = data.data;
            // Update fields
            if (nuevo.actividad) document.getElementById(`actNombre_${idx}`).value = nuevo.actividad;
            if (nuevo.descripcion) document.getElementById(`actDesc_${idx}`).value = nuevo.descripcion;
            if (nuevo.responsable) document.getElementById(`actResp_${idx}`).value = nuevo.responsable;
            if (nuevo.numeral) document.getElementById(`actNumeral_${idx}`).value = nuevo.numeral;
            if (nuevo.periodicidad) document.getElementById(`actPeriod_${idx}`).value = nuevo.periodicidad;
            if (nuevo.phva) {
                document.getElementById(`actPhva_${idx}`).value = nuevo.phva.toUpperCase();
                actualizarBadgePHVA(idx);
            }
            if (nuevo.mes_inicio) document.getElementById(`actMes_${idx}`).value = nuevo.mes_inicio;
            if (nuevo.meta) document.getElementById(`actMeta_${idx}`).value = nuevo.meta;

            // Update stored data
            actividadesGeneradas[idx] = Object.assign(actividadesGeneradas[idx] || {}, nuevo);

            mostrarToast('ia', 'Actividad Mejorada', `"${nuevo.actividad || actividadActual.actividad}" regenerada con IA.`);
        } catch (e) {
            spinner.classList.remove('active');
            mostrarToast('error', 'Error al Regenerar', e.message);
        }
    }

    // --- Save selected activities to PTA ---
    async function guardarActividadesSeleccionadas() {
        const checks = document.querySelectorAll('.act-checkbox:checked');
        if (checks.length === 0) {
            mostrarToast('warning', 'Sin seleccion', 'Seleccione al menos una actividad para guardar.');
            return;
        }

        const btn = document.getElementById('btnGuardarActividades');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

        const actividades = [];
        checks.forEach(chk => {
            const idx = parseInt(chk.dataset.idx);
            actividades.push(getActividadData(idx));
        });

        const progreso = mostrarToast('progress', 'Guardando...', `Guardando ${actividades.length} actividades en el Plan de Trabajo...`);

        try {
            const resp = await fetch(`${BASE_URL}/indicadores-sst/${ID_CLIENTE}/actividades-ia/guardar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ actividades, anio: new Date().getFullYear() })
            });
            const data = await resp.json();
            cerrarToast(progreso);

            if (!data.success) throw new Error(data.message || 'Error al guardar');

            const res = data.data;
            if (res.errores && res.errores.length > 0) {
                mostrarToast('warning', 'Guardado Parcial',
                    `${res.creadas} creadas, ${res.existentes} ya existian. Errores: ${res.errores.length}`);
            } else {
                mostrarToast('success', 'Actividades Guardadas',
                    `${res.creadas} actividades agregadas al Plan de Trabajo.${res.existentes > 0 ? ` (${res.existentes} ya existian)` : ''}`);
            }

            // Close modal and reload
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('modalActividadesIA'))?.hide();
                location.reload();
            }, 1500);
        } catch (e) {
            cerrarToast(progreso);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save me-1"></i>Guardar en Plan de Trabajo (<span id="numSeleccionadasAct">' + checks.length + '</span>)';
            mostrarToast('error', 'Error al Guardar', e.message);
        }
    }

    // Load orphan analysis on page load
    cargarHuerfanos();
    </script>
</body>
</html>
