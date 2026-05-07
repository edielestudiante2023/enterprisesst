<?php
/**
 * DIAGNOSTICO (READ-ONLY): Dimensionar el problema de multi-contexto.
 *
 * Identifica:
 *   1) Personas con tipo_usuario='miembro' que pertenecen a 2+ comites (mezcla de comites)
 *   2) Personas con email duplicado en tbl_usuarios con distinto tipo_usuario (cuentas duplicadas)
 *   3) Personas con tipo_usuario='client' que ALSO aparecen en tbl_comite_miembros (cliente que es miembro)
 *   4) Distribucion por cliente (especialmente British)
 *
 * Uso:
 *   php scripts/diag_contextos_usuarios.php             # local
 *   php scripts/diag_contextos_usuarios.php --prod      # produccion (req env vars)
 */

$isProd = in_array('--prod', $argv ?? [], true);
echo "=== " . ($isProd ? 'PRODUCCION' : 'LOCAL') . " | READ-ONLY ===\n\n";

if ($isProd) {
    $host = getenv('DB_PROD_HOST') ?: '';
    $user = getenv('DB_PROD_USER') ?: '';
    $pass = getenv('DB_PROD_PASS') ?: '';
    $port = (int)(getenv('DB_PROD_PORT') ?: 25060);
    $db   = getenv('DB_PROD_NAME') ?: 'empresas_sst';
    if ($host === '' || $user === '' || $pass === '') {
        echo "ERROR: faltan variables de entorno DB_PROD_*\n"; exit(1);
    }
    $conn = mysqli_init();
    mysqli_ssl_set($conn, null, null, null, null, null);
    $ok = @mysqli_real_connect($conn, $host, $user, $pass, $db, $port, null, MYSQLI_CLIENT_SSL);
    if (!$ok) { echo "ERROR conexion prod: " . mysqli_connect_error() . "\n"; exit(1); }
} else {
    $conn = new mysqli('localhost', 'root', '', 'empresas_sst');
    if ($conn->connect_error) { echo "ERROR conexion local: " . $conn->connect_error . "\n"; exit(1); }
}
$conn->set_charset('utf8mb4');

// ============================================================
// 1) Conteos generales
// ============================================================
echo "--- 1) CONTEOS GENERALES ---\n";
try {
    $r = $conn->query("SELECT tipo_usuario, COUNT(*) c FROM tbl_usuarios GROUP BY tipo_usuario ORDER BY c DESC");
    while ($row = $r->fetch_assoc()) {
        printf("  %-20s %s\n", $row['tipo_usuario'], $row['c']);
    }
} catch (\Throwable $e) { echo "  ERROR: " . $e->getMessage() . "\n"; }

try {
    $r = $conn->query("SELECT COUNT(*) c FROM tbl_comite_miembros WHERE estado='activo'");
    $row = $r->fetch_assoc();
    echo "  tbl_comite_miembros activos: {$row['c']}\n";
} catch (\Throwable $e) { echo "  ERROR: " . $e->getMessage() . "\n"; }

// ============================================================
// 2) Personas (email) con multiples membresias en comites
// ============================================================
echo "\n--- 2) MIEMBROS EN MULTIPLES COMITES (mezcla en dashboard) ---\n";
try {
    $sql = "SELECT m.email,
                   c.nombre_cliente,
                   COUNT(DISTINCT m.id_comite) AS num_comites,
                   GROUP_CONCAT(DISTINCT tc.codigo ORDER BY tc.codigo) AS tipos_comite
            FROM tbl_comite_miembros m
            JOIN tbl_comites com ON com.id_comite = m.id_comite
            JOIN tbl_tipos_comite tc ON tc.id_tipo = com.id_tipo
            JOIN tbl_clientes c ON c.id_cliente = m.id_cliente
            WHERE m.estado='activo' AND m.email IS NOT NULL AND m.email <> ''
            GROUP BY m.email, m.id_cliente, c.nombre_cliente
            HAVING num_comites > 1
            ORDER BY num_comites DESC, c.nombre_cliente, m.email";
    $r = $conn->query($sql);
    $total = 0;
    while ($row = $r->fetch_assoc()) {
        $total++;
        echo "  [{$row['nombre_cliente']}] {$row['email']} en {$row['num_comites']} comites: {$row['tipos_comite']}\n";
    }
    echo "  TOTAL personas con 2+ comites: {$total}\n";
} catch (\Throwable $e) { echo "  ERROR: " . $e->getMessage() . "\n"; }

// ============================================================
// 3) Emails duplicados en tbl_usuarios (cuentas con mismo email pero distinto tipo)
// ============================================================
echo "\n--- 3) CUENTAS DUPLICADAS (mismo email, distinto tipo_usuario) ---\n";
try {
    $sql = "SELECT MIN(email) AS email,
                   COUNT(*) AS num_cuentas,
                   GROUP_CONCAT(tipo_usuario ORDER BY tipo_usuario) AS tipos,
                   GROUP_CONCAT(id_usuario ORDER BY id_usuario) AS ids
            FROM tbl_usuarios
            WHERE email IS NOT NULL AND email <> ''
            GROUP BY LOWER(TRIM(email))
            HAVING num_cuentas > 1
            ORDER BY num_cuentas DESC";
    $r = $conn->query($sql);
    $total = 0;
    while ($row = $r->fetch_assoc()) {
        $total++;
        echo "  {$row['email']} | x{$row['num_cuentas']} | tipos=[{$row['tipos']}] | ids=[{$row['ids']}]\n";
    }
    echo "  TOTAL emails con multiples cuentas: {$total}\n";
} catch (\Throwable $e) { echo "  ERROR: " . $e->getMessage() . "\n"; }

// ============================================================
// 4) Personas tipo_usuario='client' que TAMBIEN son miembros de algun comite
// ============================================================
echo "\n--- 4) USUARIOS tipo='client' QUE TAMBIEN SON MIEMBROS DE COMITE ---\n";
echo "    (estos son los que necesitan el selector de contexto)\n";
try {
    $sql = "SELECT u.email,
                   u.nombre_completo,
                   c.nombre_cliente,
                   COUNT(DISTINCT m.id_comite) AS num_comites,
                   GROUP_CONCAT(DISTINCT tc.codigo ORDER BY tc.codigo) AS tipos_comite
            FROM tbl_usuarios u
            JOIN tbl_clientes c ON c.id_cliente = u.id_entidad
            JOIN tbl_comite_miembros m
                ON LOWER(TRIM(m.email)) = LOWER(TRIM(u.email))
                AND m.id_cliente = u.id_entidad
                AND m.estado = 'activo'
            JOIN tbl_comites com ON com.id_comite = m.id_comite
            JOIN tbl_tipos_comite tc ON tc.id_tipo = com.id_tipo
            WHERE u.tipo_usuario = 'client'
              AND u.estado = 'activo'
            GROUP BY u.id_usuario, u.email, u.nombre_completo, c.nombre_cliente
            ORDER BY c.nombre_cliente, u.email";
    $r = $conn->query($sql);
    $total = 0;
    while ($row = $r->fetch_assoc()) {
        $total++;
        echo "  [{$row['nombre_cliente']}] {$row['email']} ({$row['nombre_completo']}) - {$row['num_comites']} comites: {$row['tipos_comite']}\n";
    }
    echo "  TOTAL clientes que son tambien miembros: {$total}\n";
} catch (\Throwable $e) { echo "  ERROR: " . $e->getMessage() . "\n"; }

// ============================================================
// 5) Distribucion de comites por cliente (especialmente British)
// ============================================================
echo "\n--- 5) DISTRIBUCION DE COMITES POR CLIENTE (top 15) ---\n";
try {
    $sql = "SELECT c.nombre_cliente,
                   COUNT(DISTINCT com.id_comite) AS num_comites,
                   GROUP_CONCAT(DISTINCT tc.codigo ORDER BY tc.codigo) AS tipos,
                   COUNT(DISTINCT m.email) AS num_miembros_unicos,
                   COUNT(m.id_miembro) AS num_membresias
            FROM tbl_clientes c
            JOIN tbl_comites com ON com.id_cliente = c.id_cliente AND com.estado='activo'
            JOIN tbl_tipos_comite tc ON tc.id_tipo = com.id_tipo
            LEFT JOIN tbl_comite_miembros m ON m.id_comite = com.id_comite AND m.estado='activo'
            GROUP BY c.id_cliente, c.nombre_cliente
            ORDER BY num_comites DESC, num_miembros_unicos DESC
            LIMIT 15";
    $r = $conn->query($sql);
    while ($row = $r->fetch_assoc()) {
        printf("  %-40s comites=%d (%s) | miembros_unicos=%d | total_membresias=%d\n",
            mb_substr($row['nombre_cliente'], 0, 40),
            $row['num_comites'],
            $row['tipos'],
            $row['num_miembros_unicos'],
            $row['num_membresias']);
    }
} catch (\Throwable $e) { echo "  ERROR: " . $e->getMessage() . "\n"; }

// ============================================================
// 6) Foco en British: detalle completo
// ============================================================
echo "\n--- 6) FOCO EN BRITISH (todos los matches) ---\n";
try {
    $r = $conn->query("SELECT id_cliente, nombre_cliente FROM tbl_clientes WHERE nombre_cliente LIKE '%british%' OR nombre_cliente LIKE '%BRITISH%'");
    $britishs = [];
    while ($row = $r->fetch_assoc()) {
        $britishs[] = $row;
        echo "  Cliente encontrado: id={$row['id_cliente']} - {$row['nombre_cliente']}\n";
    }

    foreach ($britishs as $b) {
        $idCliente = (int)$b['id_cliente'];
        echo "\n  --- {$b['nombre_cliente']} (id={$idCliente}) ---\n";

        // Comites
        $r2 = $conn->query("SELECT com.id_comite, tc.codigo, com.estado, COUNT(m.id_miembro) AS miembros_activos
                            FROM tbl_comites com
                            JOIN tbl_tipos_comite tc ON tc.id_tipo = com.id_tipo
                            LEFT JOIN tbl_comite_miembros m ON m.id_comite = com.id_comite AND m.estado='activo'
                            WHERE com.id_cliente = {$idCliente}
                            GROUP BY com.id_comite
                            ORDER BY tc.codigo");
        echo "  Comites:\n";
        while ($row = $r2->fetch_assoc()) {
            echo "    id_comite={$row['id_comite']} | {$row['codigo']} | estado={$row['estado']} | miembros_activos={$row['miembros_activos']}\n";
        }

        // Miembros con multi-rol
        $r3 = $conn->query("SELECT m.email, m.nombre_completo, COUNT(DISTINCT m.id_comite) AS n,
                                   GROUP_CONCAT(DISTINCT tc.codigo ORDER BY tc.codigo) AS tipos
                            FROM tbl_comite_miembros m
                            JOIN tbl_comites com ON com.id_comite = m.id_comite
                            JOIN tbl_tipos_comite tc ON tc.id_tipo = com.id_tipo
                            WHERE m.id_cliente = {$idCliente} AND m.estado='activo'
                            GROUP BY m.email, m.nombre_completo
                            HAVING n > 1
                            ORDER BY n DESC, m.email");
        echo "  Personas en MULTIPLES comites (British):\n";
        $any = false;
        while ($row = $r3->fetch_assoc()) {
            $any = true;
            echo "    {$row['email']} ({$row['nombre_completo']}) - {$row['n']} comites: {$row['tipos']}\n";
        }
        if (!$any) echo "    (ninguna)\n";

        // Cliente que es miembro
        $r4 = $conn->query("SELECT u.email, u.nombre_completo,
                                   GROUP_CONCAT(DISTINCT tc.codigo ORDER BY tc.codigo) AS comites_es_miembro
                            FROM tbl_usuarios u
                            JOIN tbl_comite_miembros m ON LOWER(TRIM(m.email))=LOWER(TRIM(u.email))
                                                       AND m.id_cliente = u.id_entidad
                                                       AND m.estado='activo'
                            JOIN tbl_comites com ON com.id_comite = m.id_comite
                            JOIN tbl_tipos_comite tc ON tc.id_tipo = com.id_tipo
                            WHERE u.tipo_usuario='client' AND u.estado='activo' AND u.id_entidad = {$idCliente}
                            GROUP BY u.id_usuario, u.email, u.nombre_completo
                            ORDER BY u.email");
        echo "  Clientes que tambien son miembros (British):\n";
        $any = false;
        while ($row = $r4->fetch_assoc()) {
            $any = true;
            echo "    {$row['email']} ({$row['nombre_completo']}) - miembro de: {$row['comites_es_miembro']}\n";
        }
        if (!$any) echo "    (ninguno)\n";
    }
} catch (\Throwable $e) { echo "  ERROR: " . $e->getMessage() . "\n"; }

// ============================================================
// 7) Verificacion: filtro MiembroFilter aplicado o no
// ============================================================
// (No es DB - se hizo en codigo, ya identificado)

$conn->close();
echo "\nOK.\n";
