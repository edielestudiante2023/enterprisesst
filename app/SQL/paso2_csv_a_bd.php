<?php
/**
 * Paso 2: Importar CSV a tabla matriz_legal (LOCAL + PRODUCCION)
 * Ejecutar: php app/SQL/paso2_csv_a_bd.php
 */

$csvPath = __DIR__ . '/matriz_legal_import.csv';

$localConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'empresas_sst',
    'port' => 3306
];

$prodConfig = [
    'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_MR2SLvzRh3i_7o9fEHN',
    'database' => 'empresas_sst',
    'port' => 25060,
    'ssl' => true
];

function conectar($config) {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];
    if (!empty($config['ssl'])) {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
    return new PDO($dsn, $config['username'], $config['password'], $options);
}

function leerCSV($path) {
    $registros = [];
    $handle = fopen($path, 'r');
    if (!$handle) {
        echo "[ERROR] No se puede abrir CSV: $path\n";
        return [];
    }

    // Leer BOM UTF-8 si existe
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($handle);
    }

    // Header
    $header = fgetcsv($handle, 0, ';', '"');
    echo "[INFO] Columnas CSV: " . implode(', ', $header) . "\n";

    while (($row = fgetcsv($handle, 0, ';', '"')) !== false) {
        if (count($row) < 11) continue;
        $registros[] = array_combine($header, $row);
    }
    fclose($handle);
    return $registros;
}

function importar($config, $nombre, $registros) {
    echo "\n========== $nombre ==========\n";
    try {
        $pdo = conectar($config);
        echo "[OK] Conexion a $nombre\n";

        // Verificar tabla vacia
        $stmt = $pdo->query("SELECT COUNT(*) as n FROM matriz_legal");
        $count = $stmt->fetch()['n'];
        if ($count > 0) {
            echo "[WARN] La tabla ya tiene $count registros. Limpiando...\n";
            $pdo->exec("DELETE FROM matriz_legal");
            $pdo->exec("ALTER TABLE matriz_legal AUTO_INCREMENT = 1");
        }

        $sql = "INSERT INTO matriz_legal
            (categoria, clasificacion, tema, subtema, tipo_norma, id_norma_legal,
             anio, fecha_expedicion, descripcion_norma, autoridad_emisora, estado)
            VALUES
            (:categoria, :clasificacion, :tema, :subtema, :tipo_norma, :id_norma_legal,
             :anio, :fecha_expedicion, :descripcion_norma, :autoridad_emisora, :estado)";

        $stmt = $pdo->prepare($sql);
        $insertados = 0;
        $errores = 0;

        $pdo->beginTransaction();

        foreach ($registros as $i => $r) {
            try {
                $fecha = !empty($r['fecha_expedicion']) ? $r['fecha_expedicion'] : null;
                $anio = intval($r['anio']);
                if ($anio < 1900) $anio = 0;

                $stmt->execute([
                    ':categoria' => $r['categoria'],
                    ':clasificacion' => !empty($r['clasificacion']) ? $r['clasificacion'] : null,
                    ':tema' => $r['tema'],
                    ':subtema' => !empty($r['subtema']) ? $r['subtema'] : null,
                    ':tipo_norma' => $r['tipo_norma'],
                    ':id_norma_legal' => $r['id_norma_legal'],
                    ':anio' => $anio,
                    ':fecha_expedicion' => $fecha,
                    ':descripcion_norma' => !empty($r['descripcion_norma']) ? $r['descripcion_norma'] : null,
                    ':autoridad_emisora' => !empty($r['autoridad_emisora']) ? $r['autoridad_emisora'] : null,
                    ':estado' => $r['estado'],
                ]);
                $insertados++;
            } catch (PDOException $e) {
                $errores++;
                if ($errores <= 5) {
                    echo "[ERROR] Fila " . ($i+2) . ": " . $e->getMessage() . "\n";
                }
            }

            if ($insertados % 200 === 0) {
                echo "  Progreso: $insertados/" . count($registros) . "\n";
            }
        }

        $pdo->commit();
        echo "[OK] Insertados: $insertados | Errores: $errores\n";

        // Verificar totales
        $stmt = $pdo->query("SELECT COUNT(*) as n FROM matriz_legal");
        echo "[OK] Total en BD: " . $stmt->fetch()['n'] . " registros\n";

        // Stats por categoria
        $stmt = $pdo->query("SELECT categoria, COUNT(*) as n FROM matriz_legal GROUP BY categoria ORDER BY n DESC");
        echo "[INFO] Por categoria:\n";
        foreach ($stmt->fetchAll() as $row) {
            echo "  - {$row['categoria']}: {$row['n']}\n";
        }

        return $errores === 0;

    } catch (PDOException $e) {
        echo "[ERROR] $nombre: " . $e->getMessage() . "\n";
        return false;
    }
}

// ====== MAIN ======
echo "=== IMPORTACION CSV -> MATRIZ LEGAL ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "CSV: $csvPath\n";

$registros = leerCSV($csvPath);
echo "[INFO] Registros leidos del CSV: " . count($registros) . "\n";

if (empty($registros)) {
    echo "[ERROR] CSV vacio o no leido\n";
    exit(1);
}

// LOCAL
$localOk = importar($localConfig, 'LOCAL', $registros);

if (!$localOk) {
    echo "\n[WARN] LOCAL tuvo errores, pero continuando si hubo inserciones...\n";
}

// PRODUCCION
echo "\n[INFO] Ejecutando en PRODUCCION...\n";
$prodOk = importar($prodConfig, 'PRODUCCION', $registros);

// Resumen
echo "\n========== RESUMEN ==========\n";
echo "CSV: " . count($registros) . " registros\n";
echo "LOCAL:      " . ($localOk ? "OK" : "CON ERRORES") . "\n";
echo "PRODUCCION: " . ($prodOk ? "OK" : "CON ERRORES") . "\n";
echo "\n[LISTO] Importacion terminada.\n";
