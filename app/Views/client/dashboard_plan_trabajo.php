<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Plan de Trabajo</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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

        .metric-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .metric-card h6 {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .metric-card h2 {
            color: #f5576c;
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
        <!-- Header con métricas -->
        <div class="header-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-tasks"></i> Dashboard Plan de Trabajo</h1>
                    <p class="mb-0"><strong>Cliente:</strong> <?= esc($client['nombre_cliente']) ?></p>
                </div>
                <div class="col-md-4 text-center">
                    <div style="font-size: 3rem; font-weight: bold;">
                        <?= $totalActividades ?>
                    </div>
                    <p class="mb-0">Total Actividades</p>
                </div>
            </div>
        </div>

        <!-- Botón volver -->
        <div class="mb-3">
            <a href="<?= base_url('client/dashboard') ?>" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>

        <!-- Filtros/Selectores -->
        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-filter"></i> Seleccione Estado
                </label>
                <select class="form-select" id="filterEstado">
                    <option value="">Todos los estados</option>
                    <?php foreach ($estadosUnicos as $estado): ?>
                        <?php if (!empty($estado)): ?>
                            <option value="<?= esc($estado) ?>"><?= esc($estado) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-user-tie"></i> Seleccione Responsable
                </label>
                <select class="form-select" id="filterResponsable">
                    <option value="">Todos los responsables</option>
                    <?php foreach ($responsablesUnicos as $responsable): ?>
                        <?php if (!empty($responsable)): ?>
                            <option value="<?= esc($responsable) ?>"><?= esc($responsable) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-calendar"></i> Fecha Programada
                </label>
                <select class="form-select" id="filterFechaPropuesta">
                    <option value="">Todas las fechas</option>
                    <?php foreach ($fechasPropuestaUnicas as $valor => $etiqueta): ?>
                        <option value="<?= esc($valor) ?>"><?= esc($etiqueta) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-calendar-check"></i> Fecha Cierre
                </label>
                <select class="form-select" id="filterFechaCierre">
                    <option value="">Todas las fechas</option>
                    <?php foreach ($fechasCierreUnicas as $valor => $etiqueta): ?>
                        <option value="<?= esc($valor) ?>"><?= esc($etiqueta) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <!-- Gráfico Responsables (Barras Horizontales) -->
            <div class="col-md-4">
                <div class="chart-container">
                    <h5><i class="fas fa-users"></i> Actividades por Responsable</h5>
                    <canvas id="chartResponsables"></canvas>
                </div>
            </div>

            <!-- Gráfico Estado (Donut) -->
            <div class="col-md-4">
                <div class="chart-container">
                    <h5><i class="fas fa-toggle-on"></i> Estado de Actividades</h5>
                    <canvas id="chartEstado"></canvas>
                </div>
            </div>

            <!-- Gráfico PHVA (Donut) -->
            <div class="col-md-4">
                <div class="chart-container">
                    <h5><i class="fas fa-sync-alt"></i> Ciclo PHVA</h5>
                    <canvas id="chartPhva"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabla de datos -->
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <h5 class="mb-3"><i class="fas fa-table"></i> Detalle de Actividades</h5>
                    <table id="actividadesTable" class="table table-striped table-bordered table-hover" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th>ACTIVIDAD</th>
                                <th>FECHA PROPUESTA</th>
                                <th>FECHA CIERRE</th>
                                <th>ESTADO</th>
                                <th>RESPONSABLE</th>
                                <th>PHVA</th>
                                <th>% AVANCE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($actividades as $act): ?>
                                <tr>
                                    <td><?= esc($act['actividad_plandetrabajo'] ?? 'N/A') ?></td>
                                    <td><?= esc($act['fecha_propuesta'] ?? 'N/A') ?></td>
                                    <td><?= esc($act['fecha_cierre'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($act['estado_actividad'] ?? '') === 'CERRADA CON EJECUCIÓN' ? 'success' : (($act['estado_actividad'] ?? '') === 'ABIERTA' ? 'warning' : 'secondary') ?>">
                                            <?= esc($act['estado_actividad'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td><?= esc($act['responsable_definido_paralaactividad'] ?? $act['responsable_sugerido_plandetrabajo'] ?? 'N/A') ?></td>
                                    <td><?= esc($act['phva_plandetrabajo'] ?? 'N/A') ?></td>
                                    <td class="text-center"><?= esc($act['porcentaje_avance'] ?? '0') ?>%</td>
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
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        // Datos originales
        var originalData = <?= json_encode($actividades) ?>;

        // Variables globales para gráficos
        var chartResponsables, chartEstado, chartPhva;
        var dataTable;

        $(document).ready(function() {
            // Inicializar DataTable
            dataTable = $('#actividadesTable').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
                },
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Plan de Trabajo - <?= esc($client['nombre_cliente']) ?>',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                pageLength: 25,
                order: [[1, 'desc']],
                responsive: true
            });

            // Inicializar gráficos
            initCharts();

            // Event listeners para filtros
            $('#filterEstado, #filterResponsable, #filterFechaPropuesta, #filterFechaCierre').on('change', function() {
                applyFilters();
            });
        });

        function initCharts() {
            // Gráfico Responsables (Barras horizontales)
            var ctxResponsables = document.getElementById('chartResponsables').getContext('2d');
            var responsablesData = <?= json_encode($responsableCounts) ?>;
            chartResponsables = new Chart(ctxResponsables, {
                type: 'bar',
                data: {
                    labels: Object.keys(responsablesData),
                    datasets: [{
                        label: 'Actividades',
                        data: Object.values(responsablesData),
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

            // Gráfico Estado (Donut)
            var ctxEstado = document.getElementById('chartEstado').getContext('2d');
            var estadoData = <?= json_encode($estadoCounts) ?>;
            chartEstado = new Chart(ctxEstado, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(estadoData),
                    datasets: [{
                        data: Object.values(estadoData),
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d', '#6f42c1']
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

            // Gráfico PHVA (Donut)
            var ctxPhva = document.getElementById('chartPhva').getContext('2d');
            var phvaData = <?= json_encode($phvaCounts) ?>;
            chartPhva = new Chart(ctxPhva, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(phvaData),
                    datasets: [{
                        data: Object.values(phvaData),
                        backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0']
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
            var filterEstado = $('#filterEstado').val();
            var filterResponsable = $('#filterResponsable').val();
            var filterFechaPropuesta = $('#filterFechaPropuesta').val();
            var filterFechaCierre = $('#filterFechaCierre').val();

            // Filtrar datos
            var filteredData = originalData.filter(function(item) {
                if (filterEstado && item.estado_actividad !== filterEstado) return false;

                var responsable = item.responsable_definido_paralaactividad || item.responsable_sugerido_plandetrabajo;
                if (filterResponsable && responsable !== filterResponsable) return false;

                if (filterFechaPropuesta && item.fecha_propuesta) {
                    var fecha = new Date(item.fecha_propuesta);
                    var yearMonth = fecha.getFullYear() + '-' + String(fecha.getMonth() + 1).padStart(2, '0');
                    if (yearMonth !== filterFechaPropuesta) return false;
                }

                if (filterFechaCierre && item.fecha_cierre) {
                    var fecha = new Date(item.fecha_cierre);
                    var yearMonth = fecha.getFullYear() + '-' + String(fecha.getMonth() + 1).padStart(2, '0');
                    if (yearMonth !== filterFechaCierre) return false;
                }

                return true;
            });

            // Recalcular datos para gráficos
            var responsableCounts = {};
            var estadoCounts = {};
            var phvaCounts = {};

            filteredData.forEach(function(item) {
                // Responsables
                var responsable = item.responsable_definido_paralaactividad || item.responsable_sugerido_plandetrabajo || 'SIN ASIGNAR';
                responsableCounts[responsable] = (responsableCounts[responsable] || 0) + 1;

                // Estado
                var estado = item.estado_actividad || 'SIN ESTADO';
                estadoCounts[estado] = (estadoCounts[estado] || 0) + 1;

                // PHVA
                var phva = item.phva_plandetrabajo || 'SIN PHVA';
                phvaCounts[phva] = (phvaCounts[phva] || 0) + 1;
            });

            // Actualizar gráficos
            updateCharts(responsableCounts, estadoCounts, phvaCounts);

            // Actualizar tabla
            dataTable.clear();
            filteredData.forEach(function(item) {
                var estadoBadge = '';
                if (item.estado_actividad === 'CERRADA CON EJECUCIÓN') {
                    estadoBadge = '<span class="badge bg-success">' + (item.estado_actividad || 'N/A') + '</span>';
                } else if (item.estado_actividad === 'ABIERTA') {
                    estadoBadge = '<span class="badge bg-warning">' + (item.estado_actividad || 'N/A') + '</span>';
                } else {
                    estadoBadge = '<span class="badge bg-secondary">' + (item.estado_actividad || 'N/A') + '</span>';
                }

                var responsable = item.responsable_definido_paralaactividad || item.responsable_sugerido_plandetrabajo || 'N/A';

                dataTable.row.add([
                    item.actividad_plandetrabajo || 'N/A',
                    item.fecha_propuesta || 'N/A',
                    item.fecha_cierre || 'N/A',
                    estadoBadge,
                    responsable,
                    item.phva_plandetrabajo || 'N/A',
                    (item.porcentaje_avance || '0') + '%'
                ]);
            });
            dataTable.draw();
        }

        function updateCharts(responsableCounts, estadoCounts, phvaCounts) {
            // Actualizar gráfico Responsables
            chartResponsables.data.labels = Object.keys(responsableCounts);
            chartResponsables.data.datasets[0].data = Object.values(responsableCounts);
            chartResponsables.update();

            // Actualizar gráfico Estado
            chartEstado.data.labels = Object.keys(estadoCounts);
            chartEstado.data.datasets[0].data = Object.values(estadoCounts);
            chartEstado.update();

            // Actualizar gráfico PHVA
            chartPhva.data.labels = Object.keys(phvaCounts);
            chartPhva.data.datasets[0].data = Object.values(phvaCounts);
            chartPhva.update();
        }
    </script>
</body>

</html>
