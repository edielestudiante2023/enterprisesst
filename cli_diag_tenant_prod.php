<?php
/**
 * DIAGNOSTICO multi-tenant (solo SELECT, no modifica nada).
 *
 * Uso (desde la raiz del proyecto):
 *   php cli_diag_tenant_prod.php
 *
 * Credenciales por env (recomendado) o hardcoded abajo si las pegas tu mismo.
 */

$host = getenv('DB_HOST') ?: 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
$user = getenv('DB_USER') ?: 'cycloid_userdb';
$pass = getenv('DB_PASS') ?: 'AVNS_MR2SLvzRh3i_7o9fEHN';
$db   = getenv('DB_NAME') ?: 'empresas_sst';
$port = (int)(getenv('DB_PORT') ?: 25060);

$mysqli = mysqli_init();
$mysqli->ssl_set(null, null, null, null, null);
$ok = @$mysqli->real_connect(
    $host, $user, $pass, $db, $port, null,
    MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT
);
if (!$ok) {
    fwrite(STDERR, "[CONNECT] " . mysqli_connect_error() . "\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');
echo "Conectado: " . $mysqli->host_info . "\n";
echo str_repeat('=', 70) . "\n\n";

function q(mysqli $m, string $title, string $sql) {
    echo "==> $title\n";
    echo "SQL: " . trim(preg_replace('/\s+/', ' ', $sql)) . "\n";
    try {
        $res = $m->query($sql);
        if (!$res) { echo "ERROR: " . $m->error . "\n\n"; return; }
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        if (!$rows) { echo "(0 filas)\n\n"; return; }
        $cols = array_keys($rows[0]);
        echo implode(' | ', $cols) . "\n";
        echo str_repeat('-', 70) . "\n";
        foreach ($rows as $r) {
            echo implode(' | ', array_map(fn($v)=> $v === null ? 'NULL' : (string)$v, $r)) . "\n";
        }
        echo "\n";
    } catch (\Throwable $e) {
        echo "EXCEPTION: " . $e->getMessage() . "\n\n";
    }
}

q($mysqli, "1) Consultor 17 (creador de las 4 actas)", "
    SELECT c.id_consultor, c.nombre_consultor, c.id_empresa_consultora,
           e.razon_social AS empresa
    FROM tbl_consultor c
    LEFT JOIN tbl_empresa_consultora e ON e.id_empresa_consultora = c.id_empresa_consultora
    WHERE c.id_consultor = 17
");

q($mysqli, "2) Clientes 19 y 20 con su consultor + empresa", "
    SELECT cli.id_cliente, cli.nombre_cliente, cli.id_consultor,
           c.nombre_consultor, c.id_empresa_consultora, e.razon_social AS empresa
    FROM tbl_clientes cli
    LEFT JOIN tbl_consultor c ON c.id_consultor = cli.id_consultor
    LEFT JOIN tbl_empresa_consultora e ON e.id_empresa_consultora = c.id_empresa_consultora
    WHERE cli.id_cliente IN (19, 20)
");

q($mysqli, "3) Clientes huerfanos o de otra empresa", "
    SELECT cli.id_cliente, cli.nombre_cliente, cli.id_consultor,
           c.nombre_consultor, e.razon_social AS empresa_actual
    FROM tbl_clientes cli
    LEFT JOIN tbl_consultor c ON c.id_consultor = cli.id_consultor
    LEFT JOIN tbl_empresa_consultora e ON e.id_empresa_consultora = c.id_empresa_consultora
    WHERE cli.id_consultor IS NULL
       OR e.razon_social IS NULL
       OR e.razon_social NOT LIKE '%Cycloid%'
");

q($mysqli, "4) Empresa consultora 'Cycloid'", "
    SELECT id_empresa_consultora, razon_social, estado
    FROM tbl_empresa_consultora
    WHERE razon_social LIKE '%Cycloid%'
");

$mysqli->close();
