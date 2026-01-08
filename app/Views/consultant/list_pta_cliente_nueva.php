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
    </style>
</head>

<body>
    <div class="container-fluid">
        <!-- Enlace a Dashboard -->
        <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm mb-3">Ir a DashBoard</a>

        <!-- Tarjetas de conteo superiores -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Activas</h5>
                        <p class="card-text" id="countActivas">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h5 class="card-title">Cerradas</h5>
                        <p class="card-text" id="countCerradas">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Gestionando</h5>
                        <p class="card-text" id="countGestionando">0</p>
                    </div>
                </div>
            </div>
            <!-- Tarjeta para total de actividades -->
            <div class="col-md-3">
                <div class="card text-white bg-secondary">
                    <div class="card-body">
                        <h5 class="card-title">Total</h5>
                        <p class="card-text" id="countTotal">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas mensuales -->
        <div class="row mb-4">
            <!-- Cada tarjeta ocupa 1 columna en md y 6 en xs -->
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Enero</h6>
                        <p class="card-text text-center" id="countEnero">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Febrero</h6>
                        <p class="card-text text-center" id="countFebrero">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Marzo</h6>
                        <p class="card-text text-center" id="countMarzo">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Abril</h6>
                        <p class="card-text text-center" id="countAbril">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Mayo</h6>
                        <p class="card-text text-center" id="countMayo">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Junio</h6>
                        <p class="card-text text-center" id="countJunio">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Julio</h6>
                        <p class="card-text text-center" id="countJulio">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Agosto</h6>
                        <p class="card-text text-center" id="countAgosto">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Sept.</h6>
                        <p class="card-text text-center" id="countSeptiembre">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Oct.</h6>
                        <p class="card-text text-center" id="countOctubre">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info">
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-0">Nov.</h6>
                        <p class="card-text text-center" id="countNoviembre">0</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="card text-white bg-info">
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
                
                <!-- Sección Principal -->
                <div class="filter-section">
                    <h6><i class="fas fa-filter"></i> Filtros Principales</h6>
                    <div class="row mb-3">
                        <!-- Cliente (Campo requerido) -->
                        <div class="col-lg-6">
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
                        
                        <!-- Estado de Actividad -->
                        <div class="col-lg-3">
                            <label for="estado" class="form-label">
                                <i class="fas fa-tasks"></i> Estado de Actividad
                            </label>
                            <select name="estado" id="estado" class="form-select">
                                <option value="">Todas</option>
                                <option value="ABIERTA" <?= (service('request')->getGet('estado') == 'ABIERTA') ? 'selected' : '' ?>>ABIERTA</option>
                                <option value="CERRADA" <?= (service('request')->getGet('estado') == 'CERRADA') ? 'selected' : '' ?>>CERRADA</option>
                                <option value="GESTIONANDO" <?= (service('request')->getGet('estado') == 'GESTIONANDO') ? 'selected' : '' ?>>GESTIONANDO</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Sección Fechas -->
                <div class="filter-section">
                    <h6><i class="fas fa-calendar-alt"></i> Filtros de Fecha</h6>
                    <div class="row mb-3">
                        <!-- Fechas Manuales -->
                        <div class="col-lg-6">
                            <div class="date-range-group">
                                <small class="text-muted mb-2 d-block">
                                    <i class="fas fa-info-circle"></i> Rango manual de fechas
                                </small>
                                <div class="row">
                                    <div class="col-6">
                                        <label for="fecha_desde" class="form-label">
                                            <i class="fas fa-calendar-plus"></i> Fecha Desde
                                        </label>
                                        <input type="date" name="fecha_desde" id="fecha_desde" 
                                               class="form-control" 
                                               value="<?= esc(service('request')->getGet('fecha_desde')) ?>">
                                    </div>
                                    <div class="col-6">
                                        <label for="fecha_hasta" class="form-label">
                                            <i class="fas fa-calendar-minus"></i> Fecha Hasta
                                        </label>
                                        <input type="date" name="fecha_hasta" id="fecha_hasta" 
                                               class="form-control" 
                                               value="<?= esc(service('request')->getGet('fecha_hasta')) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Filtros Rápidos -->
                        <div class="col-lg-6">
                            <div class="quick-filters">
                                <small class="text-muted mb-2 d-block">
                                    <i class="fas fa-magic"></i> Filtros rápidos
                                </small>
                                <div class="row">
                                    <div class="col-6">
                                        <label for="anioSeleccionado" class="form-label">
                                            <i class="fas fa-calendar"></i> Año
                                        </label>
                                        <select id="anioSeleccionado" class="form-select">
                                            <option value="">Seleccione año</option>
                                            <?php 
                                            $currentYear = date('Y');
                                            for($i = $currentYear + 1; $i >= 2020; $i--): ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label for="mesSeleccionado" class="form-label">
                                            <i class="fas fa-calendar-week"></i> Período
                                        </label>
                                        <select id="mesSeleccionado" class="form-select">
                                            <option value="">Seleccione período</option>
                                            <option value="all">Todo el Año</option>
                                            <option value="1">Enero</option>
                                            <option value="2">Febrero</option>
                                            <option value="3">Marzo</option>
                                            <option value="4">Abril</option>
                                            <option value="5">Mayo</option>
                                            <option value="6">Junio</option>
                                            <option value="7">Julio</option>
                                            <option value="8">Agosto</option>
                                            <option value="9">Septiembre</option>
                                            <option value="10">Octubre</option>
                                            <option value="11">Noviembre</option>
                                            <option value="12">Diciembre</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
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

            // Al cambiar el mes, se asignan las fechas correspondientes
            $('#mesSeleccionado').on('change', function() {
                var valor = $(this).val();
                var valorAnio = $('#anioSeleccionado').val();
                
                // Si no hay mes seleccionado, no hacer nada
                if (!valor) return;
                
                // Si hay año seleccionado, usar ese; si no, mostrar alerta y salir
                if (!valorAnio) {
                    alert('Primero debe seleccionar un año.');
                    $(this).val('');
                    return;
                }
                
                var anio = parseInt(valorAnio);
                var primerDia, ultimoDia;

                if (valor === "all") {
                    // Todo el año: desde el 1 de enero hasta el 31 de diciembre
                    primerDia = new Date(anio, 0, 1);
                    ultimoDia = new Date(anio, 11, 31);
                } else {
                    var mes = parseInt(valor);
                    // Primer día del mes
                    primerDia = new Date(anio, mes - 1, 1);
                    // Último día del mes (crea una fecha del mes siguiente y resta un día)
                    ultimoDia = new Date(anio, mes, 0);
                }

                // Función para formatear la fecha a YYYY-MM-DD
                function formatearFecha(fecha) {
                    var dia = ("0" + fecha.getDate()).slice(-2);
                    var mesFormateado = ("0" + (fecha.getMonth() + 1)).slice(-2);
                    return fecha.getFullYear() + '-' + mesFormateado + '-' + dia;
                }

                $('#fecha_desde').val(formatearFecha(primerDia));
                $('#fecha_hasta').val(formatearFecha(ultimoDia));
            });

            // Al cambiar el año, si hay un mes seleccionado, actualizar las fechas
            $('#anioSeleccionado').on('change', function() {
                var valorMes = $('#mesSeleccionado').val();
                if (valorMes) {
                    $('#mesSeleccionado').trigger('change');
                }
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
                $('#anioSeleccionado').val('');
                $('#mesSeleccionado').val('');
                $('#estado').val(''); // También limpiar el estado para mostrar TODOS los registros
                
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
                var anioSeleccionado = $('#anioSeleccionado').val();
                var mesSeleccionado = $('#mesSeleccionado').val();
                
                // Validar que se haya seleccionado un cliente
                if (!cliente) {
                    showAlert('Debe seleccionar un Cliente.', 'error');
                    e.preventDefault();
                    return false;
                }
                
                // Validar filtros de búsqueda
                var esViaTodos = $(this).data('via-todos') === true;
                var tieneFechas = fechaDesde && fechaHasta;
                var tieneFiltroRapido = anioSeleccionado || mesSeleccionado;
                var tieneEstado = $('#estado').val();
                
                // PERMITIR búsqueda si:
                // 1. Viene del botón "Ver Todos"
                // 2. Tiene fechas completas (manual o rápido)
                // 3. Tiene solo cliente + estado (sin fechas)
                var puedeEjecutar = esViaTodos || tieneFechas || tieneFiltroRapido || tieneEstado;
                
                if (!puedeEjecutar) {
                    showAlert('Debe especificar al menos uno de los siguientes filtros:\n• Rango manual de fechas (Fecha Desde y Fecha Hasta)\n• Filtros rápidos (Año y/o Período)\n• Estado de Actividad\n• O hacer clic en "Ver Todos" para mostrar todos los registros', 'warning');
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
                    $('#countActivas').text(countActivas);
                    $('#countCerradas').text(countCerradas);
                    $('#countGestionando').text(countGestionando);
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
                        var options = ["ABIERTA", "CERRADA", "GESTIONANDO"];
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