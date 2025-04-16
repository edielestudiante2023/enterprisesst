<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"> 
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lista de KPIs de Clientes</title>

  <!-- CSS de Bootstrap y DataTables -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <!-- CSS para Buttons -->
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">

  <!-- jQuery, DataTables y extensiones de Buttons -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <!-- Scripts para Buttons y exportación a Excel -->
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

  <style>
    /* Estilos para la tabla */
    #kpisTable {
      table-layout: fixed !important;
      width: 100% !important;
    }
    #kpisTable tbody tr {
      height: 50px !important;
    }
    #kpisTable th,
    #kpisTable td {
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
      vertical-align: middle !important;
      padding: 8px !important;
      border: 1px solid #dee2e6 !important;
    }
    #kpisTable th:nth-child(1),
    #kpisTable td:nth-child(1) { width: 15% !important; } /* Cliente */
    #kpisTable th:nth-child(2),
    #kpisTable td:nth-child(2) { width: 15% !important; } /* KPI */
    #kpisTable th:nth-child(3),
    #kpisTable td:nth-child(3) { width: 25% !important; } /* Definición */
    #kpisTable th:nth-child(4),
    #kpisTable td:nth-child(4) { width: 10% !important; } /* Meta */
    #kpisTable th:nth-child(5),
    #kpisTable td:nth-child(5) { width: 15% !important; } /* Promedio del Indicador */
    #kpisTable th:nth-child(6),
    #kpisTable td:nth-child(6) { width: 20% !important; } /* Acciones */
    
    /* Estilos para los selects en el footer */
    #kpisTable tfoot select {
      width: 100% !important;
      margin: 3px 0 !important;
    }
  </style>
</head>

<body class="bg-light text-dark">

  <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 10px auto 0; padding: 0 20px;">
      <!-- Botón izquierdo -->
      <div style="text-align: center;">
        <h2 style="margin: 0; font-size: 16px;">Ir a Dashboard</h2>
        <a href="<?= base_url('/dashboardconsultant') ?>" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;">Ir a DashBoard</a>
      </div>
      
      <!-- Botón para Restablecer Filtros -->
      <div style="text-align: center;">
        <h2 style="margin: 0; font-size: 16px;">Restablecer Filtros</h2>
        <!-- Redirige sin parámetros GET -->
        <a href="<?= base_url('/listClientKpis') ?>" class="btn btn-danger btn-sm" style="margin-top: 5px;">Restablecer Filtros</a>
      </div>
      
      <!-- Botón derecho -->
      <div style="text-align: center;">
        <h2 style="margin: 0; font-size: 16px;">Añadir Registro</h2>
        <a href="<?= base_url('/addClientKpi') ?>" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;" target="_blank">Añadir Registro</a>
      </div>
    </div>
  </nav>

  <div style="height: 60px;"></div>

  <div class="container-fluid my-5">
    <h2 class="text-center mb-4">Lista de KPIs de Clientes</h2>
    
    <!-- Formulario para filtrar por Cliente -->
    <form method="GET" action="<?= base_url('/listClientKpis') ?>" class="mb-4">
      <div class="row g-2 align-items-end">
        <div class="col-md-4">
          <label for="id_cliente" class="form-label">Filtrar por Cliente:</label>
          <select name="id_cliente" id="id_cliente" class="form-select">
            <option value="">Todos los Clientes</option>
            <?php if (!empty($clientes)) : ?>
              <?php foreach ($clientes as $cliente): ?>
                <option value="<?= $cliente['id_cliente'] ?>" <?= (isset($selectedCliente) && $selectedCliente == $cliente['id_cliente']) ? 'selected' : '' ?>>
                  <?= $cliente['nombre_cliente'] ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
      </div>
    </form>
    
    <!-- Tabla de KPIs -->
    <div class="table-responsive">
      <table id="kpisTable" class="table table-striped table-bordered">
        <thead class="table-light">
          <tr>
            <th>Cliente</th>
            <th>KPI</th>
            <th>Definición</th>
            <th>Meta</th>
            <th>Promedio del Indicador</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($clientKpis)) : ?>
            <?php foreach ($clientKpis as $kpi) : ?>
              <tr>
                <td><?= $kpi['cliente'] ?></td>
                <td data-bs-toggle="tooltip" data-bs-placement="top" title="<?= $kpi['kpi'] ?>">
                  <?= $kpi['kpi'] ?>
                </td>
                <td data-bs-toggle="tooltip" data-bs-placement="top" title="<?= $kpi['kpi_definition'] ?>">
                  <?= $kpi['kpi_definition'] ?>
                </td>
                <td><?= $kpi['kpi_target'] ?>%</td>
                <td><?= number_format($kpi['promedio_indicadores'], 2) ?>%</td>
                <td>
                  <a href="<?= base_url('listClientKpisFull') ?>" class="btn btn-info btn-sm" target="_blank" rel="noopener noreferrer">Ver completo</a>
                  <a href="<?= base_url('/editClientKpi/' . $kpi['id_client_kpi']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-warning btn-sm">Editar</a>
                  <a href="<?= base_url('/deleteClientKpi/' . $kpi['id_client_kpi']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este KPI?')">Eliminar</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr>
            <th><select class="form-select form-select-sm"><option value="">Todos</option></select></th>
            <th><select class="form-select form-select-sm"><option value="">Todos</option></select></th>
            <th><select class="form-select form-select-sm"><option value="">Todos</option></select></th>
            <th><select class="form-select form-select-sm"><option value="">Todos</option></select></th>
            <th><select class="form-select form-select-sm"><option value="">Todos</option></select></th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
    <!-- Contenido del footer -->
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    $(document).ready(function() {
      // Inicializar tooltips de Bootstrap
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
      });

      // Inicializar DataTable con configuración mejorada
      var table = $('#kpisTable').DataTable({
        stateSave: true,
        language: {
          url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
          emptyTable: "No hay KPIs registrados"
        },
        dom: 'Bfrtip',
        buttons: [
          {
            extend: 'excelHtml5',
            text: 'Exportar a Excel',
            titleAttr: 'Exportar a Excel',
            className: 'btn btn-success btn-sm'
          }
        ],
        initComplete: function() {
          // Configurar filtros para cada columna (excepto Acciones)
          this.api().columns().every(function(index) {
            if (index === 5) return; // Saltar columna de Acciones
            
            var column = this;
            var select = $('select', column.footer())
              .empty()
              .append('<option value="">Todos</option>')
              .on('change', function() {
                var val = $(this).val();
                
                // Manejo especial para columnas de porcentaje
                if (index === 3 || index === 4) {
                  column.search(val ? '^' + val + '\\s*%?$' : '', true, false).draw();
                } else {
                  column.search(val).draw();
                }
              });
            
            // Obtener datos para los filtros
            column.data().unique().sort().each(function(d) {
              // Para columnas con porcentajes
              if (index === 3 || index === 4) {
                var num = d.toString().replace('%', '').trim();
                if (num && !isNaN(num)) {
                  select.append('<option value="' + num + '">' + num + '%</option>');
                }
              } 
              // Para otras columnas
              else {
                var text = $('<div>').html(d).text().trim();
                if (text) {
                  select.append('<option value="' + text + '">' + text + '</option>');
                }
              }
            });
          });
        }
      });

      // Restablecer filtros al hacer clic en el botón de restablecer
      $('a[href="<?= base_url('/listClientKpis') ?>"]').on('click', function() {
        table.state.clear();
        window.location.href = $(this).attr('href');
      });
    });
  </script>
</body>
</html>
