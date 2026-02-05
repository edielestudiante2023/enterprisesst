<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Generar Etapas de Inducción - <?= esc($cliente['nombre_cliente']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('consultant/dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('documentacion/dashboard/' . $cliente['id_cliente']) ?>">Documentación</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('induccion-etapas/' . $cliente['id_cliente']) ?>">Etapas Inducción</a></li>
            <li class="breadcrumb-item active">Generar</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Panel Izquierdo: Contexto del Cliente -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-building me-2"></i>
                        Contexto del Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <h6><?= esc($cliente['nombre_cliente']) ?></h6>
                    <p class="text-muted small mb-3">NIT: <?= esc($cliente['nit'] ?? 'N/A') ?></p>

                    <hr>

                    <div class="mb-3">
                        <strong>Actividad Económica:</strong>
                        <p class="text-muted mb-0"><?= esc($contexto['actividad_economica'] ?? 'No definida') ?></p>
                    </div>

                    <div class="mb-3">
                        <strong>Nivel de Riesgo ARL:</strong>
                        <span class="badge bg-<?= ($contexto['nivel_riesgo_arl'] ?? 'I') >= 'IV' ? 'danger' : 'warning' ?>">
                            <?= esc($contexto['nivel_riesgo_arl'] ?? 'I') ?>
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong>Trabajadores:</strong>
                        <span class="badge bg-info"><?= $contexto['total_trabajadores'] ?? 0 ?></span>
                    </div>

                    <div class="mb-3">
                        <strong>Estándares Aplicables:</strong>
                        <span class="badge bg-primary"><?= $contexto['estandares_aplicables'] ?? 7 ?></span>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <strong>Órganos de Participación:</strong>
                        <ul class="list-unstyled mb-0 mt-2">
                            <?php if (!empty($contexto['tiene_copasst']) && $contexto['tiene_copasst']): ?>
                                <li><i class="bi bi-check-circle text-success me-1"></i> COPASST</li>
                            <?php endif; ?>
                            <?php if (!empty($contexto['tiene_vigia_sst']) && $contexto['tiene_vigia_sst']): ?>
                                <li><i class="bi bi-check-circle text-success me-1"></i> Vigía SST</li>
                            <?php endif; ?>
                            <?php if (!empty($contexto['tiene_comite_convivencia']) && $contexto['tiene_comite_convivencia']): ?>
                                <li><i class="bi bi-check-circle text-success me-1"></i> Comité Convivencia</li>
                            <?php endif; ?>
                            <?php if (!empty($contexto['tiene_brigada_emergencias']) && $contexto['tiene_brigada_emergencias']): ?>
                                <li><i class="bi bi-check-circle text-success me-1"></i> Brigada Emergencias</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Peligros Identificados -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Peligros Identificados
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($peligros)): ?>
                        <?php
                        $categorias = [];
                        foreach ($peligros as $p) {
                            $cat = $p['categoria'] ?? 'Otros';
                            if (!isset($categorias[$cat])) {
                                $categorias[$cat] = [];
                            }
                            $categorias[$cat][] = $p['nombre'];
                        }
                        ?>
                        <?php foreach ($categorias as $cat => $items): ?>
                            <div class="mb-3">
                                <strong class="text-capitalize"><?= esc($cat) ?>:</strong>
                                <div class="mt-1">
                                    <?php foreach ($items as $item): ?>
                                        <span class="badge bg-light text-dark border me-1 mb-1">
                                            <?= esc($item) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            No hay peligros identificados en el contexto.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panel Derecho: Generación -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-magic me-2"></i>
                        Generar Etapas de Inducción
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($tieneEtapas): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Atención:</strong> Ya existen <?= count($etapasExistentes) ?> etapas para el año <?= $anio ?>.
                            Si continúa, se eliminarán las etapas actuales y se generarán nuevas.
                        </div>
                    <?php endif; ?>

                    <h5 class="mb-3">Selecciona las etapas a incluir</h5>

                    <p class="text-muted mb-3">
                        Selecciona las etapas que deseas incluir en el programa de inducción.
                        Los temas se personalizarán según los peligros identificados y el contexto del cliente.
                    </p>

                    <form action="<?= base_url("induccion-etapas/{$cliente['id_cliente']}/generar") ?>" method="post" id="formGenerar">
                        <?= csrf_field() ?>
                        <input type="hidden" name="anio" value="<?= $anio ?>">

                        <!-- Botones de selección rápida -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnSelectAll">
                                <i class="bi bi-check-all me-1"></i>Seleccionar todas
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeselectAll">
                                <i class="bi bi-x-lg me-1"></i>Deseleccionar
                            </button>
                            <span class="ms-2 text-muted small">
                                <span id="countSelected">5</span> etapas seleccionadas
                            </span>
                        </div>

                        <div class="row g-3 mb-4">
                            <!-- Etapa 1: Introducción -->
                            <div class="col-md-4">
                                <div class="card border-0 etapa-card" data-etapa="1">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input etapa-check"
                                                   name="etapas[1][incluir]" value="1" id="etapa1" checked>
                                            <label class="form-check-label w-100 cursor-pointer" for="etapa1">
                                                <div class="text-center">
                                                    <i class="bi bi-1-circle text-primary" style="font-size: 2rem;"></i>
                                                    <h6 class="mt-2 mb-1">Introducción</h6>
                                                    <small class="text-muted">Historia, Misión, Valores</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Etapa 2: SST -->
                            <div class="col-md-4">
                                <div class="card border-0 etapa-card" data-etapa="2">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input etapa-check"
                                                   name="etapas[2][incluir]" value="1" id="etapa2" checked>
                                            <label class="form-check-label w-100 cursor-pointer" for="etapa2">
                                                <div class="text-center">
                                                    <i class="bi bi-2-circle text-danger" style="font-size: 2rem;"></i>
                                                    <h6 class="mt-2 mb-1">SST</h6>
                                                    <small class="text-muted">Política, Peligros, Controles</small>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="text-center mt-2">
                                            <span class="badge bg-danger-subtle text-danger">
                                                <i class="bi bi-star-fill me-1"></i>Obligatoria
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Etapa 3: Relaciones Laborales -->
                            <div class="col-md-4">
                                <div class="card border-0 etapa-card" data-etapa="3">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input etapa-check"
                                                   name="etapas[3][incluir]" value="1" id="etapa3" checked>
                                            <label class="form-check-label w-100 cursor-pointer" for="etapa3">
                                                <div class="text-center">
                                                    <i class="bi bi-3-circle text-info" style="font-size: 2rem;"></i>
                                                    <h6 class="mt-2 mb-1">Relaciones Laborales</h6>
                                                    <small class="text-muted">Reglamento, Horario</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Etapa 4: Recorrido -->
                            <div class="col-md-6">
                                <div class="card border-0 etapa-card" data-etapa="4">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input etapa-check"
                                                   name="etapas[4][incluir]" value="1" id="etapa4" checked>
                                            <label class="form-check-label w-100 cursor-pointer" for="etapa4">
                                                <div class="text-center">
                                                    <i class="bi bi-4-circle text-warning" style="font-size: 2rem;"></i>
                                                    <h6 class="mt-2 mb-1">Recorrido</h6>
                                                    <small class="text-muted">Instalaciones, Evacuación</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Etapa 5: Entrenamiento -->
                            <div class="col-md-6">
                                <div class="card border-0 etapa-card" data-etapa="5">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input etapa-check"
                                                   name="etapas[5][incluir]" value="1" id="etapa5" checked>
                                            <label class="form-check-label w-100 cursor-pointer" for="etapa5">
                                                <div class="text-center">
                                                    <i class="bi bi-5-circle text-success" style="font-size: 2rem;"></i>
                                                    <h6 class="mt-2 mb-1">Entrenamiento</h6>
                                                    <small class="text-muted">Cargo, Procedimientos, EPP</small>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="text-center mt-2">
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="bi bi-star-fill me-1"></i>Recomendada
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info de personalización -->
                        <div class="alert alert-light border mb-4">
                            <h6 class="alert-heading"><i class="bi bi-magic me-2"></i>Personalización automática</h6>
                            <ul class="mb-0 small">
                                <li><strong>Peligros identificados:</strong> Se agregarán temas de SST específicos según los riesgos</li>
                                <li><strong>Órganos de participación:</strong> Temas sobre COPASST/Vigía según corresponda</li>
                                <li><strong>Estructura:</strong> Se adaptará según número de sedes y turnos</li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" id="btnGenerar">
                                <i class="bi bi-magic me-2"></i>
                                <?= $tieneEtapas ? 'Regenerar Etapas' : 'Generar Etapas' ?>
                            </button>
                            <a href="<?= base_url("induccion-etapas/{$cliente['id_cliente']}") ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h6><i class="bi bi-info-circle me-2"></i>Información del Auditor</h6>
                    <p class="text-muted small mb-0">
                        <em>"¿Se evidencia el cumplimiento del programa anual de capacitación y de los procesos de
                        inducción y reinducción en seguridad y salud en el trabajo previa al inicio de sus labores
                        que cubre a todos los trabajadores independientemente de su forma de vinculación y/o contratación
                        e incluye la descripción de las actividades a realizar, información de la identificación de riesgo,
                        evaluación y valoración de riesgos y establecimiento de controles para prevención de los ATEL?"</em>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.etapa-card {
    transition: all 0.2s ease;
    cursor: pointer;
    background-color: #f8f9fa;
}
.etapa-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.etapa-card.selected {
    background-color: #e8f5e9;
    border: 2px solid #4caf50 !important;
}
.etapa-card.deselected {
    background-color: #f5f5f5;
    opacity: 0.6;
}
.cursor-pointer {
    cursor: pointer;
}
.form-check-input:checked + .form-check-label {
    font-weight: 500;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.etapa-check');
    const countSelected = document.getElementById('countSelected');
    const btnSelectAll = document.getElementById('btnSelectAll');
    const btnDeselectAll = document.getElementById('btnDeselectAll');
    const btnGenerar = document.getElementById('btnGenerar');

    function updateCount() {
        const checked = document.querySelectorAll('.etapa-check:checked').length;
        countSelected.textContent = checked;

        // Habilitar/deshabilitar botón según selección
        btnGenerar.disabled = checked === 0;

        // Actualizar estilo de tarjetas
        document.querySelectorAll('.etapa-card').forEach(card => {
            const checkbox = card.querySelector('.etapa-check');
            if (checkbox.checked) {
                card.classList.add('selected');
                card.classList.remove('deselected');
            } else {
                card.classList.remove('selected');
                card.classList.add('deselected');
            }
        });
    }

    // Click en tarjeta activa el checkbox
    document.querySelectorAll('.etapa-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const checkbox = this.querySelector('.etapa-check');
                checkbox.checked = !checkbox.checked;
                updateCount();
            }
        });
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateCount);
    });

    btnSelectAll.addEventListener('click', function() {
        checkboxes.forEach(cb => cb.checked = true);
        updateCount();
    });

    btnDeselectAll.addEventListener('click', function() {
        checkboxes.forEach(cb => cb.checked = false);
        updateCount();
    });

    // Inicializar estado visual
    updateCount();

    // Submit del formulario
    document.getElementById('formGenerar').addEventListener('submit', function(e) {
        const checkedCount = document.querySelectorAll('.etapa-check:checked').length;
        if (checkedCount === 0) {
            e.preventDefault();
            alert('Debe seleccionar al menos una etapa');
            return;
        }

        btnGenerar.disabled = true;
        btnGenerar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generando ' + checkedCount + ' etapas...';
    });
});
</script>

<?= $this->endSection() ?>
