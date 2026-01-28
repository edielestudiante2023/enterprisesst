<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        .navbar { background: rgba(255, 255, 255, 0.95); border-bottom: 3px solid #bd9751; }
        .content-wrapper { margin-top: 20px; padding-bottom: 50px; }
        .page-header { background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%); color: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; }
        .carpeta-card { transition: all 0.3s ease; border: none; border-radius: 12px; }
        .carpeta-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .carpeta-icon { font-size: 2rem; color: #f0ad4e; }
        .documento-row:hover { background-color: #f8f9fa; }
        .btn-pdf { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; }
        .btn-pdf:hover { background: linear-gradient(135deg, #c82333 0%, #bd2130 100%); }
        .breadcrumb-item a { color: rgba(255,255,255,0.8); text-decoration: none; }
        .breadcrumb-item a:hover { color: white; }
        .breadcrumb-item.active { color: white; }
    </style>
</head>
<body>
    <!-- Navbar simple -->
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="/dashboardclient">
                <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Logo" height="50">
            </a>
            <div>
                <span class="text-muted me-3"><?= esc($cliente['nombre_cliente'] ?? '') ?></span>
                <a href="/dashboardclient" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-house me-1"></i>Inicio
                </a>
            </div>
        </div>
    </nav>

    <div class="container content-wrapper">
        <!-- Header con breadcrumb -->
        <div class="page-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('client/mis-documentos-sst') ?>">
                            <i class="bi bi-folder-check me-1"></i>Mis Documentos
                        </a>
                    </li>
                    <?php foreach ($ruta as $idx => $r): ?>
                        <?php if ($idx < count($ruta) - 1): ?>
                            <li class="breadcrumb-item">
                                <a href="<?= base_url('client/mis-documentos-sst/carpeta/' . $r['id_carpeta']) ?>">
                                    <?= esc($r['codigo'] ?? $r['nombre']) ?>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item active"><?= esc($r['codigo'] ?? $r['nombre']) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
            <h4 class="mb-1">
                <i class="bi bi-folder2-open me-2"></i><?= esc($carpeta['nombre']) ?>
            </h4>
            <?php if (!empty($carpeta['descripcion'])): ?>
                <p class="mb-0 opacity-75"><?= esc($carpeta['descripcion']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Subcarpetas -->
        <?php if (!empty($subcarpetas)): ?>
        <div class="mb-4">
            <h6 class="text-muted mb-3"><i class="bi bi-folder me-2"></i>Subcarpetas</h6>
            <div class="row g-3">
                <?php foreach ($subcarpetas as $sub): ?>
                <div class="col-md-4 col-lg-3">
                    <a href="<?= base_url('client/mis-documentos-sst/carpeta/' . $sub['id_carpeta']) ?>" class="text-decoration-none" target="_blank">
                        <div class="card carpeta-card h-100 position-relative">
                            <?php if ($sub['total_docs'] > 0): ?>
                                <span class="badge bg-success position-absolute top-0 end-0 m-2"><?= $sub['total_docs'] ?></span>
                            <?php endif; ?>
                            <div class="card-body text-center py-3">
                                <i class="bi bi-folder-fill carpeta-icon"></i>
                                <h6 class="mt-2 mb-0 text-dark"><?= esc($sub['codigo'] ?? $sub['nombre']) ?></h6>
                                <small class="text-muted"><?= esc($sub['nombre']) ?></small>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabla de documentos -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-file-earmark-text me-2"></i>Documentos
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($documentos)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 120px;">Codigo</th>
                                <th>Documento</th>
                                <th style="width: 80px;" class="text-center">Ano</th>
                                <th style="width: 120px;" class="text-center">Estado</th>
                                <th style="width: 100px;" class="text-center">Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documentos as $doc): ?>
                            <tr class="documento-row">
                                <td>
                                    <code class="text-primary"><?= esc($doc['codigo'] ?? 'N/A') ?></code>
                                </td>
                                <td>
                                    <strong><?= esc($doc['titulo']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?= esc($doc['anio']) ?></span>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $estadoBadge = $doc['estado'] === 'firmado' ? 'bg-success' : 'bg-primary';
                                    $estadoTexto = $doc['estado'] === 'firmado' ? 'Firmado' : 'Aprobado';
                                    $estadoIcono = $doc['estado'] === 'firmado' ? 'bi-patch-check-fill' : 'bi-check-circle';
                                    ?>
                                    <span class="badge <?= $estadoBadge ?>">
                                        <i class="bi <?= $estadoIcono ?> me-1"></i><?= $estadoTexto ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('documentos-sst/exportar-pdf/' . $doc['id_documento']) ?>"
                                       class="btn btn-sm btn-pdf text-white"
                                       title="Descargar PDF" target="_blank">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-file-earmark-x text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3 mb-0">No hay documentos disponibles en esta carpeta.</p>
                    <small class="text-muted">Los documentos apareceran aqui cuando sean aprobados.</small>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Boton volver -->
        <div class="mt-4">
            <?php if (count($ruta) > 1): ?>
                <a href="<?= base_url('client/mis-documentos-sst/carpeta/' . $ruta[count($ruta)-2]['id_carpeta']) ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            <?php else: ?>
                <a href="<?= base_url('client/mis-documentos-sst') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver al inicio
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
