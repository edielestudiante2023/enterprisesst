<?php
/**
 * Script CLI para crear tablas del módulo Inspección de Señalización
 * Uso: php migrate_inspeccion_senalizacion.php [local|production]
 *
 * Crea:
 *   - tbl_inspeccion_senalizacion (tabla principal con calificación)
 *   - tbl_item_senalizacion (37 ítems fijos por inspección)
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
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'database' => 'empresas_sst',
        'ssl'      => true,
    ];
} else {
    die("Uso: php migrate_inspeccion_senalizacion.php [local|production]\n");
}

echo "=== Migración SQL - Módulo Inspección Señalización ===\n";
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

$success = 0;
$errors = 0;
$total = 0;

$createStatements = [
    [
        'desc' => 'CREATE TABLE tbl_inspeccion_senalizacion',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_senalizacion` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,
            `id_consultor` INT NOT NULL,
            `fecha_inspeccion` DATE NOT NULL,
            `observaciones` TEXT NULL,
            `calificacion` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Porcentaje 0-100',
            `descripcion_cualitativa` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Nivel crítico/bajo/medio/bueno/excelente',
            `conteo_no_aplica` INT NOT NULL DEFAULT 0,
            `conteo_no_cumple` INT NOT NULL DEFAULT 0,
            `conteo_parcial` INT NOT NULL DEFAULT 0,
            `conteo_total` INT NOT NULL DEFAULT 0,
            `ruta_pdf` VARCHAR(255) NULL,
            `estado` ENUM('borrador', 'completo') NOT NULL DEFAULT 'borrador',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT `fk_insp_senal_cliente`
                FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
                ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_insp_senal_consultor`
                FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
                ON DELETE RESTRICT ON UPDATE CASCADE,

            INDEX `idx_insp_senal_cliente` (`id_cliente`),
            INDEX `idx_insp_senal_consultor` (`id_consultor`),
            INDEX `idx_insp_senal_estado` (`estado`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_item_senalizacion',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_item_senalizacion` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_inspeccion` INT NOT NULL,
            `nombre_item` VARCHAR(100) NOT NULL COMMENT 'Nombre del ítem de señalización',
            `grupo` VARCHAR(100) NOT NULL COMMENT 'Categoría/grupo del ítem',
            `estado_cumplimiento` VARCHAR(30) NOT NULL DEFAULT 'NO CUMPLE' COMMENT 'NO APLICA, NO CUMPLE, CUMPLE PARCIALMENTE, CUMPLE TOTALMENTE',
            `foto` VARCHAR(255) NULL COMMENT 'Ruta foto evidencia',
            `orden` TINYINT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT `fk_item_senal_inspeccion`
                FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_senalizacion`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_item_senal_inspeccion` (`id_inspeccion`),
            INDEX `idx_item_senal_estado` (`estado_cumplimiento`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
];

foreach ($createStatements as $stmt) {
    $total++;
    echo "[{$total}] {$stmt['desc']}... ";
    if ($mysqli->query($stmt['sql'])) {
        echo "OK\n";
        $success++;
    } else {
        echo "ERROR: " . $mysqli->error . "\n";
        $errors++;
    }
}

echo "\n=== RESULTADO ===\n";
echo "Exitosas: {$success}\n";
echo "Errores: {$errors}\n";
echo "Total: {$total}\n";

if ($errors === 0) {
    echo "MIGRACIÓN COMPLETADA SIN ERRORES.\n";
} else {
    echo "HAY ERRORES - REVISAR ANTES DE CONTINUAR.\n";
}

$mysqli->close();
