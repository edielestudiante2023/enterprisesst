<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cuenta Bloqueada - Enterprisesst</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .blocked-container {
            max-width: 500px;
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .blocked-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="blocked-container">
        <div class="blocked-icon">&#128274;</div>
        <h2 class="text-danger">Cuenta Bloqueada</h2>
        <p class="mt-4">
            Su cuenta ha sido bloqueada temporalmente debido a múltiples intentos fallidos de inicio de sesión.
        </p>
        <p>
            Por favor, contacte al administrador del sistema para desbloquear su cuenta.
        </p>
        <hr>
        <p class="text-muted">
            <strong>Contacto:</strong><br>
            info@cycloidtalent.com
        </p>
        <a href="<?= base_url('/login') ?>" class="btn btn-primary mt-3">Volver al Login</a>
    </div>
</body>

</html>
