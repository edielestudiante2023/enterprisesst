<?php
/**
 * Fix: Corregir nombre del estándar 2.4.1
 * Antes:  "Plan que identifica objetivos, metas, responsabilidad, recursos con cronograma y firmado"
 * Ahora:  "Plan de Trabajo Anual que identifica objetivos, metas, responsabilidad, recursos con cronograma y firmado"
 *
 * Ejecutar: php app/SQL/fix_nombre_241_plan_trabajo_anual.php
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

$textoViejo = 'Plan que identifica objetivos, metas, responsabilidad, recursos con cronograma y firmado';
$textoNuevo = 'Plan de Trabajo Anual que identifica objetivos, metas, responsabilidad, recursos con cronograma y firmado';

$queries = [
    // 1. Carpetas de documentación (nombre incluye "2.4.1. " al inicio)
    [
        'descripcion' => 'tbl_doc_carpetas - nombre carpeta',
        'sql' => "UPDATE tbl_doc_carpetas SET nombre = REPLACE(nombre, ?, ?) WHERE codigo = '2.4.1' AND nombre LIKE ?",
        'params' => [$textoViejo, $textoNuevo, '%Plan que identifica objetivos%']
    ],
    // 2. Estándares mínimos
    [
        'descripcion' => 'tbl_estandares_minimos - nombre estándar',
        'sql' => "UPDATE tbl_estandares_minimos SET nombre = ? WHERE item = '2.4.1'",
        'params' => [$textoNuevo]
    ],
    // 3. Evaluación inicial SST (campo nombre_estandar)
    [
        'descripcion' => 'tbl_evaluacion_inicial_sst - nombre_estandar',
        'sql' => "UPDATE tbl_evaluacion_inicial_sst SET nombre_estandar = REPLACE(nombre_estandar, ?, ?) WHERE item = '2.4.1' AND nombre_estandar LIKE ?",
        'params' => [$textoViejo, $textoNuevo, '%Plan que identifica objetivos%']
    ],
];

function conectar($config) {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $opciones = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($config['ssl']) {
        $opciones[PDO::MYSQL_ATTR_SSL_CA] = true;
        $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    return new PDO($dsn, $config['username'], $config['password'], $opciones);
}

echo "=== Fix nombre 2.4.1 - Plan de Trabajo Anual ===\n\n";

foreach ($conexiones as $entorno => $config) {
    echo str_repeat('=', 50) . "\n";
    echo strtoupper($entorno) . "\n";
    echo str_repeat('=', 50) . "\n";

    try {
        $pdo = conectar($config);
        echo "[OK] Conectado a {$entorno}\n\n";

        $totalAfectadas = 0;

        foreach ($queries as $q) {
            try {
                $stmt = $pdo->prepare($q['sql']);
                $stmt->execute($q['params']);
                $filas = $stmt->rowCount();
                $totalAfectadas += $filas;
                echo "  {$q['descripcion']}: {$filas} fila(s) actualizada(s)\n";
            } catch (PDOException $e) {
                // Si la tabla no existe, no es error fatal
                if (strpos($e->getMessage(), "doesn't exist") !== false) {
                    echo "  {$q['descripcion']}: TABLA NO EXISTE (skip)\n";
                } else {
                    echo "  {$q['descripcion']}: ERROR - {$e->getMessage()}\n";
                }
            }
        }

        echo "\n  Total filas afectadas en {$entorno}: {$totalAfectadas}\n";

        // Verificación: consultar el valor actual
        try {
            $verify = $pdo->query("SELECT nombre FROM tbl_estandares_minimos WHERE item = '2.4.1' LIMIT 1");
            $row = $verify->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                echo "  Verificación tbl_estandares_minimos: \"{$row['nombre']}\"\n";
            }
        } catch (PDOException $e) {
            // skip
        }

        echo "\n[OK] {$entorno} completado\n\n";

    } catch (PDOException $e) {
        echo "[ERROR] No se pudo conectar a {$entorno}: {$e->getMessage()}\n\n";
        if ($entorno === 'local') {
            echo "ABORTANDO: Local falló, no se ejecuta producción.\n";
            exit(1);
        }
    }
}

echo "=== PROCESO COMPLETADO ===\n";
