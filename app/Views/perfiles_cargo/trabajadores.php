<?= $this->extend('layouts/base') ?>
<?= $this->section('title') ?>Trabajadores - <?= esc($cliente['nombre_cliente'] ?? '') ?><?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php $idCliente = (int)$cliente['id_cliente']; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('consultantDashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url("perfiles-cargo/{$idCliente}") ?>">Perfiles de Cargo</a></li>
            <li class="breadcrumb-item active">Trabajadores</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="mb-0"><i class="bi bi-people-fill me-2 text-primary"></i> Trabajadores</h3>
            <small class="text-muted">Cliente: <strong><?= esc($cliente['nombre_cliente']) ?></strong> &middot; <?= count($trabajadores) ?> trabajadores</small>
        </div>
        <div class="btn-group">
            <button class="btn btn-success" id="btn-nuevo"><i class="bi bi-plus-lg"></i> Nuevo</button>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-importar">
                <i class="bi bi-upload"></i> Importar CSV
            </button>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php $errs = session()->getFlashdata('errores_import'); if (!empty($errs)): ?>
        <div class="alert alert-warning">
            <strong>Errores en la importacion:</strong>
            <ul class="mb-0 small">
                <?php foreach (array_slice($errs, 0, 20) as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cedula</th><th>Nombres</th><th>Apellidos</th>
                        <th>Cargo</th><th>Email</th><th>Telefono</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($trabajadores)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No hay trabajadores aun. Use "Nuevo" o "Importar CSV".</td></tr>
                <?php else: foreach ($trabajadores as $t):
                    $cargoNombre = '';
                    foreach ($cargos as $c) if ((int)$c['id'] === (int)$t['id_cargo_cliente']) { $cargoNombre = $c['nombre_cargo']; break; }
                ?>
                    <tr data-trab='<?= esc(json_encode($t), 'attr') ?>'>
                        <td><?= esc($t['tipo_documento']) ?> <?= esc($t['cedula']) ?></td>
                        <td><?= esc($t['nombres']) ?></td>
                        <td><?= esc($t['apellidos']) ?></td>
                        <td><small><?= esc($cargoNombre ?: '—') ?></small></td>
                        <td><small><?= esc($t['email'] ?? '') ?></small></td>
                        <td><small><?= esc($t['telefono'] ?? '') ?></small></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary btn-editar"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="<?= $t['id_trabajador'] ?>"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal crear/editar -->
<div class="modal fade" id="modal-trab" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="modal-trab-titulo">Trabajador</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" id="f-id">
        <div class="row g-3">
          <div class="col-md-3"><label class="form-label">Tipo documento</label>
            <select id="f-tipo-doc" class="form-select">
              <?php foreach (['CC','CE','PA','TI','PEP'] as $td): ?><option><?= $td ?></option><?php endforeach; ?>
            </select></div>
          <div class="col-md-4"><label class="form-label">Cedula *</label><input id="f-cedula" class="form-control"></div>
          <div class="col-md-5"><label class="form-label">Cargo</label>
            <select id="f-cargo" class="form-select"><option value="">—</option>
              <?php foreach ($cargos as $c): ?><option value="<?= $c['id'] ?>"><?= esc($c['nombre_cargo']) ?></option><?php endforeach; ?>
            </select></div>
          <div class="col-md-6"><label class="form-label">Nombres *</label><input id="f-nombres" class="form-control"></div>
          <div class="col-md-6"><label class="form-label">Apellidos *</label><input id="f-apellidos" class="form-control"></div>
          <div class="col-md-6"><label class="form-label">Email</label><input type="email" id="f-email" class="form-control"></div>
          <div class="col-md-3"><label class="form-label">Telefono</label><input id="f-telefono" class="form-control"></div>
          <div class="col-md-3"><label class="form-label">Fecha ingreso</label><input type="date" id="f-fecha" class="form-control"></div>
        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-success" id="btn-guardar-trab">Guardar</button></div>
    </div>
  </div>
</div>

<!-- Modal importar -->
<div class="modal fade" id="modal-importar" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data" action="<?= base_url("perfiles-cargo/{$idCliente}/trabajadores/importar") ?>">
        <?= csrf_field() ?>
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Importar trabajadores (CSV)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <!-- Paso 1: descargar plantilla -->
          <div class="border rounded p-3 mb-3 bg-light">
            <div class="d-flex align-items-start">
              <div class="me-3">
                <span class="badge bg-primary rounded-circle" style="width:32px;height:32px;font-size:14px;line-height:22px;">1</span>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-1">Descargar plantilla</h6>
                <p class="small text-muted mb-2">
                  Descarga la plantilla CSV pre-formateada con las columnas correctas y la lista de cargos disponibles para este cliente.
                </p>
                <a class="btn btn-outline-primary btn-sm"
                   href="<?= base_url("perfiles-cargo/{$idCliente}/trabajadores/plantilla-csv") ?>"
                   download>
                  <i class="bi bi-file-earmark-arrow-down"></i> Descargar plantilla CSV
                </a>
              </div>
            </div>
          </div>

          <!-- Paso 2: completar -->
          <div class="border rounded p-3 mb-3 bg-light">
            <div class="d-flex align-items-start">
              <div class="me-3">
                <span class="badge bg-primary rounded-circle" style="width:32px;height:32px;font-size:14px;line-height:22px;">2</span>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-1">Completar con tus datos</h6>
                <p class="small text-muted mb-2">
                  Abre la plantilla en Excel o un editor de texto. Elimina las filas de ejemplo y el bloque de comentarios (lineas que empiezan con <code>#</code>) y agrega tus trabajadores.
                </p>
                <div class="small">
                  <strong>Obligatorios:</strong> <code>cedula</code>, <code>nombres</code>, <code>apellidos</code><br>
                  <strong>Opcionales:</strong> tipo_documento (default CC), email, telefono, fecha_ingreso (YYYY-MM-DD), id_cargo_cliente<br>
                  <strong>Regla:</strong> si la cedula ya existe para este cliente, el registro se <strong>actualiza</strong> (no duplica).
                </div>
              </div>
            </div>
          </div>

          <!-- Paso 3: subir -->
          <div class="border rounded p-3 bg-light">
            <div class="d-flex align-items-start">
              <div class="me-3">
                <span class="badge bg-primary rounded-circle" style="width:32px;height:32px;font-size:14px;line-height:22px;">3</span>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-1">Subir archivo</h6>
                <p class="small text-muted mb-2">
                  Selecciona el archivo CSV completado. Separador coma o punto-coma (deteccion automatica).
                </p>
                <input type="file" name="archivo" accept=".csv,text/csv" class="form-control" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-success" type="submit"><i class="bi bi-upload"></i> Subir e importar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const ID_CLIENTE = <?= $idCliente ?>;
const URL_BASE = '<?= base_url('perfiles-cargo') ?>';
const modalTrab = new bootstrap.Modal(document.getElementById('modal-trab'));

function limpiarForm() {
    document.getElementById('f-id').value = '';
    ['f-cedula','f-nombres','f-apellidos','f-email','f-telefono','f-fecha'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('f-tipo-doc').value = 'CC';
    document.getElementById('f-cargo').value = '';
    document.getElementById('modal-trab-titulo').textContent = 'Nuevo trabajador';
}

document.getElementById('btn-nuevo').addEventListener('click', () => { limpiarForm(); modalTrab.show(); });

document.querySelectorAll('.btn-editar').forEach(btn => btn.addEventListener('click', e => {
    const t = JSON.parse(e.target.closest('tr').dataset.trab);
    limpiarForm();
    document.getElementById('f-id').value = t.id_trabajador;
    document.getElementById('f-tipo-doc').value = t.tipo_documento || 'CC';
    document.getElementById('f-cedula').value = t.cedula || '';
    document.getElementById('f-cargo').value = t.id_cargo_cliente || '';
    document.getElementById('f-nombres').value = t.nombres || '';
    document.getElementById('f-apellidos').value = t.apellidos || '';
    document.getElementById('f-email').value = t.email || '';
    document.getElementById('f-telefono').value = t.telefono || '';
    document.getElementById('f-fecha').value = t.fecha_ingreso || '';
    document.getElementById('modal-trab-titulo').textContent = 'Editar trabajador';
    modalTrab.show();
}));

document.getElementById('btn-guardar-trab').addEventListener('click', async () => {
    const payload = {
        id_trabajador:    +document.getElementById('f-id').value || 0,
        tipo_documento:   document.getElementById('f-tipo-doc').value,
        cedula:           document.getElementById('f-cedula').value,
        id_cargo_cliente: document.getElementById('f-cargo').value,
        nombres:          document.getElementById('f-nombres').value,
        apellidos:        document.getElementById('f-apellidos').value,
        email:            document.getElementById('f-email').value,
        telefono:         document.getElementById('f-telefono').value,
        fecha_ingreso:    document.getElementById('f-fecha').value,
    };
    const r = await fetch(URL_BASE + '/' + ID_CLIENTE + '/trabajadores/guardar', {
        method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)
    });
    const j = await r.json();
    if (j.ok) location.reload();
    else alert('Error: ' + (j.error || 'desconocido'));
});

document.querySelectorAll('.btn-eliminar').forEach(btn => btn.addEventListener('click', async e => {
    if (!confirm('Eliminar este trabajador?')) return;
    const id = e.target.closest('button').dataset.id;
    const r = await fetch(URL_BASE + '/' + ID_CLIENTE + '/trabajadores/eliminar', {
        method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id_trabajador: +id})
    });
    const j = await r.json();
    if (j.ok) location.reload();
    else alert('Error: ' + (j.error || 'desconocido'));
}));
</script>
<?= $this->endSection() ?>
