<?php
/**
 * Script para agregar tipo de documento: Programa de Estilos de Vida Saludable
 * Estandar: 3.1.7
 * Ejecutar: php app/SQL/agregar_programa_estilos_vida_saludable.php
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
('programa_estilos_vida_saludable',
 'Programa de Estilos de Vida Saludable y Entornos Saludables',
 'Establece las actividades de promocion de estilos de vida saludables, controles de tabaquismo, alcoholismo, farmacodependencia y fomento de entornos de trabajo saludables',
 '3.1.7',
 'secciones_ia',
 'programas',
 'bi-heart-pulse',
 18)
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
           'Genera el objetivo del Programa de Estilos de Vida Saludable y Entornos Saludables. Debe mencionar la promocion de habitos saludables, controles de tabaquismo, alcoholismo y farmacodependencia, cumplimiento del Decreto 1072/2015 (art. 2.2.4.6.24) y Resolucion 0312/2019 (estandar 3.1.7). Articular con la Politica de Prevencion de Consumo de Alcohol, Drogas y Tabaco.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2,
           'Define el alcance del programa. Aplica a todos los trabajadores directos, contratistas, subcontratistas y temporales. Cubre promocion de habitos saludables (alimentacion, actividad fisica, manejo del estres, higiene del sueno) y prevencion y control de consumo de tabaco, alcohol y sustancias psicoactivas.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', 3,
           'Define terminos clave: Estilo de vida saludable, Entorno de trabajo saludable, Tabaquismo, Alcoholismo, Farmacodependencia, Sustancia psicoactiva, Promocion de la salud, Prevencion de la enfermedad, Autocuidado, Factor de riesgo modificable, Pausas activas, Bienestar laboral. 12-14 definiciones.'
    UNION SELECT 4, 'Marco Legal', 'marco_legal', 'texto', 4,
           'Presenta el marco normativo: Decreto 1072/2015 art. 2.2.4.6.24, Resolucion 0312/2019 est. 3.1.7, Ley 1566/2012 (SPA), Resolucion 1075/1992 (campanas prevencion), Ley 1335/2009 (antitabaco), Ley 30/1986 (estupefacientes), Decreto 1108/1994, Resolucion 2646/2008 (riesgo psicosocial), Circular 038/2010. Formato tabla.'
    UNION SELECT 5, 'Responsabilidades', 'responsabilidades', 'texto', 5,
           'Define responsabilidades: Alta Direccion (aprobar, recursos, ambientes libres de humo), Responsable SG-SST (disenar, implementar, evaluar, coordinar con ARL/EPS), COPASST/Vigia (difusion, participacion), ARL (asesoria, capacitaciones, material), Trabajadores (participar, adoptar habitos, cumplir politica).'
    UNION SELECT 6, 'Diagnostico y Linea Base', 'diagnostico_linea_base', 'texto', 6,
           'Describe como se realiza el diagnostico: fuentes (evaluaciones medicas, perfil sociodemografico, encuesta de habitos, estadisticas ausentismo, bateria psicosocial), variables (alimentacion, actividad fisica, consumo tabaco/alcohol/SPA, estres, IMC), periodicidad (linea base inicial, actualizacion anual).'
    UNION SELECT 7, 'Actividades de Promocion de Estilos de Vida Saludable', 'actividades_promocion', 'texto', 7,
           'Genera actividades por categoria: Alimentacion saludable (campanas, valoracion nutricional, charlas), Actividad fisica y pausas activas (pausas 2/dia, jornadas deportivas, retos), Salud mental (talleres estres, campanas, apoyo psicologico), Prevencion enfermedades cronicas (tamizaje, campanas cardiovascular). Con responsable, frecuencia, evidencia.'
    UNION SELECT 8, 'Controles de Tabaquismo, Alcoholismo y Farmacodependencia', 'controles_sustancias', 'texto', 8,
           'Genera controles: Tabaquismo (ambientes libres humo Ley 1335/2009, senalizacion, campanas, apoyo cesacion), Alcoholismo (campanas Res 1075/1992, prohibicion jornada laboral, alcoholimetria cargos criticos, canalizacion EPS), Farmacodependencia (sensibilizacion Ley 1566/2012, deteccion temprana, remision EPS, acompanamiento). Incluir protocolo de actuacion ante casos.'
    UNION SELECT 9, 'Entornos de Trabajo Saludables', 'entornos_saludables', 'texto', 9,
           'Describe acciones para entornos saludables: Ambiente fisico (iluminacion, ventilacion, agua potable, espacios descanso), Ambiente psicosocial (comunicacion asertiva, prevencion acoso, equilibrio vida-trabajo), Organizacion del trabajo (cargas equitativas, pausas activas, capacitacion continua).'
    UNION SELECT 10, 'Cronograma de Actividades', 'cronograma', 'texto', 10,
           'Genera cronograma anual por trimestres: T1 (encuesta habitos, campana alimentacion, inicio pausas, tamizaje), T2 (campana antitabaco mayo 31, taller estres, jornada deportiva), T3 (campana prevencion alcohol/SPA, salud mental), T4 (evaluacion indicadores, campana enfermedades cronicas, informe anual). Con fecha, responsable, recurso.'
    UNION SELECT 11, 'Indicadores de Gestion', 'indicadores', 'texto', 11,
           'Genera indicadores: Estructura (programa aprobado, presupuesto asignado vs ejecutado), Proceso (% actividades ejecutadas meta>=90%, cobertura participantes meta>=80%, campanas realizadas meta>=6), Resultado (variacion fumadores, variacion IMC normal, variacion sedentarismo, ausentismo cronicas, satisfaccion meta>=80%). Con formula, meta, frecuencia.'
    UNION SELECT 12, 'Evaluacion y Seguimiento', 'evaluacion_seguimiento', 'texto', 12,
           'Describe evaluacion: Seguimiento trimestral (cumplimiento, participacion, ajustes), Evaluacion semestral (indicadores proceso, impacto parcial), Evaluacion anual (todos indicadores, comparacion linea base, informe direccion, planificacion siguiente ano). Mejora continua y registros requeridos.'
) s
WHERE tc.tipo_documento = 'programa_estilos_vida_saludable'
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
WHERE tc.tipo_documento = 'programa_estilos_vida_saludable'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Programa de Estilos de Vida Saludable y Entornos Saludables', 'PRG-EVS', 'programa_estilos_vida_saludable', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'programa_estilos_vida_saludable')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PRG-EVS', '3.1.7')
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
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'programa_estilos_vida_saludable'");
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
