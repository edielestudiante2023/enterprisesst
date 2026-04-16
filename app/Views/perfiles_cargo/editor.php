<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Editor perfil - <?= esc($cargo['nombre_cargo'] ?? '') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $idCliente = (int)$cliente['id_cliente'];
    $idPerfil  = (int)$perfil['id_perfil_cargo'];
    $nombreCargo = $cargo['nombre_cargo'] ?? '';
    $funcEspecificas = is_array($perfil['funciones_especificas'] ?? null) ? $perfil['funciones_especificas'] : [];
?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('consultantDashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url("perfiles-cargo/{$idCliente}") ?>">Perfiles de Cargo</a></li>
            <li class="breadcrumb-item active"><?= esc($nombreCargo) ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="mb-0"><i class="bi bi-person-badge me-2 text-primary"></i> <?= esc($nombreCargo) ?></h3>
            <small class="text-muted">
                Cliente: <?= esc($cliente['nombre_cliente']) ?> &middot;
                Estado: <span class="badge bg-secondary"><?= esc($perfil['estado']) ?></span> &middot;
                Version <?= esc($perfil['version_actual']) ?>.0
            </small>
        </div>
        <div class="btn-group">
            <a class="btn btn-outline-primary" href="<?= base_url("perfiles-cargo/{$idCliente}/trabajadores") ?>">
                <i class="bi bi-people"></i> Trabajadores
            </a>
            <a class="btn btn-outline-primary" href="<?= base_url("perfiles-cargo/{$idPerfil}/acuses") ?>">
                <i class="bi bi-pen"></i> Acuses y firmas
            </a>
            <a class="btn btn-outline-danger" href="<?= base_url("perfiles-cargo/{$idPerfil}/pdf") ?>" target="_blank">
                <i class="bi bi-file-pdf"></i> PDF
            </a>
            <button class="btn btn-success" id="btn-guardar">
                <i class="bi bi-save"></i> Guardar
            </button>
        </div>
    </div>

    <div id="save-feedback"></div>

    <ul class="nav nav-tabs mb-3" id="editorTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-identificacion">Identificacion</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-requisitos">Requisitos</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-funciones">Funciones</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-indicadores">Indicadores</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-competencias">Competencias</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-aprobacion">Aprobacion</button></li>
    </ul>

    <div class="tab-content">

        <!-- IDENTIFICACION -->
        <div class="tab-pane fade show active" id="tab-identificacion">
            <div class="card"><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Objetivo del cargo</label>
                        <div class="input-group mb-2">
                            <textarea id="f-objetivo" class="form-control" rows="4" placeholder="Escriba o genere con IA..."><?= esc($perfil['objetivo_cargo'] ?? '') ?></textarea>
                            <button type="button" class="btn btn-warning" id="btn-ia-objetivo">
                                <i class="bi bi-magic"></i> Generar con IA
                            </button>
                        </div>
                        <small class="text-muted">Requiere al menos 1 funcion especifica cargada en la pestana Funciones.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reporta a</label>
                        <input id="f-reporta" class="form-control" value="<?= esc($perfil['reporta_a'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Colaboradores a cargo</label>
                        <input id="f-colaboradores" class="form-control" value="<?= esc($perfil['colaboradores_a_cargo'] ?? '') ?>">
                    </div>
                </div>
            </div></div>
        </div>

        <!-- REQUISITOS -->
        <div class="tab-pane fade" id="tab-requisitos">
            <div class="card"><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Edad minima</label>
                        <input id="f-edad" class="form-control" value="<?= esc($perfil['edad_min'] ?? '') ?>" placeholder="Ej: &gt;25">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado civil</label>
                        <select id="f-estado-civil" class="form-select">
                            <option value="">-</option>
                            <?php foreach (['indiferente','soltero','casado'] as $ec): ?>
                                <option value="<?= $ec ?>" <?= ($perfil['estado_civil'] ?? '') === $ec ? 'selected' : '' ?>><?= ucfirst($ec) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Genero</label>
                        <select id="f-genero" class="form-select">
                            <option value="">-</option>
                            <?php foreach (['indiferente','masculino','femenino'] as $g): ?>
                                <option value="<?= $g ?>" <?= ($perfil['genero'] ?? '') === $g ? 'selected' : '' ?>><?= ucfirst($g) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Validacion educacion / experiencia</label>
                        <textarea id="f-validacion" class="form-control" rows="3"><?= esc($perfil['validacion_educacion_experiencia'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="alert alert-info mt-3 mb-0">
                    <i class="bi bi-info-circle"></i> Formacion, conocimientos complementarios, experiencia laboral y factores de riesgo se habilitaran en proximos bloques (campos JSON estructurados).
                </div>
            </div></div>
        </div>

        <!-- FUNCIONES -->
        <div class="tab-pane fade" id="tab-funciones">
            <div class="card mb-3"><div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Funciones especificas del cargo</h6>
                    <button type="button" class="btn btn-sm btn-warning" id="btn-ia-funciones">
                        <i class="bi bi-magic"></i> Sugerir con IA
                    </button>
                </div>
                <div id="lista-funciones-especificas"></div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btn-add-funcion">
                    <i class="bi bi-plus"></i> Agregar funcion
                </button>
            </div></div>

            <div class="card mb-3"><div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h6 class="mb-0">Funciones SST (transversales del cliente)</h6>
                    <a class="btn btn-sm btn-outline-primary" target="_blank"
                       href="<?= base_url("perfiles-cargo/{$idCliente}/funciones-transversales") ?>">
                        <i class="bi bi-pencil-square"></i> Editar transversales
                    </a>
                </div>
                <small class="text-muted d-block mb-2">
                    <?= count($funcionesSST) ?> items activos. Se aplican a todos los cargos del cliente.
                </small>
                <ol class="small mb-0">
                    <?php foreach ($funcionesSST as $f): ?>
                        <li><?= esc($f['texto']) ?></li>
                    <?php endforeach; ?>
                </ol>
            </div></div>

            <div class="card"><div class="card-body">
                <h6>Funciones Talento Humano (transversales del cliente)</h6>
                <small class="text-muted d-block mb-2">
                    <?= count($funcionesTH) ?> items activos.
                </small>
                <ol class="small mb-0">
                    <?php foreach ($funcionesTH as $f): ?>
                        <li><?= esc($f['texto']) ?></li>
                    <?php endforeach; ?>
                </ol>
            </div></div>
        </div>

        <!-- INDICADORES -->
        <div class="tab-pane fade" id="tab-indicadores">
            <div class="card"><div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Indicadores del cargo</h6>
                    <button type="button" class="btn btn-sm btn-warning" id="btn-ia-indicadores">
                        <i class="bi bi-magic"></i> Generar con IA
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle" id="tabla-indicadores">
                        <thead class="table-light">
                            <tr>
                                <th>Objetivo de proceso</th>
                                <th>Nombre indicador</th>
                                <th>Formula</th>
                                <th>Periodicidad</th>
                                <th>Meta</th>
                                <th>Ponderacion</th>
                                <th>Obj. calidad</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="tbody-indicadores"></tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-add-indicador">
                    <i class="bi bi-plus"></i> Agregar indicador
                </button>
            </div></div>
        </div>

        <!-- COMPETENCIAS -->
        <div class="tab-pane fade" id="tab-competencias">
            <div class="card"><div class="card-body">
                <h6 class="mb-3">Competencias requeridas para el cargo</h6>
                <div class="mb-3">
                    <label class="form-label">Agregar competencia</label>
                    <select id="select-competencia" class="form-control" style="width:100%"></select>
                    <small class="text-muted">Busca en el Diccionario de Competencias del cliente (<?= esc($cliente['nombre_cliente']) ?>).</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle" id="tabla-competencias">
                        <thead class="table-light">
                            <tr>
                                <th style="width:45%">Competencia</th>
                                <th style="width:15%">Familia</th>
                                <th style="width:15%">Nivel requerido</th>
                                <th>Observacion</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="tbody-competencias">
                            <tr><td colspan="5" class="text-center text-muted py-3">Cargando competencias del perfil...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">
                        <a href="<?= base_url("diccionario-competencias/{$idCliente}") ?>" target="_blank">
                            <i class="bi bi-box-arrow-up-right"></i> Administrar Diccionario del cliente
                        </a>
                    </small>
                    <button type="button" class="btn btn-success btn-sm" id="btn-guardar-competencias">
                        <i class="bi bi-save"></i> Guardar competencias
                    </button>
                </div>
                <div id="competencias-feedback" class="mt-2"></div>
            </div></div>
        </div>

        <!-- APROBACION -->
        <div class="tab-pane fade" id="tab-aprobacion">
            <div class="card"><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del aprobador</label>
                        <input id="f-aprob-nombre" class="form-control" value="<?= esc($perfil['aprobador_nombre'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cargo del aprobador</label>
                        <input id="f-aprob-cargo" class="form-control" value="<?= esc($perfil['aprobador_cargo'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cedula del aprobador</label>
                        <input id="f-aprob-cedula" class="form-control" value="<?= esc($perfil['aprobador_cedula'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha de aprobacion</label>
                        <input type="date" id="f-fecha-aprob" class="form-control" value="<?= esc($perfil['fecha_aprobacion'] ?? '') ?>">
                    </div>
                </div>
                <hr>
                <h6 class="mt-3">Firma del aprobador</h6>
                <p class="small text-muted mb-2">Al confirmar la firma se marca el perfil como <strong>aprobado</strong>. Posteriormente puede generar los acuses para los trabajadores.</p>

                <?php if (!empty($perfil['firma_aprobador_base64'])): ?>
                <div class="alert alert-success small py-2 mb-3">
                    <i class="bi bi-check-circle"></i> Ya hay una firma guardada. Al confirmar una nueva se reemplaza.
                    <div class="mt-2">
                        <img src="data:image/png;base64,<?= esc($perfil['firma_aprobador_base64']) ?>"
                             style="max-width:280px; max-height:100px; border:1px solid #ccc; background:#fff;">
                    </div>
                </div>
                <?php endif; ?>

                <ul class="nav nav-pills nav-sm mb-2" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active btn-sm" data-bs-toggle="pill" data-bs-target="#sig-modo-dibujar" type="button">
                            <i class="bi bi-pencil"></i> Dibujar
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link btn-sm" data-bs-toggle="pill" data-bs-target="#sig-modo-subir" type="button">
                            <i class="bi bi-upload"></i> Subir archivo
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="sig-modo-dibujar">
                        <canvas id="sig-aprobador"
                                style="border:2px dashed #aaa; background:#fff; border-radius:6px; display:block; width:100%; max-width:500px; height:150px; touch-action:none;"></canvas>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="btn-limpiar-sig">
                            <i class="bi bi-eraser"></i> Limpiar
                        </button>
                    </div>
                    <div class="tab-pane fade" id="sig-modo-subir">
                        <div class="p-3 border rounded bg-light">
                            <label class="form-label small mb-1">Seleccione archivo PNG o JPG con la firma</label>
                            <input type="file" id="sig-file" accept="image/png,image/jpeg" class="form-control form-control-sm">
                            <small class="text-muted d-block mt-1">Tamano maximo 3 MB. Idealmente con fondo transparente (PNG).</small>
                            <div id="sig-file-preview" class="mt-3" style="display:none;">
                                <p class="small mb-1"><strong>Preview:</strong></p>
                                <img id="sig-file-img" src="" style="max-width:280px; max-height:120px; border:1px solid #ccc; background:#fff;">
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="btn-quitar-archivo">
                                    <i class="bi bi-x"></i> Quitar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-success" id="btn-firmar-aprobador">
                        <i class="bi bi-check2-circle"></i> Aprobar y firmar
                    </button>
                </div>
                <div id="aprobador-feedback" class="mt-2"></div>
            </div></div>
        </div>

    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Select2 JS cargado aqui para garantizar que jQuery (base layout) ya esta disponible -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const ID_PERFIL = <?= $idPerfil ?>;
const ID_CLIENTE = <?= $idCliente ?>;
const NOMBRE_CARGO = <?= json_encode($nombreCargo) ?>;
const URL_BASE = '<?= base_url('perfiles-cargo') ?>';

let funcionesEspecificas = <?= json_encode($funcEspecificas) ?>;
let indicadores = <?= json_encode($indicadores) ?>;
let indicadoresGeneradosIA = false;

function renderFunciones() {
    const cont = document.getElementById('lista-funciones-especificas');
    cont.innerHTML = '';
    funcionesEspecificas.forEach((texto, i) => {
        const row = document.createElement('div');
        row.className = 'input-group input-group-sm mb-2';
        row.innerHTML = `
            <span class="input-group-text">${i+1}</span>
            <input class="form-control" value="${(texto||'').replace(/"/g,'&quot;')}" data-idx="${i}">
            <button class="btn btn-outline-danger" type="button" data-del="${i}"><i class="bi bi-x"></i></button>
        `;
        cont.appendChild(row);
    });
    cont.querySelectorAll('input').forEach(inp => {
        inp.addEventListener('input', e => {
            funcionesEspecificas[+e.target.dataset.idx] = e.target.value;
        });
    });
    cont.querySelectorAll('[data-del]').forEach(btn => {
        btn.addEventListener('click', e => {
            funcionesEspecificas.splice(+btn.dataset.del, 1);
            renderFunciones();
        });
    });
}
document.getElementById('btn-add-funcion').addEventListener('click', () => {
    funcionesEspecificas.push('');
    renderFunciones();
});

function renderIndicadores() {
    const tb = document.getElementById('tbody-indicadores');
    tb.innerHTML = '';
    indicadores.forEach((ind, i) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><textarea class="form-control form-control-sm" rows="2" data-idx="${i}" data-f="objetivo_proceso">${ind.objetivo_proceso||''}</textarea></td>
            <td><input class="form-control form-control-sm" data-idx="${i}" data-f="nombre_indicador" value="${(ind.nombre_indicador||'').replace(/"/g,'&quot;')}"></td>
            <td><input class="form-control form-control-sm" data-idx="${i}" data-f="formula" value="${(ind.formula||'').replace(/"/g,'&quot;')}"></td>
            <td>
                <select class="form-select form-select-sm" data-idx="${i}" data-f="periodicidad">
                    ${['mensual','bimestral','trimestral','semestral','anual'].map(p =>
                        `<option value="${p}" ${ind.periodicidad===p?'selected':''}>${p}</option>`).join('')}
                </select>
            </td>
            <td><input class="form-control form-control-sm" data-idx="${i}" data-f="meta" value="${(ind.meta||'').replace(/"/g,'&quot;')}"></td>
            <td><input class="form-control form-control-sm" data-idx="${i}" data-f="ponderacion" value="${(ind.ponderacion||'').replace(/"/g,'&quot;')}"></td>
            <td><input class="form-control form-control-sm" data-idx="${i}" data-f="objetivo_calidad_impacta" value="${(ind.objetivo_calidad_impacta||'').replace(/"/g,'&quot;')}"></td>
            <td><button class="btn btn-sm btn-outline-danger" type="button" data-del="${i}"><i class="bi bi-x"></i></button></td>
        `;
        tb.appendChild(tr);
    });
    tb.querySelectorAll('[data-idx]').forEach(el => {
        el.addEventListener('input', e => {
            const i = +e.target.dataset.idx;
            const f = e.target.dataset.f;
            indicadores[i][f] = e.target.value;
        });
    });
    tb.querySelectorAll('[data-del]').forEach(btn => {
        btn.addEventListener('click', () => {
            indicadores.splice(+btn.dataset.del, 1);
            renderIndicadores();
        });
    });
}
document.getElementById('btn-add-indicador').addEventListener('click', () => {
    indicadores.push({ objetivo_proceso:'', nombre_indicador:'', formula:'', periodicidad:'mensual', meta:'', ponderacion:'', objetivo_calidad_impacta:'' });
    renderIndicadores();
});

// IA: objetivo
document.getElementById('btn-ia-objetivo').addEventListener('click', async () => {
    if (funcionesEspecificas.filter(f => (f||'').trim()).length === 0) {
        alert('Agregue primero al menos una funcion especifica en la pestana Funciones.');
        return;
    }
    const btn = document.getElementById('btn-ia-objetivo');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generando...';
    try {
        const r = await fetch(URL_BASE + '/ia/objetivo', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ nombre_cargo: NOMBRE_CARGO, funciones: funcionesEspecificas, area: '' })
        });
        const j = await r.json();
        if (j.ok) { document.getElementById('f-objetivo').value = j.objetivo; }
        else alert('Error IA: ' + (j.error || 'desconocido'));
    } catch (e) { alert('Fallo de red: ' + e.message); }
    btn.disabled = false; btn.innerHTML = '<i class="bi bi-magic"></i> Generar con IA';
});

// IA: funciones
document.getElementById('btn-ia-funciones').addEventListener('click', async () => {
    const btn = document.getElementById('btn-ia-funciones');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    try {
        const r = await fetch(URL_BASE + '/ia/funciones', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ nombre_cargo: NOMBRE_CARGO, area: '', objetivo: document.getElementById('f-objetivo').value })
        });
        const j = await r.json();
        if (j.ok && Array.isArray(j.funciones)) {
            if (funcionesEspecificas.length > 0 && !confirm('Reemplazar las funciones actuales por las sugeridas por IA?')) {
                // agregar al final
                funcionesEspecificas = funcionesEspecificas.concat(j.funciones);
            } else {
                funcionesEspecificas = j.funciones;
            }
            renderFunciones();
        } else alert('Error IA: ' + (j.error || 'desconocido'));
    } catch (e) { alert('Fallo de red: ' + e.message); }
    btn.disabled = false; btn.innerHTML = '<i class="bi bi-magic"></i> Sugerir con IA';
});

// IA: indicadores
document.getElementById('btn-ia-indicadores').addEventListener('click', async () => {
    if (funcionesEspecificas.filter(f => (f||'').trim()).length === 0) {
        alert('Agregue primero al menos una funcion especifica.');
        return;
    }
    const btn = document.getElementById('btn-ia-indicadores');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    try {
        const r = await fetch(URL_BASE + '/ia/indicadores', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ nombre_cargo: NOMBRE_CARGO, funciones: funcionesEspecificas, objetivo: document.getElementById('f-objetivo').value })
        });
        const j = await r.json();
        if (j.ok && Array.isArray(j.indicadores)) {
            indicadores = j.indicadores;
            indicadoresGeneradosIA = true;
            renderIndicadores();
        } else alert('Error IA: ' + (j.error || 'desconocido'));
    } catch (e) { alert('Fallo de red: ' + e.message); }
    btn.disabled = false; btn.innerHTML = '<i class="bi bi-magic"></i> Generar con IA';
});

// GUARDAR
document.getElementById('btn-guardar').addEventListener('click', async () => {
    const payload = {
        objetivo_cargo: document.getElementById('f-objetivo').value,
        reporta_a: document.getElementById('f-reporta').value,
        colaboradores_a_cargo: document.getElementById('f-colaboradores').value,
        edad_min: document.getElementById('f-edad').value,
        estado_civil: document.getElementById('f-estado-civil').value,
        genero: document.getElementById('f-genero').value,
        validacion_educacion_experiencia: document.getElementById('f-validacion').value,
        aprobador_nombre: document.getElementById('f-aprob-nombre').value,
        aprobador_cargo: document.getElementById('f-aprob-cargo').value,
        aprobador_cedula: document.getElementById('f-aprob-cedula').value,
        fecha_aprobacion: document.getElementById('f-fecha-aprob').value,
        funciones_especificas: funcionesEspecificas.filter(f => (f||'').trim() !== ''),
        indicadores: indicadores,
        indicadores_generados_ia: indicadoresGeneradosIA
    };
    const fb = document.getElementById('save-feedback');
    fb.innerHTML = '<div class="alert alert-info py-2">Guardando...</div>';
    try {
        const r = await fetch(URL_BASE + '/' + ID_PERFIL + '/guardar', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(payload)
        });
        const j = await r.json();
        if (j.ok) fb.innerHTML = '<div class="alert alert-success py-2">Guardado correctamente.</div>';
        else fb.innerHTML = '<div class="alert alert-danger py-2">Error: ' + (j.error || 'desconocido') + '</div>';
        setTimeout(() => { fb.innerHTML = ''; }, 3000);
    } catch (e) {
        fb.innerHTML = '<div class="alert alert-danger py-2">Fallo de red: ' + e.message + '</div>';
    }
});

renderFunciones();
renderIndicadores();

/* ============================================================
 * COMPETENCIAS (Select2 + tabla editable)
 * ============================================================ */
let competenciasSeleccionadas = []; // [{id_competencia, nombre, familia, nivel_requerido, observacion}]

function renderCompetencias() {
    const tb = document.getElementById('tbody-competencias');
    if (competenciasSeleccionadas.length === 0) {
        tb.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Aun no hay competencias asignadas. Busque y seleccione una arriba.</td></tr>';
        return;
    }
    tb.innerHTML = '';
    competenciasSeleccionadas.forEach((c, i) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>${(c.nombre||'').replace(/</g,'&lt;')}</strong></td>
            <td><small class="text-muted">${(c.familia||'').replace(/</g,'&lt;')}</small></td>
            <td>
                <select class="form-select form-select-sm" data-idx="${i}" data-f="nivel_requerido">
                    ${[1,2,3,4,5].map(n => `<option value="${n}" ${c.nivel_requerido==n?'selected':''}>${n}</option>`).join('')}
                </select>
            </td>
            <td><input class="form-control form-control-sm" data-idx="${i}" data-f="observacion" value="${(c.observacion||'').replace(/"/g,'&quot;')}"></td>
            <td><button class="btn btn-sm btn-outline-danger" type="button" data-del-comp="${i}"><i class="bi bi-x"></i></button></td>
        `;
        tb.appendChild(tr);
    });
    tb.querySelectorAll('[data-idx]').forEach(el => {
        el.addEventListener('input', e => {
            const i = +e.target.dataset.idx;
            const f = e.target.dataset.f;
            competenciasSeleccionadas[i][f] = e.target.value;
        });
        el.addEventListener('change', e => {
            const i = +e.target.dataset.idx;
            const f = e.target.dataset.f;
            competenciasSeleccionadas[i][f] = e.target.value;
        });
    });
    tb.querySelectorAll('[data-del-comp]').forEach(btn => {
        btn.addEventListener('click', () => {
            competenciasSeleccionadas.splice(+btn.dataset.delComp, 1);
            renderCompetencias();
        });
    });
}

// Cargar competencias ya asignadas al perfil
async function cargarCompetenciasPerfil() {
    const tb = document.getElementById('tbody-competencias');
    try {
        const r = await fetch(URL_BASE + '/' + ID_PERFIL + '/competencias', {
            credentials: 'same-origin',
            headers: {'Accept': 'application/json'}
        });
        if (!r.ok) {
            tb.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3">Error HTTP ' + r.status + ' al cargar competencias.</td></tr>';
            return;
        }
        const ct = r.headers.get('content-type') || '';
        if (!ct.includes('application/json')) {
            tb.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3">Respuesta inesperada del servidor (no es JSON). Verifique la sesion.</td></tr>';
            return;
        }
        const j = await r.json();
        if (!j.ok) {
            tb.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3">Error: ' + (j.error || 'desconocido') + '</td></tr>';
            return;
        }
        const items = Array.isArray(j.items) ? j.items : [];
        competenciasSeleccionadas = items.map(it => ({
            id_competencia:  parseInt(it.id_competencia),
            nombre:          it.nombre || '',
            familia:         it.familia || '',
            codigo:          it.codigo || '',
            nivel_requerido: parseInt(it.nivel_requerido) || 3,
            observacion:     it.observacion || ''
        }));
        renderCompetencias();
    } catch (e) {
        console.error('Error cargando competencias', e);
        tb.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3">Fallo de red: ' + e.message + '</td></tr>';
    }
}

// Select2 con AJAX al diccionario del cliente
jQuery(function($) {
    $('#select-competencia').select2({
        placeholder: 'Buscar competencia en el diccionario del cliente...',
        allowClear: true,
        ajax: {
            url: URL_BASE + '/' + ID_CLIENTE + '/competencias/buscar',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term || '' }),
            processResults: data => ({
                results: (data.results || []).map(r => ({
                    id: r.id, text: r.text, familia: r.familia
                }))
            }),
            cache: true
        },
        minimumInputLength: 0,
        width: '100%'
    });

    $('#select-competencia').on('select2:select', function(e) {
        const d = e.params.data;
        // Evitar duplicados
        if (competenciasSeleccionadas.some(c => c.id_competencia === parseInt(d.id))) {
            alert('Esa competencia ya esta en la lista.');
        } else {
            competenciasSeleccionadas.push({
                id_competencia:  parseInt(d.id),
                nombre:          d.text.replace(/^\[[^\]]+\]\s*/, ''),
                familia:         d.familia || '',
                nivel_requerido: 3,
                observacion:     ''
            });
            renderCompetencias();
        }
        $('#select-competencia').val(null).trigger('change');
    });
});

// Guardar competencias
document.getElementById('btn-guardar-competencias').addEventListener('click', async () => {
    const fb = document.getElementById('competencias-feedback');
    const btn = document.getElementById('btn-guardar-competencias');
    btn.disabled = true;
    fb.innerHTML = '<div class="alert alert-info py-2 mb-0">Guardando...</div>';
    try {
        const r = await fetch(URL_BASE + '/' + ID_PERFIL + '/competencias/guardar', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                items: competenciasSeleccionadas.map((c, i) => ({
                    id_competencia:  c.id_competencia,
                    nivel_requerido: parseInt(c.nivel_requerido),
                    observacion:     c.observacion,
                    orden:           i + 1
                }))
            })
        });
        const text = await r.text();
        let j;
        try { j = JSON.parse(text); } catch(_) {
            fb.innerHTML = '<div class="alert alert-danger py-2 mb-0">Error servidor (HTTP ' + r.status + '): ' + text.substring(0, 300) + '</div>';
            btn.disabled = false; return;
        }
        if (j.ok) fb.innerHTML = '<div class="alert alert-success py-2 mb-0">Guardadas ' + j.total + ' competencias.</div>';
        else fb.innerHTML = '<div class="alert alert-danger py-2 mb-0">Error: ' + (j.error || j.detail || j.message || JSON.stringify(j.messages) || 'desconocido') + '</div>';
        setTimeout(() => { fb.innerHTML = ''; }, 3000);
    } catch (e) {
        fb.innerHTML = '<div class="alert alert-danger py-2 mb-0">Fallo de red: ' + e.message + '</div>';
    }
    btn.disabled = false;
});

cargarCompetenciasPerfil();

/* ============================================================
 * Firma del aprobador — modos: dibujar canvas O subir archivo
 * ============================================================ */
(function(){
    const canvas = document.getElementById('sig-aprobador');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    // Variable que guarda la firma capturada por upload (si existe)
    let firmaUploadedBase64 = null;

    function fit() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width; canvas.height = rect.height;
        ctx.strokeStyle = '#000'; ctx.lineWidth = 2; ctx.lineCap='round';
    }
    const tabAprobBtn = document.querySelector('[data-bs-target="#tab-aprobacion"]');
    if (tabAprobBtn) tabAprobBtn.addEventListener('shown.bs.tab', fit);
    setTimeout(fit, 300);
    window.addEventListener('resize', fit);

    // ---- Canvas dibujar ----
    let drawing = false;
    function pos(e) {
        const rect = canvas.getBoundingClientRect();
        const p = e.touches ? e.touches[0] : e;
        return { x: p.clientX - rect.left, y: p.clientY - rect.top };
    }
    function start(e) { drawing = true; const p = pos(e); ctx.beginPath(); ctx.moveTo(p.x,p.y); e.preventDefault(); }
    function move(e)  { if (!drawing) return; const p = pos(e); ctx.lineTo(p.x,p.y); ctx.stroke(); e.preventDefault(); }
    function end()    { drawing = false; }
    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    canvas.addEventListener('mouseup', end);
    canvas.addEventListener('mouseleave', end);
    canvas.addEventListener('touchstart', start);
    canvas.addEventListener('touchmove', move);
    canvas.addEventListener('touchend', end);

    document.getElementById('btn-limpiar-sig').addEventListener('click', () => {
        ctx.clearRect(0,0,canvas.width,canvas.height);
    });

    // ---- Upload archivo ----
    const fileInput  = document.getElementById('sig-file');
    const previewBox = document.getElementById('sig-file-preview');
    const previewImg = document.getElementById('sig-file-img');

    fileInput.addEventListener('change', e => {
        const f = e.target.files[0];
        if (!f) {
            firmaUploadedBase64 = null;
            previewBox.style.display = 'none';
            return;
        }
        if (f.size > 3 * 1024 * 1024) {
            alert('Archivo demasiado grande. Maximo 3 MB.');
            fileInput.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = ev => {
            firmaUploadedBase64 = ev.target.result; // data:image/...;base64,...
            previewImg.src = firmaUploadedBase64;
            previewBox.style.display = 'block';
        };
        reader.onerror = () => {
            alert('No se pudo leer el archivo.');
            firmaUploadedBase64 = null;
        };
        reader.readAsDataURL(f);
    });

    document.getElementById('btn-quitar-archivo').addEventListener('click', () => {
        fileInput.value = '';
        firmaUploadedBase64 = null;
        previewBox.style.display = 'none';
    });

    // ---- Aprobar y firmar ----
    document.getElementById('btn-firmar-aprobador').addEventListener('click', async () => {
        const nombre = document.getElementById('f-aprob-nombre').value.trim();
        const cargo  = document.getElementById('f-aprob-cargo').value.trim();
        const cedula = document.getElementById('f-aprob-cedula').value.trim();
        if (!nombre) { alert('Complete el nombre del aprobador.'); return; }
        if (!cargo)  { alert('Complete el cargo del aprobador.'); return; }

        // Determinar fuente de firma: upload tiene prioridad si hay archivo cargado
        let firmaBase64 = null;

        if (firmaUploadedBase64) {
            firmaBase64 = firmaUploadedBase64;
        } else {
            // Canvas: detectar si el usuario dibujo algo
            const blank = document.createElement('canvas');
            blank.width = canvas.width; blank.height = canvas.height;
            if (canvas.toDataURL() === blank.toDataURL()) {
                alert('Por favor firme en el recuadro o suba un archivo con la firma.');
                return;
            }
            firmaBase64 = canvas.toDataURL('image/png');
        }

        const fb  = document.getElementById('aprobador-feedback');
        const btn = document.getElementById('btn-firmar-aprobador');
        btn.disabled = true;
        fb.innerHTML = '<div class="alert alert-info py-2 mb-0"><span class="spinner-border spinner-border-sm"></span> Guardando firma...</div>';

        try {
            const r = await fetch(URL_BASE + '/' + ID_PERFIL + '/aprobador/firmar', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type':'application/json', 'Accept':'application/json'},
                body: JSON.stringify({ nombre, cargo, cedula, firma_base64: firmaBase64 })
            });
            const ct = r.headers.get('content-type') || '';
            if (!ct.includes('application/json')) {
                fb.innerHTML = '<div class="alert alert-danger py-2 mb-0">Respuesta inesperada del servidor (HTTP ' + r.status + '). Verifique la sesion.</div>';
                btn.disabled = false;
                return;
            }
            const j = await r.json();
            if (j.ok) {
                fb.innerHTML = '<div class="alert alert-success py-2 mb-0"><i class="bi bi-check-circle"></i> Firma guardada. Perfil aprobado. Recargando...</div>';
                setTimeout(() => location.reload(), 1500);
            } else {
                fb.innerHTML = '<div class="alert alert-danger py-2 mb-0"><i class="bi bi-x-circle"></i> ' + (j.error || 'Error desconocido') + '</div>';
                btn.disabled = false;
            }
        } catch (e) {
            fb.innerHTML = '<div class="alert alert-danger py-2 mb-0">Fallo de red: ' + e.message + '</div>';
            btn.disabled = false;
        }
    });
})();
</script>
<?= $this->endSection() ?>
