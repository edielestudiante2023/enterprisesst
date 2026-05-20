<?php
/**
 * Backfill: sincroniza tbl_cliente_estandares -> evaluacion_inicial_sst para un cliente,
 * para que /listEvaluaciones muestre TODOS los estandares incluido "NO APLICA",
 * alimentado desde /estandares/{id} (fuente de verdad).
 *
 * Modo SEGURO "solo rellenar": CREA los registros faltantes y RELLENA los vacios,
 * pero NO pisa una evaluacion ya hecha (evaluacion_inicial con valor). Use --forzar
 * para sobrescribir todo (peligroso).
 *
 * Mapeo identico a App\Services\SyncEstandaresService.
 *
 * Uso:
 *   php scripts/backfill_evaluaciones_cliente.php --cliente=15                     # LOCAL  dry-run
 *   php scripts/backfill_evaluaciones_cliente.php --cliente=15 --apply             # LOCAL  aplica
 *   php scripts/backfill_evaluaciones_cliente.php --cliente=15 --env=prod          # PROD   dry-run
 *   php scripts/backfill_evaluaciones_cliente.php --cliente=15 --env=prod --apply  # PROD   aplica
 */

$argvList     = $argv ?? [];
$esProduccion = in_array('--env=prod', $argvList, true);
$aplicar      = in_array('--apply', $argvList, true);
$forzar       = in_array('--forzar', $argvList, true);
$idCliente    = 15;
foreach ($argvList as $a) {
    if (str_starts_with($a, '--cliente=')) $idCliente = (int) substr($a, 10);
}

$ESTADO_TO_EVAL = [
    'cumple' => 'CUMPLE TOTALMENTE', 'no_cumple' => 'NO CUMPLE', 'no_aplica' => 'NO APLICA',
    'pendiente' => '', 'en_proceso' => '',
];
$MAP_CICLO = ['PLANEAR' => 'I. PLANEAR', 'HACER' => 'II. HACER', 'VERIFICAR' => 'III. VERIFICAR', 'ACTUAR' => 'IV. ACTUAR'];

if ($esProduccion) {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060; $dbname = 'empresas_sst';
    $username = 'cycloid_userdb'; $password = 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $ssl = true; echo "=== PRODUCCION ===\n";
} else {
    $host = '127.0.0.1'; $port = 3306; $dbname = 'empresas_sst';
    $username = 'root'; $password = ''; $ssl = false; echo "=== LOCAL ===\n";
}
echo "Modo: " . ($aplicar ? "APLICAR" : "DRY RUN") . ($forzar ? " (FORZAR sobrescritura)" : " (solo rellenar)") . " | cliente={$idCliente}\n\n";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) { $options[PDO::MYSQL_ATTR_SSL_CA] = true; $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; }
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Conexion OK\n\n";
} catch (Throwable $e) { echo "ERROR conexion: " . $e->getMessage() . "\n"; exit(1); }

// Estandares del cliente con datos del maestro
$st = $pdo->prepare(
    "SELECT ce.estado, m.id_estandar, m.item, m.nombre, m.criterio, m.ciclo_phva, m.categoria, m.categoria_nombre, m.peso_porcentual
       FROM tbl_cliente_estandares ce
       JOIN tbl_estandares_minimos m ON m.id_estandar = ce.id_estandar
      WHERE ce.id_cliente = ?
      ORDER BY m.item ASC"
);
$st->execute([$idCliente]);
$filas = $st->fetchAll(PDO::FETCH_ASSOC);

if (!$filas) { echo "El cliente {$idCliente} no tiene registros en tbl_cliente_estandares. Nada que hacer.\n"; exit(0); }

$resolverCicloEstandar = function (array $m) use ($pdo, $idCliente, $MAP_CICLO): array {
    if (!empty($m['categoria'])) {
        $h = $pdo->prepare(
            "SELECT e.ciclo, e.estandar FROM evaluacion_inicial_sst e
               JOIN tbl_estandares_minimos mm ON mm.item = e.numeral
              WHERE e.id_cliente = ? AND mm.categoria = ? AND e.ciclo IS NOT NULL LIMIT 1"
        );
        $h->execute([$idCliente, $m['categoria']]);
        $row = $h->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['ciclo'])) return [$row['ciclo'], $row['estandar']];
    }
    $ciclo = $MAP_CICLO[strtoupper(trim($m['ciclo_phva'] ?? ''))] ?? ($m['ciclo_phva'] ?? '');
    return [$ciclo, $m['categoria_nombre'] ?? ''];
};

$crear = $rellenar = $pisar = $skip = 0;
$ejemplos = [];

$pdo->beginTransaction();
foreach ($filas as $f) {
    $numeral = $f['item'];
    $eval = $ESTADO_TO_EVAL[$f['estado']] ?? '';
    $r = $pdo->prepare("SELECT id_ev_ini, evaluacion_inicial, valor FROM evaluacion_inicial_sst WHERE id_cliente=? AND numeral=?");
    $r->execute([$idCliente, $numeral]);
    $reg = $r->fetch(PDO::FETCH_ASSOC);

    if ($reg) {
        $tieneValor = trim((string) ($reg['evaluacion_inicial'] ?? '')) !== '';
        if ($tieneValor && !$forzar) { $skip++; continue; }
        $valor = (float) ($reg['valor'] ?? $f['peso_porcentual'] ?? 0);
        $puntaje = in_array($eval, ['CUMPLE TOTALMENTE', 'NO APLICA'], true) ? $valor : 0;
        if ($aplicar) {
            $u = $pdo->prepare("UPDATE evaluacion_inicial_sst SET evaluacion_inicial=?, puntaje_cuantitativo=?, updated_at=NOW() WHERE id_ev_ini=?");
            $u->execute([$eval !== '' ? $eval : null, $puntaje, $reg['id_ev_ini']]);
        }
        if ($tieneValor) { $pisar++; } else { $rellenar++; }
        if (count($ejemplos) < 8) $ejemplos[] = "  " . ($tieneValor ? "PISAR " : "RELLENAR") . " {$numeral} -> [" . ($eval ?: 'sin evaluar') . "]";
    } else {
        [$ciclo, $estandarTxt] = $resolverCicloEstandar($f);
        $valor = (float) ($f['peso_porcentual'] ?? 0);
        $puntaje = in_array($eval, ['CUMPLE TOTALMENTE', 'NO APLICA'], true) ? $valor : 0;
        if ($aplicar) {
            $ins = $pdo->prepare(
                "INSERT INTO evaluacion_inicial_sst (id_cliente, ciclo, estandar, numeral, item_del_estandar, item, criterio, evaluacion_inicial, valor, puntaje_cuantitativo, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),NOW())"
            );
            $ins->execute([$idCliente, $ciclo, $estandarTxt, $numeral, $f['nombre'], $f['nombre'], $f['criterio'], $eval !== '' ? $eval : null, $valor, $puntaje]);
        }
        $crear++;
        if (count($ejemplos) < 8) $ejemplos[] = "  CREAR   {$numeral} ({$ciclo} / {$estandarTxt}) -> [" . ($eval ?: 'sin evaluar') . "]";
    }
}

if ($aplicar) { $pdo->commit(); } else { $pdo->rollBack(); }

echo "Resumen (de " . count($filas) . " estandares del cliente):\n";
echo "  CREAR (faltantes)        : {$crear}\n";
echo "  RELLENAR (existia vacio) : {$rellenar}\n";
echo "  PISAR (tenia evaluacion) : {$pisar}" . ($forzar ? "" : "  <- omitidos (usa --forzar para sobrescribir)") . "\n";
echo "  SKIP (ya evaluados)      : {$skip}\n\n";
echo "Ejemplos:\n" . (implode("\n", $ejemplos) ?: "  (ninguno)") . "\n\n";
echo $aplicar ? "APLICADO (transaccion confirmada).\n" : "DRY RUN: no se modifico nada. Usa --apply para aplicar.\n";
