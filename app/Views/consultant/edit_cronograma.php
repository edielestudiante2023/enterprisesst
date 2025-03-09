<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cronograma de Capacitación</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css">
    <style>
        body {
            font-size: 0.9rem;
            background-color: #f8f9fa;
        }

        h2 {
            color: #495057;
        }

        .form-control {
            background-color: #ffffff;
            border-color: #ced4da;
            color: #495057;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        footer {
            background-color: white;
            padding: 20px 0;
            border-top: 1px solid #B0BEC5;
            margin-top: 40px;
            color: #3A3F51;
            font-size: 14px;
            text-align: center;
        }

        footer a {
            color: #007BFF;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        .social-icons img {
            height: 24px;
            width: 24px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
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

            <!-- Botón Dashboard -->
            <div class="ms-auto text-center">
                <h6 class="mb-1">Ir a Dashboard</h6>
                <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm">Ir a DashBoard</a>
            </div>
        </div>
    </nav>

    <!-- Espaciado para el navbar fijo -->
    <div style="height: 160px;"></div>

    <!-- Contenido Principal -->
    <div class="container my-5">
        <h2 class="text-center mb-4">Editar Cronograma de Capacitación</h2>

        <!-- Mensajes Flash -->
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-info">
                <?= session()->getFlashdata('msg') ?>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form action="<?= base_url('/editcronogCapacitacionPost/' . $cronograma['id_cronograma_capacitacion']) ?>" method="post">
            <div class="row">
                <!-- Columna Izquierda -->
                <div class="col-md-6">
                    <!-- Capacitación -->
                    <div class="mb-3">
                        <label for="id_capacitacion" class="form-label">Capacitación</label>
                        <select name="id_capacitacion" id="id_capacitacion" class="form-select" required>
                            <?php foreach ($capacitaciones as $capacitacion): ?>
                                <option value="<?= $capacitacion['id_capacitacion'] ?>" <?= ($cronograma['id_capacitacion'] == $capacitacion['id_capacitacion']) ? 'selected' : '' ?>>
                                    <?= $capacitacion['capacitacion'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Cliente -->
                    <div class="mb-3">
                        <label for="id_cliente" class="form-label">Cliente</label>
                        <select name="id_cliente" id="id_cliente" class="form-select" required>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= $cliente['id_cliente'] ?>" <?= ($cronograma['id_cliente'] == $cliente['id_cliente']) ? 'selected' : '' ?>>
                                    <?= $cliente['nombre_cliente'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Fecha Programada -->
                    <div class="mb-3">
                        <label for="fecha_programada" class="form-label">Fecha Programada</label>
                        <input type="date" name="fecha_programada" id="fecha_programada" class="form-control" value="<?= esc($cronograma['fecha_programada']) ?>" required>
                    </div>

                    <!-- Fecha de Realización -->
                    <div class="mb-3">
                        <label for="fecha_de_realizacion" class="form-label">Fecha de Realización</label>
                        <input type="date" name="fecha_de_realizacion" id="fecha_de_realizacion" class="form-control" value="<?= esc($cronograma['fecha_de_realizacion']) ?>">
                    </div>

                    <!-- Estado -->
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select name="estado" id="estado" class="form-select" required>
                            <option value="PROGRAMADA" <?= ($cronograma['estado'] == 'PROGRAMADA') ? 'selected' : '' ?>>PROGRAMADA</option>
                            <option value="EJECUTADA" <?= ($cronograma['estado'] == 'EJECUTADA') ? 'selected' : '' ?>>EJECUTADA</option>
                            <option value="CANCELADA POR EL CLIENTE" <?= ($cronograma['estado'] == 'CANCELADA POR EL CLIENTE') ? 'selected' : '' ?>>CANCELADA POR EL CLIENTE</option>
                            <option value="REPROGRAMADA" <?= ($cronograma['estado'] == 'REPROGRAMADA') ? 'selected' : '' ?>>REPROGRAMADA</option>
                        </select>
                    </div>

                    <!-- Perfil de Asistentes -->
                    <div class="mb-3">
                        <label for="perfil_de_asistentes" class="form-label">Perfil de Asistentes</label>
                        <select name="perfil_de_asistentes" id="perfil_de_asistentes" class="form-select" required>
                            <option value="" disabled>Selecciona un perfil</option>

                            <!-- Roles Internos -->
                            <option value="TODOS" <?= ($cronograma['perfil_de_asistentes'] == 'TODOS') ? 'selected' : '' ?>>TODOS</option>
                            <option value="MIEMBROS_COPASST" <?= ($cronograma['perfil_de_asistentes'] == 'MIEMBROS_COPASST') ? 'selected' : '' ?>>Miembros del COPASST</option>
                            <option value="RESPONSABLE_SST" <?= ($cronograma['perfil_de_asistentes'] == 'RESPONSABLE_SST') ? 'selected' : '' ?>>Responsable de SST</option>
                            <option value="SUPERVISORES" <?= ($cronograma['perfil_de_asistentes'] == 'SUPERVISORES') ? 'selected' : '' ?>>Supervisores o Jefes de Área</option>
                            <option value="TRABAJADORES_REPRESENTANTES" <?= ($cronograma['perfil_de_asistentes'] == 'TRABAJADORES_REPRESENTANTES') ? 'selected' : '' ?>>Trabajadores Representantes</option>
                            <option value="MIEMBROS_COMITE_CONVIVENCIA" <?= ($cronograma['perfil_de_asistentes'] == 'MIEMBROS_COMITE_CONVIVENCIA') ? 'selected' : '' ?>>Miembros del Comité de Convivencia Laboral</option>
                            <option value="RECURSOS_HUMANOS" <?= ($cronograma['perfil_de_asistentes'] == 'RECURSOS_HUMANOS') ? 'selected' : '' ?>>Departamento de Recursos Humanos</option>
                            <option value="PERSONAL_MANTENIMIENTO" <?= ($cronograma['perfil_de_asistentes'] == 'PERSONAL_MANTENIMIENTO') ? 'selected' : '' ?>>Personal de Mantenimiento o Producción</option>
                            <option value="ENCARGADO_AMBIENTAL" <?= ($cronograma['perfil_de_asistentes'] == 'ENCARGADO_AMBIENTAL') ? 'selected' : '' ?>>Encargado de Gestión Ambiental</option>
                            <option value="TRABAJADORES_RIESGOS_CRITICOS" <?= ($cronograma['perfil_de_asistentes'] == 'TRABAJADORES_RIESGOS_CRITICOS') ? 'selected' : '' ?>>Trabajadores con Riesgos Críticos</option>

                            <!-- Roles Externos -->
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

                            <!-- Opción para Todos -->
                            <option value="TODOS" <?= ($cronograma['perfil_de_asistentes'] == 'TODOS') ? 'selected' : '' ?>>TODOS</option>
                        </select>

                    </div>
                </div>

                <!-- Columna Derecha -->
                <div class="col-md-6">
                    <!-- Nombre del Capacitador -->
                    <div class="mb-3">
                        <label for="nombre_del_capacitador" class="form-label">Nombre del Capacitador</label>
                        <input type="text" name="nombre_del_capacitador" id="nombre_del_capacitador" class="form-control" value="<?= esc($cronograma['nombre_del_capacitador']) ?>" required>
                    </div>

                    <!-- Horas de Duración -->
                    <div class="mb-3">
                        <label for="horas_de_duracion_de_la_capacitacion" class="form-label">Horas de Duración</label>
                        <input type="number" name="horas_de_duracion_de_la_capacitacion" id="horas_de_duracion_de_la_capacitacion" class="form-control" value="<?= esc($cronograma['horas_de_duracion_de_la_capacitacion']) ?>" >
                    </div>

                    <!-- Indicador de Realización -->
                    <div class="mb-3">
                        <label for="indicador_de_realizacion_de_la_capacitacion" class="form-label">Indicador de Realización</label>
                        <select name="indicador_de_realizacion_de_la_capacitacion" id="indicador_de_realizacion_de_la_capacitacion" class="form-select" >
                            <option value="SE EJECUTO EN LA FECHA O ANTES DE LA FECHA" <?= ($cronograma['indicador_de_realizacion_de_la_capacitacion'] == 'SE EJECUTO EN LA FECHA O ANTES DE LA FECHA') ? 'selected' : '' ?>>SE EJECUTÓ EN LA FECHA O ANTES DE LA FECHA</option>
                            <option value="SE EJECUTO DESPUES DE LA FECHA ACORDADA A CAUSA DEL CLIENTE" <?= ($cronograma['indicador_de_realizacion_de_la_capacitacion'] == 'SE EJECUTO DESPUES DE LA FECHA ACORDADA A CAUSA DEL CLIENTE') ? 'selected' : '' ?>>SE EJECUTÓ DESPUÉS DE LA FECHA ACORDADA A CAUSA DEL CLIENTE</option>
                            <option value="DECLINADA POR EL CLIENTE" <?= ($cronograma['indicador_de_realizacion_de_la_capacitacion'] == 'DECLINADA POR EL CLIENTE') ? 'selected' : '' ?>>DECLINADA POR EL CLIENTE</option>
                            <option value="NO HAY JUSTIFICACION PORQUE NO SE REALIZÓ" <?= ($cronograma['indicador_de_realizacion_de_la_capacitacion'] == 'NO HAY JUSTIFICACION PORQUE NO SE REALIZÓ') ? 'selected' : '' ?>>NO HAY JUSTIFICACIÓN PORQUE NO SE REALIZÓ</option>
                            <option value="SE EJECUTO DESPUES DE LA FECHA POR CAUSA DEL CAPACITADOR" <?= ($cronograma['indicador_de_realizacion_de_la_capacitacion'] == 'SE EJECUTO DESPUES DE LA FECHA POR CAUSA DEL CAPACITADOR') ? 'selected' : '' ?>>SE EJECUTÓ DESPUÉS DE LA FECHA POR CAUSA DEL CAPACITADOR</option>
                        </select>
                    </div>

                    <!-- Número de Asistentes -->
                    <div class="mb-3">
                        <label for="numero_de_asistentes_a_capacitacion" class="form-label">Número de Asistentes</label>
                        <input type="number" name="numero_de_asistentes_a_capacitacion" id="numero_de_asistentes_a_capacitacion" class="form-control" value="<?= esc($cronograma['numero_de_asistentes_a_capacitacion']) ?>" >
                    </div>

                    <!-- Número Total de Programados -->
                    <div class="mb-3">
                        <label for="numero_total_de_personas_programadas" class="form-label">Número Total de Programados</label>
                        <input type="number" name="numero_total_de_personas_programadas" id="numero_total_de_personas_programadas" class="form-control" value="<?= esc($cronograma['numero_total_de_personas_programadas']) ?>" >
                    </div>

                    <!-- Porcentaje de Cobertura -->
                    <div class="mb-3">
                        <label for="porcentaje_cobertura" class="form-label">Porcentaje de Cobertura</label>
                        <input type="text" name="porcentaje_cobertura" id="porcentaje_cobertura" class="form-control" value="<?= esc($cronograma['porcentaje_cobertura']) ?>" readonly>
                    </div>

                    <!-- Número de Evaluados -->
                    <div class="mb-3">
                        <label for="numero_de_personas_evaluadas" class="form-label">Número de Evaluados</label>
                        <input type="number" name="numero_de_personas_evaluadas" id="numero_de_personas_evaluadas" class="form-control" value="<?= esc($cronograma['numero_de_personas_evaluadas']) ?>" >
                    </div>

                    <!-- Promedio de Calificaciones -->
                    <div class="mb-3">
                        <label for="promedio_de_calificaciones" class="form-label">Promedio de Calificaciones</label>
                        <input type="number" step="0.01" name="promedio_de_calificaciones" id="promedio_de_calificaciones" class="form-control" value="<?= esc($cronograma['promedio_de_calificaciones']) ?>" >
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" class="form-control" rows="4"><?= esc($cronograma['observaciones']) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary me-2">Guardar Cambios</button>
                <a href="<?= base_url('/listcronogCapacitacion') ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p class="fw-bold mb-1">Cycloid Talent SAS</p>
            <p class="mb-1">Todos los derechos reservados © 2024</p>
            <p class="mb-1">NIT: 901.653.912</p>
            <p class="mb-3">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank">https://cycloidtalent.com/</a>
            </p>
            <p><strong>Nuestras Redes Sociales:</strong></p>
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

    <!-- Bootstrap JS y dependencias (Popper.js y jQuery si es necesario) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>
</body>

</html>