<?php
/**
 * Lista TODAS las personas con multiples contextos (2+, 3+, 4+) en produccion.
 *
 * Un "contexto" es: ser cliente de una empresa, o ser miembro activo de un comite.
 * El total se calcula sumando ambas fuentes.
 *
 * Uso:
 *   php scripts/listar_personas_multi_rol.php             # local
 *   php scripts/listar_personas_multi_rol.php --prod      # produccion (req env vars)
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
        echo "ERROR: faltan env vars DB_PROD_*\n"; exit(1);
    }
    $conn = mysqli_init();
    mysqli_ssl_set($conn, null, null, null, null, null);
    if (!@mysqli_real_connect($conn, $host, $user, $pass, $db, $port, null, MYSQLI_CLIENT_SSL)) {
        echo "ERROR conexion: " . mysqli_connect_error() . "\n"; exit(1);
    }
} else {
    $conn = new mysqli('localhost', 'root', '', 'empresas_sst');
    if ($conn->connect_error) { echo "ERROR conexion local: " . $conn->connect_error . "\n"; exit(1); }
}
$conn->set_charset('utf8mb4');

/*
 * Estrategia:
 * 1) Cargar todos los usuarios tipo='client' (cada uno = 1 contexto cliente).
 * 2) Cargar todas las membresias activas de tbl_comite_miembros (cada una = 1 contexto miembro).
 * 3) Agrupar por email (lower-trim) y combinar.
 */

$personas = []; // email_norm => ['email_real','nombre','contextos' => []]

// 1) Contextos cliente
$sql1 = "SELECT u.id_usuario, u.email, u.nombre_completo, c.id_cliente, c.nombre_cliente
         FROM tbl_usuarios u
         INNER JOIN tbl_clientes c ON c.id_cliente = u.id_entidad
         WHERE u.tipo_usuario = 'client' AND u.estado = 'activo'";
$r = $conn->query($sql1);
while ($row = $r->fetch_assoc()) {
    $key = strtolower(trim($row['email']));
    if (!isset($personas[$key])) {
        $personas[$key] = [
            'email'          => $row['email'],
            'nombre'         => $row['nombre_completo'] ?? '',
            'contextos'      => [],
        ];
    }
    $personas[$key]['contextos'][] = [
        'tipo'           => 'cliente',
        'empresa'        => $row['nombre_cliente'],
        'codigo_comite'  => null,
        'rol_comite'     => null,
    ];
}

// 2) Contextos miembro (todas las membresias activas)
$sql2 = "SELECT m.email, m.nombre_completo, m.rol_comite, m.id_cliente, m.id_comite,
                tc.codigo, c.nombre_cliente
         FROM tbl_comite_miembros m
         INNER JOIN tbl_comites com ON com.id_comite = m.id_comite
         INNER JOIN tbl_tipos_comite tc ON tc.id_tipo = com.id_tipo
         INNER JOIN tbl_clientes c ON c.id_cliente = m.id_cliente
         WHERE m.estado = 'activo' AND com.estado = 'activo'
           AND m.email IS NOT NULL AND m.email <> ''";
$r = $conn->query($sql2);
while ($row = $r->fetch_assoc()) {
    $key = strtolower(trim($row['email']));
    if (!isset($personas[$key])) {
        $personas[$key] = [
            'email'     => $row['email'],
            'nombre'    => $row['nombre_completo'] ?? '',
            'contextos' => [],
        ];
    }
    if (empty($personas[$key]['nombre']) && !empty($row['nombre_completo'])) {
        $personas[$key]['nombre'] = $row['nombre_completo'];
    }
    $personas[$key]['contextos'][] = [
        'tipo'          => 'miembro',
        'empresa'       => $row['nombre_cliente'],
        'codigo_comite' => $row['codigo'],
        'rol_comite'    => $row['rol_comite'],
    ];
}

// Filtrar solo los que tienen 2+ contextos
$multi = array_filter($personas, fn($p) => count($p['contextos']) >= 2);

// Ordenar por cantidad de contextos descendente, luego por nombre
uasort($multi, function($a, $b) {
    $diff = count($b['contextos']) - count($a['contextos']);
    if ($diff !== 0) return $diff;
    return strcmp($a['nombre'], $b['nombre']);
});

// Agrupar por cantidad de contextos para presentar
$byCount = [];
foreach ($multi as $p) {
    $c = count($p['contextos']);
    $byCount[$c][] = $p;
}
krsort($byCount);

echo "PERSONAS CON MULTIPLES ROLES EN PRODUCCION\n";
echo str_repeat('=', 80) . "\n\n";

$totalMulti = count($multi);
echo "TOTAL personas con 2+ contextos: {$totalMulti}\n\n";

foreach ($byCount as $count => $personasGrupo) {
    $tagCount = match($count) {
        2 => 'DOBLE',
        3 => 'TRIPLE',
        4 => 'CUADRUPLE',
        default => "{$count} CONTEXTOS",
    };
    echo str_repeat('-', 80) . "\n";
    echo "ROL {$tagCount} (x{$count}) — " . count($personasGrupo) . " persona(s)\n";
    echo str_repeat('-', 80) . "\n";

    foreach ($personasGrupo as $p) {
        echo sprintf("  %s  <%s>\n", $p['nombre'] ?: '(sin nombre)', $p['email']);
        foreach ($p['contextos'] as $ctx) {
            if ($ctx['tipo'] === 'cliente') {
                echo sprintf("    + Cliente               | %s\n", $ctx['empresa']);
            } else {
                $rol = $ctx['rol_comite'] ? " (rol: {$ctx['rol_comite']})" : '';
                echo sprintf("    + Miembro %-12s | %s%s\n", $ctx['codigo_comite'], $ctx['empresa'], $rol);
            }
        }
        echo "\n";
    }
}

if ($totalMulti === 0) {
    echo "  (Sin personas multi-rol)\n";
}

$conn->close();
echo "OK.\n";
