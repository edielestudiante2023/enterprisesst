<?php
/**
 * Script CLI para crear tablas del módulo Inspección de Extintores
 * Uso: php migrate_inspeccion_extintores.php [local|production]
 *
 * Crea:
 *   - tbl_inspeccion_extintores (tabla principal con inventario general)
 *   - tbl_extintor_detalle (N extintores inspeccionados por inspección)
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
    die("Uso: php migrate_inspeccion_extintores.php [local|production]\n");
}

echo "=== Migración SQL - Módulo Inspección Extintores ===\n";
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
        'desc' => 'CREATE TABLE tbl_inspeccion_extintores',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_extintores` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,
            `id_consultor` INT NOT NULL,
            `fecha_inspeccion` DATE NOT NULL,
            `fecha_vencimiento_global` DATE NULL,
            `numero_extintores_totales` INT NOT NULL DEFAULT 0,
            `cantidad_abc` INT NOT NULL DEFAULT 0,
            `cantidad_co2` INT NOT NULL DEFAULT 0,
            `cantidad_solkaflam` INT NOT NULL DEFAULT 0,
            `cantidad_agua` INT NOT NULL DEFAULT 0,
            `capacidad_libras` VARCHAR(100) NULL COMMENT 'Ej: 10 LIBRAS',
            `cantidad_unidades_residenciales` INT NOT NULL DEFAULT 0,
            `cantidad_porteria` INT NOT NULL DEFAULT 0,
            `cantidad_oficina_admin` INT NOT NULL DEFAULT 0,
            `cantidad_shut_basuras` INT NOT NULL DEFAULT 0,
            `cantidad_salones_comunales` INT NOT NULL DEFAULT 0,
            `cantidad_cuarto_bombas` INT NOT NULL DEFAULT 0,
            `cantidad_planta_electrica` INT NOT NULL DEFAULT 0,
            `recomendaciones_generales` TEXT NULL,
            `ruta_pdf` VARCHAR(255) NULL,
            `estado` ENUM('borrador', 'completo') NOT NULL DEFAULT 'borrador',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT `fk_insp_ext_cliente`
                FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
                ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_insp_ext_consultor`
                FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
                ON DELETE RESTRICT ON UPDATE CASCADE,

            INDEX `idx_insp_ext_cliente` (`id_cliente`),
            INDEX `idx_insp_ext_consultor` (`id_consultor`),
            INDEX `idx_insp_ext_estado` (`estado`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_extintor_detalle',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_extintor_detalle` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_inspeccion` INT NOT NULL,
            `pintura_cilindro` VARCHAR(30) NOT NULL DEFAULT 'BUENO',
            `golpes_extintor` VARCHAR(10) NOT NULL DEFAULT 'NO',
            `autoadhesivo` VARCHAR(30) NOT NULL DEFAULT 'BUENO',
            `manija_transporte` VARCHAR(30) NOT NULL DEFAULT 'BUENO',
            `palanca_accionamiento` VARCHAR(30) NOT NULL DEFAULT 'BUENO',
            `presion` VARCHAR(30) NOT NULL DEFAULT 'CARGADO',
            `manometro` VARCHAR(30) NOT NULL DEFAULT 'BUENO',
            `boquilla` VARCHAR(30) NOT NULL DEFAULT 'BUENO',
            `manguera` VARCHAR(30) NOT NULL DEFAULT 'NO APLICA',
            `ring_seguridad` VARCHAR(30) NOT NULL DEFAULT 'BUENO',
            `senalizacion` VARCHAR(30) NOT NULL DEFAULT 'BUENO',
            `soporte` VARCHAR(30) NOT NULL DEFAULT 'BUENO',
            `fecha_vencimiento` DATE NULL,
            `foto` VARCHAR(255) NULL COMMENT 'Ruta foto evidencia',
            `observaciones` TEXT NULL,
            `orden` TINYINT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT `fk_ext_det_inspeccion`
                FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_extintores`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_ext_det_inspeccion` (`id_inspeccion`)
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
