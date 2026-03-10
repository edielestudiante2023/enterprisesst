<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informes de Avances - SG-SST</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary-dark: #1c2437;
            --gold-primary: #bd9751;
            --gold-secondary: #d4af37;
            --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        body { background: var(--gradient-bg); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; }
        .navbar-custom { background: #fff; box-shadow: 0 8px 32px rgba(28,36,55,0.15); padding: 15px 0; border-bottom: 2px solid var(--gold-primary); }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .badge-avance-significativo { background: #28a745; color: #fff; }
        .badge-avance-moderado { background: #17a2b8; color: #fff; }
        .badge-estable { background: #ffc107; color: #333; }
        .badge-reinicio { background: #dc3545; color: #fff; }
        .badge-borrador { background: #6c757d; color: #fff; }
        .badge-completo { background: #28a745; color: #fff; }
        .btn-gold { background: var(--gold-primary); color: #fff; border: none; }
        .btn-gold:hover { background: var(--gold-secondary); color: #fff; }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= base_url('consultant/admindashboard') ?>" style="color: var(--primary-dark);">
                <i class="fas fa-arrow-left me-2"></i>Dashboard
            </a>
            <span class="fw-bold" style="color: var(--primary-dark);">
                <i class="fas fa-chart-line me-2" style="color: var(--gold-primary);"></i>Informes de Avances
            </span>
        </div>
    </nav>

    <div class="container-fluid mt-3 px-4">
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= session()->getFlashdata('msg') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= session()->getFlashdata('error') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card card-custom mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Listado de Informes</h5>
                    <a href="<?= base_url('informe-avances/create') ?>" class="btn btn-gold">
                        <i class="fas fa-plus me-1"></i>Nuevo Informe
                    </a>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Filtrar por cliente:</label>
                        <select id="filtroCliente" class="form-select">
                            <option value="">Todos los clientes</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Anio:</label>
                        <select id="filtroAnio" class="form-select">
                            <option value="">Todos</option>
                            <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                                <option value="<?= $y ?>"><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <table id="tablaInformes" class="table table-striped table-hover" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>Cliente</th>
                            <th>Periodo</th>
                            <th>Puntaje</th>
                            <th>Estado Avance</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($informes as $inf): ?>
                        <tr data-cliente="<?= esc($inf['nombre_cliente'] ?? '') ?>" data-anio="<?= esc($inf['anio'] ?? '') ?>">
                            <td><?= esc($inf['nombre_cliente'] ?? 'N/A') ?></td>
                            <td><?= date('d/m/Y', strtotime($inf['fecha_desde'])) ?> - <?= date('d/m/Y', strtotime($inf['fecha_hasta'])) ?></td>
                            <td>
                                <strong><?= number_format($inf['puntaje_actual'] ?? 0, 1) ?>%</strong>
                                <?php if ($inf['diferencia_neta'] > 0): ?>
                                    <small class="text-success"><i class="fas fa-arrow-up"></i> +<?= number_format($inf['diferencia_neta'], 1) ?></small>
                                <?php elseif ($inf['diferencia_neta'] < 0): ?>
                                    <small class="text-danger"><i class="fas fa-arrow-down"></i> <?= number_format($inf['diferencia_neta'], 1) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $estadoAvance = $inf['estado_avance'] ?? 'ESTABLE';
                                    $badgeClass = match(true) {
                                        str_contains($estadoAvance, 'LÍNEA BASE')    => 'badge-avance-moderado',
                                        str_contains($estadoAvance, 'SIGNIFICATIVO') => 'badge-avance-significativo',
                                        str_contains($estadoAvance, 'MODERADO')      => 'badge-avance-moderado',
                                        str_contains($estadoAvance, 'ESTABLE')       => 'badge-estable',
                                        default                                      => 'badge-reinicio',
                                    };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= esc($estadoAvance) ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $inf['estado'] === 'completo' ? 'badge-completo' : 'badge-borrador' ?>">
                                    <?= ucfirst($inf['estado']) ?>
                                </span>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="<?= base_url('informe-avances/edit/' . $inf['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fas fa-edit"></i></a>
                                <?php if ($inf['estado'] === 'completo'): ?>
                                    <a href="<?= base_url('informe-avances/view/' . $inf['id']) ?>" class="btn btn-sm btn-outline-info" title="Ver"><i class="fas fa-eye"></i></a>
                                    <a href="<?= base_url('informe-avances/pdf/' . $inf['id']) ?>" class="btn btn-sm btn-outline-success" title="PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                <?php endif; ?>
                                <a href="<?= base_url('informe-avances/delete/' . $inf['id']) ?>" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Eliminar este informe?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        var table = $('#tablaInformes').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
            order: [[1, 'desc']],
            pageLength: 25,
        });

        // Populate client filter from table data
        var clientes = {};
        table.rows().every(function() {
            var c = $(this.node()).data('cliente');
            if (c) clientes[c] = true;
        });
        Object.keys(clientes).sort().forEach(function(c) {
            $('#filtroCliente').append('<option value="'+c+'">'+c+'</option>');
        });

        $('#filtroCliente').select2({ theme: 'bootstrap-5', allowClear: true, placeholder: 'Todos los clientes' });

        // Custom filtering
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var row = table.row(dataIndex).node();
            var clienteFilter = $('#filtroCliente').val();
            var anioFilter = $('#filtroAnio').val();
            var rowCliente = $(row).data('cliente');
            var rowAnio = String($(row).data('anio'));

            if (clienteFilter && rowCliente !== clienteFilter) return false;
            if (anioFilter && rowAnio !== anioFilter) return false;
            return true;
        });

        $('#filtroCliente, #filtroAnio').on('change', function() { table.draw(); });
    });
    </script>
</body>
</html>
