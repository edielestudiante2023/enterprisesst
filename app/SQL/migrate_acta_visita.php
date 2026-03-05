<?php
/**
 * Script CLI para crear tablas del módulo Acta de Visita
 * Uso: php app/SQL/migrate_acta_visita.php [local|production]
 *
 * Crea:
 *   - tbl_acta_visita (tabla principal)
 *   - tbl_acta_visita_integrantes
 *   - tbl_acta_visita_temas
 *   - tbl_acta_visita_fotos
 *   - ALTER tbl_pendientes ADD id_acta_visita (FK)
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
    die("Uso: php app/SQL/migrate_acta_visita.php [local|production]\n");
}

echo "=== Migración SQL - Módulo Acta de Visita ===\n";
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

function columnExists($mysqli, $database, $table, $column) {
    $stmt = $mysqli->prepare(
        "SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?"
    );
    $stmt->bind_param('sss', $database, $table, $column);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['cnt'] > 0;
}

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

// ========== PARTE 1: CREATE TABLES ==========

$createStatements = [
    [
        'desc' => 'CREATE TABLE tbl_acta_visita',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_acta_visita` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,
            `id_consultor` INT NOT NULL,

            -- Datos de la visita
            `fecha_visita` DATE NOT NULL,
            `hora_visita` TIME NOT NULL,
            `ubicacion_gps` VARCHAR(50) NULL COMMENT 'Coordenadas GPS lat,lng',
            `motivo` VARCHAR(255) NOT NULL,
            `modalidad` VARCHAR(50) NULL DEFAULT 'Presencial' COMMENT 'Presencial/Virtual/Mixta',

            -- Contenido
            `cartera` TEXT NULL,
            `observaciones` TEXT NULL,

            -- Próxima reunión
            `proxima_reunion_fecha` DATE NULL,
            `proxima_reunion_hora` TIME NULL,

            -- Firmas (rutas a imágenes PNG)
            `firma_administrador` VARCHAR(255) NULL,
            `firma_vigia` VARCHAR(255) NULL,
            `firma_consultor` VARCHAR(255) NULL,

            -- Soportes documentales
            `soporte_lavado_tanques` VARCHAR(255) NULL,
            `soporte_plagas` VARCHAR(255) NULL,

            -- PDF generado
            `ruta_pdf` VARCHAR(255) NULL,

            -- Estado y tracking
            `estado` ENUM('borrador', 'pendiente_firma', 'completo') NOT NULL DEFAULT 'borrador',
            `agenda_id` VARCHAR(50) NULL COMMENT 'Vínculo opcional con agenda',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            -- Foreign keys
            CONSTRAINT `fk_acta_visita_cliente`
                FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
                ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_acta_visita_consultor`
                FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
                ON DELETE RESTRICT ON UPDATE CASCADE,

            -- Índices
            INDEX `idx_acta_cliente` (`id_cliente`),
            INDEX `idx_acta_consultor` (`id_consultor`),
            INDEX `idx_acta_fecha` (`fecha_visita`),
            INDEX `idx_acta_estado` (`estado`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_acta_visita_integrantes',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_acta_visita_integrantes` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_acta_visita` INT NOT NULL,
            `nombre` VARCHAR(200) NOT NULL,
            `rol` VARCHAR(100) NOT NULL COMMENT 'ADMINISTRADOR, CONSULTOR, VIGÍA SST, etc.',
            `orden` TINYINT NOT NULL DEFAULT 1 COMMENT 'Orden de aparición en el acta',

            CONSTRAINT `fk_integrante_acta`
                FOREIGN KEY (`id_acta_visita`) REFERENCES `tbl_acta_visita`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_integrante_acta` (`id_acta_visita`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_acta_visita_temas',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_acta_visita_temas` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_acta_visita` INT NOT NULL,
            `descripcion` TEXT NOT NULL,
            `orden` TINYINT NOT NULL DEFAULT 1,

            CONSTRAINT `fk_tema_acta`
                FOREIGN KEY (`id_acta_visita`) REFERENCES `tbl_acta_visita`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_tema_acta` (`id_acta_visita`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_acta_visita_fotos',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_acta_visita_fotos` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_acta_visita` INT NOT NULL,
            `ruta_archivo` VARCHAR(255) NOT NULL,
            `tipo` VARCHAR(50) NOT NULL DEFAULT 'foto' COMMENT 'foto, soporte, seg_social',
            `descripcion` VARCHAR(255) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT `fk_foto_acta`
                FOREIGN KEY (`id_acta_visita`) REFERENCES `tbl_acta_visita`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_foto_acta` (`id_acta_visita`)
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

// ========== PARTE 2: ALTER TABLE tbl_pendientes ==========

$total++;
echo "[{$total}] ADD COLUMN id_acta_visita a tbl_pendientes... ";

if (columnExists($mysqli, $config['database'], 'tbl_pendientes', 'id_acta_visita')) {
    echo "SKIP (ya existe)\n";
    $skipped++;
} else {
    $sql = "ALTER TABLE `tbl_pendientes`
        ADD COLUMN `id_acta_visita` INT NULL DEFAULT NULL
        COMMENT 'FK al acta de visita que generó este pendiente (nullable)'";

    if ($mysqli->query($sql)) {
        echo "OK (columna creada)\n";
        $success++;

        // Agregar índice
        $total++;
        echo "[{$total}] ADD INDEX idx_pendiente_acta... ";
        if ($mysqli->query("ALTER TABLE `tbl_pendientes` ADD INDEX `idx_pendiente_acta` (`id_acta_visita`)")) {
            echo "OK\n";
            $success++;
        } else {
            echo "ERROR: " . $mysqli->error . "\n";
            $errors++;
        }

        // Agregar FK
        $total++;
        echo "[{$total}] ADD CONSTRAINT fk_pendiente_acta_visita... ";
        $fkSql = "ALTER TABLE `tbl_pendientes`
            ADD CONSTRAINT `fk_pendiente_acta_visita`
            FOREIGN KEY (`id_acta_visita`) REFERENCES `tbl_acta_visita`(`id`)
            ON DELETE SET NULL ON UPDATE CASCADE";
        if ($mysqli->query($fkSql)) {
            echo "OK\n";
            $success++;
        } else {
            echo "ERROR: " . $mysqli->error . "\n";
            $errors++;
        }
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
