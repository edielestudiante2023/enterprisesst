<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indicadores PVE Psicosocial - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #6f42c1">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="#">
                <i class="bi bi-brain me-2"></i>Indicadores PVE Riesgo Psicosocial
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-building me-1"></i><?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a href="<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/pve-riesgo-psicosocial') ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver a Parte 1
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Indicadores del PVE de Riesgo Psicosocial</h4>
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
                        <h3 class="text-info"><?= $resumenIndicadores['sugeridos'] ?? 7 ?></h3>
                        <small class="text-muted">Sugeridos</small>
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

        <!-- Indicadores Existentes -->
        <?php if (!empty($indicadoresExistentes)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-graph-up text-success me-2"></i>Indicadores PVE Psicosocial (<?= count($indicadoresExistentes) ?>)</h6>
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
            <div class="card-header" style="background-color: rgba(111, 66, 193, 0.15)">
                <h6 class="mb-0"><i class="bi bi-magic me-2"></i>Generar Indicadores</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">Se generaran indicadores para medir el PVE de riesgo psicosocial.</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" id="btnPreviewInd" onclick="previewIndicadores()">
                        <i class="bi bi-eye me-1"></i>Preview Indicadores
                    </button>
                    <button type="button" class="btn text-white" style="background-color: #6f42c1" id="btnGenerarInd" onclick="generarIndicadores()" disabled>
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
            const response = await fetch(`<?= base_url('generador-ia') ?>/${idCliente}/preview-indicadores-pve-psicosocial`);
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
            const response = await fetch(`<?= base_url('generador-ia') ?>/${idCliente}/generar-indicadores-pve-psicosocial`, {
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
