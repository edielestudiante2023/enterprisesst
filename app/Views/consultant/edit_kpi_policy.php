<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Política de KPI</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">

  <div class="container mt-5">

    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
      <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 20px;">

        <!-- Logo izquierdo -->
        <div>
          <a href="https://dashboard.cycloidtalent.com/login">
            <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;">
          </a>
        </div>

        <!-- Logo centro -->
        <div>
          <a href="https://cycloidtalent.com/index.php/consultoria-sst">
            <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;">
          </a>
        </div>

        <!-- Logo derecho -->
        <div>
          <a href="https://cycloidtalent.com/">
            <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;">
          </a>
        </div>

        <!-- Botón -->
        <div style="text-align: center;">
          <h2 style="margin: 0; font-size: 16px;">Ir a Dashboard</h2>
          <a href="<?= base_url('/dashboardconsultant') ?>" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;">Ir a DashBoard</a>
        </div>
      </div>
    </nav>

    <!-- Espaciado para evitar que el contenido se oculte bajo el navbar fijo -->
    <div style="height: 160px;"></div>

    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="card-title text-center text-primary">Editar Política de KPI</h2>
        <form action="<?= base_url('editKpiPolicyPost/' . $kpiPolicy['id_kpi_policy']) ?>" method="post">
          <div class="form-group">
            <label for="policy_kpi_definition" class="font-weight-bold">Definición de la Política:</label>
            <input type="text" class="form-control" name="policy_kpi_definition" id="policy_kpi_definition" value="<?= esc($kpiPolicy['policy_kpi_definition']) ?>" required>
          </div>
          <div class="form-group">
            <label for="policy_kpi_comments" class="font-weight-bold">Comentarios sobre la Política:</label>
            <input type="text" class="form-control" name="policy_kpi_comments" id="policy_kpi_comments" value="<?= esc($kpiPolicy['policy_kpi_comments']) ?>">
          </div>
          <div class="text-center mt-3">
            <input type="submit" class="btn btn-primary" value="Actualizar Política de KPI">
          </div>
        </form>
      </div>
    </div>

  </div>

  <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
      <!-- Company and Rights -->
      <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
      <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
      <p style="margin: 5px 0;">NIT: 901.653.912</p>

      <!-- Website Link -->
      <p style="margin: 5px 0;">
        Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" style="color: #007BFF; text-decoration: none;">https://cycloidtalent.com/</a>
      </p>

      <!-- Social Media Links -->
      <p style="margin: 15px 0 5px;"><strong>Nuestras Redes Sociales:</strong></p>
      <div style="display: flex; gap: 15px; justify-content: center;">
        <a href="https://www.facebook.com/CycloidTalent" target="_blank" style="color: #3A3F51; text-decoration: none;">
          <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
        </a>
        <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" style="color: #3A3F51; text-decoration: none;">
          <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
        </a>
        <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" style="color: #3A3F51; text-decoration: none;">
          <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
        </a>
        <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" style="color: #3A3F51; text-decoration: none;">
          <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
        </a>
      </div>
    </div>
  </footer>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>
