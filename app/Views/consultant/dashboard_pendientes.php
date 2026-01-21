<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pendientes - Consultor</title>

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
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
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
            color: #fa709a;
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
                <div class="col-md-12">
                    <h1><i class="fas fa-clipboard-list"></i> Dashboard Pendientes - Consultor</h1>
                    <p class="mb-0">Vista consolidada de todos los clientes</p>
                </div>
            </div>
        </div>

        <!-- Botón volver -->
        <div class="mb-3">
            <a href="<?= base_url('dashboardconsultant') ?>" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>

        <!-- Tarjetas de métricas -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="metric-card">
                    <h6><i class="fas fa-list-ol"></i> Total Pendientes</h6>
                    <h2 id="metricTotal"><?= $totalPendientes ?></h2>
                </div>
            </div>
            <div class="col-md-6">
                <div class="metric-card">
                    <h6><i class="fas fa-clock"></i> Promedio Días</h6>
                    <h2 id="metricPromedioDias"><?= $promedioDias ?></h2>
                </div>
            </div>
        </div>

        <!-- Filtros/Selectores -->
        <div class="row mb-4">
            <div class="col-md-2">
                <label class="form-label fw-bold"><i class="fas fa-building"></i> Seleccione Cliente</label>
                <select class="form-select" id="filterCliente">
                    <option value="">Todos los clientes</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente['id_cliente'] ?>"><?= esc($cliente['nombre_cliente']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
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

            <div class="col-md-2">
                <label class="form-label fw-bold">
                    <i class="fas fa-user"></i> Responsable
                </label>
                <select class="form-select" id="filterResponsable">
                    <option value="">Todos</option>
                    <?php foreach ($responsablesUnicos as $responsable): ?>
                        <?php if (!empty($responsable)): ?>
                            <option value="<?= esc($responsable) ?>"><?= esc($responsable) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">
                    <i class="fas fa-calendar-plus"></i> Mes Asignación
                </label>
                <select class="form-select" id="filterMesAsignacion">
                    <option value="">Todos los meses</option>
                    <?php foreach ($mesesAsignacionUnicos as $valor => $etiqueta): ?>
                        <option value="<?= esc($valor) ?>"><?= esc($etiqueta) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">
                    <i class="fas fa-calendar-check"></i> Mes Cierre
                </label>
                <select class="form-select" id="filterMesCierre">
                    <option value="">Todos los meses</option>
                    <?php foreach ($mesesCierreUnicos as $valor => $etiqueta): ?>
                        <option value="<?= esc($valor) ?>"><?= esc($etiqueta) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">&nbsp;</label>
                <button type="button" class="btn btn-secondary w-100" id="btnLimpiarFiltros">
                    <i class="fas fa-eraser"></i> Limpiar Filtros
                </button>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <!-- Gráfico Responsables (Barras Horizontales) -->
            <div class="col-md-8">
                <div class="chart-container">
                    <h5><i class="fas fa-users"></i> Pendientes por Responsable</h5>
                    <canvas id="chartResponsables"></canvas>
                </div>
            </div>

            <!-- Gráfico Estado (Donut) -->
            <div class="col-md-4">
                <div class="chart-container">
                    <h5><i class="fas fa-chart-pie"></i> Estado de Pendientes</h5>
                    <canvas id="chartEstado"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabla de datos -->
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <h5 class="mb-3"><i class="fas fa-table"></i> Detalle de Pendientes</h5>
                    <table id="pendientesTable" class="table table-striped table-bordered table-hover" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th>CLIENTE</th>
                                <th>ACTIVIDAD PENDIENTE</th>
                                <th>RESPONSABLE</th>
                                <th>ESTADO</th>
                                <th>FECHA ASIGNACIÓN</th>
                                <th>FECHA CIERRE</th>
                                <th>CUENTA DÍAS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendientes as $pend): ?>
                                <tr>
                                    <td><?= esc($pend['nombre_cliente']) ?></td>
                                    <td><?= esc($pend['tarea_actividad'] ?? 'N/A') ?></td>
                                    <td><?= esc($pend['responsable'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($pend['estado'] ?? '') === 'CERRADA' ? 'success' : (($pend['estado'] ?? '') === 'ABIERTA' ? 'warning' : 'secondary') ?>">
                                            <?= esc($pend['estado'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td><?= esc($pend['fecha_asignacion'] ?? 'N/A') ?></td>
                                    <td><?= esc($pend['fecha_cierre'] ?? 'N/A') ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-info"><?= esc($pend['conteo_dias'] ?? '0') ?> días</span>
                                    </td>
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
        var originalData = <?= json_encode($pendientes) ?>;

        // Variables globales para gráficos
        var chartResponsables, chartEstado;
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
            dataTable = $('#pendientesTable').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
                },
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Pendientes - Consultor',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                pageLength: 25,
                order: [[6, 'desc']],
                responsive: true
            });

            // Inicializar gráficos
            initCharts();

            // Event listeners para filtros
            $('#filterCliente, #filterEstado, #filterResponsable, #filterMesAsignacion, #filterMesCierre').on('change', function() {
                applyFilters();
            });

            // Botón limpiar filtros
            $('#btnLimpiarFiltros').on('click', function() {
                $('#filterCliente').val('').trigger('change');
                $('#filterEstado').val('');
                $('#filterResponsable').val('');
                $('#filterMesAsignacion').val('');
                $('#filterMesCierre').val('');
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
                        label: 'Pendientes',
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
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#17a2b8']
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
            var filterEstado = $('#filterEstado').val();
            var filterResponsable = $('#filterResponsable').val();
            var filterMesAsignacion = $('#filterMesAsignacion').val();
            var filterMesCierre = $('#filterMesCierre').val();

            // Filtrar datos
            var filteredData = originalData.filter(function(item) {
                if (filterCliente && item.id_cliente != filterCliente) return false;
                if (filterEstado && item.estado !== filterEstado) return false;
                if (filterResponsable && item.responsable !== filterResponsable) return false;

                if (filterMesAsignacion && item.fecha_asignacion) {
                    var fecha = new Date(item.fecha_asignacion);
                    var yearMonth = fecha.getFullYear() + '-' + String(fecha.getMonth() + 1).padStart(2, '0');
                    if (yearMonth !== filterMesAsignacion) return false;
                }

                if (filterMesCierre && item.fecha_cierre) {
                    var fecha = new Date(item.fecha_cierre);
                    var yearMonth = fecha.getFullYear() + '-' + String(fecha.getMonth() + 1).padStart(2, '0');
                    if (yearMonth !== filterMesCierre) return false;
                }

                return true;
            });

            // Calcular métricas
            var totalPendientes = filteredData.length;
            var diasTotal = 0;
            var countDias = 0;

            filteredData.forEach(function(item) {
                if (item.conteo_dias && !isNaN(item.conteo_dias)) {
                    diasTotal += parseInt(item.conteo_dias);
                    countDias++;
                }
            });

            var promedioDias = countDias > 0 ? (diasTotal / countDias).toFixed(2) : 0;

            // Actualizar métricas en pantalla
            $('#metricTotal').text(totalPendientes);
            $('#metricPromedioDias').text(promedioDias);

            // Recalcular datos para gráficos
            var responsableCounts = {};
            var estadoCounts = {};

            filteredData.forEach(function(item) {
                // Responsables
                var responsable = item.responsable || 'SIN ASIGNAR';
                responsableCounts[responsable] = (responsableCounts[responsable] || 0) + 1;

                // Estado
                var estado = item.estado || 'SIN ESTADO';
                estadoCounts[estado] = (estadoCounts[estado] || 0) + 1;
            });

            // Actualizar gráficos
            updateCharts(responsableCounts, estadoCounts);

            // Actualizar tabla
            dataTable.clear();
            filteredData.forEach(function(item) {
                var estadoBadge = '';
                if (item.estado === 'CERRADA') {
                    estadoBadge = '<span class="badge bg-success">' + (item.estado || 'N/A') + '</span>';
                } else if (item.estado === 'ABIERTA') {
                    estadoBadge = '<span class="badge bg-warning">' + (item.estado || 'N/A') + '</span>';
                } else {
                    estadoBadge = '<span class="badge bg-secondary">' + (item.estado || 'N/A') + '</span>';
                }

                dataTable.row.add([
                    item.nombre_cliente,
                    item.tarea_actividad || 'N/A',
                    item.responsable || 'N/A',
                    estadoBadge,
                    item.fecha_asignacion || 'N/A',
                    item.fecha_cierre || 'N/A',
                    '<span class="badge bg-info">' + (item.conteo_dias || '0') + ' días</span>'
                ]);
            });
            dataTable.draw();
        }

        function updateCharts(responsableCounts, estadoCounts) {
            // Actualizar gráfico Responsables
            chartResponsables.data.labels = Object.keys(responsableCounts);
            chartResponsables.data.datasets[0].data = Object.values(responsableCounts);
            chartResponsables.update();

            // Actualizar gráfico Estado
            chartEstado.data.labels = Object.keys(estadoCounts);
            chartEstado.data.datasets[0].data = Object.values(estadoCounts);
            chartEstado.update();
        }
    </script>
</body>

</html>
