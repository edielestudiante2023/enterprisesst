<?php

namespace App\Services;

use App\Models\IndicadorSSTModel;

/**
 * Servicio para generar indicadores de Estilos de Vida Saludable
 * segun Resolucion 0312/2019 - Estandar 3.1.7
 */
class IndicadoresEstilosVidaService
{
    protected IndicadorSSTModel $indicadorModel;

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
    }

    /**
     * Indicadores base de Estilos de Vida Saludable
     */
    public const INDICADORES_ESTILOS_VIDA = [
        [
            'nombre' => 'Cumplimiento de Actividades del Programa de Estilos de Vida Saludable',
            'tipo' => 'proceso',
            'formula' => '(Actividades EVS ejecutadas / Actividades EVS programadas) x 100',
            'meta' => 90,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '3.1.7',
            'descripcion' => 'Mide el cumplimiento de las actividades de promocion de estilos de vida saludables',
            'definicion' => 'Mide el porcentaje de actividades ejecutadas del programa de estilos de vida saludable frente a las programadas, incluyendo campanas de prevencion de tabaquismo, alcoholismo y farmacodependencia.',
            'interpretacion' => 'Un resultado >=90% indica buen cumplimiento. Valores menores requieren reprogramacion de actividades y revision de recursos asignados al programa.',
            'origen_datos' => 'Plan de trabajo anual (actividades EVS), registros de ejecucion, actas',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, trabajadores'
        ],
        [
            'nombre' => 'Cobertura de Participacion en Campanas de Prevencion',
            'tipo' => 'proceso',
            'formula' => '(Trabajadores participantes en campanas / Total trabajadores convocados) x 100',
            'meta' => 80,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '3.1.7',
            'descripcion' => 'Mide la participacion de trabajadores en campanas de tabaquismo, alcoholismo y farmacodependencia',
            'definicion' => 'Mide la proporcion de trabajadores que participan en las campanas de prevencion de consumo de tabaco, alcohol y sustancias psicoactivas.',
            'interpretacion' => 'Un 80% o mas indica buena participacion. Valores menores requieren revision de estrategias de convocatoria y metodologia de las campanas.',
            'origen_datos' => 'Registros de asistencia a campanas, listados de convocatoria, nomina',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, trabajadores'
        ],
        [
            'nombre' => 'Numero de Campanas de Prevencion Realizadas',
            'tipo' => 'proceso',
            'formula' => 'Campanas realizadas en el periodo',
            'meta' => 6,
            'unidad' => 'campanas/ano',
            'periodicidad' => 'semestral',
            'phva' => 'verificar',
            'numeral' => '3.1.7',
            'descripcion' => 'Mide la cantidad de campanas de prevencion de tabaquismo, alcoholismo y farmacodependencia realizadas',
            'definicion' => 'Registra el numero total de campanas de prevencion de tabaquismo, alcoholismo y farmacodependencia realizadas durante el periodo.',
            'interpretacion' => 'La meta de 6 campanas/ano (una bimestral) es el minimo recomendado. Deben cubrir los 3 temas: tabaquismo, alcoholismo y farmacodependencia.',
            'origen_datos' => 'Registros de campanas realizadas, material de divulgacion, actas de ejecucion',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia'
        ],
        [
            'nombre' => 'Variacion en Porcentaje de Fumadores Activos',
            'tipo' => 'resultado',
            'formula' => '((Fumadores periodo actual - Fumadores periodo anterior) / Fumadores periodo anterior) x 100',
            'meta' => -5,
            'unidad' => '%',
            'periodicidad' => 'anual',
            'phva' => 'verificar',
            'numeral' => '3.1.7',
            'descripcion' => 'Mide la reduccion en el porcentaje de fumadores activos entre trabajadores',
            'menor_es_mejor' => true,
            'definicion' => 'Mide la variacion porcentual de fumadores activos entre un periodo y otro, con el objetivo de evidenciar reduccion progresiva del habito de fumar.',
            'interpretacion' => 'Valores negativos indican reduccion (objetivo deseado). La meta de -5% significa reducir al menos 5% los fumadores. Valores positivos indican aumento del habito.',
            'origen_datos' => 'Encuestas de habitos de salud, diagnostico de condiciones de salud, perfil sociodemografico',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, medico ocupacional'
        ],
        [
            'nombre' => 'Tasa de Ausentismo por Enfermedades Cronicas No Transmisibles',
            'tipo' => 'resultado',
            'formula' => '(Dias ausencia por ECNT / Dias laborados programados) x 100',
            'meta' => 3,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '3.1.7',
            'descripcion' => 'Mide el ausentismo atribuible a enfermedades cronicas no transmisibles',
            'menor_es_mejor' => true,
            'definicion' => 'Mide el porcentaje de dias laborales perdidos por enfermedades cronicas no transmisibles (diabetes, hipertension, obesidad, etc.) respecto al total programado.',
            'interpretacion' => 'Valores <=3% son aceptables. Valores superiores requieren fortalecimiento de campanas de estilos de vida saludable y seguimiento medico.',
            'origen_datos' => 'Registros de incapacidades (diagnostico ECNT), nomina, EPS',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, medico ocupacional'
        ],
        [
            'nombre' => 'Satisfaccion de Trabajadores con el Programa',
            'tipo' => 'resultado',
            'formula' => '(Trabajadores satisfechos / Total trabajadores encuestados) x 100',
            'meta' => 80,
            'unidad' => '%',
            'periodicidad' => 'anual',
            'phva' => 'verificar',
            'numeral' => '3.1.7',
            'descripcion' => 'Mide la satisfaccion de los trabajadores con las actividades del programa',
            'definicion' => 'Mide el nivel de satisfaccion de los trabajadores con las actividades del programa de estilos de vida saludable mediante encuestas de percepcion.',
            'interpretacion' => 'Valores >=80% indican buena aceptacion del programa. Valores menores sugieren necesidad de ajustar temas, metodologias o frecuencia de actividades.',
            'origen_datos' => 'Encuestas de satisfaccion, evaluacion de actividades, sugerencias de trabajadores',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia'
        ],
        [
            'nombre' => 'Casos Remitidos a EPS por Consumo de SPA',
            'tipo' => 'resultado',
            'formula' => 'Numero de casos identificados y remitidos a EPS',
            'meta' => 0,
            'unidad' => 'casos',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '3.1.7',
            'descripcion' => 'Registra los casos de consumo de sustancias psicoactivas detectados y canalizados a EPS',
            'definicion' => 'Registra la cantidad de trabajadores identificados con consumo de sustancias psicoactivas (SPA) que fueron canalizados a su EPS para atencion especializada.',
            'interpretacion' => 'Lo ideal es 0 casos. La deteccion y canalizacion oportuna es obligatoria. Un aumento de casos puede indicar mejor deteccion o mayor prevalencia.',
            'origen_datos' => 'Registros de canalizacion a EPS, reportes de deteccion, seguimiento de casos',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional'
        ]
    ];

    /**
     * Obtiene el resumen de indicadores de Estilos de Vida para un cliente
     */
    public function getResumenIndicadores(int $idCliente): array
    {
        $indicadores = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'estilos_vida_saludable')
                ->orLike('nombre_indicador', 'estilos de vida', 'both')
                ->orLike('nombre_indicador', 'tabaquismo', 'both')
                ->orLike('nombre_indicador', 'alcoholismo', 'both')
                ->orLike('nombre_indicador', 'fumadores', 'both')
                ->orLike('nombre_indicador', 'farmacodependencia', 'both')
                ->orLike('nombre_indicador', 'sustancias psicoactivas', 'both')
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
            'sugeridos' => count(self::INDICADORES_ESTILOS_VIDA),
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

        // 1. Agregar indicadores base
        foreach (self::INDICADORES_ESTILOS_VIDA as $idx => $ind) {
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

        // 2. Marcar los que ya existen
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
     * Genera los indicadores de Estilos de Vida Saludable
     */
    public function generarIndicadores(int $idCliente, ?array $indicadoresSeleccionados = null): array
    {
        $creados = 0;
        $existentes = 0;
        $errores = [];

        $indicadores = $indicadoresSeleccionados ?? self::INDICADORES_ESTILOS_VIDA;

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
                    'categoria' => 'estilos_vida_saludable',
                    'formula' => $ind['formula'],
                    'meta' => $ind['meta'],
                    'unidad_medida' => $ind['unidad'],
                    'periodicidad' => $ind['periodicidad'],
                    'phva' => $ind['phva'],
                    'numeral_resolucion' => $ind['numeral'] ?? '3.1.7',
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
     * Obtiene los indicadores de Estilos de Vida de un cliente
     */
    public function getIndicadoresCliente(int $idCliente): array
    {
        return $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'estilos_vida_saludable')
                ->orLike('nombre_indicador', 'estilos de vida', 'both')
                ->orLike('nombre_indicador', 'tabaquismo', 'both')
                ->orLike('nombre_indicador', 'alcoholismo', 'both')
                ->orLike('nombre_indicador', 'fumadores', 'both')
                ->orLike('nombre_indicador', 'farmacodependencia', 'both')
                ->orLike('nombre_indicador', 'sustancias psicoactivas', 'both')
            ->groupEnd()
            ->orderBy('tipo_indicador', 'ASC')
            ->orderBy('nombre_indicador', 'ASC')
            ->findAll();
    }
}
