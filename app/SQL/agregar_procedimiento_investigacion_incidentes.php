<?php
/**
 * Script para agregar tipo de documento: Investigacion de Incidentes, Accidentes y Enfermedades Laborales
 * Estandar: 3.2.2
 * Ejecutar: php app/SQL/agregar_procedimiento_investigacion_incidentes.php
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
('procedimiento_investigacion_incidentes',
 'Investigacion de Incidentes, Accidentes de Trabajo y Enfermedades Laborales',
 'Verifica que se investigan todos los accidentes e incidentes de trabajo y las enfermedades laborales determinando causas basicas e inmediatas y realizando seguimiento a las acciones para trabajadores potencialmente expuestos',
 '3.2.2',
 'secciones_ia',
 'procedimientos',
 'bi-search',
 20)
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
           'Genera el objetivo del documento de Investigacion de Incidentes, Accidentes de Trabajo y Enfermedades Laborales. Debe mencionar: investigar TODOS los incidentes, accidentes y enfermedades laborales diagnosticadas, determinar causas basicas e inmediatas, evaluar posibilidad de nuevos casos, seguimiento a acciones para trabajadores potencialmente expuestos. Cumplimiento Decreto 1072/2015 art. 2.2.4.6.32, Res. 0312/2019 estandar 3.2.2, Res. 1401/2007.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2,
           'Define el alcance: aplica a todos los trabajadores directos, contratistas, subcontratistas, temporales y visitantes. Cubre incidentes, accidentes de trabajo (leves, graves, mortales) y enfermedades laborales diagnosticadas. Incluye determinacion de causas y seguimiento a trabajadores potencialmente expuestos en todas las sedes.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', 3,
           'Define terminos clave: Accidente de trabajo (Ley 1562/2012 art. 3), Incidente de trabajo, Enfermedad laboral (Ley 1562/2012 art. 4), Accidente grave (Res. 1401/2007), Accidente mortal, Causa inmediata, Causa basica, Factor personal, Factor del trabajo, Trabajador potencialmente expuesto, Investigacion de accidentes, FURAT, FUREL, Acto inseguro, Condicion insegura. 12-16 definiciones segun nivel de estandares.'
    UNION SELECT 4, 'Marco Legal', 'marco_legal', 'texto', 4,
           'Marco normativo: Ley 1562/2012 art. 3 y 4, Decreto 1072/2015 art. 2.2.4.6.32 y 2.2.4.6.12, Res. 0312/2019 estandar 3.2.2, Res. 1401/2007 (investigacion AT), Decreto 472/2015 (sanciones), Res. 156/2005 (FURAT/FUREL), Decreto 1530/1996 art. 4. Formato tabla.'
    UNION SELECT 5, 'Responsabilidades', 'responsabilidades', 'texto', 5,
           'Responsabilidades: Alta Direccion (garantizar investigacion de TODOS los eventos, recursos), Responsable SST (liderar investigacion, determinar causas, evaluar expuestos), COPASST/Vigia (participar, verificar acciones), Jefe inmediato (preservar evidencia), Trabajadores (reportar, colaborar), ARL (asesorar, investigar AT graves/mortales).'
    UNION SELECT 6, 'Clasificacion de Eventos Investigables', 'clasificacion_eventos', 'texto', 6,
           'Clasifica eventos investigables: 1) Incidentes (sin lesion, prevencion), 2) AT leves (investigacion interna), 3) AT graves (profesional con licencia SST), 4) AT mortales (reporte Ministerio), 5) Enfermedades laborales diagnosticadas (evaluar TODOS los expuestos). Para cada tipo: quien investiga, plazo, reportes, documentacion. Estandar 3.2.2 exige investigar TODOS sin excepcion.'
    UNION SELECT 7, 'Metodologia de Investigacion', 'metodologia_investigacion', 'texto', 7,
           'Metodologia paso a paso: 1) Atencion inmediata y preservacion evidencia, 2) Conformacion equipo investigador (jefe + SST + COPASST, para graves: profesional licencia SST, Res. 1401/2007), 3) Recopilacion informacion (declaraciones, inspeccion, documentos), 4) Reconstruccion hechos, 5) Analisis causas (ver seccion 8), 6) Evaluacion trabajadores expuestos (ver seccion 9), 7) Plan de acciones correctivas, 8) Informe final. Plazo: 15 dias calendario (Res. 1401/2007).'
    UNION SELECT 8, 'Determinacion de Causas Basicas e Inmediatas', 'determinacion_causas', 'texto', 8,
           'Metodologias segun Res. 1401/2007: Causas inmediatas (actos y condiciones inseguras), Causas basicas (factores personales y del trabajo). Metodologias: 5 Por Que (basico), Arbol de causas (intermedio), Espina de pescado Ishikawa (avanzado). La determinacion debe llegar a causas RAIZ, requisito fundamental del estandar 3.2.2.'
    UNION SELECT 9, 'Evaluacion de Trabajadores Potencialmente Expuestos', 'evaluacion_expuestos', 'texto', 9,
           'ELEMENTO DIFERENCIADOR del estandar 3.2.2: 1) Identificar trabajadores expuestos al mismo peligro, 2) Evaluar riesgo para expuestos, 3) Medidas preventivas para cargos/areas similares, 4) Evaluaciones medicas especificas para EL, 5) Comunicacion y sensibilizacion, 6) Seguimiento periodico, 7) Actualizacion matriz de peligros. Para EL: evaluar prevalencia en poblacion expuesta, activar vigilancia epidemiologica.'
    UNION SELECT 10, 'Acciones Correctivas y Seguimiento', 'acciones_seguimiento', 'texto', 10,
           'Plan de accion: para cada causa, accion correctiva/preventiva con jerarquia de controles, responsable, fecha, recursos. Plazos: inmediatas (24h), corto plazo (15 dias), mediano plazo (30-60 dias). Seguimiento semanal/mensual. COPASST verifica en reuniones. Cierre solo cuando TODAS las acciones esten implementadas y verificadas. Lecciones aprendidas. Conservacion 20 anos.'
    UNION SELECT 11, 'Indicadores de Gestion', 'indicadores', 'texto', 11,
           'Indicadores: Proceso (% eventos investigados vs reportados meta=100%, tiempo promedio inicio investigacion meta<=2 dias, % investigaciones con causas raiz meta=100%, % expuestos evaluados meta>=90%). Resultado (% acciones cerradas en plazo meta>=85%, tasa recurrencia misma causa meta=0%, indice frecuencia, indice severidad, % lecciones socializadas meta>=90%). Con formula, meta, frecuencia.'
    UNION SELECT 12, 'Registros y Evidencias', 'registros_evidencias', 'texto', 12,
           'Registros obligatorios: FURAT, FUREL, informe investigacion (Res. 1401/2007), declaraciones testigos, registro fotografico, reportes ARL/Ministerio, plan accion, seguimiento y cierre, listado trabajadores expuestos evaluados, actas COPASST, lecciones aprendidas, indicadores. Conservacion 20 anos (Decreto 1072/2015). Codificacion: FT-SST-IIA-01 a FT-SST-IIA-04.'
) s
WHERE tc.tipo_documento = 'procedimiento_investigacion_incidentes'
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
WHERE tc.tipo_documento = 'procedimiento_investigacion_incidentes'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Investigacion de Incidentes, Accidentes de Trabajo y Enfermedades Laborales', 'PRC-IIA', 'procedimiento_investigacion_incidentes', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'procedimiento_investigacion_incidentes')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PRC-IIA', '3.2.2')
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
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'procedimiento_investigacion_incidentes'");
        $tipo = $stmt->fetch();
        if ($tipo) {
            echo "  [INFO] Tipo creado con ID: {$tipo['id_tipo_config']}\n";

            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_secciones_config WHERE id_tipo_config = {$tipo['id_tipo_config']}");
            $secciones = $stmt->fetch();
            echo "  [INFO] Secciones configuradas: {$secciones['total']}\n";

            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_firmantes_config WHERE id_tipo_config = {$tipo['id_tipo_config']}");
            $firmantes = $stmt->fetch();
            echo "  [INFO] Firmantes configurados: {$firmantes['total']}\n";
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
