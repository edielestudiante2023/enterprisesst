<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?= base_url('actas/' . $cliente['id_cliente']) ?>">Comites</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $comite['id_comite']) ?>"><?= esc($comite['codigo']) ?></a></li>
                    <li class="breadcrumb-item active">Compromisos</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">Compromisos - <?= esc($comite['tipo_nombre']) ?></h1>
                    <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select class="form-select" name="estado">
                        <option value="">Todos</option>
                        <option value="pendiente" <?= ($filtros['estado'] ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="en_progreso" <?= ($filtros['estado'] ?? '') === 'en_progreso' ? 'selected' : '' ?>>En progreso</option>
                        <option value="completado" <?= ($filtros['estado'] ?? '') === 'completado' ? 'selected' : '' ?>>Completado</option>
                        <option value="vencido" <?= ($filtros['estado'] ?? '') === 'vencido' ? 'selected' : '' ?>>Vencido</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Responsable</label>
                    <select class="form-select" name="responsable">
                        <option value="">Todos</option>
                        <?php foreach ($miembros as $miembro): ?>
                        <option value="<?= $miembro['id_miembro'] ?>" <?= ($filtros['responsable'] ?? '') == $miembro['id_miembro'] ? 'selected' : '' ?>>
                            <?= esc($miembro['nombre_completo']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Desde</label>
                    <input type="date" class="form-control" name="desde" value="<?= $filtros['desde'] ?? '' ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Hasta</label>
                    <input type="date" class="form-control" name="hasta" value="<?= $filtros['hasta'] ?? '' ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $comite['id_comite'] . '/compromisos') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pendientes</h6>
                            <h3 class="mb-0"><?= $stats['pendientes'] ?? 0 ?></h3>
                        </div>
                        <i class="bi bi-clock text-secondary fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">En Progreso</h6>
                            <h3 class="mb-0"><?= $stats['en_progreso'] ?? 0 ?></h3>
                        </div>
                        <i class="bi bi-arrow-repeat text-info fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Completados</h6>
                            <h3 class="mb-0"><?= $stats['completados'] ?? 0 ?></h3>
                        </div>
                        <i class="bi bi-check-circle text-success fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-4 border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Vencidos</h6>
                            <h3 class="mb-0"><?= $stats['vencidos'] ?? 0 ?></h3>
                        </div>
                        <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de compromisos -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Lista de Compromisos</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($compromisos)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted fs-1"></i>
                <p class="text-muted mt-2">No hay compromisos registrados</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Acta</th>
                            <th>Descripcion</th>
                            <th>Responsable</th>
                            <th>Fecha Limite</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compromisos as $comp): ?>
                        <tr class="<?= $comp['estado'] === 'vencido' ? 'table-danger' : '' ?>">
                            <td>
                                <a href="<?= base_url('actas/comite/' . $comite['id_comite'] . '/acta/' . $comp['id_acta']) ?>">
                                    <?= esc($comp['numero_acta']) ?>
                                </a>
                            </td>
                            <td>
                                <?= esc($comp['descripcion']) ?>
                                <?php if (!empty($comp['observaciones'])): ?>
                                <br><small class="text-muted"><i class="bi bi-chat-dots me-1"></i><?= esc(substr($comp['observaciones'], 0, 50)) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= esc($comp['responsable_nombre'] ?? 'Sin asignar') ?>
                            </td>
                            <td>
                                <?php
                                $fechaVenc = strtotime($comp['fecha_vencimiento'] ?? 'now');
                                $hoy = strtotime('today');
                                $diasRestantes = floor(($fechaVenc - $hoy) / 86400);
                                ?>
                                <?= date('d/m/Y', $fechaVenc) ?>
                                <?php if ($comp['estado'] !== 'cumplido' && $comp['estado'] !== 'completado'): ?>
                                    <?php if ($diasRestantes < 0): ?>
                                        <br><small class="text-danger"><i class="bi bi-exclamation-circle"></i> Vencido hace <?= abs($diasRestantes) ?> dias</small>
                                    <?php elseif ($diasRestantes <= 7): ?>
                                        <br><small class="text-warning"><i class="bi bi-clock"></i> <?= $diasRestantes ?> dias restantes</small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $estadoBadge = [
                                    'pendiente' => 'bg-secondary',
                                    'en_progreso' => 'bg-info',
                                    'completado' => 'bg-success',
                                    'vencido' => 'bg-danger'
                                ];
                                ?>
                                <span class="badge <?= $estadoBadge[$comp['estado']] ?? 'bg-secondary' ?>">
                                    <?= ucfirst(str_replace('_', ' ', $comp['estado'])) ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="editarCompromiso(<?= htmlspecialchars(json_encode($comp)) ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <?php if ($comp['estado'] !== 'completado'): ?>
                                <form action="<?= base_url('actas/compromiso/' . $comp['id_compromiso'] . '/completar') ?>" method="post" class="d-inline">
                                    <button type="submit" class="btn btn-outline-success btn-sm" title="Marcar como completado">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal editar compromiso -->
<div class="modal fade" id="modalEditarCompromiso" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="post" id="formEditarCompromiso">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Compromiso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Descripcion</label>
                        <textarea class="form-control" name="descripcion" id="comp_descripcion" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Responsable</label>
                        <select class="form-select" name="id_responsable" id="comp_responsable">
                            <option value="">Sin asignar</option>
                            <?php foreach ($miembros as $miembro): ?>
                            <option value="<?= $miembro['id_miembro'] ?>"><?= esc($miembro['nombre_completo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Vencimiento</label>
                            <input type="date" class="form-control" name="fecha_vencimiento" id="comp_fecha" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="comp_estado">
                                <option value="pendiente">Pendiente</option>
                                <option value="en_progreso">En progreso</option>
                                <option value="completado">Completado</option>
                                <option value="vencido">Vencido</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observaciones" id="comp_observaciones" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarCompromiso(comp) {
    document.getElementById('formEditarCompromiso').action = '<?= base_url('actas/compromiso/') ?>' + comp.id_compromiso + '/actualizar';
    document.getElementById('comp_descripcion').value = comp.descripcion || '';
    document.getElementById('comp_responsable').value = comp.responsable_id_miembro || '';
    document.getElementById('comp_fecha').value = comp.fecha_vencimiento || '';
    document.getElementById('comp_estado').value = comp.estado || 'pendiente';
    document.getElementById('comp_observaciones').value = comp.observaciones || '';
    new bootstrap.Modal(document.getElementById('modalEditarCompromiso')).show();
}
</script>

<?= $this->endSection() ?>
