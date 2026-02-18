<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Editar Cliente<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .estado-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .35rem .85rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: .8rem;
        letter-spacing: .03em;
    }
    .estado-activo   { background: #d1fae5; color: #065f46; }
    .estado-inactivo { background: #fee2e2; color: #991b1b; }
    .estado-pendiente{ background: #fef3c7; color: #92400e; }

    .action-card {
        border: 2px solid transparent;
        border-radius: 14px;
        transition: box-shadow .2s, transform .2s;
    }
    .action-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.12); transform: translateY(-2px); }
    .action-card.card-reactivar  { border-color: #10b981; }
    .action-card.card-retirar    { border-color: #ef4444; }
    .action-card.card-pendiente  { border-color: #f59e0b; }

    .action-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
    }
    .icon-reactivar  { background: #d1fae5; color: #059669; }
    .icon-retirar    { background: #fee2e2; color: #dc2626; }
    .icon-pendiente  { background: #fef3c7; color: #d97706; }

    .section-card .card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e2e8f0;
    }
    .form-label { font-weight: 500; font-size: .875rem; color: #374151; }
</style>

<div class="container py-4 fade-in">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('/dashboardconsultant') ?>"><i class="bi bi-house me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/listClients') ?>"><i class="bi bi-people me-1"></i>Clientes</a></li>
            <li class="breadcrumb-item active">Editar: <?= esc($client['nombre_cliente']) ?></li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <h2 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Cliente</h2>
            <?php
                $estadoActual = $client['estado'] ?? 'pendiente';
                $estadoClass  = ['activo' => 'estado-activo', 'inactivo' => 'estado-inactivo', 'pendiente' => 'estado-pendiente'][$estadoActual] ?? 'estado-pendiente';
                $estadoIcon   = ['activo' => 'bi-check-circle-fill', 'inactivo' => 'bi-x-circle-fill', 'pendiente' => 'bi-clock-fill'][$estadoActual] ?? 'bi-clock-fill';
                $estadoLabel  = ['activo' => 'Activo', 'inactivo' => 'Inactivo', 'pendiente' => 'Pendiente'][$estadoActual] ?? 'Pendiente';
            ?>
            <span class="estado-badge <?= $estadoClass ?>">
                <i class="bi <?= $estadoIcon ?>"></i> <?= $estadoLabel ?>
            </span>
        </div>
        <a href="<?= base_url('/listClients') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    <!-- Alertas flash -->
    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('msg') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════
         PANEL DE ACCIONES DE ESTADO (fuera del form)
         ══════════════════════════════════════════════ -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header py-3">
            <h5 class="mb-0"><i class="bi bi-sliders me-2 text-primary"></i>Acciones de Estado del Cliente</h5>
            <small class="text-muted">Cada botón tiene efecto inmediato sobre la base de datos y las tablas relacionadas</small>
        </div>
        <div class="card-body">
            <div class="row g-3">

                <!-- Botón Reactivar -->
                <div class="col-md-4">
                    <div class="action-card card-reactivar card h-100 border-0 shadow-sm p-3">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="action-icon icon-reactivar flex-shrink-0">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 text-success fw-bold">Reactivar Cliente</h6>
                                <p class="text-muted small mb-0">Pone estado <strong>activo</strong> y <span class="text-danger">borra todo el historial</span> de actividades (PTA, capacitaciones, pendientes). El cliente empieza desde cero.</p>
                            </div>
                        </div>
                        <form action="<?= base_url('/reactivarCliente/' . $client['id_cliente']) ?>" method="post" class="mt-auto"
                              onsubmit="return confirmAccion(this, '¿Reactivar cliente?', 'Se BORRARÁ todo el historial de actividades (PTA, capacitaciones, pendientes). Esta acción no se puede deshacer.', 'success')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reactivar
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Botón Retirar -->
                <div class="col-md-4">
                    <div class="action-card card-retirar card h-100 border-0 shadow-sm p-3">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="action-icon icon-retirar flex-shrink-0">
                                <i class="bi bi-door-open"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 text-danger fw-bold">Retirar Cliente</h6>
                                <p class="text-muted small mb-0">Pone estado <strong>inactivo</strong> y marca todas las actividades abiertas como <em>CERRADA POR FIN CONTRATO</em>. El historial queda conservado.</p>
                            </div>
                        </div>
                        <form action="<?= base_url('/retirarCliente/' . $client['id_cliente']) ?>" method="post" class="mt-auto"
                              onsubmit="return confirmAccion(this, '¿Retirar cliente?', 'Se marcará como INACTIVO y todas sus actividades quedarán como CERRADA POR FIN CONTRATO.', 'warning')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-door-open me-1"></i> Retirar Cliente
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Botón Pendiente -->
                <div class="col-md-4">
                    <div class="action-card card-pendiente card h-100 border-0 shadow-sm p-3">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="action-icon icon-pendiente flex-shrink-0">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 text-warning fw-bold">Marcar Pendiente</h6>
                                <p class="text-muted small mb-0">Solo cambia el estado a <strong>pendiente</strong>. No toca actividades ni registros relacionados.</p>
                            </div>
                        </div>
                        <form action="<?= base_url('/marcarPendiente/' . $client['id_cliente']) ?>" method="post" class="mt-auto"
                              onsubmit="return confirmAccion(this, '¿Marcar como pendiente?', 'El cliente quedará en estado PENDIENTE. No se modificarán actividades.', 'info')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-clock-history me-1"></i> Marcar Pendiente
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         CERTIFICADOS Y COMUNICACIONES
         ══════════════════════════════════════════════ -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header py-3">
            <h5 class="mb-0"><i class="bi bi-patch-check me-2 text-success"></i>Certificados y Comunicaciones</h5>
            <small class="text-muted">Documentos oficiales que se envían por correo electrónico al cliente</small>
        </div>
        <div class="card-body">
            <div class="row g-3">

                <!-- Botón Paz y Salvo -->
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm p-3" style="border-left:4px solid #10b981!important;">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:44px;height:44px;background:#d1fae5;">
                                <i class="bi bi-patch-check-fill text-success fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold" style="color:#065f46;">Emitir Paz y Salvo</h6>
                                <p class="text-muted small mb-0">
                                    Verifica que no haya actividades abiertas en PTA, Cronograma ni Pendientes,
                                    y envía el certificado por email al cliente con copia al consultor y a Cycloid Talent.
                                </p>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success w-100 mt-auto"
                                data-bs-toggle="modal" data-bs-target="#modalPazYSalvo">
                            <i class="bi bi-envelope-check me-1"></i> Emitir Paz y Salvo
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal de confirmación Paz y Salvo -->
    <div class="modal fade" id="modalPazYSalvo" tabindex="-1" aria-labelledby="modalPazYSalvoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background:linear-gradient(135deg,#0f172a,#1e3a5f);color:#fff;">
                    <h5 class="modal-title" id="modalPazYSalvoLabel">
                        <i class="bi bi-patch-check-fill me-2 text-success"></i>Confirmar Paz y Salvo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Se enviará el <strong>Paz y Salvo por Todo Concepto</strong> a las siguientes direcciones:</p>

                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item px-0">
                            <span class="badge bg-primary me-2">Para</span>
                            <strong><?= esc($client['correo_cliente']) ?></strong>
                            <small class="text-muted ms-1">(cliente)</small>
                        </li>
                        <?php
                            $consultorAsignado = null;
                            foreach ($consultants as $c) {
                                if ($c['id_consultor'] == $client['id_consultor']) {
                                    $consultorAsignado = $c;
                                    break;
                                }
                            }
                            if ($consultorAsignado && !empty($consultorAsignado['correo_consultor'])):
                        ?>
                        <li class="list-group-item px-0">
                            <span class="badge bg-secondary me-2">CC</span>
                            <?= esc($consultorAsignado['correo_consultor']) ?>
                            <small class="text-muted ms-1">(consultor asignado)</small>
                        </li>
                        <?php endif; ?>
                        <li class="list-group-item px-0">
                            <span class="badge bg-secondary me-2">CC</span>
                            businesscycloidtalent@gmail.com
                        </li>
                        <li class="list-group-item px-0">
                            <span class="badge bg-secondary me-2">CC</span>
                            diana.cuestas@cycloidtalent.com
                        </li>
                    </ul>

                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-0 py-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <small>Si hay actividades abiertas, el sistema rechazará la emisión con un mensaje de error.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Cancelar
                    </button>
                    <form action="<?= base_url('/cliente/paz-y-salvo/' . $client['id_cliente']) ?>" method="post" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-envelope-check me-1"></i> Sí, emitir y enviar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         FORMULARIO DE EDICIÓN DE DATOS
         ══════════════════════════════════════════════ -->
    <form action="<?= base_url('/updateClient/' . $client['id_cliente']) ?>" method="post" enctype="multipart/form-data">

        <!-- Sección 1: Información de la Empresa -->
        <div class="card mb-4 section-card shadow-sm">
            <div class="card-header py-3">
                <h5 class="mb-0"><i class="bi bi-building me-2 text-primary"></i>Información de la Empresa</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">NIT Cliente</label>
                        <input type="text" name="nit_cliente" value="<?= esc($client['nit_cliente']) ?>" class="form-control">
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Nombre del Tercero <small class="text-muted fw-normal">(como está en el RUT)</small></label>
                        <input type="text" name="nombre_cliente" value="<?= esc($client['nombre_cliente']) ?>" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Código de Actividad Económica</label>
                        <input type="text" name="codigo_actividad_economica" value="<?= esc($client['codigo_actividad_economica']) ?>" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ciudad</label>
                        <input type="text" name="ciudad_cliente" value="<?= esc($client['ciudad_cliente']) ?>" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección 2: Acceso al Sistema -->
        <div class="card mb-4 section-card shadow-sm">
            <div class="card-header py-3">
                <h5 class="mb-0"><i class="bi bi-shield-lock me-2 text-primary"></i>Acceso al Sistema</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="usuario" value="<?= esc($client['usuario']) ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nueva Contraseña <small class="text-muted fw-normal">(dejar vacío para no cambiar)</small></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" name="password" id="passwordField" class="form-control" placeholder="••••••••">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" title="Mostrar/Ocultar contraseña">
                                <i class="bi bi-eye-slash" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección 3: Contacto -->
        <div class="card mb-4 section-card shadow-sm">
            <div class="card-header py-3">
                <h5 class="mb-0"><i class="bi bi-telephone me-2 text-primary"></i>Información de Contacto</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Correo del Cliente</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="correo_cliente" value="<?= esc($client['correo_cliente']) ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Teléfono 1</label>
                        <input type="text" name="telefono_1_cliente" value="<?= esc($client['telefono_1_cliente']) ?>" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Teléfono 2</label>
                        <input type="text" name="telefono_2_cliente" value="<?= esc($client['telefono_2_cliente'] ?? '') ?>" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion_cliente" value="<?= esc($client['direccion_cliente']) ?>" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Persona de Contacto Compras</label>
                        <input type="text" name="persona_contacto_compras" value="<?= esc($client['persona_contacto_compras']) ?>" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección 4: Representante Legal -->
        <div class="card mb-4 section-card shadow-sm">
            <div class="card-header py-3">
                <h5 class="mb-0"><i class="bi bi-person-vcard me-2 text-primary"></i>Representante Legal</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre del Representante Legal</label>
                        <input type="text" name="nombre_rep_legal" value="<?= esc($client['nombre_rep_legal']) ?>" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cédula del Representante Legal</label>
                        <input type="text" name="cedula_rep_legal" value="<?= esc($client['cedula_rep_legal']) ?>" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección 5: Contrato y Configuración (sin dropdown de estado) -->
        <div class="card mb-4 section-card shadow-sm">
            <div class="card-header py-3">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Contrato y Configuración</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha de Ingreso</label>
                        <input type="date" name="fecha_ingreso" value="<?= date('Y-m-d', strtotime($client['fecha_ingreso'])) ?>" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha Fin de Contrato</label>
                        <input type="date" name="fecha_fin_contrato" value="<?= esc($client['fecha_fin_contrato']) ?>" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tipo de Servicio</label>
                        <select name="estandares" class="form-select">
                            <?php
                            $opciones = ['7A','7B','7C','7D','7E','21A','21B','21C','21D','21E','60A','60B','60C','60D','60E'];
                            foreach ($opciones as $op): ?>
                                <option value="<?= $op ?>" <?= ($client['estandares'] ?? '') == $op ? 'selected' : '' ?>><?= $op ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Consultor Asignado</label>
                        <select name="id_consultor" class="form-select">
                            <?php foreach ($consultants as $consultant) : ?>
                                <option value="<?= $consultant['id_consultor'] ?>" <?= $consultant['id_consultor'] == $client['id_consultor'] ? 'selected' : '' ?>>
                                    <?= esc($consultant['nombre_consultor']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                        <?php if (!empty($client['logo'])): ?>
                            <div class="mt-2 p-2 border rounded d-inline-block bg-light">
                                <img src="<?= base_url('uploads/' . $client['logo']) ?>" alt="Logo actual" style="max-height: 80px;">
                                <small class="d-block text-muted mt-1">Logo actual</small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Firma del Representante Legal</label>
                        <input type="file" name="firma_representante_legal" class="form-control" accept="image/*">
                        <?php if (!empty($client['firma_representante_legal'])): ?>
                            <div class="mt-2 p-2 border rounded d-inline-block bg-light">
                                <img src="<?= base_url('uploads/' . $client['firma_representante_legal']) ?>" alt="Firma actual" style="max-height: 80px;">
                                <small class="d-block text-muted mt-1">Firma actual</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones del formulario -->
        <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
            <a href="<?= base_url('/listClients') ?>" class="btn btn-outline-secondary me-md-2">
                <i class="bi bi-x-lg me-1"></i>Cancelar
            </a>
            <button type="submit" class="btn btn-primary px-5">
                <i class="bi bi-check-lg me-1"></i>Actualizar Datos
            </button>
        </div>

    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Toggle contraseña
    document.getElementById('togglePassword').addEventListener('click', function () {
        const field = document.getElementById('passwordField');
        const icon  = document.getElementById('toggleIcon');
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        } else {
            field.type = 'password';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        }
    });

    // Confirmación con SweetAlert si está disponible, si no usa confirm() nativo
    function confirmAccion(form, titulo, mensaje, tipo) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: titulo,
                text: mensaje,
                icon: tipo === 'success' ? 'warning' : tipo,
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: tipo === 'success' ? '#10b981' : (tipo === 'warning' ? '#ef4444' : '#f59e0b'),
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
            return false;
        }
        return confirm(titulo + '\n\n' + mensaje);
    }
</script>
<?= $this->endSection() ?>
