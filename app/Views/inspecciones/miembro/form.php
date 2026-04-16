<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? site_url('miembro/inspecciones/locativa/update/' . $inspeccion['id']) : site_url('miembro/inspecciones/locativa/store');
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="locativaForm">
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

        <div class="accordion mt-2" id="accordionLocativa">

            <!-- DATOS GENERALES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accordionLocativa">
                    <div class="accordion-body">
                        <!-- Cliente (auto-asignado) -->
                        <input type="hidden" name="id_cliente" value="<?= $idCliente ?>">
                        <div class="mb-3">
                            <label class="form-label">Cliente</label>
                            <input type="text" class="form-control" value="<?= esc($cliente['nombre_cliente'] ?? '') ?>" readonly>
                        </div>

                        <!-- Realizado por (auto-asignado) -->
                        <div class="mb-3">
                            <label class="form-label">Realizado por</label>
                            <input type="text" class="form-control" value="<?= esc($miembro['nombre_completo'] ?? '') ?>" readonly>
                        </div>

                        <!-- Fecha -->
                        <div class="mb-3">
                            <label class="form-label">Fecha de inspeccion *</label>
                            <input type="date" name="fecha_inspeccion" class="form-control"
                                value="<?= $inspeccion['fecha_inspeccion'] ?? date('Y-m-d') ?>" required>
                        </div>

                        <!-- Observaciones -->
                        <div class="mb-0">
                            <label class="form-label">Observaciones generales</label>
                            <textarea name="observaciones" class="form-control" rows="3" placeholder="Observaciones de la inspeccion..."><?= esc($inspeccion['observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HALLAZGOS -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secHallazgos">
                        Hallazgos (<span id="countHallazgos"><?= count($hallazgos ?? []) ?></span>)
                    </button>
                </h2>
                <div id="secHallazgos" class="accordion-collapse collapse" data-bs-parent="#accordionLocativa">
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
                                        <div class="mb-2">
                                            <textarea name="hallazgo_descripcion[]" class="form-control" rows="2" placeholder="Descripcion del hallazgo" required><?= esc($h['descripcion']) ?></textarea>
                                        </div>
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
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i></button>
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
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i></button>
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
    function updateHallazgos() {
        const rows = document.querySelectorAll('.hallazgo-row');
        document.getElementById('countHallazgos').textContent = rows.length;
        rows.forEach((row, i) => {
            row.querySelector('.hallazgo-num').textContent = i + 1;
        });
    }

    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-hallazgo')) {
            e.target.closest('.hallazgo-row').remove();
            updateHallazgos();
        }
    });

    document.getElementById('btnAddHallazgo').addEventListener('click', function() {
        const num = document.querySelectorAll('.hallazgo-row').length + 1;
        const html = `
            <div class="card mb-3 hallazgo-row">
                <div class="card-body p-2">
                    <input type="hidden" name="hallazgo_id[]" value="">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong style="font-size:13px;">Hallazgo #<span class="hallazgo-num">${num}</span></strong>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-hallazgo" style="min-height:32px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="mb-2">
                        <textarea name="hallazgo_descripcion[]" class="form-control" rows="2" placeholder="Descripcion del hallazgo" required></textarea>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;">Foto hallazgo</label>
                            <div class="photo-input-group">
                                <input type="file" name="hallazgo_imagen[]" class="file-preview" accept="image/*" style="display:none;">
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i></button>
                                </div>
                                <div class="preview-img mt-1"></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px;">Foto correccion</label>
                            <div class="photo-input-group">
                                <input type="file" name="hallazgo_correccion[]" class="file-preview" accept="image/*" style="display:none;">
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i></button>
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
                            <input type="text" name="hallazgo_observaciones[]" class="form-control form-control-sm" placeholder="Obs...">
                        </div>
                    </div>
                </div>
            </div>`;
        document.getElementById('hallazgosContainer').insertAdjacentHTML('beforeend', html);
        updateHallazgos();

        const secHallazgos = document.getElementById('secHallazgos');
        if (!secHallazgos.classList.contains('show')) {
            new bootstrap.Collapse(secHallazgos, { toggle: true });
        }
    });

    // Camara / Galeria
    document.addEventListener('click', function(e) {
        const cameraBtn = e.target.closest('.btn-photo-camera');
        const galleryBtn = e.target.closest('.btn-photo-gallery');
        if (!cameraBtn && !galleryBtn) return;
        const group = (cameraBtn || galleryBtn).closest('.photo-input-group');
        const input = group.querySelector('input[type="file"]');
        input.removeAttribute('capture');
        input.click();
    });

    // Preview fotos
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

    // Validacion al finalizar
    document.getElementById('btnFinalizar').addEventListener('click', function(e) {
        const hallazgos = document.querySelectorAll('.hallazgo-row').length;
        if (hallazgos === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning', title: 'Datos incompletos',
                html: 'Debes agregar al menos 1 hallazgo para finalizar.',
                confirmButtonColor: '#bd9751',
            });
            return;
        }
        e.preventDefault();
        Swal.fire({
            title: 'Finalizar inspeccion?',
            html: 'Se generara el PDF y se notificara al consultor.',
            icon: 'question', showCancelButton: true,
            confirmButtonText: 'Si, finalizar', cancelButtonText: 'Cancelar',
            confirmButtonColor: '#bd9751',
        }).then(result => {
            if (result.isConfirmed) {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = 'finalizar'; input.value = '1';
                document.getElementById('locativaForm').appendChild(input);
                document.getElementById('locativaForm').submit();
            }
        });
    });

    // Autoguardado localStorage
    const STORAGE_KEY = 'miembro_locativa_draft_<?= $inspeccion['id'] ?? 'new' ?>';
    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;

    function collectFormData() {
        const data = {};
        data.fecha_inspeccion = document.querySelector('[name="fecha_inspeccion"]').value;
        data.observaciones = document.querySelector('[name="observaciones"]').value;
        data.hallazgos = [];
        document.querySelectorAll('.hallazgo-row').forEach(row => {
            const desc = row.querySelector('[name="hallazgo_descripcion[]"]').value;
            const estado = row.querySelector('[name="hallazgo_estado[]"]').value;
            const obs = row.querySelector('[name="hallazgo_observaciones[]"]').value;
            const hId = row.querySelector('[name="hallazgo_id[]"]').value;
            if (desc) data.hallazgos.push({ id: hId, descripcion: desc, estado, observaciones: obs });
        });
        data._savedAt = new Date().toISOString();
        return data;
    }

    function saveToLocal() {
        try {
            const data = collectFormData();
            if (data.hallazgos.length) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
                document.getElementById('autoSaveStatus').innerHTML =
                    '<i class="fas fa-check-circle text-success"></i> Guardado ' + new Date().toLocaleTimeString();
            }
        } catch(e) {}
    }

    setInterval(saveToLocal, 30000);
    document.getElementById('locativaForm').addEventListener('input', function() {
        clearTimeout(window._autoSaveTimeout);
        window._autoSaveTimeout = setTimeout(saveToLocal, 2000);
    });
    document.getElementById('locativaForm').addEventListener('submit', function() {
        localStorage.removeItem(STORAGE_KEY);
    });
});
</script>
