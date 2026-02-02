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
                    <li class="breadcrumb-item active">Nueva Acta</li>
                </ol>
            </nav>
            <h1 class="h3 mb-1">Nueva Acta - <?= esc($comite['tipo_nombre']) ?></h1>
            <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?></p>
        </div>
    </div>

    <form action="<?= base_url('actas/comite/' . $comite['id_comite'] . '/guardar-acta') ?>" method="post" id="formActa">
        <div class="row">
            <!-- Datos de la reunión -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Datos de la Reunion</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Usar datos preparados si vienen del instructivo
                        $prep = $datosPreparados ?? [];
                        ?>

                        <?php if (!empty($prep['objetivo'])): ?>
                        <div class="alert alert-info mb-3">
                            <strong><i class="bi bi-bullseye me-2"></i>Objetivo de la reunion:</strong>
                            <p class="mb-0 mt-1"><?= esc($prep['objetivo']) ?></p>
                            <input type="hidden" name="objetivo" value="<?= esc($prep['objetivo']) ?>">
                        </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tipo de Acta</label>
                                <select class="form-select" name="tipo_acta" required>
                                    <option value="ordinaria">Ordinaria</option>
                                    <option value="extraordinaria">Extraordinaria</option>
                                    <option value="conformacion">Conformacion</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Fecha de Reunion <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="fecha_reunion"
                                       value="<?= esc($prep['fecha'] ?? date('Y-m-d')) ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Modalidad</label>
                                <select class="form-select" name="modalidad" id="selectModalidad" onchange="toggleEnlace()">
                                    <option value="presencial" <?= ($prep['modalidad'] ?? '') === 'presencial' ? 'selected' : '' ?>>Presencial</option>
                                    <option value="virtual" <?= ($prep['modalidad'] ?? '') === 'virtual' ? 'selected' : '' ?>>Virtual</option>
                                    <option value="hibrida" <?= ($prep['modalidad'] ?? '') === 'hibrida' ? 'selected' : '' ?>>Hibrida</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Hora Inicio</label>
                                <input type="time" class="form-control" name="hora_inicio"
                                       value="<?= esc($prep['hora_inicio'] ?? '08:00') ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Hora Fin</label>
                                <input type="time" class="form-control" name="hora_fin"
                                       value="<?= esc($prep['hora_fin'] ?? '09:00') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lugar</label>
                                <input type="text" class="form-control" name="lugar"
                                       value="<?= esc($prep['lugar'] ?? $comite['lugar_habitual'] ?? '') ?>"
                                       placeholder="Sala de reuniones, oficina, etc.">
                            </div>
                            <div class="col-12 mb-3" id="enlaceVirtualContainer"
                                 style="display: <?= in_array($prep['modalidad'] ?? '', ['virtual', 'hibrida']) ? 'block' : 'none' ?>;">
                                <label class="form-label">Enlace Virtual</label>
                                <input type="url" class="form-control" name="enlace_virtual"
                                       value="<?= esc($prep['enlace_virtual'] ?? '') ?>"
                                       placeholder="https://meet.google.com/...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orden del día -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list-ol me-2"></i>Orden del Dia</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarPuntoOrden()">
                            <i class="bi bi-plus-lg"></i> Agregar Punto
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="ordenDelDia">
                            <?php
                            // Prioridad: 1) Puntos del instructivo, 2) Plantilla, 3) Por defecto
                            $puntosOrden = [];
                            if (!empty($prep['puntos']) && is_array($prep['puntos'])) {
                                $puntosOrden = $prep['puntos'];
                            } elseif (!empty($plantilla['puntos'])) {
                                $puntosOrden = array_map(fn($p) => $p['tema'], $plantilla['puntos']);
                            }
                            ?>
                            <?php if (!empty($puntosOrden)): ?>
                                <?php foreach ($puntosOrden as $i => $tema): ?>
                                <div class="input-group mb-2 punto-orden">
                                    <span class="input-group-text"><?= $i + 1 ?></span>
                                    <input type="hidden" name="orden_punto[]" value="<?= $i + 1 ?>">
                                    <input type="text" class="form-control" name="orden_tema[]"
                                           value="<?= esc($tema) ?>">
                                    <button type="button" class="btn btn-outline-danger" onclick="eliminarPuntoOrden(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="input-group mb-2 punto-orden">
                                    <span class="input-group-text">1</span>
                                    <input type="hidden" name="orden_punto[]" value="1">
                                    <input type="text" class="form-control" name="orden_tema[]" placeholder="Tema del punto...">
                                    <button type="button" class="btn btn-outline-danger" onclick="eliminarPuntoOrden(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Compromisos / Tareas -->
                <div class="card border-0 shadow-sm mb-4 border-start border-warning border-4">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list-task me-2 text-warning"></i>Compromisos / Tareas</h5>
                        <button type="button" class="btn btn-sm btn-warning" onclick="agregarCompromiso()">
                            <i class="bi bi-plus-lg"></i> Agregar
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="bi bi-lightbulb me-1"></i>Registre las tareas asignadas durante la reunion. No lo deje para despues.
                        </p>
                        <div id="listaCompromisos">
                            <!-- Los compromisos se agregan aqui dinamicamente -->
                        </div>
                        <div id="sinCompromisos" class="text-center py-3 text-muted">
                            <i class="bi bi-clipboard-check fs-4"></i>
                            <p class="mb-0 small">Sin compromisos registrados. Haga clic en "Agregar" para crear uno.</p>
                        </div>
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
                                <?php
                                // Por defecto siempre 30 días para próxima reunión
                                $proximaFecha = date('Y-m-d', strtotime("+30 days"));
                                ?>
                                <input type="date" class="form-control" name="proxima_reunion_fecha" value="<?= $proximaFecha ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Hora</label>
                                <input type="time" class="form-control" name="proxima_reunion_hora" value="08:00">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Lugar</label>
                                <input type="text" class="form-control" name="proxima_reunion_lugar"
                                       value="<?= esc($comite['lugar_habitual'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asistentes -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Asistentes</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Marque los miembros que asistieron a la reunion:</p>

                        <?php if (empty($miembros)): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            No hay miembros registrados en el comite.
                            <a href="<?= base_url('actas/comite/' . $comite['id_comite'] . '/nuevo-miembro') ?>">Agregar miembros</a>
                        </div>
                        <?php else: ?>
                            <!-- Quórum requerido -->
                            <div class="alert alert-info py-2 mb-3">
                                <small>
                                    <strong>Quorum requerido:</strong> <?= ceil(count($miembros) / 2) + 1 ?> de <?= count($miembros) ?> miembros
                                </small>
                            </div>

                            <?php
                            $principales = array_filter($miembros, fn($m) => $m['tipo_miembro'] === 'principal');
                            $suplentes = array_filter($miembros, fn($m) => $m['tipo_miembro'] === 'suplente');
                            ?>

                            <h6 class="text-muted mb-2">Principales</h6>
                            <?php foreach ($principales as $miembro): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input asistente-check" type="checkbox"
                                       name="asistio[]" value="<?= $miembro['id_miembro'] ?>"
                                       id="miembro_<?= $miembro['id_miembro'] ?>" checked>
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
                                       id="miembro_<?= $miembro['id_miembro'] ?>">
                                <label class="form-check-label" for="miembro_<?= $miembro['id_miembro'] ?>">
                                    <?= esc($miembro['nombre_completo']) ?>
                                    <br><small class="text-muted"><?= esc($miembro['cargo']) ?></small>
                                </label>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>

                            <hr>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Asistentes marcados:</small>
                                <strong id="conteoAsistentes"><?= count($principales) ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Compromisos pendientes -->
                <?php if (!empty($compromisosPendientes)): ?>
                <div class="card border-0 shadow-sm border-start border-warning border-4 mb-4">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Compromisos Pendientes</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach (array_slice($compromisosPendientes, 0, 5) as $comp): ?>
                            <li class="list-group-item py-2 <?= $comp['estado'] === 'vencido' ? 'list-group-item-danger' : '' ?>">
                                <small>
                                    <strong><?= esc($comp['numero_acta']) ?>:</strong>
                                    <?= esc(substr($comp['descripcion'], 0, 50)) ?>...
                                    <br>
                                    <span class="text-muted"><?= esc($comp['responsable_nombre']) ?></span>
                                </small>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if (count($compromisosPendientes) > 5): ?>
                        <div class="card-footer bg-transparent text-center">
                            <small class="text-muted">Y <?= count($compromisosPendientes) - 5 ?> mas...</small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Botones -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save me-2"></i>Crear Acta
                    </button>
                    <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $comite['id_comite']) ?>" class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Mostrar/ocultar enlace virtual
function toggleEnlace() {
    const modalidad = document.getElementById('selectModalidad').value;
    document.getElementById('enlaceVirtualContainer').style.display =
        (modalidad === 'virtual' || modalidad === 'hibrida') ? 'block' : 'none';
}

// También escuchar por si no se usa la función directa
document.querySelector('select[name="modalidad"]')?.addEventListener('change', toggleEnlace);

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

// === COMPROMISOS ===
let compromisoCount = 0;

function agregarCompromiso() {
    compromisoCount++;
    document.getElementById('sinCompromisos').style.display = 'none';

    // Obtener lista de miembros para el select
    const miembros = <?= json_encode(array_map(fn($m) => ['id' => $m['id_miembro'], 'nombre' => $m['nombre_completo']], $miembros)) ?>;

    let optionsMiembros = '<option value="">Seleccione responsable...</option>';
    miembros.forEach(m => {
        optionsMiembros += `<option value="${m.id}">${m.nombre}</option>`;
    });

    // Fecha por defecto: 30 días desde hoy
    const fechaDefault = new Date();
    fechaDefault.setDate(fechaDefault.getDate() + 30);
    const fechaStr = fechaDefault.toISOString().split('T')[0];

    const html = `
        <div class="card mb-3 compromiso-item" id="compromiso_${compromisoCount}">
            <div class="card-body py-3">
                <div class="row g-2">
                    <div class="col-12">
                        <input type="text" class="form-control" name="compromiso_descripcion[]"
                               placeholder="Descripcion del compromiso / tarea..." required>
                    </div>
                    <div class="col-md-5">
                        <select class="form-select" name="compromiso_responsable_id[]" required>
                            ${optionsMiembros}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="date" class="form-control" name="compromiso_fecha_vencimiento[]"
                               value="${fechaStr}" required>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-danger w-100" onclick="eliminarCompromiso(${compromisoCount})">
                            <i class="bi bi-trash"></i> Quitar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.getElementById('listaCompromisos').insertAdjacentHTML('beforeend', html);
}

function eliminarCompromiso(id) {
    document.getElementById('compromiso_' + id).remove();

    // Si no quedan compromisos, mostrar mensaje
    if (document.querySelectorAll('.compromiso-item').length === 0) {
        document.getElementById('sinCompromisos').style.display = 'block';
    }
}
</script>

<?= $this->endSection() ?>
