<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cuenta Suspendida - Cycloid Talent</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        body {
            background-color: #f5f7fa;
            color: #1c2437;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Estilo para la barra lateral vertical */
        .vertical-sidebar {
            background-color: whitesmoke;
            border-right: 2px solid #bd9751;
            height: 100vh;
            padding-top: 20px;
            position: fixed;
            top: 0;
            left: 0;
            width: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            z-index: 1020;
        }

        .vertical-sidebar a img {
            max-height: 80px;
            width: auto;
        }

        /* Espacio para el contenido a la derecha de la barra lateral */
        .main-content {
            margin-left: 220px;
            /* Ancho de la barra lateral + margen */
            padding: 20px;
        }

        .card {
            margin-top: 50px;
        }

        .btn-primary-custom {
            background-color: #1c2437;
            color: #ffffff;
            border: none;
        }

        .btn-primary-custom:hover {
            background-color: #16202c;
            color: #ffffff;
        }

        footer {
            background-color: #1c2437;
            color: #ffffff;
            padding: 15px 0;
            text-align: center;
            margin-top: 40px;
        }

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
    </style>
</head>

<body>
    <!-- Barra lateral vertical con logotipos -->
    <div class="vertical-sidebar">
        <a href="https://dashboard.cycloidtalent.com/login" target="_blank">
            <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Logo Enterprisesst">
        </a>
        <a href="https://cycloidtalent.com/index.php/consultoria-sst" target="_blank">
            <img src="<?= base_url('uploads/logosst.png') ?>" alt="Logo SST">
        </a>
        <a href="https://cycloidtalent.com/" target="_blank">
            <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Logo Cycloid">
        </a>
    </div>

    <!-- Contenido principal -->
    <div class="main-content container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h1 class="card-title">Cuenta Suspendida</h1>
                        <p class="card-text">
                            Apreciado cliente,<br>
                            Hemos detectado que su cuenta está suspendida debido a que el contrato asociado se encuentra inactivo o vencido.
                            En <strong>Cycloid Talent</strong>, nos enorgullece ofrecerle el mejor servicio.
                        </p>
                        <p class="card-text">
                            Si desea solucionar este inconveniente o necesita más información, por favor contacte a nuestro equipo comercial. Estaremos encantados de ayudarle.
                        </p>
                    </div>
                </div>

                <!-- Sección de asesoría -->
                <div class="asesoria-card">
                    <h2>¿Desea reactivar sus servicios?</h2>
                    <p>Estamos aquí para brindarle acompañamiento experto en la gestión de su SST.</p>
                    <div>
                        <img src="<?= base_url('uploads/logocycloid.png') ?>" alt="Cycloid">
                    </div>
                    <p><strong>Email:</strong> diana.cuestas@cycloidtalent.com</p>
                    <p><strong>Teléfono:</strong> 3229074371</p>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            © <?= date('Y') ?> Cycloid Talent. Todos los derechos reservados.
        </div>
    </footer>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>