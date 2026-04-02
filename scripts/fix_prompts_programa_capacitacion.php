<?php
/**
 * FIX: Copiar prompt_ia de programa_capacitacion a las secciones que están vacías.
 *
 * Causa raíz: Los registros en tbl_doc_secciones_config se insertaron en producción
 * pero sin los valores de prompt_ia (todos quedaron vacíos).
 *
 * Uso LOCAL:   php scripts/fix_prompts_programa_capacitacion.php
 * Uso PROD:    php scripts/fix_prompts_programa_capacitacion.php --prod
 */

$isProd = in_array('--prod', $argv ?? []);

// ── Prompts correctos (fuente: BD local verificada) ──
$prompts = [
    'introduccion' => 'Genera una introducción para el Programa de Capacitación en SST que incluya: justificación de por qué la empresa necesita este programa, contexto de la actividad económica y sus riesgos, mención del marco normativo (Decreto 1072/2015, Resolución 0312/2019), compromiso de la alta dirección. Ajusta la extensión según el tamaño de empresa.',
    'objetivo_general' => 'Genera el objetivo general del Programa de Capacitación. Debe ser un objetivo SMART (específico, medible, alcanzable, relevante, temporal) relacionado con la capacitación en SST.',
    'objetivos_especificos' => 'Genera los objetivos específicos del programa. Para 7 estándares: 2-3 objetivos básicos. Para 21 estándares: 3-4 objetivos. Para 60 estándares: 4-5 objetivos. Deben ser SMART y relacionados con los peligros identificados.',
    'alcance' => 'Define el alcance del programa especificando: a quién aplica (trabajadores directos, contratistas), áreas o procesos cubiertos, sedes incluidas. Máximo 5-6 ítems para 7 est, 8 ítems para 21 est, 10 ítems para 60 est.',
    'marco_legal' => 'Lista el marco normativo aplicable al programa. Para 7 estándares: MÁXIMO 4-5 normas. Para 21 estándares: MÁXIMO 6-8 normas. Para 60 estándares: según aplique. NO uses tablas Markdown.',
    'definiciones' => 'Genera un glosario de términos técnicos. Para 7 estándares: MÁXIMO 8 términos esenciales. Para 21 estándares: MÁXIMO 12 términos. Para 60 estándares: 12-15 términos. Definiciones basadas en normativa colombiana.',
    'responsabilidades' => 'Define los roles y responsabilidades. Para 7 estándares: SOLO 3-4 roles (Representante Legal, Responsable SST, VIGÍA SST -no COPASST-, Trabajadores). Para 21 estándares: 5-6 roles (incluye COPASST). Para 60 estándares: todos los roles necesarios. Si son 7 estándares, NUNCA mencionar COPASST.',
    'metodologia' => 'Describe la metodología de capacitación incluyendo: tipos de capacitación (teórica, práctica), métodos de enseñanza, materiales y recursos, evaluación del aprendizaje.',
    'cronograma' => 'Genera un párrafo introductorio sobre el cronograma de capacitaciones. NO generes la tabla, se inserta automáticamente desde el sistema.',
    'plan_trabajo' => 'Resume las actividades del Plan de Trabajo Anual relacionadas con capacitación. NO generes tabla, se inserta automáticamente.',
    'indicadores' => 'Genera un párrafo introductorio sobre los indicadores. Para 7 estándares: 2-3 indicadores simples. Para 21 estándares: 4-5 indicadores. Para 60 estándares: 6-8 indicadores. NO generes tabla.',
    'recursos' => 'Identifica los recursos necesarios para el programa. Para 7 estándares: recursos MÍNIMOS. Para 21 estándares: recursos moderados. Para 60 estándares: recursos completos. Categorías: Humanos, Físicos, Financieros.',
    'evaluacion' => 'Define el mecanismo de seguimiento y evaluación. Para 7 estándares: seguimiento TRIMESTRAL o SEMESTRAL. Para 21 estándares: seguimiento BIMESTRAL o TRIMESTRAL. Para 60 estándares: según complejidad. Incluye criterios de evaluación y responsables.',
];

// ── Conexión ──
if ($isProd) {
    echo "=== PRODUCCIÓN ===\n";
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $l) {
        $l = trim($l);
        if ($l === '' || $l[0] === '#') continue;
        $parts = explode(' = ', $l, 2);
        if (count($parts) === 2) $env[trim($parts[0])] = trim($parts[1]);
    }
    $conn = mysqli_init();
    $conn->ssl_set(null, null, '/www/ca/ca-certificate_cycloid.crt', null, null);
    $conn->real_connect(
        $env['database.default.hostname'],
        $env['database.default.username'],
        $env['database.default.password'],
        'empresas_sst',
        (int)$env['database.default.port'],
        null,
        MYSQLI_CLIENT_SSL
    );
} else {
    echo "=== LOCAL ===\n";
    $conn = new mysqli('localhost', 'root', '', 'empresas_sst');
}

if ($conn->connect_error) {
    echo "ERROR conexión: " . $conn->connect_error . "\n";
    exit(1);
}

$conn->set_charset('utf8mb4');

// ── Obtener id_tipo_config ──
$r = $conn->query("SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'programa_capacitacion' AND activo = 1");
$row = $r->fetch_assoc();
if (!$row) {
    echo "ERROR: programa_capacitacion no encontrado en tbl_doc_tipo_configuracion\n";
    exit(1);
}
$idTipo = $row['id_tipo_config'];
echo "id_tipo_config = {$idTipo}\n\n";

// ── Actualizar prompts ──
$ok = 0;
$fail = 0;
$skip = 0;

foreach ($prompts as $key => $prompt) {
    // Verificar estado actual
    $stmt = $conn->prepare("SELECT id_seccion_config, prompt_ia FROM tbl_doc_secciones_config WHERE id_tipo_config = ? AND seccion_key = ?");
    $stmt->bind_param('is', $idTipo, $key);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$result) {
        echo "  SKIP {$key}: no existe el registro\n";
        $skip++;
        continue;
    }

    if (!empty(trim($result['prompt_ia'] ?? ''))) {
        echo "  SKIP {$key}: ya tiene prompt\n";
        $skip++;
        continue;
    }

    // Actualizar
    $stmt = $conn->prepare("UPDATE tbl_doc_secciones_config SET prompt_ia = ? WHERE id_seccion_config = ?");
    $id = $result['id_seccion_config'];
    $stmt->bind_param('si', $prompt, $id);

    if ($stmt->execute()) {
        echo "  OK   {$key} (id={$id})\n";
        $ok++;
    } else {
        echo "  FAIL {$key}: " . $stmt->error . "\n";
        $fail++;
    }
    $stmt->close();
}

echo "\nResumen: {$ok} actualizados, {$skip} omitidos, {$fail} fallidos\n";
$conn->close();

if ($fail > 0) exit(1);
echo "DONE\n";
