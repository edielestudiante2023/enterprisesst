<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Perfiles de Cargo - <?= esc($cliente['nombre_cliente'] ?? '') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('consultantDashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Perfiles de Cargo</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="mb-0">
                <i class="bi bi-person-badge me-2 text-primary"></i>
                Perfiles de Cargo
            </h3>
            <small class="text-muted">Cliente: <strong><?= esc($cliente['nombre_cliente']) ?></strong></small>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('info')): ?>
        <div class="alert alert-info"><?= esc(session()->getFlashdata('info')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if (empty($cargos)): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Este cliente no tiene cargos definidos. Cree cargos primero desde el modulo IPEVR GTC45.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <strong>Cargos del cliente</strong>
                <span class="badge bg-secondary ms-2"><?= count($cargos) ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100" id="tabla-perfiles-cargo">
                    <thead class="table-light">
                        <tr>
                            <th>Cargo</th>
                            <th class="text-center">Trabajadores</th>
                            <th class="text-center">Perfil</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Version</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cargos as $c):
                        $idCargo = (int)$c['id'];
                        $perfil  = $perfilPorCargo[$idCargo] ?? null;
                        $nTrab   = (int)($conteoTrabajadores[$idCargo] ?? 0);
                    ?>
                        <tr>
                            <td>
                                <strong><?= esc($c['nombre_cargo']) ?></strong>
                                <?php if (!empty($c['descripcion'])): ?>
                                    <br><small class="text-muted"><?= esc(mb_strimwidth($c['descripcion'], 0, 120, '...')) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $nTrab ?></td>
                            <td class="text-center">
                                <?php if ($perfil): ?>
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                <?php else: ?>
                                    <i class="bi bi-dash-circle text-muted"></i>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($perfil): ?>
                                    <span class="badge bg-<?= $perfil['estado'] === 'aprobado' ? 'success' : ($perfil['estado'] === 'firmado' ? 'primary' : 'secondary') ?>">
                                        <?= esc($perfil['estado']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?= $perfil ? esc($perfil['version_actual']) . '.0' : '—' ?>
                            </td>
                            <td class="text-end">
                                <?php if ($perfil): ?>
                                    <a class="btn btn-sm btn-primary"
                                       href="<?= base_url("perfiles-cargo/{$cliente['id_cliente']}/editor/{$perfil['id_perfil_cargo']}") ?>">
                                        <i class="bi bi-pencil-square"></i> Editar
                                    </a>
                                <?php else: ?>
                                    <form method="POST" action="<?= base_url("perfiles-cargo/{$cliente['id_cliente']}/crear") ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id_cargo_cliente" value="<?= $idCargo ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-plus-circle"></i> Crear perfil
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- DataTables + Buttons (Excel) -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script>
jQuery(function($) {
    if (!$.fn.DataTable) return;

    $('#tabla-perfiles-cargo').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
        },
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
        order: [[0, 'asc']],
        columnDefs: [
            { targets: [2, 5], orderable: false, searchable: false } // Perfil (icono) y Acciones
        ],
        dom: "<'row mb-2'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel"></i> Exportar Excel',
                className: 'btn btn-success btn-sm',
                title: 'Perfiles de Cargo - <?= esc($cliente['nombre_cliente']) ?>',
                filename: 'perfiles_cargo_<?= esc(preg_replace('/[^a-z0-9]+/i', '_', strtolower($cliente['nombre_cliente']))) ?>_' + new Date().toISOString().slice(0,10),
                exportOptions: {
                    columns: [0, 1, 2, 3, 4] // excluye "Acciones"
                }
            }
        ]
    });
});
</script>
<?= $this->endSection() ?>
