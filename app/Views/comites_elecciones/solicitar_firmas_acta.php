<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<?php
$tipoComiteNombre = [
    'COPASST' => 'COPASST',
    'COCOLAB' => 'Comite de Convivencia Laboral',
    'BRIGADA' => 'Brigada de Emergencias',
    'VIGIA' => 'Vigia SST'
][$proceso['tipo_comite']] ?? $proceso['tipo_comite'];
?>

<style>
.header-firmas {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
    border-radius: 12px;
    padding: 25px;
    color: white;
    margin-bottom: 25px;
}

.card-grupo {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.card-grupo .card-header {
    background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    padding: 12px 20px;
    border-radius: 12px 12px 0 0 !important;
}

.firmante-item {
    padding: 15px 20px;
    border-bottom: 1px solid #f1f3f5;
    transition: background-color 0.2s;
}

.firmante-item:last-child {
    border-bottom: none;
}

.firmante-item:hover {
    background-color: #f8f9fa;
}

.firmante-item.ya-solicitado {
    background-color: #e8f5e9;
}

.firmante-item.firmado {
    background-color: #c8e6c9;
}

.badge-estado {
    font-size: 0.75rem;
    padding: 4px 8px;
}

.btn-seleccionar-todos {
    font-size: 0.85rem;
}

.stats-box {
    background: white;
    border-radius: 10px;
    padding: 15px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
}

.email-warning {
    color: #dc3545;
    font-size: 0.8rem;
}

/* Panel de desarrollo */
.dev-panel {
    background: #fff3cd;
    border: 2px dashed #ffc107;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="header-firmas">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="fas fa-signature me-2"></i>
                    Solicitar Firmas Electronicas
                </h4>
                <p class="mb-0 opacity-75">
                    Acta de Constitucion <?= esc($tipoComiteNombre) ?> - <?= esc($proceso['anio']) ?>
                </p>
                <small class="opacity-50"><?= esc($cliente['nombre_cliente']) ?></small>
            </div>
            <div class="col-md-4 text-end">
                <a href="<?= base_url("comites-elecciones/proceso/{$proceso['id_proceso']}/acta") ?>"
                   class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Volver al Acta
                </a>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Panel de desarrollo (solo en development) -->
    <?php if (ENVIRONMENT === 'development' && !empty($solicitudesExistentes)): ?>
    <div class="dev-panel">
        <h6 class="mb-2"><i class="fas fa-flask me-2"></i>Panel de Desarrollo - Enlaces Directos para Pruebas</h6>
        <p class="small text-muted mb-2">Estos enlaces permiten probar las firmas sin enviar correos reales:</p>
        <div class="row">
            <?php foreach ($solicitudesExistentes as $sol): ?>
                <?php if (in_array($sol['estado'], ['pendiente', 'esperando'])): ?>
                <div class="col-md-6 col-lg-4 mb-2">
                    <a href="<?= base_url('firma/firmar/' . $sol['token']) ?>"
                       target="_blank"
                       class="btn btn-sm btn-outline-warning w-100 text-start">
                        <i class="fas fa-external-link-alt me-1"></i>
                        <?= esc($sol['firmante_nombre']) ?>
                        <small class="d-block text-muted"><?= esc($sol['firmante_tipo']) ?></small>
                    </a>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Estadisticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-box">
                <div class="stats-number text-primary"><?= $totalFirmantes ?></div>
                <small class="text-muted">Total Firmantes</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-box">
                <div class="stats-number text-warning"><?= $pendientesCount ?></div>
                <small class="text-muted">Pendientes</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-box">
                <div class="stats-number text-success"><?= $firmadosCount ?></div>
                <small class="text-muted">Firmados</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-box">
                <div class="stats-number text-info">
                    <?= $totalFirmantes > 0 ? round(($firmadosCount / $totalFirmantes) * 100) : 0 ?>%
                </div>
                <small class="text-muted">Completado</small>
            </div>
        </div>
    </div>

    <!-- Formulario de seleccion -->
    <form action="<?= base_url('comites-elecciones/proceso/crear-solicitudes-acta') ?>" method="POST" id="formFirmas">
        <?= csrf_field() ?>
        <input type="hidden" name="id_proceso" value="<?= $proceso['id_proceso'] ?>">

        <!-- Botones de accion superiores -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <button type="button" class="btn btn-outline-secondary btn-sm btn-seleccionar-todos" onclick="seleccionarTodos(true)">
                    <i class="fas fa-check-double me-1"></i> Seleccionar Todos
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm ms-2" onclick="seleccionarTodos(false)">
                    <i class="fas fa-times me-1"></i> Deseleccionar Todos
                </button>
            </div>
            <div>
                <span class="text-muted me-3">
                    <span id="contadorSeleccionados">0</span> seleccionados
                </span>
                <button type="submit" class="btn btn-primary" id="btnEnviar" disabled>
                    <i class="fas fa-paper-plane me-1"></i> Enviar Solicitudes
                </button>
            </div>
        </div>

        <!-- Grupos de firmantes -->
        <?php foreach ($firmantesAgrupados as $grupo => $firmantes): ?>
        <div class="card card-grupo">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <?php
                    $iconoGrupo = match(true) {
                        str_contains($grupo, 'Jurados') => 'fa-gavel',
                        str_contains($grupo, 'Aprobacion') => 'fa-building',
                        str_contains($grupo, 'Empleador') => 'fa-user-tie',
                        str_contains($grupo, 'Trabajadores') => 'fa-users',
                        default => 'fa-user'
                    };
                    ?>
                    <i class="fas <?= $iconoGrupo ?> me-2 text-primary"></i>
                    <?= esc($grupo) ?>
                </span>
                <span class="badge bg-secondary"><?= count($firmantes) ?></span>
            </div>
            <div class="card-body p-0">
                <?php foreach ($firmantes as $firmante): ?>
                <?php
                    $firmanteCambio = $firmante['firmante_cambio'] ?? false;
                    $yasolicitado = $firmante['ya_solicitado'];
                    $firmado = $firmante['estado_firma'] === 'firmado';
                    $sinEmail = empty($firmante['email']);
                    $idCandidato = $firmante['id_candidato'] ?? null;
                    $claseItem = $firmanteCambio ? '' : ($firmado ? 'firmado' : ($yasolicitado ? 'ya-solicitado' : ''));
                ?>
                <div class="firmante-item <?= $claseItem ?>">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <?php if ($firmanteCambio): ?>
                                <div class="form-check">
                                    <input class="form-check-input checkbox-firmante"
                                           type="checkbox"
                                           name="firmantes[]"
                                           value="<?= esc($firmante['tipo']) ?>"
                                           id="firmante_<?= esc($firmante['tipo']) ?>"
                                           onchange="actualizarContador()">
                                </div>
                            <?php elseif ($firmado): ?>
                                <span class="text-success">
                                    <i class="fas fa-check-circle fa-lg"></i>
                                </span>
                            <?php elseif ($yasolicitado): ?>
                                <span class="text-warning">
                                    <i class="fas fa-clock fa-lg"></i>
                                </span>
                            <?php elseif ($sinEmail): ?>
                                <span class="text-danger">
                                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                                </span>
                            <?php else: ?>
                                <div class="form-check">
                                    <input class="form-check-input checkbox-firmante"
                                           type="checkbox"
                                           name="firmantes[]"
                                           value="<?= esc($firmante['tipo']) ?>"
                                           id="firmante_<?= esc($firmante['tipo']) ?>"
                                           onchange="actualizarContador()">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col">
                            <div class="fw-semibold"><?= esc($firmante['nombre']) ?></div>
                            <small class="text-muted">
                                <?= esc($firmante['cargo']) ?>
                                <?php if (!empty($firmante['cedula'])): ?>
                                    - CC: <?= esc($firmante['cedula']) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="col-auto text-end">
                            <?php if ($firmanteCambio): ?>
                                <span class="badge bg-warning text-dark badge-estado">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Requiere nueva firma
                                </span>
                                <br><small class="text-muted">Firma anterior: <?= esc($firmante['firmante_anterior'] ?? '') ?></small>
                            <?php elseif ($firmado): ?>
                                <span class="badge bg-success badge-estado">
                                    <i class="fas fa-check me-1"></i> Firmado
                                </span>
                            <?php elseif ($yasolicitado): ?>
                                <span class="badge bg-warning text-dark badge-estado">
                                    <i class="fas fa-clock me-1"></i> Pendiente
                                </span>
                            <?php elseif ($sinEmail && $idCandidato): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="abrirModalEmail(<?= (int)$idCandidato ?>, '<?= esc($firmante['nombre'], 'js') ?>')">
                                    <i class="fas fa-envelope me-1"></i> Agregar email
                                </button>
                            <?php elseif ($sinEmail): ?>
                                <span class="email-warning">
                                    <i class="fas fa-envelope-open me-1"></i> Sin email
                                </span>
                            <?php else: ?>
                                <small class="text-muted">
                                    <i class="fas fa-envelope me-1"></i>
                                    <?= esc($firmante['email']) ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Botones inferiores -->
        <div class="d-flex justify-content-between mt-4">
            <a href="<?= base_url("comites-elecciones/proceso/{$proceso['id_proceso']}/acta") ?>"
               class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
            <div>
                <?php if (!empty($solicitudesExistentes)): ?>
                <a href="<?= base_url("comites-elecciones/proceso/{$proceso['id_proceso']}/firmas/estado") ?>"
                   class="btn btn-info me-2">
                    <i class="fas fa-tasks me-1"></i> Ver Estado de Firmas
                </a>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary" id="btnEnviar2" disabled>
                    <i class="fas fa-paper-plane me-1"></i> Enviar Solicitudes
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Modal agregar email -->
<div class="modal fade" id="modalAgregarEmail" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"><i class="fas fa-envelope me-2"></i>Agregar email</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3" id="modalEmailNombre"></p>
                <label class="form-label fw-bold">Correo electronico <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="inputEmailCandidato" placeholder="nombre@empresa.com">
                <div class="invalid-feedback" id="emailError">Ingrese un email valido</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnGuardarEmail" onclick="guardarEmail()">
                    <i class="fas fa-save me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let emailCandidatoId = null;
const modalEmail = new bootstrap.Modal(document.getElementById('modalAgregarEmail'));

function abrirModalEmail(idCandidato, nombre) {
    emailCandidatoId = idCandidato;
    document.getElementById('modalEmailNombre').textContent = nombre;
    document.getElementById('inputEmailCandidato').value = '';
    document.getElementById('inputEmailCandidato').classList.remove('is-invalid');
    modalEmail.show();
}

function guardarEmail() {
    const email = document.getElementById('inputEmailCandidato').value.trim();
    const input = document.getElementById('inputEmailCandidato');

    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        input.classList.add('is-invalid');
        return;
    }
    input.classList.remove('is-invalid');

    const btn = document.getElementById('btnGuardarEmail');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';

    fetch(`<?= base_url('comites-elecciones/candidato/') ?>${emailCandidatoId}/email`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
        },
        body: JSON.stringify({ email })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            modalEmail.hide();
            location.reload();
        } else {
            input.classList.add('is-invalid');
            document.getElementById('emailError').textContent = data.error || 'Error al guardar';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i>Guardar';
        }
    })
    .catch(() => {
        input.classList.add('is-invalid');
        document.getElementById('emailError').textContent = 'Error de conexion';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i>Guardar';
    });
}

function seleccionarTodos(seleccionar) {
    document.querySelectorAll('.checkbox-firmante').forEach(cb => {
        cb.checked = seleccionar;
    });
    actualizarContador();
}

function actualizarContador() {
    const seleccionados = document.querySelectorAll('.checkbox-firmante:checked').length;
    document.getElementById('contadorSeleccionados').textContent = seleccionados;

    const btnEnviar = document.getElementById('btnEnviar');
    const btnEnviar2 = document.getElementById('btnEnviar2');

    if (seleccionados > 0) {
        btnEnviar.disabled = false;
        btnEnviar2.disabled = false;
        btnEnviar.innerHTML = `<i class="fas fa-paper-plane me-1"></i> Enviar ${seleccionados} Solicitud${seleccionados > 1 ? 'es' : ''}`;
        btnEnviar2.innerHTML = `<i class="fas fa-paper-plane me-1"></i> Enviar ${seleccionados} Solicitud${seleccionados > 1 ? 'es' : ''}`;
    } else {
        btnEnviar.disabled = true;
        btnEnviar2.disabled = true;
        btnEnviar.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Enviar Solicitudes';
        btnEnviar2.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Enviar Solicitudes';
    }
}

// Inicializar
document.addEventListener('DOMContentLoaded', actualizarContador);
</script>

<?= $this->endSection() ?>
