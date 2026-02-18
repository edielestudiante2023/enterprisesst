<?php
/**
 * Script para agregar tipo de documento: PVE de Riesgo Biomecanico
 * Estandar: 4.2.3
 * Ejecutar: php app/SQL/agregar_pve_riesgo_biomecanico.php
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
('pve_riesgo_biomecanico',
 'PVE de Riesgo Biomecanico',
 'Programa de Vigilancia Epidemiologica para la prevencion de desordenes musculoesqueleticos asociados a factores de riesgo biomecanico, incluyendo posturas, movimientos repetitivos, manipulacion manual de cargas y vibraciones',
 '4.2.3',
 'programa_con_pta',
 'programas',
 'bi-body-text',
 20)
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
           'Genera la portada del PVE de Riesgo Biomecanico. Debe incluir: nombre de la empresa, NIT, titulo del documento (Programa de Vigilancia Epidemiologica de Riesgo Biomecanico), codigo PVE-BIO-001, version, fecha de elaboracion, responsable SST. Cumplimiento del Decreto 1072/2015 y Resolucion 0312/2019 estandar 4.2.3.' as prompt_ia
    UNION SELECT 2, 'Objetivo', 'objetivo', 'texto', 2,
           'Genera el objetivo general y objetivos especificos del PVE de Riesgo Biomecanico. Objetivo general: prevenir la aparicion de desordenes musculoesqueleticos (DME) mediante la identificacion, evaluacion y control de factores de riesgo biomecanico. Objetivos especificos: identificar factores de riesgo (posturas, movimientos repetitivos, manipulacion de cargas, vibraciones), realizar vigilancia medica periodica, implementar medidas de control ergonomico, capacitar en higiene postural y manejo de cargas. Cumplimiento de Decreto 1072/2015 art. 2.2.4.6.24, Resolucion 0312/2019 estandar 4.2.3, GTC 45.'
    UNION SELECT 3, 'Alcance', 'alcance', 'texto', 3,
           'Define el alcance del PVE. Aplica a todos los trabajadores directos, contratistas, subcontratistas y temporales expuestos a factores de riesgo biomecanico. Cubre cargos administrativos (trabajo prolongado con VDT, posturas sedentes), cargos operativos (manipulacion manual de cargas, movimientos repetitivos, posturas forzadas), personal de mantenimiento, conductores. Incluye componente ambiental (puesto de trabajo) y componente medico (trabajador).'
    UNION SELECT 4, 'Marco Legal', 'marco_legal', 'texto', 4,
           'Presenta marco normativo aplicable: Decreto 1072/2015 art. 2.2.4.6.24 (medidas de prevencion y control), Resolucion 0312/2019 estandar 4.2.3 (medidas de prevencion y control frente a peligros), Ley 1562/2012 (Sistema General de Riesgos Laborales), Resolucion 2346/2007 (evaluaciones medicas ocupacionales), Resolucion 2844/2007 (GATISO DME), GTC 45 (identificacion de peligros), NTC 5723 (ergonomia evaluacion posturas), Resolucion 2400/1979 (disposiciones sobre vivienda, higiene y seguridad). Formato tabla con norma, descripcion y aplicacion al PVE.'
    UNION SELECT 5, 'Definiciones', 'definiciones', 'texto', 5,
           'Define terminos clave: Riesgo biomecanico, Desorden musculoesqueletico (DME), Postura forzada, Postura prolongada, Postura mantenida, Movimiento repetitivo, Manipulacion manual de cargas, Ergonomia, Higiene postural, Carga fisica, Fatiga muscular, Sindrome del tunel carpiano, Epicondilitis, Lumbalgia, Tendinitis, Puesto de trabajo con VDT, Pausas activas, Vigilancia epidemiologica. 16-18 definiciones.'
    UNION SELECT 6, 'Diagnostico Biomecanico', 'diagnostico_biomecanico', 'texto', 6,
           'Describe el diagnostico de condiciones de riesgo biomecanico: fuentes de informacion (matriz de peligros, inspecciones de puestos de trabajo, evaluaciones medicas, autoreporte de condiciones, estadisticas de ausentismo por DME, encuestas de morbilidad sentida). Herramientas de evaluacion: metodo RULA, metodo REBA, ecuacion NIOSH, Check List OCRA, cuestionario nordico. Clasificacion de nivel de riesgo por cargos. Priorizacion de intervenciones segun hallazgos. Datos del diagnostico de condiciones de salud osteomuscular.'
    UNION SELECT 7, 'Poblacion Objeto', 'poblacion_objeto', 'texto', 7,
           'Define la poblacion objeto del PVE con criterios de inclusion: trabajadores con exposicion a riesgo biomecanico alto o muy alto en matriz, trabajadores con DME diagnosticado, trabajadores con sintomatologia osteomuscular, trabajadores con restricciones medicas de origen osteomuscular. Clasificacion en 3 niveles: Caso (DME diagnosticado), Sospechoso (sintomas sin diagnostico), Expuesto sin sintomas. Criterios de ingreso, seguimiento y egreso del programa. Estratificacion por cargo y nivel de riesgo.'
    UNION SELECT 8, 'Actividades del PVE', 'actividades_pve', 'texto', 8,
           'Genera las actividades del PVE organizadas en 3 componentes: 1) Componente Ambiental (inspecciones ergonomicas de puestos, adecuacion de mobiliario, ayudas mecanicas, rediseno de tareas, rotacion de actividades), 2) Componente Medico (examenes osteomusculares periodicos, aplicacion cuestionario nordico, seguimiento a sintomaticos, manejo de restricciones medicas, remisiones EPS/ARL), 3) Componente Educativo (capacitacion higiene postural, taller manejo manual de cargas, programa de pausas activas, autocuidado ergonomico). Cada actividad con responsable, frecuencia, recurso y registro/evidencia.'
    UNION SELECT 9, 'Indicadores', 'indicadores', 'texto', 9,
           'Genera indicadores de gestion: Estructura (PVE documentado y aprobado, recursos asignados vs ejecutados, profesiograma actualizado), Proceso (% inspecciones ergonomicas ejecutadas meta>=90%, cobertura examenes osteomusculares meta>=95%, % capacitaciones ejecutadas meta>=90%, cobertura poblacion objeto meta=100%), Resultado (tasa de incidencia DME, tasa de prevalencia DME, % ausentismo por DME meta reduccion 10%, efectividad intervenciones meta>=70%). Con formula, meta, frecuencia medicion, fuente de informacion.'
    UNION SELECT 10, 'Cronograma', 'cronograma', 'texto', 10,
           'Genera cronograma anual de actividades por trimestres: T1 (actualizacion diagnostico biomecanico, clasificacion poblacion objeto, inicio examenes periodicos, capacitacion higiene postural), T2 (inspecciones ergonomicas, adecuaciones puestos trabajo, taller manejo cargas, seguimiento sintomaticos), T3 (segunda ronda inspecciones, evaluacion pausas activas, capacitacion autocuidado, seguimiento restricciones), T4 (examenes control, evaluacion indicadores, informe anual, planificacion siguiente periodo). Con responsable, recurso, registro y fecha estimada.'
    UNION SELECT 11, 'Responsabilidades', 'responsabilidades', 'texto', 11,
           'Define responsabilidades: Alta Direccion (aprobar PVE, asignar recursos, implementar mejoras ergonomicas), Responsable SG-SST (disenar, implementar, hacer seguimiento, coordinar con ARL/EPS, gestionar indicadores), Medico SST/IPS (evaluaciones osteomusculares, diagnostico, seguimiento casos, remisiones), COPASST/Vigia (participar en inspecciones, verificar controles, promover participacion), ARL (asesoria tecnica, capacitaciones, evaluaciones ergonomicas), Trabajadores (participar en actividades, reportar sintomas, aplicar higiene postural, usar ayudas mecanicas, asistir a pausas activas).'
    UNION SELECT 12, 'Anexos', 'anexos', 'texto', 12,
           'Lista los anexos del PVE: Anexo 1 - Formato de Inspeccion Ergonomica de Puesto de Trabajo, Anexo 2 - Cuestionario Nordico de Sintomas Osteomusculares, Anexo 3 - Formato de Seguimiento a Casos, Anexo 4 - Formato de Evaluacion Metodo RULA/REBA, Anexo 5 - Registro de Pausas Activas, Anexo 6 - Formato de Capacitacion Higiene Postural, Anexo 7 - Formato de Seguimiento a Restricciones Medicas, Anexo 8 - Matriz de Priorizacion de Intervenciones Ergonomicas. Breve descripcion de cada anexo y su proposito.'
) s
WHERE tc.tipo_documento = 'pve_riesgo_biomecanico'
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
WHERE tc.tipo_documento = 'pve_riesgo_biomecanico'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'PVE de Riesgo Biomecanico', 'PVE-BIO-001', 'pve_riesgo_biomecanico', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'pve_riesgo_biomecanico')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PVE-BIO-001', '4.2.3')
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
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'pve_riesgo_biomecanico'");
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
