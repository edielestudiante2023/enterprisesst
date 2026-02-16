#!/usr/bin/env php
<?php
/**
 * Actualiza Índice de Frecuencia (IF) e Índice de Severidad (IS)
 * para aclarar período rolling 12 meses en todos los clientes.
 *
 * Uso: php scripts/update_if_is_rolling.php [local|produccion]
 */

if (php_sapi_name() !== 'cli') {
    die("Solo ejecución CLI.\n");
}

$entorno = $argv[1] ?? null;
if (!in_array($entorno, ['local', 'produccion'])) {
    die("Uso: php scripts/update_if_is_rolling.php [local|produccion]\n");
}

// Configuración por entorno
if ($entorno === 'local') {
    $config = [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl'      => false,
    ];
} else {
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl'      => true,
    ];
}

echo "=== Actualizando IF e IS (rolling 12 meses) - {$entorno} ===\n";
echo "Host: {$config['host']}:{$config['port']}\n\n";

// Conectar
$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

if ($config['ssl']) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = true;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    echo "[OK] Conexión establecida.\n";
} catch (PDOException $e) {
    die("[ERROR] No se pudo conectar: " . $e->getMessage() . "\n");
}

// Definir actualizaciones
$updates = [
    [
        'nombre'          => 'Índice de Frecuencia de Accidentes de Trabajo (IF)',
        'formula'         => '(N° AT acumulados últimos 12 meses / HHT acumuladas últimos 12 meses) × 240.000',
        'definicion'      => 'Expresa el número de accidentes de trabajo ocurridos por cada 240.000 HHT. Se calcula con datos acumulados de los últimos 12 meses (rolling) para evitar distorsiones por meses con pocas horas.',
        'interpretacion'  => 'A menor valor, menor frecuencia de accidentalidad. Cálculo mensual con ventana móvil de 12 meses. Comparar con el periodo anterior y con la media del sector económico.',
        'origen_datos'    => 'FURAT, registro de accidentes de trabajo, nómina (HHT acumulada 12 meses)',
    ],
    [
        'nombre'          => 'Índice de Severidad de Accidentes de Trabajo (IS)',
        'formula'         => '(Días perdidos y cargados por AT acumulados últimos 12 meses / HHT acumuladas últimos 12 meses) × 240.000',
        'definicion'      => 'Expresa el número de días perdidos y cargados por AT por cada 240.000 HHT. Se calcula con datos acumulados de los últimos 12 meses (rolling) para evitar distorsiones por meses con pocas horas.',
        'interpretacion'  => 'A menor valor, menor severidad de los accidentes. Cálculo mensual con ventana móvil de 12 meses. Valores altos indican accidentes graves con muchos días de incapacidad.',
        'origen_datos'    => 'FURAT, incapacidades por AT, nómina (HHT acumulada 12 meses)',
    ],
];

// Contar registros antes
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_indicadores_sst WHERE nombre_indicador = ?");

$totalActualizados = 0;

$pdo->beginTransaction();

try {
    $updateStmt = $pdo->prepare("
        UPDATE tbl_indicadores_sst
        SET formula = ?,
            definicion = ?,
            interpretacion = ?,
            origen_datos = ?
        WHERE nombre_indicador = ?
    ");

    foreach ($updates as $u) {
        // Contar cuántos hay
        $countStmt->execute([$u['nombre']]);
        $count = $countStmt->fetchColumn();

        echo "\n[{$u['nombre']}]\n";
        echo "  Registros encontrados: {$count}\n";

        if ($count == 0) {
            echo "  >> Sin registros, saltando.\n";
            continue;
        }

        $updateStmt->execute([
            $u['formula'],
            $u['definicion'],
            $u['interpretacion'],
            $u['origen_datos'],
            $u['nombre'],
        ]);

        $affected = $updateStmt->rowCount();
        echo "  >> Actualizados: {$affected}\n";
        $totalActualizados += $affected;
    }

    $pdo->commit();
    echo "\n=== RESULTADO: {$totalActualizados} registros actualizados en {$entorno} ===\n";
    echo "[OK] Transacción confirmada.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "\n[ERROR] " . $e->getMessage() . "\n";
    echo "[ROLLBACK] No se aplicaron cambios.\n";
    exit(1);
}

// Verificación post-update
echo "\n--- Verificación ---\n";
$verifyStmt = $pdo->prepare("
    SELECT id_cliente, nombre_indicador, formula
    FROM tbl_indicadores_sst
    WHERE nombre_indicador IN (?, ?)
    ORDER BY id_cliente, nombre_indicador
    LIMIT 10
");
$verifyStmt->execute([
    'Índice de Frecuencia de Accidentes de Trabajo (IF)',
    'Índice de Severidad de Accidentes de Trabajo (IS)',
]);

foreach ($verifyStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  Cliente {$row['id_cliente']} | {$row['nombre_indicador']}\n";
    echo "    Formula: {$row['formula']}\n";
}

echo "\n[DONE]\n";
