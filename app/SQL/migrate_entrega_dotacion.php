<?php
/**
 * Migración: Crear tablas para módulo Entrega de Dotación / EPP
 *
 * Crea:
 *   - tbl_entrega_dotacion          (cabecera)
 *   - tbl_entrega_dotacion_asistente (operarios — FK con ON DELETE CASCADE)
 *   - tbl_entrega_dotacion_item     (items por asistente — FK con ON DELETE CASCADE)
 *
 * Uso:
 *   php app/SQL/migrate_entrega_dotacion.php          # LOCAL
 *   php app/SQL/migrate_entrega_dotacion.php --prod   # PRODUCCIÓN
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

echo "=== Migración: Entrega de Dotación — Entorno: {$label} ===\n";
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
        'desc' => 'CREATE TABLE tbl_entrega_dotacion',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_entrega_dotacion` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,
            `creado_por_tipo` ENUM('consultor') NOT NULL DEFAULT 'consultor',
            `id_consultor` INT NULL DEFAULT NULL,

            -- Datos de la entrega
            `fecha_entrega` DATE NOT NULL,
            `hora` TIME NULL,
            `lugar` VARCHAR(255) NULL,
            `responsable_entrega` VARCHAR(200) NULL COMMENT 'Quien entrega los EPP',
            `tipo_dotacion` VARCHAR(150) NULL COMMENT 'operativa, oficina, mensajero, etc. (texto libre)',
            `observaciones` TEXT NULL,

            -- PDF / token
            `token_inscripcion` VARCHAR(64) NULL DEFAULT NULL,

            -- Estado
            `estado` ENUM('borrador','esperando_firmas','completo') NOT NULL DEFAULT 'borrador',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX `idx_ed_cliente` (`id_cliente`),
            INDEX `idx_ed_fecha` (`fecha_entrega`),
            INDEX `idx_ed_estado` (`estado`),
            INDEX `idx_ed_consultor` (`id_consultor`),
            INDEX `idx_ed_token_inscripcion` (`token_inscripcion`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_entrega_dotacion_asistente',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_entrega_dotacion_asistente` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_entrega_dotacion` INT NOT NULL,

            `nombre_completo` VARCHAR(200) NOT NULL,
            `tipo_documento` ENUM('CC','CE','PA','TI','NIT') DEFAULT 'CC',
            `numero_documento` VARCHAR(20) NULL,
            `cargo` VARCHAR(150) NULL,
            `area_dependencia` VARCHAR(150) NULL,
            `email` VARCHAR(150) NULL,
            `celular` VARCHAR(30) NULL,

            -- Token y firma remota
            `token_firma` VARCHAR(64) NULL DEFAULT NULL,
            `token_expiracion` DATETIME NULL DEFAULT NULL,
            `firma_path` VARCHAR(255) NULL DEFAULT NULL,
            `firmado_at` DATETIME NULL DEFAULT NULL,

            -- PDF individual generado al firmar (uno por operario)
            `ruta_pdf` VARCHAR(255) NULL DEFAULT NULL,

            `orden` INT NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT `fk_asistente_entrega_dotacion`
                FOREIGN KEY (`id_entrega_dotacion`) REFERENCES `tbl_entrega_dotacion`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_asistente_entrega` (`id_entrega_dotacion`),
            UNIQUE KEY `uniq_ed_token_firma` (`token_firma`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_entrega_dotacion_item',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_entrega_dotacion_item` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_entrega_dotacion_asistente` INT NOT NULL,

            `descripcion` VARCHAR(255) NOT NULL COMMENT 'Texto libre',
            `cantidad` VARCHAR(50) NOT NULL DEFAULT '1' COMMENT 'Texto libre (ej: 1, 2, 1 par)',
            `talla` VARCHAR(50) NULL COMMENT 'Texto libre',
            `marca` VARCHAR(100) NULL COMMENT 'Texto libre',

            `orden` INT NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT `fk_item_entrega_dotacion_asistente`
                FOREIGN KEY (`id_entrega_dotacion_asistente`) REFERENCES `tbl_entrega_dotacion_asistente`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_item_asistente` (`id_entrega_dotacion_asistente`)
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
foreach (['tbl_entrega_dotacion', 'tbl_entrega_dotacion_asistente', 'tbl_entrega_dotacion_item'] as $table) {
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
