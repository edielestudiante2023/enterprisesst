<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if(session()->getFlashdata('msg')):?>
        <div class="alert alert-warning">
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif;?>
    <form action="<?= base_url('/loginPost') ?>" method="post">
        <label>Correo Electrónico/Usuario:</label>
        <input type="text" name="username" required><br>

        <label>Contraseña:</label>
        <input type="password" name="password" required><br>

        <label>Rol:</label>
        <select name="role" required>
            <option value="client">Cliente</option>
            <option value="consultant">Consultor</option>
        </select><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
