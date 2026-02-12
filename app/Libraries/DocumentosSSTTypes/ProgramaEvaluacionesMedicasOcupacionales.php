<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase ProgramaEvaluacionesMedicasOcupacionales
 *
 * Implementa el Programa de Evaluaciones Medicas Ocupacionales
 * para el estandar 3.1.4 de la Resolucion 0312/2019.
 *
 * Verifica que se realizan las evaluaciones medicas de acuerdo con la
 * normatividad y los peligros, con frecuencia acorde a la magnitud de
 * los riesgos, comunicacion de resultados y articulacion con PVE.
 *
 * TIPO B: Programa de 3 partes (Actividades PTA -> Indicadores -> Documento IA)
 * - Parte 1: Actividades de Evaluaciones Medicas en PTA (tipo_servicio = TIPO_SERVICIO)
 * - Parte 2: Indicadores de Evaluaciones Medicas (categoria = CATEGORIA)
 * - Parte 3: Documento formal generado con IA alimentado por Partes 1 y 2
 *
 * @package App\Libraries\DocumentosSSTTypes
 */
class ProgramaEvaluacionesMedicasOcupacionales extends AbstractDocumentoSST
{
    /** Valor de tipo_servicio en tbl_pta_cliente para este modulo */
    public const TIPO_SERVICIO = 'Evaluaciones Medicas Ocupacionales';

    /** Valor de categoria en tbl_indicadores_sst para este modulo */
    public const CATEGORIA = 'evaluaciones_medicas_ocupacionales';

    protected ?DocumentoConfigService $configService = null;
    protected ?array $configCache = null;

    protected function getConfigService(): DocumentoConfigService
    {
        if ($this->configService === null) {
            $this->configService = new DocumentoConfigService();
        }
        return $this->configService;
    }

    protected function getConfig(): array
    {
        if ($this->configCache === null) {
            $this->configCache = $this->getConfigService()->obtenerTipoDocumento($this->getTipoDocumento());
        }
        return $this->configCache;
    }

    public function getTipoDocumento(): string
    {
        return 'programa_evaluaciones_medicas_ocupacionales';
    }

    public function getNombre(): string
    {
        $config = $this->getConfig();
        return $config['nombre'] ?? 'Programa de Evaluaciones Medicas Ocupacionales';
    }

    public function getDescripcion(): string
    {
        $config = $this->getConfig();
        return $config['descripcion'] ?? 'Establece la realizacion de evaluaciones medicas ocupacionales segun peligros, frecuencia acorde a riesgos, comunicacion de resultados y articulacion con PVE';
    }

    public function getEstandar(): ?string
    {
        return '3.1.4';
    }

    public function getSecciones(): array
    {
        $seccionesBD = $this->getConfigService()->obtenerSecciones($this->getTipoDocumento());

        if (empty($seccionesBD)) {
            return $this->getSeccionesFallback();
        }

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

    protected function getSeccionesFallback(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 4, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
            ['numero' => 5, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            ['numero' => 6, 'nombre' => 'Tipos de Evaluaciones Medicas', 'key' => 'tipos_evaluaciones'],
            ['numero' => 7, 'nombre' => 'Profesiograma y Frecuencia segun Peligros', 'key' => 'profesiograma_frecuencia'],
            ['numero' => 8, 'nombre' => 'Comunicacion de Resultados al Trabajador', 'key' => 'comunicacion_resultados'],
            ['numero' => 9, 'nombre' => 'Restricciones y Recomendaciones Medicas', 'key' => 'restricciones_recomendaciones'],
            ['numero' => 10, 'nombre' => 'Articulacion con Programas de Vigilancia Epidemiologica', 'key' => 'articulacion_pve'],
            ['numero' => 11, 'nombre' => 'Indicadores de Gestion', 'key' => 'indicadores'],
            ['numero' => 12, 'nombre' => 'Cronograma y Seguimiento', 'key' => 'cronograma_seguimiento'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        $config = $this->getConfig();
        if (!empty($config['firmantes'])) {
            return array_column($config['firmantes'], 'firmante_tipo');
        }

        return ['responsable_sst', 'representante_legal'];
    }

    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        $promptBD = $this->getConfigService()->obtenerPromptSeccion($this->getTipoDocumento(), $seccionKey);

        if (!empty($promptBD)) {
            return $promptBD;
        }

        return $this->getPromptFallback($seccionKey, $estandares);
    }

    protected function getPromptFallback(string $seccionKey, int $estandares): string
    {
        $comite = $this->getTextoComite($estandares);

        $prompts = [
            'objetivo' => "Genera el objetivo del Programa de Evaluaciones Medicas Ocupacionales.
Debe mencionar:
- Garantizar la realizacion de evaluaciones medicas ocupacionales de acuerdo con los peligros identificados
- Definir frecuencias de evaluacion acordes con la magnitud de los riesgos y estado de salud
- Asegurar la comunicacion oportuna de resultados a los trabajadores
- Articular con las recomendaciones de los Programas de Vigilancia Epidemiologica
- Cumplimiento de Res. 2346/2007, Decreto 1072/2015 art. 2.2.4.6.24, Res. 0312/2019 est. 3.1.4
Maximo 2 parrafos concisos.",

            'alcance' => "Define el alcance del programa. Debe especificar:
- Aplica a todos los trabajadores directos, contratistas, subcontratistas y temporales
- Cubre evaluaciones de ingreso, periodicas, egreso, cambio de cargo y post-incapacidad
- Las evaluaciones se definen segun peligros identificados en la matriz de peligros
- La frecuencia se ajusta segun la magnitud de los riesgos y el profesiograma
- A quien aplica: alta direccion, responsable SST, medico con licencia SST, IPS, {$comite}, trabajadores
Maximo 2 parrafos.",

            'definiciones' => "Genera las definiciones clave para este programa. INCLUIR OBLIGATORIAMENTE:
- Evaluacion medica ocupacional
- Profesiograma
- Certificado de aptitud
- Aptitud medica
- Evaluacion medica de ingreso
- Evaluacion medica periodica
- Evaluacion medica de egreso
- Evaluacion medica por cambio de cargo
- Evaluacion medica post-incapacidad
- Restriccion medica
- Recomendacion medica
- Vigilancia epidemiologica
- Historia clinica ocupacional
- Descripcion sociodemografica
CANTIDAD: 13-15 definiciones basadas en Res. 2346/2007 y Decreto 1072/2015.",

            'marco_legal' => "Genera el marco legal aplicable. INCLUIR:
- Resolucion 2346 de 2007 (Evaluaciones medicas ocupacionales)
- Resolucion 1918 de 2009 (Custodia de historias clinicas)
- Decreto 1072 de 2015, articulo 2.2.4.6.24 (Medidas de prevencion y control)
- Resolucion 0312 de 2019, estandar 3.1.4 (Evaluaciones medicas ocupacionales)
- Ley 1562 de 2012 (Sistema de Riesgos Laborales)
- Decreto 1443 de 2014 (SG-SST) compilado en Decreto 1072
- Resolucion 2764 de 2022 (Bateria de Riesgo Psicosocial)
- Codigo Sustantivo del Trabajo, art. 348 (Obligaciones del empleador en salud)
Presentar en formato tabla con numero de norma, ano y descripcion breve.",

            'responsabilidades' => "Define las responsabilidades en el programa:

**Alta Direccion / Representante Legal:**
- Garantizar recursos para contratacion de IPS con licencia SST
- Aprobar el programa y el profesiograma
- Facilitar tiempo de los trabajadores para las evaluaciones
- Implementar recomendaciones y restricciones medicas

**Responsable del SG-SST:**
- Coordinar la programacion y ejecucion de evaluaciones medicas
- Asegurar que el profesiograma este actualizado segun peligros
- Entregar resultados a los trabajadores oportunamente
- Hacer seguimiento a restricciones y recomendaciones
- Articular hallazgos con los PVE

**Medico con Licencia SST / IPS:**
- Realizar evaluaciones medicas segun Res. 2346/2007
- Elaborar y actualizar el profesiograma
- Emitir certificados de aptitud y recomendaciones
- Elaborar el diagnostico de condiciones de salud

**{$comite}:**
- Verificar cumplimiento del programa
- Revisar indicadores de evaluaciones medicas
- Participar en el seguimiento a recomendaciones

**Trabajadores:**
- Asistir a las evaluaciones medicas programadas
- Informar verazmente su estado de salud
- Cumplir las restricciones y recomendaciones medicas
- Reportar cambios en su condicion de salud",

            'tipos_evaluaciones' => "Describe los tipos de evaluaciones medicas ocupacionales segun Res. 2346/2007:

**1. Evaluacion Medica de Ingreso:**
- Se realiza ANTES del inicio de labores
- Determina aptitud del trabajador para el cargo
- Examenes segun peligros del cargo (profesiograma)
- Establece linea base de salud del trabajador

**2. Evaluaciones Medicas Periodicas:**
- Programadas segun frecuencia del profesiograma
- Frecuencia acorde a magnitud de riesgos y estado de salud
- Detectar alteraciones tempranas por exposicion ocupacional
- Ajustar frecuencia segun recomendaciones de PVE

**3. Evaluacion Medica de Egreso:**
- Dentro de los 5 dias habiles posteriores al retiro
- Documentar estado de salud al finalizar vinculacion
- No es obligatoria pero si altamente recomendada

**4. Evaluacion Medica por Cambio de Cargo:**
- Cuando cambian las condiciones de exposicion a peligros
- Determinar aptitud para nuevas funciones
- Revisar examenes complementarios necesarios

**5. Evaluacion Medica Post-Incapacidad:**
- Despues de incapacidad prolongada (>30 dias)
- Verificar condiciones de reintegro laboral
- Establecer restricciones temporales o definitivas

Para cada tipo: momento, objetivo, examenes complementarios segun peligros.",

            'profesiograma_frecuencia' => "Describe el profesiograma y la definicion de frecuencias:

**Profesiograma:**
- Herramienta que define examenes medicos por cargo
- Elaborado por medico con licencia SST
- Basado en la matriz de identificacion de peligros
- Contenido: cargo, peligros, examenes de ingreso/periodicos/egreso, paraclinicos

**Definicion de frecuencias segun peligros:**
- Quimico: periodica anual (semestral si riesgo alto)
- Fisico (ruido): audiometria anual o semestral segun NPS
- Biomecanico: valoracion osteomuscular anual
- Psicosocial: evaluacion riesgo psicosocial segun Res. 2764/2022
- Biologico: periodica semestral o segun protocolo de PVE
- Trabajo en alturas: semestral o segun Res. 1409/2012
- Visual (PVD): visiometria anual o bienal

**Ajuste de frecuencias:**
- El medico define la frecuencia segun magnitud del riesgo
- Se ajusta si cambian condiciones de exposicion
- Las recomendaciones de los PVE modifican la frecuencia
- El estado de salud del trabajador puede requerir mayor frecuencia

Presentar como tabla con: tipo de peligro, examenes, frecuencia recomendada.",

            'comunicacion_resultados' => "Describe como se comunican los resultados de las evaluaciones medicas:

**Al trabajador (Res. 2346/2007 art. 16):**
- Certificado de aptitud con recomendaciones
- Entrega dentro de los 5 dias habiles siguientes
- Medio escrito, con acuse de recibo firmado
- Incluye: aptitud, restricciones, recomendaciones, remisiones
- Explicacion clara y comprensible de los resultados

**Al empleador:**
- SOLO se entrega el concepto de aptitud (Apto, Apto con restricciones, No apto)
- NO se entregan diagnosticos especificos (confidencialidad)
- Se informan restricciones y recomendaciones para el puesto
- Se informa necesidad de reubicacion si aplica

**Confidencialidad:**
- Las historias clinicas son de custodia de la IPS, NO del empleador
- El empleador solo conserva certificados de aptitud
- Se aplica la Ley Estatutaria de Habeas Data (Ley 1581/2012)
- Tiempo de conservacion: minimo 20 anos (Res. 2346/2007)

**Registros:**
- Certificados de aptitud con firma del trabajador
- Control de entrega y recepcion de resultados
- Actas de comunicacion de restricciones a jefes de area",

            'restricciones_recomendaciones' => "Describe el manejo de restricciones y recomendaciones medicas:

**Tipos:**
- Restriccion temporal: limitacion con duracion definida
- Restriccion permanente: limitacion sin fecha de levantamiento
- Recomendacion: sugerencia para mejorar condiciones laborales
- Remision: derivacion a especialista o EPS

**Procedimiento:**
- Registro de restricciones emitidas por el medico
- Comunicacion inmediata al trabajador y jefe de area
- Implementacion de medidas de ajuste en el puesto de trabajo
- Seguimiento mensual al cumplimiento de restricciones
- Reubicacion laboral cuando las restricciones lo requieran

**Seguimiento:**
- Control de restricciones vigentes por trabajador
- Verificacion de cumplimiento en puesto de trabajo
- Reevaluacion periodica de restricciones temporales
- Registro de levantamiento cuando el medico lo autorice

**Articulacion:**
- Con ARL para procesos de rehabilitacion
- Con EPS para tratamientos medicos
- Con PVE para ajustar intervenciones",

            'articulacion_pve' => "Describe la articulacion con los Programas de Vigilancia Epidemiologica:

**Fuentes de informacion para PVE:**
- Resultados de evaluaciones medicas (hallazgos, tendencias)
- Diagnostico de condiciones de salud
- Estadisticas de morbilidad y ausentismo
- Restricciones y recomendaciones medicas

**Retroalimentacion de PVE al programa:**
- Ajuste de frecuencias de evaluaciones periodicas
- Inclusion de nuevos examenes complementarios
- Priorizacion de cargos y trabajadores para evaluacion
- Modificacion del profesiograma segun nuevos hallazgos

**PVE que se alimentan de evaluaciones medicas:**
- PVE Osteomuscular (riesgo biomecanico)
- PVE Conservacion Auditiva (ruido)
- PVE Conservacion Visual
- PVE Riesgo Cardiovascular
- PVE Riesgo Psicosocial
- PVE Riesgo Quimico

**Ciclo de mejora continua:**
1. Evaluaciones medicas detectan hallazgos
2. PVE recibe datos y analiza tendencias
3. PVE emite recomendaciones
4. Se ajustan evaluaciones medicas segun recomendaciones
5. Se miden resultados de las intervenciones",

            'indicadores' => "Genera los indicadores de gestion del programa:

**Indicadores de Proceso:**
- Cobertura de evaluaciones de ingreso (meta: 100%)
- Cumplimiento de evaluaciones periodicas segun profesiograma (meta >= 90%)
- Comunicacion oportuna de resultados en 5 dias (meta >= 95%)
- Cumplimiento de restricciones y recomendaciones (meta: 100%)
- Cobertura de evaluaciones de egreso (meta >= 90%)

**Indicadores de Resultado:**
- Prevalencia de enfermedad laboral diagnosticada
- Porcentaje de trabajadores aptos sin restriccion (meta >= 80%)
- Variacion en la incidencia de enfermedad laboral
- Cumplimiento de recomendaciones de PVE

Cada indicador debe tener: nombre, formula, meta, frecuencia de medicion, responsable.",

            'cronograma_seguimiento' => "Genera el cronograma anual y mecanismo de seguimiento:

**Trimestre 1 (Enero - Marzo):**
- Actualizacion del profesiograma
- Programacion anual de evaluaciones periodicas
- Evaluaciones de ingreso (segun contrataciones)
- Seguimiento a restricciones vigentes

**Trimestre 2 (Abril - Junio):**
- Ejecucion de evaluaciones periodicas (primera ronda)
- Comunicacion de resultados y restricciones
- Articulacion con PVE
- Revision de indicadores de proceso

**Trimestre 3 (Julio - Septiembre):**
- Evaluaciones periodicas (segunda ronda si aplica)
- Actualizacion de diagnostico de condiciones de salud
- Seguimiento a cumplimiento de recomendaciones
- Evaluaciones por cambio de cargo si aplica

**Trimestre 4 (Octubre - Diciembre):**
- Evaluacion de indicadores anuales
- Evaluaciones de egreso pendientes
- Informe anual de evaluaciones medicas
- Planificacion del siguiente periodo

**Registros requeridos:**
- Profesiograma vigente
- Certificados de aptitud
- Control de entrega de resultados
- Registro de restricciones y seguimiento
- Informes trimestrales y anual"
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' del Programa de Evaluaciones Medicas Ocupacionales segun Res. 0312/2019 (estandar 3.1.4), Res. 2346/2007 y Decreto 1072/2015.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Garantizar la realizacion sistematica de evaluaciones medicas ocupacionales para todos los trabajadores de {$nombreEmpresa}, de acuerdo con los peligros a los que se encuentran expuestos y con frecuencia acorde a la magnitud de los riesgos, el estado de salud del trabajador y las recomendaciones de los Programas de Vigilancia Epidemiologica.\n\nEste programa da cumplimiento al estandar 3.1.4 de la Resolucion 0312 de 2019, la Resolucion 2346 de 2007 y el articulo 2.2.4.6.24 del Decreto 1072 de 2015, asegurando la comunicacion oportuna de resultados a los trabajadores.",

            'alcance' => "Este programa aplica a todos los trabajadores directos, contratistas, subcontratistas y temporales vinculados a {$nombreEmpresa}. Cubre las evaluaciones medicas de ingreso, periodicas, egreso, cambio de cargo y post-incapacidad.\n\nLas evaluaciones se realizan segun los peligros identificados en la matriz de peligros, con frecuencia definida por el profesiograma y ajustada segun la magnitud de los riesgos y las recomendaciones de los PVE.",

            'definiciones' => "**Evaluacion medica ocupacional:** Acto medico que busca determinar las condiciones de salud del trabajador en relacion con los factores de riesgo laborales.\n\n**Profesiograma:** Herramienta que define los examenes medicos requeridos por cargo segun los peligros identificados.\n\n**Certificado de aptitud:** Documento que indica la condicion de aptitud del trabajador para desempenar un cargo especifico.\n\n**Restriccion medica:** Limitacion parcial o total para realizar actividades laborales especificas.\n\n**Recomendacion medica:** Sugerencia del medico para mejorar las condiciones laborales del trabajador.\n\n**Vigilancia epidemiologica:** Sistema de seguimiento sistematico de las condiciones de salud de la poblacion trabajadora.",

            'marco_legal' => "**Resolucion 2346 de 2007:**\n- Evaluaciones medicas ocupacionales obligatorias.\n\n**Resolucion 1918 de 2009:**\n- Custodia de historias clinicas ocupacionales.\n\n**Decreto 1072 de 2015:**\n- Articulo 2.2.4.6.24: Medidas de prevencion y control en SST.\n\n**Resolucion 0312 de 2019:**\n- Estandar 3.1.4: Evaluaciones medicas ocupacionales.\n\n**Ley 1562 de 2012:**\n- Sistema General de Riesgos Laborales.",

            'responsabilidades' => "**Alta Direccion:**\n- Garantizar recursos para evaluaciones medicas\n- Implementar restricciones y recomendaciones\n\n**Responsable del SG-SST:**\n- Coordinar evaluaciones medicas\n- Asegurar profesiograma actualizado\n- Comunicar resultados oportunamente\n\n**Medico con Licencia SST / IPS:**\n- Realizar evaluaciones segun Res. 2346/2007\n- Elaborar profesiograma y diagnostico de condiciones de salud\n\n**{$comite}:**\n- Verificar cumplimiento del programa\n- Revisar indicadores\n\n**Trabajadores:**\n- Asistir a evaluaciones programadas\n- Informar verazmente su estado de salud\n- Cumplir restricciones medicas",

            'tipos_evaluaciones' => "**Evaluacion de Ingreso:** Antes de iniciar labores, determina aptitud.\n\n**Evaluaciones Periodicas:** Frecuencia segun profesiograma y peligros.\n\n**Evaluacion de Egreso:** Dentro de 5 dias habiles post-retiro.\n\n**Evaluacion por Cambio de Cargo:** Al cambiar condiciones de exposicion.\n\n**Evaluacion Post-Incapacidad:** Despues de incapacidad prolongada.",

            'profesiograma_frecuencia' => "El profesiograma es elaborado por medico con licencia SST, basado en la matriz de peligros. Define examenes por cargo y frecuencia segun nivel de riesgo:\n\n- Riesgo quimico: periodica anual\n- Ruido: audiometria anual\n- Biomecanico: valoracion osteomuscular anual\n- Trabajo en alturas: semestral\n- Visual: visiometria anual",

            'comunicacion_resultados' => "**Al trabajador:** Certificado de aptitud con recomendaciones, dentro de 5 dias habiles.\n\n**Al empleador:** Solo concepto de aptitud (NO diagnosticos). Restricciones y recomendaciones para el puesto.\n\n**Confidencialidad:** Historias clinicas bajo custodia de la IPS. Conservacion minimo 20 anos.",

            'restricciones_recomendaciones' => "**Registro:** Todas las restricciones se documentan formalmente.\n\n**Comunicacion:** Se informa al trabajador y jefe de area.\n\n**Seguimiento:** Verificacion mensual del cumplimiento.\n\n**Reubicacion:** Cuando las restricciones lo requieran.",

            'articulacion_pve' => "Los hallazgos de evaluaciones medicas alimentan los PVE. Las recomendaciones de los PVE ajustan la frecuencia y tipo de evaluaciones medicas. Ciclo de mejora continua entre ambos programas.",

            'indicadores' => "**Indicadores de proceso:**\n- Cobertura evaluaciones ingreso (meta: 100%)\n- Cumplimiento evaluaciones periodicas (meta >= 90%)\n- Comunicacion oportuna de resultados (meta >= 95%)\n\n**Indicadores de resultado:**\n- Prevalencia enfermedad laboral\n- Trabajadores aptos sin restriccion (meta >= 80%)",

            'cronograma_seguimiento' => "**T1:** Actualizacion profesiograma, programacion anual.\n**T2:** Ejecucion evaluaciones periodicas, comunicacion resultados.\n**T3:** Segunda ronda evaluaciones, actualizacion diagnostico salud.\n**T4:** Evaluacion indicadores, informe anual, planificacion."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }

    /**
     * Sobrescribe getContextoBase para incluir datos de actividades e indicadores
     * CRITICO: Alimenta la IA con datos reales de las fases previas (Parte 1 y Parte 2)
     */
    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $nit = $cliente['nit'] ?? '';
        $actividadEconomica = $contexto['actividad_economica_principal'] ?? 'No especificada';
        $nivelRiesgo = $contexto['nivel_riesgo_arl'] ?? $contexto['nivel_riesgo'] ?? 'No especificado';
        $numTrabajadores = $contexto['total_trabajadores'] ?? $contexto['numero_trabajadores'] ?? 'No especificado';
        $estandares = $contexto['estandares_aplicables'] ?? 7;
        $idCliente = $cliente['id_cliente'] ?? 0;
        $anio = (int) date('Y');

        $nivelTexto = match(true) {
            $estandares <= 7 => 'basico (hasta 10 trabajadores, riesgo I, II o III)',
            $estandares <= 21 => 'intermedio (11 a 50 trabajadores, riesgo I, II o III)',
            default => 'avanzado (mas de 50 trabajadores o riesgo IV y V)'
        };

        // =====================================================================
        // OBTENER ACTIVIDADES DE EVALUACIONES MEDICAS DEL PTA (FASE 1)
        // =====================================================================
        $actividadesTexto = $this->obtenerActividadesEvaluacionesMedicas($idCliente, $anio);

        // =====================================================================
        // OBTENER INDICADORES DE EVALUACIONES MEDICAS (FASE 2)
        // =====================================================================
        $indicadoresTexto = $this->obtenerIndicadoresEvaluacionesMedicas($idCliente);

        return "CONTEXTO DE LA EMPRESA:
- Nombre: {$nombreEmpresa}
- NIT: {$nit}
- Actividad economica: {$actividadEconomica}
- Nivel de riesgo: {$nivelRiesgo}
- Numero de trabajadores: {$numTrabajadores}
- Estandares aplicables: {$estandares} ({$nivelTexto})

============================================================
ACTIVIDADES DE EVALUACIONES MEDICAS OCUPACIONALES (FASE 1)
Estas son las actividades REALES registradas en el Plan de Trabajo:
============================================================
{$actividadesTexto}

============================================================
INDICADORES DE EVALUACIONES MEDICAS OCUPACIONALES (FASE 2)
Estos son los indicadores CONFIGURADOS para medir el programa:
============================================================
{$indicadoresTexto}

============================================================
INSTRUCCIONES DE GENERACION:
============================================================
- IMPORTANTE: Usa las actividades e indicadores listados arriba como base para el documento
- Los OBJETIVOS deben estar alineados con las actividades registradas
- El CRONOGRAMA debe reflejar las actividades del Plan de Trabajo
- Los INDICADORES del documento deben corresponder con los configurados
- Personaliza el contenido para esta empresa especifica
- Ajusta la extension y complejidad segun el nivel de estandares
- Las evaluaciones medicas deben estar alineadas con los PELIGROS identificados
- La FRECUENCIA de evaluaciones debe corresponder con la MAGNITUD de los riesgos
- SIEMPRE incluir la comunicacion de resultados al trabajador
- Articular con recomendaciones de Programas de Vigilancia Epidemiologica
- Usa terminologia de la normativa colombiana (Res. 2346/2007, Res. 0312/2019, Decreto 1072/2015)
- NO uses tablas Markdown a menos que se indique especificamente
- Manten un tono profesional y tecnico";
    }

    /**
     * Obtiene las actividades de Evaluaciones Medicas del Plan de Trabajo
     */
    private function obtenerActividadesEvaluacionesMedicas(int $idCliente, int $anio): string
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
                    ->where('tipo_servicio', self::TIPO_SERVICIO)
                    ->orLike('tipo_servicio', 'Evaluaciones Medicas', 'both')
                    ->orLike('tipo_servicio', 'Examenes Medicos', 'both')
                    ->orLike('actividad_plandetrabajo', 'evaluacion medica', 'both')
                    ->orLike('actividad_plandetrabajo', 'examen medico', 'both')
                    ->orLike('actividad_plandetrabajo', 'profesiograma', 'both')
                    ->orLike('actividad_plandetrabajo', 'aptitud medica', 'both')
                    ->orLike('actividad_plandetrabajo', 'evaluaciones periodicas', 'both')
                    ->orLike('actividad_plandetrabajo', 'diagnostico condiciones de salud', 'both')
                    ->orLike('actividad_plandetrabajo', 'restricciones medicas', 'both')
                    ->orLike('actividad_plandetrabajo', 'recomendaciones medicas', 'both')
                ->groupEnd()
                ->orderBy('fecha_propuesta', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($actividades)) {
                return "No hay actividades de Evaluaciones Medicas Ocupacionales registradas para el ano {$anio}";
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
            log_message('error', "Error obteniendo actividades Evaluaciones Medicas: " . $e->getMessage());
            return "Error al obtener actividades: " . $e->getMessage();
        }
    }

    /**
     * Obtiene los indicadores de Evaluaciones Medicas configurados
     */
    private function obtenerIndicadoresEvaluacionesMedicas(int $idCliente): string
    {
        if ($idCliente <= 0) {
            return "No se encontraron indicadores (cliente no identificado)";
        }

        try {
            $db = \Config\Database::connect();

            $indicadores = $db->table('tbl_indicadores_sst')
                ->where('id_cliente', $idCliente)
                ->where('categoria', self::CATEGORIA)
                ->where('activo', 1)
                ->get()
                ->getResultArray();

            if (empty($indicadores)) {
                return "No hay indicadores de Evaluaciones Medicas Ocupacionales configurados";
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
            log_message('error', "Error obteniendo indicadores Evaluaciones Medicas: " . $e->getMessage());
            return "Error al obtener indicadores: " . $e->getMessage();
        }
    }
}
