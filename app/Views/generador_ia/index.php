<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador IA - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" target="_blank">
                <i class="bi bi-robot me-2"></i>Generador IA - SG-SST
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
        <div class="d-flex justify-content-end mb-4">
            <span class="badge bg-<?= $estandares <= 7 ? 'info' : ($estandares <= 21 ? 'warning' : 'danger') ?> fs-6">
                <?= $estandares ?> Estandares
            </span>
        </div>

        <!-- Alertas -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Info del flujo -->
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Flujo correcto de generacion:</strong>
            <ol class="mb-0 mt-2">
                <li><strong>Cronograma de Capacitaciones</strong> - Se genera segun los estandares aplicables (<?= $estandares <= 7 ? '4' : ($estandares <= 21 ? '9' : '13') ?> capacitaciones)</li>
                <li><strong>Plan de Trabajo Anual (PTA)</strong> - Se agregan las capacitaciones del cronograma como actividades</li>
                <li><strong>Indicadores del SG-SST</strong> - Indicadores de capacitacion (max. <?= $estandares <= 7 ? '2' : ($estandares <= 21 ? '3' : '4') ?>)</li>
                <li><strong>Programa de Capacitacion</strong> - Documento formal con toda la informacion compilada</li>
            </ol>
        </div>

        <div class="row">
            <!-- Paso 1: Cronograma -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <span class="badge bg-white text-primary me-2">1</span>
                            Cronograma de Capacitaciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Estado actual -->
                        <div class="mb-3">
                            <p class="text-muted small mb-2">Estado actual (<?= $anio ?>):</p>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Programadas:</span>
                                <strong><?= $resumenCronograma['total'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Ejecutadas:</span>
                                <strong class="text-success"><?= $resumenCronograma['ejecutadas'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Pendientes:</span>
                                <strong class="text-warning"><?= $resumenCronograma['pendientes'] ?></strong>
                            </div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: <?= $resumenCronograma['porcentaje_cumplimiento'] ?>%"></div>
                            </div>
                            <small class="text-muted"><?= $resumenCronograma['porcentaje_cumplimiento'] ?>% cumplimiento</small>
                        </div>

                        <!-- Que se generara -->
                        <div class="alert alert-light small mb-3">
                            <i class="bi bi-lightbulb me-1"></i>
                            Para <?= $estandares ?> estandares se generaran <strong><?= $estandares <= 7 ? '4' : ($estandares <= 21 ? '9' : '13') ?></strong> capacitaciones:
                            <ul class="mb-0 mt-1">
                                <?php if ($estandares <= 7): ?>
                                    <li>Feb: Induccion SST</li>
                                    <li>Mar: Vigia SST</li>
                                    <li>Jun: Riesgos laborales</li>
                                    <li>Sep: Brigada/Emergencias</li>
                                <?php elseif ($estandares <= 21): ?>
                                    <li>Feb: Induccion/Reinduccion</li>
                                    <li>Mar: COPASST + Convivencia</li>
                                    <li>Abr-Nov: Riesgos y brigadistas</li>
                                <?php else: ?>
                                    <li>Todas las de 21 estandares</li>
                                    <li>+ COPASST sesion 3</li>
                                    <li>+ Convivencia sesion 3</li>
                                    <li>+ Brigadistas sesion 3</li>
                                    <li>+ Estilos vida saludable</li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <button type="button" class="btn btn-primary w-100" id="btnGenerarCronograma">
                            <i class="bi bi-calendar-plus me-1"></i>Generar Cronograma
                        </button>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="<?= base_url('listcronogCapacitacion') ?>" target="_blank" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-eye me-1"></i>Ver Cronograma Actual
                        </a>
                    </div>
                </div>
            </div>

            <!-- Paso 2: PTA -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <span class="badge bg-white text-success me-2">2</span>
                            Plan de Trabajo Anual
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Estado actual -->
                        <div class="mb-3">
                            <p class="text-muted small mb-2">Estado actual (<?= $anio ?>):</p>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Total actividades:</span>
                                <strong><?= $resumenPTA['total'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Cerradas:</span>
                                <strong class="text-success"><?= $resumenPTA['cerradas'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>En proceso:</span>
                                <strong class="text-warning"><?= $resumenPTA['en_proceso'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Abiertas:</span>
                                <strong class="text-secondary"><?= $resumenPTA['abiertas'] ?></strong>
                            </div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: <?= $resumenPTA['porcentaje_avance'] ?>%"></div>
                            </div>
                            <small class="text-muted"><?= $resumenPTA['porcentaje_avance'] ?>% avance</small>
                        </div>

                        <!-- Info -->
                        <div class="alert alert-light small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Este paso agrega las <strong><?= $resumenCronograma['total'] ?></strong> capacitaciones del cronograma como actividades del Plan de Trabajo Anual.
                        </div>

                        <button type="button" class="btn btn-success w-100" id="btnGenerarPTA">
                            <i class="bi bi-arrow-right me-1"></i>Agregar Capacitaciones al PTA
                        </button>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="<?= base_url('pta-cliente-nueva/list') ?>" target="_blank" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-eye me-1"></i>Ver PTA Actual
                        </a>
                    </div>
                </div>
            </div>

            <!-- Paso 3: Indicadores -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <span class="badge bg-dark text-warning me-2">3</span>
                            Indicadores del SG-SST
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Estado actual -->
                        <div class="mb-3">
                            <p class="text-muted small mb-2">Estado actual:</p>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Total indicadores:</span>
                                <strong><?= $verificacionIndicadores['total'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Medidos:</span>
                                <strong><?= $verificacionIndicadores['medidos'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Cumplen meta:</span>
                                <strong class="text-success"><?= $verificacionIndicadores['cumplen'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>No cumplen:</span>
                                <strong class="text-danger"><?= $verificacionIndicadores['no_cumplen'] ?></strong>
                            </div>
                            <?php if ($verificacionIndicadores['total'] > 0): ?>
                                <div class="progress mt-2" style="height: 10px;">
                                    <div class="progress-bar bg-success" style="width: <?= $verificacionIndicadores['porcentaje_cumplimiento'] ?>%"></div>
                                </div>
                                <small class="text-muted"><?= $verificacionIndicadores['porcentaje_cumplimiento'] ?>% cumplimiento</small>
                            <?php endif; ?>
                        </div>

                        <!-- Limites -->
                        <div class="alert alert-light small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Maximo <strong><?= $estandares <= 7 ? '2' : ($estandares <= 21 ? '3' : '4') ?></strong> indicadores para <?= $estandares ?> estandares
                        </div>

                        <button type="button" class="btn btn-warning w-100" id="btnGenerarIndicadores">
                            <i class="bi bi-graph-up me-1"></i>Generar Indicadores
                        </button>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-eye me-1"></i>Ver Indicadores
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paso 4: Generar Documento Programa de Capacitacion -->
        <?php
        // Verificar si los 3 pasos previos estan completos
        $cronogramaListo = $resumenCronograma['total'] > 0;
        $ptaListo = $resumenPTA['total'] > 0;
        $indicadoresListo = $verificacionIndicadores['total'] > 0;
        $puedeGenerarDocumento = $cronogramaListo && $ptaListo && $indicadoresListo;
        ?>
        <div class="card border-0 shadow-sm <?= !$puedeGenerarDocumento ? 'border-secondary' : 'border-success' ?>" style="<?= !$puedeGenerarDocumento ? 'opacity: 0.7;' : '' ?>">
            <div class="card-header bg-<?= $puedeGenerarDocumento ? 'dark' : 'secondary' ?> text-white">
                <h5 class="mb-0">
                    <span class="badge bg-white text-<?= $puedeGenerarDocumento ? 'dark' : 'secondary' ?> me-2">4</span>
                    Programa de Capacitacion (Documento)
                </h5>
            </div>
            <div class="card-body text-center py-4">
                <!-- Checklist de requisitos -->
                <div class="d-flex justify-content-center gap-4 mb-4">
                    <div class="text-center">
                        <i class="bi bi-<?= $cronogramaListo ? 'check-circle-fill text-success' : 'circle text-secondary' ?> fs-4"></i>
                        <div class="small <?= $cronogramaListo ? 'text-success' : 'text-muted' ?>">Cronograma</div>
                    </div>
                    <div class="text-center">
                        <i class="bi bi-<?= $ptaListo ? 'check-circle-fill text-success' : 'circle text-secondary' ?> fs-4"></i>
                        <div class="small <?= $ptaListo ? 'text-success' : 'text-muted' ?>">PTA</div>
                    </div>
                    <div class="text-center">
                        <i class="bi bi-<?= $indicadoresListo ? 'check-circle-fill text-success' : 'circle text-secondary' ?> fs-4"></i>
                        <div class="small <?= $indicadoresListo ? 'text-success' : 'text-muted' ?>">Indicadores</div>
                    </div>
                </div>

                <?php if ($puedeGenerarDocumento): ?>
                    <p class="text-muted mb-3">
                        <i class="bi bi-check-circle text-success me-1"></i>
                        Todos los requisitos cumplidos. Puede crear el documento y generar cada seccion con IA.
                    </p>
                    <a href="<?= base_url('documentos/generar/programa_capacitacion/' . $cliente['id_cliente']) ?>" class="btn btn-lg btn-dark">
                        <i class="bi bi-magic me-2"></i>Generar Secciones con IA
                    </a>
                <?php else: ?>
                    <p class="text-muted mb-3">
                        <i class="bi bi-exclamation-circle text-warning me-1"></i>
                        Complete los pasos anteriores para generar el documento:
                    </p>
                    <ul class="list-unstyled text-start d-inline-block mb-3">
                        <?php if (!$cronogramaListo): ?>
                            <li class="text-danger"><i class="bi bi-x-circle me-1"></i>Falta generar el Cronograma de Capacitaciones</li>
                        <?php endif; ?>
                        <?php if (!$ptaListo): ?>
                            <li class="text-danger"><i class="bi bi-x-circle me-1"></i>Falta agregar capacitaciones al PTA</li>
                        <?php endif; ?>
                        <?php if (!$indicadoresListo): ?>
                            <li class="text-danger"><i class="bi bi-x-circle me-1"></i>Falta crear los Indicadores</li>
                        <?php endif; ?>
                    </ul>
                    <br>
                    <button type="button" class="btn btn-lg btn-secondary" disabled>
                        <i class="bi bi-lock me-2"></i>Generar Secciones con IA
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Links a modulos relacionados -->
        <div class="row mt-4">
            <div class="col-md-4">
                <a href="<?= base_url('responsables-sst/' . $cliente['id_cliente']) ?>" target="_blank" class="card border-0 shadow-sm text-decoration-none">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-people fs-3 text-primary me-3"></i>
                        <div>
                            <h6 class="mb-0">Responsables SST</h6>
                            <small class="text-muted">Gestionar responsables del SG-SST</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" target="_blank" class="card border-0 shadow-sm text-decoration-none">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-file-earmark-text fs-3 text-success me-3"></i>
                        <div>
                            <h6 class="mb-0">Documentacion</h6>
                            <small class="text-muted">Ver documentos del SG-SST</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?= base_url('cliente/' . $cliente['id_cliente']) ?>" target="_blank" class="card border-0 shadow-sm text-decoration-none">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-building fs-3 text-info me-3"></i>
                        <div>
                            <h6 class="mb-0">Ficha del Cliente</h6>
                            <small class="text-muted">Ver informacion completa</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Modal de progreso -->
    <div class="modal fade" id="modalProgreso" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Procesando...</span>
                    </div>
                    <h5 id="textoProgreso">Generando...</h5>
                    <p class="text-muted mb-0" id="detalleProgreso"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de resultado -->
    <div class="modal fade" id="modalResultado" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloResultado">Resultado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="contenidoResultado">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Actualizar pagina
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const clienteId = <?= $cliente['id_cliente'] ?>;
        const anio = <?= $anio ?>;

        const modalProgreso = new bootstrap.Modal(document.getElementById('modalProgreso'));
        const modalResultado = new bootstrap.Modal(document.getElementById('modalResultado'));

        function mostrarProgreso(texto, detalle = '') {
            document.getElementById('textoProgreso').textContent = texto;
            document.getElementById('detalleProgreso').textContent = detalle;
            modalProgreso.show();
        }

        function ocultarProgreso() {
            modalProgreso.hide();
        }

        function mostrarResultado(titulo, contenido, esExito = true) {
            document.getElementById('tituloResultado').innerHTML =
                (esExito ? '<i class="bi bi-check-circle text-success me-2"></i>' : '<i class="bi bi-exclamation-circle text-danger me-2"></i>') + titulo;
            document.getElementById('contenidoResultado').innerHTML = contenido;
            modalResultado.show();
        }

        // Generar Cronograma
        document.getElementById('btnGenerarCronograma').addEventListener('click', function() {
            mostrarProgreso('Generando Cronograma de Capacitaciones...', 'Creando capacitaciones segun Res. 0312/2019');

            fetch('<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/generar-cronograma') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'anio=' + anio
            })
            .then(response => response.json())
            .then(data => {
                ocultarProgreso();
                if (data.success) {
                    let contenido = '<p>' + data.message + '</p>';
                    contenido += '<ul class="list-group">';
                    (data.data.capacitaciones || []).forEach(cap => {
                        const icono = cap.estado === 'creada' ? 'check-circle text-success' : 'dash-circle text-secondary';
                        contenido += '<li class="list-group-item d-flex justify-content-between align-items-center">';
                        contenido += '<span><i class="bi bi-' + icono + ' me-2"></i>' + cap.capacitacion + '</span>';
                        contenido += '<span class="badge bg-' + (cap.estado === 'creada' ? 'success' : 'secondary') + '">' + cap.estado + '</span>';
                        contenido += '</li>';
                    });
                    contenido += '</ul>';
                    mostrarResultado('Cronograma Generado', contenido, true);
                } else {
                    mostrarResultado('Error', '<p class="text-danger">' + data.message + '</p>', false);
                }
            });
        });

        // Generar PTA (solo agregar capacitaciones del cronograma)
        document.getElementById('btnGenerarPTA').addEventListener('click', function() {
            mostrarProgreso('Agregando capacitaciones al PTA...', 'Convirtiendo capacitaciones en actividades del Plan de Trabajo');

            fetch('<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/generar-pta-cronograma') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'anio=' + anio + '&tipo_servicio=Programa de Capacitacion'
            })
            .then(response => response.json())
            .then(data => {
                ocultarProgreso();
                mostrarResultado(data.success ? 'PTA Actualizado' : 'Error',
                    '<p>' + data.message + '</p>', data.success);
            });
        });

        // Generar Indicadores
        document.getElementById('btnGenerarIndicadores').addEventListener('click', function() {
            mostrarProgreso('Generando Indicadores...', 'Proponiendo indicadores segun estandares aplicables');

            fetch('<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/generar-indicadores') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                ocultarProgreso();
                mostrarResultado(data.success ? 'Indicadores Generados' : 'Error',
                    '<p>' + data.message + '</p>', data.success);
            });
        });

        // Generar Documento Programa de Capacitacion
        const btnGenerarDocumento = document.getElementById('btnGenerarDocumento');
        if (btnGenerarDocumento) {
            btnGenerarDocumento.addEventListener('click', function() {
                mostrarProgreso('Generando Programa de Capacitacion...', 'Compilando datos del cronograma, PTA e indicadores');

                fetch('<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/generar-programa-capacitacion') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'anio=' + anio
                })
                .then(response => response.json())
                .then(data => {
                    ocultarProgreso();

                    if (data.success) {
                        let contenido = '<p class="text-success">' + data.message + '</p>';

                        if (data.data && data.data.documento_url) {
                            contenido += '<div class="d-grid gap-2 mt-3">';
                            contenido += '<a href="' + data.data.documento_url + '" target="_blank" class="btn btn-primary">';
                            contenido += '<i class="bi bi-file-earmark-pdf me-2"></i>Ver Documento Generado</a>';
                            contenido += '</div>';
                        }

                        if (data.data && data.data.resumen) {
                            contenido += '<div class="mt-3 p-3 bg-light rounded">';
                            contenido += '<h6>Contenido incluido:</h6>';
                            contenido += '<ul class="mb-0 small">';
                            contenido += '<li>Capacitaciones programadas: ' + (data.data.resumen.capacitaciones || 0) + '</li>';
                            contenido += '<li>Actividades en PTA: ' + (data.data.resumen.actividades_pta || 0) + '</li>';
                            contenido += '<li>Indicadores: ' + (data.data.resumen.indicadores || 0) + '</li>';
                            contenido += '</ul></div>';
                        }

                        mostrarResultado('Programa de Capacitacion Generado', contenido, true);
                    } else {
                        mostrarResultado('Error', '<p class="text-danger">' + data.message + '</p>', false);
                    }
                })
                .catch(error => {
                    ocultarProgreso();
                    mostrarResultado('Error', '<p class="text-danger">Error de conexion: ' + error.message + '</p>', false);
                });
            });
        }
    });
    </script>
</body>
</html>
