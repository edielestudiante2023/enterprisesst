<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Consultor</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

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

    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h2 class="text-center">Editar Consultor</h2>
            </div>

            <div class="card-body">
                <?php if (session()->getFlashdata('msg')): ?>
                    <div class="alert alert-warning text-center">
                        <?= session()->getFlashdata('msg') ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('/editConsultantPost/' . $consultant['id_consultor']) ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <!-- Campos del formulario -->
                    <div class="mb-3">
                        <label for="nombre_consultor" class="form-label">Nombre Consultor:</label>
                        <input type="text" class="form-control" id="nombre_consultor" name="nombre_consultor" value="<?= $consultant['nombre_consultor'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="cedula_consultor" class="form-label">Cédula Consultor:</label>
                        <input type="text" class="form-control" id="cedula_consultor" name="cedula_consultor" value="<?= $consultant['cedula_consultor'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuario:</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" value="<?= $consultant['usuario'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="correo_consultor" class="form-label">Correo Consultor:</label>
                        <input type="email" class="form-control" id="correo_consultor" name="correo_consultor" value="<?= $consultant['correo_consultor'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="telefono_consultor" class="form-label">Teléfono Consultor:</label>
                        <input type="text" class="form-control" id="telefono_consultor" name="telefono_consultor" value="<?= $consultant['telefono_consultor'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="numero_licencia" class="form-label">Número de Licencia:</label>
                        <input type="text" class="form-control" id="numero_licencia" name="numero_licencia" value="<?= $consultant['numero_licencia'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="foto_consultor" class="form-label">Foto del Consultor:</label>
                        <input type="file" class="form-control" id="foto_consultor" name="foto_consultor" accept="image/*">
                        <?php if (!empty($consultant['foto_consultor'])): ?>
                            <div class="mt-2">
                                <img src="<?= base_url('uploads/' . $consultant['foto_consultor']) ?>" alt="Foto del Consultor" class="img-thumbnail" width="100">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="firma_consultor" class="form-label">Firma del Consultor:</label>
                        <input type="file" class="form-control" id="firma_consultor" name="firma_consultor" accept="image/*">
                        <?php if (!empty($consultant['firma_consultor'])): ?>
                            <div class="mt-2">
                                <img src="<?= base_url('uploads/' . $consultant['firma_consultor']) ?>" alt="Firma del Consultor" class="img-thumbnail" width="100">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="rol" class="form-label">Rol:</label>
                        <select name="rol" id="rol" class="form-select" required aria-label="Seleccionar Rol">
                            <option value="consultant" <?= $consultant['rol'] === 'consultant' ? 'selected' : '' ?>>Consultor</option>
                            <option value="admin" <?= $consultant['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary mt-3">Actualizar Consultor</button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <a href="<?= base_url('/listConsultants') ?>" class="btn btn-secondary">Volver a la lista de consultores</a>
                </div>
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

    <!-- Bootstrap JS and dependencies (Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <!-- Opcional: Validación de Bootstrap -->
    <script>
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>

</html>