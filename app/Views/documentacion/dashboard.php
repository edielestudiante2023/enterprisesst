<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentacion SST - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .card-stat {
            transition: transform 0.2s;
        }
        .card-stat:hover {
            transform: translateY(-3px);
        }

        /* Estados IA */
        .estado-ia-pendiente { background-color: #6c757d !important; color: white !important; }
        .estado-ia-creado { background-color: #ffc107 !important; color: #212529 !important; }
        .estado-ia-aprobado { background-color: #198754 !important; color: white !important; }

        /* Estructura de carpetas tipo arbol */
        .folder-tree {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }
        .folder-tree .folder-tree {
            padding-left: 24px;
            margin-left: 12px;
            border-left: 1px dashed #dee2e6;
        }
        .folder-tree > li {
            margin: 2px 0;
        }

        /* Carpeta colapsable */
        .folder-header {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.15s;
        }
        .folder-header:hover {
            background-color: #f8f9fa;
        }
        .folder-header.phva-planear { border-left: 4px solid #0d6efd; }
        .folder-header.phva-hacer { border-left: 4px solid #198754; }
        .folder-header.phva-verificar { border-left: 4px solid #ffc107; }
        .folder-header.phva-actuar { border-left: 4px solid #dc3545; }

        .folder-chevron {
            transition: transform 0.2s;
            font-size: 0.75rem;
            width: 16px;
            color: #6c757d;
        }
        .folder-chevron.rotated {
            transform: rotate(90deg);
        }

        .folder-icon {
            font-size: 1.1rem;
            margin-right: 8px;
        }

        .folder-name {
            flex-grow: 1;
            font-weight: 500;
            color: #333;
            text-decoration: none;
        }
        .folder-name:hover {
            color: #0d6efd;
        }

        .folder-stats {
            display: flex;
            gap: 4px;
            margin-left: 8px;
        }
        .folder-stats .badge {
            font-size: 0.65rem;
            padding: 2px 6px;
            min-width: 20px;
        }

        .folder-content {
            display: none;
        }
        .folder-content.show {
            display: block;
        }

        /* Documentos dentro de carpetas */
        .docs-container {
            margin-left: 40px;
            padding: 8px 0;
        }

        .doc-card {
            display: flex;
            align-items: center;
            padding: 10px 14px;
            margin: 4px 0;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            text-decoration: none;
            color: #333;
            transition: all 0.15s;
        }
        .doc-card:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            transform: translateX(4px);
            color: #333;
        }

        .doc-estado-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 12px;
            flex-shrink: 0;
        }
        .doc-estado-indicator.pendiente { background-color: #6c757d; }
        .doc-estado-indicator.creado { background-color: #ffc107; }
        .doc-estado-indicator.aprobado { background-color: #198754; }

        .doc-info {
            flex-grow: 1;
            min-width: 0;
        }
        .doc-nombre {
            font-weight: 500;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .doc-meta {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .doc-estado-badge {
            margin-left: 12px;
        }

        .doc-actions {
            display: flex;
            gap: 4px;
            margin-left: 12px;
            opacity: 0;
            transition: opacity 0.15s;
        }
        .doc-card:hover .doc-actions {
            opacity: 1;
        }

        /* Panel principal */
        .main-panel {
            max-height: calc(100vh - 220px);
            overflow-y: auto;
        }
        .main-panel::-webkit-scrollbar {
            width: 8px;
        }
        .main-panel::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        .main-panel::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        /* Botones de accion */
        .btn-expand-all {
            font-size: 0.8rem;
            padding: 4px 12px;
        }

        /* Leyenda */
        .leyenda-estados {
            display: flex;
            gap: 16px;
            align-items: center;
            padding: 8px 16px;
            background: #f8f9fa;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .leyenda-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/documentacion/seleccionar-cliente">
                <i class="bi bi-folder-fill me-2"></i>Documentacion SST
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-3">
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

        <!-- Panel de Roles Obligatorios del SG-SST -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-people-fill text-primary me-2 fs-5"></i>
                            <h6 class="mb-0">Roles Obligatorios del SG-SST</h6>
                            <span class="badge bg-<?= ($estandaresAplicables ?? 60) <= 7 ? 'info' : (($estandaresAplicables ?? 60) <= 21 ? 'warning' : 'danger') ?> ms-2">
                                <?= $estandaresAplicables ?? 60 ?> Estándares
                            </span>
                            <?php if ($verificacionRoles['completo'] ?? false): ?>
                                <span class="badge bg-success ms-2"><i class="bi bi-check-circle me-1"></i>Completo</span>
                            <?php endif; ?>
                        </div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-<?= ($verificacionRoles['completo'] ?? false) ? 'success' : 'warning' ?>"
                                 style="width: <?= $verificacionRoles['porcentaje'] ?? 0 ?>%"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php if (!empty($verificacionRoles['completos'])): ?>
                                    <small class="text-success d-block mb-1"><i class="bi bi-check-circle-fill me-1"></i>Roles asignados:</small>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($verificacionRoles['completos'] as $rol): ?>
                                            <span class="badge bg-success bg-opacity-75" style="font-size: 0.7rem;"><?= esc($rol['nombre']) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Sin roles asignados aún</small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <?php if (!empty($verificacionRoles['faltantes'])): ?>
                                    <small class="text-danger d-block mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i>Roles pendientes:</small>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($verificacionRoles['faltantes'] as $rol): ?>
                                            <span class="badge bg-danger bg-opacity-75" style="font-size: 0.7rem;"><?= esc($rol['nombre']) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="ms-3">
                        <a href="<?= base_url('responsables-sst/' . $cliente['id_cliente']) ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="bi bi-gear me-1"></i>Gestionar Roles
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel principal con estructura de carpetas -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-folder-fill text-warning me-2"></i>
                                    Estructura de Documentos SG-SST
                                </h5>
                                <span class="badge bg-primary"><?= $documentosPorEstado['contadores']['nivel_cliente'] ?? 60 ?> estandares</span>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary btn-sm btn-expand-all" onclick="expandAll()">
                                    <i class="bi bi-arrows-expand me-1"></i>Expandir todo
                                </button>
                                <button class="btn btn-outline-secondary btn-sm btn-expand-all" onclick="collapseAll()">
                                    <i class="bi bi-arrows-collapse me-1"></i>Colapsar todo
                                </button>
                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarCarpeta">
                                    <i class="bi bi-folder-symlink me-1"></i>Agregar carpeta de otro estandar
                                </button>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalGenerarEstructura">
                                    <i class="bi bi-folder-plus me-1"></i>Generar Estructura
                                </button>
                            </div>
                        </div>
                        <!-- Buscador de carpetas -->
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: #6c757d;"></i>
                            <input type="text" id="buscadorCarpetas" class="form-control form-control-sm" placeholder="Buscar carpeta o documento... (ej: comunicaciones, capacitacion)" style="padding-left: 34px;">
                            <button type="button" id="btnLimpiarBusqueda" class="btn-close position-absolute d-none" style="right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.6rem;"></button>
                        </div>
                    </div>
                    <div class="card-body main-panel">
                        <?php if (empty($carpetasConDocs)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-folder-x text-muted" style="font-size: 4rem;"></i>
                                <h5 class="text-muted mt-3">No hay estructura de carpetas</h5>
                                <p class="text-muted">Genera la estructura de carpetas PHVA segun la Resolucion 0312/2019</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGenerarEstructura">
                                    <i class="bi bi-folder-plus me-1"></i>Generar Estructura <?= date('Y') ?>
                                </button>
                            </div>
                        <?php else: ?>
                            <ul class="folder-tree">
                                <?php echo renderCarpetasJerarquicas($carpetasConDocs, $cliente['id_cliente'], 0); ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accesos rapidos -->
        <div class="row mt-3 g-2">
            <div class="col-6 col-md">
                <a href="<?= base_url('indicadores-sst/' . $cliente['id_cliente']) ?>" class="card border-0 shadow-sm text-decoration-none h-100" target="_blank">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-graph-up-arrow text-danger fs-4"></i>
                        <h6 class="mt-2 mb-0 text-dark">Indicadores KPI</h6>
                        <small class="text-muted">Metricas SG-SST</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md">
                <a href="<?= base_url('pta/' . $cliente['id_cliente']) ?>" class="card border-0 shadow-sm text-decoration-none h-100" target="_blank">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-calendar-check text-info fs-4"></i>
                        <h6 class="mt-2 mb-0 text-dark">Plan de Trabajo</h6>
                        <small class="text-muted">Actividades PTA</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md">
                <a href="<?= base_url('estandares/' . $cliente['id_cliente']) ?>" class="card border-0 shadow-sm text-decoration-none h-100" target="_blank">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-list-check text-primary fs-4"></i>
                        <h6 class="mt-2 mb-0 text-dark">Ver Estandares</h6>
                        <small class="text-muted">Res. 0312/2019</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md">
                <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" class="card border-0 shadow-sm text-decoration-none h-100" target="_blank">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-robot text-purple fs-4" style="color: #6f42c1 !important;"></i>
                        <h6 class="mt-2 mb-0 text-dark">Generador IA</h6>
                        <small class="text-muted">Cronograma y PTA</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md">
                <a href="<?= base_url('documentacion/instructivo') ?>" class="card border-0 shadow-sm text-decoration-none h-100" target="_blank">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-question-circle text-warning fs-4"></i>
                        <h6 class="mt-2 mb-0 text-dark">Instructivo</h6>
                        <small class="text-muted">Guia de uso</small>
                    </div>
                </a>
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
                        <p>Se creara la estructura de carpetas PHVA segun la Resolucion 0312/2019.</p>
                        <div class="alert alert-info py-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Este cliente aplica <strong><?= $documentosPorEstado['contadores']['nivel_cliente'] ?? 60 ?> estandares</strong>
                            (<?= ($documentosPorEstado['contadores']['nivel_cliente'] ?? 60) == 7 ? 'Microempresa' : (($documentosPorEstado['contadores']['nivel_cliente'] ?? 60) == 21 ? 'Pequena empresa' : 'Empresa >50 trabajadores') ?>)
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ano</label>
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

    <!-- Modal Agregar carpeta de otro estandar -->
    <div class="modal fade" id="modalAgregarCarpeta" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-folder-symlink me-1"></i>Agregar carpeta de otro estandar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Agrega a este cliente una carpeta de un estandar que normalmente corresponde a otro nivel
                        (ej. <strong>1.1.5 Trabajadores de alto riesgo</strong>, propio de 60 estandares).
                        Se ubica en su ciclo PHVA y <strong>se conserva aunque regeneres la estructura</strong>.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estandar a agregar</label>
                        <select id="selectEstandarAgregar" class="form-select">
                            <option value="">Cargando...</option>
                        </select>
                    </div>
                    <div class="text-end mb-3">
                        <button type="button" class="btn btn-primary btn-sm" id="btnConfirmarAgregarCarpeta">
                            <i class="bi bi-plus-circle me-1"></i>Agregar carpeta
                        </button>
                    </div>

                    <hr>
                    <h6 class="text-muted"><i class="bi bi-list-check me-1"></i>Carpetas agregadas manualmente</h6>
                    <ul class="list-group list-group-flush" id="listaCarpetasManuales">
                        <li class="list-group-item text-muted small">Cargando...</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sugerencias de actividades (IA) -->
    <div class="modal fade" id="modalSugerenciasIA" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-stars me-1"></i>Sugerencias de actividades (IA)</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted small mb-1">Numeral: <strong id="iaNumeralTitulo"></strong></p>
            <p class="text-muted small mb-2">Generadas por IA segun el contexto del cliente. Marca las que quieras agregar al Plan de Trabajo (PTA).</p>
            <div id="iaListaSugerencias"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" id="btnAgregarPta" onclick="agregarSeleccionadasAlPta()" disabled><i class="bi bi-plus-circle me-1"></i>Agregar al PTA</button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const idCliente = <?= $cliente['id_cliente'] ?>;

        // ============ Sugerencias de actividades (IA) ============
        const _ptaInsertUrl = '<?= base_url('pta-cliente-nueva/insertAiActivity') ?>';
        let _iaCtx = { codigo: '', nombre: '', phva: '' };

        function abrirSugerenciasIA(btn) {
            _iaCtx = { codigo: btn.dataset.codigo, nombre: btn.dataset.nombre, phva: btn.dataset.phva };
            document.getElementById('iaNumeralTitulo').textContent = _iaCtx.codigo + ' · ' + _iaCtx.nombre;
            document.getElementById('iaListaSugerencias').innerHTML = '<div class="text-muted"><span class="spinner-border spinner-border-sm me-1"></span>Consultando a la IA...</div>';
            document.getElementById('btnAgregarPta').disabled = true;
            new bootstrap.Modal(document.getElementById('modalSugerenciasIA')).show();

            const fd = new FormData();
            fd.append('codigo', _iaCtx.codigo);
            fd.append('nombre', _iaCtx.nombre);
            fetch(docBaseUrl + '/sugerir-actividades/' + idCliente, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd })
              .then(r => r.json())
              .then(data => {
                if (!data.ok) {
                    document.getElementById('iaListaSugerencias').innerHTML = '<div class="alert alert-danger mb-0">' + (data.error || 'Error') + '</div>';
                    return;
                }
                let html = '<div class="list-group">';
                data.actividades.forEach(a => {
                    html += '<label class="list-group-item">'
                      + '<input class="form-check-input me-2 chk-ia" type="checkbox" checked data-actividad="' + encodeURIComponent(a.actividad) + '">'
                      + '<strong>' + a.actividad + '</strong>'
                      + (a.descripcion ? '<br><small class="text-muted">' + a.descripcion + '</small>' : '')
                      + (a.fuente_legal ? '<br><small><span class="badge bg-light text-dark border">' + a.fuente_legal + '</span></small>' : '')
                      + (a.evidencia ? '<br><small class="text-success"><i class="bi bi-paperclip"></i> Evidencia: ' + a.evidencia + '</small>' : '')
                      + '</label>';
                });
                html += '</div>';
                document.getElementById('iaListaSugerencias').innerHTML = html;
                document.getElementById('btnAgregarPta').disabled = false;
              })
              .catch(() => { document.getElementById('iaListaSugerencias').innerHTML = '<div class="alert alert-danger mb-0">Error de conexion</div>'; });
        }

        function agregarSeleccionadasAlPta() {
            const checks = Array.from(document.querySelectorAll('.chk-ia:checked'));
            if (checks.length === 0) { Swal.fire('Atencion', 'Selecciona al menos una actividad', 'warning'); return; }
            const btn = document.getElementById('btnAgregarPta'); btn.disabled = true;
            const calls = checks.map(c => {
                const fd = new FormData();
                fd.append('id_cliente', idCliente);
                fd.append('phva', _iaCtx.phva);
                fd.append('numeral', _iaCtx.codigo);
                fd.append('actividad', decodeURIComponent(c.dataset.actividad));
                return fetch(_ptaInsertUrl, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd })
                    .then(r => r.json()).catch(() => ({ success: false }));
            });
            Promise.all(calls).then(results => {
                const ok = results.filter(r => r && r.success).length;
                const inst = bootstrap.Modal.getInstance(document.getElementById('modalSugerenciasIA'));
                if (inst) inst.hide();
                btn.disabled = false;
                Swal.fire({ icon: 'success', title: 'Agregadas al PTA', text: ok + ' actividad(es) agregada(s) al Plan de Trabajo.', confirmButtonText: 'Listo' });
            });
        }

        // ============ Agregar carpeta de otro estandar ============
        const docBaseUrl = '<?= rtrim(base_url('documentacion'), '/') ?>';
        const modalAgregarCarpetaEl = document.getElementById('modalAgregarCarpeta');

        function nivelEstandarTexto(e) {
            if (parseInt(e.aplica_7) === 1) return '7/21/60';
            if (parseInt(e.aplica_21) === 1) return '21/60';
            return 'Solo 60';
        }

        function cargarEstandaresDisponibles() {
            const sel = document.getElementById('selectEstandarAgregar');
            const lista = document.getElementById('listaCarpetasManuales');
            sel.innerHTML = '<option value="">Cargando...</option>';
            lista.innerHTML = '<li class="list-group-item text-muted small">Cargando...</li>';

            fetch(`${docBaseUrl}/estandares-disponibles/${idCliente}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    if (!data.estandares || data.estandares.length === 0) {
                        sel.innerHTML = '<option value="">No hay estandares disponibles para agregar</option>';
                    } else {
                        sel.innerHTML = '<option value="">-- Selecciona un estandar --</option>' +
                            data.estandares.map(e =>
                                `<option value="${e.id_estandar}">${e.item} - ${e.nombre} (Nivel: ${nivelEstandarTexto(e)})</option>`
                            ).join('');
                    }
                    if (!data.manuales || data.manuales.length === 0) {
                        lista.innerHTML = '<li class="list-group-item text-muted small">Aun no has agregado carpetas manuales.</li>';
                    } else {
                        lista.innerHTML = data.manuales.map(m =>
                            `<li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><span class="badge bg-warning text-dark me-1">manual</span>${m.codigo} - ${m.nombre}</span>
                                <button class="btn btn-outline-danger btn-sm btn-quitar-carpeta" data-id="${m.id_carpeta}" title="Quitar"><i class="bi bi-trash"></i></button>
                             </li>`
                        ).join('');
                    }
                })
                .catch(() => {
                    sel.innerHTML = '<option value="">Error al cargar</option>';
                    lista.innerHTML = '<li class="list-group-item text-danger small">Error al cargar.</li>';
                });
        }

        if (modalAgregarCarpetaEl) {
            modalAgregarCarpetaEl.addEventListener('show.bs.modal', cargarEstandaresDisponibles);
        }

        document.getElementById('btnConfirmarAgregarCarpeta')?.addEventListener('click', function() {
            const sel = document.getElementById('selectEstandarAgregar');
            const idEstandar = sel.value;
            if (!idEstandar) { Swal.fire('Atencion', 'Selecciona un estandar', 'warning'); return; }
            const btn = this; btn.disabled = true;
            const fd = new FormData(); fd.append('id_estandar', idEstandar);
            fetch(`${docBaseUrl}/agregar-carpeta-manual/${idCliente}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Listo', text: data.message, timer: 1500, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire('No se pudo', data.message || 'Error', 'error');
                    }
                })
                .catch(() => { btn.disabled = false; Swal.fire('Error', 'Error de conexion', 'error'); });
        });

        document.getElementById('listaCarpetasManuales')?.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-quitar-carpeta');
            if (!btn) return;
            const idCarpeta = btn.dataset.id;
            Swal.fire({
                title: 'Quitar carpeta?',
                text: 'Se eliminara esta carpeta agregada manualmente.',
                icon: 'warning', showCancelButton: true, confirmButtonText: 'Si, quitar', cancelButtonText: 'Cancelar', confirmButtonColor: '#d33'
            }).then(res => {
                if (!res.isConfirmed) return;
                fetch(`${docBaseUrl}/eliminar-carpeta-manual/${idCarpeta}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Eliminada', timer: 1200, showConfirmButton: false }).then(() => location.reload());
                        } else {
                            Swal.fire('No se pudo', data.message || 'Error', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Error de conexion', 'error'));
            });
        });

        // Toggle de carpetas
        function toggleFolder(folderId) {
            const content = document.getElementById('content-' + folderId);
            const chevron = document.getElementById('chevron-' + folderId);

            if (content) {
                content.classList.toggle('show');
            }
            if (chevron) {
                chevron.classList.toggle('rotated');
            }
        }

        // Expandir todas las carpetas
        function expandAll() {
            document.querySelectorAll('.folder-content').forEach(el => el.classList.add('show'));
            document.querySelectorAll('.folder-chevron').forEach(el => el.classList.add('rotated'));
        }

        // Colapsar todas las carpetas
        function collapseAll() {
            document.querySelectorAll('.folder-content').forEach(el => el.classList.remove('show'));
            document.querySelectorAll('.folder-chevron').forEach(el => el.classList.remove('rotated'));
        }

        // Expandir primer nivel al cargar
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.folder-tree > li > .folder-content').forEach(el => {
                el.classList.add('show');
                const folderId = el.id.replace('content-', '');
                const chevron = document.getElementById('chevron-' + folderId);
                if (chevron) chevron.classList.add('rotated');
            });
        });

        // Buscador de carpetas y documentos
        const buscadorInput = document.getElementById('buscadorCarpetas');
        const btnLimpiar = document.getElementById('btnLimpiarBusqueda');
        let debounceTimer;

        buscadorInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => filtrarArbol(this.value.trim()), 200);
            btnLimpiar.classList.toggle('d-none', !this.value.trim());
        });

        btnLimpiar.addEventListener('click', function() {
            buscadorInput.value = '';
            btnLimpiar.classList.add('d-none');
            filtrarArbol('');
            buscadorInput.focus();
        });

        function filtrarArbol(termino) {
            const carpetas = document.querySelectorAll('.folder-tree li');
            const docs = document.querySelectorAll('.doc-card');

            if (!termino) {
                // Restaurar estado normal
                carpetas.forEach(li => li.style.display = '');
                docs.forEach(doc => doc.style.display = '');
                // Restaurar: solo primer nivel expandido
                document.querySelectorAll('.folder-content').forEach(el => el.classList.remove('show'));
                document.querySelectorAll('.folder-chevron').forEach(el => el.classList.remove('rotated'));
                document.querySelectorAll('.folder-tree > li > .folder-content').forEach(el => {
                    el.classList.add('show');
                    const folderId = el.id.replace('content-', '');
                    const chevron = document.getElementById('chevron-' + folderId);
                    if (chevron) chevron.classList.add('rotated');
                });
                return;
            }

            const palabras = termino.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').split(/\s+/);

            // Primero ocultar todo
            carpetas.forEach(li => li.style.display = 'none');
            docs.forEach(doc => doc.style.display = 'none');

            // Funcion para verificar si un texto coincide con todas las palabras
            function coincide(texto) {
                const norm = texto.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                return palabras.every(p => norm.includes(p));
            }

            // Encontrar carpetas y documentos que coinciden
            carpetas.forEach(li => {
                const header = li.querySelector(':scope > .folder-header');
                if (!header) return;

                const nombre = header.querySelector('.folder-name');
                if (!nombre) return;
                const textoNombre = nombre.textContent;

                // Revisar documentos dentro de esta carpeta
                const docsEnCarpeta = li.querySelectorAll(':scope > .folder-content > .docs-container > .doc-card');
                let tieneDocCoincidente = false;
                docsEnCarpeta.forEach(doc => {
                    const docNombre = doc.querySelector('.doc-nombre');
                    const docMeta = doc.querySelector('.doc-meta');
                    const textoDoc = (docNombre ? docNombre.textContent : '') + ' ' + (docMeta ? docMeta.textContent : '');
                    if (coincide(textoDoc)) {
                        doc.style.display = '';
                        tieneDocCoincidente = true;
                    }
                });

                // La carpeta coincide por nombre o tiene docs que coinciden
                const carpetaCoincide = coincide(textoNombre);

                if (carpetaCoincide || tieneDocCoincidente) {
                    // Mostrar esta carpeta y expandirla
                    li.style.display = '';
                    const content = li.querySelector(':scope > .folder-content');
                    const chevron = li.querySelector(':scope > .folder-header .folder-chevron');
                    if (content) content.classList.add('show');
                    if (chevron) chevron.classList.add('rotated');

                    // Si la carpeta coincide por nombre, mostrar todos sus docs
                    if (carpetaCoincide) {
                        docsEnCarpeta.forEach(doc => doc.style.display = '');
                    }

                    // Mostrar y expandir todos los ancestros
                    mostrarAncestros(li);

                    // Mostrar subcarpetas si la carpeta coincide
                    if (carpetaCoincide) {
                        li.querySelectorAll('li').forEach(sub => sub.style.display = '');
                        li.querySelectorAll('.doc-card').forEach(doc => doc.style.display = '');
                        li.querySelectorAll('.folder-content').forEach(el => el.classList.add('show'));
                        li.querySelectorAll('.folder-chevron').forEach(el => el.classList.add('rotated'));
                    }
                }

                // Revisar subcarpetas recursivamente (ya se manejan en la iteracion general)
            });
        }

        function mostrarAncestros(element) {
            let parent = element.parentElement;
            while (parent) {
                if (parent.tagName === 'LI') {
                    parent.style.display = '';
                    const content = parent.querySelector(':scope > .folder-content');
                    const chevron = parent.querySelector(':scope > .folder-header .folder-chevron');
                    if (content) content.classList.add('show');
                    if (chevron) chevron.classList.add('rotated');
                }
                parent = parent.parentElement;
            }
        }

        // Generar estructura de carpetas
        document.getElementById('formGenerarEstructura').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('<?= base_url('documentacion/generar-estructura') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Estructura creada',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al generar estructura'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al procesar la solicitud'
                });
            });
        });
    </script>
</body>
</html>

<?php
/**
 * Renderiza la estructura jerarquica de carpetas con documentos
 */
function renderCarpetasJerarquicas($carpetas, $idCliente, $nivel = 0) {
    $html = '';

    foreach ($carpetas as $carpeta) {
        $stats = $carpeta['stats'] ?? ['total' => 0, 'pendiente' => 0, 'creado' => 0, 'aprobado' => 0];
        $tieneContenido = !empty($carpeta['hijos']) || !empty($carpeta['documentos']);
        $folderId = $carpeta['id_carpeta'];

        // Determinar clase de color segun tipo PHVA
        $phvaClass = '';
        if (($carpeta['tipo'] ?? '') === 'phva') {
            $codigo = $carpeta['codigo'] ?? '';
            $phvaClass = match($codigo) {
                '1' => 'phva-planear',
                '2' => 'phva-hacer',
                '3' => 'phva-verificar',
                '4' => 'phva-actuar',
                default => ''
            };
        }

        // Icono segun tipo
        $iconClass = match($carpeta['tipo'] ?? 'custom') {
            'phva' => 'bi-folder-fill text-warning',
            'categoria' => 'bi-folder text-info',
            'estandar' => 'bi-folder text-secondary',
            default => 'bi-folder text-warning'
        };

        $html .= '<li>';

        // Header de carpeta
        $html .= '<div class="folder-header ' . $phvaClass . '" onclick="toggleFolder(' . $folderId . ')">';

        // Chevron
        if ($tieneContenido) {
            $html .= '<i class="bi bi-chevron-right folder-chevron" id="chevron-' . $folderId . '"></i>';
        } else {
            $html .= '<span class="folder-chevron"></span>';
        }

        // Icono de carpeta
        $html .= '<i class="bi ' . $iconClass . ' folder-icon"></i>';

        // Nombre (clickeable para ir a la carpeta)
        $html .= '<a href="' . base_url('documentacion/carpeta/' . $folderId) . '" class="folder-name" onclick="event.stopPropagation();" target="_blank">';
        $html .= esc($carpeta['nombre']);
        $html .= '</a>';

        // Boton "Sugerir actividades (IA)" solo en carpetas de estandar (numerales)
        if (($carpeta['tipo'] ?? '') === 'estandar' && !empty($carpeta['codigo'])) {
            $cod = $carpeta['codigo'];
            $phvaLetra = ['1' => 'P', '2' => 'H', '3' => 'V', '4' => 'A'][substr($cod, 0, 1)] ?? 'P';
            $html .= '<button type="button" title="Sugerir actividades con IA"'
                  . ' onclick="event.stopPropagation(); abrirSugerenciasIA(this);"'
                  . ' data-codigo="' . esc($cod, 'attr') . '"'
                  . ' data-nombre="' . esc($carpeta['nombre'], 'attr') . '"'
                  . ' data-phva="' . $phvaLetra . '"'
                  . ' style="border:1px solid #6f42c1;color:#6f42c1;background:#fff;border-radius:4px;font-size:11px;font-weight:600;padding:1px 7px;margin-left:8px;cursor:pointer;white-space:nowrap;">'
                  . '<i class="bi bi-stars"></i> IA</button>';
        }

        // Stats badges
        if ($stats['total'] > 0) {
            $html .= '<div class="folder-stats">';
            if ($stats['aprobado'] > 0) {
                $html .= '<span class="badge estado-ia-aprobado" title="Aprobados">' . $stats['aprobado'] . '</span>';
            }
            if ($stats['creado'] > 0) {
                $html .= '<span class="badge estado-ia-creado" title="Creados">' . $stats['creado'] . '</span>';
            }
            if ($stats['pendiente'] > 0) {
                $html .= '<span class="badge estado-ia-pendiente" title="Pendientes">' . $stats['pendiente'] . '</span>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        // Contenido (documentos y subcarpetas)
        if ($tieneContenido) {
            $html .= '<div class="folder-content" id="content-' . $folderId . '">';

            // Documentos de esta carpeta
            if (!empty($carpeta['documentos'])) {
                $html .= '<div class="docs-container">';
                foreach ($carpeta['documentos'] as $doc) {
                    $estadoIA = $doc['estado_ia'] ?? 'pendiente';
                    $estadoIAText = match($estadoIA) {
                        'aprobado' => 'Aprobado',
                        'creado' => 'Creado',
                        default => 'Pendiente'
                    };
                    $estadoIAClass = match($estadoIA) {
                        'aprobado' => 'estado-ia-aprobado',
                        'creado' => 'estado-ia-creado',
                        default => 'estado-ia-pendiente'
                    };

                    $html .= '<a href="' . base_url('documentacion/ver/' . $doc['id_documento']) . '" class="doc-card" target="_blank">';
                    $html .= '<span class="doc-estado-indicator ' . $estadoIA . '" title="' . $estadoIAText . '"></span>';
                    $html .= '<div class="doc-info">';
                    $html .= '<div class="doc-nombre">' . esc($doc['nombre']) . '</div>';
                    $html .= '<div class="doc-meta">';
                    $html .= '<code>' . esc($doc['codigo']) . '</code>';
                    $html .= ' &bull; v' . esc($doc['version_actual']);
                    $html .= ' &bull; ' . date('d/m/Y', strtotime($doc['updated_at']));
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '<span class="badge ' . $estadoIAClass . ' doc-estado-badge">' . $estadoIAText . '</span>';
                    $html .= '<div class="doc-actions">';
                    $html .= '<a href="' . base_url('documentacion/editar/' . $doc['id_documento']) . '" class="btn btn-sm btn-outline-secondary" title="Editar" onclick="event.stopPropagation();" target="_blank"><i class="bi bi-pencil"></i></a>';
                    $html .= '<a href="' . base_url('exportar/pdf/' . $doc['id_documento']) . '" class="btn btn-sm btn-outline-danger" title="PDF" onclick="event.stopPropagation();" target="_blank"><i class="bi bi-file-pdf"></i></a>';
                    $html .= '</div>';
                    $html .= '</a>';
                }
                $html .= '</div>';
            }

            // Subcarpetas recursivas
            if (!empty($carpeta['hijos'])) {
                $html .= '<ul class="folder-tree">';
                $html .= renderCarpetasJerarquicas($carpeta['hijos'], $idCliente, $nivel + 1);
                $html .= '</ul>';
            }

            $html .= '</div>';
        }

        $html .= '</li>';
    }

    return $html;
}
?>
