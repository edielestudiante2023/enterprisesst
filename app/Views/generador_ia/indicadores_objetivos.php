<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indicadores Objetivos SG-SST - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-graph-up me-2"></i>Generador IA - Indicadores de Objetivos
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a href="<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/objetivos-sgsst') ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver a Objetivos
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Indicadores para Objetivos del SG-SST</h4>
                <p class="text-muted mb-0">Estandar 2.2.1 - Resolucion 0312/2019</p>
            </div>
            <div>
                <span class="badge bg-info fs-6"><?= $anio ?></span>
                <span class="badge bg-secondary fs-6"><?= $contexto['estandares_aplicables'] ?? 60 ?> estandares</span>
            </div>
        </div>

        <!-- Alertas -->
        <div id="alertContainer"></div>

        <!-- Verificacion de objetivos previos -->
        <?php if (!$verificacionObjetivos['tiene_objetivos']): ?>
        <div class="alert alert-danger mb-4">
            <i class="bi bi-exclamation-octagon me-2"></i>
            <strong>Parte 1 incompleta:</strong> <?= esc($verificacionObjetivos['mensaje']) ?>
            <a href="<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/objetivos-sgsst') ?>" class="btn btn-sm btn-danger ms-3">
                <i class="bi bi-arrow-left me-1"></i>Ir a Objetivos
            </a>
        </div>
        <?php else: ?>

        <!-- Info del flujo -->
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Indicadores para medir los Objetivos del SG-SST:</strong>
            La IA generara indicadores a partir de los <strong><?= $verificacionObjetivos['total_objetivos'] ?></strong> objetivos definidos en la Parte 1.
        </div>

        <!-- Contexto de la Empresa para la IA -->
        <?php
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
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0">
                    <i class="bi bi-cpu text-primary me-2"></i>Contexto de la Empresa
                </h6>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#collapseContexto">
                    <i class="bi bi-eye me-1"></i>Ver contexto IA
                </button>
            </div>
            <div class="collapse" id="collapseContexto">
                <div class="card-body">
                    <div class="row">
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
                                    <td><span class="badge bg-info"><?= $contexto['estandares_aplicables'] ?? 60 ?> est.</span></td>
                                </tr>
                            </table>
                        </div>
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
                        </div>
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
                </div>
            </div>
        </div>

        <!-- Instrucciones adicionales para la IA -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="bi bi-chat-dots text-primary me-2"></i>Instrucciones adicionales para la IA
                    <small class="text-muted">(opcional)</small>
                </h6>
            </div>
            <div class="card-body">
                <textarea id="instruccionesIA" class="form-control" rows="3"
                    placeholder="Ej: Enfocar indicadores en prevencion de riesgo psicosocial, incluir indicadores de resultado con metas trimestrales, la empresa tiene certificacion ISO 45001..."></textarea>
                <small class="text-muted">
                    Describa necesidades especificas para personalizar los indicadores del SG-SST.
                </small>
            </div>
        </div>

        <div class="row">
            <!-- Panel Principal: Indicadores -->
            <div class="col-md-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up me-2"></i>Indicadores de Objetivos
                        </h5>
                        <span class="badge bg-light text-primary"><?= $resumenIndicadores['existentes'] ?> configurados</span>
                    </div>
                    <div class="card-body">
                        <!-- Estado actual -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <h3 class="mb-0 text-primary"><?= $resumenIndicadores['existentes'] ?></h3>
                                    <small class="text-muted">Indicadores existentes</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <h3 class="mb-0 text-info"><?= $limiteIndicadores ?></h3>
                                    <small class="text-muted">Limite segun estandares</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <h3 class="mb-0 <?= $resumenIndicadores['completo'] ? 'text-success' : 'text-warning' ?>"><?= $resumenIndicadores['minimo'] ?></h3>
                                    <small class="text-muted">Minimo requerido</small>
                                </div>
                            </div>
                        </div>

                        <?php if ($resumenIndicadores['completo']): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>Fase completa:</strong> Ya tiene <?= $resumenIndicadores['existentes'] ?> indicadores de objetivos configurados.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Faltan indicadores:</strong> Se requieren minimo <?= $resumenIndicadores['minimo'] ?> indicadores.
                            </div>
                        <?php endif; ?>

                        <!-- Botones -->
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="previewIndicadores()">
                                <i class="bi bi-eye me-1"></i>Ver Preview de Indicadores
                            </button>
                            <button type="button" class="btn btn-primary" onclick="previewIndicadores()">
                                <i class="bi bi-magic me-1"></i>Generar Indicadores con IA
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral: Objetivos del cliente -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bi bi-bullseye me-2"></i>Objetivos Definidos (Parte 1)</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($verificacionObjetivos['objetivos'] as $obj): ?>
                            <li class="list-group-item">
                                <small>
                                    <?php
                                    $partes = explode(' - ', $obj['actividad_plandetrabajo']);
                                    echo esc($partes[0]);
                                    ?>
                                </small>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Contexto del cliente -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Contexto</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted">Riesgo ARL:</td>
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
                                <td class="text-muted">Trabajadores:</td>
                                <td><?= $contexto['total_trabajadores'] ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Estandares:</td>
                                <td><span class="badge bg-info"><?= $contexto['estandares_aplicables'] ?? 60 ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Ir al documento -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body">
                        <a href="<?= base_url('documentos/generar/plan_objetivos_metas/' . $cliente['id_cliente']) ?>"
                           class="btn btn-<?= ($resumenIndicadores['completo']) ? 'success' : 'secondary' ?> w-100"
                           <?= !$resumenIndicadores['completo'] ? 'onclick="alert(\'Primero genere los indicadores\'); return false;"' : '' ?>>
                            <i class="bi bi-magic me-1"></i>Generar Documento con IA (Parte 3)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicadores existentes -->
        <?php if (!empty($indicadoresExistentes)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-list-check me-2"></i>Indicadores de Objetivos Configurados (<?= count($indicadoresExistentes) ?>)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Indicador</th>
                                <th>Tipo</th>
                                <th>Meta</th>
                                <th>Periodicidad</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($indicadoresExistentes as $ind): ?>
                            <tr>
                                <td>
                                    <?= esc($ind['nombre_indicador']) ?>
                                    <small class="d-block text-muted"><?= esc($ind['formula'] ?? '') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $ind['tipo_indicador'] == 'resultado' ? 'danger' : ($ind['tipo_indicador'] == 'proceso' ? 'primary' : 'secondary') ?>">
                                        <?= ucfirst(esc($ind['tipo_indicador'])) ?>
                                    </span>
                                </td>
                                <td><?= $ind['meta'] ?><?= esc($ind['unidad_medida']) ?></td>
                                <td><?= ucfirst(esc($ind['periodicidad'])) ?></td>
                                <td>
                                    <?php if ($ind['cumple_meta'] === null): ?>
                                        <span class="badge bg-secondary">Sin medir</span>
                                    <?php elseif ($ind['cumple_meta'] == 1): ?>
                                        <span class="badge bg-success">Cumple</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">No cumple</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php endif; // fin if tiene_objetivos ?>

        <!-- Modal Preview -->
        <div class="modal fade" id="modalPreview" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Seleccionar Indicadores de Objetivos</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="previewContent">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary"></div>
                            <p class="mt-2">Cargando indicadores...</p>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <div>
                            <span id="contadorSeleccion" class="text-muted">0 indicadores seleccionados</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="btnGenerarSeleccionados" onclick="generarIndicadoresSeleccionados()">
                                <i class="bi bi-check-circle me-1"></i>Confirmar Seleccionados
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
    const limiteIndicadores = <?= $limiteIndicadores ?? 10 ?>;
    let indicadoresData = [];
    let explicacionIA = '';

    const tiposColor = {
        'estructura': 'secondary',
        'proceso': 'primary',
        'resultado': 'danger'
    };

    const periodicidades = {
        'mensual': 'Mensual',
        'trimestral': 'Trimestral',
        'semestral': 'Semestral',
        'anual': 'Anual'
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
        } else {
            toastIcon.className = 'bi bi-info-circle-fill text-primary me-2';
            toast.classList.add('border-primary');
        }

        toastTitle.textContent = title;
        toastBody.innerHTML = message;

        const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
        bsToast.show();
    }

    function getInstruccionesIA() {
        const textarea = document.getElementById('instruccionesIA');
        return textarea ? textarea.value.trim() : '';
    }

    function previewIndicadores() {
        const modal = new bootstrap.Modal(document.getElementById('modalPreview'));
        modal.show();

        // Mostrar spinner de carga con mensaje IA
        document.getElementById('previewContent').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2">La IA esta generando indicadores a partir de los objetivos...</p>
                <small class="text-muted">Esto puede tardar unos segundos</small>
            </div>`;

        const instrucciones = encodeURIComponent(getInstruccionesIA());
        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/preview-indicadores-objetivos?anio=${anio}&instrucciones=${instrucciones}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (data.data.error) {
                        document.getElementById('previewContent').innerHTML =
                            `<div class="alert alert-danger">${data.data.mensaje}</div>`;
                        return;
                    }
                    indicadoresData = data.data.indicadores;
                    explicacionIA = data.data.explicacion_ia || '';
                    renderPreviewTable();
                } else {
                    document.getElementById('previewContent').innerHTML =
                        `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(err => {
                document.getElementById('previewContent').innerHTML =
                    `<div class="alert alert-danger">Error de conexion</div>`;
            });
    }

    function renderPreviewTable() {
        let explicacionHtml = '';
        if (explicacionIA) {
            explicacionHtml = `
                <div class="alert alert-success small mb-3">
                    <i class="bi bi-robot me-2"></i><strong>Criterio de la IA:</strong> ${explicacionIA}
                </div>`;
        }

        let html = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <strong>Total: ${indicadoresData.length} indicadores generados por IA</strong>
                    <small class="text-muted ms-2">(limite: ${limiteIndicadores})</small>
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
            <div class="alert alert-light small border mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Indicadores generados a partir de los objetivos de la Parte 1. Edite y seleccione los que desea crear.
                <div class="mt-2">
                    <span class="badge bg-secondary me-1">Estructura</span> Miden recursos
                    <span class="badge bg-primary ms-2 me-1">Proceso</span> Miden ejecucion
                    <span class="badge bg-danger ms-2 me-1">Resultado</span> Miden impacto
                </div>
            </div>`;

        indicadoresData.forEach((ind, idx) => {
            const yaExiste = ind.ya_existe || false;
            const disabled = yaExiste ? 'disabled' : '';
            const checked = ind.seleccionado && !yaExiste ? 'checked' : '';
            const cardClass = yaExiste ? 'bg-light opacity-75' : '';
            const tipoColor = tiposColor[ind.tipo] || 'secondary';

            html += `
            <div class="card mb-3 indicador-card border-start border-4 border-${tipoColor} ${cardClass}" data-idx="${idx}">
                <div class="card-body py-3">
                    <div class="d-flex align-items-start">
                        <div class="form-check me-3 pt-1">
                            <input type="checkbox" class="form-check-input indicador-check"
                                   data-idx="${idx}" ${checked} ${disabled} onchange="actualizarContador()">
                        </div>
                        <div class="flex-grow-1">
                            <!-- Header con nombre y tipo -->
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1 me-2">
                                    <input type="text" class="form-control form-control-sm fw-bold indicador-nombre"
                                           data-idx="${idx}" value="${escapeHtml(ind.nombre)}"
                                           placeholder="Nombre del indicador" ${disabled}>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-${tipoColor}">${ind.tipo}</span>
                                    ${yaExiste ? '<span class="badge bg-secondary">Ya existe</span>' : ''}
                                </div>
                            </div>

                            <!-- Formula -->
                            <div class="mb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="bi bi-calculator"></i></span>
                                    <input type="text" class="form-control indicador-formula" data-idx="${idx}"
                                           value="${escapeHtml(ind.formula || '')}" placeholder="Formula del indicador" ${disabled}>
                                </div>
                            </div>

                            <!-- Descripcion -->
                            <div class="mb-2">
                                <textarea class="form-control form-control-sm indicador-descripcion"
                                          data-idx="${idx}" rows="2"
                                          placeholder="Descripcion del indicador" ${disabled}>${escapeHtml(ind.descripcion || '')}</textarea>
                            </div>

                            <!-- Meta, Unidad, Periodicidad, Tipo -->
                            <div class="row g-2 mb-2">
                                <div class="col-md-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-success text-white">Meta</span>
                                        <input type="text" class="form-control indicador-meta" data-idx="${idx}"
                                               value="${escapeHtml(ind.meta || '')}" ${disabled}>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Unidad</span>
                                        <input type="text" class="form-control indicador-unidad" data-idx="${idx}"
                                               value="${escapeHtml(ind.unidad || '%')}" ${disabled}>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm indicador-tipo" data-idx="${idx}" ${disabled}>
                                        <option value="estructura" ${ind.tipo === 'estructura' ? 'selected' : ''}>Estructura</option>
                                        <option value="proceso" ${ind.tipo === 'proceso' ? 'selected' : ''}>Proceso</option>
                                        <option value="resultado" ${ind.tipo === 'resultado' ? 'selected' : ''}>Resultado</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm indicador-periodicidad" data-idx="${idx}" ${disabled}>
                                        <option value="mensual" ${ind.periodicidad === 'mensual' ? 'selected' : ''}>Mensual</option>
                                        <option value="trimestral" ${ind.periodicidad === 'trimestral' ? 'selected' : ''}>Trimestral</option>
                                        <option value="semestral" ${ind.periodicidad === 'semestral' ? 'selected' : ''}>Semestral</option>
                                        <option value="anual" ${ind.periodicidad === 'anual' ? 'selected' : ''}>Anual</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Objetivo origen (vinculo a Parte 1) -->
                            ${ind.objetivo_origen ? `
                            <div class="small text-success mb-2">
                                <i class="bi bi-bullseye me-1"></i>Mide objetivo: ${escapeHtml(ind.objetivo_origen.substring(0,100))}
                            </div>` : ''}

                            <!-- Seccion IA: Instrucciones para regenerar -->
                            ${!yaExiste ? `
                            <div class="border-top pt-2 mt-2">
                                <div class="d-flex align-items-center justify-content-between">
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0"
                                            onclick="toggleIAPanel(${idx})">
                                        <i class="bi bi-robot me-1"></i>
                                        <small>Mejorar con IA</small>
                                        <i class="bi bi-chevron-down ms-1" id="iaChevron${idx}"></i>
                                    </button>
                                </div>
                                <div class="collapse mt-2" id="iaPanelIndicador${idx}">
                                    <div class="card card-body bg-light border-0 p-2">
                                        <div class="mb-2">
                                            <textarea class="form-control form-control-sm instrucciones-ia-indicador"
                                                      data-idx="${idx}" rows="2"
                                                      placeholder="Ej: Ajusta la meta a 95%, cambia periodicidad a trimestral, hazlo mas especifico para construccion..."></textarea>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-purple w-100"
                                                style="border-color:#9c27b0; color:#9c27b0;"
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
        const panel = document.getElementById(`iaPanelIndicador${idx}`);
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

    function getIndicadorData(idx) {
        // Combinar valores editados con datos originales que no son editables
        const original = indicadoresData[idx] || {};
        return {
            nombre: document.querySelector(`.indicador-nombre[data-idx="${idx}"]`).value,
            formula: document.querySelector(`.indicador-formula[data-idx="${idx}"]`).value,
            descripcion: document.querySelector(`.indicador-descripcion[data-idx="${idx}"]`).value,
            meta: document.querySelector(`.indicador-meta[data-idx="${idx}"]`).value,
            unidad: document.querySelector(`.indicador-unidad[data-idx="${idx}"]`).value,
            tipo: document.querySelector(`.indicador-tipo[data-idx="${idx}"]`).value,
            periodicidad: document.querySelector(`.indicador-periodicidad[data-idx="${idx}"]`).value,
            // Campos originales que no son editables en la UI
            phva: original.phva || 'verificar',
            numeral: original.numeral || '2.2.1',
            definicion: original.definicion || '',
            interpretacion: original.interpretacion || '',
            origen_datos: original.origen_datos || '',
            cargo_responsable: original.cargo_responsable || 'Responsable del SG-SST',
            cargos_conocer_resultado: original.cargos_conocer_resultado || '',
            objetivo_origen: original.objetivo_origen || ''
        };
    }

    function regenerarIndicadorConIA(idx) {
        const instrucciones = document.querySelector(`.instrucciones-ia-indicador[data-idx="${idx}"]`).value;
        const indicadorActual = getIndicadorData(idx);

        if (!instrucciones.trim()) {
            showToast('info', 'Instrucciones', 'Escriba instrucciones para que la IA mejore este indicador');
            return;
        }

        const btn = event.target;
        const btnOriginal = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Regenerando...';

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/regenerar-indicador`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                indicador_actual: indicadorActual,
                instrucciones: instrucciones,
                tipo_indicador: 'objetivos_sgsst'
            })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = btnOriginal;

            if (data.success && data.data) {
                const nuevoInd = data.data;
                document.querySelector(`.indicador-nombre[data-idx="${idx}"]`).value = nuevoInd.nombre || indicadorActual.nombre;
                document.querySelector(`.indicador-formula[data-idx="${idx}"]`).value = nuevoInd.formula || indicadorActual.formula;
                document.querySelector(`.indicador-descripcion[data-idx="${idx}"]`).value = nuevoInd.descripcion || indicadorActual.descripcion;
                document.querySelector(`.indicador-meta[data-idx="${idx}"]`).value = nuevoInd.meta || indicadorActual.meta;
                document.querySelector(`.indicador-unidad[data-idx="${idx}"]`).value = nuevoInd.unidad || indicadorActual.unidad;
                if (nuevoInd.tipo) document.querySelector(`.indicador-tipo[data-idx="${idx}"]`).value = nuevoInd.tipo;
                if (nuevoInd.periodicidad) document.querySelector(`.indicador-periodicidad[data-idx="${idx}"]`).value = nuevoInd.periodicidad;

                // Actualizar indicadoresData
                indicadoresData[idx] = {...indicadoresData[idx], ...nuevoInd};

                // Feedback visual
                const card = document.querySelector(`.indicador-card[data-idx="${idx}"]`);
                card.classList.add('border-success');
                setTimeout(() => card.classList.remove('border-success'), 2000);

                showToast('success', 'Indicador mejorado', 'La IA ha actualizado el indicador');
            } else {
                showToast('error', 'Error', data.message || 'No se pudo regenerar el indicador');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = btnOriginal;
            showToast('error', 'Error de conexion', 'No se pudo conectar con el servidor');
        });
    }

    function seleccionarTodos(seleccionar) {
        document.querySelectorAll('.indicador-check:not(:disabled)').forEach(cb => {
            cb.checked = seleccionar;
        });
        actualizarContador();
    }

    function actualizarContador() {
        const total = document.querySelectorAll('.indicador-check:checked').length;
        document.getElementById('contadorSeleccion').textContent = `${total} indicadores seleccionados`;

        const btnGenerar = document.getElementById('btnGenerarSeleccionados');
        if (total === 0) {
            btnGenerar.disabled = true;
            btnGenerar.innerHTML = '<i class="bi bi-check-circle me-1"></i>Seleccione indicadores';
        } else if (total > limiteIndicadores) {
            btnGenerar.disabled = true;
            btnGenerar.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>Excede limite (${limiteIndicadores})`;
        } else {
            btnGenerar.disabled = false;
            btnGenerar.innerHTML = `<i class="bi bi-check-circle me-1"></i>Confirmar ${total} Indicadores`;
        }
    }

    function generarIndicadoresSeleccionados() {
        const seleccionados = [];

        // Obtener los valores editados de los campos, no los datos originales
        document.querySelectorAll('.indicador-check:checked').forEach(cb => {
            const idx = parseInt(cb.dataset.idx);
            seleccionados.push(getIndicadorData(idx));
        });

        if (seleccionados.length === 0) {
            showToast('warning', 'Atencion', 'Seleccione al menos un indicador');
            return;
        }

        if (seleccionados.length > limiteIndicadores) {
            showToast('warning', 'Atencion', `Maximo ${limiteIndicadores} indicadores permitidos`);
            return;
        }

        if (!confirm(`¿Confirmar ${seleccionados.length} indicadores de objetivos?`)) return;

        const btn = document.getElementById('btnGenerarSeleccionados');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/generar-indicadores-objetivos`, {
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
                bootstrap.Modal.getInstance(document.getElementById('modalPreview')).hide();

                const detalles = `<strong>${data.data?.creados || 0}</strong> indicadores guardados<br>
                                  <strong>${data.data?.existentes || 0}</strong> ya existian`;
                showToast('success', 'Indicadores Guardados', detalles);

                setTimeout(() => location.reload(), 2000);
            } else {
                showToast('error', 'Error', data.message);
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-check-circle me-1"></i>Confirmar ${seleccionados.length} Indicadores`;
            }
        })
        .catch(err => {
            showToast('error', 'Error de Conexion', 'No se pudo conectar con el servidor');
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-check-circle me-1"></i>Confirmar ${seleccionados.length} Indicadores`;
        });
    }
    </script>
</body>
</html>
