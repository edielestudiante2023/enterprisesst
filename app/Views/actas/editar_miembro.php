<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?= base_url('actas/' . $cliente['id_cliente']) ?>">Comites</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $comite['id_comite']) ?>"><?= esc($comite['codigo'] ?? $comite['tipo_nombre']) ?></a></li>
                    <li class="breadcrumb-item active">Editar Miembro</li>
                </ol>
            </nav>
            <h1 class="h3 mb-1">Editar Miembro</h1>
            <p class="text-muted mb-0"><?= esc($comite['tipo_nombre']) ?> - <?= esc($cliente['nombre_cliente']) ?></p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form action="<?= base_url('actas/comite/' . $comite['id_comite'] . '/actualizar-miembro/' . $miembro['id_miembro']) ?>" method="post">

                <!-- Datos del miembro -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-person me-2"></i>Datos del Miembro</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nombre_completo" id="nombre_completo" required
                                       value="<?= esc($miembro['nombre_completo'] ?? '') ?>"
                                       placeholder="Ej: Juan Carlos Perez Rodriguez">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tipo Doc.</label>
                                <select class="form-select" name="tipo_documento" id="tipo_documento">
                                    <option value="CC" <?= ($miembro['tipo_documento'] ?? '') === 'CC' ? 'selected' : '' ?>>CC</option>
                                    <option value="CE" <?= ($miembro['tipo_documento'] ?? '') === 'CE' ? 'selected' : '' ?>>CE</option>
                                    <option value="TI" <?= ($miembro['tipo_documento'] ?? '') === 'TI' ? 'selected' : '' ?>>TI</option>
                                    <option value="PA" <?= ($miembro['tipo_documento'] ?? '') === 'PA' ? 'selected' : '' ?>>Pasaporte</option>
                                    <option value="NIT" <?= ($miembro['tipo_documento'] ?? '') === 'NIT' ? 'selected' : '' ?>>NIT</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Numero <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="numero_documento" id="numero_documento" required maxlength="20"
                                       value="<?= esc($miembro['numero_documento'] ?? '') ?>"
                                       placeholder="Ej: 1234567890">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cargo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="cargo" id="cargo" required
                                       value="<?= esc($miembro['cargo'] ?? '') ?>"
                                       placeholder="Ej: Jefe de Produccion">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Area / Dependencia</label>
                                <input type="text" class="form-control" name="area_dependencia" id="area_dependencia"
                                       value="<?= esc($miembro['area_dependencia'] ?? '') ?>"
                                       placeholder="Ej: Produccion, Administrativa...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" id="email" required
                                       value="<?= esc($miembro['email'] ?? '') ?>"
                                       placeholder="correo@empresa.com">
                                <small class="text-muted">Necesario para enviar notificaciones de firma</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telefono / Celular</label>
                                <input type="tel" class="form-control" name="telefono" id="telefono"
                                       value="<?= esc($miembro['telefono'] ?? '') ?>"
                                       placeholder="Ej: 3001234567">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rol en el comite -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-award me-2"></i>Rol en el Comite</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tipo de Miembro <span class="text-danger">*</span></label>
                                <select class="form-select" name="tipo_miembro" required>
                                    <option value="principal" <?= ($miembro['tipo_miembro'] ?? '') === 'principal' ? 'selected' : '' ?>>Principal</option>
                                    <option value="suplente" <?= ($miembro['tipo_miembro'] ?? '') === 'suplente' ? 'selected' : '' ?>>Suplente</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Representa a <span class="text-danger">*</span></label>
                                <select class="form-select" name="representacion" required>
                                    <option value="">Seleccione...</option>
                                    <option value="empleador" <?= ($miembro['representacion'] ?? '') === 'empleador' ? 'selected' : '' ?>>Empleador</option>
                                    <option value="trabajadores" <?= ($miembro['representacion'] ?? '') === 'trabajadores' ? 'selected' : '' ?>>Trabajadores</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Rol en el Comite</label>
                                <select class="form-select" name="rol_comite" id="rol_comite">
                                    <option value="miembro" <?= ($miembro['rol_comite'] ?? '') === 'miembro' ? 'selected' : '' ?>>Miembro</option>
                                    <option value="presidente" <?= ($miembro['rol_comite'] ?? '') === 'presidente' ? 'selected' : '' ?>>Presidente</option>
                                    <option value="secretario" <?= ($miembro['rol_comite'] ?? '') === 'secretario' ? 'selected' : '' ?>>Secretario</option>
                                    <option value="vigia" <?= ($miembro['rol_comite'] ?? '') === 'vigia' ? 'selected' : '' ?>>Vigia SST</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permisos -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Permisos en el Sistema</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="puede_crear_actas" id="puede_crear_actas" value="1"
                                   <?= !empty($miembro['puede_crear_actas']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="puede_crear_actas">
                                <strong>Puede crear actas</strong>
                                <br><small class="text-muted">Permite al miembro iniciar nuevas actas de reunion</small>
                            </label>
                        </div>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="puede_cerrar_actas" id="puede_cerrar_actas" value="1"
                                   <?= !empty($miembro['puede_cerrar_actas']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="puede_cerrar_actas">
                                <strong>Puede cerrar actas</strong>
                                <br><small class="text-muted">Permite al miembro cerrar actas una vez todos han firmado</small>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-lg me-2"></i>Guardar Cambios
                    </button>
                    <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $comite['id_comite']) ?>" class="btn btn-outline-secondary btn-lg">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Info del miembro -->
            <div class="card border-0 shadow-sm mb-4 border-start border-info border-4">
                <div class="card-body">
                    <h6><i class="bi bi-info-circle me-2"></i>Informacion</h6>
                    <p class="text-muted small mb-2">
                        <strong>Fecha de ingreso:</strong><br>
                        <?= !empty($miembro['fecha_ingreso']) ? date('d/m/Y', strtotime($miembro['fecha_ingreso'])) : 'No registrada' ?>
                    </p>
                    <?php if (!empty($miembro['id_responsable'])): ?>
                    <p class="text-muted small mb-0">
                        <span class="badge bg-success"><i class="bi bi-link-45deg me-1"></i>Vinculado a Responsables SST</span>
                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Miembros actuales -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Otros Miembros</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($miembrosActuales as $m): ?>
                        <?php if ($m['id_miembro'] != $miembro['id_miembro']): ?>
                        <li class="list-group-item">
                            <strong><?= esc($m['nombre_completo']) ?></strong>
                            <?php if (($m['rol_comite'] ?? 'miembro') !== 'miembro'): ?>
                                <span class="badge bg-warning text-dark"><?= ucfirst($m['rol_comite']) ?></span>
                            <?php endif; ?>
                            <br><small class="text-muted"><?= esc($m['cargo'] ?? 'Sin cargo') ?></small>
                        </li>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
