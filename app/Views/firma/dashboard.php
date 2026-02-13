<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Firmas Electronicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; }
        .navbar-firma {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
        }
        .stat-card {
            border-radius: 12px;
            border: none;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
        }
        .stat-card .stat-label {
            font-size: 0.85rem;
            opacity: 0.9;
        }
        .badge-estado {
            font-size: 0.8rem;
            padding: 0.4em 0.8em;
            border-radius: 20px;
        }
        .table-firma th {
            background: #1e3a5f;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            white-space: nowrap;
        }
        .table-firma td {
            vertical-align: middle;
            font-size: 0.9rem;
        }
        .progress-firmantes {
            height: 8px;
            border-radius: 4px;
        }
        .btn-ver-estado {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.3rem 0.8rem;
            font-size: 0.8rem;
            transition: opacity 0.2s;
        }
        .btn-ver-estado:hover {
            color: white;
            opacity: 0.85;
        }
        .filter-btn {
            border-radius: 20px;
            padding: 0.3rem 1rem;
            font-size: 0.85rem;
            border: 1px solid #dee2e6;
            background: white;
            transition: all 0.2s;
        }
        .filter-btn.active {
            background: #1e3a5f;
            color: white;
            border-color: #1e3a5f;
        }
        .filter-btn:hover:not(.active) {
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-firma navbar-dark py-3">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-pen me-2"></i>Gestion de Firmas Electronicas
            </span>
            <a href="<?= base_url('/dashboard') ?>" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Volver al Dashboard
            </a>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">
        <!-- Tarjetas resumen -->
        <div class="row mb-4 g-3">
            <div class="col-6 col-md-3">
                <div class="card stat-card shadow-sm text-center p-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div class="stat-number"><?= $totales['total'] ?></div>
                    <div class="stat-label">Total Documentos</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card shadow-sm text-center p-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                    <div class="stat-number"><?= $totales['pendientes'] ?></div>
                    <div class="stat-label">Con Pendientes</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card shadow-sm text-center p-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                    <div class="stat-number"><?= $totales['firmados'] ?></div>
                    <div class="stat-label">Completados</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card shadow-sm text-center p-3" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">
                    <div class="stat-number"><?= $totales['expirados'] ?></div>
                    <div class="stat-label">Con Expirados</div>
                </div>
            </div>
        </div>

        <!-- Filtros rapidos -->
        <div class="mb-3 d-flex flex-wrap gap-2">
            <button class="filter-btn active" data-filter="todos">Todos</button>
            <button class="filter-btn" data-filter="pendiente">Con Pendientes</button>
            <button class="filter-btn" data-filter="completado">Completados</button>
            <button class="filter-btn" data-filter="expirado">Con Expirados</button>
        </div>

        <!-- Tabla principal -->
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tablaFirmas" class="table table-firma table-hover mb-0" style="width: 100%;">
                        <thead>
                            <tr>
                                <th class="ps-3">Cliente</th>
                                <th>Documento</th>
                                <th>Tipo</th>
                                <th>Version</th>
                                <th>Firmantes</th>
                                <th>Progreso</th>
                                <th>Estado</th>
                                <th>Ultimo Evento</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documentos as $doc):
                                // Determinar estado resumen
                                if ($doc['expirados'] > 0) {
                                    $estadoLabel = 'Con expirados';
                                    $estadoClass = 'bg-danger';
                                    $estadoFiltro = 'expirado';
                                } elseif ((int)$doc['firmados'] === (int)$doc['total_firmantes']) {
                                    $estadoLabel = 'Completado';
                                    $estadoClass = 'bg-success';
                                    $estadoFiltro = 'completado';
                                } elseif ((int)$doc['firmados'] > 0) {
                                    $estadoLabel = 'En progreso';
                                    $estadoClass = 'bg-warning text-dark';
                                    $estadoFiltro = 'pendiente';
                                } elseif ((int)$doc['pendientes'] > 0 || (int)$doc['esperando'] > 0) {
                                    $estadoLabel = 'Pendiente';
                                    $estadoClass = 'bg-info';
                                    $estadoFiltro = 'pendiente';
                                } elseif ((int)$doc['cancelados'] === (int)$doc['total_firmantes']) {
                                    $estadoLabel = 'Cancelado';
                                    $estadoClass = 'bg-secondary';
                                    $estadoFiltro = 'completado';
                                } else {
                                    $estadoLabel = 'Sin firmas';
                                    $estadoClass = 'bg-light text-dark';
                                    $estadoFiltro = 'todos';
                                }

                                $porcentaje = (int)$doc['total_firmantes'] > 0
                                    ? round(((int)$doc['firmados'] / (int)$doc['total_firmantes']) * 100)
                                    : 0;

                                $ultimoEvento = $doc['ultima_firma'] ?? $doc['fecha_solicitud'] ?? '';
                                if ($ultimoEvento) {
                                    $ultimoEvento = date('d/m/Y H:i', strtotime($ultimoEvento));
                                }
                            ?>
                            <tr data-estado="<?= $estadoFiltro ?>">
                                <td class="ps-3">
                                    <div class="fw-bold"><?= esc($doc['nombre_cliente']) ?></div>
                                    <small class="text-muted">NIT: <?= esc($doc['nit_cliente']) ?></small>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= esc($doc['codigo']) ?></div>
                                    <small class="text-muted"><?= esc(mb_strimwidth($doc['titulo'], 0, 50, '...')) ?></small>
                                </td>
                                <td><small><?= esc(str_replace('_', ' ', ucfirst($doc['tipo_documento']))) ?></small></td>
                                <td class="text-center"><?= esc($doc['version']) ?></td>
                                <td class="text-center">
                                    <span class="fw-bold"><?= $doc['firmados'] ?>/<?= $doc['total_firmantes'] ?></span>
                                </td>
                                <td style="min-width: 100px;">
                                    <div class="progress progress-firmantes">
                                        <div class="progress-bar <?= $porcentaje == 100 ? 'bg-success' : 'bg-primary' ?>"
                                             style="width: <?= $porcentaje ?>%"></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-estado <?= $estadoClass ?>"><?= $estadoLabel ?></span>
                                </td>
                                <td><small><?= $ultimoEvento ?></small></td>
                                <td class="text-center">
                                    <a href="<?= base_url('firma/estado/' . $doc['id_documento']) ?>"
                                       class="btn btn-ver-estado" target="_blank"
                                       title="Ver estado de firmas">
                                        <i class="bi bi-eye me-1"></i>Ver
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
    $(document).ready(function() {
        var tabla = $('#tablaFirmas').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            order: [[7, 'desc']],
            pageLength: 25,
            responsive: true,
            dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip'
        });

        // Filtros rapidos por estado
        $('.filter-btn').on('click', function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');

            var filtro = $(this).data('filter');

            if (filtro === 'todos') {
                // Mostrar todas las filas
                tabla.rows().nodes().to$().show();
                tabla.search('').draw();
            } else {
                // Filtrar por data-estado
                tabla.rows().nodes().to$().each(function() {
                    var estado = $(this).data('estado');
                    if (estado === filtro) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });
    });
    </script>
</body>
</html>
