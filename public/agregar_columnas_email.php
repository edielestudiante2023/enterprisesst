<?php
/**
 * Agregar columnas de tracking de email a tbl_votantes_proceso
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'empresas_sst';

echo "<h1>Agregar columnas de email</h1>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Verificar si las columnas existen
    $columnas = $pdo->query("SHOW COLUMNS FROM tbl_votantes_proceso")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('email_enviado', $columnas)) {
        $pdo->exec("ALTER TABLE tbl_votantes_proceso ADD COLUMN email_enviado TINYINT(1) DEFAULT 0 AFTER email");
        echo "<p style='color:green'>Columna 'email_enviado' agregada</p>";
    } else {
        echo "<p>Columna 'email_enviado' ya existe</p>";
    }

    if (!in_array('fecha_email', $columnas)) {
        $pdo->exec("ALTER TABLE tbl_votantes_proceso ADD COLUMN fecha_email DATETIME DEFAULT NULL AFTER email_enviado");
        echo "<p style='color:green'>Columna 'fecha_email' agregada</p>";
    } else {
        echo "<p>Columna 'fecha_email' ya existe</p>";
    }

    echo "<h2 style='color:green'>Listo!</h2>";
    echo "<p><a href='comites-elecciones/proceso/1/censo'>Ir al censo de votantes</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
