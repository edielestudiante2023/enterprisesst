<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indicadores PVE Biomecánico - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-warning">
        <div class="container-fluid">
            <a class="navbar-brand text-dark" href="#">
                <i class="bi bi-body-text me-2"></i>Indicadores PVE Riesgo Biomecánico
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-dark me-3">
                    <i class="bi bi-building me-1"></i><?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a href="<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/pve-riesgo-biomecanico') ?>" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver a Parte 1
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Indicadores del PVE de Riesgo Biomecánico</h4>
                <p class="text-muted mb-0">Parte 2 - Estandar 4.2.3 - Resolucion 0312/2019</p>
            </div>
            <span class="badge bg-info fs-6"><?= $anio ?></span>
        </div>

        <div id="alertContainer"></div>

        <!-- Resumen -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><?= $resumenIndicadores['existentes'] ?? 0 ?></h3>
                        <small class="text-muted">Indicadores existentes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="text-info"><?= $resumenIndicadores['limite'] ?? 7 ?></h3>
                        <small class="text-muted">Limite IA</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="text-success"><?= $resumenIndicadores['medidos'] ?? 0 ?></h3>
                        <small class="text-muted">Con medicion</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="text-warning"><?= $resumenIndicadores['cumplen'] ?? 0 ?></h3>
                        <small class="text-muted">Cumplen meta</small>
                    </div>
                </div>
            </div>
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

        <!-- Indicadores Existentes -->
        <?php if (!empty($indicadoresExistentes)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-graph-up text-success me-2"></i>Indicadores PVE Biomecánico (<?= count($indicadoresExistentes) ?>)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Indicador</th>
                                <th>Tipo</th>
                                <th>Formula</th>
                                <th>Meta</th>
                                <th>Periodicidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($indicadoresExistentes as $ind): ?>
                            <tr>
                                <td><strong><?= esc($ind['nombre_indicador']) ?></strong></td>
                                <td><span class="badge bg-info"><?= ucfirst($ind['tipo_indicador'] ?? '') ?></span></td>
                                <td><small><?= esc($ind['formula'] ?? '') ?></small></td>
                                <td><?= esc($ind['meta'] ?? '') ?> <?= esc($ind['unidad_medida'] ?? '') ?></td>
                                <td><?= ucfirst($ind['periodicidad'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Generar Indicadores -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning bg-opacity-25">
                <h6 class="mb-0"><i class="bi bi-magic me-2"></i>Generar Indicadores</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">Se generaran indicadores para medir el PVE de riesgo biomecanico.</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" id="btnPreviewInd" onclick="previewIndicadores()">
                        <i class="bi bi-eye me-1"></i>Preview Indicadores
                    </button>
                    <button type="button" class="btn btn-warning" id="btnGenerarInd" onclick="generarIndicadores()" disabled>
                        <i class="bi bi-lightning me-1"></i>Generar Indicadores
                    </button>
                </div>
            </div>
        </div>

        <div id="previewContainer" class="d-none">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-table me-2"></i>Preview de Indicadores</h6>
                    <button type="button" class="btn btn-sm btn-success" onclick="generarIndicadores()">
                        <i class="bi bi-check-lg me-1"></i>Confirmar y Generar
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px;"><input type="checkbox" id="selectAllInd" checked></th>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Formula</th>
                                    <th>Meta</th>
                                    <th>Periodicidad</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="previewBodyInd"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const idCliente = <?= $cliente['id_cliente'] ?>;
    let indicadoresPreview = [];

    function showAlert(type, message) {
        document.getElementById('alertContainer').innerHTML = `<div class="alert alert-${type} alert-dismissible fade show">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
    }

    async function previewIndicadores() {
        const btn = document.getElementById('btnPreviewInd');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Cargando...';

        try {
            const response = await fetch(`<?= base_url('generador-ia') ?>/${idCliente}/preview-indicadores-pve-biomecanico`);
            const data = await response.json();

            if (data.success) {
                indicadoresPreview = data.data.indicadores || [];
                renderPreviewInd(indicadoresPreview);
                document.getElementById('previewContainer').classList.remove('d-none');
                document.getElementById('btnGenerarInd').disabled = false;
            } else {
                showAlert('danger', data.message || 'Error al cargar preview');
            }
        } catch (error) {
            showAlert('danger', 'Error: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-eye me-1"></i>Preview Indicadores';
        }
    }

    function renderPreviewInd(indicadores) {
        const tbody = document.getElementById('previewBodyInd');
        tbody.innerHTML = '';
        indicadores.forEach((ind, idx) => {
            const yaExiste = ind.ya_existe ? '<span class="badge bg-secondary">Ya existe</span>' : '<span class="badge bg-success">Nuevo</span>';
            const checked = ind.ya_existe ? '' : 'checked';
            const disabled = ind.ya_existe ? 'disabled' : '';
            tbody.innerHTML += `
                <tr class="${ind.ya_existe ? 'table-secondary' : ''}">
                    <td><input type="checkbox" class="indCheck" data-idx="${idx}" ${checked} ${disabled}></td>
                    <td><strong>${ind.nombre}</strong><br><small class="text-muted">${ind.descripcion || ''}</small></td>
                    <td><span class="badge bg-info">${ind.tipo}</span></td>
                    <td><small>${ind.formula}</small></td>
                    <td>${ind.meta} ${ind.unidad}</td>
                    <td>${ind.periodicidad}</td>
                    <td>${yaExiste}</td>
                </tr>`;
        });
    }

    document.getElementById('selectAllInd')?.addEventListener('change', function() {
        document.querySelectorAll('.indCheck:not([disabled])').forEach(cb => cb.checked = this.checked);
    });

    async function generarIndicadores() {
        const btn = document.getElementById('btnGenerarInd');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generando...';

        const seleccionados = [];
        document.querySelectorAll('.indCheck:checked').forEach(cb => {
            const idx = parseInt(cb.dataset.idx);
            if (indicadoresPreview[idx]) seleccionados.push(indicadoresPreview[idx]);
        });

        try {
            const response = await fetch(`<?= base_url('generador-ia') ?>/${idCliente}/generar-indicadores-pve-biomecanico`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ indicadores: seleccionados.length > 0 ? seleccionados : null })
            });
            const data = await response.json();

            if (data.success) {
                showAlert('success', `<i class="bi bi-check-circle me-1"></i>${data.message}`);
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert('danger', data.message || 'Error');
            }
        } catch (error) {
            showAlert('danger', 'Error: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-lightning me-1"></i>Generar Indicadores';
        }
    }
    </script>
</body>
</html>
