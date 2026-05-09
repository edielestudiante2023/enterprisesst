<?php
/**
 * Script para agregar tipo de documento: Certificacion de No Sustancias Cancerigenas
 * Estandar: 4.1.3 (documento complementario)
 *
 * Solo inserta en tbl_doc_plantillas y tbl_doc_plantilla_carpeta
 * (no usa IA, no necesita tbl_doc_tipo_configuracion ni secciones_config)
 *
 * Replica el patron de agregar_certificacion_no_alto_riesgo.php (1.1.5).
 *
 * Ejecutar: php app/SQL/agregar_certificacion_no_sustancias_cancerigenas.php
 *
 * Orden: LOCAL primero. Solo si LOCAL OK, ejecuta PRODUCCION.
 */

$conexiones = [
    'local' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl'      => false
    ],
    'produccion' => [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_MR2SLvzRh3i_7o9fEHN',
        'ssl'      => true
    ]
];

function ejecutar(string $nombre, array $config): bool
{
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "EJECUTANDO EN: $nombre\n";
    echo str_repeat("=", 60) . "\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "Conectado a {$config['host']}\n\n";

        echo "1. Insertando plantilla 'CERT-NSC'... ";
        $pdo->exec("
            INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
            VALUES (1, 'Certificacion de No Sustancias Cancerigenas', 'CERT-NSC', 'certificacion_no_sustancias_cancerigenas', '001', 1)
            ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)
        ");
        echo "OK\n";

        echo "2. Mapeando 'CERT-NSC' a carpeta 4.1.3... ";
        $pdo->exec("
            INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
            VALUES ('CERT-NSC', '4.1.3')
            ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta)
        ");
        echo "OK\n";

        // Verificacion
        echo "\nVerificacion:\n";
        $row = $pdo->query("SELECT * FROM tbl_doc_plantillas WHERE tipo_documento = 'certificacion_no_sustancias_cancerigenas'")->fetch(PDO::FETCH_ASSOC);
        echo "  - Plantilla: " . ($row ? $row['nombre'] . " (codigo: " . $row['codigo_sugerido'] . ")" : "NO ENCONTRADA") . "\n";
        $row2 = $pdo->query("SELECT * FROM tbl_doc_plantilla_carpeta WHERE codigo_plantilla = 'CERT-NSC'")->fetch(PDO::FETCH_ASSOC);
        echo "  - Carpeta: " . ($row2 ? $row2['codigo_carpeta'] : "NO MAPEADA") . "\n";

        echo "\n[OK] 'certificacion_no_sustancias_cancerigenas' creada en $nombre\n";
        return true;

    } catch (PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "\n";
echo "========================================================\n";
echo "  CREAR: Certificacion de No Sustancias Cancerigenas\n";
echo "  Estandar: 4.1.3 | Codigo: CERT-NSC\n";
echo "========================================================\n";

$resultLocal = ejecutar('LOCAL', $conexiones['local']);

$resultProd = false;
if ($resultLocal) {
    $resultProd = ejecutar('PRODUCCION', $conexiones['produccion']);
} else {
    echo "\n[SKIP] PRODUCCION omitida porque LOCAL fallo\n";
}

echo "\n========================================================\n";
echo "RESUMEN\n";
echo "========================================================\n";
echo "LOCAL:      " . ($resultLocal ? "[OK]" : "[FALLO]") . "\n";
echo "PRODUCCION: " . ($resultProd ? "[OK]" : "[FALLO o SKIP]") . "\n";
echo "========================================================\n";
