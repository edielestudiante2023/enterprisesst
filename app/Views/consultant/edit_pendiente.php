<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pendiente</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-4-theme@1.0.0/dist/select2-bootstrap-4.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
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

        .form-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: auto;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875em;
        }
    </style>
</head>

<body style="background-color: #f8f9fa;">

    <!-- Navbar -->
    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 20px;">

            <!-- Logo izquierdo -->
            <div>
                <a href="https://dashboard.cycloidtalent.com/login">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 60px;">
                </a>
            </div>

            <!-- Logo centro -->
            <div>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 60px;">
                </a>
            </div>

            <!-- Logo derecho -->
            <div>
                <a href="https://cycloidtalent.com/">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 60px;">
                </a>
            </div>

            <!-- Botón -->
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Ir a Dashboard</h2>
                <a href="<?= base_url('/dashboardconsultant') ?>" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;">Ir a Dashboard</a>
            </div>
        </div>
    </nav>

    <!-- Ajustar el espaciado para evitar que el contenido se oculte bajo el navbar fijo -->
    <div style="height: 160px;"></div>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Editar Pendiente</h2>

        <!-- Mensaje de error o éxito -->
        <?php if ($msg = session()->getFlashdata('msg')): ?>
            <div class="alert alert-danger">
                <?php if (is_array($msg)): ?>
                    <?php foreach ($msg as $error): ?>
                        <p><?= esc($error) ?></p>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><?= esc($msg) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="<?= base_url('/editPendientePost/' . esc($pendiente['id_pendientes'])); ?>" method="post">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="id_cliente">Cliente <span class="text-danger">*</span></label>
                    <select name="id_cliente" id="id_cliente" class="form-control select2" required>
                        <option value="">Seleccione un cliente</option>
                        <?php foreach ($clientes as $cliente) : ?>
                            <option value="<?= esc($cliente['id_cliente']); ?>" <?= set_select('id_cliente', $cliente['id_cliente'], ($pendiente['id_cliente'] == $cliente['id_cliente'])); ?>>
                                <?= esc($cliente['nombre_cliente']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($validation) && $validation->hasError('id_cliente')): ?>
                        <div class="error-message"><?= $validation->getError('id_cliente') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <input type="date" class="form-control" id="fecha_asignacion" name="fecha_asignacion"
                        value="<?= isset($pendiente['fecha_asignacion']) ? esc(substr($pendiente['fecha_asignacion'], 0, 10)) : ''; ?>"
                        required>


                </div>

                <div class="form-group">
                    <label for="responsable">Responsable:</label>
                    <select name="responsable" id="responsable" class="form-control" required>
                        <option value="">Seleccione una opción</option>
                        <option value="CLIENTE" <?= set_value('responsable', $pendiente['responsable'] ?? '') === 'CLIENTE' ? 'selected' : '' ?>>CLIENTE</option>
                        <option value="CYCLOID TALENT" <?= set_value('responsable', $pendiente['responsable'] ?? '') === 'CYCLOID TALENT' ? 'selected' : '' ?>>CYCLOID TALENT</option>
                    </select>

                    <?php if (isset($validation) && $validation->hasError('responsable')): ?>
                        <div class="error-message"><?= $validation->getError('responsable') ?></div>
                    <?php endif; ?>
                </div>


                <div class="form-group">
                    <label for="tarea_actividad">Tarea Actividad <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="tarea_actividad" name="tarea_actividad" rows="3" required><?= set_value('tarea_actividad', esc($pendiente['tarea_actividad'])); ?></textarea>
                    <?php if (isset($validation) && $validation->hasError('tarea_actividad')): ?>
                        <div class="error-message"><?= $validation->getError('tarea_actividad') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="fecha_cierre">Fecha Cierre</label>
                    <input type="date" class="form-control" id="fecha_cierre" name="fecha_cierre" value="<?= set_value('fecha_cierre', esc($pendiente['fecha_cierre'])); ?>">
                    <?php if (isset($validation) && $validation->hasError('fecha_cierre')): ?>
                        <div class="error-message"><?= $validation->getError('fecha_cierre') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="estado">Estado <span class="text-danger">*</span></label>
                    <select name="estado" id="estado" class="form-control" required>
                        <option value="ABIERTA" <?= set_select('estado', 'ABIERTA', ($pendiente['estado'] == 'ABIERTA')); ?>>ABIERTA</option>
                        <option value="CERRADA" <?= set_select('estado', 'CERRADA', ($pendiente['estado'] == 'CERRADA')); ?>>CERRADA</option>
                    </select>
                    <?php if (isset($validation) && $validation->hasError('estado')): ?>
                        <div class="error-message"><?= $validation->getError('estado') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="conteo_dias">Conteo Días</label>
                    <input type="number" class="form-control" id="conteo_dias" name="conteo_dias" value="<?= set_value('conteo_dias', esc($pendiente['conteo_dias'])); ?>" readonly>
                    <?php if (isset($validation) && $validation->hasError('conteo_dias')): ?>
                        <div class="error-message"><?= $validation->getError('conteo_dias') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="estado_avance">Estado Avance <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="estado_avance" name="estado_avance" value="<?= set_value('estado_avance', esc($pendiente['estado_avance'])); ?>">
                    <?php if (isset($validation) && $validation->hasError('estado_avance')): ?>
                        <div class="error-message"><?= $validation->getError('estado_avance') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="evidencia_para_cerrarla">Evidencia para Cerrarla</label>
                    <textarea class="form-control" id="evidencia_para_cerrarla" name="evidencia_para_cerrarla" rows="3"><?= set_value('evidencia_para_cerrarla', esc($pendiente['evidencia_para_cerrarla'])); ?></textarea>
                    <?php if (isset($validation) && $validation->hasError('evidencia_para_cerrarla')): ?>
                        <div class="error-message"><?= $validation->getError('evidencia_para_cerrarla') ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Guardar Cambios</button>
            </form>
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

        <!-- Bootstrap 4.5.2 JS and dependencies -->
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

        <!-- Select2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <!-- Scripts para calcular conteo_dias automáticamente -->
        <script>
            $(document).ready(function() {
                // Inicializar Select2
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: 'Buscar un cliente...',
                    allowClear: true,
                    minimumInputLength: 1,
                    language: {
                        noResults: function() {
                            return "No se encontraron resultados";
                        },
                        inputTooShort: function() {
                            return "Por favor ingrese 1 o más caracteres";
                        }
                    }
                });

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