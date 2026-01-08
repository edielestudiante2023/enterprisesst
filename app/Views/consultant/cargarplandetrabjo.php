<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Plan de Trabajo</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white text-center">
                <h2 class="mb-0">Cargar Archivo de Plan de Trabajo</h2>
            </div>
            <div class="card-body">
                <!-- Información de formato -->
                <div class="alert alert-info" role="alert">
                    <h6 class="alert-heading"><i class="bi bi-info-circle-fill"></i> Información del formato</h6>
                    <p class="mb-2"><strong>Encabezados requeridos (en orden):</strong></p>
                    <ol class="mb-2 small">
                        <li>id_cliente</li>
                        <li>phva_plandetrabajo</li>
                        <li>numeral_plandetrabajo</li>
                        <li>actividad_plandetrabajo</li>
                        <li>responsable_sugerido_plandetrabajo</li>
                        <li>observaciones</li>
                        <li>fecha_propuesta</li>
                    </ol>
                    <p class="mb-2 small"><strong>Formatos de fecha aceptados:</strong> dd/mm/yyyy, dd-mm-yyyy, yyyy-mm-dd, yyyy/mm/dd, dd.mm.yyyy, etc.</p>
                    <div class="alert alert-success mb-0 py-2" role="alert">
                        <small><i class="bi bi-check-circle"></i> <strong>Nota:</strong> Las actividades importadas se crearán automáticamente con estado <strong>ABIERTA</strong> y porcentaje de avance <strong>0%</strong>.</small>
                    </div>
                </div>

                <form action="<?= base_url('consultant/plan/upload') ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="file" class="form-label"><i class="bi bi-file-earmark-spreadsheet"></i> Seleccione un archivo (Excel o CSV):</label>
                        <input type="file" class="form-control" name="file" id="file" accept=".xlsx, .xls, .csv" required>
                        <div class="invalid-feedback">Por favor seleccione un archivo válido (.xlsx, .xls, .csv).</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-cloud-upload"></i> Cargar Archivo
                        </button>
                    </div>
                </form>

                <!-- Mostrar mensajes de éxito, advertencia o error -->
                <?php if (session()->getFlashdata('success')) : ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <h5 class="alert-heading"><i class="bi bi-check-circle-fill"></i> ¡Éxito!</h5>
                        <hr>
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('warning')) : ?>
                    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Advertencia</h5>
                        <hr>
                        <?= session()->getFlashdata('warning') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')) : ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <h5 class="alert-heading"><i class="bi bi-x-circle-fill"></i> Error</h5>
                        <hr>
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
