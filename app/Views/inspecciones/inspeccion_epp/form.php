<?php
$isEdit = !empty($inspeccion);
$ctx = $contexto ?? 'consultor';
$baseUrl = $ctx === 'miembro' ? 'miembro/inspecciones/inspeccion-epp' : 'inspecciones/inspeccion-epp';
$action = $isEdit ? site_url($baseUrl . '/update/' . $inspeccion['id']) : site_url($baseUrl . '/store');
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="eppForm">
        <?= csrf_field() ?>

        <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;">
            <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success mt-2" style="font-size:14px;">
            <?= session()->getFlashdata('msg') ?>
        </div>
        <?php endif; ?>

        <div class="accordion mt-2" id="accordionEpp">

            <!-- DATOS GENERALES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accordionEpp">
                    <div class="accordion-body">
                        <?php if ($ctx === 'miembro'): ?>
                        <input type="hidden" name="id_cliente" value="<?= $idCliente ?? ($inspeccion['id_cliente'] ?? '') ?>">
                        <div class="mb-3">
                            <label class="form-label">Cliente</label>
                            <input type="text" class="form-control" value="<?= esc($cliente['nombre_cliente'] ?? '') ?>" readonly>
                        </div>
                        <?php else: ?>
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select name="id_cliente" id="selectCliente" class="form-select" required>
                                <option value="">Seleccionar cliente...</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Fecha de inspeccion *</label>
                            <input type="date" name="fecha_inspeccion" class="form-control"
                                value="<?= $inspeccion['fecha_inspeccion'] ?? date('Y-m-d') ?>" required>
                        </div>

                        <div class="mb-0">
                            <label class="form-label">Observaciones / Recomendaciones</label>
                            <textarea name="observaciones" class="form-control" rows="3" placeholder="Recomendaciones generales para la empresa..."><?= esc($inspeccion['observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HALLAZGOS -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secHallazgos">
                        Hallazgos de EPP (<span id="countHallazgos"><?= count($hallazgos ?? []) ?></span>)
                    </button>
                </h2>
                <div id="secHallazgos" class="accordion-collapse collapse" data-bs-parent="#accordionEpp">
                    <div class="accordion-body">
                        <div id="hallazgosContainer">
                            <?php if (!empty($hallazgos)): ?>
                                <?php foreach ($hallazgos as $i => $h): ?>
                                <div class="card mb-3 hallazgo-row">
                                    <div class="card-body p-2">
                                        <input type="hidden" name="hallazgo_id[]" value="<?= $h['id'] ?>">

                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong style="font-size:13px;">Hallazgo #<span class="hallazgo-num"><?= $i + 1 ?></span></strong>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-hallazgo" style="min-height:32px;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>

                                        <!-- Campos especificos de EPP -->
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <label class="form-label" style="font-size:12px;">Tipo de EPP</label>
                                                <input type="text" name="hallazgo_tipo_epp[]" class="form-control form-control-sm" value="<?= esc($h['tipo_epp'] ?? '') ?>" placeholder="Ej: Casco, Guantes, Botas...">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label" style="font-size:12px;">Trabajador / Area</label>
                                                <input type="text" name="hallazgo_trabajador_area[]" class="form-control form-control-sm" value="<?= esc($h['trabajador_area'] ?? '') ?>" placeholder="Ej: Operario Juan / Bodega">
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <textarea name="hallazgo_descripcion[]" class="form-control" rows="2" placeholder="Descripcion del hallazgo (mal estado, mal uso, vencido, no usado, etc.)" required><?= esc($h['descripcion']) ?></textarea>
                                        </div>

                                        <!-- Fotos -->
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <label class="form-label" style="font-size:12px;">Foto hallazgo</label>
                                                <?php if (!empty($h['imagen'])): ?>
                                                    <div class="mb-1">
                                                        <img src="<?= base_url($h['imagen']) ?>" class="img-fluid rounded" style="max-height:80px; object-fit:cover; cursor:pointer;" onclick="openPhoto(this.src)">
                                                    </div>
                                                <?php endif; ?>
                                                <div class="photo-input-group">
                                                    <input type="file" name="hallazgo_imagen[]" class="file-preview" accept="image/*" style="display:none;">
                                                    <div class="d-flex gap-1">
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i> Galeria</button>
                                                    </div>
                                                    <div class="preview-img mt-1"></div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label" style="font-size:12px;">Foto correccion</label>
                                                <?php if (!empty($h['imagen_correccion'])): ?>
                                                    <div class="mb-1">
                                                        <img src="<?= base_url($h['imagen_correccion']) ?>" class="img-fluid rounded" style="max-height:80px; object-fit:cover; cursor:pointer;" onclick="openPhoto(this.src)">
                                                    </div>
                                                <?php endif; ?>
                                                <div class="photo-input-group">
                                                    <input type="file" name="hallazgo_correccion[]" class="file-preview" accept="image/*" style="display:none;">
                                                    <div class="d-flex gap-1">
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i> Galeria</button>
                                                    </div>
                                                    <div class="preview-img mt-1"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label" style="font-size:12px;">Estado</label>
                                                <select name="hallazgo_estado[]" class="form-select form-select-sm">
                                                    <option value="ABIERTO" <?= ($h['estado'] ?? '') === 'ABIERTO' ? 'selected' : '' ?>>ABIERTO</option>
                                                    <option value="CERRADO" <?= ($h['estado'] ?? '') === 'CERRADO' ? 'selected' : '' ?>>CERRADO</option>
                                                    <option value="TIEMPO EXCEDIDO SIN RESPUESTA" <?= ($h['estado'] ?? '') === 'TIEMPO EXCEDIDO SIN RESPUESTA' ? 'selected' : '' ?>>TIEMPO EXCEDIDO</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label" style="font-size:12px;">Observaciones</label>
                                                <input type="text" name="hallazgo_observaciones[]" class="form-control form-control-sm" value="<?= esc($h['observaciones'] ?? '') ?>" placeholder="Obs...">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddHallazgo">
                            <i class="fas fa-plus"></i> Agregar hallazgo
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <div id="autoSaveStatus" style="font-size:12px; color:#999; text-align:center; padding:4px 0;">
            <i class="fas fa-cloud"></i> Autoguardado activado
        </div>

        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <button type="submit" name="finalizar" value="1" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;" id="btnFinalizar">
                <i class="fas fa-check-circle"></i> Finalizar inspeccion
            </button>
        </div>
    </form>
</div>

<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-body p-1 text-center">
                <img id="photoModalImg" src="" class="img-fluid" style="max-height:80vh;">
            </div>
        </div>
    </div>
</div>

<script>
function openPhoto(src) {
    document.getElementById('photoModalImg').src = src;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    const ctx = '<?= $ctx ?>';
    const clienteId = '<?= $idCliente ?? '' ?>';

    <?php if ($ctx === 'consultor'): ?>
    $.ajax({
        url: '<?= site_url('inspecciones/api/clientes') ?>',
        dataType: 'json',
        success: function(data) {
            const select = document.getElementById('selectCliente');
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = c.nombre_cliente;
                if (clienteId && c.id_cliente == clienteId) opt.selected = true;
                select.appendChild(opt);
            });
            $('#selectCliente').select2({ placeholder: 'Seleccionar cliente...', width: '100%' });
            if (window._pendingClientRestore) {
                $('#selectCliente').val(window._pendingClientRestore).trigger('change');
                window._pendingClientRestore = null;
            }
        }
    });
    <?php endif; ?>

    function updateHallazgos() {
        const rows = document.querySelectorAll('.hallazgo-row');
        document.getElementById('countHallazgos').textContent = rows.length;
        rows.forEach((row, i) => { row.querySelector('.hallazgo-num').textContent = i + 1; });
    }

    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-hallazgo')) {
            e.target.closest('.hallazgo-row').remove();
            updateHallazgos();
        }
    });

    function newHallazgoHtml(num, h) {
        h = h || {};
        return `
            <div class="card mb-3 hallazgo-row">
                <div class="card-body p-2">
                    <input type="hidden" name="hallazgo_id[]" value="${(h.id||'').toString().replace(/"/g,'&quot;')}">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong style="font-size:13px;">Hallazgo #<span class="hallazgo-num">${num}</span></strong>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-hallazgo" style="min-height:32px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;">Tipo de EPP</label>
                            <input type="text" name="hallazgo_tipo_epp[]" class="form-control form-control-sm" value="${(h.tipo_epp||'').replace(/"/g,'&quot;')}" placeholder="Ej: Casco, Guantes, Botas...">
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;">Trabajador / Area</label>
                            <input type="text" name="hallazgo_trabajador_area[]" class="form-control form-control-sm" value="${(h.trabajador_area||'').replace(/"/g,'&quot;')}" placeholder="Ej: Operario Juan / Bodega">
                        </div>
                    </div>

                    <div class="mb-2">
                        <textarea name="hallazgo_descripcion[]" class="form-control" rows="2" placeholder="Descripcion del hallazgo (mal estado, mal uso, vencido, no usado, etc.)" required>${(h.descripcion||'').replace(/</g,'&lt;')}</textarea>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;">Foto hallazgo</label>
                            <div class="photo-input-group">
                                <input type="file" name="hallazgo_imagen[]" class="file-preview" accept="image/*" style="display:none;">
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i> Galeria</button>
                                </div>
                                <div class="preview-img mt-1"></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;">Foto correccion</label>
                            <div class="photo-input-group">
                                <input type="file" name="hallazgo_correccion[]" class="file-preview" accept="image/*" style="display:none;">
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i> Galeria</button>
                                </div>
                                <div class="preview-img mt-1"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;">Estado</label>
                            <select name="hallazgo_estado[]" class="form-select form-select-sm">
                                <option value="ABIERTO">ABIERTO</option>
                                <option value="CERRADO">CERRADO</option>
                                <option value="TIEMPO EXCEDIDO SIN RESPUESTA">TIEMPO EXCEDIDO</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;">Observaciones</label>
                            <input type="text" name="hallazgo_observaciones[]" class="form-control form-control-sm" placeholder="Obs..." value="${(h.observaciones||'').replace(/"/g,'&quot;')}">
                        </div>
                    </div>
                </div>
            </div>`;
    }

    document.getElementById('btnAddHallazgo').addEventListener('click', function() {
        const num = document.querySelectorAll('.hallazgo-row').length + 1;
        document.getElementById('hallazgosContainer').insertAdjacentHTML('beforeend', newHallazgoHtml(num));
        updateHallazgos();
        const sec = document.getElementById('secHallazgos');
        if (!sec.classList.contains('show')) new bootstrap.Collapse(sec, { toggle: true });
    });

    document.addEventListener('click', function(e) {
        const galleryBtn = e.target.closest('.btn-photo-gallery');
        if (!galleryBtn) return;
        const group = galleryBtn.closest('.photo-input-group');
        const input = group.querySelector('input[type="file"]');
        input.removeAttribute('capture');
        input.click();
    });

    document.addEventListener('change', function(e) {
        if (!e.target.classList.contains('file-preview')) return;
        const input = e.target;
        const group = input.closest('.photo-input-group');
        const previewDiv = group ? group.querySelector('.preview-img') : null;
        if (!previewDiv) return;

        previewDiv.innerHTML = '';
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                previewDiv.innerHTML = '<img src="' + ev.target.result + '" class="img-fluid rounded" style="max-height:80px; object-fit:cover; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">' +
                    '<div style="font-size:11px; color:#28a745; margin-top:2px;"><i class="fas fa-check-circle"></i> Foto lista</div>';
            };
            reader.readAsDataURL(input.files[0]);
        }
    });

    document.getElementById('btnFinalizar').addEventListener('click', function(e) {
        let cliente;
        if (ctx === 'consultor') {
            cliente = document.getElementById('selectCliente').value;
        } else {
            cliente = document.querySelector('input[name="id_cliente"]').value;
        }
        const hallazgos = document.querySelectorAll('.hallazgo-row').length;

        if (!cliente || hallazgos === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Datos incompletos',
                html: 'Para finalizar necesitas al menos:<br><br>' +
                    (!cliente ? '- Seleccionar un cliente<br>' : '') +
                    (hallazgos === 0 ? '- Agregar al menos 1 hallazgo<br>' : ''),
                confirmButtonColor: '#bd9751',
            });
            return;
        }

        e.preventDefault();
        Swal.fire({
            title: 'Finalizar inspeccion de EPP?',
            html: 'Se generara el PDF y no podras editar la inspeccion despues.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Si, finalizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#bd9751',
        }).then(result => {
            if (result.isConfirmed) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'finalizar';
                input.value = '1';
                document.getElementById('eppForm').appendChild(input);
                document.getElementById('eppForm').submit();
            }
        });
    });

    // ============================================================
    // AUTOGUARDADO EN LOCALSTORAGE
    // ============================================================
    const STORAGE_KEY = 'epp_draft_<?= $inspeccion['id'] ?? 'new' ?>';
    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;

    function collectFormData() {
        const data = {};
        if (ctx === 'consultor') {
            data.id_cliente = document.getElementById('selectCliente').value;
        } else {
            data.id_cliente = document.querySelector('input[name="id_cliente"]').value;
        }
        data.fecha_inspeccion = document.querySelector('[name="fecha_inspeccion"]').value;
        data.observaciones = document.querySelector('[name="observaciones"]').value;

        data.hallazgos = [];
        document.querySelectorAll('.hallazgo-row').forEach(row => {
            const tipo = row.querySelector('[name="hallazgo_tipo_epp[]"]').value;
            const trab = row.querySelector('[name="hallazgo_trabajador_area[]"]').value;
            const desc = row.querySelector('[name="hallazgo_descripcion[]"]').value;
            const estado = row.querySelector('[name="hallazgo_estado[]"]').value;
            const obs = row.querySelector('[name="hallazgo_observaciones[]"]').value;
            const hId = row.querySelector('[name="hallazgo_id[]"]').value;
            if (desc || tipo) data.hallazgos.push({ id: hId, tipo_epp: tipo, trabajador_area: trab, descripcion: desc, estado, observaciones: obs });
        });

        data._savedAt = new Date().toISOString();
        return data;
    }

    function saveToLocal() {
        try {
            const data = collectFormData();
            if (data.id_cliente || data.hallazgos.length) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
                document.getElementById('autoSaveStatus').innerHTML =
                    '<i class="fas fa-check-circle text-success"></i> Guardado ' + new Date().toLocaleTimeString();
            }
        } catch(e) {}
    }

    function restoreFromLocal(data) {
        if (data.id_cliente && ctx === 'consultor') {
            window._pendingClientRestore = data.id_cliente;
        }
        if (data.fecha_inspeccion) document.querySelector('[name="fecha_inspeccion"]').value = data.fecha_inspeccion;
        if (data.observaciones) document.querySelector('[name="observaciones"]').value = data.observaciones;

        (data.hallazgos || []).forEach(h => {
            const num = document.querySelectorAll('.hallazgo-row').length + 1;
            document.getElementById('hallazgosContainer').insertAdjacentHTML('beforeend', newHallazgoHtml(num, h));
            if (h.estado) {
                const rows = document.querySelectorAll('.hallazgo-row');
                rows[rows.length - 1].querySelector('[name="hallazgo_estado[]"]').value = h.estado;
            }
        });
        updateHallazgos();
    }

    if (!isEdit) {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const data = JSON.parse(saved);
                const savedTime = new Date(data._savedAt);
                const hoursAgo = ((Date.now() - savedTime.getTime()) / 3600000).toFixed(1);

                if (hoursAgo < 24) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Borrador recuperado',
                        html: 'Tienes un borrador guardado hace <strong>' + hoursAgo + ' horas</strong>.<br>Deseas restaurarlo?',
                        showCancelButton: true,
                        confirmButtonText: 'Si, restaurar',
                        cancelButtonText: 'No, empezar de cero',
                        confirmButtonColor: '#bd9751',
                    }).then(result => {
                        if (result.isConfirmed) restoreFromLocal(data);
                        else localStorage.removeItem(STORAGE_KEY);
                    });
                } else {
                    localStorage.removeItem(STORAGE_KEY);
                }
            }
        } catch(e) {}
    }

    setInterval(saveToLocal, 30000);
    document.getElementById('eppForm').addEventListener('input', function() {
        clearTimeout(window._autoSaveTimeout);
        window._autoSaveTimeout = setTimeout(saveToLocal, 2000);
    });
    <?php if ($ctx === 'consultor'): ?>
    $('#selectCliente').on('change', function() { setTimeout(saveToLocal, 500); });
    <?php endif; ?>

    document.getElementById('eppForm').addEventListener('submit', function() {
        localStorage.removeItem(STORAGE_KEY);
    });
});
</script>
