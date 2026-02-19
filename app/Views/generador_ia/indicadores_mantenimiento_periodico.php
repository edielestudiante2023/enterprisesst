<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador IA - Indicadores Mantenimiento Periodico - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-graph-up me-2"></i>Generador IA - Indicadores Mantenimiento Periodico
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a href="<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/mantenimiento-periodico') ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Indicadores de Mantenimiento Periodico</h4>
                <p class="text-muted mb-0">Estandar 4.2.5 - Resolucion 0312/2019</p>
            </div>
            <div>
                <span class="badge bg-info fs-6"><?= $anio ?></span>
            </div>
        </div>

        <!-- Alertas -->
        <div id="alertContainer"></div>

        <!-- Info del flujo -->
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Indicadores para medir el programa de mantenimiento periodico:</strong>
            <ol class="mb-0 mt-2">
                <li><strong>Cumplimiento del programa</strong> - Mide la ejecucion del mantenimiento preventivo</li>
                <li><strong>Estado de equipos</strong> - Mide fichas tecnicas y disponibilidad operativa</li>
                <li><strong>Indicadores de resultado</strong> - Fallas, accidentes por equipos, inspecciones</li>
            </ol>
        </div>

        <!-- Contexto de la Empresa para la IA -->
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
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0">
                    <i class="bi bi-cpu text-primary me-2"></i>Contexto de la Empresa
                </h6>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#collapseContexto">
                    <i class="bi bi-eye me-1"></i>Ver contexto IA
                </button>
            </div>
            <div class="collapse" id="collapseContexto">
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
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Panel Principal: Indicadores -->
            <div class="col-md-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up me-2"></i>Indicadores de Mantenimiento Periodico
                        </h5>
                        <span class="badge bg-light text-primary"><?= $resumenIndicadores['existentes'] ?> configurados</span>
                    </div>
                    <div class="card-body">
                        <!-- Estado actual -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <h3 class="mb-0 text-primary"><?= $resumenIndicadores['existentes'] ?></h3>
                                    <small class="text-muted">Indicadores existentes</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <h3 class="mb-0 text-info"><?= $resumenIndicadores['limite'] ?? 6 ?></h3>
                                    <small class="text-muted">Limite IA</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <h3 class="mb-0 <?= $resumenIndicadores['completo'] ? 'text-success' : 'text-warning' ?>"><?= $resumenIndicadores['minimo'] ?></h3>
                                    <small class="text-muted">Minimo requerido</small>
                                </div>
                            </div>
                        </div>

                        <?php if ($resumenIndicadores['completo']): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>Fase completa:</strong> Ya tiene <?= $resumenIndicadores['existentes'] ?> indicadores de Mantenimiento Periodico configurados.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Faltan indicadores:</strong> Se requieren minimo <?= $resumenIndicadores['minimo'] ?> indicadores.
                            </div>
                        <?php endif; ?>

                        <!-- Indicadores que se generaran -->
                        <div class="alert alert-light border mb-3">
                            <strong><i class="bi bi-lightbulb me-1"></i>Indicadores sugeridos:</strong>
                            <ul class="mb-0 mt-2 small">
                                <li><strong>Cumplimiento del programa de mantenimiento preventivo</strong></li>
                                <li><strong>Porcentaje de equipos con ficha tecnica actualizada</strong></li>
                                <li><strong>Numero de fallas por mantenimiento inadecuado</strong></li>
                                <li><strong>Disponibilidad operativa de equipos criticos</strong></li>
                                <li><strong>Cumplimiento de inspecciones de seguridad</strong></li>
                                <li><strong>Tasa de accidentes relacionados con equipos</strong></li>
                            </ul>
                        </div>

                        <!-- Botones -->
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="previewIndicadores()">
                                <i class="bi bi-eye me-1"></i>Ver Preview de Indicadores
                            </button>
                            <button type="button" class="btn btn-primary" onclick="previewIndicadores()">
                                <i class="bi bi-magic me-1"></i>Generar Indicadores
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral: Contexto -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Contexto del Cliente</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted">Actividad:</td>
                                <td><small><?= esc($contexto['actividad_economica_principal'] ?? 'No definida') ?></small></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Riesgo ARL:</td>
                                <td>
                                    <?php
                                    $riesgo = $contexto['nivel_riesgo_arl'] ?? 'N/A';
                                    $colorRiesgo = match($riesgo) {
                                        'I', 'II' => 'success',
                                        'III' => 'warning',
                                        'IV', 'V' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $colorRiesgo ?>"><?= $riesgo ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Trabajadores:</td>
                                <td><?= $contexto['total_trabajadores'] ?? 'N/A' ?></td>
                            </tr>
                        </table>

                        <?php
                        $peligros = [];
                        if (!empty($contexto['peligros_identificados'])) {
                            $peligros = json_decode($contexto['peligros_identificados'], true) ?? [];
                        }
                        ?>
                        <?php if (!empty($peligros)): ?>
                            <hr>
                            <small class="text-muted d-block mb-2">Peligros identificados:</small>
                            <div class="d-flex flex-wrap gap-1">
                                <?php foreach ($peligros as $peligro): ?>
                                    <span class="badge bg-warning text-dark"><?= esc($peligro) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Link al modulo de indicadores -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body">
                        <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-graph-up me-1"></i>Ver Modulo de Indicadores
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicadores existentes -->
        <?php if (!empty($indicadoresExistentes)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-list-check me-2"></i>Indicadores de Mantenimiento Periodico Configurados (<?= count($indicadoresExistentes) ?>)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Indicador</th>
                                <th>Tipo</th>
                                <th>Meta</th>
                                <th>Periodicidad</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($indicadoresExistentes as $ind): ?>
                            <tr>
                                <td>
                                    <?= esc($ind['nombre_indicador']) ?>
                                    <small class="d-block text-muted"><?= esc($ind['formula'] ?? '') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $ind['tipo_indicador'] == 'resultado' ? 'danger' : ($ind['tipo_indicador'] == 'proceso' ? 'success' : 'secondary') ?>">
                                        <?= ucfirst(esc($ind['tipo_indicador'])) ?>
                                    </span>
                                </td>
                                <td><?= $ind['meta'] ?><?= esc($ind['unidad_medida']) ?></td>
                                <td><?= ucfirst(esc($ind['periodicidad'])) ?></td>
                                <td>
                                    <?php if ($ind['cumple_meta'] === null): ?>
                                        <span class="badge bg-secondary">Sin medir</span>
                                    <?php elseif ($ind['cumple_meta'] == 1): ?>
                                        <span class="badge bg-success">Cumple</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">No cumple</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Modal Preview -->
        <div class="modal fade" id="modalPreview" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Seleccionar Indicadores Mantenimiento Periodico</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="previewContent">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary"></div>
                            <p class="mt-2">Cargando indicadores...</p>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <div>
                            <span id="contadorSeleccion" class="text-muted">0 indicadores seleccionados</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="btnGenerarSeleccionados" onclick="generarIndicadoresSeleccionados()">
                                <i class="bi bi-magic me-1"></i>Generar Seleccionados
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
        <div id="toastNotification" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi me-2" id="toastIcon"></i>
                <strong class="me-auto" id="toastTitle">Notificacion</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastBody"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const idCliente = <?= $cliente['id_cliente'] ?>;
    const anio = <?= $anio ?>;
    let indicadoresData = [];

    const tiposColor = {
        'estructura': 'secondary',
        'proceso': 'success',
        'resultado': 'danger'
    };

    const periodicidades = {
        'mensual': 'Mensual',
        'trimestral': 'Trimestral',
        'semestral': 'Semestral',
        'anual': 'Anual'
    };

    function showToast(type, title, message) {
        const toast = document.getElementById('toastNotification');
        const toastIcon = document.getElementById('toastIcon');
        const toastTitle = document.getElementById('toastTitle');
        const toastBody = document.getElementById('toastBody');

        toast.className = 'toast';
        if (type === 'success') {
            toastIcon.className = 'bi bi-check-circle-fill text-success me-2';
            toast.classList.add('border-success');
        } else if (type === 'error') {
            toastIcon.className = 'bi bi-x-circle-fill text-danger me-2';
            toast.classList.add('border-danger');
        } else {
            toastIcon.className = 'bi bi-info-circle-fill text-primary me-2';
            toast.classList.add('border-primary');
        }

        toastTitle.textContent = title;
        toastBody.innerHTML = message;

        const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
        bsToast.show();
    }

    function previewIndicadores() {
        const modal = new bootstrap.Modal(document.getElementById('modalPreview'));
        modal.show();

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/preview-indicadores-mantenimiento`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    indicadoresData = data.data.indicadores;
                    renderPreviewTable();
                } else {
                    document.getElementById('previewContent').innerHTML =
                        `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(err => {
                document.getElementById('previewContent').innerHTML =
                    `<div class="alert alert-danger">Error de conexion</div>`;
            });
    }

    function renderPreviewTable() {
        let html = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <strong>Total: ${indicadoresData.length} indicadores sugeridos</strong>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="seleccionarTodos(true)">
                        <i class="bi bi-check-all me-1"></i>Seleccionar Todos
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="seleccionarTodos(false)">
                        <i class="bi bi-x-lg me-1"></i>Deseleccionar
                    </button>
                </div>
            </div>
            <div class="alert alert-light small border mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Seleccione los indicadores que desea crear. Los que ya existen aparecen deshabilitados.
                <div class="mt-2">
                    <span class="badge bg-secondary me-1">Estructura</span> Miden recursos y organizacion
                    <span class="badge bg-success ms-2 me-1">Proceso</span> Miden ejecucion de actividades
                    <span class="badge bg-danger ms-2 me-1">Resultado</span> Miden impacto en seguridad
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px">
                                <input type="checkbox" class="form-check-input" id="checkAll" onchange="seleccionarTodos(this.checked)" checked>
                            </th>
                            <th>Indicador</th>
                            <th style="width:100px">Tipo</th>
                            <th style="width:80px">Meta</th>
                            <th style="width:100px">Periodicidad</th>
                            <th style="width:80px">Origen</th>
                        </tr>
                    </thead>
                    <tbody>`;

        indicadoresData.forEach((ind, idx) => {
            const yaExiste = ind.ya_existe || false;
            const disabled = yaExiste ? 'disabled' : '';
            const checked = ind.seleccionado && !yaExiste ? 'checked' : '';
            const rowClass = yaExiste ? 'table-secondary' : '';

            html += `
                <tr class="${rowClass}">
                    <td>
                        <input type="checkbox" class="form-check-input indicador-check"
                               data-idx="${idx}" ${checked} ${disabled} onchange="actualizarContador()">
                    </td>
                    <td>
                        <span class="indicador-nombre">${ind.nombre}</span>
                        <small class="d-block text-muted">${ind.formula || ''}</small>
                        ${ind.descripcion ? `<small class="d-block text-info">${ind.descripcion}</small>` : ''}
                        ${yaExiste ? `<small class="d-block text-secondary"><i class="bi bi-check-circle me-1"></i>Ya existe</small>` : ''}
                    </td>
                    <td><span class="badge bg-${tiposColor[ind.tipo] || 'secondary'}">${ind.tipo}</span></td>
                    <td>${ind.meta}${ind.unidad}</td>
                    <td><small>${periodicidades[ind.periodicidad] || ind.periodicidad}</small></td>
                    <td>
                        <span class="badge bg-secondary">Base</span>
                    </td>
                </tr>`;
        });

        html += `</tbody></table></div>`;
        document.getElementById('previewContent').innerHTML = html;
        actualizarContador();
    }

    function seleccionarTodos(seleccionar) {
        document.querySelectorAll('.indicador-check:not(:disabled)').forEach(cb => {
            cb.checked = seleccionar;
        });
        document.getElementById('checkAll').checked = seleccionar;
        actualizarContador();
    }

    function actualizarContador() {
        const total = document.querySelectorAll('.indicador-check:checked').length;
        document.getElementById('contadorSeleccion').textContent = `${total} indicadores seleccionados`;

        const btnGenerar = document.getElementById('btnGenerarSeleccionados');
        if (total === 0) {
            btnGenerar.disabled = true;
            btnGenerar.innerHTML = '<i class="bi bi-magic me-1"></i>Seleccione indicadores';
        } else {
            btnGenerar.disabled = false;
            btnGenerar.innerHTML = `<i class="bi bi-magic me-1"></i>Generar ${total} Indicadores`;
        }
    }

    function generarIndicadoresSeleccionados() {
        const seleccionados = [];

        document.querySelectorAll('.indicador-check:checked').forEach(cb => {
            const idx = parseInt(cb.dataset.idx);
            seleccionados.push(indicadoresData[idx]);
        });

        if (seleccionados.length === 0) {
            showToast('warning', 'Atencion', 'Seleccione al menos un indicador');
            return;
        }

        if (!confirm(`¿Generar ${seleccionados.length} indicadores de Mantenimiento Periodico?`)) return;

        const btn = document.getElementById('btnGenerarSeleccionados');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generando...';

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/generar-indicadores-mantenimiento`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                indicadores: seleccionados
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalPreview')).hide();

                const detalles = `<strong>${data.data?.creados || 0}</strong> indicadores creados<br>
                                  <strong>${data.data?.existentes || 0}</strong> ya existian`;
                showToast('success', 'Indicadores Generados', detalles);

                setTimeout(() => location.reload(), 2000);
            } else {
                showToast('error', 'Error', data.message);
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-magic me-1"></i>Generar ${seleccionados.length} Indicadores`;
            }
        })
        .catch(err => {
            showToast('error', 'Error de Conexion', 'No se pudo conectar con el servidor');
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-magic me-1"></i>Generar ${seleccionados.length} Indicadores`;
        });
    }
    </script>
</body>
</html>
