<?php
/**
 * Componente: Tabla de Documentos (Genérico)
 * Variables requeridas: $documentosSSTAprobados
 * Variables opcionales: $cliente, $titulo
 *
 * Componente reutilizable para mostrar documentos SST en cualquier vista de tipo.
 */
$titulo = $titulo ?? 'Documentos Generados';
?>

<?php if (!empty($documentosSSTAprobados)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-success text-white">
        <h6 class="mb-0">
            <i class="bi bi-file-earmark-check me-2"></i><?= esc($titulo) ?>
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 120px;">Codigo</th>
                        <th>Nombre</th>
                        <th style="width: 80px;">Ano</th>
                        <th style="width: 80px;">Version</th>
                        <th style="width: 110px;">Estado</th>
                        <th style="width: 150px;">Fecha Aprobacion</th>
                        <th style="width: 110px;">Firmas</th>
                        <th style="width: 180px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documentosSSTAprobados as $docSST): ?>
                        <?php
                        $estadoDoc = $docSST['estado'] ?? 'aprobado';
                        $estadoBadge = match($estadoDoc) {
                            'firmado' => 'bg-success',
                            'pendiente_firma' => 'bg-info',
                            'aprobado' => 'bg-primary',
                            'borrador' => 'bg-warning text-dark',
                            'generado' => 'bg-secondary',
                            default => 'bg-secondary'
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
                            'pendiente_firma' => 'bi-pen',
                            'aprobado' => 'bi-check-circle',
                            'borrador' => 'bi-pencil-square',
                            'generado' => 'bi-file-earmark-text',
                            default => 'bi-circle'
                        };
                        ?>
                        <tr>
                            <td><code><?= esc($docSST['codigo'] ?? 'N/A') ?></code></td>
                            <td>
                                <strong><?= esc($docSST['titulo']) ?></strong>
                                <?php if (!empty($docSST['versiones']) && count($docSST['versiones']) > 0): ?>
                                    <button class="btn btn-sm btn-link p-0 ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#versiones-<?= $docSST['id_documento'] ?>">
                                        <i class="bi bi-clock-history me-1"></i><?= count($docSST['versiones']) ?> versiones
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= esc($docSST['anio']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-primary">v<?= esc($docSST['version_texto'] ?? $docSST['version'] . '.0') ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $estadoBadge ?>">
                                    <i class="bi <?= $estadoIcono ?> me-1"></i><?= $estadoTexto ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($docSST['fecha_aprobacion'])): ?>
                                    <small><?= date('d/m/Y H:i', strtotime($docSST['fecha_aprobacion'])) ?></small>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($docSST['firmas_total']) && $docSST['firmas_total'] > 0): ?>
                                    <span class="badge <?= $docSST['firmas_firmadas'] == $docSST['firmas_total'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                                        <?= $docSST['firmas_firmadas'] ?>/<?= $docSST['firmas_total'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">Sin firmas</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($cliente)): ?>
                                    <?= view('documentacion/_components/acciones_documento', [
                                        'docSST' => $docSST,
                                        'cliente' => $cliente
                                    ]) ?>
                                <?php else: ?>
                                    <a href="<?= base_url('documentos/ver/' . $docSST['id_documento']) ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Ver documento">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (!empty($docSST['versiones'])): ?>
                        <tr class="collapse" id="versiones-<?= $docSST['id_documento'] ?>">
                            <td colspan="8" class="p-0">
                                <?= view('documentacion/_components/historial_versiones', ['versiones' => $docSST['versiones']]) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
