<?php
/**
 * Script de repoblación: Fichas Técnicas para indicadores generados por Servicios IA
 *
 * Actualiza indicadores existentes en tbl_indicadores_sst que fueron creados por los 9 servicios
 * de generación IA y NO tienen los 5 campos de ficha técnica poblados.
 *
 * Se ejecuta UNA vez para corregir indicadores ya creados. Los nuevos indicadores ya
 * nacerán con los campos completos gracias a la actualización de los servicios.
 *
 * Ejecutar: php app/SQL/repoblar_fichas_servicios_ia.php [local|prod]
 */

$entorno = $argv[1] ?? 'local';

if ($entorno === 'prod') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $user = 'cycloid_userdb';
    $pass = 'AVNS_iDypWizlpMRwHIORJGG';
    $dbname = 'empresas_sst';
    echo "=== PRODUCCIÓN ===\n";
} else {
    $host = 'localhost';
    $port = 3306;
    $user = 'root';
    $pass = '';
    $dbname = 'empresas_sst';
    echo "=== LOCAL ===\n";
}

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage() . "\n");
}

/**
 * Mapeo completo: nombre_indicador (LIKE pattern) → 5 campos de ficha técnica
 * Cubre los indicadores de los 9 servicios:
 * 1. IndicadoresObjetivosService (10 indicadores)
 * 2. IndicadoresCapacitacionService (5 indicadores)
 * 3. IndicadoresPyPSaludService (7+4 indicadores)
 * 4. IndicadoresEstilosVidaService (7 indicadores)
 * 5. IndicadoresEvaluacionesMedicasService (7 indicadores)
 * 6. IndicadoresMantenimientoPeriodicoService (6 indicadores)
 * 7. IndicadoresPveBiomecanicoService (7 indicadores)
 * 8. IndicadoresPvePsicosocialService (7 indicadores)
 * 9. InduccionEtapasService (3 indicadores)
 */
$mapeo = [
    // === OBJETIVOS SG-SST (Servicio 1) ===
    '%Frecuencia de Accidente%' => [
        'definicion' => 'Mide la relacion entre el numero de accidentes de trabajo ocurridos y las horas hombre trabajadas durante un periodo, expresado por cada 240.000 HHT.',
        'interpretacion' => 'A menor valor, menor frecuencia de accidentalidad. Un IF=0 indica cero accidentes. Valores crecientes requieren investigacion y acciones correctivas inmediatas.',
        'origen_datos' => 'Registros FURAT, reportes de accidentes de trabajo, nomina (horas trabajadas)',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL'
    ],
    '%Severidad de Accidente%' => [
        'definicion' => 'Mide la gravedad de los accidentes de trabajo ocurridos, relacionando los dias de incapacidad generados con las horas hombre trabajadas.',
        'interpretacion' => 'A menor valor, menor severidad de los accidentes. Un IS=0 indica cero dias perdidos. Valores altos indican accidentes graves que requieren intervencion prioritaria.',
        'origen_datos' => 'Registros FURAT, incapacidades por AT, nomina (horas trabajadas)',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL'
    ],
    '%Incidencia de Enfermedad Laboral%' => [
        'definicion' => 'Mide la proporcion de nuevos casos de enfermedad calificada como laboral respecto al promedio de trabajadores en el periodo.',
        'interpretacion' => 'A menor valor, mejor gestion preventiva. Un valor de 0 indica que no se presentaron nuevos casos de enfermedad laboral en el periodo.',
        'origen_datos' => 'Calificaciones de origen EPS/ARL, registros de enfermedad laboral, nomina promedio',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL, medico ocupacional'
    ],
    '%Cumplimiento de Estandares Minimos%' => [
        'definicion' => 'Mide el porcentaje de estandares minimos de la Resolucion 0312/2019 que la empresa cumple segun su clasificacion de riesgo y numero de trabajadores.',
        'interpretacion' => 'El 100% indica cumplimiento total. Valores >=85% son aceptables, entre 60-85% requieren plan de mejora, <60% estado critico.',
        'origen_datos' => 'Autoevaluacion de estandares minimos Res. 0312/2019, plan de mejora',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL'
    ],
    '%Cobertura de Capacitaci_n%' => [
        'definicion' => 'Mide la proporcion de trabajadores que han recibido capacitacion en temas de SST respecto al total de la poblacion trabajadora.',
        'interpretacion' => 'El 100% indica que todos los trabajadores han sido capacitados. Valores menores requieren refuerzo en cobertura de formacion.',
        'origen_datos' => 'Registros de asistencia a capacitaciones, cronograma de capacitacion, nomina',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, trabajadores'
    ],
    '%Cumplimiento del Plan de Trabajo%' => [
        'definicion' => 'Mide el porcentaje de actividades ejecutadas del Plan de Trabajo Anual del SG-SST frente a las actividades programadas para el periodo.',
        'interpretacion' => 'Un resultado >=90% indica buen cumplimiento. Valores menores requieren reprogramacion y analisis de causas de incumplimiento.',
        'origen_datos' => 'Plan de Trabajo Anual, cronograma de actividades, actas de ejecucion',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia'
    ],
    '%Peligros Intervenidos%' => [
        'definicion' => 'Mide la proporcion de peligros prioritarios identificados en la Matriz IPVR que cuentan con medidas de control implementadas.',
        'interpretacion' => 'A mayor porcentaje, mejor gestion de peligros. Un 80% o superior indica buena intervencion. Priorizar peligros con nivel de riesgo alto e inaceptable.',
        'origen_datos' => 'Matriz de identificacion de peligros y valoracion de riesgos (IPVR), registros de controles',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, trabajadores'
    ],
    '%Simulacros de Emergencia%' => [
        'definicion' => 'Mide el porcentaje de trabajadores que participan activamente en los simulacros de emergencia programados.',
        'interpretacion' => 'Un 90% o mas indica participacion adecuada. Valores menores requieren refuerzo en convocatoria y sensibilizacion sobre preparacion ante emergencias.',
        'origen_datos' => 'Registros de asistencia a simulacros, plan de emergencias, informe post-simulacro',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, brigada de emergencias'
    ],
    '%Lesiones Incapacitantes%' => [
        'definicion' => 'Indicador combinado que relaciona la frecuencia y la severidad de los accidentes de trabajo. Resulta de multiplicar IF por IS y dividir entre 1000.',
        'interpretacion' => 'A menor valor, menor impacto de la accidentalidad. Un ILI=0 indica cero accidentes o cero dias perdidos. Valores crecientes indican deterioro en seguridad.',
        'origen_datos' => 'Calculado a partir del Indice de Frecuencia (IF) y el Indice de Severidad (IS)',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL'
    ],
    '%Investigaci_n de Incidentes%' => [
        'definicion' => 'Mide el porcentaje de incidentes y accidentes de trabajo que fueron investigados dentro de los 15 dias calendario siguientes a su ocurrencia.',
        'interpretacion' => 'El 100% indica que todos los eventos fueron investigados oportunamente. Es obligatorio investigar todos los accidentes graves y mortales (Res. 1401/2007).',
        'origen_datos' => 'Informes de investigacion de accidentes, FURAT, registros de incidentes',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL, trabajadores'
    ],

    // === CAPACITACIÓN (Servicio 2) ===
    '%Cronograma de Capacitaci_n%' => [
        'definicion' => 'Mide el porcentaje de capacitaciones ejecutadas del cronograma anual de formacion en SST frente a las programadas para el periodo.',
        'interpretacion' => 'El 100% indica cumplimiento total del cronograma. Valores <80% requieren reprogramacion de actividades y analisis de causas de incumplimiento.',
        'origen_datos' => 'Cronograma anual de capacitacion, registros de asistencia, actas de capacitacion',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, trabajadores'
    ],
    '%Eficacia de Capacitacion%' => [
        'definicion' => 'Mide el nivel de aprendizaje y aprovechamiento de los trabajadores en las capacitaciones, evaluado mediante pruebas de conocimiento post-capacitacion.',
        'interpretacion' => 'Valores >=80% indican buena eficacia de la formacion. Valores menores sugieren necesidad de ajustar metodologia, contenidos o duracion de las capacitaciones.',
        'origen_datos' => 'Evaluaciones post-capacitacion, encuestas de satisfaccion, pruebas de conocimiento',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia'
    ],
    '%Oportunidad%Ejecuci_n%Capacitacion%' => [
        'definicion' => 'Mide el porcentaje de capacitaciones que se ejecutaron en la fecha originalmente programada en el cronograma, sin reprogramaciones.',
        'interpretacion' => 'Un 90% o mas indica buena planificacion y cumplimiento de fechas. Valores menores requieren revision de la programacion y coordinacion con areas.',
        'origen_datos' => 'Cronograma de capacitacion (fechas programadas vs ejecutadas), registros de reprogramaciones',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia'
    ],
    '%Horas de Capacitaci_n por Trabajador%' => [
        'definicion' => 'Mide el promedio de horas de formacion en SST que recibio cada trabajador durante el ano, incluyendo inducciones, reinducciones y capacitaciones especificas.',
        'interpretacion' => 'La meta de 20 horas/trabajador/ano es el estandar recomendado. Valores menores indican necesidad de intensificar la formacion en SST.',
        'origen_datos' => 'Registros de asistencia con duracion, cronograma ejecutado, nomina',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia'
    ],

    // === PyP SALUD (Servicio 3) ===
    '%Examenes Medicos Ocupacionales%' => [
        'definicion' => 'Mide la proporcion de trabajadores que cuentan con evaluaciones medicas ocupacionales vigentes (ingreso, periodicas, egreso) segun Res. 2346/2007.',
        'interpretacion' => 'El 100% indica cobertura total. Es obligatorio para todos los trabajadores. Valores menores requieren programacion inmediata de examenes pendientes.',
        'origen_datos' => 'Profesiograma, registro de evaluaciones medicas ocupacionales, IPS contratada',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, medico ocupacional'
    ],
    '%Actividades de PyP Salud%' => [
        'definicion' => 'Mide el porcentaje de actividades de promocion y prevencion en salud ejecutadas frente a las programadas en el plan anual.',
        'interpretacion' => 'Un resultado >=90% indica buen cumplimiento del programa. Valores menores requieren revision de la programacion y recursos asignados.',
        'origen_datos' => 'Plan de trabajo anual (actividades PyP), registros de ejecucion, actas',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, medico ocupacional'
    ],
    '%Prevalencia de Enfermedad Laboral%' => [
        'definicion' => 'Mide la proporcion total de casos de enfermedad laboral (nuevos y existentes) en la poblacion trabajadora durante el periodo.',
        'interpretacion' => 'A menor valor, menor carga de enfermedad laboral. Un valor de 0 indica ausencia de casos. Valores altos requieren fortalecimiento de programas de prevencion.',
        'origen_datos' => 'Registro acumulado de enfermedades laborales calificadas, nomina promedio, ARL',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL, medico ocupacional'
    ],
    '%Ausentismo por Causa M_dica%' => [
        'definicion' => 'Mide el porcentaje de dias laborales perdidos por incapacidades medicas (enfermedad comun, laboral y accidentes) respecto al total de dias programados.',
        'interpretacion' => 'Valores <=3% son aceptables. Valores superiores requieren analisis de causas principales de ausentismo y medidas preventivas.',
        'origen_datos' => 'Registros de incapacidades, nomina (dias programados), EPS, ARL',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, COPASST/Vigia'
    ],
    '%Promoci_n de Salud%' => [
        'definicion' => 'Mide el porcentaje de trabajadores que participan en las campanas y actividades de promocion de salud y prevencion de enfermedad.',
        'interpretacion' => 'Un 80% o mas indica buena participacion. Valores menores requieren revision de estrategias de convocatoria y horarios de actividades.',
        'origen_datos' => 'Registros de asistencia a actividades PyP, listados de convocatoria, nomina',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, trabajadores'
    ],
    '%Casos de Salud con Restricciones%' => [
        'definicion' => 'Mide el porcentaje de trabajadores con restricciones o recomendaciones medico-laborales que reciben seguimiento periodico por parte de la empresa.',
        'interpretacion' => 'El 100% indica que todos los casos con restricciones tienen seguimiento activo. Es obligacion legal dar cumplimiento a las recomendaciones medicas.',
        'origen_datos' => 'Certificados de aptitud laboral, registro de seguimiento a restricciones, medico ocupacional',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional, jefes inmediatos'
    ],

    // === ESTILOS DE VIDA (Servicio 4) ===
    '%Estilos de Vida Saludable%' => [
        'definicion' => 'Mide el porcentaje de actividades ejecutadas del programa de estilos de vida saludable frente a las programadas.',
        'interpretacion' => 'Un resultado >=90% indica buen cumplimiento. Valores menores requieren reprogramacion de actividades y revision de recursos asignados al programa.',
        'origen_datos' => 'Plan de trabajo anual (actividades EVS), registros de ejecucion, actas',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, trabajadores'
    ],
    '%Campa_as de Prevenci_n%' => [
        'definicion' => 'Mide la proporcion de trabajadores que participan en las campanas de prevencion de consumo de tabaco, alcohol y sustancias psicoactivas.',
        'interpretacion' => 'Un 80% o mas indica buena participacion. Valores menores requieren revision de estrategias de convocatoria y metodologia de las campanas.',
        'origen_datos' => 'Registros de asistencia a campanas, listados de convocatoria, nomina',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, trabajadores'
    ],
    '%Fumadores Activos%' => [
        'definicion' => 'Mide la variacion porcentual de fumadores activos entre un periodo y otro, con el objetivo de evidenciar reduccion progresiva del habito de fumar.',
        'interpretacion' => 'Valores negativos indican reduccion (objetivo deseado). La meta de -5% significa reducir al menos 5% los fumadores. Valores positivos indican aumento del habito.',
        'origen_datos' => 'Encuestas de habitos de salud, diagnostico de condiciones de salud, perfil sociodemografico',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, medico ocupacional'
    ],
    '%Enfermedades Cr_nicas No Transmisibles%' => [
        'definicion' => 'Mide el porcentaje de dias laborales perdidos por enfermedades cronicas no transmisibles (diabetes, hipertension, obesidad, etc.) respecto al total programado.',
        'interpretacion' => 'Valores <=3% son aceptables. Valores superiores requieren fortalecimiento de campanas de estilos de vida saludable y seguimiento medico.',
        'origen_datos' => 'Registros de incapacidades (diagnostico ECNT), nomina, EPS',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, medico ocupacional'
    ],
    '%Consumo de SPA%' => [
        'definicion' => 'Registra la cantidad de trabajadores identificados con consumo de sustancias psicoactivas (SPA) que fueron canalizados a su EPS para atencion especializada.',
        'interpretacion' => 'Lo ideal es 0 casos. La deteccion y canalizacion oportuna es obligatoria. Un aumento de casos puede indicar mejor deteccion o mayor prevalencia.',
        'origen_datos' => 'Registros de canalizacion a EPS, reportes de deteccion, seguimiento de casos',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional'
    ],

    // === EVALUACIONES MÉDICAS (Servicio 5) ===
    '%Evaluaciones M_dicas%Ingreso%' => [
        'definicion' => 'Mide la proporcion de trabajadores nuevos que recibieron evaluacion medica ocupacional de ingreso antes de iniciar sus labores, segun Res. 2346/2007.',
        'interpretacion' => 'El 100% indica cumplimiento total. Es obligatorio evaluar a todo trabajador antes del inicio de labores. Valores menores generan riesgo legal.',
        'origen_datos' => 'Registro de ingresos (Recursos Humanos), certificados de aptitud de ingreso, IPS contratada',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, medico ocupacional'
    ],
    '%Evaluaciones M_dicas Peri_dicas%Profesiograma%' => [
        'definicion' => 'Mide el porcentaje de evaluaciones medicas periodicas realizadas segun la frecuencia definida en el profesiograma por cargo y nivel de exposicion a peligros.',
        'interpretacion' => 'Un 90% o mas indica buen cumplimiento. La frecuencia depende del tipo de peligro: anual para peligros altos, bianual para medios.',
        'origen_datos' => 'Profesiograma, cronograma de evaluaciones periodicas, certificados de aptitud, IPS',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional'
    ],
    '%Comunicaci_n Oportuna%Resultados%' => [
        'definicion' => 'Mide el porcentaje de certificados de aptitud entregados al trabajador dentro de los 5 dias habiles siguientes a la evaluacion, segun Res. 2346/2007 Art. 14.',
        'interpretacion' => 'Valores >=95% indican buena oportunidad. Es un derecho del trabajador recibir copia del certificado. El incumplimiento genera sanciones.',
        'origen_datos' => 'Registros de entrega de certificados (firma del trabajador), fechas de evaluacion vs entrega',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, medico ocupacional'
    ],
    '%Restricciones%Recomendaciones M_dicas%' => [
        'definicion' => 'Mide el porcentaje de restricciones y recomendaciones medico-laborales que fueron efectivamente implementadas por la empresa en los puestos de trabajo.',
        'interpretacion' => 'El 100% es obligatorio. El incumplimiento de restricciones medicas genera responsabilidad legal directa del empleador ante accidentes o agravamiento.',
        'origen_datos' => 'Certificados de aptitud con restricciones, registro de implementacion, seguimiento de jefes inmediatos',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional, jefes inmediatos'
    ],
    '%Evaluaciones M_dicas de Egreso%' => [
        'definicion' => 'Mide la proporcion de trabajadores retirados que recibieron evaluacion medica de egreso dentro de los 5 dias siguientes a la terminacion del vinculo laboral.',
        'interpretacion' => 'Un 90% o mas indica buena gestion. La evaluacion de egreso es obligatoria y protege a la empresa ante futuras reclamaciones por enfermedad laboral.',
        'origen_datos' => 'Registro de retiros (Recursos Humanos), certificados de egreso, IPS contratada',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, medico ocupacional'
    ],
    '%Aptitud Laboral Sin Restricci_n%' => [
        'definicion' => 'Mide la proporcion de trabajadores que tras la evaluacion medica ocupacional resultan aptos para desempenar sus funciones sin ninguna restriccion medica.',
        'interpretacion' => 'Valores >=80% indican buen estado de salud general. El 20% restante puede tener restricciones temporales o permanentes que requieren seguimiento.',
        'origen_datos' => 'Certificados de aptitud laboral, diagnostico de condiciones de salud, IPS',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional, Recursos Humanos'
    ],

    // === MANTENIMIENTO (Servicio 6) ===
    '%Mantenimiento Preventivo%' => [
        'definicion' => 'Mide el porcentaje de mantenimientos preventivos ejecutados frente a los programados en el cronograma de mantenimiento de equipos, maquinas e instalaciones.',
        'interpretacion' => 'Un resultado >=90% indica buen cumplimiento. Valores menores incrementan el riesgo de fallas y accidentes por condiciones inseguras en equipos.',
        'origen_datos' => 'Cronograma de mantenimiento, ordenes de trabajo, hojas de vida de equipos',
        'cargo_responsable' => 'Responsable del SG-SST / Jefe de Mantenimiento',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, jefe de mantenimiento'
    ],
    '%Ficha T_cnica Actualizada%' => [
        'definicion' => 'Mide la proporcion de equipos, maquinas y herramientas del inventario que cuentan con ficha tecnica y hoja de vida actualizadas.',
        'interpretacion' => 'El 100% indica documentacion completa. La ficha tecnica es requisito para programar mantenimiento adecuado y garantizar condiciones seguras.',
        'origen_datos' => 'Inventario de equipos, fichas tecnicas, hojas de vida de maquinaria',
        'cargo_responsable' => 'Responsable del SG-SST / Jefe de Mantenimiento',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, jefe de mantenimiento'
    ],
    '%Fallas por Mantenimiento%' => [
        'definicion' => 'Registra la cantidad de fallas, averias o paradas no programadas atribuibles a deficiencias en el mantenimiento preventivo de equipos e instalaciones.',
        'interpretacion' => 'A menor valor, mejor programa de mantenimiento. La meta de <=2 fallas/trimestre es aceptable. Valores superiores requieren revision del cronograma de mantenimiento.',
        'origen_datos' => 'Reportes de fallas, ordenes de trabajo correctivo, registro de paradas no programadas',
        'cargo_responsable' => 'Responsable del SG-SST / Jefe de Mantenimiento',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, jefe de mantenimiento, COPASST/Vigia'
    ],
    '%Disponibilidad Operativa%' => [
        'definicion' => 'Mide el porcentaje de tiempo que los equipos criticos estuvieron disponibles para operacion frente al tiempo programado, descontando paradas por fallas.',
        'interpretacion' => 'Valores >=95% indican alta confiabilidad. Valores menores generan riesgo operacional y pueden derivar en condiciones inseguras de trabajo.',
        'origen_datos' => 'Registros de operacion de equipos, reportes de paradas, ordenes de mantenimiento',
        'cargo_responsable' => 'Jefe de Mantenimiento / Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, jefe de mantenimiento, jefe de operaciones'
    ],
    '%Inspecciones de Seguridad a Instalaciones%' => [
        'definicion' => 'Mide el porcentaje de inspecciones de seguridad ejecutadas a instalaciones fisicas frente a las programadas en el cronograma de inspecciones.',
        'interpretacion' => 'El 100% indica cumplimiento total. Las inspecciones detectan condiciones inseguras antes de que generen accidentes. Es requisito de Res. 0312/2019.',
        'origen_datos' => 'Cronograma de inspecciones, formatos de inspeccion diligenciados, registros fotograficos',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, jefe de mantenimiento'
    ],
    '%Accidentes Relacionados con Equipos%' => [
        'definicion' => 'Mide la proporcion de accidentes de trabajo cuya causa raiz fue atribuida a fallas en equipos, maquinas, herramientas o instalaciones fisicas.',
        'interpretacion' => 'El 0% indica que ningun accidente fue causado por fallas en equipos. Valores superiores requieren revision inmediata del programa de mantenimiento.',
        'origen_datos' => 'Investigaciones de accidentes de trabajo, FURAT, analisis de causalidad',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL, jefe de mantenimiento'
    ],

    // === PVE BIOMECÁNICO (Servicio 7) ===
    '%PVE Biomecanico%' => [
        'definicion' => 'Mide el porcentaje de actividades ejecutadas del Programa de Vigilancia Epidemiologica de riesgo biomecanico frente a las programadas.',
        'interpretacion' => 'Un resultado >=90% indica buen cumplimiento. Valores menores requieren revision de recursos y prioridades del PVE biomecanico.',
        'origen_datos' => 'Cronograma PVE biomecanico, registros de actividades ejecutadas, actas',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, fisioterapeuta'
    ],
    '%Evaluaciones Ergon_micas%' => [
        'definicion' => 'Mide la proporcion de puestos de trabajo con riesgo biomecanico identificado en la Matriz IPVR que han recibido evaluacion ergonomica.',
        'interpretacion' => 'Un 80% o mas indica buena cobertura. Priorizar puestos con mayor nivel de riesgo. Se recomienda usar metodologias RULA, REBA u OWAS.',
        'origen_datos' => 'Matriz IPVR (peligro biomecanico), informes de evaluacion ergonomica, fotografias de puestos',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, fisioterapeuta, jefes de area'
    ],
    '%Sintomatolog_a Osteomuscular%' => [
        'definicion' => 'Mide la proporcion de trabajadores que reportan sintomatologia osteomuscular (dolor, molestia, adormecimiento) en encuestas de morbilidad sentida.',
        'interpretacion' => 'Valores <=20% son aceptables. Valores superiores indican alta carga de sintomatologia y requieren intervencion ergonomica prioritaria.',
        'origen_datos' => 'Encuesta de morbilidad sentida (Cuestionario Nordico), evaluaciones medicas periodicas',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional, fisioterapeuta'
    ],
    '%Desordenes Musculoesquel_ticos%' => [
        'definicion' => 'Mide la proporcion de nuevos casos diagnosticados de desordenes musculoesqueleticos en trabajadores expuestos a riesgo biomecanico.',
        'interpretacion' => 'A menor valor, mejor gestion preventiva. Valores <=5% son aceptables. Valores superiores requieren revision de controles ergonomicos y del PVE.',
        'origen_datos' => 'Diagnosticos medicos (DME), evaluaciones medicas periodicas, calificaciones EPS/ARL',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional, ARL'
    ],
    '%Pausas Activas%' => [
        'definicion' => 'Mide la proporcion de trabajadores que participan de manera regular (al menos 3 veces/semana) en el programa de pausas activas de la empresa.',
        'interpretacion' => 'Un 80% o mas indica buena participacion. Las pausas activas reducen fatiga muscular y previenen DME. Valores menores requieren sensibilizacion.',
        'origen_datos' => 'Registros de participacion en pausas activas, encuestas de habitos, observacion directa',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, trabajadores'
    ],
    '%Intervenciones Ergon_micas%' => [
        'definicion' => 'Mide la proporcion de puestos de trabajo intervenidos ergonomicamente donde no se presentaron nuevas quejas osteomusculares en los siguientes 6 meses.',
        'interpretacion' => 'Valores >=70% indican buena efectividad. Valores menores sugieren que las intervenciones requieren ajustes o complementos adicionales.',
        'origen_datos' => 'Registro de intervenciones ergonomicas, seguimiento de quejas, encuestas post-intervencion',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, fisioterapeuta, medico ocupacional'
    ],
    '%Ausentismo%Osteomuscular%' => [
        'definicion' => 'Mide el porcentaje de dias laborales perdidos por incapacidades relacionadas con desordenes musculoesqueleticos respecto al total programado.',
        'interpretacion' => 'Valores <=3% son aceptables. Valores superiores indican alto impacto de DME en la productividad y requieren fortalecimiento del PVE biomecanico.',
        'origen_datos' => 'Registros de incapacidades (diagnostico DME), nomina, EPS/ARL',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, medico ocupacional'
    ],

    // === PVE PSICOSOCIAL (Servicio 8) ===
    '%PVE Psicosocial%' => [
        'definicion' => 'Mide el porcentaje de actividades ejecutadas del Programa de Vigilancia Epidemiologica de riesgo psicosocial frente a las programadas en el cronograma.',
        'interpretacion' => 'Un resultado >=90% indica buen cumplimiento. Valores menores requieren revision de recursos y prioridades del PVE psicosocial.',
        'origen_datos' => 'Cronograma PVE psicosocial, registros de actividades ejecutadas, actas, informes',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, psicologo especialista'
    ],
    '%Bater_a de Riesgo Psicosocial%' => [
        'definicion' => 'Mide la proporcion de trabajadores a quienes se les aplico la Bateria de Instrumentos de Evaluacion de Riesgo Psicosocial segun Res. 2764/2022.',
        'interpretacion' => 'Un 90% o mas indica buena cobertura. Es obligatorio aplicar la bateria cada 2 anos (riesgo alto) o 3 anos (bajo/medio). Solo puede aplicarla un psicologo especialista.',
        'origen_datos' => 'Informe de bateria de riesgo psicosocial, listado de trabajadores evaluados, psicologo especialista',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, psicologo especialista, Comite Convivencia'
    ],
    '%Riesgo Alto o Muy Alto%' => [
        'definicion' => 'Mide la proporcion de trabajadores clasificados en nivel de riesgo psicosocial alto o muy alto segun los resultados de la bateria de riesgo psicosocial.',
        'interpretacion' => 'Valores <=15% son aceptables. Valores superiores requieren intervencion prioritaria con programa especifico. Nivel muy alto exige intervencion inmediata (Res. 2764/2022).',
        'origen_datos' => 'Resultados de bateria de riesgo psicosocial, informe del psicologo especialista',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, psicologo especialista, ARL'
    ],
    '%Talleres de Intervenci_n%' => [
        'definicion' => 'Mide la proporcion de trabajadores que participan en talleres y actividades de intervencion psicosocial (manejo del estres, comunicacion, liderazgo, etc.).',
        'interpretacion' => 'Un 80% o mas indica buena participacion. Priorizar la participacion de trabajadores en nivel de riesgo alto y muy alto.',
        'origen_datos' => 'Registros de asistencia a talleres, listados de convocatoria, informes de actividades',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, psicologo especialista, COPASST/Vigia'
    ],
    '%Ausentismo por Estr_s Laboral%' => [
        'definicion' => 'Mide el porcentaje de dias laborales perdidos por incapacidades relacionadas con estres laboral, ansiedad, depresion y otros trastornos psicosociales.',
        'interpretacion' => 'Valores <=2% son aceptables. Valores superiores indican alto impacto del riesgo psicosocial y requieren revision de condiciones laborales.',
        'origen_datos' => 'Registros de incapacidades (diagnostico CIE-10 relacionado con estres), nomina, EPS',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, psicologo especialista'
    ],
    '%Intervenciones Psicosociales%' => [
        'definicion' => 'Mide la proporcion de trabajadores intervenidos que lograron reducir su nivel de riesgo psicosocial en la siguiente aplicacion de la bateria.',
        'interpretacion' => 'Valores >=60% indican buena efectividad del programa de intervencion. Se compara nivel de riesgo entre dos aplicaciones consecutivas de la bateria.',
        'origen_datos' => 'Comparativo de baterias (aplicacion anterior vs actual), informes del psicologo especialista',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, psicologo especialista, ARL'
    ],
    '%Acoso Laboral%Comit_%' => [
        'definicion' => 'Registra la cantidad de quejas formales por presunto acoso laboral recibidas por el Comite de Convivencia Laboral segun Ley 1010/2006.',
        'interpretacion' => 'Lo ideal es 0 quejas. Un aumento puede indicar deterioro del clima laboral o mayor confianza en los canales de denuncia. Toda queja debe tramitarse en maximo 10 dias.',
        'origen_datos' => 'Actas del Comite de Convivencia Laboral, registro de quejas, seguimiento de casos',
        'cargo_responsable' => 'Presidente del Comite de Convivencia Laboral',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Comite de Convivencia Laboral'
    ],

    // === INDUCCIÓN (Servicio 9) ===
    '%Cobertura de Inducci_n%' => [
        'definicion' => 'Mide la proporcion de trabajadores nuevos que completaron todas las etapas del programa de induccion antes de iniciar sus funciones.',
        'interpretacion' => 'El 100% indica que todos los trabajadores nuevos recibieron induccion completa. Es obligatorio segun Art. 2.2.4.6.11 D.1072/2015.',
        'origen_datos' => 'Registros de induccion, formato de asistencia firmado, evaluacion post-induccion',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, COPASST/Vigia'
    ],
    '%Cumplimiento del Programa de Inducci_n%' => [
        'definicion' => 'Mide el porcentaje de temas del programa de induccion y reinduccion que fueron efectivamente ejecutados respecto al total programado.',
        'interpretacion' => 'El 100% indica ejecucion completa del programa. Valores menores requieren reprogramacion de temas pendientes antes de que el trabajador inicie labores.',
        'origen_datos' => 'Programa de induccion aprobado, registros de ejecucion por etapa, evaluaciones',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, COPASST/Vigia'
    ],
    '%Oportunidad de Inducci_n%' => [
        'definicion' => 'Mide el porcentaje de inducciones que se realizaron el primer dia de vinculacion del trabajador, antes de que inicie sus funciones.',
        'interpretacion' => 'Un 90% o mas indica buena oportunidad. La induccion debe realizarse antes del inicio de labores para garantizar conocimiento de peligros y controles.',
        'origen_datos' => 'Registros de induccion (fecha vs fecha de ingreso), nomina (fecha de vinculacion)',
        'cargo_responsable' => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos'
    ],
];

// Ejecutar actualizaciones
$totalActualizados = 0;
$stmt = $pdo->prepare("
    UPDATE tbl_indicadores_sst
    SET definicion = :definicion,
        interpretacion = :interpretacion,
        origen_datos = :origen_datos,
        cargo_responsable = :cargo_responsable,
        cargos_conocer_resultado = :cargos_conocer_resultado
    WHERE nombre_indicador LIKE :patron
      AND (definicion IS NULL OR definicion = '')
      AND activo = 1
");

foreach ($mapeo as $patron => $campos) {
    $stmt->execute([
        ':patron' => $patron,
        ':definicion' => $campos['definicion'],
        ':interpretacion' => $campos['interpretacion'],
        ':origen_datos' => $campos['origen_datos'],
        ':cargo_responsable' => $campos['cargo_responsable'],
        ':cargos_conocer_resultado' => $campos['cargos_conocer_resultado'],
    ]);
    $afectados = $stmt->rowCount();
    if ($afectados > 0) {
        echo "  ✓ '{$patron}' → {$afectados} indicador(es) actualizado(s)\n";
        $totalActualizados += $afectados;
    }
}

echo "\n=== RESUMEN ===\n";
echo "Total indicadores actualizados: {$totalActualizados}\n";

// Verificar cuántos quedan sin ficha
$stmtPendientes = $pdo->query("
    SELECT COUNT(*) as total FROM tbl_indicadores_sst
    WHERE activo = 1
    AND (definicion IS NULL OR definicion = '')
");
$pendientes = $stmtPendientes->fetch(PDO::FETCH_ASSOC)['total'];
echo "Indicadores activos sin ficha técnica: {$pendientes}\n";

echo "\nCompletado.\n";
