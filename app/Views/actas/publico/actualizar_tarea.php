<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Tarea - <?= esc($compromiso['descripcion']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .task-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .task-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 30px;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 8px 16px;
        }
        .btn-status {
            padding: 15px 25px;
            font-size: 1.1rem;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="task-container">
        <div class="card">
            <div class="task-header text-center">
                <i class="bi bi-list-check display-4 mb-3"></i>
                <h4 class="mb-2">Actualizacion de Tarea</h4>
                <p class="mb-0 opacity-75">Acta <?= esc($acta['numero_acta']) ?></p>
            </div>

            <div class="card-body p-4">
                <!-- Info del compromiso -->
                <div class="mb-4">
                    <h5 class="mb-3">Descripcion de la Tarea</h5>
                    <p class="lead"><?= esc($compromiso['descripcion']) ?></p>

                    <div class="row mt-4">
                        <div class="col-6">
                            <small class="text-muted d-block">Responsable</small>
                            <strong><?= esc($compromiso['responsable_nombre'] ?? 'Sin asignar') ?></strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Fecha Limite</small>
                            <strong><?= date('d/m/Y', strtotime($compromiso['fecha_limite'])) ?></strong>
                            <?php
                            $fechaLimite = strtotime($compromiso['fecha_limite']);
                            $hoy = strtotime('today');
                            $diasRestantes = floor(($fechaLimite - $hoy) / 86400);
                            ?>
                            <?php if ($diasRestantes < 0): ?>
                                <br><span class="text-danger small"><i class="bi bi-exclamation-circle"></i> Vencido</span>
                            <?php elseif ($diasRestantes <= 7): ?>
                                <br><span class="text-warning small"><i class="bi bi-clock"></i> <?= $diasRestantes ?> dias restantes</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Estado actual -->
                <div class="text-center mb-4">
                    <p class="text-muted mb-2">Estado actual:</p>
                    <?php
                    $estadoBadges = [
                        'pendiente' => 'bg-secondary',
                        'en_progreso' => 'bg-info',
                        'completado' => 'bg-success',
                        'vencido' => 'bg-danger'
                    ];
                    ?>
                    <span class="badge <?= $estadoBadges[$compromiso['estado']] ?? 'bg-secondary' ?> status-badge">
                        <?= ucfirst(str_replace('_', ' ', $compromiso['estado'])) ?>
                    </span>
                </div>

                <?php if ($compromiso['estado'] !== 'completado'): ?>
                <!-- Formulario de actualización -->
                <form action="<?= base_url('actas/publico/tarea/' . $token . '/guardar') ?>" method="post">
                    <div class="mb-4">
                        <label class="form-label">Actualizar Estado</label>
                        <div class="d-grid gap-2">
                            <button type="submit" name="estado" value="en_progreso" class="btn btn-info btn-status">
                                <i class="bi bi-arrow-repeat me-2"></i> En Progreso
                            </button>
                            <button type="submit" name="estado" value="completado" class="btn btn-success btn-status">
                                <i class="bi bi-check-circle me-2"></i> Completado
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" name="observaciones" rows="3"
                                  placeholder="Agregue notas sobre el avance o la completitud de la tarea..."><?= esc($compromiso['observaciones'] ?? '') ?></textarea>
                    </div>
                </form>
                <?php else: ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Tarea completada</strong>
                    <?php if (!empty($compromiso['fecha_completado'])): ?>
                        <br><small>Completada el <?= date('d/m/Y H:i', strtotime($compromiso['fecha_completado'])) ?></small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($compromiso['observaciones'])): ?>
                <div class="mt-4">
                    <h6><i class="bi bi-chat-dots me-2"></i>Observaciones</h6>
                    <p class="text-muted mb-0"><?= nl2br(esc($compromiso['observaciones'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mt-4">
            <small class="text-white-50">
                <i class="bi bi-shield-check me-1"></i>
                Acceso seguro mediante token unico
            </small>
        </div>
    </div>
</body>
</html>
