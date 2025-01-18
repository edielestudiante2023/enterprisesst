<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Actividad - Plan de Trabajo Anual</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            color: #343a40;
        }

        .container {
            max-width: 800px;
            margin-top: 50px;
        }

        h2 {
            font-size: 1.75rem;
            font-weight: bold;
            color: #495057;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
    </style>
</head>

<body>

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
        <h2 class="text-center mb-4">Editar Actividad del Plan de Trabajo Anual</h2>
        <form action="<?= base_url('/editPlanDeTrabajoAnualPost/' . $plan['id_ptacliente']) ?>" method="post">

            <div class="form-group">
                <label for="id_cliente">Cliente:</label>
                <select name="id_cliente" id="id_cliente" class="form-control" required>
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($clientes as $cliente) : ?>
                        <option value="<?= $cliente['id_cliente'] ?>" <?= ($cliente['id_cliente'] == $plan['id_cliente']) ? 'selected' : '' ?>>
                            <?= $cliente['nombre_cliente'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_plandetrabajo">Actividad del Plan de Trabajo:</label>
                <select name="id_plandetrabajo" id="id_plandetrabajo" class="form-control" required>
                    <option value="">Seleccione una actividad</option>
                    <?php foreach ($actividades as $actividad) : ?>
                        <option value="<?= $actividad->id_inventario_actividades_plandetrabajo ?>" <?= ($actividad->id_inventario_actividades_plandetrabajo == $plan['id_plandetrabajo']) ? 'selected' : '' ?>>
                            <?= $actividad->actividad_plandetrabajo ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="phva_plandetrabajo">PHVA:</label>
                <select class="form-control" id="phva_plandetrabajo" name="phva_plandetrabajo" required>
                    <option value="">Seleccione una opción</option>
                    <option value="PLANEAR" <?= ($plan['phva_plandetrabajo'] == 'PLANEAR') ? 'selected' : '' ?>>PLANEAR</option>
                    <option value="HACER" <?= ($plan['phva_plandetrabajo'] == 'HACER') ? 'selected' : '' ?>>HACER</option>
                    <option value="VERIFICAR" <?= ($plan['phva_plandetrabajo'] == 'VERIFICAR') ? 'selected' : '' ?>>VERIFICAR</option>
                    <option value="ACTUAR" <?= ($plan['phva_plandetrabajo'] == 'ACTUAR') ? 'selected' : '' ?>>ACTUAR</option>
                </select>
            </div>

            <div class="form-group">
                <label for="numeral_plandetrabajo">Numeral:</label>
                <input type="text" class="form-control" id="numeral_plandetrabajo" name="numeral_plandetrabajo" value="<?= $plan['numeral_plandetrabajo'] ?>" required>
            </div>
            <div class="form-group">
                <label for="responsable_sugerido_plandetrabajo">Responsable Sugerido:</label>
                <input type="text" class="form-control" id="responsable_sugerido_plandetrabajo" name="responsable_sugerido_plandetrabajo" value="<?= $plan['responsable_sugerido_plandetrabajo'] ?>" required>
            </div>

            <div class="form-group">
                <label for="fecha_propuesta">Fecha Propuesta:</label>
                <input type="date" class="form-control" id="fecha_propuesta" name="fecha_propuesta" value="<?= $plan['fecha_propuesta'] ?>" required>
            </div>
            <div class="form-group">
                <label for="fecha_cierre">Fecha Cierre:</label>
                <input type="date" class="form-control" id="fecha_cierre" name="fecha_cierre" value="<?= $plan['fecha_cierre'] ?>">
            </div>

            <div class="form-group">
                <label for="responsable_definido_paralaactividad">Responsable Definido:</label>
                <input type="text" class="form-control" id="responsable_definido_paralaactividad" name="responsable_definido_paralaactividad" value="<?= $plan['responsable_definido_paralaactividad'] ?>">
            </div>

            <div class="form-group">
                <label for="estado_actividad">Estado de la Actividad:</label>
                <select name="estado_actividad" id="estado_actividad" class="form-control" required>
                    <option value="ABIERTA" <?= ($plan['estado_actividad'] == 'ABIERTA') ? 'selected' : '' ?>>ABIERTA</option>
                    <option value="CERRADA" <?= ($plan['estado_actividad'] == 'CERRADA') ? 'selected' : '' ?>>CERRADA</option>
                </select>
            </div>

            <div class="form-group">
                <label for="porcentaje_avance">Porcentaje de Avance:</label>
                <input type="number" class="form-control" id="porcentaje_avance" name="porcentaje_avance" value="<?= $plan['porcentaje_avance'] ?>" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="semana">Semana:</label>
                <input type="text" class="form-control" id="semana" name="semana" value="<?= $plan['semana'] ?>">
            </div>

            <div class="form-group">
                <label for="observaciones">Observaciones:</label>
                <textarea class="form-control" id="observaciones" name="observaciones"><?= $plan['observaciones'] ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Actualizar Actividad</button>
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

    <script>
        $(document).ready(function() {
            $('table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es_es.json'
                }
            });
        });
    </script>
</body>

</html>