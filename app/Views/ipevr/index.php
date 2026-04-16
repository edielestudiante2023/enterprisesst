<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($titulo) ?> - <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--primary-dark:#1c2437;--secondary-dark:#2c3e50;--gold-primary:#bd9751;--gold-secondary:#d4af37;--gradient-bg:linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%);}
body{background:var(--gradient-bg);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;}
.page-header{background:linear-gradient(135deg,var(--primary-dark),var(--secondary-dark),var(--gold-primary));color:#fff;padding:25px 30px;border-radius:15px;margin-bottom:20px;box-shadow:0 10px 30px rgba(0,0,0,.2);}
.page-header h1{font-size:1.6rem;margin:0 0 4px;font-weight:700;}
.page-header .subtitulo{opacity:.85;font-size:.95rem;}
.card-main{background:#fff;border:none;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.08);}
.btn-gold{background:linear-gradient(135deg,var(--gold-primary),var(--gold-secondary));color:#fff;border:none;font-weight:600;}
.btn-gold:hover{filter:brightness(1.1);color:#fff;}
.matriz-table{font-size:.92rem;}
.matriz-table thead{background:var(--primary-dark);color:#fff;}
.estado-badge{font-size:.75rem;padding:6px 12px;border-radius:20px;font-weight:600;text-transform:uppercase;}
.estado-borrador{background:#e5e7eb;color:#374151;}
.estado-revision{background:#fef3c7;color:#92400e;}
.estado-aprobada{background:#dbeafe;color:#1e40af;}
.estado-vigente{background:#d1fae5;color:#065f46;}
.estado-historica{background:#f3f4f6;color:#6b7280;}
.empty-state{text-align:center;padding:60px 20px;color:#888;}
.empty-state .big-icon{font-size:5rem;color:#d1d5db;margin-bottom:20px;}
.version-badge{background:var(--gold-primary);color:#fff;padding:4px 10px;border-radius:6px;font-weight:700;font-size:.85rem;}
</style>
</head>
<body>
<div class="container-fluid py-4">
  <div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h1><i class="fa-solid fa-shield-halved me-2"></i><?= esc($titulo) ?></h1>
      <div class="subtitulo"><?= esc($cliente['nombre_cliente']) ?> (ID <?= (int)$cliente['id_cliente'] ?>)</div>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= base_url('ipevr/tablas-gtc45') ?>" target="_blank" class="btn btn-info">
        <i class="fa-solid fa-table-list me-1"></i> Tablas GTC 45
      </a>
      <a href="<?= base_url('maestros-cliente/' . $cliente['id_cliente']) ?>" class="btn btn-light">
        <i class="fa-solid fa-database me-1"></i> Maestros del cliente
      </a>
      <button class="btn btn-gold" onclick="abrirNueva()">
        <i class="fa-solid fa-plus me-1"></i> Nueva matriz IPEVR
      </button>
    </div>
  </div>

  <div class="card card-main p-3">
    <h5 class="mb-3"><i class="fa-solid fa-list me-2"></i>Matrices registradas (<?= count($matrices) ?>)</h5>

    <?php if (empty($matrices)): ?>
      <div class="empty-state">
        <div class="big-icon"><i class="fa-solid fa-folder-open"></i></div>
        <h5>No hay matrices IPEVR para este cliente</h5>
        <p>Haz clic en "Nueva matriz IPEVR" para crear la primera versión.</p>
        <button class="btn btn-gold" onclick="abrirNueva()"><i class="fa-solid fa-plus me-1"></i>Nueva matriz IPEVR</button>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover matriz-table">
          <thead>
            <tr>
              <th style="width:90px">Versión</th>
              <th>Nombre</th>
              <th style="width:140px">Estado</th>
              <th style="width:120px">Fecha creación</th>
              <th style="width:100px" class="text-center">Filas</th>
              <th style="width:140px">Elaborado por</th>
              <th style="width:200px" class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($matrices as $m): ?>
            <tr>
              <td><span class="version-badge">v<?= esc($m['version']) ?></span></td>
              <td><strong><?= esc($m['nombre']) ?></strong></td>
              <td><span class="estado-badge estado-<?= esc($m['estado']) ?>"><?= esc($m['estado']) ?></span></td>
              <td><?= esc($m['fecha_creacion'] ?? $m['created_at'] ?? '—') ?></td>
              <td class="text-center"><strong><?= (int)$m['total_filas'] ?></strong></td>
              <td><?= esc($m['elaborado_por'] ?? '—') ?></td>
              <td class="text-end">
                <a href="<?= base_url('ipevr/matriz/' . $m['id'] . '/editar') ?>" class="btn btn-sm btn-outline-primary" title="Editar en PC">
                  <i class="fa-solid fa-pen-to-square"></i>
                </a>
                <a href="<?= base_url('ipevr/matriz/' . $m['id'] . '/pwa') ?>" class="btn btn-sm btn-outline-secondary" title="Abrir PWA">
                  <i class="fa-solid fa-mobile-screen"></i>
                </a>
                <a href="<?= base_url('ipevr/matriz/' . $m['id'] . '/exportar/xlsx') ?>" class="btn btn-sm btn-outline-success" title="Exportar Excel">
                  <i class="fa-solid fa-file-excel"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL NUEVA MATRIZ -->
<div class="modal fade" id="modalNueva" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" onsubmit="crearMatriz(event)">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="fa-solid fa-plus me-2"></i>Nueva matriz IPEVR</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_cliente" value="<?= (int)$cliente['id_cliente'] ?>">
        <div class="mb-3">
          <label class="form-label">Nombre de la matriz</label>
          <input class="form-control" name="nombre" value="Matriz IPEVR GTC 45" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Versión inicial</label>
          <input class="form-control" name="version" value="001" pattern="\d{3}" maxlength="3" required>
          <div class="form-text">Formato 3 dígitos (001, 002, …).</div>
        </div>
        <div class="alert alert-info small mb-0">
          <i class="fa-solid fa-info-circle me-1"></i>
          Al crear la matriz podrás diligenciarla manualmente o sugerir filas iniciales con IA a partir del contexto del cliente.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-gold"><i class="fa-solid fa-check me-1"></i>Crear y continuar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const BASE = '<?= base_url() ?>';

function abrirNueva(){
  new bootstrap.Modal(document.getElementById('modalNueva')).show();
}

async function crearMatriz(ev){
  ev.preventDefault();
  const fd = new FormData(ev.target);
  const r = await fetch(`${BASE}ipevr/matriz/crear`, {method:'POST', body:fd});
  const j = await r.json();
  if (j.ok) {
    window.location.href = j.redirect;
  } else {
    alert('Error: ' + (j.error || 'no se pudo crear la matriz'));
  }
}
</script>
</body>
</html>
