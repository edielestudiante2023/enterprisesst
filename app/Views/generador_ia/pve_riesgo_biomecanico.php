<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador PVE Riesgo Biomecánico - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-warning">
        <div class="container-fluid">
            <a class="navbar-brand text-dark" href="#">
                <i class="bi bi-body-text me-2"></i>Generador IA - PVE Riesgo Biomecánico
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-dark me-3">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">PVE de Riesgo Biomecánico</h4>
                <p class="text-muted mb-0">Estandar 4.2.3 - Resolucion 0312/2019</p>
            </div>
            <div>
                <span class="badge bg-info fs-6"><?= $anio ?></span>
            </div>
        </div>

        <div id="alertContainer"></div>

        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Flujo de generacion del PVE de Riesgo Biomecánico:</strong>
            <ol class="mb-0 mt-2">
                <li><strong>Actividades PVE Biomecánico</strong> - Se generan actividades de prevencion de riesgo biomecanico en el PTA (evaluaciones ergonomicas, pausas activas, capacitaciones)</li>
                <li><strong>Indicadores PVE Biomecánico</strong> - Se configuran indicadores para medir el programa</li>
                <li><strong>Documento del PVE</strong> - Se genera el documento formal con datos de la BD</li>
            </ol>
        </div>

        <!-- Contexto del Cliente para la IA -->
        <?php
            $riesgo = $contexto['nivel_riesgo_arl'] ?? 'N/A';
            $colorRiesgo = match($riesgo) {
                'I', 'II' => 'success',
                'III' => 'warning',
                'IV', 'V' => 'danger',
                default => 'secondary'
            };
            $peligros = [];
            if (!empty($contexto['peligros_identificados'])) {
                $peligros = json_decode($contexto['peligros_identificados'], true) ?: [];
            }
            $infraestructura = [];
            if (!empty($contexto['tiene_copasst'])) $infraestructura[] = 'COPASST';
            if (!empty($contexto['tiene_vigia_sst'])) $infraestructura[] = 'Vigia SST';
            if (!empty($contexto['tiene_comite_convivencia'])) $infraestructura[] = 'Comite Convivencia';
            if (!empty($contexto['tiene_brigada_emergencias'])) $infraestructura[] = 'Brigada Emergencias';
        ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-cpu text-primary me-2"></i>Contexto para la IA
                    <small class="text-muted ms-2">La IA usara esta informacion para generar actividades PVE biomecanico personalizadas</small>
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <a href="<?= base_url('contexto/' . $cliente['id_cliente']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Editar Contexto
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#collapseContexto">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
            </div>
            <div class="collapse show" id="collapseContexto">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-3"><i class="bi bi-building me-1"></i>Datos de la Empresa</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width:45%">Actividad:</td>
                                    <td><strong><?= esc($contexto['actividad_economica_principal'] ?? 'No definida') ?></strong></td>
                                </tr>
                                <?php if (!empty($contexto['sector_economico'])): ?>
                                <tr>
                                    <td class="text-muted">Sector:</td>
                                    <td><?= esc($contexto['sector_economico']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="text-muted">Riesgo ARL:</td>
                                    <td><span class="badge bg-<?= $colorRiesgo ?>"><?= $riesgo ?></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Trabajadores:</td>
                                    <td><strong><?= $contexto['total_trabajadores'] ?? 'No definido' ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Estandares:</td>
                                    <td><span class="badge bg-info"><?= $contexto['estandares_aplicables'] ?? 60 ?> est.</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-3"><i class="bi bi-shield-check me-1"></i>Infraestructura SST</h6>
                            <?php if (!empty($infraestructura)): ?>
                                <?php foreach ($infraestructura as $inf): ?>
                                    <span class="badge bg-success-subtle text-success me-1 mb-1"><i class="bi bi-check me-1"></i><?= $inf ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <small class="text-muted">Sin infraestructura registrada</small>
                            <?php endif; ?>
                            <?php if (!empty($contexto['responsable_sgsst_cargo'])): ?>
                            <div class="mt-2">
                                <small class="text-muted">Responsable SST:</small>
                                <br><small><strong><?= esc($contexto['responsable_sgsst_cargo']) ?></strong></small>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-3"><i class="bi bi-exclamation-diamond me-1"></i>Peligros Identificados</h6>
                            <?php if (!empty($peligros)): ?>
                                <div style="max-height:120px; overflow-y:auto;">
                                    <?php foreach ($peligros as $peligro): ?>
                                        <span class="badge bg-danger-subtle text-danger me-1 mb-1" style="font-size:0.7rem;"><?= esc($peligro) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <small class="text-muted"><?= count($peligros) ?> peligro(s) registrado(s)</small>
                            <?php else: ?>
                                <div class="alert alert-warning small py-1 px-2 mb-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Sin peligros registrados.
                                    <a href="<?= base_url('contexto/' . $cliente['id_cliente']) ?>" target="_blank">Registrar</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($contexto['observaciones_contexto'])): ?>
                    <hr class="my-2">
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-2"><i class="bi bi-journal-text me-1"></i>Contexto y Observaciones</h6>
                            <div class="alert alert-light border small mb-0" style="max-height:100px; overflow-y:auto;">
                                <?= nl2br(esc($contexto['observaciones_contexto'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <hr class="my-3">
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label">
                                <i class="bi bi-chat-dots me-1"></i>Instrucciones adicionales para la IA
                                <small class="text-muted">(opcional)</small>
                            </label>
                            <textarea id="instruccionesIA" class="form-control" rows="3"
                                placeholder="Ej: Enfocarse en evaluaciones ergonomicas de puestos de trabajo, incluir pausas activas personalizadas, priorizar capacitaciones de higiene postural..."></textarea>
                            <small class="text-muted">
                                Describa necesidades especificas para personalizar las actividades PVE de riesgo biomecanico.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado Actual -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-graph-up text-primary me-2"></i><strong>Estado Actual</strong>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span>Actividades PVE existentes: <span class="badge bg-<?= ($resumenActividades['total'] ?? 0) > 0 ? 'success' : 'warning' ?>"><?= $resumenActividades['total'] ?? 0 ?> actividades</span></span>
                        <span>Sugeridas: <span class="badge bg-info"><?= $resumenActividades['sugeridas'] ?? 12 ?></span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actividades Existentes -->
        <?php if (!empty($actividadesExistentes)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-list-check text-success me-2"></i>Actividades PVE Biomecánico en el PTA (<?= count($actividadesExistentes) ?>)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Actividad</th>
                                <th style="width: 120px;">Mes Propuesto</th>
                                <th style="width: 100px;">PHVA</th>
                                <th style="width: 100px;">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($actividadesExistentes as $act): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($act['actividad']) ?></strong>
                                    <?php if (!empty($act['descripcion'])): ?>
                                        <br><small class="text-muted"><?= esc(mb_substr($act['descripcion'], 0, 100)) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($act['fecha_propuesta'] ?? 'N/A') ?></td>
                                <td><span class="badge bg-info"><?= esc(strtoupper($act['phva'] ?? 'H')) ?></span></td>
                                <td>
                                    <?php $estado = $act['estado'] ?? 'pendiente'; $colorEstado = match($estado) { 'completada' => 'success', 'en_progreso' => 'warning', default => 'secondary' }; ?>
                                    <span class="badge bg-<?= $colorEstado ?>"><?= ucfirst($estado) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Boton de Preview y Generar -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning bg-opacity-25">
                <h6 class="mb-0"><i class="bi bi-magic me-2"></i>Generar Actividades PVE Biomecánico</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">Se generaran actividades de prevencion de riesgo biomecanico adaptadas al contexto de la empresa.</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" id="btnPreview" onclick="previewActividades()">
                        <i class="bi bi-eye me-1"></i>Preview Actividades
                    </button>
                    <button type="button" class="btn btn-warning" id="btnGenerar" onclick="generarActividades()" disabled>
                        <i class="bi bi-lightning me-1"></i>Generar en PTA
                    </button>
                    <a href="<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/indicadores-pve-biomecanico') ?>" class="btn btn-outline-success ms-auto">
                        Ir a Parte 2: Indicadores <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Preview Container -->
        <div id="previewContainer" class="d-none">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-table me-2"></i>Preview de Actividades</h6>
                    <button type="button" class="btn btn-sm btn-success" onclick="generarActividades()">
                        <i class="bi bi-check-lg me-1"></i>Confirmar y Generar
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="tablaPreview">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox" id="selectAll" checked></th>
                                    <th>Actividad</th>
                                    <th style="width: 150px;">Descripcion</th>
                                    <th style="width: 100px;">Mes</th>
                                    <th style="width: 80px;">PHVA</th>
                                    <th style="width: 80px;">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="previewBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const idCliente = <?= $cliente['id_cliente'] ?>;
    const anio = <?= $anio ?>;
    let actividadesPreview = [];

    function showAlert(type, message) {
        const container = document.getElementById('alertContainer');
        container.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
    }

    async function previewActividades() {
        const btn = document.getElementById('btnPreview');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Cargando...';

        try {
            const response = await fetch(`<?= base_url('generador-ia') ?>/${idCliente}/preview-actividades-pve-biomecanico?anio=${anio}`);
            const data = await response.json();

            if (data.success) {
                actividadesPreview = data.data.actividades || [];
                renderPreview(actividadesPreview);
                document.getElementById('previewContainer').classList.remove('d-none');
                document.getElementById('btnGenerar').disabled = false;
            } else {
                showAlert('danger', data.message || 'Error al cargar preview');
            }
        } catch (error) {
            showAlert('danger', 'Error de conexion: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-eye me-1"></i>Preview Actividades';
        }
    }

    function renderPreview(actividades) {
        const tbody = document.getElementById('previewBody');
        tbody.innerHTML = '';

        actividades.forEach((act, idx) => {
            const yaExiste = act.ya_existe ? '<span class="badge bg-secondary">Ya existe</span>' : '<span class="badge bg-success">Nueva</span>';
            const checked = act.ya_existe ? '' : 'checked';
            const disabled = act.ya_existe ? 'disabled' : '';
            tbody.innerHTML += `
                <tr class="${act.ya_existe ? 'table-secondary' : ''}">
                    <td><input type="checkbox" class="actCheck" data-idx="${idx}" ${checked} ${disabled}></td>
                    <td><strong>${act.nombre}</strong></td>
                    <td><small class="text-muted">${(act.descripcion || '').substring(0, 80)}</small></td>
                    <td>${act.mes_nombre || 'N/A'}</td>
                    <td><span class="badge bg-info">${(act.phva || 'H').toUpperCase()}</span></td>
                    <td>${yaExiste}</td>
                </tr>
            `;
        });
    }

    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.actCheck:not([disabled])').forEach(cb => cb.checked = this.checked);
    });

    async function generarActividades() {
        const btn = document.getElementById('btnGenerar');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generando...';

        const seleccionadas = [];
        document.querySelectorAll('.actCheck:checked').forEach(cb => {
            const idx = parseInt(cb.dataset.idx);
            if (actividadesPreview[idx]) seleccionadas.push(actividadesPreview[idx]);
        });

        try {
            const response = await fetch(`<?= base_url('generador-ia') ?>/${idCliente}/generar-actividades-pve-biomecanico`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ anio: anio, actividades: seleccionadas.length > 0 ? seleccionadas : null })
            });
            const data = await response.json();

            if (data.success) {
                showAlert('success', `<i class="bi bi-check-circle me-1"></i>${data.message}`);
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert('danger', data.message || 'Error al generar actividades');
            }
        } catch (error) {
            showAlert('danger', 'Error de conexion: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-lightning me-1"></i>Generar en PTA';
        }
    }
    </script>
</body>
</html>
