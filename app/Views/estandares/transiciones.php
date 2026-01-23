<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transiciones de Nivel - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .nivel-badge {
            font-size: 1.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 1rem;
        }
        .nivel-7 { background: linear-gradient(135deg, #10B981, #059669); color: white; }
        .nivel-21 { background: linear-gradient(135deg, #F59E0B, #D97706); color: white; }
        .nivel-60 { background: linear-gradient(135deg, #3B82F6, #2563EB); color: white; }
        .timeline-item {
            position: relative;
            padding-left: 40px;
            padding-bottom: 30px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 30px;
            bottom: 0;
            width: 2px;
            background: #E5E7EB;
        }
        .timeline-item:last-child::before {
            display: none;
        }
        .timeline-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .transition-card {
            transition: all 0.3s ease;
        }
        .transition-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .arrow-transition {
            font-size: 2rem;
            color: #9CA3AF;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-arrow-left-right me-2"></i>Transiciones de Nivel
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a class="nav-link" href="/estandares/<?= $cliente['id_cliente'] ?>">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Informacion sobre niveles -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Niveles de Estandares segun Resolucion 0312/2019
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <div class="nivel-badge nivel-7 mx-auto mb-2">7</div>
                        <h6>Nivel Basico</h6>
                        <p class="text-muted small mb-0">
                            Empresas con 10 o menos trabajadores<br>
                            Riesgo I, II o III
                        </p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="nivel-badge nivel-21 mx-auto mb-2">21</div>
                        <h6>Nivel Intermedio</h6>
                        <p class="text-muted small mb-0">
                            Empresas de 11 a 50 trabajadores<br>
                            Riesgo I, II o III
                        </p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="nivel-badge nivel-60 mx-auto mb-2">60</div>
                        <h6>Nivel Completo</h6>
                        <p class="text-muted small mb-0">
                            Mas de 50 trabajadores<br>
                            O cualquier Riesgo IV o V
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transiciones pendientes -->
        <?php
        $pendientes = array_filter($transiciones, fn($t) => $t['estado'] === 'detectado');
        if (!empty($pendientes)):
        ?>
        <div class="card border-0 shadow-sm mb-4 border-warning">
            <div class="card-header bg-warning bg-opacity-10">
                <h5 class="mb-0 text-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Transiciones Pendientes de Aplicar
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($pendientes as $transicion): ?>
                    <div class="transition-card card border mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center">
                                    <small class="text-muted d-block mb-1">Nivel Anterior</small>
                                    <span class="nivel-badge nivel-<?= $transicion['nivel_anterior'] ?>">
                                        <?= $transicion['nivel_anterior'] ?>
                                    </span>
                                </div>
                                <div class="col-md-2 text-center">
                                    <i class="bi bi-arrow-right arrow-transition"></i>
                                </div>
                                <div class="col-md-3 text-center">
                                    <small class="text-muted d-block mb-1">Nuevo Nivel</small>
                                    <span class="nivel-badge nivel-<?= $transicion['nivel_nuevo'] ?>">
                                        <?= $transicion['nivel_nuevo'] ?>
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-2"><strong>Motivo:</strong> <?= esc($transicion['motivo']) ?></p>
                                    <p class="mb-2 text-muted small">
                                        <i class="bi bi-calendar me-1"></i>
                                        Detectado: <?= date('d/m/Y H:i', strtotime($transicion['fecha_deteccion'])) ?>
                                    </p>
                                    <form action="/estandares/aplicar-transicion/<?= $transicion['id_transicion'] ?>" method="post" class="d-inline">
                                        <button type="submit" class="btn btn-success btn-sm"
                                                onclick="return confirm('¿Aplicar esta transicion? Se actualizaran los estandares del cliente.')">
                                            <i class="bi bi-check-lg me-1"></i>Aplicar Transicion
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <?php if ($transicion['nivel_nuevo'] > $transicion['nivel_anterior']): ?>
                                <div class="alert alert-info mt-3 mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Impacto:</strong> Se agregaran
                                    <?= $transicion['nivel_nuevo'] - $transicion['nivel_anterior'] ?> nuevos estandares
                                    que deberan ser evaluados y cumplidos.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-success mt-3 mb-0">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>Impacto:</strong> Se reduciran los estandares requeridos.
                                    Algunos estandares actuales pasaran a "No Aplica".
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Historial de transiciones -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Historial de Transiciones
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($transiciones)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No hay transiciones registradas para este cliente</p>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($transiciones as $transicion): ?>
                            <?php
                            $iconClass = match($transicion['estado']) {
                                'aplicado' => 'bg-success',
                                'detectado' => 'bg-warning',
                                'rechazado' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            $iconName = match($transicion['estado']) {
                                'aplicado' => 'check-lg',
                                'detectado' => 'exclamation-lg',
                                'rechazado' => 'x-lg',
                                default => 'circle'
                            };
                            ?>
                            <div class="timeline-item">
                                <div class="timeline-icon <?= $iconClass ?>">
                                    <i class="bi bi-<?= $iconName ?>"></i>
                                </div>
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="badge nivel-<?= $transicion['nivel_anterior'] ?> me-2">
                                                    <?= $transicion['nivel_anterior'] ?>
                                                </span>
                                                <i class="bi bi-arrow-right mx-2"></i>
                                                <span class="badge nivel-<?= $transicion['nivel_nuevo'] ?>">
                                                    <?= $transicion['nivel_nuevo'] ?>
                                                </span>
                                            </div>
                                            <span class="badge bg-<?= $transicion['estado'] === 'aplicado' ? 'success' : ($transicion['estado'] === 'detectado' ? 'warning' : 'danger') ?>">
                                                <?= ucfirst($transicion['estado']) ?>
                                            </span>
                                        </div>
                                        <p class="mb-1"><strong>Motivo:</strong> <?= esc($transicion['motivo']) ?></p>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i>
                                            Detectado: <?= date('d/m/Y', strtotime($transicion['fecha_deteccion'])) ?>
                                            <?php if ($transicion['fecha_aplicacion']): ?>
                                                | Aplicado: <?= date('d/m/Y', strtotime($transicion['fecha_aplicacion'])) ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Simulador de cambio -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-calculator me-2"></i>
                    Simulador de Cambio de Nivel
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Simule como cambiaria el nivel de estandares si modificara el numero de trabajadores o el nivel de riesgo.
                </p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Numero de Trabajadores</label>
                        <input type="number" id="simTrabajadores" class="form-control" min="1" placeholder="Ej: 25">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nivel de Riesgo ARL</label>
                        <select id="simRiesgo" class="form-select">
                            <option value="">Seleccione...</option>
                            <option value="I">Riesgo I</option>
                            <option value="II">Riesgo II</option>
                            <option value="III">Riesgo III</option>
                            <option value="IV">Riesgo IV</option>
                            <option value="V">Riesgo V</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" id="btnSimular" class="btn btn-primary w-100">
                            <i class="bi bi-play me-1"></i>Simular
                        </button>
                    </div>
                </div>
                <div id="resultadoSimulacion" class="mt-4 d-none">
                    <div class="alert alert-info">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <strong>Resultado:</strong>
                            </div>
                            <div class="col text-center">
                                <span id="nivelResultado" class="nivel-badge">-</span>
                                <p class="mb-0 mt-2" id="descripcionResultado"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('btnSimular').addEventListener('click', function() {
            const trabajadores = parseInt(document.getElementById('simTrabajadores').value);
            const riesgo = document.getElementById('simRiesgo').value;

            if (!trabajadores || !riesgo) {
                alert('Complete todos los campos');
                return;
            }

            let nivel = 60;
            let descripcion = 'Nivel completo: Mas de 50 trabajadores o riesgo IV/V';

            if (trabajadores <= 10 && ['I', 'II', 'III'].includes(riesgo)) {
                nivel = 7;
                descripcion = 'Nivel basico: Hasta 10 trabajadores con riesgo I, II o III';
            } else if (trabajadores >= 11 && trabajadores <= 50 && ['I', 'II', 'III'].includes(riesgo)) {
                nivel = 21;
                descripcion = 'Nivel intermedio: 11-50 trabajadores con riesgo I, II o III';
            }

            const resultado = document.getElementById('resultadoSimulacion');
            const badge = document.getElementById('nivelResultado');
            const desc = document.getElementById('descripcionResultado');

            badge.textContent = nivel + ' estandares';
            badge.className = 'nivel-badge nivel-' + nivel;
            desc.textContent = descripcion;
            resultado.classList.remove('d-none');
        });
    </script>
</body>
</html>
