<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Firma Electrónica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-5">
                        <div class="mb-4">
                            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="text-danger mb-3">Error</h2>
                        <p class="text-muted mb-4">
                            <?= esc($error ?? 'No se pudo procesar la solicitud de firma.') ?>
                        </p>
                        <p class="small text-muted">
                            Si cree que esto es un error, por favor contacte al administrador del sistema.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
