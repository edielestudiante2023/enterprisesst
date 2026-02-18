<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Cronograma de Capacitación</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #eff6ff;
            --success: #16a34a;
            --success-light: #f0fdf4;
            --warning: #d97706;
            --warning-light: #fffbeb;
            --info: #0891b2;
            --info-light: #ecfeff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
        }

        body {
            font-size: 0.9rem;
            background-color: var(--gray-50);
            color: var(--gray-700);
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }

        .page-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 1.4rem;
        }

        .page-header .subtitle {
            opacity: 0.85;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .section-card {
            background: white;
            border-radius: 10px;
            border: 1px solid var(--gray-200);
            margin-bottom: 1.25rem;
            overflow: hidden;
            transition: box-shadow 0.2s;
        }

        .section-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        .section-header {
            padding: 0.85rem 1.25rem;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .section-header i {
            font-size: 1.1rem;
        }

        .section-header.header-blue {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .section-header.header-green {
            background-color: var(--success-light);
            color: var(--success);
        }

        .section-header.header-amber {
            background-color: var(--warning-light);
            color: var(--warning);
        }

        .section-header.header-cyan {
            background-color: var(--info-light);
            color: var(--info);
        }

        .section-body {
            padding: 1.25rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--gray-600);
            font-size: 0.82rem;
            margin-bottom: 0.35rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border-color: var(--gray-200);
            font-size: 0.88rem;
            padding: 0.5rem 0.75rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-action-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-action-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            color: white;
        }

        .btn-action-secondary {
            background: white;
            border: 1px solid var(--gray-200);
            color: var(--gray-600);
            font-weight: 600;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-action-secondary:hover {
            background: var(--gray-100);
            color: var(--gray-800);
        }

        .btn-ia {
            background: linear-gradient(135deg, var(--success) 0%, #15803d 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-ia:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
            color: white;
        }

        .action-bar {
            background: white;
            border-radius: 10px;
            border: 1px solid var(--gray-200);
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        /* Navbar */
        .top-navbar {
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .top-navbar img {
            height: 50px;
        }

        /* Select2 tweaks */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 8px !important;
            border-color: var(--gray-200) !important;
            font-size: 0.88rem !important;
            min-height: 38px !important;
        }

        /* Footer */
        .site-footer {
            background: white;
            border-top: 1px solid var(--gray-200);
            margin-top: 2rem;
            padding: 1.5rem 0;
            font-size: 0.82rem;
            color: var(--gray-600);
        }

        .social-icons img {
            height: 22px;
            width: 22px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .social-icons img:hover {
            opacity: 1;
        }

        /* Modal IA */
        .modal-ia .modal-header {
            background: linear-gradient(135deg, var(--success) 0%, #15803d 100%);
            color: white;
            border-bottom: none;
        }

        .modal-ia .modal-content {
            border-radius: 12px;
            border: none;
            overflow: hidden;
        }

        .modal-ia .modal-body {
            padding: 1.5rem;
        }

        /* Readonly field */
        .form-control[readonly] {
            background-color: var(--gray-100);
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top top-navbar">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center gap-3">
                <a href="https://dashboard.cycloidtalent.com/login">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo">
                </a>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo">
                </a>
                <a href="https://cycloidtalent.com/">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo">
                </a>
            </div>
            <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-grid-fill me-1"></i> Dashboard
            </a>
        </div>
    </nav>

    <!-- Espaciado para navbar fijo -->
    <div style="height: 90px;"></div>

    <!-- Contenido Principal -->
    <div class="container-lg py-4" style="max-width: 960px;">

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="bi bi-calendar-plus me-2"></i>Agregar Cronograma</h2>
                <div class="subtitle">Programa una nueva capacitación en el cronograma SST</div>
            </div>
            <button type="button" class="btn btn-ia" data-bs-toggle="modal" data-bs-target="#modalCrearConIA">
                <i class="bi bi-stars me-1"></i> Crear con IA
            </button>
        </div>

        <!-- Mensajes Flash -->
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-1"></i> <?= session()->getFlashdata('msg') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form action="<?= base_url('/addcronogCapacitacionPost') ?>" method="post">
            <div class="row g-4">
                <!-- Columna Izquierda -->
                <div class="col-lg-6">

                    <!-- Sección: Información General -->
                    <div class="section-card">
                        <div class="section-header header-blue">
                            <i class="bi bi-info-circle-fill"></i> Información General
                        </div>
                        <div class="section-body">
                            <div class="mb-3">
                                <label for="id_capacitacion" class="form-label">Capacitación</label>
                                <select name="id_capacitacion" id="id_capacitacion" class="form-select select2" required>
                                    <option value="" disabled selected>Selecciona una capacitación</option>
                                    <?php foreach ($capacitaciones as $capacitacion): ?>
                                        <option value="<?= htmlspecialchars($capacitacion['id_capacitacion']) ?>">
                                            <?= htmlspecialchars($capacitacion['capacitacion']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="id_cliente" class="form-label">Cliente</label>
                                <select name="id_cliente" id="id_cliente" class="form-select select2" required>
                                    <option value="" disabled selected>Selecciona un cliente</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?= $cliente['id_cliente'] ?>">
                                            <?= $cliente['nombre_cliente'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="perfil_de_asistentes" class="form-label">Perfil de Asistentes</label>
                                <select name="perfil_de_asistentes" id="perfil_de_asistentes" class="form-select" required>
                                    <option value="" disabled selected>Selecciona un perfil</option>
                                    <optgroup label="Roles Internos">
                                        <option value="TODOS">TODOS</option>
                                        <option value="MIEMBROS_COPASST">Miembros del COPASST</option>
                                        <option value="RESPONSABLE_SST">Responsable de SST</option>
                                        <option value="SUPERVISORES">Supervisores o Jefes de Área</option>
                                        <option value="TRABAJADORES_REPRESENTANTES">Trabajadores Representantes</option>
                                        <option value="MIEMBROS_COMITE_CONVIVENCIA">Miembros del Comité de Convivencia Laboral</option>
                                        <option value="RECURSOS_HUMANOS">Departamento de Recursos Humanos</option>
                                        <option value="PERSONAL_MANTENIMIENTO">Personal de Mantenimiento o Producción</option>
                                        <option value="BRIGADA">Brigada</option>
                                        <option value="TRABAJADORES_RIESGOS_CRITICOS">Trabajadores con Riesgos Críticos</option>
                                    </optgroup>
                                    <optgroup label="Roles Externos">
                                        <option value="ASESOR_SST">Asesor o Consultor en SST</option>
                                        <option value="AUDITOR_EXTERNO">Auditores Externos</option>
                                        <option value="CAPACITADOR_EXTERNO">Capacitadores Externos</option>
                                        <option value="CONTRATISTAS">Contratistas y Proveedores</option>
                                        <option value="INSPECTORES_GUBERNAMENTALES">Inspectores Gubernamentales</option>
                                        <option value="FISIOTERAPEUTAS_ERGONOMOS">Fisioterapeutas o Ergónomos</option>
                                        <option value="TECNICOS_ESPECIALIZADOS">Técnicos en Riesgos Especializados</option>
                                        <option value="BRIGADISTAS_EXTERNOS">Brigadistas o Personal de Emergencias Externo</option>
                                        <option value="REPRESENTANTES_ARL">Representantes de Aseguradoras (ARL)</option>
                                        <option value="AUDITORES_ISO">Auditores de Normas ISO</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="mb-0">
                                <label for="nombre_del_capacitador" class="form-label">Nombre del Capacitador</label>
                                <input type="text" name="nombre_del_capacitador" id="nombre_del_capacitador" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Programación -->
                    <div class="section-card">
                        <div class="section-header header-green">
                            <i class="bi bi-calendar-event-fill"></i> Programación
                        </div>
                        <div class="section-body">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="fecha_programada" class="form-label">Fecha Programada</label>
                                    <input type="date" name="fecha_programada" id="fecha_programada" class="form-control" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="fecha_de_realizacion" class="form-label">Fecha de Realización</label>
                                    <input type="date" name="fecha_de_realizacion" id="fecha_de_realizacion" class="form-control">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-0">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select name="estado" id="estado" class="form-select" required>
                                        <option value="" disabled selected>Selecciona</option>
                                        <option value="PROGRAMADA">PROGRAMADA</option>
                                        <option value="EJECUTADA">EJECUTADA</option>
                                        <option value="CANCELADA POR EL CLIENTE">CANCELADA POR EL CLIENTE</option>
                                        <option value="REPROGRAMADA">REPROGRAMADA</option>
                                    </select>
                                </div>
                                <div class="col-6 mb-0">
                                    <label for="horas_de_duracion_de_la_capacitacion" class="form-label">Horas de Duración</label>
                                    <input type="number" name="horas_de_duracion_de_la_capacitacion" id="horas_de_duracion_de_la_capacitacion" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha -->
                <div class="col-lg-6">

                    <!-- Sección: Ejecución -->
                    <div class="section-card">
                        <div class="section-header header-amber">
                            <i class="bi bi-clipboard-check-fill"></i> Ejecución
                        </div>
                        <div class="section-body">
                            <div class="mb-3">
                                <label for="indicador_de_realizacion_de_la_capacitacion" class="form-label">Indicador de Realización</label>
                                <select name="indicador_de_realizacion_de_la_capacitacion" id="indicador_de_realizacion_de_la_capacitacion" class="form-select">
                                    <option value="" disabled selected>Selecciona un indicador</option>
                                    <option value="SE EJECUTO EN LA FECHA O ANTES DE LA FECHA">SE EJECUTÓ EN LA FECHA O ANTES</option>
                                    <option value="SE EJECUTO DESPUES DE LA FECHA ACORDADA A CAUSA DEL CLIENTE">SE EJECUTÓ DESPUÉS - CAUSA CLIENTE</option>
                                    <option value="DECLINADA POR EL CLIENTE">DECLINADA POR EL CLIENTE</option>
                                    <option value="NO HAY JUSTIFICACION PORQUE NO SE REALIZÓ">SIN JUSTIFICACIÓN</option>
                                    <option value="SE EJECUTO DESPUES DE LA FECHA POR CAUSA DEL CAPACITADOR">SE EJECUTÓ DESPUÉS - CAUSA CAPACITADOR</option>
                                </select>
                            </div>
                            <div class="mb-0">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea name="observaciones" id="observaciones" class="form-control" rows="3" placeholder="Notas adicionales sobre la capacitación..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Métricas -->
                    <div class="section-card">
                        <div class="section-header header-cyan">
                            <i class="bi bi-bar-chart-fill"></i> Métricas de Asistencia
                        </div>
                        <div class="section-body">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="numero_de_asistentes_a_capacitacion" class="form-label">N° Asistentes</label>
                                    <input type="number" name="numero_de_asistentes_a_capacitacion" id="numero_de_asistentes_a_capacitacion" class="form-control" min="0">
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="numero_total_de_personas_programadas" class="form-label">N° Programados</label>
                                    <input type="number" name="numero_total_de_personas_programadas" id="numero_total_de_personas_programadas" class="form-control" min="0">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="porcentaje_cobertura" class="form-label">Porcentaje de Cobertura</label>
                                <div class="input-group">
                                    <input type="text" name="porcentaje_cobertura" id="porcentaje_cobertura" class="form-control" readonly>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-0">
                                    <label for="numero_de_personas_evaluadas" class="form-label">N° Evaluados</label>
                                    <input type="number" name="numero_de_personas_evaluadas" id="numero_de_personas_evaluadas" class="form-control" min="0">
                                </div>
                                <div class="col-6 mb-0">
                                    <label for="promedio_de_calificaciones" class="form-label">Promedio Calificaciones</label>
                                    <input type="number" step="0.01" name="promedio_de_calificaciones" id="promedio_de_calificaciones" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra de Acciones -->
            <div class="action-bar mt-3">
                <a href="<?= base_url('/listcronogCapacitacion') ?>" class="btn btn-action-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-action-primary">
                    <i class="bi bi-check-lg me-1"></i> Agregar Cronograma
                </button>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer class="site-footer text-center">
        <div class="container">
            <p class="fw-bold mb-1">Cycloid Talent SAS</p>
            <p class="mb-1">Todos los derechos reservados &copy; 2024 &middot; NIT: 901.653.912</p>
            <p class="mb-2">
                <a href="https://cycloidtalent.com/" target="_blank">cycloidtalent.com</a>
            </p>
            <div class="social-icons d-flex justify-content-center gap-3">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok">
                </a>
            </div>
        </div>
    </footer>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Modal Crear con IA -->
    <div class="modal fade modal-ia" id="modalCrearConIA" tabindex="-1" aria-labelledby="modalCrearConIALabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearConIALabel">
                        <i class="bi bi-stars me-2"></i>Crear Capacitación con IA
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Escribe un tema y la IA generará la capacitación completa con objetivo, perfil de asistentes y la agregará al cronograma.</p>
                    <div class="mb-3">
                        <label for="ia_tema" class="form-label fw-bold">Tema de la capacitación</label>
                        <input type="text" id="ia_tema" class="form-control" placeholder="Ej: manejo de extintores, riesgo químico, primeros auxilios...">
                    </div>
                    <div class="mb-3">
                        <label for="ia_cliente" class="form-label fw-bold">Cliente</label>
                        <select id="ia_cliente" class="form-select">
                            <option value="" disabled selected>Selecciona un cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= $cliente['id_cliente'] ?>"><?= esc($cliente['nombre_cliente']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="ia_fecha" class="form-label fw-bold">Fecha programada</label>
                        <input type="date" id="ia_fecha" class="form-control">
                    </div>
                    <div id="ia_resultado" class="d-none">
                        <hr>
                        <div class="alert alert-success mb-0" id="ia_resultado_contenido"></div>
                    </div>
                    <div id="ia_error" class="d-none">
                        <div class="alert alert-danger mb-0" id="ia_error_contenido"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-action-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-ia" id="btnGenerarIA" onclick="generarConIA()">
                        <span id="btnGenerarIA_text"><i class="bi bi-stars me-1"></i> Generar con IA</span>
                        <span id="btnGenerarIA_spinner" class="d-none">
                            <span class="spinner-border spinner-border-sm" role="status"></span> Generando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Select2
            $('#id_capacitacion').select2({
                placeholder: "Buscar y seleccionar capacitación",
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });

            $('#id_cliente').select2({
                placeholder: "Buscar y seleccionar cliente",
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });

            // Select2 para cliente en modal IA (dropdownParent para que funcione dentro del modal)
            $('#ia_cliente').select2({
                placeholder: "Buscar y seleccionar cliente",
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5',
                dropdownParent: $('#modalCrearConIA')
            });

            // Auto-calcular porcentaje de cobertura
            $('#numero_de_asistentes_a_capacitacion, #numero_total_de_personas_programadas').on('input', function() {
                var asistentes = parseInt($('#numero_de_asistentes_a_capacitacion').val()) || 0;
                var programados = parseInt($('#numero_total_de_personas_programadas').val()) || 0;
                var porcentaje = programados > 0 ? ((asistentes / programados) * 100).toFixed(1) : '0';
                $('#porcentaje_cobertura').val(porcentaje);
            });
        });

        function generarConIA() {
            var tema = $('#ia_tema').val();
            var idCliente = $('#ia_cliente').val();
            var fecha = $('#ia_fecha').val();

            if (!tema || !idCliente || !fecha) {
                $('#ia_error_contenido').text('Por favor completa todos los campos.');
                $('#ia_error').removeClass('d-none');
                return;
            }

            // UI: loading
            $('#btnGenerarIA').prop('disabled', true);
            $('#btnGenerarIA_text').addClass('d-none');
            $('#btnGenerarIA_spinner').removeClass('d-none');
            $('#ia_resultado').addClass('d-none');
            $('#ia_error').addClass('d-none');

            $.ajax({
                url: '<?= base_url("/cronogCapacitacion/generarConIA") ?>',
                type: 'POST',
                data: { tema: tema, id_cliente: idCliente, fecha_programada: fecha },
                dataType: 'json',
                success: function(response) {
                    $('#btnGenerarIA').prop('disabled', false);
                    $('#btnGenerarIA_text').removeClass('d-none');
                    $('#btnGenerarIA_spinner').addClass('d-none');

                    if (response.success) {
                        var d = response.data;
                        var html = '<strong>Capacitación:</strong> ' + d.capacitacion + '<br>';
                        html += '<strong>Objetivo:</strong> ' + d.objetivo + '<br>';
                        html += '<strong>Perfil:</strong> ' + d.perfil + '<br>';
                        html += '<strong>Horas:</strong> ' + d.horas + '<br>';
                        html += d.nueva ? '<em>Se creó nueva capacitación en el catálogo.</em>' : '<em>Ya existía en el catálogo.</em>';
                        $('#ia_resultado_contenido').html(html);
                        $('#ia_resultado').removeClass('d-none');

                        setTimeout(function() {
                            window.location.href = '<?= base_url("/listcronogCapacitacion") ?>';
                        }, 3000);
                    } else {
                        $('#ia_error_contenido').text(response.message);
                        $('#ia_error').removeClass('d-none');
                    }
                },
                error: function() {
                    $('#btnGenerarIA').prop('disabled', false);
                    $('#btnGenerarIA_text').removeClass('d-none');
                    $('#btnGenerarIA_spinner').addClass('d-none');
                    $('#ia_error_contenido').text('Error de conexión. Intenta de nuevo.');
                    $('#ia_error').removeClass('d-none');
                }
            });
        }
    </script>
</body>

</html>