<?php

namespace App\Services;

use App\Models\IndicadorSSTModel;

/**
 * Servicio para generar Indicadores de Objetivos del SG-SST
 * Estándar 2.2.1 - Resolución 0312/2019
 *
 * PARTE 2 del módulo de 3 partes:
 * - CONSUME los objetivos de Parte 1 (tbl_pta_cliente tipo_servicio='Objetivos SG-SST')
 * - Genera indicadores para medir cumplimiento de objetivos
 * - Se guardan en tbl_indicadores_sst con categoria = 'objetivos_sgsst'
 */
class IndicadoresObjetivosService
{
    protected IndicadorSSTModel $indicadorModel;
    protected ObjetivosSgsstService $objetivosService;

    protected const CATEGORIA = 'objetivos_sgsst';

    /**
     * Límites fijos de indicadores según estándares
     */
    public const LIMITES_INDICADORES = [
        7 => 5,   // Básico: 5 indicadores
        21 => 8,  // Intermedio: 8 indicadores
        60 => 10  // Avanzado: 10 indicadores
    ];

    /**
     * Indicadores base para objetivos del SG-SST
     */
    public const INDICADORES_BASE = [
        [
            'nombre' => 'Indice de Frecuencia de Accidentes de Trabajo',
            'tipo' => 'resultado',
            'formula' => '(Numero de accidentes x 240.000) / Horas hombre trabajadas',
            'meta' => 0,
            'unidad' => 'IF',
            'periodicidad' => 'mensual',
            'phva' => 'verificar',
            'numeral' => '2.2.1',
            'descripcion' => 'Mide la frecuencia de accidentes de trabajo por horas trabajadas',
            'menor_es_mejor' => true,
            'objetivo_relacionado' => 'Reducir la accidentalidad laboral',
            'definicion' => 'Mide la relacion entre el numero de accidentes de trabajo ocurridos y las horas hombre trabajadas durante un periodo, expresado por cada 240.000 HHT.',
            'interpretacion' => 'A menor valor, menor frecuencia de accidentalidad. Un IF=0 indica cero accidentes. Valores crecientes requieren investigacion y acciones correctivas inmediatas.',
            'origen_datos' => 'Registros FURAT, reportes de accidentes de trabajo, nomina (horas trabajadas)',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL'
        ],
        [
            'nombre' => 'Indice de Severidad de Accidentes de Trabajo',
            'tipo' => 'resultado',
            'formula' => '(Dias perdidos por AT x 240.000) / Horas hombre trabajadas',
            'meta' => 0,
            'unidad' => 'IS',
            'periodicidad' => 'mensual',
            'phva' => 'verificar',
            'numeral' => '2.2.1',
            'descripcion' => 'Mide la severidad de accidentes de trabajo en dias perdidos',
            'menor_es_mejor' => true,
            'objetivo_relacionado' => 'Reducir la accidentalidad laboral',
            'definicion' => 'Mide la gravedad de los accidentes de trabajo ocurridos, relacionando los dias de incapacidad generados con las horas hombre trabajadas.',
            'interpretacion' => 'A menor valor, menor severidad de los accidentes. Un IS=0 indica cero dias perdidos. Valores altos indican accidentes graves que requieren intervencion prioritaria.',
            'origen_datos' => 'Registros FURAT, incapacidades por AT, nomina (horas trabajadas)',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL'
        ],
        [
            'nombre' => 'Tasa de Incidencia de Enfermedad Laboral',
            'tipo' => 'resultado',
            'formula' => '(Casos nuevos de enfermedad laboral / Promedio trabajadores) x 100.000',
            'meta' => 0,
            'unidad' => 'x100mil',
            'periodicidad' => 'anual',
            'phva' => 'verificar',
            'numeral' => '2.2.1',
            'descripcion' => 'Mide la aparicion de nuevos casos de enfermedad laboral',
            'menor_es_mejor' => true,
            'objetivo_relacionado' => 'Prevenir enfermedades laborales',
            'definicion' => 'Mide la proporcion de nuevos casos de enfermedad calificada como laboral respecto al promedio de trabajadores en el periodo.',
            'interpretacion' => 'A menor valor, mejor gestion preventiva. Un valor de 0 indica que no se presentaron nuevos casos de enfermedad laboral en el periodo.',
            'origen_datos' => 'Calificaciones de origen EPS/ARL, registros de enfermedad laboral, nomina promedio',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL, medico ocupacional'
        ],
        [
            'nombre' => 'Porcentaje de Cumplimiento de Estandares Minimos',
            'tipo' => 'proceso',
            'formula' => '(Estandares cumplidos / Total estandares aplicables) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'anual',
            'phva' => 'verificar',
            'numeral' => '2.2.1',
            'descripcion' => 'Mide el cumplimiento de la Resolucion 0312/2019',
            'objetivo_relacionado' => 'Cumplir los requisitos legales en SST',
            'definicion' => 'Mide el porcentaje de estandares minimos de la Resolucion 0312/2019 que la empresa cumple segun su clasificacion de riesgo y numero de trabajadores.',
            'interpretacion' => 'El 100% indica cumplimiento total. Valores >=85% son aceptables, entre 60-85% requieren plan de mejora, <60% estado critico.',
            'origen_datos' => 'Autoevaluacion de estandares minimos Res. 0312/2019, plan de mejora',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL'
        ],
        [
            'nombre' => 'Cobertura de Capacitacion en SST',
            'tipo' => 'proceso',
            'formula' => '(Trabajadores capacitados / Total trabajadores) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '2.2.1',
            'descripcion' => 'Mide el porcentaje de trabajadores capacitados en SST',
            'objetivo_relacionado' => 'Fortalecer la cultura de autocuidado',
            'definicion' => 'Mide la proporcion de trabajadores que han recibido capacitacion en temas de SST respecto al total de la poblacion trabajadora.',
            'interpretacion' => 'El 100% indica que todos los trabajadores han sido capacitados. Valores menores requieren refuerzo en cobertura de formacion.',
            'origen_datos' => 'Registros de asistencia a capacitaciones, cronograma de capacitacion, nomina',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, trabajadores'
        ],
        [
            'nombre' => 'Cumplimiento del Plan de Trabajo Anual',
            'tipo' => 'proceso',
            'formula' => '(Actividades ejecutadas / Actividades programadas) x 100',
            'meta' => 90,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '2.2.1',
            'descripcion' => 'Mide la ejecucion de las actividades del plan de trabajo SST',
            'definicion' => 'Mide el porcentaje de actividades ejecutadas del Plan de Trabajo Anual del SG-SST frente a las actividades programadas para el periodo.',
            'interpretacion' => 'Un resultado >=90% indica buen cumplimiento. Valores menores requieren reprogramacion y analisis de causas de incumplimiento.',
            'origen_datos' => 'Plan de Trabajo Anual, cronograma de actividades, actas de ejecucion',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia'
        ],
        [
            'nombre' => 'Porcentaje de Peligros Intervenidos',
            'tipo' => 'proceso',
            'formula' => '(Peligros con control implementado / Total peligros prioritarios) x 100',
            'meta' => 80,
            'unidad' => '%',
            'periodicidad' => 'semestral',
            'phva' => 'verificar',
            'numeral' => '2.2.1',
            'descripcion' => 'Mide la gestion de peligros identificados en la matriz',
            'objetivo_relacionado' => 'Gestionar eficazmente los peligros identificados',
            'definicion' => 'Mide la proporcion de peligros prioritarios identificados en la Matriz IPVR que cuentan con medidas de control implementadas.',
            'interpretacion' => 'A mayor porcentaje, mejor gestion de peligros. Un 80% o superior indica buena intervencion. Priorizar peligros con nivel de riesgo alto e inaceptable.',
            'origen_datos' => 'Matriz de identificacion de peligros y valoracion de riesgos (IPVR), registros de controles',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, trabajadores'
        ],
        [
            'nombre' => 'Participacion en Simulacros de Emergencia',
            'tipo' => 'proceso',
            'formula' => '(Trabajadores participantes / Total trabajadores convocados) x 100',
            'meta' => 90,
            'unidad' => '%',
            'periodicidad' => 'semestral',
            'phva' => 'verificar',
            'numeral' => '2.2.1',
            'descripcion' => 'Mide la participacion del personal en simulacros',
            'objetivo_relacionado' => 'Mejorar la respuesta ante emergencias',
            'definicion' => 'Mide el porcentaje de trabajadores que participan activamente en los simulacros de emergencia programados.',
            'interpretacion' => 'Un 90% o mas indica participacion adecuada. Valores menores requieren refuerzo en convocatoria y sensibilizacion sobre preparacion ante emergencias.',
            'origen_datos' => 'Registros de asistencia a simulacros, plan de emergencias, informe post-simulacro',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, brigada de emergencias'
        ],
        [
            'nombre' => 'Indice de Lesiones Incapacitantes',
            'tipo' => 'resultado',
            'formula' => '(IF x IS) / 1000',
            'meta' => 0,
            'unidad' => 'ILI',
            'periodicidad' => 'mensual',
            'phva' => 'verificar',
            'numeral' => '2.2.1',
            'descripcion' => 'Indicador combinado de frecuencia y severidad',
            'menor_es_mejor' => true,
            'definicion' => 'Indicador combinado que relaciona la frecuencia y la severidad de los accidentes de trabajo. Resulta de multiplicar IF por IS y dividir entre 1000.',
            'interpretacion' => 'A menor valor, menor impacto de la accidentalidad. Un ILI=0 indica cero accidentes o cero dias perdidos. Valores crecientes indican deterioro en seguridad.',
            'origen_datos' => 'Calculado a partir del Indice de Frecuencia (IF) y el Indice de Severidad (IS)',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL'
        ],
        [
            'nombre' => 'Investigacion de Incidentes y Accidentes',
            'tipo' => 'proceso',
            'formula' => '(Incidentes/Accidentes investigados / Total ocurridos) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'mensual',
            'phva' => 'verificar',
            'numeral' => '2.2.1',
            'descripcion' => 'Mide el cumplimiento de investigacion de eventos',
            'definicion' => 'Mide el porcentaje de incidentes y accidentes de trabajo que fueron investigados dentro de los 15 dias calendario siguientes a su ocurrencia.',
            'interpretacion' => 'El 100% indica que todos los eventos fueron investigados oportunamente. Es obligatorio investigar todos los accidentes graves y mortales (Res. 1401/2007).',
            'origen_datos' => 'Informes de investigacion de accidentes, FURAT, registros de incidentes',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL, trabajadores'
        ]
    ];

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
        $this->objetivosService = new ObjetivosSgsstService();
    }

    /**
     * Obtiene el límite de indicadores según estándares del cliente
     */
    public function getLimiteIndicadores(int $estandares): int
    {
        if ($estandares <= 7) return self::LIMITES_INDICADORES[7];
        if ($estandares <= 21) return self::LIMITES_INDICADORES[21];
        return self::LIMITES_INDICADORES[60];
    }

    /**
     * Obtiene el resumen de indicadores de objetivos para un cliente
     */
    public function getResumenIndicadores(int $idCliente): array
    {
        $indicadores = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->where('categoria', self::CATEGORIA)
            ->findAll();

        $total = count($indicadores);
        $medidos = 0;
        $cumplen = 0;

        foreach ($indicadores as $ind) {
            if ($ind['cumple_meta'] !== null) {
                $medidos++;
                if ($ind['cumple_meta'] == 1) {
                    $cumplen++;
                }
            }
        }

        return [
            'existentes' => $total,
            'sugeridos' => count(self::INDICADORES_BASE),
            'medidos' => $medidos,
            'cumplen' => $cumplen,
            'completo' => $total >= 3,
            'minimo' => 3
        ];
    }

    /**
     * VALIDACIÓN OBLIGATORIA: Verificar que existan objetivos de Parte 1
     */
    public function verificarObjetivosPrevios(int $idCliente, int $anio): array
    {
        $objetivos = $this->objetivosService->getObjetivosCliente($idCliente, $anio);

        return [
            'tiene_objetivos' => count($objetivos) > 0,
            'total_objetivos' => count($objetivos),
            'objetivos' => $objetivos,
            'mensaje' => count($objetivos) > 0
                ? 'Objetivos encontrados para asociar indicadores'
                : 'Debe completar la Parte 1 (Objetivos) antes de generar indicadores'
        ];
    }

    /**
     * Preview de indicadores que se generarían
     * CONSUME los objetivos de Parte 1 para sugerir indicadores relevantes
     */
    public function previewIndicadores(int $idCliente, int $anio, ?array $contexto = null): array
    {
        // VALIDACIÓN: Verificar objetivos previos
        $verificacion = $this->verificarObjetivosPrevios($idCliente, $anio);
        if (!$verificacion['tiene_objetivos']) {
            return [
                'indicadores' => [],
                'total' => 0,
                'error' => true,
                'mensaje' => $verificacion['mensaje']
            ];
        }

        $estandares = $contexto['estandares_aplicables'] ?? 60;
        $limite = $this->getLimiteIndicadores($estandares);

        // Obtener objetivos existentes para mapear indicadores
        $objetivosCliente = $verificacion['objetivos'];
        $objetivosTexto = array_map(function($obj) {
            return $obj['actividad_plandetrabajo'];
        }, $objetivosCliente);

        // Tomar indicadores base hasta el límite
        $indicadoresBase = array_slice(self::INDICADORES_BASE, 0, $limite);

        $indicadores = [];
        foreach ($indicadoresBase as $idx => $ind) {
            // Buscar si hay un objetivo relacionado
            $objetivoAsociado = '';
            if (!empty($ind['objetivo_relacionado'])) {
                foreach ($objetivosTexto as $objTexto) {
                    if (stripos($objTexto, substr($ind['objetivo_relacionado'], 0, 20)) !== false) {
                        $objetivoAsociado = $objTexto;
                        break;
                    }
                }
            }

            $indicadores[] = [
                'indice' => $idx,
                'nombre' => $ind['nombre'],
                'tipo' => $ind['tipo'],
                'formula' => $ind['formula'],
                'meta' => $ind['meta'],
                'unidad' => $ind['unidad'],
                'periodicidad' => $ind['periodicidad'],
                'phva' => $ind['phva'],
                'numeral' => $ind['numeral'],
                'descripcion' => $ind['descripcion'] ?? '',
                'menor_es_mejor' => $ind['menor_es_mejor'] ?? false,
                'objetivo_relacionado' => $ind['objetivo_relacionado'] ?? '',
                'objetivo_asociado' => $objetivoAsociado,
                'origen' => 'base',
                'seleccionado' => true
            ];
        }

        // Marcar los que ya existen
        $existentes = $this->getIndicadoresCliente($idCliente);
        $nombresExistentes = array_map('strtolower', array_column($existentes, 'nombre_indicador'));

        foreach ($indicadores as &$ind) {
            $nombreLower = strtolower($ind['nombre']);
            foreach ($nombresExistentes as $existente) {
                if (similar_text($nombreLower, $existente) > strlen($nombreLower) * 0.6) {
                    $ind['ya_existe'] = true;
                    $ind['seleccionado'] = false;
                    break;
                }
            }
        }

        return [
            'indicadores' => $indicadores,
            'total' => count($indicadores),
            'limite' => $limite,
            'estandares' => $estandares,
            'objetivos_base' => count($objetivosCliente),
            'contexto_aplicado' => $contexto ? true : false
        ];
    }

    /**
     * Genera los indicadores de objetivos en la BD
     */
    public function generarIndicadores(int $idCliente, int $anio, ?array $indicadoresSeleccionados = null): array
    {
        // VALIDACIÓN: Verificar objetivos previos
        $verificacion = $this->verificarObjetivosPrevios($idCliente, $anio);
        if (!$verificacion['tiene_objetivos']) {
            return [
                'creados' => 0,
                'existentes' => 0,
                'errores' => [$verificacion['mensaje']],
                'total' => 0
            ];
        }

        $creados = 0;
        $existentes = 0;
        $errores = [];

        $indicadores = $indicadoresSeleccionados ?? self::INDICADORES_BASE;

        foreach ($indicadores as $ind) {
            // Verificar si ya existe un indicador similar
            $existe = $this->indicadorModel
                ->where('id_cliente', $idCliente)
                ->where('activo', 1)
                ->like('nombre_indicador', substr($ind['nombre'], 0, 30), 'both')
                ->countAllResults();

            if ($existe > 0) {
                $existentes++;
                continue;
            }

            try {
                $this->indicadorModel->insert([
                    'id_cliente' => $idCliente,
                    'nombre_indicador' => $ind['nombre'],
                    'tipo_indicador' => $ind['tipo'],
                    'categoria' => self::CATEGORIA,
                    'formula' => $ind['formula'],
                    'meta' => $ind['meta'],
                    'unidad_medida' => $ind['unidad'],
                    'periodicidad' => $ind['periodicidad'],
                    'phva' => $ind['phva'],
                    'numeral_resolucion' => $ind['numeral'] ?? '2.2.1',
                    'definicion' => $ind['definicion'] ?? null,
                    'interpretacion' => $ind['interpretacion'] ?? null,
                    'origen_datos' => $ind['origen_datos'] ?? null,
                    'cargo_responsable' => $ind['cargo_responsable'] ?? null,
                    'cargos_conocer_resultado' => $ind['cargos_conocer_resultado'] ?? null,
                    'activo' => 1
                ]);
                $creados++;
            } catch (\Exception $e) {
                $errores[] = "Error en '{$ind['nombre']}': " . $e->getMessage();
            }
        }

        return [
            'creados' => $creados,
            'existentes' => $existentes,
            'errores' => $errores,
            'total' => count($indicadores)
        ];
    }

    /**
     * Obtiene los indicadores de objetivos de un cliente
     */
    public function getIndicadoresCliente(int $idCliente): array
    {
        return $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->where('categoria', self::CATEGORIA)
            ->orderBy('tipo_indicador', 'ASC')
            ->orderBy('nombre_indicador', 'ASC')
            ->findAll();
    }

    /**
     * Obtiene indicadores formateados para el contexto del documento (Parte 3)
     */
    public function getIndicadoresParaContexto(int $idCliente): string
    {
        $indicadores = $this->getIndicadoresCliente($idCliente);

        if (empty($indicadores)) {
            return "No hay indicadores configurados para los objetivos del SG-SST.";
        }

        $texto = "Total: " . count($indicadores) . " indicadores\n\n";

        $porTipo = ['resultado' => [], 'proceso' => [], 'estructura' => []];
        foreach ($indicadores as $ind) {
            $tipo = $ind['tipo_indicador'] ?? 'proceso';
            $porTipo[$tipo][] = $ind;
        }

        foreach ($porTipo as $tipo => $inds) {
            if (!empty($inds)) {
                $texto .= strtoupper("INDICADORES DE " . $tipo) . ":\n";
                foreach ($inds as $i => $ind) {
                    $texto .= ($i + 1) . ". {$ind['nombre_indicador']}\n";
                    $texto .= "   - Formula: {$ind['formula']}\n";
                    $texto .= "   - Meta: {$ind['meta']} {$ind['unidad_medida']}\n";
                    $texto .= "   - Periodicidad: {$ind['periodicidad']}\n\n";
                }
            }
        }

        return $texto;
    }
}
