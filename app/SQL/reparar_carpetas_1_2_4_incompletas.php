<?php

/**
 * Script: reparar_carpetas_1_2_4_incompletas.php
 *
 * Problema: el script anterior (agregar_carpeta_1_2_4_reglamento.php) saltó
 * clientes que YA tenían 1.2.4, pero no verificó si TODAS sus raíces/años
 * tienen la carpeta. Un cliente puede tener múltiples raíces SG-SST YYYY
 * (ej. por regeneración) y solo la primera tiene 1.2.4.
 *
 * Este script:
 * 1. Busca TODAS las carpetas PLANEAR (hijas de raíces SG-SST) que NO
 *    tengan un hijo 1.2.4, y los repara (shift orden >= 12, INSERT 1.2.4).
 * 2. Verifica también que las carpetas 2.1.1 queden en orden >= 13.
 * 3. Imprime un reporte de verificación final.
 *
 * LOCAL primero. PRODUCCIÓN solo si LOCAL OK.
 */

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

function ejecutarReparacion(PDO $pdo, string $entorno): bool
{
    echo "=== {$entorno} ===\n";
    $ok = true;

    $pdo->beginTransaction();
    try {
        // ------------------------------------------------------------------
        // FASE 1: Encontrar todos los PLANEAR sin 1.2.4
        // Un PLANEAR es una carpeta con código '1' hija de una raíz SG-SST
        // ------------------------------------------------------------------
        $planarSin124 = $pdo->query("
            SELECT planear.id_carpeta AS id_planear, planear.id_cliente
            FROM tbl_doc_carpetas planear
            JOIN tbl_doc_carpetas raiz ON raiz.id_carpeta = planear.id_carpeta_padre
            WHERE planear.codigo = '1'
              AND raiz.tipo = 'raiz'
              AND NOT EXISTS (
                  SELECT 1 FROM tbl_doc_carpetas h
                  WHERE h.id_carpeta_padre = planear.id_carpeta
                    AND h.codigo = '1.2.4'
              )
        ")->fetchAll(PDO::FETCH_ASSOC);

        $total = count($planarSin124);
        echo "  PLANEAR sin carpeta 1.2.4: {$total}\n";

        $insertadas = 0;
        foreach ($planarSin124 as $row) {
            $idPlanear  = (int) $row['id_planear'];
            $idCliente  = (int) $row['id_cliente'];

            // Shift: carpetas con orden >= 12 bajo este PLANEAR pasan a +1
            $pdo->prepare("
                UPDATE tbl_doc_carpetas
                SET orden = orden + 1
                WHERE id_carpeta_padre = ? AND orden >= 12
            ")->execute([$idPlanear]);

            // Insertar 1.2.4
            $pdo->prepare("
                INSERT INTO tbl_doc_carpetas
                    (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
                VALUES (?, ?, '1.2.4. Reglamento de Higiene y Seguridad Industrial',
                        '1.2.4', 12, 'estandar', 'clipboard-check')
            ")->execute([$idCliente, $idPlanear]);

            echo "    Reparado: cliente_id={$idCliente}, PLANEAR_id={$idPlanear}\n";
            $insertadas++;
        }

        echo "  Carpetas 1.2.4 insertadas en reparación: {$insertadas}\n";

        // ------------------------------------------------------------------
        // FASE 2: Verificación — buscar CUALQUIER 2.1.1 en orden < 13
        //         que tenga un hermano 1.2.4 (indicaría shift sin aplicar)
        // ------------------------------------------------------------------
        $problemas = $pdo->query("
            SELECT c211.id_carpeta, c211.id_cliente, c211.orden, c211.id_carpeta_padre
            FROM tbl_doc_carpetas c211
            WHERE c211.codigo = '2.1.1'
              AND c211.orden < 13
              AND EXISTS (
                  SELECT 1 FROM tbl_doc_carpetas c124
                  WHERE c124.id_carpeta_padre = c211.id_carpeta_padre
                    AND c124.codigo = '1.2.4'
              )
        ")->fetchAll(PDO::FETCH_ASSOC);

        if (empty($problemas)) {
            echo "  VERIFICACIÓN: OK — ningún 2.1.1 en orden incorrecto\n";
        } else {
            echo "  ADVERTENCIA: hay 2.1.1 con orden < 13 junto a 1.2.4:\n";
            foreach ($problemas as $p) {
                echo "    cliente={$p['id_cliente']} carpeta_id={$p['id_carpeta']} orden={$p['orden']}\n";
                // Corrección directa: el UPDATE de shift debería haberlo movido
                // Si aún está en <13 es porque ya tenía 1.2.4 y el FASE1 no lo procesó
                // Corregimos el orden directamente
                $pdo->prepare("
                    UPDATE tbl_doc_carpetas
                    SET orden = orden + 1
                    WHERE id_carpeta_padre = ? AND orden >= 12
                      AND codigo NOT IN ('1.2.4')
                ")->execute([$p['id_carpeta_padre']]);
                echo "    Corregido shift para PLANEAR_id={$p['id_carpeta_padre']}\n";
            }
        }

        // ------------------------------------------------------------------
        // FASE 3: Reporte final de estado
        // ------------------------------------------------------------------
        $resumen = $pdo->query("
            SELECT
                COUNT(DISTINCT CASE WHEN c.codigo = '1.2.4' THEN c.id_carpeta_padre END) AS planear_con_124,
                COUNT(DISTINCT CASE WHEN c.codigo = '1' AND EXISTS (
                    SELECT 1 FROM tbl_doc_carpetas h WHERE h.id_carpeta_padre = c.id_carpeta AND h.codigo = '1.2.4'
                ) THEN c.id_carpeta END) AS planear_con_124_check
            FROM tbl_doc_carpetas c
            JOIN tbl_doc_carpetas raiz ON raiz.id_carpeta = c.id_carpeta_padre
            WHERE c.codigo = '1' AND raiz.tipo = 'raiz'
        ")->fetch(PDO::FETCH_ASSOC);

        $totalPlanear = $pdo->query("
            SELECT COUNT(*) FROM tbl_doc_carpetas c
            JOIN tbl_doc_carpetas raiz ON raiz.id_carpeta = c.id_carpeta_padre
            WHERE c.codigo = '1' AND raiz.tipo = 'raiz'
        ")->fetchColumn();

        echo "  REPORTE FINAL:\n";
        echo "    Total PLANEAR en BD: {$totalPlanear}\n";
        echo "    PLANEAR con 1.2.4:  {$resumen['planear_con_124']}\n";

        $pdo->commit();
        echo "  COMMIT OK ✓\n";

    } catch (\Exception $e) {
        $pdo->rollBack();
        echo "  ERROR: " . $e->getMessage() . "\n";
        echo "  ROLLBACK ejecutado\n";
        $ok = false;
    }

    return $ok;
}

function verificarSP(PDO $pdo, string $entorno): void
{
    echo "\n--- Verificación SP en {$entorno} ---\n";
    $sp = $pdo->query("SHOW PROCEDURE STATUS WHERE Name = 'sp_generar_carpetas_por_nivel'")->fetch(PDO::FETCH_ASSOC);
    if ($sp) {
        echo "  SP existe: {$sp['Name']}\n";
        echo "  Modificado: {$sp['Modified']}\n";
        // Verificar que contiene 1.2.4
        $body = $pdo->query("SELECT ROUTINE_DEFINITION FROM information_schema.ROUTINES WHERE ROUTINE_NAME = 'sp_generar_carpetas_por_nivel' AND ROUTINE_SCHEMA = DATABASE()")->fetchColumn();
        if (strpos($body, '1.2.4') !== false) {
            echo "  Contiene 1.2.4: SI ✓\n";
        } else {
            echo "  Contiene 1.2.4: NO ✗ (SP desactualizado!)\n";
        }
    } else {
        echo "  SP NO encontrado ✗\n";
    }
}

// ============================================================
// EJECUCIÓN
// ============================================================

echo "\nConectando LOCAL...\n";
try {
    $pdoLocal = conectar($configLocal);
    $localOk  = ejecutarReparacion($pdoLocal, 'LOCAL');
    verificarSP($pdoLocal, 'LOCAL');
} catch (\Exception $e) {
    echo "ERROR conexión LOCAL: " . $e->getMessage() . "\n";
    $localOk = false;
}

echo "\n";

if (!$localOk) {
    echo "LOCAL falló. NO se ejecuta PRODUCCIÓN.\n";
    exit(1);
}

echo "LOCAL OK. Conectando PRODUCCIÓN...\n";
try {
    $pdoProd = conectar($configProd);
    $prodOk  = ejecutarReparacion($pdoProd, 'PRODUCCIÓN');
    verificarSP($pdoProd, 'PRODUCCIÓN');
    if (!$prodOk) {
        echo "\nPRODUCCIÓN falló.\n";
        exit(2);
    }
} catch (\Exception $e) {
    echo "ERROR conexión PRODUCCIÓN: " . $e->getMessage() . "\n";
    exit(2);
}

echo "\nReparación completada correctamente en LOCAL y PRODUCCIÓN.\n";
