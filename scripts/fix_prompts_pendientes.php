<?php
/**
 * FIX: Insertar prompt_ia faltantes en:
 *   1) manual_convivencia_laboral (16 secciones sin prompt)
 *   2) procedimiento_control_documental → documentos_externos (1 sección sin prompt)
 *
 * Uso LOCAL:   php scripts/fix_prompts_pendientes.php
 * Uso PROD:    php scripts/fix_prompts_pendientes.php --prod
 */

$isProd = in_array('--prod', $argv ?? []);

// ── Prompts a insertar ──
$fixes = [
    'manual_convivencia_laboral' => [
        'introduccion_proposito' => 'Genera una introducción y propósito del Manual de Convivencia Laboral. Incluye: importancia de la convivencia en el ambiente laboral, compromiso de la empresa con un entorno respetuoso, referencia a la Ley 1010 de 2006 (acoso laboral) y Resolución 2646 de 2008. Ajusta según tamaño de empresa.',
        'fundamentacion_normativa' => 'Lista el marco normativo del Manual de Convivencia Laboral. Incluye: Ley 1010 de 2006, Resolución 2646 de 2008, Resolución 652 de 2012 (Comité de Convivencia), Resolución 1356 de 2012, Ley 1257 de 2008, Código Sustantivo del Trabajo. Para 7 estándares: MÁXIMO 5 normas. Para 21 estándares: MÁXIMO 8 normas. Para 60 estándares: según aplique. NO uses tablas Markdown.',
        'objetivo_principal' => 'Genera el objetivo principal del Manual de Convivencia Laboral. Debe establecer las normas de comportamiento y convivencia que garanticen un ambiente de trabajo respetuoso, libre de acoso laboral y violencia. Redacta como objetivo SMART.',
        'objetivos_generales' => 'Genera los objetivos generales del manual. Para 7 estándares: 2-3 objetivos básicos. Para 21 estándares: 3-4 objetivos. Para 60 estándares: 4-5 objetivos. Deben cubrir: prevención del acoso, promoción del respeto, mecanismos de resolución de conflictos.',
        'alcance' => 'Define el alcance del manual especificando: a quién aplica (trabajadores directos, contratistas, temporales, practicantes), áreas cubiertas, relaciones interpersonales incluidas (entre pares, jefe-subordinado, con terceros). Máximo 5 ítems para 7 est, 8 ítems para 21 est.',
        'valores_corporativos' => 'Genera los valores corporativos que fundamentan la convivencia laboral. Incluye: respeto, tolerancia, honestidad, responsabilidad, solidaridad, equidad. Para 7 estándares: 4-5 valores con descripción breve. Para 21 estándares: 6-7 valores. Para 60 estándares: 7-8 valores. Cada valor con una breve descripción de cómo se aplica en el entorno laboral.',
        'conductas_aceptables' => 'Genera la lista de conductas aceptables en el entorno laboral. Incluye: trato respetuoso, comunicación asertiva, trabajo en equipo, puntualidad, uso adecuado de recursos, respeto por la diversidad. Para 7 estándares: 6-8 conductas. Para 21 estándares: 8-10 conductas. Para 60 estándares: 10-12 conductas.',
        'conductas_no_aceptables' => 'Genera la lista de conductas NO aceptables en el entorno laboral según la Ley 1010 de 2006. Incluye modalidades de acoso: maltrato laboral, persecución laboral, discriminación laboral, entorpecimiento laboral, inequidad laboral, desprotección laboral. Para 7 estándares: 6-8 conductas. Para 21 estándares: 8-10. Para 60 estándares: 10-12.',
        'comportamientos_prohibidos' => 'Genera los comportamientos expresamente prohibidos. Incluye: agresión física o verbal, amenazas, intimidación, acoso sexual, discriminación por género/raza/religión/orientación, uso de sustancias psicoactivas en el trabajo, divulgación de información confidencial. Para 7 estándares: 5-7 comportamientos. Para 21 estándares: 7-9. Para 60 estándares: 9-12.',
        'seguridad_laboral' => 'Genera las normas de seguridad laboral relacionadas con la convivencia. Incluye: uso de EPP, reporte de condiciones inseguras, respeto de señalización, no interferir con equipos de seguridad, participar en simulacros y capacitaciones de emergencia. Ajusta según nivel de riesgo de la empresa.',
        'resolucion_conflictos' => 'Genera el procedimiento de resolución de conflictos. Incluye: diálogo directo entre las partes, mediación del jefe inmediato, intervención del Comité de Convivencia Laboral (Resolución 652 de 2012), remisión a instancias externas si persiste. Para 7 estándares: procedimiento simplificado. Para 21 estándares: procedimiento detallado con plazos.',
        'procedimiento_reportes' => 'Genera el procedimiento para reportar conductas de acoso o violencia. Incluye: canales de denuncia (verbal, escrito, buzón), confidencialidad, protección al denunciante, plazos de atención, rol del Comité de Convivencia Laboral. Referencia a Resolución 652 de 2012 y Ley 1010 de 2006.',
        'sanciones' => 'Genera el régimen de sanciones y procedimiento disciplinario. Incluye: amonestación verbal, amonestación escrita, suspensión, terminación del contrato con justa causa. Referencia al Código Sustantivo del Trabajo y al Reglamento Interno de Trabajo. Menciona el debido proceso y derecho a la defensa.',
        'roles_responsabilidades' => 'Define los roles y responsabilidades en la convivencia laboral. Para 7 estándares: 3-4 roles (Representante Legal, Responsable SST, Vigía SST, Trabajadores). Para 21 estándares: 5-6 roles (incluye Comité de Convivencia, COPASST). Para 60 estándares: todos los roles necesarios. Si son 7 estándares, usar Vigía SST en vez de COPASST.',
        'difusion_manual' => 'Genera el plan de difusión del manual. Incluye: socialización en inducción y reinducción, publicación en carteleras y medios internos, firma de conocimiento por cada trabajador, actualización periódica. Para 7 estándares: difusión básica. Para 21 estándares: incluir capacitaciones específicas.',
        'aceptacion_compromiso' => 'Genera la cláusula de aceptación y compromiso que firma cada trabajador. Incluye: declaración de haber leído y comprendido el manual, compromiso de cumplimiento, aceptación de las consecuencias por incumplimiento. Redactar en primera persona del trabajador.',
    ],
    'procedimiento_control_documental' => [
        'documentos_externos' => 'Genera el procedimiento para la gestión de documentos externos del SG-SST. Incluye: identificación de documentos externos aplicables (normas, leyes, guías técnicas), proceso de recepción y registro, control de vigencia, distribución a las áreas pertinentes, actualización cuando cambia la normativa. Referencia al Decreto 1072 de 2015 Artículo 2.2.4.6.12.',
    ],
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

// ── Procesar cada tipo ──
$totalOk = 0;
$totalFail = 0;
$totalSkip = 0;

foreach ($fixes as $tipoDocumento => $prompts) {
    echo "\n--- {$tipoDocumento} ---\n";

    // Obtener id_tipo_config
    $r = $conn->query("SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento = '{$tipoDocumento}' AND activo = 1");
    $row = $r->fetch_assoc();
    if (!$row) {
        echo "  ERROR: '{$tipoDocumento}' no encontrado en tbl_doc_tipo_configuracion\n";
        $totalFail += count($prompts);
        continue;
    }
    $idTipo = $row['id_tipo_config'];

    foreach ($prompts as $key => $prompt) {
        $stmt = $conn->prepare("SELECT id_seccion_config, prompt_ia FROM tbl_doc_secciones_config WHERE id_tipo_config = ? AND seccion_key = ?");
        $stmt->bind_param('is', $idTipo, $key);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$result) {
            echo "  SKIP {$key}: no existe el registro\n";
            $totalSkip++;
            continue;
        }

        if (!empty(trim($result['prompt_ia'] ?? ''))) {
            echo "  SKIP {$key}: ya tiene prompt\n";
            $totalSkip++;
            continue;
        }

        $stmt = $conn->prepare("UPDATE tbl_doc_secciones_config SET prompt_ia = ? WHERE id_seccion_config = ?");
        $id = $result['id_seccion_config'];
        $stmt->bind_param('si', $prompt, $id);

        if ($stmt->execute()) {
            echo "  OK   {$key} (id={$id})\n";
            $totalOk++;
        } else {
            echo "  FAIL {$key}: " . $stmt->error . "\n";
            $totalFail++;
        }
        $stmt->close();
    }
}

echo "\nResumen: {$totalOk} actualizados, {$totalSkip} omitidos, {$totalFail} fallidos\n";
$conn->close();

if ($totalFail > 0) exit(1);
echo "DONE\n";
