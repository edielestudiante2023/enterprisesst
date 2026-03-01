<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Editar Secciones - <?= esc($documento['codigo'] ?? '') ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .seccion-card { border-left: 4px solid #1c2437; }
    .seccion-card .card-header { background: #f8f9fa; cursor: pointer; }
    .seccion-card .card-header:hover { background: #e9ecef; }
    .seccion-textarea { font-family: 'Courier New', monospace; font-size: 0.85rem; line-height: 1.5; }
    .char-count { font-size: 0.75rem; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);">
                <div class="card-body text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="bi bi-pencil-square me-2"></i>Editar Secciones
                            </h4>
                            <small class="opacity-75">
                                <?= esc($documento['nombre_cliente'] ?? '') ?> ·
                                <strong><?= esc($documento['codigo'] ?? '') ?></strong> ·
                                <?= esc($documento['titulo'] ?? '') ?> ·
                                v<?= esc($versionVigente['version_texto'] ?? ($documento['version'] ?? '1') . '.0') ?>
                                <span class="badge bg-<?= ($documento['estado'] === 'aprobado') ? 'success' : (($documento['estado'] === 'firmado') ? 'info' : 'secondary') ?> ms-2"><?= esc($documento['estado']) ?></span>
                            </small>
                        </div>
                        <a href="<?= site_url('admin/editor-secciones') ?>" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Volver al listado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i><?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i><?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Advertencia -->
    <div class="alert alert-warning d-flex align-items-center mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
        <div>
            <strong>Edición directa.</strong> Los cambios se aplican al documento y su snapshot vigente sin crear nueva versión.
            <?php if ($versionVigente): ?>
                Se actualizará el snapshot de la versión <strong><?= esc($versionVigente['version_texto']) ?></strong> (vigente).
            <?php else: ?>
                <span class="text-danger">No hay versión vigente — solo se actualizará el documento.</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Buscador de texto -->
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="row align-items-center g-2">
                <div class="col">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="buscadorTexto" class="form-control" placeholder="Buscar texto en todas las secciones... (ej: 66.67%, incapacidad)">
                    </div>
                </div>
                <div class="col-auto">
                    <span id="resultadoBusqueda" class="badge bg-secondary">0 coincidencias</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary ms-1" id="btnPrevMatch" disabled title="Anterior"><i class="bi bi-chevron-up"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnNextMatch" disabled title="Siguiente"><i class="bi bi-chevron-down"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-1" id="btnLimpiarBusqueda" title="Limpiar"><i class="bi bi-x-lg"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de secciones -->
    <form action="<?= site_url('admin/editor-secciones/update/' . $documento['id_documento']) ?>" method="post">
        <?= csrf_field() ?>

        <?php if (empty($secciones)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-1"></i>Este documento no tiene secciones en su contenido JSON.
            </div>
        <?php else: ?>
            <?php foreach ($secciones as $idx => $seccion): ?>
                <?php
                    $key = $seccion['key'] ?? 'seccion_' . $idx;
                    $titulo = $seccion['titulo'] ?? 'Sección ' . ($idx + 1);
                    $contenidoSeccion = $seccion['contenido'] ?? '';
                    // Si el contenido es un array (tabla dinámica), mostrar como JSON
                    if (is_array($contenidoSeccion)) {
                        $contenidoSeccion = json_encode($contenidoSeccion, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    }
                    $chars = mb_strlen($contenidoSeccion);
                ?>
                <div class="card seccion-card shadow-sm mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center py-2" data-bs-toggle="collapse" data-bs-target="#seccion_<?= $idx ?>">
                        <div>
                            <strong><?= esc($titulo) ?></strong>
                            <code class="ms-2 small text-muted"><?= esc($key) ?></code>
                        </div>
                        <div>
                            <span class="badge bg-dark char-count" id="count_<?= $idx ?>"><?= number_format($chars) ?> chars</span>
                            <i class="bi bi-chevron-down ms-2"></i>
                        </div>
                    </div>
                    <div class="collapse show" id="seccion_<?= $idx ?>">
                        <div class="card-body p-2">
                            <textarea name="secciones[<?= esc($key) ?>]"
                                      class="form-control seccion-textarea"
                                      rows="10"
                                      data-idx="<?= $idx ?>"
                                      oninput="updateCount(this, <?= $idx ?>)"><?= esc($contenidoSeccion) ?></textarea>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Botones -->
            <div class="d-flex justify-content-between mt-4 mb-5">
                <a href="<?= site_url('admin/editor-secciones') ?>" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                </button>
            </div>
        <?php endif; ?>
    </form>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function updateCount(textarea, idx) {
    var count = textarea.value.length;
    document.getElementById('count_' + idx).textContent = count.toLocaleString() + ' chars';
}

// Buscador de texto en secciones
(function() {
    var input = document.getElementById('buscadorTexto');
    var badge = document.getElementById('resultadoBusqueda');
    var btnNext = document.getElementById('btnNextMatch');
    var btnPrev = document.getElementById('btnPrevMatch');
    var btnClear = document.getElementById('btnLimpiarBusqueda');
    var matches = [];
    var currentMatch = -1;

    function buscar() {
        var query = input.value.trim().toLowerCase();
        matches = [];
        currentMatch = -1;

        // Limpiar highlights previos
        document.querySelectorAll('.seccion-card').forEach(function(card) {
            card.style.border = '';
        });

        if (!query) {
            badge.textContent = '0 coincidencias';
            badge.className = 'badge bg-secondary';
            btnNext.disabled = true;
            btnPrev.disabled = true;
            return;
        }

        document.querySelectorAll('.seccion-textarea').forEach(function(textarea) {
            var contenido = textarea.value.toLowerCase();
            if (contenido.indexOf(query) !== -1) {
                var idx = textarea.getAttribute('data-idx');
                matches.push({ textarea: textarea, idx: idx });
                // Expandir la sección colapsada
                var collapse = document.getElementById('seccion_' + idx);
                if (collapse && !collapse.classList.contains('show')) {
                    new bootstrap.Collapse(collapse, { show: true });
                }
                // Highlight la card
                textarea.closest('.seccion-card').style.border = '2px solid #e44d26';
            }
        });

        badge.textContent = matches.length + ' coincidencia' + (matches.length !== 1 ? 's' : '');
        badge.className = matches.length > 0 ? 'badge bg-success' : 'badge bg-danger';
        btnNext.disabled = matches.length === 0;
        btnPrev.disabled = matches.length === 0;

        if (matches.length > 0) irA(0);
    }

    function irA(idx) {
        currentMatch = idx;
        var m = matches[idx];
        m.textarea.focus();
        // Buscar posición del texto y seleccionarlo
        var pos = m.textarea.value.toLowerCase().indexOf(input.value.trim().toLowerCase());
        if (pos !== -1) {
            m.textarea.setSelectionRange(pos, pos + input.value.trim().length);
        }
        m.textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
        badge.textContent = (idx + 1) + '/' + matches.length;
    }

    var timer;
    input.addEventListener('input', function() {
        clearTimeout(timer);
        timer = setTimeout(buscar, 300);
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (matches.length > 0) {
                irA((currentMatch + 1) % matches.length);
            }
        }
    });

    btnNext.addEventListener('click', function() {
        if (matches.length > 0) irA((currentMatch + 1) % matches.length);
    });

    btnPrev.addEventListener('click', function() {
        if (matches.length > 0) irA((currentMatch - 1 + matches.length) % matches.length);
    });

    btnClear.addEventListener('click', function() {
        input.value = '';
        buscar();
        input.focus();
    });
})();
</script>
<?= $this->endSection() ?>
