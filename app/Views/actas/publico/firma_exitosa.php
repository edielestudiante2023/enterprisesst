<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Registrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-container {
            max-width: 500px;
            padding: 20px;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: pulse 2s infinite;
        }
        .success-icon i {
            font-size: 50px;
            color: white;
        }
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(17, 153, 142, 0.4); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 20px rgba(17, 153, 142, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(17, 153, 142, 0); }
        }
        .firma-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
        }
        .progreso-container {
            background: #e9ecef;
            border-radius: 20px;
            padding: 4px;
            margin-top: 20px;
        }
        .progreso-container .progress {
            height: 30px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="card">
            <div class="card-body text-center p-5">
                <div class="success-icon">
                    <i class="bi bi-check-lg"></i>
                </div>

                <h2 class="mb-3">Firma Registrada</h2>
                <p class="text-muted mb-4">
                    Su firma ha sido registrada exitosamente en el acta
                    <strong><?= esc($acta['numero_acta']) ?></strong>
                </p>

                <div class="firma-info text-start">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Firmante:</small>
                            <p class="mb-2"><strong><?= esc($asistente['nombre_completo']) ?></strong></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Fecha y hora:</small>
                            <p class="mb-2"><strong><?= date('d/m/Y H:i') ?></strong></p>
                        </div>
                    </div>
                </div>

                <!-- Progreso de firmas -->
                <div class="progreso-container">
                    <?php $porcentaje = $totalFirmantes > 0 ? ($firmados / $totalFirmantes) * 100 : 0; ?>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: <?= $porcentaje ?>%">
                            <?= $firmados ?> de <?= $totalFirmantes ?> firmas
                        </div>
                    </div>
                </div>

                <?php if ($firmados >= $totalFirmantes): ?>
                <div class="alert alert-success mt-4 mb-0">
                    <i class="bi bi-trophy me-2"></i>
                    <strong>El acta esta completa.</strong><br>
                    Todas las firmas han sido registradas.
                </div>
                <?php else: ?>
                <p class="text-muted mt-4 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Faltan <?= $totalFirmantes - $firmados ?> firma(s) para completar el acta.
                </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mt-4">
            <small class="text-white-50">
                <i class="bi bi-shield-check me-1"></i>
                Firma verificada y almacenada de forma segura
            </small>
        </div>
    </div>
</body>
</html>
