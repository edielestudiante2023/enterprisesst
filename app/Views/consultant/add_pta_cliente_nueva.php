<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Agregar Pta Cliente Nueva</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>

<body>
    <div class="container mt-4">
        <h1>Agregar Registro Pta Cliente Nueva</h1>
        <!-- Mostrar mensajes de error o éxito -->
        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger"><?= session('error') ?></div>
        <?php endif; ?>
        <?php if (session()->has('message')): ?>
            <div class="alert alert-success"><?= session('message') ?></div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('/pta-cliente-nueva/addpost') ?>">
            <?= csrf_field() ?>
            <!-- Cliente (llave foránea) -->
            <div class="mb-3">
                <label for="id_cliente" class="form-label">Cliente</label>
                <select name="id_cliente" id="id_cliente" class="form-select" required>
                    <option value="">Seleccione un Cliente</option>
                    <?php if (isset($clients) && !empty($clients)): ?>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= esc($client['id_cliente']) ?>">
                                <?= esc($client['nombre_cliente']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Tipo Servicio -->
            <div class="mb-3">
                <label for="tipo_servicio" class="form-label">Tipo Servicio</label>
                <input type="text" name="tipo_servicio" id="tipo_servicio" class="form-control">
            </div>

            <!-- PHVA Plan de Trabajo -->
            <div class="mb-3">
                <label for="phva_plandetrabajo" class="form-label">PHVA Plan de Trabajo</label>
                <input type="text" name="phva_plandetrabajo" id="phva_plandetrabajo" class="form-control" required>
            </div>

            <!-- Numeral Plan de Trabajo -->
            <div class="mb-3">
                <label for="numeral_plandetrabajo" class="form-label">Numeral Plan de Trabajo</label>
                <input type="text" name="numeral_plandetrabajo" id="numeral_plandetrabajo" class="form-control" required>
            </div>

            <!-- Actividad Plan de Trabajo -->
            <div class="mb-3">
                <label for="actividad_plandetrabajo" class="form-label">Actividad Plan de Trabajo</label>
                <textarea name="actividad_plandetrabajo" id="actividad_plandetrabajo" class="form-control" rows="4" required></textarea>
            </div>

            <!-- Responsable Sugerido -->
            <div class="mb-3">
                <label for="responsable_sugerido_plandetrabajo" class="form-label">Responsable Sugerido</label>
                <input type="text" name="responsable_sugerido_plandetrabajo" id="responsable_sugerido_plandetrabajo" class="form-control">
            </div>

            <!-- Fecha Propuesta -->
            <div class="mb-3">
                <label for="fecha_propuesta" class="form-label">Fecha Propuesta</label>
                <input type="date" name="fecha_propuesta" id="fecha_propuesta" class="form-control" required>
            </div>

            <!-- Fecha Cierre -->
            <div class="mb-3">
                <label for="fecha_cierre" class="form-label">Fecha Cierre</label>
                <input type="date" name="fecha_cierre" id="fecha_cierre" class="form-control">
            </div>

            <!-- Estado Actividad -->
            <div class="mb-3">
                <label for="estado_actividad" class="form-label">Estado Actividad</label>
                <select name="estado_actividad" id="estado_actividad" class="form-select" required>
                    <option value="ABIERTA" selected>ABIERTA</option>
                    <option value="CERRADA">CERRADA</option>
                    <option value="GESTIONANDO">GESTIONANDO</option>
                </select>
            </div>

            <!-- Porcentaje Avance -->
            <div class="mb-3">
                <label for="porcentaje_avance" class="form-label">Porcentaje Avance</label>
                <input type="number" name="porcentaje_avance" id="porcentaje_avance" class="form-control" step="0.01" min="0" max="100" value="0.00">
            </div>

            <!-- Observaciones -->
            <div class="mb-3">
                <label for="observaciones" class="form-label">Observaciones</label>
                <textarea name="observaciones" id="observaciones" class="form-control" rows="3"></textarea>
            </div>

            <!-- Botón para enviar -->
            <button type="submit" class="btn btn-primary">Agregar Registro</button>
            <a href="<?= site_url('/pta-cliente-nueva/list') ?>" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <!-- jQuery, Bootstrap 5 JS, and Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar Select2 en el dropdown de clientes
            $('#id_cliente').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Buscar un cliente...',
                allowClear: true,
                minimumInputLength: 0, // Permitir ver la lista completa
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    }
                }
            });
        });
    </script>
</body>

</html>