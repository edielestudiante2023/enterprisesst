<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsables SST - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('contexto/' . $cliente['id_cliente']) ?>">
                <i class="bi bi-people-fill me-2"></i>Responsables SST
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a href="<?= base_url('contexto/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm">
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
                    <i class="bi bi-people-fill me-2"></i>Responsables del SG-SST
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('contexto') ?>">Clientes</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('contexto/' . $cliente['id_cliente']) ?>"><?= esc($cliente['nombre_cliente']) ?></a></li>
                        <li class="breadcrumb-item active">Responsables SST</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="<?= base_url('responsables-sst/' . $cliente['id_cliente'] . '/crear') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Agregar Responsable
                </a>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Panel izquierdo: Info y verificación -->
            <div class="col-md-4">
                <!-- Info del cliente -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Cliente</h6>
                        <h5 class="mb-1"><?= esc($cliente['nombre_cliente']) ?></h5>
                        <p class="text-muted mb-2">NIT: <?= esc($cliente['nit_cliente']) ?></p>
                        <span class="badge bg-<?= $estandares <= 7 ? 'info' : ($estandares <= 21 ? 'warning' : 'danger') ?>">
                            <?= $estandares ?> Estándares
                        </span>
                    </div>
                </div>

                <!-- Verificación de roles obligatorios -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="bi bi-shield-check me-1"></i>Roles Obligatorios
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-3" style="height: 25px;">
                            <div class="progress-bar bg-<?= $verificacion['completo'] ? 'success' : 'warning' ?>"
                                 style="width: <?= $verificacion['porcentaje'] ?>%">
                                <?= $verificacion['porcentaje'] ?>%
                            </div>
                        </div>

                        <?php if (!empty($verificacion['completos'])): ?>
                            <p class="small text-muted mb-2">Roles registrados:</p>
                            <?php foreach ($verificacion['completos'] as $rol): ?>
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    <span class="small"><?= esc($rol['nombre']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($verificacion['faltantes'])): ?>
                            <p class="small text-muted mb-2 mt-3">Roles pendientes:</p>
                            <?php foreach ($verificacion['faltantes'] as $rol): ?>
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-exclamation-circle text-warning me-2"></i>
                                    <span class="small"><?= esc($rol['nombre']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Botón migrar datos -->
                <?php if (!empty($contexto['representante_legal_nombre']) || !empty($contexto['responsable_sgsst_nombre'])): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="small text-muted mb-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Se detectaron datos de responsables en el contexto antiguo.
                            </p>
                            <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btnMigrar">
                                <i class="bi bi-arrow-repeat me-1"></i>Migrar datos existentes
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Panel derecho: Lista de responsables -->
            <div class="col-md-8">
                <?php
                /**
                 * Según Resolución 0312/2019:
                 * - 7 estándares: Solo Vigía SST (< 10 trabajadores, Riesgo I-III)
                 * - 21/60 estándares: COPASST obligatorio, NO Vigía
                 */
                foreach ($responsablesAgrupados as $grupo => $data):
                    // Determinar si mostrar esta sección según estándares
                    $mostrarSeccion = true;
                    $mensajeNoAplica = '';

                    if ($grupo === 'vigia' && $estandares > 7) {
                        // Vigía NO aplica para 21 y 60 estándares
                        $mostrarSeccion = false;
                    } elseif ($grupo === 'copasst' && $estandares <= 7) {
                        // COPASST NO aplica para 7 estándares
                        $mostrarSeccion = false;
                    } elseif ($grupo === 'convivencia' && $estandares <= 7) {
                        // Comité de Convivencia: aunque es obligatorio por Ley 1010/2006,
                        // para 7 estándares se simplifica y no se exige en el formulario
                        $mostrarSeccion = false;
                    } elseif ($grupo === 'brigada' && $estandares <= 7) {
                        // Brigada de Emergencias: para 7 estándares no se exige formalmente
                        $mostrarSeccion = false;
                    }

                    // Mostrar solo secciones que aplican o que tienen items
                    if ($mostrarSeccion && (!empty($data['items']) || in_array($grupo, ['direccion', 'vigia', 'copasst', 'convivencia', 'brigada']))):
                ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><?= esc($data['titulo']) ?></h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($data['items'])): ?>
                                <p class="text-muted text-center mb-0">
                                    <i class="bi bi-person-plus me-1"></i>
                                    No hay responsables registrados en esta categoría
                                </p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Rol</th>
                                                <th>Nombre</th>
                                                <th>Documento</th>
                                                <th>Cargo</th>
                                                <th width="100">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['items'] as $resp): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-primary"><?= esc($resp['nombre_rol']) ?></span>
                                                    </td>
                                                    <td>
                                                        <strong><?= esc($resp['nombre_completo']) ?></strong>
                                                        <?php if (!empty($resp['email'])): ?>
                                                            <br><small class="text-muted"><?= esc($resp['email']) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= esc($resp['tipo_documento']) ?> <?= esc($resp['numero_documento']) ?>
                                                    </td>
                                                    <td><?= esc($resp['cargo']) ?></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="<?= base_url('responsables-sst/' . $cliente['id_cliente'] . '/editar/' . $resp['id_responsable']) ?>"
                                                               class="btn btn-outline-primary" title="Editar">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-outline-danger btn-eliminar"
                                                                    data-id="<?= $resp['id_responsable'] ?>"
                                                                    data-nombre="<?= esc($resp['nombre_completo']) ?>"
                                                                    title="Eliminar">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </div>
    </div>

    <!-- Modal confirmar eliminación -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar a <strong id="nombreEliminar"></strong>?</p>
                    <p class="text-muted small">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formEliminar" method="POST" style="display: inline;">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Eliminar responsable
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const nombre = this.dataset.nombre;

                document.getElementById('nombreEliminar').textContent = nombre;
                document.getElementById('formEliminar').action =
                    '<?= base_url('responsables-sst/' . $cliente['id_cliente'] . '/eliminar') ?>/' + id;

                new bootstrap.Modal(document.getElementById('modalEliminar')).show();
            });
        });

        // Migrar datos
        const btnMigrar = document.getElementById('btnMigrar');
        if (btnMigrar) {
            btnMigrar.addEventListener('click', function() {
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Migrando...';

                fetch('<?= base_url('responsables-sst/' . $cliente['id_cliente'] . '/migrar') ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error al migrar');
                        this.disabled = false;
                        this.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Migrar datos existentes';
                    }
                });
            });
        }
    });
    </script>
</body>
</html>
