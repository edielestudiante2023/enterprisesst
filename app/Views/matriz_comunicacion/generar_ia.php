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

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
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

        .search-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 30px;
        }

        .search-input {
            font-size: 1.2rem;
            padding: 15px 20px;
            border: 2px solid #ddd;
            border-radius: 50px;
        }

        .search-input:focus {
            border-color: var(--gold-primary);
            box-shadow: 0 0 0 0.2rem rgba(189, 151, 81, 0.25);
        }

        .btn-search {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 50px;
            padding: 15px 30px;
            font-size: 1.1rem;
            color: white;
        }

        .btn-search:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
            color: white;
        }

        .suggestion-chip {
            display: inline-block;
            padding: 8px 16px;
            margin: 5px;
            background: #f0f0f0;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .suggestion-chip:hover {
            background: var(--gold-primary);
            color: white;
            transform: translateY(-2px);
        }

        /* Tabs */
        .nav-tabs .nav-link {
            color: var(--primary-dark);
            font-weight: 600;
            border: none;
            padding: 12px 24px;
        }

        .nav-tabs .nav-link.active {
            color: var(--gold-primary);
            border-bottom: 3px solid var(--gold-primary);
            background: transparent;
        }

        /* Loading */
        .loading-container {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--gold-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Preview cards */
        .preview-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .preview-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark));
            color: white;
            padding: 15px 20px;
        }

        .preview-body {
            padding: 20px;
        }

        .preview-field {
            display: flex;
            align-items: flex-start;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .preview-field:last-child {
            border-bottom: none;
        }

        .preview-label {
            flex: 0 0 180px;
            font-weight: 600;
            color: var(--primary-dark);
            font-size: 0.85rem;
            padding: 8px 12px;
            background: #f8f9fa;
            border-left: 3px solid var(--gold-primary);
            border-radius: 0 4px 4px 0;
        }

        .preview-value {
            flex: 1;
            padding: 4px 12px;
        }

        .preview-value input,
        .preview-value textarea,
        .preview-value select {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 0.9rem;
        }

        .preview-value textarea {
            resize: vertical;
            min-height: 60px;
        }

        .preview-actions {
            padding: 15px 20px;
            background: #f8f9fa;
            text-align: right;
        }

        /* Bulk mode */
        .bulk-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 30px;
        }

        .client-info {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .client-info-item {
            margin-bottom: 8px;
        }

        .client-info-label {
            font-weight: 600;
            color: var(--primary-dark);
        }

        .bulk-table-container {
            max-height: 500px;
            overflow-y: auto;
        }

        .bulk-table th {
            position: sticky;
            top: 0;
            background: var(--primary-dark);
            color: white;
            z-index: 1;
        }

        .bulk-row-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .robot-icon {
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-custom mb-4">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="<?= base_url('matriz-comunicacion') ?>">
                <i class="fas fa-robot me-2 robot-icon"></i>Generar con IA
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url('matriz-comunicacion') ?>" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Volver a Matriz
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2"><i class="fas fa-robot me-2"></i>Generar Matriz de Comunicacion con IA</h2>
                    <p class="mb-0 opacity-75">Genera protocolos de comunicacion SST automaticamente usando inteligencia artificial</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-inline-block text-start">
                        <small><strong>Protocolos existentes:</strong> <?= $estadisticas['total'] ?? 0 ?></small><br>
                        <small><strong>Categorias cubiertas:</strong> <?= count($estadisticas['por_categoria'] ?? []) ?>/11</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tabBulk" role="tab">
                    <i class="fas fa-magic me-2"></i>Generar Matriz Completa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tabIndividual" role="tab">
                    <i class="fas fa-search me-2"></i>Buscar Protocolo Especifico
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Tab 1: Generacion masiva -->
            <div class="tab-pane fade show active" id="tabBulk" role="tabpanel">
                <div class="bulk-card">
                    <h4 class="mb-3"><i class="fas fa-list-check me-2"></i>Generar Matriz Completa para este Cliente</h4>

                    <div class="client-info">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="client-info-item">
                                    <span class="client-info-label"><i class="fas fa-building me-1"></i> Empresa:</span>
                                    <span><?= esc($cliente['nombre_cliente'] ?? $cliente['razon_social'] ?? 'N/A') ?></span>
                                </div>
                                <div class="client-info-item">
                                    <span class="client-info-label"><i class="fas fa-industry me-1"></i> Sector:</span>
                                    <span><?= esc($contexto['sector_economico'] ?? 'No definido') ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="client-info-item">
                                    <span class="client-info-label"><i class="fas fa-exclamation-triangle me-1"></i> Riesgo ARL:</span>
                                    <span><?= esc($contexto['nivel_riesgo_arl'] ?? 'N/A') ?></span>
                                </div>
                                <div class="client-info-item">
                                    <span class="client-info-label"><i class="fas fa-users me-1"></i> Trabajadores:</span>
                                    <span><?= esc($contexto['total_trabajadores'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="client-info-item">
                                    <span class="client-info-label"><i class="fas fa-shield-alt me-1"></i> Comite:</span>
                                    <span><?= (!empty($contexto['tiene_copasst']) && $contexto['tiene_copasst'] === 'si') ? 'COPASST' : 'Vigia SST' ?></span>
                                </div>
                                <div class="client-info-item">
                                    <span class="client-info-label"><i class="fas fa-hospital me-1"></i> ARL:</span>
                                    <span><?= esc($contexto['arl_actual'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mb-3">
                        <p class="text-muted">La IA generara entre 25 y 35 protocolos cubriendo las 11 categorias obligatorias, adaptados al sector y nivel de riesgo de esta empresa.</p>
                        <button type="button" class="btn btn-gold btn-lg" onclick="generarBulk()" id="btnGenerarBulk">
                            <i class="fas fa-magic me-2"></i>Generar Matriz Completa con IA
                        </button>
                    </div>

                    <!-- Loading bulk -->
                    <div class="loading-container" id="loadingBulk">
                        <div class="loading-spinner"></div>
                        <h5>Generando protocolos de comunicacion...</h5>
                        <p class="text-muted" id="loadingBulkText">Analizando contexto de la empresa y normativa aplicable. Esto puede tomar hasta 2 minutos.</p>
                    </div>

                    <!-- Resultados bulk -->
                    <div id="resultadosBulk" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="fas fa-check-circle text-success me-2"></i>Protocolos Generados: <span id="totalGenerados">0</span></h5>
                            <div>
                                <button class="btn btn-sm btn-outline-primary me-2" onclick="seleccionarTodos()">
                                    <i class="fas fa-check-double me-1"></i>Seleccionar Todos
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="deseleccionarTodos()">
                                    <i class="fas fa-times me-1"></i>Deseleccionar
                                </button>
                            </div>
                        </div>

                        <div class="bulk-table-container">
                            <table class="table table-sm table-striped table-hover" id="tablaBulk">
                                <thead>
                                    <tr>
                                        <th style="width:40px"><input type="checkbox" id="checkAll" onchange="toggleAll(this)" class="bulk-row-checkbox"></th>
                                        <th>Categoria</th>
                                        <th>Situacion/Evento</th>
                                        <th>Quien Comunica</th>
                                        <th>A Quien</th>
                                        <th>Canal</th>
                                        <th>Plazo</th>
                                        <th>Tipo</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyBulk"></tbody>
                            </table>
                        </div>

                        <div class="text-center mt-3">
                            <span class="me-3"><strong id="seleccionadosCount">0</strong> seleccionados</span>
                            <button type="button" class="btn btn-gold btn-lg" onclick="guardarBulk()">
                                <i class="fas fa-save me-2"></i>Guardar Seleccionados en la Matriz
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Busqueda individual -->
            <div class="tab-pane fade" id="tabIndividual" role="tabpanel">
                <div class="search-card">
                    <div class="row justify-content-center">
                        <div class="col-md-10">
                            <div class="input-group mb-4">
                                <input type="text" id="consultaIA" class="form-control search-input"
                                       placeholder="Describa la situacion, ej: Acoso sexual, Accidente mortal, Incendio..."
                                       autofocus>
                                <button class="btn btn-search" type="button" onclick="buscarIndividual()">
                                    <i class="fas fa-robot me-2"></i>Generar
                                </button>
                            </div>

                            <div class="text-center mb-4">
                                <small class="text-muted d-block mb-2">Sugerencias:</small>
                                <span class="suggestion-chip" onclick="buscarSugerencia('Accidente de trabajo mortal')">Accidente de trabajo mortal</span>
                                <span class="suggestion-chip" onclick="buscarSugerencia('Acoso sexual en el trabajo')">Acoso sexual en el trabajo</span>
                                <span class="suggestion-chip" onclick="buscarSugerencia('Acoso laboral')">Acoso laboral</span>
                                <span class="suggestion-chip" onclick="buscarSugerencia('Incendio o explosion')">Incendio o explosion</span>
                                <span class="suggestion-chip" onclick="buscarSugerencia('Enfermedad laboral diagnosticada')">Enfermedad laboral diagnosticada</span>
                                <span class="suggestion-chip" onclick="buscarSugerencia('Reporte de condiciones inseguras')">Reporte de condiciones inseguras</span>
                                <span class="suggestion-chip" onclick="buscarSugerencia('Resultados de auditoria interna')">Resultados de auditoria interna</span>
                                <span class="suggestion-chip" onclick="buscarSugerencia('Emergencia por sismo')">Emergencia por sismo</span>
                                <span class="suggestion-chip" onclick="buscarSugerencia('Derrame de sustancias quimicas')">Derrame de sustancias quimicas</span>
                            </div>
                        </div>
                    </div>

                    <!-- Loading individual -->
                    <div class="loading-container" id="loadingIndividual">
                        <div class="loading-spinner"></div>
                        <h5>Generando protocolos...</h5>
                        <p class="text-muted">Consultando normativa colombiana aplicable</p>
                    </div>

                    <!-- Resultados individuales -->
                    <div id="resultadosIndividual" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let protocolosBulk = [];

        // ==================== TAB 1: BULK ====================

        function generarBulk() {
            $('#btnGenerarBulk').prop('disabled', true);
            $('#loadingBulk').slideDown();
            $('#resultadosBulk').hide();

            $.ajax({
                url: '<?= base_url('matriz-comunicacion/generar-bulk-ia') ?>',
                type: 'POST',
                timeout: 150000,
                success: function(response) {
                    $('#loadingBulk').slideUp();
                    $('#btnGenerarBulk').prop('disabled', false);

                    if (response.success && response.protocolos) {
                        protocolosBulk = response.protocolos;
                        renderizarTablaBulk(protocolosBulk);
                        $('#totalGenerados').text(protocolosBulk.length);
                        $('#resultadosBulk').slideDown();
                    } else {
                        Swal.fire('Error', response.message || 'No se pudieron generar los protocolos', 'error');
                    }
                },
                error: function(xhr) {
                    $('#loadingBulk').slideUp();
                    $('#btnGenerarBulk').prop('disabled', false);
                    Swal.fire('Error', 'Error de conexion. Intente nuevamente.', 'error');
                }
            });
        }

        function renderizarTablaBulk(protocolos) {
            let html = '';
            protocolos.forEach(function(p, idx) {
                let tipoLabel = (p.tipo || 'interna');
                html += `<tr>
                    <td><input type="checkbox" class="bulk-row-checkbox row-check" data-index="${idx}" checked onchange="actualizarConteo()"></td>
                    <td><small>${escapeHtml(p.categoria || '')}</small></td>
                    <td><small>${escapeHtml(p.situacion_evento || '')}</small></td>
                    <td><small>${escapeHtml(p.quien_comunica || '')}</small></td>
                    <td><small>${escapeHtml(p.a_quien_comunicar || '')}</small></td>
                    <td><small>${escapeHtml(p.mecanismo_canal || '')}</small></td>
                    <td><small>${escapeHtml(p.frecuencia_plazo || '')}</small></td>
                    <td><span class="badge bg-${tipoLabel === 'interna' ? 'info' : tipoLabel === 'externa' ? 'purple' : 'warning'}">${tipoLabel}</span></td>
                </tr>`;
            });
            $('#bodyBulk').html(html);
            actualizarConteo();
        }

        function toggleAll(checkbox) {
            $('.row-check').prop('checked', checkbox.checked);
            actualizarConteo();
        }

        function seleccionarTodos() {
            $('.row-check').prop('checked', true);
            $('#checkAll').prop('checked', true);
            actualizarConteo();
        }

        function deseleccionarTodos() {
            $('.row-check').prop('checked', false);
            $('#checkAll').prop('checked', false);
            actualizarConteo();
        }

        function actualizarConteo() {
            let count = $('.row-check:checked').length;
            $('#seleccionadosCount').text(count);
        }

        function guardarBulk() {
            let seleccionados = [];
            $('.row-check:checked').each(function() {
                let idx = $(this).data('index');
                seleccionados.push(protocolosBulk[idx]);
            });

            if (seleccionados.length === 0) {
                Swal.fire('Aviso', 'Seleccione al menos un protocolo para guardar', 'warning');
                return;
            }

            Swal.fire({
                title: 'Guardar protocolos?',
                text: `Se guardaran ${seleccionados.length} protocolos en la matriz`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#bd9751',
                confirmButtonText: 'Si, guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Guardando...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    $.ajax({
                        url: '<?= base_url('matriz-comunicacion/guardar-desde-ia') ?>',
                        type: 'POST',
                        data: { protocolos: JSON.stringify(seleccionados) },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Guardado exitoso',
                                    html: `<strong>${response.insertados}</strong> protocolos guardados en la matriz`,
                                    showCancelButton: true,
                                    confirmButtonText: 'Ver Matriz',
                                    cancelButtonText: 'Generar mas',
                                    confirmButtonColor: '#bd9751'
                                }).then((r) => {
                                    if (r.isConfirmed) {
                                        window.location.href = '<?= base_url('matriz-comunicacion') ?>';
                                    }
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Error al guardar', 'error');
                        }
                    });
                }
            });
        }

        // ==================== TAB 2: INDIVIDUAL ====================

        function buscarSugerencia(texto) {
            $('#consultaIA').val(texto);
            buscarIndividual();
        }

        function buscarIndividual() {
            let consulta = $('#consultaIA').val().trim();
            if (!consulta) {
                Swal.fire('Aviso', 'Escriba una situacion o escenario', 'warning');
                return;
            }

            $('#loadingIndividual').slideDown();
            $('#resultadosIndividual').hide().empty();

            $.ajax({
                url: '<?= base_url('matriz-comunicacion/procesar-generacion-ia') ?>',
                type: 'POST',
                data: { consulta: consulta },
                timeout: 120000,
                success: function(response) {
                    $('#loadingIndividual').slideUp();

                    if (response.success && response.protocolos) {
                        renderizarPreviewCards(response.protocolos);
                    } else {
                        Swal.fire('Sin resultados', response.message || 'No se pudieron generar protocolos para esta situacion', 'info');
                    }
                },
                error: function() {
                    $('#loadingIndividual').slideUp();
                    Swal.fire('Error', 'Error de conexion. Intente nuevamente.', 'error');
                }
            });
        }

        // Enter para buscar
        $('#consultaIA').on('keypress', function(e) {
            if (e.which === 13) buscarIndividual();
        });

        function renderizarPreviewCards(protocolos) {
            let html = '';

            protocolos.forEach(function(p, idx) {
                html += `
                <div class="preview-card" id="previewCard_${idx}">
                    <div class="preview-header">
                        <h5 class="mb-0"><i class="fas fa-comments me-2"></i>${escapeHtml(p.categoria || 'Protocolo')} - ${escapeHtml(p.situacion_evento || '')}</h5>
                    </div>
                    <div class="preview-body">
                        <div class="preview-field">
                            <div class="preview-label"><i class="fas fa-tag me-1"></i> Categoria</div>
                            <div class="preview-value">
                                <select class="form-select form-select-sm" id="prev_cat_${idx}">
                                    <?php foreach ($categorias as $key => $value): ?>
                                        <option value="<?= $key ?>">${'<?= $key ?>' === (p.categoria || '') ? 'selected' : ''}><?= $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="preview-field">
                            <div class="preview-label"><i class="fas fa-exclamation-circle me-1"></i> Situacion</div>
                            <div class="preview-value"><input type="text" id="prev_sit_${idx}" value="${escapeAttr(p.situacion_evento || '')}"></div>
                        </div>
                        <div class="preview-field">
                            <div class="preview-label"><i class="fas fa-bullhorn me-1"></i> Que Comunicar</div>
                            <div class="preview-value"><textarea id="prev_que_${idx}">${escapeHtml(p.que_comunicar || '')}</textarea></div>
                        </div>
                        <div class="preview-field">
                            <div class="preview-label"><i class="fas fa-user me-1"></i> Quien Comunica</div>
                            <div class="preview-value"><input type="text" id="prev_quien_${idx}" value="${escapeAttr(p.quien_comunica || '')}"></div>
                        </div>
                        <div class="preview-field">
                            <div class="preview-label"><i class="fas fa-users me-1"></i> A Quien</div>
                            <div class="preview-value"><input type="text" id="prev_aquien_${idx}" value="${escapeAttr(p.a_quien_comunicar || '')}"></div>
                        </div>
                        <div class="preview-field">
                            <div class="preview-label"><i class="fas fa-satellite-dish me-1"></i> Canal</div>
                            <div class="preview-value"><input type="text" id="prev_canal_${idx}" value="${escapeAttr(p.mecanismo_canal || '')}"></div>
                        </div>
                        <div class="preview-field">
                            <div class="preview-label"><i class="fas fa-clock me-1"></i> Plazo</div>
                            <div class="preview-value"><input type="text" id="prev_plazo_${idx}" value="${escapeAttr(p.frecuencia_plazo || '')}"></div>
                        </div>
                        <div class="preview-field">
                            <div class="preview-label"><i class="fas fa-clipboard-check me-1"></i> Registro</div>
                            <div class="preview-value"><input type="text" id="prev_reg_${idx}" value="${escapeAttr(p.registro_evidencia || '')}"></div>
                        </div>
                        <div class="preview-field">
                            <div class="preview-label"><i class="fas fa-gavel me-1"></i> Norma</div>
                            <div class="preview-value"><input type="text" id="prev_norma_${idx}" value="${escapeAttr(p.norma_aplicable || '')}"></div>
                        </div>
                        <div class="preview-field">
                            <div class="preview-label"><i class="fas fa-exchange-alt me-1"></i> Tipo</div>
                            <div class="preview-value">
                                <select class="form-select form-select-sm" id="prev_tipo_${idx}">
                                    <option value="interna" ${(p.tipo || '') === 'interna' ? 'selected' : ''}>Interna</option>
                                    <option value="externa" ${(p.tipo || '') === 'externa' ? 'selected' : ''}>Externa</option>
                                    <option value="ambas" ${(p.tipo || '') === 'ambas' ? 'selected' : ''}>Ambas</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="preview-actions">
                        <button class="btn btn-gold" onclick="guardarIndividual(${idx})">
                            <i class="fas fa-plus me-1"></i>Agregar a Matriz
                        </button>
                    </div>
                </div>`;
            });

            $('#resultadosIndividual').html(html).slideDown();

            // Set categoria selects
            protocolos.forEach(function(p, idx) {
                $(`#prev_cat_${idx}`).val(p.categoria || '');
            });
        }

        function guardarIndividual(idx) {
            let data = {
                categoria: $(`#prev_cat_${idx}`).val(),
                situacion_evento: $(`#prev_sit_${idx}`).val(),
                que_comunicar: $(`#prev_que_${idx}`).val(),
                quien_comunica: $(`#prev_quien_${idx}`).val(),
                a_quien_comunicar: $(`#prev_aquien_${idx}`).val(),
                mecanismo_canal: $(`#prev_canal_${idx}`).val(),
                frecuencia_plazo: $(`#prev_plazo_${idx}`).val(),
                registro_evidencia: $(`#prev_reg_${idx}`).val(),
                norma_aplicable: $(`#prev_norma_${idx}`).val(),
                tipo: $(`#prev_tipo_${idx}`).val()
            };

            Swal.fire({
                title: 'Guardando...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: '<?= base_url('matriz-comunicacion/guardar-desde-ia') ?>',
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $(`#previewCard_${idx}`).slideUp(300, function() { $(this).remove(); });
                        Swal.fire({
                            icon: 'success',
                            title: 'Agregado',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Aviso', response.message, 'warning');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al guardar', 'error');
                }
            });
        }

        // Helpers
        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/&/g, "&amp;")
                       .replace(/</g, "&lt;")
                       .replace(/>/g, "&gt;")
                       .replace(/"/g, "&quot;")
                       .replace(/'/g, "&#039;");
        }

        function escapeAttr(text) {
            if (!text) return '';
            return text.replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }
    </script>
</body>

</html>
