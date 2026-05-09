<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inscripcion - Entrega de Dotación</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); min-height: 100vh; padding: 20px 0; }
        .card-inscripcion { max-width: 560px; margin: 0 auto; border: none; border-radius: 14px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); }
        .card-header-cap { background: linear-gradient(135deg, #bd9751 0%, #d4af6a 100%); color: white; padding: 22px; border-radius: 14px 14px 0 0; text-align: center; }
        .card-header-cap h4 { margin: 0; font-weight: 700; }
        .card-header-cap .subtitle { color: rgba(255,255,255,0.9); font-size: 13px; margin-top: 6px; }
        .info-box { background: #f8f9fa; border-left: 4px solid #bd9751; padding: 14px 16px; border-radius: 6px; margin-bottom: 18px; font-size: 13px; }
        .info-box p { margin: 4px 0; }
        .form-label { font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 4px; }
        .form-control, .form-select { font-size: 16px; padding: 10px 12px; border-radius: 8px; }
        .req::after { content: ' *'; color: #dc3545; }
        .btn-submit { background: #bd9751; color: white; padding: 14px; font-size: 16px; font-weight: 600; border: none; border-radius: 10px; width: 100%; }
        .btn-submit:hover { background: #a88240; color: white; }
        .item-talla-row { background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:10px; margin-bottom:8px; }
        .section-divider { font-size:13px; font-weight:700; color:#374151; margin:18px 0 8px; padding-top:10px; border-top:1px solid #e5e7eb; }
        .estado-radio-group { display:flex; gap:10px; margin-top:8px; }
        .estado-radio-btn { flex:1; padding:14px; border:2px solid #e5e7eb; border-radius:8px; text-align:center; cursor:pointer; font-weight:600; transition:all 0.2s; }
        .estado-radio-btn input { display:none; }
        .estado-radio-btn.selected-si { border-color:#16a34a; background:#dcfce7; color:#15803d; }
        .estado-radio-btn.selected-no { border-color:#dc2626; background:#fee2e2; color:#991b1b; }
    </style>
</head>
<body>
    <div class="card card-inscripcion">
        <div class="card-header-cap">
            <i class="fas fa-mitten" style="font-size: 32px;"></i>
            <h4 class="mt-2">Recibido de dotación</h4>
            <div class="subtitle">
                <?= esc($cliente['nombre_cliente'] ?? '') ?>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="info-box">
                <p><strong>Fecha de entrega:</strong> <?= !empty($entrega['fecha_entrega']) ? date('d/m/Y', strtotime($entrega['fecha_entrega'])) : '-' ?></p>
                <?php if (!empty($entrega['responsable_entrega'])): ?>
                <p><strong>Responsable:</strong> <?= esc($entrega['responsable_entrega']) ?></p>
                <?php endif; ?>
                <?php if (!empty($entrega['tipo_dotacion'])): ?>
                <p><strong>Tipo de dotación:</strong> <?= esc($entrega['tipo_dotacion']) ?></p>
                <?php endif; ?>
            </div>

            <p class="text-muted" style="font-size:13px;">
                Completa tus datos. Los elementos a entregar ya estan definidos por el responsable, solo digita tu <strong>talla</strong> en cada uno.
            </p>

            <form id="formInscripcion">
                <input type="hidden" name="token" value="<?= esc($token) ?>">

                <div class="mb-3">
                    <label class="form-label req">Nombre completo</label>
                    <input type="text" name="nombre_completo" class="form-control" required maxlength="150" autofocus>
                </div>

                <div class="row">
                    <div class="col-4">
                        <label class="form-label">Tipo doc</label>
                        <select name="tipo_documento" class="form-select">
                            <option value="CC" selected>CC</option>
                            <option value="CE">CE</option>
                            <option value="PA">PA</option>
                            <option value="TI">TI</option>
                            <option value="NIT">NIT</option>
                        </select>
                    </div>
                    <div class="col-8">
                        <label class="form-label req">Numero documento</label>
                        <input type="text" name="numero_documento" class="form-control" required maxlength="20" inputmode="numeric">
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label">Cargo</label>
                    <input type="text" name="cargo" class="form-control" maxlength="100">
                </div>

                <div class="mb-3">
                    <label class="form-label">Area / Dependencia</label>
                    <input type="text" name="area_dependencia" class="form-control" maxlength="100">
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" maxlength="120">
                </div>

                <div class="mb-3">
                    <label class="form-label">Celular</label>
                    <input type="tel" name="celular" class="form-control" maxlength="20" inputmode="tel">
                </div>

                <?php if (!empty($items)): ?>
                <div class="section-divider">
                    <i class="fas fa-list"></i> ELEMENTOS QUE RECIBES — DIGITA TU TALLA
                </div>
                <p class="text-muted" style="font-size:12px; margin-bottom:8px;">
                    Los elementos están predefinidos. Solo escribe tu talla en cada uno (ej: M, L, 38, 42, etc).
                </p>

                <?php foreach ($items as $it): ?>
                <div class="item-talla-row">
                    <div style="font-weight:600; font-size:14px;"><?= esc($it['descripcion']) ?></div>
                    <div class="text-muted" style="font-size:11px; margin-bottom:6px;">
                        Cantidad: <?= esc($it['cantidad']) ?>
                        <?php if (!empty($it['marca'])): ?> &middot; Marca: <?= esc($it['marca']) ?><?php endif; ?>
                    </div>
                    <input type="text"
                        name="tallas[<?= (int)$it['id'] ?>]"
                        class="form-control form-control-sm"
                        placeholder="Tu talla (ej: M, L, 38, 42...)"
                        maxlength="50">
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="alert alert-warning py-2" style="font-size:13px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    Esta entrega aún no tiene elementos definidos. Pide al responsable que los registre antes de continuar.
                </div>
                <?php endif; ?>

                <div class="section-divider">
                    <i class="fas fa-clipboard-check"></i> ¿RECIBIÓ LA DOTACIÓN EN BUEN ESTADO?
                </div>
                <p class="text-muted" style="font-size:12px; margin-bottom:8px;">
                    Esta confirmación es importante. Si dices NO, describe qué estaba mal.
                </p>

                <div class="estado-radio-group" id="estadoRadioGroup">
                    <label class="estado-radio-btn" id="lblEstadoSi">
                        <input type="radio" name="recibido_buen_estado" value="si" required>
                        <i class="fas fa-check-circle"></i> SÍ, en buen estado
                    </label>
                    <label class="estado-radio-btn" id="lblEstadoNo">
                        <input type="radio" name="recibido_buen_estado" value="no" required>
                        <i class="fas fa-times-circle"></i> NO, hay problemas
                    </label>
                </div>

                <div id="observacionesRecibidoBox" class="mb-3 mt-3" style="display:none;">
                    <label class="form-label req">Describe qué problema encontraste</label>
                    <textarea name="observaciones_recibido" class="form-control" rows="3" maxlength="500" placeholder="Ej: Los guantes están rotos, el casco vino con grietas, las botas no son de mi talla..."></textarea>
                </div>

                <button type="submit" class="btn-submit mt-4" id="btnEnviar">
                    <i class="fas fa-arrow-right"></i> Continuar a firmar
                </button>
            </form>

            <p class="text-center text-muted mt-3" style="font-size: 11px;">
                Tus datos seran usados unicamente para esta entrega de dotación.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    var lblSi = document.getElementById('lblEstadoSi');
    var lblNo = document.getElementById('lblEstadoNo');
    var obsBox = document.getElementById('observacionesRecibidoBox');

    document.querySelectorAll('input[name="recibido_buen_estado"]').forEach(function(r) {
        r.addEventListener('change', function() {
            lblSi.classList.remove('selected-si');
            lblNo.classList.remove('selected-no');
            if (r.value === 'si') {
                lblSi.classList.add('selected-si');
                obsBox.style.display = 'none';
                obsBox.querySelector('textarea').required = false;
            } else {
                lblNo.classList.add('selected-no');
                obsBox.style.display = 'block';
                obsBox.querySelector('textarea').required = true;
            }
        });
    });

    document.getElementById('formInscripcion').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = e.target;
        var btn = document.getElementById('btnEnviar');
        var fd = new FormData(form);

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

        fetch('<?= base_url('entrega-dotacion/procesar-inscripcion') ?>', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-arrow-right"></i> Continuar a firmar';
                if (data.duplicado) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Ya estas registrado',
                        text: 'Ya hay un operario con este numero de documento en esta entrega.',
                        confirmButtonColor: '#bd9751',
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'No se pudo registrar',
                        text: data.error || 'Intenta de nuevo.',
                        confirmButtonColor: '#bd9751',
                    });
                }
                return;
            }
            window.location.href = data.url_firmar;
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-arrow-right"></i> Continuar a firmar';
            Swal.fire({
                icon: 'error',
                title: 'Error de conexion',
                text: 'Verifica tu conexion a internet e intenta de nuevo.',
                confirmButtonColor: '#bd9751',
            });
        });
    });
    </script>
</body>
</html>
