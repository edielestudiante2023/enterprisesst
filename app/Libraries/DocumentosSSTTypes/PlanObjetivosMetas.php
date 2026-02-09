<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase PlanObjetivosMetas
 *
 * Implementa la generación del Plan de Objetivos y Metas del SG-SST (Estándar 2.2.1)
 *
 * PARTE 3 del módulo de 3 partes:
 * - CONSUME los objetivos de Parte 1 (tbl_pta_cliente tipo_servicio='Objetivos SG-SST')
 * - CONSUME los indicadores de Parte 2 (tbl_indicadores_sst categoria='objetivos_sgsst')
 * - Genera el documento formal con datos REALES de la BD
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class PlanObjetivosMetas extends AbstractDocumentoSST
{
    private ?DocumentoConfigService $configService = null;

    protected const TIPO_SERVICIO_OBJETIVOS = 'Objetivos SG-SST';
    protected const CATEGORIA_INDICADORES = 'objetivos_sgsst';

    public function getTipoDocumento(): string
    {
        return 'plan_objetivos_metas';
    }

    public function getNombre(): string
    {
        return 'Plan de Objetivos y Metas del SG-SST';
    }

    public function getDescripcion(): string
    {
        return 'Define los objetivos del Sistema de Gestión de Seguridad y Salud en el Trabajo, con sus metas cuantificables e indicadores de medición. Cumple con el estándar 2.2.1 de la Resolución 0312/2019.';
    }

    public function getEstandar(): ?string
    {
        return '2.2.1';
    }

    protected function getConfigService(): DocumentoConfigService
    {
        if ($this->configService === null) {
            $this->configService = new DocumentoConfigService();
        }
        return $this->configService;
    }

    public function getSecciones(): array
    {
        // Intentar obtener secciones desde BD
        $seccionesBD = $this->getConfigService()->obtenerSecciones($this->getTipoDocumento());
        if (!empty($seccionesBD)) {
            $secciones = [];
            foreach ($seccionesBD as $s) {
                $secciones[] = [
                    'numero' => (int)($s['numero'] ?? 0),
                    'nombre' => $s['nombre'] ?? '',
                    'key' => $s['key'] ?? $s['seccion_key'] ?? ''
                ];
            }
            return $secciones;
        }

        // Fallback con secciones hardcodeadas
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 4, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            ['numero' => 5, 'nombre' => 'Marco Normativo', 'key' => 'marco_normativo'],
            ['numero' => 6, 'nombre' => 'Objetivos del SG-SST', 'key' => 'objetivos_sgsst'],
            ['numero' => 7, 'nombre' => 'Indicadores de Medición', 'key' => 'indicadores_medicion'],
            ['numero' => 8, 'nombre' => 'Seguimiento y Evaluación', 'key' => 'seguimiento_evaluacion'],
            ['numero' => 9, 'nombre' => 'Revisión y Actualización', 'key' => 'revision_actualizacion'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['responsable_sst', 'representante_legal'];
    }

    /**
     * Sobrescribe getContextoBase para incluir datos de objetivos e indicadores
     * CRÍTICO: Esto alimenta la IA con los datos reales de las fases previas
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

        // =====================================================================
        // OBTENER OBJETIVOS DEL SG-SST (PARTE 1)
        // =====================================================================
        $objetivosTexto = $this->obtenerObjetivosSgsst($idCliente, $anio);

        // =====================================================================
        // OBTENER INDICADORES DE OBJETIVOS (PARTE 2)
        // =====================================================================
        $indicadoresTexto = $this->obtenerIndicadoresObjetivos($idCliente);

        // Construir contexto completo
        return "CONTEXTO DE LA EMPRESA:
- Nombre: {$nombreEmpresa}
- NIT: {$nit}
- Actividad económica: {$actividadEconomica}
- Nivel de riesgo: {$nivelRiesgo}
- Número de trabajadores: {$numTrabajadores}
- Estándares aplicables: {$estandares} ({$nivelTexto})

============================================================
DATOS DEL MÓDULO (Parte 1) - OBJETIVOS DEL SG-SST
Estos son los objetivos REALES definidos para el cliente:
============================================================
{$objetivosTexto}

============================================================
INDICADORES (Parte 2) - INDICADORES DE OBJETIVOS
Estos son los indicadores CONFIGURADOS para medir los objetivos:
============================================================
{$indicadoresTexto}

============================================================
INSTRUCCIONES DE GENERACIÓN:
============================================================
- IMPORTANTE: Usa ÚNICAMENTE los objetivos e indicadores listados arriba
- NO inventes objetivos ni indicadores que no estén en el contexto
- Si no hay datos, indica que deben completarse las fases anteriores
- Cada objetivo debe presentarse con su meta e indicador asociado
- Personaliza el contenido para esta empresa específica
- Ajusta la extensión según el nivel de estándares
- Usa terminología de la normativa colombiana (Resolución 0312/2019, Decreto 1072/2015)
- NO uses tablas Markdown a menos que se indique específicamente
- Mantén un tono profesional y técnico";
    }

    /**
     * Obtiene los objetivos del SG-SST definidos en Parte 1
     */
    private function obtenerObjetivosSgsst(int $idCliente, int $anio): string
    {
        if ($idCliente <= 0) {
            return "⚠️ NO HAY DATOS. Complete primero la Parte 1 (Objetivos).";
        }

        try {
            $db = \Config\Database::connect();

            $objetivos = $db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('tipo_servicio', self::TIPO_SERVICIO_OBJETIVOS)
                ->where('YEAR(fecha_propuesta)', $anio)
                ->where('estado_actividad !=', 'CERRADA')
                ->orderBy('fecha_propuesta', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($objetivos)) {
                return "⚠️ NO HAY OBJETIVOS DEFINIDOS para el año {$anio}.\nComplete primero la Parte 1 en: Generador IA > Objetivos SG-SST";
            }

            $texto = "Total: " . count($objetivos) . " objetivos\n\n";

            foreach ($objetivos as $i => $obj) {
                $num = $i + 1;
                $actividad = $obj['actividad_plandetrabajo'] ?? 'Sin nombre';
                $responsable = $obj['responsable_sugerido_plandetrabajo'] ?? 'Responsable SST';
                $phva = $obj['phva_plandetrabajo'] ?? 'PLANEAR';
                $estado = $obj['estado_actividad'] ?? 'ABIERTA';

                // Parsear objetivo y meta si viene en formato "Objetivo - Descripcion | Meta: ..."
                $partes = explode(' | Meta: ', $actividad);
                $objetivo = $partes[0];
                $meta = $partes[1] ?? 'Por definir';

                $texto .= "{$num}. {$objetivo}\n";
                if (!empty($partes[1])) {
                    $texto .= "   - Meta: {$meta}\n";
                }
                $texto .= "   - Responsable: {$responsable}\n";
                $texto .= "   - Ciclo PHVA: {$phva}\n";
                $texto .= "   - Estado: {$estado}\n\n";
            }

            return $texto;

        } catch (\Exception $e) {
            log_message('error', "Error obteniendo objetivos SG-SST: " . $e->getMessage());
            return "Error al obtener objetivos: " . $e->getMessage();
        }
    }

    /**
     * Obtiene los indicadores de objetivos configurados en Parte 2
     */
    private function obtenerIndicadoresObjetivos(int $idCliente): string
    {
        if ($idCliente <= 0) {
            return "⚠️ NO HAY INDICADORES. Complete primero la Parte 2.";
        }

        try {
            $db = \Config\Database::connect();

            $indicadores = $db->table('tbl_indicadores_sst')
                ->where('id_cliente', $idCliente)
                ->where('categoria', self::CATEGORIA_INDICADORES)
                ->where('activo', 1)
                ->orderBy('tipo_indicador', 'ASC')
                ->orderBy('nombre_indicador', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($indicadores)) {
                return "⚠️ NO HAY INDICADORES CONFIGURADOS.\nComplete primero la Parte 2 en: Generador IA > Indicadores de Objetivos";
            }

            $texto = "Total: " . count($indicadores) . " indicadores\n\n";

            // Agrupar por tipo
            $porTipo = ['resultado' => [], 'proceso' => [], 'estructura' => []];
            foreach ($indicadores as $ind) {
                $tipo = $ind['tipo_indicador'] ?? 'proceso';
                $porTipo[$tipo][] = $ind;
            }

            foreach ($porTipo as $tipo => $inds) {
                if (!empty($inds)) {
                    $texto .= "INDICADORES DE " . strtoupper($tipo) . ":\n";
                    foreach ($inds as $i => $ind) {
                        $num = $i + 1;
                        $nombre = $ind['nombre_indicador'] ?? 'Sin nombre';
                        $formula = $ind['formula'] ?? 'No definida';
                        $meta = $ind['meta'] ?? 'No definida';
                        $unidad = $ind['unidad_medida'] ?? '';
                        $periodicidad = $ind['periodicidad'] ?? 'No definida';

                        $texto .= "{$num}. {$nombre}\n";
                        $texto .= "   - Fórmula: {$formula}\n";
                        $texto .= "   - Meta: {$meta}{$unidad}\n";
                        $texto .= "   - Periodicidad: {$periodicidad}\n\n";
                    }
                }
            }

            return $texto;

        } catch (\Exception $e) {
            log_message('error', "Error obteniendo indicadores de objetivos: " . $e->getMessage());
            return "Error al obtener indicadores: " . $e->getMessage();
        }
    }

    /**
     * Obtiene el prompt para una sección (BD primero, luego fallback estático)
     */
    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        // Intentar obtener prompt desde BD
        $promptBD = $this->getConfigService()->obtenerPromptSeccion($this->getTipoDocumento(), $seccionKey);
        if (!empty($promptBD)) {
            return str_replace('{ESTANDARES}', (string)$estandares, $promptBD);
        }

        // Fallback con prompts hardcodeados
        return $this->getPromptEstatico($seccionKey, $estandares);
    }

    /**
     * Prompts estáticos de fallback
     */
    private function getPromptEstatico(string $seccionKey, int $estandares): string
    {
        $comite = $this->getTextoComite($estandares);

        $prompts = [
            'objetivo' => "Genera el objetivo del Plan de Objetivos y Metas del SG-SST.
Debe establecer:
- El propósito de definir objetivos claros, medibles y cuantificables en SST
- La importancia de establecer metas e indicadores de cumplimiento
- Referencia al cumplimiento del estándar 2.2.1 de la Resolución 0312/2019
- La alineación con la política de SST y los peligros identificados
Máximo 2 párrafos concisos.",

            'alcance' => "Define el alcance del Plan de Objetivos y Metas en SST.
Debe especificar:
- Que aplica a todos los trabajadores directos e indirectos
- Incluye contratistas y subcontratistas
- Cubre todos los procesos, actividades y sedes
- Vigencia anual con revisión periódica
Máximo 2 párrafos.",

            'definiciones' => "Define los términos clave para el Plan de Objetivos y Metas.
INCLUIR OBLIGATORIAMENTE:
- Objetivo de SST
- Meta
- Indicador
- Indicador de estructura
- Indicador de proceso
- Indicador de resultado
- Plan de trabajo anual
- Ciclo PHVA
- Mejora continua
- Eficacia
Formato: término en negrita seguido de definición. Máximo 10 definiciones.",

            'responsabilidades' => "Define las responsabilidades de cada actor en el cumplimiento de objetivos del SG-SST.

**Alta Dirección:**
- Aprobar los objetivos y metas del SG-SST
- Asignar recursos para el cumplimiento de objetivos
- Revisar periódicamente el avance de las metas

**Responsable del SG-SST:**
- Formular y proponer los objetivos y metas
- Diseñar y gestionar los indicadores de medición
- Realizar seguimiento periódico al cumplimiento
- Generar informes de avance y desviaciones

**{$comite}:**
- Participar en la formulación de objetivos
- Verificar el cumplimiento de las metas
- Proponer acciones de mejora

**Trabajadores:**
- Conocer los objetivos del SG-SST
- Contribuir al cumplimiento de las metas
- Reportar condiciones que afecten el cumplimiento",

            'marco_normativo' => "Describe el marco normativo aplicable al Plan de Objetivos y Metas del SG-SST.

INCLUIR:
- **Decreto 1072 de 2015:** Artículo 2.2.4.6.18 sobre objetivos de SST
- **Resolución 0312 de 2019:** Estándar 2.2.1 - Objetivos definidos, claros, medibles, cuantificables con metas, documentados, revisados anualmente y comunicados
- **ISO 45001:2018:** Requisitos para objetivos y planificación del SST
- Indicar requisitos específicos de cada norma sobre objetivos
Extensión: 2-3 párrafos.",

            'objetivos_sgsst' => "Genera la sección de Objetivos del SG-SST.

IMPORTANTE:
- Usa ÚNICAMENTE los objetivos listados en 'DATOS DEL MÓDULO (Parte 1)'
- NO inventes objetivos que no estén en el contexto

Para cada objetivo presenta:
1. **Nombre del objetivo**
2. Descripción o justificación breve
3. Meta cuantificable asociada
4. Responsable de seguimiento
5. Plazo de cumplimiento (anual)
6. Ciclo PHVA al que pertenece

Si no hay datos en el contexto, indica que deben completarse las fases anteriores del módulo.",

            'indicadores_medicion' => "Genera la sección de Indicadores de Medición de Objetivos.

IMPORTANTE:
- Usa ÚNICAMENTE los indicadores listados en 'INDICADORES (Parte 2)'
- NO inventes indicadores que no estén en el contexto

Para cada indicador presenta:
1. **Nombre del indicador**
2. Tipo (estructura, proceso o resultado)
3. Fórmula de cálculo
4. Meta establecida
5. Periodicidad de medición
6. Fuente de datos
7. Responsable de medición

Agrupa los indicadores por tipo (estructura, proceso, resultado).
Si no hay datos en el contexto, indica que deben completarse las fases anteriores.",

            'seguimiento_evaluacion' => "Genera la sección de Seguimiento y Evaluación que incluya:
- Frecuencia de medición de cada tipo de indicador:
  * Indicadores de estructura: anual
  * Indicadores de proceso: trimestral
  * Indicadores de resultado: mensual
- Responsables del seguimiento
- Mecanismos de reporte (tableros de control, informes)
- Revisión en reuniones del {$comite}
- Acciones ante desviaciones de metas
- Comunicación de resultados a partes interesadas

Personaliza según el tamaño de la empresa ({$estandares} estándares). Extensión: 2-3 párrafos.",

            'revision_actualizacion' => "Genera la sección de Revisión y Actualización que defina:
- Periodicidad de revisión de objetivos (mínimo anual)
- Criterios para modificar objetivos o metas durante el año
- Eventos que disparan una revisión extraordinaria:
  * Accidentes graves o mortales
  * Cambios en la normatividad
  * Cambios significativos en procesos
  * Nuevos peligros identificados
- Registro de cambios en el control de versiones
- Aprobación por la Alta Dirección
- Comunicación de cambios a trabajadores

Extensión: 1-2 párrafos concisos."
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la sección '{$seccionKey}' del Plan de Objetivos y Metas del SG-SST según la Resolución 0312/2019 y el Decreto 1072/2015.";
    }

    /**
     * Contenido estático de fallback
     */
    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Establecer los objetivos y metas del Sistema de Gestión de Seguridad y Salud en el Trabajo de {$nombreEmpresa}, definiendo indicadores que permitan medir el cumplimiento y el desempeño del SG-SST para el año {$anio}.\n\nEste documento da cumplimiento al estándar 2.2.1 de la Resolución 0312 de 2019, garantizando que los objetivos sean claros, medibles, cuantificables, con metas definidas, coherentes con el plan de trabajo anual, compatibles con las normas vigentes y comunicados a los trabajadores.",

            'alcance' => "Este Plan de Objetivos y Metas aplica a:\n\n- Todos los trabajadores directos de {$nombreEmpresa}\n- Contratistas y subcontratistas\n- Todos los procesos, actividades y áreas de trabajo\n- Todas las sedes y centros de trabajo\n\nLa vigencia del presente plan es anual, correspondiente al año {$anio}, y será revisado periódicamente para evaluar el cumplimiento de las metas establecidas.",

            'definiciones' => "**Objetivo de SST:** Fin que la organización establece en materia de desempeño de Seguridad y Salud en el Trabajo para alcanzar los resultados previstos.\n\n**Meta:** Valor cuantificable que se pretende alcanzar en un período determinado para cumplir un objetivo.\n\n**Indicador:** Expresión cuantitativa del comportamiento o desempeño de un objetivo o proceso, cuya magnitud al ser comparada con un nivel de referencia señala una desviación sobre la cual se tomarán acciones correctivas.\n\n**Indicador de Estructura:** Mide la disponibilidad de recursos, políticas y capacidad organizacional para SST.\n\n**Indicador de Proceso:** Mide las actividades intermedias del SG-SST que contribuyen al logro de los objetivos.\n\n**Indicador de Resultado:** Mide los cambios alcanzados en el período definido, como consecuencia de las actividades realizadas.\n\n**Plan de Trabajo Anual:** Documento que contiene las actividades a desarrollar durante el año para el cumplimiento de los objetivos del SG-SST.\n\n**Ciclo PHVA:** Metodología de mejora continua: Planear, Hacer, Verificar y Actuar.\n\n**Mejora Continua:** Proceso recurrente de optimización del SG-SST para lograr mejoras en el desempeño.",

            'responsabilidades' => "**Alta Dirección / Representante Legal:**\n- Aprobar los objetivos y metas del SG-SST\n- Asignar los recursos necesarios para el cumplimiento de los objetivos\n- Revisar periódicamente el avance de las metas en la revisión por la dirección\n- Tomar decisiones sobre desviaciones significativas\n\n**Responsable del SG-SST:**\n- Formular y proponer los objetivos y metas alineados con la política de SST\n- Diseñar y gestionar los indicadores de medición\n- Realizar seguimiento periódico al cumplimiento de objetivos\n- Generar informes de avance y análisis de desviaciones\n- Proponer acciones correctivas cuando no se cumplan las metas\n\n**{$comite}:**\n- Participar en la formulación y revisión de objetivos\n- Verificar mensualmente el cumplimiento de las metas\n- Proponer acciones de mejora basadas en los resultados\n- Comunicar a los trabajadores el estado de los objetivos\n\n**Trabajadores:**\n- Conocer los objetivos del SG-SST que les aplican\n- Contribuir activamente al cumplimiento de las metas\n- Reportar condiciones que puedan afectar el cumplimiento\n- Participar en las actividades programadas",

            'marco_normativo' => "El presente Plan de Objetivos y Metas se fundamenta en:\n\n**Decreto 1072 de 2015 - Decreto Único Reglamentario del Sector Trabajo:**\n- Artículo 2.2.4.6.18: Los objetivos deben ser claros, medibles, cuantificables, con metas definidas para su cumplimiento, coherentes con el plan de trabajo anual, compatibles con las normas vigentes y documentados y comunicados a todos los niveles.\n\n**Resolución 0312 de 2019 - Estándares Mínimos del SG-SST:**\n- Estándar 2.2.1: Objetivos definidos, claros, medibles, cuantificables, con metas, documentados, revisados del SG-SST. Los objetivos deben ser revisados y evaluados mínimo una vez al año.\n\n**ISO 45001:2018 - Sistemas de Gestión de SST:**\n- Numeral 6.2: La organización debe establecer objetivos de SST para las funciones y niveles pertinentes, mantenerlos y actualizarlos.",

            'objetivos_sgsst' => "Los objetivos del SG-SST de {$nombreEmpresa} para el año {$anio} son:\n\n**Objetivo 1: Reducir la accidentalidad laboral**\n- Meta: Disminuir en un 10% el índice de frecuencia de accidentes respecto al año anterior\n- Responsable: Responsable del SG-SST\n- Plazo: Diciembre {$anio}\n- Ciclo PHVA: HACER\n\n**Objetivo 2: Cumplir los requisitos legales en SST**\n- Meta: Alcanzar el 100% de cumplimiento en la autoevaluación de estándares mínimos\n- Responsable: Responsable del SG-SST\n- Plazo: Diciembre {$anio}\n- Ciclo PHVA: VERIFICAR\n\n**Objetivo 3: Fortalecer la cultura de autocuidado**\n- Meta: Capacitar al 100% del personal en temas de SST\n- Responsable: Responsable del SG-SST\n- Plazo: Diciembre {$anio}\n- Ciclo PHVA: HACER\n\n*Nota: Estos son objetivos de ejemplo. Complete la Parte 1 del módulo para generar objetivos personalizados.*",

            'indicadores_medicion' => "Los indicadores para medir el cumplimiento de los objetivos son:\n\n**INDICADORES DE RESULTADO:**\n\n1. **Índice de Frecuencia de Accidentes de Trabajo (IFAT)**\n   - Fórmula: (N° AT × 240.000) / HHT\n   - Meta: Reducir 10% respecto al año anterior\n   - Periodicidad: Mensual\n\n2. **Índice de Severidad de Accidentes (ISAT)**\n   - Fórmula: (Días perdidos × 240.000) / HHT\n   - Meta: Reducir 10% respecto al año anterior\n   - Periodicidad: Mensual\n\n**INDICADORES DE PROCESO:**\n\n3. **Cobertura de Capacitación en SST**\n   - Fórmula: (Trabajadores capacitados / Total trabajadores) × 100\n   - Meta: ≥ 100%\n   - Periodicidad: Trimestral\n\n4. **Cumplimiento del Plan de Trabajo**\n   - Fórmula: (Actividades ejecutadas / Actividades programadas) × 100\n   - Meta: ≥ 90%\n   - Periodicidad: Trimestral\n\n**INDICADORES DE ESTRUCTURA:**\n\n5. **Cumplimiento de Estándares Mínimos**\n   - Fórmula: Calificación de autoevaluación\n   - Meta: ≥ 85%\n   - Periodicidad: Anual\n\n*Nota: Estos son indicadores de ejemplo. Complete la Parte 2 del módulo para generar indicadores personalizados.*",

            'seguimiento_evaluacion' => "El seguimiento a los objetivos y metas se realizará mediante:\n\n**Frecuencia de Medición:**\n- Indicadores de resultado: medición mensual\n- Indicadores de proceso: medición trimestral\n- Indicadores de estructura: medición anual\n\n**Responsables:**\n- El Responsable del SG-SST realizará la medición y análisis de indicadores\n- El {$comite} verificará el cumplimiento en sus reuniones mensuales\n- La Alta Dirección revisará los resultados en la revisión por la dirección\n\n**Mecanismos de Reporte:**\n- Tablero de control de indicadores (actualización mensual)\n- Informe trimestral al {$comite}\n- Informe anual en la revisión por la dirección\n\n**Acciones ante Desviaciones:**\nCuando un indicador no alcance la meta establecida, se analizarán las causas raíz y se definirán acciones correctivas con plazos y responsables. El seguimiento a estas acciones se realizará hasta su cierre efectivo.",

            'revision_actualizacion' => "Los objetivos y metas del SG-SST serán revisados:\n\n- **Anualmente:** Como parte de la planificación del SG-SST para el siguiente período, en el marco de la revisión por la Alta Dirección.\n\n- **De manera extraordinaria cuando ocurra:**\n  - Un accidente grave o mortal\n  - Cambios significativos en la normatividad aplicable\n  - Cambios en los procesos o actividades de la empresa\n  - Nuevos peligros identificados\n  - Cuando los indicadores evidencien desviaciones significativas\n\nToda modificación será aprobada por el Representante Legal, documentada en el control de cambios del documento y comunicada a los trabajadores y partes interesadas."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
