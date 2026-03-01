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

        .badge-tipo-interna {
            background: #17a2b8;
        }

        .badge-tipo-externa {
            background: #6f42c1;
        }

        .badge-tipo-ambas {
            background: #fd7e14;
        }

        .badge-estado-activo {
            background: #28a745;
        }

        .badge-estado-inactivo {
            background: #dc3545;
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
                <i class="fas fa-project-diagram me-2"></i><?= esc($titulo) ?>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url('matriz-comunicacion/importar') ?>" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-file-csv me-1"></i>Importar CSV
                </a>
                <a href="<?= base_url('matriz-comunicacion/generar-ia') ?>" class="btn btn-gold btn-sm">
                    <i class="fas fa-robot me-1"></i>Generar con IA
                </a>
                <a href="<?= base_url('matriz-comunicacion/exportar') ?>" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-download me-1"></i>Exportar
                </a>
                <a href="<?= base_url('consultor/dashboard') ?>" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <!-- Header con estadisticas -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-2"><i class="fas fa-comments me-2"></i>Matriz de Comunicacion SST</h2>
                    <p class="mb-0 opacity-75">Protocolos de comunicacion interna y externa del SG-SST</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <h3 class="mb-0"><?= number_format($estadisticas['total'] ?? 0) ?></h3>
                            <small>Total Protocolos</small>
                        </div>
                        <div class="col-4">
                            <h3 class="mb-0"><?= $estadisticas['por_estado']['activo'] ?? 0 ?></h3>
                            <small>Activos</small>
                        </div>
                        <div class="col-4">
                            <h3 class="mb-0"><?= count($estadisticas['por_categoria'] ?? []) ?></h3>
                            <small>Categorias</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de accion -->
        <div class="mb-4 d-flex gap-2 align-items-center">
            <button type="button" class="btn btn-gold btn-lg" data-bs-toggle="modal" data-bs-target="#modalProtocolo" onclick="limpiarFormulario()">
                <i class="fas fa-plus me-2"></i>Nuevo Protocolo
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btnLimpiarFiltros" style="display: none;" onclick="limpiarFiltros()">
                <i class="fas fa-times me-1"></i>Limpiar Filtros
            </button>
            <span id="filtrosActivos" class="badge bg-info" style="display: none;"></span>
        </div>

        <!-- Tabla -->
        <div class="table-container">
            <table id="tablaMatrizComunicacion" class="table table-striped table-hover" style="width:100%">
                <thead>
                    <tr class="table-dark">
                        <th></th>
                        <th>Categoria</th>
                        <th>Situacion/Evento</th>
                        <th>Quien Comunica</th>
                        <th>A Quien</th>
                        <th>Tipo</th>
                        <th>Plazo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                    <tr class="filters-row">
                        <th></th>
                        <th>
                            <select class="form-select form-select-sm filter-select" data-column="1">
                                <option value="">Todas</option>
                                <?php foreach ($categorias as $key => $value): ?>
                                    <option value="<?= $key ?>"><?= $key ?></option>
                                <?php endforeach; ?>
                            </select>
                        </th>
                        <th>
                            <input type="text" class="form-control form-control-sm filter-input" data-column="2" placeholder="Buscar...">
                        </th>
                        <th>
                            <input type="text" class="form-control form-control-sm filter-input" data-column="3" placeholder="Buscar...">
                        </th>
                        <th>
                            <input type="text" class="form-control form-control-sm filter-input" data-column="4" placeholder="Buscar...">
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" data-column="5">
                                <option value="">Todos</option>
                                <?php foreach ($tipos as $key => $value): ?>
                                    <option value="<?= $key ?>"><?= $value ?></option>
                                <?php endforeach; ?>
                            </select>
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

    <!-- Modal Crear/Editar Protocolo -->
    <div class="modal fade" id="modalProtocolo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="modalProtocoloTitle">
                        <i class="fas fa-comments me-2"></i>Nuevo Protocolo de Comunicacion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formProtocolo">
                        <input type="hidden" name="id" id="protocolo_id">

                        <div class="row">
                            <!-- Columna izquierda -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label form-label-custom">Categoria *</label>
                                    <select name="categoria" id="categoria" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($categorias as $key => $value): ?>
                                            <option value="<?= $key ?>"><?= $value ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label form-label-custom">Situacion / Evento *</label>
                                    <input type="text" name="situacion_evento" id="situacion_evento" class="form-control" required placeholder="Ej: Presunto caso de acoso sexual">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label form-label-custom">Que Comunicar *</label>
                                    <textarea name="que_comunicar" id="que_comunicar" class="form-control" rows="3" required placeholder="Que informacion se debe transmitir..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label form-label-custom">Quien Comunica *</label>
                                    <input type="text" name="quien_comunica" id="quien_comunica" class="form-control" required placeholder="Ej: Trabajador afectado / Jefe inmediato">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label form-label-custom">A Quien Comunicar *</label>
                                    <input type="text" name="a_quien_comunicar" id="a_quien_comunicar" class="form-control" required placeholder="Ej: Comite de Convivencia / Alta Direccion">
                                </div>
                            </div>

                            <!-- Columna derecha -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label form-label-custom">Mecanismo / Canal *</label>
                                    <input type="text" name="mecanismo_canal" id="mecanismo_canal" class="form-control" required placeholder="Ej: Escrito confidencial / Correo / Telefono">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label form-label-custom">Frecuencia / Plazo *</label>
                                    <input type="text" name="frecuencia_plazo" id="frecuencia_plazo" class="form-control" required placeholder="Ej: Inmediato / 24 horas / Mensual">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label form-label-custom">Registro / Evidencia</label>
                                    <input type="text" name="registro_evidencia" id="registro_evidencia" class="form-control" placeholder="Ej: FURAT, Acta, Formato de queja">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label form-label-custom">Norma Aplicable</label>
                                    <input type="text" name="norma_aplicable" id="norma_aplicable" class="form-control" placeholder="Ej: Decreto 1072/2015, Ley 1010/2006">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label form-label-custom">Tipo</label>
                                            <select name="tipo" id="tipo" class="form-select">
                                                <?php foreach ($tipos as $key => $value): ?>
                                                    <option value="<?= $key ?>"><?= $value ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label form-label-custom">Estado</label>
                                            <select name="estado" id="estado" class="form-select">
                                                <?php foreach ($estados as $key => $value): ?>
                                                    <option value="<?= $key ?>"><?= $value ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-gold" onclick="guardarProtocolo()">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
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

        function formatearDetalle(d) {
            let html = '<div class="detail-row"><div class="row">';

            if (d.que_comunicar) {
                html += `<div class="col-md-6"><div class="detail-item">
                    <div class="detail-label"><i class="fas fa-bullhorn me-1"></i>Que Comunicar</div>
                    <div class="detail-value">${escapeHtml(d.que_comunicar)}</div>
                </div></div>`;
            }

            if (d.mecanismo_canal) {
                html += `<div class="col-md-6"><div class="detail-item">
                    <div class="detail-label"><i class="fas fa-satellite-dish me-1"></i>Mecanismo / Canal</div>
                    <div class="detail-value">${escapeHtml(d.mecanismo_canal)}</div>
                </div></div>`;
            }

            if (d.registro_evidencia) {
                html += `<div class="col-md-6"><div class="detail-item">
                    <div class="detail-label"><i class="fas fa-clipboard-check me-1"></i>Registro / Evidencia</div>
                    <div class="detail-value">${escapeHtml(d.registro_evidencia)}</div>
                </div></div>`;
            }

            if (d.norma_aplicable) {
                html += `<div class="col-md-6"><div class="detail-item">
                    <div class="detail-label"><i class="fas fa-gavel me-1"></i>Norma Aplicable</div>
                    <div class="detail-value">${escapeHtml(d.norma_aplicable)}</div>
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
            tabla = $('#tablaMatrizComunicacion').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= base_url('matriz-comunicacion/datatable') ?>',
                    type: 'GET'
                },
                columns: [
                    {
                        className: 'dt-control',
                        orderable: false,
                        data: null,
                        defaultContent: ''
                    },
                    { data: 'categoria' },
                    {
                        data: 'situacion_evento',
                        render: function(data) {
                            if (data && data.length > 80) {
                                return data.substring(0, 80) + '...';
                            }
                            return data;
                        }
                    },
                    { data: 'quien_comunica' },
                    { data: 'a_quien_comunicar' },
                    {
                        data: 'tipo',
                        render: function(data) {
                            let badgeClass = 'badge-tipo-' + data;
                            let label = data.charAt(0).toUpperCase() + data.slice(1);
                            return `<span class="badge ${badgeClass}">${label}</span>`;
                        }
                    },
                    { data: 'frecuencia_plazo' },
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
                                    <button class="btn btn-outline-primary" onclick="editarProtocolo(${data.id})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="eliminarProtocolo(${data.id})" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[1, 'asc']],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                pageLength: 25
            });

            // Expandir/colapsar filas
            $('#tablaMatrizComunicacion tbody').on('click', 'td.dt-control', function() {
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

            // Filtros - Selectores
            $('.filter-select').on('change', function() {
                let column = $(this).data('column');
                let value = $(this).val();
                tabla.column(column).search(value).draw();
                actualizarIndicadorFiltros();
            });

            // Filtros - Inputs de texto (con debounce)
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

        function limpiarFiltros() {
            $('.filter-select').val('');
            $('.filter-input').val('');
            tabla.columns().search('').draw();
            actualizarIndicadorFiltros();
        }

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
            $('#formProtocolo')[0].reset();
            $('#protocolo_id').val('');
            $('#modalProtocoloTitle').html('<i class="fas fa-comments me-2"></i>Nuevo Protocolo de Comunicacion');
        }

        function editarProtocolo(id) {
            $.get('<?= base_url('matriz-comunicacion/ver') ?>/' + id, function(response) {
                if (response.success) {
                    let p = response.protocolo;
                    $('#protocolo_id').val(p.id);
                    $('#categoria').val(p.categoria);
                    $('#situacion_evento').val(p.situacion_evento);
                    $('#que_comunicar').val(p.que_comunicar);
                    $('#quien_comunica').val(p.quien_comunica);
                    $('#a_quien_comunicar').val(p.a_quien_comunicar);
                    $('#mecanismo_canal').val(p.mecanismo_canal);
                    $('#frecuencia_plazo').val(p.frecuencia_plazo);
                    $('#registro_evidencia').val(p.registro_evidencia);
                    $('#norma_aplicable').val(p.norma_aplicable);
                    $('#tipo').val(p.tipo);
                    $('#estado').val(p.estado);

                    $('#modalProtocoloTitle').html('<i class="fas fa-edit me-2"></i>Editar Protocolo');
                    $('#modalProtocolo').modal('show');
                }
            });
        }

        function guardarProtocolo() {
            let formData = new FormData($('#formProtocolo')[0]);

            $.ajax({
                url: '<?= base_url('matriz-comunicacion/guardar') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#modalProtocolo').modal('hide');
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
                        text: 'Error al guardar el protocolo'
                    });
                }
            });
        }

        function eliminarProtocolo(id) {
            Swal.fire({
                title: 'Eliminar protocolo?',
                text: 'Esta accion no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('<?= base_url('matriz-comunicacion/eliminar') ?>/' + id, function(response) {
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
