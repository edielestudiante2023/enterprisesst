<?php
/**
 * Script para agregar dos tipos de documento de gestion del COCOLAB:
 *   1) informe_trimestral_cocolab (codigo INF-COC-T)
 *   2) informe_anual_cocolab       (codigo INF-COC-A)
 *
 * Estandar: 1.1.8 (vive en la carpeta de Conformacion Comite de Convivencia)
 * Flujo: secciones_ia (clase PHP sobrescribe getContextoBase para inyectar datos del comite COCOLAB)
 *
 * NO requiere ALTER TABLE: la columna trimestre ya fue creada por agregar_informes_copasst.php
 *
 * Ejecutar: php app/SQL/agregar_informes_cocolab.php
 *
 * Orden: LOCAL primero. Solo si LOCAL OK, ejecuta PRODUCCION.
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
// 1A) Tipo de documento: informe_trimestral_cocolab
// =========================================================================
$sqlTipoTrim = <<<'SQL'
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
VALUES
('informe_trimestral_cocolab',
 'Informe Trimestral de Gestion del COCOLAB',
 'Informe trimestral de la gestion del Comite de Convivencia Laboral (COCOLAB). Sintetiza reuniones, asistencia, casos atendidos, cumplimiento del cronograma y compromisos del trimestre, con recomendaciones del consultor SST generadas por IA y editables. Mantiene confidencialidad sobre los casos.',
 '1.1.8',
 'secciones_ia',
 'informes_comites',
 'bi-clipboard-data',
 52)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), descripcion = VALUES(descripcion), updated_at = NOW();
SQL;

// =========================================================================
// 1B) Secciones (9) del informe trimestral COCOLAB
// =========================================================================
$sqlSeccionesTrim = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, s.tipo_contenido, s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 AS numero, 'Resumen Ejecutivo' AS nombre, 'resumen_ejecutivo' AS seccion_key, 'texto' AS tipo_contenido, 1 AS orden,
'Genera el resumen ejecutivo del informe trimestral del COCOLAB usando los DATOS REALES DEL COMITE EN EL TRIMESTRE incluidos en el contexto. Incluir: cantidad de reuniones realizadas vs esperadas, % de asistencia promedio, cantidad de casos / quejas atendidos en el periodo, % de cumplimiento de compromisos. Maximo 2 parrafos concisos. Tono profesional. NO menciones nombres de quejosos ni denunciados. NO uses tablas markdown.' AS prompt_ia
    UNION SELECT 2, 'Conformacion del COCOLAB', 'conformacion_comite', 'texto', 2,
'Describe la conformacion del COCOLAB vigente al cierre del trimestre con base en los DATOS REALES incluidos en el contexto (lista de miembros, representacion empleador/trabajador, rol). Identifica presidente y secretario. Cambios del trimestre (ingresos, retiros). Confirma cumplimiento de la Resolucion 0652 de 2012 (modificada por la Resolucion 1356 de 2012) sobre conformacion del Comite de Convivencia Laboral.'
    UNION SELECT 3, 'Reuniones Realizadas', 'reuniones_realizadas', 'texto', 3,
'Sintetiza las reuniones del COCOLAB realizadas en el trimestre con base en los DATOS REALES (numero de acta, fecha, modalidad, hora_inicio, hora_fin, lugar, quorum). Indica la frecuencia esperada para el COCOLAB (trimestral ordinaria conforme Resolucion 0652/2012, mas extraordinarias cuando hay casos urgentes). Si hay menos reuniones que las esperadas, identifica el gap. Para cada reunion menciona el orden_del_dia o tema principal de manera RESUMIDA y SIN identificar casos especificos.'
    UNION SELECT 4, 'Asistencia', 'asistencia', 'texto', 4,
'Analiza la asistencia al COCOLAB durante el trimestre con base en los DATOS REALES de asistentes por acta. Calcula % de asistencia por miembro y promedio del trimestre. Identifica miembros con asistencia critica (<50%). Recomienda accion si algun miembro presenta ausentismo recurrente sin justificacion.'
    UNION SELECT 5, 'Casos / Quejas Atendidos', 'casos_atendidos', 'texto', 5,
'Sintetiza los casos / quejas atendidos por el COCOLAB en el trimestre, con base en los DATOS REALES extraidos de campos desarrollo, conclusiones y compromisos de las actas. IMPORTANTE: mantenle ESTRICTA confidencialidad: NO menciones nombres de quejosos ni denunciados, NO identifiques areas pequenas que individualicen, NO transcribas detalles del caso. Reporta solo agregados: cantidad de casos abiertos al inicio del trimestre, cantidad recibidos en el periodo, tipo (acoso laboral, acoso sexual, violencia, conflicto interpersonal, otro), estado (en_proceso, conciliado, derivado a la ARL/Min Trabajo, archivado), y resultado general. Recuerda Ley 1010 de 2006 y Resolucion 3461 de 2025.'
    UNION SELECT 6, 'Cumplimiento del Cronograma', 'cumplimiento_cronograma', 'texto', 6,
'Evalua el cumplimiento del cronograma del COCOLAB en el trimestre. Compara reuniones ordinarias esperadas (1 por trimestre conforme Resolucion 0652/2012 art 7) y extraordinarias realizadas. Justifica gaps con base en las actas. Si hay incumplimientos sin justificar, marca como hallazgo a la alta direccion.'
    UNION SELECT 7, 'Hallazgos / Tendencias', 'hallazgos', 'texto', 7,
'Identifica hallazgos y tendencias del periodo SIN romper confidencialidad: tipos de conducta mas frecuentes en las quejas (sin nombrar personas), areas / procesos con mayor incidencia (de forma general), tiempos de atencion promedio, casos derivados a otras instancias. Hallazgos de gestion del comite (capacitacion pendiente, falta de protocolos, etc.). Ordenar por criticidad.'
    UNION SELECT 8, 'Recomendaciones del Consultor SST', 'recomendaciones_ia', 'texto', 8,
'Genera entre 5 y 8 recomendaciones del consultor SST en base al trimestre: capacitacion del comite, fortalecimiento del codigo de etica, campanias de prevencion del acoso laboral / sexual / violencias (Resolucion 3461/2025), articulacion con SST y Talento Humano, mejora de canales de denuncia. Cada recomendacion: que hacer, por que, indicador o evidencia que la motiva, plazo sugerido. El consultor podra editarlas manualmente.'
    UNION SELECT 9, 'Compromisos / Plan de Accion del Proximo Trimestre', 'plan_accion_proximo', 'texto', 9,
'Genera el plan de accion para el siguiente trimestre con base en (a) compromisos pendientes / vencidos al cierre del trimestre, (b) recomendaciones del consultor, y (c) obligaciones legales del COCOLAB (proxima reunion ordinaria, capacitacion del comite, atencion de casos abiertos, etc.). Tabla: numero, accion, responsable, fecha limite, indicador de cierre. Minimo 5 acciones, maximo 12.'
) s
WHERE tc.tipo_documento = 'informe_trimestral_cocolab'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
SQL;

// =========================================================================
// 1C) Firmantes
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
WHERE tc.tipo_documento = 'informe_trimestral_cocolab'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// =========================================================================
// 1D) Plantilla y mapeo carpeta para trimestral COCOLAB
// =========================================================================
$sqlPlantillaTrim = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Informe Trimestral de Gestion del COCOLAB', 'INF-COC-T', 'informe_trimestral_cocolab', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'informe_trimestral_cocolab')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

$sqlMapeoCarpetaTrim = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('INF-COC-T', '1.1.8')
ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta);
SQL;

// =========================================================================
// 2A) Tipo de documento: informe_anual_cocolab
// =========================================================================
$sqlTipoAnu = <<<'SQL'
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
VALUES
('informe_anual_cocolab',
 'Informe Anual de Gestion del COCOLAB',
 'Informe anual de gestion del Comite de Convivencia Laboral (COCOLAB). Sintetiza el comportamiento del comite a lo largo del anio, con comparativo trimestral, casos atendidos (en agregado y con confidencialidad), cumplimiento del cronograma anual, y recomendaciones del consultor SST.',
 '1.1.8',
 'secciones_ia',
 'informes_comites',
 'bi-graph-up',
 53)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), descripcion = VALUES(descripcion), updated_at = NOW();
SQL;

// =========================================================================
// 2B) Secciones (10) del informe anual COCOLAB
// =========================================================================
$sqlSeccionesAnu = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, s.tipo_contenido, s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 AS numero, 'Resumen Ejecutivo Anual' AS nombre, 'resumen_ejecutivo' AS seccion_key, 'texto' AS tipo_contenido, 1 AS orden,
'Genera el resumen ejecutivo del informe ANUAL del COCOLAB usando los DATOS REALES DEL COMITE EN EL ANIO. Incluir: total reuniones realizadas vs esperadas (4 ordinarias + extraordinarias), % asistencia anual, cantidad de casos atendidos en el anio (agregado y anonimo), % compromisos cumplidos. Maximo 2 parrafos. NO uses tablas markdown. Mantiene confidencialidad sobre casos.' AS prompt_ia
    UNION SELECT 2, 'Conformacion del COCOLAB', 'conformacion_comite', 'texto', 2,
'Describe la composicion final del COCOLAB al cierre del anio. Cambios durante el anio (ingresos, retiros, motivo). Cumplimiento Resolucion 0652/2012 y 1356/2012.'
    UNION SELECT 3, 'Comparativo Trimestral', 'comparativo_trimestres', 'texto', 3,
'Tabla comparativa T1-T4: reuniones realizadas, % asistencia, casos atendidos (cantidad agregada), compromisos generados / cerrados. Identifica tendencias: mejora, estabilidad o deterioro. Conclusion sobre la trayectoria.'
    UNION SELECT 4, 'Reuniones Realizadas en el Anio', 'reuniones_realizadas', 'texto', 4,
'Lista todas las reuniones del COCOLAB en el anio (numero acta, fecha, modalidad, quorum, tema general). Total realizado vs esperado. Identifica meses sin reunion y la causa.'
    UNION SELECT 5, 'Asistencia Anual', 'asistencia', 'texto', 5,
'Analiza la asistencia anual al COCOLAB. % por miembro y promedio anual. Identifica miembros con ausentismo critico (recomienda reemplazo). Compara con periodos anteriores si los datos lo permiten.'
    UNION SELECT 6, 'Casos / Quejas Atendidos en el Anio', 'casos_atendidos', 'texto', 6,
'Agregado anonimo de casos del anio: total recibidos, total cerrados, total derivados, total archivados. Distribucion por tipo (acoso laboral, sexual, violencia, conflicto interpersonal, otros). Tiempo promedio de atencion. ESTRICTA confidencialidad: NO nombres, NO areas individualizables, NO detalles del caso. Aplica Ley 1010 de 2006, Resoluciones 0652/2012, 1356/2012 y 3461 de 2025.'
    UNION SELECT 7, 'Cumplimiento del Cronograma Anual', 'cumplimiento_cronograma', 'texto', 7,
'% de cumplimiento del cronograma del comite en el anio (reuniones ordinarias minimas: 4 al anio segun Resolucion 0652/2012 art 7). Justificacion de gaps.'
    UNION SELECT 8, 'Hallazgos del Anio', 'hallazgos', 'texto', 8,
'Hallazgos / tendencias del anio sin romper confidencialidad: tipos de conducta mas frecuentes, areas con mayor incidencia (general), tiempos de respuesta, eficacia de intervenciones. Hallazgos de gestion del comite (capacitacion, protocolos). Acciones preventivas sugeridas.'
    UNION SELECT 9, 'Recomendaciones del Consultor SST', 'recomendaciones_ia', 'texto', 9,
'Recomendaciones (6 a 10) para el siguiente periodo: capacitacion COCOLAB, campanias preventivas (Resolucion 3461/2025 - acoso laboral, sexual, violencias basadas en genero), articulacion con SST y Talento Humano, codigo de etica, canales de denuncia, encuesta de clima/convivencia. IA + editable.'
    UNION SELECT 10, 'Plan de Accion para el Proximo Anio', 'plan_accion_proximo', 'texto', 10,
'Plan priorizado para el siguiente anio con base en compromisos pendientes, recomendaciones, y obligaciones legales (renovacion COCOLAB cada 2 anios segun Resolucion 0652/2012, capacitacion anual, etc). Tabla: numero, accion, responsable, fecha limite, indicador de cierre. Minimo 8, maximo 15.'
) s
WHERE tc.tipo_documento = 'informe_anual_cocolab'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
SQL;

// =========================================================================
// 2C) Firmantes
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
WHERE tc.tipo_documento = 'informe_anual_cocolab'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// =========================================================================
// 2D) Plantilla y mapeo carpeta para anual COCOLAB
// =========================================================================
$sqlPlantillaAnu = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Informe Anual de Gestion del COCOLAB', 'INF-COC-A', 'informe_anual_cocolab', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'informe_anual_cocolab')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

$sqlMapeoCarpetaAnu = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('INF-COC-A', '1.1.8')
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

// =========================================================================
// Loop principal
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

        // Trimestral
        $ok = ejecutarSQL($pdo, $sqlTipoTrim,         'Tipo informe_trimestral_cocolab')   && $ok;
        $ok = ejecutarSQL($pdo, $sqlSeccionesTrim,    'Secciones trimestral (9)')          && $ok;
        $ok = ejecutarSQL($pdo, $sqlFirmantesTrim,    'Firmantes trimestral')              && $ok;
        $ok = ejecutarSQL($pdo, $sqlPlantillaTrim,    'Plantilla trimestral (INF-COC-T)')  && $ok;
        $ok = ejecutarSQL($pdo, $sqlMapeoCarpetaTrim, 'Mapeo carpeta trimestral -> 1.1.8') && $ok;

        // Anual
        $ok = ejecutarSQL($pdo, $sqlTipoAnu,         'Tipo informe_anual_cocolab')   && $ok;
        $ok = ejecutarSQL($pdo, $sqlSeccionesAnu,    'Secciones anual (10)')          && $ok;
        $ok = ejecutarSQL($pdo, $sqlFirmantesAnu,    'Firmantes anual')               && $ok;
        $ok = ejecutarSQL($pdo, $sqlPlantillaAnu,    'Plantilla anual (INF-COC-A)')   && $ok;
        $ok = ejecutarSQL($pdo, $sqlMapeoCarpetaAnu, 'Mapeo carpeta anual -> 1.1.8')  && $ok;

        // Verificar
        foreach (['informe_trimestral_cocolab', 'informe_anual_cocolab'] as $tipo) {
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
