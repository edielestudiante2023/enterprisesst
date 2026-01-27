<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa - <?= esc($documento['codigo'] ?? 'Documento') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            .documento-preview { box-shadow: none !important; }
        }

        .documento-preview {
            background: white;
            max-width: 816px; /* Tamaño carta */
            margin: 0 auto;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .documento-encabezado {
            border-bottom: 2px solid #3B82F6;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .documento-logo {
            max-height: 60px;
        }

        .documento-info {
            text-align: right;
            font-size: 0.85rem;
        }

        .documento-titulo {
            text-align: center;
            padding: 1rem;
            background: #F3F4F6;
            margin-bottom: 1rem;
        }

        .documento-contenido {
            padding: 1.5rem 2rem;
        }

        .seccion-titulo {
            color: #1F2937;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 0.5rem;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }

        .seccion-contenido {
            line-height: 1.7;
            text-align: justify;
        }

        .seccion-contenido table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }

        .seccion-contenido table th,
        .seccion-contenido table td {
            border: 1px solid #D1D5DB;
            padding: 0.5rem;
            font-size: 0.9rem;
        }

        .seccion-contenido table th {
            background: #F3F4F6;
        }

        .seccion-contenido ul, .seccion-contenido ol {
            padding-left: 1.5rem;
        }

        .documento-firmas {
            margin-top: 3rem;
            display: flex;
            justify-content: space-around;
            text-align: center;
        }

        .firma-bloque {
            width: 200px;
        }

        .firma-linea {
            border-top: 1px solid #000;
            margin-bottom: 0.5rem;
        }

        .firma-nombre {
            font-weight: bold;
            font-size: 0.9rem;
        }

        .firma-cargo {
            font-size: 0.8rem;
            color: #666;
        }

        .documento-pie {
            border-top: 1px solid #E5E7EB;
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            display: flex;
            justify-content: space-between;
            color: #6B7280;
        }

        .badge-estado {
            font-size: 0.75rem;
        }

        .sidebar-acciones {
            position: sticky;
            top: 1rem;
        }

        /* Convertir markdown a HTML básico */
        .seccion-contenido h1, .seccion-contenido h2, .seccion-contenido h3 {
            font-size: 1.1rem;
            font-weight: bold;
            margin-top: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary no-print">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-file-earmark-text me-2"></i>Vista Previa
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <code><?= esc($documento['codigo'] ?? '') ?></code>
                </span>
                <a class="nav-link" href="/documentacion/editar/<?= $documento['id_documento'] ?>">
                    <i class="bi bi-pencil me-1"></i>Editar
                </a>
                <a class="nav-link" href="/documentacion/<?= $cliente['id_cliente'] ?>">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row">
            <!-- Panel lateral de acciones -->
            <div class="col-md-3 no-print">
                <div class="sidebar-acciones">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="mb-3">
                                <i class="bi bi-info-circle me-2"></i>Información
                            </h6>
                            <table class="table table-sm">
                                <tr>
                                    <td class="text-muted">Código:</td>
                                    <td><code><?= esc($documento['codigo'] ?? 'Sin código') ?></code></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Versión:</td>
                                    <td><?= esc($documento['version_actual'] ?? '1.0') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Estado:</td>
                                    <td>
                                        <?php
                                        $estadoBadges = [
                                            'borrador' => 'bg-secondary',
                                            'en_revision' => 'bg-warning text-dark',
                                            'pendiente_firma' => 'bg-info',
                                            'aprobado' => 'bg-success',
                                            'obsoleto' => 'bg-danger'
                                        ];
                                        $badge = $estadoBadges[$documento['estado'] ?? 'borrador'] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?= $badge ?> badge-estado">
                                            <?= ucfirst(str_replace('_', ' ', $documento['estado'] ?? 'borrador')) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Secciones:</td>
                                    <td><?= count($secciones ?? []) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="mb-3">
                                <i class="bi bi-gear me-2"></i>Acciones
                            </h6>

                            <a href="<?= base_url('documentacion/editar/' . $documento['id_documento']) ?>"
                               class="btn btn-outline-primary w-100 mb-2">
                                <i class="bi bi-pencil me-1"></i>Seguir Editando
                            </a>

                            <button onclick="window.print()" class="btn btn-outline-secondary w-100 mb-2">
                                <i class="bi bi-printer me-1"></i>Imprimir
                            </button>

                            <?php if (($documento['estado'] ?? '') === 'borrador'): ?>
                                <!-- Verificar que todas las secciones tengan contenido -->
                                <?php
                                $seccionesCompletas = true;
                                foreach ($secciones as $sec) {
                                    if (empty($sec['contenido'])) {
                                        $seccionesCompletas = false;
                                        break;
                                    }
                                }
                                ?>

                                <?php if ($seccionesCompletas): ?>
                                    <hr>
                                    <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#modalAprobar">
                                        <i class="bi bi-check-lg me-1"></i>Enviar a Revisión
                                    </button>
                                <?php else: ?>
                                    <hr>
                                    <button class="btn btn-secondary w-100" disabled>
                                        <i class="bi bi-exclamation-circle me-1"></i>Secciones incompletas
                                    </button>
                                    <small class="text-muted d-block mt-2">Complete todas las secciones antes de aprobar</small>
                                <?php endif; ?>
                            <?php elseif (($documento['estado'] ?? '') === 'en_revision'): ?>
                                <hr>
                                <a href="/firma/solicitar/<?= $documento['id_documento'] ?>" class="btn btn-info w-100">
                                    <i class="bi bi-pen me-1"></i>Solicitar Firmas
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="mb-3">
                                <i class="bi bi-download me-2"></i>Exportar
                            </h6>
                            <a href="/exportar/pdf-borrador/<?= $documento['id_documento'] ?>"
                               class="btn btn-outline-danger w-100 mb-2">
                                <i class="bi bi-file-pdf me-1"></i>PDF Borrador
                            </a>
                            <a href="/exportar/word/<?= $documento['id_documento'] ?>"
                               class="btn btn-outline-primary w-100">
                                <i class="bi bi-file-word me-1"></i>Word (.docx)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documento -->
            <div class="col-md-9">
                <div class="documento-preview">
                    <!-- Encabezado -->
                    <div class="documento-encabezado">
                        <div>
                            <strong><?= esc($cliente['nombre_cliente'] ?? 'EMPRESA') ?></strong><br>
                            <small>NIT: <?= esc($cliente['nit'] ?? '') ?></small>
                        </div>
                        <div class="documento-info">
                            <strong>SISTEMA DE GESTIÓN DE SST</strong><br>
                            <span>Código: <?= esc($documento['codigo'] ?? 'XXX-000') ?></span><br>
                            <span>Versión: <?= esc($documento['version_actual'] ?? '1.0') ?></span>
                        </div>
                    </div>

                    <!-- Título -->
                    <div class="documento-titulo">
                        <h4 class="mb-0"><?= esc(strtoupper($documento['nombre'] ?? 'DOCUMENTO')) ?></h4>
                        <?php if (!empty($documento['tipo_nombre'])): ?>
                            <small class="text-muted"><?= esc($documento['tipo_nombre']) ?></small>
                        <?php endif; ?>
                    </div>

                    <!-- Contenido -->
                    <div class="documento-contenido">
                        <?php if (empty($secciones)): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Este documento no tiene secciones definidas.
                            </div>
                        <?php else: ?>
                            <?php
                            // Función para convertir Markdown básico a HTML
                            function markdownToHtml($text) {
                                $text = esc($text);
                                // Negritas: **texto** -> <strong>texto</strong>
                                $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
                                // Cursivas: *texto* -> <em>texto</em>
                                $text = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $text);
                                // Listas con guión al inicio de línea
                                $text = preg_replace('/^- (.+)$/m', '<li>$1</li>', $text);
                                // Envolver listas consecutivas en <ul>
                                $text = preg_replace('/(<li>.*<\/li>\n?)+/s', '<ul>$0</ul>', $text);
                                // Limpiar ul anidados
                                $text = str_replace("</ul>\n<ul>", "\n", $text);
                                // Saltos de línea
                                $text = nl2br($text);
                                return $text;
                            }
                            ?>
                            <?php foreach ($secciones as $seccion): ?>
                                <h5 class="seccion-titulo">
                                    <?= $seccion['numero_seccion'] ?>. <?= esc($seccion['nombre_seccion']) ?>
                                </h5>
                                <div class="seccion-contenido">
                                    <?php if (empty($seccion['contenido'])): ?>
                                        <p class="text-muted fst-italic">[Sección pendiente de contenido]</p>
                                    <?php else: ?>
                                        <?= markdownToHtml($seccion['contenido']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Bloque de firmas -->
                        <div class="documento-firmas">
                            <div class="firma-bloque">
                                <div style="height: 60px;"></div>
                                <div class="firma-linea"></div>
                                <div class="firma-nombre">_________________</div>
                                <div class="firma-cargo">Elaboró</div>
                            </div>
                            <div class="firma-bloque">
                                <div style="height: 60px;"></div>
                                <div class="firma-linea"></div>
                                <div class="firma-nombre">_________________</div>
                                <div class="firma-cargo">Revisó</div>
                            </div>
                            <div class="firma-bloque">
                                <div style="height: 60px;"></div>
                                <div class="firma-linea"></div>
                                <div class="firma-nombre">_________________</div>
                                <div class="firma-cargo">Aprobó</div>
                            </div>
                        </div>
                    </div>

                    <!-- Pie de página -->
                    <div class="documento-pie">
                        <span><?= esc($documento['codigo'] ?? '') ?> | v<?= esc($documento['version_actual'] ?? '1.0') ?></span>
                        <span><?= esc($cliente['nombre_cliente'] ?? '') ?></span>
                        <span>Estado: <?= ucfirst($documento['estado'] ?? 'borrador') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de aprobación -->
    <div class="modal fade" id="modalAprobar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-check-circle me-2"></i>Enviar a Revisión
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Esta acción:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Cambiará el estado a "En Revisión"</li>
                            <li>Bloqueará la edición del documento</li>
                            <li>Preparará el documento para firmas</li>
                        </ul>
                    </div>

                    <p><strong>Documento:</strong> <?= esc($documento['codigo'] ?? '') ?> - <?= esc($documento['nombre'] ?? '') ?></p>
                    <p><strong>Versión a generar:</strong> <?= esc($documento['version_actual'] ?? '1.0') ?></p>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmarRevision">
                        <label class="form-check-label" for="confirmarRevision">
                            He revisado el documento y confirmo que está listo
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="/documentacion/finalizar/<?= $documento['id_documento'] ?>" method="post" style="display: inline;">
                        <button type="submit" class="btn btn-success" id="btnConfirmarAprobacion" disabled>
                            <i class="bi bi-check-lg me-1"></i>Confirmar y Enviar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Habilitar botón de confirmar solo cuando se marca el checkbox
        document.getElementById('confirmarRevision').addEventListener('change', function() {
            document.getElementById('btnConfirmarAprobacion').disabled = !this.checked;
        });
    </script>
</body>
</html>
