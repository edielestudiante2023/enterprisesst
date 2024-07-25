<?php
$servidor="localhost";
$baseDeDatos="website";
$usuario="Admon";
$contrasenia="Colombia2024";

try{

    $conexion=new PDO("mysql:host=$servidor;dbname=$baseDeDatos",$usuario,$contrasenia);

    

}catch (Exception $error) {
    echo $error->getMessage();
}


?>