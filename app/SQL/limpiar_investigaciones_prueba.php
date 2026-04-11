<?php
/**
 * Limpiar registros de prueba de investigación de accidentes
 *
 * Uso LOCAL:       php app/SQL/limpiar_investigaciones_prueba.php
 * Uso PRODUCCION:  php app/SQL/limpiar_investigaciones_prueba.php --prod
 */

$isProd = in_array('--prod', $argv ?? []);

if ($isProd) {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $dbname   = 'empresas_sst';
    $user     = 'cycloid_userdb';
    $pass     = 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $sslMode  = true;
    echo "=== PRODUCCION ===\n";
} else {
    $host     = 'localhost';
    $port     = 3306;
    $dbname   = 'empresas_sst';
    $user     = 'root';
    $pass     = '';
    $sslMode  = false;
    echo "=== LOCAL ===\n";
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($sslMode) {
        $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
        $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $user, $pass, $opts);
    echo "Conexion OK\n\n";
} catch (PDOException $e) {
    die("Error conexion: " . $e->getMessage() . "\n");
}

// Contar antes de eliminar
$count = $pdo->query("SELECT COUNT(*) FROM tbl_investigacion_accidente")->fetchColumn();
echo "Registros en tbl_investigacion_accidente: $count\n";

$countT = $pdo->query("SELECT COUNT(*) FROM tbl_investigacion_testigos")->fetchColumn();
echo "Registros en tbl_investigacion_testigos: $countT\n";

$countE = $pdo->query("SELECT COUNT(*) FROM tbl_investigacion_evidencia")->fetchColumn();
echo "Registros en tbl_investigacion_evidencia: $countE\n";

$countM = $pdo->query("SELECT COUNT(*) FROM tbl_investigacion_medidas")->fetchColumn();
echo "Registros en tbl_investigacion_medidas: $countM\n";

// Eliminar (CASCADE borra testigos, evidencia, medidas)
$pdo->exec("DELETE FROM tbl_investigacion_accidente");
echo "\n[OK] Todos los registros eliminados (CASCADE aplica a tablas hijas)\n";

// Eliminar reportes asociados
$deleted = $pdo->exec("DELETE FROM tbl_reporte WHERE id_detailreport = 38 AND id_report_type = 7");
echo "[OK] Reportes eliminados de tbl_reporte: $deleted\n";

// Reset auto_increment
$pdo->exec("ALTER TABLE tbl_investigacion_accidente AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE tbl_investigacion_testigos AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE tbl_investigacion_evidencia AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE tbl_investigacion_medidas AUTO_INCREMENT = 1");
echo "[OK] AUTO_INCREMENT reseteado en las 4 tablas\n";
