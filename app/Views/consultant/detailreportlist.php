<!-- app/Views/consultant/detailreportlist.php -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Detalles de Reporte</title>
    <!-- Bootstrap 4.5 CSS (manteniendo tu versión original) -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Lista de Detalles de Reporte</h2>

    <!-- Usando base_url sin la barra inicial para evitar duplicar la ruta -->
    <a href="<?= base_url('/detailreportadd') ?>" class="btn btn-primary mb-3">Agregar Nuevo</a>

    <table id="detailReportTable" class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Detalle de Reporte</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <!-- Filtro en tfoot -->
        <tfoot>
            <tr>
                <th></th>
                <th>
                    <select id="filterDetailReport" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </th>
                <th></th>
            </tr>
        </tfoot>
        <tbody>
            <?php if($detailReports): ?>
                <?php foreach($detailReports as $report): ?>
                    <tr>
                        <td><?= esc($report['id_detailreport']) ?></td>
                        <td><?= esc($report['detail_report']) ?></td>
                        <td>
                            <!-- Editar -->
                            <a href="<?= base_url('detailreportedit/' . $report['id_detailreport']) ?>" class="btn btn-warning btn-sm">Editar</a>
                            
                            <!-- Eliminar -->
                            <!-- Recomendable usar POST para eliminar, pero respetamos tu estructura -->
                            <a href="<?= base_url('detailreportdelete/' . $report['id_detailreport']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este registro?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No se encontraron registros.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Dependencias JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<!-- DataTables Bootstrap 5 JS -->
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#detailReportTable').DataTable({
            // Ordena por ID en forma descendente (más reciente primero)
            "order": [[0, "desc"]],
            // Traduce DataTables al español
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            initComplete: function() {
                // Filtro en la columna de Detalle de Reporte (índice 1)
                var column = this.api().column(1);
                var select = $('#filterDetailReport');
                
                // Crea opciones únicas según los datos
                column.data().unique().sort().each(function(d) {
                    select.append('<option value="'+ d +'">'+ d +'</option>');
                });
                
                // Aplica el filtro al cambiar el select
                select.on('change', function() {
                    var val = $.fn.dataTable.util.escapeRegex($(this).val());
                    column.search(val ? '^'+ val +'$' : '', true, false).draw();
                });
            }
        });
    });
</script>
</body>
</html>
