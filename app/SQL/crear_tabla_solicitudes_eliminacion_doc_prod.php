<?php
/**
 * Script PRODUCCIÓN: Crear tabla tbl_solicitudes_eliminacion_doc
 * Ejecutar: php app/SQL/crear_tabla_solicitudes_eliminacion_doc_prod.php
 */

$host   = getenv('DB_HOST_PROD') ?: getenv('DB_HOST') ?: '127.0.0.1';
$user   = getenv('DB_USER_PROD') ?: getenv('DB_USER') ?: 'root';
$pass   = getenv('DB_PASS_PROD') ?: getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME_PROD') ?: getenv('DB_NAME') ?: 'empresas_sst';
$port   = getenv('DB_PORT_PROD') ?: 3306;

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
$opts = [PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false];

try {
    $pdo = new PDO($dsn, $user, $pass, $opts);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Fallback sin SSL
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

$sql = "
CREATE TABLE IF NOT EXISTS tbl_solicitudes_eliminacion_doc (
    id_solicitud     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_documento     INT NOT NULL,
    titulo_documento VARCHAR(255) NOT NULL,
    codigo_documento VARCHAR(50) NOT NULL,
    nombre_cliente   VARCHAR(255) NOT NULL,
    motivo           TEXT NOT NULL,
    solicitado_por   VARCHAR(255) NOT NULL,
    token            VARCHAR(64) NOT NULL UNIQUE,
    estado           ENUM('pendiente','aprobada','rechazada','expirada') NOT NULL DEFAULT 'pendiente',
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    aprobado_at      DATETIME NULL,
    expires_at       DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $pdo->exec($sql);
    echo "OK PROD: tabla tbl_solicitudes_eliminacion_doc creada.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
