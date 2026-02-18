<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase ProgramaPromocionPrevencionSalud
 *
 * Implementa la generación del Programa de Promoción y Prevención en Salud (3.1.2)
 * Este documento se alimenta de:
 * - Actividades de PyP Salud del Plan de Trabajo (Fase 1)
 * - Indicadores de PyP Salud configurados (Fase 2)
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class ProgramaPromocionPrevencionSalud extends AbstractDocumentoSST
{
    private ?DocumentoConfigService $configService = null;

    public function getTipoDocumento(): string
    {
        return 'programa_promocion_prevencion_salud';
    }

    public function getNombre(): string
    {
        return 'Programa de Promoción y Prevención en Salud';
    }

    public function getDescripcion(): string
    {
        return 'Programa que establece las actividades de promoción de la salud y prevención de enfermedades laborales, incluyendo estilos de vida saludables, pausas activas, exámenes médicos ocupacionales y control de riesgos derivados de las condiciones de trabajo.';
    }

    public function getEstandar(): ?string
    {
        return '3.1.2';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Introducción', 'key' => 'introduccion'],
            ['numero' => 2, 'nombre' => 'Objetivo General', 'key' => 'objetivo_general'],
            ['numero' => 3, 'nombre' => 'Objetivos Específicos', 'key' => 'objetivos_especificos'],
            ['numero' => 4, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 5, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
            ['numero' => 6, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 7, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            ['numero' => 8, 'nombre' => 'Metodología', 'key' => 'metodologia'],
            ['numero' => 9, 'nombre' => 'Cronograma de Actividades', 'key' => 'cronograma'],
            ['numero' => 10, 'nombre' => 'Indicadores', 'key' => 'indicadores'],
            ['numero' => 11, 'nombre' => 'Recursos', 'key' => 'recursos'],
            ['numero' => 12, 'nombre' => 'Evaluación y Seguimiento', 'key' => 'evaluacion_seguimiento'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['responsable_sst', 'representante_legal'];
    }

    /**
     * Sobrescribe getContextoBase para incluir datos de actividades e indicadores
     * CRÍTICO: Esto alimenta la IA con los datos reales de las fases previas
     */
    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        // Contexto base de la empresa
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $nit = $cliente['nit'] ?? '';
        $actividadEconomica = $contexto['actividad_economica_principal'] ?? 'No especificada';
        $nivelRiesgo = $contexto['nivel_riesgo'] ?? 'No especificado';
        $numTrabajadores = $contexto['numero_trabajadores'] ?? 'No especificado';
        $estandares = $contexto['estandares_aplicables'] ?? 7;
        $idCliente = $cliente['id_cliente'] ?? 0;
        $anio = (int) date('Y');

        $nivelTexto = match(true) {
            $estandares <= 7 => 'básico (hasta 10 trabajadores, riesgo I, II o III)',
            $estandares <= 21 => 'intermedio (11 a 50 trabajadores, riesgo I, II o III)',
            default => 'avanzado (más de 50 trabajadores o riesgo IV y V)'
        };

        // =====================================================================
        // OBTENER ACTIVIDADES DE PYP SALUD DEL PLAN DE TRABAJO (FASE 1)
        // =====================================================================
        $actividadesTexto = $this->obtenerActividadesPyPSalud($idCliente, $anio);

        // =====================================================================
        // OBTENER INDICADORES DE PYP SALUD (FASE 2)
        // =====================================================================
        $indicadoresTexto = $this->obtenerIndicadoresPyPSalud($idCliente, $anio);

        // Construir contexto completo
        return "CONTEXTO DE LA EMPRESA:
- Nombre: {$nombreEmpresa}
- NIT: {$nit}
- Actividad económica: {$actividadEconomica}
- Nivel de riesgo: {$nivelRiesgo}
- Número de trabajadores: {$numTrabajadores}
- Estándares aplicables: {$estandares} ({$nivelTexto})

============================================================
ACTIVIDADES DE PROMOCIÓN Y PREVENCIÓN EN SALUD (FASE 1)
Estas son las actividades REALES registradas en el Plan de Trabajo:
============================================================
{$actividadesTexto}

============================================================
INDICADORES DE PROMOCIÓN Y PREVENCIÓN EN SALUD (FASE 2)
Estos son los indicadores CONFIGURADOS para medir el programa:
============================================================
{$indicadoresTexto}

============================================================
INSTRUCCIONES DE GENERACIÓN:
============================================================
- IMPORTANTE: Usa las actividades e indicadores listados arriba como base para el documento
- Los OBJETIVOS deben estar alineados con las actividades registradas
- El CRONOGRAMA debe reflejar las actividades del Plan de Trabajo
- Los INDICADORES del documento deben corresponder con los configurados
- Personaliza el contenido para esta empresa específica
- Ajusta la extensión y complejidad según el nivel de estándares
- Usa terminología de la normativa colombiana (Resolución 0312/2019, Decreto 1072/2015)
- NO uses tablas Markdown a menos que se indique específicamente
- Mantén un tono profesional y técnico";
    }

    /**
     * Obtiene las actividades de PyP Salud del Plan de Trabajo
     * Usa conexión directa a BD para evitar dependencias de Models en constructor
     */
    private function obtenerActividadesPyPSalud(int $idCliente, int $anio): string
    {
        if ($idCliente <= 0) {
            return "No se encontraron actividades (cliente no identificado)";
        }

        try {
            $db = \Config\Database::connect();

            // Consulta directa - mismo filtro que ActividadesPyPSaludService::getActividadesCliente()
            $actividades = $db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('YEAR(fecha_propuesta)', $anio)
                ->groupStart()
                    ->where('tipo_servicio', 'Programa PyP Salud')
                    ->orLike('tipo_servicio', 'Promocion', 'both')
                    ->orLike('tipo_servicio', 'Prevencion', 'both')
                    ->orLike('actividad_plandetrabajo', 'examen medico', 'both')
                    ->orLike('actividad_plandetrabajo', 'examenes medicos', 'both')
                    ->orLike('actividad_plandetrabajo', 'pausas activas', 'both')
                    ->orLike('actividad_plandetrabajo', 'promocion', 'both')
                    ->orLike('actividad_plandetrabajo', 'prevencion', 'both')
                    ->orLike('actividad_plandetrabajo', 'semana de la salud', 'both')
                    ->orLike('actividad_plandetrabajo', 'vacunacion', 'both')
                    ->orLike('actividad_plandetrabajo', 'estilos de vida saludables', 'both')
                ->groupEnd()
                ->orderBy('fecha_propuesta', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($actividades)) {
                return "No hay actividades de PyP Salud registradas para el año {$anio}";
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
            log_message('error', "Error obteniendo actividades PyP Salud: " . $e->getMessage());
            return "Error al obtener actividades: " . $e->getMessage();
        }
    }

    /**
     * Obtiene los indicadores de PyP Salud configurados
     * Usa conexión directa a BD para evitar dependencias de Models en constructor
     */
    private function obtenerIndicadoresPyPSalud(int $idCliente, int $anio): string
    {
        if ($idCliente <= 0) {
            return "No se encontraron indicadores (cliente no identificado)";
        }

        try {
            $db = \Config\Database::connect();

            // Consulta directa a tbl_indicadores_sst
            // Nota: La tabla no tiene columna 'anio', buscamos por categoría y estado activo
            $indicadores = $db->table('tbl_indicadores_sst')
                ->where('id_cliente', $idCliente)
                ->where('categoria', 'pyp_salud')
                ->where('activo', 1)
                ->get()
                ->getResultArray();

            if (empty($indicadores)) {
                return "No hay indicadores de PyP Salud configurados";
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
                $texto .= "   - Fórmula: {$formula}\n";
                $texto .= "   - Meta: {$meta}\n";
                $texto .= "   - Periodicidad: {$periodicidad}\n\n";
            }

            return $texto;

        } catch (\Exception $e) {
            log_message('error', "Error obteniendo indicadores PyP Salud: " . $e->getMessage());
            return "Error al obtener indicadores: " . $e->getMessage();
        }
    }

    /**
     * Prompts estáticos de fallback
     */
    private function getPromptEstatico(string $seccionKey, int $estandares): string
    {
        $prompts = [
            'introduccion' => "Genera una introducción para el Programa de Promoción y Prevención en Salud.
Usa el contexto de la empresa y menciona la importancia de la promoción de la salud en el ámbito laboral.
Incluye referencia a la Resolución 0312/2019 estándar 3.1.2.
Máximo 3 párrafos.",

            'objetivo_general' => "Genera el objetivo general del Programa de Promoción y Prevención en Salud.
Debe ser un objetivo SMART orientado a promover la salud y prevenir enfermedades laborales.
IMPORTANTE: El objetivo debe estar alineado con las ACTIVIDADES registradas en el Plan de Trabajo.",

            'objetivos_especificos' => "Genera los objetivos específicos del programa.
IMPORTANTE: Los objetivos deben derivarse de las ACTIVIDADES de PyP Salud listadas en el contexto.
Incluir mínimo 5 objetivos que aborden las actividades programadas.
Presentar en formato de lista numerada.",

            'cronograma' => "Genera el cronograma de actividades del Programa de Promoción y Prevención en Salud.
IMPORTANTE: Usa las ACTIVIDADES REALES del Plan de Trabajo listadas en el contexto.
Presenta las actividades con sus meses programados en formato de tabla markdown.
Columnas: Actividad | Responsable | Ene | Feb | Mar | Abr | May | Jun | Jul | Ago | Sep | Oct | Nov | Dic",

            'indicadores' => "Define los indicadores del Programa de Promoción y Prevención en Salud.
IMPORTANTE: Usa los INDICADORES CONFIGURADOS listados en el contexto.
Presenta cada indicador con: nombre, tipo, fórmula, meta y periodicidad.",

            'metodologia' => "Genera la metodología del Programa de Promoción y Prevención en Salud.
IMPORTANTE: La metodología debe describir CÓMO se ejecutarán las ACTIVIDADES REALES listadas en el contexto.
Estructura la metodología en las siguientes fases:

1. **Diagnóstico de condiciones de salud:**
   - Análisis del perfil sociodemográfico
   - Revisión de condiciones de salud de los trabajadores
   - Identificación de necesidades de promoción y prevención

2. **Planeación de actividades:**
   - Describe brevemente las actividades programadas (usa las del Plan de Trabajo en el contexto)
   - Indica recursos necesarios y responsables

3. **Ejecución del programa:**
   - Detalla cómo se implementarán las actividades específicas del Plan de Trabajo
   - Menciona las actividades concretas: {ACTIVIDADES_PTA}
   - Describe el proceso de exámenes médicos ocupacionales
   - Explica cómo se realizarán las capacitaciones y campañas

4. **Seguimiento y evaluación:**
   - Describe cómo se medirá el cumplimiento usando los indicadores del contexto
   - Periodicidad de revisión y ajustes al programa

NO generes contenido genérico. Usa las actividades ESPECÍFICAS del Plan de Trabajo listadas arriba.",

            'alcance' => "Genera el alcance del Programa de Promoción y Prevención en Salud.
Debe especificar:
- A quiénes aplica (trabajadores, contratistas, etc.)
- Qué actividades cubre (mencionar las del Plan de Trabajo)
- Dónde aplica (instalaciones, trabajo en campo, etc.)
- Período de vigencia",

            'responsabilidades' => "Genera las responsabilidades para el Programa de Promoción y Prevención en Salud.
Incluir responsabilidades de:
- Representante Legal o Alta Dirección
- Responsable del SG-SST (usar las actividades del PTA como referencia)
- COPASST o Vigía de SST
- Trabajadores
Las responsabilidades deben estar alineadas con las actividades del Plan de Trabajo.",

            'recursos' => "Genera la sección de recursos necesarios para el Programa de Promoción y Prevención en Salud.
Basándote en las ACTIVIDADES del Plan de Trabajo, identifica:
- Recursos humanos (quién ejecutará cada tipo de actividad)
- Recursos físicos (espacios, equipos)
- Recursos financieros (presupuesto estimado según actividades)
- Recursos tecnológicos (si aplica)
Sé específico según las actividades listadas en el contexto.",

            'evaluacion_seguimiento' => "Genera la sección de Evaluación y Seguimiento del programa.
IMPORTANTE: Usa los INDICADORES listados en el contexto para describir cómo se evaluará el programa.
Incluye:
- Periodicidad de medición de cada indicador
- Responsable de la medición
- Mecanismo de seguimiento a las actividades del Plan de Trabajo
- Proceso de revisión y mejora continua
- Reportes a generar y su frecuencia"
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la sección '{$seccionKey}' del Programa de Promoción y Prevención en Salud.";
    }

    /**
     * Contenido estático de fallback
     */
    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'introduccion' => "{$nombreEmpresa} en cumplimiento de la normatividad legal vigente en materia de Seguridad y Salud en el Trabajo, específicamente el estándar 3.1.2 de la Resolución 0312 de 2019, ha desarrollado el presente Programa de Promoción y Prevención en Salud.\n\nEste programa tiene como propósito fundamental promover la salud y prevenir enfermedades derivadas de las condiciones de trabajo, implementando actividades que fomenten estilos de vida saludables y el autocuidado en todos los trabajadores.\n\nLa promoción de la salud en el ámbito laboral es una estrategia que combina actividades de información, educación y capacitación para crear conciencia sobre la importancia de mantener hábitos saludables dentro y fuera del trabajo.",

            'objetivo_general' => "Promover la salud y prevenir enfermedades laborales en todos los trabajadores de {$nombreEmpresa}, mediante la implementación de actividades de promoción y prevención que fomenten estilos de vida saludables, el autocuidado y el control de los riesgos derivados de las condiciones de trabajo, cumpliendo con los requisitos del estándar 3.1.2 de la Resolución 0312 de 2019.",

            'objetivos_especificos' => "1. Implementar exámenes médicos ocupacionales de ingreso, periódicos y de egreso según la normatividad vigente\n2. Desarrollar actividades de promoción de estilos de vida saludables (pausas activas, ejercicio, nutrición)\n3. Realizar seguimiento a las condiciones de salud de los trabajadores\n4. Implementar campañas de prevención de enfermedades asociadas al trabajo\n5. Capacitar a los trabajadores en autocuidado y hábitos saludables\n6. Evaluar periódicamente la efectividad del programa mediante indicadores de gestión",

            'alcance' => "El presente programa aplica a todos los trabajadores de {$nombreEmpresa}, incluyendo:\n- Personal administrativo\n- Personal operativo\n- Contratistas y subcontratistas (cuando aplique)\n\nCubre todas las actividades de promoción de la salud y prevención de enfermedades realizadas dentro y fuera de las instalaciones de la empresa.",

            'marco_legal' => "**Normativa aplicable:**\n- Ley 1562 de 2012: Por la cual se modifica el Sistema de Riesgos Laborales\n- Decreto 1072 de 2015: Decreto Único Reglamentario del Sector Trabajo\n- Resolución 0312 de 2019: Estándares Mínimos del SG-SST (Estándar 3.1.2)\n- Resolución 2346 de 2007: Evaluaciones médicas ocupacionales\n- Resolución 2646 de 2008: Riesgo psicosocial",

            'definiciones' => "**Promoción de la salud:** Proceso que permite a las personas incrementar el control sobre su salud para mejorarla.\n\n**Prevención de la enfermedad:** Conjunto de actividades orientadas a evitar la aparición de enfermedades.\n\n**Estilo de vida saludable:** Conjunto de comportamientos que reducen el riesgo de enfermar.\n\n**Autocuidado:** Capacidad de las personas para cuidar su propia salud.\n\n**Pausas activas:** Ejercicios físicos realizados durante la jornada laboral.",

            'responsabilidades' => "**Representante Legal:**\n- Asignar recursos para el programa\n- Aprobar el programa y sus actividades\n\n**Responsable del SG-SST:**\n- Coordinar la ejecución del programa\n- Gestionar exámenes médicos ocupacionales\n- Implementar actividades de promoción y prevención\n\n**{$comite}:**\n- Participar en actividades de promoción\n- Verificar cumplimiento del programa\n\n**Trabajadores:**\n- Participar activamente en las actividades\n- Practicar el autocuidado",

            'metodologia' => "El programa se desarrolla en las siguientes fases:\n\n**Fase 1 - Diagnóstico:**\nAnálisis del perfil sociodemográfico y condiciones de salud\n\n**Fase 2 - Planeación:**\nDefinición de actividades según necesidades identificadas\n\n**Fase 3 - Ejecución:**\nRealización de exámenes médicos, capacitaciones y actividades de promoción\n\n**Fase 4 - Seguimiento:**\nMonitoreo de indicadores y mejora continua",

            'cronograma' => "El cronograma de actividades se desarrolla según lo establecido en el Plan de Trabajo Anual, incluyendo actividades de exámenes médicos, capacitaciones en estilos de vida saludables, pausas activas y campañas de salud distribuidas a lo largo del año {$anio}.",

            'indicadores' => "**Indicadores de gestión:**\n\n1. **Cobertura de Exámenes Médicos:**\n   Fórmula: (Exámenes realizados / Exámenes programados) x 100\n   Meta: ≥ 90%\n\n2. **Cumplimiento de Actividades PyP:**\n   Fórmula: (Actividades ejecutadas / Actividades programadas) x 100\n   Meta: ≥ 85%\n\n3. **Participación en Capacitaciones:**\n   Fórmula: (Trabajadores capacitados / Total trabajadores) x 100\n   Meta: ≥ 80%",

            'recursos' => "**Recursos humanos:**\n- Responsable del SG-SST\n- Médico ocupacional o IPS contratada\n\n**Recursos físicos:**\n- Espacio para capacitaciones\n- Equipos para pausas activas\n\n**Recursos financieros:**\n- Presupuesto para exámenes médicos\n- Presupuesto para campañas de salud",

            'evaluacion_seguimiento' => "El seguimiento al programa se realiza mediante:\n\n- Revisión mensual de avance de actividades\n- Seguimiento trimestral de indicadores\n- Evaluación semestral del programa\n- Análisis anual de cumplimiento de objetivos\n\nLos resultados se documentan y se presentan en la revisión por la dirección."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
