<?php
/**
 * Script para agregar tipo de documento: Procedimiento de Gestion del Cambio
 * Estandar: 2.11.1
 * Ejecutar: php app/SQL/agregar_procedimiento_gestion_cambio.php
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
('procedimiento_gestion_cambio',
 'Procedimiento de Gestion del Cambio',
 'Establece la metodologia para evaluar el impacto sobre la SST que generan los cambios internos y externos a la empresa, e informar y capacitar a los trabajadores',
 '2.11.1',
 'secciones_ia',
 'procedimientos',
 'bi-arrow-left-right',
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
           'Genera el objetivo del Procedimiento de Gestion del Cambio en SST. Debe mencionar el proposito de evaluar el impacto de cambios internos y externos sobre la SST, informar y capacitar a los trabajadores, y el cumplimiento del Decreto 1072/2015 art. 2.2.4.6.26 y Resolucion 0312/2019 estandar 2.11.1.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2,
           'Define el alcance: aplica a cambios internos (procesos, instalaciones, maquinaria, metodos, estructura, personal, turnos, contratistas) y externos (legislacion, tecnologia, entorno). Aplica a alta direccion, responsable SST, jefes de area, trabajadores, contratistas, COPASST/Vigia.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', 3,
           'Define terminos clave: Gestion del cambio, Cambio interno, Cambio externo, Evaluacion de impacto, Peligro emergente, Riesgo residual, Analisis de riesgos, Cambio temporal, Cambio permanente. 9-11 definiciones basadas en Decreto 1072/2015.'
    UNION SELECT 4, 'Marco Legal', 'marco_legal', 'texto', 4,
           'Lista marco legal: Decreto 1072/2015 art. 2.2.4.6.26 (Gestion del cambio) y art. 2.2.4.6.8 num. 9, Resolucion 0312/2019 estandar 2.11.1, Ley 1562/2012, ISO 45001:2018 clausula 8.1.3 como referencia voluntaria.'
    UNION SELECT 5, 'Responsabilidades', 'responsabilidades', 'texto', 5,
           'Define responsabilidades: Alta Direccion (aprobar cambios, asegurar recursos), Responsable SST (identificar, evaluar, controlar, comunicar), COPASST/Vigia (participar en evaluacion, verificar controles), Jefes de area (reportar, implementar), Trabajadores (reportar, capacitarse, cumplir controles).'
    UNION SELECT 6, 'Tipos de Cambios Internos y Externos', 'tipos_cambios', 'texto', 6,
           'Describe tipos de cambios internos (procesos, maquinaria, instalaciones, estructura organizacional, metodos de trabajo, materias primas, turnos, contratistas) y externos (legislacion, requisitos clientes, tecnologias, entorno, emergencias, proveedores). Tabla con categoria, ejemplo y responsable de reportar.'
    UNION SELECT 7, 'Procedimiento de Evaluacion del Impacto', 'evaluacion_impacto', 'texto', 7,
           'Describe 5 pasos: 1-Identificacion (registro, clasificacion), 2-Analisis de impacto (peligros, controles existentes, nuevos controles), 3-Medidas de control (jerarquia de controles, responsables, cronograma), 4-Aprobacion (responsable SST, alta direccion), 5-Implementacion y seguimiento. Incluir diagrama de flujo textual.'
    UNION SELECT 8, 'Comunicacion e Informacion a los Trabajadores', 'comunicacion_informacion', 'texto', 8,
           'Describe comunicacion antes (informar cambio, peligros, medidas), durante (instrucciones, senalizacion, supervision), despues (retroalimentacion, actualizacion procedimientos, lecciones aprendidas). Registros: listas asistencia, actas COPASST, comunicados.'
    UNION SELECT 9, 'Capacitacion ante Cambios', 'capacitacion_cambios', 'texto', 9,
           'Describe cuando se requiere capacitacion (nuevos peligros, cambio procedimientos, nueva maquinaria, cambio EPP). Contenido minimo, metodologia (presencial, material apoyo, evaluacion). Registros: lista asistencia, material, evaluacion conocimiento.'
    UNION SELECT 10, 'Seguimiento y Control', 'seguimiento_control', 'texto', 10,
           'Describe seguimiento corto plazo (1-4 semanas: verificar controles, monitorear incidentes) y mediano plazo (1-6 meses: eficacia medidas, indicadores). Indicadores: cambios evaluados vs no evaluados, tiempo respuesta, incidentes asociados. Revision trimestral y anual.'
    UNION SELECT 11, 'Registros y Evidencias', 'registros', 'texto', 11,
           'Lista registros: Formato Solicitud y Registro de Cambios, Formato Evaluacion de Impacto SST, Plan de Accion, Acta Comunicacion, Lista Asistencia Capacitacion, Registro Fotografico, Actualizacion Matriz Peligros. Conservacion minimo 20 anos. Trazabilidad con consecutivo unico.'
) s
WHERE tc.tipo_documento = 'procedimiento_gestion_cambio'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
SQL;

// SQL para firmantes (3 firmantes: consultor, responsable SST, representante legal)
$sqlFirmantes = <<<'SQL'
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
SELECT tc.id_tipo_config, f.firmante_tipo, f.rol_display, f.columna_encabezado, f.orden, f.mostrar_licencia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 'consultor_sst' as firmante_tipo, 'Elaboro' as rol_display, 'Elaboro / Consultor SST' as columna_encabezado, 1 as orden, 1 as mostrar_licencia
    UNION SELECT 'responsable_sst', 'Reviso', 'Reviso / Responsable del SG-SST', 2, 1
    UNION SELECT 'representante_legal', 'Aprobo', 'Aprobo / Representante Legal', 3, 0
) f
WHERE tc.tipo_documento = 'procedimiento_gestion_cambio'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Procedimiento de Gestion del Cambio', 'PRC-GDC', 'procedimiento_gestion_cambio', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'procedimiento_gestion_cambio')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PRC-GDC', '2.11.1')
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
        $ok = true;
        $ok = ejecutarSQL($pdo, $sqlTipo, 'Tipo de documento') && $ok;
        $ok = ejecutarSQL($pdo, $sqlSecciones, 'Secciones') && $ok;
        $ok = ejecutarSQL($pdo, $sqlFirmantes, 'Firmantes') && $ok;
        $ok = ejecutarSQL($pdo, $sqlPlantilla, 'Plantilla') && $ok;
        $ok = ejecutarSQL($pdo, $sqlMapeoCarpeta, 'Mapeo carpeta') && $ok;

        // Verificar resultado
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'procedimiento_gestion_cambio'");
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

        if ($entorno === 'local' && !$ok) {
            echo "\n  [ABORT] Errores en LOCAL - NO se ejecutara en produccion\n";
            break;
        }

    } catch (PDOException $e) {
        echo "  [ERROR] Conexion: " . $e->getMessage() . "\n";
        if ($entorno === 'local') {
            echo "\n  [ABORT] Error en LOCAL - NO se ejecutara en produccion\n";
            break;
        }
    }
}

echo "\n=== Proceso completado ===\n";
