<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma no disponible</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background:#f0f2f5; min-height:100vh; display:flex; align-items:center; justify-content:center; font-family:'Segoe UI', sans-serif; }
        .error-card { background:white; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.1); padding:40px 30px; max-width:420px; margin:20px; text-align:center; }
        .error-icon { font-size:64px; color:#dc3545; margin-bottom:20px; }
        h3 { color:#2c3e50; margin-bottom:12px; }
        p { color:#666; font-size:15px; margin-bottom:0; }
    </style>
</head>
<body>
    <div class="error-card">
        <i class="fas fa-exclamation-circle error-icon"></i>
        <h3>Firma no disponible</h3>
        <p><?= esc($mensaje) ?></p>
    </div>
</body>
</html>
