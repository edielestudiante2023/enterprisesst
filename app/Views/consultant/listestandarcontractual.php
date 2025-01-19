<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lista de Estándares</title>
  <!-- Carga de Bootstrap desde un CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- DataTables CSS con Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
</head>
<body>
  <div class="container mt-5">
    <h1>Lista de Estándares</h1>
    <!-- Enlace para agregar un nuevo estándar -->
    <a href="<?= base_url('estandarcontractual/add') ?>" class="btn btn-primary mb-3">Agregar Estándar</a>
    <!-- Botón para restablecer filtros -->
    <button id="clearState" class="btn btn-secondary mb-3">Restablecer Filtros</button>

    <form method="get" class="mb-3">
      <div class="row">
        <div class="col-md-6">
          <input type="text" name="nombre" class="form-control" placeholder="Filtrar por Nombre">
        </div>
        <div class="col-md-6">
          <button type="submit" class="btn btn-secondary">Filtrar</button>
        </div>
      </div>
    </form>

    <table id="estandaresTable" class="table table-bordered">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Acciones</th>
        </tr>
      </tfoot>
      <tbody>
        <?php foreach ($estandares as $estandar): ?>
          <tr>
            <td><?= esc($estandar['id_estandar']) ?></td>
            <td><?= esc($estandar['nombre']) ?></td>
            <td>
              <a href="<?= base_url('estandarcontractual/edit/' . $estandar['id_estandar']) ?>" class="btn btn-warning btn-sm">Editar</a>
              <a href="<?= base_url('estandarcontractual/delete/' . $estandar['id_estandar']) ?>" class="btn btn-danger btn-sm">Eliminar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Carga de dependencias JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>

  <script>
    $(document).ready(function () {
      // Inicialización de DataTable
      var table = $('#estandaresTable').DataTable({
        stateSave: true,
        responsive: true,
        order: [[1, 'asc']],  // Ordenar inicialmente por la columna "Nombre"
      });

      // Configurar selectores de filtro en el pie de la tabla
      $('#estandaresTable tfoot th').each(function () {
        var title = $(this).text();
        if (title !== 'Acciones') {
          $(this).html('<select class="form-select form-select-sm"><option value="">' + title + '</option></select>');
        } else {
          $(this).empty();
        }
      });

      // Aplicar filtros a cada columna usando los selects del pie
      table.columns().every(function () {
        var column = this;
        var select = $('select', column.footer());
        if (select.length) {
          column.data().unique().sort().each(function (d) {
            if (d) {
              select.append('<option value="' + d + '">' + d + '</option>');
            }
          });

          select.on('change', function () {
            var val = $.fn.dataTable.util.escapeRegex($(this).val());
            column.search(val ? '^' + val + '$' : '', true, false).draw();
          });
        }
      });

      // Botón para restablecer los filtros sin recargar la página
      $('#clearState').on('click', function () {
        // Limpiar la búsqueda global
        table.search('');
        // Limpiar la búsqueda por cada columna
        table.columns().every(function () {
          this.search('');
        });
        // Restaurar los selectores de filtro a su valor predeterminado
        $('#estandaresTable tfoot select').val('');
        // Aplicar los cambios en la tabla
        table.draw();
      });
    });
  </script>
</body>
</html>

