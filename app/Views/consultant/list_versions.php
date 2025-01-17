<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8"> 
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Versiones del Documento</title>

  <!-- CSS de Bootstrap y DataTables -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
  <!-- CSS para Buttons -->
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">

  <!-- jQuery, DataTables y extensiones de Buttons -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
  <!-- Scripts para Buttons y exportación a Excel -->
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

  <style>
    /* Configuración de la tabla */
    #documentTable {
      table-layout: fixed; /* Distribución fija para controlar los anchos de columna */
      width: 100%;
    }

    /* Fijar la altura de cada fila de la tabla */
    #documentTable tbody tr {
      height: 50px; /* Ajusta este valor según la altura deseada */
    }

    /* Evitar que el texto se ajuste a varias líneas y agregar puntos suspensivos si es demasiado largo */
    #documentTable th,
    #documentTable td {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      vertical-align: middle;
      padding: 8px;
      border: 1px solid #dee2e6;
    }

    /* Definir anchos razonables para cada columna usando nth-child */
    #documentTable th:nth-child(1),
    #documentTable td:nth-child(1) {
      width: 12%;
    }
    #documentTable th:nth-child(2),
    #documentTable td:nth-child(2) {
      width: 15%;
    }
    #documentTable th:nth-child(3),
    #documentTable td:nth-child(3) {
      width: 10%;
    }
    #documentTable th:nth-child(4),
    #documentTable td:nth-child(4) {
      width: 8%;
    }
    #documentTable th:nth-child(5),
    #documentTable td:nth-child(5) {
      width: 8%;
    }
    #documentTable th:nth-child(6),
    #documentTable td:nth-child(6) {
      width: 12%;
    }
    #documentTable th:nth-child(7),
    #documentTable td:nth-child(7) {
      width: 8%;
    }
    #documentTable th:nth-child(8),
    #documentTable td:nth-child(8) {
      width: 10%;
    }
    #documentTable th:nth-child(9),
    #documentTable td:nth-child(9) {
      width: 12%;
    }
    #documentTable th:nth-child(10),
    #documentTable td:nth-child(10) {
      width: 15%;
    }

    /* Opcional: Ajustar selectores en el pie de la tabla si es necesario */
    #documentTable tfoot select {
      width: 100%;
      box-sizing: border-box;
    }
  </style>
</head>

<body class="bg-light text-dark">

  <!-- Navbar fija -->
  <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">

      <!-- Logo izquierdo -->
      <div>
        <a href="https://dashboard.cycloidtalent.com/login">
          <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;">
        </a>
      </div>

      <!-- Logo centro -->
      <div>
        <a href="https://cycloidtalent.com/index.php/consultoria-sst">
          <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;">
        </a>
      </div>

      <!-- Logo derecho -->
      <div>
        <a href="https://cycloidtalent.com/">
          <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;">
        </a>
      </div>

    </div>

    <!-- Fila de botones -->
    <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 10px auto 0; padding: 0 20px;">
      <!-- Botón izquierdo -->
      <div style="text-align: center;">
        <h2 style="margin: 0; font-size: 16px;">Ir a Dashboard</h2>
        <a href="<?= base_url('/dashboardconsultant') ?>" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;">Ir a DashBoard</a>
      </div>

      <button id="clearState" class="btn btn-danger btn-sm">Restablecer Filtros</button>

      <!-- Botón derecho -->
      <div style="text-align: center;">
        <h2 style="margin: 0; font-size: 16px;">Añadir Registro</h2>
        <a href="<?= base_url('/addVersion') ?>" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;" target="_blank">Añadir Registro</a>
      </div>
    </div>
  </nav>

  <!-- Espaciado para el navbar fijo -->
  <div style="height: 160px;"></div>

  <!-- Contenido principal -->
  <div class="container mt-5">
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
          <?php foreach ($versions as $version): ?>
            <tr>
              <td><?= $version['nombre_cliente'] ?></td>
              <td><?= $version['type_name'] ?></td>
              <td><?= $version['document_type'] ?></td>
              <td><?= $version['acronym'] ?></td>
              <td><?= $version['version_number'] ?></td>
              <td><?= $version['location'] ?></td>
              <td><?= $version['status'] ?></td>
              <td><?= $version['change_control'] ?></td>
              <td><?= $version['created_at'] ?></td>
              <td>
                <a href="<?= base_url('editVersion/' . $version['id']) ?>" class="btn btn-outline-primary btn-sm me-2">Editar</a>
                <a href="<?= base_url('deleteVersion/' . $version['id']) ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta versión?');">Eliminar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
    <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS - Todos los derechos reservados © 2024</p>
  </footer>

  <!-- Script de DataTables con filtros, exportación a Excel y botón Restablecer Filtros -->
  <script>
    $(document).ready(function () {
      // Inicializar DataTable con persistencia, botones y filtros
      var table = $('#documentTable').DataTable({
        stateSave: true,
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
        },
        // Incluir botones en la interfaz
        dom: 'Bfrtip',
        buttons: [
          {
            extend: 'excelHtml5',
            text: 'Exportar a Excel',
            titleAttr: 'Exportar a Excel'
          }
        ],
        initComplete: function () {
          // Añadir filtros en el pie de tabla
          this.api().columns().every(function () {
            var column = this;
            var select = $('<select class="form-select form-select-sm"><option value="">Todos</option></select>')
              .appendTo($(column.footer()).empty())
              .on('change', function () {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                column.search(val ? '^' + val + '$' : '', true, false).draw();
              });

            // Rellenar el select con valores únicos
            column.data().unique().sort().each(function (d, j) {
              select.append('<option value="' + d + '">' + d + '</option>');
            });
          });
        }
      });

      // Botón para borrar el estado y restablecer filtros
      $('#clearState').on('click', function () {
        // Borrar estado guardado en localStorage
        localStorage.removeItem('DataTables_documentTable_/');
        table.state.clear(); // Limpiar estado en DataTables
        location.reload(); // Recargar la página para restablecer filtros
      });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
