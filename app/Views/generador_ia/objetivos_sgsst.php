<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador Objetivos SG-SST - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-bullseye me-2"></i>Generador IA - Objetivos SG-SST
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Objetivos y Metas del SG-SST</h4>
                <p class="text-muted mb-0">Estandar 2.2.1 - Resolucion 0312/2019</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-info fs-6"><?= $anio ?></span>
                <span class="badge bg-secondary fs-6"><?= $contexto['estandares_aplicables'] ?? 60 ?> estandares</span>
                <a href="<?= base_url('documentos/generar/plan_objetivos_metas/' . $cliente['id_cliente']) ?>" class="btn btn-sm ms-2" style="background: linear-gradient(90deg, #667eea, #764ba2); color: white;">
                    <i class="bi bi-stars me-1"></i>Generar con IA
                </a>
            </div>
        </div>

        <!-- Alertas -->
        <div id="alertContainer"></div>

        <!-- Info del flujo de 3 partes -->
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Flujo de generacion del Plan de Objetivos y Metas:</strong>
            <ol class="mb-0 mt-2">
                <li><strong>Objetivos SG-SST</strong> - Se definen los objetivos medibles del sistema (esta fase)</li>
                <li><strong>Indicadores</strong> - Se configuran indicadores para medir cumplimiento de objetivos</li>
                <li><strong>Documento</strong> - Se genera el documento formal con datos de la BD</li>
            </ol>
        </div>

        <!-- Contexto del Cliente para la IA -->
        <?php
            $estandares = $contexto['estandares_aplicables'] ?? 60;
            $limiteObj = $estandares <= 7 ? 3 : ($estandares <= 21 ? 4 : 6);
            $riesgo = $contexto['nivel_riesgo_arl'] ?? 'N/A';
            $colorRiesgo = match($riesgo) {
                'I', 'II' => 'success',
                'III' => 'warning',
                'IV', 'V' => 'danger',
                default => 'secondary'
            };
            $peligros = [];
            if (!empty($contexto['peligros_identificados'])) {
                $peligros = json_decode($contexto['peligros_identificados'], true) ?: [];
            }
            $infraestructura = [];
            if (!empty($contexto['tiene_copasst'])) $infraestructura[] = 'COPASST';
            if (!empty($contexto['tiene_vigia_sst'])) $infraestructura[] = 'Vigia SST';
            if (!empty($contexto['tiene_comite_convivencia'])) $infraestructura[] = 'Comite Convivencia';
            if (!empty($contexto['tiene_brigada_emergencias'])) $infraestructura[] = 'Brigada Emergencias';
        ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-cpu text-primary me-2"></i>Contexto para la IA
                    <small class="text-muted ms-2">La IA usara toda esta informacion para generar objetivos personalizados</small>
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <a href="<?= base_url('contexto/' . $cliente['id_cliente']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Editar Contexto
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#collapseContexto">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
            </div>
            <div class="collapse show" id="collapseContexto">
                <div class="card-body">
                    <div class="row">
                        <!-- Datos del Cliente -->
                        <div class="col-md-4">
                            <h6 class="text-muted mb-3"><i class="bi bi-building me-1"></i>Datos de la Empresa</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width:45%">Actividad:</td>
                                    <td><strong><?= esc($contexto['actividad_economica_principal'] ?? 'No definida') ?></strong></td>
                                </tr>
                                <?php if (!empty($contexto['sector_economico'])): ?>
                                <tr>
                                    <td class="text-muted">Sector:</td>
                                    <td><?= esc($contexto['sector_economico']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="text-muted">Riesgo ARL:</td>
                                    <td><span class="badge bg-<?= $colorRiesgo ?>"><?= $riesgo ?></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Trabajadores:</td>
                                    <td><strong><?= $contexto['total_trabajadores'] ?? 'No definido' ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Estandares:</td>
                                    <td><span class="badge bg-info"><?= $estandares ?> est.</span> = <strong class="text-success"><?= $limiteObj ?> obj. max</strong></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Infraestructura SST -->
                        <div class="col-md-4">
                            <h6 class="text-muted mb-3"><i class="bi bi-shield-check me-1"></i>Infraestructura SST</h6>
                            <?php if (!empty($infraestructura)): ?>
                                <?php foreach ($infraestructura as $inf): ?>
                                    <span class="badge bg-success-subtle text-success me-1 mb-1"><i class="bi bi-check me-1"></i><?= $inf ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <small class="text-muted">Sin infraestructura registrada</small>
                            <?php endif; ?>

                            <?php if (!empty($contexto['responsable_sgsst_cargo'])): ?>
                            <div class="mt-2">
                                <small class="text-muted">Responsable SST:</small>
                                <br><small><strong><?= esc($contexto['responsable_sgsst_cargo']) ?></strong></small>
                            </div>
                            <?php endif; ?>

                            <div class="mt-2">
                                <small class="text-muted">Politica SST:</small>
                                <?php if (!empty($politicaSST)): ?>
                                    <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle me-1"></i>Documentada</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Pendiente</span>
                                <?php endif; ?>
                            </div>

                            <div class="mt-2">
                                <small class="text-muted d-block">Segun Res. 0312/2019:</small>
                                <small class="text-muted">7 est.=3 obj, 21 est.=4 obj, 60 est.=6 obj</small>
                            </div>
                        </div>

                        <!-- Peligros identificados -->
                        <div class="col-md-4">
                            <h6 class="text-muted mb-3"><i class="bi bi-exclamation-diamond me-1"></i>Peligros Identificados</h6>
                            <?php if (!empty($peligros)): ?>
                                <div style="max-height:120px; overflow-y:auto;">
                                    <?php foreach ($peligros as $peligro): ?>
                                        <span class="badge bg-danger-subtle text-danger me-1 mb-1" style="font-size:0.7rem;"><?= esc($peligro) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <small class="text-muted"><?= count($peligros) ?> peligro(s) registrado(s)</small>
                            <?php else: ?>
                                <div class="alert alert-warning small py-1 px-2 mb-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Sin peligros registrados.
                                    <a href="<?= base_url('contexto/' . $cliente['id_cliente']) ?>" target="_blank">Registrar</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($contexto['observaciones_contexto'])): ?>
                    <hr class="my-2">
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-2"><i class="bi bi-journal-text me-1"></i>Contexto y Observaciones</h6>
                            <div class="alert alert-light border small mb-0" style="max-height:100px; overflow-y:auto;">
                                <?= nl2br(esc($contexto['observaciones_contexto'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <hr class="my-3">

                    <!-- Instrucciones adicionales para la IA -->
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label">
                                <i class="bi bi-chat-dots me-1"></i>Instrucciones adicionales para la IA
                                <small class="text-muted">(opcional)</small>
                            </label>
                            <textarea id="instruccionesIA" class="form-control" rows="3"
                                placeholder="Ej: Enfocar objetivos en prevencion de riesgo psicosocial, incluir objetivo de bienestar laboral, la empresa tiene certificacion ISO 45001..."></textarea>
                            <small class="text-muted">
                                Describa necesidades especificas para personalizar los objetivos del SG-SST.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado y acciones de Objetivos -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-bullseye me-2"></i>Objetivos del SG-SST
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-white text-dark"><?= $resumenObjetivos['existentes'] ?> / <?= $limiteObj ?> objetivos</span>
                            <?php if ($resumenObjetivos['completo']): ?>
                                <span class="badge bg-light text-success"><i class="bi bi-check-circle me-1"></i>Fase completa</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <?php if (!$resumenObjetivos['completo']): ?>
                                    <div class="alert alert-warning small mb-0">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Minimo 3 objetivos requeridos. Genere objetivos medibles y cuantificables para el SG-SST.
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-0 small">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Objetivos definidos para <?= $anio ?>. Puede regenerar o agregar mas objetivos si lo necesita.
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-success" onclick="previewObjetivos()">
                                        <i class="bi bi-eye me-1"></i>Ver Preview
                                    </button>
                                    <button type="button" class="btn btn-success" onclick="generarObjetivos()">
                                        <i class="bi bi-magic me-1"></i>Generar Objetivos
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navegacion al siguiente paso -->
        <?php if ($resumenObjetivos['completo']): ?>
        <div class="d-flex justify-content-end mb-4">
            <a href="<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/indicadores-objetivos') ?>"
               class="btn btn-primary">
                Siguiente: Indicadores de Objetivos
                <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <?php endif; ?>

        <!-- Objetivos existentes -->
        <?php if (!empty($objetivosExistentes)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list-check me-2"></i>Objetivos Definidos (<?= count($objetivosExistentes) ?>)
                </h5>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminarTodos()">
                    <i class="bi bi-trash me-1"></i>Eliminar Todos
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Objetivo</th>
                                <th>Responsable</th>
                                <th>PHVA</th>
                                <th>Estado</th>
                                <th style="width:80px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($objetivosExistentes as $obj): ?>
                            <tr>
                                <td>
                                    <?php
                                    $partes = explode(' | Meta: ', $obj['actividad_plandetrabajo']);
                                    $titulo = $partes[0];
                                    $meta = $partes[1] ?? '';
                                    ?>
                                    <strong><?= esc($titulo) ?></strong>
                                    <?php if ($meta): ?>
                                        <small class="d-block text-muted">Meta: <?= esc($meta) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($obj['responsable_sugerido_plandetrabajo'] ?? 'Responsable SST') ?></td>
                                <td><span class="badge bg-secondary"><?= esc($obj['phva_plandetrabajo'] ?? 'PLANEAR') ?></span></td>
                                <td>
                                    <span class="badge bg-<?= $obj['estado_actividad'] == 'CERRADA' ? 'success' : 'warning' ?>">
                                        <?= esc($obj['estado_actividad']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="eliminarObjetivo(<?= $obj['id_ptacliente'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
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
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Seleccionar Objetivos SG-SST</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="previewContent">
                        <div class="text-center py-4">
                            <div class="spinner-border text-success"></div>
                            <p class="mt-2">Cargando objetivos...</p>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <div>
                            <span id="contadorSeleccion" class="text-muted">0 objetivos seleccionados</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success" id="btnGenerarSeleccionados" onclick="generarObjetivosSeleccionados()">
                                <i class="bi bi-send me-1"></i>Enviar al Plan de Trabajo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
        <div id="toastNotification" class="toast" role="alert">
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
    const limiteObjetivos = <?= $limiteObj ?>;
    let objetivosData = [];
    let explicacionIA = '';

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

    function previewObjetivos() {
        const modal = new bootstrap.Modal(document.getElementById('modalPreview'));
        modal.show();

        // Mostrar spinner mientras la IA genera los objetivos
        document.getElementById('previewContent').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-success mb-3" role="status" style="width:3rem;height:3rem;">
                    <span class="visually-hidden">Generando...</span>
                </div>
                <h6 class="text-muted">Generando objetivos personalizados con IA...</h6>
                <small class="text-muted">Analizando contexto de la empresa, peligros y observaciones</small>
            </div>`;

        const instrucciones = encodeURIComponent(getInstruccionesIA());
        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/preview-objetivos?anio=${anio}&instrucciones=${instrucciones}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (data.data.error) {
                        document.getElementById('previewContent').innerHTML =
                            `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>${data.data.explicacion_ia}</div>`;
                        return;
                    }
                    objetivosData = data.data.objetivos;
                    explicacionIA = data.data.explicacion_ia || '';
                    if (objetivosData.length === 0) {
                        document.getElementById('previewContent').innerHTML =
                            `<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>La IA no genero objetivos. Intente de nuevo o agregue instrucciones adicionales.</div>`;
                        return;
                    }
                    renderPreviewTable();
                } else {
                    document.getElementById('previewContent').innerHTML =
                        `<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>${data.message || 'Error al generar objetivos'}</div>`;
                }
            })
            .catch(err => {
                document.getElementById('previewContent').innerHTML =
                    `<div class="alert alert-danger"><i class="bi bi-wifi-off me-2"></i>Error de conexion con el servidor</div>`;
            });
    }

    function renderPreviewTable() {
        let explicacionHtml = '';
        if (explicacionIA) {
            explicacionHtml = `
                <div class="alert alert-success small mb-3">
                    <i class="bi bi-robot me-2"></i><strong>IA:</strong> ${explicacionIA}
                </div>`;
        }

        let html = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <strong>Total: ${objetivosData.length} objetivos generados por IA</strong>
                    <small class="text-muted ms-2">(limite: ${limiteObjetivos})</small>
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
                Seleccione los objetivos que desea incluir. Cada objetivo debe ser medible y tener una meta cuantificable.
            </div>`;

        objetivosData.forEach((obj, idx) => {
            const phvaColors = {
                'PLANEAR': 'primary',
                'HACER': 'success',
                'VERIFICAR': 'warning',
                'ACTUAR': 'danger'
            };

            const isIA = obj.generado_por_ia || obj.origen === 'ia';

            html += `
            <div class="card mb-3 objetivo-card border-start border-4 border-${phvaColors[obj.phva] || 'secondary'}" data-idx="${idx}">
                <div class="card-body py-3">
                    <div class="d-flex align-items-start">
                        <div class="form-check me-3 pt-1">
                            <input type="checkbox" class="form-check-input objetivo-check"
                                   data-idx="${idx}" checked onchange="actualizarContador()">
                        </div>
                        <div class="flex-grow-1">
                            <!-- Header con titulo y acciones -->
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1 me-2">
                                    <input type="text" class="form-control form-control-sm fw-bold objetivo-titulo"
                                           data-idx="${idx}" value="${escapeHtml(obj.objetivo)}"
                                           placeholder="Titulo del objetivo">
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-${phvaColors[obj.phva] || 'secondary'}">${obj.phva}</span>
                                    ${isIA ? '<span class="badge" style="background-color:#9c27b0">IA</span>' : ''}
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            onclick="toggleEditMode(${idx})" title="Editar/Colapsar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Descripcion -->
                            <div class="mb-2">
                                <textarea class="form-control form-control-sm objetivo-descripcion"
                                          data-idx="${idx}" rows="2"
                                          placeholder="Descripcion del objetivo">${escapeHtml(obj.descripcion || '')}</textarea>
                            </div>

                            <!-- Meta e Indicador -->
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-success text-white">
                                            <i class="bi bi-flag"></i>
                                        </span>
                                        <input type="text" class="form-control objetivo-meta" data-idx="${idx}"
                                               value="${escapeHtml(obj.meta || '')}" placeholder="Meta cuantificable">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="bi bi-graph-up"></i>
                                        </span>
                                        <input type="text" class="form-control objetivo-indicador" data-idx="${idx}"
                                               value="${escapeHtml(obj.indicador_sugerido || '')}" placeholder="Indicador sugerido">
                                    </div>
                                </div>
                            </div>

                            <!-- Responsable y PHVA -->
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">
                                            <i class="bi bi-person"></i>
                                        </span>
                                        <input type="text" class="form-control objetivo-responsable" data-idx="${idx}"
                                               value="${escapeHtml(obj.responsable || 'Responsable SST')}" placeholder="Responsable">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select form-select-sm objetivo-phva" data-idx="${idx}">
                                        <option value="PLANEAR" ${obj.phva === 'PLANEAR' ? 'selected' : ''}>PLANEAR</option>
                                        <option value="HACER" ${obj.phva === 'HACER' ? 'selected' : ''}>HACER</option>
                                        <option value="VERIFICAR" ${obj.phva === 'VERIFICAR' ? 'selected' : ''}>VERIFICAR</option>
                                        <option value="ACTUAR" ${obj.phva === 'ACTUAR' ? 'selected' : ''}>ACTUAR</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Seccion IA: Regenerar o Replantear -->
                            <div class="border-top pt-2 mt-2">
                                <div class="d-flex align-items-center justify-content-between">
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0"
                                            onclick="toggleIAPanel(${idx})">
                                        <i class="bi bi-robot me-1"></i>
                                        <small>Mejorar con IA</small>
                                        <i class="bi bi-chevron-down ms-1" id="iaChevron${idx}"></i>
                                    </button>
                                </div>
                                <div class="collapse mt-2" id="iaPanelObjetivo${idx}">
                                    <div class="card card-body bg-light border-0 p-2">
                                        <div class="mb-2">
                                            <textarea class="form-control form-control-sm instrucciones-ia-objetivo"
                                                      data-idx="${idx}" rows="3"
                                                      placeholder="Ej: Enfocarlo en riesgo biologico, agregar meta trimestral... O escriba el objetivo completo que desea"></textarea>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm flex-fill"
                                                    style="border:1px solid #9c27b0; color:#9c27b0;"
                                                    onclick="regenerarObjetivoConIA(${idx}, this, 'mejorar')">
                                                <i class="bi bi-magic me-1"></i>Regenerar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger flex-fill"
                                                    onclick="regenerarObjetivoConIA(${idx}, this, 'replantear')">
                                                <i class="bi bi-arrow-repeat me-1"></i>Replantear nuevo
                                            </button>
                                        </div>
                                        <small class="text-muted mt-1 d-block"><strong>Regenerar:</strong> mejora el actual &nbsp;|&nbsp; <strong>Replantear:</strong> lienzo en blanco</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        });

        document.getElementById('previewContent').innerHTML = html;
        actualizarContador();
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function toggleIAPanel(idx) {
        const panel = document.getElementById(`iaPanelObjetivo${idx}`);
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

    function toggleEditMode(idx) {
        const card = document.querySelector(`.objetivo-card[data-idx="${idx}"]`);
        card.classList.toggle('border-warning');
    }

    function regenerarObjetivoConIA(idx, btnElement, modo) {
        const instrucciones = document.querySelector(`.instrucciones-ia-objetivo[data-idx="${idx}"]`).value;
        const objetivoActual = getObjetivoData(idx);

        // Replantear no necesita instrucciones (lienzo en blanco)
        if (modo === 'mejorar' && !instrucciones.trim()) {
            showToast('info', 'Instrucciones', 'Escriba instrucciones para mejorar este objetivo');
            return;
        }

        // Deshabilitar AMBOS botones del panel
        const panel = document.getElementById(`iaPanelObjetivo${idx}`);
        const botones = panel.querySelectorAll('button');
        botones.forEach(b => b.disabled = true);

        const btn = btnElement;
        const btnOriginal = btn.innerHTML;
        const labelModo = modo === 'replantear' ? 'Replanteando...' : 'Regenerando...';
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>${labelModo}`;

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/regenerar-objetivo`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                objetivo_actual: objetivoActual,
                instrucciones: instrucciones,
                contexto_general: getInstruccionesIA(),
                modo: modo
            })
        })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            botones.forEach(b => b.disabled = false);
            btn.innerHTML = btnOriginal;

            if (data.success && data.data) {
                const nuevoObj = data.data;

                // Actualizar TODOS los campos
                if (nuevoObj.objetivo) document.querySelector(`.objetivo-titulo[data-idx="${idx}"]`).value = nuevoObj.objetivo;
                if (nuevoObj.descripcion) document.querySelector(`.objetivo-descripcion[data-idx="${idx}"]`).value = nuevoObj.descripcion;
                if (nuevoObj.meta) document.querySelector(`.objetivo-meta[data-idx="${idx}"]`).value = nuevoObj.meta;
                if (nuevoObj.indicador_sugerido) document.querySelector(`.objetivo-indicador[data-idx="${idx}"]`).value = nuevoObj.indicador_sugerido;
                if (nuevoObj.responsable) document.querySelector(`.objetivo-responsable[data-idx="${idx}"]`).value = nuevoObj.responsable;
                if (nuevoObj.phva) {
                    document.querySelector(`.objetivo-phva[data-idx="${idx}"]`).value = nuevoObj.phva;
                }

                // Actualizar objetivosData
                objetivosData[idx] = {...objetivosData[idx], ...nuevoObj, generado_por_ia: true};

                // Feedback visual
                const card = document.querySelector(`.objetivo-card[data-idx="${idx}"]`);
                const borderColor = modo === 'replantear' ? 'border-danger' : 'border-success';
                card.classList.add(borderColor);
                setTimeout(() => card.classList.remove(borderColor), 2500);

                // Limpiar textarea
                document.querySelector(`.instrucciones-ia-objetivo[data-idx="${idx}"]`).value = '';

                const msg = modo === 'replantear' ? 'Objetivo replanteado desde cero' : 'Objetivo mejorado por la IA';
                showToast('success', modo === 'replantear' ? 'Nuevo objetivo' : 'Objetivo mejorado', msg);
            } else {
                showToast('error', 'Error', data.message || 'No se pudo regenerar el objetivo');
                console.error('Regenerar error:', data);
            }
        })
        .catch(err => {
            botones.forEach(b => b.disabled = false);
            btn.innerHTML = btnOriginal;
            showToast('error', 'Error de conexion', err.message);
            console.error('Regenerar fetch error:', err);
        });
    }

    function getObjetivoData(idx) {
        return {
            objetivo: document.querySelector(`.objetivo-titulo[data-idx="${idx}"]`).value,
            descripcion: document.querySelector(`.objetivo-descripcion[data-idx="${idx}"]`).value,
            meta: document.querySelector(`.objetivo-meta[data-idx="${idx}"]`).value,
            indicador_sugerido: document.querySelector(`.objetivo-indicador[data-idx="${idx}"]`).value,
            responsable: document.querySelector(`.objetivo-responsable[data-idx="${idx}"]`).value,
            phva: document.querySelector(`.objetivo-phva[data-idx="${idx}"]`).value
        };
    }

    function seleccionarTodos(seleccionar) {
        document.querySelectorAll('.objetivo-check').forEach(cb => {
            cb.checked = seleccionar;
        });
        actualizarContador();
    }

    function actualizarContador() {
        const total = document.querySelectorAll('.objetivo-check:checked').length;
        document.getElementById('contadorSeleccion').textContent = `${total} objetivos seleccionados`;

        const btnGenerar = document.getElementById('btnGenerarSeleccionados');
        if (total === 0) {
            btnGenerar.disabled = true;
            btnGenerar.innerHTML = '<i class="bi bi-send me-1"></i>Seleccione objetivos';
        } else if (total > limiteObjetivos) {
            btnGenerar.disabled = true;
            btnGenerar.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>Excede limite (${limiteObjetivos})`;
        } else {
            btnGenerar.disabled = false;
            btnGenerar.innerHTML = `<i class="bi bi-send me-1"></i>Enviar ${total} al Plan de Trabajo`;
        }
    }

    function generarObjetivos() {
        previewObjetivos();
    }

    function generarObjetivosSeleccionados() {
        const seleccionados = [];

        // Obtener los valores editados de los campos, no los datos originales
        document.querySelectorAll('.objetivo-check:checked').forEach(cb => {
            const idx = parseInt(cb.dataset.idx);
            seleccionados.push(getObjetivoData(idx));
        });

        if (seleccionados.length === 0) {
            showAlert('warning', 'Seleccione al menos un objetivo');
            return;
        }

        if (seleccionados.length > limiteObjetivos) {
            showAlert('warning', `Maximo ${limiteObjetivos} objetivos permitidos segun estandares`);
            return;
        }

        if (!confirm(`¿Enviar ${seleccionados.length} objetivos al Plan de Trabajo?`)) return;

        const btn = document.getElementById('btnGenerarSeleccionados');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando...';

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/generar-objetivos`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                anio: anio,
                objetivos: seleccionados,
                instrucciones: getInstruccionesIA()
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalPreview')).hide();

                const detalles = `<strong>${data.data?.creados || 0}</strong> objetivos enviados al PTA<br>
                                  <strong>${data.data?.existentes || 0}</strong> ya existian`;
                showToast('success', 'Objetivos Enviados', detalles);

                setTimeout(() => location.reload(), 2000);
            } else {
                showToast('error', 'Error', data.message);
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-send me-1"></i>Enviar ${seleccionados.length} al Plan de Trabajo`;
            }
        })
        .catch(err => {
            showToast('error', 'Error de Conexion', 'No se pudo conectar con el servidor');
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-send me-1"></i>Enviar ${seleccionados.length} al Plan de Trabajo`;
        });
    }

    function eliminarObjetivo(idPta) {
        if (!confirm('Eliminar este objetivo?')) return;

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/eliminar-objetivo/${idPta}`, {
            method: 'DELETE'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Eliminado', 'Objetivo eliminado correctamente');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('error', 'Error', data.message);
            }
        });
    }

    function confirmarEliminarTodos() {
        if (!confirm('Eliminar TODOS los objetivos definidos? Esta accion no se puede deshacer.')) return;

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/eliminar-todos-objetivos?anio=${anio}`, {
            method: 'DELETE'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Eliminados', `${data.data?.eliminados || 0} objetivos eliminados`);
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('error', 'Error', data.message);
            }
        });
    }
    </script>
</body>
</html>
