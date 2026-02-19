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

    /* === UX: Estado Badges === */
    .estado-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.78rem;
      font-weight: 600;
      letter-spacing: 0.3px;
      text-transform: uppercase;
      white-space: nowrap;
    }
    .estado-abierta     { background: #cce5ff; color: #004085; }
    .estado-cerrada     { background: #f8d7da; color: #721c24; }
    .estado-gestionando { background: #fff3cd; color: #856404; }

    /* === UX: Mini Progress Bar === */
    .mini-progress { display: flex; align-items: center; gap: 6px; min-width: 100px; }
    .mini-progress-bar { flex: 1; height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden; }
    .mini-progress-fill { height: 100%; border-radius: 4px; transition: width .3s; }
    .mini-progress-text { font-size: 0.78rem; font-weight: 600; white-space: nowrap; }

    /* === UX: Tabla estilizada === */
    #planesTable thead th {
      background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
      color: #fff !important;
      font-size: 0.82rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 10px 8px;
      border-bottom: 2px solid #1a3a8a;
      white-space: nowrap;
    }
    #planesTable tbody td {
      padding: 6px 8px;
      vertical-align: middle;
      font-size: 0.85rem;
    }
    #planesTable tbody tr:hover { background-color: #eef2ff !important; }

    /* === UX: Texto truncado expandible === */
    .col-truncate { max-width: 250px !important; }
    .cell-truncate {
      max-height: 60px;
      overflow: hidden;
      transition: max-height .3s ease;
      position: relative;
    }
    .cell-truncate.expanded { max-height: none; }
    .btn-expand {
      display: inline-block;
      font-size: 0.72rem;
      color: #4e73df;
      cursor: pointer;
      margin-top: 2px;
      font-weight: 600;
    }
    .btn-expand:hover { text-decoration: underline; }

    /* === UX: Accordion filtros === */
    .filter-toggle-btn {
      background: none;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      padding: 4px 14px;
      font-size: 0.82rem;
      color: #6c757d;
      cursor: pointer;
      transition: all .2s;
    }
    .filter-toggle-btn:hover { background: #f8f9fa; color: #4e73df; }
    .filter-toggle-btn i { transition: transform .2s; }
    .filter-toggle-btn.collapsed i { transform: rotate(-90deg); }
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

    <!-- Accordion: Estado + Mes -->
    <div class="d-flex justify-content-end mb-2">
      <button class="filter-toggle-btn" data-bs-toggle="collapse" data-bs-target="#cardFiltersPanel" aria-expanded="true">
        <i class="fas fa-chevron-down"></i> Estado y Mes
      </button>
    </div>
    <div class="collapse show" id="cardFiltersPanel">

    <!-- Tarjetas de Estados (clickeables) -->
    <div class="section-title">
      <i class="fas fa-tasks"></i> Filtrar por Estado
    </div>
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card text-white bg-primary card-clickable card-status" data-status="ABIERTA">
          <div class="card-body text-center">
            <h5 class="card-title">Activas</h5>
            <p class="card-text display-6" id="countActivas">0</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-danger card-clickable card-status" data-status="CERRADA">
          <div class="card-body text-center">
            <h5 class="card-title">Cerradas</h5>
            <p class="card-text display-6" id="countCerradas">0</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-warning card-clickable card-status" data-status="GESTIONANDO">
          <div class="card-body text-center">
            <h5 class="card-title">Gestionando</h5>
            <p class="card-text display-6" id="countGestionando">0</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-secondary">
          <div class="card-body text-center">
            <h5 class="card-title">Total</h5>
            <p class="card-text display-6" id="countTotal">0</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tarjetas mensuales (clickeables) -->
    <div class="section-title">
      <i class="fas fa-calendar-week"></i> Filtrar por Mes
    </div>
    <div class="row mb-4">
      <?php
      $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Sept.', 'Oct.', 'Nov.', 'Dic.'];
      $ids = ['countEnero', 'countFebrero', 'countMarzo', 'countAbril', 'countMayo', 'countJunio', 'countJulio', 'countAgosto', 'countSeptiembre', 'countOctubre', 'countNoviembre', 'countDiciembre'];
      foreach ($meses as $i => $mes): ?>
          <div class="col-6 col-md-1">
              <div class="card text-white bg-info card-clickable card-month" data-month="<?= $i + 1 ?>">
                  <div class="card-body p-2">
                      <h6 class="card-title text-center mb-0"><?= $mes ?></h6>
                      <p class="card-text text-center" id="<?= $ids[$i] ?>">0</p>
                  </div>
              </div>
          </div>
      <?php endforeach; ?>
    </div>

    </div><!-- /cardFiltersPanel -->

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
        <thead>
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
            <td><?php
              $est = esc($plan['estado_actividad']);
              $cls = 'estado-abierta';
              if ($est === 'CERRADA') $cls = 'estado-cerrada';
              elseif ($est === 'GESTIONANDO') $cls = 'estado-gestionando';
              echo '<span class="estado-badge ' . $cls . '">' . $est . '</span>';
            ?></td>
            <!-- Se asume que en el controlador ya se formateó la fecha a DD-MM-YYYY -->
            <td><?= esc($plan['fecha_propuesta']) ?></td>
            <td><?= esc($plan['phva_plandetrabajo']) ?></td>
            <td class="col-truncate"><div class="cell-truncate"><?= esc($plan['nombre_actividad']) ?></div></td>
            <td><?= esc($plan['fecha_cierre']) ?></td>
            <td><?= esc($plan['responsable_sugerido_plandetrabajo']) ?></td>
            <td><?php
              $pct = floatval($plan['porcentaje_avance']);
              $color = '#e74a3b';
              if ($pct >= 100) $color = '#1cc88a';
              elseif ($pct >= 50) $color = '#4e73df';
              elseif ($pct > 0) $color = '#f6c23e';
              $w = max($pct, 2);
              echo '<div class="mini-progress"><div class="mini-progress-bar"><div class="mini-progress-fill" style="width:' . $w . '%;background:' . $color . '"></div></div><span class="mini-progress-text">' . $pct . '%</span></div>';
            ?></td>
            <td class="col-truncate"><div class="cell-truncate"><?= esc($plan['observaciones']) ?></div></td>
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
      // Variables globales para filtros activos
      var activeYear = null;
      var activeMonth = null;
      var activeStatus = null;

      // Helper: extraer texto plano de HTML
      function stripHtml(html) {
        var tmp = document.createElement('DIV');
        tmp.innerHTML = html;
        return (tmp.textContent || tmp.innerText || '').trim();
      }

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
          exportOptions: {
            columns: ':visible',
            format: {
              body: function(data) {
                return stripHtml(data);
              }
            }
          }
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
        var countActivas = data.filter(function(x) { return stripHtml(x) === 'ABIERTA'; }).length;
        var countCerradas = data.filter(function(x) { return stripHtml(x) === 'CERRADA'; }).length;
        var countGestionando = data.filter(function(x) { return stripHtml(x) === 'GESTIONANDO'; }).length;
        $('#countActivas').text(countActivas);
        $('#countCerradas').text(countCerradas);
        $('#countGestionando').text(countGestionando);
        $('#countTotal').text(table.rows({search: 'applied'}).data().length);
      }

      // Generar tarjetas de años dinámicamente
      function generateYearCards() {
        if (!table) return;

        var yearCounts = {};

        // Contar actividades por año basado en fecha_propuesta (columna 1, formato DD-MM-YYYY)
        table.rows({search: 'applied'}).every(function() {
          var data = this.data();
          var fechaPropuesta = data[1]; // Columna 1: Fecha Propuesta (DD-MM-YYYY)
          if (fechaPropuesta && fechaPropuesta.trim() !== '') {
            var parts = fechaPropuesta.trim().split("-");
            if (parts.length === 3) {
              var year = parts[2].trim(); // El año está en la tercera posición (DD-MM-YYYY)
              if (year && year.length === 4) {
                yearCounts[year] = (yearCounts[year] || 0) + 1;
              }
            }
          }
        });

        var yearArray = Object.keys(yearCounts).sort().reverse();
        var yearCardsHtml = '';

        if (yearArray.length === 0) {
          yearCardsHtml = '<div class="col-12"><p class="text-muted text-center">No hay datos para mostrar</p></div>';
        } else {
          yearArray.forEach(function(year) {
            var count = yearCounts[year];
            yearCardsHtml += `
              <div class="col-6 col-md-2">
                <div class="card text-white card-year card-clickable" data-year="${year}">
                  <div class="card-body text-center p-3">
                    <h4 class="card-title mb-1">${year}</h4>
                    <p class="mb-0" style="font-size: 1.5rem; font-weight: bold;">${count}</p>
                    <small style="font-size: 0.75rem;">actividades</small>
                  </div>
                </div>
              </div>
            `;
          });
        }

        $('#yearCards').html(yearCardsHtml);
      }

      // Función para actualizar tarjetas mensuales
      function updateMonthlyCounts() {
        var monthlyCounts = Array(12).fill(0);
        var data = table.rows({search: 'applied'}).data().toArray();
        data.forEach(function(row) {
          var fechaPropuesta = row[1]; // columna "Fecha Propuesta" (DD-MM-YYYY)
          if (fechaPropuesta) {
            var parts = fechaPropuesta.split("-");
            if (parts.length === 3) {
              var month = parseInt(parts[1], 10); // El mes está en la segunda posición
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

      // Función para aplicar filtros de tarjetas combinados
      function applyCardFilters() {
        if (!table) return;

        // Limpiar filtros personalizados previos (mantener solo el filtro de rango de fechas)
        var currentSearch = $.fn.dataTable.ext.search.slice();
        $.fn.dataTable.ext.search = currentSearch.filter(function(fn) {
          // Mantener solo el filtro de rango de fechas (el primero que se agregó)
          return fn.toString().indexOf('fechaDesde') !== -1;
        });

        // Añadir filtro personalizado para tarjetas
        $.fn.dataTable.ext.search.push(
          function(settings, data, dataIndex) {
            var fechaPropuesta = (data[1] || '').trim(); // Columna 1: Fecha Propuesta (DD-MM-YYYY)
            var estado = stripHtml(data[0] || ''); // Columna 0: Estado (stripHtml para badges)

            // Filtro por año
            if (activeYear) {
              if (fechaPropuesta) {
                var parts = fechaPropuesta.split("-");
                if (parts.length === 3) {
                  var year = parts[2].trim();
                  if (year !== activeYear.toString()) {
                    return false;
                  }
                } else {
                  return false;
                }
              } else {
                return false;
              }
            }

            // Filtro por mes
            if (activeMonth) {
              if (fechaPropuesta) {
                var parts = fechaPropuesta.split("-");
                if (parts.length === 3) {
                  var month = parseInt(parts[1].trim(), 10);
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
              if (estado !== activeStatus) {
                return false;
              }
            }

            return true;
          }
        );

        table.draw();
        generateYearCards();
        updateCardCounts();
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

        applyCardFilters();
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

        applyCardFilters();
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

        applyCardFilters();
      });

      // Botón para limpiar filtros de tarjetas
      $('#btnClearCardFilters').on('click', function() {
        activeYear = null;
        activeMonth = null;
        activeStatus = null;

        $('.card-year').removeClass('active');
        $('.card-month').removeClass('active');
        $('.card-status').removeClass('active');

        // Limpiar filtros personalizados de tarjetas (mantener el de fechas)
        var currentSearch = $.fn.dataTable.ext.search.slice();
        $.fn.dataTable.ext.search = currentSearch.filter(function(fn) {
          return fn.toString().indexOf('fechaDesde') !== -1;
        });

        if (table) {
          table.draw();
          generateYearCards();
          updateCardCounts();
          updateMonthlyCounts();
        }
      });

      // Cada vez que se dibuje la tabla, se actualizan las tarjetas
      table.on('draw', function() {
        updateCardCounts();
        updateMonthlyCounts();
        generateYearCards();
      });
      updateCardCounts();
      updateMonthlyCounts();
      generateYearCards();

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

      // === UX: Texto truncado con ver más / ver menos ===
      function initTruncateButtons() {
        $('#planesTable .cell-truncate').each(function() {
          var $cell = $(this);
          $cell.removeClass('expanded');
          $cell.next('.btn-expand').remove();
          if (this.scrollHeight > 62) {
            $cell.after('<span class="btn-expand">ver más</span>');
          }
        });
      }
      $(document).on('click', '.btn-expand', function() {
        var $btn = $(this);
        var $cell = $btn.prev('.cell-truncate');
        if ($cell.hasClass('expanded')) {
          $cell.removeClass('expanded');
          $btn.text('ver más');
        } else {
          $cell.addClass('expanded');
          $btn.text('ver menos');
        }
      });
      table.on('draw.dt', function() {
        setTimeout(initTruncateButtons, 50);
      });
      initTruncateButtons();

      // === UX: Accordion toggle ===
      $('#cardFiltersPanel').on('shown.bs.collapse', function() {
        $('.filter-toggle-btn').removeClass('collapsed');
      }).on('hidden.bs.collapse', function() {
        $('.filter-toggle-btn').addClass('collapsed');
      });

      // Botón para restablecer filtros
      $('#clearState').click(function() {
        // Limpiar filtros de formulario
        $('#estadoFilter').val('');
        $('#fechaDesde').val('');
        $('#fechaHasta').val('');
        $('#mesSeleccionado').val('');
        table.column(0).search('');

        // Limpiar filtros de tarjetas
        activeYear = null;
        activeMonth = null;
        activeStatus = null;
        $('.card-year').removeClass('active');
        $('.card-month').removeClass('active');
        $('.card-status').removeClass('active');

        // Limpiar todos los filtros personalizados
        var currentSearch = $.fn.dataTable.ext.search.slice();
        $.fn.dataTable.ext.search = currentSearch.filter(function(fn) {
          return fn.toString().indexOf('fechaDesde') !== -1;
        });

        table.draw();
      });

    });
  </script>
</body>
</html>
