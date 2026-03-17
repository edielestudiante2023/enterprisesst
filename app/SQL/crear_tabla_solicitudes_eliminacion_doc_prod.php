<?php
/**
 * Script PRODUCCIÓN: Crear tabla tbl_solicitudes_eliminacion_doc
 * Ejecutar: php app/SQL/crear_tabla_solicitudes_eliminacion_doc_prod.php
 */

$dsn  = 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4';
$user = 'cycloid_userdb';
$pass = 'AVNS_iDypWizlpMRwHIORJGG';

$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
]);

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
