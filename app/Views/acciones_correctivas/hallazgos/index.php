<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hallazgos - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('dashboard') ?>">
                <i class="bi bi-shield-check me-2"></i>EnterpriseSST
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}") ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">
                    <i class="bi bi-exclamation-diamond text-warning me-2"></i>
                    Hallazgos
                </h3>
                <p class="text-muted mb-0">
                    Cliente: <strong><?= esc($cliente['nombre_cliente']) ?></strong>
                </p>
            </div>
            <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgo/crear") ?>"
               class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i>Nuevo Hallazgo
            </a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Numeral</label>
                        <select class="form-select form-select-sm" id="filtro_numeral">
                            <option value="">Todos</option>
                            <option value="7.1.1">7.1.1 - Resultados SG-SST</option>
                            <option value="7.1.2">7.1.2 - Efectividad Medidas</option>
                            <option value="7.1.3">7.1.3 - Investigacion ATEL</option>
                            <option value="7.1.4">7.1.4 - ARL/Autoridades</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Estado</label>
                        <select class="form-select form-select-sm" id="filtro_estado">
                            <option value="">Todos</option>
                            <option value="abierto">Abierto</option>
                            <option value="en_tratamiento">En Tratamiento</option>
                            <option value="cerrado">Cerrado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Severidad</label>
                        <select class="form-select form-select-sm" id="filtro_severidad">
                            <option value="">Todas</option>
                            <option value="critica">Critica</option>
                            <option value="alta">Alta</option>
                            <option value="media">Media</option>
                            <option value="baja">Baja</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-outline-secondary btn-sm w-100" onclick="limpiarFiltros()">
                            <i class="bi bi-x-circle me-1"></i>Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Hallazgos -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tabla_hallazgos">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Titulo</th>
                                <th>Numeral</th>
                                <th>Origen</th>
                                <th>Severidad</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                                <th class="text-center">Ver</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($hallazgos)): ?>
                            <?php foreach ($hallazgos as $h): ?>
                            <?php
                            $severidadClase = match($h['severidad']) {
                                'critica' => 'danger',
                                'alta' => 'warning',
                                'media' => 'info',
                                'baja' => 'secondary',
                                default => 'secondary'
                            };
                            $estadoClase = match($h['estado']) {
                                'abierto' => 'warning',
                                'en_tratamiento' => 'primary',
                                'cerrado' => 'success',
                                default => 'secondary'
                            };
                            $origenInfo = $catalogo_origenes[$h['tipo_origen']] ?? ['nombre_mostrar' => $h['tipo_origen'], 'icono' => 'bi-circle'];
                            ?>
                            <tr data-numeral="<?= $h['numeral_asociado'] ?>"
                                data-estado="<?= $h['estado'] ?>"
                                data-severidad="<?= $h['severidad'] ?>">
                                <td><small class="text-muted">#<?= $h['id_hallazgo'] ?></small></td>
                                <td>
                                    <strong><?= esc(substr($h['titulo'], 0, 50)) ?><?= strlen($h['titulo']) > 50 ? '...' : '' ?></strong>
                                    <?php if (!empty($h['area_proceso'])): ?>
                                    <br><small class="text-muted"><?= esc($h['area_proceso']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-dark"><?= $h['numeral_asociado'] ?></span></td>
                                <td>
                                    <i class="bi <?= $origenInfo['icono'] ?? 'bi-circle' ?> me-1"></i>
                                    <small><?= esc($origenInfo['nombre_mostrar'] ?? $h['tipo_origen']) ?></small>
                                </td>
                                <td><span class="badge bg-<?= $severidadClase ?>"><?= ucfirst($h['severidad']) ?></span></td>
                                <td><span class="badge bg-<?= $estadoClase ?>"><?= ucwords(str_replace('_', ' ', $h['estado'])) ?></span></td>
                                <td><small><?= date('d/m/Y', strtotime($h['fecha_deteccion'])) ?></small></td>
                                <td><span class="badge bg-secondary"><?= $h['total_acciones'] ?? 0 ?></span></td>
                                <td class="text-center">
                                    <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgo/{$h['id_hallazgo']}") ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">No hay hallazgos registrados</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function filtrarTabla() {
        const numeral = $('#filtro_numeral').val();
        const estado = $('#filtro_estado').val();
        const severidad = $('#filtro_severidad').val();

        $('#tabla_hallazgos tbody tr').each(function() {
            const row = $(this);
            let mostrar = true;

            if (numeral && row.data('numeral') !== numeral) mostrar = false;
            if (estado && row.data('estado') !== estado) mostrar = false;
            if (severidad && row.data('severidad') !== severidad) mostrar = false;

            row.toggle(mostrar);
        });
    }

    function limpiarFiltros() {
        $('#filtro_numeral, #filtro_estado, #filtro_severidad').val('');
        filtrarTabla();
    }

    $(document).ready(function() {
        $('#filtro_numeral, #filtro_estado, #filtro_severidad').on('change', filtrarTabla);
    });
    </script>
</body>
</html>
