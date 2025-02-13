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
            options = ['ABIERTA', 'CERRADA'];
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
          table.ajax.reload();
        } else {
          alert('Por favor, seleccione un cliente.');
        }
      });

      // Actualizar automáticamente al cambiar el select
      $('#clientSelect').on('change', function() {
        var clientId = $(this).val();
        if (clientId) {
          localStorage.setItem('selectedClient', clientId);
          table.ajax.reload();
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

      // Inicializar tooltips de Bootstrap
      function initializeTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl);
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