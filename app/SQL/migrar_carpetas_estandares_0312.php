<?php
/**
 * Migración: Reestructurar carpetas de categorías a estándares Res. 0312/2019
 *
 * ANTES: SG-SST / PHVA / Categoría (1.1 Recursos, 2.4 Capacitación, etc.)
 * AHORA: SG-SST / PHVA / Estándar (1.1.1, 1.2.1, 2.1.1, etc.)
 *
 * Este script:
 * 1. Re-crea el stored procedure sp_generar_carpetas_por_nivel
 * 2. Actualiza el mapeo tbl_doc_plantilla_carpeta
 * 3. Regenera las carpetas para cada cliente existente
 *
 * Uso: php migrar_carpetas_estandares_0312.php
 */

echo "=============================================================\n";
echo "MIGRACIÓN: Carpetas por Estándares Res. 0312/2019\n";
echo "=============================================================\n\n";

$conexiones = [
    'LOCAL' => [
        'dsn' => 'mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4',
        'user' => 'root',
        'pass' => '',
        'options' => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
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

// Leer el SP desde el archivo SQL
$spFile = __DIR__ . '/sp/sp_04_generar_carpetas_por_nivel.sql';
if (!file_exists($spFile)) {
    echo "ERROR: No se encuentra el archivo del SP: {$spFile}\n";
    exit(1);
}
$spSQL = file_get_contents($spFile);

// Extraer solo el DROP y CREATE del SP (sin USE y DELIMITER)
// Ejecutaremos los comandos por separado
$dropSQL = "DROP PROCEDURE IF EXISTS sp_generar_carpetas_por_nivel";

// Extraer el body del CREATE PROCEDURE entre DELIMITER // y // DELIMITER ;
if (preg_match('/DELIMITER \/\/\s*(CREATE PROCEDURE.+?)\/\/\s*DELIMITER/s', $spSQL, $matches)) {
    $createSQL = $matches[1];
} else {
    echo "ERROR: No se pudo extraer el CREATE PROCEDURE del archivo SQL\n";
    exit(1);
}

// Nuevos mapeos plantilla → carpeta (estándares)
$nuevosMapeos = [
    ['PRG-CAP', '1.2.1', 'Programa capacitación PYP'],
    ['PLA-CAP', '1.2.1', 'Plan capacitación anual'],
    ['CAP-ANUAL', '1.2.1', 'Cronograma capacitación'],
    ['FOR-ASI', '1.2.1', 'Formato asistencia capacitación'],
    ['POL-SST', '2.1.1', 'Política SST'],
    ['OBJ-SST', '2.2.1', 'Objetivos SST'],
    ['PLA-TRA', '2.4.1', 'Plan anual de trabajo'],
    ['MTZ-LEG', '2.7.1', 'Matriz legal'],
    ['PRO-EMO', '3.1.1', 'Procedimiento exámenes médicos'],
    ['PRG-MED', '3.1.2', 'Programa medicina preventiva'],
    ['PRO-REP', '3.2.1', 'Procedimiento reporte ATEL'],
    ['PRO-INV', '3.2.2', 'Procedimiento investigación ATEL'],
    ['PRO-IPR', '4.1.1', 'Procedimiento IPER'],
    ['MTZ-PEL', '4.1.2', 'Matriz de peligros'],
    ['MTZ-EPP', '4.2.6', 'Matriz EPP'],
    ['PRO-EPP', '4.2.6', 'Procedimiento EPP'],
    ['PLA-EME', '5.1.1', 'Plan de emergencias'],
    ['PRG-BRI', '5.1.2', 'Programa brigada'],
];

foreach ($conexiones as $entorno => $config) {
    echo "--- {$entorno} ---\n";

    try {
        $pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);
        echo "  Conectado OK\n";

        // PASO 1: Re-crear el Stored Procedure
        echo "  [1/4] Eliminando SP antiguo...\n";
        $pdo->exec($dropSQL);
        echo "        DROP OK\n";

        echo "  [2/4] Creando SP nuevo con 60 estándares...\n";
        $pdo->exec($createSQL);
        echo "        CREATE OK\n";

        // PASO 2: Actualizar tbl_doc_plantilla_carpeta si existe
        echo "  [3/4] Actualizando mapeo plantilla→carpeta...\n";
        $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_doc_plantilla_carpeta'");
        if ($stmt->rowCount() > 0) {
            $pdo->exec("DELETE FROM tbl_doc_plantilla_carpeta");
            $insertMapeo = $pdo->prepare(
                "INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta, descripcion) VALUES (?, ?, ?)"
            );
            foreach ($nuevosMapeos as $mapeo) {
                try {
                    $insertMapeo->execute($mapeo);
                } catch (PDOException $e) {
                    echo "        Warn mapeo {$mapeo[0]}: " . $e->getMessage() . "\n";
                }
            }
            echo "        " . count($nuevosMapeos) . " mapeos insertados\n";
        } else {
            echo "        Tabla tbl_doc_plantilla_carpeta no existe, saltando\n";
        }

        // PASO 3: Regenerar carpetas para cada cliente
        echo "  [4/4] Regenerando carpetas para clientes existentes...\n";

        // Obtener clientes que tienen carpetas generadas
        $stmt = $pdo->query("
            SELECT DISTINCT c.id_cliente,
                   COALESCE(ctx.nivel_estandares, 60) as nivel_estandares
            FROM tbl_doc_carpetas dc
            JOIN tbl_clientes c ON c.id_cliente = dc.id_cliente
            LEFT JOIN tbl_cliente_contexto_sst ctx ON ctx.id_cliente = c.id_cliente
            WHERE dc.tipo = 'raiz'
        ");
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($clientes)) {
            echo "        No hay clientes con carpetas generadas\n";
        } else {
            foreach ($clientes as $cliente) {
                $idCliente = $cliente['id_cliente'];
                $nivel = (int)$cliente['nivel_estandares'];

                // Obtener los años de las raíces existentes
                $stmtAnios = $pdo->prepare("
                    SELECT nombre FROM tbl_doc_carpetas
                    WHERE id_cliente = ? AND tipo = 'raiz'
                ");
                $stmtAnios->execute([$idCliente]);
                $raices = $stmtAnios->fetchAll(PDO::FETCH_COLUMN);

                foreach ($raices as $nombreRaiz) {
                    // Extraer año del nombre "SG-SST 2026"
                    if (preg_match('/SG-SST (\d{4})/', $nombreRaiz, $m)) {
                        $anio = (int)$m[1];
                        try {
                            $pdo->prepare("CALL sp_generar_carpetas_por_nivel(?, ?, ?)")
                                ->execute([$idCliente, $anio, $nivel]);
                            echo "        Cliente {$idCliente} año {$anio} nivel {$nivel} → OK\n";
                        } catch (PDOException $e) {
                            echo "        Cliente {$idCliente} año {$anio}: ERROR - " . $e->getMessage() . "\n";
                        }
                    }
                }
            }
        }

        echo "  ✓ {$entorno} completado\n\n";

    } catch (PDOException $e) {
        echo "  ERROR: " . $e->getMessage() . "\n\n";
    }
}

echo "=============================================================\n";
echo "Migración completada.\n";
echo "=============================================================\n";
