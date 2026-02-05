<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Hallazgo - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f8f9fa; }
        .form-label { font-weight: 500; }
        .origen-card { cursor: pointer; transition: all 0.2s; border: 2px solid transparent; }
        .origen-card:hover { border-color: #0d6efd; }
        .origen-card.selected { border-color: #0d6efd; background-color: #e7f1ff; }
        .origen-card .icono { font-size: 1.5rem; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('dashboard') ?>">
                <i class="bi bi-shield-check me-2"></i>EnterpriseSST
            </a>
            <div class="d-flex align-items-center">
                <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}") ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Cancelar
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="mb-4">
                    <h2>
                        <i class="bi bi-plus-circle text-success me-2"></i>
                        Registrar Nuevo Hallazgo
                    </h2>
                    <p class="text-muted">
                        Cliente: <strong><?= esc($cliente['nombre_cliente']) ?></strong>
                        <?php if (!empty($numeral_preseleccionado)): ?>
                        | Numeral: <span class="badge bg-primary"><?= esc($numeral_preseleccionado) ?></span>
                        <?php endif; ?>
                    </p>
                </div>

                <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form action="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgo/guardar") ?>"
                      method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <!-- Paso 1: Tipo de Origen -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">
                                <span class="badge bg-primary rounded-circle me-2">1</span>
                                Tipo de Origen del Hallazgo
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3" id="origenes-container">
                                <?php foreach ($catalogo_origenes as $origen): ?>
                                <div class="col-md-4">
                                    <div class="card origen-card h-100 p-3 text-center"
                                         data-origen="<?= esc($origen['tipo_origen']) ?>"
                                         data-numeral="<?= esc($origen['numeral_default']) ?>">
                                        <i class="bi <?= esc($origen['icono']) ?> icono mb-2"
                                           style="color: <?= esc($origen['color']) ?>"></i>
                                        <small class="fw-bold d-block"><?= esc($origen['nombre_mostrar']) ?></small>
                                        <small class="text-muted"><?= esc($origen['numeral_default']) ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="tipo_origen" id="tipo_origen" value="<?= old('tipo_origen') ?>" required>
                            <input type="hidden" name="numeral_asociado" id="numeral_asociado"
                                   value="<?= old('numeral_asociado', $numeral_preseleccionado ?? '') ?>">
                        </div>
                    </div>

                    <!-- Paso 2: Información del Hallazgo -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">
                                <span class="badge bg-primary rounded-circle me-2">2</span>
                                Información del Hallazgo
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="titulo" class="form-label">Título del Hallazgo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="titulo" name="titulo"
                                       value="<?= old('titulo') ?>" required maxlength="255"
                                       placeholder="Ej: Trabajador sin EPP en área de producción">
                                <div class="form-text">Descripción corta y clara del hallazgo</div>
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción Detallada <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required
                                          placeholder="Describa en detalle el hallazgo: qué se observó, dónde, cuándo, quién estaba involucrado..."><?= old('descripcion') ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="area_proceso" class="form-label">Área / Proceso</label>
                                    <input type="text" class="form-control" id="area_proceso" name="area_proceso"
                                           value="<?= old('area_proceso') ?>"
                                           placeholder="Ej: Producción, Almacén, Oficinas">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="severidad" class="form-label">Severidad <span class="text-danger">*</span></label>
                                    <select class="form-select" id="severidad" name="severidad" required>
                                        <option value="">Seleccione...</option>
                                        <option value="critica" <?= old('severidad') === 'critica' ? 'selected' : '' ?>>
                                            Crítica - Riesgo inminente de AT/EL grave
                                        </option>
                                        <option value="alta" <?= old('severidad') === 'alta' ? 'selected' : '' ?>>
                                            Alta - Posible AT/EL, requiere acción urgente
                                        </option>
                                        <option value="media" <?= old('severidad') === 'media' ? 'selected' : '' ?>>
                                            Media - Incumplimiento que debe corregirse
                                        </option>
                                        <option value="baja" <?= old('severidad') === 'baja' ? 'selected' : '' ?>>
                                            Baja - Oportunidad de mejora
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_deteccion" class="form-label">Fecha de Detección <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_deteccion" name="fecha_deteccion"
                                           value="<?= old('fecha_deteccion', date('Y-m-d')) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_limite_accion" class="form-label">Fecha Límite para Acción</label>
                                    <input type="date" class="form-control" id="fecha_limite_accion" name="fecha_limite_accion"
                                           value="<?= old('fecha_limite_accion') ?>">
                                    <div class="form-text">Opcional: fecha máxima para implementar acciones</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 3: Evidencia -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">
                                <span class="badge bg-primary rounded-circle me-2">3</span>
                                Evidencia Inicial (Opcional)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="evidencia_inicial" class="form-label">Archivo de Evidencia</label>
                                <input type="file" class="form-control" id="evidencia_inicial" name="evidencia_inicial"
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls">
                                <div class="form-text">
                                    Formatos permitidos: PDF, Word, Excel, imágenes. Máximo 10MB.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}") ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle me-1"></i>Registrar Hallazgo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Selección de tipo de origen
        $('.origen-card').on('click', function() {
            $('.origen-card').removeClass('selected');
            $(this).addClass('selected');

            const tipoOrigen = $(this).data('origen');
            const numeral = $(this).data('numeral');

            $('#tipo_origen').val(tipoOrigen);
            $('#numeral_asociado').val(numeral);
        });

        // Pre-seleccionar si hay numeral preseleccionado
        <?php if (!empty($numeral_preseleccionado)): ?>
        const numeralPre = '<?= $numeral_preseleccionado ?>';
        const primerOrigen = $('.origen-card[data-numeral="' + numeralPre + '"]').first();
        if (primerOrigen.length) {
            primerOrigen.click();
        }
        <?php endif; ?>

        // Validación antes de enviar
        $('form').on('submit', function(e) {
            if (!$('#tipo_origen').val()) {
                e.preventDefault();
                alert('Por favor seleccione un tipo de origen');
                $('html, body').animate({ scrollTop: 0 }, 300);
            }
        });
    });
    </script>
</body>
</html>
