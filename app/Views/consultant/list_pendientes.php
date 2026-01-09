<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lista de Pendientes</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <!-- DataTables Buttons CSS -->
  <link href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css" rel="stylesheet">
  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    body {
      background-color: #f9f9f9;
      font-family: Arial, sans-serif;
    }

    h1 {
      margin: 20px 0;
      text-align: center;
      color: #333;
    }

    table {
      width: 100%;
    }

    .dataTables_filter input {
      background-color: #f0f0f0;
      border-radius: 5px;
      border: 1px solid #ccc;
      padding: 6px;
    }

    .dataTables_length select {
      background-color: #f0f0f0;
      border-radius: 5px;
      padding: 6px;
    }

    td,
    th {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      height: 25px;
    }

    .tooltip-inner {
      max-width: 300px;
      word-wrap: break-word;
      z-index: 1050;
    }

    .filters select,
    .filters input {
      width: 100%;
      padding: 4px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }

    /* Fila expandible */
    td.details-control {
      background: url('https://www.datatables.net/examples/resources/details_open.png') no-repeat center center;
      cursor: pointer;
    }

    tr.shown td.details-control {
      background: url('https://www.datatables.net/examples/resources/details_close.png') no-repeat center center;
    }

    /* Aseguramos que las celdas editables tengan un mínimo de contenido para ser clicables */
    .editable,
    .editable-select,
    .editable-date {
      min-height: 1em;
    }

    th:nth-child(7),
    td:nth-child(7) {
      max-width: 200px;
      /* Ajusta el valor según necesites */
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* Estilos para tarjetas clickeables */
    .card-clickable {
      cursor: pointer;
      transition: all 0.3s ease;
      border: 2px solid transparent;
    }

    .card-clickable:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .card-clickable.active {
      border: 3px solid #ffeb3b !important;
      box-shadow: 0 0 25px rgba(255, 235, 59, 0.8), 0 0 10px rgba(255, 255, 255, 0.5) !important;
      transform: scale(1.08) !important;
      position: relative;
    }

    .card-clickable.active::after {
      content: '✓';
      position: absolute;
      top: 5px;
      right: 5px;
      background: #ffeb3b;
      color: #000;
      width: 25px;
      height: 25px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 16px;
    }

    .card-year {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 10px;
      min-height: 80px;
    }

    .card-month {
      min-height: 70px;
    }

    .card-status {
      min-height: 90px;
    }

    .section-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: #4e73df;
      border-left: 4px solid #4e73df;
      padding-left: 10px;
      margin: 20px 0 15px 0;
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
    <div class="container-fluid">
      <div class="d-flex align-items-center">
        <a href="https://dashboard.cycloidtalent.com/login" class="me-3">
          <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" height="60">
        </a>
        <a href="https://cycloidtalent.com/index.php/consultoria-sst" class="me-3">
          <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" height="60">
        </a>
        <a href="https://cycloidtalent.com/">
          <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" height="60">
        </a>
      </div>
      <div class="ms-auto d-flex">
        <div class="text-center me-3">
          <h6 class="mb-1" style="font-size: 16px;">Ir a Dashboard</h6>
          <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm">Ir a DashBoard</a>
        </div>
        <div class="text-center">
          <h6 class="mb-1" style="font-size: 16px;">Añadir Registro</h6>
          <a href="<?= base_url('/addPendiente') ?>" class="btn btn-success btn-sm" target="_blank">Añadir Registro</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Espaciado para el navbar fijo -->
  <div style="height: 100px;"></div>

  <div class="container-fluid mt-5">
    <h1 class="text-center mb-4">Lista de Pendientes</h1>

    <!-- Mensaje informativo -->
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <i class="fas fa-info-circle"></i>
      <strong>Filtros Dinámicos:</strong> Las tarjetas de año, estado y mes son interactivas.
      Haz clic sobre ellas para filtrar la tabla instantáneamente. Puedes combinar múltiples filtros.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <!-- Sección de Filtros por Año -->
    <div class="d-flex justify-content-between align-items-center">
      <div class="section-title mb-0">
        <i class="fas fa-calendar-alt"></i> Filtrar por Año
      </div>
      <button type="button" id="btnClearCardFilters" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-times"></i> Limpiar Filtros de Tarjetas
      </button>
    </div>
    <div class="row mb-4 mt-2" id="yearCards">
      <!-- Se generarán dinámicamente con JavaScript -->
    </div>

    <!-- Tarjetas de Estados (clickeables) -->
    <div class="section-title">
      <i class="fas fa-tasks"></i> Filtrar por Estado
    </div>
    <div class="row mb-4">
      <div class="col-md-4">
        <div class="card text-white bg-primary card-clickable card-status" data-status="ABIERTA">
          <div class="card-body text-center">
            <h5 class="card-title">Abiertas</h5>
            <p class="card-text display-6" id="countAbierta">0</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-white bg-danger card-clickable card-status" data-status="CERRADA">
          <div class="card-body text-center">
            <h5 class="card-title">Cerradas</h5>
            <p class="card-text display-6" id="countCerrada">0</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-white bg-warning card-clickable card-status" data-status="SIN RESPUESTA DEL CLIENTE">
          <div class="card-body text-center">
            <h5 class="card-title">Sin Respuesta del Cliente</h5>
            <p class="card-text display-6" id="countSinRespuesta">0</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tarjetas mensuales (clickeables) -->
    <div class="section-title">
      <i class="fas fa-calendar-week"></i> Filtrar por Mes
    </div>
    <div class="row mb-4">
      <div class="col-6 col-md-1">
        <div class="card text-white bg-info card-clickable card-month" data-month="1">
          <div class="card-body p-2">
            <h6 class="card-title text-center mb-0">Enero</h6>
            <p class="card-text text-center" id="countEnero">0</p>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-1">
        <div class="card text-white bg-info card-clickable card-month" data-month="2">
          <div class="card-body p-2">
            <h6 class="card-title text-center mb-0">Febrero</h6>
            <p class="card-text text-center" id="countFebrero">0</p>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-1">
        <div class="card text-white bg-info card-clickable card-month" data-month="3">
          <div class="card-body p-2">
            <h6 class="card-title text-center mb-0">Marzo</h6>
            <p class="card-text text-center" id="countMarzo">0</p>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-1">
        <div class="card text-white bg-info card-clickable card-month" data-month="4">
          <div class="card-body p-2">
            <h6 class="card-title text-center mb-0">Abril</h6>
            <p class="card-text text-center" id="countAbril">0</p>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-1">
        <div class="card text-white bg-info card-clickable card-month" data-month="5">
          <div class="card-body p-2">
            <h6 class="card-title text-center mb-0">Mayo</h6>
            <p class="card-text text-center" id="countMayo">0</p>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-1">
        <div class="card text-white bg-info card-clickable card-month" data-month="6">
          <div class="card-body p-2">
            <h6 class="card-title text-center mb-0">Junio</h6>
            <p class="card-text text-center" id="countJunio">0</p>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-1">
        <div class="card text-white bg-info card-clickable card-month" data-month="7">
          <div class="card-body p-2">
            <h6 class="card-title text-center mb-0">Julio</h6>
            <p class="card-text text-center" id="countJulio">0</p>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-1">
        <div class="card text-white bg-info card-clickable card-month" data-month="8">
          <div class="card-body p-2">
            <h6 class="card-title text-center mb-0">Agosto</h6>
            <p class="card-text text-center" id="countAgosto">0</p>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-1">
        <div class="card text-white bg-info card-clickable card-month" data-month="9">
          <div class="card-body p-2">
            <h6 class="card-title text-center mb-0">Sept.</h6>
            <p class="card-text text-center" id="countSeptiembre">0</p>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-1">
        <div class="card text-white bg-info card-clickable card-month" data-month="10">
          <div class="card-body p-2">
            <h6 class="card-title text-center mb-0">Oct.</h6>
            <p class="card-text text-center" id="countOctubre">0</p>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-1">
        <div class="card text-white bg-info card-clickable card-month" data-month="11">
          <div class="card-body p-2">
            <h6 class="card-title text-center mb-0">Nov.</h6>
            <p class="card-text text-center" id="countNoviembre">0</p>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-1">
        <div class="card text-white bg-info card-clickable card-month" data-month="12">
          <div class="card-body p-2">
            <h6 class="card-title text-center mb-0">Dic.</h6>
            <p class="card-text text-center" id="countDiciembre">0</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Selección de Cliente -->
    <div class="row mb-3">
      <div class="col-md-4">
        <label for="clientSelect">Selecciona un Cliente:</label>
        <select id="clientSelect" class="form-select">
          <option value="">Seleccione un cliente</option>
        </select>
      </div>
      <div class="col-md-2 align-self-end">
        <button id="loadData" class="btn btn-primary">Cargar Datos</button>
      </div>
    </div>

    <button id="clearState" class="btn btn-danger btn-sm mb-3">Restablecer Filtros</button>
    <button id="btnRecalcularDias" class="btn btn-warning btn-sm mb-3">Recalcular Conteo de Días</button>
    <div id="buttonsContainer"></div>

    <div class="table-responsive">
      <table id="pendientesTable" class="table table-bordered table-striped">
        <thead>
          <tr>
            <!-- Podríamos agregar una columna para fila expandible si se requiere -->
            <th></th>
            <th>ID</th>
            <th>Acciones</th>
            <th>Cliente</th>
            <th>Fecha Asignación</th>
            <th>Responsable</th>
            <th>*Tarea Actividad</th>
            <th>*Fecha Cierre</th>
            <th>*Estado</th>
            <th>Conteo Días</th>
            <th>*Estado Avance</th>
            <th>*Evidencia para Cerrarla</th>
          </tr>
        </thead>
        <tfoot>
          <tr class="filters">
            <th></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar ID"></th>
            <th><!-- Sin filtro para Acciones --></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Cliente"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Fecha"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Responsable"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Tarea"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Fecha"></th>
            <th>
              <select class="form-control form-control-sm filter-search">
                <option value="">Todos</option>
                <option value="ABIERTA">ABIERTA</option>
                <option value="CERRADA">CERRADA</option>
                <option value="SIN RESPUESTA DEL CLIENTE">SIN RESPUESTA DEL CLIENTE</option>
              </select>
            </th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Conteo"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Avance"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Evidencia"></th>
          </tr>
        </tfoot>
        <tbody>
          <!-- Los datos se cargarán vía AJAX -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-white py-4 border-top mt-4">
    <div class="container text-center">
      <p class="fw-bold mb-1">Cycloid Talent SAS</p>
      <p class="mb-1">Todos los derechos reservados © 2024</p>
      <p class="mb-1">NIT: 901.653.912</p>
      <p class="mb-3">
        Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank">https://cycloidtalent.com/</a>
      </p>
    </div>
  </footer>

  <!-- Scripts -->
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
  <!-- DataTables Buttons JS -->
  <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>

  <script>
    // Función para formatear la fila expandible (child row) si se requiere
    function format(rowData) {
      // Se puede personalizar para mostrar más detalles
      var html = '<table class="table table-sm table-borderless" style="width:100%; table-layout:auto;">';
      html += '<tr><td><strong>Cliente:</strong></td><td>' + (rowData.nombre_cliente || '') + '</td></tr>';
      html += '<tr><td><strong>Fecha Asignación:</strong></td><td>' + (rowData.fecha_asignacion || '') + '</td></tr>';
      html += '<tr><td><strong>Responsable:</strong></td><td>' + (rowData.responsable || '') + '</td></tr>';
      html += '<tr><td><strong>Tarea/Actividad:</strong></td><td>' + (rowData.tarea_actividad || '') + '</td></tr>';
      html += '<tr><td><strong>Fecha Cierre:</strong></td><td>' + (rowData.fecha_cierre || '') + '</td></tr>';
      html += '<tr><td><strong>Estado:</strong></td><td>' + (rowData.estado || '') + '</td></tr>';
      html += '<tr><td><strong>Conteo Días:</strong></td><td>' + (rowData.conteo_dias || '0') + '</td></tr>';
      html += '<tr><td><strong>Estado Avance:</strong></td><td>' + (rowData.estado_avance || '') + '</td></tr>';
      html += '<tr><td><strong>Evidencia:</strong></td><td>' + (rowData.evidencia_para_cerrarla || '') + '</td></tr>';
      html += '</table>';
      return html;
    }

    $(document).ready(function() {
      // Variables globales para filtros activos
      var activeYear = null;
      var activeMonth = null;
      var activeStatus = null;

      // Inicializar el select con Select2
      $('#clientSelect').select2({
        placeholder: 'Seleccione un cliente',
        allowClear: true,
        width: '100%'
      });

      // Cargar clientes vía AJAX
      $.ajax({
        url: "<?= base_url('/api/getClientes') ?>",
        method: "GET",
        dataType: "json",
        success: function(data) {
          data.forEach(function(cliente) {
            $("#clientSelect").append('<option value="' + cliente.id + '">' + cliente.nombre + '</option>');
          });
          var storedClient = localStorage.getItem('selectedClient');
          if (storedClient) {
            $("#clientSelect").val(storedClient).trigger('change');
          }
        },
        error: function() {
          alert('Error al cargar la lista de clientes.');
        }
      });

      // Inicializar DataTable con AJAX, stateSave y filtros en tfoot
      var table = $('#pendientesTable').DataTable({
        stateSave: true,
        stateLoadCallback: function(settings) {
          return JSON.parse(localStorage.getItem('DataTables_' + settings.sTableId + '_' + window.location.pathname));
        },
        language: {
          url: "//cdn.datatables.net/plug-ins/1.11.3/i18n/es-ES.json"
        },
        pagingType: "full_numbers",
        responsive: true,
        autoWidth: false,
        dom: 'Bfltip',
        pageLength: 10,
        buttons: [{
            extend: 'excelHtml5',
            text: 'Exportar a Excel',
            className: 'btn btn-success btn-sm'
          },
          {
            extend: 'colvis',
            text: 'Seleccionar Columnas',
            className: 'btn btn-secondary btn-sm'
          }
        ],
        ajax: {
          url: "<?= base_url('/api/getPendientesAjax') ?>",
          data: function(d) {
            d.cliente = $("#clientSelect").val();
          },
          dataSrc: ''
        },
        columns: [{
            data: null,
            orderable: false,
            className: 'details-control',
            defaultContent: ''
          },
          {
            data: 'id_pendientes'
          },
          {
            data: 'acciones',
            orderable: false
          },
          {
            data: 'nombre_cliente'
          },
          {
            data: 'fecha_asignacion',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable-date" data-field="fecha_asignacion" data-id="' + row.id_pendientes + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          },
          {
            data: 'responsable'
          },
          {
            data: 'tarea_actividad',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable" data-field="tarea_actividad" data-id="' + row.id_pendientes + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          },
          {
            data: 'fecha_cierre',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable-date" data-field="fecha_cierre" data-id="' + row.id_pendientes + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          },
          {
            data: 'estado',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable-select" data-field="estado" data-id="' + row.id_pendientes + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          },
          {
            data: 'conteo_dias'
          },
          {
            data: 'estado_avance',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable" data-field="estado_avance" data-id="' + row.id_pendientes + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          },
          {
            data: 'evidencia_para_cerrarla',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable" data-field="evidencia_para_cerrarla" data-id="' + row.id_pendientes + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          }
        ],
        initComplete: function() {
          var api = this.api();
          api.columns().every(function() {
            var column = this;
            var index = column.index();
            // Se actualizó el selector para usar .filter-search
            var filterElement = $('tfoot tr.filters th').eq(index).find('.filter-search');
            if (filterElement.length) {
              column.data().unique().sort().each(function(d) {
                if (d !== null && d !== '' && filterElement.find('option[value="' + d + '"]').length === 0) {
                  filterElement.append('<option value="' + d + '">' + d + '</option>');
                }
              });
              var search = column.search();
              if (search) {
                filterElement.val(search);
              }
            }
          });
        }
      });

      table.buttons().container().appendTo('#buttonsContainer');

      // Generar tarjetas de años dinámicamente
      function generateYearCards() {
        if (!table) return;

        var yearCounts = {};

        // Contar pendientes por año basado en fecha_asignacion
        table.rows({search: 'applied'}).every(function() {
          var data = this.data();
          var fechaAsignacion = data.fecha_asignacion; // Acceder por nombre de propiedad
          if (fechaAsignacion) {
            var parts = fechaAsignacion.split("-");
            if (parts.length >= 1) {
              var year = parts[0];
              yearCounts[year] = (yearCounts[year] || 0) + 1;
            }
          }
        });

        var yearArray = Object.keys(yearCounts).sort().reverse();
        var yearCardsHtml = '';

        yearArray.forEach(function(year) {
          var count = yearCounts[year];
          yearCardsHtml += `
            <div class="col-6 col-md-2">
              <div class="card text-white card-year card-clickable" data-year="${year}">
                <div class="card-body text-center p-3">
                  <h4 class="card-title mb-1">${year}</h4>
                  <p class="mb-0" style="font-size: 1.5rem; font-weight: bold;">${count}</p>
                  <small style="font-size: 0.75rem;">pendientes</small>
                </div>
              </div>
            </div>
          `;
        });

        $('#yearCards').html(yearCardsHtml);
      }

      // Actualizar contadores de estados
      function updateStatusCounts() {
        if (!table) return;

        var countAbierta = 0;
        var countCerrada = 0;
        var countSinRespuesta = 0;

        table.rows({search: 'applied'}).every(function() {
          var data = this.data();
          var estado = data.estado; // Acceder por nombre de propiedad
          if (estado === 'ABIERTA') {
            countAbierta++;
          } else if (estado === 'CERRADA') {
            countCerrada++;
          } else if (estado === 'SIN RESPUESTA DEL CLIENTE') {
            countSinRespuesta++;
          }
        });

        $('#countAbierta').text(countAbierta);
        $('#countCerrada').text(countCerrada);
        $('#countSinRespuesta').text(countSinRespuesta);
      }

      // Actualizar contadores de meses
      function updateMonthlyCounts() {
        if (!table) return;

        var monthlyCounts = {
          1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0,
          7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0
        };

        table.rows({search: 'applied'}).every(function() {
          var data = this.data();
          var fechaAsignacion = data.fecha_asignacion; // Acceder por nombre de propiedad
          if (fechaAsignacion) {
            var parts = fechaAsignacion.split("-");
            if (parts.length >= 2) {
              var month = parseInt(parts[1], 10);
              if (month >= 1 && month <= 12) {
                monthlyCounts[month]++;
              }
            }
          }
        });

        $('#countEnero').text(monthlyCounts[1]);
        $('#countFebrero').text(monthlyCounts[2]);
        $('#countMarzo').text(monthlyCounts[3]);
        $('#countAbril').text(monthlyCounts[4]);
        $('#countMayo').text(monthlyCounts[5]);
        $('#countJunio').text(monthlyCounts[6]);
        $('#countJulio').text(monthlyCounts[7]);
        $('#countAgosto').text(monthlyCounts[8]);
        $('#countSeptiembre').text(monthlyCounts[9]);
        $('#countOctubre').text(monthlyCounts[10]);
        $('#countNoviembre').text(monthlyCounts[11]);
        $('#countDiciembre').text(monthlyCounts[12]);
      }

      // Función para aplicar filtros combinados
      function applyFilters() {
        if (!table) return;

        $.fn.dataTable.ext.search.pop(); // Limpiar filtros personalizados previos

        $.fn.dataTable.ext.search.push(
          function(settings, data, dataIndex) {
            // Obtener los datos del objeto row
            var rowData = table.row(dataIndex).data();
            var fechaAsignacion = rowData.fecha_asignacion || '';
            var estado = rowData.estado || '';

            // Filtro por año
            if (activeYear) {
              if (!fechaAsignacion.startsWith(activeYear)) {
                return false;
              }
            }

            // Filtro por mes
            if (activeMonth) {
              if (fechaAsignacion) {
                var parts = fechaAsignacion.split("-");
                if (parts.length >= 2) {
                  var month = parseInt(parts[1], 10);
                  if (month !== parseInt(activeMonth)) {
                    return false;
                  }
                } else {
                  return false;
                }
              } else {
                return false;
              }
            }

            // Filtro por estado
            if (activeStatus) {
              if (estado.trim() !== activeStatus) {
                return false;
              }
            }

            return true;
          }
        );

        table.draw();
        generateYearCards();
        updateStatusCounts();
        updateMonthlyCounts();
      }

      // Click en tarjetas de año
      $(document).on('click', '.card-year', function() {
        var year = $(this).data('year');

        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          activeYear = null;
        } else {
          $('.card-year').removeClass('active');
          $(this).addClass('active');
          activeYear = year;
        }

        applyFilters();
      });

      // Click en tarjetas de mes
      $(document).on('click', '.card-month', function() {
        var month = $(this).data('month');

        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          activeMonth = null;
        } else {
          $('.card-month').removeClass('active');
          $(this).addClass('active');
          activeMonth = month;
        }

        applyFilters();
      });

      // Click en tarjetas de estado
      $(document).on('click', '.card-status', function() {
        var status = $(this).data('status');

        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          activeStatus = null;
        } else {
          $('.card-status').removeClass('active');
          $(this).addClass('active');
          activeStatus = status;
        }

        applyFilters();
      });

      // Botón para limpiar todos los filtros de tarjetas
      $('#btnClearCardFilters').on('click', function() {
        activeYear = null;
        activeMonth = null;
        activeStatus = null;

        $('.card-year').removeClass('active');
        $('.card-month').removeClass('active');
        $('.card-status').removeClass('active');

        $.fn.dataTable.ext.search.pop();

        if (table) {
          table.draw();
          generateYearCards();
          updateStatusCounts();
          updateMonthlyCounts();
        }
      });

      // Actualizar contadores cuando la tabla se redibuja
      table.on('draw', function() {
        updateStatusCounts();
        updateMonthlyCounts();
        generateYearCards();
      });

      // Inicializar contadores y tarjetas de año
      updateStatusCounts();
      updateMonthlyCounts();
      generateYearCards();

      // Filtros por columna en el tfoot (se actualizó el selector a .filter-search)
      $('tfoot .filter-search').on('keyup change', function() {
        var index = $(this).parent().index();
        table.column(index).search($(this).val()).draw();
      });

      // Evento para expandir/contraer fila (child row)
      $('#pendientesTable tbody').on('click', 'td.details-control', function() {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        if (row.child.isShown()) {
          row.child.hide();
          tr.removeClass('shown');
        } else {
          row.child(format(row.data())).show();
          tr.addClass('shown');
        }
      });

      // Inline editing: se detecta clic en celdas editables
      $(document).on('click', '.editable, .editable-select, .editable-date', function(e) {
        e.stopPropagation(); // Evitar conflictos con rowchild
        if ($(this).find('input, select').length) return;
        var cell = $(this);
        var field = cell.data('field');
        var id = cell.data('id');
        var currentValue = cell.text().trim();
        // Si está vacío, asegurar un espacio no separable
        currentValue = currentValue === '' ? '' : currentValue;

        if (cell.hasClass('editable-date')) {
          var input = $('<input>', {
            type: 'date',
            class: 'form-control form-control-sm',
            value: currentValue
          });
          cell.html(input);
          input.focus();
          input.on('blur change', function() {
            var newValue = input.val();
            cell.html(newValue || '&nbsp;');
            updateField(id, field, newValue, cell);
          });
        } else if (cell.hasClass('editable-select')) {
          var options = [];
          if (field === 'estado') {
            options = ['ABIERTA', 'CERRADA', 'SIN RESPUESTA DEL CLIENTE'];
          }
          // Agregar otras opciones si es necesario
          var select = $('<select>', {
            class: 'form-control form-control-sm'
          });
          options.forEach(function(option) {
            select.append($('<option>', {
              value: option,
              text: option,
              selected: option === currentValue
            }));
          });
          cell.html(select);
          select.focus();
          select.on('blur change', function() {
            setTimeout(function() {
              var newValue = select.val();
              cell.html(newValue || '&nbsp;');
              updateField(id, field, newValue, cell);
            }, 200);
          });
        } else {
          var input = $('<input>', {
            type: 'text',
            class: 'form-control form-control-sm',
            value: currentValue
          });
          cell.html(input);
          input.focus();
          input.on('blur', function() {
            var newValue = input.val();
            cell.html(newValue || '&nbsp;');
            updateField(id, field, newValue, cell);
          });
        }
      });

      function updateField(id, field, value, cell) {
        $.ajax({
          url: '<?= base_url('/api/updatePendiente') ?>',
          method: 'POST',
          data: {
            id: id,
            field: field,
            value: value
          },
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              console.log('Registro actualizado correctamente');
              // Si se actualiza algún campo que afecte otros valores (ej. conteo_dias), se puede actualizar aquí
            } else {
              alert('Error: ' + response.message);
            }
          },
          error: function(xhr, status, error) {
            console.error('Error al comunicarse con el servidor:', error);
            alert('Error al comunicarse con el servidor: ' + error);
          }
        });
      }

      // Botón para cargar datos
      $("#loadData").click(function() {
        var clientId = $("#clientSelect").val();
        if (clientId) {
          localStorage.setItem('selectedClient', clientId);
          table.ajax.reload(function() {
            updateStatusCounts();
            updateMonthlyCounts();
            generateYearCards();
          });
        } else {
          alert('Por favor, seleccione un cliente.');
        }
      });

      // Actualizar automáticamente al cambiar el select
      $('#clientSelect').on('change', function() {
        var clientId = $(this).val();
        if (clientId) {
          localStorage.setItem('selectedClient', clientId);
          table.ajax.reload(function() {
            updateStatusCounts();
            updateMonthlyCounts();
            generateYearCards();
          });
        }
      });

      // Botón para restablecer filtros y estado
      $("#clearState").on("click", function() {
        localStorage.removeItem('selectedClient');
        var storageKey = 'DataTables_' + table.table().node().id + '_' + window.location.pathname;
        localStorage.removeItem(storageKey);
        table.state.clear();
        $('tfoot .filter-search').val('');
        table.search('').columns().search('').draw();
        $("#clientSelect").val(null).trigger("change");
      });

      // Nuevo manejador para recalcular el conteo de días
      $("#btnRecalcularDias").on("click", function () {
        $.ajax({
          url: "<?= base_url('/api/recalcularConteoDias') ?>",
          method: "POST",
          dataType: "json",
          success: function (response) {
            alert(response.message || 'Conteo de días recalculado');
            $('#pendientesTable').DataTable().ajax.reload();
          },
          error: function () {
            alert("Error al recalcular el conteo de días.");
          }
        });
      });

      // Inicializar tooltips de Bootstrap
      function initializeTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
          // Si ya existe una instancia, la eliminamos
          var tooltipInstance = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
          if (tooltipInstance) {
            tooltipInstance.dispose();
          }
          // Creamos una nueva instancia del tooltip
          new bootstrap.Tooltip(tooltipTriggerEl);
        });
      }

      initializeTooltips();
      table.on('draw.dt', function() {
        initializeTooltips();
      });
    });
  </script>
</body>

</html>