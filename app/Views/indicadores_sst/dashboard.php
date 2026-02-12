<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Indicadores SST - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        /* Gauge containers */
        .gauge-master { max-width: 280px; margin: 0 auto; }
        .gauge-tipo { max-width: 180px; margin: 0 auto; }
        .gauge-categoria { max-width: 100px; margin: 0 auto; }

        /* Semáforo colors */
        .semaforo-verde { color: #27ae60; }
        .semaforo-amarillo { color: #f39c12; }
        .semaforo-rojo { color: #e74c3c; }
        .semaforo-gris { color: #6c757d; }

        .bg-semaforo-verde { background: #27ae60 !important; }
        .bg-semaforo-amarillo { background: #f39c12 !important; }
        .bg-semaforo-rojo { background: #e74c3c !important; }

        /* Category cards */
        .cat-card {
            cursor: pointer;
            transition: all 0.25s ease;
            border: 2px solid transparent;
            border-radius: 12px;
        }
        .cat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.12); }
        .cat-card.active { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,0.25); }

        /* Mini stacked bar */
        .stacked-bar { height: 6px; border-radius: 3px; overflow: hidden; display: flex; }
        .stacked-bar .bar-e { background: #3498db; }
        .stacked-bar .bar-p { background: #f39c12; }
        .stacked-bar .bar-r { background: #27ae60; }

        /* Nivel 4 drill-down */
        .drill-down { display: none; }
        .drill-down.show { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

        /* Indicator row */
        .ind-row { border-left: 4px solid; padding: 0.6rem 0.8rem; margin-bottom: 0.4rem; border-radius: 0 6px 6px 0; background: #fff; transition: all 0.2s; }
        .ind-row:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .ind-row.tipo-estructura { border-left-color: #3498db; }
        .ind-row.tipo-proceso { border-left-color: #f39c12; }
        .ind-row.tipo-resultado { border-left-color: #27ae60; }

        /* Sparkline container */
        .sparkline-container { width: 80px; height: 30px; }

        /* Mínimos panel */
        .minimo-card { border-radius: 10px; text-align: center; padding: 0.6rem; }
        .minimo-valor { font-size: 1.4rem; font-weight: 700; }
        .minimo-meta { font-size: 0.7rem; color: #6c757d; }

        /* Header dark */
        .header-dark {
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
            border-radius: 15px;
            padding: 1.5rem 2rem;
            color: #fff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        /* Tipo badge compacto */
        .tipo-badge { font-size: 0.6rem; padding: 2px 6px; border-radius: 3px; font-weight: 600; text-transform: uppercase; }
        .tipo-badge.estructura { background: #3498db22; color: #3498db; }
        .tipo-badge.proceso { background: #f39c1222; color: #f39c12; }
        .tipo-badge.resultado { background: #27ae6022; color: #27ae60; }

        /* Badge mínimo */
        .badge-minimo { font-size: 0.55rem; background: #e74c3c; vertical-align: top; }
        /* Colores custom para categorias sin clase Bootstrap bg-* */
        .bg-orange { background-color: #fd7e14 !important; color: #fff !important; }
        .text-orange { color: #fd7e14 !important; }
        .bg-purple { background-color: #6f42c1 !important; color: #fff !important; }
        .text-purple { color: #6f42c1 !important; }
        .bg-teal { background-color: #20c997 !important; color: #fff !important; }
        .text-teal { color: #20c997 !important; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard Indicadores SST
            </a>
            <div class="navbar-nav ms-auto d-flex flex-row gap-2">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-building me-1"></i><?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>" class="btn btn-outline-info btn-sm" title="Ver indicadores con opción de Ficha Técnica individual" target="_blank">
                    <i class="bi bi-file-earmark-text me-1"></i>Fichas Técnicas
                </a>
                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente'] . '/matriz-objetivos-metas') ?>" class="btn btn-outline-success btn-sm" target="_blank">
                    <i class="bi bi-table me-1"></i>Matriz Objetivos y Metas
                </a>
                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-list-ul me-1"></i>Lista CRUD
                </a>
                <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- ============================================================ -->
        <!-- HEADER: Nivel 1 (Gauge Maestro) + Mínimos Res. 0312         -->
        <!-- ============================================================ -->
        <div class="row mb-4">
            <!-- Gauge Maestro -->
            <div class="col-lg-5">
                <div class="header-dark h-100">
                    <div class="row align-items-center">
                        <div class="col-7">
                            <div class="gauge-master">
                                <canvas id="gaugeMaestro"></canvas>
                            </div>
                        </div>
                        <div class="col-5">
                            <h6 class="text-white-50 mb-1">Cumplimiento Global</h6>
                            <h2 class="text-warning mb-1" id="globalPct"><?= $dashboard['nivel1']['global'] ?>%</h2>
                            <p class="mb-2 small text-white-50">SG-SST <?= date('Y') ?></p>
                            <div class="d-flex gap-2">
                                <span class="badge bg-<?= $dashboard['nivel1']['semaforo'] === 'verde' ? 'success' : ($dashboard['nivel1']['semaforo'] === 'amarillo' ? 'warning' : ($dashboard['nivel1']['semaforo'] === 'rojo' ? 'danger' : 'secondary')) ?>">
                                    <?= ucfirst($dashboard['nivel1']['semaforo']) ?>
                                </span>
                                <span class="badge bg-dark"><?= $dashboard['total_indicadores'] ?> ind.</span>
                            </div>
                            <hr class="border-secondary my-2">
                            <div class="small text-white-50">
                                <div><i class="bi bi-building-gear me-1" style="color:#3498db"></i>Estructura: <?= $dashboard['nivel2']['estructura']['valor'] ?>%</div>
                                <div><i class="bi bi-gear-wide-connected me-1" style="color:#f39c12"></i>Proceso: <?= $dashboard['nivel2']['proceso']['valor'] ?>%</div>
                                <div><i class="bi bi-trophy me-1" style="color:#27ae60"></i>Resultado: <?= $dashboard['nivel2']['resultado']['valor'] ?>%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mínimos Obligatorios Res. 0312 -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <span class="fw-bold small">
                            <i class="bi bi-shield-check me-1 text-danger"></i>Indicadores Minimos Obligatorios
                            <span class="text-muted">(Res. 0312/2019, Art. 30)</span>
                        </span>
                        <span class="badge bg-<?= $minimos['porcentaje'] >= 100 ? 'success' : ($minimos['porcentaje'] >= 50 ? 'warning' : 'danger') ?>">
                            <?= $minimos['cumplen'] ?>/<?= $minimos['total'] ?> cumplen
                        </span>
                    </div>
                    <div class="card-body py-2">
                        <?php if (empty($minimos['indicadores'])): ?>
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-info-circle me-1"></i>
                                No hay indicadores marcados como minimos obligatorios.
                                <br><small>Marque los 6 indicadores de Res. 0312 (IF, IS, PATM, PEL, IEL, ACM) desde la lista CRUD.</small>
                            </div>
                        <?php else: ?>
                            <div class="row g-2">
                                <?php foreach ($minimos['indicadores'] as $m): ?>
                                    <?php
                                    $nombre = $m['nombre_indicador'];
                                    // Abreviar nombres largos
                                    $abrev = $nombre;
                                    if (stripos($nombre, 'Frecuencia') !== false) $abrev = 'IF - Frecuencia AT';
                                    elseif (stripos($nombre, 'Severidad') !== false) $abrev = 'IS - Severidad AT';
                                    elseif (stripos($nombre, 'mortales') !== false || stripos($nombre, 'mortalidad') !== false) $abrev = 'PATM - Mortalidad';
                                    elseif (stripos($nombre, 'Prevalencia') !== false) $abrev = 'PEL - Prevalencia EL';
                                    elseif (stripos($nombre, 'Incidencia') !== false) $abrev = 'IEL - Incidencia EL';
                                    elseif (stripos($nombre, 'Ausentismo') !== false) $abrev = 'ACM - Ausentismo';

                                    $valorReal = $m['valor_resultado'] ?? null;
                                    $meta = $m['meta'] ?? 0;
                                    $cumple = $m['cumple_meta'];
                                    $colorBg = $cumple === null ? 'bg-light' : ($cumple == 1 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10');
                                    $colorBorder = $cumple === null ? 'border-secondary' : ($cumple == 1 ? 'border-success' : 'border-danger');
                                    ?>
                                    <div class="col-6 col-md-4 col-xl-2">
                                        <div class="minimo-card border <?= $colorBorder ?> <?= $colorBg ?>">
                                            <div class="minimo-valor <?= $cumple === null ? 'text-muted' : ($cumple == 1 ? 'text-success' : 'text-danger') ?>">
                                                <?= $valorReal !== null ? number_format($valorReal, 1) : '--' ?>
                                            </div>
                                            <div class="minimo-meta">Meta: <?= number_format($meta, 1) ?><?= $m['unidad_medida'] ?></div>
                                            <div class="fw-bold" style="font-size:0.65rem"><?= esc($abrev) ?></div>
                                            <?php if ($cumple !== null): ?>
                                                <i class="bi <?= $cumple == 1 ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?>" style="font-size:0.8rem"></i>
                                            <?php else: ?>
                                                <span class="badge bg-secondary" style="font-size:0.55rem">Sin medir</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- NIVEL 2: 3 Gauges por Tipo Legal                            -->
        <!-- ============================================================ -->
        <div class="row mb-4 g-3">
            <?php
            $tipoConfig = [
                'estructura' => ['label' => 'ESTRUCTURA', 'sublabel' => 'Planear', 'icon' => 'bi-building-gear', 'color' => '#3498db', 'art' => 'Art. 2.2.4.6.20'],
                'proceso'    => ['label' => 'PROCESO', 'sublabel' => 'Hacer', 'icon' => 'bi-gear-wide-connected', 'color' => '#f39c12', 'art' => 'Art. 2.2.4.6.21'],
                'resultado'  => ['label' => 'RESULTADO', 'sublabel' => 'Verificar/Actuar', 'icon' => 'bi-trophy', 'color' => '#27ae60', 'art' => 'Art. 2.2.4.6.22'],
            ];
            ?>
            <?php foreach ($tipoConfig as $tipo => $cfg): ?>
                <?php $d = $dashboard['nivel2'][$tipo]; ?>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center py-3">
                            <div class="gauge-tipo">
                                <canvas id="gauge_<?= $tipo ?>"></canvas>
                            </div>
                            <h6 class="mb-0 mt-2" style="color: <?= $cfg['color'] ?>">
                                <i class="bi <?= $cfg['icon'] ?> me-1"></i><?= $cfg['label'] ?>
                            </h6>
                            <small class="text-muted"><?= $cfg['sublabel'] ?> &mdash; <?= $cfg['art'] ?></small>
                            <div class="d-flex justify-content-center gap-3 mt-2 small">
                                <span class="text-success"><i class="bi bi-check-circle"></i> <?= $d['cumplen'] ?></span>
                                <span class="text-danger"><i class="bi bi-x-circle"></i> <?= $d['no_cumplen'] ?></span>
                                <span class="text-muted"><i class="bi bi-clock"></i> <?= $d['sin_medir'] ?></span>
                            </div>
                            <div class="text-muted" style="font-size:0.7rem"><?= $d['total'] ?> indicadores</div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ============================================================ -->
        <!-- NIVEL 3: Gauges por Categoría                                -->
        <!-- ============================================================ -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-2">
                <span class="fw-bold small"><i class="bi bi-grid-3x3-gap me-1 text-primary"></i>Indicadores por Categoria</span>
                <span class="text-muted small ms-2">(click para ver detalle)</span>
            </div>
            <div class="card-body">
                <div class="row g-2" id="categoriasGrid">
                    <?php foreach ($dashboard['nivel3'] as $cat => $catData): ?>
                        <?php
                        $catConfig = $categorias[$cat] ?? ['nombre' => $cat, 'icono' => 'bi-three-dots', 'color' => 'secondary'];
                        $pct = $catData['valor'];
                        $semaforoCat = $pct >= 85 ? 'success' : ($pct >= 60 ? 'warning' : ($catData['medidos'] > 0 ? 'danger' : 'secondary'));
                        ?>
                        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                            <div class="cat-card card h-100 text-center p-2" data-cat="<?= $cat ?>" onclick="toggleDrillDown('<?= $cat ?>')">
                                <div class="gauge-categoria">
                                    <canvas id="gauge_cat_<?= $cat ?>"></canvas>
                                </div>
                                <div class="fw-bold" style="font-size:0.7rem">
                                    <?= esc($catConfig['nombre']) ?>
                                    <?php if ($catData['es_minimo']): ?>
                                        <span class="badge badge-minimo text-white">0312</span>
                                    <?php endif; ?>
                                </div>
                                <div class="stacked-bar mt-1">
                                    <?php
                                    $totalCat = max(1, $catData['total']);
                                    $pctE = ($catData['por_tipo']['estructura']['total'] / $totalCat) * 100;
                                    $pctP = ($catData['por_tipo']['proceso']['total'] / $totalCat) * 100;
                                    $pctR = ($catData['por_tipo']['resultado']['total'] / $totalCat) * 100;
                                    ?>
                                    <div class="bar-e" style="width:<?= $pctE ?>%"></div>
                                    <div class="bar-p" style="width:<?= $pctP ?>%"></div>
                                    <div class="bar-r" style="width:<?= $pctR ?>%"></div>
                                </div>
                                <div style="font-size:0.6rem" class="text-muted mt-1"><?= $catData['total'] ?> ind.</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- ============================================================ -->
                <!-- NIVEL 4: Drill-down de indicadores individuales              -->
                <!-- ============================================================ -->
                <?php foreach ($dashboard['nivel3'] as $cat => $catData): ?>
                    <?php
                    $catConfig = $categorias[$cat] ?? ['nombre' => $cat, 'icono' => 'bi-three-dots', 'color' => 'secondary'];
                    ?>
                    <div class="drill-down mt-3" id="drill_<?= $cat ?>">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">
                                <i class="bi <?= $catConfig['icono'] ?> me-1 text-<?= $catConfig['color'] ?>"></i>
                                <?= esc($catConfig['nombre']) ?>
                                <span class="badge bg-<?= $catConfig['color'] ?>"><?= $catData['total'] ?></span>
                            </h6>
                            <div class="small">
                                <span style="color:#3498db"><i class="bi bi-square-fill"></i> E:<?= $catData['por_tipo']['estructura']['total'] ?></span>
                                <span style="color:#f39c12" class="ms-1"><i class="bi bi-square-fill"></i> P:<?= $catData['por_tipo']['proceso']['total'] ?></span>
                                <span style="color:#27ae60" class="ms-1"><i class="bi bi-square-fill"></i> R:<?= $catData['por_tipo']['resultado']['total'] ?></span>
                            </div>
                        </div>
                        <div id="indicadores_<?= $cat ?>">
                            <!-- Se carga vía JS o se pre-renderiza -->
                            <div class="text-center text-muted small py-2">
                                <i class="bi bi-arrow-clockwise spin me-1"></i>Cargando indicadores...
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    // ═══════════════════════════════════════════════════════════
    // DATA del backend
    // ═══════════════════════════════════════════════════════════
    const DASHBOARD = <?= json_encode($dashboard, JSON_UNESCAPED_UNICODE) ?>;
    const CATEGORIAS = <?= json_encode($categorias, JSON_UNESCAPED_UNICODE) ?>;
    const ID_CLIENTE = <?= (int)$cliente['id_cliente'] ?>;
    const BASE_URL = '<?= base_url() ?>';

    // ═══════════════════════════════════════════════════════════
    // PLUGIN: Texto central para gauges
    // ═══════════════════════════════════════════════════════════
    const centerTextPlugin = {
        id: 'centerText',
        afterDraw(chart) {
            if (!chart.config.options.plugins.centerText) return;
            const { ctx, width, height } = chart;
            const cfg = chart.config.options.plugins.centerText;
            ctx.save();
            // Número principal
            ctx.font = `bold ${cfg.fontSize || '1.5rem'} Segoe UI`;
            ctx.fillStyle = cfg.color || '#333';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            const yOffset = cfg.sublabel ? -6 : 0;
            ctx.fillText(cfg.text || '', width / 2, height * 0.58 + yOffset);
            // Sublabel
            if (cfg.sublabel) {
                ctx.font = `${cfg.subFontSize || '0.6rem'} Segoe UI`;
                ctx.fillStyle = '#6c757d';
                ctx.fillText(cfg.sublabel, width / 2, height * 0.58 + 14);
            }
            ctx.restore();
        }
    };
    Chart.register(centerTextPlugin);

    // ═══════════════════════════════════════════════════════════
    // HELPER: Crear gauge semicircular
    // ═══════════════════════════════════════════════════════════
    function crearGauge(canvasId, valor, color, label, opts = {}) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        const semaforoColor = valor >= 85 ? '#27ae60' : (valor >= 60 ? '#f39c12' : '#e74c3c');
        const useColor = color || semaforoColor;

        return new Chart(canvas, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [valor, Math.max(0, 100 - valor)],
                    backgroundColor: [useColor, '#e9ecef'],
                    borderWidth: 0
                }]
            },
            options: {
                rotation: -90,
                circumference: 180,
                cutout: opts.cutout || '75%',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false },
                    centerText: {
                        text: valor + '%',
                        sublabel: label || '',
                        color: semaforoColor,
                        fontSize: opts.fontSize || '1.5rem',
                        subFontSize: opts.subFontSize || '0.6rem'
                    }
                }
            }
        });
    }

    // ═══════════════════════════════════════════════════════════
    // HELPER: Crear sparkline mini
    // ═══════════════════════════════════════════════════════════
    function crearSparkline(canvasId, datos, meta) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !datos || datos.length === 0) return null;

        return new Chart(canvas, {
            type: 'line',
            data: {
                labels: datos.map(d => d.periodo),
                datasets: [
                    {
                        data: datos.map(d => parseFloat(d.valor_resultado || 0)),
                        borderColor: '#3498db',
                        borderWidth: 1.5,
                        pointRadius: 1.5,
                        fill: false,
                        tension: 0.3
                    },
                    {
                        data: Array(datos.length).fill(meta),
                        borderColor: '#e74c3c',
                        borderWidth: 1,
                        borderDash: [3, 3],
                        pointRadius: 0,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } }
            }
        });
    }

    // ═══════════════════════════════════════════════════════════
    // RENDER: Nivel 4 - Indicadores individuales
    // ═══════════════════════════════════════════════════════════
    function renderIndicadores(cat, indicadores) {
        const container = document.getElementById('indicadores_' + cat);
        if (!container) return;

        if (!indicadores || indicadores.length === 0) {
            container.innerHTML = '<div class="text-muted small text-center py-2">Sin indicadores en esta categoria</div>';
            return;
        }

        let html = '';
        indicadores.forEach((ind, i) => {
            const tipo = ind.tipo_indicador || 'proceso';
            const cumple = ind.cumple_meta;
            const valorReal = ind.valor_resultado !== null ? parseFloat(ind.valor_resultado) : null;
            const meta = ind.meta !== null ? parseFloat(ind.meta) : null;
            const pct = (meta && meta > 0 && valorReal !== null) ? Math.min(100, (valorReal / meta) * 100) : 0;
            const esMinimo = ind.es_minimo_obligatorio == 1;

            let estadoIcon, estadoClass;
            if (cumple === null || cumple === '') {
                estadoIcon = 'bi-clock text-muted';
                estadoClass = '';
            } else if (cumple == 1) {
                estadoIcon = 'bi-check-circle-fill text-success';
                estadoClass = 'border-success';
            } else {
                estadoIcon = 'bi-x-circle-fill text-danger';
                estadoClass = 'border-danger';
            }

            const barColor = cumple == 1 ? '#27ae60' : (cumple == 0 ? '#e74c3c' : '#6c757d');
            const sparkId = 'spark_' + cat + '_' + i;

            html += `
            <div class="ind-row tipo-${tipo} d-flex align-items-center gap-2">
                <i class="bi ${estadoIcon}" style="font-size:1.1rem"></i>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-1">
                        <span class="tipo-badge ${tipo}">${tipo.charAt(0).toUpperCase()}</span>
                        <span class="fw-semibold small">${ind.nombre_indicador}</span>
                        ${esMinimo ? '<span class="badge badge-minimo text-white ms-1">0312</span>' : ''}
                    </div>
                    <div class="progress mt-1" style="height:4px">
                        <div class="progress-bar" style="width:${pct}%; background:${barColor}"></div>
                    </div>
                    <div class="d-flex justify-content-between" style="font-size:0.65rem">
                        <span class="text-muted">${ind.formula || ''}</span>
                        <span>
                            ${valorReal !== null ? valorReal.toFixed(1) : '--'}
                            / Meta: ${meta !== null ? meta : '--'}${ind.unidad_medida || '%'}
                        </span>
                    </div>
                </div>
                <div class="sparkline-container">
                    <canvas id="${sparkId}"></canvas>
                </div>
                <a href="${BASE_URL}/indicadores-sst/${ID_CLIENTE}/editar/${ind.id_indicador}"
                   class="btn btn-outline-secondary btn-sm" style="font-size:0.65rem" title="Editar">
                    <i class="bi bi-pencil"></i>
                </a>
            </div>`;
        });

        container.innerHTML = html;

        // Crear sparklines después de inyectar HTML
        indicadores.forEach((ind, i) => {
            if (ind.historico && ind.historico.length > 0) {
                crearSparkline('spark_' + cat + '_' + i, ind.historico.reverse(), ind.meta);
            }
        });
    }

    // ═══════════════════════════════════════════════════════════
    // DRILL-DOWN: Toggle categoría
    // ═══════════════════════════════════════════════════════════
    let drillDownActivo = null;
    let indicadoresCache = {};

    function toggleDrillDown(cat) {
        // Desactivar cards
        document.querySelectorAll('.cat-card').forEach(c => c.classList.remove('active'));

        const panel = document.getElementById('drill_' + cat);
        if (!panel) return;

        // Si ya está abierto, cerrar
        if (drillDownActivo === cat) {
            panel.classList.remove('show');
            drillDownActivo = null;
            return;
        }

        // Cerrar anterior
        document.querySelectorAll('.drill-down').forEach(d => d.classList.remove('show'));

        // Activar este
        document.querySelector(`.cat-card[data-cat="${cat}"]`)?.classList.add('active');
        panel.classList.add('show');
        drillDownActivo = cat;

        // Cargar datos si no están en cache
        if (indicadoresCache[cat]) {
            renderIndicadores(cat, indicadoresCache[cat]);
        } else {
            cargarIndicadoresCategoria(cat);
        }
    }

    async function cargarIndicadoresCategoria(cat) {
        try {
            const resp = await fetch(`${BASE_URL}/indicadores-sst/${ID_CLIENTE}/api/dashboard`);
            const json = await resp.json();
            if (json.success && json.data.indicadores_por_categoria) {
                // Cache todas las categorías
                for (const [c, inds] of Object.entries(json.data.indicadores_por_categoria)) {
                    indicadoresCache[c] = inds;
                }
                renderIndicadores(cat, indicadoresCache[cat] || []);
            }
        } catch (e) {
            console.error('Error cargando indicadores:', e);
            const container = document.getElementById('indicadores_' + cat);
            if (container) container.innerHTML = '<div class="text-danger small">Error al cargar</div>';
        }
    }

    // ═══════════════════════════════════════════════════════════
    // INIT: Crear todos los gauges
    // ═══════════════════════════════════════════════════════════
    document.addEventListener('DOMContentLoaded', function() {
        // Nivel 1: Gauge maestro
        crearGauge('gaugeMaestro', DASHBOARD.nivel1.global, null, 'SG-SST', {
            fontSize: '2rem', subFontSize: '0.7rem', cutout: '72%'
        });

        // Nivel 2: Gauges por tipo
        const tipoColors = { estructura: '#3498db', proceso: '#f39c12', resultado: '#27ae60' };
        for (const [tipo, color] of Object.entries(tipoColors)) {
            const val = DASHBOARD.nivel2[tipo]?.valor || 0;
            crearGauge('gauge_' + tipo, val, color, '', {
                fontSize: '1.3rem', cutout: '72%'
            });
        }

        // Nivel 3: Gauges por categoría
        for (const [cat, data] of Object.entries(DASHBOARD.nivel3)) {
            crearGauge('gauge_cat_' + cat, data.valor, null, '', {
                fontSize: '0.9rem', cutout: '70%'
            });
        }
    });
    </script>
</body>
</html>