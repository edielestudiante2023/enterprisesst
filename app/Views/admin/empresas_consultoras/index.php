<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empresas Consultoras</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <style>
        body { background-color: #f8f9fa; color: #343a40; }
        .container { margin-top: 30px; }
        .badge-activo { background-color: #28a745; color: white; }
        .badge-suspendido { background-color: #dc3545; color: white; }
        .badge-inactivo { background-color: #6c757d; color: white; }
        .card-stats { border-left: 4px solid #007bff; }
    </style>
</head>
<body>

<nav style="background-color: white; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <div>
            <a href="<?= base_url('/admin/dashboard') ?>" class="btn btn-outline-primary btn-sm">Ir a Dashboard</a>
        </div>
        <div>
            <h4 style="margin: 0;">Empresas Consultoras</h4>
        </div>
        <div>
            <a href="<?= base_url('/admin/empresas-consultoras/crear') ?>" class="btn btn-success btn-sm">+ Nueva Empresa</a>
        </div>
    </div>
</nav>

<div class="container-fluid" style="margin-top: 20px;">

    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('msg') ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table id="empresasTable" class="table table-striped table-bordered" style="width:100%">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Razon Social</th>
                    <th>NIT</th>
                    <th>Estado</th>
                    <th>Consultores</th>
                    <th>Clientes</th>
                    <th>Plan</th>
                    <th>Fecha Contrato</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empresas as $emp): ?>
                <tr>
                    <td><?= $emp['id_empresa_consultora'] ?></td>
                    <td>
                        <?php if (!empty($emp['logo'])): ?>
                            <img src="<?= base_url($emp['logo']) ?>" alt="logo" style="height:25px; margin-right:8px; vertical-align:middle;">
                        <?php endif; ?>
                        <?= esc($emp['razon_social']) ?>
                    </td>
                    <td><?= esc($emp['nit'] ?? '-') ?></td>
                    <td>
                        <span class="badge badge-<?= $emp['estado'] ?>">
                            <?= ucfirst($emp['estado']) ?>
                        </span>
                    </td>
                    <td class="text-center"><?= $emp['num_consultores'] ?? 0 ?></td>
                    <td class="text-center"><?= $emp['num_clientes'] ?? 0 ?></td>
                    <td><?= esc($emp['plan'] ?? '-') ?></td>
                    <td><?= $emp['fecha_inicio_contrato'] ?? '-' ?></td>
                    <td>
                        <a href="<?= base_url('/admin/empresas-consultoras/editar/' . $emp['id_empresa_consultora']) ?>"
                           class="btn btn-sm btn-primary" title="Editar">Editar</a>

                        <?php if ($emp['estado'] === 'activo'): ?>
                            <a href="<?= base_url('/admin/empresas-consultoras/toggle/' . $emp['id_empresa_consultora']) ?>"
                               class="btn btn-sm btn-warning"
                               onclick="return confirm('Suspender esta empresa? Sus usuarios no podran hacer login.')"
                               title="Suspender">Suspender</a>
                        <?php else: ?>
                            <a href="<?= base_url('/admin/empresas-consultoras/toggle/' . $emp['id_empresa_consultora']) ?>"
                               class="btn btn-sm btn-success" title="Activar">Activar</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#empresasTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json' },
        pageLength: 25,
        order: [[0, 'asc']]
    });
});
</script>
</body>
</html>
