<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Versión <?= esc($version['version']) ?> - <?= esc($documento['codigo'] ?? '') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
        .documento-container {
            background: white;
            max-width: 816px;
            margin: 0 auto;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .encabezado-iso {
            border: 1px solid #000;
            font-size: 0.85rem;
        }
        .encabezado-iso td, .encabezado-iso th {
            border: 1px solid #000;
            padding: 0.5rem;
        }
        .seccion-contenido {
            line-height: 1.7;
            text-align: justify;
        }
        .version-badge {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 100;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark no-print" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-file-earmark-text me-2"></i>Versión <?= esc($version['version']) ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/control-documental/historial/<?= $documento['id_documento'] ?>">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Historial
                </a>
            </div>
        </div>
    </nav>

    <!-- Badge de versión -->
    <div class="version-badge no-print">
        <span class="badge <?= $version['estado'] === 'vigente' ? 'bg-success' : 'bg-secondary' ?> fs-6 p-2">
            <i class="bi bi-<?= $version['estado'] === 'vigente' ? 'check-circle' : 'archive' ?> me-1"></i>
            v<?= esc($version['version']) ?> - <?= strtoupper($version['estado']) ?>
        </span>
    </div>

    <div class="container py-4">
        <!-- Información de la versión -->
        <div class="card border-0 shadow-sm mb-4 no-print">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-1"><?= esc($documento['nombre']) ?></h5>
                        <p class="text-muted mb-0">
                            <code><?= esc($documento['codigo']) ?></code> |
                            Versión <?= esc($version['version']) ?> |
                            <?= $version['tipo_cambio'] === 'mayor' ? 'Cambio Mayor' : 'Cambio Menor' ?> |
                            <?= date('d/m/Y H:i', strtotime($version['fecha'] ?? $version['created_at'])) ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-printer me-1"></i>Imprimir
                        </button>
                        <?php if ($version['estado'] !== 'vigente' && !empty($snapshot)): ?>
                            <a href="/control-documental/restaurar/<?= $version['id_version'] ?>"
                               class="btn btn-warning btn-sm"
                               onclick="return confirm('¿Restaurar esta versión?')">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documento -->
        <div class="documento-container p-4">
            <!-- Encabezado ISO -->
            <table class="encabezado-iso w-100 mb-4 text-center">
                <tr>
                    <td rowspan="3" style="width: 25%;">
                        <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong><br>
                        <small>NIT: <?= esc($cliente['nit'] ?? '') ?></small>
                    </td>
                    <td colspan="2"><strong>SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO</strong></td>
                </tr>
                <tr>
                    <td colspan="2"><?= esc(strtoupper($documento['nombre'] ?? '')) ?></td>
                </tr>
                <tr>
                    <td>Código: <?= esc($documento['codigo'] ?? '') ?></td>
                    <td>Versión: <?= esc($version['version']) ?></td>
                </tr>
            </table>

            <!-- Contenido -->
            <?php if (empty($snapshot)): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    No hay snapshot de contenido disponible para esta versión.
                </div>
            <?php else: ?>
                <?php foreach ($snapshot as $seccion): ?>
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2">
                            <?= $seccion['numero_seccion'] ?>. <?= esc($seccion['nombre_seccion']) ?>
                        </h5>
                        <div class="seccion-contenido">
                            <?php if (empty($seccion['contenido'])): ?>
                                <p class="text-muted fst-italic">[Sin contenido]</p>
                            <?php else: ?>
                                <?= nl2br(esc($seccion['contenido'])) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Bloque de firmas -->
            <div class="mt-5 pt-4 border-top">
                <div class="row text-center">
                    <div class="col-4">
                        <div style="height: 60px;"></div>
                        <div class="border-top border-dark pt-2">
                            <strong>Elaboró</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="height: 60px;"></div>
                        <div class="border-top border-dark pt-2">
                            <strong>Revisó</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="height: 60px;"></div>
                        <div class="border-top border-dark pt-2">
                            <strong>Aprobó</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pie de página -->
            <div class="mt-4 pt-3 border-top text-muted small d-flex justify-content-between">
                <span><?= esc($documento['codigo']) ?> | Versión <?= esc($version['version']) ?></span>
                <span><?= esc($cliente['nombre_cliente'] ?? '') ?></span>
                <span>Fecha: <?= date('d/m/Y', strtotime($version['fecha'] ?? $version['created_at'])) ?></span>
            </div>
        </div>

        <!-- Información del cambio -->
        <div class="card border-0 shadow-sm mt-4 no-print">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información del Cambio</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted" style="width: 150px;">Tipo de cambio:</td>
                        <td>
                            <span class="badge <?= $version['tipo_cambio'] === 'mayor' ? 'bg-danger' : 'bg-warning text-dark' ?>">
                                <?= ucfirst($version['tipo_cambio']) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Descripción:</td>
                        <td><?= esc($version['descripcion_cambio']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Autorizado por:</td>
                        <td><?= esc($version['autorizado_por'] ?? 'Sistema') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Fecha:</td>
                        <td><?= date('d/m/Y H:i:s', strtotime($version['fecha'] ?? $version['created_at'])) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Estado:</td>
                        <td>
                            <span class="badge <?= $version['estado'] === 'vigente' ? 'bg-success' : 'bg-secondary' ?>">
                                <?= ucfirst($version['estado']) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
