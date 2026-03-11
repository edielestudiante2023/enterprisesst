<?php
/**
 * Script para agregar tipo de documento: Certificacion de No Alto Riesgo
 * Estandar: 1.1.5 (documento complementario)
 *
 * Solo inserta en tbl_doc_plantillas y tbl_doc_plantilla_carpeta
 * (no usa IA, no necesita tbl_doc_tipo_configuracion ni secciones_config)
 *
 * Ejecutar: php app/SQL/agregar_certificacion_no_alto_riesgo.php
 */

$conexiones = [
    'local' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl' => false
    ],
    'produccion' => [
        'host' => getenv('DB_PROD_HOST') ?: 'TU_HOST_PRODUCCION',
        'port' => getenv('DB_PROD_PORT') ?: 25060,
        'database' => getenv('DB_PROD_DATABASE') ?: 'empresas_sst',
        'username' => getenv('DB_PROD_USERNAME') ?: 'TU_USUARIO',
        'password' => getenv('DB_PROD_PASSWORD') ?: 'TU_PASSWORD',
        'ssl' => true
    ]
];

function ejecutar($nombre, $config) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "EJECUTANDO EN: $nombre\n";
    echo str_repeat("=", 60) . "\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "Conectado a {$config['host']}\n\n";

        // 1. Insertar plantilla
        echo "1. Insertando plantilla... ";
        $pdo->exec("
            INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
            VALUES (1, 'Certificacion de No Alto Riesgo', 'CRT-AR', 'certificacion_no_alto_riesgo', '001', 1)
            ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)
        ");
        echo "OK\n";

        // 2. Mapear a carpeta 1.1.5
        echo "2. Mapeando a carpeta 1.1.5... ";
        $pdo->exec("
            INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
            VALUES ('CRT-AR', '1.1.5')
            ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta)
        ");
        echo "OK\n";

        // Verificar
        echo "\nVerificacion:\n";
        $row = $pdo->query("SELECT * FROM tbl_doc_plantillas WHERE tipo_documento = 'certificacion_no_alto_riesgo'")->fetch(PDO::FETCH_ASSOC);
        echo "  - Plantilla: " . ($row ? $row['nombre'] . " (codigo: " . $row['codigo_sugerido'] . ")" : "NO ENCONTRADA") . "\n";
        $row2 = $pdo->query("SELECT * FROM tbl_doc_plantilla_carpeta WHERE codigo_plantilla = 'CRT-AR'")->fetch(PDO::FETCH_ASSOC);
        echo "  - Carpeta: " . ($row2 ? $row2['codigo_carpeta'] : "NO MAPEADA") . "\n";

        echo "\n✅ Certificacion 'certificacion_no_alto_riesgo' creada en $nombre\n";
        return true;

    } catch (PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "\n";
echo "========================================================\n";
echo "  CREAR: Certificacion de No Alto Riesgo\n";
echo "  Estandar: 1.1.5 | Codigo: CRT-AR\n";
echo "========================================================\n";

// Ejecutar en LOCAL
$resultLocal = ejecutar('LOCAL', $conexiones['local']);

// Ejecutar en PRODUCCION
$resultProd = ejecutar('PRODUCCION', $conexiones['produccion']);

echo "\n========================================================\n";
echo "RESUMEN\n";
echo "========================================================\n";
echo "LOCAL:      " . ($resultLocal ? "✅ OK" : "❌ FALLO") . "\n";
echo "PRODUCCION: " . ($resultProd ? "✅ OK" : "❌ FALLO") . "\n";
echo "========================================================\n";
