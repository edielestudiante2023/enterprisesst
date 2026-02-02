<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta ya firmada</title>
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
        .info-container {
            max-width: 500px;
            padding: 20px;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .info-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .info-icon i {
            font-size: 50px;
            color: white;
        }
        .firma-registrada {
            background: #d4edda;
            border-radius: 10px;
            padding: 15px;
        }
    </style>
</head>
<body>
    <div class="info-container">
        <div class="card">
            <div class="card-body text-center p-5">
                <div class="info-icon">
                    <i class="bi bi-check-circle"></i>
                </div>

                <h2 class="mb-3">Acta ya firmada</h2>
                <p class="text-muted mb-4">
                    <?= esc($mensaje ?? 'Usted ya ha firmado esta acta anteriormente.') ?>
                </p>

                <div class="firma-registrada text-start">
                    <div class="row">
                        <div class="col-12">
                            <small class="text-muted">Firmante:</small>
                            <p class="mb-2"><strong><?= esc($asistente['nombre_completo']) ?></strong></p>
                        </div>
                        <?php if (!empty($asistente['firma_fecha'])): ?>
                        <div class="col-12">
                            <small class="text-muted">Fecha de firma:</small>
                            <p class="mb-0"><strong><?= date('d/m/Y H:i', strtotime($asistente['firma_fecha'])) ?></strong></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-muted mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        No es necesario firmar nuevamente.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
