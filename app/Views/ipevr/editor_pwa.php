<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<title><?= esc($titulo) ?> - <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
<link rel="manifest" href="<?= base_url('manifest_ipevr.json') ?>">
<meta name="theme-color" content="#bd9751">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="IPEVR">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body{background:#f5f7fa;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;padding-bottom:90px;}
.header-pwa{background:linear-gradient(135deg,#1c2437,#bd9751);color:#fff;padding:15px;position:sticky;top:0;z-index:100;box-shadow:0 3px 10px rgba(0,0,0,.2);}
.header-pwa h1{font-size:1.1rem;margin:0;}
.header-pwa .sub{font-size:.75rem;opacity:.85;}
.card-fila{background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.08);margin:10px;padding:12px;border-left:4px solid #bd9751;}
.card-fila h6{margin:0 0 4px;font-size:.95rem;font-weight:700;color:#1c2437;}
.card-fila .meta{font-size:.75rem;color:#666;}
.badge-nr-pwa{font-weight:700;padding:4px 10px;border-radius:6px;color:#fff;font-size:.75rem;}
.fab{position:fixed;bottom:20px;right:20px;width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#bd9751,#d4af37);color:#fff;border:none;font-size:1.8rem;box-shadow:0 6px 20px rgba(0,0,0,.3);z-index:200;}
.fab:active{transform:scale(.95);}
.sync-bar{position:fixed;bottom:0;left:0;right:0;background:#1c2437;color:#fff;padding:8px;font-size:.8rem;text-align:center;z-index:150;}
.sync-bar.offline{background:#dc2626;}
.wizard-step{display:none;}
.wizard-step.active{display:block;}
.wizard-progress{background:#e5e7eb;height:6px;border-radius:3px;margin:10px 0;overflow:hidden;}
.wizard-progress-bar{background:#bd9751;height:100%;transition:width .3s;}
.wizard-nav{display:flex;gap:8px;justify-content:space-between;margin-top:15px;}
.empty-pwa{text-align:center;padding:50px 20px;color:#888;}
</style>
</head>
<body>
<div class="header-pwa d-flex justify-content-between align-items-center">
  <div>
    <h1><i class="fa-solid fa-shield-halved me-1"></i><?= esc($matriz['nombre']) ?></h1>
    <div class="sub"><?= esc($cliente['nombre_cliente']) ?> · v<?= esc($matriz['version']) ?> · <?= esc($matriz['estado']) ?></div>
  </div>
  <a href="<?= base_url('ipevr/matriz/' . $matriz['id'] . '/editar') ?>" class="btn btn-sm btn-light">PC</a>
</div>

<div id="listaFilas">
  <?php if (empty($filas)): ?>
    <div class="empty-pwa">
      <i class="fa-solid fa-table-list" style="font-size:4rem;color:#d1d5db;"></i>
      <p class="mt-3">No hay filas aún.<br>Toca <strong>+</strong> para agregar la primera.</p>
    </div>
  <?php else: ?>
    <?php foreach ($filas as $f): ?>
      <div class="card-fila" onclick="editarFila(<?= (int)$f['id'] ?>)">
        <h6><?= esc($f['proceso_texto'] ?: '(sin proceso)') ?> — <?= esc($f['tarea_texto'] ?: '(sin tarea)') ?></h6>
        <div class="meta"><?= esc($f['descripcion_peligro'] ?: '(sin peligro)') ?></div>
        <div class="mt-2">
          <?php if ($f['nr']): ?>
            <?php
              $nr = array_column($catalogo['nr'], null, 'id')[$f['id_nivel_riesgo']] ?? null;
              if ($nr):
            ?>
              <span class="badge-nr-pwa" style="background:<?= esc($nr['color_hex']) ?>">Nivel <?= esc($nr['nombre']) ?> · NR=<?= (int)$f['nr'] ?></span>
            <?php endif; ?>
          <?php else: ?>
            <span class="badge bg-secondary">Sin evaluar</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<button class="fab" onclick="abrirNuevaFila()" aria-label="Agregar fila">+</button>
<div class="sync-bar" id="syncBar">
  <span id="syncStatus"><i class="fa-solid fa-wifi me-1"></i>Online</span>
  · En cola: <span id="colaSize">0</span>
</div>

<!-- MODAL WIZARD -->
<div class="modal fade" id="modalWizard" tabindex="-1">
  <div class="modal-dialog modal-fullscreen">
    <form class="modal-content" id="formWizard" onsubmit="guardarWizard(event)">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="wizardTitulo">Nueva fila</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="w_id">
        <input type="hidden" name="id_matriz" value="<?= (int)$matriz['id'] ?>">

        <div class="wizard-progress"><div class="wizard-progress-bar" id="wizProgBar" style="width:16.66%"></div></div>
        <h6 id="wizStepTitle">Paso 1 de 6 — Proceso / Tarea</h6>

        <!-- Paso 1 -->
        <div class="wizard-step active" data-step="1">
          <div class="mb-2"><label class="form-label">Proceso</label><input class="form-control" name="proceso_texto" id="w_proceso_texto"></div>
          <div class="mb-2"><label class="form-label">Zona</label><input class="form-control" name="zona_texto" id="w_zona_texto"></div>
          <div class="mb-2"><label class="form-label">Actividad</label><input class="form-control" name="actividad" id="w_actividad"></div>
          <div class="mb-2"><label class="form-label">Tarea</label><input class="form-control" name="tarea_texto" id="w_tarea_texto"></div>
          <div class="mb-2"><label class="form-label">Rutinaria</label>
            <select class="form-select" name="rutinaria" id="w_rutinaria"><option value="1">Sí</option><option value="0">No</option></select>
          </div>
          <div class="mb-2"><label class="form-label">Cargos (coma)</label><input class="form-control" name="cargos_expuestos" id="w_cargos_expuestos"></div>
          <div class="mb-2"><label class="form-label">N° expuestos</label><input type="number" min="0" class="form-control" name="num_expuestos" id="w_num_expuestos" value="0"></div>
        </div>

        <!-- Paso 2 -->
        <div class="wizard-step" data-step="2">
          <div class="mb-2"><label class="form-label">Peligro (catálogo)</label>
            <select class="form-select" name="id_peligro_catalogo" id="w_id_peligro_catalogo"></select>
          </div>
          <div class="mb-2"><label class="form-label">Clasificación</label>
            <select class="form-select" name="id_clasificacion" id="w_id_clasificacion"></select>
          </div>
          <div class="mb-2"><label class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion_peligro" id="w_descripcion_peligro" rows="2"></textarea>
          </div>
          <div class="mb-2"><label class="form-label">Efectos posibles</label>
            <textarea class="form-control" name="efectos_posibles" id="w_efectos_posibles" rows="2"></textarea>
          </div>
        </div>

        <!-- Paso 3 -->
        <div class="wizard-step" data-step="3">
          <div class="mb-2"><label class="form-label">Control fuente</label><textarea class="form-control" name="control_fuente" id="w_control_fuente" rows="2"></textarea></div>
          <div class="mb-2"><label class="form-label">Control medio</label><textarea class="form-control" name="control_medio" id="w_control_medio" rows="2"></textarea></div>
          <div class="mb-2"><label class="form-label">Control individuo</label><textarea class="form-control" name="control_individuo" id="w_control_individuo" rows="2"></textarea></div>
        </div>

        <!-- Paso 4 -->
        <div class="wizard-step" data-step="4">
          <div class="mb-2"><label class="form-label">Nivel Deficiencia (ND)</label><select class="form-select" name="id_nd" id="w_id_nd" onchange="recalcularWiz()"></select></div>
          <div class="mb-2"><label class="form-label">Nivel Exposición (NE)</label><select class="form-select" name="id_ne" id="w_id_ne" onchange="recalcularWiz()"></select></div>
          <div class="mb-2"><label class="form-label">Nivel Consecuencia (NC)</label><select class="form-select" name="id_nc" id="w_id_nc" onchange="recalcularWiz()"></select></div>
          <div class="p-3 rounded text-center text-white mt-3" id="w_resultado_box" style="background:#9ca3af;">
            <div class="small opacity-75">Nivel de riesgo</div>
            <div class="fs-4 fw-bold" id="w_nivel">—</div>
            <div id="w_acept">—</div>
          </div>
        </div>

        <!-- Paso 5 -->
        <div class="wizard-step" data-step="5">
          <div class="mb-2"><label class="form-label">Peor consecuencia</label><textarea class="form-control" name="peor_consecuencia" id="w_peor_consecuencia" rows="2"></textarea></div>
          <div class="mb-2"><label class="form-label">Requisito legal</label><textarea class="form-control" name="requisito_legal" id="w_requisito_legal" rows="2"></textarea></div>
        </div>

        <!-- Paso 6 -->
        <div class="wizard-step" data-step="6">
          <div class="mb-2"><label class="form-label">Eliminación</label><textarea class="form-control" name="medida_eliminacion" id="w_medida_eliminacion" rows="2"></textarea></div>
          <div class="mb-2"><label class="form-label">Sustitución</label><textarea class="form-control" name="medida_sustitucion" id="w_medida_sustitucion" rows="2"></textarea></div>
          <div class="mb-2"><label class="form-label">Ingeniería</label><textarea class="form-control" name="medida_ingenieria" id="w_medida_ingenieria" rows="2"></textarea></div>
          <div class="mb-2"><label class="form-label">Administrativo</label><textarea class="form-control" name="medida_administrativa" id="w_medida_administrativa" rows="2"></textarea></div>
          <div class="mb-2"><label class="form-label">EPP</label><textarea class="form-control" name="medida_epp" id="w_medida_epp" rows="2"></textarea></div>
        </div>

        <div class="wizard-nav">
          <button type="button" class="btn btn-secondary" id="wBtnPrev" onclick="wizPrev()"><i class="fa-solid fa-arrow-left me-1"></i>Anterior</button>
          <button type="button" class="btn btn-warning" id="wBtnNext" onclick="wizNext()">Siguiente<i class="fa-solid fa-arrow-right ms-1"></i></button>
          <button type="submit" class="btn btn-success" id="wBtnSave" style="display:none"><i class="fa-solid fa-check me-1"></i>Guardar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
window.IPEVR_CTX = {
  id_matriz: <?= (int)$matriz['id'] ?>,
  id_cliente: <?= (int)$matriz['id_cliente'] ?>,
  estado: <?= json_encode($matriz['estado']) ?>,
  base: '<?= base_url() ?>',
};
window.GTC45_CATALOGO = <?= json_encode($catalogo) ?>;
window.MAESTROS_CLIENTE = <?= json_encode($maestros) ?>;
window.FILAS_DATA = <?= json_encode($filas) ?>;
window.IPEVR_PWA_ENDPOINT = '<?= base_url('ipevr/pwa/fila/autosave') ?>';
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('js/ipevr_calculadora.js') ?>"></script>
<script src="<?= base_url('js/ipevr_pwa_queue.js') ?>"></script>
<script>
const BASE = window.IPEVR_CTX.base;
const CAT = window.GTC45_CATALOGO;
const MAE = window.MAESTROS_CLIENTE;
const FILAS = Object.fromEntries((window.FILAS_DATA||[]).map(f=>[String(f.id),f]));

let wizPaso = 1;
const WIZ_TOTAL = 6;

function poblarSel(id, lista, labelKey, idKey='id', placeholder='(Ninguno)'){
  const el = document.getElementById(id);
  el.innerHTML = `<option value="">${placeholder}</option>` + lista.map(x => `<option value="${x[idKey]}">${x[labelKey]}</option>`).join('');
}
function poblarPeligrosPwa(){
  const el = document.getElementById('w_id_peligro_catalogo');
  const porClasif = {};
  CAT.clasificaciones.forEach(c => porClasif[c.id] = { nombre: c.nombre, items: [] });
  CAT.peligros.forEach(p => { if (porClasif[p.id_clasificacion]) porClasif[p.id_clasificacion].items.push(p); });
  let html = '<option value="">(Seleccione)</option>';
  Object.values(porClasif).forEach(g => {
    if (!g.items.length) return;
    html += `<optgroup label="${g.nombre}">` + g.items.map(p => `<option value="${p.id}" data-clasif="${p.id_clasificacion}">${p.nombre}</option>`).join('') + '</optgroup>';
  });
  el.innerHTML = html;
}

function recalcularWiz(){
  const ndId = document.getElementById('w_id_nd').value;
  const neId = document.getElementById('w_id_ne').value;
  const ncId = document.getElementById('w_id_nc').value;
  const rNP = IPEVR.calcularNP(ndId, neId);
  const rNR = IPEVR.calcularNR(rNP.np, ncId);
  const box = document.getElementById('w_resultado_box');
  if (rNR.interpretacion) {
    box.style.background = rNR.color || '#9ca3af';
    document.getElementById('w_nivel').textContent = 'Nivel ' + rNR.interpretacion.nombre + ' (NR=' + rNR.nr + ')';
    document.getElementById('w_acept').textContent = rNR.aceptabilidad;
  } else {
    box.style.background = '#9ca3af';
    document.getElementById('w_nivel').textContent = '—';
    document.getElementById('w_acept').textContent = '—';
  }
}

function mostrarPaso(n){
  wizPaso = Math.max(1, Math.min(WIZ_TOTAL, n));
  document.querySelectorAll('.wizard-step').forEach(s => s.classList.remove('active'));
  document.querySelector(`.wizard-step[data-step="${wizPaso}"]`).classList.add('active');
  document.getElementById('wizProgBar').style.width = (wizPaso / WIZ_TOTAL * 100) + '%';
  document.getElementById('wizStepTitle').textContent = `Paso ${wizPaso} de ${WIZ_TOTAL}`;
  document.getElementById('wBtnPrev').style.display = wizPaso === 1 ? 'none' : '';
  document.getElementById('wBtnNext').style.display = wizPaso === WIZ_TOTAL ? 'none' : '';
  document.getElementById('wBtnSave').style.display = wizPaso === WIZ_TOTAL ? '' : 'none';
}
function wizNext(){ mostrarPaso(wizPaso + 1); }
function wizPrev(){ mostrarPaso(wizPaso - 1); }

const CAMPOS = ['id','proceso_texto','zona_texto','actividad','tarea_texto','rutinaria','cargos_expuestos','num_expuestos',
  'id_peligro_catalogo','id_clasificacion','descripcion_peligro','efectos_posibles',
  'control_fuente','control_medio','control_individuo',
  'id_nd','id_ne','id_nc',
  'peor_consecuencia','requisito_legal',
  'medida_eliminacion','medida_sustitucion','medida_ingenieria','medida_administrativa','medida_epp'];

function limpiarWiz(){
  CAMPOS.forEach(k => { const el = document.getElementById('w_'+k); if (el) el.value = ''; });
  document.getElementById('w_rutinaria').value = '1';
  document.getElementById('w_num_expuestos').value = '0';
  recalcularWiz();
}

function cargarWiz(fila){
  CAMPOS.forEach(k => {
    const el = document.getElementById('w_'+k);
    if (!el) return;
    let v = fila[k];
    if (k === 'cargos_expuestos') {
      try { v = Array.isArray(v) ? v.join(', ') : JSON.parse(v||'[]').join(', '); } catch(e){ v = v || ''; }
    }
    el.value = v == null ? '' : v;
  });
  recalcularWiz();
}

function abrirNuevaFila(){
  limpiarWiz();
  mostrarPaso(1);
  document.getElementById('wizardTitulo').textContent = 'Nueva fila';
  new bootstrap.Modal(document.getElementById('modalWizard')).show();
}
function editarFila(id){
  const f = FILAS[String(id)];
  if (!f) return;
  limpiarWiz();
  cargarWiz(f);
  mostrarPaso(1);
  document.getElementById('wizardTitulo').textContent = 'Editar fila #' + id;
  new bootstrap.Modal(document.getElementById('modalWizard')).show();
}

async function guardarWizard(ev){
  ev.preventDefault();
  const fd = new FormData(document.getElementById('formWizard'));
  const payload = Object.fromEntries(fd.entries());

  if (!navigator.onLine) {
    IPEVR_QUEUE.enqueue(payload);
    actualizarCola();
    alert('Sin conexión. Fila guardada en cola local.');
    bootstrap.Modal.getInstance(document.getElementById('modalWizard')).hide();
    return;
  }

  try {
    const r = await fetch(BASE + 'ipevr/pwa/fila/autosave', { method: 'POST', body: fd });
    const j = await r.json();
    if (j.ok) {
      bootstrap.Modal.getInstance(document.getElementById('modalWizard')).hide();
      location.reload();
    } else {
      alert('Error: ' + (j.error || 'no se pudo guardar'));
    }
  } catch (e) {
    IPEVR_QUEUE.enqueue(payload);
    actualizarCola();
    alert('Error de red. Guardado en cola local.');
    bootstrap.Modal.getInstance(document.getElementById('modalWizard')).hide();
  }
}

function actualizarCola(){
  document.getElementById('colaSize').textContent = IPEVR_QUEUE.size();
  const bar = document.getElementById('syncBar');
  const st  = document.getElementById('syncStatus');
  if (navigator.onLine) {
    bar.classList.remove('offline');
    st.innerHTML = '<i class="fa-solid fa-wifi me-1"></i>Online';
  } else {
    bar.classList.add('offline');
    st.innerHTML = '<i class="fa-solid fa-xmark me-1"></i>Offline';
  }
}
window.addEventListener('online',  actualizarCola);
window.addEventListener('offline', actualizarCola);

document.addEventListener('DOMContentLoaded', () => {
  poblarSel('w_id_clasificacion', CAT.clasificaciones, 'nombre');
  poblarPeligrosPwa();
  poblarSel('w_id_nd', CAT.nd, 'nombre', 'id', 'Seleccione ND');
  poblarSel('w_id_ne', CAT.ne, 'nombre', 'id', 'Seleccione NE');
  poblarSel('w_id_nc', CAT.nc, 'nombre', 'id', 'Seleccione NC');
  actualizarCola();
});
</script>
</body>
</html>
