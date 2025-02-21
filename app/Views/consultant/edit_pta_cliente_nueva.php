<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Pta Cliente Nueva</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>

<body>
    <div class="container mt-4">
        <h1>Editar Registro Pta Cliente Nueva</h1>
        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger"><?= session('error') ?></div>
        <?php endif; ?>
        <?php if (session()->has('message')): ?>
            <div class="alert alert-success"><?= session('message') ?></div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('/pta-cliente-nueva/editpost/' . esc($record['id_ptacliente'])) ?>">
            <?= csrf_field() ?>
            <!-- Campos ocultos para mantener los filtros -->
            <input type="hidden" name="filter_cliente" value="<?= esc($filters['cliente'] ?? '') ?>">
            <input type="hidden" name="filter_fecha_desde" value="<?= esc($filters['fecha_desde'] ?? '') ?>">
            <input type="hidden" name="filter_fecha_hasta" value="<?= esc($filters['fecha_hasta'] ?? '') ?>">
            <input type="hidden" name="filter_estado" value="<?= esc($filters['estado'] ?? '') ?>">

            <!-- Cliente (llave foránea) -->
            <div class="mb-3">
                <label for="id_cliente" class="form-label">Cliente</label>
                <select name="id_cliente" id="id_cliente" class="form-select" required>
                    <option value="">Seleccione un Cliente</option>
                    <?php if (isset($clients) && !empty($clients)): ?>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= esc($client['id_cliente']) ?>"
                                <?= ($record['id_cliente'] == $client['id_cliente']) ? 'selected' : '' ?>>
                                <?= esc($client['nombre_cliente']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Tipo Servicio -->
            <div class="mb-3">
                <label for="tipo_servicio" class="form-label">Tipo Servicio</label>
                <input type="text" name="tipo_servicio" id="tipo_servicio" class="form-control" value="<?= esc($record['tipo_servicio']) ?>">
            </div>

            <!-- PHVA Plan de Trabajo -->
            <div class="mb-3">
                <label for="phva_plandetrabajo" class="form-label">PHVA Plan de Trabajo</label>
                <input type="text" name="phva_plandetrabajo" id="phva_plandetrabajo" class="form-control" value="<?= esc($record['phva_plandetrabajo']) ?>" required>
            </div>

            <!-- Numeral Plan de Trabajo -->
            <div class="mb-3">
                <label for="numeral_plandetrabajo" class="form-label">Numeral Plan de Trabajo</label>
                <input type="text" name="numeral_plandetrabajo" id="numeral_plandetrabajo" class="form-control" value="<?= esc($record['numeral_plandetrabajo']) ?>" required>
            </div>

            <!-- Actividad Plan de Trabajo -->
            <div class="mb-3">
                <label for="actividad_plandetrabajo" class="form-label">Actividad Plan de Trabajo</label>
                <textarea name="actividad_plandetrabajo" id="actividad_plandetrabajo" class="form-control" rows="4" required><?= esc($record['actividad_plandetrabajo']) ?></textarea>
            </div>

            <!-- Responsable Sugerido -->
            <div class="mb-3">
                <label for="responsable_sugerido_plandetrabajo" class="form-label">Responsable Sugerido</label>
                <input type="text" name="responsable_sugerido_plandetrabajo" id="responsable_sugerido_plandetrabajo" class="form-control" value="<?= esc($record['responsable_sugerido_plandetrabajo']) ?>">
            </div>

            <!-- Fecha Propuesta -->
            <div class="mb-3">
                <label for="fecha_propuesta" class="form-label">Fecha Propuesta</label>
                <input type="date" name="fecha_propuesta" id="fecha_propuesta" class="form-control" value="<?= esc($record['fecha_propuesta']) ?>" required>
            </div>

            <!-- Fecha Cierre -->
            <div class="mb-3">
                <label for="fecha_cierre" class="form-label">Fecha Cierre</label>
                <input type="date" name="fecha_cierre" id="fecha_cierre" class="form-control" value="<?= esc($record['fecha_cierre']) ?>">
            </div>

            <!-- Estado Actividad -->
            <div class="mb-3">
                <label for="estado_actividad" class="form-label">Estado Actividad</label>
                <select name="estado_actividad" id="estado_actividad" class="form-select" required>
                    <option value="ABIERTA" <?= ($record['estado_actividad'] == 'ABIERTA') ? 'selected' : '' ?>>ABIERTA</option>
                    <option value="CERRADA" <?= ($record['estado_actividad'] == 'CERRADA') ? 'selected' : '' ?>>CERRADA</option>
                    <option value="GESTIONANDO" <?= ($record['estado_actividad'] == 'GESTIONANDO') ? 'selected' : '' ?>>GESTIONANDO</option>
                </select>
            </div>

            <!-- Porcentaje Avance -->
            <div class="mb-3">
                <label for="porcentaje_avance" class="form-label">Porcentaje Avance</label>
                <input type="number" name="porcentaje_avance" id="porcentaje_avance" class="form-control" step="0.01" min="0" max="100" value="<?= esc($record['porcentaje_avance']) ?>">
            </div>

            <!-- Observaciones -->
            <div class="mb-3">
                <label for="observaciones" class="form-label">Observaciones</label>
                <textarea name="observaciones" id="observaciones" class="form-control" rows="3"><?= esc($record['observaciones']) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Actualizar Registro</button>
            <a href="<?= site_url('/pta-cliente-nueva/list?' . http_build_query($filters ?? [])) ?>" class="btn btn-secondary">Cancelar</a>
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