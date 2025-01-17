<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir CSV - KPIs de Empresas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h1>Subir archivo CSV para KPIs de Empresas</h1>
        <form action="<?= base_url('consultant/csvkpisempresas/upload') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="file" class="form-label">Archivo CSV</label>
                <input type="file" name="file" id="file" class="form-control" accept=".csv" required>
            </div>
            <button type="submit" class="btn btn-primary">Subir y Procesar</button>
        </form>
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger mt-3">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php elseif(session()->getFlashdata('success')): ?>
            <div class="alert alert-success mt-3">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
