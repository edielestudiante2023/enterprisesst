<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarea Actualizada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-container {
            max-width: 500px;
            padding: 20px;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: pulse 2s infinite;
        }
        .success-icon i {
            font-size: 50px;
            color: white;
        }
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(17, 153, 142, 0.4); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 20px rgba(17, 153, 142, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(17, 153, 142, 0); }
        }
        .status-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="card">
            <div class="card-body text-center p-5">
                <div class="success-icon">
                    <i class="bi bi-check-lg"></i>
                </div>

                <h2 class="mb-3">Tarea Actualizada</h2>
                <p class="text-muted mb-4">
                    El estado de la tarea ha sido actualizado correctamente.
                </p>

                <div class="status-info text-start">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <small class="text-muted">Tarea:</small>
                            <p class="mb-0"><strong><?= esc(substr($compromiso['descripcion'], 0, 100)) ?><?= strlen($compromiso['descripcion']) > 100 ? '...' : '' ?></strong></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Nuevo estado:</small>
                            <p class="mb-0">
                                <?php
                                $estadoBadges = [
                                    'pendiente' => '<span class="badge bg-secondary">Pendiente</span>',
                                    'en_progreso' => '<span class="badge bg-info">En progreso</span>',
                                    'completado' => '<span class="badge bg-success">Completado</span>',
                                    'vencido' => '<span class="badge bg-danger">Vencido</span>'
                                ];
                                echo $estadoBadges[$compromiso['estado']] ?? '<span class="badge bg-secondary">-</span>';
                                ?>
                            </p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Fecha:</small>
                            <p class="mb-0"><strong><?= date('d/m/Y H:i') ?></strong></p>
                        </div>
                    </div>
                </div>

                <?php if ($compromiso['estado'] === 'completado'): ?>
                <div class="alert alert-success mt-4 mb-0">
                    <i class="bi bi-trophy me-2"></i>
                    <strong>Excelente trabajo!</strong><br>
                    La tarea ha sido marcada como completada.
                </div>
                <?php else: ?>
                <p class="text-muted mt-4 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    El responsable del comite sera notificado de esta actualizacion.
                </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mt-4">
            <small class="text-white-50">
                <i class="bi bi-shield-check me-1"></i>
                Actualizacion registrada de forma segura
            </small>
        </div>
    </div>
</body>
</html>
