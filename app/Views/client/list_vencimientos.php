<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestion de Vencimientos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Personalizacion de badges de estado */
        .estado {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .estado-sin-ejecutar {
            background-color: #6c757d;
            color: white;
        }

        .estado-ejecutado {
            background-color: #198754;
            color: white;
        }

        .estado-pendiente {
            background-color: #0d6efd;
            color: white;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center w-100">
                <!-- Logos -->
                <div class="d-flex align-items-center justify-content-around w-100">
                    <a href="https://dashboard.cycloidtalent.com/login" class="mx-2">
                        <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 80px;">
                    </a>
                    <a href="https://cycloidtalent.com/index.php/consultoria-sst" class="mx-2">
                        <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 80px;">
                    </a>
                    <a href="https://cycloidtalent.com/" class="mx-2">
                        <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 80px;">
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <div style="height: 100px;"></div>
    <div class="container my-5">
        <h2 class="mb-4 text-center text-uppercase" style="color:rgb(102, 4, 102);">VENCIMIENTOS DE MANTENIMIENTO <br> <?= esc($cliente) ?></h2>

        <?php if (!empty($vencimientos) && is_array($vencimientos)): ?>
            <!-- Tabla de Sin Ejecutar -->
            <h2>Sin Ejecutar</h2>
            <div class="table-responsive">
                <table id="vencimientosTableSinEjecutar" class="table table-striped table-bordered w-100">
                    <thead class="table-dark">
                        <tr>
                            <th>Mantenimiento</th>
                            <th>Fecha de Vencimiento</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vencimientos as $vencimiento): ?>
                            <?php if (strtolower($vencimiento['estado_actividad']) === 'sin ejecutar'): ?>
                                <tr>
                                    <td><?= esc($vencimiento['detalle_mantenimiento']) ?></td>
                                    <td><?= esc($vencimiento['fecha_vencimiento']) ?></td>
                                    <td>
                                        <?php
                                        $estadoClase = 'estado-sin-ejecutar';
                                        switch (strtolower($vencimiento['estado_actividad'])) {
                                            case 'ejecutado':
                                                $estadoClase = 'estado-ejecutado';
                                                break;
                                            case 'pendiente':
                                                $estadoClase = 'estado-pendiente';
                                                break;
                                        }
                                        ?>
                                        <span class="estado <?= $estadoClase ?>">
                                            <?= esc($vencimiento['estado_actividad']) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($vencimiento['observaciones'] ?? 'N/A') ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tabla de Ejecutado -->
            <h2>Ejecutado</h2>
            <div class="table-responsive">
                <table id="vencimientosTableEjecutado" class="table table-striped table-bordered w-100">
                    <thead class="table-dark">
                        <tr>
                            <th>Mantenimiento</th>
                            <th>Fecha de Vencimiento</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vencimientos as $vencimiento): ?>
                            <?php if (strtolower($vencimiento['estado_actividad']) === 'ejecutado'): ?>
                                <tr>
                                    <td><?= esc($vencimiento['detalle_mantenimiento']) ?></td>
                                    <td><?= esc($vencimiento['fecha_vencimiento']) ?></td>
                                    <td>
                                        <?php
                                        $estadoClase = 'estado-sin-ejecutar';
                                        switch (strtolower($vencimiento['estado_actividad'])) {
                                            case 'ejecutado':
                                                $estadoClase = 'estado-ejecutado';
                                                break;
                                            case 'pendiente':
                                                $estadoClase = 'estado-pendiente';
                                                break;
                                        }
                                        ?>
                                        <span class="estado <?= $estadoClase ?>">
                                            <?= esc($vencimiento['estado_actividad']) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($vencimiento['observaciones'] ?? 'N/A') ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                En este momento no tienes vencimientos asignados.
            </div>
        <?php endif; ?>
    </div>

    <!-- jQuery (requerido por DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#vencimientosTableSinEjecutar').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                "columnDefs": [{
                    "orderable": false,
                    "targets": [3]
                }],
                "pageLength": 10,
                "lengthMenu": [5, 10, 25, 50, 100]
            });
            $('#vencimientosTableEjecutado').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                "columnDefs": [{
                    "orderable": false,
                    "targets": [3]
                }],
                "pageLength": 10,
                "lengthMenu": [5, 10, 25, 50, 100]
            });
        });
    </script>
</body>

</html>
