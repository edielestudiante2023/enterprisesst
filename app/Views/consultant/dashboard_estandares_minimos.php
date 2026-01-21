<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Estándares Mínimos SST - Consultor</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-logos {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 10px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .header-section {
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .header-section h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .puntaje-total {
            font-size: 3rem;
            font-weight: bold;
            color: #FFD700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .metric-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .metric-card h6 {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .metric-card h2 {
            color: #1c2437;
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            min-height: 350px;
        }

        .chart-container h5 {
            text-align: center;
            color: #1c2437;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        canvas {
            max-height: 300px;
        }

        .btn-volver {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-volver:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            color: white;
        }
    </style>
</head>

<body>
    <!-- Navbar con logos -->
    <nav class="navbar-logos">
        <div class="container-fluid d-flex justify-content-around align-items-center">
            <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprise SST" height="60">
            <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST" height="60">
            <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloid" height="60">
        </div>
    </nav>

    <div style="height: 100px;"></div>

    <!-- Contenido principal -->
    <div class="container-fluid px-4">
        <!-- Header con puntaje -->
        <div class="header-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-chart-pie"></i> Dashboard Estándares Mínimos SST - Consultor</h1>
                    <p class="mb-0">Vista consolidada de todos los clientes</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="puntaje-total">
                        <span id="metricCalificado"><?= $totalCalificado ?></span> / <span id="metricPosible"><?= $totalPosible ?></span>
                    </div>
                    <p class="mb-0">Puntaje Total</p>
                </div>
            </div>
        </div>

        <!-- Botón volver -->
        <div class="mb-3">
            <a href="<?= base_url('dashboardconsultant') ?>" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>

        <!-- Filtros/Selectores -->
        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-building"></i> Seleccione Cliente</label>
                <select class="form-select" id="filterCliente">
                    <option value="">Todos los clientes</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente['id_cliente'] ?>"><?= esc($cliente['nombre_cliente']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-filter"></i> Seleccione Dimensión
                </label>
                <select class="form-select" id="filterDimension">
                    <option value="">Todas las dimensiones</option>
                    <?php foreach ($dimensionesUnicas as $dim): ?>
                        <?php if (!empty($dim)): ?>
                            <option value="<?= esc($dim) ?>"><?= esc($dim) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-filter"></i> Seleccione Calificación
                </label>
                <select class="form-select" id="filterCalificacion">
                    <option value="">Todas las calificaciones</option>
                    <?php foreach ($calificacionesUnicas as $calif): ?>
                        <option value="<?= esc($calif) ?>"><?= esc($calif) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-filter"></i> Seleccione Ciclo PHVA
                </label>
                <select class="form-select" id="filterPHVA">
                    <option value="">Todos los ciclos</option>
                    <?php foreach ($ciclosUnicos as $ciclo): ?>
                        <?php if (!empty($ciclo)): ?>
                            <option value="<?= esc($ciclo) ?>"><?= esc($ciclo) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">&nbsp;</label>
                <button type="button" class="btn btn-secondary w-100" id="btnLimpiarFiltros">
                    <i class="fas fa-eraser"></i> Limpiar Filtros
                </button>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <!-- Gráfico PHVA -->
            <div class="col-md-4">
                <div class="chart-container">
                    <h5><i class="fas fa-sync-alt"></i> Ciclo PHVA</h5>
                    <canvas id="chartPhva"></canvas>
                </div>
            </div>

            <!-- Gráfico Dimensión (Treemap simulado con barras horizontales) -->
            <div class="col-md-4">
                <div class="chart-container">
                    <h5><i class="fas fa-layer-group"></i> Gestión por Dimensión</h5>
                    <canvas id="chartDimension"></canvas>
                </div>
            </div>

            <!-- Gráfico Calificación -->
            <div class="col-md-4">
                <div class="chart-container">
                    <h5><i class="fas fa-check-circle"></i> Calificación</h5>
                    <canvas id="chartCalificacion"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabla de datos -->
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <h5 class="mb-3"><i class="fas fa-table"></i> Detalle de Evaluaciones</h5>
                    <table id="estandaresTable" class="table table-striped table-bordered table-hover" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th>CLIENTE</th>
                                <th>ÍTEM</th>
                                <th>PHVA</th>
                                <th>ESTÁNDAR</th>
                                <th>CALIFICACIÓN</th>
                                <th>CALIFICADO</th>
                                <th>MÁX. POSIBLE</th>
                                <th>NUMERAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($evaluaciones as $ev): ?>
                                <tr>
                                    <td><?= esc($ev['nombre_cliente']) ?></td>
                                    <td><?= esc($ev['item_del_estandar']) ?></td>
                                    <td><?= esc($ev['ciclo']) ?></td>
                                    <td><?= esc($ev['estandar']) ?></td>
                                    <td><?= empty($ev['evaluacion_inicial']) ? 'SIN EVALUAR' : esc($ev['evaluacion_inicial']) ?></td>
                                    <td class="text-end"><?= esc($ev['valor']) ?></td>
                                    <td class="text-end"><?= esc($ev['puntaje_cuantitativo']) ?></td>
                                    <td><?= esc($ev['numeral']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div style="height: 50px;"></div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        // Datos originales
        var originalData = <?= json_encode($evaluaciones) ?>;

        // Variables globales para gráficos
        var chartPhva, chartDimension, chartCalificacion;
        var dataTable;

        $(document).ready(function() {
            // Inicializar Select2 para el selector de cliente
            $('#filterCliente').select2({
                theme: 'bootstrap-5',
                placeholder: 'Seleccione un cliente',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });

            // Inicializar DataTable
            dataTable = $('#estandaresTable').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
                },
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Estándares Mínimos SST - Consultor',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                pageLength: 25,
                order: [[5, 'desc']],
                responsive: true
            });

            // Inicializar gráficos
            initCharts();

            // Event listeners para filtros
            $('#filterCliente, #filterDimension, #filterCalificacion, #filterPHVA').on('change', function() {
                applyFilters();
            });

            // Botón limpiar filtros
            $('#btnLimpiarFiltros').on('click', function() {
                $('#filterCliente').val('').trigger('change');
                $('#filterDimension').val('');
                $('#filterCalificacion').val('');
                $('#filterPHVA').val('');
                applyFilters();
            });
        });

        function initCharts() {
            // Gráfico PHVA (Donut)
            var ctxPhva = document.getElementById('chartPhva').getContext('2d');
            var phvaData = <?= json_encode($phvaCounts) ?>;
            chartPhva = new Chart(ctxPhva, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(phvaData),
                    datasets: [{
                        data: Object.values(phvaData),
                        backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Gráfico Dimensión (Barras horizontales como treemap simulado)
            var ctxDimension = document.getElementById('chartDimension').getContext('2d');
            var dimensionData = <?= json_encode($dimensionCounts) ?>;
            chartDimension = new Chart(ctxDimension, {
                type: 'bar',
                data: {
                    labels: Object.keys(dimensionData),
                    datasets: [{
                        label: 'Puntaje',
                        data: Object.values(dimensionData),
                        backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Gráfico Calificación (Donut)
            var ctxCalificacion = document.getElementById('chartCalificacion').getContext('2d');
            var calificacionData = <?= json_encode($calificacionCounts) ?>;
            chartCalificacion = new Chart(ctxCalificacion, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(calificacionData),
                    datasets: [{
                        data: Object.values(calificacionData),
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function applyFilters() {
            var filterCliente = $('#filterCliente').val();
            var filterDimension = $('#filterDimension').val();
            var filterCalificacion = $('#filterCalificacion').val();
            var filterPHVA = $('#filterPHVA').val();

            // Filtrar datos
            var filteredData = originalData.filter(function(item) {
                if (filterCliente && item.id_cliente != filterCliente) return false;
                if (filterDimension && item.estandar !== filterDimension) return false;

                // Manejar "SIN EVALUAR" para calificaciones vacías
                if (filterCalificacion) {
                    var itemCalif = item.evaluacion_inicial ? item.evaluacion_inicial : 'SIN EVALUAR';
                    if (itemCalif !== filterCalificacion) return false;
                }

                if (filterPHVA && item.ciclo !== filterPHVA) return false;
                return true;
            });

            // Calcular métricas totales
            var totalCalificado = 0;
            var totalPosible = 0;
            filteredData.forEach(function(item) {
                totalCalificado += parseFloat(item.valor || 0);
                totalPosible += parseFloat(item.puntaje_cuantitativo || 0);
            });

            // Actualizar métricas en pantalla
            $('#metricCalificado').text(totalCalificado.toFixed(2));
            $('#metricPosible').text(totalPosible.toFixed(2));

            // Recalcular datos para gráficos
            var phvaCounts = {};
            var dimensionCounts = {};
            var calificacionCounts = {};

            filteredData.forEach(function(item) {
                // PHVA
                if (item.ciclo) {
                    phvaCounts[item.ciclo] = (phvaCounts[item.ciclo] || 0) + 1;
                }
                // Dimensión
                if (item.estandar) {
                    dimensionCounts[item.estandar] = (dimensionCounts[item.estandar] || 0) + parseFloat(item.valor || 0);
                }
                // Calificación (incluir vacíos como "SIN EVALUAR")
                var calif = item.evaluacion_inicial ? item.evaluacion_inicial : 'SIN EVALUAR';
                calificacionCounts[calif] = (calificacionCounts[calif] || 0) + 1;
            });

            // Actualizar gráficos
            updateCharts(phvaCounts, dimensionCounts, calificacionCounts);

            // Actualizar tabla
            dataTable.clear();
            filteredData.forEach(function(item) {
                dataTable.row.add([
                    item.nombre_cliente,
                    item.item_del_estandar,
                    item.ciclo,
                    item.estandar,
                    item.evaluacion_inicial ? item.evaluacion_inicial : 'SIN EVALUAR',
                    item.valor,
                    item.puntaje_cuantitativo,
                    item.numeral
                ]);
            });
            dataTable.draw();
        }

        function updateCharts(phvaCounts, dimensionCounts, calificacionCounts) {
            // Actualizar gráfico PHVA
            chartPhva.data.labels = Object.keys(phvaCounts);
            chartPhva.data.datasets[0].data = Object.values(phvaCounts);
            chartPhva.update();

            // Actualizar gráfico Dimensión
            chartDimension.data.labels = Object.keys(dimensionCounts);
            chartDimension.data.datasets[0].data = Object.values(dimensionCounts);
            chartDimension.update();

            // Actualizar gráfico Calificación
            chartCalificacion.data.labels = Object.keys(calificacionCounts);
            chartCalificacion.data.datasets[0].data = Object.values(calificacionCounts);
            chartCalificacion.update();
        }
    </script>
</body>

</html>
