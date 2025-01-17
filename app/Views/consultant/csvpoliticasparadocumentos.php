<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Subir Archivo CSV - Políticas de Clientes</title>
  <!-- Incluimos el CSS de Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container my-5">
    <h1 class="mb-4 text-center">Subir Archivo CSV - Políticas de Clientes</h1>
    
    <!-- Mensajes de éxito o error con alertas de Bootstrap -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php elseif (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <div class="card shadow-sm">
      <div class="card-body">
        <form action="<?= base_url('consultant/csvpoliticasparadocumentos/upload') ?>" method="post" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="file" class="form-label">Seleccione un archivo CSV:</label>
            <input type="file" class="form-control" name="file" id="file" accept=".csv" required>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Subir Archivo</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Incluimos el JS de Bootstrap 5 -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
