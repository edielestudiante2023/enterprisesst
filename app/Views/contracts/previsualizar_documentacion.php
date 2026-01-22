<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentación del Contrato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .header-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .info-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .file-item {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s;
        }
        .file-item:hover { background-color: #f8f9fa; }
        .file-item:last-child { border-bottom: none; }
        .file-icon { width: 40px; text-align: center; }
        .file-missing { opacity: 0.5; }
        .stats-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 15px;
        }
        .stats-box h3 { margin: 0; font-size: 2rem; }
        .stats-box small { opacity: 0.9; }
        .btn-excel {
            background: linear-gradient(135deg, #1d6f42 0%, #28a745 100%);
            color: white;
            border: none;
        }
        .btn-excel:hover {
            background: linear-gradient(135deg, #155d36 0%, #1e7e34 100%);
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4">
        <div class="container-fluid">
            <?php if (isset($fromReportList) && $fromReportList): ?>
                <a class="navbar-brand" href="<?= base_url('/contracts/seleccionar-documentacion/' . $contract['id_cliente']) ?>">
                    <i class="fas fa-arrow-left"></i> Cambiar Filtro
                </a>
                <a class="navbar-brand ms-3" href="<?= base_url('/reportList') ?>">
                    <i class="fas fa-list"></i> Ir a Lista de Reportes
                </a>
            <?php else: ?>
                <a class="navbar-brand" href="<?= base_url('/contracts/view/' . $contract['id_contrato']) ?>">
                    <i class="fas fa-arrow-left"></i> Volver al Contrato
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('warning')): ?>
            <div class="alert alert-warning"><?= session()->getFlashdata('warning') ?></div>
        <?php endif; ?>

        <!-- Header -->
        <div class="header-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-folder-open"></i> Documentación del Contrato</h2>
                    <p class="mb-1"><strong><?= htmlspecialchars($contract['nombre_cliente']) ?></strong></p>
                    <p class="mb-0">
                        <i class="fas fa-file-contract"></i> <?= htmlspecialchars($contract['numero_contrato']) ?> |
                        <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($contract['fecha_inicio'])) ?> - <?= date('d/m/Y', strtotime($contract['fecha_fin'])) ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <?php if ($archivosExistentes > 0): ?>
                        <?php if (isset($fromReportList) && $fromReportList && isset($filtroTipo)): ?>
                            <?php
                                $downloadParams = ['filtro_tipo' => $filtroTipo];
                                if ($filtroTipo === 'contrato' && isset($contract['id_contrato'])) {
                                    $downloadParams['id_contrato'] = $contract['id_contrato'];
                                } elseif ($filtroTipo === 'fechas') {
                                    $downloadParams['fecha_desde'] = $fechaInicio;
                                    $downloadParams['fecha_hasta'] = $fechaFin;
                                }
                            ?>
                            <a href="<?= base_url('/contracts/descargar-filtrado/' . $contract['id_cliente']) ?>?<?= http_build_query($downloadParams) ?>"
                               class="btn btn-light btn-lg">
                                <i class="fas fa-download"></i> Descargar ZIP
                            </a>
                        <?php elseif (isset($fromReportList) && $fromReportList): ?>
                            <a href="<?= base_url('/contracts/descargar-documentacion-cliente/' . $contract['id_cliente']) ?>"
                               class="btn btn-light btn-lg">
                                <i class="fas fa-download"></i> Descargar ZIP
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('/contracts/descargar-documentacion/' . $contract['id_contrato']) ?>"
                               class="btn btn-light btn-lg">
                                <i class="fas fa-download"></i> Descargar ZIP
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Estadísticas -->
            <div class="col-md-4">
                <div class="stats-box">
                    <h3><?= $totalReportes ?></h3>
                    <small>Documentos en el período</small>
                </div>
                <div class="stats-box" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <h3><?= $archivosExistentes ?></h3>
                    <small>Archivos disponibles</small>
                </div>
                <div class="stats-box" style="background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);">
                    <h3><?= number_format($tamanoTotal / 1024 / 1024, 2) ?> MB</h3>
                    <small>Tamaño total</small>
                </div>

                <div class="info-card">
                    <h6><i class="fas fa-info-circle"></i> Información</h6>
                    <hr>
                    <p class="small mb-1"><strong>NIT:</strong> <?= htmlspecialchars($contract['nit_cliente']) ?></p>
                    <p class="small mb-1"><strong>Período:</strong></p>
                    <p class="small mb-0">
                        <?= date('d/m/Y', strtotime($contract['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($contract['fecha_fin'])) ?>
                    </p>
                </div>
            </div>

            <!-- Lista de archivos -->
            <div class="col-md-8">
                <div class="info-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-file-pdf"></i> Documentos del Contrato</h5>
                        <?php if (!empty($archivos)): ?>
                            <button type="button" id="btnExportExcel" class="btn btn-excel">
                                <i class="fas fa-file-excel"></i> Exportar a Excel
                            </button>
                        <?php endif; ?>
                    </div>
                    <hr>

                    <?php if (empty($archivos)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No se encontraron documentos cargados en el período de este contrato.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="tablaDocumentos">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50"></th>
                                        <th>Documento</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($archivos as $archivo): ?>
                                        <tr class="<?= !$archivo['existe'] ? 'file-missing' : '' ?>">
                                            <td class="file-icon">
                                                <?php if ($archivo['existe']): ?>
                                                    <i class="fas fa-file-pdf text-danger fa-lg"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-file-excel text-secondary fa-lg"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($archivo['reporte']['titulo_reporte']) ?></strong>
                                                <?php if ($archivo['existe']): ?>
                                                    <br><small class="text-muted"><?= number_format($archivo['tamano'] / 1024, 1) ?> KB</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars($archivo['reporte']['detail_report'] ?? 'N/A') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= date('d/m/Y', strtotime($archivo['reporte']['created_at'])) ?>
                                            </td>
                                            <td>
                                                <?php if ($archivo['existe']): ?>
                                                    <span class="badge bg-success"><i class="fas fa-check"></i> Disponible</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning"><i class="fas fa-exclamation-triangle"></i> No encontrado</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($archivosExistentes > 0): ?>
                    <div class="text-center">
                        <?php if (isset($fromReportList) && $fromReportList && isset($filtroTipo)): ?>
                            <?php
                                $downloadParams = ['filtro_tipo' => $filtroTipo];
                                if ($filtroTipo === 'contrato' && isset($contract['id_contrato'])) {
                                    $downloadParams['id_contrato'] = $contract['id_contrato'];
                                } elseif ($filtroTipo === 'fechas') {
                                    $downloadParams['fecha_desde'] = $fechaInicio;
                                    $downloadParams['fecha_hasta'] = $fechaFin;
                                }
                            ?>
                            <a href="<?= base_url('/contracts/descargar-filtrado/' . $contract['id_cliente']) ?>?<?= http_build_query($downloadParams) ?>"
                               class="btn btn-success btn-lg me-2">
                                <i class="fas fa-download"></i> Descargar <?= $archivosExistentes ?> archivo(s) en ZIP
                            </a>
                        <?php elseif (isset($fromReportList) && $fromReportList): ?>
                            <a href="<?= base_url('/contracts/descargar-documentacion-cliente/' . $contract['id_cliente']) ?>"
                               class="btn btn-success btn-lg me-2">
                                <i class="fas fa-download"></i> Descargar <?= $archivosExistentes ?> archivo(s) en ZIP
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('/contracts/descargar-documentacion/' . $contract['id_contrato']) ?>"
                               class="btn btn-success btn-lg me-2">
                                <i class="fas fa-download"></i> Descargar <?= $archivosExistentes ?> archivo(s) en ZIP
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
    document.getElementById('btnExportExcel')?.addEventListener('click', function() {
        // Datos del cliente y contrato
        const clienteNombre = <?= json_encode($contract['nombre_cliente']) ?>;
        const clienteNit = <?= json_encode($contract['nit_cliente']) ?>;
        const numeroContrato = <?= json_encode($contract['numero_contrato']) ?>;
        const fechaInicio = <?= json_encode(date('d/m/Y', strtotime($contract['fecha_inicio']))) ?>;
        const fechaFin = <?= json_encode(date('d/m/Y', strtotime($contract['fecha_fin']))) ?>;

        // Crear datos para el Excel
        const datosExcel = [];

        // Encabezado con información del cliente
        datosExcel.push(['DOCUMENTACIÓN ENTREGADA - SG-SST']);
        datosExcel.push([]);
        datosExcel.push(['Cliente:', clienteNombre]);
        datosExcel.push(['NIT:', clienteNit]);
        datosExcel.push(['Contrato:', numeroContrato]);
        datosExcel.push(['Período:', fechaInicio + ' al ' + fechaFin]);
        datosExcel.push(['Fecha de Generación:', new Date().toLocaleDateString('es-CO')]);
        datosExcel.push([]);
        datosExcel.push([]);

        // Encabezados de la tabla
        datosExcel.push(['No.', 'DOCUMENTO', 'TIPO', 'FECHA', 'ESTADO']);

        // Datos de los documentos
        <?php $contador = 1; ?>
        <?php foreach ($archivos as $archivo): ?>
        datosExcel.push([
            <?= $contador++ ?>,
            <?= json_encode($archivo['reporte']['titulo_reporte']) ?>,
            <?= json_encode($archivo['reporte']['detail_report'] ?? 'N/A') ?>,
            <?= json_encode(date('d/m/Y', strtotime($archivo['reporte']['created_at']))) ?>,
            <?= json_encode($archivo['existe'] ? 'Disponible' : 'No encontrado') ?>
        ]);
        <?php endforeach; ?>

        // Resumen al final
        datosExcel.push([]);
        datosExcel.push(['', '', '', 'Total Documentos:', <?= $totalReportes ?>]);
        datosExcel.push(['', '', '', 'Archivos Disponibles:', <?= $archivosExistentes ?>]);

        // Crear libro de Excel
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(datosExcel);

        // Ajustar ancho de columnas
        ws['!cols'] = [
            { wch: 5 },   // No.
            { wch: 50 },  // Documento
            { wch: 20 },  // Tipo
            { wch: 12 },  // Fecha
            { wch: 15 }   // Estado
        ];

        // Combinar celdas del título
        ws['!merges'] = [
            { s: { r: 0, c: 0 }, e: { r: 0, c: 4 } }  // Título
        ];

        XLSX.utils.book_append_sheet(wb, ws, 'Documentación');

        // Generar nombre del archivo
        const nombreArchivo = 'Documentacion_' + clienteNombre.replace(/[^a-zA-Z0-9]/g, '_') + '_' + numeroContrato + '.xlsx';

        // Descargar
        XLSX.writeFile(wb, nombreArchivo);
    });
    </script>
</body>
</html>
