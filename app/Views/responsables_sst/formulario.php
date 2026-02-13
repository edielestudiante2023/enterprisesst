<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('responsables-sst/' . $cliente['id_cliente']) ?>">
                <i class="bi bi-people-fill me-2"></i>Responsables SST
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a href="<?= base_url('responsables-sst/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">
                    <i class="bi bi-person-plus-fill me-2"></i><?= $titulo ?>
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('contexto') ?>">Clientes</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('responsables-sst/' . $cliente['id_cliente']) ?>"><?= esc($cliente['nombre_cliente']) ?></a></li>
                        <li class="breadcrumb-item active"><?= $responsable ? 'Editar' : 'Agregar' ?></li>
                    </ol>
                </nav>
            </div>
            <a href="<?= base_url('responsables-sst/' . $cliente['id_cliente']) ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </div>

        <!-- Alertas -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="<?= base_url('responsables-sst/' . $cliente['id_cliente'] . '/guardar') ?>" method="POST">
                            <?php if ($responsable): ?>
                                <input type="hidden" name="id_responsable" value="<?= $responsable['id_responsable'] ?>">
                            <?php endif; ?>

                            <!-- Tipo de Rol -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Tipo de Rol <span class="text-danger">*</span></label>
                                <select name="tipo_rol" class="form-select form-select-lg" required id="tipoRol">
                                    <option value="">Seleccione el rol...</option>
                                    <?php foreach ($tiposRol as $key => $nombre): ?>
                                        <option value="<?= $key ?>"
                                            <?= ($responsable && $responsable['tipo_rol'] === $key) ? 'selected' : '' ?>>
                                            <?= esc($nombre) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    <?php if ($estandares <= 10): ?>
                                        <i class="bi bi-info-circle me-1"></i>Empresa con <?= $estandares ?> estándares: Usa Vigía SST (no COPASST)
                                    <?php else: ?>
                                        <i class="bi bi-info-circle me-1"></i>Empresa con <?= $estandares ?> estándares: Requiere COPASST
                                    <?php endif; ?>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Datos Personales -->
                            <h6 class="text-muted mb-3"><i class="bi bi-person me-1"></i>Datos Personales</h6>

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre_completo" class="form-control"
                                           value="<?= $responsable['nombre_completo'] ?? '' ?>"
                                           placeholder="Nombre completo del responsable" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tipo Documento</label>
                                    <select name="tipo_documento" class="form-select">
                                        <option value="CC" <?= ($responsable['tipo_documento'] ?? 'CC') === 'CC' ? 'selected' : '' ?>>CC - Cédula</option>
                                        <option value="CE" <?= ($responsable['tipo_documento'] ?? '') === 'CE' ? 'selected' : '' ?>>CE - Cédula Extranjería</option>
                                        <option value="PA" <?= ($responsable['tipo_documento'] ?? '') === 'PA' ? 'selected' : '' ?>>PA - Pasaporte</option>
                                        <option value="TI" <?= ($responsable['tipo_documento'] ?? '') === 'TI' ? 'selected' : '' ?>>TI - Tarjeta Identidad</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Número Documento <span class="text-danger">*</span></label>
                                    <input type="text" name="numero_documento" class="form-control"
                                           value="<?= $responsable['numero_documento'] ?? '' ?>"
                                           placeholder="Sin puntos ni guiones" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cargo <span class="text-danger">*</span></label>
                                    <input type="text" name="cargo" class="form-control"
                                           value="<?= $responsable['cargo'] ?? '' ?>"
                                           placeholder="Cargo en la empresa" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Teléfono</label>
                                    <input type="tel" name="telefono" class="form-control"
                                           value="<?= $responsable['telefono'] ?? '' ?>"
                                           placeholder="Número de contacto">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?= $responsable['email'] ?? '' ?>"
                                       placeholder="correo@ejemplo.com">
                            </div>

                            <!-- Datos específicos para Responsable SG-SST -->
                            <div id="camposResponsableSST" style="display: none;">
                                <hr class="my-4">
                                <h6 class="text-muted mb-3"><i class="bi bi-award me-1"></i>Información de Licencia SST</h6>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Formación SST</label>
                                        <select name="formacion_sst" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <option value="Curso 50 horas" <?= ($responsable['formacion_sst'] ?? '') === 'Curso 50 horas' ? 'selected' : '' ?>>Curso 50 horas</option>
                                            <option value="Técnico SST" <?= ($responsable['formacion_sst'] ?? '') === 'Técnico SST' ? 'selected' : '' ?>>Técnico SST</option>
                                            <option value="Tecnólogo SST" <?= ($responsable['formacion_sst'] ?? '') === 'Tecnólogo SST' ? 'selected' : '' ?>>Tecnólogo SST</option>
                                            <option value="Profesional SST" <?= ($responsable['formacion_sst'] ?? '') === 'Profesional SST' ? 'selected' : '' ?>>Profesional SST</option>
                                            <option value="Especialista SST" <?= ($responsable['formacion_sst'] ?? '') === 'Especialista SST' ? 'selected' : '' ?>>Especialista SST</option>
                                        </select>
                                        <div class="form-text">
                                            <?php if ($estandares <= 10): ?>
                                                Para <?= $estandares ?> estándares no se requiere licencia SST
                                            <?php else: ?>
                                                Para <?= $estandares ?> estándares se recomienda profesional con licencia
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Número de Licencia SST</label>
                                        <input type="text" name="licencia_sst_numero" class="form-control"
                                               value="<?= $responsable['licencia_sst_numero'] ?? '' ?>"
                                               placeholder="Número de resolución">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Vigencia Licencia</label>
                                        <input type="date" name="licencia_sst_vigencia" class="form-control"
                                               value="<?= $responsable['licencia_sst_vigencia'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="mb-3">
                                <label class="form-label">Observaciones</label>
                                <textarea name="observaciones" class="form-control" rows="2"
                                          placeholder="Notas adicionales..."><?= $responsable['observaciones'] ?? '' ?></textarea>
                            </div>

                            <!-- Estado -->
                            <div class="form-check mb-3">
                                <input type="checkbox" name="activo" value="1" class="form-check-input"
                                       <?= ($responsable['activo'] ?? 1) ? 'checked' : '' ?> id="checkActivo">
                                <label class="form-check-label" for="checkActivo">
                                    Responsable activo
                                </label>
                            </div>

                            <!-- Acceso al Sistema -->
                            <div class="card border-primary mb-4" id="cardCrearUsuario">
                                <div class="card-body py-3">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="crear_usuario" value="0">
                                        <input type="checkbox" name="crear_usuario" value="1" class="form-check-input"
                                               role="switch" id="checkCrearUsuario"
                                               <?= !empty($responsable['id_usuario']) ? 'checked disabled' : '' ?>
                                               style="transform: scale(1.3);">
                                        <label class="form-check-label fw-bold fs-6 ms-2" for="checkCrearUsuario">
                                            <i class="bi bi-person-badge me-1"></i>Crear acceso al sistema
                                        </label>
                                    </div>
                                    <div class="form-text ms-5">
                                        <?php if (!empty($responsable['id_usuario'])): ?>
                                            <span class="text-success"><i class="bi bi-check-circle me-1"></i>Este responsable ya tiene acceso al sistema</span>
                                        <?php else: ?>
                                            <span id="msgCrearUsuario">Active esta opcion para crear un usuario con el email como nombre de usuario.</span>
                                            <br><small class="text-muted">Requiere que el campo Email este diligenciado.</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= base_url('responsables-sst/' . $cliente['id_cliente']) ?>" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i><?= $responsable ? 'Actualizar' : 'Guardar' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tipoRol = document.getElementById('tipoRol');
        const camposSST = document.getElementById('camposResponsableSST');

        function toggleCamposSST() {
            if (tipoRol.value === 'responsable_sgsst') {
                camposSST.style.display = 'block';
            } else {
                camposSST.style.display = 'none';
            }
        }

        tipoRol.addEventListener('change', toggleCamposSST);
        toggleCamposSST(); // Ejecutar al cargar

        // Visual feedback para el switch de crear usuario
        const checkCrear = document.getElementById('checkCrearUsuario');
        const cardCrear = document.getElementById('cardCrearUsuario');
        const msgCrear = document.getElementById('msgCrearUsuario');

        function toggleCardCrear() {
            if (checkCrear && cardCrear) {
                if (checkCrear.checked) {
                    cardCrear.classList.remove('border-primary', 'bg-light');
                    cardCrear.classList.add('border-success', 'bg-success', 'bg-opacity-10');
                    if (msgCrear) msgCrear.innerHTML = '<strong class="text-success"><i class="bi bi-check-circle me-1"></i>Se creara usuario al guardar</strong>';
                } else {
                    cardCrear.classList.remove('border-success', 'bg-success', 'bg-opacity-10');
                    cardCrear.classList.add('border-primary');
                    if (msgCrear) msgCrear.innerHTML = 'Active esta opcion para crear un usuario con el email como nombre de usuario.';
                }
            }
        }

        if (checkCrear) {
            checkCrear.addEventListener('change', toggleCardCrear);
            toggleCardCrear();
        }
    });
    </script>
</body>
</html>
