<?php
/**
 * Agrega columnas de token firma remota a tbl_acta_visita
 * - token_firma_remota VARCHAR(64)
 * - token_firma_tipo VARCHAR(20)
 * - token_firma_expiracion DATETIME
 *
 * Uso: php app/SQL/migrate_acta_firma_remota.php [--prod]
 */

$isProd = in_array('--prod', $argv ?? []);

if ($isProd) {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $user = 'cycloid_userdb';
    $pass = 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $name = 'empresas_sst';
    $port = 25060;
    $label = 'PRODUCCION';
} else {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $name = 'empresas_sst';
    $port = 3306;
    $label = 'LOCAL';
}

echo "=== Ejecutando en {$label} ===\n";

try {
    $flags = $isProd ? MYSQLI_CLIENT_SSL : 0;
    $db = mysqli_init();
    if ($isProd) {
        $db->ssl_set(null, null, null, null, null);
    }
    $db->real_connect($host, $user, $pass, $name, $port, null, $flags);
    $db->set_charset('utf8mb4');

    $columnas = [
        'token_firma_remota'     => "VARCHAR(64) NULL DEFAULT NULL COMMENT 'Token hex 64 chars para firma remota'",
        'token_firma_tipo'       => "VARCHAR(20) NULL DEFAULT NULL COMMENT 'administrador|vigia|consultor'",
        'token_firma_expiracion' => "DATETIME NULL DEFAULT NULL COMMENT 'Expiracion del token (24h)'",
    ];

    foreach ($columnas as $col => $def) {
        $check = $db->query("SHOW COLUMNS FROM tbl_acta_visita LIKE '{$col}'");
        if ($check->num_rows > 0) {
            echo "[OK] Columna {$col} ya existe.\n";
        } else {
            $sql = "ALTER TABLE tbl_acta_visita ADD COLUMN `{$col}` {$def} AFTER `firma_consultor`";
            if ($db->query($sql)) {
                echo "[OK] Columna {$col} agregada.\n";
            } else {
                echo "[ERROR] {$col}: " . $db->error . "\n";
            }
        }
    }

    $db->close();
    echo "=== Fin {$label} ===\n";
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
