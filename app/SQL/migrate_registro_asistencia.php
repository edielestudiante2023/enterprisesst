<?php
/**
 * Migración: Módulo Registro de Asistencia (transversal)
 * Tablas: tbl_registro_asistencia (master) + tbl_registro_asistencia_asistente (detalle)
 * Uso: php migrate_registro_asistencia.php [local|production]
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la línea de comandos.');
}

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => '',
        'database' => 'empresas_sst',
        'ssl'      => false,
    ];
} elseif ($env === 'production') {
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'user'     => 'cycloid_userdb',
        'password' => getenv('DB_PROD_PASS') ?: 'AVNS_MR2SLvzRh3i_7o9fEHN',
        'database' => 'empresas_sst',
        'ssl'      => true,
    ];
} else {
    die("Uso: php migrate_registro_asistencia.php [local|production]\n");
}

echo "=== Migración SQL - Módulo Registro de Asistencia ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$config['host']}:{$config['port']}\n";
echo "Database: {$config['database']}\n";
echo "---\n";

$mysqli = mysqli_init();

if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}

$connected = @$mysqli->real_connect(
    $config['host'],
    $config['user'],
    $config['password'],
    $config['database'],
    $config['port'],
    null,
    $config['ssl'] ? MYSQLI_CLIENT_SSL : 0
);

if (!$connected) {
    die("[ERROR] No se pudo conectar a MySQL: " . $mysqli->connect_error . "\n");
}

echo "[OK] Conectado a MySQL.\n\n";

$queries = [];

// ── 1. Tabla master: tbl_registro_asistencia ──
$queries['tbl_registro_asistencia'] = "
    CREATE TABLE IF NOT EXISTS tbl_registro_asistencia (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_cliente INT NOT NULL,
        id_consultor INT NOT NULL,
        fecha_sesion DATE NOT NULL,

        tema TEXT NULL,
        lugar VARCHAR(255) NULL,
        objetivo TEXT NULL,
        capacitador VARCHAR(255) NULL,
        tipo_reunion ENUM(
            'capacitacion',
            'charla',
            'socializacion',
            'reunion_general',
            'comite',
            'brigada',
            'simulacro',
            'induccion_reinduccion',
            'otro'
        ) NULL,
        material VARCHAR(255) NULL,
        tiempo_horas DECIMAL(4,1) NULL,

        observaciones TEXT NULL,
        ruta_pdf_asistencia VARCHAR(255) NULL,
        estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',

        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX idx_reg_asist_cliente (id_cliente),
        INDEX idx_reg_asist_consultor (id_consultor),
        INDEX idx_reg_asist_estado (estado),
        INDEX idx_reg_asist_tipo (tipo_reunion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

// ── 2. Tabla detalle: tbl_registro_asistencia_asistente ──
$queries['tbl_registro_asistencia_asistente'] = "
    CREATE TABLE IF NOT EXISTS tbl_registro_asistencia_asistente (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_asistencia INT NOT NULL,
        nombre VARCHAR(255) NOT NULL,
        cedula VARCHAR(50) NOT NULL,
        cargo VARCHAR(255) NULL,
        firma VARCHAR(255) NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        INDEX idx_reg_asist_det_master (id_asistencia)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

foreach ($queries as $tabla => $sql) {
    if ($mysqli->query($sql)) {
        echo "[OK] {$tabla} creada/verificada.\n";
    } else {
        echo "[ERROR] {$tabla}: " . $mysqli->error . "\n";
    }
}

// ── 3. Registro en detail_report (catálogo) ──
$check = $mysqli->query("SELECT id_detailreport FROM detail_report WHERE id_detailreport = 14");
if ($check && $check->num_rows === 0) {
    if ($mysqli->query("INSERT INTO detail_report (id_detailreport, detail_report) VALUES (14, 'Registro de Asistencia')")) {
        echo "[OK] detail_report id=14 'Registro de Asistencia' insertado.\n";
    } else {
        echo "[ERROR] detail_report: " . $mysqli->error . "\n";
    }
} else {
    echo "[SKIP] detail_report id=14 ya existe.\n";
}

$mysqli->close();
echo "\n=== Migración completada ===\n";
