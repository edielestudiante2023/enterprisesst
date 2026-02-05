<?php
/**
 * Sincronizar representantes del empleador a PRODUCCION
 */

$idProceso = 1;
$idCliente = 18;

$prod = [
    'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port' => 25060,
    'user' => 'cycloid_userdb',
    'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
    'dbname' => 'empresas_sst'
];

$representantes = [
    ['Gerente General', 'Roberto Carlos', 'Mendez Villanueva', '80123456', 'principal'],
    ['Director de RRHH', 'Patricia Elena', 'Suarez Montoya', '80234567', 'principal'],
    ['Director Financiero', 'Andres Felipe', 'Gutierrez Parra', '80345678', 'principal'],
    ['Director de Operaciones', 'Carolina', 'Betancourt Rios', '80456789', 'principal'],
    ['Jefe de Produccion', 'Luis Fernando', 'Cardona Mesa', '80567890', 'suplente'],
    ['Jefe de Calidad', 'Diana Marcela', 'Ospina Velez', '80678901', 'suplente'],
    ['Jefe Administrativo', 'Jorge Eduardo', 'Aristizabal Gomez', '80789012', 'suplente'],
    ['Coordinador SST', 'Sandra Patricia', 'Valencia Duque', '80890123', 'suplente'],
];

echo "Sincronizando a PRODUCCION...\n";

try {
    $dsn = "mysql:host={$prod['host']};port={$prod['port']};dbname={$prod['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $prod['user'], $prod['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ]);

    echo "Conectado a produccion\n";

    // Verificar si ya existen
    $existentes = $pdo->query("SELECT COUNT(*) FROM tbl_candidatos_comite WHERE id_proceso = $idProceso AND representacion = 'empleador'")->fetchColumn();

    if ($existentes > 0) {
        echo "Ya hay $existentes representantes en produccion\n";
    } else {
        $stmt = $pdo->prepare("INSERT INTO tbl_candidatos_comite
            (id_proceso, id_cliente, documento_identidad, nombres, apellidos, cargo, representacion, tipo_plaza, estado, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'empleador', ?, 'designado', NOW())");

        foreach ($representantes as $r) {
            $stmt->execute([$idProceso, $idCliente, $r[3], $r[1], $r[2], $r[0], $r[4]]);
            echo "OK: {$r[1]} {$r[2]}\n";
        }
        echo "\n" . count($representantes) . " representantes agregados a PRODUCCION\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
