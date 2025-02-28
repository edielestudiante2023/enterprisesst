<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"> 
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Versiones del Documento</title>

  <!-- CSS de Bootstrap, DataTables y Select2 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

  <!-- jQuery, DataTables, Select2 y extensiones -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <style>
    /* Estilos de la tabla */
    #documentTable {
      width: auto;
      margin: 0;
      padding: 0;
    }

    .table-responsive {
      padding: 20px;
      margin: 0;
      overflow-x: auto;
      width: 100%;
      max-width: 100%;
    }

    .container-fluid {
      max-width: 100%;
      padding: 0 20px;
      overflow-x: hidden;
    }

    .table-container {
      max-width: 100%;
      overflow-x: auto;
      padding: 0 20px;
    }

    #documentTable tbody tr {
      height: 50px;
    }

    #documentTable th,
    #documentTable td {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      vertical-align: middle;
      padding: 8px;
      border: 1px solid #dee2e6;
    }

    /* Anchos de columna ajustados */
    #documentTable th:nth-child(1),
    #documentTable td:nth-child(1) { min-width: 200px; }  /* Nombre del Cliente */
    #documentTable th:nth-child(2),
    #documentTable td:nth-child(2) { min-width: 200px; }  /* Nombre del Documento */
    #documentTable th:nth-child(3),
    #documentTable td:nth-child(3) { min-width: 150px; }  /* Tipo de Documento */
    #documentTable th:nth-child(4),
    #documentTable td:nth-child(4) { min-width: 100px; }  /* Acrónimo */
    #documentTable th:nth-child(5),
    #documentTable td:nth-child(5) { min-width: 100px; }  /* Número de Versión */
    #documentTable th:nth-child(6),
    #documentTable td:nth-child(6) { min-width: 150px; }  /* Ubicación */
    #documentTable th:nth-child(7),
    #documentTable td:nth-child(7) { min-width: 100px; }  /* Estado */
    #documentTable th:nth-child(8),
    #documentTable td:nth-child(8) { min-width: 150px; }  /* Control de Cambios */
    #documentTable th:nth-child(9),
    #documentTable td:nth-child(9) { min-width: 120px; }  /* Fecha de Creación */
    #documentTable th:nth-child(10),
    #documentTable td:nth-child(10) { min-width: 150px; } /* Acciones */

    /* Nuevo estilo para el contenedor del select2 */
    .client-selector-container {
      max-width: 500px;
      margin: 20px auto;
      padding: 15px;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Ocultar tabla inicialmente */
    .table-container {
      display: none;
    }
  </style>
</head>

<body class="bg-light text-dark">
  <!-- Navbar mantenido igual -->
  <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; padding: 0 20px;">
      <div>
        <a href="https://dashboard.cycloidtalent.com/login">
          <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;">
        </a>
      </div>
      <div>
        <a href="https://cycloidtalent.com/index.php/consultoria-sst">
          <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;">
        </a>
      </div>
      <div>
        <a href="https://cycloidtalent.com/">
          <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;">
        </a>
      </div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin: 10px 0 0; padding: 0 20px;">
      <div style="text-align: center;">
        <h2 style="margin: 0; font-size: 16px;">Ir a Dashboard</h2>
        <a href="<?= base_url('/dashboardconsultant') ?>" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;">Ir a DashBoard</a>
      </div>

      <button id="clearState" class="btn btn-danger btn-sm">Restablecer Filtros</button>

      <div style="text-align: center;">
        <h2 style="margin: 0; font-size: 16px;">Añadir Registro</h2>
        <a href="<?= base_url('/addVersion') ?>" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;" target="_blank">Añadir Registro</a>
      </div>
    </div>
  </nav>

  <!-- Espaciado para el navbar fijo -->
  <div style="height: 160px;"></div>

  <!-- Selector de Cliente -->
  <div class="container-fluid mt-5 px-0">
    <div class="client-selector-container mx-auto">
      <h5 class="text-center mb-3">Seleccione un Cliente</h5>
      <select id="clientSelector" class="form-select" style="width: 100%;">
        <option value="">Seleccione un cliente</option>
        <?php foreach ($clients as $client): ?>
          <option value="<?= $client['id_cliente'] ?>"><?= $client['nombre_cliente'] ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Contenedor de la tabla (inicialmente oculto) -->
    <div class="table-container px-0">
      <h1 class="text-center mb-4">Versiones del Documento</h1>
      <div class="table-responsive">
        <table id="documentTable" class="table table-bordered table-hover">
          <thead class="table-light">
            <tr>
              <th>Nombre del Cliente</th>
              <th>Nombre del Documento</th>
              <th>Tipo de Documento</th>
              <th>Acrónimo</th>
              <th>Número de Versión</th>
              <th>Ubicación</th>
              <th>Estado</th>
              <th>Control de Cambios</th>
              <th>Fecha de Creación</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th>Cliente</th>
              <th>Documento</th>
              <th>Tipo</th>
              <th>Acrónimo</th>
              <th>Versión</th>
              <th>Ubicación</th>
              <th>Estado</th>
              <th>Control</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </tfoot>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
    <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS - Todos los derechos reservados © 2024</p>
  </footer>

  <script>
    $(document).ready(function () {
      // Initialize Select2
      $('#clientSelector').select2({
        theme: 'bootstrap-5',
        placeholder: 'Seleccione un cliente',
        allowClear: true
      });

      // Initialize DataTable with empty data
      var table = $('#documentTable').DataTable({
        data: [],
        scrollX: true,
        autoWidth: false,
        stateSave: false,
        deferRender: true,
        processing: true,
        columns: [
          { data: 'nombre_cliente' },
          { data: 'type_name' },
          { data: 'document_type' },
          { data: 'acronym' },
          { data: 'version_number' },
          { data: 'location' },
          { data: 'status' },
          { data: 'change_control' },
          { data: 'created_at' },
          {
            data: 'id',
            render: function(data, type, row) {
              return `
                <a href="<?= base_url('editVersion/') ?>${data}" class="btn btn-outline-primary btn-sm me-2">Editar</a>
                <a href="<?= base_url('deleteVersion/') ?>${data}" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta versión?');">Eliminar</a>
              `;
            }
          }
        ],
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
        },
        dom: 'Bfrtip',
        buttons: [
          {
            extend: 'excelHtml5',
            text: 'Exportar a Excel',
            titleAttr: 'Exportar a Excel'
          }
        ],
        initComplete: function () {
          this.api().columns().every(function () {
            var column = this;
            var select = $('<select class="form-select form-select-sm"><option value="">Todos</option></select>')
              .appendTo($(column.footer()).empty())
              .on('change', function () {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                column.search(val ? '^' + val + '$' : '', true, false).draw();
              });

            column.data().unique().sort().each(function (d, j) {
              select.append('<option value="' + d + '">' + d + '</option>');
            });
          });
        }
      });

      // Hide table container initially
      $('.table-container').hide();

      // Handle client selection
      $('#clientSelector').on('change', function() {
        var selectedClient = $(this).val();
        
        if (selectedClient) {
          // Show table container when a client is selected
          $('.table-container').show();
          
          // Load data for selected client
          $.ajax({
            url: '<?= base_url('getVersionsByClient/') ?>' + selectedClient,
            method: 'GET',
            success: function(data) {
              table.clear().rows.add(data).draw();
            },
            error: function(xhr, status, error) {
              console.error('Error loading versions:', error);
            }
          });
        } else {
          // Hide table and clear data when no client is selected
          $('.table-container').hide();
          table.clear().draw();
        }
      });

      // Handle clear state button
      $('#clearState').on('click', function () {
        $('#clientSelector').val('').trigger('change');
        table.clear().draw();
        $('.table-container').hide();
      });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
