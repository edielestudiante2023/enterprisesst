<?php
/**
 * Script CLI para crear tabla tbl_acta_visita_pta
 * Vinculación entre Acta de Visita y actividades PTA
 *
 * Uso:
 *   php app/SQL/migrate_acta_visita_pta.php          (local)
 *   DB_PROD_PASS=xxx php app/SQL/migrate_acta_visita_pta.php production
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la línea de comandos.');
}

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => '',
        'database' => 'empresas_sst',
        'ssl'      => false,
    ];
} elseif ($env === 'production') {
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'user'     => 'cycloid_userdb',
        'password' => getenv('DB_PROD_PASS') ?: '',
        'database' => 'empresas_sst',
        'ssl'      => true,
    ];
} else {
    die("Uso: php app/SQL/migrate_acta_visita_pta.php [local|production]\n");
}

echo "=== Migración SQL - Tabla tbl_acta_visita_pta ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$config['host']}:{$config['port']}\n";
echo "Database: {$config['database']}\n";
echo "---\n";

$mysqli = mysqli_init();

if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}

$connected = @$mysqli->real_connect(
    $config['host'],
    $config['user'],
    $config['password'],
    $config['database'],
    $config['port'],
    null,
    $config['ssl'] ? MYSQLI_CLIENT_SSL : 0
);

if (!$connected) {
    die("ERROR de conexión: " . $mysqli->connect_error . "\n");
}

echo "Conexión exitosa.\n\n";

function tableExists($mysqli, $database, $table) {
    $stmt = $mysqli->prepare(
        "SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?"
    );
    $stmt->bind_param('ss', $database, $table);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['cnt'] > 0;
}

$success = 0;
$skipped = 0;
$errors = 0;
$total = 0;

// ========== CREATE TABLE tbl_acta_visita_pta ==========

$total++;
echo "[{$total}] CREATE TABLE tbl_acta_visita_pta... ";

if (tableExists($mysqli, $config['database'], 'tbl_acta_visita_pta')) {
    echo "SKIP (ya existe)\n";
    $skipped++;
} else {
    $sql = "CREATE TABLE `tbl_acta_visita_pta` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_acta_visita` INT NOT NULL,
        `id_ptacliente` INT NOT NULL,
        `cerrada` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=cerrada en esta visita, 0=no cerrada',
        `justificacion_no_cierre` TEXT NULL COMMENT 'Razón por la que no se cerró (solo si cerrada=0)',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,

        CONSTRAINT `fk_acta_pta_visita`
            FOREIGN KEY (`id_acta_visita`) REFERENCES `tbl_acta_visita`(`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,

        CONSTRAINT `fk_acta_pta_cliente`
            FOREIGN KEY (`id_ptacliente`) REFERENCES `tbl_pta_cliente`(`id_ptacliente`)
            ON DELETE CASCADE ON UPDATE CASCADE,

        UNIQUE KEY `uk_acta_pta` (`id_acta_visita`, `id_ptacliente`),

        INDEX `idx_acta_pta_visita` (`id_acta_visita`),
        INDEX `idx_acta_pta_cliente` (`id_ptacliente`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Vinculación entre actas de visita y actividades PTA gestionadas'";

    if ($mysqli->query($sql)) {
        echo "OK\n";
        $success++;
    } else {
        echo "ERROR: " . $mysqli->error . "\n";
        $errors++;
    }
}

// ========== RESULTADO ==========

echo "\n=== RESULTADO ===\n";
echo "Exitosas: {$success}\n";
echo "Omitidas (ya existían): {$skipped}\n";
echo "Errores: {$errors}\n";
echo "Total: {$total}\n";

if ($errors === 0) {
    echo "MIGRACIÓN COMPLETADA SIN ERRORES.\n";
} else {
    echo "HAY ERRORES - REVISAR ANTES DE CONTINUAR.\n";
}

$mysqli->close();
