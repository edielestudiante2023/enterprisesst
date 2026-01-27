<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Documento - Paso 2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .step-indicator { display: flex; justify-content: center; margin-bottom: 2rem; }
        .step { display: flex; align-items: center; }
        .step-number {
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-weight: bold;
        }
        .step-number.active { background-color: #3B82F6; color: white; }
        .step-number.completed { background-color: #10B981; color: white; }
        .step-number.inactive { background-color: #E5E7EB; color: #6B7280; }
        .step-line { width: 60px; height: 2px; background-color: #E5E7EB; margin: 0 0.5rem; }
        .step-line.completed { background-color: #10B981; }
        .estructura-preview { max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-file-earmark-plus me-2"></i>Configurar Documento
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3"><?= esc($cliente['nombre_cliente']) ?></span>
                <a class="nav-link" href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Indicador de pasos -->
        <div class="step-indicator">
            <div class="step">
                <div class="step-number completed"><i class="bi bi-check"></i></div>
                <span class="ms-2 text-muted">Tipo</span>
            </div>
            <div class="step-line completed"></div>
            <div class="step">
                <div class="step-number active">2</div>
                <span class="ms-2 fw-bold">Configurar</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-number inactive">3</div>
                <span class="ms-2 text-muted">Generar</span>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Datos del Documento</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?= base_url('documentacion/crear/' . $cliente['id_cliente']) ?>" method="post">
                            <input type="hidden" name="id_tipo" value="<?= $tipo['id_tipo'] ?? '' ?>">
                            <input type="hidden" name="id_plantilla" value="<?= $plantilla['id_plantilla'] ?? '' ?>">

                            <div class="mb-3">
                                <label class="form-label">Nombre del Documento *</label>
                                <input type="text" name="nombre" class="form-control" required
                                       value="<?= esc($plantilla['nombre'] ?? $tipo['nombre'] ?? '') ?>"
                                       placeholder="Ej: Programa de Capacitacion en Promocion y Prevencion">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-folder me-1"></i>Carpeta de Destino *
                                </label>
                                <select name="id_carpeta" class="form-select" required id="selectCarpeta" style="font-family: monospace;">
                                    <option value="">-- Seleccione carpeta PHVA --</option>
                                    <?php if (!empty($carpetas)): ?>
                                        <?php foreach ($carpetas as $carpeta):
                                            $nivel = $carpeta['nivel'] ?? 0;
                                            // Crear indentacion visual tipo arbol
                                            $indent = '';
                                            if ($nivel > 0) {
                                                $indent = str_repeat('   ', $nivel - 1) . '└─ ';
                                            }
                                            // Iconos segun nivel
                                            $icono = ($nivel == 0) ? '📁' : (($nivel == 1) ? '📂' : '📄');
                                            // Verificar si es la carpeta sugerida
                                            $esSeleccionada = isset($carpetaSugerida) && $carpeta['id_carpeta'] == $carpetaSugerida;
                                            // Mostrar codigo si existe
                                            $codigo = !empty($carpeta['codigo']) ? "[{$carpeta['codigo']}] " : '';
                                        ?>
                                            <option value="<?= $carpeta['id_carpeta'] ?>"
                                                    data-nivel="<?= $nivel ?>"
                                                    data-codigo="<?= esc($carpeta['codigo'] ?? '') ?>"
                                                    <?= $esSeleccionada ? 'selected' : '' ?>>
                                                <?= $indent ?><?= $icono ?> <?= $codigo ?><?= esc($carpeta['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if (isset($carpetaSugerida) && $carpetaSugerida): ?>
                                    <div class="form-text text-success">
                                        <i class="bi bi-check-circle me-1"></i>Carpeta sugerida para este tipo de documento
                                    </div>
                                <?php else: ?>
                                    <div class="form-text">
                                        Seleccione la subcarpeta PHVA donde se guardara el documento
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="2"
                                          placeholder="Breve descripción del documento..."><?= esc($plantilla['descripcion'] ?? '') ?></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Código de Tipo</label>
                                    <input type="text" name="codigo_tipo" class="form-control"
                                           value="<?= esc($tipo['codigo'] ?? 'DOC') ?>" maxlength="10">
                                    <div class="form-text">Prefijo del código (Ej: POL, PRO, PLA)</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Código de Tema</label>
                                    <input type="text" name="codigo_tema" class="form-control"
                                           placeholder="SST" maxlength="10">
                                    <div class="form-text">Identificador del tema (Ej: SST, EPP)</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Código Generado</label>
                                    <input type="text" class="form-control" disabled
                                           value="Se generará automáticamente">
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="<?= base_url('documentacion/nuevo/' . $cliente['id_cliente']) ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>Volver
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    Crear y Continuar <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Info del tipo/plantilla -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Tipo Seleccionado</h6>
                    </div>
                    <div class="card-body">
                        <h5><?= esc($plantilla['nombre'] ?? $tipo['nombre'] ?? 'N/D') ?></h5>
                        <?php if (!empty($plantilla)): ?>
                            <span class="badge bg-primary">Plantilla</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Tipo Base</span>
                        <?php endif; ?>
                        <p class="text-muted mt-2 mb-0">
                            <?= esc($plantilla['descripcion'] ?? $tipo['descripcion'] ?? '') ?>
                        </p>
                    </div>
                </div>

                <!-- Estructura del documento -->
                <?php if (!empty($estructura)): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-list-ol me-2"></i>Estructura (<?= count($estructura) ?> secciones)</h6>
                        </div>
                        <div class="card-body estructura-preview">
                            <ol class="mb-0 small">
                                <?php foreach ($estructura as $idx => $seccion): ?>
                                    <li class="mb-1">
                                        <?php if (is_array($seccion)): ?>
                                            <?= esc($seccion['nombre'] ?? $seccion['nombre_seccion'] ?? 'Seccion ' . ($idx + 1)) ?>
                                        <?php else: ?>
                                            <?= esc($seccion) ?>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Contexto del cliente -->
                <?php if (!empty($contexto)): ?>
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-building me-2"></i>Contexto SST</h6>
                        </div>
                        <div class="card-body">
                            <small>
                                <p class="mb-1"><strong>Trabajadores:</strong> <?= $contexto['total_trabajadores'] ?? 'N/D' ?></p>
                                <p class="mb-1"><strong>Riesgo:</strong> <?= $contexto['niveles_riesgo_arl'] ?? 'N/D' ?></p>
                                <p class="mb-0"><strong>Actividad:</strong> <?= esc($contexto['sector_economico'] ?? 'N/D') ?></p>
                            </small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
