<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo 60 Estándares - Resolución 0312/2019</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .phva-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        .phva-planear { background-color: #3B82F6 !important; }
        .phva-hacer { background-color: #10B981 !important; }
        .phva-verificar { background-color: #F59E0B !important; }
        .phva-actuar { background-color: #EF4444 !important; }
        .nivel-badge {
            font-size: 0.65rem;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .nivel-7 { background-color: #E0F2FE; color: #0369A1; }
        .nivel-21 { background-color: #FEF3C7; color: #B45309; }
        .nivel-60 { background-color: #FEE2E2; color: #DC2626; }
        .table-estandares th { font-size: 0.85rem; }
        .table-estandares td { font-size: 0.85rem; vertical-align: middle; }
        .categoria-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/documentacion">
                <i class="bi bi-book me-2"></i>Catálogo Estándares
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/documentacion">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1"><i class="bi bi-list-check text-primary me-2"></i>60 Estándares Mínimos SG-SST</h4>
                        <p class="text-muted mb-0">Resolución 0312 de 2019 - Ministerio de Trabajo de Colombia</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <span class="badge nivel-7">7</span>
                            <small class="text-muted">≤10 trab.</small>
                            <span class="badge nivel-21">21</span>
                            <small class="text-muted">11-50 trab.</small>
                            <span class="badge nivel-60">60</span>
                            <small class="text-muted">>50 / Riesgo IV-V</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leyenda PHVA -->
        <div class="d-flex gap-3 mb-4">
            <span class="badge phva-planear"><i class="bi bi-clipboard-check me-1"></i>PLANEAR</span>
            <span class="badge phva-hacer"><i class="bi bi-gear me-1"></i>HACER</span>
            <span class="badge phva-verificar"><i class="bi bi-search me-1"></i>VERIFICAR</span>
            <span class="badge phva-actuar"><i class="bi bi-arrow-repeat me-1"></i>ACTUAR</span>
        </div>

        <!-- Tabla de estándares -->
        <?php if (empty($estandaresAgrupados)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                No hay estándares cargados. Ejecute el script de inicialización de la base de datos.
            </div>
        <?php else: ?>
            <?php foreach ($estandaresAgrupados as $ciclo => $categorias): ?>
                <?php
                    $phvaClass = match(strtolower($ciclo)) {
                        'planear' => 'phva-planear',
                        'hacer' => 'phva-hacer',
                        'verificar' => 'phva-verificar',
                        'actuar' => 'phva-actuar',
                        default => 'bg-secondary'
                    };
                    $phvaIcon = match(strtolower($ciclo)) {
                        'planear' => 'bi-clipboard-check',
                        'hacer' => 'bi-gear',
                        'verificar' => 'bi-search',
                        'actuar' => 'bi-arrow-repeat',
                        default => 'bi-folder'
                    };
                ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header <?= $phvaClass ?> text-white">
                        <h5 class="mb-0">
                            <i class="bi <?= $phvaIcon ?> me-2"></i>
                            <?= strtoupper($ciclo) ?>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach ($categorias as $categoria => $estandares): ?>
                            <div class="categoria-header px-3 py-2">
                                <strong><?= esc($categoria) ?></strong>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-estandares mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;">Núm.</th>
                                            <th style="width: 100px;">Código</th>
                                            <th>Estándar</th>
                                            <th style="width: 60px;" class="text-center">Peso</th>
                                            <th style="width: 80px;" class="text-center">Nivel</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($estandares as $est): ?>
                                            <tr>
                                                <td><span class="badge bg-secondary"><?= $est['numero_estandar'] ?></span></td>
                                                <td><code><?= esc($est['codigo']) ?></code></td>
                                                <td><?= esc($est['nombre']) ?></td>
                                                <td class="text-center">
                                                    <span class="badge bg-light text-dark"><?= $est['peso'] ?>%</span>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                        $nivelMin = $est['nivel_minimo'] ?? 60;
                                                        $nivelClass = match($nivelMin) {
                                                            7 => 'nivel-7',
                                                            21 => 'nivel-21',
                                                            default => 'nivel-60'
                                                        };
                                                    ?>
                                                    <span class="badge nivel-badge <?= $nivelClass ?>"><?= $nivelMin ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
