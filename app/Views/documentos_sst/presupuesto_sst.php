<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presupuesto SST <?= $anio ?> - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        .navbar { background: rgba(255, 255, 255, 0.95); border-bottom: 3px solid #bd9751; }

        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }

        .presupuesto-table {
            font-size: 0.8rem;
        }
        .presupuesto-table th {
            background-color: #1a5f7a;
            color: white;
            text-align: center;
            vertical-align: middle;
            padding: 4px 6px;
            font-weight: 500;
        }
        .presupuesto-table td {
            padding: 2px 4px;
            vertical-align: middle;
        }
        .presupuesto-table .categoria-header {
            background-color: #e8f4f8;
            font-weight: bold;
        }
        .presupuesto-table .subtotal-row {
            background-color: #d4edda;
            font-weight: bold;
        }
        .presupuesto-table .total-row {
            background-color: #1a5f7a;
            color: white;
            font-weight: bold;
        }
        .monto-input {
            width: 85px;
            text-align: right;
            font-size: 0.75rem;
            padding: 2px 4px;
            border: 1px solid #dee2e6;
            border-radius: 3px;
        }
        .monto-input:focus {
            border-color: #1a5f7a;
            box-shadow: 0 0 0 0.1rem rgba(26, 95, 122, 0.25);
        }
        .monto-input.ejecutado {
            background-color: #fff3cd;
        }
        .col-mes {
            min-width: 180px;
        }
        .col-mes-header {
            text-align: center;
        }
        .sub-header {
            font-size: 0.7rem;
        }
        .item-actividad {
            min-width: 150px;
        }
        .item-descripcion {
            min-width: 120px;
            font-size: 0.75rem;
            color: #666;
        }
        .btn-accion {
            padding: 2px 6px;
            font-size: 0.7rem;
        }
        .total-cell {
            text-align: right;
            font-weight: bold;
        }
        .estado-badge {
            font-size: 0.75rem;
        }
        .saving-indicator {
            display: none;
            color: #28a745;
            font-size: 0.75rem;
        }
        .table-responsive {
            max-height: 70vh;
            overflow: auto;
        }
        .sticky-col {
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 1;
        }
        .sticky-col-2 {
            position: sticky;
            left: 35px;
            background-color: white;
            z-index: 1;
        }
        .sticky-col-3 {
            position: sticky;
            left: 185px;
            background-color: white;
            z-index: 1;
        }
        .page-header {
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Barra de herramientas -->
    <div class="no-print bg-dark text-white py-2 sticky-top">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" target="_blank" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </a>
                    <span class="ms-3"><?= esc($cliente['nombre_cliente']) ?> - Presupuesto SST <?= $anio ?></span>
                </div>
                <div>
                    <!-- Selector de año -->
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-calendar me-1"></i>Año: <?= $anio ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php foreach ($anios_disponibles as $a): ?>
                                <li>
                                    <a class="dropdown-item <?= $a == $anio ? 'active' : '' ?>"
                                       href="<?= base_url('documentos-sst/presupuesto/' . $cliente['id_cliente'] . '/' . $a) ?>"
                                       target="_blank">
                                        <?= $a ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Ver Preview (formato documento para firmas) -->
                    <a href="<?= base_url('documentos-sst/presupuesto/preview/' . $cliente['id_cliente'] . '/' . $anio) ?>"
                       target="_blank" class="btn btn-info btn-sm me-2">
                        <i class="bi bi-eye me-1"></i>Ver Preview
                    </a>

                    <!-- Exportar Excel -->
                    <a href="<?= base_url('documentos-sst/presupuesto/excel/' . $cliente['id_cliente'] . '/' . $anio) ?>"
                       target="_blank" class="btn btn-outline-light btn-sm me-2">
                        <i class="bi bi-file-earmark-excel me-1"></i>Exportar Excel
                    </a>

                    <?php
                    // Permitir agregar items en borrador y aprobado (para registrar gastos no presupuestados)
                    // Solo bloquear en pendiente_firma y cerrado
                    $puedeEditar = in_array($presupuesto['estado'], ['borrador', 'aprobado', '', null]);
                    ?>
                    <?php if ($puedeEditar): ?>
                        <button type="button" class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalAgregarItem">
                            <i class="bi bi-plus-lg me-1"></i>Agregar Item
                        </button>
                    <?php endif; ?>

                    <!-- Boton de enviar a firmas (solo en borrador) -->
                    <?php if (($presupuesto['estado'] ?? 'borrador') === 'borrador'): ?>
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEnviarFirmas">
                            <i class="bi bi-pen me-1"></i>Enviar a Firmas
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-3">
        <!-- Header -->
        <div class="page-header no-print">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2" style="--bs-breadcrumb-divider-color: rgba(255,255,255,0.5);">
                            <li class="breadcrumb-item"><a href="<?= base_url('clientes') ?>" target="_blank" class="text-white-50">Clientes</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" target="_blank" class="text-white-50"><?= esc($cliente['nombre_cliente']) ?></a></li>
                            <li class="breadcrumb-item active text-white">Presupuesto SST</li>
                        </ol>
                    </nav>
                    <h4 class="mb-1">
                        <i class="bi bi-currency-dollar me-2"></i><?= $codigoDocumento ?? 'FT-SST-001' ?> <?= $tituloDocumento ?? 'Asignacion de Recursos SG-SST' ?>
                    </h4>
                    <p class="mb-0 opacity-75">
                        <?= esc($cliente['nombre_cliente']) ?> - Año <?= $anio ?>
                        <?php
                        $estadoActual = $presupuesto['estado'] ?? 'borrador';
                        // Permitir edicion en borrador y aprobado (para gastos no presupuestados)
                        // Bloquear en pendiente_firma (esperando aprobacion) y cerrado (año fiscal cerrado)
                        $puedeEditar = in_array($estadoActual, ['borrador', 'aprobado', '', null]);
                        $badgeClass = match($estadoActual) {
                            'aprobado' => 'success',
                            'cerrado' => 'secondary',
                            'pendiente_firma' => 'warning text-dark',
                            default => 'info'
                        };
                        $estadoTexto = match($estadoActual) {
                            'pendiente_firma' => 'Pendiente de firma',
                            'borrador' => 'Borrador',
                            'aprobado' => 'Aprobado',
                            'cerrado' => 'Cerrado',
                            default => ucfirst($estadoActual)
                        };
                        ?>
                        <span class="badge bg-<?= $badgeClass ?> estado-badge ms-2">
                            <?= $estadoTexto ?>
                        </span>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="bg-white bg-opacity-10 rounded p-2">
                        <div><strong class="text-success">Presupuestado:</strong> $<?= number_format($totales['general_presupuestado'], 0, ',', '.') ?></div>
                        <div><strong class="text-warning">Ejecutado:</strong> $<?= number_format($totales['general_ejecutado'], 0, ',', '.') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Indicador de guardado -->
        <div class="saving-indicator mb-2" id="savingIndicator">
            <i class="bi bi-check-circle me-1"></i>Guardado automaticamente
        </div>

        <!-- Tabla de Presupuesto -->
        <div class="card shadow-sm">
            <div class="card-header bg-light py-2">
                <div class="row align-items-center">
                    <div class="col">
                        <strong>SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</strong><br>
                        <small class="text-muted">Codigo: <?= $codigoDocumento ?? 'FT-SST-001' ?> | Version: <?= $versionDocumento ?? '001' ?></small>
                    </div>
                    <div class="col-auto">
                        <strong class="text-success">Total Presupuestado: $<?= number_format($totales['general_presupuestado'], 0, ',', '.') ?></strong>
                        <span class="mx-2">|</span>
                        <strong class="text-warning">Total Ejecutado: $<?= number_format($totales['general_ejecutado'], 0, ',', '.') ?></strong>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm presupuesto-table mb-0" id="tablaPresupuesto">
                        <thead>
                            <tr>
                                <th rowspan="2" class="sticky-col" style="width: 35px;">Item</th>
                                <th rowspan="2" class="sticky-col-2 item-actividad">Actividad</th>
                                <th rowspan="2" class="sticky-col-3 item-descripcion">Descripcion</th>
                                <?php foreach ($meses as $mes): ?>
                                    <th colspan="2" class="col-mes-header"><?= $mes['nombre'] ?></th>
                                <?php endforeach; ?>
                                <th colspan="2" class="col-mes-header">TOTAL</th>
                                <?php if ($puedeEditar): ?>
                                    <th rowspan="2" style="width: 50px;">Acc.</th>
                                <?php endif; ?>
                            </tr>
                            <tr>
                                <?php foreach ($meses as $mes): ?>
                                    <th class="sub-header" style="width: 90px;">Presup.</th>
                                    <th class="sub-header" style="width: 90px;">Ejec.</th>
                                <?php endforeach; ?>
                                <th class="sub-header" style="width: 90px;">Presup.</th>
                                <th class="sub-header" style="width: 90px;">Ejec.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $categoriaActual = '';
                            $itemsArray = is_array($items) ? $items : iterator_to_array($items);
                            $totalItems = count($itemsArray);

                            foreach ($itemsArray as $idx => $item):
                                // Encabezado de categoria
                                if ($categoriaActual !== $item['categoria_codigo']):
                                    $categoriaActual = $item['categoria_codigo'];
                            ?>
                                <tr class="categoria-header">
                                    <td colspan="3" class="sticky-col">
                                        <strong><?= $item['categoria_codigo'] ?>. <?= esc($item['categoria_nombre']) ?></strong>
                                    </td>
                                    <td colspan="<?= count($meses) * 2 + 2 + ($puedeEditar ? 1 : 0) ?>"></td>
                                </tr>
                            <?php endif; ?>

                            <!-- Fila de item -->
                            <tr data-item-id="<?= $item['id_item'] ?>">
                                <td class="sticky-col text-center"><?= esc($item['codigo_item']) ?></td>
                                <td class="sticky-col-2 item-actividad">
                                    <?php if ($puedeEditar): ?>
                                        <input type="text" class="form-control form-control-sm actividad-input"
                                               value="<?= esc($item['actividad']) ?>"
                                               data-item-id="<?= $item['id_item'] ?>">
                                    <?php else: ?>
                                        <?= esc($item['actividad']) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="sticky-col-3 item-descripcion">
                                    <?php if ($puedeEditar): ?>
                                        <input type="text" class="form-control form-control-sm descripcion-input"
                                               value="<?= esc($item['descripcion'] ?? '') ?>"
                                               data-item-id="<?= $item['id_item'] ?>"
                                               placeholder="Descripcion...">
                                    <?php else: ?>
                                        <?= esc($item['descripcion'] ?? '') ?>
                                    <?php endif; ?>
                                </td>

                                <?php foreach ($meses as $mes):
                                    $detalle = $item['detalles'][$mes['numero']] ?? null;
                                    $presupuestado = $detalle ? floatval($detalle['presupuestado']) : 0;
                                    $ejecutado = $detalle ? floatval($detalle['ejecutado']) : 0;
                                ?>
                                    <td>
                                        <?php if ($puedeEditar): ?>
                                            <input type="text" class="monto-input presupuestado-input"
                                                   value="<?= $presupuestado > 0 ? number_format($presupuestado, 0, '', '') : '' ?>"
                                                   data-item-id="<?= $item['id_item'] ?>"
                                                   data-mes="<?= $mes['numero'] ?>"
                                                   data-anio="<?= $mes['anio'] ?>"
                                                   data-tipo="presupuestado"
                                                   placeholder="0">
                                        <?php else: ?>
                                            <span class="text-end d-block">
                                                <?= $presupuestado > 0 ? '$' . number_format($presupuestado, 0, ',', '.') : '-' ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($presupuesto['estado'] !== 'cerrado'): ?>
                                            <input type="text" class="monto-input ejecutado ejecutado-input"
                                                   value="<?= $ejecutado > 0 ? number_format($ejecutado, 0, '', '') : '' ?>"
                                                   data-item-id="<?= $item['id_item'] ?>"
                                                   data-mes="<?= $mes['numero'] ?>"
                                                   data-anio="<?= $mes['anio'] ?>"
                                                   data-tipo="ejecutado"
                                                   placeholder="0">
                                        <?php else: ?>
                                            <span class="text-end d-block">
                                                <?= $ejecutado > 0 ? '$' . number_format($ejecutado, 0, ',', '.') : '-' ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>

                                <!-- Totales del item -->
                                <td class="total-cell total-presupuestado-<?= $item['id_item'] ?>">
                                    $<?= number_format($item['total_presupuestado'], 0, ',', '.') ?>
                                </td>
                                <td class="total-cell total-ejecutado-<?= $item['id_item'] ?>">
                                    $<?= number_format($item['total_ejecutado'], 0, ',', '.') ?>
                                </td>

                                <?php if ($puedeEditar): ?>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-accion btn-eliminar-item"
                                                data-item-id="<?= $item['id_item'] ?>" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>

                            <?php
                            // Subtotal de categoria (si es el ultimo item de la categoria)
                            $nextIdx = $idx + 1;
                            $nextItem = $nextIdx < $totalItems ? $itemsArray[$nextIdx] : null;
                            if ($nextItem === null || $nextItem['categoria_codigo'] !== $categoriaActual):
                                $totCat = $totales['por_categoria'][$categoriaActual] ?? ['presupuestado' => 0, 'ejecutado' => 0, 'por_mes' => []];
                            ?>
                                <tr class="subtotal-row">
                                    <td colspan="3" class="sticky-col text-end"><strong>Sub Total</strong></td>
                                    <?php foreach ($meses as $mes):
                                        $totMes = $totCat['por_mes'][$mes['numero']] ?? ['presupuestado' => 0, 'ejecutado' => 0];
                                    ?>
                                        <td class="text-end">$<?= number_format($totMes['presupuestado'], 0, ',', '.') ?></td>
                                        <td class="text-end">$<?= number_format($totMes['ejecutado'], 0, ',', '.') ?></td>
                                    <?php endforeach; ?>
                                    <td class="text-end"><strong>$<?= number_format($totCat['presupuestado'], 0, ',', '.') ?></strong></td>
                                    <td class="text-end"><strong>$<?= number_format($totCat['ejecutado'], 0, ',', '.') ?></strong></td>
                                    <?php if ($puedeEditar): ?>
                                        <td></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>

                            <?php if (empty($itemsArray)): ?>
                                <tr>
                                    <td colspan="<?= 3 + count($meses) * 2 + 2 + ($puedeEditar ? 1 : 0) ?>" class="text-center py-4">
                                        <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">No hay items en el presupuesto. Haga clic en "Agregar Item" para comenzar.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <!-- Total General -->
                                <tr class="total-row">
                                    <td colspan="3" class="text-end"><strong>TOTAL GENERAL</strong></td>
                                    <?php foreach ($meses as $mes):
                                        $totMes = $totales['por_mes'][$mes['numero']] ?? ['presupuestado' => 0, 'ejecutado' => 0];
                                    ?>
                                        <td class="text-end">$<?= number_format($totMes['presupuestado'], 0, ',', '.') ?></td>
                                        <td class="text-end">$<?= number_format($totMes['ejecutado'], 0, ',', '.') ?></td>
                                    <?php endforeach; ?>
                                    <td class="text-end" id="totalGeneralPresupuestado">
                                        <strong>$<?= number_format($totales['general_presupuestado'], 0, ',', '.') ?></strong>
                                    </td>
                                    <td class="text-end" id="totalGeneralEjecutado">
                                        <strong>$<?= number_format($totales['general_ejecutado'], 0, ',', '.') ?></strong>
                                    </td>
                                    <?php if ($puedeEditar): ?>
                                        <td></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <!-- Firmas del presupuesto -->
                        <div class="d-flex gap-4 flex-wrap">
                            <!-- ELABORÓ: Consultor SST -->
                            <div>
                                <small class="text-muted d-block"><strong>ELABORÓ (CONSULTOR SST):</strong></small>
                                <span class="text-primary"><?= esc($consultor['nombre_consultor'] ?? 'No asignado') ?></span>
                                <?php if (!empty($consultor['numero_licencia'])): ?>
                                    <br><small class="text-muted">Lic: <?= esc($consultor['numero_licencia']) ?></small>
                                <?php endif; ?>
                            </div>
                            <!-- APROBÓ: Representante Legal -->
                            <div>
                                <small class="text-muted d-block"><strong>APROBÓ (REP. LEGAL):</strong></small>
                                <?php if (!empty($presupuesto['firma_imagen'])): ?>
                                    <img src="<?= base_url($presupuesto['firma_imagen']) ?>" alt="Firma" class="border rounded" style="max-height: 50px;">
                                    <br><small class="text-success"><?= esc($presupuesto['firmado_por'] ?? '') ?> - <?= date('d/m/Y', strtotime($presupuesto['fecha_aprobacion'])) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">____________________</span>
                                <?php endif; ?>
                            </div>
                            <?php
                            // Mostrar firma del Delegado SST si el cliente tiene delegado configurado
                            $tieneDelegado = !empty($contexto['delegado_sst_nombre']);
                            ?>
                            <?php if ($tieneDelegado): ?>
                            <!-- REVISÓ: Delegado SST -->
                            <div>
                                <small class="text-muted d-block"><strong>REVISÓ (DELEGADO SST):</strong></small>
                                <?php if (!empty($presupuesto['firma_delegado_imagen'])): ?>
                                    <img src="<?= base_url($presupuesto['firma_delegado_imagen']) ?>" alt="Firma Delegado" class="border rounded" style="max-height: 50px;">
                                    <br><small class="text-success"><?= esc($presupuesto['firmado_delegado_por'] ?? $contexto['delegado_sst_nombre']) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">____________________</span>
                                    <?php if ($presupuesto['estado'] === 'aprobado'): ?>
                                        <br><small class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Pendiente firma Delegado</small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>Use el boton PDF para generar el documento con firmas
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Item -->
    <div class="modal fade" id="modalAgregarItem" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Agregar Item al Presupuesto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formAgregarItem">
                        <input type="hidden" name="id_presupuesto" value="<?= $presupuesto['id_presupuesto'] ?>">
                        <input type="hidden" name="codigo_item" id="codigoItemHidden" value="">

                        <div class="mb-3">
                            <label class="form-label">Categoria</label>
                            <select name="id_categoria" id="selectCategoria" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id_categoria'] ?>" data-codigo="<?= $cat['codigo'] ?>"><?= $cat['codigo'] ?>. <?= esc($cat['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Actividad</label>
                            <select name="actividad" id="selectActividad" class="form-select" required disabled>
                                <option value="">Primero seleccione categoria...</option>
                            </select>
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" id="chkActividadManual">
                                <label class="form-check-label small text-muted" for="chkActividadManual">Escribir actividad manualmente</label>
                            </div>
                            <input type="text" name="actividad_manual" id="inputActividadManual" class="form-control mt-2 d-none" placeholder="Escriba la actividad...">
                        </div>

                        <div class="row">
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label">Codigo</label>
                                    <input type="text" id="codigoItemDisplay" class="form-control bg-light" readonly placeholder="Auto">
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="mb-3">
                                    <label class="form-label">Valor Presupuestado ($)</label>
                                    <input type="text" name="valor_inicial" id="valorInicial" class="form-control text-end" placeholder="0" value="">
                                    <small class="text-muted">Se aplicara a cada mes seleccionado</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Meses a presupuestar <small class="text-muted">(seleccione uno o mas)</small></label>
                            <div class="row g-2">
                                <div class="col-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input chk-mes" type="checkbox" name="meses[]" value="1" id="chkMes1">
                                        <label class="form-check-label" for="chkMes1">Enero</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input chk-mes" type="checkbox" name="meses[]" value="2" id="chkMes2">
                                        <label class="form-check-label" for="chkMes2">Febrero</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input chk-mes" type="checkbox" name="meses[]" value="3" id="chkMes3">
                                        <label class="form-check-label" for="chkMes3">Marzo</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input chk-mes" type="checkbox" name="meses[]" value="4" id="chkMes4">
                                        <label class="form-check-label" for="chkMes4">Abril</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input chk-mes" type="checkbox" name="meses[]" value="5" id="chkMes5">
                                        <label class="form-check-label" for="chkMes5">Mayo</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input chk-mes" type="checkbox" name="meses[]" value="6" id="chkMes6">
                                        <label class="form-check-label" for="chkMes6">Junio</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input chk-mes" type="checkbox" name="meses[]" value="7" id="chkMes7">
                                        <label class="form-check-label" for="chkMes7">Julio</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input chk-mes" type="checkbox" name="meses[]" value="8" id="chkMes8">
                                        <label class="form-check-label" for="chkMes8">Agosto</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input chk-mes" type="checkbox" name="meses[]" value="9" id="chkMes9">
                                        <label class="form-check-label" for="chkMes9">Septiembre</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input chk-mes" type="checkbox" name="meses[]" value="10" id="chkMes10">
                                        <label class="form-check-label" for="chkMes10">Octubre</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input chk-mes" type="checkbox" name="meses[]" value="11" id="chkMes11">
                                        <label class="form-check-label" for="chkMes11">Noviembre</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input chk-mes" type="checkbox" name="meses[]" value="12" id="chkMes12">
                                        <label class="form-check-label" for="chkMes12">Diciembre</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSeleccionarTodos">Todos</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeseleccionarTodos">Ninguno</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarItem">
                        <i class="bi bi-save me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Enviar a Firmas -->
    <div class="modal fade" id="modalEnviarFirmas" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pen me-2"></i>Enviar Presupuesto a Firmas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Al enviar a firmas, el presupuesto pasara a estado <strong>"Pendiente de Firma"</strong>
                        y no podra ser modificado hasta que sea aprobado o rechazado.
                    </div>

                    <h6 class="mb-3">Resumen del Presupuesto <?= $anio ?></h6>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Concepto</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Total Presupuestado</td>
                                    <td class="text-end text-success fw-bold">$<?= number_format($totales['general_presupuestado'], 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>Total Ejecutado</td>
                                    <td class="text-end text-warning fw-bold">$<?= number_format($totales['general_ejecutado'], 0, ',', '.') ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <h6>Acciones a realizar:</h6>
                        <ol class="small">
                            <li>Descargar el PDF del presupuesto con los campos de firma</li>
                            <li>Obtener las firmas del Representante Legal y Responsable SST</li>
                            <li>Subir el documento firmado o cambiar el estado manualmente</li>
                        </ol>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <a href="<?= base_url('documentos-sst/presupuesto/pdf/' . $cliente['id_cliente'] . '/' . $anio) ?>"
                           target="_blank" class="btn btn-danger">
                            <i class="bi bi-file-earmark-pdf me-2"></i>Descargar PDF para Firma
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="<?= base_url('documentos-sst/presupuesto/estado/' . $presupuesto['id_presupuesto'] . '/pendiente_firma') ?>"
                       target="_blank" class="btn btn-warning"
                       onclick="return confirm('¿Confirma que desea cambiar el estado a Pendiente de Firma? El presupuesto no podra ser editado hasta que sea aprobado.');">
                        <i class="bi bi-send me-1"></i>Cambiar a Pendiente de Firma
                    </a>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Compartir Enlace de Consulta -->
    <div class="modal fade" id="modalCompartirConsulta" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-share me-2"></i>Compartir Presupuesto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Comparta este enlace con el cliente para que pueda consultar el presupuesto (solo lectura):</p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="inputEnlaceConsulta" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="btnCopiarEnlace">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <div id="msgEnlaceCopiado" class="text-success small d-none">
                        <i class="bi bi-check-circle me-1"></i>Enlace copiado al portapapeles
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const idPresupuesto = <?= $presupuesto['id_presupuesto'] ?>;
        let saveTimeout = null;

        // Formatear numero como moneda
        function formatMoney(value) {
            return '$' + parseInt(value || 0).toLocaleString('es-CO');
        }

        // Mostrar indicador de guardado
        function showSaveIndicator() {
            const indicator = document.getElementById('savingIndicator');
            indicator.style.display = 'block';
            setTimeout(() => indicator.style.display = 'none', 2000);
        }

        // Guardar monto con debounce
        function saveMonto(input) {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                const data = new FormData();
                data.append('id_item', input.dataset.itemId);
                data.append('mes', input.dataset.mes);
                data.append('anio', input.dataset.anio);
                data.append('tipo', input.dataset.tipo);
                data.append('valor', input.value.replace(/[^0-9]/g, ''));

                fetch('<?= base_url("documentos-sst/presupuesto/actualizar-monto") ?>', {
                    method: 'POST',
                    body: data
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        // Actualizar totales del item
                        const totPresup = document.querySelector(`.total-presupuestado-${input.dataset.itemId}`);
                        const totEjec = document.querySelector(`.total-ejecutado-${input.dataset.itemId}`);
                        if (totPresup) totPresup.textContent = formatMoney(result.total_presupuestado);
                        if (totEjec) totEjec.textContent = formatMoney(result.total_ejecutado);

                        showSaveIndicator();
                    }
                })
                .catch(error => console.error('Error:', error));
            }, 500);
        }

        // Event listeners para inputs de monto
        document.querySelectorAll('.monto-input').forEach(input => {
            input.addEventListener('input', function() {
                // Solo permitir numeros
                let value = this.value.replace(/[^0-9]/g, '');
                this.value = value;
            });

            input.addEventListener('change', function() {
                saveMonto(this);
            });

            input.addEventListener('blur', function() {
                saveMonto(this);
            });
        });

        // Guardar actividad/descripcion
        function saveItemField(input) {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                const row = input.closest('tr');
                const actividadInput = row.querySelector('.actividad-input');
                const descripcionInput = row.querySelector('.descripcion-input');

                const data = new FormData();
                data.append('id_item', input.dataset.itemId);
                data.append('actividad', actividadInput ? actividadInput.value : '');
                data.append('descripcion', descripcionInput ? descripcionInput.value : '');

                fetch('<?= base_url("documentos-sst/presupuesto/actualizar-item") ?>', {
                    method: 'POST',
                    body: data
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showSaveIndicator();
                    }
                })
                .catch(error => console.error('Error:', error));
            }, 500);
        }

        document.querySelectorAll('.actividad-input, .descripcion-input').forEach(input => {
            input.addEventListener('change', function() {
                saveItemField(this);
            });
        });

        // Agregar nuevo item
        document.getElementById('btnGuardarItem')?.addEventListener('click', function() {
            const form = document.getElementById('formAgregarItem');
            const data = new FormData(form);

            // Determinar actividad (del select o manual)
            const chkManual = document.getElementById('chkActividadManual');
            const selectActividad = document.getElementById('selectActividad');
            const inputManual = document.getElementById('inputActividadManual');

            let actividad = '';
            if (chkManual.checked) {
                actividad = inputManual.value.trim();
            } else {
                actividad = selectActividad.value;
            }

            if (!actividad) {
                alert('Debe seleccionar o escribir una actividad');
                return;
            }

            // Sobrescribir actividad en FormData
            data.set('actividad', actividad);

            // Agregar valor y meses seleccionados
            const valorInicial = document.getElementById('valorInicial').value.replace(/[^0-9]/g, '');
            data.set('valor_inicial', valorInicial || '0');

            // Obtener meses seleccionados
            const mesesSeleccionados = [];
            document.querySelectorAll('.chk-mes:checked').forEach(chk => {
                mesesSeleccionados.push(chk.value);
            });

            if (mesesSeleccionados.length === 0) {
                alert('Debe seleccionar al menos un mes');
                return;
            }

            data.set('meses', JSON.stringify(mesesSeleccionados));

            fetch('<?= base_url("documentos-sst/presupuesto/agregar-item") ?>', {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Recargar con parametro para mostrar toast
                    window.location.href = window.location.pathname + '?saved=1';
                } else {
                    alert(result.message || 'Error al agregar item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexion');
            });
        });

        // Eliminar item
        document.querySelectorAll('.btn-eliminar-item').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!confirm('Eliminar este item del presupuesto?')) return;

                const data = new FormData();
                data.append('id_item', this.dataset.itemId);

                fetch('<?= base_url("documentos-sst/presupuesto/eliminar-item") ?>', {
                    method: 'POST',
                    body: data
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        this.closest('tr').remove();
                        showSaveIndicator();
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });

        // ========================================
        // MODAL AGREGAR ITEM - Actividades predefinidas
        // ========================================
        const actividadesPorCategoria = {
            '1': [ // Talento Humano SST
                'Honorarios Responsable SST',
                'Honorarios Consultor SST externo',
                'Salario Coordinador SST',
                'Apoyo administrativo SST',
                'Asesorias especializadas SST',
                'Auditoria interna SG-SST',
                'Auditoria externa SG-SST'
            ],
            '2': [ // Capacitacion y Formacion
                'Induccion y reinduccion SST',
                'Capacitacion COPASST/Vigia',
                'Capacitacion trabajo en alturas',
                'Capacitacion manejo de cargas',
                'Capacitacion riesgo quimico',
                'Capacitacion riesgo electrico',
                'Capacitacion primeros auxilios',
                'Capacitacion uso de EPP',
                'Capacitacion ergonomia',
                'Capacitacion prevencion incendios',
                'Capacitacion manejo defensivo',
                'Capacitacion brigadas de emergencia',
                'Curso virtual 50 horas SG-SST'
            ],
            '3': [ // Medicina Preventiva y del Trabajo
                'Examenes medicos ocupacionales ingreso',
                'Examenes medicos periodicos',
                'Examenes medicos de egreso',
                'Examenes complementarios (audiometria, espirometria)',
                'Vacunacion ocupacional',
                'Valoraciones medicas especiales',
                'Diagnostico condiciones de salud',
                'Programa de vigilancia epidemiologica'
            ],
            '4': [ // Promocion y Prevencion
                'Pausas activas y gimnasia laboral',
                'Campanas de salud (cardiovascular, visual)',
                'Semana de la salud ocupacional',
                'Programa de estilos de vida saludable',
                'Material educativo SST'
            ],
            '5': [ // Seguridad Industrial e Higiene
                'EPP proteccion respiratoria',
                'EPP proteccion visual',
                'EPP proteccion auditiva',
                'EPP proteccion manos (guantes)',
                'EPP proteccion pies (calzado)',
                'EPP proteccion corporal',
                'EPP trabajo en alturas',
                'Dotacion de trabajo',
                'Reposicion de EPP',
                'Senalizacion de seguridad',
                'Mediciones ambientales (ruido, iluminacion)',
                'Estudios de puestos de trabajo',
                'Inspecciones de seguridad',
                'Investigacion de accidentes e incidentes'
            ],
            '6': [ // Gestion de Emergencias
                'Extintores y recarga',
                'Botiquines y reposicion',
                'Camillas y equipos de rescate',
                'Senalizacion de emergencias',
                'Simulacros de evacuacion',
                'Plan de emergencias',
                'Kit de derrames',
                'Alarmas y sistemas de alerta'
            ],
            '7': [ // Otros Gastos SST
                'Software y licencias SG-SST',
                'Papeleria y materiales SST',
                'Mantenimiento de equipos de seguridad',
                'Imprevistos SST'
            ]
        };

        // Conteo de items existentes por categoria (para generar codigo)
        const itemsPorCategoria = {};
        <?php foreach ($itemsArray as $item): ?>
            if (!itemsPorCategoria['<?= $item['categoria_codigo'] ?>']) {
                itemsPorCategoria['<?= $item['categoria_codigo'] ?>'] = 0;
            }
            itemsPorCategoria['<?= $item['categoria_codigo'] ?>']++;
        <?php endforeach; ?>

        const selectCategoria = document.getElementById('selectCategoria');
        const selectActividad = document.getElementById('selectActividad');
        const codigoDisplay = document.getElementById('codigoItemDisplay');
        const codigoHidden = document.getElementById('codigoItemHidden');
        const chkManual = document.getElementById('chkActividadManual');
        const inputManual = document.getElementById('inputActividadManual');
        const valorInicial = document.getElementById('valorInicial');

        // Cambio de categoria - cargar actividades y generar codigo
        selectCategoria?.addEventListener('change', function() {
            const idCategoria = this.value;
            const codigoCategoria = this.options[this.selectedIndex]?.dataset?.codigo || '';

            // Limpiar y habilitar selector de actividad
            selectActividad.innerHTML = '<option value="">Seleccione actividad...</option>';
            selectActividad.disabled = false;
            chkManual.checked = false;
            inputManual.classList.add('d-none');
            inputManual.value = '';

            if (idCategoria && actividadesPorCategoria[codigoCategoria]) {
                actividadesPorCategoria[codigoCategoria].forEach(act => {
                    const opt = document.createElement('option');
                    opt.value = act;
                    opt.textContent = act;
                    selectActividad.appendChild(opt);
                });
            }

            // Generar codigo automatico
            if (codigoCategoria) {
                const countExistente = itemsPorCategoria[codigoCategoria] || 0;
                const nuevoCodigo = codigoCategoria + '.' + (countExistente + 1);
                codigoDisplay.value = nuevoCodigo;
                codigoHidden.value = nuevoCodigo;
            } else {
                codigoDisplay.value = '';
                codigoHidden.value = '';
            }
        });

        // Toggle actividad manual
        chkManual?.addEventListener('change', function() {
            if (this.checked) {
                selectActividad.disabled = true;
                selectActividad.value = '';
                inputManual.classList.remove('d-none');
                inputManual.required = true;
                inputManual.focus();
            } else {
                selectActividad.disabled = false;
                inputManual.classList.add('d-none');
                inputManual.required = false;
                inputManual.value = '';
            }
        });

        // Formatear valor inicial
        valorInicial?.addEventListener('input', function() {
            let value = this.value.replace(/[^0-9]/g, '');
            this.value = value;
        });

        // Botones seleccionar todos/ninguno meses
        document.getElementById('btnSeleccionarTodos')?.addEventListener('click', function() {
            document.querySelectorAll('.chk-mes').forEach(chk => chk.checked = true);
        });

        document.getElementById('btnDeseleccionarTodos')?.addEventListener('click', function() {
            document.querySelectorAll('.chk-mes').forEach(chk => chk.checked = false);
        });

        // Reset modal al abrir
        document.getElementById('modalAgregarItem')?.addEventListener('show.bs.modal', function() {
            document.getElementById('formAgregarItem').reset();
            selectActividad.innerHTML = '<option value="">Primero seleccione categoria...</option>';
            selectActividad.disabled = true;
            codigoDisplay.value = '';
            codigoHidden.value = '';
            inputManual.classList.add('d-none');
            // Deseleccionar todos los meses
            document.querySelectorAll('.chk-mes').forEach(chk => chk.checked = false);
        });

        // ========================================
        // COMPARTIR ENLACE DE CONSULTA (opcional)
        // ========================================
        document.getElementById('btnCompartirConsulta')?.addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;

            const data = new FormData();
            data.append('id_presupuesto', <?= $presupuesto['id_presupuesto'] ?>);

            fetch('<?= base_url("documentos-sst/presupuesto/generar-token-consulta") ?>', {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(result => {
                btn.disabled = false;
                if (result.success) {
                    document.getElementById('inputEnlaceConsulta').value = result.url;
                    const modal = new bootstrap.Modal(document.getElementById('modalCompartirConsulta'));
                    modal.show();
                } else {
                    alert('Error: ' + (result.message || 'No se pudo generar el enlace'));
                }
            })
            .catch(error => {
                btn.disabled = false;
                console.error('Error:', error);
                alert('Error de conexion');
            });
        });

        // Copiar enlace al portapapeles
        document.getElementById('btnCopiarEnlace')?.addEventListener('click', function() {
            const input = document.getElementById('inputEnlaceConsulta');
            input.select();
            document.execCommand('copy');

            // Mostrar mensaje de copiado
            const msg = document.getElementById('msgEnlaceCopiado');
            msg.classList.remove('d-none');
            setTimeout(() => msg.classList.add('d-none'), 2000);
        });
        // ========================================
        // TOAST PARA INDICAR VER PDF
        // ========================================
        // Mostrar toast si se guardo un item
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('saved') === '1') {
            showPdfToast();
            // Limpiar el parametro de la URL sin recargar
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        function showPdfToast() {
            const toastEl = document.getElementById('toastPdf');
            if (toastEl) {
                const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
                toast.show();
            }
        }
    });
    </script>

    <!-- Toast para indicar ver PDF -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toastPdf" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="bi bi-check-circle me-2"></i>
                <strong class="me-auto">Item guardado</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                <i class="bi bi-file-earmark-pdf me-1"></i>
                <strong>Ver PDF</strong> para generar el documento con firmas de aprobacion.
            </div>
        </div>
    </div>
</body>
</html>
