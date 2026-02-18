<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase ProcedimientoGestionCambio
 *
 * Implementa el Procedimiento de Gestion del Cambio del SG-SST
 * para el estandar 2.11.1 de la Resolucion 0312/2019.
 *
 * Permite evaluar el impacto sobre la SST que puedan generar los cambios
 * internos y externos a la empresa, e informar y capacitar a los trabajadores.
 *
 * TIPO A: Solo documento formal con IA (sin actividades PTA ni indicadores)
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class ProcedimientoGestionCambio extends AbstractDocumentoSST
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
        return 'procedimiento_gestion_cambio';
    }

    public function getNombre(): string
    {
        $config = $this->getConfig();
        return $config['nombre'] ?? 'Procedimiento de Gestion del Cambio';
    }

    public function getDescripcion(): string
    {
        $config = $this->getConfig();
        return $config['descripcion'] ?? 'Establece la metodologia para evaluar el impacto sobre la SST que generan los cambios internos y externos, e informar y capacitar a los trabajadores';
    }

    public function getEstandar(): ?string
    {
        return '2.11.1';
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
            ['numero' => 6, 'nombre' => 'Tipos de Cambios Internos y Externos', 'key' => 'tipos_cambios'],
            ['numero' => 7, 'nombre' => 'Procedimiento de Evaluacion del Impacto', 'key' => 'evaluacion_impacto'],
            ['numero' => 8, 'nombre' => 'Comunicacion e Informacion a los Trabajadores', 'key' => 'comunicacion_informacion'],
            ['numero' => 9, 'nombre' => 'Capacitacion ante Cambios', 'key' => 'capacitacion_cambios'],
            ['numero' => 10, 'nombre' => 'Seguimiento y Control', 'key' => 'seguimiento_control'],
            ['numero' => 11, 'nombre' => 'Registros y Evidencias', 'key' => 'registros'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        $config = $this->getConfig();
        if (!empty($config['firmantes'])) {
            return array_column($config['firmantes'], 'firmante_tipo');
        }

        return ['responsable_sst', 'representante_legal', 'consultor_sst'];
    }

    protected function getPromptFallback(string $seccionKey, int $estandares): string
    {
        $comite = $this->getTextoComite($estandares);

        $prompts = [
            'objetivo' => "Genera el objetivo del Procedimiento de Gestion del Cambio en SST.
Debe mencionar:
- El proposito de evaluar el impacto sobre la seguridad y salud en el trabajo que generan los cambios internos y externos
- Garantizar que se informe y capacite a los trabajadores sobre dichos cambios
- Cumplimiento del Decreto 1072 de 2015 (articulo 2.2.4.6.26) y Resolucion 0312 de 2019 (estandar 2.11.1)
- Prevenir la aparicion de nuevos peligros y riesgos derivados de los cambios
Maximo 2 parrafos concisos.",

            'alcance' => "Define el alcance del procedimiento de gestion del cambio. Debe especificar:
- Aplica a todos los cambios internos (procesos, instalaciones, maquinaria, metodos de trabajo, estructura organizacional, personal clave, turnos, contratistas)
- Aplica a todos los cambios externos (legislacion, normativa, tecnologia, condiciones del entorno, nuevos clientes/proyectos)
- A quien aplica: alta direccion, responsable SST, jefes de area, trabajadores, contratistas, {$comite}
- Cubre cambios temporales y permanentes
Maximo 2 parrafos.",

            'definiciones' => "Genera las definiciones clave para el procedimiento de gestion del cambio en SST. INCLUIR OBLIGATORIAMENTE:
- Gestion del cambio
- Cambio interno
- Cambio externo
- Evaluacion de impacto
- Peligro emergente
- Riesgo residual
- Analisis de riesgos
- Cambio temporal
- Cambio permanente
CANTIDAD: 9-11 definiciones basadas en normativa colombiana (Decreto 1072/2015).",

            'marco_legal' => "Genera el marco legal aplicable al procedimiento de gestion del cambio en SST. INCLUIR:
- Decreto 1072 de 2015, articulo 2.2.4.6.26 (Gestion del cambio)
- Decreto 1072 de 2015, articulo 2.2.4.6.8 (Obligaciones de los empleadores - numeral 9)
- Resolucion 0312 de 2019, estandar 2.11.1
- Ley 1562 de 2012 (Sistema General de Riesgos Laborales)
- ISO 45001:2018, clausula 8.1.3 (Gestion del cambio) como referencia voluntaria
Presentar en formato de listado con numero de norma, ano y descripcion breve.",

            'responsabilidades' => "Define las responsabilidades en la gestion del cambio:

**Alta Direccion / Representante Legal:**
- Aprobar los cambios significativos que afecten el SG-SST
- Asegurar recursos para evaluar y gestionar los cambios
- Informarse sobre los resultados de las evaluaciones de impacto

**Responsable del SG-SST:**
- Identificar y registrar los cambios que puedan impactar la SST
- Coordinar la evaluacion de impacto de cada cambio
- Definir y hacer seguimiento a las medidas de control
- Actualizar la identificacion de peligros y valoracion de riesgos
- Comunicar los cambios a los trabajadores

**{$comite}:**
- Participar en la evaluacion de impacto de los cambios
- Verificar que se implementen las medidas de control definidas
- Recibir y tramitar reportes de trabajadores sobre impactos de cambios

**Jefes de Area / Supervisores:**
- Reportar cambios en su area al responsable SST
- Implementar las medidas de control en su area
- Capacitar a su equipo sobre los cambios

**Trabajadores:**
- Reportar cambios o condiciones nuevas que identifiquen
- Participar en las capacitaciones sobre cambios
- Cumplir las nuevas medidas de control establecidas",

            'tipos_cambios' => "Describe los tipos de cambios internos y externos que deben evaluarse:

**CAMBIOS INTERNOS:**
Genera una tabla con ejemplos concretos para cada categoria:
- Cambios en procesos productivos o de servicio
- Introduccion de nueva maquinaria, equipos o herramientas
- Cambios en instalaciones o infraestructura fisica
- Cambios en la estructura organizacional (fusiones, nuevas areas)
- Cambios en el personal clave (nuevo gerente, nuevo responsable SST)
- Cambios en metodos o procedimientos de trabajo
- Cambios en materias primas, insumos o sustancias quimicas
- Cambios en turnos, horarios o jornadas laborales
- Ingreso de contratistas o subcontratistas nuevos
- Cambios en condiciones de almacenamiento

**CAMBIOS EXTERNOS:**
Genera una tabla con ejemplos concretos para cada categoria:
- Nueva legislacion o normativa aplicable en SST
- Cambios en requisitos de clientes o partes interesadas
- Nuevas tecnologias disponibles en el sector
- Cambios en el entorno fisico (vias, vecindarios, clima)
- Emergencias sanitarias o ambientales
- Cambios en proveedores de servicios criticos (ARL, EPS)

Para cada tipo, indicar: Categoria, Ejemplo tipico, Responsable de reportar.",

            'evaluacion_impacto' => "Describe el procedimiento paso a paso para evaluar el impacto de un cambio sobre la SST:

**PASO 1 - Identificacion del cambio:**
- Registro del cambio (quien lo solicita, descripcion, fecha prevista)
- Clasificacion: interno/externo, temporal/permanente, urgente/planificado

**PASO 2 - Analisis de impacto en SST:**
- Identificar nuevos peligros que puede generar el cambio
- Evaluar si los controles existentes siguen siendo eficaces
- Determinar si se requieren nuevos controles o EPP
- Evaluar impacto en la salud de los trabajadores

**PASO 3 - Definicion de medidas de control:**
- Aplicar jerarquia de controles (eliminacion, sustitucion, ingenieria, administrativos, EPP)
- Definir responsables y cronograma de implementacion
- Establecer indicadores de seguimiento

**PASO 4 - Aprobacion:**
- Revision por el responsable SST
- Aprobacion por la alta direccion (para cambios significativos)
- Registro de la decision

**PASO 5 - Implementacion y seguimiento:**
- Ejecutar las medidas definidas
- Verificar eficacia de los controles
- Actualizar documentos del SG-SST (matriz de peligros, procedimientos, etc.)

Incluir un diagrama de flujo textual del proceso.",

            'comunicacion_informacion' => "Describe como se comunica e informa a los trabajadores sobre los cambios:

**Antes del cambio:**
- Informacion sobre el cambio planificado y sus razones
- Explicacion de los nuevos peligros identificados
- Descripcion de las medidas de control que se implementaran
- Canales: reuniones de area, correos, carteleras, comunicados

**Durante el cambio:**
- Instrucciones operativas especificas
- Senalizacion temporal si aplica
- Supervision reforzada durante la transicion
- Canal abierto para reportar novedades

**Despues del cambio:**
- Retroalimentacion sobre la implementacion
- Actualizacion de procedimientos e instructivos
- Socializacion de lecciones aprendidas
- Reunion de cierre con el {$comite}

**Registros de comunicacion:**
- Listas de asistencia a reuniones informativas
- Actas de reunion del {$comite}
- Correos y comunicados formales
- Registro de socializacion de procedimientos actualizados",

            'capacitacion_cambios' => "Describe el plan de capacitacion ante cambios:

**Cuando se requiere capacitacion:**
- Siempre que un cambio introduzca nuevos peligros o riesgos
- Cuando se modifiquen procedimientos de trabajo
- Al introducir nueva maquinaria o equipos
- Cuando cambien las medidas de control o EPP requeridos
- Al ingresar personal nuevo a areas con cambios recientes

**Contenido minimo de la capacitacion:**
- Descripcion del cambio realizado
- Nuevos peligros y riesgos identificados
- Medidas de control implementadas
- Procedimientos nuevos o actualizados
- Uso correcto de nuevos EPP (si aplica)
- Procedimiento de reporte de novedades

**Metodologia:**
- Capacitacion presencial (preferible para cambios significativos)
- Material de apoyo (presentaciones, fichas tecnicas, videos)
- Evaluacion de comprension (quiz o practica demostrativa)

**Responsable:** Responsable del SG-SST con apoyo de jefes de area

**Registros:**
- Lista de asistencia firmada
- Material de la capacitacion
- Evaluacion de conocimiento
- Fecha y duracion de la capacitacion",

            'seguimiento_control' => "Describe como se realiza el seguimiento y control de los cambios gestionados:

**Seguimiento a corto plazo (1-4 semanas post-cambio):**
- Verificar implementacion efectiva de los controles
- Monitorear incidentes o condiciones inseguras relacionadas
- Recoger retroalimentacion de los trabajadores afectados
- Ajustar controles si es necesario

**Seguimiento a mediano plazo (1-6 meses post-cambio):**
- Evaluar eficacia de las medidas de control implementadas
- Revisar indicadores de accidentalidad y ausentismo del area
- Verificar que las capacitaciones se completaron
- Actualizar la matriz de peligros y riesgos si aplica

**Indicadores de gestion del cambio:**
- Numero de cambios identificados y evaluados vs. cambios realizados sin evaluacion
- Porcentaje de cambios con evaluacion de impacto completada
- Tiempo promedio entre identificacion del cambio y evaluacion de impacto
- Numero de incidentes asociados a cambios no gestionados

**Revision periodica:**
- Revision trimestral del registro de cambios por el responsable SST
- Inclusion en la revision anual por la alta direccion
- Reporte al {$comite} sobre el estado de la gestion del cambio",

            'registros' => "Describe los registros y evidencias que se deben mantener:

**Formatos y registros requeridos:**
1. Formato de Solicitud y Registro de Cambios (incluir: descripcion del cambio, fecha, solicitante, tipo, area afectada)
2. Formato de Evaluacion de Impacto en SST (incluir: peligros identificados, riesgos evaluados, controles propuestos)
3. Plan de Accion de Gestion del Cambio (incluir: medidas, responsables, fechas, estado)
4. Acta de Comunicacion y Socializacion del Cambio
5. Lista de Asistencia a Capacitacion sobre el Cambio
6. Registro Fotografico (antes y despues, si aplica)
7. Actualizacion de la Matriz de Peligros y Riesgos

**Archivo y conservacion:**
- Tiempo minimo de conservacion: 20 anos (Resolucion 0312 de 2019)
- Ubicacion: Archivo documental del SG-SST
- Acceso: Responsable SST, alta direccion, auditor, {$comite}

**Trazabilidad:**
- Cada cambio debe tener un consecutivo unico
- El historial de cambios debe incluir fecha de registro, evaluacion, aprobacion, implementacion y cierre
- Los documentos actualizados deben reflejar el cambio en su control de versiones"
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' del Procedimiento de Gestion del Cambio segun la Resolucion 0312/2019 (estandar 2.11.1) y el Decreto 1072/2015 (articulo 2.2.4.6.26). El procedimiento debe permitir evaluar el impacto sobre la SST de cambios internos y externos, e informar y capacitar a los trabajadores.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Establecer la metodologia para identificar, evaluar y gestionar los cambios internos y externos que puedan generar impactos sobre la Seguridad y Salud en el Trabajo en {$nombreEmpresa}, garantizando que los trabajadores sean informados y capacitados oportunamente sobre dichos cambios.\n\nEste procedimiento da cumplimiento al articulo 2.2.4.6.26 del Decreto 1072 de 2015 y al estandar 2.11.1 de la Resolucion 0312 de 2019, que exigen contar con un procedimiento de gestion del cambio que permita evaluar el impacto sobre la SST.",

            'alcance' => "Este procedimiento aplica a todos los cambios internos (procesos, instalaciones, maquinaria, metodos de trabajo, estructura organizacional, personal, turnos, contratistas) y externos (legislacion, normativa, tecnologia, condiciones del entorno) que puedan afectar la seguridad y salud de los trabajadores de {$nombreEmpresa}.\n\nIncluye cambios temporales y permanentes, y es de obligatorio cumplimiento para la alta direccion, el responsable del SG-SST, jefes de area, trabajadores, contratistas y el {$comite}.",

            'definiciones' => "**Gestion del Cambio:** Proceso sistematico para identificar, evaluar y controlar los efectos que los cambios pueden generar sobre la seguridad y salud de los trabajadores.\n\n**Cambio Interno:** Modificacion originada dentro de la organizacion que puede afectar las condiciones de SST (procesos, maquinaria, personal, instalaciones, metodos de trabajo).\n\n**Cambio Externo:** Modificacion originada fuera de la organizacion que puede afectar la SST (legislacion, normativa, tecnologia, condiciones del entorno, requisitos de clientes).\n\n**Evaluacion de Impacto:** Analisis sistematico para determinar como un cambio puede afectar la seguridad y salud de los trabajadores y que medidas de control se requieren.\n\n**Peligro Emergente:** Nuevo peligro que surge como consecuencia de un cambio en los procesos, instalaciones o entorno de trabajo.\n\n**Riesgo Residual:** Riesgo que permanece despues de implementar las medidas de control asociadas a un cambio.",

            'marco_legal' => "**Decreto 1072 de 2015:**\n- Articulo 2.2.4.6.26: Gestion del cambio - El empleador debe implementar un procedimiento para evaluar el impacto sobre la SST que puedan generar los cambios internos o externos.\n- Articulo 2.2.4.6.8, numeral 9: Obligacion de informar y capacitar a los trabajadores sobre los cambios.\n\n**Resolucion 0312 de 2019:**\n- Estandar 2.11.1: Evaluacion del impacto de cambios internos y externos en el SG-SST.\n\n**Ley 1562 de 2012:**\n- Articulo 1: Definiciones del Sistema General de Riesgos Laborales.\n\n**ISO 45001:2018 (referencia voluntaria):**\n- Clausula 8.1.3: Gestion del cambio.",

            'responsabilidades' => "**Alta Direccion / Representante Legal:**\n- Aprobar los cambios significativos que afecten el SG-SST\n- Asegurar recursos para evaluar y gestionar los cambios\n- Informarse sobre los resultados de las evaluaciones de impacto\n\n**Responsable del SG-SST:**\n- Identificar y registrar los cambios que puedan impactar la SST\n- Coordinar la evaluacion de impacto de cada cambio\n- Definir y hacer seguimiento a las medidas de control\n- Comunicar los cambios y capacitar a los trabajadores\n\n**{$comite}:**\n- Participar en la evaluacion de impacto de los cambios\n- Verificar que se implementen las medidas de control\n- Recibir y tramitar reportes de trabajadores\n\n**Trabajadores:**\n- Reportar cambios o condiciones nuevas que identifiquen\n- Participar en las capacitaciones sobre cambios\n- Cumplir las nuevas medidas de control establecidas",

            'tipos_cambios' => "**CAMBIOS INTERNOS:**\n- Cambios en procesos productivos o de servicio\n- Introduccion de nueva maquinaria, equipos o herramientas\n- Cambios en instalaciones o infraestructura fisica\n- Cambios en la estructura organizacional\n- Cambios en metodos o procedimientos de trabajo\n- Cambios en materias primas, insumos o sustancias quimicas\n- Cambios en turnos, horarios o jornadas laborales\n- Ingreso de contratistas o subcontratistas nuevos\n\n**CAMBIOS EXTERNOS:**\n- Nueva legislacion o normativa aplicable en SST\n- Cambios en requisitos de clientes o partes interesadas\n- Nuevas tecnologias disponibles en el sector\n- Cambios en el entorno fisico\n- Emergencias sanitarias o ambientales\n- Cambios en proveedores de servicios criticos",

            'evaluacion_impacto' => "**Paso 1 - Identificacion:** Registro del cambio (solicitante, descripcion, fecha, clasificacion).\n\n**Paso 2 - Analisis de Impacto:** Identificar nuevos peligros, evaluar controles existentes, determinar nuevos controles necesarios.\n\n**Paso 3 - Medidas de Control:** Aplicar jerarquia de controles, definir responsables y cronograma.\n\n**Paso 4 - Aprobacion:** Revision por responsable SST, aprobacion por alta direccion.\n\n**Paso 5 - Implementacion:** Ejecutar medidas, verificar eficacia, actualizar documentos del SG-SST.",

            'comunicacion_informacion' => "**Antes del cambio:** Informar sobre el cambio, nuevos peligros y medidas de control.\n**Durante el cambio:** Instrucciones operativas, supervision reforzada, canal de reporte.\n**Despues del cambio:** Retroalimentacion, actualizacion de procedimientos, lecciones aprendidas.\n\n**Registros:** Listas de asistencia, actas de reunion del {$comite}, correos y comunicados formales.",

            'capacitacion_cambios' => "**Se requiere capacitacion cuando:**\n- Un cambio introduzca nuevos peligros o riesgos\n- Se modifiquen procedimientos de trabajo\n- Se introduzca nueva maquinaria o equipos\n- Cambien medidas de control o EPP\n\n**Contenido minimo:** Descripcion del cambio, peligros y riesgos, medidas de control, procedimientos actualizados.\n\n**Registros:** Lista de asistencia, material de capacitacion, evaluacion de conocimiento.",

            'seguimiento_control' => "**Corto plazo (1-4 semanas):** Verificar implementacion de controles, monitorear incidentes.\n\n**Mediano plazo (1-6 meses):** Evaluar eficacia de medidas, revisar indicadores.\n\n**Indicadores:** Cambios evaluados vs. no evaluados, tiempo de respuesta, incidentes asociados.\n\n**Revision periodica:** Trimestral por responsable SST, anual por alta direccion, reporte al {$comite}.",

            'registros' => "**Formatos requeridos:**\n1. Formato de Solicitud y Registro de Cambios\n2. Formato de Evaluacion de Impacto en SST\n3. Plan de Accion de Gestion del Cambio\n4. Acta de Comunicacion y Socializacion\n5. Lista de Asistencia a Capacitacion\n6. Registro Fotografico\n7. Actualizacion de Matriz de Peligros y Riesgos\n\n**Conservacion:** Minimo 20 anos (Resolucion 0312 de 2019).\n**Acceso:** Responsable SST, alta direccion, auditor, {$comite}."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
