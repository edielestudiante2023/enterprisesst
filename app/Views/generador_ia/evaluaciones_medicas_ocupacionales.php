<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador Evaluaciones Medicas Ocupacionales - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-clipboard2-pulse me-2"></i>Generador IA - Evaluaciones Medicas Ocupacionales
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
                <h4 class="mb-1">Programa de Evaluaciones Medicas Ocupacionales</h4>
                <p class="text-muted mb-0">Estandar 3.1.4 - Resolucion 0312/2019</p>
            </div>
            <div>
                <span class="badge bg-info fs-6"><?= $anio ?></span>
            </div>
        </div>

        <!-- Alertas -->
        <div id="alertContainer"></div>

        <!-- Info del flujo -->
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Flujo de generacion del Programa de Evaluaciones Medicas Ocupacionales:</strong>
            <ol class="mb-0 mt-2">
                <li><strong>Actividades Evaluaciones Medicas Ocupacionales</strong> - Se generan actividades de evaluaciones medicas ocupacionales segun peligros, frecuencia acorde a riesgos, comunicacion de resultados y articulacion con PVE en el PTA</li>
                <li><strong>Indicadores Evaluaciones Medicas Ocupacionales</strong> - Se configuran indicadores para medir el programa</li>
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
                                placeholder="Ej: Incluir evaluaciones medicas especificas para trabajo en alturas, enfocarse en examenes periodicos segun nivel de riesgo, agregar profesiogramas..."></textarea>
                            <small class="text-muted">
                                Describa necesidades especificas de la empresa para personalizar las actividades de Evaluaciones Medicas Ocupacionales.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Paso 1: Actividades Evaluaciones Medicas Ocupacionales -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <span class="badge bg-white text-success me-2">1</span>
                            Actividades de Evaluaciones Medicas Ocupacionales
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Estado actual -->
                        <div class="mb-3">
                            <p class="text-muted small mb-2">Estado actual (<?= $anio ?>):</p>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Actividades existentes:</span>
                                <strong><?= $resumenActividades['existentes'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Actividades sugeridas:</span>
                                <strong><?= $resumenActividades['sugeridas'] ?></strong>
                            </div>
                            <?php if ($resumenActividades['completo']): ?>
                                <div class="alert alert-success small mb-0 mt-2">
                                    <i class="bi bi-check-circle me-1"></i>Fase completa
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning small mb-0 mt-2">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Minimo 5 actividades requeridas
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Que se generara -->
                        <div class="alert alert-light small mb-3">
                            <strong>Se generaran actividades como:</strong>
                            <ul class="mb-0 mt-1">
                                <li>Evaluaciones medicas de ingreso, periodicas y de retiro</li>
                                <li>Examenes medicos segun peligros identificados</li>
                                <li>Comunicacion de resultados a trabajadores</li>
                                <li>Articulacion con Programas de Vigilancia Epidemiologica (PVE)</li>
                                <li>Seguimiento a restricciones y recomendaciones medicas</li>
                            </ul>
                        </div>

                        <!-- Botones -->
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-success" onclick="previewActividades()">
                                <i class="bi bi-eye me-1"></i>Ver Preview
                            </button>
                            <button type="button" class="btn btn-success" onclick="generarActividades()">
                                <i class="bi bi-magic me-1"></i>Generar Actividades
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
                            Indicadores de Evaluaciones Medicas Ocupacionales
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Estado actual -->
                        <div class="mb-3">
                            <p class="text-muted small mb-2">Estado actual:</p>
                            <?php if ($resumenActividades['completo']): ?>
                                <div class="alert alert-success small mb-0 mt-2">
                                    <i class="bi bi-check-circle me-1"></i>Actividades completas - puede configurar indicadores
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning small mb-0 mt-2">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Primero genere las actividades de Evaluaciones Medicas Ocupacionales
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Indicadores sugeridos -->
                        <div class="alert alert-light small mb-3">
                            <strong>Indicadores recomendados:</strong>
                            <ul class="mb-0 mt-1">
                                <li>Cumplimiento de evaluaciones medicas programadas</li>
                                <li>Cobertura de examenes medicos ocupacionales</li>
                                <li>Seguimiento a recomendaciones medico-laborales</li>
                                <li>Oportunidad en comunicacion de resultados</li>
                            </ul>
                        </div>

                        <!-- Boton -->
                        <div class="d-grid">
                            <a href="<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/indicadores-evaluaciones-medicas') ?>"
                               class="btn btn-<?= $resumenActividades['completo'] ? 'primary' : 'secondary' ?>"
                               <?= !$resumenActividades['completo'] ? 'onclick="alert(\'Primero genere las actividades de Evaluaciones Medicas Ocupacionales\'); return false;"' : '' ?>>
                                <i class="bi bi-graph-up me-1"></i>Ir a Indicadores
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actividades existentes -->
        <?php if (!empty($actividadesExistentes)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-list-check me-2"></i>Actividades de Evaluaciones Medicas Ocupacionales en el PTA (<?= count($actividadesExistentes) ?>)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Actividad</th>
                                <th>Responsable</th>
                                <th>Fecha</th>
                                <th>PHVA</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($actividadesExistentes as $act): ?>
                            <tr>
                                <td><?= esc($act['actividad_plandetrabajo']) ?></td>
                                <td><?= esc($act['responsable_sugerido_plandetrabajo'] ?? 'Responsable SST') ?></td>
                                <td><?= date('M Y', strtotime($act['fecha_propuesta'])) ?></td>
                                <td><span class="badge bg-secondary"><?= esc($act['phva_plandetrabajo'] ?? 'HACER') ?></span></td>
                                <td>
                                    <span class="badge bg-<?= $act['estado_actividad'] == 'CERRADA' ? 'success' : 'warning' ?>">
                                        <?= esc($act['estado_actividad']) ?>
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
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Seleccionar Actividades Evaluaciones Medicas Ocupacionales</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="previewContent">
                        <div class="text-center py-4">
                            <div class="spinner-border text-success"></div>
                            <p class="mt-2">Cargando actividades...</p>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <div>
                            <span id="contadorSeleccion" class="text-muted">0 actividades seleccionadas</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success" id="btnGenerarSeleccionadas" onclick="generarActividadesSeleccionadas()">
                                <i class="bi bi-magic me-1"></i>Generar Seleccionadas
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
    let actividadesData = [];
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

        // Configurar icono y colores segun tipo
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

    function previewActividades() {
        const modal = new bootstrap.Modal(document.getElementById('modalPreview'));
        modal.show();

        const instrucciones = encodeURIComponent(getInstruccionesIA());
        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/preview-actividades-evaluaciones-medicas?anio=${anio}&instrucciones=${instrucciones}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    actividadesData = data.data.actividades;
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
        // Mostrar explicacion de la IA si existe
        let explicacionHtml = '';
        if (explicacionIA) {
            explicacionHtml = `
                <div class="alert alert-success small mb-3">
                    <i class="bi bi-robot me-2"></i><strong>IA aplico cambios:</strong> ${explicacionIA}
                </div>`;
        }

        let html = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <strong>Total: ${actividadesData.length} actividades sugeridas para ${anio}</strong>
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
                Seleccione las actividades que desea incluir y ajuste el mes si es necesario.
                <div class="mt-2">
                    <span class="badge bg-secondary me-1">Base</span> Actividades estandar
                    <span class="badge bg-warning text-dark ms-2 me-1">Peligro</span> Segun peligros identificados
                    <span class="badge bg-purple text-white ms-2 me-1" style="background-color:#9c27b0">IA</span> Generado/modificado por IA
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px">
                                <input type="checkbox" class="form-check-input" id="checkAll" onchange="seleccionarTodos(this.checked)" checked>
                            </th>
                            <th style="width:130px">Mes</th>
                            <th>Actividad</th>
                            <th style="width:150px">Responsable</th>
                            <th style="width:90px">PHVA</th>
                            <th style="width:80px">Origen</th>
                        </tr>
                    </thead>
                    <tbody>`;

        actividadesData.forEach((act, idx) => {
            const phvaColors = {
                'PLANEAR': 'primary',
                'HACER': 'success',
                'VERIFICAR': 'warning',
                'ACTUAR': 'danger'
            };

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

            // Determinar origen (priorizar IA si fue generado o modificado)
            let origen = act.origen || 'base';
            if (act.generado_por_ia || act.modificada_por_ia) {
                origen = 'ia';
            }

            const origenStyle = origen === 'ia' ? 'style="background-color:#9c27b0"' : '';
            const origenBadge = `<span class="badge bg-${origenColors[origen]} ${origen === 'peligro' ? 'text-dark' : ''}" ${origenStyle}>${origenLabels[origen]}</span>`;

            html += `
                <tr class="actividad-row" data-idx="${idx}">
                    <td>
                        <input type="checkbox" class="form-check-input actividad-check"
                               data-idx="${idx}" checked onchange="actualizarContador()">
                    </td>
                    <td>
                        <select class="form-select form-select-sm mes-select" data-idx="${idx}">`;

            for (let m = 1; m <= 12; m++) {
                const selected = m === act.mes_num ? 'selected' : '';
                html += `<option value="${m}" ${selected}>${meses[m]}</option>`;
            }

            html += `   </select>
                    </td>
                    <td>
                        <span class="actividad-nombre">${act.actividad}</span>
                        <small class="d-block text-muted">${act.objetivo || ''}</small>
                        ${act.peligro_relacionado ? `<small class="d-block text-warning"><i class="bi bi-exclamation-triangle me-1"></i>${act.peligro_relacionado}</small>` : ''}
                        ${act.razon_modificacion ? `<small class="d-block text-purple" style="color:#9c27b0"><i class="bi bi-robot me-1"></i>${act.razon_modificacion}</small>` : ''}
                        ${act.generado_por_ia ? `<small class="d-block text-purple" style="color:#9c27b0"><i class="bi bi-robot me-1"></i>Sugerido por IA</small>` : ''}
                    </td>
                    <td><small>${act.responsable}</small></td>
                    <td><span class="badge bg-${phvaColors[act.phva] || 'secondary'}">${act.phva}</span></td>
                    <td>${origenBadge}</td>
                </tr>`;
        });

        html += `</tbody></table></div>`;
        document.getElementById('previewContent').innerHTML = html;
        actualizarContador();
    }

    function seleccionarTodos(seleccionar) {
        document.querySelectorAll('.actividad-check').forEach(cb => {
            cb.checked = seleccionar;
        });
        document.getElementById('checkAll').checked = seleccionar;
        actualizarContador();
    }

    function actualizarContador() {
        const total = document.querySelectorAll('.actividad-check:checked').length;
        document.getElementById('contadorSeleccion').textContent = `${total} actividades seleccionadas`;

        const btnGenerar = document.getElementById('btnGenerarSeleccionadas');
        if (total === 0) {
            btnGenerar.disabled = true;
            btnGenerar.innerHTML = '<i class="bi bi-magic me-1"></i>Seleccione actividades';
        } else {
            btnGenerar.disabled = false;
            btnGenerar.innerHTML = `<i class="bi bi-magic me-1"></i>Generar ${total} Actividades`;
        }
    }

    function generarActividades() {
        // Abre el modal de preview para seleccionar
        previewActividades();
    }

    function generarActividadesSeleccionadas() {
        const seleccionadas = [];

        document.querySelectorAll('.actividad-check:checked').forEach(cb => {
            const idx = parseInt(cb.dataset.idx);
            const mesSelect = document.querySelector(`.mes-select[data-idx="${idx}"]`);
            const mesNuevo = parseInt(mesSelect.value);

            seleccionadas.push({
                ...actividadesData[idx],
                mes_num: mesNuevo,
                mes: meses[mesNuevo]
            });
        });

        if (seleccionadas.length === 0) {
            showAlert('warning', 'Seleccione al menos una actividad');
            return;
        }

        if (!confirm(`¿Generar ${seleccionadas.length} actividades de Evaluaciones Medicas Ocupacionales en el PTA?`)) return;

        const btn = document.getElementById('btnGenerarSeleccionadas');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generando...';

        fetch(`<?= base_url('generador-ia') ?>/${idCliente}/generar-actividades-evaluaciones-medicas`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                anio: anio,
                actividades: seleccionadas,
                instrucciones: getInstruccionesIA()
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Cerrar modal
                bootstrap.Modal.getInstance(document.getElementById('modalPreview')).hide();

                // Mostrar toast de exito con detalles
                const detalles = `<strong>${data.data?.creadas || 0}</strong> actividades creadas<br>
                                  <strong>${data.data?.existentes || 0}</strong> ya existian`;
                showToast('success', 'Actividades Generadas', detalles);

                // Recargar despues de mostrar el toast
                setTimeout(() => location.reload(), 2000);
            } else {
                showToast('error', 'Error', data.message);
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-magic me-1"></i>Generar ${seleccionadas.length} Actividades`;
            }
        })
        .catch(err => {
            showToast('error', 'Error de Conexion', 'No se pudo conectar con el servidor');
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-magic me-1"></i>Generar ${seleccionadas.length} Actividades`;
        });
    }
    </script>
</body>
</html>
