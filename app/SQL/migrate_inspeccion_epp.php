<?php
/**
 * Migración: Crear tablas para módulo Inspección de EPP
 *
 * Crea:
 *   - tbl_inspeccion_epp (cabecera)
 *   - tbl_hallazgo_epp   (FK con ON DELETE CASCADE)
 *
 * Espejo del esquema de tbl_inspeccion_locativa pero con campos extra
 * orientados a EPP en cada hallazgo (tipo_epp + trabajador_area).
 *
 * Uso:
 *   php app/SQL/migrate_inspeccion_epp.php          # LOCAL
 *   php app/SQL/migrate_inspeccion_epp.php --prod   # PRODUCCIÓN
 */

if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ejecutarse desde la línea de comandos.\n");
}

$isProd = in_array('--prod', $argv ?? [], true);

if ($isProd) {
    $cfg = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'user'     => 'cycloid_userdb',
        'password' => 'AVNS_MR2SLvzRh3i_7o9fEHN',
        'database' => 'empresas_sst',
        'ssl'      => true,
    ];
    $label = 'PRODUCCIÓN';
} else {
    $cfg = [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => '',
        'database' => 'empresas_sst',
        'ssl'      => false,
    ];
    $label = 'LOCAL';
}

echo "=== Migración: Inspección de EPP — Entorno: {$label} ===\n";
echo "Host: {$cfg['host']}:{$cfg['port']}/{$cfg['database']}\n\n";

$mysqli = mysqli_init();
if ($cfg['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}
$ok = @$mysqli->real_connect(
    $cfg['host'], $cfg['user'], $cfg['password'], $cfg['database'], $cfg['port'],
    null, $cfg['ssl'] ? MYSQLI_CLIENT_SSL : 0
);
if (!$ok) {
    die("ERROR conexión: " . $mysqli->connect_error . "\n");
}
$mysqli->set_charset('utf8mb4');
echo "Conexión exitosa.\n\n";

$success = 0;
$errors  = 0;
$total   = 0;

$statements = [
    [
        'desc' => 'CREATE TABLE tbl_inspeccion_epp',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_epp` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,
            `id_consultor` INT NULL DEFAULT NULL,
            `id_miembro` INT NULL DEFAULT NULL,
            `creado_por_tipo` ENUM('consultor','miembro') NOT NULL DEFAULT 'consultor',

            `fecha_inspeccion` DATE NOT NULL,
            `observaciones` TEXT NULL COMMENT 'Recomendaciones generales para la empresa',

            `ruta_pdf` VARCHAR(255) NULL,
            `estado` ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX `idx_iepp_cliente` (`id_cliente`),
            INDEX `idx_iepp_fecha` (`fecha_inspeccion`),
            INDEX `idx_iepp_estado` (`estado`),
            INDEX `idx_iepp_creador` (`creado_por_tipo`, `id_miembro`, `id_consultor`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_hallazgo_epp',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_hallazgo_epp` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_inspeccion` INT NOT NULL,

            `tipo_epp` VARCHAR(150) NULL COMMENT 'Texto libre: casco, guantes, botas, etc.',
            `trabajador_area` VARCHAR(200) NULL COMMENT 'Texto libre: trabajador o area afectada',
            `descripcion` TEXT NOT NULL,
            `imagen` VARCHAR(255) NULL,
            `imagen_correccion` VARCHAR(255) NULL,
            `fecha_hallazgo` DATE NULL,
            `fecha_correccion` DATE NULL,
            `estado` VARCHAR(50) NOT NULL DEFAULT 'ABIERTO',
            `observaciones` TEXT NULL,
            `orden` TINYINT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT `fk_hallazgo_inspeccion_epp`
                FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_epp`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_hepp_inspeccion` (`id_inspeccion`),
            INDEX `idx_hepp_estado` (`estado`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
];

foreach ($statements as $stmt) {
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

// Verificación
echo "\n=== Verificación de columnas ===\n";
foreach (['tbl_inspeccion_epp', 'tbl_hallazgo_epp'] as $table) {
    echo "\n{$table}:\n";
    $result = $mysqli->query("SHOW COLUMNS FROM `{$table}`");
    if ($result) {
        while ($col = $result->fetch_assoc()) {
            echo "  - {$col['Field']} ({$col['Type']})" . ($col['Null'] === 'NO' ? ' NOT NULL' : '') . "\n";
        }
        $result->free();
    } else {
        echo "  ERROR: " . $mysqli->error . "\n";
    }
}

echo "\n=== RESULTADO ===\n";
echo "Exitosas: {$success}\n";
echo "Errores:  {$errors}\n";
echo "Total:    {$total}\n";
echo $errors === 0 ? "MIGRACIÓN OK\n" : "HAY ERRORES — REVISAR ANTES DE IR A PRODUCCIÓN\n";

$mysqli->close();
exit($errors === 0 ? 0 : 1);
