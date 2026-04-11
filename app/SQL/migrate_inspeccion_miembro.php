<?php
/**
 * Agregar campos id_miembro y creado_por_tipo a tbl_inspeccion_locativa
 * Para permitir que miembros COPASST creen inspecciones
 * Uso: php app/SQL/migrate_inspeccion_miembro.php [--prod]
 */
$isProd = in_array('--prod', $argv ?? []);

if ($isProd) {
    $dsn = 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4';
    $user = 'cycloid_userdb';
    $pass = 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $opts = [PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false];
    $label = 'PRODUCCION';
} else {
    $dsn = 'mysql:host=localhost;dbname=empresas_sst;charset=utf8mb4';
    $user = 'root';
    $pass = '';
    $opts = [];
    $label = 'LOCAL';
}

echo "=== {$label} ===\n";

try {
    $pdo = new PDO($dsn, $user, $pass, $opts);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conectado OK\n";
} catch (PDOException $e) {
    die("Error de conexion: " . $e->getMessage() . "\n");
}

// Verificar si columnas ya existen
$cols = array_column(
    $pdo->query("SHOW COLUMNS FROM tbl_inspeccion_locativa")->fetchAll(PDO::FETCH_ASSOC),
    'Field'
);

if (in_array('id_miembro', $cols)) {
    echo "Columna id_miembro ya existe, saltando.\n";
} else {
    try {
        $pdo->exec("ALTER TABLE tbl_inspeccion_locativa ADD COLUMN id_miembro INT NULL DEFAULT NULL AFTER id_consultor");
        echo "OK: Columna id_miembro agregada.\n";
    } catch (PDOException $e) {
        echo "ERROR id_miembro: " . $e->getMessage() . "\n";
    }
}

if (in_array('creado_por_tipo', $cols)) {
    echo "Columna creado_por_tipo ya existe, saltando.\n";
} else {
    try {
        $pdo->exec("ALTER TABLE tbl_inspeccion_locativa ADD COLUMN creado_por_tipo ENUM('consultor','miembro') NOT NULL DEFAULT 'consultor' AFTER id_miembro");
        echo "OK: Columna creado_por_tipo agregada.\n";
    } catch (PDOException $e) {
        echo "ERROR creado_por_tipo: " . $e->getMessage() . "\n";
    }
}

// Verificar resultado
echo "\nColumnas actuales:\n";
$cols = $pdo->query("SHOW COLUMNS FROM tbl_inspeccion_locativa")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo "  {$col['Field']} ({$col['Type']})\n";
}
