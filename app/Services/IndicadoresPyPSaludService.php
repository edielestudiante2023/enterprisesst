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
            'descripcion' => 'Mide el porcentaje de trabajadores con examenes medicos ocupacionales al dia'
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
            'descripcion' => 'Mide el cumplimiento de las actividades de promocion y prevencion en salud'
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
            'menor_es_mejor' => true
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
            'menor_es_mejor' => true
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
            'menor_es_mejor' => true
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
            'descripcion' => 'Mide la participacion de trabajadores en campanas y actividades de promocion'
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
            'descripcion' => 'Mide el seguimiento a trabajadores con recomendaciones o restricciones medicas'
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
                'numeral' => '3.1.2'
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
                'menor_es_mejor' => true
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
                'numeral' => '3.1.4'
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
                'numeral' => '3.1.4'
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
