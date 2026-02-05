<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($titulo) ?> - Enterprisesst</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1c2437;
            --secondary-dark: #2c3e50;
            --gold-primary: #bd9751;
            --gold-secondary: #d4af37;
            --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        body {
            background: var(--gradient-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark));
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .navbar-custom .navbar-brand {
            color: white;
            font-weight: 700;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark), var(--gold-primary));
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .card-stats {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .card-stats:hover {
            transform: translateY(-5px);
        }

        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 25px;
        }

        .btn-gold {
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary));
            border: none;
            color: white;
            font-weight: 600;
        }

        .btn-gold:hover {
            background: linear-gradient(135deg, var(--gold-secondary), var(--gold-primary));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(189, 151, 81, 0.4);
        }

        .btn-primary-dark {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark));
            border: none;
            color: white;
        }

        .btn-primary-dark:hover {
            background: linear-gradient(135deg, var(--secondary-dark), var(--primary-dark));
            color: white;
        }

        /* DataTables - Filas expandibles */
        table.dataTable tbody td.dt-control {
            cursor: pointer;
        }

        table.dataTable tbody td.dt-control:before {
            content: '\f0fe';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: var(--gold-primary);
            font-size: 1.2rem;
        }

        table.dataTable tbody tr.shown td.dt-control:before {
            content: '\f146';
        }

        .detail-row {
            background: #f8f9fa;
            padding: 20px;
        }

        .detail-row .detail-item {
            margin-bottom: 15px;
            padding: 10px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid var(--gold-primary);
        }

        .detail-row .detail-label {
            font-weight: 600;
            color: var(--primary-dark);
            font-size: 0.85rem;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .detail-row .detail-value {
            color: #333;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .badge-sector {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .badge-estado-activa {
            background: #28a745;
        }

        .badge-estado-derogada {
            background: #dc3545;
        }

        .badge-estado-modificada {
            background: #ffc107;
            color: #333;
        }

        /* Modal */
        .modal-header-custom {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark));
            color: white;
        }

        .form-label-custom {
            font-weight: 600;
            color: var(--primary-dark);
        }

        /* Filtros */
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        /* Filtros en thead */
        .filters-row {
            background: #f8f9fa !important;
        }

        .filters-row th {
            padding: 8px 5px !important;
            vertical-align: middle;
        }

        .filters-row .form-select,
        .filters-row .form-control {
            font-size: 0.8rem;
            border: 1px solid #dee2e6;
        }

        .filters-row .form-select:focus,
        .filters-row .form-control:focus {
            border-color: var(--gold-primary);
            box-shadow: 0 0 0 0.2rem rgba(189, 151, 81, 0.25);
        }

        .filters-row .form-control::placeholder {
            color: #adb5bd;
            font-style: italic;
        }

        /* Botón limpiar filtros */
        .btn-clear-filters {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('consultor/dashboard') ?>">
                <i class="fas fa-balance-scale me-2"></i><?= esc($titulo) ?>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url('matriz-legal/importar') ?>" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-file-csv me-1"></i>Importar CSV
                </a>
                <a href="<?= base_url('matriz-legal/buscar-ia') ?>" class="btn btn-gold btn-sm">
                    <i class="fas fa-robot me-1"></i>Buscar con IA
                </a>
                <a href="<?= base_url('matriz-legal/exportar') ?>" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-download me-1"></i>Exportar
                </a>
                <a href="<?= base_url('consultor/dashboard') ?>" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <!-- Header con estadísticas -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-2"><i class="fas fa-gavel me-2"></i>Matriz Legal SST</h2>
                    <p class="mb-0 opacity-75">Gestión de normativa aplicable en Seguridad y Salud en el Trabajo</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <h3 class="mb-0"><?= number_format($estadisticas['total'] ?? 0) ?></h3>
                            <small>Total Normas</small>
                        </div>
                        <div class="col-4">
                            <h3 class="mb-0"><?= $estadisticas['por_estado']['activa'] ?? 0 ?></h3>
                            <small>Activas</small>
                        </div>
                        <div class="col-4">
                            <h3 class="mb-0"><?= count($estadisticas['por_tipo'] ?? []) ?></h3>
                            <small>Tipos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="mb-4 d-flex gap-2 align-items-center">
            <button type="button" class="btn btn-gold btn-lg" data-bs-toggle="modal" data-bs-target="#modalNorma" onclick="limpiarFormulario()">
                <i class="fas fa-plus me-2"></i>Nueva Norma
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btnLimpiarFiltros" style="display: none;" onclick="limpiarFiltros()">
                <i class="fas fa-times me-1"></i>Limpiar Filtros
            </button>
            <span id="filtrosActivos" class="badge bg-info" style="display: none;"></span>
        </div>

        <!-- Tabla -->
        <div class="table-container">
            <table id="tablaMatrizLegal" class="table table-striped table-hover" style="width:100%">
                <thead>
                    <tr class="table-dark">
                        <th></th>
                        <th>Sector</th>
                        <th>Tipo</th>
                        <th>Norma</th>
                        <th>Año</th>
                        <th>Tema</th>
                        <th>Autoridad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                    <tr class="filters-row">
                        <th></th>
                        <th>
                            <select class="form-select form-select-sm filter-select" data-column="1">
                                <option value="">Todos</option>
                                <?php foreach ($sectores as $key => $value): ?>
                                    <option value="<?= $key ?>"><?= $key ?></option>
                                <?php endforeach; ?>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" data-column="2">
                                <option value="">Todos</option>
                                <?php foreach ($tiposNorma as $key => $value): ?>
                                    <option value="<?= $key ?>"><?= $key ?></option>
                                <?php endforeach; ?>
                            </select>
                        </th>
                        <th>
                            <input type="text" class="form-control form-control-sm filter-input" data-column="3" placeholder="Buscar...">
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" data-column="4">
                                <option value="">Todos</option>
                                <?php foreach ($anios as $anio): ?>
                                    <option value="<?= $anio['anio'] ?>"><?= $anio['anio'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </th>
                        <th>
                            <input type="text" class="form-control form-control-sm filter-input" data-column="5" placeholder="Buscar tema...">
                        </th>
                        <th>
                            <input type="text" class="form-control form-control-sm filter-input" data-column="6" placeholder="Buscar...">
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" data-column="7">
                                <option value="">Todos</option>
                                <?php foreach ($estados as $key => $value): ?>
                                    <option value="<?= $key ?>"><?= $value ?></option>
                                <?php endforeach; ?>
                            </select>
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Modal Crear/Editar Norma -->
    <div class="modal fade" id="modalNorma" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="modalNormaTitle">
                        <i class="fas fa-file-contract me-2"></i>Nueva Norma Legal
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNorma">
                        <input type="hidden" name="id" id="norma_id">

                        <div class="row">
                            <!-- Columna izquierda -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label form-label-custom">Sector *</label>
                                    <select name="sector" id="sector" class="form-select">
                                        <?php foreach ($sectores as $key => $value): ?>
                                            <option value="<?= $key ?>" <?= $key === 'General' ? 'selected' : '' ?>><?= $value ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label form-label-custom">Tema *</label>
                                    <input type="text" name="tema" id="tema" class="form-control" required placeholder="Ej: Sistema General de Riesgos Laborales">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label form-label-custom">Subtema</label>
                                    <input type="text" name="subtema" id="subtema" class="form-control" placeholder="Ej: Accidente de Trabajo">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label form-label-custom">Tipo de Norma *</label>
                                            <select name="tipo_norma" id="tipo_norma" class="form-select" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($tiposNorma as $key => $value): ?>
                                                    <option value="<?= $key ?>"><?= $value ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label form-label-custom">No. Norma *</label>
                                            <input type="text" name="id_norma_legal" id="id_norma_legal" class="form-control" required placeholder="0312">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label form-label-custom">Año *</label>
                                            <input type="number" name="anio" id="anio" class="form-control" required min="1900" max="2100" placeholder="2019">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label form-label-custom">Autoridad que Emite</label>
                                    <input type="text" name="autoridad_emisora" id="autoridad_emisora" class="form-control" placeholder="Ej: Ministerio del Trabajo">
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label form-label-custom">Estado</label>
                                            <select name="estado" id="estado" class="form-select">
                                                <?php foreach ($estados as $key => $value): ?>
                                                    <option value="<?= $key ?>"><?= $value ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3 pt-4">
                                            <div class="form-check">
                                                <input type="checkbox" name="referente_nacional" id="referente_nacional" class="form-check-input" value="1">
                                                <label class="form-check-label">Ref. Nacional</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3 pt-4">
                                            <div class="form-check">
                                                <input type="checkbox" name="referente_internacional" id="referente_internacional" class="form-check-input" value="1">
                                                <label class="form-check-label">Ref. Internacional</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna derecha -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label form-label-custom">Descripción de la Norma</label>
                                    <textarea name="descripcion_norma" id="descripcion_norma" class="form-control" rows="3" placeholder="Descripción general de la norma..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label form-label-custom">Artículos Aplicables</label>
                                    <textarea name="articulos_aplicables" id="articulos_aplicables" class="form-control" rows="2" placeholder="Artículos específicos que aplican..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label form-label-custom">Parámetros</label>
                                    <textarea name="parametros" id="parametros" class="form-control" rows="4" placeholder="Parámetros, requisitos y detalles de la norma..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label form-label-custom">Notas de Vigencia / Observaciones</label>
                                    <textarea name="notas_vigencia" id="notas_vigencia" class="form-control" rows="2" placeholder="Modificaciones, derogatorias, observaciones..."></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-gold" onclick="guardarNorma()">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ver Detalle -->
    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="modalDetalleTitle">
                        <i class="fas fa-eye me-2"></i>Detalle de Norma
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalDetalleBody">
                    <!-- Contenido dinámico -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let tabla;

        // Función para formatear detalle expandible
        function formatearDetalle(d) {
            let html = '<div class="detail-row"><div class="row">';

            if (d.descripcion_norma) {
                html += `<div class="col-md-6"><div class="detail-item">
                    <div class="detail-label"><i class="fas fa-file-alt me-1"></i>Descripción</div>
                    <div class="detail-value">${escapeHtml(d.descripcion_norma)}</div>
                </div></div>`;
            }

            if (d.articulos_aplicables) {
                html += `<div class="col-md-6"><div class="detail-item">
                    <div class="detail-label"><i class="fas fa-list-ol me-1"></i>Artículos Aplicables</div>
                    <div class="detail-value">${escapeHtml(d.articulos_aplicables)}</div>
                </div></div>`;
            }

            if (d.parametros) {
                html += `<div class="col-12"><div class="detail-item">
                    <div class="detail-label"><i class="fas fa-cogs me-1"></i>Parámetros</div>
                    <div class="detail-value">${escapeHtml(d.parametros)}</div>
                </div></div>`;
            }

            if (d.notas_vigencia) {
                html += `<div class="col-12"><div class="detail-item">
                    <div class="detail-label"><i class="fas fa-sticky-note me-1"></i>Notas de Vigencia</div>
                    <div class="detail-value">${escapeHtml(d.notas_vigencia)}</div>
                </div></div>`;
            }

            html += '</div></div>';
            return html;
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/&/g, "&amp;")
                       .replace(/</g, "&lt;")
                       .replace(/>/g, "&gt;")
                       .replace(/"/g, "&quot;")
                       .replace(/'/g, "&#039;")
                       .replace(/\n/g, "<br>");
        }

        $(document).ready(function() {
            // Inicializar DataTable
            tabla = $('#tablaMatrizLegal').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= base_url('matriz-legal/datatable') ?>',
                    type: 'GET'
                },
                columns: [
                    {
                        className: 'dt-control',
                        orderable: false,
                        data: null,
                        defaultContent: ''
                    },
                    { data: 'sector' },
                    { data: 'tipo_norma' },
                    {
                        data: null,
                        render: function(data) {
                            return `<strong>${data.tipo_norma} ${data.id_norma_legal}</strong>`;
                        }
                    },
                    { data: 'anio' },
                    {
                        data: 'tema',
                        render: function(data, type, row) {
                            let html = data;
                            if (row.subtema) {
                                html += `<br><small class="text-muted">${row.subtema}</small>`;
                            }
                            return html;
                        }
                    },
                    { data: 'autoridad_emisora' },
                    {
                        data: 'estado',
                        render: function(data) {
                            let badgeClass = 'badge-estado-' + data;
                            let label = data.charAt(0).toUpperCase() + data.slice(1);
                            return `<span class="badge ${badgeClass}">${label}</span>`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data) {
                            return `
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="editarNorma(${data.id})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="eliminarNorma(${data.id})" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[4, 'desc']],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                pageLength: 25
            });

            // Expandir/colapsar filas
            $('#tablaMatrizLegal tbody').on('click', 'td.dt-control', function() {
                let tr = $(this).closest('tr');
                let row = tabla.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    row.child(formatearDetalle(row.data())).show();
                    tr.addClass('shown');
                }
            });

            // Filtros en thead - Selectores
            $('.filter-select').on('change', function() {
                let column = $(this).data('column');
                let value = $(this).val();
                tabla.column(column).search(value).draw();
                actualizarIndicadorFiltros();
            });

            // Filtros en thead - Inputs de texto (con debounce)
            let filterTimeout;
            $('.filter-input').on('keyup', function() {
                let input = $(this);
                let column = input.data('column');
                let value = input.val();

                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(function() {
                    tabla.column(column).search(value).draw();
                    actualizarIndicadorFiltros();
                }, 400);
            });
        });

        // Limpiar todos los filtros
        function limpiarFiltros() {
            $('.filter-select').val('');
            $('.filter-input').val('');

            // Limpiar búsqueda en todas las columnas
            tabla.columns().search('').draw();

            actualizarIndicadorFiltros();
        }

        // Mostrar/ocultar indicador de filtros activos
        function actualizarIndicadorFiltros() {
            let filtrosActivos = 0;

            $('.filter-select').each(function() {
                if ($(this).val() !== '') filtrosActivos++;
            });

            $('.filter-input').each(function() {
                if ($(this).val() !== '') filtrosActivos++;
            });

            if (filtrosActivos > 0) {
                $('#btnLimpiarFiltros').show();
                $('#filtrosActivos').text(filtrosActivos + ' filtro(s) activo(s)').show();
            } else {
                $('#btnLimpiarFiltros').hide();
                $('#filtrosActivos').hide();
            }
        }

        function limpiarFormulario() {
            $('#formNorma')[0].reset();
            $('#norma_id').val('');
            $('#modalNormaTitle').html('<i class="fas fa-file-contract me-2"></i>Nueva Norma Legal');
            $('#sector').val('General');
        }

        function editarNorma(id) {
            $.get('<?= base_url('matriz-legal/ver') ?>/' + id, function(response) {
                if (response.success) {
                    let n = response.norma;
                    $('#norma_id').val(n.id);
                    $('#sector').val(n.sector);
                    $('#tema').val(n.tema);
                    $('#subtema').val(n.subtema);
                    $('#tipo_norma').val(n.tipo_norma);
                    $('#id_norma_legal').val(n.id_norma_legal);
                    $('#anio').val(n.anio);
                    $('#descripcion_norma').val(n.descripcion_norma);
                    $('#autoridad_emisora').val(n.autoridad_emisora);
                    $('#referente_nacional').prop('checked', n.referente_nacional === 'x');
                    $('#referente_internacional').prop('checked', n.referente_internacional === 'x');
                    $('#articulos_aplicables').val(n.articulos_aplicables);
                    $('#parametros').val(n.parametros);
                    $('#notas_vigencia').val(n.notas_vigencia);
                    $('#estado').val(n.estado);

                    $('#modalNormaTitle').html('<i class="fas fa-edit me-2"></i>Editar Norma Legal');
                    $('#modalNorma').modal('show');
                }
            });
        }

        function guardarNorma() {
            let formData = new FormData($('#formNorma')[0]);

            $.ajax({
                url: '<?= base_url('matriz-legal/guardar') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#modalNorma').modal('hide');
                        tabla.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Guardado',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        let errores = Object.values(response.errors || {}).join('<br>');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errores || response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al guardar la norma'
                    });
                }
            });
        }

        function eliminarNorma(id) {
            Swal.fire({
                title: '¿Eliminar norma?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('<?= base_url('matriz-legal/eliminar') ?>/' + id, function(response) {
                        if (response.success) {
                            tabla.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    });
                }
            });
        }
    </script>
</body>

</html>
