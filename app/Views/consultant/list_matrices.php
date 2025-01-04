<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Matrices</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <!-- Título y Botón Agregar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">Lista de Matrices</h1>
            <a href="<?= base_url('matrices/add') ?>" class="btn btn-success">Agregar Enlace de Matriz o Carpeta</a>
        </div>

        <!-- Tabla de Matrices -->
        <table id="datatable" class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Observaciones</th>
                    <th>Enlace</th>
                    <th>Cliente</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matrices as $matriz): ?>
                    <tr>
                        <td><?= $matriz['id_matriz'] ?></td>
                        <td><?= htmlspecialchars($matriz['tipo'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($matriz['descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($matriz['observaciones'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="<?= htmlspecialchars($matriz['enlace'], ENT_QUOTES, 'UTF-8') ?>" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="btn btn-link text-decoration-none">Ver</a>
                        </td>
                        <td>
                            <!-- Mostrar el nombre del cliente utilizando array_search -->
                            <?= $clients[array_search($matriz['id_cliente'], array_column($clients, 'id_cliente'))]['nombre_cliente'] ?>
                        </td>
                        <td>
                            <a href="<?= base_url('matrices/edit/' . $matriz['id_matriz']) ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="<?= base_url('matrices/delete/' . $matriz['id_matriz']) ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('¿Estás seguro de eliminar este registro?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTable con traducción y configuración
            $('#datatable').DataTable({
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" // Traducción al español
                },
                responsive: true, // Habilitar tabla responsiva
                lengthMenu: [5, 10, 25, 50], // Opciones de filas por página
                pageLength: 10 // Número inicial de filas por página
            });
        });
    </script>
</body>
</html>
