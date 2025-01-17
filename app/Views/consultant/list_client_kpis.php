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
    /* Asegurarse de que nuestros estilos se apliquen */
    #kpisTable {
      table-layout: fixed !important;
      width: 100% !important;
    }
    /* Fijar la altura de cada fila de la tabla */
    #kpisTable tbody tr {
      height: 50px !important;
    }
    /* Evitar que el texto se ajuste a varias líneas y agregar puntos suspensivos */
    #kpisTable th,
    #kpisTable td {
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
      vertical-align: middle !important;
      padding: 8px !important;
      border: 1px solid #dee2e6 !important;
    }
    /* Definir anchos razonables para cada columna usando nth-child */
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
        <button id="clearState" class="btn btn-danger btn-sm" style="margin-top: 5px;">Restablecer Filtros</button>
      </div>
      
      <!-- Botón derecho -->
      <div style="text-align: center;">
        <h2 style="margin: 0; font-size: 16px;">Añadir Registro</h2>
        <a href="<?= base_url('/addClientKpi') ?>" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;" target="_blank">Añadir Registro</a>
      </div>
    </div>
  </nav>

  <div style="height: 60px;"></div>

  <div class="container my-5">
    <h2 class="text-center mb-4">Lista de KPIs de Clientes</h2>
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
          <tr>
            <th>
              <select id="clienteFilter" class="form-select">
                <option value="">Todos los Clientes</option>
              </select>
            </th>
            <th colspan="5"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($clientKpis)) : ?>
            <?php foreach ($clientKpis as $kpi) : ?>
              <tr>
                <td><?= $kpi['cliente'] ?></td>
                <td><?= $kpi['kpi'] ?></td>
                <td><?= $kpi['kpi_definition'] ?></td>
                <td><?= $kpi['kpi_target'] ?>%</td>
                <td><?= number_format($kpi['promedio_indicadores'], 2) ?>%</td>
                <td>
                  <a href="<?= base_url('/listClientKpisFull/' . $kpi['id_client_kpi']) ?>" class="btn btn-info btn-sm">Ver completo</a>
                  <a href="<?= base_url('/editClientKpi/' . $kpi['id_client_kpi']) ?>" class="btn btn-warning btn-sm">Editar</a>
                  <a href="<?= base_url('/deleteClientKpi/' . $kpi['id_client_kpi']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este KPI?')">Eliminar</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else : ?>
            <tr>
              <td colspan="6" class="text-center">No hay KPIs registrados</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
    <!-- Contenido del footer (sin cambios) -->
    <!-- ... -->
  </footer>

  <script>
    $(document).ready(function() {
      const table = $('#kpisTable').DataTable({
        stateSave: true,
        language: {
          url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
        },
        dom: 'Bfrtip',
        buttons: [
          {
            extend: 'excelHtml5',
            text: 'Exportar a Excel',
            titleAttr: 'Exportar a Excel'
          }
        ],
        initComplete: function() {
          // Rellenar select de filtro "Cliente" con valores únicos
          const clienteFilter = $('#clienteFilter');
          table.column(0).data().unique().sort().each(function(d) {
            clienteFilter.append(`<option value="${d}">${d}</option>`);
          });
        }
      });

      $('#clienteFilter').on('change', function() {
        const val = $.fn.dataTable.util.escapeRegex($(this).val());
        table.column(0).search(val ? '^' + val + '$' : '', true, false).draw();
      });

      $('#clearState').on('click', function() {
        localStorage.removeItem('DataTables_kpisTable_/');
        table.state.clear();
        $('#clienteFilter').val('');
        table.column(0).search('').draw();
      });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
