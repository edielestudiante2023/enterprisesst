<?php
/**
 * Script para agregar tipo de documento: Procedimiento de Evaluaciones Medicas Ocupacionales
 * Estandar: 3.1.1
 * Ejecutar: php app/SQL/agregar_procedimiento_evaluaciones_medicas.php
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
('procedimiento_evaluaciones_medicas',
 'Procedimiento de Evaluaciones Medicas Ocupacionales',
 'Establece la metodologia para la realizacion de evaluaciones medicas ocupacionales, descripcion sociodemografica y diagnostico de condiciones de salud',
 '3.1.1',
 'secciones_ia',
 'procedimientos',
 'bi-heart-pulse',
 16)
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
           'Genera el objetivo del procedimiento de evaluaciones medicas ocupacionales. Debe mencionar el cumplimiento de la Resolucion 2346 de 2007, el Decreto 1072 de 2015 y la Resolucion 0312 de 2019 (estandar 3.1.1).' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2,
           'Define el alcance del procedimiento. Aplica a todos los trabajadores (directos, contratistas, temporales). Cubre evaluaciones de ingreso, periodicas, egreso, post-incapacidad y cambio de cargo.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', 3,
           'Define los terminos clave: Evaluacion Medica Ocupacional, Profesiograma, Descripcion Sociodemografica, Diagnostico de Condiciones de Salud, Aptitud Medica, Historia Clinica Ocupacional, Certificado de Aptitud.'
    UNION SELECT 4, 'Marco Legal', 'marco_legal', 'texto', 4,
           'Presenta el marco normativo: Resolucion 2346/2007 (evaluaciones medicas), Resolucion 1918/2009 (custodia historias clinicas), Decreto 1072/2015 (art. 2.2.4.6.24), Resolucion 0312/2019 (estandar 3.1.1), Ley 1562/2012.'
    UNION SELECT 5, 'Tipos de Evaluaciones Medicas Ocupacionales', 'tipos_evaluaciones_medicas', 'texto', 5,
           'Describe los 5 tipos de evaluaciones medicas segun Resolucion 2346/2007: ingreso, periodicas programadas, cambio de ocupacion, egreso, post-incapacidad. Para cada una: momento, objetivo y examenes complementarios.'
    UNION SELECT 6, 'Frecuencia de Evaluaciones segun Riesgos', 'frecuencia_evaluaciones', 'texto', 6,
           'Define la frecuencia de evaluaciones periodicas segun factores de riesgo: quimico, fisico (ruido), biomecanico, psicosocial, trabajo en alturas, biologico. La frecuencia la define el medico en el profesiograma.'
    UNION SELECT 7, 'Profesiograma', 'profesiograma', 'texto', 7,
           'Describe el profesiograma: herramienta que define examenes medicos por cargo segun peligros identificados. Contenido minimo, quien lo elabora, frecuencia de actualizacion.'
    UNION SELECT 8, 'Descripcion Sociodemografica', 'descripcion_sociodemografica', 'texto', 8,
           'Describe como se elabora la descripcion sociodemografica: variables (edad, sexo, escolaridad, estado civil, estrato, antiguedad), fuentes, frecuencia de actualizacion (minimo anual).'
    UNION SELECT 9, 'Diagnostico de Condiciones de Salud', 'diagnostico_condiciones_salud', 'texto', 9,
           'Describe como se elabora el diagnostico de condiciones de salud: fuentes, contenido minimo (prevalencia, incidencia, morbilidad), analisis estadistico, recomendaciones del medico, confidencialidad.'
    UNION SELECT 10, 'Comunicacion de Resultados al Trabajador', 'comunicacion_resultados', 'texto', 10,
           'Describe como se comunican resultados: al trabajador (certificado con recomendaciones), al empleador (solo aptitud, NO diagnosticos), plazo (5 dias habiles), medio escrito, confidencialidad (Res. 2346/2007 art. 16).'
    UNION SELECT 11, 'Restricciones y Recomendaciones Medicas', 'restricciones_recomendaciones', 'texto', 11,
           'Describe el manejo de restricciones y recomendaciones: definicion, registro, comunicacion, seguimiento, reubicacion laboral si aplica.'
    UNION SELECT 12, 'Custodia de Historias Clinicas', 'custodia_historias_clinicas', 'texto', 12,
           'Describe obligaciones de custodia segun Res. 2346/2007 y Res. 1918/2009: la IPS custodia (NO el empleador), tiempo minimo 20 anos, acceso restringido, el empleador solo tiene certificados de aptitud.'
    UNION SELECT 13, 'Responsabilidades', 'responsabilidades', 'texto', 13,
           'Define responsabilidades: Alta Direccion (recursos, contratacion IPS), Responsable SG-SST (coordinacion, seguimiento), Medico con licencia SST (evaluaciones, profesiograma, diagnostico), COPASST/Vigia (seguimiento), Trabajadores (asistir, informar, cumplir).'
) s
WHERE tc.tipo_documento = 'procedimiento_evaluaciones_medicas'
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
WHERE tc.tipo_documento = 'procedimiento_evaluaciones_medicas'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Procedimiento de Evaluaciones Medicas Ocupacionales', 'PRC-EMO', 'procedimiento_evaluaciones_medicas', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'procedimiento_evaluaciones_medicas')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PRC-EMO', '3.1.1')
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

foreach ($conexiones as $entorno => $config) {
    echo "\n=== Ejecutando en $entorno ===\n";

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
        ejecutarSQL($pdo, $sqlTipo, 'Tipo de documento');
        ejecutarSQL($pdo, $sqlSecciones, 'Secciones (13)');
        ejecutarSQL($pdo, $sqlFirmantes, 'Firmantes');
        ejecutarSQL($pdo, $sqlPlantilla, 'Plantilla');
        ejecutarSQL($pdo, $sqlMapeoCarpeta, 'Mapeo carpeta');

        // Verificar resultado
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'procedimiento_evaluaciones_medicas'");
        $tipo = $stmt->fetch();
        if ($tipo) {
            echo "  [INFO] Tipo creado con ID: {$tipo['id_tipo_config']}\n";

            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_secciones_config WHERE id_tipo_config = {$tipo['id_tipo_config']}");
            $secciones = $stmt->fetch();
            echo "  [INFO] Secciones configuradas: {$secciones['total']}\n";
        }

    } catch (PDOException $e) {
        echo "  [ERROR] Conexion: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Proceso completado ===\n";
