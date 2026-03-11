<?php
/**
 * FIX: Agregar asesores SST externos a todos los comités de sus clientes
 * USO: php app/SQL/fix_asesor_a_comites.php [local|produccion]
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

echo "=== AGREGAR ASESORES SST A COMITES ===\n";
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

    // Buscar todos los asesores SST externos
    $asesores = $pdo->query("
        SELECT * FROM tbl_cliente_responsables_sst
        WHERE tipo_rol = 'asesor_sst_externo' AND activo = 1
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo "Asesores encontrados: " . count($asesores) . "\n\n";

    $insertados = 0;
    $yaExisten = 0;

    foreach ($asesores as $asesor) {
        echo "Asesor: {$asesor['nombre_completo']} (cliente {$asesor['id_cliente']})\n";

        // Obtener todos los comités activos del cliente
        $comites = $pdo->prepare("SELECT * FROM tbl_comites WHERE id_cliente = ? AND estado = 'activo'");
        $comites->execute([$asesor['id_cliente']]);
        $comites = $comites->fetchAll(PDO::FETCH_ASSOC);

        foreach ($comites as $comite) {
            // Verificar si ya existe
            $existe = $pdo->prepare("SELECT id_miembro FROM tbl_miembros_comite WHERE id_comite = ? AND email = ?");
            $existe->execute([$comite['id_comite'], $asesor['email']]);

            if ($existe->fetch()) {
                $yaExisten++;
                echo "  - Comite #{$comite['id_comite']}: ya existe\n";
                continue;
            }

            // Insertar
            $insert = $pdo->prepare("
                INSERT INTO tbl_miembros_comite
                (id_comite, nombres, apellidos, documento_identidad, cargo, email, telefono,
                 representacion, tipo_miembro, rol_comite, estado, fecha_ingreso, created_at)
                VALUES (?, ?, '', ?, ?, ?, ?, 'empleador', 'principal', 'asesor', 'activo', CURDATE(), NOW())
            ");
            $insert->execute([
                $comite['id_comite'],
                $asesor['nombre_completo'],
                $asesor['numero_documento'] ?? '',
                $asesor['cargo'] ?? 'Consultor SST',
                $asesor['email'],
                $asesor['telefono'] ?? ''
            ]);

            $insertados++;
            echo "  + Comite #{$comite['id_comite']}: insertado como asesor\n";
        }
    }

    echo "\n>> Insertados: $insertados | Ya existian: $yaExisten\n";
    echo "\n=== COMPLETADO ===\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
