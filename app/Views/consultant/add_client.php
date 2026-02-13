<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
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


    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Agregar Nuevo Cliente</h2>
            
        </div>

        <?php if (session()->getFlashdata('msg')) : ?>
            <div class="alert alert-warning">
                <?= session()->getFlashdata('msg') ?>
            </div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body">
                <form action="<?= base_url('/addClientPost') ?>" method="post" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label class="form-label">Fecha Inicio del Contrato:</label>
                        <input type="date" name="fecha_ingreso" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nit Cliente:</label>
                        <input type="text" name="nit_cliente" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre del Tercero (Como está en el RUT):</label>
                        <input type="text" name="nombre_cliente" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Usuario:</label>
                        <input type="text" name="usuario" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password:</label>
                        <div class="input-group">
                            <input type="password" name="password" id="passwordField" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye-slash" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Correo del Cliente:</label>
                        <input type="email" name="correo_cliente" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Teléfono 1:</label>
                        <input type="text" name="telefono_1_cliente" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Teléfono 2:</label>
                        <input type="text" name="telefono_2_cliente" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dirección:</label>
                        <input type="text" name="direccion_cliente" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Persona de Contacto para Compras:</label>
                        <input type="text" name="persona_contacto_compras" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Código de Actividad Económica:</label>
                        <input type="text" name="codigo_actividad_economica" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre del Representante Legal:</label>
                        <input type="text" name="nombre_rep_legal" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cédula del Representante Legal:</label>
                        <input type="text" name="cedula_rep_legal" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha Fin de Contrato:</label>
                        <input type="date" name="fecha_fin_contrato" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ciudad del Cliente:</label>
                        <input type="text" name="ciudad_cliente" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado:</label>
                        <select name="estado" class="form-select" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="pendiente">Pendiente</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ID Consultor:</label>
                        <select name="id_consultor" class="form-select" required>
                            <option value="1" selected>Seleccione un Consultor</option>
                            <?php foreach ($consultants as $consultant) : ?>
                                <?php if ($consultant['id_consultor'] != 1) : ?>
                                    <option value="<?= $consultant['id_consultor'] ?>"><?= $consultant['nombre_consultor'] ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Logo:</label>
                        <input type="file" name="logo" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Firma del Representante Legal:</label>
                        <input type="file" name="firma_representante_legal" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo de Servicio:</label>
                        <select name="estandares" class="form-select">
                            <option value="7A">7A</option>
                            <option value="7B">7B</option>
                            <option value="7C">7C</option>
                            <option value="7D">7D</option>
                            <option value="7E">7E</option>
                            <option value="21A">21A</option>
                            <option value="21B">21B</option>
                            <option value="21C">21C</option>
                            <option value="21D">21D</option>
                            <option value="21E">21E</option>
                            <option value="60A">60A</option>
                            <option value="60B">60B</option>
                            <option value="60C">60C</option>
                            <option value="60D">60D</option>
                            <option value="60E">60E</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Agregar Cliente</button>
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


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const field = document.getElementById('passwordField');
            const icon = document.getElementById('toggleIcon');
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            } else {
                field.type = 'password';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            }
        });
    </script>
</body>

</html>