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
        .item-row { background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:10px; margin-bottom:8px; }
        .section-divider { font-size:13px; font-weight:700; color:#374151; margin:18px 0 8px; padding-top:10px; border-top:1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="card card-inscripcion">
        <div class="card-header-cap">
            <i class="fas fa-helmet-safety" style="font-size: 32px;"></i>
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
                Completa tus datos y los elementos que estás recibiendo. Despues firmas el recibido.
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

                <div class="section-divider">
                    <i class="fas fa-list"></i> ELEMENTOS QUE RECIBES
                </div>
                <p class="text-muted" style="font-size:12px; margin-bottom:8px;">
                    Agrega cada elemento que estás recibiendo (descripción, cantidad, talla y marca son opcionales pero útiles).
                </p>

                <div id="itemsContainer"></div>
                <button type="button" class="btn btn-sm btn-outline-dark mt-1" id="btnAddItem">
                    <i class="fas fa-plus"></i> Agregar elemento
                </button>

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
    function addItemRow() {
        var div = document.createElement('div');
        div.className = 'item-row';
        div.innerHTML =
            '<div class="d-flex justify-content-between align-items-center mb-2">'
          +     '<small style="font-weight:600;">Elemento</small>'
          +     '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" style="padding:2px 8px;"><i class="fas fa-times"></i></button>'
          + '</div>'
          + '<input type="text" name="item_descripcion[]" class="form-control form-control-sm mb-2" placeholder="Descripción del elemento">'
          + '<div class="row g-2">'
          +     '<div class="col-4"><input type="text" name="item_cantidad[]" class="form-control form-control-sm" placeholder="Cant." value="1"></div>'
          +     '<div class="col-4"><input type="text" name="item_talla[]" class="form-control form-control-sm" placeholder="Talla"></div>'
          +     '<div class="col-4"><input type="text" name="item_marca[]" class="form-control form-control-sm" placeholder="Marca"></div>'
          + '</div>';
        document.getElementById('itemsContainer').appendChild(div);
    }
    document.getElementById('btnAddItem').addEventListener('click', addItemRow);
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-remove-item');
        if (!btn) return;
        var row = btn.closest('.item-row');
        if (row) row.remove();
    });
    // Empezar con una fila vacía por comodidad
    addItemRow();

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
