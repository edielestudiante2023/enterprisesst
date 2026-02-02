<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presupuesto SST <?= $anio ?> - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: #f5f7fa;
            min-height: 100vh;
        }
        .consulta-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .card-presupuesto {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header-presupuesto {
            background: linear-gradient(135deg, #1a5f7a 0%, #2c3e50 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 25px;
        }
        .tabla-presupuesto {
            font-size: 0.85rem;
        }
        .tabla-presupuesto th {
            background-color: #1a5f7a;
            color: white;
            font-size: 0.75rem;
            padding: 8px;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .tabla-presupuesto td {
            padding: 6px 8px;
        }
        .categoria-row {
            background-color: #e8f4f8 !important;
            font-weight: bold;
        }
        .subtotal-row {
            background-color: #d4edda !important;
            font-weight: bold;
        }
        .total-row {
            background-color: #1a5f7a !important;
            color: white !important;
            font-weight: bold;
        }
        .badge-estado {
            font-size: 0.9rem;
            padding: 8px 16px;
        }
        .firma-info {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-radius: 8px;
            padding: 20px;
        }
        .firma-imagen {
            max-height: 80px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            padding: 5px;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .card-presupuesto { box-shadow: none; }
        }
    </style>
</head>
<body class="py-4">
    <div class="container consulta-container">
        <!-- Header con logo -->
        <div class="text-center mb-4 no-print">
            <h4 class="text-muted"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Consulta de Presupuesto SST</h4>
        </div>

        <div class="card card-presupuesto">
            <!-- Header del presupuesto -->
            <div class="header-presupuesto">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <h4 class="mb-1"><?= esc($codigoDocumento ?? 'FT-SST-001') ?> Asignacion de Recursos SG-SST</h4>
                        <p class="mb-0 opacity-75"><?= esc($cliente['nombre_cliente']) ?></p>
                    </div>
                    <div class="col-md-5 text-end">
                        <div class="d-flex justify-content-end gap-3">
                            <div class="bg-white bg-opacity-10 rounded p-2 text-center">
                                <div class="fs-5 fw-bold">$<?= number_format($totales['general_presupuestado'], 0, ',', '.') ?></div>
                                <small>Presupuestado</small>
                            </div>
                            <div class="bg-white bg-opacity-10 rounded p-2 text-center">
                                <div class="fs-5 fw-bold">$<?= number_format($totales['general_ejecutado'], 0, ',', '.') ?></div>
                                <small>Ejecutado</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Info empresa y estado -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <small class="text-muted">Empresa:</small>
                        <div class="fw-bold"><?= esc($cliente['nombre_cliente']) ?></div>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted">NIT:</small>
                        <div><?= esc($cliente['nit_cliente'] ?? 'N/A') ?></div>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted">Periodo:</small>
                        <div class="fw-bold">Año <?= $anio ?></div>
                    </div>
                    <div class="col-md-5 text-end">
                        <?php if ($presupuesto['estado'] === 'borrador'): ?>
                            <span class="badge bg-secondary badge-estado">
                                <i class="bi bi-pencil me-1"></i>En Elaboracion
                            </span>
                        <?php elseif ($presupuesto['estado'] === 'pendiente_firma'): ?>
                            <span class="badge bg-warning text-dark badge-estado">
                                <i class="bi bi-clock-history me-1"></i>Pendiente de Firma
                            </span>
                        <?php elseif ($presupuesto['estado'] === 'aprobado'): ?>
                            <span class="badge bg-success badge-estado">
                                <i class="bi bi-patch-check-fill me-1"></i>Aprobado
                            </span>
                        <?php elseif ($presupuesto['estado'] === 'cerrado'): ?>
                            <span class="badge bg-dark badge-estado">
                                <i class="bi bi-lock me-1"></i>Cerrado
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tabla de presupuesto -->
                <div class="table-responsive mb-4" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-bordered table-sm tabla-presupuesto mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Item</th>
                                <th>Actividad</th>
                                <?php foreach ($meses as $mes): ?>
                                    <th class="text-center" style="width: 75px;">
                                        <?= $mes['nombre'] ?><br>
                                        <small class="fw-normal">P / E</small>
                                    </th>
                                <?php endforeach; ?>
                                <th class="text-center" style="width: 100px;">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itemsPorCategoria as $codigoCat => $categoria): ?>
                                <!-- Categoria -->
                                <tr class="categoria-row">
                                    <td colspan="<?= 2 + count($meses) + 1 ?>">
                                        <?= $codigoCat ?>. <?= esc($categoria['nombre']) ?>
                                    </td>
                                </tr>

                                <!-- Items -->
                                <?php foreach ($categoria['items'] as $item): ?>
                                <tr>
                                    <td class="text-center"><?= esc($item['codigo_item']) ?></td>
                                    <td><?= esc($item['actividad']) ?></td>
                                    <?php foreach ($meses as $mes):
                                        $detalle = $item['detalles'][$mes['numero']] ?? null;
                                        $presup = $detalle ? floatval($detalle['presupuestado']) : 0;
                                        $ejec = $detalle ? floatval($detalle['ejecutado']) : 0;
                                    ?>
                                        <td class="text-end">
                                            <?php if ($presup > 0 || $ejec > 0): ?>
                                                <small class="text-muted"><?= number_format($presup/1000, 0) ?>k</small>
                                                <?php if ($ejec > 0): ?>
                                                    <br><small class="text-success"><?= number_format($ejec/1000, 0) ?>k</small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="text-end">
                                        <strong>$<?= number_format($item['total_presupuestado'], 0, ',', '.') ?></strong>
                                        <?php if ($item['total_ejecutado'] > 0): ?>
                                            <br><small class="text-success">$<?= number_format($item['total_ejecutado'], 0, ',', '.') ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                                <!-- Subtotal categoria -->
                                <?php $totCat = $totales['por_categoria'][$codigoCat] ?? ['presupuestado' => 0, 'ejecutado' => 0, 'por_mes' => []]; ?>
                                <tr class="subtotal-row">
                                    <td colspan="2" class="text-end">Sub Total <?= $codigoCat ?></td>
                                    <?php foreach ($meses as $mes):
                                        $totMes = $totCat['por_mes'][$mes['numero']] ?? ['presupuestado' => 0, 'ejecutado' => 0];
                                    ?>
                                        <td class="text-end">
                                            <small><?= number_format($totMes['presupuestado']/1000, 0) ?>k</small>
                                            <?php if ($totMes['ejecutado'] > 0): ?>
                                                <br><small class="text-success"><?= number_format($totMes['ejecutado']/1000, 0) ?>k</small>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="text-end">
                                        <strong>$<?= number_format($totCat['presupuestado'], 0, ',', '.') ?></strong>
                                        <?php if ($totCat['ejecutado'] > 0): ?>
                                            <br><small class="text-success">$<?= number_format($totCat['ejecutado'], 0, ',', '.') ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- Total General -->
                            <tr class="total-row">
                                <td colspan="2" class="text-end">TOTAL GENERAL</td>
                                <?php foreach ($meses as $mes):
                                    $totMes = $totales['por_mes'][$mes['numero']] ?? ['presupuestado' => 0, 'ejecutado' => 0];
                                ?>
                                    <td class="text-end">
                                        <?= number_format($totMes['presupuestado']/1000, 0) ?>k
                                        <?php if ($totMes['ejecutado'] > 0): ?>
                                            <br><small><?= number_format($totMes['ejecutado']/1000, 0) ?>k</small>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="text-end">
                                    <div class="fs-6">$<?= number_format($totales['general_presupuestado'], 0, ',', '.') ?></div>
                                    <?php if ($totales['general_ejecutado'] > 0): ?>
                                        <small>$<?= number_format($totales['general_ejecutado'], 0, ',', '.') ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Leyenda -->
                <div class="mb-4">
                    <small class="text-muted">
                        <strong>P</strong> = Presupuestado |
                        <strong class="text-success">E</strong> = Ejecutado |
                        <strong>k</strong> = Miles de pesos
                    </small>
                </div>

                <?php if ($presupuesto['estado'] === 'aprobado' && !empty($presupuesto['firmado_por'])): ?>
                <!-- Info de aprobacion -->
                <div class="firma-info">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-2 text-success">
                                <i class="bi bi-patch-check-fill me-2"></i>Presupuesto Aprobado
                            </h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted">Firmado por:</small>
                                    <div class="fw-bold"><?= esc($presupuesto['firmado_por']) ?></div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Cedula:</small>
                                    <div><?= esc($presupuesto['cedula_firmante'] ?? 'N/A') ?></div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Fecha de aprobacion:</small>
                                    <div><?= date('d/m/Y H:i', strtotime($presupuesto['fecha_aprobacion'])) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <?php if (!empty($presupuesto['firma_imagen'])): ?>
                                <img src="<?= base_url($presupuesto['firma_imagen']) ?>" alt="Firma" class="firma-imagen">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($presupuesto['estado'] === 'pendiente_firma'): ?>
                <!-- Aviso de pendiente firma -->
                <div class="alert alert-warning">
                    <i class="bi bi-clock-history me-2"></i>
                    <strong>Pendiente de Firma:</strong> Este presupuesto ha sido enviado para aprobacion.
                    Por favor revise su correo electronico para firmar digitalmente.
                </div>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="card-footer">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="bi bi-calendar3 me-1"></i>
                            Generado: <?= date('d/m/Y H:i') ?>
                        </small>
                    </div>
                    <div class="col-md-6 text-end no-print">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i>Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer info -->
        <div class="text-center mt-4 text-muted no-print">
            <small>Enterprise SST - Sistema de Gestion de Seguridad y Salud en el Trabajo</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
