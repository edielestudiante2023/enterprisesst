<?php
/**
 * Corregir collation de TODAS las tablas
 */

$host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
$port = 25060;
$user = 'cycloid_userdb';
$pass = 'AVNS_iDypWizlpMRwHIORJGG';
$dbname = 'empresas_sst';

echo "<h1>Correccion de Collation - TODAS las tablas</h1>";
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

    // Obtener todas las tablas con collation unicode_ci
    $stmt = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$dbname' AND TABLE_COLLATION = 'utf8mb4_unicode_ci'");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<p>Tablas a convertir: " . count($tablas) . "</p>";
    echo "<ul>";

    foreach ($tablas as $tabla) {
        try {
            $sql = "ALTER TABLE `$tabla` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
            $pdo->exec($sql);
            echo "<li style='color: green;'>$tabla - OK</li>";
        } catch (PDOException $e) {
            echo "<li style='color: red;'>$tabla - Error: " . $e->getMessage() . "</li>";
        }
    }
    echo "</ul>";

    // Verificar resultado
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$dbname' AND TABLE_COLLATION = 'utf8mb4_unicode_ci'");
    $restantes = $stmt->fetchColumn();

    if ($restantes == 0) {
        echo "<h2 style='color: green;'>TODAS las tablas convertidas correctamente</h2>";
    } else {
        echo "<h2 style='color: orange;'>Quedan $restantes tablas pendientes</h2>";
    }

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Error</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>
