<?php
// Quick check script - delete after use
try {
    $pdo = new PDO('mysql:host=localhost;dbname=empresas_sst', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_doc_firma%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        echo "STATUS: NO_TABLES\n";
        // Try to create them
        $sql = file_get_contents(__DIR__ . '/app/SQL/crear_tablas_firma_electronica.sql');
        $pdo->exec($sql);
        echo "CREATED: firma tables\n";

        // Also run alter contexto
        $sql2 = file_get_contents(__DIR__ . '/app/SQL/alter_contexto_firmantes.sql');
        $pdo->exec($sql2);
        echo "EXECUTED: alter contexto firmantes\n";

        // Verify
        $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_doc_firma%'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "AFTER CREATE:\n";
        foreach ($tables as $t) echo "  $t\n";
    } else {
        echo "STATUS: TABLES_EXIST\n";
        foreach ($tables as $t) {
            echo "  $t\n";
            $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='empresas_sst' AND TABLE_NAME='$t'")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($cols as $c) echo "    - $c\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
