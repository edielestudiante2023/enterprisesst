<?php
/**
 * Componente: Modal para Crear Nueva Version
 *
 * Este es el modal ESTANDARIZADO para todos los documentos SST.
 * Garantiza consistencia en el proceso de versionamiento.
 *
 * PARAMETROS REQUERIDOS:
 * @param int $id_documento - ID del documento
 * @param string $version_actual - Version actual del documento (ej: "1.0")
 * @param string $tipo_documento - Tipo de documento (para mostrar nombre)
 *
 * PARAMETROS OPCIONALES:
 * @param string $modal_id - ID del modal (default: 'modalNuevaVersion')
 * @param array $campos_adicionales - Campos extra especificos del documento
 * @param string $endpoint_iniciar - URL para iniciar nueva version
 * @param string $endpoint_aprobar - URL para aprobar documento
 * @param bool $mostrar_tipo_cambio - Si mostrar selector de tipo (default: true)
 * @param string $tipo_cambio_default - Tipo de cambio por defecto ('menor' o 'mayor')
 *
 * USO:
 * <?= view('documentos_sst/_components/modal_nueva_version', [
 *     'id_documento' => $documento['id_documento'],
 *     'version_actual' => $versionVigente['version_texto'] ?? '1.0',
 *     'tipo_documento' => 'programa_capacitacion'
 * ]) ?>
 */

// Valores por defecto
$modal_id = $modal_id ?? 'modalNuevaVersion';
$version_actual = $version_actual ?? '1.0';
$tipo_documento = $tipo_documento ?? '';
$campos_adicionales = $campos_adicionales ?? [];
$endpoint_iniciar = $endpoint_iniciar ?? base_url('documentos-sst/iniciar-nueva-version');
$endpoint_aprobar = $endpoint_aprobar ?? base_url('documentos-sst/aprobar-documento');
$mostrar_tipo_cambio = $mostrar_tipo_cambio ?? true;
$tipo_cambio_default = $tipo_cambio_default ?? 'menor';

// Calcular proximas versiones (prediccion)
$versionParts = explode('.', $version_actual);
$versionMayor = (int)($versionParts[0] ?? 1);
$versionMenor = (int)($versionParts[1] ?? 0);
$proximaVersionMenor = $versionMayor . '.' . ($versionMenor + 1);
$proximaVersionMayor = ($versionMayor + 1) . '.0';
?>

<!-- Modal: Nueva Version - ESTANDAR -->
<div class="modal fade" id="<?= $modal_id ?>" tabindex="-1" aria-labelledby="<?= $modal_id ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="<?= $modal_id ?>Label">
                    <i class="fas fa-plus-circle me-2"></i>Crear Nueva Version
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- Informacion de version actual -->
                <div class="alert alert-info mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-2 fs-5"></i>
                        <div>
                            <strong>Version actual:</strong> <?= esc($version_actual) ?><br>
                            <span class="text-muted small">
                                Nueva version: <span id="<?= $modal_id ?>_preview_version"><?= $tipo_cambio_default === 'mayor' ? $proximaVersionMayor : $proximaVersionMenor ?></span>
                            </span>
                        </div>
                    </div>
                </div>

                <form id="<?= $modal_id ?>_form">
                    <input type="hidden" name="id_documento" value="<?= esc($id_documento) ?>">
                    <input type="hidden" name="tipo_documento" value="<?= esc($tipo_documento) ?>">

                    <?php if ($mostrar_tipo_cambio): ?>
                    <!-- Tipo de cambio -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-code-branch me-1"></i>Tipo de cambio
                        </label>

                        <div class="row g-3">
                            <!-- Opcion: Cambio Menor -->
                            <div class="col-6">
                                <div class="form-check card p-3 h-100 <?= $tipo_cambio_default === 'menor' ? 'border-primary' : '' ?>" id="<?= $modal_id ?>_card_menor">
                                    <input class="form-check-input" type="radio" name="tipo_cambio" id="<?= $modal_id ?>_tipo_menor"
                                           value="menor" <?= $tipo_cambio_default === 'menor' ? 'checked' : '' ?>
                                           data-version="<?= $proximaVersionMenor ?>">
                                    <label class="form-check-label w-100" for="<?= $modal_id ?>_tipo_menor">
                                        <div class="fw-bold text-primary mb-1">
                                            <i class="fas fa-minus-circle me-1"></i>Menor
                                        </div>
                                        <div class="small text-muted">
                                            <?= $version_actual ?> → <strong><?= $proximaVersionMenor ?></strong>
                                        </div>
                                        <div class="small mt-2 text-secondary">
                                            Correcciones, ajustes menores, actualizacion de datos
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Opcion: Cambio Mayor -->
                            <div class="col-6">
                                <div class="form-check card p-3 h-100 <?= $tipo_cambio_default === 'mayor' ? 'border-primary' : '' ?>" id="<?= $modal_id ?>_card_mayor">
                                    <input class="form-check-input" type="radio" name="tipo_cambio" id="<?= $modal_id ?>_tipo_mayor"
                                           value="mayor" <?= $tipo_cambio_default === 'mayor' ? 'checked' : '' ?>
                                           data-version="<?= $proximaVersionMayor ?>">
                                    <label class="form-check-label w-100" for="<?= $modal_id ?>_tipo_mayor">
                                        <div class="fw-bold text-warning mb-1">
                                            <i class="fas fa-plus-circle me-1"></i>Mayor
                                        </div>
                                        <div class="small text-muted">
                                            <?= $version_actual ?> → <strong><?= $proximaVersionMayor ?></strong>
                                        </div>
                                        <div class="small mt-2 text-secondary">
                                            Cambios significativos, restructuracion, nueva normativa
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <input type="hidden" name="tipo_cambio" value="<?= esc($tipo_cambio_default) ?>">
                    <?php endif; ?>

                    <!-- Descripcion del cambio -->
                    <div class="mb-4">
                        <label for="<?= $modal_id ?>_descripcion" class="form-label fw-bold">
                            <i class="fas fa-edit me-1"></i>Descripcion del cambio <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="<?= $modal_id ?>_descripcion" name="descripcion_cambio"
                                  rows="3" required minlength="10" maxlength="500"
                                  placeholder="Describa los cambios realizados en esta nueva version..."></textarea>
                        <div class="form-text">
                            Esta descripcion aparecera en el historial de Control de Cambios.
                        </div>

                        <!-- Ejemplos de descripcion -->
                        <div class="mt-2">
                            <small class="text-muted">Ejemplos:</small>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                <button type="button" class="btn btn-outline-secondary btn-sm ejemplo-descripcion"
                                        data-texto="Actualizacion de datos del responsable SST">
                                    Cambio responsable
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm ejemplo-descripcion"
                                        data-texto="Correccion de datos y ajustes menores">
                                    Correcciones
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm ejemplo-descripcion"
                                        data-texto="Actualizacion por cambio de normativa">
                                    Nueva normativa
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm ejemplo-descripcion"
                                        data-texto="Revision anual del documento">
                                    Revision anual
                                </button>
                            </div>
                        </div>
                    </div>

                    <?php
                    // Campos adicionales especificos del documento
                    if (!empty($campos_adicionales)):
                        foreach ($campos_adicionales as $campo):
                    ?>
                    <div class="mb-3">
                        <label for="<?= $modal_id ?>_<?= $campo['name'] ?>" class="form-label fw-bold">
                            <?php if (!empty($campo['icon'])): ?>
                            <i class="<?= $campo['icon'] ?> me-1"></i>
                            <?php endif; ?>
                            <?= esc($campo['label']) ?>
                            <?php if (!empty($campo['required'])): ?>
                            <span class="text-danger">*</span>
                            <?php endif; ?>
                        </label>

                        <?php if ($campo['type'] === 'select'): ?>
                        <select class="form-select" id="<?= $modal_id ?>_<?= $campo['name'] ?>" name="<?= $campo['name'] ?>"
                                <?= !empty($campo['required']) ? 'required' : '' ?>>
                            <?php foreach ($campo['options'] as $opt): ?>
                            <option value="<?= esc($opt['value']) ?>" <?= (!empty($opt['selected'])) ? 'selected' : '' ?>>
                                <?= esc($opt['label']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php elseif ($campo['type'] === 'text'): ?>
                        <input type="text" class="form-control" id="<?= $modal_id ?>_<?= $campo['name'] ?>"
                               name="<?= $campo['name'] ?>" value="<?= esc($campo['value'] ?? '') ?>"
                               <?= !empty($campo['required']) ? 'required' : '' ?>
                               <?= !empty($campo['readonly']) ? 'readonly' : '' ?>>
                        <?php endif; ?>

                        <?php if (!empty($campo['help'])): ?>
                        <div class="form-text"><?= esc($campo['help']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="<?= $modal_id ?>_btn_crear">
                    <i class="fas fa-plus-circle me-1"></i>Crear Nueva Version
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const modalId = '<?= $modal_id ?>';
    const form = document.getElementById(modalId + '_form');
    const btnCrear = document.getElementById(modalId + '_btn_crear');
    const previewVersion = document.getElementById(modalId + '_preview_version');
    const descripcionInput = document.getElementById(modalId + '_descripcion');

    // Manejar cambio de tipo de version
    document.querySelectorAll('input[name="tipo_cambio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Actualizar preview de version
            if (previewVersion) {
                previewVersion.textContent = this.dataset.version;
            }

            // Actualizar estilos de las cards
            document.getElementById(modalId + '_card_menor')?.classList.remove('border-primary');
            document.getElementById(modalId + '_card_mayor')?.classList.remove('border-primary');
            document.getElementById(modalId + '_card_' + this.value)?.classList.add('border-primary');
        });
    });

    // Ejemplos de descripcion
    document.querySelectorAll('.ejemplo-descripcion').forEach(btn => {
        btn.addEventListener('click', function() {
            if (descripcionInput) {
                descripcionInput.value = this.dataset.texto;
                descripcionInput.focus();
            }
        });
    });

    // Boton crear nueva version
    if (btnCrear) {
        btnCrear.addEventListener('click', async function() {
            // Validar formulario
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const descripcion = descripcionInput?.value?.trim();
            if (!descripcion || descripcion.length < 10) {
                alert('Por favor, ingrese una descripcion del cambio (minimo 10 caracteres)');
                descripcionInput?.focus();
                return;
            }

            // Deshabilitar boton
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Procesando...';

            try {
                const formData = new FormData(form);
                const response = await fetch('<?= $endpoint_iniciar ?>', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Mostrar mensaje de exito
                    Swal.fire({
                        icon: 'success',
                        title: 'Nueva version iniciada',
                        text: result.message || 'El documento esta en modo de edicion.',
                        confirmButtonText: 'Continuar'
                    }).then(() => {
                        // Redirigir a la URL de edicion si existe
                        if (result.data?.url_edicion) {
                            window.location.href = result.data.url_edicion;
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    throw new Error(result.message || 'Error al crear nueva version');
                }

            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Ocurrio un error al procesar la solicitud'
                });

                // Restaurar boton
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-plus-circle me-1"></i>Crear Nueva Version';
            }
        });
    }
})();
</script>

<style>
#<?= $modal_id ?> .form-check.card {
    cursor: pointer;
    transition: all 0.2s ease;
}
#<?= $modal_id ?> .form-check.card:hover {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}
#<?= $modal_id ?> .form-check.card.border-primary {
    border-width: 2px !important;
    background-color: rgba(13, 110, 253, 0.05);
}
#<?= $modal_id ?> .ejemplo-descripcion {
    font-size: 0.75rem;
    padding: 0.2rem 0.5rem;
}
</style>
