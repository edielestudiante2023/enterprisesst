<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($documento['codigo']) ?> - <?= esc($documento['nombre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .documento-header {
            background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
            color: white;
            padding: 2rem 0;
        }
        .seccion-content {
            line-height: 1.8;
        }
        .seccion-content p { margin-bottom: 1rem; }
        .seccion-content ul, .seccion-content ol { margin-bottom: 1rem; }
        .toc-link {
            color: #6B7280;
            text-decoration: none;
            display: block;
            padding: 0.5rem 1rem;
            border-left: 2px solid transparent;
        }
        .toc-link:hover {
            color: #3B82F6;
            border-left-color: #3B82F6;
            background-color: #F3F4F6;
        }
        .firma-box {
            border: 1px dashed #D1D5DB;
            padding: 1rem;
            text-align: center;
            min-height: 100px;
        }
        .firma-box.firmado {
            border-color: #10B981;
            background-color: #D1FAE5;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Header del documento -->
    <div class="documento-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item">
                                <a href="/documentacion/<?= $cliente['id_cliente'] ?>" class="text-white-50">Documentación</a>
                            </li>
                            <li class="breadcrumb-item text-white"><?= esc($documento['codigo']) ?></li>
                        </ol>
                    </nav>
                    <h2 class="mb-1"><?= esc($documento['nombre']) ?></h2>
                    <p class="mb-0 text-white-50"><?= esc($documento['descripcion'] ?? '') ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="mb-2">
                        <span class="badge bg-light text-dark me-2">v<?= $documento['version_actual'] ?></span>
                        <?php
                            $estadoClass = match($documento['estado']) {
                                'borrador' => 'bg-info',
                                'en_revision' => 'bg-warning text-dark',
                                'pendiente_firma' => 'bg-purple',
                                'aprobado' => 'bg-success',
                                'obsoleto' => 'bg-secondary',
                                default => 'bg-light text-dark'
                            };
                        ?>
                        <span class="badge <?= $estadoClass ?>"><?= ucfirst(str_replace('_', ' ', $documento['estado'])) ?></span>
                    </div>
                    <div class="btn-group">
                        <a href="/documentacion/editar/<?= $documento['id_documento'] ?>" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil me-1"></i>Editar
                        </a>
                        <a href="/exportar/pdf/<?= $documento['id_documento'] ?>" class="btn btn-light btn-sm">
                            <i class="bi bi-file-pdf me-1"></i>PDF
                        </a>
                        <a href="/firma/solicitar/<?= $documento['id_documento'] ?>" class="btn btn-light btn-sm">
                            <i class="bi bi-pen me-1"></i>Firmas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <div class="row">
            <!-- Tabla de contenido -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm sticky-top" style="top: 1rem;">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Contenido</h6>
                    </div>
                    <div class="card-body p-0">
                        <nav class="nav flex-column">
                            <?php if (!empty($secciones)): ?>
                                <?php foreach ($secciones as $seccion): ?>
                                    <a class="toc-link" href="#seccion-<?= $seccion['numero_seccion'] ?>">
                                        <?= $seccion['numero_seccion'] ?>. <?= esc($seccion['nombre_seccion']) ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Contenido del documento -->
            <div class="col-md-9">
                <!-- Metadatos -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <small class="text-muted d-block">Código</small>
                                <strong><?= esc($documento['codigo']) ?></strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Versión</small>
                                <strong><?= $documento['version_actual'] ?></strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Fecha Creación</small>
                                <strong><?= date('d/m/Y', strtotime($documento['created_at'])) ?></strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Última Actualización</small>
                                <strong><?= date('d/m/Y', strtotime($documento['updated_at'])) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Secciones -->
                <?php if (empty($secciones)): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-file-earmark-x text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">Este documento no tiene contenido aún</p>
                            <a href="/documentacion/editar/<?= $documento['id_documento'] ?>" class="btn btn-primary">
                                <i class="bi bi-pencil me-1"></i>Agregar contenido
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($secciones as $seccion): ?>
                        <div class="card border-0 shadow-sm mb-4" id="seccion-<?= $seccion['numero_seccion'] ?>">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <?= $seccion['numero_seccion'] ?>. <?= esc($seccion['nombre_seccion']) ?>
                                </h5>
                            </div>
                            <div class="card-body seccion-content">
                                <?php if (!empty($seccion['contenido'])): ?>
                                    <?= $seccion['contenido'] ?>
                                <?php else: ?>
                                    <p class="text-muted fst-italic">Sección sin contenido</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Firmas -->
                <?php if (!empty($firmas)): ?>
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-pen me-2"></i>Firmas</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($firmas as $firma): ?>
                                    <div class="col-md-4">
                                        <div class="firma-box <?= $firma['estado'] === 'firmado' ? 'firmado' : '' ?>">
                                            <?php if ($firma['estado'] === 'firmado'): ?>
                                                <i class="bi bi-check-circle text-success fs-3"></i>
                                                <p class="mb-1 fw-bold"><?= esc($firma['firmante_nombre']) ?></p>
                                                <small class="text-muted"><?= esc($firma['firmante_cargo']) ?></small>
                                                <br><small class="text-success">
                                                    Firmado: <?= date('d/m/Y H:i', strtotime($firma['fecha_firma'])) ?>
                                                </small>
                                            <?php else: ?>
                                                <i class="bi bi-hourglass-split text-warning fs-3"></i>
                                                <p class="mb-1">Pendiente de firma</p>
                                                <small class="text-muted"><?= ucfirst($firma['firmante_tipo']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
