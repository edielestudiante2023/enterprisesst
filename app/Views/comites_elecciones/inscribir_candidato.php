<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<?php
// Iconos y colores por tipo de comite
$iconos = [
    'COPASST' => 'bi-shield-check text-success',
    'COCOLAB' => 'bi-chat-heart text-warning',
    'BRIGADA' => 'bi-fire text-danger',
    'VIGIA' => 'bi-person-badge text-info'
];
$colores = [
    'COPASST' => 'success',
    'COCOLAB' => 'warning',
    'BRIGADA' => 'danger',
    'VIGIA' => 'info'
];
$colorTema = $colores[$proceso['tipo_comite']] ?? 'primary';
$icono = $iconos[$proceso['tipo_comite']] ?? 'bi-people';

// Texto segun representacion
$esEmpleador = $representacion === 'empleador';
$tituloFormulario = $esEmpleador ? 'Designar Representante del Empleador' : 'Inscribir Candidato Trabajadores';
$subtitulo = $esEmpleador
    ? 'Los representantes del empleador son designados directamente por la gerencia'
    : 'Los candidatos trabajadores participaran en el proceso de votacion';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente']) ?>">Comites</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/proceso/' . $proceso['id_proceso']) ?>">
                            <?= $proceso['tipo_comite'] ?> <?= $proceso['anio'] ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Inscribir Candidato</li>
                </ol>
            </nav>
            <h1 class="h3 mb-1">
                <i class="bi <?= $icono ?> me-2"></i>
                <?= $tituloFormulario ?>
            </h1>
            <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?> - <?= $proceso['tipo_comite'] ?> <?= $proceso['anio'] ?></p>
        </div>
    </div>

    <!-- Mensajes flash -->
    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Info de plazas -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="alert alert-<?= $colorTema ?> border-0">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle fs-4 me-3"></i>
                    <div>
                        <strong><?= $subtitulo ?></strong>
                        <p class="mb-0 small">
                            Plazas <?= $esEmpleador ? 'empleador' : 'trabajadores' ?>:
                            <strong><?= $proceso['plazas_principales'] ?></strong> principales +
                            <strong><?= $proceso['plazas_suplentes'] ?></strong> suplentes
                            = <strong><?= $plazasTotal ?></strong> total
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body py-2">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="mb-0 text-<?= $colorTema ?>"><?= count($candidatosInscritos) ?></h4>
                            <small class="text-muted">Inscritos</small>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-0 text-success"><?= $plazasDisponibles ?></h4>
                            <small class="text-muted">Disponibles</small>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-0"><?= $plazasTotal ?></h4>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Formulario de inscripcion -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-<?= $colorTema ?> <?= $colorTema === 'warning' ? 'text-dark' : 'text-white' ?>">
                    <h5 class="mb-0">
                        <i class="bi bi-person-plus me-2"></i>
                        <?= $esEmpleador ? 'Datos del Representante' : 'Datos del Candidato' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('comites-elecciones/guardar-candidato') ?>" method="post" enctype="multipart/form-data" id="formInscripcion">
                        <input type="hidden" name="id_proceso" value="<?= $proceso['id_proceso'] ?>">
                        <input type="hidden" name="representacion" value="<?= $representacion ?>">

                        <!-- Foto del candidato -->
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <label class="form-label fw-bold d-block">Foto del Candidato</label>
                                <div class="position-relative d-inline-block">
                                    <img id="previewFoto" src="<?= base_url('uploads/default-avatar.png') ?>"
                                         class="rounded-circle border shadow-sm"
                                         style="width: 150px; height: 150px; object-fit: cover;"
                                         alt="Foto candidato">
                                    <label for="foto" class="position-absolute bottom-0 end-0 btn btn-sm btn-<?= $colorTema ?> rounded-circle"
                                           style="width: 40px; height: 40px; cursor: pointer;">
                                        <i class="bi bi-camera"></i>
                                    </label>
                                </div>
                                <input type="file" class="d-none" id="foto" name="foto" accept="image/*">
                                <div class="form-text mt-2">JPG, PNG. Max 2MB</div>
                            </div>
                            <div class="col-md-8">
                                <!-- Nombres y Apellidos -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nombres" class="form-label fw-bold">Nombres <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nombres" name="nombres" required
                                               value="<?= old('nombres') ?>" placeholder="Ej: Juan Carlos">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="apellidos" class="form-label fw-bold">Apellidos <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="apellidos" name="apellidos" required
                                               value="<?= old('apellidos') ?>" placeholder="Ej: Perez Garcia">
                                    </div>
                                </div>
                                <!-- Documento -->
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="tipo_documento" class="form-label fw-bold">Tipo Doc.</label>
                                        <select class="form-select" id="tipo_documento" name="tipo_documento">
                                            <option value="CC" selected>C.C.</option>
                                            <option value="CE">C.E.</option>
                                            <option value="TI">T.I.</option>
                                            <option value="PA">Pasaporte</option>
                                            <option value="PEP">PEP</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <label for="documento_identidad" class="form-label fw-bold">No. Documento <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="documento_identidad" name="documento_identidad" required
                                               value="<?= old('documento_identidad') ?>" placeholder="Ej: 1234567890">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cargo y Area -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cargo" class="form-label fw-bold">Cargo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="cargo" name="cargo" required
                                       value="<?= old('cargo') ?>" placeholder="Ej: Operario de produccion">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="area" class="form-label fw-bold">Area/Departamento</label>
                                <input type="text" class="form-control" id="area" name="area"
                                       value="<?= old('area') ?>" placeholder="Ej: Produccion">
                            </div>
                        </div>

                        <!-- Contacto -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-bold">Correo Electronico</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= old('email') ?>" placeholder="candidato@empresa.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label fw-bold">Telefono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono"
                                       value="<?= old('telefono') ?>" placeholder="3001234567">
                            </div>
                        </div>

                        <?php if ($esEmpleador): ?>
                        <!-- Tipo de plaza - SOLO para representantes del empleador (designados) -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tipo de Plaza <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="tipo_plaza" id="plazaPrincipal" value="principal" checked>
                                    <label class="btn btn-outline-<?= $colorTema ?>" for="plazaPrincipal">
                                        <i class="bi bi-star-fill me-1"></i>Principal
                                    </label>
                                    <input type="radio" class="btn-check" name="tipo_plaza" id="plazaSuplente" value="suplente">
                                    <label class="btn btn-outline-<?= $colorTema ?>" for="plazaSuplente">
                                        <i class="bi bi-star me-1"></i>Suplente
                                    </label>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Para trabajadores: tipo_plaza se determina automaticamente en el escrutinio -->
                        <input type="hidden" name="tipo_plaza" value="principal">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>El tipo de plaza (principal/suplente) se determinara automaticamente segun los votos obtenidos en el escrutinio.</small>
                        </div>
                        <?php endif; ?>

                        <?php if ($requiereCertificado50h && !$esEmpleador): ?>
                        <!-- Nota sobre certificado 50 horas - Solo informativo -->
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Nota:</strong> Si el candidato resulta elegido, debera acreditar el certificado de 50 horas en SST
                            segun el Decreto 1072 de 2015 y la Resolucion 0312 de 2019.
                        </div>
                        <?php endif ?>

                        <?php if ($esEmpleador): ?>
                        <!-- Certificado 50 horas SST - Solo para representantes del empleador (designados directamente) -->
                        <div class="card border-warning mb-3">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="bi bi-award me-2"></i>
                                    Certificado 50 Horas SST
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="tiene_certificado_50h" name="tiene_certificado_50h" value="1">
                                    <label class="form-check-label" for="tiene_certificado_50h">
                                        El representante cuenta con certificado de 50 horas en SST
                                    </label>
                                </div>

                                <div id="seccionCertificado" class="d-none">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="fecha_certificado_50h" class="form-label">Fecha del Certificado</label>
                                            <input type="date" class="form-control" id="fecha_certificado_50h" name="fecha_certificado_50h">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="institucion_certificado" class="form-label">Institucion que Certifica</label>
                                            <input type="text" class="form-control" id="institucion_certificado" name="institucion_certificado"
                                                   placeholder="Ej: SENA, ARL Positiva...">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="archivo_certificado_50h" class="form-label">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>Adjuntar Certificado (PDF/Imagen)
                                        </label>
                                        <input type="file" class="form-control" id="archivo_certificado_50h" name="archivo_certificado_50h"
                                               accept=".pdf,.jpg,.jpeg,.png">
                                        <div class="form-text">Formatos: PDF, JPG, PNG. Max 5MB</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Observaciones -->
                        <div class="mb-3">
                            <label for="observaciones" class="form-label fw-bold">Observaciones (opcional)</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="2"
                                      placeholder="Notas adicionales sobre el candidato..."><?= old('observaciones') ?></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/proceso/' . $proceso['id_proceso']) ?>"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-<?= $colorTema ?> <?= $colorTema === 'warning' ? 'text-dark' : '' ?>"
                                    id="btnGuardar" <?= $plazasDisponibles <= 0 ? 'disabled' : '' ?>>
                                <i class="bi bi-check-lg me-1"></i>
                                <?= $esEmpleador ? 'Designar Representante' : 'Inscribir Candidato' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista de candidatos inscritos -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-people me-2"></i>
                        <?= $esEmpleador ? 'Representantes Designados' : 'Candidatos Inscritos' ?>
                        <span class="badge bg-<?= $colorTema ?> ms-2"><?= count($candidatosInscritos) ?></span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($candidatosInscritos)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($candidatosInscritos as $candidato): ?>
                        <li class="list-group-item d-flex align-items-center py-3">
                            <?php if (!empty($candidato['foto'])): ?>
                            <img src="<?= base_url($candidato['foto']) ?>" class="rounded-circle me-3"
                                 style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-3"
                                 style="width: 50px; height: 50px;">
                                <i class="bi bi-person"></i>
                            </div>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <strong><?= esc($candidato['nombres'] . ' ' . $candidato['apellidos']) ?></strong>
                                <br>
                                <small class="text-muted"><?= esc($candidato['cargo']) ?></small>
                                <?php if ($esEmpleador): ?>
                                <span class="badge bg-<?= $candidato['tipo_plaza'] === 'principal' ? 'primary' : 'secondary' ?> ms-1">
                                    <?= ucfirst($candidato['tipo_plaza']) ?>
                                </span>
                                <?php else: ?>
                                <span class="badge bg-<?= $candidato['estado'] === 'aprobado' ? 'success' : 'warning' ?> ms-1">
                                    <?= ucfirst($candidato['estado']) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($candidato['tiene_certificado_50h']): ?>
                            <span class="badge bg-success" title="Certificado 50h">
                                <i class="bi bi-award"></i>
                            </span>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-person-plus text-muted" style="font-size: 2.5rem;"></i>
                        <p class="text-muted mt-2 mb-0">
                            No hay <?= $esEmpleador ? 'representantes' : 'candidatos' ?> inscritos aun
                        </p>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($plazasDisponibles <= 0): ?>
                <div class="card-footer bg-warning text-dark">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Se han cubierto todas las plazas disponibles
                </div>
                <?php endif; ?>
            </div>

            <!-- Info adicional -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informacion</h6>
                </div>
                <div class="card-body small">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Plazas principales: <strong><?= $proceso['plazas_principales'] ?></strong>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Plazas suplentes: <strong><?= $proceso['plazas_suplentes'] ?></strong>
                        </li>
                        <?php if ($requiereCertificado50h): ?>
                        <li class="mb-2">
                            <i class="bi bi-award text-warning me-1"></i>
                            Certificado 50h SST: <strong>Obligatorio</strong>
                        </li>
                        <?php endif; ?>
                        <li>
                            <i class="bi bi-calendar text-info me-1"></i>
                            Periodo: <?= date('d/m/Y', strtotime($proceso['fecha_inicio_periodo'])) ?>
                            - <?= date('d/m/Y', strtotime($proceso['fecha_fin_periodo'])) ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview de foto
document.getElementById('foto').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewFoto').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Toggle seccion certificado 50h
document.getElementById('tiene_certificado_50h')?.addEventListener('change', function() {
    const seccion = document.getElementById('seccionCertificado');
    if (this.checked) {
        seccion.classList.remove('d-none');
    } else {
        seccion.classList.add('d-none');
    }
});

// Validacion antes de enviar
document.getElementById('formInscripcion').addEventListener('submit', function(e) {
    const btn = document.getElementById('btnGuardar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';
});
</script>

<?= $this->endSection() ?>
