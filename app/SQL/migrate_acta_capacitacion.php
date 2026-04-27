<?php
/**
 * Migración: Crear tablas para módulo Acta de Capacitación
 *
 * Crea:
 *   - tbl_acta_capacitacion
 *   - tbl_acta_capacitacion_asistente (FK con ON DELETE CASCADE)
 *
 * Uso:
 *   php app/SQL/migrate_acta_capacitacion.php          # LOCAL
 *   php app/SQL/migrate_acta_capacitacion.php --prod   # PRODUCCIÓN
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

echo "=== Migración: Acta de Capacitación — Entorno: {$label} ===\n";
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
        'desc' => 'CREATE TABLE tbl_acta_capacitacion',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_acta_capacitacion` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,
            `id_comite` INT NULL DEFAULT NULL COMMENT 'Opcional: NULL si consultor crea sin comité',
            `creado_por_tipo` ENUM('miembro','consultor') NOT NULL,
            `id_miembro` INT NULL DEFAULT NULL,
            `id_consultor` INT NULL DEFAULT NULL,

            -- Datos de la capacitación
            `tema` VARCHAR(255) NOT NULL,
            `fecha_capacitacion` DATE NOT NULL,
            `hora_inicio` TIME NULL,
            `hora_fin` TIME NULL,
            `dictada_por` ENUM('ARL','Consultor','Empresa','Otro') NOT NULL DEFAULT 'ARL',
            `nombre_capacitador` VARCHAR(200) NULL,
            `entidad_capacitadora` VARCHAR(200) NULL,
            `modalidad` ENUM('virtual','presencial','mixta') NOT NULL DEFAULT 'virtual',
            `enlace_grabacion` VARCHAR(500) NULL,
            `objetivos` TEXT NULL,
            `contenido` TEXT NULL,
            `observaciones` TEXT NULL,

            -- PDF
            `ruta_pdf` VARCHAR(255) NULL,

            -- Estado y tracking
            `estado` ENUM('borrador','esperando_firmas','completo') NOT NULL DEFAULT 'borrador',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX `idx_acta_cap_cliente` (`id_cliente`),
            INDEX `idx_acta_cap_comite` (`id_comite`),
            INDEX `idx_acta_cap_fecha` (`fecha_capacitacion`),
            INDEX `idx_acta_cap_estado` (`estado`),
            INDEX `idx_acta_cap_creador` (`creado_por_tipo`, `id_miembro`, `id_consultor`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_acta_capacitacion_asistente',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_acta_capacitacion_asistente` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_acta_capacitacion` INT NOT NULL,

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

            `orden` INT NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT `fk_asistente_acta_cap`
                FOREIGN KEY (`id_acta_capacitacion`) REFERENCES `tbl_acta_capacitacion`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_asistente_acta` (`id_acta_capacitacion`),
            UNIQUE KEY `uniq_token_firma` (`token_firma`)
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
foreach (['tbl_acta_capacitacion', 'tbl_acta_capacitacion_asistente'] as $table) {
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
