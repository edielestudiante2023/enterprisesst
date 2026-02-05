<?php
/**
 * Agrega/actualiza el prompt de metodología para PyP Salud
 * para que use los datos reales del Plan de Trabajo
 */

echo "=== AGREGANDO PROMPT DE METODOLOGIA PARA PYP SALUD ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

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

$promptMetodologia = 'Genera la metodología del Programa de Promoción y Prevención en Salud para {EMPRESA}.

IMPORTANTE: La metodología debe describir CÓMO se ejecutarán las ACTIVIDADES REALES listadas en el contexto de "ACTIVIDADES DE PROMOCIÓN Y PREVENCIÓN EN SALUD".

Estructura la metodología en las siguientes fases:

**1. FASE DE DIAGNÓSTICO:**
- Análisis del perfil sociodemográfico de los trabajadores
- Revisión de condiciones de salud (resultados de exámenes médicos)
- Análisis de ausentismo y morbilidad
- Identificación de necesidades de promoción y prevención

**2. FASE DE PLANEACIÓN:**
- Definición de actividades basadas en el diagnóstico
- Las actividades programadas en el Plan de Trabajo son (menciona las del contexto):
  [USA LAS ACTIVIDADES REALES DEL CONTEXTO AQUÍ]
- Asignación de recursos y responsables
- Definición de cronograma de ejecución

**3. FASE DE EJECUCIÓN:**
Describe cómo se implementará cada tipo de actividad del Plan de Trabajo:
- Exámenes médicos ocupacionales (ingreso, periódicos, egreso)
- Actividades de promoción de estilos de vida saludables
- Capacitaciones en autocuidado
- Campañas de salud y jornadas de bienestar
- Pausas activas y ejercicios de estiramiento
- Vigilancia epidemiológica

**4. FASE DE SEGUIMIENTO Y EVALUACIÓN:**
- Monitoreo de indicadores (usa los del contexto)
- Revisión trimestral de avance
- Ajustes al programa según resultados
- Documentación y reporte de hallazgos

NO generes contenido genérico. Menciona las actividades ESPECÍFICAS del Plan de Trabajo que aparecen en el contexto.
La metodología debe permitir entender cómo se ejecutará cada actividad planificada.';

foreach ($conexiones as $entorno => $config) {
    echo "=== " . strtoupper($entorno) . " ===\n";
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "Conectado\n";

        // Obtener id_tipo_config para programa_promocion_prevencion_salud
        $sql = "SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'programa_promocion_prevencion_salud'";
        $stmt = $pdo->query($sql);
        $tipoConfig = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tipoConfig) {
            echo "  No se encontro configuracion para programa_promocion_prevencion_salud\n";
            continue;
        }

        $idTipoConfig = $tipoConfig['id_tipo_config'];
        echo "  id_tipo_config: {$idTipoConfig}\n";

        // Verificar si existe la seccion metodologia
        $sql = "SELECT id_seccion_config FROM tbl_doc_secciones_config WHERE id_tipo_config = :id AND seccion_key = 'metodologia'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $idTipoConfig]);
        $seccion = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($seccion) {
            // Actualizar
            $sql = "UPDATE tbl_doc_secciones_config SET prompt_ia = :prompt WHERE id_seccion_config = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['prompt' => $promptMetodologia, 'id' => $seccion['id_seccion_config']]);
            echo "  Metodologia ACTUALIZADA\n";
        } else {
            // Insertar
            $sql = "INSERT INTO tbl_doc_secciones_config (id_tipo_config, seccion_key, nombre, numero, orden, prompt_ia, activo)
                    VALUES (:id_tipo, 'metodologia', 'Metodologia', 8, 8, :prompt, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id_tipo' => $idTipoConfig, 'prompt' => $promptMetodologia]);
            echo "  Metodologia INSERTADA\n";
        }

        // También actualizar responsabilidades, recursos y evaluacion_seguimiento
        $seccionesExtra = [
            'responsabilidades' => 'Genera las responsabilidades para el Programa de Promoción y Prevención en Salud de {EMPRESA}.

Las responsabilidades deben estar alineadas con las ACTIVIDADES del Plan de Trabajo listadas en el contexto.

Incluir:

**REPRESENTANTE LEGAL / ALTA DIRECCIÓN:**
- Aprobar el programa y sus recursos
- Asegurar la asignación presupuestal
- Participar en la revisión por la dirección

**RESPONSABLE DEL SG-SST:**
- Coordinar la ejecución de las actividades del programa
- Gestionar los exámenes médicos ocupacionales
- Implementar las actividades de promoción y prevención del PTA
- Realizar seguimiento a los indicadores del programa
- Documentar la ejecución de actividades

**COPASST / VIGÍA DE SST:**
- Participar en las actividades de promoción de la salud
- Verificar el cumplimiento del programa
- Proponer mejoras al programa

**TRABAJADORES:**
- Participar activamente en las actividades programadas
- Asistir a los exámenes médicos ocupacionales
- Reportar condiciones de salud que requieran atención
- Practicar el autocuidado',

            'recursos' => 'Genera la sección de recursos necesarios para el Programa de Promoción y Prevención en Salud de {EMPRESA}.

Basándote en las ACTIVIDADES del Plan de Trabajo listadas en el contexto, identifica:

**RECURSOS HUMANOS:**
- Responsable del SG-SST (coordinación del programa)
- Médico ocupacional o IPS contratada (exámenes médicos)
- Profesionales de apoyo según actividades (nutricionista, fisioterapeuta, etc.)

**RECURSOS FÍSICOS:**
- Espacio para capacitaciones y talleres
- Elementos para pausas activas
- Consultorio médico o convenio con IPS

**RECURSOS FINANCIEROS:**
- Presupuesto para exámenes médicos ocupacionales
- Presupuesto para campañas de salud
- Presupuesto para capacitaciones y talleres
(Indicar montos estimados según las actividades del PTA)

**RECURSOS TECNOLÓGICOS:**
- Sistema de registro y seguimiento
- Material audiovisual para capacitaciones',

            'evaluacion_seguimiento' => 'Genera la sección de Evaluación y Seguimiento del Programa de Promoción y Prevención en Salud de {EMPRESA}.

USA LOS INDICADORES listados en el contexto de "INDICADORES DE PROMOCIÓN Y PREVENCIÓN EN SALUD".

**EVALUACIÓN DEL PROGRAMA:**

1. **Indicadores de gestión:**
   [Lista los indicadores del contexto con su fórmula y meta]

2. **Periodicidad de medición:**
   - Indicadores mensuales: cobertura de actividades
   - Indicadores trimestrales: cumplimiento del programa
   - Indicadores anuales: efectividad del programa

**SEGUIMIENTO:**

1. **Revisión mensual:**
   - Verificar ejecución de actividades programadas
   - Registrar asistencia y participación

2. **Revisión trimestral:**
   - Calcular indicadores del programa
   - Identificar desviaciones y acciones correctivas

3. **Revisión semestral:**
   - Evaluar tendencias de ausentismo
   - Revisar resultados de exámenes médicos

4. **Revisión anual:**
   - Evaluación integral del programa
   - Definir mejoras para el siguiente año
   - Incluir en revisión por la dirección'
        ];

        foreach ($seccionesExtra as $key => $prompt) {
            $sql = "UPDATE tbl_doc_secciones_config SET prompt_ia = :prompt
                    WHERE id_tipo_config = :id_tipo AND seccion_key = :key";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute(['prompt' => $prompt, 'id_tipo' => $idTipoConfig, 'key' => $key]);
            $affected = $stmt->rowCount();
            echo "  {$key}: {$affected} fila(s) actualizada(s)\n";
        }

    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "=== COMPLETADO ===\n";
echo "Ahora la metodología y otras secciones usarán los datos reales del Plan de Trabajo e Indicadores.\n";
