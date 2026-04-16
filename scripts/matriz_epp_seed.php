<?php
/**
 * Matriz EPP - Fase 3: Seed
 *
 * Siembra:
 *   1) 9 categorias en tbl_epp_categoria
 *   2) ~10 items base en tbl_epp_maestro (extraidos del Excel SST-MT-G-003)
 *
 * Idempotente:
 *   - Categorias: INSERT IGNORE por nombre+tipo
 *   - Items maestro: se omiten si ya existe (id_categoria, elemento)
 *
 * NO poblar tbl_epp_cliente: el consultor lo hace via UI.
 *
 * Uso:
 *   php scripts/matriz_epp_seed.php             # LOCAL
 *   php scripts/matriz_epp_seed.php --env=prod  # PRODUCCION
 *
 * Ver: docs/MODULO_MATRIZ_EPP/ARQUITECTURA.md §3 y Excel docs/SST-MT-G-003 Matriz de EPP.xlsx
 */

$esProduccion = in_array('--env=prod', $argv ?? []);

if ($esProduccion) {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $dbname   = 'empresas_sst';
    $username = 'cycloid_userdb';
    $password = getenv('DB_PROD_PASS') ?: 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $ssl      = true;
    echo "=== PRODUCCION ===\n";
} else {
    $host     = '127.0.0.1';
    $port     = 3306;
    $dbname   = 'empresas_sst';
    $username = 'root';
    $password = '';
    $ssl      = false;
    echo "=== LOCAL ===\n";
}

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Conexion OK\n\n";
} catch (Throwable $e) {
    echo "ERROR conexion: " . $e->getMessage() . "\n";
    exit(1);
}

// ---------------------- CATEGORIAS ----------------------

$categorias = [
    ['Proteccion para cabeza',        'EPP',      10],
    ['Proteccion visual',             'EPP',      20],
    ['Proteccion auditiva',           'EPP',      30],
    ['Proteccion respiratoria',       'EPP',      40],
    ['Proteccion para manos',         'EPP',      50],
    ['Proteccion para pies',          'EPP',      60],
    ['Proteccion contra caidas',      'EPP',      70],
    ['Dotacion - Cuerpo',             'DOTACION', 80],
    ['Otros',                         'EPP',      90],
];

echo "-- Paso 1: Categorias --\n";
$insertados = 0;
$omitidos = 0;
$stmtCheck = $pdo->prepare("SELECT id_categoria FROM tbl_epp_categoria WHERE nombre = ? AND tipo = ?");
$stmtIns   = $pdo->prepare("INSERT INTO tbl_epp_categoria (nombre, tipo, orden, activo) VALUES (?, ?, ?, 1)");

foreach ($categorias as [$nombre, $tipo, $orden]) {
    $stmtCheck->execute([$nombre, $tipo]);
    if ($stmtCheck->fetchColumn()) {
        echo "  SKIP {$nombre} ({$tipo})\n";
        $omitidos++;
    } else {
        $stmtIns->execute([$nombre, $tipo, $orden]);
        echo "  OK   {$nombre} ({$tipo})\n";
        $insertados++;
    }
}
echo "  Total categorias: insertadas={$insertados} omitidas={$omitidos}\n\n";

// Mapear nombre -> id_categoria para usar en items
$mapCat = [];
$rows = $pdo->query("SELECT id_categoria, nombre FROM tbl_epp_categoria")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $mapCat[$r['nombre']] = (int)$r['id_categoria'];
}

// ---------------------- ITEMS MAESTRO ----------------------

$items = [
    [
        'categoria'         => 'Proteccion para pies',
        'elemento'          => 'Botas de seguridad dielectricas',
        'norma'             => 'DIN 53516 - NTC 2396-1 - NTC 2038 - ANSI Z41/99 - CST Art. 230',
        'mantenimiento'     => 'Limpieza regular, secado a la sombra, evitar exposicion prolongada a humedad o calor.',
        'frecuencia_cambio' => 'Cada 5 anos o segun indicacion del fabricante',
        'motivos_cambio'    => 'Golpes fuertes, desgaste visible, roturas, despues de una caida.',
        'momentos_uso'      => 'Permanentemente en salidas a campo y trabajo en obra donde pueda haber caida de objetos o golpes con objetos.',
    ],
    [
        'categoria'         => 'Proteccion para manos',
        'elemento'          => 'Guantes dielectricos',
        'norma'             => 'NTC 1801',
        'mantenimiento'     => 'Limpieza con pano humedo, almacenamiento en lugar seco y protegido de luz solar.',
        'frecuencia_cambio' => 'Cada cuatro meses segun resultados de pruebas dielectricas',
        'motivos_cambio'    => 'Perforaciones, grietas, perdida de elasticidad, exposicion a quimicos o calor excesivo.',
        'momentos_uso'      => 'Trabajos con riesgo de contacto electrico (tableros, mantenimiento electrico, pruebas de tension).',
    ],
    [
        'categoria'         => 'Proteccion para manos',
        'elemento'          => 'Guantes de agarre o mecanicos (capa nitrilo)',
        'norma'             => 'NTC 2190',
        'mantenimiento'     => 'Lavado periodico cuidando secarlos en su totalidad, colocandolos por el reves al secar.',
        'frecuencia_cambio' => 'Cada cuatro meses',
        'motivos_cambio'    => 'Guantes rotos, rasgados o impregnados con materiales quimicos no deben utilizarse.',
        'momentos_uso'      => 'Manipulacion de cargas, sistemas de control y materiales.',
    ],
    [
        'categoria'         => 'Proteccion para manos',
        'elemento'          => 'Guantes de caucho',
        'norma'             => 'NTC 1726 Z-81',
        'mantenimiento'     => 'Lavar con chorro de agua despues de cada uso con jabon o detergente, secar al aire.',
        'frecuencia_cambio' => 'Cada cuatro meses',
        'motivos_cambio'    => 'Rotos, defectuosos, deteriorados o que representen un riesgo para la operacion.',
        'momentos_uso'      => 'Cada vez que se realicen labores de aseo.',
    ],
    [
        'categoria'         => 'Proteccion auditiva',
        'elemento'          => 'Protector auditivo tipo insercion',
        'norma'             => 'ANSI S3.19-1974 / EN 352-2:2002',
        'mantenimiento'     => 'Lavarse periodicamente con agua fria y jabon de tocador. Mantener los tapones en su estuche cuando no esten en uso.',
        'frecuencia_cambio' => 'Cada cuatro meses',
        'motivos_cambio'    => 'Grietas o fisuras en las membranas de ajuste, limite de empleo, cuando se ensucien, deterioren o deformen parcialmente.',
        'momentos_uso'      => 'De manera continua cuando haya cualquier tipo de herramienta o equipo electrico encendido (esmeril, sierra circular, taladros, etc.).',
    ],
    [
        'categoria'         => 'Proteccion respiratoria',
        'elemento'          => 'Tapabocas N95',
        'norma'             => 'NIOSH 42 CFR 84 / NTC 5790',
        'mantenimiento'     => 'No requiere mantenimiento. Debe almacenarse en bolsa limpia y seca si es reutilizable temporalmente.',
        'frecuencia_cambio' => 'Diario o cada 8 horas continuas de uso (o antes si se humedece o dana)',
        'motivos_cambio'    => 'Humedad, deformacion, roturas, perdida de ajuste facial, contaminacion visible.',
        'momentos_uso'      => 'Ambientes con riesgo biologico, exposicion a particulas en suspension, hospitales, laboratorios, obras con polvo.',
    ],
    [
        'categoria'         => 'Proteccion visual',
        'elemento'          => 'Gafas de seguridad',
        'norma'             => 'ANSI Z87.1',
        'mantenimiento'     => 'Limpie los lentes bajo chorro de agua y jabon neutro, secar con pano o al aire. Guardarlas en lugar limpio y seco.',
        'frecuencia_cambio' => 'Cada cuatro meses',
        'motivos_cambio'    => 'Rayados (impiden ver con claridad), rotos, doblados o danados.',
        'momentos_uso'      => 'Permanentemente en labores donde pueda haber caida de particulas a los ojos.',
    ],
    [
        'categoria'         => 'Dotacion - Cuerpo',
        'elemento'          => 'Camisa manga larga',
        'norma'             => 'CST Art. 230',
        'mantenimiento'     => 'Lavado normal sin blanqueadores, lavado individual sin mezclar con otra ropa de casa.',
        'frecuencia_cambio' => 'Cada cuatro meses',
        'motivos_cambio'    => 'N.A.',
        'momentos_uso'      => 'Permanentemente durante la jornada laboral.',
    ],
    [
        'categoria'         => 'Dotacion - Cuerpo',
        'elemento'          => 'Pantalon de jean',
        'norma'             => 'CST Art. 230',
        'mantenimiento'     => 'Lavado normal sin blanqueadores, lavado individual sin mezclar con otra ropa de casa.',
        'frecuencia_cambio' => 'Cada cuatro meses',
        'motivos_cambio'    => 'N.A.',
        'momentos_uso'      => 'Permanentemente durante la jornada laboral.',
    ],
];

echo "-- Paso 2: Items maestro --\n";
$insItems = 0;
$skipItems = 0;
$errItems = 0;

$stmtCheckItem = $pdo->prepare("SELECT id_epp FROM tbl_epp_maestro WHERE id_categoria = ? AND elemento = ?");
$stmtInsItem = $pdo->prepare(
    "INSERT INTO tbl_epp_maestro
        (id_categoria, elemento, norma, mantenimiento, frecuencia_cambio, motivos_cambio, momentos_uso, ia_generado, activo)
     VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1)"
);

foreach ($items as $it) {
    try {
        if (!isset($mapCat[$it['categoria']])) {
            echo "  ERR categoria no encontrada: {$it['categoria']}\n";
            $errItems++;
            continue;
        }
        $idCat = $mapCat[$it['categoria']];
        $stmtCheckItem->execute([$idCat, $it['elemento']]);
        if ($stmtCheckItem->fetchColumn()) {
            echo "  SKIP [{$it['categoria']}] {$it['elemento']}\n";
            $skipItems++;
            continue;
        }
        $stmtInsItem->execute([
            $idCat,
            $it['elemento'],
            $it['norma'],
            $it['mantenimiento'],
            $it['frecuencia_cambio'],
            $it['motivos_cambio'],
            $it['momentos_uso'],
        ]);
        echo "  OK   [{$it['categoria']}] {$it['elemento']}\n";
        $insItems++;
    } catch (Throwable $e) {
        echo "  ERR [{$it['categoria']}] {$it['elemento']}: " . $e->getMessage() . "\n";
        $errItems++;
    }
}

echo "\n-- Verificacion --\n";
$totCat = (int)$pdo->query("SELECT COUNT(*) FROM tbl_epp_categoria")->fetchColumn();
$totItem = (int)$pdo->query("SELECT COUNT(*) FROM tbl_epp_maestro")->fetchColumn();
echo "  tbl_epp_categoria = {$totCat} filas\n";
echo "  tbl_epp_maestro   = {$totItem} filas\n";
echo "  items: insertados={$insItems} omitidos={$skipItems} errores={$errItems}\n";

$ok = ($errItems === 0);
echo "\n" . ($ok ? "FASE 3 COMPLETA\n" : "FASE 3 CON ERRORES\n");
exit($ok ? 0 : 1);
