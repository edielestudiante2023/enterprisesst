<?php
/**
 * Fix: plan_objetivos_metas debe tener flujo='programa_con_pta' (3 partes)
 * Estaba como 'secciones_ia' (1 parte), lo cual causaba que el SweetAlert
 * no consultara PTA ni indicadores antes de generar el documento.
 *
 * Ejecutar: php app/SQL/fix_flujo_plan_objetivos_metas.php [--produccion]
 */

$esProduccion = in_array('--produccion', $argv ?? []);

if ($esProduccion) {
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'user'     => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'database' => 'empresas_sst',
        'ssl'      => true,
        'label'    => 'PRODUCCION'
    ];
} else {
    $config = [
        'host'     => 'localhost',
        'port'     => 3306,
        'user'     => 'root',
        'password' => '',
        'database' => 'empresas_sst',
        'ssl'      => false,
        'label'    => 'LOCAL'
    ];
}

echo "=== Fix flujo plan_objetivos_metas → programa_con_pta ===\n";
echo "Entorno: {$config['label']}\n\n";

$mysqli = new mysqli($config['host'], $config['user'], $config['password'], $config['database'], $config['port']);

if ($mysqli->connect_error) {
    echo "ERROR conexion: {$mysqli->connect_error}\n";
    exit(1);
}

if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
}

// 1. Verificar estado actual
$r = $mysqli->query("SELECT tipo_documento, flujo FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'plan_objetivos_metas'");
$row = $r->fetch_assoc();

if (!$row) {
    echo "ERROR: No existe 'plan_objetivos_metas' en tbl_doc_tipo_configuracion\n";
    $mysqli->close();
    exit(1);
}

echo "Estado actual: flujo = '{$row['flujo']}'\n";

if ($row['flujo'] === 'programa_con_pta') {
    echo "Ya tiene el valor correcto. No se requiere cambio.\n";
    $mysqli->close();
    exit(0);
}

// 2. Aplicar fix
$sql = "UPDATE tbl_doc_tipo_configuracion SET flujo = 'programa_con_pta' WHERE tipo_documento = 'plan_objetivos_metas'";
echo "Ejecutando: {$sql}\n";

if ($mysqli->query($sql)) {
    echo "OK - Filas afectadas: {$mysqli->affected_rows}\n";
} else {
    echo "ERROR: {$mysqli->error}\n";
    $mysqli->close();
    exit(1);
}

// 3. Verificar
$r2 = $mysqli->query("SELECT tipo_documento, flujo FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'plan_objetivos_metas'");
$row2 = $r2->fetch_assoc();
echo "Verificacion: flujo = '{$row2['flujo']}'\n";
echo "\nCompletado en {$config['label']}.\n";

$mysqli->close();
exit(0);
