<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentación SST - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card-stat {
            transition: transform 0.2s;
        }
        .card-stat:hover {
            transform: translateY(-5px);
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
        .phva-planear { border-left: 4px solid #3B82F6; }
        .phva-hacer { border-left: 4px solid #10B981; }
        .phva-verificar { border-left: 4px solid #F59E0B; }
        .phva-actuar { border-left: 4px solid #EF4444; }
        .progress-ring {
            width: 120px;
            height: 120px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/documentacion">
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

    <div class="container-fluid py-4">
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
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card card-stat border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Estándares</h6>
                                <h3 class="mb-0"><?= $cumplimiento['cumple'] ?? 0 ?>/<?= $cumplimiento['total'] ?? 0 ?></h3>
                                <small class="text-success"><?= $cumplimiento['porcentaje_cumplimiento'] ?? 0 ?>% cumplimiento</small>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-check-circle text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Documentos</h6>
                                <h3 class="mb-0"><?= $estadisticas['total'] ?? 0 ?></h3>
                                <small class="text-muted"><?= $estadisticas['aprobado'] ?? 0 ?> aprobados</small>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-file-earmark-text text-success fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">En Revisión</h6>
                                <h3 class="mb-0"><?= $estadisticas['en_revision'] ?? 0 ?></h3>
                                <small class="text-warning">Pendientes de aprobación</small>
                            </div>
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-hourglass-split text-warning fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Borradores</h6>
                                <h3 class="mb-0"><?= $estadisticas['borrador'] ?? 0 ?></h3>
                                <small class="text-info">En elaboración</small>
                            </div>
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-pencil text-info fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Árbol de carpetas -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-folder-fill me-2 text-warning"></i>Estructura PHVA</h5>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalGenerarEstructura">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        <?php if (empty($carpetas)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-folder-x text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">No hay estructura de carpetas</p>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalGenerarEstructura">
                                    <i class="bi bi-plus-lg me-1"></i>Generar Estructura <?= date('Y') ?>
                                </button>
                            </div>
                        <?php else: ?>
                            <?php echo renderCarpetas($carpetas); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Documentos recientes -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Documentos Recientes</h5>
                        <a href="/documentacion/nuevo/<?= $cliente['id_cliente'] ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i>Nuevo Documento
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($documentos)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-file-earmark-x text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">No hay documentos aún</p>
                                <a href="/documentacion/nuevo/<?= $cliente['id_cliente'] ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i>Crear primer documento
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Código</th>
                                            <th>Nombre</th>
                                            <th>Versión</th>
                                            <th>Estado</th>
                                            <th>Actualizado</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($documentos, 0, 10) as $doc): ?>
                                            <tr>
                                                <td><code><?= esc($doc['codigo']) ?></code></td>
                                                <td><?= esc($doc['nombre']) ?></td>
                                                <td><span class="badge bg-secondary">v<?= esc($doc['version_actual']) ?></span></td>
                                                <td>
                                                    <?php
                                                    $badgeClass = match($doc['estado']) {
                                                        'borrador' => 'bg-info',
                                                        'en_revision' => 'bg-warning',
                                                        'pendiente_firma' => 'bg-purple',
                                                        'aprobado' => 'bg-success',
                                                        'obsoleto' => 'bg-secondary',
                                                        default => 'bg-light text-dark'
                                                    };
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst(str_replace('_', ' ', $doc['estado'])) ?></span>
                                                </td>
                                                <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime($doc['updated_at'])) ?></small></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="/documentacion/ver/<?= $doc['id_documento'] ?>" class="btn btn-outline-primary" title="Ver">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="/documentacion/editar/<?= $doc['id_documento'] ?>" class="btn btn-outline-secondary" title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (count($documentos) > 10): ?>
                        <div class="card-footer bg-white text-center">
                            <a href="/documentacion/documentos/<?= $cliente['id_cliente'] ?>" class="text-primary">
                                Ver todos los documentos <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Accesos rápidos -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <a href="/estandares/<?= $cliente['id_cliente'] ?>" class="card border-0 shadow-sm text-decoration-none">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-list-check text-primary" style="font-size: 2rem;"></i>
                                <h6 class="mt-2 mb-0 text-dark">Ver Estándares</h6>
                                <small class="text-muted">Cumplimiento 0312/2019</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="/estandares/catalogo" class="card border-0 shadow-sm text-decoration-none">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-book text-success" style="font-size: 2rem;"></i>
                                <h6 class="mt-2 mb-0 text-dark">Catálogo 60 Estándares</h6>
                                <small class="text-muted">Resolución 0312/2019</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="/documentacion/proximos-revision/<?= $cliente['id_cliente'] ?>" class="card border-0 shadow-sm text-decoration-none">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-calendar-event text-warning" style="font-size: 2rem;"></i>
                                <h6 class="mt-2 mb-0 text-dark">Próximas Revisiones</h6>
                                <small class="text-muted">Documentos por vencer</small>
                            </div>
                        </a>
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
                        <p>Se creará la estructura de carpetas PHVA con los 60 estándares de la Resolución 0312/2019.</p>
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
    <script>
        document.getElementById('formGenerarEstructura').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('/documentacion/generar-estructura', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error al generar estructura');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
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
