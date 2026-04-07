<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enlace no valido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #bd9751 0%, #8b6914 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
<div class="text-center text-white p-4">
    <i class="fas fa-times-circle fa-4x mb-3" style="opacity:0.8;"></i>
    <h5><?= esc($mensaje ?? 'Enlace no valido o expirado') ?></h5>
    <p style="font-size:13px; opacity:0.85;">Solicite un nuevo enlace al consultor por WhatsApp.</p>
</div>
</body>
</html>
