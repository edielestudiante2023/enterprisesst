<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? '/inspecciones/pausas-activas/update/' . $inspeccion['id'] : '/inspecciones/pausas-activas/store';
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="pausaForm">
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

        <div class="accordion mt-2" id="accordionPausa">

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accordionPausa">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select name="id_cliente" id="selectCliente" class="form-select" required>
                                <option value="">Seleccionar cliente...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha de actividad *</label>
                            <input type="date" name="fecha_actividad" class="form-control"
                                value="<?= $inspeccion['fecha_actividad'] ?? date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Observaciones generales</label>
                            <textarea name="observaciones" class="form-control" rows="3" placeholder="Observaciones..."><?= esc($inspeccion['observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secRegistros">
                        Registros (<span id="countRegistros"><?= count($registros ?? []) ?></span>)
                    </button>
                </h2>
                <div id="secRegistros" class="accordion-collapse collapse" data-bs-parent="#accordionPausa">
                    <div class="accordion-body">
                        <div id="registrosContainer">
                            <?php if (!empty($registros)): ?>
                                <?php foreach ($registros as $i => $r): ?>
                                <div class="card mb-3 registro-row">
                                    <div class="card-body p-2">
                                        <input type="hidden" name="registro_id[]" value="<?= $r['id'] ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong style="font-size:13px;">Registro #<span class="registro-num"><?= $i + 1 ?></span></strong>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-registro" style="min-height:32px;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label" style="font-size:12px;">Tipo de pausa</label>
                                            <input type="text" name="registro_tipo[]" class="form-control" value="<?= esc($r['tipo_pausa']) ?>" placeholder="Ej: Estiramiento, Relajacion visual..." required>
                                        </div>
                                        <div>
                                            <label class="form-label" style="font-size:12px;">Foto evidencia</label>
                                            <?php if (!empty($r['imagen'])): ?>
                                                <div class="mb-1">
                                                    <img src="<?= base_url($r['imagen']) ?>" class="img-fluid rounded" style="max-height:80px; object-fit:cover; cursor:pointer;" onclick="openPhoto(this.src)">
                                                </div>
                                            <?php endif; ?>
                                            <div class="photo-input-group">
                                                <input type="file" name="registro_imagen[]" class="file-preview" accept="image/*" style="display:none;">
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i> Galeria</button>
                                                </div>
                                                <div class="preview-img mt-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddRegistro">
                            <i class="fas fa-plus"></i> Agregar registro
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
                <i class="fas fa-check-circle"></i> Finalizar
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
        }
    });

    function updateRegistros() {
        const rows = document.querySelectorAll('.registro-row');
        document.getElementById('countRegistros').textContent = rows.length;
        rows.forEach((row, i) => { row.querySelector('.registro-num').textContent = i + 1; });
    }

    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-registro')) {
            e.target.closest('.registro-row').remove();
            updateRegistros();
        }
    });

    document.getElementById('btnAddRegistro').addEventListener('click', function() {
        const num = document.querySelectorAll('.registro-row').length + 1;
        const html = `
            <div class="card mb-3 registro-row">
                <div class="card-body p-2">
                    <input type="hidden" name="registro_id[]" value="">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong style="font-size:13px;">Registro #<span class="registro-num">${num}</span></strong>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-registro" style="min-height:32px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:12px;">Tipo de pausa</label>
                        <input type="text" name="registro_tipo[]" class="form-control" placeholder="Ej: Estiramiento, Relajacion visual..." required>
                    </div>
                    <div>
                        <label class="form-label" style="font-size:12px;">Foto evidencia</label>
                        <div class="photo-input-group">
                            <input type="file" name="registro_imagen[]" class="file-preview" accept="image/*" style="display:none;">
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i> Galeria</button>
                            </div>
                            <div class="preview-img mt-1"></div>
                        </div>
                    </div>
                </div>
            </div>`;
        document.getElementById('registrosContainer').insertAdjacentHTML('beforeend', html);
        updateRegistros();
        const sec = document.getElementById('secRegistros');
        if (!sec.classList.contains('show')) new bootstrap.Collapse(sec, { toggle: true });
    });

    document.addEventListener('click', function(e) {
        const galleryBtn = e.target.closest('.btn-photo-gallery');
        if (!galleryBtn) return;
        const group = galleryBtn.closest('.photo-input-group');
        group.querySelector('input[type="file"]').click();
    });

    document.addEventListener('change', function(e) {
        if (!e.target.classList.contains('file-preview')) return;
        const previewDiv = e.target.closest('.photo-input-group')?.querySelector('.preview-img');
        if (!previewDiv) return;
        previewDiv.innerHTML = '';
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = ev => {
                previewDiv.innerHTML = '<img src="' + ev.target.result + '" class="img-fluid rounded" style="max-height:80px; object-fit:cover; border:2px solid #28a745;" onclick="openPhoto(this.src)">' +
                    '<div style="font-size:11px; color:#28a745; margin-top:2px;"><i class="fas fa-check-circle"></i> Foto lista</div>';
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    document.getElementById('btnFinalizar').addEventListener('click', function(e) {
        const registros = document.querySelectorAll('.registro-row').length;
        const cliente = document.getElementById('selectCliente').value;
        if (!cliente || registros === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning', title: 'Datos incompletos',
                html: (!cliente ? '- Seleccionar un cliente<br>' : '') + (registros === 0 ? '- Agregar al menos 1 registro' : ''),
                confirmButtonColor: '#bd9751',
            });
            return;
        }
        e.preventDefault();
        Swal.fire({
            title: 'Finalizar pausa activa?', html: 'Se generara el PDF.',
            icon: 'question', showCancelButton: true,
            confirmButtonText: 'Si, finalizar', cancelButtonText: 'Cancelar', confirmButtonColor: '#bd9751',
        }).then(result => {
            if (result.isConfirmed) {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = 'finalizar'; input.value = '1';
                document.getElementById('pausaForm').appendChild(input);
                document.getElementById('pausaForm').submit();
            }
        });
    });
});
</script>
