<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador Capacitaciones SST - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-warning">
        <div class="container-fluid">
            <a class="navbar-brand text-dark" href="#">
                <i class="bi bi-mortarboard me-2"></i>Generador IA - Capacitaciones SST
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-dark me-3">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Programa de Capacitacion en SST</h4>
                <p class="text-muted mb-0">Estandar 1.2.1 - Resolucion 0312/2019</p>
            </div>
            <div>
                <span class="badge bg-info fs-6"><?= $anio ?></span>
            </div>
        </div>

        <!-- Alertas -->
        <div id="alertContainer"></div>

        <!-- Info del flujo -->
        <div class="alert alert-warning mb-4">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Flujo de generacion del Programa de Capacitacion:</strong>
            <ol class="mb-0 mt-2">
                <li><strong>Capacitaciones SST</strong> - Se generan capacitaciones en el cronograma</li>
                <li><strong>Plan de Trabajo Anual</strong> - Las capacitaciones se reflejan en el PTA</li>
                <li><strong>Indicadores</strong> - Se configuran indicadores para medir cumplimiento</li>
                <li><strong>Documento del Programa</strong> - Se genera el documento formal con datos de la BD</li>
            </ol>
        </div>

        <!-- Contexto del Cliente para la IA -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-cpu text-primary me-2"></i>Contexto para la IA
                </h6>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#collapseContexto">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>
            <div class="collapse show" id="collapseContexto">
                <div class="card-body">
                    <div class="row">
                        <!-- Datos del Cliente -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3"><i class="bi bi-building me-1"></i>Datos de la Empresa</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width:40%">Actividad economica:</td>
                                    <td><strong><?= esc($contexto['actividad_economica_principal'] ?? 'No definida') ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Codigo CIIU:</td>
                                    <td><?= esc($contexto['codigo_ciiu_principal'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Nivel de riesgo ARL:</td>
                                    <td>
                                        <?php
                                        $riesgo = $contexto['nivel_riesgo_arl'] ?? 'N/A';
                                        $colorRiesgo = match($riesgo) {
                                            'I', 'II' => 'success',
                                            'III' => 'warning',
                                            'IV', 'V' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $colorRiesgo ?>"><?= $riesgo ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Total trabajadores:</td>
                                    <td><strong><?= $contexto['total_trabajadores'] ?? 'No definido' ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Estandares aplicables:</td>
                                    <td><span class="badge bg-info"><?= $contexto['estandares_aplicables'] ?? '60' ?> estandares</span></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Peligros Identificados -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3"><i class="bi bi-exclamation-triangle me-1"></i>Peligros Identificados</h6>
                            <?php
                            $peligros = [];
                            if (!empty($contexto['peligros_identificados'])) {
                                $peligros = json_decode($contexto['peligros_identificados'], true) ?? [];
                            }
                            ?>
                            <?php if (!empty($peligros)): ?>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach ($peligros as $peligro): ?>
                                        <span class="badge bg-warning text-dark"><?= esc($peligro) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-info-circle me-1"></i>No hay peligros identificados.
                                    <a href="<?= base_url('contexto-cliente/' . $cliente['id_cliente']) ?>">Configurar contexto</a>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($contexto['observaciones_contexto'])): ?>
                                <div class="mt-3">
                                    <small class="text-muted d-block mb-1">Observaciones:</small>
                                    <small><?= esc($contexto['observaciones_contexto']) ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Instrucciones adicionales para la IA -->
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label">
                                <i class="bi bi-chat-dots me-1"></i>Instrucciones adicionales para la IA
                                <small class="text-muted">(opcional)</small>
                            </label>
                            <textarea id="instruccionesIA" class="form-control" rows="3"
                                placeholder="Ej: Incluir capacitacion de trabajo en alturas, enfocarse en riesgo quimico, agregar capacitacion de primeros auxilios..."></textarea>
                            <small class="text-muted">
                                Describa necesidades especificas de la empresa para personalizar las capacitaciones.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Paso 1: Capacitaciones SST -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <span class="badge bg-dark text-warning me-2">1</span>
                            Capacitaciones SST
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Estado actual -->
                        <div class="mb-3">
                            <p class="text-muted small mb-2">Estado actual (<?= $anio ?>):</p>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Capacitaciones existentes:</span>
                                <strong><?= $resumenCapacitaciones['existentes'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Minimo requerido:</span>
                                <strong><?= $resumenCapacitaciones['minimo'] ?></strong>
                            </div>
                            <?php if ($resumenCapacitaciones['completo']): ?>
                                <div class="alert alert-success small mb-0 mt-2">
                                    <i class="bi bi-check-circle me-1"></i>Fase completa
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning small mb-0 mt-2">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Faltan <?= $resumenCapacitaciones['minimo'] - $resumenCapacitaciones['existentes'] ?> capacitaciones
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Que se generara -->
                        <div class="alert alert-light small mb-3">
                            <strong>Se generaran capacitaciones como:</strong>
                            <ul class="mb-0 mt-1">
                                <li>Induccion y reinduccion en SST</li>
                                <li>Identificacion de peligros y riesgos</li>
                                <li>Uso de EPP</li>
                                <li>Capacitacion en emergencias</li>
                                <li>Capacitacion segun peligros de la empresa</li>
                            </ul>
                        </div>

                        <!-- Botones -->
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-warning text-dark" onclick="previewCapacitaciones()">
                                <i class="bi bi-eye me-1"></i>Ver Preview
                            </button>
                            <button type="button" class="btn btn-warning text-dark" onclick="generarCapacitaciones()">
                                <i class="bi bi-magic me-1"></i>Generar Capacitaciones
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paso 2: Indicadores -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <span class="badge bg-white text-primary me-2">2</span>
                            Indicadores de Capacitacion
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Estado actual -->
                        <div class="mb-3">
                            <p class="text-muted small mb-2">Estado actual:</p>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Indicadores configurados:</span>
                                <strong><?= $verificacionIndicadores['total'] ?? 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Minimo requerido:</span>
                                <strong><?= $verificacionIndicadores['minimo'] ?? 2 ?></strong>
                            </div>
                            <?php if (($verificacionIndicadores['completo'] ?? false)): ?>
                                <div class="alert alert-success small mb-0 mt-2">
                                    <i class="bi bi-check-circle me-1"></i>Fase completa
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning small mb-0 mt-2">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Faltan indicadores
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Indicadores sugeridos -->
                        <div class="alert alert-light small mb-3">
                            <strong>Indicadores recomendados:</strong>
                            <ul class="mb-0 mt-1">
                                <li>Cumplimiento del cronograma</li>
                                <li>Cobertura de capacitaciones</li>
                                <li>Eficacia de la capacitacion</li>
                                <li>Horas de capacitacion por trabajador</li>
                            </ul>
                        </div>

                        <!-- Botones igual que Parte 1 -->
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary"
                                    onclick="previewIndicadores()"
                                    <?= !$resumenCapacitaciones['completo'] ? 'disabled' : '' ?>>
                                <i class="bi bi-eye me-1"></i>Ver Preview
                            </button>
                            <button type="button" class="btn btn-primary"
                                    onclick="generarIndicadoresDirecto()"
                                    <?= !$resumenCapacitaciones['completo'] ? 'disabled' : '' ?>>
                                <i class="bi bi-graph-up me-1"></i>Generar Indicadores
                            </button>
                            <?php if (!$resumenCapacitaciones['completo']): ?>
                                <small class="text-muted text-center">
                                    <i class="bi bi-info-circle me-1"></i>Primero genere las capacitaciones
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parte 3: Documento del Programa -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <span class="badge bg-white text-success me-2">3</span>
                            Documento del Programa de Capacitacion
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p class="mb-2">
                                    <i class="bi bi-file-earmark-text me-2"></i>
                                    El documento formal del Programa de Capacitacion consolida:
                                </p>
                                <ul class="mb-3">
                                    <li>Las capacitaciones definidas en el cronograma (Parte 1)</li>
                                    <li>Los indicadores de medicion configurados (Parte 2)</li>
                                    <li>Datos del contexto de la empresa</li>
                                </ul>

                                <?php
                                $parte1Completa = $resumenCapacitaciones['completo'] ?? false;
                                $parte2Completa = $verificacionIndicadores['completo'] ?? false;
                                $puedeGenerarDocumento = $parte1Completa && $parte2Completa;
                                ?>

                                <?php if ($puedeGenerarDocumento): ?>
                                    <div class="alert alert-success small mb-0">
                                        <i class="bi bi-check-circle me-1"></i>
                                        <strong>Listo para generar</strong> - Parte 1 y Parte 2 completadas
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning small mb-0">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        <strong>Requisitos pendientes:</strong>
                                        <ul class="mb-0 mt-1">
                                            <?php if (!$parte1Completa): ?>
                                                <li>Complete la Parte 1 (Capacitaciones)</li>
                                            <?php endif; ?>
                                            <?php if (!$parte2Completa): ?>
                                                <li>Complete la Parte 2 (Indicadores)</li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 d-flex align-items-center justify-content-end">
                                <a href="<?= base_url('documentos/generar/programa_capacitacion/' . $cliente['id_cliente']) ?>"
                                   class="btn btn-success btn-lg <?= !$puedeGenerarDocumento ? 'disabled' : '' ?>">
                                    <i class="bi bi-file-earmark-plus me-2"></i>
                                    Ir a Generar Documento
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Capacitaciones existentes -->
        <?php if (!empty($capacitacionesExistentes)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-list-check me-2"></i>Capacitaciones en el Cronograma (<?= count($capacitacionesExistentes) ?>)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Capacitacion</th>
                                <th>Objetivo</th>
                                <th>Fecha Programada</th>
                                <th>Perfil Asistentes</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($capacitacionesExistentes as $cap): ?>
                            <tr>
                                <td><?= esc($cap['nombre_capacitacion'] ?? $cap['capacitacion'] ?? 'Sin nombre') ?></td>
                                <td><small class="text-muted"><?= esc(substr($cap['objetivo_capacitacion'] ?? '', 0, 80)) ?>...</small></td>
                                <td><?= !empty($cap['fecha_programada']) ? date('d M Y', strtotime($cap['fecha_programada'])) : '-' ?></td>
                                <td><span class="badge bg-secondary"><?= esc($cap['perfil_de_asistentes'] ?? 'TODOS') ?></span></td>
                                <td>
                                    <?php
                                    $estadoColor = match(strtoupper($cap['estado'] ?? '')) {
                                        'EJECUTADA' => 'success',
                                        'PROGRAMADA' => 'primary',
                                        'REPROGRAMADA' => 'warning',
                                        'CANCELADA POR EL CLIENTE' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $estadoColor ?>">
                                        <?= esc($cap['estado'] ?? 'PENDIENTE') ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Modal Preview con seleccion -->
        <div class="modal fade" id="modalPreview" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Seleccionar Capacitaciones SST</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="previewContent">
                        <div class="text-center py-4">
                            <div class="spinner-border text-warning"></div>
                            <p class="mt-2">Generando capacitaciones sugeridas...</p>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <div>
                            <span id="contadorSeleccion" class="text-muted">0 capacitaciones seleccionadas</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-warning text-dark" id="btnGenerarSeleccionadas" onclick="generarCapacitacionesSeleccionadas()">
                                <i class="bi bi-magic me-1"></i>Generar Seleccionadas
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Preview Indicadores (Parte 2) -->
        <div class="modal fade" id="modalPreviewIndicadores" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-graph-up me-2"></i>Seleccionar Indicadores de Capacitacion</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="previewIndicadoresContent">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary"></div>
                            <p class="mt-2">Generando indicadores sugeridos...</p>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <div>
                            <span id="contadorIndicadores" class="text-muted">0 indicadores seleccionados</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="btnGenerarIndicadores" onclick="generarIndicadoresSeleccionados()">
                                <i class="bi bi-graph-up me-1"></i>Generar Seleccionados
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
        <div id="toastNotification" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi me-2" id="toastIcon"></i>
                <strong class="me-auto" id="toastTitle">Notificacion</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastBody"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const idCliente = <?= $cliente['id_cliente'] ?>;
    const anio = <?= $anio ?>;
    let capacitacionesData = [];
    let explicacionIA = '';

    const meses = {
        1: 'Enero', 2: 'Febrero', 3: 'Marzo', 4: 'Abril',
        5: 'Mayo', 6: 'Junio', 7: 'Julio', 8: 'Agosto',
        9: 'Septiembre', 10: 'Octubre', 11: 'Noviembre', 12: 'Diciembre'
    };

    function showToast(type, title, message) {
        const toast = document.getElementById('toastNotification');
        const toastIcon = document.getElementById('toastIcon');
        const toastTitle = document.getElementById('toastTitle');
        const toastBody = document.getElementById('toastBody');

        toast.className = 'toast';
        if (type === 'success') {
            toastIcon.className = 'bi bi-check-circle-fill text-success me-2';
            toast.classList.add('border-success');
        } else if (type === 'error') {
            toastIcon.className = 'bi bi-x-circle-fill text-danger me-2';
            toast.classList.add('border-danger');
        } else if (type === 'warning') {
            toastIcon.className = 'bi bi-exclamation-triangle-fill text-warning me-2';
            toast.classList.add('border-warning');
        } else {
            toastIcon.className = 'bi bi-info-circle-fill text-primary me-2';
            toast.classList.add('border-primary');
        }

        toastTitle.textContent = title;
        toastBody.innerHTML = message;

        const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
        bsToast.show();
    }

    function showAlert(type, message) {
        document.getElementById('alertContainer').innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show">
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }

    function getInstruccionesIA() {
        const textarea = document.getElementById('instruccionesIA');
        return textarea ? textarea.value.trim() : '';
    }

    function previewCapacitaciones() {
        const modal = new bootstrap.Modal(document.getElementById('modalPreview'));
        modal.show();

        const instrucciones = encodeURIComponent(getInstruccionesIA());
        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/preview-capacitaciones-sst?anio=${anio}&instrucciones=${instrucciones}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    capacitacionesData = data.data.capacitaciones;
                    explicacionIA = data.data.explicacion_ia || '';
                    renderPreviewTable();
                } else {
                    document.getElementById('previewContent').innerHTML =
                        `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(err => {
                document.getElementById('previewContent').innerHTML =
                    `<div class="alert alert-danger">Error de conexion: ${err.message}</div>`;
            });
    }

    function renderPreviewTable() {
        let explicacionHtml = '';
        if (explicacionIA) {
            explicacionHtml = `
                <div class="alert alert-success small mb-3">
                    <i class="bi bi-robot me-2"></i><strong>IA aplico ajustes:</strong> ${explicacionIA}
                </div>`;
        }

        const origenColors = {
            'base': 'secondary',
            'peligro': 'warning',
            'instruccion': 'info',
            'ia': 'purple'
        };

        const origenLabels = {
            'base': 'Base',
            'peligro': 'Peligro',
            'instruccion': 'Instruccion',
            'ia': 'IA'
        };

        let html = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <strong>Total: ${capacitacionesData.length} capacitaciones sugeridas para ${anio}</strong>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="seleccionarTodos(true)">
                        <i class="bi bi-check-all me-1"></i>Seleccionar Todos
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="seleccionarTodos(false)">
                        <i class="bi bi-x-lg me-1"></i>Deseleccionar
                    </button>
                </div>
            </div>
            ${explicacionHtml}
            <div class="alert alert-light small mb-3 border">
                <i class="bi bi-info-circle me-1"></i>
                Seleccione las capacitaciones, <strong>edite los campos</strong> si es necesario, y use <strong>"Mejorar con IA"</strong> para regenerar individualmente.
                <div class="mt-2">
                    <span class="badge bg-secondary me-1">Base</span> Obligatorias
                    <span class="badge bg-warning text-dark ms-2 me-1">Peligro</span> Segun peligros
                    <span class="badge bg-purple text-white ms-2 me-1" style="background-color:#9c27b0">IA</span> Generado por IA
                </div>
            </div>
            <div style="max-height: 60vh; overflow-y: auto;">`;

        capacitacionesData.forEach((cap, idx) => {
            let origen = cap.origen || 'base';
            if (cap.generado_por_ia || cap.modificada_por_ia) {
                origen = 'ia';
            }

            const origenStyle = origen === 'ia' ? 'style="background-color:#9c27b0"' : '';
            const origenBadge = `<span class="badge bg-${origenColors[origen]} ${origen === 'peligro' ? 'text-dark' : ''}" ${origenStyle}>${origenLabels[origen]}</span>`;

            let mesOptions = '';
            for (let m = 1; m <= 12; m++) {
                const selected = m === cap.mes_num ? 'selected' : '';
                mesOptions += `<option value="${m}" ${selected}>${meses[m]}</option>`;
            }

            html += `
            <div class="card mb-2 capacitacion-card border-start border-3 border-warning" data-idx="${idx}">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-start">
                        <!-- CHECKBOX -->
                        <div class="form-check me-2 pt-1">
                            <input type="checkbox" class="form-check-input capacitacion-check"
                                   data-idx="${idx}" checked onchange="actualizarContador()">
                        </div>

                        <div class="flex-grow-1">
                            <!-- FILA 1: Nombre editable + Badge origen -->
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <input type="text" class="form-control form-control-sm fw-bold cap-nombre"
                                       data-idx="${idx}" value="${escapeHtml(cap.nombre)}"
                                       placeholder="Nombre de la capacitacion" style="flex:1; margin-right:8px;">
                                ${origenBadge}
                            </div>

                            <!-- FILA 2: Objetivo editable -->
                            <div class="mb-2">
                                <textarea class="form-control form-control-sm cap-objetivo" data-idx="${idx}"
                                          rows="2" placeholder="Objetivo de la capacitacion">${escapeHtml(cap.objetivo || '')}</textarea>
                            </div>

                            <!-- FILA 3: Mes, Perfil, Horas -->
                            <div class="row g-2 mb-2">
                                <div class="col-md-4">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                        <select class="form-select form-select-sm mes-select" data-idx="${idx}">
                                            ${mesOptions}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-people"></i></span>
                                        <select class="form-select form-select-sm cap-perfil" data-idx="${idx}">
                                            <option value="TODOS" ${cap.perfil_asistentes === 'TODOS' ? 'selected' : ''}>TODOS</option>
                                            <option value="COPASST_VIGIA" ${cap.perfil_asistentes === 'COPASST_VIGIA' ? 'selected' : ''}>COPASST / VIGIA</option>
                                            <option value="COMITE_CONVIVENCIA" ${cap.perfil_asistentes === 'COMITE_CONVIVENCIA' ? 'selected' : ''}>COMITE CONVIVENCIA</option>
                                            <option value="BRIGADA_EMERGENCIAS" ${cap.perfil_asistentes === 'BRIGADA_EMERGENCIAS' ? 'selected' : ''}>BRIGADA</option>
                                            <option value="OPERATIVOS" ${cap.perfil_asistentes === 'OPERATIVOS' ? 'selected' : ''}>OPERATIVOS</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                        <input type="number" class="form-control form-control-sm cap-horas"
                                               data-idx="${idx}" value="${cap.horas || 1}" min="1" max="40">
                                        <span class="input-group-text">h</span>
                                    </div>
                                </div>
                            </div>

                            ${cap.peligro_relacionado ? `<small class="d-block text-warning mb-2"><i class="bi bi-exclamation-triangle me-1"></i>${cap.peligro_relacionado}</small>` : ''}

                            <!-- PANEL MEJORAR CON IA -->
                            <div class="border-top pt-2">
                                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0"
                                        onclick="toggleIAPanel(${idx})">
                                    <i class="bi bi-robot me-1"></i>
                                    <small>Mejorar con IA</small>
                                    <i class="bi bi-chevron-down ms-1" id="iaChevron${idx}"></i>
                                </button>

                                <div class="collapse mt-2" id="iaPanelCap${idx}">
                                    <div class="card card-body bg-light border-0 p-2">
                                        <textarea class="form-control form-control-sm instrucciones-ia-cap mb-2"
                                                  data-idx="${idx}" rows="2"
                                                  placeholder="Ej: Hazlo mas especifico para riesgo quimico, enfoca en EPP, agrega practica..."></textarea>
                                        <button type="button" class="btn btn-sm w-100"
                                                style="border-color:#9c27b0; color:#9c27b0; background:white;"
                                                onclick="regenerarCapacitacionConIA(${idx})">
                                            <i class="bi bi-magic me-1"></i>Regenerar esta capacitacion
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        });

        html += `</div>`;
        document.getElementById('previewContent').innerHTML = html;
        actualizarContador();
    }

    function toggleIAPanel(idx) {
        const panel = document.getElementById(`iaPanelCap${idx}`);
        const chevron = document.getElementById(`iaChevron${idx}`);

        if (panel.classList.contains('show')) {
            panel.classList.remove('show');
            chevron.classList.remove('bi-chevron-up');
            chevron.classList.add('bi-chevron-down');
        } else {
            panel.classList.add('show');
            chevron.classList.remove('bi-chevron-down');
            chevron.classList.add('bi-chevron-up');
        }
    }

    function getCapacitacionData(idx) {
        return {
            nombre: document.querySelector(`.cap-nombre[data-idx="${idx}"]`).value,
            objetivo: document.querySelector(`.cap-objetivo[data-idx="${idx}"]`).value,
            mes_num: parseInt(document.querySelector(`.mes-select[data-idx="${idx}"]`).value),
            perfil_asistentes: document.querySelector(`.cap-perfil[data-idx="${idx}"]`).value,
            horas: parseInt(document.querySelector(`.cap-horas[data-idx="${idx}"]`).value) || 1,
            origen: capacitacionesData[idx]?.origen || 'base'
        };
    }

    function regenerarCapacitacionConIA(idx) {
        const instrucciones = document.querySelector(`.instrucciones-ia-cap[data-idx="${idx}"]`).value;
        const capActual = getCapacitacionData(idx);

        if (!instrucciones.trim()) {
            showToast('info', 'Instrucciones', 'Escriba instrucciones para que la IA mejore esta capacitacion');
            return;
        }

        const btn = event.target;
        const btnOriginal = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Regenerando...';

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/regenerar-capacitacion`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                capacitacion: capActual,
                instrucciones: instrucciones,
                contexto_general: getInstruccionesIA()
            })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = btnOriginal;

            if (data.success && data.data) {
                const nuevo = data.data;
                document.querySelector(`.cap-nombre[data-idx="${idx}"]`).value = nuevo.nombre || capActual.nombre;
                document.querySelector(`.cap-objetivo[data-idx="${idx}"]`).value = nuevo.objetivo || capActual.objetivo;
                if (nuevo.horas) document.querySelector(`.cap-horas[data-idx="${idx}"]`).value = nuevo.horas;
                if (nuevo.perfil_asistentes) document.querySelector(`.cap-perfil[data-idx="${idx}"]`).value = nuevo.perfil_asistentes;

                // Actualizar datos en memoria
                capacitacionesData[idx] = {...capacitacionesData[idx], ...nuevo, modificada_por_ia: true};

                showToast('success', 'Regenerado', 'La capacitacion fue mejorada por la IA');
                document.querySelector(`.instrucciones-ia-cap[data-idx="${idx}"]`).value = '';
            } else {
                showToast('error', 'Error', data.message || 'No se pudo regenerar');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = btnOriginal;
            showToast('error', 'Error', 'Error de conexion');
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function seleccionarTodos(seleccionar) {
        document.querySelectorAll('.capacitacion-check').forEach(cb => {
            cb.checked = seleccionar;
        });
        document.getElementById('checkAll').checked = seleccionar;
        actualizarContador();
    }

    function actualizarContador() {
        const total = document.querySelectorAll('.capacitacion-check:checked').length;
        document.getElementById('contadorSeleccion').textContent = `${total} capacitaciones seleccionadas`;

        const btnGenerar = document.getElementById('btnGenerarSeleccionadas');
        if (total === 0) {
            btnGenerar.disabled = true;
            btnGenerar.innerHTML = '<i class="bi bi-magic me-1"></i>Seleccione capacitaciones';
        } else {
            btnGenerar.disabled = false;
            btnGenerar.innerHTML = `<i class="bi bi-magic me-1"></i>Generar ${total} Capacitaciones`;
        }
    }

    function generarCapacitaciones() {
        previewCapacitaciones();
    }

    function generarCapacitacionesSeleccionadas() {
        const seleccionadas = [];

        document.querySelectorAll('.capacitacion-check:checked').forEach(cb => {
            const idx = parseInt(cb.dataset.idx);

            // Leer valores editados de los campos
            const capEditada = getCapacitacionData(idx);

            seleccionadas.push({
                ...capacitacionesData[idx],
                ...capEditada,
                mes: meses[capEditada.mes_num]
            });
        });

        if (seleccionadas.length === 0) {
            showAlert('warning', 'Seleccione al menos una capacitacion');
            return;
        }

        if (!confirm(`¿Generar ${seleccionadas.length} capacitaciones en el cronograma?`)) return;

        const btn = document.getElementById('btnGenerarSeleccionadas');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generando...';

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/generar-capacitaciones-sst`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                anio: anio,
                capacitaciones: seleccionadas,
                instrucciones: getInstruccionesIA()
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalPreview')).hide();

                const detalles = `<strong>${data.data?.creadas || 0}</strong> capacitaciones creadas<br>
                                  <strong>${data.data?.existentes || 0}</strong> ya existian`;
                showToast('success', 'Capacitaciones Generadas', detalles);

                setTimeout(() => location.reload(), 2000);
            } else {
                showToast('error', 'Error', data.message);
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-magic me-1"></i>Generar ${seleccionadas.length} Capacitaciones`;
            }
        })
        .catch(err => {
            showToast('error', 'Error de Conexion', 'No se pudo conectar con el servidor');
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-magic me-1"></i>Generar ${seleccionadas.length} Capacitaciones`;
        });
    }

    // =========================================================================
    // PARTE 2: INDICADORES DE CAPACITACION
    // =========================================================================

    let indicadoresData = [];
    let explicacionIndicadoresIA = '';

    const tiposIndicador = {
        'proceso': { color: 'primary', label: 'Proceso' },
        'resultado': { color: 'success', label: 'Resultado' },
        'estructura': { color: 'info', label: 'Estructura' }
    };

    const periodicidades = {
        'mensual': 'Mensual',
        'trimestral': 'Trimestral',
        'semestral': 'Semestral',
        'anual': 'Anual'
    };

    function previewIndicadores() {
        const modal = new bootstrap.Modal(document.getElementById('modalPreviewIndicadores'));
        modal.show();

        const instrucciones = encodeURIComponent(getInstruccionesIA());
        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/preview-indicadores-capacitacion?anio=${anio}&instrucciones=${instrucciones}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (data.data.error) {
                        document.getElementById('previewIndicadoresContent').innerHTML =
                            `<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>${data.data.mensaje}</div>`;
                        return;
                    }
                    indicadoresData = data.data.indicadores;
                    explicacionIndicadoresIA = data.data.explicacion_ia || '';
                    renderIndicadoresCards();
                } else {
                    document.getElementById('previewIndicadoresContent').innerHTML =
                        `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(err => {
                document.getElementById('previewIndicadoresContent').innerHTML =
                    `<div class="alert alert-danger">Error de conexion: ${err.message}</div>`;
            });
    }

    function generarIndicadoresDirecto() {
        previewIndicadores();
    }

    function renderIndicadoresCards() {
        let explicacionHtml = '';
        if (explicacionIndicadoresIA) {
            explicacionHtml = `
                <div class="alert alert-info small mb-3">
                    <i class="bi bi-robot me-2"></i><strong>IA:</strong> ${explicacionIndicadoresIA}
                </div>`;
        }

        let html = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <strong>Total: ${indicadoresData.length} indicadores sugeridos</strong>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="seleccionarTodosIndicadores(true)">
                        <i class="bi bi-check-all me-1"></i>Seleccionar Todos
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="seleccionarTodosIndicadores(false)">
                        <i class="bi bi-x-lg me-1"></i>Deseleccionar
                    </button>
                </div>
            </div>
            ${explicacionHtml}
            <div class="alert alert-light small mb-3 border">
                <i class="bi bi-info-circle me-1"></i>
                Seleccione los indicadores, <strong>edite los campos</strong> si es necesario, y use <strong>"Mejorar con IA"</strong> para regenerar individualmente.
                <div class="mt-2">
                    <span class="badge bg-primary me-1">Proceso</span> Miden ejecucion de actividades
                    <span class="badge bg-success ms-2 me-1">Resultado</span> Miden impacto en SST
                </div>
            </div>
            <div style="max-height: 60vh; overflow-y: auto;">`;

        indicadoresData.forEach((ind, idx) => {
            const tipo = ind.tipo || 'proceso';
            const tipoInfo = tiposIndicador[tipo] || tiposIndicador.proceso;

            const yaExiste = ind.ya_existe ? '<span class="badge bg-secondary ms-2">Ya existe</span>' : '';
            const checked = ind.seleccionado !== false && !ind.ya_existe ? 'checked' : '';
            const disabled = ind.ya_existe ? 'disabled' : '';

            let periodicidadOptions = '';
            for (const [key, label] of Object.entries(periodicidades)) {
                const selected = key === ind.periodicidad ? 'selected' : '';
                periodicidadOptions += `<option value="${key}" ${selected}>${label}</option>`;
            }

            let tipoOptions = '';
            for (const [key, info] of Object.entries(tiposIndicador)) {
                const selected = key === tipo ? 'selected' : '';
                tipoOptions += `<option value="${key}" ${selected}>${info.label}</option>`;
            }

            html += `
            <div class="card mb-2 indicador-card border-start border-3 border-primary" data-idx="${idx}">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-start">
                        <!-- CHECKBOX -->
                        <div class="form-check me-2 pt-1">
                            <input type="checkbox" class="form-check-input indicador-check"
                                   data-idx="${idx}" ${checked} ${disabled} onchange="actualizarContadorIndicadores()">
                        </div>

                        <div class="flex-grow-1">
                            <!-- FILA 1: Nombre editable + Badge tipo -->
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <input type="text" class="form-control form-control-sm fw-bold ind-nombre"
                                       data-idx="${idx}" value="${escapeHtml(ind.nombre)}"
                                       placeholder="Nombre del indicador" style="flex:1; margin-right:8px;" ${disabled}>
                                <span class="badge bg-${tipoInfo.color}">${tipoInfo.label}</span>
                                ${yaExiste}
                            </div>

                            <!-- FILA 2: Formula editable -->
                            <div class="mb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calculator"></i></span>
                                    <input type="text" class="form-control form-control-sm ind-formula"
                                           data-idx="${idx}" value="${escapeHtml(ind.formula || '')}"
                                           placeholder="Formula del indicador" ${disabled}>
                                </div>
                            </div>

                            <!-- FILA 3: Meta, Unidad, Periodicidad, Tipo -->
                            <div class="row g-2 mb-2">
                                <div class="col-md-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Meta</span>
                                        <input type="number" class="form-control form-control-sm ind-meta"
                                               data-idx="${idx}" value="${ind.meta || 100}" min="0" max="100" ${disabled}>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Unidad</span>
                                        <input type="text" class="form-control form-control-sm ind-unidad"
                                               data-idx="${idx}" value="${escapeHtml(ind.unidad || '%')}" ${disabled}>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm ind-periodicidad" data-idx="${idx}" ${disabled}>
                                        ${periodicidadOptions}
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm ind-tipo" data-idx="${idx}" ${disabled}>
                                        ${tipoOptions}
                                    </select>
                                </div>
                            </div>

                            <!-- FILA 4: Descripcion editable -->
                            <div class="mb-2">
                                <textarea class="form-control form-control-sm ind-descripcion" data-idx="${idx}"
                                          rows="2" placeholder="Descripcion del indicador" ${disabled}>${escapeHtml(ind.descripcion || '')}</textarea>
                            </div>

                            ${ind.obligatorio ? '<small class="d-block text-primary mb-2"><i class="bi bi-check-circle me-1"></i>Indicador obligatorio segun Res. 0312/2019</small>' : ''}

                            <!-- PANEL MEJORAR CON IA -->
                            ${!ind.ya_existe ? `
                            <div class="border-top pt-2">
                                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0"
                                        onclick="toggleIAPanelIndicador(${idx})">
                                    <i class="bi bi-robot me-1"></i>
                                    <small>Mejorar con IA</small>
                                    <i class="bi bi-chevron-down ms-1" id="iaChevronInd${idx}"></i>
                                </button>

                                <div class="collapse mt-2" id="iaPanelInd${idx}">
                                    <div class="card card-body bg-light border-0 p-2">
                                        <textarea class="form-control form-control-sm instrucciones-ia-ind mb-2"
                                                  data-idx="${idx}" rows="2"
                                                  placeholder="Ej: Hazlo mas especifico, cambia la meta, ajusta la formula..."></textarea>
                                        <button type="button" class="btn btn-sm w-100"
                                                style="border-color:#0d6efd; color:#0d6efd; background:white;"
                                                onclick="regenerarIndicadorConIA(${idx})">
                                            <i class="bi bi-magic me-1"></i>Regenerar este indicador
                                        </button>
                                    </div>
                                </div>
                            </div>` : ''}
                        </div>
                    </div>
                </div>
            </div>`;
        });

        html += `</div>`;
        document.getElementById('previewIndicadoresContent').innerHTML = html;
        actualizarContadorIndicadores();
    }

    function toggleIAPanelIndicador(idx) {
        const panel = document.getElementById(`iaPanelInd${idx}`);
        const chevron = document.getElementById(`iaChevronInd${idx}`);

        if (panel.classList.contains('show')) {
            panel.classList.remove('show');
            chevron.classList.remove('bi-chevron-up');
            chevron.classList.add('bi-chevron-down');
        } else {
            panel.classList.add('show');
            chevron.classList.remove('bi-chevron-down');
            chevron.classList.add('bi-chevron-up');
        }
    }

    function getIndicadorData(idx) {
        return {
            nombre: document.querySelector(`.ind-nombre[data-idx="${idx}"]`).value,
            formula: document.querySelector(`.ind-formula[data-idx="${idx}"]`).value,
            meta: parseInt(document.querySelector(`.ind-meta[data-idx="${idx}"]`).value) || 100,
            unidad: document.querySelector(`.ind-unidad[data-idx="${idx}"]`).value || '%',
            periodicidad: document.querySelector(`.ind-periodicidad[data-idx="${idx}"]`).value,
            tipo: document.querySelector(`.ind-tipo[data-idx="${idx}"]`).value,
            descripcion: document.querySelector(`.ind-descripcion[data-idx="${idx}"]`).value
        };
    }

    function regenerarIndicadorConIA(idx) {
        const instrucciones = document.querySelector(`.instrucciones-ia-ind[data-idx="${idx}"]`).value;
        const indActual = getIndicadorData(idx);

        if (!instrucciones.trim()) {
            showToast('info', 'Instrucciones', 'Escriba instrucciones para que la IA mejore este indicador');
            return;
        }

        const btn = event.target;
        const btnOriginal = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Regenerando...';

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/regenerar-indicador-capacitacion`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                indicador: indActual,
                instrucciones: instrucciones
            })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = btnOriginal;

            if (data.success && data.data) {
                const nuevo = data.data;
                document.querySelector(`.ind-nombre[data-idx="${idx}"]`).value = nuevo.nombre || indActual.nombre;
                document.querySelector(`.ind-formula[data-idx="${idx}"]`).value = nuevo.formula || indActual.formula;
                document.querySelector(`.ind-meta[data-idx="${idx}"]`).value = nuevo.meta || indActual.meta;
                document.querySelector(`.ind-unidad[data-idx="${idx}"]`).value = nuevo.unidad || indActual.unidad;
                if (nuevo.periodicidad) document.querySelector(`.ind-periodicidad[data-idx="${idx}"]`).value = nuevo.periodicidad;
                if (nuevo.tipo) document.querySelector(`.ind-tipo[data-idx="${idx}"]`).value = nuevo.tipo;
                if (nuevo.descripcion) document.querySelector(`.ind-descripcion[data-idx="${idx}"]`).value = nuevo.descripcion;

                // Actualizar datos en memoria
                indicadoresData[idx] = {...indicadoresData[idx], ...nuevo};

                showToast('success', 'Regenerado', 'El indicador fue mejorado por la IA');
                document.querySelector(`.instrucciones-ia-ind[data-idx="${idx}"]`).value = '';
            } else {
                showToast('error', 'Error', data.message || 'No se pudo regenerar');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = btnOriginal;
            showToast('error', 'Error', 'Error de conexion');
        });
    }

    function seleccionarTodosIndicadores(seleccionar) {
        document.querySelectorAll('.indicador-check:not(:disabled)').forEach(cb => {
            cb.checked = seleccionar;
        });
        actualizarContadorIndicadores();
    }

    function actualizarContadorIndicadores() {
        const total = document.querySelectorAll('.indicador-check:checked').length;
        document.getElementById('contadorIndicadores').textContent = `${total} indicadores seleccionados`;

        const btnGenerar = document.getElementById('btnGenerarIndicadores');
        if (total === 0) {
            btnGenerar.disabled = true;
            btnGenerar.innerHTML = '<i class="bi bi-graph-up me-1"></i>Seleccione indicadores';
        } else {
            btnGenerar.disabled = false;
            btnGenerar.innerHTML = `<i class="bi bi-graph-up me-1"></i>Generar ${total} Indicadores`;
        }
    }

    function generarIndicadoresSeleccionados() {
        const seleccionados = [];

        document.querySelectorAll('.indicador-check:checked').forEach(cb => {
            const idx = parseInt(cb.dataset.idx);

            // Leer valores editados de los campos
            const indEditado = getIndicadorData(idx);

            seleccionados.push({
                ...indicadoresData[idx],
                ...indEditado
            });
        });

        if (seleccionados.length === 0) {
            showAlert('warning', 'Seleccione al menos un indicador');
            return;
        }

        if (!confirm(`¿Generar ${seleccionados.length} indicadores?`)) return;

        const btn = document.getElementById('btnGenerarIndicadores');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generando...';

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/generar-indicadores-capacitacion`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                anio: anio,
                indicadores: seleccionados
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalPreviewIndicadores')).hide();

                const detalles = `<strong>${data.data?.creados || 0}</strong> indicadores creados<br>
                                  <strong>${data.data?.existentes || 0}</strong> ya existian`;
                showToast('success', 'Indicadores Generados', detalles);

                setTimeout(() => location.reload(), 2000);
            } else {
                showToast('error', 'Error', data.message);
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-graph-up me-1"></i>Generar ${seleccionados.length} Indicadores`;
            }
        })
        .catch(err => {
            showToast('error', 'Error de Conexion', 'No se pudo conectar con el servidor');
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-graph-up me-1"></i>Generar ${seleccionados.length} Indicadores`;
        });
    }
    </script>
</body>
</html>
