<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error de Votacion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            background: white;
            border-radius: 20px;
            padding: 50px;
            text-align: center;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .error-icon {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        .error-icon i {
            font-size: 4rem;
            color: white;
        }
        .error-icon.invalido { background: #dc3545; }
        .error-icon.ya_voto { background: #ffc107; }
        .error-icon.expirado { background: #6c757d; }
        .error-icon.no_activo { background: #17a2b8; }
        .error-icon.no_iniciado { background: #6f42c1; }
        .error-icon.finalizado { background: #343a40; }
    </style>
</head>
<body>
    <div class="error-card">
        <?php
        $iconos = [
            'invalido' => 'bi-x-circle',
            'ya_voto' => 'bi-check-circle',
            'expirado' => 'bi-hourglass-bottom',
            'no_activo' => 'bi-pause-circle',
            'no_iniciado' => 'bi-clock',
            'finalizado' => 'bi-stop-circle'
        ];
        $icono = $iconos[$tipo] ?? 'bi-exclamation-circle';
        ?>

        <div class="error-icon <?= esc($tipo) ?>">
            <i class="bi <?= $icono ?>"></i>
        </div>

        <?php if ($tipo === 'ya_voto'): ?>
            <h1 class="text-warning mb-3">Ya ha votado</h1>
        <?php elseif ($tipo === 'expirado'): ?>
            <h1 class="text-secondary mb-3">Enlace Expirado</h1>
        <?php elseif ($tipo === 'no_activo'): ?>
            <h1 class="text-info mb-3">Votacion No Activa</h1>
        <?php elseif ($tipo === 'no_iniciado'): ?>
            <h1 class="text-purple mb-3">Votacion No Iniciada</h1>
        <?php elseif ($tipo === 'finalizado'): ?>
            <h1 class="text-dark mb-3">Votacion Finalizada</h1>
        <?php else: ?>
            <h1 class="text-danger mb-3">Error de Acceso</h1>
        <?php endif; ?>

        <p class="lead text-muted mb-4">
            <?= esc($mensaje) ?>
        </p>

        <?php if ($tipo === 'ya_voto'): ?>
        <div class="alert alert-success">
            <i class="bi bi-shield-check me-2"></i>
            Su voto ya fue registrado anteriormente.
        </div>
        <?php endif; ?>

        <hr>

        <p class="text-muted small mb-0">
            Si cree que esto es un error, contacte al administrador del proceso electoral.
        </p>
    </div>
</body>
</html>
