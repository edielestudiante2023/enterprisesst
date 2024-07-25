<?php
session_start();
if ($_POST) {
    include("./admin/bd.php");

    $cliente = trim((isset($_POST['cliente'])) ? $_POST['cliente'] : "");
    $password = trim((isset($_POST['password'])) ? $_POST['password'] : "");

    // Seleccionar registros
    $sentencia = $conexion->prepare("SELECT *, count(*) as n_cliente 
    FROM tbl_cliente
    WHERE cliente = :cliente
    GROUP BY cliente, password");
    $sentencia->bindParam(":cliente", $cliente);
    $sentencia->execute();

    $lista_cliente = $sentencia->fetch(PDO::FETCH_LAZY);

    // Verificar la contraseña
    if ($lista_cliente['n_cliente'] > 0 && password_verify($password, $lista_cliente['password'])) {
        $_SESSION['cliente'] = $lista_cliente['cliente'];
        $_SESSION['logueado'] = true;
        header("Location: index.php");

        print_r($_SESSION);

    } else {
        $mensaje = "Error: El cliente o contraseña son incorrectos";
    }
}


?>

<!doctype html>
<html lang="en">

<head>
    <title>Login</title>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
</head>

<body>
    <header>
        <!-- place navbar here -->
    </header>
    <main>
        <div class="container">
            <div class="row">
                <div class="col-4">
                    <br><br>
                </div>
                <div class="col-4">
                    <br><br>
                    <?php if (isset($mensaje)) { ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <strong><?php echo $mensaje; ?></strong>
                    </div> 
                    <?php } ?>
                    <div class="card">
                        <div class="card-header">Login</div>
                        <div class="card-body">
                            <script>
                                var alertList = document.querySelectorAll(".alert");
                                alertList.forEach(function(alert) {
                                    new bootstrap.Alert(alert);
                                });
                            </script>
                            <form action="" method="POST">
                                <div class="mb-3">
                                    <label for="cliente" class="form-label">cliente</label>
                                    <input type="text" class="form-control" name="cliente" id="cliente" aria-describedby="helpId" placeholder="" />
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" name="password" id="password" aria-describedby="helpId" placeholder="" />
                                    <br>
                                </div>
                                <input name="" id="" class="btn btn-primary" type="submit" value="Entrar" />
                            </form>
                            <div class="card-footer text-muted"></div>
                        </div>
                    </div>
                </div>
            </div>
    </main>
    <footer>
        <!-- place footer here -->
    </footer>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</body>

</html>
