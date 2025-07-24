<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Versión</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Select2 Bootstrap-5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>

<body class="bg-light">

    <!-- Navbar fijo -->
    <nav class="bg-white fixed-top py-2 shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <!-- Logos -->
            <a href="https://dashboard.cycloidtalent.com/login">
                <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" height="100">
            </a>
            <a href="https://cycloidtalent.com/index.php/consultoria-sst">
                <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" height="100">
            </a>
            <a href="https://cycloidtalent.com/">
                <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" height="100">
            </a>
            <!-- Botón Dashboard -->
            <div class="text-center">
                <h2 class="h6 mb-1">Ir a Dashboard</h2>
                <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm">Ir a Dashboard</a>
            </div>
        </div>
    </nav>

    <!-- Espacio para navbar -->
    <div style="height: 160px;"></div>

    <div class="container mt-5">
        <h1 class="mb-4">Editar Versión</h1>

        <form action="<?= base_url('editVersionPost/' . $version['id']) ?>" method="post">
            <div class="table-responsive">
                <table id="versionTable" class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre del Cliente</th>
                            <th>Nombre del Documento</th>
                            <th>Tipo de Documento</th>
                            <th>Acrónimo</th>
                            <th>Número de Versión</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            <th>Control de Cambios</th>
                            <th>Fecha de Creación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="client_id" id="client_id" class="form-select select2" required>
                                    <option value="" disabled>Seleccione un cliente</option>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client['id_cliente'] ?>"
                                            <?= $client['id_cliente'] == $version['client_id'] ? 'selected' : '' ?>>
                                            <?= esc($client['nombre_cliente']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select name="policy_type_id" id="policy_type_id" class="form-select select2" required>
                                    <option value="" disabled>Seleccione un documento</option>
                                    <?php foreach ($policyTypes as $policyType): ?>
                                        <option value="<?= $policyType['id'] ?>"
                                            <?= $policyType['id'] == $version['policy_type_id'] ? 'selected' : '' ?>>
                                            <?= esc($policyType['type_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="document_type" class="form-control" value="<?= esc($version['document_type']) ?>" required>
                            </td>
                            <td>
                                <input type="text" name="acronym" class="form-control" value="<?= esc($version['acronym']) ?>" required>
                            </td>
                            <td>
                                <input type="number" name="version_number" class="form-control" value="<?= esc($version['version_number']) ?>" required>
                            </td>
                            <td>
                                <input type="text" name="location" class="form-control" value="<?= esc($version['location']) ?>" required>
                            </td>
                            <td>
                                <input type="text" name="status" class="form-control" value="<?= esc($version['status']) ?>" required>
                            </td>
                            <td>
                                <textarea name="change_control" class="form-control" rows="3"><?= esc($version['change_control']) ?></textarea>
                            </td>
                            <td><?= esc($version['created_at']) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="<?= base_url('listVersions') ?>" class="btn btn-secondary">Cancelar</a>
            </div>

            <div class="mt-5">
                <h2 class="h5">Ir a Dashboard</h2>
                <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-success btn-sm">Ir a Dashboard</a>
            </div>
        </form>
    </div>

    <footer class="bg-white border-top mt-5 py-4 text-center" style="color: #3A3F51; font-size: 14px;">
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Iniciar DataTable sin paginación ni búsqueda
            $('#versionTable').DataTable({
                paging: false,
                searching: false,
                info: false
            });

            // Iniciar Select2 en todos los selects con clase .select2
            $('select.select2').select2({
                theme: 'bootstrap-5',
                placeholder: 'Seleccione una opción',
                width: '100%'
            });
        });
    </script>
</body>

</html>