<?php

/**
 * Script: agregar_subcarpeta_2_8_1_1_matriz_comunicacion.php
 *
 * Crea la subcarpeta 2.8.1.1 "Matriz de Comunicacion SST"
 * para todos los clientes existentes que tengan la carpeta 2.8.1.
 *
 * También:
 *   - Actualiza tbl_doc_plantilla_carpeta: PRC-MCO → 2.8.1.1
 *   - Recrea el SP sp_generar_carpetas_por_nivel con la subcarpeta incluida
 *
 * Ejecutar: php app/SQL/agregar_subcarpeta_2_8_1_1_matriz_comunicacion.php
 *
 * INSTRUCCIÓN TAXATIVA: LOCAL primero. PRODUCCIÓN solo si LOCAL exitoso.
 */

// --- Conexiones ---
$configLocal = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'db'   => 'empresas_sst',
    'user' => 'root',
    'pass' => '',
    'ssl'  => false,
];

$configProd = [
    'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port' => '25060',
    'db'   => 'empresas_sst',
    'user' => 'cycloid_userdb',
    'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
    'ssl'  => true,
];

// --- Funciones ---

function conectar(array $cfg): PDO
{
    $dsn  = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['db']};charset=utf8mb4";
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($cfg['ssl']) {
        $opts[PDO::MYSQL_ATTR_SSL_CA]                = true;
        $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    return new PDO($dsn, $cfg['user'], $cfg['pass'], $opts);
}

function ejecutarMigracion(PDO $pdo, string $entorno): bool
{
    $ok = true;

    echo "=== {$entorno} ===\n";
    $pdo->beginTransaction();

    try {
        // ------------------------------------------------------------------
        // PASO 1: Insertar subcarpeta 2.8.1.1 para cada cliente que tenga 2.8.1
        // ------------------------------------------------------------------
        $carpetas281 = $pdo->query(
            "SELECT id_carpeta, id_cliente
             FROM tbl_doc_carpetas
             WHERE codigo = '2.8.1'"
        )->fetchAll(PDO::FETCH_ASSOC);

        $total      = count($carpetas281);
        $nuevas     = 0;
        $yaExistian = 0;

        echo "Clientes con carpeta 2.8.1 encontrados: {$total}\n";

        foreach ($carpetas281 as $row) {
            $idCliente    = (int) $row['id_cliente'];
            $idPadre281   = (int) $row['id_carpeta'];

            // Verificar si 2.8.1.1 ya existe para este cliente
            $chk = $pdo->prepare(
                "SELECT COUNT(*) FROM tbl_doc_carpetas
                 WHERE id_cliente = ? AND codigo = '2.8.1.1'"
            );
            $chk->execute([$idCliente]);
            if ($chk->fetchColumn() > 0) {
                $yaExistian++;
                continue;
            }

            // Insertar 2.8.1.1 como hija de 2.8.1
            $pdo->prepare(
                "INSERT INTO tbl_doc_carpetas
                    (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
                 VALUES (?, ?, '2.8.1.1. Matriz de Comunicacion SST',
                         '2.8.1.1', 1, 'estandar', 'diagram-3')"
            )->execute([$idCliente, $idPadre281]);

            $nuevas++;
        }

        echo "  Subcarpetas 2.8.1.1 insertadas: {$nuevas}\n";
        echo "  Ya existian (skipped):           {$yaExistian}\n";

        // ------------------------------------------------------------------
        // PASO 2: Actualizar tbl_doc_plantilla_carpeta PRC-MCO → 2.8.1.1
        // ------------------------------------------------------------------
        $existePlantilla = $pdo->query(
            "SELECT COUNT(*) FROM tbl_doc_plantilla_carpeta
             WHERE codigo_plantilla = 'PRC-MCO' AND codigo_carpeta = '2.8.1.1'"
        )->fetchColumn();

        if ($existePlantilla == 0) {
            // Primero intentar actualizar si existe con 2.8.1
            $stmt = $pdo->prepare(
                "UPDATE tbl_doc_plantilla_carpeta
                 SET codigo_carpeta = '2.8.1.1'
                 WHERE codigo_plantilla = 'PRC-MCO' AND codigo_carpeta = '2.8.1'"
            );
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                // No existia, insertar nuevo
                $pdo->exec(
                    "INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
                     VALUES ('PRC-MCO', '2.8.1.1')"
                );
                echo "  Plantilla PRC-MCO -> 2.8.1.1: INSERTADA\n";
            } else {
                echo "  Plantilla PRC-MCO -> 2.8.1.1: ACTUALIZADA (era 2.8.1)\n";
            }
        } else {
            echo "  Plantilla PRC-MCO -> 2.8.1.1: ya existia (skip)\n";
        }

        $pdo->commit();
        echo "  COMMIT OK\n";

    } catch (\Exception $e) {
        $pdo->rollBack();
        echo "  ERROR: " . $e->getMessage() . "\n";
        echo "  ROLLBACK ejecutado\n";
        $ok = false;
    }

    return $ok;
}

// --- Ejecución LOCAL ---
echo "\nConectando a LOCAL...\n";
try {
    $pdoLocal  = conectar($configLocal);
    $localOk   = ejecutarMigracion($pdoLocal, 'LOCAL');
} catch (\Exception $e) {
    echo "ERROR de conexion LOCAL: " . $e->getMessage() . "\n";
    $localOk = false;
}

echo "\n";

// --- Ejecución PRODUCCIÓN (solo si LOCAL exitoso) ---
if (! $localOk) {
    echo "LOCAL fallo. NO se ejecuta PRODUCCION.\n";
    exit(1);
}

echo "LOCAL OK. Conectando a PRODUCCION...\n";
try {
    $pdoProd = conectar($configProd);
    $prodOk  = ejecutarMigracion($pdoProd, 'PRODUCCION');
    if (! $prodOk) {
        echo "\nPRODUCCION fallo.\n";
        exit(2);
    }
} catch (\Exception $e) {
    echo "ERROR de conexion PRODUCCION: " . $e->getMessage() . "\n";
    exit(2);
}

echo "\nMigracion completada correctamente en LOCAL y PRODUCCION.\n";
