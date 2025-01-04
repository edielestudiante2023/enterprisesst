<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>

<body style="background-color: #f8f9fa;">

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
        <h2 class="mb-4">Editar Cliente</h2>

        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('msg') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('/updateClient/' . $client['id_cliente']) ?>" method="post" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">
            <div class="mb-3">
                <label class="form-label">Fecha de Ingreso:</label>
                <input type="date" name="fecha_ingreso" value="<p>FECHA: <strong><?= strftime('%d de %B de %Y', strtotime($client['fecha_ingreso'])); ?></strong>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">NIT Cliente:</label>
                <input type="text" name="nit_cliente" value="<?= $client['nit_cliente'] ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Nombre Cliente:</label>
                <input type="text" name="nombre_cliente" value="<?= $client['nombre_cliente'] ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Usuario:</label>
                <input type="text" name="usuario" value="<?= $client['usuario'] ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Correo Cliente:</label>
                <input type="email" name="correo_cliente" value="<?= $client['correo_cliente'] ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Teléfono 1:</label>
                <input type="text" name="telefono_1_cliente" value="<?= $client['telefono_1_cliente'] ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Teléfono 2:</label>
                <input type="text" name="telefono_2_cliente" value="<?= isset($client['telefono_2_cliente']) ? $client['telefono_2_cliente'] : '' ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Dirección:</label>
                <input type="text" name="direccion_cliente" value="<?= $client['direccion_cliente'] ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Persona de Contacto para Compras:</label>
                <input type="text" name="persona_contacto_compras" value="<?= $client['persona_contacto_compras'] ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Código de Actividad Económica:</label>
                <input type="text" name="codigo_actividad_economica" value="<?= $client['codigo_actividad_economica'] ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Nombre del Representante Legal:</label>
                <input type="text" name="nombre_rep_legal" value="<?= $client['nombre_rep_legal'] ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Cédula del Representante Legal:</label>
                <input type="text" name="cedula_rep_legal" value="<?= $client['cedula_rep_legal'] ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Fecha Fin de Contrato:</label>
                <input type="date" name="fecha_fin_contrato" value="<?= $client['fecha_fin_contrato'] ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Ciudad:</label>
                <input type="text" name="ciudad_cliente" value="<?= $client['ciudad_cliente'] ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Estado:</label>
                <select name="estado" class="form-select">
                    <option value="activo" <?= $client['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= $client['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    <option value="pendiente" <?= $client['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Consultor Asignado:</label>
                <select name="id_consultor" class="form-select">
                    <?php foreach ($consultants as $consultant) : ?>
                        <option value="<?= $consultant['id_consultor'] ?>" <?= $consultant['id_consultor'] == $client['id_consultor'] ? 'selected' : '' ?>>
                            <?= $consultant['nombre_consultor'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Logo:</label>
                <input type="file" name="logo" class="form-control">
                <?php if ($client['logo']): ?>
                    <img src="<?= base_url('uploads/' . $client['logo']) ?>" alt="Logo" width="100" class="mt-2">
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Firma Representante Legal:</label>
                <input type="file" name="firma_representante_legal" class="form-control">
                <?php if ($client['firma_representante_legal']): ?>
                    <img src="<?= base_url('uploads/' . $client['firma_representante_legal']) ?>" alt="Firma" width="100" class="mt-2">
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Tipo de Servicio:</label>
                <select name="estandares" class="form-select">
                    <option value="7A" <?= $client['estandares'] == '7A' ? 'selected' : '' ?>>7A</option>
                    <option value="7B" <?= $client['estandares'] == '7B' ? 'selected' : '' ?>>7B</option>
                    <option value="7C" <?= $client['estandares'] == '7C' ? 'selected' : '' ?>>7C</option>
                    <option value="7D" <?= $client['estandares'] == '7D' ? 'selected' : '' ?>>7D</option>
                    <option value="7E" <?= $client['estandares'] == '7E' ? 'selected' : '' ?>>7E</option>
                    <option value="21A" <?= $client['estandares'] == '21A' ? 'selected' : '' ?>>21A</option>
                    <option value="21B" <?= $client['estandares'] == '21B' ? 'selected' : '' ?>>21B</option>
                    <option value="21C" <?= $client['estandares'] == '21C' ? 'selected' : '' ?>>21C</option>
                    <option value="21D" <?= $client['estandares'] == '21D' ? 'selected' : '' ?>>21D</option>
                    <option value="21E" <?= $client['estandares'] == '21E' ? 'selected' : '' ?>>21E</option>
                    <option value="60A" <?= $client['estandares'] == '60A' ? 'selected' : '' ?>>60A</option>
                    <option value="60B" <?= $client['estandares'] == '60B' ? 'selected' : '' ?>>60B</option>
                    <option value="60C" <?= $client['estandares'] == '60C' ? 'selected' : '' ?>>60C</option>
                    <option value="60D" <?= $client['estandares'] == '60D' ? 'selected' : '' ?>>60D</option>
                    <option value="60E" <?= $client['estandares'] == '60E' ? 'selected' : '' ?>>60E</option>
                </select>

            </div>

            <button type="submit" class="btn btn-primary">Actualizar Cliente</button>
        </form>


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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
</body>

</html>