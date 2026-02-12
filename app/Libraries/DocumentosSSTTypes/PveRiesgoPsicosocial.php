<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase PveRiesgoPsicosocial
 *
 * Implementa la generación del PVE de Riesgo Psicosocial (4.2.3)
 * Este documento se alimenta de:
 * - Actividades PVE Psicosocial del Plan de Trabajo (Fase 1)
 * - Indicadores PVE Psicosocial configurados (Fase 2)
 *
 * @package App\Libraries\DocumentosSSTTypes
 */
class PveRiesgoPsicosocial extends AbstractDocumentoSST
{
    private ?DocumentoConfigService $configService = null;

    public function getTipoDocumento(): string
    {
        return 'pve_riesgo_psicosocial';
    }

    public function getNombre(): string
    {
        return 'PVE de Riesgo Psicosocial';
    }

    public function getDescripcion(): string
    {
        return 'Programa de Vigilancia Epidemiológica orientado a la prevención e intervención de factores de riesgo psicosocial: estrés laboral, carga mental, relaciones interpersonales, acoso laboral, según Resolución 2646/2008 y Resolución 2764/2022.';
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

        $actividadesTexto = $this->obtenerActividadesPvePsicosocial($idCliente, $anio);
        $indicadoresTexto = $this->obtenerIndicadoresPvePsicosocial($idCliente, $anio);

        return "CONTEXTO DE LA EMPRESA:
- Nombre: {$nombreEmpresa}
- NIT: {$nit}
- Actividad económica: {$actividadEconomica}
- Nivel de riesgo: {$nivelRiesgo}
- Número de trabajadores: {$numTrabajadores}
- Estándares aplicables: {$estandares} ({$nivelTexto})

============================================================
ACTIVIDADES DEL PVE DE RIESGO PSICOSOCIAL (FASE 1)
Estas son las actividades REALES registradas en el Plan de Trabajo:
============================================================
{$actividadesTexto}

============================================================
INDICADORES DEL PVE DE RIESGO PSICOSOCIAL (FASE 2)
Estos son los indicadores CONFIGURADOS para medir el programa:
============================================================
{$indicadoresTexto}

============================================================
INSTRUCCIONES DE GENERACIÓN:
============================================================
- IMPORTANTE: Usa las actividades e indicadores listados arriba como base para el documento
- Este es un PVE de Riesgo Psicosocial enfocado en factores intralaborales y extralaborales
- Los OBJETIVOS deben estar alineados con las actividades registradas
- El CRONOGRAMA debe reflejar las actividades del Plan de Trabajo
- Los INDICADORES del documento deben corresponder con los configurados
- Incluir referencias a la Batería de Riesgo Psicosocial (Res. 2764/2022)
- Mencionar la Resolución 2646/2008 y la Ley 1010/2006 (acoso laboral)
- Personaliza el contenido para esta empresa específica
- Usa terminología de la normativa colombiana
- NO uses tablas Markdown a menos que se indique específicamente
- Mantén un tono profesional y técnico";
    }

    private function obtenerActividadesPvePsicosocial(int $idCliente, int $anio): string
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
                    ->where('tipo_servicio', 'PVE Riesgo Psicosocial')
                    ->orLike('tipo_servicio', 'Psicosocial', 'both')
                    ->orLike('actividad_plandetrabajo', 'psicosocial', 'both')
                    ->orLike('actividad_plandetrabajo', 'bateria', 'both')
                    ->orLike('actividad_plandetrabajo', 'estres laboral', 'both')
                    ->orLike('actividad_plandetrabajo', 'acoso laboral', 'both')
                    ->orLike('actividad_plandetrabajo', 'clima organizacional', 'both')
                ->groupEnd()
                ->orderBy('fecha_propuesta', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($actividades)) {
                return "No hay actividades del PVE Psicosocial registradas para el año {$anio}";
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
            log_message('error', "Error obteniendo actividades PVE Psicosocial: " . $e->getMessage());
            return "Error al obtener actividades: " . $e->getMessage();
        }
    }

    private function obtenerIndicadoresPvePsicosocial(int $idCliente, int $anio): string
    {
        if ($idCliente <= 0) {
            return "No se encontraron indicadores (cliente no identificado)";
        }

        try {
            $db = \Config\Database::connect();

            $indicadores = $db->table('tbl_indicadores_sst')
                ->where('id_cliente', $idCliente)
                ->where('categoria', 'pve_psicosocial')
                ->where('activo', 1)
                ->get()
                ->getResultArray();

            if (empty($indicadores)) {
                return "No hay indicadores del PVE Psicosocial configurados";
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
            log_message('error', "Error obteniendo indicadores PVE Psicosocial: " . $e->getMessage());
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
            'introduccion' => "Genera una introducción para el PVE de Riesgo Psicosocial.
Usa el contexto de la empresa y menciona la importancia de la vigilancia de factores psicosociales.
Incluye referencia a la Resolución 0312/2019 estándar 4.2.3, Resolución 2646/2008 y Resolución 2764/2022.
Máximo 3 párrafos.",

            'objetivo_general' => "Genera el objetivo general del PVE de Riesgo Psicosocial.
Debe ser un objetivo SMART orientado a identificar, evaluar e intervenir factores de riesgo psicosocial.
IMPORTANTE: El objetivo debe estar alineado con las ACTIVIDADES registradas en el Plan de Trabajo.",

            'objetivos_especificos' => "Genera los objetivos específicos del PVE.
IMPORTANTE: Los objetivos deben derivarse de las ACTIVIDADES del PVE listadas en el contexto.
Incluir mínimo 5 objetivos que aborden: diagnóstico con batería, intervención, capacitación, seguimiento a casos y evaluación.
Presentar en formato de lista numerada.",

            'alcance' => "Genera el alcance del PVE de Riesgo Psicosocial.
Debe especificar:
- A quiénes aplica (todos los trabajadores)
- Qué factores cubre (intralaborales, extralaborales, estrés)
- Articulación con el Comité de Convivencia Laboral
- Período de vigencia",

            'marco_legal' => "Genera el marco legal del PVE de Riesgo Psicosocial.
Incluir:
- Resolución 0312 de 2019 (Estándar 4.2.3)
- Resolución 2646 de 2008 (Factores de riesgo psicosocial)
- Resolución 2764 de 2022 (Batería de riesgo psicosocial)
- Ley 1010 de 2006 (Acoso laboral)
- Decreto 1072 de 2015 (Decreto Único Reglamentario)
- Ley 1616 de 2013 (Salud mental)
- Resolución 2404 de 2019 (Guía técnica)",

            'definiciones' => "Genera las definiciones técnicas relevantes para el PVE de Riesgo Psicosocial.
Incluir: Factor de riesgo psicosocial, Factor intralaboral, Factor extralaboral, Estrés laboral, Batería de riesgo psicosocial, Acoso laboral, Burnout, Carga mental, Clima organizacional, Comité de Convivencia Laboral.",

            'responsabilidades' => "Genera las responsabilidades para el PVE de Riesgo Psicosocial.
Incluir responsabilidades de:
- Representante Legal o Alta Dirección
- Responsable del SG-SST
- Psicólogo especialista en SST
- COPASST o Vigía de SST
- Comité de Convivencia Laboral
- Trabajadores",

            'metodologia' => "Genera la metodología del PVE de Riesgo Psicosocial.
IMPORTANTE: La metodología debe describir CÓMO se ejecutarán las ACTIVIDADES REALES listadas en el contexto.
Estructura en fases:
1. Diagnóstico: Aplicación de Batería de Riesgo Psicosocial (Res. 2764/2022)
2. Análisis: Clasificación de resultados por dominios y dimensiones
3. Intervención: Acciones según nivel de riesgo (primaria, secundaria, terciaria)
4. Seguimiento: Monitoreo de casos alto/muy alto, articulación con EPS
5. Evaluación: Medición de indicadores y reaplicación de batería",

            'cronograma' => "Genera el cronograma de actividades del PVE de Riesgo Psicosocial.
IMPORTANTE: Usa las ACTIVIDADES REALES del Plan de Trabajo listadas en el contexto.
Presenta las actividades con sus meses programados en formato de tabla markdown.
Columnas: Actividad | Responsable | Ene | Feb | Mar | Abr | May | Jun | Jul | Ago | Sep | Oct | Nov | Dic",

            'indicadores' => "Define los indicadores del PVE de Riesgo Psicosocial.
IMPORTANTE: Usa los INDICADORES CONFIGURADOS listados en el contexto.
Presenta cada indicador con: nombre, tipo, fórmula, meta y periodicidad.",

            'recursos' => "Genera la sección de recursos necesarios para el PVE.
Basándote en las ACTIVIDADES del Plan de Trabajo, identifica:
- Recursos humanos (psicólogo especialista en SST obligatorio para aplicación de batería)
- Recursos físicos (espacios privados para aplicación)
- Recursos financieros
- Recursos tecnológicos (plataforma para batería)",

            'evaluacion_seguimiento' => "Genera la sección de Evaluación y Seguimiento del PVE.
IMPORTANTE: Usa los INDICADORES listados en el contexto.
Incluye:
- Periodicidad de medición de cada indicador
- Frecuencia de reaplicación de la batería (mínimo cada 2 años según Res. 2764/2022)
- Seguimiento a casos individuales de riesgo alto/muy alto
- Articulación con Comité de Convivencia Laboral
- Proceso de mejora continua"
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la sección '{$seccionKey}' del PVE de Riesgo Psicosocial.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'introduccion' => "{$nombreEmpresa} en cumplimiento de la normatividad legal vigente, específicamente el estándar 4.2.3 de la Resolución 0312 de 2019 y la Resolución 2646 de 2008, ha desarrollado el presente Programa de Vigilancia Epidemiológica para Riesgo Psicosocial.\n\nEste programa tiene como propósito identificar, evaluar e intervenir los factores de riesgo psicosocial que pueden afectar la salud mental y el bienestar de los trabajadores, incluyendo factores intralaborales, extralaborales y niveles de estrés.\n\nLa evaluación de factores de riesgo psicosocial se realiza mediante la aplicación de la Batería de Instrumentos adoptada por la Resolución 2764 de 2022 del Ministerio del Trabajo.",

            'objetivo_general' => "Identificar, evaluar, prevenir e intervenir los factores de riesgo psicosocial en los trabajadores de {$nombreEmpresa}, mediante la aplicación de la Batería de Riesgo Psicosocial y la implementación de actividades de promoción y prevención, cumpliendo con los requisitos de la Resolución 2646 de 2008 y el estándar 4.2.3 de la Resolución 0312 de 2019.",

            'objetivos_especificos' => "1. Aplicar la Batería de Riesgo Psicosocial (Res. 2764/2022) para diagnosticar los factores de riesgo\n2. Analizar los resultados por dominios y dimensiones para priorizar intervenciones\n3. Implementar programas de intervención según los niveles de riesgo identificados\n4. Capacitar a los trabajadores en manejo del estrés y comunicación asertiva\n5. Realizar seguimiento a trabajadores clasificados en riesgo alto o muy alto\n6. Evaluar la efectividad de las intervenciones mediante indicadores de gestión",

            'alcance' => "El presente PVE aplica a todos los trabajadores de {$nombreEmpresa}, incluyendo:\n- Personal administrativo y operativo\n- Jefes y supervisores\n- Contratistas (cuando aplique)\n\nAbarca la evaluación de factores intralaborales, extralaborales y niveles de estrés, articulado con el Comité de Convivencia Laboral.",

            'marco_legal' => "**Normativa aplicable:**\n- Resolución 0312 de 2019: Estándares Mínimos del SG-SST (Estándar 4.2.3)\n- Resolución 2646 de 2008: Factores de riesgo psicosocial en el trabajo\n- Resolución 2764 de 2022: Batería de instrumentos de riesgo psicosocial\n- Ley 1010 de 2006: Medidas para prevenir el acoso laboral\n- Decreto 1072 de 2015: Decreto Único Reglamentario\n- Ley 1616 de 2013: Ley de salud mental\n- Resolución 2404 de 2019: Guía técnica general de riesgo psicosocial",

            'definiciones' => "**Factor de riesgo psicosocial:** Condiciones del trabajo que pueden producir efectos negativos en la salud del trabajador.\n\n**Factor intralaboral:** Condiciones propias del trabajo y su organización.\n\n**Factor extralaboral:** Condiciones externas al trabajo que influyen en la salud.\n\n**Estrés laboral:** Respuesta ante exigencias y presiones laborales que no se ajustan a los conocimientos y capacidades del trabajador.\n\n**Batería de riesgo psicosocial:** Instrumento validado para Colombia que evalúa factores de riesgo psicosocial.\n\n**Acoso laboral:** Toda conducta persistente y demostrable ejercida sobre un empleado.",

            'responsabilidades' => "**Representante Legal:**\n- Asignar recursos para el PVE\n- Garantizar la confidencialidad de los resultados\n\n**Responsable del SG-SST:**\n- Coordinar la aplicación de la batería\n- Implementar el plan de intervención\n\n**Psicólogo Especialista SST:**\n- Aplicar e interpretar la batería (obligatorio)\n- Diseñar intervenciones según resultados\n\n**{$comite}:**\n- Participar en actividades de promoción\n\n**Comité de Convivencia Laboral:**\n- Atender quejas de acoso laboral\n\n**Trabajadores:**\n- Participar en las evaluaciones\n- Reportar situaciones de riesgo psicosocial",

            'metodologia' => "El PVE se desarrolla en las siguientes fases:\n\n**Fase 1 - Diagnóstico:**\nAplicación de la Batería de Riesgo Psicosocial (Res. 2764/2022) por psicólogo especialista\n\n**Fase 2 - Análisis:**\nInterpretación de resultados por dominios y dimensiones\n\n**Fase 3 - Intervención:**\nAcciones según nivel de riesgo identificado\n\n**Fase 4 - Seguimiento:**\nMonitoreo de casos y articulación con EPS\n\n**Fase 5 - Evaluación:**\nMedición de indicadores y reaplicación de batería",

            'cronograma' => "El cronograma de actividades se desarrolla según lo establecido en el Plan de Trabajo Anual, incluyendo la aplicación de la batería, talleres de intervención, capacitaciones y seguimiento a casos distribuidos a lo largo del año {$anio}.",

            'indicadores' => "**Indicadores de gestión:**\n\n1. **Cumplimiento de Actividades PVE:**\n   Fórmula: (Actividades ejecutadas / Actividades programadas) x 100\n   Meta: ≥ 90%\n\n2. **Cobertura de Batería:**\n   Fórmula: (Trabajadores evaluados / Total trabajadores) x 100\n   Meta: ≥ 90%\n\n3. **Proporción Riesgo Alto/Muy Alto:**\n   Fórmula: (Trabajadores alto+muy alto / Total evaluados) x 100\n   Meta: ≤ 15%",

            'recursos' => "**Recursos humanos:**\n- Psicólogo especialista en SST (obligatorio para batería)\n- Responsable del SG-SST\n\n**Recursos físicos:**\n- Espacio privado para aplicación individual\n- Sala para talleres grupales\n\n**Recursos financieros:**\n- Presupuesto para aplicación de batería\n- Presupuesto para talleres de intervención",

            'evaluacion_seguimiento' => "El seguimiento al PVE se realiza mediante:\n\n- Revisión mensual de avance de actividades\n- Seguimiento trimestral de indicadores\n- Seguimiento individual a casos alto/muy alto\n- Reaplicación de batería (mínimo cada 2 años)\n- Análisis anual de cumplimiento de objetivos\n\nLos resultados se documentan y se presentan en la revisión por la dirección."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
