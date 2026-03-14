<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? '/inspecciones/senalizacion/update/' . $inspeccion['id'] : '/inspecciones/senalizacion/store';
$estados = ['NO APLICA', 'NO CUMPLE', 'CUMPLE PARCIALMENTE', 'CUMPLE TOTALMENTE'];
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="senalForm">
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

        <div class="accordion mt-2" id="accordionSenal">

            <!-- DATOS GENERALES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accordionSenal">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select name="id_cliente" id="selectCliente" class="form-select" required>
                                <option value="">Seleccionar cliente...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha de inspección *</label>
                            <input type="date" name="fecha_inspeccion" class="form-control"
                                value="<?= $inspeccion['fecha_inspeccion'] ?? date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Observaciones generales</label>
                            <textarea name="observaciones" class="form-control" rows="3" placeholder="Observaciones..."><?= esc($inspeccion['observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ÍTEMS POR GRUPO -->
            <?php
            $itemIndex = 0;
            foreach ($itemsGrouped as $grupo => $items):
            $grupoId = 'sec_' . preg_replace('/[^a-z0-9]/', '_', strtolower($grupo));
            ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $grupoId ?>">
                        <?= esc($grupo) ?> (<?= count($items) ?>)
                    </button>
                </h2>
                <div id="<?= $grupoId ?>" class="accordion-collapse collapse" data-bs-parent="#accordionSenal">
                    <div class="accordion-body p-2">
                        <?php foreach ($items as $item): ?>
                        <div class="card mb-2 item-senal-row" style="border-left:3px solid #bd9751;">
                            <div class="card-body p-2">
                                <input type="hidden" name="item_id[]" value="<?= $item['id'] ?? '' ?>">
                                <input type="hidden" name="item_nombre[]" value="<?= esc($item['nombre_item']) ?>">
                                <input type="hidden" name="item_grupo[]" value="<?= esc($item['grupo']) ?>">

                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong style="font-size:12px;"><?= esc($item['nombre_item']) ?></strong>
                                </div>

                                <select name="item_estado[]" class="form-select form-select-sm item-estado-select mb-1" style="font-size:12px;">
                                    <?php foreach ($estados as $est): ?>
                                    <option value="<?= $est ?>" <?= ($item['estado_cumplimiento'] ?? '') === $est ? 'selected' : '' ?>><?= $est ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <?php if (!empty($item['foto'])): ?>
                                <div class="mb-1">
                                    <img src="<?= base_url($item['foto']) ?>" class="img-fluid rounded" style="max-height:60px; object-fit:cover; cursor:pointer;" onclick="openPhoto(this.src)">
                                </div>
                                <?php endif; ?>

                                <div class="photo-input-group">
                                    <input type="file" name="item_foto[]" class="file-preview" accept="image/*" style="display:none;">
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera" style="font-size:11px; padding:2px 6px;">
                                            <i class="fas fa-camera"></i> Camara
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery" style="font-size:11px; padding:2px 6px;">
                                            <i class="fas fa-images"></i> Galeria
                                        </button>
                                    </div>
                                    <div class="preview-img mt-1"></div>
                                </div>
                            </div>
                        </div>
                        <?php $itemIndex++; endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

        </div><!-- /accordion -->

        <!-- Resumen calificación -->
        <div class="card mt-3" id="cardResumen" style="border:2px solid #bd9751;">
            <div class="card-body p-2 text-center">
                <strong style="font-size:14px;">Calificación:</strong>
                <span id="calcCalificacion" style="font-size:18px; font-weight:700; color:#bd9751;">--</span>%
                <br>
                <small id="calcDescripcion" style="font-size:12px; color:#666;">--</small>
                <br>
                <small style="font-size:11px; color:#999;">
                    NA: <span id="calcNA">0</span> |
                    NC: <span id="calcNC">0</span> |
                    CP: <span id="calcCP">0</span> |
                    CT: <span id="calcCT">0</span>
                </small>
            </div>
        </div>

        <!-- Indicador autoguardado -->
        <div id="autoSaveStatus" style="font-size:12px; color:#999; text-align:center; padding:4px 0;">
            <i class="fas fa-cloud"></i> Autoguardado activado
        </div>

        <!-- Botones -->
        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <button type="submit" name="finalizar" value="1" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;" id="btnFinalizar">
                <i class="fas fa-check-circle"></i> Finalizar inspección
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

document.addEventListener('DOMContentLoaded', function() {
    const clienteId = '<?= $idCliente ?? '' ?>';

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

    function recalcular() {
        let na = 0, nc = 0, cp = 0, ct = 0;
        document.querySelectorAll('.item-estado-select').forEach(sel => {
            switch (sel.value) {
                case 'NO APLICA':           na++; break;
                case 'NO CUMPLE':           nc++; break;
                case 'CUMPLE PARCIALMENTE': cp++; break;
                case 'CUMPLE TOTALMENTE':   ct++; break;
            }
        });
        const denom = nc + cp + ct;
        const calif = denom > 0 ? (100 * (0.5 * cp + ct) / denom) : 0;

        document.getElementById('calcNA').textContent = na;
        document.getElementById('calcNC').textContent = nc;
        document.getElementById('calcCP').textContent = cp;
        document.getElementById('calcCT').textContent = ct;
        document.getElementById('calcCalificacion').textContent = calif.toFixed(1);

        let desc = '--';
        let color = '#bd9751';
        if (denom > 0) {
            if (calif <= 40) { desc = 'Nivel critico'; color = '#dc3545'; }
            else if (calif <= 60) { desc = 'Nivel bajo'; color = '#fd7e14'; }
            else if (calif <= 80) { desc = 'Nivel medio'; color = '#ffc107'; }
            else if (calif <= 90) { desc = 'Nivel bueno'; color = '#28a745'; }
            else { desc = 'Nivel excelente'; color = '#28a745'; }
        }
        document.getElementById('calcDescripcion').textContent = desc;
        document.getElementById('calcCalificacion').style.color = color;
    }

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-estado-select')) recalcular();
    });
    recalcular();

    document.addEventListener('click', function(e) {
        const cameraBtn = e.target.closest('.btn-photo-camera');
        const galleryBtn = e.target.closest('.btn-photo-gallery');
        if (!cameraBtn && !galleryBtn) return;

        const group = (cameraBtn || galleryBtn).closest('.photo-input-group');
        const input = group.querySelector('input[type="file"]');

        // No usar capture="environment": con ese atributo la foto NO se guarda en galeria del celular
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
                previewDiv.innerHTML = '<img src="' + ev.target.result + '" class="img-fluid rounded" style="max-height:60px; object-fit:cover; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">' +
                    '<div style="font-size:11px; color:#28a745; margin-top:2px;"><i class="fas fa-check-circle"></i> Foto lista</div>';
            };
            reader.readAsDataURL(input.files[0]);
        }
    });

    document.getElementById('btnFinalizar').addEventListener('click', function(e) {
        const cliente = document.getElementById('selectCliente').value;
        if (!cliente) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Selecciona un cliente', confirmButtonColor: '#bd9751' });
            return;
        }
        e.preventDefault();
        Swal.fire({
            title: 'Finalizar inspección?',
            html: 'Se generará el PDF y no podrás editar después.',
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
                document.getElementById('senalForm').appendChild(input);
                document.getElementById('senalForm').submit();
            }
        });
    });

    // AUTOGUARDADO
    const STORAGE_KEY = 'senal_draft_<?= $inspeccion['id'] ?? 'new' ?>';
    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;

    function collectFormData() {
        const data = {};
        data.id_cliente = document.getElementById('selectCliente').value;
        data.fecha_inspeccion = document.querySelector('[name="fecha_inspeccion"]').value;
        data.observaciones = document.querySelector('[name="observaciones"]').value;
        data.items = [];
        document.querySelectorAll('.item-senal-row').forEach(row => {
            const nombre = row.querySelector('[name="item_nombre[]"]').value;
            const estado = row.querySelector('[name="item_estado[]"]').value;
            data.items.push({ nombre, estado });
        });
        data._savedAt = new Date().toISOString();
        return data;
    }

    function saveToLocal() {
        try {
            const data = collectFormData();
            if (data.id_cliente || data.items.length) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
                document.getElementById('autoSaveStatus').innerHTML =
                    '<i class="fas fa-check-circle text-success"></i> Guardado ' + new Date().toLocaleTimeString();
            }
        } catch(e) {}
    }

    function restoreFromLocal(data) {
        if (data.id_cliente) window._pendingClientRestore = data.id_cliente;
        if (data.fecha_inspeccion) document.querySelector('[name="fecha_inspeccion"]').value = data.fecha_inspeccion;
        if (data.observaciones) document.querySelector('[name="observaciones"]').value = data.observaciones;

        if (data.items && data.items.length) {
            const rows = document.querySelectorAll('.item-senal-row');
            data.items.forEach((item, i) => {
                if (rows[i]) {
                    const sel = rows[i].querySelector('[name="item_estado[]"]');
                    if (sel && item.estado) sel.value = item.estado;
                }
            });
            recalcular();
        }
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
    document.getElementById('senalForm').addEventListener('input', function() {
        clearTimeout(window._autoSaveTimeout);
        window._autoSaveTimeout = setTimeout(saveToLocal, 2000);
    });
    $('#selectCliente').on('change', function() { setTimeout(saveToLocal, 500); });
    document.getElementById('senalForm').addEventListener('submit', function() {
        localStorage.removeItem(STORAGE_KEY);
    });
});
</script>
