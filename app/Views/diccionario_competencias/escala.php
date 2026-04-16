<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Escala de Competencias - <?= esc($cliente['nombre_cliente'] ?? '') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('consultantDashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url("diccionario-competencias/{$cliente['id_cliente']}") ?>">Diccionario</a></li>
            <li class="breadcrumb-item active">Escala</li>
        </ol>
    </nav>

    <div class="mb-4">
        <h3><i class="fas fa-layer-group me-2 text-warning"></i> Escala de evaluacion 1-5</h3>
        <small class="text-muted">Cliente: <strong><?= esc($cliente['nombre_cliente']) ?></strong></small>
    </div>

    <?php if (session('ok')): ?>
        <div class="alert alert-success"><?= esc(session('ok')) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url("diccionario-competencias/{$cliente['id_cliente']}/escala/guardar") ?>">
        <?= csrf_field() ?>
        <div class="card">
            <div class="card-body">
                <?php
                // Asegurar que aparezcan los 5 niveles aunque falten en BD
                $porNivel = [];
                foreach ($escala as $e) { $porNivel[(int)$e['nivel']] = $e; }
                for ($i = 1; $i <= 5; $i++):
                    $e = $porNivel[$i] ?? ['nombre' => '', 'etiqueta' => '', 'descripcion' => ''];
                ?>
                <div class="row g-2 align-items-start mb-3 border-bottom pb-3">
                    <div class="col-md-1">
                        <span class="badge bg-info fs-5">Nivel <?= $i ?></span>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Nombre</label>
                        <input type="text" class="form-control form-control-sm"
                               name="niveles[<?= $i ?>][nombre]"
                               value="<?= esc($e['nombre']) ?>" placeholder="Inicial / Basico / ...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Etiqueta</label>
                        <input type="text" class="form-control form-control-sm"
                               name="niveles[<?= $i ?>][etiqueta]"
                               value="<?= esc($e['etiqueta']) ?>" placeholder="No evidenciado / ...">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small mb-1">Descripcion</label>
                        <textarea class="form-control form-control-sm" rows="3"
                                  name="niveles[<?= $i ?>][descripcion]"><?= esc($e['descripcion']) ?></textarea>
                    </div>
                </div>
                <?php endfor; ?>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= base_url("diccionario-competencias/{$cliente['id_cliente']}") ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                    <button type="submit" class="btn btn-warning text-white">
                        <i class="fas fa-save me-1"></i> Guardar escala
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
