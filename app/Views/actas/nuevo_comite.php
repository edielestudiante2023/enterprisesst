<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?= base_url('actas/' . $cliente['id_cliente']) ?>">Comites</a></li>
                    <li class="breadcrumb-item active">Nuevo Comite</li>
                </ol>
            </nav>
            <h1 class="h3 mb-1">Nuevo Comite</h1>
            <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?></p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form action="<?= base_url('actas/' . $cliente['id_cliente'] . '/guardar-comite') ?>" method="post">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Informacion del Comite</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo de Comite <span class="text-danger">*</span></label>
                                <select class="form-select" name="id_tipo" id="tipoComite" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($tiposComite as $tipo): ?>
                                    <option value="<?= $tipo['id_tipo'] ?>"
                                            data-requiere-paridad="<?= !empty($tipo['requiere_paridad']) ? '1' : '0' ?>"
                                            data-periodicidad="<?= $tipo['periodicidad_dias'] ?? 30 ?>">
                                        <?= esc($tipo['nombre']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted" id="infoTipo"></small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Codigo Interno</label>
                                <input type="text" class="form-control" name="codigo"
                                       placeholder="Ej: COPASST-2024, COCOLAB-001..."
                                       maxlength="50">
                                <small class="text-muted">Identificador unico para este comite</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha de Conformacion</label>
                                <input type="date" class="form-control" name="fecha_conformacion"
                                       value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vigencia (anos)</label>
                                <select class="form-select" name="vigencia_anos">
                                    <option value="1">1 ano</option>
                                    <option value="2" selected>2 anos</option>
                                    <option value="3">3 anos</option>
                                    <option value="4">4 anos</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Periodicidad de Reuniones (dias)</label>
                                <input type="number" class="form-control" name="periodicidad_efectiva"
                                       id="periodicidad" min="1" max="365" value="30">
                                <small class="text-muted">Cada cuantos dias se reunen</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lugar Habitual</label>
                                <input type="text" class="form-control" name="lugar_habitual"
                                       placeholder="Sala de reuniones, oficina principal...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info legal segun tipo -->
                <div class="card border-0 shadow-sm mb-4 d-none" id="infoLegal">
                    <div class="card-header bg-warning bg-opacity-25">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Requisitos Legales</h5>
                    </div>
                    <div class="card-body" id="infoLegalContent">
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-chat-text me-2"></i>Observaciones</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" name="observaciones" rows="3"
                                  placeholder="Notas adicionales sobre el comite..."></textarea>
                    </div>
                </div>

                <!-- Botones -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save me-2"></i>Crear Comite
                    </button>
                    <a href="<?= base_url('actas/' . $cliente['id_cliente']) ?>" class="btn btn-outline-secondary btn-lg">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

        <!-- Sidebar con info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tipos de Comite</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary">COPASST</h6>
                        <small class="text-muted">
                            Comite Paritario de Seguridad y Salud en el Trabajo.
                            Obligatorio para empresas con mas de 10 trabajadores.
                            Reuniones mensuales.
                        </small>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h6 class="text-success">COCOLAB</h6>
                        <small class="text-muted">
                            Comite de Convivencia Laboral.
                            Obligatorio segun Resoluciones 652 y 1356 de 2012.
                            Reuniones trimestrales minimo.
                        </small>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h6 class="text-warning">Brigada de Emergencias</h6>
                        <small class="text-muted">
                            Grupo de trabajadores capacitados para atender emergencias.
                            Reuniones de coordinacion y capacitacion.
                        </small>
                    </div>
                    <hr>
                    <div class="mb-0">
                        <h6 class="text-info">Actas Generales</h6>
                        <small class="text-muted">
                            Para reuniones de seguimiento general de SST,
                            inspecciones, y otras actividades.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Comités existentes -->
            <?php if (!empty($comitesExistentes)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-folder me-2"></i>Comites Existentes</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($comitesExistentes as $comite): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= esc($comite['tipo_nombre']) ?></strong>
                                <br><small class="text-muted"><?= esc($comite['codigo'] ?? 'Sin codigo') ?></small>
                            </div>
                            <span class="badge <?= $comite['activo'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $comite['activo'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const infoLegalPorTipo = {
    'COPASST': `
        <ul class="mb-0">
            <li>Obligatorio para empresas con mas de 10 trabajadores</li>
            <li>Conformacion paritaria: igual numero de representantes del empleador y trabajadores</li>
            <li>Vigencia de 2 anos</li>
            <li>Reuniones mensuales obligatorias</li>
            <li>Quorum: mitad mas uno de los miembros</li>
            <li>Normativa: Decreto 1072 de 2015, Resolucion 2013 de 1986</li>
        </ul>
    `,
    'COCOLAB': `
        <ul class="mb-0">
            <li>Obligatorio para todas las empresas</li>
            <li>Conformacion bipartita</li>
            <li>Minimo 2 representantes del empleador y 2 de los trabajadores</li>
            <li>Reuniones trimestrales ordinarias</li>
            <li>Reuniones extraordinarias cuando se requiera</li>
            <li>Normativa: Resolucion 652 de 2012, Resolucion 1356 de 2012</li>
        </ul>
    `,
    'Brigada de Emergencias': `
        <ul class="mb-0">
            <li>Grupo organizado de trabajadores capacitados</li>
            <li>Minimo recomendado: 10% de la poblacion trabajadora</li>
            <li>Debe recibir capacitacion en primeros auxilios, evacuacion, y control de incendios</li>
            <li>Reuniones periodicas de coordinacion y entrenamiento</li>
        </ul>
    `,
    'Actas Generales': `
        <ul class="mb-0">
            <li>Flexibilidad en conformacion y periodicidad</li>
            <li>Para reuniones de seguimiento de SST</li>
            <li>Inspecciones de seguridad</li>
            <li>Actividades varias del SG-SST</li>
        </ul>
    `
};

document.getElementById('tipoComite').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const periodicidad = selected.dataset.periodicidad;
    const requiereParidad = selected.dataset.requiereParidad === '1';
    const nombreTipo = selected.text;

    // Actualizar periodicidad sugerida
    if (periodicidad) {
        document.getElementById('periodicidad').value = periodicidad;
    }

    // Mostrar info legal
    const infoLegal = document.getElementById('infoLegal');
    const infoLegalContent = document.getElementById('infoLegalContent');

    if (infoLegalPorTipo[nombreTipo]) {
        infoLegalContent.innerHTML = infoLegalPorTipo[nombreTipo];
        infoLegal.classList.remove('d-none');
    } else {
        infoLegal.classList.add('d-none');
    }

    // Info adicional
    const infoTipo = document.getElementById('infoTipo');
    if (requiereParidad) {
        infoTipo.innerHTML = '<span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Este comite requiere conformacion paritaria</span>';
    } else {
        infoTipo.innerHTML = '';
    }
});
</script>

<?= $this->endSection() ?>
