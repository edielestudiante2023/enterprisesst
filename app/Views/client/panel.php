<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .dashboard-title {
            color: #003366;
        }

        .accordion-button {
            background-color: #007bff;
            color: white;
            border: none;
            transition: background-color 0.3s ease-in-out;
        }

        .accordion-button:hover {
            background-color: #0056b3;
        }

        .grid-container {
            margin-top: 20px;
        }

        .grid-item {
            background-color: #eef2f7;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }

        .grid-item:hover {
            transform: scale(1.05);
        }

        .grid-item a {
            text-decoration: none;
            color: #003366;
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        .grid-item i {
            font-size: 2rem;
            color: #007bff;
        }

        .icon-container img {
            width: 200px;
            height: auto;
            transition: transform 0.3s ease-in-out;
        }

        .icon-container img:hover {
            transform: scale(1.1);
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="text-center mb-5">
            <h1 class="dashboard-title">Dashboard de Gestión</h1>
            <p>Bienvenido al panel de gestión de <?= esc($client['nombre_cliente']) ?></p>
        </div>

        <div class="card shadow-lg p-4 mb-4">
            <h2 class="text-center text-primary">Información del Cliente</h2>
            <p><strong>Nombre:</strong> <?= esc($client['nombre_cliente']) ?></p>
            <p><strong>Estándar:</strong> <?= esc($estandar) ?></p>
        </div>

        <?php if (!$isRestricted) : ?>
            <div class="accordion" id="menuGestion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelGestion-<?= $client['id_cliente'] ?>">
                            Gestión para <?= esc($client['nombre_cliente']) ?>
                        </button>
                    </h2>
                    <div id="panelGestion-<?= $client['id_cliente'] ?>" class="accordion-collapse collapse show" data-bs-parent="#menuGestion">
                        <div class="accordion-body">
                            <!-- Grilla 2x2 de funcionalidades -->
                            <div class="container grid-container">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="grid-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <a href="<?= base_url('listCronogramasCliente/' . $client['id_cliente']) ?>" target="_blank">Cronograma de Capacitaciones</a>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="grid-item">
                                            <i class="fas fa-tasks"></i>
                                            <a href="<?= base_url('listPendientesCliente/' . $client['id_cliente']) ?>" target="_blank">Pendientes</a>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="grid-item">
                                            <i class="fas fa-chart-line"></i>
                                            <a href="<?= base_url('listEvaluaciones/' . $client['id_cliente']) ?>" target="_blank">Evaluaciones</a>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="grid-item">
                                            <i class="fas fa-folder-open"></i>
                                            <a href="<?= base_url('/viewDocuments') ?>" target="_blank">Gestor Documental</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección de imágenes en un container y fila centrada -->
                            <div class="container mt-5">
                                <div class="row justify-content-center text-center">
                                    <div class="col-md-6 mb-4">
                                        <div class="icon-container">
                                            <h5>Dashboard KPI</h5>
                                            <a href="<?= base_url('/client/lista-lookerstudio') ?>" target="_blank">
                                                <img src="<?= base_url('/uploads/kpi_image.png') ?>" alt="Ir al Dashboard" class="img-fluid">
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="icon-container">
                                            <h5>Matrices</h5>
                                            <a href="<?= base_url('/client/lista-matrices') ?>" target="_blank">
                                                <img src="<?= base_url('/uploads/xlsx.png') ?>" alt="Ir al Dashboard" class="img-fluid">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <div class="alert alert-warning mt-4" role="alert">
                <h5 style="text-align: center;">Entendemos que quieres aprovechar al máximo nuestras herramientas. Para acceder a esta función, te recomendamos actualizar tu plan. ¡Estamos aquí para asesorarte!</h5>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                title: 'Bienvenido',
                text: 'Nunca Antes Gestionar tu SG-SST fue tan fácil',
                icon: 'info',
                confirmButtonText: 'Entendido'
            });
        });
    </script>
</body>

</html>
