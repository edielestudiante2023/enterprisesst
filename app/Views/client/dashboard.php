<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprisesst, SG-SST Empresas</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilo global */
        body {
            background-color: #f5f7fa;
            color: #1c2437;
            font-family: Arial, sans-serif;
        }

        /* Navbar fija */
        .navbar {
            background-color: whitesmoke;
            /* Azul oscuro */
            border-bottom: 2px solid #bd9751;
            /* Dorado */
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
        }

        .navbar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
        }

        .navbar-content img {
            max-height: 80px;
        }

        /* Espaciado del contenido principal */
        .content-wrapper {
            margin-top: 120px;
        }

        /* Botones personalizados */
        .btn-primary-custom {
            background-color: #1c2437;
            /* Azul oscuro */
            color: #ffffff;
            /* Blanco */
            border: none;
        }

        .btn-primary-custom:hover {
            background-color: #16202c;
            color: #ffffff;
        }

        .btn-success-custom {
            background-color: #bd9751;
            /* Dorado */
            color: #ffffff;
            border: none;
        }

        .btn-success-custom:hover {
            background-color: #a07f42;
            color: #ffffff;
        }

        .btn-info-custom {
            background-color: #ffffff;
            /* Blanco */
            color: #1c2437;
            /* Azul oscuro */
            border: 1px solid #1c2437;
        }

        .btn-info-custom:hover {
            background-color: #f0f0f0;
            color: #1c2437;
        }

        /* Tabla de accesos */
        #accesosTable {
            margin-top: 20px;
        }

        #accesosTable th {
            background-color: #1c2437;
            color: #ffffff;
            text-align: left;
        }

        #accesosTable td {
            text-align: left;
        }

        /* Botón de cerrar sesión */
        .btn-logout {
            background-color: #ff4d4d;
            color: white;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 5px;
        }

        .btn-logout:hover {
            background-color: #e63939;
        }

        /* Sección de asesoría */
        .asesoria-card {
            background-color: #e9ecef;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-top: 40px;
        }

        .asesoria-card img {
            max-height: 100px;
            margin: 1rem auto;
        }

        /* Footer */
        footer {
            background-color: #1c2437;
            color: #ffffff;
            padding: 15px 0;
        }
    </style>
</head>

<body>

    <!-- Navbar fija -->
    <nav class="navbar">
        <div class="container navbar-content">
            <!-- Logo izquierdo -->
            <a href="https://dashboard.cycloidtalent.com/login" target="_blank">
                <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Logo Enterprisesst">
            </a>
            <!-- Logo central -->
            <a href="https://cycloidtalent.com/index.php/consultoria-sst" target="_blank">
                <img src="<?= base_url('uploads/logosst.png') ?>" alt="Logo SST">
            </a>
            <!-- Logo derecho -->
            <a href="https://cycloidtalent.com/" target="_blank">
                <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Logo Cycloid">
            </a>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="content-wrapper">
        <div class="container">
            <!-- Header -->
            <div class="welcome-header text-center">
                <h1>¡<?= esc($client['nombre_cliente']) ?>!</h1>
                <p>Bienvenido a Enterprisesst, tu aplicativo especializado en SG-SST</p>
            </div>

            <!-- Quick Access Buttons -->
            <div class="quick-access text-center">
                <div class="row justify-content-center">
                    <div class="col-md-4 mb-3">
                        <a href="<?= base_url('listPlanTrabajoCliente/' . $client['id_cliente']) ?>" target="_blank" class="btn btn-primary-custom w-100">
                            <i class="fas fa-calendar-alt"></i> Plan de Trabajo
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="<?= base_url('/report_dashboard') ?>" target="_blank" class="btn btn-success-custom w-100">
                            <i class="fas fa-file-alt"></i> Documentos
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="<?= base_url('client/panel') ?>" target="_blank" class="btn btn-info-custom w-100">
                            <i class="fas fa-chart-line"></i> Panel de Gestión
                        </a>
                    </div>
                </div>
            </div>

            <!-- Título -->
            <h4 class="text-center" style="color: #bd9751;">Dispositivos Documentales Sistema de Gestión en Seguridad y Salud en el Trabajo</h4>

            <!-- Tabla -->
            <table id="accesosTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Acceso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $current_dimension = '';
                    $index = 1;

                    foreach ($accesos as $acceso):
                        if ($current_dimension !== $acceso['dimension']):
                            $current_dimension = $acceso['dimension']; ?>
                            <tr>
                                <td colspan="2" style="background-color: #f8f9fa; font-weight: bold; text-align: left;">
                                    <?= esc($current_dimension) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td><?= $index++ ?></td>
                            <td><a href="<?= base_url($acceso['url']) ?>" target="_blank"><?= esc($acceso['nombre']) ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>


            <div class="text-center mt-4">
                <a href="<?= base_url('/logout') ?>" rel="noopener noreferrer">
                    <button type="button" class="btn btn-logout" aria-label="Cerrar Sesión">Cerrar Sesión</button>
                </a>
            </div>

            <!-- Asesoría Section -->
            <div class="asesoria-card">
                <h2>¿Necesitas Asesoría?</h2>
                <p>Contáctanos para obtener ayuda en la gestión de tu SST.</p>
                <div>
                    <img src="<?= base_url('uploads/logocycloid.png') ?>" alt="Cycloid">
                </div>
                <p><strong>Email:</strong> diana.cuestas@cycloidtalent.com</p>
                <p><strong>Teléfono:</strong> 3229074371</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <p>&copy; 2024 Cycloid Talent SAS. Todos los derechos reservados.</p>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function logout() {
            alert('Cerrar sesión presionado');
        }
    </script>
</body>

</html>