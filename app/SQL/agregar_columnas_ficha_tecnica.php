<?php
/**
 * Migración: Agregar columnas para Fichas Técnicas de Indicadores SST
 *
 * Columnas nuevas en tbl_indicadores_sst:
 * - definicion, interpretacion, origen_datos, cargo_responsable,
 *   cargos_conocer_resultado, analisis_datos, requiere_plan_accion, numero_accion
 *
 * Luego auto-poblar los 18 indicadores legales con valores por defecto.
 *
 * EJECUTAR: php app/SQL/agregar_columnas_ficha_tecnica.php
 */

if (php_sapi_name() !== 'cli') {
    die("Solo ejecutar desde CLI\n");
}

// ═══════════════════════════════════════════════════
// Valores por defecto para los 18 indicadores legales
// ═══════════════════════════════════════════════════
$valoresLegales = [
    // ESTRUCTURA
    'Disponibilidad de Recursos del SG-SST' => [
        'definicion'              => 'Mide la proporción de recursos técnicos, financieros, humanos y de infraestructura disponibles frente a los planeados para la implementación del SG-SST.',
        'interpretacion'          => 'Un resultado del 100% indica que todos los recursos planeados están disponibles. Valores menores requieren gestión para completar la asignación.',
        'origen_datos'            => 'Presupuesto SST, actas de asignación de recursos, plan de trabajo anual',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía',
    ],
    // PROCESO
    'Evaluación Inicial del SG-SST' => [
        'definicion'              => 'Mide el porcentaje de cumplimiento de los estándares mínimos evaluados en la evaluación inicial del SG-SST según la Resolución 0312/2019.',
        'interpretacion'          => 'A mayor porcentaje, mayor grado de implementación del SG-SST. Valores <60% requieren plan de mejora inmediato.',
        'origen_datos'            => 'Formato de evaluación inicial de estándares mínimos, Resolución 0312/2019',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía, ARL',
    ],
    'Cumplimiento del Plan de Trabajo Anual' => [
        'definicion'              => 'Mide el porcentaje de actividades ejecutadas del Plan de Trabajo Anual frente a las actividades programadas.',
        'interpretacion'          => 'Un resultado del 100% indica cumplimiento total del PTA. Valores menores indican actividades pendientes que requieren reprogramación.',
        'origen_datos'            => 'Plan de Trabajo Anual, cronograma de actividades, actas de ejecución',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía',
    ],
    'Cumplimiento del Programa de Capacitación' => [
        'definicion'              => 'Mide el porcentaje de capacitaciones ejecutadas frente a las programadas en el cronograma anual de capacitación.',
        'interpretacion'          => 'A mayor porcentaje, mayor cobertura de formación. Valores <80% requieren reprogramación de actividades.',
        'origen_datos'            => 'Cronograma de capacitación, registros de asistencia, evaluaciones',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía, trabajadores',
    ],
    'Intervención de Peligros Identificados (Matriz IPVR)' => [
        'definicion'              => 'Mide la proporción de peligros identificados en la Matriz IPVR que han sido intervenidos con medidas de control.',
        'interpretacion'          => 'Un resultado del 100% indica que todos los peligros identificados tienen controles implementados. Priorizar intervención por nivel de riesgo.',
        'origen_datos'            => 'Matriz de identificación de peligros, valoración y control de riesgos (IPVR)',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía, trabajadores',
    ],
    'Cumplimiento Programas de Vigilancia Epidemiológica' => [
        'definicion'              => 'Mide el porcentaje de actividades ejecutadas de los programas de vigilancia epidemiológica frente a las programadas.',
        'interpretacion'          => 'A mayor porcentaje, mejor seguimiento de la salud de los trabajadores expuestos a factores de riesgo prioritarios.',
        'origen_datos'            => 'Programas PVE, informes de monitoreo biológico, registros de actividades',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, médico ocupacional, ARL',
    ],
    'Eficacia de Acciones Preventivas, Correctivas y de Mejora' => [
        'definicion'              => 'Mide la proporción de acciones correctivas, preventivas y de mejora que fueron cerradas eficazmente dentro del plazo establecido.',
        'interpretacion'          => 'Valores ≥90% indican gestión efectiva. Valores menores requieren revisión del proceso de acciones de mejora.',
        'origen_datos'            => 'Registro de acciones correctivas y preventivas, auditorías internas, inspecciones',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía',
    ],
    'Investigación de Incidentes y Accidentes de Trabajo' => [
        'definicion'              => 'Mide la proporción de incidentes y accidentes de trabajo que fueron investigados conforme al procedimiento establecido.',
        'interpretacion'          => 'Debe ser 100%. Cualquier incidente/accidente no investigado incumple el Art. 2.2.4.6.32 del Decreto 1072/2015.',
        'origen_datos'            => 'Formato de investigación de incidentes/accidentes, FURAT, reportes ARL',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía, ARL',
    ],
    // RESULTADO
    'Cumplimiento de Objetivos del SG-SST' => [
        'definicion'              => 'Mide el porcentaje de objetivos del SG-SST alcanzados durante el periodo evaluado.',
        'interpretacion'          => 'Un resultado del 100% indica cumplimiento total de los objetivos. Valores menores requieren ajuste en la planificación.',
        'origen_datos'            => 'Plan de objetivos y metas del SG-SST, informes de gestión',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía',
    ],
    'Cumplimiento de Requisitos Legales Aplicables' => [
        'definicion'              => 'Mide la proporción de requisitos legales en SST identificados en la matriz legal que la organización cumple.',
        'interpretacion'          => 'Debe ser 100%. Valores menores indican incumplimientos normativos que pueden acarrear sanciones.',
        'origen_datos'            => 'Matriz de requisitos legales, evaluaciones de cumplimiento',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, asesor jurídico',
    ],
    'Resultados de Programas de Rehabilitación' => [
        'definicion'              => 'Mide la proporción de trabajadores que fueron reintegrados exitosamente al trabajo después de un programa de rehabilitación.',
        'interpretacion'          => 'A mayor porcentaje, mayor efectividad del programa de rehabilitación y reintegro laboral.',
        'origen_datos'            => 'Registros de rehabilitación, informes médicos, actas de reintegro',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, médico ocupacional, ARL',
    ],
    // 6 MÍNIMOS OBLIGATORIOS
    'Índice de Frecuencia de Accidentes de Trabajo (IF)' => [
        'definicion'              => 'Expresa el número de accidentes de trabajo ocurridos durante el último año por cada 240.000 horas hombre trabajadas.',
        'interpretacion'          => 'A menor valor, menor frecuencia de accidentalidad. Se debe comparar con el periodo anterior y con la media del sector económico.',
        'origen_datos'            => 'FURAT, registro de accidentes de trabajo, nómina (HHT)',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía, ARL, trabajadores',
    ],
    'Índice de Severidad de Accidentes de Trabajo (IS)' => [
        'definicion'              => 'Expresa el número de días perdidos y cargados por accidentes de trabajo durante el último año por cada 240.000 horas hombre trabajadas.',
        'interpretacion'          => 'A menor valor, menor severidad de los accidentes. Valores altos indican accidentes graves con muchos días de incapacidad.',
        'origen_datos'            => 'FURAT, incapacidades por AT, nómina (HHT)',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía, ARL, trabajadores',
    ],
    'Proporción de Accidentes de Trabajo Mortales (PATM)' => [
        'definicion'              => 'Expresa la relación porcentual de accidentes de trabajo mortales sobre el total de accidentes de trabajo ocurridos en el periodo.',
        'interpretacion'          => 'Debe ser 0%. Cualquier valor mayor a 0% indica una fatalidad que requiere investigación inmediata y acciones correctivas urgentes.',
        'origen_datos'            => 'FURAT, reportes ARL, investigaciones de accidentes mortales',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía, ARL, MinTrabajo',
    ],
    'Prevalencia de Enfermedad Laboral (PEL)' => [
        'definicion'              => 'Mide el número total de casos de enfermedad laboral (nuevos y existentes) por cada 100.000 trabajadores en el periodo.',
        'interpretacion'          => 'A menor valor, menor carga de enfermedad laboral. Se compara con estadísticas sectoriales de la ARL.',
        'origen_datos'            => 'Diagnósticos médicos ocupacionales, reportes EPS/ARL, historias clínicas ocupacionales',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, médico ocupacional, ARL',
    ],
    'Incidencia de Enfermedad Laboral (IEL)' => [
        'definicion'              => 'Mide el número de casos nuevos de enfermedad laboral por cada 100.000 trabajadores en el periodo.',
        'interpretacion'          => 'A menor valor, mejor control de los factores de riesgo. Un aumento indica falla en las medidas preventivas.',
        'origen_datos'            => 'Diagnósticos médicos ocupacionales, reportes EPS/ARL, primeros diagnósticos',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, médico ocupacional, ARL',
    ],
    'Ausentismo por Causa Médica (ACM)' => [
        'definicion'              => 'Mide la proporción de días de ausencia por incapacidades médicas frente al total de días de trabajo programados.',
        'interpretacion'          => 'A menor porcentaje, menor ausentismo. Valores altos requieren análisis de causas (enfermedad general vs laboral) y acciones correctivas.',
        'origen_datos'            => 'Registro de incapacidades médicas, nómina, RRHH',
        'cargo_responsable'       => 'Responsable del SG-SST',
        'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, RRHH, ARL',
    ],
];

// ═══════════════════════════════════════════════════
// Función principal
// ═══════════════════════════════════════════════════
function ejecutarMigracion(PDO $pdo, string $entorno, array $valoresLegales): array
{
    $resultado = ['columnas_agregadas' => 0, 'indicadores_actualizados' => 0, 'errores' => []];

    // 1. Agregar columnas nuevas (si no existen)
    $columnasNuevas = [
        'definicion'               => "TEXT NULL COMMENT 'Definición del indicador para ficha técnica'",
        'interpretacion'           => "TEXT NULL COMMENT 'Cómo interpretar el resultado'",
        'origen_datos'             => "VARCHAR(255) NULL COMMENT 'Fuente de los datos'",
        'cargo_responsable'        => "VARCHAR(255) NULL COMMENT 'Cargo responsable de medir'",
        'cargos_conocer_resultado' => "VARCHAR(500) NULL COMMENT 'Cargos que deben conocer el resultado'",
        'analisis_datos'           => "TEXT NULL COMMENT 'Análisis/interpretación textual de la sección 4'",
        'requiere_plan_accion'     => "TINYINT(1) NULL DEFAULT NULL COMMENT '1=SI, 0=NO, NULL=No evaluado'",
        'numero_accion'            => "VARCHAR(50) NULL COMMENT 'Código del plan de acción'",
    ];

    // Obtener columnas existentes
    $stmt = $pdo->query("DESCRIBE tbl_indicadores_sst");
    $columnasExistentes = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');

    // Determinar AFTER para cada columna
    $afterMap = [
        'definicion'               => 'nombre_indicador',
        'interpretacion'           => 'definicion',
        'origen_datos'             => 'interpretacion',
        'cargo_responsable'        => 'origen_datos',
        'cargos_conocer_resultado' => 'cargo_responsable',
        'analisis_datos'           => 'acciones_mejora',
        'requiere_plan_accion'     => 'analisis_datos',
        'numero_accion'            => 'requiere_plan_accion',
    ];

    foreach ($columnasNuevas as $col => $definicion) {
        if (!in_array($col, $columnasExistentes)) {
            $after = $afterMap[$col];
            // Si la columna AFTER no existe, no usar AFTER
            $afterClause = in_array($after, $columnasExistentes) ? " AFTER `{$after}`" : "";
            try {
                $pdo->exec("ALTER TABLE tbl_indicadores_sst ADD COLUMN `{$col}` {$definicion}{$afterClause}");
                $resultado['columnas_agregadas']++;
                $columnasExistentes[] = $col; // Para las siguientes iteraciones
                echo "  [+] Columna '{$col}' agregada\n";
            } catch (PDOException $e) {
                $resultado['errores'][] = "Error columna {$col}: " . $e->getMessage();
                echo "  [!] Error columna '{$col}': " . $e->getMessage() . "\n";
            }
        } else {
            echo "  [=] Columna '{$col}' ya existe\n";
        }
    }

    // 2. Auto-poblar indicadores legales con valores por defecto
    echo "\n  Actualizando indicadores legales con datos de ficha técnica...\n";

    $stmtUpdate = $pdo->prepare("
        UPDATE tbl_indicadores_sst
        SET definicion = :definicion,
            interpretacion = :interpretacion,
            origen_datos = :origen_datos,
            cargo_responsable = :cargo_responsable,
            cargos_conocer_resultado = :cargos_conocer_resultado
        WHERE nombre_indicador = :nombre_indicador
          AND (definicion IS NULL OR definicion = '')
    ");

    foreach ($valoresLegales as $nombre => $valores) {
        $stmtUpdate->execute([
            ':definicion'               => $valores['definicion'],
            ':interpretacion'           => $valores['interpretacion'],
            ':origen_datos'             => $valores['origen_datos'],
            ':cargo_responsable'        => $valores['cargo_responsable'],
            ':cargos_conocer_resultado' => $valores['cargos_conocer_resultado'],
            ':nombre_indicador'         => $nombre,
        ]);
        $filas = $stmtUpdate->rowCount();
        if ($filas > 0) {
            $resultado['indicadores_actualizados'] += $filas;
            echo "  [U] '{$nombre}' → {$filas} actualizados\n";
        }
    }

    return $resultado;
}

// ═══════════════════════════════════════════════════
// Ejecutar: LOCAL primero, luego PROD
// ═══════════════════════════════════════════════════

echo "═══════════════════════════════════════════════\n";
echo "  MIGRACIÓN: Columnas para Fichas Técnicas\n";
echo "═══════════════════════════════════════════════\n\n";

// LOCAL
echo "▶ EJECUTANDO EN LOCAL...\n";
try {
    $pdoLocal = new PDO(
        'mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $resLocal = ejecutarMigracion($pdoLocal, 'LOCAL', $valoresLegales);
    echo "\n  ✓ LOCAL completado: {$resLocal['columnas_agregadas']} columnas, {$resLocal['indicadores_actualizados']} indicadores actualizados\n";
    if (!empty($resLocal['errores'])) {
        echo "  ⚠ Errores LOCAL: " . implode(', ', $resLocal['errores']) . "\n";
    }
} catch (PDOException $e) {
    echo "  ✗ ERROR LOCAL: " . $e->getMessage() . "\n";
    echo "  Abortando. No se ejecutará en PROD.\n";
    exit(1);
}

echo "\n";

// PROD
echo "▶ EJECUTANDO EN PRODUCCIÓN...\n";
try {
    $pdoProd = new PDO(
        'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
        'cycloid_userdb', 'AVNS_iDypWizlpMRwHIORJGG',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $resProd = ejecutarMigracion($pdoProd, 'PROD', $valoresLegales);
    echo "\n  ✓ PROD completado: {$resProd['columnas_agregadas']} columnas, {$resProd['indicadores_actualizados']} indicadores actualizados\n";
    if (!empty($resProd['errores'])) {
        echo "  ⚠ Errores PROD: " . implode(', ', $resProd['errores']) . "\n";
    }
} catch (PDOException $e) {
    echo "  ✗ ERROR PROD: " . $e->getMessage() . "\n";
}

echo "\n═══════════════════════════════════════════════\n";
echo "  MIGRACIÓN COMPLETA\n";
echo "═══════════════════════════════════════════════\n";
