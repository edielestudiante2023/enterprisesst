<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indicadores SST - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
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
                <a href="<?= base_url('generador-ia/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">
                    <i class="bi bi-graph-up me-2"></i>Indicadores del SG-SST
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('clientes') ?>">Clientes</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('cliente/' . $cliente['id_cliente']) ?>"><?= esc($cliente['nombre_cliente']) ?></a></li>
                        <li class="breadcrumb-item active">Indicadores</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/crear') ?><?= $categoriaFiltro ? '?categoria=' . $categoriaFiltro : '' ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Agregar Indicador
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

        <!-- Navegacion por Categorías -->
        <div class="row g-3 mb-4">
            <!-- Tarjeta "Todos" -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>" class="text-decoration-none">
                    <div class="card categoria-card h-100 text-center <?= !$categoriaFiltro ? 'active' : '' ?>">
                        <div class="card-body py-3 position-relative">
                            <span class="categoria-badge bg-secondary text-white"><?= $verificacion['total'] ?></span>
                            <div class="categoria-icon text-secondary">
                                <i class="bi bi-grid-3x3-gap-fill"></i>
                            </div>
                            <h6 class="card-title mb-0 small">Todos</h6>
                        </div>
                    </div>
                </a>
            </div>

            <?php foreach ($categorias as $catKey => $catInfo): ?>
                <?php
                $stats = $resumenCategorias[$catKey] ?? null;
                $count = $stats['total'] ?? 0;
                ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '?categoria=' . $catKey) ?>" class="text-decoration-none">
                        <div class="card categoria-card h-100 text-center <?= $categoriaFiltro === $catKey ? 'active' : '' ?>">
                            <div class="card-body py-3 position-relative">
                                <?php if ($count > 0): ?>
                                    <span class="categoria-badge bg-<?= $catInfo['color'] ?> text-white"><?= $count ?></span>
                                <?php endif; ?>
                                <div class="categoria-icon text-<?= $catInfo['color'] ?>">
                                    <i class="bi <?= $catInfo['icono'] ?>"></i>
                                </div>
                                <h6 class="card-title mb-0 small"><?= esc($catInfo['nombre']) ?></h6>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <!-- Panel izquierdo: Resumen y sugerencias -->
            <div class="col-md-4">
                <!-- Info del cliente -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Cliente</h6>
                        <h5 class="mb-1"><?= esc($cliente['nombre_cliente']) ?></h5>
                        <p class="text-muted mb-2">NIT: <?= esc($cliente['nit_cliente']) ?></p>
                        <span class="badge bg-<?= $estandares <= 7 ? 'info' : ($estandares <= 21 ? 'warning' : 'danger') ?>">
                            <?= $estandares ?> Estandares
                        </span>
                    </div>
                </div>

                <!-- Resumen de cumplimiento -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="bi bi-speedometer me-1"></i>Estado General
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if ($verificacion['total'] > 0): ?>
                            <!-- Barra de medición -->
                            <p class="small text-muted mb-1">Indicadores medidos</p>
                            <div class="progress mb-3" style="height: 20px;">
                                <div class="progress-bar bg-info"
                                     style="width: <?= $verificacion['porcentaje_medicion'] ?>%">
                                    <?= $verificacion['porcentaje_medicion'] ?>%
                                </div>
                            </div>

                            <!-- Barra de cumplimiento -->
                            <p class="small text-muted mb-1">Cumplimiento de metas</p>
                            <div class="progress mb-3" style="height: 20px;">
                                <div class="progress-bar bg-<?= $verificacion['porcentaje_cumplimiento'] >= 80 ? 'success' : ($verificacion['porcentaje_cumplimiento'] >= 50 ? 'warning' : 'danger') ?>"
                                     style="width: <?= $verificacion['porcentaje_cumplimiento'] ?>%">
                                    <?= $verificacion['porcentaje_cumplimiento'] ?>%
                                </div>
                            </div>

                            <div class="row text-center">
                                <div class="col-4">
                                    <span class="h4 text-success"><?= $verificacion['cumplen'] ?></span>
                                    <br><small class="text-muted">Cumplen</small>
                                </div>
                                <div class="col-4">
                                    <span class="h4 text-danger"><?= $verificacion['no_cumplen'] ?></span>
                                    <br><small class="text-muted">No cumplen</small>
                                </div>
                                <div class="col-4">
                                    <span class="h4 text-secondary"><?= $verificacion['sin_medir'] ?></span>
                                    <br><small class="text-muted">Sin medir</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                No hay indicadores configurados
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Resumen por Categoría -->
                <?php if (!empty($resumenCategorias)): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">
                                <i class="bi bi-pie-chart me-1"></i>Por Programa
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($resumenCategorias as $catKey => $stats): ?>
                                    <?php $catInfo = $categorias[$catKey] ?? ['nombre' => ucfirst($catKey), 'icono' => 'bi-folder', 'color' => 'secondary']; ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi <?= $catInfo['icono'] ?> text-<?= $catInfo['color'] ?> me-2"></i>
                                            <span class="small"><?= esc($catInfo['nombre']) ?></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-<?= $catInfo['color'] ?>"><?= $stats['total'] ?></span>
                                            <?php if ($stats['porcentaje_cumplimiento'] !== null): ?>
                                                <small class="text-muted ms-1"><?= $stats['porcentaje_cumplimiento'] ?>%</small>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Sugerencias de IA -->
                <?php if (!empty($sugerencias['sugeridos'])): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">
                                <i class="bi bi-lightbulb me-1"></i>Indicadores Sugeridos
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-3">
                                Indicadores recomendados para <?= $estandares ?> estandares:
                            </p>
                            <?php foreach ($sugerencias['sugeridos'] as $sug): ?>
                                <div class="d-flex align-items-start mb-2">
                                    <i class="bi bi-plus-circle text-primary me-2 mt-1"></i>
                                    <div>
                                        <strong class="small"><?= esc($sug['nombre']) ?></strong>
                                        <br><small class="text-muted"><?= esc($sug['formula']) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <button type="button" class="btn btn-outline-primary btn-sm w-100 mt-3" id="btnGenerarSugeridos">
                                <i class="bi bi-magic me-1"></i>Crear indicadores sugeridos
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Panel derecho: Lista de indicadores -->
            <div class="col-md-8">
                <?php
                // Determinar qué categorías mostrar
                $categoriasAMostrar = $categoriaFiltro ? [$categoriaFiltro => $indicadoresPorCategoria[$categoriaFiltro] ?? []] : $indicadoresPorCategoria;
                $hayIndicadores = false;
                ?>

                <?php foreach ($categoriasAMostrar as $catKey => $indicadores): ?>
                    <?php
                    if (empty($indicadores)) continue;
                    $hayIndicadores = true;
                    $catInfo = $categorias[$catKey] ?? ['nombre' => ucfirst($catKey), 'icono' => 'bi-folder', 'color' => 'secondary', 'descripcion' => ''];
                    $periodicidades = [
                        'mensual' => 'Mensual',
                        'trimestral' => 'Trimestral',
                        'semestral' => 'Semestral',
                        'anual' => 'Anual'
                    ];
                    ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">
                                    <i class="bi <?= $catInfo['icono'] ?> text-<?= $catInfo['color'] ?> me-2"></i>
                                    <?= esc($catInfo['nombre']) ?>
                                    <span class="badge bg-<?= $catInfo['color'] ?> ms-2"><?= count($indicadores) ?></span>
                                </h6>
                                <small class="text-muted"><?= esc($catInfo['descripcion']) ?></small>
                            </div>
                            <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/crear?categoria=' . $catKey) ?>" class="btn btn-sm btn-outline-<?= $catInfo['color'] ?>">
                                <i class="bi bi-plus-lg"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <?php foreach ($indicadores as $ind): ?>
                                <div class="card mb-3 indicador-card border-<?= $catInfo['color'] ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?= esc($ind['nombre_indicador']) ?></h6>
                                                <?php if (!empty($ind['formula'])): ?>
                                                    <p class="text-muted small mb-2">
                                                        <i class="bi bi-calculator me-1"></i>
                                                        <strong>Formula:</strong> <?= esc($ind['formula']) ?>
                                                    </p>
                                                <?php endif; ?>

                                                <div class="row g-2 mt-2">
                                                    <div class="col-auto">
                                                        <span class="badge bg-light text-dark border">
                                                            <i class="bi bi-bullseye me-1"></i>
                                                            Meta: <?= $ind['meta'] !== null ? $ind['meta'] . esc($ind['unidad_medida']) : 'Sin definir' ?>
                                                        </span>
                                                    </div>
                                                    <div class="col-auto">
                                                        <span class="badge bg-light text-dark border">
                                                            <i class="bi bi-calendar-check me-1"></i>
                                                            <?= $periodicidades[$ind['periodicidad']] ?? ucfirst($ind['periodicidad'] ?? 'Sin definir') ?>
                                                        </span>
                                                    </div>
                                                    <div class="col-auto">
                                                        <span class="badge bg-light text-dark border">
                                                            <i class="bi bi-arrow-repeat me-1"></i>
                                                            PHVA: <?= strtoupper($ind['phva'] ?? 'V') ?>
                                                        </span>
                                                    </div>
                                                    <div class="col-auto">
                                                        <span class="badge bg-<?= $ind['tipo_indicador'] === 'estructura' ? 'info' : ($ind['tipo_indicador'] === 'proceso' ? 'warning' : 'success') ?> text-white">
                                                            <?= ucfirst($ind['tipo_indicador'] ?? 'proceso') ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-end ms-3">
                                                <div class="btn-group btn-group-sm mb-2">
                                                    <button type="button" class="btn btn-outline-success btn-medir"
                                                            data-id="<?= $ind['id_indicador'] ?>"
                                                            data-nombre="<?= esc($ind['nombre_indicador']) ?>"
                                                            title="Registrar medicion">
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
                                            </div>
                                        </div>

                                        <!-- Resultado actual -->
                                        <div class="mt-3 pt-3 border-top">
                                            <div class="row align-items-center">
                                                <div class="col-md-4">
                                                    <small class="text-muted d-block">Ultimo Resultado:</small>
                                                    <?php if ($ind['valor_resultado'] !== null): ?>
                                                        <span class="h5 mb-0"><?= number_format($ind['valor_resultado'], 1) ?><?= esc($ind['unidad_medida']) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Sin medir</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <small class="text-muted d-block">Estado:</small>
                                                    <?php if ($ind['cumple_meta'] === null): ?>
                                                        <span class="badge bg-secondary">Pendiente</span>
                                                    <?php elseif ($ind['cumple_meta'] == 1): ?>
                                                        <span class="badge bg-success">Cumple</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">No cumple</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <small class="text-muted d-block">Ultima medicion:</small>
                                                    <?php if (!empty($ind['fecha_medicion'])): ?>
                                                        <span><?= date('d/m/Y', strtotime($ind['fecha_medicion'])) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Sin fecha</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (!$hayIndicadores): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <?php if ($categoriaFiltro): ?>
                                <?php $catInfo = $categorias[$categoriaFiltro] ?? ['nombre' => ucfirst($categoriaFiltro), 'icono' => 'bi-folder', 'color' => 'secondary']; ?>
                                <i class="bi <?= $catInfo['icono'] ?> text-<?= $catInfo['color'] ?>" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">No hay indicadores de <?= esc($catInfo['nombre']) ?></h5>
                                <p class="text-muted">Cree el primer indicador para esta categoria</p>
                                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/crear?categoria=' . $categoriaFiltro) ?>" class="btn btn-<?= $catInfo['color'] ?>">
                                    <i class="bi bi-plus-lg me-1"></i>Agregar Indicador
                                </a>
                            <?php else: ?>
                                <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">No hay indicadores configurados</h5>
                                <p class="text-muted">Comience agregando indicadores o use los sugeridos</p>
                                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/crear') ?>" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>Agregar Indicador
                                </a>
                            <?php endif; ?>
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let indicadorActual = null;

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
