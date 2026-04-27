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
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-asist" style="min-height:32px;">
                                                <i class="fas fa-times"></i>
                                            </button>
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
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-success btn-whatsapp-firma w-100"
                                                data-asistente-id="<?= $a['id'] ?>"
                                                data-nombre="<?= esc($a['nombre_completo']) ?>">
                                                <i class="fab fa-whatsapp"></i> Enviar enlace de firma por WhatsApp
                                            </button>
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

    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-asist')) {
            e.target.closest('.asistente-row').remove();
            updateAsist();
        }
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
                </div>
            </div>`;
        document.getElementById('asistentesContainer').insertAdjacentHTML('beforeend', html);
        updateAsist();
        var sec = document.getElementById('secAsist');
        if (!sec.classList.contains('show')) new bootstrap.Collapse(sec, { toggle: true });
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
