<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Cronogramas de Capacitación</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Columnas de texto largo permiten wrap */
        .styled-table tfoot th {
            white-space: nowrap;
        }

        /* Estilos personalizados adicionales */
        body {
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            margin-top: 30px;
            max-width: 100%;
        }

        h2 {
            color: #333;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }

        .table-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        /* === UX: Tabla estilizada con gradiente === */
        #cronogramasTable thead th {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
            color: #fff !important;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 8px;
            border-bottom: 2px solid #1a3a8a;
            white-space: nowrap;
            text-align: center;
        }

        #cronogramasTable tbody td {
            text-align: center;
            font-size: 0.85rem;
            vertical-align: middle;
            padding: 6px 8px;
        }

        #cronogramasTable tbody tr:hover { background-color: #eef2ff !important; }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
            border-radius: 5px;
        }

        .empty-message {
            color: #333;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            padding: 20px;
        }

        /* Estilos para tarjetas clickeables */
        .card-clickable {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .card-clickable:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card-clickable.active {
            border: 3px solid #ffeb3b !important;
            box-shadow: 0 0 25px rgba(255, 235, 59, 0.8), 0 0 10px rgba(255, 255, 255, 0.5) !important;
            transform: scale(1.08) !important;
            position: relative;
        }

        .card-clickable.active::after {
            content: '✓';
            position: absolute;
            top: 5px;
            right: 5px;
            background: #ffeb3b;
            color: #000;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }

        .card-year {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            min-height: 80px;
        }

        .card-month {
            min-height: 70px;
        }

        .card-status {
            min-height: 90px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #4e73df;
            border-left: 4px solid #4e73df;
            padding-left: 10px;
            margin: 20px 0 15px 0;
        }

        /* === UX: Estado Badges === */
        .estado-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .estado-programada   { background: #cce5ff; color: #004085; }
        .estado-ejecutada    { background: #d4edda; color: #155724; }
        .estado-cancelada    { background: #f8d7da; color: #721c24; }
        .estado-reprogramada { background: #fff3cd; color: #856404; }

        /* === UX: Mini Progress Bar === */
        .mini-progress { display: flex; align-items: center; gap: 6px; min-width: 100px; }
        .mini-progress-bar { flex: 1; height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden; }
        .mini-progress-fill { height: 100%; border-radius: 4px; transition: width .3s; }
        .mini-progress-text { font-size: 0.78rem; font-weight: 600; white-space: nowrap; }

        /* === UX: Texto truncado expandible === */
        .col-truncate { max-width: 250px !important; }
        .cell-truncate {
            max-height: 60px;
            overflow: hidden;
            transition: max-height .3s ease;
            position: relative;
        }
        .cell-truncate.expanded { max-height: none; }
        .btn-expand {
            display: inline-block;
            font-size: 0.72rem;
            color: #4e73df;
            cursor: pointer;
            margin-top: 2px;
            font-weight: 600;
        }
        .btn-expand:hover { text-decoration: underline; }

        /* === UX: Accordion filtros === */
        .filter-toggle-btn {
            background: none;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 4px 14px;
            font-size: 0.82rem;
            color: #6c757d;
            cursor: pointer;
            transition: all .2s;
        }
        .filter-toggle-btn:hover { background: #f8f9fa; color: #4e73df; }
        .filter-toggle-btn i { transition: transform .2s; }
        .filter-toggle-btn.collapsed i { transform: rotate(-90deg); }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container-fluid">
            <!-- Logos -->
            <div class="d-flex align-items-center">
                <a href="https://dashboard.cycloidtalent.com/login" class="me-3">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" height="60">
                </a>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst" class="me-3">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" height="60">
                </a>
                <a href="https://cycloidtalent.com/">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" height="60">
                </a>
            </div>

            <!-- Botones Dashboard y Añadir Registro -->
            <div class="ms-auto d-flex">
                <div class="text-center me-3">
                    <h6 class="mb-1" style="font-size: 16px;">Ir a Dashboard</h6>
                    <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm">Ir a DashBoard</a>
                </div>
                <div class="text-center">
                    <h6 class="mb-1" style="font-size: 16px;">Añadir Registro</h6>
                    <a href="<?= base_url('/addCronograma') ?>" class="btn btn-success btn-sm" target="_blank">Añadir Registro</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Espaciado para el navbar fijo -->
    <div style="height: 120px;"></div>

    <div class="container">
        <h2>Listado de Cronogramas de Capacitación</h2>

        <?php if (session()->getFlashdata('msg')) : ?>
            <div class="alert alert-success text-center"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>

        <!-- Mensaje informativo -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i>
            <strong>Filtros Dinámicos:</strong> Las tarjetas de año, estado y mes son interactivas.
            Haz clic sobre ellas para filtrar la tabla instantáneamente. Puedes combinar múltiples filtros.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- Sección de Filtros por Año -->
        <div class="d-flex justify-content-between align-items-center">
            <div class="section-title mb-0">
                <i class="fas fa-calendar-alt"></i> Filtrar por Año
            </div>
            <button type="button" id="btnClearCardFilters" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-times"></i> Limpiar Filtros de Tarjetas
            </button>
        </div>
        <div class="row mb-4 mt-2" id="yearCards">
            <!-- Se generarán dinámicamente con JavaScript -->
        </div>

        <!-- Accordion: Estado + Mes -->
        <div class="d-flex justify-content-end mb-2">
            <button class="filter-toggle-btn" data-bs-toggle="collapse" data-bs-target="#cardFiltersPanel" aria-expanded="true">
                <i class="fas fa-chevron-down"></i> Estado y Mes
            </button>
        </div>
        <div class="collapse show" id="cardFiltersPanel">

        <!-- Tarjetas de Estados (clickeables) -->
        <div class="section-title">
            <i class="fas fa-tasks"></i> Filtrar por Estado
        </div>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary card-clickable card-status" data-status="PROGRAMADA">
                    <div class="card-body text-center">
                        <h5 class="card-title">Programada</h5>
                        <p class="card-text display-6" id="countProgramada">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success card-clickable card-status" data-status="EJECUTADA">
                    <div class="card-body text-center">
                        <h5 class="card-title">Ejecutada</h5>
                        <p class="card-text display-6" id="countEjecutada">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger card-clickable card-status" data-status="CANCELADA POR EL CLIENTE">
                    <div class="card-body text-center">
                        <h5 class="card-title">Cancelada</h5>
                        <p class="card-text display-6" id="countCancelada">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning card-clickable card-status" data-status="REPROGRAMADA">
                    <div class="card-body text-center">
                        <h5 class="card-title">Reprogramada</h5>
                        <p class="card-text display-6" id="countReprogramada">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas mensuales (clickeables) -->
        <div class="section-title">
            <i class="fas fa-calendar-week"></i> Filtrar por Mes
        </div>
        <div class="row mb-4">
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info card-clickable card-month" data-month="1">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Enero</h6>
                        <p class="card-text text-center" id="countEnero">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info card-clickable card-month" data-month="2">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Febrero</h6>
                        <p class="card-text text-center" id="countFebrero">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info card-clickable card-month" data-month="3">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Marzo</h6>
                        <p class="card-text text-center" id="countMarzo">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info card-clickable card-month" data-month="4">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Abril</h6>
                        <p class="card-text text-center" id="countAbril">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info card-clickable card-month" data-month="5">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Mayo</h6>
                        <p class="card-text text-center" id="countMayo">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info card-clickable card-month" data-month="6">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Junio</h6>
                        <p class="card-text text-center" id="countJunio">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info card-clickable card-month" data-month="7">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Julio</h6>
                        <p class="card-text text-center" id="countJulio">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info card-clickable card-month" data-month="8">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Agosto</h6>
                        <p class="card-text text-center" id="countAgosto">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info card-clickable card-month" data-month="9">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Sept.</h6>
                        <p class="card-text text-center" id="countSeptiembre">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info card-clickable card-month" data-month="10">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Oct.</h6>
                        <p class="card-text text-center" id="countOctubre">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info card-clickable card-month" data-month="11">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Nov.</h6>
                        <p class="card-text text-center" id="countNoviembre">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info card-clickable card-month" data-month="12">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Dic.</h6>
                        <p class="card-text text-center" id="countDiciembre">0</p>
                    </div>
                </div>
            </div>
        </div>

        </div><!-- /cardFiltersPanel -->

        <div class="table-container">
            <!-- Botones de DataTables -->
            <div class="d-flex justify-content-between mb-3">
                <button id="clearState" class="btn btn-danger btn-sm">Restablecer Filtros</button>
                <div id="buttonsContainer"></div>
            </div>

            <table id="cronogramasTable" class="styled-table table table-hover table-bordered nowrap" style="width:100%">
                <thead>
                    <tr>
                        <!-- Se muestran las columnas definidas -->
                        <th>Capacitación</th>
                        <th>Fecha Programada</th>
                        <th>Fecha de Realización</th>
                        <th>Estado</th>
                        <th>Perfil de Asistentes</th>
                        <th>Nombre del Capacitador</th>
                        <th>Horas de Duración</th>
                        <th>Indicador de Realización</th>
                        <th>Número de Asistentes</th>
                        <th>Número Total de Personas Programadas</th>
                        <th>Porcentaje de Cobertura</th>
                        <th>Número de Personas Evaluadas</th>
                        <th>Promedio de Calificaciones</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tfoot class="table-light">
                    <tr class="filters">
                        <!-- Filtros para cada columna -->
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Capacitación">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Fecha Programada">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Fecha de Realización">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Estado">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Perfil de Asistentes">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Nombre del Capacitador">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Horas de Duración">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Indicador de Realización">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Número de Asistentes">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Número Total de Personas Programadas">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Porcentaje de Cobertura">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Número de Personas Evaluadas">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Promedio de Calificaciones">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Observaciones">
                                <option value="">Todos</option>
                            </select>
                        </th>
                    </tr>
                </tfoot>

                <tbody>
                    <?php if (!empty($cronogramas) && is_array($cronogramas)): ?>
                        <?php foreach ($cronogramas as $cronograma): ?>
                            <tr>
                                <td class="col-truncate"><div class="cell-truncate"><?= esc($cronograma['nombre_capacitacion']); ?></div></td>
                                <td><?= esc($cronograma['fecha_programada']); ?></td>
                                <td><?= esc($cronograma['fecha_de_realizacion']); ?></td>
                                <td><?php
                                    $est = esc($cronograma['estado']);
                                    $cls = 'estado-programada';
                                    if ($est === 'EJECUTADA') $cls = 'estado-ejecutada';
                                    elseif ($est === 'CANCELADA POR EL CLIENTE') $cls = 'estado-cancelada';
                                    elseif ($est === 'REPROGRAMADA') $cls = 'estado-reprogramada';
                                    echo '<span class="estado-badge ' . $cls . '">' . $est . '</span>';
                                ?></td>
                                <td class="col-truncate"><div class="cell-truncate"><?= esc($cronograma['perfil_de_asistentes']); ?></div></td>
                                <td><?= esc($cronograma['nombre_del_capacitador']); ?></td>
                                <td><?= esc($cronograma['horas_de_duracion_de_la_capacitacion']); ?></td>
                                <td><?= esc($cronograma['indicador_de_realizacion_de_la_capacitacion']); ?></td>
                                <td><?= esc($cronograma['numero_de_asistentes_a_capacitacion']); ?></td>
                                <td><?= esc($cronograma['numero_total_de_personas_programadas']); ?></td>
                                <td><?php
                                    $pct = floatval($cronograma['porcentaje_cobertura']);
                                    $color = '#e74a3b';
                                    if ($pct >= 100) $color = '#1cc88a';
                                    elseif ($pct >= 50) $color = '#4e73df';
                                    elseif ($pct > 0) $color = '#f6c23e';
                                    $w = max($pct, 2);
                                    echo '<div class="mini-progress"><div class="mini-progress-bar"><div class="mini-progress-fill" style="width:' . $w . '%;background:' . $color . '"></div></div><span class="mini-progress-text">' . $pct . '%</span></div>';
                                ?></td>
                                <td><?= esc($cronograma['numero_de_personas_evaluadas']); ?></td>
                                <td><?= esc($cronograma['promedio_de_calificaciones']); ?></td>
                                <td class="col-truncate"><div class="cell-truncate"><?= esc($cronograma['observaciones']); ?></div></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="14" class="empty-message">No hay cronogramas de capacitación registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 border-top mt-4">
        <div class="container text-center">
            <p class="fw-bold mb-1">Cycloid Talent SAS</p>
            <p class="mb-1">Todos los derechos reservados © 2024</p>
            <p class="mb-1">NIT: 901.653.912</p>
            <p class="mb-3">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank">https://cycloidtalent.com/</a>
            </p>
            <p><strong>Nuestras Redes Sociales:</strong></p>
            <div class="social-icons d-flex justify-content-center gap-3">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
                </a>
            </div>
        </div>
    </footer>

    <!-- Scripts al final del body para mejor rendimiento -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle (Incluye Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function () {
            // Variables globales para filtros activos
            var activeYear = null;
            var activeMonth = null;
            var activeStatus = null;

            // Helper: extraer texto plano de HTML
            function stripHtml(html) {
                var tmp = document.createElement('DIV');
                tmp.innerHTML = html;
                return (tmp.textContent || tmp.innerText || '').trim();
            }

            // Inicializar DataTables con Buttons, filtros en el <tfoot> y configuración de columnas
            var table = $('#cronogramasTable').DataTable({
                stateSave: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                responsive: true,
                autoWidth: false,
                dom: 'Bfltip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: ':visible',
                            format: {
                                body: function(data) {
                                    return stripHtml(data);
                                }
                            }
                        }
                    },
                    {
                        extend: 'colvis',
                        text: 'Seleccionar Columnas',
                        className: 'btn btn-secondary btn-sm'
                    }
                ],
                initComplete: function () {
                    var api = this.api();
                    // Para cada columna, crear un filtro en el <tfoot>
                    api.columns().every(function () {
                        var column = this;
                        var headerIndex = column.index();
                        var filterElement = $('tfoot tr.filters th').eq(headerIndex).find('.filter-select');
                        if (filterElement.length && !filterElement.prop('disabled')) {
                            column.data().unique().sort().each(function (d) {
                                if (d) {
                                    var clean = stripHtml(d);
                                    if (clean && filterElement.find('option[value="' + clean + '"]').length === 0) {
                                        filterElement.append('<option value="' + clean + '">' + clean + '</option>');
                                    }
                                }
                            });
                            var search = column.search();
                            if (search) {
                                var cleanedSearch = search.replace(/[\^\$(){}.+*?\\|]/g, '');
                                filterElement.val(cleanedSearch);
                            }
                        }
                    });
                }
            });

            // Colocar los botones de DataTables en el contenedor específico
            table.buttons().container().appendTo('#buttonsContainer');

            // Generar tarjetas de años dinámicamente
            function generateYearCards() {
                if (!table) return;

                var yearCounts = {};

                // Contar cronogramas por año basado en fecha_programada (columna 1)
                table.rows({search: 'applied'}).every(function() {
                    var data = this.data();
                    var fechaProgramada = data[1]; // Columna 1: Fecha Programada
                    if (fechaProgramada) {
                        var parts = fechaProgramada.split("-");
                        if (parts.length >= 1) {
                            var year = parts[0];
                            yearCounts[year] = (yearCounts[year] || 0) + 1;
                        }
                    }
                });

                var yearArray = Object.keys(yearCounts).sort().reverse();
                var yearCardsHtml = '';

                yearArray.forEach(function(year) {
                    var count = yearCounts[year];
                    yearCardsHtml += `
                        <div class="col-6 col-md-2">
                            <div class="card text-white card-year card-clickable" data-year="${year}">
                                <div class="card-body text-center p-3">
                                    <h4 class="card-title mb-1">${year}</h4>
                                    <p class="mb-0" style="font-size: 1.5rem; font-weight: bold;">${count}</p>
                                    <small style="font-size: 0.75rem;">capacitaciones</small>
                                </div>
                            </div>
                        </div>
                    `;
                });

                $('#yearCards').html(yearCardsHtml);
            }

            // Actualizar contadores de estados
            function updateStatusCounts() {
                if (!table) return;

                var countProgramada = 0;
                var countEjecutada = 0;
                var countCancelada = 0;
                var countReprogramada = 0;

                table.rows({search: 'applied'}).every(function() {
                    var data = this.data();
                    var estado = stripHtml(data[3]); // Columna 3: Estado (stripHtml para badges)
                    if (estado === 'PROGRAMADA') {
                        countProgramada++;
                    } else if (estado === 'EJECUTADA') {
                        countEjecutada++;
                    } else if (estado === 'CANCELADA POR EL CLIENTE') {
                        countCancelada++;
                    } else if (estado === 'REPROGRAMADA') {
                        countReprogramada++;
                    }
                });

                $('#countProgramada').text(countProgramada);
                $('#countEjecutada').text(countEjecutada);
                $('#countCancelada').text(countCancelada);
                $('#countReprogramada').text(countReprogramada);
            }

            // Actualizar contadores de meses
            function updateMonthlyCounts() {
                if (!table) return;

                var monthlyCounts = {
                    1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0,
                    7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0
                };

                table.rows({search: 'applied'}).every(function() {
                    var data = this.data();
                    var fechaProgramada = data[1]; // Columna 1: Fecha Programada
                    if (fechaProgramada) {
                        var parts = fechaProgramada.split("-");
                        if (parts.length >= 2) {
                            var month = parseInt(parts[1], 10);
                            if (month >= 1 && month <= 12) {
                                monthlyCounts[month]++;
                            }
                        }
                    }
                });

                $('#countEnero').text(monthlyCounts[1]);
                $('#countFebrero').text(monthlyCounts[2]);
                $('#countMarzo').text(monthlyCounts[3]);
                $('#countAbril').text(monthlyCounts[4]);
                $('#countMayo').text(monthlyCounts[5]);
                $('#countJunio').text(monthlyCounts[6]);
                $('#countJulio').text(monthlyCounts[7]);
                $('#countAgosto').text(monthlyCounts[8]);
                $('#countSeptiembre').text(monthlyCounts[9]);
                $('#countOctubre').text(monthlyCounts[10]);
                $('#countNoviembre').text(monthlyCounts[11]);
                $('#countDiciembre').text(monthlyCounts[12]);
            }

            // Función para aplicar filtros combinados
            function applyFilters() {
                if (!table) return;

                $.fn.dataTable.ext.search.pop(); // Limpiar filtros personalizados previos

                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        var fechaProgramada = data[1] || ''; // Columna 1: Fecha Programada
                        var estado = stripHtml(data[3] || ''); // Columna 3: Estado (stripHtml para badges)

                        // Filtro por año
                        if (activeYear) {
                            if (!fechaProgramada.startsWith(activeYear)) {
                                return false;
                            }
                        }

                        // Filtro por mes
                        if (activeMonth) {
                            if (fechaProgramada) {
                                var parts = fechaProgramada.split("-");
                                if (parts.length >= 2) {
                                    var month = parseInt(parts[1], 10);
                                    if (month !== parseInt(activeMonth)) {
                                        return false;
                                    }
                                } else {
                                    return false;
                                }
                            } else {
                                return false;
                            }
                        }

                        // Filtro por estado
                        if (activeStatus) {
                            if (estado.trim() !== activeStatus) {
                                return false;
                            }
                        }

                        return true;
                    }
                );

                table.draw();
                generateYearCards();
                updateStatusCounts();
                updateMonthlyCounts();
            }

            // Click en tarjetas de año
            $(document).on('click', '.card-year', function() {
                var year = $(this).data('year');

                if ($(this).hasClass('active')) {
                    $(this).removeClass('active');
                    activeYear = null;
                } else {
                    $('.card-year').removeClass('active');
                    $(this).addClass('active');
                    activeYear = year;
                }

                applyFilters();
            });

            // Click en tarjetas de mes
            $(document).on('click', '.card-month', function() {
                var month = $(this).data('month');

                if ($(this).hasClass('active')) {
                    $(this).removeClass('active');
                    activeMonth = null;
                } else {
                    $('.card-month').removeClass('active');
                    $(this).addClass('active');
                    activeMonth = month;
                }

                applyFilters();
            });

            // Click en tarjetas de estado
            $(document).on('click', '.card-status', function() {
                var status = $(this).data('status');

                if ($(this).hasClass('active')) {
                    $(this).removeClass('active');
                    activeStatus = null;
                } else {
                    $('.card-status').removeClass('active');
                    $(this).addClass('active');
                    activeStatus = status;
                }

                applyFilters();
            });

            // Botón para limpiar todos los filtros de tarjetas
            $('#btnClearCardFilters').on('click', function() {
                activeYear = null;
                activeMonth = null;
                activeStatus = null;

                $('.card-year').removeClass('active');
                $('.card-month').removeClass('active');
                $('.card-status').removeClass('active');

                $.fn.dataTable.ext.search.pop();

                if (table) {
                    table.draw();
                    generateYearCards();
                    updateStatusCounts();
                    updateMonthlyCounts();
                }
            });

            // Actualizar contadores cuando la tabla se redibuja
            table.on('draw', function() {
                updateStatusCounts();
                updateMonthlyCounts();
                generateYearCards();
            });

            // Inicializar contadores y tarjetas de año
            updateStatusCounts();
            updateMonthlyCounts();
            generateYearCards();

            // === UX: Texto truncado con ver más / ver menos ===
            function initTruncateButtons() {
                $('#cronogramasTable .cell-truncate').each(function() {
                    var $cell = $(this);
                    $cell.removeClass('expanded');
                    $cell.next('.btn-expand').remove();
                    if (this.scrollHeight > 62) {
                        $cell.after('<span class="btn-expand">ver más</span>');
                    }
                });
            }
            $(document).on('click', '.btn-expand', function() {
                var $btn = $(this);
                var $cell = $btn.prev('.cell-truncate');
                if ($cell.hasClass('expanded')) {
                    $cell.removeClass('expanded');
                    $btn.text('ver más');
                } else {
                    $cell.addClass('expanded');
                    $btn.text('ver menos');
                }
            });
            table.on('draw.dt', function() {
                setTimeout(initTruncateButtons, 50);
            });
            initTruncateButtons();

            // === UX: Accordion toggle ===
            $('#cardFiltersPanel').on('shown.bs.collapse', function() {
                $('.filter-toggle-btn').removeClass('collapsed');
            }).on('hidden.bs.collapse', function() {
                $('.filter-toggle-btn').addClass('collapsed');
            });

            // Evento para los filtros del <tfoot>
            $('tfoot .filter-select').on('change', function () {
                var columnIndex = $(this).closest('th').index();
                var value = $(this).val();
                table.column(columnIndex).search(value ? '^' + value + '$' : '', true, false).draw();
            });

            // Botón para restablecer el estado y filtros
            $('#clearState').on('click', function () {
                var storageKey = 'DataTables_' + table.table().node().id + '_' + window.location.pathname;
                localStorage.removeItem(storageKey);
                table.state.clear();
                $('tfoot .filter-select').each(function () {
                    $(this).val('');
                });
                table.columns().search('').draw();
            });
        });
    </script>
</body>

</html>
