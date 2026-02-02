<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Enlace no valido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            max-width: 500px;
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card error-card mx-auto">
            <div class="card-body text-center py-5 px-4">
                <div class="text-danger mb-4">
                    <i class="bi bi-exclamation-triangle" style="font-size: 5rem;"></i>
                </div>
                <h3 class="text-danger mb-3">Enlace No Valido</h3>
                <p class="text-muted mb-4"><?= esc($mensaje) ?></p>
                <div class="alert alert-light">
                    <small class="text-muted">
                        Si cree que esto es un error, contacte al administrador del sistema o solicite un nuevo enlace de aprobacion.
                    </small>
                </div>
            </div>
            <div class="card-footer text-center text-muted bg-light">
                <small>Enterprise SST - Sistema de Gestion de Seguridad y Salud en el Trabajo</small>
            </div>
        </div>
    </div>
</body>
</html>
