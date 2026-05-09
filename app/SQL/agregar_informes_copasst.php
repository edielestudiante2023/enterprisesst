<?php
/**
 * Script para agregar dos tipos de documento de gestion del COPASST:
 *   1) informe_trimestral_copasst (codigo INF-COP-T)
 *   2) informe_anual_copasst       (codigo INF-COP-A)
 *
 * Estandar: 1.1.6 (vive en la carpeta de Conformacion COPASST)
 * Flujo: secciones_ia (Tipo A; clase PHP sobrescribe getContextoBase para inyectar datos del comite)
 *
 * Tambien aplica el ALTER para soportar trimestre 1-4 en tbl_documentos_sst.
 *
 * Ejecutar: php app/SQL/agregar_informes_copasst.php
 *
 * Orden de ejecucion: LOCAL primero. Solo si LOCAL termina OK se ejecuta PRODUCCION.
 */

$conexiones = [
    'local' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl'      => false
    ],
    'produccion' => [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_MR2SLvzRh3i_7o9fEHN',
        'ssl'      => true
    ]
];

// =========================================================================
// 0) ALTER: agregar columna trimestre TINYINT NULL si no existe (idempotente)
// =========================================================================
$sqlAlterTrimestre = <<<'SQL'
SELECT COUNT(*) AS existe
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME   = 'tbl_documentos_sst'
  AND COLUMN_NAME  = 'trimestre'
SQL;

$sqlAlterTrimestreApply = <<<'SQL'
ALTER TABLE tbl_documentos_sst
  ADD COLUMN trimestre TINYINT NULL DEFAULT NULL
  COMMENT '1-4 para informes trimestrales; NULL para los demas tipos'
  AFTER anio
SQL;

// =========================================================================
// 1A) Tipo de documento: informe_trimestral_copasst
// =========================================================================
$sqlTipoTrim = <<<'SQL'
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
VALUES
('informe_trimestral_copasst',
 'Informe Trimestral de Gestion del COPASST',
 'Informe trimestral de la gestion del Comite Paritario de Seguridad y Salud en el Trabajo (COPASST). Sintetiza reuniones, asistencia, decisiones, cumplimiento del cronograma y compromisos del trimestre, con recomendaciones del consultor SST generadas por IA y editables.',
 '1.1.6',
 'secciones_ia',
 'informes_comites',
 'bi-clipboard-data',
 50)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), descripcion = VALUES(descripcion), updated_at = NOW();
SQL;

// =========================================================================
// 1B) Secciones (9) del informe trimestral COPASST
// =========================================================================
$sqlSeccionesTrim = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, s.tipo_contenido, s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 AS numero, 'Resumen Ejecutivo' AS nombre, 'resumen_ejecutivo' AS seccion_key, 'texto' AS tipo_contenido, 1 AS orden,
'Genera el resumen ejecutivo del informe trimestral del COPASST usando los DATOS REALES DEL COMITE EN EL TRIMESTRE incluidos en el contexto. Debe incluir: cantidad de reuniones realizadas vs esperadas, % de asistencia promedio, principales decisiones tomadas, % de cumplimiento de compromisos del trimestre. Maximo 2 parrafos concisos. Tono profesional. NO uses tablas markdown.' AS prompt_ia
    UNION SELECT 2, 'Conformacion del COPASST', 'conformacion_comite', 'texto', 2,
'Describe la conformacion del COPASST vigente al cierre del trimestre con base en los DATOS REALES DEL COMITE incluidos en el contexto (lista de miembros, representacion, rol). Lista miembros principales y suplentes separados por representacion (empleador / trabajador). Identifica presidente y secretario. Menciona ingresos / retiros ocurridos durante el trimestre si los hubo. Aclara si el comite cumple paridad y vigencia conforme al Decreto 1072/2015 articulo 2.2.4.6.2 y la Resolucion 2013/1986.'
    UNION SELECT 3, 'Reuniones Realizadas', 'reuniones_realizadas', 'texto', 3,
'Sintetiza las reuniones del COPASST realizadas en el trimestre con base en los DATOS REALES (numero de acta, fecha, modalidad presencial/virtual, hora_inicio, hora_fin, lugar, quorum alcanzado). Indica la frecuencia esperada segun la periodicidad del comite (mensual segun Decreto 1072/2015 art 2.2.4.6.2). Si hay menos reuniones de las esperadas, identifica el gap y sugiere causas posibles. Para cada reunion menciona el orden_del_dia o tema principal de manera resumida.'
    UNION SELECT 4, 'Asistencia', 'asistencia', 'texto', 4,
'Analiza la asistencia al COPASST durante el trimestre con base en los DATOS REALES de asistentes por acta. Calcula el % de asistencia por miembro y el promedio del trimestre. Identifica miembros con asistencia critica (menor al 50%) y miembros con asistencia ejemplar. Lista las ausencias justificadas vs no justificadas. Recomienda accion si algun miembro presenta ausentismo recurrente sin justificacion.'
    UNION SELECT 5, 'Decisiones y Votaciones', 'decisiones_votaciones', 'texto', 5,
'Sintetiza las decisiones y votaciones del COPASST tomadas durante el trimestre, extraidas de los campos desarrollo, conclusiones y observaciones de las actas incluidas en el contexto. Agrupa las decisiones por tema (gestion de riesgos, capacitacion, investigacion de accidentes, evaluacion de proveedores SST, etc.). Mantenlo objetivo: solo lo que figura en las actas, sin agregar valoraciones propias.'
    UNION SELECT 6, 'Cumplimiento del Cronograma', 'cumplimiento_cronograma', 'texto', 6,
'Evalua el cumplimiento del cronograma del comite en el trimestre. Comparar la cantidad de reuniones esperadas (segun la periodicidad del comite, normalmente 3 en el trimestre si es mensual) vs las realizadas. Calcula el % de cumplimiento. Si hubo gaps, justifica con base en justificaciones de las actas (vacaciones, incapacidades, suspension de actividades, etc.). Si no hubo justificacion, marca como hallazgo a corregir.'
    UNION SELECT 7, 'Hallazgos Identificados', 'hallazgos', 'texto', 7,
'Lista los hallazgos / observaciones / no conformidades surgidas en las reuniones del COPASST durante el trimestre. Extrae de los campos observaciones y conclusiones de las actas y de los compromisos abiertos. Ordenalos por criticidad (alta / media / baja). Para cada hallazgo indica: descripcion breve, area / proceso afectado, fecha de identificacion (acta), y si tiene compromiso asociado.'
    UNION SELECT 8, 'Recomendaciones del Consultor SST', 'recomendaciones_ia', 'texto', 8,
'Genera entre 5 y 8 recomendaciones concretas y accionables del consultor SST en base a los datos del trimestre (asistencia baja, cumplimiento de cronograma, compromisos vencidos, hallazgos no cerrados, paridad amenazada, etc.). Cada recomendacion debe incluir: que hacer, por que, cual es el indicador o evidencia que la motiva, y plazo sugerido. Recuerda que el consultor podra editar estas recomendaciones manualmente.'
    UNION SELECT 9, 'Compromisos / Plan de Accion del Proximo Trimestre', 'plan_accion_proximo', 'texto', 9,
'Genera el plan de accion para el siguiente trimestre con base en (a) los compromisos pendientes y vencidos del trimestre actual incluidos en el contexto, (b) las recomendaciones generadas en la seccion anterior, y (c) las obligaciones legales del COPASST (proxima reunion, capacitacion del comite, investigacion de accidentes, etc.). Presenta una tabla con columnas: numero, accion, responsable, fecha limite, indicador de cierre. Minimo 5 acciones, maximo 12.'
) s
WHERE tc.tipo_documento = 'informe_trimestral_copasst'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
SQL;

// =========================================================================
// 1C) Firmantes del informe trimestral
// =========================================================================
$sqlFirmantesTrim = <<<'SQL'
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
SELECT tc.id_tipo_config, f.firmante_tipo, f.rol_display, f.columna_encabezado, f.orden, f.mostrar_licencia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 'responsable_sst' AS firmante_tipo, 'Elaboro' AS rol_display, 'Elaboro / Responsable del SG-SST' AS columna_encabezado, 1 AS orden, 1 AS mostrar_licencia
    UNION SELECT 'representante_legal', 'Aprobo', 'Aprobo / Representante Legal', 2, 0
) f
WHERE tc.tipo_documento = 'informe_trimestral_copasst'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// =========================================================================
// 1D) Plantilla y mapeo carpeta para trimestral
// =========================================================================
$sqlPlantillaTrim = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Informe Trimestral de Gestion del COPASST', 'INF-COP-T', 'informe_trimestral_copasst', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'informe_trimestral_copasst')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

$sqlMapeoCarpetaTrim = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('INF-COP-T', '1.1.6')
ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta);
SQL;

// =========================================================================
// 2A) Tipo de documento: informe_anual_copasst
// =========================================================================
$sqlTipoAnu = <<<'SQL'
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
VALUES
('informe_anual_copasst',
 'Informe Anual de Gestion del COPASST',
 'Informe anual de la gestion del COPASST. Sintetiza el comportamiento del comite a lo largo del anio (4 trimestres), con comparativo trimestral, cumplimiento del cronograma anual, compromisos cerrados/pendientes y recomendaciones para el siguiente periodo.',
 '1.1.6',
 'secciones_ia',
 'informes_comites',
 'bi-graph-up',
 51)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), descripcion = VALUES(descripcion), updated_at = NOW();
SQL;

// =========================================================================
// 2B) Secciones (10) del informe anual COPASST
// =========================================================================
$sqlSeccionesAnu = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, s.tipo_contenido, s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 AS numero, 'Resumen Ejecutivo Anual' AS nombre, 'resumen_ejecutivo' AS seccion_key, 'texto' AS tipo_contenido, 1 AS orden,
'Genera el resumen ejecutivo del informe ANUAL del COPASST usando los DATOS REALES DEL COMITE EN EL ANIO incluidos en el contexto. Debe incluir: total de reuniones realizadas vs esperadas (12 si periodicidad mensual), % de asistencia anual promedio, decisiones clave del anio, % de compromisos cumplidos al cierre del periodo. Maximo 2 parrafos concisos. Tono profesional. NO uses tablas markdown.' AS prompt_ia
    UNION SELECT 2, 'Conformacion del COPASST', 'conformacion_comite', 'texto', 2,
'Describe la composicion final del COPASST al cierre del anio con base en los DATOS REALES incluidos en el contexto, listando miembros vigentes (principales y suplentes, representacion empleador/trabajador, presidente, secretario). Detalla los cambios ocurridos durante el anio (ingresos, retiros, motivo). Confirma cumplimiento de paridad y vigencia conforme a Decreto 1072/2015 art 2.2.4.6.2.'
    UNION SELECT 3, 'Comparativo Trimestral', 'comparativo_trimestres', 'texto', 3,
'Genera una tabla comparativa de los 4 trimestres del anio con columnas: Trimestre, Reuniones realizadas, Reuniones esperadas, % asistencia, Compromisos generados, Compromisos cerrados, Principales decisiones (resumen). Identifica tendencias: mejora, estabilidad o deterioro en la gestion del comite a lo largo del anio. Aporta una conclusion sobre la trayectoria.'
    UNION SELECT 4, 'Reuniones Realizadas en el Anio', 'reuniones_realizadas', 'texto', 4,
'Lista todas las reuniones del COPASST en el anio con base en los DATOS REALES (numero acta, fecha, modalidad, quorum, tema principal). Calcula el total realizado vs el esperado segun periodicidad. Identifica los meses sin reunion y la causa segun consta en los registros.'
    UNION SELECT 5, 'Asistencia Anual', 'asistencia', 'texto', 5,
'Analiza la asistencia anual al COPASST. Calcula el % de asistencia por miembro y el promedio anual. Identifica miembros con ausentismo critico sostenido (menor al 50% durante el anio) y recomienda reemplazo. Identifica miembros ejemplares para reconocimiento. Compara con periodos anteriores si los datos lo permiten.'
    UNION SELECT 6, 'Decisiones y Votaciones del Anio', 'decisiones_votaciones', 'texto', 6,
'Sintetiza las principales decisiones y votaciones del COPASST tomadas durante el anio, agrupadas por tema (gestion de riesgos, capacitacion, investigacion ATEL, recomendaciones a la alta direccion, etc). Solo lo que figura en las actas, sin valoraciones propias.'
    UNION SELECT 7, 'Cumplimiento del Cronograma Anual', 'cumplimiento_cronograma', 'texto', 7,
'Evalua el cumplimiento global del cronograma del comite en el anio. % de cumplimiento (reuniones realizadas / esperadas * 100). Justificacion de gaps con base en las actas. Si hay incumplimientos sin justificar, levantarlos como hallazgo a la alta direccion.'
    UNION SELECT 8, 'Hallazgos del Anio', 'hallazgos', 'texto', 8,
'Lista los hallazgos / observaciones / no conformidades del anio surgidas en las reuniones, agrupados por tema (riesgos, capacitacion, equipo de proteccion personal, infraestructura, salud mental, etc.) y por estado (abierto / cerrado). Para cada hallazgo: descripcion, fecha de identificacion, estado actual, accion ejecutada o pendiente.'
    UNION SELECT 9, 'Recomendaciones del Consultor SST', 'recomendaciones_ia', 'texto', 9,
'Genera entre 6 y 10 recomendaciones del consultor SST para el siguiente periodo en base al comportamiento anual: continuidad del comite, plan de capacitacion, mejora de cronograma, cierre de hallazgos, integracion con plan anual de trabajo, etc. Cada recomendacion: que hacer, por que, indicador o evidencia que la motiva, plazo sugerido. El consultor podra editarlas manualmente.'
    UNION SELECT 10, 'Plan de Accion para el Proximo Anio', 'plan_accion_proximo', 'texto', 10,
'Genera el plan de accion priorizado para el siguiente anio con base en (a) los compromisos pendientes al cierre, (b) las recomendaciones del informe, y (c) las obligaciones legales del COPASST (renovacion, capacitacion, integracion con SG-SST). Tabla: numero, accion, responsable, fecha limite, indicador de cierre. Minimo 8 acciones, maximo 15.'
) s
WHERE tc.tipo_documento = 'informe_anual_copasst'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
SQL;

// =========================================================================
// 2C) Firmantes del informe anual
// =========================================================================
$sqlFirmantesAnu = <<<'SQL'
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
SELECT tc.id_tipo_config, f.firmante_tipo, f.rol_display, f.columna_encabezado, f.orden, f.mostrar_licencia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 'responsable_sst' AS firmante_tipo, 'Elaboro' AS rol_display, 'Elaboro / Responsable del SG-SST' AS columna_encabezado, 1 AS orden, 1 AS mostrar_licencia
    UNION SELECT 'representante_legal', 'Aprobo', 'Aprobo / Representante Legal', 2, 0
) f
WHERE tc.tipo_documento = 'informe_anual_copasst'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// =========================================================================
// 2D) Plantilla y mapeo carpeta para anual
// =========================================================================
$sqlPlantillaAnu = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Informe Anual de Gestion del COPASST', 'INF-COP-A', 'informe_anual_copasst', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'informe_anual_copasst')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

$sqlMapeoCarpetaAnu = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('INF-COP-A', '1.1.6')
ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta);
SQL;

// =========================================================================
// Helpers
// =========================================================================
function ejecutarSQL(PDO $pdo, string $sql, string $nombre): bool
{
    try {
        $pdo->exec($sql);
        echo "  [OK] $nombre\n";
        return true;
    } catch (PDOException $e) {
        echo "  [ERROR] $nombre: " . $e->getMessage() . "\n";
        return false;
    }
}

function aplicarAlterTrimestre(PDO $pdo, string $sqlExiste, string $sqlApply): bool
{
    try {
        $stmt = $pdo->query($sqlExiste);
        $row  = $stmt->fetch();
        if ((int) ($row['existe'] ?? 0) > 0) {
            echo "  [OK] ALTER trimestre (ya existia, no se aplica)\n";
            return true;
        }
        $pdo->exec($sqlApply);
        echo "  [OK] ALTER trimestre aplicado\n";
        return true;
    } catch (PDOException $e) {
        echo "  [ERROR] ALTER trimestre: " . $e->getMessage() . "\n";
        return false;
    }
}

// =========================================================================
// Loop principal: LOCAL primero, PRODUCCION solo si LOCAL OK
// =========================================================================
$localExito = false;

foreach ($conexiones as $entorno => $config) {
    echo "\n=== Ejecutando en $entorno ===\n";

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
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA]                = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "  [OK] Conexion establecida\n";

        $ok = true;

        // Paso 0: ALTER trimestre (idempotente)
        $ok = aplicarAlterTrimestre($pdo, $sqlAlterTrimestre, $sqlAlterTrimestreApply) && $ok;

        // Trimestral
        $ok = ejecutarSQL($pdo, $sqlTipoTrim,         'Tipo informe_trimestral_copasst')   && $ok;
        $ok = ejecutarSQL($pdo, $sqlSeccionesTrim,    'Secciones trimestral (9)')          && $ok;
        $ok = ejecutarSQL($pdo, $sqlFirmantesTrim,    'Firmantes trimestral')              && $ok;
        $ok = ejecutarSQL($pdo, $sqlPlantillaTrim,    'Plantilla trimestral (INF-COP-T)')  && $ok;
        $ok = ejecutarSQL($pdo, $sqlMapeoCarpetaTrim, 'Mapeo carpeta trimestral -> 1.1.6') && $ok;

        // Anual
        $ok = ejecutarSQL($pdo, $sqlTipoAnu,         'Tipo informe_anual_copasst')   && $ok;
        $ok = ejecutarSQL($pdo, $sqlSeccionesAnu,    'Secciones anual (10)')          && $ok;
        $ok = ejecutarSQL($pdo, $sqlFirmantesAnu,    'Firmantes anual')               && $ok;
        $ok = ejecutarSQL($pdo, $sqlPlantillaAnu,    'Plantilla anual (INF-COP-A)')   && $ok;
        $ok = ejecutarSQL($pdo, $sqlMapeoCarpetaAnu, 'Mapeo carpeta anual -> 1.1.6')  && $ok;

        // Verificar
        foreach (['informe_trimestral_copasst', 'informe_anual_copasst'] as $tipo) {
            $stmt = $pdo->prepare("SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento = :t");
            $stmt->execute([':t' => $tipo]);
            $r = $stmt->fetch();
            if ($r) {
                $stmt2 = $pdo->prepare("SELECT COUNT(*) AS total FROM tbl_doc_secciones_config WHERE id_tipo_config = :id");
                $stmt2->execute([':id' => $r['id_tipo_config']]);
                $r2 = $stmt2->fetch();
                echo "  [INFO] $tipo => id_tipo_config={$r['id_tipo_config']}, secciones={$r2['total']}\n";
            } else {
                echo "  [WARN] $tipo no encontrado tras INSERT\n";
            }
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
