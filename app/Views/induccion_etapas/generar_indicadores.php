<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Generar Indicadores - <?= esc($cliente['nombre_cliente']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('consultant/dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('documentacion/dashboard/' . $cliente['id_cliente']) ?>">Documentación</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('induccion-etapas/' . $cliente['id_cliente']) ?>">Etapas Inducción</a></li>
            <li class="breadcrumb-item active">Generar Indicadores</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-graph-up text-primary me-2"></i>
                Propuesta de Indicadores de Inducción
            </h2>
            <p class="text-muted mb-0">
                <?= esc($cliente['nombre_cliente']) ?>
            </p>
        </div>
        <div>
            <span class="badge bg-info fs-6 py-2 px-3">
                <i class="bi bi-speedometer2 me-1"></i>
                <?= count($indicadores) ?> indicadores propuestos
            </span>
        </div>
    </div>

    <!-- Indicador de generación con IA -->
    <?php if (!empty($generado_con_ia)): ?>
    <div class="alert alert-success mb-4 border-0">
        <div class="d-flex align-items-center">
            <i class="bi bi-stars me-3 fs-4"></i>
            <div>
                <strong><i class="bi bi-robot me-1"></i>Indicadores generados con IA</strong>
                <p class="mb-0 small">
                    Estos indicadores fueron generados inteligentemente a partir de las
                    <strong><?= $actividades_pta ?> actividades</strong> del Plan de Trabajo Anual.
                    La IA analizó tus actividades para proponer indicadores específicos y medibles.
                </p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle-fill me-3 fs-4"></i>
            <div>
                <strong>Indicadores según Resolución 0312/2019</strong>
                <p class="mb-0 small">
                    Estos indicadores permiten medir el cumplimiento del programa de inducción y reinducción.
                    Son requeridos para demostrar la efectividad del SG-SST ante auditorías.
                    <?php if (($actividades_pta ?? 0) === 0): ?>
                    <br><span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>No se encontraron actividades en el PTA. Se muestran indicadores base.</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($indicadores)): ?>

    <form action="<?= base_url("induccion-etapas/{$cliente['id_cliente']}/enviar-indicadores") ?>" method="post" id="formEnviarIndicadores">
        <?= csrf_field() ?>

        <div class="row g-4 mb-4">
            <?php foreach ($indicadores as $index => $indicador): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input indicador-check"
                                   name="indicadores[<?= $index ?>][incluir]" value="1" checked
                                   id="check<?= $index ?>">
                            <label class="form-check-label fw-bold" for="check<?= $index ?>">
                                <?= esc($indicador['nombre']) ?>
                            </label>
                        </div>
                        <span class="badge bg-<?= $indicador['tipo'] === 'proceso' ? 'primary' : 'success' ?>">
                            <?= ucfirst($indicador['tipo']) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="indicadores[<?= $index ?>][nombre]" value="<?= esc($indicador['nombre']) ?>">
                        <input type="hidden" name="indicadores[<?= $index ?>][tipo]" value="<?= esc($indicador['tipo']) ?>">

                        <div class="mb-3">
                            <label class="form-label small text-muted">Fórmula</label>
                            <textarea class="form-control form-control-sm" rows="2"
                                      name="indicadores[<?= $index ?>][formula]"><?= esc($indicador['formula']) ?></textarea>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small text-muted">Meta</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control"
                                           name="indicadores[<?= $index ?>][meta]"
                                           value="<?= $indicador['meta'] ?>">
                                    <span class="input-group-text"><?= esc($indicador['unidad']) ?></span>
                                </div>
                                <input type="hidden" name="indicadores[<?= $index ?>][unidad]" value="<?= esc($indicador['unidad']) ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted">Periodicidad</label>
                                <select class="form-select form-select-sm" name="indicadores[<?= $index ?>][periodicidad]">
                                    <option value="mensual" <?= $indicador['periodicidad'] === 'mensual' ? 'selected' : '' ?>>Mensual</option>
                                    <option value="trimestral" <?= $indicador['periodicidad'] === 'trimestral' ? 'selected' : '' ?>>Trimestral</option>
                                    <option value="semestral" <?= $indicador['periodicidad'] === 'semestral' ? 'selected' : '' ?>>Semestral</option>
                                    <option value="anual" <?= $indicador['periodicidad'] === 'anual' ? 'selected' : '' ?>>Anual</option>
                                </select>
                            </div>
                        </div>

                        <?php if (!empty($indicador['justificacion'])): ?>
                        <div class="mt-3 p-2 bg-light rounded small">
                            <i class="bi bi-lightbulb text-warning me-1"></i>
                            <em><?= esc($indicador['justificacion']) ?></em>
                        </div>
                        <?php endif; ?>

                        <div class="mt-3 d-flex flex-wrap align-items-center gap-1">
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-bookmark me-1"></i>
                                Numeral <?= esc($indicador['numeral']) ?>
                            </span>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-arrow-repeat me-1"></i>
                                <?= esc($indicador['phva']) ?>
                            </span>
                            <?php if (!empty($indicador['generado_por_ia'])): ?>
                            <span class="badge bg-success" title="Generado con Inteligencia Artificial">
                                <i class="bi bi-robot"></i> IA
                            </span>
                            <?php endif; ?>
                            <button type="button" class="badge bg-warning text-dark border-0 btn-ajustar-ia ms-auto"
                                    data-index="<?= $index ?>" title="Ajustar con IA">
                                <i class="bi bi-chat-dots me-1"></i>Ajustar
                            </button>
                        </div>

                        <!-- Panel de ajuste con IA -->
                        <div class="ajuste-ia-panel mt-3 d-none" id="panelAjuste<?= $index ?>">
                            <div class="border rounded p-2 bg-light">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-robot text-primary me-2"></i>
                                    <small class="fw-bold text-primary">Ajustar indicador con IA</small>
                                </div>
                                <textarea class="form-control form-control-sm mb-2" rows="2"
                                          id="feedbackIA<?= $index ?>"
                                          placeholder="Ej: Cambia la meta a 80% porque es más realista para nuestra empresa..."></textarea>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-primary flex-grow-1 btn-enviar-ajuste"
                                            data-index="<?= $index ?>">
                                        <i class="bi bi-send me-1"></i>Ajustar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-cerrar-ajuste"
                                            data-index="<?= $index ?>">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                                <div class="mt-2 small text-muted d-none" id="respuestaIA<?= $index ?>"></div>
                            </div>
                        </div>

                        <input type="hidden" name="indicadores[<?= $index ?>][numeral]" value="<?= esc($indicador['numeral']) ?>">
                        <input type="hidden" name="indicadores[<?= $index ?>][phva]" value="<?= esc($indicador['phva']) ?>">
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Acciones -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Los indicadores seleccionados se agregarán al tablero de indicadores del cliente.
                        </span>
                    </div>
                    <div>
                        <a href="<?= base_url("induccion-etapas/{$cliente['id_cliente']}") ?>" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary" id="btnEnviar">
                            <i class="bi bi-send me-1"></i>
                            Enviar Indicadores (<span id="countSelected"><?= count($indicadores) ?></span>)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Información adicional -->
    <div class="row g-4 mt-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6><i class="bi bi-lightbulb text-warning me-2"></i>Interpretación de los Indicadores</h6>
                    <?php if (!empty($generado_con_ia)): ?>
                    <p class="small text-muted mb-2">
                        <i class="bi bi-robot me-1"></i>
                        Los indicadores fueron personalizados según las <?= $actividades_pta ?> actividades de tu Plan de Trabajo.
                    </p>
                    <ul class="small text-muted mb-0">
                        <?php foreach ($indicadores as $ind): ?>
                        <li><strong><?= esc($ind['nombre']) ?>:</strong> <?= !empty($ind['justificacion']) ? esc($ind['justificacion']) : 'Indicador de ' . esc($ind['tipo']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <ul class="small text-muted mb-0">
                        <li><strong>Cobertura:</strong> Mide qué porcentaje de trabajadores nuevos reciben la inducción completa</li>
                        <li><strong>Cumplimiento:</strong> Mide qué porcentaje de los temas programados se ejecutan efectivamente</li>
                        <li><strong>Oportunidad:</strong> Mide si la inducción se realiza antes del inicio de labores</li>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6><i class="bi bi-check-circle text-success me-2"></i>Evidencias Requeridas</h6>
                    <ul class="small text-muted mb-0">
                        <li>Registros de asistencia a inducción firmados</li>
                        <li>Evaluaciones de conocimientos post-inducción</li>
                        <li>Formato de inducción/reinducción diligenciado</li>
                        <li>Registro fotográfico de las capacitaciones</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>

    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        No se pudieron generar indicadores. Verifique que las etapas estén aprobadas.
        <a href="<?= base_url("induccion-etapas/{$cliente['id_cliente']}") ?>">Volver a ver etapas</a>
    </div>

    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.indicador-check');
    const countSelected = document.getElementById('countSelected');

    function updateCount() {
        const checked = document.querySelectorAll('.indicador-check:checked').length;
        countSelected.textContent = checked;
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateCount);
    });

    document.getElementById('formEnviarIndicadores').addEventListener('submit', function(e) {
        const btn = document.getElementById('btnEnviar');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
    });

    // Mostrar/Ocultar panel de ajuste IA
    document.querySelectorAll('.btn-ajustar-ia').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = this.dataset.index;
            const panel = document.getElementById('panelAjuste' + index);
            panel.classList.toggle('d-none');
            if (!panel.classList.contains('d-none')) {
                document.getElementById('feedbackIA' + index).focus();
            }
        });
    });

    // Cerrar panel de ajuste
    document.querySelectorAll('.btn-cerrar-ajuste').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = this.dataset.index;
            document.getElementById('panelAjuste' + index).classList.add('d-none');
        });
    });

    // Enviar ajuste con IA
    document.querySelectorAll('.btn-enviar-ajuste').forEach(btn => {
        btn.addEventListener('click', async function() {
            const index = this.dataset.index;
            const feedback = document.getElementById('feedbackIA' + index).value.trim();
            const respuestaDiv = document.getElementById('respuestaIA' + index);

            if (!feedback) {
                alert('Por favor escribe qué quieres ajustar del indicador');
                return;
            }

            // Obtener datos actuales del indicador
            const card = this.closest('.card');
            const nombreInput = card.querySelector('input[name*="[nombre]"]');
            const formulaInput = card.querySelector('textarea[name*="[formula]"]');
            const metaInput = card.querySelector('input[name*="[meta]"]');
            const periodicidadSelect = card.querySelector('select[name*="[periodicidad]"]');
            const tipoInput = card.querySelector('input[name*="[tipo]"]');

            const indicadorActual = {
                nombre: nombreInput.value,
                formula: formulaInput.value,
                meta: metaInput.value,
                periodicidad: periodicidadSelect.value,
                tipo: tipoInput.value
            };

            // Mostrar loading
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            respuestaDiv.classList.remove('d-none');
            respuestaDiv.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>La IA está procesando tu solicitud...';

            try {
                const response = await fetch('<?= base_url("induccion-etapas/{$cliente['id_cliente']}/ajustar-indicador") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        indicador: indicadorActual,
                        feedback: feedback,
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Actualizar los campos con la respuesta de la IA
                    if (data.indicador.nombre) {
                        nombreInput.value = data.indicador.nombre;
                        card.querySelector('.form-check-label').textContent = data.indicador.nombre;
                    }
                    if (data.indicador.formula) {
                        formulaInput.value = data.indicador.formula;
                    }
                    if (data.indicador.meta) {
                        metaInput.value = data.indicador.meta;
                    }
                    if (data.indicador.periodicidad) {
                        periodicidadSelect.value = data.indicador.periodicidad;
                    }

                    respuestaDiv.innerHTML = '<i class="bi bi-check-circle text-success me-1"></i>' +
                        (data.explicacion || 'Indicador ajustado correctamente');
                    respuestaDiv.classList.remove('text-muted');
                    respuestaDiv.classList.add('text-success');

                    // Limpiar el textarea
                    document.getElementById('feedbackIA' + index).value = '';

                    // Cerrar el panel después de 3 segundos
                    setTimeout(() => {
                        document.getElementById('panelAjuste' + index).classList.add('d-none');
                        respuestaDiv.classList.add('d-none');
                        respuestaDiv.classList.remove('text-success');
                        respuestaDiv.classList.add('text-muted');
                    }, 3000);
                } else {
                    respuestaDiv.innerHTML = '<i class="bi bi-exclamation-triangle text-warning me-1"></i>' +
                        (data.error || 'Error al procesar la solicitud');
                    respuestaDiv.classList.remove('text-muted');
                    respuestaDiv.classList.add('text-danger');
                }
            } catch (error) {
                respuestaDiv.innerHTML = '<i class="bi bi-exclamation-triangle text-danger me-1"></i>Error de conexión';
                respuestaDiv.classList.add('text-danger');
            }

            // Restaurar botón
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-send me-1"></i>Ajustar';
        });
    });
});
</script>

<?= $this->endSection() ?>
