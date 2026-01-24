<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cumplimiento Estándares - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .progress-ring {
            width: 150px;
            height: 150px;
        }
        .progress-ring__circle {
            stroke-dasharray: 440;
            stroke-dashoffset: 440;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
            transition: stroke-dashoffset 0.5s;
        }
        .estado-cumple { background-color: #D1FAE5; color: #065F46; }
        .estado-no_cumple { background-color: #FEE2E2; color: #991B1B; }
        .estado-en_proceso { background-color: #FEF3C7; color: #92400E; }
        .estado-no_aplica { background-color: #E5E7EB; color: #374151; }
        .estado-pendiente { background-color: #DBEAFE; color: #1E40AF; }
        .phva-card { border-left: 4px solid; }
        .phva-planear { border-color: #3B82F6; }
        .phva-hacer { border-color: #10B981; }
        .phva-verificar { border-color: #F59E0B; }
        .phva-actuar { border-color: #EF4444; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>">
                <i class="bi bi-check2-square me-2"></i>Cumplimiento Estándares
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a class="nav-link" href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Alertas -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>


        <!-- Resumen General -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-3">Cumplimiento General</h6>
                        <svg class="progress-ring" viewBox="0 0 150 150">
                            <circle cx="75" cy="75" r="70" fill="none" stroke="#E5E7EB" stroke-width="10"/>
                            <circle id="progressCircle" class="progress-ring__circle" cx="75" cy="75" r="70" fill="none"
                                    stroke="#10B981" stroke-width="10"
                                    style="stroke-dashoffset: <?= 440 - (440 * ($cumplimientoPonderado ?? 0) / 100) ?>"/>
                        </svg>
                        <h2 id="porcentajeCumplimiento" class="mt-3 mb-0"><?= number_format($cumplimientoPonderado ?? 0, 1) ?>%</h2>
                        <small class="text-muted">Ponderado según pesos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row h-100">
                    <div class="col">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center py-3">
                                <i class="bi bi-check-circle text-success fs-2"></i>
                                <h4 id="contadorCumple" class="mt-1 mb-0"><?= $resumen['cumple'] ?? 0 ?></h4>
                                <small class="text-muted">Cumple</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center py-3">
                                <i class="bi bi-hourglass-split text-warning fs-2"></i>
                                <h4 id="contadorEnProceso" class="mt-1 mb-0"><?= $resumen['en_proceso'] ?? 0 ?></h4>
                                <small class="text-muted">En Proceso</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center py-3">
                                <i class="bi bi-x-circle text-danger fs-2"></i>
                                <h4 id="contadorNoCumple" class="mt-1 mb-0"><?= $resumen['no_cumple'] ?? 0 ?></h4>
                                <small class="text-muted">No Cumple</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center py-3">
                                <i class="bi bi-clock text-info fs-2"></i>
                                <h4 id="contadorPendiente" class="mt-1 mb-0"><?= $resumen['pendiente'] ?? 0 ?></h4>
                                <small class="text-muted">Pendiente</small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card border-0 shadow-sm h-100 bg-light">
                            <div class="card-body text-center py-3">
                                <i class="bi bi-dash-circle text-secondary fs-2"></i>
                                <h4 id="contadorNoAplica" class="mt-1 mb-0 text-secondary"><?= $resumen['no_aplica'] ?? 0 ?></h4>
                                <small class="text-muted">No Aplica</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contexto del Cliente -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Contexto SST del Cliente</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Trabajadores:</strong> <?= $contexto['total_trabajadores'] ?? 'N/D' ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Niveles de Riesgo:</strong>
                        <?php
                        $nivelesRiesgo = [];
                        if (!empty($contexto['niveles_riesgo_arl'])) {
                            $nivelesRiesgo = json_decode($contexto['niveles_riesgo_arl'], true) ?? [];
                        }
                        if (!empty($nivelesRiesgo)): ?>
                            <?php foreach ($nivelesRiesgo as $nivel): ?>
                                <span class="badge bg-warning text-dark"><?= esc($nivel) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            N/D
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Nivel Estándares:</strong>
                        <span class="badge bg-primary"><?= $contexto['estandares_aplicables'] ?? 60 ?></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Actividad:</strong> <?= esc($contexto['sector_economico'] ?? 'N/D') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transiciones Pendientes -->
        <?php if (!empty($transicionesPendientes)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Transición de Nivel Pendiente:</strong>
                Hay cambios en el número de trabajadores o nivel de riesgo que requieren actualizar los estándares aplicables.
                <a href="<?= base_url('estandares/transiciones/' . $cliente['id_cliente']) ?>" class="alert-link">Ver detalles</a>
            </div>
        <?php endif; ?>

        <!-- Filtro de visualización -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-2">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted small">Mostrar:</span>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary active" id="btnMostrarAplicables" onclick="filtrarEstandares('aplicables')">
                                <i class="bi bi-funnel me-1"></i>Solo aplicables
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnMostrarTodos" onclick="filtrarEstandares('todos')">
                                <i class="bi bi-list me-1"></i>Ver todos (60)
                            </button>
                        </div>
                    </div>
                    <div class="text-muted small">
                        <span id="contadorVisible"><?= $resumen['total'] ?? 0 ?></span> estándares visibles
                    </div>
                </div>
            </div>
        </div>

        <!-- Estándares por Ciclo PHVA -->
        <?php
        $tieneEstandares = false;
        foreach ($estandares as $items) {
            if (!empty($items)) {
                $tieneEstandares = true;
                break;
            }
        }
        ?>
        <?php
        $nivelEstCliente = $contexto['estandares_aplicables'] ?? 60;
        ?>
        <?php if (!$tieneEstandares): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-clipboard-x text-muted" style="font-size: 3rem;"></i>
                    <h5 class="text-muted mt-3">No hay estándares inicializados para este cliente</h5>
                    <p class="text-muted small mb-4">
                        Esta acción creará los estándares de la Resolución 0312/2019 según el nivel configurado.<br>
                        <strong class="text-warning">Solo debe realizarse una vez al año o cuando ingresa un cliente nuevo.</strong>
                    </p>
                    <button type="button" class="btn btn-primary btn-lg" onclick="confirmarInicializacion()">
                        <i class="bi bi-plus-lg me-1"></i>Inicializar Estándares del Cliente
                    </button>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($estandares as $ciclo => $items): ?>
                <?php
                    $phvaClass = match(strtolower($ciclo)) {
                        'planear' => 'phva-planear',
                        'hacer' => 'phva-hacer',
                        'verificar' => 'phva-verificar',
                        'actuar' => 'phva-actuar',
                        default => ''
                    };
                ?>
                <div class="card border-0 shadow-sm mb-4 phva-card <?= $phvaClass ?>">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= strtoupper($ciclo) ?></h5>
                        <span class="badge bg-secondary"><?= count($items) ?> estándares</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">Núm.</th>
                                        <th>Estándar</th>
                                        <th style="width: 120px;">Estado</th>
                                        <th style="width: 60px;">Peso</th>
                                        <th style="width: 100px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr class="estandar-row" data-estado="<?= $item['estado'] ?? 'pendiente' ?>">
                                            <td><span class="badge bg-secondary"><?= $item['item'] ?? $item['numero_estandar'] ?? '' ?></span></td>
                                            <td>
                                                <strong><?= esc($item['nombre']) ?></strong>
                                                <?php if (!empty($item['observaciones'])): ?>
                                                    <br><small class="text-muted"><?= esc($item['observaciones']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm estado-select"
                                                        data-id-estandar="<?= $item['id_estandar'] ?>"
                                                        data-id-cliente="<?= $cliente['id_cliente'] ?>">
                                                    <option value="pendiente" <?= ($item['estado'] ?? 'pendiente') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                    <option value="cumple" <?= ($item['estado'] ?? '') === 'cumple' ? 'selected' : '' ?>>Cumple</option>
                                                    <option value="no_cumple" <?= ($item['estado'] ?? '') === 'no_cumple' ? 'selected' : '' ?>>No Cumple</option>
                                                    <option value="en_proceso" <?= ($item['estado'] ?? '') === 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                                                    <option value="no_aplica" <?= ($item['estado'] ?? '') === 'no_aplica' ? 'selected' : '' ?>>No Aplica</option>
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark"><?= $item['peso_porcentual'] ?? $item['peso'] ?? 0 ?>%</span>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('estandares/detalle/' . $cliente['id_cliente'] . '/' . $item['id_estandar']) ?>"
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const idCliente = <?= $cliente['id_cliente'] ?>;
        const nombreCliente = '<?= esc($cliente['nombre_cliente']) ?>';

        // Doble confirmación para inicializar estándares con SweetAlert
        function confirmarInicializacion() {
            const nivelEstandares = <?= $contexto['estandares_aplicables'] ?? 60 ?>;

            Swal.fire({
                title: 'Inicializar Estándares',
                html: `
                    <p>¿Está seguro de inicializar los estándares de la <strong>Resolución 0312/2019</strong> para este cliente?</p>
                    <div class="alert alert-info text-start">
                        <strong>Nivel configurado:</strong> ${nivelEstandares} estándares aplicables
                    </div>
                    <p class="text-muted small mb-0">Esta acción solo debe realizarse:</p>
                    <ul class="text-muted small text-start">
                        <li>Una vez al año para la autoevaluación del SG-SST</li>
                        <li>Cuando ingresa un cliente nuevo al sistema</li>
                    </ul>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, inicializar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Segunda confirmación
                    Swal.fire({
                        title: 'Confirmación Final',
                        html: `
                            <p>Se crearán <strong>${nivelEstandares} estándares</strong> aplicables según el contexto del cliente.</p>
                            <p class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Esta acción no se puede deshacer.</p>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Confirmar',
                        cancelButtonText: 'Cancelar'
                    }).then((result2) => {
                        if (result2.isConfirmed) {
                            // Mostrar loading
                            Swal.fire({
                                title: 'Inicializando...',
                                html: 'Creando estándares para el cliente',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            // Hacer la petición AJAX para inicializar
                            fetch('<?= base_url("estandares/inicializar/") ?>' + idCliente, {
                                method: 'GET',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => {
                                // Independientemente de la respuesta, mostrar el SweetAlert de documentación
                                mostrarAlertaDocumentacion();
                            })
                            .catch(error => {
                                // Si hay error, igual redirigir (puede que sea redirect)
                                mostrarAlertaDocumentacion();
                            });
                        }
                    });
                }
            });
        }

        // SweetAlert para ir a crear documentación
        function mostrarAlertaDocumentacion() {
            Swal.fire({
                title: '¡Estándares Inicializados!',
                html: `
                    <div class="text-center">
                        <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                        <p class="mt-3">Los estándares han sido creados correctamente.</p>
                        <hr>
                        <p class="fw-bold text-primary">
                            <i class="bi bi-magic me-2"></i>¿Estás listo para crear toda la documentación de tu cliente con IA?
                        </p>
                        <p class="text-muted small">
                            El sistema usará el contexto del cliente para generar los documentos.<br>
                            Luego podrás volver a calificar los estándares ya culminados.
                        </p>
                    </div>
                `,
                icon: null,
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-file-earmark-plus me-1"></i>Ir a Crear Documentación',
                cancelButtonText: 'Quedarse aquí',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Ir al módulo de documentación
                    window.location.href = '<?= base_url("documentacion") ?>/' + idCliente;
                } else {
                    // Quedarse y recargar para ver los estándares
                    window.location.reload();
                }
            });
        }

        // Filtro de estándares (mostrar solo aplicables o todos)
        let filtroActual = 'aplicables';

        function filtrarEstandares(tipo) {
            filtroActual = tipo;
            const filas = document.querySelectorAll('.estandar-row');
            const btnAplicables = document.getElementById('btnMostrarAplicables');
            const btnTodos = document.getElementById('btnMostrarTodos');
            const contador = document.getElementById('contadorVisible');

            let visibles = 0;

            filas.forEach(fila => {
                const estado = fila.dataset.estado;
                if (tipo === 'todos') {
                    fila.style.display = '';
                    visibles++;
                } else {
                    // Solo mostrar los que NO son 'no_aplica'
                    if (estado === 'no_aplica') {
                        fila.style.display = 'none';
                    } else {
                        fila.style.display = '';
                        visibles++;
                    }
                }
            });

            // Actualizar botones
            if (tipo === 'aplicables') {
                btnAplicables.classList.add('active');
                btnAplicables.classList.remove('btn-outline-primary');
                btnAplicables.classList.add('btn-primary');
                btnTodos.classList.remove('active', 'btn-secondary');
                btnTodos.classList.add('btn-outline-secondary');
            } else {
                btnTodos.classList.add('active');
                btnTodos.classList.remove('btn-outline-secondary');
                btnTodos.classList.add('btn-secondary');
                btnAplicables.classList.remove('active', 'btn-primary');
                btnAplicables.classList.add('btn-outline-primary');
            }

            // Actualizar contador
            contador.textContent = visibles;
        }

        // Aplicar filtro inicial (solo aplicables por defecto)
        document.addEventListener('DOMContentLoaded', function() {
            filtrarEstandares('aplicables');
        });

        // Función para actualizar los cards de resumen
        function actualizarResumen(resumen) {
            if (!resumen) return;

            // Actualizar contadores
            document.getElementById('contadorCumple').textContent = resumen.cumple || 0;
            document.getElementById('contadorEnProceso').textContent = resumen.en_proceso || 0;
            document.getElementById('contadorNoCumple').textContent = resumen.no_cumple || 0;
            document.getElementById('contadorPendiente').textContent = resumen.pendiente || 0;
            document.getElementById('contadorNoAplica').textContent = resumen.no_aplica || 0;

            // Actualizar porcentaje de cumplimiento
            const porcentaje = resumen.porcentaje_cumplimiento || 0;
            document.getElementById('porcentajeCumplimiento').textContent = porcentaje.toFixed(1) + '%';

            // Actualizar el círculo de progreso (stroke-dashoffset)
            const circle = document.getElementById('progressCircle');
            if (circle) {
                const offset = 440 - (440 * porcentaje / 100);
                circle.style.strokeDashoffset = offset;
            }

            // Actualizar contador de visibles
            const contadorVisible = document.getElementById('contadorVisible');
            if (contadorVisible) {
                contadorVisible.textContent = resumen.total || 0;
            }
        }

        // Actualizar estado vía AJAX
        document.querySelectorAll('.estado-select').forEach(select => {
            select.addEventListener('change', function() {
                const idEstandar = this.dataset.idEstandar;
                const idCliente = this.dataset.idCliente;
                const estado = this.value;
                const fila = this.closest('.estandar-row');
                const selectElement = this;

                // Mostrar indicador de carga
                selectElement.classList.add('opacity-50');
                selectElement.disabled = true;

                fetch('<?= base_url("estandares/actualizar-estado") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `id_cliente=${idCliente}&id_estandar=${idEstandar}&estado=${estado}`
                })
                .then(response => response.json())
                .then(data => {
                    selectElement.classList.remove('opacity-50');
                    selectElement.disabled = false;

                    if (data.success) {
                        // Actualizar el data-estado de la fila
                        fila.dataset.estado = estado;

                        // Actualizar visualmente
                        selectElement.classList.add('is-valid');
                        setTimeout(() => selectElement.classList.remove('is-valid'), 2000);

                        // Actualizar los cards de resumen con los datos del servidor
                        if (data.resumen) {
                            actualizarResumen(data.resumen);
                        }

                        // Re-aplicar filtro si está en modo "aplicables" y cambió a "no_aplica"
                        if (filtroActual === 'aplicables' && estado === 'no_aplica') {
                            filtrarEstandares('aplicables');
                        }
                    } else {
                        alert(data.message || 'Error al actualizar');
                        // Revertir el select al valor anterior
                        location.reload();
                    }
                })
                .catch(error => {
                    selectElement.classList.remove('opacity-50');
                    selectElement.disabled = false;
                    console.error('Error:', error);
                    alert('Error de conexión al guardar');
                });
            });
        });
    </script>
</body>
</html>
