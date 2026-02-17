<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentacion SST - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .card-stat {
            transition: transform 0.2s;
        }
        .card-stat:hover {
            transform: translateY(-3px);
        }

        /* Estados IA */
        .estado-ia-pendiente { background-color: #6c757d !important; color: white !important; }
        .estado-ia-creado { background-color: #ffc107 !important; color: #212529 !important; }
        .estado-ia-aprobado { background-color: #198754 !important; color: white !important; }

        /* Estructura de carpetas tipo arbol */
        .folder-tree {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }
        .folder-tree .folder-tree {
            padding-left: 24px;
            margin-left: 12px;
            border-left: 1px dashed #dee2e6;
        }
        .folder-tree > li {
            margin: 2px 0;
        }

        /* Carpeta colapsable */
        .folder-header {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.15s;
        }
        .folder-header:hover {
            background-color: #f8f9fa;
        }
        .folder-header.phva-planear { border-left: 4px solid #0d6efd; }
        .folder-header.phva-hacer { border-left: 4px solid #198754; }
        .folder-header.phva-verificar { border-left: 4px solid #ffc107; }
        .folder-header.phva-actuar { border-left: 4px solid #dc3545; }

        .folder-chevron {
            transition: transform 0.2s;
            font-size: 0.75rem;
            width: 16px;
            color: #6c757d;
        }
        .folder-chevron.rotated {
            transform: rotate(90deg);
        }

        .folder-icon {
            font-size: 1.1rem;
            margin-right: 8px;
        }

        .folder-name {
            flex-grow: 1;
            font-weight: 500;
            color: #333;
            text-decoration: none;
        }
        .folder-name:hover {
            color: #0d6efd;
        }

        .folder-stats {
            display: flex;
            gap: 4px;
            margin-left: 8px;
        }
        .folder-stats .badge {
            font-size: 0.65rem;
            padding: 2px 6px;
            min-width: 20px;
        }

        .folder-content {
            display: none;
        }
        .folder-content.show {
            display: block;
        }

        /* Documentos dentro de carpetas */
        .docs-container {
            margin-left: 40px;
            padding: 8px 0;
        }

        .doc-card {
            display: flex;
            align-items: center;
            padding: 10px 14px;
            margin: 4px 0;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            text-decoration: none;
            color: #333;
            transition: all 0.15s;
        }
        .doc-card:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            transform: translateX(4px);
            color: #333;
        }

        .doc-estado-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 12px;
            flex-shrink: 0;
        }
        .doc-estado-indicator.pendiente { background-color: #6c757d; }
        .doc-estado-indicator.creado { background-color: #ffc107; }
        .doc-estado-indicator.aprobado { background-color: #198754; }

        .doc-info {
            flex-grow: 1;
            min-width: 0;
        }
        .doc-nombre {
            font-weight: 500;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .doc-meta {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .doc-estado-badge {
            margin-left: 12px;
        }

        .doc-actions {
            display: flex;
            gap: 4px;
            margin-left: 12px;
            opacity: 0;
            transition: opacity 0.15s;
        }
        .doc-card:hover .doc-actions {
            opacity: 1;
        }

        /* Panel principal */
        .main-panel {
            max-height: calc(100vh - 220px);
            overflow-y: auto;
        }
        .main-panel::-webkit-scrollbar {
            width: 8px;
        }
        .main-panel::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        .main-panel::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        /* Botones de accion */
        .btn-expand-all {
            font-size: 0.8rem;
            padding: 4px 12px;
        }

        /* Leyenda */
        .leyenda-estados {
            display: flex;
            gap: 16px;
            align-items: center;
            padding: 8px 16px;
            background: #f8f9fa;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .leyenda-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/documentacion/seleccionar-cliente">
                <i class="bi bi-folder-fill me-2"></i>Documentacion SST
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-3">
        <!-- Alertas -->
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

        <!-- Panel de Roles Obligatorios del SG-SST -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-people-fill text-primary me-2 fs-5"></i>
                            <h6 class="mb-0">Roles Obligatorios del SG-SST</h6>
                            <span class="badge bg-<?= ($estandaresAplicables ?? 60) <= 7 ? 'info' : (($estandaresAplicables ?? 60) <= 21 ? 'warning' : 'danger') ?> ms-2">
                                <?= $estandaresAplicables ?? 60 ?> Estándares
                            </span>
                            <?php if ($verificacionRoles['completo'] ?? false): ?>
                                <span class="badge bg-success ms-2"><i class="bi bi-check-circle me-1"></i>Completo</span>
                            <?php endif; ?>
                        </div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-<?= ($verificacionRoles['completo'] ?? false) ? 'success' : 'warning' ?>"
                                 style="width: <?= $verificacionRoles['porcentaje'] ?? 0 ?>%"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php if (!empty($verificacionRoles['completos'])): ?>
                                    <small class="text-success d-block mb-1"><i class="bi bi-check-circle-fill me-1"></i>Roles asignados:</small>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($verificacionRoles['completos'] as $rol): ?>
                                            <span class="badge bg-success bg-opacity-75" style="font-size: 0.7rem;"><?= esc($rol['nombre']) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Sin roles asignados aún</small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <?php if (!empty($verificacionRoles['faltantes'])): ?>
                                    <small class="text-danger d-block mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i>Roles pendientes:</small>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($verificacionRoles['faltantes'] as $rol): ?>
                                            <span class="badge bg-danger bg-opacity-75" style="font-size: 0.7rem;"><?= esc($rol['nombre']) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="ms-3">
                        <a href="<?= base_url('responsables-sst/' . $cliente['id_cliente']) ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="bi bi-gear me-1"></i>Gestionar Roles
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel principal con estructura de carpetas -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-folder-fill text-warning me-2"></i>
                                    Estructura de Documentos SG-SST
                                </h5>
                                <span class="badge bg-primary"><?= $documentosPorEstado['contadores']['nivel_cliente'] ?? 60 ?> estandares</span>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary btn-sm btn-expand-all" onclick="expandAll()">
                                    <i class="bi bi-arrows-expand me-1"></i>Expandir todo
                                </button>
                                <button class="btn btn-outline-secondary btn-sm btn-expand-all" onclick="collapseAll()">
                                    <i class="bi bi-arrows-collapse me-1"></i>Colapsar todo
                                </button>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalGenerarEstructura">
                                    <i class="bi bi-folder-plus me-1"></i>Generar Estructura
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body main-panel">
                        <?php if (empty($carpetasConDocs)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-folder-x text-muted" style="font-size: 4rem;"></i>
                                <h5 class="text-muted mt-3">No hay estructura de carpetas</h5>
                                <p class="text-muted">Genera la estructura de carpetas PHVA segun la Resolucion 0312/2019</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGenerarEstructura">
                                    <i class="bi bi-folder-plus me-1"></i>Generar Estructura <?= date('Y') ?>
                                </button>
                            </div>
                        <?php else: ?>
                            <ul class="folder-tree">
                                <?php echo renderCarpetasJerarquicas($carpetasConDocs, $cliente['id_cliente'], 0); ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accesos rapidos -->
        <div class="row mt-3 g-2">
            <div class="col-6 col-md">
                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>" class="card border-0 shadow-sm text-decoration-none h-100" target="_blank">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-graph-up-arrow text-danger fs-4"></i>
                        <h6 class="mt-2 mb-0 text-dark">Indicadores KPI</h6>
                        <small class="text-muted">Metricas SG-SST</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md">
                <a href="<?= base_url('pta/' . $cliente['id_cliente']) ?>" class="card border-0 shadow-sm text-decoration-none h-100" target="_blank">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-calendar-check text-info fs-4"></i>
                        <h6 class="mt-2 mb-0 text-dark">Plan de Trabajo</h6>
                        <small class="text-muted">Actividades PTA</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md">
                <a href="<?= base_url('estandares/' . $cliente['id_cliente']) ?>" class="card border-0 shadow-sm text-decoration-none h-100" target="_blank">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-list-check text-primary fs-4"></i>
                        <h6 class="mt-2 mb-0 text-dark">Ver Estandares</h6>
                        <small class="text-muted">Res. 0312/2019</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md">
                <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" class="card border-0 shadow-sm text-decoration-none h-100" target="_blank">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-robot text-purple fs-4" style="color: #6f42c1 !important;"></i>
                        <h6 class="mt-2 mb-0 text-dark">Generador IA</h6>
                        <small class="text-muted">Cronograma y PTA</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md">
                <a href="<?= base_url('documentacion/instructivo') ?>" class="card border-0 shadow-sm text-decoration-none h-100" target="_blank">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-question-circle text-warning fs-4"></i>
                        <h6 class="mt-2 mb-0 text-dark">Instructivo</h6>
                        <small class="text-muted">Guia de uso</small>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Generar Estructura -->
    <div class="modal fade" id="modalGenerarEstructura" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generar Estructura de Carpetas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formGenerarEstructura">
                    <div class="modal-body">
                        <p>Se creara la estructura de carpetas PHVA segun la Resolucion 0312/2019.</p>
                        <div class="alert alert-info py-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Este cliente aplica <strong><?= $documentosPorEstado['contadores']['nivel_cliente'] ?? 60 ?> estandares</strong>
                            (<?= ($documentosPorEstado['contadores']['nivel_cliente'] ?? 60) == 7 ? 'Microempresa' : (($documentosPorEstado['contadores']['nivel_cliente'] ?? 60) == 21 ? 'Pequena empresa' : 'Empresa >50 trabajadores') ?>)
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ano</label>
                            <select name="anio" class="form-select">
                                <option value="<?= date('Y') ?>"><?= date('Y') ?></option>
                                <option value="<?= date('Y') + 1 ?>"><?= date('Y') + 1 ?></option>
                            </select>
                        </div>
                        <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-folder-plus me-1"></i>Generar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const idCliente = <?= $cliente['id_cliente'] ?>;

        // Toggle de carpetas
        function toggleFolder(folderId) {
            const content = document.getElementById('content-' + folderId);
            const chevron = document.getElementById('chevron-' + folderId);

            if (content) {
                content.classList.toggle('show');
            }
            if (chevron) {
                chevron.classList.toggle('rotated');
            }
        }

        // Expandir todas las carpetas
        function expandAll() {
            document.querySelectorAll('.folder-content').forEach(el => el.classList.add('show'));
            document.querySelectorAll('.folder-chevron').forEach(el => el.classList.add('rotated'));
        }

        // Colapsar todas las carpetas
        function collapseAll() {
            document.querySelectorAll('.folder-content').forEach(el => el.classList.remove('show'));
            document.querySelectorAll('.folder-chevron').forEach(el => el.classList.remove('rotated'));
        }

        // Expandir primer nivel al cargar
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.folder-tree > li > .folder-content').forEach(el => {
                el.classList.add('show');
                const folderId = el.id.replace('content-', '');
                const chevron = document.getElementById('chevron-' + folderId);
                if (chevron) chevron.classList.add('rotated');
            });
        });

        // Generar estructura de carpetas
        document.getElementById('formGenerarEstructura').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('<?= base_url('documentacion/generar-estructura') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Estructura creada',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al generar estructura'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al procesar la solicitud'
                });
            });
        });
    </script>
</body>
</html>

<?php
/**
 * Renderiza la estructura jerarquica de carpetas con documentos
 */
function renderCarpetasJerarquicas($carpetas, $idCliente, $nivel = 0) {
    $html = '';

    foreach ($carpetas as $carpeta) {
        $stats = $carpeta['stats'] ?? ['total' => 0, 'pendiente' => 0, 'creado' => 0, 'aprobado' => 0];
        $tieneContenido = !empty($carpeta['hijos']) || !empty($carpeta['documentos']);
        $folderId = $carpeta['id_carpeta'];

        // Determinar clase de color segun tipo PHVA
        $phvaClass = '';
        if (($carpeta['tipo'] ?? '') === 'phva') {
            $codigo = $carpeta['codigo'] ?? '';
            $phvaClass = match($codigo) {
                '1' => 'phva-planear',
                '2' => 'phva-hacer',
                '3' => 'phva-verificar',
                '4' => 'phva-actuar',
                default => ''
            };
        }

        // Icono segun tipo
        $iconClass = match($carpeta['tipo'] ?? 'custom') {
            'phva' => 'bi-folder-fill text-warning',
            'categoria' => 'bi-folder text-info',
            'estandar' => 'bi-folder text-secondary',
            default => 'bi-folder text-warning'
        };

        $html .= '<li>';

        // Header de carpeta
        $html .= '<div class="folder-header ' . $phvaClass . '" onclick="toggleFolder(' . $folderId . ')">';

        // Chevron
        if ($tieneContenido) {
            $html .= '<i class="bi bi-chevron-right folder-chevron" id="chevron-' . $folderId . '"></i>';
        } else {
            $html .= '<span class="folder-chevron"></span>';
        }

        // Icono de carpeta
        $html .= '<i class="bi ' . $iconClass . ' folder-icon"></i>';

        // Nombre (clickeable para ir a la carpeta)
        $html .= '<a href="' . base_url('documentacion/carpeta/' . $folderId) . '" class="folder-name" onclick="event.stopPropagation();" target="_blank">';
        $html .= esc($carpeta['nombre']);
        $html .= '</a>';

        // Stats badges
        if ($stats['total'] > 0) {
            $html .= '<div class="folder-stats">';
            if ($stats['aprobado'] > 0) {
                $html .= '<span class="badge estado-ia-aprobado" title="Aprobados">' . $stats['aprobado'] . '</span>';
            }
            if ($stats['creado'] > 0) {
                $html .= '<span class="badge estado-ia-creado" title="Creados">' . $stats['creado'] . '</span>';
            }
            if ($stats['pendiente'] > 0) {
                $html .= '<span class="badge estado-ia-pendiente" title="Pendientes">' . $stats['pendiente'] . '</span>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        // Contenido (documentos y subcarpetas)
        if ($tieneContenido) {
            $html .= '<div class="folder-content" id="content-' . $folderId . '">';

            // Documentos de esta carpeta
            if (!empty($carpeta['documentos'])) {
                $html .= '<div class="docs-container">';
                foreach ($carpeta['documentos'] as $doc) {
                    $estadoIA = $doc['estado_ia'] ?? 'pendiente';
                    $estadoIAText = match($estadoIA) {
                        'aprobado' => 'Aprobado',
                        'creado' => 'Creado',
                        default => 'Pendiente'
                    };
                    $estadoIAClass = match($estadoIA) {
                        'aprobado' => 'estado-ia-aprobado',
                        'creado' => 'estado-ia-creado',
                        default => 'estado-ia-pendiente'
                    };

                    $html .= '<a href="' . base_url('documentacion/ver/' . $doc['id_documento']) . '" class="doc-card" target="_blank">';
                    $html .= '<span class="doc-estado-indicator ' . $estadoIA . '" title="' . $estadoIAText . '"></span>';
                    $html .= '<div class="doc-info">';
                    $html .= '<div class="doc-nombre">' . esc($doc['nombre']) . '</div>';
                    $html .= '<div class="doc-meta">';
                    $html .= '<code>' . esc($doc['codigo']) . '</code>';
                    $html .= ' &bull; v' . esc($doc['version_actual']);
                    $html .= ' &bull; ' . date('d/m/Y', strtotime($doc['updated_at']));
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '<span class="badge ' . $estadoIAClass . ' doc-estado-badge">' . $estadoIAText . '</span>';
                    $html .= '<div class="doc-actions">';
                    $html .= '<a href="' . base_url('documentacion/editar/' . $doc['id_documento']) . '" class="btn btn-sm btn-outline-secondary" title="Editar" onclick="event.stopPropagation();" target="_blank"><i class="bi bi-pencil"></i></a>';
                    $html .= '<a href="' . base_url('exportar/pdf/' . $doc['id_documento']) . '" class="btn btn-sm btn-outline-danger" title="PDF" onclick="event.stopPropagation();" target="_blank"><i class="bi bi-file-pdf"></i></a>';
                    $html .= '</div>';
                    $html .= '</a>';
                }
                $html .= '</div>';
            }

            // Subcarpetas recursivas
            if (!empty($carpeta['hijos'])) {
                $html .= '<ul class="folder-tree">';
                $html .= renderCarpetasJerarquicas($carpeta['hijos'], $idCliente, $nivel + 1);
                $html .= '</ul>';
            }

            $html .= '</div>';
        }

        $html .= '</li>';
    }

    return $html;
}
?>
