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
        'id_cliente', 'id_actividad_pta', 'nombre_indicador', 'tipo_indicador',
        'categoria', 'formula', 'meta', 'unidad_medida', 'periodicidad', 'numeral_resolucion',
        'phva', 'valor_numerador', 'valor_denominador', 'valor_resultado',
        'fecha_medicion', 'cumple_meta', 'observaciones', 'acciones_mejora',
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
        'otro' => [
            'nombre' => 'Otros',
            'icono' => 'bi-three-dots',
            'color' => 'dark',
            'descripcion' => 'Otros indicadores del SG-SST'
        ]
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
}
