<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Firma Acta de Capacitación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --gold: #bd9751; --dark: #2c3e50; }
        body { background:#f0f2f5; min-height:100vh; font-family:'Segoe UI', sans-serif; font-size:14px; }
        .top-bar { background:var(--dark); color:white; padding:14px 16px 12px; position:sticky; top:0; z-index:10; box-shadow:0 2px 8px rgba(0,0,0,0.3); }
        .top-bar .logo { font-size:11px; opacity:0.6; text-transform:uppercase; letter-spacing:1px; }
        .top-bar h6 { margin:2px 0 0; font-size:15px; }
        .top-bar p { margin:2px 0 0; font-size:12px; opacity:0.7; }
        .acta-card { background:white; border-radius:10px; box-shadow:0 1px 6px rgba(0,0,0,0.07); padding:16px; margin-bottom:12px; }
        .section-title { background:var(--dark); color:white; font-size:11px; font-weight:700; letter-spacing:0.8px; padding:5px 10px; border-radius:4px; margin-bottom:10px; display:flex; align-items:center; gap:6px; }
        .dato-label { font-size:10px; text-transform:uppercase; color:#aaa; font-weight:600; margin-bottom:1px; }
        .dato-val { font-size:14px; color:#222; font-weight:500; }
        .firma-section { background:white; border-radius:10px; box-shadow:0 1px 6px rgba(0,0,0,0.07); padding:16px; margin-bottom:30px; }
        .firma-canvas { border:2px dashed #ccc; border-radius:8px; background:#fafafa; cursor:crosshair; width:100%; touch-action:none; display:block; }
        .btn-firmar { background:linear-gradient(135deg,#28a745,#1e7e34); border:none; padding:14px; font-size:1rem; color:white; border-radius:8px; width:100%; font-weight:700; letter-spacing:0.3px; }
        .btn-firmar:disabled { opacity:0.6; cursor:not-allowed; }
        .aviso-firma { background:#fffbeb; border:1px solid #fbbf24; border-radius:8px; padding:10px 12px; font-size:12px; color:#78350f; }
    </style>
</head>
<body>

<div class="top-bar">
    <div class="logo">EnterpriseSST</div>
    <h6><i class="fas fa-graduation-cap me-2"></i>Acta de Capacitación</h6>
    <p><?= esc($cliente['nombre_cliente'] ?? '') ?> &middot; <?= date('d M Y', strtotime($acta['fecha_capacitacion'])) ?></p>
</div>

<div class="container-fluid px-3 pt-3">
    <div class="aviso-firma mb-3">
        <i class="fas fa-pen-nib me-1"></i>
        Hola <strong><?= esc($asistente['nombre_completo']) ?></strong>, revisa el contenido del acta y firma al final para confirmar tu asistencia.
    </div>

    <div class="acta-card">
        <div class="section-title"><i class="fas fa-clipboard-list"></i> DATOS DE LA CAPACITACIÓN</div>
        <div class="row g-3">
            <div class="col-12"><div class="dato-label">Tema</div><div class="dato-val"><?= esc($acta['tema']) ?></div></div>
            <div class="col-6"><div class="dato-label">Fecha</div><div class="dato-val"><?= date('d/m/Y', strtotime($acta['fecha_capacitacion'])) ?></div></div>
            <div class="col-6"><div class="dato-label">Modalidad</div><div class="dato-val"><?= ucfirst($acta['modalidad']) ?></div></div>
            <?php if (!empty($acta['hora_inicio'])): ?>
            <div class="col-6"><div class="dato-label">Hora inicio</div><div class="dato-val"><?= date('g:i A', strtotime($acta['hora_inicio'])) ?></div></div>
            <?php endif; ?>
            <?php if (!empty($acta['hora_fin'])): ?>
            <div class="col-6"><div class="dato-label">Hora fin</div><div class="dato-val"><?= date('g:i A', strtotime($acta['hora_fin'])) ?></div></div>
            <?php endif; ?>
            <div class="col-12"><div class="dato-label">Dictada por</div><div class="dato-val"><?= esc($acta['dictada_por']) ?><?= !empty($acta['entidad_capacitadora']) ? ' — ' . esc($acta['entidad_capacitadora']) : '' ?></div></div>
            <?php if (!empty($acta['nombre_capacitador'])): ?>
            <div class="col-12"><div class="dato-label">Capacitador</div><div class="dato-val"><?= esc($acta['nombre_capacitador']) ?></div></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($acta['objetivos'])): ?>
    <div class="acta-card">
        <div class="section-title"><i class="fas fa-bullseye"></i> OBJETIVOS</div>
        <p style="margin:0;"><?= nl2br(esc($acta['objetivos'])) ?></p>
    </div>
    <?php endif; ?>

    <?php if (!empty($acta['contenido'])): ?>
    <div class="acta-card">
        <div class="section-title"><i class="fas fa-book"></i> CONTENIDO</div>
        <p style="margin:0;"><?= nl2br(esc($acta['contenido'])) ?></p>
    </div>
    <?php endif; ?>

    <div class="firma-section">
        <div class="section-title"><i class="fas fa-signature"></i> TU FIRMA</div>
        <div class="dato-label" style="margin-bottom:8px;">Firmando como:</div>
        <div class="dato-val mb-2"><?= esc($asistente['nombre_completo']) ?>
            <?php if (!empty($asistente['numero_documento'])): ?>
                <small class="text-muted">(<?= esc($asistente['tipo_documento']) ?> <?= esc($asistente['numero_documento']) ?>)</small>
            <?php endif; ?>
        </div>

        <canvas id="canvasFirma" class="firma-canvas" width="600" height="220" style="height:220px;"></canvas>

        <div class="d-flex justify-content-between mt-2 mb-3">
            <button type="button" id="btnLimpiar" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-eraser"></i> Limpiar
            </button>
            <small class="text-muted align-self-center">Firma con el dedo o el mouse</small>
        </div>

        <button type="button" id="btnFirmar" class="btn-firmar" disabled>
            <i class="fas fa-check-circle"></i> Confirmar firma
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
    function start(e) {
        e.preventDefault();
        dibujando = true;
        ult = getPos(e);
    }
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
            title: 'Confirmar firma',
            text: 'Tu firma será registrada en el acta. ¿Continuar?',
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

            fetch('<?= site_url('acta-capacitacion/procesar-firma-remota') ?>', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Firma registrada!',
                        text: 'Gracias por confirmar tu asistencia.',
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
