<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase ProcedimientoInvestigacionIncidentes
 *
 * Implementa la generacion del documento de Investigacion de Incidentes,
 * Accidentes de Trabajo y Enfermedades Laborales para el estandar 3.2.2
 * de la Resolucion 0312/2019.
 *
 * Verifica que se investigan TODOS los accidentes e incidentes de trabajo
 * y las enfermedades laborales, determinando causas basicas e inmediatas,
 * evaluando la posibilidad de nuevos casos y realizando seguimiento a las
 * acciones para otros trabajadores potencialmente expuestos.
 *
 * TIPO A: Solo Parte 3 (documento formal con IA, sin actividades PTA ni indicadores)
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class ProcedimientoInvestigacionIncidentes extends AbstractDocumentoSST
{
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
        return 'procedimiento_investigacion_incidentes';
    }

    public function getNombre(): string
    {
        $config = $this->getConfig();
        return $config['nombre'] ?? 'Investigacion de Incidentes, Accidentes de Trabajo y Enfermedades Laborales';
    }

    public function getDescripcion(): string
    {
        $config = $this->getConfig();
        return $config['descripcion'] ?? 'Establece la metodologia para investigar todos los incidentes, accidentes de trabajo y enfermedades laborales, determinando causas basicas e inmediatas y realizando seguimiento a las acciones para trabajadores potencialmente expuestos';
    }

    public function getEstandar(): ?string
    {
        return '3.2.2';
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
            ['numero' => 6, 'nombre' => 'Clasificacion de Eventos Investigables', 'key' => 'clasificacion_eventos'],
            ['numero' => 7, 'nombre' => 'Metodologia de Investigacion', 'key' => 'metodologia_investigacion'],
            ['numero' => 8, 'nombre' => 'Determinacion de Causas Basicas e Inmediatas', 'key' => 'determinacion_causas'],
            ['numero' => 9, 'nombre' => 'Evaluacion de Trabajadores Potencialmente Expuestos', 'key' => 'evaluacion_expuestos'],
            ['numero' => 10, 'nombre' => 'Acciones Correctivas y Seguimiento', 'key' => 'acciones_seguimiento'],
            ['numero' => 11, 'nombre' => 'Indicadores de Gestion', 'key' => 'indicadores'],
            ['numero' => 12, 'nombre' => 'Registros y Evidencias', 'key' => 'registros_evidencias'],
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

        $nivelTexto = match(true) {
            $estandares <= 7 => 'basico (7 estandares)',
            $estandares <= 21 => 'intermedio (21 estandares)',
            default => 'avanzado (60 estandares)'
        };

        $prompts = [
            'objetivo' => "Genera el objetivo del documento de Investigacion de Incidentes, Accidentes de Trabajo y Enfermedades Laborales.
Debe establecer:
- El proposito de investigar TODOS los incidentes, accidentes de trabajo y enfermedades laborales diagnosticadas como laborales
- Determinar causas basicas e inmediatas de cada evento
- Evaluar la posibilidad de que se presenten nuevos casos
- Realizar seguimiento a las acciones y recomendaciones para OTROS TRABAJADORES POTENCIALMENTE EXPUESTOS
- Referencia al Decreto 1072/2015 art. 2.2.4.6.32, Resolucion 0312/2019 estandar 3.2.2, y Resolucion 1401/2007
FORMATO: Maximo 2 parrafos concisos.
TONO: Formal, tecnico, en tercera persona.",

            'alcance' => "Define el alcance de la investigacion de incidentes, accidentes y enfermedades laborales.
Debe especificar:
- Aplica a TODOS los trabajadores directos, contratistas, subcontratistas, temporales y visitantes
- Cubre incidentes, accidentes de trabajo (leves, graves, mortales) y enfermedades laborales diagnosticadas
- Aplica en todas las sedes, centros de trabajo y actividades externas
- Incluye la determinacion de causas y el seguimiento a trabajadores potencialmente expuestos
AJUSTAR segun nivel ({$nivelTexto}).
FORMATO: Lista con vinetas.",

            'definiciones' => "Define los terminos clave para la investigacion de incidentes y accidentes.
TERMINOS OBLIGATORIOS: Accidente de trabajo (Ley 1562/2012 art. 3), Incidente de trabajo, Enfermedad laboral (Ley 1562/2012 art. 4), Accidente grave (Res. 1401/2007 art. 3), Accidente mortal, Causa inmediata, Causa basica, Factor personal, Factor del trabajo, Trabajador potencialmente expuesto, Investigacion de accidentes, FURAT, FUREL, Acto inseguro, Condicion insegura.
CANTIDAD segun estandares:
- 7 estandares: 10-12 terminos esenciales
- 21 estandares: 12-14 terminos
- 60 estandares: 14-16 terminos (agregar: Arbol de causas, Metodologia de los 5 por que, Barrera de seguridad, Lecciones aprendidas)
Formato: termino en **negrita** seguido de dos puntos y definicion.
NO usar tablas Markdown.",

            'marco_legal' => "Presenta el marco normativo aplicable a la investigacion de incidentes, accidentes y enfermedades laborales.
NORMAS OBLIGATORIAS:
- Ley 1562 de 2012 art. 3 y 4 (definiciones AT y EL)
- Decreto 1072 de 2015 art. 2.2.4.6.32 (Investigacion de incidentes, accidentes y enfermedades)
- Decreto 1072 de 2015 art. 2.2.4.6.12 (Documentacion - conservar 20 anos)
- Resolucion 0312 de 2019 estandar 3.2.2 (Investigacion de Accidentes, Incidentes y Enfermedad Laboral)
- Resolucion 1401 de 2007 (Investigacion de incidentes y accidentes de trabajo)
- Decreto 472 de 2015 (Sanciones por no reportar AT mortales)
- Resolucion 156 de 2005 (Formatos FURAT y FUREL)
- Decreto 1530 de 1996 art. 4 (Reporte AT dentro de 2 dias habiles)
Formato tabla: Norma | Descripcion | Articulo relevante.",

            'responsabilidades' => "Define los roles y responsabilidades en la investigacion de incidentes, accidentes y enfermedades laborales.
ROLES segun estandares:
- 7 estandares: 4 roles (Representante Legal, Responsable SST, Vigia SST, Trabajadores)
- 21 estandares: 5-6 roles (agregar Jefes de area/Supervisores, ARL)
- 60 estandares: Todos (agregar Brigada de emergencias, Equipo investigador, Area juridica)
IMPORTANTE para {$estandares} estandares: usar '{$comite}' correctamente.
RESPONSABILIDADES CLAVE:
- Empleador/Rep. Legal: garantizar investigacion de TODOS los eventos, asignar recursos
- Responsable SST: liderar investigacion, determinar causas, evaluar trabajadores expuestos
- {$comite}: participar en investigacion, verificar cumplimiento acciones correctivas
- Jefe inmediato: preservar evidencia, facilitar informacion, implementar acciones inmediatas
- Trabajadores: reportar incidentes/AT inmediatamente, colaborar con la investigacion
- ARL: asesorar en investigacion, capacitar, investigar AT graves y mortales
FORMATO: Rol en **negrita**, seguido de lista de responsabilidades.",

            'clasificacion_eventos' => "Clasifica los eventos que deben ser investigados obligatoriamente.
TIPOS DE EVENTOS:
1. **Incidentes de trabajo**: eventos sin lesion pero con potencial de dano (cuasi-accidentes). Se investigan para prevenir accidentes futuros
2. **Accidentes de trabajo leves**: lesion que NO genera incapacidad o genera incapacidad temporal menor. Investigacion interna
3. **Accidentes de trabajo graves**: lesion que genera hospitalizacion, cirugia, incapacidad permanente, discapacidad. Requiere profesional con licencia SST
4. **Accidentes de trabajo mortales**: fallecimiento del trabajador. Investigacion obligatoria con profesional con licencia SST. Reporte al Ministerio
5. **Enfermedades laborales diagnosticadas**: cuando la EPS, ARL o junta de calificacion determina origen laboral. Evaluacion de TODOS los trabajadores expuestos al mismo riesgo

Para cada tipo: quien investiga, plazo de investigacion, a quien se reporta, documentacion requerida.
IMPORTANTE: El estandar 3.2.2 exige que se investiguen TODOS los eventos sin excepcion.
AJUSTAR segun nivel ({$nivelTexto}).
NO usar tablas Markdown.",

            'metodologia_investigacion' => "Describe la metodologia paso a paso para investigar incidentes, accidentes y enfermedades laborales.
ETAPAS:
1. **Atencion inmediata y preservacion de evidencia**: primeros auxilios, asegurar area, preservar escena, registrar condiciones
2. **Conformacion del equipo investigador**: jefe inmediato + responsable SST + miembro del {$comite}. Para AT graves/mortales: profesional con licencia SST (Res. 1401/2007)
3. **Recopilacion de informacion**: declaracion del accidentado y testigos, inspeccion del lugar, revision documental (procedimientos, capacitaciones, ATS, permisos de trabajo)
4. **Reconstruccion de hechos**: cronologia del evento, secuencia de acciones, condiciones del entorno
5. **Analisis de causas**: aplicar metodologias de analisis para determinar causas inmediatas y basicas (ver seccion 8)
6. **Evaluacion de trabajadores expuestos**: identificar otros trabajadores con exposicion al mismo riesgo (ver seccion 9)
7. **Definicion de acciones correctivas y preventivas**: plan de accion con responsables, plazos y recursos
8. **Elaboracion del informe**: formato completo segun Resolucion 1401/2007

PLAZOS: Investigacion debe iniciar dentro de 15 dias calendario siguientes al evento (Res. 1401/2007 art. 4).
AT graves/mortales: enviar copia del informe a la ARL dentro de 15 dias calendario.
NO usar tablas Markdown.",

            'determinacion_causas' => "Describe las metodologias para determinar causas basicas e inmediatas.
SEGUN Resolucion 1401/2007:
**Causas inmediatas:**
- Actos inseguros/subestandar: comportamientos que desviaron el procedimiento seguro
- Condiciones inseguras/subestandar: circunstancias del ambiente que propiciaron el evento

**Causas basicas:**
- Factores personales: capacidad fisica/mental, conocimiento, motivacion, estres, fatiga
- Factores del trabajo: supervision inadecuada, diseno deficiente, procedimientos inexistentes, desgaste, mantenimiento

**Metodologias de analisis causal:**
SEGUN nivel ({$nivelTexto}):
- 7 estandares: Metodologia de los 5 Por Que (preguntar 'por que' sucesivamente)
- 21 estandares: 5 Por Que + Arbol de causas (diagrama de cadena causal)
- 60 estandares: 5 Por Que + Arbol de causas + Espina de pescado (Ishikawa) con ejemplos aplicados

IMPORTANTE: La determinacion de causas debe ser suficientemente profunda para identificar las causas RAIZ, no solo los sintomas. Esto es requisito fundamental del estandar 3.2.2.
NO usar tablas Markdown.",

            'evaluacion_expuestos' => "Describe como se evalua la posibilidad de que se presenten nuevos casos y como se protege a otros trabajadores potencialmente expuestos.
ESTE ES EL ELEMENTO DIFERENCIADOR DEL ESTANDAR 3.2.2.
INCLUIR:
1. **Identificacion de trabajadores expuestos**: mapear todos los cargos y personas con exposicion al mismo peligro que causo el evento
2. **Evaluacion del riesgo para expuestos**: determinar si los mismos factores de riesgo estan presentes en otros puestos de trabajo
3. **Medidas preventivas para expuestos**: implementar controles adicionales para trabajadores del mismo cargo, area o con exposicion similar
4. **Evaluaciones medicas especificas**: para enfermedades laborales, realizar evaluaciones medicas a los demas trabajadores expuestos al mismo agente
5. **Comunicacion y sensibilizacion**: informar a los trabajadores expuestos sobre los hallazgos, riesgos y medidas adoptadas
6. **Seguimiento periodico**: monitorear la salud y condiciones de los trabajadores potencialmente expuestos
7. **Actualizacion de la matriz de peligros**: incluir los nuevos hallazgos en la identificacion de peligros y valoracion de riesgos

Para enfermedades laborales diagnosticadas: evaluar prevalencia en la poblacion expuesta, activar vigilancia epidemiologica si aplica.
FORMATO: Lista estructurada con subniveles.
NO usar tablas Markdown.",

            'acciones_seguimiento' => "Describe como se definen acciones correctivas/preventivas y como se realiza el seguimiento.
INCLUIR:
**Plan de accion:**
- Para cada causa identificada: accion correctiva/preventiva con jerarquia de controles
- Responsable, fecha limite, recursos necesarios, indicador de cumplimiento
- Acciones inmediatas (24h), corto plazo (15 dias), mediano plazo (30-60 dias)

**Seguimiento:**
- Verificacion semanal de acciones inmediatas
- Verificacion mensual de acciones a mediano plazo
- El {$comite} verifica avance en reuniones ordinarias
- Responsable SST consolida reporte trimestral

**Cierre de investigacion:**
- Solo cuando TODAS las acciones esten implementadas y verificadas
- Evaluacion de eficacia de las medidas adoptadas
- Retroalimentacion a la alta direccion
- Documentar lecciones aprendidas y socializarlas

**Conservacion:** Registros de investigacion y seguimiento por minimo 20 anos (Decreto 1072/2015).
NO usar tablas Markdown.",

            'indicadores' => "Genera indicadores de gestion para la investigacion de incidentes, accidentes y enfermedades laborales.
INDICADORES DE PROCESO:
- Porcentaje de eventos investigados vs. reportados (meta = 100%)
- Tiempo promedio entre ocurrencia del evento e inicio de investigacion (meta <= 2 dias habiles)
- Porcentaje de investigaciones con determinacion de causas raiz (meta = 100%)
- Porcentaje de trabajadores expuestos evaluados post-evento (meta >= 90%)

INDICADORES DE RESULTADO:
- Porcentaje de acciones correctivas cerradas en plazo (meta >= 85%)
- Tasa de recurrencia de eventos por la misma causa (meta = 0%)
- Indice de frecuencia de accidentalidad
- Indice de severidad de accidentalidad
- Porcentaje de lecciones aprendidas socializadas (meta >= 90%)

Para cada indicador: nombre, formula, meta, frecuencia de medicion, responsable.
AJUSTAR segun nivel ({$nivelTexto}).
NO usar tablas Markdown.",

            'registros_evidencias' => "Lista los registros y evidencias que se deben mantener del proceso de investigacion.
REGISTROS OBLIGATORIOS:
- FURAT diligenciado (Formato Unico de Reporte de Accidente de Trabajo)
- FUREL diligenciado (Formato Unico de Reporte de Enfermedad Laboral)
- Informe de investigacion de accidentes (formato Resolucion 1401/2007)
- Declaraciones de testigos y del accidentado
- Registro fotografico del lugar del evento
- Copia del reporte a la ARL (dentro de 2 dias habiles)
- Copia del reporte a la Direccion Territorial (AT graves/mortales)
- Plan de accion con acciones correctivas/preventivas
- Registro de seguimiento y cierre de acciones
- Listado de trabajadores potencialmente expuestos evaluados
- Actas del {$comite} con revision de investigaciones
- Lecciones aprendidas documentadas y socializadas
- Indicadores de gestion actualizados

CONSERVACION: Minimo 20 anos segun Decreto 1072/2015 art. 2.2.4.6.12.
CODIFICACION sugerida: FT-SST-IIA-01 (Informe investigacion), FT-SST-IIA-02 (Seguimiento acciones), FT-SST-IIA-03 (Evaluacion expuestos), FT-SST-IIA-04 (Lecciones aprendidas).
Formato: lista con vinetas agrupada por tipo de registro."
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' del documento de Investigacion de Incidentes, Accidentes de Trabajo y Enfermedades Laborales segun la Resolucion 0312/2019 estandar 3.2.2, el Decreto 1072/2015 y la Resolucion 1401/2007. Enfatizar la determinacion de causas basicas e inmediatas y la evaluacion de trabajadores potencialmente expuestos.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Establecer la metodologia para la investigacion de todos los incidentes, accidentes de trabajo y enfermedades laborales diagnosticadas como de origen laboral que se presenten en {$nombreEmpresa}, con el fin de determinar las causas basicas e inmediatas de cada evento, evaluar la posibilidad de que se presenten nuevos casos, y realizar seguimiento a las acciones y recomendaciones para proteger a otros trabajadores potencialmente expuestos.\n\nEste documento da cumplimiento al estandar 3.2.2 de la Resolucion 0312 de 2019, al articulo 2.2.4.6.32 del Decreto 1072 de 2015 y a la Resolucion 1401 de 2007, garantizando que todos los eventos sean investigados con determinacion de causas raiz y que se implementen medidas preventivas para la poblacion trabajadora expuesta.",

            'alcance' => "Este documento aplica a:\n\n- Todos los trabajadores directos de {$nombreEmpresa}\n- Contratistas, subcontratistas y trabajadores en mision\n- Trabajadores temporales y visitantes en las instalaciones\n\nCubre la investigacion de:\n\n- Incidentes de trabajo (eventos sin lesion con potencial de dano)\n- Accidentes de trabajo leves, graves y mortales\n- Enfermedades laborales diagnosticadas como de origen laboral\n\nIncluye la determinacion de causas basicas e inmediatas, la evaluacion de trabajadores potencialmente expuestos al mismo riesgo, y el seguimiento a las acciones correctivas y preventivas en todas las sedes y centros de trabajo.",

            'definiciones' => "**Accidente de Trabajo:** Todo suceso repentino que sobrevenga por causa o con ocasion del trabajo, y que produzca en el trabajador una lesion organica, una perturbacion funcional o psiquiatrica, una invalidez o la muerte (Ley 1562 de 2012, art. 3).\n\n**Incidente de Trabajo:** Suceso acaecido en el curso del trabajo o en relacion con este, que tuvo el potencial de ser un accidente, sin que se presentaran lesiones o danos a la propiedad.\n\n**Enfermedad Laboral:** La contraida como resultado de la exposicion a factores de riesgo inherentes a la actividad laboral o del medio en el que el trabajador se ha visto obligado a trabajar (Ley 1562 de 2012, art. 4).\n\n**Causa Inmediata:** Circunstancia que se presenta justamente antes del contacto. Se clasifican en actos inseguros (comportamientos) y condiciones inseguras (ambiente).\n\n**Causa Basica:** Causa real detras de las causas inmediatas. Se clasifican en factores personales (capacidad, conocimiento, motivacion) y factores del trabajo (supervision, diseno, procedimientos).\n\n**Trabajador Potencialmente Expuesto:** Persona que realiza actividades similares o esta expuesta a los mismos peligros que originaron el evento investigado, y que podria sufrir un evento similar.\n\n**FURAT:** Formato Unico de Reporte de Accidentes de Trabajo (Resolucion 156 de 2005).\n\n**FUREL:** Formato Unico de Reporte de Enfermedad Laboral (Resolucion 156 de 2005).\n\n**Investigacion de Accidente o Incidente:** Proceso sistematico de determinacion y ordenacion de causas, hechos o situaciones que generaron o favorecieron la ocurrencia del evento (Resolucion 1401 de 2007).",

            'responsabilidades' => "**Alta Direccion / Representante Legal:**\n- Garantizar que se investiguen TODOS los incidentes, accidentes y enfermedades laborales sin excepcion\n- Asignar recursos para la investigacion y las acciones correctivas\n- Reportar accidentes graves y mortales al Ministerio de Trabajo dentro de 2 dias habiles\n- Revisar los resultados de las investigaciones\n\n**Responsable del SG-SST:**\n- Liderar y coordinar la investigacion de todos los eventos\n- Determinar causas basicas e inmediatas de cada evento\n- Evaluar la posibilidad de nuevos casos en trabajadores expuestos\n- Realizar seguimiento a las acciones correctivas y preventivas\n- Mantener registros y evidencias durante minimo 20 anos\n\n**{$comite}:**\n- Participar activamente en las investigaciones\n- Verificar cumplimiento de acciones correctivas y preventivas\n- Proponer medidas de prevencion basadas en las investigaciones\n- Hacer seguimiento en reuniones ordinarias\n\n**Trabajadores:**\n- Reportar inmediatamente cualquier incidente, accidente o condicion de salud\n- Colaborar con la investigacion proporcionando informacion veraz\n- Cumplir las acciones correctivas y preventivas establecidas\n- Participar en la socializacion de lecciones aprendidas",

            'marco_legal' => "**Ley 1562 de 2012:**\n- Articulo 3: Definicion de accidente de trabajo\n- Articulo 4: Definicion de enfermedad laboral\n\n**Decreto 1072 de 2015:**\n- Articulo 2.2.4.6.32: Investigacion de incidentes, accidentes de trabajo y enfermedades laborales\n- Articulo 2.2.4.6.12: Documentacion y conservacion por 20 anos\n\n**Resolucion 0312 de 2019:**\n- Estandar 3.2.2: Investigacion de accidentes, incidentes y enfermedad laboral\n\n**Resolucion 1401 de 2007:**\n- Reglamentacion de la investigacion de incidentes y accidentes de trabajo\n\n**Decreto 472 de 2015:**\n- Sanciones por incumplimiento en reporte de accidentes mortales\n\n**Resolucion 156 de 2005:**\n- Formatos FURAT y FUREL\n\n**Decreto 1530 de 1996:**\n- Articulo 4: Reporte de accidentes dentro de 2 dias habiles"
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
