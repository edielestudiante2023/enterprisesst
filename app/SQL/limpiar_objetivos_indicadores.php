<?php
/**
 * Limpia objetivos (PTA), indicadores de objetivos y documento plan_objetivos_metas
 * para clientes 12 y 18, permitiendo regenerarlos con el código corregido.
 *
 * Ejecutar: php app/SQL/limpiar_objetivos_indicadores.php [--produccion]
 */

$esProduccion = in_array('--produccion', $argv ?? []);

if ($esProduccion) {
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'user'     => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'database' => 'empresas_sst',
        'label'    => 'PRODUCCION'
    ];
} else {
    $config = [
        'host'     => 'localhost',
        'port'     => 3306,
        'user'     => 'root',
        'password' => '',
        'database' => 'empresas_sst',
        'label'    => 'LOCAL'
    ];
}

echo "=== Limpiar Objetivos + Indicadores + Documento ===\n";
echo "Entorno: {$config['label']}\n";
echo "Clientes: 12, 18\n\n";

$db = new mysqli($config['host'], $config['user'], $config['password'], $config['database'], $config['port']);
if ($db->connect_error) {
    echo "ERROR conexion: {$db->connect_error}\n";
    exit(1);
}

$clientes = [12, 18];
$totalPTA = 0;
$totalInd = 0;
$totalDoc = 0;

foreach ($clientes as $cid) {
    echo "--- Cliente $cid ---\n";

    // 1. Borrar PTA con tipo_servicio='Objetivos SG-SST'
    $db->query("DELETE FROM tbl_pta_cliente WHERE id_cliente = $cid AND tipo_servicio = 'Objetivos SG-SST'");
    $af1 = $db->affected_rows;
    $totalPTA += $af1;
    echo "  PTA (Objetivos SG-SST): $af1 borrados\n";

    // 2. Borrar indicadores con categoria='objetivos_sgsst'
    $db->query("DELETE FROM tbl_indicadores_sst WHERE id_cliente = $cid AND categoria = 'objetivos_sgsst'");
    $af2 = $db->affected_rows;
    $totalInd += $af2;
    echo "  Indicadores (objetivos_sgsst): $af2 borrados\n";

    // 3. Borrar documento plan_objetivos_metas
    $db->query("DELETE FROM tbl_documentos_sst WHERE id_cliente = $cid AND tipo_documento = 'plan_objetivos_metas'");
    $af3 = $db->affected_rows;
    $totalDoc += $af3;
    echo "  Documento (plan_objetivos_metas): $af3 borrados\n";
}

echo "\n=== RESUMEN {$config['label']} ===\n";
echo "PTA borrados: $totalPTA\n";
echo "Indicadores borrados: $totalInd\n";
echo "Documentos borrados: $totalDoc\n";

// Verificacion
echo "\n=== VERIFICACION ===\n";
foreach ($clientes as $cid) {
    $r1 = $db->query("SELECT COUNT(*) as c FROM tbl_pta_cliente WHERE id_cliente = $cid AND tipo_servicio = 'Objetivos SG-SST'")->fetch_assoc();
    $r2 = $db->query("SELECT COUNT(*) as c FROM tbl_indicadores_sst WHERE id_cliente = $cid AND categoria = 'objetivos_sgsst'")->fetch_assoc();
    $r3 = $db->query("SELECT COUNT(*) as c FROM tbl_documentos_sst WHERE id_cliente = $cid AND tipo_documento = 'plan_objetivos_metas'")->fetch_assoc();
    echo "  Cliente $cid: PTA={$r1['c']}, Indicadores={$r2['c']}, Docs={$r3['c']}\n";

    // Confirmar que otras categorias siguen intactas
    $r4 = $db->query("SELECT COUNT(*) as c FROM tbl_indicadores_sst WHERE id_cliente = $cid AND activo = 1 AND categoria != 'objetivos_sgsst'")->fetch_assoc();
    echo "  Cliente $cid: Indicadores OTRAS categorias (intactos): {$r4['c']}\n";
}

echo "\nCompletado en {$config['label']}.\n";
$db->close();
