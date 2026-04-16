<?php
/**
 * IPEVR GTC 45 - Fase 1: Tablas catalogo + seed
 *
 * Crea 7 tablas catalogo globales de la metodologia GTC 45 y hace seed de datos fijos.
 * Idempotente: CREATE TABLE IF NOT EXISTS + INSERT IGNORE.
 *
 * NO toca tablas operacionales (tbl_ipevr_matriz, tbl_ipevr_fila) — eso es Fase 3.
 * NO toca maestros cliente — eso es Fase 2.
 *
 * Uso:
 *   php scripts/ipevr_gtc45_fase1.php             # LOCAL (XAMPP)
 *   php scripts/ipevr_gtc45_fase1.php --env=prod  # PRODUCCION (DigitalOcean SSL)
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

$run = function(string $label, string $sql) use ($pdo) {
    try {
        $pdo->exec($sql);
        echo "  OK  {$label}\n";
    } catch (Throwable $e) {
        echo "  ERR {$label}: " . $e->getMessage() . "\n";
    }
};

echo "-- Paso 1: CREATE TABLES --\n";

$run('tbl_gtc45_clasificacion_peligro', "
CREATE TABLE IF NOT EXISTS tbl_gtc45_clasificacion_peligro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    nombre VARCHAR(80) NOT NULL,
    descripcion TEXT NULL,
    orden TINYINT NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_gtc45_peligro_catalogo', "
CREATE TABLE IF NOT EXISTS tbl_gtc45_peligro_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_clasificacion INT NOT NULL,
    codigo VARCHAR(60) NOT NULL UNIQUE,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_clasif (id_clasificacion),
    CONSTRAINT fk_gtc45_peligro_clasif FOREIGN KEY (id_clasificacion)
        REFERENCES tbl_gtc45_clasificacion_peligro(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_gtc45_nivel_deficiencia', "
CREATE TABLE IF NOT EXISTS tbl_gtc45_nivel_deficiencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(5) NOT NULL UNIQUE,
    nombre VARCHAR(30) NOT NULL,
    valor TINYINT NOT NULL,
    descripcion TEXT NOT NULL,
    orden TINYINT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_gtc45_nivel_exposicion', "
CREATE TABLE IF NOT EXISTS tbl_gtc45_nivel_exposicion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(5) NOT NULL UNIQUE,
    nombre VARCHAR(30) NOT NULL,
    valor TINYINT NOT NULL,
    descripcion TEXT NOT NULL,
    orden TINYINT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_gtc45_nivel_consecuencia', "
CREATE TABLE IF NOT EXISTS tbl_gtc45_nivel_consecuencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(5) NOT NULL UNIQUE,
    nombre VARCHAR(40) NOT NULL,
    valor SMALLINT NOT NULL,
    danos_personales TEXT NOT NULL,
    orden TINYINT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_gtc45_nivel_probabilidad', "
CREATE TABLE IF NOT EXISTS tbl_gtc45_nivel_probabilidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(5) NOT NULL UNIQUE,
    nombre VARCHAR(30) NOT NULL,
    rango_min SMALLINT NOT NULL,
    rango_max SMALLINT NOT NULL,
    descripcion TEXT NOT NULL,
    orden TINYINT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_gtc45_nivel_riesgo', "
CREATE TABLE IF NOT EXISTS tbl_gtc45_nivel_riesgo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(5) NOT NULL UNIQUE,
    nombre VARCHAR(30) NOT NULL,
    rango_min SMALLINT NOT NULL,
    rango_max SMALLINT NOT NULL,
    significado TEXT NOT NULL,
    aceptabilidad VARCHAR(80) NOT NULL,
    color_hex VARCHAR(10) NOT NULL,
    orden TINYINT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

echo "\n-- Paso 2: SEED catalogos --\n";

// Clasificaciones de peligro (7 categorias GTC 45)
$clasificaciones = [
    ['biologico',             'Biologico',               'Virus, bacterias, hongos, parasitos, picaduras, mordeduras, fluidos biologicos', 1],
    ['fisico',                'Fisico',                  'Ruido, iluminacion, vibracion, temperaturas extremas, presion, radiaciones',    2],
    ['quimico',               'Quimico',                 'Polvos, fibras, liquidos, gases, vapores, humos, material particulado',         3],
    ['psicosocial',           'Psicosocial',             'Gestion organizacional, caracteristicas de la tarea, jornada, interfaz',        4],
    ['biomecanico',           'Biomecanico',             'Postura, movimiento repetitivo, esfuerzo, manipulacion manual de cargas',       5],
    ['condiciones_seguridad', 'Condiciones de Seguridad','Mecanico, electrico, locativo, tecnologico, accidentes transito, publicos',     6],
    ['fenomenos_naturales',   'Fenomenos Naturales',     'Sismo, inundacion, vendaval, derrumbe, precipitaciones, tormenta electrica',    7],
];
$stmt = $pdo->prepare("INSERT IGNORE INTO tbl_gtc45_clasificacion_peligro (codigo,nombre,descripcion,orden) VALUES (?,?,?,?)");
foreach ($clasificaciones as $c) $stmt->execute($c);
echo "  OK  clasificaciones insertadas (" . count($clasificaciones) . ")\n";

// Map codigo -> id para FK del catalogo
$mapClasif = $pdo->query("SELECT codigo,id FROM tbl_gtc45_clasificacion_peligro")
    ->fetchAll(PDO::FETCH_KEY_PAIR);

// Catalogo de peligros (extraido de ContextoClienteController::getPeligrosDisponibles)
$peligros = [
    // fisicos
    ['fisico','ruido','Ruido (continuo, intermitente, impacto)'],
    ['fisico','iluminacion','Iluminacion (deficiencia, exceso)'],
    ['fisico','vibracion','Vibracion (cuerpo entero, segmentaria)'],
    ['fisico','temperaturas_altas','Temperaturas extremas - Calor'],
    ['fisico','temperaturas_bajas','Temperaturas extremas - Frio'],
    ['fisico','presion_atmosferica','Presion atmosferica (normal, ajustada)'],
    ['fisico','radiaciones_ionizantes','Radiaciones ionizantes (rayos X, gamma)'],
    ['fisico','radiaciones_no_ionizantes','Radiaciones no ionizantes (UV, laser, infrarrojo)'],
    // quimicos
    ['quimico','polvos_organicos','Polvos organicos'],
    ['quimico','polvos_inorganicos','Polvos inorganicos'],
    ['quimico','fibras','Fibras'],
    ['quimico','gases_vapores','Gases y vapores'],
    ['quimico','humos_metalicos','Humos metalicos'],
    ['quimico','humos_no_metalicos','Humos no metalicos'],
    ['quimico','material_particulado','Material particulado'],
    ['quimico','liquidos_quimicos','Liquidos (nieblas y rocios)'],
    // biologicos
    ['biologico','virus','Virus'],
    ['biologico','bacterias','Bacterias'],
    ['biologico','hongos','Hongos'],
    ['biologico','parasitos','Parasitos'],
    ['biologico','picaduras','Picaduras (insectos, aranas)'],
    ['biologico','mordeduras','Mordeduras (serpientes, roedores)'],
    ['biologico','fluidos_biologicos','Fluidos o excrementos'],
    // biomecanicos
    ['biomecanico','postura_prolongada','Postura prolongada (sentado, de pie)'],
    ['biomecanico','postura_forzada','Postura forzada (fuera de angulos de confort)'],
    ['biomecanico','postura_antigravitacional','Postura antigravitacional'],
    ['biomecanico','movimiento_repetitivo','Movimiento repetitivo'],
    ['biomecanico','manipulacion_cargas','Esfuerzo (manipulacion manual de cargas)'],
    // psicosociales
    ['psicosocial','gestion_organizacional','Gestion organizacional (estilo de mando, evaluacion)'],
    ['psicosocial','condiciones_tarea','Caracteristicas de la tarea (carga mental, monotonia)'],
    ['psicosocial','jornada_trabajo','Jornada de trabajo (pausas, trabajo nocturno)'],
    ['psicosocial','interfaz_persona_tarea','Interfaz persona-tarea (conocimientos, habilidades)'],
    ['psicosocial','condiciones_medio_ambiente','Condiciones del medio ambiente de trabajo'],
    // condiciones de seguridad
    ['condiciones_seguridad','mecanico','Mecanico (elementos de maquinas, herramientas)'],
    ['condiciones_seguridad','electrico','Electrico (alta y baja tension, estatica)'],
    ['condiciones_seguridad','locativo','Locativo (superficies, orden, almacenamiento)'],
    ['condiciones_seguridad','tecnologico','Tecnologico (explosion, fuga, incendio)'],
    ['condiciones_seguridad','accidentes_transito','Accidentes de transito'],
    ['condiciones_seguridad','publicos','Publicos (robos, asaltos, atentados)'],
    ['condiciones_seguridad','trabajo_alturas','Trabajo en alturas'],
    ['condiciones_seguridad','espacios_confinados','Espacios confinados'],
    // fenomenos naturales
    ['fenomenos_naturales','sismo','Sismo / Terremoto'],
    ['fenomenos_naturales','inundacion','Inundacion'],
    ['fenomenos_naturales','vendaval','Vendaval'],
    ['fenomenos_naturales','derrumbe','Derrumbe / Deslizamiento'],
    ['fenomenos_naturales','precipitaciones','Precipitaciones (lluvias, granizo)'],
    ['fenomenos_naturales','tormenta_electrica','Tormenta electrica'],
];
$stmtP = $pdo->prepare("INSERT IGNORE INTO tbl_gtc45_peligro_catalogo (id_clasificacion,codigo,nombre) VALUES (?,?,?)");
$okP = 0;
foreach ($peligros as [$clasif, $cod, $nom]) {
    if (!isset($mapClasif[$clasif])) { echo "  WARN clasif no encontrada: {$clasif}\n"; continue; }
    $stmtP->execute([$mapClasif[$clasif], $cod, $nom]);
    $okP++;
}
echo "  OK  peligros catalogo procesados ({$okP})\n";

// Nivel de Deficiencia (ND)
$nds = [
    ['MA','Muy Alto',10,'Se ha(n) detectado peligro(s) que determina(n) como posible la generacion de incidentes o consecuencias muy significativas, o la eficacia del conjunto de medidas preventivas existentes respecto al riesgo es nula o no existe, o ambos.',1],
    ['A','Alto',6,'Se ha(n) detectado algun(os) peligro(s) que pueden dar lugar a consecuencias significativa(s), o la eficacia del conjunto de medidas preventivas existentes es baja, o ambos.',2],
    ['M','Medio',2,'Se han detectado peligros que pueden dar lugar a consecuencias poco significativas o de menor importancia, o la eficacia del conjunto de medidas preventivas existentes es moderada, o ambos.',3],
    ['B','Bajo',0,'No se ha detectado consecuencia alguna, o la eficacia del conjunto de medidas preventivas existentes es alta, o ambos. El riesgo esta controlado. Estos peligros se clasifican directamente en el nivel de riesgo IV.',4],
];
$stmt = $pdo->prepare("INSERT IGNORE INTO tbl_gtc45_nivel_deficiencia (codigo,nombre,valor,descripcion,orden) VALUES (?,?,?,?,?)");
foreach ($nds as $n) $stmt->execute($n);
echo "  OK  nivel_deficiencia (" . count($nds) . ")\n";

// Nivel de Exposicion (NE)
$nes = [
    ['EC','Continua',4,'La situacion de exposicion se presenta sin interrupcion o varias veces con tiempo prolongado durante la jornada laboral.',1],
    ['EF','Frecuente',3,'La situacion de exposicion se presenta varias veces durante la jornada laboral por tiempos cortos.',2],
    ['EO','Ocasional',2,'La situacion de exposicion se presenta alguna vez durante la jornada laboral y por un periodo de tiempo corto.',3],
    ['EE','Esporadica',1,'La situacion de exposicion se presenta de manera eventual.',4],
];
$stmt = $pdo->prepare("INSERT IGNORE INTO tbl_gtc45_nivel_exposicion (codigo,nombre,valor,descripcion,orden) VALUES (?,?,?,?,?)");
foreach ($nes as $n) $stmt->execute($n);
echo "  OK  nivel_exposicion (" . count($nes) . ")\n";

// Nivel de Consecuencia (NC)
$ncs = [
    ['M','Mortal o Catastrofico',100,'Muerte(s)',1],
    ['MG','Muy Grave',60,'Lesiones o enfermedades graves irreparables (Incapacidad permanente, parcial o invalidez).',2],
    ['G','Grave',25,'Lesiones o enfermedades con incapacidad laboral temporal (ILT).',3],
    ['L','Leve',10,'Lesiones o enfermedades que no requieren incapacidad.',4],
];
$stmt = $pdo->prepare("INSERT IGNORE INTO tbl_gtc45_nivel_consecuencia (codigo,nombre,valor,danos_personales,orden) VALUES (?,?,?,?,?)");
foreach ($ncs as $n) $stmt->execute($n);
echo "  OK  nivel_consecuencia (" . count($ncs) . ")\n";

// Nivel de Probabilidad (NP) - rangos resultado de ND*NE
$nps = [
    ['MA','Muy Alto',24,40,'Situacion deficiente con exposicion continua, o muy deficiente con exposicion frecuente. Normalmente la materializacion del riesgo ocurre con frecuencia.',1],
    ['A','Alto',10,20,'Situacion deficiente con exposicion frecuente u ocasional, o bien situacion muy deficiente con exposicion ocasional o esporadica. La materializacion del riesgo es posible que suceda varias veces en la vida laboral.',2],
    ['M','Medio',6,8,'Situacion deficiente con exposicion esporadica, o bien situacion mejorable con exposicion continuada frecuente. Es posible que suceda el dano alguna vez.',3],
    ['B','Bajo',2,4,'Situacion mejorable con exposicion ocasional o esporadica, o situacion sin anomalia destacable con cualquier nivel de exposicion. No es esperable que se materialice el riesgo, aunque puede ser concebible.',4],
];
$stmt = $pdo->prepare("INSERT IGNORE INTO tbl_gtc45_nivel_probabilidad (codigo,nombre,rango_min,rango_max,descripcion,orden) VALUES (?,?,?,?,?,?)");
foreach ($nps as $n) $stmt->execute($n);
echo "  OK  nivel_probabilidad (" . count($nps) . ")\n";

// Nivel de Riesgo (NR) - rangos resultado NP*NC, con aceptabilidad y color
$nrs = [
    ['I','I',600,4000,'Situacion critica. Suspender actividades hasta que el riesgo este bajo control. Intervencion urgente.','No Aceptable','#c0392b',1],
    ['II','II',150,500,'Corregir y adoptar medidas de control inmediato. Sin embargo, suspenda actividades si el nivel de riesgo esta por encima o igual de 360.','No Aceptable o Aceptable con control especifico','#e67e22',2],
    ['III','III',40,120,'Mejorar si es posible. Seria conveniente justificar la intervencion y su rentabilidad.','Aceptable','#f1c40f',3],
    ['IV','IV',0,20,'Mantener las medidas de control existentes, pero se deberian considerar soluciones o mejoras y se deben hacer comprobaciones periodicas para asegurar que el riesgo aun es aceptable.','Aceptable','#27ae60',4],
];
$stmt = $pdo->prepare("INSERT IGNORE INTO tbl_gtc45_nivel_riesgo (codigo,nombre,rango_min,rango_max,significado,aceptabilidad,color_hex,orden) VALUES (?,?,?,?,?,?,?,?)");
foreach ($nrs as $n) $stmt->execute($n);
echo "  OK  nivel_riesgo (" . count($nrs) . ")\n";

echo "\n-- Paso 3: VERIFICACION --\n";
$tablas = [
    'tbl_gtc45_clasificacion_peligro' => 7,
    'tbl_gtc45_peligro_catalogo'      => 46,
    'tbl_gtc45_nivel_deficiencia'     => 4,
    'tbl_gtc45_nivel_exposicion'      => 4,
    'tbl_gtc45_nivel_consecuencia'    => 4,
    'tbl_gtc45_nivel_probabilidad'    => 4,
    'tbl_gtc45_nivel_riesgo'          => 4,
];
$todoOk = true;
foreach ($tablas as $t => $esperado) {
    $c = (int)$pdo->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();
    $mark = ($c >= $esperado) ? 'OK ' : 'ERR';
    if ($c < $esperado) $todoOk = false;
    echo "  {$mark} {$t}: {$c} filas (esperado >= {$esperado})\n";
}

echo "\n" . ($todoOk ? "FASE 1 COMPLETADA OK" : "FASE 1 CON ERRORES") . "\n";
exit($todoOk ? 0 : 1);
