<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Estándar</title>
  <!-- Cargar Bootstrap 5 CSS desde un CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
  <div class="container mt-5">
    <h1>Editar Estándar</h1>
    <!-- Uso de base_url() para rutas internas -->
    <form action="<?= base_url('estandarcontractual/edit') ?>" method="post">
      <!-- Campo oculto para el ID -->
      <input type="hidden" name="id_estandar" value="<?= esc($estandar['id_estandar']) ?>">

      <div class="mb-3">
        <label for="nombre" class="form-label">Nombre</label>
        <input 
          type="text" 
          class="form-control" 
          id="nombre" 
          name="nombre" 
          value="<?= esc($estandar['nombre']) ?>" 
          required>
      </div>

      <button type="submit" class="btn btn-primary">Guardar Cambios</button>
      <a href="<?= base_url('estandarcontractual/list') ?>" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>

  <!-- Cargar Bootstrap 5 JS desde un CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
