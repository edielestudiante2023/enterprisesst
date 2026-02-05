<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votacion - <?= esc($proceso['tipo_comite']) ?> <?= $proceso['anio'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .card-header {
            border-radius: 20px 20px 0 0 !important;
            padding: 2rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        .btn-votar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
        }
        .btn-votar:hover {
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .empresa-logo {
            max-height: 80px;
            max-width: 200px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <?php if (!empty($cliente['logo'])): ?>
                        <img src="<?= base_url($cliente['logo']) ?>" alt="Logo" class="empresa-logo mb-3 bg-white p-2 rounded">
                        <?php endif; ?>
                        <h4 class="mb-1"><?= esc($cliente['nombre_cliente']) ?></h4>
                        <p class="mb-0 opacity-75">Proceso Electoral <?= esc($proceso['tipo_comite']) ?> <?= $proceso['anio'] ?></p>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-person-badge text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="mt-3 mb-1">Acceso a Votacion</h5>
                            <p class="text-muted">Ingrese su documento de identidad para votar</p>
                        </div>

                        <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <form action="<?= base_url('votar/validar') ?>" method="post">
                            <input type="hidden" name="enlace" value="<?= esc($enlace) ?>">

                            <div class="mb-4">
                                <label for="documento" class="form-label fw-bold">
                                    <i class="bi bi-card-text me-1"></i>Documento de Identidad
                                </label>
                                <input type="text" class="form-control form-control-lg" id="documento" name="documento"
                                       placeholder="Ej: 1234567890" required autofocus
                                       pattern="[0-9]+" title="Solo numeros"
                                       inputmode="numeric">
                                <div class="form-text">Ingrese solo numeros, sin puntos ni espacios.</div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-votar btn-lg text-white">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar a Votar
                                </button>
                            </div>
                        </form>

                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between text-muted small">
                                <span><i class="bi bi-calendar me-1"></i>Votacion hasta:</span>
                                <strong><?= date('d/m/Y H:i', strtotime($proceso['fecha_fin_votacion'])) ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-center py-3">
                        <small class="text-muted">
                            <i class="bi bi-shield-check me-1"></i>
                            Su voto es secreto y anonimo
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
