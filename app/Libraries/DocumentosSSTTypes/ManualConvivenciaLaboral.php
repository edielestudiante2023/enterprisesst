<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase ManualConvivenciaLaboral
 *
 * Manual de Convivencia Laboral - Estandar 1.1.8
 * Basado en Resolucion 3461 de 2025, Ley 1010 de 2006, Ley 2406 de 2025, Convenio C190 OIT
 *
 * IMPORTANTE: Este documento NO usa IA - es contenido 100% estatico normativo
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class ManualConvivenciaLaboral extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'manual_convivencia_laboral';
    }

    public function getNombre(): string
    {
        return 'Manual de Convivencia Laboral';
    }

    public function getDescripcion(): string
    {
        return 'Manual que establece las normas de comportamiento, conductas aceptables y no aceptables, y mecanismos de resolucion de conflictos en el entorno laboral. Basado en Resolucion 3461 de 2025.';
    }

    public function getEstandar(): ?string
    {
        return '1.1.8';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Introduccion y Proposito', 'key' => 'introduccion_proposito'],
            ['numero' => 2, 'nombre' => 'Fundamentacion Normativa', 'key' => 'fundamentacion_normativa'],
            ['numero' => 3, 'nombre' => 'Objetivo Principal', 'key' => 'objetivo_principal'],
            ['numero' => 4, 'nombre' => 'Objetivos Generales', 'key' => 'objetivos_generales'],
            ['numero' => 5, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 6, 'nombre' => 'Valores Corporativos', 'key' => 'valores_corporativos'],
            ['numero' => 7, 'nombre' => 'Conductas Aceptables', 'key' => 'conductas_aceptables'],
            ['numero' => 8, 'nombre' => 'Conductas NO Aceptables', 'key' => 'conductas_no_aceptables'],
            ['numero' => 9, 'nombre' => 'Comportamientos Prohibidos', 'key' => 'comportamientos_prohibidos'],
            ['numero' => 10, 'nombre' => 'Seguridad Laboral', 'key' => 'seguridad_laboral'],
            ['numero' => 11, 'nombre' => 'Resolucion de Conflictos', 'key' => 'resolucion_conflictos'],
            ['numero' => 12, 'nombre' => 'Procedimiento para Reportar Conductas', 'key' => 'procedimiento_reportes'],
            ['numero' => 13, 'nombre' => 'Sanciones y Procedimiento', 'key' => 'sanciones'],
            ['numero' => 14, 'nombre' => 'Roles y Responsabilidades', 'key' => 'roles_responsabilidades'],
            ['numero' => 15, 'nombre' => 'Difusion del Manual', 'key' => 'difusion_manual'],
            ['numero' => 16, 'nombre' => 'Aceptacion y Compromiso', 'key' => 'aceptacion_compromiso'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        // 2 firmantes: Elaboro (Responsable SST) + Aprobo (Rep Legal)
        // Si requiere_delegado_sst = true, el sistema automaticamente agrega 3er firmante
        return ['responsable_sst', 'representante_legal'];
    }

    /**
     * Contenido estatico del Manual de Convivencia Laboral
     * Basado en Resolucion 3461 de 2025
     */
    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? $cliente['razon_social'] ?? 'LA EMPRESA';
        $correoEmpresa = $cliente['email'] ?? $contexto['email_empresa'] ?? 'convivencia@empresa.com';

        $contenidos = [
            'introduccion_proposito' => $this->getIntroduccionProposito($nombreEmpresa),
            'fundamentacion_normativa' => $this->getFundamentacionNormativa(),
            'objetivo_principal' => $this->getObjetivoPrincipal(),
            'objetivos_generales' => $this->getObjetivosGenerales(),
            'alcance' => $this->getAlcance($nombreEmpresa),
            'valores_corporativos' => $this->getValoresCorporativos(),
            'conductas_aceptables' => $this->getConductasAceptables($nombreEmpresa),
            'conductas_no_aceptables' => $this->getConductasNoAceptables(),
            'comportamientos_prohibidos' => $this->getComportamientosProhibidos(),
            'seguridad_laboral' => $this->getSeguridadLaboral(),
            'resolucion_conflictos' => $this->getResolucionConflictos(),
            'procedimiento_reportes' => $this->getProcedimientoReportes($nombreEmpresa, $correoEmpresa),
            'sanciones' => $this->getSanciones(),
            'roles_responsabilidades' => $this->getRolesResponsabilidades(),
            'difusion_manual' => $this->getDifusionManual(),
            'aceptacion_compromiso' => $this->getAceptacionCompromiso($nombreEmpresa),
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }

    private function getIntroduccionProposito(string $nombreEmpresa): string
    {
        return "El presente Manual de Convivencia Laboral ha sido disenado en cumplimiento de lo establecido en la Resolucion 3461 de 2025 del Ministerio del Trabajo, asi como de las disposiciones contenidas en la Ley 1010 de 2006 sobre acoso laboral, la Ley 2406 de 2025, y los compromisos adquiridos por Colombia en materia de derechos humanos y laborales a traves del Convenio 190 de la Organizacion Internacional del Trabajo (OIT).

Este documento busca convertirse en una herramienta practica y preventiva que oriente a todos los colaboradores, contratistas, aprendices y demas actores vinculados con la entidad, sobre las **conductas aceptables y no aceptables** en el entorno laboral, con el proposito de fortalecer la cultura organizacional, proteger la dignidad humana y garantizar el derecho a un trabajo en condiciones seguras, saludables y respetuosas.

La convivencia en el trabajo no se limita unicamente a evitar conflictos, sino que implica promover relaciones basadas en el respeto mutuo, la tolerancia, la igualdad y la inclusion. En consecuencia, este manual establece las **normas de comportamiento, los mecanismos de dialogo y las rutas de atencion** que permitiran identificar, prevenir, gestionar y resolver de manera oportuna las situaciones que puedan afectar la armonia laboral.

Con este instrumento, **{$nombreEmpresa}** reafirma su compromiso con:

- La prevencion del acoso laboral y de cualquier forma de violencia o discriminacion.
- La promocion de un entorno libre de violencia de genero y de cualquier conducta que atente contra la dignidad de las personas.
- La garantia del derecho al debido proceso, la confidencialidad y la imparcialidad en la atencion de los casos.
- La proteccion integral de la salud mental y el bienestar de todos los trabajadores y trabajadoras.
- El fortalecimiento de los valores organizacionales, generando un clima de confianza, cooperacion y productividad.";
    }

    private function getFundamentacionNormativa(): string
    {
        return "El presente manual se fundamenta en la siguiente normatividad:

- **Resolucion 3461 de 2025:** Establece los lineamientos para la elaboracion del Manual de Convivencia Laboral.
- **Convenio C190 de la OIT:** Derecho a un entorno laboral libre de violencia y acoso.
- **Ley 1010 de 2006:** Define, previene, corrige y sanciona las diversas formas de agresion, maltrato, vejamenes, trato desconsiderado y ofensivo y en general todo ultraje a la dignidad humana que se ejercen sobre quienes realizan sus actividades economicas en el contexto de una relacion laboral privada o publica.
- **Ley 2406 de 2025:** Normas sobre acoso laboral, plazos y trabajo decente.
- **Resolucion 2646 de Julio 17 de 2008:** El Ministerio de la Proteccion Social establece disposiciones y define responsabilidades para la identificacion, evaluacion, intervencion y monitoreo permanente a la exposicion de factores de riesgo psicosocial en el trabajo y la determinacion del origen de las patologias causadas por el estres ocupacional.
- **Decreto 1072 de 2015:** Decreto Unico Reglamentario del Sector Trabajo.
- **Resolucion 0312 de 2019:** Estandares Minimos del Sistema de Gestion de Seguridad y Salud en el Trabajo.";
    }

    private function getObjetivoPrincipal(): string
    {
        return "Promover un ambiente laboral sano y respetuoso, alineado con la politica de prevencion del acoso laboral y la convivencia laboral, identificando las conductas no aceptables en la empresa y creando planes de intervencion idoneos y permanentes.";
    }

    private function getObjetivosGenerales(): string
    {
        return "- Garantizar el respeto mutuo entre todos los miembros de la comunidad empresarial.
- Promover la seguridad y el bienestar fisico y emocional en el trabajo.
- Establecer pautas claras para la comunicacion y la resolucion de conflictos.
- Reforzar los valores corporativos de la empresa.
- Prevenir toda forma de acoso laboral, discriminacion y violencia en el trabajo.
- Establecer mecanismos claros para la denuncia y gestion de conductas no aceptables.";
    }

    private function getAlcance(string $nombreEmpresa): string
    {
        return "Aplicable a todos los trabajadores de la empresa **{$nombreEmpresa}**, contratistas, pasantes, aprendices y proveedores, conforme al ambito general de la Resolucion 3461 de 2025.

Este manual aplica en:
- Todas las instalaciones de la empresa
- Actividades realizadas fuera de las instalaciones cuando se ejecuten labores relacionadas con la empresa
- Eventos empresariales, capacitaciones y reuniones
- Comunicaciones digitales y uso de herramientas tecnologicas corporativas";
    }

    private function getValoresCorporativos(): string
    {
        return "Los comportamientos esperados se basan en estos valores:

- **Respeto:** A todas las personas, independientemente de su cargo, genero, edad, origen o creencias.
- **Honestidad:** En todas las acciones y comunicaciones laborales.
- **Trabajo en equipo:** Colaborar para alcanzar metas comunes.
- **Compromiso:** Con la calidad del servicio, la empresa y los clientes.
- **Seguridad:** Priorizar la proteccion de si mismo y los demas.
- **Tolerancia:** Aceptar las diferencias y promover la inclusion.
- **Responsabilidad:** Cumplir con las obligaciones laborales y normativas.";
    }

    private function getConductasAceptables(string $nombreEmpresa): string
    {
        return "**1. En la comunicacion**
- Hablar con educacion y cordialidad.
- Escuchar activamente a los demas sin interrumpir.
- Evitar lenguaje ofensivo, discriminatorio o irrespetuoso.
- Utilizar los canales de comunicacion corporativos de manera adecuada.

**2. En el ambiente de trabajo**
- Mantener el espacio de trabajo ordenado y limpio.
- Cuidar el equipamiento y los bienes de la empresa.
- Respetar el horario de trabajo y los descansos establecidos.
- Evitar actividades no laborales durante el horario laboral (a menos que se autorice).

**3. En las relaciones interpersonales**
- Tratar a todos con igualdad y justicia.
- Evitar acoso, bullying o cualquier forma de agresion fisica o verbal.
- No difundir rumores o informacion falsa sobre companeros o la empresa.
- Fomentar la colaboracion y el apoyo mutuo.

**4. Uso de tecnologia y herramientas corporativas**
- Usar un lenguaje formal y respetuoso en todos los mensajes de correo electronico.
- No acceder a sitios web inapropiados, ilegales o que representen un riesgo para la red corporativa.
- Usar contrasenas seguras y no compartirlas con nadie.
- Cumplir con las normas de proteccion de datos personales (Ley 1581 de 2012).

**5. Atencion al cliente**
- Atender a los clientes con cordialidad, sonrisa y respeto en todo momento.
- Escuchar activamente sus necesidades y preocupaciones sin interrumpir.
- Ofrecer soluciones realistas y coherentes con los servicios de la empresa.

**6. Atencion a proveedores**
- Tratar a los proveedores con la misma cortesia y respeto que a clientes y companeros.
- Evitar solicitudes de regalos, favores o ventajas personalizadas a proveedores.
- Cumplir con los plazos de pago acordados.

**7. Vestimenta laboral**
- Seguir las normas de vestimenta establecidas segun el area de trabajo.
- Uso obligatorio de Elementos de Proteccion Personal (EPP) en areas de riesgo.

**8. Uso de instalaciones comunes**
- Mantener las areas comunes limpias y ordenadas.
- Respetar los horarios de uso de comedores y salas de descanso.
- Reportar inmediatamente cualquier problema de mantenimiento.

**9. Equilibrio trabajo-vida**
- Cumplir con el horario laboral asignado.
- Tomar los descansos cortos establecidos para descansar y reponerse.
- Cuidar la salud fisica y emocional.";
    }

    private function getConductasNoAceptables(): string
    {
        return "**1. Relacionadas con el respeto y la dignidad:**
- Uso de palabras ofensivas, insultos, burlas o apodos denigrantes.
- Gestos o expresiones de desprecio, sarcasmo o ridiculizacion.
- Descalificar publicamente el trabajo o las capacidades de un companero.
- Difundir rumores, chismes o informacion falsa que afecte la reputacion de alguien.
- Exclusion intencionada de reuniones, grupos de trabajo o espacios sociales.

**2. Conductas de acoso u hostigamiento laboral:**
- Sobrecargar deliberadamente de trabajo a un empleado como forma de presion.
- Asignar funciones degradantes, ajenas a las capacidades o con el fin de humillar.
- Vigilancia excesiva, injustificada o persecucion constante.
- Retener informacion clave para afectar el desempeno.
- Amenazar con despido, sanciones o represalias por ejercer derechos.

**3. Discriminacion e inequidad:**
- Trato diferenciado por razones de sexo, genero, identidad u orientacion sexual.
- Desigualdad de oportunidades por raza, religion, ideologia politica, origen nacional o condicion socioeconomica.
- Discriminacion por embarazo, lactancia o responsabilidades de cuidado.
- Exclusion o trato hostil hacia personas con discapacidad.
- Cualquier acto de segregacion contrario a la inclusion y diversidad.

**4. Violencia y acoso de genero:**
- Comentarios sexistas, insinuaciones o bromas de caracter sexual.
- Contacto fisico no consentido o invasion del espacio personal.
- Mensajes, imagenes o insinuaciones sexuales enviados por cualquier medio.
- Favores sexuales solicitados a cambio de beneficios laborales.
- Menosprecio hacia mujeres u hombres por su genero en reuniones, decisiones o asignacion de roles.

**5. Conductas relacionadas con la comunicacion y trabajo en equipo:**
- Interrumpir constantemente o impedir que otros se expresen.
- No escuchar, ignorar o invalidar de manera intencionada las opiniones de otros.
- Ocultar informacion necesaria para el trabajo conjunto.
- Apropiarse de ideas, logros o proyectos de otros sin dar reconocimiento.

**6. Conductas frente al uso de medios digitales y tecnologicos:**
- Envio de mensajes ofensivos por correo, chats o redes internas.
- Difusion de informacion confidencial o sensible sin autorizacion.
- Uso indebido de redes sociales para atacar o exponer a companeros de trabajo.
- Ciberacoso u hostigamiento virtual.

**7. Conductas que afectan la salud, seguridad y ambiente laboral:**
- Consumo de alcohol o drogas dentro de la jornada o en instalaciones de la empresa.
- Fumar en lugares no autorizados.
- Dano intencional a instalaciones, equipos o herramientas de trabajo.
- No seguir las normas de seguridad, poniendo en riesgo a otros.
- Actitudes negligentes que afecten la integridad de los companeros.

**8. Conductas contra la etica y la transparencia:**
- Solicitar o aceptar sobornos, regalos o favores indebidos.
- Uso de la posicion de poder para obtener beneficios personales.
- Manipulacion de documentos, registros o informacion oficial.
- Ocultamiento intencionado de errores que afecten la organizacion.";
    }

    private function getComportamientosProhibidos(): string
    {
        return "Los siguientes comportamientos estan **estrictamente prohibidos** y pueden dar lugar a sanciones disciplinarias graves, incluyendo el despido inmediato:

1. Acoso (sexual, laboral o moral) y bullying.
2. Discriminacion por cualquier motivo.
3. Consumo, posesion o distribucion de drogas, alcohol o sustancias daninas en instalaciones.
4. Agresion fisica o verbal.
5. Divulgacion de informacion confidencial.
6. Uso inadecuado de tecnologia corporativa.
7. Incumplimiento de normas de seguridad laboral.
8. Robo, hurto o apropiacion indebida de bienes de la empresa o de companeros.
9. Falsificacion de documentos o informacion.
10. Cualquier forma de violencia de genero.";
    }

    private function getSeguridadLaboral(): string
    {
        return "Todos los trabajadores deben:

1. Cumplir todas las instrucciones de seguridad emitidas por la empresa.
2. Usar EPP obligatorio en areas de riesgo.
3. Reportar inmediatamente cualquier incidente, riesgo o dano.
4. Mantener pasillos, salidas de emergencia y areas de trabajo despejados.
5. Participar en las capacitaciones de seguridad programadas.
6. Conocer y seguir los procedimientos de emergencia.
7. No manipular equipos o maquinaria sin la capacitacion adecuada.";
    }

    private function getResolucionConflictos(): string
    {
        return "Para la resolucion de conflictos se seguira el siguiente procedimiento:

1. **Dialogo directo:** Dialogar directamente con la persona involucrada para buscar una solucion amistosa.
2. **Mediacion:** Si no se resuelve, acudir al supervisor inmediato para mediacion.
3. **Denuncia formal:** Si el problema persiste, presentar una denuncia escrita a la direccion o departamento de RRHH.
4. **Comite de Convivencia Laboral:** El caso sera evaluado por el Comite de Convivencia Laboral.
5. **Confidencialidad:** Todos los procesos son tratados con confidencialidad.

El objetivo siempre sera buscar soluciones conciliatorias que permitan restablecer las relaciones laborales.";
    }

    private function getProcedimientoReportes(string $nombreEmpresa, string $correoEmpresa): string
    {
        return "**Canales disponibles:**
La empresa **{$nombreEmpresa}** ha dispuesto el correo **{$correoEmpresa}** para manifestar cualquier comportamiento contrario a este manual.

**Confidencialidad garantizada:**
El trabajador que lidere la revision del correo electronico debera tener acuerdo de confidencialidad firmado.

**Plazos y etapas:**
- **Recepcion de quejas:** 5 dias habiles.
- **Investigacion:** 5 dias habiles, ampliables hasta 15 con justificacion.
- **Dialogo y plan de mejora:** Desde 5 dias despues de escucha de partes.
- **Remision a autoridades o direccion:** Si no hay acuerdo o persistencia de conducta, maximo en 15 dias.

**Seguimiento y cierre:**
- Cumplir compromisos establecidos.
- Seguimiento mensual del caso.
- Informes trimestrales y anuales a la Gerencia sobre casos atendidos.";
    }

    private function getSanciones(): string
    {
        return "**Tipos de Sanciones (segun gravedad del incumplimiento):**

1. **Amonestacion verbal:** Para incumplimientos leves (ej: llegada tarde sin justificacion, desorden en espacio de trabajo).

2. **Amonestacion escrita:** Para incumplimientos repetidos o de mayor gravedad (ej: uso inadecuado de tecnologia, falta de respeto verbal leve).

3. **Suspension temporal sin sueldo:** Para incumplimientos graves (ej: agresion verbal, incumplimiento de normas de seguridad que ponga en riesgo a otros). Duracion: 1 a 5 dias habiles segun caso.

4. **Despido inmediato:** Para incumplimientos muy graves (ej: acoso, agresion fisica, robo, divulgacion de informacion confidencial, consumo de drogas en instalaciones).

**Procedimiento para aplicar sanciones:**

**PASO 1:** Deteccion y registro del incumplimiento.
**PASO 2:** Investigacion inicial (maximo 24 horas habiles).
**PASO 3:** Notificacion al involucrado (48 horas habiles para entrevista).
**PASO 4:** Entrevista y garantia de defensa.
**PASO 5:** Toma de decision (maximo 3 dias habiles).
**PASO 6:** Aplicacion de la sancion y derecho a recurrir (5 dias habiles).
**PASO 7:** Registro del expediente confidencial.

**Importante:** Todo el proceso se desarrolla con respeto, transparencia y garantia de defensa para el involucrado.";
    }

    private function getRolesResponsabilidades(): string
    {
        return "**Empleador:**
- Formular la politica de convivencia laboral.
- Socializar el presente manual.
- Proporcionar recursos (espacio, tiempo, apoyo tecnico).
- Garantizar la confidencialidad y el debido proceso.

**Comite de Convivencia Laboral (CCL):**
- Receptar las quejas y denuncias.
- Analizar los casos presentados.
- Conciliar entre las partes.
- Recomendar medidas correctivas.
- Hacer seguimiento a los compromisos.
- Remitir casos a instancias superiores cuando corresponda.

**ARL:**
- Capacitar al comite y brigadas.
- Promover la salud mental en el trabajo.
- Reportar al Ministerio del Trabajo.
- Asesorar en prevencion de riesgos psicosociales.

**Colaboradores:**
- Conocer y cumplir el presente manual.
- Participar en capacitaciones sobre convivencia laboral.
- Denunciar conductas contrarias al manual.
- Colaborar en las investigaciones cuando sea requerido.
- Mantener la confidencialidad de los casos conocidos.";
    }

    private function getDifusionManual(): string
    {
        return "El presente Manual de Convivencia Laboral sera:

- Socializado ante todos los trabajadores en induccion y reinduccion.
- Publicado en espacios de facil acceso a los trabajadores tanto fisico como de forma digital.
- Incluido en el proceso de contratacion de nuevos empleados.
- Actualizado anualmente o cuando la normatividad lo requiera.
- Comunicado a contratistas, proveedores y visitantes frecuentes.";
    }

    private function getAceptacionCompromiso(string $nombreEmpresa): string
    {
        return "Todos los colaboradores deben leer, entender y firmar este manual al ingresar a **{$nombreEmpresa}**. Las modificaciones al manual se comunicaran con anticipacion.

**\"El cumplimiento de estas normas es responsabilidad de todos, y contribuye a construir una empresa mejor para todos.\"**

Con la firma de este documento, el trabajador manifiesta:
- Haber recibido copia del Manual de Convivencia Laboral.
- Haber leido y comprendido su contenido.
- Comprometerse a cumplir las normas aqui establecidas.
- Conocer las consecuencias del incumplimiento de las mismas.";
    }

    /**
     * Este documento es plantilla fija, no requiere generacion IA
     */
    public function requiereGeneracionIA(): bool
    {
        return false;
    }
}
