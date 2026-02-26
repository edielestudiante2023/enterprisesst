<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Mantenimientos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Lista de Mantenimientos</h1>

        <!-- Boton para agregar mantenimiento -->
        <a href="<?= base_url('mantenimientos/add') ?>" class="btn btn-primary mb-3">Agregar Mantenimiento</a>

        <!-- Tabla de mantenimientos -->
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Detalle</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mantenimientos as $mantenimiento): ?>
                <tr>
                    <td><?= $mantenimiento['id_mantenimiento'] ?></td>
                    <td><?= $mantenimiento['detalle_mantenimiento'] ?></td>
                    <td>
                        <!-- Boton para editar -->
                        <a href="<?= base_url('mantenimientos/edit/'.$mantenimiento['id_mantenimiento']) ?>" class="btn btn-warning btn-sm">Editar</a>

                        <!-- Boton para eliminar con confirmacion -->
                        <a href="<?= base_url('mantenimientos/delete/'.$mantenimiento['id_mantenimiento']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Estas seguro de eliminar este mantenimiento?')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap 5 JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
