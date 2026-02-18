<?php
/**
 * Agrega secciones y firmantes para:
 *   - plan_emergencias (estandar 5.1.1) — 12 secciones
 *   - reglamento_higiene_seguridad (estandar 1.1.2) — 11 secciones
 *
 * Los tipos ya existen en tbl_doc_tipo_configuracion.
 * Este script solo inserta en tbl_doc_secciones_config + tbl_doc_firmantes_config.
 * Ejecutar: php app/SQL/agregar_secciones_plan_emergencias_y_reglamento.php
 */

$conexiones = [
    'local' => [
        'host' => 'localhost', 'port' => 3306,
        'database' => 'empresas_sst', 'username' => 'root', 'password' => '', 'ssl' => false
    ],
    'produccion' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com', 'port' => 25060,
        'database' => 'empresas_sst', 'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG', 'ssl' => true
    ]
];

// ─────────────────────────────────────────────────────────────────────────────
// PLAN EMERGENCIAS — 12 secciones
// ─────────────────────────────────────────────────────────────────────────────
$sqlPlanEmergenciasSecciones = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, s.tipo_contenido, s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 AS numero, 'Objetivo y Alcance' AS nombre, 'objetivo_alcance' AS seccion_key,
           'texto' AS tipo_contenido, 1 AS orden,
           'Genera el objetivo general y el alcance del Plan de Prevención, Preparación y Respuesta ante Emergencias. El objetivo debe orientarse a proteger la vida e integridad de los trabajadores y garantizar la continuidad operativa. El alcance debe especificar a quiénes aplica (trabajadores directos, contratistas, visitantes) y en qué instalaciones. Referencia normativa: Resolución 0312/2019 estándar 5.1.1, Decreto 1072/2015 art. 2.2.4.6.25. Máximo 2 párrafos.' AS prompt_ia
    UNION SELECT 2, 'Marco Legal', 'marco_legal', 'texto', 2,
           'Genera el marco legal del Plan de Emergencias de la empresa. Incluir: Resolución 0312/2019 estándar 5.1.1, Decreto 1072/2015 art. 2.2.4.6.25, Ley 1523/2012 (Política Nacional Gestión del Riesgo), Resolución 256/2014 (Brigadas de emergencias), NTC 1410 y NTC 3807 (señalización), GTC 45 (análisis de vulnerabilidad). Formato tabla con columnas: Norma | Descripción | Aplicación al plan.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', 3,
           'Define los términos clave del Plan de Emergencias: Amenaza, Riesgo, Vulnerabilidad, Emergencia, Desastre, Evacuación, Brigadista, Punto de encuentro, Simulacro, Incidente, Primeros auxilios, Plan de respuesta, Coordinador de emergencias, CREPAD/CLOPAD. Mínimo 12 definiciones.'
    UNION SELECT 4, 'Identificación de Amenazas', 'identificacion_amenazas', 'texto', 4,
           'Identifica y clasifica las amenazas para la empresa según su actividad económica, ubicación geográfica y nivel de riesgo. Clasificar en: Amenazas naturales (sismo, vendaval, inundación, granizo), Amenazas antrópicas no intencionales (incendio, explosión, derrame), Amenazas antrópicas intencionales (robo, sabotaje), Amenazas tecnológicas (falla sistemas). Para cada amenaza indicar: descripción, probabilidad (baja/media/alta) y tipo de daño esperado. Personalizar según la actividad económica y ubicación de la empresa.'
    UNION SELECT 5, 'Análisis de Vulnerabilidad', 'analisis_vulnerabilidad', 'texto', 5,
           'Realiza el análisis de vulnerabilidad evaluando tres elementos: 1) Personas (trabajadores, visitantes): nivel de capacitación, número en el área, movilidad. 2) Recursos (bienes, infraestructura): estado de la edificación, sistemas de protección, materiales presentes. 3) Sistemas y procesos (organización, servicios públicos): procedimientos, comunicaciones, red de apoyo. Calificar cada elemento como: Bueno (1 punto), Regular (0.5 puntos), Malo (0 puntos). Clasificar nivel de vulnerabilidad por amenaza: Baja (2.1-3.0), Media (1.1-2.0), Alta (0-1.0).'
    UNION SELECT 6, 'Organización para Emergencias (Brigadas)', 'organizacion_brigadas', 'texto', 6,
           'Describe la estructura organizacional para la atención de emergencias. Incluir: Coordinador General de Emergencias (rol, funciones), Brigada contra incendios (funciones: uso de extintores, control del fuego, evacuación), Brigada de primeros auxilios (funciones: atención básica pre-hospitalaria, estabilización), Brigada de evacuación y rescate (funciones: guía de evacuación, conteo de personal, rescate). Criterios de selección de brigadistas, proceso de capacitación (mínimo 16 horas según Res. 256/2014), reconocimiento. Línea de mando en emergencias.'
    UNION SELECT 7, 'Procedimientos de Emergencia', 'procedimientos_emergencia', 'texto', 7,
           'Genera los procedimientos de respuesta para cada tipo de emergencia identificada. Para cada procedimiento incluir: ¿Quién activa?, pasos de acción (numerados), ¿A quién avisar?, recursos necesarios. Procedimientos mínimos: 1) Incendio, 2) Sismo/Terremoto, 3) Amenaza de bomba, 4) Derrame de sustancias (si aplica), 5) Emergencia médica. Incluir el procedimiento DETECTAR-ALERTAR-EVALUAR-ACTUAR-EVACUAR-AYUDAR como secuencia general. Números de emergencia: Bomberos 119, Policía 123, Cruz Roja 132, Ambulancia 125.'
    UNION SELECT 8, 'Plan de Evacuación', 'plan_evacuacion', 'texto', 8,
           'Detalla el plan de evacuación de las instalaciones. Incluir: Señalización de rutas de evacuación (requisitos de señales, iluminación de emergencia), Puntos de encuentro (primario y alterno), Procedimiento paso a paso de evacuación (desde la alarma hasta el conteo en punto de encuentro), Responsabilidades durante evacuación (trabajadores, brigadistas, coordinador), Tiempo objetivo de evacuación (máximo 5 minutos para edificaciones de hasta 3 pisos), Procedimiento de verificación de personal evacuado, Criterios de regreso a instalaciones.'
    UNION SELECT 9, 'Comunicaciones de Emergencia', 'comunicaciones_emergencia', 'texto', 9,
           'Define el sistema de comunicaciones internas y externas durante una emergencia. Incluir: Tipos de alarma (sonora, visual) y significado de cada señal, Protocolo de activación de la alarma, Directorio completo de emergencias (Bomberos, Policía, Cruz Roja, ARL, EPS, DIAN, gerencia), Comunicación interna entre coordinador y brigadistas, Comunicación post-emergencia (reporte a ARL en 2 días hábiles, reporte al Ministerio si hay muerte/incapacidad permanente), Uso de medios alternativos si fallan los principales.'
    UNION SELECT 10, 'Equipos y Recursos para Emergencias', 'equipos_recursos', 'texto', 10,
           'Inventario y normas de mantenimiento de equipos de emergencia. Incluir por categoría: 1) Equipos contra incendios: extintores (tipo, capacidad, ubicación, inspección mensual, recarga anual), detectores de humo, red contra incendios. 2) Equipos de primeros auxilios: botiquines (contenido mínimo, ubicación, revisión mensual). 3) Equipos de evacuación: camillas, linternas de emergencia, silbatos, chalecos para brigadistas. 4) Señalización: señales de evacuación, salidas de emergencia, equipos contra incendio. Especificar responsable de inspección y frecuencia.'
    UNION SELECT 11, 'Capacitación y Simulacros', 'capacitacion_simulacros', 'texto', 11,
           'Plan anual de capacitación y simulacros. Capacitaciones: Formación inicial de brigadistas (16 horas según Res. 256/2014), actualización anual brigadas (8 horas), inducción a nuevos trabajadores (conocimiento del plan), primeros auxilios básicos para todos (4 horas mínimo). Simulacros: Mínimo 1 simulacro general al año (obligatorio por norma), simulacro parcial recomendado semestral, cronograma anual de simulacros, proceso de evaluación post-simulacro (tiempo de evacuación, conteo de personas, lecciones aprendidas), indicadores de efectividad (tiempo meta ≤ 5 min).'
    UNION SELECT 12, 'Investigación Post-Emergencia', 'investigacion_post_emergencia', 'texto', 12,
           'Procedimiento de investigación y recuperación post-emergencia. Fases: 1) Inmediata (0-24h): verificar heridos, asegurar área, evaluar daños. 2) Investigación (1-8 días): determinar causa raíz, identificar fallas en el plan, entrevistar testigos, documentar con registro fotográfico. 3) Acciones correctivas: plan de mejora, actualización del plan de emergencias, reposición de equipos, refuerzo de capacitaciones. 4) Reporte: a la ARL (accidentes AT), al Ministerio del Trabajo (accidentes graves), a la gerencia (informe ejecutivo). Registro en el histórico de emergencias. Formato de seguimiento con fechas de cierre.'
) s
WHERE tc.tipo_documento = 'plan_emergencias'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
SQL;

$sqlPlanEmergenciasFirmantes = <<<'SQL'
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
SELECT tc.id_tipo_config, f.firmante_tipo, f.rol_display, f.columna_encabezado, f.orden, f.mostrar_licencia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 'responsable_sst' AS firmante_tipo, 'Elaboro' AS rol_display,
           'Elaboro / Responsable del SG-SST' AS columna_encabezado, 1 AS orden, 1 AS mostrar_licencia
    UNION SELECT 'representante_legal', 'Aprobo', 'Aprobo / Representante Legal', 2, 0
) f
WHERE tc.tipo_documento = 'plan_emergencias'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// ─────────────────────────────────────────────────────────────────────────────
// REGLAMENTO HIGIENE Y SEGURIDAD — 11 secciones
// ─────────────────────────────────────────────────────────────────────────────
$sqlReglamentoSecciones = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, s.tipo_contenido, s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 AS numero, 'Prescripciones Generales' AS nombre, 'prescripciones_generales' AS seccion_key,
           'texto' AS tipo_contenido, 1 AS orden,
           'Genera las prescripciones generales del Reglamento de Higiene y Seguridad Industrial para la empresa. Debe mencionar: razón social, NIT, actividad económica, número de trabajadores, domicilio. Base normativa: Código Sustantivo del Trabajo arts. 349-352, Resolución 1016/1989, Decreto 1072/2015, Resolución 0312/2019 estándar 1.1.2. Indicar que es de obligatorio cumplimiento para todos los trabajadores directos, contratistas, aprendices y visitantes. Mencionar que el trabajador debe leer y firmar el reglamento al ingresar.' AS prompt_ia
    UNION SELECT 2, 'Obligaciones del Empleador', 'obligaciones_empleador', 'texto', 2,
           'Genera las obligaciones del empleador en materia de higiene y seguridad según la normatividad colombiana vigente (Decreto 1072/2015, Resolución 0312/2019). Incluir: afiliación al SGRL desde primer día, suministro de EPP sin costo, investigación de accidentes, reporte a ARL en 2 días hábiles, exámenes médicos ocupacionales, capacitaciones en SST, conformación del COPASST o Vigía SST, implementación y mantenimiento del SG-SST, evaluaciones médicas de ingreso/periódicas/retiro, garantizar condiciones seguras de trabajo. Mínimo 10 obligaciones.'
    UNION SELECT 3, 'Obligaciones de los Trabajadores', 'obligaciones_trabajadores', 'texto', 3,
           'Genera las obligaciones de los trabajadores en materia de higiene y seguridad. Incluir: cumplimiento del reglamento y normas de SST, uso de EPP asignado, reporte inmediato de accidentes/incidentes/condiciones inseguras, no operar equipos sin autorización, asistencia a capacitaciones, no trabajar bajo influencia de alcohol o drogas, mantener orden y limpieza, participar en simulacros, cooperar en investigaciones. Base: Decreto 1072/2015 art. 2.2.4.6.10, Código Sustantivo del Trabajo. Mínimo 10 obligaciones claras.'
    UNION SELECT 4, 'Medidas de Higiene Industrial', 'higiene_industrial', 'texto', 4,
           'Genera las medidas de higiene industrial aplicables en la empresa. Incluir: higiene personal (lavado de manos, ropa de trabajo adecuada), condiciones sanitarias del lugar de trabajo (servicios sanitarios según Resolución 2400/1979: 1 por cada 15 trabajadores hombres, dotación de agua potable), iluminación mínima según GTC 8 (500 lux puestos trabajo, 300 lux pasillos), ventilación y control de temperatura, control de ruido ocupacional (límite 85 dB en 8 horas según Resolución 1792/1990), control de sustancias químicas peligrosas (fichas SDS, almacenamiento), manejo de residuos. Adaptar a la actividad económica de la empresa.'
    UNION SELECT 5, 'Medidas de Seguridad Industrial', 'seguridad_industrial', 'texto', 5,
           'Genera las medidas de seguridad industrial para el control de los principales riesgos en la empresa. Incluir: identificación previa de peligros antes de iniciar tareas, aplicación de análisis de trabajo seguro (ATS) en tareas críticas, sistemas de permisos de trabajo para actividades de alto riesgo (alturas, espacios confinados, trabajo en caliente), control de energías peligrosas mediante LOTO (Lockout/Tagout), normas para trabajo en alturas si aplica (Resolución 4272/2021: certificación obligatoria, arnés, eslinga, puntos de anclaje), manejo manual de cargas seguro (límites de peso: hombres 25 kg, mujeres 12.5 kg). Adaptar a riesgos específicos de la actividad económica.'
    UNION SELECT 6, 'Normas para Uso de Equipos y Maquinaria', 'uso_equipos_maquinaria', 'texto', 6,
           'Genera las normas para el uso seguro de equipos, maquinaria y herramientas. Incluir: Solo personal capacitado y autorizado puede operar, inspección pre-operacional obligatoria antes de cada uso, prohibición de remover guardas de seguridad o dispositivos de protección, reporte inmediato de fallas, prohibición de mantenimiento con equipo energizado (aplicar LOTO), requisito de polo a tierra en equipos eléctricos, estado de herramientas manuales (sin mangos rotos), prohibición de uso de celulares durante operación, seguimiento de manuales del fabricante, hoja de vida de cada equipo crítico.'
    UNION SELECT 7, 'Elementos de Protección Personal', 'elementos_proteccion_personal', 'texto', 7,
           'Genera la política y normas de uso de Elementos de Protección Personal (EPP). Incluir: principio de jerarquía de controles (EPP es la última barrera), obligación de la empresa de suministrar EPP sin costo, obligación del trabajador de usarlos cuando esté expuesto al riesgo, prohibición de prestar EPP de uso personal, EPP mínimos por área/cargo según actividad económica de la empresa (casco, calzado de seguridad, guantes, gafas, protección auditiva, respiratoria según aplique), normas de mantenimiento y reposición, certificación de EPP (norma ANSI/ICONTEC aplicable), registro de entrega de EPP.'
    UNION SELECT 8, 'Señalización y Demarcación', 'senalizacion_demarcacion', 'texto', 8,
           'Genera las normas de señalización y demarcación de áreas. Incluir: sistema de colores de seguridad según NTC 1461 (rojo=prohibición/peligro, amarillo=advertencia/precaución, verde=seguridad/evacuación, azul=obligación), tipos de señales requeridas (prohibición, advertencia, obligación, información, emergencia), demarcación de pasillos y vías de circulación (ancho mínimo 90 cm, franjas amarillas de 10 cm), demarcación de áreas de almacenamiento, señalización de equipos contra incendios, señalización de rutas de evacuación y salidas de emergencia (fotoluminiscentes), señalización de riesgos eléctricos. Mantenimiento mensual de señalización.'
    UNION SELECT 9, 'Orden y Limpieza', 'orden_limpieza', 'texto', 9,
           'Genera las normas de orden y limpieza basadas en el programa 5S. Incluir las 5 fases: Seiri (clasificar/eliminar lo innecesario), Seiton (ordenar: un lugar para cada cosa), Seiso (limpiar e inspeccionar diariamente), Seiketsu (estandarizar procedimientos de limpieza), Shitsuke (disciplina para mantener lo logrado). Normas específicas obligatorias: pasillos y rutas de evacuación siempre despejados, residuos en recipientes correctos (segregación según tipo), derrames limpiados inmediatamente, materiales no apilados en zonas no demarcadas, cables eléctricos sin cruzar pasillos, responsables de limpieza por área y frecuencia.'
    UNION SELECT 10, 'Procedimiento ante Accidente', 'procedimiento_accidente', 'texto', 10,
           'Genera el procedimiento paso a paso ante un accidente de trabajo en la empresa. Dividir en: 1) Respuesta inmediata (primeros 5 minutos): dar alarma, llamar brigadista de primeros auxilios, proteger área para evitar más accidentados, proporcionar primeros auxilios, llamar ambulancia si necesario (125), NO mover al lesionado si se sospecha lesión en columna. 2) Notificación (dentro de 2 horas): notificar al supervisor. 3) Reporte (dentro de 2 días hábiles): reportar a la ARL, si hay muerte o incapacidad permanente reportar también al Ministerio del Trabajo. 4) Investigación (dentro de 15 días): equipo investigador, causas inmediatas y básicas, medidas correctivas con responsables y fechas. Formato FURAT.'
    UNION SELECT 11, 'Sanciones por Incumplimiento', 'sanciones', 'texto', 11,
           'Genera el sistema de sanciones disciplinarias por incumplimiento del reglamento. Incluir 4 niveles: Nivel 1 Amonestación verbal (faltas leves, primera vez: desorden área trabajo, omisiones menores). Nivel 2 Amonestación escrita (reincidencia falta leve o primera falta moderada: no uso de EPP, no reporte de incidentes). Nivel 3 Suspensión sin sueldo 1-5 días (faltas graves: retirar guardas seguridad, operar sin autorización, presentarse bajo influencia de alcohol/drogas). Nivel 4 Despido con justa causa según CST art. 62 (faltas muy graves: poner en peligro vida propia o de otros, reincidencia después de suspensión, agresión física). Garantizar derecho de defensa y debido proceso en todo procedimiento.'
) s
WHERE tc.tipo_documento = 'reglamento_higiene_seguridad'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
SQL;

$sqlReglamentoFirmantes = <<<'SQL'
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
SELECT tc.id_tipo_config, f.firmante_tipo, f.rol_display, f.columna_encabezado, f.orden, f.mostrar_licencia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 'representante_legal' AS firmante_tipo, 'Aprobó' AS rol_display,
           'Aprobó / Representante Legal' AS columna_encabezado, 1 AS orden, 0 AS mostrar_licencia
    UNION SELECT 'responsable_sst', 'Elaboró', 'Elaboró / Responsable del SG-SST', 2, 1
) f
WHERE tc.tipo_documento = 'reglamento_higiene_seguridad'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// ─────────────────────────────────────────────────────────────────────────────
// Ejecución
// ─────────────────────────────────────────────────────────────────────────────
function ejecutarSQL($pdo, string $sql, string $nombre): bool
{
    try {
        $pdo->exec($sql);
        echo "  [OK] $nombre\n";
        return true;
    } catch (PDOException $e) {
        echo "  [ERROR] $nombre: " . $e->getMessage() . "\n";
        return false;
    }
}

function verificarDoc($pdo, string $tipo): void
{
    $stmt = $pdo->query("SELECT dtc.id_tipo_config,
        (SELECT COUNT(*) FROM tbl_doc_secciones_config WHERE id_tipo_config = dtc.id_tipo_config) as secciones,
        (SELECT COUNT(*) FROM tbl_doc_firmantes_config WHERE id_tipo_config = dtc.id_tipo_config) as firmantes
        FROM tbl_doc_tipo_configuracion dtc WHERE dtc.tipo_documento = '{$tipo}'");
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) {
        echo "  [INFO] {$tipo}: ID={$r['id_tipo_config']}, secciones={$r['secciones']}, firmantes={$r['firmantes']}\n";
    } else {
        echo "  [WARN] No se encontró {$tipo} en tbl_doc_tipo_configuracion\n";
    }
}

$localExito = false;

foreach ($conexiones as $entorno => $config) {
    echo "\n=== Ejecutando en $entorno ===\n";

    if ($entorno === 'produccion' && !$localExito) {
        echo "  [SKIP] No se ejecuta en produccion porque LOCAL fallo\n";
        continue;
    }

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "  [OK] Conexion establecida\n";

        $ok = true;

        // Plan Emergencias
        echo "\n  -- plan_emergencias --\n";
        $ok = ejecutarSQL($pdo, $sqlPlanEmergenciasSecciones, 'Secciones plan_emergencias (12)') && $ok;
        $ok = ejecutarSQL($pdo, $sqlPlanEmergenciasFirmantes, 'Firmantes plan_emergencias') && $ok;
        verificarDoc($pdo, 'plan_emergencias');

        // Reglamento Higiene y Seguridad
        echo "\n  -- reglamento_higiene_seguridad --\n";
        $ok = ejecutarSQL($pdo, $sqlReglamentoSecciones, 'Secciones reglamento_higiene_seguridad (11)') && $ok;
        $ok = ejecutarSQL($pdo, $sqlReglamentoFirmantes, 'Firmantes reglamento_higiene_seguridad') && $ok;
        verificarDoc($pdo, 'reglamento_higiene_seguridad');

        if ($entorno === 'local') $localExito = $ok;

    } catch (PDOException $e) {
        echo "  [ERROR] Conexion: " . $e->getMessage() . "\n";
        if ($entorno === 'local') $localExito = false;
    }
}

echo "\n=== Proceso completado ===\n";
