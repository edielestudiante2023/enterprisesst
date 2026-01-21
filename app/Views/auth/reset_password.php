<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Enterprise SST</title>
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

        .password-requirements {
            font-size: 0.85rem;
            color: #666;
            margin-top: 8px;
        }

        .password-match {
            font-size: 0.85rem;
            margin-top: 5px;
        }

        .match-success {
            color: #28a745;
        }

        .match-error {
            color: #dc3545;
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-left: none;
            border-radius: 0 12px 12px 0;
            cursor: pointer;
        }

        .form-control.with-toggle {
            border-radius: 12px 0 0 12px;
            border-right: none;
        }
    </style>
</head>
<body>

<div class="main-container">
    <div class="logo-container">
        <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo">
    </div>

    <div class="header-icon">🔑</div>
    <h2 class="title">Crear Nueva Contraseña</h2>
    <p class="subtitle">Ingresa tu nueva contraseña. Asegúrate de que sea segura.</p>

    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-danger-custom mb-4">
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('/reset-password-post') ?>" method="post" id="resetForm">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= $token ?>">

        <div class="mb-4">
            <label for="password" class="form-label">Nueva Contraseña</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control with-toggle"
                       placeholder="Mínimo 6 caracteres" required minlength="6">
                <span class="input-group-text" onclick="togglePassword('password', this)">
                    👁️
                </span>
            </div>
            <div class="password-requirements">
                Mínimo 6 caracteres
            </div>
        </div>

        <div class="mb-4">
            <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
            <div class="input-group">
                <input type="password" name="confirm_password" id="confirm_password" class="form-control with-toggle"
                       placeholder="Repite tu contraseña" required minlength="6">
                <span class="input-group-text" onclick="togglePassword('confirm_password', this)">
                    👁️
                </span>
            </div>
            <div id="passwordMatch" class="password-match"></div>
        </div>

        <button type="submit" class="btn btn-primary-custom" id="submitBtn">
            Guardar Nueva Contraseña
        </button>
    </form>

    <div class="back-link">
        <a href="<?= base_url('/login') ?>">← Volver al inicio de sesión</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '🙈';
        } else {
            input.type = 'password';
            btn.textContent = '👁️';
        }
    }

    // Validar que las contraseñas coincidan
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const matchDiv = document.getElementById('passwordMatch');
    const submitBtn = document.getElementById('submitBtn');

    function checkMatch() {
        if (confirmPassword.value === '') {
            matchDiv.textContent = '';
            return;
        }

        if (password.value === confirmPassword.value) {
            matchDiv.textContent = '✓ Las contraseñas coinciden';
            matchDiv.className = 'password-match match-success';
            submitBtn.disabled = false;
        } else {
            matchDiv.textContent = '✗ Las contraseñas no coinciden';
            matchDiv.className = 'password-match match-error';
            submitBtn.disabled = true;
        }
    }

    password.addEventListener('input', checkMatch);
    confirmPassword.addEventListener('input', checkMatch);

    // Validar antes de enviar
    document.getElementById('resetForm').addEventListener('submit', function(e) {
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
        }
    });
</script>

</body>
</html>
