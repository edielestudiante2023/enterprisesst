<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Enterprise SST</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Estilos personalizados -->
    <style>
        body {
            background-color: #F9F9F9; /* Fondo claro */
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            display: flex;
            background-color: #FFFFFF;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        .logos-box {
            background-color: #F3F3F3;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            width: 40%;
            text-align: center;
        }
        .logos-box img {
            max-width: 80%;
            margin-bottom: 20px;
        }
        .login-box {
            padding: 40px;
            width: 60%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-box h2 {
            margin-bottom: 20px;
            color: #333333;
            text-align: center;
            font-weight: bold;
        }
        .btn-custom {
            background-color: #4CAF93;
            border: none;
            color: #FFFFFF;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            background-color: #43A086;
        }
        .form-control, .form-select {
            background-color: #FFFFFF;
            border: 1px solid #CCCCCC;
            color: #333333;
        }
        .form-control:focus, .form-select:focus {
            border-color: #4CAF93;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 147, 0.25);
        }
        .form-control::placeholder {
            color: #AAAAAA;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Caja de Logos -->
    <div class="logos-box">
        <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Logo Enterprise SST">
        <img src="<?= base_url('uploads/logocycloid.png') ?>" alt="Logo Cycloid">
        <img src="<?= base_url('uploads/logosst.png') ?>" alt="Logo SST">
    </div>

    <!-- Caja de Login -->
    <div class="login-box">
        <div style="text-align: center;">
        <h2>Aplicativo Enterprisesst</h2>
        <h4>Inicio de Sesión Empresas </h4>
        </div>

        <br>
        <br>
        <!-- Mensaje de error -->
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('msg') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Formulario de login -->
        <form action="<?= base_url('/loginPost') ?>" method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico:</label>
                <input type="email" name="username" id="email" class="form-control" placeholder="Ingrese su correo" required aria-label="Correo Electrónico">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña:</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Ingrese su contraseña" required aria-label="Contraseña">
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Rol:</label>
                <select name="role" id="role" class="form-select" required aria-label="Seleccionar Rol">
                    <option value="client">Cliente</option>
                    <option value="consultant">Consultor</option>
                </select>
            </div>

            <button type="submit" class="btn btn-custom w-100 py-2">Iniciar Sesión</button>

            <div style="text-align: center; color:grey">

            <br> <br>
        <h6>Empowered By Cycloid Talent S.A.S.</h6>
        
        </div>
        </form>
    </div>

    
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
