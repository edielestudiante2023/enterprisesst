<?php
/**
 * Script para agregar tipo de documento: Procedimiento de Investigacion de Incidentes,
 * Accidentes de Trabajo y Enfermedades Laborales
 * Estandar: 3.2.1
 * Ejecutar: php app/SQL/agregar_procedimiento_investigacion_accidentes.php
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
('procedimiento_investigacion_accidentes',
 'Procedimiento de Investigacion de Incidentes, Accidentes de Trabajo y Enfermedades Laborales',
 'Establece la metodologia para investigar incidentes, accidentes de trabajo y enfermedades laborales, determinando causas basicas e inmediatas, y realizando seguimiento a las acciones correctivas',
 '3.2.1',
 'secciones_ia',
 'procedimientos',
 'bi-exclamation-triangle',
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
           'Genera el objetivo del Procedimiento de Investigacion de Incidentes, Accidentes de Trabajo y Enfermedades Laborales. Debe mencionar: investigar TODOS los incidentes, accidentes y enfermedades laborales diagnosticadas, determinar causas basicas e inmediatas, prevenir nuevos casos, realizar seguimiento a acciones correctivas. Referencia al Decreto 1072/2015 art. 2.2.4.6.32, Resolucion 0312/2019 estandar 3.2.1 y Resolucion 1401/2007.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2,
           'Define el alcance: aplica a todos los trabajadores directos, contratistas, subcontratistas, temporales y visitantes. Cubre incidentes, accidentes de trabajo (leves, graves, mortales) y enfermedades laborales diagnosticadas. Aplica en todas las sedes y actividades externas. Incluye reporte a ARL, EPS y Direccion Territorial del Ministerio de Trabajo.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', 3,
           'Define terminos clave: Accidente de trabajo (Ley 1562/2012 art. 3), Incidente de trabajo, Enfermedad laboral (Ley 1562/2012 art. 4), Accidente grave (Res. 1401/2007 art. 3), Accidente mortal, Causa inmediata, Causa basica, Factor personal, Factor del trabajo, Investigacion de accidentes, FURAT, FUREL, Acto inseguro, Condicion insegura. 12-16 definiciones.'
    UNION SELECT 4, 'Marco Legal', 'marco_legal', 'texto', 4,
           'Marco normativo: Ley 1562/2012 art. 3 y 4, Decreto 1072/2015 art. 2.2.4.6.32 y 2.2.4.6.12, Resolucion 0312/2019 est. 3.2.1, Resolucion 1401/2007, Decreto 472/2015 (sanciones), Resolucion 156/2005 (FURAT/FUREL), Decreto 1530/1996 art. 4. Formato tabla.'
    UNION SELECT 5, 'Responsabilidades', 'responsabilidades', 'texto', 5,
           'Responsabilidades: Alta Direccion (garantizar investigacion, reportar AT graves/mortales al Ministerio), Responsable SST (liderar investigacion, diligenciar FURAT/FUREL, reportar ARL en 2 dias), COPASST/Vigia (participar investigacion, verificar acciones), Jefe inmediato (preservar evidencia), Trabajadores (reportar, colaborar), ARL (asesorar, investigar AT graves).'
    UNION SELECT 6, 'Reporte de Accidentes de Trabajo y Enfermedades Laborales', 'reporte_at_el', 'texto', 6,
           'Procedimiento de reporte: 1) Reporte interno inmediato (24h), 2) Reporte ARL via FURAT (2 dias habiles, Decreto 1530/1996), 3) Reporte EPS para atencion medica, 4) Reporte Direccion Territorial Ministerio de Trabajo (AT graves/mortales, 2 dias habiles, Decreto 472/2015), 5) Reporte EL via FUREL. Consecuencias no reporte: sanciones 1-1000 SMMLV.'
    UNION SELECT 7, 'Procedimiento de Investigacion de Incidentes y Accidentes', 'investigacion_incidentes_accidentes', 'texto', 7,
           'Etapas segun Res. 1401/2007: 1) Conformacion equipo investigador (jefe inmediato, responsable SST, COPASST; AT graves: profesional con licencia SST), 2) Atencion lesionado y aseguramiento area, 3) Recopilacion informacion (declaraciones, inspeccion, documentos), 4) Analisis informacion, 5) Determinacion causas, 6) Acciones correctivas, 7) Informe. Plazo: 15 dias calendario.'
    UNION SELECT 8, 'Investigacion de Enfermedades Laborales', 'investigacion_enfermedades', 'texto', 8,
           'Investigacion EL: 1) Notificacion (EPS/ARL/junta calificacion), 2) Recopilacion historial ocupacional y exposicion, 3) Analisis causalidad, 4) Acciones preventivas para trabajadores potencialmente expuestos (clave estandar 3.2.1), 5) Seguimiento medico trabajadores expuestos, 6) Registro FUREL e informe.'
    UNION SELECT 9, 'Determinacion de Causas Basicas e Inmediatas', 'causas_basicas_inmediatas', 'texto', 9,
           'Metodologia Res. 1401/2007: Causas inmediatas (actos inseguros, condiciones inseguras), Causas basicas (factores personales: capacidad, conocimiento, estres; factores del trabajo: supervision, diseno, mantenimiento). Metodologias: Arbol de causas, 5 Por Que, Espina de pescado (Ishikawa). Ajustar complejidad segun nivel empresa.'
    UNION SELECT 10, 'Acciones Correctivas y Preventivas', 'acciones_correctivas_preventivas', 'texto', 10,
           'Plan de accion por cada causa: jerarquia de controles (eliminacion > sustitucion > ingenieria > administrativos > EPP), responsable, fecha, recursos. Comunicar a trabajadores expuestos al mismo riesgo. Lecciones aprendidas. Actualizar matriz peligros y procedimientos. Plazos: inmediata 24h, corto 15 dias, mediano 30-60 dias. Proteger OTROS TRABAJADORES POTENCIALMENTE EXPUESTOS.'
    UNION SELECT 11, 'Seguimiento y Verificacion de Acciones', 'seguimiento_verificacion', 'texto', 11,
           'Verificacion cumplimiento en fechas establecidas. Indicadores: tasa accidentalidad, indice frecuencia, indice severidad, % acciones cerradas vs pendientes. Revision COPASST/Vigia en reuniones ordinarias. Retroalimentacion direccion. Cierre investigacion cuando todas las acciones implementadas. Conservacion registros 20 anos (Decreto 1072/2015 art. 2.2.4.6.12).'
    UNION SELECT 12, 'Registros y Evidencias', 'registros_evidencias', 'texto', 12,
           'Registros obligatorios: FURAT, FUREL, Informe investigacion (formato Res. 1401/2007), Declaraciones testigos, Registro fotografico, Reporte ARL, Reporte Direccion Territorial (AT graves/mortales), Plan de accion, Evidencia seguimiento, Actas COPASST, Lecciones aprendidas. Conservacion minimo 20 anos. Codificacion: FT-SST-IAT-01 a 03.'
) s
WHERE tc.tipo_documento = 'procedimiento_investigacion_accidentes'
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
WHERE tc.tipo_documento = 'procedimiento_investigacion_accidentes'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Procedimiento de Investigacion de Incidentes, Accidentes de Trabajo y Enfermedades Laborales', 'PRC-IAT', 'procedimiento_investigacion_accidentes', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'procedimiento_investigacion_accidentes')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PRC-IAT', '3.2.1')
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
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'procedimiento_investigacion_accidentes'");
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
