<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos SST - <?= esc($cliente['nombre_cliente']) ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        :root {
            --primary-color: #00b894;
            --secondary-color: #00cec9;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #3498db;
            --dark-color: #2c3e50;
            --light-bg: #f8f9fa;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 30px;
            margin-bottom: 30px;
        }

        .header-title {
            color: var(--dark-color);
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .client-info {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .metrics-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .metric-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-5px);
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .metric-label {
            font-size: 0.9rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .filters-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 25px;
            margin-bottom: 30px;
        }

        .filters-title {
            color: var(--dark-color);
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 30px;
        }

        .badge-tipo-a {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-tipo-b {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-electoral {
            background: linear-gradient(135deg, #ffd89b 0%, #ff9a56 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-generado {
            background: var(--success-color);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-no-generado {
            background: #95a5a6;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-no-aplica {
            background: linear-gradient(135deg, #636e72 0%, #b2bec3 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .row-no-aplica {
            opacity: 0.55;
        }

        .btn-toggle-exclusion {
            border: 1px solid #b2bec3;
            background: transparent;
            color: #636e72;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-toggle-exclusion:hover {
            background: #636e72;
            color: white;
        }

        .btn-toggle-exclusion.active {
            background: #e17055;
            border-color: #e17055;
            color: white;
        }

        .btn-generar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-generar:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,184,148,0.4);
            color: white;
        }

        .btn-ver {
            background: linear-gradient(135deg, var(--success-color) 0%, #2ecc71 100%);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-ver:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(39,174,96,0.4);
            color: white;
        }

        .btn-nueva-version {
            background: linear-gradient(135deg, var(--info-color) 0%, #5dade2 100%);
            color: white;
            border: none;
            padding: 6px 15px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .btn-nueva-version:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(52,152,219,0.4);
            color: white;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            padding: 8px 15px;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            padding: 5px 10px;
        }

        table.dataTable thead th {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            font-weight: 600;
            border: none;
        }

        table.dataTable tbody tr:hover {
            background-color: #e8f5f2 !important;
        }

        .select2-container--bootstrap-5 .select2-selection {
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            min-height: 45px;
        }

        .progress-bar-custom {
            height: 30px;
            background: linear-gradient(90deg, var(--success-color) 0%, var(--primary-color) 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .back-button {
            background: linear-gradient(135deg, #636e72 0%, #2d3436 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(45,52,54,0.4);
            color: white;
        }

        .numeral-badge {
            background: #34495e;
            color: white;
            padding: 4px 10px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .category-text {
            color: #7f8c8d;
            font-size: 0.9rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header con información del cliente -->
        <div class="header-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1 class="header-title">
                        <i class="fas fa-file-alt text-primary"></i> Documentos SST
                    </h1>
                    <p class="client-info">
                        <strong><?= esc($cliente['nombre_cliente']) ?></strong> - NIT: <?= esc($cliente['nit_cliente']) ?>
                    </p>
                </div>
                <a href="<?= base_url('consultant/dashboard') ?>" class="back-button">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                </a>
            </div>

            <!-- Métricas -->
            <div class="metrics-container">
                <div class="metric-card" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                    <div class="metric-value"><?= $generados ?></div>
                    <div class="metric-label">Documentos Generados</div>
                </div>

                <div class="metric-card" style="background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);">
                    <div class="metric-value"><?= $no_generados ?></div>
                    <div class="metric-label">Pendientes de Generar</div>
                </div>

                <div class="metric-card" style="background: linear-gradient(135deg, #636e72 0%, #b2bec3 100%);">
                    <div class="metric-value"><?= $no_aplica ?></div>
                    <div class="metric-label">No Aplica</div>
                </div>

                <div class="metric-card" style="background: linear-gradient(135deg, #3498db 0%, #5dade2 100%);">
                    <div class="metric-value"><?= $total_aplicable ?></div>
                    <div class="metric-label">Total Aplicables</div>
                </div>

                <div class="metric-card" style="background: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%);">
                    <div class="metric-value"><?= $porcentaje ?>%</div>
                    <div class="metric-label">Completitud</div>
                </div>
            </div>

            <!-- Barra de progreso -->
            <div class="mt-4">
                <div class="progress" style="height: 30px; border-radius: 15px; background: #ecf0f1;">
                    <div class="progress-bar-custom" style="width: <?= $porcentaje ?>%;">
                        <?= $generados ?> de <?= $total_aplicable ?> documentos generados
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-card">
            <h5 class="filters-title">
                <i class="fas fa-filter"></i> Filtros de Búsqueda
            </h5>

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-layer-group me-1"></i> Categoría
                    </label>
                    <select id="filtroCategoria" class="form-select">
                        <option value="">Todas las categorías</option>
                        <?php
                            $categorias = array_unique(array_column($documentos, 'categoria'));
                            sort($categorias);
                            foreach ($categorias as $cat):
                                if ($cat):
                        ?>
                            <option value="<?= esc($cat) ?>"><?= esc($cat) ?></option>
                        <?php
                                endif;
                            endforeach;
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-cogs me-1"></i> Tipo de Flujo
                    </label>
                    <select id="filtroTipo" class="form-select">
                        <option value="">Todos los tipos</option>
                        <option value="Tipo A">Tipo A (Contexto Cliente)</option>
                        <option value="Tipo B">Tipo B (PTA + Indicadores)</option>
                        <option value="Electoral">Electoral (Comités)</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-check-circle me-1"></i> Estado
                    </label>
                    <select id="filtroEstado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="Generado">Generado</option>
                        <option value="No Generado">No Generado</option>
                        <option value="No Aplica">No Aplica</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-hashtag me-1"></i> Numeral
                    </label>
                    <input type="text" id="filtroNumeral" class="form-control" placeholder="Ej: 2.1, 3.2...">
                </div>
            </div>

            <div class="mt-3">
                <button id="btnLimpiarFiltros" class="btn btn-outline-secondary">
                    <i class="fas fa-eraser me-2"></i>Limpiar Filtros
                </button>
            </div>
        </div>

        <!-- Tabla de documentos -->
        <div class="table-card">
            <h5 class="mb-4">
                <i class="fas fa-list me-2"></i>Listado de Documentos SST
            </h5>

            <div class="table-responsive">
                <table id="tablaDocumentos" class="table table-hover table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>Numeral</th>
                            <th>Categoría</th>
                            <th>Nombre del Documento</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Versión</th>
                            <th>Última Modificación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): ?>
                        <tr class="<?= $doc['estado_doc'] === 'no_aplica' ? 'row-no-aplica' : '' ?>">
                            <td>
                                <span class="numeral-badge"><?= esc($doc['numeral']) ?></span>
                            </td>
                            <td>
                                <span class="category-text"><?= esc($doc['categoria']) ?></span>
                            </td>
                            <td>
                                <strong><?= esc($doc['nombre']) ?></strong>
                                <?php if ($doc['estado_doc'] === 'no_aplica' && $doc['motivo_exclusion']): ?>
                                    <br><small class="text-muted"><?= esc($doc['motivo_exclusion']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($doc['flujo'] === 'Tipo A'): ?>
                                    <span class="badge-tipo-a">Tipo A</span>
                                <?php elseif ($doc['flujo'] === 'Tipo B'): ?>
                                    <span class="badge-tipo-b">Tipo B</span>
                                <?php else: ?>
                                    <span class="badge-electoral">Electoral</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($doc['estado_doc'] === 'no_aplica'): ?>
                                    <span class="badge-no-aplica">
                                        <i class="fas fa-ban me-1"></i>No Aplica
                                    </span>
                                <?php elseif ($doc['existe']): ?>
                                    <span class="badge-generado">
                                        <i class="fas fa-check-circle me-1"></i>Generado
                                    </span>
                                <?php else: ?>
                                    <span class="badge-no-generado">
                                        <i class="fas fa-times-circle me-1"></i>No Generado
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $doc['version'] ? 'v' . esc($doc['version']) : '<span class="text-muted">N/A</span>' ?>
                            </td>
                            <td>
                                <?php if ($doc['fecha_modificacion']): ?>
                                    <?= date('d M Y', strtotime($doc['fecha_modificacion'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    $(document).ready(function() {
        // Inicializar DataTable
        var table = $('#tablaDocumentos').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            order: [[0, 'asc']], // Ordenar por numeral
            pageLength: 25,
            columnDefs: [
                { orderable: false, targets: [7] } // Deshabilitar orden en columna de acciones
            ],
            initComplete: function() {
                console.log('DataTable inicializado con ' + this.api().data().length + ' registros');
            }
        });

        // Inicializar Select2 en filtros
        $('#filtroCategoria, #filtroTipo, #filtroEstado').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: function() {
                return $(this).find('option:first').text();
            },
            allowClear: true
        });

        // Filtro custom global con $.fn.dataTable.ext.search
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            // data[] contiene el texto visible de cada columna (HTML stripped)
            var filtroCategoria = $('#filtroCategoria').val() || '';
            var filtroTipo = $('#filtroTipo').val() || '';
            var filtroEstado = $('#filtroEstado').val() || '';
            var filtroNumeral = $('#filtroNumeral').val() || '';

            var categoria = data[1].trim();
            var tipo = data[3].trim();
            var estado = data[4].trim();
            var numeral = data[0].trim();

            // Categoría: match exacto
            if (filtroCategoria && categoria !== filtroCategoria) return false;

            // Tipo: match exacto
            if (filtroTipo && tipo !== filtroTipo) return false;

            // Estado: match exacto
            if (filtroEstado && estado !== filtroEstado) return false;

            // Numeral: match parcial (startsWith)
            if (filtroNumeral && numeral.indexOf(filtroNumeral) !== 0) return false;

            return true;
        });

        // Redibujar tabla al cambiar cualquier filtro
        $('#filtroCategoria, #filtroTipo, #filtroEstado').on('change', function() {
            table.draw();
        });

        $('#filtroNumeral').on('keyup', function() {
            table.draw();
        });

        // Limpiar todos los filtros
        $('#btnLimpiarFiltros').on('click', function() {
            $('#filtroCategoria, #filtroTipo, #filtroEstado').val(null).trigger('change.select2');
            $('#filtroNumeral').val('');
            table.draw();
        });

        // Búsqueda global de DataTable también funciona
        $('.dataTables_filter input').attr('placeholder', 'Buscar en toda la tabla...');

        // Toggle exclusión (No Aplica)
        $(document).on('click', '.btn-toggle-exclusion', function() {
            var btn = $(this);
            var tipo = btn.data('tipo');
            var cliente = btn.data('cliente');
            var esReactivar = btn.hasClass('active');

            var mensaje = esReactivar
                ? 'Reactivar este documento?'
                : 'Marcar como No Aplica?';

            if (esReactivar) {
                // Reactivar directo
                enviarToggle(tipo, cliente, '');
            } else {
                // Pedir motivo opcional
                var motivo = prompt('Motivo (opcional):') || '';
                enviarToggle(tipo, cliente, motivo);
            }
        });

        function enviarToggle(tipo, cliente, motivo) {
            $.post('<?= base_url('documentos-sst/toggle-exclusion') ?>', {
                tipo_documento: tipo,
                id_cliente: cliente,
                motivo: motivo,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            })
            .done(function(resp) {
                if (resp.success) {
                    location.reload();
                } else {
                    alert('Error: ' + resp.message);
                }
            })
            .fail(function() {
                alert('Error de conexión');
            });
        }
    });
    </script>
</body>
</html>
