<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Plan de Trabajo Anual</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
  <style>
    body { padding: 20px; }
    .dataTables_wrapper .dataTables_filter { float: right; text-align: right; }
    .dt-buttons { margin-bottom: 15px; }
    .dt-buttons .btn { margin-right: 5px; }
    /* Opcional: ajustes para el navbar y footer */
  </style>
</head>
<body>
  <!-- Navbar fijo -->
  <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
      <div class="container d-flex justify-content-between align-items-center">
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
  </nav>

  <!-- Espaciador para el navbar -->
  <div style="height: 160px;"></div>

  <div class="container-fluid">
    <!-- Tarjetas de conteo superiores -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card text-white bg-primary">
          <div class="card-body">
            <h5 class="card-title">Activas</h5>
            <p class="card-text" id="countActivas">0</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-danger">
          <div class="card-body">
            <h5 class="card-title">Cerradas</h5>
            <p class="card-text" id="countCerradas">0</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-warning">
          <div class="card-body">
            <h5 class="card-title">Gestionando</h5>
            <p class="card-text" id="countGestionando">0</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-secondary">
          <div class="card-body">
            <h5 class="card-title">Total</h5>
            <p class="card-text" id="countTotal">0</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tarjetas mensuales -->
    <div class="row mb-4">
      <?php
      $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Sept.', 'Oct.', 'Nov.', 'Dic.'];
      $ids = ['countEnero', 'countFebrero', 'countMarzo', 'countAbril', 'countMayo', 'countJunio', 'countJulio', 'countAgosto', 'countSeptiembre', 'countOctubre', 'countNoviembre', 'countDiciembre'];
      foreach ($meses as $i => $mes): ?>
          <div class="col-6 col-md-1">
              <div class="card text-white bg-info">
                  <div class="card-body p-2">
                      <h6 class="card-title text-center mb-0"><?= $mes ?></h6>
                      <p class="card-text text-center" id="<?= $ids[$i] ?>">0</p>
                  </div>
              </div>
          </div>
      <?php endforeach; ?>
    </div>

    <!-- Título y nombre del cliente -->
    <div class="text-center mb-4">
      <h2 class="mb-2">Plan de Trabajo Anual</h2>
      <h3 class="mb-4"><?= esc($nombre_cliente) ?></h3>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
      <div class="col-md-3">
        <label for="estadoFilter" class="form-label">Estado de Actividad</label>
        <select id="estadoFilter" class="form-select">
          <option value="">Todos</option>
          <option value="ABIERTA">ABIERTA</option>
          <option value="CERRADA">CERRADA</option>
          <option value="GESTIONANDO">GESTIONANDO</option>
        </select>
      </div>
      <div class="col-md-3">
        <label for="fechaDesde" class="form-label">Fecha Desde</label>
        <input type="date" id="fechaDesde" class="form-control">
      </div>
      <div class="col-md-3">
        <label for="fechaHasta" class="form-label">Fecha Hasta</label>
        <input type="date" id="fechaHasta" class="form-control">
      </div>
      <div class="col-md-3">
        <label for="mesSeleccionado" class="form-label">Mes / Todo el Año</label>
        <select id="mesSeleccionado" class="form-select">
          <option value="">-- Seleccione una opción --</option>
          <option value="all">Todo el Año</option>
          <option value="1">Enero</option>
          <option value="2">Febrero</option>
          <option value="3">Marzo</option>
          <option value="4">Abril</option>
          <option value="5">Mayo</option>
          <option value="6">Junio</option>
          <option value="7">Julio</option>
          <option value="8">Agosto</option>
          <option value="9">Septiembre</option>
          <option value="10">Octubre</option>
          <option value="11">Noviembre</option>
          <option value="12">Diciembre</option>
        </select>
      </div>
    </div>

    <!-- Botones de acción -->
    <div class="d-flex justify-content-end gap-2 mb-3">
      <button id="aplicarFiltros" class="btn btn-primary">Aplicar Filtros</button>
      <button id="clearState" class="btn btn-secondary">Restablecer Filtros</button>
    </div>

    <!-- Tabla -->
    <div class="table-responsive">
      <table id="planesTable" class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>Estado de Actividad</th>
            <th>Fecha Propuesta</th>
            <th>PHVA</th>
            <th>Actividad</th>
            <th>Fecha Cierre</th>
            <th>Responsable</th>
            <th>Porcentaje de Avance</th>
            <th>Observaciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($planes as $plan): ?>
          <tr>
            <td><?= esc($plan['estado_actividad']) ?></td>
            <!-- Se asume que en el controlador ya se formateó la fecha a DD-MM-YYYY -->
            <td><?= esc($plan['fecha_propuesta']) ?></td>
            <td><?= esc($plan['phva_plandetrabajo']) ?></td>
            <td><?= esc($plan['nombre_actividad']) ?></td>
            <td><?= esc($plan['fecha_cierre']) ?></td>
            <td><?= esc($plan['responsable_sugerido_plandetrabajo']) ?></td>
            <td><?= esc($plan['porcentaje_avance']) ?>%</td>
            <td><?= esc($plan['observaciones']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Footer -->
  <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
      <div class="container d-flex flex-column align-items-center">
          <p class="fw-bold mb-0">Cycloid Talent SAS</p>
          <p class="mb-2">Todos los derechos reservados © 2024</p>
          <p class="mb-2">NIT: 901.653.912</p>
          <p class="mb-2">
              Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" class="text-primary text-decoration-none">https://cycloidtalent.com/</a>
          </p>
          <p class="mb-3"><strong>Nuestras Redes Sociales:</strong></p>
          <div class="d-flex gap-3 justify-content-center">
              <a href="https://www.facebook.com/CycloidTalent" target="_blank" class="text-dark">
                  <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
              </a>
              <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" class="text-dark">
                  <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
              </a>
              <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" class="text-dark">
                  <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
              </a>
              <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" class="text-dark">
                  <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
              </a>
          </div>
      </div>
  </footer>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
  <script src="https://cdn.datatables.net/plug-ins/1.13.4/sorting/datetime-moment.js"></script>
  
  <script>
    $(document).ready(function() {
      // Configuramos moment.js para el formato DD-MM-YYYY que se muestra en la tabla
      $.fn.dataTable.moment('DD-MM-YYYY');

      // Inicializar DataTable
      var table = $('#planesTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json' },
        pageLength: 10,
        responsive: true,
        dom: 'Bfrtip',
        order: [[1, 'asc']],
        buttons: [{
          extend: 'excelHtml5',
          text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
          className: 'btn btn-success',
          title: 'Plan de Trabajo',
          exportOptions: { columns: ':visible' }
        }]
      });

      // Función de búsqueda personalizada para rango de fechas
      $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var min = $('#fechaDesde').val(); // En formato YYYY-MM-DD
        var max = $('#fechaHasta').val(); // En formato YYYY-MM-DD
        var fechaPropuesta = data[1] || "";
        // Convertir la fecha de la tabla (DD-MM-YYYY) a YYYY-MM-DD para comparar
        var fecha = moment(fechaPropuesta, "DD-MM-YYYY").format("YYYY-MM-DD");

        if ((min === "" && max === "") ||
            (min === "" && fecha <= max) ||
            (min <= fecha && max === "") ||
            (min <= fecha && fecha <= max)) {
          return true;
        }
        return false;
      });

      // Función para actualizar las tarjetas superiores
      function updateCardCounts() {
        var data = table.column(0, {search: 'applied'}).data().toArray();
        var countActivas = data.filter(function(x) { return x.trim() === 'ABIERTA'; }).length;
        var countCerradas = data.filter(function(x) { return x.trim() === 'CERRADA'; }).length;
        var countGestionando = data.filter(function(x) { return x.trim() === 'GESTIONANDO'; }).length;
        $('#countActivas').text(countActivas);
        $('#countCerradas').text(countCerradas);
        $('#countGestionando').text(countGestionando);
        $('#countTotal').text(table.rows({search: 'applied'}).data().length);
      }

      // Función para actualizar tarjetas mensuales
      function updateMonthlyCounts() {
        var monthlyCounts = Array(12).fill(0);
        var data = table.rows({search: 'applied'}).data().toArray();
        data.forEach(function(row) {
          var fechaPropuesta = row[1]; // columna "Fecha Propuesta"
          if (fechaPropuesta) {
            var parts = fechaPropuesta.split("-");
            if (parts.length >= 3) {
              var month = parseInt(parts[1], 10);
              if (!isNaN(month) && month >= 1 && month <= 12) {
                monthlyCounts[month - 1]++;
              }
            }
          }
        });
        var monthIds = ["countEnero", "countFebrero", "countMarzo", "countAbril", "countMayo", "countJunio", 
                        "countJulio", "countAgosto", "countSeptiembre", "countOctubre", "countNoviembre", "countDiciembre"];
        monthIds.forEach(function(id, index) {
          $('#' + id).text(monthlyCounts[index]);
        });
      }

      // Cada vez que se dibuje la tabla, se actualizan las tarjetas
      table.on('draw', function() {
        updateCardCounts();
        updateMonthlyCounts();
      });
      updateCardCounts();
      updateMonthlyCounts();

      // Función para aplicar filtros (incluye estado y fecha)
      function aplicarFiltros() {
        var estado = $('#estadoFilter').val();
        table.column(0).search(estado);
        table.draw();
      }

      $('#aplicarFiltros').click(function() {
        aplicarFiltros();
      });

      // Actualizar inputs de fecha según el mes seleccionado
      $('#mesSeleccionado').on('change', function() {
        var valor = $(this).val();
        var anio = new Date().getFullYear();
        var primerDia, ultimoDia;
        if (valor === "all") {
          primerDia = new Date(anio, 0, 1);
          ultimoDia = new Date(anio, 11, 31);
        } else {
          var mes = parseInt(valor);
          if (!mes) return;
          primerDia = new Date(anio, mes - 1, 1);
          ultimoDia = new Date(anio, mes, 0);
        }
        function formatearFecha(fecha) {
          var dia = ("0" + fecha.getDate()).slice(-2);
          var mesFormateado = ("0" + (fecha.getMonth() + 1)).slice(-2);
          return fecha.getFullYear() + '-' + mesFormateado + '-' + dia;
        }
        $('#fechaDesde').val(formatearFecha(primerDia));
        $('#fechaHasta').val(formatearFecha(ultimoDia));
      });

      // Botón para restablecer filtros
      $('#clearState').click(function() {
        $('#estadoFilter').val('');
        $('#fechaDesde').val('');
        $('#fechaHasta').val('');
        $('#mesSeleccionado').val('');
        table.column(0).search('');
        table.draw();
      });

    });
  </script>
</body>
</html>
