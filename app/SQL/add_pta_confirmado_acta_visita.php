<?php
/**
 * Agrega columna pta_confirmado a tbl_acta_visita
 * Controla si el consultor ya paso por la vista intermedia PTA
 *
 * Uso: php app/SQL/add_pta_confirmado_acta_visita.php [--prod]
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

    // Verificar si la columna ya existe
    $check = $db->query("SHOW COLUMNS FROM tbl_acta_visita LIKE 'pta_confirmado'");
    if ($check->num_rows > 0) {
        echo "[OK] Columna pta_confirmado ya existe. Nada que hacer.\n";
    } else {
        $sql = "ALTER TABLE tbl_acta_visita ADD COLUMN `pta_confirmado` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Si paso por vista intermedia PTA' AFTER `agenda_id`";
        if ($db->query($sql)) {
            echo "[OK] Columna pta_confirmado agregada exitosamente.\n";
        } else {
            echo "[ERROR] " . $db->error . "\n";
        }
    }

    $db->close();
    echo "=== Fin {$label} ===\n";
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
