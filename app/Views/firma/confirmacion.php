<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Exitosa</title>
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
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="text-success mb-3">Firma Registrada</h2>
                        <p class="text-muted mb-4">
                            Su firma electrónica ha sido registrada exitosamente.
                            <br>Se ha enviado una copia de confirmación a su correo electrónico.
                        </p>

                        <div class="alert alert-light text-start">
                            <p class="mb-1"><strong>Firmante:</strong> <?= esc($solicitud['firmante_nombre']) ?></p>
                            <p class="mb-1"><strong>Tipo de firma:</strong> <?= ucfirst($solicitud['firmante_tipo']) ?></p>
                            <p class="mb-0"><strong>Fecha:</strong> <?= date('d/m/Y H:i:s') ?></p>
                        </div>

                        <p class="small text-muted mt-4">
                            <i class="bi bi-shield-check me-1"></i>
                            Esta firma cumple con los requisitos de la Ley 527 de 1999
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
