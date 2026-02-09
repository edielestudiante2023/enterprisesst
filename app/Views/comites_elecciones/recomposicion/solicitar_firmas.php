<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<?php
$tipoComiteNombre = [
    'COPASST' => 'COPASST',
    'COCOLAB' => 'Comite de Convivencia Laboral',
    'BRIGADA' => 'Brigada de Emergencias',
    'VIGIA' => 'Vigia SST'
][$proceso['tipo_comite']] ?? $proceso['tipo_comite'];
?>

<style>
.header-firmas {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    border-radius: 12px;
    padding: 25px;
    color: white;
    margin-bottom: 25px;
}

.card-firmante {
    border: 2px solid #dee2e6;
    border-radius: 12px;
    margin-bottom: 15px;
    transition: all 0.2s;
}

.card-firmante:hover {
    border-color: #dc3545;
}

.card-firmante.selected {
    border-color: #dc3545;
    background-color: #fff5f5;
}

.card-firmante.ya-solicitado {
    border-color: #28a745;
    background-color: #e8f5e9;
}

.card-firmante.firmado {
    border-color: #28a745;
    background-color: #c8e6c9;
}

.firmante-check {
    width: 24px;
    height: 24px;
}

.badge-obligatorio {
    background-color: #dc3545;
    color: white;
    font-size: 0.7rem;
}

.badge-opcional {
    background-color: #6c757d;
    color: white;
    font-size: 0.7rem;
}

.email-warning {
    color: #dc3545;
    font-size: 0.8rem;
}

.stats-box {
    background: white;
    border-radius: 10px;
    padding: 15px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
}
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="header-firmas">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="fas fa-signature me-2"></i>
                    Solicitar Firmas - Recomposicion #<?= $recomposicion['numero_recomposicion'] ?>
                </h4>
                <p class="mb-0 opacity-75">
                    <?= esc($tipoComiteNombre) ?> - <?= esc($cliente['nombre_cliente']) ?>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <a href="<?= base_url("comites-elecciones/proceso/{$proceso['id_proceso']}/recomposicion/{$recomposicion['id_recomposicion']}") ?>"
                   class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stats-box">
                <div class="stats-number text-primary"><?= $totalFirmantes ?></div>
                <small class="text-muted">Total Firmantes</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-box">
                <div class="stats-number text-success"><?= $firmadosCount ?></div>
                <small class="text-muted">Firmados</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-box">
                <div class="stats-number text-warning"><?= $pendientesCount ?></div>
                <small class="text-muted">Pendientes</small>
            </div>
        </div>
    </div>

    <!-- Info -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Firmantes requeridos para el Acta de Recomposicion:</strong>
        <ol class="mb-0 mt-2">
            <li><strong>Nuevo Integrante</strong> - Acepta formalmente su ingreso al comite</li>
            <li><strong>Delegado SST</strong> (si existe) - Constancia de conocimiento</li>
            <li><strong>Representante Legal</strong> - Aprueba el cambio en el comite (firma último por jerarquía)</li>
        </ol>
    </div>

    <form action="<?= base_url('comites-elecciones/proceso/crear-solicitudes-recomposicion') ?>" method="POST" id="formFirmas">
        <?= csrf_field() ?>
        <input type="hidden" name="id_proceso" value="<?= $proceso['id_proceso'] ?>">
        <input type="hidden" name="id_recomposicion" value="<?= $recomposicion['id_recomposicion'] ?>">
        <input type="hidden" name="id_documento" value="<?= $documento['id_documento'] ?>">

        <div class="row">
            <?php foreach ($firmantesAgrupados as $grupo => $firmantes): ?>
            <div class="col-12">
                <h5 class="mb-3 text-secondary">
                    <i class="fas fa-users me-2"></i><?= $grupo ?>
                </h5>
            </div>

            <?php foreach ($firmantes as $f): ?>
            <?php
                $yaFirmado = $f['estado_firma'] === 'firmado';
                $yaSolicitado = $f['ya_solicitado'];
                $tieneEmail = !empty($f['email']);
                $cardClass = $yaFirmado ? 'firmado' : ($yaSolicitado ? 'ya-solicitado' : '');
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-firmante <?= $cardClass ?>" id="card-<?= $f['tipo'] ?>">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <?php if (!$yaSolicitado): ?>
                            <div class="me-3">
                                <input type="checkbox"
                                       class="form-check-input firmante-check"
                                       name="firmantes[<?= $f['tipo'] ?>][selected]"
                                       value="1"
                                       id="check-<?= $f['tipo'] ?>"
                                       <?= !$tieneEmail ? 'disabled' : '' ?>
                                       <?= $f['obligatorio'] ?? false ? 'checked' : '' ?>
                                       onchange="toggleCard('<?= $f['tipo'] ?>')">
                            </div>
                            <?php endif; ?>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0"><?= esc($f['nombre']) ?></h6>
                                    <?php if ($yaFirmado): ?>
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Firmado</span>
                                    <?php elseif ($yaSolicitado): ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pendiente</span>
                                    <?php elseif ($f['obligatorio'] ?? false): ?>
                                        <span class="badge badge-obligatorio">Obligatorio</span>
                                    <?php else: ?>
                                        <span class="badge badge-opcional">Opcional</span>
                                    <?php endif; ?>
                                </div>

                                <p class="text-muted mb-1 small">
                                    <i class="fas fa-id-card me-1"></i><?= esc($f['cargo']) ?>
                                </p>
                                <p class="text-muted mb-1 small">
                                    <i class="fas fa-id-badge me-1"></i>C.C. <?= esc($f['cedula']) ?>
                                </p>

                                <?php if ($tieneEmail): ?>
                                <p class="text-muted mb-0 small">
                                    <i class="fas fa-envelope me-1"></i><?= esc($f['email']) ?>
                                </p>
                                <?php else: ?>
                                <p class="email-warning mb-0">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Sin email registrado
                                </p>
                                <?php endif; ?>

                                <?php if (!$yaSolicitado && $tieneEmail): ?>
                                <!-- Campos ocultos para enviar datos -->
                                <input type="hidden" name="firmantes[<?= $f['tipo'] ?>][nombre]" value="<?= esc($f['nombre']) ?>">
                                <input type="hidden" name="firmantes[<?= $f['tipo'] ?>][email]" value="<?= esc($f['email']) ?>">
                                <input type="hidden" name="firmantes[<?= $f['tipo'] ?>][cargo]" value="<?= esc($f['cargo']) ?>">
                                <input type="hidden" name="firmantes[<?= $f['tipo'] ?>][cedula]" value="<?= esc($f['cedula']) ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <!-- Botones -->
        <div class="d-flex justify-content-between mt-4">
            <a href="<?= base_url("comites-elecciones/proceso/{$proceso['id_proceso']}/recomposicion/{$recomposicion['id_recomposicion']}") ?>"
               class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>

            <?php
            $tienePendientes = false;
            foreach ($firmantesAgrupados as $grupo => $firmantes) {
                foreach ($firmantes as $f) {
                    if (!$f['ya_solicitado'] && !empty($f['email'])) {
                        $tienePendientes = true;
                        break 2;
                    }
                }
            }
            ?>

            <?php if ($tienePendientes): ?>
            <button type="submit" class="btn btn-danger btn-lg">
                <i class="fas fa-paper-plane me-2"></i>
                Enviar Solicitudes de Firma
            </button>
            <?php else: ?>
            <a href="<?= base_url("comites-elecciones/proceso/{$proceso['id_proceso']}/recomposicion/{$recomposicion['id_recomposicion']}/firmas/estado") ?>"
               class="btn btn-success btn-lg">
                <i class="fas fa-eye me-2"></i>
                Ver Estado de Firmas
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
function toggleCard(tipo) {
    const card = document.getElementById('card-' + tipo);
    const check = document.getElementById('check-' + tipo);
    if (check.checked) {
        card.classList.add('selected');
    } else {
        card.classList.remove('selected');
    }
}

// Inicializar estado visual de cards seleccionadas
document.querySelectorAll('.firmante-check:checked').forEach(check => {
    const tipo = check.id.replace('check-', '');
    toggleCard(tipo);
});
</script>

<?= $this->endSection() ?>
