<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Firma Investigacion de Accidente/Incidente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --gold: #bd9751; --dark: #2c3e50; }
        body { background: #f0f2f5; min-height: 100vh; font-family: 'Segoe UI', sans-serif; font-size: 14px; }
        .top-bar { background: var(--dark); color: white; padding: 14px 16px 12px; position: sticky; top: 0; z-index: 10; box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
        .top-bar .logo { font-size: 11px; opacity: 0.6; text-transform: uppercase; letter-spacing: 1px; }
        .top-bar h6 { margin: 2px 0 0; font-size: 15px; }
        .top-bar p  { margin: 2px 0 0; font-size: 12px; opacity: 0.7; }
        .info-card { background: white; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,0.07); padding: 16px; margin-bottom: 12px; }
        .section-title { background: var(--dark); color: white; font-size: 11px; font-weight: 700;
                         letter-spacing: 0.8px; padding: 5px 10px; border-radius: 4px; margin-bottom: 10px; display: flex; align-items: center; gap: 6px; }
        .section-title i { opacity: 0.8; }
        .dato-label { font-size: 10px; text-transform: uppercase; color: #aaa; font-weight: 600; margin-bottom: 1px; }
        .dato-val   { font-size: 14px; color: #222; font-weight: 500; }
        .firma-section { background: white; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,0.07); padding: 16px; margin-bottom: 30px; }
        .firma-canvas { border: 2px dashed #ccc; border-radius: 8px; background: #fafafa; cursor: crosshair; width: 100%; touch-action: none; display: block; }
        .btn-firmar { background: linear-gradient(135deg, #28a745, #1e7e34); border: none; padding: 14px; font-size: 1rem; color: white; border-radius: 8px; width: 100%; font-weight: 700; letter-spacing: 0.3px; }
        .aviso-firma { background: #fffbeb; border: 1px solid #fbbf24; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #78350f; }
    </style>
</head>
<body>

<!-- Header sticky -->
<div class="top-bar">
    <div class="logo">EnterpriseSST</div>
    <h6><i class="fas fa-file-signature me-2"></i>Investigacion de <?= ($inv['tipo_evento'] ?? '') === 'accidente' ? 'Accidente' : 'Incidente' ?> de Trabajo</h6>
    <p><?= esc($cliente['nombre_cliente'] ?? '') ?> &middot; <?= date('d M Y', strtotime($inv['fecha_evento'])) ?></p>
</div>

<div class="container-fluid px-3 pt-3">

    <!-- Aviso -->
    <div class="aviso-firma mb-3">
        <i class="fas fa-pen-nib me-1"></i>
        <?php
        $tipoLabel = ['jefe' => 'Jefe Inmediato', 'copasst' => 'Representante COPASST', 'sst' => 'Responsable SST'];
        $label = $tipoLabel[$tipo] ?? ucfirst($tipo);
        ?>
        Revise la informacion de la investigacion y firme al final como <strong><?= $label ?></strong>.
        <?php if ($nombreFirmante): ?> (<?= esc($nombreFirmante) ?>)<?php endif; ?>
    </div>

    <!-- Datos del evento -->
    <div class="info-card">
        <div class="section-title"><i class="fas fa-exclamation-triangle"></i> DATOS DEL EVENTO</div>
        <div class="row g-3">
            <div class="col-6">
                <div class="dato-label">Tipo</div>
                <div class="dato-val"><?= ucfirst(esc($inv['tipo_evento'] ?? '')) ?></div>
            </div>
            <div class="col-6">
                <div class="dato-label">Fecha</div>
                <div class="dato-val"><?= date('d/m/Y', strtotime($inv['fecha_evento'])) ?></div>
            </div>
            <?php if (!empty($inv['lugar_exacto'])): ?>
            <div class="col-12">
                <div class="dato-label">Lugar</div>
                <div class="dato-val"><?= esc($inv['lugar_exacto']) ?></div>
            </div>
            <?php endif; ?>
            <div class="col-12">
                <div class="dato-label">Descripcion</div>
                <div class="dato-val" style="font-size:13px;"><?= nl2br(esc($inv['descripcion_detallada'] ?? '')) ?></div>
            </div>
        </div>
    </div>

    <!-- Datos del trabajador -->
    <div class="info-card">
        <div class="section-title"><i class="fas fa-user"></i> TRABAJADOR <?= ($inv['tipo_evento'] ?? '') === 'accidente' ? 'LESIONADO' : 'INVOLUCRADO' ?></div>
        <div class="row g-3">
            <div class="col-12">
                <div class="dato-label">Nombre</div>
                <div class="dato-val"><?= esc($inv['nombre_trabajador'] ?? '') ?></div>
            </div>
            <?php if (!empty($inv['cargo_trabajador'])): ?>
            <div class="col-6">
                <div class="dato-label">Cargo</div>
                <div class="dato-val"><?= esc($inv['cargo_trabajador']) ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($inv['area_trabajador'])): ?>
            <div class="col-6">
                <div class="dato-label">Area</div>
                <div class="dato-val"><?= esc($inv['area_trabajador']) ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== SECCION FIRMA ===== -->
    <div class="firma-section">
        <div class="section-title"><i class="fas fa-signature"></i> FIRMA — <?= strtoupper($label) ?></div>
        <?php if ($nombreFirmante): ?>
            <p style="font-weight:600; font-size:15px; margin-bottom:12px;"><?= esc($nombreFirmante) ?></p>
        <?php endif; ?>

        <div class="aviso-firma mb-3">
            <i class="fas fa-lock me-1"></i>
            Al firmar confirma su participacion en esta investigacion de <?= ($inv['tipo_evento'] ?? '') === 'accidente' ? 'accidente' : 'incidente' ?> de trabajo y acepta el tratamiento de sus datos personales conforme a la Ley 1581 de 2012.
        </div>

        <label class="form-label fw-bold mb-2">Dibuje su firma aqui <small class="text-muted fw-normal">(use su dedo)</small></label>
        <canvas id="firmaCanvas" class="firma-canvas" height="220"></canvas>
        <div class="d-flex justify-content-end mt-2 mb-3">
            <button type="button" id="btnLimpiar" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-eraser me-1"></i>Limpiar
            </button>
        </div>

        <button type="button" id="btnFirmar" class="btn btn-firmar">
            <i class="fas fa-signature me-2"></i>Firmar Investigacion
        </button>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var canvas  = document.getElementById('firmaCanvas');
    var ctx     = canvas.getContext('2d');
    var drawing = false;
    var dpr     = window.devicePixelRatio || 1;

    function resizeCanvas() {
        var rect = canvas.getBoundingClientRect();
        canvas.width  = rect.width * dpr;
        canvas.height = 220 * dpr;
        canvas.style.height = '220px';
        ctx.scale(dpr, dpr);
        ctx.strokeStyle = '#000';
        ctx.lineWidth   = 3;
        ctx.lineCap     = 'round';
        ctx.lineJoin    = 'round';
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    function getPos(e) {
        var rect = canvas.getBoundingClientRect();
        var src  = (e.touches && e.touches.length > 0) ? e.touches[0] : e;
        return { x: src.clientX - rect.left, y: src.clientY - rect.top };
    }
    function startDraw(e) {
        if (e.touches && e.touches.length > 1) return;
        drawing = true;
        var pos = getPos(e);
        ctx.beginPath(); ctx.moveTo(pos.x, pos.y);
        e.preventDefault();
    }
    function draw(e) {
        if (!drawing) return;
        if (e.touches && e.touches.length > 1) { drawing = false; return; }
        var pos = getPos(e);
        ctx.lineTo(pos.x, pos.y); ctx.stroke();
        e.preventDefault();
    }
    function stopDraw() { drawing = false; }

    canvas.addEventListener('mousedown',  startDraw);
    canvas.addEventListener('mousemove',  draw);
    canvas.addEventListener('mouseup',    stopDraw);
    canvas.addEventListener('mouseleave', stopDraw);
    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove',  draw,      { passive: false });
    canvas.addEventListener('touchend',   stopDraw);

    document.getElementById('btnLimpiar').addEventListener('click', function() {
        ctx.clearRect(0, 0, canvas.width / dpr, canvas.height / dpr);
    });

    document.getElementById('btnFirmar').addEventListener('click', function() {
        var imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        var pixeles = 0;
        for (var i = 3; i < imgData.data.length; i += 4) {
            if (imgData.data[i] > 128) pixeles++;
        }
        if (pixeles < 100) {
            Swal.fire('Firma requerida', 'Por favor dibuje su firma en el recuadro.', 'warning');
            return;
        }

        var firmaImagen = canvas.toDataURL('image/png');

        Swal.fire({
            title: 'Confirmar firma',
            html: '<p style="font-size:13px;">Verifique que su firma es correcta:</p>' +
                  '<img src="' + firmaImagen + '" style="max-width:100%;border:1px solid #ddd;border-radius:6px;margin-top:8px;">',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Si, firmar',
            cancelButtonText:  'Repetir',
            confirmButtonColor: '#28a745',
        }).then(function(result) {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Guardando firma...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });

            var formData = new FormData();
            formData.append('token',        '<?= esc($token) ?>');
            formData.append('firma_imagen', firmaImagen);

            fetch('/investigacion-accidente/procesar-firma-remota', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    document.getElementById('btnFirmar').disabled = true;
                    document.getElementById('btnFirmar').innerHTML = '<i class="fas fa-check-circle me-2"></i>Firma registrada';
                    document.getElementById('btnFirmar').style.background = '#6c757d';
                    Swal.fire({
                        icon: 'success',
                        title: 'Firma registrada!',
                        text: 'Gracias. Su firma ha sido guardada exitosamente en la investigacion.',
                        confirmButtonColor: '#28a745',
                        allowOutsideClick: false,
                    });
                } else {
                    Swal.fire('Error', data.error || 'No se pudo guardar la firma', 'error');
                }
            })
            .catch(function() {
                Swal.fire('Error', 'Error de conexion. Intente nuevamente.', 'error');
            });
        });
    });
});
</script>
</body>
</html>
