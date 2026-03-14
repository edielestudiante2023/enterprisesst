<?php
$isEdit = !empty($acta);
$action = $isEdit ? '/inspecciones/acta-visita/update/' . $acta['id'] : '/inspecciones/acta-visita/store';
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="actaForm">
        <?= csrf_field() ?>

        <!-- Errores de validacion -->
        <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;">
            <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Accordion de secciones -->
        <div class="accordion mt-2" id="accordionActa">

            <!-- DATOS GENERALES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accordionActa">
                    <div class="accordion-body">
                        <!-- Cliente -->
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select name="id_cliente" id="selectCliente" class="form-select" required>
                                <option value="">Seleccionar cliente...</option>
                            </select>
                        </div>

                        <!-- Fecha y Hora -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Fecha *</label>
                                <input type="date" name="fecha_visita" class="form-control"
                                    value="<?= $acta['fecha_visita'] ?? date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Hora *</label>
                                <input type="time" name="hora_visita" class="form-control"
                                    value="<?= $acta['hora_visita'] ?? date('H:i') ?>" required>
                            </div>
                        </div>

                        <!-- Motivo -->
                        <div class="mb-3">
                            <label class="form-label">Motivo *</label>
                            <input type="text" name="motivo" class="form-control"
                                value="<?= esc($acta['motivo'] ?? '') ?>"
                                placeholder="Ej: Visita mensual de seguimiento" required>
                        </div>

                        <!-- Modalidad -->
                        <div class="mb-3">
                            <label class="form-label">Modalidad</label>
                            <select name="modalidad" class="form-select">
                                <option value="Presencial" <?= ($acta['modalidad'] ?? '') === 'Presencial' ? 'selected' : '' ?>>Presencial</option>
                                <option value="Virtual" <?= ($acta['modalidad'] ?? '') === 'Virtual' ? 'selected' : '' ?>>Virtual</option>
                                <option value="Mixta" <?= ($acta['modalidad'] ?? '') === 'Mixta' ? 'selected' : '' ?>>Mixta</option>
                            </select>
                        </div>

                        <!-- Ubicacion GPS -->
                        <input type="hidden" name="ubicacion_gps" id="ubicacionGps" value="<?= esc($acta['ubicacion_gps'] ?? '') ?>">
                        <div class="mb-0" id="gpsStatus" style="font-size:13px; color:#999;">
                            <i class="fas fa-map-marker-alt"></i> Capturando ubicacion...
                        </div>
                    </div>
                </div>
            </div>

            <!-- INTEGRANTES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secIntegrantes">
                        Integrantes (<span id="countIntegrantes"><?= count($integrantes) ?></span>)
                    </button>
                </h2>
                <div id="secIntegrantes" class="accordion-collapse collapse" data-bs-parent="#accordionActa">
                    <div class="accordion-body">
                        <div id="integrantesContainer">
                            <?php if (!empty($integrantes)): ?>
                                <?php foreach ($integrantes as $integrante): ?>
                                <div class="row g-2 mb-2 integrante-row">
                                    <div class="col-5">
                                        <input type="text" name="integrante_nombre[]" class="form-control" placeholder="Nombre" value="<?= esc($integrante['nombre']) ?>">
                                    </div>
                                    <div class="col-5">
                                        <select name="integrante_rol[]" class="form-select">
                                            <option value="">Rol...</option>
                                            <?php foreach (['CLIENTE', 'CONSULTOR CYCLOID TALENT'] as $rol): ?>
                                            <option value="<?= $rol ?>" <?= $integrante['rol'] === $rol ? 'selected' : '' ?>><?= $rol ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-2 text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-height:44px;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddIntegrante">
                            <i class="fas fa-plus"></i> Agregar integrante
                        </button>
                    </div>
                </div>
            </div>

            <!-- TEMAS -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secTemas">
                        Temas (<span id="countTemas"><?= count($temas) ?></span>)
                    </button>
                </h2>
                <div id="secTemas" class="accordion-collapse collapse" data-bs-parent="#accordionActa">
                    <div class="accordion-body">
                        <div id="temasContainer">
                            <?php if (!empty($temas)): ?>
                                <?php foreach ($temas as $tema): ?>
                                <div class="mb-2 tema-row d-flex gap-2">
                                    <textarea name="tema[]" class="form-control" rows="2" placeholder="Descripcion del tema"><?= esc($tema['descripcion']) ?></textarea>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-width:44px;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddTema">
                            <i class="fas fa-plus"></i> Agregar tema
                        </button>
                    </div>
                </div>
            </div>

            <!-- TEMAS ABIERTOS (auto) -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secTemasAbiertos">
                        Temas Abiertos y Vencidos (auto)
                    </button>
                </h2>
                <div id="secTemasAbiertos" class="accordion-collapse collapse" data-bs-parent="#accordionActa">
                    <div class="accordion-body" id="temasAbiertosContent">
                        <p class="text-muted" style="font-size:13px;">Selecciona un cliente para ver sus pendientes y mantenimientos.</p>
                    </div>
                </div>
            </div>

            <!-- ACTIVIDADES PTA DEL MES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secPta">
                        Actividades PTA del Mes (<span id="countPta">0</span>)
                    </button>
                </h2>
                <div id="secPta" class="accordion-collapse collapse" data-bs-parent="#accordionActa">
                    <div class="accordion-body" id="ptaContent">
                        <p class="text-muted" style="font-size:13px;">Selecciona un cliente y fecha para ver las actividades PTA.</p>
                    </div>
                </div>
            </div>

            <!-- OBSERVACIONES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secObs">
                        Observaciones
                    </button>
                </h2>
                <div id="secObs" class="accordion-collapse collapse" data-bs-parent="#accordionActa">
                    <div class="accordion-body">
                        <textarea name="observaciones" class="form-control" rows="4" placeholder="Observaciones generales..."><?= esc($acta['observaciones'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- COMPROMISOS -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secCompromisos">
                        Compromisos (<span id="countCompromisos"><?= count($compromisos ?? []) ?></span>)
                    </button>
                </h2>
                <div id="secCompromisos" class="accordion-collapse collapse" data-bs-parent="#accordionActa">
                    <div class="accordion-body">
                        <div id="compromisosContainer">
                            <?php if (!empty($compromisos)): ?>
                                <?php foreach ($compromisos as $comp): ?>
                                <div class="card mb-2 compromiso-row">
                                    <div class="card-body p-2">
                                        <input type="text" name="compromiso_actividad[]" class="form-control mb-1" placeholder="Actividad" value="<?= esc($comp['tarea_actividad']) ?>">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <input type="date" name="compromiso_fecha[]" class="form-control" value="<?= $comp['fecha_cierre'] ?? '' ?>">
                                            </div>
                                            <div class="col-5">
                                                <input type="text" name="compromiso_responsable[]" class="form-control" placeholder="Responsable" value="<?= esc($comp['responsable'] ?? '') ?>">
                                            </div>
                                            <div class="col-1 text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-height:44px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddCompromiso">
                            <i class="fas fa-plus"></i> Agregar compromiso
                        </button>
                    </div>
                </div>
            </div>

            <!-- FOTOS Y SOPORTES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secFotos">
                        Fotos y Soportes
                    </button>
                </h2>
                <div id="secFotos" class="accordion-collapse collapse" data-bs-parent="#accordionActa">
                    <div class="accordion-body">
                        <!-- Fotos existentes -->
                        <?php if (!empty($fotos)): ?>
                        <div class="row g-2 mb-3">
                            <?php foreach ($fotos as $foto): ?>
                            <div class="col-4">
                                <img src="<?= base_url($foto['ruta_archivo']) ?>" class="img-fluid rounded" style="max-height:120px; object-fit:cover; width:100%;">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <label class="form-label">Agregar fotos</label>
                        <div class="photo-input-group">
                            <input type="file" name="fotos[]" class="file-preview" accept="image/*" style="display:none;" multiple>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera"><i class="fas fa-camera"></i> Camara</button>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery"><i class="fas fa-images"></i> Galeria</button>
                            </div>
                            <div class="preview-img mt-2 d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /accordion -->

        <!-- Indicador autoguardado -->
        <div id="autoSaveStatus" style="font-size:12px; color:#999; text-align:center; padding:4px 0;">
            <i class="fas fa-cloud"></i> Autoguardado activado
        </div>

        <!-- Botones de accion -->
        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <button type="button" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;" id="btnIrFirmas">
                <i class="fas fa-signature"></i> Guardar e ir a firmas
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clienteId = '<?= $idCliente ?? '' ?>';

    // --- Select2 para clientes ---
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

            // Restaurar cliente desde autoguardado si aplica
            if (window._pendingClientRestore) {
                $('#selectCliente').val(window._pendingClientRestore).trigger('change');
                window._pendingClientRestore = null;
            }

            // Si ya hay cliente seleccionado, cargar temas abiertos
            if (clienteId) loadTemasAbiertos(clienteId);
        }
    });

    // Cargar temas abiertos y PTA al cambiar cliente
    $('#selectCliente').on('change', function() {
        const id = this.value;
        if (id) {
            loadTemasAbiertos(id);
            loadPtaActividades();
        }
    });

    // Recargar PTA al cambiar fecha
    document.querySelector('[name="fecha_visita"]').addEventListener('change', function() {
        loadPtaActividades();
    });

    function loadTemasAbiertos(idCliente) {
        const container = document.getElementById('temasAbiertosContent');
        container.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Cargando...</p>';

        Promise.all([
            fetch('/inspecciones/api/pendientes/' + idCliente).then(r => r.json()),
            fetch('/inspecciones/api/mantenimientos/' + idCliente).then(r => r.json()),
        ]).then(([pendientes, mantenimientos]) => {
            let html = '';

            // Mantenimientos
            html += '<h6 style="font-size:14px; font-weight:700;">Mantenimientos por vencer</h6>';
            if (mantenimientos.length === 0) {
                html += '<p style="font-size:13px; color:green;"><i class="fas fa-check-circle"></i> Sin mantenimientos por vencer</p>';
            } else {
                html += '<ul style="font-size:13px; padding-left:20px;">';
                mantenimientos.forEach(m => {
                    html += '<li>' + (m.descripcion_mantenimiento || 'Mantenimiento') + ' - Vence: ' + m.fecha_vencimiento + '</li>';
                });
                html += '</ul>';
            }

            // Pendientes
            html += '<h6 style="font-size:14px; font-weight:700; margin-top:12px;">Pendientes abiertos</h6>';
            if (pendientes.length === 0) {
                html += '<p style="font-size:13px; color:green;"><i class="fas fa-check-circle"></i> Sin pendientes abiertos</p>';
            } else {
                html += '<ul style="font-size:13px; padding-left:20px;">';
                pendientes.forEach(p => {
                    var fecha = p.fecha_asignacion ? p.fecha_asignacion.split('-').reverse().join('/') : '';
                    var cierre = p.fecha_cierre ? ' → Cierre: ' + p.fecha_cierre.split('-').reverse().join('/') : '';
                    html += '<li>' + p.tarea_actividad + ' - ' + (p.responsable || '') + '<br><small style="color:#888;">Asignado: ' + fecha + cierre + ' (' + p.conteo_dias + ' dias)</small></li>';
                });
                html += '</ul>';
            }

            container.innerHTML = html;
        });
    }

    // --- PTA Actividades ---
    const actaId = '<?= $acta['id'] ?? '' ?>';

    function loadPtaActividades() {
        const idCliente = document.getElementById('selectCliente').value;
        const fechaVisita = document.querySelector('[name="fecha_visita"]').value;
        const container = document.getElementById('ptaContent');

        if (!idCliente || !fechaVisita) {
            container.innerHTML = '<p class="text-muted" style="font-size:13px;">Selecciona un cliente y fecha para ver las actividades PTA.</p>';
            document.getElementById('countPta').textContent = '0';
            return;
        }

        container.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Cargando actividades PTA...</p>';

        let url = '/inspecciones/acta-visita/api/pta-actividades?id_cliente=' + idCliente + '&fecha_visita=' + fechaVisita;
        if (actaId) url += '&id_acta=' + actaId;

        fetch(url).then(r => r.json()).then(data => {
            const actividades = data.actividades || [];
            const links = data.links || {};

            if (actividades.length === 0) {
                container.innerHTML = '<p class="text-muted" style="font-size:13px;"><i class="fas fa-check-circle text-success"></i> No hay actividades PTA abiertas para este periodo.</p>';
                document.getElementById('countPta').textContent = '0';
                return;
            }

            document.getElementById('countPta').textContent = actividades.length;

            let html = '<div class="list-group list-group-flush">';
            actividades.forEach(act => {
                const linkData = links[act.id_ptacliente];
                const yaCerradaPrev = linkData && parseInt(linkData.cerrada) === 1;
                const checkedAttr = yaCerradaPrev ? 'checked disabled' : '';
                const badgeRezagada = act.rezagada ? ' <span class="badge bg-warning text-dark" style="font-size:10px;">REZAGADA</span>' : '';
                const badgeCerrada = yaCerradaPrev ? ' <span class="badge bg-success" style="font-size:10px;">CERRADA</span>' : '';

                html += '<label class="list-group-item d-flex align-items-start gap-2" style="font-size:13px; cursor:pointer;">';
                html += '<input type="hidden" name="pta_actividad_id[]" value="' + act.id_ptacliente + '">';
                html += '<input type="checkbox" name="pta_actividad_checked[]" value="' + act.id_ptacliente + '" class="form-check-input mt-1 pta-checkbox" ' + checkedAttr + ' style="min-width:18px;">';
                html += '<div>';
                html += '<strong>' + (act.numeral_plandetrabajo || '') + '</strong> ' + (act.actividad_plandetrabajo || '');
                html += badgeRezagada + badgeCerrada;
                html += '<br><small class="text-muted">Fecha propuesta: ' + act.fecha_propuesta + '</small>';
                html += '</div>';
                html += '</label>';
            });
            html += '</div>';
            html += '<small class="text-muted d-block mt-2" style="font-size:11px;"><i class="fas fa-info-circle"></i> Marca las actividades que cerraste en esta visita. Las no marcadas pediran justificacion al guardar.</small>';

            container.innerHTML = html;
        }).catch(() => {
            container.innerHTML = '<p class="text-danger" style="font-size:13px;"><i class="fas fa-exclamation-triangle"></i> Error cargando actividades PTA.</p>';
        });
    }

    // Cargar PTA al inicio si ya hay cliente
    if (clienteId) {
        setTimeout(loadPtaActividades, 500);
    }

    /**
     * SweetAlert encadenado: pide justificacion por cada actividad PTA no marcada
     */
    function askPtaJustifications(callback) {
        const allIds = document.querySelectorAll('input[name="pta_actividad_id[]"]');
        const checkedIds = Array.from(document.querySelectorAll('input[name="pta_actividad_checked[]"]:checked')).map(c => c.value);

        // Filtrar solo las que no estan checked y no estan disabled (ya cerradas)
        const unchecked = [];
        allIds.forEach(input => {
            const id = input.value;
            const checkbox = input.nextElementSibling;
            if (!checkedIds.includes(id) && !checkbox.disabled) {
                const label = input.closest('label');
                const texto = label ? label.querySelector('div').textContent.trim().split('\n')[0] : 'Actividad ' + id;
                unchecked.push({ id, texto });
            }
        });

        if (unchecked.length === 0) {
            callback({});
            return;
        }

        const justificaciones = {};
        let index = 0;

        function pedirSiguiente() {
            if (index >= unchecked.length) {
                callback(justificaciones);
                return;
            }

            const item = unchecked[index];
            Swal.fire({
                title: 'Justificacion requerida',
                html: '<div style="text-align:left; font-size:13px; margin-bottom:10px;"><strong>Actividad no cerrada:</strong><br>' + item.texto + '</div>',
                input: 'textarea',
                inputPlaceholder: 'Explica por que no se cerro esta actividad...',
                inputAttributes: { 'aria-label': 'Justificacion' },
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                confirmButtonText: (index + 1) + '/' + unchecked.length + ' Siguiente',
                confirmButtonColor: '#bd9751',
                allowOutsideClick: false,
                inputValidator: (value) => {
                    if (!value || !value.trim()) return 'La justificacion es obligatoria';
                }
            }).then(result => {
                if (result.isConfirmed) {
                    justificaciones[item.id] = result.value.trim();
                    index++;
                    pedirSiguiente();
                }
                // Si cancela, no llama callback (el submit se aborta)
            });
        }

        pedirSiguiente();
    }

    // --- Interceptor de submit para PTA justificaciones ---
    const actaForm = document.getElementById('actaForm');
    actaForm.addEventListener('submit', function(e) {
        // Si ya procesamos PTA, dejar pasar
        if (actaForm._ptaProcessed) {
            actaForm._ptaProcessed = false;
            return;
        }

        // Si no hay items PTA, dejar pasar
        const ptaItems = document.querySelectorAll('input[name="pta_actividad_id[]"]');
        if (ptaItems.length === 0) return;

        // Verificar si hay items no checked (excluyendo disabled)
        const checkedIds = Array.from(document.querySelectorAll('input[name="pta_actividad_checked[]"]:checked')).map(c => c.value);
        let hayNoMarcadas = false;
        ptaItems.forEach(input => {
            const checkbox = input.nextElementSibling;
            if (!checkedIds.includes(input.value) && !checkbox.disabled) {
                hayNoMarcadas = true;
            }
        });

        if (!hayNoMarcadas) return;

        e.preventDefault();

        askPtaJustifications(function(justificaciones) {
            // Inyectar hidden inputs con justificaciones
            Object.keys(justificaciones).forEach(id => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'pta_justificacion[' + id + ']';
                hidden.value = justificaciones[id];
                actaForm.appendChild(hidden);
            });

            actaForm._ptaProcessed = true;
            actaForm.requestSubmit();
        });
    });

    // --- Interceptor "Ir a firmas" para PTA ---
    const originalIrFirmasHandler = document.getElementById('btnIrFirmas');
    // Sobreescribimos el handler del boton "Ir a firmas"
    // El handler original ya esta definido, pero necesitamos interceptar PTA antes
    // Lo hacemos redefiniendo el click handler

    // --- GPS ---
    if (navigator.geolocation && !document.getElementById('ubicacionGps').value) {
        navigator.geolocation.getCurrentPosition(
            pos => {
                document.getElementById('ubicacionGps').value = pos.coords.latitude + ',' + pos.coords.longitude;
                document.getElementById('gpsStatus').innerHTML = '<i class="fas fa-map-marker-alt text-success"></i> Ubicacion capturada';
            },
            () => {
                document.getElementById('gpsStatus').innerHTML = '<i class="fas fa-map-marker-alt text-warning"></i> No se pudo capturar ubicacion';
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    } else if (document.getElementById('ubicacionGps').value) {
        document.getElementById('gpsStatus').innerHTML = '<i class="fas fa-map-marker-alt text-success"></i> Ubicacion capturada';
    }

    // --- Dynamic rows ---
    function updateCounts() {
        document.getElementById('countIntegrantes').textContent = document.querySelectorAll('.integrante-row').length;
        document.getElementById('countTemas').textContent = document.querySelectorAll('.tema-row').length;
        document.getElementById('countCompromisos').textContent = document.querySelectorAll('.compromiso-row').length;
    }

    // Remove row handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-row')) {
            e.target.closest('.integrante-row, .tema-row, .compromiso-row').remove();
            updateCounts();
        }
    });

    // Add integrante
    document.getElementById('btnAddIntegrante').addEventListener('click', function() {
        const roles = ['CLIENTE', 'CONSULTOR CYCLOID TALENT'];
        const options = roles.map(r => '<option value="' + r + '">' + r + '</option>').join('');
        const html = `
            <div class="row g-2 mb-2 integrante-row">
                <div class="col-5">
                    <input type="text" name="integrante_nombre[]" class="form-control" placeholder="Nombre">
                </div>
                <div class="col-5">
                    <select name="integrante_rol[]" class="form-select">
                        <option value="">Rol...</option>${options}
                    </select>
                </div>
                <div class="col-2 text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-height:44px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>`;
        document.getElementById('integrantesContainer').insertAdjacentHTML('beforeend', html);
        updateCounts();
    });

    // Add tema
    document.getElementById('btnAddTema').addEventListener('click', function() {
        const html = `
            <div class="mb-2 tema-row d-flex gap-2">
                <textarea name="tema[]" class="form-control" rows="2" placeholder="Descripcion del tema"></textarea>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-width:44px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>`;
        document.getElementById('temasContainer').insertAdjacentHTML('beforeend', html);
        updateCounts();
    });

    // Add compromiso
    document.getElementById('btnAddCompromiso').addEventListener('click', function() {
        const html = `
            <div class="card mb-2 compromiso-row">
                <div class="card-body p-2">
                    <input type="text" name="compromiso_actividad[]" class="form-control mb-1" placeholder="Actividad">
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="date" name="compromiso_fecha[]" class="form-control">
                        </div>
                        <div class="col-5">
                            <input type="text" name="compromiso_responsable[]" class="form-control" placeholder="Responsable">
                        </div>
                        <div class="col-1 text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-height:44px;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        document.getElementById('compromisosContainer').insertAdjacentHTML('beforeend', html);
        updateCounts();
    });

    // --- Validacion + submit independiente para "Guardar e ir a firmas" ---
    // Usa type="button" para evitar doble-activacion con el otro submit
    document.getElementById('btnIrFirmas').addEventListener('click', function() {
        const btn = this;
        const form = document.getElementById('actaForm');
        const cliente = document.getElementById('selectCliente').value;
        const temas = document.querySelectorAll('.tema-row').length;
        const integrantes = document.querySelectorAll('.integrante-row').length;

        if (!cliente || temas === 0 || integrantes === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Datos incompletos',
                html: 'Para ir a firmas necesitas al menos:<br><br>' +
                    (!cliente ? '- Seleccionar un cliente<br>' : '') +
                    (integrantes === 0 ? '- Agregar al menos 1 integrante<br>' : '') +
                    (temas === 0 ? '- Agregar al menos 1 tema<br>' : ''),
                confirmButtonColor: '#bd9751',
            });
            return;
        }

        // Inyectar hidden input para ir a firmas
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'ir_a_firmas';
        hidden.value = '1';
        form.appendChild(hidden);

        function doSubmit() {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            form._ptaProcessed = true;
            form.requestSubmit();
        }

        // Verificar si hay PTA no marcadas que necesitan justificacion
        const ptaItems = document.querySelectorAll('input[name="pta_actividad_id[]"]');
        const checkedIds = Array.from(document.querySelectorAll('input[name="pta_actividad_checked[]"]:checked')).map(c => c.value);
        let hayNoMarcadas = false;
        ptaItems.forEach(input => {
            const checkbox = input.nextElementSibling;
            if (!checkedIds.includes(input.value) && !checkbox.disabled) {
                hayNoMarcadas = true;
            }
        });

        if (ptaItems.length > 0 && hayNoMarcadas) {
            askPtaJustifications(function(justificaciones) {
                Object.keys(justificaciones).forEach(id => {
                    const h = document.createElement('input');
                    h.type = 'hidden';
                    h.name = 'pta_justificacion[' + id + ']';
                    h.value = justificaciones[id];
                    form.appendChild(h);
                });
                doSubmit();
            });
        } else {
            doSubmit();
        }
    });

    // ============================================================
    // AUTOGUARDADO EN LOCALSTORAGE
    // ============================================================
    const STORAGE_KEY = 'acta_draft_<?= $acta['id'] ?? 'new' ?>';
    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;

    function collectFormData() {
        const data = {};
        // Campos simples
        data.id_cliente = document.getElementById('selectCliente').value;
        data.fecha_visita = document.querySelector('[name="fecha_visita"]').value;
        data.hora_visita = document.querySelector('[name="hora_visita"]').value;
        data.motivo = document.querySelector('[name="motivo"]').value;
        data.modalidad = document.querySelector('[name="modalidad"]').value;
        data.observaciones = document.querySelector('[name="observaciones"]').value;
        data.ubicacion_gps = document.getElementById('ubicacionGps').value;

        // Integrantes
        data.integrantes = [];
        document.querySelectorAll('.integrante-row').forEach(row => {
            const nombre = row.querySelector('[name="integrante_nombre[]"]').value;
            const rol = row.querySelector('[name="integrante_rol[]"]').value;
            if (nombre || rol) data.integrantes.push({ nombre, rol });
        });

        // Temas
        data.temas = [];
        document.querySelectorAll('.tema-row textarea').forEach(ta => {
            if (ta.value) data.temas.push(ta.value);
        });

        // Compromisos
        data.compromisos = [];
        document.querySelectorAll('.compromiso-row').forEach(row => {
            const actividad = row.querySelector('[name="compromiso_actividad[]"]').value;
            const fecha = row.querySelector('[name="compromiso_fecha[]"]').value;
            const responsable = row.querySelector('[name="compromiso_responsable[]"]').value;
            if (actividad) data.compromisos.push({ actividad, fecha, responsable });
        });

        data._savedAt = new Date().toISOString();
        return data;
    }

    function saveToLocal() {
        try {
            const data = collectFormData();
            // Solo guardar si hay algo significativo
            if (data.id_cliente || data.motivo || data.integrantes.length || data.temas.length) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
                document.getElementById('autoSaveStatus').innerHTML =
                    '<i class="fas fa-check-circle text-success"></i> Guardado ' + new Date().toLocaleTimeString();
            }
        } catch(e) { /* localStorage lleno o no disponible */ }
    }

    function restoreFromLocal(data) {
        // Cliente - se restaura despues de que Select2 cargue
        if (data.id_cliente) {
            window._pendingClientRestore = data.id_cliente;
        }
        if (data.fecha_visita) document.querySelector('[name="fecha_visita"]').value = data.fecha_visita;
        if (data.hora_visita) document.querySelector('[name="hora_visita"]').value = data.hora_visita;
        if (data.motivo) document.querySelector('[name="motivo"]').value = data.motivo;
        if (data.modalidad) document.querySelector('[name="modalidad"]').value = data.modalidad;
        if (data.observaciones) document.querySelector('[name="observaciones"]').value = data.observaciones;
        if (data.ubicacion_gps) document.getElementById('ubicacionGps').value = data.ubicacion_gps;

        // Integrantes
        const roles = ['CLIENTE', 'CONSULTOR CYCLOID TALENT'];
        const roleOpts = roles.map(r => '<option value="' + r + '">' + r + '</option>').join('');
        (data.integrantes || []).forEach(int => {
            const html = '<div class="row g-2 mb-2 integrante-row"><div class="col-5"><input type="text" name="integrante_nombre[]" class="form-control" placeholder="Nombre" value="' + (int.nombre||'').replace(/"/g,'&quot;') + '"></div><div class="col-5"><select name="integrante_rol[]" class="form-select"><option value="">Rol...</option>' + roleOpts + '</select></div><div class="col-2 text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-height:44px;"><i class="fas fa-times"></i></button></div></div>';
            document.getElementById('integrantesContainer').insertAdjacentHTML('beforeend', html);
            if (int.rol) {
                const rows = document.querySelectorAll('.integrante-row');
                rows[rows.length - 1].querySelector('[name="integrante_rol[]"]').value = int.rol;
            }
        });

        // Temas
        (data.temas || []).forEach(t => {
            const html = '<div class="mb-2 tema-row d-flex gap-2"><textarea name="tema[]" class="form-control" rows="2" placeholder="Descripcion del tema">' + t.replace(/</g,'&lt;') + '</textarea><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-width:44px;"><i class="fas fa-times"></i></button></div>';
            document.getElementById('temasContainer').insertAdjacentHTML('beforeend', html);
        });

        // Compromisos
        (data.compromisos || []).forEach(c => {
            const html = '<div class="card mb-2 compromiso-row"><div class="card-body p-2"><input type="text" name="compromiso_actividad[]" class="form-control mb-1" placeholder="Actividad" value="' + (c.actividad||'').replace(/"/g,'&quot;') + '"><div class="row g-2"><div class="col-6"><input type="date" name="compromiso_fecha[]" class="form-control" value="' + (c.fecha||'') + '"></div><div class="col-5"><input type="text" name="compromiso_responsable[]" class="form-control" placeholder="Responsable" value="' + (c.responsable||'').replace(/"/g,'&quot;') + '"></div><div class="col-1 text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="min-height:44px;"><i class="fas fa-times"></i></button></div></div></div></div>';
            document.getElementById('compromisosContainer').insertAdjacentHTML('beforeend', html);
        });

        updateCounts();
    }

    // Verificar si hay borrador guardado (solo en creacion nueva sin datos previos del servidor)
    if (!isEdit) {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const data = JSON.parse(saved);
                const savedTime = new Date(data._savedAt);
                const hoursAgo = ((Date.now() - savedTime.getTime()) / 3600000).toFixed(1);

                // Solo ofrecer restaurar si tiene menos de 24 horas
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
                        if (result.isConfirmed) {
                            restoreFromLocal(data);
                        } else {
                            localStorage.removeItem(STORAGE_KEY);
                        }
                    });
                } else {
                    localStorage.removeItem(STORAGE_KEY);
                }
            }
        } catch(e) {}
    }

    // Auto-guardar cada 30 segundos
    setInterval(saveToLocal, 30000);

    // Guardar al cambiar cualquier campo
    document.getElementById('actaForm').addEventListener('input', function() {
        clearTimeout(window._autoSaveTimeout);
        window._autoSaveTimeout = setTimeout(saveToLocal, 2000);
    });
    $('#selectCliente').on('change', function() {
        setTimeout(saveToLocal, 500);
    });

    // Limpiar localStorage al enviar formulario exitosamente
    document.getElementById('actaForm').addEventListener('submit', function() {
        localStorage.removeItem(STORAGE_KEY);
    });

    // --- Botones Camara / Galeria ---
    document.addEventListener('click', function(e) {
        var cameraBtn = e.target.closest('.btn-photo-camera');
        var galleryBtn = e.target.closest('.btn-photo-gallery');
        if (!cameraBtn && !galleryBtn) return;

        var group = (cameraBtn || galleryBtn).closest('.photo-input-group');
        var input = group.querySelector('input[type="file"]');

        // No usar capture="environment": con ese atributo la foto NO se guarda en galeria del celular
        input.removeAttribute('capture');
        input.click();
    });

    // --- Preview de fotos al seleccionar/tomar ---
    document.addEventListener('change', function(e) {
        if (!e.target.classList.contains('file-preview')) return;
        var input = e.target;
        var group = input.closest('.photo-input-group');
        var previewDiv = group ? group.querySelector('.preview-img') : null;
        if (!previewDiv) return;

        previewDiv.innerHTML = '';
        if (input.files) {
            Array.from(input.files).forEach(function(file) {
                var reader = new FileReader();
                reader.onload = function(ev) {
                    previewDiv.insertAdjacentHTML('beforeend',
                        '<img src="' + ev.target.result + '" class="img-fluid rounded" style="max-height:80px; object-fit:cover; border:2px solid #28a745;">');
                };
                reader.readAsDataURL(file);
            });
        }
    });
});
</script>
