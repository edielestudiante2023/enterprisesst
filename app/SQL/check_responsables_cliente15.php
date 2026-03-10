<?php
/**
 * Diagnostico: ver responsables SST del cliente 15
 * USO: php app/SQL/check_responsables_cliente15.php produccion
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

echo "=== DIAGNOSTICO RESPONSABLES SST - CLIENTE 15 ===\n";
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

    // 1. Info del cliente
    $stmt = $pdo->query("SELECT * FROM tbl_clientes WHERE id_cliente = 15");
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    $nombreCliente = $cliente['razon_social'] ?? $cliente['nombre_empresa'] ?? $cliente['nombre'] ?? 'ID 15';
    echo "Cliente: $nombreCliente\n";
    echo "Trabajadores: " . ($cliente['total_trabajadores'] ?? $cliente['numero_trabajadores'] ?? '?') . "\n\n";

    // 2. Tabla de responsables
    $stmt = $pdo->query("SHOW TABLES LIKE '%responsable%'");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas responsables: " . implode(', ', $tablas) . "\n\n";

    // 3. Todos los responsables del cliente 15
    foreach ($tablas as $tabla) {
        echo "--- $tabla ---\n";
        $cols = $pdo->query("SHOW COLUMNS FROM `$tabla`")->fetchAll(PDO::FETCH_COLUMN);
        echo "Columnas: " . implode(', ', $cols) . "\n";

        // Buscar columna de cliente
        $colCliente = null;
        foreach (['id_cliente', 'cliente_id'] as $c) {
            if (in_array($c, $cols)) {
                $colCliente = $c;
                break;
            }
        }

        if ($colCliente) {
            $stmt = $pdo->prepare("SELECT * FROM `$tabla` WHERE `$colCliente` = 15");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "Registros cliente 15: " . count($rows) . "\n";
            foreach ($rows as $r) {
                // Mostrar campos clave
                $nombre = $r['nombre'] ?? $r['nombres'] ?? $r['nombre_completo'] ?? '?';
                $rol = $r['rol'] ?? $r['tipo_responsable'] ?? $r['cargo_sst'] ?? $r['tipo'] ?? '?';
                $estado = $r['estado'] ?? $r['activo'] ?? '?';
                echo "  - [$rol] $nombre (estado: $estado)\n";
            }
        } else {
            echo "No tiene columna id_cliente\n";
        }
        echo "\n";
    }

    // 4. Buscar tambien en comites
    echo "--- COMITES CONVIVENCIA ---\n";
    $stmt = $pdo->query("SHOW TABLES LIKE '%comite%convivencia%'");
    $tablas2 = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tablas2)) {
        $stmt = $pdo->query("SHOW TABLES LIKE '%convivencia%'");
        $tablas2 = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    echo "Tablas convivencia: " . (empty($tablas2) ? 'NINGUNA' : implode(', ', $tablas2)) . "\n";

    // 5. Buscar numero de estandares del cliente
    echo "\n--- ESTANDARES CLIENTE 15 ---\n";
    $stmt = $pdo->query("SHOW TABLES LIKE '%estandar%'");
    $tablasEst = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas estandares: " . implode(', ', $tablasEst) . "\n";

    // total_trabajadores o numero_trabajadores
    $stmt = $pdo->query("SELECT * FROM tbl_clientes WHERE id_cliente = 15");
    $cl = $stmt->fetch(PDO::FETCH_ASSOC);
    $trabajadores = $cl['total_trabajadores'] ?? $cl['numero_trabajadores'] ?? '?';
    echo "Trabajadores: $trabajadores\n";

    // Buscar estandares asignados
    foreach ($tablasEst as $te) {
        $colsE = $pdo->query("SHOW COLUMNS FROM `$te`")->fetchAll(PDO::FETCH_COLUMN);
        foreach (['id_cliente', 'cliente_id'] as $cc) {
            if (in_array($cc, $colsE)) {
                $count = $pdo->query("SELECT COUNT(*) FROM `$te` WHERE `$cc` = 15")->fetchColumn();
                echo "$te: $count registros\n";
                break;
            }
        }
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
