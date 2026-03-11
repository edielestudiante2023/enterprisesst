<?php
/**
 * FIX: Agregar 'asesor' al ENUM tipo_asistente en tbl_acta_asistentes
 * Para permitir registrar al Consultor SST como asistente/firmante en las actas
 *
 * USO: php app/SQL/fix_enum_tipo_asistente_asesor.php [local|produccion]
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

echo "=== FIX ENUM: Agregar 'asesor' a tipo_asistente ===\n";
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

    // Verificar ENUM actual
    $col = $pdo->query("SHOW COLUMNS FROM tbl_acta_asistentes LIKE 'tipo_asistente'")->fetch(PDO::FETCH_ASSOC);
    echo "ENUM actual: {$col['Type']}\n\n";

    if (strpos($col['Type'], "'asesor'") !== false) {
        echo "El valor 'asesor' ya existe en el ENUM. No se requiere cambio.\n";
    } else {
        $sql = "ALTER TABLE tbl_acta_asistentes MODIFY COLUMN tipo_asistente ENUM(
            'miembro',
            'invitado',
            'ausente_justificado',
            'ausente',
            'asesor'
        ) NOT NULL DEFAULT 'miembro'";

        echo "Ejecutando ALTER TABLE...\n";
        $pdo->exec($sql);
        echo "ENUM actualizado OK\n\n";

        // Verificar
        $col = $pdo->query("SHOW COLUMNS FROM tbl_acta_asistentes LIKE 'tipo_asistente'")->fetch(PDO::FETCH_ASSOC);
        echo "Nuevo ENUM: {$col['Type']}\n";
    }

    echo "\n=== COMPLETADO ===\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
