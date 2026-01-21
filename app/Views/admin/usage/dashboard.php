<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Consumo de Plataforma - Enterprise SST</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            color: #343a40;
        }

        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            border-left: 4px solid;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.primary {
            border-left-color: #007bff;
        }

        .stat-card.success {
            border-left-color: #28a745;
        }

        .stat-card.warning {
            border-left-color: #ffc107;
        }

        .stat-card.info {
            border-left-color: #17a2b8;
        }

        .stat-card .icon {
            font-size: 2.5rem;
            opacity: 0.3;
            position: absolute;
            right: 20px;
            top: 20px;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: #1c2437;
        }

        .stat-card .label {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .filter-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .badge-admin {
            background-color: #007bff;
            color: white;
        }

        .badge-consultant {
            background-color: #17a2b8;
            color: white;
        }

        .badge-client {
            background-color: #6f42c1;
            color: white;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-gold {
            background: linear-gradient(135deg, #bd9751, #d4af37);
            color: white;
            border: none;
        }

        .btn-gold:hover {
            background: linear-gradient(135deg, #d4af37, #bd9751);
            color: white;
        }

        .page-header {
            background: linear-gradient(135deg, #1c2437, #2c3e50);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .page-header h2 {
            margin: 0;
            font-weight: 700;
        }

        .page-header p {
            margin: 10px 0 0;
            opacity: 0.8;
        }
    </style>
</head>

<body>

    <!-- Navbar Fijo -->
    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">
            <div>
                <a href="<?= base_url('/admin/dashboard') ?>">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 80px;">
                </a>
            </div>
            <div>
                <a href="<?= base_url('/admin/dashboard') ?>" class="btn btn-primary btn-sm mr-2">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="<?= base_url('/admin/users') ?>" class="btn btn-secondary btn-sm mr-2">
                    <i class="fas fa-users"></i> Usuarios
                </a>
                <a href="<?= base_url('/admin/usage/export-csv') ?>?fecha_inicio=<?= $fechaInicio ?>&fecha_fin=<?= $fechaFin ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Exportar CSV
                </a>
            </div>
        </div>
    </nav>

    <div style="height: 120px;"></div>

    <div class="container-fluid px-4">
        <!-- Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-chart-line mr-2"></i>Consumo de Plataforma</h2>
                    <p>Monitoreo de tiempo de uso y actividad de usuarios</p>
                </div>
                <div class="col-md-4 text-right">
                    <i class="fas fa-clock" style="font-size: 4rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filter-card">
            <form method="get" class="row align-items-end">
                <div class="col-md-4">
                    <label for="fecha_inicio"><i class="fas fa-calendar"></i> Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="<?= $fechaInicio ?>">
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin"><i class="fas fa-calendar"></i> Fecha Fin</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="<?= $fechaFin ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-gold btn-block">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Estadísticas Generales -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card primary position-relative">
                    <i class="fas fa-sign-in-alt icon"></i>
                    <div class="value"><?= number_format($estadisticas['total_sesiones'] ?? 0) ?></div>
                    <div class="label">Total Sesiones</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success position-relative">
                    <i class="fas fa-users icon"></i>
                    <div class="value"><?= number_format($estadisticas['usuarios_unicos'] ?? 0) ?></div>
                    <div class="label">Usuarios Únicos</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning position-relative">
                    <i class="fas fa-clock icon"></i>
                    <div class="value"><?= gmdate('H:i:s', (int)($estadisticas['tiempo_total_segundos'] ?? 0)) ?></div>
                    <div class="label">Tiempo Total</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card info position-relative">
                    <i class="fas fa-stopwatch icon"></i>
                    <div class="value"><?= gmdate('H:i:s', (int)($estadisticas['promedio_duracion'] ?? 0)) ?></div>
                    <div class="label">Promedio/Sesión</div>
                </div>
            </div>
        </div>

        <!-- Gráfica -->
        <div class="chart-container">
            <h5 class="mb-4"><i class="fas fa-chart-area mr-2"></i>Actividad Diaria</h5>
            <canvas id="activityChart" height="100"></canvas>
        </div>

        <!-- Tabla de Usuarios -->
        <div class="table-container">
            <h5 class="mb-4"><i class="fas fa-table mr-2"></i>Consumo por Usuario</h5>
            <div class="table-responsive">
                <table id="usageTable" class="table table-striped table-bordered" style="width:100%">
                    <thead class="thead-light">
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Sesiones</th>
                            <th>Tiempo Total</th>
                            <th>Promedio</th>
                            <th>Última Sesión</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resumenUsuarios as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['nombre_completo']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $user['tipo_usuario'] ?>">
                                        <?= ucfirst($user['tipo_usuario']) ?>
                                    </span>
                                </td>
                                <td><?= number_format($user['total_sesiones']) ?></td>
                                <td>
                                    <strong><?= $user['tiempo_total_formato'] ?></strong>
                                </td>
                                <td><?= gmdate('H:i:s', (int)$user['promedio_duracion_segundos']) ?></td>
                                <td>
                                    <?= $user['ultima_sesion'] ? date('d/m/Y H:i', strtotime($user['ultima_sesion'])) : '<span class="text-muted">Nunca</span>' ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('/admin/usage/user/' . $user['id_usuario']) ?>" class="btn btn-sm btn-info" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
            <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#usageTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                },
                "order": [[4, "desc"]], // Ordenar por tiempo total
                "pageLength": 25
            });

            // Cargar datos de gráfica
            fetch('<?= base_url('/admin/usage/chart-data') ?>?fecha_inicio=<?= $fechaInicio ?>&fecha_fin=<?= $fechaFin ?>')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('activityChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Sesiones',
                                    data: data.sesiones,
                                    borderColor: '#007bff',
                                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                    fill: true,
                                    tension: 0.4
                                },
                                {
                                    label: 'Usuarios Únicos',
                                    data: data.usuarios,
                                    borderColor: '#28a745',
                                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                    fill: true,
                                    tension: 0.4
                                },
                                {
                                    label: 'Horas de Uso',
                                    data: data.tiempos,
                                    borderColor: '#ffc107',
                                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                                    fill: true,
                                    tension: 0.4,
                                    yAxisID: 'y1'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            scales: {
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    title: {
                                        display: true,
                                        text: 'Cantidad'
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'Horas'
                                    },
                                    grid: {
                                        drawOnChartArea: false,
                                    },
                                }
                            }
                        }
                    });
                });
        });
    </script>

</body>

</html>
