<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Acciones Correctivas - <?= esc($cliente['nombre_cliente']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4; color: #333; }
        .container { padding: 20px; }

        /* Header */
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid #0d6efd; }
        .header h1 { color: #0d6efd; font-size: 18px; margin-bottom: 5px; }
        .header p { color: #666; font-size: 12px; }

        /* Info Cliente */
        .info-cliente { background: #f8f9fa; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .info-cliente h3 { color: #333; font-size: 14px; margin-bottom: 10px; }
        .info-grid { display: flex; flex-wrap: wrap; }
        .info-item { width: 50%; margin-bottom: 5px; }
        .info-item label { color: #666; font-weight: normal; }
        .info-item span { font-weight: bold; }

        /* KPIs */
        .kpis { display: flex; justify-content: space-around; margin-bottom: 25px; }
        .kpi-box { text-align: center; padding: 15px; background: #fff; border: 1px solid #dee2e6; border-radius: 8px; width: 22%; }
        .kpi-value { font-size: 24px; font-weight: bold; color: #0d6efd; }
        .kpi-label { font-size: 10px; color: #666; text-transform: uppercase; }
        .kpi-box.danger .kpi-value { color: #dc3545; }
        .kpi-box.success .kpi-value { color: #198754; }
        .kpi-box.warning .kpi-value { color: #ffc107; }

        /* Tablas */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px 10px; text-align: left; border: 1px solid #dee2e6; }
        th { background: #0d6efd; color: white; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        tr:nth-child(even) { background: #f8f9fa; }

        /* Badges */
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-success { background: #198754; color: white; }
        .badge-info { background: #0dcaf0; color: #333; }
        .badge-primary { background: #0d6efd; color: white; }
        .badge-secondary { background: #6c757d; color: white; }

        /* Secciones */
        .section { margin-bottom: 25px; page-break-inside: avoid; }
        .section-title { color: #0d6efd; font-size: 14px; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid #dee2e6; }

        /* Footer */
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #666; padding: 10px 0; border-top: 1px solid #dee2e6; }

        @media print {
            .container { padding: 10px; }
            .section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>REPORTE DE ACCIONES CORRECTIVAS</h1>
            <p>Modulo de Gestion CAPA - Numerales 7.1.1, 7.1.2, 7.1.3, 7.1.4 (Res. 0312/2019)</p>
            <p>Generado: <?= date('d/m/Y H:i') ?></p>
        </div>

        <!-- Informacion del Cliente -->
        <div class="info-cliente">
            <h3><?= esc($cliente['nombre_cliente']) ?></h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>NIT:</label> <span><?= esc($cliente['nit_cliente']) ?></span>
                </div>
                <div class="info-item">
                    <label>Periodo:</label> <span>Ano <?= $anio ?? date('Y') ?></span>
                </div>
            </div>
        </div>

        <!-- KPIs -->
        <div class="kpis">
            <div class="kpi-box">
                <div class="kpi-value"><?= $kpis['total_hallazgos'] ?? 0 ?></div>
                <div class="kpi-label">Hallazgos</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-value"><?= $kpis['total_acciones'] ?? 0 ?></div>
                <div class="kpi-label">Acciones</div>
            </div>
            <div class="kpi-box <?= ($kpis['acciones_vencidas'] ?? 0) > 0 ? 'danger' : 'success' ?>">
                <div class="kpi-value"><?= $kpis['acciones_vencidas'] ?? 0 ?></div>
                <div class="kpi-label">Vencidas</div>
            </div>
            <div class="kpi-box success">
                <div class="kpi-value"><?= is_array($kpis['efectividad'] ?? 0) ? ($kpis['efectividad']['valor'] ?? 0) : ($kpis['efectividad'] ?? 0) ?>%</div>
                <div class="kpi-label">Efectividad</div>
            </div>
        </div>

        <!-- Resumen por Numeral -->
        <div class="section">
            <h3 class="section-title">Resumen por Numeral</h3>
            <table>
                <thead>
                    <tr>
                        <th>Numeral</th>
                        <th>Descripcion</th>
                        <th>Hallazgos</th>
                        <th>Acciones</th>
                        <th>Cerradas</th>
                        <th>Pendientes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $numerales = [
                        '7.1.1' => 'Acciones resultados SG-SST',
                        '7.1.2' => 'Efectividad medidas de prevencion',
                        '7.1.3' => 'Investigacion ATEL',
                        '7.1.4' => 'Requerimientos ARL/Autoridades'
                    ];
                    foreach ($numerales as $num => $desc):
                        $datosNum = $estadisticas_por_numeral[$num] ?? ['hallazgos' => 0, 'acciones' => 0, 'cerradas' => 0, 'pendientes' => 0];
                    ?>
                    <tr>
                        <td><span class="badge badge-primary"><?= $num ?></span></td>
                        <td><?= $desc ?></td>
                        <td><?= $datosNum['hallazgos'] ?? 0 ?></td>
                        <td><?= $datosNum['acciones'] ?? 0 ?></td>
                        <td><?= $datosNum['cerradas'] ?? 0 ?></td>
                        <td><?= $datosNum['pendientes'] ?? 0 ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Lista de Acciones Abiertas -->
        <?php if (!empty($acciones_abiertas)): ?>
        <div class="section">
            <h3 class="section-title">Acciones Abiertas</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hallazgo</th>
                        <th>Accion</th>
                        <th>Tipo</th>
                        <th>Responsable</th>
                        <th>Fecha Compromiso</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($acciones_abiertas as $a): ?>
                    <?php
                    $tipoClase = match($a['tipo_accion']) {
                        'correctiva' => 'danger',
                        'preventiva' => 'warning',
                        'mejora' => 'success',
                        default => 'secondary'
                    };
                    $estadoClase = match($a['estado']) {
                        'asignada' => 'info',
                        'en_ejecucion' => 'primary',
                        'en_revision' => 'warning',
                        'en_verificacion' => 'info',
                        default => 'secondary'
                    };
                    $vencida = isset($a['fecha_compromiso']) && $a['fecha_compromiso'] < date('Y-m-d');
                    ?>
                    <tr style="<?= $vencida ? 'background-color: #ffe6e6;' : '' ?>">
                        <td>#<?= $a['id_accion'] ?></td>
                        <td><?= esc(substr($a['hallazgo_titulo'] ?? '', 0, 30)) ?>...</td>
                        <td><?= esc(substr($a['descripcion_accion'], 0, 40)) ?>...</td>
                        <td><span class="badge badge-<?= $tipoClase ?>"><?= ucfirst($a['tipo_accion']) ?></span></td>
                        <td><?= esc($a['responsable_usuario_nombre'] ?? $a['responsable_nombre'] ?? '-') ?></td>
                        <td>
                            <?= !empty($a['fecha_compromiso']) ? date('d/m/Y', strtotime($a['fecha_compromiso'])) : '-' ?>
                            <?= $vencida ? ' (VENCIDA)' : '' ?>
                        </td>
                        <td><span class="badge badge-<?= $estadoClase ?>"><?= ucwords(str_replace('_', ' ', $a['estado'])) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Lista de Acciones Cerradas -->
        <?php if (!empty($acciones_cerradas)): ?>
        <div class="section">
            <h3 class="section-title">Acciones Cerradas (ultimas 20)</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hallazgo</th>
                        <th>Tipo</th>
                        <th>Fecha Cierre</th>
                        <th>Resultado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($acciones_cerradas, 0, 20) as $a): ?>
                    <tr>
                        <td>#<?= $a['id_accion'] ?></td>
                        <td><?= esc(substr($a['hallazgo_titulo'] ?? '', 0, 40)) ?>...</td>
                        <td><?= ucfirst($a['tipo_accion']) ?></td>
                        <td><?= !empty($a['fecha_cierre_real']) ? date('d/m/Y', strtotime($a['fecha_cierre_real'])) : '-' ?></td>
                        <td>
                            <span class="badge badge-<?= $a['estado'] === 'cerrada_efectiva' ? 'success' : 'danger' ?>">
                                <?= $a['estado'] === 'cerrada_efectiva' ? 'Efectiva' : 'No Efectiva' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <p>
                EnterpriseSST - Sistema de Gestion de Seguridad y Salud en el Trabajo
                | Generado automaticamente el <?= date('d/m/Y') ?> a las <?= date('H:i') ?>
            </p>
        </div>
    </div>
</body>
</html>
