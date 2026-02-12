<?php
/**
 * Script CLI para crear la tabla tbl_pta_cliente_audit
 * Uso: php apply_audit_table.php [local|production]
 */

if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ejecutarse desde la linea de comandos.\n");
}

$env = $argv[1] ?? 'local';

$configs = [
    'local' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl'      => false,
    ],
    'production' => [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl'      => true,
    ],
];

if (!isset($configs[$env])) {
    die("Entorno invalido. Use: php apply_audit_table.php [local|production]\n");
}

$cfg = $configs[$env];
echo "=== Aplicando SQL en: " . strtoupper($env) . " ===\n";
echo "Host: {$cfg['host']}:{$cfg['port']}\n";
echo "Database: {$cfg['database']}\n\n";

try {
    $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    if ($cfg['ssl']) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $options);
    echo "[OK] Conexion establecida.\n";

    // Verificar si la tabla ya existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_pta_cliente_audit'");
    if ($stmt->rowCount() > 0) {
        echo "[INFO] La tabla tbl_pta_cliente_audit YA EXISTE. No se realizan cambios.\n";
        exit(0);
    }

    // Crear la tabla
    $sql = "CREATE TABLE IF NOT EXISTS tbl_pta_cliente_audit (
        id_audit          INT AUTO_INCREMENT PRIMARY KEY,
        id_ptacliente     INT,
        id_cliente        INT,
        accion            ENUM('INSERT','UPDATE','DELETE','BULK_UPDATE'),
        campo_modificado  VARCHAR(100),
        valor_anterior    TEXT,
        valor_nuevo       TEXT,
        id_usuario        INT,
        nombre_usuario    VARCHAR(255),
        email_usuario     VARCHAR(255),
        rol_usuario       VARCHAR(50),
        ip_address        VARCHAR(45),
        user_agent        TEXT,
        metodo            VARCHAR(100),
        descripcion       TEXT,
        fecha_accion      DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "[OK] Tabla tbl_pta_cliente_audit creada exitosamente.\n";

    // Verificar creacion
    $stmt = $pdo->query("DESCRIBE tbl_pta_cliente_audit");
    $columns = $stmt->fetchAll();
    echo "[OK] Verificacion: " . count($columns) . " columnas creadas.\n";

    foreach ($columns as $col) {
        echo "     - {$col['Field']} ({$col['Type']})\n";
    }

    echo "\n[EXITO] SQL aplicado correctamente en " . strtoupper($env) . ".\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
