<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Documento - Seleccionar Tipo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .tipo-card {
            transition: all 0.2s;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .tipo-card:hover {
            transform: translateY(-5px);
            border-color: #3B82F6;
        }
        .tipo-card.selected {
            border-color: #3B82F6;
            background-color: #EFF6FF;
        }
        .tipo-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .plantilla-item {
            transition: background-color 0.2s;
            cursor: pointer;
        }
        .plantilla-item:hover {
            background-color: #F3F4F6;
        }
        .plantilla-item.selected {
            background-color: #DBEAFE;
            border-left: 3px solid #3B82F6;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            display: flex;
            align-items: center;
        }
        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .step-number.active {
            background-color: #3B82F6;
            color: white;
        }
        .step-number.inactive {
            background-color: #E5E7EB;
            color: #6B7280;
        }
        .step-line {
            width: 60px;
            height: 2px;
            background-color: #E5E7EB;
            margin: 0 0.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/documentacion/<?= $cliente['id_cliente'] ?>">
                <i class="bi bi-file-earmark-plus me-2"></i>Nuevo Documento
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a class="nav-link" href="/documentacion/<?= $cliente['id_cliente'] ?>">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Indicador de pasos -->
        <div class="step-indicator">
            <div class="step">
                <div class="step-number active">1</div>
                <span class="ms-2 fw-bold">Tipo</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-number inactive">2</div>
                <span class="ms-2 text-muted">Configurar</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-number inactive">3</div>
                <span class="ms-2 text-muted">Generar</span>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Seleccione el tipo de documento</h5>
            </div>
            <div class="card-body">
                <form action="/documentacion/configurar/<?= $cliente['id_cliente'] ?>" method="post" id="formTipo">
                    <input type="hidden" name="id_tipo" id="id_tipo">
                    <input type="hidden" name="id_plantilla" id="id_plantilla">

                    <!-- Tipos de documento -->
                    <h6 class="text-muted mb-3">
                        <i class="bi bi-folder me-2"></i>Tipos de Documento
                    </h6>
                    <div class="row g-3 mb-4">
                        <?php if (empty($tipos)): ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    No hay tipos de documento configurados.
                                    Configure los tipos en la administración del sistema.
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($tipos as $tipo): ?>
                                <?php
                                    $iconos = [
                                        'programa' => 'bi-calendar-check',
                                        'procedimiento' => 'bi-list-task',
                                        'plan' => 'bi-map',
                                        'politica' => 'bi-shield-check',
                                        'manual' => 'bi-book',
                                        'protocolo' => 'bi-clipboard-pulse',
                                        'formato' => 'bi-file-earmark-ruled',
                                        'instructivo' => 'bi-signpost-2',
                                        'matriz' => 'bi-grid-3x3',
                                        'registro' => 'bi-journal-text'
                                    ];
                                    $icono = $iconos[strtolower($tipo['codigo'] ?? '')] ?? 'bi-file-earmark';
                                    $colores = [
                                        'programa' => 'text-primary',
                                        'procedimiento' => 'text-success',
                                        'plan' => 'text-warning',
                                        'politica' => 'text-danger',
                                        'manual' => 'text-info',
                                        'protocolo' => 'text-purple',
                                        'formato' => 'text-secondary'
                                    ];
                                    $color = $colores[strtolower($tipo['codigo'] ?? '')] ?? 'text-primary';
                                ?>
                                <div class="col-md-3">
                                    <div class="card tipo-card h-100" onclick="selectTipo(<?= $tipo['id_tipo'] ?>)">
                                        <div class="card-body text-center">
                                            <i class="bi <?= $icono ?> tipo-icon <?= $color ?>"></i>
                                            <h6 class="mb-1"><?= esc($tipo['nombre']) ?></h6>
                                            <small class="text-muted"><?= esc($tipo['descripcion'] ?? '') ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Plantillas predefinidas -->
                    <?php if (!empty($plantillasAgrupadas)): ?>
                        <hr class="my-4">
                        <h6 class="text-muted mb-3">
                            <i class="bi bi-lightning me-2"></i>O use una plantilla predefinida
                        </h6>
                        <div class="accordion" id="accordionPlantillas">
                            <?php $i = 0; foreach ($plantillasAgrupadas as $tipoNombre => $plantillas): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>">
                                            <?= esc($tipoNombre) ?>
                                            <span class="badge bg-secondary ms-2"><?= count($plantillas) ?></span>
                                        </button>
                                    </h2>
                                    <div id="collapse<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                                         data-bs-parent="#accordionPlantillas">
                                        <div class="accordion-body p-0">
                                            <div class="list-group list-group-flush">
                                                <?php foreach ($plantillas as $plantilla): ?>
                                                    <div class="list-group-item plantilla-item"
                                                         onclick="selectPlantilla(<?= $plantilla['id_plantilla'] ?>)">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong><?= esc($plantilla['nombre']) ?></strong>
                                                                <?php if (!empty($plantilla['descripcion'])): ?>
                                                                    <br><small class="text-muted"><?= esc($plantilla['descripcion']) ?></small>
                                                                <?php endif; ?>
                                                            </div>
                                                            <i class="bi bi-chevron-right text-muted"></i>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php $i++; endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4 text-end">
                        <a href="/documentacion/<?= $cliente['id_cliente'] ?>" class="btn btn-outline-secondary me-2">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary" id="btnContinuar" disabled>
                            Continuar <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectTipo(idTipo) {
            // Limpiar selecciones
            document.querySelectorAll('.tipo-card').forEach(c => c.classList.remove('selected'));
            document.querySelectorAll('.plantilla-item').forEach(p => p.classList.remove('selected'));

            // Seleccionar tipo
            event.currentTarget.classList.add('selected');
            document.getElementById('id_tipo').value = idTipo;
            document.getElementById('id_plantilla').value = '';
            document.getElementById('btnContinuar').disabled = false;
        }

        function selectPlantilla(idPlantilla) {
            // Limpiar selecciones
            document.querySelectorAll('.tipo-card').forEach(c => c.classList.remove('selected'));
            document.querySelectorAll('.plantilla-item').forEach(p => p.classList.remove('selected'));

            // Seleccionar plantilla
            event.currentTarget.classList.add('selected');
            document.getElementById('id_plantilla').value = idPlantilla;
            document.getElementById('id_tipo').value = '';
            document.getElementById('btnContinuar').disabled = false;
        }
    </script>
</body>
</html>
