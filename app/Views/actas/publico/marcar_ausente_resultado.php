<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado - Marcar Ausente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .result-container {
            max-width: 500px;
            padding: 20px;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .result-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .result-icon i {
            font-size: 50px;
            color: white;
        }
    </style>
</head>
<?php
    $bgColor = match($resultado ?? 'error') {
        'aprobada' => 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
        'rechazada' => 'linear-gradient(135deg, #fc5c7d 0%, #6a82fb 100%)',
        default => 'linear-gradient(135deg, #f97316 0%, #ea580c 100%)'
    };
    $iconClass = match($resultado ?? 'error') {
        'aprobada' => 'bi-check-circle-fill',
        'rechazada' => 'bi-x-circle-fill',
        default => 'bi-exclamation-triangle-fill'
    };
    $iconBg = $bgColor;
    $titulo = match($resultado ?? 'error') {
        'aprobada' => 'Marcado como Ausente',
        'rechazada' => 'Solicitud Rechazada',
        default => 'Error'
    };
?>
<body style="background: <?= $bgColor ?>;">
    <div class="result-container">
        <div class="card">
            <div class="card-body text-center p-5">
                <div class="result-icon" style="background: <?= $iconBg ?>;">
                    <i class="bi <?= $iconClass ?>"></i>
                </div>

                <h3 class="mb-3"><?= $titulo ?></h3>
                <p class="text-muted mb-4"><?= esc($mensaje) ?></p>

                <?php if (($resultado ?? '') === 'aprobada' && !empty($asistente)): ?>
                <div class="bg-light rounded p-3 mb-3">
                    <p class="mb-1"><strong>Persona:</strong> <?= esc($asistente['nombre_completo']) ?></p>
                    <p class="mb-0"><strong>Estado:</strong> <span class="badge bg-danger">Ausente</span></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mt-3">
            <small class="text-white-50">EnterpriseSST - Sistema de Gestion de Seguridad y Salud en el Trabajo</small>
        </div>
    </div>
</body>
</html>
