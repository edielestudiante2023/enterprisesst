<?php
/**
 * Script CLI para crear tablas del módulo Mantenimientos y Vencimientos
 * Uso: php crear_tablas_mantenimientos.php [local|production]
 *
 * Crea:
 *   - tbl_mantenimientos (catálogo de tipos de mantenimiento)
 *   - tbl_vencimientos_mantenimientos (registro de vencimientos por cliente)
 *   - INSERT datos iniciales en tbl_mantenimientos
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
    die("Uso: php crear_tablas_mantenimientos.php [local|production]\n");
}

echo "=== Migración SQL - Módulo Mantenimientos y Vencimientos ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$config['host']}:{$config['port']}\n";
echo "Database: {$config['database']}\n";
echo "---\n";

// Conectar
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

// ========== PARTE 1: CREATE TABLE tbl_mantenimientos ==========

$total++;
echo "[{$total}] CREATE TABLE tbl_mantenimientos... ";

$sql1 = "CREATE TABLE IF NOT EXISTS `tbl_mantenimientos` (
    `id_mantenimiento` INT AUTO_INCREMENT PRIMARY KEY,
    `detalle_mantenimiento` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql1)) {
    echo "OK\n";
    $success++;
} else {
    echo "ERROR: " . $mysqli->error . "\n";
    $errors++;
}

// ========== PARTE 2: INSERT datos iniciales ==========

$total++;
echo "[{$total}] INSERT datos iniciales en tbl_mantenimientos... ";

// Verificar si ya hay datos
$countResult = $mysqli->query("SELECT COUNT(*) as cnt FROM `tbl_mantenimientos`");
$count = $countResult->fetch_assoc()['cnt'];

if ($count > 0) {
    echo "SKIP (ya tiene {$count} registros)\n";
    $skipped++;
} else {
    $insertSql = "INSERT INTO `tbl_mantenimientos` (`detalle_mantenimiento`) VALUES
        ('Mantenimiento de extintores'),
        ('Recarga de extintores'),
        ('Mantenimiento de botiquines'),
        ('Mantenimiento de gabinetes contra incendio'),
        ('Mantenimiento de sistemas de alarma'),
        ('Mantenimiento de señalizacion'),
        ('Mantenimiento de equipos de comunicacion'),
        ('Mantenimiento de iluminacion de emergencia'),
        ('Fumigacion y control de plagas'),
        ('Mantenimiento de ascensores'),
        ('Mantenimiento electrico'),
        ('Mantenimiento hidraulico'),
        ('Limpieza de tanques de agua')";

    if ($mysqli->query($insertSql)) {
        echo "OK ({$mysqli->affected_rows} registros insertados)\n";
        $success++;
    } else {
        echo "ERROR: " . $mysqli->error . "\n";
        $errors++;
    }
}

// ========== PARTE 3: CREATE TABLE tbl_vencimientos_mantenimientos ==========

$total++;
echo "[{$total}] CREATE TABLE tbl_vencimientos_mantenimientos... ";

$sql2 = "CREATE TABLE IF NOT EXISTS `tbl_vencimientos_mantenimientos` (
    `id_vencimientos_mmttos` INT AUTO_INCREMENT PRIMARY KEY,
    `id_mantenimiento` INT NOT NULL,
    `id_cliente` INT NOT NULL,
    `id_consultor` INT NOT NULL,
    `fecha_vencimiento` DATE NOT NULL,
    `estado_actividad` ENUM('sin ejecutar','ejecutado','CERRADA','CERRADA POR FIN CONTRATO') NOT NULL DEFAULT 'sin ejecutar',
    `fecha_realizacion` DATE NULL,
    `observaciones` TEXT NULL,

    CONSTRAINT `fk_venc_mantenimiento`
        FOREIGN KEY (`id_mantenimiento`) REFERENCES `tbl_mantenimientos`(`id_mantenimiento`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_venc_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_venc_consultor`
        FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    INDEX `idx_venc_cliente` (`id_cliente`),
    INDEX `idx_venc_estado` (`estado_actividad`),
    INDEX `idx_venc_fecha` (`fecha_vencimiento`),
    INDEX `idx_venc_consultor` (`id_consultor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql2)) {
    echo "OK\n";
    $success++;
} else {
    echo "ERROR: " . $mysqli->error . "\n";
    $errors++;
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
