<?php
/**
 * Crear tabla tbl_acta_solicitudes_reapertura
 * Uso: php app/SQL/crear_tabla_solicitudes_reapertura.php [--prod]
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
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $options = $isProd ? [PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false] : [];
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conectado a {$host}/{$name}\n";
} catch (PDOException $e) {
    die("Error de conexion: " . $e->getMessage() . "\n");
}

$sql = "
CREATE TABLE IF NOT EXISTS tbl_acta_solicitudes_reapertura (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_acta INT NOT NULL,
    id_cliente INT NOT NULL,
    solicitante_nombre VARCHAR(255) NOT NULL,
    solicitante_email VARCHAR(255) NOT NULL,
    solicitante_cargo VARCHAR(150) DEFAULT NULL,
    justificacion TEXT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    token_expira DATETIME NOT NULL,
    estado ENUM('pendiente','aprobada','rechazada','expirada') DEFAULT 'pendiente',
    aprobado_por VARCHAR(255) DEFAULT NULL,
    aprobado_at DATETIME DEFAULT NULL,
    rechazado_motivo TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_acta_estado (id_acta, estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $pdo->exec($sql);
    echo "OK: Tabla tbl_acta_solicitudes_reapertura creada.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Verificar
$cols = $pdo->query("SHOW COLUMNS FROM tbl_acta_solicitudes_reapertura")->fetchAll(PDO::FETCH_ASSOC);
echo "\nColumnas:\n";
foreach ($cols as $col) {
    echo "  {$col['Field']} ({$col['Type']})\n";
}
