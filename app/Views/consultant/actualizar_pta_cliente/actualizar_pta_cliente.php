<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar PTA Cliente</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <!-- Mostrar mensajes flash (éxito o error) -->
        <?php if(session()->getFlashdata('message')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('message') ?>
            </div>
        <?php endif; ?>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="card-title h4 mb-0">Subir Archivo CSV para Actualizar Actividades</h2>
                    </div>
                    <div class="card-body">
                        <form action="<?= base_url('csv/upload'); ?>" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="csv_file" class="form-label">Selecciona un archivo CSV:</label>
                                <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Subir y Procesar</button>
                        </form>
                        <hr>
                        <p>Formato esperado de columnas (en este orden):</p>
                        <ul>
                            <li><b>id_ptacliente</b> (int)</li>
                            <li><b>actividad_plandetrabajo</b> (texto)</li>
                            <li><b>responsable_sugerido_plandetrabajo</b> (texto)</li>
                            <li><b>fecha_propuesta</b> (en formato dd/mm/yyyy o yyyy-mm-dd)</li>
                            <li><b>fecha_cierre</b> (en formato dd/mm/yyyy o yyyy-mm-dd, puede estar vacía)</li>
                            <li><b>estado_actividad</b> (texto)</li>
                            <li><b>porcentaje_avance</b> (entero, 0 por defecto si está vacío)</li>
                            <li><b>observaciones</b> (texto, se mantendrá vacío si no se envía nada)</li>
                            <li><b>accion</b> ("actualizar" o "eliminar")</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap 5 Bundle JS (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
