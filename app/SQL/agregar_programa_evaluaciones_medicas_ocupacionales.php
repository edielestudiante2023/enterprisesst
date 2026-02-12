<?php
/**
 * Script para agregar tipo de documento: Programa de Evaluaciones Medicas Ocupacionales
 * Estandar: 3.1.4
 * Ejecutar: php app/SQL/agregar_programa_evaluaciones_medicas_ocupacionales.php
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
('programa_evaluaciones_medicas_ocupacionales',
 'Programa de Evaluaciones Medicas Ocupacionales',
 'Establece la realizacion de evaluaciones medicas ocupacionales segun peligros, con frecuencia acorde a la magnitud de los riesgos, comunicacion de resultados al trabajador y articulacion con PVE',
 '3.1.4',
 'secciones_ia',
 'programas',
 'bi-clipboard2-pulse',
 19)
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
           'Genera el objetivo del Programa de Evaluaciones Medicas Ocupacionales. Debe mencionar la realizacion de evaluaciones medicas de acuerdo con los peligros identificados, frecuencia acorde a la magnitud de los riesgos y estado de salud, comunicacion oportuna de resultados, articulacion con PVE. Cumplimiento de Res. 2346/2007, Decreto 1072/2015 art. 2.2.4.6.24, Res. 0312/2019 estandar 3.1.4.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2,
           'Define el alcance del programa. Aplica a todos los trabajadores directos, contratistas, subcontratistas y temporales. Cubre evaluaciones de ingreso, periodicas, egreso, cambio de cargo y post-incapacidad. Las evaluaciones se definen segun peligros de la matriz y frecuencia segun magnitud de riesgos.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', 3,
           'Define terminos clave: Evaluacion medica ocupacional, Profesiograma, Certificado de aptitud, Aptitud medica, Evaluacion de ingreso, Evaluacion periodica, Evaluacion de egreso, Evaluacion por cambio de cargo, Evaluacion post-incapacidad, Restriccion medica, Recomendacion medica, Vigilancia epidemiologica, Historia clinica ocupacional, Descripcion sociodemografica. 13-15 definiciones.'
    UNION SELECT 4, 'Marco Legal', 'marco_legal', 'texto', 4,
           'Presenta marco normativo: Res. 2346/2007 (evaluaciones medicas), Res. 1918/2009 (custodia historias clinicas), Decreto 1072/2015 art. 2.2.4.6.24, Res. 0312/2019 est. 3.1.4, Ley 1562/2012, Res. 2764/2022 (bateria psicosocial), CST art. 348. Formato tabla.'
    UNION SELECT 5, 'Responsabilidades', 'responsabilidades', 'texto', 5,
           'Define responsabilidades: Alta Direccion (recursos, implementar restricciones), Responsable SST (coordinar, comunicar, seguimiento), Medico con licencia SST/IPS (evaluar, profesiograma, diagnostico), COPASST/Vigia (verificar cumplimiento), Trabajadores (asistir, informar, cumplir restricciones).'
    UNION SELECT 6, 'Tipos de Evaluaciones Medicas', 'tipos_evaluaciones', 'texto', 6,
           'Describe los 5 tipos segun Res. 2346/2007: Ingreso (antes de labores, aptitud), Periodicas (segun profesiograma y peligros), Egreso (5 dias post-retiro), Cambio de cargo (nuevos peligros), Post-incapacidad (>30 dias). Para cada uno: momento, objetivo, examenes complementarios segun peligros.'
    UNION SELECT 7, 'Profesiograma y Frecuencia segun Peligros', 'profesiograma_frecuencia', 'texto', 7,
           'Describe el profesiograma (examenes por cargo segun peligros) y frecuencias: Quimico (anual), Ruido (audiometria anual/semestral), Biomecanico (osteomuscular anual), Psicosocial (Res. 2764/2022), Biologico (semestral), Alturas (semestral), Visual (anual/bienal). Ajuste segun magnitud de riesgo y PVE.'
    UNION SELECT 8, 'Comunicacion de Resultados al Trabajador', 'comunicacion_resultados', 'texto', 8,
           'Describe comunicacion: Al trabajador (certificado aptitud + recomendaciones, 5 dias habiles, acuse de recibo), Al empleador (SOLO aptitud, NO diagnosticos), Confidencialidad (historias clinicas en IPS, no empleador, 20 anos conservacion). Registros de entrega.'
    UNION SELECT 9, 'Restricciones y Recomendaciones Medicas', 'restricciones_recomendaciones', 'texto', 9,
           'Describe manejo de restricciones temporales/permanentes y recomendaciones. Procedimiento: registro, comunicacion trabajador y jefe, implementacion ajustes puesto, seguimiento mensual, reubicacion si aplica. Articulacion con ARL y EPS.'
    UNION SELECT 10, 'Articulacion con Programas de Vigilancia Epidemiologica', 'articulacion_pve', 'texto', 10,
           'Describe articulacion bidireccional: evaluaciones medicas alimentan PVE (hallazgos, tendencias, morbilidad), PVE retroalimenta programa (ajuste frecuencias, nuevos examenes, priorizacion). PVE alimentados: Osteomuscular, Auditivo, Visual, Cardiovascular, Psicosocial, Quimico. Ciclo de mejora continua.'
    UNION SELECT 11, 'Indicadores de Gestion', 'indicadores', 'texto', 11,
           'Genera indicadores: Proceso (cobertura ingreso meta=100%, cumplimiento periodicas meta>=90%, comunicacion oportuna meta>=95%, cumplimiento restricciones meta=100%, cobertura egreso meta>=90%), Resultado (prevalencia enfermedad laboral, aptos sin restriccion meta>=80%). Con formula, meta, frecuencia.'
    UNION SELECT 12, 'Cronograma y Seguimiento', 'cronograma_seguimiento', 'texto', 12,
           'Genera cronograma anual: T1 (actualizacion profesiograma, programacion anual), T2 (ejecucion periodicas, comunicacion resultados, articulacion PVE), T3 (segunda ronda, actualizacion diagnostico salud), T4 (evaluacion indicadores, informe anual, planificacion). Registros requeridos.'
) s
WHERE tc.tipo_documento = 'programa_evaluaciones_medicas_ocupacionales'
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
WHERE tc.tipo_documento = 'programa_evaluaciones_medicas_ocupacionales'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Programa de Evaluaciones Medicas Ocupacionales', 'PRG-EMO', 'programa_evaluaciones_medicas_ocupacionales', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'programa_evaluaciones_medicas_ocupacionales')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PRG-EMO', '3.1.4')
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
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'programa_evaluaciones_medicas_ocupacionales'");
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
