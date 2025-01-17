<!-- app/Views/consultant/additemdashboard.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Add Dashboard Item</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Add Dashboard Item</h2>
    <form action="<?= site_url('consultant/additemdashboardpost') ?>" method="post">
        <div class="mb-3">
            <label>Rol</label>
            <input type="text" name="rol" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Tipo de Proceso</label>
            <input type="text" name="tipo_proceso" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Detalle</label>
            <input type="text" name="detalle" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Descripción/Funcionalidad</label>
            <textarea name="descripcion" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label>Acción URL</label>
            <input type="text" name="accion_url" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Orden</label>
            <input type="number" name="orden" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Add Item</button>
        <a href="<?= site_url('consultant/listitemdashboard') ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
