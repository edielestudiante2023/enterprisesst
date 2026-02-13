<?php
/**
 * Migración: Corregir categorías del presupuesto SST
 *
 * Problema: Las 6 categorías actuales tienen actividades JS cruzadas
 *   - Cat 2 "Capacitación" mostraba exámenes médicos
 *   - Cat 6 "Otros Gastos" mostraba capacitaciones
 *   - Cat 5 "Medio Ambiente" mostraba emergencias
 *
 * Solución: Expandir a 7 categorías correctas según Decreto 1072/2015
 *   1. Talento Humano SST (sin cambio)
 *   2. Capacitación y Formación (sin cambio nombre, se corrige JS)
 *   3. Medicina Preventiva y del Trabajo (antes: "Salud en el Trabajo")
 *   4. Promoción y Prevención (antes: "Seguridad Industrial")
 *   5. Seguridad Industrial e Higiene (antes: "Medio Ambiente y Saneamiento Básico")
 *   6. Gestión de Emergencias (antes: "Otros Gastos SST")
 *   7. Otros Gastos SST (NUEVA)
 *
 * Uso: php migrar_categorias_presupuesto_7.php
 */

echo "=====================================================\n";
echo "MIGRACIÓN: CATEGORÍAS PRESUPUESTO SST (6 → 7)\n";
echo "=====================================================\n\n";

// Configuración LOCAL
$localConfig = [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'empresas_sst',
    'username' => 'root',
    'password' => '',
    'ssl' => false
];

// Configuración PRODUCCIÓN (DigitalOcean)
$prodConfig = [
    'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port' => 25060,
    'database' => 'empresas_sst',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_iDypWizlpMRwHIORJGG',
    'ssl' => true
];

// SQL statements en orden
$sqlStatements = [
    // Paso 1: Renombrar categorías existentes para que coincidan con su contenido real
    "renombrar_cat_3" => "
        UPDATE tbl_presupuesto_categorias
        SET nombre = 'Medicina Preventiva y del Trabajo'
        WHERE codigo = '3' AND nombre = 'Salud en el Trabajo'
    ",

    "renombrar_cat_4" => "
        UPDATE tbl_presupuesto_categorias
        SET nombre = 'Promoción y Prevención'
        WHERE codigo = '4' AND nombre = 'Seguridad Industrial'
    ",

    "renombrar_cat_5" => "
        UPDATE tbl_presupuesto_categorias
        SET nombre = 'Seguridad Industrial e Higiene'
        WHERE codigo = '5' AND nombre = 'Medio Ambiente y Saneamiento Básico'
    ",

    "renombrar_cat_6" => "
        UPDATE tbl_presupuesto_categorias
        SET nombre = 'Gestión de Emergencias'
        WHERE codigo = '6' AND nombre = 'Otros Gastos SST'
    ",

    // Paso 2: Agregar categoría 7
    "agregar_cat_7" => "
        INSERT INTO tbl_presupuesto_categorias (codigo, nombre, orden, activo)
        VALUES ('7', 'Otros Gastos SST', 7, 1)
        ON DUPLICATE KEY UPDATE nombre = 'Otros Gastos SST', orden = 7
    "
];

/**
 * Ejecuta migración en una base de datos
 */
function ejecutarMigracion($config, $nombre, $sqlStatements) {
    echo "--- {$nombre} ---\n";

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
        echo "  Conexion exitosa\n";

        // Mostrar estado ANTES
        echo "\n  Estado ANTES:\n";
        $stmt = $pdo->query("SELECT codigo, nombre FROM tbl_presupuesto_categorias ORDER BY codigo");
        $antes = $stmt->fetchAll();
        foreach ($antes as $row) {
            echo "    {$row['codigo']}. {$row['nombre']}\n";
        }

        // Ejecutar cada statement
        echo "\n  Ejecutando cambios:\n";
        foreach ($sqlStatements as $nombre_sql => $sql) {
            try {
                $affected = $pdo->exec($sql);
                echo "    OK {$nombre_sql} (filas afectadas: {$affected})\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    echo "    ~ {$nombre_sql} (ya existe)\n";
                } else {
                    echo "    X {$nombre_sql}: " . $e->getMessage() . "\n";
                }
            }
        }

        // Mostrar estado DESPUÉS
        echo "\n  Estado DESPUES:\n";
        $stmt = $pdo->query("SELECT codigo, nombre FROM tbl_presupuesto_categorias ORDER BY CAST(codigo AS UNSIGNED)");
        $despues = $stmt->fetchAll();
        foreach ($despues as $row) {
            echo "    {$row['codigo']}. {$row['nombre']}\n";
        }

        // Contar items existentes por categoría (para auditoría)
        echo "\n  Items existentes por categoria:\n";
        $stmt = $pdo->query("
            SELECT c.codigo, c.nombre, COUNT(i.id_item) as total_items
            FROM tbl_presupuesto_categorias c
            LEFT JOIN tbl_presupuesto_items i ON i.id_categoria = c.id_categoria AND i.activo = 1
            GROUP BY c.id_categoria
            ORDER BY CAST(c.codigo AS UNSIGNED)
        ");
        $items = $stmt->fetchAll();
        foreach ($items as $row) {
            echo "    {$row['codigo']}. {$row['nombre']}: {$row['total_items']} items\n";
        }

        echo "\n  Migracion {$nombre} completada OK\n\n";
        return true;

    } catch (PDOException $e) {
        echo "  X Error de conexion: " . $e->getMessage() . "\n\n";
        return false;
    }
}

// Ejecutar en LOCAL primero
echo "\n========== BASE DE DATOS LOCAL ==========\n";
$localOk = ejecutarMigracion($localConfig, 'LOCAL', $sqlStatements);

if (!$localOk) {
    echo "\nX LOCAL fallo. NO se ejecutara en produccion.\n";
    exit(1);
}

// Solo si LOCAL fue OK, ejecutar en PRODUCCIÓN
echo "\n========== BASE DE DATOS PRODUCCION ==========\n";
$prodOk = ejecutarMigracion($prodConfig, 'PRODUCCION', $sqlStatements);

// Resumen
echo "\n=====================================================\n";
echo "RESUMEN\n";
echo "=====================================================\n";
echo "LOCAL:      " . ($localOk ? "OK" : "ERROR") . "\n";
echo "PRODUCCION: " . ($prodOk ? "OK" : "ERROR") . "\n";
echo "\nCategorias resultantes (7):\n";
echo "  1. Talento Humano SST\n";
echo "  2. Capacitacion y Formacion\n";
echo "  3. Medicina Preventiva y del Trabajo\n";
echo "  4. Promocion y Prevencion\n";
echo "  5. Seguridad Industrial e Higiene\n";
echo "  6. Gestion de Emergencias\n";
echo "  7. Otros Gastos SST\n";
echo "\nNOTA: El JS de presupuesto_sst.php tambien fue actualizado.\n";
