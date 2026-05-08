<?php
/**
 * Crear tbl_socializaciones para registrar envios de PDFs de socializacion
 * (miembros del comite y cronograma de reuniones) a los colaboradores via email.
 *
 * El PDF en si y el PDF de evidencia se guardan en tbl_documentos_sst; aqui solo
 * vive la metadata del envio: a quien se le mando, asunto/cuerpo del email,
 * estado de cada envio, y snapshot de los datos del PDF.
 *
 * Uso:
 *   php scripts/crear_tbl_socializaciones.php             # local dry-run
 *   php scripts/crear_tbl_socializaciones.php --apply     # local apply
 *   php scripts/crear_tbl_socializaciones.php --prod              # prod dry-run
 *   php scripts/crear_tbl_socializaciones.php --prod --apply      # prod apply
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

// Idempotencia
$r = $conn->query("SHOW TABLES LIKE 'tbl_socializaciones'");
if ($r->num_rows > 0) {
    echo "Tabla tbl_socializaciones YA EXISTE. Nada que hacer.\n";
    $conn->close();
    exit(0);
}

$ddl = "CREATE TABLE tbl_socializaciones (
    id_socializacion          INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente                INT NOT NULL,
    id_comite                 INT NULL,
    id_proceso                INT NULL,
    tipo_socializacion        VARCHAR(40) NOT NULL COMMENT 'miembros | cronograma',
    tipo_comite               VARCHAR(20) NOT NULL COMMENT 'COPASST | COCOLAB | BRIGADA',
    id_documento_sst          INT NULL COMMENT 'FK a tbl_documentos_sst del PDF principal',
    id_documento_evidencia    INT NULL COMMENT 'FK a tbl_documentos_sst del PDF de evidencia',
    asunto_email              VARCHAR(500),
    cuerpo_email              TEXT,
    destinatarios_json        JSON COMMENT '[{email, nombre, status, error_msg}]',
    total_destinatarios       INT NOT NULL DEFAULT 0,
    enviados_ok               INT NOT NULL DEFAULT 0,
    fallidos                  INT NOT NULL DEFAULT 0,
    estado                    VARCHAR(20) NOT NULL DEFAULT 'borrador' COMMENT 'borrador | enviado | parcial | fallido',
    contenido_snapshot        JSON COMMENT 'datos del PDF (periodo, mensaje, fechas cronograma, etc)',
    created_by                INT,
    created_at                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cliente (id_cliente),
    INDEX idx_comite (id_comite),
    INDEX idx_proceso (id_proceso),
    INDEX idx_tipo (tipo_socializacion, tipo_comite),
    INDEX idx_estado (estado),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Metadata de envios de socializacion (PDF miembros / cronograma) por email a colaboradores'";

echo "DDL:\n{$ddl}\n\n";

if (!$apply) {
    echo "[DRY-RUN] Sin cambios. Para aplicar usar --apply.\n";
    $conn->close();
    exit(0);
}

try {
    if (!$conn->query($ddl)) {
        echo "ERROR CREATE: " . $conn->error . "\n";
        exit(1);
    }
    echo "OK. Tabla creada.\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n"; exit(1);
}

// Verificacion
$r = $conn->query("SHOW TABLES LIKE 'tbl_socializaciones'");
echo $r->num_rows > 0 ? "Verificado: tabla existe.\n" : "ERROR: la tabla no existe tras CREATE.\n";

$conn->close();
