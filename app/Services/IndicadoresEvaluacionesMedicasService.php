<?php

namespace App\Services;

use App\Models\IndicadorSSTModel;

/**
 * Servicio para generar indicadores de Evaluaciones Medicas Ocupacionales
 * segun Resolucion 0312/2019 - Estandar 3.1.4
 */
class IndicadoresEvaluacionesMedicasService
{
    protected IndicadorSSTModel $indicadorModel;

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
    }

    /**
     * Indicadores base de Evaluaciones Medicas Ocupacionales
     */
    public const INDICADORES_EVALUACIONES_MEDICAS = [
        [
            'nombre' => 'Cobertura de Evaluaciones Medicas Ocupacionales de Ingreso',
            'tipo' => 'proceso',
            'formula' => '(Trabajadores con evaluacion de ingreso / Total trabajadores que ingresaron en el periodo) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '3.1.4',
            'descripcion' => 'Mide que todos los trabajadores nuevos sean evaluados medicamente antes de iniciar labores',
            'definicion' => 'Mide la proporcion de trabajadores nuevos que recibieron evaluacion medica ocupacional de ingreso antes de iniciar sus labores, segun Res. 2346/2007.',
            'interpretacion' => 'El 100% indica cumplimiento total. Es obligatorio evaluar a todo trabajador antes del inicio de labores. Valores menores generan riesgo legal.',
            'origen_datos' => 'Registro de ingresos (Recursos Humanos), certificados de aptitud de ingreso, IPS contratada',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, medico ocupacional'
        ],
        [
            'nombre' => 'Cumplimiento de Evaluaciones Medicas Periodicas segun Profesiograma',
            'tipo' => 'proceso',
            'formula' => '(Evaluaciones periodicas realizadas / Evaluaciones periodicas programadas segun profesiograma) x 100',
            'meta' => 90,
            'unidad' => '%',
            'periodicidad' => 'semestral',
            'phva' => 'verificar',
            'numeral' => '3.1.4',
            'descripcion' => 'Mide el cumplimiento de las evaluaciones periodicas segun la frecuencia definida por el profesiograma y los peligros',
            'definicion' => 'Mide el porcentaje de evaluaciones medicas periodicas realizadas segun la frecuencia definida en el profesiograma por cargo y nivel de exposicion a peligros.',
            'interpretacion' => 'Un 90% o mas indica buen cumplimiento. La frecuencia depende del tipo de peligro: anual para peligros altos, bianual para medios.',
            'origen_datos' => 'Profesiograma, cronograma de evaluaciones periodicas, certificados de aptitud, IPS',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional'
        ],
        [
            'nombre' => 'Comunicacion Oportuna de Resultados a Trabajadores',
            'tipo' => 'proceso',
            'formula' => '(Certificados entregados en 5 dias habiles / Total evaluaciones realizadas) x 100',
            'meta' => 95,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '3.1.4',
            'descripcion' => 'Mide la oportunidad en la entrega de resultados a los trabajadores segun Res. 2346/2007',
            'definicion' => 'Mide el porcentaje de certificados de aptitud entregados al trabajador dentro de los 5 dias habiles siguientes a la evaluacion, segun Res. 2346/2007 Art. 14.',
            'interpretacion' => 'Valores >=95% indican buena oportunidad. Es un derecho del trabajador recibir copia del certificado. El incumplimiento genera sanciones.',
            'origen_datos' => 'Registros de entrega de certificados (firma del trabajador), fechas de evaluacion vs entrega',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, medico ocupacional'
        ],
        [
            'nombre' => 'Cumplimiento de Restricciones y Recomendaciones Medicas',
            'tipo' => 'proceso',
            'formula' => '(Restricciones implementadas / Total restricciones emitidas por el medico) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '3.1.4',
            'descripcion' => 'Mide la implementacion efectiva de las restricciones y recomendaciones medico-laborales',
            'definicion' => 'Mide el porcentaje de restricciones y recomendaciones medico-laborales que fueron efectivamente implementadas por la empresa en los puestos de trabajo.',
            'interpretacion' => 'El 100% es obligatorio. El incumplimiento de restricciones medicas genera responsabilidad legal directa del empleador ante accidentes o agravamiento.',
            'origen_datos' => 'Certificados de aptitud con restricciones, registro de implementacion, seguimiento de jefes inmediatos',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional, jefes inmediatos'
        ],
        [
            'nombre' => 'Cobertura de Evaluaciones Medicas de Egreso',
            'tipo' => 'proceso',
            'formula' => '(Trabajadores con evaluacion de egreso / Total trabajadores retirados en el periodo) x 100',
            'meta' => 90,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '3.1.4',
            'descripcion' => 'Mide que los trabajadores retirados sean evaluados medicamente al finalizar la relacion laboral',
            'definicion' => 'Mide la proporcion de trabajadores retirados que recibieron evaluacion medica de egreso dentro de los 5 dias siguientes a la terminacion del vinculo laboral.',
            'interpretacion' => 'Un 90% o mas indica buena gestion. La evaluacion de egreso es obligatoria y protege a la empresa ante futuras reclamaciones por enfermedad laboral.',
            'origen_datos' => 'Registro de retiros (Recursos Humanos), certificados de egreso, IPS contratada',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, medico ocupacional'
        ],
        [
            'nombre' => 'Prevalencia de Enfermedad Laboral Diagnosticada',
            'tipo' => 'resultado',
            'formula' => '(Casos de enfermedad laboral diagnosticada / Total trabajadores evaluados) x 100',
            'meta' => 5,
            'unidad' => '%',
            'periodicidad' => 'anual',
            'phva' => 'verificar',
            'numeral' => '3.1.4',
            'descripcion' => 'Mide la prevalencia de enfermedades laborales detectadas mediante las evaluaciones medicas',
            'menor_es_mejor' => true,
            'definicion' => 'Mide la proporcion de trabajadores evaluados que presentan diagnostico de enfermedad de origen laboral calificada por EPS/ARL.',
            'interpretacion' => 'A menor valor, mejor estado de salud laboral. Valores <=5% son aceptables. Valores superiores requieren revision de PVE y medidas de control.',
            'origen_datos' => 'Diagnostico de condiciones de salud, calificaciones de origen EPS/ARL, evaluaciones medicas',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional, ARL'
        ],
        [
            'nombre' => 'Trabajadores con Aptitud Laboral Sin Restriccion',
            'tipo' => 'resultado',
            'formula' => '(Trabajadores aptos sin restriccion / Total trabajadores evaluados) x 100',
            'meta' => 80,
            'unidad' => '%',
            'periodicidad' => 'anual',
            'phva' => 'verificar',
            'numeral' => '3.1.4',
            'descripcion' => 'Mide el porcentaje de trabajadores que resultan aptos sin restricciones en sus evaluaciones medicas',
            'definicion' => 'Mide la proporcion de trabajadores que tras la evaluacion medica ocupacional resultan aptos para desempenar sus funciones sin ninguna restriccion medica.',
            'interpretacion' => 'Valores >=80% indican buen estado de salud general. El 20% restante puede tener restricciones temporales o permanentes que requieren seguimiento.',
            'origen_datos' => 'Certificados de aptitud laboral, diagnostico de condiciones de salud, IPS',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional, Recursos Humanos'
        ]
    ];

    /**
     * Obtiene el resumen de indicadores de Evaluaciones Medicas para un cliente
     */
    public function getResumenIndicadores(int $idCliente): array
    {
        $indicadores = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'evaluaciones_medicas_ocupacionales')
                ->orLike('nombre_indicador', 'evaluacion medica', 'both')
                ->orLike('nombre_indicador', 'evaluaciones medicas', 'both')
                ->orLike('nombre_indicador', 'profesiograma', 'both')
                ->orLike('nombre_indicador', 'aptitud laboral', 'both')
                ->orLike('nombre_indicador', 'restricciones medicas', 'both')
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
            'sugeridos' => count(self::INDICADORES_EVALUACIONES_MEDICAS),
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
        foreach (self::INDICADORES_EVALUACIONES_MEDICAS as $idx => $ind) {
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
     * Genera los indicadores de Evaluaciones Medicas Ocupacionales
     */
    public function generarIndicadores(int $idCliente, ?array $indicadoresSeleccionados = null): array
    {
        $creados = 0;
        $existentes = 0;
        $errores = [];

        $indicadores = $indicadoresSeleccionados ?? self::INDICADORES_EVALUACIONES_MEDICAS;

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
                    'categoria' => 'evaluaciones_medicas_ocupacionales',
                    'formula' => $ind['formula'],
                    'meta' => $ind['meta'],
                    'unidad_medida' => $ind['unidad'],
                    'periodicidad' => $ind['periodicidad'],
                    'phva' => $ind['phva'],
                    'numeral_resolucion' => $ind['numeral'] ?? '3.1.4',
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
     * Obtiene los indicadores de Evaluaciones Medicas de un cliente
     */
    public function getIndicadoresCliente(int $idCliente): array
    {
        return $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'evaluaciones_medicas_ocupacionales')
                ->orLike('nombre_indicador', 'evaluacion medica', 'both')
                ->orLike('nombre_indicador', 'evaluaciones medicas', 'both')
                ->orLike('nombre_indicador', 'profesiograma', 'both')
                ->orLike('nombre_indicador', 'aptitud laboral', 'both')
                ->orLike('nombre_indicador', 'restricciones medicas', 'both')
            ->groupEnd()
            ->orderBy('tipo_indicador', 'ASC')
            ->orderBy('nombre_indicador', 'ASC')
            ->findAll();
    }
}
