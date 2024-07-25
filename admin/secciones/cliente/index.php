<?php
include("../../bd.php");

if(isset($_GET['txtID'])){
    // Borrar el registro con el ID correspondiente
    $txtID=(isset($_GET['txtID']))?$_GET['txtID']:"";
    $sentencia=$conexion->prepare("DELETE FROM tbl_cliente WHERE ID=:id");
    $sentencia->bindParam(":id",$txtID);
    $sentencia->execute();
}

//seleccionar registros
$sentencia = $conexion->prepare("SELECT * FROM tbl_cliente");
$sentencia->execute();
$lista_clientes = $sentencia->fetchAll(PDO::FETCH_ASSOC);

include("../../templates/header.php");
?>
<div class="card">
    <div class="card-header">

        <a name="" id="" class="btn btn-primary" href="crear.php" role="button">Agregar Registros</a>
    </div>
    <div class="card-body">
    </div>
    <div class="table-responsive-sm">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Usuario</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Contraseña</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lista_clientes as $registros) { ?>
                    <tr class="">
                        <td scope="row"><?php echo $registros['cliente']; ?></td>
                        <td><?php echo $registros['correo']; ?></td>
                        <td><?php echo $registros['password']; ?></td>
                        <td>
                            <a name="" id="" class="btn btn-info" href="editar.php?txtID=<?php echo $registros['ID']; ?>" role="button">Editar</a>
                            |
                            <a name="" id="" class="btn btn-danger" href="index.php?txtID=<?php echo $registros['ID']; ?>" role="button">Eliminar</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>



<?php
include("../../templates/footer.php");
?>