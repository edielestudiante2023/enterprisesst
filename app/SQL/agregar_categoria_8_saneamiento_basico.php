<?php
/**
 * Script para agregar la categoria 8 al modulo Presupuesto SST:
 * "Plan de Saneamiento Basico" (fumigacion, control de plagas, residuos, etc.)
 *
 * Solo inserta una fila en tbl_presupuesto_categorias.
 * Las actividades sugeridas estan en el JS del front (presupuesto_sst.php).
 *
 * Ejecutar: php app/SQL/agregar_categoria_8_saneamiento_basico.php
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

        echo "Insertando categoria 8 'Plan de Saneamiento Basico'... ";
        // Idempotente: solo inserta si NO existe ya una categoria con codigo='8'.
        $existe = (int) $pdo->query("SELECT COUNT(*) FROM tbl_presupuesto_categorias WHERE codigo = '8'")->fetchColumn();
        if ($existe > 0) {
            echo "YA EXISTE — actualizando nombre/orden/activo\n";
            $pdo->exec("UPDATE tbl_presupuesto_categorias SET nombre = 'Plan de Saneamiento Basico', orden = 8, activo = 1 WHERE codigo = '8'");
        } else {
            $pdo->exec("INSERT INTO tbl_presupuesto_categorias (codigo, nombre, orden, activo) VALUES ('8', 'Plan de Saneamiento Basico', 8, 1)");
            echo "OK (insertada)\n";
        }
        echo "\n";

        echo "Verificacion (categorias activas, ordenadas):\n";
        foreach ($pdo->query("SELECT id_categoria, codigo, nombre, orden, activo FROM tbl_presupuesto_categorias ORDER BY orden ASC")->fetchAll(PDO::FETCH_ASSOC) as $r) {
            echo "  {$r['orden']}. [{$r['codigo']}] {$r['nombre']} (id={$r['id_categoria']}, activo={$r['activo']})\n";
        }

        return true;
    } catch (PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "\n";
echo "========================================================\n";
echo "  AGREGAR CATEGORIA 8 - Plan de Saneamiento Basico\n";
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
