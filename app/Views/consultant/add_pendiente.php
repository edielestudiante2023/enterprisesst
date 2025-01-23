<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Pendiente</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        h2 {
            color: #333;
        }

        .form-control,
        .btn {
            border-radius: 5px;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        label {
            font-weight: bold;
            color: #555;
        }
    </style>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <!-- Bootstrap JS Bundle -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>

<body>

    <!-- Navbar -->
    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 20px;">

            <!-- Logo izquierdo -->
            <div>
                <a href="https://dashboard.cycloidtalent.com/login">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;">
                </a>
            </div>

            <!-- Logo centro -->
            <div>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;">
                </a>
            </div>

            <!-- Logo derecho -->
            <div>
                <a href="https://cycloidtalent.com/">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;">
                </a>
            </div>

            <!-- Botón -->
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Ir a Dashboard</h2>
                <a href="<?= base_url('/dashboardconsultant') ?>" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;">Ir a DashBoard</a>
            </div>
        </div>
    </nav>

    <!-- Ajustar el espaciado para evitar que el contenido se oculte bajo el navbar fijo -->
    <div style="height: 160px;"></div>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Agregar Pendiente</h2>

        <!-- Mensaje de error o éxito -->
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>

        <form action="<?= base_url('/addPendientePost') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="id_cliente">Cliente:</label>
                <select name="id_cliente" id="id_cliente" class="form-control" required>
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($clientes as $cliente) : ?>
                        <option value="<?= esc($cliente['id_cliente']); ?>" <?= set_select('id_cliente', $cliente['id_cliente']); ?>>
                            <?= esc($cliente['nombre_cliente']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="text-danger"><?= isset($validation) ? $validation->getError('id_cliente') : '' ?></div>
            </div>

            <div class="form-group">
                <label for="fecha_asignacion">Fecha de Asignación:</label>
                <input type="date" class="form-control" id="fecha_asignacion" name="fecha_asignacion" value="<?= set_value('fecha_asignacion', date('Y-m-d')); ?>" required>
                <div class="text-danger"><?= isset($validation) ? $validation->getError('fecha_asignacion') : '' ?></div>
            </div>

            <div class="form-group">
                <label for="responsable">Responsable:</label>
                <select name="responsable" id="responsable" class="form-control" required>
                    <option value="">Seleccione un responsable</option>
                    <option value="GERENTE" <?= set_select('responsable', 'GERENTE'); ?>>Gerente General o Dirección</option>
                    <option value="COORDINADOR_SST" <?= set_select('responsable', 'COORDINADOR_SST'); ?>>Coordinador de SST o Responsable SST</option>
                    <option value="COPASST" <?= set_select('responsable', 'COPASST'); ?>>Miembro del COPASST</option>
                    <option value="RRHH" <?= set_select('responsable', 'RRHH'); ?>>Recursos Humanos</option>
                    <option value="SUPERVISOR" <?= set_select('responsable', 'SUPERVISOR'); ?>>Supervisor de Área o Jefe de Equipo</option>
                    <option value="MANTENIMIENTO" <?= set_select('responsable', 'MANTENIMIENTO'); ?>>Personal de Mantenimiento</option>
                    <option value="TRABAJADOR_DESIGNADO" <?= set_select('responsable', 'TRABAJADOR_DESIGNADO'); ?>>Trabajador Designado por Área (Inspector)</option>
                    <option value="AUDITOR" <?= set_select('responsable', 'AUDITOR'); ?>>Auditor Interno SST</option>
                    <option value="GESTION_DOCUMENTAL" <?= set_select('responsable', 'GESTION_DOCUMENTAL'); ?>>Encargado de Gestión Documental</option>
                    <option value="CONSULTOR_EXTERNO" <?= set_select('responsable', 'CONSULTOR_EXTERNO'); ?>>Consultor Externo - Cycloid Talent</option>
                </select>
                <div class="text-danger"><?= isset($validation) ? $validation->getError('responsable') : '' ?></div>
            </div>

            <div class="form-group">
                <label for="tarea_actividad">Tarea Actividad:</label>
                <textarea class="form-control" id="tarea_actividad" name="tarea_actividad" required><?= set_value('tarea_actividad'); ?></textarea>
                <div class="text-danger"><?= isset($validation) ? $validation->getError('tarea_actividad') : '' ?></div>
            </div>

            <div class="form-group">
                <label for="fecha_cierre">Fecha Cierre:</label>
                <input type="date" class="form-control" id="fecha_cierre" name="fecha_cierre" value="<?= set_value('fecha_cierre'); ?>">
                <div class="text-danger"><?= isset($validation) ? $validation->getError('fecha_cierre') : '' ?></div>
            </div>

            <div class="form-group">
                <label for="estado">Estado:</label>
                <select name="estado" id="estado" class="form-control" required>
                    <option value="ABIERTA" <?= set_select('estado', 'ABIERTA'); ?>>ABIERTA</option>
                    <option value="CERRADA" <?= set_select('estado', 'CERRADA'); ?>>CERRADA</option>
                </select>
                <div class="text-danger"><?= isset($validation) ? $validation->getError('estado') : '' ?></div>
            </div>

            <div class="form-group">
                <label for="conteo_dias">Conteo Días:</label>
                <input type="number" class="form-control" id="conteo_dias" name="conteo_dias" readonly>
                <div class="text-danger"><?= isset($validation) ? $validation->getError('conteo_dias') : '' ?></div>
            </div>

            <div class="form-group">
                <label for="estado_avance">Estado Avance:</label>
                <input type="text" class="form-control" id="estado_avance" name="estado_avance" value="<?= set_value('estado_avance'); ?>">
                <div class="text-danger"><?= isset($validation) ? $validation->getError('estado_avance') : '' ?></div>
            </div>

            <div class="form-group">
                <label for="evidencia_para_cerrarla">Evidencia para Cerrarla:</label>
                <textarea class="form-control" id="evidencia_para_cerrarla" name="evidencia_para_cerrarla"><?= set_value('evidencia_para_cerrarla'); ?></textarea>
                <div class="text-danger"><?= isset($validation) ? $validation->getError('evidencia_para_cerrarla') : '' ?></div>
            </div>

            <button type="submit" class="btn btn-primary">Guardar Pendiente</button>
        </form>

        <!-- Ejemplo de tabla interactiva -->
        <div class="mt-5">
            <h3 class="text-center">Pendientes</h3>
            <table id="pendientesTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Fecha Asignación</th> <!-- Nueva Columna -->
                        <th>Fecha Creación</th>
                        <th>Responsable</th>
                        <th>Tarea</th>
                        <th>Fecha Cierre</th>
                        <th>Estado</th>
                        <th>Conteo Días</th>
                        <th>Estado Avance</th>
                        <th>Evidencia para Cerrarla</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Rellena con datos dinámicos de PHP si es necesario -->
                </tbody>
            </table>
        </div>
    </div>
    <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
            <!-- Company and Rights -->
            <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
            <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
            <p style="margin: 5px 0;">NIT: 901.653.912</p>

            <!-- Website Link -->
            <p style="margin: 5px 0;">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" style="color: #007BFF; text-decoration: none;">https://cycloidtalent.com/</a>
            </p>

            <!-- Social Media Links -->
            <p style="margin: 15px 0 5px;"><strong>Nuestras Redes Sociales:</strong></p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
                </a>
            </div>
        </div>
    </footer>

    <!-- Scripts para calcular conteo_dias automáticamente -->
    <script>
        $(document).ready(function() {
            function calculateConteoDias() {
                var fechaAsignacion = $('#fecha_asignacion').val();
                var fechaCierre = $('#fecha_cierre').val();
                var estado = $('#estado').val();
                var conteoDias = 0;

                if (fechaAsignacion) {
                    var asignacionDate = new Date(fechaAsignacion);
                    var currentDate = new Date();

                    if (estado === 'ABIERTA') {
                        var timeDiff = currentDate - asignacionDate;
                        conteoDias = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
                    } else if (estado === 'CERRADA' && fechaCierre) {
                        var cierreDate = new Date(fechaCierre);
                        var timeDiff = cierreDate - asignacionDate;
                        conteoDias = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
                    }
                }

                $('#conteo_dias').val(conteoDias);
            }

            // Calcular al cargar la página
            calculateConteoDias();

            // Calcular al cambiar fecha_asignacion, fecha_cierre o estado
            $('#fecha_asignacion, #fecha_cierre, #estado').on('change', function() {
                calculateConteoDias();
            });
        });
    </script>

</body>

</html>
