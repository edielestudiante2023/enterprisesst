<?php
/**
 * Fix: Eliminar FK fk_firma_version que apunta a tbl_doc_versiones (tabla vieja)
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

        // Eliminar fk_firma_version
        try {
            $pdo->exec("ALTER TABLE `tbl_doc_firma_solicitudes` DROP FOREIGN KEY `fk_firma_version`");
            echo "  FK fk_firma_version ELIMINADA\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), "check that column/key exists") !== false || strpos($e->getMessage(), "Can't DROP") !== false) {
                echo "  FK fk_firma_version no existe (ya fue eliminada)\n";
            } else {
                echo "  Error: " . $e->getMessage() . "\n";
            }
        }

        // Verificar que no quedan FKs a tablas viejas
        $stmt = $pdo->query("
            SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'tbl_doc_firma_solicitudes'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($fks)) {
            echo "  Sin FK constraints restantes - OK\n";
        } else {
            foreach ($fks as $fk) {
                echo "  FK restante: {$fk['CONSTRAINT_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}\n";
            }
        }

    } catch (PDOException $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
echo "Completado.\n";
