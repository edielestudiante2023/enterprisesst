<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Cronograma de Capacitación</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <h2 class="mb-0">Cargar Archivo de Cronograma de Capacitación</h2>
            </div>
            <div class="card-body">
                <!-- Instrucciones para el archivo CSV -->
                <div class="alert alert-info mb-4">
                    <h5 class="alert-heading">Instrucciones para el archivo CSV:</h5>
                    <p class="mb-2">El archivo CSV debe contener las siguientes columnas en este orden:</p>
                    <ol>
                        <li>id_capacitacion</li>
                        <li>id_cliente</li>
                        <li>fecha_programada (Acepta formatos como: DD/MM/YYYY, MM-DD-YYYY, YYYY-MM-DD)</li>
                        <li>estado</li>
                        <li>perfil_de_asistentes</li>
                        <li>nombre_del_capacitador</li>
                        <li>horas_de_duracion_de_la_capacitacion</li>
                        <li>indicador_de_realizacion_de_la_capacitacion</li>
                    </ol>
                    <p class="mb-0"><strong>Nota:</strong> La primera fila debe contener los nombres exactos de las columnas como se muestran arriba.</p>
                </div>

                <form action="<?= base_url('consultant/csvcronogramadecapacitacion/upload') ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="file" class="form-label">Seleccione un archivo (CSV):</label>
                        <input type="file" class="form-control" name="file" id="file" accept=".csv" required>
                        <div class="invalid-feedback">Por favor seleccione un archivo CSV.</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Cargar Archivo</button>
                    </div>
                </form>

                <!-- Mostrar mensajes de éxito o error -->
                <?php if (session()->getFlashdata('success')) : ?>
                    <div class="alert alert-success mt-3" role="alert">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')) : ?>
                    <div class="alert alert-danger mt-3" role="alert">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación del formulario
        (function () {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>
