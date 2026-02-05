<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-gradient" style="background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);">
                <div class="card-body text-white py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h2 mb-2">
                                <i class="bi bi-clipboard-check me-2"></i>Gestion de Actas y Comites
                            </h1>
                            <p class="mb-0 opacity-75">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= esc($usuario['nombre']) ?>
                                <span class="badge bg-white text-dark ms-2"><?= ucfirst($usuario['role']) ?></span>
                            </p>
                        </div>
                        <div class="text-end d-none d-md-block">
                            <div class="display-4 opacity-50">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadisticas Globales -->
    <div class="row mb-4">
        <?php
        $totalComites = array_sum(array_column($clientes, 'total_comites'));
        $totalActas = array_sum(array_column($clientes, 'total_actas'));
        $totalPendientes = array_sum(array_column($clientes, 'actas_pendientes'));
        $totalCompromisos = array_sum(array_column($clientes, 'compromisos_pendientes'));
        ?>
        <div class="col-6 col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="h2 text-primary mb-1"><?= count($clientes) ?></div>
                    <small class="text-muted">Clientes Activos</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="h2 text-success mb-1"><?= $totalComites ?></div>
                    <small class="text-muted">Comites Totales</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="h2 text-info mb-1"><?= $totalActas ?></div>
                    <small class="text-muted">Actas Generadas</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="h2 <?= $totalPendientes > 0 ? 'text-warning' : 'text-muted' ?> mb-1"><?= $totalPendientes ?></div>
                    <small class="text-muted">Pendientes de Firma</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Busqueda -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="searchClientes" class="form-control border-start-0" placeholder="Buscar cliente por nombre o NIT...">
            </div>
        </div>
    </div>

    <!-- Lista de Clientes -->
    <div class="row" id="clientesContainer">
        <?php if (empty($clientes)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-building display-1 text-muted"></i>
                    <h4 class="mt-3">No hay clientes asignados</h4>
                    <p class="text-muted">Aun no tienes clientes activos para gestionar actas.</p>
                </div>
            </div>
        </div>
        <?php else: ?>
            <?php foreach ($clientes as $cliente): ?>
            <div class="col-md-6 col-lg-4 col-xl-3 mb-4 cliente-card"
                 data-nombre="<?= strtolower(esc($cliente['nombre_cliente'])) ?>"
                 data-nit="<?= strtolower(esc($cliente['nit_cliente'])) ?>">
                <div class="card border-0 shadow-sm h-100 card-hover">
                    <div class="card-body">
                        <!-- Logo y Nombre -->
                        <div class="d-flex align-items-center mb-3">
                            <?php if (!empty($cliente['logo'])): ?>
                                <img src="<?= base_url('uploads/' . $cliente['logo']) ?>"
                                     alt="Logo"
                                     class="rounded-circle me-3"
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle me-3 d-flex align-items-center justify-content-center bg-light"
                                     style="width: 50px; height: 50px;">
                                    <i class="bi bi-building text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="flex-grow-1 overflow-hidden">
                                <h6 class="mb-0 text-truncate" title="<?= esc($cliente['nombre_cliente']) ?>">
                                    <?= esc($cliente['nombre_cliente']) ?>
                                </h6>
                                <small class="text-muted">NIT: <?= esc($cliente['nit_cliente']) ?></small>
                            </div>
                        </div>

                        <!-- Estadisticas -->
                        <div class="row text-center mb-3 g-2">
                            <div class="col-4">
                                <div class="bg-light rounded py-2">
                                    <div class="h5 mb-0 text-primary"><?= $cliente['total_comites'] ?></div>
                                    <small class="text-muted" style="font-size: 0.7rem;">Comites</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded py-2">
                                    <div class="h5 mb-0 text-success"><?= $cliente['total_actas'] ?></div>
                                    <small class="text-muted" style="font-size: 0.7rem;">Actas</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded py-2">
                                    <div class="h5 mb-0 <?= $cliente['actas_pendientes'] > 0 ? 'text-warning' : 'text-muted' ?>">
                                        <?= $cliente['actas_pendientes'] ?>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;">Pendientes</small>
                                </div>
                            </div>
                        </div>

                        <!-- Alertas -->
                        <?php if ($cliente['compromisos_pendientes'] > 0): ?>
                        <div class="alert alert-danger py-1 px-2 mb-3" style="font-size: 0.8rem;">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <?= $cliente['compromisos_pendientes'] ?> compromiso(s) vencido(s)
                        </div>
                        <?php endif; ?>

                        <?php if ($cliente['actas_pendientes'] > 0): ?>
                        <div class="alert alert-warning py-1 px-2 mb-3" style="font-size: 0.8rem;">
                            <i class="bi bi-pen me-1"></i>
                            <?= $cliente['actas_pendientes'] ?> acta(s) por firmar
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-footer bg-transparent border-0 pt-0">
                        <a href="<?= base_url('actas/' . $cliente['id_cliente']) ?>" class="btn btn-primary w-100">
                            <i class="bi bi-arrow-right-circle me-1"></i>Gestionar Actas
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Mensaje cuando no hay resultados de busqueda -->
    <div class="row d-none" id="noResultados">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <i class="bi bi-search display-4 text-muted"></i>
                    <h5 class="mt-3">No se encontraron resultados</h5>
                    <p class="text-muted mb-0">Intenta con otro termino de busqueda</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card-hover {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.bg-gradient {
    background-size: 200% 200%;
    animation: gradientShift 10s ease infinite;
}
@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchClientes');
    const cards = document.querySelectorAll('.cliente-card');
    const noResultados = document.getElementById('noResultados');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        let visibleCount = 0;

        cards.forEach(function(card) {
            const nombre = card.dataset.nombre;
            const nit = card.dataset.nit;

            if (nombre.includes(searchTerm) || nit.includes(searchTerm)) {
                card.classList.remove('d-none');
                visibleCount++;
            } else {
                card.classList.add('d-none');
            }
        });

        // Mostrar mensaje si no hay resultados
        if (visibleCount === 0 && searchTerm !== '') {
            noResultados.classList.remove('d-none');
        } else {
            noResultados.classList.add('d-none');
        }
    });
});
</script>

<?= $this->endSection() ?>
