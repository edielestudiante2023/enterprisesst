<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Consultor 2025</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Roboto', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Cabecera */
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
        }

        .header-logos img {
            max-height: 60px;
            margin-right: 15px;
        }

        /* Banner de bienvenida animado con tonos violetas */
        .welcome-banner {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #8A2BE2, #B19CD9);
            padding: 30px 30px;
            border-radius: 12px;
            text-align: center;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }

        .welcome-banner::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 8s linear infinite;
            z-index: -1;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .welcome-banner h3 {
            font-size: 2.2rem;
            margin-bottom: 15px;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .welcome-banner h4 {
            font-size: 1.6rem;
            margin-bottom: 10px;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        .welcome-banner p {
            font-size: 1.3rem;
            position: relative;
            z-index: 1;
        }

        /* Tabla a pantalla completa */
        .table-responsive {
            margin-bottom: 30px;
        }

        .table {
            width: 100% !important;
        }

        .table thead {
            background-color: #2575fc;
            color: #fff;
        }

        /* Botón de Cerrar Sesión */
        .btn-logout {
            background-color: #dc3545;
            border: none;
            color: #fff;
            border-radius: 50px;
            padding: 10px 30px;
            font-weight: 500;
        }

        .btn-logout:hover {
            background-color: #c82333;
        }

        /* Footer */
        footer {
            background-color: #ffffff;
            padding: 20px 0;
            border-top: 1px solid #dee2e6;
            margin-top: 30px;
            text-align: center;
        }

        footer a {
            color: #2575fc;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <!-- Cabecera -->
    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <div class="d-flex align-items-center justify-content-between w-100 header-logos">
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

    <!-- Contenido principal -->
    <main class="container-fluid my-5">
        <!-- Banner de Bienvenida -->
        <div class="welcome-banner">
            <h3>Enterprisesst - PH // Consultor</h3>
            <h4>Hola mi Nata, Dianita y Eleyson</h4>
            <p class="mb-0">¡Con Enterprisesst - PH nos vamos a Comer el Mundo!</p>
        </div>

        <!-- Tabla a pantalla completa -->
        <div class="table-responsive">
            <table id="itemTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo de Proceso</th>
                        <th>Detalle</th>
                        <th>Descripción</th>
                        <th>Acción URL</th>
                        <th>Orden</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Tipo de Proceso</th>
                        <th>Detalle</th>
                        <th>Descripción</th>
                        <th>Acción URL</th>
                        <th>Orden</th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= esc($item['id']) ?></td>
                            <td><?= esc($item['tipo_proceso']) ?></td>
                            <td><?= esc($item['detalle']) ?></td>
                            <td><?= esc($item['descripcion']) ?></td>
                            <td>
                                <a href="<?= base_url($item['accion_url']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm" title="Ir a <?= esc($item['detalle']) ?>">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </td>
                            <td><?= esc($item['orden']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Botón de Cerrar Sesión -->
        <div class="text-center">
            <a href="<?= base_url('/logout') ?>" rel="noopener noreferrer">
                <button type="button" class="btn btn-logout">Cerrar Sesión</button>
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p class="fw-bold mb-1">Cycloid Talent SAS</p>
            <p class="mb-1">Todos los derechos reservados © <span id="currentYear"></span></p>
            <p class="mb-1">NIT: 901.653.912</p>
            <p class="mb-0">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" rel="noopener noreferrer">https://cycloidtalent.com/</a>
            </p>
            <div class="mt-3">
                <strong>Nuestras Redes Sociales:</strong>
                <div class="d-flex justify-content-center gap-3 mt-2">
                    <a href="https://www.facebook.com/CycloidTalent" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                        <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
                    </a>
                    <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                        <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
                    </a>
                    <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                        <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
                    </a>
                    <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                        <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
                    </a>
                </div>
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
                    [5, 'asc']
                ], // Ordena internamente por la columna "Orden" (índice 5)
                columnDefs: [{
                        targets: [0, 5],
                        visible: false
                    } // Oculta las columnas "ID" y "Orden"
                ],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                initComplete: function() {
                    this.api().columns().every(function() {
                        var column = this;
                        var select = $('<select class="form-select form-select-sm"><option value=""></option></select>')
                            .appendTo($(column.footer()).empty())
                            .on('change', function() {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column.search(val ? '^' + val + '$' : '', true, false).draw();
                            });
                        column.data().unique().sort().each(function(d) {
                            if (d) {
                                select.append('<option value="' + d + '">' + d + '</option>');
                            }
                        });
                    });
                }
            });
            // Actualiza el año en el footer
            $('#currentYear').text(new Date().getFullYear());
        });
    </script>
</body>

</html>