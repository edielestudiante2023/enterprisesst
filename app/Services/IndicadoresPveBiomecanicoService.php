<?php

namespace App\Services;

use App\Models\IndicadorSSTModel;

/**
 * Servicio para generar indicadores del PVE de Riesgo Biomecanico
 * segun Resolucion 0312/2019 - Estandar 4.2.3
 */
class IndicadoresPveBiomecanicoService
{
    protected IndicadorSSTModel $indicadorModel;

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
    }

    /**
     * Indicadores base del PVE de Riesgo Biomecanico
     */
    public const INDICADORES_PVE_BIOMECANICO = [
        [
            'nombre' => 'Cumplimiento de Actividades del PVE Biomecanico',
            'tipo' => 'proceso',
            'formula' => '(Actividades PVE Biomecanico ejecutadas / Actividades PVE Biomecanico programadas) x 100',
            'meta' => 90,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '4.2.3',
            'descripcion' => 'Mide el cumplimiento de las actividades del programa de vigilancia epidemiologica de riesgo biomecanico',
            'definicion' => 'Mide el porcentaje de actividades ejecutadas del Programa de Vigilancia Epidemiologica de riesgo biomecanico frente a las programadas.',
            'interpretacion' => 'Un resultado >=90% indica buen cumplimiento. Valores menores requieren revision de recursos y prioridades del PVE biomecanico.',
            'origen_datos' => 'Cronograma PVE biomecanico, registros de actividades ejecutadas, actas',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, fisioterapeuta/ergonom'
        ],
        [
            'nombre' => 'Cobertura de Evaluaciones Ergonomicas de Puestos de Trabajo',
            'tipo' => 'proceso',
            'formula' => '(Puestos de trabajo evaluados / Puestos de trabajo con riesgo biomecanico identificado) x 100',
            'meta' => 80,
            'unidad' => '%',
            'periodicidad' => 'semestral',
            'phva' => 'verificar',
            'numeral' => '4.2.3',
            'descripcion' => 'Mide la cobertura de las evaluaciones ergonomicas en puestos con exposicion a riesgo biomecanico',
            'definicion' => 'Mide la proporcion de puestos de trabajo con riesgo biomecanico identificado en la Matriz IPVR que han recibido evaluacion ergonomica.',
            'interpretacion' => 'Un 80% o mas indica buena cobertura. Priorizar puestos con mayor nivel de riesgo. Se recomienda usar metodologias RULA, REBA u OWAS.',
            'origen_datos' => 'Matriz IPVR (peligro biomecanico), informes de evaluacion ergonomica, fotografias de puestos',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, fisioterapeuta/ergonom, jefes de area'
        ],
        [
            'nombre' => 'Prevalencia de Sintomatologia Osteomuscular',
            'tipo' => 'resultado',
            'formula' => '(Trabajadores con sintomas osteomusculares / Total trabajadores evaluados) x 100',
            'meta' => 20,
            'unidad' => '%',
            'periodicidad' => 'semestral',
            'phva' => 'verificar',
            'numeral' => '4.2.3',
            'descripcion' => 'Mide la prevalencia de sintomatologia osteomuscular en la poblacion trabajadora',
            'menor_es_mejor' => true,
            'definicion' => 'Mide la proporcion de trabajadores que reportan sintomatologia osteomuscular (dolor, molestia, adormecimiento) en encuestas de morbilidad sentida.',
            'interpretacion' => 'Valores <=20% son aceptables. Valores superiores indican alta carga de sintomatologia y requieren intervencion ergonomica prioritaria.',
            'origen_datos' => 'Encuesta de morbilidad sentida (Cuestionario Nordico), evaluaciones medicas periodicas',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional, fisioterapeuta'
        ],
        [
            'nombre' => 'Incidencia de Desordenes Musculoesqueleticos (DME)',
            'tipo' => 'resultado',
            'formula' => '(Casos nuevos de DME en el periodo / Total trabajadores expuestos) x 100',
            'meta' => 5,
            'unidad' => '%',
            'periodicidad' => 'anual',
            'phva' => 'verificar',
            'numeral' => '4.2.3',
            'descripcion' => 'Mide la incidencia de nuevos casos de DME diagnosticados en el periodo',
            'menor_es_mejor' => true,
            'definicion' => 'Mide la proporcion de nuevos casos diagnosticados de desordenes musculoesqueleticos en trabajadores expuestos a riesgo biomecanico.',
            'interpretacion' => 'A menor valor, mejor gestion preventiva. Valores <=5% son aceptables. Valores superiores requieren revision de controles ergonomicos y del PVE.',
            'origen_datos' => 'Diagnosticos medicos (DME), evaluaciones medicas periodicas, calificaciones EPS/ARL',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, medico ocupacional, ARL'
        ],
        [
            'nombre' => 'Cobertura de Participacion en Pausas Activas',
            'tipo' => 'proceso',
            'formula' => '(Trabajadores que participan regularmente en pausas activas / Total trabajadores) x 100',
            'meta' => 80,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '4.2.3',
            'descripcion' => 'Mide la participacion de los trabajadores en el programa de pausas activas',
            'definicion' => 'Mide la proporcion de trabajadores que participan de manera regular (al menos 3 veces/semana) en el programa de pausas activas de la empresa.',
            'interpretacion' => 'Un 80% o mas indica buena participacion. Las pausas activas reducen fatiga muscular y previenen DME. Valores menores requieren sensibilizacion.',
            'origen_datos' => 'Registros de participacion en pausas activas, encuestas de habitos, observacion directa',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, trabajadores'
        ],
        [
            'nombre' => 'Efectividad de Intervenciones Ergonomicas',
            'tipo' => 'resultado',
            'formula' => '(Puestos intervenidos sin recurrencia de quejas / Total puestos intervenidos) x 100',
            'meta' => 70,
            'unidad' => '%',
            'periodicidad' => 'anual',
            'phva' => 'verificar',
            'numeral' => '4.2.3',
            'descripcion' => 'Mide la efectividad de las intervenciones ergonomicas realizadas en puestos de trabajo',
            'definicion' => 'Mide la proporcion de puestos de trabajo intervenidos ergonomicamente donde no se presentaron nuevas quejas osteomusculares en los siguientes 6 meses.',
            'interpretacion' => 'Valores >=70% indican buena efectividad. Valores menores sugieren que las intervenciones requieren ajustes o complementos adicionales.',
            'origen_datos' => 'Registro de intervenciones ergonomicas, seguimiento de quejas, encuestas post-intervencion',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, fisioterapeuta/ergonom, medico ocupacional'
        ],
        [
            'nombre' => 'Ausentismo por Causa Osteomuscular',
            'tipo' => 'resultado',
            'formula' => '(Dias de ausencia por DME / Total dias laborados programados) x 100',
            'meta' => 3,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '4.2.3',
            'descripcion' => 'Mide el ausentismo laboral atribuible a desordenes musculoesqueleticos',
            'menor_es_mejor' => true,
            'definicion' => 'Mide el porcentaje de dias laborales perdidos por incapacidades relacionadas con desordenes musculoesqueleticos respecto al total programado.',
            'interpretacion' => 'Valores <=3% son aceptables. Valores superiores indican alto impacto de DME en la productividad y requieren fortalecimiento del PVE biomecanico.',
            'origen_datos' => 'Registros de incapacidades (diagnostico DME), nomina, EPS/ARL',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, medico ocupacional'
        ]
    ];

    /**
     * Obtiene el resumen de indicadores del PVE Biomecanico para un cliente
     */
    public function getResumenIndicadores(int $idCliente): array
    {
        $indicadores = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'pve_biomecanico')
                ->orLike('nombre_indicador', 'biomecanico', 'both')
                ->orLike('nombre_indicador', 'osteomuscular', 'both')
                ->orLike('nombre_indicador', 'ergonomic', 'both')
                ->orLike('nombre_indicador', 'DME', 'both')
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
            'sugeridos' => count(self::INDICADORES_PVE_BIOMECANICO),
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

        foreach (self::INDICADORES_PVE_BIOMECANICO as $idx => $ind) {
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
     * Genera los indicadores del PVE Biomecanico
     */
    public function generarIndicadores(int $idCliente, ?array $indicadoresSeleccionados = null): array
    {
        $creados = 0;
        $existentes = 0;
        $errores = [];

        $indicadores = $indicadoresSeleccionados ?? self::INDICADORES_PVE_BIOMECANICO;

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
                    'categoria' => 'pve_biomecanico',
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
     * Obtiene los indicadores del PVE Biomecanico de un cliente
     */
    public function getIndicadoresCliente(int $idCliente): array
    {
        return $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'pve_biomecanico')
                ->orLike('nombre_indicador', 'biomecanico', 'both')
                ->orLike('nombre_indicador', 'osteomuscular', 'both')
                ->orLike('nombre_indicador', 'ergonomic', 'both')
                ->orLike('nombre_indicador', 'DME', 'both')
            ->groupEnd()
            ->orderBy('tipo_indicador', 'ASC')
            ->orderBy('nombre_indicador', 'ASC')
            ->findAll();
    }
}
