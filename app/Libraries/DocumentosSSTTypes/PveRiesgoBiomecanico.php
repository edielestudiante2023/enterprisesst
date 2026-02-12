<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase PveRiesgoBiomecanico
 *
 * Implementa la generación del PVE de Riesgo Biomecánico (4.2.3)
 * Este documento se alimenta de:
 * - Actividades PVE Biomecánico del Plan de Trabajo (Fase 1)
 * - Indicadores PVE Biomecánico configurados (Fase 2)
 *
 * @package App\Libraries\DocumentosSSTTypes
 */
class PveRiesgoBiomecanico extends AbstractDocumentoSST
{
    private ?DocumentoConfigService $configService = null;

    public function getTipoDocumento(): string
    {
        return 'pve_riesgo_biomecanico';
    }

    public function getNombre(): string
    {
        return 'PVE de Riesgo Biomecánico';
    }

    public function getDescripcion(): string
    {
        return 'Programa de Vigilancia Epidemiológica orientado a la prevención de Desórdenes Músculo-Esqueléticos (DME) por exposición a factores de riesgo biomecánico: posturas prolongadas, movimientos repetitivos, manipulación manual de cargas y esfuerzos.';
    }

    public function getEstandar(): ?string
    {
        return '4.2.3';
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
     * Sobrescribe getContextoBase para incluir datos de actividades e indicadores del PVE
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
            $estandares <= 7 => 'básico (hasta 10 trabajadores, riesgo I, II o III)',
            $estandares <= 21 => 'intermedio (11 a 50 trabajadores, riesgo I, II o III)',
            default => 'avanzado (más de 50 trabajadores o riesgo IV y V)'
        };

        $actividadesTexto = $this->obtenerActividadesPveBiomecanico($idCliente, $anio);
        $indicadoresTexto = $this->obtenerIndicadoresPveBiomecanico($idCliente, $anio);

        return "CONTEXTO DE LA EMPRESA:
- Nombre: {$nombreEmpresa}
- NIT: {$nit}
- Actividad económica: {$actividadEconomica}
- Nivel de riesgo: {$nivelRiesgo}
- Número de trabajadores: {$numTrabajadores}
- Estándares aplicables: {$estandares} ({$nivelTexto})

============================================================
ACTIVIDADES DEL PVE DE RIESGO BIOMECÁNICO (FASE 1)
Estas son las actividades REALES registradas en el Plan de Trabajo:
============================================================
{$actividadesTexto}

============================================================
INDICADORES DEL PVE DE RIESGO BIOMECÁNICO (FASE 2)
Estos son los indicadores CONFIGURADOS para medir el programa:
============================================================
{$indicadoresTexto}

============================================================
INSTRUCCIONES DE GENERACIÓN:
============================================================
- IMPORTANTE: Usa las actividades e indicadores listados arriba como base para el documento
- Este es un PVE de Riesgo Biomecánico enfocado en prevención de DME
- Los OBJETIVOS deben estar alineados con las actividades registradas
- El CRONOGRAMA debe reflejar las actividades del Plan de Trabajo
- Los INDICADORES del documento deben corresponder con los configurados
- Incluir referencias a GATISO DME (Guías de Atención Integral)
- Mencionar herramientas como Cuestionario Nórdico, REBA, RULA, OWAS según aplique
- Personaliza el contenido para esta empresa específica
- Usa terminología de la normativa colombiana (Resolución 0312/2019, Decreto 1072/2015)
- NO uses tablas Markdown a menos que se indique específicamente
- Mantén un tono profesional y técnico";
    }

    private function obtenerActividadesPveBiomecanico(int $idCliente, int $anio): string
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
                    ->where('tipo_servicio', 'PVE Riesgo Biomecanico')
                    ->orLike('tipo_servicio', 'Biomecanico', 'both')
                    ->orLike('tipo_servicio', 'Biomec', 'both')
                    ->orLike('actividad_plandetrabajo', 'osteomuscular', 'both')
                    ->orLike('actividad_plandetrabajo', 'ergonomic', 'both')
                    ->orLike('actividad_plandetrabajo', 'biomecanico', 'both')
                    ->orLike('actividad_plandetrabajo', 'higiene postural', 'both')
                    ->orLike('actividad_plandetrabajo', 'manejo de cargas', 'both')
                ->groupEnd()
                ->orderBy('fecha_propuesta', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($actividades)) {
                return "No hay actividades del PVE Biomecánico registradas para el año {$anio}";
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
            log_message('error', "Error obteniendo actividades PVE Biomecánico: " . $e->getMessage());
            return "Error al obtener actividades: " . $e->getMessage();
        }
    }

    private function obtenerIndicadoresPveBiomecanico(int $idCliente, int $anio): string
    {
        if ($idCliente <= 0) {
            return "No se encontraron indicadores (cliente no identificado)";
        }

        try {
            $db = \Config\Database::connect();

            $indicadores = $db->table('tbl_indicadores_sst')
                ->where('id_cliente', $idCliente)
                ->where('categoria', 'pve_biomecanico')
                ->where('activo', 1)
                ->get()
                ->getResultArray();

            if (empty($indicadores)) {
                return "No hay indicadores del PVE Biomecánico configurados";
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
            log_message('error', "Error obteniendo indicadores PVE Biomecánico: " . $e->getMessage());
            return "Error al obtener indicadores: " . $e->getMessage();
        }
    }

    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        try {
            if ($this->configService === null) {
                $this->configService = new DocumentoConfigService();
            }

            $prompt = $this->configService->obtenerPromptSeccion(
                $this->getTipoDocumento(),
                $seccionKey
            );

            if (!empty($prompt)) {
                return str_replace('{ESTANDARES}', (string)$estandares, $prompt);
            }

        } catch (\Exception $e) {
            log_message('warning', "Error obteniendo prompt de BD para {$seccionKey}: " . $e->getMessage());
        }

        return $this->getPromptEstatico($seccionKey, $estandares);
    }

    private function getPromptEstatico(string $seccionKey, int $estandares): string
    {
        $prompts = [
            'introduccion' => "Genera una introducción para el PVE de Riesgo Biomecánico.
Usa el contexto de la empresa y menciona la importancia de la vigilancia epidemiológica para prevenir DME.
Incluye referencia a la Resolución 0312/2019 estándar 4.2.3 y las GATISO de DME.
Máximo 3 párrafos.",

            'objetivo_general' => "Genera el objetivo general del PVE de Riesgo Biomecánico.
Debe ser un objetivo SMART orientado a prevenir desórdenes músculo-esqueléticos.
IMPORTANTE: El objetivo debe estar alineado con las ACTIVIDADES registradas en el Plan de Trabajo.",

            'objetivos_especificos' => "Genera los objetivos específicos del PVE.
IMPORTANTE: Los objetivos deben derivarse de las ACTIVIDADES del PVE listadas en el contexto.
Incluir mínimo 5 objetivos que aborden: diagnóstico, intervención ergonómica, capacitación, seguimiento y evaluación.
Presentar en formato de lista numerada.",

            'alcance' => "Genera el alcance del PVE de Riesgo Biomecánico.
Debe especificar:
- A quiénes aplica (trabajadores expuestos a riesgo biomecánico)
- Qué factores de riesgo cubre (posturas, movimientos repetitivos, manipulación de cargas)
- Dónde aplica (puestos de trabajo con exposición)
- Período de vigencia",

            'marco_legal' => "Genera el marco legal del PVE de Riesgo Biomecánico.
Incluir:
- Resolución 0312 de 2019 (Estándar 4.2.3)
- Decreto 1072 de 2015 (Decreto Único Reglamentario)
- GATISO para DME (Guía de Atención Integral - Ministerio de Protección Social)
- Resolución 2844 de 2007 (Guías de atención)
- Resolución 2346 de 2007 (Evaluaciones médicas ocupacionales)
- NTC 5723 (Ergonomía - Manipulación manual)
- GTC 45 (Identificación de peligros)",

            'definiciones' => "Genera las definiciones técnicas relevantes para el PVE de Riesgo Biomecánico.
Incluir: DME, Riesgo biomecánico, Postura prolongada, Movimiento repetitivo, Manipulación manual de cargas, Ergonomía, Cuestionario Nórdico, Puesto de trabajo, Vigilancia epidemiológica, Pausas activas.",

            'responsabilidades' => "Genera las responsabilidades para el PVE de Riesgo Biomecánico.
Incluir responsabilidades de:
- Representante Legal o Alta Dirección
- Responsable del SG-SST
- COPASST o Vigía de SST
- Trabajadores expuestos a riesgo biomecánico
Las responsabilidades deben estar alineadas con las actividades del Plan de Trabajo.",

            'metodologia' => "Genera la metodología del PVE de Riesgo Biomecánico.
IMPORTANTE: La metodología debe describir CÓMO se ejecutarán las ACTIVIDADES REALES listadas en el contexto.
Estructura en fases:
1. Diagnóstico: Cuestionario Nórdico, evaluación de puestos de trabajo (REBA/RULA/OWAS)
2. Clasificación: Trabajadores según nivel de riesgo (sin riesgo, riesgo bajo, riesgo medio, riesgo alto)
3. Intervención: Controles de ingeniería, administrativos y en el trabajador
4. Seguimiento: Monitoreo de casos, aplicación periódica de encuestas
5. Evaluación: Medición de indicadores y ajuste del programa",

            'cronograma' => "Genera el cronograma de actividades del PVE de Riesgo Biomecánico.
IMPORTANTE: Usa las ACTIVIDADES REALES del Plan de Trabajo listadas en el contexto.
Presenta las actividades con sus meses programados en formato de tabla markdown.
Columnas: Actividad | Responsable | Ene | Feb | Mar | Abr | May | Jun | Jul | Ago | Sep | Oct | Nov | Dic",

            'indicadores' => "Define los indicadores del PVE de Riesgo Biomecánico.
IMPORTANTE: Usa los INDICADORES CONFIGURADOS listados en el contexto.
Presenta cada indicador con: nombre, tipo, fórmula, meta y periodicidad.",

            'recursos' => "Genera la sección de recursos necesarios para el PVE.
Basándote en las ACTIVIDADES del Plan de Trabajo, identifica:
- Recursos humanos (ergónomo, fisioterapeuta, médico ocupacional)
- Recursos físicos (equipos de evaluación ergonómica, mobiliario)
- Recursos financieros
- Recursos tecnológicos (software de evaluación ergonómica)",

            'evaluacion_seguimiento' => "Genera la sección de Evaluación y Seguimiento del PVE.
IMPORTANTE: Usa los INDICADORES listados en el contexto.
Incluye:
- Periodicidad de medición de cada indicador
- Responsable de la medición
- Seguimiento a casos individuales
- Proceso de revisión y mejora continua
- Reportes a generar y su frecuencia"
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la sección '{$seccionKey}' del PVE de Riesgo Biomecánico.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'introduccion' => "{$nombreEmpresa} en cumplimiento de la normatividad legal vigente en materia de Seguridad y Salud en el Trabajo, específicamente el estándar 4.2.3 de la Resolución 0312 de 2019, ha desarrollado el presente Programa de Vigilancia Epidemiológica para Riesgo Biomecánico.\n\nEste programa tiene como propósito fundamental prevenir la aparición de Desórdenes Músculo-Esqueléticos (DME) en los trabajadores expuestos a factores de riesgo biomecánico como posturas prolongadas, movimientos repetitivos y manipulación manual de cargas.\n\nLa vigilancia epidemiológica del riesgo biomecánico permite la detección temprana de síntomas y signos asociados a DME, facilitando la intervención oportuna y la implementación de medidas de control efectivas.",

            'objetivo_general' => "Prevenir la aparición de Desórdenes Músculo-Esqueléticos (DME) en los trabajadores de {$nombreEmpresa} expuestos a factores de riesgo biomecánico, mediante la implementación sistemática de actividades de vigilancia, diagnóstico, intervención y seguimiento, cumpliendo con los requisitos del estándar 4.2.3 de la Resolución 0312 de 2019.",

            'objetivos_especificos' => "1. Identificar y evaluar los factores de riesgo biomecánico presentes en los puestos de trabajo\n2. Detectar tempranamente la sintomatología osteomuscular en los trabajadores expuestos\n3. Implementar controles de ingeniería y administrativos para reducir la exposición\n4. Capacitar a los trabajadores en higiene postural, mecánica corporal y autocuidado\n5. Realizar seguimiento a los casos identificados y evaluar la efectividad de las intervenciones\n6. Medir el programa mediante indicadores de gestión y resultado",

            'alcance' => "El presente PVE aplica a todos los trabajadores de {$nombreEmpresa} expuestos a factores de riesgo biomecánico, incluyendo:\n- Personal administrativo con postura sedente prolongada\n- Personal operativo con manipulación manual de cargas\n- Trabajadores con exposición a movimientos repetitivos\n\nCubre todas las actividades de vigilancia, intervención y seguimiento realizadas en las instalaciones de la empresa.",

            'marco_legal' => "**Normativa aplicable:**\n- Resolución 0312 de 2019: Estándares Mínimos del SG-SST (Estándar 4.2.3)\n- Decreto 1072 de 2015: Decreto Único Reglamentario del Sector Trabajo\n- GATISO DME: Guías de Atención Integral en SST para DME (Ministerio de Protección Social)\n- Resolución 2844 de 2007: Guías de atención integral en SST\n- Resolución 2346 de 2007: Evaluaciones médicas ocupacionales\n- NTC 5723: Ergonomía - Manipulación manual\n- GTC 45: Identificación de peligros y valoración de riesgos",

            'definiciones' => "**DME (Desórdenes Músculo-Esqueléticos):** Lesiones de músculos, tendones, nervios, articulaciones, cartílagos y discos intervertebrales causadas o agravadas por el trabajo.\n\n**Riesgo biomecánico:** Probabilidad de sufrir un evento adverso por exposición a factores como posturas inadecuadas, movimientos repetitivos o manipulación de cargas.\n\n**Postura prolongada:** Mantenimiento de una misma postura por más de 2 horas continuas.\n\n**Movimiento repetitivo:** Movimiento que se repite en ciclos de menos de 30 segundos.\n\n**Cuestionario Nórdico:** Instrumento estandarizado para detección de síntomas osteomusculares.\n\n**Ergonomía:** Disciplina que estudia la relación entre el trabajador y su entorno laboral.",

            'responsabilidades' => "**Representante Legal:**\n- Asignar recursos para el PVE\n- Aprobar las intervenciones ergonómicas\n\n**Responsable del SG-SST:**\n- Coordinar la ejecución del PVE\n- Gestionar evaluaciones ergonómicas\n- Implementar controles y seguimiento\n\n**{$comite}:**\n- Participar en inspecciones ergonómicas\n- Verificar cumplimiento del programa\n\n**Trabajadores:**\n- Participar en evaluaciones y capacitaciones\n- Reportar síntomas osteomusculares oportunamente\n- Aplicar las recomendaciones ergonómicas",

            'metodologia' => "El PVE se desarrolla en las siguientes fases:\n\n**Fase 1 - Diagnóstico:**\nAplicación del Cuestionario Nórdico y evaluación ergonómica de puestos de trabajo\n\n**Fase 2 - Clasificación:**\nClasificación de trabajadores según nivel de riesgo biomecánico\n\n**Fase 3 - Intervención:**\nImplementación de controles de ingeniería, administrativos y en el trabajador\n\n**Fase 4 - Seguimiento:**\nMonitoreo de casos y aplicación periódica de encuestas\n\n**Fase 5 - Evaluación:**\nMedición de indicadores y mejora continua",

            'cronograma' => "El cronograma de actividades se desarrolla según lo establecido en el Plan de Trabajo Anual, incluyendo evaluaciones ergonómicas, capacitaciones en higiene postural, programa de pausas activas y seguimiento a casos DME distribuidos a lo largo del año {$anio}.",

            'indicadores' => "**Indicadores de gestión:**\n\n1. **Cumplimiento de Actividades PVE:**\n   Fórmula: (Actividades ejecutadas / Actividades programadas) x 100\n   Meta: ≥ 90%\n\n2. **Cobertura de Evaluaciones Ergonómicas:**\n   Fórmula: (Puestos evaluados / Puestos con riesgo) x 100\n   Meta: ≥ 80%\n\n3. **Prevalencia de Sintomatología:**\n   Fórmula: (Trabajadores con síntomas / Total evaluados) x 100\n   Meta: ≤ 20%",

            'recursos' => "**Recursos humanos:**\n- Responsable del SG-SST\n- Ergónomo o fisioterapeuta\n- Médico ocupacional o IPS contratada\n\n**Recursos físicos:**\n- Equipos de evaluación ergonómica\n- Mobiliario ergonómico de reemplazo\n\n**Recursos financieros:**\n- Presupuesto para evaluaciones ergonómicas\n- Presupuesto para adecuaciones de puestos de trabajo",

            'evaluacion_seguimiento' => "El seguimiento al PVE se realiza mediante:\n\n- Revisión mensual de avance de actividades\n- Seguimiento trimestral de indicadores\n- Evaluación semestral de sintomatología\n- Análisis anual de cumplimiento de objetivos\n\nLos resultados se documentan y se presentan en la revisión por la dirección."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
