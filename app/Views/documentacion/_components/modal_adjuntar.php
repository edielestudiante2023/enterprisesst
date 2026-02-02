<?php
/**
 * Componente: Modal para Adjuntar Documento Firmado
 * Sin variables requeridas (usa data attributes del boton)
 */
?>
<!-- Modal para adjuntar documento firmado escaneado -->
<div class="modal fade" id="modalAdjuntarFirmado" tabindex="-1" aria-labelledby="modalAdjuntarFirmadoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalAdjuntarFirmadoLabel">
                    <i class="bi bi-paperclip me-2"></i>Adjuntar Documento Firmado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAdjuntarFirmado" action="<?= base_url('documentos-sst/adjuntar-firmado') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_documento" id="adjuntar_id_documento">

                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Documento:</strong> <span id="adjuntar_titulo_documento"></span>
                    </div>

                    <p class="text-muted mb-3">
                        Suba el documento escaneado con las firmas fisicas de los trabajadores.
                        Este archivo quedara publicado en reportList y sera consultable.
                    </p>

                    <div class="mb-3">
                        <label for="archivo_firmado" class="form-label">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Archivo escaneado (PDF o imagen)
                        </label>
                        <input type="file" class="form-control" id="archivo_firmado" name="archivo_firmado"
                               accept=".pdf,.jpg,.jpeg,.png" required>
                        <div class="form-text">Formatos aceptados: PDF, JPG, PNG. Tamano maximo: 10MB</div>
                    </div>

                    <div class="mb-3">
                        <label for="observaciones_firmado" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_firmado" name="observaciones" rows="2"
                                  placeholder="Ej: Firmado en induccion del 29/01/2026"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info" id="btnAdjuntarFirmado">
                        <i class="bi bi-cloud-upload me-1"></i>Subir y Publicar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
