<?php
/**
 * Componente: Tabla de Documentos SST con DataTables Avanzado
 * Variables requeridas: $tipoCarpetaFases, $documentosSSTAprobados, $cliente
 * Características: Filtros por columna, Exportación Excel avanzada
 */
$tiposConTabla = ['capacitacion_sst', 'responsables_sst', 'responsabilidades_sgsst', 'archivo_documental', 'presupuesto_sst', 'induccion_reinduccion', 'promocion_prevencion_salud', 'matriz_legal', 'politicas_2_1_1', 'mecanismos_comunicacion_sgsst', 'plan_objetivos_metas', 'adquisiciones_sst', 'evaluacion_proveedores', 'procedimiento_evaluaciones_medicas', 'estilos_vida_saludable', 'evaluacion_impacto_cambios', 'diagnostico_condiciones_salud', 'evaluaciones_medicas', 'reporte_accidentes_trabajo', 'investigacion_incidentes', 'metodologia_identificacion_peligros', 'identificacion_sustancias_cancerigenas', 'procedimientos_seguridad', 'mantenimiento_periodico', 'identificacion_alto_riesgo', 'manual_convivencia_1_1_8'];
if (!isset($tipoCarpetaFases) || !in_array($tipoCarpetaFases, $tiposConTabla)) {
    return;
}

$tableId = 'tablaDocumentosSST_' . uniqid();
$nombreCliente = $cliente['nombre_cliente'] ?? $cliente['razon_social'] ?? $cliente['nombre'] ?? 'Cliente';
$nitCliente = $cliente['nit_cliente'] ?? $cliente['nit'] ?? 'N/A';
?>
<!-- Toast Stack (sistema estandar ZZ_91) -->
<div class="toast-container position-fixed top-0 end-0 p-3" id="toastStack" style="z-index: 9999;"></div>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/searchpanes/2.2.0/css/searchPanes.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.bootstrap5.min.css">

<!-- Tabla de Documentos SST con DataTables -->
<div class="card border-0 shadow-lg mb-4 tabla-documentos-sst-card">
    <!-- Header con gradiente moderno -->
    <div class="card-header <?= ($tipoCarpetaFases === 'archivo_documental') ? 'header-archivo-documental' : 'header-documentos-sst' ?>">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 d-flex align-items-center">
                <?php if ($tipoCarpetaFases === 'archivo_documental'): ?>
                    <div class="icon-wrapper me-3">
                        <i class="bi bi-archive-fill"></i>
                    </div>
                    <div>
                        <span class="header-title">Control Documental del SG-SST</span>
                        <small class="d-block header-subtitle">Gestión integral de documentos</small>
                    </div>
                <?php else: ?>
                    <div class="icon-wrapper me-3">
                        <i class="bi bi-file-earmark-check-fill"></i>
                    </div>
                    <div>
                        <span class="header-title">Documentos SST</span>
                        <small class="d-block header-subtitle">Sistema de Gestión</small>
                    </div>
                <?php endif; ?>
            </h6>
            <div class="header-stats">
                <span class="stat-badge">
                    <i class="bi bi-files me-1"></i>
                    <span id="totalDocumentos"><?= count($documentosSSTAprobados) ?></span> documentos
                </span>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <?php if (!empty($documentosSSTAprobados)): ?>
        <!-- Barra de herramientas -->
        <div class="toolbar-container">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="search-box">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" id="searchInput_<?= $tableId ?>" class="form-control search-input" placeholder="Buscar documentos...">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end gap-2" id="exportButtons_<?= $tableId ?>">
                        <!-- Botón de exportar Excel -->
                        <button class="btn btn-success btn-export-excel" id="btnExportExcel_<?= $tableId ?>" type="button">
                            <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                            <span>Exportar Excel</span>
                        </button>
                        <div class="btn-group">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-eye me-1"></i>Columnas
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><label class="dropdown-item"><input type="checkbox" class="col-toggle me-2" data-column="0" checked> Código</label></li>
                                <li><label class="dropdown-item"><input type="checkbox" class="col-toggle me-2" data-column="1" checked> Nombre</label></li>
                                <li><label class="dropdown-item"><input type="checkbox" class="col-toggle me-2" data-column="2" checked> Año</label></li>
                                <li><label class="dropdown-item"><input type="checkbox" class="col-toggle me-2" data-column="3" checked> Versión</label></li>
                                <li><label class="dropdown-item"><input type="checkbox" class="col-toggle me-2" data-column="4" checked> Estado</label></li>
                                <li><label class="dropdown-item"><input type="checkbox" class="col-toggle me-2" data-column="5" checked> Fecha</label></li>
                                <li><label class="dropdown-item"><input type="checkbox" class="col-toggle me-2" data-column="6" checked> Firmas</label></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Contenedor de la tabla (siempre visible - patron ReportList) -->
        <div class="table-responsive tabla-container">
            <table id="<?= $tableId ?>" class="table table-hover tabla-documentos-moderna" style="width:100%">
                <thead>
                    <!-- Fila de títulos -->
                    <tr class="header-row">
                        <th data-priority="1">Código</th>
                        <th data-priority="1">Nombre del Documento</th>
                        <th data-priority="3">Año</th>
                        <th data-priority="3">Versión</th>
                        <th data-priority="2">Estado</th>
                        <th data-priority="4">Fecha Aprobación</th>
                        <th data-priority="3">Firmas</th>
                        <th data-priority="1" class="no-export">Acciones</th>
                    </tr>
                    <?php if (!empty($documentosSSTAprobados)): ?>
                    <!-- Fila de filtros -->
                    <tr class="filters-row">
                        <th><input type="text" class="form-control form-control-sm filter-input" placeholder="Filtrar..."></th>
                        <th><input type="text" class="form-control form-control-sm filter-input" placeholder="Filtrar..."></th>
                        <th>
                            <select class="form-select form-select-sm filter-select">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select">
                                <option value="">Todas</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select">
                                <option value="">Todos</option>
                                <option value="Firmado">Firmado</option>
                                <option value="Pendiente firma">Pendiente firma</option>
                                <option value="Aprobado">Aprobado</option>
                                <option value="Borrador">Borrador</option>
                                <option value="Generado">Generado</option>
                            </select>
                        </th>
                        <th><input type="text" class="form-control form-control-sm filter-input" placeholder="dd/mm/yyyy"></th>
                        <th>
                            <select class="form-select form-select-sm filter-select">
                                <option value="">Todos</option>
                                <option value="completas">Completas</option>
                                <option value="pendientes">Pendientes</option>
                                <option value="sin">Sin firmas</option>
                            </select>
                        </th>
                        <th></th>
                    </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php if (!empty($documentosSSTAprobados)): ?>
                    <?php foreach ($documentosSSTAprobados as $docSST): ?>
                        <?php
                        $estadoDoc = $docSST['estado'] ?? 'aprobado';
                        $estadoBadge = match($estadoDoc) {
                            'firmado' => 'estado-firmado',
                            'pendiente_firma' => 'estado-pendiente-firma',
                            'aprobado' => 'estado-aprobado',
                            'borrador' => 'estado-borrador',
                            'generado' => 'estado-generado',
                            default => 'estado-default'
                        };
                        $estadoTexto = match($estadoDoc) {
                            'firmado' => 'Firmado',
                            'pendiente_firma' => 'Pendiente firma',
                            'aprobado' => 'Aprobado',
                            'borrador' => 'Borrador',
                            'generado' => 'Generado',
                            default => ucfirst(str_replace('_', ' ', $estadoDoc))
                        };
                        $estadoIcono = match($estadoDoc) {
                            'firmado' => 'bi-patch-check-fill',
                            'pendiente_firma' => 'bi-pen-fill',
                            'aprobado' => 'bi-check-circle-fill',
                            'borrador' => 'bi-pencil-square',
                            'generado' => 'bi-file-earmark-text-fill',
                            default => 'bi-circle-fill'
                        };

                        // Determinar estado de firmas para filtro
                        $firmasStatus = 'sin';
                        if ($docSST['firmas_total'] > 0) {
                            $firmasStatus = ($docSST['firmas_firmadas'] == $docSST['firmas_total']) ? 'completas' : 'pendientes';
                        }
                        ?>
                        <?php
                        // Preparar datos de versiones para el child row de DataTables
                        $versionesHtml = '';
                        if (!empty($docSST['versiones'])) {
                            ob_start();
                            echo view('documentacion/_components/historial_versiones', ['versiones' => $docSST['versiones']]);
                            $versionesHtml = ob_get_clean();
                        }
                        ?>
                        <tr data-id="<?= $docSST['id_documento'] ?>"
                            data-firmas-status="<?= $firmasStatus ?>"
                            data-versiones="<?= htmlspecialchars($versionesHtml, ENT_QUOTES, 'UTF-8') ?>">
                            <td>
                                <div class="codigo-cell">
                                    <code class="codigo-badge"><?= esc($docSST['codigo'] ?? 'N/A') ?></code>
                                </div>
                            </td>
                            <td>
                                <div class="nombre-cell">
                                    <span class="nombre-documento"><?= esc($docSST['titulo']) ?></span>
                                    <?php if (!empty($docSST['versiones']) && count($docSST['versiones']) > 0): ?>
                                        <button class="btn btn-sm btn-link p-0 ms-2 btn-versiones" type="button">
                                            <i class="bi bi-clock-history me-1"></i>
                                            <span class="badge bg-light text-dark"><?= count($docSST['versiones']) ?></span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="anio-badge"><?= esc($docSST['anio']) ?></span>
                            </td>
                            <td>
                                <span class="version-badge">
                                    <i class="bi bi-layers me-1"></i>v<?= esc($docSST['version_texto'] ?? $docSST['version'] . '.0') ?>
                                </span>
                            </td>
                            <td>
                                <span class="estado-badge-moderno <?= $estadoBadge ?>">
                                    <i class="bi <?= $estadoIcono ?> me-1"></i><?= $estadoTexto ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($docSST['fecha_aprobacion'])): ?>
                                    <div class="fecha-cell">
                                        <i class="bi bi-calendar-check me-1 text-muted"></i>
                                        <span><?= date('d/m/Y', strtotime($docSST['fecha_aprobacion'])) ?></span>
                                        <small class="d-block text-muted"><?= date('H:i', strtotime($docSST['fecha_aprobacion'])) ?></small>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($docSST['firmas_total'] > 0): ?>
                                    <div class="firmas-cell">
                                        <div class="progress-circle <?= $docSST['firmas_firmadas'] == $docSST['firmas_total'] ? 'completo' : 'pendiente' ?>">
                                            <span><?= $docSST['firmas_firmadas'] ?>/<?= $docSST['firmas_total'] ?></span>
                                        </div>
                                        <div class="progress" style="height: 4px; width: 60px;">
                                            <div class="progress-bar <?= $docSST['firmas_firmadas'] == $docSST['firmas_total'] ? 'bg-success' : 'bg-warning' ?>"
                                                 style="width: <?= ($docSST['firmas_firmadas'] / $docSST['firmas_total']) * 100 ?>%"></div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted">Sin firmas</span>
                                <?php endif; ?>
                            </td>
                            <td class="acciones-cell">
                                <?= view('documentacion/_components/acciones_documento', [
                                    'docSST' => $docSST,
                                    'cliente' => $cliente
                                ]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <!-- Fila vacia (patron ReportList - encabezados siempre visibles) -->
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="bi bi-folder2-open text-muted" style="font-size: 2rem;"></i>
                            <?php if ($tipoCarpetaFases === 'archivo_documental'): ?>
                                <p class="text-muted mt-2 mb-0">No hay documentos generados</p>
                                <small class="text-muted">Los documentos del SG-SST aparecerán aquí cuando sean creados.</small>
                            <?php else: ?>
                                <p class="text-muted mt-2 mb-0">No hay documentos aprobados o firmados aún.</p>
                                <small class="text-muted">Complete las fases y apruebe el documento para verlo aquí.</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- jQuery (debe cargarse primero) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- DataTables Scripts -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<!-- ExcelJS para exportación avanzada -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<script>
$(document).ready(function() {
    const tableId = '<?= $tableId ?>';

    // Solo inicializar DataTables si hay documentos en la tabla
    if ($('#' + tableId + ' tbody tr[data-id]').length === 0) return;

    const nombreCliente = '<?= addslashes($nombreCliente) ?>';
    const nitCliente = '<?= addslashes($nitCliente) ?>';
    const fechaExport = new Date().toLocaleDateString('es-CO', {day: '2-digit', month: '2-digit', year: 'numeric'});
    const fechaHoraExport = new Date().toLocaleString('es-CO', {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'});

    // Datos para exportación
    const documentosData = <?= json_encode(array_map(function($doc) {
        return [
            'codigo' => $doc['codigo'] ?? 'N/A',
            'titulo' => $doc['titulo'] ?? '',
            'anio' => $doc['anio'] ?? '',
            'version' => $doc['version_texto'] ?? ($doc['version'] . '.0'),
            'estado' => match($doc['estado'] ?? 'aprobado') {
                'firmado' => 'Firmado',
                'pendiente_firma' => 'Pendiente firma',
                'aprobado' => 'Aprobado',
                'borrador' => 'Borrador',
                'generado' => 'Generado',
                default => ucfirst(str_replace('_', ' ', $doc['estado'] ?? 'aprobado'))
            },
            'fecha_aprobacion' => !empty($doc['fecha_aprobacion']) ? date('d/m/Y H:i', strtotime($doc['fecha_aprobacion'])) : '-',
            'firmas' => ($doc['firmas_total'] > 0) ? ($doc['firmas_firmadas'] . '/' . $doc['firmas_total']) : 'Sin firmas',
            'firmas_completas' => ($doc['firmas_total'] > 0 && $doc['firmas_firmadas'] == $doc['firmas_total'])
        ];
    }, $documentosSSTAprobados)) ?>;

    // ========================================
    // FUNCIÓN DE EXPORTACIÓN EXCEL AVANZADA
    // ========================================
    async function exportarExcelAvanzado() {
        const workbook = new ExcelJS.Workbook();

        // Propiedades del documento
        workbook.creator = 'Enterprise SST';
        workbook.lastModifiedBy = 'Sistema SG-SST';
        workbook.created = new Date();
        workbook.modified = new Date();
        workbook.properties = {
            title: 'Control Documental SG-SST',
            subject: 'Documentos del Sistema de Gestión de Seguridad y Salud en el Trabajo',
            company: nombreCliente
        };

        // Crear hoja principal
        const worksheet = workbook.addWorksheet('Control Documental', {
            properties: { tabColor: { argb: '1e3a5f' } },
            pageSetup: {
                paperSize: 9, // A4
                orientation: 'landscape',
                fitToPage: true,
                fitToWidth: 1,
                fitToHeight: 0,
                margins: {
                    left: 0.5, right: 0.5,
                    top: 0.75, bottom: 0.75,
                    header: 0.3, footer: 0.3
                }
            },
            headerFooter: {
                oddHeader: '&C&B&14CONTROL DOCUMENTAL SG-SST',
                oddFooter: '&LGenerado: ' + fechaHoraExport + '&CPágina &P de &N&REnterprise SST'
            }
        });

        // Definir columnas con anchos optimizados
        worksheet.columns = [
            { key: 'codigo', width: 18 },
            { key: 'titulo', width: 55 },
            { key: 'anio', width: 10 },
            { key: 'version', width: 12 },
            { key: 'estado', width: 18 },
            { key: 'fecha', width: 20 },
            { key: 'firmas', width: 14 }
        ];

        // ========================================
        // ENCABEZADO CORPORATIVO
        // ========================================

        // Fila 1: Título principal con merge
        worksheet.mergeCells('A1:G1');
        const titleCell = worksheet.getCell('A1');
        titleCell.value = '📋 CONTROL DOCUMENTAL DEL SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO (SG-SST)';
        titleCell.font = { name: 'Calibri', size: 16, bold: true, color: { argb: 'FFFFFFFF' } };
        titleCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1e3a5f' } };
        titleCell.alignment = { horizontal: 'center', vertical: 'middle' };
        worksheet.getRow(1).height = 35;

        // Fila 2: Información del cliente
        worksheet.mergeCells('A2:G2');
        const clienteCell = worksheet.getCell('A2');
        clienteCell.value = `🏢 Empresa: ${nombreCliente}  |  NIT: ${nitCliente}`;
        clienteCell.font = { name: 'Calibri', size: 12, bold: true, color: { argb: 'FFFFFFFF' } };
        clienteCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF2563eb' } };
        clienteCell.alignment = { horizontal: 'center', vertical: 'middle' };
        worksheet.getRow(2).height = 28;

        // Fila 3: Fecha de generación y estadísticas
        const totalDocs = documentosData.length;
        const firmados = documentosData.filter(d => d.estado === 'Firmado').length;
        const aprobados = documentosData.filter(d => d.estado === 'Aprobado').length;
        const pendientes = documentosData.filter(d => d.estado === 'Pendiente firma').length;

        worksheet.mergeCells('A3:G3');
        const statsCell = worksheet.getCell('A3');
        statsCell.value = `📅 Fecha: ${fechaHoraExport}  |  📊 Total: ${totalDocs} documentos  |  ✅ Firmados: ${firmados}  |  📝 Aprobados: ${aprobados}  |  ⏳ Pendientes: ${pendientes}`;
        statsCell.font = { name: 'Calibri', size: 11, color: { argb: 'FF1e293b' } };
        statsCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFe2e8f0' } };
        statsCell.alignment = { horizontal: 'center', vertical: 'middle' };
        worksheet.getRow(3).height = 25;

        // Fila 4: Espacio
        worksheet.getRow(4).height = 8;

        // ========================================
        // ENCABEZADOS DE LA TABLA
        // ========================================
        const headerRow = worksheet.getRow(5);
        const headers = ['CÓDIGO', 'NOMBRE DEL DOCUMENTO', 'AÑO', 'VERSIÓN', 'ESTADO', 'FECHA APROBACIÓN', 'FIRMAS'];

        headers.forEach((header, index) => {
            const cell = headerRow.getCell(index + 1);
            cell.value = header;
            cell.font = { name: 'Calibri', size: 11, bold: true, color: { argb: 'FFFFFFFF' } };
            cell.fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: 'FF1e3a5f' }
            };
            cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
            cell.border = {
                top: { style: 'medium', color: { argb: 'FF0f172a' } },
                left: { style: 'thin', color: { argb: 'FF334155' } },
                bottom: { style: 'medium', color: { argb: 'FF0f172a' } },
                right: { style: 'thin', color: { argb: 'FF334155' } }
            };
        });
        headerRow.height = 30;

        // ========================================
        // DATOS DE LA TABLA
        // ========================================
        let rowIndex = 6;
        documentosData.forEach((doc, index) => {
            const row = worksheet.getRow(rowIndex);
            const isEven = index % 2 === 0;

            // Valores
            row.getCell(1).value = doc.codigo;
            row.getCell(2).value = doc.titulo;
            row.getCell(3).value = doc.anio;
            row.getCell(4).value = 'v' + doc.version;
            row.getCell(5).value = doc.estado;
            row.getCell(6).value = doc.fecha_aprobacion;
            row.getCell(7).value = doc.firmas;

            // Estilos por columna
            row.eachCell({ includeEmpty: true }, (cell, colNumber) => {
                // Fondo alternado
                const bgColor = isEven ? 'FFF8FAFC' : 'FFFFFFFF';
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: bgColor } };

                // Bordes
                cell.border = {
                    top: { style: 'thin', color: { argb: 'FFe2e8f0' } },
                    left: { style: 'thin', color: { argb: 'FFe2e8f0' } },
                    bottom: { style: 'thin', color: { argb: 'FFe2e8f0' } },
                    right: { style: 'thin', color: { argb: 'FFe2e8f0' } }
                };

                // Fuente base
                cell.font = { name: 'Calibri', size: 10 };
                cell.alignment = { vertical: 'middle' };
            });

            // Estilo específico para código
            row.getCell(1).font = { name: 'Consolas', size: 10, bold: true, color: { argb: 'FF1e3a5f' } };
            row.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };

            // Estilo para título
            row.getCell(2).font = { name: 'Calibri', size: 10, bold: false };
            row.getCell(2).alignment = { horizontal: 'left', vertical: 'middle', wrapText: true };

            // Estilo para año y versión (centrado)
            row.getCell(3).alignment = { horizontal: 'center', vertical: 'middle' };
            row.getCell(4).alignment = { horizontal: 'center', vertical: 'middle' };
            row.getCell(4).font = { name: 'Calibri', size: 10, bold: true, color: { argb: 'FF2563eb' } };

            // Estilo condicional para ESTADO
            const estadoCell = row.getCell(5);
            estadoCell.alignment = { horizontal: 'center', vertical: 'middle' };
            estadoCell.font = { name: 'Calibri', size: 10, bold: true };

            switch(doc.estado) {
                case 'Firmado':
                    estadoCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFd1fae5' } };
                    estadoCell.font.color = { argb: 'FF065f46' };
                    break;
                case 'Aprobado':
                    estadoCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFdbeafe' } };
                    estadoCell.font.color = { argb: 'FF1e40af' };
                    break;
                case 'Pendiente firma':
                    estadoCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFcffafe' } };
                    estadoCell.font.color = { argb: 'FF0e7490' };
                    break;
                case 'Borrador':
                    estadoCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFfef3c7' } };
                    estadoCell.font.color = { argb: 'FF92400e' };
                    break;
                case 'Generado':
                    estadoCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFf1f5f9' } };
                    estadoCell.font.color = { argb: 'FF475569' };
                    break;
            }

            // Estilo para fecha
            row.getCell(6).alignment = { horizontal: 'center', vertical: 'middle' };
            row.getCell(6).font = { name: 'Calibri', size: 10, color: { argb: 'FF64748b' } };

            // Estilo para firmas
            const firmasCell = row.getCell(7);
            firmasCell.alignment = { horizontal: 'center', vertical: 'middle' };
            if (doc.firmas_completas) {
                firmasCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFd1fae5' } };
                firmasCell.font = { name: 'Calibri', size: 10, bold: true, color: { argb: 'FF065f46' } };
            } else if (doc.firmas !== 'Sin firmas') {
                firmasCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFfef3c7' } };
                firmasCell.font = { name: 'Calibri', size: 10, bold: true, color: { argb: 'FF92400e' } };
            }

            row.height = 22;
            rowIndex++;
        });

        // ========================================
        // PIE DE TABLA CON RESUMEN
        // ========================================
        rowIndex++;
        worksheet.mergeCells(`A${rowIndex}:G${rowIndex}`);
        const footerCell = worksheet.getCell(`A${rowIndex}`);
        footerCell.value = `📌 Este documento ha sido generado automáticamente por Enterprise SST - Sistema de Gestión de Seguridad y Salud en el Trabajo`;
        footerCell.font = { name: 'Calibri', size: 9, italic: true, color: { argb: 'FF64748b' } };
        footerCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFf8fafc' } };
        footerCell.alignment = { horizontal: 'center', vertical: 'middle' };
        footerCell.border = {
            top: { style: 'medium', color: { argb: 'FF1e3a5f' } }
        };
        worksheet.getRow(rowIndex).height = 25;

        // ========================================
        // HOJA DE ESTADÍSTICAS
        // ========================================
        const statsSheet = workbook.addWorksheet('Estadísticas', {
            properties: { tabColor: { argb: '059669' } }
        });

        statsSheet.columns = [
            { key: 'indicador', width: 35 },
            { key: 'valor', width: 15 },
            { key: 'porcentaje', width: 15 }
        ];

        // Título estadísticas
        statsSheet.mergeCells('A1:C1');
        const statsTitleCell = statsSheet.getCell('A1');
        statsTitleCell.value = '📊 ESTADÍSTICAS DE DOCUMENTACIÓN SG-SST';
        statsTitleCell.font = { name: 'Calibri', size: 14, bold: true, color: { argb: 'FFFFFFFF' } };
        statsTitleCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF059669' } };
        statsTitleCell.alignment = { horizontal: 'center', vertical: 'middle' };
        statsSheet.getRow(1).height = 30;

        // Info cliente
        statsSheet.mergeCells('A2:C2');
        statsSheet.getCell('A2').value = `${nombreCliente} - Generado: ${fechaHoraExport}`;
        statsSheet.getCell('A2').font = { name: 'Calibri', size: 11, color: { argb: 'FF1e293b' } };
        statsSheet.getCell('A2').fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFe2e8f0' } };
        statsSheet.getCell('A2').alignment = { horizontal: 'center', vertical: 'middle' };

        // Headers
        const statsHeaderRow = statsSheet.getRow(4);
        ['INDICADOR', 'CANTIDAD', 'PORCENTAJE'].forEach((h, i) => {
            const cell = statsHeaderRow.getCell(i + 1);
            cell.value = h;
            cell.font = { name: 'Calibri', size: 11, bold: true, color: { argb: 'FFFFFFFF' } };
            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1e3a5f' } };
            cell.alignment = { horizontal: 'center', vertical: 'middle' };
            cell.border = {
                top: { style: 'medium', color: { argb: 'FF0f172a' } },
                bottom: { style: 'medium', color: { argb: 'FF0f172a' } }
            };
        });

        // Datos estadísticos
        const estadisticas = [
            { indicador: '📄 Total de Documentos', valor: totalDocs, color: 'FF1e3a5f' },
            { indicador: '✅ Documentos Firmados', valor: firmados, color: 'FF059669' },
            { indicador: '📝 Documentos Aprobados', valor: aprobados, color: 'FF2563eb' },
            { indicador: '⏳ Pendientes de Firma', valor: pendientes, color: 'FF0891b2' },
            { indicador: '📋 Borradores', valor: documentosData.filter(d => d.estado === 'Borrador').length, color: 'FFd97706' },
            { indicador: '🔄 Generados', valor: documentosData.filter(d => d.estado === 'Generado').length, color: 'FF64748b' }
        ];

        estadisticas.forEach((stat, index) => {
            const row = statsSheet.getRow(5 + index);
            row.getCell(1).value = stat.indicador;
            row.getCell(2).value = stat.valor;
            row.getCell(3).value = totalDocs > 0 ? ((stat.valor / totalDocs) * 100).toFixed(1) + '%' : '0%';

            row.eachCell((cell, colNumber) => {
                cell.font = { name: 'Calibri', size: 11 };
                cell.alignment = { horizontal: colNumber === 1 ? 'left' : 'center', vertical: 'middle' };
                cell.border = {
                    bottom: { style: 'thin', color: { argb: 'FFe2e8f0' } }
                };
                if (colNumber === 2) {
                    cell.font = { name: 'Calibri', size: 12, bold: true, color: { argb: stat.color } };
                }
            });
            row.height = 25;
        });

        // ========================================
        // APLICAR FILTROS AUTOMÁTICOS
        // ========================================
        worksheet.autoFilter = {
            from: { row: 5, column: 1 },
            to: { row: rowIndex - 1, column: 7 }
        };

        // ========================================
        // CONGELAR PANELES
        // ========================================
        worksheet.views = [
            { state: 'frozen', xSplit: 0, ySplit: 5, activeCell: 'A6' }
        ];

        // ========================================
        // GENERAR Y DESCARGAR
        // ========================================
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const fileName = `Control_Documental_SGSST_${nombreCliente.replace(/[^a-z0-9]/gi, '_')}_${fechaExport.replace(/\//g, '-')}.xlsx`;

        saveAs(blob, fileName);

        // Notificación de éxito
        mostrarToast('success', '¡Excel generado exitosamente!', `Se ha descargado: ${fileName}`);
    }

    // Sistema de Toast estandar (ZZ_91)
    function mostrarToast(tipo, titulo, mensaje) {
        const iconos = {
            'success': 'bi-check-circle-fill',
            'error': 'bi-x-circle-fill',
            'warning': 'bi-exclamation-triangle-fill',
            'info': 'bi-info-circle-fill'
        };
        const colores = {
            'success': 'bg-success',
            'error': 'bg-danger',
            'warning': 'bg-warning',
            'info': 'bg-info'
        };
        const duraciones = {
            'error': 8000,
            'success': 6000,
            'warning': 6000
        };

        const toastId = `toast-${Date.now()}-${Math.random().toString(36).substr(2, 5)}`;
        const ahora = new Date();
        const timestamp = ahora.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const duracion = duraciones[tipo] || 5000;

        const toastEl = document.createElement('div');
        toastEl.id = toastId;
        toastEl.className = 'toast';
        toastEl.setAttribute('role', 'alert');
        toastEl.style.minWidth = '300px';
        toastEl.style.boxShadow = '0 4px 12px rgba(0,0,0,.15)';
        toastEl.style.marginBottom = '8px';
        toastEl.innerHTML = `
            <div class="toast-header ${colores[tipo] || 'bg-info'} text-white">
                <i class="bi ${iconos[tipo] || 'bi-info-circle-fill'} me-2"></i>
                <strong class="me-auto">${titulo}</strong>
                <small>${timestamp}</small>
                <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${mensaje}</div>
        `;

        let container = document.getElementById('toastStack');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastStack';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        container.appendChild(toastEl);

        const bsToast = new bootstrap.Toast(toastEl, { delay: duracion });
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        bsToast.show();

        return { id: toastId, element: toastEl, instance: bsToast };
    }

    function cerrarToast(ref) {
        if (ref && ref.instance) ref.instance.hide();
    }

    // ========================================
    // CONFIGURACIÓN DATATABLES
    // ========================================
    const table = $('#' + tableId).DataTable({
        dom: '<"top"<"row"<"col-md-6"l><"col-md-6"f>>>rt<"bottom"<"row"<"col-md-5"i><"col-md-7"p>>>',
        orderCellsTop: true,
        fixedHeader: true,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>',
            lengthMenu: 'Mostrar <select class="form-select form-select-sm d-inline-block w-auto">' +
                '<option value="10">10</option>' +
                '<option value="25">25</option>' +
                '<option value="50">50</option>' +
                '<option value="100">100</option>' +
                '<option value="-1">Todos</option>' +
                '</select> registros',
            zeroRecords: '<div class="text-center py-4"><i class="bi bi-search fs-1 text-muted"></i><p class="mt-2">No se encontraron documentos</p></div>',
            info: 'Mostrando <strong>_START_</strong> a <strong>_END_</strong> de <strong>_TOTAL_</strong> documentos',
            infoEmpty: 'Sin documentos disponibles',
            infoFiltered: '(filtrado de _MAX_ totales)',
            search: '',
            searchPlaceholder: 'Buscar...',
            paginate: {
                first: '<i class="bi bi-chevron-double-left"></i>',
                last: '<i class="bi bi-chevron-double-right"></i>',
                next: '<i class="bi bi-chevron-right"></i>',
                previous: '<i class="bi bi-chevron-left"></i>'
            }
        },
        order: [[5, 'desc']],
        pageLength: 25,
        responsive: true,
        stateSave: true,
        columnDefs: [
            { targets: [7], orderable: false, searchable: false }
        ],
        initComplete: function() {
            // Obtener referencia a la API de DataTables
            const api = this.api();

            // Toggle de columnas
            $('#exportButtons_' + tableId + ' .col-toggle').on('change', function() {
                const column = api.column($(this).data('column'));
                column.visible($(this).is(':checked'));
            });

            // Ocultar búsqueda nativa
            $('#' + tableId + '_filter').hide();

            // Conectar búsqueda personalizada
            $('#searchInput_' + tableId).on('keyup', function() {
                api.search(this.value).draw();
            });

            // Configurar filtros de columna
            api.columns().every(function(index) {
                const column = this;
                const filterCell = $('#' + tableId + ' thead tr.filters-row th').eq(index);

                if (index === 2 || index === 3) {
                    const select = filterCell.find('select');
                    const uniqueValues = [];

                    column.data().each(function(d) {
                        const temp = document.createElement('div');
                        temp.innerHTML = d;
                        const text = temp.textContent || temp.innerText || '';
                        if (text && !uniqueValues.includes(text.trim())) {
                            uniqueValues.push(text.trim());
                        }
                    });

                    uniqueValues.sort().forEach(function(val) {
                        select.append('<option value="' + val + '">' + val + '</option>');
                    });
                }

                filterCell.find('input').on('keyup change', function() {
                    if (column.search() !== this.value) {
                        column.search(this.value).draw();
                    }
                });

                filterCell.find('select').on('change', function() {
                    const val = $.fn.dataTable.util.escapeRegex($(this).val());
                    column.search(val ? val : '', false, false).draw();
                });
            });

            // Filtro especial para firmas
            $('#' + tableId + ' thead tr.filters-row th').eq(6).find('select').off('change').on('change', function() {
                const val = $(this).val();
                if (val === '') {
                    $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(fn => fn.name !== 'firmasFilter');
                    api.draw();
                } else {
                    $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(fn => fn.name !== 'firmasFilter');
                    const firmasFilter = function(settings, data, dataIndex) {
                        if (settings.nTable.id !== tableId) return true;
                        const row = api.row(dataIndex).node();
                        return $(row).data('firmas-status') === val;
                    };
                    Object.defineProperty(firmasFilter, 'name', { value: 'firmasFilter' });
                    $.fn.dataTable.ext.search.push(firmasFilter);
                    api.draw();
                }
            });

            // Actualizar contador
            api.on('draw', function() {
                $('#totalDocumentos').text(api.page.info().recordsDisplay);
            });

            // Manejar click en botón de versiones (child rows de DataTables)
            $('#' + tableId + ' tbody').on('click', '.btn-versiones', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const tr = $(this).closest('tr');
                const row = api.row(tr);
                const versionesHtml = tr.data('versiones');

                if (!versionesHtml) return;

                if (row.child.isShown()) {
                    // Cerrar child row
                    row.child.hide();
                    tr.removeClass('shown');
                    $(this).find('i').removeClass('bi-clock-history-fill').addClass('bi-clock-history');
                } else {
                    // Abrir child row
                    row.child('<div class="versiones-container p-3" style="border-left: 4px solid #3b82f6; background: #f8fafc;">' + versionesHtml + '</div>').show();
                    tr.addClass('shown');
                    $(this).find('i').removeClass('bi-clock-history').addClass('bi-clock-history-fill');
                }
            });
        }
    });

    // Animación de entrada
    $('#' + tableId + ' tbody tr').each(function(i) {
        $(this).css('animation-delay', (i * 0.03) + 's');
    });

    // ========================================
    // EVENTO DEL BOTÓN EXPORTAR EXCEL
    // ========================================
    const btnExcel = document.getElementById('btnExportExcel_' + tableId);
    if (btnExcel) {
        btnExcel.addEventListener('click', async function(e) {
            e.preventDefault();
            const btn = this;

            // Verificar que ExcelJS esté cargado
            if (typeof ExcelJS === 'undefined') {
                alert('Error: La librería ExcelJS no se ha cargado correctamente. Recargue la página.');
                console.error('ExcelJS no está definido');
                return;
            }

            // Verificar que saveAs esté disponible
            if (typeof saveAs === 'undefined') {
                alert('Error: La librería FileSaver no se ha cargado correctamente. Recargue la página.');
                console.error('FileSaver no está definido');
                return;
            }

            btn.disabled = true;
            btn.querySelector('span').textContent = 'Generando...';
            btn.querySelector('i').className = 'bi bi-hourglass-split spin-animation me-2';

            try {
                await exportarExcelAvanzado();
                btn.disabled = false;
                btn.querySelector('span').textContent = 'Exportar Excel';
                btn.querySelector('i').className = 'bi bi-file-earmark-spreadsheet me-2';
            } catch (err) {
                console.error('Error al exportar:', err);
                alert('Error al exportar: ' + err.message);
                btn.disabled = false;
                btn.querySelector('span').textContent = 'Exportar Excel';
                btn.querySelector('i').className = 'bi bi-file-earmark-spreadsheet me-2';
                mostrarToast('error', 'Error al exportar', err.message || 'No se pudo generar el archivo Excel.');
            }
        });
    } else {
        console.error('No se encontró el botón de exportar con ID: btnExportExcel_' + tableId);
    }
});
</script>

<!-- Estilos específicos para esta tabla -->
<style>
/* Card principal */
.tabla-documentos-sst-card {
    border-radius: 16px;
    overflow: hidden;
    border: none;
}

/* Headers con gradiente */
.header-archivo-documental {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 50%, #3b82f6 100%);
    padding: 20px 24px;
    border: none;
}

.header-documentos-sst {
    background: linear-gradient(135deg, #065f46 0%, #059669 50%, #10b981 100%);
    padding: 20px 24px;
    border: none;
}

.icon-wrapper {
    width: 48px;
    height: 48px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.header-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: white;
}

.header-subtitle {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.8);
    margin-top: 2px;
}

.stat-badge {
    background: rgba(255,255,255,0.2);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    color: white;
    font-weight: 500;
}

/* Barra de herramientas */
.toolbar-container {
    padding: 16px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.search-box {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 1rem;
}

.search-input {
    padding-left: 42px;
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    transition: all 0.3s;
}

.search-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Botones de exportación */
.btn-export-excel {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-export-excel:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.btn-colvis {
    border-radius: 10px;
    padding: 10px 20px;
}

/* Tabla container */
.tabla-container {
    padding: 0;
}

/* Header de la tabla */
.tabla-documentos-moderna thead .header-row th {
    background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #475569;
    padding: 14px 12px;
    border: none;
    white-space: nowrap;
}

/* Fila de filtros */
.tabla-documentos-moderna thead .filters-row th {
    background: #f8fafc;
    padding: 8px;
    border-bottom: 2px solid #e2e8f0;
}

.filter-input, .filter-select {
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    font-size: 0.8rem;
}

.filter-input:focus, .filter-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

/* Celdas de la tabla */
.tabla-documentos-moderna tbody tr {
    animation: fadeInUp 0.4s ease forwards;
    opacity: 0;
    transition: all 0.2s;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.tabla-documentos-moderna tbody tr:hover {
    background: #f0f9ff;
}

.tabla-documentos-moderna tbody td {
    padding: 14px 12px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

/* Estilos de celdas específicas */
.codigo-badge {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: white;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.nombre-documento {
    font-weight: 500;
    color: #1e293b;
}

.btn-versiones {
    color: #64748b;
    text-decoration: none;
}

.btn-versiones:hover {
    color: #3b82f6;
}

.anio-badge {
    background: #e2e8f0;
    color: #475569;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
}

.version-badge {
    background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
    color: white;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Estados modernos */
.estado-badge-moderno {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.estado-firmado {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: white;
}

.estado-pendiente-firma {
    background: linear-gradient(135deg, #0891b2 0%, #22d3ee 100%);
    color: white;
}

.estado-aprobado {
    background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
    color: white;
}

.estado-borrador {
    background: linear-gradient(135deg, #d97706 0%, #fbbf24 100%);
    color: #1e293b;
}

.estado-generado {
    background: linear-gradient(135deg, #64748b 0%, #94a3b8 100%);
    color: white;
}

.estado-default {
    background: #e2e8f0;
    color: #475569;
}

/* Celda de fecha */
.fecha-cell {
    font-size: 0.85rem;
    color: #475569;
}

/* Celda de firmas */
.firmas-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}

.progress-circle {
    font-size: 0.8rem;
    font-weight: 600;
}

.progress-circle.completo {
    color: #059669;
}

.progress-circle.pendiente {
    color: #d97706;
}

/* Celda de acciones */
.acciones-cell .btn-group .btn {
    border-radius: 8px;
    margin: 0 2px;
}

/* Versiones expandidas - Child rows de DataTables */
.tabla-documentos-moderna tbody tr.shown {
    background: #eff6ff !important;
}

.tabla-documentos-moderna tbody tr.shown td {
    border-bottom-color: #3b82f6;
}

.tabla-documentos-moderna tbody tr.shown .btn-versiones {
    color: #3b82f6;
}

.versiones-container {
    border-left: 4px solid #3b82f6;
    background: #f8fafc;
}

/* Estado vacío */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #e2e8f0 0%, #f1f5f9 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.empty-icon i {
    font-size: 2.5rem;
    color: #94a3b8;
}

.empty-state h5 {
    color: #475569;
    margin-bottom: 8px;
}

.empty-state p {
    color: #94a3b8;
    max-width: 300px;
    margin: 0 auto;
}

/* DataTables overrides */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 0;
}

.dataTables_wrapper .dataTables_info {
    padding-top: 16px;
    color: #64748b;
}

.dataTables_wrapper .dataTables_paginate {
    padding-top: 16px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius: 8px !important;
    margin: 0 2px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%) !important;
    border-color: transparent !important;
    color: white !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
    color: #1e293b !important;
}

/* Responsive */
@media (max-width: 768px) {
    .toolbar-container .row {
        flex-direction: column;
    }

    #exportButtons_<?= $tableId ?> {
        justify-content: flex-start !important;
        margin-top: 12px;
    }

    .header-stats {
        display: none;
    }
}

/* ========================================
   ESTILOS PARA BOTÓN DE EXPORTACIÓN
   ======================================== */
.btn-export-excel {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    border: none;
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.btn-export-excel:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
    background: linear-gradient(135deg, #047857 0%, #059669 100%);
}

.btn-export-excel:active {
    transform: translateY(-1px);
}

.btn-export-excel:disabled {
    opacity: 0.8;
    cursor: wait;
}

/* Animación de spinner */
.spin-animation {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* ========================================
   TOAST STACK ESTANDAR (ZZ_91)
   ======================================== */
.toast-container {
    z-index: 9999;
}
.toast {
    min-width: 300px;
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
    margin-bottom: 8px;
}

/* Dropdown de columnas */
.dropdown-menu {
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border: 1px solid #e2e8f0;
    padding: 8px;
}

.dropdown-item {
    border-radius: 8px;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: all 0.2s;
}

.dropdown-item:hover {
    background: #f1f5f9;
}

.dropdown-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #3b82f6;
}

/* Progress bar mejorado */
.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.6s ease;
}

/* Ajustes adicionales para mejor visualización */
.tabla-documentos-moderna th,
.tabla-documentos-moderna td {
    white-space: nowrap;
}

.tabla-documentos-moderna td:nth-child(2) {
    white-space: normal;
    min-width: 250px;
}
</style>
