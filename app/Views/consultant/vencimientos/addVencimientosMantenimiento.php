<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agregar Vencimiento de Mantenimiento</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-5-theme/1.3.0/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h1 class="h4 mb-0"><i class="bi bi-calendar-plus me-2"></i>Agregar Vencimiento de Mantenimiento</h1>
                    </div>
                    <div class="card-body">
                        <!-- Mensaje de error -->
                        <?php if (session()->getFlashdata('msg')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?= session()->getFlashdata('msg') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario -->
                        <form action="<?= base_url('vencimientos/addpost') ?>" method="post" class="needs-validation" novalidate>
                            <?= csrf_field() ?>

                            <div class="mb-3">
                                <label for="id_cliente" class="form-label">Cliente</label>
                                <select class="form-select" name="id_cliente" id="id_cliente" required>
                                    <option value="">Seleccione un cliente</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?= esc($cliente['id_cliente']) ?>" <?= set_select('id_cliente', $cliente['id_cliente']) ?>>
                                            <?= esc($cliente['nombre_cliente']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un cliente.</div>
                            </div>

                            <div class="mb-3">
                                <label for="id_consultor" class="form-label">Consultor</label>
                                <select class="form-select" name="id_consultor" id="id_consultor" required>
                                    <option value="">Seleccione un consultor</option>
                                    <?php foreach ($consultores as $consultor): ?>
                                        <option value="<?= esc($consultor['id_consultor']) ?>" <?= set_select('id_consultor', $consultor['id_consultor']) ?>>
                                            <?= esc($consultor['nombre_consultor']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un consultor.</div>
                            </div>

                            <div class="mb-3">
                                <label for="id_mantenimiento" class="form-label">Mantenimiento</label>
                                <select class="form-select" name="id_mantenimiento" id="id_mantenimiento" required>
                                    <option value="">Seleccione un mantenimiento</option>
                                    <?php foreach ($mantenimientos as $mantenimiento): ?>
                                        <option value="<?= esc($mantenimiento['id_mantenimiento']) ?>" <?= set_select('id_mantenimiento', $mantenimiento['id_mantenimiento']) ?>>
                                            <?= esc($mantenimiento['detalle_mantenimiento']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un mantenimiento.</div>
                            </div>

                            <div class="mb-3">
                                <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
                                <input type="date" class="form-control" name="fecha_vencimiento" id="fecha_vencimiento" value="<?= set_value('fecha_vencimiento') ?>" required>
                                <div class="invalid-feedback">Por favor seleccione una fecha de vencimiento.</div>
                            </div>

                            <div class="mb-3">
                                <label for="estado_actividad" class="form-label">Estado de la Actividad</label>
                                <select class="form-select" name="estado_actividad" id="estado_actividad" required>
                                    <option value="sin ejecutar" <?= set_select('estado_actividad', 'sin ejecutar', true) ?>>Sin Ejecutar</option>
                                    <option value="ejecutado" <?= set_select('estado_actividad', 'ejecutado') ?>>Ejecutado</option>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un estado.</div>
                            </div>

                            <div class="mb-3">
                                <label for="fecha_realizacion" class="form-label">Fecha de Realizacion</label>
                                <input type="date" class="form-control" name="fecha_realizacion" id="fecha_realizacion" value="<?= set_value('fecha_realizacion') ?>">
                            </div>

                            <div class="mb-4">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" name="observaciones" id="observaciones" rows="3"><?= set_value('observaciones') ?></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="<?= base_url('vencimientos') ?>" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-arrow-left me-1"></i>Volver
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>Agregar Vencimiento
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (required for Select2) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <!-- Form Validation Script -->
    <script>
        // Initialize Select2
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

        (() => {
            'use strict';

            // Fetch all forms that need validation
            const forms = document.querySelectorAll('.needs-validation');

            // Loop over them and prevent submission
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            // Show/hide fecha_realizacion based on estado_actividad
            const estadoSelect = document.getElementById('estado_actividad');
            const fechaRealizacionDiv = document.querySelector('[for="fecha_realizacion"]').parentNode;

            function toggleFechaRealizacion() {
                if (estadoSelect.value === 'ejecutado') {
                    fechaRealizacionDiv.style.display = 'block';
                    document.getElementById('fecha_realizacion').required = true;
                } else {
                    fechaRealizacionDiv.style.display = 'none';
                    document.getElementById('fecha_realizacion').required = false;
                }
            }

            estadoSelect.addEventListener('change', toggleFechaRealizacion);
            toggleFechaRealizacion(); // Initial state
        })();
    </script>
</body>
</html>
