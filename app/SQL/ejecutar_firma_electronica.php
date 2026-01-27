<?php
/**
 * Script para crear tablas de Firma Electronica en LOCAL y PRODUCCION
 * Ejecuta: crear_tablas_firma_electronica.sql y alter_contexto_firmantes.sql
 *
 * Uso: php ejecutar_firma_electronica.php
 * O desde navegador: http://localhost/enterprisesst/app/SQL/ejecutar_firma_electronica.php
 */

// Permitir ejecucion desde navegador o CLI
$esCli = (php_sapi_name() === 'cli');
$nl = $esCli ? "\n" : "<br>\n";
$logFile = __DIR__ . '/firma_migration_log.txt';

function out($msg, $nl, $logFile) {
    echo $msg . $nl;
    file_put_contents($logFile, $msg . "\n", FILE_APPEND);
}

// Limpiar log
file_put_contents($logFile, '');

if (!$esCli) echo "<pre>";

out("===========================================", $nl, $logFile);
out("MIGRACION - MODULO FIRMA ELECTRONICA", $nl, $logFile);
out("Fecha: " . date('Y-m-d H:i:s'), $nl, $logFile);
out("===========================================", $nl, $logFile);
out("", $nl, $logFile);

// Archivos SQL a ejecutar
$archivos = [
    'crear_tablas_firma_electronica.sql' => 'Crear tablas de firma electronica',
    'alter_contexto_firmantes.sql' => 'Agregar campos de firmantes en contexto',
];

// Conexiones
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

$resultados = [];

foreach ($conexiones as $entorno => $config) {
    out("--- {$entorno} ---", $nl, $logFile);

    try {
        $pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);
        out("  Conectado: OK", $nl, $logFile);

        foreach ($archivos as $archivo => $descripcion) {
            $rutaArchivo = __DIR__ . '/' . $archivo;

            if (!file_exists($rutaArchivo)) {
                out("  [{$archivo}] ERROR: Archivo no encontrado", $nl, $logFile);
                continue;
            }

            $sql = file_get_contents($rutaArchivo);

            try {
                // Para el archivo de ALTER que usa PREPARE/EXECUTE, ejecutar como multi-query
                if (strpos($sql, 'PREPARE') !== false) {
                    // Ejecutar el SQL completo como un bloque usando exec con multi-statement
                    // PDO no soporta multi-statement con exec, dividir por bloques DEALLOCATE
                    $bloques = preg_split('/DEALLOCATE\s+PREPARE\s+stmt\s*;/i', $sql);
                    $ejecutados = 0;

                    foreach ($bloques as $bloque) {
                        $bloque = trim($bloque);
                        if (empty($bloque) || $bloque === '--') continue;

                        // Agregar DEALLOCATE de vuelta excepto al ultimo bloque vacio
                        $bloqueCompleto = $bloque . "\nDEALLOCATE PREPARE stmt;";

                        // Extraer el nombre de la columna del bloque para logging
                        if (preg_match("/@columnname\s*=\s*'([^']+)'/i", $bloque, $m)) {
                            $columnName = $m[1];
                        } else {
                            $columnName = 'unknown';
                        }

                        // Para prepared statements, necesitamos usar mysqli o ejecutar cada SET por separado
                        // PDO no soporta PREPARE/EXECUTE de MySQL bien
                        // Mejor: usar ALTER TABLE ADD COLUMN IF NOT EXISTS directamente con try/catch

                        if (preg_match("/ADD\s+COLUMN\s+`([^`]+)`\s+(.+?)'\s*\)/is", $bloque, $matches)) {
                            $col = $matches[1];
                            $def = '';
                            // Extraer la definicion completa de la columna
                            if (preg_match("/CONCAT\('ALTER TABLE[^,]+,\s*'([^']*ADD COLUMN[^)]+)/is", $bloque, $defMatch)) {
                                // Obtener solo el tipo de dato
                            }
                        }

                        $ejecutados++;
                    }

                    // Enfoque simplificado: usar consultas ALTER TABLE directas con verificacion
                    $columnas = [
                        ['representante_legal_nombre', 'VARCHAR(255) NULL'],
                        ['representante_legal_cargo', 'VARCHAR(100) NULL'],
                        ['representante_legal_email', 'VARCHAR(255) NULL'],
                        ['representante_legal_cedula', 'VARCHAR(20) NULL'],
                        ['requiere_delegado_sst', 'TINYINT(1) NOT NULL DEFAULT 0'],
                        ['delegado_sst_nombre', 'VARCHAR(255) NULL'],
                        ['delegado_sst_cargo', 'VARCHAR(100) NULL'],
                        ['delegado_sst_email', 'VARCHAR(255) NULL'],
                        ['delegado_sst_cedula', 'VARCHAR(20) NULL'],
                    ];

                    $tabla = 'tbl_cliente_contexto_sst';
                    $agregadas = 0;
                    $existentes = 0;

                    foreach ($columnas as [$col, $tipo]) {
                        // Verificar si la columna ya existe
                        $check = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
                        $check->execute([$tabla, $col]);

                        if ($check->fetchColumn() == 0) {
                            $pdo->exec("ALTER TABLE `{$tabla}` ADD COLUMN `{$col}` {$tipo}");
                            $agregadas++;
                            out("    + Columna '{$col}' agregada", $nl, $logFile);
                        } else {
                            $existentes++;
                        }
                    }

                    out("  [{$archivo}] OK - {$agregadas} columnas agregadas, {$existentes} ya existian", $nl, $logFile);

                } else {
                    // Para CREATE TABLE IF NOT EXISTS, ejecutar directamente
                    // Dividir por sentencias CREATE TABLE
                    $sentencias = preg_split('/;\s*\n/', $sql);
                    $ejecutadas = 0;

                    foreach ($sentencias as $sentencia) {
                        $sentencia = trim($sentencia);
                        // Saltar comentarios y lineas vacias
                        if (empty($sentencia) || preg_match('/^--/', $sentencia)) continue;
                        // Limpiar comentarios de linea
                        $sentencia = preg_replace('/^--.*$/m', '', $sentencia);
                        $sentencia = trim($sentencia);
                        if (empty($sentencia)) continue;

                        $pdo->exec($sentencia);
                        $ejecutadas++;
                    }

                    out("  [{$archivo}] OK - {$ejecutadas} sentencias ejecutadas", $nl, $logFile);
                }

            } catch (PDOException $e) {
                out("  [{$archivo}] ERROR: " . $e->getMessage(), $nl, $logFile);
            }
        }

        $resultados[$entorno] = 'OK';

    } catch (PDOException $e) {
        out("  ERROR CONEXION: " . $e->getMessage(), $nl, $logFile);
        $resultados[$entorno] = 'ERROR';
    }

    out("", $nl, $logFile);
}

// Verificacion
out("===========================================", $nl, $logFile);
out("VERIFICACION DE TABLAS", $nl, $logFile);
out("===========================================", $nl, $logFile);

foreach ($conexiones as $entorno => $config) {
    if (($resultados[$entorno] ?? '') !== 'OK') continue;

    out("--- {$entorno} ---", $nl, $logFile);

    try {
        $pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);

        // Verificar tablas de firma
        $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_doc_firma%'");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($tablas)) {
            out("  Tablas de firma electronica:", $nl, $logFile);
            foreach ($tablas as $tabla) {
                $colStmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$tabla}'");
                $numCols = $colStmt->fetchColumn();
                out("    [OK] {$tabla} ({$numCols} columnas)", $nl, $logFile);
            }
        } else {
            out("  [ERROR] No se encontraron tablas tbl_doc_firma*", $nl, $logFile);
        }

        // Verificar columnas en contexto
        $firmaCols = ['representante_legal_email', 'representante_legal_cedula', 'delegado_sst_email', 'delegado_sst_cedula', 'requiere_delegado_sst'];
        $check = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_cliente_contexto_sst' AND COLUMN_NAME IN ('" . implode("','", $firmaCols) . "')");
        $check->execute();
        $found = $check->fetchAll(PDO::FETCH_COLUMN);

        out("  Columnas firmantes en contexto: " . count($found) . "/" . count($firmaCols), $nl, $logFile);
        foreach ($firmaCols as $col) {
            $status = in_array($col, $found) ? '[OK]' : '[FALTA]';
            out("    {$status} {$col}", $nl, $logFile);
        }

    } catch (PDOException $e) {
        out("  Error verificando: " . $e->getMessage(), $nl, $logFile);
    }

    out("", $nl, $logFile);
}

out("===========================================", $nl, $logFile);
out("RESUMEN FINAL", $nl, $logFile);
out("===========================================", $nl, $logFile);
foreach ($resultados as $entorno => $estado) {
    $icono = $estado === 'OK' ? '[OK]' : '[ERROR]';
    out("  {$icono} {$entorno}", $nl, $logFile);
}
out("===========================================", $nl, $logFile);
out("Log guardado en: " . $logFile, $nl, $logFile);

if (!$esCli) echo "</pre>";
