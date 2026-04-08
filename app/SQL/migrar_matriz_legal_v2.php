<?php
/**
 * Migración Matriz Legal v2
 * 1) Duplica matriz_legal → matriz_legal_old (backup)
 * 2) Elimina matriz_legal
 * 3) Crea nueva matriz_legal con categoria + clasificacion (sin sector)
 *
 * Ejecutar: php app/SQL/migrar_matriz_legal_v2.php
 */

// Configuración LOCAL
$localConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'empresas_sst',
    'port' => 3306
];

// Configuración PRODUCCIÓN
$prodConfig = [
    'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_MR2SLvzRh3i_7o9fEHN',
    'database' => 'empresas_sst',
    'port' => 25060,
    'ssl' => true
];

// SQL: Paso 1 - Backup
$sqlBackup = "CREATE TABLE IF NOT EXISTS matriz_legal_old AS SELECT * FROM matriz_legal;";

// SQL: Paso 2 - Eliminar tabla vieja
$sqlDrop = "DROP TABLE IF EXISTS matriz_legal;";

// SQL: Paso 3 - Crear nueva tabla
$sqlCreate = "
CREATE TABLE matriz_legal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria VARCHAR(100) NOT NULL COMMENT 'Categoría principal: Medicina Laboral, Seguridad e Higiene Industrial, etc.',
    clasificacion VARCHAR(255) NULL COMMENT 'Sub-agrupación dentro de la categoría (segmentador)',
    tema VARCHAR(255) NOT NULL COMMENT 'Tema principal de la norma',
    subtema VARCHAR(255) NULL COMMENT 'Subtema específico',
    tipo_norma VARCHAR(100) NOT NULL COMMENT 'Tipo: Resolución, Decreto, Ley, Circular, etc.',
    id_norma_legal VARCHAR(50) NOT NULL COMMENT 'Número identificador de la norma',
    anio INT NOT NULL COMMENT 'Año de expedición',
    fecha_expedicion DATE NULL COMMENT 'Fecha exacta de expedición',
    descripcion_norma TEXT NULL COMMENT 'Descripción general / temática de la norma',
    autoridad_emisora VARCHAR(255) NULL COMMENT 'Entidad que emite la norma',
    referente_nacional VARCHAR(10) DEFAULT '' COMMENT 'Marca X si es referente nacional',
    referente_internacional VARCHAR(10) DEFAULT '' COMMENT 'Marca X si es referente internacional',
    articulos_aplicables TEXT NULL COMMENT 'Artículos específicos que aplican',
    parametros LONGTEXT NULL COMMENT 'Parámetros y detalles extensos',
    notas_vigencia TEXT NULL COMMENT 'Notas sobre vigencia y observaciones',
    estado ENUM('activa', 'derogada', 'modificada') DEFAULT 'activa' COMMENT 'Estado de la norma',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_categoria (categoria),
    INDEX idx_clasificacion (clasificacion),
    INDEX idx_tema (tema),
    INDEX idx_tipo_norma (tipo_norma),
    INDEX idx_anio (anio),
    INDEX idx_estado (estado),
    INDEX idx_id_norma (id_norma_legal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Matriz Legal SST v2 - Con categorías y clasificaciones';
";

function conectar($config, $nombre) {
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

function ejecutarMigracion($config, $nombre) {
    global $sqlBackup, $sqlDrop, $sqlCreate;

    echo "\n========== $nombre ==========\n";

    try {
        $pdo = conectar($config, $nombre);
        echo "[OK] Conexion exitosa a $nombre\n";

        // Verificar si matriz_legal existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'matriz_legal'");
        $existe = $stmt->fetch();

        if ($existe) {
            // Verificar si ya fue migrada (tiene columna 'categoria')
            $stmt = $pdo->query("SHOW COLUMNS FROM matriz_legal LIKE 'categoria'");
            $yaMigrada = $stmt->fetch();
            if ($yaMigrada) {
                echo "[INFO] matriz_legal YA tiene estructura v2, saltando backup...\n";
                // Solo recrear para asegurar estructura limpia
                $pdo->exec($sqlDrop);
                echo "[OK] Tabla v2 vieja eliminada\n";
                $pdo->exec($sqlCreate);
                echo "[OK] Nueva tabla matriz_legal creada (v2)\n";
                $stmt = $pdo->query("DESCRIBE matriz_legal");
                $columns = $stmt->fetchAll();
                echo "[INFO] Estructura:\n";
                foreach ($columns as $col) {
                    echo "  - {$col['Field']} ({$col['Type']})\n";
                }
                return true;
            }

            // Contar registros actuales
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM matriz_legal");
            $count = $stmt->fetch();
            echo "[INFO] matriz_legal tiene {$count['total']} registros\n";

            // Paso 1: Backup (con PK para DigitalOcean)
            $pdo->exec("DROP TABLE IF EXISTS matriz_legal_old");
            $pdo->exec("CREATE TABLE matriz_legal_old (
                backup_id INT AUTO_INCREMENT PRIMARY KEY,
                id INT NULL,
                sector VARCHAR(100) NULL,
                tema VARCHAR(255) NULL,
                subtema VARCHAR(255) NULL,
                tipo_norma VARCHAR(100) NULL,
                id_norma_legal VARCHAR(50) NULL,
                anio INT NULL,
                descripcion_norma TEXT NULL,
                autoridad_emisora VARCHAR(255) NULL,
                referente_nacional VARCHAR(10) NULL,
                referente_internacional VARCHAR(10) NULL,
                articulos_aplicables TEXT NULL,
                parametros LONGTEXT NULL,
                notas_vigencia TEXT NULL,
                estado VARCHAR(20) NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $pdo->exec("INSERT INTO matriz_legal_old (id, sector, tema, subtema, tipo_norma, id_norma_legal, anio, descripcion_norma, autoridad_emisora, referente_nacional, referente_internacional, articulos_aplicables, parametros, notas_vigencia, estado, created_at, updated_at) SELECT * FROM matriz_legal");
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM matriz_legal_old");
            $countOld = $stmt->fetch();
            echo "[OK] Backup: matriz_legal_old creada ({$countOld['total']} registros)\n";
        } else {
            echo "[INFO] matriz_legal no existe, se creara nueva\n";
        }

        // Paso 2: Eliminar vieja
        $pdo->exec($sqlDrop);
        echo "[OK] Tabla matriz_legal eliminada\n";

        // Paso 3: Crear nueva
        $pdo->exec($sqlCreate);
        echo "[OK] Nueva tabla matriz_legal creada (v2: categoria + clasificacion)\n";

        // Verificar estructura
        $stmt = $pdo->query("DESCRIBE matriz_legal");
        $columns = $stmt->fetchAll();
        echo "[INFO] Estructura nueva:\n";
        foreach ($columns as $col) {
            echo "  - {$col['Field']} ({$col['Type']})\n";
        }

        return true;

    } catch (PDOException $e) {
        echo "[ERROR] $nombre: " . $e->getMessage() . "\n";
        return false;
    }
}

// ====== EJECUCION ======
echo "=== MIGRACION MATRIZ LEGAL v2 ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";

// LOCAL primero
$localOk = ejecutarMigracion($localConfig, 'LOCAL');

if (!$localOk) {
    echo "\n[ABORT] LOCAL fallo. NO se ejecuta en PRODUCCION.\n";
    exit(1);
}

// PRODUCCION solo si LOCAL OK
echo "\n[INFO] LOCAL OK -> Ejecutando en PRODUCCION...\n";
$prodOk = ejecutarMigracion($prodConfig, 'PRODUCCION');

// Resumen
echo "\n========== RESUMEN ==========\n";
echo "LOCAL:      " . ($localOk ? "OK" : "ERROR") . "\n";
echo "PRODUCCION: " . ($prodOk ? "OK" : "ERROR") . "\n";

if ($localOk && $prodOk) {
    echo "\n[LISTO] Migracion completada en ambos entornos.\n";
    echo "Siguiente paso: ejecutar script de importacion del Excel.\n";
}
