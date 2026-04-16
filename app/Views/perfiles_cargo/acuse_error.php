<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>Acuse</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-5">
    <div class="alert alert-danger text-center">
        <h4>No fue posible acceder al acuse</h4>
        <p class="mb-0"><?= esc($mensaje ?? 'Error desconocido') ?></p>
    </div>
</div></body></html>
