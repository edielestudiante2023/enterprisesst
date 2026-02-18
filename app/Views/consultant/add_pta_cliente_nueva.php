<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Actividad PTA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; }
        .page-header { background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%); color: #fff; padding: 1.5rem 0; margin-bottom: 2rem; }
        .page-header h1 { font-size: 1.5rem; font-weight: 600; margin: 0; }
        .page-header .breadcrumb-item a { color: rgba(255,255,255,.7); text-decoration: none; }
        .page-header .breadcrumb-item.active { color: rgba(255,255,255,.9); }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .card-header { border-radius: 12px 12px 0 0 !important; }
        .ia-card { border: 2px solid #1a73e8; background: linear-gradient(135deg, #e8f0fe 0%, #f8f9ff 100%); }
        .ia-card .card-header { background: linear-gradient(135deg, #1a73e8 0%, #4285f4 100%); border: none; }
        .form-label { font-weight: 600; font-size: .85rem; color: #495057; margin-bottom: .35rem; }
        .form-control:focus, .form-select:focus { border-color: #1a73e8; box-shadow: 0 0 0 .2rem rgba(26,115,232,.15); }
        .section-title { font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; padding-bottom: .5rem; border-bottom: 2px solid #e9ecef; margin-bottom: 1rem; }
        .btn-primary { background: #1a73e8; border-color: #1a73e8; }
        .btn-primary:hover { background: #1557b0; border-color: #1557b0; }
        .ia-highlight { animation: iaGlow .6s ease-out; }
        @keyframes iaGlow { 0% { background-color: #bbdefb; } 100% { background-color: #fff; } }
        .estado-badge { display: inline-block; padding: .25rem .75rem; border-radius: 50px; font-size: .75rem; font-weight: 600; }
        .estado-abierta { background: #fff3e0; color: #e65100; }
        .estado-gestionando { background: #e3f2fd; color: #1565c0; }
        .estado-cerrada { background: #e8f5e9; color: #2e7d32; }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="page-header">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="--bs-breadcrumb-divider: '>';">
                    <li class="breadcrumb-item"><a href="<?= site_url('/pta-cliente-nueva/list') ?>">Plan de Trabajo</a></li>
                    <li class="breadcrumb-item active">Nueva Actividad</li>
                </ol>
            </nav>
            <h1><i class="bi bi-plus-circle me-2"></i>Agregar Actividad al PTA</h1>
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

        <!-- Asistente IA -->
        <div class="card ia-card mb-4">
            <div class="card-header text-white py-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-robot fs-4 me-2"></i>
                    <div>
                        <h6 class="mb-0 fw-bold">Asistente IA</h6>
                        <small class="opacity-75">Describe la actividad y la IA completa el formulario</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-end g-3">
                    <div class="col-lg-4">
                        <label class="form-label"><i class="bi bi-building me-1"></i>Cliente</label>
                        <select id="ia_cliente" class="form-select form-select-lg">
                            <option value="">Seleccione un cliente...</option>
                            <?php if (isset($clients) && !empty($clients)): ?>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= esc($client['id_cliente']) ?>">
                                        <?= esc($client['nombre_cliente']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-lg-5">
                        <label class="form-label"><i class="bi bi-chat-dots me-1"></i>Describe brevemente la actividad</label>
                        <input type="text" id="ia_descripcion" class="form-control form-control-lg" placeholder="Ej: capacitación manejo de extintores..." maxlength="300">
                    </div>
                    <div class="col-lg-3">
                        <button type="button" id="btn_completar_ia" class="btn btn-primary btn-lg w-100" disabled>
                            <span id="ia_spinner" class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                            <i class="bi bi-magic me-1" id="ia_icon"></i>Completar con IA
                        </button>
                    </div>
                </div>
                <div id="ia_feedback" class="mt-3 d-none rounded-3"></div>
            </div>
        </div>

        <!-- Formulario -->
        <form method="post" action="<?= site_url('/pta-cliente-nueva/addpost') ?>">
            <?= csrf_field() ?>

            <div class="row g-4">
                <!-- Columna izquierda: Datos principales -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body p-4">
                            <div class="section-title"><i class="bi bi-clipboard-data me-1"></i>Datos de la Actividad</div>

                            <!-- Cliente -->
                            <div class="mb-3">
                                <label for="id_cliente" class="form-label"><i class="bi bi-building me-1"></i>Cliente <span class="text-danger">*</span></label>
                                <select name="id_cliente" id="id_cliente" class="form-select" required>
                                    <option value="">Seleccione un Cliente</option>
                                    <?php if (isset($clients) && !empty($clients)): ?>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?= esc($client['id_cliente']) ?>">
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
                                        <option value="P">P - Planear</option>
                                        <option value="H">H - Hacer</option>
                                        <option value="V">V - Verificar</option>
                                        <option value="A">A - Actuar</option>
                                    </select>
                                </div>
                                <!-- Numeral -->
                                <div class="col-md-4">
                                    <label for="numeral_plandetrabajo" class="form-label">Numeral <span class="text-danger">*</span></label>
                                    <input type="text" name="numeral_plandetrabajo" id="numeral_plandetrabajo" class="form-control" placeholder="Ej: 2.1.1" required>
                                </div>
                                <!-- Tipo Servicio -->
                                <div class="col-md-4">
                                    <label for="tipo_servicio" class="form-label">Tipo Servicio</label>
                                    <input type="text" name="tipo_servicio" id="tipo_servicio" class="form-control" placeholder="Opcional">
                                </div>
                            </div>

                            <!-- Actividad -->
                            <div class="mt-3">
                                <label for="actividad_plandetrabajo" class="form-label"><i class="bi bi-journal-text me-1"></i>Actividad <span class="text-danger">*</span></label>
                                <textarea name="actividad_plandetrabajo" id="actividad_plandetrabajo" class="form-control" rows="4" placeholder="Descripci&oacute;n de la actividad del plan de trabajo..." required></textarea>
                            </div>

                            <!-- Responsable -->
                            <div class="mt-3">
                                <label for="responsable_sugerido_plandetrabajo" class="form-label"><i class="bi bi-person-badge me-1"></i>Responsable Sugerido</label>
                                <input type="text" name="responsable_sugerido_plandetrabajo" id="responsable_sugerido_plandetrabajo" class="form-control" placeholder="Ej: Responsable del SG-SST, Empleador, ARL...">
                            </div>

                            <!-- Observaciones -->
                            <div class="mt-3">
                                <label for="observaciones" class="form-label"><i class="bi bi-chat-left-text me-1"></i>Observaciones</label>
                                <textarea name="observaciones" id="observaciones" class="form-control" rows="2" placeholder="Opcional"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: Fechas y estado -->
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-body p-4">
                            <div class="section-title"><i class="bi bi-calendar-event me-1"></i>Fechas</div>

                            <div class="mb-3">
                                <label for="fecha_propuesta" class="form-label">Fecha Propuesta <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_propuesta" id="fecha_propuesta" class="form-control" required>
                            </div>

                            <div class="mb-0">
                                <label for="fecha_cierre" class="form-label">Fecha Cierre</label>
                                <input type="date" name="fecha_cierre" id="fecha_cierre" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body p-4">
                            <div class="section-title"><i class="bi bi-speedometer2 me-1"></i>Estado y Avance</div>

                            <div class="mb-3">
                                <label for="estado_actividad" class="form-label">Estado <span class="text-danger">*</span></label>
                                <select name="estado_actividad" id="estado_actividad" class="form-select" required>
                                    <option value="ABIERTA" selected>ABIERTA</option>
                                    <option value="GESTIONANDO">GESTIONANDO</option>
                                    <option value="CERRADA">CERRADA</option>
                                </select>
                            </div>

                            <div class="mb-0">
                                <label for="porcentaje_avance" class="form-label">Avance</label>
                                <div class="input-group">
                                    <input type="number" name="porcentaje_avance" id="porcentaje_avance" class="form-control" step="1" min="0" max="100" value="0">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div id="avance_bar" class="progress-bar bg-warning" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg me-1"></i>Agregar Actividad
                        </button>
                        <a href="<?= site_url('/pta-cliente-nueva/list') ?>" class="btn btn-outline-secondary">
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
            // Select2 para ambos selects de cliente
            $('#id_cliente, #ia_cliente').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Buscar un cliente...',
                allowClear: true,
                minimumInputLength: 0,
                language: { noResults: function() { return "No se encontraron resultados"; } }
            });

            // Sincronizar selects: IA → formulario y formulario → IA
            $('#ia_cliente').on('change', function() {
                var val = $(this).val();
                $('#id_cliente').val(val).trigger('change.select2');
                toggleIAButton();
            });
            $('#id_cliente').on('change', function() {
                var val = $(this).val();
                $('#ia_cliente').val(val).trigger('change.select2');
                toggleIAButton();
            });

            // Barra de progreso avance
            $('#porcentaje_avance').on('input change', function() {
                var val = parseInt($(this).val()) || 0;
                var bar = $('#avance_bar');
                bar.css('width', val + '%');
                bar.removeClass('bg-danger bg-warning bg-info bg-success');
                if (val >= 100) bar.addClass('bg-success');
                else if (val >= 50) bar.addClass('bg-info');
                else if (val > 0) bar.addClass('bg-warning');
                else bar.addClass('bg-danger');
            });

            // Auto-ajustar porcentaje al cambiar estado
            $('#estado_actividad').on('change', function() {
                var estado = $(this).val();
                if (estado === 'CERRADA') $('#porcentaje_avance').val(100).trigger('change');
                else if (estado === 'GESTIONANDO') $('#porcentaje_avance').val(50).trigger('change');
                else if (estado === 'ABIERTA') $('#porcentaje_avance').val(0).trigger('change');
            });

            // --- Asistente IA ---
            function toggleIAButton() {
                var clienteOk = $('#ia_cliente').val() && $('#ia_cliente').val() !== '';
                var descOk = $('#ia_descripcion').val().trim().length >= 3;
                $('#btn_completar_ia').prop('disabled', !(clienteOk && descOk));
            }
            $('#ia_descripcion').on('input', toggleIAButton);

            $('#ia_descripcion').on('keydown', function(e) {
                if (e.key === 'Enter') { e.preventDefault(); if (!$('#btn_completar_ia').prop('disabled')) $('#btn_completar_ia').click(); }
            });

            $('#btn_completar_ia').on('click', function() {
                var btn = $(this), spinner = $('#ia_spinner'), icon = $('#ia_icon'), feedback = $('#ia_feedback');
                var idCliente = $('#ia_cliente').val(), descripcion = $('#ia_descripcion').val().trim();
                if (!idCliente || !descripcion) return;

                btn.prop('disabled', true);
                spinner.removeClass('d-none');
                icon.addClass('d-none');
                feedback.removeClass('d-none alert-danger alert-success').addClass('alert alert-info').html('<i class="bi bi-hourglass-split me-1"></i>Consultando IA...');

                $.ajax({
                    url: '<?= site_url("/pta-cliente-nueva/completar-ia") ?>',
                    method: 'POST',
                    dataType: 'json',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    data: {
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>',
                        id_cliente: idCliente,
                        descripcion: descripcion
                    },
                    success: function(res) {
                        if (res.success && res.campos) {
                            var phva = (res.campos.phva_plandetrabajo || '').charAt(0).toUpperCase();
                            $('#phva_plandetrabajo').val(phva);
                            $('#numeral_plandetrabajo').val(res.campos.numeral_plandetrabajo);
                            $('#actividad_plandetrabajo').val(res.campos.actividad_plandetrabajo);
                            $('#responsable_sugerido_plandetrabajo').val(res.campos.responsable_sugerido_plandetrabajo);
                            $('#fecha_propuesta').val(res.campos.fecha_propuesta);

                            ['phva_plandetrabajo','numeral_plandetrabajo','actividad_plandetrabajo','responsable_sugerido_plandetrabajo','fecha_propuesta'].forEach(function(id) {
                                var el = $('#' + id);
                                el.addClass('ia-highlight');
                                setTimeout(function() { el.removeClass('ia-highlight'); }, 2000);
                            });

                            feedback.removeClass('alert-info alert-danger').addClass('alert-success').html('<i class="bi bi-check-circle me-1"></i>Campos completados. Revisa y ajusta antes de guardar.');
                        } else {
                            feedback.removeClass('alert-info alert-success').addClass('alert-danger').html('<i class="bi bi-exclamation-triangle me-1"></i>' + (res.error || 'Error al consultar IA'));
                        }
                    },
                    error: function() {
                        feedback.removeClass('alert-info alert-success').addClass('alert-danger').html('<i class="bi bi-wifi-off me-1"></i>Error de conexión. Intenta de nuevo.');
                    },
                    complete: function() {
                        spinner.addClass('d-none');
                        icon.removeClass('d-none');
                        toggleIAButton();
                    }
                });
            });
        });
    </script>
</body>

</html>
