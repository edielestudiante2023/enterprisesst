<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inscripcion - <?= esc($acta['tema'] ?? 'Capacitacion') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); min-height: 100vh; padding: 20px 0; }
        .card-inscripcion { max-width: 520px; margin: 0 auto; border: none; border-radius: 14px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); }
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
    </style>
</head>
<body>
    <div class="card card-inscripcion">
        <div class="card-header-cap">
            <i class="fas fa-clipboard-list" style="font-size: 32px;"></i>
            <h4 class="mt-2">Registro de asistencia</h4>
            <div class="subtitle">
                <?= esc($cliente['nombre_cliente'] ?? '') ?>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="info-box">
                <p><strong>Tema:</strong> <?= esc($acta['tema'] ?? '-') ?></p>
                <p><strong>Fecha:</strong> <?= !empty($acta['fecha_capacitacion']) ? date('d/m/Y', strtotime($acta['fecha_capacitacion'])) : '-' ?></p>
                <?php if (!empty($acta['nombre_capacitador'])): ?>
                <p><strong>Capacitador:</strong> <?= esc($acta['nombre_capacitador']) ?></p>
                <?php endif; ?>
            </div>

            <p class="text-muted" style="font-size:13px;">
                Completa tus datos para registrar tu asistencia. Despues podras firmar electronicamente.
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

                <div class="mb-4">
                    <label class="form-label">Celular</label>
                    <input type="tel" name="celular" class="form-control" maxlength="20" inputmode="tel">
                </div>

                <button type="submit" class="btn-submit" id="btnEnviar">
                    <i class="fas fa-check"></i> Registrarme
                </button>
            </form>

            <p class="text-center text-muted mt-3" style="font-size: 11px;">
                Tus datos seran usados unicamente para el acta de capacitacion.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.getElementById('formInscripcion').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = e.target;
        var btn = document.getElementById('btnEnviar');
        var fd = new FormData(form);

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

        fetch('<?= base_url('acta-capacitacion/procesar-inscripcion') ?>', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Registrarme';
                if (data.duplicado) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Ya estas registrado',
                        text: 'Ya hay un asistente con este numero de documento en esta capacitacion.',
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
            // Exito → redirigir directo al canvas de firma
            window.location.href = data.url_firmar;
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Registrarme';
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
