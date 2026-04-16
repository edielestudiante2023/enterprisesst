<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Acuse de recibido — <?= esc($cargo['nombre_cargo'] ?? '') ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f4f6fa; }
.sig-pad { border:2px dashed #aaa; background:#fff; border-radius:6px; touch-action:none; display:block; width:100%; max-width:500px; height:180px; }
.doc { background:#fff; padding:2rem; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,.05); max-width:900px; margin:0 auto; }
h5 { border-bottom:2px solid #1e4c9a; padding-bottom:.3rem; margin-top:1.5rem; color:#1e4c9a; }
.lista-small li { margin-bottom:.2rem; }
</style>
</head>
<body>
<div class="container py-4">
    <div class="doc">
        <?php if ($acuse['estado'] === 'firmado'): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> <strong>Ya firmado</strong> el <?= esc($acuse['fecha_firma']) ?>.
                <a class="btn btn-sm btn-outline-success ms-2" target="_blank" href="<?= base_url("perfil-acuse/{$acuse['token_firma']}/pdf") ?>">Descargar PDF</a>
            </div>
        <?php endif; ?>

        <h3 class="mb-0">PERFIL DEL CARGO</h3>
        <p class="text-muted">Cliente: <strong><?= esc($cliente['nombre_cliente']) ?></strong></p>

        <h5>IDENTIFICACION DEL CARGO</h5>
        <table class="table table-sm table-bordered">
            <tr><th style="width:25%">Nombre del cargo</th><td><?= esc($cargo['nombre_cargo'] ?? '') ?></td></tr>
            <tr><th>Reporta a</th><td><?= esc($perfil['reporta_a'] ?? '') ?></td></tr>
            <tr><th>Colaboradores a cargo</th><td><?= esc($perfil['colaboradores_a_cargo'] ?? '') ?></td></tr>
        </table>

        <h5>OBJETIVO DEL CARGO</h5>
        <p><?= nl2br(esc($perfil['objetivo_cargo'] ?? '')) ?></p>

        <h5>FUNCIONES ESPECIFICAS</h5>
        <ol class="lista-small">
            <?php foreach ((array)($perfil['funciones_especificas'] ?? []) as $f): ?>
                <li><?= esc($f) ?></li>
            <?php endforeach; ?>
        </ol>

        <h5>COMPETENCIAS REQUERIDAS</h5>
        <table class="table table-sm table-bordered">
            <thead class="table-light"><tr><th>Competencia</th><th>Nivel</th></tr></thead>
            <tbody>
                <?php foreach ($competencias as $c): ?>
                    <tr><td><?= esc($c['nombre']) ?></td><td><?= esc($c['nivel_requerido']) ?>/5</td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h5>FUNCIONES — SEGURIDAD Y SALUD EN EL TRABAJO</h5>
        <ol class="lista-small small">
            <?php foreach ($funcionesSST as $f): ?><li><?= esc($f['texto']) ?></li><?php endforeach; ?>
        </ol>

        <h5>FUNCIONES — TALENTO HUMANO</h5>
        <ol class="lista-small small">
            <?php foreach ($funcionesTH as $f): ?><li><?= esc($f['texto']) ?></li><?php endforeach; ?>
        </ol>

        <h5>INDICADORES DEL CARGO</h5>
        <table class="table table-sm table-bordered small">
            <thead class="table-light"><tr><th>Indicador</th><th>Formula</th><th>Periodicidad</th><th>Meta</th></tr></thead>
            <tbody>
                <?php foreach ($indicadores as $i): ?>
                    <tr><td><?= esc($i['nombre_indicador']) ?></td><td><?= esc($i['formula']) ?></td><td><?= esc($i['periodicidad']) ?></td><td><?= esc($i['meta']) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <hr class="my-4">

        <h5>FIRMA DE RECIBIDO</h5>
        <p>
            Yo, <strong><?= esc($acuse['nombre_trabajador']) ?></strong>,
            identificado con cedula <strong><?= esc($acuse['cedula_trabajador']) ?></strong>,
            declaro haber recibido, leido y comprendido las funciones, responsabilidades e indicadores del perfil del cargo
            <strong><?= esc($cargo['nombre_cargo'] ?? '') ?></strong>.
        </p>

        <?php if ($acuse['estado'] !== 'firmado'): ?>
            <div class="mt-3">
                <label class="form-label">Firme en el recuadro</label>
                <canvas id="sig" class="sig-pad"></canvas>
                <div class="mt-2">
                    <button class="btn btn-outline-secondary btn-sm" id="btn-limpiar">Limpiar</button>
                    <button class="btn btn-success" id="btn-firmar">Confirmar firma</button>
                </div>
                <div id="feedback" class="mt-2"></div>
            </div>
        <?php else: ?>
            <div class="mt-3">
                <p class="mb-1"><small class="text-muted">Firma capturada:</small></p>
                <img src="data:image/png;base64,<?= esc($acuse['firma_imagen']) ?>" alt="firma" style="max-width:400px; border:1px solid #ccc; background:#fff;">
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const TOKEN = <?= json_encode($acuse['token_firma']) ?>;
const URL_FIRMAR = '<?= base_url("perfil-acuse/{$acuse['token_firma']}/firmar") ?>';

<?php if ($acuse['estado'] !== 'firmado'): ?>
// Canvas firma
const canvas = document.getElementById('sig');
const ctx = canvas.getContext('2d');
function fit() {
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width;
    canvas.height = rect.height;
    ctx.strokeStyle = '#000'; ctx.lineWidth = 2; ctx.lineCap='round';
}
fit(); window.addEventListener('resize', fit);
let drawing = false;
function pos(e) {
    const rect = canvas.getBoundingClientRect();
    const p = e.touches ? e.touches[0] : e;
    return { x: p.clientX - rect.left, y: p.clientY - rect.top };
}
function start(e) { drawing = true; const p = pos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); e.preventDefault(); }
function move(e)  { if (!drawing) return; const p = pos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); e.preventDefault(); }
function end()    { drawing = false; }
canvas.addEventListener('mousedown', start); canvas.addEventListener('mousemove', move);
canvas.addEventListener('mouseup', end);     canvas.addEventListener('mouseleave', end);
canvas.addEventListener('touchstart', start); canvas.addEventListener('touchmove', move); canvas.addEventListener('touchend', end);

document.getElementById('btn-limpiar').addEventListener('click', () => ctx.clearRect(0,0,canvas.width,canvas.height));

document.getElementById('btn-firmar').addEventListener('click', async () => {
    // detectar canvas vacio
    const blank = document.createElement('canvas');
    blank.width = canvas.width; blank.height = canvas.height;
    if (canvas.toDataURL() === blank.toDataURL()) { alert('Por favor firme antes de confirmar.'); return; }

    const fb = document.getElementById('feedback');
    fb.innerHTML = '<div class="alert alert-info py-2 mb-0">Enviando firma...</div>';
    const r = await fetch(URL_FIRMAR, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ firma_base64: canvas.toDataURL('image/png') })
    });
    const j = await r.json();
    if (j.ok) {
        fb.innerHTML = '<div class="alert alert-success py-2 mb-0">Firma registrada. Recargando...</div>';
        setTimeout(() => location.reload(), 1500);
    } else {
        fb.innerHTML = '<div class="alert alert-danger py-2 mb-0">Error: ' + (j.error || 'desconocido') + '</div>';
    }
});
<?php endif; ?>
</script>
</body>
</html>
