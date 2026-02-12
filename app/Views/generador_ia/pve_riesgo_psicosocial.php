<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador PVE Riesgo Psicosocial - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #6f42c1">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="#">
                <i class="bi bi-brain me-2"></i>Generador IA - PVE Riesgo Psicosocial
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">PVE de Riesgo Psicosocial</h4>
                <p class="text-muted mb-0">Estandar 4.2.3 - Resolucion 0312/2019</p>
            </div>
            <div>
                <span class="badge bg-info fs-6"><?= $anio ?></span>
            </div>
        </div>

        <div id="alertContainer"></div>

        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Flujo de generacion del PVE de Riesgo Psicosocial:</strong>
            <ol class="mb-0 mt-2">
                <li><strong>Actividades PVE Psicosocial</strong> - Se generan actividades de prevencion de riesgo psicosocial en el PTA (aplicacion bateria, talleres manejo del estres, intervencion clima organizacional)</li>
                <li><strong>Indicadores PVE Psicosocial</strong> - Se configuran indicadores para medir el programa</li>
                <li><strong>Documento del PVE</strong> - Se genera el documento formal con datos de la BD</li>
            </ol>
        </div>

        <!-- Contexto del Cliente -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-cpu text-primary me-2"></i>Contexto para la IA</h6>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#collapseContexto">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>
            <div class="collapse show" id="collapseContexto">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3"><i class="bi bi-building me-1"></i>Datos de la Empresa</h6>
                            <table class="table table-sm table-borderless">
                                <tr><td class="text-muted" style="width:40%">Actividad economica:</td><td><strong><?= esc($contexto['actividad_economica_principal'] ?? 'No definida') ?></strong></td></tr>
                                <tr><td class="text-muted">Codigo CIIU:</td><td><?= esc($contexto['codigo_ciiu_principal'] ?? 'N/A') ?></td></tr>
                                <tr>
                                    <td class="text-muted">Nivel de riesgo ARL:</td>
                                    <td>
                                        <?php $riesgo = $contexto['nivel_riesgo_arl'] ?? 'N/A'; $colorRiesgo = match($riesgo) { 'I','II' => 'success', 'III' => 'warning', 'IV','V' => 'danger', default => 'secondary' }; ?>
                                        <span class="badge bg-<?= $colorRiesgo ?>"><?= $riesgo ?></span>
                                    </td>
                                </tr>
                                <tr><td class="text-muted">Total trabajadores:</td><td><strong><?= $contexto['total_trabajadores'] ?? 'No definido' ?></strong></td></tr>
                                <tr><td class="text-muted">Estandares aplicables:</td><td><span class="badge bg-info"><?= $contexto['estandares_aplicables'] ?? '60' ?> estandares</span></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3"><i class="bi bi-graph-up me-1"></i>Estado Actual</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width:50%">Actividades PVE existentes:</td>
                                    <td>
                                        <span class="badge bg-<?= ($resumenActividades['total'] ?? 0) > 0 ? 'success' : 'warning' ?>">
                                            <?= $resumenActividades['total'] ?? 0 ?> actividades
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Actividades sugeridas:</td>
                                    <td><span class="badge bg-info"><?= $resumenActividades['sugeridas'] ?? 12 ?></span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actividades Existentes -->
        <?php if (!empty($actividadesExistentes)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-list-check text-success me-2"></i>Actividades PVE Psicosocial en el PTA (<?= count($actividadesExistentes) ?>)</h6>
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
            <div class="card-header" style="background-color: rgba(111, 66, 193, 0.15)">
                <h6 class="mb-0"><i class="bi bi-magic me-2"></i>Generar Actividades PVE Psicosocial</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">Se generaran actividades de prevencion de riesgo psicosocial adaptadas al contexto de la empresa.</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" id="btnPreview" onclick="previewActividades()">
                        <i class="bi bi-eye me-1"></i>Preview Actividades
                    </button>
                    <button type="button" class="btn text-white" style="background-color: #6f42c1" id="btnGenerar" onclick="generarActividades()" disabled>
                        <i class="bi bi-lightning me-1"></i>Generar en PTA
                    </button>
                    <a href="<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/indicadores-pve-psicosocial') ?>" class="btn btn-outline-success ms-auto">
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
            const response = await fetch(`<?= base_url('generador-ia') ?>/${idCliente}/preview-actividades-pve-psicosocial?anio=${anio}`);
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
            const response = await fetch(`<?= base_url('generador-ia') ?>/${idCliente}/generar-actividades-pve-psicosocial`, {
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
