<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Agregar Cliente<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container py-4 fade-in">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('/dashboardconsultant') ?>"><i class="bi bi-house me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/listClients') ?>"><i class="bi bi-people me-1"></i>Clientes</a></li>
            <li class="breadcrumb-item active">Agregar Cliente</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-person-plus me-2"></i>Agregar Nuevo Cliente</h2>
        <a href="<?= base_url('/listClients') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    <?php if (session()->getFlashdata('msg')) : ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('msg') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('/addClientPost') ?>" method="post" enctype="multipart/form-data">

        <!-- Sección 1: Información de la Empresa -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Información de la Empresa</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">NIT Cliente <span class="text-danger">*</span></label>
                        <input type="text" name="nit_cliente" class="form-control" placeholder="Ej: 900.123.456-7" required>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Nombre del Tercero (Como está en el RUT) <span class="text-danger">*</span></label>
                        <input type="text" name="nombre_cliente" class="form-control" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Código de Actividad Económica <span class="text-danger">*</span></label>
                        <input type="text" name="codigo_actividad_economica" class="form-control" placeholder="Ej: 7020" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ciudad del Cliente <span class="text-danger">*</span></label>
                        <input type="text" name="ciudad_cliente" class="form-control" required>
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
                        <label class="form-label">Usuario <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="usuario" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" name="password" id="passwordField" class="form-control" required>
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
                        <label class="form-label">Correo del Cliente <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="correo_cliente" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Teléfono 1 <span class="text-danger">*</span></label>
                        <input type="text" name="telefono_1_cliente" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Teléfono 2</label>
                        <input type="text" name="telefono_2_cliente" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Dirección <span class="text-danger">*</span></label>
                        <input type="text" name="direccion_cliente" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Persona de Contacto Compras <span class="text-danger">*</span></label>
                        <input type="text" name="persona_contacto_compras" class="form-control" required>
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
                        <label class="form-label">Nombre del Representante Legal <span class="text-danger">*</span></label>
                        <input type="text" name="nombre_rep_legal" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cédula del Representante Legal <span class="text-danger">*</span></label>
                        <input type="text" name="cedula_rep_legal" class="form-control" required>
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
                        <label class="form-label">Fecha Inicio del Contrato <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_ingreso" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha Fin de Contrato <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_fin_contrato" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Estado <span class="text-danger">*</span></label>
                        <select name="estado" class="form-select" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="pendiente">Pendiente</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Consultor Asignado <span class="text-danger">*</span></label>
                        <select name="id_consultor" class="form-select" required>
                            <option value="" selected disabled>Seleccione un Consultor</option>
                            <?php foreach ($consultants as $consultant) : ?>
                                <?php if ($consultant['id_consultor'] != 1) : ?>
                                    <option value="<?= $consultant['id_consultor'] ?>"><?= $consultant['nombre_consultor'] ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipo de Servicio</label>
                        <select name="estandares" class="form-select">
                            <option value="7A">7A</option>
                            <option value="7B">7B</option>
                            <option value="7C">7C</option>
                            <option value="7D">7D</option>
                            <option value="7E">7E</option>
                            <option value="21A">21A</option>
                            <option value="21B">21B</option>
                            <option value="21C">21C</option>
                            <option value="21D">21D</option>
                            <option value="21E">21E</option>
                            <option value="60A">60A</option>
                            <option value="60B">60B</option>
                            <option value="60C">60C</option>
                            <option value="60D">60D</option>
                            <option value="60E">60E</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Firma del Representante Legal</label>
                        <input type="file" name="firma_representante_legal" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón Submit -->
        <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
            <a href="<?= base_url('/listClients') ?>" class="btn btn-outline-secondary me-md-2">
                <i class="bi bi-x-lg me-1"></i>Cancelar
            </a>
            <button type="submit" class="btn btn-success px-5">
                <i class="bi bi-check-lg me-1"></i>Agregar Cliente
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
