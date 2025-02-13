<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <!-- Encabezado -->
                    <div class="card-header bg-success text-white text-center">
                        <h3 class="mb-0">Agregar Dashboard</h3>
                    </div>
                    <div class="card-body">
                        <!-- Subtítulo -->
                        <p class="text-muted text-center">
                            Complete el formulario a continuación para agregar un nuevo dashboard.
                        </p>
                        <!-- Formulario -->
                        <form action="<?= base_url('lookerstudio/addPost') ?>" method="post" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="tipodedashboard" class="form-label">Tipo de Dashboard</label>
                                <input type="text" class="form-control" id="tipodedashboard" name="tipodedashboard" required>
                                <div class="invalid-feedback">Por favor, ingrese el tipo de dashboard.</div>
                            </div>

                            <div class="mb-3">
                                <label for="enlace" class="form-label">Enlace</label>
                                <input type="url" class="form-control" id="enlace" name="enlace" required>
                                <div class="invalid-feedback">Por favor, ingrese un enlace válido.</div>
                            </div>

                            <div class="mb-3">
                                <label for="id_cliente" class="form-label">Cliente</label>
                                <select class="form-select" id="id_cliente" name="id_cliente" required>
                                    <option value="" disabled selected>Seleccione un cliente</option>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client['id_cliente'] ?>"><?= $client['nombre_cliente'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Por favor, seleccione un cliente.</div>
                            </div>

                            <!-- Botón de envío -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery (necesario para Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Inicializar Select2
        $(document).ready(function() {
            $('#id_cliente').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Buscar cliente...',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });
        });

        // Activar validación de Bootstrap
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
