<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($titulo) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--primary-dark:#1c2437;--gold-primary:#bd9751;--gold-secondary:#d4af37;}
body{background:linear-gradient(135deg,#f5f7fa,#c3cfe2);font-family:'Segoe UI',sans-serif;min-height:100vh;}
.page-header{background:linear-gradient(135deg,var(--primary-dark),#2c3e50,var(--gold-primary));color:#fff;padding:20px 25px;border-radius:12px;margin-bottom:20px;}
.page-header h1{font-size:1.35rem;margin:0 0 4px;font-weight:700;}
.card-accel{background:#fff;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.08);padding:20px;margin-bottom:16px;border-left:4px solid #d1d5db;}
.card-accel.pta{border-left-color:#3b82f6;}
.card-accel.cap{border-left-color:#8b5cf6;}
.card-accel.epp{border-left-color:#bd9751;}
.card-accel.insp{border-left-color:#f59e0b;}
.card-accel.pve{border-left-color:#ef4444;}
.card-accel.prof{border-left-color:#10b981;}
.card-accel h5{font-weight:700;color:var(--primary-dark);margin-bottom:8px;}
.card-accel .desc{color:#6b7280;font-size:.88rem;margin-bottom:12px;}
.btn-gold{background:linear-gradient(135deg,var(--gold-primary),var(--gold-secondary));color:#fff;border:none;font-weight:600;}
.btn-gold:hover{filter:brightness(1.1);color:#fff;}
.resultado-tabla{max-height:400px;overflow-y:auto;margin-top:12px;}
.resultado-tabla table{font-size:.82rem;}
.resultado-tabla th{background:var(--primary-dark);color:#fff;position:sticky;top:0;}
.fecha-input{width:130px;}
.badge-nivel{padding:4px 8px;border-radius:6px;color:#fff;font-weight:700;font-size:.75rem;}
</style>
</head>
<body>
<div class="container-fluid py-3">
  <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h1><i class="fa-solid fa-rocket me-2"></i><?= esc($titulo) ?></h1>
      <small class="opacity-75"><?= esc($cliente['nombre_cliente']) ?> · <?= esc($matriz['nombre']) ?> v<?= esc($matriz['version']) ?> · <?= count($filas) ?> peligros identificados</small>
    </div>
    <a href="<?= base_url('ipevr/matriz/' . $matriz['id'] . '/editar') ?>" class="btn btn-light btn-sm">
      <i class="fa-solid fa-arrow-left me-1"></i> Volver al editor
    </a>
  </div>

  <div class="alert alert-info">
    <i class="fa-solid fa-lightbulb me-1"></i>
    <strong>¿Qué es esto?</strong> El Acelerador toma los peligros y medidas de intervención de tu Matriz IPEVR y genera automáticamente las actividades para los módulos del SG-SST. Así no tienes que crearlas una por una.
  </div>

  <div class="row">
    <!-- PTA -->
    <div class="col-md-6">
      <div class="card-accel pta">
        <h5><i class="fa-solid fa-clipboard-list me-2"></i>Plan de Trabajo Anual (PTA)</h5>
        <div class="desc">Genera actividades para el PTA basándose en las medidas de intervención de la matriz. Se guardan directamente en <code>tbl_pta_cliente</code>.</div>
        <button class="btn btn-primary btn-sm" onclick="generar('pta')"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Generar actividades con IA</button>
        <div id="resultado-pta"></div>
      </div>
    </div>

    <!-- CAPACITACIONES -->
    <div class="col-md-6">
      <div class="card-accel cap">
        <h5><i class="fa-solid fa-chalkboard-teacher me-2"></i>Cronograma de Capacitaciones</h5>
        <div class="desc">Sugiere capacitaciones según los peligros identificados. Se guardan en <code>tbl_cronog_capacitacion</code>.</div>
        <button class="btn btn-primary btn-sm" onclick="generar('capacitaciones')"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Generar capacitaciones con IA</button>
        <div id="resultado-capacitaciones"></div>
      </div>
    </div>

    <!-- EPP -->
    <div class="col-md-6">
      <div class="card-accel epp">
        <h5><i class="fa-solid fa-hard-hat me-2"></i>Matriz de EPP</h5>
        <div class="desc">Asigna Elementos de Protección Personal según los peligros por cargo. Ya existe un módulo dedicado.</div>
        <a href="<?= base_url('matrizEpp/cliente/' . $matriz['id_cliente']) ?>" target="_blank" class="btn btn-gold btn-sm">
          <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Ir a Matriz EPP del cliente
        </a>
      </div>
    </div>

    <!-- INSPECCIONES -->
    <div class="col-md-6">
      <div class="card-accel insp">
        <h5><i class="fa-solid fa-magnifying-glass me-2"></i>Inspecciones sugeridas</h5>
        <div class="desc">La IA sugiere qué inspeccionar según los riesgos críticos (Nivel I y II). Solo orientativo — descarga en Excel.</div>
        <button class="btn btn-warning btn-sm" onclick="generar('inspecciones')"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Sugerir inspecciones</button>
        <div id="resultado-inspecciones"></div>
      </div>
    </div>

    <!-- PVE -->
    <div class="col-md-6">
      <div class="card-accel pve">
        <h5><i class="fa-solid fa-stethoscope me-2"></i>Programas de Vigilancia Epidemiológica (PVE)</h5>
        <div class="desc">Sugiere PVE según clasificaciones de peligro (biomecánico → osteomuscular, psicosocial → riesgo psicosocial, etc.). Solo orientativo — descarga en Excel.</div>
        <button class="btn btn-danger btn-sm text-white" onclick="generar('pve')"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Sugerir PVE</button>
        <div id="resultado-pve"></div>
      </div>
    </div>

    <!-- PROFESIOGRAMA -->
    <div class="col-md-6">
      <div class="card-accel prof">
        <h5><i class="fa-solid fa-user-doctor me-2"></i>Profesiograma</h5>
        <div class="desc">Define exámenes médicos ocupacionales por cargo según exposición a peligros identificados en la matriz.</div>
        <a href="<?= base_url('profesiograma/cliente/' . $matriz['id_cliente']) ?>" target="_blank" class="btn btn-success btn-sm">
          <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Ir al Profesiograma del cliente
        </a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
const BASE = '<?= base_url() ?>';
const ID_MATRIZ = <?= (int)$matriz['id'] ?>;
const ID_CLIENTE = <?= (int)$matriz['id_cliente'] ?>;

// Almacén temporal de sugerencias generadas
const _sugerencias = {};

async function generar(tipo) {
  const container = document.getElementById('resultado-' + tipo);

  Swal.fire({
    title: 'Generando con IA...',
    html: '<i class="fa-solid fa-wand-magic-sparkles fa-beat-fade" style="font-size:2.5rem;color:#bd9751"></i><br><br>Analizando la matriz IPEVR y el contexto del cliente...',
    allowOutsideClick: false, showConfirmButton: false,
    didOpen: () => Swal.showLoading(),
  });

  try {
    const fd = new FormData();
    fd.append('tipo', tipo);
    fd.append('id_matriz', ID_MATRIZ);
    const r = await fetch(BASE + 'ipevr/acelerador/generar', { method: 'POST', body: fd });
    const j = await r.json();
    Swal.close();

    if (!j.ok) { Swal.fire({ icon: 'error', title: 'Error', text: j.error }); return; }

    const sug = j.sugerencias || {};

    if (tipo === 'pta') renderPreviewConCheckbox(container, sug.actividades || [], 'pta', ['actividad','phva','numeral'], 'actividad');
    else if (tipo === 'capacitaciones') renderPreviewConCheckbox(container, sug.capacitaciones || [], 'capacitaciones', ['nombre','objetivo','perfil_asistentes','horas'], 'nombre');
    else if (tipo === 'inspecciones') renderSoloLectura(container, sug.inspecciones || [], 'inspecciones', ['nombre','que_inspeccionar','frecuencia','responsable_sugerido','normativa']);
    else if (tipo === 'pve') renderSoloLectura(container, sug.pve || [], 'pve', ['nombre','peligro_asociado','poblacion_objetivo','actividades_principales','periodicidad','normativa']);

  } catch (e) {
    Swal.fire({ icon: 'error', title: 'Error de red', text: e.message });
  }
}

function renderPreviewConCheckbox(container, items, tipo, cols, labelCol) {
  if (!items.length) { container.innerHTML = '<div class="alert alert-warning mt-2">La IA no generó sugerencias.</div>'; return; }
  _sugerencias[tipo] = items;

  const hoy = new Date().toISOString().slice(0, 10);
  let html = `<div class="resultado-tabla mt-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <small class="text-muted"><b>${items.length}</b> sugerencias generadas. Selecciona las que deseas guardar y asigna la fecha a cada una.</small>
      <div>
        <button class="btn btn-sm btn-outline-secondary" onclick="toggleAll('${tipo}', true)">Seleccionar todas</button>
        <button class="btn btn-sm btn-outline-secondary" onclick="toggleAll('${tipo}', false)">Ninguna</button>
      </div>
    </div>
    <table class="table table-sm table-bordered"><thead><tr>
      <th style="width:40px"><input type="checkbox" checked onclick="toggleAll('${tipo}', this.checked)" id="chkAll_${tipo}"></th>
      <th style="width:130px">Fecha</th>`;
  cols.forEach(c => html += `<th>${c.replace(/_/g, ' ')}</th>`);
  html += '</tr></thead><tbody>';
  items.forEach((item, i) => {
    html += `<tr>
      <td><input type="checkbox" checked class="chk-${tipo}" data-idx="${i}"></td>
      <td><input type="date" class="form-control form-control-sm fecha-${tipo}" data-idx="${i}" value="${hoy}"></td>`;
    cols.forEach(c => html += `<td class="small">${item[c] || ''}</td>`);
    html += '</tr>';
  });
  html += '</tbody></table></div>';
  html += `<button class="btn btn-success btn-sm mt-1" onclick="guardarSeleccionadas('${tipo}')"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar seleccionadas</button> `;
  html += `<button class="btn btn-outline-success btn-sm mt-1" onclick="descargarExcel('${tipo}')"><i class="fa-solid fa-file-excel me-1"></i>Descargar Excel</button>`;
  container.innerHTML = html;
}

function toggleAll(tipo, checked) {
  document.querySelectorAll(`.chk-${tipo}`).forEach(c => c.checked = checked);
  const chkAll = document.getElementById('chkAll_' + tipo);
  if (chkAll) chkAll.checked = checked;
}

async function guardarSeleccionadas(tipo) {
  const items = _sugerencias[tipo];
  if (!items) return;

  const seleccionadas = [];
  document.querySelectorAll(`.chk-${tipo}:checked`).forEach(chk => {
    const idx = parseInt(chk.dataset.idx);
    const fechaInput = document.querySelector(`.fecha-${tipo}[data-idx="${idx}"]`);
    seleccionadas.push({ ...items[idx], _fecha: fechaInput ? fechaInput.value : '' });
  });

  if (!seleccionadas.length) {
    Swal.fire({ icon: 'warning', title: 'Sin selección', text: 'Selecciona al menos una fila para guardar.' });
    return;
  }

  const { isConfirmed } = await Swal.fire({
    icon: 'question',
    title: `¿Guardar ${seleccionadas.length} registros?`,
    html: `Se insertarán <b>${seleccionadas.length}</b> de ${items.length} sugerencias en la base de datos.`,
    confirmButtonText: 'Sí, guardar', confirmButtonColor: '#bd9751',
    showCancelButton: true, cancelButtonText: 'Cancelar',
  });
  if (!isConfirmed) return;

  Swal.fire({ title: 'Guardando...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });

  try {
    const fd = new FormData();
    fd.append('tipo', tipo);
    fd.append('id_cliente', ID_CLIENTE);
    fd.append('datos', JSON.stringify(seleccionadas));
    const r = await fetch(BASE + 'ipevr/acelerador/guardar', { method: 'POST', body: fd });
    const j = await r.json();
    if (j.ok) {
      Swal.fire({ icon: 'success', title: 'Guardado', html: `<b>${j.insertados}</b> registros insertados.`, confirmButtonColor: '#bd9751' });
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: j.error || 'No se pudo guardar' });
    }
  } catch (e) {
    Swal.fire({ icon: 'error', title: 'Error de red', text: e.message });
  }
}

function renderSoloLectura(container, items, tipo, cols) {
  if (!items.length) { container.innerHTML = '<div class="alert alert-warning mt-2">No se generaron sugerencias.</div>'; return; }
  _sugerencias[tipo] = { items, cols };

  let html = '<div class="resultado-tabla mt-3"><table class="table table-sm table-bordered"><thead><tr>';
  cols.forEach(c => html += `<th>${c.replace(/_/g, ' ')}</th>`);
  html += '</tr></thead><tbody>';
  items.forEach(item => {
    html += '<tr>';
    cols.forEach(c => html += `<td class="small">${item[c] || ''}</td>`);
    html += '</tr>';
  });
  html += '</tbody></table></div>';
  html += `<button class="btn btn-success btn-sm mt-2" onclick="descargarExcel('${tipo}')"><i class="fa-solid fa-file-excel me-1"></i>Descargar Excel</button>`;
  container.innerHTML = html;
}

function descargarExcel(tipo) {
  let data = _sugerencias[tipo];
  if (!data) return;
  // normalizar: si tiene .items/.cols (solo lectura) o es array directo (checkbox)
  let items, cols;
  if (Array.isArray(data)) {
    items = data;
    cols = Object.keys(items[0] || {}).filter(k => !k.startsWith('_'));
  } else {
    items = data.items || data;
    cols = data.cols || Object.keys(items[0] || {}).filter(k => !k.startsWith('_'));
  }

  const ws_data = [cols.map(c => c.replace(/_/g, ' ').toUpperCase())];
  items.forEach(item => ws_data.push(cols.map(c => item[c] || '')));

  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(ws_data);
  XLSX.utils.book_append_sheet(wb, ws, tipo.toUpperCase());
  XLSX.writeFile(wb, `Sugerencias_${tipo}_${new Date().toISOString().slice(0, 10)}.xlsx`);
}
</script>
</body>
</html>
