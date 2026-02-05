<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accion #<?= $accion['id_accion'] ?> - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .timeline { position: relative; padding-left: 30px; }
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #6c757d;
        }
        .timeline-item.primary::before { background: #0d6efd; }
        .timeline-item.success::before { background: #198754; }
        .timeline-item.warning::before { background: #ffc107; }
        .timeline-item.danger::before { background: #dc3545; }
        .timeline-item.info::before { background: #0dcaf0; }
        .estado-badge { font-size: 0.9rem; }
        .bg-purple { background-color: #6f42c1 !important; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('dashboard') ?>">
                <i class="bi bi-shield-check me-2"></i>EnterpriseSST
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgo/{$accion['id_hallazgo']}") ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Hallazgo
                </a>
                <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}") ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-grid-3x3-gap me-1"></i>Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
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

        <div class="row">
            <!-- Columna Principal -->
            <div class="col-lg-8">
                <!-- Header de la Accion -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h4 class="mb-1">
                                    <?php
                                    $tipoClase = match($accion['tipo_accion']) {
                                        'correctiva' => 'danger',
                                        'preventiva' => 'warning',
                                        'mejora' => 'success',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $tipoClase ?> me-2">
                                        <?= ucfirst($accion['tipo_accion']) ?>
                                    </span>
                                    Accion #<?= $accion['id_accion'] ?>
                                </h4>
                                <small class="text-muted">
                                    Hallazgo: <?= esc($accion['hallazgo_titulo']) ?>
                                    <span class="badge bg-secondary"><?= $accion['numeral_asociado'] ?></span>
                                </small>
                            </div>
                            <?php
                            $estadoClase = match($accion['estado']) {
                                'borrador' => 'secondary',
                                'asignada' => 'info',
                                'en_ejecucion' => 'primary',
                                'en_revision' => 'warning',
                                'en_verificacion' => 'purple',
                                'cerrada_efectiva' => 'success',
                                'cerrada_no_efectiva' => 'danger',
                                'reabierta' => 'warning',
                                'cancelada' => 'dark',
                                default => 'secondary'
                            };
                            $estadoTexto = match($accion['estado']) {
                                'borrador' => 'Borrador',
                                'asignada' => 'Asignada',
                                'en_ejecucion' => 'En Ejecucion',
                                'en_revision' => 'En Revision',
                                'en_verificacion' => 'En Verificacion',
                                'cerrada_efectiva' => 'Cerrada Efectiva',
                                'cerrada_no_efectiva' => 'Cerrada No Efectiva',
                                'reabierta' => 'Reabierta',
                                'cancelada' => 'Cancelada',
                                default => ucfirst($accion['estado'])
                            };
                            ?>
                            <span class="badge bg-<?= $estadoClase ?> estado-badge py-2 px-3">
                                <?= $estadoTexto ?>
                            </span>
                        </div>

                        <p class="mb-3"><?= nl2br(esc($accion['descripcion_accion'])) ?></p>

                        <?php if (!empty($accion['esta_vencida'])): ?>
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Accion vencida!</strong> El plazo vencio hace <?= abs($accion['dias_restantes']) ?> dia(s).
                        </div>
                        <?php elseif (isset($accion['dias_restantes']) && $accion['dias_restantes'] <= 7 && $accion['dias_restantes'] > 0): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-clock-fill me-2"></i>
                            <strong>Proxima a vencer!</strong> Quedan <?= $accion['dias_restantes'] ?> dia(s).
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Informacion Detallada -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informacion Detallada</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small d-block">Responsable</label>
                                <strong><?= esc($accion['responsable_usuario_nombre'] ?? $accion['responsable_nombre'] ?? 'No asignado') ?></strong>
                                <?php if (!empty($accion['responsable_email'])): ?>
                                <br><small class="text-muted"><?= esc($accion['responsable_email']) ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small d-block">Clasificacion Temporal</label>
                                <strong><?= ucwords(str_replace('_', ' ', $accion['clasificacion_temporal'] ?? '-')) ?></strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small d-block">Fecha Asignacion</label>
                                <strong><?= !empty($accion['fecha_asignacion']) ? date('d/m/Y', strtotime($accion['fecha_asignacion'])) : '-' ?></strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small d-block">Fecha Compromiso</label>
                                <strong class="<?= !empty($accion['esta_vencida']) ? 'text-danger' : '' ?>">
                                    <?= !empty($accion['fecha_compromiso']) ? date('d/m/Y', strtotime($accion['fecha_compromiso'])) : '-' ?>
                                </strong>
                            </div>
                            <?php if (!empty($accion['fecha_cierre_real'])): ?>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small d-block">Fecha Cierre Real</label>
                                <strong><?= date('d/m/Y', strtotime($accion['fecha_cierre_real'])) ?></strong>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($accion['recursos_requeridos'])): ?>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small d-block">Recursos Requeridos</label>
                                <strong><?= esc($accion['recursos_requeridos']) ?></strong>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($accion['costo_estimado'])): ?>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small d-block">Costo Estimado</label>
                                <strong>$<?= number_format($accion['costo_estimado'], 0, ',', '.') ?> COP</strong>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($accion['causa_raiz_identificada'])): ?>
                        <div class="mt-3 p-3 bg-light rounded">
                            <label class="text-muted small d-block mb-2">
                                <i class="bi bi-lightbulb me-1"></i>Causa Raiz Identificada
                            </label>
                            <p class="mb-0"><?= nl2br(esc($accion['causa_raiz_identificada'])) ?></p>
                        </div>
                        <?php else: ?>
                        <div class="mt-3">
                            <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/accion/{$accion['id_accion']}/analisis-causa-raiz") ?>"
                               class="btn btn-outline-primary">
                                <i class="bi bi-robot me-1"></i>Iniciar Analisis de Causa Raiz con IA
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Timeline de Seguimientos -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Historial de Seguimiento</h6>
                        <span class="badge bg-secondary"><?= count($timeline) ?> registro(s)</span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($timeline)): ?>
                        <div class="timeline">
                            <?php foreach ($timeline as $item): ?>
                            <div class="timeline-item <?= $item['color'] ?? 'secondary' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <i class="bi <?= $item['icono'] ?? 'bi-circle' ?> me-2"></i>
                                        <?= esc($item['descripcion']) ?>
                                        <?php if (!empty($item['porcentaje_avance'])): ?>
                                        <span class="badge bg-primary ms-2"><?= $item['porcentaje_avance'] ?>%</span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['archivo_adjunto'])): ?>
                                        <a href="<?= esc($item['archivo_adjunto']) ?>" target="_blank"
                                           class="btn btn-sm btn-outline-success ms-2">
                                            <i class="bi bi-file-earmark-arrow-down"></i> <?= esc($item['nombre_archivo']) ?>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?= $item['fecha_formateada'] ?? date('d/m/Y H:i', strtotime($item['created_at'])) ?></small>
                                </div>
                                <?php if (!empty($item['usuario_nombre'])): ?>
                                <small class="text-muted d-block mt-1">
                                    Por: <?= esc($item['usuario_nombre']) ?>
                                </small>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-muted text-center mb-0">
                            <i class="bi bi-inbox display-6 d-block mb-2"></i>
                            No hay registros de seguimiento aun
                        </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Verificaciones -->
                <?php if (!empty($accion['verificaciones'])): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Verificaciones de Efectividad</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Metodo</th>
                                        <th>Resultado</th>
                                        <th>Verificado por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($accion['verificaciones'] as $v): ?>
                                    <?php
                                    $resultadoClase = match($v['resultado']) {
                                        'efectiva' => 'success',
                                        'parcialmente_efectiva' => 'warning',
                                        'no_efectiva' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($v['fecha_verificacion'])) ?></td>
                                        <td><?= esc($metodos_verificacion[$v['metodo_verificacion']]['nombre'] ?? $v['metodo_verificacion']) ?></td>
                                        <td><span class="badge bg-<?= $resultadoClase ?>"><?= ucwords(str_replace('_', ' ', $v['resultado'])) ?></span></td>
                                        <td><?= esc($v['verificador_nombre'] ?? $v['verificado_por_nombre']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Columna Lateral - Acciones -->
            <div class="col-lg-4">
                <!-- Cambiar Estado -->
                <?php if (!empty($transiciones_validas)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0"><i class="bi bi-arrow-repeat me-2"></i>Cambiar Estado</h6>
                    </div>
                    <div class="card-body">
                        <form action="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/accion/{$accion['id_accion']}/cambiar-estado") ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <select class="form-select" name="nuevo_estado" required>
                                    <option value="">Seleccionar estado...</option>
                                    <?php foreach ($transiciones_validas as $estado): ?>
                                    <?php
                                    $estadoLabel = match($estado) {
                                        'asignada' => 'Asignada',
                                        'en_ejecucion' => 'En Ejecucion',
                                        'en_revision' => 'En Revision',
                                        'en_verificacion' => 'En Verificacion',
                                        'cerrada_efectiva' => 'Cerrada Efectiva',
                                        'cerrada_no_efectiva' => 'Cerrada No Efectiva',
                                        'reabierta' => 'Reabierta',
                                        'cancelada' => 'Cancelada',
                                        default => ucfirst($estado)
                                    };
                                    ?>
                                    <option value="<?= $estado ?>"><?= $estadoLabel ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" name="notas" rows="2" placeholder="Notas del cambio (opcional)"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check2 me-1"></i>Actualizar Estado
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Registrar Avance -->
                <?php if (!in_array($accion['estado'], ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada'])): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Registrar Avance</h6>
                    </div>
                    <div class="card-body">
                        <form action="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/accion/{$accion['id_accion']}/avance") ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label">Porcentaje de Avance</label>
                                <input type="range" class="form-range" name="porcentaje_avance" id="porcentaje_avance"
                                       min="0" max="100" step="5" value="50">
                                <div class="text-center"><span id="porcentaje_label">50</span>%</div>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" name="descripcion" rows="2"
                                          placeholder="Describa el avance realizado..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle me-1"></i>Registrar Avance
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Subir Evidencia -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0"><i class="bi bi-paperclip me-2"></i>Subir Evidencia</h6>
                    </div>
                    <div class="card-body">
                        <!-- Tabs para tipo de evidencia -->
                        <ul class="nav nav-tabs mb-3" id="evidenciaTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="archivo-tab" data-bs-toggle="tab" data-bs-target="#archivo-pane"
                                        type="button" role="tab">
                                    <i class="bi bi-file-earmark me-1"></i>Archivo
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="enlace-tab" data-bs-toggle="tab" data-bs-target="#enlace-pane"
                                        type="button" role="tab">
                                    <i class="bi bi-link-45deg me-1"></i>Enlace
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content" id="evidenciaTabContent">
                            <!-- Tab Archivo -->
                            <div class="tab-pane fade show active" id="archivo-pane" role="tabpanel">
                                <form action="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/accion/{$accion['id_accion']}/evidencia") ?>"
                                      method="post" enctype="multipart/form-data">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="tipo_evidencia" value="archivo">
                                    <div class="mb-3">
                                        <input type="file" class="form-control" name="archivo" required
                                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls">
                                    </div>
                                    <div class="mb-3">
                                        <textarea class="form-control" name="descripcion" rows="2"
                                                  placeholder="Descripcion de la evidencia..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-info w-100 text-white">
                                        <i class="bi bi-upload me-1"></i>Subir Archivo
                                    </button>
                                </form>
                            </div>
                            <!-- Tab Enlace -->
                            <div class="tab-pane fade" id="enlace-pane" role="tabpanel">
                                <form action="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/accion/{$accion['id_accion']}/evidencia") ?>"
                                      method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="tipo_evidencia" value="enlace">
                                    <div class="mb-3">
                                        <label class="form-label">URL del Enlace</label>
                                        <input type="url" class="form-control" name="enlace_url" required
                                               placeholder="https://ejemplo.com/documento">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Nombre del Enlace</label>
                                        <input type="text" class="form-control" name="enlace_nombre"
                                               placeholder="Ej: Informe de Auditoria 2024">
                                    </div>
                                    <div class="mb-3">
                                        <textarea class="form-control" name="descripcion" rows="2"
                                                  placeholder="Descripcion de la evidencia..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-info w-100 text-white">
                                        <i class="bi bi-link me-1"></i>Guardar Enlace
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Verificacion de Efectividad -->
                <?php if ($accion['estado'] === 'en_verificacion'): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Verificar Efectividad</h6>
                    </div>
                    <div class="card-body">
                        <form action="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/accion/{$accion['id_accion']}/verificacion") ?>" method="post"
                              enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label">Metodo de Verificacion</label>
                                <select class="form-select" name="metodo_verificacion" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($metodos_verificacion as $codigo => $metodo): ?>
                                    <option value="<?= $codigo ?>"><?= esc($metodo['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Resultado</label>
                                <select class="form-select" name="resultado" required>
                                    <option value="">Seleccione...</option>
                                    <option value="efectiva">Efectiva</option>
                                    <option value="parcialmente_efectiva">Parcialmente Efectiva</option>
                                    <option value="no_efectiva">No Efectiva</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fecha de Verificacion</label>
                                <input type="date" class="form-control" name="fecha_verificacion" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" name="observaciones" rows="3"
                                          placeholder="Observaciones de la verificacion..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Evidencia (opcional)</label>
                                <input type="file" class="form-control" name="evidencia_verificacion"
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="crear_nueva_accion" id="crear_nueva_accion">
                                <label class="form-check-label" for="crear_nueva_accion">
                                    Crear nueva accion si no es efectiva
                                </label>
                            </div>
                            <button type="submit" class="btn btn-purple w-100 text-white" style="background-color: #6f42c1;">
                                <i class="bi bi-check2-all me-1"></i>Registrar Verificacion
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Comentario -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Agregar Comentario</h6>
                    </div>
                    <div class="card-body">
                        <form action="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/accion/{$accion['id_accion']}/comentario") ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <textarea class="form-control" name="comentario" rows="3"
                                          placeholder="Escriba su comentario..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-send me-1"></i>Enviar Comentario
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('porcentaje_avance')?.addEventListener('input', function() {
        document.getElementById('porcentaje_label').textContent = this.value;
    });
    </script>
</body>
</html>
