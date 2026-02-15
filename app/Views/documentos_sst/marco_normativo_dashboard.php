<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marcos Normativos IA - Dashboard</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #5a6fd6;
            --secondary: #6a4190;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .table-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .badge-vigente {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .badge-vencido {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .btn-consultar-ia {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            color: white;
        }

        .btn-consultar-ia:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
            color: white;
        }

        .btn-ver-editar {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            border: none;
            color: white;
        }

        .btn-ver-editar:hover {
            background: linear-gradient(135deg, #f5576c, #f093fb);
            color: white;
        }

        .table-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .icon-large {
            font-size: 3rem;
            opacity: 0.2;
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-0">
                        <i class="bi bi-book-open me-2"></i>
                        Marcos Normativos IA
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">Gestión centralizada de marcos normativos para generación con IA</p>
                </div>
                <a href="<?= base_url('/consultor/dashboard') ?>" class="btn btn-light">
                    <i class="bi bi-arrow-left me-2"></i>Volver al Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="stat-number text-primary"><?= $total ?></p>
                            <p class="stat-label mb-0">Total Marcos Normativos</p>
                        </div>
                        <i class="bi bi-file-text icon-large text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="stat-number text-success">
                                <?= count(array_filter($marcos, fn($m) => $m['vigente'])) ?>
                            </p>
                            <p class="stat-label mb-0">Vigentes</p>
                        </div>
                        <i class="bi bi-check-circle icon-large text-success"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="stat-number text-danger">
                                <?= count(array_filter($marcos, fn($m) => !$m['vigente'])) ?>
                            </p>
                            <p class="stat-label mb-0">Vencidos</p>
                        </div>
                        <i class="bi bi-exclamation-triangle icon-large text-danger"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="stat-number text-info">
                                <i class="bi bi-robot"></i>
                            </p>
                            <p class="stat-label mb-0">Con GPT-4o + Web Search</p>
                        </div>
                        <i class="bi bi-stars icon-large text-info"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Marcos Normativos -->
        <div class="table-card">
            <h4 class="mb-4">
                <i class="bi bi-table me-2"></i>
                Listado de Marcos Normativos
            </h4>

            <?php if (empty($marcos)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    No hay marcos normativos registrados. Genera un documento SST con IA para crear el primer marco normativo.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="tablaMarcosNormativos" class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo de Documento</th>
                                <th>Estado</th>
                                <th>Última Actualización</th>
                                <th>Días</th>
                                <th>Método</th>
                                <th>Actualizado Por</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($marcos as $marco): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($marco['nombre']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= esc($marco['tipo']) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($marco['vigente']): ?>
                                            <span class="badge badge-vigente">
                                                <i class="bi bi-check-circle me-1"></i>Vigente
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-vencido">
                                                <i class="bi bi-exclamation-triangle me-1"></i>Vencido
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($marco['fecha'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $marco['dias'] > $marco['vigencia_dias'] ? 'danger' : 'success' ?>">
                                            <?= $marco['dias'] ?> días
                                        </span>
                                        <br>
                                        <small class="text-muted">Vigencia: <?= $marco['vigencia_dias'] ?> días</small>
                                    </td>
                                    <td>
                                        <?php
                                        $metodoIcons = [
                                            'automatico' => '<i class="bi bi-robot me-1"></i>Automático',
                                            'boton' => '<i class="bi bi-hand-index me-1"></i>Manual (botón)',
                                            'confirmacion' => '<i class="bi bi-question-circle me-1"></i>Confirmación',
                                            'manual' => '<i class="bi bi-pencil me-1"></i>Edición manual'
                                        ];
                                        echo $metodoIcons[$marco['metodo']] ?? $marco['metodo'];
                                        ?>
                                    </td>
                                    <td>
                                        <i class="bi bi-person me-1"></i>
                                        <?= esc($marco['actualizado_por']) ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-consultar-ia btn-sm"
                                                    onclick="consultarIA('<?= esc($marco['tipo']) ?>')"
                                                    title="Consultar con GPT-4o + búsqueda web">
                                                <i class="bi bi-robot"></i>
                                            </button>
                                            <button class="btn btn-ver-editar btn-sm"
                                                    onclick="verMarco('<?= esc($marco['tipo']) ?>', '<?= esc($marco['nombre']) ?>')"
                                                    title="Ver/Editar marco normativo">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Ver/Editar Marco Normativo -->
    <div class="modal fade" id="modalVerMarco" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-book-open me-2"></i>
                        <span id="modalTituloDocumento"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Marco Normativo Vigente:</label>
                        <textarea id="marcoTexto" class="form-control" rows="15" style="font-family: monospace; font-size: 0.9rem;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-robot me-1"></i>
                            Contexto adicional para IA (opcional):
                        </label>
                        <textarea id="marcoContextoIA" class="form-control" rows="2"
                            placeholder="Ej: 'Enfocarse en acoso laboral', 'Incluir legislación reciente <?= date('Y') ?>', 'Priorizar resoluciones del Ministerio de Trabajo'..."
                            style="font-size: 0.9rem;"></textarea>
                        <small class="text-muted">Este contexto personaliza la búsqueda web de GPT-4o.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-consultar-ia" onclick="consultarIADesdeModal()">
                        <i class="bi bi-robot me-2"></i>Consultar con IA
                    </button>
                    <button type="button" class="btn btn-success" onclick="guardarMarco()">
                        <i class="bi bi-save me-2"></i>Guardar Cambios
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let tipoActual = '';

        $(document).ready(function() {
            $('#tablaMarcosNormativos').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                order: [[2, 'desc']], // Ordenar por fecha de actualización
                pageLength: 25
            });
        });

        function verMarco(tipo, nombre) {
            tipoActual = tipo;
            $('#modalTituloDocumento').text(nombre);

            // Cargar marco normativo
            $.get(`<?= base_url('/documentos/marco-normativo') ?>/${tipo}`, function(data) {
                if (data.existe) {
                    $('#marcoTexto').val(data.texto);
                } else {
                    $('#marcoTexto').val('');
                    Swal.fire('Sin marco normativo', 'Este tipo de documento aún no tiene marco normativo registrado.', 'info');
                }
            });

            $('#modalVerMarco').modal('show');
        }

        function consultarIA(tipo) {
            Swal.fire({
                title: '¿Consultar marco normativo con IA?',
                html: `
                    <p class="mb-3">Se consultará a GPT-4o con búsqueda web en tiempo real para obtener el marco normativo vigente.</p>
                    <ul class="text-start">
                        <li><strong>Modelo:</strong> GPT-4o con web_search_preview</li>
                        <li><strong>Tiempo estimado:</strong> 30-90 segundos</li>
                        <li><strong>Resultado:</strong> Marco normativo actualizado con fuentes verificadas</li>
                    </ul>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, consultar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#667eea'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarConsultaIA(tipo);
                }
            });
        }

        function consultarIADesdeModal() {
            if (!tipoActual) return;
            ejecutarConsultaIA(tipoActual);
        }

        function ejecutarConsultaIA(tipo) {
            const contexto = $('#marcoContextoIA').val();

            Swal.fire({
                title: 'Consultando...',
                html: 'GPT-4o está buscando el marco normativo vigente en la web. Esto puede tardar hasta 90 segundos.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.post('<?= base_url('/documentos/marco-normativo/consultar-ia') ?>', {
                tipo_documento: tipo,
                metodo: 'boton',
                contexto: contexto
            })
            .done(function(response) {
                Swal.close();
                if (response.success) {
                    $('#marcoTexto').val(response.texto);
                    Swal.fire('¡Éxito!', 'Marco normativo actualizado con IA. El contenido se inyectará en todas las generaciones IA.', 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    Swal.fire('Error', response.error || 'No se pudo consultar el marco normativo', 'error');
                }
            })
            .fail(function() {
                Swal.close();
                Swal.fire('Error', 'Error de conexión con el servidor', 'error');
            });
        }

        function guardarMarco() {
            if (!tipoActual) return;

            const texto = $('#marcoTexto').val().trim();
            if (!texto) {
                Swal.fire('Error', 'El marco normativo no puede estar vacío', 'warning');
                return;
            }

            $.post('<?= base_url('/documentos/marco-normativo/guardar') ?>', {
                tipo_documento: tipoActual,
                marco_normativo_texto: texto
            })
            .done(function(response) {
                if (response.success) {
                    Swal.fire('¡Guardado!', 'Marco normativo actualizado correctamente. Este contenido se inyectará en todas las generaciones IA.', 'success');
                    $('#modalVerMarco').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire('Error', response.message || 'No se pudo guardar', 'error');
                }
            })
            .fail(function() {
                Swal.fire('Error', 'Error de conexión con el servidor', 'error');
            });
        }
    </script>
</body>
</html>
