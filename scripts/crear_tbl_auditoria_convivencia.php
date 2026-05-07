<?php
/**
 * Crear tbl_auditoria_convivencia para registrar accesos a vistas de Comite de Convivencia.
 *
 * Cumple obligacion de trazabilidad por confidencialidad (Ley 1010 / Res 3461).
 *
 * Uso:
 *   php scripts/crear_tbl_auditoria_convivencia.php             # local dry-run
 *   php scripts/crear_tbl_auditoria_convivencia.php --apply     # local apply
 *   php scripts/crear_tbl_auditoria_convivencia.php --prod              # prod dry-run
 *   php scripts/crear_tbl_auditoria_convivencia.php --prod --apply      # prod apply
 */

$isProd = in_array('--prod', $argv ?? [], true);
$apply  = in_array('--apply', $argv ?? [], true);
echo "=== " . ($isProd ? 'PRODUCCION' : 'LOCAL') . " | " . ($apply ? 'APPLY' : 'DRY-RUN') . " ===\n\n";

if ($isProd) {
    $host = getenv('DB_PROD_HOST') ?: '';
    $user = getenv('DB_PROD_USER') ?: '';
    $pass = getenv('DB_PROD_PASS') ?: '';
    $port = (int)(getenv('DB_PROD_PORT') ?: 25060);
    $db   = getenv('DB_PROD_NAME') ?: 'empresas_sst';
    if ($host === '' || $user === '' || $pass === '') { echo "ERROR: faltan env vars DB_PROD_*\n"; exit(1); }
    $conn = mysqli_init();
    mysqli_ssl_set($conn, null, null, null, null, null);
    if (!@mysqli_real_connect($conn, $host, $user, $pass, $db, $port, null, MYSQLI_CLIENT_SSL)) {
        echo "ERROR conexion: " . mysqli_connect_error() . "\n"; exit(1);
    }
} else {
    $conn = new mysqli('localhost', 'root', '', 'empresas_sst');
    if ($conn->connect_error) { echo "ERROR conexion local: " . $conn->connect_error . "\n"; exit(1); }
}
$conn->set_charset('utf8mb4');

// Verificar si la tabla ya existe
$r = $conn->query("SHOW TABLES LIKE 'tbl_auditoria_convivencia'");
if ($r->num_rows > 0) {
    echo "Tabla tbl_auditoria_convivencia YA EXISTE. Nada que hacer.\n";
    $conn->close();
    exit(0);
}

$ddl = "CREATE TABLE tbl_auditoria_convivencia (
    id_auditoria   INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario     INT NOT NULL,
    email          VARCHAR(255) NOT NULL,
    nombre_usuario VARCHAR(255),
    id_cliente     INT NOT NULL,
    id_comite      INT,
    metodo_http    VARCHAR(10) NOT NULL,
    ruta           VARCHAR(500) NOT NULL,
    parametros     TEXT,
    ip             VARCHAR(45),
    user_agent     VARCHAR(500),
    accion_resumen VARCHAR(255),
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (id_usuario),
    INDEX idx_cliente (id_cliente),
    INDEX idx_created (created_at),
    INDEX idx_ruta (ruta(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Auditoria de accesos al modulo de Comite de Convivencia (Ley 1010 / Res 3461)'";

echo "DDL a ejecutar:\n{$ddl}\n\n";

if (!$apply) {
    echo "[DRY-RUN] Sin cambios. Para aplicar, vuelve a correr con --apply.\n";
    $conn->close();
    exit(0);
}

try {
    if (!$conn->query($ddl)) {
        echo "ERROR CREATE: " . $conn->error . "\n"; exit(1);
    }
    echo "OK. Tabla creada.\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n"; exit(1);
}

// Verificacion
$r = $conn->query("SHOW TABLES LIKE 'tbl_auditoria_convivencia'");
echo $r->num_rows > 0 ? "Verificado: tabla existe.\n" : "ERROR: la tabla no existe tras CREATE.\n";

$conn->close();
