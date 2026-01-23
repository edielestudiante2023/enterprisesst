<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Versiones - <?= esc($documento['codigo'] ?? '') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .version-card {
            border-left: 4px solid #3B82F6;
            transition: all 0.2s;
        }
        .version-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .version-card.vigente {
            border-left-color: #10B981;
            background: #F0FDF4;
        }
        .version-card.obsoleto {
            border-left-color: #EF4444;
            opacity: 0.7;
        }
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #E5E7EB;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 8px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #3B82F6;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #3B82F6;
        }
        .timeline-item.vigente::before {
            background: #10B981;
            box-shadow: 0 0 0 2px #10B981;
        }
        .encabezado-iso {
            border: 1px solid #000;
            font-size: 0.85rem;
        }
        .encabezado-iso td, .encabezado-iso th {
            border: 1px solid #000;
            padding: 0.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-clock-history me-2"></i>Control Documental
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <code><?= esc($documento['codigo'] ?? '') ?></code> v<?= esc($documento['version_actual'] ?? '1.0') ?>
                </span>
                <a class="nav-link" href="/documentacion/ver/<?= $documento['id_documento'] ?>">
                    <i class="bi bi-file-text me-1"></i>Ver Documento
                </a>
                <a class="nav-link" href="/documentacion/<?= $cliente['id_cliente'] ?>">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row">
            <!-- Panel lateral -->
            <div class="col-md-4">
                <!-- Información del documento -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Documento</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td class="text-muted">Código:</td>
                                <td><code><?= esc($documento['codigo'] ?? 'Sin código') ?></code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Nombre:</td>
                                <td><?= esc($documento['nombre'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tipo:</td>
                                <td><?= esc($documento['tipo_nombre'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Versión actual:</td>
                                <td><span class="badge bg-primary"><?= esc($documento['version_actual'] ?? '1.0') ?></span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Estado:</td>
                                <td>
                                    <?php
                                    $estadoBadges = [
                                        'borrador' => 'bg-secondary',
                                        'en_revision' => 'bg-warning text-dark',
                                        'pendiente_firma' => 'bg-info',
                                        'aprobado' => 'bg-success',
                                        'obsoleto' => 'bg-danger'
                                    ];
                                    $badge = $estadoBadges[$documento['estado'] ?? 'borrador'] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $badge ?>">
                                        <?= ucfirst(str_replace('_', ' ', $documento['estado'] ?? 'borrador')) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total versiones:</td>
                                <td><?= count($versiones ?? []) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Encabezado ISO Preview -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-layout-text-window me-2"></i>Encabezado ISO</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="encabezado-iso w-100 text-center">
                            <tr>
                                <td rowspan="3" style="width: 25%;">
                                    <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong><br>
                                    <small>NIT: <?= esc($cliente['nit'] ?? '') ?></small>
                                </td>
                                <td colspan="2"><strong>SISTEMA DE GESTIÓN DE SST</strong></td>
                            </tr>
                            <tr>
                                <td colspan="2"><?= esc(strtoupper($documento['nombre'] ?? '')) ?></td>
                            </tr>
                            <tr>
                                <td>Código: <?= esc($documento['codigo'] ?? '') ?></td>
                                <td>Versión: <?= esc($documento['version_actual'] ?? '1.0') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Acciones</h6>
                    </div>
                    <div class="card-body">
                        <?php if (in_array($documento['estado'], ['aprobado', 'en_revision'])): ?>
                            <a href="/control-documental/nueva-version/<?= $documento['id_documento'] ?>"
                               class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-plus-circle me-1"></i>Nueva Versión
                            </a>
                        <?php endif; ?>

                        <a href="/documentacion/editar/<?= $documento['id_documento'] ?>"
                           class="btn btn-outline-primary w-100 mb-2">
                            <i class="bi bi-pencil me-1"></i>Editar Documento
                        </a>

                        <button type="button" class="btn btn-outline-secondary w-100 mb-2"
                                onclick="exportarHistorial()">
                            <i class="bi bi-download me-1"></i>Exportar Historial
                        </button>

                        <?php if ($documento['estado'] !== 'obsoleto'): ?>
                            <button type="button" class="btn btn-outline-danger w-100"
                                    data-bs-toggle="modal" data-bs-target="#modalObsoleto">
                                <i class="bi bi-x-circle me-1"></i>Marcar Obsoleto
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Historial de versiones -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history me-2"></i>Historial de Versiones
                        </h5>
                        <?php if (count($versiones) >= 2): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal" data-bs-target="#modalComparar">
                                <i class="bi bi-arrow-left-right me-1"></i>Comparar Versiones
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($versiones)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-clock text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">No hay versiones registradas aún</p>
                                <p class="text-muted small">Las versiones se crean cuando el documento es aprobado o modificado</p>
                            </div>
                        <?php else: ?>
                            <div class="timeline">
                                <?php foreach ($versiones as $version): ?>
                                    <div class="timeline-item <?= $version['estado'] ?>">
                                        <div class="card version-card <?= $version['estado'] ?> mb-3">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="mb-0">
                                                            <span class="badge <?= $version['tipo_cambio'] === 'mayor' ? 'bg-danger' : 'bg-warning text-dark' ?> me-2">
                                                                <?= $version['tipo_cambio'] === 'mayor' ? 'MAYOR' : 'MENOR' ?>
                                                            </span>
                                                            Versión <?= esc($version['version']) ?>
                                                        </h6>
                                                    </div>
                                                    <div>
                                                        <?php if ($version['estado'] === 'vigente'): ?>
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-check-circle me-1"></i>VIGENTE
                                                            </span>
                                                        <?php elseif ($version['estado'] === 'obsoleto'): ?>
                                                            <span class="badge bg-secondary">OBSOLETO</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <p class="mb-2"><?= esc($version['descripcion_cambio']) ?></p>

                                                <div class="d-flex justify-content-between align-items-center text-muted small">
                                                    <div>
                                                        <i class="bi bi-person me-1"></i><?= esc($version['autorizado_por'] ?? 'Sistema') ?>
                                                    </div>
                                                    <div>
                                                        <i class="bi bi-calendar me-1"></i>
                                                        <?= date('d/m/Y H:i', strtotime($version['fecha'] ?? $version['created_at'])) ?>
                                                    </div>
                                                </div>

                                                <div class="mt-3">
                                                    <a href="/control-documental/ver-version/<?= $version['id_version'] ?>"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye me-1"></i>Ver
                                                    </a>
                                                    <?php if ($version['estado'] !== 'vigente' && !empty($version['contenido_snapshot'])): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-warning"
                                                                onclick="confirmarRestaurar(<?= $version['id_version'] ?>, '<?= esc($version['version']) ?>')">
                                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurar
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if (!empty($version['archivo_pdf'])): ?>
                                                        <a href="<?= esc($version['archivo_pdf']) ?>"
                                                           class="btn btn-sm btn-outline-danger" target="_blank">
                                                            <i class="bi bi-file-pdf me-1"></i>PDF
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tabla de Control de Cambios -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="bi bi-table me-2"></i>Tabla de Control de Cambios (ISO)
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Versión</th>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Descripción del Cambio</th>
                                        <th>Autorizado Por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($versiones)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">
                                                Sin registros de cambios
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($versiones as $v): ?>
                                            <tr>
                                                <td><strong><?= esc($v['version']) ?></strong></td>
                                                <td><?= date('d/m/Y', strtotime($v['fecha'] ?? $v['created_at'])) ?></td>
                                                <td>
                                                    <span class="badge <?= $v['tipo_cambio'] === 'mayor' ? 'bg-danger' : 'bg-info' ?>">
                                                        <?= ucfirst($v['tipo_cambio']) ?>
                                                    </span>
                                                </td>
                                                <td><?= esc($v['descripcion_cambio']) ?></td>
                                                <td><?= esc($v['autorizado_por'] ?? '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Comparar Versiones -->
    <div class="modal fade" id="modalComparar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-arrow-left-right me-2"></i>Comparar Versiones</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="/control-documental/comparar/<?= $documento['id_documento'] ?>" method="get">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Versión 1</label>
                                <select name="v1" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    <?php foreach ($versiones as $v): ?>
                                        <option value="<?= $v['id_version'] ?>">v<?= esc($v['version']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Versión 2</label>
                                <select name="v2" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    <?php foreach ($versiones as $v): ?>
                                        <option value="<?= $v['id_version'] ?>">v<?= esc($v['version']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-arrow-left-right me-1"></i>Comparar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Marcar Obsoleto -->
    <div class="modal fade" id="modalObsoleto" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Marcar como Obsoleto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="/control-documental/marcar-obsoleto/<?= $documento['id_documento'] ?>" method="post">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Esta acción marcará el documento como obsoleto. No podrá ser editado ni firmado.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motivo</label>
                            <textarea name="motivo" class="form-control" rows="3" required
                                      placeholder="Describa el motivo por el cual este documento queda obsoleto..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle me-1"></i>Confirmar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarRestaurar(idVersion, version) {
            if (confirm(`¿Está seguro de restaurar a la versión ${version}?\n\nEsto creará una nueva versión con el contenido anterior.`)) {
                window.location.href = `/control-documental/restaurar/${idVersion}`;
            }
        }

        function exportarHistorial() {
            window.print();
        }
    </script>
</body>
</html>
