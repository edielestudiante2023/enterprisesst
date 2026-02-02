<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enlace no valido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            max-width: 500px;
            padding: 20px;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .error-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .error-icon i {
            font-size: 50px;
            color: white;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="card">
            <div class="card-body text-center p-5">
                <div class="error-icon">
                    <i class="bi bi-x-lg"></i>
                </div>

                <h2 class="mb-3">Enlace no valido</h2>
                <p class="text-muted mb-4">
                    <?= esc($mensaje ?? 'El enlace que intenta acceder no es valido o ha expirado.') ?>
                </p>

                <?php if (!empty($sugerencia)): ?>
                <div class="alert alert-info text-start">
                    <i class="bi bi-lightbulb me-2"></i>
                    <?= esc($sugerencia) ?>
                </div>
                <?php endif; ?>

                <div class="d-grid">
                    <a href="<?= base_url() ?>" class="btn btn-primary">
                        <i class="bi bi-house me-2"></i>Ir al Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
