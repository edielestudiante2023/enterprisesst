<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        .navbar { background: rgba(255, 255, 255, 0.95); border-bottom: 3px solid #bd9751; }
        .content-wrapper { margin-top: 20px; padding-bottom: 50px; }
        .page-header { background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%); color: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; }
        .carpeta-card { transition: all 0.3s ease; border: none; border-radius: 12px; }
        .carpeta-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .carpeta-icon { font-size: 2.5rem; color: #f0ad4e; }
        .badge-docs { position: absolute; top: 10px; right: 10px; }
        .ciclo-phva { font-weight: 600; color: #1c2437; border-bottom: 2px solid #bd9751; padding-bottom: 0.5rem; margin-bottom: 1rem; }
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
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container content-wrapper">
        <!-- Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="bi bi-folder-check me-2"></i><?= esc($titulo) ?></h4>
                    <p class="mb-0 opacity-75">Documentos del Sistema de Gestion de Seguridad y Salud en el Trabajo</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-success fs-6">
                        <i class="bi bi-file-earmark-check me-1"></i><?= $totalDocumentos ?> documento(s)
                    </span>
                </div>
            </div>
        </div>

        <!-- Arbol de carpetas por ciclo PHVA -->
        <?php foreach ($arbolCarpetas as $ciclo): ?>
        <div class="mb-4">
            <h5 class="ciclo-phva">
                <i class="bi bi-folder2-open me-2"></i><?= esc($ciclo['nombre']) ?>
                <?php if ($ciclo['total_docs'] > 0): ?>
                    <span class="badge bg-primary ms-2"><?= $ciclo['total_docs'] ?></span>
                <?php endif; ?>
            </h5>

            <div class="row g-3">
                <?php if (!empty($ciclo['hijos'])): ?>
                    <?php foreach ($ciclo['hijos'] as $carpeta): ?>
                    <div class="col-md-4 col-lg-3">
                        <a href="<?= base_url('client/mis-documentos-sst/carpeta/' . $carpeta['id_carpeta']) ?>" class="text-decoration-none" target="_blank">
                            <div class="card carpeta-card h-100 position-relative">
                                <?php if ($carpeta['total_docs'] > 0): ?>
                                    <span class="badge bg-success badge-docs"><?= $carpeta['total_docs'] ?></span>
                                <?php endif; ?>
                                <div class="card-body text-center">
                                    <i class="bi bi-folder-fill carpeta-icon"></i>
                                    <h6 class="mt-2 mb-1 text-dark"><?= esc($carpeta['codigo'] ?? '') ?></h6>
                                    <small class="text-muted"><?= esc($carpeta['nombre']) ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <p class="text-muted"><i class="bi bi-info-circle me-1"></i>No hay subcarpetas en esta seccion.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($arbolCarpetas)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>No hay estructura de carpetas configurada para su empresa.
            Puede ver todos sus documentos en la tabla de abajo.
        </div>
        <?php endif; ?>

        <!-- Lista completa de documentos del cliente (solo descarga) -->
        <?php if (!empty($todosDocumentos)): ?>
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-text me-2"></i>Todos mis Documentos
                </h5>
                <span class="badge bg-primary"><?= count($todosDocumentos) ?> documento(s)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tablaDocumentos" class="table table-hover mb-0" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Codigo</th>
                                <th>Documento</th>
                                <th class="text-center">Ano</th>
                                <th class="text-center">Descargar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todosDocumentos as $doc): ?>
                            <tr>
                                <td>
                                    <code class="text-primary"><?= esc($doc['codigo'] ?? 'N/A') ?></code>
                                </td>
                                <td>
                                    <strong><?= esc($doc['titulo']) ?></strong>
                                    <br><small class="text-muted"><?= esc(ucwords(str_replace('_', ' ', $doc['tipo_documento'] ?? ''))) ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?= esc($doc['anio']) ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($doc['archivo_firmado'])): ?>
                                        <a href="<?= esc($doc['archivo_firmado']) ?>"
                                           class="btn btn-sm btn-danger text-white"
                                           title="Descargar PDF" target="_blank">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= base_url('documentos-sst/exportar-pdf/' . $doc['id_documento']) ?>"
                                           class="btn btn-sm btn-danger text-white"
                                           title="Descargar PDF" target="_blank">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php elseif (empty($arbolCarpetas)): ?>
        <div class="text-center py-5">
            <i class="bi bi-folder-x text-muted" style="font-size: 4rem;"></i>
            <p class="text-muted mt-3">No hay documentos disponibles.</p>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#tablaDocumentos').DataTable({
            dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="bi bi-file-earmark-excel me-1"></i>Exportar Excel',
                    className: 'btn btn-success btn-sm',
                    title: '<?= esc($cliente['nombre_cliente'] ?? '') ?> - Documentos SST',
                    exportOptions: {
                        columns: [0, 1, 2]
                    }
                }
            ],
            language: {
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ documentos",
                infoEmpty: "Sin documentos",
                infoFiltered: "(filtrado de _MAX_ total)",
                zeroRecords: "No se encontraron documentos",
                paginate: { first: "Primero", last: "Ultimo", next: "Siguiente", previous: "Anterior" }
            },
            pageLength: 25,
            order: [[2, 'desc'], [0, 'asc']],
            columnDefs: [
                { targets: 3, orderable: false, searchable: false }
            ]
        });
    });
    </script>
</body>
</html>
