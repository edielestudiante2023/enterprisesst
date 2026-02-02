<?php
/**
 * Componente: Header (Navbar + Breadcrumb)
 * Variables requeridas: $cliente, $carpeta, $ruta
 */
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>">
            <i class="bi bi-folder-fill me-2"></i>Documentacion SST
        </a>
        <div class="navbar-nav ms-auto">
            <span class="navbar-text text-white me-3">
                <i class="bi bi-building me-1"></i>
                <?= esc($cliente['nombre_cliente']) ?>
            </span>
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    <!-- Breadcrumb mejorado -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-white p-3 rounded shadow-sm">
            <li class="breadcrumb-item">
                <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>">
                    <i class="bi bi-house me-1"></i>Inicio
                </a>
            </li>
            <?php if (!empty($ruta)): ?>
                <?php foreach ($ruta as $index => $item): ?>
                    <?php if ($item['id_carpeta'] == $carpeta['id_carpeta']): ?>
                        <li class="breadcrumb-item active">
                            <i class="bi bi-folder-fill text-warning me-1"></i>
                            <?= esc($item['nombre']) ?>
                        </li>
                    <?php else: ?>
                        <li class="breadcrumb-item">
                            <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>">
                                <?= esc($item['nombre']) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </ol>
    </nav>
