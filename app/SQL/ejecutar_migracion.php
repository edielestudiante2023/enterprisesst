<?php
/**
 * Script para ejecutar SQL en LOCAL y PRODUCCION
 * Uso: php ejecutar_migracion.php nombre_archivo.sql
 */

if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ejecutarse desde la línea de comandos.\n");
}

if ($argc < 2) {
    echo "Uso: php ejecutar_migracion.php <archivo.sql>\n";
    echo "Ejemplo: php ejecutar_migracion.php crear_tablas_indicadores_sst.sql\n";
    exit(1);
}

$archivoSQL = $argv[1];

// Si no tiene ruta completa, buscar en el directorio actual
if (!file_exists($archivoSQL)) {
    $archivoSQL = __DIR__ . '/' . $archivoSQL;
}

if (!file_exists($archivoSQL)) {
    echo "Error: Archivo no encontrado: {$argv[1]}\n";
    exit(1);
}

$sql = file_get_contents($archivoSQL);

// Configuracion de conexiones
$conexiones = [
    'LOCAL' => [
        'dsn' => 'mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4',
        'user' => 'root',
        'pass' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    ],
    'PRODUCCION' => [
        'dsn' => 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
        'user' => 'cycloid_userdb',
        'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
        ]
    ]
];

echo "\n";
echo "===========================================\n";
echo "MIGRACION SQL - LOCAL Y PRODUCCION\n";
echo "===========================================\n";
echo "Archivo: {$argv[1]}\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "===========================================\n\n";

$resultados = [];

foreach ($conexiones as $nombre => $config) {
    echo "--- {$nombre} ---\n";

    try {
        $pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);
        echo "  Conectado: OK\n";

        // Dividir el SQL en multiples sentencias si es necesario
        $sentencias = array_filter(
            array_map('trim', explode(';', $sql)),
            function($s) { return !empty($s) && $s !== ''; }
        );

        $ejecutadas = 0;
        foreach ($sentencias as $sentencia) {
            if (!empty(trim($sentencia))) {
                $pdo->exec($sentencia);
                $ejecutadas++;
            }
        }

        echo "  Sentencias ejecutadas: {$ejecutadas}\n";
        echo "  Estado: OK\n";
        $resultados[$nombre] = 'OK';

    } catch (PDOException $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
        $resultados[$nombre] = 'ERROR';
    }

    echo "\n";
}

echo "===========================================\n";
echo "RESUMEN\n";
echo "===========================================\n";
foreach ($resultados as $entorno => $estado) {
    $icono = $estado === 'OK' ? '[OK]' : '[ERROR]';
    echo "  {$icono} {$entorno}: {$estado}\n";
}
echo "===========================================\n";

// Verificar tablas creadas
echo "\nVerificando tablas creadas...\n\n";

foreach ($conexiones as $nombre => $config) {
    if ($resultados[$nombre] !== 'OK') continue;

    echo "--- {$nombre} ---\n";
    try {
        $pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);

        // Verificar tablas de indicadores
        $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_indicadores_sst%'");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($tablas)) {
            echo "  Tablas tbl_indicadores_sst*:\n";
            foreach ($tablas as $tabla) {
                $countStmt = $pdo->query("SELECT COUNT(*) FROM {$tabla}");
                $count = $countStmt->fetchColumn();
                echo "    - {$tabla} ({$count} registros)\n";
            }
        }

        // Verificar tabla de responsables
        $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_cliente_responsables_sst'");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($tablas)) {
            echo "  Tablas tbl_cliente_responsables_sst:\n";
            foreach ($tablas as $tabla) {
                $countStmt = $pdo->query("SELECT COUNT(*) FROM {$tabla}");
                $count = $countStmt->fetchColumn();
                echo "    - {$tabla} ({$count} registros)\n";
            }
        }

    } catch (PDOException $e) {
        echo "  Error verificando: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "Proceso completado.\n";
