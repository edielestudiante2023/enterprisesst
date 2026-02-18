<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Editar Sección Config<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container py-4" style="max-width: 860px;">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= site_url('listSeccionesConfig') ?>">
                    <i class="bi bi-gear me-1"></i>Secciones Config
                </a>
            </li>
            <li class="breadcrumb-item active">Editar #<?= esc($seccion['id_seccion_config']) ?></li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header py-3" style="background: linear-gradient(135deg, #bd9751 0%, #a07c3a 100%);">
            <h5 class="mb-0 text-white">
                <i class="bi bi-pencil-square me-2"></i>Editar Sección:
                <em><?= esc($seccion['nombre']) ?></em>
            </h5>
        </div>
        <div class="card-body p-4">
            <form action="<?= site_url('editSeccionConfigPost/' . $seccion['id_seccion_config']) ?>" method="post">
                <?= csrf_field() ?>

                <!-- Tipo de config -->
                <div class="mb-3">
                    <label for="id_tipo_config" class="form-label fw-semibold">
                        Tipo de Documento <span class="text-danger">*</span>
                    </label>
                    <select name="id_tipo_config" id="id_tipo_config" class="form-select" required>
                        <option value="">— Seleccione un tipo —</option>
                        <?php foreach ($tipos_config as $tipo): ?>
                            <option value="<?= esc($tipo['id_tipo_config']) ?>"
                                <?= $tipo['id_tipo_config'] == $seccion['id_tipo_config'] ? 'selected' : '' ?>>
                                <?= esc($tipo['nombre']) ?>
                                (<?= esc($tipo['tipo_documento']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-3">
                    <!-- Número -->
                    <div class="col-sm-3">
                        <label for="numero" class="form-label fw-semibold">
                            Número <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="numero" id="numero" class="form-control"
                               min="1" required value="<?= esc($seccion['numero']) ?>">
                    </div>

                    <!-- Orden -->
                    <div class="col-sm-3">
                        <label for="orden" class="form-label fw-semibold">
                            Orden <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="orden" id="orden" class="form-control"
                               min="0" required value="<?= esc($seccion['orden']) ?>">
                    </div>

                    <!-- Tipo contenido -->
                    <div class="col-sm-6">
                        <label for="tipo_contenido" class="form-label fw-semibold">Tipo de Contenido</label>
                        <select name="tipo_contenido" id="tipo_contenido" class="form-select">
                            <?php foreach (['texto', 'tabla_dinamica', 'lista', 'mixto'] as $tc): ?>
                                <option value="<?= $tc ?>" <?= $seccion['tipo_contenido'] === $tc ? 'selected' : '' ?>>
                                    <?= $tc ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Nombre -->
                <div class="mt-3 mb-3">
                    <label for="nombre" class="form-label fw-semibold">
                        Nombre de la Sección <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nombre" id="nombre" class="form-control"
                           required maxlength="255" value="<?= esc($seccion['nombre']) ?>">
                </div>

                <!-- Seccion Key -->
                <div class="mb-3">
                    <label for="seccion_key" class="form-label fw-semibold">
                        Sección Key <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="seccion_key" id="seccion_key" class="form-control font-monospace"
                           required maxlength="100" value="<?= esc($seccion['seccion_key']) ?>">
                    <div class="form-text">Identificador único dentro del tipo. Ej: <code>objetivo</code>, <code>alcance</code></div>
                </div>

                <!-- Tabla dinámica tipo -->
                <div class="mb-3" id="wrapTablaDinamica"
                     style="display: <?= $seccion['tipo_contenido'] === 'tabla_dinamica' ? 'block' : 'none' ?>">
                    <label for="tabla_dinamica_tipo" class="form-label fw-semibold">Tabla Dinámica Tipo</label>
                    <input type="text" name="tabla_dinamica_tipo" id="tabla_dinamica_tipo" class="form-control"
                           maxlength="50" value="<?= esc($seccion['tabla_dinamica_tipo'] ?? '') ?>">
                    <div class="form-text">Aplica solo cuando el tipo de contenido es <code>tabla_dinamica</code>.</div>
                </div>

                <!-- Prompt IA -->
                <div class="mb-3">
                    <label for="prompt_ia" class="form-label fw-semibold">Prompt IA</label>
                    <textarea name="prompt_ia" id="prompt_ia" class="form-control font-monospace"
                              rows="5"><?= esc($seccion['prompt_ia'] ?? '') ?></textarea>
                </div>

                <!-- Checkboxes -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="es_obligatoria"
                                   id="es_obligatoria" value="1"
                                   <?= $seccion['es_obligatoria'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="es_obligatoria">Sección obligatoria</label>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="activo"
                                   id="activo" value="1"
                                   <?= $seccion['activo'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="activo">Activo</label>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning px-4 fw-semibold">
                        <i class="bi bi-floppy me-1"></i>Actualizar
                    </button>
                    <a href="<?= site_url('listSeccionesConfig') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('tipo_contenido').addEventListener('change', function () {
    const wrap = document.getElementById('wrapTablaDinamica');
    wrap.style.display = this.value === 'tabla_dinamica' ? 'block' : 'none';
});
</script>
<?= $this->endSection() ?>
