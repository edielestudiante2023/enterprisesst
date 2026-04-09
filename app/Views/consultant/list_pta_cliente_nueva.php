<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Plan de Trabajo Anual</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <style>
        body {
            padding: 20px;
        }

        .dataTables_wrapper .dataTables_filter {
            float: right;
            text-align: right;
        }

        .dt-button-collection {
            padding: 8px;
            max-height: 400px;
            overflow-y: auto;
        }
        .dt-button-collection .dt-button {
            display: block !important;
            padding: 6px 14px !important;
            margin: 2px 0 !important;
            width: 100%;
            text-align: left;
            border-radius: 4px;
            font-size: 13px;
        }
        .dt-button-collection .dt-button.active {
            background: #4e73df !important;
            color: #fff !important;
        }

        td.editable {
            cursor: pointer;
        }

        .dt-buttons {
            margin-bottom: 15px;
        }

        .dt-buttons .btn {
            margin-right: 5px;
        }

        .dt-button-collection {
            padding: 8px;
        }

        .dt-button {
            display: inline-block !important;
            padding: 8px 16px !important;
            margin: 5px !important;
        }

        .btn-warning {
            color: #000;
            background-color: #ffc107;
            border-color: #ffc107;
        }

        .btn-warning:hover {
            color: #000;
            background-color: #ffca2c;
            border-color: #ffc720;
        }

        /* Estilos mejorados para los filtros */
        .filter-card {
            background: #fff;
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .filter-section {
            border-left: 3px solid #4e73df;
            padding-left: 15px;
            margin-bottom: 1rem;
        }

        .filter-section h6 {
            color: #4e73df;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-label {
            font-weight: 500;
            color: #5a5c69;
            margin-bottom: 0.3rem;
        }

        .form-label i {
            margin-right: 0.5rem;
            color: #858796;
        }

        .form-control, .form-select {
            border: 1px solid #d1d3e2;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        }

        .required-field {
            border-left: 3px solid #e74a3b;
        }

        .btn-group-filters {
            background: #f8f9fc;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e3e6f0;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            border-radius: 6px;
            font-weight: 500;
            padding: 0.6rem 1.2rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            border: none;
            border-radius: 6px;
            font-weight: 500;
            padding: 0.6rem 1.2rem;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
            border: none;
            border-radius: 6px;
            font-weight: 500;
            padding: 0.6rem 1.2rem;
        }

        .date-range-group {
            background: #f1f3ff;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #d1d9ff;
        }

        .quick-filters {
            background: #fff8e1;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #ffe082;
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

        .card-programa {
            min-height: 70px;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .card-programa.active {
            transform: scale(1.05);
            box-shadow: 0 0 0 3px rgba(255,255,255,0.8), 0 4px 12px rgba(0,0,0,0.3);
        }
        .bg-purple { background-color: #6f42c1 !important; }
        .bg-teal { background-color: #20c997 !important; }
        .bg-orange { background-color: #fd7e14 !important; color: #fff !important; }
        .bg-indigo { background-color: #6610f2 !important; }
        .bg-pink { background-color: #d63384 !important; }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #4e73df;
            border-left: 4px solid #4e73df;
            padding-left: 10px;
            margin: 20px 0 15px 0;
        }

        /* ============ BOTONES DE GESTIÓN RÁPIDA ============ */
        .btn-month {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid #6c757d;
            background-color: #fff;
            color: #495057;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            padding: 0;
        }

        .btn-month:hover {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
            transform: scale(1.1);
        }

        .btn-month.has-date {
            background-color: #28a745;
            color: #fff;
            border-color: #28a745;
        }

        .btn-month:active {
            transform: scale(0.95);
        }

        .month-buttons {
            max-width: 200px;
        }

        /* ============ BOTÓN ADICIONAR ACTIVIDAD ============ */
        .btn-add-actividad {
            background: linear-gradient(135deg, #ff6a00 0%, #ee0979 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.75rem 1.5rem;
            box-shadow: 0 4px 15px rgba(238, 9, 121, 0.4);
            animation: pulse-btn 2s infinite;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-add-actividad:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 20px rgba(238, 9, 121, 0.6);
            color: #fff;
        }

        .btn-add-actividad:active {
            transform: scale(0.98);
        }

        .btn-add-actividad i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }

        @keyframes pulse-btn {
            0% { box-shadow: 0 4px 15px rgba(238, 9, 121, 0.4); }
            50% { box-shadow: 0 4px 25px rgba(238, 9, 121, 0.7); }
            100% { box-shadow: 0 4px 15px rgba(238, 9, 121, 0.4); }
        }

        /* ============ TEXTO TRUNCADO EXPANDIBLE ============ */
        .cell-truncate {
            max-height: 60px;
            overflow: hidden;
            position: relative;
            transition: max-height 0.3s ease;
        }
        .cell-truncate.expanded {
            max-height: 2000px;
        }
        .btn-expand {
            display: inline-block;
            font-size: 11px;
            color: #4e73df;
            cursor: pointer;
            font-weight: 600;
            margin-top: 2px;
            user-select: none;
        }
        .btn-expand:hover {
            color: #224abe;
            text-decoration: underline;
        }

        /* ============ BADGES DE ESTADO ============ */
        .estado-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        .estado-abierta { background: #fff3e0; color: #e65100; border: 1px solid #ffcc80; }
        .estado-cerrada { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .estado-gestionando { background: #e3f2fd; color: #1565c0; border: 1px solid #90caf9; }
        .estado-cerrada-sin { background: #fce4ec; color: #c62828; border: 1px solid #ef9a9a; }

        /* ============ MINI PROGRESS BAR ============ */
        .mini-progress {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 100px;
        }
        .mini-progress-bar {
            flex: 1;
            height: 14px;
            background: #dee2e6;
            border-radius: 7px;
            overflow: hidden;
            min-width: 60px;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        }
        .mini-progress-fill {
            height: 100%;
            border-radius: 7px;
            transition: width 0.3s ease;
            min-width: 2px;
        }
        .mini-progress-text {
            font-size: 13px;
            font-weight: 800;
            min-width: 40px;
            text-align: right;
            color: #333;
        }

        /* ============ CELDAS EDITABLES ============ */
        td.editable {
            cursor: pointer;
        }

        /* ============ BOTONES ACCIONES COMPACTOS ============ */
        .btn-action {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 13px;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-action:hover { transform: scale(1.1); }
        .btn-action-edit { background: #ffc107; color: #000; }
        .btn-action-edit:hover { background: #ffca2c; color: #000; }
        .btn-action-delete { background: #dc3545; color: #fff; }
        .btn-action-delete:hover { background: #e04050; color: #fff; }
        .action-group { display: flex; gap: 4px; justify-content: center; }

        .btn-purple { background: linear-gradient(135deg, #7c3aed, #a855f7); color: #fff; border: none; }
        .btn-purple:hover { background: linear-gradient(135deg, #6d28d9, #9333ea); color: #fff; }
        .text-purple { color: #7c3aed !important; }

        /* ============ FILAS COMPACTAS Y TABLA ESTILIZADA ============ */
        #ptaTable {
            border-collapse: separate;
            border-spacing: 0;
        }
        #ptaTable thead th {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 10px 8px;
            white-space: nowrap;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        #ptaTable thead th:first-child { border-radius: 8px 0 0 0; }
        #ptaTable thead th:last-child { border-radius: 0 8px 0 0; }
        #ptaTable tbody td {
            vertical-align: middle;
            padding: 8px 8px;
            font-size: 13px;
            border-bottom: 1px solid #e9ecef;
        }
        #ptaTable tbody tr:hover td {
            background-color: #f0f4ff !important;
        }
        #ptaTable tbody tr:nth-child(even) td {
            background-color: #f8f9fc;
        }

        /* ============ ACORDEON DE FILTROS ============ */
        .filter-toggle-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .filter-toggle-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .filter-toggle-btn .fa-chevron-down {
            transition: transform 0.3s ease;
        }
        .filter-toggle-btn.collapsed .fa-chevron-down {
            transform: rotate(-90deg);
        }
        #cardFiltersPanel {
            transition: all 0.35s ease;
        }

        /* ============ TOAST NOTIFICATIONS ============ */
        .toast-container { z-index: 9999; }
        .toast { min-width: 320px; box-shadow: 0 4px 12px rgba(0,0,0,.15); margin-bottom: 8px; }
    </style>
</head>

<body>
    <?php
        $anioSeleccionado = $anioSeleccionado ?? $filters['anio'] ?? (string) date('Y');
    ?>
    <!-- Toast Stack -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastStack"></div>

    <div class="container-fluid">
        <!-- Enlaces de navegación -->
        <div class="d-flex gap-2 mb-3">
            <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm">Ir a DashBoard</a>
            <?php if (!empty($filters['cliente'])): ?>
            <button type="button" id="btnEliminarAbiertas" class="btn btn-danger btn-sm"
                    title="Elimina TODAS las actividades en estado ABIERTA de este cliente. Requiere resolver 3 operaciones matemáticas para confirmar.">
                <i class="fas fa-eraser"></i> Eliminar Abiertas
            </button>
            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#regenerarPlanModal"
                    title="Agrega actividades faltantes desde la plantilla CSV sin tocar las existentes.">
                <i class="fas fa-redo"></i> Regenerar Plan
            </button>
            <button type="button" class="btn btn-purple btn-sm" data-bs-toggle="modal" data-bs-target="#crearActividadIAModal"
                    title="Crea una actividad nueva: busque en el inventario o describa lo que necesita y la IA propondrá opciones.">
                <i class="fas fa-robot"></i> Crear con IA
            </button>
            <?php endif; ?>
        </div>

        <!-- Mensaje informativo -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i>
            <strong>Filtros Dinámicos:</strong> Las tarjetas de año, estado y mes son interactivas.
            Haz clic sobre ellas para filtrar la tabla instantáneamente. Puedes combinar múltiples filtros.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- Toggle de filtros por tarjetas -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <button class="filter-toggle-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cardFiltersPanel" aria-expanded="false">
                <i class="fas fa-layer-group me-2"></i>Filtros por Tarjetas (Año / Estado / Mes)
                <i class="fas fa-chevron-down ms-2"></i>
            </button>
            <button type="button" id="btnClearCardFilters" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-times"></i> Limpiar Filtros
            </button>
        </div>
        <div class="collapse" id="cardFiltersPanel">
        <!-- Sección de Filtros por Año -->
        <div class="section-title mt-2">
            <i class="fas fa-calendar-alt"></i> Filtrar por Año
        </div>
        <div class="row mb-4 mt-2" id="yearCards">
            <!-- Se generarán dinámicamente con JavaScript -->
        </div>

        <!-- Tarjetas de Estados (clickeables) -->
        <div class="section-title">
            <i class="fas fa-tasks"></i> Filtrar por Estado
        </div>
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card text-white bg-primary card-clickable card-status" data-status="ABIERTA">
                    <div class="card-body text-center">
                        <h5 class="card-title">Activas</h5>
                        <p class="card-text display-6" id="countActivas">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-danger card-clickable card-status" data-status="CERRADA">
                    <div class="card-body text-center">
                        <h5 class="card-title">Cerradas</h5>
                        <p class="card-text display-6" id="countCerradas">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-warning card-clickable card-status" data-status="GESTIONANDO">
                    <div class="card-body text-center">
                        <h5 class="card-title">Gestionando</h5>
                        <p class="card-text display-6" id="countGestionando">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-dark card-clickable card-status" data-status="CERRADA SIN EJECUCIÓN">
                    <div class="card-body text-center">
                        <h5 class="card-title">Cerradas Sin Ejecución</h5>
                        <p class="card-text display-6" id="countCerradasSinEjecucion">0</p>
                    </div>
                </div>
            </div>
            <!-- Tarjeta para total de actividades -->
            <div class="col-md-3">
                <div class="card text-white bg-secondary card-clickable card-status" data-status="ALL">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total</h5>
                        <p class="card-text display-6" id="countTotal">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Fuente de Actividad (clickeables) -->
        <div class="section-title">
            <i class="fas fa-project-diagram"></i> Filtrar por Fuente de la Actividad
        </div>
        <div class="row mb-4" id="programaCards">
            <!-- Se generarán dinámicamente con JavaScript -->
        </div>

        <!-- Tarjetas mensuales (clickeables) -->
        <div class="section-title">
            <i class="fas fa-calendar-week"></i> Filtrar por Mes
        </div>
        <div class="row mb-4">
            <!-- Cada tarjeta ocupa 1 columna en md y 6 en xs -->
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
        </div><!-- Fin cardFiltersPanel collapse -->

        <h1 class="mb-4">Plan de Trabajo Anual Cliente</h1>
        
        <!-- FORMULARIO DE FILTROS MEJORADO -->
        <div class="filter-card">
            <form id="filterForm" method="get" action="<?= site_url('/pta-cliente-nueva/list') ?>">

                <!-- Filtros en una sola fila -->
                <div class="filter-section">
                    <h6><i class="fas fa-filter"></i> Filtros de Búsqueda</h6>
                    <div class="row mb-3">
                        <!-- Cliente (Campo requerido) -->
                        <div class="col-lg-3">
                            <label for="cliente" class="form-label">
                                <i class="fas fa-user-tie"></i> Cliente *
                            </label>
                            <select name="cliente" id="cliente" class="form-select required-field">
                                <option value="">Seleccione un Cliente</option>
                                <?php if (isset($clients) && !empty($clients)): ?>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?= esc($client['id_cliente']) ?>"
                                            <?= (service('request')->getGet('cliente') == $client['id_cliente']) ? 'selected' : '' ?>>
                                            <?= esc($client['nombre_cliente']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Año -->
                        <div class="col-lg-3">
                            <label for="anio" class="form-label">
                                <i class="fas fa-calendar-alt"></i> Año
                            </label>
                            <select name="anio" id="anio" class="form-select">
                                <?php foreach ($aniosFiltro as $anioOpcion): ?>
                                    <option value="<?= esc($anioOpcion) ?>" <?= (string) $anioSeleccionado === (string) $anioOpcion ? 'selected' : '' ?>>
                                        <?= $anioOpcion === 'todos' ? 'Todos' : esc($anioOpcion) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Fecha Desde -->
                        <div class="col-lg-3">
                            <label for="fecha_desde" class="form-label">
                                <i class="fas fa-calendar-plus"></i> Fecha Desde
                            </label>
                            <input type="date" name="fecha_desde" id="fecha_desde"
                                   class="form-control"
                                   value="<?= esc(service('request')->getGet('fecha_desde')) ?>">
                        </div>

                        <!-- Fecha Hasta -->
                        <div class="col-lg-3">
                            <label for="fecha_hasta" class="form-label">
                                <i class="fas fa-calendar-minus"></i> Fecha Hasta
                            </label>
                            <input type="date" name="fecha_hasta" id="fecha_hasta"
                                   class="form-control"
                                   value="<?= esc(service('request')->getGet('fecha_hasta')) ?>">
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="btn-group-filters">
                    <div class="row">
                        <div class="col-md-8 d-flex flex-wrap gap-2 align-items-center">
                            <button type="submit" class="btn btn-primary" id="btnBuscar">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button type="button" id="btnMostrarTodos" class="btn btn-success">
                                <i class="fas fa-eye"></i> Ver Todos
                            </button>
                            <button type="reset" id="resetFilters" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Limpiar
                            </button>
                        </div>
                        <div class="col-md-4 text-end d-flex flex-wrap gap-2 justify-content-end align-items-center">
                            <button type="button" id="btnDeleteSelected" class="btn btn-danger" style="display:none;">
                                <i class="fas fa-trash-alt"></i> Eliminar Seleccionados (<span id="selectedCount">0</span>)
                            </button>
                            <button type="button" id="btnCalificarCerradas" class="btn btn-warning">
                                <i class="fas fa-check-double"></i> Calificar Cerradas
                            </button>
                            <button type="button" id="btnFixCerradasSinFecha" class="btn btn-outline-warning" title="Asignar fecha propuesta como fecha de cierre a las actividades CERRADA sin fecha">
                                <i class="fas fa-calendar-check"></i> Fix Cerradas sin Fecha
                            </button>
                            <a href="<?= base_url('/pta-cliente-nueva/add?' . http_build_query($filters)) ?>" class="btn btn-info">
                                <i class="fas fa-plus"></i> Nuevo
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Mostrar la tabla solo si existen registros -->
        <?php if (!empty($records)): ?>
            <div class="table-responsive">
                <table id="ptaTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll" title="Seleccionar todos"></th>
                            <th>Acciones</th>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Fuente de la Actividad</th>
                            <th>PHVA</th>
                            <th>Numeral Plan Trabajo</th>
                            <th>Actividad</th>
                            <th>Responsable Sugerido</th>
                            <th>Fecha Propuesta</th>
                            <th>Fecha Cierre</th>
                            <th>Estado Actividad</th>
                            <th>Porcentaje Avance</th>
                            <th>Observaciones</th>
                            <th>Gestión Rápida</th>
                            <th>Responsable Definido</th>
                            <th>Semana</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td><input type="checkbox" class="row-select" value="<?= esc($row['id_ptacliente']) ?>"></td>
                                <td>
                                    <div class="action-group">
                                        <a href="<?= base_url('/pta-cliente-nueva/edit/' . esc($row['id_ptacliente']) . '?' . http_build_query($filters)) ?>" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-pen"></i></a>
                                        <button type="button" class="btn-action btn-action-delete btn-delete-single" data-id="<?= esc($row['id_ptacliente']) ?>" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                                <td><?= esc($row['id_ptacliente']) ?></td>
                                <td class="editable"><?= esc($row['nombre_cliente']) ?></td>
                                <td class="editable"><?= esc($row['tipo_servicio']) ?></td>
                                <?php
                                    $phvaMap = ['P'=>'PLANEAR','H'=>'HACER','V'=>'VERIFICAR','A'=>'ACTUAR'];
                                    $phvaRaw = strtoupper(trim($row['phva_plandetrabajo'] ?? ''));
                                    $phvaDisplay = $phvaMap[$phvaRaw] ?? $phvaRaw;
                                ?>
                                <td class="editable"><?= esc($phvaDisplay) ?></td>
                                <td class="editable"><?= esc($row['numeral_plandetrabajo']) ?></td>
                                <td class="editable">
                                    <div class="cell-truncate"><?= esc($row['actividad_plandetrabajo']) ?></div>
                                </td>
                                <td class="editable"><?= esc($row['responsable_sugerido_plandetrabajo']) ?></td>
                                <td class="editable"><?= esc($row['fecha_propuesta']) ?></td>
                                <td class="editable"><?= esc($row['fecha_cierre']) ?></td>
                                <td class="editable">
                                    <?php
                                    $estado = $row['estado_actividad'];
                                    $badgeClass = 'estado-abierta';
                                    if ($estado === 'CERRADA') $badgeClass = 'estado-cerrada';
                                    elseif ($estado === 'GESTIONANDO') $badgeClass = 'estado-gestionando';
                                    elseif ($estado === 'CERRADA SIN EJECUCIÓN') $badgeClass = 'estado-cerrada-sin';
                                    ?>
                                    <span class="estado-badge <?= $badgeClass ?>"><?= esc($estado) ?></span>
                                </td>
                                <td class="editable">
                                    <?php
                                    $pct = (float)($row['porcentaje_avance'] ?? 0);
                                    $barColor = '#e74a3b';
                                    if ($pct >= 100) $barColor = '#1cc88a';
                                    elseif ($pct >= 50) $barColor = '#4e73df';
                                    elseif ($pct > 0) $barColor = '#f6c23e';
                                    ?>
                                    <?php $barWidth = max($pct, 2); ?>
                                    <div class="mini-progress">
                                        <div class="mini-progress-bar">
                                            <div class="mini-progress-fill" style="width:<?= $barWidth ?>%;background:<?= $barColor ?>"></div>
                                        </div>
                                        <span class="mini-progress-text"><?= number_format($pct, 0) ?>%</span>
                                    </div>
                                </td>
                                <td class="editable">
                                    <div class="cell-truncate"><?= esc($row['observaciones']) ?></div>
                                </td>
                                <td class="text-center">
                                    <div class="month-buttons" style="display: grid; grid-template-columns: repeat(4, 32px); gap: 4px; justify-content: center;">
                                        <?php
                                        $mesesEspanol = [
                                            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                                            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                                        ];
                                        $mesActual = 0;
                                        if (!empty($row['fecha_propuesta'])) {
                                            $mesActual = (int) date('m', strtotime($row['fecha_propuesta']));
                                        }
                                        for ($month = 1; $month <= 12; $month++):
                                        ?>
                                            <button type="button"
                                                    class="btn-month<?= ($mesActual === $month) ? ' has-date' : '' ?>"
                                                    data-id="<?= esc($row['id_ptacliente']) ?>"
                                                    data-month="<?= $month ?>"
                                                    title="<?= $mesesEspanol[$month - 1] ?>">
                                                <?= $month ?>
                                            </button>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td><?= esc($row['responsable_definido_paralaactividad']) ?></td>
                                <td><?= esc($row['semana']) ?></td>
                                <td><?= esc($row['created_at']) ?></td>
                                <td><?= esc($row['updated_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th></th><!-- checkbox -->
                            <th></th><!-- acciones -->
                            <th><input type="text" placeholder="Buscar ID" class="form-control form-control-sm"></th>
                            <th><input type="text" placeholder="Buscar Cliente" class="form-control form-control-sm"></th>
                            <th><input type="text" placeholder="Buscar Fuente" class="form-control form-control-sm"></th>
                            <th><input type="text" placeholder="Buscar PHVA" class="form-control form-control-sm"></th>
                            <th><input type="text" placeholder="Buscar Numeral Plan Trabajo" class="form-control form-control-sm"></th>
                            <th><input type="text" placeholder="Buscar Actividad" class="form-control form-control-sm"></th>
                            <th><input type="text" placeholder="Buscar Responsable Sugerido" class="form-control form-control-sm"></th>
                            <th><input type="date" placeholder="Buscar Fecha Propuesta" class="form-control form-control-sm"></th>
                            <th><input type="date" placeholder="Buscar Fecha Cierre" class="form-control form-control-sm"></th>
                            <th>
                                <select class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="ABIERTA">ABIERTA</option>
                                    <option value="CERRADA">CERRADA</option>
                                    <option value="GESTIONANDO">GESTIONANDO</option>
                                    <option value="CERRADA SIN EJECUCIÓN">CERRADA SIN EJECUCIÓN</option>
                                </select>
                            </th>
                            <th><input type="text" placeholder="Buscar Porcentaje Avance" class="form-control form-control-sm"></th>
                            <th><input type="text" placeholder="Buscar Observaciones" class="form-control form-control-sm"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>

        <!-- Mensajes flash -->
        <?php if (session()->has('message')): ?>
            <div class="alert alert-success mt-3"><?= session('message') ?></div>
        <?php endif; ?>
        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger mt-3"><?= session('error') ?></div>
        <?php endif; ?>
        <?php if (session()->has('warning')): ?>
            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= session('warning') ?>
            </div>
        <?php endif; ?>
        <?php if (session()->has('info')): ?>
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                <?= session('info') ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- jQuery, Bootstrap 5 y DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // ==========================================
            // SISTEMA DE TOAST NOTIFICATIONS
            // ==========================================
            function mostrarToast(tipo, titulo, mensaje) {
                var container = document.getElementById('toastStack');
                var toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5);
                var hora = new Date().toLocaleTimeString('es-CO', {hour:'2-digit', minute:'2-digit', second:'2-digit'});

                var configs = {
                    'success':  { bg: 'bg-success', text: 'text-white', icon: 'fas fa-check-circle' },
                    'error':    { bg: 'bg-danger',  text: 'text-white', icon: 'fas fa-times-circle' },
                    'warning':  { bg: 'bg-warning', text: 'text-dark',  icon: 'fas fa-exclamation-triangle' },
                    'info':     { bg: 'bg-info',    text: 'text-white', icon: 'fas fa-info-circle' },
                    'progress': { bg: 'bg-primary', text: 'text-white', icon: '' }
                };
                var cfg = configs[tipo] || configs['info'];

                var iconHtml = tipo === 'progress'
                    ? '<span class="spinner-border spinner-border-sm me-2"></span>'
                    : '<i class="' + cfg.icon + ' me-2"></i>';

                var closeWhite = cfg.text === 'text-white' ? ' btn-close-white' : '';
                var toastHtml = '<div id="' + toastId + '" class="toast" role="alert">'
                    + '<div class="toast-header ' + cfg.bg + ' ' + cfg.text + '">'
                    + iconHtml
                    + '<strong class="me-auto">' + titulo + '</strong>'
                    + '<small>' + hora + '</small>'
                    + '<button type="button" class="btn-close' + closeWhite + '" data-bs-dismiss="toast"></button>'
                    + '</div>'
                    + '<div class="toast-body">' + mensaje + '</div>'
                    + '</div>';

                container.insertAdjacentHTML('beforeend', toastHtml);
                var toastEl = document.getElementById(toastId);

                var duraciones = { 'error': 8000, 'success': 5000, 'warning': 6000, 'info': 4000, 'progress': 60000 };
                var delay = duraciones[tipo] || 5000;
                var autohide = tipo !== 'progress';

                var toast = new bootstrap.Toast(toastEl, { delay: delay, autohide: autohide });
                toastEl.addEventListener('hidden.bs.toast', function() { toastEl.remove(); });
                toast.show();

                return { id: toastId, element: toastEl, instance: toast };
            }

            function cerrarToast(ref) {
                if (ref && ref.instance) ref.instance.hide();
            }

            // Helper: leer CSRF token dinámico desde la cookie (soporta regenerate=true)
            function getCsrfHash() {
                var name = '<?= config('Security')->cookieName ?>=';
                var cookies = decodeURIComponent(document.cookie).split(';');
                for (var i = 0; i < cookies.length; i++) {
                    var c = cookies[i].trim();
                    if (c.indexOf(name) === 0) {
                        return c.substring(name.length);
                    }
                }
                return '<?= csrf_hash() ?>'; // fallback al valor inicial
            }

            // Variables globales para filtros activos
            var activeYear = null;
            var activeMonth = null;
            var activeStatus = null;
            var activePrograma = null;

            // Guardar filtros de tarjetas en sessionStorage antes de reload
            function reloadWithFilters() {
                sessionStorage.setItem('ptaCardFilters', JSON.stringify({
                    year: activeYear,
                    month: activeMonth,
                    status: activeStatus,
                    programa: activePrograma
                }));
                location.reload();
            }


            // Initialize Select2 on client dropdown
            $('#cliente').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Buscar o seleccionar cliente...',
                allowClear: true,
                minimumInputLength: 0,
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });

            // Auto-submit al cambiar el año (si hay cliente seleccionado)
            $('#anio').on('change', function() {
                var cliente = $('#cliente').val();
                if (cliente) {
                    $('#filterForm').submit();
                }
            });

            // Generar tarjetas de años dinámicamente
            function generateYearCards() {
                if (!table) return;

                var yearCounts = {};

                // Contar actividades por año
                table.rows({search: 'applied'}).every(function() {
                    var data = this.data();
                    var fechaPropuesta = data[9]; // Columna "Fecha Propuesta" (shifted +1 by checkbox)
                    if (fechaPropuesta) {
                        var parts = fechaPropuesta.split("-");
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
                                    <small style="font-size: 0.75rem;">actividades</small>
                                </div>
                            </div>
                        </div>
                    `;
                });

                $('#yearCards').html(yearCardsHtml);
            }

            // Función para aplicar filtros combinados
            function applyFilters() {
                if (!table) return;

                $.fn.dataTable.ext.search.pop(); // Limpiar filtros personalizados previos

                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        var fechaPropuesta = data[9] || ''; // Columna 9: Fecha Propuesta (shifted +1)
                        var estadoActividad = $('<div/>').html(data[11] || '').text().trim(); // Columna 11: Estado (shifted +1)
                        var tipoServicio = (data[4] || '').trim(); // Columna 4: Fuente de la Actividad (tipo_servicio)

                        // Filtro por año
                        if (activeYear) {
                            if (!fechaPropuesta.startsWith(activeYear)) {
                                return false;
                            }
                        }

                        // Filtro por mes
                        if (activeMonth) {
                            if (fechaPropuesta) {
                                var parts = fechaPropuesta.split("-");
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
                        if (activeStatus && activeStatus !== 'ALL') {
                            if (estadoActividad.trim() !== activeStatus) {
                                return false;
                            }
                        }

                        // Filtro por programa (tipo_servicio)
                        if (activePrograma) {
                            if (activePrograma === '__SIN_PROGRAMA__') {
                                if (tipoServicio !== '') return false;
                            } else {
                                if (tipoServicio !== activePrograma) return false;
                            }
                        }

                        return true;
                    }
                );

                table.draw();

                // Actualizar tarjetas de año después de aplicar filtros
                generateYearCards();
            }

            // Click en tarjetas de año
            $(document).on('click', '.card-year', function() {
                var year = $(this).data('year');

                if ($(this).hasClass('active')) {
                    // Desactivar filtro
                    $(this).removeClass('active');
                    activeYear = null;
                } else {
                    // Activar filtro
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
                    // Desactivar filtro
                    $(this).removeClass('active');
                    activeMonth = null;
                } else {
                    // Activar filtro
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
                    // Desactivar filtro
                    $(this).removeClass('active');
                    activeStatus = null;
                } else {
                    // Activar filtro
                    $('.card-status').removeClass('active');
                    $(this).addClass('active');
                    activeStatus = status;
                }

                applyFilters();
            });

            // Click en tarjetas de programa
            $(document).on('click', '.card-programa', function() {
                var programa = $(this).data('programa');

                if ($(this).hasClass('active')) {
                    $(this).removeClass('active');
                    activePrograma = null;
                } else {
                    $('.card-programa').removeClass('active');
                    $(this).addClass('active');
                    activePrograma = programa;
                }

                applyFilters();
            });

            // Generar tarjetas de programa dinámicamente desde los datos de la tabla
            function generateProgramaCards() {
                if (!table) return;
                var programaCounts = {};
                var allData = table.column(4).data().toArray();
                allData.forEach(function(val) {
                    var key = (val || '').trim();
                    if (key === '') key = '__SIN_PROGRAMA__';
                    programaCounts[key] = (programaCounts[key] || 0) + 1;
                });

                var colores = [
                    'bg-primary', 'bg-success', 'bg-info', 'bg-warning text-dark',
                    'bg-danger', 'bg-purple', 'bg-teal', 'bg-orange',
                    'bg-indigo', 'bg-pink', 'bg-secondary'
                ];
                var container = $('#programaCards');
                container.empty();

                // Ordenar: primero los que tienen nombre, al final "Sin programa"
                var keys = Object.keys(programaCounts).sort(function(a, b) {
                    if (a === '__SIN_PROGRAMA__') return 1;
                    if (b === '__SIN_PROGRAMA__') return -1;
                    return a.localeCompare(b);
                });

                if (keys.length === 0) return;

                keys.forEach(function(key, idx) {
                    var displayName = key === '__SIN_PROGRAMA__' ? 'Sin programa' : key;
                    var colorClass = key === '__SIN_PROGRAMA__' ? 'bg-secondary' : colores[idx % colores.length];
                    var isActive = activePrograma === key ? ' active' : '';
                    var card = '<div class="col-6 col-md-2 mb-2">' +
                        '<div class="card text-white ' + colorClass + ' card-clickable card-programa' + isActive + '" data-programa="' + key + '" style="cursor:pointer">' +
                        '<div class="card-body p-2 text-center">' +
                        '<h6 class="card-title mb-0" style="font-size:0.75rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="' + displayName + '">' + displayName + '</h6>' +
                        '<p class="card-text display-6 mb-0">' + programaCounts[key] + '</p>' +
                        '</div></div></div>';
                    container.append(card);
                });
            }

            // Botón para limpiar todos los filtros de tarjetas
            $('#btnClearCardFilters').on('click', function() {
                // Limpiar estados
                activeYear = null;
                activeMonth = null;
                activeStatus = null;
                activePrograma = null;

                // Remover clases activas
                $('.card-year').removeClass('active');
                $('.card-month').removeClass('active');
                $('.card-status').removeClass('active');
                $('.card-programa').removeClass('active');

                // Limpiar filtros personalizados de DataTables
                $.fn.dataTable.ext.search.pop();

                if (table) {
                    table.draw();
                    generateYearCards(); // Regenerar tarjetas de año
                    generateProgramaCards(); // Regenerar tarjetas de programa
                }

                showAlert('Filtros de tarjetas limpiados. Mostrando todos los registros.', 'info');
            });

            // Botón para mostrar todos los registros (limpiar filtros de fecha)
            $('#btnMostrarTodos').on('click', function() {
                var cliente = $('#cliente').val();
                if (!cliente) {
                    showAlert('Primero debe seleccionar un Cliente antes de usar "Ver Todos".', 'warning');
                    return;
                }

                // Limpiar todos los filtros de fecha
                $('#anio').val('todos');
                $('#fecha_desde').val('');
                $('#fecha_hasta').val('');

                showAlert('Mostrando todos los registros del cliente seleccionado...', 'success');

                // Marcar que viene del botón "Ver Todos" para evitar validación de fechas
                $('#filterForm').data('via-todos', true);

                // Agregar campo oculto para que el backend sepa que es "Ver Todos"
                if (!$('#verTodosFlag').length) {
                    $('#filterForm').append('<input type="hidden" id="verTodosFlag" name="ver_todos" value="1">');
                }

                // Enviar automáticamente el formulario después de limpiar las fechas
                setTimeout(function() {
                    $('#filterForm').submit();
                }, 1000); // Esperar 1 segundo para que el usuario vea el mensaje
            });

            $('#filterForm').on('submit', function(e) {
                var cliente = $('#cliente').val();
                var anio = $('#anio').val();
                var fechaDesde = $('#fecha_desde').val();
                var fechaHasta = $('#fecha_hasta').val();

                // Validar que se haya seleccionado un cliente
                if (!cliente) {
                    showAlert('Debe seleccionar un Cliente.', 'error');
                    e.preventDefault();
                    return false;
                }

                // Quitar flag ver_todos si no viene del botón "Ver Todos"
                var esViaTodos = $(this).data('via-todos') === true;
                if (!esViaTodos) {
                    $('#verTodosFlag').remove();
                }
                var tieneFechas = fechaDesde && fechaHasta;

                // PERMITIR búsqueda si:
                // 1. Viene del botón "Ver Todos"
                // 2. Tiene fechas completas
                // 3. Tiene un año seleccionado
                var tieneAnio = !!anio;
                var puedeEjecutar = esViaTodos || tieneFechas || tieneAnio;

                if (!puedeEjecutar) {
                    showAlert('Debe especificar:\n• Año\n• O rango de fechas (Fecha Desde y Fecha Hasta)\n• O hacer clic en "Ver Todos" para mostrar todos los registros del cliente', 'warning');
                    e.preventDefault();
                    return false;
                }

                // Limpiar el flag después de usarlo
                $(this).removeData('via-todos');

                // Si tiene fechas manuales incompletas, avisar
                if ((fechaDesde && !fechaHasta) || (!fechaDesde && fechaHasta)) {
                    showAlert('Para usar rango manual debe completar tanto "Fecha Desde" como "Fecha Hasta".', 'warning');
                    e.preventDefault();
                    return false;
                }
            });

            // Función para mostrar alertas mejoradas
            function showAlert(message, type = 'info') {
                const alertClass = {
                    'error': 'alert-danger',
                    'warning': 'alert-warning',
                    'success': 'alert-success',
                    'info': 'alert-info'
                }[type] || 'alert-info';
                
                const icon = {
                    'error': 'fas fa-exclamation-circle',
                    'warning': 'fas fa-exclamation-triangle',
                    'success': 'fas fa-check-circle',
                    'info': 'fas fa-info-circle'
                }[type] || 'fas fa-info-circle';
                
                // Remover alertas previas
                $('.custom-alert').remove();
                
                // Crear nueva alerta
                const alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show custom-alert" role="alert" style="position: relative; z-index: 1050;">
                        <i class="${icon} me-2"></i>
                        <strong>${message.replace(/\n/g, '<br>')}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                
                // Insertar antes del formulario
                $('.filter-card').before(alertHtml);
                
                // Auto-ocultar después de 8 segundos
                setTimeout(function() {
                    $('.custom-alert').fadeOut();
                }, 8000);
            }

            var table;
            if ($('#ptaTable').length) {
                table = $('#ptaTable').DataTable({
                    "lengthChange": true,
                    "responsive": true,
                    "autoWidth": false,
                    "order": [
                        [11, 'asc'],
                        [9, 'asc'],
                        [5, 'asc'],
                        [7, 'asc']
                    ],
                    "dom": '<"row"<"col-sm-12"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                    "columnDefs": [
                        { "visible": false, "targets": [0, 1, 2, 4, 15, 16, 17, 18] },
                        { "orderable": false, "searchable": false, "targets": [0] },
                        { "orderable": false, "targets": [14] }
                    ],
                    "buttons": [
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i> Columnas Visibles',
                        className: 'btn btn-outline-primary',
                        columns: ':not(:first-child):not(:last-child)'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
                        className: 'btn btn-success',
                        title: 'Lista_PTA_Cliente',
                        charset: 'UTF-8',
                        bom: true,
                        exportOptions: {
                            columns: ':visible',
                            format: {
                                body: function(data, row, column, node) {
                                    return $('<div/>').html(data).text();
                                }
                            }
                        }
                    }],
                    "initComplete": function() {
                        this.api().columns().every(function() {
                            var column = this;
                            var select = $('select', column.footer());
                            var input = $('input', column.footer());
                            if (select.length) {
                                // Si la columna no es "Estado Actividad" (índice 10), agregamos las opciones
                                if (column.index() !== 11) {
                                    column.data().unique().sort().each(function(d) {
                                        if (d) {
                                            select.append('<option value="' + d + '">' + d + '</option>');
                                        }
                                    });
                                }
                                // En cualquier caso, asignamos el evento change
                                select.on('change', function() {
                                    var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                    column.search(val ? '^' + val + '$' : '', true, false).draw();
                                });
                            }
                            if (input.length) {
                                input.on('keyup change clear', function() {
                                    if (column.search() !== this.value) {
                                        column.search(this.value).draw();
                                    }
                                });
                            }
                        });
                    }
                });

                // Helper: extraer texto plano de HTML
                function stripHtml(html) {
                    return $('<div/>').html(html).text().trim();
                }

                // Función para actualizar los contadores de las tarjetas superiores
                function updateCardCounts() {
                    var data = table.column(11, {
                        search: 'applied'
                    }).data().toArray();
                    var countActivas = data.filter(function(x) {
                        return stripHtml(x) === 'ABIERTA';
                    }).length;
                    var countCerradas = data.filter(function(x) {
                        return stripHtml(x) === 'CERRADA';
                    }).length;
                    var countGestionando = data.filter(function(x) {
                        return stripHtml(x) === 'GESTIONANDO';
                    }).length;
                    var countCerradasSinEjecucion = data.filter(function(x) {
                        return stripHtml(x) === 'CERRADA SIN EJECUCIÓN';
                    }).length;
                    $('#countActivas').text(countActivas);
                    $('#countCerradas').text(countCerradas);
                    $('#countGestionando').text(countGestionando);
                    $('#countCerradasSinEjecucion').text(countCerradasSinEjecucion);
                    // Total es la suma de todas las filas filtradas
                    $('#countTotal').text(table.rows({
                        search: 'applied'
                    }).data().length);
                }

                // Función para actualizar los contadores mensuales basado en la fecha propuesta (columna 8)
                function updateMonthlyCounts() {
                    var monthlyCounts = Array(12).fill(0);
                    var data = table.rows({
                        search: 'applied'
                    }).data().toArray();
                    data.forEach(function(row) {
                        var fechaPropuesta = row[9]; // Columna "Fecha Propuesta" (shifted +1)
                        if (fechaPropuesta) {
                            // Se asume formato YYYY-MM-DD
                            var parts = fechaPropuesta.split("-");
                            if (parts.length >= 2) {
                                var month = parseInt(parts[1], 10);
                                if (!isNaN(month) && month >= 1 && month <= 12) {
                                    monthlyCounts[month - 1]++;
                                }
                            }
                        }
                    });
                    // Actualizar las cajitas de cada mes
                    var monthIds = ["countEnero", "countFebrero", "countMarzo", "countAbril", "countMayo", "countJunio", "countJulio", "countAgosto", "countSeptiembre", "countOctubre", "countNoviembre", "countDiciembre"];
                    monthIds.forEach(function(id, index) {
                        $('#' + id).text(monthlyCounts[index]);
                    });
                }

                table.on('draw', function() {
                    updateCardCounts();
                    updateMonthlyCounts();
                });
                updateCardCounts();
                updateMonthlyCounts();
                generateYearCards();
                generateProgramaCards();

                // Restaurar filtros de tarjetas desde sessionStorage (si venimos de un reload)
                var savedFilters = sessionStorage.getItem('ptaCardFilters');
                if (savedFilters) {
                    sessionStorage.removeItem('ptaCardFilters');
                    var f = JSON.parse(savedFilters);
                    if (f.year) {
                        activeYear = f.year;
                        $('.card-year[data-year="' + f.year + '"]').addClass('active');
                    }
                    if (f.month) {
                        activeMonth = f.month;
                        $('.card-month[data-month="' + f.month + '"]').addClass('active');
                    }
                    if (f.status) {
                        activeStatus = f.status;
                        $('.card-status[data-status="' + f.status + '"]').addClass('active');
                    }
                    if (f.programa) {
                        activePrograma = f.programa;
                        $('.card-programa[data-programa="' + f.programa + '"]').addClass('active');
                    }
                    if (f.year || f.month || f.status || f.programa) {
                        // Abrir el panel de filtros y aplicar
                        $('#cardFiltersPanel').addClass('show');
                        $('.filter-toggle-btn').removeClass('collapsed');
                        applyFilters();
                    }
                }

                // Helpers para construir HTML de badge y progress bar
                function buildEstadoBadge(estado) {
                    var cls = 'estado-abierta';
                    if (estado === 'CERRADA') cls = 'estado-cerrada';
                    else if (estado === 'GESTIONANDO') cls = 'estado-gestionando';
                    else if (estado === 'CERRADA SIN EJECUCIÓN') cls = 'estado-cerrada-sin';
                    return '<span class="estado-badge ' + cls + '">' + estado + '</span>';
                }
                function buildProgressBar(pct) {
                    pct = parseFloat(pct) || 0;
                    var color = '#e74a3b';
                    if (pct >= 100) color = '#1cc88a';
                    else if (pct >= 50) color = '#4e73df';
                    else if (pct > 0) color = '#f6c23e';
                    var w = Math.max(pct, 2); // minimo visible
                    return '<div class="mini-progress"><div class="mini-progress-bar"><div class="mini-progress-fill" style="width:' + w + '%;background:' + color + '"></div></div><span class="mini-progress-text">' + pct + '%</span></div>';
                }
                function buildTruncateCell(text) {
                    return '<div class="cell-truncate">' + $('<span/>').text(text).html() + '</div>';
                }

                $('#ptaTable tbody').on('dblclick', 'td.editable', function() {
                    var cell = table.cell(this);
                    var originalHtml = cell.data();
                    var $td = $(this);
                    if ($td.find('input, select').length > 0) return;
                    var colIndex = table.cell($td).index().column;
                    var editableMapping = {
                        4: 'tipo_servicio',
                        5: 'phva_plandetrabajo',
                        6: 'numeral_plandetrabajo',
                        7: 'actividad_plandetrabajo',
                        8: 'responsable_sugerido_plandetrabajo',
                        9: 'fecha_propuesta',
                        10: 'fecha_cierre',
                        11: 'estado_actividad',
                        12: 'porcentaje_avance',
                        13: 'observaciones'
                    };
                    var disallowed = [0, 1, 2, 3, 14, 15, 16, 17, 18];

                    // Opciones predefinidas para tipo_servicio (Fuente de la Actividad)
                    var opcionesTipoServicio = [
                        // Programas Tipo B (valores exactos que usan los Services)
                        'Programa Capacitación SST',
                        'Programa Induccion y Reinduccion',
                        'Objetivos SG-SST',
                        'Programa PyP Salud',
                        'PVE Riesgo Biomecanico',
                        'PVE Riesgo Psicosocial',
                        'Programa de Inspecciones',
                        'Estilos de Vida Saludable',
                        'Evaluaciones Medicas Ocupacionales',
                        'Mantenimiento Periodico',
                        // Opciones generales
                        'Plan de Trabajo Anual',
                        'Gestion SG-SST',
                    ];
                    if (disallowed.indexOf(colIndex) !== -1 || !editableMapping.hasOwnProperty(colIndex)) {
                        cell.data(originalHtml).draw();
                        return;
                    }

                    var plainValue = stripHtml(originalHtml);
                    if (colIndex === 12) plainValue = plainValue.replace('%', '').trim();

                    var inputElement;
                    if (colIndex === 4) {
                        // Tipo servicio: select con opciones predefinidas + "Otro..."
                        inputElement = $('<select class="form-select form-select-sm"></select>');
                        inputElement.append('<option value="">-- Sin programa --</option>');
                        $.each(opcionesTipoServicio, function(i, opt) {
                            var selected = (plainValue === opt) ? 'selected' : '';
                            inputElement.append('<option value="' + opt + '" ' + selected + '>' + opt + '</option>');
                        });
                        inputElement.append('<option value="__OTRO__"' + (plainValue && opcionesTipoServicio.indexOf(plainValue) === -1 && plainValue !== '' ? ' selected' : '') + '>Otro...</option>');
                        // Si el valor actual no está en las opciones, mostrar input de texto
                        if (plainValue && opcionesTipoServicio.indexOf(plainValue) === -1 && plainValue !== '') {
                            var wrapper = $('<div class="d-flex gap-1"></div>');
                            var customInput = $('<input type="text" class="form-control form-control-sm" />').val(plainValue);
                            inputElement.css('width', '55%');
                            customInput.css('width', '45%');
                            wrapper.append(inputElement).append(customInput);
                            inputElement = wrapper;
                            inputElement._isWrapper = true;
                        }
                    } else if (colIndex === 9 || colIndex === 10) {
                        inputElement = $('<input type="date" class="form-control form-control-sm" />').val(plainValue);
                    } else if (colIndex === 11) {
                        inputElement = $('<select class="form-select form-select-sm"></select>');
                        var options = ["ABIERTA", "CERRADA", "GESTIONANDO", "CERRADA SIN EJECUCIÓN"];
                        $.each(options, function(i, option) {
                            var selected = (plainValue === option) ? "selected" : "";
                            inputElement.append('<option value="' + option + '" ' + selected + '>' + option + '</option>');
                        });
                    } else if (colIndex === 12) {
                        inputElement = $('<input type="number" class="form-control form-control-sm" min="0" max="100" step="1" />').val(plainValue);
                    } else {
                        inputElement = $('<input type="text" class="form-control form-control-sm" />').val(plainValue);
                    }

                    $td.empty().append(inputElement);

                    // Para columna tipo_servicio: manejar "Otro..." con input dinámico
                    if (colIndex === 4) {
                        var $select = inputElement._isWrapper ? inputElement.find('select') : inputElement;
                        var $customInput = inputElement._isWrapper ? inputElement.find('input') : null;
                        $select.focus();

                        $select.on('change', function() {
                            if ($(this).val() === '__OTRO__') {
                                if (!$td.find('input[type="text"]').length) {
                                    var ci = $('<input type="text" class="form-control form-control-sm mt-1" placeholder="Escriba el nombre..." />');
                                    $td.append(ci);
                                    ci.focus();
                                    ci.on('blur keydown', function(ev) {
                                        if (ev.type === 'blur' || (ev.type === 'keydown' && ev.which === 13)) {
                                            ev.preventDefault();
                                            var customVal = $(this).val().trim();
                                            if (!customVal) { cell.data(originalHtml).draw(); return; }
                                            guardarTipoServicio(customVal);
                                        }
                                        if (ev.type === 'keydown' && ev.which === 27) { cell.data(originalHtml).draw(); }
                                    });
                                }
                            } else {
                                $td.find('input[type="text"]').remove();
                                var val = $(this).val();
                                guardarTipoServicio(val);
                            }
                        });

                        // Si ya es wrapper (valor custom existente), escuchar blur en el input
                        if ($customInput) {
                            $customInput.on('blur keydown', function(ev) {
                                if (ev.type === 'blur' || (ev.type === 'keydown' && ev.which === 13)) {
                                    ev.preventDefault();
                                    var val = $select.val() === '__OTRO__' ? $(this).val().trim() : $select.val();
                                    if (!val && $select.val() === '__OTRO__') { cell.data(originalHtml).draw(); return; }
                                    guardarTipoServicio(val || '');
                                }
                                if (ev.type === 'keydown' && ev.which === 27) { cell.data(originalHtml).draw(); }
                            });
                        }

                        // Escape para cancelar
                        $select.on('keydown', function(ev) {
                            if (ev.which === 27) { cell.data(originalHtml).draw(); }
                        });

                        function guardarTipoServicio(newVal) {
                            if (newVal === plainValue) { cell.data(originalHtml).draw(); return; }
                            var rowData = table.row($td.closest('tr')).data();
                            var id = rowData[2];
                            $.ajax({
                                url: "<?= site_url('/pta-cliente-nueva/editinginline') ?>",
                                method: "POST",
                                data: {
                                    id: id,
                                    tipo_servicio: newVal,
                                    "<?= csrf_token() ?>": "<?= csrf_hash() ?>"
                                },
                                dataType: "json",
                                success: function(response) {
                                    if (response.status === 'success') {
                                        cell.data(newVal).draw();
                                        generateProgramaCards();
                                    } else {
                                        cell.data(originalHtml).draw();
                                        showAlert('Error: ' + (response.message || 'No se pudo guardar'), 'danger');
                                    }
                                },
                                error: function() {
                                    cell.data(originalHtml).draw();
                                    showAlert('Error de conexión', 'danger');
                                }
                            });
                        }
                        return; // No ejecutar el blur genérico de abajo
                    }

                    inputElement.focus();

                    inputElement.on('blur keydown', function(e) {
                        if (e.type === 'blur' || (e.type === 'keydown' && e.which === 13)) {
                            var newValue = (colIndex === 11) ? inputElement.find("option:selected").val() : $(this).val();
                            if (newValue === plainValue) {
                                cell.data(originalHtml).draw();
                                return;
                            }
                            var fieldName = editableMapping[colIndex];
                            var rowData = table.row($td.closest('tr')).data();
                            var id = rowData[2];
                            var dataToSend = { id: id };
                            dataToSend[fieldName] = newValue;

                            if (colIndex === 10 && newValue && newValue.trim() !== '') {
                                dataToSend['estado_actividad'] = 'CERRADA';
                            }

                            dataToSend["<?= csrf_token() ?>"] = "<?= csrf_hash() ?>";

                            $.ajax({
                                url: "<?= site_url('/pta-cliente-nueva/editinginline') ?>",
                                method: "POST",
                                data: dataToSend,
                                dataType: "json",
                                success: function(response) {
                                    if (response.status === 'success') {
                                        // Reconstruir HTML según la columna
                                        if (colIndex === 11) {
                                            cell.data(buildEstadoBadge(newValue)).draw();
                                        } else if (colIndex === 12) {
                                            cell.data(buildProgressBar(newValue)).draw();
                                        } else if (colIndex === 7 || colIndex === 13) {
                                            cell.data(buildTruncateCell(newValue)).draw();
                                        } else {
                                            cell.data(newValue).draw();
                                        }

                                        // Fecha cierre → estado CERRADA
                                        if (colIndex === 10 && newValue && newValue.trim() !== '') {
                                            var estadoCell = table.cell($td.closest('tr'), 11);
                                            estadoCell.data(buildEstadoBadge('CERRADA')).draw();
                                        }

                                        // Estado cambió → actualizar porcentaje
                                        if (fieldName === 'estado_actividad' && response.porcentaje_avance !== undefined) {
                                            var porcentajeCell = table.cell($td.closest('tr'), 12);
                                            porcentajeCell.data(buildProgressBar(response.porcentaje_avance)).draw();
                                        }

                                        // Estado cambió a CERRADA → actualizar fecha_cierre
                                        if (fieldName === 'estado_actividad' && response.fecha_cierre !== undefined) {
                                            var fechaCierreCell = table.cell($td.closest('tr'), 10);
                                            fechaCierreCell.data(response.fecha_cierre).draw();
                                        }

                                        // Fecha cierre → actualizar porcentaje
                                        if (colIndex === 10 && response.porcentaje_avance !== undefined) {
                                            var porcentajeCell = table.cell($td.closest('tr'), 12);
                                            porcentajeCell.data(buildProgressBar(response.porcentaje_avance)).draw();
                                        }

                                        updateCardCounts();
                                        updateMonthlyCounts();
                                        initTruncateButtons();
                                    } else {
                                        alert('Error: ' + response.message);
                                        cell.data(originalHtml).draw();
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("AJAX error:", status, error);
                                    alert('Error en la comunicación con el servidor.');
                                    cell.data(originalHtml).draw();
                                }
                            });
                        }
                    });
                });
            }

            $('#resetFilters').click(function() {
                $('#filterForm')[0].reset();
                window.location.href = "<?= site_url('/pta-cliente-nueva/list') ?>";
            });

            // Manejador para el botón Calificar Cerradas
            $('#btnCalificarCerradas').click(function() {
                if (!$('#ptaTable').length) {
                    alert('Primero debe realizar una búsqueda para obtener registros');
                    return;
                }

                var ids = [];
                table.rows().every(function() {
                    var data = this.data();
                    if (stripHtml(data[11]) === 'CERRADA') {
                        ids.push(data[2]);
                    }
                });

                if (ids.length === 0) {
                    alert('No se encontraron registros con estado CERRADA');
                    return;
                }

                $.ajax({
                    url: '<?= site_url('/pta-cliente-nueva/updateCerradas') ?>',
                    method: 'POST',
                    data: {
                        ids: ids,
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            table.rows().every(function() {
                                var data = this.data();
                                if (stripHtml(data[11]) === 'CERRADA') {
                                    data[12] = '<div class="mini-progress"><div class="mini-progress-bar"><div class="mini-progress-fill" style="width:100%;background:#1cc88a"></div></div><span class="mini-progress-text">100%</span></div>';
                                    this.data(data);
                                }
                            });
                            updateCardCounts();
                            updateMonthlyCounts();
                            alert(response.message);
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error en la comunicación con el servidor');
                        console.error(error);
                    }
                });
            });

            // ===================================================================
            // GESTIÓN RÁPIDA: Asignación de fecha por mes con un solo clic
            // ===================================================================
            var mesesNombres = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                                'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

            $(document).on('click', '.btn-month', function() {
                var $button    = $(this);
                var activityId = $button.data('id');
                var month      = $button.data('month');
                var mesNombre  = mesesNombres[month - 1];

                // Feedback visual inmediato
                $button.prop('disabled', true).css('opacity', '0.5');

                // Toast de progreso
                var progressToast = mostrarToast('progress', 'Actualizando...', 'Asignando fecha a <strong>' + mesNombre + '</strong> para actividad #' + activityId);

                var csrfData = {};
                csrfData['<?= csrf_token() ?>'] = getCsrfHash();

                $.ajax({
                    url: '<?= site_url("/pta-cliente-nueva/updateDateByMonth") ?>',
                    method: 'POST',
                    data: $.extend({ id: activityId, month: month }, csrfData),
                    dataType: 'json',
                    success: function(response) {
                        cerrarToast(progressToast);

                        if (response.success) {
                            // Actualizar la celda de fecha_propuesta en DataTables
                            var row = table.row(function(idx, data, node) {
                                return data[2] == activityId;
                            });

                            if (row.length > 0) {
                                var rowData = row.data();
                                rowData[9]  = response.newDate;
                                row.data(rowData);
                            }

                            // Reordenar por fecha_propuesta ASC y redibujar
                            table.order([9, 'asc']).draw(false);

                            // Tras el redraw, re-buscar botones en la fila nueva
                            if (row.length > 0) {
                                var $newRow = $(row.node());
                                $newRow.find('.btn-month').removeClass('has-date');
                                $newRow.find('.btn-month[data-month="' + month + '"]').addClass('has-date');
                            }

                            // Actualizar contadores
                            updateCardCounts();
                            updateMonthlyCounts();

                            // Toast de éxito
                            mostrarToast('success', 'Fecha Asignada',
                                'Actividad #' + activityId + ' &rarr; <strong>' + response.formatted + '</strong> (' + mesNombre + ')');
                        } else {
                            mostrarToast('error', 'Error', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        cerrarToast(progressToast);
                        mostrarToast('error', 'Error de Conexión', 'No se pudo actualizar la fecha: ' + error);
                        console.error('Error AJAX:', xhr.responseText);
                    },
                    complete: function() {
                        $button.prop('disabled', false).css('opacity', '1');
                    }
                });
            });

            // ===================================================================
            // TEXTO TRUNCADO EXPANDIBLE
            // ===================================================================
            function initTruncateButtons() {
                $('.cell-truncate').each(function() {
                    var $el = $(this);
                    // Remover botón previo si existe
                    $el.next('.btn-expand').remove();
                    $el.removeClass('expanded');
                    // Solo agregar botón si el contenido desborda
                    if (this.scrollHeight > 65) {
                        if ($el.next('.btn-expand').length === 0) {
                            $el.after('<span class="btn-expand">ver m&aacute;s &#9660;</span>');
                        }
                    }
                });
            }

            // Delegado para click en "ver más / ver menos"
            $(document).on('click', '.btn-expand', function() {
                var $btn = $(this);
                var $cell = $btn.prev('.cell-truncate');
                if ($cell.hasClass('expanded')) {
                    $cell.removeClass('expanded');
                    $btn.html('ver m&aacute;s &#9660;');
                } else {
                    $cell.addClass('expanded');
                    $btn.html('ver menos &#9650;');
                }
            });

            // Inicializar después de cada draw de DataTables
            if (table) {
                table.on('draw', function() {
                    initTruncateButtons();
                });
                initTruncateButtons();
            }

            // ===================================================================
            // CHECKBOX SELECT ALL / BULK DELETE / SINGLE DELETE AJAX
            // ===================================================================
            function updateSelectedCount() {
                var count = $('.row-select:checked').length;
                $('#selectedCount').text(count);
                if (count > 0) {
                    $('#btnDeleteSelected').show();
                } else {
                    $('#btnDeleteSelected').hide();
                }
            }

            $(document).on('change', '#selectAll', function() {
                var checked = $(this).is(':checked');
                table.rows({ search: 'applied' }).nodes().each(function() {
                    $(this).find('.row-select').prop('checked', checked);
                });
                updateSelectedCount();
            });

            $(document).on('change', '.row-select', function() {
                if (!$(this).is(':checked')) {
                    $('#selectAll').prop('checked', false);
                }
                updateSelectedCount();
            });

            // Bulk delete con validación aritmética SweetAlert
            $('#btnDeleteSelected').on('click', function() {
                var ids = [];
                $('.row-select:checked').each(function() {
                    ids.push($(this).val());
                });
                if (ids.length === 0) return;

                var ops = [];
                for (var i = 0; i < 3; i++) {
                    var a = Math.floor(Math.random() * 50) + 10;
                    var b = Math.floor(Math.random() * 20) + 1;
                    var tipo = Math.random() > 0.5 ? '+' : '-';
                    var result = tipo === '+' ? a + b : a - b;
                    ops.push({ expr: a + ' ' + tipo + ' ' + b, result: result });
                }

                var htmlForm = '<div class="text-start">' +
                    '<div class="alert alert-danger"><strong>ADVERTENCIA:</strong> Se eliminarán <strong>' + ids.length + '</strong> registro(s) seleccionados. Esta acción no se puede deshacer.</div>' +
                    '<p class="fw-bold">Resuelva las 3 operaciones para confirmar:</p>';
                for (var i = 0; i < 3; i++) {
                    htmlForm += '<div class="mb-2"><label class="form-label">' + ops[i].expr + ' = </label>' +
                        '<input type="number" class="form-control form-control-sm bulk-arith-input" data-idx="' + i + '" style="max-width:120px; display:inline-block; margin-left:10px;" /></div>';
                }
                htmlForm += '</div>';

                Swal.fire({
                    title: 'Eliminar ' + ids.length + ' Registro(s)',
                    html: htmlForm,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Eliminar',
                    cancelButtonText: 'Cancelar',
                    preConfirm: () => {
                        var allCorrect = true;
                        for (var i = 0; i < 3; i++) {
                            var val = parseInt($('.bulk-arith-input[data-idx="' + i + '"]').val());
                            if (val !== ops[i].result) { allCorrect = false; break; }
                        }
                        if (!allCorrect) {
                            Swal.showValidationMessage('Una o más respuestas son incorrectas.');
                            return false;
                        }
                        return true;
                    }
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    var $btn = $('#btnDeleteSelected');
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Eliminando...');

                    $.ajax({
                        url: '<?= site_url('/pta-cliente-nueva/deleteMultiple') ?>',
                        method: 'POST',
                        data: {
                            ids: ids,
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                $('.row-select:checked').each(function() {
                                    var row = table.row($(this).closest('tr'));
                                    row.remove();
                                });
                                table.draw(false);
                                $('#selectAll').prop('checked', false);
                                updateSelectedCount();
                                updateCardCounts();
                                updateMonthlyCounts();
                                generateYearCards();
                                Swal.fire('Listo', response.message, 'success');
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error', 'Error en la comunicación: ' + error, 'error');
                        },
                        complete: function() {
                            $btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i> Eliminar Seleccionados (<span id="selectedCount">0</span>)');
                            updateSelectedCount();
                        }
                    });
                });
            });

            // Single delete via AJAX
            $(document).on('click', '.btn-delete-single', function() {
                var $btn = $(this);
                var id = $btn.data('id');

                if (!confirm('¿Seguro que deseas eliminar este registro?')) return;

                $btn.prop('disabled', true);

                $.ajax({
                    url: '<?= site_url('/pta-cliente-nueva/deleteMultiple') ?>',
                    method: 'POST',
                    data: {
                        ids: [id],
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            var row = table.row($btn.closest('tr'));
                            row.remove().draw(false);
                            updateCardCounts();
                            updateMonthlyCounts();
                            generateYearCards();
                            showAlert('Registro eliminado correctamente.', 'success');
                        } else {
                            showAlert('Error: ' + response.message, 'error');
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error al eliminar: ' + error, 'error');
                        $btn.prop('disabled', false);
                    }
                });
            });

            // Fix Cerradas sin Fecha
            $('#btnFixCerradasSinFecha').click(function() {
                var clienteId = '<?= esc($filters['cliente'] ?? '') ?>';
                if (!clienteId) {
                    alert('Primero seleccione un cliente');
                    return;
                }
                if (!confirm('¿Asignar la fecha propuesta como fecha de cierre a todas las actividades CERRADA que no tengan fecha de cierre?')) return;

                $.ajax({
                    url: '<?= site_url('/pta-cliente-nueva/fixCerradasSinFecha') ?>',
                    method: 'POST',
                    data: {
                        id_cliente: clienteId,
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            if (response.fixed > 0) reloadWithFilters();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error en la comunicación con el servidor');
                    }
                });
            });

            // Toggle botón acordeón (clase collapsed)
            $('#cardFiltersPanel').on('show.bs.collapse', function() {
                $('.filter-toggle-btn').removeClass('collapsed');
            }).on('hide.bs.collapse', function() {
                $('.filter-toggle-btn').addClass('collapsed');
            });

            // Manejador para el botón Socializar Plan de Trabajo
            $('#btnSocializarPlanTrabajo').on('click', function() {
                var clienteId = '<?= $filters['cliente'] ?? '' ?>';

                if (!clienteId) {
                    showAlert('Debe seleccionar un cliente primero.', 'warning');
                    return;
                }

                if (!$('#ptaTable').length) {
                    showAlert('Primero debe realizar una búsqueda para obtener registros.', 'warning');
                    return;
                }

                // Usar el año seleccionado en la tarjeta o el año actual por defecto
                var yearToSend = activeYear || new Date().getFullYear();

                var confirmMsg = '¿Desea enviar el Plan de Trabajo del año ' + yearToSend + ' por email al cliente y al consultor asignado?';
                if (!confirm(confirmMsg)) {
                    return;
                }

                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');

                $.ajax({
                    url: '<?= base_url('/socializacion/send-plan-trabajo') ?>',
                    method: 'POST',
                    data: {
                        id_cliente: clienteId,
                        year: yearToSend,
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            var mensaje = 'Email enviado exitosamente.\n\n';
                            mensaje += 'Cliente: ' + response.cliente + '\n';
                            mensaje += 'Año: ' + response.year + '\n';
                            mensaje += 'Total actividades: ' + response.totalActividades + '\n';
                            mensaje += 'Enviado a: ' + response.emailsEnviados.join(', ');
                            alert(mensaje);
                            showAlert('Email de socialización enviado correctamente.', 'success');
                        } else {
                            showAlert('Error: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error al enviar el email: ' + error, 'error');
                        console.error('Error AJAX:', status, error);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-envelope"></i> Socializar Plan');
                    }
                });
            });
        });
    </script>
    <!-- Modal Regenerar Plan -->
    <div class="modal fade" id="regenerarPlanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-redo"></i> Regenerar Plan de Trabajo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle"></i> Se insertarán actividades desde la plantilla CSV. Las actividades que ya existan en el año actual (cualquier estado) serán omitidas.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Año del SGSST <span class="text-danger">*</span></label>
                        <select class="form-select" id="regenerar_year" required>
                            <option value="">Seleccione...</option>
                            <option value="1">Año 1</option>
                            <option value="2">Año 2</option>
                            <option value="3">Año 3</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Servicio <span class="text-danger">*</span></label>
                        <select class="form-select" id="regenerar_service" required>
                            <option value="">Seleccione...</option>
                            <option value="mensual">Mensual</option>
                            <option value="bimensual">Bimensual</option>
                            <option value="trimestral">Trimestral</option>
                            <option value="proyecto">Proyecto</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info text-white" id="btnRegenerar"><i class="fas fa-redo"></i> Regenerar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Crear Actividad con IA -->
    <div class="modal fade" id="crearActividadIAModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #7c3aed, #a855f7); color: #fff;">
                    <h5 class="modal-title"><i class="fas fa-robot"></i> Crear Actividad con IA</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Paso 1: Elegir modo -->
                    <div id="iaStep1">
                        <h6 class="mb-3">¿Cómo desea crear la actividad?</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card h-100 text-center p-4" id="iaOpcionInventario" style="cursor:pointer; border:2px solid #e3e6f0;">
                                    <i class="fas fa-search fa-3x text-primary mb-3"></i>
                                    <h6>Del inventario existente</h6>
                                    <small class="text-muted">Buscar en las actividades del Decreto 1072</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100 text-center p-4" id="iaOpcionNuevo" style="cursor:pointer; border:2px solid #e3e6f0;">
                                    <i class="fas fa-magic fa-3x mb-3" style="color:#7c3aed;"></i>
                                    <h6>Item nuevo con IA</h6>
                                    <small class="text-muted">Describe lo que necesitas, la IA propone opciones</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 2A: Buscar en inventario -->
                    <div id="iaStep2A" style="display:none;">
                        <button class="btn btn-sm btn-outline-secondary mb-3" id="iaBackToStep1A"><i class="fas fa-arrow-left"></i> Volver</button>
                        <div class="mb-3">
                            <label class="form-label">Seleccione una o varias actividades:</label>
                            <select id="iaInventarioSelect" class="form-select" style="width:100%;" multiple>
                                <?php
                                $csvPath = FCPATH . '../PTA2026.csv';
                                if (file_exists($csvPath)) {
                                    $csvFile = fopen($csvPath, 'r');
                                    $first = true;
                                    while (($row = fgetcsv($csvFile, 0, ';')) !== false) {
                                        if ($first) { $first = false; continue; }
                                        $phva     = trim($row[0] ?? '');
                                        $numeral  = trim($row[1] ?? '');
                                        $actividad = trim($row[2] ?? '');
                                        if (!$phva || !$actividad) continue;
                                        $label = '[' . htmlspecialchars($phva) . ' - ' . htmlspecialchars($numeral) . '] ' . htmlspecialchars($actividad);
                                        echo '<option value="' . htmlspecialchars($actividad) . '" '
                                            . 'data-phva="' . htmlspecialchars($phva) . '" '
                                            . 'data-numeral="' . htmlspecialchars($numeral) . '">'
                                            . $label . '</option>' . "\n";
                                    }
                                    fclose($csvFile);
                                }
                                ?>
                            </select>
                        </div>
                        <button class="btn btn-primary" id="iaInventarioAgregar" disabled><i class="fas fa-plus"></i> Agregar <span id="iaInventarioCount"></span></button>
                    </div>

                    <!-- Paso 2B: Crear con IA -->
                    <div id="iaStep2B" style="display:none;">
                        <button class="btn btn-sm btn-outline-secondary mb-3" id="iaBackToStep1B"><i class="fas fa-arrow-left"></i> Volver</button>
                        <div class="mb-3">
                            <label class="form-label">Describa la actividad que necesita:</label>
                            <textarea class="form-control" id="iaDescripcion" rows="3" placeholder="Ej: necesito una actividad sobre pausas activas para trabajadores administrativos..."></textarea>
                        </div>
                        <button class="btn btn-purple text-white mb-3" id="iaGenerarBtn"><i class="fas fa-magic"></i> Generar opciones con IA</button>
                        <div id="iaGenerating" style="display:none;" class="text-center my-3">
                            <div class="spinner-border" role="status" style="color:#7c3aed;"></div>
                            <p class="mt-2 text-muted">La IA está generando opciones...</p>
                        </div>
                        <div id="iaOptions" style="max-height:300px; overflow-y:auto;"></div>
                        <div id="iaRefineSection" style="display:none;" class="mt-3">
                            <div class="input-group">
                                <input type="text" class="form-control" id="iaRefineInput" placeholder="Ajuste o realinee a la IA...">
                                <button class="btn btn-outline-secondary" id="iaRefineBtn"><i class="fas fa-sync"></i> Refinar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        var clienteId = '<?= $filters['cliente'] ?? '' ?>';
        var csrfName = '<?= csrf_token() ?>';
        var csrfHash = '<?= csrf_hash() ?>';

        // =====================================================================
        // BOTÓN ELIMINAR ABIERTAS - Triple validación aritmética
        // =====================================================================
        $('#btnEliminarAbiertas').on('click', function() {
            if (!clienteId) { alert('Seleccione un cliente primero.'); return; }

            var ops = [];
            for (var i = 0; i < 3; i++) {
                var a = Math.floor(Math.random() * 50) + 10;
                var b = Math.floor(Math.random() * 20) + 1;
                var tipo = Math.random() > 0.5 ? '+' : '-';
                var result = tipo === '+' ? a + b : a - b;
                ops.push({ expr: a + ' ' + tipo + ' ' + b, result: result });
            }

            var htmlForm = '<div class="text-start">' +
                '<div class="alert alert-danger"><strong>ADVERTENCIA:</strong> Esta acción eliminará TODAS las actividades en estado ABIERTA de este cliente. No se puede deshacer.</div>' +
                '<p class="fw-bold">Resuelva las 3 operaciones para confirmar:</p>';
            for (var i = 0; i < 3; i++) {
                htmlForm += '<div class="mb-2"><label class="form-label">' + ops[i].expr + ' = </label>' +
                    '<input type="number" class="form-control form-control-sm arith-input" data-idx="' + i + '" style="max-width:120px; display:inline-block; margin-left:10px;" /></div>';
            }
            htmlForm += '</div>';

            Swal.fire({
                title: 'Eliminar Actividades Abiertas',
                html: htmlForm,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    var allCorrect = true;
                    for (var i = 0; i < 3; i++) {
                        var val = parseInt($('.arith-input[data-idx="' + i + '"]').val());
                        if (val !== ops[i].result) { allCorrect = false; break; }
                    }
                    if (!allCorrect) {
                        Swal.showValidationMessage('Una o más respuestas son incorrectas.');
                        return false;
                    }
                    return true;
                }
            }).then((result) => {
                if (!result.isConfirmed) return;

                var $btn = $('#btnEliminarAbiertas');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Eliminando...');

                $.ajax({
                    url: '<?= site_url('/pta-cliente-nueva/deleteAbiertas') ?>',
                    method: 'POST',
                    data: { id_cliente: clienteId, [csrfName]: csrfHash },
                    dataType: 'json',
                    success: function(resp) {
                        if (resp.success) {
                            Swal.fire('Listo', resp.message, 'success').then(() => reloadWithFilters());
                        } else {
                            Swal.fire('Error', resp.message, 'error');
                        }
                    },
                    error: function() { Swal.fire('Error', 'Error de comunicación', 'error'); },
                    complete: function() { $btn.prop('disabled', false).html('<i class="fas fa-eraser"></i> Eliminar Abiertas'); }
                });
            });
        });

        // =====================================================================
        // BOTÓN REGENERAR PLAN
        // =====================================================================
        $('#btnRegenerar').on('click', function() {
            var year = $('#regenerar_year').val();
            var service = $('#regenerar_service').val();
            if (!year || !service) { alert('Seleccione año y tipo de servicio.'); return; }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Regenerando...');

            $.ajax({
                url: '<?= site_url('/pta-cliente-nueva/regenerarPlan') ?>',
                method: 'POST',
                data: { id_cliente: clienteId, year: year, service_type: service, [csrfName]: csrfHash },
                dataType: 'json',
                success: function(resp) {
                    if (resp.success) {
                        $('#regenerarPlanModal').modal('hide');
                        Swal.fire('Plan Regenerado', resp.message, 'success').then(() => reloadWithFilters());
                    } else {
                        Swal.fire('Error', resp.message, 'error');
                    }
                },
                error: function() { Swal.fire('Error', 'Error de comunicación', 'error'); },
                complete: function() { $btn.prop('disabled', false).html('<i class="fas fa-redo"></i> Regenerar'); }
            });
        });

        // =====================================================================
        // CREAR ACTIVIDAD CON IA
        // =====================================================================
        var iaLastDescription = '';

        $('#iaOpcionInventario').on('click', function() {
            $('#iaStep1').hide(); $('#iaStep2A').show();
        });
        $('#iaOpcionNuevo').on('click', function() {
            $('#iaStep1').hide(); $('#iaStep2B').show();
            $('#iaDescripcion').focus();
        });
        $('#iaBackToStep1A, #iaBackToStep1B').on('click', function() {
            $('#iaStep2A, #iaStep2B').hide(); $('#iaStep1').show();
        });

        $('#crearActividadIAModal').on('hidden.bs.modal', function() {
            $('#iaStep2A, #iaStep2B').hide(); $('#iaStep1').show();
            $('#iaOptions').empty();
            $('#iaInventarioSelect').val(null).trigger('change');
            $('#iaInventarioAgregar').prop('disabled', true);
            $('#iaDescripcion, #iaRefineInput').val('');
            $('#iaRefineSection').hide();
        });

        // Select2 inventario
        $('#iaInventarioSelect').select2({
            theme: 'bootstrap-5',
            placeholder: 'Buscar y seleccionar actividades...',
            allowClear: true,
            closeOnSelect: false,
            dropdownParent: $('#crearActividadIAModal'),
            templateResult: function(opt) {
                if (!opt.id) return opt.text;
                var checked = $(opt.element).is(':selected') ? 'checked' : '';
                return $('<span><input type="checkbox" ' + checked + ' style="margin-right:7px;pointer-events:none;">' + opt.text + '</span>');
            },
            templateSelection: function(opt) { return opt.text || opt.id; }
        });

        $('#iaInventarioSelect').on('select2:select select2:unselect', function() {
            var search = $(this).data('select2').dropdown.$search || $(this).data('select2').selection.$search;
            var term = search.val();
            setTimeout(function() { search.val(term).trigger('input'); }, 0);
        });

        $('#iaInventarioSelect').on('change', function() {
            var n = $(this).val() ? $(this).val().length : 0;
            $('#iaInventarioAgregar').prop('disabled', n === 0);
            $('#iaInventarioCount').text(n > 0 ? n + (n === 1 ? ' actividad' : ' actividades') : '');
        });

        $('#iaInventarioAgregar').on('click', function() {
            var $opts = $('#iaInventarioSelect').find('option:selected');
            if (!$opts.length) return;

            var lista = '';
            $opts.each(function() { lista += '<li>' + escHtml($(this).val()) + '</li>'; });

            Swal.fire({
                title: 'Confirmar ' + $opts.length + ($opts.length === 1 ? ' actividad' : ' actividades'),
                html: '<ul style="text-align:left;max-height:150px;overflow-y:auto;">' + lista + '</ul>' + buildMonthPanel(),
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Insertar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#7c3aed',
                preConfirm: () => {
                    var months = getSelectedMonths();
                    if ($('#swalMonthToggle').is(':checked') && months.length === 0) {
                        Swal.showValidationMessage('Seleccione al menos un mes');
                        return false;
                    }
                    return { months: months };
                }
            }).then((result) => {
                if (!result.isConfirmed) return;

                var items = [];
                $opts.each(function() {
                    items.push({ phva: $(this).data('phva'), numeral: $(this).data('numeral'), actividad: $(this).val() });
                });
                var months = result.value.months;
                var totalInserted = 0;

                var idx = 0;
                function insertNext() {
                    if (idx >= items.length) {
                        $('#crearActividadIAModal').modal('hide');
                        var totalRows = months.length > 0 ? items.length * months.length : items.length;
                        Swal.fire('Listo', totalRows + ' fila(s) insertada(s).', 'success').then(() => reloadWithFilters());
                        return;
                    }
                    var item = items[idx++];
                    doInsertActivity(item.phva, item.numeral, item.actividad, months, function(resp) {
                        totalInserted += (resp.inserted || 1);
                        insertNext();
                    });
                }
                insertNext();
            });
        });

        // Generar con IA
        $('#iaGenerarBtn').on('click', function() { doAiGenerate($('#iaDescripcion').val().trim(), ''); });
        $('#iaRefineBtn').on('click', function() { doAiGenerate(iaLastDescription, $('#iaRefineInput').val().trim()); });

        function doAiGenerate(description, context) {
            if (!description) { alert('Describa la actividad.'); return; }
            iaLastDescription = description;

            $('#iaGenerating').show();
            $('#iaOptions').empty();
            $('#iaRefineSection').hide();

            $.ajax({
                url: '<?= site_url('/pta-cliente-nueva/generateAiActivity') ?>',
                method: 'POST',
                data: { description: description, context: context, id_cliente: clienteId, [csrfName]: csrfHash },
                dataType: 'json',
                success: function(resp) {
                    $('#iaGenerating').hide();
                    if (!resp.success) { Swal.fire('Error', resp.message, 'error'); return; }

                    var html = '<div class="list-group">';
                    resp.options.forEach(function(opt, idx) {
                        html += '<button type="button" class="list-group-item list-group-item-action ia-select-activity" ' +
                            'data-phva="' + escHtml(opt.phva) + '" data-numeral="' + escHtml(opt.numeral) + '" data-actividad="' + escHtml(opt.actividad) + '">' +
                            '<span class="badge bg-primary me-2">Opción ' + (idx+1) + '</span>' +
                            '<strong>[' + escHtml(opt.phva) + ' - ' + escHtml(opt.numeral) + ']</strong> ' + escHtml(opt.actividad) + '</button>';
                    });
                    html += '</div>';
                    $('#iaOptions').html(html);
                    $('#iaRefineSection').show();
                },
                error: function() { $('#iaGenerating').hide(); Swal.fire('Error', 'Error de comunicación', 'error'); }
            });
        }

        // HTML del panel de meses reutilizable
        var monthNames = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        function buildMonthPanel() {
            var html = '<div class="mt-3 text-start">' +
                '<div class="form-check mb-2">' +
                '<input type="checkbox" class="form-check-input" id="swalMonthToggle">' +
                '<label class="form-check-label fw-bold" for="swalMonthToggle">Programar en meses específicos</label>' +
                '</div>' +
                '<div id="swalMonthGrid" style="display:none;">' +
                '<small class="text-muted d-block mb-2">Se creará una fila por cada mes seleccionado (último día del mes)</small>' +
                '<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:6px;">';
            for (var i = 1; i <= 12; i++) {
                html += '<button type="button" class="btn btn-sm btn-outline-secondary swal-month-btn" data-month="' + i + '">' + monthNames[i-1] + '</button>';
            }
            html += '</div></div></div>';
            return html;
        }

        function getSelectedMonths() {
            if (!$('#swalMonthToggle').is(':checked')) return [];
            var months = [];
            $('.swal-month-btn.btn-primary').each(function() { months.push($(this).data('month')); });
            return months;
        }

        // Delegado para toggle y click de meses dentro de SweetAlert
        $(document).on('change', '#swalMonthToggle', function() {
            $('#swalMonthGrid').toggle($(this).is(':checked'));
        });
        $(document).on('click', '.swal-month-btn', function() {
            $(this).toggleClass('btn-outline-secondary btn-primary text-white');
        });

        // Función para insertar actividad(es) con meses opcionales
        function doInsertActivity(phva, numeral, actividad, months, callback) {
            var sendData = { id_cliente: clienteId, phva: phva, numeral: numeral, actividad: actividad, [csrfName]: csrfHash };
            if (months && months.length > 0) {
                sendData.months = months;
            }
            $.ajax({
                url: '<?= site_url('/pta-cliente-nueva/insertAiActivity') ?>',
                method: 'POST',
                data: sendData,
                dataType: 'json',
                success: function(resp) {
                    if (resp.success) {
                        if (callback) callback(resp);
                        else {
                            $('#crearActividadIAModal').modal('hide');
                            Swal.fire('Insertada', resp.message, 'success').then(() => reloadWithFilters());
                        }
                    } else {
                        Swal.fire('Error', resp.message, 'error');
                    }
                },
                error: function() { Swal.fire('Error', 'Error de comunicación', 'error'); }
            });
        }

        $(document).on('click', '.ia-select-activity', function() {
            var phva = $(this).data('phva');
            var numeral = $(this).data('numeral');
            var actividad = $(this).data('actividad');

            Swal.fire({
                title: 'Confirmar actividad',
                html: '<p><strong>PHVA:</strong> ' + escHtml(phva) + '</p>' +
                      '<p><strong>Numeral:</strong> ' + escHtml(numeral) + '</p>' +
                      '<p><strong>Actividad:</strong> ' + escHtml(actividad) + '</p>' +
                      buildMonthPanel(),
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Insertar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#7c3aed',
                preConfirm: () => {
                    var months = getSelectedMonths();
                    if ($('#swalMonthToggle').is(':checked') && months.length === 0) {
                        Swal.showValidationMessage('Seleccione al menos un mes');
                        return false;
                    }
                    return { months: months };
                }
            }).then((result) => {
                if (!result.isConfirmed) return;
                doInsertActivity(phva, numeral, actividad, result.value.months);
            });
        });

        function escHtml(str) {
            if (!str) return '';
            return $('<div>').text(str).html();
        }
    });
    </script>
</body>

</html>
