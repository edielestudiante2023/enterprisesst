<?php
/**
 * Componente: Modal de Historial de Versiones
 *
 * Muestra el historial completo de versiones de un documento SST.
 * Permite visualizar, descargar PDFs y restaurar versiones anteriores.
 *
 * PARAMETROS REQUERIDOS:
 * @param int $id_documento - ID del documento
 * @param array $versiones - Array de versiones del documento
 *
 * PARAMETROS OPCIONALES:
 * @param string $modal_id - ID del modal (default: 'modalHistorialVersiones')
 * @param bool $permitir_restaurar - Si permite restaurar versiones (default: true)
 * @param bool $permitir_descargar - Si permite descargar PDFs (default: true)
 * @param string $endpoint_restaurar - URL para restaurar version
 *
 * USO:
 * <?= view('documentos_sst/_components/modal_historial_versiones', [
 *     'id_documento' => $documento['id_documento'],
 *     'versiones' => $historialVersiones
 * ]) ?>
 */

// Valores por defecto
$modal_id = $modal_id ?? 'modalHistorialVersiones';
$versiones = $versiones ?? [];
$permitir_restaurar = $permitir_restaurar ?? true;
$permitir_descargar = $permitir_descargar ?? true;
$endpoint_restaurar = $endpoint_restaurar ?? base_url('documentos-sst/restaurar-version');
?>

<!-- Modal: Historial de Versiones -->
<div class="modal fade" id="<?= $modal_id ?>" tabindex="-1" aria-labelledby="<?= $modal_id ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="<?= $modal_id ?>Label">
                    <i class="fas fa-history me-2"></i>Historial de Versiones
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <?php if (empty($versiones)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>No hay versiones registradas para este documento.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 80px;">Version</th>
                                <th style="width: 70px;">Tipo</th>
                                <th>Descripcion</th>
                                <th style="width: 120px;">Fecha</th>
                                <th style="width: 150px;">Autorizado por</th>
                                <th style="width: 100px;" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($versiones as $index => $version): ?>
                            <tr class="<?= $version['estado'] === 'vigente' ? 'table-success' : '' ?>">
                                <td>
                                    <span class="badge <?= $version['estado'] === 'vigente' ? 'bg-success' : 'bg-secondary' ?> me-1">
                                        v<?= esc($version['version_texto']) ?>
                                    </span>
                                    <?php if ($version['estado'] === 'vigente'): ?>
                                    <span class="badge bg-primary">Vigente</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning text-dark">Obsoleto</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($version['tipo_cambio'] === 'mayor'): ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-plus-circle me-1"></i>Mayor
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-info text-dark">
                                        <i class="fas fa-minus-circle me-1"></i>Menor
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 250px;" title="<?= esc($version['descripcion_cambio']) ?>">
                                        <?= esc($version['descripcion_cambio']) ?>
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <?= date('d/m/Y', strtotime($version['fecha_autorizacion'])) ?><br>
                                        <span class="text-muted"><?= date('H:i', strtotime($version['fecha_autorizacion'])) ?></span>
                                    </small>
                                </td>
                                <td>
                                    <small><?= esc($version['autorizado_por'] ?? 'Usuario del sistema') ?></small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($permitir_descargar && !empty($version['archivo_pdf'])): ?>
                                        <a href="<?= esc($version['archivo_pdf']) ?>" target="_blank"
                                           class="btn btn-outline-danger" title="Descargar PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <?php endif; ?>

                                        <?php if ($permitir_restaurar && $version['estado'] !== 'vigente'): ?>
                                        <button type="button" class="btn btn-outline-warning btn-restaurar-version"
                                                data-id-version="<?= $version['id_version'] ?>"
                                                data-version-texto="<?= esc($version['version_texto']) ?>"
                                                title="Restaurar esta version">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Leyenda -->
                <div class="border-top p-3 bg-light">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Leyenda:</strong>
                        <span class="badge bg-success ms-2">Vigente</span> Version actual activa
                        <span class="badge bg-warning text-dark ms-2">Obsoleto</span> Versiones anteriores
                        <span class="badge bg-info text-dark ms-2">Menor</span> Correcciones
                        <span class="badge bg-warning text-dark ms-2">Mayor</span> Cambios significativos
                    </small>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<?php if ($permitir_restaurar): ?>
<script>
(function() {
    const modalId = '<?= $modal_id ?>';
    const idDocumento = <?= (int)$id_documento ?>;

    // Manejar restauracion de versiones
    document.querySelectorAll('#' + modalId + ' .btn-restaurar-version').forEach(btn => {
        btn.addEventListener('click', async function() {
            const idVersion = this.dataset.idVersion;
            const versionTexto = this.dataset.versionTexto;

            const confirmacion = await Swal.fire({
                icon: 'question',
                title: 'Restaurar version ' + versionTexto + '?',
                html: `
                    <p>El contenido del documento sera reemplazado con el de la version <strong>${versionTexto}</strong>.</p>
                    <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> El documento quedara en estado <strong>borrador</strong> y debera ser aprobado nuevamente.</p>
                `,
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-undo"></i> Si, restaurar',
                cancelButtonText: 'Cancelar'
            });

            if (!confirmacion.isConfirmed) return;

            // Mostrar loading
            Swal.fire({
                title: 'Restaurando version...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData();
                formData.append('id_documento', idDocumento);
                formData.append('id_version', idVersion);

                const response = await fetch('<?= $endpoint_restaurar ?>', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Version restaurada',
                        text: result.message || 'El documento ha sido restaurado. Apruebelo para crear la nueva version.',
                        confirmButtonText: 'Continuar'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(result.message || 'Error al restaurar');
                }

            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'No se pudo restaurar la version'
                });
            }
        });
    });
})();
</script>
<?php endif; ?>
