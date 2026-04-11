<?php
/**
 * FIX: Agregar 'asesor_sst_externo' al ENUM tipo_rol en tbl_cliente_responsables_sst
 * Para registrar al consultor SST como asesor técnico externo en los comités
 *
 * USO: php app/SQL/fix_enum_asesor_externo.php [local|produccion]
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

echo "=== FIX ENUM: Agregar asesor_sst_externo ===\n";
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

    // ENUM completo con asesor_sst_externo
    $sql = "ALTER TABLE tbl_cliente_responsables_sst MODIFY COLUMN tipo_rol ENUM(
        'representante_legal',
        'responsable_sgsst',
        'vigia_sst',
        'vigia_sst_suplente',
        'copasst_presidente',
        'copasst_secretario',
        'copasst_representante_empleador',
        'copasst_representante_trabajadores',
        'copasst_suplente_empleador',
        'copasst_suplente_trabajadores',
        'comite_convivencia_presidente',
        'comite_convivencia_secretario',
        'comite_convivencia_representante_empleador',
        'comite_convivencia_representante_trabajadores',
        'comite_convivencia_suplente_empleador',
        'comite_convivencia_suplente_trabajadores',
        'comite_convivencia_miembro',
        'brigada_coordinador',
        'brigada_lider_evacuacion',
        'brigada_lider_primeros_auxilios',
        'brigada_lider_control_incendios',
        'asesor_sst_externo',
        'otro'
    ) NOT NULL";

    echo "Ejecutando ALTER TABLE...\n";
    $pdo->exec($sql);
    echo "ENUM actualizado OK\n\n";

    // Verificar
    $col = $pdo->query("SHOW COLUMNS FROM tbl_cliente_responsables_sst LIKE 'tipo_rol'")->fetch(PDO::FETCH_ASSOC);
    echo "Nuevo ENUM: {$col['Type']}\n\n";

    // Recrear vista con el nuevo rol
    echo "Recreando vista vw_responsables_sst_activos...\n";
    $pdo->exec("DROP VIEW IF EXISTS vw_responsables_sst_activos");
    $pdo->exec("
        CREATE VIEW vw_responsables_sst_activos AS
        SELECT r.*,
               c.nombre_cliente,
               c.nit_cliente,
               CASE r.tipo_rol
                   WHEN 'representante_legal' THEN 'Representante Legal'
                   WHEN 'responsable_sgsst' THEN 'Responsable del SG-SST'
                   WHEN 'vigia_sst' THEN 'Vigía de SST'
                   WHEN 'vigia_sst_suplente' THEN 'Vigía de SST (Suplente)'
                   WHEN 'copasst_presidente' THEN 'COPASST - Presidente'
                   WHEN 'copasst_secretario' THEN 'COPASST - Secretario'
                   WHEN 'copasst_representante_empleador' THEN 'COPASST - Representante Empleador'
                   WHEN 'copasst_representante_trabajadores' THEN 'COPASST - Representante Trabajadores'
                   WHEN 'copasst_suplente_empleador' THEN 'COPASST - Suplente Empleador'
                   WHEN 'copasst_suplente_trabajadores' THEN 'COPASST - Suplente Trabajadores'
                   WHEN 'comite_convivencia_presidente' THEN 'Comité Convivencia - Presidente'
                   WHEN 'comite_convivencia_secretario' THEN 'Comité Convivencia - Secretario'
                   WHEN 'comite_convivencia_representante_empleador' THEN 'Comité Convivencia - Representante Empleador'
                   WHEN 'comite_convivencia_representante_trabajadores' THEN 'Comité Convivencia - Representante Trabajadores'
                   WHEN 'comite_convivencia_suplente_empleador' THEN 'Comité Convivencia - Suplente Empleador'
                   WHEN 'comite_convivencia_suplente_trabajadores' THEN 'Comité Convivencia - Suplente Trabajadores'
                   WHEN 'comite_convivencia_miembro' THEN 'Comité Convivencia - Miembro'
                   WHEN 'brigada_coordinador' THEN 'Brigada - Coordinador'
                   WHEN 'brigada_lider_evacuacion' THEN 'Brigada - Líder Evacuación'
                   WHEN 'brigada_lider_primeros_auxilios' THEN 'Brigada - Líder Primeros Auxilios'
                   WHEN 'brigada_lider_control_incendios' THEN 'Brigada - Líder Control Incendios'
                   WHEN 'asesor_sst_externo' THEN 'Consultor SST Externo'
                   ELSE 'Otro'
               END AS nombre_rol
        FROM tbl_cliente_responsables_sst r
        JOIN tbl_clientes c ON r.id_cliente = c.id_cliente
        WHERE r.activo = 1
    ");
    echo "Vista recreada OK\n";

    echo "\n=== COMPLETADO ===\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
