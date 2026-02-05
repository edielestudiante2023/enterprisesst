<?php
/**
 * Corregir collation de tabla tbl_candidatos_comite
 * Para coincidir con las demas tablas de la base de datos
 */

$host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
$port = 25060;
$user = 'cycloid_userdb';
$pass = 'AVNS_iDypWizlpMRwHIORJGG';
$dbname = 'empresas_sst';

echo "<h1>Correccion de Collation - tbl_candidatos_comite</h1>";
echo "<hr>";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::MYSQL_ATTR_SSL_CA => null,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<p style='color: green;'>Conexion establecida</p>";

    // Cambiar collation de la tabla completa
    $sql = "ALTER TABLE tbl_candidatos_comite
            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";

    $pdo->exec($sql);
    echo "<p style='color: green;'>Tabla convertida a utf8mb4_general_ci</p>";

    // Verificar collation actual
    $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'tbl_candidatos_comite'");
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Collation actual: <strong>" . $info['Collation'] . "</strong></p>";

    // Verificar collation de otras tablas para comparar
    echo "<h3>Collation de otras tablas:</h3>";
    $stmt = $pdo->query("SHOW TABLE STATUS WHERE Name IN ('tbl_procesos_electorales', 'tbl_cliente', 'tbl_tipos_comite')");
    $tablas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Tabla</th><th>Collation</th></tr>";
    foreach ($tablas as $t) {
        echo "<tr><td>{$t['Name']}</td><td>{$t['Collation']}</td></tr>";
    }
    echo "</table>";

    echo "<hr>";
    echo "<h2 style='color: green;'>Collation corregida. Prueba el modulo nuevamente.</h2>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Error</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>
