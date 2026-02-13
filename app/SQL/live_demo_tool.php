<?php
/**
 * Herramienta CLI para Live Demo - Investigar y Limpiar datos
 *
 * Modos de uso:
 *   php live_demo_tool.php query "SELECT * FROM tbl_clientes WHERE id = 99"
 *   php live_demo_tool.php cleanup archivo_cleanup.sql
 *   php live_demo_tool.php tables "tbl_clientes"  (describe tabla)
 *   php live_demo_tool.php fk "tbl_clientes"      (foreign keys que apuntan a esta tabla)
 */

if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ejecutarse desde la linea de comandos.\n");
}

if ($argc < 3) {
    echo "=== LIVE DEMO TOOL ===\n";
    echo "Modos:\n";
    echo "  php live_demo_tool.php query \"SQL_QUERY\"        - Ejecutar query (SELECT/INSERT/UPDATE/DELETE)\n";
    echo "  php live_demo_tool.php cleanup archivo.sql       - Ejecutar archivo SQL de limpieza\n";
    echo "  php live_demo_tool.php tables \"patron\"           - SHOW TABLES LIKE patron\n";
    echo "  php live_demo_tool.php describe \"tabla\"          - DESCRIBE tabla\n";
    echo "  php live_demo_tool.php fk \"tabla\"               - Foreign keys que referencian esta tabla\n";
    exit(1);
}

$modo = $argv[1];
$param = $argv[2];

// Solo produccion
$config = [
    'dsn'  => 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
    'user' => 'cycloid_userdb',
    'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ]
];

try {
    $pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);
} catch (PDOException $e) {
    echo "ERROR de conexion: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n[PRODUCCION] " . date('Y-m-d H:i:s') . "\n";
echo str_repeat('-', 60) . "\n";

switch ($modo) {
    case 'query':
        ejecutarQuery($pdo, $param);
        break;
    case 'cleanup':
        ejecutarCleanup($pdo, $param);
        break;
    case 'tables':
        mostrarTablas($pdo, $param);
        break;
    case 'describe':
        describirTabla($pdo, $param);
        break;
    case 'fk':
        mostrarFK($pdo, $param);
        break;
    default:
        echo "Modo no reconocido: {$modo}\n";
        exit(1);
}

// ============================================================
// FUNCIONES
// ============================================================

function ejecutarQuery($pdo, $sql) {
    echo "SQL: {$sql}\n\n";

    $esSelect = stripos(trim($sql), 'SELECT') === 0
             || stripos(trim($sql), 'SHOW') === 0
             || stripos(trim($sql), 'DESCRIBE') === 0;

    try {
        if ($esSelect) {
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                echo "(Sin resultados)\n";
                return;
            }

            // Encabezados
            $cols = array_keys($rows[0]);

            // Calcular ancho de columnas
            $anchos = [];
            foreach ($cols as $col) {
                $anchos[$col] = strlen($col);
            }
            foreach ($rows as $row) {
                foreach ($cols as $col) {
                    $len = strlen((string)($row[$col] ?? 'NULL'));
                    if ($len > $anchos[$col]) {
                        $anchos[$col] = min($len, 50); // max 50 chars por columna
                    }
                }
            }

            // Imprimir tabla
            $header = '| ';
            $separator = '+-';
            foreach ($cols as $col) {
                $header .= str_pad($col, $anchos[$col]) . ' | ';
                $separator .= str_repeat('-', $anchos[$col]) . '-+-';
            }

            echo $separator . "\n";
            echo $header . "\n";
            echo $separator . "\n";

            foreach ($rows as $row) {
                $line = '| ';
                foreach ($cols as $col) {
                    $val = $row[$col] ?? 'NULL';
                    if (strlen($val) > 50) $val = substr($val, 0, 47) . '...';
                    $line .= str_pad($val, $anchos[$col]) . ' | ';
                }
                echo $line . "\n";
            }

            echo $separator . "\n";
            echo "(" . count($rows) . " filas)\n";

        } else {
            // INSERT, UPDATE, DELETE
            $affected = $pdo->exec($sql);
            echo "Filas afectadas: {$affected}\n";
        }
    } catch (PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

function ejecutarCleanup($pdo, $archivo) {
    if (!file_exists($archivo)) {
        $archivo = __DIR__ . '/' . $archivo;
    }
    if (!file_exists($archivo)) {
        echo "Archivo no encontrado: {$archivo}\n";
        exit(1);
    }

    $sql = file_get_contents($archivo);
    $sentencias = array_filter(
        array_map('trim', explode(';', $sql)),
        function($s) { return !empty($s) && $s !== ''; }
    );

    echo "Archivo: {$archivo}\n";
    echo "Sentencias a ejecutar: " . count($sentencias) . "\n\n";

    $ok = 0;
    $errores = 0;

    foreach ($sentencias as $i => $sentencia) {
        if (empty(trim($sentencia))) continue;

        // Saltar comentarios puros
        $limpia = trim(preg_replace('/^--.*$/m', '', $sentencia));
        if (empty($limpia)) continue;

        $num = $i + 1;
        $preview = substr(trim($sentencia), 0, 80);
        echo "[{$num}] {$preview}...\n";

        try {
            $affected = $pdo->exec($sentencia);
            echo "     OK - Filas afectadas: {$affected}\n";
            $ok++;
        } catch (PDOException $e) {
            echo "     ERROR: " . $e->getMessage() . "\n";
            $errores++;
        }
    }

    echo "\n=== RESUMEN ===\n";
    echo "Exitosas: {$ok}\n";
    echo "Errores:  {$errores}\n";
}

function mostrarTablas($pdo, $patron) {
    $stmt = $pdo->query("SHOW TABLES LIKE '{$patron}'");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tablas)) {
        echo "No se encontraron tablas con patron: {$patron}\n";
        return;
    }

    echo "Tablas que coinciden con '{$patron}':\n\n";
    foreach ($tablas as $tabla) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$tabla}`");
        $count = $countStmt->fetchColumn();
        echo "  {$tabla} ({$count} registros)\n";
    }
}

function describirTabla($pdo, $tabla) {
    echo "DESCRIBE {$tabla}:\n\n";
    $stmt = $pdo->query("DESCRIBE `{$tabla}`");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cols as $col) {
        $null = $col['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
        $key = $col['Key'] ? "[{$col['Key']}]" : '';
        $default = $col['Default'] !== null ? "DEFAULT '{$col['Default']}'" : '';
        echo "  {$col['Field']}  {$col['Type']}  {$null}  {$key}  {$default}\n";
    }
}

function mostrarFK($pdo, $tabla) {
    echo "Foreign keys que REFERENCIAN a '{$tabla}':\n\n";

    $sql = "SELECT
                TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_NAME = :tabla
            AND TABLE_SCHEMA = 'empresas_sst'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['tabla' => $tabla]);
    $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($fks)) {
        echo "  (No se encontraron foreign keys apuntando a esta tabla)\n";

        // Buscar tambien por convencion de nombres
        echo "\n  Buscando tablas con columna 'id_{$tabla}' o similar...\n";
        $buscar = str_replace('tbl_', '', $tabla);
        $stmt2 = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = 'empresas_sst'
            AND COLUMN_NAME LIKE '%id_{$buscar}%'");
        $refs = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($refs)) {
            foreach ($refs as $ref) {
                echo "    {$ref['TABLE_NAME']}.{$ref['COLUMN_NAME']}\n";
            }
        } else {
            echo "    (Ninguna encontrada)\n";
        }
        return;
    }

    foreach ($fks as $fk) {
        echo "  {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
        echo "    Constraint: {$fk['CONSTRAINT_NAME']}\n\n";
    }
}
