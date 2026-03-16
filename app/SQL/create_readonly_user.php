<?php
/**
 * create_readonly_user.php — Crea el usuario MySQL empresas_readonly
 * con GRANT SELECT solo sobre las vistas v_* y tablas catálogo.
 *
 * Uso:
 *   php create_readonly_user.php local
 *   DB_PROD_PASS="..." DB_READONLY_PASS="MiPassSegura" php create_readonly_user.php production
 *
 * INSTRUCCIÓN TAXATIVA: ejecutar LOCAL primero. Solo si OK ejecutar PRODUCTION.
 */

$env = $argv[1] ?? 'local';
if (!in_array($env, ['local', 'production'])) {
    die("Uso: php create_readonly_user.php [local|production]\n");
}

// ─── Configuración ────────────────────────────────────────────
if ($env === 'local') {
    $host        = 'localhost';
    $port        = 3306;
    $adminUser   = 'root';
    $adminPass   = '';
    $db          = 'empresas_sst';
    $ssl         = false;
    $roPass      = getenv('DB_READONLY_PASS') ?: 'EmpresasReadOnly2026!';
    $roHosts     = ['localhost', '127.0.0.1'];
} else {
    $host        = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port        = 25060;
    $adminUser   = 'cycloid_userdb';
    $adminPass   = getenv('DB_PROD_PASS');
    $db          = 'empresas_sst';
    $ssl         = true;
    $roPass      = getenv('DB_READONLY_PASS');
    $roHosts     = ['%'];

    if (!$adminPass) die("ERROR: Define DB_PROD_PASS\n");
    if (!$roPass)    die("ERROR: Define DB_READONLY_PASS\n");
}

// ─── Vistas y tablas con acceso SELECT ───────────────────────
$views = [
    'v_pta_cliente', 'v_pendientes', 'v_indicadores_sst', 'v_indicadores_mediciones',
    'v_cronog_capacitacion', 'v_documentos_sst', 'v_doc_versiones_sst',
    'v_evaluacion_inicial', 'v_cliente_estandares', 'v_reportes',
    'v_inspeccion_botiquin', 'v_inspeccion_extintores', 'v_inspeccion_locativa',
    'v_inspeccion_senalizacion', 'v_acta_visita', 'v_acc_hallazgos', 'v_acc_acciones',
    'v_acc_seguimientos', 'v_actas_comite', 'v_acta_compromisos', 'v_comite_miembros',
    'v_cliente_contexto', 'v_responsables_sst', 'v_contratos',
    'v_presupuesto', 'v_presupuesto_detalle', 'v_matrices',
    'v_vencimientos_mantenimientos', 'v_vigias', 'v_procesos_electorales',
    'v_candidatos_comite', 'v_induccion_etapas', 'v_lookerstudio',
];

// Catálogos de solo lectura que el cliente también puede necesitar
$catalogs = [
    'tbl_clientes', 'estandares', 'capacitaciones_sst', 'tbl_mantenimientos',
    'tbl_estandares_minimos', 'tbl_tipos_comite', 'tbl_marco_normativo', 'matriz_legal',
];

// ─── Conexión admin ───────────────────────────────────────────
echo "─────────────────────────────────────────\n";
echo "Ambiente : " . strtoupper($env) . "\n";
echo "Host     : {$host}:{$port}\n";
echo "─────────────────────────────────────────\n";

$conn = mysqli_init();
if ($ssl) mysqli_ssl_set($conn, null, null, null, null, null);
$connected = @mysqli_real_connect(
    $conn, $host, $adminUser, $adminPass, $db, $port,
    null, $ssl ? MYSQLI_CLIENT_SSL : 0
);
if (!$connected) die("ERROR conexión: " . mysqli_connect_error() . "\n");
echo "Conexión admin OK\n\n";

$ok = 0; $err = 0;

function runSQL(mysqli $c, string $sql, string $label): void {
    global $ok, $err;
    try {
        if (@mysqli_query($c, $sql)) {
            echo "  ✓  {$label}\n"; $ok++;
        } else {
            $e = mysqli_error($c);
            // "no such grant" al hacer REVOKE en usuario nuevo — es esperado
            if (str_contains($e, 'no such grant') || str_contains($e, 'There is no such grant')) {
                echo "  ~  {$label} (skip: {$e})\n"; $ok++;
            } else {
                echo "  ✗  {$label} — {$e}\n"; $err++;
            }
        }
    } catch (Exception $e2) {
        $msg = $e2->getMessage();
        if (str_contains($msg, 'no such grant') || str_contains($msg, 'There is no such grant')) {
            echo "  ~  {$label} (skip: nuevo usuario sin grants previos)\n"; $ok++;
        } else {
            echo "  ✗  {$label} — {$msg}\n"; $err++;
        }
    }
}

// ─── Crear usuario en cada host ───────────────────────────────
foreach ($roHosts as $roHost) {
    $escaped = mysqli_real_escape_string($conn, $roPass);

    echo "Usuario empresas_readonly@{$roHost}:\n";

    // Crear usuario (ignorar si ya existe)
    runSQL($conn,
        "CREATE USER IF NOT EXISTS 'empresas_readonly'@'{$roHost}' IDENTIFIED BY '{$escaped}'",
        "CREATE USER"
    );

    // Revocar permisos previos (DigitalOcean: solo sobre la BD, no global)
    runSQL($conn,
        "REVOKE ALL ON `{$db}`.* FROM 'empresas_readonly'@'{$roHost}'",
        "REVOKE ALL"
    );

    // Grant SELECT sobre cada vista
    foreach ($views as $view) {
        runSQL($conn,
            "GRANT SELECT ON `{$db}`.`{$view}` TO 'empresas_readonly'@'{$roHost}'",
            "GRANT SELECT ON {$view}"
        );
    }

    // Grant SELECT sobre catálogos
    foreach ($catalogs as $tbl) {
        runSQL($conn,
            "GRANT SELECT ON `{$db}`.`{$tbl}` TO 'empresas_readonly'@'{$roHost}'",
            "GRANT SELECT ON {$tbl}"
        );
    }

    runSQL($conn, "FLUSH PRIVILEGES", "FLUSH PRIVILEGES");
    echo "\n";
}

mysqli_close($conn);

// ─── Resumen ──────────────────────────────────────────────────
echo "─────────────────────────────────────────\n";
echo "Resultado [{$env}]: {$ok} OK, {$err} errores\n";

if ($err > 0) {
    echo "\n⚠  Hay errores — corregir antes de ejecutar en producción.\n";
    exit(1);
} else {
    echo "\n✓  Usuario empresas_readonly creado con permisos correctos.\n";
    if ($env === 'local') {
        echo "→  Puedes ejecutar en producción:\n";
        echo "   DB_PROD_PASS=\"...\" DB_READONLY_PASS=\"...\" php create_readonly_user.php production\n";
    }
    exit(0);
}
