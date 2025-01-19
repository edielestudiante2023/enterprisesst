<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lista de Accesos</title>
  <!-- Bootstrap 5 CSS desde CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <!-- DataTables CSS con Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" />
  <!-- DataTables Buttons CSS con Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css" />

  <!-- Bloque de estilos personalizados -->
  <style>
    /* Ajustes generales para la tabla */
    table.dataTable thead th {
      vertical-align: middle;
      text-align: center;
    }

    table.dataTable tbody td {
      vertical-align: middle;
    }

    table.dataTable tbody tr {
      height: 60px;
    }

    /* Estilos para los selectores de filtro en el pie de la tabla */
    tfoot select.form-select-sm {
      width: 100%;
      padding: 0.25rem;
      font-size: 0.875rem;
    }

    .dt-buttons .btn {
      margin-right: 0.5rem;
    }

    @media (max-width: 768px) {
      .card-header h1 {
        font-size: 1.25rem;
      }

      .dt-buttons .btn {
        margin-bottom: 0.5rem;
      }

      .btn-custom-add {
        background-color: #28a745;
        /* Verde personalizado */
        color: #fff;
      }

      .btn-custom-reset {
        background-color: #17a2b8;
        /* Azul claro personalizado */
        color: #fff;
      }

      .btn-outline-secondary {
        color: #adb5bd;
        /* Tono gris claro para el texto */
        border-color: #adb5bd;
        /* Borde gris claro */
      }

      .btn-outline-secondary:hover {
        color: #fff;
        /* Texto blanco al pasar el cursor */
        background-color: #adb5bd;
        /* Fondo gris claro al pasar el cursor */
        border-color: #adb5bd;
      }


    }
  </style>
</head>

<body>
  <div class="container mt-5">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h1 class="h4 mb-0">Lista de Accesos</h1>
        <div>
          <!-- Botón "Agregar Acceso" con color ajustado a 'success' (verde) -->
          <a href="<?= base_url('accesosseguncliente/add') ?>" class="btn btn-success me-2">Agregar Acceso</a>
          <!-- Botón "Restablecer Filtros" con color ajustado a 'info' (azul claro) -->
          <button id="clearState" class="btn btn-info">Restablecer Filtros</button>
        </div>
      </div>
      <div class="card-body">
        <form method="get" class="mb-3">
          <div class="row g-2">
            <div class="col-md-4">
              <input type="text" name="nombre" class="form-control" placeholder="Filtrar por Nombre" />
            </div>
            <div class="col-md-4">
              <input type="text" name="url" class="form-control" placeholder="Filtrar por URL" />
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
            </div>
          </div>
        </form>

        <div class="table-responsive">
          <table id="evaluacionesTable" class="table table-bordered table-striped">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>URL</th>
                <th>Dimensión</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>URL</th>
                <th>Dimensión</th>
                <th>Acciones</th>
              </tr>
            </tfoot>
            <tbody>
              <?php foreach ($accesos as $acceso): ?>
                <tr>
                  <td><?= htmlspecialchars($acceso['id_acceso']) ?></td>
                  <td><?= htmlspecialchars($acceso['nombre']) ?></td>
                  <td><?= htmlspecialchars($acceso['url']) ?></td>
                  <td><?= htmlspecialchars($acceso['dimension']) ?></td>
                  <td>
                    <!-- Botones de acción con colores ajustados -->
                    <a href="<?= base_url('accesosseguncliente/edit/' . $acceso['id_acceso']) ?>"
                      class="btn btn-warning btn-sm"
                      data-bs-toggle="tooltip"
                      title="Editar este acceso">Editar</a>
                    <a href="<?= base_url('accesosseguncliente/delete/' . htmlspecialchars($acceso['id_acceso'])) ?>"
                      class="btn btn-danger btn-sm btn-delete"
                      data-bs-toggle="tooltip"
                      title="Eliminar este acceso">Eliminar</a>

                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- jQuery 3.6.0 -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap 5 JS desde CDN -->
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
    $(document).ready(function() {
      var table = $('#evaluacionesTable').DataTable({
        stateSave: true,
        order: [
          [1, 'asc'],
          [2, 'asc']
        ],
        buttons: [{
            extend: 'colvis',
            text: 'Columnas',
            className: ''
          },
          {
            extend: 'excelHtml5',
            text: 'Exportar a Excel',
            className: '',
            titleAttr: 'Exportar tabla a Excel'
          }
        ],
        dom: "<'row mb-3'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        autoWidth: false,
        responsive: true,
        drawCallback: function() {
          var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
          tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
          });
        }
      });

      table.buttons().container().appendTo('#evaluacionesTable_wrapper .col-md-6:eq(0)');

      $('#evaluacionesTable tfoot th').each(function() {
        var title = $(this).text();
        if (title !== "Acciones") {
          $(this).html('<select class="form-select form-select-sm"><option value="">' + title + '</option></select>');
        } else {
          $(this).html('');
        }
      });

      table.columns().every(function() {
        var column = this;
        var select = $('select', column.footer());
        column.data().unique().sort().each(function(d) {
          if (d) {
            select.append('<option value="' + d + '">' + d + '</option>');
          }
        });
        select.on('change', function() {
          var val = $.fn.dataTable.util.escapeRegex($(this).val());
          column.search(val ? '^' + val + '$' : '', true, false).draw();
        });
      });

      $('#clearState').on('click', function() {
        table.search('');
        table.columns().every(function() {
          this.search('');
        });
        $('#evaluacionesTable tfoot select').val('');
        table.draw();
      });

      $('.btn-delete').on('click', function(e) {
        e.preventDefault(); // Prevenir la acción por defecto del enlace
        var href = $(this).attr('href'); // Obtener la URL del atributo href
        if (confirm('¿Estás seguro de que deseas eliminar este acceso?')) {
          window.location.href = href; // Redirigir a la URL de eliminación
        }
      });

    });
  </script>
</body>

</html>