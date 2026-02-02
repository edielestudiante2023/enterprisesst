<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Portal de Miembro</h1>
                    <p class="text-muted mb-0">Bienvenido, <strong><?= esc($miembro['nombre_completo'] ?? 'Miembro') ?></strong></p>
                    <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente'] ?? '') ?></p>
                </div>
                <a href="<?= base_url('logout') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesion
                </a>
            </div>
        </div>
    </div>

    <!-- Acceso rápido -->
    <div class="row mb-4">
        <div class="col-md-6">
            <a href="<?= base_url('miembro/compromisos') ?>" class="card border-0 shadow-sm text-decoration-none h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                        <i class="bi bi-list-check text-danger fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 text-dark">Mis Compromisos</h5>
                        <p class="text-muted mb-0 small">Ver tareas asignadas</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Mis Comités -->
    <h5 class="mb-3"><i class="bi bi-people me-2"></i>Mis Comites</h5>
    <div class="row">
        <?php if (!empty($comites)): ?>
            <?php foreach ($comites as $comiteItem): ?>
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-people-fill me-2"></i><?= esc($comiteItem['tipo_nombre']) ?>
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
                                <div class="h4 mb-0 text-success"><?= $stats['firmadas'] ?? 0 ?></div>
                                <small class="text-muted">Firmadas</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="<?= base_url('miembro/comite/' . $comiteItem['id_comite']) ?>" class="btn btn-primary w-100">
                            <i class="bi bi-arrow-right-circle me-1"></i>Ver Actas del Comite
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox text-muted fs-1"></i>
                        <p class="text-muted mt-2">No perteneces a ningun comite activo</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
