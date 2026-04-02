<?php
/**
 * Crea la tabla tbl_normas_derogadas y migra las normas hardcodeadas como seed inicial.
 *
 * Uso: php app/SQL/crear_tbl_normas_derogadas.php
 */

echo "=== CREAR TABLA tbl_normas_derogadas + SEED ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

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
        'password' => 'AVNS_MR2SLvzRh3i_7o9fEHN',
        'ssl' => true
    ]
];

$createTable = "CREATE TABLE IF NOT EXISTS tbl_normas_derogadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    norma_derogada VARCHAR(300) NOT NULL COMMENT 'Ej: Resolución 652 de 2012',
    norma_reemplazo VARCHAR(300) NOT NULL COMMENT 'Ej: Resolución 3461 de 2025',
    texto_original TEXT NOT NULL COMMENT 'Texto libre que escribió el consultor',
    reportado_por VARCHAR(200) NOT NULL DEFAULT 'Sistema',
    fecha_reporte DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Seed: normas que estaban hardcodeadas en NormasVigentes.php
$seeds = [
    ['Decreto 1443 de 2014', 'Decreto 1072 de 2015 (compilado)', 'Migración inicial: fue compilado en el Decreto 1072 de 2015'],
    ['Resolución 1111 de 2017', 'Resolución 0312 de 2019', 'Migración inicial: derogada por Resolución 0312 de 2019'],
    ['Resolución 652 de 2012', 'Resolución 3461 de 2025', 'Migración inicial: derogada por Resolución 3461 de 2025'],
    ['Resolución 1356 de 2012', 'Resolución 3461 de 2025', 'Migración inicial: derogada por Resolución 3461 de 2025'],
    ['Resolución 2404 de 2019', 'Resolución 2764 de 2022', 'Migración inicial: derogada por Resolución 2764 de 2022'],
];

foreach ($conexiones as $entorno => $config) {
    echo "=== " . strtoupper($entorno) . " ===\n";
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "  Conectado\n";

        // Crear tabla
        $pdo->exec($createTable);
        echo "  Tabla tbl_normas_derogadas: OK\n";

        // Seed
        $insertSql = "INSERT INTO tbl_normas_derogadas (norma_derogada, norma_reemplazo, texto_original, reportado_por)
                      SELECT :norma, :reemplazo, :texto, 'Sistema (migración inicial)'
                      FROM DUAL
                      WHERE NOT EXISTS (
                          SELECT 1 FROM tbl_normas_derogadas WHERE norma_derogada = :norma2 AND activo = 1
                      )";

        $insertados = 0;
        $omitidos = 0;
        foreach ($seeds as $seed) {
            $stmt = $pdo->prepare($insertSql);
            $stmt->execute([
                'norma' => $seed[0],
                'reemplazo' => $seed[1],
                'texto' => $seed[2],
                'norma2' => $seed[0]
            ]);
            if ($stmt->rowCount() > 0) {
                echo "  + {$seed[0]}\n";
                $insertados++;
            } else {
                $omitidos++;
            }
        }
        echo "  Seed: {$insertados} insertados, {$omitidos} ya existían\n";

    } catch (Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "DONE\n";
