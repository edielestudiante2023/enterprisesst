<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $modo === 'crear' ? 'Nueva Empresa Consultora' : 'Editar Empresa Consultora' ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; color: #343a40; }
        .form-container { max-width: 800px; margin: 30px auto; }
        .section-title { border-bottom: 2px solid #007bff; padding-bottom: 5px; margin-bottom: 15px; color: #007bff; }
    </style>
</head>
<body>

<?php
    $isSuperAdmin = session()->get('is_superadmin');
    $backUrl = $isSuperAdmin ? '/admin/empresas-consultoras' : '/admin/dashboard';
?>

<nav style="background-color: white; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; max-width: 800px; margin: 0 auto; padding: 0 20px;">
        <a href="<?= base_url($backUrl) ?>" class="btn btn-outline-secondary btn-sm">Volver</a>
        <h4 style="margin: 0;"><?= $modo === 'crear' ? 'Nueva Empresa Consultora' : 'Editar Empresa' ?></h4>
        <div></div>
    </div>
</nav>

<div class="form-container">

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>

    <?php
        $action = $modo === 'crear'
            ? base_url('/admin/empresas-consultoras/guardar')
            : base_url('/admin/empresas-consultoras/actualizar/' . ($empresa['id_empresa_consultora'] ?? ''));
    ?>

    <form action="<?= $action ?>" method="post" enctype="multipart/form-data">

        <h5 class="section-title">Datos de la Empresa</h5>

        <div class="form-row">
            <div class="form-group col-md-8">
                <label>Razon Social *</label>
                <input type="text" name="razon_social" class="form-control" required
                       value="<?= esc($empresa['razon_social'] ?? old('razon_social')) ?>">
            </div>
            <div class="form-group col-md-4">
                <label>NIT</label>
                <input type="text" name="nit" class="form-control"
                       value="<?= esc($empresa['nit'] ?? old('nit')) ?>" placeholder="900000000-1">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Direccion</label>
                <input type="text" name="direccion" class="form-control"
                       value="<?= esc($empresa['direccion'] ?? old('direccion')) ?>">
            </div>
            <div class="form-group col-md-3">
                <label>Telefono</label>
                <input type="text" name="telefono" class="form-control"
                       value="<?= esc($empresa['telefono'] ?? old('telefono')) ?>">
            </div>
            <div class="form-group col-md-3">
                <label>Correo</label>
                <input type="email" name="correo" class="form-control"
                       value="<?= esc($empresa['correo'] ?? old('correo')) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Logo</label>
                <input type="file" name="logo" class="form-control-file" accept="image/*">
                <?php if (!empty($empresa['logo'])): ?>
                    <small class="text-muted">
                        Actual: <img src="<?= base_url($empresa['logo']) ?>" style="height:30px; vertical-align:middle;">
                    </small>
                <?php endif; ?>
            </div>
            <div class="form-group col-md-2">
                <label>Color primario</label>
                <input type="color" name="color_primario" class="form-control"
                       value="<?= esc($empresa['color_primario'] ?? '#007bff') ?>" style="height: 38px;">
            </div>
            <div class="form-group col-md-3">
                <label>Fecha inicio contrato</label>
                <input type="date" name="fecha_inicio_contrato" class="form-control"
                       value="<?= esc($empresa['fecha_inicio_contrato'] ?? old('fecha_inicio_contrato')) ?>">
            </div>
            <div class="form-group col-md-3">
                <label>Plan</label>
                <input type="text" name="plan" class="form-control" placeholder="basico, premium, etc."
                       value="<?= esc($empresa['plan'] ?? old('plan')) ?>">
            </div>
        </div>

        <?php if ($isSuperAdmin && $modo === 'editar'): ?>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Estado</label>
                <select name="estado" class="form-control">
                    <option value="activo" <?= ($empresa['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="suspendido" <?= ($empresa['estado'] ?? '') === 'suspendido' ? 'selected' : '' ?>>Suspendido</option>
                    <option value="inactivo" <?= ($empresa['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($modo === 'crear'): ?>
        <h5 class="section-title mt-4">Primer Usuario Admin (opcional)</h5>
        <p class="text-muted small">Si proporcionas email y nombre, se creara automaticamente un usuario admin para esta empresa con una contrasena temporal.</p>

        <div class="form-row">
            <div class="form-group col-md-5">
                <label>Nombre del admin</label>
                <input type="text" name="nombre_admin" class="form-control" placeholder="Ej: Ingeniero Pepito">
            </div>
            <div class="form-group col-md-5">
                <label>Email del admin</label>
                <input type="email" name="email_admin" class="form-control" placeholder="pepito@suempresa.com">
            </div>
        </div>
        <?php endif; ?>

        <hr>
        <button type="submit" class="btn btn-primary btn-lg btn-block">
            <?= $modo === 'crear' ? 'Crear Empresa' : 'Guardar Cambios' ?>
        </button>

    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
