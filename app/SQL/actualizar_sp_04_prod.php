<?php
/**
 * Actualiza el stored procedure sp_04_generar_carpetas_por_nivel en PRODUCCIÓN
 * con la nueva carpeta 1.2.4 (Reglamento de Higiene y Seguridad Industrial).
 * Ejecutar: php app/SQL/actualizar_sp_04_prod.php
 */

$configProd = [
    'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port' => '25060',
    'db'   => 'empresas_sst',
    'user' => 'cycloid_userdb',
    'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
];

$dsn = "mysql:host={$configProd['host']};port={$configProd['port']};dbname={$configProd['db']};charset=utf8mb4";
$opts = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_SSL_CA => true,
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
];

try {
    echo "Conectando a PRODUCCIÓN...\n";
    $pdo = new PDO($dsn, $configProd['user'], $configProd['pass'], $opts);
    echo "Conexión OK\n";

    // Leer el SP del archivo
    $sqlFile = __DIR__ . '/sp/sp_04_generar_carpetas_por_nivel.sql';
    $sqlRaw  = file_get_contents($sqlFile);

    // Extraer el cuerpo del CREATE PROCEDURE (entre DELIMITER // y DELIMITER ;)
    // Eliminamos las líneas DELIMITER y el USE statement
    $lines = explode("\n", $sqlRaw);
    $filtradas = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (str_starts_with($trimmed, 'DELIMITER') || str_starts_with($trimmed, 'USE ')) {
            continue;
        }
        // Eliminar el // al final del END
        if ($trimmed === '//') {
            continue;
        }
        $filtradas[] = $line;
    }
    $sql = implode("\n", $filtradas);

    // Separar DROP y CREATE PROCEDURE
    // DROP
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_generar_carpetas_por_nivel");
    echo "DROP PROCEDURE ejecutado\n";

    // Extraer solo el bloque CREATE PROCEDURE
    $inicio = strpos($sql, 'CREATE PROCEDURE');
    $fin    = strrpos($sql, 'END');
    if ($inicio === false || $fin === false) {
        throw new Exception("No se pudo extraer el CREATE PROCEDURE del archivo");
    }
    $createSQL = substr($sql, $inicio, $fin - $inicio + strlen('END'));

    $pdo->exec($createSQL);
    echo "CREATE PROCEDURE ejecutado OK ✓\n";

    // Verificar que existe
    $check = $pdo->query("SHOW PROCEDURE STATUS WHERE Name = 'sp_generar_carpetas_por_nivel'")->fetch(PDO::FETCH_ASSOC);
    if ($check) {
        echo "SP verificado en BD: " . $check['Name'] . " (modificado: " . $check['Modified'] . ")\n";
    } else {
        echo "ADVERTENCIA: SP no encontrado después del CREATE\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nSP actualizado correctamente en PRODUCCIÓN.\n";
