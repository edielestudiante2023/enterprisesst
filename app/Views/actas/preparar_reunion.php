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
                    <li class="breadcrumb-item active">Preparar Reunion</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1"><i class="bi bi-journal-text me-2"></i>Preparar Reunion de <?= esc($comite['tipo_nombre']) ?></h1>
                    <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna izquierda: Instructivo -->
        <div class="col-lg-5 mb-4">
            <!-- Instructivo Principal -->
            <div class="card border-0 shadow-sm border-start border-4 border-primary mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Como hacer un Acta de Reunion Efectiva</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Importante:</strong> Un acta bien elaborada es evidencia legal del SG-SST y garantiza el seguimiento de compromisos.
                    </div>

                    <!-- Paso 1 -->
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <span class="badge bg-primary rounded-circle p-2 fs-5">1</span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="fw-bold text-primary">ANTES DE LA REUNION</h6>
                            <ul class="mb-0 small">
                                <li><strong>Defina el objetivo:</strong> ¿Para que se reunen? Sea especifico.</li>
                                <li><strong>Prepare el orden del dia:</strong> Liste los temas a tratar en orden de prioridad.</li>
                                <li><strong>Revise compromisos anteriores:</strong> ¿Que quedo pendiente?</li>
                                <li><strong>Convoque con anticipacion:</strong> Minimo 48 horas antes.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Paso 2 -->
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <span class="badge bg-success rounded-circle p-2 fs-5">2</span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="fw-bold text-success">DURANTE LA REUNION</h6>
                            <ul class="mb-0 small">
                                <li><strong>Verifique quorum:</strong> Debe haber minimo la mitad + 1 de los miembros principales.</li>
                                <li><strong>Siga el orden del dia:</strong> No se desvie de los temas planificados.</li>
                                <li><strong>Registre decisiones:</strong> Anote cada acuerdo tomado.</li>
                                <li><strong>Asigne compromisos DE INMEDIATO:</strong> No lo deje para despues. Defina QUE, QUIEN y CUANDO.</li>
                                <li><strong>Controle el tiempo:</strong> Respete la hora de inicio y fin.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Paso 3 -->
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <span class="badge bg-warning text-dark rounded-circle p-2 fs-5">3</span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="fw-bold text-warning">DESPUES DE LA REUNION</h6>
                            <ul class="mb-0 small">
                                <li><strong>Complete el desarrollo:</strong> Describa lo tratado en cada punto.</li>
                                <li><strong>Verifique compromisos:</strong> Asegurese que todos quedaron registrados.</li>
                                <li><strong>Envie a firmas:</strong> Todos los asistentes deben firmar.</li>
                                <li><strong>Haga seguimiento:</strong> Revise el cumplimiento de compromisos.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tips de Reuniones Efectivas -->
            <div class="card border-0 shadow-sm border-start border-4 border-info">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0 text-info"><i class="bi bi-clock-history me-2"></i>Tips para Reuniones Efectivas</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <i class="bi bi-hourglass-split text-info fs-3"></i>
                                <p class="small mb-0 mt-2"><strong>Duracion ideal:</strong><br>45-60 minutos</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <i class="bi bi-list-ol text-info fs-3"></i>
                                <p class="small mb-0 mt-2"><strong>Temas por reunion:</strong><br>3 a 5 maximo</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <i class="bi bi-people text-info fs-3"></i>
                                <p class="small mb-0 mt-2"><strong>Participantes:</strong><br>Solo los necesarios</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <i class="bi bi-calendar-check text-info fs-3"></i>
                                <p class="small mb-0 mt-2"><strong>Periodicidad:</strong><br>Mensual minimo</p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="alert alert-warning mb-0 small">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Evite:</strong> Reuniones sin objetivo claro, sin orden del dia, sin control de tiempo, o donde no se asignan responsables a los compromisos.
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Preparacion -->
        <div class="col-lg-7">
            <!-- Compromisos Pendientes de Reuniones Anteriores -->
            <?php if (!empty($compromisosPendientes)): ?>
            <div class="card border-0 shadow-sm mb-4 border-start border-4 border-danger">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-exclamation-circle me-2"></i>Compromisos Pendientes (<?= count($compromisosPendientes) ?>)</h5>
                    <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $comite['id_comite'] . '/compromisos') ?>" class="btn btn-sm btn-light">
                        <i class="bi bi-arrow-right"></i> Ver Dashboard
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Acta</th>
                                    <th>Compromiso</th>
                                    <th>Responsable</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($compromisosPendientes, 0, 5) as $comp): ?>
                                <tr class="<?= strtotime($comp['fecha_vencimiento']) < time() ? 'table-danger' : '' ?>">
                                    <td><small><?= esc($comp['numero_acta'] ?? '-') ?></small></td>
                                    <td><small><?= esc(substr($comp['descripcion'], 0, 40)) ?>...</small></td>
                                    <td><small><?= esc($comp['responsable_nombre'] ?? '-') ?></small></td>
                                    <td>
                                        <small>
                                            <?= date('d/m/Y', strtotime($comp['fecha_vencimiento'])) ?>
                                            <?php if (strtotime($comp['fecha_vencimiento']) < time()): ?>
                                                <span class="badge bg-danger">Vencido</span>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = match($comp['estado']) {
                                            'pendiente' => 'bg-secondary',
                                            'en_proceso' => 'bg-info',
                                            'vencido' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= ucfirst($comp['estado']) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($compromisosPendientes) > 5): ?>
                    <div class="text-center py-2 bg-light">
                        <small class="text-muted">Mostrando 5 de <?= count($compromisosPendientes) ?> compromisos pendientes</small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Formulario de Preparacion -->
            <form action="<?= base_url('actas/comite/' . $comite['id_comite'] . '/nueva-acta') ?>" method="get" id="formPreparar">
                <!-- Objetivo de la Reunion -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-bullseye me-2 text-primary"></i>Paso 1: Defina el Objetivo</h5>
                    </div>
                    <div class="card-body">
                        <label class="form-label">¿Cual es el proposito principal de esta reunion?</label>
                        <textarea class="form-control" name="objetivo" rows="2"
                                  placeholder="Ej: Revisar el cumplimiento del plan de trabajo del primer trimestre y aprobar las acciones correctivas propuestas..."></textarea>
                        <small class="text-muted">Este objetivo aparecera en el encabezado del acta.</small>
                    </div>
                </div>

                <!-- Orden del Dia -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list-ol me-2 text-success"></i>Paso 2: Orden del Dia</h5>
                        <button type="button" class="btn btn-sm btn-success" onclick="agregarPunto()">
                            <i class="bi bi-plus-lg"></i> Agregar Punto
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Defina los temas a tratar en orden de prioridad. Se recomienda entre 3 y 5 puntos por reunion.
                        </p>

                        <div id="ordenDelDia">
                            <!-- Puntos predeterminados -->
                            <div class="input-group mb-2 punto-orden">
                                <span class="input-group-text bg-success text-white">1</span>
                                <input type="text" class="form-control" name="punto[]" value="Verificacion de quorum y aprobacion del orden del dia" readonly>
                                <span class="input-group-text text-muted"><i class="bi bi-lock"></i></span>
                            </div>
                            <div class="input-group mb-2 punto-orden">
                                <span class="input-group-text bg-success text-white">2</span>
                                <input type="text" class="form-control" name="punto[]" value="Lectura y aprobacion del acta anterior">
                                <button type="button" class="btn btn-outline-danger" onclick="quitarPunto(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="input-group mb-2 punto-orden">
                                <span class="input-group-text bg-success text-white">3</span>
                                <input type="text" class="form-control" name="punto[]" value="Revision de compromisos pendientes de actas anteriores">
                                <button type="button" class="btn btn-outline-danger" onclick="quitarPunto(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div id="nuevosPuntos">
                            <!-- Aqui se agregan puntos dinamicamente -->
                        </div>

                        <div class="input-group mb-2">
                            <span class="input-group-text bg-light" id="numeroPropuestas">Propuestas y varios</span>
                            <input type="text" class="form-control" name="punto[]" value="Proposiciones y varios" readonly>
                            <span class="input-group-text text-muted"><i class="bi bi-lock"></i></span>
                        </div>
                    </div>
                </div>

                <!-- Informacion de la Reunion -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-calendar-event me-2 text-info"></i>Paso 3: Datos de la Reunion</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Fecha</label>
                                <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Hora Inicio</label>
                                <input type="time" class="form-control" name="hora_inicio" value="08:00" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Hora Fin (estimada)</label>
                                <input type="time" class="form-control" name="hora_fin" value="09:00" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lugar</label>
                                <input type="text" class="form-control" name="lugar" placeholder="Ej: Sala de juntas principal" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Modalidad</label>
                                <select class="form-select" name="modalidad" id="modalidad" onchange="toggleEnlaceVirtual()">
                                    <option value="presencial">Presencial</option>
                                    <option value="virtual">Virtual</option>
                                    <option value="hibrida">Hibrida</option>
                                </select>
                            </div>
                            <div class="col-12" id="divEnlaceVirtual" style="display: none;">
                                <label class="form-label">Enlace de la reunion virtual</label>
                                <input type="url" class="form-control" name="enlace_virtual" placeholder="https://meet.google.com/xxx o https://zoom.us/j/xxx">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Accion -->
                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $comite['id_comite']) ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Volver al Comite
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-pencil-square me-2"></i>Crear Acta y Continuar
                    </button>
                </div>

                <div class="alert alert-light mt-3 small">
                    <i class="bi bi-info-circle me-2"></i>
                    Al hacer clic en "Crear Acta y Continuar", se creara el acta con la informacion proporcionada.
                    Luego podra desarrollar cada punto, agregar mas temas si es necesario, asignar compromisos y enviar a firmas.
                    <strong>Puede guardar y volver a editar cuantas veces necesite antes de cerrar el acta.</strong>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let puntosCount = 3;

function agregarPunto() {
    puntosCount++;
    const html = `
        <div class="input-group mb-2 punto-orden punto-nuevo">
            <span class="input-group-text bg-success text-white punto-numero">${puntosCount}</span>
            <input type="text" class="form-control" name="punto[]" placeholder="Describa el tema a tratar..." required>
            <button type="button" class="btn btn-outline-danger" onclick="quitarPunto(this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    document.getElementById('nuevosPuntos').insertAdjacentHTML('beforeend', html);
    renumerarPuntos();
}

function quitarPunto(btn) {
    btn.closest('.input-group').remove();
    renumerarPuntos();
}

function renumerarPuntos() {
    const puntos = document.querySelectorAll('.punto-orden');
    puntos.forEach((punto, index) => {
        const numero = punto.querySelector('.punto-numero, .input-group-text:first-child');
        if (numero && !numero.classList.contains('bg-light')) {
            numero.textContent = index + 1;
        }
    });
    puntosCount = puntos.length;
}

function toggleEnlaceVirtual() {
    const modalidad = document.getElementById('modalidad').value;
    const divEnlace = document.getElementById('divEnlaceVirtual');
    divEnlace.style.display = (modalidad === 'virtual' || modalidad === 'hibrida') ? 'block' : 'none';
}
</script>

<?= $this->endSection() ?>
