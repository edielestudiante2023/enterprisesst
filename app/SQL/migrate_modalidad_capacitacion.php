<?php
/**
 * Agregar columna 'modalidad' a tbl_cronog_capacitacion.
 * Valores: VIRTUAL | PRESENCIAL | MIXTA. Default PRESENCIAL.
 * Uso: php app/SQL/migrate_modalidad_capacitacion.php [--prod]
 */
$isProd = in_array('--prod', $argv ?? []);

if ($isProd) {
    $dsn  = 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4';
    $user = 'cycloid_userdb';
    $pass = 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $opts = [PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false];
    $label = 'PRODUCCION';
} else {
    $dsn  = 'mysql:host=localhost;dbname=empresas_sst;charset=utf8mb4';
    $user = 'root';
    $pass = '';
    $opts = [];
    $label = 'LOCAL';
}

echo "=== {$label} ===\n";

try {
    $pdo = new PDO($dsn, $user, $pass, $opts);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "ERROR conexion: " . $e->getMessage() . "\n";
    exit(1);
}

// Verificar si la columna ya existe
$existe = $pdo->query("
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_cronog_capacitacion'
      AND COLUMN_NAME = 'modalidad'
")->fetchColumn();

if ($existe) {
    echo "OK: columna 'modalidad' ya existe en tbl_cronog_capacitacion. Nada que hacer.\n";
} else {
    try {
        $pdo->exec("
            ALTER TABLE tbl_cronog_capacitacion
            ADD COLUMN modalidad ENUM('VIRTUAL','PRESENCIAL','MIXTA') NOT NULL DEFAULT 'PRESENCIAL'
            AFTER perfil_de_asistentes
        ");
        echo "OK: columna 'modalidad' agregada (default PRESENCIAL).\n";
    } catch (PDOException $e) {
        echo "ERROR ALTER: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Verificar resultado
echo "\nColumnas de tbl_cronog_capacitacion (relevantes):\n";
$cols = $pdo->query("SHOW COLUMNS FROM tbl_cronog_capacitacion LIKE 'modalidad'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo "  {$col['Field']} ({$col['Type']}) NULL={$col['Null']} DEFAULT={$col['Default']}\n";
}

// Conteo de registros y distribucion
$total = (int) $pdo->query("SELECT COUNT(*) FROM tbl_cronog_capacitacion")->fetchColumn();
echo "\nTotal cronogramas: {$total}\n";

$dist = $pdo->query("SELECT modalidad, COUNT(*) AS n FROM tbl_cronog_capacitacion GROUP BY modalidad")->fetchAll(PDO::FETCH_ASSOC);
echo "Distribucion modalidad:\n";
foreach ($dist as $r) {
    echo "  {$r['modalidad']}: {$r['n']}\n";
}
