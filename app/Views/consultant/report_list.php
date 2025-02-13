<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lista de Reportes</title>
  <!-- Bootstrap CSS, DataTables y DataTables Buttons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet" />
  <!-- Iconos Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    /* La tabla ocupa el ancho completo */
    table.dataTable {
      width: 100%;
    }

    /* Estilos generales para celdas de la tabla */
    table.dataTable thead th,
    table.dataTable tbody td,
    table.dataTable tfoot th {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      vertical-align: middle;
    }

    /* Clases para columnas sin truncamiento */
    td.title-col,
    td.tipodoc-col,
    td.tiporeporte-col {
      white-space: normal;
      overflow: visible;
      text-overflow: clip;
    }

    /* Columna Observaciones (se trunca a 40 caracteres) */
    td.observaciones-col {
      max-width: 40ch;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* Columna Enlace: muestra solo un icono */
    td.enlace-col {
      text-align: center;
    }

    /* Estilo para los filtros en el tfoot */
    tfoot th {
      padding: 8px 10px;
      background-color: #f8f9fa;
    }

    /* Alinear la búsqueda a la izquierda */
    div.dataTables_filter {
      text-align: left !important;
    }

    /* Icono de detalles: cursor pointer */
    .details-control {
      cursor: pointer;
      margin-right: 5px;
    }
  </style>
</head>

<body class="bg-light">
  <!-- Navbar (sin cambios en estructura) -->
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
        <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm mt-1">Ir a DashBoard</a>
      </div>
      <!-- Botón derecho -->
      <div style="text-align: center;">
        <h2 style="margin: 0; font-size: 16px;">Añadir Registro</h2>
        <a href="<?= base_url('/addReport') ?>" class="btn btn-success btn-sm mt-1">Añadir Registro</a>
      </div>
    </div>
  </nav>

  <!-- Espaciado para evitar que el contenido quede oculto por el navbar -->
  <div style="height: 200px;"></div>

  <!-- Contenedor fluid -->
  <div class="container-fluid my-4">
    <h2 class="text-center mb-4">Lista de Reportes</h2>

    <h3 class="mb-3">Reportes</h3>

    <?php if (session()->get('msg')) : ?>
      <div class="alert alert-info">
        <?= session()->get('msg') ?>
      </div>
    <?php endif; ?>

    <?php if (isset($reports) && !empty($reports)) : ?>
      <!-- Botón para restablecer filtros y exportar a Excel -->
      <div class="mb-3">
        <button id="clearState" class="btn btn-danger btn-sm">Restablecer Filtros</button>
      </div>

      <table id="reportTable" class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Acciones</th>
            <th>Fecha de Creación</th>
            <th>Enlace</th>
            <th>ID</th>
            <th>Título del Reporte</th>
            <th>Tipo de Documento</th>
            <th>Tipo de Reporte</th>
            <th>Estado</th>
            <th>Observaciones</th>
            <th>ID Cliente</th>
            <th>Nombre del Cliente</th>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <!-- Sin filtro -->
            <th>Acciones</th>
            <!-- Dropdown Filtering -->
            <th>Fecha de Creación</th>
            <th>Enlace</th>
            <!-- Sin filtro -->
            <th>ID</th>
            <!-- Text Input Filtering -->
            <th>Título del Reporte</th>
            <!-- Dropdown Filtering -->
            <th>Tipo de Documento</th>
            <th>Tipo de Reporte</th>
            <!-- Dropdown Filtering -->
            <th>Estado</th>
            <!-- Text Input Filtering -->
            <th>Observaciones</th>
            <!-- Sin filtro -->
            <th>ID Cliente</th>
            <!-- Text Input Filtering -->
            <th>Nombre del Cliente</th>
          </tr>
        </tfoot>
        <tbody>
          <?php foreach ($reports as $report) : ?>
            <tr>
              <!-- Columna Acciones: incluye icono para fila expandible y botones de Editar/Eliminar -->
              <td>
                <i class="bi bi-plus-square details-control"></i>
                <a href="<?= base_url('/editReport/' . $report['id_reporte']) ?>" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Editar Reporte">Editar</a>
                <a href="<?= base_url('/deleteReport/' . $report['id_reporte']) ?>" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Eliminar Reporte" onclick="return confirm('¿Está seguro de eliminar este reporte?');">Eliminar</a>
              </td>
              <!-- Columna Fecha de Creación -->
              <td data-bs-toggle="tooltip" title="Fecha de Creación: <?= htmlspecialchars($report['created_at']) ?>">
                <?= htmlspecialchars($report['created_at']) ?>
              </td>
              <!-- Columna Enlace: se muestra un icono; se guarda el link en data-link -->
              <td class="enlace-col" data-link="<?= htmlspecialchars($report['enlace']) ?>" data-bs-toggle="tooltip" title="Abrir documento">
                <a href="<?= htmlspecialchars($report['enlace']) ?>" target="_blank">
                  <i class="bi bi-link-45deg"></i>
                </a>
              </td>
              <!-- Columna ID -->
              <td data-bs-toggle="tooltip" title="ID Reporte: <?= $report['id_reporte'] ?>">
                <?= $report['id_reporte'] ?>
              </td>
              <!-- Columna Título del Reporte -->
              <td data-bs-toggle="tooltip" title="Título: <?= htmlspecialchars($report['titulo_reporte']) ?>">
                <?= htmlspecialchars($report['titulo_reporte']) ?>
              </td>
              <!-- Columna Tipo de Documento -->
              <td data-bs-toggle="tooltip" title="Tipo de Documento: <?= htmlspecialchars($details[array_search($report['id_detailreport'], array_column($details, 'id_detailreport'))]['detail_report'] ?? 'N/A') ?>">
                <?= htmlspecialchars($details[array_search($report['id_detailreport'], array_column($details, 'id_detailreport'))]['detail_report'] ?? 'N/A') ?>
              </td>
              <!-- Columna Tipo de Reporte -->
              <td data-bs-toggle="tooltip" title="Tipo de Reporte: <?= htmlspecialchars($reportTypes[array_search($report['id_report_type'], array_column($reportTypes, 'id_report_type'))]['report_type'] ?? '') ?>">
                <?= htmlspecialchars($reportTypes[array_search($report['id_report_type'], array_column($reportTypes, 'id_report_type'))]['report_type'] ?? '') ?>
              </td>
              <!-- Columna Estado -->
              <td data-bs-toggle="tooltip" title="Estado: <?= htmlspecialchars($report['estado']) ?>">
                <?= htmlspecialchars($report['estado']) ?>
              </td>
              <!-- Columna Observaciones -->
              <td class="observaciones-col" data-bs-toggle="tooltip" title="Observaciones: <?= htmlspecialchars($report['observaciones']) ?>">
                <?= htmlspecialchars($report['observaciones']) ?>
              </td>
              <!-- Columna ID Cliente -->
              <td data-bs-toggle="tooltip" title="ID Cliente: <?= htmlspecialchars($report['id_cliente']) ?>">
                <?= htmlspecialchars($report['id_cliente']) ?>
              </td>
              <!-- Columna Nombre del Cliente -->
              <td data-bs-toggle="tooltip" title="Nombre del Cliente: <?= htmlspecialchars($clients[array_search($report['id_cliente'], array_column($clients, 'id_cliente'))]['nombre_cliente'] ?? '') ?>">
                <?= htmlspecialchars($clients[array_search($report['id_cliente'], array_column($clients, 'id_cliente'))]['nombre_cliente'] ?? '') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else : ?>
      <p class="text-muted">No hay reportes disponibles.</p>
    <?php endif; ?>
  </div>

  <!-- Footer (sin cambios) -->
  <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
      <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
      <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
      <p style="margin: 5px 0;">NIT: 901.653.912</p>
      <p style="margin: 5px 0;">
        Sitio oficial:
        <a href="https://cycloidtalent.com/" target="_blank" style="color: #007BFF; text-decoration: none;">
          https://cycloidtalent.com/
        </a>
      </p>
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

  <!-- Scripts: jQuery, Bootstrap, DataTables y Buttons -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>

  <script>
    // Función para generar el contenido del row child details
    function format(d) {
      // d[0]: Acciones (omitido)
      // d[1]: Fecha de Creación
      // d[2]: Enlace
      // d[3]: ID
      // d[4]: Título del Reporte
      // d[5]: Tipo de Documento
      // d[6]: Tipo de Reporte
      // d[7]: Estado
      // d[8]: Observaciones
      // d[9]: ID Cliente
      // d[10]: Nombre del Cliente
      return '<div style="overflow:auto;">' +
        '<table style="width:100%;" class="table table-sm table-borderless">' +
        '<tr><td style="width:30%;"><strong>Fecha de Creación:</strong></td><td style="width:70%;">' + d[1] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Enlace:</strong></td><td style="width:70%;">' + d[2] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>ID:</strong></td><td style="width:70%;">' + d[3] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Título del Reporte:</strong></td><td style="width:70%;">' + d[4] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Tipo de Documento:</strong></td><td style="width:70%;">' + d[5] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Tipo de Reporte:</strong></td><td style="width:70%;">' + d[6] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Estado:</strong></td><td style="width:70%;">' + d[7] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Observaciones:</strong></td><td style="width:70%;">' + d[8] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>ID Cliente:</strong></td><td style="width:70%;">' + d[9] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Nombre del Cliente:</strong></td><td style="width:70%;">' + d[10] + '</td></tr>' +
        '</table>' +
        '</div>';
    }

    $(document).ready(function() {
      const table = $('#reportTable').DataTable({
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
        },
        pageLength: 20,
        lengthMenu: [
          [20, 50, 100],
          [20, 50, 100]
        ],
        // Ordenación por defecto por la columna ID (índice 3)
        order: [
          [3, "desc"]
        ],
        // Se deshabilita el ordenamiento en las columnas "Acciones" (índice 0) y "Enlace" (índice 2)
        columnDefs: [{
            targets: [0, 2],
            orderable: false,
            searchable: false
          },
          {
            targets: 8, // "Observaciones" ahora es la columna índice 8
            className: "observaciones-col"
          }
        ],
        stateSave: true,
        stateSaveCallback: function(settings, data) {
          localStorage.setItem('DataTables_reportTable', JSON.stringify(data));
        },
        stateLoadCallback: function(settings) {
          return JSON.parse(localStorage.getItem('DataTables_reportTable'));
        },
        dom: 'Bfrtip',
        buttons: [{
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Exportar a Excel',
            className: 'btn btn-success btn-sm',
            exportOptions: {
              // Excluir columnas "Acciones" (índice 0) y "Enlace" (índice 2)
              columns: function(idx, data, node) {
                return idx !== 0 && idx !== 2;
              }
            },
            title: 'Lista de Reportes',
            filename: 'Lista_de_Reportes',
            titleAttr: 'Exportar a Excel',
            customize: function(xlsx) {
              var sheet = xlsx.xl.worksheets['sheet1.xml'];
              $('row:first c', sheet).attr('s', '2');
            }
          },
          {
            extend: 'colvis',
            text: '<i class="bi bi-eye"></i> Visibilidad de Columnas',
            className: 'btn btn-secondary btn-sm',
            titleAttr: 'Mostrar u Ocultar Columnas'
          }
        ],
        initComplete: function() {
          var api = this.api();
          // Recorremos todas las columnas para colocar los filtros en el footer
          api.columns().every(function() {
            var column = this;
            var columnIdx = column.index();
            var $footerCell = $(column.footer()).empty();

            // Definimos los filtros según la nueva posición de las columnas:
            // Filtros de tipo input (texto) para: Título del Reporte (índice 4), Observaciones (índice 8) y Nombre del Cliente (índice 10)
            if ([4, 8, 10].indexOf(columnIdx) !== -1) {
              var input = $('<input type="text" class="form-control form-control-sm" placeholder="Buscar...">')
                .appendTo($footerCell)
                .on('keyup change', function() {
                  if (column.search() !== this.value) {
                    column.search(this.value).draw();
                  }
                });
              var state = api.state.loaded();
              if (state && state.columns[columnIdx].search && state.columns[columnIdx].search.search) {
                input.val(state.columns[columnIdx].search.search);
              }
            }
            // Filtros dropdown para: Fecha de Creación (índice 1), Tipo de Documento (índice 5), Tipo de Reporte (índice 6) y Estado (índice 7)
            else if ([1, 5, 6, 7].indexOf(columnIdx) !== -1) {
              var select = $('<select class="form-select form-select-sm"><option value="">Todos</option></select>')
                .appendTo($footerCell)
                .on('change', function() {
                  var val = $.fn.dataTable.util.escapeRegex($(this).val());
                  column.search(val ? '^' + val + '$' : '', true, false).draw();
                });
              $footerCell.data('select', select);

              var state = api.state.loaded();
              if (state && state.columns[columnIdx].search && state.columns[columnIdx].search.search) {
                var searchVal = state.columns[columnIdx].search.search.replace(/^\^|\$$/g, '');
                select.val(searchVal);
              }
            }
          });
        },
        // Actualizar opciones de filtros dropdown en cada draw
        drawCallback: function(settings) {
          var api = this.api();
          api.columns().every(function() {
            var column = this;
            var columnIdx = column.index();
            if ([1, 5, 6, 7].indexOf(columnIdx) !== -1) {
              var $footerCell = $(column.footer());
              var select = $footerCell.data('select');
              if (select) {
                var currentVal = select.val();
                select.find('option:not(:first)').remove();
                column.data({
                  filter: 'applied'
                }).unique().sort().each(function(d, j) {
                  var text = $('<div>').html(d).text();
                  select.append('<option value="' + text + '">' + text + '</option>');
                });
                select.val(currentVal);
              }
            }
          });
        }
      });

      // Evento para la fila expandible (row child details)
      $('#reportTable tbody').on('click', 'td .details-control', function() {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
          row.child.hide();
          $(this).removeClass('bi-dash-square').addClass('bi-plus-square');
        } else {
          row.child(format(row.data())).show();
          $(this).removeClass('bi-plus-square').addClass('bi-dash-square');
        }
      });

      // Botón para restablecer estado y recargar la página
      $('#clearState').on('click', function() {
        localStorage.removeItem('DataTables_reportTable');
        table.state.clear();
        location.reload();
      });
    });
  </script>

</body>

</html>