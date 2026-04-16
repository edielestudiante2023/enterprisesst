<?= $this->extend('layouts/base') ?>
<?= $this->section('title') ?>Funciones transversales - <?= esc($cliente['nombre_cliente'] ?? '') ?><?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php $idCliente = (int)$cliente['id_cliente']; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('consultor/dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url("perfiles-cargo/{$idCliente}") ?>">Perfiles de Cargo</a></li>
            <li class="breadcrumb-item active">Funciones transversales</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h3 class="mb-0"><i class="bi bi-list-check me-2 text-primary"></i> Funciones transversales</h3>
            <small class="text-muted">Cliente: <strong><?= esc($cliente['nombre_cliente']) ?></strong></small>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Estas funciones <strong>aplican a TODOS los perfiles de cargo</strong> de este cliente.
        Si editas un item, el cambio se refleja automaticamente en los PDFs y acuses generados despues del cambio
        (los acuses ya firmados conservan el contenido que tenian al momento de la firma).
    </div>

    <div class="row g-3">
        <!-- Columna SST -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong><i class="bi bi-shield-check text-success me-1"></i> SEGURIDAD Y SALUD EN EL TRABAJO</strong>
                        <span class="badge bg-secondary ms-2" id="count-sst"><?= count($funcionesSST) ?></span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-sst">
                        <i class="bi bi-plus"></i> Agregar
                    </button>
                </div>
                <div class="card-body">
                    <div id="lista-sst"></div>
                    <button type="button" class="btn btn-success w-100 mt-3" id="btn-guardar-sst">
                        <i class="bi bi-save"></i> Guardar funciones SST
                    </button>
                    <div id="feedback-sst" class="mt-2"></div>
                </div>
            </div>
        </div>

        <!-- Columna TH -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong><i class="bi bi-people text-primary me-1"></i> TALENTO HUMANO</strong>
                        <span class="badge bg-secondary ms-2" id="count-th"><?= count($funcionesTH) ?></span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-th">
                        <i class="bi bi-plus"></i> Agregar
                    </button>
                </div>
                <div class="card-body">
                    <div id="lista-th"></div>
                    <button type="button" class="btn btn-success w-100 mt-3" id="btn-guardar-th">
                        <i class="bi bi-save"></i> Guardar funciones TH
                    </button>
                    <div id="feedback-th" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const URL_BASE = '<?= base_url("perfiles-cargo/{$idCliente}/funciones-transversales") ?>';
let funcionesSST = <?= json_encode(array_map(fn($f) => $f['texto'], $funcionesSST)) ?>;
let funcionesTH  = <?= json_encode(array_map(fn($f) => $f['texto'], $funcionesTH))  ?>;

function renderLista(tipo) {
    const lista = tipo === 'sst' ? funcionesSST : funcionesTH;
    const cont = document.getElementById('lista-' + tipo);
    const count = document.getElementById('count-' + tipo);
    count.textContent = lista.length;

    if (lista.length === 0) {
        cont.innerHTML = '<div class="text-center text-muted py-3"><em>Sin funciones. Usa el boton "Agregar".</em></div>';
        return;
    }

    cont.innerHTML = '';
    lista.forEach((texto, i) => {
        const row = document.createElement('div');
        row.className = 'd-flex gap-2 mb-2 align-items-start';
        row.innerHTML = `
            <span class="badge bg-light text-dark border" style="min-width:32px; text-align:center; line-height:22px; margin-top:6px;">${i+1}</span>
            <textarea class="form-control form-control-sm" rows="2" data-idx="${i}">${(texto||'').replace(/</g,'&lt;').replace(/>/g,'&gt;')}</textarea>
            <div class="d-flex flex-column gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-up="${i}" ${i===0?'disabled':''} title="Subir"><i class="bi bi-arrow-up"></i></button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-down="${i}" ${i===lista.length-1?'disabled':''} title="Bajar"><i class="bi bi-arrow-down"></i></button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-del="${i}" title="Eliminar"><i class="bi bi-x"></i></button>
            </div>
        `;
        cont.appendChild(row);
    });
    // Wire events
    cont.querySelectorAll('textarea').forEach(el => {
        el.addEventListener('input', e => {
            const i = +e.target.dataset.idx;
            if (tipo === 'sst') funcionesSST[i] = e.target.value;
            else funcionesTH[i] = e.target.value;
        });
    });
    cont.querySelectorAll('[data-del]').forEach(btn => {
        btn.addEventListener('click', () => {
            const i = +btn.dataset.del;
            if (tipo === 'sst') funcionesSST.splice(i, 1);
            else funcionesTH.splice(i, 1);
            renderLista(tipo);
        });
    });
    cont.querySelectorAll('[data-up]').forEach(btn => {
        btn.addEventListener('click', () => {
            const i = +btn.dataset.up;
            if (i === 0) return;
            const arr = tipo === 'sst' ? funcionesSST : funcionesTH;
            [arr[i-1], arr[i]] = [arr[i], arr[i-1]];
            renderLista(tipo);
        });
    });
    cont.querySelectorAll('[data-down]').forEach(btn => {
        btn.addEventListener('click', () => {
            const i = +btn.dataset.down;
            const arr = tipo === 'sst' ? funcionesSST : funcionesTH;
            if (i >= arr.length - 1) return;
            [arr[i+1], arr[i]] = [arr[i], arr[i+1]];
            renderLista(tipo);
        });
    });
}

document.getElementById('btn-add-sst').addEventListener('click', () => {
    funcionesSST.push('');
    renderLista('sst');
});
document.getElementById('btn-add-th').addEventListener('click', () => {
    funcionesTH.push('');
    renderLista('th');
});

async function guardar(tipo) {
    const btn = document.getElementById('btn-guardar-' + tipo);
    const fb  = document.getElementById('feedback-' + tipo);
    const lista = tipo === 'sst' ? funcionesSST : funcionesTH;
    const items = lista.map(t => (t || '').trim()).filter(t => t !== '');

    btn.disabled = true;
    fb.innerHTML = '<div class="alert alert-info py-2 mb-0">Guardando...</div>';
    try {
        const r = await fetch(URL_BASE + '/' + tipo, {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ items })
        });
        const j = await r.json();
        if (j.ok) {
            fb.innerHTML = '<div class="alert alert-success py-2 mb-0">Guardado. Total: ' + j.total + ' funciones activas.</div>';
            // actualizar estado local a lo saneado
            if (tipo === 'sst') funcionesSST = items;
            else funcionesTH = items;
            renderLista(tipo);
        } else {
            fb.innerHTML = '<div class="alert alert-danger py-2 mb-0">Error: ' + (j.error || 'desconocido') + '</div>';
        }
        setTimeout(() => { fb.innerHTML = ''; }, 4000);
    } catch (e) {
        fb.innerHTML = '<div class="alert alert-danger py-2 mb-0">Fallo de red: ' + e.message + '</div>';
    }
    btn.disabled = false;
}
document.getElementById('btn-guardar-sst').addEventListener('click', () => guardar('sst'));
document.getElementById('btn-guardar-th').addEventListener('click', () => guardar('th'));

renderLista('sst');
renderLista('th');
</script>
<?= $this->endSection() ?>
