<?php
/**
 * DESCUBRIMIENTO (solo lectura) - Lista todas las tablas con columna id_cliente
 * y cuenta filas para un cliente dado. NO MODIFICA NADA.
 *
 * Uso: php cli_descubrir_tablas_cliente.php [local|prod] [id_cliente]
 */

$entorno   = $argv[1] ?? 'local';
$idCliente = (int)($argv[2] ?? 11);

$conexiones = [
    'local' => [
        'dsn'  => 'mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4',
        'user' => 'root',
        'pass' => '',
        'opts' => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
    ],
    'prod' => [
        'dsn'  => 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
        'user' => 'cycloid_userdb',
        'pass' => 'AVNS_MR2SLvzRh3i_7o9fEHN',
        'opts' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ],
    ],
];

if (!isset($conexiones[$entorno])) {
    die("Entorno invalido. Usa 'local' o 'prod'\n");
}

$c = $conexiones[$entorno];
echo "=== DESCUBRIMIENTO [{$entorno}] cliente_id={$idCliente} ===\n";

try {
    $pdo = new PDO($c['dsn'], $c['user'], $c['pass'], $c['opts']);
} catch (PDOException $e) {
    die("Error conexion: " . $e->getMessage() . "\n");
}

// 1) Listar tablas con columna id_cliente
$sql = "SELECT TABLE_NAME, COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND (COLUMN_NAME = 'id_cliente' OR COLUMN_NAME = 'cliente_id')
        ORDER BY TABLE_NAME";
$tablas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

echo "Tablas con columna id_cliente/cliente_id: " . count($tablas) . "\n\n";
printf("%-50s %-15s %10s\n", "TABLA", "COLUMNA", "FILAS_CL");
echo str_repeat("-", 80) . "\n";

$totalFilas = 0;
$tablasConDatos = [];

foreach ($tablas as $t) {
    $tabla = $t['TABLE_NAME'];
    $col   = $t['COLUMN_NAME'];
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$tabla}` WHERE `{$col}` = ?");
        $stmt->execute([$idCliente]);
        $n = (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        $n = -1;
    }
    printf("%-50s %-15s %10d\n", $tabla, $col, $n);
    $totalFilas += max(0, $n);
    if ($n > 0) {
        $tablasConDatos[$tabla] = ['col' => $col, 'filas' => $n];
    }
}

echo str_repeat("-", 80) . "\n";
echo "TOTAL filas cliente {$idCliente}: {$totalFilas}\n\n";

// 2) Resumen de tablas con datos
echo "=== TABLAS CON DATOS DEL CLIENTE {$idCliente} ===\n";
foreach ($tablasConDatos as $tabla => $info) {
    echo "  {$tabla} ({$info['col']}) => {$info['filas']}\n";
}

// 3) Check clave: tbl_cliente_estandares (para saber cuantos estandares tiene configurados)
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_cliente_estandares WHERE id_cliente = ?");
    $stmt->execute([$idCliente]);
    echo "\ntbl_cliente_estandares: " . $stmt->fetchColumn() . " registros\n";
} catch (PDOException $e) {}

echo "\n=== FIN DESCUBRIMIENTO ===\n";
