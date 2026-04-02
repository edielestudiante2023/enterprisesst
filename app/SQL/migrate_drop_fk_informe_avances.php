<?php

/**
 * Migración: Eliminar FK del consultor en tbl_informe_avances
 *
 * Permite que id_consultor sea NULL (para informes generados por API sin sesión).
 *
 * Ejecutar: php app/SQL/migrate_drop_fk_informe_avances.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$app = \Config\Services::codeigniter();
$app->initialize();

$db = \Config\Database::connect();

echo "=== Migración: Drop FK consultor en tbl_informe_avances ===\n\n";

try {
    // Verificar si existe la FK
    $result = $db->query("
        SELECT CONSTRAINT_NAME
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'tbl_informe_avances'
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
          AND CONSTRAINT_NAME LIKE '%consultor%'
    ")->getResultArray();

    if (empty($result)) {
        echo "No se encontró FK de consultor. Nada que hacer.\n";
    } else {
        foreach ($result as $fk) {
            $fkName = $fk['CONSTRAINT_NAME'];
            echo "Eliminando FK: {$fkName}... ";
            $db->query("ALTER TABLE tbl_informe_avances DROP FOREIGN KEY {$fkName}");
            echo "OK\n";
        }
    }

    // Asegurar que id_consultor permite NULL
    echo "Asegurando que id_consultor permite NULL... ";
    $db->query("ALTER TABLE tbl_informe_avances MODIFY COLUMN id_consultor INT DEFAULT NULL");
    echo "OK\n";

    echo "\n=== Migración completada ===\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
