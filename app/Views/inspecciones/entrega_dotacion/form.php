<?php
$isEdit = !empty($entrega);
$baseUrl = 'inspecciones/entrega-dotacion';
$action = $isEdit
    ? site_url($baseUrl . '/update/' . $entrega['id'])
    : site_url($baseUrl . '/store');
$tokenUrlBase            = 'inspecciones/entrega-dotacion/generar-token-firma/';
$saveAsistUrlBase        = 'inspecciones/entrega-dotacion/asistente/save/';
$emailUrlBase            = 'inspecciones/entrega-dotacion/asistente/enviar-email/';
$deleteAsistUrlBase      = 'inspecciones/entrega-dotacion/asistente/delete/';
$statusUrlBase           = 'inspecciones/entrega-dotacion/asistentes-status/';
$tokenInscripcionUrlBase = 'inspecciones/entrega-dotacion/generar-token-inscripcion/';
$itemsGlobales = $items ?? [];
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" id="entregaDotForm">
        <?= csrf_field() ?>

        <div class="accordion mt-2" id="accEntrega">

            <!-- Datos Generales -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accEntrega">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select name="id_cliente" id="selectClienteEntrega" class="form-select" required>
                                <option value="">Cargando...</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Fecha de entrega *</label>
                                <input type="date" name="fecha_entrega" class="form-control"
                                    value="<?= $entrega['fecha_entrega'] ?? date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Hora</label>
                                <input type="time" name="hora" class="form-control"
                                    value="<?= $entrega['hora'] ?? '' ?>">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Tipo de dotación</label>
                                <input type="text" name="tipo_dotacion" class="form-control"
                                    value="<?= esc($entrega['tipo_dotacion'] ?? '') ?>" placeholder="Ej: Operativa">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lugar</label>
                            <input type="text" name="lugar" class="form-control"
                                value="<?= esc($entrega['lugar'] ?? '') ?>" placeholder="Ej: Bodega principal">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Responsable de entrega</label>
                            <input type="text" name="responsable_entrega" class="form-control"
                                value="<?= esc($entrega['responsable_entrega'] ?? '') ?>" placeholder="Quien entrega los EPP">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2"><?= esc($entrega['observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ELEMENTOS ENTREGADOS (globales — todos los operarios reciben lo mismo) -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secItems">
                        <i class="fas fa-list me-2"></i> Elementos Entregados (<span id="countItems"><?= count($itemsGlobales) ?></span>)
                    </button>
                </h2>
                <div id="secItems" class="accordion-collapse collapse show" data-bs-parent="#accEntrega">
                    <div class="accordion-body">
                        <div class="alert alert-info py-2" style="font-size:13px;">
                            <i class="fas fa-info-circle"></i>
                            Esta es la lista de elementos que recibirán <strong>todos los operarios</strong>. La <strong>talla</strong> la digita cada operario al escanear su QR.
                            Si alguien necesita elementos diferentes, crea otra entrega aparte.
                        </div>
                        <div id="itemsContainer">
                            <?php foreach ($itemsGlobales as $i => $it): ?>
                            <div class="card mb-2 item-row">
                                <div class="card-body p-2">
                                    <input type="hidden" name="item_id[]" value="<?= $it['id'] ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong style="font-size:13px;">Item #<span class="item-num"><?= $i + 1 ?></span></strong>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" style="min-height:32px;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <input type="text" name="item_descripcion[]" class="form-control form-control-sm"
                                                value="<?= esc($it['descripcion']) ?>" placeholder="Descripción del elemento *" required>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" name="item_cantidad[]" class="form-control form-control-sm"
                                                value="<?= esc($it['cantidad']) ?>" placeholder="Cant.">
                                        </div>
                                        <div class="col-8">
                                            <input type="text" name="item_marca[]" class="form-control form-control-sm"
                                                value="<?= esc($it['marca'] ?? '') ?>" placeholder="Marca (opcional)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddItem">
                            <i class="fas fa-plus"></i> Agregar elemento
                        </button>
                    </div>
                </div>
            </div>

            <!-- Operarios (solo datos personales — items son globales arriba) -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secAsist">
                        Operarios (<span id="countAsist"><?= count($asistentes ?? []) ?></span>)
                    </button>
                </h2>
                <div id="secAsist" class="accordion-collapse collapse" data-bs-parent="#accEntrega">
                    <div class="accordion-body">
                        <?php if (!$isEdit): ?>
                            <div class="alert alert-info py-2" style="font-size:13px;">
                                <i class="fas fa-info-circle"></i>
                                Guarda primero la entrega como borrador y los elementos. Después agrega los operarios o reparte el QR para que se inscriban solos.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning py-2" style="font-size:13px;">
                                <i class="fas fa-exclamation-triangle"></i>
                                Para que el botón <strong>Enviar WhatsApp</strong> funcione, primero <strong>guarda</strong> el operario recién agregado.
                            </div>
                        <?php endif; ?>

                        <?php if ($isEdit): ?>
                        <div class="mb-3 p-3 rounded" style="background:#fff7ed;border:1px solid #fed7aa;">
                            <div class="d-flex justify-content-between align-items-center" style="gap:10px;">
                                <div style="font-size:13px;line-height:1.4;">
                                    <strong style="color:#9a3412;">
                                        <i class="fas fa-qrcode"></i> Auto-inscripcion via QR
                                    </strong>
                                    <div class="text-muted" style="font-size:12px;">
                                        Cada operario escanea el QR, llena sus datos y sus tallas, confirma estado y firma desde su celular.
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-warning" id="btnMostrarQR" style="white-space:nowrap;font-weight:600;">
                                    <i class="fas fa-qrcode"></i> Mostrar QR
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php
                        $totalAsist = count($asistentes ?? []);
                        $firmadosAsist = 0;
                        foreach (($asistentes ?? []) as $__a) { if (!empty($__a['firma_path'])) $firmadosAsist++; }
                        $pctAsist = $totalAsist > 0 ? (int) round($firmadosAsist * 100 / $totalAsist) : 0;
                        ?>
                        <?php if ($isEdit && $totalAsist > 0): ?>
                        <div id="firmasProgreso" class="mb-3 p-2 rounded" style="background:#f8f9fa;border:1px solid #e5e7eb;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span style="font-size:13px;font-weight:600;color:#111827;">
                                    <i class="fas fa-signature text-success"></i>
                                    Firmas: <span id="firmasFirmados"><?= $firmadosAsist ?></span> de <span id="firmasTotal"><?= $totalAsist ?></span>
                                    (<span id="firmasPct"><?= $pctAsist ?></span>%)
                                </span>
                                <button type="button" id="btnRefreshFirmas" class="btn btn-sm btn-outline-primary" style="font-size:12px;padding:3px 10px;">
                                    <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                            </div>
                            <div style="background:#e5e7eb;border-radius:999px;height:8px;overflow:hidden;">
                                <div id="firmasBar" style="background:#10b981;height:100%;width:<?= $pctAsist ?>%;transition:width 0.3s;"></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div id="asistentesContainer">
                            <?php if (!empty($asistentes)): ?>
                                <?php foreach ($asistentes as $i => $a): ?>
                                <div class="card mb-3 asistente-row" data-asistente-id="<?= $a['id'] ?>">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong style="font-size:13px;">Operario #<span class="asist-num"><?= $i + 1 ?></span>
                                                <span class="asist-status-badge">
                                                <?php if (!empty($a['firma_path'])): ?>
                                                    <span class="badge bg-success" style="font-size:10px;"><i class="fas fa-check"></i> Firmado</span>
                                                <?php elseif (!empty($a['token_firma'])): ?>
                                                    <span class="badge bg-warning text-dark" style="font-size:10px;"><i class="fas fa-clock"></i> Enlace enviado</span>
                                                <?php endif; ?>
                                                </span>
                                            </strong>
                                            <?php if (empty($a['firma_path'])): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-asist" style="min-height:32px;" data-id="<?= $a['id'] ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php else: ?>
                                            <span class="text-muted small" title="No se puede quitar: ya firmo">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="row g-2 datos-asist">
                                            <div class="col-12">
                                                <input type="text" class="form-control form-control-sm input-nombre"
                                                    value="<?= esc($a['nombre_completo']) ?>" placeholder="Nombre completo *" required>
                                            </div>
                                            <div class="col-4">
                                                <select class="form-select form-select-sm input-tipo-doc">
                                                    <?php foreach (['CC','CE','PA','TI','NIT'] as $td): ?>
                                                        <option value="<?= $td ?>" <?= ($a['tipo_documento'] ?? 'CC') === $td ? 'selected' : '' ?>><?= $td ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-8">
                                                <input type="text" class="form-control form-control-sm input-num-doc"
                                                    value="<?= esc($a['numero_documento'] ?? '') ?>" placeholder="Número documento">
                                            </div>
                                            <div class="col-12">
                                                <input type="text" class="form-control form-control-sm input-cargo"
                                                    value="<?= esc($a['cargo'] ?? '') ?>" placeholder="Cargo">
                                            </div>
                                            <div class="col-12">
                                                <input type="text" class="form-control form-control-sm input-area"
                                                    value="<?= esc($a['area_dependencia'] ?? '') ?>" placeholder="Área / Dependencia">
                                            </div>
                                            <div class="col-12">
                                                <input type="email" class="form-control form-control-sm input-email"
                                                    value="<?= esc($a['email'] ?? '') ?>" placeholder="Email (opcional)">
                                            </div>
                                            <div class="col-12">
                                                <input type="text" class="form-control form-control-sm input-celular"
                                                    value="<?= esc($a['celular'] ?? '') ?>" placeholder="Celular (opcional)">
                                            </div>
                                        </div>

                                        <?php if (!empty($a['tallas_map'])): ?>
                                        <div class="mt-2 p-2 rounded" style="background:#f9fafb; border:1px solid #e5e7eb; font-size:11px;">
                                            <strong><i class="fas fa-ruler"></i> Tallas registradas por el operario:</strong>
                                            <div class="text-muted mt-1">
                                                <?php foreach ($itemsGlobales as $itGlobal): ?>
                                                    <?php $talla = $a['tallas_map'][$itGlobal['id']] ?? ''; ?>
                                                    <?php if ($talla !== ''): ?>
                                                        <div><?= esc($itGlobal['descripcion']) ?>: <strong><?= esc($talla) ?></strong></div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($a['recibido_buen_estado'])): ?>
                                        <div class="mt-2" style="font-size:11px;">
                                            <strong>¿Recibió en buen estado?</strong>
                                            <?php if ($a['recibido_buen_estado'] === 'si'): ?>
                                                <span class="badge bg-success">SÍ</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">NO</span>
                                                <?php if (!empty($a['observaciones_recibido'])): ?>
                                                    <div class="text-muted mt-1"><i class="fas fa-comment-alt"></i> <?= esc($a['observaciones_recibido']) ?></div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (empty($a['firma_path'])): ?>
                                        <div class="mt-2 d-grid gap-1">
                                            <button type="button" class="btn btn-sm btn-primary btn-save-asist" data-asistente-id="<?= $a['id'] ?>">
                                                <i class="fas fa-save"></i> Guardar este operario
                                            </button>
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-copiar-firma flex-fill" data-asistente-id="<?= $a['id'] ?>" data-nombre="<?= esc($a['nombre_completo']) ?>">
                                                    <i class="fas fa-copy"></i> Copiar
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-email-firma flex-fill" data-asistente-id="<?= $a['id'] ?>" data-nombre="<?= esc($a['nombre_completo']) ?>" data-email="<?= esc($a['email'] ?? '') ?>">
                                                    <i class="fas fa-envelope"></i> Email
                                                </button>
                                                <button type="button" class="btn btn-sm btn-success btn-whatsapp-firma flex-fill" data-asistente-id="<?= $a['id'] ?>" data-nombre="<?= esc($a['nombre_completo']) ?>">
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
                            <i class="fas fa-plus"></i> Agregar operario
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
                <i class="fas fa-check-circle"></i> Finalizar y generar PDFs
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
    var saveAsistUrlBase = '<?= site_url($saveAsistUrlBase) ?>';
    var emailUrlBase = '<?= site_url($emailUrlBase) ?>';
    var deleteAsistUrlBase = '<?= site_url($deleteAsistUrlBase) ?>';
    var statusUrlBase = '<?= site_url($statusUrlBase) ?>';
    var tokenInscripcionUrlBase = '<?= site_url($tokenInscripcionUrlBase) ?>';
    var idEntregaActual = <?= (int)($entrega['id'] ?? 0) ?>;

    var clienteIdSel = '<?= $idCliente ?? ($entrega['id_cliente'] ?? '') ?>';
    if (typeof $ !== 'undefined') {
        $.ajax({
            url: '<?= site_url('inspecciones/api/clientes') ?>',
            dataType: 'json',
            success: function(data) {
                var sel = document.getElementById('selectClienteEntrega');
                if (!sel) return;
                sel.innerHTML = '<option value="">Seleccionar cliente...</option>';
                data.forEach(function(c) {
                    var opt = document.createElement('option');
                    opt.value = c.id_cliente;
                    opt.textContent = c.nombre_cliente;
                    if (clienteIdSel && String(c.id_cliente) === String(clienteIdSel)) opt.selected = true;
                    sel.appendChild(opt);
                });
                if ($('#selectClienteEntrega').select2) {
                    $('#selectClienteEntrega').select2({ placeholder: 'Seleccionar cliente...', width: '100%' });
                }
            }
        });
    }

    // ============================================================
    // ITEMS GLOBALES (NUEVO)
    // ============================================================
    function updateItemsCount() {
        var rows = document.querySelectorAll('.item-row');
        document.getElementById('countItems').textContent = rows.length;
        rows.forEach((row, i) => {
            var num = row.querySelector('.item-num');
            if (num) num.textContent = i + 1;
        });
    }

    function newItemHtml(num) {
        return '<div class="card mb-2 item-row">'
             + '<div class="card-body p-2">'
             +   '<input type="hidden" name="item_id[]" value="">'
             +   '<div class="d-flex justify-content-between align-items-center mb-1">'
             +       '<strong style="font-size:13px;">Item #<span class="item-num">' + num + '</span></strong>'
             +       '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" style="min-height:32px;"><i class="fas fa-times"></i></button>'
             +   '</div>'
             +   '<div class="row g-2">'
             +       '<div class="col-12"><input type="text" name="item_descripcion[]" class="form-control form-control-sm" placeholder="Descripción del elemento *" required></div>'
             +       '<div class="col-4"><input type="text" name="item_cantidad[]" class="form-control form-control-sm" value="1" placeholder="Cant."></div>'
             +       '<div class="col-8"><input type="text" name="item_marca[]" class="form-control form-control-sm" placeholder="Marca (opcional)"></div>'
             +   '</div>'
             + '</div>'
             + '</div>';
    }

    document.getElementById('btnAddItem').addEventListener('click', function() {
        var num = document.querySelectorAll('.item-row').length + 1;
        document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', newItemHtml(num));
        updateItemsCount();
    });

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-remove-item');
        if (!btn) return;
        var row = btn.closest('.item-row');
        if (row) {
            row.remove();
            updateItemsCount();
        }
    });

    // ============================================================
    // OPERARIOS
    // ============================================================
    function updateAsist() {
        var rows = document.querySelectorAll('.asistente-row');
        document.getElementById('countAsist').textContent = rows.length;
        rows.forEach((row, i) => {
            var num = row.querySelector('.asist-num');
            if (num) num.textContent = i + 1;
        });
    }

    function asistRowHtml(num) {
        return '<div class="card mb-3 asistente-row" data-asistente-id="">'
             + '<div class="card-body p-2">'
             +   '<div class="d-flex justify-content-between align-items-center mb-2">'
             +       '<strong style="font-size:13px;">Operario #<span class="asist-num">' + num + '</span> '
             +           '<span class="badge bg-secondary" style="font-size:10px;">Sin guardar</span>'
             +       '</strong>'
             +       '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-asist" style="min-height:32px;"><i class="fas fa-times"></i></button>'
             +   '</div>'
             +   '<div class="row g-2 datos-asist">'
             +       '<div class="col-12"><input type="text" class="form-control form-control-sm input-nombre" placeholder="Nombre completo *" required></div>'
             +       '<div class="col-4"><select class="form-select form-select-sm input-tipo-doc"><option value="CC" selected>CC</option><option value="CE">CE</option><option value="PA">PA</option><option value="TI">TI</option><option value="NIT">NIT</option></select></div>'
             +       '<div class="col-8"><input type="text" class="form-control form-control-sm input-num-doc" placeholder="Número documento"></div>'
             +       '<div class="col-12"><input type="text" class="form-control form-control-sm input-cargo" placeholder="Cargo"></div>'
             +       '<div class="col-12"><input type="text" class="form-control form-control-sm input-area" placeholder="Área / Dependencia"></div>'
             +       '<div class="col-12"><input type="email" class="form-control form-control-sm input-email" placeholder="Email (opcional)"></div>'
             +       '<div class="col-12"><input type="text" class="form-control form-control-sm input-celular" placeholder="Celular (opcional)"></div>'
             +   '</div>'
             +   '<div class="mt-2 d-grid gap-1">'
             +       '<button type="button" class="btn btn-sm btn-primary btn-save-asist" data-asistente-id=""><i class="fas fa-save"></i> Guardar este operario</button>'
             +   '</div>'
             + '</div>'
             + '</div>';
    }

    document.getElementById('btnAddAsist').addEventListener('click', function() {
        var num = document.querySelectorAll('.asistente-row').length + 1;
        document.getElementById('asistentesContainer').insertAdjacentHTML('beforeend', asistRowHtml(num));
        updateAsist();
        var sec = document.getElementById('secAsist');
        if (!sec.classList.contains('show')) new bootstrap.Collapse(sec, { toggle: true });
    });

    document.addEventListener('click', function(e) {
        var btnRm = e.target.closest('.btn-remove-asist');
        if (!btnRm) return;
        var row = btnRm.closest('.asistente-row');
        if (!row) return;
        var idAsistente = row.dataset.asistenteId || '';
        var nombre = (row.querySelector('.input-nombre') || {}).value || 'sin nombre';

        if (!idAsistente) {
            row.remove();
            updateAsist();
            return;
        }

        Swal.fire({
            icon: 'warning',
            title: '¿Eliminar este operario?',
            html: 'Vas a eliminar a <strong>' + nombre + '</strong> de la entrega.<br><br>'
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
            if (!idEntregaActual) { row.remove(); updateAsist(); return; }

            var fd = new FormData();
            fd.append(csrfName, csrfHash);

            fetch(deleteAsistUrlBase + idEntregaActual + '/' + idAsistente, {
                method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) { Swal.fire('No se pudo eliminar', data.error || 'Intenta de nuevo', 'error'); return; }
                row.remove();
                updateAsist();
                Swal.fire({ icon: 'success', title: 'Operario eliminado', toast: true, position: 'top', showConfirmButton: false, timer: 1500 });
            })
            .catch(function() { Swal.fire('Error de conexión', 'No se pudo eliminar.', 'error'); });
        });
    });

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-save-asist');
        if (!btn) return;

        if (!idEntregaActual) {
            Swal.fire('Guarda primero la entrega', 'Primero guarda la entrega como borrador para poder agregar operarios.', 'info');
            return;
        }

        var row = btn.closest('.asistente-row');
        var nombre = (row.querySelector('.input-nombre') || {}).value || '';
        if (!nombre.trim()) {
            Swal.fire({ icon: 'warning', title: 'Nombre requerido', text: 'Ingresa el nombre del operario.' });
            return;
        }

        var idAsistente = btn.dataset.asistenteId || row.dataset.asistenteId || '';
        var email = (row.querySelector('.input-email') || {}).value || '';
        var fd = new FormData();
        fd.append(csrfName, csrfHash);
        fd.append('id_asistente', idAsistente);
        fd.append('nombre_completo', nombre);
        fd.append('tipo_documento', (row.querySelector('.input-tipo-doc') || {}).value || 'CC');
        fd.append('numero_documento', (row.querySelector('.input-num-doc') || {}).value || '');
        fd.append('cargo', (row.querySelector('.input-cargo') || {}).value || '');
        fd.append('area_dependencia', (row.querySelector('.input-area') || {}).value || '');
        fd.append('email', email);
        fd.append('celular', (row.querySelector('.input-celular') || {}).value || '');
        var rowsAll = Array.from(document.querySelectorAll('.asistente-row'));
        fd.append('orden', String(rowsAll.indexOf(row) + 1));

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        fetch(saveAsistUrlBase + idEntregaActual, {
            method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            if (!data.success) {
                btn.innerHTML = '<i class="fas fa-save"></i> Guardar este operario';
                Swal.fire('Error', data.error || 'No se pudo guardar', 'error');
                return;
            }
            row.dataset.asistenteId = data.id;
            btn.dataset.asistenteId = data.id;
            btn.innerHTML = '<i class="fas fa-check"></i> Guardado';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            var badge = row.querySelector('.badge.bg-secondary');
            if (badge) { badge.textContent = 'Guardado'; badge.classList.remove('bg-secondary'); badge.classList.add('bg-success'); }
            Swal.fire({ icon: 'success', title: 'Operario guardado', toast: true, position: 'top', showConfirmButton: false, timer: 1500 });
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Guardar este operario';
            Swal.fire('Error', 'Error de conexión', 'error');
        });
    });

    function generarTokenYHacer(idAsistente, callback) {
        var fd = new FormData();
        fd.append(csrfName, csrfHash);
        fetch(tokenUrlBase + idAsistente, {
            method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) { Swal.fire('Error', data.error || 'No se pudo generar el enlace', 'error'); return; }
            callback(data.url);
        })
        .catch(function() { Swal.fire('Error', 'Error de conexión', 'error'); });
    }

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-copiar-firma');
        if (!btn) return;
        var idAsistente = btn.dataset.asistenteId;
        if (!idAsistente) { Swal.fire('Guarda primero', 'Guarda este operario antes de copiar el enlace.', 'info'); return; }
        Swal.fire({ title: 'Generando enlace...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
        generarTokenYHacer(idAsistente, function(url) {
            navigator.clipboard.writeText(url).then(function() {
                Swal.fire({ icon: 'success', title: 'Enlace copiado',
                    html: '<p style="font-size:13px;">El enlace ya está en tu portapapeles.</p>'
                        + '<div style="background:#f8f9fa;border-radius:8px;padding:10px;font-size:11px;word-break:break-all;border:1px solid #dee2e6;">' + url + '</div>',
                    confirmButtonText: 'Cerrar',
                });
            }).catch(function() {
                Swal.fire({ icon: 'info', title: 'Copia manualmente',
                    html: '<div style="background:#f8f9fa;border-radius:8px;padding:10px;font-size:11px;word-break:break-all;border:1px solid #dee2e6;">' + url + '</div>',
                    confirmButtonText: 'Cerrar',
                });
            });
        });
    });

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-email-firma');
        if (!btn) return;
        var idAsistente = btn.dataset.asistenteId;
        var nombre = btn.dataset.nombre;
        var email = btn.dataset.email;
        if (!idAsistente) { Swal.fire('Guarda primero', 'Guarda este operario antes de enviar el email.', 'info'); return; }
        if (!email) {
            var row = btn.closest('.asistente-row');
            email = (row && row.querySelector('.input-email')) ? row.querySelector('.input-email').value : '';
        }
        if (!email) { Swal.fire('Sin email', 'Este operario no tiene email registrado. Edita la fila y guárdala primero.', 'warning'); return; }

        Swal.fire({
            title: 'Enviar email de firma',
            html: '<p style="font-size:14px;">Se enviará un enlace de firma a:<br><strong>' + email + '</strong><br>(' + nombre + ')</p>',
            icon: 'question', showCancelButton: true,
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Enviar',
            cancelButtonText: 'Cancelar', confirmButtonColor: '#0d6efd',
        }).then(function(r) {
            if (!r.isConfirmed) return;
            Swal.fire({ title: 'Enviando email...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
            var fd = new FormData(); fd.append(csrfName, csrfHash);
            fetch(emailUrlBase + idAsistente, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }})
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) Swal.fire({ icon: 'success', title: '¡Email enviado!', text: 'Enviado a ' + (data.email || email), confirmButtonColor: '#0d6efd' });
                else Swal.fire('Error', data.error || 'No se pudo enviar el email', 'error');
            })
            .catch(function() { Swal.fire('Error', 'Error de conexión', 'error'); });
        });
    });

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-whatsapp-firma');
        if (!btn) return;
        var idAsistente = btn.dataset.asistenteId;
        var nombre = btn.dataset.nombre;
        if (!idAsistente) { Swal.fire('Guarda primero', 'Guarda la entrega para generar el enlace.', 'info'); return; }

        Swal.fire({
            title: 'Enviar enlace de firma',
            html: '<p style="font-size:14px;">Se generará un enlace para que <strong>' + nombre + '</strong> firme desde su celular.<br><small class="text-muted">Vence en 7 días.</small></p>',
            icon: 'question', showCancelButton: true,
            confirmButtonText: '<i class="fab fa-whatsapp"></i> Generar enlace',
            cancelButtonText: 'Cancelar', confirmButtonColor: '#25D366',
        }).then(function(result) {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Generando enlace...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
            generarTokenYHacer(idAsistente, function(url) {
                var texto = encodeURIComponent('Hola ' + nombre + ', por favor firma el recibido de la dotación haciendo clic en este enlace (vence en 7 días):\n' + url);
                var waUrl = 'https://wa.me/?text=' + texto;
                Swal.fire({
                    title: 'Enlace generado',
                    html: '<p style="font-size:13px;">Comparte con <strong>' + nombre + '</strong>:</p>'
                          + '<div style="background:#f8f9fa;border-radius:8px;padding:10px;font-size:11px;word-break:break-all;margin-bottom:12px;border:1px solid #dee2e6;">' + url + '</div>',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fab fa-whatsapp"></i> Abrir WhatsApp',
                    cancelButtonText: 'Cerrar', confirmButtonColor: '#25D366',
                }).then(function(r) {
                    if (r.isConfirmed) window.open(waUrl, '_blank');
                });
            });
        });
    });

    function mostrarQrModal(data) {
        Swal.fire({
            title: '<i class="fas fa-qrcode" style="color:#bd9751;"></i> QR de auto-inscripcion',
            html:
                '<div style="text-align:center;">'
                + '<div style="background:white;padding:14px;border:2px solid #e5e7eb;border-radius:12px;display:inline-block;max-width:320px;width:100%;">' + data.qr_svg + '</div>'
                + '<p style="font-size:13px;color:#6b7280;margin-top:14px;line-height:1.4;">'
                +   'Acerca el celular de cada operario para que escanee.<br>Llena sus datos, sus tallas y firma.'
                + '</p>'
                + '<div style="background:#f3f4f6;padding:10px;border-radius:8px;margin-top:10px;font-size:11px;word-break:break-all;color:#374151;">' + data.url + '</div>'
                + '<div class="d-flex gap-2 mt-3">'
                +   '<button type="button" class="btn btn-sm btn-outline-secondary flex-fill" id="btnCopiarQrUrl"><i class="fas fa-copy"></i> Copiar enlace</button>'
                +   '<button type="button" class="btn btn-sm btn-outline-danger flex-fill" id="btnRotarQrUrl"><i class="fas fa-redo"></i> Generar nuevo</button>'
                + '</div>'
                + '</div>',
            width: 460, showConfirmButton: true, confirmButtonText: 'Cerrar', confirmButtonColor: '#bd9751',
            didOpen: function() {
                document.getElementById('btnCopiarQrUrl').addEventListener('click', function() {
                    navigator.clipboard.writeText(data.url).then(function() {
                        var b = document.getElementById('btnCopiarQrUrl');
                        b.innerHTML = '<i class="fas fa-check"></i> Copiado';
                    });
                });
                document.getElementById('btnRotarQrUrl').addEventListener('click', function() {
                    Swal.fire({ icon:'warning', title:'¿Generar un nuevo QR?',
                        html: 'El QR actual <strong>dejara de funcionar</strong>.',
                        showCancelButton:true, confirmButtonColor:'#dc3545', cancelButtonColor:'#6c757d',
                        confirmButtonText: 'Si, regenerar', cancelButtonText: 'Cancelar', reverseButtons: true,
                    }).then(function(res) { if (res.isConfirmed) generarQR(true); });
                });
            }
        });
    }
    function generarQR(regenerar) {
        if (!idEntregaActual) { Swal.fire('Guarda primero la entrega', 'Primero guarda la entrega como borrador para generar el QR.', 'info'); return; }
        Swal.fire({ title: 'Generando QR...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
        var fd = new FormData(); fd.append(csrfName, csrfHash);
        if (regenerar) fd.append('regenerar', '1');
        fetch(tokenInscripcionUrlBase + idEntregaActual, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }})
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) { Swal.fire('Error', data.error || 'No se pudo generar el QR', 'error'); return; }
            mostrarQrModal(data);
        })
        .catch(function() { Swal.fire('Error de conexion', 'No se pudo generar el QR.', 'error'); });
    }
    var btnQR = document.getElementById('btnMostrarQR');
    if (btnQR) btnQR.addEventListener('click', function() { generarQR(false); });

    var btnRefresh = document.getElementById('btnRefreshFirmas');
    if (btnRefresh) {
        btnRefresh.addEventListener('click', function() {
            if (!idEntregaActual) return;
            var origHtml = btnRefresh.innerHTML;
            btnRefresh.disabled = true;
            btnRefresh.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
            fetch(statusUrlBase + idEntregaActual, { method:'GET', headers:{'X-Requested-With':'XMLHttpRequest'}, cache:'no-store' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btnRefresh.disabled = false;
                btnRefresh.innerHTML = origHtml;
                if (!data.success) { Swal.fire('No se pudo actualizar', data.error || 'Intenta de nuevo', 'error'); return; }
                var elFirmados = document.getElementById('firmasFirmados');
                var elTotal    = document.getElementById('firmasTotal');
                var elPct      = document.getElementById('firmasPct');
                var elBar      = document.getElementById('firmasBar');
                if (elFirmados) elFirmados.textContent = data.firmados;
                if (elTotal) elTotal.textContent = data.total;
                if (elPct) elPct.textContent = data.pct;
                if (elBar) elBar.style.width = data.pct + '%';
                Swal.fire({ icon:'success', title:'Estado actualizado', toast:true, position:'top', showConfirmButton:false, timer:1200 });
            })
            .catch(function() {
                btnRefresh.disabled = false;
                btnRefresh.innerHTML = origHtml;
                Swal.fire('Error de conexión', 'No se pudo actualizar el estado.', 'error');
            });
        });
    }

    var btnFinalizar = document.getElementById('btnFinalizar');
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', function() {
            var firmados = parseInt(document.getElementById('firmasFirmados') ? document.getElementById('firmasFirmados').textContent : '0', 10);
            if (firmados === 0) {
                Swal.fire({ icon: 'warning', title: 'No hay operarios firmados', text: 'Al menos uno debe haber firmado para generar PDFs.', confirmButtonColor: '#bd9751' });
                return;
            }
            Swal.fire({
                title: '¿Finalizar la entrega?',
                html: 'Se generará <strong>UN PDF por cada operario firmado</strong> (' + firmados + ') y se subirán al reportList del cliente.<br><small>Los operarios que aún no firmaron no entran al PDF y no se podrá agregar más.</small>',
                icon: 'question', showCancelButton: true,
                confirmButtonText: 'Sí, finalizar', cancelButtonText: 'Cancelar', confirmButtonColor: '#bd9751',
            }).then(r => {
                if (r.isConfirmed) {
                    var i = document.createElement('input');
                    i.type = 'hidden'; i.name = 'finalizar'; i.value = '1';
                    document.getElementById('entregaDotForm').appendChild(i);
                    document.getElementById('entregaDotForm').submit();
                }
            });
        });
    }
});
</script>
