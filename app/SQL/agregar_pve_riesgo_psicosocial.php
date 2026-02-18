<?php
/**
 * Script para agregar tipo de documento: PVE de Riesgo Psicosocial
 * Estandar: 4.2.3
 * Ejecutar: php app/SQL/agregar_pve_riesgo_psicosocial.php
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

// SQL para tipo de documento
$sqlTipo = <<<'SQL'
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
VALUES
('pve_riesgo_psicosocial',
 'PVE de Riesgo Psicosocial',
 'Programa de Vigilancia Epidemiologica para la prevencion de efectos en la salud derivados de la exposicion a factores de riesgo psicosocial, incluyendo estres laboral, acoso, carga mental, jornadas laborales y relaciones interpersonales',
 '4.2.3',
 'programa_con_pta',
 'programas',
 'bi-brain',
 21)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), descripcion = VALUES(descripcion), updated_at = NOW();
SQL;

// SQL para secciones (12 secciones)
$sqlSecciones = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, s.tipo_contenido, s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 as numero, 'Portada' as nombre, 'portada' as seccion_key, 'texto' as tipo_contenido, 1 as orden,
           'Genera la portada del PVE de Riesgo Psicosocial. Debe incluir: nombre de la empresa, NIT, titulo del documento (Programa de Vigilancia Epidemiologica de Riesgo Psicosocial), codigo PVE-PSI-001, version, fecha de elaboracion, responsable SST. Cumplimiento del Decreto 1072/2015 y Resolucion 0312/2019 estandar 4.2.3.' as prompt_ia
    UNION SELECT 2, 'Objetivo', 'objetivo', 'texto', 2,
           'Genera el objetivo general y objetivos especificos del PVE de Riesgo Psicosocial. Objetivo general: prevenir los efectos negativos en la salud de los trabajadores derivados de la exposicion a factores de riesgo psicosocial intralaboral, extralaboral e individual. Objetivos especificos: aplicar la Bateria de Riesgo Psicosocial (Res. 2764/2022), identificar factores de riesgo y protectores, implementar programas de intervencion, realizar seguimiento a trabajadores en riesgo alto y muy alto, fomentar entornos laborales saludables, reducir estres laboral y sus efectos. Cumplimiento de Res. 2646/2008, Res. 2764/2022, Decreto 1072/2015 art. 2.2.4.6.24, Resolucion 0312/2019 estandar 4.2.3.'
    UNION SELECT 3, 'Alcance', 'alcance', 'texto', 3,
           'Define el alcance del PVE. Aplica a todos los trabajadores directos, contratistas, subcontratistas y temporales. Cubre los tres dominios de riesgo psicosocial: intralaboral (demandas del trabajo, control sobre el trabajo, liderazgo, recompensas), extralaboral (tiempo fuera del trabajo, relaciones familiares, comunicacion, situacion economica, vivienda, transporte), e individual (informacion sociodemografica, condiciones de salud, estrategias de afrontamiento). Aplica a todos los niveles jerarquicos de la organizacion.'
    UNION SELECT 4, 'Marco Legal', 'marco_legal', 'texto', 4,
           'Presenta marco normativo aplicable: Resolucion 2646/2008 (factores de riesgo psicosocial), Resolucion 2764/2022 (Bateria de Instrumentos, deroga Res. 2404/2019), Ley 1010/2006 (acoso laboral), Decreto 1072/2015 art. 2.2.4.6.24, Resolucion 0312/2019 estandar 4.2.3, Ley 1562/2012, Circular 064/2020 (acciones en promocion de salud mental), Resolucion 2346/2007 (evaluaciones medicas), Ley 1616/2013 (salud mental), Decreto 472/2015 (sanciones). Formato tabla con norma, descripcion y aplicacion al PVE.'
    UNION SELECT 5, 'Definiciones', 'definiciones', 'texto', 5,
           'Define terminos clave: Riesgo psicosocial, Factor de riesgo psicosocial intralaboral, Factor de riesgo psicosocial extralaboral, Factor individual, Estres laboral, Sindrome de burnout, Acoso laboral, Carga mental, Demandas del trabajo, Control sobre el trabajo, Liderazgo y relaciones sociales, Recompensas, Bateria de riesgo psicosocial, Nivel de riesgo (sin riesgo, bajo, medio, alto, muy alto), Intervencion primaria, Intervencion secundaria, Intervencion terciaria, Vigilancia epidemiologica, Protocolo de atencion. 17-20 definiciones.'
    UNION SELECT 6, 'Diagnostico Psicosocial', 'diagnostico_psicosocial', 'texto', 6,
           'Describe el diagnostico de condiciones de riesgo psicosocial: aplicacion de la Bateria de Instrumentos para la Evaluacion de Factores de Riesgo Psicosocial (Res. 2764/2022), que incluye: Cuestionario de factores intralaborales (forma A y B), Cuestionario de factores extralaborales, Cuestionario de estres, Ficha de datos generales. Condiciones de aplicacion: psicologo con licencia SST, confidencialidad, consentimiento informado, periodicidad minima anual. Analisis de resultados por dominios y dimensiones. Priorizacion de intervencion segun niveles de riesgo. Articulacion con diagnostico de condiciones de salud y perfil sociodemografico.'
    UNION SELECT 7, 'Poblacion Objeto', 'poblacion_objeto', 'texto', 7,
           'Define la poblacion objeto del PVE con criterios de inclusion: trabajadores con nivel de riesgo alto o muy alto en bateria psicosocial, trabajadores con diagnostico de patologia mental relacionada con el trabajo, trabajadores con sintomatologia de estres severo, trabajadores victimas de acoso laboral. Clasificacion en 3 niveles: Caso (patologia diagnosticada), Sospechoso (riesgo alto/muy alto sin diagnostico), Expuesto (riesgo medio o bajo). Criterios de ingreso, seguimiento y egreso del programa. Diferenciacion entre Forma A (jefes, profesionales, tecnicos) y Forma B (auxiliares, operarios).'
    UNION SELECT 8, 'Actividades del PVE', 'actividades_pve', 'texto', 8,
           'Genera las actividades del PVE organizadas en 3 niveles de intervencion: 1) Intervencion Primaria - promocion y prevencion (talleres de manejo del estres, comunicacion asertiva, trabajo en equipo, liderazgo positivo, equilibrio vida-trabajo, campanas de salud mental, sensibilizacion acoso laboral Ley 1010/2006), 2) Intervencion Secundaria - riesgo alto y muy alto (grupos focales, intervencion por dimensiones criticas, acompanamiento psicologico, seguimiento individual, remision EPS), 3) Intervencion Terciaria - casos confirmados (seguimiento a diagnosticados, articulacion EPS/ARL, reintegro laboral, seguimiento post-incapacidad). Cada actividad con responsable (psicologo con licencia SST), frecuencia, recurso y registro/evidencia.'
    UNION SELECT 9, 'Indicadores', 'indicadores', 'texto', 9,
           'Genera indicadores de gestion: Estructura (PVE documentado y aprobado, psicologo con licencia SST contratado, presupuesto asignado vs ejecutado), Proceso (% cobertura aplicacion bateria meta>=95%, % actividades de intervencion ejecutadas meta>=90%, % seguimiento a poblacion riesgo alto/muy alto meta=100%, % capacitaciones ejecutadas meta>=90%), Resultado (% reduccion nivel de riesgo psicosocial, tasa de prevalencia patologias mentales, % ausentismo por causa mental meta reduccion 10%, satisfaccion laboral meta>=70%, efectividad intervenciones meta>=60%). Con formula, meta, frecuencia medicion, fuente de informacion.'
    UNION SELECT 10, 'Cronograma', 'cronograma', 'texto', 10,
           'Genera cronograma anual de actividades por trimestres: T1 (planificacion, aplicacion bateria de riesgo psicosocial, analisis de resultados, clasificacion poblacion objeto), T2 (socializacion resultados, inicio actividades de intervencion primaria, talleres manejo estres, seguimiento casos), T3 (continuacion intervenciones, grupos focales riesgo alto, acompanamiento psicologico, capacitacion liderazgo positivo), T4 (segunda aplicacion bateria si aplica, evaluacion indicadores, comparativo con linea base, informe anual, planificacion siguiente periodo). Con responsable, recurso, registro y fecha estimada.'
    UNION SELECT 11, 'Responsabilidades', 'responsabilidades', 'texto', 11,
           'Define responsabilidades: Alta Direccion (aprobar PVE, asignar recursos, implementar politicas de bienestar, ambiente libre de acoso), Responsable SG-SST (coordinar aplicacion bateria, gestionar intervenciones, seguimiento indicadores, articular con ARL/EPS), Psicologo con licencia SST (aplicar bateria, analizar resultados, disenar e implementar intervenciones, confidencialidad, seguimiento casos), COPASST/Vigia (participar en socializacion, verificar intervenciones, recibir quejas de acoso laboral), Comite de Convivencia Laboral (tramitar quejas acoso, medidas preventivas y correctivas, seguimiento), ARL (asesoria tecnica, acompanamiento, capacitaciones), Trabajadores (participar en bateria y actividades, reportar situaciones, aplicar estrategias de afrontamiento).'
    UNION SELECT 12, 'Anexos', 'anexos', 'texto', 12,
           'Lista los anexos del PVE: Anexo 1 - Consentimiento Informado para Aplicacion de Bateria Psicosocial, Anexo 2 - Formato de Seguimiento Individual a Casos, Anexo 3 - Formato de Remision a EPS/ARL, Anexo 4 - Formato de Registro de Actividades de Intervencion, Anexo 5 - Formato de Evaluacion de Talleres y Capacitaciones, Anexo 6 - Protocolo de Actuacion ante Acoso Laboral, Anexo 7 - Formato de Seguimiento Post-Incapacidad por Causa Mental, Anexo 8 - Matriz de Priorizacion de Intervenciones por Dimensiones. Breve descripcion de cada anexo y su proposito.'
) s
WHERE tc.tipo_documento = 'pve_riesgo_psicosocial'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
SQL;

// SQL para firmantes
$sqlFirmantes = <<<'SQL'
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
SELECT tc.id_tipo_config, f.firmante_tipo, f.rol_display, f.columna_encabezado, f.orden, f.mostrar_licencia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 'responsable_sst' as firmante_tipo, 'Elaboro' as rol_display, 'Elaboro / Responsable del SG-SST' as columna_encabezado, 1 as orden, 1 as mostrar_licencia
    UNION SELECT 'representante_legal', 'Aprobo', 'Aprobo / Representante Legal', 2, 0
) f
WHERE tc.tipo_documento = 'pve_riesgo_psicosocial'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'PVE de Riesgo Psicosocial', 'PVE-PSI-001', 'pve_riesgo_psicosocial', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'pve_riesgo_psicosocial')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PVE-PSI-001', '4.2.3')
ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta);
SQL;

function ejecutarSQL($pdo, $sql, $nombre) {
    try {
        $pdo->exec($sql);
        echo "  [OK] $nombre\n";
        return true;
    } catch (PDOException $e) {
        echo "  [ERROR] $nombre: " . $e->getMessage() . "\n";
        return false;
    }
}

$localExito = false;

foreach ($conexiones as $entorno => $config) {
    echo "\n=== Ejecutando en $entorno ===\n";

    // Solo ejecutar produccion si local fue exitoso
    if ($entorno === 'produccion' && !$localExito) {
        echo "  [SKIP] No se ejecuta en produccion porque LOCAL fallo\n";
        continue;
    }

    if ($entorno === 'produccion' && empty($config['password'])) {
        echo "  [SKIP] Sin credenciales de produccion\n";
        continue;
    }

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "  [OK] Conexion establecida\n";

        // Ejecutar SQLs en orden
        $ok = true;
        $ok = ejecutarSQL($pdo, $sqlTipo, 'Tipo de documento') && $ok;
        $ok = ejecutarSQL($pdo, $sqlSecciones, 'Secciones (12)') && $ok;
        $ok = ejecutarSQL($pdo, $sqlFirmantes, 'Firmantes') && $ok;
        $ok = ejecutarSQL($pdo, $sqlPlantilla, 'Plantilla') && $ok;
        $ok = ejecutarSQL($pdo, $sqlMapeoCarpeta, 'Mapeo carpeta') && $ok;

        // Verificar resultado
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'pve_riesgo_psicosocial'");
        $tipo = $stmt->fetch();
        if ($tipo) {
            echo "  [INFO] Tipo creado con ID: {$tipo['id_tipo_config']}\n";

            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_secciones_config WHERE id_tipo_config = {$tipo['id_tipo_config']}");
            $secciones = $stmt->fetch();
            echo "  [INFO] Secciones configuradas: {$secciones['total']}\n";
        }

        if ($entorno === 'local') {
            $localExito = $ok;
        }

    } catch (PDOException $e) {
        echo "  [ERROR] Conexion: " . $e->getMessage() . "\n";
        if ($entorno === 'local') {
            $localExito = false;
        }
    }
}

echo "\n=== Proceso completado ===\n";
