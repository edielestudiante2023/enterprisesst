<?php

/**
 * Script: agregar_carpeta_1_2_4_reglamento.php
 *
 * Crea la carpeta 1.2.4 "Reglamento de Higiene y Seguridad Industrial"
 * para todos los clientes existentes que tengan la estructura PLANEAR (1.2.3).
 *
 * También:
 *   - Agrega entrada en tbl_doc_plantilla_carpeta: REG-HSI → 1.2.4
 *   - Actualiza estandar en tbl_doc_tipo_configuracion para
 *     reglamento_higiene_seguridad (de '1.1.2' a '1.2.4')
 *
 * Ejecutar: php app/SQL/agregar_carpeta_1_2_4_reglamento.php
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
        // PASO 1: Insertar carpeta 1.2.4 para cada cliente que tenga 1.2.3
        // ------------------------------------------------------------------
        // La carpeta 1.2.4 va bajo el mismo padre (PLANEAR) que 1.2.3.
        // El orden 12 queda libre porque en el nuevo SP:
        //   1.2.3 = orden 11, 1.2.4 = orden 12, 2.1.1 = orden 13
        // Para carpetas existentes corremos UPDATE previo para desplazar
        // las que ya están en orden >= 12 bajo PLANEAR.

        $carpetas123 = $pdo->query(
            "SELECT id_cliente, id_carpeta_padre
             FROM tbl_doc_carpetas
             WHERE codigo = '1.2.3'"
        )->fetchAll(PDO::FETCH_ASSOC);

        $total    = count($carpetas123);
        $nuevas   = 0;
        $yaExistian = 0;

        echo "Clientes con carpeta 1.2.3 encontrados: {$total}\n";

        foreach ($carpetas123 as $row) {
            $idCliente     = (int) $row['id_cliente'];
            $idPadrePlanear = (int) $row['id_carpeta_padre'];

            // Verificar si 1.2.4 ya existe para este cliente
            $chk = $pdo->prepare(
                "SELECT COUNT(*) FROM tbl_doc_carpetas
                 WHERE id_cliente = ? AND codigo = '1.2.4'"
            );
            $chk->execute([$idCliente]);
            if ($chk->fetchColumn() > 0) {
                $yaExistian++;
                continue;
            }

            // Desplazar en +1 todas las carpetas hijas de PLANEAR con orden >= 12
            // (esto mueve 2.1.1 de orden=12 a 13, 2.2.1 de 13 a 14, etc.)
            $pdo->prepare(
                "UPDATE tbl_doc_carpetas
                 SET orden = orden + 1
                 WHERE id_carpeta_padre = ? AND orden >= 12"
            )->execute([$idPadrePlanear]);

            // Insertar 1.2.4 en orden=12
            $pdo->prepare(
                "INSERT INTO tbl_doc_carpetas
                    (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
                 VALUES (?, ?, '1.2.4. Reglamento de Higiene y Seguridad Industrial',
                         '1.2.4', 12, 'estandar', 'clipboard-check')"
            )->execute([$idCliente, $idPadrePlanear]);

            $nuevas++;
        }

        echo "  Carpetas 1.2.4 insertadas: {$nuevas}\n";
        echo "  Ya existían (skipped):     {$yaExistian}\n";

        // ------------------------------------------------------------------
        // PASO 2: Agregar tbl_doc_plantilla_carpeta REG-HSI → 1.2.4
        // ------------------------------------------------------------------
        $existePlantilla = $pdo->query(
            "SELECT COUNT(*) FROM tbl_doc_plantilla_carpeta
             WHERE codigo_plantilla = 'REG-HSI' AND codigo_carpeta = '1.2.4'"
        )->fetchColumn();

        if ($existePlantilla == 0) {
            $pdo->exec(
                "INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
                 VALUES ('REG-HSI', '1.2.4')"
            );
            echo "  Plantilla REG-HSI → 1.2.4: INSERTADA\n";
        } else {
            echo "  Plantilla REG-HSI → 1.2.4: ya existía (skip)\n";
        }

        // ------------------------------------------------------------------
        // PASO 3: Actualizar estandar en tbl_doc_tipo_configuracion
        //         reglamento_higiene_seguridad: '1.1.2' → '1.2.4'
        // ------------------------------------------------------------------
        $stmt = $pdo->prepare(
            "UPDATE tbl_doc_tipo_configuracion
             SET estandar = '1.2.4'
             WHERE tipo_documento = 'reglamento_higiene_seguridad'"
        );
        $stmt->execute();
        $afectadas = $stmt->rowCount();
        echo "  tbl_doc_tipo_configuracion actualizado: {$afectadas} fila(s) (estandar '1.1.2' → '1.2.4')\n";

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

// --- Ejecución LOCAL ---
echo "\nConectando a LOCAL...\n";
try {
    $pdoLocal  = conectar($configLocal);
    $localOk   = ejecutarMigracion($pdoLocal, 'LOCAL');
} catch (\Exception $e) {
    echo "ERROR de conexión LOCAL: " . $e->getMessage() . "\n";
    $localOk = false;
}

echo "\n";

// --- Ejecución PRODUCCIÓN (solo si LOCAL exitoso) ---
if (! $localOk) {
    echo "LOCAL falló. NO se ejecuta PRODUCCIÓN.\n";
    exit(1);
}

echo "LOCAL OK. Conectando a PRODUCCIÓN...\n";
try {
    $pdoProd = conectar($configProd);
    $prodOk  = ejecutarMigracion($pdoProd, 'PRODUCCIÓN');
    if (! $prodOk) {
        echo "\nPRODUCCIÓN falló.\n";
        exit(2);
    }
} catch (\Exception $e) {
    echo "ERROR de conexión PRODUCCIÓN: " . $e->getMessage() . "\n";
    exit(2);
}

echo "\nMigración completada correctamente en LOCAL y PRODUCCIÓN.\n";
