<?php
/**
 * Script CLI para crear tablas del módulo Inspección de Botiquín Tipo B
 * Uso: php migrate_inspeccion_botiquin.php [local|production]
 *
 * Crea:
 *   - tbl_inspeccion_botiquin (tabla principal con datos generales + equipos especiales)
 *   - tbl_elemento_botiquin (32 elementos fijos por inspección)
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
    die("Uso: php migrate_inspeccion_botiquin.php [local|production]\n");
}

echo "=== Migración SQL - Módulo Inspección Botiquín Tipo B ===\n";
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
        'desc' => 'CREATE TABLE tbl_inspeccion_botiquin',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_botiquin` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,
            `id_consultor` INT NOT NULL,
            `fecha_inspeccion` DATE NOT NULL,
            `ubicacion_botiquin` VARCHAR(255) NULL,
            `foto_1` VARCHAR(255) NULL COMMENT 'Foto general del botiquin',
            `foto_2` VARCHAR(255) NULL COMMENT 'Foto general del botiquin 2',
            `instalado_pared` ENUM('SI','NO') NOT NULL DEFAULT 'SI',
            `libre_obstaculos` ENUM('SI','NO') NOT NULL DEFAULT 'SI',
            `lugar_visible` ENUM('SI','NO') NOT NULL DEFAULT 'SI',
            `con_senalizacion` ENUM('SI','NO') NOT NULL DEFAULT 'SI',
            `tipo_botiquin` VARCHAR(50) NOT NULL DEFAULT 'LONA' COMMENT 'LONA, METALICO',
            `estado_botiquin` VARCHAR(50) NOT NULL DEFAULT 'BUEN ESTADO' COMMENT 'BUEN ESTADO, ESTADO REGULAR, MAL ESTADO',
            `foto_tabla_espinal` VARCHAR(255) NULL,
            `obs_tabla_espinal` TEXT NULL,
            `estado_collares` VARCHAR(50) NULL DEFAULT 'BUEN ESTADO',
            `foto_collares` VARCHAR(255) NULL,
            `estado_inmovilizadores` VARCHAR(50) NULL DEFAULT 'BUEN ESTADO',
            `foto_inmovilizadores` VARCHAR(255) NULL,
            `recomendaciones` TEXT NULL,
            `pendientes_generados` TEXT NULL COMMENT 'Auto-calculado al finalizar',
            `ruta_pdf` VARCHAR(255) NULL,
            `estado` ENUM('borrador', 'completo') NOT NULL DEFAULT 'borrador',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT `fk_insp_bot_cliente`
                FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
                ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_insp_bot_consultor`
                FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
                ON DELETE RESTRICT ON UPDATE CASCADE,

            INDEX `idx_insp_bot_cliente` (`id_cliente`),
            INDEX `idx_insp_bot_consultor` (`id_consultor`),
            INDEX `idx_insp_bot_estado` (`estado`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_elemento_botiquin',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_elemento_botiquin` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_inspeccion` INT NOT NULL,
            `clave` VARCHAR(50) NOT NULL COMMENT 'Key del ELEMENTOS constant',
            `cantidad` INT NOT NULL DEFAULT 0,
            `estado` VARCHAR(50) NOT NULL DEFAULT 'BUEN ESTADO' COMMENT 'BUEN ESTADO, ESTADO REGULAR, MAL ESTADO, SIN EXISTENCIAS, VENCIDO, NO APLICA',
            `fecha_vencimiento` DATE NULL COMMENT 'Solo para items con vencimiento',
            `orden` TINYINT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT `fk_elem_bot_inspeccion`
                FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_botiquin`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_elem_bot_inspeccion` (`id_inspeccion`)
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
