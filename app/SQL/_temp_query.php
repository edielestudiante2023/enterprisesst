<?php
$pdo = new PDO(
    'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
    'cycloid_userdb',
    'AVNS_iDypWizlpMRwHIORJGG',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false]
);

// Agregar campo clausula_primera_objeto a tbl_contratos
echo "=== Agregando clausula_primera_objeto ===\n";
try {
    $pdo->exec("ALTER TABLE tbl_contratos ADD COLUMN clausula_primera_objeto TEXT NULL AFTER clausula_cuarta_duracion");
    echo "OK: Campo clausula_primera_objeto agregado exitosamente\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "INFO: El campo ya existe, no se necesita agregar\n";
    } else {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

// Verificar
$stmt = $pdo->query("SHOW COLUMNS FROM tbl_contratos LIKE 'clausula_primera_objeto'");
$col = $stmt->fetch(PDO::FETCH_ASSOC);
if ($col) {
    echo "VERIFICADO: Campo existe - Type: {$col['Type']}, Null: {$col['Null']}\n";
} else {
    echo "ERROR: Campo NO encontrado despues del ALTER\n";
}
