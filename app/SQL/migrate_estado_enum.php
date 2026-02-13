<?php
/**
 * =====================================================
 * Migración: Extender ENUM estado de tbl_documentos_sst
 * para soportar flujo de firma electrónica
 * =====================================================
 *
 * Uso:
 *   php migrate_estado_enum.php local
 *   php migrate_estado_enum.php production
 *
 * Estados nuevos: en_revision, pendiente_firma, firmado
 */

// Validar argumento
$env = $argv[1] ?? null;

if (!in_array($env, ['local', 'production'])) {
    echo "=== ERROR: Debe especificar el entorno ===\n";
    echo "Uso: php migrate_estado_enum.php [local|production]\n";
    exit(1);
}

// Configuración por entorno
$configs = [
    'local' => [
        'host'     => '127.0.0.1',
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

$cfg = $configs[$env];

echo "=== Migración ENUM estado - tbl_documentos_sst ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$cfg['host']}:{$cfg['port']}\n";
echo "Base de datos: {$cfg['database']}\n";
echo "SSL: " . ($cfg['ssl'] ? 'SI' : 'NO') . "\n";
echo "---------------------------------------------------\n";

// Conectar
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
    echo "[OK] Conexión establecida.\n";

} catch (PDOException $e) {
    echo "[ERROR] No se pudo conectar: " . $e->getMessage() . "\n";
    exit(2);
}

// 1. Verificar estado actual del ENUM
echo "\n--- Paso 1: Verificar estado actual del ENUM ---\n";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_documentos_sst LIKE 'estado'");
    $col = $stmt->fetch();

    if (!$col) {
        echo "[ERROR] No se encontró la columna 'estado' en tbl_documentos_sst.\n";
        exit(3);
    }

    echo "Tipo actual: {$col['Type']}\n";

    // Verificar si ya tiene los nuevos estados
    $tieneNuevos = (
        strpos($col['Type'], 'en_revision') !== false &&
        strpos($col['Type'], 'pendiente_firma') !== false &&
        strpos($col['Type'], 'firmado') !== false
    );

    if ($tieneNuevos) {
        echo "[INFO] El ENUM ya contiene los estados nuevos. No se requiere migración.\n";
        echo "=== MIGRACIÓN COMPLETADA (sin cambios) ===\n";
        exit(0);
    }

    echo "[INFO] Falta(n) estado(s) nuevo(s). Se procederá con la migración.\n";

} catch (PDOException $e) {
    echo "[ERROR] No se pudo verificar la columna: " . $e->getMessage() . "\n";
    exit(3);
}

// 2. Ejecutar ALTER TABLE
echo "\n--- Paso 2: Ejecutar ALTER TABLE ---\n";
$sql = "ALTER TABLE tbl_documentos_sst
MODIFY COLUMN estado ENUM('borrador', 'generado', 'en_revision', 'pendiente_firma', 'aprobado', 'firmado', 'obsoleto') NOT NULL DEFAULT 'borrador'";

try {
    $pdo->exec($sql);
    echo "[OK] ALTER TABLE ejecutado exitosamente.\n";
} catch (PDOException $e) {
    echo "[ERROR] Falló el ALTER TABLE: " . $e->getMessage() . "\n";
    exit(4);
}

// 3. Verificar resultado
echo "\n--- Paso 3: Verificar resultado ---\n";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_documentos_sst LIKE 'estado'");
    $col = $stmt->fetch();
    echo "Tipo nuevo: {$col['Type']}\n";

    $tieneNuevos = (
        strpos($col['Type'], 'en_revision') !== false &&
        strpos($col['Type'], 'pendiente_firma') !== false &&
        strpos($col['Type'], 'firmado') !== false
    );

    if ($tieneNuevos) {
        echo "[OK] Verificación exitosa. Todos los estados están presentes.\n";
    } else {
        echo "[WARN] La verificación indica que faltan estados. Revisar manualmente.\n";
    }

} catch (PDOException $e) {
    echo "[WARN] No se pudo verificar: " . $e->getMessage() . "\n";
}

// 4. Mostrar registros actuales
echo "\n--- Paso 4: Conteo de documentos por estado ---\n";
try {
    $stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM tbl_documentos_sst GROUP BY estado ORDER BY estado");
    $rows = $stmt->fetchAll();

    if (empty($rows)) {
        echo "(sin registros)\n";
    } else {
        foreach ($rows as $row) {
            echo "  {$row['estado']}: {$row['total']} documento(s)\n";
        }
    }
} catch (PDOException $e) {
    echo "[WARN] No se pudo contar: " . $e->getMessage() . "\n";
}

echo "\n=== MIGRACIÓN COMPLETADA EXITOSAMENTE en " . strtoupper($env) . " ===\n";
exit(0);
