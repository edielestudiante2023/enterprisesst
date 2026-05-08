<?php
/**
 * Corrige typo "JEDE" -> "JEFE" en cargos de candidatos y miembros del comite.
 *
 * Idempotente (REPLACE solo toca strings que tienen "JEDE"). Re-ejecutar = no-op.
 *
 * Uso:
 *   php scripts/fix_typo_jede_a_jefe.php             # local dry-run
 *   php scripts/fix_typo_jede_a_jefe.php --apply     # local apply
 *   php scripts/fix_typo_jede_a_jefe.php --prod              # prod dry-run
 *   php scripts/fix_typo_jede_a_jefe.php --prod --apply      # prod apply
 */

$isProd = in_array('--prod', $argv ?? [], true);
$apply  = in_array('--apply', $argv ?? [], true);
echo "=== " . ($isProd ? 'PROD' : 'LOCAL') . " | " . ($apply ? 'APPLY' : 'DRY-RUN') . " ===\n\n";

if ($isProd) {
    $h = getenv('DB_PROD_HOST'); $u = getenv('DB_PROD_USER'); $p = getenv('DB_PROD_PASS');
    $po = (int)(getenv('DB_PROD_PORT') ?: 25060); $d = getenv('DB_PROD_NAME') ?: 'empresas_sst';
    if (!$h || !$u || !$p) { echo "ERROR env vars\n"; exit(1); }
    $c = mysqli_init();
    mysqli_ssl_set($c, null, null, null, null, null);
    if (!@mysqli_real_connect($c, $h, $u, $p, $d, $po, null, MYSQLI_CLIENT_SSL)) { echo "ERROR conn\n"; exit(1); }
} else {
    $c = new mysqli('localhost', 'root', '', 'empresas_sst');
    if ($c->connect_error) { echo "ERROR\n"; exit(1); }
}
$c->set_charset('utf8mb4');

// 1) Listar registros antes (snapshot)
echo "ANTES del fix:\n";
// Tabla => [pk, columna_cargo]
$tablas = [
    'tbl_candidatos_comite'    => ['pk' => 'id_candidato',  'col' => 'cargo'],
    'tbl_comite_miembros'      => ['pk' => 'id_miembro',    'col' => 'cargo'],
    'tbl_doc_firma_solicitudes'=> ['pk' => 'id_solicitud',  'col' => 'firmante_cargo'],
];
$snapshots = [];
foreach ($tablas as $tabla => $cfg) {
    $pk = $cfg['pk']; $col = $cfg['col'];
    $r = $c->query("SELECT {$pk} AS id, {$col} AS cargo FROM {$tabla} WHERE {$col} LIKE '%JEDE%'");
    while ($row = $r->fetch_assoc()) {
        $snapshots[] = ['tabla' => $tabla, 'pk' => $pk, 'col' => $col, 'id' => $row['id'], 'cargo_antes' => $row['cargo']];
        echo "  {$tabla}.{$col} id={$row['id']} -> [{$row['cargo']}]\n";
    }
}
echo "\nTotal a actualizar: " . count($snapshots) . "\n";

if (count($snapshots) === 0) {
    echo "\nNada que hacer.\n";
    $c->close();
    exit(0);
}

if (!$apply) {
    echo "\n[DRY-RUN] Sin cambios. Para aplicar: agregar --apply\n";
    $c->close();
    exit(0);
}

// 2) Aplicar updates
try {
    $c->begin_transaction();

    foreach ($tablas as $tabla => $cfg) {
        $col = $cfg['col'];
        $sql = "UPDATE {$tabla} SET {$col} = REPLACE({$col}, 'JEDE', 'JEFE') WHERE {$col} LIKE '%JEDE%'";
        if (!$c->query($sql)) throw new \Exception("Error en {$tabla}: " . $c->error);
        echo "OK {$tabla}.{$col}: {$c->affected_rows} fila(s) actualizadas\n";
    }

    $c->commit();
    echo "\nCommit OK.\n";
} catch (\Throwable $e) {
    $c->rollback();
    echo "\nROLLBACK: " . $e->getMessage() . "\n";
    exit(1);
}

// 3) Verificar
echo "\nVerificacion (debe haber 0 ocurrencias de JEDE):\n";
foreach ($tablas as $tabla => $cfg) {
    $col = $cfg['col'];
    $r = $c->query("SELECT COUNT(*) c FROM {$tabla} WHERE {$col} LIKE '%JEDE%'");
    $row = $r->fetch_assoc();
    echo "  {$tabla}.{$col}: {$row['c']} ocurrencia(s) restante(s)\n";
}

// Mostrar nuevos valores
echo "\nDESPUES del fix:\n";
foreach ($snapshots as $s) {
    $r = $c->query("SELECT {$s['col']} AS cargo FROM {$s['tabla']} WHERE {$s['pk']} = " . (int)$s['id']);
    $row = $r->fetch_assoc();
    echo "  {$s['tabla']} id={$s['id']}: [{$s['cargo_antes']}] -> [{$row['cargo']}]\n";
}

$c->close();
echo "\nOK.\n";
