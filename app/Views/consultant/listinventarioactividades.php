<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Listado de Inventario de Actividades</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-5">
    <h1 class="mb-4">Inventario de Actividades</h1>
    <a href="<?= base_url('/addinventarioactividades') ?>" class="btn btn-primary mb-3">Añadir Actividad</a>
    <!-- Botón para restablecer filtros -->
    <button id="clearState" class="btn btn-secondary mb-3">Restablecer Filtros</button>
    <!-- Botón para exportar seleccionados a Excel -->
    <button id="exportSelected" class="btn btn-success mb-3">Exportar Seleccionados a Excel</button>
    <table id="inventarioTable" class="table table-striped table-bordered nowrap" style="width:100%">
      <thead>
        <tr>
          <th><input type="checkbox" id="selectAll"></th>
          <th>ID</th>
          <th>PHVA</th>
          <th>Numeral</th>
          <th>Actividad</th>
          <th>Responsable Sugerido</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th></th>
          <th>ID</th>
          <th>PHVA</th>
          <th>Numeral</th>
          <th>Actividad</th>
          <th>Responsable Sugerido</th>
          <th></th>
        </tr>
      </tfoot>
      <tbody>
        <?php foreach ($actividades as $actividad): ?>
          <tr>
            <td><input type="checkbox" class="row-select" value="<?= $actividad->id_inventario_actividades_plandetrabajo ?>"></td>
            <td data-bs-toggle="tooltip" title="ID: <?= $actividad->id_inventario_actividades_plandetrabajo ?>"><?= $actividad->id_inventario_actividades_plandetrabajo ?></td>
            <td><?= $actividad->phva_plandetrabajo ?></td>
            <td><?= $actividad->numeral_plandetrabajo ?></td>
            <td><?= $actividad->actividad_plandetrabajo ?></td>
            <td><?= $actividad->responsable_sugerido_plandetrabajo ?? 'N/A' ?></td>
            <td>
              <a href="<?= base_url('/editinventarioactividades/' . $actividad->id_inventario_actividades_plandetrabajo) ?>" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Editar">Editar</a>
              <a href="<?= base_url('/deleteinventarioactividades/' . $actividad->id_inventario_actividades_plandetrabajo) ?>" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta actividad?');">Eliminar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Inclusión de scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>

  <script>
    $(document).ready(function () {
      const selectedRows = new Set();

      // Inicialización de DataTable con botones, persistencia, filtros y responsividad
      const table = $('#inventarioTable').DataTable({
        stateSave: true,            // Persistencia de estado (filtros, paginación, etc.)
        responsive: true,           // Tabla responsiva
        dom: 'Bfrtip',              // Definición de elementos: Botones, filtro, tabla, etc.
        buttons: [
          {
            extend: 'excelHtml5',    // Botón para exportar todo a Excel
            text: 'Exportar Todo a Excel',
            className: 'btn btn-success',
            exportOptions: {
              columns: ':not(:first-child):not(:last-child)', // Excluir columnas de checkbox y acciones
              modifier: { page: 'all' }
            }
          },
          {
            extend: 'colvis',        // Botón para controlar la visibilidad de columnas
            text: 'Visibilidad de Columnas',
            className: 'btn btn-info'
          }
        ],
        initComplete: function () {
          // Creación de filtros desplegables en el <tfoot>
          this.api().columns().every(function () {
            const column = this;
            const select = $('<select class="form-select form-select-sm mb-2"><option value="">Filtrar</option></select>')
              .appendTo($(column.footer()).empty())
              .on('change', function () {
                const val = $.fn.dataTable.util.escapeRegex($(this).val());
                column.search(val ? '^' + val + '$' : '', true, false).draw();
              });

            column.data().unique().sort().each(function (d) {
              // Limpia el HTML y obtiene solo texto para evitar duplicados con tags
              const text = $('<div>').html(d).text();
              if (text) {
                select.append('<option value="' + text + '">' + text + '</option>');
              }
            });
          });
        },
        drawCallback: function () {
          // Re-inicializar tooltips tras cada redibujado
          $('[data-bs-toggle="tooltip"]').tooltip();
        }
      });

      // Manejador para el checkbox "Seleccionar Todo"
      $('#selectAll').on('click', function () {
        const isChecked = $(this).is(':checked');
        $('.row-select').prop('checked', isChecked);
        table.rows().every(function () {
          const rowData = this.data();
          const rowId = rowData[1]; // Suponiendo que la columna 1 es el ID
          if (isChecked) {
            selectedRows.add(rowId);
          } else {
            selectedRows.delete(rowId);
          }
        });
      });

      // Manejador para selección individual de filas
      $(document).on('change', '.row-select', function () {
        const rowId = $(this).val();
        if ($(this).is(':checked')) {
          selectedRows.add(rowId);
        } else {
          selectedRows.delete(rowId);
        }

        // Actualizar el estado del checkbox "selectAll"
        const allChecked = $('.row-select').length === $('.row-select:checked').length;
        $('#selectAll').prop('checked', allChecked);
      });

      // Mantener el estado de los checkboxes al cambiar de página
      table.on('draw', function () {
        $('.row-select').each(function () {
          const rowId = $(this).val();
          if (selectedRows.has(rowId)) {
            $(this).prop('checked', true);
          } else {
            $(this).prop('checked', false);
          }
        });

        const allChecked = $('.row-select').length === $('.row-select:checked').length;
        $('#selectAll').prop('checked', allChecked);
      });

      // Exportar filas seleccionadas a Excel
      $('#exportSelected').on('click', function () {
        const rowsToExport = [];
        table.rows().every(function () {
          const rowData = this.data();
          const rowId = rowData[1]; // Suponiendo que la columna 1 es el ID
          if (selectedRows.has(rowId)) {
            rowsToExport.push({
              ID: rowData[1],
              PHVA: rowData[2],
              Numeral: rowData[3],
              Actividad: rowData[4],
              Responsable: rowData[5]
            });
          }
        });

        if (rowsToExport.length === 0) {
          alert('No hay filas seleccionadas para exportar.');
          return;
        }

        const worksheet = XLSX.utils.json_to_sheet(rowsToExport);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Seleccionados');
        XLSX.writeFile(workbook, 'seleccion_actividades.xlsx');
      });

      // Botón para restablecer filtros y estado
      $('#clearState').on('click', function () {
        // Elimina el estado almacenado y recarga la página
        localStorage.removeItem('DataTables_inventarioTable_/');
        table.state.clear();
        location.reload();
      });

      // Inicialización inicial de tooltips
      $('[data-bs-toggle="tooltip"]').tooltip();
    });
  </script>
</body>

</html>
