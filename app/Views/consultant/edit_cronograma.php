<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cronograma de Capacitación</title>
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
        <div class="page-header">
            <h2><i class="bi bi-pencil-square me-2"></i>Editar Cronograma</h2>
            <div class="subtitle">Modifica los datos de la capacitación programada #<?= esc($cronograma['id_cronograma_capacitacion']) ?></div>
        </div>

        <!-- Mensajes Flash -->
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-1"></i> <?= session()->getFlashdata('msg') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form action="<?= base_url('/editcronogCapacitacionPost/' . $cronograma['id_cronograma_capacitacion']) ?>" method="post">
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
                                    <?php foreach ($capacitaciones as $capacitacion): ?>
                                        <option value="<?= $capacitacion['id_capacitacion'] ?>" <?= ($cronograma['id_capacitacion'] == $capacitacion['id_capacitacion']) ? 'selected' : '' ?>>
                                            <?= $capacitacion['capacitacion'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="id_cliente" class="form-label">Cliente</label>
                                <select name="id_cliente" id="id_cliente" class="form-select select2" required>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?= $cliente['id_cliente'] ?>" <?= ($cronograma['id_cliente'] == $cliente['id_cliente']) ? 'selected' : '' ?>>
                                            <?= $cliente['nombre_cliente'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="perfil_de_asistentes" class="form-label">Perfil de Asistentes</label>
                                <select name="perfil_de_asistentes" id="perfil_de_asistentes" class="form-select" required>
                                    <option value="" disabled>Selecciona un perfil</option>
                                    <optgroup label="Roles Internos">
                                        <option value="TODOS" <?= ($cronograma['perfil_de_asistentes'] == 'TODOS') ? 'selected' : '' ?>>TODOS</option>
                                        <option value="MIEMBROS_COPASST" <?= ($cronograma['perfil_de_asistentes'] == 'MIEMBROS_COPASST') ? 'selected' : '' ?>>Miembros del COPASST</option>
                                        <option value="RESPONSABLE_SST" <?= ($cronograma['perfil_de_asistentes'] == 'RESPONSABLE_SST') ? 'selected' : '' ?>>Responsable de SST</option>
                                        <option value="SUPERVISORES" <?= ($cronograma['perfil_de_asistentes'] == 'SUPERVISORES') ? 'selected' : '' ?>>Supervisores o Jefes de Área</option>
                                        <option value="TRABAJADORES_REPRESENTANTES" <?= ($cronograma['perfil_de_asistentes'] == 'TRABAJADORES_REPRESENTANTES') ? 'selected' : '' ?>>Trabajadores Representantes</option>
                                        <option value="MIEMBROS_COMITE_CONVIVENCIA" <?= ($cronograma['perfil_de_asistentes'] == 'MIEMBROS_COMITE_CONVIVENCIA') ? 'selected' : '' ?>>Miembros del Comité de Convivencia Laboral</option>
                                        <option value="RECURSOS_HUMANOS" <?= ($cronograma['perfil_de_asistentes'] == 'RECURSOS_HUMANOS') ? 'selected' : '' ?>>Departamento de Recursos Humanos</option>
                                        <option value="PERSONAL_MANTENIMIENTO" <?= ($cronograma['perfil_de_asistentes'] == 'PERSONAL_MANTENIMIENTO') ? 'selected' : '' ?>>Personal de Mantenimiento o Producción</option>
                                        <option value="BRIGADA" <?= ($cronograma['perfil_de_asistentes'] == 'BRIGADA') ? 'selected' : '' ?>>Brigada</option>
                                        <option value="TRABAJADORES_RIESGOS_CRITICOS" <?= ($cronograma['perfil_de_asistentes'] == 'TRABAJADORES_RIESGOS_CRITICOS') ? 'selected' : '' ?>>Trabajadores con Riesgos Críticos</option>
                                    </optgroup>
                                    <optgroup label="Roles Externos">
                                        <option value="ASESOR_SST" <?= ($cronograma['perfil_de_asistentes'] == 'ASESOR_SST') ? 'selected' : '' ?>>Asesor o Consultor en SST</option>
                                        <option value="AUDITOR_EXTERNO" <?= ($cronograma['perfil_de_asistentes'] == 'AUDITOR_EXTERNO') ? 'selected' : '' ?>>Auditores Externos</option>
                                        <option value="CAPACITADOR_EXTERNO" <?= ($cronograma['perfil_de_asistentes'] == 'CAPACITADOR_EXTERNO') ? 'selected' : '' ?>>Capacitadores Externos</option>
                                        <option value="CONTRATISTAS" <?= ($cronograma['perfil_de_asistentes'] == 'CONTRATISTAS') ? 'selected' : '' ?>>Contratistas y Proveedores</option>
                                        <option value="INSPECTORES_GUBERNAMENTALES" <?= ($cronograma['perfil_de_asistentes'] == 'INSPECTORES_GUBERNAMENTALES') ? 'selected' : '' ?>>Inspectores Gubernamentales</option>
                                        <option value="FISIOTERAPEUTAS_ERGONOMOS" <?= ($cronograma['perfil_de_asistentes'] == 'FISIOTERAPEUTAS_ERGONOMOS') ? 'selected' : '' ?>>Fisioterapeutas o Ergónomos</option>
                                        <option value="TECNICOS_ESPECIALIZADOS" <?= ($cronograma['perfil_de_asistentes'] == 'TECNICOS_ESPECIALIZADOS') ? 'selected' : '' ?>>Técnicos en Riesgos Especializados</option>
                                        <option value="BRIGADISTAS_EXTERNOS" <?= ($cronograma['perfil_de_asistentes'] == 'BRIGADISTAS_EXTERNOS') ? 'selected' : '' ?>>Brigadistas o Personal de Emergencias Externo</option>
                                        <option value="REPRESENTANTES_ARL" <?= ($cronograma['perfil_de_asistentes'] == 'REPRESENTANTES_ARL') ? 'selected' : '' ?>>Representantes de Aseguradoras (ARL)</option>
                                        <option value="AUDITORES_ISO" <?= ($cronograma['perfil_de_asistentes'] == 'AUDITORES_ISO') ? 'selected' : '' ?>>Auditores de Normas ISO</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="mb-0">
                                <label for="nombre_del_capacitador" class="form-label">Nombre del Capacitador</label>
                                <input type="text" name="nombre_del_capacitador" id="nombre_del_capacitador" class="form-control" value="<?= esc($cronograma['nombre_del_capacitador']) ?>" required>
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
                                    <input type="date" name="fecha_programada" id="fecha_programada" class="form-control" value="<?= esc($cronograma['fecha_programada']) ?>" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="fecha_de_realizacion" class="form-label">Fecha de Realización</label>
                                    <input type="date" name="fecha_de_realizacion" id="fecha_de_realizacion" class="form-control" value="<?= esc($cronograma['fecha_de_realizacion']) ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-0">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select name="estado" id="estado" class="form-select" required>
                                        <option value="PROGRAMADA" <?= ($cronograma['estado'] == 'PROGRAMADA') ? 'selected' : '' ?>>PROGRAMADA</option>
                                        <option value="EJECUTADA" <?= ($cronograma['estado'] == 'EJECUTADA') ? 'selected' : '' ?>>EJECUTADA</option>
                                        <option value="CANCELADA POR EL CLIENTE" <?= ($cronograma['estado'] == 'CANCELADA POR EL CLIENTE') ? 'selected' : '' ?>>CANCELADA POR EL CLIENTE</option>
                                        <option value="REPROGRAMADA" <?= ($cronograma['estado'] == 'REPROGRAMADA') ? 'selected' : '' ?>>REPROGRAMADA</option>
                                    </select>
                                </div>
                                <div class="col-6 mb-0">
                                    <label for="horas_de_duracion_de_la_capacitacion" class="form-label">Horas de Duración</label>
                                    <input type="number" name="horas_de_duracion_de_la_capacitacion" id="horas_de_duracion_de_la_capacitacion" class="form-control" value="<?= esc($cronograma['horas_de_duracion_de_la_capacitacion']) ?>">
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
                                    <option value="SE EJECUTO EN LA FECHA O ANTES DE LA FECHA" <?= ($cronograma['indicador_de_realizacion_de_la_capacitacion'] == 'SE EJECUTO EN LA FECHA O ANTES DE LA FECHA') ? 'selected' : '' ?>>SE EJECUTÓ EN LA FECHA O ANTES</option>
                                    <option value="SE EJECUTO DESPUES DE LA FECHA ACORDADA A CAUSA DEL CLIENTE" <?= ($cronograma['indicador_de_realizacion_de_la_capacitacion'] == 'SE EJECUTO DESPUES DE LA FECHA ACORDADA A CAUSA DEL CLIENTE') ? 'selected' : '' ?>>SE EJECUTÓ DESPUÉS - CAUSA CLIENTE</option>
                                    <option value="DECLINADA POR EL CLIENTE" <?= ($cronograma['indicador_de_realizacion_de_la_capacitacion'] == 'DECLINADA POR EL CLIENTE') ? 'selected' : '' ?>>DECLINADA POR EL CLIENTE</option>
                                    <option value="NO HAY JUSTIFICACION PORQUE NO SE REALIZÓ" <?= ($cronograma['indicador_de_realizacion_de_la_capacitacion'] == 'NO HAY JUSTIFICACION PORQUE NO SE REALIZÓ') ? 'selected' : '' ?>>SIN JUSTIFICACIÓN</option>
                                    <option value="SE EJECUTO DESPUES DE LA FECHA POR CAUSA DEL CAPACITADOR" <?= ($cronograma['indicador_de_realizacion_de_la_capacitacion'] == 'SE EJECUTO DESPUES DE LA FECHA POR CAUSA DEL CAPACITADOR') ? 'selected' : '' ?>>SE EJECUTÓ DESPUÉS - CAUSA CAPACITADOR</option>
                                </select>
                            </div>
                            <div class="mb-0">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea name="observaciones" id="observaciones" class="form-control" rows="3" placeholder="Notas adicionales sobre la capacitación..."><?= esc($cronograma['observaciones']) ?></textarea>
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
                                    <input type="number" name="numero_de_asistentes_a_capacitacion" id="numero_de_asistentes_a_capacitacion" class="form-control" value="<?= esc($cronograma['numero_de_asistentes_a_capacitacion']) ?>" min="0">
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="numero_total_de_personas_programadas" class="form-label">N° Programados</label>
                                    <input type="number" name="numero_total_de_personas_programadas" id="numero_total_de_personas_programadas" class="form-control" value="<?= esc($cronograma['numero_total_de_personas_programadas']) ?>" min="0">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="porcentaje_cobertura" class="form-label">Porcentaje de Cobertura</label>
                                <div class="input-group">
                                    <input type="text" name="porcentaje_cobertura" id="porcentaje_cobertura" class="form-control" value="<?= esc($cronograma['porcentaje_cobertura']) ?>" readonly>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-0">
                                    <label for="numero_de_personas_evaluadas" class="form-label">N° Evaluados</label>
                                    <input type="number" name="numero_de_personas_evaluadas" id="numero_de_personas_evaluadas" class="form-control" value="<?= esc($cronograma['numero_de_personas_evaluadas']) ?>" min="0">
                                </div>
                                <div class="col-6 mb-0">
                                    <label for="promedio_de_calificaciones" class="form-label">Promedio Calificaciones</label>
                                    <input type="number" step="0.01" name="promedio_de_calificaciones" id="promedio_de_calificaciones" class="form-control" value="<?= esc($cronograma['promedio_de_calificaciones']) ?>">
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
                    <i class="bi bi-check-lg me-1"></i> Guardar Cambios
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

            // Auto-calcular porcentaje de cobertura
            $('#numero_de_asistentes_a_capacitacion, #numero_total_de_personas_programadas').on('input', function() {
                var asistentes = parseInt($('#numero_de_asistentes_a_capacitacion').val()) || 0;
                var programados = parseInt($('#numero_total_de_personas_programadas').val()) || 0;
                var porcentaje = programados > 0 ? ((asistentes / programados) * 100).toFixed(1) : '0';
                $('#porcentaje_cobertura').val(porcentaje);
            });
        });
    </script>
</body>

</html>