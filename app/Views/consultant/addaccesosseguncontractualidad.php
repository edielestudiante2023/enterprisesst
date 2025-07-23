<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agregar Relación</title>
  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>
<body>
  <div class="container mt-5">
    <h1>Agregar Relación</h1>
    <form action="<?= base_url('accesosseguncontractualidad/add') ?>" method="post">
      <div class="mb-3">
        <label for="id_estandar" class="form-label">Estándar</label>
        <select name="id_estandar" id="id_estandar" class="form-control" required>
          <option value=""></option>
          <?php foreach ($estandares as $estandar): ?>
            <option value="<?= esc($estandar['id_estandar']) ?>"><?= esc($estandar['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label for="id_acceso" class="form-label">Acceso</label>
        <select name="id_acceso" id="id_acceso" class="form-control" required>
          <option value=""></option>
          <?php foreach ($accesos as $acceso): ?>
            <option value="<?= esc($acceso['id_acceso']) ?>"><?= esc($acceso['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Guardar</button>
      <a href="<?= base_url('accesosseguncontractualidad/list') ?>" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>

  <!-- Bootstrap 5 JS (no jQuery dependency) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- jQuery (necesario para Select2) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    $(document).ready(function() {
      $('#id_estandar').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Seleccione un estándar',
        allowClear: true
      });
      $('#id_acceso').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Seleccione un acceso',
        allowClear: true
      });
    });
  </script>
</body>
</html>
