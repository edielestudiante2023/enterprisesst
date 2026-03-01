<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? '/inspecciones/extintores/update/' . $inspeccion['id'] : '/inspecciones/extintores/store';
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="extForm">
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

        <div class="accordion mt-2" id="accordionExt">

            <!-- DATOS GENERALES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accordionExt">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select name="id_cliente" id="selectCliente" class="form-select" required>
                                <option value="">Seleccionar cliente...</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Fecha inspeccion *</label>
                                <input type="date" name="fecha_inspeccion" class="form-control"
                                    value="<?= $inspeccion['fecha_inspeccion'] ?? date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Fecha vencimiento global</label>
                                <input type="date" name="fecha_vencimiento_global" class="form-control"
                                    value="<?= $inspeccion['fecha_vencimiento_global'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INVENTARIO GENERAL -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secInventario">
                        Inventario General
                    </button>
                </h2>
                <div id="secInventario" class="accordion-collapse collapse" data-bs-parent="#accordionExt">
                    <div class="accordion-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Total extintores</label>
                                <input type="number" name="numero_extintores_totales" class="form-control form-control-sm" min="0" value="<?= $inspeccion['numero_extintores_totales'] ?? 0 ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Capacidad (libras)</label>
                                <input type="text" name="capacidad_libras" class="form-control form-control-sm" placeholder="Ej: 10 LIBRAS" value="<?= esc($inspeccion['capacidad_libras'] ?? '') ?>">
                            </div>
                        </div>
                        <hr style="margin:8px 0;">
                        <small class="text-muted d-block mb-2" style="font-size:11px;">Por tipo de agente:</small>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">ABC (Multiproposito)</label>
                                <input type="number" name="cantidad_abc" class="form-control form-control-sm" min="0" value="<?= $inspeccion['cantidad_abc'] ?? 0 ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">CO2</label>
                                <input type="number" name="cantidad_co2" class="form-control form-control-sm" min="0" value="<?= $inspeccion['cantidad_co2'] ?? 0 ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Solkaflam 123</label>
                                <input type="number" name="cantidad_solkaflam" class="form-control form-control-sm" min="0" value="<?= $inspeccion['cantidad_solkaflam'] ?? 0 ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Agua</label>
                                <input type="number" name="cantidad_agua" class="form-control form-control-sm" min="0" value="<?= $inspeccion['cantidad_agua'] ?? 0 ?>">
                            </div>
                        </div>
                        <hr style="margin:8px 0;">
                        <small class="text-muted d-block mb-2" style="font-size:11px;">Distribucion por ubicacion:</small>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Unidades residenciales</label>
                                <input type="number" name="cantidad_unidades_residenciales" class="form-control form-control-sm" min="0" value="<?= $inspeccion['cantidad_unidades_residenciales'] ?? 0 ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Porteria</label>
                                <input type="number" name="cantidad_porteria" class="form-control form-control-sm" min="0" value="<?= $inspeccion['cantidad_porteria'] ?? 0 ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Oficina admin</label>
                                <input type="number" name="cantidad_oficina_admin" class="form-control form-control-sm" min="0" value="<?= $inspeccion['cantidad_oficina_admin'] ?? 0 ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Shut basuras</label>
                                <input type="number" name="cantidad_shut_basuras" class="form-control form-control-sm" min="0" value="<?= $inspeccion['cantidad_shut_basuras'] ?? 0 ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Salones comunales</label>
                                <input type="number" name="cantidad_salones_comunales" class="form-control form-control-sm" min="0" value="<?= $inspeccion['cantidad_salones_comunales'] ?? 0 ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Cuarto bombas</label>
                                <input type="number" name="cantidad_cuarto_bombas" class="form-control form-control-sm" min="0" value="<?= $inspeccion['cantidad_cuarto_bombas'] ?? 0 ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Planta electrica</label>
                                <input type="number" name="cantidad_planta_electrica" class="form-control form-control-sm" min="0" value="<?= $inspeccion['cantidad_planta_electrica'] ?? 0 ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EXTINTORES INSPECCIONADOS -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secExtintores">
                        Extintores Inspeccionados (<span id="countExt"><?= count($extintores ?? []) ?></span>)
                    </button>
                </h2>
                <div id="secExtintores" class="accordion-collapse collapse" data-bs-parent="#accordionExt">
                    <div class="accordion-body p-2">
                        <div id="extintoresContainer">
                            <?php if (!empty($extintores)): ?>
                                <?php foreach ($extintores as $i => $ext): ?>
                                <?php include __DIR__ . '/_extintor_row.php'; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddExtintor">
                            <i class="fas fa-plus"></i> Agregar extintor
                        </button>
                    </div>
                </div>
            </div>

        </div><!-- /accordion -->

        <!-- Recomendaciones generales -->
        <div class="card mt-3">
            <div class="card-body p-2">
                <label class="form-label" style="font-size:13px;">Recomendaciones generales</label>
                <textarea name="recomendaciones_generales" class="form-control" rows="3" placeholder="Recomendaciones..."><?= esc($inspeccion['recomendaciones_generales'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Indicador autoguardado -->
        <div id="autoSaveStatus" style="font-size:12px; color:#999; text-align:center; padding:4px 0;">
            <i class="fas fa-cloud"></i> Autoguardado activado
        </div>

        <!-- Botones -->
        <div class="mt-3 mb-4">
            <button type="submit" class="btn btn-pwa btn-pwa-outline">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <button type="submit" name="finalizar" value="1" class="btn btn-pwa btn-pwa-primary" id="btnFinalizar">
                <i class="fas fa-check-circle"></i> Finalizar inspeccion
            </button>
        </div>
    </form>
</div>

<!-- Modal foto ampliada -->
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

// Criterios en JS para template dinámico
const CRITERIOS = <?= json_encode($criterios) ?>;

function buildExtintorRow(num, data) {
    data = data || {};
    let criteriosHtml = '';
    for (const [key, cfg] of Object.entries(CRITERIOS)) {
        let optionsHtml = '';
        cfg.opciones.forEach(opt => {
            const sel = (data[key] === opt) ? 'selected' : (!data[key] && opt === cfg.default ? 'selected' : '');
            optionsHtml += '<option value="' + opt + '" ' + sel + '>' + opt + '</option>';
        });
        criteriosHtml += `
            <div class="col-6 mb-1">
                <label class="form-label" style="font-size:11px;">${cfg.label}</label>
                <select name="ext_${key}[]" class="form-select form-select-sm" style="font-size:12px;">
                    ${optionsHtml}
                </select>
            </div>`;
    }

    return `
    <div class="card mb-2 extintor-row" style="border-left:3px solid #dc3545;">
        <div class="card-body p-2">
            <input type="hidden" name="ext_id[]" value="${data.id || ''}">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <strong style="font-size:13px;"><i class="fas fa-fire-extinguisher text-danger"></i> Extintor #<span class="ext-num">${num}</span></strong>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-ext" style="min-height:32px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row g-1">
                ${criteriosHtml}
            </div>
            <div class="row g-2 mt-1">
                <div class="col-6">
                    <label class="form-label" style="font-size:11px;">Fecha vencimiento</label>
                    <input type="date" name="ext_fecha_vencimiento[]" class="form-control form-control-sm" value="${data.fecha_vencimiento || ''}">
                </div>
                <div class="col-6">
                    <label class="form-label" style="font-size:11px;">Foto</label>
                    <div class="photo-input-group">
                        <input type="file" name="ext_foto[]" class="file-preview" accept="image/*" style="display:none;">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera" style="font-size:11px; padding:2px 6px;"><i class="fas fa-camera"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery" style="font-size:11px; padding:2px 6px;"><i class="fas fa-images"></i></button>
                        </div>
                        <div class="preview-img mt-1"></div>
                    </div>
                </div>
            </div>
            <div class="mt-1">
                <label class="form-label" style="font-size:11px;">Observaciones</label>
                <input type="text" name="ext_observaciones[]" class="form-control form-control-sm" placeholder="Observaciones..." value="${(data.observaciones || '').replace(/"/g, '&quot;')}">
            </div>
        </div>
    </div>`;
}

document.addEventListener('DOMContentLoaded', function() {
    const clienteId = '<?= $idCliente ?? '' ?>';

    // --- Select2 clientes ---
    $.ajax({
        url: '/inspecciones/api/clientes',
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

    // --- Conteo y numeracion ---
    function updateExtintores() {
        const rows = document.querySelectorAll('.extintor-row');
        document.getElementById('countExt').textContent = rows.length;
        rows.forEach((row, i) => {
            row.querySelector('.ext-num').textContent = i + 1;
        });
    }

    // --- Eliminar extintor ---
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-ext')) {
            e.target.closest('.extintor-row').remove();
            updateExtintores();
        }
    });

    // --- Agregar extintor ---
    document.getElementById('btnAddExtintor').addEventListener('click', function() {
        const num = document.querySelectorAll('.extintor-row').length + 1;
        document.getElementById('extintoresContainer').insertAdjacentHTML('beforeend', buildExtintorRow(num));
        updateExtintores();

        const secExt = document.getElementById('secExtintores');
        if (!secExt.classList.contains('show')) {
            new bootstrap.Collapse(secExt, { toggle: true });
        }
    });

    // --- Botones Camara / Galeria ---
    document.addEventListener('click', function(e) {
        const cameraBtn = e.target.closest('.btn-photo-camera');
        const galleryBtn = e.target.closest('.btn-photo-gallery');
        if (!cameraBtn && !galleryBtn) return;

        const group = (cameraBtn || galleryBtn).closest('.photo-input-group');
        const input = group.querySelector('input[type="file"]');

        if (cameraBtn) {
            input.setAttribute('capture', 'environment');
        } else {
            input.removeAttribute('capture');
        }
        input.click();
    });

    // --- Preview fotos ---
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
                previewDiv.innerHTML = '<img src="' + ev.target.result + '" class="img-fluid rounded" style="max-height:60px; object-fit:cover; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">' +
                    '<div style="font-size:11px; color:#28a745; margin-top:2px;"><i class="fas fa-check-circle"></i> Foto lista</div>';
            };
            reader.readAsDataURL(input.files[0]);
        }
    });

    // --- Finalizar validacion ---
    document.getElementById('btnFinalizar').addEventListener('click', function(e) {
        const cliente = document.getElementById('selectCliente').value;
        if (!cliente) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Selecciona un cliente', confirmButtonColor: '#bd9751' });
            return;
        }
        e.preventDefault();
        Swal.fire({
            title: 'Finalizar inspeccion?',
            html: 'Se generara el PDF y no podras editar despues.',
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
                document.getElementById('extForm').appendChild(input);
                document.getElementById('extForm').submit();
            }
        });
    });

    // ============================================================
    // AUTOGUARDADO EN LOCALSTORAGE
    // ============================================================
    const STORAGE_KEY = 'ext_draft_<?= $inspeccion['id'] ?? 'new' ?>';
    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;

    function collectFormData() {
        const data = {};
        data.id_cliente = document.getElementById('selectCliente').value;
        data.fecha_inspeccion = document.querySelector('[name="fecha_inspeccion"]').value;
        data.fecha_vencimiento_global = document.querySelector('[name="fecha_vencimiento_global"]').value;
        data.recomendaciones_generales = document.querySelector('[name="recomendaciones_generales"]').value;

        // Inventario
        ['numero_extintores_totales','cantidad_abc','cantidad_co2','cantidad_solkaflam','cantidad_agua',
         'capacidad_libras','cantidad_unidades_residenciales','cantidad_porteria','cantidad_oficina_admin',
         'cantidad_shut_basuras','cantidad_salones_comunales','cantidad_cuarto_bombas','cantidad_planta_electrica'
        ].forEach(f => { data[f] = document.querySelector('[name="'+f+'"]')?.value || ''; });

        // Extintores
        data.extintores = [];
        document.querySelectorAll('.extintor-row').forEach(row => {
            const ext = {};
            ext.id = row.querySelector('[name="ext_id[]"]').value;
            for (const key of Object.keys(CRITERIOS)) {
                ext[key] = row.querySelector('[name="ext_'+key+'[]"]')?.value || '';
            }
            ext.fecha_vencimiento = row.querySelector('[name="ext_fecha_vencimiento[]"]')?.value || '';
            ext.observaciones = row.querySelector('[name="ext_observaciones[]"]')?.value || '';
            data.extintores.push(ext);
        });

        data._savedAt = new Date().toISOString();
        return data;
    }

    function saveToLocal() {
        try {
            const data = collectFormData();
            if (data.id_cliente || data.extintores.length) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
                document.getElementById('autoSaveStatus').innerHTML =
                    '<i class="fas fa-check-circle text-success"></i> Guardado ' + new Date().toLocaleTimeString();
            }
        } catch(e) {}
    }

    function restoreFromLocal(data) {
        if (data.id_cliente) window._pendingClientRestore = data.id_cliente;
        if (data.fecha_inspeccion) document.querySelector('[name="fecha_inspeccion"]').value = data.fecha_inspeccion;
        if (data.fecha_vencimiento_global) document.querySelector('[name="fecha_vencimiento_global"]').value = data.fecha_vencimiento_global;
        if (data.recomendaciones_generales) document.querySelector('[name="recomendaciones_generales"]').value = data.recomendaciones_generales;

        // Inventario
        ['numero_extintores_totales','cantidad_abc','cantidad_co2','cantidad_solkaflam','cantidad_agua',
         'capacidad_libras','cantidad_unidades_residenciales','cantidad_porteria','cantidad_oficina_admin',
         'cantidad_shut_basuras','cantidad_salones_comunales','cantidad_cuarto_bombas','cantidad_planta_electrica'
        ].forEach(f => {
            const el = document.querySelector('[name="'+f+'"]');
            if (el && data[f]) el.value = data[f];
        });

        // Extintores
        (data.extintores || []).forEach((ext, i) => {
            document.getElementById('extintoresContainer').insertAdjacentHTML('beforeend', buildExtintorRow(i + 1, ext));
        });
        updateExtintores();
    }

    if (!isEdit) {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const data = JSON.parse(saved);
                const hoursAgo = ((Date.now() - new Date(data._savedAt).getTime()) / 3600000).toFixed(1);
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
    document.getElementById('extForm').addEventListener('input', function() {
        clearTimeout(window._autoSaveTimeout);
        window._autoSaveTimeout = setTimeout(saveToLocal, 2000);
    });
    $('#selectCliente').on('change', function() { setTimeout(saveToLocal, 500); });
    document.getElementById('extForm').addEventListener('submit', function() {
        localStorage.removeItem(STORAGE_KEY);
    });
});
</script>
