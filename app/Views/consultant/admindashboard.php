<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administración</title>
    <!-- Favicon -->
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            /* Fondo claro */
            color: #333333;
            /* Texto oscuro para mejor legibilidad */
            font-family: Arial, sans-serif;
        }

        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 0;
        }

        .navbar-brand img {
            max-height: 50px;
        }

        .header-logos img {
            max-height: 50px;
            margin-right: 10px;
        }

        .content {
            padding: 20px;
        }

        .welcome-banner {
            background-color: #e9ecef;
            border-left: 5px solid #0d6efd;
            color: #333333;
        }

        .welcome-banner h3 {
            color: #0d6efd;
        }

        .table th {
            background-color: #0d6efd;
            color: #ffffff;
        }

        .table td a {
            color: #0d6efd;
            text-decoration: none;
        }

        .table td a:hover {
            color: #0b5ed7;
            text-decoration: underline;
        }

        .logout-button {
            text-align: center;
            margin-top: 20px;
        }

        /* Botones personalizados */
        .btn-outline-secondary {
            border-color: #6c757d;
            color: #6c757d;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #ffffff;
        }

        .btn-primary-custom {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #ffffff;
        }

        .btn-primary-custom:hover {
            background-color: #ffffff;
            color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-danger-custom {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #ffffff;
        }

        .btn-danger-custom:hover {
            background-color: #ffffff;
            color: #dc3545;
            border-color: #dc3545;
        }

        .footer {
            background-color: #ffffff;
            color: #333333;
        }

        .footer a {
            color: #0d6efd;
            text-decoration: none;
        }

        .footer a:hover {
            color: #0b5ed7;
            text-decoration: underline;
        }

        .navbar-spacing {
            height: 100px;
        }

        @media (max-width: 768px) {
            .header-logos {
                flex-direction: column;
                align-items: center;
            }

            .header-logos img {
                margin-bottom: 10px;
            }
        }

        .highlighted {
            background-color: #cce5ff !important;
            transition: background-color 0.5s ease;
        }

        .custom-search {
            margin-bottom: 15px;
        }

        tfoot th {
            padding: 8px 10px;
            background-color: #f8f9fa;
        }

        .description-col {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        tfoot select {
            width: 100%;
            padding: 4px;
            box-sizing: border-box;
            background-color: #f8f9fa;
            color: #333;
        }

        tfoot th {
            background-color: #e9ecef;
        }
    </style>
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg fixed-top">
            <div class="container-fluid">
                <div class="header-logos d-flex justify-content-between align-items-center w-100">
                    <!-- Logo izquierdo -->
                    <div>
                        <a href="https://dashboard.cycloidtalent.com/login" target="_blank" rel="noopener noreferrer">
                            <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo">
                        </a>
                    </div>
                    <!-- Logo centro -->
                    <div>
                        <a href="https://cycloidtalent.com/index.php/consultoria-sst" target="_blank" rel="noopener noreferrer">
                            <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo">
                        </a>
                    </div>
                    <!-- Logo derecho -->
                    <div>
                        <a href="https://cycloidtalent.com/" target="_blank" rel="noopener noreferrer">
                            <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo">
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Espaciador para evitar que el contenido se oculte bajo el navbar fijo -->
    <div class="navbar-spacing"></div>

    <main class="container-fluid content">
        <div class="welcome-banner p-4 mb-4 rounded">
            <h3 class="mb-3">¡Bienvenido al Dashboard de Administración en Propiedad Horizontal de Cycloid Talent!</h3>
            <p class="mt-3">Explora las diferentes secciones y aprovecha las herramientas disponibles para optimizar tu desempeño.</p>
        </div>

        <div class="table-responsive">
            <table id="itemTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rol</th>
                        <th>Tipo de Proceso</th>
                        <th>Detalle</th>
                        <th>Descripción</th>
                        <th>Acción URL</th>
                        <th>Orden</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Rol</th>
                        <th>Tipo de Proceso</th>
                        <th>Detalle</th>
                        <th>Descripción</th>
                        <th>Acción URL</th>
                        <th>Orden</th>
                        <th></th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= esc($item['id']) ?></td>
                            <td><?= esc($item['rol']) ?></td>
                            <td><?= esc($item['tipo_proceso']) ?></td>
                            <td><?= esc($item['detalle']) ?></td>
                            <td><?= esc($item['descripcion']) ?></td>
                            <td>
                                <a href="<?= base_url($item['accion_url']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm" title="Ir a <?= esc($item['detalle']) ?>">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </td>
                            <td><?= esc($item['orden']) ?></td>
                            <td>
                                <a href="<?= base_url('consultant/edititemdashboar/' . $item['id']) ?>" class="btn btn-warning btn-sm" target="_blank">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>

                                <!-- <a href="<?= base_url('consultant/deleteitemdashboard/' . $item['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                    <i class="bi bi-trash"></i> Delete
                                </a> -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="logout-button">
            <a href="<?= base_url('/logout') ?>" rel="noopener noreferrer">
                <button type="button" class="btn btn-danger-custom" aria-label="Cerrar Sesión">Cerrar Sesión</button>
            </a>
        </div>
    </main>

    <footer class="footer mt-auto py-3 border-top">
        <div class="container text-center">
            <p class="fw-bold mb-0">Cycloid Talent SAS</p>
            <p class="mb-0">Todos los derechos reservados © <span id="currentYear"></span></p>
            <p class="mb-0">NIT: 901.653.912</p>
            <p class="mb-0">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" rel="noopener noreferrer">https://cycloidtalent.com/</a>
            </p>
            <p class="mt-3 mb-0"><strong>Nuestras Redes Sociales:</strong></p>
            <div class="d-flex justify-content-center gap-3">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank" rel="noopener noreferrer" class="text-secondary text-decoration-none" aria-label="Facebook">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" rel="noopener noreferrer" class="text-secondary text-decoration-none" aria-label="LinkedIn">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" rel="noopener noreferrer" class="text-secondary text-decoration-none" aria-label="Instagram">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" rel="noopener noreferrer" class="text-secondary text-decoration-none" aria-label="TikTok">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
                </a>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#itemTable').DataTable({
                order: [
                    [6, 'asc']
                ], // Ordena por la columna "Orden" (índice 6) de forma ascendente
                columnDefs: [{
                        targets: 0,
                        visible: false
                    }, // Oculta la columna "ID" (índice 0)
                    {
                        targets: 6,
                        visible: false
                    } // Oculta la columna "Orden" (índice 6), pero se usa para el ordenamiento
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                initComplete: function() {
                    this.api().columns().every(function() {
                        var column = this;
                        var select = $('<select><option value=""></option></select>')
                            .appendTo($(column.footer()).empty())
                            .on('change', function() {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column.search(val ? '^' + val + '$' : '', true, false).draw();
                            });

                        column.data().unique().sort().each(function(d, j) {
                            if (d) {
                                select.append('<option value="' + d + '">' + d + '</option>');
                            }
                        });
                    });
                }
            });
        });
    </script>
</body>

</html>