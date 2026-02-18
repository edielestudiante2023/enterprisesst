<?php
/**
 * Script CLI: Actualizar SP sp_generar_carpetas_por_nivel en PRODUCCION
 * Ejecutar: php actualizar_sp_produccion.php
 * Fecha: 2026-02-17
 */

$host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
$port = 25060;
$db   = 'empresas_sst';
$user = 'cycloid_userdb';
$pass = 'AVNS_iDypWizlpMRwHIORJGG';

echo "=== Actualizar SP en PRODUCCION ===\n";

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false]
    );
    echo "Conexion OK\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Drop SP existente
$pdo->exec("DROP PROCEDURE IF EXISTS sp_generar_carpetas_por_nivel");
echo "SP eliminado\n";

// Leer archivo y extraer CREATE PROCEDURE ... END // (sin DELIMITER)
$sqlFile = file_get_contents(__DIR__ . '/app/SQL/sp/sp_04_generar_carpetas_por_nivel.sql');

// Extraer desde CREATE PROCEDURE hasta END // (el END final del procedure)
if (preg_match('/CREATE PROCEDURE.*?END\s*\/\//s', $sqlFile, $matches)) {
    // Quitar el // final
    $spSQL = rtrim($matches[0]);
    $spSQL = preg_replace('/\/\/\s*$/', '', $spSQL);

    $pdo->exec($spSQL);
    echo "SP creado exitosamente\n";
} else {
    echo "ERROR: No se pudo extraer el CREATE PROCEDURE del archivo\n";
    exit(1);
}

// Verificar
$stmt = $pdo->query("SHOW CREATE PROCEDURE sp_generar_carpetas_por_nivel");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$body = $row['Create Procedure'];

if (strpos($body, '2.5.1.1') !== false) {
    echo "VERIFICADO: SP contiene sub-carpeta 2.5.1.1\n";
} else {
    echo "ALERTA: SP NO contiene 2.5.1.1\n";
}

echo "\n=== SP actualizado correctamente ===\n";
