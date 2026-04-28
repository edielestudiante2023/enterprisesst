<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inscripcion no disponible</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); min-height: 100vh; display: flex; align-items: center; padding: 20px; }
        .card-error { max-width: 480px; margin: 0 auto; border: none; border-radius: 14px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); padding: 40px 30px; text-align: center; background: white; }
        .icono-error { font-size: 64px; color: #f97316; margin-bottom: 18px; }
    </style>
</head>
<body>
    <div class="card-error">
        <div class="icono-error"><i class="fas fa-exclamation-triangle"></i></div>
        <h4 class="mb-3" style="color:#1e3a5f;">No es posible inscribirse</h4>
        <p class="text-muted"><?= esc($mensaje ?? 'Este enlace no es valido.') ?></p>
    </div>
</body>
</html>
