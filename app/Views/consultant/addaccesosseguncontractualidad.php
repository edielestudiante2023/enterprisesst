<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agregar Relación</title>
  <!-- Carga de Bootstrap 5 CSS desde un CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
  <div class="container mt-5">
    <h1>Agregar Relación</h1>
    <!-- Uso de base_url() para rutas internas -->
    <form action="<?= base_url('accesosseguncontractualidad/add') ?>" method="post">
      <div class="mb-3">
        <label for="id_estandar" class="form-label">Estándar</label>
        <select name="id_estandar" id="id_estandar" class="form-control" required>
          <?php foreach ($estandares as $estandar): ?>
            <option value="<?= esc($estandar['id_estandar']) ?>"><?= esc($estandar['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label for="id_acceso" class="form-label">Acceso</label>
        <select name="id_acceso" id="id_acceso" class="form-control" required>
          <?php foreach ($accesos as $acceso): ?>
            <option value="<?= esc($acceso['id_acceso']) ?>"><?= esc($acceso['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Guardar</button>
      <!-- Enlace interno utilizando base_url() -->
      <a href="<?= base_url('accesosseguncontractualidad/list') ?>" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
  <!-- Carga de Bootstrap 5 JS desde un CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
