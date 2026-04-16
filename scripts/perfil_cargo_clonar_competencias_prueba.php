<?php
/**
 * Clona el diccionario de competencias de cliente 11 (Cycloid) → cliente 18 (Validacion).
 *
 * SOLO LOCAL. No tiene modo --env=prod intencionalmente.
 *
 * Copia:
 *   - tbl_competencia_escala_cliente (escala 1-5)
 *   - tbl_competencia_cliente (catalogo de competencias)
 *   - tbl_competencia_nivel_cliente (rubricas de cada competencia)
 *
 * Idempotente: aborta si cliente 18 ya tiene competencias activas.
 * Regenera IDs (no conserva PKs originales).
 *
 * Uso:
 *   php scripts/perfil_cargo_clonar_competencias_prueba.php
 *
 * Motivacion: desbloquear pruebas end-to-end del modulo Perfiles de Cargo
 * en LOCAL, donde cliente 18 tiene 29 cargos reales pero 0 competencias.
 */

$ORIGEN  = 11; // Cycloid Talent SAS
$DESTINO = 18; // CLIENTE DE VALIDACION

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=empresas_sst;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "Conexion LOCAL OK\n";
} catch (Throwable $e) {
    echo "ERROR conexion: " . $e->getMessage() . "\n";
    exit(1);
}

// Verificar clientes existen
$origen  = $pdo->query("SELECT id_cliente, nombre_cliente FROM tbl_clientes WHERE id_cliente={$ORIGEN}")->fetch(PDO::FETCH_ASSOC);
$destino = $pdo->query("SELECT id_cliente, nombre_cliente FROM tbl_clientes WHERE id_cliente={$DESTINO}")->fetch(PDO::FETCH_ASSOC);
if (!$origen || !$destino) {
    echo "ERROR: Cliente origen ({$ORIGEN}) o destino ({$DESTINO}) no existe\n";
    exit(1);
}
echo "Origen:  {$ORIGEN} — {$origen['nombre_cliente']}\n";
echo "Destino: {$DESTINO} — {$destino['nombre_cliente']}\n\n";

// Guardrail: destino debe estar vacio
$nDest = (int)$pdo->query("SELECT COUNT(*) FROM tbl_competencia_cliente WHERE id_cliente={$DESTINO}")->fetchColumn();
if ($nDest > 0) {
    echo "ABORT: Cliente destino ya tiene {$nDest} competencias. Script idempotente — no sobreescribe.\n";
    exit(0);
}

// Origen debe tener datos
$nOrig = (int)$pdo->query("SELECT COUNT(*) FROM tbl_competencia_cliente WHERE id_cliente={$ORIGEN} AND activo=1")->fetchColumn();
if ($nOrig === 0) {
    echo "ERROR: Cliente origen no tiene competencias activas\n";
    exit(1);
}
echo "Cliente origen tiene {$nOrig} competencias activas\n\n";

$pdo->beginTransaction();
try {
    // 1. Clonar escala
    echo "-- Paso 1: Clonar escala 1-5 --\n";
    $rows = $pdo->query("SELECT nivel, nombre, etiqueta, descripcion FROM tbl_competencia_escala_cliente WHERE id_cliente={$ORIGEN} ORDER BY nivel")->fetchAll(PDO::FETCH_ASSOC);
    $ins = $pdo->prepare("INSERT INTO tbl_competencia_escala_cliente (id_cliente, nivel, nombre, etiqueta, descripcion) VALUES (?,?,?,?,?)");
    foreach ($rows as $r) {
        $ins->execute([$DESTINO, $r['nivel'], $r['nombre'], $r['etiqueta'], $r['descripcion']]);
    }
    echo "  OK  " . count($rows) . " filas en tbl_competencia_escala_cliente\n";

    // 2. Clonar competencias + mapeo id_origen → id_destino
    echo "-- Paso 2: Clonar competencias --\n";
    $rows = $pdo->query("SELECT id_competencia, numero, codigo, nombre, definicion, pregunta_clave, familia, activo FROM tbl_competencia_cliente WHERE id_cliente={$ORIGEN} ORDER BY numero")->fetchAll(PDO::FETCH_ASSOC);
    $ins = $pdo->prepare("INSERT INTO tbl_competencia_cliente (id_cliente, numero, codigo, nombre, definicion, pregunta_clave, familia, activo) VALUES (?,?,?,?,?,?,?,?)");
    $mapa = []; // id_origen => id_destino
    foreach ($rows as $r) {
        $ins->execute([$DESTINO, $r['numero'], $r['codigo'], $r['nombre'], $r['definicion'], $r['pregunta_clave'], $r['familia'], $r['activo']]);
        $mapa[(int)$r['id_competencia']] = (int)$pdo->lastInsertId();
    }
    echo "  OK  " . count($rows) . " filas en tbl_competencia_cliente\n";

    // 3. Clonar niveles (rubricas) por cada competencia
    echo "-- Paso 3: Clonar niveles/rubricas --\n";
    $selNiv = $pdo->prepare("SELECT nivel_numero, titulo_corto, descripcion_conducta FROM tbl_competencia_nivel_cliente WHERE id_competencia=? ORDER BY nivel_numero");
    $insNiv = $pdo->prepare("INSERT INTO tbl_competencia_nivel_cliente (id_competencia, nivel_numero, titulo_corto, descripcion_conducta) VALUES (?,?,?,?)");
    $totalNiv = 0;
    foreach ($mapa as $idOrigen => $idDestino) {
        $selNiv->execute([$idOrigen]);
        foreach ($selNiv->fetchAll(PDO::FETCH_ASSOC) as $n) {
            $insNiv->execute([$idDestino, $n['nivel_numero'], $n['titulo_corto'], $n['descripcion_conducta']]);
            $totalNiv++;
        }
    }
    echo "  OK  {$totalNiv} filas en tbl_competencia_nivel_cliente\n";

    $pdo->commit();
    echo "\nFASE CLONADO COMPLETA\n";
    echo "Cliente 18 ahora tiene:\n";
    echo "  - " . (int)$pdo->query("SELECT COUNT(*) FROM tbl_competencia_escala_cliente WHERE id_cliente={$DESTINO}")->fetchColumn() . " filas de escala\n";
    echo "  - " . (int)$pdo->query("SELECT COUNT(*) FROM tbl_competencia_cliente WHERE id_cliente={$DESTINO}")->fetchColumn() . " competencias\n";
    echo "  - " . (int)$pdo->query("SELECT COUNT(*) FROM tbl_competencia_nivel_cliente WHERE id_competencia IN (SELECT id_competencia FROM tbl_competencia_cliente WHERE id_cliente={$DESTINO})")->fetchColumn() . " niveles\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\nRollback ejecutado.\n";
    exit(1);
}
