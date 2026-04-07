<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Firma Acta de Visita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --gold: #bd9751; --dark: #2c3e50; }
        body { background: #f0f2f5; min-height: 100vh; font-family: 'Segoe UI', sans-serif; font-size: 14px; }
        .top-bar { background: var(--dark); color: white; padding: 14px 16px 12px; position: sticky; top: 0; z-index: 10; box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
        .top-bar .logo { font-size: 11px; opacity: 0.6; text-transform: uppercase; letter-spacing: 1px; }
        .top-bar h6 { margin: 2px 0 0; font-size: 15px; }
        .top-bar p  { margin: 2px 0 0; font-size: 12px; opacity: 0.7; }
        .acta-card { background: white; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,0.07); padding: 16px; margin-bottom: 12px; }
        .section-title { background: var(--dark); color: white; font-size: 11px; font-weight: 700;
                         letter-spacing: 0.8px; padding: 5px 10px; border-radius: 4px; margin-bottom: 10px; display: flex; align-items: center; gap: 6px; }
        .section-title i { opacity: 0.8; }
        .dato-label { font-size: 10px; text-transform: uppercase; color: #aaa; font-weight: 600; margin-bottom: 1px; }
        .dato-val   { font-size: 14px; color: #222; font-weight: 500; }
        .integrante-row { display: flex; justify-content: space-between; align-items: center; padding: 7px 0; border-bottom: 1px solid #f0f0f0; }
        .integrante-row:last-child { border-bottom: none; }
        .tema-item { padding: 6px 0; border-bottom: 1px solid #f5f5f5; font-size: 13px; line-height: 1.4; }
        .tema-item:last-child { border-bottom: none; }
        .tema-num { font-weight: 700; color: var(--gold); margin-right: 6px; }
        .tbl-mini { width: 100%; border-collapse: collapse; font-size: 12px; }
        .tbl-mini th { background: #f8f8f8; color: #777; font-size: 10px; text-transform: uppercase; padding: 5px 6px; font-weight: 600; }
        .tbl-mini td { padding: 5px 6px; border-bottom: 1px solid #f5f5f5; vertical-align: top; }
        .tbl-mini tr:last-child td { border-bottom: none; }
        .badge-rol { font-size: 10px; padding: 3px 7px; background: #eee; color: #555; border-radius: 20px; font-weight: 600; }
        .dias-badge { display: inline-block; padding: 2px 6px; border-radius: 10px; font-size: 11px; font-weight: 600; }
        .dias-vencido { background: #fee2e2; color: #dc2626; }
        .dias-urgente { background: #fef3c7; color: #d97706; }
        .dias-ok      { background: #dcfce7; color: #16a34a; }
        .firma-section { background: white; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,0.07); padding: 16px; margin-bottom: 30px; }
        .firma-canvas { border: 2px dashed #ccc; border-radius: 8px; background: #fafafa; cursor: crosshair; width: 100%; touch-action: none; display: block; }
        .btn-firmar { background: linear-gradient(135deg, #28a745, #1e7e34); border: none; padding: 14px; font-size: 1rem; color: white; border-radius: 8px; width: 100%; font-weight: 700; letter-spacing: 0.3px; }
        .aviso-firma { background: #fffbeb; border: 1px solid #fbbf24; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #78350f; }
    </style>
</head>
<body>

<!-- Header sticky -->
<div class="top-bar">
    <div class="logo">Cycloid Talent · SST</div>
    <h6><i class="fas fa-file-signature me-2"></i>Acta de Visita</h6>
    <p><?= esc($cliente['nombre_cliente'] ?? '') ?> &middot; <?= date('d M Y', strtotime($acta['fecha_visita'])) ?></p>
</div>

<div class="container-fluid px-3 pt-3">

    <!-- Aviso -->
    <div class="aviso-firma mb-3">
        <i class="fas fa-pen-nib me-1"></i>
        <?php $tipoLabel = ['administrador' => 'Administrador', 'vigia' => 'Vigia SST', 'consultor' => 'Consultor'];
              $label = $tipoLabel[$tipo] ?? ucfirst($tipo); ?>
        Revise el contenido del acta y firme al final como <strong><?= $label ?></strong>.
        <?php if ($nombreFirmante): ?> (<?= esc($nombreFirmante) ?>)<?php endif; ?>
    </div>

    <!-- 1. DATOS GENERALES -->
    <div class="acta-card">
        <div class="section-title"><i class="fas fa-clipboard-list"></i> DATOS DE LA VISITA</div>
        <div class="row g-3">
            <div class="col-6">
                <div class="dato-label">Fecha</div>
                <div class="dato-val"><?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?></div>
            </div>
            <div class="col-6">
                <div class="dato-label">Hora</div>
                <div class="dato-val"><?= date('g:i A', strtotime($acta['hora_visita'])) ?></div>
            </div>
            <div class="col-12">
                <div class="dato-label">Motivo</div>
                <div class="dato-val"><?= esc($acta['motivo']) ?></div>
            </div>
            <div class="col-6">
                <div class="dato-label">Modalidad</div>
                <div class="dato-val"><?= esc($acta['modalidad'] ?? 'Presencial') ?></div>
            </div>
            <div class="col-6">
                <div class="dato-label">Cliente</div>
                <div class="dato-val" style="font-size:13px;"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>
            </div>
            <?php if (!empty($acta['cartera'])): ?>
            <div class="col-6">
                <div class="dato-label">Cartera</div>
                <div class="dato-val">$<?= number_format((float)$acta['cartera'], 0, ',', '.') ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. INTEGRANTES -->
    <?php if (!empty($integrantes)): ?>
    <div class="acta-card">
        <div class="section-title"><i class="fas fa-users"></i> INTEGRANTES</div>
        <?php foreach ($integrantes as $int): ?>
        <div class="integrante-row">
            <span style="font-weight:500;"><?= esc($int['nombre']) ?></span>
            <span class="badge-rol"><?= esc($int['rol']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- 3. TEMAS ABIERTOS Y VENCIDOS -->
    <?php if (!empty($mantenimientos) || !empty($pendientesAbiertos)): ?>
    <div class="acta-card">
        <div class="section-title"><i class="fas fa-exclamation-triangle"></i> TEMAS ABIERTOS Y VENCIDOS</div>

        <?php if (!empty($mantenimientos)): ?>
        <p style="font-size:11px; font-weight:700; color:#666; text-transform:uppercase; margin-bottom:6px;">Mantenimientos</p>
        <table class="tbl-mini mb-3">
            <thead><tr><th>Mantenimiento</th><th>Vencimiento</th></tr></thead>
            <tbody>
            <?php foreach ($mantenimientos as $m): ?>
            <tr>
                <td><?= esc($m['detalle_mantenimiento'] ?? '') ?></td>
                <td style="white-space:nowrap;">
                    <?php
                    $dias = (int)((strtotime($m['fecha_vencimiento']) - time()) / 86400);
                    $cls  = $dias < 0 ? 'dias-vencido' : ($dias <= 7 ? 'dias-urgente' : 'dias-ok');
                    ?>
                    <span class="dias-badge <?= $cls ?>"><?= date('d/m/Y', strtotime($m['fecha_vencimiento'])) ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <?php if (!empty($pendientesAbiertos)): ?>
        <p style="font-size:11px; font-weight:700; color:#666; text-transform:uppercase; margin-bottom:6px;">Pendientes abiertos</p>
        <table class="tbl-mini">
            <thead><tr><th>Actividad</th><th>Responsable</th><th>Cierre</th><th>Dias</th></tr></thead>
            <tbody>
            <?php foreach ($pendientesAbiertos as $p): ?>
            <?php
            $diasP = $p['fecha_cierre'] ? (int)((strtotime($p['fecha_cierre']) - time()) / 86400) : 999;
            $clsP  = $diasP < 0 ? 'dias-vencido' : ($diasP <= 7 ? 'dias-urgente' : 'dias-ok');
            ?>
            <tr>
                <td><?= esc($p['tarea_actividad']) ?></td>
                <td><?= esc($p['responsable']) ?></td>
                <td style="white-space:nowrap;"><?= $p['fecha_cierre'] ? date('d/m/Y', strtotime($p['fecha_cierre'])) : '-' ?></td>
                <td><span class="dias-badge <?= $clsP ?>"><?= $diasP < 999 ? $diasP : '-' ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- 4. TEMAS TRATADOS -->
    <?php if (!empty($temas)): ?>
    <div class="acta-card">
        <div class="section-title"><i class="fas fa-comments"></i> TEMAS TRATADOS</div>
        <?php foreach ($temas as $i => $tema): ?>
        <div class="tema-item">
            <span class="tema-num">TEMA <?= $i + 1 ?>:</span>
            <?= esc(is_array($tema) ? ($tema['descripcion'] ?? $tema['tema'] ?? '') : $tema) ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- 5. COMPROMISOS -->
    <?php if (!empty($compromisos)): ?>
    <div class="acta-card">
        <div class="section-title"><i class="fas fa-tasks"></i> COMPROMISOS</div>
        <table class="tbl-mini">
            <thead><tr><th>Actividad</th><th>Cierre</th><th>Responsable</th></tr></thead>
            <tbody>
            <?php foreach ($compromisos as $c): ?>
            <tr>
                <td><?= esc($c['tarea_actividad']) ?></td>
                <td style="white-space:nowrap;"><?= $c['fecha_cierre'] ? date('d/m/Y', strtotime($c['fecha_cierre'])) : '-' ?></td>
                <td><?= esc($c['responsable']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- 6. OBSERVACIONES -->
    <?php if (!empty($acta['observaciones'])): ?>
    <div class="acta-card">
        <div class="section-title"><i class="fas fa-sticky-note"></i> OBSERVACIONES</div>
        <p style="font-size:13px; margin:0; line-height:1.5;"><?= nl2br(esc($acta['observaciones'])) ?></p>
    </div>
    <?php endif; ?>

    <!-- ===== SECCION FIRMA ===== -->
    <div class="firma-section">
        <div class="section-title"><i class="fas fa-signature"></i> FIRMA — <?= strtoupper($label) ?></div>
        <?php if ($nombreFirmante): ?>
            <p style="font-weight:600; font-size:15px; margin-bottom:12px;"><?= esc($nombreFirmante) ?></p>
        <?php endif; ?>

        <div class="aviso-firma mb-3">
            <i class="fas fa-lock me-1"></i>
            Al firmar confirma su participacion en esta visita de seguimiento SST y acepta el tratamiento de sus datos personales conforme a la Ley 1581 de 2012.
        </div>

        <label class="form-label fw-bold mb-2">Dibuje su firma aqui <small class="text-muted fw-normal">(use su dedo)</small></label>
        <canvas id="firmaCanvas" class="firma-canvas" height="220"></canvas>
        <div class="d-flex justify-content-end mt-2 mb-3">
            <button type="button" id="btnLimpiar" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-eraser me-1"></i>Limpiar
            </button>
        </div>

        <button type="button" id="btnFirmar" class="btn btn-firmar">
            <i class="fas fa-signature me-2"></i>Firmar Acta de Visita
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
            if (imgData[i] > 128) pixeles++;
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

            fetch('/acta-visita/procesar-firma-remota', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    document.getElementById('btnFirmar').disabled = true;
                    document.getElementById('btnFirmar').innerHTML = '<i class="fas fa-check-circle me-2"></i>Firma registrada';
                    document.getElementById('btnFirmar').style.background = '#6c757d';
                    Swal.fire({
                        icon: 'success',
                        title: 'Firma registrada!',
                        text: 'Gracias. Su firma ha sido guardada exitosamente en el acta.',
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
