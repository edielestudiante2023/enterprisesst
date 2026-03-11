<?php
/**
 * FIX: Cambiar usuarios de comite de 'client' a 'miembro'
 * Los usuarios de COPASST/COCOLAB del cliente 15 (Ardurra) se crearon como 'client'
 * pero solo deberian tener acceso a actas de comites (tipo 'miembro')
 *
 * USO: php app/SQL/fix_usuarios_comite_ardurra.php [local|produccion]
 */

$env = $argv[1] ?? 'local';

if ($env === 'produccion') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $user = 'cycloid_userdb';
    $pass = 'AVNS_iDypWizlpMRwHIORJGG';
    $db   = 'empresas_sst';
    $ssl  = true;
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $user = 'root';
    $pass = '';
    $db   = 'empresas_sst';
    $ssl  = false;
}

echo "=== FIX USUARIOS COMITE ARDURRA (CLIENTE 15) ===\n";
echo "Entorno: " . strtoupper($env) . "\n\n";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Conectado OK\n\n";

    // Roles que deben ser 'client' (acceso completo)
    $rolesCliente = ['representante_legal', 'responsable_sgsst'];

    // 1. Ver estado actual: responsables con usuario creado
    echo "--- ESTADO ACTUAL ---\n";
    $stmt = $pdo->query("
        SELECT r.id_responsable, r.tipo_rol, r.nombre_completo, r.email,
               u.id_usuario, u.tipo_usuario, u.estado as estado_usuario
        FROM tbl_cliente_responsables_sst r
        LEFT JOIN tbl_usuarios u ON r.email = u.email
        WHERE r.id_cliente = 15
          AND u.id_usuario IS NOT NULL
        ORDER BY r.tipo_rol
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cambiar = [];
    foreach ($rows as $r) {
        $esRolCliente = in_array($r['tipo_rol'], $rolesCliente);
        $tipoCorrecro = $esRolCliente ? 'client' : 'miembro';
        $necesitaCambio = $r['tipo_usuario'] !== $tipoCorrecro;
        $estado = $necesitaCambio ? '>> CAMBIAR A ' . $tipoCorrecro : 'OK';

        echo "  ID:{$r['id_usuario']} | {$r['tipo_usuario']} | [{$r['tipo_rol']}] {$r['nombre_completo']} ({$r['email']}) -> $estado\n";

        if ($necesitaCambio) {
            $cambiar[] = ['id_usuario' => $r['id_usuario'], 'tipo_correcto' => $tipoCorrecro, 'nombre' => $r['nombre_completo']];
        }
    }

    echo "\nTotal usuarios: " . count($rows) . " | Necesitan cambio: " . count($cambiar) . "\n\n";

    if (empty($cambiar)) {
        echo "No hay cambios necesarios.\n";
        exit(0);
    }

    // 2. Aplicar cambios
    echo "--- APLICANDO CAMBIOS ---\n";
    $update = $pdo->prepare("UPDATE tbl_usuarios SET tipo_usuario = ? WHERE id_usuario = ?");

    foreach ($cambiar as $c) {
        $update->execute([$c['tipo_correcto'], $c['id_usuario']]);
        echo "  ID:{$c['id_usuario']} ({$c['nombre']}) -> {$c['tipo_correcto']} OK\n";
    }

    echo "\n=== COMPLETADO: " . count($cambiar) . " usuarios actualizados ===\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
