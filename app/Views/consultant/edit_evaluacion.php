<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Evaluación</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
        <h2 class="mb-4 text-center">Editar Evaluación</h2>

        <form action="<?= base_url('editEvaluacionPost/' . $evaluacion['id_ev_ini']) ?>" method="post" class="p-4 bg-white shadow-sm rounded">

            <!-- Selección del cliente -->
            <div class="form-group">
                <label for="id_cliente">Cliente:</label>
                <select class="form-control" name="id_cliente" id="id_cliente" required>
                    <?php foreach ($clients as $cliente): ?>
                        <option value="<?= esc($cliente['id_cliente']) ?>" <?= $cliente['id_cliente'] == $evaluacion['id_cliente'] ? 'selected' : '' ?>>
                            <?= esc($cliente['nombre_cliente']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Otros campos de la evaluación -->
            <div class="form-group">
                <label for="ciclo">Ciclo:</label>
                <input type="text" class="form-control" name="ciclo" id="ciclo" value="<?= esc($evaluacion['ciclo']) ?>" required>
            </div>

            <div class="form-group">
                <label for="estandar">Estandar:</label>
                <input type="text" class="form-control" name="estandar" id="estandar" value="<?= esc($evaluacion['estandar']) ?>" required>
            </div>

            <div class="form-group">
                <label for="detalle_estandar">Detalle del Estándar:</label>
                <textarea class="form-control" name="detalle_estandar" id="detalle_estandar" rows="3" required><?= esc($evaluacion['detalle_estandar']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="estandares_minimos">Estándares Mínimos:</label>
                <input type="text" class="form-control" name="estandares_minimos" id="estandares_minimos" value="<?= esc($evaluacion['estandares_minimos']) ?>" required>
            </div>

            <div class="form-group">
                <label for="numeral">Numeral:</label>
                <input type="text" class="form-control" name="numeral" id="numeral" value="<?= esc($evaluacion['numeral']) ?>" required>
            </div>

            <div class="form-group">
                <label for="numerales_del_cliente">Numerales del Cliente:</label>
                <input type="text" class="form-control" name="numerales_del_cliente" id="numerales_del_cliente" value="<?= esc($evaluacion['numerales_del_cliente']) ?>" required>
            </div>

            <div class="form-group">
                <label for="siete">Siete:</label>
                <input type="text" class="form-control" name="siete" id="siete" value="<?= esc($evaluacion['siete']) ?>">
            </div>

            <div class="form-group">
                <label for="veintiun">Veintiún:</label>
                <input type="text" class="form-control" name="veintiun" id="veintiun" value="<?= esc($evaluacion['veintiun']) ?>">
            </div>

            <div class="form-group">
                <label for="sesenta">Sesenta:</label>
                <input type="text" class="form-control" name="sesenta" id="sesenta" value="<?= esc($evaluacion['sesenta']) ?>">
            </div>

            <div class="form-group">
                <label for="item_del_estandar">Item del Estándar:</label>
                <input type="text" class="form-control" name="item_del_estandar" id="item_del_estandar" value="<?= esc($evaluacion['item_del_estandar']) ?>" required>
            </div>

            <div class="form-group">
                <label for="evaluacion_inicial">Evaluación Inicial:</label>
                <select class="form-control" name="evaluacion_inicial" id="evaluacion_inicial" required>
                    <option value="CUMPLE TOTALMENTE" <?= $evaluacion['evaluacion_inicial'] == 'CUMPLE TOTALMENTE' ? 'selected' : '' ?>>CUMPLE TOTALMENTE</option>
                    <option value="NO CUMPLE" <?= $evaluacion['evaluacion_inicial'] == 'NO CUMPLE' ? 'selected' : '' ?>>NO CUMPLE</option>
                    <option value="NO APLICA" <?= $evaluacion['evaluacion_inicial'] == 'NO APLICA' ? 'selected' : '' ?>>NO APLICA</option>
                </select>
            </div>

            <div class="form-group">
                <label for="valor">Valor:</label>
                <input type="text" class="form-control" name="valor" id="valor" value="<?= esc($evaluacion['valor']) ?>" required>
            </div>

            <div class="form-group">
                <label for="puntaje_cuantitativo">Puntaje Cuantitativo:</label>
                <input type="number" step="0.01" class="form-control" name="puntaje_cuantitativo" id="puntaje_cuantitativo" value="<?= esc($evaluacion['puntaje_cuantitativo']) ?>" required>
            </div>

            <div class="form-group">
                <label for="item">Item:</label>
                <input type="text" class="form-control" name="item" id="item" value="<?= esc($evaluacion['item']) ?>" required>
            </div>

            <div class="form-group">
                <label for="criterio">Criterio:</label>
                <textarea class="form-control" name="criterio" id="criterio" rows="3" required><?= esc($evaluacion['criterio']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="modo_de_verificacion">Modo de Verificación:</label>
                <textarea class="form-control" name="modo_de_verificacion" id="modo_de_verificacion" rows="3" required><?= esc($evaluacion['modo_de_verificacion']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="calificacion">Calificación:</label>
                <input type="text" class="form-control" name="calificacion" id="calificacion" value="<?= esc($evaluacion['calificacion']) ?>">
            </div>

            <div class="form-group">
                <label for="nivel_de_evaluacion">Nivel de Evaluación:</label>
                <input type="text" class="form-control" name="nivel_de_evaluacion" id="nivel_de_evaluacion" value="<?= esc($evaluacion['nivel_de_evaluacion']) ?>">
            </div>

            <div class="form-group">
                <label for="observaciones">Observaciones:</label>
                <textarea class="form-control" name="observaciones" id="observaciones" rows="3"><?= esc($evaluacion['observaciones']) ?></textarea>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="<?= base_url('listEvaluaciones') ?>" class="btn btn-secondary">Cancelar</a>
            </div>

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

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>