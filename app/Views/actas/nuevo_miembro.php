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
                    <li class="breadcrumb-item active">Nuevo Miembro</li>
                </ol>
            </nav>
            <h1 class="h3 mb-1">Agregar Miembro</h1>
            <p class="text-muted mb-0"><?= esc($comite['tipo_nombre']) ?> - <?= esc($cliente['nombre_cliente']) ?></p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form action="<?= base_url('actas/comite/' . $comite['id_comite'] . '/guardar-miembro') ?>" method="post">

                <!-- Seleccionar de responsables existentes -->
                <?php if (!empty($responsablesComite) || !empty($todosResponsables)): ?>
                <div class="card border-0 shadow-sm mb-4 border-start border-primary border-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-person-check me-2 text-primary"></i>Seleccionar de Responsables Existentes</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Seleccione una persona ya registrada en el sistema para agregarla como miembro del comite.
                            Los datos se autocompletaran automaticamente.
                        </p>

                        <?php if (!empty($responsablesComite)): ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Responsables del <?= esc($comite['tipo_nombre']) ?></label>
                            <select class="form-select" id="selectResponsableComite" onchange="seleccionarResponsable(this)">
                                <option value="">-- Seleccionar persona --</option>
                                <?php foreach ($responsablesComite as $resp): ?>
                                <option value="<?= $resp['id_responsable'] ?>"
                                        data-nombre="<?= esc($resp['nombre_completo']) ?>"
                                        data-documento="<?= esc($resp['numero_documento']) ?>"
                                        data-tipo-documento="<?= esc($resp['tipo_documento'] ?? 'CC') ?>"
                                        data-cargo="<?= esc($resp['cargo'] ?? '') ?>"
                                        data-email="<?= esc($resp['email'] ?? '') ?>"
                                        data-telefono="<?= esc($resp['telefono'] ?? '') ?>"
                                        data-rol="<?= esc($resp['tipo_rol']) ?>">
                                    <?= esc($resp['nombre_completo']) ?> - <?= esc($resp['nombre_rol']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($todosResponsables) && count($todosResponsables) > count($responsablesComite ?? [])): ?>
                        <div class="mb-3">
                            <label class="form-label">O seleccione de todos los responsables registrados</label>
                            <select class="form-select" id="selectTodosResponsables" onchange="seleccionarResponsable(this)">
                                <option value="">-- Seleccionar persona --</option>
                                <?php foreach ($todosResponsables as $resp): ?>
                                <option value="<?= $resp['id_responsable'] ?>"
                                        data-nombre="<?= esc($resp['nombre_completo']) ?>"
                                        data-documento="<?= esc($resp['numero_documento']) ?>"
                                        data-tipo-documento="<?= esc($resp['tipo_documento'] ?? 'CC') ?>"
                                        data-cargo="<?= esc($resp['cargo'] ?? '') ?>"
                                        data-email="<?= esc($resp['email'] ?? '') ?>"
                                        data-telefono="<?= esc($resp['telefono'] ?? '') ?>"
                                        data-rol="<?= esc($resp['tipo_rol']) ?>">
                                    <?= esc($resp['nombre_completo']) ?> - <?= esc($resp['nombre_rol']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="alert alert-info py-2 mb-0">
                            <small><i class="bi bi-info-circle me-1"></i>
                            Si la persona no esta en la lista, puede ingresar los datos manualmente en el formulario de abajo.
                            </small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Input oculto para id_responsable -->
                <input type="hidden" name="id_responsable" id="id_responsable" value="">

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
                                       placeholder="Ej: Juan Carlos Perez Rodriguez">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tipo Doc.</label>
                                <select class="form-select" name="tipo_documento" id="tipo_documento">
                                    <option value="CC">CC</option>
                                    <option value="CE">CE</option>
                                    <option value="TI">TI</option>
                                    <option value="PA">Pasaporte</option>
                                    <option value="NIT">NIT</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Numero <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="numero_documento" id="numero_documento" required maxlength="20"
                                       placeholder="Ej: 1234567890">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cargo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="cargo" id="cargo" required
                                       placeholder="Ej: Jefe de Produccion">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Area / Dependencia</label>
                                <input type="text" class="form-control" name="area_dependencia" id="area_dependencia"
                                       placeholder="Ej: Produccion, Administrativa...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" id="email" required
                                       placeholder="correo@empresa.com">
                                <small class="text-muted">Necesario para enviar notificaciones de firma</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telefono / Celular</label>
                                <input type="tel" class="form-control" name="telefono" id="telefono"
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
                                    <option value="principal">Principal</option>
                                    <option value="suplente">Suplente</option>
                                </select>
                                <?php if (!empty($comite['requiere_paridad'])): ?>
                                <small class="text-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Este comite requiere paridad
                                </small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Representa a <span class="text-danger">*</span></label>
                                <select class="form-select" name="representacion" required>
                                    <option value="">Seleccione...</option>
                                    <option value="empleador">Empleador</option>
                                    <option value="trabajadores">Trabajadores</option>
                                </select>
                                <small class="text-muted">COPASST requiere paridad entre partes</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Rol en el Comite</label>
                                <select class="form-select" name="rol_comite" id="rol_comite">
                                    <option value="miembro">Miembro</option>
                                    <option value="presidente">Presidente</option>
                                    <option value="secretario">Secretario</option>
                                    <option value="vigia">Vigia SST</option>
                                </select>
                            </div>
                        </div>

                        <!-- Nota informativa segun tipo de comite -->
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Recuerde:</strong>
                            <ul class="mb-0 mt-2">
                                <li>El <strong>Presidente</strong> es elegido por el empleador (de sus representantes)</li>
                                <li>El <strong>Secretario</strong> es elegido por votacion de todo el comite</li>
                                <li>Para empresas de menos de 10 trabajadores, se designa un <strong>Vigia SST</strong> en lugar de COPASST</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Permisos -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Permisos en el Sistema</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Estos permisos determinan que puede hacer el miembro en el sistema de actas.
                            <strong>El consultor siempre tiene acceso completo.</strong>
                        </p>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="puede_crear_actas" id="puede_crear_actas" value="1">
                            <label class="form-check-label" for="puede_crear_actas">
                                <strong>Puede crear actas</strong>
                                <br><small class="text-muted">Permite al miembro iniciar nuevas actas de reunion</small>
                            </label>
                        </div>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="puede_cerrar_actas" id="puede_cerrar_actas" value="1">
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
                        <i class="bi bi-person-plus me-2"></i>Agregar Miembro
                    </button>
                    <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $comite['id_comite']) ?>" class="btn btn-outline-secondary btn-lg">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Miembros actuales -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Miembros Actuales</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($miembrosActuales)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-people text-muted fs-1"></i>
                        <p class="text-muted mb-0">Sin miembros registrados</p>
                        <small class="text-muted">Este sera el primer miembro del comite</small>
                    </div>
                    <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php
                        $principales = array_filter($miembrosActuales, fn($m) => $m['tipo_miembro'] === 'principal');
                        $suplentes = array_filter($miembrosActuales, fn($m) => $m['tipo_miembro'] === 'suplente');
                        ?>
                        <?php if (!empty($principales)): ?>
                        <li class="list-group-item bg-light">
                            <small class="text-muted fw-bold">PRINCIPALES (<?= count($principales) ?>)</small>
                        </li>
                        <?php foreach ($principales as $m): ?>
                        <li class="list-group-item">
                            <strong><?= esc($m['nombre_completo']) ?></strong>
                            <?php if (($m['rol_comite'] ?? 'miembro') !== 'miembro'): ?>
                                <span class="badge bg-warning text-dark"><?= ucfirst($m['rol_comite']) ?></span>
                            <?php endif; ?>
                            <br><small class="text-muted"><?= esc($m['cargo'] ?? 'Sin cargo') ?></small>
                            <?php if (!empty($m['representacion'])): ?>
                                <br><span class="badge <?= $m['representacion'] === 'empleador' ? 'bg-primary' : 'bg-success' ?>"><?= ucfirst($m['representacion']) ?></span>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($suplentes)): ?>
                        <li class="list-group-item bg-light">
                            <small class="text-muted fw-bold">SUPLENTES (<?= count($suplentes) ?>)</small>
                        </li>
                        <?php foreach ($suplentes as $m): ?>
                        <li class="list-group-item">
                            <strong><?= esc($m['nombre_completo']) ?></strong>
                            <br><small class="text-muted"><?= esc($m['cargo'] ?? 'Sin cargo') ?></small>
                            <?php if (!empty($m['representacion'])): ?>
                                <br><span class="badge <?= $m['representacion'] === 'empleador' ? 'bg-primary' : 'bg-success' ?>"><?= ucfirst($m['representacion']) ?></span>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info de paridad -->
            <?php if (!empty($comite['requiere_paridad'])): ?>
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <h6 class="text-warning"><i class="bi bi-balance-scale me-2"></i>Paridad Requerida</h6>
                    <p class="text-muted mb-0 small">
                        Este comite requiere igual numero de representantes del empleador y de los trabajadores.
                    </p>
                    <?php
                    $empleador = count(array_filter($miembrosActuales ?? [], fn($m) => ($m['representacion'] ?? '') === 'empleador' && $m['tipo_miembro'] === 'principal'));
                    $trabajadores = count(array_filter($miembrosActuales ?? [], fn($m) => ($m['representacion'] ?? '') === 'trabajadores' && $m['tipo_miembro'] === 'principal'));
                    ?>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="mb-0 text-primary"><?= $empleador ?></h4>
                            <small>Empleador</small>
                        </div>
                        <div class="col-6">
                            <h4 class="mb-0 text-success"><?= $trabajadores ?></h4>
                            <small>Trabajadores</small>
                        </div>
                    </div>
                    <?php if ($empleador !== $trabajadores && ($empleador > 0 || $trabajadores > 0)): ?>
                    <div class="alert alert-warning mt-3 mb-0 py-2">
                        <small><i class="bi bi-exclamation-triangle me-1"></i>La paridad no esta balanceada</small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Link a Responsables SST -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6><i class="bi bi-link-45deg me-2"></i>Gestionar Responsables</h6>
                    <p class="text-muted small mb-3">
                        Si necesita agregar nuevas personas al sistema, puede hacerlo desde el modulo de Responsables SST.
                    </p>
                    <a href="<?= base_url('responsables-sst/' . $cliente['id_cliente']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Ir a Responsables SST
                    </a>
                </div>
            </div>

            <!-- Ayuda -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6><i class="bi bi-question-circle me-2"></i>Ayuda</h6>
                    <p class="text-muted small mb-0">
                        <strong>COPASST:</strong> Minimo 4 miembros (2 empleador + 2 trabajadores) para empresas de 10+ trabajadores.
                        <br><br>
                        <strong>COCOLAB:</strong> Minimo 2 miembros. No requiere paridad estricta pero se recomienda representacion de ambas partes.
                        <br><br>
                        <strong>Brigada:</strong> Sin requisitos de paridad. Los miembros son voluntarios capacitados.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function seleccionarResponsable(select) {
    const option = select.options[select.selectedIndex];

    if (!option.value) {
        // Limpiar formulario si no se selecciona nada
        document.getElementById('id_responsable').value = '';
        document.getElementById('nombre_completo').value = '';
        document.getElementById('numero_documento').value = '';
        document.getElementById('tipo_documento').value = 'CC';
        document.getElementById('cargo').value = '';
        document.getElementById('email').value = '';
        document.getElementById('telefono').value = '';
        return;
    }

    // Llenar formulario con datos del responsable seleccionado
    document.getElementById('id_responsable').value = option.value;
    document.getElementById('nombre_completo').value = option.dataset.nombre || '';
    document.getElementById('numero_documento').value = option.dataset.documento || '';
    document.getElementById('tipo_documento').value = option.dataset.tipoDocumento || 'CC';
    document.getElementById('cargo').value = option.dataset.cargo || '';
    document.getElementById('email').value = option.dataset.email || '';
    document.getElementById('telefono').value = option.dataset.telefono || '';

    // Intentar pre-seleccionar el rol basado en el tipo de responsable
    const rol = option.dataset.rol || '';
    if (rol.includes('presidente')) {
        document.getElementById('rol_comite').value = 'presidente';
    } else if (rol.includes('secretario')) {
        document.getElementById('rol_comite').value = 'secretario';
    } else if (rol.includes('vigia')) {
        document.getElementById('rol_comite').value = 'vigia';
    } else {
        document.getElementById('rol_comite').value = 'miembro';
    }

    // Limpiar el otro selector
    const otroSelector = select.id === 'selectResponsableComite' ? 'selectTodosResponsables' : 'selectResponsableComite';
    const otroSelect = document.getElementById(otroSelector);
    if (otroSelect) {
        otroSelect.value = '';
    }

    // Resaltar que se selecciono un responsable existente
    document.getElementById('nombre_completo').classList.add('border-success');
    setTimeout(() => {
        document.getElementById('nombre_completo').classList.remove('border-success');
    }, 2000);
}
</script>
<?= $this->endSection() ?>
