<?php
/**
 * Diagnostico: modelos con trait TenantScopedModel cuyas tablas NO tienen id_cliente.
 *
 * Estos modelos NO estan siendo aislados por tenant y potencialmente exponen
 * datos entre empresas. Hay que evaluar caso por caso si necesitan una
 * estrategia alternativa (filtrar via JOIN, ej: via id_documento -> id_cliente).
 *
 * Uso:
 *   php scripts/multitenant_05_diagnostico.php            # LOCAL
 *   php scripts/multitenant_05_diagnostico.php --env=prod # PRODUCCION
 */

$esProduccion = in_array('--env=prod', $argv ?? []);

if ($esProduccion) {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $dbname   = 'empresas_sst';
    $username = 'cycloid_userdb';
    $password = getenv('DB_PROD_PASS') ?: 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $ssl      = true;
    echo "=== PRODUCCION ===\n\n";
} else {
    $host     = '127.0.0.1';
    $port     = 3306;
    $dbname   = 'empresas_sst';
    $username = 'root';
    $password = '';
    $ssl      = false;
    echo "=== LOCAL ===\n\n";
}

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
        $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $username, $password, $opts);
} catch (Throwable $e) {
    echo "ERROR conexion: " . $e->getMessage() . "\n";
    exit(1);
}

// 1) Listar tablas que tienen id_cliente
$tablasConIdCliente = [];
$rows = $pdo->query("
    SELECT TABLE_NAME
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND COLUMN_NAME = 'id_cliente'
")->fetchAll(PDO::FETCH_COLUMN);
$tablasConIdCliente = array_flip($rows);

// 2) Recorrer modelos que tienen el trait
$modelsDir = __DIR__ . '/../app/Models';
$archivos = glob($modelsDir . '/*.php');

$conTraitYTabla = [];
$conTraitSinTabla = [];
$sinTrait = [];

foreach ($archivos as $archivo) {
    $nombre = basename($archivo, '.php');
    $contenido = file_get_contents($archivo);

    $usaTrait = strpos($contenido, 'use TenantScopedModel;') !== false;
    if (!$usaTrait) {
        $sinTrait[] = $nombre;
        continue;
    }

    // Extraer el nombre de la tabla
    if (preg_match('/protected\s+\$table\s*=\s*[\'"]([^\'"]+)[\'"]/', $contenido, $m)) {
        $tabla = $m[1];
        if (isset($tablasConIdCliente[$tabla])) {
            $conTraitYTabla[$nombre] = $tabla;
        } else {
            $conTraitSinTabla[$nombre] = $tabla;
        }
    }
}

echo "## Modelos con trait aplicado y tabla con id_cliente (OK - aislados)\n";
echo "Total: " . count($conTraitYTabla) . "\n\n";

echo "## MODELOS CON TRAIT PERO TABLA SIN id_cliente (trait NO tiene efecto)\n";
echo "Total: " . count($conTraitSinTabla) . "\n\n";
foreach ($conTraitSinTabla as $m => $t) {
    echo "  - {$m}  (tabla: {$t})\n";
}

echo "\n## Tablas con id_cliente en BD que NO tienen modelo con trait (potencial fuga)\n";
$tablasEnUsoPorModelos = array_merge(array_values($conTraitYTabla), array_values($conTraitSinTabla));
$tablasSinModelo = array_diff(array_keys($tablasConIdCliente), $tablasEnUsoPorModelos);
echo "Total: " . count($tablasSinModelo) . "\n";
foreach ($tablasSinModelo as $t) {
    echo "  - {$t}\n";
}

echo "\n## Resumen\n";
echo "  Tablas con id_cliente en BD: " . count($tablasConIdCliente) . "\n";
echo "  Modelos con trait (total): " . (count($conTraitYTabla) + count($conTraitSinTabla)) . "\n";
echo "  Modelos aislados correctamente: " . count($conTraitYTabla) . "\n";
echo "  Modelos con trait inutil (tabla sin id_cliente): " . count($conTraitSinTabla) . "\n";
echo "  Tablas con id_cliente sin modelo: " . count($tablasSinModelo) . "\n";
