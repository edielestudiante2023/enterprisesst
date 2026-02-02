<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-1">Portal de Miembro</h1>
            <p class="text-muted mb-0">Bienvenido, <strong><?= esc($miembro['nombre_completo']) ?></strong></p>
            <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?></p>
        </div>
    </div>

    <!-- Mis Comités -->
    <div class="row">
        <?php foreach ($comites as $comiteItem): ?>
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2"></i><?= esc($comiteItem['tipo_nombre']) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted">Mi rol:</small><br>
                            <span class="badge bg-info"><?= ucfirst($comiteItem['rol_comite'] ?? 'Miembro') ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Representacion:</small><br>
                            <span class="badge bg-<?= ($comiteItem['representacion'] ?? '') === 'empleador' ? 'primary' : 'success' ?>">
                                <?= ucfirst($comiteItem['representacion'] ?? '-') ?>
                            </span>
                        </div>
                    </div>

                    <!-- Estadísticas del comité -->
                    <?php $stats = $comiteItem['stats'] ?? []; ?>
                    <div class="row text-center border-top pt-3">
                        <div class="col-4">
                            <div class="h4 mb-0"><?= $stats['total'] ?? 0 ?></div>
                            <small class="text-muted">Actas</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-0 text-warning"><?= $stats['pendientes_firma'] ?? 0 ?></div>
                            <small class="text-muted">Pend. Firma</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-0 text-danger"><?= $comiteItem['compromisos_pendientes'] ?? 0 ?></div>
                            <small class="text-muted">Compromisos</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="<?= base_url('miembro/' . $token . '/comite/' . $comiteItem['id_comite']) ?>" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-right-circle me-1"></i>Ver Actas del Comite
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($comites)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox text-muted fs-1"></i>
            <p class="text-muted mt-2">No perteneces a ningun comite activo</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Información de acceso -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h6><i class="bi bi-info-circle me-2"></i>Informacion de tu Acceso</h6>
            <ul class="mb-0 small text-muted">
                <li>Este enlace es personal y expira en 30 dias</li>
                <li>Si necesitas un nuevo enlace, contacta al consultor SST</li>
                <li>Guarda este enlace en tus favoritos para acceso rapido</li>
            </ul>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
