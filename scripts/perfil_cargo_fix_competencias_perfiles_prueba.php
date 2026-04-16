<?php
/**
 * Fix: agrega competencias a los 9 perfiles de prueba creados por
 * perfil_cargo_seed_perfiles_prueba.php, usando matching normalizado
 * (sin tildes, mayusculas, sin sufijos tipo "(ET)", "(HC)", etc.).
 *
 * SOLO LOCAL. Idempotente: si ya hay competencias para un perfil, las elimina y reinserta.
 */

$ID_CLIENTE = 18;

$pdo = new PDO('mysql:host=127.0.0.1;dbname=empresas_sst;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
echo "Conexion LOCAL OK\n\n";

// Normalizacion robusta (sin iconv que falla en Windows)
function normalizar(string $s): string {
    $s = mb_strtoupper($s, 'UTF-8');
    // Mapa de tildes y enie UTF-8 → ASCII
    $map = [
        'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ü'=>'U','Ñ'=>'N',
        'À'=>'A','È'=>'E','Ì'=>'I','Ò'=>'O','Ù'=>'U',
        'Ä'=>'A','Ë'=>'E','Ï'=>'I','Ö'=>'O',
        'Â'=>'A','Ê'=>'E','Î'=>'I','Ô'=>'O','Û'=>'U',
    ];
    $s = strtr($s, $map);
    // Eliminar sufijos "(XX)" con letras/espacios
    $s = preg_replace('/\s*\([A-Z ]+\)\s*/', '', $s);
    // Normalizar separadores
    $s = str_replace(['/', '-', '.', ','], ' ', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
}

// Mapa normalizado id_competencia => nombre real + version normalizada
$compMapNorm = [];
foreach ($pdo->query("SELECT id_competencia, nombre FROM tbl_competencia_cliente WHERE id_cliente={$ID_CLIENTE} AND activo=1") as $r) {
    $compMapNorm[normalizar($r['nombre'])] = (int)$r['id_competencia'];
}
echo "Competencias normalizadas: " . count($compMapNorm) . "\n\n";

// Definicion de competencias por cargo (nombre cargo => [ [nombre_competencia, nivel], ... ])
$competenciasPorCargo = [
    'Gerente General' => [
        ['Liderazgo', 5],
        ['Pensamiento Analitico', 5],
        ['Direccion de Personas', 5],
        ['Impacto e Influencia', 5],
        ['Orientacion al Logro', 5],
    ],
    'Director de Riesgos' => [
        ['Pensamiento Analitico', 5],
        ['Conciencia Financiera', 5],
        ['Experiencia Funcional/Tecnica', 5],
        ['Responsabilidad por Resultados', 4],
        ['Integridad', 5],
    ],
    'Analista Contable' => [
        ['Pensamiento Analitico', 4],
        ['Preocupacion por el Orden y la Calidad', 5],
        ['Responsabilidad por Resultados', 4],
        ['Experiencia Funcional/Tecnica', 4],
    ],
    'Contador' => [
        ['Conciencia Financiera', 5],
        ['Pensamiento Analitico', 5],
        ['Experiencia Funcional/Tecnica', 5],
        ['Integridad', 5],
        ['Direccion de Personas', 3],
    ],
    'Director de Talento Humano' => [
        ['Desarrollo de Personas', 5],
        ['Comprension Interpersonal', 5],
        ['Direccion de Personas', 5],
        ['Liderazgo', 5],
        ['Integridad', 5],
    ],
    'Coordinador SG-SST' => [
        ['Experiencia Funcional/Tecnica', 5],
        ['Preocupacion por el Orden y la Calidad', 5],
        ['Habilidades de Planeacion', 4],
        ['Busqueda de Informacion', 4],
    ],
    'Analista de Credito' => [
        ['Pensamiento Analitico', 5],
        ['Conciencia Financiera', 4],
        ['Busqueda de Informacion', 4],
        ['Preocupacion por el Orden y la Calidad', 4],
    ],
    'Analista de Soporte' => [
        ['Orientacion al Cliente', 5],
        ['Experiencia Funcional/Tecnica', 4],
        ['Busqueda de Informacion', 4],
        ['Flexibilidad', 4],
    ],
    'Oficial de Ciberseguridad' => [
        ['Experiencia Funcional/Tecnica', 5],
        ['Pensamiento Analitico', 5],
        ['Autocontrol', 4],
        ['Integridad', 5],
    ],
    'Asesor Comercial Ahorro' => [
        ['Orientacion al Cliente', 5],
        ['Orientacion al Logro', 5],
        ['Comprension Interpersonal', 4],
        ['Negociacion', 4],
    ],
];

// Resolver id_perfil de cada cargo
$resolverPerfil = $pdo->prepare("
    SELECT pc.id_perfil_cargo
    FROM tbl_perfil_cargo pc
    JOIN tbl_cargos_cliente cg ON cg.id = pc.id_cargo_cliente
    WHERE pc.id_cliente = ? AND cg.nombre_cargo = ? LIMIT 1
");

$delComp = $pdo->prepare("DELETE FROM tbl_perfil_cargo_competencia WHERE id_perfil_cargo = ?");
$insComp = $pdo->prepare("INSERT INTO tbl_perfil_cargo_competencia (id_perfil_cargo, id_competencia, nivel_requerido, orden) VALUES (?, ?, ?, ?)");

$totalOk = 0; $totalNo = 0;

foreach ($competenciasPorCargo as $cargoNombre => $lista) {
    $resolverPerfil->execute([$ID_CLIENTE, $cargoNombre]);
    $row = $resolverPerfil->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo "  ??  Perfil no existe para cargo: {$cargoNombre}\n";
        continue;
    }
    $idPerfil = (int)$row['id_perfil_cargo'];

    // Limpiar competencias anteriores de este perfil
    $delComp->execute([$idPerfil]);

    $okCount = 0; $noCount = 0;
    foreach ($lista as $i => $c) {
        $nombreBuscado = normalizar($c[0]);
        $nivel = (int)$c[1];
        $idComp = $compMapNorm[$nombreBuscado] ?? null;
        if ($idComp === null) {
            // Fallback: matching parcial por LIKE sobre normalizado
            foreach ($compMapNorm as $nRaw => $id) {
                if (str_contains($nRaw, $nombreBuscado) || str_contains($nombreBuscado, $nRaw)) {
                    $idComp = $id;
                    break;
                }
            }
        }
        if ($idComp === null) {
            echo "      NO  {$c[0]} (normalizado: {$nombreBuscado})\n";
            $noCount++;
            continue;
        }
        $insComp->execute([$idPerfil, $idComp, $nivel, $i + 1]);
        $okCount++;
    }

    echo "  OK   [{$idPerfil}] {$cargoNombre}  (competencias insertadas: {$okCount}/" . count($lista) . ")\n";
    $totalOk += $okCount;
    $totalNo += $noCount;
}

echo "\n-- Resumen --\n";
echo "  Competencias insertadas: {$totalOk}\n";
echo "  Competencias no resueltas: {$totalNo}\n";
echo "LISTO\n";
