<?php
/**
 * Crea tbl_carpeta_reporte_vinculo: tabla puente N:M entre tbl_doc_carpetas y tbl_reporte.
 *
 * Permite "vincular" un reporte existente del reportList a una carpeta de documentacion
 * SIN duplicar el archivo. La carpeta solo muestra el reporte como referencia
 * apuntando al enlace original.
 *
 * Uso:
 *   php scripts/crear_tbl_carpeta_reporte_vinculo.php             # local dry-run
 *   php scripts/crear_tbl_carpeta_reporte_vinculo.php --apply     # local apply
 *   php scripts/crear_tbl_carpeta_reporte_vinculo.php --prod              # prod dry-run
 *   php scripts/crear_tbl_carpeta_reporte_vinculo.php --prod --apply      # prod apply
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
    if ($host === '' || $user === '' || $pass === '') { echo "ERROR env vars\n"; exit(1); }
    $conn = mysqli_init();
    mysqli_ssl_set($conn, null, null, null, null, null);
    if (!@mysqli_real_connect($conn, $host, $user, $pass, $db, $port, null, MYSQLI_CLIENT_SSL)) {
        echo "ERROR conn: " . mysqli_connect_error() . "\n"; exit(1);
    }
} else {
    $conn = new mysqli('localhost', 'root', '', 'empresas_sst');
    if ($conn->connect_error) { echo "ERROR\n"; exit(1); }
}
$conn->set_charset('utf8mb4');

$r = $conn->query("SHOW TABLES LIKE 'tbl_carpeta_reporte_vinculo'");
if ($r->num_rows > 0) {
    echo "Tabla YA EXISTE. Nada que hacer.\n";
    $conn->close();
    exit(0);
}

$ddl = "CREATE TABLE tbl_carpeta_reporte_vinculo (
    id_vinculo  INT AUTO_INCREMENT PRIMARY KEY,
    id_carpeta  INT NOT NULL,
    id_reporte  INT NOT NULL,
    id_cliente  INT NOT NULL COMMENT 'Denorm para validar consistencia y queries rapidas',
    observacion VARCHAR(500) NULL COMMENT 'Por que el consultor lo vinculo (opcional)',
    created_by  INT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_carpeta_reporte (id_carpeta, id_reporte),
    INDEX idx_carpeta (id_carpeta),
    INDEX idx_reporte (id_reporte),
    INDEX idx_cliente (id_cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Vinculos N:M entre carpetas de documentacion y reportes del reportList. NO copia archivos.'";

echo "DDL:\n{$ddl}\n\n";

if (!$apply) {
    echo "[DRY-RUN] Sin cambios. Para aplicar usar --apply.\n";
    $conn->close();
    exit(0);
}

try {
    if (!$conn->query($ddl)) { echo "ERROR CREATE: " . $conn->error . "\n"; exit(1); }
    echo "OK. Tabla creada.\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n"; exit(1);
}

$r = $conn->query("SHOW TABLES LIKE 'tbl_carpeta_reporte_vinculo'");
echo $r->num_rows > 0 ? "Verificado.\n" : "ERROR: tabla no existe tras CREATE.\n";

$conn->close();
