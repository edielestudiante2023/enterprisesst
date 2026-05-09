<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Firma Recibido - Entrega de Dotación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --gold: #bd9751; --dark: #2c3e50; }
        body { background:#f0f2f5; min-height:100vh; font-family:'Segoe UI', sans-serif; font-size:14px; }
        .top-bar { background:var(--dark); color:white; padding:14px 16px 12px; position:sticky; top:0; z-index:10; box-shadow:0 2px 8px rgba(0,0,0,0.3); }
        .top-bar .logo { font-size:11px; opacity:0.6; text-transform:uppercase; letter-spacing:1px; }
        .top-bar h6 { margin:2px 0 0; font-size:15px; }
        .top-bar p { margin:2px 0 0; font-size:12px; opacity:0.7; }
        .entrega-card { background:white; border-radius:10px; box-shadow:0 1px 6px rgba(0,0,0,0.07); padding:16px; margin-bottom:12px; }
        .section-title { background:var(--dark); color:white; font-size:11px; font-weight:700; letter-spacing:0.8px; padding:5px 10px; border-radius:4px; margin-bottom:10px; display:flex; align-items:center; gap:6px; }
        .dato-label { font-size:10px; text-transform:uppercase; color:#aaa; font-weight:600; margin-bottom:1px; }
        .dato-val { font-size:14px; color:#222; font-weight:500; }
        .firma-section { background:white; border-radius:10px; box-shadow:0 1px 6px rgba(0,0,0,0.07); padding:16px; margin-bottom:30px; }
        .firma-canvas { border:2px dashed #ccc; border-radius:8px; background:#fafafa; cursor:crosshair; width:100%; touch-action:none; display:block; }
        .btn-firmar { background:linear-gradient(135deg,#28a745,#1e7e34); border:none; padding:14px; font-size:1rem; color:white; border-radius:8px; width:100%; font-weight:700; letter-spacing:0.3px; }
        .btn-firmar:disabled { opacity:0.6; cursor:not-allowed; }
        .aviso-firma { background:#fffbeb; border:1px solid #fbbf24; border-radius:8px; padding:10px 12px; font-size:12px; color:#78350f; }
        table.items-table { width:100%; border-collapse:collapse; font-size:13px; }
        table.items-table th { background:#f3f4f6; text-align:left; padding:6px 8px; font-size:11px; color:#374151; }
        table.items-table td { padding:6px 8px; border-bottom:1px solid #e5e7eb; }
        .badge-buen-estado { background:#dcfce7; color:#15803d; padding:4px 10px; border-radius:20px; font-weight:600; font-size:12px; }
        .badge-mal-estado { background:#fee2e2; color:#991b1b; padding:4px 10px; border-radius:20px; font-weight:600; font-size:12px; }
    </style>
</head>
<body>

<div class="top-bar">
    <div class="logo">EnterpriseSST</div>
    <h6><i class="fas fa-mitten me-2"></i>Entrega de Dotación</h6>
    <p><?= esc($cliente['nombre_cliente'] ?? '') ?> &middot; <?= date('d M Y', strtotime($entrega['fecha_entrega'])) ?></p>
</div>

<div class="container-fluid px-3 pt-3">
    <div class="aviso-firma mb-3">
        <i class="fas fa-pen-nib me-1"></i>
        Hola <strong><?= esc($asistente['nombre_completo']) ?></strong>, revisa el resumen y firma para confirmar el recibido de la dotación.
    </div>

    <div class="entrega-card">
        <div class="section-title"><i class="fas fa-clipboard-list"></i> DATOS DE LA ENTREGA</div>
        <div class="row g-3">
            <div class="col-6"><div class="dato-label">Fecha</div><div class="dato-val"><?= date('d/m/Y', strtotime($entrega['fecha_entrega'])) ?></div></div>
            <?php if (!empty($entrega['hora'])): ?>
            <div class="col-6"><div class="dato-label">Hora</div><div class="dato-val"><?= date('g:i A', strtotime($entrega['hora'])) ?></div></div>
            <?php endif; ?>
            <?php if (!empty($entrega['lugar'])): ?>
            <div class="col-12"><div class="dato-label">Lugar</div><div class="dato-val"><?= esc($entrega['lugar']) ?></div></div>
            <?php endif; ?>
            <?php if (!empty($entrega['responsable_entrega'])): ?>
            <div class="col-12"><div class="dato-label">Responsable de entrega</div><div class="dato-val"><?= esc($entrega['responsable_entrega']) ?></div></div>
            <?php endif; ?>
            <?php if (!empty($entrega['tipo_dotacion'])): ?>
            <div class="col-12"><div class="dato-label">Tipo de dotación</div><div class="dato-val"><?= esc($entrega['tipo_dotacion']) ?></div></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="entrega-card">
        <div class="section-title"><i class="fas fa-list"></i> ELEMENTOS QUE RECIBES</div>
        <?php if (!empty($items)): ?>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Elemento</th>
                    <th style="width:60px;">Cant.</th>
                    <th style="width:80px;">Marca</th>
                    <th style="width:60px;">Tu talla</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                <?php $tallaOp = $tallas_map[$it['id']] ?? ''; ?>
                <tr>
                    <td><?= esc($it['descripcion']) ?></td>
                    <td><?= esc($it['cantidad']) ?></td>
                    <td><?= esc($it['marca'] ?? '-') ?></td>
                    <td><strong><?= $tallaOp !== '' ? esc($tallaOp) : '-' ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted" style="font-size:13px; margin:0;">Sin elementos registrados.</p>
        <?php endif; ?>
    </div>

    <div class="entrega-card">
        <div class="section-title"><i class="fas fa-clipboard-check"></i> CONFIRMACIÓN DE BUEN ESTADO</div>
        <?php if (($asistente['recibido_buen_estado'] ?? '') === 'si'): ?>
            <span class="badge-buen-estado"><i class="fas fa-check-circle"></i> Recibido en buen estado</span>
        <?php elseif (($asistente['recibido_buen_estado'] ?? '') === 'no'): ?>
            <span class="badge-mal-estado"><i class="fas fa-times-circle"></i> NO en buen estado</span>
            <?php if (!empty($asistente['observaciones_recibido'])): ?>
            <div class="text-muted mt-2" style="font-size:12px;">
                <strong>Problema reportado:</strong><br>
                <?= nl2br(esc($asistente['observaciones_recibido'])) ?>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <span class="text-muted" style="font-size:12px;">Sin confirmación.</span>
        <?php endif; ?>
    </div>

    <div class="firma-section">
        <div class="section-title"><i class="fas fa-signature"></i> TU FIRMA DE RECIBIDO</div>
        <div class="dato-label" style="margin-bottom:8px;">Firmando como:</div>
        <div class="dato-val mb-2"><?= esc($asistente['nombre_completo']) ?>
            <?php if (!empty($asistente['numero_documento'])): ?>
                <small class="text-muted">(<?= esc($asistente['tipo_documento']) ?> <?= esc($asistente['numero_documento']) ?>)</small>
            <?php endif; ?>
        </div>

        <p class="text-muted" style="font-size:12px; margin-bottom:8px;">
            Al firmar, declaro haber recibido los elementos listados con las tallas y el estado registrados arriba.
        </p>

        <canvas id="canvasFirma" class="firma-canvas" width="600" height="220" style="height:220px;"></canvas>

        <div class="d-flex justify-content-between mt-2 mb-3">
            <button type="button" id="btnLimpiar" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-eraser"></i> Limpiar
            </button>
            <small class="text-muted align-self-center">Firma con el dedo o el mouse</small>
        </div>

        <button type="button" id="btnFirmar" class="btn-firmar" disabled>
            <i class="fas fa-check-circle"></i> Confirmar recibido
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function() {
    var canvas = document.getElementById('canvasFirma');
    var ctx = canvas.getContext('2d');
    var btnFirmar = document.getElementById('btnFirmar');
    var btnLimpiar = document.getElementById('btnLimpiar');
    var dibujando = false;
    var hayFirma = false;
    var ult = { x: 0, y: 0 };

    function ajustarCanvas() {
        var rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#000';
    }
    ajustarCanvas();
    window.addEventListener('resize', ajustarCanvas);

    function getPos(e) {
        var rect = canvas.getBoundingClientRect();
        var t = e.touches ? e.touches[0] : e;
        return { x: t.clientX - rect.left, y: t.clientY - rect.top };
    }
    function start(e) { e.preventDefault(); dibujando = true; ult = getPos(e); }
    function move(e) {
        if (!dibujando) return;
        e.preventDefault();
        var p = getPos(e);
        ctx.beginPath();
        ctx.moveTo(ult.x, ult.y);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        ult = p;
        hayFirma = true;
        btnFirmar.disabled = false;
    }
    function end() { dibujando = false; }

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    canvas.addEventListener('mouseup', end);
    canvas.addEventListener('mouseleave', end);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move,  { passive: false });
    canvas.addEventListener('touchend', end);

    btnLimpiar.addEventListener('click', function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hayFirma = false;
        btnFirmar.disabled = true;
    });

    btnFirmar.addEventListener('click', function() {
        if (!hayFirma) return;

        Swal.fire({
            title: 'Confirmar recibido',
            text: 'Al firmar declaras haber recibido la dotación con la información registrada. ¿Continuar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, firmar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
        }).then(function(r) {
            if (!r.isConfirmed) return;

            Swal.fire({ title: 'Enviando firma...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });

            var firmaBase64 = canvas.toDataURL('image/png');
            var fd = new FormData();
            fd.append('token', '<?= esc($token) ?>');
            fd.append('firma_imagen', firmaBase64);

            fetch('<?= site_url('entrega-dotacion/procesar-firma-remota') ?>', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Recibido confirmado!',
                        text: 'Gracias por confirmar la recepción de los elementos.',
                        confirmButtonText: 'Cerrar',
                    }).then(function() {
                        document.body.innerHTML = '<div style="padding:40px; text-align:center;"><i class="fas fa-check-circle" style="font-size:64px; color:#28a745;"></i><h3>Firma registrada</h3><p>Ya puedes cerrar esta ventana.</p></div>';
                    });
                } else {
                    Swal.fire('Error', data.error || 'No se pudo registrar la firma', 'error');
                }
            })
            .catch(function() {
                Swal.fire('Error', 'Error de conexión. Intenta de nuevo.', 'error');
            });
        });
    });
})();
</script>

</body>
</html>
