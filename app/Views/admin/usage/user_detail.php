<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Detalle de Usuario - Enterprise SST</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #343a40;
        }

        .user-card {
            background: linear-gradient(135deg, #1c2437, #2c3e50);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .user-card .avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #bd9751, #d4af37);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin-right: 20px;
        }

        .stat-mini {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }

        .stat-mini .value {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-mini .label {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .badge-activa {
            background-color: #28a745;
            color: white;
        }

        .badge-cerrada {
            background-color: #6c757d;
            color: white;
        }

        .badge-expirada {
            background-color: #ffc107;
            color: black;
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
                <a href="<?= base_url('/admin/usage') ?>" class="btn btn-primary btn-sm mr-2">
                    <i class="fas fa-arrow-left"></i> Volver a Consumo
                </a>
                <a href="<?= base_url('/admin/users') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-users"></i> Usuarios
                </a>
            </div>
        </div>
    </nav>

    <div style="height: 120px;"></div>

    <div class="container-fluid px-4">
        <!-- User Card -->
        <div class="user-card">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <div class="avatar">
                            <?= strtoupper(substr($user['nombre_completo'], 0, 1)) ?>
                        </div>
                        <div>
                            <h3 class="mb-1"><?= htmlspecialchars($user['nombre_completo']) ?></h3>
                            <p class="mb-1"><i class="fas fa-envelope mr-2"></i><?= htmlspecialchars($user['email']) ?></p>
                            <span class="badge badge-<?= $user['tipo_usuario'] ?>">
                                <?= ucfirst($user['tipo_usuario']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <?php
                        $totalSesiones = count($historial);
                        $tiempoTotal = array_sum(array_column($historial, 'duracion_segundos'));
                        $promedio = $totalSesiones > 0 ? $tiempoTotal / $totalSesiones : 0;
                        ?>
                        <div class="col-4">
                            <div class="stat-mini">
                                <div class="value"><?= $totalSesiones ?></div>
                                <div class="label">Sesiones</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-mini">
                                <div class="value"><?= gmdate('H:i', $tiempoTotal) ?></div>
                                <div class="label">Tiempo Total</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-mini">
                                <div class="value"><?= gmdate('H:i', (int)$promedio) ?></div>
                                <div class="label">Promedio</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial de Sesiones -->
        <div class="table-container">
            <h5 class="mb-4"><i class="fas fa-history mr-2"></i>Historial de Sesiones</h5>
            <div class="table-responsive">
                <table id="historyTable" class="table table-striped table-bordered" style="width:100%">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Duración</th>
                            <th>IP</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial as $sesion): ?>
                            <tr>
                                <td><?= $sesion['id_sesion'] ?></td>
                                <td><?= date('d/m/Y H:i:s', strtotime($sesion['inicio_sesion'])) ?></td>
                                <td>
                                    <?= $sesion['fin_sesion'] ? date('d/m/Y H:i:s', strtotime($sesion['fin_sesion'])) : '<span class="text-muted">-</span>' ?>
                                </td>
                                <td>
                                    <?php if ($sesion['duracion_segundos']): ?>
                                        <strong><?= gmdate('H:i:s', $sesion['duracion_segundos']) ?></strong>
                                    <?php elseif ($sesion['estado'] === 'activa'): ?>
                                        <span class="text-success"><i class="fas fa-circle"></i> En curso</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars($sesion['ip_address'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $sesion['estado'] ?>">
                                        <?= ucfirst($sesion['estado']) ?>
                                    </span>
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
            $('#historyTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                },
                "order": [[1, "desc"]],
                "pageLength": 25
            });
        });
    </script>

</body>

</html>
