<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta <?= esc($acta['numero_acta']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
        }
        .acta-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        .acta-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background: transparent;
            border-bottom: 1px solid #eee;
        }
        .firma-img {
            max-height: 80px;
            max-width: 150px;
        }
        @media print {
            .no-print { display: none !important; }
            .acta-header { background: #333 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="acta-container">
        <!-- Header -->
        <div class="acta-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-1">Acta <?= esc($acta['numero_acta']) ?></h2>
                    <p class="mb-0 opacity-75"><?= esc($comite['tipo_nombre']) ?></p>
                    <p class="mb-0 opacity-75"><?= esc($cliente['nombre_cliente']) ?></p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-white text-dark fs-6">
                        <?= ucfirst($acta['tipo_acta']) ?>
                    </span>
                    <br>
                    <span class="mt-2 d-inline-block">
                        <?= date('d/m/Y', strtotime($acta['fecha_reunion'])) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="d-flex gap-2 mb-4 no-print">
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="bi bi-printer me-1"></i> Imprimir
            </button>
        </div>

        <!-- Información de la reunión -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informacion de la Reunion</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <small class="text-muted d-block">Fecha</small>
                        <strong><?= date('d/m/Y', strtotime($acta['fecha_reunion'])) ?></strong>
                    </div>
                    <div class="col-md-3 mb-3">
                        <small class="text-muted d-block">Horario</small>
                        <strong><?= $acta['hora_inicio'] ?> - <?= $acta['hora_fin'] ?></strong>
                    </div>
                    <div class="col-md-3 mb-3">
                        <small class="text-muted d-block">Modalidad</small>
                        <strong><?= ucfirst($acta['modalidad']) ?></strong>
                    </div>
                    <div class="col-md-3 mb-3">
                        <small class="text-muted d-block">Lugar</small>
                        <strong><?= esc($acta['lugar'] ?? 'No especificado') ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asistentes -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>Asistentes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Cargo</th>
                                <th>Rol</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($asistentes as $asist): ?>
                            <tr>
                                <td><?= esc($asist['nombre_completo']) ?></td>
                                <td><?= esc($asist['cargo'] ?? '-') ?></td>
                                <td>
                                    <?php if (!empty($asist['rol_comite']) && $asist['rol_comite'] !== 'miembro'): ?>
                                        <span class="badge bg-warning text-dark"><?= ucfirst($asist['rol_comite']) ?></span>
                                    <?php else: ?>
                                        Miembro
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Orden del día -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-ol me-2"></i>Orden del Dia</h5>
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    <?php
                    $ordenDia = is_string($acta['orden_del_dia']) ? json_decode($acta['orden_del_dia'], true) : $acta['orden_del_dia'];
                    if (!empty($ordenDia)):
                        foreach ($ordenDia as $punto):
                    ?>
                    <li class="mb-1"><?= esc($punto['tema'] ?? '') ?></li>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </ol>
            </div>
        </div>

        <!-- Desarrollo -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Desarrollo de la Reunion</h5>
            </div>
            <div class="card-body">
                <?php
                $desarrollo = is_string($acta['desarrollo']) ? json_decode($acta['desarrollo'], true) : $acta['desarrollo'];
                if (!empty($ordenDia)):
                    foreach ($ordenDia as $punto):
                        $numPunto = $punto['punto'] ?? '';
                        $contenido = $desarrollo[$numPunto] ?? '';
                ?>
                <div class="mb-4">
                    <h6 class="fw-bold"><?= $numPunto ?>. <?= esc($punto['tema'] ?? '') ?></h6>
                    <?php if (!empty($contenido)): ?>
                        <p class="mb-0" style="white-space: pre-line;"><?= esc($contenido) ?></p>
                    <?php else: ?>
                        <p class="text-muted mb-0 fst-italic">Sin desarrollo registrado</p>
                    <?php endif; ?>
                </div>
                <?php
                    endforeach;
                endif;
                ?>
            </div>
        </div>

        <!-- Compromisos -->
        <?php if (!empty($compromisos)): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Compromisos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Compromiso</th>
                                <th>Responsable</th>
                                <th>Fecha Limite</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($compromisos as $comp): ?>
                            <tr>
                                <td><?= esc($comp['descripcion']) ?></td>
                                <td><?= esc($comp['responsable_nombre'] ?? 'Sin asignar') ?></td>
                                <td><?= date('d/m/Y', strtotime($comp['fecha_limite'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Próxima reunión -->
        <?php if (!empty($acta['proxima_reunion_fecha'])): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>Proxima Reunion</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Fecha</small>
                        <strong><?= date('d/m/Y', strtotime($acta['proxima_reunion_fecha'])) ?></strong>
                    </div>
                    <?php if (!empty($acta['proxima_reunion_hora'])): ?>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Hora</small>
                        <strong><?= $acta['proxima_reunion_hora'] ?></strong>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($acta['proxima_reunion_lugar'])): ?>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Lugar</small>
                        <strong><?= esc($acta['proxima_reunion_lugar']) ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Firmas -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pen me-2"></i>Firmas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($asistentes as $asist): ?>
                    <div class="col-md-4 mb-4 text-center">
                        <?php if (!empty($asist['firma_imagen'])): ?>
                            <img src="data:image/png;base64,<?= $asist['firma_imagen'] ?>" alt="Firma" class="firma-img mb-2">
                        <?php else: ?>
                            <div class="border rounded p-3 mb-2" style="height: 80px; display: flex; align-items: center; justify-content: center;">
                                <span class="text-muted">Pendiente</span>
                            </div>
                        <?php endif; ?>
                        <hr style="margin: 5px 30px;">
                        <strong class="d-block"><?= esc($asist['nombre_completo']) ?></strong>
                        <small class="text-muted"><?= esc($asist['cargo'] ?? '') ?></small>
                        <?php if (!empty($asist['rol_comite']) && $asist['rol_comite'] !== 'miembro'): ?>
                            <br><small class="badge bg-warning text-dark"><?= ucfirst($asist['rol_comite']) ?></small>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-muted mt-4">
            <small>
                <i class="bi bi-shield-check me-1"></i>
                Documento generado por EnterpriseSST
                <?php if (!empty($acta['codigo_verificacion'])): ?>
                    | Codigo de verificacion: <?= esc($acta['codigo_verificacion']) ?>
                <?php endif; ?>
            </small>
        </div>
    </div>
</body>
</html>
