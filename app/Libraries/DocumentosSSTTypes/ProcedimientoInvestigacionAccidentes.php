<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase ProcedimientoInvestigacionAccidentes
 *
 * Implementa la generacion del Procedimiento de Investigacion de Incidentes,
 * Accidentes de Trabajo y Enfermedades Laborales para el estandar 3.2.1
 * de la Resolucion 0312/2019.
 *
 * TIPO A: Solo Parte 3 (documento formal con IA, sin actividades PTA ni indicadores)
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class ProcedimientoInvestigacionAccidentes extends AbstractDocumentoSST
{
    protected ?DocumentoConfigService $configService = null;

    protected function getConfigService(): DocumentoConfigService
    {
        if ($this->configService === null) {
            $this->configService = new DocumentoConfigService();
        }
        return $this->configService;
    }

    public function getTipoDocumento(): string
    {
        return 'procedimiento_investigacion_accidentes';
    }

    public function getNombre(): string
    {
        return 'Procedimiento de Investigacion de Incidentes, Accidentes de Trabajo y Enfermedades Laborales';
    }

    public function getDescripcion(): string
    {
        return 'Establece la metodologia para investigar incidentes, accidentes de trabajo y enfermedades laborales, determinando causas basicas e inmediatas, y realizando seguimiento a las acciones correctivas';
    }

    public function getEstandar(): ?string
    {
        return '3.2.1';
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
            ['numero' => 4, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
            ['numero' => 5, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            ['numero' => 6, 'nombre' => 'Reporte de Accidentes de Trabajo y Enfermedades Laborales', 'key' => 'reporte_at_el'],
            ['numero' => 7, 'nombre' => 'Procedimiento de Investigacion de Incidentes y Accidentes', 'key' => 'investigacion_incidentes_accidentes'],
            ['numero' => 8, 'nombre' => 'Investigacion de Enfermedades Laborales', 'key' => 'investigacion_enfermedades'],
            ['numero' => 9, 'nombre' => 'Determinacion de Causas Basicas e Inmediatas', 'key' => 'causas_basicas_inmediatas'],
            ['numero' => 10, 'nombre' => 'Acciones Correctivas y Preventivas', 'key' => 'acciones_correctivas_preventivas'],
            ['numero' => 11, 'nombre' => 'Seguimiento y Verificacion de Acciones', 'key' => 'seguimiento_verificacion'],
            ['numero' => 12, 'nombre' => 'Registros y Evidencias', 'key' => 'registros_evidencias'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['responsable_sst', 'representante_legal'];
    }

    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        // Intentar obtener prompt desde BD
        $promptBD = $this->getConfigService()->obtenerPromptSeccion($this->getTipoDocumento(), $seccionKey);
        if (!empty($promptBD)) {
            return $promptBD;
        }

        $comite = $this->getTextoComite($estandares);

        $nivelTexto = match(true) {
            $estandares <= 7 => 'basico (7 estandares)',
            $estandares <= 21 => 'intermedio (21 estandares)',
            default => 'avanzado (60 estandares)'
        };

        // Fallback con prompts hardcodeados
        $prompts = [
            'objetivo' => "Genera el objetivo del Procedimiento de Investigacion de Incidentes, Accidentes de Trabajo y Enfermedades Laborales.
Debe establecer:
- El proposito de investigar TODOS los incidentes, accidentes de trabajo y enfermedades laborales diagnosticadas
- Determinar causas basicas e inmediatas de cada evento
- Prevenir la ocurrencia de nuevos casos
- Realizar seguimiento a las acciones correctivas y recomendaciones
- Referencia al Decreto 1072/2015 art. 2.2.4.6.32, Resolucion 0312/2019 estandar 3.2.1, y Resolucion 1401/2007
FORMATO: Maximo 2 parrafos concisos.
TONO: Formal, tecnico, en tercera persona.",

            'alcance' => "Define el alcance del procedimiento de investigacion de accidentes.
Debe especificar:
- Aplica a TODOS los trabajadores directos, contratistas, subcontratistas, temporales y visitantes
- Cubre incidentes, accidentes de trabajo (leves, graves, mortales) y enfermedades laborales diagnosticadas
- Aplica en todas las sedes, centros de trabajo y actividades externas
- Incluye el reporte a ARL, EPS y Direccion Territorial del Ministerio de Trabajo
AJUSTAR segun nivel ({$nivelTexto}).
FORMATO: Lista con vinetas.",

            'definiciones' => "Define los terminos clave para el procedimiento de investigacion de accidentes.
TERMINOS OBLIGATORIOS: Accidente de trabajo (Ley 1562/2012 art. 3), Incidente de trabajo, Enfermedad laboral (Ley 1562/2012 art. 4), Accidente grave (Res. 1401/2007 art. 3), Accidente mortal, Causa inmediata, Causa basica, Factor personal, Factor del trabajo, Investigacion de accidentes, FURAT (Formato Unico de Reporte de Accidentes de Trabajo), FUREL (Formato Unico de Reporte de Enfermedad Laboral), Acto inseguro, Condicion insegura.
CANTIDAD segun estandares:
- 7 estandares: 10-12 terminos esenciales
- 21 estandares: 12-14 terminos
- 60 estandares: 14-16 terminos (agregar: Arbol de causas, Metodologia de los 5 por que, Barrera de seguridad, Lecciones aprendidas)
Formato: termino en **negrita** seguido de dos puntos y definicion.
NO usar tablas Markdown.",

            'marco_legal' => "Presenta el marco normativo aplicable a la investigacion de accidentes e incidentes.
NORMAS OBLIGATORIAS:
- Ley 1562 de 2012 art. 3 y 4 (definiciones AT y EL)
- Decreto 1072 de 2015 art. 2.2.4.6.32 (Investigacion de incidentes, accidentes y enfermedades)
- Decreto 1072 de 2015 art. 2.2.4.6.12 (Documentacion - conservar 20 anos)
- Resolucion 0312 de 2019 estandar 3.2.1 (Reporte e investigacion)
- Resolucion 1401 de 2007 (Investigacion de incidentes y accidentes de trabajo)
- Decreto 472 de 2015 (Sanciones por no reportar AT mortales)
- Resolucion 156 de 2005 (Formatos FURAT y FUREL)
- Decreto 1530 de 1996 art. 4 (Reporte AT dentro de 2 dias habiles)
Formato tabla: Norma | Descripcion | Articulo relevante.",

            'responsabilidades' => "Define los roles y responsabilidades en la investigacion de accidentes.
ROLES segun estandares:
- 7 estandares: 4 roles (Representante Legal, Responsable SST, Vigia SST, Trabajadores)
- 21 estandares: 5-6 roles (agregar Jefes de area/Supervisores, ARL)
- 60 estandares: Todos (agregar Brigada de emergencias, Equipo investigador, Area juridica)
IMPORTANTE para {$estandares} estandares: usar '{$comite}' correctamente.
RESPONSABILIDADES CLAVE:
- Empleador/Rep. Legal: garantizar investigacion, asignar recursos, reportar AT graves/mortales al Ministerio
- Responsable SST: liderar investigacion, diligenciar FURAT/FUREL, reportar a ARL en 2 dias habiles
- {$comite}: participar en investigacion, verificar cumplimiento acciones correctivas
- Jefe inmediato: preservar evidencia, facilitar informacion, implementar acciones inmediatas
- Trabajadores: reportar incidentes/AT inmediatamente, colaborar con la investigacion, cumplir acciones
- ARL: asesorar en investigacion, capacitar, investigar AT graves y mortales
FORMATO: Rol en **negrita**, seguido de lista de responsabilidades.",

            'reporte_at_el' => "Describe el procedimiento de reporte de accidentes de trabajo y enfermedades laborales.
DEBE INCLUIR:
1. **Reporte interno inmediato**: trabajador o testigo reporta al jefe inmediato y al Responsable SST (antes de 24 horas)
2. **Reporte a la ARL**: diligenciar FURAT dentro de los 2 dias habiles siguientes (Decreto 1530/1996 art. 4)
3. **Reporte a la EPS**: informar para atencion medica del trabajador
4. **Reporte a Direccion Territorial del Ministerio de Trabajo**: obligatorio en accidentes graves y mortales (dentro de 2 dias habiles, Decreto 472/2015)
5. **Reporte de Enfermedad Laboral**: diligenciar FUREL cuando la EPS o medico SST diagnostica enfermedad de origen laboral

Para cada tipo de reporte: Responsable, Plazo, Medio/Formato, Destinatario.
Incluir consecuencias del no reporte (sanciones Decreto 472/2015: de 1 a 1000 SMMLV segun tamano empresa).
NO usar tablas Markdown. Usar listas estructuradas.",

            'investigacion_incidentes_accidentes' => "Describe el procedimiento paso a paso para investigar incidentes y accidentes de trabajo.
ETAPAS SEGUN Resolucion 1401/2007:
1. **Conformacion del equipo investigador**: jefe inmediato, responsable SST, miembro del {$comite}, y para AT graves/mortales: profesional con licencia SST
2. **Atencion al lesionado y aseguramiento del area**: primeros auxilios, preservar escena, asegurar evidencias
3. **Recopilacion de informacion**: declaracion del accidentado y testigos, inspeccion del lugar, revision documental (permisos, procedimientos, ATS, capacitaciones)
4. **Analisis de la informacion**: reconstruccion de hechos, secuencia del evento, identificacion de actos y condiciones inseguras
5. **Determinacion de causas**: causas inmediatas y causas basicas (ver seccion 9)
6. **Definicion de acciones correctivas**: plan de accion con responsables, fechas y recursos
7. **Elaboracion del informe**: formato Resolucion 1401/2007 con toda la informacion recopilada

Plazos: Investigacion debe iniciar dentro de los 15 dias calendario siguientes al AT (Res. 1401/2007 art. 4).
AT graves y mortales: enviar copia del informe a la ARL dentro de 15 dias calendario.
AJUSTAR complejidad segun nivel ({$nivelTexto}).",

            'investigacion_enfermedades' => "Describe el procedimiento para investigar enfermedades laborales diagnosticadas.
INCLUIR:
1. **Notificacion**: cuando la EPS, ARL o junta de calificacion determina origen laboral
2. **Recopilacion de informacion**: historial ocupacional, exposicion a peligros, condiciones de trabajo, evaluaciones medicas previas, perfil sociodemografico
3. **Analisis de causalidad**: relacion entre la exposicion laboral y la patologia diagnosticada, tiempo de exposicion, factores de riesgo identificados en la matriz de peligros
4. **Acciones preventivas**: medidas para evitar nuevos casos en trabajadores potencialmente expuestos (esto es clave para el estandar 3.2.1)
5. **Seguimiento medico**: control de trabajadores expuestos al mismo riesgo, evaluaciones medicas especificas
6. **Registro y documentacion**: FUREL diligenciado, informe de investigacion, plan de accion

Destacar: la empresa debe evaluar la posibilidad de que se presenten nuevos casos y tomar medidas preventivas para OTROS TRABAJADORES potencialmente expuestos.
NO usar tablas Markdown.",

            'causas_basicas_inmediatas' => "Describe la metodologia para determinar causas basicas e inmediatas de incidentes, accidentes y enfermedades laborales.
SEGUN Resolucion 1401/2007:
**Causas inmediatas:**
- Actos inseguros/subestandar: comportamientos del trabajador que desviaron el procedimiento seguro
- Condiciones inseguras/subestandar: circunstancias del ambiente de trabajo que propiciaron el evento

**Causas basicas:**
- Factores personales: capacidad fisica/mental, conocimiento, motivacion, estres, fatiga
- Factores del trabajo: supervision inadecuada, diseno ingenieria deficiente, procedimientos inexistentes/inadecuados, desgaste, mantenimiento deficiente

**Metodologias de analisis:**
- Arbol de causas: diagrama que muestra la cadena causal desde el evento hasta las causas raiz
- Metodologia de los 5 Por Que: preguntar 'por que' sucesivamente hasta identificar la causa raiz
- Espina de pescado (Ishikawa): analisis por categorias (maquina, metodo, mano de obra, material, medio, medida)

AJUSTAR segun nivel ({$nivelTexto}):
- 7 estandares: 1 metodologia basica (5 Por Que)
- 21 estandares: 2 metodologias (5 Por Que + Arbol de causas)
- 60 estandares: las 3 metodologias con ejemplos
NO usar tablas Markdown.",

            'acciones_correctivas_preventivas' => "Describe como se definen las acciones correctivas y preventivas derivadas de la investigacion.
INCLUIR:
1. **Plan de accion**: para cada causa identificada, definir accion correctiva/preventiva
2. **Jerarquia de controles**: Eliminacion > Sustitucion > Controles de ingenieria > Controles administrativos > EPP
3. **Asignacion**: responsable, fecha limite, recursos necesarios
4. **Comunicacion**: informar a todos los trabajadores expuestos al mismo riesgo sobre los hallazgos y medidas adoptadas
5. **Lecciones aprendidas**: documentar y socializar para prevenir recurrencia en otras areas/procesos
6. **Revision de documentos**: actualizar matriz de peligros, procedimientos de trabajo seguro, programa de capacitacion si aplica

IMPORTANTE: Las acciones deben orientarse a proteger a OTROS TRABAJADORES POTENCIALMENTE EXPUESTOS (requisito clave del estandar 3.2.1).
Incluir plazos razonables segun tipo de accion: inmediata (24h), corto plazo (15 dias), mediano plazo (30-60 dias).
NO usar tablas Markdown. Usar listas estructuradas.",

            'seguimiento_verificacion' => "Describe como se realiza el seguimiento y verificacion de las acciones correctivas y preventivas.
INCLUIR:
- Verificacion de cumplimiento: Responsable SST verifica implementacion en las fechas establecidas
- Eficacia de las acciones: evaluar si las medidas adoptadas eliminaron o controlaron las causas
- Indicadores de seguimiento: tasa de accidentalidad, indice de frecuencia, indice de severidad, indice de lesiones incapacitantes, % acciones cerradas vs pendientes
- Revision por el {$comite}: en reuniones ordinarias verificar avance de las acciones
- Retroalimentacion a la direccion: incluir en la revision por la alta direccion
- Cierre de la investigacion: solo cuando todas las acciones esten implementadas y verificadas
- Conservacion de registros: minimo 20 anos (Decreto 1072/2015 art. 2.2.4.6.12)

Periodicidad de seguimiento: semanal para acciones inmediatas, mensual para mediano plazo.
AJUSTAR segun nivel ({$nivelTexto}).
NO usar tablas Markdown.",

            'registros_evidencias' => "Lista los registros y evidencias que se deben mantener del proceso de investigacion.
REGISTROS OBLIGATORIOS:
- FURAT diligenciado (Formato Unico de Reporte de Accidente de Trabajo)
- FUREL diligenciado (Formato Unico de Reporte de Enfermedad Laboral)
- Informe de investigacion de accidentes (formato Resolucion 1401/2007)
- Declaraciones de testigos
- Registro fotografico del lugar del accidente
- Copia del reporte a la ARL
- Copia del reporte a la Direccion Territorial (AT graves/mortales)
- Plan de accion con acciones correctivas/preventivas
- Evidencia de seguimiento a las acciones (actas, fotos, registros)
- Actas del {$comite} con revision de investigaciones
- Lecciones aprendidas socializadas

CONSERVACION: Minimo 20 anos segun Decreto 1072/2015.
CODIFICACION sugerida de formatos: FT-SST-IAT-01 (Informe AT), FT-SST-IAT-02 (Seguimiento acciones), FT-SST-IAT-03 (Lecciones aprendidas).
Formato: lista con vinetas agrupada por tipo de registro."
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' del Procedimiento de Investigacion de Incidentes, Accidentes de Trabajo y Enfermedades Laborales segun la Resolucion 0312/2019, el Decreto 1072/2015 y la Resolucion 1401/2007.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Establecer la metodologia para la investigacion de incidentes, accidentes de trabajo y enfermedades laborales que se presenten en {$nombreEmpresa}, con el fin de determinar las causas basicas e inmediatas de cada evento, definir acciones correctivas y preventivas, y realizar seguimiento a su implementacion para proteger a los trabajadores potencialmente expuestos.\n\nEste procedimiento da cumplimiento al estandar 3.2.1 de la Resolucion 0312 de 2019, al articulo 2.2.4.6.32 del Decreto 1072 de 2015 y a la Resolucion 1401 de 2007, garantizando que todos los incidentes, accidentes de trabajo y enfermedades laborales sean debidamente reportados a la ARL, EPS y Direccion Territorial del Ministerio de Trabajo, e investigados para prevenir su recurrencia.",

            'alcance' => "Este procedimiento aplica a:\n\n- Todos los trabajadores directos de {$nombreEmpresa}\n- Contratistas y subcontratistas\n- Trabajadores en mision y temporales\n- Visitantes y terceros en las instalaciones\n\nCubre la investigacion de:\n\n- Incidentes de trabajo (eventos sin lesion)\n- Accidentes de trabajo leves, graves y mortales\n- Enfermedades laborales diagnosticadas como de origen laboral\n\nAplica en todas las sedes, centros de trabajo y actividades externas realizadas en nombre de la organizacion.",

            'definiciones' => "**Accidente de Trabajo:** Todo suceso repentino que sobrevenga por causa o con ocasion del trabajo, y que produzca en el trabajador una lesion organica, una perturbacion funcional o psiquiatrica, una invalidez o la muerte (Ley 1562 de 2012, art. 3).\n\n**Incidente de Trabajo:** Suceso acaecido en el curso del trabajo o en relacion con este, que tuvo el potencial de ser un accidente, en el que hubo personas involucradas sin que sufrieran lesiones o se presentaran danos a la propiedad y/o perdida en los procesos.\n\n**Enfermedad Laboral:** La contraida como resultado de la exposicion a factores de riesgo inherentes a la actividad laboral o del medio en el que el trabajador se ha visto obligado a trabajar (Ley 1562 de 2012, art. 4).\n\n**Causa Inmediata:** Circunstancias que se presentan justamente antes del contacto; por lo general son observables o se hacen sentir. Se clasifican en actos inseguros y condiciones inseguras.\n\n**Causa Basica:** Causas reales que se manifiestan detras de los sintomas; razones por las cuales ocurren los actos y condiciones inseguros. Se clasifican en factores personales y factores del trabajo.\n\n**FURAT:** Formato Unico de Reporte de Accidentes de Trabajo, establecido por la Resolucion 156 de 2005.\n\n**FUREL:** Formato Unico de Reporte de Enfermedad Laboral, establecido por la Resolucion 156 de 2005.\n\n**Investigacion de Accidente o Incidente:** Proceso sistematico de determinacion y ordenacion de causas, hechos o situaciones que generaron o favorecieron la ocurrencia del accidente o incidente (Resolucion 1401 de 2007).",

            'responsabilidades' => "**Alta Direccion / Representante Legal:**\n- Garantizar que se investiguen todos los incidentes, accidentes y enfermedades laborales\n- Asignar los recursos necesarios para la investigacion y las acciones correctivas\n- Reportar los accidentes graves y mortales a la Direccion Territorial del Ministerio de Trabajo dentro de los 2 dias habiles\n- Revisar los resultados de las investigaciones y aprobar las acciones correctivas\n\n**Responsable del SG-SST:**\n- Liderar y coordinar la investigacion de todos los eventos\n- Diligenciar y enviar el FURAT a la ARL dentro de los 2 dias habiles siguientes al accidente\n- Diligenciar el FUREL cuando se diagnostique enfermedad laboral\n- Conformar el equipo investigador segun la severidad del evento\n- Definir acciones correctivas y preventivas con el equipo investigador\n- Realizar seguimiento al cumplimiento de las acciones\n- Mantener los registros y evidencias durante minimo 20 anos\n\n**{$comite}:**\n- Participar en la investigacion de accidentes e incidentes\n- Verificar el cumplimiento de las acciones correctivas y preventivas\n- Proponer medidas de prevencion basadas en las investigaciones\n- Hacer seguimiento en las reuniones ordinarias\n\n**Trabajadores:**\n- Reportar inmediatamente cualquier incidente, accidente o condicion de salud de origen laboral\n- Colaborar con la investigacion proporcionando informacion veraz\n- Cumplir las acciones correctivas y preventivas establecidas\n- Participar en las actividades de socializacion de lecciones aprendidas",
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
