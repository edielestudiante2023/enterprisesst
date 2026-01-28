<?php
/**
 * Script de migracion: Insertar plantilla Asignacion de Responsable del SG-SST
 * Ejecutar con: php app/SQL/ejecutar_plantilla_asignacion_responsable.php
 */

// Configuracion de entornos
$environments = [
    'LOCAL' => [
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'empresas_sst',
        'user' => 'root',
        'pass' => '',
        'ssl' => false
    ],
    'PRODUCTION' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'dbname' => 'empresas_sst',
        'user' => 'cycloid_userdb',
        'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ]
];

// SQL a ejecutar (separados para mejor manejo)
$sqlStatements = [
    // 1. Verificar/crear el tipo de documento "Acta" si no existe
    "INSERT INTO tbl_doc_tipos (nombre, codigo, descripcion, activo, created_at)
     SELECT 'Acta', 'ACT', 'Actas y asignaciones formales del SG-SST', 1, NOW()
     FROM DUAL
     WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_tipos WHERE codigo = 'ACT')",

    // 2. Insertar la plantilla de Asignacion de Responsable
    "INSERT INTO tbl_doc_plantillas (
        id_tipo, nombre, descripcion, codigo_sugerido,
        aplica_7, aplica_21, aplica_60,
        activo, orden, created_at
    )
    SELECT
        (SELECT id_tipo FROM tbl_doc_tipos WHERE codigo = 'ACT' LIMIT 1),
        'Asignacion de Responsable del SG-SST',
        'Acta de asignacion del responsable del diseno e implementacion del Sistema de Gestion de Seguridad y Salud en el Trabajo (Estandar 1.1.1)',
        'ASG-RES',
        1, 1, 1,
        1, 1, NOW()
    FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE codigo_sugerido = 'ASG-RES')",

    // 3. Mapear la plantilla a la carpeta del estandar 1.1.1
    "INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
     SELECT 'ASG-RES', '1.1.1'
     FROM DUAL
     WHERE NOT EXISTS (
         SELECT 1 FROM tbl_doc_plantilla_carpeta
         WHERE codigo_plantilla = 'ASG-RES' AND codigo_carpeta = '1.1.1'
     )"
];

echo "\n========================================\n";
echo "MIGRACION: Plantilla Asignacion Responsable SG-SST\n";
echo "========================================\n";

foreach ($environments as $envName => $config) {
    echo "\n---------- {$envName} ----------\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        echo "Conectado a {$envName}\n";

        foreach ($sqlStatements as $index => $sql) {
            try {
                $result = $pdo->exec($sql);
                $affected = $result !== false ? $result : 0;
                echo "OK [{$index}]: " . ($affected > 0 ? "Insertado" : "Ya existe o sin cambios") . "\n";
            } catch (PDOException $e) {
                // Ignorar errores de duplicado (ya existe)
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    echo "SKIP [{$index}]: Ya existe\n";
                } else {
                    echo "ERROR [{$index}]: " . $e->getMessage() . "\n";
                }
            }
        }

        // Verificar resultado
        echo "\nVerificando insercion...\n";

        // Verificar plantilla
        $stmt = $pdo->query("SELECT id_plantilla, nombre, codigo_sugerido FROM tbl_doc_plantillas WHERE codigo_sugerido = 'ASG-RES'");
        $plantilla = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($plantilla) {
            echo "  Plantilla: ID={$plantilla['id_plantilla']}, Codigo={$plantilla['codigo_sugerido']}\n";
        } else {
            echo "  Plantilla: NO ENCONTRADA\n";
        }

        // Verificar mapeo
        $stmt = $pdo->query("SELECT * FROM tbl_doc_plantilla_carpeta WHERE codigo_plantilla = 'ASG-RES'");
        $mapeo = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($mapeo) {
            echo "  Mapeo: {$mapeo['codigo_plantilla']} -> Carpeta {$mapeo['codigo_carpeta']}\n";
        } else {
            echo "  Mapeo: NO ENCONTRADO\n";
        }

    } catch (PDOException $e) {
        echo "ERROR conexion {$envName}: " . $e->getMessage() . "\n";
    }
}

echo "\n========================================\n";
echo "MIGRACION COMPLETADA\n";
echo "========================================\n";
