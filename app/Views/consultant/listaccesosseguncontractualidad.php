<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lista de Accesos según Contractualidad</title>
  <!-- Bootstrap CSS desde CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- DataTables y botones CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css">
</head>
<body>
  <div class="container mt-5">
    <h1>Lista de Accesos según Contractualidad</h1>
    <a href="<?= base_url('accesosseguncontractualidad/add') ?>" class="btn btn-primary mb-3">Agregar Relación</a>
    <button id="clearState" class="btn btn-secondary mb-3">Restablecer Filtros</button>
    
    <table id="evaluacionesTable" class="table table-bordered table-responsive">
      <thead>
        <tr>
          <th>ID</th>
          <th>Estándar</th>
          <th>Acceso</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th>
            <select class="form-select"><option value="">Todos</option></select>
          </th>
          <th>
            <select class="form-select"><option value="">Todos</option></select>
          </th>
          <th>
            <select class="form-select"><option value="">Todos</option></select>
          </th>
          <th></th>
        </tr>
      </tfoot>
      <tbody>
        <?php foreach ($estandares_accesos as $item): ?>
          <tr>
            <td><?= esc($item['id']) ?></td>
            <td><?= esc($item['estandar']) ?></td>
            <td><?= esc($item['acceso']) ?></td>
            <td>
              <a href="<?= base_url('accesosseguncontractualidad/edit/' . $item['id']) ?>" 
                 class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Editar">Editar</a>
              <a href="<?= base_url('accesosseguncontractualidad/delete/' . $item['id']) ?>" 
                 class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Eliminar">Eliminar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- jQuery desde CDN -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap Bundle JS desde CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- DataTables y extensiones JS -->
  <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.min.js"></script>

  <script>
    $(document).ready(function () {
      var table = $('#evaluacionesTable').DataTable({
        stateSave: true,
        responsive: true,
        order: [[1, 'asc'], [2, 'asc']],  // Ordenar por Estándar y luego por Acceso
        dom: 'Bfrtip',
        buttons: [
          {
            extend: 'colvis',
            text: 'Columnas',
            className: 'btn btn-success'
          },
          {
            extend: 'excelHtml5',
            text: 'Exportar a Excel',
            className: 'btn btn-primary'
          }
        ],
        initComplete: function () {
          this.api().columns().every(function () {
            var column = this;
            var select = $('select', column.footer());
            if (select.length) {
              column.data().unique().sort().each(function (d) {
                if(d) {
                  select.append('<option value="' + d + '">' + d + '</option>');
                }
              });
              select.on('change', function () {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                column.search(val ? '^' + val + '$' : '', true, false).draw();
              });
            }
          });
        }
      });

      table.on('draw.dt', function () {
        $('[data-bs-toggle="tooltip"]').tooltip();
      });

      $('[data-bs-toggle="tooltip"]').tooltip();

      $('#clearState').on('click', function () {
        // Elimina el estado guardado y recarga la página
        localStorage.removeItem('DataTables_evaluacionesTable_<?= current_url() ?>');
        table.state.clear();
        location.reload();
      });
    });
  </script>
</body>
</html>
