<?php

include("../../bd.php");

if (isset($_GET['txtID'])) {
    // Recuperar los datos del ID correspondiente - seleccionado
    $txtID = (isset($_GET['txtID'])) ? $_GET['txtID'] : "";
    $sentencia = $conexion->prepare("SELECT * FROM tbl_usuarios WHERE ID=:id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $registro = $sentencia->fetch(PDO::FETCH_LAZY);

    $usuario = $registro['usuario'];
    $correo = $registro['correo'];
    $password = $registro['password'];
}

if ($_POST) {
    $txtID = trim((isset($_POST['txtID'])) ? $_POST['txtID'] : "");
    $usuario = trim((isset($_POST['usuario'])) ? $_POST['usuario'] : "");
    $correo = trim((isset($_POST['correo'])) ? $_POST['correo'] : "");
    $password = trim((isset($_POST['password'])) ? $_POST['password'] : "");

    if (!empty($password)) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $password_hashed = $registro['password']; // Mantener la contraseña antigua si no se proporciona una nueva
    }

    $sentencia = $conexion->prepare("UPDATE tbl_usuarios
    SET 
    usuario=:usuario,
    correo=:correo,
    password=:password
    WHERE ID=:id");

    $sentencia->bindParam(":usuario", $usuario);
    $sentencia->bindParam(":correo", $correo);
    $sentencia->bindParam(":password", $password_hashed);
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    
    $mensaje = "Registro modificado con éxito.";
    header("Location:index.php?mensaje=" . urlencode($mensaje));
}

include("../../templates/header.php");
?>

<div class="card">
    <div class="card-header">Usuario</div>
    <div class="card-body">
        <form action="" method="post">

            <div class="mb-3">
                <label for="txtID" class="form-label">ID:</label>
                <input readonly value="<?php echo $txtID; ?>" type="text" class="form-control" name="txtID" id="txtID" aria-describedby="helpId" placeholder="ID" />
            </div>

            <div class="mb-3">
                <label for="usuario" class="form-label">Nombre del usuario</label>
                <input value="<?php echo $usuario; ?>" type="text" class="form-control" name="usuario" id="usuario" aria-describedby="helpId" placeholder="Nombre del usuario" />
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input value="" type="password" class="form-control" name="password" id="password" aria-describedby="helpId" placeholder="Password" />
            </div>

            <div class="mb-3">
                <label for="correo" class="form-label">Correo</label>
                <input value="<?php echo $correo; ?>" type="email" class="form-control" name="correo" id="correo" aria-describedby="emailHelpId" placeholder="Correo" />
            </div>
            <button type="submit" class="btn btn-success">Actualizar</button>
            <a name="" id="" class="btn btn-primary" href="index.php" role="button">Cancelar</a>
        </form>
    </div>
</div>

<?php
include("../../templates/footer.php");
?>
