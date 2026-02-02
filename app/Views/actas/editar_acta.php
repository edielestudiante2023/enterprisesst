<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?= base_url('actas/' . $cliente['id_cliente']) ?>">Comites</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $comite['id_comite']) ?>"><?= esc($comite['codigo']) ?></a></li>
                    <li class="breadcrumb-item active">Editar Acta <?= esc($acta['numero_acta']) ?></li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Acta <?= esc($acta['numero_acta']) ?></h1>
                    <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?> - <?= esc($comite['tipo_nombre']) ?></p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $comite['id_comite'] . '/compromisos') ?>"
                       class="btn btn-warning" title="Ver dashboard de compromisos" target="_blank">
                        <i class="bi bi-list-task me-1"></i>Dashboard Compromisos <i class="bi bi-box-arrow-up-right small"></i>
                    </a>
                    <?php
                    $estadoClases = [
                        'borrador' => 'bg-secondary',
                        'en_edicion' => 'bg-info',
                        'pendiente_firma' => 'bg-warning text-dark',
                        'firmada' => 'bg-success',
                        'cerrada' => 'bg-primary'
                    ];
                    ?>
                    <span class="badge <?= $estadoClases[$acta['estado']] ?? 'bg-secondary' ?> fs-6">
                        <?= ucfirst(str_replace('_', ' ', $acta['estado'])) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <?php if ($acta['estado'] !== 'borrador'): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Esta acta ya no esta en borrador. Algunos campos no pueden ser modificados.
    </div>
    <?php endif; ?>

    <form action="<?= base_url('actas/comite/' . $comite['id_comite'] . '/acta/' . $acta['id_acta'] . '/actualizar') ?>" method="post" id="formActa">
        <div class="row">
            <!-- Datos de la reunión -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Datos de la Reunion</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tipo de Acta</label>
                                <select class="form-select" name="tipo_acta" <?= $acta['estado'] !== 'borrador' ? 'disabled' : '' ?>>
                                    <option value="ordinaria" <?= $acta['tipo_acta'] === 'ordinaria' ? 'selected' : '' ?>>Ordinaria</option>
                                    <option value="extraordinaria" <?= $acta['tipo_acta'] === 'extraordinaria' ? 'selected' : '' ?>>Extraordinaria</option>
                                    <option value="conformacion" <?= $acta['tipo_acta'] === 'conformacion' ? 'selected' : '' ?>>Conformacion</option>
                                </select>
                                <?php if ($acta['estado'] !== 'borrador'): ?>
                                <input type="hidden" name="tipo_acta" value="<?= $acta['tipo_acta'] ?>">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Fecha de Reunion <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="fecha_reunion"
                                       value="<?= $acta['fecha_reunion'] ?>" required
                                       <?= $acta['estado'] !== 'borrador' ? 'readonly' : '' ?>>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Modalidad</label>
                                <select class="form-select" name="modalidad" <?= $acta['estado'] !== 'borrador' ? 'disabled' : '' ?>>
                                    <option value="presencial" <?= $acta['modalidad'] === 'presencial' ? 'selected' : '' ?>>Presencial</option>
                                    <option value="virtual" <?= $acta['modalidad'] === 'virtual' ? 'selected' : '' ?>>Virtual</option>
                                    <option value="mixta" <?= $acta['modalidad'] === 'mixta' ? 'selected' : '' ?>>Mixta</option>
                                </select>
                                <?php if ($acta['estado'] !== 'borrador'): ?>
                                <input type="hidden" name="modalidad" value="<?= $acta['modalidad'] ?>">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Hora Inicio</label>
                                <input type="time" class="form-control" name="hora_inicio" value="<?= $acta['hora_inicio'] ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Hora Fin</label>
                                <input type="time" class="form-control" name="hora_fin" value="<?= $acta['hora_fin'] ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lugar</label>
                                <input type="text" class="form-control" name="lugar"
                                       value="<?= esc($acta['lugar'] ?? '') ?>"
                                       placeholder="Sala de reuniones, oficina, etc.">
                            </div>
                            <?php if ($acta['modalidad'] !== 'presencial'): ?>
                            <div class="col-12 mb-3" id="enlaceVirtualContainer">
                                <label class="form-label">Enlace Virtual</label>
                                <input type="url" class="form-control" name="enlace_virtual"
                                       value="<?= esc($acta['enlace_virtual'] ?? '') ?>"
                                       placeholder="https://meet.google.com/...">
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Orden del día -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list-ol me-2"></i>Orden del Dia</h5>
                        <?php if ($acta['estado'] === 'borrador'): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarPuntoOrden()">
                            <i class="bi bi-plus-lg"></i> Agregar Punto
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div id="ordenDelDia">
                            <?php
                            $ordenDia = is_string($acta['orden_del_dia']) ? json_decode($acta['orden_del_dia'], true) : $acta['orden_del_dia'];
                            if (!empty($ordenDia)):
                                foreach ($ordenDia as $i => $punto):
                            ?>
                            <div class="input-group mb-2 punto-orden">
                                <span class="input-group-text"><?= $punto['punto'] ?? ($i + 1) ?></span>
                                <input type="hidden" name="orden_punto[]" value="<?= $punto['punto'] ?? ($i + 1) ?>">
                                <input type="text" class="form-control" name="orden_tema[]"
                                       value="<?= esc($punto['tema'] ?? '') ?>"
                                       <?= $acta['estado'] !== 'borrador' ? 'readonly' : '' ?>>
                                <?php if ($acta['estado'] === 'borrador'): ?>
                                <button type="button" class="btn btn-outline-danger" onclick="eliminarPuntoOrden(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Desarrollo de la reunión -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Desarrollo de la Reunion</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $desarrollo = is_string($acta['desarrollo']) ? json_decode($acta['desarrollo'], true) : $acta['desarrollo'];
                        if (!empty($ordenDia)):
                            foreach ($ordenDia as $i => $punto):
                        ?>
                        <div class="mb-4 desarrollo-punto">
                            <label class="form-label fw-bold">
                                <?= $punto['punto'] ?? ($i + 1) ?>. <?= esc($punto['tema'] ?? '') ?>
                            </label>
                            <textarea class="form-control" name="desarrollo[<?= $punto['punto'] ?? ($i + 1) ?>]"
                                      rows="4" placeholder="Describa lo tratado en este punto..."><?= esc($desarrollo[$punto['punto'] ?? ($i + 1)] ?? '') ?></textarea>
                        </div>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                </div>

                <!-- Compromisos -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Compromisos</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarCompromiso()">
                            <i class="bi bi-plus-lg"></i> Agregar Compromiso
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="compromisos">
                            <?php if (!empty($compromisos)): ?>
                                <?php foreach ($compromisos as $comp): ?>
                                <div class="card mb-3 compromiso-item">
                                    <div class="card-body">
                                        <input type="hidden" name="compromiso_id[]" value="<?= $comp['id_compromiso'] ?>">
                                        <div class="row">
                                            <div class="col-md-8 mb-2">
                                                <label class="form-label">Descripcion</label>
                                                <textarea class="form-control" name="compromiso_descripcion[]" rows="2"><?= esc($comp['descripcion']) ?></textarea>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Responsable</label>
                                                <select class="form-select" name="compromiso_responsable[]">
                                                    <option value="">Seleccione...</option>
                                                    <?php foreach ($asistentes as $asist): ?>
                                                    <option value="<?= $asist['id_asistente'] ?>" <?= $comp['id_responsable'] == $asist['id_asistente'] ? 'selected' : '' ?>>
                                                        <?= esc($asist['nombre_completo']) ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Fecha limite</label>
                                                <input type="date" class="form-control" name="compromiso_fecha[]" value="<?= $comp['fecha_limite'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Estado</label>
                                                <select class="form-select" name="compromiso_estado[]">
                                                    <option value="pendiente" <?= $comp['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                    <option value="en_progreso" <?= $comp['estado'] === 'en_progreso' ? 'selected' : '' ?>>En progreso</option>
                                                    <option value="completado" <?= $comp['estado'] === 'completado' ? 'selected' : '' ?>>Completado</option>
                                                    <option value="vencido" <?= $comp['estado'] === 'vencido' ? 'selected' : '' ?>>Vencido</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-outline-danger" onclick="eliminarCompromiso(this, <?= $comp['id_compromiso'] ?>)">
                                                    <i class="bi bi-trash"></i> Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div id="nuevosCompromisos"></div>
                    </div>
                </div>

                <!-- Próxima reunión -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>Proxima Reunion</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Fecha</label>
                                <input type="date" class="form-control" name="proxima_reunion_fecha"
                                       value="<?= $acta['proxima_reunion_fecha'] ?? '' ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Hora</label>
                                <input type="time" class="form-control" name="proxima_reunion_hora"
                                       value="<?= $acta['proxima_reunion_hora'] ?? '08:00' ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Lugar</label>
                                <input type="text" class="form-control" name="proxima_reunion_lugar"
                                       value="<?= esc($acta['proxima_reunion_lugar'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Asistentes -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Asistentes</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($acta['estado'] === 'borrador'): ?>
                        <p class="text-muted small mb-3">Marque los miembros que asistieron:</p>
                        <?php else: ?>
                        <p class="text-muted small mb-3">Asistentes registrados:</p>
                        <?php endif; ?>

                        <?php
                        $asistentesIds = array_column($asistentes, 'id_miembro');
                        $principales = array_filter($miembros, fn($m) => $m['tipo_miembro'] === 'principal');
                        $suplentes = array_filter($miembros, fn($m) => $m['tipo_miembro'] === 'suplente');
                        ?>

                        <h6 class="text-muted mb-2">Principales</h6>
                        <?php foreach ($principales as $miembro): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input asistente-check" type="checkbox"
                                   name="asistio[]" value="<?= $miembro['id_miembro'] ?>"
                                   id="miembro_<?= $miembro['id_miembro'] ?>"
                                   <?= in_array($miembro['id_miembro'], $asistentesIds) ? 'checked' : '' ?>
                                   <?= $acta['estado'] !== 'borrador' ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="miembro_<?= $miembro['id_miembro'] ?>">
                                <?= esc($miembro['nombre_completo']) ?>
                                <?php if ($miembro['rol_comite'] !== 'miembro'): ?>
                                    <span class="badge bg-warning text-dark"><?= ucfirst($miembro['rol_comite']) ?></span>
                                <?php endif; ?>
                                <br><small class="text-muted"><?= esc($miembro['cargo']) ?></small>
                            </label>
                        </div>
                        <?php endforeach; ?>

                        <?php if (!empty($suplentes)): ?>
                        <h6 class="text-muted mb-2 mt-3">Suplentes</h6>
                        <?php foreach ($suplentes as $miembro): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input asistente-check" type="checkbox"
                                   name="asistio[]" value="<?= $miembro['id_miembro'] ?>"
                                   id="miembro_<?= $miembro['id_miembro'] ?>"
                                   <?= in_array($miembro['id_miembro'], $asistentesIds) ? 'checked' : '' ?>
                                   <?= $acta['estado'] !== 'borrador' ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="miembro_<?= $miembro['id_miembro'] ?>">
                                <?= esc($miembro['nombre_completo']) ?>
                                <br><small class="text-muted"><?= esc($miembro['cargo']) ?></small>
                            </label>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <hr>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Asistentes:</small>
                            <strong id="conteoAsistentes"><?= count($asistentes) ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Estado de firmas -->
                <?php if ($acta['estado'] !== 'borrador'): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-pen me-2"></i>Estado de Firmas</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $firmados = count(array_filter($asistentes, fn($a) => !empty($a['firma_fecha'])));
                        $totalAsist = count($asistentes);
                        $porcentaje = $totalAsist > 0 ? ($firmados / $totalAsist) * 100 : 0;
                        ?>
                        <div class="progress mb-3" style="height: 25px;">
                            <div class="progress-bar bg-success" style="width: <?= $porcentaje ?>%">
                                <?= $firmados ?> / <?= $totalAsist ?>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($asistentes as $asist): ?>
                            <li class="d-flex justify-content-between align-items-center py-1">
                                <span><?= esc($asist['nombre_completo']) ?></span>
                                <?php if (!empty($asist['firma_fecha'])): ?>
                                    <span class="badge bg-success"><i class="bi bi-check"></i></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i class="bi bi-clock"></i></span>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="<?= base_url('actas/comite/' . $comite['id_comite'] . '/acta/' . $acta['id_acta'] . '/firmas') ?>" class="btn btn-outline-primary btn-sm w-100 mt-3">
                            <i class="bi bi-eye me-1"></i> Ver detalle de firmas
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Botones de acción -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save me-2"></i>Guardar Cambios
                    </button>

                    <?php if ($acta['estado'] === 'borrador'): ?>
                    <button type="button" class="btn btn-success" onclick="enviarAFirmas()">
                        <i class="bi bi-send me-2"></i>Enviar a Firmas
                    </button>
                    <?php endif; ?>

                    <?php if ($acta['estado'] === 'firmada' && $puedesCerrar): ?>
                    <button type="button" class="btn btn-warning" onclick="cerrarActa()">
                        <i class="bi bi-lock me-2"></i>Cerrar Acta
                    </button>
                    <?php endif; ?>

                    <a href="<?= base_url('actas/comite/' . $comite['id_comite'] . '/acta/' . $acta['id_acta']) ?>" class="btn btn-outline-secondary">
                        Ver Acta
                    </a>
                    <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $comite['id_comite']) ?>" class="btn btn-outline-secondary">
                        Volver al Comite
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal para confirmar envío a firmas -->
<div class="modal fade" id="modalEnviarFirmas" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enviar Acta a Firmas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Esta accion enviara notificaciones por correo a todos los asistentes para que firmen el acta.</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Una vez enviada, no podra modificar la lista de asistentes ni el orden del dia.
                </div>
                <p>Desea continuar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="<?= base_url('actas/comite/' . $comite['id_comite'] . '/acta/' . $acta['id_acta'] . '/enviar-firmas') ?>" method="post">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-send me-1"></i> Enviar a Firmas
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cerrar acta -->
<div class="modal fade" id="modalCerrarActa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cerrar Acta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Una vez cerrada, el acta quedara como documento oficial y no podra ser modificada.</p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Asegurese de que toda la informacion este correcta antes de cerrar.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="<?= base_url('actas/comite/' . $comite['id_comite'] . '/acta/' . $acta['id_acta'] . '/cerrar') ?>" method="post">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-lock me-1"></i> Cerrar Acta
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="compromisos_eliminados" name="compromisos_eliminados" value="">

<script>
// Mostrar/ocultar enlace virtual
document.querySelector('select[name="modalidad"]')?.addEventListener('change', function() {
    const container = document.getElementById('enlaceVirtualContainer');
    if (container) {
        container.style.display = this.value === 'presencial' ? 'none' : 'block';
    }
});

// Agregar punto al orden del día
function agregarPuntoOrden() {
    const container = document.getElementById('ordenDelDia');
    const puntos = container.querySelectorAll('.punto-orden');
    const siguiente = puntos.length + 1;

    const html = `
        <div class="input-group mb-2 punto-orden">
            <span class="input-group-text">${siguiente}</span>
            <input type="hidden" name="orden_punto[]" value="${siguiente}">
            <input type="text" class="form-control" name="orden_tema[]" placeholder="Tema del punto...">
            <button type="button" class="btn btn-outline-danger" onclick="eliminarPuntoOrden(this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
}

// Eliminar punto del orden del día
function eliminarPuntoOrden(btn) {
    btn.closest('.punto-orden').remove();
    renumerarPuntos();
}

// Renumerar puntos
function renumerarPuntos() {
    const puntos = document.querySelectorAll('#ordenDelDia .punto-orden');
    puntos.forEach((punto, index) => {
        punto.querySelector('.input-group-text').textContent = index + 1;
        punto.querySelector('input[name="orden_punto[]"]').value = index + 1;
    });
}

// Conteo de asistentes
document.querySelectorAll('.asistente-check').forEach(check => {
    check.addEventListener('change', actualizarConteo);
});

function actualizarConteo() {
    const marcados = document.querySelectorAll('.asistente-check:checked').length;
    document.getElementById('conteoAsistentes').textContent = marcados;
}

// Agregar nuevo compromiso
let nuevoCompromisoCount = 0;
function agregarCompromiso() {
    nuevoCompromisoCount++;
    const container = document.getElementById('nuevosCompromisos');
    const asistentesOptions = <?= json_encode(array_map(fn($a) => ['id' => $a['id_asistente'], 'nombre' => $a['nombre_completo']], $asistentes)) ?>;

    let optionsHtml = '<option value="">Seleccione...</option>';
    asistentesOptions.forEach(a => {
        optionsHtml += `<option value="${a.id}">${a.nombre}</option>`;
    });

    const html = `
        <div class="card mb-3 compromiso-nuevo">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Descripcion</label>
                        <textarea class="form-control" name="nuevo_compromiso_descripcion[]" rows="2" required></textarea>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Responsable</label>
                        <select class="form-select" name="nuevo_compromiso_responsable[]" required>
                            ${optionsHtml}
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Fecha limite</label>
                        <input type="date" class="form-control" name="nuevo_compromiso_fecha[]" required>
                    </div>
                    <div class="col-md-8 mb-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger" onclick="eliminarNuevoCompromiso(this)">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function eliminarNuevoCompromiso(btn) {
    btn.closest('.compromiso-nuevo').remove();
}

function eliminarCompromiso(btn, id) {
    if (confirm('Eliminar este compromiso?')) {
        const eliminados = document.getElementById('compromisos_eliminados');
        eliminados.value = eliminados.value ? eliminados.value + ',' + id : id;
        btn.closest('.compromiso-item').remove();
    }
}

function enviarAFirmas() {
    new bootstrap.Modal(document.getElementById('modalEnviarFirmas')).show();
}

function cerrarActa() {
    new bootstrap.Modal(document.getElementById('modalCerrarActa')).show();
}
</script>

<?= $this->endSection() ?>
