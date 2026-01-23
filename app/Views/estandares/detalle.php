<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estándar <?= esc($estandar['numero_estandar']) ?> - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/estandares/<?= $cliente['id_cliente'] ?>">
                <i class="bi bi-arrow-left me-2"></i>Volver a Estándares
            </a>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row">
            <div class="col-md-8">
                <!-- Detalle del estándar -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <span class="badge bg-primary me-2"><?= $estandar['numero_estandar'] ?></span>
                                <?= esc($estandar['nombre']) ?>
                            </h5>
                            <span class="badge bg-light text-dark">Peso: <?= $estandar['peso'] ?>%</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($estandar['descripcion'])): ?>
                            <p><?= esc($estandar['descripcion']) ?></p>
                        <?php endif; ?>

                        <div class="row mt-4">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Ciclo PHVA</small>
                                <strong><?= ucfirst($estandar['ciclo_phva'] ?? 'N/D') ?></strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Categoría</small>
                                <strong><?= esc($estandar['categoria'] ?? 'N/D') ?></strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Nivel Mínimo</small>
                                <strong><?= $estandar['nivel_minimo'] ?? 60 ?> estándares</strong>
                            </div>
                        </div>

                        <?php if (!empty($estandar['requisitos'])): ?>
                            <hr>
                            <h6>Requisitos</h6>
                            <p class="text-muted"><?= nl2br(esc($estandar['requisitos'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Estado de cumplimiento -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-check2-square me-2"></i>Estado de Cumplimiento</h6>
                    </div>
                    <div class="card-body">
                        <form action="/estandares/actualizar-estado" method="post">
                            <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                            <input type="hidden" name="id_estandar" value="<?= $estandar['id_estandar'] ?>">

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Estado</label>
                                    <select name="estado" class="form-select">
                                        <option value="pendiente" <?= ($clienteEstandar['estado'] ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="cumple" <?= ($clienteEstandar['estado'] ?? '') === 'cumple' ? 'selected' : '' ?>>Cumple</option>
                                        <option value="no_cumple" <?= ($clienteEstandar['estado'] ?? '') === 'no_cumple' ? 'selected' : '' ?>>No Cumple</option>
                                        <option value="en_proceso" <?= ($clienteEstandar['estado'] ?? '') === 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                                        <option value="no_aplica" <?= ($clienteEstandar['estado'] ?? '') === 'no_aplica' ? 'selected' : '' ?>>No Aplica</option>
                                    </select>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Observaciones</label>
                                    <textarea name="observaciones" class="form-control" rows="2"><?= esc($clienteEstandar['observaciones'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Guardar Cambios
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Documentos relacionados -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Documentos Relacionados</h6>
                        <a href="/documentacion/nuevo/<?= $cliente['id_cliente'] ?>?estandar=<?= $estandar['id_estandar'] ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-lg me-1"></i>Agregar
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($documentos)): ?>
                            <p class="text-muted mb-0">No hay documentos vinculados a este estándar.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($documentos as $doc): ?>
                                    <a href="/documentacion/ver/<?= $doc['id_documento'] ?>"
                                       class="list-group-item list-group-item-action d-flex justify-content-between">
                                        <div>
                                            <strong><?= esc($doc['codigo']) ?></strong> - <?= esc($doc['nombre']) ?>
                                        </div>
                                        <span class="badge bg-<?= $doc['estado'] === 'aprobado' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($doc['estado']) ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Info del cliente -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-building me-2"></i>Cliente</h6>
                    </div>
                    <div class="card-body">
                        <h5><?= esc($cliente['nombre_cliente']) ?></h5>
                        <p class="text-muted mb-0"><?= esc($cliente['nit'] ?? '') ?></p>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Acciones</h6>
                    </div>
                    <div class="card-body">
                        <a href="/estandares/<?= $cliente['id_cliente'] ?>" class="btn btn-outline-primary w-100 mb-2">
                            <i class="bi bi-list-check me-1"></i>Ver Todos los Estándares
                        </a>
                        <a href="/documentacion/<?= $cliente['id_cliente'] ?>" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-folder me-1"></i>Ir a Documentación
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
