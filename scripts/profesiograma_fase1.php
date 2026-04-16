<?php
/**
 * PROFESIOGRAMA - Fase 1: Catalogo examenes + tabla operacional + dashboard item
 *
 * Crea:
 *   1. tbl_profesiograma_examenes_catalogo (global, seed ~20 examenes)
 *   2. tbl_profesiograma_cliente (operacional, por cliente+cargo)
 *   3. Dashboard item para Consultor
 *
 * Idempotente: CREATE TABLE IF NOT EXISTS + INSERT IGNORE.
 *
 * Uso:
 *   php scripts/profesiograma_fase1.php             # LOCAL (XAMPP)
 *   php scripts/profesiograma_fase1.php --env=prod  # PRODUCCION (DigitalOcean SSL)
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

// =========================================================
echo "-- Paso 1: CREATE TABLES --\n";
// =========================================================

$run('tbl_profesiograma_examenes_catalogo', "
CREATE TABLE IF NOT EXISTS tbl_profesiograma_examenes_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    tipo_examen ENUM('laboratorio','imagenologia','funcional','psicologico','especialista') NOT NULL,
    descripcion TEXT NULL,
    clasificaciones_aplica JSON NULL COMMENT 'Array de codigos GTC45: [\"biomecanico\",\"quimico\"]',
    normativa_referencia VARCHAR(200) NULL COMMENT 'Ej: Res 2346/2007 Art 5',
    aplica_retiro TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=incluir en momento retiro',
    frecuencia_sugerida VARCHAR(50) NULL DEFAULT 'anual' COMMENT 'anual, semestral, cada_2_anios',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    orden INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_profesiograma_cliente', "
CREATE TABLE IF NOT EXISTS tbl_profesiograma_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_cargo INT NULL COMMENT 'FK a tbl_cargos_cliente',
    cargo_texto VARCHAR(200) NULL COMMENT 'Fallback texto libre si no hay maestro',
    id_examen INT NOT NULL COMMENT 'FK a catalogo examenes',
    momento ENUM('ingreso','periodico','retiro','cambio_cargo') NOT NULL,
    frecuencia VARCHAR(50) NULL COMMENT 'anual, semestral, cada_2_anios, unica_vez',
    obligatorio TINYINT(1) NOT NULL DEFAULT 1,
    observaciones TEXT NULL,
    origen ENUM('manual','ia','ipevr') NOT NULL DEFAULT 'manual',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_prof_cliente (id_cliente),
    KEY idx_prof_cargo (id_cargo),
    KEY idx_prof_examen (id_examen),
    KEY idx_prof_cliente_cargo (id_cliente, id_cargo),
    UNIQUE KEY uq_cliente_cargo_examen_momento (id_cliente, id_cargo, id_examen, momento),
    CONSTRAINT fk_prof_cliente FOREIGN KEY (id_cliente) REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE,
    CONSTRAINT fk_prof_cargo FOREIGN KEY (id_cargo) REFERENCES tbl_cargos_cliente(id) ON DELETE SET NULL,
    CONSTRAINT fk_prof_examen FOREIGN KEY (id_examen) REFERENCES tbl_profesiograma_examenes_catalogo(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// =========================================================
echo "\n-- Paso 2: SEED CATALOGO EXAMENES --\n";
// =========================================================

// clasificaciones_aplica usa codigos de tbl_gtc45_clasificacion_peligro:
// biologico, fisico, quimico, psicosocial, biomecanico, condiciones_seguridad, fenomenos_naturales

$examenes = [
    // [nombre, tipo_examen, descripcion, clasificaciones_aplica (JSON), normativa, aplica_retiro, frecuencia_sugerida, orden]
    [
        'Examen medico ocupacional con enfasis osteomuscular',
        'especialista',
        'Evaluacion medica general con enfasis en sistema musculoesqueletico, columna vertebral, extremidades',
        '["biomecanico","condiciones_seguridad"]',
        'Res 2346/2007',
        1,
        'anual',
        1,
    ],
    [
        'Visiometria',
        'funcional',
        'Evaluacion de agudeza visual, campo visual, vision cromatica, profundidad',
        '["biomecanico","fisico"]',
        'Res 2346/2007',
        0,
        'anual',
        2,
    ],
    [
        'Audiometria',
        'funcional',
        'Evaluacion de la capacidad auditiva, umbrales de audicion por frecuencia',
        '["fisico"]',
        'Res 2346/2007 - GATISO Hipoacusia',
        1,
        'anual',
        3,
    ],
    [
        'Espirometria',
        'funcional',
        'Evaluacion de la funcion pulmonar: capacidad vital forzada, volumen espiratorio',
        '["quimico","biologico"]',
        'Res 2346/2007 - GATISO Asma Ocupacional',
        1,
        'anual',
        4,
    ],
    [
        'Optometria',
        'funcional',
        'Evaluacion visual completa: refraccion, acomodacion, convergencia, motilidad ocular',
        '["biomecanico","fisico"]',
        'Res 2346/2007',
        0,
        'anual',
        5,
    ],
    [
        'Electrocardiograma en reposo',
        'funcional',
        'Evaluacion de la actividad electrica del corazon, ritmo cardiaco, conduccion',
        '["condiciones_seguridad","biomecanico"]',
        'Res 2346/2007',
        0,
        'anual',
        6,
    ],
    [
        'Prueba de equilibrio / vestibular',
        'funcional',
        'Evaluacion del sistema vestibular para trabajo en alturas o espacios confinados',
        '["condiciones_seguridad"]',
        'Res 1409/2012 - Trabajo en Alturas',
        0,
        'anual',
        7,
    ],
    [
        'Psicosensometrico',
        'funcional',
        'Evaluacion de tiempos de reaccion, coordinacion visomotora, percepcion de profundidad',
        '["condiciones_seguridad"]',
        'Res 2346/2007',
        0,
        'anual',
        8,
    ],
    [
        'Perfil lipidico',
        'laboratorio',
        'Colesterol total, HDL, LDL, trigliceridos — riesgo cardiovascular',
        '["psicosocial"]',
        'Res 2346/2007',
        0,
        'anual',
        9,
    ],
    [
        'Glicemia en ayunas',
        'laboratorio',
        'Evaluacion de niveles de glucosa en sangre',
        '["psicosocial","biomecanico"]',
        'Res 2346/2007',
        0,
        'anual',
        10,
    ],
    [
        'Cuadro hematico completo',
        'laboratorio',
        'Hemograma: hemoglobina, hematocrito, leucocitos, plaquetas, formula diferencial',
        '["biologico","quimico"]',
        'Res 2346/2007',
        0,
        'anual',
        11,
    ],
    [
        'Parcial de orina',
        'laboratorio',
        'Analisis fisicoquimico y microscopico de orina',
        '["quimico","biologico"]',
        'Res 2346/2007',
        0,
        'anual',
        12,
    ],
    [
        'Prueba psicologica ocupacional',
        'psicologico',
        'Evaluacion de aptitud psicologica para el cargo, estres, ansiedad, adaptacion',
        '["psicosocial"]',
        'Res 2646/2008',
        0,
        'anual',
        13,
    ],
    [
        'Bateria de riesgo psicosocial',
        'psicologico',
        'Aplicacion de bateria del Ministerio de Trabajo para identificacion de factores de riesgo psicosocial',
        '["psicosocial"]',
        'Res 2646/2008 - Res 2764/2022',
        0,
        'anual',
        14,
    ],
    [
        'Rx columna lumbosacra',
        'imagenologia',
        'Radiografia de columna lumbar para evaluacion de patologia osteomuscular',
        '["biomecanico"]',
        'Res 2346/2007 - GATISO DME',
        1,
        'cada_2_anios',
        15,
    ],
    [
        'Rx torax',
        'imagenologia',
        'Radiografia de torax para evaluacion pulmonar, exposicion a polvos, asbesto, humos',
        '["quimico"]',
        'Res 2346/2007 - GATISO Neumoconiosis',
        1,
        'anual',
        16,
    ],
    [
        'Colinesterasa',
        'laboratorio',
        'Niveles de colinesterasa en sangre para exposicion a plaguicidas organofosforados',
        '["quimico"]',
        'Res 2346/2007',
        0,
        'semestral',
        17,
    ],
    [
        'Prueba de embarazo (beta HCG)',
        'laboratorio',
        'Deteccion de embarazo para mujeres expuestas a agentes teratogenicos',
        '["quimico","biologico"]',
        'Res 2346/2007',
        0,
        'segun_caso',
        18,
    ],
    [
        'Valoracion dermatologica',
        'especialista',
        'Evaluacion de piel para exposicion a agentes quimicos, biologicos o fisicos',
        '["quimico","biologico","fisico"]',
        'Res 2346/2007',
        0,
        'anual',
        19,
    ],
    [
        'Valoracion de trabajo en alturas',
        'especialista',
        'Evaluacion integral para aptitud en trabajo en alturas: cardiovascular, vestibular, visual, musculoesqueletica',
        '["condiciones_seguridad"]',
        'Res 1409/2012 - Res 4272/2021',
        0,
        'anual',
        20,
    ],
];

$stmtCheck = $pdo->prepare("SELECT id FROM tbl_profesiograma_examenes_catalogo WHERE nombre = ?");
$stmtInsert = $pdo->prepare("
    INSERT INTO tbl_profesiograma_examenes_catalogo
    (nombre, tipo_examen, descripcion, clasificaciones_aplica, normativa_referencia, aplica_retiro, frecuencia_sugerida, orden)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$insertados = 0;
$existentes = 0;

foreach ($examenes as $ex) {
    $stmtCheck->execute([$ex[0]]);
    if ($stmtCheck->fetchColumn()) {
        $existentes++;
        echo "  SKIP  {$ex[0]}\n";
    } else {
        $stmtInsert->execute($ex);
        $insertados++;
        echo "  NEW   {$ex[0]}\n";
    }
}

echo "  Resultado: {$insertados} insertados, {$existentes} ya existian\n";

// =========================================================
echo "\n-- Paso 3: DASHBOARD ITEM --\n";
// =========================================================

$dashItem = [
    'rol' => 'Consultor',
    'tipo_proceso' => 'Cumplimiento',
    'detalle' => 'Profesiograma',
    'descripcion' => 'Examenes medicos ocupacionales por cargo segun IPEVR - Profesiograma Res 2346/2007 (requiere selector)',
    'accion_url' => '/profesiograma/cliente/{id_cliente}',
    'orden' => 12,
    'categoria' => 'Operación por Cliente',
    'icono' => 'fas fa-stethoscope',
    'color_gradiente' => '#bd9751,#d4af37',
    'target_blank' => 0,
    'activo' => 1,
];

$stmtCheckDash = $pdo->prepare("SELECT id FROM dashboard_items WHERE accion_url = ? AND rol = ?");
$stmtCheckDash->execute([$dashItem['accion_url'], $dashItem['rol']]);
$existente = $stmtCheckDash->fetchColumn();

if ($existente) {
    $stmtUpd = $pdo->prepare("
        UPDATE dashboard_items
        SET tipo_proceso=?, detalle=?, descripcion=?, orden=?, categoria=?, icono=?, color_gradiente=?, target_blank=?, activo=?, actualizado_en=NOW()
        WHERE id=?
    ");
    $stmtUpd->execute([
        $dashItem['tipo_proceso'], $dashItem['detalle'], $dashItem['descripcion'], $dashItem['orden'],
        $dashItem['categoria'], $dashItem['icono'], $dashItem['color_gradiente'], $dashItem['target_blank'], $dashItem['activo'],
        $existente,
    ]);
    echo "  UPD id={$existente}  {$dashItem['detalle']}\n";
} else {
    $stmtIns = $pdo->prepare("
        INSERT INTO dashboard_items
        (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo, creado_en, actualizado_en)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmtIns->execute([
        $dashItem['rol'], $dashItem['tipo_proceso'], $dashItem['detalle'], $dashItem['descripcion'], $dashItem['accion_url'],
        $dashItem['orden'], $dashItem['categoria'], $dashItem['icono'], $dashItem['color_gradiente'], $dashItem['target_blank'], $dashItem['activo'],
    ]);
    echo "  NEW id=" . $pdo->lastInsertId() . "  {$dashItem['detalle']}\n";
}

// =========================================================
echo "\n-- Paso 4: VERIFICACION --\n";
// =========================================================

$tablas = [
    'tbl_profesiograma_examenes_catalogo' => 20,
    'tbl_profesiograma_cliente' => 0,
];

$todoOk = true;
foreach ($tablas as $t => $esperado) {
    try {
        $c = (int)$pdo->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();
        $mark = ($c >= $esperado) ? 'OK' : 'WARN';
        if ($mark === 'WARN') $todoOk = false;
        echo "  {$mark}  {$t}: {$c} filas (esperado >= {$esperado})\n";
    } catch (Throwable $e) {
        echo "  ERR {$t}: " . $e->getMessage() . "\n";
        $todoOk = false;
    }
}

// Verificar dashboard item
$stmtCheckDash->execute([$dashItem['accion_url'], $dashItem['rol']]);
$dashId = $stmtCheckDash->fetchColumn();
echo "  " . ($dashId ? "OK  dashboard_items id={$dashId}" : "MISS dashboard_items") . "  {$dashItem['accion_url']}\n";

echo "\n" . ($todoOk ? "FASE 1 PROFESIOGRAMA COMPLETADA OK" : "FASE 1 CON ADVERTENCIAS") . "\n";
exit($todoOk ? 0 : 1);
