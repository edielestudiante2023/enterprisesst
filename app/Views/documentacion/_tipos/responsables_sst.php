<?php
/**
 * Vista de Tipo: 1.1.1 Responsables SST
 * Carpeta para asignación de responsable del SG-SST
 * Variables: $carpeta, $cliente, $fasesInfo, $documentosSSTAprobados, $documentoExistente
 */

// Verificar si hay documento para el año actual
$hayAprobadoAnioActual = false;
if (!empty($documentosSSTAprobados)) {
    foreach ($documentosSSTAprobados as $d) {
        if ($d['anio'] == date('Y')) {
            $hayAprobadoAnioActual = true;
            break;
        }
    }
}
?>

<!-- Card de Carpeta con Botón Específico -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="bi bi-folder-fill text-warning me-2"></i>
                    <?= esc($carpeta['nombre']) ?>
                </h4>
                <?php if (!empty($carpeta['codigo'])): ?>
                    <span class="badge bg-light text-dark me-2"><?= esc($carpeta['codigo']) ?></span>
                <?php endif; ?>
                <?php if (!empty($carpeta['descripcion'])): ?>
                    <p class="text-muted mb-0 mt-1"><?= esc($carpeta['descripcion']) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <?php if (isset($fasesInfo) && $fasesInfo && !$fasesInfo['puede_generar_documento']): ?>
                    <button type="button" class="btn btn-secondary" disabled title="Complete las fases previas">
                        <i class="bi bi-lock me-1"></i>Generar Asignación
                    </button>
                <?php elseif (!$hayAprobadoAnioActual): ?>
                    <form action="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/crear-asignacion-responsable-sst') ?>" method="post" style="display:inline;">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-file-earmark-plus me-1"></i>Generar Asignación <?= date('Y') ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Panel de Fases -->
<?= view('documentacion/_components/panel_fases', [
    'fasesInfo' => $fasesInfo ?? null,
    'tipoCarpetaFases' => 'responsables_sst',
    'cliente' => $cliente,
    'carpeta' => $carpeta,
    'documentoExistente' => $documentoExistente ?? null
]) ?>

<!-- Tabla de Documentos SST (local - cumple instructivo 6_AA) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-success text-white">
        <h6 class="mb-0">
            <i class="bi bi-file-earmark-check me-2"></i>Documentos SST
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 120px;">Código</th>
                        <th>Nombre</th>
                        <th style="width: 80px;">Año</th>
                        <th style="width: 80px;">Versión</th>
                        <th style="width: 110px;">Estado</th>
                        <th style="width: 150px;">Fecha Aprobación</th>
                        <th style="width: 110px;">Firmas</th>
                        <th style="width: 180px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($documentosSSTAprobados)): ?>
                        <?php foreach ($documentosSSTAprobados as $docSST): ?>
                            <?php
                            $estadoDoc = $docSST['estado'] ?? 'aprobado';
                            $estadoConfig = match($estadoDoc) {
                                'firmado' => ['bg-success', 'bi-patch-check-fill', 'Firmado'],
                                'pendiente_firma' => ['bg-info', 'bi-pen', 'Pendiente firma'],
                                'aprobado' => ['bg-primary', 'bi-check-circle', 'Aprobado'],
                                'borrador' => ['bg-warning text-dark', 'bi-pencil-square', 'Borrador'],
                                'generado' => ['bg-secondary', 'bi-file-earmark-text', 'Generado'],
                                default => ['bg-secondary', 'bi-circle', ucfirst(str_replace('_', ' ', $estadoDoc))]
                            };
                            ?>
                            <tr>
                                <td><code><?= esc($docSST['codigo'] ?? 'N/A') ?></code></td>
                                <td>
                                    <strong><?= esc($docSST['titulo']) ?></strong>
                                    <?php if (!empty($docSST['versiones']) && count($docSST['versiones']) > 0): ?>
                                        <button class="btn btn-sm btn-link p-0 ms-2" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#versiones_<?= $docSST['id_documento'] ?>">
                                            <i class="bi bi-clock-history me-1"></i>
                                            <span class="badge bg-light text-dark"><?= count($docSST['versiones']) ?></span>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-light text-dark"><?= esc($docSST['anio']) ?></span></td>
                                <td><span class="badge bg-info text-white">v<?= esc($docSST['version_texto'] ?? $docSST['version'] . '.0') ?></span></td>
                                <td>
                                    <span class="badge <?= $estadoConfig[0] ?>">
                                        <i class="bi <?= $estadoConfig[1] ?> me-1"></i><?= $estadoConfig[2] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($docSST['fecha_aprobacion'])): ?>
                                        <?= date('d/m/Y H:i', strtotime($docSST['fecha_aprobacion'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($docSST['firmas_total'] > 0): ?>
                                        <span class="badge <?= $docSST['firmas_firmadas'] == $docSST['firmas_total'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                                            <?= $docSST['firmas_firmadas'] ?>/<?= $docSST['firmas_total'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= view('documentacion/_components/acciones_documento', [
                                        'docSST' => $docSST,
                                        'cliente' => $cliente
                                    ]) ?>
                                </td>
                            </tr>
                            <?php if (!empty($docSST['versiones'])): ?>
                                <tr class="collapse" id="versiones_<?= $docSST['id_documento'] ?>">
                                    <td colspan="8" class="p-0">
                                        <?= view('documentacion/_components/historial_versiones', ['versiones' => $docSST['versiones']]) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-folder2-open text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2 mb-0">No hay documentos aprobados o firmados aún.</p>
                                <small class="text-muted">Complete las fases y apruebe el documento para verlo aquí.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Subcarpetas -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
</div>
