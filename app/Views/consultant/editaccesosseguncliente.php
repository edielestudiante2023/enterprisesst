<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Acceso</title>
  <!-- Cargar Bootstrap 5 CSS desde un CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
  <div class="container mt-5">
    <h1>Editar Acceso</h1>
    <!-- Uso de base_url() para rutas internas -->
    <form action="<?= base_url('accesosseguncliente/edit') ?>" method="post">
      <!-- ID del acceso (campo oculto) -->
      <input type="hidden" name="id_acceso" value="<?= esc($acceso['id_acceso']) ?>">

      <div class="mb-3">
        <label for="nombre" class="form-label">Nombre</label>
        <input 
          type="text" 
          class="form-control" 
          id="nombre" 
          name="nombre" 
          value="<?= esc($acceso['nombre']) ?>" 
          required>
      </div>

      <div class="mb-3">
        <label for="url" class="form-label">URL</label>
        <input 
          type="text" 
          class="form-control" 
          id="url" 
          name="url" 
          value="<?= esc($acceso['url']) ?>" 
          required>
      </div>

      <div class="mb-3">
        <label for="dimension" class="form-label">Dimensión</label>
        <input 
          type="text" 
          class="form-control" 
          id="dimension" 
          name="dimension" 
          value="<?= esc($acceso['dimension']) ?>" 
          required>
      </div>

      <button type="submit" class="btn btn-primary">Guardar Cambios</button>
      <a href="<?= base_url('accesosseguncliente/list') ?>" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>

  <!-- Cargar Bootstrap 5 JS desde un CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
