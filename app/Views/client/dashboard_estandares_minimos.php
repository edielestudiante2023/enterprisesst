<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Estándares Mínimos SST</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-logos {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 10px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar-logos img {
            max-height: 50px;
            width: auto;
        }

        @media (max-width: 576px) {
            .navbar-logos img {
                max-height: 35px;
            }
        }

        .header-section {
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .header-section h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .puntaje-total {
            font-size: 2.5rem;
            font-weight: bold;
            color: #FFD700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .puntaje-filtrado {
            font-size: 1rem;
            color: #adb5bd;
            margin-top: 0.25rem;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            height: 280px;
        }

        .chart-container h6 {
            text-align: center;
            color: #1c2437;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        canvas {
            max-height: 220px;
        }

        .btn-volver {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
        }

        .btn-volver:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .btn-export {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            color: white;
        }

        /* Acordeón PHVA */
        .accordion-phva .accordion-button {
            font-weight: 700;
            font-size: 1.1rem;
            padding: 1rem 1.25rem;
        }

        .accordion-phva .accordion-button:not(.collapsed) {
            color: white;
        }

        .accordion-phva .accordion-body {
            padding: 0.75rem;
            background-color: #f8f9fa;
        }

        /* Colores por ciclo PHVA */
        .phva-planear .accordion-button {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        .phva-planear .accordion-button:not(.collapsed) {
            background-color: #1565c0;
        }

        .phva-hacer .accordion-button {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .phva-hacer .accordion-button:not(.collapsed) {
            background-color: #2e7d32;
        }

        .phva-verificar .accordion-button {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        .phva-verificar .accordion-button:not(.collapsed) {
            background-color: #ef6c00;
        }

        .phva-actuar .accordion-button {
            background-color: #fce4ec;
            color: #c2185b;
        }
        .phva-actuar .accordion-button:not(.collapsed) {
            background-color: #c2185b;
        }

        /* Acordeón Estándar (nivel 2) */
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
            background-color: white;
        }

        /* Tabla dentro del acordeón */
        .table-accordion-wrapper {
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
        }

        .table-accordion tr:hover {
            background-color: #f1f3f4;
        }

        /* Badges de calificación */
        .badge-cumple {
            background-color: #28a745;
            color: white;
        }

        .badge-no-cumple {
            background-color: #dc3545;
            color: white;
        }

        .badge-parcial {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-sin-evaluar {
            background-color: #6c757d;
            color: white;
        }

        .badge-no-aplica {
            background-color: #17a2b8;
            color: white;
        }

        /* Resumen en header del acordeón */
        .phva-summary {
            font-size: 0.85rem;
            font-weight: normal;
            margin-left: auto;
            display: flex;
            gap: 1rem;
        }

        .phva-summary span {
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

        .filter-section label {
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }

        .filter-section .form-select {
            font-size: 0.9rem;
        }

        /* Indicador de filtro activo */
        .filter-active {
            position: relative;
        }

        .filter-active::after {
            content: '';
            position: absolute;
            top: 5px;
            right: 5px;
            width: 8px;
            height: 8px;
            background-color: #dc3545;
            border-radius: 50%;
        }

        /* Expandir/Colapsar todos */
        .btn-expand-all {
            font-size: 0.8rem;
            padding: 0.25rem 0.75rem;
        }

        /* Loading overlay */
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

    <!-- Navbar con logos -->
    <nav class="navbar-logos">
        <div class="container-fluid d-flex justify-content-around align-items-center">
            <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprise SST">
            <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST">
            <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloid">
        </div>
    </nav>

    <div style="height: 80px;"></div>

    <!-- Contenido principal -->
    <div class="container-fluid px-3">
        <!-- Header con puntaje -->
        <div class="header-section">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h1><i class="fas fa-chart-pie"></i> Estándares Mínimos SST</h1>
                    <p class="mb-0"><strong>Cliente:</strong> <?= esc($client['nombre_cliente']) ?></p>
                </div>
                <div class="col-md-5 text-center text-md-end">
                    <div class="puntaje-total" id="puntajeDisplay">
                        <?= $totalCalificado ?> / <?= $totalPosible ?>
                    </div>
                    <p class="mb-0">Puntaje Total</p>
                    <div class="puntaje-filtrado" id="puntajeFiltrado" style="display: none;">
                        Filtrado: <span id="puntajeFiltradoValor">0 / 0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="<?= base_url('client/dashboard') ?>" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <button class="btn-export" id="btnExportExcel">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
            <button class="btn btn-outline-secondary btn-expand-all" id="btnExpandAll">
                <i class="fas fa-expand-alt"></i> Expandir todo
            </button>
            <button class="btn btn-outline-secondary btn-expand-all" id="btnCollapseAll">
                <i class="fas fa-compress-alt"></i> Colapsar todo
            </button>
        </div>

        <!-- Filtros y Gráficos en una fila -->
        <div class="row">
            <!-- Filtros -->
            <div class="col-lg-3">
                <div class="filter-section">
                    <h6 class="mb-3"><i class="fas fa-filter"></i> Filtros</h6>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ciclo PHVA</label>
                        <select class="form-select form-select-sm" id="filterPHVA">
                            <option value="">Todos</option>
                            <?php foreach ($ciclosUnicos as $ciclo): ?>
                                <?php if (!empty($ciclo)): ?>
                                    <option value="<?= esc($ciclo) ?>"><?= esc($ciclo) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Estándar</label>
                        <select class="form-select form-select-sm" id="filterDimension">
                            <option value="">Todos</option>
                            <?php foreach ($dimensionesUnicas as $dim): ?>
                                <?php if (!empty($dim)): ?>
                                    <option value="<?= esc($dim) ?>"><?= esc($dim) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Calificación</label>
                        <select class="form-select form-select-sm" id="filterCalificacion">
                            <option value="">Todas</option>
                            <?php foreach ($calificacionesUnicas as $calif): ?>
                                <option value="<?= esc($calif) ?>"><?= esc($calif) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button class="btn btn-sm btn-outline-danger w-100" id="btnClearFilters">
                        <i class="fas fa-times"></i> Limpiar filtros
                    </button>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="col-lg-9">
                <div class="row">
                    <div class="col-md-4">
                        <div class="chart-container">
                            <h6><i class="fas fa-sync-alt"></i> Ciclo PHVA</h6>
                            <canvas id="chartPhva"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="chart-container">
                            <h6><i class="fas fa-layer-group"></i> Por Estándar</h6>
                            <canvas id="chartDimension"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="chart-container">
                            <h6><i class="fas fa-check-circle"></i> Calificación</h6>
                            <canvas id="chartCalificacion"></canvas>
                        </div>
                    </div>
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

    <div style="height: 50px;"></div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        // Datos originales del servidor
        const originalData = <?= json_encode($evaluaciones) ?>;
        const totalCalificadoOriginal = <?= $totalCalificado ?>;
        const totalPosibleOriginal = <?= $totalPosible ?>;
        const clientName = '<?= esc($client['nombre_cliente']) ?>';

        // Variables globales para gráficos
        let chartPhva, chartDimension, chartCalificacion;

        // Orden de ciclos PHVA
        const phvaOrder = ['PLANEAR', 'HACER', 'VERIFICAR', 'ACTUAR'];

        // Colores PHVA
        const phvaColors = {
            'PLANEAR': '#1565c0',
            'HACER': '#2e7d32',
            'VERIFICAR': '#ef6c00',
            'ACTUAR': '#c2185b'
        };

        // Colores de calificación
        const calificacionColors = {
            'CUMPLE': '#28a745',
            'NO CUMPLE': '#dc3545',
            'CUMPLE PARCIALMENTE': '#ffc107',
            'NO APLICA': '#17a2b8',
            'SIN EVALUAR': '#6c757d'
        };

        $(document).ready(function() {
            initCharts();
            renderAccordion(originalData);

            // Event listeners para filtros
            $('#filterDimension, #filterCalificacion, #filterPHVA').on('change', function() {
                applyFilters();
            });

            // Limpiar filtros
            $('#btnClearFilters').on('click', function() {
                $('#filterDimension, #filterCalificacion, #filterPHVA').val('');
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

        function initCharts() {
            const phvaData = <?= json_encode($phvaCounts) ?>;
            const dimensionData = <?= json_encode($dimensionCounts) ?>;
            const calificacionData = <?= json_encode($calificacionCounts) ?>;

            // Gráfico PHVA
            const ctxPhva = document.getElementById('chartPhva').getContext('2d');
            chartPhva = new Chart(ctxPhva, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(phvaData),
                    datasets: [{
                        data: Object.values(phvaData),
                        backgroundColor: Object.keys(phvaData).map(k => phvaColors[k] || '#999')
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { size: 10 } } }
                    }
                }
            });

            // Gráfico Dimensión
            const ctxDimension = document.getElementById('chartDimension').getContext('2d');
            const dimLabels = Object.keys(dimensionData).map(l => l.length > 15 ? l.substring(0, 15) + '...' : l);
            chartDimension = new Chart(ctxDimension, {
                type: 'bar',
                data: {
                    labels: dimLabels,
                    datasets: [{
                        label: 'Puntaje',
                        data: Object.values(dimensionData),
                        backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true } }
                }
            });

            // Gráfico Calificación
            const ctxCalificacion = document.getElementById('chartCalificacion').getContext('2d');
            chartCalificacion = new Chart(ctxCalificacion, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(calificacionData),
                    datasets: [{
                        data: Object.values(calificacionData),
                        backgroundColor: Object.keys(calificacionData).map(k => calificacionColors[k] || '#999')
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { size: 10 } } }
                    }
                }
            });
        }

        function getCalificacionBadge(calificacion) {
            const calif = calificacion || 'SIN EVALUAR';
            let badgeClass = 'badge-sin-evaluar';

            if (calif === 'CUMPLE') badgeClass = 'badge-cumple';
            else if (calif === 'NO CUMPLE') badgeClass = 'badge-no-cumple';
            else if (calif.includes('PARCIAL')) badgeClass = 'badge-parcial';
            else if (calif === 'NO APLICA') badgeClass = 'badge-no-aplica';

            return `<span class="badge ${badgeClass}">${calif}</span>`;
        }

        function renderAccordion(data) {
            // Agrupar por PHVA y luego por Estándar
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

            // Ordenar por ciclo PHVA
            const sortedCiclos = Object.keys(grouped).sort((a, b) => {
                const indexA = phvaOrder.indexOf(a);
                const indexB = phvaOrder.indexOf(b);
                return (indexA === -1 ? 999 : indexA) - (indexB === -1 ? 999 : indexB);
            });

            sortedCiclos.forEach(ciclo => {
                const estandares = grouped[ciclo];
                const cicloClass = 'phva-' + ciclo.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

                // Calcular totales del ciclo
                let cicloCalificado = 0;
                let cicloPosible = 0;
                let cicloItems = 0;

                Object.values(estandares).forEach(items => {
                    items.forEach(item => {
                        cicloCalificado += parseFloat(item.valor || 0);
                        cicloPosible += parseFloat(item.puntaje_cuantitativo || 0);
                        cicloItems++;
                    });
                });

                html += `
                <div class="accordion-item ${cicloClass}">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#phva-${phvaIndex}">
                            <i class="fas fa-circle me-2"></i> ${ciclo}
                            <div class="phva-summary">
                                <span>${cicloItems} ítems</span>
                                <span>${cicloCalificado.toFixed(1)} / ${cicloPosible.toFixed(1)} pts</span>
                            </div>
                        </button>
                    </h2>
                    <div id="phva-${phvaIndex}" class="accordion-collapse collapse" data-bs-parent="#accordionPHVA">
                        <div class="accordion-body">
                            <div class="accordion accordion-estandar" id="accordionEstandar-${phvaIndex}">
                `;

                let estandarIndex = 0;
                Object.keys(estandares).sort().forEach(estandar => {
                    const items = estandares[estandar];

                    // Calcular totales del estándar
                    let estCalificado = 0;
                    let estPosible = 0;

                    items.forEach(item => {
                        estCalificado += parseFloat(item.valor || 0);
                        estPosible += parseFloat(item.puntaje_cuantitativo || 0);
                    });

                    html += `
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#estandar-${phvaIndex}-${estandarIndex}">
                                ${estandar}
                                <span class="estandar-summary">(${items.length} ítems | ${estCalificado.toFixed(1)}/${estPosible.toFixed(1)} pts)</span>
                            </button>
                        </h2>
                        <div id="estandar-${phvaIndex}-${estandarIndex}" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="table-accordion-wrapper">
                                    <table class="table table-accordion table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Ítem</th>
                                                <th>Numeral</th>
                                                <th>Calificación</th>
                                                <th>Puntos</th>
                                                <th>Máx.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                    `;

                    items.forEach(item => {
                        html += `
                            <tr>
                                <td>${item.item_del_estandar || '-'}</td>
                                <td>${item.numeral || '-'}</td>
                                <td>${getCalificacionBadge(item.evaluacion_inicial)}</td>
                                <td class="text-end">${parseFloat(item.valor || 0).toFixed(1)}</td>
                                <td class="text-end">${parseFloat(item.puntaje_cuantitativo || 0).toFixed(1)}</td>
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
                    estandarIndex++;
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

            // Mostrar/ocultar mensaje de sin resultados
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
                const filterDimension = $('#filterDimension').val();
                const filterCalificacion = $('#filterCalificacion').val();
                const filterPHVA = $('#filterPHVA').val();

                // Filtrar datos
                const filteredData = originalData.filter(item => {
                    if (filterDimension && item.estandar !== filterDimension) return false;

                    const itemCalif = item.evaluacion_inicial || 'SIN EVALUAR';
                    if (filterCalificacion && itemCalif !== filterCalificacion) return false;

                    if (filterPHVA && item.ciclo !== filterPHVA) return false;

                    return true;
                });

                // Recalcular totales
                let totalCalificado = 0;
                let totalPosible = 0;
                const phvaCounts = {};
                const dimensionCounts = {};
                const calificacionCounts = {};

                filteredData.forEach(item => {
                    totalCalificado += parseFloat(item.valor || 0);
                    totalPosible += parseFloat(item.puntaje_cuantitativo || 0);

                    // PHVA
                    if (item.ciclo) {
                        phvaCounts[item.ciclo] = (phvaCounts[item.ciclo] || 0) + 1;
                    }
                    // Dimensión
                    if (item.estandar) {
                        dimensionCounts[item.estandar] = (dimensionCounts[item.estandar] || 0) + parseFloat(item.valor || 0);
                    }
                    // Calificación
                    const calif = item.evaluacion_inicial || 'SIN EVALUAR';
                    calificacionCounts[calif] = (calificacionCounts[calif] || 0) + 1;
                });

                // Actualizar puntaje mostrado
                const hasFilters = filterDimension || filterCalificacion || filterPHVA;
                if (hasFilters) {
                    $('#puntajeFiltrado').show();
                    $('#puntajeFiltradoValor').text(`${totalCalificado.toFixed(1)} / ${totalPosible.toFixed(1)}`);
                } else {
                    $('#puntajeFiltrado').hide();
                }

                // Actualizar gráficos
                updateCharts(phvaCounts, dimensionCounts, calificacionCounts);

                // Renderizar acordeón
                renderAccordion(filteredData);

                $('#loadingOverlay').removeClass('show');
            }, 100);
        }

        function updateCharts(phvaCounts, dimensionCounts, calificacionCounts) {
            // PHVA
            chartPhva.data.labels = Object.keys(phvaCounts);
            chartPhva.data.datasets[0].data = Object.values(phvaCounts);
            chartPhva.data.datasets[0].backgroundColor = Object.keys(phvaCounts).map(k => phvaColors[k] || '#999');
            chartPhva.update();

            // Dimensión
            const dimLabels = Object.keys(dimensionCounts).map(l => l.length > 15 ? l.substring(0, 15) + '...' : l);
            chartDimension.data.labels = dimLabels;
            chartDimension.data.datasets[0].data = Object.values(dimensionCounts);
            chartDimension.update();

            // Calificación
            chartCalificacion.data.labels = Object.keys(calificacionCounts);
            chartCalificacion.data.datasets[0].data = Object.values(calificacionCounts);
            chartCalificacion.data.datasets[0].backgroundColor = Object.keys(calificacionCounts).map(k => calificacionColors[k] || '#999');
            chartCalificacion.update();
        }

        function exportToExcel() {
            // Preparar datos para exportar
            const exportData = originalData.map(item => ({
                'Ciclo PHVA': item.ciclo || '',
                'Estándar': item.estandar || '',
                'Ítem del Estándar': item.item_del_estandar || '',
                'Numeral': item.numeral || '',
                'Calificación': item.evaluacion_inicial || 'SIN EVALUAR',
                'Puntos Obtenidos': parseFloat(item.valor || 0),
                'Puntos Máximos': parseFloat(item.puntaje_cuantitativo || 0)
            }));

            // Crear workbook
            const ws = XLSX.utils.json_to_sheet(exportData);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Estándares Mínimos');

            // Ajustar anchos de columna
            ws['!cols'] = [
                { wch: 12 },  // Ciclo PHVA
                { wch: 30 },  // Estándar
                { wch: 50 },  // Ítem
                { wch: 10 },  // Numeral
                { wch: 20 },  // Calificación
                { wch: 15 },  // Puntos Obtenidos
                { wch: 15 }   // Puntos Máximos
            ];

            // Descargar
            const fileName = `Estandares_Minimos_SST_${clientName.replace(/[^a-zA-Z0-9]/g, '_')}.xlsx`;
            XLSX.writeFile(wb, fileName);
        }
    </script>
</body>

</html>
