<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Accion - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f8f9fa; }
        .form-label { font-weight: 500; }
        .tipo-card { cursor: pointer; transition: all 0.2s; border: 2px solid transparent; }
        .tipo-card:hover { border-color: #0d6efd; }
        .tipo-card.selected { border-color: #0d6efd; background-color: #e7f1ff; }
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
                <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgo/{$hallazgo['id_hallazgo']}") ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Hallazgo
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
                        Nueva Accion
                    </h2>
                    <p class="text-muted mb-1">
                        Cliente: <strong><?= esc($cliente['nombre_cliente']) ?></strong>
                    </p>
                    <div class="alert alert-light border">
                        <strong>Hallazgo:</strong> <?= esc($hallazgo['titulo']) ?>
                        <br>
                        <small class="text-muted"><?= esc($hallazgo['descripcion']) ?></small>
                    </div>
                </div>

                <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form action="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgo/{$hallazgo['id_hallazgo']}/accion/guardar") ?>"
                      method="post">
                    <?= csrf_field() ?>

                    <!-- Tipo de Accion -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">
                                <span class="badge bg-primary rounded-circle me-2">1</span>
                                Tipo de Accion
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="card tipo-card h-100 p-3 text-center" data-tipo="correctiva">
                                        <i class="bi bi-tools text-danger fs-1 mb-2"></i>
                                        <strong class="d-block">Correctiva</strong>
                                        <small class="text-muted">Elimina la causa de una no conformidad detectada</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card tipo-card h-100 p-3 text-center" data-tipo="preventiva">
                                        <i class="bi bi-shield-exclamation text-warning fs-1 mb-2"></i>
                                        <strong class="d-block">Preventiva</strong>
                                        <small class="text-muted">Elimina la causa de una no conformidad potencial</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card tipo-card h-100 p-3 text-center" data-tipo="mejora">
                                        <i class="bi bi-graph-up-arrow text-success fs-1 mb-2"></i>
                                        <strong class="d-block">Mejora</strong>
                                        <small class="text-muted">Incrementa la eficacia del sistema de gestion</small>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="tipo_accion" id="tipo_accion" value="<?= old('tipo_accion', 'correctiva') ?>" required>
                        </div>
                    </div>

                    <!-- Descripcion de la Accion -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">
                                <span class="badge bg-primary rounded-circle me-2">2</span>
                                Descripcion de la Accion
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="descripcion_accion" class="form-label">Descripcion de la Accion <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="descripcion_accion" name="descripcion_accion" rows="4" required
                                          placeholder="Describa detalladamente la accion a implementar: que se va a hacer, como, donde..."><?= old('descripcion_accion') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="clasificacion_temporal" class="form-label">Clasificacion Temporal <span class="text-danger">*</span></label>
                                <select class="form-select" id="clasificacion_temporal" name="clasificacion_temporal" required>
                                    <option value="">Seleccione...</option>
                                    <option value="inmediata" <?= old('clasificacion_temporal') === 'inmediata' ? 'selected' : '' ?>>
                                        Inmediata (1-7 dias)
                                    </option>
                                    <option value="corto_plazo" <?= old('clasificacion_temporal') === 'corto_plazo' ? 'selected' : '' ?>>
                                        Corto Plazo (8-30 dias)
                                    </option>
                                    <option value="mediano_plazo" <?= old('clasificacion_temporal') === 'mediano_plazo' ? 'selected' : '' ?>>
                                        Mediano Plazo (31-90 dias)
                                    </option>
                                    <option value="largo_plazo" <?= old('clasificacion_temporal') === 'largo_plazo' ? 'selected' : '' ?>>
                                        Largo Plazo (mas de 90 dias)
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Asignacion -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">
                                <span class="badge bg-primary rounded-circle me-2">3</span>
                                Asignacion y Plazos
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="responsable_id" class="form-label">Responsable <span class="text-danger">*</span></label>
                                    <select class="form-select select2-usuarios" id="responsable_id" name="responsable_id" required>
                                        <option value="">Seleccione el responsable...</option>
                                        <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?= $usuario['id_usuario'] ?>"
                                                <?= old('responsable_id') == $usuario['id_usuario'] ? 'selected' : '' ?>>
                                            <?= esc($usuario['nombre_completo'] ?? $usuario['email']) ?>
                                            <?php if (!empty($usuario['cargo'])): ?>
                                                (<?= esc($usuario['cargo']) ?>)
                                            <?php endif; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_compromiso" class="form-label">Fecha de Compromiso <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_compromiso" name="fecha_compromiso"
                                           value="<?= old('fecha_compromiso', date('Y-m-d', strtotime('+30 days'))) ?>" required
                                           min="<?= date('Y-m-d') ?>">
                                    <div class="form-text">Fecha limite para completar la accion</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="recursos_requeridos" class="form-label">Recursos Requeridos</label>
                                <textarea class="form-control" id="recursos_requeridos" name="recursos_requeridos" rows="2"
                                          placeholder="Personal, materiales, equipos, capacitacion, etc."><?= old('recursos_requeridos') ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="costo_estimado" class="form-label">Costo Estimado (COP)</label>
                                    <input type="number" class="form-control" id="costo_estimado" name="costo_estimado"
                                           value="<?= old('costo_estimado') ?>" min="0" step="1000"
                                           placeholder="0">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notas" class="form-label">Notas Adicionales</label>
                                <textarea class="form-control" id="notas" name="notas" rows="2"
                                          placeholder="Observaciones, consideraciones especiales..."><?= old('notas') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Accion -->
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgo/{$hallazgo['id_hallazgo']}") ?>"
                           class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle me-1"></i>Crear Accion
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        // Select2 para usuarios
        $('.select2-usuarios').select2({
            theme: 'bootstrap-5',
            placeholder: 'Buscar usuario...',
            allowClear: true
        });

        // Seleccion de tipo de accion
        $('.tipo-card').on('click', function() {
            $('.tipo-card').removeClass('selected');
            $(this).addClass('selected');
            $('#tipo_accion').val($(this).data('tipo'));
        });

        // Pre-seleccionar correctiva por defecto
        const tipoActual = $('#tipo_accion').val() || 'correctiva';
        $(`.tipo-card[data-tipo="${tipoActual}"]`).addClass('selected');

        // Ajustar fecha segun clasificacion temporal
        $('#clasificacion_temporal').on('change', function() {
            const hoy = new Date();
            let diasAgregar = 30;

            switch($(this).val()) {
                case 'inmediata': diasAgregar = 7; break;
                case 'corto_plazo': diasAgregar = 30; break;
                case 'mediano_plazo': diasAgregar = 60; break;
                case 'largo_plazo': diasAgregar = 120; break;
            }

            hoy.setDate(hoy.getDate() + diasAgregar);
            const fechaSugerida = hoy.toISOString().split('T')[0];
            $('#fecha_compromiso').val(fechaSugerida);
        });
    });
    </script>
</body>
</html>
