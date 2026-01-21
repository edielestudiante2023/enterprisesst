<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Enterprise SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #bd9751 0%, #d4af37 25%, #f5f7fa 50%, #c3cfe2 75%, #bd9751 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            animation: backgroundShift 15s ease-in-out infinite;
        }

        @keyframes backgroundShift {
            0%, 100% {
                background: linear-gradient(135deg, #bd9751 0%, #d4af37 25%, #f5f7fa 50%, #c3cfe2 75%, #bd9751 100%);
            }
            50% {
                background: linear-gradient(135deg, #d4af37 0%, #bd9751 25%, #c3cfe2 50%, #f5f7fa 75%, #d4af37 100%);
            }
        }

        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(25px);
            border-radius: 25px;
            border: 2px solid rgba(189, 151, 81, 0.3);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
            padding: 50px;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-icon {
            font-size: 60px;
            text-align: center;
            margin-bottom: 20px;
        }

        .title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1c2437;
            text-align: center;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        .form-control {
            background: #ffffff;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #bd9751;
            box-shadow: 0 0 0 4px rgba(189, 151, 81, 0.2);
        }

        .form-label {
            font-weight: 600;
            color: #1c2437;
            margin-bottom: 8px;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #1c2437, #2c3e50);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            padding: 15px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(28, 36, 55, 0.3);
            background: linear-gradient(135deg, #2c3e50, #bd9751);
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: #bd9751;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: #1c2437;
        }

        .alert-success-custom {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(40, 167, 69, 0.2));
            border: none;
            border-left: 4px solid #28a745;
            border-radius: 12px;
            color: #155724;
        }

        .alert-danger-custom {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(220, 53, 69, 0.2));
            border: none;
            border-left: 4px solid #dc3545;
            border-radius: 12px;
            color: #721c24;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-container img {
            height: 80px;
        }
    </style>
</head>
<body>

<div class="main-container">
    <div class="logo-container">
        <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo">
    </div>

    <div class="header-icon">🔐</div>
    <h2 class="title">¿Olvidaste tu contraseña?</h2>
    <p class="subtitle">Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>

    <?php if (session()->getFlashdata('msg_success')): ?>
        <div class="alert alert-success-custom mb-4">
            <?= session()->getFlashdata('msg_success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-danger-custom mb-4">
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('/forgot-password') ?>" method="post">
        <?= csrf_field() ?>

        <div class="mb-4">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input type="email" name="email" id="email" class="form-control"
                   placeholder="Ingresa tu correo registrado" required autofocus>
        </div>

        <button type="submit" class="btn btn-primary-custom">
            Enviar Enlace de Recuperación
        </button>
    </form>

    <div class="back-link">
        <a href="<?= base_url('/login') ?>">← Volver al inicio de sesión</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
