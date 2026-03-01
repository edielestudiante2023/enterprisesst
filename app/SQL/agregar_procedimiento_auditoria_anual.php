<?php
/**
 * Script para agregar tipo de documento: Procedimiento para la Realizacion de la Auditoria Anual del SG-SST
 * Estandar: 6.1.2
 * Ejecutar: php app/SQL/agregar_procedimiento_auditoria_anual.php
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
('procedimiento_auditoria_anual',
 'Procedimiento para la Realizacion de la Auditoria Anual del SG-SST',
 'Establece la metodologia y los pasos para planificar, ejecutar y documentar la auditoria anual del Sistema de Gestion de Seguridad y Salud en el Trabajo',
 '6.1.2',
 'secciones_ia',
 'procedimientos',
 'bi-clipboard-check',
 28)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), descripcion = VALUES(descripcion), updated_at = NOW();
SQL;

// SQL para secciones
$sqlSecciones = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, s.tipo_contenido, s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 'texto' as tipo_contenido, 1 as orden,
           'Genera el objetivo del Procedimiento de Auditoria Anual del SG-SST. Debe mencionar el cumplimiento del Decreto 1072 de 2015 (articulos 2.2.4.6.29 y 2.2.4.6.30) y la Resolucion 0312 de 2019 (estandar 6.1.2). El proposito es establecer la metodologia para planificar, ejecutar y documentar la auditoria anual que permita verificar el cumplimiento del SG-SST, identificar oportunidades de mejora y asegurar la conformidad con la normativa vigente. Maximo 2 parrafos concisos.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2,
           'Define el alcance de la auditoria anual del SG-SST. Debe cubrir todos los elementos del Sistema de Gestion: politica, organizacion, planificacion, aplicacion, evaluacion, auditoria y mejora. Aplica a todas las areas, procesos, niveles jerarquicos y centros de trabajo de la organizacion. Involucra: alta direccion, responsable del SG-SST, COPASST/Vigia, auditores internos o externos, y todos los trabajadores como entrevistados. Maximo 2 parrafos.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', 3,
           'Genera definiciones clave para la auditoria del SG-SST. INCLUIR OBLIGATORIAMENTE: Auditoria, Auditor, Criterios de auditoria, Evidencia de auditoria, Hallazgo de auditoria, No conformidad, No conformidad mayor, No conformidad menor, Observacion, Oportunidad de mejora, Accion correctiva, Programa de auditoria, Plan de auditoria, Lista de verificacion. 12-15 definiciones basadas en ISO 19011:2018 y normativa colombiana SST.'
    UNION SELECT 4, 'Marco Legal', 'marco_legal', 'texto', 4,
           'Genera el marco legal aplicable a la auditoria anual del SG-SST en Colombia. INCLUIR: Decreto 1072 de 2015 articulo 2.2.4.6.29 (Auditoria de cumplimiento del SG-SST), Decreto 1072 de 2015 articulo 2.2.4.6.30 (Alcance de la auditoria), Resolucion 0312 de 2019 estandar 6.1.2 (La empresa adelanta auditoria por lo menos una vez al ano), Resolucion 0312 de 2019 estandar 6.1.1 (Definicion indicadores del SG-SST), Decreto 1072 de 2015 articulo 2.2.4.6.31 (Revision por la alta direccion), ISO 19011:2018 (Directrices para la auditoria de sistemas de gestion - como referencia). Presentar en formato de tabla con: Norma, Articulo/Estandar, Descripcion.'
    UNION SELECT 5, 'Responsabilidades', 'responsabilidades', 'texto', 5,
           'Define las responsabilidades en la auditoria anual del SG-SST: Alta Direccion (disponer recursos, designar auditor, revisar resultados, aprobar plan de accion), Responsable del SG-SST (planificar programa de auditoria, coordinar logistica, acompanar proceso, elaborar plan de accion correctivo, hacer seguimiento), COPASST/Vigia (participar activamente en la auditoria, verificar que se auditen todos los aspectos del SG-SST, recibir y analizar resultados), Auditor interno o externo (ejecutar auditoria conforme al plan, aplicar criterios objetivos, emitir informe con hallazgos y recomendaciones, verificar cierre de acciones), Trabajadores (facilitar informacion veraz, participar en entrevistas, colaborar con el proceso). Importante: el auditor debe ser independiente y no auditar su propio trabajo.'
    UNION SELECT 6, 'Planificacion de la Auditoria', 'planificacion_auditoria', 'texto', 6,
           'Genera la seccion de planificacion de la auditoria anual del SG-SST. Incluir: 1) Definicion del programa anual de auditoria (frecuencia minima anual, periodo recomendado), 2) Seleccion del equipo auditor (requisitos: independencia, competencia, conocimiento normativo; puede ser interno capacitado o externo contratado), 3) Elaboracion del plan de auditoria (objetivo, alcance, criterios, areas/procesos a auditar, cronograma, recursos), 4) Preparacion de documentos de trabajo (listas de verificacion basadas en Decreto 1072 art. 2.2.4.6.30, formatos de registro), 5) Comunicacion previa a los auditados (notificacion, agenda, documentacion requerida). Incluir que el alcance de la auditoria debe cubrir los 11 aspectos del art. 2.2.4.6.30 del Decreto 1072/2015.'
    UNION SELECT 7, 'Ejecucion de la Auditoria', 'ejecucion_auditoria', 'texto', 7,
           'Genera el procedimiento paso a paso para ejecutar la auditoria anual del SG-SST. Incluir: 1) Reunion de apertura (presentacion del equipo auditor, confirmacion del plan, acuerdos logisticos), 2) Recopilacion de evidencias (revision documental, entrevistas, observacion directa, verificacion de registros), 3) Tecnicas de auditoria (muestreo, trazabilidad, triangulacion de evidencias), 4) Evaluacion de hallazgos (clasificar como: conformidad, no conformidad mayor, no conformidad menor, observacion, oportunidad de mejora), 5) Reunion de cierre (presentacion de hallazgos preliminares, acuerdos sobre plazos), 6) Aspectos que se deben auditar segun Decreto 1072 art. 2.2.4.6.30 (cumplimiento politica, resultado de indicadores, participacion trabajadores, mecanismos de comunicacion, planificacion, gestion peligros/riesgos, plan de prevencion/emergencias, supervision/medicion, acciones preventivas/correctivas, reporte ATEL, continuidad del SG-SST).'
    UNION SELECT 8, 'Criterios y Metodologia de Auditoria', 'criterios_metodologia', 'texto', 8,
           'Genera los criterios y la metodologia de evaluacion para la auditoria del SG-SST. Incluir: 1) Criterios de auditoria basados en: Decreto 1072/2015 (Libro 2, Parte 2, Titulo 4, Capitulo 6), Resolucion 0312/2019 (estandares minimos segun nivel de riesgo y numero de trabajadores), Politica y objetivos del SG-SST de la organizacion, procedimientos internos, 2) Metodologia de calificacion (escala: Cumple, Cumple parcialmente, No cumple, No aplica), 3) Lista de verificacion modelo con los 11 aspectos del art. 2.2.4.6.30, 4) Criterios para clasificar no conformidades (mayor: riesgo inmediato o incumplimiento sistematico; menor: desviacion puntual sin impacto critico), 5) Porcentaje de cumplimiento (formula y rangos de aceptabilidad).'
    UNION SELECT 9, 'Informe de Resultados', 'informe_resultados', 'texto', 9,
           'Genera la estructura y contenido del informe de resultados de la auditoria anual del SG-SST. Incluir: 1) Datos generales (fecha, auditor, periodo auditado, areas cubiertas), 2) Resumen ejecutivo (porcentaje de cumplimiento global, principales fortalezas, principales hallazgos), 3) Detalle de hallazgos por aspecto auditado (tipo: NC mayor, NC menor, observacion, oportunidad de mejora; evidencia objetiva; requisito incumplido; area/proceso afectado), 4) Conclusiones del auditor, 5) Recomendaciones, 6) Anexos (listas de verificacion diligenciadas, evidencias fotograficas, registros de entrevistas). El informe debe comunicarse a la alta direccion y al COPASST/Vigia conforme al art. 2.2.4.6.29 del Decreto 1072.'
    UNION SELECT 10, 'Seguimiento y Acciones Correctivas', 'seguimiento_acciones', 'texto', 10,
           'Genera la seccion de seguimiento y acciones correctivas post-auditoria. Incluir: 1) Elaboracion del plan de accion correctivo (responsable, accion, plazo, recurso, indicador de cierre para cada hallazgo), 2) Priorizacion de acciones (NC mayores: accion inmediata maximo 30 dias; NC menores: maximo 90 dias; observaciones: siguiente periodo), 3) Seguimiento al cierre (verificacion de implementacion de acciones, evidencias de cierre, auditor verifica eficacia), 4) Indicadores de gestion de la auditoria (% cumplimiento SG-SST, % NC cerradas en plazo, numero de hallazgos vs periodo anterior, indice de eficacia de acciones correctivas), 5) Integracion con mejora continua (resultados alimentan revision por la direccion art. 2.2.4.6.31, actualizacion del SG-SST, ajuste del plan de trabajo anual), 6) Conservacion de registros minimo 20 anos conforme art. 2.2.4.6.13 del Decreto 1072.'
) s
WHERE tc.tipo_documento = 'procedimiento_auditoria_anual'
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
WHERE tc.tipo_documento = 'procedimiento_auditoria_anual'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Procedimiento de Auditoria Anual del SG-SST', 'PRC-AUD', 'procedimiento_auditoria_anual', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'procedimiento_auditoria_anual')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PRC-AUD', '6.1.2')
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
        $ok = ejecutarSQL($pdo, $sqlSecciones, 'Secciones (10)') && $ok;
        $ok = ejecutarSQL($pdo, $sqlFirmantes, 'Firmantes') && $ok;
        $ok = ejecutarSQL($pdo, $sqlPlantilla, 'Plantilla') && $ok;
        $ok = ejecutarSQL($pdo, $sqlMapeoCarpeta, 'Mapeo carpeta') && $ok;

        // Verificar resultado
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'procedimiento_auditoria_anual'");
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
