<?php
/**
 * Migración: Campos para Dashboard Jerárquico de Indicadores SST
 * Agrega es_minimo_obligatorio y peso_ponderacion a tbl_indicadores_sst
 *
 * Ejecutar: php app/SQL/agregar_campos_dashboard_indicadores.php
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
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ]
];

// Marcar indicadores mínimos existentes basándose en nombre
$sqlMarcarMinimos = <<<'SQL'
UPDATE tbl_indicadores_sst
SET es_minimo_obligatorio = 1
WHERE activo = 1
  AND es_minimo_obligatorio = 0
  AND (
    nombre_indicador LIKE '%Frecuencia%Accidente%'
    OR nombre_indicador LIKE '%Severidad%Accidente%'
    OR nombre_indicador LIKE '%proporci_n%accidentes mortales%'
    OR nombre_indicador LIKE '%Prevalencia%Enfermedad%'
    OR nombre_indicador LIKE '%Incidencia%Enfermedad%'
    OR nombre_indicador LIKE '%Ausentismo%'
    OR nombre_indicador LIKE '%Indice de Frecuencia%'
    OR nombre_indicador LIKE '%Indice de Severidad%'
    OR nombre_indicador LIKE '%ndice%Frecuencia%Accidente%'
    OR nombre_indicador LIKE '%ndice%Severidad%Accidente%'
    OR nombre_indicador LIKE '%ndice%mortalidad%accidente%'
  )
SQL;

function ejecutarMigracion($nombre, $config, $sqlMarcarMinimos) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "MIGRANDO: $nombre\n";
    echo str_repeat("=", 60) . "\n";

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
        echo "  Conectado a {$config['host']}:{$config['port']}\n\n";

        // Verificar columnas existentes
        $stmt = $pdo->query("DESCRIBE tbl_indicadores_sst");
        $columnas = array_column($stmt->fetchAll(), 'Field');
        $yaExisteMinimo = in_array('es_minimo_obligatorio', $columnas);
        $yaExistePeso = in_array('peso_ponderacion', $columnas);

        echo "  Estado previo:\n";
        echo "    es_minimo_obligatorio: " . ($yaExisteMinimo ? "YA EXISTE" : "NO EXISTE") . "\n";
        echo "    peso_ponderacion:      " . ($yaExistePeso ? "YA EXISTE" : "NO EXISTE") . "\n\n";

        // 1) Agregar es_minimo_obligatorio si falta
        if (!$yaExisteMinimo) {
            echo "  Ejecutando: ADD COLUMN es_minimo_obligatorio... ";
            $pdo->exec("ALTER TABLE tbl_indicadores_sst ADD COLUMN `es_minimo_obligatorio` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Indicador minimo obligatorio Res. 0312/2019 Art.30' AFTER `numeral_resolucion`");
            echo "OK\n";
        }

        // 2) Agregar peso_ponderacion si falta
        if (!$yaExistePeso) {
            echo "  Ejecutando: ADD COLUMN peso_ponderacion... ";
            $pdo->exec("ALTER TABLE tbl_indicadores_sst ADD COLUMN `peso_ponderacion` DECIMAL(5,2) DEFAULT NULL COMMENT 'Peso ponderado para consolidacion dashboard' AFTER `es_minimo_obligatorio`");
            echo "OK\n";
        }

        // 3) Crear índice (verificar si existe)
        $stmtIdx = $pdo->query("SHOW INDEX FROM tbl_indicadores_sst WHERE Key_name = 'idx_minimo_cliente'");
        if ($stmtIdx->rowCount() === 0) {
            echo "  Ejecutando: CREATE INDEX idx_minimo_cliente... ";
            $pdo->exec("CREATE INDEX idx_minimo_cliente ON tbl_indicadores_sst(es_minimo_obligatorio, id_cliente)");
            echo "OK\n";
        } else {
            echo "  Indice idx_minimo_cliente: ya existe\n";
        }

        // 4) Marcar indicadores mínimos
        echo "  Ejecutando: Marcar indicadores minimos existentes... ";
        $affected = $pdo->exec($sqlMarcarMinimos);
        echo "OK ($affected filas afectadas)\n";

        // Verificación posterior
        echo "\n  Verificacion posterior:\n";
        $stmt = $pdo->query("DESCRIBE tbl_indicadores_sst");
        $columnas = array_column($stmt->fetchAll(), 'Field');
        echo "    es_minimo_obligatorio: " . (in_array('es_minimo_obligatorio', $columnas) ? "OK" : "FALTA") . "\n";
        echo "    peso_ponderacion:      " . (in_array('peso_ponderacion', $columnas) ? "OK" : "FALTA") . "\n";

        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_indicadores_sst WHERE es_minimo_obligatorio = 1");
        echo "    Indicadores minimos marcados: " . $stmt->fetch()['total'] . "\n";

        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_indicadores_sst WHERE activo = 1");
        echo "    Total indicadores activos: " . $stmt->fetch()['total'] . "\n";

        echo "\n  Migracion de $nombre COMPLETADA\n";
        return true;

    } catch (PDOException $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "\n";
echo str_repeat("*", 60) . "\n";
echo "  MIGRACION: Dashboard Jerarquico Indicadores SST\n";
echo "  Campos: es_minimo_obligatorio, peso_ponderacion\n";
echo str_repeat("*", 60) . "\n";

// 1) LOCAL primero
$resultadoLocal = ejecutarMigracion('LOCAL', $conexiones['local'], $sqlMarcarMinimos);

if (!$resultadoLocal) {
    echo "\n  LOCAL FALLO - NO se ejecutara en PRODUCCION\n";
    exit(1);
}

// 2) PRODUCCIÓN solo si LOCAL OK
$resultadoProduccion = ejecutarMigracion('PRODUCCION', $conexiones['produccion'], $sqlMarcarMinimos);

// Resumen
echo "\n" . str_repeat("=", 60) . "\n";
echo "RESUMEN FINAL\n";
echo str_repeat("=", 60) . "\n";
echo "LOCAL:      " . ($resultadoLocal ? "OK" : "FALLO") . "\n";
echo "PRODUCCION: " . ($resultadoProduccion ? "OK" : "FALLO") . "\n";
echo str_repeat("=", 60) . "\n";
