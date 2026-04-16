<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($titulo) ?> — <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
:root{--primary-dark:#1c2437;--secondary-dark:#2c3e50;--gold-primary:#bd9751;--gold-secondary:#d4af37;--gradient-bg:linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%);}
body{background:var(--gradient-bg);font-family:'Segoe UI',sans-serif;min-height:100vh;}
.page-header{background:linear-gradient(135deg,var(--primary-dark),var(--secondary-dark),var(--gold-primary));color:#fff;padding:25px 30px;border-radius:15px;margin-bottom:20px;box-shadow:0 10px 30px rgba(0,0,0,.2);}
.page-header h1{font-size:1.6rem;margin:0 0 4px;font-weight:700;}
.card-main{background:#fff;border:none;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.08);}
.btn-gold{background:linear-gradient(135deg,var(--gold-primary),var(--gold-secondary));color:#fff;border:none;font-weight:600;}
.btn-gold:hover{filter:brightness(1.1);color:#fff;}
.cat-header{background:var(--primary-dark);color:#fff;padding:10px 15px;border-radius:8px;margin:20px 0 10px;font-weight:600;}
.epp-foto{width:90px;height:90px;object-fit:cover;border-radius:10px;border:1px solid #e5e7eb;}
.epp-foto-empty{width:90px;height:90px;border-radius:10px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:2rem;}
.epp-row td{vertical-align:top;padding:12px 10px;}
.inline-input,.inline-ta{width:100%;border:1px dashed transparent;background:transparent;padding:6px 8px;border-radius:6px;font-size:.88rem;transition:all .15s;resize:vertical;}
.inline-input:hover,.inline-ta:hover{border-color:#d4af37;background:#fffbeb;}
.inline-input:focus,.inline-ta:focus{border-color:#bd9751;background:#fff;outline:none;box-shadow:0 0 0 2px rgba(189,151,81,.2);}
.sync-ok{color:#059669;font-weight:600;}
.sync-edit{color:#d97706;font-weight:600;}
.save-badge{position:fixed;bottom:20px;right:20px;background:#059669;color:#fff;padding:10px 20px;border-radius:20px;opacity:0;transition:opacity .3s;box-shadow:0 4px 15px rgba(0,0,0,.2);z-index:9999;}
.save-badge.show{opacity:1;}
.save-badge.err{background:#dc2626;}
table.matriz{width:100%;table-layout:fixed;}
table.matriz th{background:#f1f5f9;font-size:.8rem;text-transform:uppercase;padding:10px 8px;border-bottom:2px solid var(--gold-primary);}
.col-foto{width:100px;}
.col-elem{width:14%;}
.col-norma{width:14%;}
.col-mant{width:16%;}
.col-freq{width:12%;}
.col-mot{width:14%;}
.col-mom{width:14%;}
.col-obs{width:12%;}
.col-sync{width:70px;}
.col-act{width:85px;}
.diff-table{width:100%;font-size:.85rem;}
.diff-table th{background:#f1f5f9;padding:6px 10px;text-align:left;border-bottom:2px solid #bd9751;}
.diff-table td{padding:8px 10px;vertical-align:top;border-bottom:1px solid #e5e7eb;}
.diff-table .c-actual{background:#fef3c7;color:#78350f;}
.diff-table .c-maestro{background:#dcfce7;color:#14532d;}
.diff-label{font-weight:600;color:#64748b;text-transform:uppercase;font-size:.75rem;}
</style>
</head>
<body>
<div class="container-fluid py-4">
  <div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h1><i class="fa-solid fa-shield-halved me-2"></i><?= esc($titulo) ?></h1>
      <div style="opacity:.85"><?= esc($cliente['nombre_cliente']) ?> (ID <?= (int)$cliente['id_cliente'] ?>) · <?= (int)$total ?> elementos</div>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-warning" onclick="abrirDiffMasivo()">
        <i class="fa-solid fa-arrows-rotate me-1"></i> Resincronizar desde maestro
      </button>
      <a href="<?= base_url('matrizEpp/maestro') ?>?cliente_destino=<?= (int)$cliente['id_cliente'] ?>" class="btn btn-light">
        <i class="fa-solid fa-plus me-1"></i> Agregar más del catálogo
      </a>
    </div>
  </div>

  <div class="alert alert-warning small">
    <i class="fa-solid fa-pen-to-square me-1"></i>
    <strong>Edición inline:</strong> haz click en cualquier celda para editarla. Los cambios se guardan automáticamente al salir del campo.
    Si editas los campos técnicos, el ítem se marca como <span class="sync-edit">✏️ editado localmente</span>
    (ya no sigue sincronizado con el maestro). La observación del cliente NO afecta la sincronización.
  </div>

  <div class="card card-main p-3">
    <?php if (empty($grupos)): ?>
      <div class="text-center py-5 text-muted">
        <div style="font-size:4rem"><i class="fa-solid fa-folder-open"></i></div>
        <p>Este cliente aún no tiene elementos asignados en su matriz de EPP.</p>
        <a href="<?= base_url('matrizEpp/maestro') ?>" class="btn btn-gold"><i class="fa-solid fa-plus me-1"></i>Asignar del catálogo maestro</a>
      </div>
    <?php else: ?>
      <?php foreach ($grupos as $nombreCat => $items): ?>
        <div class="cat-header">
          <i class="fa-solid fa-layer-group me-2"></i><?= esc($nombreCat) ?>
          <span class="float-end small" style="opacity:.8"><?= count($items) ?> elementos</span>
        </div>

        <div class="table-responsive">
          <table class="matriz">
            <colgroup>
              <col class="col-foto"><col class="col-elem"><col class="col-norma"><col class="col-mant">
              <col class="col-freq"><col class="col-mot"><col class="col-mom"><col class="col-obs">
              <col class="col-sync"><col class="col-act">
            </colgroup>
            <thead>
              <tr>
                <th>Foto</th>
                <th>Elemento</th>
                <th>Norma</th>
                <th>Mantenimiento</th>
                <th>Frecuencia</th>
                <th>Motivos cambio</th>
                <th>Momentos de uso</th>
                <th>Observación cliente</th>
                <th class="text-center">Sync</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $f): ?>
              <tr class="epp-row" data-id-epp="<?= (int)$f['id_epp'] ?>">
                <td>
                  <?php if (!empty($f['foto_path'])): ?>
                    <img src="<?= base_url($f['foto_path']) ?>" class="epp-foto" alt="">
                  <?php else: ?>
                    <div class="epp-foto-empty"><i class="fa-solid fa-image"></i></div>
                  <?php endif ?>
                </td>
                <td><input type="text" class="inline-input" data-campo="elemento" value="<?= esc($f['elemento']) ?>"></td>
                <td><textarea class="inline-ta" data-campo="norma" rows="3"><?= esc($f['norma']) ?></textarea></td>
                <td><textarea class="inline-ta" data-campo="mantenimiento" rows="3"><?= esc($f['mantenimiento']) ?></textarea></td>
                <td><textarea class="inline-ta" data-campo="frecuencia_cambio" rows="3"><?= esc($f['frecuencia_cambio']) ?></textarea></td>
                <td><textarea class="inline-ta" data-campo="motivos_cambio" rows="3"><?= esc($f['motivos_cambio']) ?></textarea></td>
                <td><textarea class="inline-ta" data-campo="momentos_uso" rows="3"><?= esc($f['momentos_uso']) ?></textarea></td>
                <td><textarea class="inline-ta" data-campo="observacion_cliente" rows="3" placeholder="—"><?= esc($f['observacion_cliente'] ?? '') ?></textarea></td>
                <td class="text-center small">
                  <span class="sync-label">
                    <?php if ((int)$f['sincronizado_maestro'] === 1): ?>
                      <span class="sync-ok" title="Sincronizado con el maestro">✅ Sync</span>
                    <?php else: ?>
                      <span class="sync-edit" title="Editado localmente para este cliente">✏️ Editado</span>
                    <?php endif ?>
                  </span>
                </td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-warning mb-1" onclick="abrirDiffFila(<?= (int)$f['id_epp'] ?>)" title="Resincronizar desde maestro">
                    <i class="fa-solid fa-arrows-rotate"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" onclick="quitarItem(<?= (int)$f['id_epp'] ?>)" title="Quitar de la matriz">
                    <i class="fa-solid fa-times"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach ?>
            </tbody>
          </table>
        </div>
      <?php endforeach ?>
    <?php endif ?>
  </div>
</div>

<div class="save-badge" id="saveBadge"><i class="fa-solid fa-check me-1"></i><span id="saveMsg">Guardado</span></div>

<script>
const ID_CLIENTE = <?= (int)$cliente['id_cliente'] ?>;
const URL_INLINE = '<?= base_url('matrizEpp/cliente/') ?>' + ID_CLIENTE + '/item/';
const badge = document.getElementById('saveBadge');
const badgeMsg = document.getElementById('saveMsg');

function showBadge(msg, err=false){
  badgeMsg.textContent = msg;
  badge.classList.toggle('err', err);
  badge.classList.add('show');
  setTimeout(() => badge.classList.remove('show'), 1500);
}

// Snapshot inicial para detectar cambios reales
document.querySelectorAll('.epp-row').forEach(row => {
  row.querySelectorAll('[data-campo]').forEach(el => {
    el.dataset.original = el.value;
    el.addEventListener('blur', () => guardarCampo(row, el));
  });
});

async function guardarCampo(row, el){
  const valor = el.value;
  if (valor === el.dataset.original) return; // sin cambios
  const campo = el.dataset.campo;
  const idEpp = row.dataset.idEpp;

  const fd = new FormData();
  fd.append('campo', campo);
  fd.append('valor', valor);

  try {
    const r = await fetch(URL_INLINE + idEpp + '/editarInline', {method:'POST', body:fd});
    const j = await r.json();
    if (!j.ok) {
      showBadge('Error: ' + (j.error || 'fallo'), true);
      el.value = el.dataset.original;
      return;
    }
    el.dataset.original = valor;
    showBadge('Guardado');

    // Si cambió el estado de sincronización, actualizar el badge de la fila
    if (campo !== 'observacion_cliente') {
      const label = row.querySelector('.sync-label');
      if (j.sincronizado_maestro === 0) {
        label.innerHTML = '<span class="sync-edit" title="Editado localmente">✏️ Editado</span>';
      }
    }
  } catch (err) {
    showBadge('Error de red', true);
    el.value = el.dataset.original;
  }
}

// ============ RESINCRONIZACIÓN DESDE MAESTRO ============

const LABELS_CAMPOS = {
  elemento: 'Elemento',
  norma: 'Norma',
  mantenimiento: 'Mantenimiento',
  frecuencia_cambio: 'Frecuencia',
  motivos_cambio: 'Motivos cambio',
  momentos_uso: 'Momentos de uso'
};

function renderDiffTable(diff){
  if (!diff.cambios.length) {
    return '<div class="alert alert-success mb-0">Este ítem ya está sincronizado con el maestro. No hay diferencias.</div>';
  }
  let html = '<table class="diff-table"><thead><tr>' +
    '<th style="width:20%">Campo</th><th style="width:40%">Valor actual (cliente)</th><th style="width:40%">Valor en maestro</th></tr></thead><tbody>';
  diff.cambios.forEach(c => {
    html += `<tr>
      <td class="diff-label">${LABELS_CAMPOS[c] || c}</td>
      <td class="c-actual">${escapeHtml(diff.cliente[c] || '—')}</td>
      <td class="c-maestro">${escapeHtml(diff.maestro[c] || '—')}</td>
    </tr>`;
  });
  html += '</tbody></table>';
  return html;
}

function escapeHtml(str){
  return String(str).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[s]));
}

async function abrirDiffFila(idEpp){
  const r = await fetch(URL_INLINE + idEpp + '/diffMaestro');
  const j = await r.json();
  if (!j.ok) {
    Swal.fire({icon:'error', title:'Error', text: j.error || 'No se pudo cargar el diff'});
    return;
  }

  if (j.cambios.length === 0) {
    Swal.fire({icon:'success', title:'Ya está sincronizado', text:'Este ítem coincide con el maestro.'});
    return;
  }

  const html = '<div class="text-start">' +
    '<p class="small text-muted mb-2">Revisa las diferencias. Al confirmar, los valores del <strong>maestro</strong> sobrescribirán los del cliente.</p>' +
    renderDiffTable(j) +
    '<div class="alert alert-warning small mt-3 mb-0"><i class="fa-solid fa-triangle-exclamation me-1"></i>' +
    '<strong>Advertencia:</strong> esta acción sobrescribe tus ajustes inline con los valores del maestro. La observación del cliente no se toca.</div>' +
    '</div>';

  const confirm = await Swal.fire({
    title: 'Resincronizar desde maestro',
    html: html,
    width: 900,
    showCancelButton: true,
    confirmButtonText: 'Sí, sobrescribir con el maestro',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#dc2626',
    focusCancel: true
  });
  if (!confirm.isConfirmed) return;

  const rr = await fetch(URL_INLINE + idEpp + '/resincronizar', {method:'POST'});
  const jj = await rr.json();
  if (jj.ok) {
    Swal.fire({icon:'success', title:'Resincronizado', timer:1200, showConfirmButton:false}).then(() => location.reload());
  } else {
    Swal.fire({icon:'error', title:'Error', text: jj.error || 'Falló la operación'});
  }
}

async function abrirDiffMasivo(){
  Swal.fire({title:'Calculando diferencias...', allowOutsideClick:false, didOpen:()=>Swal.showLoading()});
  const r = await fetch('<?= base_url('matrizEpp/cliente/') ?>' + ID_CLIENTE + '/diffMasivoJson');
  const j = await r.json();
  Swal.close();

  if (!j.ok) {
    Swal.fire({icon:'error', title:'Error', text:'No se pudo calcular el diff'});
    return;
  }
  if (j.total === 0) {
    Swal.fire({icon:'success', title:'Todo sincronizado', text:'Ningún ítem difiere del maestro.'});
    return;
  }

  let html = '<div class="text-start">' +
    '<p class="small text-muted mb-2">' + j.total + ' ítem(s) difieren del maestro. Selecciona cuáles resincronizar:</p>' +
    '<div style="max-height:400px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:8px;padding:10px">';

  j.diffs.forEach(d => {
    const campos = d.cambios.map(c => LABELS_CAMPOS[c] || c).join(', ');
    html += `<div class="form-check mb-2 pb-2 border-bottom">
      <input class="form-check-input diff-chk" type="checkbox" value="${d.id_epp}" id="diff_${d.id_epp}" checked>
      <label class="form-check-label w-100" for="diff_${d.id_epp}">
        <strong>${escapeHtml(d.elemento_cliente)}</strong>
        ${d.elemento_cliente !== d.elemento_maestro ? '<span class="badge bg-danger ms-2">nombre cambió</span>' : ''}
        <div class="small text-muted">Campos con diferencia: ${escapeHtml(campos)}</div>
      </label>
    </div>`;
  });

  html += '</div>' +
    '<div class="alert alert-warning small mt-3 mb-0"><i class="fa-solid fa-triangle-exclamation me-1"></i>' +
    '<strong>Advertencia:</strong> los valores del maestro sobrescribirán tus ediciones inline en los ítems seleccionados.</div>' +
    '</div>';

  const confirm = await Swal.fire({
    title: 'Resincronizar varios ítems',
    html: html,
    width: 800,
    showCancelButton: true,
    confirmButtonText: 'Sobrescribir seleccionados',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#dc2626',
    focusCancel: true,
    preConfirm: () => {
      const ids = Array.from(document.querySelectorAll('.diff-chk:checked')).map(c => c.value);
      if (ids.length === 0) {
        Swal.showValidationMessage('Selecciona al menos un ítem');
        return false;
      }
      return ids;
    }
  });
  if (!confirm.isConfirmed) return;

  const fd = new FormData();
  confirm.value.forEach(id => fd.append('ids_epp[]', id));
  const rr = await fetch('<?= base_url('matrizEpp/cliente/') ?>' + ID_CLIENTE + '/resincronizarTodos', {method:'POST', body:fd});
  const jj = await rr.json();
  if (jj.ok) {
    Swal.fire({icon:'success', title:`${jj.actualizados} ítem(s) resincronizados`, timer:1500, showConfirmButton:false}).then(() => location.reload());
  } else {
    Swal.fire({icon:'error', title:'Error', text: jj.error || 'Falló la operación'});
  }
}

function quitarItem(idEpp){
  Swal.fire({
    title: '¿Quitar este elemento de la matriz?',
    text: 'El elemento seguirá existiendo en el catálogo maestro. Solo se remueve de este cliente.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, quitar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#dc2626'
  }).then(r => {
    if (!r.isConfirmed) return;
    fetch(URL_INLINE + idEpp + '/quitar', {method:'POST'})
      .then(r => r.json())
      .then(j => { if (j.ok) location.reload(); });
  });
}
</script>
</body>
</html>
