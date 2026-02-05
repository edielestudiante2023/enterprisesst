<?php
/**
 * Corregir collation de todas las tablas de comites
 * Para que todas usen utf8mb4_general_ci
 */

$host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
$port = 25060;
$user = 'cycloid_userdb';
$pass = 'AVNS_iDypWizlpMRwHIORJGG';
$dbname = 'empresas_sst';

echo "<h1>Correccion de Collation - Tablas de Comites</h1>";
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

    // Tablas a convertir
    $tablas = [
        'tbl_procesos_electorales',
        'tbl_candidatos_comite',
        'tbl_participantes_comite',
        'tbl_jurados_eleccion',
        'tbl_votos_comite'
    ];

    foreach ($tablas as $tabla) {
        try {
            $sql = "ALTER TABLE `$tabla` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
            $pdo->exec($sql);
            echo "<p style='color: green;'>$tabla convertida a utf8mb4_general_ci</p>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                echo "<p style='color: orange;'>$tabla no existe (OK)</p>";
            } else {
                echo "<p style='color: orange;'>$tabla: " . $e->getMessage() . "</p>";
            }
        }
    }

    // Verificar collation de tablas relevantes
    echo "<h3>Collation actual de tablas:</h3>";
    $stmt = $pdo->query("SHOW TABLE STATUS WHERE Name LIKE 'tbl_%comite%' OR Name LIKE 'tbl_proceso%' OR Name LIKE 'tbl_tipos%' OR Name LIKE 'tbl_jurado%' OR Name LIKE 'tbl_voto%'");
    $tablas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5'>";
    echo "<tr style='background:#333;color:white;'><th>Tabla</th><th>Collation</th></tr>";
    foreach ($tablas as $t) {
        $color = ($t['Collation'] === 'utf8mb4_general_ci') ? 'green' : 'orange';
        echo "<tr><td>{$t['Name']}</td><td style='color:$color'>{$t['Collation']}</td></tr>";
    }
    echo "</table>";

    echo "<hr>";
    echo "<h2 style='color: green;'>Collation corregida en todas las tablas. Prueba el modulo nuevamente.</h2>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Error</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>
