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
                <optgroup label="Roles Internos">
                  <option value="TODOS">TODOS</option>
                  <option value="MIEMBROS_COPASST">Miembros del COPASST</option>
                  <option value="RESPONSABLE_SST">Responsable de SST</option>
                  <option value="SUPERVISORES">Supervisores o Jefes de Área</option>
                  <option value="TRABAJADORES_REPRESENTANTES">Trabajadores Representantes</option>
                  <option value="MIEMBROS_COMITE_CONVIVENCIA">Miembros del Comité de Convivencia Laboral</option>
                  <option value="RECURSOS_HUMANOS">Departamento de Recursos Humanos</option>
                  <option value="PERSONAL_MANTENIMIENTO">Personal de Mantenimiento o Producción</option>
                  <option value="ENCARGADO_AMBIENTAL">Encargado de Gestión Ambiental</option>
                  <option value="TRABAJADORES_RIESGOS_CRITICOS">Trabajadores con Riesgos Críticos</option>
                </optgroup>
                <optgroup label="Roles Externos">
                  <option value="ASESOR_SST">Asesor o Consultor en SST</option>
                  <option value="AUDITOR_EXTERNO">Auditores Externos</option>
                  <option value="CAPACITADOR_EXTERNO">Capacitadores Externos</option>
                  <option value="CONTRATISTAS">Contratistas y Proveedores</option>
                  <option value="INSPECTORES_GUBERNAMENTALES">Inspectores Gubernamentales</option>
                  <option value="FISIOTERAPEUTAS_ERGONOMOS">Fisioterapeutas o Ergónomos</option>
                  <option value="TECNICOS_ESPECIALIZADOS">Técnicos en Riesgos Especializados</option>
                  <option value="BRIGADISTAS_EXTERNOS">Brigadistas o Personal de Emergencias Externo</option>
                  <option value="REPRESENTANTES_ARL">Representantes de Aseguradoras (ARL)</option>
                  <option value="AUDITORES_ISO">Auditores de Normas ISO</option>
                </optgroup>
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
    // 1. Definimos la lista de perfiles (value + label) una sola vez
    const perfiles = [
      // Internos
      {
        value: 'TODOS',
        label: 'TODOS'
      },
      {
        value: 'MIEMBROS_COPASST',
        label: 'Miembros del COPASST'
      },
      {
        value: 'RESPONSABLE_SST',
        label: 'Responsable de SST'
      },
      {
        value: 'SUPERVISORES',
        label: 'Supervisores o Jefes de Área'
      },
      {
        value: 'TRABAJADORES_REPRESENTANTES',
        label: 'Trabajadores Representantes'
      },
      {
        value: 'MIEMBROS_COMITE_CONVIVENCIA',
        label: 'Miembros del Comité de Convivencia Laboral'
      },
      {
        value: 'RECURSOS_HUMANOS',
        label: 'Departamento de Recursos Humanos'
      },
      {
        value: 'PERSONAL_MANTENIMIENTO',
        label: 'Personal de Mantenimiento o Producción'
      },
      {
        value: 'ENCARGADO_AMBIENTAL',
        label: 'Encargado de Gestión Ambiental'
      },
      {
        value: 'TRABAJADORES_RIESGOS_CRITICOS',
        label: 'Trabajadores con Riesgos Críticos'
      },
      // Externos
      {
        value: 'ASESOR_SST',
        label: 'Asesor o Consultor en SST'
      },
      {
        value: 'AUDITOR_EXTERNO',
        label: 'Auditores Externos'
      },
      {
        value: 'CAPACITADOR_EXTERNO',
        label: 'Capacitadores Externos'
      },
      {
        value: 'CONTRATISTAS',
        label: 'Contratistas y Proveedores'
      },
      {
        value: 'INSPECTORES_GUBERNAMENTALES',
        label: 'Inspectores Gubernamentales'
      },
      {
        value: 'FISIOTERAPEUTAS_ERGONOMOS',
        label: 'Fisioterapeutas o Ergónomos'
      },
      {
        value: 'TECNICOS_ESPECIALIZADOS',
        label: 'Técnicos en Riesgos Especializados'
      },
      {
        value: 'BRIGADISTAS_EXTERNOS',
        label: 'Brigadistas o Personal de Emergencias Externo'
      },
      {
        value: 'REPRESENTANTES_ARL',
        label: 'Representantes de Aseguradoras (ARL)'
      },
      {
        value: 'AUDITORES_ISO',
        label: 'Auditores de Normas ISO'
      }
    ];

    // 2. Función para formatear la fila expandible
    function format(rowData) {
      let html = '<table class="table table-sm table-borderless" style="width:100%;">';
      const filas = [
        ['Capacitación', rowData.nombre_capacitacion],
        ['Objetivo', rowData.objetivo_capacitacion],
        ['Cliente', rowData.nombre_cliente],
        ['Fecha Programada', rowData.fecha_programada],
        ['Fecha de Realización', rowData.fecha_de_realizacion],
        ['Estado', rowData.estado],
        ['Perfil de Asistentes', rowData.perfil_de_asistentes],
        ['Capacitador', rowData.nombre_del_capacitador],
        ['Horas de Duración', rowData.horas_de_duracion_de_la_capacitacion],
        ['Indicador de Realización', rowData.indicador_de_realizacion_de_la_capacitacion],
        ['Nº Asistentes', rowData.numero_de_asistentes_a_capacitacion],
        ['Total Programados', rowData.numero_total_de_personas_programadas],
        ['% Cobertura', rowData.porcentaje_cobertura],
        ['Personas Evaluadas', rowData.numero_de_personas_evaluadas],
        ['Promedio', rowData.promedio_de_calificaciones],
        ['Observaciones', rowData.observaciones]
      ];
      filas.forEach(([label, val]) => {
        html += `<tr><td style="width:30%;"><strong>${label}:</strong></td>` +
          `<td style="width:70%; overflow:auto;">${val||''}</td></tr>`;
      });
      html += '</table>';
      return html;
    }

    $(document).ready(function() {
      // 3. Inicializar Select2 para clientes
      $('#clientSelect').select2({
        placeholder: 'Seleccione un cliente',
        allowClear: true,
        width: '100%'
      });
      $.getJSON("<?= base_url('/api/getClientes') ?>", data => {
        data.forEach(c => $('#clientSelect').append(`<option value="${c.id}">${c.nombre}</option>`));
        const sel = localStorage.getItem('selectedClient');
        if (sel) $('#clientSelect').val(sel).trigger('change');
      });

      // 4. Inicializar DataTable
      const table = $('#cronogramaTable').DataTable({
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
          data: d => d.cliente = $('#clientSelect').val(),
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
          // ... tus columnas previas ...
          {
            data: 'perfil_de_asistentes',
            render: (d, t, row) => {
              const txt = d || '&nbsp;';
              return `<span class="editable-select" data-field="perfil_de_asistentes" data-id="${row.id_cronograma_capacitacion}"` +
                ` data-bs-toggle="tooltip" title="${d||''}">${txt}</span>`;
            }
          },
          // ... resto de columnas ...
        ],
        initComplete() {
          /* tu lógica de filtros dinámicos */ }
      });
      table.buttons().container().appendTo('#buttonsContainer');

      // 5. Expandir / contraer fila
      $('#cronogramaTable tbody').on('click', 'td.details-control', function() {
        const tr = $(this).closest('tr'),
          row = table.row(tr);
        row.child.isShown() ? row.child.hide() && tr.removeClass('shown') :
          row.child(format(row.data())).show() && tr.addClass('shown');
      });

      // 6. Inline editing
      $(document).on('click', '.editable, .editable-select, .editable-date', function(e) {
        e.stopPropagation();
        const cell = $(this),
          field = cell.data('field'),
          id = cell.data('id'),
          curr = cell.text().trim() || '';

        if (cell.hasClass('editable-date')) {
          const inp = $('<input type="date" class="form-control form-control-sm">').val(curr);
          cell.html(inp).find('input').focus().on('blur change', () => {
            const v = inp.val();
            cell.html(v || '&nbsp;');
            updateField(id, field, v);
          });

        } else if (cell.hasClass('editable-select')) {
          let opts = [];
          if (field === 'estado') {
            opts = ['PROGRAMADA', 'EJECUTADA', 'CANCELADA POR EL CLIENTE', 'REPROGRAMADA']
              .map(v => ({
                value: v,
                label: v
              }));
          } else if (field === 'perfil_de_asistentes') {
            opts = perfiles;
          } else if (field === 'indicador_de_realizacion_de_la_capacitacion') {
            opts = ['SE EJECUTO EN LA FECHA O ANTES', 'SE EJECUTO DESPUES', 'DECLINADA', 'NO SE REALIZÓ']
              .map(v => ({
                value: v,
                label: v
              }));
          }
          const sel = $('<select class="form-select form-select-sm">');
          opts.forEach(o => sel.append(new Option(o.label, o.value, o.value === curr, o.value === curr)));
          cell.html(sel).find('select').focus().on('blur change', () => {
            const v = sel.val();
            cell.html(v || '&nbsp;');
            updateField(id, field, v);
          });

        } else {
          const inp = $('<input type="text" class="form-control form-control-sm">').val(curr);
          cell.html(inp).find('input').focus().on('blur', () => {
            const v = inp.val();
            cell.html(v || '&nbsp;');
            updateField(id, field, v);
          });
        }
      });

      // 7. Función AJAX de actualización
      function updateField(id, field, value) {
        $.post('<?= base_url('/api/updatecronogCapacitacion') ?>', {
            id,
            field,
            value
          })
          .fail(() => alert('Error al comunicarse con el servidor.'));
      }

      // 8. Cargar/restablecer filtros
      $('#loadData').click(() => {
        const c = $('#clientSelect').val();
        if (c) {
          localStorage.setItem('selectedClient', c);
          table.ajax.reload();
        } else alert('Seleccione un cliente.');
      });
      $('#clientSelect').change(() => $('#loadData').click());
      $('#clearState').click(() => {
        localStorage.removeItem('selectedClient');
        table.state.clear();
        table.columns().search('').draw();
        $('#clientSelect').val(null).trigger('change');
      });

      // 9. Tooltips
      function initTooltips() {
        $('[data-bs-toggle="tooltip"]').each((_, el) => new bootstrap.Tooltip(el));
      }
      initTooltips();
      table.on('draw.dt', initTooltips);
    });
  </script>

</body>

</html>