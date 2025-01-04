<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Reporte</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <nav style="background-color: white; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div class="container">
            <a href="<?= base_url('/reportList') ?>" class="btn btn-secondary">Volver a la lista</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2>Agregar Nuevo Reporte</h2>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('msg')) : ?>
                    <div class="alert alert-warning">
                        <?= session()->getFlashdata('msg') ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('/addReportPost') ?>" method="post" enctype="multipart/form-data">
                    <!-- Título del Reporte -->
                    <div class="form-group">
                        <label for="titulo_reporte">Título del Reporte:</label>
                        <input type="text" class="form-control" id="titulo_reporte" name="titulo_reporte" required>
                    </div>

                    <!-- Tipo de Documento -->
                    <div class="form-group">
                        <label for="id_detailreport">Tipo de Documento:</label>
                        <select class="form-control" id="id_detailreport" name="id_detailreport" required>
                            <option value="">Seleccione el tipo de documento</option>
                            <?php foreach ($details as $detail) : ?>
                                <option value="<?= $detail['id_detailreport'] ?>">
                                    <?= $detail['detail_report'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tipo de Reporte -->
                    <div class="form-group">
                        <label for="id_report_type">Tipo de Reporte:</label>
                        <select class="form-control" id="id_report_type" name="id_report_type" required>
                            <option value="">Seleccione el tipo de reporte</option>
                            <?php foreach ($reportTypes as $type) : ?>
                                <option value="<?= $type['id_report_type'] ?>">
                                    <?= $type['report_type'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Archivo -->
                    <div class="form-group">
                        <label for="archivo">Archivo:</label>
                        <input type="file" class="form-control" id="archivo" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png" required>
                    </div>

                    <!-- Estado -->
                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <select class="form-control" id="estado" name="estado" required>
                            <option value="ABIERTO">ABIERTO</option>
                            <option value="GESTIONANDO">GESTIONANDO</option>
                            <option value="CERRADO">CERRADO</option>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="form-group">
                        <label for="observaciones">Observaciones:</label>
                        <textarea class="form-control" id="observaciones" name="observaciones"></textarea>
                    </div>

                    <!-- Cliente -->
                    <div class="form-group">
                        <label for="id_cliente">Cliente:</label>
                        <select class="form-control" id="id_cliente" name="id_cliente" required>
                            <option value="">Seleccione el cliente</option>
                            <?php foreach ($clients as $client) : ?>
                                <option value="<?= $client['id_cliente'] ?>">
                                    <?= $client['nombre_cliente'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Agregar Reporte</button>
                </form>
            </div>
        </div>
    </div>

    <footer class="text-center mt-5">
        <p>© 2025 Cycloid Talent SAS - Todos los derechos reservados</p>
    </footer>
</body>

</html>
