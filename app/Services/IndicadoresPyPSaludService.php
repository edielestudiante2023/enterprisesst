<?php

namespace App\Services;

use App\Models\IndicadorSSTModel;

/**
 * Servicio para generar indicadores de Promocion y Prevencion en Salud
 * segun Resolucion 0312/2019 - Estandar 3.1.2
 */
class IndicadoresPyPSaludService
{
    protected IndicadorSSTModel $indicadorModel;

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
    }

    /**
     * Indicadores base de PyP Salud para cualquier empresa
     */
    public const INDICADORES_PYP_SALUD = [
        [
            'nombre' => 'Cobertura de Examenes Medicos Ocupacionales',
            'tipo' => 'proceso',
            'formula' => '(Trabajadores con examen medico vigente / Total trabajadores) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'anual',
            'phva' => 'verificar',
            'numeral' => '3.1.4',
            'descripcion' => 'Mide el porcentaje de trabajadores con examenes medicos ocupacionales al dia',
            'definicion' => 'Mide la proporcion de trabajadores que cuentan con evaluaciones medicas ocupacionales vigentes (ingreso, periodicas, egreso) segun Res. 2346/2007.',
            'interpretacion' => 'El 100% indica cobertura total. Es obligatorio para todos los trabajadores. Valores menores requieren programacion inmediata de examenes pendientes.',
            'origen_datos' => 'Profesiograma, registro de evaluaciones medicas ocupacionales, IPS contratada',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, medico ocupacional'
        ],
        [
            'nombre' => 'Cumplimiento de Actividades de PyP Salud',
            'tipo' => 'proceso',
            'formula' => '(Actividades PyP ejecutadas / Actividades PyP programadas) x 100',
            'meta' => 90,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '3.1.2',
            'descripcion' => 'Mide el cumplimiento de las actividades de promocion y prevencion en salud',
            'definicion' => 'Mide el porcentaje de actividades de promocion y prevencion en salud ejecutadas frente a las programadas en el plan anual.',
            'interpretacion' => 'Un resultado >=90% indica buen cumplimiento del programa. Valores menores requieren revision de la programacion y recursos asignados.',
            'origen_datos' => 'Plan de trabajo anual (actividades PyP), registros de ejecucion, actas',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, medico ocupacional'
        ],
        [
            'nombre' => 'Tasa de Incidencia de Enfermedad Laboral',
            'tipo' => 'resultado',
            'formula' => '(Casos nuevos de enfermedad laboral / Promedio trabajadores) x 100.000',
            'meta' => 0,
            'unidad' => 'x100mil',
            'periodicidad' => 'anual',
            'phva' => 'verificar',
            'numeral' => '3.1.6',
            'descripcion' => 'Mide la aparicion de nuevos casos de enfermedad laboral',
            'menor_es_mejor' => true,
            'definicion' => 'Mide la proporcion de nuevos casos de enfermedad calificada como laboral en el periodo respecto al promedio de trabajadores expuestos.',
            'interpretacion' => 'A menor valor, mejor gestion preventiva. Un valor de 0 indica ausencia de nuevos casos. Valores crecientes requieren revision de PVE y controles.',
            'origen_datos' => 'Calificaciones EPS/ARL, certificados de enfermedad laboral, nomina promedio',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL, medico ocupacional'
        ],
        [
            'nombre' => 'Prevalencia de Enfermedad Laboral',
            'tipo' => 'resultado',
            'formula' => '(Total casos enfermedad laboral / Promedio trabajadores) x 100.000',
            'meta' => 0,
            'unidad' => 'x100mil',
            'periodicidad' => 'anual',
            'phva' => 'verificar',
            'numeral' => '3.1.6',
            'descripcion' => 'Mide el total de casos de enfermedad laboral en la poblacion',
            'menor_es_mejor' => true,
            'definicion' => 'Mide la proporcion total de casos de enfermedad laboral (nuevos y existentes) en la poblacion trabajadora durante el periodo.',
            'interpretacion' => 'A menor valor, menor carga de enfermedad laboral. Un valor de 0 indica ausencia de casos. Valores altos requieren fortalecimiento de programas de prevencion.',
            'origen_datos' => 'Registro acumulado de enfermedades laborales calificadas, nomina promedio, ARL',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL, medico ocupacional'
        ],
        [
            'nombre' => 'Tasa de Ausentismo por Causa Medica',
            'tipo' => 'resultado',
            'formula' => '(Dias de ausencia por causa medica / Dias laborados programados) x 100',
            'meta' => 3,
            'unidad' => '%',
            'periodicidad' => 'mensual',
            'phva' => 'verificar',
            'numeral' => '3.1.7',
            'descripcion' => 'Mide el porcentaje de ausentismo por incapacidades medicas',
            'menor_es_mejor' => true,
            'definicion' => 'Mide el porcentaje de dias laborales perdidos por incapacidades medicas (enfermedad comun, laboral y accidentes) respecto al total de dias programados.',
            'interpretacion' => 'Valores <=3% son aceptables. Valores superiores requieren analisis de causas principales de ausentismo y medidas preventivas.',
            'origen_datos' => 'Registros de incapacidades, nomina (dias programados), EPS, ARL',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, COPASST/Vigia'
        ],
        [
            'nombre' => 'Participacion en Actividades de Promocion de Salud',
            'tipo' => 'proceso',
            'formula' => '(Trabajadores participantes / Total trabajadores convocados) x 100',
            'meta' => 80,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '3.1.2',
            'descripcion' => 'Mide la participacion de trabajadores en campanas y actividades de promocion',
            'definicion' => 'Mide el porcentaje de trabajadores que participan en las campanas y actividades de promocion de salud y prevencion de enfermedad.',
            'interpretacion' => 'Un 80% o mas indica buena participacion. Valores menores requieren revision de estrategias de convocatoria y horarios de actividades.',
            'origen_datos' => 'Registros de asistencia a actividades PyP, listados de convocatoria, nomina',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, trabajadores'
        ],
        [
            'nombre' => 'Seguimiento a Casos de Salud con Restricciones',
            'tipo' => 'proceso',
            'formula' => '(Casos con seguimiento / Total casos con restricciones) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '3.1.5',
            'descripcion' => 'Mide el seguimiento a trabajadores con recomendaciones o restricciones medicas',
            'definicion' => 'Mide el porcentaje de trabajadores con restricciones o recomendaciones medico-laborales que reciben seguimiento periodico por parte de la empresa.',
            'interpretacion' => 'El 100% indica que todos los casos con restricciones tienen seguimiento activo. Es obligacion legal dar cumplimiento a las recomendaciones medicas.',
            'origen_datos' => 'Certificados de aptitud laboral, registro de seguimiento a restricciones, medico ocupacional',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional, jefes inmediatos'
        ]
    ];

    /**
     * Indicadores adicionales segun peligros identificados
     */
    public const INDICADORES_POR_PELIGRO = [
        'Psicosocial' => [
            [
                'nombre' => 'Cobertura de Evaluacion de Riesgo Psicosocial',
                'tipo' => 'proceso',
                'formula' => '(Trabajadores evaluados riesgo psicosocial / Total trabajadores) x 100',
                'meta' => 100,
                'unidad' => '%',
                'periodicidad' => 'anual',
                'phva' => 'verificar',
                'numeral' => '3.1.2',
                'definicion' => 'Mide la cobertura de aplicacion de la bateria de riesgo psicosocial (Res. 2764/2022) en la poblacion trabajadora.',
                'interpretacion' => 'El 100% indica evaluacion completa. Es obligatorio aplicar la bateria cada 2 anos (riesgo alto) o cada 3 anos (riesgo bajo/medio).',
                'origen_datos' => 'Resultados de bateria de riesgo psicosocial, informe del psicologo especialista',
                'cargo_responsable' => 'Responsable del SG-SST',
                'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, psicologo especialista'
            ]
        ],
        'Biomecanico' => [
            [
                'nombre' => 'Incidencia de Desordenes Musculoesqueleticos',
                'tipo' => 'resultado',
                'formula' => '(Casos nuevos DME / Promedio trabajadores expuestos) x 100',
                'meta' => 5,
                'unidad' => '%',
                'periodicidad' => 'anual',
                'phva' => 'verificar',
                'numeral' => '3.1.6',
                'menor_es_mejor' => true,
                'definicion' => 'Mide la incidencia de nuevos casos de desordenes musculoesqueleticos (DME) en trabajadores expuestos a riesgo biomecanico.',
                'interpretacion' => 'A menor valor, mejor gestion del riesgo biomecanico. Valores <=5% son aceptables. Valores superiores requieren revision de controles ergonomicos.',
                'origen_datos' => 'Evaluaciones medicas ocupacionales, encuestas de morbilidad sentida, diagnosticos DME',
                'cargo_responsable' => 'Responsable del SG-SST',
                'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional, fisioterapeuta'
            ]
        ],
        'Quimico' => [
            [
                'nombre' => 'Cobertura de Examenes por Exposicion Quimica',
                'tipo' => 'proceso',
                'formula' => '(Trabajadores con examen especifico / Total expuestos) x 100',
                'meta' => 100,
                'unidad' => '%',
                'periodicidad' => 'anual',
                'phva' => 'verificar',
                'numeral' => '3.1.4',
                'definicion' => 'Mide la cobertura de examenes medicos especificos para trabajadores expuestos a agentes quimicos segun el profesiograma.',
                'interpretacion' => 'El 100% indica que todos los expuestos tienen examen especifico vigente. Es obligatorio segun la exposicion identificada en la Matriz IPVR.',
                'origen_datos' => 'Profesiograma, registro de evaluaciones medicas, matriz IPVR (peligro quimico)',
                'cargo_responsable' => 'Responsable del SG-SST',
                'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional, higienista industrial'
            ]
        ],
        'Fisico' => [
            [
                'nombre' => 'Cobertura de Audiometrias',
                'tipo' => 'proceso',
                'formula' => '(Trabajadores con audiometria vigente / Total expuestos a ruido) x 100',
                'meta' => 100,
                'unidad' => '%',
                'periodicidad' => 'anual',
                'phva' => 'verificar',
                'numeral' => '3.1.4',
                'definicion' => 'Mide la cobertura de audiometrias para trabajadores expuestos a niveles de ruido ocupacional que superan los limites permisibles.',
                'interpretacion' => 'El 100% indica cobertura total. Es obligatorio para expuestos a ruido >=80 dBA segun GATISO Hipoacusia Neurosensorial.',
                'origen_datos' => 'Profesiograma, resultados de audiometrias, mediciones de ruido ambiental',
                'cargo_responsable' => 'Responsable del SG-SST',
                'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional, higienista industrial'
            ]
        ]
    ];

    /**
     * Obtiene el resumen de indicadores de PyP Salud para un cliente
     */
    public function getResumenIndicadores(int $idCliente): array
    {
        $indicadores = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'pyp_salud')
                ->orLike('nombre_indicador', 'examen', 'both')
                ->orLike('nombre_indicador', 'enfermedad', 'both')
                ->orLike('nombre_indicador', 'salud', 'both')
                ->orLike('nombre_indicador', 'ausentismo', 'both')
                ->orLike('nombre_indicador', 'PyP', 'both')
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
            'sugeridos' => count(self::INDICADORES_PYP_SALUD),
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
        foreach (self::INDICADORES_PYP_SALUD as $idx => $ind) {
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

        // 2. Agregar indicadores segun peligros identificados
        if ($contexto && !empty($contexto['peligros_identificados'])) {
            $peligros = json_decode($contexto['peligros_identificados'], true) ?? [];

            foreach ($peligros as $peligro) {
                foreach (self::INDICADORES_POR_PELIGRO as $tipoPeligro => $indicadoresPeligro) {
                    if (stripos($peligro, $tipoPeligro) !== false) {
                        foreach ($indicadoresPeligro as $indPeligro) {
                            $indicadores[] = [
                                'nombre' => $indPeligro['nombre'],
                                'tipo' => $indPeligro['tipo'],
                                'formula' => $indPeligro['formula'],
                                'meta' => $indPeligro['meta'],
                                'unidad' => $indPeligro['unidad'],
                                'periodicidad' => $indPeligro['periodicidad'],
                                'phva' => $indPeligro['phva'],
                                'numeral' => $indPeligro['numeral'],
                                'origen' => 'peligro',
                                'peligro_relacionado' => $peligro,
                                'seleccionado' => true
                            ];
                        }
                    }
                }
            }
        }

        // 3. Marcar los que ya existen
        $existentes = $this->indicadorModel->getByCliente($idCliente);
        $nombresExistentes = array_map('strtolower', array_column($existentes, 'nombre_indicador'));

        foreach ($indicadores as &$ind) {
            $nombreLower = strtolower($ind['nombre']);
            // Buscar coincidencia parcial
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
     * Genera los indicadores de PyP Salud
     */
    public function generarIndicadores(int $idCliente, ?array $indicadoresSeleccionados = null): array
    {
        $creados = 0;
        $existentes = 0;
        $errores = [];

        // Usar indicadores seleccionados o los predefinidos
        $indicadores = $indicadoresSeleccionados ?? self::INDICADORES_PYP_SALUD;

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
                    'categoria' => 'pyp_salud',
                    'formula' => $ind['formula'],
                    'meta' => $ind['meta'],
                    'unidad_medida' => $ind['unidad'],
                    'periodicidad' => $ind['periodicidad'],
                    'phva' => $ind['phva'],
                    'numeral_resolucion' => $ind['numeral'] ?? '3.1.2',
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
     * Obtiene los indicadores de PyP Salud de un cliente
     */
    public function getIndicadoresCliente(int $idCliente): array
    {
        return $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'pyp_salud')
                ->orLike('nombre_indicador', 'examen', 'both')
                ->orLike('nombre_indicador', 'enfermedad', 'both')
                ->orLike('nombre_indicador', 'salud', 'both')
                ->orLike('nombre_indicador', 'ausentismo', 'both')
                ->orLike('nombre_indicador', 'PyP', 'both')
            ->groupEnd()
            ->orderBy('tipo_indicador', 'ASC')
            ->orderBy('nombre_indicador', 'ASC')
            ->findAll();
    }
}
