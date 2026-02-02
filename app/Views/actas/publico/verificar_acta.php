<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Acta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .verify-container {
            max-width: 500px;
            width: 100%;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .verify-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .verify-icon.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .verify-icon.error {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
        }
        .verify-icon i {
            font-size: 40px;
            color: white;
        }
        .info-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="card">
            <div class="card-body text-center p-5">
                <?php if (isset($acta) && $acta): ?>
                    <!-- Acta válida -->
                    <div class="verify-icon success">
                        <i class="bi bi-check-lg"></i>
                    </div>

                    <h2 class="mb-3 text-success">Acta Verificada</h2>
                    <p class="text-muted mb-4">
                        Este documento es autentico y fue generado por EnterpriseSST.
                    </p>

                    <div class="text-start">
                        <div class="info-item">
                            <div class="row">
                                <div class="col-5"><small class="text-muted">Numero de Acta:</small></div>
                                <div class="col-7"><strong><?= esc($acta['numero_acta']) ?></strong></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="row">
                                <div class="col-5"><small class="text-muted">Empresa:</small></div>
                                <div class="col-7"><strong><?= esc($cliente['nombre_cliente']) ?></strong></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="row">
                                <div class="col-5"><small class="text-muted">Comite:</small></div>
                                <div class="col-7"><strong><?= esc($comite['tipo_nombre']) ?></strong></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="row">
                                <div class="col-5"><small class="text-muted">Fecha Reunion:</small></div>
                                <div class="col-7"><strong><?= date('d/m/Y', strtotime($acta['fecha_reunion'])) ?></strong></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="row">
                                <div class="col-5"><small class="text-muted">Estado:</small></div>
                                <div class="col-7">
                                    <?php
                                    $estadoBadges = [
                                        'borrador' => '<span class="badge bg-secondary">Borrador</span>',
                                        'pendiente_firma' => '<span class="badge bg-warning text-dark">En Firma</span>',
                                        'firmada' => '<span class="badge bg-success">Firmada</span>',
                                        'cerrada' => '<span class="badge bg-primary">Cerrada</span>'
                                    ];
                                    echo $estadoBadges[$acta['estado']] ?? '<span class="badge bg-secondary">-</span>';
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="row">
                                <div class="col-5"><small class="text-muted">Firmas:</small></div>
                                <div class="col-7"><strong><?= $firmados ?> de <?= $totalFirmantes ?></strong></div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Código no válido -->
                    <div class="verify-icon error">
                        <i class="bi bi-x-lg"></i>
                    </div>

                    <h2 class="mb-3 text-danger">Codigo no Valido</h2>
                    <p class="text-muted mb-4">
                        El codigo de verificacion ingresado no corresponde a ninguna acta en nuestro sistema.
                    </p>

                    <div class="alert alert-warning text-start">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Posibles causas:</strong>
                        <ul class="mb-0 mt-2">
                            <li>El codigo fue ingresado incorrectamente</li>
                            <li>El documento no fue generado por EnterpriseSST</li>
                            <li>El documento podria ser falso o alterado</li>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Formulario de verificación -->
                <hr class="my-4">
                <h6 class="mb-3">Verificar otro codigo</h6>
                <form action="<?= base_url('actas/verificar') ?>" method="get">
                    <div class="input-group">
                        <input type="text" class="form-control" name="codigo"
                               placeholder="Ingrese codigo de verificacion"
                               value="<?= esc($codigo ?? '') ?>" required>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-4">
            <small class="text-white-50">
                <i class="bi bi-shield-check me-1"></i>
                Sistema de verificacion de documentos EnterpriseSST
            </small>
        </div>
    </div>
</body>
</html>
