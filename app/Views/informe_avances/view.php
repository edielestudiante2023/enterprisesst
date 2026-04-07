<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Avances - <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-dark: #1c2437; --gold-primary: #bd9751; --gold-secondary: #d4af37; --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); }
        body { background: var(--gradient-bg); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; }
        .navbar-custom { background: #fff; box-shadow: 0 8px 32px rgba(28,36,55,0.15); padding: 15px 0; border-bottom: 2px solid var(--gold-primary); }
        .card-section { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .card-section .card-header { background: var(--primary-dark); color: #fff; border-radius: 16px 16px 0 0; font-weight: 600; }
        .card-section .card-header i { color: var(--gold-primary); }
        .btn-gold { background: var(--gold-primary); color: #fff; border: none; }
        .btn-gold:hover { background: var(--gold-secondary); color: #fff; }
        .metric-box { background: #f8f9fa; border-radius: 12px; padding: 15px; text-align: center; border: 1px solid #e9ecef; }
        .metric-box .value { font-size: 1.8rem; font-weight: 700; color: var(--primary-dark); }
        .metric-box .label { font-size: 0.85rem; color: #6c757d; }
        .resumen-text { white-space: pre-wrap; line-height: 1.7; color: #333; }
        .pilar-card { background: #fff; border-radius: 14px; border: 1px solid #e9ecef; padding: 18px; height: 100%; }
        .pilar-card .pilar-title { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; margin-bottom: 8px; }
        .pilar-card .pilar-metric { font-size: 2rem; font-weight: 800; color: var(--primary-dark); line-height: 1; }
        .pilar-card .pilar-detail { font-size: 0.78rem; color: #6c757d; margin-top: 4px; }
        .pilar-card canvas { max-height: 160px; }
        .badge-estado { font-size: 0.85rem; padding: 4px 12px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= base_url('informe-avances') ?>" style="color: var(--primary-dark);">
                <i class="fas fa-arrow-left me-2"></i>Volver al listado
            </a>
            <div>
                <a href="<?= base_url('informe-avances/pdf/' . $informe['id']) ?>" class="btn btn-gold btn-sm" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i>Ver PDF
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-3">
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= session()->getFlashdata('msg') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <!-- Header info -->
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-building me-2"></i>Informe de Avances</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Cliente:</strong> <?= esc($cliente['nombre_cliente'] ?? 'N/A') ?></p>
                        <p><strong>NIT:</strong> <?= esc($cliente['nit_cliente'] ?? 'N/A') ?></p>
                        <p><strong>Consultor:</strong> <?= esc($consultor['nombre_consultor'] ?? 'N/A') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Periodo:</strong> <?= date('d/m/Y', strtotime($informe['fecha_desde'])) ?> - <?= date('d/m/Y', strtotime($informe['fecha_hasta'])) ?></p>
                        <p><strong>Anio:</strong> <?= esc($informe['anio']) ?></p>
                        <p><strong>Estado:</strong> <span class="badge <?= $informe['estado'] === 'completo' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst($informe['estado']) ?></span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicadores por Pilar SG-SST -->
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-chart-pie me-2"></i>Indicadores por Pilar SG-SST</div>
            <div class="card-body">
                <?php
                    $puntajeActual = floatval($informe['puntaje_actual'] ?? 0);
                    $puntajeAnterior = floatval($informe['puntaje_anterior'] ?? 39.75);
                    $dif = floatval($informe['diferencia_neta'] ?? 0);
                    $ea = $informe['estado_avance'] ?? 'ESTABLE';
                    $eaClass = match(true) {
                        str_contains($ea, 'SIGNIFICATIVO') => 'bg-success',
                        str_contains($ea, 'MODERADO')      => 'bg-info',
                        str_contains($ea, 'ESTABLE')       => 'bg-warning text-dark',
                        default                            => 'bg-danger',
                    };
                    $planPct = floatval($informe['indicador_plan_trabajo'] ?? 0);
                    $capPct = floatval($informe['indicador_capacitacion'] ?? 0);
                ?>
                <div class="row g-3">
                    <!-- Pilar 1: Estandares -->
                    <div class="col-md-6">
                        <div class="pilar-card">
                            <div class="pilar-title"><i class="fas fa-clipboard-check me-1" style="color:#36A2EB"></i>Estandares Minimos (Res. 0312)</div>
                            <div class="row align-items-center">
                                <div class="col-6 text-center"><canvas id="chartEstandares"></canvas></div>
                                <div class="col-6">
                                    <div class="pilar-metric"><?= number_format($puntajeActual, 1) ?>%</div>
                                    <div class="pilar-detail">Anterior: <?= number_format($puntajeAnterior, 1) ?>%
                                        <span style="color:<?= $dif > 0 ? '#28a745' : ($dif < 0 ? '#dc3545' : '#6c757d') ?>;font-weight:bold;">
                                            <?= $dif > 0 ? '+' : '' ?><?= number_format($dif, 1) ?> pp
                                        </span>
                                    </div>
                                    <span class="badge badge-estado <?= $eaClass ?> mt-2"><?= esc($ea) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Pilar 2: Plan de Trabajo -->
                    <div class="col-md-6">
                        <div class="pilar-card">
                            <div class="pilar-title"><i class="fas fa-tasks me-1" style="color:#28a745"></i>Plan de Trabajo Anual</div>
                            <div class="row align-items-center">
                                <div class="col-6 text-center"><canvas id="chartPlanTrabajo"></canvas></div>
                                <div class="col-6">
                                    <div class="pilar-metric"><?= number_format($planPct, 1) ?>%</div>
                                    <div class="pilar-detail" id="detailPlanView">Actividades cerradas</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Pilar 3: Capacitacion -->
                    <div class="col-md-6">
                        <div class="pilar-card">
                            <div class="pilar-title"><i class="fas fa-chalkboard-teacher me-1" style="color:#ffc107"></i>Programa de Capacitacion</div>
                            <div class="row align-items-center">
                                <div class="col-6 text-center"><canvas id="chartCapacitacion"></canvas></div>
                                <div class="col-6">
                                    <div class="pilar-metric"><?= number_format($capPct, 1) ?>%</div>
                                    <div class="pilar-detail" id="detailCapView">Capacitaciones ejecutadas</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Pilar 4: Pendientes -->
                    <div class="col-md-6">
                        <div class="pilar-card">
                            <div class="pilar-title"><i class="fas fa-exclamation-circle me-1" style="color:#dc3545"></i>Compromisos / Pendientes</div>
                            <div class="row align-items-center">
                                <div class="col-6 text-center"><canvas id="chartPendientes"></canvas></div>
                                <div class="col-6">
                                    <div class="pilar-metric" id="metricPendView">--</div>
                                    <div class="pilar-detail" id="detailPendView">Estado de compromisos</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6 MODULOS ADICIONALES -->
        <?php
            $desglose = json_decode($informe['metricas_desglose_json'] ?? '{}', true) ?: [];
            $feData = $desglose['firma_electronica'] ?? [];
            $dsData = $desglose['documentos_sst'] ?? [];
            $indData = $desglose['indicadores_sst'] ?? [];
            $acData = $desglose['acciones_correctivas'] ?? [];
            $atData = $desglose['actas_comite'] ?? [];
            $insData = $desglose['inspecciones'] ?? [];
        ?>

        <?php if (!empty($feData) && ($feData['total_solicitudes'] ?? 0) > 0): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-signature me-2"></i>Firma Electronica</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><div class="metric-box"><div class="value"><?= $feData['total_solicitudes'] ?></div><div class="label">Solicitudes</div></div></div>
                    <div class="col-md-3"><div class="metric-box"><div class="value text-success"><?= $feData['firmados'] ?? 0 ?></div><div class="label">Firmados</div></div></div>
                    <div class="col-md-3"><div class="metric-box"><div class="value text-warning"><?= $feData['pendientes'] ?? 0 ?></div><div class="label">Pendientes</div></div></div>
                    <div class="col-md-3"><div class="metric-box"><div class="value text-danger"><?= $feData['expirados'] ?? 0 ?></div><div class="label">Expirados</div></div></div>
                </div>
                <div class="mt-3">
                    <div class="progress progress-custom"><div class="progress-bar bg-success" style="width:<?= $feData['tasa_firma'] ?? 0 ?>%"><?= number_format($feData['tasa_firma'] ?? 0, 1) ?>% firmados</div></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($dsData) && ($dsData['total_creados'] ?? 0) > 0): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-file-alt me-2"></i>Documentos SST Creados</div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4"><div class="metric-box"><div class="value"><?= $dsData['total_creados'] ?></div><div class="label">Creados en periodo</div></div></div>
                    <div class="col-md-4"><div class="metric-box"><div class="value text-success"><?= $dsData['aprobados_periodo'] ?? 0 ?></div><div class="label">Aprobados</div></div></div>
                </div>
                <?php if (!empty($dsData['por_tipo'])): ?>
                <table class="table table-sm table-bordered">
                    <thead class="table-light"><tr><th>Tipo</th><th class="text-center">Cantidad</th></tr></thead>
                    <tbody>
                    <?php foreach ($dsData['por_tipo'] as $t): ?>
                        <tr><td><?= esc(str_replace('_', ' ', $t['tipo_documento'] ?? '')) ?></td><td class="text-center"><?= $t['cantidad'] ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($indData) && ($indData['total_activos'] ?? 0) > 0): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-chart-bar me-2"></i>Indicadores SST</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><div class="metric-box"><div class="value"><?= $indData['total_activos'] ?></div><div class="label">Activos</div></div></div>
                    <div class="col-md-3"><div class="metric-box"><div class="value"><?= $indData['medidos_periodo'] ?? 0 ?></div><div class="label">Medidos</div></div></div>
                    <div class="col-md-3"><div class="metric-box"><div class="value text-success"><?= $indData['cumplen_meta'] ?? 0 ?></div><div class="label">Cumplen meta</div></div></div>
                    <div class="col-md-3"><div class="metric-box"><div class="value"><?= number_format($indData['pct_cumplimiento'] ?? 0, 1) ?>%</div><div class="label">Cumplimiento</div></div></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($acData) && ($acData['acciones_total'] ?? 0) > 0): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-exclamation-triangle me-2"></i>Acciones Correctivas</div>
            <div class="card-body">
                <?php $kp = $acData['kpis'] ?? []; ?>
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><div class="metric-box"><div class="value"><?= $kp['cierre_a_tiempo'] ?? 0 ?>%</div><div class="label">Cierre a tiempo</div><div class="small text-muted">Meta: 85%</div></div></div>
                    <div class="col-md-3"><div class="metric-box"><div class="value"><?= $kp['efectividad'] ?? 0 ?>%</div><div class="label">Efectividad</div><div class="small text-muted">Meta: 80%</div></div></div>
                    <div class="col-md-3"><div class="metric-box"><div class="value"><?= $kp['dias_promedio'] ?? 0 ?></div><div class="label">Dias promedio</div><div class="small text-muted">Meta: 30</div></div></div>
                    <div class="col-md-3"><div class="metric-box"><div class="value"><?= $kp['reincidencia'] ?? 0 ?>%</div><div class="label">Reincidencia</div><div class="small text-muted">Meta: &le;10%</div></div></div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4"><div class="metric-box"><div class="value"><?= $acData['hallazgos_total'] ?? 0 ?></div><div class="label">Hallazgos</div></div></div>
                    <div class="col-md-4"><div class="metric-box"><div class="value"><?= $acData['acciones_total'] ?? 0 ?></div><div class="label">Acciones</div></div></div>
                    <div class="col-md-4"><div class="metric-box"><div class="value text-danger"><?= $acData['acciones_vencidas'] ?? 0 ?></div><div class="label">Vencidas</div></div></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($atData) && (($atData['reuniones_periodo'] ?? 0) > 0 || ($atData['compromisos']['total'] ?? 0) > 0)): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-gavel me-2"></i>Actas de Comite</div>
            <div class="card-body">
                <div class="metric-box mb-3" style="display:inline-block;padding:10px 25px;"><div class="value"><?= $atData['reuniones_periodo'] ?? 0 ?></div><div class="label">Reuniones en periodo</div></div>
                <?php if (!empty($atData['por_comite'])): ?>
                <table class="table table-sm table-bordered">
                    <thead class="table-light"><tr><th>Comite</th><th class="text-center">Reuniones</th><th class="text-center">Esperadas</th><th class="text-center">Cumplimiento</th></tr></thead>
                    <tbody>
                    <?php foreach ($atData['por_comite'] as $c): ?>
                        <tr><td><?= esc($c['tipo_comite']) ?></td><td class="text-center"><?= $c['total_anio'] ?></td><td class="text-center"><?= $c['esperadas'] ?></td><td class="text-center"><?= $c['cumplimiento'] ?>%</td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
                <?php $comp = $atData['compromisos'] ?? []; ?>
                <?php if (($comp['total'] ?? 0) > 0): ?>
                <div class="row g-3">
                    <div class="col-md-3"><div class="metric-box"><div class="value"><?= $comp['total'] ?></div><div class="label">Total</div></div></div>
                    <div class="col-md-3"><div class="metric-box"><div class="value text-success"><?= $comp['cumplidos'] ?? 0 ?></div><div class="label">Cumplidos</div></div></div>
                    <div class="col-md-3"><div class="metric-box"><div class="value text-warning"><?= $comp['pendientes'] ?? 0 ?></div><div class="label">Pendientes</div></div></div>
                    <div class="col-md-3"><div class="metric-box"><div class="value text-danger"><?= $comp['vencidos'] ?? 0 ?></div><div class="label">Vencidos</div></div></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($insData) && ($insData['total_inspecciones'] ?? 0) > 0): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-clipboard-check me-2"></i>Inspecciones</div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <?php
                    $nombres = ['locativa'=>'Locativa','extintores'=>'Extintores','botiquin'=>'Botiquin','senalizacion'=>'Senalizacion'];
                    $colores = ['locativa'=>'#0d6efd','extintores'=>'#dc3545','botiquin'=>'#198754','senalizacion'=>'#ffc107'];
                    foreach ($nombres as $k => $n):
                        $v = $insData['por_tipo'][$k] ?? ['total'=>0,'completadas'=>0];
                        if ($v['total'] > 0):
                    ?>
                    <div class="col-md-3"><div class="metric-box" style="border-left:4px solid <?= $colores[$k] ?>"><div class="value"><?= $v['completadas'] ?>/<?= $v['total'] ?></div><div class="label"><?= $n ?></div></div></div>
                    <?php endif; endforeach; ?>
                </div>
                <?php $hall = $insData['hallazgos'] ?? []; ?>
                <?php if (($hall['total'] ?? 0) > 0): ?>
                <div class="alert alert-info mb-0"><i class="fas fa-search me-2"></i>Hallazgos locativos: <strong><?= $hall['total'] ?></strong> encontrados, <strong><?= $hall['corregidos'] ?? 0 ?></strong> corregidos, <strong><?= $hall['pendientes'] ?? 0 ?></strong> pendientes</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Evolución Histórica -->
        <?php if (!empty($historialEstandares) || !empty($historialPlan)): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-chart-line me-2"></i>Evolución Histórica del Cliente</div>
            <div class="card-body">
                <div class="row g-3">
                    <?php if (!empty($historialEstandares)): ?>
                    <div class="col-md-6">
                        <h6 class="text-muted fw-bold" style="font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">
                            <i class="fas fa-clipboard-check me-1" style="color:#667eea"></i>% Cumplimiento Estandares Minimos
                        </h6>
                        <canvas id="chartEvolucionEstandares" style="max-height:200px;"></canvas>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($historialPlan)): ?>
                    <div class="col-md-6">
                        <h6 class="text-muted fw-bold" style="font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">
                            <i class="fas fa-tasks me-1" style="color:#4facfe"></i>% Actividades Abiertas Plan de Trabajo
                        </h6>
                        <canvas id="chartEvolucionPlan" style="max-height:200px;"></canvas>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Resumen -->
        <?php if (!empty($informe['resumen_avance'])): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-file-alt me-2"></i>Resumen de Avance</div>
            <div class="card-body">
                <div class="resumen-text"><?= nl2br(esc($informe['resumen_avance'])) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actividades cerradas -->
        <?php if (!empty($informe['actividades_cerradas_periodo'])): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-check-circle me-2"></i>Actividades PTA Cerradas en el Periodo</div>
            <div class="card-body">
                <?php
                $cerradasText = $informe['actividades_cerradas_periodo'];
                $cerradasLines = array_filter(explode("\n", trim($cerradasText)), fn($l) => preg_match('/^\s*-\s*\[/', $l));
                if (!empty($cerradasLines)): ?>
                <table class="table table-sm table-bordered">
                    <thead class="table-light"><tr><th>Actividad</th><th>Numeral</th><th>PHVA</th><th>Responsable</th><th>Fecha Cierre</th></tr></thead>
                    <tbody>
                    <?php foreach ($cerradasLines as $line):
                        preg_match('/^\s*-\s*\[([^\]]*)\]\s*(.*?)\s*\|\s*PHVA:\s*(.*?)\s*\|\s*Resp:\s*(.*?)\s*\|\s*Cerrada:\s*(.*)/', $line, $m);
                        if ($m): ?>
                        <tr><td><?= esc(trim($m[2])) ?></td><td><?= esc(trim($m[1])) ?></td><td><?= esc(trim($m[3])) ?></td><td><?= esc(trim($m[4])) ?></td><td><?= esc(trim($m[5])) ?></td></tr>
                    <?php endif; endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="resumen-text"><?= nl2br(esc($cerradasText)) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actividades PTA No Cerradas -->
        <?php if (!empty($informe['actividades_no_cerradas_pta'])): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-clock me-2"></i>Actividades PTA No Cerradas en el Periodo</div>
            <div class="card-body">
                <div class="resumen-text"><?= nl2br(esc($informe['actividades_no_cerradas_pta'])) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actividades abiertas -->
        <?php if (!empty($informe['actividades_abiertas'])): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-exclamation-triangle me-2"></i>Actividades Abiertas</div>
            <div class="card-body">
                <div class="resumen-text"><?= nl2br(esc($informe['actividades_abiertas'])) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Observaciones -->
        <?php if (!empty($informe['observaciones'])): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-comment-alt me-2"></i>Observaciones</div>
            <div class="card-body">
                <div class="resumen-text"><?= nl2br(esc($informe['observaciones'])) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Vencimientos de Mantenimientos -->
        <?php if (!empty($vencimientos)): ?>
        <div class="card card-section">
            <div class="card-header py-3" style="background:#dc3545;">
                <i class="fas fa-exclamation-circle me-2"></i>Elementos con Vencimiento Proximo o Vencido
                <span class="badge bg-light text-danger ms-2"><?= count($vencimientos) ?></span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:5%;" class="text-center">#</th>
                            <th style="width:35%;">Elemento</th>
                            <th style="width:18%;" class="text-center">Fecha Vencimiento</th>
                            <th style="width:14%;" class="text-center">Estado</th>
                            <th style="width:28%;">Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $hoy = date('Y-m-d');
                            foreach ($vencimientos as $idx => $v):
                                $vencido = ($v['fecha_vencimiento'] <= $hoy);
                        ?>
                        <tr class="<?= $vencido ? 'table-danger' : 'table-warning' ?>">
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td><?= esc($v['detalle_mantenimiento'] ?? 'N/A') ?></td>
                            <td class="text-center"><?= date('d/m/Y', strtotime($v['fecha_vencimiento'])) ?></td>
                            <td class="text-center">
                                <span class="badge <?= $vencido ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= $vencido ? 'VENCIDO' : 'PROXIMO' ?></span>
                            </td>
                            <td class="small"><?= esc($v['observaciones'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Soportes -->
        <?php
            $haySoportes = false;
            for ($i = 1; $i <= 4; $i++) {
                if (!empty($informe["soporte_{$i}_texto"]) || !empty($informe["soporte_{$i}_imagen"])) { $haySoportes = true; break; }
            }
        ?>
        <?php if ($haySoportes): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-paperclip me-2"></i>Soportes</div>
            <div class="card-body">
                <div class="row g-3">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <?php if (!empty($informe["soporte_{$i}_texto"]) || !empty($informe["soporte_{$i}_imagen"])): ?>
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <h6 class="fw-bold">Soporte <?= $i ?></h6>
                                <?php if (!empty($informe["soporte_{$i}_texto"])): ?>
                                    <p><?= esc($informe["soporte_{$i}_texto"]) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($informe["soporte_{$i}_imagen"])): ?>
                                    <img src="<?= base_url($informe["soporte_{$i}_imagen"]) ?>" class="img-fluid rounded" style="max-height: 300px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalImg<?= $i ?>">
                                    <!-- Modal -->
                                    <div class="modal fade" id="modalImg<?= $i ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-body p-0">
                                                    <img src="<?= base_url($informe["soporte_{$i}_imagen"]) ?>" class="img-fluid w-100">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="mb-5"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
    <script>
    Chart.register(ChartDataLabels);

    var COLORS_PHVA = ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];
    var COLORS_ESTADO = ['#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d', '#6f42c1'];

    function makeDoughnut(canvasId, labels, data, colors) {
        return new Chart(document.getElementById(canvasId).getContext('2d'), {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: data, backgroundColor: colors.slice(0, labels.length) }] },
            options: {
                responsive: true, maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 12 } },
                    datalabels: {
                        color: '#fff', font: { weight: 'bold', size: 11 },
                        formatter: function(v, ctx) { var t = ctx.dataset.data.reduce((a,b) => a+b, 0); return t > 0 && v > 0 ? ((v/t)*100).toFixed(0)+'%' : ''; }
                    }
                }
            }
        });
    }

    (function() {
        var raw = <?= json_encode($informe['metricas_desglose_json'] ?? '') ?>;
        if (!raw) return;
        try { var d = JSON.parse(raw); } catch(e) { return; }

        // Estandares
        var est = d.desglose_estandares || [];
        if (est.length > 0) {
            makeDoughnut('chartEstandares', est.map(e => e.ciclo || ''), est.map(e => parseFloat(e.total_valor) || 0), COLORS_PHVA);
        }

        // Plan de trabajo
        var plan = d.desglose_plan_trabajo || [];
        if (plan.length > 0) {
            makeDoughnut('chartPlanTrabajo', plan.map(p => p.estado_actividad || ''), plan.map(p => parseInt(p.cantidad) || 0), COLORS_ESTADO);
            var cerr = 0, tot = 0;
            plan.forEach(function(p) { tot += parseInt(p.cantidad)||0; if (p.estado_actividad === 'CERRADA') cerr = parseInt(p.cantidad)||0; });
            document.getElementById('detailPlanView').textContent = cerr + ' cerradas de ' + tot + ' actividades';
        }

        // Capacitacion
        var cap = d.desglose_capacitacion || [];
        if (cap.length > 0) {
            makeDoughnut('chartCapacitacion', cap.map(c => c.estado || ''), cap.map(c => parseInt(c.cantidad) || 0), COLORS_ESTADO);
            var ejec = 0, totc = 0;
            cap.forEach(function(c) { totc += parseInt(c.cantidad)||0; if (c.estado === 'EJECUTADA') ejec = parseInt(c.cantidad)||0; });
            document.getElementById('detailCapView').textContent = ejec + ' ejecutadas de ' + totc + ' capacitaciones';
        }

        // Pendientes
        var pend = d.desglose_pendientes || [];
        if (pend.length > 0) {
            makeDoughnut('chartPendientes', pend.map(p => p.estado || ''), pend.map(p => parseInt(p.cantidad) || 0), COLORS_ESTADO);
            var ab = 0, totp = 0, prom = 0;
            pend.forEach(function(p) { totp += parseInt(p.cantidad)||0; if (p.estado === 'ABIERTA') { ab = parseInt(p.cantidad)||0; prom = parseFloat(p.promedio_dias)||0; } });
            document.getElementById('metricPendView').textContent = ab + ' / ' + totp;
            document.getElementById('detailPendView').textContent = ab + ' abiertos' + (prom > 0 ? ' (prom ' + prom.toFixed(0) + ' dias)' : '');
        }
    })();
    </script>

    <?php if (!empty($historialEstandares) || !empty($historialPlan)): ?>
    <script>
    (function() {
        var mesesNombres = {
            '01':'Ene','02':'Feb','03':'Mar','04':'Abr','05':'May','06':'Jun',
            '07':'Jul','08':'Ago','09':'Sep','10':'Oct','11':'Nov','12':'Dic'
        };
        function formatMes(ym) {
            var parts = ym.split('-');
            return (mesesNombres[parts[1]] || parts[1]) + ' ' + parts[0].substring(2);
        }
        function makeLineChart(canvasId, historial, label, color) {
            var canvas = document.getElementById(canvasId);
            if (!canvas || !historial.length) return;
            var labels = historial.map(function(h) { return formatMes(h.mes); });
            var values = historial.map(function(h) { return h.promedio; });
            new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: values,
                        borderColor: color,
                        backgroundColor: color + '22',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: color
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        datalabels: {
                            color: color,
                            font: { weight: 'bold', size: 10 },
                            anchor: 'end', align: 'top',
                            formatter: function(v) { return v.toFixed(1) + '%'; }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, suggestedMax: 100, title: { display: true, text: '%' } }
                    }
                }
            });
        }

        <?php if (!empty($historialEstandares)): ?>
        makeLineChart('chartEvolucionEstandares', <?= json_encode($historialEstandares) ?>, '% Estandares', '#667eea');
        <?php endif; ?>

        <?php if (!empty($historialPlan)): ?>
        makeLineChart('chartEvolucionPlan', <?= json_encode($historialPlan) ?>, '% Abiertas', '#4facfe');
        <?php endif; ?>
    })();
    </script>
    <?php endif; ?>
</body>
</html>
