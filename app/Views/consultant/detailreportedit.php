<!DOCTYPE html>
<html>
<head>
    <title>Editar Detalle de Reporte</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>

<body>
<div class="container mt-5">
    <h2>Editar Detalle de Reporte</h2>

    <?php if(isset($validation)): ?>
        <div class="alert alert-danger">
            <?= $validation->listErrors() ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('/detailreportedit') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="id_detailreport" value="<?= esc($detailReport['id_detailreport']) ?>">
        <div class="form-group">
            <label for="detail_report">Detalle de Reporte</label>
            <input type="text" name="detail_report" class="form-control" id="detail_report" value="<?= esc($detailReport['detail_report']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="<?= base_url('/detailreportlist') ?>" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>
