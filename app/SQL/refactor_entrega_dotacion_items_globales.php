<?php
/**
 * Refactor: Entrega de Dotación → items globales por entrega + tallas por asistente
 *
 * Cambios:
 *   1. tbl_entrega_dotacion_item: cambiar FK id_entrega_dotacion_asistente → id_entrega_dotacion
 *      (drop talla porque ahora va en tabla aparte)
 *   2. Crear tbl_entrega_dotacion_asistente_talla (id, id_asistente, id_item, talla)
 *   3. tbl_entrega_dotacion_asistente: agregar columnas recibido_buen_estado + observaciones_recibido
 *
 * Como las 3 tablas tienen 0 registros (ambiente recién deployado), se hace DROP + CREATE
 * para evitar mantener foreign keys huérfanas y simplificar el ALTER.
 *
 * Uso:
 *   php app/SQL/refactor_entrega_dotacion_items_globales.php          # LOCAL
 *   php app/SQL/refactor_entrega_dotacion_items_globales.php --prod   # PRODUCCIÓN
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

echo "=== Refactor: Entrega de Dotación (items globales) — Entorno: {$label} ===\n";
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

// Validar que las tablas estén vacías antes de DROP (seguridad)
$result = $mysqli->query("SELECT COUNT(*) AS c FROM tbl_entrega_dotacion_item");
$itemsCount = (int)($result ? $result->fetch_assoc()['c'] : 0);
$result = $mysqli->query("SELECT COUNT(*) AS c FROM tbl_entrega_dotacion_asistente");
$asistCount = (int)($result ? $result->fetch_assoc()['c'] : 0);

echo "Registros existentes:\n";
echo "  - tbl_entrega_dotacion_item: {$itemsCount}\n";
echo "  - tbl_entrega_dotacion_asistente: {$asistCount}\n\n";

if ($itemsCount > 0) {
    die("ABORTAR: tbl_entrega_dotacion_item tiene datos. Hay que migrarlos manualmente antes de continuar.\n");
}

$success = 0;
$errors  = 0;
$total   = 0;

$statements = [
    [
        'desc' => 'DROP TABLE tbl_entrega_dotacion_item (vacía)',
        'sql'  => "DROP TABLE IF EXISTS `tbl_entrega_dotacion_item`",
    ],
    [
        'desc' => 'CREATE TABLE tbl_entrega_dotacion_item (FK ahora a entrega, sin talla)',
        'sql'  => "CREATE TABLE `tbl_entrega_dotacion_item` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_entrega_dotacion` INT NOT NULL,

            `descripcion` VARCHAR(255) NOT NULL COMMENT 'Texto libre, item global de la entrega',
            `cantidad` VARCHAR(50) NOT NULL DEFAULT '1' COMMENT 'Texto libre',
            `marca` VARCHAR(100) NULL COMMENT 'Texto libre',

            `orden` INT NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT `fk_item_entrega_dotacion`
                FOREIGN KEY (`id_entrega_dotacion`) REFERENCES `tbl_entrega_dotacion`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_item_entrega` (`id_entrega_dotacion`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ],
    [
        'desc' => 'CREATE TABLE tbl_entrega_dotacion_asistente_talla (talla por asistente x item)',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_entrega_dotacion_asistente_talla` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_entrega_dotacion_asistente` INT NOT NULL,
            `id_entrega_dotacion_item` INT NOT NULL,
            `talla` VARCHAR(50) NULL COMMENT 'Texto libre digitado por el operario',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT `fk_talla_asistente`
                FOREIGN KEY (`id_entrega_dotacion_asistente`) REFERENCES `tbl_entrega_dotacion_asistente`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_talla_item`
                FOREIGN KEY (`id_entrega_dotacion_item`) REFERENCES `tbl_entrega_dotacion_item`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            UNIQUE KEY `uniq_talla_asistente_item` (`id_entrega_dotacion_asistente`, `id_entrega_dotacion_item`),
            INDEX `idx_talla_asistente` (`id_entrega_dotacion_asistente`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ],
    [
        'desc' => 'ALTER tbl_entrega_dotacion_asistente ADD recibido_buen_estado',
        'sql'  => "ALTER TABLE `tbl_entrega_dotacion_asistente`
                   ADD COLUMN `recibido_buen_estado` ENUM('si','no') NULL DEFAULT NULL
                   COMMENT 'Operario confirma si recibe en buen estado'
                   AFTER `firmado_at`",
    ],
    [
        'desc' => 'ALTER tbl_entrega_dotacion_asistente ADD observaciones_recibido',
        'sql'  => "ALTER TABLE `tbl_entrega_dotacion_asistente`
                   ADD COLUMN `observaciones_recibido` TEXT NULL
                   COMMENT 'Observaciones del operario si NO recibió en buen estado'
                   AFTER `recibido_buen_estado`",
    ],
];

foreach ($statements as $stmt) {
    $total++;
    echo "[{$total}] {$stmt['desc']}... ";
    if ($mysqli->query($stmt['sql'])) {
        echo "OK\n";
        $success++;
    } else {
        $err = $mysqli->error;
        // Si la columna ya existe (re-ejecución del script), ignorar
        if (strpos($err, 'Duplicate column name') !== false) {
            echo "SKIP (ya existe)\n";
            $success++;
        } else {
            echo "ERROR: " . $err . "\n";
            $errors++;
        }
    }
}

// Verificación
echo "\n=== Verificación ===\n";
foreach (['tbl_entrega_dotacion', 'tbl_entrega_dotacion_asistente', 'tbl_entrega_dotacion_item', 'tbl_entrega_dotacion_asistente_talla'] as $table) {
    echo "\n{$table}:\n";
    $result = $mysqli->query("SHOW COLUMNS FROM `{$table}`");
    if ($result) {
        while ($col = $result->fetch_assoc()) {
            echo "  - {$col['Field']} ({$col['Type']})" . ($col['Null'] === 'NO' ? ' NOT NULL' : '') . "\n";
        }
        $result->free();
    }
}

echo "\n=== RESULTADO ===\n";
echo "Exitosas: {$success}\n";
echo "Errores:  {$errors}\n";
echo "Total:    {$total}\n";
echo $errors === 0 ? "REFACTOR OK\n" : "HAY ERRORES — REVISAR ANTES DE IR A PRODUCCIÓN\n";

$mysqli->close();
exit($errors === 0 ? 0 : 1);
