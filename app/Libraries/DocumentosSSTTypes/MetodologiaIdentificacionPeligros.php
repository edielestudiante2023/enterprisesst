<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase MetodologiaIdentificacionPeligros
 *
 * Implementa la Metodologia para la Identificacion de Peligros,
 * Evaluacion y Valoracion de Riesgos del SG-SST para el estandar 4.1.1
 * de la Resolucion 0312/2019.
 *
 * Permite documentar la metodologia aplicada (GTC 45, NTC-ISO 31000 u otra)
 * para identificar peligros, evaluar y valorar riesgos, y establecer controles
 * con alcance a todos los procesos, actividades rutinarias y no rutinarias,
 * maquinas, equipos y todos los trabajadores.
 *
 * TIPO A: Solo documento formal con IA (sin actividades PTA ni indicadores)
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class MetodologiaIdentificacionPeligros extends AbstractDocumentoSST
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
        return 'metodologia_identificacion_peligros';
    }

    public function getNombre(): string
    {
        $config = $this->getConfig();
        return $config['nombre'] ?? 'Metodologia para la Identificacion de Peligros, Evaluacion y Valoracion de Riesgos';
    }

    public function getDescripcion(): string
    {
        $config = $this->getConfig();
        return $config['descripcion'] ?? 'Establece la metodologia para identificar peligros, evaluar y valorar riesgos y establecer controles con alcance a todos los procesos y trabajadores';
    }

    public function getEstandar(): ?string
    {
        return '4.1.1';
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
            ['numero' => 6, 'nombre' => 'Metodologia Adoptada', 'key' => 'metodologia_adoptada'],
            ['numero' => 7, 'nombre' => 'Identificacion de Peligros', 'key' => 'identificacion_peligros'],
            ['numero' => 8, 'nombre' => 'Evaluacion y Valoracion de Riesgos', 'key' => 'evaluacion_valoracion_riesgos'],
            ['numero' => 9, 'nombre' => 'Determinacion de Controles', 'key' => 'determinacion_controles'],
            ['numero' => 10, 'nombre' => 'Priorizacion de Riesgos', 'key' => 'priorizacion_riesgos'],
            ['numero' => 11, 'nombre' => 'Documentacion y Actualizacion de la Matriz', 'key' => 'documentacion_matriz'],
            ['numero' => 12, 'nombre' => 'Registros y Evidencias', 'key' => 'registros'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        $config = $this->getConfig();
        if (!empty($config['firmantes'])) {
            return array_column($config['firmantes'], 'firmante_tipo');
        }

        return ['consultor_sst', 'responsable_sst', 'representante_legal'];
    }

    protected function getPromptFallback(string $seccionKey, int $estandares): string
    {
        $comite = $this->getTextoComite($estandares);

        $prompts = [
            'objetivo' => "Genera el objetivo de la Metodologia para la Identificacion de Peligros, Evaluacion y Valoracion de Riesgos.
Debe mencionar:
- Establecer una metodologia sistematica para identificar peligros, evaluar y valorar riesgos, y establecer controles
- Alcance a todos los procesos, actividades rutinarias y no rutinarias, maquinas, equipos
- Aplica a todos los trabajadores independientemente de su forma de vinculacion o contratacion
- Identificar riesgos prioritarios
- Cumplimiento del Decreto 1072/2015 (art. 2.2.4.6.15 y 2.2.4.6.23) y Resolucion 0312/2019 (estandar 4.1.1)
- Referencia a la GTC 45:2012 como metodologia base adoptada
Maximo 2 parrafos concisos.",

            'alcance' => "Define el alcance de la metodologia. Debe especificar:
- Aplica a TODOS los procesos de la organizacion (administrativos, operativos, de apoyo)
- Cubre actividades rutinarias y no rutinarias
- Incluye todas las maquinas, equipos, herramientas e instalaciones
- Aplica a todos los trabajadores: directos, contratistas, subcontratistas, temporales, practicantes, visitantes
- Cubre todas las sedes, centros de trabajo y proyectos
- Incluye peligros internos y externos al lugar de trabajo
- Es aplicable durante todo el ciclo de vida de las actividades (planificacion, ejecucion, mantenimiento)
Maximo 2 parrafos.",

            'definiciones' => "Genera las definiciones clave para la metodologia de identificacion de peligros y valoracion de riesgos. INCLUIR OBLIGATORIAMENTE:
- Peligro
- Riesgo
- Identificacion de peligros
- Evaluacion del riesgo
- Valoracion del riesgo
- Nivel de consecuencia (NC)
- Nivel de probabilidad (NP)
- Nivel de riesgo (NR)
- Nivel de deficiencia (ND)
- Nivel de exposicion (NE)
- Aceptabilidad del riesgo
- Control del riesgo
- Actividad rutinaria
- Actividad no rutinaria
- Exposicion
- Consecuencia
- Medida de control
- Matriz de peligros y riesgos
CANTIDAD: 15-18 definiciones basadas en GTC 45:2012 y Decreto 1072/2015.",

            'marco_legal' => "Genera el marco legal aplicable a la metodologia de identificacion de peligros y valoracion de riesgos. INCLUIR:
- Decreto 1072 de 2015, art. 2.2.4.6.15 (Identificacion de peligros, evaluacion y valoracion de los riesgos)
- Decreto 1072 de 2015, art. 2.2.4.6.23 (Gestion de los peligros y riesgos)
- Resolucion 0312 de 2019, estandar 4.1.1 (Metodologia para la identificacion de peligros)
- GTC 45:2012 (Guia para la identificacion de peligros y valoracion de riesgos)
- NTC-ISO 31000:2018 (Gestion del riesgo - Directrices)
- Ley 1562 de 2012 (Sistema General de Riesgos Laborales)
- Decreto 1295 de 1994 (Organizacion y administracion del SGRL)
- Resolucion 2400 de 1979 (Disposiciones sobre higiene y seguridad industrial)
Presentar en formato de listado con numero de norma, ano y descripcion breve.",

            'responsabilidades' => "Define las responsabilidades en la identificacion de peligros y valoracion de riesgos:

**Alta Direccion / Representante Legal:**
- Garantizar recursos para la identificacion de peligros y valoracion de riesgos
- Aprobar la metodologia adoptada
- Implementar controles derivados de la valoracion

**Responsable del SG-SST:**
- Aplicar la metodologia de identificacion de peligros y valoracion de riesgos
- Elaborar y actualizar la Matriz de Peligros y Riesgos
- Coordinar la participacion de los trabajadores en la identificacion
- Comunicar los resultados a la alta direccion y al {$comite}
- Priorizar riesgos y proponer controles

**{$comite}:**
- Participar en la identificacion de peligros y valoracion de riesgos
- Verificar que la metodologia se aplique con el alcance requerido
- Proponer medidas de control

**Jefes de Area / Supervisores:**
- Reportar peligros nuevos o cambios en sus areas
- Participar en las inspecciones y evaluaciones
- Implementar los controles definidos en su area

**Trabajadores:**
- Reportar peligros, condiciones inseguras y actos inseguros
- Participar activamente en la identificacion de peligros
- Cumplir con los controles establecidos",

            'metodologia_adoptada' => "Describe la metodologia adoptada para la identificacion de peligros y valoracion de riesgos. Debe incluir:

**Metodologia base:** GTC 45:2012 - Guia para la identificacion de los peligros y la valoracion de los riesgos en seguridad y salud ocupacional.

**Justificacion:** Es la guia tecnica colombiana reconocida y recomendada por el Ministerio del Trabajo para dar cumplimiento al Decreto 1072/2015. Compatible con ISO 31000 y OHSAS 18001/ISO 45001.

**Descripcion general del proceso:**
1. Definir el instrumento (Matriz de Peligros y Riesgos)
2. Clasificar los procesos, actividades y tareas
3. Identificar los peligros asociados a cada actividad
4. Identificar los controles existentes
5. Evaluar el riesgo (probabilidad x consecuencia)
6. Valorar si el riesgo es aceptable o no
7. Elaborar plan de accion para riesgos no aceptables
8. Revisar conveniencia del plan

**Clasificacion de peligros:** Segun GTC 45 (biologico, fisico, quimico, psicosocial, biomecanico, condiciones de seguridad, fenomenos naturales).

**Instrumentos:** Formato Matriz de Peligros y Riesgos, listas de verificacion, inspecciones planeadas.",

            'identificacion_peligros' => "Describe como se realiza la identificacion de peligros:

**Fuentes de informacion:**
- Inspecciones planeadas de seguridad
- Reporte de actos y condiciones inseguras por trabajadores
- Investigacion de incidentes y accidentes
- Evaluaciones medicas ocupacionales
- Mediciones ambientales
- Estudios de puesto de trabajo
- Manuales de maquinaria y fichas de seguridad (FDS)
- Observaciones directas de las actividades

**Proceso de identificacion:**
1. Describir el proceso, zona o actividad
2. Identificar si es rutinaria o no rutinaria
3. Clasificar el peligro segun GTC 45 (biologico, fisico, quimico, psicosocial, biomecanico, condiciones de seguridad, fenomenos naturales)
4. Describir el peligro especifico (ej: ruido continuo >85 dB)
5. Identificar los efectos posibles (danos a la salud)
6. Determinar el numero de trabajadores expuestos

**Alcance obligatorio:**
- Actividades rutinarias (diarias, repetitivas) y no rutinarias (mantenimiento, emergencias)
- Toda maquinaria, equipo y herramienta
- Todos los trabajadores sin importar vinculacion (directos, temporales, contratistas, visitantes)
- Peligros generados fuera del lugar de trabajo que afecten la salud (desplazamientos, trabajo en campo)

**Frecuencia:** Minimo anual y cada vez que ocurra un accidente mortal, un evento catastrofico, o cambios significativos en procesos, instalaciones o maquinaria.",

            'evaluacion_valoracion_riesgos' => "Describe como se evaluan y valoran los riesgos segun GTC 45:2012:

**Evaluacion del riesgo - Nivel de Riesgo (NR) = Nivel de Probabilidad (NP) x Nivel de Consecuencia (NC):**

**1. Nivel de Deficiencia (ND):**
| Nivel | Valor | Significado |
|-------|-------|-------------|
| Muy Alto (MA) | 10 | Se han detectado peligros que determinan posible generacion de incidentes muy significativos |
| Alto (A) | 6 | Se han detectado peligros que pueden dar lugar a incidentes significativos |
| Medio (M) | 2 | Se han detectado peligros que pueden dar lugar a incidentes poco significativos |
| Bajo (B) | 0 | No se ha detectado peligro o la eficacia de controles es alta |

**2. Nivel de Exposicion (NE):**
| Nivel | Valor | Significado |
|-------|-------|-------------|
| Continua (EC) | 4 | La situacion de exposicion se presenta sin interrupcion o varias veces con tiempo prolongado |
| Frecuente (EF) | 3 | Varias veces durante la jornada por tiempos cortos |
| Ocasional (EO) | 2 | Alguna vez durante la jornada laboral y por un periodo de tiempo corto |
| Esporadica (EE) | 1 | De manera eventual |

**3. Nivel de Probabilidad (NP) = ND x NE**
**4. Nivel de Consecuencia (NC):** Mortal/Catastrofico (100), Muy grave (60), Grave (25), Leve (10)
**5. Nivel de Riesgo (NR) = NP x NC**

**Valoracion del riesgo - Aceptabilidad:**
| Nivel de Riesgo | Interpretacion | Aceptabilidad |
|-----------------|----------------|---------------|
| I (4000-600) | Situacion critica | No Aceptable |
| II (500-150) | Corregir y adoptar medidas de control inmediato | No Aceptable o Aceptable con control especifico |
| III (120-40) | Mejorar si es posible | Aceptable |
| IV (20) | Mantener medidas de control existentes | Aceptable |",

            'determinacion_controles' => "Describe como se determinan los controles segun la jerarquia de la GTC 45 y el Decreto 1072/2015:

**Jerarquia de controles (orden de prioridad):**
1. **Eliminacion:** Suprimir el peligro (cambiar proceso, retirar sustancia)
2. **Sustitucion:** Reemplazar el peligro por uno de menor riesgo (sustancia menos toxica, herramienta mas segura)
3. **Controles de ingenieria:** Aislar, encerrar, ventilar, insonorizar, automatizar
4. **Controles administrativos:** Senalizacion, procedimientos, permisos de trabajo, rotacion, capacitacion
5. **Equipos de Proteccion Personal (EPP):** Ultimo recurso, complementario a los anteriores

**Para cada control se debe definir:**
- Descripcion de la medida
- Responsable de implementacion
- Fecha limite de implementacion
- Recursos requeridos
- Indicador de seguimiento
- Eficacia esperada

**Criterios para seleccionar controles:**
- Eficacia para reducir o eliminar el riesgo
- Factibilidad tecnica y economica
- No generar nuevos peligros
- Proteger a todos los trabajadores expuestos

**Los controles existentes deben clasificarse en:**
- Controles en la fuente
- Controles en el medio
- Controles en el individuo (trabajador)",

            'priorizacion_riesgos' => "Describe como se priorizan los riesgos para la intervencion:

**Criterios de priorizacion:**
1. Nivel de riesgo (NR): Los riesgos de Nivel I y II son prioritarios
2. Numero de trabajadores expuestos
3. Peor consecuencia posible (mortal, incapacidad permanente)
4. Requisito legal especifico que lo exija
5. Resultados de accidentalidad (donde se han materializado incidentes)

**Tratamiento segun nivel de riesgo:**
- **Nivel I (No Aceptable):** Intervencion inmediata. Suspender actividad hasta reducir el riesgo. Definir controles de ingenieria o sustitucion.
- **Nivel II (No Aceptable o Aceptable con control):** Intervencion a corto plazo. Implementar controles administrativos y EPP mientras se definen controles de mayor jerarquia.
- **Nivel III (Aceptable):** Mejorar cuando sea posible. Mantener y fortalecer controles existentes.
- **Nivel IV (Aceptable):** Mantener controles actuales. Incluir en inspecciones de rutina.

**Riesgos prioritarios identificados:**
Se deben comunicar a la alta direccion y al {$comite}. Incluir en el plan de trabajo anual del SG-SST y hacer seguimiento trimestral.",

            'documentacion_matriz' => "Describe como se documenta, revisa y actualiza la Matriz de Peligros y Riesgos:

**Contenido minimo de la Matriz:**
- Proceso / Area / Zona
- Actividad (rutinaria / no rutinaria)
- Tarea especifica
- Clasificacion del peligro (GTC 45)
- Descripcion del peligro
- Efectos posibles
- Controles existentes (fuente, medio, individuo)
- Evaluacion del riesgo (ND, NE, NP, NC, NR)
- Interpretacion y aceptabilidad
- Numero de expuestos
- Medidas de intervencion propuestas
- Responsable y fecha

**Actualizacion obligatoria cuando:**
- Se presente un accidente de trabajo mortal
- Ocurra un evento catastrofico
- Haya cambios en procesos, instalaciones, maquinaria o equipos
- Cambien las condiciones de trabajo o el entorno
- Se identifiquen nuevos peligros
- Los resultados de inspecciones o auditorias lo requieran
- Como minimo una (1) vez al ano

**Participacion de los trabajadores:**
La identificacion de peligros debe realizarse CON la participacion de los trabajadores de todos los niveles, conforme al art. 2.2.4.6.15 del Decreto 1072/2015.

**Revision y aprobacion:**
La matriz actualizada debe ser socializada al {$comite} y aprobada por la alta direccion.",

            'registros' => "Describe los registros y evidencias que se deben mantener:

**Formatos y registros requeridos:**
1. Matriz de Peligros y Riesgos (formato GTC 45)
2. Procedimiento documentado de la metodologia
3. Acta de socializacion de la metodologia al {$comite}
4. Listas de asistencia a capacitacion en la metodologia
5. Registro de participacion de trabajadores en la identificacion
6. Acta de aprobacion de la Matriz por la alta direccion
7. Plan de accion para riesgos priorizados
8. Registros de actualizaciones de la Matriz (control de cambios)
9. Informes de inspecciones de seguridad
10. Reportes de condiciones y actos inseguros

**Archivo y conservacion:**
- Tiempo minimo de conservacion: 20 anos (Resolucion 0312 de 2019)
- Ubicacion: Archivo documental del SG-SST
- Acceso: Responsable SST, alta direccion, auditor, {$comite}, ARL

**Trazabilidad:**
- Cada version de la Matriz debe tener fecha de elaboracion, revision y aprobacion
- Mantener historico de versiones anteriores
- Los documentos actualizados deben reflejar el cambio en su control de versiones"
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' de la Metodologia para la Identificacion de Peligros, Evaluacion y Valoracion de Riesgos segun la Resolucion 0312/2019 (estandar 4.1.1) y el Decreto 1072/2015. La metodologia debe basarse en la GTC 45:2012 y abarcar todos los procesos, actividades, maquinas, equipos y trabajadores.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Establecer la metodologia sistematica para la identificacion de peligros, evaluacion y valoracion de los riesgos y determinacion de controles en {$nombreEmpresa}, con alcance a todos los procesos, actividades rutinarias y no rutinarias, maquinas, equipos y a todos los trabajadores independientemente de su forma de vinculacion o contratacion, identificando aquellos riesgos que son prioritarios.\n\nEsta metodologia da cumplimiento a los articulos 2.2.4.6.15 y 2.2.4.6.23 del Decreto 1072 de 2015 y al estandar 4.1.1 de la Resolucion 0312 de 2019, adoptando como referencia la Guia Tecnica Colombiana GTC 45:2012.",

            'alcance' => "Esta metodologia aplica a todos los procesos, actividades y tareas de {$nombreEmpresa}, incluyendo actividades rutinarias y no rutinarias, todas las maquinas, equipos y herramientas, e involucra a todos los trabajadores directos, contratistas, subcontratistas, temporales, practicantes y visitantes en todas las sedes y centros de trabajo.\n\nCubre la identificacion de peligros internos y externos al lugar de trabajo, abarcando todas las clasificaciones de peligros segun la GTC 45:2012: biologicos, fisicos, quimicos, psicosociales, biomecanicos, condiciones de seguridad y fenomenos naturales.",

            'definiciones' => "**Peligro:** Fuente, situacion o acto con potencial de dano en terminos de enfermedad o lesion a las personas, o una combinacion de estos (GTC 45).\n\n**Riesgo:** Combinacion de la probabilidad de que ocurra un evento o exposicion peligrosa y la severidad de la lesion o enfermedad que puede ser causada por el evento o exposicion (GTC 45).\n\n**Identificacion de peligros:** Proceso para reconocer si existe un peligro y definir sus caracteristicas.\n\n**Evaluacion del riesgo:** Proceso para determinar el nivel de riesgo asociado a la probabilidad y la consecuencia.\n\n**Valoracion del riesgo:** Proceso de evaluar los riesgos que surgen de un peligro teniendo en cuenta la suficiencia de los controles existentes y de decidir si el riesgo es aceptable o no.\n\n**Actividad rutinaria:** Actividad que forma parte de la operacion normal de la organizacion, se ha planificado y es estandarizable.\n\n**Actividad no rutinaria:** Actividad que no forma parte de la operacion normal, no es estandarizable debido a la diversidad de escenarios y condiciones.\n\n**Nivel de riesgo (NR):** Magnitud de un riesgo resultante del producto del nivel de probabilidad por el nivel de consecuencia.",

            'marco_legal' => "**Decreto 1072 de 2015:**\n- Articulo 2.2.4.6.15: Identificacion de peligros, evaluacion y valoracion de los riesgos.\n- Articulo 2.2.4.6.23: Gestion de los peligros y riesgos.\n\n**Resolucion 0312 de 2019:**\n- Estandar 4.1.1: Metodologia para la identificacion de peligros, evaluacion y valoracion de los riesgos.\n\n**GTC 45:2012:**\n- Guia para la identificacion de los peligros y la valoracion de los riesgos en seguridad y salud ocupacional.\n\n**NTC-ISO 31000:2018:**\n- Gestion del riesgo - Directrices.\n\n**Ley 1562 de 2012:**\n- Sistema General de Riesgos Laborales.\n\n**Resolucion 2400 de 1979:**\n- Disposiciones sobre higiene y seguridad industrial.",

            'responsabilidades' => "**Alta Direccion / Representante Legal:**\n- Garantizar recursos para la identificacion de peligros y valoracion de riesgos\n- Aprobar la metodologia adoptada y la Matriz de Peligros\n- Implementar controles derivados de la valoracion\n\n**Responsable del SG-SST:**\n- Aplicar la metodologia de identificacion de peligros y valoracion de riesgos\n- Elaborar y actualizar la Matriz de Peligros y Riesgos\n- Coordinar la participacion de los trabajadores\n- Comunicar resultados a la alta direccion y al {$comite}\n- Priorizar riesgos y proponer controles\n\n**{$comite}:**\n- Participar en la identificacion de peligros y valoracion de riesgos\n- Verificar que la metodologia se aplique con el alcance requerido\n- Proponer medidas de control\n\n**Trabajadores:**\n- Reportar peligros, condiciones inseguras y actos inseguros\n- Participar activamente en la identificacion de peligros\n- Cumplir con los controles establecidos",

            'metodologia_adoptada' => "{$nombreEmpresa} adopta como metodologia para la identificacion de peligros y valoracion de riesgos la Guia Tecnica Colombiana GTC 45:2012, reconocida y recomendada por el Ministerio del Trabajo para dar cumplimiento al Decreto 1072 de 2015.\n\nEl proceso comprende: 1) Definir el instrumento (Matriz de Peligros y Riesgos), 2) Clasificar procesos, actividades y tareas, 3) Identificar peligros asociados, 4) Identificar controles existentes, 5) Evaluar el riesgo, 6) Valorar la aceptabilidad del riesgo, 7) Elaborar plan de accion para riesgos no aceptables, 8) Revisar conveniencia del plan.\n\nLa clasificacion de peligros sigue las categorias de la GTC 45: biologico, fisico, quimico, psicosocial, biomecanico, condiciones de seguridad y fenomenos naturales.",

            'identificacion_peligros' => "La identificacion de peligros se realiza mediante: inspecciones planeadas, reporte de actos y condiciones inseguras, investigacion de incidentes, evaluaciones medicas, mediciones ambientales, estudios de puesto de trabajo y observaciones directas.\n\nPara cada actividad se identifica: proceso/area, si es rutinaria o no rutinaria, clasificacion del peligro segun GTC 45, descripcion especifica, efectos posibles y numero de trabajadores expuestos.\n\nLa identificacion se actualiza anualmente y cada vez que ocurra un accidente mortal, evento catastrofico, o cambios en procesos, instalaciones o maquinaria.",

            'evaluacion_valoracion_riesgos' => "La evaluacion se realiza segun GTC 45:2012 calculando el Nivel de Riesgo (NR) = Nivel de Probabilidad (NP) x Nivel de Consecuencia (NC), donde NP = Nivel de Deficiencia (ND) x Nivel de Exposicion (NE).\n\nNiveles de deficiencia: Muy Alto (10), Alto (6), Medio (2), Bajo (0). Niveles de exposicion: Continua (4), Frecuente (3), Ocasional (2), Esporadica (1). Niveles de consecuencia: Mortal (100), Muy grave (60), Grave (25), Leve (10).\n\nLa aceptabilidad se determina segun: Nivel I (No Aceptable), Nivel II (No Aceptable o Aceptable con control), Nivel III (Aceptable), Nivel IV (Aceptable).",

            'determinacion_controles' => "Los controles se determinan siguiendo la jerarquia: 1) Eliminacion, 2) Sustitucion, 3) Controles de ingenieria, 4) Controles administrativos, 5) Equipos de Proteccion Personal (EPP). Se priorizan controles de mayor jerarquia sobre los de menor.\n\nLos controles existentes se clasifican en: controles en la fuente, controles en el medio y controles en el individuo. Para cada control se define: descripcion, responsable, fecha limite, recursos y eficacia esperada.",

            'priorizacion_riesgos' => "Los riesgos se priorizan segun: nivel de riesgo (NR), numero de trabajadores expuestos, peor consecuencia posible, requisitos legales y resultados de accidentalidad.\n\nNivel I: intervencion inmediata, suspender actividad. Nivel II: intervencion a corto plazo. Nivel III: mejorar cuando sea posible. Nivel IV: mantener controles actuales.\n\nLos riesgos prioritarios se comunican a la alta direccion y al {$comite}, se incluyen en el plan de trabajo anual del SG-SST y se les hace seguimiento trimestral.",

            'documentacion_matriz' => "La Matriz de Peligros y Riesgos contiene: proceso, actividad, tarea, clasificacion y descripcion del peligro, efectos posibles, controles existentes, evaluacion del riesgo (ND, NE, NP, NC, NR), aceptabilidad, expuestos, medidas de intervencion, responsable y fecha.\n\nSe actualiza obligatoriamente cuando: accidente mortal, evento catastrofico, cambios en procesos/instalaciones/maquinaria, nuevos peligros, resultados de inspecciones/auditorias, y minimo una vez al ano. La identificacion se realiza CON participacion de los trabajadores y la matriz es aprobada por la alta direccion.",

            'registros' => "**Formatos requeridos:**\n1. Matriz de Peligros y Riesgos (formato GTC 45)\n2. Procedimiento documentado de la metodologia\n3. Acta de socializacion al {$comite}\n4. Listas de asistencia a capacitacion\n5. Registro de participacion de trabajadores\n6. Acta de aprobacion por alta direccion\n7. Plan de accion para riesgos priorizados\n8. Registros de actualizaciones (control de cambios)\n\n**Conservacion:** Minimo 20 anos (Resolucion 0312 de 2019).\n**Acceso:** Responsable SST, alta direccion, auditor, {$comite}, ARL."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
