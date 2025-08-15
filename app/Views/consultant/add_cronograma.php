<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Cronograma de Capacitación</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        body {
            font-size: 0.9rem;
            background-color: #f9f9f9;
        }

        h2 {
            color: #333;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #555;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            font-weight: bold;
        }

        .btn-secondary {
            font-weight: bold;
        }

        .alert {
            font-weight: bold;
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
        <h2 class="text-center mb-4">Agregar Cronograma de Capacitación</h2>

        <!-- Mensajes Flash -->
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-info">
                <?= session()->getFlashdata('msg') ?>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form action="<?= base_url('/addcronogCapacitacionPost') ?>" method="post">
            <div class="row">
                <!-- Columna Izquierda -->
                <div class="col-md-6">
                    <!-- Capacitación -->
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




                </div>

                <!-- Cliente -->
                <div class="mb-3">
                    <label for="id_cliente" class="form-label">Cliente</label>
                    <select name="id_cliente" id="id_cliente" class="form-select" required>
                        <option value="" disabled selected>Selecciona un cliente</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['id_cliente'] ?>">
                                <?= $cliente['nombre_cliente'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Fecha Programada -->
                <div class="mb-3">
                    <label for="fecha_programada" class="form-label">Fecha Programada</label>
                    <input type="date" name="fecha_programada" id="fecha_programada" class="form-control" required>
                </div>

                <!-- Fecha de Realización -->
                <div class="mb-3">
                    <label for="fecha_de_realizacion" class="form-label">Fecha de Realización</label>
                    <input type="date" name="fecha_de_realizacion" id="fecha_de_realizacion" class="form-control">
                </div>

                <!-- Estado -->
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select" required>
                        <option value="" disabled selected>Selecciona un estado</option>
                        <option value="PROGRAMADA">PROGRAMADA</option>
                        <option value="EJECUTADA">EJECUTADA</option>
                        <option value="CANCELADA POR EL CLIENTE">CANCELADA POR EL CLIENTE</option>
                        <option value="REPROGRAMADA">REPROGRAMADA</option>
                    </select>
                </div>

                <!-- Perfil de Asistentes -->
                <div class="mb-3">
                    <label for="perfil_de_asistentes" class="form-label">Perfil de Asistentes</label>
                    <select name="perfil_de_asistentes" id="perfil_de_asistentes" class="form-select" required>
                        <option value="" disabled selected>Selecciona un perfil</option>

                        <!-- Roles Internos -->
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

                        <!-- Roles Externos -->
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

                        <!-- Opción para Todos -->
                        <option value="TODOS">TODOS</option>
                    </select>

                </div>
            </div>

            <!-- Columna Derecha -->
            <div class="col-md-6">
                <!-- Nombre del Capacitador -->
                <div class="mb-3">
                    <label for="nombre_del_capacitador" class="form-label">Nombre del Capacitador</label>
                    <input type="text" name="nombre_del_capacitador" id="nombre_del_capacitador" class="form-control" >
                </div>

                <!-- Horas de Duración -->
                <div class="mb-3">
                    <label for="horas_de_duracion_de_la_capacitacion" class="form-label">Horas de Duración</label>
                    <input type="number" name="horas_de_duracion_de_la_capacitacion" id="horas_de_duracion_de_la_capacitacion" class="form-control" >
                </div>

                <!-- Indicador de Realización -->
                <div class="mb-3">
                    <label for="indicador_de_realizacion_de_la_capacitacion" class="form-label">Indicador de Realización</label>
                    <select name="indicador_de_realizacion_de_la_capacitacion" id="indicador_de_realizacion_de_la_capacitacion" class="form-select" >
                        <option value="" disabled selected>Selecciona un indicador</option>
                        <option value="SE EJECUTO EN LA FECHA O ANTES DE LA FECHA">SE EJECUTÓ EN LA FECHA O ANTES DE LA FECHA</option>
                        <option value="SE EJECUTO DESPUES DE LA FECHA ACORDADA A CAUSA DEL CLIENTE">SE EJECUTÓ DESPUÉS DE LA FECHA ACORDADA A CAUSA DEL CLIENTE</option>
                        <option value="DECLINADA POR EL CLIENTE">DECLINADA POR EL CLIENTE</option>
                        <option value="NO HAY JUSTIFICACION PORQUE NO SE REALIZÓ">NO HAY JUSTIFICACIÓN PORQUE NO SE REALIZÓ</option>
                        <option value="SE EJECUTO DESPUES DE LA FECHA POR CAUSA DEL CAPACITADOR">SE EJECUTÓ DESPUÉS DE LA FECHA POR CAUSA DEL CAPACITADOR</option>
                    </select>
                </div>

                <!-- Número de Asistentes -->
                <div class="mb-3">
                    <label for="numero_de_asistentes_a_capacitacion" class="form-label">Número de Asistentes</label>
                    <input type="number" name="numero_de_asistentes_a_capacitacion" id="numero_de_asistentes_a_capacitacion" class="form-control" >
                </div>

                <!-- Número Total de Programados -->
                <div class="mb-3">
                    <label for="numero_total_de_personas_programadas" class="form-label">Número Total de Programados</label>
                    <input type="number" name="numero_total_de_personas_programadas" id="numero_total_de_personas_programadas" class="form-control" >
                </div>

                <!-- Porcentaje de Cobertura -->
                <div class="mb-3">
                    <label for="porcentaje_cobertura" class="form-label">Porcentaje de Cobertura</label>
                    <input type="text" name="porcentaje_cobertura" id="porcentaje_cobertura" class="form-control" readonly>
                </div>

                <!-- Número de Evaluados -->
                <div class="mb-3">
                    <label for="numero_de_personas_evaluadas" class="form-label">Número de Evaluados</label>
                    <input type="number" name="numero_de_personas_evaluadas" id="numero_de_personas_evaluadas" class="form-control" >
                </div>

                <!-- Promedio de Calificaciones -->
                <div class="mb-3">
                    <label for="promedio_de_calificaciones" class="form-label">Promedio de Calificaciones</label>
                    <input type="number" step="0.01" name="promedio_de_calificaciones" id="promedio_de_calificaciones" class="form-control" >
                </div>

                <!-- Observaciones -->
                <div class="mb-3">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" class="form-control" rows="4"></textarea>
                </div>
            </div>
    </div>

    <!-- Botones de Acción -->
    <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-primary me-2">Agregar Cronograma</button>
        <a href="<?= base_url('/listcronogCapacitacion') ?>" class="btn btn-secondary">Cancelar</a>
    </div>
    </form>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 border-top">
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
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Inicializar Select2 para capacitación
            $('#id_capacitacion').select2({
                placeholder: "Buscar y seleccionar capacitación",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
</body>

</html>