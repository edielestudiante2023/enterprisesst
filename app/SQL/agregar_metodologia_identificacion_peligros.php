<?php
/**
 * Script para agregar tipo de documento: Metodologia para la Identificacion de Peligros,
 * Evaluacion y Valoracion de Riesgos
 * Estandar: 4.1.1
 * Ejecutar: php app/SQL/agregar_metodologia_identificacion_peligros.php
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
('metodologia_identificacion_peligros',
 'Metodologia para la Identificacion de Peligros, Evaluacion y Valoracion de Riesgos',
 'Establece la metodologia sistematica para identificar peligros, evaluar y valorar riesgos y establecer controles con alcance a todos los procesos, actividades rutinarias y no rutinarias, maquinas, equipos y todos los trabajadores, identificando los riesgos prioritarios. Basada en GTC 45:2012',
 '4.1.1',
 'secciones_ia',
 'procedimientos',
 'bi-shield-exclamation',
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
           'Genera el objetivo de la Metodologia para la Identificacion de Peligros, Evaluacion y Valoracion de Riesgos. Debe mencionar: establecer metodologia sistematica para identificar peligros, evaluar y valorar riesgos y establecer controles; alcance a todos los procesos, actividades rutinarias y no rutinarias, maquinas y equipos; aplica a todos los trabajadores independientemente de su vinculacion; identificar riesgos prioritarios; cumplimiento Decreto 1072/2015 art. 2.2.4.6.15 y 2.2.4.6.23, Res. 0312/2019 est. 4.1.1; referencia GTC 45:2012.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2,
           'Define el alcance de la metodologia. Aplica a TODOS los procesos (administrativos, operativos, de apoyo), actividades rutinarias y no rutinarias, todas las maquinas, equipos, herramientas e instalaciones. Incluye todos los trabajadores: directos, contratistas, subcontratistas, temporales, practicantes, visitantes. Cubre todas las sedes y centros de trabajo. Incluye peligros internos y externos. Clasificacion de peligros segun GTC 45.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', 3,
           'Define terminos clave segun GTC 45:2012: Peligro, Riesgo, Identificacion de peligros, Evaluacion del riesgo, Valoracion del riesgo, Nivel de consecuencia (NC), Nivel de probabilidad (NP), Nivel de riesgo (NR), Nivel de deficiencia (ND), Nivel de exposicion (NE), Aceptabilidad del riesgo, Control del riesgo, Actividad rutinaria, Actividad no rutinaria, Exposicion, Consecuencia, Medida de control, Matriz de peligros. 15-18 definiciones.'
    UNION SELECT 4, 'Marco Legal', 'marco_legal', 'texto', 4,
           'Presenta marco normativo: Decreto 1072/2015 art. 2.2.4.6.15 (Identificacion peligros) y art. 2.2.4.6.23 (Gestion peligros y riesgos), Res. 0312/2019 est. 4.1.1, GTC 45:2012, NTC-ISO 31000:2018, Ley 1562/2012, Decreto 1295/1994, Res. 2400/1979. Formato tabla con norma, ano y descripcion.'
    UNION SELECT 5, 'Responsabilidades', 'responsabilidades', 'texto', 5,
           'Define responsabilidades: Alta Direccion (recursos, aprobar metodologia, implementar controles), Responsable SST (aplicar metodologia, elaborar y actualizar Matriz, coordinar participacion trabajadores, comunicar resultados, priorizar riesgos), COPASST/Vigia (participar en identificacion, verificar alcance, proponer controles), Jefes de Area (reportar peligros, participar inspecciones, implementar controles), Trabajadores (reportar peligros y condiciones inseguras, participar activamente, cumplir controles).'
    UNION SELECT 6, 'Metodologia Adoptada', 'metodologia_adoptada', 'texto', 6,
           'Describe la metodologia adoptada: GTC 45:2012 como base, justificacion (guia colombiana reconocida por MinTrabajo, compatible con ISO 31000 e ISO 45001). Proceso general: 1) Definir instrumento (Matriz), 2) Clasificar procesos/actividades/tareas, 3) Identificar peligros, 4) Identificar controles existentes, 5) Evaluar riesgo (NP x NC), 6) Valorar aceptabilidad, 7) Plan de accion para no aceptables, 8) Revisar conveniencia. Clasificacion peligros GTC 45: biologico, fisico, quimico, psicosocial, biomecanico, condiciones de seguridad, fenomenos naturales.'
    UNION SELECT 7, 'Identificacion de Peligros', 'identificacion_peligros', 'texto', 7,
           'Describe como se identifican peligros: Fuentes (inspecciones planeadas, reporte actos/condiciones inseguras, investigacion incidentes, evaluaciones medicas, mediciones ambientales, estudios de puesto, FDS, observaciones directas). Proceso: describir proceso/zona, clasificar rutinaria/no rutinaria, clasificar peligro GTC 45, describir especificamente, identificar efectos, determinar expuestos. Alcance obligatorio: rutinarias y no rutinarias, toda maquinaria, todos los trabajadores, peligros internos y externos. Frecuencia: minimo anual y ante accidente mortal, evento catastrofico, o cambios significativos.'
    UNION SELECT 8, 'Evaluacion y Valoracion de Riesgos', 'evaluacion_valoracion_riesgos', 'texto', 8,
           'Describe evaluacion segun GTC 45:2012: NR = NP x NC, donde NP = ND x NE. Tablas de: Nivel de Deficiencia (MA=10, A=6, M=2, B=0), Nivel de Exposicion (Continua=4, Frecuente=3, Ocasional=2, Esporadica=1), Nivel de Consecuencia (Mortal=100, Muy grave=60, Grave=25, Leve=10). Tabla de aceptabilidad: Nivel I (4000-600, No Aceptable), Nivel II (500-150, No Aceptable o Aceptable con control), Nivel III (120-40, Aceptable), Nivel IV (20, Aceptable). Incluir ejemplo de calculo.'
    UNION SELECT 9, 'Determinacion de Controles', 'determinacion_controles', 'texto', 9,
           'Describe jerarquia de controles: 1) Eliminacion, 2) Sustitucion, 3) Controles de ingenieria, 4) Controles administrativos, 5) EPP. Clasificacion controles existentes: fuente, medio, individuo. Para cada control: descripcion, responsable, fecha limite, recursos, eficacia esperada. Criterios de seleccion: eficacia, factibilidad, no generar nuevos peligros, proteger todos los expuestos.'
    UNION SELECT 10, 'Priorizacion de Riesgos', 'priorizacion_riesgos', 'texto', 10,
           'Describe priorizacion: Criterios (nivel de riesgo NR, numero expuestos, peor consecuencia, requisito legal, accidentalidad). Tratamiento segun nivel: I (inmediata, suspender actividad), II (corto plazo, controles administrativos+EPP), III (mejorar si posible), IV (mantener controles). Riesgos prioritarios se comunican a alta direccion y COPASST/Vigia, se incluyen en plan de trabajo anual, seguimiento trimestral.'
    UNION SELECT 11, 'Documentacion y Actualizacion de la Matriz', 'documentacion_matriz', 'texto', 11,
           'Describe Matriz de Peligros: contenido minimo (proceso, actividad, tarea, clasificacion y descripcion peligro, efectos, controles existentes, evaluacion ND/NE/NP/NC/NR, aceptabilidad, expuestos, intervenciones, responsable, fecha). Actualizacion obligatoria ante: accidente mortal, evento catastrofico, cambios procesos/instalaciones/maquinaria, nuevos peligros, resultados inspecciones/auditorias, minimo anual. Participacion trabajadores obligatoria (art. 2.2.4.6.15 Decreto 1072). Aprobacion por alta direccion, socializacion COPASST/Vigia.'
    UNION SELECT 12, 'Registros y Evidencias', 'registros', 'texto', 12,
           'Lista registros: 1) Matriz de Peligros y Riesgos (GTC 45), 2) Procedimiento documentado, 3) Acta socializacion al COPASST/Vigia, 4) Listas asistencia capacitacion, 5) Registro participacion trabajadores, 6) Acta aprobacion alta direccion, 7) Plan accion riesgos priorizados, 8) Registros actualizaciones (control cambios), 9) Informes inspecciones seguridad, 10) Reportes condiciones/actos inseguros. Conservacion minimo 20 anos. Trazabilidad con fecha elaboracion, revision, aprobacion y historico versiones.'
) s
WHERE tc.tipo_documento = 'metodologia_identificacion_peligros'
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
WHERE tc.tipo_documento = 'metodologia_identificacion_peligros'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Metodologia Identificacion de Peligros y Valoracion de Riesgos', 'MET-IPR', 'metodologia_identificacion_peligros', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'metodologia_identificacion_peligros')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('MET-IPR', '4.1.1')
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
        $ok = ejecutarSQL($pdo, $sqlFirmantes, 'Firmantes (3)') && $ok;
        $ok = ejecutarSQL($pdo, $sqlPlantilla, 'Plantilla') && $ok;
        $ok = ejecutarSQL($pdo, $sqlMapeoCarpeta, 'Mapeo carpeta') && $ok;

        // Verificar resultado
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'metodologia_identificacion_peligros'");
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
