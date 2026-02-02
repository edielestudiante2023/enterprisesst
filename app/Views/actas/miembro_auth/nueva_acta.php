<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?= base_url('miembro/dashboard') ?>">Mis Comites</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('miembro/comite/' . $comite['id_comite']) ?>"><?= esc($comite['tipo_nombre']) ?></a></li>
                    <li class="breadcrumb-item active">Nueva Acta</li>
                </ol>
            </nav>
            <h1 class="h3 mb-1">Nueva Acta - <?= esc($comite['tipo_nombre']) ?></h1>
            <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?></p>
        </div>
    </div>

    <form id="formActa">
        <?= csrf_field() ?>
        <div class="row">
            <!-- Datos de la reunion -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Datos de la Reunion</h5>
                    </div>
                    <div class="card-body">
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
                                <input type="date" class="form-control" name="fecha_reunion" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Modalidad</label>
                                <select class="form-select" name="modalidad" id="selectModalidad" onchange="toggleEnlace()">
                                    <option value="presencial">Presencial</option>
                                    <option value="virtual">Virtual</option>
                                    <option value="hibrida">Hibrida</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Hora Inicio</label>
                                <input type="time" class="form-control" name="hora_inicio" value="08:00">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Hora Fin</label>
                                <input type="time" class="form-control" name="hora_fin" value="09:00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lugar</label>
                                <input type="text" class="form-control" name="lugar"
                                       value="<?= esc($comite['lugar_habitual'] ?? '') ?>"
                                       placeholder="Sala de reuniones, oficina, etc.">
                            </div>
                            <div class="col-12 mb-3" id="enlaceVirtualContainer" style="display: none;">
                                <label class="form-label">Enlace Virtual</label>
                                <input type="url" class="form-control" name="enlace_virtual"
                                       placeholder="https://meet.google.com/...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orden del dia -->
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
                            $puntosOrden = [];
                            if (!empty($plantilla['puntos'])) {
                                $puntosOrden = array_map(fn($p) => $p['tema'], $plantilla['puntos']);
                            }
                            ?>
                            <?php if (!empty($puntosOrden)): ?>
                                <?php foreach ($puntosOrden as $i => $tema): ?>
                                <div class="input-group mb-2 punto-orden">
                                    <span class="input-group-text"><?= $i + 1 ?></span>
                                    <input type="hidden" name="orden_punto[]" value="<?= $i + 1 ?>">
                                    <input type="text" class="form-control" name="orden_tema[]" value="<?= esc($tema) ?>">
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

                <!-- Proxima reunion -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>Proxima Reunion</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Fecha</label>
                                <input type="date" class="form-control" name="proxima_reunion_fecha" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
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
                        </div>
                        <?php else: ?>
                            <!-- Quorum requerido -->
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
                            <?php foreach ($principales as $m): ?>
                            <div class="form-check mb-2">
                                <input type="hidden" name="asistentes[]" value="<?= $m['id_miembro'] ?>">
                                <input class="form-check-input asistente-check" type="checkbox"
                                       name="asistio[<?= $m['id_miembro'] ?>]" value="1"
                                       id="miembro_<?= $m['id_miembro'] ?>" checked>
                                <label class="form-check-label" for="miembro_<?= $m['id_miembro'] ?>">
                                    <?= esc($m['nombre_completo']) ?>
                                    <?php if ($m['rol_comite'] !== 'miembro'): ?>
                                        <span class="badge bg-warning text-dark"><?= ucfirst($m['rol_comite']) ?></span>
                                    <?php endif; ?>
                                    <br><small class="text-muted"><?= esc($m['cargo']) ?></small>
                                </label>
                            </div>
                            <?php endforeach; ?>

                            <?php if (!empty($suplentes)): ?>
                            <h6 class="text-muted mb-2 mt-3">Suplentes</h6>
                            <?php foreach ($suplentes as $m): ?>
                            <div class="form-check mb-2">
                                <input type="hidden" name="asistentes[]" value="<?= $m['id_miembro'] ?>">
                                <input class="form-check-input asistente-check" type="checkbox"
                                       name="asistio[<?= $m['id_miembro'] ?>]" value="1"
                                       id="miembro_<?= $m['id_miembro'] ?>">
                                <label class="form-check-label" for="miembro_<?= $m['id_miembro'] ?>">
                                    <?= esc($m['nombre_completo']) ?>
                                    <br><small class="text-muted"><?= esc($m['cargo']) ?></small>
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
                    <button type="submit" class="btn btn-primary btn-lg" id="btnCrear">
                        <i class="bi bi-save me-2"></i>Crear Acta
                    </button>
                    <a href="<?= base_url('miembro/comite/' . $comite['id_comite']) ?>" class="btn btn-outline-secondary">
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

document.querySelector('select[name="modalidad"]')?.addEventListener('change', toggleEnlace);

// Agregar punto al orden del dia
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

// Eliminar punto del orden del dia
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

// Envio del formulario via AJAX
document.getElementById('formActa').addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn = document.getElementById('btnCrear');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creando...';

    try {
        const formData = new FormData(this);

        const response = await fetch('<?= base_url('miembro/comite/' . $comite['id_comite'] . '/guardar-acta') ?>', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert('Acta creada exitosamente');
            window.location.href = result.redirect;
        } else {
            alert('Error: ' + result.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save me-2"></i>Crear Acta';
        }
    } catch (error) {
        alert('Error de conexion: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-2"></i>Crear Acta';
    }
});
</script>

<?= $this->endSection() ?>
