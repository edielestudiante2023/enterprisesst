<?php
/**
 * Script para agregar columna id_plantilla a tbl_doc_documentos
 * Ejecutar: php ejecutar_add_plantilla.php
 */

echo "=====================================================\n";
echo "AGREGAR COLUMNA id_plantilla A tbl_doc_documentos\n";
echo "=====================================================\n\n";

// Configuraciones de conexión
$configs = [
    'LOCAL' => [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'db' => 'empresas_sst'
    ],
    'PRODUCTION' => [
        'host' => 'localhost',
        'user' => 'enterprisesst',
        'pass' => 'j&HoLtM!@bO6',
        'db' => 'enterprisesst'
    ]
];

// Detectar entorno
$isProduction = (PHP_OS !== 'WINNT' && PHP_OS !== 'WIN32');
$env = $isProduction ? 'PRODUCTION' : 'LOCAL';

echo "Entorno detectado: $env\n\n";

$config = $configs[$env];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Conexión exitosa a {$config['db']}\n\n";

    // Verificar si la columna ya existe
    $stmt = $pdo->query("
        SELECT COUNT(*) as existe
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = '{$config['db']}'
        AND TABLE_NAME = 'tbl_doc_documentos'
        AND COLUMN_NAME = 'id_plantilla'
    ");
    $existe = $stmt->fetch(PDO::FETCH_ASSOC)['existe'];

    if ($existe > 0) {
        echo "La columna 'id_plantilla' ya existe en tbl_doc_documentos\n";
    } else {
        echo "Agregando columna 'id_plantilla'...\n";
        $pdo->exec("ALTER TABLE tbl_doc_documentos ADD COLUMN id_plantilla INT NULL AFTER id_tipo");
        echo "Columna agregada exitosamente!\n";
    }

    // Mostrar estructura actual
    echo "\nEstructura actual de tbl_doc_documentos:\n";
    echo str_repeat("-", 60) . "\n";

    $stmt = $pdo->query("DESCRIBE tbl_doc_documentos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col) {
        printf("%-25s %-20s %s\n", $col['Field'], $col['Type'], $col['Null'] === 'YES' ? 'NULL' : 'NOT NULL');
    }

    echo "\n=====================================================\n";
    echo "PROCESO COMPLETADO EXITOSAMENTE\n";
    echo "=====================================================\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
