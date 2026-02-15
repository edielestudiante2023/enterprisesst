<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase PoliticaDesconexionLaboral
 *
 * Implementa la generacion de la Politica de Desconexion Laboral
 * Numeral 2.1.1 de la Resolucion 0312/2019
 * Basada en la Ley 2191 de 2022 - Derecho a la Desconexion Laboral
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class PoliticaDesconexionLaboral extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'politica_desconexion_laboral';
    }

    public function getNombre(): string
    {
        return 'Politica de Desconexion Laboral';
    }

    public function getDescripcion(): string
    {
        return 'Politica que establece el compromiso de la empresa con el derecho a la desconexion laboral, garantizando el equilibrio entre vida laboral y personal conforme a la Ley 2191 de 2022';
    }

    public function getEstandar(): ?string
    {
        return '2.1.1';
    }

    public function getCodigoDocumento(): string
    {
        return 'POL-DES';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Declaracion de la Politica', 'key' => 'declaracion'],
            ['numero' => 4, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 5, 'nombre' => 'Horarios de Conexion y Desconexion', 'key' => 'horarios'],
            ['numero' => 6, 'nombre' => 'Buenas Practicas', 'key' => 'buenas_practicas'],
            ['numero' => 7, 'nombre' => 'Derechos del Trabajador', 'key' => 'derechos'],
            ['numero' => 8, 'nombre' => 'Excepciones', 'key' => 'excepciones'],
            ['numero' => 9, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
            ['numero' => 10, 'nombre' => 'Comunicacion y Divulgacion', 'key' => 'comunicacion'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['consultor_sst', 'representante_legal'];
    }

    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        $prompts = [
            'objetivo' => "Genera el objetivo de la Politica de Desconexion Laboral.
Debe expresar el compromiso de la organizacion con:
- Garantizar el derecho a la desconexion laboral (Ley 2191 de 2022)
- Promover el equilibrio entre vida laboral y personal
- Proteger la salud mental y prevenir el burnout
- Respetar el tiempo libre, descanso y vida familiar
IMPORTANTE: Maximo 2-3 parrafos concisos.",

            'alcance' => "Define el alcance de la Politica de Desconexion Laboral.
Debe especificar que aplica a:
- Todos los trabajadores independientemente de su modalidad (presencial, teletrabajo, remoto, hibrido)
- Todos los cargos y niveles jerarquicos
- Todas las formas de comunicacion (correo, WhatsApp, Teams, llamadas, etc.)
- Jornada laboral, dias de descanso, festivos y vacaciones
Para empresas de {$estandares} estandares, ajusta el alcance proporcionalmente.",

            'declaracion' => "Genera la declaracion formal de la Politica de Desconexion Laboral.
Debe incluir el compromiso explicito de la alta direccion con:
- Respetar el derecho a la desconexion fuera del horario laboral
- No exigir disponibilidad permanente
- Promover el descanso efectivo
- Proteger la salud mental y vida familiar
- Cumplir con la Ley 2191 de 2022
FORMATO: Redactar en primera persona plural.
TONO: Formal, comprometido, empatico.",

            'definiciones' => "Proporciona definiciones claras de los terminos clave.
INCLUIR:
- Desconexion laboral: Derecho a no responder comunicaciones fuera de jornada
- Jornada laboral: Horario contractual establecido
- Herramientas digitales: Medios de comunicacion laboral (correo, WhatsApp, Teams, etc.)
- Excepciones: Situaciones que justifican contacto fuera de jornada
- Teletrabajo/Trabajo remoto: Modalidades de trabajo a distancia
- Derecho al descanso: Tiempo libre sin obligaciones laborales
FORMATO: Lista con definiciones claras y concisas.",

            'horarios' => "Define los horarios de conexion y desconexion laboral.
DEBE INCLUIR:
- Jornada laboral estandar de la empresa (ej: lunes a viernes 8:00 a.m. - 5:00 p.m.)
- Franjas de no contacto (despues de jornada, fines de semana, festivos)
- Excepciones autorizadas (guardias, emergencias)
- Acuerdos especiales para cargos especificos
IMPORTANTE: Mencionar que los horarios especificos se establecen en el contrato de trabajo.
Para {$estandares} estandares, ajusta la complejidad.",

            'buenas_practicas' => "Lista las buenas practicas para respetar la desconexion laboral.
PRACTICAS:
1. No enviar correos o mensajes fuera de horario laboral
2. Usar funcion de programacion de envio en correo electronico
3. Evitar llamadas fuera de jornada salvo emergencia
4. Respetar dias de descanso, festivos y vacaciones
5. No generar expectativas de respuesta inmediata
6. Promover reuniones dentro del horario laboral
7. Planificar actividades con anticipacion
8. Uso responsable de grupos de WhatsApp laborales
Para {$estandares} estandares, ajusta la complejidad del programa.",

            'derechos' => "Describe los derechos del trabajador en materia de desconexion laboral.
DERECHOS:
1. Derecho a no responder comunicaciones fuera de jornada
2. Derecho a desactivar notificaciones laborales
3. Derecho a no ser sancionado por ejercer la desconexion
4. Derecho al descanso efectivo
5. Derecho a conciliar vida laboral y personal
6. Proteccion contra represalias
7. Derecho a reportar incumplimientos sin temor
IMPORTANTE: Basarse en la Ley 2191 de 2022. Mencionar que el ejercicio de este derecho no puede ser motivo de sancion disciplinaria.",

            'excepciones' => "Define las excepciones al derecho de desconexion laboral.
EXCEPCIONES VALIDAS:
1. Emergencias operativas o de seguridad (deben compensarse)
2. Actividades de guardia previamente pactadas y remuneradas
3. Situaciones de fuerza mayor
4. Casos excepcionales de responsabilidad jerarquica (con acuerdo previo)
IMPORTANTE:
- Las excepciones deben ser justificadas y documentadas
- Requieren compensacion (descanso compensatorio o pago extra)
- No pueden ser la norma habitual
- Deben pactarse previamente en el contrato o mediante acuerdo escrito",

            'marco_legal' => "Lista el marco normativo aplicable a la Desconexion Laboral.
NORMATIVA ESENCIAL:
- Ley 2191 de 2022: Regula el derecho a la desconexion laboral
- Codigo Sustantivo del Trabajo: Jornadas laborales y descansos
- Decreto 1072 de 2015: Obligaciones del empleador en SST
- Resolucion 0312 de 2019: Estandares Minimos del SG-SST
- Ley 1221 de 2008: Teletrabajo
- Ley 2088 de 2021: Trabajo en casa
- Constitucion Politica de Colombia: Art. 53 (Principios minimos fundamentales del trabajo)
FORMATO: Lista con vinetas, NO usar tablas.",

            'comunicacion' => "Define como se comunicara y divulgara la politica.
INCLUIR:
- Comunicacion al COPASST/Vigia SST (segun estandares del cliente)
- Publicacion en canales internos (intranet, carteleras, correo)
- Inclusion en induccion y reinduccion
- Capacitacion periodica sobre el derecho a la desconexion
- Difusion de canales para reportar incumplimientos
- Revision anual o cuando sea necesario
- Socializacion con lideres y gerentes
IMPORTANTE: Para {$estandares} estandares, usar 'Vigia SST' si son 7 estandares, 'COPASST' si son 21 o mas."
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' de la Politica de Desconexion Laboral.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'LA EMPRESA';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "La presente politica tiene como objetivo garantizar el derecho a la desconexion laboral de todos los trabajadores de {$nombreEmpresa}, conforme a lo establecido en la Ley 2191 de 2022, promoviendo el equilibrio entre la vida laboral y personal, protegiendo la salud mental y previniendo riesgos psicosociales asociados a la sobrecarga laboral.\n\nEsta politica busca establecer limites claros entre el tiempo de trabajo y el tiempo de descanso, respetando el derecho de los trabajadores a desconectarse de las herramientas digitales y comunicaciones laborales fuera del horario establecido, fomentando una cultura de respeto por el tiempo libre y la vida familiar.",

            'alcance' => "La Politica de Desconexion Laboral de {$nombreEmpresa} aplica a:\n\n- Todos los trabajadores, independientemente de su modalidad de trabajo (presencial, teletrabajo, trabajo remoto o hibrido)\n- Todos los cargos y niveles jerarquicos\n- Todas las formas de comunicacion laboral (correo electronico, WhatsApp, Microsoft Teams, llamadas telefonicas, mensajes de texto, etc.)\n- Jornada laboral, tiempo de descanso, dias no laborables, festivos y vacaciones\n- Contratistas y proveedores que interactuen con los trabajadores\n\nAplica en todas las instalaciones de la empresa y en cualquier lugar donde se ejecuten actividades laborales.",

            'declaracion' => "{$nombreEmpresa} se compromete a respetar y garantizar el derecho a la desconexion laboral de todos sus trabajadores, reconociendo que el descanso efectivo es esencial para la salud, el bienestar y la productividad.\n\n**Nos comprometemos a:**\n\n- Respetar el derecho de los trabajadores a no responder comunicaciones fuera del horario laboral.\n- No exigir disponibilidad permanente ni generar expectativas de respuesta inmediata fuera de jornada.\n- Promover el uso responsable de herramientas digitales laborales.\n- Proteger la salud mental y prevenir riesgos psicosociales.\n- Fomentar el equilibrio entre vida laboral, personal y familiar.\n- Cumplir con lo establecido en la Ley 2191 de 2022 y demas normatividad vigente.\n\nEsta politica se aplica sin discriminacion y respetando la dignidad de todos los trabajadores.",

            'definiciones' => "Para efectos de esta politica, se establecen las siguientes definiciones:\n\n**Desconexion Laboral:** Derecho del trabajador a no responder ni atender comunicaciones, mensajes, llamadas o requerimientos relacionados con el trabajo fuera del horario laboral establecido.\n\n**Jornada Laboral:** Horario de trabajo contractualmente establecido, durante el cual el trabajador esta a disposicion del empleador.\n\n**Herramientas Digitales:** Medios tecnologicos utilizados para comunicacion laboral, incluyendo correo electronico, WhatsApp, Microsoft Teams, llamadas telefonicas, SMS, aplicaciones moviles, entre otros.\n\n**Excepciones:** Situaciones extraordinarias que justifican el contacto fuera de jornada, tales como emergencias operativas, guardias pactadas o fuerza mayor.\n\n**Teletrabajo/Trabajo Remoto:** Modalidades de prestacion del servicio desde un lugar diferente a las instalaciones del empleador, utilizando tecnologias de la informacion.\n\n**Derecho al Descanso:** Tiempo libre del trabajador sin obligaciones laborales, destinado al descanso, esparcimiento y vida personal/familiar.",

            'horarios' => "**Jornada Laboral Estandar:**\n\nLa jornada laboral de {$nombreEmpresa} se establece conforme a lo pactado en el contrato de trabajo individual de cada trabajador, cumpliendo con los limites legales establecidos en el Codigo Sustantivo del Trabajo.\n\n**Horario general de referencia:** Lunes a viernes de 8:00 a.m. a 5:00 p.m. (puede variar segun contrato)\n\n**Franjas de No Contacto:**\n\n- Fuera del horario laboral establecido (noches)\n- Fines de semana\n- Dias festivos\n- Periodos de vacaciones\n- Licencias e incapacidades\n\n**Excepciones Autorizadas:**\n\nUnicamente en casos de:\n- Emergencias operativas o de seguridad\n- Guardias previamente pactadas y remuneradas\n- Situaciones de fuerza mayor\n\n_Los trabajadores con jornadas especiales o guardias tendran sus horarios claramente definidos en su contrato de trabajo._",

            'buenas_practicas' => "Para garantizar el derecho a la desconexion laboral, {$nombreEmpresa} promueve las siguientes buenas practicas:\n\n**Para todos los trabajadores:**\n\n1. **No enviar** correos electronicos o mensajes fuera del horario laboral, salvo casos excepcionales.\n\n2. **Usar la funcion de programacion de envio** en correos electronicos para que se entreguen dentro del horario laboral.\n\n3. **Evitar llamadas** fuera de jornada, excepto en emergencias justificadas.\n\n4. **Respetar** dias de descanso, festivos y vacaciones de los companeros.\n\n5. **No generar expectativas** de respuesta inmediata fuera del horario laboral.\n\n6. **Planificar reuniones** dentro del horario laboral.\n\n7. **Uso responsable** de grupos de WhatsApp laborales (evitar mensajes fuera de horario).\n\n8. **Desactivar notificaciones** laborales fuera de jornada.\n\n_El liderazgo de la empresa debe dar ejemplo en el cumplimiento de estas buenas practicas._",

            'derechos' => "Los trabajadores de {$nombreEmpresa} tienen los siguientes derechos en materia de desconexion laboral:\n\n1. **Derecho a no responder** comunicaciones, correos, mensajes o llamadas laborales fuera del horario de trabajo.\n\n2. **Derecho a desactivar** notificaciones de herramientas digitales laborales fuera de jornada.\n\n3. **Derecho a no ser sancionado** por ejercer la desconexion laboral.\n\n4. **Derecho al descanso efectivo** durante fines de semana, festivos y vacaciones.\n\n5. **Derecho a conciliar** vida laboral, personal y familiar.\n\n6. **Proteccion contra represalias** por ejercer este derecho.\n\n7. **Derecho a reportar incumplimientos** sin temor a sanciones.\n\n_El ejercicio del derecho a la desconexion laboral NO puede ser motivo de sancion disciplinaria ni afectar la evaluacion de desempeno del trabajador._",

            'excepciones' => "El derecho a la desconexion laboral admite las siguientes excepciones, las cuales deben ser justificadas, documentadas y compensadas:\n\n**1. Emergencias Operativas o de Seguridad:**\n- Situaciones imprevistas que amenacen la continuidad operativa o la seguridad de personas, instalaciones o informacion.\n- Deben compensarse con descanso compensatorio o pago de horas extras.\n\n**2. Actividades de Guardia:**\n- Guardias previamente pactadas en el contrato de trabajo.\n- Deben ser remuneradas conforme a la ley.\n- Rotativas y limitadas en frecuencia.\n\n**3. Fuerza Mayor:**\n- Situaciones extraordinarias, imprevisibles e irresistibles.\n- Catastrofes naturales, emergencias sanitarias, etc.\n\n**4. Responsabilidades Jerarquicas Excepcionales:**\n- Casos especificos de cargos directivos, con acuerdo previo por escrito.\n- Compensacion adecuada.\n\n_Las excepciones NO pueden convertirse en la norma habitual y deben estar debidamente justificadas._",

            'marco_legal' => "La presente politica se fundamenta en la siguiente normatividad:\n\n- **Ley 2191 de 2022:** Derecho a la desconexion laboral en Colombia.\n\n- **Codigo Sustantivo del Trabajo:** Jornadas laborales, descansos obligatorios y limitaciones a la jornada de trabajo.\n\n- **Decreto 1072 de 2015:** Obligaciones del empleador en materia de Seguridad y Salud en el Trabajo.\n\n- **Resolucion 0312 de 2019:** Estandares Minimos del Sistema de Gestion de SST.\n\n- **Ley 1221 de 2008:** Teletrabajo en Colombia.\n\n- **Ley 2088 de 2021:** Regulacion del trabajo en casa.\n\n- **Constitucion Politica de Colombia:** Articulo 53 sobre principios minimos fundamentales del trabajo.\n\n- **Resolucion 2646 de 2008:** Factores de riesgo psicosocial en el trabajo.",

            'comunicacion' => "La Politica de Desconexion Laboral sera:\n\n1. **Comunicada al {$comite}** para su conocimiento y apoyo en la difusion.\n\n2. **Publicada** en lugares visibles, intranet y canales internos de comunicacion.\n\n3. **Incluida** en el proceso de induccion y reinduccion de todos los trabajadores.\n\n4. **Socializada** con lideres, gerentes y supervisores, quienes deben dar ejemplo.\n\n5. **Capacitacion periodica** sobre el derecho a la desconexion laboral (al menos una vez al ano).\n\n6. **Canales de reporte:** Los trabajadores pueden reportar incumplimientos al area de talento humano, {$comite} o directamente a la alta direccion.\n\n7. **Revision anual** y actualizacion cuando sea necesario.\n\n_Todos los trabajadores tienen el deber de conocer esta politica y el derecho de exigir su cumplimiento._"
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
