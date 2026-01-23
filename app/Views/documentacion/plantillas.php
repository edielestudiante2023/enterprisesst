<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plantillas de Documentos SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .plantilla-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
            height: 100%;
        }
        .plantilla-card:hover {
            transform: translateY(-5px);
            border-color: #3B82F6;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.2);
        }
        .tipo-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 10px 10px 0 0;
        }
        .tipo-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .tipo-programa { background-color: #3B82F6; }
        .tipo-politica { background-color: #EF4444; }
        .tipo-procedimiento { background-color: #10B981; }
        .tipo-plan { background-color: #F59E0B; }
        .tipo-manual { background-color: #6366F1; }
        .tipo-protocolo { background-color: #EC4899; }
        .tipo-formato { background-color: #6B7280; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-file-earmark-text me-2"></i>Plantillas de Documentos SST
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= base_url('documentacion') ?>">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="text-center mb-5">
            <h2><i class="bi bi-magic text-primary me-2"></i>Plantillas de Documentos</h2>
            <p class="text-muted">Seleccione una plantilla para crear documentos con estructura predefinida</p>
        </div>

        <!-- Tipos de documento disponibles -->
        <div class="row mb-5">
            <div class="col-12">
                <h5 class="text-muted mb-3"><i class="bi bi-folder me-2"></i>Tipos de Documento</h5>
            </div>
            <?php if (!empty($tipos)): ?>
                <?php
                $iconos = [
                    'programa' => ['bi-calendar-check', 'tipo-programa'],
                    'politica' => ['bi-shield-check', 'tipo-politica'],
                    'procedimiento' => ['bi-list-task', 'tipo-procedimiento'],
                    'plan' => ['bi-map', 'tipo-plan'],
                    'manual' => ['bi-book', 'tipo-manual'],
                    'protocolo' => ['bi-clipboard-pulse', 'tipo-protocolo'],
                    'formato' => ['bi-file-earmark-ruled', 'tipo-formato']
                ];
                ?>
                <?php foreach ($tipos as $tipo): ?>
                    <?php
                    $codigo = strtolower($tipo['codigo'] ?? 'default');
                    $icono = $iconos[$codigo] ?? ['bi-file-earmark', 'bg-secondary'];
                    ?>
                    <div class="col-md-3 col-6 mb-3">
                        <a href="<?= base_url('documentacion/seleccionar-cliente') ?>?tipo=<?= $tipo['id_tipo'] ?>"
                           class="card plantilla-card border-0 shadow-sm text-decoration-none">
                            <div class="card-body text-center py-4">
                                <div class="tipo-icon <?= $icono[1] ?> text-white mx-auto mb-3">
                                    <i class="bi <?= $icono[0] ?>"></i>
                                </div>
                                <h6 class="text-dark mb-1"><?= esc($tipo['nombre']) ?></h6>
                                <small class="text-muted"><?= esc($tipo['descripcion'] ?? '') ?></small>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        No hay tipos de documento configurados. Configure los tipos en la administración.
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Plantillas predefinidas -->
        <?php if (!empty($plantillasAgrupadas)): ?>
            <h5 class="text-muted mb-3"><i class="bi bi-lightning me-2"></i>Plantillas Predefinidas</h5>

            <?php foreach ($plantillasAgrupadas as $tipoNombre => $plantillas): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="tipo-header">
                        <h5 class="mb-0"><i class="bi bi-folder-fill me-2"></i><?= esc($tipoNombre) ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php foreach ($plantillas as $plantilla): ?>
                                <div class="col-md-4">
                                    <a href="<?= base_url('documentacion/seleccionar-cliente') ?>?plantilla=<?= $plantilla['id_plantilla'] ?>"
                                       class="card plantilla-card border shadow-sm text-decoration-none h-100">
                                        <div class="card-body">
                                            <h6 class="text-dark"><?= esc($plantilla['nombre']) ?></h6>
                                            <?php if (!empty($plantilla['descripcion'])): ?>
                                                <p class="text-muted small mb-0"><?= esc($plantilla['descripcion']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer bg-transparent border-0">
                                            <small class="text-primary">
                                                <i class="bi bi-arrow-right me-1"></i>Usar plantilla
                                            </small>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                No hay plantillas predefinidas disponibles. Las plantillas facilitan la creación de documentos con estructura estandarizada.
            </div>
        <?php endif; ?>

        <!-- Info adicional -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h6><i class="bi bi-info-circle text-primary me-2"></i>Sobre las Plantillas</h6>
                <p class="text-muted mb-0">
                    Las plantillas incluyen la estructura de secciones predefinida según la Resolución 0312 de 2019
                    y el ciclo PHVA (Planear, Hacer, Verificar, Actuar). Al seleccionar una plantilla, el documento
                    se creará con todas las secciones necesarias listas para completar o generar con IA.
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
