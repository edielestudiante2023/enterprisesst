<?php
/**
 * Script de repoblación fuzzy - Ficha Técnica de Indicadores
 *
 * Problema: El script original (agregar_columnas_ficha_tecnica.php) usaba
 * WHERE nombre_indicador = '...' con match exacto. Los indicadores creados
 * ANTES del auto-seed tienen nombres ligeramente diferentes (sin tildes,
 * abreviados, etc.) y no matchearon.
 *
 * Este script usa mapeo fuzzy basado en palabras clave para poblar los
 * campos de ficha técnica (definicion, interpretacion, origen_datos, etc.)
 * en indicadores que aún los tienen vacíos.
 *
 * Ejecución: php app/SQL/repoblar_fichas_tecnicas_fuzzy.php
 */

// ═══════════════════════════════════════════════════
// Mapeo fuzzy: patrón LIKE → datos de ficha técnica
// ═══════════════════════════════════════════════════
$mapeoFuzzy = [
    // ── ESTRUCTURA ──
    [
        'patron' => '%Plan de Trabajo Anual%',
        'datos' => [
            'definicion'              => 'Mide el porcentaje de actividades del Plan de Trabajo Anual del SG-SST que fueron ejecutadas en el periodo evaluado.',
            'interpretacion'          => 'Un resultado del 100% indica cumplimiento total del PTA. Valores menores indican actividades pendientes que requieren reprogramación.',
            'origen_datos'            => 'Plan de Trabajo Anual, cronograma de actividades, actas de ejecución',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía',
        ],
    ],
    [
        'patron' => '%Disponibilidad%Recursos%SG%SST%',
        'datos' => [
            'definicion'              => 'Evalúa si la organización ha asignado los recursos humanos, financieros, técnicos y físicos necesarios para implementar el SG-SST.',
            'interpretacion'          => 'Un resultado del 100% indica asignación total de recursos. Valores menores requieren gestión ante la alta dirección.',
            'origen_datos'            => 'Presupuesto SST aprobado, actas de comité, informes de gestión',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía',
        ],
    ],
    [
        'patron' => '%Evaluaci_n Inicial%SG%SST%',
        'datos' => [
            'definicion'              => 'Verifica si se realizó la evaluación inicial del SG-SST conforme a los estándares mínimos de la Resolución 0312/2019.',
            'interpretacion'          => 'Debe ser 100% (SI se realizó). Es requisito previo para la planificación del sistema.',
            'origen_datos'            => 'Formato de evaluación inicial, autoevaluación de estándares mínimos',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, ARL',
        ],
    ],
    // ── PROCESO ──
    [
        'patron' => '%Capacitaci_n%SST%',
        'datos' => [
            'definicion'              => 'Mide el porcentaje de capacitaciones en SST ejecutadas frente a las programadas.',
            'interpretacion'          => 'A mayor porcentaje, mayor cobertura de formación en SST. Valores <80% requieren reprogramación.',
            'origen_datos'            => 'Cronograma de capacitación, registros de asistencia, evaluaciones',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía, trabajadores',
        ],
    ],
    [
        'patron' => '%Cronograma%Capacitaci_n%',
        'datos' => [
            'definicion'              => 'Mide el porcentaje de cumplimiento del cronograma de capacitación anual.',
            'interpretacion'          => 'A mayor porcentaje, mejor cumplimiento del plan de formación. Se debe reprogramar actividades incumplidas.',
            'origen_datos'            => 'Cronograma de capacitación, registros de asistencia',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía',
        ],
    ],
    [
        'patron' => '%Programa%Capacitaci_n%',
        'datos' => [
            'definicion'              => 'Mide el porcentaje de capacitaciones ejecutadas frente a las programadas en el programa anual.',
            'interpretacion'          => 'A mayor porcentaje, mayor cobertura de formación. Valores <80% requieren reprogramación de actividades.',
            'origen_datos'            => 'Programa de capacitación, registros de asistencia, evaluaciones',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía, trabajadores',
        ],
    ],
    [
        'patron' => '%Eficacia%Capacitaci_n%',
        'datos' => [
            'definicion'              => 'Mide el porcentaje de trabajadores que alcanzaron el nivel esperado en las evaluaciones post-capacitación.',
            'interpretacion'          => 'Valores ≥80% indican formación efectiva. Valores menores requieren refuerzo o cambio de metodología.',
            'origen_datos'            => 'Evaluaciones de conocimiento, pre-test y post-test, registros de asistencia',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, trabajadores',
        ],
    ],
    [
        'patron' => '%Oportunidad%Capacitaci_n%',
        'datos' => [
            'definicion'              => 'Mide si las capacitaciones se ejecutaron dentro de las fechas programadas.',
            'interpretacion'          => 'Debe ser 100%. Valores menores indican retrasos que pueden afectar la competencia de los trabajadores.',
            'origen_datos'            => 'Cronograma de capacitación, fechas reales de ejecución',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST',
        ],
    ],
    [
        'patron' => '%Inducci_n General%',
        'datos' => [
            'definicion'              => 'Mide la cobertura de inducción general en SST para trabajadores nuevos.',
            'interpretacion'          => 'Debe ser 100%. Todo trabajador nuevo debe recibir inducción antes de iniciar labores.',
            'origen_datos'            => 'Registros de inducción, formatos de asistencia, nómina de nuevos ingresos',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, RRHH',
        ],
    ],
    [
        'patron' => '%Cobertura%Inducci_n%',
        'datos' => [
            'definicion'              => 'Mide el porcentaje de trabajadores que recibieron inducción en SST respecto al total de nuevos ingresos.',
            'interpretacion'          => 'Debe ser 100%. Todo trabajador nuevo debe recibir inducción en SST antes de iniciar labores.',
            'origen_datos'            => 'Registros de inducción, formatos de asistencia, nómina de nuevos ingresos',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, RRHH',
        ],
    ],
    [
        'patron' => '%Programa%Inducci_n%',
        'datos' => [
            'definicion'              => 'Mide el cumplimiento del programa de inducción y reinducción en SST.',
            'interpretacion'          => 'A mayor porcentaje, mejor cobertura de formación inicial. Debe ser 100% para nuevos ingresos.',
            'origen_datos'            => 'Programa de inducción, registros de asistencia, evaluaciones',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, RRHH',
        ],
    ],
    [
        'patron' => '%Oportunidad%Inducci_n%',
        'datos' => [
            'definicion'              => 'Mide si las inducciones se realizaron dentro del plazo establecido (antes de iniciar labores).',
            'interpretacion'          => 'Debe ser 100%. Inducciones tardías exponen al trabajador a riesgos sin la formación necesaria.',
            'origen_datos'            => 'Fechas de ingreso vs fechas de inducción, registros RRHH',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, RRHH',
        ],
    ],
    [
        'patron' => '%Efectividad%Inducci_n%',
        'datos' => [
            'definicion'              => 'Mide el porcentaje de trabajadores que aprobaron la evaluación posterior a la inducción.',
            'interpretacion'          => 'Valores ≥80% indican inducción efectiva. Valores menores requieren refuerzo del contenido.',
            'origen_datos'            => 'Evaluaciones post-inducción, registros de asistencia',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, RRHH',
        ],
    ],
    [
        'patron' => '%Reinducci_n%Anual%',
        'datos' => [
            'definicion'              => 'Mide el porcentaje de trabajadores que recibieron reinducción anual en SST.',
            'interpretacion'          => 'Debe ser 100%. La reinducción anual es obligatoria para refrescar conocimientos en SST.',
            'origen_datos'            => 'Registros de reinducción, formatos de asistencia, nómina',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, trabajadores',
        ],
    ],
    [
        'patron' => '%Peligros Intervenidos%',
        'datos' => [
            'definicion'              => 'Mide la proporción de peligros identificados que han sido intervenidos con medidas de control.',
            'interpretacion'          => 'Un resultado del 100% indica que todos los peligros identificados tienen controles implementados. Priorizar intervención por nivel de riesgo.',
            'origen_datos'            => 'Matriz de identificación de peligros, valoración y control de riesgos (IPVR)',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía, trabajadores',
        ],
    ],
    [
        'patron' => '%Intervenci_n%Peligros%',
        'datos' => [
            'definicion'              => 'Mide la proporción de peligros identificados en la Matriz IPVR que han sido intervenidos con medidas de control.',
            'interpretacion'          => 'Un resultado del 100% indica que todos los peligros identificados tienen controles implementados.',
            'origen_datos'            => 'Matriz IPVR, planes de acción, registros de intervención',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía',
        ],
    ],
    [
        'patron' => '%Vigilancia Epidemiol_gica%',
        'datos' => [
            'definicion'              => 'Mide el porcentaje de actividades ejecutadas de los programas de vigilancia epidemiológica frente a las programadas.',
            'interpretacion'          => 'A mayor porcentaje, mejor seguimiento de la salud de los trabajadores expuestos a factores de riesgo prioritarios.',
            'origen_datos'            => 'Programas PVE, informes de monitoreo biológico, registros de actividades',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, médico ocupacional, ARL',
        ],
    ],
    [
        'patron' => '%Acciones%Correctivas%Mejora%',
        'datos' => [
            'definicion'              => 'Mide la proporción de acciones correctivas, preventivas y de mejora cerradas eficazmente dentro del plazo establecido.',
            'interpretacion'          => 'Valores ≥90% indican gestión efectiva. Valores menores requieren revisión del proceso de acciones de mejora.',
            'origen_datos'            => 'Registro de acciones correctivas y preventivas, auditorías internas, inspecciones',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía',
        ],
    ],
    [
        'patron' => '%Investigaci_n%Incidentes%Accidentes%',
        'datos' => [
            'definicion'              => 'Mide la proporción de incidentes y accidentes de trabajo que fueron investigados conforme al procedimiento establecido.',
            'interpretacion'          => 'Debe ser 100%. Cualquier incidente/accidente no investigado incumple el Art. 2.2.4.6.32 del Decreto 1072/2015.',
            'origen_datos'            => 'Formato de investigación de incidentes/accidentes, FURAT, reportes ARL',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía, ARL',
        ],
    ],
    [
        'patron' => '%Simulacro%Emergencia%',
        'datos' => [
            'definicion'              => 'Mide el cumplimiento y oportunidad de los simulacros de emergencia programados.',
            'interpretacion'          => 'Debe ser 100%. Los simulacros permiten evaluar la preparación ante emergencias y mejorar los planes.',
            'origen_datos'            => 'Plan de emergencias, informes de simulacros, registros de asistencia',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, brigada de emergencias, COPASST/Vigía',
        ],
    ],
    [
        'patron' => '%Participaci_n%Simulacro%',
        'datos' => [
            'definicion'              => 'Mide el porcentaje de trabajadores que participaron en los simulacros de emergencia.',
            'interpretacion'          => 'A mayor porcentaje, mejor preparación del personal ante emergencias. Meta mínima recomendada: 90%.',
            'origen_datos'            => 'Registros de asistencia a simulacros, nómina',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, brigada de emergencias',
        ],
    ],
    [
        'patron' => '%Ex_menes M_dicos Ocupacionales%',
        'datos' => [
            'definicion'              => 'Mide la cobertura de exámenes médicos ocupacionales realizados respecto a los programados.',
            'interpretacion'          => 'Debe ser 100%. Todo trabajador expuesto a factores de riesgo debe tener exámenes médicos vigentes.',
            'origen_datos'            => 'Programa de exámenes médicos, certificados de aptitud, historias clínicas ocupacionales',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, médico ocupacional',
        ],
    ],
    [
        'patron' => '%Cobertura%Examenes%Quimica%',
        'datos' => [
            'definicion'              => 'Mide el porcentaje de trabajadores expuestos a riesgo químico que cuentan con exámenes médicos específicos.',
            'interpretacion'          => 'Debe ser 100% para trabajadores expuestos. Priorizar según nivel de exposición.',
            'origen_datos'            => 'Matriz de riesgo químico, certificados de aptitud, monitoreo biológico',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, médico ocupacional, ARL',
        ],
    ],
    [
        'patron' => '%Actividades%PyP%Salud%',
        'datos' => [
            'definicion'              => 'Mide el cumplimiento de actividades de promoción y prevención en salud programadas.',
            'interpretacion'          => 'A mayor porcentaje, mejor cobertura de promoción de la salud. Meta mínima: 80%.',
            'origen_datos'            => 'Programa de PyP, registros de actividades, informes de salud',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, médico ocupacional',
        ],
    ],
    [
        'patron' => '%Promoci_n%Salud%',
        'datos' => [
            'definicion'              => 'Mide la participación de trabajadores en actividades de promoción de la salud.',
            'interpretacion'          => 'A mayor porcentaje, mayor compromiso con la salud. Valores bajos requieren estrategias de motivación.',
            'origen_datos'            => 'Registros de asistencia, programas de bienestar',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, RRHH',
        ],
    ],
    [
        'patron' => '%Restricciones%',
        'datos' => [
            'definicion'              => 'Mide el seguimiento a casos de salud con restricciones o recomendaciones médicas.',
            'interpretacion'          => 'Debe ser 100%. Todo caso con restricciones debe tener seguimiento y adaptación del puesto.',
            'origen_datos'            => 'Conceptos médicos, recomendaciones ocupacionales, registros de seguimiento',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, médico ocupacional, RRHH',
        ],
    ],
    [
        'patron' => '%Riesgo Psicosocial%',
        'datos' => [
            'definicion'              => 'Mide la efectividad de la capacitación en riesgo psicosocial.',
            'interpretacion'          => 'Valores ≥80% indican formación efectiva. Importante para cumplimiento de Resolución 2764/2022.',
            'origen_datos'            => 'Evaluaciones post-capacitación, batería de riesgo psicosocial',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, psicólogo ocupacional',
        ],
    ],
    // ── RESULTADO ──
    [
        'patron' => '%Objetivos%SG%SST%',
        'datos' => [
            'definicion'              => 'Mide el porcentaje de objetivos del SG-SST alcanzados durante el periodo evaluado.',
            'interpretacion'          => 'Un resultado del 100% indica cumplimiento total de los objetivos. Valores menores requieren ajuste en la planificación.',
            'origen_datos'            => 'Plan de objetivos y metas del SG-SST, informes de gestión',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía',
        ],
    ],
    [
        'patron' => '%Requisitos Legales%',
        'datos' => [
            'definicion'              => 'Mide la proporción de requisitos legales en SST identificados en la matriz legal que la organización cumple.',
            'interpretacion'          => 'Debe ser 100%. Valores menores indican incumplimientos normativos que pueden acarrear sanciones.',
            'origen_datos'            => 'Matriz de requisitos legales, evaluaciones de cumplimiento',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, asesor jurídico',
        ],
    ],
    [
        'patron' => '%Rehabilitaci_n%',
        'datos' => [
            'definicion'              => 'Mide la proporción de trabajadores reintegrados exitosamente al trabajo después de un programa de rehabilitación.',
            'interpretacion'          => 'A mayor porcentaje, mayor efectividad del programa de rehabilitación y reintegro laboral.',
            'origen_datos'            => 'Registros de rehabilitación, informes médicos, actas de reintegro',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, médico ocupacional, ARL',
        ],
    ],
    // ── 6 MÍNIMOS OBLIGATORIOS ──
    [
        'patron' => '%Frecuencia%Accidentes%Trabajo%',
        'datos' => [
            'definicion'              => 'Expresa el número de accidentes de trabajo ocurridos durante el último año por cada 240.000 horas hombre trabajadas.',
            'interpretacion'          => 'A menor valor, menor frecuencia de accidentalidad. Se debe comparar con el periodo anterior y con la media del sector económico.',
            'origen_datos'            => 'FURAT, registro de accidentes de trabajo, nómina (HHT)',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía, ARL, trabajadores',
        ],
    ],
    [
        'patron' => '%Severidad%Accidentes%Trabajo%',
        'datos' => [
            'definicion'              => 'Expresa el número de días perdidos y cargados por accidentes de trabajo durante el último año por cada 240.000 horas hombre trabajadas.',
            'interpretacion'          => 'A menor valor, menor severidad de los accidentes. Valores altos indican accidentes graves con muchos días de incapacidad.',
            'origen_datos'            => 'FURAT, incapacidades por AT, nómina (HHT)',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía, ARL, trabajadores',
        ],
    ],
    [
        'patron' => '%Accidentes%Trabajo%Mortal%',
        'datos' => [
            'definicion'              => 'Expresa la relación porcentual de accidentes de trabajo mortales sobre el total de accidentes ocurridos en el periodo.',
            'interpretacion'          => 'Debe ser 0%. Cualquier valor mayor a 0% indica una fatalidad que requiere investigación inmediata.',
            'origen_datos'            => 'FURAT, reportes ARL, investigaciones de accidentes mortales',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía, ARL, MinTrabajo',
        ],
    ],
    [
        'patron' => '%Prevalencia%Enfermedad%Laboral%',
        'datos' => [
            'definicion'              => 'Mide el número total de casos de enfermedad laboral (nuevos y existentes) por cada 100.000 trabajadores en el periodo.',
            'interpretacion'          => 'A menor valor, menor carga de enfermedad laboral. Se compara con estadísticas sectoriales de la ARL.',
            'origen_datos'            => 'Diagnósticos médicos ocupacionales, reportes EPS/ARL, historias clínicas ocupacionales',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, médico ocupacional, ARL',
        ],
    ],
    [
        'patron' => '%Incidencia%Enfermedad%Laboral%',
        'datos' => [
            'definicion'              => 'Mide el número de casos nuevos de enfermedad laboral por cada 100.000 trabajadores en el periodo.',
            'interpretacion'          => 'A menor valor, mejor control de los factores de riesgo. Un aumento indica falla en las medidas preventivas.',
            'origen_datos'            => 'Diagnósticos médicos ocupacionales, reportes EPS/ARL, primeros diagnósticos',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, médico ocupacional, ARL',
        ],
    ],
    [
        'patron' => '%Ausentismo%Causa%M_dica%',
        'datos' => [
            'definicion'              => 'Mide la proporción de días de ausencia por incapacidades médicas frente al total de días de trabajo programados.',
            'interpretacion'          => 'A menor porcentaje, menor ausentismo. Valores altos requieren análisis de causas y acciones correctivas.',
            'origen_datos'            => 'Registro de incapacidades médicas, nómina, RRHH',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, RRHH, ARL',
        ],
    ],
    [
        'patron' => '%Lesiones Incapacitantes%',
        'datos' => [
            'definicion'              => 'Relaciona la frecuencia y severidad de los accidentes de trabajo. Es el producto del IF × IS dividido entre 1000.',
            'interpretacion'          => 'A menor valor, menor impacto global de la accidentalidad. Permite comparar periodos y establecer tendencias.',
            'origen_datos'            => 'Índice de Frecuencia (IF), Índice de Severidad (IS), registros de AT',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía, ARL',
        ],
    ],
    [
        'patron' => '%Estandares Minimos%',
        'datos' => [
            'definicion'              => 'Mide el porcentaje de cumplimiento de los estándares mínimos de la Resolución 0312/2019.',
            'interpretacion'          => 'Debe ser ≥85% para calificación Aceptable. <60% es Crítico y requiere plan de mejora inmediato.',
            'origen_datos'            => 'Autoevaluación de estándares mínimos, informes ARL, acta de revisión por la dirección',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, ARL, MinTrabajo',
        ],
    ],
    [
        'patron' => '%Cobertura%Capacitaci_n%SST%',
        'datos' => [
            'definicion'              => 'Mide el porcentaje de trabajadores que recibieron al menos una capacitación en SST durante el periodo.',
            'interpretacion'          => 'Debe ser 100%. Todo trabajador debe recibir formación en SST según su cargo y exposición a riesgos.',
            'origen_datos'            => 'Registros de asistencia a capacitaciones, nómina',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía',
        ],
    ],
    [
        'patron' => '%Tasa%Incidencia%Enfermedad%',
        'datos' => [
            'definicion'              => 'Mide la tasa de aparición de nuevos casos de enfermedad laboral en el periodo.',
            'interpretacion'          => 'A menor tasa, mejor control de los factores de riesgo ocupacional.',
            'origen_datos'            => 'Diagnósticos médicos ocupacionales, reportes EPS/ARL',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, médico ocupacional, ARL',
        ],
    ],
    [
        'patron' => 'Cobertura de Capacitaci_n',
        'datos' => [
            'definicion'              => 'Mide el porcentaje de trabajadores que recibieron capacitación en SST respecto al total de la nómina.',
            'interpretacion'          => 'Debe ser 100%. Todo trabajador debe recibir formación en SST según su cargo y exposición a riesgos.',
            'origen_datos'            => 'Registros de asistencia a capacitaciones, nómina',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, COPASST/Vigía',
        ],
    ],
    [
        'patron' => '%Tasa%Ausentismo%M_dica%',
        'datos' => [
            'definicion'              => 'Mide la tasa de ausentismo laboral por causas médicas (enfermedad general y laboral).',
            'interpretacion'          => 'A menor tasa, menor impacto del ausentismo. Valores altos requieren análisis de causas raíz.',
            'origen_datos'            => 'Registro de incapacidades, nómina, RRHH',
            'cargo_responsable'       => 'Responsable del SG-SST',
            'cargos_conocer_resultado'=> 'Gerencia, Responsable SG-SST, RRHH, ARL',
        ],
    ],
];


// ═══════════════════════════════════════════════════
// Función principal
// ═══════════════════════════════════════════════════
function ejecutarRepoblacion(PDO $pdo, string $entorno, array $mapeoFuzzy): array
{
    $resultado = ['actualizados' => 0, 'ya_tenian_datos' => 0, 'sin_match' => 0];

    echo "=== [{$entorno}] Repoblación fuzzy de fichas técnicas ===\n\n";

    // Obtener todos los indicadores con definicion vacía
    $stmt = $pdo->query("
        SELECT id_indicador, nombre_indicador, id_cliente
        FROM tbl_indicadores_sst
        WHERE (definicion IS NULL OR definicion = '')
        ORDER BY id_cliente, id_indicador
    ");
    $sinDatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "  Indicadores sin datos de ficha técnica: " . count($sinDatos) . "\n\n";

    if (count($sinDatos) === 0) {
        echo "  ¡Todos los indicadores ya tienen datos!\n";
        return $resultado;
    }

    // Preparar UPDATE
    $stmtUpdate = $pdo->prepare("
        UPDATE tbl_indicadores_sst
        SET definicion = :definicion,
            interpretacion = :interpretacion,
            origen_datos = :origen_datos,
            cargo_responsable = :cargo_responsable,
            cargos_conocer_resultado = :cargos_conocer_resultado
        WHERE id_indicador = :id_indicador
    ");

    foreach ($sinDatos as $ind) {
        $matchFound = false;

        foreach ($mapeoFuzzy as $mapeo) {
            // Usar LIKE en PHP con fnmatch-style (convertir SQL LIKE a regex)
            $patron = $mapeo['patron'];
            // Convertir % a .* y _ a . para regex
            $regex = '/^' . str_replace(['%', '_'], ['.*', '.'], preg_quote($patron, '/')) . '$/iu';
            // Ajustar: el preg_quote escapó % y _ que ya convertimos, así que hacerlo al revés
            $regex = '/^' . str_replace(
                [preg_quote('%', '/'), preg_quote('_', '/')],
                ['.*', '.'],
                preg_quote($patron, '/')
            ) . '$/iu';

            if (preg_match($regex, $ind['nombre_indicador'])) {
                $stmtUpdate->execute([
                    ':definicion'              => $mapeo['datos']['definicion'],
                    ':interpretacion'          => $mapeo['datos']['interpretacion'],
                    ':origen_datos'            => $mapeo['datos']['origen_datos'],
                    ':cargo_responsable'       => $mapeo['datos']['cargo_responsable'],
                    ':cargos_conocer_resultado'=> $mapeo['datos']['cargos_conocer_resultado'],
                    ':id_indicador'            => $ind['id_indicador'],
                ]);
                $resultado['actualizados']++;
                echo "  [+] ID {$ind['id_indicador']} | {$ind['nombre_indicador']} → ACTUALIZADO\n";
                $matchFound = true;
                break;
            }
        }

        if (!$matchFound) {
            $resultado['sin_match']++;
            echo "  [-] ID {$ind['id_indicador']} | {$ind['nombre_indicador']} → SIN MATCH\n";
        }
    }

    echo "\n  Resumen: {$resultado['actualizados']} actualizados, {$resultado['sin_match']} sin match\n";
    return $resultado;
}


// ═══════════════════════════════════════════════════
// Ejecutar LOCAL
// ═══════════════════════════════════════════════════
echo "\n╔═══════════════════════════════════════╗\n";
echo "║  REPOBLACIÓN FUZZY - LOCAL            ║\n";
echo "╚═══════════════════════════════════════╝\n\n";

try {
    $pdoLocal = new PDO(
        'mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $resLocal = ejecutarRepoblacion($pdoLocal, 'LOCAL', $mapeoFuzzy);
} catch (PDOException $e) {
    echo "ERROR LOCAL: " . $e->getMessage() . "\n";
    exit(1);
}


// ═══════════════════════════════════════════════════
// Ejecutar PROD
// ═══════════════════════════════════════════════════
echo "\n╔═══════════════════════════════════════╗\n";
echo "║  REPOBLACIÓN FUZZY - PROD             ║\n";
echo "╚═══════════════════════════════════════╝\n\n";

try {
    $pdoProd = new PDO(
        'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
        'cycloid_userdb',
        'AVNS_iDypWizlpMRwHIORJGG',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $resProd = ejecutarRepoblacion($pdoProd, 'PROD', $mapeoFuzzy);
} catch (PDOException $e) {
    echo "ERROR PROD: " . $e->getMessage() . "\n";
}

echo "\n✓ Proceso completado.\n";
