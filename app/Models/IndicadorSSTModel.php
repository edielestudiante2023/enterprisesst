<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gestionar indicadores del SG-SST
 * Resolución 0312/2019
 */
class IndicadorSSTModel extends Model
{
    protected $table = 'tbl_indicadores_sst';
    protected $primaryKey = 'id_indicador';
    protected $allowedFields = [
        'id_cliente', 'id_actividad_pta', 'nombre_indicador',
        'definicion', 'interpretacion', 'origen_datos', 'cargo_responsable', 'cargos_conocer_resultado',
        'tipo_indicador',
        'categoria', 'formula', 'meta', 'unidad_medida', 'periodicidad', 'numeral_resolucion',
        'phva', 'valor_numerador', 'valor_denominador', 'valor_resultado',
        'fecha_medicion', 'cumple_meta', 'observaciones', 'acciones_mejora',
        'analisis_datos', 'requiere_plan_accion', 'numero_accion',
        'es_minimo_obligatorio', 'peso_ponderacion',
        'activo', 'created_by', 'updated_by'
    ];

    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Tipos de indicador según Res. 0312/2019
     */
    public const TIPOS_INDICADOR = [
        'estructura' => 'Indicador de Estructura',
        'proceso' => 'Indicador de Proceso',
        'resultado' => 'Indicador de Resultado'
    ];

    /**
     * Periodicidades disponibles
     */
    public const PERIODICIDADES = [
        'mensual' => 'Mensual',
        'trimestral' => 'Trimestral',
        'semestral' => 'Semestral',
        'anual' => 'Anual'
    ];

    /**
     * Fases PHVA
     */
    public const FASES_PHVA = [
        'planear' => 'PLANEAR',
        'hacer' => 'HACER',
        'verificar' => 'VERIFICAR',
        'actuar' => 'ACTUAR'
    ];

    /**
     * Categorías de indicadores del SG-SST
     */
    public const CATEGORIAS = [
        'capacitacion' => [
            'nombre' => 'Capacitación',
            'icono' => 'bi-mortarboard',
            'color' => 'primary',
            'descripcion' => 'Indicadores del programa de capacitación y formación'
        ],
        'accidentalidad' => [
            'nombre' => 'Accidentalidad',
            'icono' => 'bi-bandaid',
            'color' => 'danger',
            'descripcion' => 'Índices de frecuencia, severidad y accidentalidad'
        ],
        'ausentismo' => [
            'nombre' => 'Ausentismo',
            'icono' => 'bi-calendar-x',
            'color' => 'warning',
            'descripcion' => 'Indicadores de ausentismo laboral'
        ],
        'pta' => [
            'nombre' => 'Plan de Trabajo Anual',
            'icono' => 'bi-list-check',
            'color' => 'success',
            'descripcion' => 'Cumplimiento del Plan de Trabajo Anual'
        ],
        'inspecciones' => [
            'nombre' => 'Inspecciones',
            'icono' => 'bi-search',
            'color' => 'info',
            'descripcion' => 'Cumplimiento del programa de inspecciones'
        ],
        'emergencias' => [
            'nombre' => 'Emergencias',
            'icono' => 'bi-exclamation-triangle',
            'color' => 'orange',
            'descripcion' => 'Indicadores del plan de emergencias y simulacros'
        ],
        'vigilancia' => [
            'nombre' => 'Vigilancia Epidemiológica',
            'icono' => 'bi-heart-pulse',
            'color' => 'purple',
            'descripcion' => 'Programas de vigilancia epidemiológica'
        ],
        'riesgos' => [
            'nombre' => 'Gestión de Riesgos',
            'icono' => 'bi-shield-exclamation',
            'color' => 'secondary',
            'descripcion' => 'Gestión de peligros y riesgos'
        ],
        'pyp_salud' => [
            'nombre' => 'Promoción y Prevención en Salud',
            'icono' => 'bi-heart-pulse',
            'color' => 'danger',
            'descripcion' => 'Indicadores del programa de promoción y prevención en salud (3.1.2)'
        ],
        'objetivos_sgsst' => [
            'nombre' => 'Objetivos del SG-SST',
            'icono' => 'bi-bullseye',
            'color' => 'success',
            'descripcion' => 'Indicadores de medición de objetivos (Estándar 2.2.1)'
        ],
        'induccion' => [
            'nombre' => 'Inducción y Reinducción',
            'icono' => 'bi-person-badge',
            'color' => 'info',
            'descripcion' => 'Indicadores del programa de inducción y reinducción'
        ],
        'estilos_vida_saludable' => [
            'nombre' => 'Estilos de Vida Saludable',
            'icono' => 'bi-heart-pulse',
            'color' => 'success',
            'descripcion' => 'Indicadores del programa de estilos de vida saludable y controles de tabaquismo, alcoholismo y farmacodependencia (Estándar 3.1.7)'
        ],
        'evaluaciones_medicas_ocupacionales' => [
            'nombre' => 'Evaluaciones Médicas Ocupacionales',
            'icono' => 'bi-clipboard2-pulse',
            'color' => 'teal',
            'descripcion' => 'Indicadores de evaluaciones médicas ocupacionales: cobertura, frecuencia según peligros, comunicación de resultados (Estándar 3.1.4)'
        ],
        'pve_biomecanico' => [
            'nombre' => 'PVE Riesgo Biomecánico',
            'icono' => 'bi-body-text',
            'color' => 'warning',
            'descripcion' => 'Indicadores del PVE de riesgo biomecánico: DME, ergonomía, pausas activas (Estándar 4.2.3)'
        ],
        'pve_psicosocial' => [
            'nombre' => 'PVE Riesgo Psicosocial',
            'icono' => 'bi-brain',
            'color' => 'purple',
            'descripcion' => 'Indicadores del PVE de riesgo psicosocial: batería, estrés, clima laboral (Estándar 4.2.3)'
        ],
        'mantenimiento_periodico' => [
            'nombre' => 'Mantenimiento Periódico',
            'icono' => 'bi-tools',
            'color' => 'primary',
            'descripcion' => 'Indicadores de mantenimiento periódico de instalaciones, equipos, máquinas y herramientas (Estándar 4.2.5)'
        ],
        'otro' => [
            'nombre' => 'Otros',
            'icono' => 'bi-three-dots',
            'color' => 'dark',
            'descripcion' => 'Otros indicadores del SG-SST'
        ]
    ];

    /**
     * 18 Indicadores Legales Obligatorios - Decreto 1072/2015 + Resolución 0312/2019
     * Arts. 2.2.4.6.19 (Estructura), 2.2.4.6.20 (Proceso), 2.2.4.6.21 (Resultado)
     * Art. 30 Res. 0312: 6 mínimos obligatorios (IF, IS, PATM, PEL, IEL, ACM)
     *
     * Se siembran automáticamente al crear un cliente. Son transversales a todos.
     * 'keywords' se usa para detectar duplicados existentes.
     */
    public const INDICADORES_LEGALES = [
        // ═══════════════════════════════════════════════════
        // ESTRUCTURA (Art. 2.2.4.6.19) — PLANEAR
        // ═══════════════════════════════════════════════════
        [
            'nombre_indicador'      => 'Disponibilidad de Recursos del SG-SST',
            'tipo_indicador'        => 'estructura',
            'categoria'             => 'objetivos_sgsst',
            'formula'               => '(Recursos disponibles / Recursos planeados) × 100',
            'meta'                  => 100,
            'unidad_medida'         => '%',
            'periodicidad'          => 'anual',
            'numeral_resolucion'    => 'Art. 2.2.4.6.19 D.1072',
            'phva'                  => 'planear',
            'es_minimo_obligatorio' => 0,
            'definicion'            => 'Mide la proporción de recursos técnicos, financieros, humanos y de infraestructura disponibles frente a los planeados para la implementación del SG-SST.',
            'interpretacion'        => 'Un resultado del 100% indica que todos los recursos planeados están disponibles. Valores menores requieren gestión para completar la asignación.',
            'origen_datos'          => 'Presupuesto SST, actas de asignación de recursos, plan de trabajo anual',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigía',
            'keywords'              => ['estructura', 'recursos', 'disponibilidad']
        ],

        // ═══════════════════════════════════════════════════
        // PROCESO (Art. 2.2.4.6.20) — HACER
        // ═══════════════════════════════════════════════════
        [
            'nombre_indicador'      => 'Evaluación Inicial del SG-SST',
            'tipo_indicador'        => 'proceso',
            'categoria'             => 'pta',
            'formula'               => '(Ítems evaluados cumplidos / Total ítems evaluados) × 100',
            'meta'                  => 100,
            'unidad_medida'         => '%',
            'periodicidad'          => 'anual',
            'numeral_resolucion'    => 'Art. 2.2.4.6.16 D.1072',
            'phva'                  => 'hacer',
            'es_minimo_obligatorio' => 0,
            'definicion'            => 'Verifica si se realizó la evaluación inicial del SG-SST conforme a los estándares mínimos de la Resolución 0312/2019.',
            'interpretacion'        => 'Debe ser 100% (SI se realizó). Es requisito previo para la planificación del sistema.',
            'origen_datos'          => 'Formato de evaluación inicial, autoevaluación de estándares mínimos',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, ARL',
            'keywords'              => ['evaluación inicial', 'evaluacion inicial']
        ],
        [
            'nombre_indicador'      => 'Cumplimiento del Plan de Trabajo Anual',
            'tipo_indicador'        => 'proceso',
            'categoria'             => 'pta',
            'formula'               => '(Actividades ejecutadas / Actividades programadas PTA) × 100',
            'meta'                  => 100,
            'unidad_medida'         => '%',
            'periodicidad'          => 'trimestral',
            'numeral_resolucion'    => 'Art. 2.2.4.6.20 D.1072',
            'phva'                  => 'hacer',
            'es_minimo_obligatorio' => 0,
            'definicion'            => 'Mide el porcentaje de actividades ejecutadas del Plan de Trabajo Anual frente a las actividades programadas.',
            'interpretacion'        => 'Un resultado del 100% indica cumplimiento total del PTA. Valores menores indican actividades pendientes que requieren reprogramación.',
            'origen_datos'          => 'Plan de Trabajo Anual, cronograma de actividades, actas de ejecución',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigía',
            'keywords'              => ['plan de trabajo', 'pta', 'plan anual']
        ],
        [
            'nombre_indicador'      => 'Cumplimiento del Programa de Capacitación',
            'tipo_indicador'        => 'proceso',
            'categoria'             => 'capacitacion',
            'formula'               => '(Capacitaciones ejecutadas / Capacitaciones programadas) × 100',
            'meta'                  => 100,
            'unidad_medida'         => '%',
            'periodicidad'          => 'trimestral',
            'numeral_resolucion'    => 'Art. 2.2.4.6.11 D.1072',
            'phva'                  => 'hacer',
            'es_minimo_obligatorio' => 0,
            'definicion'            => 'Mide el porcentaje de capacitaciones ejecutadas frente a las programadas en el cronograma anual de capacitación.',
            'interpretacion'        => 'A mayor porcentaje, mayor cobertura de formación. Valores <80% requieren reprogramación de actividades.',
            'origen_datos'          => 'Cronograma de capacitación, registros de asistencia, evaluaciones',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigía, trabajadores',
            'keywords'              => ['programa de capacitación', 'programa de capacitacion', 'cronograma de capacitación', 'cronograma de capacitacion']
        ],
        [
            'nombre_indicador'      => 'Intervención de Peligros Identificados (Matriz IPVR)',
            'tipo_indicador'        => 'proceso',
            'categoria'             => 'riesgos',
            'formula'               => '(Peligros intervenidos / Peligros identificados) × 100',
            'meta'                  => 100,
            'unidad_medida'         => '%',
            'periodicidad'          => 'semestral',
            'numeral_resolucion'    => 'Art. 2.2.4.6.23 D.1072',
            'phva'                  => 'hacer',
            'es_minimo_obligatorio' => 0,
            'definicion'            => 'Mide la proporción de peligros identificados en la Matriz IPVR que han sido intervenidos con medidas de control.',
            'interpretacion'        => 'Un resultado del 100% indica que todos los peligros identificados tienen controles implementados. Priorizar intervención por nivel de riesgo.',
            'origen_datos'          => 'Matriz de identificación de peligros, valoración y control de riesgos (IPVR)',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigía, trabajadores',
            'keywords'              => ['peligros identificados', 'matriz ipvr', 'intervención de peligros', 'intervencion de peligros']
        ],
        [
            'nombre_indicador'      => 'Cumplimiento Programas de Vigilancia Epidemiológica',
            'tipo_indicador'        => 'proceso',
            'categoria'             => 'vigilancia',
            'formula'               => '(Actividades PVE ejecutadas / Actividades PVE programadas) × 100',
            'meta'                  => 100,
            'unidad_medida'         => '%',
            'periodicidad'          => 'trimestral',
            'numeral_resolucion'    => 'Art. 2.2.4.6.24 D.1072',
            'phva'                  => 'hacer',
            'es_minimo_obligatorio' => 0,
            'definicion'            => 'Mide el porcentaje de actividades ejecutadas de los programas de vigilancia epidemiológica frente a las programadas.',
            'interpretacion'        => 'A mayor porcentaje, mejor seguimiento de la salud de los trabajadores expuestos a factores de riesgo prioritarios.',
            'origen_datos'          => 'Programas PVE, informes de monitoreo biológico, registros de actividades',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, médico ocupacional, ARL',
            'keywords'              => ['vigilancia epidemiológica', 'vigilancia epidemiologica', 'pve']
        ],
        [
            'nombre_indicador'      => 'Eficacia de Acciones Preventivas, Correctivas y de Mejora',
            'tipo_indicador'        => 'proceso',
            'categoria'             => 'objetivos_sgsst',
            'formula'               => '(Acciones cerradas eficazmente / Total acciones generadas) × 100',
            'meta'                  => 90,
            'unidad_medida'         => '%',
            'periodicidad'          => 'trimestral',
            'numeral_resolucion'    => 'Art. 2.2.4.6.33 D.1072',
            'phva'                  => 'actuar',
            'es_minimo_obligatorio' => 0,
            'definicion'            => 'Mide la proporción de acciones correctivas, preventivas y de mejora que fueron cerradas eficazmente dentro del plazo establecido.',
            'interpretacion'        => 'Valores ≥90% indican gestión efectiva. Valores menores requieren revisión del proceso de acciones de mejora.',
            'origen_datos'          => 'Registro de acciones correctivas y preventivas, auditorías internas, inspecciones',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigía',
            'keywords'              => ['acciones preventivas', 'acciones correctivas', 'eficacia de acciones']
        ],
        [
            'nombre_indicador'      => 'Investigación de Incidentes y Accidentes de Trabajo',
            'tipo_indicador'        => 'proceso',
            'categoria'             => 'accidentalidad',
            'formula'               => '(Incidentes/accidentes investigados / Total reportados) × 100',
            'meta'                  => 100,
            'unidad_medida'         => '%',
            'periodicidad'          => 'trimestral',
            'numeral_resolucion'    => 'Art. 2.2.4.6.32 D.1072',
            'phva'                  => 'hacer',
            'es_minimo_obligatorio' => 0,
            'definicion'            => 'Mide la proporción de incidentes y accidentes de trabajo que fueron investigados conforme al procedimiento establecido.',
            'interpretacion'        => 'Debe ser 100%. Cualquier incidente/accidente no investigado incumple el Art. 2.2.4.6.32 del Decreto 1072/2015.',
            'origen_datos'          => 'Formato de investigación de incidentes/accidentes, FURAT, reportes ARL',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigía, ARL',
            'keywords'              => ['investigación de incidentes', 'investigacion de incidentes', 'investigación de accidentes', 'investigacion de accidentes', 'reporte e investigación']
        ],

        // ═══════════════════════════════════════════════════
        // RESULTADO (Art. 2.2.4.6.21) — VERIFICAR/ACTUAR
        // ═══════════════════════════════════════════════════
        [
            'nombre_indicador'      => 'Cumplimiento de Objetivos del SG-SST',
            'tipo_indicador'        => 'resultado',
            'categoria'             => 'objetivos_sgsst',
            'formula'               => '(Objetivos cumplidos / Total objetivos definidos) × 100',
            'meta'                  => 100,
            'unidad_medida'         => '%',
            'periodicidad'          => 'anual',
            'numeral_resolucion'    => 'Art. 2.2.4.6.18 D.1072',
            'phva'                  => 'verificar',
            'es_minimo_obligatorio' => 0,
            'definicion'            => 'Mide el porcentaje de objetivos del SG-SST alcanzados durante el periodo evaluado.',
            'interpretacion'        => 'Un resultado del 100% indica cumplimiento total de los objetivos. Valores menores requieren ajuste en la planificación.',
            'origen_datos'          => 'Plan de objetivos y metas del SG-SST, informes de gestión',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigía',
            'keywords'              => ['cumplimiento de objetivos', 'objetivos del sg-sst', 'objetivos del sgsst']
        ],
        [
            'nombre_indicador'      => 'Cumplimiento de Requisitos Legales Aplicables',
            'tipo_indicador'        => 'resultado',
            'categoria'             => 'objetivos_sgsst',
            'formula'               => '(Requisitos legales cumplidos / Total requisitos identificados) × 100',
            'meta'                  => 100,
            'unidad_medida'         => '%',
            'periodicidad'          => 'semestral',
            'numeral_resolucion'    => 'Art. 2.2.4.6.8 D.1072',
            'phva'                  => 'verificar',
            'es_minimo_obligatorio' => 0,
            'definicion'            => 'Mide la proporción de requisitos legales en SST identificados en la matriz legal que la organización cumple.',
            'interpretacion'        => 'Debe ser 100%. Valores menores indican incumplimientos normativos que pueden acarrear sanciones.',
            'origen_datos'          => 'Matriz de requisitos legales, evaluaciones de cumplimiento',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, asesor jurídico',
            'keywords'              => ['requisitos legales', 'cumplimiento legal', 'matriz legal']
        ],
        [
            'nombre_indicador'      => 'Resultados de Programas de Rehabilitación',
            'tipo_indicador'        => 'resultado',
            'categoria'             => 'vigilancia',
            'formula'               => '(Trabajadores reintegrados exitosamente / Total en rehabilitación) × 100',
            'meta'                  => 100,
            'unidad_medida'         => '%',
            'periodicidad'          => 'semestral',
            'numeral_resolucion'    => 'Art. 2.2.4.6.22 D.1072',
            'phva'                  => 'verificar',
            'es_minimo_obligatorio' => 0,
            'definicion'            => 'Mide la proporción de trabajadores que fueron reintegrados exitosamente al trabajo después de un programa de rehabilitación.',
            'interpretacion'        => 'A mayor porcentaje, mayor efectividad del programa de rehabilitación y reintegro laboral.',
            'origen_datos'          => 'Registros de rehabilitación, informes médicos, actas de reintegro',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, médico ocupacional, ARL',
            'keywords'              => ['rehabilitación', 'rehabilitacion', 'reintegro']
        ],

        // ═══════════════════════════════════════════════════
        // 6 MÍNIMOS OBLIGATORIOS — Res. 0312/2019 Art. 30
        // ═══════════════════════════════════════════════════
        [
            'nombre_indicador'      => 'Índice de Frecuencia de Accidentes de Trabajo (IF)',
            'tipo_indicador'        => 'resultado',
            'categoria'             => 'accidentalidad',
            'formula'               => '(N° accidentes de trabajo en el periodo / HHT en el periodo) × 240.000',
            'meta'                  => null,
            'unidad_medida'         => 'por 240.000 HHT',
            'periodicidad'          => 'mensual',
            'numeral_resolucion'    => 'Art. 30 Res. 0312/2019',
            'phva'                  => 'verificar',
            'es_minimo_obligatorio' => 1,
            'definicion'            => 'Expresa el número de accidentes de trabajo ocurridos durante el último año por cada 240.000 horas hombre trabajadas.',
            'interpretacion'        => 'A menor valor, menor frecuencia de accidentalidad. Se debe comparar con el periodo anterior y con la media del sector económico.',
            'origen_datos'          => 'FURAT, registro de accidentes de trabajo, nómina (HHT)',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigía, ARL, trabajadores',
            'keywords'              => ['frecuencia de accidentes', 'índice de frecuencia', 'indice de frecuencia', 'IF ']
        ],
        [
            'nombre_indicador'      => 'Índice de Severidad de Accidentes de Trabajo (IS)',
            'tipo_indicador'        => 'resultado',
            'categoria'             => 'accidentalidad',
            'formula'               => '(N° días perdidos y cargados por AT / HHT en el periodo) × 240.000',
            'meta'                  => null,
            'unidad_medida'         => 'por 240.000 HHT',
            'periodicidad'          => 'mensual',
            'numeral_resolucion'    => 'Art. 30 Res. 0312/2019',
            'phva'                  => 'verificar',
            'es_minimo_obligatorio' => 1,
            'definicion'            => 'Expresa el número de días perdidos y cargados por accidentes de trabajo durante el último año por cada 240.000 horas hombre trabajadas.',
            'interpretacion'        => 'A menor valor, menor severidad de los accidentes. Valores altos indican accidentes graves con muchos días de incapacidad.',
            'origen_datos'          => 'FURAT, incapacidades por AT, nómina (HHT)',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigía, ARL, trabajadores',
            'keywords'              => ['severidad de accidentes', 'índice de severidad', 'indice de severidad', 'IS ']
        ],
        [
            'nombre_indicador'      => 'Proporción de Accidentes de Trabajo Mortales (PATM)',
            'tipo_indicador'        => 'resultado',
            'categoria'             => 'accidentalidad',
            'formula'               => '(N° accidentes de trabajo mortales / Total accidentes de trabajo) × 100',
            'meta'                  => 0,
            'unidad_medida'         => '%',
            'periodicidad'          => 'anual',
            'numeral_resolucion'    => 'Art. 30 Res. 0312/2019',
            'phva'                  => 'verificar',
            'es_minimo_obligatorio' => 1,
            'definicion'            => 'Expresa la relación porcentual de accidentes de trabajo mortales sobre el total de accidentes de trabajo ocurridos en el periodo.',
            'interpretacion'        => 'Debe ser 0%. Cualquier valor mayor a 0% indica una fatalidad que requiere investigación inmediata y acciones correctivas urgentes.',
            'origen_datos'          => 'FURAT, reportes ARL, investigaciones de accidentes mortales',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigía, ARL, MinTrabajo',
            'keywords'              => ['accidentes mortales', 'mortalidad', 'proporción de accidentes de trabajo mortales', 'PATM']
        ],
        [
            'nombre_indicador'      => 'Prevalencia de Enfermedad Laboral (PEL)',
            'tipo_indicador'        => 'resultado',
            'categoria'             => 'vigilancia',
            'formula'               => '(N° casos nuevos y antiguos de EL / N° promedio trabajadores año) × 100.000',
            'meta'                  => null,
            'unidad_medida'         => 'por 100.000',
            'periodicidad'          => 'anual',
            'numeral_resolucion'    => 'Art. 30 Res. 0312/2019',
            'phva'                  => 'verificar',
            'es_minimo_obligatorio' => 1,
            'definicion'            => 'Mide el número total de casos de enfermedad laboral (nuevos y existentes) por cada 100.000 trabajadores en el periodo.',
            'interpretacion'        => 'A menor valor, menor carga de enfermedad laboral. Se compara con estadísticas sectoriales de la ARL.',
            'origen_datos'          => 'Diagnósticos médicos ocupacionales, reportes EPS/ARL, historias clínicas ocupacionales',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, médico ocupacional, ARL',
            'keywords'              => ['prevalencia', 'enfermedad laboral', 'PEL']
        ],
        [
            'nombre_indicador'      => 'Incidencia de Enfermedad Laboral (IEL)',
            'tipo_indicador'        => 'resultado',
            'categoria'             => 'vigilancia',
            'formula'               => '(N° casos nuevos de EL en el periodo / N° promedio trabajadores año) × 100.000',
            'meta'                  => null,
            'unidad_medida'         => 'por 100.000',
            'periodicidad'          => 'anual',
            'numeral_resolucion'    => 'Art. 30 Res. 0312/2019',
            'phva'                  => 'verificar',
            'es_minimo_obligatorio' => 1,
            'definicion'            => 'Mide el número de casos nuevos de enfermedad laboral por cada 100.000 trabajadores en el periodo.',
            'interpretacion'        => 'A menor valor, mejor control de los factores de riesgo. Un aumento indica falla en las medidas preventivas.',
            'origen_datos'          => 'Diagnósticos médicos ocupacionales, reportes EPS/ARL, primeros diagnósticos',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, médico ocupacional, ARL',
            'keywords'              => ['incidencia', 'enfermedad laboral', 'IEL']
        ],
        [
            'nombre_indicador'      => 'Ausentismo por Causa Médica (ACM)',
            'tipo_indicador'        => 'resultado',
            'categoria'             => 'ausentismo',
            'formula'               => '(N° días de ausencia por causa médica / N° días de trabajo programados) × 100',
            'meta'                  => null,
            'unidad_medida'         => '%',
            'periodicidad'          => 'mensual',
            'numeral_resolucion'    => 'Art. 30 Res. 0312/2019',
            'phva'                  => 'verificar',
            'es_minimo_obligatorio' => 1,
            'definicion'            => 'Mide la proporción de días de ausencia por incapacidades médicas frente al total de días de trabajo programados.',
            'interpretacion'        => 'A menor porcentaje, menor ausentismo. Valores altos requieren análisis de causas (enfermedad general vs laboral) y acciones correctivas.',
            'origen_datos'          => 'Registro de incapacidades médicas, nómina, RRHH',
            'cargo_responsable'     => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, RRHH, ARL',
            'keywords'              => ['ausentismo', 'causa médica', 'causa medica', 'ACM']
        ],
    ];

    /**
     * Indicadores sugeridos para el cronograma de capacitaciones
     * Máximo: 7 est = 2, 21 est = 3, 60 est = 4
     * Solo indicadores relacionados con capacitaciones
     */
    public const INDICADORES_SUGERIDOS = [
        7 => [
            [
                'nombre' => 'Cumplimiento del Cronograma de Capacitación',
                'tipo' => 'proceso',
                'categoria' => 'capacitacion',
                'formula' => '(Capacitaciones ejecutadas / Capacitaciones programadas) x 100',
                'meta' => 100,
                'unidad' => '%',
                'periodicidad' => 'trimestral',
                'phva' => 'verificar'
            ],
            [
                'nombre' => 'Cobertura de Capacitación',
                'tipo' => 'proceso',
                'categoria' => 'capacitacion',
                'formula' => '(Trabajadores capacitados / Total trabajadores programados) x 100',
                'meta' => 100,
                'unidad' => '%',
                'periodicidad' => 'trimestral',
                'phva' => 'verificar'
            ]
        ],
        21 => [
            [
                'nombre' => 'Cumplimiento del Cronograma de Capacitación',
                'tipo' => 'proceso',
                'categoria' => 'capacitacion',
                'formula' => '(Capacitaciones ejecutadas / Capacitaciones programadas) x 100',
                'meta' => 100,
                'unidad' => '%',
                'periodicidad' => 'trimestral',
                'phva' => 'verificar'
            ],
            [
                'nombre' => 'Cobertura de Capacitación',
                'tipo' => 'proceso',
                'categoria' => 'capacitacion',
                'formula' => '(Trabajadores capacitados / Total trabajadores programados) x 100',
                'meta' => 100,
                'unidad' => '%',
                'periodicidad' => 'trimestral',
                'phva' => 'verificar'
            ],
            [
                'nombre' => 'Evaluación de Capacitaciones',
                'tipo' => 'resultado',
                'categoria' => 'capacitacion',
                'formula' => '(Promedio de calificaciones obtenidas / Calificación máxima) x 100',
                'meta' => 80,
                'unidad' => '%',
                'periodicidad' => 'trimestral',
                'phva' => 'verificar'
            ]
        ],
        60 => [
            [
                'nombre' => 'Cumplimiento del Cronograma de Capacitación',
                'tipo' => 'proceso',
                'categoria' => 'capacitacion',
                'formula' => '(Capacitaciones ejecutadas / Capacitaciones programadas) x 100',
                'meta' => 100,
                'unidad' => '%',
                'periodicidad' => 'trimestral',
                'phva' => 'verificar'
            ],
            [
                'nombre' => 'Cobertura de Capacitación',
                'tipo' => 'proceso',
                'categoria' => 'capacitacion',
                'formula' => '(Trabajadores capacitados / Total trabajadores programados) x 100',
                'meta' => 100,
                'unidad' => '%',
                'periodicidad' => 'trimestral',
                'phva' => 'verificar'
            ],
            [
                'nombre' => 'Evaluación de Capacitaciones',
                'tipo' => 'resultado',
                'categoria' => 'capacitacion',
                'formula' => '(Promedio de calificaciones obtenidas / Calificación máxima) x 100',
                'meta' => 80,
                'unidad' => '%',
                'periodicidad' => 'trimestral',
                'phva' => 'verificar'
            ],
            [
                'nombre' => 'Oportunidad en la Ejecución de Capacitaciones',
                'tipo' => 'proceso',
                'categoria' => 'capacitacion',
                'formula' => '(Capacitaciones ejecutadas en fecha / Total capacitaciones ejecutadas) x 100',
                'meta' => 90,
                'unidad' => '%',
                'periodicidad' => 'trimestral',
                'phva' => 'verificar'
            ]
        ]
    ];

    /**
     * Obtiene indicadores de un cliente
     */
    public function getByCliente(int $idCliente, bool $soloActivos = true, ?string $categoria = null): array
    {
        $builder = $this->where('id_cliente', $idCliente);

        if ($soloActivos) {
            $builder->where('activo', 1);
        }

        if ($categoria !== null) {
            $builder->where('categoria', $categoria);
        }

        return $builder->orderBy('categoria', 'ASC')
                      ->orderBy('tipo_indicador', 'ASC')
                      ->orderBy('nombre_indicador', 'ASC')
                      ->findAll();
    }

    /**
     * Obtiene indicadores agrupados por tipo
     */
    public function getByClienteAgrupados(int $idCliente): array
    {
        $indicadores = $this->getByCliente($idCliente);

        $grupos = [
            'estructura' => [
                'titulo' => 'Indicadores de Estructura',
                'descripcion' => 'Miden recursos, políticas y organización del SG-SST',
                'items' => []
            ],
            'proceso' => [
                'titulo' => 'Indicadores de Proceso',
                'descripcion' => 'Miden la ejecución de actividades del SG-SST',
                'items' => []
            ],
            'resultado' => [
                'titulo' => 'Indicadores de Resultado',
                'descripcion' => 'Miden el impacto en seguridad y salud de los trabajadores',
                'items' => []
            ]
        ];

        foreach ($indicadores as $ind) {
            $tipo = $ind['tipo_indicador'] ?? 'proceso';
            if (isset($grupos[$tipo])) {
                $grupos[$tipo]['items'][] = $ind;
            }
        }

        return $grupos;
    }

    /**
     * Obtiene indicadores agrupados por categoría (solo los items)
     * Devuelve array [categoria => [indicadores...]]
     */
    public function getByClienteAgrupadosPorCategoria(int $idCliente): array
    {
        $indicadores = $this->getByCliente($idCliente);

        $grupos = [];

        foreach ($indicadores as $ind) {
            $cat = $ind['categoria'] ?? 'otro';
            if (!isset(self::CATEGORIAS[$cat])) {
                $cat = 'otro';
            }

            if (!isset($grupos[$cat])) {
                $grupos[$cat] = [];
            }

            $grupos[$cat][] = $ind;
        }

        return $grupos;
    }

    /**
     * Obtiene resumen de indicadores por categoría (solo categorías con indicadores)
     * Devuelve array con estadísticas por categoría
     */
    public function getResumenPorCategoria(int $idCliente): array
    {
        $indicadores = $this->getByCliente($idCliente);

        $resumen = [];

        foreach ($indicadores as $ind) {
            $cat = $ind['categoria'] ?? 'otro';
            if (!isset(self::CATEGORIAS[$cat])) {
                $cat = 'otro';
            }

            if (!isset($resumen[$cat])) {
                $resumen[$cat] = [
                    'total' => 0,
                    'medidos' => 0,
                    'cumplen' => 0,
                    'no_cumplen' => 0,
                    'porcentaje_cumplimiento' => null
                ];
            }

            $resumen[$cat]['total']++;

            if ($ind['cumple_meta'] !== null) {
                $resumen[$cat]['medidos']++;
                if ($ind['cumple_meta'] == 1) {
                    $resumen[$cat]['cumplen']++;
                } else {
                    $resumen[$cat]['no_cumplen']++;
                }
            }
        }

        // Calcular porcentajes
        foreach ($resumen as $cat => &$stats) {
            if ($stats['medidos'] > 0) {
                $stats['porcentaje_cumplimiento'] = round(($stats['cumplen'] / $stats['medidos']) * 100);
            }
        }

        return $resumen;
    }

    /**
     * Obtiene indicadores vinculados a una actividad del PTA
     */
    public function getByActividad(int $idActividad): array
    {
        return $this->where('id_actividad_pta', $idActividad)
                    ->where('activo', 1)
                    ->findAll();
    }

    /**
     * Verifica cumplimiento de indicadores
     */
    public function verificarCumplimiento(int $idCliente): array
    {
        $indicadores = $this->getByCliente($idCliente);

        $total = count($indicadores);
        $medidos = 0;
        $cumplen = 0;
        $noCumplen = 0;
        $sinMedir = 0;

        foreach ($indicadores as $ind) {
            if ($ind['cumple_meta'] === null) {
                $sinMedir++;
            } else {
                $medidos++;
                if ($ind['cumple_meta'] == 1) {
                    $cumplen++;
                } else {
                    $noCumplen++;
                }
            }
        }

        return [
            'total' => $total,
            'medidos' => $medidos,
            'cumplen' => $cumplen,
            'no_cumplen' => $noCumplen,
            'sin_medir' => $sinMedir,
            'porcentaje_cumplimiento' => $medidos > 0
                ? round(($cumplen / $medidos) * 100)
                : 0,
            'porcentaje_medicion' => $total > 0
                ? round(($medidos / $total) * 100)
                : 0
        ];
    }

    /**
     * Verificar cumplimiento de indicadores específicos de PyP Salud
     */
    public function verificarCumplimientoPyPSalud(int $idCliente): array
    {
        $indicadores = $this->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'pyp_salud')
                ->orWhere('categoria', 'promocion_prevencion')
                ->orLike('nombre_indicador', 'examen', 'both', true, true)
                ->orLike('nombre_indicador', 'enfermedad', 'both', true, true)
                ->orLike('nombre_indicador', 'salud', 'both', true, true)
                ->orLike('nombre_indicador', 'medico', 'both', true, true)
                ->orLike('nombre_indicador', 'ausentismo', 'both', true, true)
            ->groupEnd()
            ->findAll();

        $total = count($indicadores);

        return [
            'total' => $total,
            'completo' => $total >= 3,
            'minimo' => 3,
            'indicadores' => $indicadores
        ];
    }

    /**
     * Registra una medición de indicador
     */
    public function registrarMedicion(int $idIndicador, array $datos): bool
    {
        $indicador = $this->find($idIndicador);
        if (!$indicador) {
            return false;
        }

        $db = \Config\Database::connect();

        // Calcular resultado si hay numerador y denominador
        $resultado = null;
        if (!empty($datos['valor_numerador']) && !empty($datos['valor_denominador']) && $datos['valor_denominador'] > 0) {
            $resultado = ($datos['valor_numerador'] / $datos['valor_denominador']) * 100;
        }

        // Verificar si cumple meta
        $cumple = null;
        if ($resultado !== null && $indicador['meta'] !== null) {
            // Para índices de accidentalidad, menor es mejor
            if (strpos(strtolower($indicador['nombre_indicador']), 'accidentalidad') !== false) {
                $cumple = $resultado <= $indicador['meta'] ? 1 : 0;
            } else {
                $cumple = $resultado >= $indicador['meta'] ? 1 : 0;
            }
        }

        // Actualizar indicador
        $this->update($idIndicador, [
            'valor_numerador' => $datos['valor_numerador'] ?? null,
            'valor_denominador' => $datos['valor_denominador'] ?? null,
            'valor_resultado' => $resultado,
            'fecha_medicion' => $datos['fecha_medicion'] ?? date('Y-m-d'),
            'cumple_meta' => $cumple,
            'observaciones' => $datos['observaciones'] ?? null
        ]);

        // Guardar en histórico
        $periodo = $datos['periodo'] ?? date('Y-m');
        $db->table('tbl_indicadores_sst_mediciones')->insert([
            'id_indicador' => $idIndicador,
            'periodo' => $periodo,
            'valor_numerador' => $datos['valor_numerador'] ?? null,
            'valor_denominador' => $datos['valor_denominador'] ?? null,
            'valor_resultado' => $resultado,
            'cumple_meta' => $cumple,
            'observaciones' => $datos['observaciones'] ?? null,
            'registrado_por' => $datos['registrado_por'] ?? null
        ]);

        return true;
    }

    /**
     * Obtiene histórico de mediciones de un indicador
     */
    public function getHistoricoMediciones(int $idIndicador, int $limite = 12): array
    {
        $db = \Config\Database::connect();

        return $db->table('tbl_indicadores_sst_mediciones')
                  ->where('id_indicador', $idIndicador)
                  ->orderBy('fecha_registro', 'DESC')
                  ->limit($limite)
                  ->get()
                  ->getResultArray();
    }

    /**
     * Genera indicadores sugeridos para un cliente según sus estándares
     */
    public function generarIndicadoresSugeridos(int $idCliente, int $estandares): array
    {
        // Determinar nivel
        $nivel = $estandares <= 7 ? 7 : ($estandares <= 21 ? 21 : 60);

        // Máximo de indicadores según nivel
        $maxIndicadores = $nivel <= 7 ? 2 : ($nivel <= 21 ? 3 : 4);

        $sugeridos = self::INDICADORES_SUGERIDOS[$nivel] ?? self::INDICADORES_SUGERIDOS[7];

        // Verificar cuáles ya existen
        $existentes = $this->getByCliente($idCliente);
        $nombresExistentes = array_column($existentes, 'nombre_indicador');

        $nuevos = [];
        foreach ($sugeridos as $sug) {
            if (!in_array($sug['nombre'], $nombresExistentes) && count($nuevos) < $maxIndicadores) {
                $nuevos[] = $sug;
            }
        }

        return [
            'nivel' => $nivel,
            'max_indicadores' => $maxIndicadores,
            'existentes' => count($existentes),
            'sugeridos' => $nuevos
        ];
    }

    /**
     * Crea indicadores sugeridos para un cliente
     */
    public function crearIndicadoresSugeridos(int $idCliente, int $estandares): int
    {
        $sugerencia = $this->generarIndicadoresSugeridos($idCliente, $estandares);

        $creados = 0;
        foreach ($sugerencia['sugeridos'] as $sug) {
            $this->insert([
                'id_cliente' => $idCliente,
                'nombre_indicador' => $sug['nombre'],
                'tipo_indicador' => $sug['tipo'],
                'categoria' => $sug['categoria'] ?? 'capacitacion',
                'formula' => $sug['formula'],
                'meta' => $sug['meta'],
                'unidad_medida' => $sug['unidad'],
                'periodicidad' => $sug['periodicidad'],
                'phva' => $sug['phva'],
                'activo' => 1
            ]);
            $creados++;
        }

        return $creados;
    }

    /**
     * Verificar cumplimiento de indicadores de Capacitacion SST
     */
    public function verificarCumplimientoCapacitacion(int $idCliente): array
    {
        $indicadores = $this->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'capacitacion')
                ->orLike('nombre_indicador', 'capacitacion', 'both', true, true)
                ->orLike('nombre_indicador', 'cobertura', 'both', true, true)
                ->orLike('nombre_indicador', 'cronograma', 'both', true, true)
            ->groupEnd()
            ->findAll();

        $total = count($indicadores);

        // Minimo segun estandares (simplificado a 2)
        $minimo = 2;

        return [
            'total' => $total,
            'completo' => $total >= $minimo,
            'minimo' => $minimo,
            'indicadores' => $indicadores
        ];
    }

    /**
     * Genera contenido de indicadores para documentos
     */
    public function generarContenidoParaDocumento(int $idCliente): string
    {
        $indicadores = $this->getByCliente($idCliente);

        if (empty($indicadores)) {
            return "[PENDIENTE: Configurar indicadores del SG-SST en el módulo de Indicadores]";
        }

        $contenido = "**INDICADORES DEL SG-SST**\n\n";

        $agrupados = $this->getByClienteAgrupados($idCliente);

        foreach ($agrupados as $tipo => $grupo) {
            if (!empty($grupo['items'])) {
                $contenido .= "### " . $grupo['titulo'] . "\n";
                $contenido .= $grupo['descripcion'] . "\n\n";

                foreach ($grupo['items'] as $ind) {
                    $contenido .= "**{$ind['nombre_indicador']}**\n";
                    if (!empty($ind['formula'])) {
                        $contenido .= "- Fórmula: {$ind['formula']}\n";
                    }
                    if (!empty($ind['meta'])) {
                        $contenido .= "- Meta: {$ind['meta']}{$ind['unidad_medida']}\n";
                    }
                    $contenido .= "- Periodicidad: " . (self::PERIODICIDADES[$ind['periodicidad']] ?? $ind['periodicidad']) . "\n";

                    if ($ind['valor_resultado'] !== null) {
                        $estado = $ind['cumple_meta'] ? '✓ Cumple' : '✗ No cumple';
                        $contenido .= "- Último resultado: {$ind['valor_resultado']}{$ind['unidad_medida']} ({$estado})\n";
                    }
                    $contenido .= "\n";
                }
            }
        }

        return $contenido;
    }

    // ─────────────────────────────────────────────────────────
    // DASHBOARD JERÁRQUICO (ZZ_94)
    // ─────────────────────────────────────────────────────────

    /**
     * Pesos de ponderación por tipo (Decreto 1072 + Res. 0312 PHVA)
     */
    public const PESOS_TIPO = [
        'estructura' => 0.25,
        'proceso'    => 0.35,
        'resultado'  => 0.40
    ];

    /**
     * Colores del dashboard por tipo legal
     */
    public const COLORES_TIPO = [
        'estructura' => '#3498db',
        'proceso'    => '#f39c12',
        'resultado'  => '#27ae60'
    ];

    /**
     * Datos consolidados para dashboard jerárquico (Niveles 1-3)
     */
    public function getDashboardData(int $idCliente): array
    {
        $indicadores = $this->getByCliente($idCliente);

        // Nivel 2: por tipo legal
        $nivel2 = [];
        foreach (['estructura', 'proceso', 'resultado'] as $tipo) {
            $nivel2[$tipo] = [
                'total' => 0, 'medidos' => 0, 'cumplen' => 0,
                'no_cumplen' => 0, 'sin_medir' => 0, 'valor' => 0
            ];
        }

        // Nivel 3: por categoría (con sub-breakdown E/P/R)
        $nivel3 = [];

        foreach ($indicadores as $ind) {
            $tipo = $ind['tipo_indicador'] ?? 'proceso';
            $cat  = $ind['categoria'] ?? 'otro';
            if (!isset(self::CATEGORIAS[$cat])) $cat = 'otro';

            // Nivel 2
            $nivel2[$tipo]['total']++;
            if ($ind['cumple_meta'] !== null) {
                $nivel2[$tipo]['medidos']++;
                if ($ind['cumple_meta'] == 1) {
                    $nivel2[$tipo]['cumplen']++;
                } else {
                    $nivel2[$tipo]['no_cumplen']++;
                }
            } else {
                $nivel2[$tipo]['sin_medir']++;
            }

            // Nivel 3
            if (!isset($nivel3[$cat])) {
                $nivel3[$cat] = [
                    'total' => 0, 'medidos' => 0, 'cumplen' => 0,
                    'no_cumplen' => 0, 'sin_medir' => 0,
                    'valor' => 0, 'es_minimo' => false,
                    'por_tipo' => [
                        'estructura' => ['total' => 0, 'cumplen' => 0, 'medidos' => 0],
                        'proceso'    => ['total' => 0, 'cumplen' => 0, 'medidos' => 0],
                        'resultado'  => ['total' => 0, 'cumplen' => 0, 'medidos' => 0],
                    ]
                ];
            }

            $nivel3[$cat]['total']++;
            $nivel3[$cat]['por_tipo'][$tipo]['total']++;

            if (!empty($ind['es_minimo_obligatorio'])) {
                $nivel3[$cat]['es_minimo'] = true;
            }

            if ($ind['cumple_meta'] !== null) {
                $nivel3[$cat]['medidos']++;
                $nivel3[$cat]['por_tipo'][$tipo]['medidos']++;
                if ($ind['cumple_meta'] == 1) {
                    $nivel3[$cat]['cumplen']++;
                    $nivel3[$cat]['por_tipo'][$tipo]['cumplen']++;
                } else {
                    $nivel3[$cat]['no_cumplen']++;
                }
            } else {
                $nivel3[$cat]['sin_medir']++;
            }
        }

        // Calcular porcentajes Nivel 2
        foreach ($nivel2 as $tipo => &$data) {
            $data['valor'] = $data['medidos'] > 0
                ? round(($data['cumplen'] / $data['medidos']) * 100)
                : 0;
        }
        unset($data);

        // Calcular porcentajes Nivel 3
        foreach ($nivel3 as $cat => &$data) {
            $data['valor'] = $data['medidos'] > 0
                ? round(($data['cumplen'] / $data['medidos']) * 100)
                : 0;
            foreach ($data['por_tipo'] as $tipo => &$sub) {
                $sub['valor'] = $sub['medidos'] > 0
                    ? round(($sub['cumplen'] / $sub['medidos']) * 100)
                    : 0;
            }
            unset($sub);
        }
        unset($data);

        // Nivel 1: consolidación global ponderada
        $nivel1 = $this->calcularConsolidacion($nivel2);

        return [
            'nivel1' => $nivel1,
            'nivel2' => $nivel2,
            'nivel3' => $nivel3,
            'total_indicadores' => count($indicadores)
        ];
    }

    /**
     * Consolidación global ponderada (Decreto 1072 PHVA)
     * Estructura × 25% + Proceso × 35% + Resultado × 40%
     */
    public function getConsolidacionGlobal(int $idCliente): array
    {
        $indicadores = $this->getByCliente($idCliente);

        $porTipo = [];
        foreach (['estructura', 'proceso', 'resultado'] as $tipo) {
            $porTipo[$tipo] = ['medidos' => 0, 'cumplen' => 0, 'valor' => 0];
        }

        foreach ($indicadores as $ind) {
            $tipo = $ind['tipo_indicador'] ?? 'proceso';
            if ($ind['cumple_meta'] !== null) {
                $porTipo[$tipo]['medidos']++;
                if ($ind['cumple_meta'] == 1) {
                    $porTipo[$tipo]['cumplen']++;
                }
            }
        }

        foreach ($porTipo as &$data) {
            $data['valor'] = $data['medidos'] > 0
                ? round(($data['cumplen'] / $data['medidos']) * 100)
                : 0;
        }
        unset($data);

        return $this->calcularConsolidacion($porTipo);
    }

    /**
     * Indicadores mínimos obligatorios (Res. 0312 Art. 30)
     */
    public function getMinimosObligatorios(int $idCliente): array
    {
        $minimos = $this->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->where('es_minimo_obligatorio', 1)
            ->orderBy('nombre_indicador', 'ASC')
            ->findAll();

        $total = count($minimos);
        $cumplen = 0;

        foreach ($minimos as &$m) {
            // Agregar histórico resumido (últimas 4 mediciones)
            $m['historico'] = $this->getHistoricoMediciones($m['id_indicador'], 4);
            if ($m['cumple_meta'] == 1) {
                $cumplen++;
            }
        }
        unset($m);

        return [
            'indicadores' => $minimos,
            'total' => $total,
            'cumplen' => $cumplen,
            'porcentaje' => $total > 0 ? round(($cumplen / $total) * 100) : 0
        ];
    }

    // ─────────────────────────────────────────────────────────
    // AUTO-SEED INDICADORES LEGALES (Decreto 1072 + Res. 0312)
    // ─────────────────────────────────────────────────────────

    /**
     * Crea indicadores legales obligatorios para un cliente.
     * Detecta duplicados por keywords para no duplicar existentes.
     * Corrige categoría/tipo de indicadores mal clasificados.
     *
     * @return array ['creados' => int, 'corregidos' => int, 'existentes' => int]
     */
    public function crearIndicadoresLegales(int $idCliente): array
    {
        $existentes = $this->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->findAll();

        $nombresExistentes = [];
        foreach ($existentes as $ind) {
            $nombresExistentes[] = [
                'id'     => $ind['id_indicador'],
                'nombre' => mb_strtolower($ind['nombre_indicador']),
                'tipo'   => $ind['tipo_indicador'],
                'cat'    => $ind['categoria'],
            ];
        }

        $creados = 0;
        $corregidos = 0;
        $yaExisten = 0;

        foreach (self::INDICADORES_LEGALES as $legal) {
            $keywords = $legal['keywords'] ?? [];
            $encontrado = null;

            // Buscar si ya existe un indicador que matchee algún keyword
            foreach ($nombresExistentes as $ex) {
                foreach ($keywords as $kw) {
                    if (mb_stripos($ex['nombre'], mb_strtolower($kw)) !== false) {
                        $encontrado = $ex;
                        break 2;
                    }
                }
            }

            if ($encontrado) {
                // Ya existe: verificar si necesita corrección de tipo/categoría
                $necesitaCorreccion = false;
                $updates = [];

                if ($encontrado['tipo'] !== $legal['tipo_indicador']) {
                    $updates['tipo_indicador'] = $legal['tipo_indicador'];
                    $necesitaCorreccion = true;
                }
                if ($encontrado['cat'] !== $legal['categoria']) {
                    $updates['categoria'] = $legal['categoria'];
                    $necesitaCorreccion = true;
                }
                if ($legal['es_minimo_obligatorio'] && empty($this->find($encontrado['id'])['es_minimo_obligatorio'])) {
                    $updates['es_minimo_obligatorio'] = 1;
                    $necesitaCorreccion = true;
                }

                if ($necesitaCorreccion) {
                    $this->update($encontrado['id'], $updates);
                    $corregidos++;
                } else {
                    $yaExisten++;
                }
            } else {
                // No existe: crear
                $datos = [
                    'id_cliente'              => $idCliente,
                    'nombre_indicador'        => $legal['nombre_indicador'],
                    'tipo_indicador'          => $legal['tipo_indicador'],
                    'categoria'               => $legal['categoria'],
                    'formula'                 => $legal['formula'],
                    'meta'                    => $legal['meta'],
                    'unidad_medida'           => $legal['unidad_medida'],
                    'periodicidad'            => $legal['periodicidad'],
                    'numeral_resolucion'      => $legal['numeral_resolucion'],
                    'phva'                    => $legal['phva'],
                    'es_minimo_obligatorio'   => $legal['es_minimo_obligatorio'],
                    'definicion'              => $legal['definicion'] ?? null,
                    'interpretacion'          => $legal['interpretacion'] ?? null,
                    'origen_datos'            => $legal['origen_datos'] ?? null,
                    'cargo_responsable'       => $legal['cargo_responsable'] ?? null,
                    'cargos_conocer_resultado'=> $legal['cargos_conocer_resultado'] ?? null,
                    'activo'                  => 1,
                ];
                $this->insert($datos);
                $creados++;
            }
        }

        return [
            'creados'    => $creados,
            'corregidos' => $corregidos,
            'existentes' => $yaExisten,
        ];
    }

    /**
     * Verifica si un cliente ya tiene los indicadores legales sembrados.
     * Retorna true si TODOS los 18 están presentes (por keyword match).
     */
    public function tieneIndicadoresLegales(int $idCliente): bool
    {
        $existentes = $this->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->findAll();

        $nombresLower = array_map(function ($ind) {
            return mb_strtolower($ind['nombre_indicador']);
        }, $existentes);

        foreach (self::INDICADORES_LEGALES as $legal) {
            $keywords = $legal['keywords'] ?? [];
            $encontrado = false;

            foreach ($keywords as $kw) {
                foreach ($nombresLower as $nombre) {
                    if (mb_stripos($nombre, mb_strtolower($kw)) !== false) {
                        $encontrado = true;
                        break 2;
                    }
                }
            }

            if (!$encontrado) {
                return false;
            }
        }

        return true;
    }

    /**
     * Helper: calcula consolidación ponderada de nivel 2 a nivel 1
     */
    private function calcularConsolidacion(array $nivel2): array
    {
        $tieneData = false;
        foreach ($nivel2 as $data) {
            if (($data['medidos'] ?? 0) > 0 || ($data['total'] ?? 0) > 0) {
                $tieneData = true;
                break;
            }
        }

        if (!$tieneData) {
            return ['global' => 0, 'semaforo' => 'gris', 'tiene_datos' => false];
        }

        $global = 0;
        $pesoUsado = 0;
        foreach (self::PESOS_TIPO as $tipo => $peso) {
            if (isset($nivel2[$tipo]) && ($nivel2[$tipo]['medidos'] ?? 0) > 0) {
                $global += $nivel2[$tipo]['valor'] * $peso;
                $pesoUsado += $peso;
            }
        }

        // Normalizar si no hay todos los tipos
        if ($pesoUsado > 0 && $pesoUsado < 1.0) {
            $global = round($global / $pesoUsado);
        } else {
            $global = round($global);
        }

        // Semáforo
        if ($global >= 85) {
            $semaforo = 'verde';
        } elseif ($global >= 60) {
            $semaforo = 'amarillo';
        } else {
            $semaforo = 'rojo';
        }

        return [
            'global' => $global,
            'semaforo' => $semaforo,
            'tiene_datos' => true
        ];
    }

    // ─────────────────────────────────────────────────────────
    // FICHAS TÉCNICAS DE INDICADORES (ZZ_99)
    // ─────────────────────────────────────────────────────────

    /**
     * Obtiene mediciones de un indicador para un año específico,
     * indexadas por periodo.
     */
    public function getMedicionesAnio(int $idIndicador, int $anio): array
    {
        $db = \Config\Database::connect();

        $mediciones = $db->table('tbl_indicadores_sst_mediciones')
            ->where('id_indicador', $idIndicador)
            ->like('periodo', (string)$anio, 'after')
            ->orderBy('periodo', 'ASC')
            ->get()
            ->getResultArray();

        // Indexar por periodo
        $indexadas = [];
        foreach ($mediciones as $m) {
            $indexadas[$m['periodo']] = $m;
        }

        return $indexadas;
    }

    /**
     * Genera los periodos (columnas) según la periodicidad del indicador.
     */
    public static function getPeriodosParaPeriodicidad(string $periodicidad, int $anio): array
    {
        switch ($periodicidad) {
            case 'mensual':
                $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                $periodos = [];
                for ($i = 1; $i <= 12; $i++) {
                    $periodos[] = [
                        'periodo' => $anio . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                        'label'   => $meses[$i - 1],
                    ];
                }
                return $periodos;

            case 'trimestral':
                return [
                    ['periodo' => "{$anio}-Q1", 'label' => 'Trim I'],
                    ['periodo' => "{$anio}-Q2", 'label' => 'Trim II'],
                    ['periodo' => "{$anio}-Q3", 'label' => 'Trim III'],
                    ['periodo' => "{$anio}-Q4", 'label' => 'Trim IV'],
                ];

            case 'semestral':
                return [
                    ['periodo' => "{$anio}-S1", 'label' => 'Sem I'],
                    ['periodo' => "{$anio}-S2", 'label' => 'Sem II'],
                ];

            case 'anual':
                return [
                    ['periodo' => (string)$anio, 'label' => 'Anual'],
                ];

            default:
                return [['periodo' => (string)$anio, 'label' => 'Periodo']];
        }
    }

    /**
     * Obtiene todos los datos necesarios para renderizar una Ficha Técnica.
     */
    public function getDatosFichaTecnica(int $idIndicador, int $anio): ?array
    {
        $indicador = $this->find($idIndicador);
        if (!$indicador) {
            return null;
        }

        $periodicidad = $indicador['periodicidad'] ?? 'trimestral';
        $periodos = self::getPeriodosParaPeriodicidad($periodicidad, $anio);
        $mediciones = $this->getMedicionesAnio($idIndicador, $anio);

        // Calcular acumulados
        $sumNumerador = 0;
        $sumDenominador = 0;
        $sumResultado = 0;
        $contResultados = 0;

        foreach ($periodos as &$p) {
            $m = $mediciones[$p['periodo']] ?? null;
            $p['numerador']    = $m['valor_numerador'] ?? null;
            $p['denominador']  = $m['valor_denominador'] ?? null;
            $p['resultado']    = $m['valor_resultado'] ?? null;
            $p['cumple_meta']  = $m['cumple_meta'] ?? null;

            if ($p['numerador'] !== null) {
                $sumNumerador += (float)$p['numerador'];
            }
            if ($p['denominador'] !== null) {
                $sumDenominador += (float)$p['denominador'];
            }
            if ($p['resultado'] !== null) {
                $sumResultado += (float)$p['resultado'];
                $contResultados++;
            }
        }
        unset($p);

        // Acumulado
        $acumulado = [
            'numerador'   => $sumNumerador > 0 ? $sumNumerador : null,
            'denominador' => $sumDenominador > 0 ? $sumDenominador : null,
            'resultado'   => $contResultados > 0 ? round($sumResultado / $contResultados, 2) : null,
        ];

        return [
            'indicador'  => $indicador,
            'periodos'   => $periodos,
            'acumulado'  => $acumulado,
            'anio'       => $anio,
        ];
    }
}
