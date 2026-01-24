<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentación SST - <?= esc($cliente['nombre_cliente']) ?></title>
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
        .folder-tree {
            list-style: none;
            padding-left: 20px;
        }
        .folder-tree > li {
            margin: 5px 0;
        }
        .folder-icon {
            margin-right: 8px;
        }

        /* Cards de documentos */
        .doc-card {
            transition: all 0.2s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .doc-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .doc-card.sin-generar {
            border-left: 4px solid #dc3545;
        }
        .doc-card.borrador {
            border-left: 4px solid #0dcaf0;
        }
        .doc-card.en-revision {
            border-left: 4px solid #ffc107;
        }
        .doc-card.pendiente-firma {
            border-left: 4px solid #6f42c1;
        }
        .doc-card.aprobado {
            border-left: 4px solid #198754;
        }

        .doc-card .card-body {
            padding: 0.75rem;
        }
        .doc-card .doc-codigo {
            font-family: monospace;
            font-size: 0.75rem;
            color: #6c757d;
        }
        .doc-card .doc-nombre {
            font-size: 0.85rem;
            font-weight: 500;
            line-height: 1.2;
            margin-bottom: 0.25rem;
        }
        .doc-card .doc-tipo {
            font-size: 0.7rem;
            color: #6c757d;
        }

        /* Secciones de estado */
        .estado-section {
            margin-bottom: 1.5rem;
        }
        .estado-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }
        .estado-badge {
            font-size: 0.9rem;
            padding: 0.35rem 0.75rem;
        }
        .estado-count {
            font-size: 0.8rem;
            color: #6c757d;
            margin-left: 0.5rem;
        }

        /* Grid de cards */
        .docs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 0.75rem;
        }

        /* Panel izquierdo - Carpetas */
        .carpetas-panel {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }

        /* Panel derecho - Documentos */
        .documentos-panel {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }

        /* Scrollbar personalizado */
        .carpetas-panel::-webkit-scrollbar,
        .documentos-panel::-webkit-scrollbar {
            width: 6px;
        }
        .carpetas-panel::-webkit-scrollbar-track,
        .documentos-panel::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .carpetas-panel::-webkit-scrollbar-thumb,
        .documentos-panel::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/documentacion/seleccionar-cliente">
                <i class="bi bi-folder-fill me-2"></i>Documentación SST
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

        <!-- Tarjetas de estadísticas -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card card-stat border-0 shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-0 small">Estándares</h6>
                                <h4 class="mb-0"><?= $cumplimiento['cumple'] ?? 0 ?>/<?= $cumplimiento['total'] ?? 0 ?></h4>
                                <small class="text-success"><?= $cumplimiento['porcentaje_cumplimiento'] ?? 0 ?>% cumplimiento</small>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                <i class="bi bi-check-circle text-primary fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat border-0 shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-0 small">Documentos</h6>
                                <h4 class="mb-0"><?= $estadisticas['total'] ?? 0 ?></h4>
                                <small class="text-muted"><?= $estadisticas['aprobado'] ?? 0 ?> aprobados</small>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                <i class="bi bi-file-earmark-text text-success fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat border-0 shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-0 small">En Revisión</h6>
                                <h4 class="mb-0"><?= $estadisticas['en_revision'] ?? 0 ?></h4>
                                <small class="text-warning">Pendientes de aprobación</small>
                            </div>
                            <div class="bg-warning bg-opacity-10 rounded-circle p-2">
                                <i class="bi bi-hourglass-split text-warning fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat border-0 shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-0 small">Borradores</h6>
                                <h4 class="mb-0"><?= $estadisticas['borrador'] ?? 0 ?></h4>
                                <small class="text-info">En elaboración</small>
                            </div>
                            <div class="bg-info bg-opacity-10 rounded-circle p-2">
                                <i class="bi bi-pencil text-info fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Panel Izquierdo: Estructura PHVA -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0"><i class="bi bi-folder-fill me-2 text-warning"></i>Estructura PHVA</h6>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalGenerarEstructura">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div class="card-body carpetas-panel">
                        <?php if (empty($carpetas)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-folder-x text-muted" style="font-size: 2.5rem;"></i>
                                <p class="text-muted mt-2 mb-2">No hay estructura de carpetas</p>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalGenerarEstructura">
                                    <i class="bi bi-plus-lg me-1"></i>Generar Estructura <?= date('Y') ?>
                                </button>
                            </div>
                        <?php else: ?>
                            <?php echo renderCarpetas($carpetas); ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Accesos rápidos -->
                <div class="row mt-3 g-2">
                    <div class="col-6">
                        <a href="/estandares/<?= $cliente['id_cliente'] ?>" class="card border-0 shadow-sm text-decoration-none h-100">
                            <div class="card-body text-center py-3">
                                <i class="bi bi-list-check text-primary fs-4"></i>
                                <h6 class="mt-1 mb-0 small text-dark">Ver Estándares</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="/estandares/catalogo" class="card border-0 shadow-sm text-decoration-none h-100">
                            <div class="card-body text-center py-3">
                                <i class="bi bi-book text-success fs-4"></i>
                                <h6 class="mt-1 mb-0 small text-dark">Catálogo 60</h6>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Panel Derecho: Documentos por Estado -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0">
                            <i class="bi bi-file-earmark-text me-2"></i>Documentos del Cliente
                            <span class="badge bg-primary ms-2"><?= $documentosPorEstado['contadores']['nivel_cliente'] ?? 60 ?> estándares</span>
                        </h6>
                        <span class="badge bg-secondary">
                            <?= $documentosPorEstado['contadores']['total_plantillas'] ?? 0 ?> documentos aplicables
                        </span>
                    </div>
                    <div class="card-body documentos-panel">

                        <!-- SIN GENERAR -->
                        <?php if (!empty($documentosPorEstado['sin_generar'])): ?>
                        <div class="estado-section">
                            <div class="estado-header">
                                <span class="badge bg-danger estado-badge">
                                    <i class="bi bi-exclamation-circle me-1"></i>Sin Generar
                                </span>
                                <span class="estado-count">(<?= count($documentosPorEstado['sin_generar']) ?> documentos pendientes)</span>
                            </div>
                            <div class="docs-grid">
                                <?php foreach ($documentosPorEstado['sin_generar'] as $item): ?>
                                <div class="card doc-card sin-generar"
                                     onclick="crearDocumento(<?= $item['plantilla']['id_plantilla'] ?>, '<?= esc($item['nombre']) ?>')"
                                     title="Clic para crear con IA">
                                    <div class="card-body">
                                        <div class="doc-codigo"><?= esc($item['codigo_sugerido']) ?></div>
                                        <div class="doc-nombre"><?= esc($item['nombre']) ?></div>
                                        <div class="doc-tipo"><?= esc($item['tipo_nombre']) ?></div>
                                        <div class="mt-2 d-flex justify-content-between align-items-center flex-wrap gap-1">
                                            <?php if (!empty($item['codigo_carpeta'])): ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size: 0.65rem;" title="Carpeta destino">
                                                <i class="bi bi-folder me-1"></i><?= esc($item['codigo_carpeta']) ?>
                                            </span>
                                            <?php endif; ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size: 0.65rem;">
                                                <i class="bi bi-magic me-1"></i>Crear con IA
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- BORRADORES -->
                        <?php if (!empty($documentosPorEstado['borrador'])): ?>
                        <div class="estado-section">
                            <div class="estado-header">
                                <span class="badge bg-info estado-badge">
                                    <i class="bi bi-pencil me-1"></i>Borradores
                                </span>
                                <span class="estado-count">(<?= count($documentosPorEstado['borrador']) ?> en elaboración)</span>
                            </div>
                            <div class="docs-grid">
                                <?php foreach ($documentosPorEstado['borrador'] as $doc): ?>
                                <div class="card doc-card borrador"
                                     onclick="window.location.href='/documentacion/editar/<?= $doc['id_documento'] ?>'"
                                     title="Clic para editar">
                                    <div class="card-body">
                                        <div class="doc-codigo"><?= esc($doc['codigo']) ?></div>
                                        <div class="doc-nombre"><?= esc($doc['nombre']) ?></div>
                                        <div class="doc-tipo">v<?= esc($doc['version_actual']) ?></div>
                                        <div class="mt-2">
                                            <span class="badge bg-info bg-opacity-10 text-info" style="font-size: 0.65rem;">
                                                <i class="bi bi-pencil me-1"></i>Editar
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- EN REVISIÓN -->
                        <?php if (!empty($documentosPorEstado['en_revision'])): ?>
                        <div class="estado-section">
                            <div class="estado-header">
                                <span class="badge bg-warning text-dark estado-badge">
                                    <i class="bi bi-hourglass-split me-1"></i>En Revisión
                                </span>
                                <span class="estado-count">(<?= count($documentosPorEstado['en_revision']) ?> pendientes)</span>
                            </div>
                            <div class="docs-grid">
                                <?php foreach ($documentosPorEstado['en_revision'] as $doc): ?>
                                <div class="card doc-card en-revision"
                                     onclick="window.location.href='/documentacion/ver/<?= $doc['id_documento'] ?>'"
                                     title="Clic para ver">
                                    <div class="card-body">
                                        <div class="doc-codigo"><?= esc($doc['codigo']) ?></div>
                                        <div class="doc-nombre"><?= esc($doc['nombre']) ?></div>
                                        <div class="doc-tipo">v<?= esc($doc['version_actual']) ?></div>
                                        <div class="mt-2">
                                            <span class="badge bg-warning bg-opacity-25 text-dark" style="font-size: 0.65rem;">
                                                <i class="bi bi-eye me-1"></i>Revisar
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- PENDIENTE FIRMA -->
                        <?php if (!empty($documentosPorEstado['pendiente_firma'])): ?>
                        <div class="estado-section">
                            <div class="estado-header">
                                <span class="badge bg-purple estado-badge" style="background-color: #6f42c1;">
                                    <i class="bi bi-pen me-1"></i>Pendiente Firma
                                </span>
                                <span class="estado-count">(<?= count($documentosPorEstado['pendiente_firma']) ?> esperando firma)</span>
                            </div>
                            <div class="docs-grid">
                                <?php foreach ($documentosPorEstado['pendiente_firma'] as $doc): ?>
                                <div class="card doc-card pendiente-firma"
                                     onclick="window.location.href='/firma/estado/<?= $doc['id_documento'] ?>'"
                                     title="Clic para ver estado de firma">
                                    <div class="card-body">
                                        <div class="doc-codigo"><?= esc($doc['codigo']) ?></div>
                                        <div class="doc-nombre"><?= esc($doc['nombre']) ?></div>
                                        <div class="doc-tipo">v<?= esc($doc['version_actual']) ?></div>
                                        <div class="mt-2">
                                            <span class="badge text-white" style="font-size: 0.65rem; background-color: #6f42c1;">
                                                <i class="bi bi-pen me-1"></i>Ver firma
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- APROBADOS -->
                        <?php if (!empty($documentosPorEstado['aprobado'])): ?>
                        <div class="estado-section">
                            <div class="estado-header">
                                <span class="badge bg-success estado-badge">
                                    <i class="bi bi-check-circle me-1"></i>Aprobados
                                </span>
                                <span class="estado-count">(<?= count($documentosPorEstado['aprobado']) ?> documentos)</span>
                            </div>
                            <div class="docs-grid">
                                <?php foreach ($documentosPorEstado['aprobado'] as $doc): ?>
                                <div class="card doc-card aprobado"
                                     onclick="window.location.href='/documentacion/ver/<?= $doc['id_documento'] ?>'"
                                     title="Clic para ver y descargar">
                                    <div class="card-body">
                                        <div class="doc-codigo"><?= esc($doc['codigo']) ?></div>
                                        <div class="doc-nombre"><?= esc($doc['nombre']) ?></div>
                                        <div class="doc-tipo">v<?= esc($doc['version_actual']) ?></div>
                                        <div class="mt-2">
                                            <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 0.65rem;">
                                                <i class="bi bi-download me-1"></i>Descargar
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Si no hay ningún documento -->
                        <?php if (empty($documentosPorEstado['sin_generar']) &&
                                  empty($documentosPorEstado['borrador']) &&
                                  empty($documentosPorEstado['en_revision']) &&
                                  empty($documentosPorEstado['pendiente_firma']) &&
                                  empty($documentosPorEstado['aprobado'])): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-file-earmark-x text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No hay plantillas de documentos configuradas</p>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
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
                        <p>Se creará la estructura de carpetas PHVA según la Resolución 0312/2019.</p>
                        <div class="alert alert-info py-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Este cliente aplica <strong><?= $documentosPorEstado['contadores']['nivel_cliente'] ?? 60 ?> estándares</strong>
                            (<?= ($documentosPorEstado['contadores']['nivel_cliente'] ?? 60) == 7 ? 'Microempresa' : (($documentosPorEstado['contadores']['nivel_cliente'] ?? 60) == 21 ? 'Pequeña empresa' : 'Empresa >50 trabajadores') ?>)
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Año</label>
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

        // Crear documento con IA
        function crearDocumento(idPlantilla, nombreDoc) {
            Swal.fire({
                title: '¿Crear documento con IA?',
                html: `
                    <p>Se creará el documento:</p>
                    <p class="fw-bold">${nombreDoc}</p>
                    <p class="text-muted small">El sistema usará el contexto del cliente para generar el contenido con Inteligencia Artificial.</p>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-magic me-1"></i>Crear con IA',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirigir al generador con la plantilla seleccionada
                    window.location.href = `<?= base_url('documentacion/nuevo') ?>/${idCliente}?plantilla=${idPlantilla}`;
                }
            });
        }

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
// Función helper para renderizar carpetas recursivamente
function renderCarpetas($carpetas, $nivel = 0) {
    $html = '<ul class="folder-tree' . ($nivel === 0 ? ' ps-0' : '') . '">';

    foreach ($carpetas as $carpeta) {
        $iconColor = match($carpeta['tipo']) {
            'phva' => match($carpeta['codigo'] ?? '') {
                '1' => 'text-primary',
                '2' => 'text-success',
                '3' => 'text-warning',
                '4' => 'text-danger',
                default => 'text-warning'
            },
            'categoria' => 'text-info',
            'estandar' => 'text-secondary',
            default => 'text-warning'
        };

        $icon = match($carpeta['tipo']) {
            'phva' => 'bi-folder-fill',
            'categoria' => 'bi-folder',
            'estandar' => 'bi-file-earmark',
            default => 'bi-folder'
        };

        $html .= '<li>';
        $html .= '<a href="/documentacion/carpeta/' . $carpeta['id_carpeta'] . '" class="text-decoration-none text-dark">';
        $html .= '<i class="bi ' . $icon . ' folder-icon ' . $iconColor . '"></i>';
        $html .= esc($carpeta['nombre']);
        $html .= '</a>';

        if (!empty($carpeta['hijos'])) {
            $html .= renderCarpetas($carpeta['hijos'], $nivel + 1);
        }

        $html .= '</li>';
    }

    $html .= '</ul>';
    return $html;
}
?>
