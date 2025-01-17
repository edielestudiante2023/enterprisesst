<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Subir CSV - Versiones de Documentos</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card shadow-sm">
          <div class="card-body">
            <h1 class="card-title mb-4 text-center">Subir archivo CSV para Versiones de Documentos</h1>
            <form action="<?= base_url('consultant/csvversionesdocumentos/upload') ?>" method="post" enctype="multipart/form-data">
              <?= csrf_field() ?>
              <div class="mb-3">
                <label for="file" class="form-label">Archivo CSV</label>
                <input type="file" name="file" id="file" class="form-control" accept=".csv" required>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Subir y Procesar</button>
              </div>
            </form>
            <!-- Mensajes de alerta -->
            <?php if(session()->getFlashdata('error')): ?>
              <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
              </div>
            <?php elseif(session()->getFlashdata('success')): ?>
              <div class="alert alert-success mt-3 alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- JS de Bootstrap 5 -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
