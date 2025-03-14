<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Tipo de Documento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>

<body class="bg-light text-dark">


    <div class="container my-5">
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

        <!-- Ajustar el espaciado para evitar que el contenido se oculte bajo el navbar fijo -->
        <div style="height: 160px;"></div>

        <div class="card shadow-sm p-4 mb-5 bg-white rounded">
            <h1 class="mb-4">Editar Tipo de Documento</h1>

            <?php if (isset($policyType)): ?>
                <form action="<?= base_url('/editPolicyTypePost/' . $policyType['id']) ?>" method="post">
                    <div class="mb-3">
                        <label for="type_name" class="form-label">Nombre del Tipo de Documento:</label>
                        <input type="text" name="type_name" class="form-control" value="<?= $policyType['type_name'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción del Tipo de Documento:</label>
                        <input type="text" name="description" class="form-control" value="<?= $policyType['description'] ?>">
                    </div>
                    <button type="submit" class="btn btn-success">Actualizar</button>
                </form>
            <?php else: ?>
                <div class="alert alert-danger" role="alert">
                    Error: No se pudo cargar la información del tipo de política.
                </div>
            <?php endif; ?>
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

    <script>
        $(document).ready(function() {
            $('table').DataTable();
        });
    </script>
</body>

</html>