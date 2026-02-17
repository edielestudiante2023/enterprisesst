<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Generar Actividades PTA - <?= esc($cliente['nombre_cliente']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('consultant/dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('documentacion/dashboard/' . $cliente['id_cliente']) ?>">Documentación</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('induccion-etapas/' . $cliente['id_cliente']) ?>">Etapas Inducción</a></li>
            <li class="breadcrumb-item active">Generar Actividades PTA</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-calendar-check text-success me-2"></i>
                Propuesta de Actividades para el PTA
            </h2>
            <p class="text-muted mb-0">
                <?= esc($cliente['nombre_cliente']) ?> - Año <?= $anio ?>
            </p>
        </div>
        <div class="text-end">
            <span class="badge bg-success fs-6 py-2 px-3 d-block mb-2">
                <i class="bi bi-robot me-1"></i>
                <?= count($actividades) ?> actividades operativas
            </span>
            <?php if (isset($total_temas_originales) && $total_temas_originales > count($actividades)): ?>
            <small class="text-muted">
                <i class="bi bi-arrow-down-circle me-1"></i>
                Basado en <?= $total_temas_originales ?> temas del programa
            </small>
            <?php endif; ?>
        </div>
    </div>

    <!-- Resumen generación IA -->
    <div class="alert alert-success mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-robot me-3 fs-4"></i>
            <div>
                <strong>
                    <?php if (isset($consolidado_con_ia) && $consolidado_con_ia): ?>
                        <i class="bi bi-stars text-warning me-1"></i>
                        Actividades operativas generadas con IA
                    <?php else: ?>
                        Actividades operativas predeterminadas
                    <?php endif; ?>
                </strong>
                <p class="mb-0 small">
                    <?php if (isset($total_temas_originales)): ?>
                        La IA ha generado <strong><?= count($actividades) ?> actividades de preparación y ejecución</strong>
                        para la jornada de inducción basada en <?= $total_temas_originales ?> temas de <?= count($etapas) ?> etapas.
                    <?php else: ?>
                        Basado en <?= count($etapas) ?> etapas de inducción aprobadas.
                    <?php endif; ?>
                    Puedes editar los campos antes de enviar.
                </p>
            </div>
        </div>
    </div>

    <?php if (!empty($actividades)): ?>

    <!-- Tabla de actividades propuestas -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list-task me-2"></i>
                    Actividades Propuestas
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <label class="text-muted small text-nowrap mb-0">Fecha para todas:</label>
                    <input type="date" class="form-control form-control-sm" id="fechaGlobal" style="width: 160px;">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAplicarFecha">
                        <i class="bi bi-calendar-check"></i> Aplicar
                    </button>
                    <span class="border-start mx-1"></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSelectAll">
                        <i class="bi bi-check-all"></i> Seleccionar todas
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeselectAll">
                        <i class="bi bi-x-lg"></i> Deseleccionar
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <form action="<?= base_url("induccion-etapas/{$cliente['id_cliente']}/enviar-pta") ?>" method="post" id="formEnviarPTA">
                <?= csrf_field() ?>
                <input type="hidden" name="anio" value="<?= $anio ?>">

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="checkAll" checked>
                                </th>
                                <th style="width: 50px;">#</th>
                                <th>Actividad Operativa</th>
                                <th style="width: 200px;">Responsable</th>
                                <th style="width: 130px;">Fecha Propuesta</th>
                                <th style="width: 80px;">PHVA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($actividades as $index => $actividad): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input actividad-check"
                                           name="actividades[<?= $index ?>][incluir]" value="1" checked>
                                </td>
                                <td>
                                    <span class="badge bg-success rounded-circle" style="width: 28px; height: 28px; line-height: 20px;">
                                        <?= $index + 1 ?>
                                    </span>
                                </td>
                                <td>
                                    <input type="hidden" name="actividades[<?= $index ?>][numero_etapa]" value="<?= $actividad['numero_etapa'] ?? ($index + 1) ?>">
                                    <input type="text" class="form-control form-control-sm border-0 bg-light fw-semibold"
                                           name="actividades[<?= $index ?>][actividad]"
                                           value="<?= esc($actividad['actividad']) ?>">
                                    <textarea class="form-control form-control-sm mt-1" rows="2"
                                              name="actividades[<?= $index ?>][descripcion]"
                                              placeholder="Observaciones / temas incluidos"><?= esc($actividad['descripcion'] ?? '') ?></textarea>
                                    <?php if (!empty($actividad['generado_por_ia'])): ?>
                                    <span class="badge bg-light text-success border border-success mt-1">
                                        <i class="bi bi-robot me-1"></i>Generado con IA
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                           name="actividades[<?= $index ?>][responsable]"
                                           value="<?= esc($actividad['responsable']) ?>">
                                </td>
                                <td>
                                    <input type="date" class="form-control form-control-sm"
                                           name="actividades[<?= $index ?>][fecha]"
                                           value="<?= $actividad['fecha'] ?>">
                                </td>
                                <td>
                                    <?php
                                        $phva = $actividad['phva'] ?? 'HACER';
                                        $phvaColors = ['PLANEAR' => 'primary', 'HACER' => 'info', 'VERIFICAR' => 'warning', 'ACTUAR' => 'danger'];
                                        $phvaColor = $phvaColors[$phva] ?? 'info';
                                    ?>
                                    <span class="badge bg-<?= $phvaColor ?>"><?= esc($phva) ?></span>
                                    <input type="hidden" name="actividades[<?= $index ?>][phva]" value="<?= esc($phva) ?>">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Las actividades seleccionadas se agregarán al Plan de Trabajo Anual del cliente.
                    </span>
                </div>
                <div>
                    <a href="<?= base_url("induccion-etapas/{$cliente['id_cliente']}") ?>" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                    </a>
                    <button type="submit" form="formEnviarPTA" class="btn btn-success" id="btnEnviar">
                        <i class="bi bi-send me-1"></i>
                        Enviar al Plan de Trabajo (<span id="countSelected"><?= count($actividades) ?></span>)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6><i class="bi bi-lightbulb text-warning me-2"></i>Actividades operativas, no temáticas</h6>
                    <ul class="small text-muted mb-0">
                        <li><strong>Planificación:</strong> Coordinar logística, fecha, recursos</li>
                        <li><strong>Convocatoria:</strong> Invitación, socialización por medios digitales o físicos</li>
                        <li><strong>Ejecución:</strong> Asistencia, desarrollo de contenidos, medios audiovisuales</li>
                        <li><strong>Evaluación:</strong> Evaluación de conocimientos, calificación, registros fotográficos</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6><i class="bi bi-bookmark-check text-primary me-2"></i>Estándar 1.2.2</h6>
                    <p class="small text-muted mb-0">
                        <em>"¿Se evidencia el cumplimiento del programa anual de capacitación y de los procesos de
                        inducción y reinducción en seguridad y salud en el trabajo previa al inicio de sus labores
                        que cubre a todos los trabajadores?"</em>
                    </p>
                    <hr class="my-2">
                    <small class="text-success">
                        <i class="bi bi-check-circle me-1"></i>
                        Con 3-4 actividades bien definidas es más fácil demostrar cumplimiento
                    </small>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>

    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        No hay etapas de inducción aprobadas.
        <a href="<?= base_url("induccion-etapas/{$cliente['id_cliente']}") ?>">Volver a ver etapas</a>
    </div>

    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('.actividad-check');
    const countSelected = document.getElementById('countSelected');
    const btnSelectAll = document.getElementById('btnSelectAll');
    const btnDeselectAll = document.getElementById('btnDeselectAll');

    function updateCount() {
        const checked = document.querySelectorAll('.actividad-check:checked').length;
        countSelected.textContent = checked;
    }

    checkAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateCount();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateCount);
    });

    btnSelectAll.addEventListener('click', function() {
        checkAll.checked = true;
        checkboxes.forEach(cb => cb.checked = true);
        updateCount();
    });

    btnDeselectAll.addEventListener('click', function() {
        checkAll.checked = false;
        checkboxes.forEach(cb => cb.checked = false);
        updateCount();
    });

    // Aplicar fecha global a todas las actividades
    document.getElementById('btnAplicarFecha').addEventListener('click', function() {
        const fecha = document.getElementById('fechaGlobal').value;
        if (!fecha) return;
        document.querySelectorAll('input[name$="[fecha]"]').forEach(function(input) {
            input.value = fecha;
        });
    });

    document.getElementById('formEnviarPTA').addEventListener('submit', function(e) {
        const btn = document.getElementById('btnEnviar');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
    });
});
</script>

<?= $this->endSection() ?>
