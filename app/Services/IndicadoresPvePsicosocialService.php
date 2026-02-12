<?php

namespace App\Services;

use App\Models\IndicadorSSTModel;

/**
 * Servicio para generar indicadores del PVE de Riesgo Psicosocial
 * segun Resolucion 0312/2019 - Estandar 4.2.3
 */
class IndicadoresPvePsicosocialService
{
    protected IndicadorSSTModel $indicadorModel;

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
    }

    /**
     * Indicadores base del PVE de Riesgo Psicosocial
     */
    public const INDICADORES_PVE_PSICOSOCIAL = [
        [
            'nombre' => 'Cumplimiento de Actividades del PVE Psicosocial',
            'tipo' => 'proceso',
            'formula' => '(Actividades PVE Psicosocial ejecutadas / Actividades PVE Psicosocial programadas) x 100',
            'meta' => 90,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '4.2.3',
            'descripcion' => 'Mide el cumplimiento de las actividades del programa de vigilancia epidemiologica de riesgo psicosocial',
            'definicion' => 'Mide el porcentaje de actividades ejecutadas del Programa de Vigilancia Epidemiologica de riesgo psicosocial frente a las programadas en el cronograma.',
            'interpretacion' => 'Un resultado >=90% indica buen cumplimiento. Valores menores requieren revision de recursos y prioridades del PVE psicosocial.',
            'origen_datos' => 'Cronograma PVE psicosocial, registros de actividades ejecutadas, actas, informes',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, psicologo especialista'
        ],
        [
            'nombre' => 'Cobertura de Aplicacion de Bateria de Riesgo Psicosocial',
            'tipo' => 'proceso',
            'formula' => '(Trabajadores evaluados con bateria / Total trabajadores de la empresa) x 100',
            'meta' => 90,
            'unidad' => '%',
            'periodicidad' => 'anual',
            'phva' => 'verificar',
            'numeral' => '4.2.3',
            'descripcion' => 'Mide la cobertura de aplicacion de la bateria de riesgo psicosocial (Res. 2764/2022)',
            'definicion' => 'Mide la proporcion de trabajadores a quienes se les aplico la Bateria de Instrumentos de Evaluacion de Riesgo Psicosocial segun Res. 2764/2022.',
            'interpretacion' => 'Un 90% o mas indica buena cobertura. Es obligatorio aplicar la bateria cada 2 anos (riesgo alto) o 3 anos (bajo/medio). Solo puede aplicarla un psicologo especialista.',
            'origen_datos' => 'Informe de bateria de riesgo psicosocial, listado de trabajadores evaluados, psicologo especialista',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, psicologo especialista, Comite Convivencia'
        ],
        [
            'nombre' => 'Proporcion de Trabajadores en Riesgo Alto o Muy Alto',
            'tipo' => 'resultado',
            'formula' => '(Trabajadores en nivel alto + muy alto / Total trabajadores evaluados) x 100',
            'meta' => 15,
            'unidad' => '%',
            'periodicidad' => 'anual',
            'phva' => 'verificar',
            'numeral' => '4.2.3',
            'descripcion' => 'Mide la proporcion de trabajadores clasificados en riesgo psicosocial alto o muy alto',
            'menor_es_mejor' => true,
            'definicion' => 'Mide la proporcion de trabajadores clasificados en nivel de riesgo psicosocial alto o muy alto segun los resultados de la bateria de riesgo psicosocial.',
            'interpretacion' => 'Valores <=15% son aceptables. Valores superiores requieren intervencion prioritaria con programa especifico. Nivel muy alto exige intervencion inmediata (Res. 2764/2022).',
            'origen_datos' => 'Resultados de bateria de riesgo psicosocial, informe del psicologo especialista',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, psicologo especialista, ARL'
        ],
        [
            'nombre' => 'Cobertura de Participacion en Talleres de Intervencion',
            'tipo' => 'proceso',
            'formula' => '(Trabajadores participantes en talleres / Total trabajadores convocados) x 100',
            'meta' => 80,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '4.2.3',
            'descripcion' => 'Mide la participacion de trabajadores en talleres de intervencion psicosocial',
            'definicion' => 'Mide la proporcion de trabajadores que participan en talleres y actividades de intervencion psicosocial (manejo del estres, comunicacion, liderazgo, etc.).',
            'interpretacion' => 'Un 80% o mas indica buena participacion. Priorizar la participacion de trabajadores en nivel de riesgo alto y muy alto.',
            'origen_datos' => 'Registros de asistencia a talleres, listados de convocatoria, informes de actividades',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, psicologo especialista, COPASST/Vigia'
        ],
        [
            'nombre' => 'Tasa de Ausentismo por Estres Laboral',
            'tipo' => 'resultado',
            'formula' => '(Dias de ausencia por estres laboral / Total dias laborados programados) x 100',
            'meta' => 2,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '4.2.3',
            'descripcion' => 'Mide el ausentismo laboral atribuible a factores de estres y riesgo psicosocial',
            'menor_es_mejor' => true,
            'definicion' => 'Mide el porcentaje de dias laborales perdidos por incapacidades relacionadas con estres laboral, ansiedad, depresion y otros trastornos psicosociales.',
            'interpretacion' => 'Valores <=2% son aceptables. Valores superiores indican alto impacto del riesgo psicosocial y requieren revision de condiciones laborales.',
            'origen_datos' => 'Registros de incapacidades (diagnostico CIE-10 relacionado con estres), nomina, EPS',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, psicologo especialista'
        ],
        [
            'nombre' => 'Efectividad de Intervenciones Psicosociales',
            'tipo' => 'resultado',
            'formula' => '(Trabajadores que mejoraron nivel de riesgo / Total trabajadores intervenidos) x 100',
            'meta' => 60,
            'unidad' => '%',
            'periodicidad' => 'anual',
            'phva' => 'verificar',
            'numeral' => '4.2.3',
            'descripcion' => 'Mide la efectividad de las intervenciones midiendo la reduccion del nivel de riesgo psicosocial',
            'definicion' => 'Mide la proporcion de trabajadores intervenidos que lograron reducir su nivel de riesgo psicosocial en la siguiente aplicacion de la bateria.',
            'interpretacion' => 'Valores >=60% indican buena efectividad del programa de intervencion. Se compara nivel de riesgo entre dos aplicaciones consecutivas de la bateria.',
            'origen_datos' => 'Comparativo de baterias (aplicacion anterior vs actual), informes del psicologo especialista',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, psicologo especialista, ARL'
        ],
        [
            'nombre' => 'Quejas por Acoso Laboral ante Comite de Convivencia',
            'tipo' => 'resultado',
            'formula' => 'Numero de quejas recibidas por acoso laboral en el periodo',
            'meta' => 0,
            'unidad' => 'quejas',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '4.2.3',
            'descripcion' => 'Registra las quejas formales de acoso laboral presentadas ante el Comite de Convivencia Laboral',
            'menor_es_mejor' => true,
            'definicion' => 'Registra la cantidad de quejas formales por presunto acoso laboral recibidas por el Comite de Convivencia Laboral segun Ley 1010/2006.',
            'interpretacion' => 'Lo ideal es 0 quejas. Un aumento puede indicar deterioro del clima laboral o mayor confianza en los canales de denuncia. Toda queja debe tramitarse en maximo 10 dias.',
            'origen_datos' => 'Actas del Comite de Convivencia Laboral, registro de quejas, seguimiento de casos',
            'cargo_responsable' => 'Presidente del Comite de Convivencia Laboral',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Comite de Convivencia Laboral'
        ]
    ];

    /**
     * Obtiene el resumen de indicadores del PVE Psicosocial para un cliente
     */
    public function getResumenIndicadores(int $idCliente): array
    {
        $indicadores = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'pve_psicosocial')
                ->orLike('nombre_indicador', 'psicosocial', 'both')
                ->orLike('nombre_indicador', 'estres laboral', 'both')
                ->orLike('nombre_indicador', 'bateria', 'both')
                ->orLike('nombre_indicador', 'acoso laboral', 'both')
            ->groupEnd()
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
            'sugeridos' => count(self::INDICADORES_PVE_PSICOSOCIAL),
            'medidos' => $medidos,
            'cumplen' => $cumplen,
            'completo' => $total >= 3,
            'minimo' => 3
        ];
    }

    /**
     * Preview de indicadores que se generarian
     */
    public function previewIndicadores(int $idCliente, ?array $contexto = null): array
    {
        $indicadores = [];

        foreach (self::INDICADORES_PVE_PSICOSOCIAL as $idx => $ind) {
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
                'origen' => 'base',
                'seleccionado' => true
            ];
        }

        $existentes = $this->indicadorModel->getByCliente($idCliente);
        $nombresExistentes = array_map('strtolower', array_column($existentes, 'nombre_indicador'));

        foreach ($indicadores as &$ind) {
            $nombreLower = strtolower($ind['nombre']);
            foreach ($nombresExistentes as $existente) {
                if (similar_text($nombreLower, $existente) > strlen($nombreLower) * 0.7) {
                    $ind['ya_existe'] = true;
                    $ind['seleccionado'] = false;
                    break;
                }
            }
        }

        return [
            'indicadores' => $indicadores,
            'total' => count($indicadores),
            'contexto_aplicado' => $contexto ? true : false
        ];
    }

    /**
     * Genera los indicadores del PVE Psicosocial
     */
    public function generarIndicadores(int $idCliente, ?array $indicadoresSeleccionados = null): array
    {
        $creados = 0;
        $existentes = 0;
        $errores = [];

        $indicadores = $indicadoresSeleccionados ?? self::INDICADORES_PVE_PSICOSOCIAL;

        foreach ($indicadores as $ind) {
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
                    'categoria' => 'pve_psicosocial',
                    'formula' => $ind['formula'],
                    'meta' => $ind['meta'],
                    'unidad_medida' => $ind['unidad'],
                    'periodicidad' => $ind['periodicidad'],
                    'phva' => $ind['phva'],
                    'numeral_resolucion' => $ind['numeral'] ?? '4.2.3',
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
     * Obtiene los indicadores del PVE Psicosocial de un cliente
     */
    public function getIndicadoresCliente(int $idCliente): array
    {
        return $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'pve_psicosocial')
                ->orLike('nombre_indicador', 'psicosocial', 'both')
                ->orLike('nombre_indicador', 'estres laboral', 'both')
                ->orLike('nombre_indicador', 'bateria', 'both')
                ->orLike('nombre_indicador', 'acoso laboral', 'both')
            ->groupEnd()
            ->orderBy('tipo_indicador', 'ASC')
            ->orderBy('nombre_indicador', 'ASC')
            ->findAll();
    }
}
