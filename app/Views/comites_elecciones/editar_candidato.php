<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<?php
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
$esEmpleador = $candidato['representacion'] === 'empleador';
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
                    <li class="breadcrumb-item active">Editar Candidato</li>
                </ol>
            </nav>
            <h1 class="h3 mb-1">
                <i class="bi bi-pencil-square me-2"></i>
                Editar <?= $esEmpleador ? 'Representante' : 'Candidato' ?>
            </h1>
            <p class="text-muted mb-0"><?= esc($candidato['nombres'] . ' ' . $candidato['apellidos']) ?></p>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-<?= $colorTema ?> <?= $colorTema === 'warning' ? 'text-dark' : 'text-white' ?>">
                    <h5 class="mb-0">
                        <i class="bi bi-person-lines-fill me-2"></i>
                        Datos del <?= $esEmpleador ? 'Representante' : 'Candidato' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('comites-elecciones/candidato/' . $candidato['id_candidato'] . '/actualizar') ?>" method="post" enctype="multipart/form-data">

                        <!-- Foto del candidato -->
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <label class="form-label fw-bold d-block">Foto</label>
                                <div class="position-relative d-inline-block">
                                    <?php if (!empty($candidato['foto'])): ?>
                                    <img id="previewFoto" src="<?= base_url($candidato['foto']) ?>"
                                         class="rounded-circle border shadow-sm"
                                         style="width: 150px; height: 150px; object-fit: cover;"
                                         alt="Foto candidato">
                                    <?php else: ?>
                                    <div id="previewFoto" class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center border shadow-sm"
                                         style="width: 150px; height: 150px;">
                                        <i class="bi bi-person" style="font-size: 4rem;"></i>
                                    </div>
                                    <?php endif; ?>
                                    <label for="foto" class="position-absolute bottom-0 end-0 btn btn-sm btn-<?= $colorTema ?> rounded-circle"
                                           style="width: 40px; height: 40px; cursor: pointer;">
                                        <i class="bi bi-camera"></i>
                                    </label>
                                </div>
                                <input type="file" class="d-none" id="foto" name="foto" accept="image/*">
                                <div class="form-text mt-2">Cambiar foto (opcional)</div>
                            </div>
                            <div class="col-md-8">
                                <!-- Nombres y Apellidos -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nombres" class="form-label fw-bold">Nombres <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nombres" name="nombres" required
                                               value="<?= esc($candidato['nombres']) ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="apellidos" class="form-label fw-bold">Apellidos <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="apellidos" name="apellidos" required
                                               value="<?= esc($candidato['apellidos']) ?>">
                                    </div>
                                </div>
                                <!-- Documento (solo lectura) -->
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-bold">Documento de Identidad</label>
                                        <input type="text" class="form-control" readonly disabled
                                               value="<?= esc($candidato['documento_identidad']) ?>">
                                        <div class="form-text">El documento no se puede modificar</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cargo y Area -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cargo" class="form-label fw-bold">Cargo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="cargo" name="cargo" required
                                       value="<?= esc($candidato['cargo']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="area" class="form-label fw-bold">Area/Departamento</label>
                                <input type="text" class="form-control" id="area" name="area"
                                       value="<?= esc($candidato['area']) ?>">
                            </div>
                        </div>

                        <!-- Contacto -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-bold">Correo Electronico</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= esc($candidato['email']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label fw-bold">Telefono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono"
                                       value="<?= esc($candidato['telefono']) ?>">
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/proceso/' . $proceso['id_proceso']) ?>"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-<?= $colorTema ?> <?= $colorTema === 'warning' ? 'text-dark' : '' ?>">
                                <i class="bi bi-check-lg me-1"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info lateral -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informacion</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <small class="text-muted">Representacion:</small>
                            <br><strong><?= ucfirst($candidato['representacion']) ?></strong>
                        </li>
                        <li class="mb-2">
                            <small class="text-muted">Estado:</small>
                            <br><span class="badge bg-success"><?= ucfirst($candidato['estado']) ?></span>
                        </li>
                        <li class="mb-2">
                            <small class="text-muted">Proceso:</small>
                            <br><strong><?= $proceso['tipo_comite'] ?> <?= $proceso['anio'] ?></strong>
                        </li>
                        <li>
                            <small class="text-muted">Registrado:</small>
                            <br><strong><?= date('d/m/Y H:i', strtotime($candidato['created_at'])) ?></strong>
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
            const preview = document.getElementById('previewFoto');
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                // Reemplazar div placeholder por imagen
                const img = document.createElement('img');
                img.id = 'previewFoto';
                img.src = e.target.result;
                img.className = 'rounded-circle border shadow-sm';
                img.style.cssText = 'width: 150px; height: 150px; object-fit: cover;';
                preview.parentNode.replaceChild(img, preview);
            }
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?= $this->endSection() ?>
