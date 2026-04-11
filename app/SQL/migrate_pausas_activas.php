<?php
/**
 * Crear tablas para módulo de pausas activas
 * Uso: php app/SQL/migrate_pausas_activas.php [--prod]
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
$pdo = new PDO($dsn, $user, $pass, $opts);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Tabla principal
$sql1 = "
CREATE TABLE IF NOT EXISTS tbl_pausas_activas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NULL DEFAULT NULL,
    id_miembro INT NULL DEFAULT NULL,
    creado_por_tipo ENUM('consultor','miembro') NOT NULL DEFAULT 'consultor',
    fecha_actividad DATE NOT NULL,
    observaciones TEXT NULL,
    ruta_pdf VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cliente (id_cliente),
    INDEX idx_fecha (fecha_actividad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// Tabla de registros (equivalente a hallazgos en locativa)
$sql2 = "
CREATE TABLE IF NOT EXISTS tbl_pausa_activa_registros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pausa INT NOT NULL,
    tipo_pausa VARCHAR(255) NOT NULL,
    imagen VARCHAR(255) NULL,
    orden INT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pausa (id_pausa),
    FOREIGN KEY (id_pausa) REFERENCES tbl_pausas_activas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $pdo->exec($sql1);
    echo "OK: tbl_pausas_activas creada.\n";
} catch (PDOException $e) {
    echo "ERROR tbl_pausas_activas: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec($sql2);
    echo "OK: tbl_pausa_activa_registros creada.\n";
} catch (PDOException $e) {
    echo "ERROR tbl_pausa_activa_registros: " . $e->getMessage() . "\n";
}

// Verificar
foreach (['tbl_pausas_activas', 'tbl_pausa_activa_registros'] as $t) {
    echo "\n{$t}:\n";
    $cols = $pdo->query("SHOW COLUMNS FROM {$t}")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        echo "  {$col['Field']} ({$col['Type']})\n";
    }
}
