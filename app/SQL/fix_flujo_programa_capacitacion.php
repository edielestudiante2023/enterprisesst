<?php
/**
 * Fix: Cambiar flujo de programa_capacitacion de 'secciones_ia' a 'programa_con_pta'
 * Ejecutar: php app/SQL/fix_flujo_programa_capacitacion.php
 */

$conexiones = [
    'local' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl' => false
    ],
    'produccion' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ]
];

function aplicarFix(string $nombre, array $config): bool
{
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "  {$nombre}\n";
    echo str_repeat("=", 50) . "\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "Conectado a {$config['host']}\n\n";

        // 1. Verificar estado ANTES
        $stmt = $pdo->prepare("SELECT tipo_documento, flujo FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'programa_capacitacion'");
        $stmt->execute();
        $antes = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$antes) {
            echo "  [WARN] tipo_documento 'programa_capacitacion' NO existe en esta BD\n";
            echo "  Insertando...\n";
            // No existe, no hay nada que actualizar
            return true;
        }

        echo "  ANTES: flujo = '{$antes['flujo']}'\n";

        if ($antes['flujo'] === 'programa_con_pta') {
            echo "  [OK] Ya tiene el flujo correcto. Nada que hacer.\n";
            return true;
        }

        // 2. Aplicar UPDATE
        $stmt = $pdo->prepare("UPDATE tbl_doc_tipo_configuracion SET flujo = 'programa_con_pta' WHERE tipo_documento = 'programa_capacitacion'");
        $stmt->execute();
        $affected = $stmt->rowCount();

        // 3. Verificar estado DESPUES
        $stmt = $pdo->prepare("SELECT flujo FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'programa_capacitacion'");
        $stmt->execute();
        $despues = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "  DESPUES: flujo = '{$despues['flujo']}'\n";
        echo "  Filas afectadas: {$affected}\n";
        echo "  [OK] Fix aplicado correctamente\n";

        return true;

    } catch (PDOException $e) {
        echo "  [ERROR] " . $e->getMessage() . "\n";
        return false;
    }
}

// ========== EJECUCION ==========
echo "\nFix: programa_capacitacion flujo -> programa_con_pta\n";

// 1. LOCAL primero
$okLocal = aplicarFix('LOCAL', $conexiones['local']);

if (!$okLocal) {
    echo "\n[ABORT] Local fallo. NO se ejecuta en produccion.\n";
    exit(1);
}

// 2. PRODUCCION solo si local OK
$okProd = aplicarFix('PRODUCCION', $conexiones['produccion']);

echo "\n" . str_repeat("=", 50) . "\n";
echo "  RESULTADO FINAL\n";
echo str_repeat("=", 50) . "\n";
echo "  Local:      " . ($okLocal ? "OK" : "FALLO") . "\n";
echo "  Produccion: " . ($okProd ? "OK" : "FALLO") . "\n";
echo str_repeat("=", 50) . "\n";
