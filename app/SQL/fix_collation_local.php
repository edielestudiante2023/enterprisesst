<?php
/**
 * Corregir collation en MySQL LOCAL (XAMPP)
 * Ejecutar: php app/SQL/fix_collation_local.php
 */

$host = 'localhost';
$port = 3306;
$user = 'root';
$pass = '';
$dbname = 'empresas_sst';

echo "=== Correccion de Collation - MySQL LOCAL ===\n\n";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Conexion establecida\n";

    // Cambiar collation de la base de datos
    $pdo->exec("ALTER DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "Base de datos: OK\n";

    // Obtener todas las tablas con unicode_ci
    $stmt = $pdo->query("
        SELECT TABLE_NAME
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = '$dbname'
        AND TABLE_TYPE = 'BASE TABLE'
        AND TABLE_COLLATION = 'utf8mb4_unicode_ci'
    ");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "\nTablas a convertir: " . count($tablas) . "\n";

    foreach ($tablas as $tabla) {
        try {
            $pdo->exec("ALTER TABLE `$tabla` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            echo "  $tabla: OK\n";
        } catch (PDOException $e) {
            echo "  $tabla: Error - " . $e->getMessage() . "\n";
        }
    }

    // Verificar resultado
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = '$dbname'
        AND TABLE_TYPE = 'BASE TABLE'
        AND TABLE_COLLATION = 'utf8mb4_unicode_ci'
    ");
    $restantes = $stmt->fetchColumn();

    echo "\n=== RESULTADO ===\n";
    if ($restantes == 0) {
        echo "TODAS las tablas convertidas correctamente\n";
    } else {
        echo "Quedan $restantes tablas pendientes\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
