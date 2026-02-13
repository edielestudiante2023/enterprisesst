<?php
/**
 * Script CLI para agregar columnas de firma digital a tbl_contratos
 * Uso: php app/SQL/_temp_query.php [local|production]
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'empresas_sst';
    $user = 'cycloid_userdb';
    $pass = 'AVNS_iDypWizlpMRwHIORJGG';
    $ssl  = [PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false];
} else {
    $host = 'localhost';
    $port = 3306;
    $db   = 'empresas_sst';
    $user = 'root';
    $pass = '';
    $ssl  = [];
}

echo "=== Ejecutando en: " . strtoupper($env) . " ($host:$port) ===\n\n";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user, $pass,
        array_merge([PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION], $ssl)
    );
    echo "Conexion exitosa.\n\n";
} catch (PDOException $e) {
    echo "ERROR de conexion: " . $e->getMessage() . "\n";
    exit(1);
}

// Columnas a agregar
$columnas = [
    "ADD COLUMN token_firma VARCHAR(64) NULL",
    "ADD COLUMN token_firma_expiracion DATETIME NULL",
    "ADD COLUMN estado_firma VARCHAR(20) DEFAULT 'sin_enviar'",
    "ADD COLUMN firma_cliente_nombre VARCHAR(200) NULL",
    "ADD COLUMN firma_cliente_cedula VARCHAR(20) NULL",
    "ADD COLUMN firma_cliente_imagen VARCHAR(255) NULL",
    "ADD COLUMN firma_cliente_ip VARCHAR(45) NULL",
    "ADD COLUMN firma_cliente_fecha DATETIME NULL",
];

foreach ($columnas as $col) {
    // Extraer nombre de la columna
    preg_match('/COLUMN\s+(\w+)/', $col, $matches);
    $nombreCol = $matches[1] ?? 'desconocido';

    echo "--- $nombreCol ---\n";
    try {
        $pdo->exec("ALTER TABLE tbl_contratos $col");
        echo "  OK: Columna agregada\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "  INFO: Ya existe, saltando\n";
        } else {
            echo "  ERROR: " . $e->getMessage() . "\n";
        }
    }
}

// Agregar indice para token_firma
echo "\n--- Indice idx_contrato_token_firma ---\n";
try {
    $pdo->exec("CREATE INDEX idx_contrato_token_firma ON tbl_contratos(token_firma)");
    echo "  OK: Indice creado\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
        echo "  INFO: Indice ya existe\n";
    } else {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
}

// Verificacion
echo "\n=== VERIFICACION ===\n";
$stmt = $pdo->query("SHOW COLUMNS FROM tbl_contratos WHERE Field IN ('token_firma','token_firma_expiracion','estado_firma','firma_cliente_nombre','firma_cliente_cedula','firma_cliente_imagen','firma_cliente_ip','firma_cliente_fecha')");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Columnas encontradas: " . count($cols) . "/8\n";
foreach ($cols as $c) {
    echo "  {$c['Field']}: {$c['Type']} | Null={$c['Null']} | Default={$c['Default']}\n";
}

if (count($cols) === 8) {
    echo "\nTODO OK - Las 8 columnas existen.\n";
} else {
    echo "\nADVERTENCIA: Faltan " . (8 - count($cols)) . " columnas.\n";
}
