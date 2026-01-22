<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Contratos - <?= esc($client['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .content-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .client-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
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
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        .timeline-item {
            position: relative;
            padding-left: 40px;
            margin-bottom: 30px;
            border-left: 2px solid #dee2e6;
        }
        .timeline-item:last-child {
            border-left: 0;
        }
        .timeline-marker {
            position: absolute;
            left: -10px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: white;
            border: 3px solid #007bff;
        }
        .timeline-marker.active {
            border-color: #28a745;
            background: #28a745;
        }
        .timeline-marker.expired {
            border-color: #dc3545;
            background: #dc3545;
        }
        .timeline-marker.cancelled {
            border-color: #6c757d;
            background: #6c757d;
        }
        .contract-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('/contracts') ?>">
                <i class="fas fa-file-contract"></i> Gestión de Contratos
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= base_url('/contracts') ?>">
                    <i class="fas fa-arrow-left"></i> Volver a Contratos
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="content-container">
            <!-- Header del Cliente -->
            <div class="client-header">
                <h2 class="mb-3">
                    <i class="fas fa-building"></i> <?= esc($client['nombre_cliente']) ?>
                </h2>
                <div class="row">
                    <div class="col-md-4">
                        <p class="mb-1"><i class="fas fa-id-card"></i> NIT: <?= esc($client['nit_cliente']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><i class="fas fa-envelope"></i> <?= esc($client['correo_cliente']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><i class="fas fa-phone"></i> <?= esc($client['telefono_1_cliente']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Estadísticas del Cliente -->
            <h4 class="mb-3"><i class="fas fa-chart-line"></i> Estadísticas</h4>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stats-card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5>Total Contratos</h5>
                            <h2><?= $history['total_contracts'] ?? 0 ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-success text-white">
                        <div class="card-body text-center">
                            <h5>Renovaciones</h5>
                            <h2><?= $history['total_renewals'] ?? 0 ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-info text-white">
                        <div class="card-body text-center">
                            <h5>Antigüedad (Años)</h5>
                            <h2><?= $history['client_antiquity_years'] ?? 0 ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-warning text-white">
                        <div class="card-body text-center">
                            <h5>Primer Contrato</h5>
                            <h6><?= $history['first_contract_date'] ? date('d/m/Y', strtotime($history['first_contract_date'])) : 'N/A' ?></h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline de Contratos -->
            <h4 class="mb-3"><i class="fas fa-history"></i> Historial de Contratos</h4>

            <?php if (empty($history['contracts'])): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay contratos registrados para este cliente.
                </div>
            <?php else: ?>
                <div class="timeline">
                    <?php foreach ($history['contracts'] as $contract): ?>
                        <?php
                            $estadoClass = '';
                            $estadoBadge = '';
                            $markerClass = '';

                            switch ($contract['estado']) {
                                case 'activo':
                                    $estadoClass = 'success';
                                    $estadoBadge = 'bg-success';
                                    $markerClass = 'active';
                                    break;
                                case 'vencido':
                                    $estadoClass = 'danger';
                                    $estadoBadge = 'bg-danger';
                                    $markerClass = 'expired';
                                    break;
                                case 'cancelado':
                                    $estadoClass = 'secondary';
                                    $estadoBadge = 'bg-secondary';
                                    $markerClass = 'cancelled';
                                    break;
                            }

                            $tipoIcon = $contract['tipo_contrato'] === 'renovacion'
                                ? '<i class="fas fa-sync-alt text-primary"></i>'
                                : '<i class="fas fa-star text-warning"></i>';
                        ?>

                        <div class="timeline-item">
                            <div class="timeline-marker <?= $markerClass ?>"></div>

                            <div class="contract-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-2">
                                            <?= $tipoIcon ?>
                                            Contrato #<?= esc($contract['numero_contrato']) ?>
                                            <span class="badge <?= $estadoBadge ?> ms-2">
                                                <?= strtoupper($contract['estado']) ?>
                                            </span>
                                            <span class="badge bg-info ms-1">
                                                <?= strtoupper($contract['tipo_contrato']) ?>
                                            </span>
                                        </h5>

                                        <div class="row mt-3">
                                            <div class="col-md-3">
                                                <small class="text-muted">Fecha Inicio</small>
                                                <p class="mb-0"><strong><?= date('d/m/Y', strtotime($contract['fecha_inicio'])) ?></strong></p>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">Fecha Fin</small>
                                                <p class="mb-0"><strong><?= date('d/m/Y', strtotime($contract['fecha_fin'])) ?></strong></p>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">Valor Total</small>
                                                <p class="mb-0"><strong>$<?= number_format($contract['valor_contrato'], 0, ',', '.') ?></strong></p>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">Valor Mensual</small>
                                                <p class="mb-0"><strong>$<?= number_format($contract['valor_mensual'], 0, ',', '.') ?></strong></p>
                                            </div>
                                        </div>

                                        <?php if ($contract['observaciones']): ?>
                                            <div class="mt-3">
                                                <small class="text-muted">Observaciones:</small>
                                                <p class="mb-0"><?= nl2br(esc($contract['observaciones'])) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <a href="<?= base_url('contracts/view/' . $contract['id_contrato']) ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>

                                        <?php if ($contract['contrato_generado'] && $contract['ruta_pdf_contrato']): ?>
                                            <a href="<?= base_url('contracts/download-pdf/' . $contract['id_contrato']) ?>"
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Tabla Resumen -->
                <h4 class="mb-3 mt-5"><i class="fas fa-table"></i> Resumen en Tabla</h4>
                <div class="table-responsive">
                    <table class="table table-hover" id="contractsTable">
                        <thead class="table-primary">
                            <tr>
                                <th>Número</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Duración</th>
                                <th>Valor Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history['contracts'] as $contract): ?>
                                <?php
                                    $fechaInicio = new DateTime($contract['fecha_inicio']);
                                    $fechaFin = new DateTime($contract['fecha_fin']);
                                    $diff = $fechaInicio->diff($fechaFin);
                                    $meses = ($diff->y * 12) + $diff->m;
                                ?>
                                <tr>
                                    <td><?= esc($contract['numero_contrato']) ?></td>
                                    <td>
                                        <?php if ($contract['tipo_contrato'] === 'renovacion'): ?>
                                            <span class="badge bg-primary">Renovación</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Inicial</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($contract['estado'] === 'activo'): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php elseif ($contract['estado'] === 'vencido'): ?>
                                            <span class="badge bg-danger">Vencido</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Cancelado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($contract['fecha_inicio'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($contract['fecha_fin'])) ?></td>
                                    <td><?= $meses ?> meses</td>
                                    <td>$<?= number_format($contract['valor_contrato'], 0, ',', '.') ?></td>
                                    <td>
                                        <a href="<?= base_url('contracts/view/' . $contract['id_contrato']) ?>"
                                           class="btn btn-sm btn-primary"
                                           title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($contract['contrato_generado']): ?>
                                            <a href="<?= base_url('contracts/download-pdf/' . $contract['id_contrato']) ?>"
                                               class="btn btn-sm btn-secondary"
                                               title="Descargar PDF">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#contractsTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                order: [[3, 'desc']], // Ordenar por fecha de inicio descendente
                pageLength: 10
            });
        });
    </script>
</body>
</html>
