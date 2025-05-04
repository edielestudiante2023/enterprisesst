<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lista de Cronogramas de Capacitación</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <!-- DataTables Buttons CSS -->
  <link href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css" rel="stylesheet">
  <!-- Select2 CSS para select buscable -->
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
      max-width: 20ch;
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

    .filters select {
      width: 100%;
      padding: 4px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }

    /* Columna para fila expandible */
    td.details-control {
      background: url('https://www.datatables.net/examples/resources/details_open.png') no-repeat center center;
      cursor: pointer;
    }

    tr.shown td.details-control {
      background: url('https://www.datatables.net/examples/resources/details_close.png') no-repeat center center;
    }

    /* Para celdas editables: se asignan estilos mínimos para que siempre contengan contenido (por ejemplo, un espacio no separable) */
    .editable,
    .editable-select,
    .editable-date {
      min-height: 1em;
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
          <a href="<?= base_url('/addcronogCapacitacion') ?>" class="btn btn-success btn-sm" target="_blank">Añadir Registro</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Espaciado para el navbar fijo -->
  <div style="height: 100px;"></div>

  <div class="container-fluid mt-5">
    <h1 class="text-center mb-4">Lista de Cronogramas de Capacitación</h1>

    <!-- Bloque para seleccionar cliente -->
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
      <table id="cronogramaTable" class="table table-striped table-bordered nowrap" style="width:100%">
        <thead class="table-light">
          <tr>
            <!-- Columna para fila expandible -->
            <th></th>
            <th>#</th>
            <th>Acciones</th>
            <th>Capacitación</th>
            <th>Objetivo</th>
            <th>Cliente</th>
            <th>*Fecha Programada</th>
            <th>*Fecha de Realización</th>
            <th>*Estado</th>
            <th>*Perfil de Asistentes</th>
            <th>*Capacitador</th>
            <th>*Horas de Duración</th>
            <th>*Indicador de Realización</th>
            <th>*Asistentes</th>
            <th>*Total Programados</th>
            <th>% Cobertura</th>
            <th>*Evaluadas</th>
            <th>*Promedio</th>
            <th>*Observaciones</th>
          </tr>
        </thead>
        <tfoot class="table-light">
          <tr class="filters">
            <th></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar ID"></th>
            <th></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Capacitación"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Objetivo"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Cliente"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Fecha"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Fecha"></th>
            <th>
              <select class="form-select form-select-sm filter-search">
                <option value="">Todos</option>
                <option value="PROGRAMADA">PROGRAMADA</option>
                <option value="EJECUTADA">EJECUTADA</option>
                <option value="CANCELADA POR EL CLIENTE">CANCELADA POR EL CLIENTE</option>
                <option value="REPROGRAMADA">REPROGRAMADA</option>
              </select>
            </th>
            <th>
              <select class="form-select form-select-sm filter-search">
                <option value="">Todos</option>
                <option value="CONTRATISTAS">CONTRATISTAS</option>
                <option value="RESIDENTES">RESIDENTES</option>
                <option value="TODOS">TODOS</option>
                <option value="ASAMBLEA">ASAMBLEA</option>
                <option value="CONSEJO DE ADMINISTRACIÓN">CONSEJO DE ADMINISTRACIÓN</option>
                <option value="ADMINISTRADOR">ADMINISTRADOR</option>
              </select>
            </th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Capacitador"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Horas"></th>
            <th>
              <select class="form-select form-select-sm filter-search">
                <option value="">Todos</option>
                <option value="SE EJECUTO EN LA FECHA O ANTES">SE EJECUTO EN LA FECHA O ANTES</option>
                <option value="SE EJECUTO DESPUES">SE EJECUTO DESPUES</option>
                <option value="DECLINADA">DECLINADA</option>
                <option value="NO SE REALIZÓ">NO SE REALIZÓ</option>
              </select>
            </th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Asistentes"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Total"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar % Cobertura"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Evaluadas"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Promedio"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Observaciones"></th>
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
  <!-- Bootstrap Bundle (incluye Popper.js) -->
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
    // Función para formatear la fila expandible (detalles) con 30% para el nombre y 70% para el texto (con overflow auto)
    function format(rowData) {
      var html = '<table class="table table-sm table-borderless" style="width:100%;">';
      html += '<tr><td style="width:30%;"><strong>Capacitación:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.nombre_capacitacion || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Objetivo:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.objetivo_capacitacion || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Cliente:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.nombre_cliente || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Fecha Programada:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.fecha_programada || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Fecha de Realización:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.fecha_de_realizacion || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Estado:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.estado || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Perfil de Asistentes:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.perfil_de_asistentes || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Capacitador:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.nombre_del_capacitador || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Horas de Duración:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.horas_de_duracion_de_la_capacitacion || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Indicador de Realización:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.indicador_de_realizacion_de_la_capacitacion || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Nº Asistentes:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.numero_de_asistentes_a_capacitacion || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Total Programados:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.numero_total_de_personas_programadas || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>% Cobertura:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.porcentaje_cobertura || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Personas Evaluadas:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.numero_de_personas_evaluadas || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Promedio:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.promedio_de_calificaciones || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Observaciones:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.observaciones || '') + '</td></tr>';
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

      // Cargar clientes vía AJAX usando las claves 'id' y 'nombre'
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

      // Inicializar DataTable con fila expandible y render para inline editing
      var table = $('#cronogramaTable').DataTable({
        stateSave: true,
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
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
          url: "<?= base_url('/api/getCronogramasAjax') ?>",
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
            data: 'id_cronograma_capacitacion'
          },
          {
            data: 'acciones',
            orderable: false
          },
          {
            data: 'nombre_capacitacion',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "--" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable" data-field="nombre_capacitacion" data-id="' + row.id_cronograma_capacitacion + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          },
          {
            data: 'objetivo_capacitacion'
          },
          {
            data: 'nombre_cliente',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "--" : data;
              var displayText = data || '&nbsp;';
              return '<span data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          },
          {
            data: 'fecha_programada',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable-date" data-field="fecha_programada" data-id="' + row.id_cronograma_capacitacion + '">' + displayText + '</span>';
            }
          },
          {
            data: 'fecha_de_realizacion',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "--" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable-date" data-field="fecha_de_realizacion" data-id="' + row.id_cronograma_capacitacion + '">' + displayText + '</span>';
            }
          },
          {
            data: 'estado',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "--" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable-select" data-field="estado" data-id="' + row.id_cronograma_capacitacion + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          },
          {
            data: 'perfil_de_asistentes',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "--" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable-select" data-field="perfil_de_asistentes" data-id="' + row.id_cronograma_capacitacion + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          },
          {
            data: 'nombre_del_capacitador',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "--" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable" data-field="nombre_del_capacitador" data-id="' + row.id_cronograma_capacitacion + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          },
          {
            data: 'horas_de_duracion_de_la_capacitacion',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "--" : data;
              var displayText = data || '&nbsp;';
              // Se elimina tooltip en esta columna
              return '<span class="editable" data-field="horas_de_duracion_de_la_capacitacion" data-id="' + row.id_cronograma_capacitacion + '">' + displayText + '</span>';
            }
          },
          {
            data: 'indicador_de_realizacion_de_la_capacitacion',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "--" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable-select" data-field="indicador_de_realizacion_de_la_capacitacion" data-id="' + row.id_cronograma_capacitacion + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          },
          {
            data: 'numero_de_asistentes_a_capacitacion',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "--" : data;
              var displayText = data || '&nbsp;';
              // Se elimina tooltip en esta columna
              return '<span class="editable" data-field="numero_de_asistentes_a_capacitacion" data-id="' + row.id_cronograma_capacitacion + '">' + displayText + '</span>';
            }
          },
          {
            data: 'numero_total_de_personas_programadas',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "--" : data;
              var displayText = data || '&nbsp;';
              // Se elimina tooltip en esta columna
              return '<span class="editable" data-field="numero_total_de_personas_programadas" data-id="' + row.id_cronograma_capacitacion + '">' + displayText + '</span>';
            }
          },
          {
            data: 'porcentaje_cobertura'
          },
          {
            data: 'numero_de_personas_evaluadas',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "--" : data;
              var displayText = data || '&nbsp;';
              // Se elimina tooltip en esta columna
              return '<span class="editable" data-field="numero_de_personas_evaluadas" data-id="' + row.id_cronograma_capacitacion + '">' + displayText + '</span>';
            }
          },
          {
            data: 'promedio_de_calificaciones',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "--" : data;
              var displayText = data ? data + '%' : '&nbsp;';
              // Se elimina tooltip en esta columna
              return '<span class="editable" data-field="promedio_de_calificaciones" data-id="' + row.id_cronograma_capacitacion + '">' + displayText + '</span>';
            }
          },
          {
            data: 'observaciones',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "--" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable" data-field="observaciones" data-id="' + row.id_cronograma_capacitacion + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          }
        ],
        initComplete: function() {
          var api = this.api();
          api.columns().every(function() {
            var column = this;
            var headerIndex = column.index();
            var filterElement = $('tfoot tr.filters th').eq(headerIndex).find('.filter-search');
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

      // Filtros por columna (global o por select en tfoot)
      $('tfoot .filter-search').on('keyup change', function() {
        var index = $(this).parent().index();
        table.column(index).search(this.value).draw();
      });

      // Evento para expandir/contraer la fila (child row)
      $('#cronogramaTable tbody').on('click', 'td.details-control', function() {
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

      // Inline editing: detecta clic en celdas con clases editable, editable-select o editable-date
      $(document).on('click', '.editable, .editable-select, .editable-date', function(e) {
        e.stopPropagation(); // Evita que se active la expansión de fila
        if ($(this).find('input, select').length) return;
        var cell = $(this);
        var field = cell.data('field');
        var id = cell.data('id');
        var currentValue = cell.text().trim();
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
            options = ['PROGRAMADA', 'EJECUTADA', 'CANCELADA POR EL CLIENTE', 'REPROGRAMADA'];
          } else if (field === 'perfil_de_asistentes') {
            options = ['CONTRATISTAS', 'RESIDENTES', 'TODOS', 'ASAMBLEA', 'CONSEJO DE ADMINISTRACIÓN', 'ADMINISTRADOR'];
          } else if (field === 'indicador_de_realizacion_de_la_capacitacion') {
            options = ['SE EJECUTO EN LA FECHA O ANTES', 'SE EJECUTO DESPUES', 'DECLINADA', 'NO SE REALIZÓ'];
          }
          var select = $('<select>', {
            class: 'form-select form-select-sm'
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

      // Función para enviar la actualización vía AJAX
      function updateField(id, field, value, cell) {
        $.ajax({
          url: '<?= base_url('/api/updatecronogCapacitacion') ?>',
          method: 'POST',
          data: {
            id: id,
            field: field,
            value: value
          },
          success: function(response) {
            if (response.success) {
              console.log('Registro actualizado correctamente');
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

      // Botón para cargar datos cuando se haga clic
      $("#loadData").click(function() {
        var clientId = $("#clientSelect").val();
        if (clientId) {
          localStorage.setItem('selectedClient', clientId);
          table.ajax.reload();
        } else {
          alert('Por favor, seleccione un cliente.');
        }
      });

      // Recargar la tabla automáticamente al cambiar el select
      $('#clientSelect').on('change', function() {
        var clientId = $(this).val();
        if (clientId) {
          localStorage.setItem('selectedClient', clientId);
          table.ajax.reload();
        }
      });

      // Botón para restablecer filtros y estado guardado
      $("#clearState").on("click", function() {
        localStorage.removeItem('selectedClient');
        var storageKey = 'DataTables_' + table.table().node().id + '_' + window.location.pathname;
        localStorage.removeItem(storageKey);
        table.state.clear();
        $('tfoot .filter-search').each(function() {
          $(this).val('');
        });
        table.columns().search('').draw();
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

    $.ajax({
      url: '<?= base_url("updatecronogCapacitacion") ?>',
      type: 'POST',
      data: {
        id: id,
        field: field,
        value: value
      },
      success: function(response) {
        if (response.success) {
          // Actualiza el elemento del DOM con el nuevo valor
          $('#porcentaje-cobertura-' + id).text(response.newValue);
          // También podrías agregar un mensaje de éxito aquí si es necesario
        }
      },
      error: function(error) {
        console.log(error);
      }
    });
  </script>
</body>

</html>