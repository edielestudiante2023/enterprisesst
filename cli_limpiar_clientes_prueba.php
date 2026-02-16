<?php
/**
 * CLI Script - Limpieza total de datos de clientes de prueba
 *
 * Estrategia: Consulta INFORMATION_SCHEMA.COLUMNS para descubrir TODAS las tablas
 * que tienen columna `id_cliente`, y elimina registros de los clientes indicados
 * en TODAS ellas, EXCEPTO tbl_clientes (donde se conservan activos).
 *
 * Uso: php cli_limpiar_clientes_prueba.php prod
 */

// Detectar entorno
$env = $argv[1] ?? 'local';

echo "\n========================================\n";
echo "  LIMPIEZA CLIENTES DE PRUEBA\n";
echo "  Entorno: " . strtoupper($env) . "\n";
echo "========================================\n\n";

// Configuración de conexión
if ($env === 'prod') {
    $config = [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ];
} else {
    $config = [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl' => false
    ];
}

// Conectar
echo "[*] Conectando a {$config['host']}:{$config['port']}...\n";

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];

    if ($config['ssl']) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    echo "[OK] Conexión establecida\n\n";
} catch (PDOException $e) {
    echo "[ERROR] No se pudo conectar: " . $e->getMessage() . "\n";
    exit(1);
}

// ============================================================
// PASO 1: Identificar los IDs de los clientes de prueba
// ============================================================
echo "[*] Buscando clientes de prueba...\n";

// IDs conocidos: 19 = EMPRESA OMEGA, 21 = CLIENTE DE INDUCCIÓN
$ids = [19, 21];

$stmt = $pdo->prepare("SELECT id_cliente, nombre_cliente FROM tbl_clientes WHERE id_cliente IN (?, ?)");
$stmt->execute($ids);
$clientes = $stmt->fetchAll();

if (count($clientes) === 0) {
    echo "[ERROR] No se encontraron los clientes con IDs 19 y 21\n";
    exit(1);
}

foreach ($clientes as $c) {
    echo "  - ID {$c['id_cliente']}: {$c['nombre_cliente']}\n";
}

$idsPlaceholders = implode(',', array_fill(0, count($ids), '?'));
echo "\n";

// ============================================================
// PASO 2: Descubrir TODAS las tablas con columna id_cliente
// ============================================================
echo "[*] Consultando INFORMATION_SCHEMA para descubrir tablas con id_cliente...\n";

$stmt = $pdo->prepare("
    SELECT c.TABLE_NAME
    FROM INFORMATION_SCHEMA.COLUMNS c
    INNER JOIN INFORMATION_SCHEMA.TABLES t
        ON c.TABLE_SCHEMA = t.TABLE_SCHEMA AND c.TABLE_NAME = t.TABLE_NAME
    WHERE c.TABLE_SCHEMA = ?
      AND c.COLUMN_NAME = 'id_cliente'
      AND t.TABLE_TYPE = 'BASE TABLE'
    ORDER BY c.TABLE_NAME
");
$stmt->execute([$config['database']]);
$tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "[OK] Tablas con id_cliente encontradas: " . count($tablas) . "\n\n";

// Excluir tbl_clientes
$tablasExcluidas = ['tbl_clientes'];
$tablasALimpiar = array_filter($tablas, function($t) use ($tablasExcluidas) {
    return !in_array($t, $tablasExcluidas);
});

echo "Tablas a limpiar (" . count($tablasALimpiar) . "):\n";
foreach ($tablasALimpiar as $t) {
    echo "  - {$t}\n";
}
echo "\nTablas excluidas (se conservan): " . implode(', ', $tablasExcluidas) . "\n\n";

// ============================================================
// PASO 3: Contar registros ANTES de borrar (reporte previo)
// ============================================================
echo "[*] Contando registros por tabla ANTES de la limpieza...\n\n";

$conteos = [];
foreach ($tablasALimpiar as $tabla) {
    $sql = "SELECT COUNT(*) FROM `{$tabla}` WHERE id_cliente IN ({$idsPlaceholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    $count = (int) $stmt->fetchColumn();
    $conteos[$tabla] = $count;

    $marker = $count > 0 ? "[>>]" : "[--]";
    echo "  {$marker} {$tabla}: {$count} registros\n";
}

$totalRegistros = array_sum($conteos);
$tablasConDatos = count(array_filter($conteos, fn($c) => $c > 0));

echo "\n  TOTAL: {$totalRegistros} registros en {$tablasConDatos} tablas con datos\n\n";

if ($totalRegistros === 0) {
    echo "[OK] No hay registros que eliminar. Los clientes ya están limpios.\n";
    exit(0);
}

// ============================================================
// PASO 4: Ejecutar DELETEs dentro de una transacción
// ============================================================
echo "[*] Iniciando eliminación en transacción...\n\n";

try {
    $pdo->beginTransaction();

    $eliminados = [];

    foreach ($tablasALimpiar as $tabla) {
        if ($conteos[$tabla] === 0) continue;

        $sql = "DELETE FROM `{$tabla}` WHERE id_cliente IN ({$idsPlaceholders})";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);
        $rows = $stmt->rowCount();
        $eliminados[$tabla] = $rows;

        echo "  [OK] {$tabla}: {$rows} registros eliminados\n";
    }

    $pdo->commit();
    echo "\n[OK] COMMIT exitoso - Transacción completada\n";

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "\n[ERROR] ROLLBACK - Error durante eliminación: " . $e->getMessage() . "\n";
    echo "[!] No se modificó ningún dato.\n";
    exit(1);
}

// ============================================================
// PASO 5: Verificación post-limpieza
// ============================================================
echo "\n[*] Verificando que la limpieza fue completa...\n\n";

$residuos = 0;
foreach ($tablasALimpiar as $tabla) {
    $sql = "SELECT COUNT(*) FROM `{$tabla}` WHERE id_cliente IN ({$idsPlaceholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    $count = (int) $stmt->fetchColumn();

    if ($count > 0) {
        echo "  [!!] {$tabla}: QUEDAN {$count} registros\n";
        $residuos += $count;
    }
}

if ($residuos === 0) {
    echo "  [OK] Verificación exitosa: 0 registros residuales\n";
} else {
    echo "\n  [!!] ALERTA: Quedan {$residuos} registros sin eliminar\n";
}

// ============================================================
// PASO 6: Confirmar que siguen en tbl_clientes
// ============================================================
echo "\n[*] Verificando que los clientes siguen activos en tbl_clientes...\n";

$stmt = $pdo->prepare("SELECT id_cliente, nombre_cliente, estado FROM tbl_clientes WHERE id_cliente IN ({$idsPlaceholders})");
$stmt->execute($ids);
$clientesPost = $stmt->fetchAll();

foreach ($clientesPost as $c) {
    echo "  [OK] ID {$c['id_cliente']}: {$c['nombre_cliente']} - Estado: {$c['estado']}\n";
}

// Resumen final
echo "\n========================================\n";
echo "  RESUMEN FINAL\n";
echo "========================================\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Clientes limpiados: " . implode(', ', array_column($clientes, 'nombre_cliente')) . "\n";
echo "Tablas escaneadas: " . count($tablas) . "\n";
echo "Tablas con datos eliminados: " . count($eliminados) . "\n";
echo "Total registros eliminados: " . array_sum($eliminados) . "\n";
echo "Registros residuales: {$residuos}\n";
echo "Clientes conservados en tbl_clientes: SI\n";
echo "========================================\n\n";

exit($residuos > 0 ? 1 : 0);
