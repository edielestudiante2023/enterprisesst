<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voto Registrado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 50px;
            text-align: center;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .success-icon {
            font-size: 6rem;
            color: #28a745;
            animation: pulse 1s ease-in-out;
        }
        @keyframes pulse {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        .checkmark {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        .checkmark i {
            font-size: 4rem;
            color: white;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="checkmark">
            <i class="bi bi-check-lg"></i>
        </div>

        <h1 class="text-success mb-3">Voto Registrado</h1>

        <p class="lead text-muted mb-4">
            Gracias <strong><?= esc($votante['nombres']) ?></strong>, su voto ha sido registrado exitosamente de forma anonima.
        </p>

        <div class="alert alert-success">
            <i class="bi bi-shield-check me-2"></i>
            Su participacion ha sido registrada de manera segura y confidencial.
        </div>

        <hr>

        <p class="text-muted small">
            <i class="bi bi-clock me-1"></i>
            Fecha y hora: <?= date('d/m/Y H:i:s') ?>
        </p>

        <p class="text-muted small mb-0">
            Ya puede cerrar esta ventana.
        </p>
    </div>
</body>
</html>
