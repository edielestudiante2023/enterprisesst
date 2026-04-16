<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($titulo) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
.cat-header .badge-tipo{background:var(--gold-primary);font-size:.7rem;padding:4px 10px;border-radius:12px;margin-left:10px;}
.epp-thumb{width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid #ddd;}
.epp-thumb-empty{width:64px;height:64px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:1.5rem;}
.ia-badge{background:#fef3c7;color:#92400e;font-size:.7rem;padding:3px 8px;border-radius:10px;font-weight:600;}
.sticky-actions{position:sticky;bottom:20px;background:#fff;padding:15px;border-radius:12px;box-shadow:0 -4px 15px rgba(0,0,0,.1);margin-top:20px;display:none;}
.sticky-actions.visible{display:flex;align-items:center;justify-content:space-between;}
.truncate-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
</style>
</head>
<body>
<div class="container-fluid py-4">
  <div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h1><i class="fa-solid fa-shield-halved me-2"></i><?= esc($titulo) ?></h1>
      <div class="subtitulo" style="opacity:.85">Universo global de EPP y dotación. Asignable a cualquier cliente.</div>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= base_url('matrizEpp/maestro/nuevo') ?>" class="btn btn-gold">
        <i class="fa-solid fa-plus me-1"></i> Nuevo elemento
      </a>
    </div>
  </div>

  <?php if (session('success')): ?>
    <div class="alert alert-success"><?= esc(session('success')) ?></div>
  <?php endif ?>
  <?php if (session('error')): ?>
    <div class="alert alert-danger"><?= esc(session('error')) ?></div>
  <?php endif ?>

  <!-- Filtros -->
  <div class="card card-main p-3 mb-3">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label small mb-1">Categoría</label>
        <select name="id_categoria" class="form-select form-select-sm">
          <option value="">Todas</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= $c['id_categoria'] ?>" <?= (string)$filtros['id_categoria']===(string)$c['id_categoria']?'selected':'' ?>>
              <?= esc($c['nombre']) ?> (<?= esc($c['tipo']) ?>)
            </option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-1">Tipo</label>
        <select name="tipo" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="EPP" <?= $filtros['tipo']==='EPP'?'selected':'' ?>>EPP</option>
          <option value="DOTACION" <?= $filtros['tipo']==='DOTACION'?'selected':'' ?>>Dotación</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-1">Generado IA</label>
        <select name="ia_generado" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="1" <?= (string)$filtros['ia_generado']==='1'?'selected':'' ?>>Sí (pendiente validar)</option>
          <option value="0" <?= (string)$filtros['ia_generado']==='0'?'selected':'' ?>>No</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1">Buscar</label>
        <input type="text" name="q" class="form-control form-control-sm" value="<?= esc($filtros['q']) ?>" placeholder="elemento o norma...">
      </div>
      <div class="col-md-2 d-grid">
        <button class="btn btn-sm btn-gold"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
      </div>
    </form>
  </div>

  <!-- Listado agrupado por categoria -->
  <div class="card card-main p-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Elementos del catálogo (<?= (int)$totalItems ?>)</h5>
      <div class="small text-muted">
        <label><input type="checkbox" id="chkAll"> Seleccionar todos</label>
      </div>
    </div>

    <?php if (empty($grupos)): ?>
      <div class="text-center py-5 text-muted">
        <div style="font-size:4rem"><i class="fa-solid fa-folder-open"></i></div>
        <p>No hay elementos que coincidan con los filtros.</p>
        <a href="<?= base_url('matrizEpp/maestro/nuevo') ?>" class="btn btn-gold"><i class="fa-solid fa-plus me-1"></i>Crear el primero</a>
      </div>
    <?php else: ?>
      <?php foreach ($grupos as $nombreCat => $items): ?>
        <div class="cat-header">
          <i class="fa-solid fa-layer-group me-2"></i><?= esc($nombreCat) ?>
          <span class="badge-tipo"><?= esc($items[0]['categoria_tipo'] ?? '') ?></span>
          <span class="float-end small" style="opacity:.8">(<?= count($items) ?>)</span>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th style="width:40px"></th>
                <th style="width:80px">Foto</th>
                <th>Elemento</th>
                <th>Norma</th>
                <th>Frecuencia</th>
                <th style="width:100px">IA</th>
                <th style="width:130px" class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $it): ?>
              <tr>
                <td>
                  <input type="checkbox" class="chk-epp form-check-input" value="<?= (int)$it['id_epp'] ?>">
                </td>
                <td>
                  <?php if (!empty($it['foto_path'])): ?>
                    <img src="<?= base_url($it['foto_path']) ?>?v=<?= strtotime($it['updated_at'] ?? 'now') ?>" class="epp-thumb" alt="">
                  <?php else: ?>
                    <div class="epp-thumb-empty"><i class="fa-solid fa-image"></i></div>
                  <?php endif ?>
                </td>
                <td><strong><?= esc($it['elemento']) ?></strong></td>
                <td class="small text-muted"><div class="truncate-2"><?= esc($it['norma'] ?? '') ?></div></td>
                <td class="small"><?= esc($it['frecuencia_cambio'] ?? '') ?></td>
                <td>
                  <?php if ((int)$it['ia_generado'] === 1): ?>
                    <span class="ia-badge"><i class="fa-solid fa-robot me-1"></i>IA</span>
                  <?php endif ?>
                </td>
                <td class="text-end">
                  <a href="<?= base_url('matrizEpp/maestro/' . $it['id_epp'] . '/editar') ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fa-solid fa-pen"></i>
                  </a>
                  <button type="button" class="btn btn-sm btn-outline-danger" onclick="desactivar(<?= (int)$it['id_epp'] ?>)">
                    <i class="fa-solid fa-trash"></i>
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

  <!-- Sticky bar asignacion masiva -->
  <div class="sticky-actions" id="stickyActions">
    <div><i class="fa-solid fa-check-square me-2"></i><span id="chkCount">0</span> elementos seleccionados</div>
    <button class="btn btn-gold" id="btnAsignar">
      <i class="fa-solid fa-user-plus me-1"></i> Asignar a cliente
    </button>
  </div>
</div>

<!-- Modal asignacion -->
<div class="modal fade" id="modalAsignar" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:#1c2437;color:#fff">
        <h5 class="modal-title"><i class="fa-solid fa-user-plus me-2"></i>Asignar elementos a un cliente</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label"><strong>Cliente destino</strong></label>
          <select id="selectCliente" class="form-select" style="width:100%">
            <option value="">-- selecciona un cliente --</option>
          </select>
        </div>

        <div class="alert alert-info small">
          Vas a asignar <strong><span id="modalCount">0</span></strong> elementos. Los 5 campos técnicos del maestro
          (norma, mantenimiento, frecuencia, motivos, momentos de uso) se copiarán como snapshot editable
          en la matriz del cliente.
        </div>

        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="chkSobrescribir">
          <label class="form-check-label" for="chkSobrescribir">
            Sobrescribir si el elemento ya existe en la matriz del cliente
            <div class="small text-muted">(descarta ediciones inline previas del consultor — usar con cuidado)</div>
          </label>
        </div>

        <details>
          <summary class="small text-muted">Ver lista de elementos seleccionados</summary>
          <ul class="small mt-2" id="listaSeleccionados"></ul>
        </details>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-gold" id="btnConfirmarAsignar"><i class="fa-solid fa-check me-1"></i>Confirmar asignación</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
const chks = document.querySelectorAll('.chk-epp');
const chkAll = document.getElementById('chkAll');
const sticky = document.getElementById('stickyActions');
const chkCount = document.getElementById('chkCount');

function refreshCount(){
  const n = document.querySelectorAll('.chk-epp:checked').length;
  chkCount.textContent = n;
  sticky.classList.toggle('visible', n > 0);
}
chks.forEach(c => c.addEventListener('change', refreshCount));
if (chkAll) chkAll.addEventListener('change', e => {
  chks.forEach(c => c.checked = e.target.checked);
  refreshCount();
});

// Cargar clientes en Select2 (lazy: al abrir modal)
let clientesCargados = false;
async function cargarClientes(){
  if (clientesCargados) return;
  try {
    const r = await fetch('<?= base_url('matrizEpp/clientesJson') ?>');
    const j = await r.json();
    if (j.ok) {
      const $sel = jQuery('#selectCliente');
      j.clientes.forEach(c => {
        $sel.append(new Option(c.nombre_cliente + ' (ID ' + c.id_cliente + ')', c.id_cliente));
      });
      $sel.select2({ dropdownParent: jQuery('#modalAsignar'), theme: 'bootstrap-5', placeholder: 'Busca un cliente...' });
      clientesCargados = true;
    }
  } catch (e) { console.error(e); }
}

document.getElementById('btnAsignar').addEventListener('click', async () => {
  const ids = Array.from(document.querySelectorAll('.chk-epp:checked')).map(c => c.value);
  if (ids.length === 0) return;
  document.getElementById('modalCount').textContent = ids.length;
  const lista = document.getElementById('listaSeleccionados');
  lista.innerHTML = '';
  document.querySelectorAll('.chk-epp:checked').forEach(c => {
    const nombre = c.closest('tr').querySelector('td:nth-child(3)').textContent.trim();
    const li = document.createElement('li');
    li.textContent = nombre;
    lista.appendChild(li);
  });
  await cargarClientes();
  new bootstrap.Modal(document.getElementById('modalAsignar')).show();
});

document.getElementById('btnConfirmarAsignar').addEventListener('click', async () => {
  const idCliente = document.getElementById('selectCliente').value;
  if (!idCliente) {
    Swal.fire({icon:'warning', title:'Selecciona un cliente'});
    return;
  }
  const ids = Array.from(document.querySelectorAll('.chk-epp:checked')).map(c => c.value);
  if (ids.length === 0) return;
  const sobrescribir = document.getElementById('chkSobrescribir').checked ? 1 : 0;

  const confirm = await Swal.fire({
    title: '¿Confirmar asignación?',
    html: `Asignar <b>${ids.length}</b> elementos al cliente.<br>` +
          (sobrescribir ? '<span class="text-danger">Los existentes serán sobrescritos.</span>' : 'Los ya existentes serán omitidos.'),
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, asignar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#bd9751'
  });
  if (!confirm.isConfirmed) return;

  const fd = new FormData();
  fd.append('id_cliente', idCliente);
  fd.append('sobrescribir', sobrescribir);
  ids.forEach(id => fd.append('ids_epp[]', id));

  const r = await fetch('<?= base_url('matrizEpp/asignarACliente') ?>', {method:'POST', body:fd});
  const j = await r.json();
  if (!j.ok) {
    Swal.fire({icon:'error', title:'Error', text: (j.errores||[]).join(', ') || 'Falló la operación'});
    return;
  }

  const modal = bootstrap.Modal.getInstance(document.getElementById('modalAsignar'));
  modal.hide();

  Swal.fire({
    icon: 'success',
    title: 'Asignación completa',
    html: `Insertados: <b>${j.insertados}</b><br>Actualizados: <b>${j.actualizados}</b><br>Omitidos: <b>${j.omitidos}</b>`,
    showCancelButton: true,
    confirmButtonText: 'Ir a la matriz del cliente',
    cancelButtonText: 'Seguir aquí',
    confirmButtonColor: '#bd9751'
  }).then(res => {
    if (res.isConfirmed) {
      location.href = '<?= base_url('matrizEpp/cliente/') ?>' + idCliente;
    } else {
      document.querySelectorAll('.chk-epp:checked').forEach(c => c.checked = false);
      refreshCount();
    }
  });
});

function desactivar(id){
  Swal.fire({
    title: '¿Desactivar este elemento?',
    text: 'No se eliminará de la BD, solo se ocultará del catálogo.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, desactivar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#bd9751'
  }).then(r => {
    if (!r.isConfirmed) return;
    fetch('<?= base_url('matrizEpp/maestro/') ?>' + id + '/desactivar', { method:'POST' })
      .then(r => r.json())
      .then(j => { if (j.ok) location.reload(); });
  });
}
</script>
</body>
</html>
