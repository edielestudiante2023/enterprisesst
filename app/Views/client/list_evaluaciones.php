<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación del Cliente</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Navbar */
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 10px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar-custom img {
            max-height: 50px;
            width: auto;
        }

        @media (max-width: 768px) {
            .navbar-custom img {
                max-height: 35px;
            }
            .navbar-custom .btn {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
        }

        /* Header */
        .header-section {
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        /* Indicadores */
        .indicator-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            height: 100%;
        }

        .indicator-card.pink { border-left: 4px solid #e91e63; }
        .indicator-card.cyan { border-left: 4px solid #00bcd4; }
        .indicator-card.green { border-left: 4px solid #4caf50; }

        .indicator-value {
            font-size: 2rem;
            font-weight: bold;
            color: #1c2437;
        }

        .indicator-label {
            font-size: 0.85rem;
            color: #6c757d;
        }

        /* Estado Alert */
        .estado-alert {
            border-radius: 10px;
            padding: 1rem;
        }

        /* Botones de acción */
        .btn-action {
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-volver {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }

        .btn-export {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
        }

        .btn-add {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border: none;
            color: white;
        }

        /* Acordeón PHVA - Nivel 1 */
        .accordion-phva .accordion-item {
            border: none;
            margin-bottom: 0.5rem;
            border-radius: 10px !important;
            overflow: hidden;
        }

        .accordion-phva .accordion-button {
            font-weight: 700;
            font-size: 1.1rem;
            padding: 1rem 1.25rem;
            border-radius: 10px !important;
        }

        .accordion-phva .accordion-button:not(.collapsed) {
            color: white;
            box-shadow: none;
        }

        .accordion-phva .accordion-button::after {
            filter: brightness(0) invert(1);
        }

        .accordion-phva .accordion-button.collapsed::after {
            filter: none;
        }

        .accordion-phva .accordion-body {
            padding: 0.75rem;
            background-color: #f8f9fa;
        }

        /* Colores PHVA */
        .phva-planear .accordion-button { background-color: #e3f2fd; color: #1565c0; }
        .phva-planear .accordion-button:not(.collapsed) { background-color: #1565c0; }

        .phva-hacer .accordion-button { background-color: #e8f5e9; color: #2e7d32; }
        .phva-hacer .accordion-button:not(.collapsed) { background-color: #2e7d32; }

        .phva-verificar .accordion-button { background-color: #fff3e0; color: #ef6c00; }
        .phva-verificar .accordion-button:not(.collapsed) { background-color: #ef6c00; }

        .phva-actuar .accordion-button { background-color: #fce4ec; color: #c2185b; }
        .phva-actuar .accordion-button:not(.collapsed) { background-color: #c2185b; }

        /* Acordeón Estándar - Nivel 2 */
        .accordion-estandar .accordion-item {
            border: 1px solid #dee2e6;
            margin-bottom: 0.25rem;
        }

        .accordion-estandar .accordion-button {
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.75rem 1rem;
            background-color: white;
        }

        .accordion-estandar .accordion-button:not(.collapsed) {
            background-color: #e9ecef;
            color: #1c2437;
        }

        .accordion-estandar .accordion-body {
            padding: 0;
        }

        /* Tabla dentro del acordeón */
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-accordion {
            margin-bottom: 0;
            font-size: 0.85rem;
        }

        .table-accordion th {
            background-color: #343a40;
            color: white;
            font-weight: 600;
            white-space: nowrap;
            padding: 0.5rem;
            position: sticky;
            top: 0;
        }

        .table-accordion td {
            padding: 0.5rem;
            vertical-align: middle;
            max-width: 300px;
        }

        .table-accordion tr:hover {
            background-color: #f1f3f4;
        }

        /* Badges */
        .badge-cumple { background-color: #28a745; }
        .badge-no-cumple { background-color: #dc3545; }
        .badge-parcial { background-color: #ffc107; color: #212529; }
        .badge-no-aplica { background-color: #17a2b8; }
        .badge-sin-evaluar { background-color: #6c757d; }

        /* Resumen en acordeón */
        .accordion-summary {
            font-size: 0.85rem;
            font-weight: normal;
            margin-left: auto;
            display: flex;
            gap: 0.75rem;
        }

        .accordion-summary span {
            background: rgba(255,255,255,0.3);
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
        }

        .estandar-summary {
            font-size: 0.8rem;
            font-weight: normal;
            color: #6c757d;
            margin-left: 0.5rem;
        }

        /* Filtros */
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }

        /* Loading */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading-overlay.show {
            display: flex;
        }

        /* Indicadores pequeños */
        .mini-indicators {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .mini-indicator {
            background: white;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            min-width: 120px;
        }

        .mini-indicator.success { border-top: 3px solid #28a745; }
        .mini-indicator.danger { border-top: 3px solid #dc3545; }
        .mini-indicator.warning { border-top: 3px solid #ffc107; }

        .mini-indicator h6 {
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .mini-indicator .value {
            font-size: 1.25rem;
            font-weight: bold;
        }

        /* Footer */
        footer {
            background: white;
            border-top: 1px solid #dee2e6;
            padding: 2rem 0;
            margin-top: 2rem;
        }

        footer a {
            color: #007bff;
            text-decoration: none;
        }

        .social-icons img {
            height: 24px;
            width: 24px;
            transition: transform 0.2s;
        }

        .social-icons img:hover {
            transform: scale(1.2);
        }

        /* Sin resultados */
        .no-results {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .no-results i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        /* Celda expandible */
        .cell-expandable {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
        }

        .cell-expandable:hover {
            white-space: normal;
            overflow: visible;
            background: #fff;
            position: relative;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
    </style>
</head>

<body>
    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Aplicando filtros...</p>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar-custom">
        <div class="container-fluid d-flex justify-content-between align-items-center px-3">
            <div class="d-flex align-items-center gap-2">
                <a href="https://dashboard.cycloidtalent.com/login">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst">
                </a>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST">
                </a>
                <a href="https://cycloidtalent.com/">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids">
                </a>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="<?= base_url('/addEvaluacion') ?>" class="btn btn-success btn-sm" target="_blank">
                    <i class="fas fa-plus"></i> Añadir
                </a>
            </div>
        </div>
    </nav>

    <div style="height: 80px;"></div>

    <div class="container-fluid px-3">
        <!-- Header -->
        <div class="header-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-1"><i class="fas fa-clipboard-check"></i> Evaluación Estándares Mínimos</h4>
                    <p class="mb-0">Cliente: <strong><?= esc($client['nombre_cliente']) ?></strong></p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="indicator-value text-warning"><?= number_format($indicador_general * 100, 0) ?>%</div>
                    <small>Cumplimiento Decreto 1072</small>
                </div>
            </div>
        </div>

        <!-- Indicadores principales -->
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="indicator-card pink">
                    <div class="indicator-value"><?= esc($sum_valor) ?></div>
                    <div class="indicator-label">Puntuación Actual</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="indicator-card cyan">
                    <div class="indicator-value"><?= esc($sum_puntaje_cuantitativo) ?></div>
                    <div class="indicator-label">Puntuación Máxima</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="indicator-card green">
                    <div class="indicator-value"><?= number_format($indicador_general * 100, 0) ?>%</div>
                    <div class="indicator-label">Indicador General</div>
                </div>
            </div>
        </div>

        <!-- Mini indicadores por categoría -->
        <div class="mini-indicators mb-3">
            <div class="mini-indicator success">
                <h6>Cumple</h6>
                <div class="value text-success"><?= esc($count_cumple) ?></div>
            </div>
            <div class="mini-indicator danger">
                <h6>No Cumple</h6>
                <div class="value text-danger"><?= esc($count_no_cumple) ?></div>
            </div>
            <div class="mini-indicator warning">
                <h6>No Aplica</h6>
                <div class="value text-warning"><?= esc($count_no_aplica) ?></div>
            </div>
        </div>

        <!-- Estado del cliente -->
        <div class="alert estado-alert <?php if ($indicador_general < 0.6): ?>alert-danger<?php elseif ($indicador_general <= 0.85): ?>alert-warning<?php else: ?>alert-success<?php endif; ?> mb-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap">
                <div>
                    <strong>Estado: </strong>
                    <?php if ($indicador_general < 0.6): ?>
                        <span class="badge bg-danger">CRÍTICO</span> - Plan de mejoramiento inmediato requerido
                    <?php elseif ($indicador_general <= 0.85): ?>
                        <span class="badge bg-warning text-dark">MODERADO</span> - Plan de mejoramiento requerido
                    <?php else: ?>
                        <span class="badge bg-success">ACEPTABLE</span> - Mantener y continuar mejorando
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Botones de acción y Filtros -->
        <div class="row mb-3">
            <div class="col-lg-8">
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-action btn-volver">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <button class="btn btn-action btn-export" id="btnExportExcel">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" id="btnExpandAll">
                        <i class="fas fa-expand-alt"></i> Expandir
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" id="btnCollapseAll">
                        <i class="fas fa-compress-alt"></i> Colapsar
                    </button>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="filterPHVA">
                        <option value="">Todos los ciclos</option>
                    </select>
                    <select class="form-select form-select-sm" id="filterCalificacion">
                        <option value="">Todas las calificaciones</option>
                    </select>
                    <button class="btn btn-outline-danger btn-sm" id="btnClearFilters" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Acordeón principal por PHVA -->
        <div class="accordion accordion-phva" id="accordionPHVA">
            <!-- Se genera dinámicamente con JavaScript -->
        </div>

        <!-- Sin resultados -->
        <div class="no-results" id="noResults" style="display: none;">
            <i class="fas fa-search"></i>
            <h5>No se encontraron resultados</h5>
            <p>Intenta ajustar los filtros de búsqueda</p>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p class="fw-bold mb-1">Cycloid Talent SAS</p>
            <p class="mb-1 small">Todos los derechos reservados © 2024 | NIT: 901.653.912</p>
            <p class="mb-2 small">
                <a href="https://cycloidtalent.com/" target="_blank">cycloidtalent.com</a>
            </p>
            <div class="social-icons d-flex justify-content-center gap-3">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn">
                </a>
                <a href="https://www.instagram.com/cycloid_talent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok">
                </a>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        // Datos del servidor
        const evaluaciones = <?= json_encode($evaluaciones) ?>;
        const clientName = '<?= esc($client['nombre_cliente']) ?>';

        // Orden PHVA
        const phvaOrder = ['I. PLANEAR', 'II. HACER', 'III. VERIFICAR', 'IV. ACTUAR', 'PLANEAR', 'HACER', 'VERIFICAR', 'ACTUAR'];

        // Colores PHVA
        const phvaClasses = {
            'I. PLANEAR': 'phva-planear',
            'II. HACER': 'phva-hacer',
            'III. VERIFICAR': 'phva-verificar',
            'IV. ACTUAR': 'phva-actuar',
            'PLANEAR': 'phva-planear',
            'HACER': 'phva-hacer',
            'VERIFICAR': 'phva-verificar',
            'ACTUAR': 'phva-actuar'
        };

        $(document).ready(function() {
            populateFilters();
            renderAccordion(evaluaciones);

            // Filtros
            $('#filterPHVA, #filterCalificacion').on('change', applyFilters);

            // Limpiar filtros
            $('#btnClearFilters').on('click', function() {
                $('#filterPHVA, #filterCalificacion').val('');
                applyFilters();
            });

            // Expandir todo
            $('#btnExpandAll').on('click', function() {
                $('.accordion-button.collapsed').each(function() {
                    $(this).click();
                });
            });

            // Colapsar todo
            $('#btnCollapseAll').on('click', function() {
                $('.accordion-button:not(.collapsed)').each(function() {
                    $(this).click();
                });
            });

            // Exportar Excel
            $('#btnExportExcel').on('click', exportToExcel);
        });

        function populateFilters() {
            // Ciclos únicos
            const ciclos = [...new Set(evaluaciones.map(e => e.ciclo).filter(Boolean))].sort((a, b) => {
                return phvaOrder.indexOf(a) - phvaOrder.indexOf(b);
            });
            ciclos.forEach(c => {
                $('#filterPHVA').append(`<option value="${c}">${c}</option>`);
            });

            // Calificaciones únicas
            const califs = [...new Set(evaluaciones.map(e => e.evaluacion_inicial || 'SIN EVALUAR'))].sort();
            califs.forEach(c => {
                $('#filterCalificacion').append(`<option value="${c}">${c}</option>`);
            });
        }

        function getCalificacionBadge(calificacion) {
            const calif = calificacion || 'SIN EVALUAR';
            let badgeClass = 'badge-sin-evaluar';

            if (calif.includes('CUMPLE TOTALMENTE') || calif === 'CUMPLE') badgeClass = 'badge-cumple';
            else if (calif.includes('NO CUMPLE')) badgeClass = 'badge-no-cumple';
            else if (calif.includes('PARCIAL')) badgeClass = 'badge-parcial';
            else if (calif.includes('NO APLICA')) badgeClass = 'badge-no-aplica';

            return `<span class="badge ${badgeClass}">${calif}</span>`;
        }

        function renderAccordion(data) {
            // Agrupar por Ciclo y luego por Estándar
            const grouped = {};

            data.forEach(item => {
                const ciclo = item.ciclo || 'SIN CICLO';
                const estandar = item.estandar || 'SIN ESTÁNDAR';

                if (!grouped[ciclo]) grouped[ciclo] = {};
                if (!grouped[ciclo][estandar]) grouped[ciclo][estandar] = [];

                grouped[ciclo][estandar].push(item);
            });

            let html = '';
            let phvaIndex = 0;

            // Ordenar por PHVA
            const sortedCiclos = Object.keys(grouped).sort((a, b) => {
                const idxA = phvaOrder.findIndex(p => a.includes(p) || p.includes(a));
                const idxB = phvaOrder.findIndex(p => b.includes(p) || p.includes(b));
                return (idxA === -1 ? 999 : idxA) - (idxB === -1 ? 999 : idxB);
            });

            sortedCiclos.forEach(ciclo => {
                const estandares = grouped[ciclo];
                const cicloClass = phvaClasses[ciclo] || 'phva-planear';

                // Totales del ciclo
                let cicloValor = 0, cicloPuntaje = 0, cicloItems = 0;
                Object.values(estandares).forEach(items => {
                    items.forEach(item => {
                        cicloValor += parseFloat(item.valor || 0);
                        cicloPuntaje += parseFloat(item.puntaje_cuantitativo || 0);
                        cicloItems++;
                    });
                });

                html += `
                <div class="accordion-item ${cicloClass}">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#phva-${phvaIndex}">
                            <i class="fas fa-layer-group me-2"></i> ${ciclo}
                            <div class="accordion-summary">
                                <span><i class="fas fa-list"></i> ${cicloItems}</span>
                                <span><i class="fas fa-star"></i> ${cicloValor.toFixed(1)}/${cicloPuntaje.toFixed(1)}</span>
                            </div>
                        </button>
                    </h2>
                    <div id="phva-${phvaIndex}" class="accordion-collapse collapse" data-bs-parent="#accordionPHVA">
                        <div class="accordion-body">
                            <div class="accordion accordion-estandar" id="accordionEst-${phvaIndex}">
                `;

                let estIndex = 0;
                Object.keys(estandares).sort().forEach(estandar => {
                    const items = estandares[estandar];
                    let estValor = 0, estPuntaje = 0;
                    items.forEach(item => {
                        estValor += parseFloat(item.valor || 0);
                        estPuntaje += parseFloat(item.puntaje_cuantitativo || 0);
                    });

                    html += `
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#est-${phvaIndex}-${estIndex}">
                                ${estandar}
                                <span class="estandar-summary">(${items.length} | ${estValor.toFixed(1)}/${estPuntaje.toFixed(1)})</span>
                            </button>
                        </h2>
                        <div id="est-${phvaIndex}-${estIndex}" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="table-wrapper">
                                    <table class="table table-accordion table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Ítem</th>
                                                <th>Detalle</th>
                                                <th>Evaluación</th>
                                                <th>Valor</th>
                                                <th>Máx</th>
                                                <th>Criterio</th>
                                                <th>Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                    `;

                    items.forEach(item => {
                        html += `
                            <tr>
                                <td class="cell-expandable" title="${item.item_del_estandar || ''}">${item.item_del_estandar || '-'}</td>
                                <td class="cell-expandable" title="${item.detalle_estandar || ''}">${item.detalle_estandar || '-'}</td>
                                <td>${getCalificacionBadge(item.evaluacion_inicial)}</td>
                                <td class="text-end">${parseFloat(item.valor || 0).toFixed(1)}</td>
                                <td class="text-end">${parseFloat(item.puntaje_cuantitativo || 0).toFixed(1)}</td>
                                <td class="cell-expandable" title="${item.criterio || ''}">${item.criterio || '-'}</td>
                                <td class="cell-expandable" title="${item.observaciones || ''}">${item.observaciones || '-'}</td>
                            </tr>
                        `;
                    });

                    html += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;
                    estIndex++;
                });

                html += `
                            </div>
                        </div>
                    </div>
                </div>
                `;
                phvaIndex++;
            });

            $('#accordionPHVA').html(html);

            if (data.length === 0) {
                $('#noResults').show();
                $('#accordionPHVA').hide();
            } else {
                $('#noResults').hide();
                $('#accordionPHVA').show();
            }
        }

        function applyFilters() {
            $('#loadingOverlay').addClass('show');

            setTimeout(() => {
                const filterPHVA = $('#filterPHVA').val();
                const filterCalif = $('#filterCalificacion').val();

                const filtered = evaluaciones.filter(item => {
                    if (filterPHVA && item.ciclo !== filterPHVA) return false;
                    const itemCalif = item.evaluacion_inicial || 'SIN EVALUAR';
                    if (filterCalif && itemCalif !== filterCalif) return false;
                    return true;
                });

                renderAccordion(filtered);
                $('#loadingOverlay').removeClass('show');
            }, 100);
        }

        function exportToExcel() {
            const exportData = evaluaciones.map(item => ({
                'Ciclo': item.ciclo || '',
                'Estándar': item.estandar || '',
                'Detalle Estándar': item.detalle_estandar || '',
                'Ítem del Estándar': item.item_del_estandar || '',
                'Evaluación Inicial': item.evaluacion_inicial || 'SIN EVALUAR',
                'Valor': parseFloat(item.valor || 0),
                'Puntaje Máximo': parseFloat(item.puntaje_cuantitativo || 0),
                'Ítem': item.item || '',
                'Criterio': item.criterio || '',
                'Modo Verificación': item.modo_de_verificacion || '',
                'Observaciones': item.observaciones || ''
            }));

            const ws = XLSX.utils.json_to_sheet(exportData);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Evaluaciones');

            ws['!cols'] = [
                { wch: 15 }, { wch: 35 }, { wch: 40 }, { wch: 45 },
                { wch: 20 }, { wch: 10 }, { wch: 12 }, { wch: 10 },
                { wch: 40 }, { wch: 30 }, { wch: 40 }
            ];

            const fileName = `Evaluacion_${clientName.replace(/[^a-zA-Z0-9]/g, '_')}.xlsx`;
            XLSX.writeFile(wb, fileName);
        }
    </script>
</body>

</html>
