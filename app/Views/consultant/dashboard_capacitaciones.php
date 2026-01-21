<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Capacitaciones - Consultor</title>

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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            color: #667eea;
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
                    <h1><i class="fas fa-graduation-cap"></i> Dashboard Capacitaciones - Consultor</h1>
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
            <div class="col-md-4">
                <div class="metric-card">
                    <h6><i class="fas fa-list-ol"></i> Total Capacitaciones</h6>
                    <h2 id="metricTotal"><?= $totalCapacitaciones ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card">
                    <h6><i class="fas fa-users"></i> Asistentes Promedio</h6>
                    <h2 id="metricAsistentes"><?= $promedioAsistentes ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card">
                    <h6><i class="fas fa-star"></i> Promedio Calificaciones</h6>
                    <h2 id="metricCalificaciones"><?= $promedioCalificaciones ?></h2>
                </div>
            </div>
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
                    <i class="fas fa-calendar-alt"></i> Seleccione Mes Programado
                </label>
                <select class="form-select" id="filterMes">
                    <option value="">Todos los meses</option>
                    <?php foreach ($mesesUnicos as $valor => $etiqueta): ?>
                        <option value="<?= esc($valor) ?>"><?= esc($etiqueta) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-toggle-on"></i> Seleccione Estado Actual
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
                <label class="form-label fw-bold">&nbsp;</label>
                <button type="button" class="btn btn-secondary w-100" id="btnLimpiarFiltros">
                    <i class="fas fa-eraser"></i> Limpiar Filtros
                </button>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <!-- Gráfico Estado -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h5><i class="fas fa-chart-pie"></i> Estado de Capacitaciones</h5>
                    <canvas id="chartEstado"></canvas>
                </div>
            </div>

            <!-- Gráfico Tipo Participantes -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h5><i class="fas fa-user-friends"></i> Tipo de Participantes</h5>
                    <canvas id="chartParticipantes"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabla de datos -->
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <h5 class="mb-3"><i class="fas fa-table"></i> Detalle de Capacitaciones</h5>
                    <table id="capacitacionesTable" class="table table-striped table-bordered table-hover" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th>CLIENTE</th>
                                <th>NOMBRE CAPACITACIÓN</th>
                                <th>FECHA PROGRAMADA</th>
                                <th>FECHA REALIZACIÓN</th>
                                <th>ESTADO ACTUAL</th>
                                <th>ASISTENTES</th>
                                <th>CALIFICACIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($capacitaciones as $cap): ?>
                                <tr>
                                    <td><?= esc($cap['nombre_cliente']) ?></td>
                                    <td><?= esc($cap['objetivo_de_capacitacion'] ?? 'N/A') ?></td>
                                    <td><?= esc($cap['fecha_programada'] ?? 'N/A') ?></td>
                                    <td><?= esc($cap['fecha_de_realizacion'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($cap['estado'] ?? '') === 'EJECUTADA' ? 'success' : (($cap['estado'] ?? '') === 'PROGRAMADA' ? 'warning' : 'secondary') ?>">
                                            <?= esc($cap['estado'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?= esc($cap['numero_de_asistentes_a_capacitacion'] ?? 'N/A') ?></td>
                                    <td class="text-center"><?= esc($cap['promedio_de_calificaciones'] ?? 'N/A') ?></td>
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
        var originalData = <?= json_encode($capacitaciones) ?>;

        // Variables globales para gráficos
        var chartEstado, chartParticipantes;
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
            dataTable = $('#capacitacionesTable').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
                },
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Capacitaciones - Consultor',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                pageLength: 25,
                order: [[2, 'desc']],
                responsive: true
            });

            // Inicializar gráficos
            initCharts();

            // Event listeners para filtros
            $('#filterCliente, #filterMes, #filterEstado').on('change', function() {
                applyFilters();
            });

            // Botón limpiar filtros
            $('#btnLimpiarFiltros').on('click', function() {
                $('#filterCliente').val('').trigger('change');
                $('#filterMes').val('');
                $('#filterEstado').val('');
                applyFilters();
            });
        });

        function initCharts() {
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

            // Gráfico Tipo Participantes (Donut)
            var ctxParticipantes = document.getElementById('chartParticipantes').getContext('2d');
            var participantesData = <?= json_encode($participantesCounts) ?>;
            chartParticipantes = new Chart(ctxParticipantes, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(participantesData),
                    datasets: [{
                        data: Object.values(participantesData),
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
        }

        function applyFilters() {
            var filterCliente = $('#filterCliente').val();
            var filterMes = $('#filterMes').val();
            var filterEstado = $('#filterEstado').val();

            // Filtrar datos
            var filteredData = originalData.filter(function(item) {
                if (filterCliente && item.id_cliente != filterCliente) return false;
                if (filterMes && item.fecha_programada) {
                    var fecha = new Date(item.fecha_programada);
                    var yearMonth = fecha.getFullYear() + '-' + String(fecha.getMonth() + 1).padStart(2, '0');
                    if (yearMonth !== filterMes) return false;
                }
                if (filterEstado && item.estado !== filterEstado) return false;
                return true;
            });

            // Calcular métricas
            var totalCapacitaciones = filteredData.length;
            var asistentesTotal = 0;
            var calificacionesTotal = 0;
            var countAsistentes = 0;
            var countCalificaciones = 0;

            filteredData.forEach(function(item) {
                if (item.numero_de_asistentes_a_capacitacion && !isNaN(item.numero_de_asistentes_a_capacitacion)) {
                    asistentesTotal += parseInt(item.numero_de_asistentes_a_capacitacion);
                    countAsistentes++;
                }
                if (item.promedio_de_calificaciones && !isNaN(item.promedio_de_calificaciones)) {
                    calificacionesTotal += parseFloat(item.promedio_de_calificaciones);
                    countCalificaciones++;
                }
            });

            var promedioAsistentes = countAsistentes > 0 ? (asistentesTotal / countAsistentes).toFixed(2) : 0;
            var promedioCalificaciones = countCalificaciones > 0 ? (calificacionesTotal / countCalificaciones).toFixed(2) : 0;

            // Actualizar métricas en pantalla
            $('#metricTotal').text(totalCapacitaciones);
            $('#metricAsistentes').text(promedioAsistentes);
            $('#metricCalificaciones').text(promedioCalificaciones);

            // Recalcular datos para gráficos
            var estadoCounts = {};
            var participantesCounts = {};

            filteredData.forEach(function(item) {
                // Estado
                var estado = item.estado || 'SIN ESTADO';
                estadoCounts[estado] = (estadoCounts[estado] || 0) + 1;

                // Participantes
                var tipo = item.perfil_de_asistentes || 'SIN DEFINIR';
                participantesCounts[tipo] = (participantesCounts[tipo] || 0) + 1;
            });

            // Actualizar gráficos
            updateCharts(estadoCounts, participantesCounts);

            // Actualizar tabla
            dataTable.clear();
            filteredData.forEach(function(item) {
                var estadoBadge = '';
                if (item.estado === 'EJECUTADA') {
                    estadoBadge = '<span class="badge bg-success">' + (item.estado || 'N/A') + '</span>';
                } else if (item.estado === 'PROGRAMADA') {
                    estadoBadge = '<span class="badge bg-warning">' + (item.estado || 'N/A') + '</span>';
                } else {
                    estadoBadge = '<span class="badge bg-secondary">' + (item.estado || 'N/A') + '</span>';
                }

                dataTable.row.add([
                    item.nombre_cliente,
                    item.objetivo_de_capacitacion || 'N/A',
                    item.fecha_programada || 'N/A',
                    item.fecha_de_realizacion || 'N/A',
                    estadoBadge,
                    item.numero_de_asistentes_a_capacitacion || 'N/A',
                    item.promedio_de_calificaciones || 'N/A'
                ]);
            });
            dataTable.draw();
        }

        function updateCharts(estadoCounts, participantesCounts) {
            // Actualizar gráfico Estado
            chartEstado.data.labels = Object.keys(estadoCounts);
            chartEstado.data.datasets[0].data = Object.values(estadoCounts);
            chartEstado.update();

            // Actualizar gráfico Participantes
            chartParticipantes.data.labels = Object.keys(participantesCounts);
            chartParticipantes.data.datasets[0].data = Object.values(participantesCounts);
            chartParticipantes.update();
        }
    </script>
</body>

</html>
