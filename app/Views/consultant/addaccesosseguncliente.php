<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agregar Acceso</title>
  <!-- Cargar Bootstrap 5 CSS desde CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
  <div class="container mt-5">
    <div class="card shadow-lg">
      <div class="card-header bg-primary text-white">
        <h1 class="h4 mb-0">Agregar Acceso</h1>
      </div>
      <div class="card-body">
        <!-- Uso de base_url() para la ruta interna del formulario -->
        <form action="<?= base_url('accesosseguncliente/add') ?>" method="post">
          <!-- Nombre -->
          <div class="mb-3">
            <label for="nombre" class="form-label fw-bold">Nombre</label>
            <input 
              type="text" 
              class="form-control" 
              id="nombre" 
              name="nombre" 
              placeholder="Ingrese el nombre del acceso" 
              required>
          </div>

          <!-- URL -->
          <div class="mb-3">
            <label for="url" class="form-label fw-bold">URL</label>
            <input 
              type="text" 
              class="form-control" 
              id="url" 
              name="url" 
              placeholder="Ingrese la URL del acceso" 
              required>
          </div>

          <!-- Dimensión -->
          <div class="mb-3">
            <label for="dimension" class="form-label fw-bold">Dimensión</label>
            <input 
              type="text" 
              class="form-control" 
              id="dimension" 
              name="dimension" 
              placeholder="Ingrese la dimensión asociada" 
              required>
          </div>

          <!-- Botones -->
          <div class="d-flex justify-content-end">
            <button type="reset" class="btn btn-secondary me-2">Limpiar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Cargar Bootstrap 5 JS desde CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
