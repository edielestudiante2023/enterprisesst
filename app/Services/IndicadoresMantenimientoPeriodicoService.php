<?php

namespace App\Services;

use App\Models\IndicadorSSTModel;

/**
 * Servicio para generar indicadores de Mantenimiento Periodico
 * segun Resolucion 0312/2019 - Estandar 4.2.5
 */
class IndicadoresMantenimientoPeriodicoService
{
    protected IndicadorSSTModel $indicadorModel;

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
    }

    /**
     * Indicadores base de Mantenimiento Periodico
     */
    public const INDICADORES_MANTENIMIENTO = [
        [
            'nombre' => 'Cumplimiento del Programa de Mantenimiento Preventivo',
            'tipo' => 'proceso',
            'formula' => '(Mantenimientos preventivos ejecutados / Mantenimientos preventivos programados) x 100',
            'meta' => 90,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '4.2.5',
            'descripcion' => 'Mide el grado de cumplimiento del cronograma de mantenimiento preventivo programado',
            'definicion' => 'Mide el porcentaje de mantenimientos preventivos ejecutados frente a los programados en el cronograma de mantenimiento de equipos, maquinas e instalaciones.',
            'interpretacion' => 'Un resultado >=90% indica buen cumplimiento. Valores menores incrementan el riesgo de fallas y accidentes por condiciones inseguras en equipos.',
            'origen_datos' => 'Cronograma de mantenimiento, ordenes de trabajo, hojas de vida de equipos',
            'cargo_responsable' => 'Responsable del SG-SST / Jefe de Mantenimiento',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, jefe de mantenimiento'
        ],
        [
            'nombre' => 'Porcentaje de Equipos con Ficha Tecnica Actualizada',
            'tipo' => 'proceso',
            'formula' => '(Equipos con ficha tecnica actualizada / Total equipos inventariados) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'semestral',
            'phva' => 'verificar',
            'numeral' => '4.2.5',
            'descripcion' => 'Verifica que todos los equipos cuenten con ficha tecnica y hoja de vida actualizada',
            'definicion' => 'Mide la proporcion de equipos, maquinas y herramientas del inventario que cuentan con ficha tecnica y hoja de vida actualizadas.',
            'interpretacion' => 'El 100% indica documentacion completa. La ficha tecnica es requisito para programar mantenimiento adecuado y garantizar condiciones seguras.',
            'origen_datos' => 'Inventario de equipos, fichas tecnicas, hojas de vida de maquinaria',
            'cargo_responsable' => 'Responsable del SG-SST / Jefe de Mantenimiento',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, jefe de mantenimiento'
        ],
        [
            'nombre' => 'Numero de Fallas por Mantenimiento Inadecuado',
            'tipo' => 'resultado',
            'formula' => 'Fallas reportadas por deficiencia en mantenimiento en el periodo',
            'meta' => 2,
            'unidad' => 'fallas/trimestre',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '4.2.5',
            'descripcion' => 'Registra las fallas o averias atribuibles a falta o deficiencia en mantenimiento',
            'menor_es_mejor' => true,
            'definicion' => 'Registra la cantidad de fallas, averias o paradas no programadas atribuibles a deficiencias en el mantenimiento preventivo de equipos e instalaciones.',
            'interpretacion' => 'A menor valor, mejor programa de mantenimiento. La meta de <=2 fallas/trimestre es aceptable. Valores superiores requieren revision del cronograma de mantenimiento.',
            'origen_datos' => 'Reportes de fallas, ordenes de trabajo correctivo, registro de paradas no programadas',
            'cargo_responsable' => 'Responsable del SG-SST / Jefe de Mantenimiento',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, jefe de mantenimiento, COPASST/Vigia'
        ],
        [
            'nombre' => 'Disponibilidad Operativa de Equipos Criticos',
            'tipo' => 'resultado',
            'formula' => '(Horas operativas reales / Horas operativas programadas) x 100',
            'meta' => 95,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '4.2.5',
            'descripcion' => 'Mide la disponibilidad operativa de equipos clasificados como criticos para la operacion',
            'definicion' => 'Mide el porcentaje de tiempo que los equipos criticos estuvieron disponibles para operacion frente al tiempo programado, descontando paradas por fallas.',
            'interpretacion' => 'Valores >=95% indican alta confiabilidad. Valores menores generan riesgo operacional y pueden derivar en condiciones inseguras de trabajo.',
            'origen_datos' => 'Registros de operacion de equipos, reportes de paradas, ordenes de mantenimiento',
            'cargo_responsable' => 'Jefe de Mantenimiento / Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, jefe de mantenimiento, jefe de operaciones'
        ],
        [
            'nombre' => 'Cumplimiento de Inspecciones de Seguridad a Instalaciones',
            'tipo' => 'proceso',
            'formula' => '(Inspecciones realizadas / Inspecciones programadas) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '4.2.5',
            'descripcion' => 'Mide el cumplimiento del programa de inspecciones de seguridad a instalaciones fisicas',
            'definicion' => 'Mide el porcentaje de inspecciones de seguridad ejecutadas a instalaciones fisicas frente a las programadas en el cronograma de inspecciones.',
            'interpretacion' => 'El 100% indica cumplimiento total. Las inspecciones detectan condiciones inseguras antes de que generen accidentes. Es requisito de Res. 0312/2019.',
            'origen_datos' => 'Cronograma de inspecciones, formatos de inspeccion diligenciados, registros fotograficos',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, jefe de mantenimiento'
        ],
        [
            'nombre' => 'Tasa de Accidentes Relacionados con Equipos o Instalaciones',
            'tipo' => 'resultado',
            'formula' => '(Accidentes por fallas en equipos o instalaciones / Total accidentes) x 100',
            'meta' => 0,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'numeral' => '4.2.5',
            'descripcion' => 'Mide la proporcion de accidentes laborales atribuibles a fallas en equipos, maquinas o instalaciones',
            'menor_es_mejor' => true,
            'definicion' => 'Mide la proporcion de accidentes de trabajo cuya causa raiz fue atribuida a fallas en equipos, maquinas, herramientas o instalaciones fisicas.',
            'interpretacion' => 'El 0% indica que ningun accidente fue causado por fallas en equipos. Valores superiores requieren revision inmediata del programa de mantenimiento.',
            'origen_datos' => 'Investigaciones de accidentes de trabajo, FURAT, analisis de causalidad',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL, jefe de mantenimiento'
        ]
    ];

    /**
     * Obtiene el resumen de indicadores de Mantenimiento Periodico para un cliente
     */
    public function getResumenIndicadores(int $idCliente): array
    {
        $indicadores = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'mantenimiento_periodico')
                ->orLike('nombre_indicador', 'mantenimiento preventivo', 'both')
                ->orLike('nombre_indicador', 'mantenimiento periodico', 'both')
                ->orLike('nombre_indicador', 'ficha tecnica', 'both')
                ->orLike('nombre_indicador', 'inspecciones de seguridad', 'both')
                ->orLike('nombre_indicador', 'disponibilidad operativa', 'both')
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
            'sugeridos' => count(self::INDICADORES_MANTENIMIENTO),
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
        foreach (self::INDICADORES_MANTENIMIENTO as $idx => $ind) {
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
     * Genera los indicadores de Mantenimiento Periodico
     */
    public function generarIndicadores(int $idCliente, ?array $indicadoresSeleccionados = null): array
    {
        $creados = 0;
        $existentes = 0;
        $errores = [];

        $indicadores = $indicadoresSeleccionados ?? self::INDICADORES_MANTENIMIENTO;

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
                    'categoria' => 'mantenimiento_periodico',
                    'formula' => $ind['formula'],
                    'meta' => $ind['meta'],
                    'unidad_medida' => $ind['unidad'],
                    'periodicidad' => $ind['periodicidad'],
                    'phva' => $ind['phva'],
                    'numeral_resolucion' => $ind['numeral'] ?? '4.2.5',
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
     * Obtiene los indicadores de Mantenimiento Periodico de un cliente
     */
    public function getIndicadoresCliente(int $idCliente): array
    {
        return $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', 'mantenimiento_periodico')
                ->orLike('nombre_indicador', 'mantenimiento preventivo', 'both')
                ->orLike('nombre_indicador', 'mantenimiento periodico', 'both')
                ->orLike('nombre_indicador', 'ficha tecnica', 'both')
                ->orLike('nombre_indicador', 'inspecciones de seguridad', 'both')
                ->orLike('nombre_indicador', 'disponibilidad operativa', 'both')
            ->groupEnd()
            ->orderBy('tipo_indicador', 'ASC')
            ->orderBy('nombre_indicador', 'ASC')
            ->findAll();
    }
}
