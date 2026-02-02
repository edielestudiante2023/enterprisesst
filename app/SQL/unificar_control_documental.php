<?php
/**
 * Script para unificar el control documental
 *
 * ACCIONES:
 * 1. Agregar columnas version y id_estandar a tbl_doc_plantillas
 * 2. Agregar mapeo faltante de Presupuesto (1.1.3) en tbl_doc_plantilla_carpeta
 * 3. Insertar plantilla FT-SST-004 en tbl_doc_plantillas
 *
 * Uso: php app/SQL/unificar_control_documental.php
 */

// Configuracion LOCAL
$localConfig = [
    'host' => 'localhost',
    'port' => 3306,
    'user' => 'root',
    'pass' => '',
    'db'   => 'empresas_sst'
];

// Configuracion PRODUCTION (DigitalOcean) - YA EJECUTADO 2026-01-30
$prodConfig = [
    'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port' => 25060,
    'user' => 'cycloid_userdb',
    'pass' => '*** REMOVIDO ***', // Credenciales removidas despues de ejecucion
    'db'   => 'empresas_sst',
    'ssl'  => true
];

function ejecutarMigracion($config, $nombre) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "=== Ejecutando en {$nombre} ===\n";
    echo str_repeat("=", 60) . "\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['db']};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        if (!empty($config['ssl'])) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        echo "Conectado a {$nombre}\n\n";

        // ============================================================
        // PASO 1: Agregar columna 'version' a tbl_doc_plantillas
        // ============================================================
        echo "--- PASO 1: Agregar columna 'version' ---\n";

        $columnExists = $pdo->query("
            SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '{$config['db']}'
            AND TABLE_NAME = 'tbl_doc_plantillas'
            AND COLUMN_NAME = 'version'
        ")->fetch()['cnt'];

        if ($columnExists == 0) {
            $pdo->exec("ALTER TABLE tbl_doc_plantillas ADD COLUMN version VARCHAR(10) DEFAULT '001' AFTER codigo_sugerido");
            echo "OK: Columna 'version' agregada\n";
        } else {
            echo "SKIP: Columna 'version' ya existe\n";
        }

        // ============================================================
        // PASO 2: Agregar columna 'id_estandar' a tbl_doc_plantillas
        // ============================================================
        echo "\n--- PASO 2: Agregar columna 'id_estandar' ---\n";

        $columnExists = $pdo->query("
            SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '{$config['db']}'
            AND TABLE_NAME = 'tbl_doc_plantillas'
            AND COLUMN_NAME = 'id_estandar'
        ")->fetch()['cnt'];

        if ($columnExists == 0) {
            $pdo->exec("ALTER TABLE tbl_doc_plantillas ADD COLUMN id_estandar INT NULL AFTER id_tipo");
            echo "OK: Columna 'id_estandar' agregada\n";

            // Agregar indice
            try {
                $pdo->exec("CREATE INDEX idx_id_estandar ON tbl_doc_plantillas(id_estandar)");
                echo "OK: Indice idx_id_estandar creado\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    echo "SKIP: Indice ya existe\n";
                }
            }
        } else {
            echo "SKIP: Columna 'id_estandar' ya existe\n";
        }

        // ============================================================
        // PASO 3: Agregar mapeo Presupuesto (1.1.3) en tbl_doc_plantilla_carpeta
        // ============================================================
        echo "\n--- PASO 3: Agregar mapeo 1.1.3 (Presupuesto) ---\n";

        $mapeoExists = $pdo->query("
            SELECT COUNT(*) as cnt FROM tbl_doc_plantilla_carpeta
            WHERE codigo_carpeta = '1.1.3' AND codigo_plantilla = 'FT-SST-004'
        ")->fetch()['cnt'];

        if ($mapeoExists == 0) {
            // Verificar si la tabla tiene columna 'descripcion'
            $hasDescripcion = $pdo->query("
                SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = '{$config['db']}'
                AND TABLE_NAME = 'tbl_doc_plantilla_carpeta'
                AND COLUMN_NAME = 'descripcion'
            ")->fetch()['cnt'];

            if ($hasDescripcion > 0) {
                $pdo->exec("
                    INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta, descripcion, created_at)
                    VALUES ('FT-SST-004', '1.1.3', 'Presupuesto SST', NOW())
                ");
            } else {
                $pdo->exec("
                    INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
                    VALUES ('FT-SST-004', '1.1.3')
                ");
            }
            echo "OK: Mapeo FT-SST-004 -> 1.1.3 agregado\n";
        } else {
            echo "SKIP: Mapeo ya existe\n";
        }

        // ============================================================
        // PASO 4: Insertar plantilla FT-SST-004 en tbl_doc_plantillas
        // ============================================================
        echo "\n--- PASO 4: Insertar plantilla FT-SST-004 ---\n";

        $plantillaExists = $pdo->query("
            SELECT COUNT(*) as cnt FROM tbl_doc_plantillas
            WHERE codigo_sugerido = 'FT-SST-004'
        ")->fetch()['cnt'];

        if ($plantillaExists == 0) {
            // Obtener id_estandar correcto (buscar 1.1.3)
            $estandar = $pdo->query("
                SELECT id_estandar FROM tbl_estandares_minimos
                WHERE item = '1.1.3' LIMIT 1
            ")->fetch();

            $idEstandar = $estandar ? $estandar['id_estandar'] : 3;

            $pdo->exec("
                INSERT INTO tbl_doc_plantillas (
                    id_tipo, id_estandar, nombre, descripcion, codigo_sugerido, version,
                    activo, orden, aplica_7, aplica_21, aplica_60, created_at, updated_at
                ) VALUES (
                    9, -- FOR (Formato)
                    {$idEstandar},
                    'Asignacion de Recursos para el SG-SST',
                    'Presupuesto anual de recursos financieros, tecnicos y humanos para el SG-SST',
                    'FT-SST-004',
                    '001',
                    1, 100, 1, 1, 1, NOW(), NOW()
                )
            ");
            echo "OK: Plantilla FT-SST-004 insertada con id_estandar={$idEstandar}\n";
        } else {
            // Actualizar id_estandar si no tiene
            $pdo->exec("
                UPDATE tbl_doc_plantillas
                SET id_estandar = (SELECT id_estandar FROM tbl_estandares_minimos WHERE item = '1.1.3' LIMIT 1)
                WHERE codigo_sugerido = 'FT-SST-004' AND id_estandar IS NULL
            ");
            echo "SKIP: Plantilla ya existe (id_estandar actualizado si estaba NULL)\n";
        }

        // ============================================================
        // VERIFICACION FINAL
        // ============================================================
        echo "\n--- VERIFICACION FINAL ---\n";

        // Estructura de tbl_doc_plantillas
        echo "\nEstructura de tbl_doc_plantillas (columnas nuevas):\n";
        $columns = $pdo->query("
            SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '{$config['db']}'
            AND TABLE_NAME = 'tbl_doc_plantillas'
            AND COLUMN_NAME IN ('version', 'id_estandar', 'codigo_sugerido')
            ORDER BY ORDINAL_POSITION
        ")->fetchAll();
        foreach ($columns as $col) {
            echo "  - {$col['COLUMN_NAME']} ({$col['COLUMN_TYPE']}) DEFAULT: {$col['COLUMN_DEFAULT']}\n";
        }

        // Mapeos de 1.1.x
        echo "\nMapeos de estandar 1.1.x:\n";
        $mapeos = $pdo->query("
            SELECT codigo_carpeta, codigo_plantilla
            FROM tbl_doc_plantilla_carpeta
            WHERE codigo_carpeta LIKE '1.1.%'
            ORDER BY codigo_carpeta
        ")->fetchAll();
        foreach ($mapeos as $m) {
            echo "  - {$m['codigo_carpeta']} -> {$m['codigo_plantilla']}\n";
        }

        // Plantilla FT-SST-004
        echo "\nPlantilla FT-SST-004:\n";
        $plantilla = $pdo->query("
            SELECT id_plantilla, nombre, codigo_sugerido, version, id_estandar
            FROM tbl_doc_plantillas
            WHERE codigo_sugerido = 'FT-SST-004'
        ")->fetch();
        if ($plantilla) {
            echo "  - ID: {$plantilla['id_plantilla']}\n";
            echo "  - Nombre: {$plantilla['nombre']}\n";
            echo "  - Codigo: {$plantilla['codigo_sugerido']}\n";
            echo "  - Version: {$plantilla['version']}\n";
            echo "  - id_estandar: {$plantilla['id_estandar']}\n";
        }

        echo "\n" . str_repeat("=", 60) . "\n";
        echo "Migracion completada en {$nombre}\n";
        echo str_repeat("=", 60) . "\n";
        return true;

    } catch (PDOException $e) {
        echo "\nERROR en {$nombre}: {$e->getMessage()}\n";
        return false;
    }
}

// ============================================================
// EJECUCION
// ============================================================
echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║   UNIFICACION DEL CONTROL DOCUMENTAL - EnterpriseSST    ║\n";
echo "╠══════════════════════════════════════════════════════════╣\n";
echo "║ 1. Agregar version e id_estandar a tbl_doc_plantillas   ║\n";
echo "║ 2. Mapear FT-SST-004 -> 1.1.3 (Presupuesto)             ║\n";
echo "║ 3. Insertar plantilla FT-SST-004                        ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";

// LOCAL
ejecutarMigracion($localConfig, 'LOCAL');

// PRODUCTION - Solo si tiene password configurado
if (!empty($prodConfig['pass'])) {
    ejecutarMigracion($prodConfig, 'PRODUCTION');
} else {
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║ PRODUCCION: Password no configurado                     ║\n";
    echo "║ Agrega el password en \$prodConfig['pass'] y re-ejecuta  ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n";
}

echo "\n=== SCRIPT FINALIZADO ===\n\n";
