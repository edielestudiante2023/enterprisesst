<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Actividad PTA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; }
        .page-header { background: linear-gradient(135deg, #e65100 0%, #bf360c 100%); color: #fff; padding: 1.5rem 0; margin-bottom: 2rem; }
        .page-header h1 { font-size: 1.5rem; font-weight: 600; margin: 0; }
        .page-header .breadcrumb-item a { color: rgba(255,255,255,.7); text-decoration: none; }
        .page-header .breadcrumb-item.active { color: rgba(255,255,255,.9); }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .card-header { border-radius: 12px 12px 0 0 !important; }
        .form-label { font-weight: 600; font-size: .85rem; color: #495057; margin-bottom: .35rem; }
        .form-control:focus, .form-select:focus { border-color: #e65100; box-shadow: 0 0 0 .2rem rgba(230,81,0,.15); }
        .section-title { font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; padding-bottom: .5rem; border-bottom: 2px solid #e9ecef; margin-bottom: 1rem; }
        .btn-primary { background: #e65100; border-color: #e65100; }
        .btn-primary:hover { background: #bf360c; border-color: #bf360c; }
        .id-badge { background: #f5f5f5; border: 1px solid #dee2e6; border-radius: 8px; padding: .5rem 1rem; font-size: .85rem; color: #6c757d; }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="page-header">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="--bs-breadcrumb-divider: '>';">
                    <li class="breadcrumb-item"><a href="<?= site_url('/pta-cliente-nueva/list?' . http_build_query($filters ?? [])) ?>">Plan de Trabajo</a></li>
                    <li class="breadcrumb-item active">Editar #<?= esc($record['id_ptacliente']) ?></li>
                </ol>
            </nav>
            <h1><i class="bi bi-pencil-square me-2"></i>Editar Actividad PTA</h1>
        </div>
    </div>

    <div class="container pb-5">
        <!-- Alertas -->
        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i><?= session('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->has('message')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i><?= session('message') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form method="post" action="<?= site_url('/pta-cliente-nueva/editpost/' . esc($record['id_ptacliente'])) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="filter_cliente" value="<?= esc($filters['cliente'] ?? '') ?>">
            <input type="hidden" name="filter_fecha_desde" value="<?= esc($filters['fecha_desde'] ?? '') ?>">
            <input type="hidden" name="filter_fecha_hasta" value="<?= esc($filters['fecha_hasta'] ?? '') ?>">
            <input type="hidden" name="filter_estado" value="<?= esc($filters['estado'] ?? '') ?>">

            <div class="row g-4">
                <!-- Columna izquierda -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="section-title mb-0 border-0 pb-0"><i class="bi bi-clipboard-data me-1"></i>Datos de la Actividad</div>
                                <span class="id-badge"><i class="bi bi-hash"></i>ID: <?= esc($record['id_ptacliente']) ?></span>
                            </div>

                            <!-- Cliente -->
                            <div class="mb-3">
                                <label for="id_cliente" class="form-label"><i class="bi bi-building me-1"></i>Cliente <span class="text-danger">*</span></label>
                                <select name="id_cliente" id="id_cliente" class="form-select" required>
                                    <option value="">Seleccione un Cliente</option>
                                    <?php if (isset($clients) && !empty($clients)): ?>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?= esc($client['id_cliente']) ?>"
                                                <?= ($record['id_cliente'] == $client['id_cliente']) ? 'selected' : '' ?>>
                                                <?= esc($client['nombre_cliente']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="row g-3">
                                <!-- PHVA -->
                                <div class="col-md-4">
                                    <label for="phva_plandetrabajo" class="form-label">PHVA <span class="text-danger">*</span></label>
                                    <select name="phva_plandetrabajo" id="phva_plandetrabajo" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <option value="P" <?= ($record['phva_plandetrabajo'] == 'P') ? 'selected' : '' ?>>P - Planear</option>
                                        <option value="H" <?= ($record['phva_plandetrabajo'] == 'H') ? 'selected' : '' ?>>H - Hacer</option>
                                        <option value="V" <?= ($record['phva_plandetrabajo'] == 'V') ? 'selected' : '' ?>>V - Verificar</option>
                                        <option value="A" <?= ($record['phva_plandetrabajo'] == 'A') ? 'selected' : '' ?>>A - Actuar</option>
                                    </select>
                                </div>
                                <!-- Numeral -->
                                <div class="col-md-4">
                                    <label for="numeral_plandetrabajo" class="form-label">Numeral <span class="text-danger">*</span></label>
                                    <input type="text" name="numeral_plandetrabajo" id="numeral_plandetrabajo" class="form-control" value="<?= esc($record['numeral_plandetrabajo']) ?>" required>
                                </div>
                                <!-- Tipo Servicio -->
                                <div class="col-md-4">
                                    <label for="tipo_servicio" class="form-label">Tipo Servicio</label>
                                    <input type="text" name="tipo_servicio" id="tipo_servicio" class="form-control" value="<?= esc($record['tipo_servicio']) ?>">
                                </div>
                            </div>

                            <!-- Actividad -->
                            <div class="mt-3">
                                <label for="actividad_plandetrabajo" class="form-label"><i class="bi bi-journal-text me-1"></i>Actividad <span class="text-danger">*</span></label>
                                <textarea name="actividad_plandetrabajo" id="actividad_plandetrabajo" class="form-control" rows="4" required><?= esc($record['actividad_plandetrabajo']) ?></textarea>
                            </div>

                            <!-- Responsable -->
                            <div class="mt-3">
                                <label for="responsable_sugerido_plandetrabajo" class="form-label"><i class="bi bi-person-badge me-1"></i>Responsable Sugerido</label>
                                <input type="text" name="responsable_sugerido_plandetrabajo" id="responsable_sugerido_plandetrabajo" class="form-control" value="<?= esc($record['responsable_sugerido_plandetrabajo']) ?>">
                            </div>

                            <!-- Observaciones -->
                            <div class="mt-3">
                                <label for="observaciones" class="form-label"><i class="bi bi-chat-left-text me-1"></i>Observaciones</label>
                                <textarea name="observaciones" id="observaciones" class="form-control" rows="2"><?= esc($record['observaciones']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha -->
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-body p-4">
                            <div class="section-title"><i class="bi bi-calendar-event me-1"></i>Fechas</div>

                            <div class="mb-3">
                                <label for="fecha_propuesta" class="form-label">Fecha Propuesta <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_propuesta" id="fecha_propuesta" class="form-control" value="<?= esc($record['fecha_propuesta']) ?>" required>
                            </div>

                            <div class="mb-0">
                                <label for="fecha_cierre" class="form-label">Fecha Cierre</label>
                                <input type="date" name="fecha_cierre" id="fecha_cierre" class="form-control" value="<?= esc($record['fecha_cierre']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body p-4">
                            <div class="section-title"><i class="bi bi-speedometer2 me-1"></i>Estado y Avance</div>

                            <div class="mb-3">
                                <label for="estado_actividad" class="form-label">Estado <span class="text-danger">*</span></label>
                                <select name="estado_actividad" id="estado_actividad" class="form-select" required>
                                    <option value="ABIERTA" <?= ($record['estado_actividad'] == 'ABIERTA') ? 'selected' : '' ?>>ABIERTA</option>
                                    <option value="GESTIONANDO" <?= ($record['estado_actividad'] == 'GESTIONANDO') ? 'selected' : '' ?>>GESTIONANDO</option>
                                    <option value="CERRADA" <?= ($record['estado_actividad'] == 'CERRADA') ? 'selected' : '' ?>>CERRADA</option>
                                </select>
                            </div>

                            <div class="mb-0">
                                <label for="porcentaje_avance" class="form-label">Avance</label>
                                <div class="input-group">
                                    <input type="number" name="porcentaje_avance" id="porcentaje_avance" class="form-control" step="1" min="0" max="100" value="<?= esc($record['porcentaje_avance']) ?>">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div id="avance_bar" class="progress-bar" role="progressbar" style="width: <?= esc($record['porcentaje_avance']) ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg me-1"></i>Guardar Cambios
                        </button>
                        <a href="<?= site_url('/pta-cliente-nueva/list?' . http_build_query($filters ?? [])) ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Volver al listado
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Select2
            $('#id_cliente').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Buscar un cliente...',
                allowClear: true,
                minimumInputLength: 0,
                language: { noResults: function() { return "No se encontraron resultados"; } }
            });

            // Barra de progreso
            function updateBar() {
                var val = parseInt($('#porcentaje_avance').val()) || 0;
                var bar = $('#avance_bar');
                bar.css('width', val + '%');
                bar.removeClass('bg-danger bg-warning bg-info bg-success');
                if (val >= 100) bar.addClass('bg-success');
                else if (val >= 50) bar.addClass('bg-info');
                else if (val > 0) bar.addClass('bg-warning');
                else bar.addClass('bg-danger');
            }
            updateBar();
            $('#porcentaje_avance').on('input change', updateBar);

            // Auto-ajustar porcentaje al cambiar estado
            $('#estado_actividad').on('change', function() {
                var estado = $(this).val();
                if (estado === 'CERRADA') $('#porcentaje_avance').val(100).trigger('change');
                else if (estado === 'GESTIONANDO') $('#porcentaje_avance').val(50).trigger('change');
                else if (estado === 'ABIERTA') $('#porcentaje_avance').val(0).trigger('change');
            });
        });
    </script>
</body>

</html>
