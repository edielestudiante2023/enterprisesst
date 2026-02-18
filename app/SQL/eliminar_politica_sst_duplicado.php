<?php
/**
 * Elimina el tipo duplicado 'politica_sst' de tbl_doc_tipo_configuracion.
 * El tipo válido es 'politica_sst_general' (tiene 7 secciones, clase PHP y prompts).
 * Ejecutar: php app/SQL/eliminar_politica_sst_duplicado.php
 */

$conexiones = [
    'local' => [
        'host' => 'localhost', 'port' => 3306,
        'database' => 'empresas_sst', 'username' => 'root', 'password' => '', 'ssl' => false
    ],
    'produccion' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com', 'port' => 25060,
        'database' => 'empresas_sst', 'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG', 'ssl' => true
    ]
];

$localExito = false;

foreach ($conexiones as $entorno => $config) {
    echo "\n=== Ejecutando en $entorno ===\n";

    if ($entorno === 'produccion' && !$localExito) {
        echo "  [SKIP] No se ejecuta en produccion porque LOCAL fallo\n";
        continue;
    }

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "  [OK] Conexion establecida\n";

        // Verificar que no haya documentos reales usando este tipo
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM tbl_documentos_sst WHERE tipo_documento = 'politica_sst'");
        $cnt = $stmt->fetch()['cnt'];
        if ($cnt > 0) {
            echo "  [ABORT] Hay {$cnt} documentos con tipo_documento='politica_sst'. No se puede eliminar.\n";
            if ($entorno === 'local') $localExito = false;
            continue;
        }
        echo "  [OK] Sin documentos reales — seguro eliminar\n";

        // Eliminar secciones asociadas (aunque son 0, por limpieza)
        $pdo->exec("DELETE dsc FROM tbl_doc_secciones_config dsc
                    INNER JOIN tbl_doc_tipo_configuracion dtc ON dsc.id_tipo_config = dtc.id_tipo_config
                    WHERE dtc.tipo_documento = 'politica_sst'");
        echo "  [OK] Secciones eliminadas\n";

        // Eliminar firmantes asociados
        $pdo->exec("DELETE dfc FROM tbl_doc_firmantes_config dfc
                    INNER JOIN tbl_doc_tipo_configuracion dtc ON dfc.id_tipo_config = dtc.id_tipo_config
                    WHERE dtc.tipo_documento = 'politica_sst'");
        echo "  [OK] Firmantes eliminados\n";

        // Eliminar el tipo
        $affected = $pdo->exec("DELETE FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'politica_sst'");
        echo "  [OK] Tipo eliminado (filas afectadas: {$affected})\n";

        // Verificar que politica_sst_general sigue intacto
        $stmt = $pdo->query("SELECT id_tipo_config, nombre, flujo FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'politica_sst_general'");
        $general = $stmt->fetch();
        if ($general) {
            echo "  [OK] politica_sst_general intacto (ID: {$general['id_tipo_config']}, flujo: {$general['flujo']})\n";
        }

        if ($entorno === 'local') $localExito = true;

    } catch (PDOException $e) {
        echo "  [ERROR] " . $e->getMessage() . "\n";
        if ($entorno === 'local') $localExito = false;
    }
}

echo "\n=== Proceso completado ===\n";
