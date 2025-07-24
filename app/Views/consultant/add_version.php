<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Nueva Versión</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Select2 Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>

<body style="background-color: #f8f9fa; color: #343a40; font-family: Arial, sans-serif;">

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
                <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm mt-1">Ir a Dashboard</a>
            </div>
        </div>
    </nav>

    <!-- Espacio para navbar fijo -->
    <div style="height: 160px;"></div>

    <div class="container mt-5">
        <h1 class="text-center mb-4">Añadir Nueva Versión</h1>

        <form action="<?= base_url('addVersionPost') ?>" method="post" class="p-4 border rounded bg-white shadow-sm">
            <div class="mb-3">
                <label for="client_id" class="form-label">Nombre del Cliente:</label>
                <select name="client_id" id="client_id" class="form-select select2" required>
                    <option value="" disabled selected>Seleccione un cliente</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= $client['id_cliente'] ?>"><?= esc($client['nombre_cliente']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="policy_type_id" class="form-label">Nombre del Documento:</label>
                <select name="policy_type_id" id="policy_type_id" class="form-select select2" required>
                    <option value="" disabled selected>Seleccione un documento</option>
                    <?php foreach ($policyTypes as $policyType): ?>
                        <option value="<?= $policyType['id'] ?>"><?= esc($policyType['type_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="document_type" class="form-label">Tipo de Documento:</label>
                <input type="text" name="document_type" id="document_type" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="acronym" class="form-label">Acrónimo:</label>
                <input type="text" name="acronym" id="acronym" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="version_number" class="form-label">Número de Versión:</label>
                <input type="number" name="version_number" id="version_number" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="location" class="form-label">Ubicación:</label>
                <input type="text" name="location" id="location" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Estado:</label>
                <input type="text" name="status" id="status" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="change_control" class="form-label">Control de Cambios:</label>
                <textarea name="change_control" id="change_control" class="form-control" rows="3"></textarea>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Guardar Versión</button>
                <a href="<?= base_url('listVersions') ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <footer class="mt-5 py-4 bg-white border-top" style="color: #3A3F51; font-size: 14px; text-align: center;">
        <div class="container d-flex flex-column align-items-center">
            <p class="mb-1 fw-bold">Cycloid Talent SAS</p>
            <p class="mb-1">Todos los derechos reservados © 2024</p>
            <p class="mb-1">NIT: 901.653.912</p>
            <p class="mb-1">
                Sitio oficial:
                <a href="https://cycloidtalent.com/" target="_blank" class="text-primary text-decoration-none">
                    cycloidtalent.com
                </a>
            </p>
            <p class="mt-3 mb-1"><strong>Nuestras Redes Sociales:</strong></p>
            <div class="d-flex gap-3">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" width="24" height="24">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" width="24" height="24">
                </a>
                <a href="https://www.instagram.com/cycloid_talent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" width="24" height="24">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" width="24" height="24">
                </a>
            </div>
        </div>
    </footer>

    <script>
        $(document).ready(function() {
            // Inicializar DataTables
            $('.dataTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                }
            });

            // Inicializar Select2 en los selects con clase .select2
            $('select.select2').select2({
                theme: 'bootstrap-5',
                placeholder: 'Seleccione una opción',
                width: '100%'
            });
        });
    </script>

</body>

</html>