<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase ProgramaEstilosVidaSaludable
 *
 * Implementa el Programa de Estilos de Vida Saludable y Entornos de Trabajo Saludables
 * para el estandar 3.1.7 de la Resolucion 0312/2019.
 *
 * Establece las actividades de promocion de estilos de vida saludables,
 * controles de tabaquismo, alcoholismo, farmacodependencia y otros factores,
 * para el fomento de habitos saludables en los trabajadores.
 *
 * TIPO B: Programa de 3 partes (Actividades PTA → Indicadores → Documento IA)
 * - Parte 1: Actividades de Estilos de Vida Saludable en PTA (tipo_servicio = TIPO_SERVICIO)
 * - Parte 2: Indicadores de Estilos de Vida (categoria = CATEGORIA)
 * - Parte 3: Documento formal generado con IA alimentado por Partes 1 y 2
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 2.0
 */
class ProgramaEstilosVidaSaludable extends AbstractDocumentoSST
{
    /** Valor de tipo_servicio en tbl_pta_cliente para este modulo */
    public const TIPO_SERVICIO = 'Estilos de Vida Saludable';

    /** Valor de categoria en tbl_indicadores_sst para este modulo */
    public const CATEGORIA = 'estilos_vida_saludable';

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
        return 'programa_estilos_vida_saludable';
    }

    public function getNombre(): string
    {
        $config = $this->getConfig();
        return $config['nombre'] ?? 'Programa de Estilos de Vida Saludable y Entornos Saludables';
    }

    public function getDescripcion(): string
    {
        $config = $this->getConfig();
        return $config['descripcion'] ?? 'Establece las actividades de promocion de estilos de vida saludables, controles de tabaquismo, alcoholismo, farmacodependencia y fomento de habitos saludables en los trabajadores';
    }

    public function getEstandar(): ?string
    {
        return '3.1.7';
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
            ['numero' => 6, 'nombre' => 'Diagnostico y Linea Base', 'key' => 'diagnostico_linea_base'],
            ['numero' => 7, 'nombre' => 'Actividades de Promocion de Estilos de Vida Saludable', 'key' => 'actividades_promocion'],
            ['numero' => 8, 'nombre' => 'Controles de Tabaquismo, Alcoholismo y Farmacodependencia', 'key' => 'controles_sustancias'],
            ['numero' => 9, 'nombre' => 'Entornos de Trabajo Saludables', 'key' => 'entornos_saludables'],
            ['numero' => 10, 'nombre' => 'Cronograma de Actividades', 'key' => 'cronograma'],
            ['numero' => 11, 'nombre' => 'Indicadores de Gestion', 'key' => 'indicadores'],
            ['numero' => 12, 'nombre' => 'Evaluacion y Seguimiento', 'key' => 'evaluacion_seguimiento'],
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

    protected function getPromptFallback(string $seccionKey, int $estandares): string
    {
        $comite = $this->getTextoComite($estandares);

        $prompts = [
            'objetivo' => "Genera el objetivo del Programa de Estilos de Vida Saludable y Entornos Saludables.
Debe mencionar:
- Promover habitos y estilos de vida saludables en los trabajadores
- Implementar controles efectivos contra tabaquismo, alcoholismo, farmacodependencia y otros
- Fomentar entornos de trabajo saludables que contribuyan al bienestar integral
- Cumplimiento del Decreto 1072 de 2015 (articulo 2.2.4.6.24) y Resolucion 0312 de 2019 (estandar 3.1.7)
- Articulacion con la Politica de Prevencion de Consumo de Alcohol, Drogas y Tabaco
Maximo 2 parrafos concisos.",

            'alcance' => "Define el alcance del programa. Debe especificar:
- Aplica a todos los trabajadores directos, contratistas, subcontratistas y temporales
- Cubre actividades dentro y fuera de las instalaciones de la empresa
- Incluye promocion de habitos saludables (alimentacion, actividad fisica, manejo del estres, higiene del sueno)
- Incluye prevencion y control de consumo de tabaco, alcohol y sustancias psicoactivas
- Incluye condiciones del entorno laboral que favorezcan la salud
- A quien aplica: alta direccion, responsable SST, ARL, {$comite}, trabajadores
Maximo 2 parrafos.",

            'definiciones' => "Genera las definiciones clave para este programa. INCLUIR OBLIGATORIAMENTE:
- Estilo de vida saludable
- Entorno de trabajo saludable
- Tabaquismo
- Alcoholismo
- Farmacodependencia
- Sustancia psicoactiva
- Promocion de la salud
- Prevencion de la enfermedad
- Autocuidado
- Factor de riesgo modificable
- Pausas activas
- Bienestar laboral
CANTIDAD: 12-14 definiciones basadas en normativa colombiana y OMS.",

            'marco_legal' => "Genera el marco legal aplicable. INCLUIR:
- Decreto 1072 de 2015, articulo 2.2.4.6.24 (Medidas de prevencion y control)
- Resolucion 0312 de 2019, estandar 3.1.7 (Estilos de vida y entornos saludables)
- Ley 1566 de 2012 (Atencion integral a personas con consumo de SPA)
- Resolucion 1075 de 1992 (Campanas prevencion alcoholismo, tabaquismo y farmacodependencia)
- Ley 1335 de 2009 (Prevencion del tabaquismo - Ambientes libres de humo)
- Ley 30 de 1986 (Estatuto Nacional de Estupefacientes)
- Decreto 1108 de 1994 (Porte y consumo de sustancias psicoactivas)
- Resolucion 2646 de 2008 (Factores de riesgo psicosocial)
- Circular 038 de 2010 (Espacios libres de humo y sustancias psicoactivas)
Presentar en formato tabla con numero de norma, ano y descripcion breve.",

            'responsabilidades' => "Define las responsabilidades en el programa:

**Alta Direccion / Representante Legal:**
- Aprobar y respaldar el programa
- Asignar recursos economicos y logisticos
- Participar en las actividades de promocion
- Garantizar ambientes libres de humo de tabaco

**Responsable del SG-SST:**
- Disenar, implementar y evaluar el programa
- Coordinar actividades con la ARL y EPS
- Realizar campanas de sensibilizacion
- Hacer seguimiento a indicadores del programa
- Gestionar casos identificados con apoyo de EPS/ARL

**{$comite}:**
- Apoyar la difusion del programa entre trabajadores
- Participar en actividades de promocion y prevencion
- Recibir reportes sobre situaciones relacionadas

**ARL:**
- Brindar asesoria tecnica y capacitaciones
- Apoyar con material educativo y campanas
- Apoyar en la evaluacion de resultados

**Trabajadores:**
- Participar activamente en las actividades del programa
- Adoptar habitos de vida saludables
- Reportar situaciones de riesgo observadas
- Cumplir la Politica de Prevencion de Consumo de Alcohol, Drogas y Tabaco",

            'diagnostico_linea_base' => "Describe como se realiza el diagnostico y linea base del programa:

**Fuentes de informacion:**
- Resultados de las evaluaciones medicas ocupacionales (ingreso, periodicas)
- Perfil sociodemografico de la poblacion trabajadora
- Diagnostico de condiciones de salud
- Encuesta de habitos y estilos de vida (aplicar a toda la poblacion)
- Estadisticas de ausentismo y morbilidad
- Resultados de la bateria de riesgo psicosocial (si aplica)

**Variables a evaluar:**
- Habitos alimentarios (frecuencia, tipo de alimentacion)
- Nivel de actividad fisica (sedentarismo, ejercicio regular)
- Consumo de tabaco (fumadores activos, pasivos, ex fumadores)
- Consumo de alcohol (frecuencia, cantidad, tipo de consumo)
- Consumo de sustancias psicoactivas
- Manejo del estres y salud mental
- Horas de sueno y descanso
- Indices de masa corporal y perimetro abdominal

**Periodicidad:** Linea base al inicio del programa, actualizacion minimo anual.

**Resultado:** Informe con priorizacion de intervenciones segun hallazgos.",

            'actividades_promocion' => "Genera las actividades de promocion de estilos de vida saludable:

**Alimentacion saludable:**
- Campanas sobre loncheras saludables y habitos nutricionales
- Jornadas de valoracion nutricional (IMC, perimetro abdominal)
- Senalizacion en comedores y areas de alimentacion
- Charlas con nutricionista (al menos 2 al ano)

**Actividad fisica y pausas activas:**
- Programa de pausas activas (minimo 2 veces al dia, 10 minutos)
- Jornadas deportivas y recreativas (trimestral)
- Retos de actividad fisica (caminatas, conteo de pasos)
- Convenios con gimnasios o actividades grupales

**Salud mental y manejo del estres:**
- Talleres de manejo del estres y relajacion
- Campanas de salud mental y prevencion del burnout
- Linea de apoyo psicologico (articulacion con EPS/ARL)
- Actividades de integracion y bienestar laboral

**Higiene del sueno:**
- Campanas sobre importancia del descanso adecuado
- Recomendaciones para mejorar la calidad del sueno

**Prevencion de enfermedades cronicas:**
- Jornadas de tamizaje (tension arterial, glicemia, colesterol)
- Campanas de prevencion cardiovascular y diabetes
- Control de factores de riesgo modificables

Para cada actividad: responsable, frecuencia, poblacion objetivo, evidencia esperada.",

            'controles_sustancias' => "Genera los controles para tabaquismo, alcoholismo y farmacodependencia:

**PREVENCION DEL TABAQUISMO (Ley 1335/2009):**
- Declaracion de ambientes 100% libres de humo de tabaco
- Senalizacion de prohibicion de fumar en todas las instalaciones
- Campanas de sensibilizacion sobre efectos del tabaco (minimo 2 al ano)
- Apoyo a trabajadores fumadores que deseen dejar el habito (remision a EPS)
- Eliminacion de zonas de fumadores dentro de las instalaciones

**PREVENCION DEL ALCOHOLISMO (Res. 1075/1992):**
- Campanas educativas sobre consumo responsable y efectos del alcohol
- Politica clara de prohibicion de consumo durante la jornada laboral
- Protocolo de actuacion ante trabajadores bajo efectos del alcohol
- Pruebas de alcoholimetria (especialmente en cargos criticos y alto riesgo)
- Canalizacion a programas de tratamiento mediante EPS

**PREVENCION DE FARMACODEPENDENCIA (Ley 1566/2012, Ley 30/1986):**
- Campanas de sensibilizacion sobre sustancias psicoactivas (minimo 2 al ano)
- Protocolo de deteccion temprana de consumo
- Procedimiento de intervencion y remision a EPS/ARL
- Apoyo y acompanamiento al trabajador en proceso de rehabilitacion
- Capacitacion a lideres para identificacion de senales de alerta

**PROTOCOLO DE ACTUACION:**
Describir los pasos a seguir cuando se identifica un caso:
1. Deteccion y confirmacion
2. Intervencion inicial (dialogo privado y confidencial)
3. Remision a EPS para valoracion
4. Seguimiento del caso
5. Reintegro laboral con acompanamiento",

            'entornos_saludables' => "Describe las acciones para fomentar entornos de trabajo saludables:

**Ambiente fisico saludable:**
- Condiciones de iluminacion, ventilacion y temperatura adecuadas
- Mantenimiento de areas comunes limpias y ordenadas
- Disponibilidad de agua potable y servicios sanitarios adecuados
- Espacios de descanso y alimentacion apropiados
- Areas verdes o de esparcimiento (si es posible)

**Ambiente psicosocial saludable:**
- Promocion de comunicacion asertiva y respeto mutuo
- Prevencion del acoso laboral (articulacion con Comite de Convivencia)
- Equilibrio vida-trabajo (flexibilidad horaria cuando sea posible)
- Reconocimiento y valoracion del trabajo
- Canales de participacion y retroalimentacion

**Organizacion del trabajo saludable:**
- Distribucion equitativa de cargas de trabajo
- Rotacion de tareas para prevenir monotonia
- Pausas activas programadas dentro de la jornada
- Capacitacion continua y desarrollo profesional

**Conectividad con la comunidad:**
- Promocion de actividades saludables en familia
- Material educativo para llevar a casa
- Alianzas con entidades de salud y bienestar",

            'cronograma' => "Genera un cronograma anual de actividades del programa distribuido por trimestres:

**Trimestre 1 (Enero - Marzo):**
- Aplicacion de encuesta de habitos y estilos de vida (linea base)
- Campana de alimentacion saludable
- Inicio de pausas activas programadas
- Jornada de tamizaje de salud (tension, glicemia, IMC)

**Trimestre 2 (Abril - Junio):**
- Campana de prevencion de tabaquismo (Dia Sin Tabaco - 31 mayo)
- Taller de manejo del estres
- Jornada deportiva y recreativa
- Charla con nutricionista

**Trimestre 3 (Julio - Septiembre):**
- Campana de prevencion de alcoholismo y farmacodependencia
- Jornada de salud mental y bienestar
- Actividad de integracion familiar
- Seguimiento a pausas activas

**Trimestre 4 (Octubre - Diciembre):**
- Evaluacion de indicadores del programa
- Campana de prevencion de enfermedades cronicas
- Jornada deportiva de cierre de ano
- Informe de resultados y planificacion del siguiente ano

Para cada actividad: fecha tentativa, responsable, recurso necesario, indicador de cumplimiento.",

            'indicadores' => "Genera los indicadores de gestion del programa:

**Indicadores de Estructura:**
- Existencia del programa documentado y aprobado (Si/No)
- Presupuesto asignado vs. ejecutado (%)
- Numero de alianzas con EPS/ARL para actividades

**Indicadores de Proceso:**
- Porcentaje de actividades programadas ejecutadas (meta >= 90%)
- Cobertura de trabajadores participantes en actividades (meta >= 80%)
- Numero de campanas de prevencion realizadas al ano (meta >= 6)
- Porcentaje de trabajadores con encuesta de habitos aplicada (meta >= 90%)
- Frecuencia de realizacion de pausas activas (meta: 2 diarias)

**Indicadores de Resultado:**
- Variacion en el porcentaje de fumadores activos (meta: reduccion >= 5%)
- Variacion en el porcentaje de trabajadores con IMC normal (meta: incremento)
- Variacion en el porcentaje de trabajadores sedentarios (meta: reduccion >= 10%)
- Tasa de ausentismo por enfermedades cronicas no transmisibles
- Numero de casos remitidos a EPS por consumo de SPA
- Nivel de satisfaccion de los trabajadores con el programa (meta >= 80%)

Cada indicador debe tener: nombre, formula, meta, frecuencia de medicion, responsable.",

            'evaluacion_seguimiento' => "Describe como se evalua y hace seguimiento al programa:

**Seguimiento trimestral:**
- Revision de cumplimiento de actividades programadas
- Analisis de participacion de los trabajadores
- Identificacion de dificultades y ajustes necesarios
- Informe al {$comite}

**Evaluacion semestral:**
- Medicion de indicadores de proceso
- Evaluacion de impacto parcial
- Ajuste de actividades segun resultados

**Evaluacion anual:**
- Medicion de todos los indicadores (estructura, proceso, resultado)
- Comparacion con linea base
- Identificacion de tendencias y logros
- Informe a la alta direccion
- Insumo para la revision por la direccion del SG-SST
- Planificacion del programa del siguiente ano

**Mejora continua:**
- Analisis de lecciones aprendidas
- Benchmarking con otras organizaciones del sector
- Actualizacion segun nuevas evidencias cientificas y normativas
- Retroalimentacion de los trabajadores para mejorar el programa

**Registros:**
- Encuestas de habitos y estilos de vida
- Listas de asistencia a actividades
- Informes de jornadas de tamizaje
- Informes trimestrales y anuales del programa
- Actas de reunion de seguimiento"
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' del Programa de Estilos de Vida Saludable segun la Resolucion 0312/2019 (estandar 3.1.7) y el Decreto 1072/2015. El programa debe promover habitos saludables y controles de tabaquismo, alcoholismo y farmacodependencia.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Promover estilos de vida saludables y fomentar entornos de trabajo saludables para todos los trabajadores de {$nombreEmpresa}, mediante la implementacion de actividades de promocion de la salud, prevencion de enfermedades y controles efectivos contra el tabaquismo, alcoholismo y farmacodependencia.\n\nEste programa da cumplimiento al estandar 3.1.7 de la Resolucion 0312 de 2019 y al articulo 2.2.4.6.24 del Decreto 1072 de 2015, asi como a la Resolucion 1075 de 1992 que establece la obligatoriedad de campanas de prevencion de alcoholismo, tabaquismo y farmacodependencia.",

            'alcance' => "Este programa aplica a todos los trabajadores directos, contratistas, subcontratistas y temporales vinculados a {$nombreEmpresa}, cubriendo actividades de promocion de habitos saludables (alimentacion, actividad fisica, manejo del estres, higiene del sueno) y prevencion y control de consumo de tabaco, alcohol y sustancias psicoactivas.\n\nEs de obligatorio cumplimiento para la alta direccion, el responsable del SG-SST, la ARL, el {$comite} y todos los trabajadores de la organizacion.",

            'definiciones' => "**Estilo de vida saludable:** Conjunto de habitos y comportamientos que contribuyen a mantener y mejorar la salud fisica, mental y social de las personas.\n\n**Entorno de trabajo saludable:** Ambiente laboral que promueve el bienestar integral de los trabajadores a traves de condiciones fisicas, psicosociales y organizacionales adecuadas.\n\n**Tabaquismo:** Adiccion al tabaco provocada por la nicotina. Es una enfermedad cronica que afecta la salud del fumador activo y pasivo.\n\n**Alcoholismo:** Consumo cronico y excesivo de alcohol que genera dependencia y afecta la salud fisica, mental y el desempeno laboral.\n\n**Farmacodependencia:** Dependencia a sustancias psicoactivas que altera el funcionamiento del sistema nervioso central.\n\n**Sustancia psicoactiva:** Toda sustancia que al ser consumida modifica las funciones del sistema nervioso central.\n\n**Promocion de la salud:** Acciones dirigidas a proporcionar medios necesarios para mejorar la salud de las personas.\n\n**Autocuidado:** Capacidad de las personas para cuidar su propia salud mediante practicas voluntarias y responsables.\n\n**Factor de riesgo modificable:** Condicion de salud que puede ser cambiada mediante modificacion de habitos y comportamientos.\n\n**Pausas activas:** Ejercicios fisicos y mentales realizados durante la jornada laboral para prevenir la fatiga.\n\n**Bienestar laboral:** Estado de satisfaccion integral del trabajador derivado de condiciones laborales favorables.",

            'marco_legal' => "**Decreto 1072 de 2015:**\n- Articulo 2.2.4.6.24: Medidas de prevencion y control en SST.\n\n**Resolucion 0312 de 2019:**\n- Estandar 3.1.7: Estilos de vida y entornos saludables (controles tabaquismo, alcoholismo, farmacodependencia y otros).\n\n**Resolucion 1075 de 1992:**\n- Campanas especificas para fomentar la prevencion y control de la farmacodependencia, el alcoholismo y el tabaquismo.\n\n**Ley 1335 de 2009:**\n- Disposiciones para la prevencion del consumo de tabaco - Ambientes 100% libres de humo.\n\n**Ley 1566 de 2012:**\n- Atencion integral a personas con consumo de sustancias psicoactivas.\n\n**Ley 30 de 1986:**\n- Estatuto Nacional de Estupefacientes.\n\n**Resolucion 2646 de 2008:**\n- Factores de riesgo psicosocial en el trabajo.\n\n**Circular 038 de 2010:**\n- Espacios libres de humo y sustancias psicoactivas en empresas.",

            'responsabilidades' => "**Alta Direccion / Representante Legal:**\n- Aprobar y respaldar el programa\n- Asignar recursos economicos y logisticos\n- Garantizar ambientes libres de humo de tabaco\n\n**Responsable del SG-SST:**\n- Disenar, implementar y evaluar el programa\n- Coordinar actividades con la ARL y EPS\n- Realizar campanas de sensibilizacion\n- Gestionar casos identificados con apoyo de EPS/ARL\n\n**{$comite}:**\n- Apoyar la difusion del programa\n- Participar en actividades de promocion y prevencion\n- Recibir reportes sobre situaciones relacionadas\n\n**Trabajadores:**\n- Participar activamente en las actividades\n- Adoptar habitos de vida saludables\n- Cumplir la Politica de Prevencion de Consumo de Alcohol, Drogas y Tabaco",

            'diagnostico_linea_base' => "**Fuentes de informacion:**\n- Evaluaciones medicas ocupacionales\n- Perfil sociodemografico\n- Diagnostico de condiciones de salud\n- Encuesta de habitos y estilos de vida\n- Estadisticas de ausentismo\n\n**Variables evaluadas:**\n- Habitos alimentarios\n- Nivel de actividad fisica\n- Consumo de tabaco, alcohol y SPA\n- Manejo del estres\n- Indices de masa corporal\n\n**Periodicidad:** Linea base inicial, actualizacion anual.",

            'actividades_promocion' => "**Alimentacion saludable:**\n- Campanas sobre loncheras saludables\n- Jornadas de valoracion nutricional\n- Charlas con nutricionista\n\n**Actividad fisica y pausas activas:**\n- Pausas activas 2 veces al dia\n- Jornadas deportivas trimestrales\n- Retos de actividad fisica\n\n**Salud mental y manejo del estres:**\n- Talleres de manejo del estres\n- Campanas de salud mental\n- Actividades de integracion\n\n**Prevencion de enfermedades cronicas:**\n- Jornadas de tamizaje\n- Campanas de prevencion cardiovascular",

            'controles_sustancias' => "**Prevencion del tabaquismo:**\n- Ambientes 100% libres de humo\n- Senalizacion de prohibicion\n- Campanas de sensibilizacion (minimo 2 al ano)\n- Apoyo a fumadores que deseen dejar el habito\n\n**Prevencion del alcoholismo:**\n- Campanas educativas\n- Prohibicion de consumo en jornada laboral\n- Pruebas de alcoholimetria en cargos criticos\n- Canalizacion a EPS\n\n**Prevencion de farmacodependencia:**\n- Campanas de sensibilizacion\n- Protocolo de deteccion temprana\n- Remision a EPS/ARL\n- Acompanamiento en rehabilitacion",

            'entornos_saludables' => "**Ambiente fisico:**\n- Condiciones adecuadas de iluminacion, ventilacion y temperatura\n- Areas comunes limpias y ordenadas\n- Disponibilidad de agua potable\n- Espacios de descanso apropiados\n\n**Ambiente psicosocial:**\n- Comunicacion asertiva y respeto mutuo\n- Prevencion del acoso laboral\n- Equilibrio vida-trabajo\n- Reconocimiento del trabajo\n\n**Organizacion del trabajo:**\n- Distribucion equitativa de cargas\n- Pausas activas programadas\n- Capacitacion continua",

            'cronograma' => "**Trimestre 1:** Encuesta de habitos, campana de alimentacion saludable, inicio pausas activas, tamizaje de salud.\n\n**Trimestre 2:** Campana prevencion tabaquismo, taller manejo del estres, jornada deportiva.\n\n**Trimestre 3:** Campana prevencion alcoholismo y farmacodependencia, jornada de salud mental.\n\n**Trimestre 4:** Evaluacion de indicadores, campana prevencion enfermedades cronicas, informe anual.",

            'indicadores' => "**Indicadores de proceso:**\n- Porcentaje de actividades ejecutadas (meta >= 90%)\n- Cobertura de trabajadores participantes (meta >= 80%)\n- Numero de campanas de prevencion realizadas\n\n**Indicadores de resultado:**\n- Variacion en porcentaje de fumadores activos\n- Variacion en trabajadores con IMC normal\n- Variacion en trabajadores sedentarios\n- Nivel de satisfaccion con el programa (meta >= 80%)",

            'evaluacion_seguimiento' => "**Seguimiento trimestral:** Revision de cumplimiento, analisis de participacion, informe al {$comite}.\n\n**Evaluacion anual:** Medicion de todos los indicadores, comparacion con linea base, informe a alta direccion, planificacion del siguiente ano.\n\n**Registros:** Encuestas de habitos, listas de asistencia, informes de tamizaje, informes trimestrales y anuales."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }

    /**
     * Sobrescribe getContextoBase para incluir datos de actividades e indicadores
     * CRITICO: Esto alimenta la IA con los datos reales de las fases previas (Parte 1 y Parte 2)
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
        // OBTENER ACTIVIDADES DE ESTILOS DE VIDA SALUDABLE DEL PTA (FASE 1)
        // =====================================================================
        $actividadesTexto = $this->obtenerActividadesEstilosVida($idCliente, $anio);

        // =====================================================================
        // OBTENER INDICADORES DE ESTILOS DE VIDA SALUDABLE (FASE 2)
        // =====================================================================
        $indicadoresTexto = $this->obtenerIndicadoresEstilosVida($idCliente);

        return "CONTEXTO DE LA EMPRESA:
- Nombre: {$nombreEmpresa}
- NIT: {$nit}
- Actividad economica: {$actividadEconomica}
- Nivel de riesgo: {$nivelRiesgo}
- Numero de trabajadores: {$numTrabajadores}
- Estandares aplicables: {$estandares} ({$nivelTexto})

============================================================
ACTIVIDADES DE ESTILOS DE VIDA SALUDABLE (FASE 1)
Estas son las actividades REALES registradas en el Plan de Trabajo:
============================================================
{$actividadesTexto}

============================================================
INDICADORES DE ESTILOS DE VIDA SALUDABLE (FASE 2)
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
- Usa terminologia de la normativa colombiana (Resolucion 0312/2019, Decreto 1072/2015)
- NO uses tablas Markdown a menos que se indique especificamente
- Mantén un tono profesional y tecnico
- El programa debe incluir controles de tabaquismo, alcoholismo y farmacodependencia
- Referenciar Ley 1335/2009, Ley 1566/2012, Resolucion 1075/1992";
    }

    /**
     * Obtiene las actividades de Estilos de Vida Saludable del Plan de Trabajo
     */
    private function obtenerActividadesEstilosVida(int $idCliente, int $anio): string
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
                    ->orLike('tipo_servicio', 'Estilos de Vida', 'both')
                    ->orLike('tipo_servicio', 'Vida Saludable', 'both')
                    ->orLike('actividad_plandetrabajo', 'tabaquismo', 'both')
                    ->orLike('actividad_plandetrabajo', 'alcoholismo', 'both')
                    ->orLike('actividad_plandetrabajo', 'farmacodependencia', 'both')
                    ->orLike('actividad_plandetrabajo', 'estilos de vida', 'both')
                    ->orLike('actividad_plandetrabajo', 'entorno saludable', 'both')
                    ->orLike('actividad_plandetrabajo', 'habitos saludables', 'both')
                    ->orLike('actividad_plandetrabajo', 'prevencion consumo', 'both')
                    ->orLike('actividad_plandetrabajo', 'sustancias psicoactivas', 'both')
                ->groupEnd()
                ->orderBy('fecha_propuesta', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($actividades)) {
                return "No hay actividades de Estilos de Vida Saludable registradas para el ano {$anio}";
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
            log_message('error', "Error obteniendo actividades Estilos de Vida: " . $e->getMessage());
            return "Error al obtener actividades: " . $e->getMessage();
        }
    }

    /**
     * Obtiene los indicadores de Estilos de Vida Saludable configurados
     */
    private function obtenerIndicadoresEstilosVida(int $idCliente): string
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
                return "No hay indicadores de Estilos de Vida Saludable configurados";
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
            log_message('error', "Error obteniendo indicadores Estilos de Vida: " . $e->getMessage());
            return "Error al obtener indicadores: " . $e->getMessage();
        }
    }
}
