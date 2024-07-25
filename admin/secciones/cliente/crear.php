<?php
include("../../bd.php");

if($_POST){
    $cliente = trim((isset($_POST['cliente'])) ? $_POST['cliente'] : "");
    $password = trim((isset($_POST['password'])) ? $_POST['password'] : "");
    $correo = trim((isset($_POST['correo'])) ? $_POST['correo'] : "");

    // Hash de la contraseña
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    $sentencia = $conexion->prepare("INSERT INTO `tbl_cliente` 
    (`ID`, `cliente`, `password`, `correo`) 
    VALUES (NULL, :cliente, :password, :correo);");

    $sentencia->bindParam(":cliente", $cliente);
    $sentencia->bindParam(":password", $password_hashed);
    $sentencia->bindParam(":correo", $correo);
    $sentencia->execute();

    $mensaje = "Registro agregado con éxito.";
    header("Location:index.php?mensaje=" . urlencode($mensaje));
}

include("../../templates/header.php");
?>

<div class="card">
    <div class="card-header">cliente</div>
    <div class="card-body">
        <form action="" method="post">

            <div class="mb-3">
                <label for="" class="form-label">Correo del cliente</label>
                <input type="email" class="form-control" name="cliente" id="cliente" aria-describedby="helpId" placeholder="Nombre del cliente" />
            </div>

            <div class="mb-3">
                <label for="" class="form-label">Nit (Sin dígito de verificación)</label>
                <input type="password" class="form-control" name="password" id="password" aria-describedby="helpId" placeholder="Password" />
            </div>

            <div class="mb-3">
                <label for="" class="form-label">Cliente</label>
                <input type="text" class="form-control" name="correo" id="correo" aria-describedby="emailHelpId" placeholder="Cliente" />
            </div>

            <button type="submit" class="btn btn-success">Agregar</button>
            <a name="" id="" class="btn btn-primary" href="index.php" role="button">Cancelar</a>
        </form>
    </div>
</div>

<?php
include("../../templates/footer.php");
?>
