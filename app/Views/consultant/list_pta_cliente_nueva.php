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
    <style>
        body {
            padding: 20px;
        }

        .dataTables_wrapper .dataTables_filter {
            float: right;
            text-align: right;
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

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #4e73df;
            border-left: 4px solid #4e73df;
            padding-left: 10px;
            margin: 20px 0 15px 0;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <!-- Enlace a Dashboard -->
        <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm mb-3">Ir a DashBoard</a>

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

        <h1 class="mb-4">Plan de Trabajo Anual Cliente</h1>
        
        <!-- FORMULARIO DE FILTROS MEJORADO -->
        <div class="filter-card">
            <form id="filterForm" method="get" action="<?= site_url('/pta-cliente-nueva/list') ?>">

                <!-- Filtros en una sola fila -->
                <div class="filter-section">
                    <h6><i class="fas fa-filter"></i> Filtros de Búsqueda</h6>
                    <div class="row mb-3">
                        <!-- Cliente (Campo requerido) -->
                        <div class="col-lg-4">
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

                        <!-- Fecha Desde -->
                        <div class="col-lg-4">
                            <label for="fecha_desde" class="form-label">
                                <i class="fas fa-calendar-plus"></i> Fecha Desde
                            </label>
                            <input type="date" name="fecha_desde" id="fecha_desde"
                                   class="form-control"
                                   value="<?= esc(service('request')->getGet('fecha_desde')) ?>">
                        </div>

                        <!-- Fecha Hasta -->
                        <div class="col-lg-4">
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
                        <div class="col-md-8">
                            <button type="submit" class="btn btn-primary me-2" id="btnBuscar">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button type="button" id="btnMostrarTodos" class="btn btn-success me-2">
                                <i class="fas fa-eye"></i> Ver Todos
                            </button>
                            <button type="reset" id="resetFilters" class="btn btn-secondary me-2">
                                <i class="fas fa-undo"></i> Limpiar
                            </button>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" id="btnCalificarCerradas" class="btn btn-warning me-2">
                                <i class="fas fa-check-double"></i> Calificar Cerradas
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
                            <th>Acciones</th>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th class="d-none">Tipo Servicio</th>
                            <th>PHVA</th>
                            <th>Numeral Plan Trabajo</th>
                            <th>Actividad</th>
                            <th>Responsable Sugerido</th>
                            <th>Fecha Propuesta</th>
                            <th>Fecha Cierre</th>
                            <th>Estado Actividad</th>
                            <th>Porcentaje Avance</th>
                            <th>Observaciones</th>
                            <th class="d-none">Responsable Definido</th>
                            <th class="d-none">Semana</th>
                            <th class="d-none">Created At</th>
                            <th class="d-none">Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td>
                                    <!-- Se incluyen los filtros en los enlaces de editar y eliminar -->
                                    <a href="<?= base_url('/pta-cliente-nueva/edit/' . esc($row['id_ptacliente']) . '?' . http_build_query($filters)) ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="<?= base_url('/pta-cliente-nueva/delete/' . esc($row['id_ptacliente']) . '?' . http_build_query($filters)) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este registro?')">Eliminar</a>
                                </td>
                                <td><?= esc($row['id_ptacliente']) ?></td>
                                <td class="editable"><?= esc($row['nombre_cliente']) ?></td>
                                <td class="d-none"><?= esc($row['tipo_servicio']) ?></td>
                                <td class="editable"><?= esc($row['phva_plandetrabajo']) ?></td>
                                <td class="editable"><?= esc($row['numeral_plandetrabajo']) ?></td>
                                <td class="editable"><?= esc($row['actividad_plandetrabajo']) ?></td>
                                <td class="editable"><?= esc($row['responsable_sugerido_plandetrabajo']) ?></td>
                                <td class="editable"><?= esc($row['fecha_propuesta']) ?></td>
                                <td class="editable"><?= esc($row['fecha_cierre']) ?></td>
                                <td class="editable"><?= esc($row['estado_actividad']) ?></td>
                                <td class="editable"><?= esc($row['porcentaje_avance']) ?></td>
                                <td class="editable"><?= esc($row['observaciones']) ?></td>
                                <td class="d-none"><?= esc($row['responsable_definido_paralaactividad']) ?></td>
                                <td class="d-none"><?= esc($row['semana']) ?></td>
                                <td class="d-none"><?= esc($row['created_at']) ?></td>
                                <td class="d-none"><?= esc($row['updated_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th></th>
                            <th><input type="text" placeholder="Buscar ID" class="form-control form-control-sm"></th>
                            <th><input type="text" placeholder="Buscar Cliente" class="form-control form-control-sm"></th>
                            <th class="d-none"></th>
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
                            <th class="d-none"></th>
                            <th class="d-none"></th>
                            <th class="d-none"></th>
                            <th class="d-none"></th>
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
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Variables globales para filtros activos
            var activeYear = null;
            var activeMonth = null;
            var activeStatus = null;

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

            // Generar tarjetas de años dinámicamente
            function generateYearCards() {
                if (!table) return;

                var years = new Set();
                table.rows().every(function() {
                    var data = this.data();
                    var fechaPropuesta = data[8]; // Columna "Fecha Propuesta"
                    if (fechaPropuesta) {
                        var parts = fechaPropuesta.split("-");
                        if (parts.length >= 1) {
                            years.add(parts[0]);
                        }
                    }
                });

                var yearArray = Array.from(years).sort().reverse();
                var yearCardsHtml = '';

                yearArray.forEach(function(year) {
                    yearCardsHtml += `
                        <div class="col-6 col-md-2">
                            <div class="card text-white card-year card-clickable" data-year="${year}">
                                <div class="card-body text-center p-3">
                                    <h4 class="card-title mb-0">${year}</h4>
                                    <small>Click para filtrar</small>
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
                        var fechaPropuesta = data[8] || ''; // Columna 8: Fecha Propuesta
                        var estadoActividad = data[10] || ''; // Columna 10: Estado

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

                        return true;
                    }
                );

                table.draw();
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

            // Botón para limpiar todos los filtros de tarjetas
            $('#btnClearCardFilters').on('click', function() {
                // Limpiar estados
                activeYear = null;
                activeMonth = null;
                activeStatus = null;

                // Remover clases activas
                $('.card-year').removeClass('active');
                $('.card-month').removeClass('active');
                $('.card-status').removeClass('active');

                // Limpiar filtros personalizados de DataTables
                $.fn.dataTable.ext.search.pop();

                if (table) {
                    table.draw();
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
                $('#fecha_desde').val('');
                $('#fecha_hasta').val('');

                showAlert('Mostrando todos los registros del cliente seleccionado...', 'success');

                // Marcar que viene del botón "Ver Todos" para evitar validación de fechas
                $('#filterForm').data('via-todos', true);

                // Enviar automáticamente el formulario después de limpiar las fechas
                setTimeout(function() {
                    $('#filterForm').submit();
                }, 1000); // Esperar 1 segundo para que el usuario vea el mensaje
            });

            $('#filterForm').on('submit', function(e) {
                var cliente = $('#cliente').val();
                var fechaDesde = $('#fecha_desde').val();
                var fechaHasta = $('#fecha_hasta').val();

                // Validar que se haya seleccionado un cliente
                if (!cliente) {
                    showAlert('Debe seleccionar un Cliente.', 'error');
                    e.preventDefault();
                    return false;
                }

                // Validar filtros de búsqueda
                var esViaTodos = $(this).data('via-todos') === true;
                var tieneFechas = fechaDesde && fechaHasta;

                // PERMITIR búsqueda si:
                // 1. Viene del botón "Ver Todos"
                // 2. Tiene fechas completas
                var puedeEjecutar = esViaTodos || tieneFechas;

                if (!puedeEjecutar) {
                    showAlert('Debe especificar:\n• Rango de fechas (Fecha Desde y Fecha Hasta)\n• O hacer clic en "Ver Todos" para mostrar todos los registros del cliente', 'warning');
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
                        [10, 'asc'],
                        [8, 'asc'],
                        [4, 'asc'],
                        [6, 'asc']
                    ],
                    "dom": '<"row"<"col-sm-12"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                    "buttons": [{
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
                                    // Decode HTML entities
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
                                if (column.index() !== 10) {
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

                // Función para actualizar los contadores de las tarjetas superiores
                function updateCardCounts() {
                    var data = table.column(10, {
                        search: 'applied'
                    }).data().toArray();
                    var countActivas = data.filter(function(x) {
                        return x.trim() === 'ABIERTA';
                    }).length;
                    var countCerradas = data.filter(function(x) {
                        return x.trim() === 'CERRADA';
                    }).length;
                    var countGestionando = data.filter(function(x) {
                        return x.trim() === 'GESTIONANDO';
                    }).length;
                    var countCerradasSinEjecucion = data.filter(function(x) {
                        return x.trim() === 'CERRADA SIN EJECUCIÓN';
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
                        var fechaPropuesta = row[8]; // Columna "Fecha Propuesta"
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
                generateYearCards(); // Generar tarjetas de año al inicializar

                $('#ptaTable tbody').on('dblclick', 'td.editable', function() {
                    var cell = table.cell(this);
                    var originalValue = cell.data();
                    var $td = $(this);
                    if ($td.find('input, select').length > 0) return;
                    var colIndex = table.cell($td).index().column;
                    var editableMapping = {
                        4: 'phva_plandetrabajo',
                        5: 'numeral_plandetrabajo',
                        6: 'actividad_plandetrabajo',
                        7: 'responsable_sugerido_plandetrabajo',
                        8: 'fecha_propuesta',
                        9: 'fecha_cierre',
                        10: 'estado_actividad',
                        11: 'porcentaje_avance',
                        12: 'observaciones'
                    };
                    var disallowed = [0, 1, 2, 3, 13, 14, 15, 16];
                    if (disallowed.indexOf(colIndex) !== -1 || !editableMapping.hasOwnProperty(colIndex)) {
                        cell.data(originalValue).draw();
                        return;
                    }

                    var inputElement;
                    if (colIndex === 8 || colIndex === 9) {
                        inputElement = $('<input type="date" class="form-control form-control-sm" />').val(originalValue);
                    } else if (colIndex === 10) {
                        inputElement = $('<select class="form-select form-select-sm"></select>');
                        var options = ["ABIERTA", "CERRADA", "GESTIONANDO", "CERRADA SIN EJECUCIÓN"];
                        $.each(options, function(i, option) {
                            var selected = (originalValue === option) ? "selected" : "";
                            inputElement.append('<option value="' + option + '" ' + selected + '>' + option + '</option>');
                        });
                    } else {
                        inputElement = $('<input type="text" class="form-control form-control-sm" />').val(originalValue);
                    }

                    $td.empty().append(inputElement);
                    inputElement.focus();

                    inputElement.on('blur keydown', function(e) {
                        if (e.type === 'blur' || (e.type === 'keydown' && e.which === 13)) {
                            var newValue = (colIndex === 10) ? inputElement.find("option:selected").val() : $(this).val();
                            if (newValue === originalValue) {
                                cell.data(originalValue).draw();
                                return;
                            }
                            var fieldName = editableMapping[colIndex];
                            var rowData = table.row($td.closest('tr')).data();
                            var id = rowData[1];
                            var dataToSend = {
                                id: id
                            };
                            dataToSend[fieldName] = newValue;
                            dataToSend["<?= csrf_token() ?>"] = "<?= csrf_hash() ?>";

                            $.ajax({
                                url: "<?= site_url('/pta-cliente-nueva/editinginline') ?>",
                                method: "POST",
                                data: dataToSend,
                                dataType: "json",
                                success: function(response) {
                                    if (response.status === 'success') {
                                        cell.data(newValue).draw();
                                        
                                        // Si se cambió el estado y se retornó un porcentaje, actualizar la celda del porcentaje
                                        if (fieldName === 'estado_actividad' && response.porcentaje_avance !== undefined) {
                                            var porcentajeCell = table.cell($td.closest('tr'), 11); // Columna 11 es porcentaje_avance
                                            porcentajeCell.data(response.porcentaje_avance).draw();
                                        }
                                        
                                        updateCardCounts();
                                        updateMonthlyCounts();
                                    } else {
                                        alert('Error: ' + response.message);
                                        cell.data(originalValue).draw();
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("AJAX error:", status, error);
                                    alert('Error en la comunicación con el servidor.');
                                    cell.data(originalValue).draw();
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
                    if (data[10] === 'CERRADA') {
                        ids.push(data[1]);
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
                                if (data[10] === 'CERRADA') {
                                    data[11] = '100';
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
        });
    </script>
</body>

</html>