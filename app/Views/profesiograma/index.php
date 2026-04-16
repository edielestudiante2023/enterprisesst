<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($titulo) ?> - <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
:root{--primary-dark:#1c2437;--secondary-dark:#2c3e50;--gold-primary:#bd9751;--gold-secondary:#d4af37;--gradient-bg:linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%);}
body{background:var(--gradient-bg);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;}
.page-header{background:linear-gradient(135deg,var(--primary-dark),var(--secondary-dark),var(--gold-primary));color:#fff;padding:25px 30px;border-radius:15px;margin-bottom:20px;box-shadow:0 10px 30px rgba(0,0,0,.2);}
.page-header h1{font-size:1.6rem;margin:0 0 4px;font-weight:700;}
.page-header .subtitulo{opacity:.85;font-size:.95rem;}
.card-main{background:#fff;border:none;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.08);}
.btn-gold{background:linear-gradient(135deg,var(--gold-primary),var(--gold-secondary));color:#fff;border:none;font-weight:600;}
.btn-gold:hover{filter:brightness(1.1);color:#fff;}
.btn-outline-gold{border:2px solid var(--gold-primary);color:var(--gold-primary);font-weight:600;background:transparent;}
.btn-outline-gold:hover{background:var(--gold-primary);color:#fff;}
.prof-table{font-size:.92rem;}
.prof-table thead{background:var(--primary-dark);color:#fff;}
.prof-table thead th{font-weight:600;padding:12px 10px;white-space:nowrap;}
.prof-table tbody td{vertical-align:middle;padding:10px;}
.badge-momento{font-size:.8rem;padding:5px 10px;border-radius:12px;font-weight:600;}
.badge-ingreso{background:#dbeafe;color:#1e40af;}
.badge-periodico{background:#fef3c7;color:#92400e;}
.badge-retiro{background:#fce7f3;color:#9d174d;}
.badge-cambio{background:#e0e7ff;color:#3730a3;}
.stat-card{background:#fff;border-radius:12px;padding:20px;text-align:center;box-shadow:0 2px 10px rgba(0,0,0,.06);}
.stat-card .stat-number{font-size:2rem;font-weight:700;color:var(--primary-dark);}
.stat-card .stat-label{font-size:.85rem;color:#888;margin-top:4px;}
.empty-state{text-align:center;padding:60px 20px;color:#888;}
.empty-state .big-icon{font-size:5rem;color:#d1d5db;margin-bottom:20px;}
.ipevr-badge{background:var(--gold-primary);color:#fff;padding:4px 10px;border-radius:6px;font-weight:700;font-size:.85rem;}
.callout-gtc{background:#fffbeb;border-left:4px solid var(--gold-primary);padding:12px 16px;border-radius:0 8px 8px 0;margin-bottom:20px;font-size:.9rem;}
.callout-gtc i{color:var(--gold-primary);margin-right:6px;}
</style>
</head>
<body>
<div class="container-fluid py-4">
  <!-- HEADER -->
  <div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h1><i class="fa-solid fa-stethoscope me-2"></i><?= esc($titulo) ?></h1>
      <div class="subtitulo"><?= esc($cliente['nombre_cliente']) ?> (ID <?= (int)$cliente['id_cliente'] ?>)</div>
    </div>
    <div class="d-flex gap-2 mt-2 mt-md-0">
      <a href="<?= base_url('ipevr/cliente/' . $cliente['id_cliente']) ?>" class="btn btn-outline-gold btn-sm" target="_blank">
        <i class="fa-solid fa-shield-halved me-1"></i> Ver IPEVR
      </a>
      <a href="<?= base_url('profesiograma/exportar/xlsx/' . $cliente['id_cliente']) ?>" class="btn btn-success btn-sm">
        <i class="fa-solid fa-file-excel me-1"></i> Descargar Excel
      </a>
      <button class="btn btn-gold btn-sm" id="btnGenerarIpevr" onclick="generarDesdeIpevr()">
        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Generar desde IPEVR
      </button>
    </div>
  </div>

  <!-- CALLOUT -->
  <div class="callout-gtc">
    <i class="fa-solid fa-book-medical"></i>
    <strong>Resolucion 2346/2007:</strong> Las evaluaciones medicas ocupacionales (ingreso, periodicas, retiro)
    deben realizarse segun los factores de riesgo identificados en la Matriz IPEVR.
    <span class="ipevr-badge ms-2">IPEVR: <?= esc($ipevr['nombre'] ?? 'Vigente') ?></span>
  </div>

  <!-- STATS -->
  <div class="row mb-4 g-3">
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-number"><?= count($cargos) ?></div>
        <div class="stat-label">Cargos registrados</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-number"><?= count(array_filter($resumen, fn($r) => $r['total'] > 0)) ?></div>
        <div class="stat-label">Cargos con examenes</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-number"><?= array_sum(array_column($resumen, 'total')) ?></div>
        <div class="stat-label">Total asignaciones</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-number"><?= array_sum(array_column($resumen, 'ingreso')) ?></div>
        <div class="stat-label">Examenes ingreso</div>
      </div>
    </div>
  </div>

  <!-- TABLA PRINCIPAL -->
  <div class="card card-main">
    <div class="card-body p-3">
      <?php if (empty($cargos)): ?>
        <div class="empty-state">
          <div class="big-icon"><i class="fa-solid fa-users-slash"></i></div>
          <h4>No hay cargos registrados</h4>
          <p>Primero registre los cargos del cliente en Maestros del Cliente.</p>
          <a href="<?= base_url('maestros-cliente/' . $cliente['id_cliente']) ?>" class="btn btn-gold mt-3">
            <i class="fa-solid fa-database me-1"></i> Ir a Maestros
          </a>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table prof-table mb-0" id="tablaProfesiograma">
            <thead>
              <tr>
                <th>#</th>
                <th>Cargo</th>
                <th class="text-center">Ocupantes</th>
                <th class="text-center"><span class="badge-momento badge-ingreso">Ingreso</span></th>
                <th class="text-center"><span class="badge-momento badge-periodico">Periodico</span></th>
                <th class="text-center"><span class="badge-momento badge-retiro">Retiro</span></th>
                <th class="text-center">Total</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cargos as $i => $cargo): ?>
                <?php
                  $idC = (int)$cargo['id'];
                  $r = $resumen[$idC] ?? ['ingreso'=>0,'periodico'=>0,'retiro'=>0,'total'=>0];
                ?>
                <tr>
                  <td><?= $i + 1 ?></td>
                  <td><strong><?= esc($cargo['nombre_cargo']) ?></strong></td>
                  <td class="text-center"><?= (int)($cargo['num_ocupantes'] ?? 0) ?></td>
                  <td class="text-center">
                    <?php if ($r['ingreso'] > 0): ?>
                      <span class="badge badge-ingreso"><?= $r['ingreso'] ?></span>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <?php if ($r['periodico'] > 0): ?>
                      <span class="badge badge-periodico"><?= $r['periodico'] ?></span>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <?php if ($r['retiro'] > 0): ?>
                      <span class="badge badge-retiro"><?= $r['retiro'] ?></span>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <strong><?= $r['total'] ?></strong>
                  </td>
                  <td class="text-center">
                    <a href="<?= base_url("profesiograma/cliente/{$cliente['id_cliente']}/cargo/{$idC}") ?>"
                       class="btn btn-sm btn-outline-gold">
                      <i class="fa-solid fa-pen-to-square me-1"></i> Editar
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
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function() {
    if ($('#tablaProfesiograma tbody tr').length > 0) {
        $('#tablaProfesiograma').DataTable({
            responsive: true,
            ordering: true,
            paging: true,
            pageLength: 25,
            language: {
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ cargos",
                paginate: { previous: "Anterior", next: "Siguiente" },
                emptyTable: "No hay cargos registrados",
                zeroRecords: "No se encontraron resultados"
            }
        });
    }
});

function generarDesdeIpevr() {
    Swal.fire({
        title: 'Generar Profesiograma desde IPEVR',
        html: `
            <p style="font-size:.95rem;text-align:left;">
                Se cruzaran los peligros identificados en la Matriz IPEVR con el catalogo de examenes medicos.
                <br><br>
                <strong>Proceso:</strong>
                <ol style="text-align:left;font-size:.9rem;">
                    <li>Leer cargos y clasificaciones de peligro de la IPEVR</li>
                    <li>Cruzar contra catalogo de examenes medicos</li>
                    <li>Asignar examenes por cargo (ingreso + periodico + retiro)</li>
                </ol>
                <small class="text-muted">Los examenes ya asignados no se duplicaran.</small>
            </p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-wand-magic-sparkles me-1"></i> Generar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#bd9751',
    }).then((result) => {
        if (!result.isConfirmed) return;

        const btn = document.getElementById('btnGenerarIpevr');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Generando...';

        fetch('<?= base_url("profesiograma/generar-ipevr/" . $cliente['id_cliente']) ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles me-1"></i> Generar desde IPEVR';

            if (data.ok) {
                Swal.fire({
                    title: 'Profesiograma generado',
                    html: `
                        <div style="text-align:left;font-size:.95rem;">
                            <p><strong>${data.cargos}</strong> cargos procesados</p>
                            <p><strong>${data.insertados}</strong> examenes asignados</p>
                            <p><strong>${data.duplicados}</strong> ya existian (no duplicados)</p>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonColor: '#bd9751',
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', data.error || 'Error al generar', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles me-1"></i> Generar desde IPEVR';
            Swal.fire('Error', 'Error de conexion: ' + err.message, 'error');
        });
    });
}
</script>
</body>
</html>
