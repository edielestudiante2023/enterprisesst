<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
  <!-- DataTables Buttons CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <!-- DataTables Buttons JS y dependencias -->
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

  <title>Listado de Capacitaciones</title>
  <style>
    body {
      background-color: #f9f9f9;
      color: #333;
      font-family: Arial, sans-serif;
    }

    table {
      background-color: #fff;
    }

    th,
    td {
      text-align: left;
    }
  </style>
</head>

<body>

  <!-- Navbar -->
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

      <!-- Botón derecho -->
      <div style="text-align: center;">
        <h2 style="margin: 0; font-size: 16px;">Añadir Registro</h2>
        <a href="<?= base_url('/addCapacitacion') ?>" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;" target="_blank">Añadir Registro</a>
      </div>
    </div>
  </nav>

  <!-- Espaciado para evitar que el contenido se oculte bajo el navbar fijo -->
  <div style="height: 160px;"></div>

  <div class="container mt-5">
    <h2 class="mb-4">Listado de Capacitaciones</h2>

    <?php if (session()->getFlashdata('msg')): ?>
      <div class="alert alert-info" role="alert">
        <?= session()->getFlashdata('msg') ?>
      </div>
    <?php endif; ?>

    <div class="table-responsive">
      <table id="capacitacionesTable" class="table table-striped table-bordered" style="width: 100%;">
        <thead class="table-light">
          <!-- Fila de títulos -->
          <tr>
            <th>ID</th>
            <th>Capacitación</th>
            <th>Objetivo de la Capacitación</th>
            <th>Observaciones</th>
            <th>Acciones</th>
          </tr>
        <tbody>
          <?php if (empty($capacitaciones)): ?>
            <tr>
              <td colspan="5" class="text-center">No hay capacitaciones registradas.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($capacitaciones as $capacitacion): ?>
              <tr>
                <td><?= esc($capacitacion['id_capacitacion']) ?></td>
                <td><?= esc($capacitacion['capacitacion']) ?></td>
                <td><?= esc($capacitacion['objetivo_capacitacion']) ?></td>
                <td><?= esc($capacitacion['observaciones']) ?></td>
                <td>
                  <a href="<?= base_url('editCapacitacion/' . $capacitacion['id_capacitacion']) ?>" class="btn btn-warning btn-sm">Editar</a>
                  <a href="<?= base_url('deleteCapacitacion/' . $capacitacion['id_capacitacion']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta capacitación?');">Eliminar</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr>
            <!-- Sin filtro para ID -->
            <th></th>
            <!-- Filtro para "Capacitación": select dinámico -->
            <th>
              <select id="filterCapacitacion" class="form-select form-select-sm">
                <option value="">Todos</option>
              </select>
            </th>
            <!-- Filtro para "Objetivo de la Capacitación": select dinámico -->
            <th>
              <select id="filterObjetivos" class="form-select form-select-sm">
                <option value="">Todos</option>
              </select>
            </th>
            <!-- Filtro para "Observaciones": select dinámico -->
            <th>
              <select id="filterObservaciones" class="form-select form-select-sm">
                <option value="">Todos</option>
              </select>
            </th>
            <!-- Sin filtro para "Acciones" -->
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
      <!-- Empresa y Derechos -->
      <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
      <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
      <p style="margin: 5px 0;">NIT: 901.653.912</p>

      <!-- Enlace al sitio web -->
      <p style="margin: 5px 0;">
        Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" style="color: #007BFF; text-decoration: none;">https://cycloidtalent.com/</a>
      </p>

      <!-- Enlaces a Redes Sociales -->
      <p style="margin: 15px 0 5px;"><strong>Nuestras Redes Sociales:</strong></p>
      <div style="display: flex; gap: 15px; justify-content: center;">
        <a href="https://www.facebook.com/CycloidTalent" target="_blank" style="color: #3A3F51; text-decoration: none;">
          <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
        </a>
        <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" style="color: #3A3F51; text-decoration: none;">
          <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
        </a>
        <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" style="color: #3A3F51; text-decoration: none;">
          <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
        </a>
        <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" style="color: #3A3F51; text-decoration: none;">
          <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
        </a>
      </div>
    </div>
  </footer>

  <!-- Inicialización de DataTables con botones y filtros -->
  <!-- ... resto del código HTML ... -->

<script>
  $(document).ready(function () {
    var table = $('#capacitacionesTable').DataTable({
      scrollX: true,
      autoWidth: false,
      dom: 'Bfrtip', // Contenedor para los botones
      buttons: [
        {
          extend: 'excelHtml5',
          title: 'Listado de Capacitaciones',
          exportOptions: {
            // Exporta las columnas 0 a 3 (se omite la columna "Acciones")
            columns: [0, 1, 2, 3],
            // Personaliza la forma en que se exportan los encabezados
            format: {
              header: function (data, columnIdx) {
                // Se retorna el texto del <th> de la primera fila (fila de títulos)
                return $('#capacitacionesTable thead tr th').eq(columnIdx).text();
              }
            }
          }
        }
      ],
      language: {
        url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
      },
      initComplete: function () {
        var api = this.api();

        // Filtro para la columna "Capacitación" (índice 1)
        var columnCap = api.column(1);
        var selectCap = $('#filterCapacitacion');
        columnCap.data().unique().sort().each(function (d) {
          selectCap.append('<option value="' + d + '">' + d + '</option>');
        });
        selectCap.on('change', function () {
          var val = $.fn.dataTable.util.escapeRegex($(this).val());
          columnCap.search(val ? '^' + val + '$' : '', true, false).draw();
        });

        // Filtro para la columna "Objetivo de la Capacitación" (índice 2)
        var columnObj = api.column(2);
        var selectObj = $('#filterObjetivos');
        columnObj.data().unique().sort().each(function (d) {
          selectObj.append('<option value="' + d + '">' + d + '</option>');
        });
        selectObj.on('change', function () {
          var val = $.fn.dataTable.util.escapeRegex($(this).val());
          columnObj.search(val ? '^' + val + '$' : '', true, false).draw();
        });

        // Filtro para la columna "Observaciones" (índice 3)
        var columnObs = api.column(3);
        var selectObs = $('#filterObservaciones');
        columnObs.data().unique().sort().each(function (d) {
          selectObs.append('<option value="' + d + '">' + d + '</option>');
        });
        selectObs.on('change', function () {
          var val = $.fn.dataTable.util.escapeRegex($(this).val());
          columnObs.search(val ? '^' + val + '$' : '', true, false).draw();
        });
      }
    });
  });
</script>

<!-- ... resto del código HTML ... -->


  <!-- Bootstrap JS y Popper -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>
