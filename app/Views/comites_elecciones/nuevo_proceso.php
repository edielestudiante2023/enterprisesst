<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente']) ?>">Comites</a>
                    </li>
                    <li class="breadcrumb-item active">Nuevo Proceso</li>
                </ol>
            </nav>
            <h1 class="h3 mb-1">
                <?php
                $iconos = [
                    'COPASST' => 'bi-shield-check text-success',
                    'COCOLAB' => 'bi-chat-heart text-warning',
                    'BRIGADA' => 'bi-fire text-danger',
                    'VIGIA' => 'bi-person-badge text-info'
                ];
                $nombresCompletos = [
                    'COPASST' => 'Comite Paritario de Seguridad y Salud en el Trabajo',
                    'COCOLAB' => 'Comite de Convivencia Laboral',
                    'BRIGADA' => 'Brigada de Emergencias',
                    'VIGIA' => 'Vigia de Seguridad y Salud en el Trabajo'
                ];
                ?>
                <?php if ($tipoComite): ?>
                    <i class="bi <?= $iconos[$tipoComite] ?? 'bi-people' ?> me-2"></i>
                    Nuevo Proceso: <?= $tipoComite ?>
                <?php else: ?>
                    <i class="bi bi-plus-circle text-primary me-2"></i>
                    Nuevo Proceso de Conformacion
                <?php endif; ?>
            </h1>
            <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?></p>
        </div>
    </div>

    <!-- Mensajes flash -->
    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Alerta si ya existe proceso activo -->
    <?php if ($procesoExistente): ?>
    <div class="alert alert-warning" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Atencion:</strong> Ya existe un proceso de <?= $tipoComite ?> activo para el ano <?= $anioActual ?>.
        <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/proceso/' . $procesoExistente['id_proceso']) ?>"
           class="alert-link">Ver proceso existente</a>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-gear me-2"></i>Configuracion del Proceso
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('comites-elecciones/guardar-proceso') ?>" method="post">
                        <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">

                        <!-- Tipo de Comite -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Tipo de Comite</label>
                            <?php if ($tipoComite): ?>
                                <input type="hidden" name="tipo_comite" value="<?= $tipoComite ?>">
                                <div class="alert alert-light border">
                                    <div class="d-flex align-items-center">
                                        <i class="bi <?= $iconos[$tipoComite] ?> fs-3 me-3"></i>
                                        <div>
                                            <strong><?= $tipoComite ?></strong>
                                            <p class="mb-0 text-muted small"><?= $nombresCompletos[$tipoComite] ?? '' ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <select class="form-select" name="tipo_comite" required>
                                    <option value="">Seleccione...</option>
                                    <option value="COPASST" <?= $numTrabajadores < 10 ? 'disabled' : '' ?>>
                                        COPASST - Comite Paritario SST (10+ trabajadores)
                                    </option>
                                    <option value="VIGIA" <?= $numTrabajadores >= 10 ? 'disabled' : '' ?>>
                                        Vigia SST (1-9 trabajadores)
                                    </option>
                                    <option value="COCOLAB">COCOLAB - Comite Convivencia Laboral</option>
                                    <option value="BRIGADA">Brigada de Emergencias</option>
                                </select>
                            <?php endif; ?>
                        </div>

                        <!-- Ano -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Ano del Proceso</label>
                            <select class="form-select" name="anio" required>
                                <?php for ($y = $anioActual; $y <= $anioActual + 1; $y++): ?>
                                <option value="<?= $y ?>" <?= $y == $anioActual ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Plazas (solo para COPASST y COCOLAB) -->
                        <?php if ($tipoComite !== 'VIGIA' && $tipoComite !== 'BRIGADA'): ?>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Plazas Principales</label>
                                <input type="number" class="form-control" name="plazas_principales"
                                       value="<?= $plazasPorDefecto['principales'] ?? 2 ?>" min="1" max="10" required>
                                <div class="form-text">Por cada parte (empleador y trabajadores)</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Plazas Suplentes</label>
                                <input type="number" class="form-control" name="plazas_suplentes"
                                       value="<?= $plazasPorDefecto['suplentes'] ?? 2 ?>" min="1" max="10" required>
                                <div class="form-text">Por cada parte (empleador y trabajadores)</div>
                            </div>
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="plazas_principales" value="<?= $plazasPorDefecto['principales'] ?? 1 ?>">
                        <input type="hidden" name="plazas_suplentes" value="<?= $plazasPorDefecto['suplentes'] ?? 1 ?>">
                        <?php endif; ?>

                        <!-- Fecha inicio periodo -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Fecha Inicio del Periodo</label>
                            <input type="date" class="form-control" name="fecha_inicio_periodo"
                                   value="<?= date('Y-m-d') ?>" required>
                            <div class="form-text">El periodo del comite sera de 2 anos desde esta fecha.</div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente']) ?>"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" <?= $procesoExistente ? 'disabled' : '' ?>>
                                <i class="bi bi-check-lg me-1"></i>Crear Proceso
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel informativo -->
        <div class="col-lg-4">
            <!-- Info segun tipo -->
            <?php if ($tipoComite === 'COPASST'): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>COPASST - Informacion</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Periodo de vigencia: <strong>2 anos</strong>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Representacion paritaria (50% empleador, 50% trabajadores)
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Eleccion de representantes de trabajadores por <strong>votacion</strong>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Designacion de representantes del empleador
                        </li>
                        <li>
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Certificado 50 horas SST <strong>obligatorio</strong>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-list-ol me-2"></i>Etapas del Proceso</h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Configuracion:</strong> Definir plazas y fechas</li>
                        <li class="mb-2"><strong>Inscripcion:</strong> Registro de candidatos trabajadores</li>
                        <li class="mb-2"><strong>Votacion:</strong> Enlace de 24 horas para votar</li>
                        <li class="mb-2"><strong>Escrutinio:</strong> Conteo y resultados</li>
                        <li class="mb-2"><strong>Designacion:</strong> Representantes del empleador</li>
                        <li class="mb-2"><strong>Firmas:</strong> Acta de constitucion</li>
                        <li><strong>Completado:</strong> Comite conformado</li>
                    </ol>
                </div>
            </div>

            <?php elseif ($tipoComite === 'COCOLAB'): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>COCOLAB - Informacion</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Periodo de vigencia: <strong>2 anos</strong>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Resolucion 652 de 2012 / Resolucion 3461 de 2025
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Representacion paritaria (empleador y trabajadores)
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Funcion: Prevencion del acoso laboral
                        </li>
                        <li>
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Reuniones <strong>trimestrales</strong> minimo
                        </li>
                    </ul>
                </div>
            </div>

            <?php elseif ($tipoComite === 'VIGIA'): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Vigia SST - Informacion</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Para empresas de <strong>1-9 trabajadores</strong>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Designacion directa</strong> por el empleador
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            No requiere proceso de votacion
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Mismas funciones del COPASST
                        </li>
                        <li>
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Certificado 50 horas SST <strong>obligatorio</strong>
                        </li>
                    </ul>
                </div>
            </div>

            <?php elseif ($tipoComite === 'BRIGADA'): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Brigada - Informacion</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Brigada <strong>integral</strong> de emergencias
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Proceso de <strong>voluntariado</strong>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Designacion directa por el empleador
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Roles: Evacuacion, primeros auxilios, control de incendios
                        </li>
                        <li>
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Capacitacion especializada recomendada
                        </li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <!-- Normativa -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-journal-bookmark me-2"></i>Marco Normativo</h6>
                </div>
                <div class="card-body small">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-1"><strong>Resolucion 2013 de 1986:</strong> COPASST</li>
                        <li class="mb-1"><strong>Decreto 1072 de 2015:</strong> Reglamento SST</li>
                        <li class="mb-1"><strong>Resolucion 652 de 2012:</strong> COCOLAB</li>
                        <li class="mb-1"><strong>Resolucion 3461 de 2025:</strong> Modifica COCOLAB</li>
                        <li><strong>Resolucion 0312 de 2019:</strong> Estandares Minimos</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
