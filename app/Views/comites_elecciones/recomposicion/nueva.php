<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<?php
$tipoComiteNombre = [
    'COPASST' => 'COPASST',
    'COCOLAB' => 'Comité de Convivencia Laboral',
    'BRIGADA' => 'Brigada de Emergencias',
    'VIGIA' => 'Vigía SST'
][$proceso['tipo_comite']] ?? $proceso['tipo_comite'];
?>

<style>
.card-recomposicion {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}
.card-recomposicion .card-header {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border-radius: 12px 12px 0 0;
    padding: 20px 25px;
}
.miembro-card {
    border: 2px solid #dee2e6;
    border-radius: 10px;
    padding: 15px;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: 10px;
}
.miembro-card:hover {
    border-color: #dc3545;
    background: #fff5f5;
}
.miembro-card.selected {
    border-color: #dc3545;
    background: #ffeaec;
}
.miembro-card .badge-representacion {
    font-size: 0.7rem;
}
.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #dc3545;
}
.form-section {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}
.entrante-card {
    border: 2px dashed #28a745;
    border-radius: 10px;
    padding: 15px;
    background: #f0fff4;
    cursor: pointer;
    transition: all 0.2s;
}
.entrante-card:hover {
    background: #d4edda;
}
.entrante-card.selected {
    border-style: solid;
    background: #d4edda;
}
.posicion-votacion {
    background: #0d6efd;
    color: white;
    font-size: 0.75rem;
    padding: 2px 8px;
    border-radius: 20px;
}
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-user-minus text-danger me-2"></i>
                Nueva Recomposicion del <?= esc($tipoComiteNombre) ?>
            </h4>
            <p class="text-muted mb-0">
                <?= esc($cliente['nombre_cliente']) ?> - Periodo <?= esc($proceso['anio']) ?>
            </p>
        </div>
        <a href="<?= base_url("comites-elecciones/proceso/{$proceso['id_proceso']}/recomposiciones") ?>"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <form action="<?= base_url('comites-elecciones/proceso/guardar-recomposicion') ?>" method="POST" id="formRecomposicion">
        <?= csrf_field() ?>
        <input type="hidden" name="id_proceso" value="<?= $proceso['id_proceso'] ?>">
        <input type="hidden" name="id_candidato_saliente" id="id_candidato_saliente" value="">

        <div class="row">
            <!-- Columna izquierda: Miembro saliente -->
            <div class="col-lg-6">
                <div class="card card-recomposicion">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-minus me-2"></i>
                            1. Seleccionar Miembro que Sale
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($miembrosActuales)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            No hay miembros activos en el comite.
                        </div>
                        <?php else: ?>

                        <!-- Representantes de Trabajadores -->
                        <?php
                        $trabajadores = array_filter($miembrosActuales, fn($m) => $m['representacion'] === 'trabajador');
                        $empleadores = array_filter($miembrosActuales, fn($m) => $m['representacion'] === 'empleador');
                        ?>

                        <?php if (!empty($trabajadores)): ?>
                        <h6 class="section-title">
                            <i class="fas fa-users text-primary me-2"></i>
                            Representantes de los Trabajadores
                        </h6>
                        <?php foreach ($trabajadores as $m): ?>
                        <div class="miembro-card"
                             data-id="<?= $m['id_candidato'] ?>"
                             data-representacion="trabajador"
                             onclick="seleccionarSaliente(this)">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= esc($m['nombres'] . ' ' . $m['apellidos']) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        C.C. <?= esc($m['documento_identidad']) ?> - <?= esc($m['cargo']) ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary badge-representacion">
                                        <?= ucfirst($m['tipo_plaza'] ?? 'Principal') ?>
                                    </span>
                                    <?php if (!empty($m['votos_obtenidos'])): ?>
                                    <br><small class="text-muted"><?= $m['votos_obtenidos'] ?> votos</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($empleadores)): ?>
                        <h6 class="section-title mt-4">
                            <i class="fas fa-building text-success me-2"></i>
                            Representantes del Empleador
                        </h6>
                        <?php foreach ($empleadores as $m): ?>
                        <div class="miembro-card"
                             data-id="<?= $m['id_candidato'] ?>"
                             data-representacion="empleador"
                             onclick="seleccionarSaliente(this)">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= esc($m['nombres'] . ' ' . $m['apellidos']) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        C.C. <?= esc($m['documento_identidad']) ?> - <?= esc($m['cargo']) ?>
                                    </small>
                                </div>
                                <span class="badge bg-success badge-representacion">
                                    <?= ucfirst($m['tipo_plaza'] ?? 'Principal') ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <?php endif; ?>
                    </div>
                </div>

                <!-- Motivo de salida -->
                <div class="card card-recomposicion mt-3" id="seccionMotivo" style="display: none;">
                    <div class="card-header" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>
                            2. Motivo de la Salida
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Causal de retiro <span class="text-danger">*</span></label>
                            <select name="motivo_salida" id="motivo_salida" class="form-select" required>
                                <option value="">-- Seleccionar motivo --</option>
                                <?php foreach ($motivosSalida as $key => $label): ?>
                                <option value="<?= $key ?>"><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Detalle adicional</label>
                            <textarea name="motivo_detalle" class="form-control" rows="3"
                                      placeholder="Proporcione detalles adicionales si aplica..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Fecha efectiva de salida <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_salida" class="form-control" required
                                       value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Fecha de recomposicion <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_recomposicion" class="form-control" required
                                       value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Miembro entrante -->
            <div class="col-lg-6">
                <div class="card card-recomposicion" id="seccionEntrante" style="display: none;">
                    <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #218838 100%);">
                        <h5 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>
                            3. Seleccionar Reemplazo
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Tipo de ingreso -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo de ingreso <span class="text-danger">*</span></label>
                            <select name="tipo_ingreso" id="tipo_ingreso" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <small class="text-muted" id="tipoIngresoHelp"></small>
                        </div>

                        <!-- Seccion para trabajadores: seleccionar del listado de votacion -->
                        <div id="seccionCandidatosVotacion" style="display: none;">
                            <h6 class="section-title">
                                <i class="fas fa-vote-yea text-primary me-2"></i>
                                Candidatos Disponibles (por votos)
                            </h6>
                            <input type="hidden" name="id_candidato_entrante" id="id_candidato_entrante" value="">

                            <?php if (empty($candidatosDisponibles)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                No hay candidatos disponibles. Debera realizar una asamblea extraordinaria o designar nuevo miembro.
                            </div>
                            <?php else: ?>
                            <?php foreach ($candidatosDisponibles as $idx => $c): ?>
                            <div class="entrante-card mb-2"
                                 data-id="<?= $c['id_candidato'] ?>"
                                 onclick="seleccionarEntrante(this)">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="posicion-votacion me-2">#<?= $idx + 1 ?></span>
                                        <strong><?= esc($c['nombres'] . ' ' . $c['apellidos']) ?></strong>
                                        <br>
                                        <small class="text-muted ms-4">
                                            C.C. <?= esc($c['documento_identidad']) ?> - <?= esc($c['cargo']) ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-info">
                                            <?= $c['votos_obtenidos'] ?? 0 ?> votos
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Seccion para empleadores o asamblea: datos manuales -->
                        <div id="seccionNuevoMiembro" style="display: none;">
                            <h6 class="section-title">
                                <i class="fas fa-user-edit text-success me-2"></i>
                                Datos del Nuevo Miembro
                            </h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" name="entrante_nombres" class="form-control campo-nuevo">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                    <input type="text" name="entrante_apellidos" class="form-control campo-nuevo">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Documento <span class="text-danger">*</span></label>
                                    <input type="text" name="entrante_documento" class="form-control campo-nuevo">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cargo <span class="text-danger">*</span></label>
                                    <input type="text" name="entrante_cargo" class="form-control campo-nuevo">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="entrante_email" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Telefono</label>
                                    <input type="text" name="entrante_telefono" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="card card-recomposicion mt-3" id="seccionObservaciones" style="display: none;">
                    <div class="card-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                        <h5 class="mb-0">
                            <i class="fas fa-sticky-note me-2"></i>
                            4. Observaciones y Justificacion Legal
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Observaciones adicionales</label>
                            <textarea name="observaciones" class="form-control" rows="2"
                                      placeholder="Notas u observaciones adicionales..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Justificacion legal</label>
                            <textarea name="justificacion_legal" class="form-control" rows="3"
                                      placeholder="Fundamentos legales de la recomposicion..."><?php
                            if ($proceso['tipo_comite'] === 'COCOLAB') {
                                echo "De conformidad con lo establecido en la Resolución 652 de 2012, modificada por la Resolución 1356 de 2012, el Comité de Convivencia Laboral deberá garantizar la continuidad de su funcionamiento.";
                            } else {
                                echo "De conformidad con el Decreto 1072 de 2015, Artículo 2.2.4.6.29, el Comité Paritario de Seguridad y Salud en el Trabajo (COPASST) deberá mantener su integración durante el periodo para el cual fue elegido.";
                            }
                            ?></textarea>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg" id="btnGuardar" disabled>
                                <i class="fas fa-save me-2"></i>
                                Registrar Recomposicion
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let representacionSaliente = null;

function seleccionarSaliente(card) {
    // Quitar seleccion anterior
    document.querySelectorAll('.miembro-card').forEach(c => c.classList.remove('selected'));

    // Seleccionar nuevo
    card.classList.add('selected');
    document.getElementById('id_candidato_saliente').value = card.dataset.id;
    representacionSaliente = card.dataset.representacion;

    // Mostrar seccion de motivo
    document.getElementById('seccionMotivo').style.display = 'block';
    document.getElementById('seccionEntrante').style.display = 'block';
    document.getElementById('seccionObservaciones').style.display = 'block';

    // Configurar opciones de tipo de ingreso segun representacion
    configurarTipoIngreso();

    validarFormulario();
}

function configurarTipoIngreso() {
    const select = document.getElementById('tipo_ingreso');
    const help = document.getElementById('tipoIngresoHelp');
    select.innerHTML = '<option value="">-- Seleccionar --</option>';

    if (representacionSaliente === 'trabajador') {
        <?php if (!empty($candidatosDisponibles)): ?>
        select.innerHTML += '<option value="siguiente_votacion" selected>Siguiente en votacion</option>';
        help.textContent = 'Se asignara el siguiente candidato con mayor votacion que no fue elegido.';
        document.getElementById('seccionCandidatosVotacion').style.display = 'block';
        document.getElementById('seccionNuevoMiembro').style.display = 'none';
        <?php else: ?>
        select.innerHTML += '<option value="asamblea_extraordinaria">Asamblea extraordinaria</option>';
        help.textContent = 'No hay candidatos disponibles. Se debe realizar asamblea extraordinaria.';
        document.getElementById('seccionCandidatosVotacion').style.display = 'none';
        document.getElementById('seccionNuevoMiembro').style.display = 'block';
        <?php endif; ?>
    } else {
        select.innerHTML += '<option value="designacion_empleador" selected>Designacion del empleador</option>';
        help.textContent = 'El empleador designara al nuevo representante.';
        document.getElementById('seccionCandidatosVotacion').style.display = 'none';
        document.getElementById('seccionNuevoMiembro').style.display = 'block';
    }
}

function seleccionarEntrante(card) {
    document.querySelectorAll('.entrante-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    document.getElementById('id_candidato_entrante').value = card.dataset.id;
    validarFormulario();
}

document.getElementById('tipo_ingreso').addEventListener('change', function() {
    if (this.value === 'siguiente_votacion') {
        document.getElementById('seccionCandidatosVotacion').style.display = 'block';
        document.getElementById('seccionNuevoMiembro').style.display = 'none';
    } else if (this.value === 'designacion_empleador' || this.value === 'asamblea_extraordinaria') {
        document.getElementById('seccionCandidatosVotacion').style.display = 'none';
        document.getElementById('seccionNuevoMiembro').style.display = 'block';
    }
    validarFormulario();
});

function validarFormulario() {
    const saliente = document.getElementById('id_candidato_saliente').value;
    const motivo = document.getElementById('motivo_salida').value;
    const tipoIngreso = document.getElementById('tipo_ingreso').value;

    let entranteValido = false;

    if (tipoIngreso === 'siguiente_votacion') {
        entranteValido = document.getElementById('id_candidato_entrante').value !== '';
    } else if (tipoIngreso === 'designacion_empleador' || tipoIngreso === 'asamblea_extraordinaria') {
        const campos = document.querySelectorAll('.campo-nuevo');
        entranteValido = Array.from(campos).every(c => c.value.trim() !== '');
    }

    document.getElementById('btnGuardar').disabled = !(saliente && motivo && tipoIngreso && entranteValido);
}

// Validar en tiempo real
document.querySelectorAll('input, select, textarea').forEach(el => {
    el.addEventListener('change', validarFormulario);
    el.addEventListener('input', validarFormulario);
});
</script>

<?= $this->endSection() ?>
