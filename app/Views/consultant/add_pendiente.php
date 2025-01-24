<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Pendiente</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            color: #343a40;
        }

        h2 {
            color: #495057;
            font-weight: 700;
        }

        .form-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 700px;
            margin: auto;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875em;
        }
    </style>
</head>

<body>

    <!-- Navbar similar a la lista de pendientes -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center w-100">
                <!-- Logos -->
                <div class="d-flex align-items-center">
                    <a href="https://dashboard.cycloidtalent.com/login" class="me-3">
                        <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 60px;">
                    </a>
                    <a href="https://cycloidtalent.com/index.php/consultoria-sst" class="me-3">
                        <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 60px;">
                    </a>
                    <a href="https://cycloidtalent.com/">
                        <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 60px;">
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Espaciado para el navbar fijo -->
    <div style="height: 100px;"></div>

    <div class="container my-5">
        <h2 class="text-center mb-4">Agregar Pendiente</h2>

        <!-- Mensaje de error o éxito -->
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-danger">
                <?php 
                    $msg = session()->getFlashdata('msg'); 
                    // Verificar si $msg es un arreglo
                    if (is_array($msg)): 
                        foreach ($msg as $error): ?>
                            <p><?= esc($error) ?></p>
                <?php 
                        endforeach; 
                    elseif (is_string($msg)): // Si es una cadena, mostrarla directamente ?>
                        <p><?= esc($msg) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="<?= base_url('/addPendientePost') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="id_cliente" class="form-label">Cliente <span class="text-danger">*</span></label>
                    <select name="id_cliente" id="id_cliente" class="form-select" required>
                        <option value="">Seleccione un cliente</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= esc($cliente['id_cliente']); ?>" <?= set_select('id_cliente', $cliente['id_cliente']); ?>>
                                <?= esc($cliente['nombre_cliente']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($validation) && $validation->hasError('id_cliente')): ?>
                        <div class="error-message"><?= $validation->getError('id_cliente') ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="fecha_asignacion" class="form-label">Fecha de Asignación <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_asignacion" name="fecha_asignacion" value="<?= set_value('fecha_asignacion', date('Y-m-d')); ?>" required>
                    <?php if (isset($validation) && $validation->hasError('fecha_asignacion')): ?>
                        <div class="error-message"><?= $validation->getError('fecha_asignacion') ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="responsable" class="form-label">Responsable <span class="text-danger">*</span></label>
                    <input type="text" name="responsable" id="responsable" class="form-control" placeholder="Ingrese el nombre del responsable" required value="<?= set_value('responsable'); ?>">

                    <?php if (isset($validation) && $validation->hasError('responsable')): ?>
                        <div class="error-message"><?= $validation->getError('responsable') ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="tarea_actividad" class="form-label">Tarea Actividad <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="tarea_actividad" name="tarea_actividad" rows="3" required><?= set_value('tarea_actividad'); ?></textarea>
                    <?php if (isset($validation) && $validation->hasError('tarea_actividad')): ?>
                        <div class="error-message"><?= $validation->getError('tarea_actividad') ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="fecha_cierre" class="form-label">Fecha Cierre</label>
                    <input type="date" class="form-control" id="fecha_cierre" name="fecha_cierre" value="<?= set_value('fecha_cierre'); ?>">
                    <?php if (isset($validation) && $validation->hasError('fecha_cierre')): ?>
                        <div class="error-message"><?= $validation->getError('fecha_cierre') ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                    <select name="estado" id="estado" class="form-select" required>
                        <option value="ABIERTA" <?= set_select('estado', 'ABIERTA'); ?>>ABIERTA</option>
                        <option value="CERRADA" <?= set_select('estado', 'CERRADA'); ?>>CERRADA</option>
                    </select>
                    <?php if (isset($validation) && $validation->hasError('estado')): ?>
                        <div class="error-message"><?= $validation->getError('estado') ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="conteo_dias" class="form-label">Conteo Días</label>
                    <input type="number" class="form-control" id="conteo_dias" name="conteo_dias" readonly>
                </div>

                <div class="mb-3">
                    <label for="estado_avance" class="form-label">Estado Avance</label>
                    <input type="text" class="form-control" id="estado_avance" name="estado_avance" value="<?= set_value('estado_avance'); ?>">
                    <?php if (isset($validation) && $validation->hasError('estado_avance')): ?>
                        <div class="error-message"><?= $validation->getError('estado_avance') ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="evidencia_para_cerrarla" class="form-label">Evidencia para Cerrarla</label>
                    <textarea class="form-control" id="evidencia_para_cerrarla" name="evidencia_para_cerrarla" rows="3"><?= set_value('evidencia_para_cerrarla'); ?></textarea>
                    <?php if (isset($validation) && $validation->hasError('evidencia_para_cerrarla')): ?>
                        <div class="error-message"><?= $validation->getError('evidencia_para_cerrarla') ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary w-100">Guardar Pendiente</button>
            </form>
        </div>

        <!-- Scripts para calcular conteo_dias automáticamente -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- Bootstrap 5 JS Bundle (includes Popper) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
