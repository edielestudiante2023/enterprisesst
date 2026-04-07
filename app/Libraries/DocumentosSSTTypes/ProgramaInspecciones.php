<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase ProgramaInspecciones
 *
 * GOLD STANDARD — Usar como referencia para crear nuevos programas Tipo B (Parte 3 - Handler).
 * Guia: docs/MODULO_NUMERALES_SGSST/03_MODULO_3_PARTES/ZZ_98_COMO_AGREGAR_PROGRAMA.md
 *
 * Implementa la generacion del Programa de Inspecciones a Instalaciones,
 * Maquinaria o Equipos (4.2.4) con participacion del COPASST.
 *
 * Este documento se alimenta de:
 * - Actividades de Inspecciones del Plan de Trabajo (Fase 1)
 * - Indicadores de Inspecciones configurados (Fase 2)
 *
 * @package App\Libraries\DocumentosSSTTypes
 */
class ProgramaInspecciones extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'programa_inspecciones';
    }

    public function getNombre(): string
    {
        return 'Programa de Inspecciones';
    }

    public function getDescripcion(): string
    {
        return 'Programa de inspecciones a instalaciones, maquinaria o equipos con participacion del COPASST o Vigia SST, segun Resolucion 0312/2019 estandar 4.2.4 y Decreto 1072/2015.';
    }

    public function getEstandar(): ?string
    {
        return '4.2.4';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Marco Normativo', 'key' => 'marco_normativo'],
            ['numero' => 4, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 5, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            ['numero' => 6, 'nombre' => 'Tipos de Inspecciones', 'key' => 'tipos_inspecciones'],
            ['numero' => 7, 'nombre' => 'Metodologia de Inspeccion', 'key' => 'metodologia'],
            ['numero' => 8, 'nombre' => 'Cronograma de Inspecciones', 'key' => 'cronograma_inspecciones'],
            ['numero' => 9, 'nombre' => 'Hallazgos y Acciones Correctivas', 'key' => 'hallazgos_acciones'],
            ['numero' => 10, 'nombre' => 'Indicadores de Gestion', 'key' => 'indicadores_gestion'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['responsable_sst', 'representante_legal'];
    }

    /**
     * Sobrescribe getContextoBase para incluir datos de actividades e indicadores de inspecciones
     */
    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $nit = $cliente['nit'] ?? '';
        $actividadEconomica = $contexto['actividad_economica_principal'] ?? 'No especificada';
        $nivelRiesgo = $contexto['nivel_riesgo'] ?? 'No especificado';
        $numTrabajadores = $contexto['numero_trabajadores'] ?? 'No especificado';
        $estandares = $contexto['estandares_aplicables'] ?? 7;
        $idCliente = $cliente['id_cliente'] ?? 0;
        $anio = (int) date('Y');

        $nivelTexto = match(true) {
            $estandares <= 7 => 'basico (hasta 10 trabajadores, riesgo I, II o III)',
            $estandares <= 21 => 'intermedio (11 a 50 trabajadores, riesgo I, II o III)',
            default => 'avanzado (mas de 50 trabajadores o riesgo IV y V)'
        };

        $comite = $estandares <= 7 ? 'Vigia SST' : 'COPASST';
        $actividadesTexto = $this->obtenerActividadesInspecciones($idCliente, $anio);
        $indicadoresTexto = $this->obtenerIndicadoresInspecciones($idCliente);

        $contextoTexto = "CONTEXTO DE LA EMPRESA:
- Nombre: {$nombreEmpresa}
- NIT: {$nit}
- Actividad economica: {$actividadEconomica}
- Nivel de riesgo: {$nivelRiesgo}
- Numero de trabajadores: {$numTrabajadores}
- Estandares aplicables: {$estandares} ({$nivelTexto})
- Organismo de participacion: {$comite}

============================================================
ACTIVIDADES DEL PROGRAMA DE INSPECCIONES (FASE 1)
Estas son las actividades REALES registradas en el Plan de Trabajo:
============================================================
{$actividadesTexto}

============================================================
INDICADORES DEL PROGRAMA DE INSPECCIONES (FASE 2)
Estos son los indicadores CONFIGURADOS para medir el programa:
============================================================
{$indicadoresTexto}

============================================================
INSTRUCCIONES DE GENERACION:
============================================================
- IMPORTANTE: Usa las actividades e indicadores listados arriba como base para el documento
- Este es un Programa de Inspecciones enfocado en instalaciones, maquinaria y equipos
- La participacion del {$comite} es OBLIGATORIA en todas las inspecciones (estandar 4.2.4)
- Los OBJETIVOS deben estar alineados con las actividades registradas
- El CRONOGRAMA debe reflejar las actividades del Plan de Trabajo
- Los INDICADORES del documento deben corresponder con los configurados
- Incluir tipos de inspeccion: generales, especificas, pre-operacionales, EPP, emergencia
- Incluir sistema de hallazgos: clasificacion (critico/mayor/menor), acciones correctivas, cierre
- Referenciar NTC 4114 (inspecciones planeadas de seguridad)
- Personaliza el contenido para esta empresa especifica
- Usa terminologia de la normativa colombiana (Resolucion 0312/2019, Decreto 1072/2015)
- NO uses tablas Markdown a menos que se indique especificamente
- Manten un tono profesional y tecnico\n";

        // Campos ampliados del contexto
        if (!empty($contexto['horario_lunes_viernes'])) $contextoTexto .= "- Horario L-V: {$contexto['horario_lunes_viernes']}\n";
        if (!empty($contexto['descripcion_turnos'])) $contextoTexto .= "- Detalle turnos: {$contexto['descripcion_turnos']}\n";
        if (!empty($contexto['eps_principales'])) $contextoTexto .= "- EPS: {$contexto['eps_principales']}\n";
        if (!empty($contexto['manejo_incapacidades'])) $contextoTexto .= "- Manejo incapacidades: {$contexto['manejo_incapacidades']}\n";
        if (!empty($contexto['epp_por_cargo'])) $contextoTexto .= "- EPP por cargo: {$contexto['epp_por_cargo']}\n";
        if (!empty($contexto['vehiculos_maquinaria'])) $contextoTexto .= "- Vehiculos/maquinaria: {$contexto['vehiculos_maquinaria']}\n";
        if (!empty($contexto['actividades_alto_riesgo'])) {
            $actArr = is_array($contexto['actividades_alto_riesgo']) ? $contexto['actividades_alto_riesgo'] : json_decode($contexto['actividades_alto_riesgo'], true);
            if (is_array($actArr) && !empty($actArr)) $contextoTexto .= "- Actividades alto riesgo: " . implode(', ', $actArr) . "\n";
        }
        if (!empty($contexto['accidentes_ultimo_anio']) && $contexto['accidentes_ultimo_anio'] > 0) $contextoTexto .= "- Accidentes ultimo ano: {$contexto['accidentes_ultimo_anio']}\n";
        if (!empty($contexto['enfermedades_laborales_activas'])) $contextoTexto .= "- Enfermedades laborales: {$contexto['enfermedades_laborales_activas']}\n";
        if (!empty($contexto['numero_pisos']) && $contexto['numero_pisos'] > 1) $contextoTexto .= "- Pisos: {$contexto['numero_pisos']}\n";
        if (!empty($contexto['sustancias_quimicas'])) $contextoTexto .= "- Sustancias quimicas: {$contexto['sustancias_quimicas']}\n";

        return $contextoTexto;
    }

    private function obtenerActividadesInspecciones(int $idCliente, int $anio): string
    {
        if ($idCliente <= 0) {
            return "No se encontraron actividades (cliente no identificado)";
        }

        try {
            $db = \Config\Database::connect();

            $actividades = $db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('YEAR(fecha_propuesta)', $anio)
                ->groupStart()
                    ->where('tipo_servicio', 'Programa de Inspecciones')
                    ->orLike('tipo_servicio', 'Inspecciones', 'both')
                    ->orLike('actividad_plandetrabajo', 'inspeccion', 'both')
                    ->orLike('actividad_plandetrabajo', 'instalaciones', 'both')
                    ->orLike('actividad_plandetrabajo', 'maquinaria', 'both')
                    ->orLike('actividad_plandetrabajo', 'equipos de emergencia', 'both')
                    ->orLike('actividad_plandetrabajo', 'condiciones inseguras', 'both')
                ->groupEnd()
                ->orderBy('fecha_propuesta', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($actividades)) {
                return "No hay actividades de Inspecciones registradas para el ano {$anio}";
            }

            $texto = "Total: " . count($actividades) . " actividades\n\n";

            foreach ($actividades as $i => $act) {
                $num = $i + 1;
                $actividad = $act['actividad_plandetrabajo'] ?? 'Sin nombre';
                $responsable = $act['responsable_sugerido_plandetrabajo'] ?? 'Responsable SST';
                $fecha = $act['fecha_propuesta'] ?? '';
                $mes = $fecha ? date('F Y', strtotime($fecha)) : 'No programada';
                $estado = $act['estado_actividad'] ?? 'ABIERTA';
                $phva = $act['phva_plandetrabajo'] ?? 'HACER';

                $texto .= "{$num}. {$actividad}\n";
                $texto .= "   - Responsable: {$responsable}\n";
                $texto .= "   - Mes programado: {$mes}\n";
                $texto .= "   - Ciclo PHVA: {$phva}\n";
                $texto .= "   - Estado: {$estado}\n\n";
            }

            return $texto;

        } catch (\Exception $e) {
            log_message('error', "Error obteniendo actividades Inspecciones: " . $e->getMessage());
            return "Error al obtener actividades: " . $e->getMessage();
        }
    }

    private function obtenerIndicadoresInspecciones(int $idCliente): string
    {
        if ($idCliente <= 0) {
            return "No se encontraron indicadores (cliente no identificado)";
        }

        try {
            $db = \Config\Database::connect();

            $indicadores = $db->table('tbl_indicadores_sst')
                ->where('id_cliente', $idCliente)
                ->where('categoria', 'inspecciones')
                ->where('activo', 1)
                ->get()
                ->getResultArray();

            if (empty($indicadores)) {
                return "No hay indicadores de Inspecciones configurados";
            }

            $texto = "Total: " . count($indicadores) . " indicadores\n\n";

            foreach ($indicadores as $i => $ind) {
                $num = $i + 1;
                $nombre = $ind['nombre_indicador'] ?? 'Sin nombre';
                $formula = $ind['formula'] ?? 'No definida';
                $meta = $ind['meta'] ?? 'No definida';
                $periodicidad = $ind['periodicidad'] ?? 'No definida';
                $tipo = $ind['tipo_indicador'] ?? 'No definido';

                $texto .= "{$num}. {$nombre}\n";
                $texto .= "   - Tipo: {$tipo}\n";
                $texto .= "   - Formula: {$formula}\n";
                $texto .= "   - Meta: {$meta}\n";
                $texto .= "   - Periodicidad: {$periodicidad}\n\n";
            }

            return $texto;

        } catch (\Exception $e) {
            log_message('error', "Error obteniendo indicadores Inspecciones: " . $e->getMessage());
            return "Error al obtener indicadores: " . $e->getMessage();
        }
    }
}
