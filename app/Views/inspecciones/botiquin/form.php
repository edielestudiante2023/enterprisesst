<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? '/inspecciones/botiquin/update/' . $inspeccion['id'] : '/inspecciones/botiquin/store';

// Agrupar elementos por grupo
$grupos = [];
foreach ($elementos as $clave => $config) {
    $grupos[$config['grupo']][$clave] = $config;
}

$estadosElemento = ['BUEN ESTADO', 'ESTADO REGULAR', 'MAL ESTADO', 'SIN EXISTENCIAS', 'VENCIDO', 'NO APLICA'];
$estadosEquipo = ['BUEN ESTADO', 'ESTADO REGULAR', 'MAL ESTADO'];
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="botForm">
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

        <div class="accordion mt-2" id="accordionBot">

            <!-- DATOS GENERALES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accordionBot">
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
                                <label class="form-label">Ubicacion botiquin</label>
                                <input type="text" name="ubicacion_botiquin" class="form-control"
                                    placeholder="Ej: Porteria principal"
                                    value="<?= esc($inspeccion['ubicacion_botiquin'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Fotos del botiquín -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Foto 1 del botiquin</label>
                                <div class="photo-input-group">
                                    <input type="file" name="foto_1" class="file-preview" accept="image/*" style="display:none;">
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera" style="font-size:11px; padding:2px 6px;"><i class="fas fa-camera"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery" style="font-size:11px; padding:2px 6px;"><i class="fas fa-images"></i></button>
                                    </div>
                                    <div class="preview-img mt-1">
                                        <?php if (!empty($inspeccion['foto_1'])): ?>
                                        <img src="/<?= esc($inspeccion['foto_1']) ?>" class="img-fluid rounded" style="max-height:60px; object-fit:cover; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Foto 2 del botiquin</label>
                                <div class="photo-input-group">
                                    <input type="file" name="foto_2" class="file-preview" accept="image/*" style="display:none;">
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera" style="font-size:11px; padding:2px 6px;"><i class="fas fa-camera"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery" style="font-size:11px; padding:2px 6px;"><i class="fas fa-images"></i></button>
                                    </div>
                                    <div class="preview-img mt-1">
                                        <?php if (!empty($inspeccion['foto_2'])): ?>
                                        <img src="/<?= esc($inspeccion['foto_2']) ?>" class="img-fluid rounded" style="max-height:60px; object-fit:cover; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4 Preguntas SI/NO -->
                        <div class="row g-2 mb-3">
                            <?php
                            $preguntas = [
                                'instalado_pared'  => 'Instalado en la pared?',
                                'libre_obstaculos' => 'Libre de obstaculos?',
                                'lugar_visible'    => 'Localizado en lugar visible?',
                                'con_senalizacion' => 'Con senalizacion?',
                            ];
                            foreach ($preguntas as $campo => $pregunta):
                                $valor = $inspeccion[$campo] ?? 'SI';
                            ?>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;"><?= $pregunta ?></label>
                                <div class="d-flex gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="<?= $campo ?>" value="SI" <?= $valor === 'SI' ? 'checked' : '' ?>>
                                        <label class="form-check-label" style="font-size:12px;">SI</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="<?= $campo ?>" value="NO" <?= $valor === 'NO' ? 'checked' : '' ?>>
                                        <label class="form-check-label" style="font-size:12px;">NO</label>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Tipo y estado -->
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Tipo de botiquin</label>
                                <select name="tipo_botiquin" class="form-select form-select-sm">
                                    <?php foreach (['LONA', 'METALICO'] as $tipo): ?>
                                    <option value="<?= $tipo ?>" <?= ($inspeccion['tipo_botiquin'] ?? 'LONA') === $tipo ? 'selected' : '' ?>><?= $tipo ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Estado del botiquin</label>
                                <select name="estado_botiquin" class="form-select form-select-sm">
                                    <?php foreach ($estadosEquipo as $est): ?>
                                    <option value="<?= $est ?>" <?= ($inspeccion['estado_botiquin'] ?? 'BUEN ESTADO') === $est ? 'selected' : '' ?>><?= $est ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRUPOS DE ELEMENTOS -->
            <?php
            $secNum = 1;
            foreach ($grupos as $grupoNombre => $items):
                $secId = 'secGrupo' . $secNum;
                $isInmovilizacion = ($grupoNombre === 'Equipos de inmovilizacion');
            ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $secId ?>">
                        <?= esc($grupoNombre) ?> (<?= count($items) ?>)
                    </button>
                </h2>
                <div id="<?= $secId ?>" class="accordion-collapse collapse" data-bs-parent="#accordionBot">
                    <div class="accordion-body p-2">

                    <?php if ($isInmovilizacion): ?>
                        <!-- Tabla espinal -->
                        <?php
                        $claveTE = 'tabla_espinal';
                        $configTE = $items[$claveTE];
                        $dataTE = $elementosData[$claveTE] ?? null;
                        ?>
                        <div class="card mb-2" style="border-left:3px solid #17a2b8;">
                            <div class="card-body p-2">
                                <strong style="font-size:12px;"><?= esc($configTE['label']) ?></strong>
                                <div class="row g-2 mt-1">
                                    <div class="col-4">
                                        <label class="form-label" style="font-size:11px;">Cantidad (min: <?= $configTE['min'] ?>)</label>
                                        <input type="number" name="elem_<?= $claveTE ?>_cantidad" class="form-control form-control-sm" min="0" value="<?= $dataTE['cantidad'] ?? 0 ?>">
                                    </div>
                                    <div class="col-8">
                                        <label class="form-label" style="font-size:11px;">Estado</label>
                                        <select name="elem_<?= $claveTE ?>_estado" class="form-select form-select-sm" style="font-size:12px;">
                                            <?php foreach ($estadosElemento as $est): ?>
                                            <option value="<?= $est ?>" <?= ($dataTE['estado'] ?? 'BUEN ESTADO') === $est ? 'selected' : '' ?>><?= $est ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <!-- Foto tabla espinal -->
                                <div class="row g-2 mt-1">
                                    <div class="col-6">
                                        <label class="form-label" style="font-size:11px;">Foto tabla espinal</label>
                                        <div class="photo-input-group">
                                            <input type="file" name="foto_tabla_espinal" class="file-preview" accept="image/*" style="display:none;">
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera" style="font-size:11px; padding:2px 6px;"><i class="fas fa-camera"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery" style="font-size:11px; padding:2px 6px;"><i class="fas fa-images"></i></button>
                                            </div>
                                            <div class="preview-img mt-1">
                                                <?php if (!empty($inspeccion['foto_tabla_espinal'])): ?>
                                                <img src="/<?= esc($inspeccion['foto_tabla_espinal']) ?>" class="img-fluid rounded" style="max-height:60px; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" style="font-size:11px;">Observaciones</label>
                                        <input type="text" name="obs_tabla_espinal" class="form-control form-control-sm" value="<?= esc($inspeccion['obs_tabla_espinal'] ?? '') ?>" placeholder="Obs...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Collares cervicales -->
                        <div class="card mb-2" style="border-left:3px solid #17a2b8;">
                            <div class="card-body p-2">
                                <strong style="font-size:12px;">Collares cervicales</strong>
                                <div class="row g-2 mt-1">
                                    <?php
                                    foreach (['collar_adulto', 'collar_nino'] as $claveC):
                                        $configC = $items[$claveC];
                                        $dataC = $elementosData[$claveC] ?? null;
                                    ?>
                                    <div class="col-6">
                                        <label class="form-label" style="font-size:11px;"><?= esc($configC['label']) ?> (min: <?= $configC['min'] ?>)</label>
                                        <input type="number" name="elem_<?= $claveC ?>_cantidad" class="form-control form-control-sm" min="0" value="<?= $dataC['cantidad'] ?? 0 ?>">
                                        <input type="hidden" name="elem_<?= $claveC ?>_estado" value="BUEN ESTADO">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="row g-2 mt-1">
                                    <div class="col-6">
                                        <label class="form-label" style="font-size:11px;">Estado collares</label>
                                        <select name="estado_collares" class="form-select form-select-sm" style="font-size:12px;">
                                            <?php foreach ($estadosEquipo as $est): ?>
                                            <option value="<?= $est ?>" <?= ($inspeccion['estado_collares'] ?? 'BUEN ESTADO') === $est ? 'selected' : '' ?>><?= $est ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" style="font-size:11px;">Foto collares</label>
                                        <div class="photo-input-group">
                                            <input type="file" name="foto_collares" class="file-preview" accept="image/*" style="display:none;">
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera" style="font-size:11px; padding:2px 6px;"><i class="fas fa-camera"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery" style="font-size:11px; padding:2px 6px;"><i class="fas fa-images"></i></button>
                                            </div>
                                            <div class="preview-img mt-1">
                                                <?php if (!empty($inspeccion['foto_collares'])): ?>
                                                <img src="/<?= esc($inspeccion['foto_collares']) ?>" class="img-fluid rounded" style="max-height:60px; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Inmovilizadores -->
                        <div class="card mb-2" style="border-left:3px solid #17a2b8;">
                            <div class="card-body p-2">
                                <strong style="font-size:12px;">Inmovilizadores / Ferulas</strong>
                                <div class="row g-2 mt-1">
                                    <?php
                                    foreach (['inmov_sup_adulto', 'inmov_inf_adulto', 'inmov_sup_nino', 'inmov_inf_nino'] as $claveI):
                                        $configI = $items[$claveI];
                                        $dataI = $elementosData[$claveI] ?? null;
                                    ?>
                                    <div class="col-6">
                                        <label class="form-label" style="font-size:11px;"><?= esc($configI['label']) ?> (min: <?= $configI['min'] ?>)</label>
                                        <input type="number" name="elem_<?= $claveI ?>_cantidad" class="form-control form-control-sm" min="0" value="<?= $dataI['cantidad'] ?? 0 ?>">
                                        <input type="hidden" name="elem_<?= $claveI ?>_estado" value="BUEN ESTADO">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="row g-2 mt-1">
                                    <div class="col-6">
                                        <label class="form-label" style="font-size:11px;">Estado inmovilizadores</label>
                                        <select name="estado_inmovilizadores" class="form-select form-select-sm" style="font-size:12px;">
                                            <?php foreach ($estadosEquipo as $est): ?>
                                            <option value="<?= $est ?>" <?= ($inspeccion['estado_inmovilizadores'] ?? 'BUEN ESTADO') === $est ? 'selected' : '' ?>><?= $est ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" style="font-size:11px;">Foto inmovilizadores</label>
                                        <div class="photo-input-group">
                                            <input type="file" name="foto_inmovilizadores" class="file-preview" accept="image/*" style="display:none;">
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera" style="font-size:11px; padding:2px 6px;"><i class="fas fa-camera"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery" style="font-size:11px; padding:2px 6px;"><i class="fas fa-images"></i></button>
                                            </div>
                                            <div class="preview-img mt-1">
                                                <?php if (!empty($inspeccion['foto_inmovilizadores'])): ?>
                                                <img src="/<?= esc($inspeccion['foto_inmovilizadores']) ?>" class="img-fluid rounded" style="max-height:60px; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- Elementos estándar del grupo -->
                        <?php foreach ($items as $clave => $config):
                            $data = $elementosData[$clave] ?? null;
                        ?>
                        <div class="card mb-2" style="border-left:3px solid <?= $config['venc'] ? '#ffc107' : '#6c757d' ?>;">
                            <div class="card-body p-2">
                                <strong style="font-size:12px;"><?= esc($config['label']) ?></strong>
                                <?php if (!empty($config['medicamento'])): ?>
                                <span class="badge bg-info" style="font-size:10px;">Medicamento</span>
                                <?php endif; ?>
                                <div class="row g-2 mt-1">
                                    <div class="col-4">
                                        <label class="form-label" style="font-size:11px;">Cantidad (min: <?= $config['min'] ?>)</label>
                                        <input type="number" name="elem_<?= $clave ?>_cantidad" class="form-control form-control-sm" min="0" value="<?= $data['cantidad'] ?? 0 ?>">
                                    </div>
                                    <div class="<?= $config['venc'] ? 'col-4' : 'col-8' ?>">
                                        <label class="form-label" style="font-size:11px;">Estado</label>
                                        <select name="elem_<?= $clave ?>_estado" class="form-select form-select-sm" style="font-size:12px;">
                                            <?php foreach ($estadosElemento as $est): ?>
                                            <option value="<?= $est ?>" <?= ($data['estado'] ?? 'BUEN ESTADO') === $est ? 'selected' : '' ?>><?= $est ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php if ($config['venc']): ?>
                                    <div class="col-4">
                                        <label class="form-label" style="font-size:11px;">Vencimiento</label>
                                        <input type="date" name="elem_<?= $clave ?>_vencimiento" class="form-control form-control-sm" value="<?= $data['fecha_vencimiento'] ?? '' ?>">
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    </div>
                </div>
            </div>
            <?php $secNum++; endforeach; ?>

        </div><!-- /accordion -->

        <!-- Recomendaciones -->
        <div class="card mt-3">
            <div class="card-body p-2">
                <label class="form-label" style="font-size:13px;">Recomendaciones de la inspeccion</label>
                <textarea name="recomendaciones" class="form-control" rows="3" placeholder="Recomendaciones..."><?= esc($inspeccion['recomendaciones'] ?? '') ?></textarea>
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
            html: 'Se generaran los pendientes, el PDF, y no podras editar despues.',
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
                document.getElementById('botForm').appendChild(input);
                document.getElementById('botForm').submit();
            }
        });
    });

    // ============================================================
    // AUTOGUARDADO EN LOCALSTORAGE
    // ============================================================
    const STORAGE_KEY = 'bot_draft_<?= $inspeccion['id'] ?? 'new' ?>';
    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;

    function collectFormData() {
        const data = {};
        data.id_cliente = document.getElementById('selectCliente').value;
        data.fecha_inspeccion = document.querySelector('[name="fecha_inspeccion"]')?.value || '';
        data.ubicacion_botiquin = document.querySelector('[name="ubicacion_botiquin"]')?.value || '';
        data.recomendaciones = document.querySelector('[name="recomendaciones"]')?.value || '';

        // Preguntas SI/NO
        ['instalado_pared','libre_obstaculos','lugar_visible','con_senalizacion'].forEach(f => {
            const checked = document.querySelector('[name="'+f+'"]:checked');
            data[f] = checked ? checked.value : 'SI';
        });

        data.tipo_botiquin = document.querySelector('[name="tipo_botiquin"]')?.value || '';
        data.estado_botiquin = document.querySelector('[name="estado_botiquin"]')?.value || '';
        data.obs_tabla_espinal = document.querySelector('[name="obs_tabla_espinal"]')?.value || '';
        data.estado_collares = document.querySelector('[name="estado_collares"]')?.value || '';
        data.estado_inmovilizadores = document.querySelector('[name="estado_inmovilizadores"]')?.value || '';

        // Elementos
        data.elementos = {};
        document.querySelectorAll('[name^="elem_"][name$="_cantidad"]').forEach(input => {
            const match = input.name.match(/^elem_(.+)_cantidad$/);
            if (match) {
                const clave = match[1];
                data.elementos[clave] = {
                    cantidad: input.value,
                    estado: document.querySelector('[name="elem_'+clave+'_estado"]')?.value || '',
                    vencimiento: document.querySelector('[name="elem_'+clave+'_vencimiento"]')?.value || '',
                };
            }
        });

        data._savedAt = new Date().toISOString();
        return data;
    }

    function saveToLocal() {
        try {
            const data = collectFormData();
            if (data.id_cliente || Object.keys(data.elementos).length) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
                document.getElementById('autoSaveStatus').innerHTML =
                    '<i class="fas fa-check-circle text-success"></i> Guardado ' + new Date().toLocaleTimeString();
            }
        } catch(e) {}
    }

    function restoreFromLocal(data) {
        if (data.id_cliente) window._pendingClientRestore = data.id_cliente;
        if (data.fecha_inspeccion) document.querySelector('[name="fecha_inspeccion"]').value = data.fecha_inspeccion;
        if (data.ubicacion_botiquin) document.querySelector('[name="ubicacion_botiquin"]').value = data.ubicacion_botiquin;
        if (data.recomendaciones) document.querySelector('[name="recomendaciones"]').value = data.recomendaciones;

        ['instalado_pared','libre_obstaculos','lugar_visible','con_senalizacion'].forEach(f => {
            if (data[f]) {
                const radio = document.querySelector('[name="'+f+'"][value="'+data[f]+'"]');
                if (radio) radio.checked = true;
            }
        });

        if (data.tipo_botiquin) document.querySelector('[name="tipo_botiquin"]').value = data.tipo_botiquin;
        if (data.estado_botiquin) document.querySelector('[name="estado_botiquin"]').value = data.estado_botiquin;
        if (data.obs_tabla_espinal) document.querySelector('[name="obs_tabla_espinal"]').value = data.obs_tabla_espinal;
        if (data.estado_collares) document.querySelector('[name="estado_collares"]').value = data.estado_collares;
        if (data.estado_inmovilizadores) document.querySelector('[name="estado_inmovilizadores"]').value = data.estado_inmovilizadores;

        if (data.elementos) {
            for (const [clave, vals] of Object.entries(data.elementos)) {
                const cantInput = document.querySelector('[name="elem_'+clave+'_cantidad"]');
                const estInput = document.querySelector('[name="elem_'+clave+'_estado"]');
                const vencInput = document.querySelector('[name="elem_'+clave+'_vencimiento"]');
                if (cantInput && vals.cantidad) cantInput.value = vals.cantidad;
                if (estInput && vals.estado) estInput.value = vals.estado;
                if (vencInput && vals.vencimiento) vencInput.value = vals.vencimiento;
            }
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
    document.getElementById('botForm').addEventListener('input', function() {
        clearTimeout(window._autoSaveTimeout);
        window._autoSaveTimeout = setTimeout(saveToLocal, 2000);
    });
    $('#selectCliente').on('change', function() { setTimeout(saveToLocal, 500); });
    document.getElementById('botForm').addEventListener('submit', function() {
        localStorage.removeItem(STORAGE_KEY);
    });
});
</script>
