<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pendiente</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
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
        <h2 class="text-center mb-4 text-dark">Editar Pendiente</h2>

        <!-- Mensaje de error o éxito -->
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>

        <form action="<?= base_url('/editPendientePost/' . $pendiente['id_pendientes']) ?>" method="post" class="p-4 shadow bg-white rounded">
            <div class="form-group">
                <label for="id_cliente" class="font-weight-bold">Cliente:</label>
                <select name="id_cliente" id="id_cliente" class="form-control" required>
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($clientes as $cliente) : ?>
                        <option value="<?= $cliente['id_cliente'] ?>" <?= ($cliente['id_cliente'] == $pendiente['id_cliente']) ? 'selected' : '' ?>>
                            <?= $cliente['nombre_cliente'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="responsable" class="font-weight-bold">Responsable:</label>
                <select name="responsable" id="responsable" class="form-control" required>
                    <option value="">Seleccione un responsable</option>
                    <option value="ADMINISTRADOR" <?= ($pendiente['responsable'] == 'ADMINISTRADOR') ? 'selected' : '' ?>>ADMINISTRADOR</option>
                    <option value="EDISON CUERVO" <?= ($pendiente['responsable'] == 'EDISON CUERVO') ? 'selected' : '' ?>>EDISON CUERVO</option>
                    <option value="ELEYSON SEGURA" <?= ($pendiente['responsable'] == 'ELEYSON SEGURA') ? 'selected' : '' ?>>ELEYSON SEGURA</option>
                    <option value="NATALIA JIMENEZ" <?= ($pendiente['responsable'] == 'NATALIA JIMENEZ') ? 'selected' : '' ?>>NATALIA JIMENEZ</option>
                    <option value="DIANA CUESTAS" <?= ($pendiente['responsable'] == 'DIANA CUESTAS') ? 'selected' : '' ?>>DIANA CUESTAS</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tarea_actividad" class="font-weight-bold">Tarea Actividad:</label>
                <textarea class="form-control" id="tarea_actividad" name="tarea_actividad" required><?= $pendiente['tarea_actividad'] ?></textarea>
            </div>

            <div class="form-group">
                <label for="fecha_cierre" class="font-weight-bold">Fecha Cierre:</label>
                <input type="date" class="form-control" id="fecha_cierre" name="fecha_cierre" value="<?= $pendiente['fecha_cierre'] ?>">
            </div>

            <div class="form-group">
                <label for="estado" class="font-weight-bold">Estado:</label>
                <select name="estado" id="estado" class="form-control" required>
                    <option value="ABIERTA" <?= ($pendiente['estado'] == 'ABIERTA') ? 'selected' : '' ?>>ABIERTA</option>
                    <option value="CERRADA" <?= ($pendiente['estado'] == 'CERRADA') ? 'selected' : '' ?>>CERRADA</option>
                </select>
            </div>

            <div class="form-group">
                <label for="conteo_dias" class="font-weight-bold">Conteo Días:</label>
                <input type="number" class="form-control" id="conteo_dias" name="conteo_dias" value="<?= $pendiente['conteo_dias'] ?>" readonly>
            </div>

            <div class="form-group">
                <label for="estado_avance" class="font-weight-bold">Estado Avance:</label>
                <input type="text" class="form-control" id="estado_avance" name="estado_avance" value="<?= $pendiente['estado_avance'] ?>" required>
            </div>

            <div class="form-group">
                <label for="evidencia_para_cerrarla" class="font-weight-bold">Evidencia para Cerrarla:</label>
                <textarea class="form-control" id="evidencia_para_cerrarla" name="evidencia_para_cerrarla"><?= $pendiente['evidencia_para_cerrarla'] ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Guardar Cambios</button>
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

    <!-- Configuración de DataTables si se utiliza en algún lugar de la página -->
    <script>
        $(document).ready(function() {
            $('.table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json"
                }
            });
        });
    </script>
</body>

</html>