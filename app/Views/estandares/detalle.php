<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estándar <?= esc($estandar['item'] ?? $estandar['numero_estandar'] ?? '') ?> - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .criterio-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #0d6efd;
            padding: 1.5rem;
            border-radius: 0.5rem;
        }
        .estado-cumple { background-color: #198754 !important; }
        .estado-no_cumple { background-color: #dc3545 !important; }
        .estado-en_proceso { background-color: #ffc107 !important; color: #000 !important; }
        .estado-pendiente { background-color: #6c757d !important; }
        .estado-no_aplica { background-color: #0dcaf0 !important; color: #000 !important; }
        .calificacion-display {
            font-size: 2.5rem;
            font-weight: 700;
        }
        .peso-badge {
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('estandares/' . $cliente['id_cliente']) ?>">
                <i class="bi bi-arrow-left me-2"></i>Volver a Estándares
            </a>
            <span class="navbar-text text-white">
                <?= esc($cliente['nombre_cliente']) ?>
            </span>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row">
            <div class="col-lg-8">
                <!-- Encabezado del estándar -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-primary fs-5 me-2"><?= esc($estandar['item'] ?? $estandar['numero_estandar'] ?? '') ?></span>
                                <span class="badge bg-secondary"><?= ucfirst($estandar['ciclo_phva'] ?? 'N/D') ?></span>
                            </div>
                            <span class="badge bg-info peso-badge">
                                <i class="bi bi-percent me-1"></i>Peso: <?= $estandar['peso_porcentual'] ?? $estandar['peso'] ?? 0 ?>%
                            </span>
                        </div>
                        <h4 class="mt-3 mb-0"><?= esc($estandar['nombre']) ?></h4>
                        <small class="text-muted"><?= esc($estandar['categoria_nombre'] ?? $estandar['categoria'] ?? '') ?></small>
                    </div>
                </div>

                <!-- Criterio de Verificación (Pregunta de Auditoría) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-question-circle text-primary me-2"></i>
                            Criterio de Verificación
                        </h5>
                        <small class="text-muted">Pregunta de auditoría según Res. 0312/2019</small>
                    </div>
                    <div class="card-body">
                        <div class="criterio-box">
                            <?php if (!empty($estandar['criterio'])): ?>
                                <p class="mb-0 fs-5"><?= esc($estandar['criterio']) ?></p>
                            <?php else: ?>
                                <p class="mb-0 text-muted fst-italic">
                                    El criterio de verificación no ha sido configurado para este estándar.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Estado de Cumplimiento y Calificación -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-check text-success me-2"></i>
                            Evaluación del Estándar
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="formEvaluacion" method="post">
                            <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                            <input type="hidden" name="id_estandar" value="<?= $estandar['id_estandar'] ?>">

                            <div class="row g-4">
                                <!-- Estado -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Estado de Cumplimiento</label>
                                    <select name="estado" id="estadoSelect" class="form-select form-select-lg">
                                        <option value="pendiente" <?= ($clienteEstandar['estado'] ?? '') === 'pendiente' ? 'selected' : '' ?>>
                                            Pendiente
                                        </option>
                                        <option value="cumple" <?= ($clienteEstandar['estado'] ?? '') === 'cumple' ? 'selected' : '' ?>>
                                            Cumple Totalmente
                                        </option>
                                        <option value="no_cumple" <?= ($clienteEstandar['estado'] ?? '') === 'no_cumple' ? 'selected' : '' ?>>
                                            No Cumple
                                        </option>
                                        <option value="en_proceso" <?= ($clienteEstandar['estado'] ?? '') === 'en_proceso' ? 'selected' : '' ?>>
                                            En Proceso
                                        </option>
                                        <option value="no_aplica" <?= ($clienteEstandar['estado'] ?? '') === 'no_aplica' ? 'selected' : '' ?>>
                                            No Aplica
                                        </option>
                                    </select>
                                </div>

                                <!-- Calificación (Automática según Res. 0312/2019) -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Calificación Obtenida</label>
                                    <?php
                                    $estadoActual = $clienteEstandar['estado'] ?? 'pendiente';
                                    $pesoEstandar = (float)($estandar['peso_porcentual'] ?? $estandar['peso'] ?? 0);
                                    // Calificación automática según estado (Res. 0312/2019)
                                    $calificacionAuto = ($estadoActual === 'cumple' || $estadoActual === 'no_aplica')
                                                        ? $pesoEstandar
                                                        : 0;
                                    ?>
                                    <div class="input-group input-group-lg">
                                        <input type="text"
                                               id="calificacionDisplay"
                                               class="form-control text-center fw-bold"
                                               value="<?= number_format($calificacionAuto, 2) ?>"
                                               readonly
                                               style="background-color: #e9ecef;">
                                        <span class="input-group-text">/ <?= $pesoEstandar ?></span>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Automática según Res. 0312/2019
                                    </small>
                                    <!-- Campo oculto para enviar el valor calculado -->
                                    <input type="hidden" name="calificacion" id="calificacionInput" value="<?= $calificacionAuto ?>">
                                </div>

                                <!-- Fecha cumplimiento -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Fecha de Evaluación</label>
                                    <input type="date"
                                           name="fecha_cumplimiento"
                                           class="form-control form-control-lg"
                                           value="<?= $clienteEstandar['fecha_cumplimiento'] ?? date('Y-m-d') ?>">
                                </div>

                                <!-- Observaciones -->
                                <div class="col-12">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-chat-left-text me-1"></i>
                                        Observaciones / Justificación
                                    </label>
                                    <textarea name="observaciones"
                                              id="observacionesText"
                                              class="form-control"
                                              rows="4"
                                              placeholder="Describa las evidencias encontradas, hallazgos, o justificación si no aplica..."><?= esc($clienteEstandar['observaciones'] ?? '') ?></textarea>
                                    <small class="text-muted">
                                        Incluya: evidencias revisadas, documentos verificados, hallazgos, oportunidades de mejora.
                                    </small>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                                    <i class="bi bi-arrow-left me-1"></i>Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-save me-1"></i>Guardar Evaluación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Documentos sugeridos -->
                <?php if (!empty($estandar['documentos_sugeridos'])): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-text text-warning me-2"></i>
                            Documentos Sugeridos para Evidencia
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php
                            $docsSugeridos = is_array($estandar['documentos_sugeridos'])
                                ? $estandar['documentos_sugeridos']
                                : explode(',', $estandar['documentos_sugeridos']);
                            foreach ($docsSugeridos as $doc):
                                $doc = trim($doc);
                                if (!empty($doc)):
                            ?>
                            <li class="list-group-item d-flex align-items-center">
                                <i class="bi bi-file-earmark me-2 text-muted"></i>
                                <?= esc($doc) ?>
                            </li>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Documentos cargados -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-folder2-open text-success me-2"></i>
                            Documentos Vinculados
                        </h5>
                        <a href="<?= base_url('documentacion/nuevo/' . $cliente['id_cliente']) ?>?estandar=<?= $estandar['id_estandar'] ?>"
                           class="btn btn-sm btn-success">
                            <i class="bi bi-plus-lg me-1"></i>Vincular Documento
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($documentos)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-folder2 display-4 text-muted"></i>
                                <p class="text-muted mt-2 mb-0">No hay documentos vinculados a este estándar.</p>
                                <small class="text-muted">Los documentos sirven como evidencia de cumplimiento.</small>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($documentos as $doc): ?>
                                    <a href="<?= base_url('documentacion/ver/' . $doc['id_documento']) ?>"
                                       class="list-group-item list-group-item-action">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                                <strong><?= esc($doc['codigo'] ?? '') ?></strong>
                                                <?= esc($doc['nombre']) ?>
                                            </div>
                                            <span class="badge bg-<?= ($doc['estado'] ?? '') === 'aprobado' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst($doc['estado'] ?? 'borrador') ?>
                                            </span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Resumen de calificación -->
                <div class="card border-0 shadow-sm mb-4 text-center">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">Calificación Actual</h6>
                        <?php
                        $estado = $clienteEstandar['estado'] ?? 'pendiente';
                        $pesoMax = (float)($estandar['peso_porcentual'] ?? $estandar['peso'] ?? 0);
                        // Calificación automática según Res. 0312/2019
                        $calificacion = ($estado === 'cumple' || $estado === 'no_aplica') ? $pesoMax : 0;
                        $porcentaje = $pesoMax > 0 ? round(($calificacion / $pesoMax) * 100) : 0;
                        ?>
                        <div class="calificacion-display text-<?= $estado === 'cumple' ? 'success' : ($estado === 'no_aplica' ? 'info' : ($estado === 'no_cumple' ? 'danger' : 'secondary')) ?>">
                            <?= number_format($calificacion, 2) ?>
                        </div>
                        <p class="mb-2">de <?= $pesoMax ?>% posible</p>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-<?= $estado === 'cumple' ? 'success' : ($estado === 'no_aplica' ? 'info' : ($estado === 'no_cumple' ? 'danger' : 'secondary')) ?>"
                                 style="width: <?= $porcentaje ?>%"></div>
                        </div>
                        <span class="badge estado-<?= $estado ?> mt-3 py-2 px-3">
                            <?= ucfirst(str_replace('_', ' ', $estado)) ?>
                        </span>
                        <p class="text-muted small mt-2 mb-0">
                            <i class="bi bi-info-circle"></i> Según Res. 0312/2019
                        </p>
                    </div>
                </div>

                <!-- Info del estándar -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información del Estándar</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted">Ciclo PHVA:</td>
                                <td class="text-end"><strong><?= ucfirst($estandar['ciclo_phva'] ?? 'N/D') ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Categoría:</td>
                                <td class="text-end"><strong><?= esc($estandar['categoria'] ?? 'N/D') ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Peso %:</td>
                                <td class="text-end"><strong><?= $estandar['peso_porcentual'] ?? $estandar['peso'] ?? 0 ?>%</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Aplica desde:</td>
                                <td class="text-end">
                                    <strong>
                                        <?php
                                        if (($estandar['aplica_7'] ?? 0) == 1) echo '7 estándares';
                                        elseif (($estandar['aplica_21'] ?? 0) == 1) echo '21 estándares';
                                        else echo '60 estándares';
                                        ?>
                                    </strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Info del cliente -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-building me-2"></i>Cliente</h6>
                    </div>
                    <div class="card-body">
                        <h5 class="mb-1"><?= esc($cliente['nombre_cliente']) ?></h5>
                        <p class="text-muted mb-0"><?= esc($cliente['nit_cliente'] ?? $cliente['nit'] ?? '') ?></p>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Acciones</h6>
                    </div>
                    <div class="card-body">
                        <a href="<?= base_url('estandares/' . $cliente['id_cliente']) ?>"
                           class="btn btn-outline-primary w-100 mb-2">
                            <i class="bi bi-list-check me-1"></i>Ver Todos los Estándares
                        </a>
                        <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>"
                           class="btn btn-outline-secondary w-100">
                            <i class="bi bi-folder me-1"></i>Ir a Documentación
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Calificación automática según Res. 0312/2019
        const pesoMax = <?= $estandar['peso_porcentual'] ?? $estandar['peso'] ?? 0 ?>;

        function calcularCalificacion(estado) {
            // Según Res. 0312/2019:
            // Cumple = 100% del peso del estándar
            // No Aplica = 100% del peso (cuando está debidamente justificado)
            // No Cumple / Pendiente / En Proceso = 0%
            return (estado === 'cumple' || estado === 'no_aplica') ? pesoMax : 0;
        }

        function actualizarCalificacionDisplay() {
            const estado = document.getElementById('estadoSelect').value;
            const calificacion = calcularCalificacion(estado);

            document.getElementById('calificacionDisplay').value = calificacion.toFixed(2);
            document.getElementById('calificacionInput').value = calificacion;
        }

        // Auto-ajustar calificación según estado
        document.getElementById('estadoSelect').addEventListener('change', actualizarCalificacionDisplay);

        // Envío del formulario via AJAX
        document.getElementById('formEvaluacion').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('<?= base_url('estandares/actualizar-estado') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Evaluación guardada correctamente');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo guardar'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Fallback: enviar formulario normal
                this.action = '<?= base_url('estandares/actualizar-estado') ?>';
                this.submit();
            });
        });
    </script>
</body>
</html>
