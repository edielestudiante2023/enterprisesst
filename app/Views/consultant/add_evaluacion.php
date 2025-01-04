<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Nueva Evaluación</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
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
        <h2 class="text-center mb-4">Agregar Nueva Evaluación</h2>

        <form action="<?= base_url('addEvaluacionPost') ?>" method="post" class="p-4 bg-white shadow rounded">

            <!-- Selección del cliente -->
            <div class="form-group">
                <label for="id_cliente">Cliente:</label>
                <select name="id_cliente" id="id_cliente" class="form-control" required>
                    <?php foreach ($clients as $cliente): ?>
                        <option value="<?= esc($cliente['id_cliente']) ?>"><?= esc($cliente['nombre_cliente']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Otros campos de la evaluación -->
            <div class="form-group">
                <label for="ciclo">Ciclo:</label>
                <input type="text" name="ciclo" id="ciclo" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="estandar">Estándar:</label>
                <input type="text" name="estandar" id="estandar" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="detalle_estandar">Detalle del Estándar:</label>
                <textarea name="detalle_estandar" id="detalle_estandar" class="form-control" required></textarea>
            </div>

            <div class="form-group">
                <label for="estandares_minimos">Estándares Mínimos:</label>
                <input type="text" name="estandares_minimos" id="estandares_minimos" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="numeral">Numeral:</label>
                <input type="text" name="numeral" id="numeral" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="numerales_del_cliente">Numerales del Cliente:</label>
                <input type="text" name="numerales_del_cliente" id="numerales_del_cliente" class="form-control">
            </div>

            <div class="form-group">
                <label for="siete">Siete:</label>
                <input type="text" name="siete" id="siete" class="form-control">
            </div>

            <div class="form-group">
                <label for="veintiun">Veintiún:</label>
                <input type="text" name="veintiun" id="veintiun" class="form-control">
            </div>

            <div class="form-group">
                <label for="sesenta">Sesenta:</label>
                <input type="text" name="sesenta" id="sesenta" class="form-control">
            </div>

            <div class="form-group">
                <label for="item_del_estandar">Ítem del Estándar:</label>
                <input type="text" name="item_del_estandar" id="item_del_estandar" class="form-control">
            </div>

            <div class="form-group">
                <label for="evaluacion_inicial">Evaluación Inicial:</label>
                <input type="text" name="evaluacion_inicial" id="evaluacion_inicial" class="form-control">
            </div>

            <div class="form-group">
                <label for="valor">Valor:</label>
                <input type="text" name="valor" id="valor" class="form-control">
            </div>

            <div class="form-group">
                <label for="puntaje_cuantitativo">Puntaje Cuantitativo:</label>
                <input type="text" name="puntaje_cuantitativo" id="puntaje_cuantitativo" class="form-control">
            </div>

            <div class="form-group">
                <label for="item">Ítem:</label>
                <input type="text" name="item" id="item" class="form-control">
            </div>

            <div class="form-group">
                <label for="criterio">Criterio:</label>
                <input type="text" name="criterio" id="criterio" class="form-control">
            </div>

            <div class="form-group">
                <label for="modo_de_verificacion">Modo de Verificación:</label>
                <textarea name="modo_de_verificacion" id="modo_de_verificacion" class="form-control"></textarea>
            </div>

            <div class="form-group">
                <label for="calificacion">Calificación:</label>
                <input type="text" name="calificacion" id="calificacion" class="form-control">
            </div>

            <div class="form-group">
                <label for="nivel_de_evaluacion">Nivel de Evaluación:</label>
                <input type="text" name="nivel_de_evaluacion" id="nivel_de_evaluacion" class="form-control">
            </div>

            <div class="form-group">
                <label for="observaciones">Observaciones:</label>
                <textarea name="observaciones" id="observaciones" class="form-control"></textarea>
            </div>

            <!-- Botón para enviar el formulario -->
            <button type="submit" class="btn btn-primary btn-block">Guardar Evaluación</button>
        </form>

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

    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Iniciación de DataTables en caso de haber tablas en la página
        $(document).ready(function() {
            $('table').DataTable({
                responsive: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
                }
            });
        });
    </script>

</body>

</html>