<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos por Subtema - Enterprisesst</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
            color: #333;
        }

        .container {
            margin-top: 30px;
            max-width: 1200px;
        }

        .table-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .table-container h2 {
            color: #333;
            font-weight: 600;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .dataTables_wrapper .dataTables_filter input {
            margin-left: 0.5em;
            display: inline-block;
            width: auto;
        }

        .empty-message {
            color: #333;
            font-size: 18px;
            text-align: center;
            padding: 20px;
        }

        /* Responsive table adjustments */
        @media (max-width: 768px) {
            .table-container {
                padding: 10px;
            }

            .table-container h2 {
                font-size: 20px;
            }

            .table th,
            .table td {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>

    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">

            <!-- Logos -->
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

    <!-- Espacio para el Navbar Fijo -->
    <div style="height: 120px;"></div>
    <div class="container">
        <?php foreach ($topicsList as $key => $titulo): ?>
            <div class="table-container">
                <h2><?= esc($titulo) ?></h2>
                <?php
                // Acceder a los reportes correspondientes al tema actual
                $reportes = ${$key};
                ?>
                <?php if (!empty($reportes)) : ?>
                    <table id="<?= esc($key) ?>Table" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Enlace</th>
                                <th>Estado</th>
                                <th>Tipo de Reporte</th>
                                <th>Detalle</th>
                                <th>Observaciones</th>
                                <th>Creado el</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportes as $reporte) : ?>
                                <tr>
                                    <td><?= esc($reporte['titulo_reporte']) ?></td>
                                    <td>
                                        <a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary">
                                            <i class="fas fa-file-alt me-1"></i> Ver
                                        </a>
                                    </td>
                                    <td><?= esc($reporte['estado']) ?></td>
                                    <td><?= esc($reporte['tipo_reporte']) ?></td>
                                    <td><?= esc($reporte['detalle_reporte']) ?></td>
                                    <td><?= esc($reporte['observaciones']) ?></td>
                                    <td><?= esc($reporte['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p class="empty-message">Aún no hay reportes de Gestión disponibles.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>




    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle JS (Includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            // Lista de claves para las tablas basadas en los temas
            var tableKeys = <?= json_encode(array_keys($topicsList)) ?>;

            var dataTableConfig = {
                responsive: true,
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print', 'colvis'],
                order: [
                    [5, 'desc']
                ],
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            };

            // Inicializar DataTables para cada tabla generada
            tableKeys.forEach(function(key) {
                $('#' + key + 'Table').DataTable(dataTableConfig);
            });

            // ... (mantener otras lógicas, como manejo de botones de eliminar si es necesario)
        });
    </script>

</body>

</html>