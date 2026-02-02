<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?= base_url('actas/' . $cliente['id_cliente']) ?>">Comites</a></li>
                    <li class="breadcrumb-item active"><?= esc($comite['codigo']) ?></li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1"><?= esc($comite['tipo_nombre']) ?></h1>
                    <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?></p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $comite['id_comite'] . '/compromisos') ?>" class="btn btn-warning">
                        <i class="bi bi-list-task me-1"></i>Compromisos
                    </a>
                    <a href="<?= base_url('actas/comite/' . $comite['id_comite'] . '/preparar-reunion') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Nueva Acta
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información del comité -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informacion del Comite</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="text-muted">Codigo:</td>
                            <td><strong><?= esc($comite['codigo']) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Conformacion:</td>
                            <td><?= date('d/m/Y', strtotime($comite['fecha_conformacion'])) ?></td>
                        </tr>
                        <?php if (!empty($comite['fecha_vencimiento'])): ?>
                        <tr>
                            <td class="text-muted">Vencimiento:</td>
                            <td>
                                <?php
                                $diasRestantes = (strtotime($comite['fecha_vencimiento']) - time()) / 86400;
                                $claseVencimiento = $diasRestantes < 30 ? 'text-danger' : ($diasRestantes < 90 ? 'text-warning' : '');
                                ?>
                                <span class="<?= $claseVencimiento ?>">
                                    <?= date('d/m/Y', strtotime($comite['fecha_vencimiento'])) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="text-muted">Periodicidad:</td>
                            <td><?= $comite['periodicidad_efectiva'] ?? 30 ?> dias</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Estado:</td>
                            <td>
                                <?php if ($comite['estado'] === 'activo'): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= ucfirst($comite['estado']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <!-- Estadísticas -->
                    <hr>
                    <h6>Estadisticas <?= $anioActual ?></h6>
                    <div class="row text-center">
                        <div class="col-6 mb-2">
                            <div class="h4 mb-0"><?= $estadisticas['total'] ?></div>
                            <small class="text-muted">Actas</small>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="h4 mb-0 text-success"><?= $estadisticas['firmadas'] ?></div>
                            <small class="text-muted">Firmadas</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0 text-warning"><?= $estadisticas['pendientes_firma'] ?></div>
                            <small class="text-muted">Pend. Firma</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0"><?= $estadisticas['cumplimiento'] ?>%</div>
                            <small class="text-muted">Cumplimiento</small>
                        </div>
                    </div>

                    <!-- Paridad -->
                    <?php if (in_array($comite['codigo'], ['COPASST', 'COCOLAB'])): ?>
                    <hr>
                    <h6>Paridad</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Empleador: <?= $paridad['empleador'] ?></span>
                        <span>Trabajadores: <?= $paridad['trabajador'] ?></span>
                    </div>
                    <?php if ($paridad['hay_paridad']): ?>
                        <span class="badge bg-success w-100"><i class="bi bi-check-circle me-1"></i>Paridad OK</span>
                    <?php else: ?>
                        <span class="badge bg-danger w-100"><i class="bi bi-exclamation-circle me-1"></i>Sin paridad</span>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Miembros -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Miembros del Comite</h5>
                    <a href="<?= base_url('actas/comite/' . $comite['id_comite'] . '/nuevo-miembro') ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-lg me-1"></i>Agregar
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($miembros)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-people display-4 text-muted"></i>
                        <p class="mt-2 text-muted">No hay miembros registrados</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Cargo</th>
                                    <th>Representacion</th>
                                    <th>Tipo</th>
                                    <th>Rol</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($miembros as $miembro): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($miembro['nombre_completo']) ?></strong>
                                        <?php if (!empty($miembro['email'])): ?>
                                        <br><small class="text-muted"><?= esc($miembro['email']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($miembro['cargo'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($miembro['representacion'])): ?>
                                        <span class="badge bg-<?= $miembro['representacion'] === 'empleador' ? 'primary' : 'success' ?>">
                                            <?= ucfirst($miembro['representacion']) ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ($miembro['tipo_miembro'] ?? '') === 'principal' ? 'dark' : 'secondary' ?>">
                                            <?= ucfirst($miembro['tipo_miembro'] ?? 'principal') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php $rol = $miembro['rol_comite'] ?? 'miembro'; ?>
                                        <?php if ($rol !== 'miembro' && !empty($rol)): ?>
                                            <span class="badge bg-warning text-dark"><?= ucfirst($rol) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Miembro</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('actas/comite/' . $comite['id_comite'] . '/editar-miembro/' . $miembro['id_miembro']) ?>"
                                               class="btn btn-outline-primary" title="Editar miembro">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if (!empty($miembro['email'])): ?>
                                            <button type="button" class="btn btn-outline-info" title="Enviar credenciales de acceso"
                                                    onclick="reenviarAcceso(<?= $miembro['id_miembro'] ?>, '<?= esc($miembro['nombre_completo']) ?>', '<?= esc($miembro['email']) ?>')">
                                                <i class="bi bi-key"></i>
                                            </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-outline-danger" title="Retirar miembro"
                                                    onclick="retirarMiembro(<?= $miembro['id_miembro'] ?>, '<?= esc($miembro['nombre_completo']) ?>')">
                                                <i class="bi bi-person-dash"></i>
                                            </button>
                                        </div>
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
    </div>

    <!-- Actas del año -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Actas <?= $anioActual ?></h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($actas)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-text display-4 text-muted"></i>
                        <p class="mt-2 text-muted">No hay actas registradas este ano</p>
                        <a href="<?= base_url('actas/comite/' . $comite['id_comite'] . '/nueva-acta') ?>" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>Crear Primera Acta
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Acta</th>
                                    <th>Tipo</th>
                                    <th>Fecha Reunion</th>
                                    <th>Lugar</th>
                                    <th>Quorum</th>
                                    <th>Estado</th>
                                    <th>Firmas</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($actas as $acta): ?>
                                <tr>
                                    <td><strong><?= esc($acta['numero_acta']) ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?= $acta['tipo_acta'] === 'ordinaria' ? 'secondary' : 'info' ?>">
                                            <?= ucfirst($acta['tipo_acta']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($acta['fecha_reunion'])) ?></td>
                                    <td><?= esc($acta['lugar']) ?></td>
                                    <td>
                                        <?php if ($acta['hay_quorum']): ?>
                                            <span class="badge bg-success"><?= $acta['quorum_presente'] ?>/<?= $acta['quorum_requerido'] ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><?= $acta['quorum_presente'] ?>/<?= $acta['quorum_requerido'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeEstado = match($acta['estado']) {
                                            'firmada' => 'bg-success',
                                            'pendiente_firma' => 'bg-warning text-dark',
                                            'en_edicion' => 'bg-info',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeEstado ?>"><?= ucfirst(str_replace('_', ' ', $acta['estado'])) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($acta['estado'] === 'pendiente_firma' || $acta['estado'] === 'firmada'): ?>
                                            <?= $acta['firmantes_completados'] ?>/<?= $acta['total_firmantes'] ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?php if (in_array($acta['estado'], ['borrador', 'en_edicion'])): ?>
                                                <a href="<?= base_url('actas/editar/' . $acta['id_acta']) ?>" class="btn btn-outline-primary" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= base_url('actas/ver/' . $acta['id_acta']) ?>" class="btn btn-outline-secondary" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($acta['estado'] === 'pendiente_firma'): ?>
                                                <a href="<?= base_url('actas/firmas/' . $acta['id_acta']) ?>" class="btn btn-outline-warning" title="Estado Firmas">
                                                    <i class="bi bi-pen"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($acta['estado'] === 'firmada'): ?>
                                                <a href="<?= base_url('actas/pdf/' . $acta['id_acta']) ?>" class="btn btn-outline-danger" title="Descargar PDF">
                                                    <i class="bi bi-file-pdf"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
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
    </div>

    <!-- Compromisos pendientes -->
    <?php if (!empty($compromisosPendientes)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Compromisos Pendientes de Actas Anteriores</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Acta</th>
                                    <th>Descripcion</th>
                                    <th>Responsable</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($compromisosPendientes as $comp): ?>
                                <tr class="<?= $comp['estado'] === 'vencido' ? 'table-danger' : '' ?>">
                                    <td><?= esc($comp['numero_acta']) ?></td>
                                    <td><?= esc(substr($comp['descripcion'], 0, 60)) ?>...</td>
                                    <td><?= esc($comp['responsable_nombre']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($comp['fecha_vencimiento'])) ?></td>
                                    <td>
                                        <?php
                                        $badgeComp = match($comp['estado']) {
                                            'vencido' => 'bg-danger',
                                            'en_proceso' => 'bg-info',
                                            default => 'bg-warning text-dark'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeComp ?>"><?= ucfirst($comp['estado']) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Retirar Miembro -->
<div class="modal fade" id="modalRetirarMiembro" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Retirar Miembro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Esta por retirar a <strong id="nombreMiembroRetirar"></strong> del comite.</p>
                <p class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Esta accion implica una reconformacion del comite.</p>
                <div class="mb-3">
                    <label class="form-label">Motivo del retiro:</label>
                    <textarea class="form-control" id="motivoRetiro" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarRetiro()">Retirar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.forEach(function(el) {
        new bootstrap.Tooltip(el);
    });
});

let idMiembroRetirar = null;

function retirarMiembro(id, nombre) {
    idMiembroRetirar = id;
    document.getElementById('nombreMiembroRetirar').textContent = nombre;
    document.getElementById('motivoRetiro').value = '';
    new bootstrap.Modal(document.getElementById('modalRetirarMiembro')).show();
}

function confirmarRetiro() {
    const motivo = document.getElementById('motivoRetiro').value.trim();
    if (!motivo) {
        alert('Debe indicar el motivo del retiro');
        return;
    }

    fetch(`<?= base_url('actas/miembro') ?>/${idMiembroRetirar}/retirar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `motivo=${encodeURIComponent(motivo)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al retirar miembro');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexion');
    });
}

function reenviarAcceso(idMiembro, nombre, email) {
    if (!confirm(`¿Enviar credenciales de acceso a ${nombre} (${email})?\n\nSe generara una nueva contraseña y se enviara por email.`)) {
        return;
    }

    // Mostrar loading
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btn.disabled = true;

    fetch(`<?= base_url('actas/miembro') ?>/${idMiembro}/reenviar-acceso`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;

        if (data.success) {
            alert(data.message);
        } else {
            alert(data.message || 'Error al enviar enlace');
        }
    })
    .catch(error => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
        console.error('Error:', error);
        alert('Error de conexion');
    });
}
</script>

<?= $this->endSection() ?>
