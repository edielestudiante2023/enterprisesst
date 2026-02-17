<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Checklist PTA - <?= esc($cliente['nombre_cliente']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('consultant/dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('documentacion/dashboard/' . $cliente['id_cliente']) ?>">Documentación</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('induccion-etapas/' . $cliente['id_cliente']) ?>">Etapas Inducción</a></li>
            <li class="breadcrumb-item active">Checklist PTA</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-clipboard-check text-primary me-2"></i>
                Preparación de la Jornada de Inducción
            </h2>
            <p class="text-muted mb-0">
                <?= esc($cliente['nombre_cliente']) ?> - Año <?= $anio ?>
            </p>
        </div>
    </div>

    <!-- Instrucciones -->
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-info-circle me-3 fs-4 mt-1"></i>
            <div>
                <strong>¿Para qué es este checklist?</strong>
                <p class="mb-0 small">
                    Antes de generar las actividades del PTA, defina cómo se va a ejecutar la inducción.
                    La IA usará esta información para generar actividades <strong>específicas y contextualizadas</strong>
                    en lugar de genéricas.
                </p>
            </div>
        </div>
    </div>

    <form action="<?= base_url("induccion-etapas/{$cliente['id_cliente']}/generar-pta") ?>" method="post" id="formChecklist">
        <?= csrf_field() ?>

        <!-- PASO 1: Modalidad -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <span class="badge bg-primary rounded-circle me-2" style="width:28px;height:28px;line-height:20px;">1</span>
                    Modalidad de la inducción
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="d-block modalidad-card selected" role="button">
                            <input type="radio" name="modalidad" value="presencial" class="d-none" checked>
                            <div class="card border-2 h-100">
                                <div class="card-body text-center py-4">
                                    <i class="bi bi-building fs-1 text-primary"></i>
                                    <h6 class="mt-2 mb-1">Presencial</h6>
                                    <small class="text-muted">Salón, refrigerio, formato físico</small>
                                    <div class="mt-2 modalidad-check">
                                        <i class="bi bi-check-circle-fill text-primary fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label class="d-block modalidad-card" role="button">
                            <input type="radio" name="modalidad" value="virtual" class="d-none">
                            <div class="card border-2 h-100">
                                <div class="card-body text-center py-4">
                                    <i class="bi bi-laptop fs-1 text-success"></i>
                                    <h6 class="mt-2 mb-1">Virtual</h6>
                                    <small class="text-muted">Plataforma, enlace, formularios</small>
                                    <div class="mt-2 modalidad-check">
                                        <i class="bi bi-check-circle-fill text-primary fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label class="d-block modalidad-card" role="button">
                            <input type="radio" name="modalidad" value="mixta" class="d-none">
                            <div class="card border-2 h-100">
                                <div class="card-body text-center py-4">
                                    <i class="bi bi-arrows-angle-expand fs-1 text-warning"></i>
                                    <h6 class="mt-2 mb-1">Mixta</h6>
                                    <small class="text-muted">Presencial + virtual simultáneo</small>
                                    <div class="mt-2 modalidad-check">
                                        <i class="bi bi-check-circle-fill text-primary fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- PASO 2: Checklist -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <span class="badge bg-primary rounded-circle me-2" style="width:28px;height:28px;line-height:20px;">2</span>
                        Checklist de preparación
                        <small class="text-muted fw-normal ms-2">Marque lo que ya tiene listo o en curso</small>
                    </h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnMarcarTodos">
                            <i class="bi bi-check-all"></i> Marcar todos
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDesmarcarTodos">
                            <i class="bi bi-x-lg"></i> Desmarcar
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <?php
                // Agrupar ítems por fase
                $fases = [];
                foreach ($checklistItems as $id => $item) {
                    $fases[$item['fase']][$id] = $item;
                }

                $faseLabels = [
                    'planificacion' => ['Planificación', 'bi-calendar-event', 'primary'],
                    'convocatoria' => ['Convocatoria', 'bi-megaphone', 'info'],
                    'preparacion_material' => ['Preparación de material', 'bi-file-earmark-slides', 'success'],
                    'ejecucion' => ['Ejecución', 'bi-play-circle', 'warning'],
                    'evaluacion' => ['Evaluación', 'bi-clipboard-data', 'danger'],
                    'evidencias' => ['Evidencias', 'bi-camera', 'secondary'],
                    'logistica' => ['Logística por modalidad', 'bi-gear', 'dark'],
                    'cierre' => ['Cierre', 'bi-check-circle', 'success'],
                ];
                ?>

                <?php foreach ($fases as $faseKey => $items): ?>
                <?php $faseInfo = $faseLabels[$faseKey] ?? [$faseKey, 'bi-list', 'secondary']; ?>
                <div class="border-bottom">
                    <div class="px-3 py-2 bg-light">
                        <strong>
                            <i class="bi <?= $faseInfo[1] ?> text-<?= $faseInfo[2] ?> me-2"></i>
                            <?= $faseInfo[0] ?>
                        </strong>
                    </div>
                    <div class="px-3 py-2">
                        <?php foreach ($items as $id => $item): ?>
                        <div class="form-check py-1 checklist-item" data-modalidad-item="<?= $item['modalidad'] ?>">
                            <input class="form-check-input checklist-check" type="checkbox"
                                   name="checklist[]" value="<?= $id ?>" id="item_<?= $id ?>">
                            <label class="form-check-label" for="item_<?= $id ?>">
                                <span class="item-numero text-muted me-1"><?= $id ?>.</span>
                                <?= esc($item['texto']) ?>
                                <?php if ($item['modalidad'] !== 'PVM'): ?>
                                <span class="badge bg-light text-dark border ms-1" style="font-size:0.7em;">
                                    <?php
                                    $modLabels = ['P' => 'Presencial', 'V' => 'Virtual', 'M' => 'Mixta'];
                                    echo $modLabels[$item['modalidad']] ?? $item['modalidad'];
                                    ?>
                                </span>
                                <?php endif; ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="card-footer bg-white">
                <small class="text-muted">
                    <i class="bi bi-eye me-1"></i>
                    Mostrando <strong id="itemsVisibles"><?= count($checklistItems) ?></strong> de <?= count($checklistItems) ?> ítems según la modalidad seleccionada
                </small>
            </div>
        </div>

        <!-- PASO 3: Notas -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <span class="badge bg-primary rounded-circle me-2" style="width:28px;height:28px;line-height:20px;">3</span>
                    Notas adicionales
                    <small class="text-muted fw-normal ms-2">(Opcional)</small>
                </h5>
            </div>
            <div class="card-body">
                <textarea class="form-control" name="notas" rows="3"
                          placeholder="Ej: La empresa tiene turnos rotativos, se necesitan 2 jornadas en el mismo día. El salón principal está en remodelación, usar el alterno..."
                ></textarea>
            </div>
        </div>

        <!-- Botones -->
        <div class="d-flex justify-content-between">
            <a href="<?= base_url("induccion-etapas/{$cliente['id_cliente']}") ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver a etapas
            </a>
            <button type="submit" class="btn btn-success btn-lg" id="btnGenerar">
                <i class="bi bi-robot me-2"></i>Generar Actividades con IA
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.modalidad-card { cursor: pointer; transition: all 0.2s; }
.modalidad-card:hover .card { border-color: #0d6efd !important; }
.modalidad-card .card { border-color: #dee2e6 !important; }
.modalidad-card .modalidad-check { display: none; }
.modalidad-card.selected .card { border-color: #0d6efd !important; background-color: #f0f7ff; }
.modalidad-card.selected .modalidad-check { display: block; }
.checklist-item.hidden-by-modalidad { display: none !important; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('CHECKLIST JS CARGADO - radios:', document.querySelectorAll('input[name="modalidad"]').length, 'items:', document.querySelectorAll('.checklist-item').length);
    var radios = document.querySelectorAll('input[name="modalidad"]');
    var labels = document.querySelectorAll('.modalidad-card');
    var items = document.querySelectorAll('.checklist-item');
    var countEl = document.getElementById('itemsVisibles');

    // Escuchar cambio en los radios
    radios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            labels.forEach(function(l) { l.classList.remove('selected'); });
            this.closest('.modalidad-card').classList.add('selected');
            filtrar();
        });
    });

    function filtrar() {
        var modalidad = document.querySelector('input[name="modalidad"]:checked').value;
        var visibles = 0;

        items.forEach(function(item) {
            var mod = item.getAttribute('data-modalidad-item');
            var mostrar = false;

            if (mod === 'PVM') mostrar = true;
            else if (modalidad === 'mixta') mostrar = true;
            else if (modalidad === 'presencial' && mod === 'P') mostrar = true;
            else if (modalidad === 'virtual' && mod === 'V') mostrar = true;

            if (mostrar) {
                item.style.display = '';
                visibles++;
            } else {
                item.style.display = 'none';
                var cb = item.querySelector('input[type="checkbox"]');
                if (cb) cb.checked = false;
            }
        });
        countEl.textContent = visibles;
    }

    // Marcar/desmarcar todos visibles
    document.getElementById('btnMarcarTodos').addEventListener('click', function() {
        items.forEach(function(item) {
            if (item.style.display !== 'none') {
                var cb = item.querySelector('input[type="checkbox"]');
                if (cb) cb.checked = true;
            }
        });
    });

    document.getElementById('btnDesmarcarTodos').addEventListener('click', function() {
        document.querySelectorAll('.checklist-check').forEach(function(cb) { cb.checked = false; });
    });

    // Submit con loading
    document.getElementById('formChecklist').addEventListener('submit', function() {
        var btn = document.getElementById('btnGenerar');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generando actividades con IA...';
    });

    // Filtro inicial
    filtrar();
});
</script>
<?= $this->endSection() ?>
