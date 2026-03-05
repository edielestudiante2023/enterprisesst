<?php
$backUrl = base_url('client/inspecciones');
$iconos = [
    'acta_visita'             => 'fa-file-signature',
    'locativa'                => 'fa-building',
    'senalizacion'            => 'fa-sign',
    'botiquin'                => 'fa-first-aid',
    'extintores'              => 'fa-fire-extinguisher',
    'comunicaciones'          => 'fa-broadcast-tower',
    'gabinetes'               => 'fa-shower',
    'matriz_vulnerabilidad'   => 'fa-shield-alt',
    'probabilidad_peligros'   => 'fa-exclamation-triangle',
    'recursos_seguridad'      => 'fa-hard-hat',
    'hv_brigadista'           => 'fa-id-card-alt',
    'plan_emergencia'         => 'fa-route',
    'simulacro'               => 'fa-running',
    'limpieza'                => 'fa-pump-soap',
    'dotacion_vigilante'      => 'fa-user-tie',
    'dotacion_aseadora'       => 'fa-broom',
    'dotacion_todero'         => 'fa-hard-hat',
    'auditoria_residuos'      => 'fa-recycle',
    'asistencia_induccion'    => 'fa-chalkboard-teacher',
    'reporte_capacitacion'    => 'fa-graduation-cap',
    'preparacion_simulacro'   => 'fa-clipboard-list',
    'residuos'                => 'fa-recycle',
    'plagas'                  => 'fa-bug',
    'agua_potable'            => 'fa-tint',
    'plan_saneamiento'        => 'fa-shield-alt',
    'kpi_limpieza'            => 'fa-chart-line',
    'kpi_residuos'            => 'fa-chart-bar',
    'kpi_plagas'              => 'fa-chart-pie',
    'kpi_agua_potable'        => 'fa-chart-area',
];
$icono = $iconos[$tipo] ?? 'fa-clipboard-list';
?>

<div class="page-header">
    <h1><i class="fas <?= $icono ?> me-2"></i> <?= esc($titulo) ?></h1>
    <a href="<?= $backUrl ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<?php if (empty($inspecciones)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-inbox" style="font-size:3rem; color:#ccc;"></i>
        <h5 class="mt-3 text-muted">No hay <?= strtolower(esc($titulo)) ?> completadas</h5>
    </div>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($inspecciones as $insp): ?>
    <div class="col-md-6 col-lg-4">
        <a href="<?= base_url($base_url . '/' . $insp['id']) ?>" style="text-decoration:none;">
            <div class="card h-100" style="transition:all 0.3s ease; cursor:pointer;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span style="font-size:13px; color:#1c2437; font-weight:700;">
                            #<?= $insp['id'] ?>
                        </span>
                        <span class="badge bg-success" style="font-size:11px;">Completo</span>
                    </div>
                    <div style="font-size:14px; color:#555;">
                        <i class="fas fa-calendar-alt me-1" style="color:#bd9751;"></i>
                        <?= date('d/m/Y', strtotime($insp[$campo_fecha])) ?>
                    </div>
                    <?php if ($tipo === 'acta_visita' && !empty($insp['motivo'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-info-circle me-1"></i> <?= esc(mb_strimwidth($insp['motivo'], 0, 60, '...')) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($tipo === 'extintores' && isset($insp['numero_extintores_totales'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-fire-extinguisher me-1"></i> <?= $insp['numero_extintores_totales'] ?> extintores
                    </div>
                    <?php endif; ?>
                    <div class="mt-3 text-end">
                        <span style="font-size:12px; color:#bd9751; font-weight:600;">
                            Ver detalle <i class="fas fa-chevron-right ms-1"></i>
                        </span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<style>
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
    }
</style>
