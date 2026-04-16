<?php
/**
 * Diccionario de Competencias - Fase 3: Seed clientes iniciales
 *
 * Inserta la escala 1-5, las 29 competencias y sus rubricas desde
 * scripts/data/diccionario_competencias.json para:
 *   - CYCLOID TALENT SAS                       (id_cliente = 11)
 *   - COMPANIA INTERAMERICANA DE FIANZAS SAS   (id_cliente = 12)
 *
 * Idempotente a nivel cliente: si el cliente ya tiene competencias,
 * se saltea ese cliente. Para forzar re-seed pasar --force (borra y recarga).
 *
 * Uso:
 *   php scripts/diccionario_competencias_seed.php              # LOCAL
 *   php scripts/diccionario_competencias_seed.php --env=prod   # PRODUCCION
 *   php scripts/diccionario_competencias_seed.php --force      # reemplaza
 *
 * Ver: docs/MODULO_DICCIONARIO_COMPETENCIAS/ARQUITECTURA.md §7
 */

$esProduccion = in_array('--env=prod', $argv ?? []);
$forzar       = in_array('--force', $argv ?? []);

if ($esProduccion) {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $dbname   = 'empresas_sst';
    $username = 'cycloid_userdb';
    $password = getenv('DB_PROD_PASS') ?: 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $ssl      = true;
    echo "=== PRODUCCION ===\n";
} else {
    $host     = '127.0.0.1';
    $port     = 3306;
    $dbname   = 'empresas_sst';
    $username = 'root';
    $password = '';
    $ssl      = false;
    echo "=== LOCAL ===\n";
}
if ($forzar) echo "(modo --force: reemplaza diccionario existente)\n";

$jsonPath = __DIR__ . '/data/diccionario_competencias.json';
if (!file_exists($jsonPath)) {
    echo "ERROR no existe {$jsonPath}\n"; exit(1);
}
$data = json_decode(file_get_contents($jsonPath), true);
if (!$data || !isset($data['escala'], $data['competencias'])) {
    echo "ERROR JSON invalido\n"; exit(1);
}
echo "JSON OK: " . count($data['escala']) . " niveles escala, " . count($data['competencias']) . " competencias\n";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Conexion OK\n\n";
} catch (Throwable $e) {
    echo "ERROR conexion: " . $e->getMessage() . "\n"; exit(1);
}

$clientes = [
    11 => 'CYCLOID TALENT SAS',
    12 => 'COMPANIA INTERAMERICANA DE FIANZAS SAS',
];

// Precheck existencia
foreach ($clientes as $id => $nombre) {
    $q = $pdo->prepare("SELECT nombre_cliente FROM tbl_clientes WHERE id_cliente = ?");
    $q->execute([$id]);
    $real = $q->fetchColumn();
    if (!$real) {
        echo "ERROR cliente id={$id} ({$nombre}) NO existe\n"; exit(1);
    }
    echo "  OK  cliente id={$id} -> {$real}\n";
}
echo "\n";

$seedCliente = function(int $idCliente, string $label) use ($pdo, $data, $forzar) {
    echo "-- Cliente {$idCliente} {$label} --\n";

    $q = $pdo->prepare("SELECT COUNT(*) FROM tbl_competencia_cliente WHERE id_cliente = ?");
    $q->execute([$idCliente]);
    $ya = (int)$q->fetchColumn();

    if ($ya > 0 && !$forzar) {
        echo "  SKIP ya tiene {$ya} competencias. Usa --force para reemplazar.\n\n";
        return;
    }

    try {
        $pdo->beginTransaction();

        if ($ya > 0 && $forzar) {
            // FKs ON DELETE CASCADE: al borrar competencia se borran niveles y asignaciones
            $pdo->prepare("DELETE FROM tbl_competencia_cliente WHERE id_cliente = ?")->execute([$idCliente]);
            $pdo->prepare("DELETE FROM tbl_competencia_escala_cliente WHERE id_cliente = ?")->execute([$idCliente]);
            echo "  OK  registros previos eliminados\n";
        }

        // 1) Escala
        $sqlE = "INSERT INTO tbl_competencia_escala_cliente (id_cliente, nivel, nombre, etiqueta, descripcion) VALUES (?,?,?,?,?)";
        $stE = $pdo->prepare($sqlE);
        foreach ($data['escala'] as $e) {
            $stE->execute([$idCliente, $e['nivel'], $e['nombre'], $e['etiqueta'], $e['descripcion']]);
        }
        echo "  OK  escala: " . count($data['escala']) . " niveles\n";

        // 2) Competencias + niveles
        $sqlC = "INSERT INTO tbl_competencia_cliente (id_cliente, numero, codigo, nombre, definicion, pregunta_clave, familia, activo) VALUES (?,?,?,?,?,?,?,1)";
        $stC = $pdo->prepare($sqlC);
        $sqlN = "INSERT INTO tbl_competencia_nivel_cliente (id_competencia, nivel_numero, titulo_corto, descripcion_conducta) VALUES (?,?,?,?)";
        $stN = $pdo->prepare($sqlN);

        $totalN = 0;
        foreach ($data['competencias'] as $c) {
            $stC->execute([
                $idCliente,
                $c['numero'],
                $c['codigo'],
                $c['nombre'],
                $c['definicion'],
                $c['pregunta_clave'],
                $c['familia'],
            ]);
            $idComp = (int)$pdo->lastInsertId();
            foreach ($c['niveles'] as $n) {
                $stN->execute([
                    $idComp,
                    $n['nivel_numero'],
                    $n['titulo_corto'],
                    $n['descripcion_conducta'],
                ]);
                $totalN++;
            }
        }
        echo "  OK  competencias: " . count($data['competencias']) . " | niveles insertados: {$totalN}\n";

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        echo "  ERROR: " . $e->getMessage() . "\n";
        throw $e;
    }
    echo "\n";
};

foreach ($clientes as $id => $nombre) {
    $seedCliente($id, $nombre);
}

// Verificacion final
echo "-- Verificacion --\n";
foreach ($clientes as $id => $nombre) {
    $qE = $pdo->prepare("SELECT COUNT(*) FROM tbl_competencia_escala_cliente WHERE id_cliente=?");
    $qE->execute([$id]);
    $qC = $pdo->prepare("SELECT COUNT(*) FROM tbl_competencia_cliente WHERE id_cliente=?");
    $qC->execute([$id]);
    $qN = $pdo->prepare("SELECT COUNT(*) FROM tbl_competencia_nivel_cliente n INNER JOIN tbl_competencia_cliente c ON c.id_competencia=n.id_competencia WHERE c.id_cliente=?");
    $qN->execute([$id]);
    echo "  cliente={$id} escala=" . (int)$qE->fetchColumn()
        . " competencias=" . (int)$qC->fetchColumn()
        . " niveles=" . (int)$qN->fetchColumn() . "\n";
}

echo "\nFASE 3 COMPLETA\n";
