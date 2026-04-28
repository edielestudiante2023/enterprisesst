<?php
$isEdit = !empty($acta);
$ctx = $contexto ?? 'miembro';
$baseUrl = $ctx === 'consultor' ? 'inspecciones/acta-capacitacion' : 'miembro/acta-capacitacion';
$action = $isEdit
    ? site_url($baseUrl . '/update/' . $acta['id'])
    : site_url($baseUrl . '/store');
$tokenUrlBase = $ctx === 'consultor'
    ? 'inspecciones/acta-capacitacion/generar-token-firma/'
    : 'miembro/acta-capacitacion/generar-token-firma/';
$saveAsistUrlBase = $ctx === 'consultor'
    ? 'inspecciones/acta-capacitacion/asistente/save/'
    : 'miembro/acta-capacitacion/asistente/save/';
$emailUrlBase = $ctx === 'consultor'
    ? 'inspecciones/acta-capacitacion/asistente/enviar-email/'
    : 'miembro/acta-capacitacion/asistente/enviar-email/';
$deleteAsistUrlBase = $ctx === 'consultor'
    ? 'inspecciones/acta-capacitacion/asistente/delete/'
    : 'miembro/acta-capacitacion/asistente/delete/';
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" id="actaCapForm">
        <?= csrf_field() ?>

        <div class="accordion mt-2" id="accCap">
            <!-- Datos Generales -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accCap">
                    <div class="accordion-body">
                        <?php if ($ctx === 'miembro'): ?>
                        <input type="hidden" name="id_cliente" value="<?= $idCliente ?? ($acta['id_cliente'] ?? '') ?>">
                        <div class="mb-3">
                            <label class="form-label">Cliente</label>
                            <input type="text" class="form-control" value="<?= esc($cliente['nombre_cliente'] ?? '') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Registrado por</label>
                            <input type="text" class="form-control" value="<?= esc($miembro['nombre_completo'] ?? '') ?>" readonly>
                        </div>
                        <?php else: ?>
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select name="id_cliente" id="selectClienteCap" class="form-select" required>
                                <option value="">Cargando...</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Tema *</label>
                            <input type="text" name="tema" class="form-control"
                                value="<?= esc($acta['tema'] ?? '') ?>" placeholder="Ej: Riesgo biomecánico" required>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Fecha *</label>
                                <input type="date" name="fecha_capacitacion" class="form-control"
                                    value="<?= $acta['fecha_capacitacion'] ?? date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Hora inicio</label>
                                <input type="time" name="hora_inicio" class="form-control"
                                    value="<?= $acta['hora_inicio'] ?? '' ?>">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Hora fin</label>
                                <input type="time" name="hora_fin" class="form-control"
                                    value="<?= $acta['hora_fin'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Dictada por</label>
                                <select name="dictada_por" class="form-select">
                                    <?php foreach (['ARL','Consultor','Empresa','Otro'] as $op): ?>
                                        <option value="<?= $op ?>" <?= ($acta['dictada_por'] ?? 'ARL') === $op ? 'selected' : '' ?>><?= $op ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Modalidad</label>
                                <select name="modalidad" class="form-select">
                                    <?php foreach (['virtual','presencial','mixta'] as $op): ?>
                                        <option value="<?= $op ?>" <?= ($acta['modalidad'] ?? 'virtual') === $op ? 'selected' : '' ?>><?= ucfirst($op) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Entidad capacitadora</label>
                            <input type="text" name="entidad_capacitadora" class="form-control"
                                value="<?= esc($acta['entidad_capacitadora'] ?? '') ?>" placeholder="Ej: ARL Sura">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre del capacitador</label>
                            <input type="text" name="nombre_capacitador" class="form-control"
                                value="<?= esc($acta['nombre_capacitador'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Enlace grabación</label>
                            <input type="url" name="enlace_grabacion" class="form-control"
                                value="<?= esc($acta['enlace_grabacion'] ?? '') ?>" placeholder="https://...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Objetivos</label>
                            <textarea name="objetivos" class="form-control" rows="2" placeholder="Objetivos de la capacitación..."><?= esc($acta['objetivos'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contenido / Resumen</label>
                            <textarea name="contenido" class="form-control" rows="3" placeholder="Resumen de lo cubierto en la capacitación..."><?= esc($acta['contenido'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2"><?= esc($acta['observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asistentes -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secAsist">
                        Asistentes (<span id="countAsist"><?= count($asistentes ?? []) ?></span>)
                    </button>
                </h2>
                <div id="secAsist" class="accordion-collapse collapse" data-bs-parent="#accCap">
                    <div class="accordion-body">
                        <?php if (!$isEdit): ?>
                            <div class="alert alert-info py-2" style="font-size:13px;">
                                <i class="fas fa-info-circle"></i>
                                Guarda primero el acta como borrador. Después podrás agregar asistentes y enviar enlaces de firma por WhatsApp.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning py-2" style="font-size:13px;">
                                <i class="fas fa-exclamation-triangle"></i>
                                Para que el botón <strong>Enviar WhatsApp</strong> funcione, primero <strong>guarda</strong> el asistente recién agregado.
                            </div>
                        <?php endif; ?>

                        <div id="asistentesContainer">
                            <?php if (!empty($asistentes)): ?>
                                <?php foreach ($asistentes as $i => $a): ?>
                                <div class="card mb-3 asistente-row" data-asistente-id="<?= $a['id'] ?>">
                                    <div class="card-body p-2">
                                        <input type="hidden" name="asistente_id[]" value="<?= $a['id'] ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong style="font-size:13px;">Asistente #<span class="asist-num"><?= $i + 1 ?></span>
                                                <?php if (!empty($a['firma_path'])): ?>
                                                    <span class="badge bg-success" style="font-size:10px;"><i class="fas fa-check"></i> Firmado</span>
                                                <?php elseif (!empty($a['token_firma'])): ?>
                                                    <span class="badge bg-warning text-dark" style="font-size:10px;"><i class="fas fa-clock"></i> Enlace enviado</span>
                                                <?php endif; ?>
                                            </strong>
                                            <?php if (empty($a['firma_path'])): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-asist" style="min-height:32px;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php else: ?>
                                            <span class="text-muted small" title="No se puede quitar: ya firmo">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <input type="text" name="asistente_nombre[]" class="form-control form-control-sm"
                                                    value="<?= esc($a['nombre_completo']) ?>" placeholder="Nombre completo *" required>
                                            </div>
                                            <div class="col-4">
                                                <select name="asistente_tipo_doc[]" class="form-select form-select-sm">
                                                    <?php foreach (['CC','CE','PA','TI','NIT'] as $td): ?>
                                                        <option value="<?= $td ?>" <?= ($a['tipo_documento'] ?? 'CC') === $td ? 'selected' : '' ?>><?= $td ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-8">
                                                <input type="text" name="asistente_num_doc[]" class="form-control form-control-sm"
                                                    value="<?= esc($a['numero_documento'] ?? '') ?>" placeholder="Número documento">
                                            </div>
                                            <div class="col-12">
                                                <input type="text" name="asistente_cargo[]" class="form-control form-control-sm"
                                                    value="<?= esc($a['cargo'] ?? '') ?>" placeholder="Cargo">
                                            </div>
                                            <div class="col-12">
                                                <input type="text" name="asistente_area[]" class="form-control form-control-sm"
                                                    value="<?= esc($a['area_dependencia'] ?? '') ?>" placeholder="Área / Dependencia">
                                            </div>
                                            <div class="col-12">
                                                <input type="email" name="asistente_email[]" class="form-control form-control-sm"
                                                    value="<?= esc($a['email'] ?? '') ?>" placeholder="Email (opcional)">
                                            </div>
                                            <div class="col-12">
                                                <input type="text" name="asistente_celular[]" class="form-control form-control-sm"
                                                    value="<?= esc($a['celular'] ?? '') ?>" placeholder="Celular (opcional)">
                                            </div>
                                        </div>
                                        <?php if (empty($a['firma_path'])): ?>
                                        <div class="mt-2 d-grid gap-1">
                                            <button type="button" class="btn btn-sm btn-primary btn-save-asist"
                                                data-asistente-id="<?= $a['id'] ?>">
                                                <i class="fas fa-save"></i> Guardar este asistente
                                            </button>
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-copiar-firma flex-fill"
                                                    data-asistente-id="<?= $a['id'] ?>"
                                                    data-nombre="<?= esc($a['nombre_completo']) ?>"
                                                    title="Copiar enlace">
                                                    <i class="fas fa-copy"></i> Copiar
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-email-firma flex-fill"
                                                    data-asistente-id="<?= $a['id'] ?>"
                                                    data-nombre="<?= esc($a['nombre_completo']) ?>"
                                                    data-email="<?= esc($a['email'] ?? '') ?>"
                                                    title="Enviar enlace al email">
                                                    <i class="fas fa-envelope"></i> Email
                                                </button>
                                                <button type="button" class="btn btn-sm btn-success btn-whatsapp-firma flex-fill"
                                                    data-asistente-id="<?= $a['id'] ?>"
                                                    data-nombre="<?= esc($a['nombre_completo']) ?>"
                                                    title="Compartir por WhatsApp">
                                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                                </button>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddAsist">
                            <i class="fas fa-plus"></i> Agregar asistente
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <?php if ($isEdit): ?>
            <button type="button" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;" id="btnFinalizar">
                <i class="fas fa-check-circle"></i> Finalizar y generar PDF
            </button>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
    var tokenUrlBase = '<?= site_url($tokenUrlBase) ?>';

    <?php if ($ctx === 'consultor'): ?>
    var clienteIdSel = '<?= $idCliente ?? ($acta['id_cliente'] ?? '') ?>';
    if (typeof $ !== 'undefined') {
        $.ajax({
            url: '<?= site_url('inspecciones/api/clientes') ?>',
            dataType: 'json',
            success: function(data) {
                var sel = document.getElementById('selectClienteCap');
                if (!sel) return;
                sel.innerHTML = '<option value="">Seleccionar cliente...</option>';
                data.forEach(function(c) {
                    var opt = document.createElement('option');
                    opt.value = c.id_cliente;
                    opt.textContent = c.nombre_cliente;
                    if (clienteIdSel && String(c.id_cliente) === String(clienteIdSel)) opt.selected = true;
                    sel.appendChild(opt);
                });
                if ($('#selectClienteCap').select2) {
                    $('#selectClienteCap').select2({ placeholder: 'Seleccionar cliente...', width: '100%' });
                }
            }
        });
    }
    <?php endif; ?>

    function updateAsist() {
        var rows = document.querySelectorAll('.asistente-row');
        document.getElementById('countAsist').textContent = rows.length;
        rows.forEach((row, i) => {
            var num = row.querySelector('.asist-num');
            if (num) num.textContent = i + 1;
        });
    }

    var deleteAsistUrlBase = '<?= site_url($deleteAsistUrlBase) ?>';

    document.addEventListener('click', function(e) {
        var btnRm = e.target.closest('.btn-remove-asist');
        if (!btnRm) return;

        var row = btnRm.closest('.asistente-row');
        var hiddenId = row.querySelector('input[name="asistente_id[]"]');
        var idAsistente = (hiddenId && hiddenId.value) ? hiddenId.value : '';
        var nombre = (row.querySelector('input[name="asistente_nombre[]"]') || {}).value || 'sin nombre';

        // Si la fila aun no fue guardada en BD (no tiene id), solo quitar del DOM
        if (!idAsistente) {
            row.remove();
            updateAsist();
            return;
        }

        // Confirmar antes de eliminar de BD
        Swal.fire({
            icon: 'warning',
            title: '¿Eliminar este asistente?',
            html: 'Vas a eliminar a <strong>' + nombre + '</strong> del acta.<br><br>'
                + '<span style="color:#dc3545;font-size:13px;">Esta acción no se puede deshacer.</span>',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            focusCancel: true,
        }).then(function(result) {
            if (!result.isConfirmed) return;

            if (!idActaActual) {
                // No hay acta guardada, solo remover del DOM
                row.remove();
                updateAsist();
                return;
            }

            var fd = new FormData();
            fd.append(csrfName, csrfHash);

            fetch(deleteAsistUrlBase + idActaActual + '/' + idAsistente, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) {
                    Swal.fire('No se pudo eliminar', data.error || 'Intenta de nuevo', 'error');
                    return;
                }
                row.remove();
                updateAsist();
                Swal.fire({ icon: 'success', title: 'Asistente eliminado', toast: true, position: 'top', showConfirmButton: false, timer: 1500 });
            })
            .catch(function() {
                Swal.fire('Error de conexión', 'No se pudo eliminar el asistente.', 'error');
            });
        });
    });

    document.getElementById('btnAddAsist').addEventListener('click', function() {
        var num = document.querySelectorAll('.asistente-row').length + 1;
        var html = `
            <div class="card mb-3 asistente-row" data-asistente-id="">
                <div class="card-body p-2">
                    <input type="hidden" name="asistente_id[]" value="">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong style="font-size:13px;">Asistente #<span class="asist-num">${num}</span>
                            <span class="badge bg-secondary" style="font-size:10px;">Sin guardar</span>
                        </strong>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-asist" style="min-height:32px;"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="row g-2">
                        <div class="col-12"><input type="text" name="asistente_nombre[]" class="form-control form-control-sm" placeholder="Nombre completo *" required></div>
                        <div class="col-4">
                            <select name="asistente_tipo_doc[]" class="form-select form-select-sm">
                                <option value="CC" selected>CC</option><option value="CE">CE</option>
                                <option value="PA">PA</option><option value="TI">TI</option><option value="NIT">NIT</option>
                            </select>
                        </div>
                        <div class="col-8"><input type="text" name="asistente_num_doc[]" class="form-control form-control-sm" placeholder="Número documento"></div>
                        <div class="col-12"><input type="text" name="asistente_cargo[]" class="form-control form-control-sm" placeholder="Cargo"></div>
                        <div class="col-12"><input type="text" name="asistente_area[]" class="form-control form-control-sm" placeholder="Área / Dependencia"></div>
                        <div class="col-12"><input type="email" name="asistente_email[]" class="form-control form-control-sm" placeholder="Email (opcional)"></div>
                        <div class="col-12"><input type="text" name="asistente_celular[]" class="form-control form-control-sm" placeholder="Celular (opcional)"></div>
                    </div>
                    <div class="mt-2 d-grid gap-1">
                        <button type="button" class="btn btn-sm btn-primary btn-save-asist" data-asistente-id="">
                            <i class="fas fa-save"></i> Guardar este asistente
                        </button>
                    </div>
                </div>
            </div>`;
        document.getElementById('asistentesContainer').insertAdjacentHTML('beforeend', html);
        updateAsist();
        var sec = document.getElementById('secAsist');
        if (!sec.classList.contains('show')) new bootstrap.Collapse(sec, { toggle: true });
    });

    var saveAsistUrlBase = '<?= site_url($saveAsistUrlBase) ?>';
    var emailUrlBase = '<?= site_url($emailUrlBase) ?>';
    var idActaActual = <?= (int)($acta['id'] ?? 0) ?>;

    // Inyecta el bloque de 3 botones (Copiar/Email/WhatsApp) en una fila después de guardarla
    function inyectarBotonesFirma(row, idAsistente, nombre, email) {
        if (row.querySelector('.btn-whatsapp-firma')) return; // ya existen
        var div = document.createElement('div');
        div.className = 'd-flex gap-1 mt-1';
        div.innerHTML = ''
            + '<button type="button" class="btn btn-sm btn-outline-secondary btn-copiar-firma flex-fill" '
            +   'data-asistente-id="' + idAsistente + '" data-nombre="' + nombre + '" title="Copiar enlace">'
            +   '<i class="fas fa-copy"></i> Copiar</button>'
            + '<button type="button" class="btn btn-sm btn-outline-primary btn-email-firma flex-fill" '
            +   'data-asistente-id="' + idAsistente + '" data-nombre="' + nombre + '" data-email="' + (email || '') + '" title="Enviar enlace al email">'
            +   '<i class="fas fa-envelope"></i> Email</button>'
            + '<button type="button" class="btn btn-sm btn-success btn-whatsapp-firma flex-fill" '
            +   'data-asistente-id="' + idAsistente + '" data-nombre="' + nombre + '" title="Compartir por WhatsApp">'
            +   '<i class="fab fa-whatsapp"></i> WhatsApp</button>';
        var saveBtn = row.querySelector('.btn-save-asist');
        if (saveBtn && saveBtn.parentElement) {
            saveBtn.parentElement.appendChild(div);
        }
    }

    // ===== Guardar UN asistente (AJAX) =====
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-save-asist');
        if (!btn) return;

        if (!idActaActual) {
            Swal.fire('Guarda primero el acta', 'Primero guarda el acta como borrador para poder agregar asistentes.', 'info');
            return;
        }

        var row = btn.closest('.asistente-row');
        var nombre = (row.querySelector('input[name="asistente_nombre[]"]') || {}).value || '';
        if (!nombre.trim()) {
            Swal.fire({ icon: 'warning', title: 'Nombre requerido', text: 'Ingresa el nombre del asistente.' });
            return;
        }

        var idAsistente = btn.dataset.asistenteId || (row.querySelector('input[name="asistente_id[]"]') || {}).value || '';
        var email = (row.querySelector('input[name="asistente_email[]"]') || {}).value || '';
        var fd = new FormData();
        fd.append(csrfName, csrfHash);
        fd.append('id_asistente', idAsistente);
        fd.append('nombre_completo', nombre);
        fd.append('tipo_documento', (row.querySelector('select[name="asistente_tipo_doc[]"]') || {}).value || 'CC');
        fd.append('numero_documento', (row.querySelector('input[name="asistente_num_doc[]"]') || {}).value || '');
        fd.append('cargo', (row.querySelector('input[name="asistente_cargo[]"]') || {}).value || '');
        fd.append('area_dependencia', (row.querySelector('input[name="asistente_area[]"]') || {}).value || '');
        fd.append('email', email);
        fd.append('celular', (row.querySelector('input[name="asistente_celular[]"]') || {}).value || '');
        var rows = Array.from(document.querySelectorAll('.asistente-row'));
        fd.append('orden', String(rows.indexOf(row) + 1));

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        fetch(saveAsistUrlBase + idActaActual, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            if (!data.success) {
                btn.innerHTML = '<i class="fas fa-save"></i> Guardar este asistente';
                Swal.fire('Error', data.error || 'No se pudo guardar', 'error');
                return;
            }
            // Actualiza ID en la fila
            var hiddenId = row.querySelector('input[name="asistente_id[]"]');
            if (hiddenId) hiddenId.value = data.id;
            row.dataset.asistenteId = data.id;
            btn.dataset.asistenteId = data.id;
            btn.innerHTML = '<i class="fas fa-check"></i> Guardado';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');

            // Actualiza badge "Sin guardar" → "Guardado"
            var badge = row.querySelector('.badge.bg-secondary');
            if (badge) {
                badge.textContent = 'Guardado';
                badge.classList.remove('bg-secondary');
                badge.classList.add('bg-success');
            }

            // Inyecta botones de firma si aún no existen
            inyectarBotonesFirma(row, data.id, nombre, email);

            Swal.fire({ icon: 'success', title: 'Asistente guardado', toast: true, position: 'top', showConfirmButton: false, timer: 1500 });
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Guardar este asistente';
            Swal.fire('Error', 'Error de conexión', 'error');
        });
    });

    // ===== Copiar enlace =====
    function generarTokenYHacer(idAsistente, callback) {
        var fd = new FormData();
        fd.append(csrfName, csrfHash);
        fetch(tokenUrlBase + idAsistente, {
            method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                Swal.fire('Error', data.error || 'No se pudo generar el enlace', 'error');
                return;
            }
            callback(data.url);
        })
        .catch(function() { Swal.fire('Error', 'Error de conexión', 'error'); });
    }

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-copiar-firma');
        if (!btn) return;
        var idAsistente = btn.dataset.asistenteId;
        if (!idAsistente) {
            Swal.fire('Guarda primero', 'Guarda este asistente antes de copiar el enlace.', 'info');
            return;
        }
        Swal.fire({ title: 'Generando enlace...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
        generarTokenYHacer(idAsistente, function(url) {
            navigator.clipboard.writeText(url).then(function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Enlace copiado',
                    html: '<p style="font-size:13px;">El enlace ya está en tu portapapeles. Pégalo donde necesites.</p>'
                        + '<div style="background:#f8f9fa;border-radius:8px;padding:10px;font-size:11px;word-break:break-all;border:1px solid #dee2e6;">'
                        + url + '</div>',
                    confirmButtonText: 'Cerrar',
                });
            }).catch(function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Copia manualmente',
                    html: '<div style="background:#f8f9fa;border-radius:8px;padding:10px;font-size:11px;word-break:break-all;border:1px solid #dee2e6;">'
                        + url + '</div>',
                    confirmButtonText: 'Cerrar',
                });
            });
        });
    });

    // ===== Email firma =====
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-email-firma');
        if (!btn) return;

        var idAsistente = btn.dataset.asistenteId;
        var nombre = btn.dataset.nombre;
        var email = btn.dataset.email;

        if (!idAsistente) {
            Swal.fire('Guarda primero', 'Guarda este asistente antes de enviar el email.', 'info');
            return;
        }
        // Si el dataset email está vacío, intenta leerlo del input por si lo acaban de escribir
        if (!email) {
            var row = btn.closest('.asistente-row');
            email = (row && row.querySelector('input[name="asistente_email[]"]')) ? row.querySelector('input[name="asistente_email[]"]').value : '';
        }
        if (!email) {
            Swal.fire('Sin email', 'Este asistente no tiene email registrado. Edita la fila y guárdala primero.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Enviar email de firma',
            html: '<p style="font-size:14px;">Se enviará un enlace de firma a:<br><strong>' + email + '</strong><br>(' + nombre + ')</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Enviar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0d6efd',
        }).then(function(r) {
            if (!r.isConfirmed) return;
            Swal.fire({ title: 'Enviando email...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });

            var fd = new FormData();
            fd.append(csrfName, csrfHash);

            fetch(emailUrlBase + idAsistente, {
                method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: '¡Email enviado!', text: 'Enviado a ' + (data.email || email), confirmButtonColor: '#0d6efd' });
                } else {
                    Swal.fire('Error', data.error || 'No se pudo enviar el email', 'error');
                }
            })
            .catch(function() { Swal.fire('Error', 'Error de conexión', 'error'); });
        });
    });

    // ===== WhatsApp firma remota =====
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-whatsapp-firma');
        if (!btn) return;

        var idAsistente = btn.dataset.asistenteId;
        var nombre = btn.dataset.nombre;

        if (!idAsistente) {
            Swal.fire('Guarda primero', 'Guarda el acta para generar el enlace de este asistente.', 'info');
            return;
        }

        Swal.fire({
            title: 'Enviar enlace de firma',
            html: '<p style="font-size:14px;">Se generará un enlace para que <strong>' + nombre + '</strong> firme desde su celular.<br><small class="text-muted">Vence en 7 días.</small></p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fab fa-whatsapp"></i> Generar enlace',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#25D366',
        }).then(function(result) {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Generando enlace...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });

            var formData = new FormData();
            formData.append(csrfName, csrfHash);

            fetch(tokenUrlBase + idAsistente, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) {
                    Swal.fire('Error', data.error || 'No se pudo generar el enlace', 'error');
                    return;
                }
                var url = data.url;
                var texto = encodeURIComponent('Hola ' + nombre + ', por favor firma el acta de capacitación haciendo clic en este enlace (vence en 7 días):\n' + url);
                var waUrl = 'https://wa.me/?text=' + texto;

                Swal.fire({
                    title: 'Enlace generado',
                    html: '<p style="font-size:13px;">Comparte con <strong>' + nombre + '</strong>:</p>'
                          + '<div style="background:#f8f9fa;border-radius:8px;padding:10px;font-size:11px;word-break:break-all;margin-bottom:12px;border:1px solid #dee2e6;">'
                          + url + '</div>'
                          + '<button type="button" id="btnCopiar" class="btn btn-sm btn-outline-secondary"><i class="fas fa-copy"></i> Copiar enlace</button>',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fab fa-whatsapp"></i> Abrir WhatsApp',
                    cancelButtonText: 'Cerrar',
                    confirmButtonColor: '#25D366',
                    didOpen: function() {
                        document.getElementById('btnCopiar').addEventListener('click', function() {
                            navigator.clipboard.writeText(url).then(function() {
                                var b = document.getElementById('btnCopiar');
                                b.innerHTML = '<i class="fas fa-check"></i> Copiado';
                                b.classList.remove('btn-outline-secondary'); b.classList.add('btn-success');
                            });
                        });
                    }
                }).then(function(r) {
                    if (r.isConfirmed) window.open(waUrl, '_blank');
                });
            })
            .catch(function() { Swal.fire('Error', 'Error de conexión', 'error'); });
        });
    });

    var btnFinalizar = document.getElementById('btnFinalizar');
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', function(e) {
            if (document.querySelectorAll('.asistente-row').length === 0) {
                Swal.fire({ icon: 'warning', title: 'Agrega al menos 1 asistente antes de finalizar', confirmButtonColor: '#bd9751' });
                return;
            }
            Swal.fire({
                title: '¿Finalizar?',
                html: 'Se generará el PDF con las firmas registradas a la fecha.<br><small>Los asistentes que aún no firmaron quedarán sin firma en el documento.</small>',
                icon: 'question', showCancelButton: true,
                confirmButtonText: 'Sí, finalizar', cancelButtonText: 'Cancelar', confirmButtonColor: '#bd9751',
            }).then(r => {
                if (r.isConfirmed) {
                    var i = document.createElement('input');
                    i.type = 'hidden'; i.name = 'finalizar'; i.value = '1';
                    document.getElementById('actaCapForm').appendChild(i);
                    document.getElementById('actaCapForm').submit();
                }
            });
        });
    }
});
</script>
