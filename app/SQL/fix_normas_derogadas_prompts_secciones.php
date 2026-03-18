<?php
/**
 * fix_normas_derogadas_prompts_secciones.php
 *
 * Corrige prompts de tbl_doc_secciones_config que tenían normas derogadas hardcodeadas:
 * - politica_acoso_sexual Marco Legal (id=584): Resolucion 652/2012 → 3461/2025
 * - politica_acoso_laboral Marco Legal (id=175): prompt enriquecido con normas vigentes
 * - politica_discriminacion Marco Legal (id=205): prompt enriquecido con normas vigentes
 *
 * Uso: php app/SQL/fix_normas_derogadas_prompts_secciones.php [local|prod]
 */

$entorno = $argv[1] ?? 'local';

if ($entorno === 'prod') {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $user     = 'cycloid_userdb';
    $pass     = 'AVNS_iDypWizlpMRwHIORJGG';
    $dbname   = 'empresas_sst';
    $ssl      = true;
} else {
    $host     = 'localhost';
    $port     = 3306;
    $user     = 'root';
    $pass     = '';
    $dbname   = 'empresas_sst';
    $ssl      = false;
}

echo "=== Entorno: $entorno ===\n";

$db = mysqli_init();
if ($ssl) {
    mysqli_ssl_set($db, null, null, null, null, null);
    mysqli_real_connect($db, $host, $user, $pass, $dbname, $port, null, MYSQLI_CLIENT_SSL);
} else {
    mysqli_real_connect($db, $host, $user, $pass, $dbname, $port);
}

if (mysqli_connect_errno()) {
    die("Error conexión: " . mysqli_connect_error() . "\n");
}

$fixes = [
    [
        'id'    => 584,
        'desc'  => 'politica_acoso_sexual Marco Legal — reemplaza Resolucion 652/2012 por 3461/2025',
        'sql'   => "UPDATE tbl_doc_secciones_config
                    SET prompt_ia = 'Lista el marco normativo de la Politica de Acoso Sexual. Incluir obligatoriamente: Constitucion Politica Arts. 1/13/43, Codigo Penal Art. 210A, Ley 2365 de 2024 (modificacion Art. 210A), Ley 1257 de 2008, Ley 1719 de 2014, Resolucion 3461 de 2025 (deroga Resoluciones 652 y 1356 de 2012, norma vigente sobre Comite de Convivencia Laboral), Decreto 1072/2015, Resolucion 0312/2019.'
                    WHERE id_seccion_config = 584",
    ],
    [
        'id'    => 175,
        'desc'  => 'politica_acoso_laboral Marco Legal — enriquecer con normas vigentes y excluir derogadas',
        'sql'   => "UPDATE tbl_doc_secciones_config
                    SET prompt_ia = 'Lista el marco normativo aplicable a la Politica de Acoso Laboral. Incluir obligatoriamente: Ley 1010 de 2006 modificada por Ley 2209 de 2022 (acoso laboral), Resolucion 3461 de 2025 (norma vigente sobre conformacion y funcionamiento del Comite de Convivencia Laboral - derogo las Resoluciones 652 y 1356 de 2012), Decreto 1072 de 2015, Resolucion 0312 de 2019, Constitucion Politica Arts. 25 y 53. NO citar Resoluciones 652 de 2012 ni 1356 de 2012 pues fueron derogadas. NO citar Decreto 1443 de 2014 como norma independiente pues fue compilado en el Decreto 1072 de 2015.'
                    WHERE id_seccion_config = 175",
    ],
    [
        'id'    => 205,
        'desc'  => 'politica_discriminacion Marco Legal — enriquecer con normas vigentes',
        'sql'   => "UPDATE tbl_doc_secciones_config
                    SET prompt_ia = 'Lista el marco normativo aplicable a la Politica de No Discriminacion e Igualdad. Incluir obligatoriamente: Constitucion Politica Arts. 1/13/43/53, Ley 1482 de 2011 modificada por Ley 1752 de 2015 (antidiscriminacion), Convenio OIT C111, Codigo Sustantivo del Trabajo Arts. 10 y 143, Decreto 1072 de 2015, Resolucion 0312 de 2019. Para temas relacionados con convivencia laboral, la norma vigente es la Resolucion 3461 de 2025 que derogo las Resoluciones 652 y 1356 de 2012. NO citar Decreto 1443 de 2014 como norma independiente.'
                    WHERE id_seccion_config = 205",
    ],
];

foreach ($fixes as $fix) {
    try {
        $ok = mysqli_query($db, $fix['sql']);
        $rows = mysqli_affected_rows($db);
        echo "  [OK] id={$fix['id']} ({$rows} fila) — {$fix['desc']}\n";
    } catch (Exception $e) {
        echo "  [ERROR] id={$fix['id']}: " . $e->getMessage() . "\n";
    }
}

mysqli_close($db);
echo "=== Listo ===\n";
