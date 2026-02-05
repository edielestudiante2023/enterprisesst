<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-gear-wide-connected text-primary me-2"></i>
                        Administracion de Procesos Electorales
                    </h1>
                    <p class="text-muted mb-0">Gestione y modifique el estado de los procesos electorales</p>
                </div>
                <a href="<?= base_url('dashboardconsultant') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="text" id="buscarProceso" class="form-control" placeholder="Buscar por cliente, tipo...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select id="filtroEstado" class="form-select">
                                <option value="">Todos los estados</option>
                                <?php foreach ($estadosInfo as $codigo => $info): ?>
                                <option value="<?= $codigo ?>"><?= $info['label'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="filtroTipo" class="form-select">
                                <option value="">Todos los tipos</option>
                                <option value="COPASST">COPASST</option>
                                <option value="COCOLAB">COCOLAB</option>
                                <option value="BRIGADA">Brigada</option>
                                <option value="VIGIA">Vigia</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-end">
                            <span class="badge bg-secondary" id="contadorProcesos"><?= count($procesos) ?> procesos</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de procesos -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaProcesos">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3" style="width: 60px;">ID</th>
                                    <th>Cliente</th>
                                    <th>Tipo Comite</th>
                                    <th class="text-center">Año</th>
                                    <th class="text-center">Estado Actual</th>
                                    <th>Fechas</th>
                                    <th class="text-center" style="width: 200px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($procesos)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No hay procesos electorales registrados
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($procesos as $p): ?>
                                <?php $info = $estadosInfo[$p['estado']] ?? ['label' => $p['estado'], 'color' => 'secondary', 'icon' => 'question']; ?>
                                <tr class="proceso-row"
                                    data-estado="<?= $p['estado'] ?>"
                                    data-tipo="<?= $p['tipo_comite'] ?>"
                                    data-cliente="<?= strtolower($p['nombre_cliente'] ?? '') ?>">
                                    <td class="ps-3">
                                        <strong class="text-primary">#<?= $p['id_proceso'] ?></strong>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= esc($p['nombre_cliente'] ?? 'Sin cliente') ?></div>
                                        <small class="text-muted">NIT: <?= esc($p['nit'] ?? 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $p['tipo_comite'] == 'COPASST' ? 'success' : ($p['tipo_comite'] == 'COCOLAB' ? 'warning' : 'info') ?> bg-opacity-10 text-<?= $p['tipo_comite'] == 'COPASST' ? 'success' : ($p['tipo_comite'] == 'COCOLAB' ? 'warning' : 'info') ?>">
                                            <?= $p['nombre_comite'] ?? $p['tipo_comite'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <strong><?= $p['anio'] ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $info['color'] ?> px-3 py-2">
                                            <i class="bi bi-<?= $info['icon'] ?> me-1"></i>
                                            <?= $info['label'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="d-block text-muted">
                                            <i class="bi bi-calendar-event me-1"></i>Creado: <?= date('d/m/Y', strtotime($p['created_at'])) ?>
                                        </small>
                                        <?php if ($p['fecha_completado']): ?>
                                        <small class="d-block text-success">
                                            <i class="bi bi-check-circle me-1"></i>Completado: <?= date('d/m/Y', strtotime($p['fecha_completado'])) ?>
                                        </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('comites-elecciones/' . $p['id_cliente'] . '/proceso/' . $p['id_proceso']) ?>"
                                               class="btn btn-outline-primary" title="Ver proceso">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($p['estado'] !== 'cancelado'): ?>
                                            <button type="button" class="btn btn-outline-warning btn-reabrir"
                                                    data-id="<?= $p['id_proceso'] ?>"
                                                    data-estado="<?= $p['estado'] ?>"
                                                    data-cliente="<?= esc($p['nombre_cliente'] ?? '') ?>"
                                                    data-tipo="<?= $p['tipo_comite'] ?>"
                                                    data-anio="<?= $p['anio'] ?>"
                                                    title="Reabrir/Cambiar estado">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-cancelar"
                                                    data-id="<?= $p['id_proceso'] ?>"
                                                    data-cliente="<?= esc($p['nombre_cliente'] ?? '') ?>"
                                                    data-tipo="<?= $p['tipo_comite'] ?>"
                                                    title="Cancelar proceso">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-outline-success btn-reactivar"
                                                    data-id="<?= $p['id_proceso'] ?>"
                                                    data-cliente="<?= esc($p['nombre_cliente'] ?? '') ?>"
                                                    data-tipo="<?= $p['tipo_comite'] ?>"
                                                    title="Reactivar proceso">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leyenda de estados -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 bg-light">
                <div class="card-body py-3">
                    <h6 class="mb-2"><i class="bi bi-info-circle me-1"></i>Flujo de estados del proceso electoral:</h6>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <?php
                        $orden = ['configuracion', 'inscripcion', 'votacion', 'escrutinio', 'designacion_empleador', 'firmas', 'completado'];
                        foreach ($orden as $i => $codigo):
                            $info = $estadosInfo[$codigo];
                        ?>
                        <span class="badge bg-<?= $info['color'] ?> bg-opacity-75">
                            <?= ($i + 1) ?>. <?= $info['label'] ?>
                        </span>
                        <?php if ($i < count($orden) - 1): ?>
                        <i class="bi bi-arrow-right text-muted"></i>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reabrir Proceso -->
<div class="modal fade" id="modalReabrir" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-10 border-warning">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-counterclockwise text-warning me-2"></i>
                    Reabrir Proceso Electoral
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Advertencia:</strong> Esta accion modificara el estado del proceso electoral y puede afectar los datos registrados.
                </div>

                <div class="mb-3 p-3 bg-light rounded">
                    <p class="mb-1"><strong>Proceso:</strong> <span id="infoProceso"></span></p>
                    <p class="mb-0"><strong>Estado actual:</strong> <span id="infoEstadoActual" class="badge"></span></p>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Seleccione el nuevo estado:</label>
                    <select id="selectNuevoEstado" class="form-select form-select-lg">
                        <option value="">-- Seleccione --</option>
                        <option value="configuracion">1. Configuracion</option>
                        <option value="inscripcion">2. Inscripcion</option>
                        <option value="votacion">3. Votacion</option>
                        <option value="escrutinio">4. Escrutinio</option>
                        <option value="designacion_empleador">5. Designacion Empleador</option>
                        <option value="firmas">6. Firmas</option>
                    </select>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="checkConfirmarReabrir">
                    <label class="form-check-label" for="checkConfirmarReabrir">
                        Confirmo que deseo cambiar el estado de este proceso
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnConfirmarReabrir" disabled>
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reabrir Proceso
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalReabrir = new bootstrap.Modal(document.getElementById('modalReabrir'));
    let procesoActual = null;

    // Estados info para JS
    const estadosInfo = <?= json_encode($estadosInfo) ?>;

    // Filtros
    const buscarInput = document.getElementById('buscarProceso');
    const filtroEstado = document.getElementById('filtroEstado');
    const filtroTipo = document.getElementById('filtroTipo');
    const filas = document.querySelectorAll('.proceso-row');
    const contador = document.getElementById('contadorProcesos');

    function filtrarProcesos() {
        const texto = buscarInput.value.toLowerCase();
        const estado = filtroEstado.value;
        const tipo = filtroTipo.value;
        let visibles = 0;

        filas.forEach(fila => {
            const cliente = fila.dataset.cliente || '';
            const estadoFila = fila.dataset.estado;
            const tipoFila = fila.dataset.tipo;

            const coincideTexto = !texto || cliente.includes(texto) || tipoFila.toLowerCase().includes(texto);
            const coincideEstado = !estado || estadoFila === estado;
            const coincideTipo = !tipo || tipoFila === tipo;

            if (coincideTexto && coincideEstado && coincideTipo) {
                fila.style.display = '';
                visibles++;
            } else {
                fila.style.display = 'none';
            }
        });

        contador.textContent = visibles + ' procesos';
    }

    buscarInput.addEventListener('input', filtrarProcesos);
    filtroEstado.addEventListener('change', filtrarProcesos);
    filtroTipo.addEventListener('change', filtrarProcesos);

    // Boton Reabrir
    document.querySelectorAll('.btn-reabrir').forEach(btn => {
        btn.addEventListener('click', function() {
            procesoActual = {
                id: this.dataset.id,
                estado: this.dataset.estado,
                cliente: this.dataset.cliente,
                tipo: this.dataset.tipo,
                anio: this.dataset.anio
            };

            document.getElementById('infoProceso').textContent =
                `${procesoActual.tipo} ${procesoActual.anio} - ${procesoActual.cliente}`;

            const info = estadosInfo[procesoActual.estado] || { label: procesoActual.estado, color: 'secondary' };
            const badgeEstado = document.getElementById('infoEstadoActual');
            badgeEstado.textContent = info.label;
            badgeEstado.className = 'badge bg-' + info.color;

            // Reset form
            document.getElementById('selectNuevoEstado').value = '';
            document.getElementById('checkConfirmarReabrir').checked = false;
            document.getElementById('btnConfirmarReabrir').disabled = true;

            modalReabrir.show();
        });
    });

    // Habilitar boton cuando checkbox este marcado
    document.getElementById('checkConfirmarReabrir').addEventListener('change', function() {
        const selectEstado = document.getElementById('selectNuevoEstado').value;
        document.getElementById('btnConfirmarReabrir').disabled = !this.checked || !selectEstado;
    });

    document.getElementById('selectNuevoEstado').addEventListener('change', function() {
        const checkbox = document.getElementById('checkConfirmarReabrir').checked;
        document.getElementById('btnConfirmarReabrir').disabled = !checkbox || !this.value;
    });

    // Confirmar Reabrir con doble confirmacion SweetAlert
    document.getElementById('btnConfirmarReabrir').addEventListener('click', function() {
        const nuevoEstado = document.getElementById('selectNuevoEstado').value;
        const nuevoEstadoLabel = document.getElementById('selectNuevoEstado').selectedOptions[0].text;

        modalReabrir.hide();

        // Primera confirmacion
        Swal.fire({
            title: '¿Reabrir proceso?',
            html: `
                <p>Esta a punto de cambiar el estado del proceso:</p>
                <div class="text-start bg-light p-3 rounded mb-3">
                    <strong>${procesoActual.tipo} ${procesoActual.anio}</strong><br>
                    <small class="text-muted">${procesoActual.cliente}</small>
                </div>
                <p class="mb-0">
                    <span class="badge bg-secondary">Estado actual</span>
                    <i class="bi bi-arrow-right mx-2"></i>
                    <span class="badge bg-warning text-dark">${nuevoEstadoLabel}</span>
                </p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f0ad4e',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Si, continuar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Segunda confirmacion (mas seria)
                Swal.fire({
                    title: '¡CONFIRMACION FINAL!',
                    html: `
                        <div class="alert alert-danger text-start">
                            <i class="bi bi-exclamation-octagon me-2"></i>
                            <strong>Esta accion puede tener consecuencias:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Los datos del estado actual podrian modificarse</li>
                                <li>Los votantes o candidatos podrian verse afectados</li>
                                <li>Esta accion quedara registrada en el sistema</li>
                            </ul>
                        </div>
                        <p class="fw-bold">¿Esta completamente seguro de reabrir este proceso a "${nuevoEstadoLabel}"?</p>
                    `,
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'SI, REABRIR AHORA',
                    cancelButtonText: 'No, cancelar',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result2) => {
                    if (result2.isConfirmed) {
                        // Ejecutar la accion
                        ejecutarReabrir(procesoActual.id, nuevoEstado);
                    }
                });
            }
        });
    });

    function ejecutarReabrir(idProceso, nuevoEstado) {
        Swal.fire({
            title: 'Procesando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('<?= base_url('comites-elecciones/admin/reabrir-proceso') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `id_proceso=${idProceso}&nuevo_estado=${nuevoEstado}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: '¡Proceso Reabierto!',
                    html: `
                        <p class="mb-2">${data.message}</p>
                        <div class="mt-3">
                            <span class="badge bg-secondary">${data.estado_anterior}</span>
                            <i class="bi bi-arrow-right mx-2"></i>
                            <span class="badge bg-success">${data.estado_nuevo}</span>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'Entendido'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'Ocurrio un error al procesar la solicitud',
                icon: 'error'
            });
            console.error(error);
        });
    }

    // Boton Cancelar proceso
    document.querySelectorAll('.btn-cancelar').forEach(btn => {
        btn.addEventListener('click', function() {
            const idProceso = this.dataset.id;
            const cliente = this.dataset.cliente;
            const tipo = this.dataset.tipo;

            Swal.fire({
                title: '¿Cancelar proceso electoral?',
                html: `
                    <p>Esta a punto de cancelar el proceso:</p>
                    <div class="bg-light p-3 rounded mb-3">
                        <strong>${tipo}</strong> - ${cliente}
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-start d-block">Motivo de cancelacion:</label>
                        <textarea id="motivoCancelacion" class="form-control" rows="3" placeholder="Escriba el motivo..."></textarea>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Cancelar Proceso',
                cancelButtonText: 'Volver',
                reverseButtons: true,
                preConfirm: () => {
                    const motivo = document.getElementById('motivoCancelacion').value;
                    if (!motivo.trim()) {
                        Swal.showValidationMessage('Debe ingresar un motivo');
                        return false;
                    }
                    return motivo;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Segunda confirmacion
                    Swal.fire({
                        title: '¿CONFIRMAR CANCELACION?',
                        text: 'Esta accion no se puede deshacer facilmente. El proceso quedara marcado como cancelado.',
                        icon: 'error',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'SI, CANCELAR PROCESO',
                        cancelButtonText: 'No',
                        reverseButtons: true,
                        focusCancel: true
                    }).then((result2) => {
                        if (result2.isConfirmed) {
                            ejecutarCancelar(idProceso, result.value);
                        }
                    });
                }
            });
        });
    });

    function ejecutarCancelar(idProceso, motivo) {
        Swal.fire({
            title: 'Procesando...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch('<?= base_url('comites-elecciones/admin/cancelar-proceso') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `id_proceso=${idProceso}&motivo=${encodeURIComponent(motivo)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Proceso Cancelado',
                    text: data.message,
                    icon: 'success'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            Swal.fire('Error', 'Ocurrio un error', 'error');
            console.error(error);
        });
    }

    // Boton Reactivar (para procesos cancelados)
    document.querySelectorAll('.btn-reactivar').forEach(btn => {
        btn.addEventListener('click', function() {
            const idProceso = this.dataset.id;
            const cliente = this.dataset.cliente;

            Swal.fire({
                title: '¿Reactivar proceso?',
                html: `<p>El proceso sera reactivado al estado de <strong>Configuracion</strong> para que pueda continuar.</p>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Si, reactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarReabrir(idProceso, 'configuracion');
                }
            });
        });
    });
});
</script>
<?= $this->endSection() ?>
