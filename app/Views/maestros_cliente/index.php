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
.nav-tabs .nav-link{color:var(--primary-dark);font-weight:600;}
.nav-tabs .nav-link.active{background:var(--gold-primary);color:#fff;border-color:var(--gold-primary);}
.card-maestro{background:#fff;border:none;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.08);}
.btn-gold{background:linear-gradient(135deg,var(--gold-primary),var(--gold-secondary));color:#fff;border:none;font-weight:600;}
.btn-gold:hover{filter:brightness(1.1);color:#fff;}
table.maestro-table{font-size:.92rem;}
table.maestro-table thead{background:var(--primary-dark);color:#fff;}
.badge-tipo-estrategico{background:#2563eb;}
.badge-tipo-misional{background:#059669;}
.badge-tipo-apoyo{background:#7c3aed;}
.badge-tipo-evaluacion{background:#d97706;}
.inactivo{opacity:.45;}
.empty-hint{color:#888;font-style:italic;padding:25px;text-align:center;}
.guia-gtc45{background:#f0f7ff;border:1px solid #bfdbfe;border-left:4px solid #3b82f6;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:.82rem;color:#1e3a5f;}
.guia-gtc45 .guia-titulo{font-weight:700;margin-bottom:4px;color:#1e40af;}
.guia-gtc45 .guia-titulo i{margin-right:5px;}
.guia-gtc45 ul{margin:4px 0 0;padding-left:18px;}
.guia-gtc45 li{margin-bottom:2px;}
.guia-gtc45 .guia-ejemplo{color:#6b7280;font-style:italic;}
</style>
</head>
<body>
<div class="container-fluid py-4">
  <div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h1><i class="fa-solid fa-database me-2"></i><?= esc($titulo) ?></h1>
      <div class="subtitulo"><?= esc($cliente['nombre_cliente']) ?> (ID <?= (int)$cliente['id_cliente'] ?>)</div>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= base_url('ipevr/tablas-gtc45') ?>" target="_blank" class="btn btn-info btn-sm">
        <i class="fa-solid fa-table-list me-1"></i> Tablas GTC 45
      </a>
      <a href="https://posipedia.com.co/wp-content/uploads/2021/04/15-MARZO-.-MATERIAL-DE-APOYO-PREVENCIO%CC%81N-DE-PELIGROS-EN-EL-ADMINISTRACIO%CC%81N-PUBLICA-GENERALIDADES.pdf" target="_blank" class="btn btn-outline-light btn-sm">
        <i class="fa-solid fa-book me-1"></i> Norma GTC 45
      </a>
      <a href="<?= base_url('ipevr/cliente/' . $cliente['id_cliente']) ?>" class="btn btn-light btn-sm">
        <i class="fa-solid fa-arrow-right-long me-1"></i> Ir a Matriz IPEVR
      </a>
    </div>
  </div>

  <ul class="nav nav-tabs mb-3" id="tabsMaestros">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-procesos"><i class="fa-solid fa-diagram-project me-1"></i>Procesos (<?= count($procesos) ?>)</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-cargos"><i class="fa-solid fa-user-tie me-1"></i>Cargos (<?= count($cargos) ?>)</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tareas"><i class="fa-solid fa-list-check me-1"></i>Tareas (<?= count($tareas) ?>)</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-zonas"><i class="fa-solid fa-location-dot me-1"></i>Zonas (<?= count($zonas) ?>)</button></li>
  </ul>

  <div class="tab-content">
    <!-- PROCESOS -->
    <div class="tab-pane fade show active" id="tab-procesos">
      <div class="card card-maestro p-3">
        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
          <h5 class="m-0"><i class="fa-solid fa-diagram-project me-2"></i>Procesos</h5>
          <div class="d-flex gap-2">
            <a href="<?= base_url('maestros-cliente/plantilla/proceso') ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-download me-1"></i>Plantilla CSV</a>
            <button class="btn btn-outline-success btn-sm" onclick="abrirCargaCsv('proceso')"><i class="fa-solid fa-file-csv me-1"></i>Cargar CSV</button>
            <button class="btn btn-gold btn-sm" onclick="abrirProceso()"><i class="fa-solid fa-plus me-1"></i>Nuevo</button>
          </div>
        </div>
        <div class="guia-gtc45">
          <div class="guia-titulo"><i class="fa-solid fa-lightbulb"></i>GTC 45 — ¿Qué son los procesos?</div>
          <ul>
            <li>Según la GTC 45, debe identificar <b>todos los procesos</b> de la organización para luego asociar peligros a cada uno.</li>
            <li><b>Estratégico:</b> planeación, dirección, gestión gerencial. <span class="guia-ejemplo">Ej: Direccionamiento estratégico.</span></li>
            <li><b>Misional:</b> el "core" del negocio, lo que genera valor. <span class="guia-ejemplo">Ej: Producción, Comercialización, Importaciones.</span></li>
            <li><b>Apoyo:</b> soportan los procesos misionales. <span class="guia-ejemplo">Ej: Talento Humano, Contabilidad, Tecnología, Logística.</span></li>
            <li><b>Evaluación:</b> miden y controlan. <span class="guia-ejemplo">Ej: Auditoría interna, Control de calidad.</span></li>
          </ul>
          <small>Piense: ¿qué hace la empresa? (misional) · ¿cómo se planifica? (estratégico) · ¿qué la soporta? (apoyo) · ¿cómo se evalúa? (evaluación).</small>
        </div>
        <table class="table table-hover maestro-table">
          <thead><tr><th style="width:50px">#</th><th>Nombre</th><th>Tipo</th><th>Descripción</th><th style="width:130px" class="text-end">Acciones</th></tr></thead>
          <tbody id="tb-procesos">
          <?php foreach ($procesos as $p): ?>
            <tr class="<?= $p['activo']?'':'inactivo' ?>">
              <td><?= $p['id'] ?></td>
              <td><strong><?= esc($p['nombre_proceso']) ?></strong></td>
              <td><?= $p['tipo'] ? '<span class="badge badge-tipo-'.esc($p['tipo']).'">'.esc(ucfirst($p['tipo'])).'</span>' : '<span class="text-muted">—</span>' ?></td>
              <td><?= esc($p['descripcion'] ?? '') ?></td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary" onclick='abrirProceso(<?= json_encode($p) ?>)'><i class="fa-solid fa-pen"></i></button>
                <?php if ($p['activo']): ?>
                  <button class="btn btn-sm btn-outline-danger" onclick="eliminar('proceso',<?= $p['id'] ?>)"><i class="fa-solid fa-trash"></i></button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; if (empty($procesos)): ?>
            <tr><td colspan="5" class="empty-hint">No hay procesos registrados. Haz clic en "Nuevo proceso".</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- CARGOS -->
    <div class="tab-pane fade" id="tab-cargos">
      <div class="card card-maestro p-3">
        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
          <h5 class="m-0"><i class="fa-solid fa-user-tie me-2"></i>Cargos</h5>
          <div class="d-flex gap-2">
            <a href="<?= base_url('maestros-cliente/plantilla/cargo') ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-download me-1"></i>Plantilla CSV</a>
            <button class="btn btn-outline-success btn-sm" onclick="abrirCargaCsv('cargo')"><i class="fa-solid fa-file-csv me-1"></i>Cargar CSV</button>
            <button class="btn btn-gold btn-sm" onclick="abrirCargo()"><i class="fa-solid fa-plus me-1"></i>Nuevo</button>
          </div>
        </div>
        <?php $procById = array_column($procesos, 'nombre_proceso', 'id'); ?>
        <div class="guia-gtc45">
          <div class="guia-titulo"><i class="fa-solid fa-lightbulb"></i>GTC 45 — ¿Qué son los cargos?</div>
          <ul>
            <li>Registre <b>todos los cargos</b> de la organización. Cada cargo se asocia a un proceso.</li>
            <li>La GTC 45 necesita saber <b>quiénes</b> están expuestos a cada peligro y <b>cuántos</b> trabajadores ocupan cada cargo.</li>
            <li>Incluya desde la gerencia hasta operarios, servicios generales y contratistas permanentes.</li>
            <li><span class="guia-ejemplo">Ej: Gerente General (1), Auxiliar de bodega (4), Conductor repartidor (3), Asesor comercial (6).</span></li>
          </ul>
        </div>
        <table class="table table-hover maestro-table">
          <thead><tr><th style="width:50px">#</th><th>Nombre</th><th>Proceso</th><th>N° ocupantes</th><th>Descripción</th><th style="width:130px" class="text-end">Acciones</th></tr></thead>
          <tbody id="tb-cargos">
          <?php foreach ($cargos as $c): ?>
            <tr class="<?= $c['activo']?'':'inactivo' ?>">
              <td><?= $c['id'] ?></td>
              <td><strong><?= esc($c['nombre_cargo']) ?></strong></td>
              <td><?= $c['id_proceso'] && isset($procById[$c['id_proceso']]) ? esc($procById[$c['id_proceso']]) : '<span class="text-muted">—</span>' ?></td>
              <td><?= (int)$c['num_ocupantes'] ?></td>
              <td><?= esc($c['descripcion'] ?? '') ?></td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary" onclick='abrirCargo(<?= json_encode($c) ?>)'><i class="fa-solid fa-pen"></i></button>
                <?php if ($c['activo']): ?>
                  <button class="btn btn-sm btn-outline-danger" onclick="eliminar('cargo',<?= $c['id'] ?>)"><i class="fa-solid fa-trash"></i></button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; if (empty($cargos)): ?>
            <tr><td colspan="6" class="empty-hint">No hay cargos registrados.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAREAS -->
    <div class="tab-pane fade" id="tab-tareas">
      <div class="card card-maestro p-3">
        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
          <h5 class="m-0"><i class="fa-solid fa-list-check me-2"></i>Tareas</h5>
          <div class="d-flex gap-2">
            <a href="<?= base_url('maestros-cliente/plantilla/tarea') ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-download me-1"></i>Plantilla CSV</a>
            <button class="btn btn-outline-success btn-sm" onclick="abrirCargaCsv('tarea')"><i class="fa-solid fa-file-csv me-1"></i>Cargar CSV</button>
            <button class="btn btn-gold btn-sm" onclick="abrirTarea()"><i class="fa-solid fa-plus me-1"></i>Nueva</button>
          </div>
        </div>
        <div class="guia-gtc45">
          <div class="guia-titulo"><i class="fa-solid fa-lightbulb"></i>GTC 45 — ¿Qué son las tareas?</div>
          <ul>
            <li>Una <b>tarea</b> es la acción específica que realiza el trabajador dentro de un proceso.</li>
            <li><b>Rutinaria (Sí):</b> se realiza de forma habitual, diaria o frecuente. <span class="guia-ejemplo">Ej: Atender caja, digitar informes, operar montacargas.</span></li>
            <li><b>No rutinaria (No):</b> se realiza de forma esporádica o eventual. <span class="guia-ejemplo">Ej: Inventario semestral, mantenimiento correctivo, atender emergencias.</span></li>
            <li>Describa <b>qué hace</b> el trabajador y <b>con qué</b> (herramientas, equipos, sustancias, vehículos).</li>
          </ul>
          <small>La clasificación rutinaria/no rutinaria afecta la evaluación del Nivel de Exposición (NE) en la matriz IPEVR.</small>
        </div>
        <table class="table table-hover maestro-table">
          <thead><tr><th style="width:50px">#</th><th>Nombre</th><th>Proceso</th><th>Rutinaria</th><th>Descripción</th><th style="width:130px" class="text-end">Acciones</th></tr></thead>
          <tbody id="tb-tareas">
          <?php foreach ($tareas as $t): ?>
            <tr class="<?= $t['activo']?'':'inactivo' ?>">
              <td><?= $t['id'] ?></td>
              <td><strong><?= esc($t['nombre_tarea']) ?></strong></td>
              <td><?= $t['id_proceso'] && isset($procById[$t['id_proceso']]) ? esc($procById[$t['id_proceso']]) : '<span class="text-muted">—</span>' ?></td>
              <td><?= $t['rutinaria'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
              <td><?= esc($t['descripcion'] ?? '') ?></td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary" onclick='abrirTarea(<?= json_encode($t) ?>)'><i class="fa-solid fa-pen"></i></button>
                <?php if ($t['activo']): ?>
                  <button class="btn btn-sm btn-outline-danger" onclick="eliminar('tarea',<?= $t['id'] ?>)"><i class="fa-solid fa-trash"></i></button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; if (empty($tareas)): ?>
            <tr><td colspan="6" class="empty-hint">No hay tareas registradas.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ZONAS -->
    <div class="tab-pane fade" id="tab-zonas">
      <div class="card card-maestro p-3">
        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
          <h5 class="m-0"><i class="fa-solid fa-location-dot me-2"></i>Zonas</h5>
          <div class="d-flex gap-2">
            <a href="<?= base_url('maestros-cliente/plantilla/zona') ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-download me-1"></i>Plantilla CSV</a>
            <button class="btn btn-outline-success btn-sm" onclick="abrirCargaCsv('zona')"><i class="fa-solid fa-file-csv me-1"></i>Cargar CSV</button>
            <button class="btn btn-gold btn-sm" onclick="abrirZona()"><i class="fa-solid fa-plus me-1"></i>Nueva</button>
          </div>
        </div>
        <?php $sedeById = array_column($sedes, 'nombre_sede', 'id_sede'); ?>
        <div class="guia-gtc45">
          <div class="guia-titulo"><i class="fa-solid fa-lightbulb"></i>GTC 45 — ¿Qué son las zonas?</div>
          <ul>
            <li>Según la GTC 45, debe identificar <b>todos los lugares</b> donde se realizan actividades laborales.</li>
            <li>Incluya: oficinas, bodegas, plantas de producción, talleres, vehículos, cuartos técnicos, zonas comunes.</li>
            <li>Piense en <b>dónde</b> trabajan las personas y qué condiciones ambientales tiene ese lugar.</li>
            <li><span class="guia-ejemplo">Ej: Bodega principal (estanterías, montacargas), Oficina administrativa (puestos con pantalla), Zona de cargue (muelle, acceso vehicular).</span></li>
          </ul>
          <small>Si el cliente tiene múltiples sedes, asocie cada zona a su sede. Si no hay sedes registradas, déjelo sin sede.</small>
        </div>
        <table class="table table-hover maestro-table">
          <thead><tr><th style="width:50px">#</th><th>Nombre</th><th>Sede</th><th>Descripción</th><th style="width:130px" class="text-end">Acciones</th></tr></thead>
          <tbody id="tb-zonas">
          <?php foreach ($zonas as $z): ?>
            <tr class="<?= $z['activo']?'':'inactivo' ?>">
              <td><?= $z['id'] ?></td>
              <td><strong><?= esc($z['nombre_zona']) ?></strong></td>
              <td><?= $z['id_sede'] && isset($sedeById[$z['id_sede']]) ? esc($sedeById[$z['id_sede']]) : '<span class="text-muted">—</span>' ?></td>
              <td><?= esc($z['descripcion'] ?? '') ?></td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary" onclick='abrirZona(<?= json_encode($z) ?>)'><i class="fa-solid fa-pen"></i></button>
                <?php if ($z['activo']): ?>
                  <button class="btn btn-sm btn-outline-danger" onclick="eliminar('zona',<?= $z['id'] ?>)"><i class="fa-solid fa-trash"></i></button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; if (empty($zonas)): ?>
            <tr><td colspan="5" class="empty-hint">No hay zonas registradas.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- MODAL GENERICO -->
<div class="modal fade" id="modalMaestro" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" id="formMaestro" onsubmit="guardar(event)">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="modalTitulo">Maestro</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-gold"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL CARGA CSV -->
<div class="modal fade" id="modalCsv" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" id="formCsv" onsubmit="enviarCsv(event)">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="fa-solid fa-file-csv me-2"></i>Cargar CSV — <span id="csvTipoLabel"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="tipo" id="csvTipo">
        <input type="hidden" name="id_cliente" value="<?= (int)$cliente['id_cliente'] ?>">
        <div class="alert alert-info small">
          <i class="fa-solid fa-lightbulb me-1"></i>
          <strong>Tip:</strong> Descargue primero la plantilla CSV para ver el formato esperado y las instrucciones de la GTC 45.
        </div>
        <div class="mb-3">
          <label class="form-label">Archivo CSV</label>
          <input type="file" class="form-control" name="archivo_csv" accept=".csv" required>
        </div>
        <div id="csvPreview" style="display:none">
          <hr>
          <div id="csvPreviewContent"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-success"><i class="fa-solid fa-upload me-1"></i>Importar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const ID_CLIENTE = <?= (int)$cliente['id_cliente'] ?>;
const BASE = '<?= base_url() ?>';
const PROCESOS_OPCIONES = <?= json_encode(array_map(fn($p)=>['id'=>$p['id'],'nombre'=>$p['nombre_proceso']], array_filter($procesos, fn($p)=>$p['activo']))) ?>;
const SEDES_OPCIONES = <?= json_encode(array_map(fn($s)=>['id'=>$s['id_sede'],'nombre'=>$s['nombre_sede']], $sedes)) ?>;

let tipoActual = '';

function procesoSelect(selected){
  let html = '<option value="">(Sin proceso)</option>';
  PROCESOS_OPCIONES.forEach(p => {
    html += `<option value="${p.id}" ${String(selected)===String(p.id)?'selected':''}>${p.nombre}</option>`;
  });
  return html;
}
function sedeSelect(selected){
  let html = '<option value="">(Sin sede)</option>';
  SEDES_OPCIONES.forEach(s => {
    html += `<option value="${s.id}" ${String(selected)===String(s.id)?'selected':''}>${s.nombre}</option>`;
  });
  return html;
}

function abrirProceso(p={}){
  tipoActual='proceso';
  document.getElementById('modalTitulo').textContent = p.id ? 'Editar proceso' : 'Nuevo proceso';
  document.getElementById('modalBody').innerHTML = `
    <input type="hidden" name="id_cliente" value="${ID_CLIENTE}">
    <input type="hidden" name="id" value="${p.id||''}">
    <div class="mb-3"><label class="form-label">Nombre del proceso *</label>
      <input class="form-control" name="nombre_proceso" value="${(p.nombre_proceso||'').replace(/"/g,'&quot;')}" required></div>
    <div class="mb-3"><label class="form-label">Tipo</label>
      <select class="form-select" name="tipo">
        <option value="">(Sin tipo)</option>
        ${['estrategico','misional','apoyo','evaluacion'].map(t=>`<option value="${t}" ${p.tipo===t?'selected':''}>${t}</option>`).join('')}
      </select></div>
    <div class="mb-3"><label class="form-label">Descripción</label>
      <textarea class="form-control" name="descripcion" rows="2">${p.descripcion||''}</textarea></div>`;
  new bootstrap.Modal(document.getElementById('modalMaestro')).show();
}

function abrirCargo(c={}){
  tipoActual='cargo';
  document.getElementById('modalTitulo').textContent = c.id ? 'Editar cargo' : 'Nuevo cargo';
  document.getElementById('modalBody').innerHTML = `
    <input type="hidden" name="id_cliente" value="${ID_CLIENTE}">
    <input type="hidden" name="id" value="${c.id||''}">
    <div class="mb-3"><label class="form-label">Nombre del cargo *</label>
      <input class="form-control" name="nombre_cargo" value="${(c.nombre_cargo||'').replace(/"/g,'&quot;')}" required></div>
    <div class="mb-3"><label class="form-label">Proceso</label>
      <select class="form-select" name="id_proceso">${procesoSelect(c.id_proceso)}</select></div>
    <div class="mb-3"><label class="form-label">N° de ocupantes</label>
      <input type="number" class="form-control" name="num_ocupantes" value="${c.num_ocupantes||0}" min="0"></div>
    <div class="mb-3"><label class="form-label">Descripción</label>
      <textarea class="form-control" name="descripcion" rows="2">${c.descripcion||''}</textarea></div>`;
  new bootstrap.Modal(document.getElementById('modalMaestro')).show();
}

function abrirTarea(t={}){
  tipoActual='tarea';
  document.getElementById('modalTitulo').textContent = t.id ? 'Editar tarea' : 'Nueva tarea';
  document.getElementById('modalBody').innerHTML = `
    <input type="hidden" name="id_cliente" value="${ID_CLIENTE}">
    <input type="hidden" name="id" value="${t.id||''}">
    <div class="mb-3"><label class="form-label">Nombre de la tarea *</label>
      <input class="form-control" name="nombre_tarea" value="${(t.nombre_tarea||'').replace(/"/g,'&quot;')}" required></div>
    <div class="mb-3"><label class="form-label">Proceso</label>
      <select class="form-select" name="id_proceso">${procesoSelect(t.id_proceso)}</select></div>
    <div class="mb-3"><label class="form-label">¿Rutinaria?</label>
      <select class="form-select" name="rutinaria">
        <option value="1" ${t.rutinaria!=0?'selected':''}>Sí</option>
        <option value="0" ${t.rutinaria==0?'selected':''}>No</option>
      </select></div>
    <div class="mb-3"><label class="form-label">Descripción</label>
      <textarea class="form-control" name="descripcion" rows="2">${t.descripcion||''}</textarea></div>`;
  new bootstrap.Modal(document.getElementById('modalMaestro')).show();
}

function abrirZona(z={}){
  tipoActual='zona';
  document.getElementById('modalTitulo').textContent = z.id ? 'Editar zona' : 'Nueva zona';
  document.getElementById('modalBody').innerHTML = `
    <input type="hidden" name="id_cliente" value="${ID_CLIENTE}">
    <input type="hidden" name="id" value="${z.id||''}">
    <div class="mb-3"><label class="form-label">Nombre de la zona *</label>
      <input class="form-control" name="nombre_zona" value="${(z.nombre_zona||'').replace(/"/g,'&quot;')}" required></div>
    <div class="mb-3"><label class="form-label">Sede</label>
      <select class="form-select" name="id_sede">${sedeSelect(z.id_sede)}</select></div>
    <div class="mb-3"><label class="form-label">Descripción</label>
      <textarea class="form-control" name="descripcion" rows="2">${z.descripcion||''}</textarea></div>`;
  new bootstrap.Modal(document.getElementById('modalMaestro')).show();
}

async function guardar(ev){
  ev.preventDefault();
  const fd = new FormData(document.getElementById('formMaestro'));
  const url = `${BASE}maestros-cliente/${tipoActual}/upsert`;
  const r = await fetch(url, {method:'POST', body:fd});
  const j = await r.json();
  if (j.ok) {
    bootstrap.Modal.getInstance(document.getElementById('modalMaestro')).hide();
    location.reload();
  } else {
    alert('Error: ' + (j.error || 'no se pudo guardar'));
  }
}

async function eliminar(tipo, id){
  if (!confirm('¿Marcar como inactivo?')) return;
  const r = await fetch(`${BASE}maestros-cliente/${tipo}/eliminar/${id}`, {method:'POST'});
  const j = await r.json();
  if (j.ok) location.reload();
}

// ---------- CARGA CSV ----------
const TIPO_LABELS = { proceso: 'Procesos', cargo: 'Cargos', tarea: 'Tareas', zona: 'Zonas' };

function abrirCargaCsv(tipo) {
  document.getElementById('csvTipo').value = tipo;
  document.getElementById('csvTipoLabel').textContent = TIPO_LABELS[tipo] || tipo;
  document.getElementById('csvPreview').style.display = 'none';
  document.getElementById('formCsv').reset();
  document.getElementById('csvTipo').value = tipo;
  new bootstrap.Modal(document.getElementById('modalCsv')).show();
}

async function enviarCsv(ev) {
  ev.preventDefault();
  const fd = new FormData(document.getElementById('formCsv'));
  const tipo = fd.get('tipo');
  const btn = ev.target.querySelector('[type="submit"]');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Importando...';

  try {
    const r = await fetch(`${BASE}maestros-cliente/cargar-csv`, { method: 'POST', body: fd });
    const j = await r.json();
    bootstrap.Modal.getInstance(document.getElementById('modalCsv')).hide();

    if (j.ok) {
      let msg = `Se importaron <b>${j.insertados}</b> de ${j.total_leidas} registros de <b>${TIPO_LABELS[tipo]}</b>.`;
      if (j.errores && j.errores.length) {
        msg += '<br><br><b>Errores:</b><br><small>' + j.errores.slice(0, 10).join('<br>') + '</small>';
      }
      Swal.fire({
        icon: j.insertados > 0 ? 'success' : 'warning',
        title: j.insertados > 0 ? 'Importación completada' : 'Sin registros importados',
        html: msg,
        confirmButtonColor: '#bd9751',
      }).then(() => location.reload());
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: j.error || 'No se pudo procesar el CSV' });
    }
  } catch (e) {
    Swal.fire({ icon: 'error', title: 'Error de red', text: e.message });
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-upload me-1"></i>Importar';
  }
}
</script>
</body>
</html>
