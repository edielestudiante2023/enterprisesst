<?php
/**
 * Componente: Lista de Subcarpetas
 * Variables requeridas: $subcarpetas
 */
if (empty($subcarpetas)) {
    return;
}
?>
<div class="col-12 mb-4">
    <h6 class="text-muted mb-3"><i class="bi bi-folder me-2"></i>Subcarpetas</h6>
    <div class="row g-3">
        <?php foreach ($subcarpetas as $sub): ?>
            <?php
                $stats = $sub['stats'] ?? ['total' => 0, 'pendiente' => 0, 'creado' => 0, 'aprobado' => 0];
            ?>
            <div class="col-md-3">
                <a href="<?= base_url('documentacion/carpeta/' . $sub['id_carpeta']) ?>" class="text-decoration-none">
                    <div class="card border-0 shadow-sm doc-card folder-card h-100">
                        <div class="card-body text-center py-3">
                            <i class="bi bi-folder-fill text-warning" style="font-size: 2rem;"></i>
                            <h6 class="mt-2 mb-1 text-dark"><?= esc($sub['nombre']) ?></h6>
                            <small class="text-muted d-block">
                                <?= $stats['total'] ?> documento(s)
                            </small>
                            <?php if ($stats['total'] > 0): ?>
                            <div class="folder-stats">
                                <?php if ($stats['aprobado'] > 0): ?>
                                    <span class="badge estado-ia-aprobado" title="Aprobados">
                                        <i class="bi bi-check-circle"></i> <?= $stats['aprobado'] ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($stats['creado'] > 0): ?>
                                    <span class="badge estado-ia-creado" title="Creados">
                                        <i class="bi bi-pencil"></i> <?= $stats['creado'] ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($stats['pendiente'] > 0): ?>
                                    <span class="badge estado-ia-pendiente" title="Pendientes">
                                        <i class="bi bi-clock"></i> <?= $stats['pendiente'] ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
