<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($carpeta['nombre']) ?> - Documentación SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .breadcrumb-item a { text-decoration: none; }
        .doc-card { transition: transform 0.2s; }
        .doc-card:hover { transform: translateY(-3px); }
        .folder-card { background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); }
        .estado-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/documentacion/<?= $cliente['id_cliente'] ?>">
                <i class="bi bi-folder-fill me-2"></i>Documentación SST
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="/documentacion/<?= $cliente['id_cliente'] ?>">
                        <i class="bi bi-house me-1"></i>Inicio
                    </a>
                </li>
                <?php if (!empty($breadcrumb)): ?>
                    <?php foreach ($breadcrumb as $item): ?>
                        <?php if ($item['id_carpeta'] == $carpeta['id_carpeta']): ?>
                            <li class="breadcrumb-item active"><?= esc($item['nombre']) ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item">
                                <a href="/documentacion/carpeta/<?= $item['id_carpeta'] ?>"><?= esc($item['nombre']) ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="breadcrumb-item active"><?= esc($carpeta['nombre']) ?></li>
                <?php endif; ?>
            </ol>
        </nav>

        <!-- Alertas -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Header de carpeta -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1">
                            <i class="bi bi-folder-fill text-warning me-2"></i>
                            <?= esc($carpeta['nombre']) ?>
                        </h4>
                        <?php if (!empty($carpeta['descripcion'])): ?>
                            <p class="text-muted mb-0"><?= esc($carpeta['descripcion']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="/documentacion/nuevo/<?= $cliente['id_cliente'] ?>?carpeta=<?= $carpeta['id_carpeta'] ?>"
                           class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>Nuevo Documento
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Subcarpetas -->
            <?php if (!empty($subcarpetas)): ?>
                <div class="col-12 mb-4">
                    <h6 class="text-muted mb-3"><i class="bi bi-folder me-2"></i>Subcarpetas</h6>
                    <div class="row g-3">
                        <?php foreach ($subcarpetas as $sub): ?>
                            <div class="col-md-3">
                                <a href="/documentacion/carpeta/<?= $sub['id_carpeta'] ?>" class="text-decoration-none">
                                    <div class="card border-0 shadow-sm doc-card folder-card">
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-folder-fill text-warning" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2 mb-0 text-dark"><?= esc($sub['nombre']) ?></h6>
                                            <small class="text-muted">
                                                <?= $sub['total_documentos'] ?? 0 ?> documento(s)
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Documentos -->
            <div class="col-12">
                <h6 class="text-muted mb-3"><i class="bi bi-file-earmark-text me-2"></i>Documentos</h6>
                <?php if (empty($documentos)): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-file-earmark-x text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No hay documentos en esta carpeta</p>
                            <a href="/documentacion/nuevo/<?= $cliente['id_cliente'] ?>?carpeta=<?= $carpeta['id_carpeta'] ?>"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i>Crear documento
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Versión</th>
                                        <th>Estado</th>
                                        <th>Actualizado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documentos as $doc): ?>
                                        <tr>
                                            <td><code><?= esc($doc['codigo']) ?></code></td>
                                            <td>
                                                <a href="/documentacion/ver/<?= $doc['id_documento'] ?>" class="text-decoration-none">
                                                    <?= esc($doc['nombre']) ?>
                                                </a>
                                            </td>
                                            <td><span class="badge bg-light text-dark"><?= esc($doc['tipo_nombre'] ?? 'N/D') ?></span></td>
                                            <td><span class="badge bg-secondary">v<?= $doc['version_actual'] ?></span></td>
                                            <td>
                                                <?php
                                                    $estadoClass = match($doc['estado']) {
                                                        'borrador' => 'bg-info',
                                                        'en_revision' => 'bg-warning',
                                                        'pendiente_firma' => 'bg-purple',
                                                        'aprobado' => 'bg-success',
                                                        'obsoleto' => 'bg-secondary',
                                                        default => 'bg-light text-dark'
                                                    };
                                                ?>
                                                <span class="badge estado-badge <?= $estadoClass ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $doc['estado'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($doc['updated_at'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="/documentacion/ver/<?= $doc['id_documento'] ?>"
                                                       class="btn btn-outline-primary" title="Ver">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="/documentacion/editar/<?= $doc['id_documento'] ?>"
                                                       class="btn btn-outline-secondary" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="/exportar/pdf/<?= $doc['id_documento'] ?>"
                                                       class="btn btn-outline-danger" title="PDF">
                                                        <i class="bi bi-file-pdf"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
