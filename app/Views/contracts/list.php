<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Contratos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .stats-card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .badge-custom {
            padding: 8px 12px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-file-contract"></i> Gestión de Contratos
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= base_url('/listClients') ?>">
                    <i class="fas fa-users"></i> Lista de Clientes
                </a>
                <a class="nav-link" href="<?= base_url('/dashboardconsultant') ?>">
                    <i class="fas fa-dashboard"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Mensajes Flash -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card stats-card bg-primary text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Contratos</h5>
                        <h2><?= $stats['total_contratos'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Activos</h5>
                        <h2><?= $stats['contratos_activos'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card bg-danger text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Vencidos</h5>
                        <h2><?= $stats['contratos_vencidos'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card bg-secondary text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Cancelados</h5>
                        <h2><?= $stats['contratos_cancelados'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card bg-info text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Renovaciones</h5>
                        <h2><?= $stats['total_renovaciones'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h5 class="card-title">Tasa Renovación</h5>
                        <h2><?= number_format($stats['tasa_renovacion'] ?? 0, 1) ?>%</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="btn-group" role="group">
                    <a href="<?= base_url('/contracts/create') ?>" class="btn btn-success">
                        <i class="fas fa-plus"></i> Nuevo Contrato
                    </a>
                    <a href="<?= base_url('/contracts/alerts') ?>" class="btn btn-warning">
                        <i class="fas fa-bell"></i> Alertas de Vencimiento
                    </a>
                    <a href="<?= base_url('/contracts') ?>" class="btn btn-primary">
                        <i class="fas fa-refresh"></i> Todos los Contratos
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="get" action="<?= base_url('/contracts') ?>" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="activo" <?= ($filters['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                                    <option value="vencido" <?= ($filters['estado'] ?? '') === 'vencido' ? 'selected' : '' ?>>Vencido</option>
                                    <option value="cancelado" <?= ($filters['estado'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo</label>
                                <select name="tipo" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="inicial" <?= ($filters['tipo'] ?? '') === 'inicial' ? 'selected' : '' ?>>Inicial</option>
                                    <option value="renovacion" <?= ($filters['tipo'] ?? '') === 'renovacion' ? 'selected' : '' ?>>Renovación</option>
                                    <option value="ampliacion" <?= ($filters['tipo'] ?? '') === 'ampliacion' ? 'selected' : '' ?>>Ampliación</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cliente</label>
                                <select name="id_cliente" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client['id_cliente'] ?>" <?= ($filters['id_cliente'] ?? '') == $client['id_cliente'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($client['nombre_cliente']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Contratos -->
        <div class="table-container">
            <h4 class="mb-3">Lista de Contratos</h4>
            <div class="table-responsive">
                <table id="contractsTable" class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Número Contrato</th>
                            <th>Cliente</th>
                            <th>NIT</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Días Restantes</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Valor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($contracts)): ?>
                            <tr>
                                <td colspan="11" class="text-center">
                                    <p class="text-muted my-3">No hay contratos para mostrar</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($contracts as $contract): ?>
                                <?php
                                    $fechaFin = new DateTime($contract['fecha_fin']);
                                    $hoy = new DateTime();
                                    $diasRestantes = (int)$hoy->diff($fechaFin)->format('%r%a');

                                    // Determinar clase de alerta
                                    $alertClass = 'success';
                                    if ($diasRestantes < 0) {
                                        $alertClass = 'danger';
                                    } elseif ($diasRestantes <= 7) {
                                        $alertClass = 'danger';
                                    } elseif ($diasRestantes <= 15) {
                                        $alertClass = 'warning';
                                    } elseif ($diasRestantes <= 30) {
                                        $alertClass = 'info';
                                    }
                                ?>
                                <tr>
                                    <td><?= $contract['id_contrato'] ?></td>
                                    <td><strong><?= htmlspecialchars($contract['numero_contrato']) ?></strong></td>
                                    <td><?= htmlspecialchars($contract['nombre_cliente']) ?></td>
                                    <td><?= htmlspecialchars($contract['nit_cliente']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($contract['fecha_inicio'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($contract['fecha_fin'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $alertClass ?>">
                                            <?= $diasRestantes ?> días
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                            $tipoBadge = [
                                                'inicial' => 'primary',
                                                'renovacion' => 'info',
                                                'ampliacion' => 'warning'
                                            ];
                                        ?>
                                        <span class="badge bg-<?= $tipoBadge[$contract['tipo_contrato']] ?? 'secondary' ?>">
                                            <?= ucfirst($contract['tipo_contrato']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                            $estadoBadge = [
                                                'activo' => 'success',
                                                'vencido' => 'danger',
                                                'cancelado' => 'secondary'
                                            ];
                                        ?>
                                        <span class="badge bg-<?= $estadoBadge[$contract['estado']] ?? 'secondary' ?>">
                                            <?= ucfirst($contract['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($contract['valor_contrato']): ?>
                                            $<?= number_format($contract['valor_contrato'], 0, ',', '.') ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('/contracts/view/' . $contract['id_contrato']) ?>"
                                               class="btn btn-info" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($contract['estado'] === 'activo'): ?>
                                                <a href="<?= base_url('/contracts/renew/' . $contract['id_contrato']) ?>"
                                                   class="btn btn-success" title="Renovar">
                                                    <i class="fas fa-sync"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= base_url('/contracts/client-history/' . $contract['id_cliente']) ?>"
                                               class="btn btn-warning" title="Historial del Cliente">
                                                <i class="fas fa-history"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#contractsTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                order: [[0, 'desc']],
                pageLength: 25
            });
        });
    </script>
</body>
</html>
