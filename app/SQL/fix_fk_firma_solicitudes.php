<?php
/**
 * Fix: Eliminar FK constraint que apunta a tbl_doc_documentos (tabla vieja)
 * El id_documento ahora referencia tbl_documentos_sst (sin FK formal)
 */

$conexiones = [
    'LOCAL' => [
        'dsn' => 'mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4',
        'user' => 'root',
        'pass' => '',
        'options' => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    ],
    'PRODUCCION' => [
        'dsn' => 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
        'user' => 'cycloid_userdb',
        'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
        ]
    ]
];

foreach ($conexiones as $entorno => $config) {
    echo "--- {$entorno} ---\n";

    try {
        $pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);
        echo "  Conectado OK\n";

        // Buscar todas las FK constraints en tbl_doc_firma_solicitudes
        $stmt = $pdo->query("
            SELECT CONSTRAINT_NAME, TABLE_NAME, REFERENCED_TABLE_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'tbl_doc_firma_solicitudes'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($fks)) {
            echo "  No se encontraron FK constraints\n";
        } else {
            foreach ($fks as $fk) {
                echo "  FK: {$fk['CONSTRAINT_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}\n";

                // Eliminar FK que apunta a tbl_doc_documentos
                if ($fk['REFERENCED_TABLE_NAME'] === 'tbl_doc_documentos') {
                    $pdo->exec("ALTER TABLE `tbl_doc_firma_solicitudes` DROP FOREIGN KEY `{$fk['CONSTRAINT_NAME']}`");
                    echo "    ELIMINADA (apuntaba a tabla vieja tbl_doc_documentos)\n";
                }
            }
        }

        echo "  OK\n\n";

    } catch (PDOException $e) {
        echo "  ERROR: " . $e->getMessage() . "\n\n";
    }
}

echo "Fix completado.\n";
