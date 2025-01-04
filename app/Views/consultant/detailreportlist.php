<!-- app/Views/consultant/detailreportlist.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Lista de Detalles de Reporte</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Lista de Detalles de Reporte</h2>

  

    <!-- Usando base_url sin la barra inicial para evitar duplicar la ruta -->
    <a href="<?= base_url('/detailreportadd') ?>" class="btn btn-primary mb-3">Agregar Nuevo</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Detalle de Reporte</th>
                <th>Acciones</th>
            </tr>
        </thead>
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
                            <!-- Es recomendable usar POST para eliminar, pero siguiendo tu estructura inicial, utilizamos GET con confirmación -->
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
</body>
</html>
