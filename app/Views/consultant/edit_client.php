<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Editar Cliente<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container py-4 fade-in">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('/dashboardconsultant') ?>"><i class="bi bi-house me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/listClients') ?>"><i class="bi bi-people me-1"></i>Clientes</a></li>
            <li class="breadcrumb-item active">Editar: <?= esc($client['nombre_cliente']) ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Cliente</h2>
        <a href="<?= base_url('/listClients') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

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

    <form action="<?= base_url('/updateClient/' . $client['id_cliente']) ?>" method="post" enctype="multipart/form-data">

        <!-- Sección 1: Información de la Empresa -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Información de la Empresa</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">NIT Cliente</label>
                        <input type="text" name="nit_cliente" value="<?= esc($client['nit_cliente']) ?>" class="form-control">
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Nombre del Tercero (Como está en el RUT)</label>
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
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Acceso al Sistema</h5>
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
                        <label class="form-label">Nueva Contraseña <small class="text-muted">(dejar vacío para no cambiar)</small></label>
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
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-telephone me-2"></i>Información de Contacto</h5>
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
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-vcard me-2"></i>Representante Legal</h5>
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

        <!-- Sección 5: Contrato y Configuración -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Contrato y Configuración</h5>
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
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="activo" <?= $client['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= $client['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            <option value="pendiente" <?= $client['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
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
                    <div class="col-md-6 mb-3">
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

        <!-- Botones -->
        <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
            <a href="<?= base_url('/listClients') ?>" class="btn btn-outline-secondary me-md-2">
                <i class="bi bi-x-lg me-1"></i>Cancelar
            </a>
            <button type="submit" class="btn btn-primary px-5">
                <i class="bi bi-check-lg me-1"></i>Actualizar Cliente
            </button>
        </div>

    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const field = document.getElementById('passwordField');
        const icon = document.getElementById('toggleIcon');
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        } else {
            field.type = 'password';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        }
    });
</script>
<?= $this->endSection() ?>
