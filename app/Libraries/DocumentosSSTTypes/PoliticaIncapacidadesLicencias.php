<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase PoliticaIncapacidadesLicencias
 *
 * Implementa la generacion de la Politica de Gestion de Incapacidades y Licencias
 * Numeral 2.1.1 de la Resolucion 0312/2019
 * Actualizada con Ley 2466 de 2025 (Reforma Laboral para el Trabajo Decente y Digno)
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class PoliticaIncapacidadesLicencias extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'politica_incapacidades_licencias';
    }

    public function getNombre(): string
    {
        return 'Politica de Gestion de Incapacidades y Licencias';
    }

    public function getDescripcion(): string
    {
        return 'Politica que establece los lineamientos para la gestion responsable de incapacidades y licencias, garantizando los derechos de los trabajadores conforme a la Ley 2466 de 2025 y la normativa complementaria';
    }

    public function getEstandar(): ?string
    {
        return '2.1.1';
    }

    public function getCodigoDocumento(): string
    {
        return 'POL-INC';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1,  'nombre' => 'Objetivo',                          'key' => 'objetivo'],
            ['numero' => 2,  'nombre' => 'Alcance',                           'key' => 'alcance'],
            ['numero' => 3,  'nombre' => 'Declaracion de la Politica',        'key' => 'declaracion'],
            ['numero' => 4,  'nombre' => 'Definiciones',                      'key' => 'definiciones'],
            ['numero' => 5,  'nombre' => 'Tipos de Licencias y Permisos',     'key' => 'tipos_licencias'],
            ['numero' => 6,  'nombre' => 'Manejo de Incapacidades',           'key' => 'manejo_incapacidades'],
            ['numero' => 7,  'nombre' => 'Derechos y Obligaciones',           'key' => 'derechos_obligaciones'],
            ['numero' => 8,  'nombre' => 'Procedimiento de Reporte y Gestion','key' => 'procedimiento'],
            ['numero' => 9,  'nombre' => 'Marco Legal',                       'key' => 'marco_legal'],
            ['numero' => 10, 'nombre' => 'Comunicacion y Divulgacion',        'key' => 'comunicacion'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['consultor_sst', 'representante_legal'];
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'LA EMPRESA';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "La presente politica tiene como objetivo establecer los lineamientos de {$nombreEmpresa} para la gestion responsable y humana de las incapacidades y licencias de sus trabajadores, garantizando el pleno respeto de sus derechos conforme a la Ley 2466 de 2025 (Reforma Laboral para el Trabajo Decente y Digno) y la normativa de seguridad social vigente.\n\nBuscamos asegurar el pago oportuno de las incapacidades, conceder todas las licencias a las que el trabajador tiene derecho por ley, proteger su estabilidad laboral durante los periodos de ausencia por razones de salud, y facilitar el reintegro seguro y digno al trabajo.\n\nEsta politica reafirma nuestro compromiso con la salud integral de nuestros trabajadores y con la construccion de un entorno laboral justo, inclusivo y respetuoso de la dignidad humana.",

            'alcance' => "La Politica de Gestion de Incapacidades y Licencias de {$nombreEmpresa} aplica a:\n\n- **Todos los trabajadores** vinculados mediante contrato de trabajo, independientemente de su modalidad (termino fijo, indefinido, obra o labor) o forma de prestacion del servicio (presencial, remoto, hibrido)\n- **Todos los tipos de incapacidad:** enfermedad general (EPS), accidente de trabajo y enfermedad laboral (ARL)\n- **Todas las licencias remuneradas:** maternidad, paternidad, luto, calamidad domestica, citas medicas, obligaciones escolares y dia de bicicleta (Ley 2466/2025)\n- **Personal de RR.HH. y jefes inmediatos** responsables de gestionar las ausencias\n\nAplica en todas las instalaciones y modalidades de trabajo de la empresa.",

            'declaracion' => "{$nombreEmpresa} se compromete formalmente a gestionar las incapacidades y licencias de sus trabajadores con transparencia, oportunidad y respeto absoluto por sus derechos.\n\n**Nos comprometemos a:**\n\n- **Pagar oportunamente** las incapacidades en los terminos y porcentajes establecidos por la ley.\n- **Conceder todas las licencias remuneradas** a que tienen derecho los trabajadores, incluyendo las nuevas licencias introducidas por la Ley 2466 de 2025 (citas medicas, obligaciones escolares y dia de bicicleta).\n- **Prohibir** cualquier practica de despido, presion para renunciar o discriminacion motivada por el estado de salud, incapacidad o afectacion de salud mental del trabajador, en cumplimiento del Articulo 17 de la Ley 2466 de 2025.\n- **Garantizar la confidencialidad** de los diagnosticos medicos de nuestros trabajadores.\n- **Facilitar el reintegro** laboral digno y seguro tras cualquier periodo de incapacidad.\n- **Mantener los contratos de trabajo** vigentes durante la incapacidad temporal.\n\nEsta politica es de obligatorio cumplimiento para toda la organizacion.",

            'definiciones' => "Para efectos de esta politica, se establecen las siguientes definiciones:\n\n**Incapacidad temporal:** Imposibilidad fisica o mental del trabajador para desempenar sus labores habituales por un periodo determinado, certificada por un medico.\n\n**Incapacidad de origen comun:** Generada por enfermedad o accidente que no tiene relacion con las actividades laborales. Su reconocimiento corresponde al Sistema de Salud (EPS).\n\n**Incapacidad de origen laboral:** Generada por accidente de trabajo o enfermedad profesional directamente relacionada con las actividades laborales. Su reconocimiento corresponde a la ARL.\n\n**Licencia remunerada:** Permiso de ausencia del trabajo con pago completo del salario, establecido por ley o convenio.\n\n**Licencia no remunerada:** Permiso de ausencia acordado entre empleador y trabajador sin pago de salario durante el periodo.\n\n**Reintegro laboral:** Proceso mediante el cual el trabajador retorna a sus funciones habituales tras un periodo de incapacidad o licencia, con el alta medica correspondiente.\n\n**Ajuste razonable:** Modificacion o adaptacion del puesto de trabajo, funciones o condiciones laborales para facilitar el reintegro de trabajadores con condiciones de salud especiales, sin que implique una carga desproporcionada para la empresa.",

            'tipos_licencias' => "**Licencias reconocidas por {$nombreEmpresa}:**\n\n| Tipo de Licencia | Duracion | Quien Paga | Requisito |\n|---|---|---|---|\n| **Maternidad** | 18 semanas | EPS (si cotizo min. 1 semana) | Certificado medico preparto |\n| **Paternidad** | 2 semanas | EPS | Registro civil de nacimiento |\n| **Luto** | 5 dias habiles | Empleador | Certificado de defuncion |\n| **Calamidad domestica** | Hasta 5 dias | Empleador | Soporte de la situacion |\n| **Cita medica** *(Ley 2466/2025)* | Tiempo necesario | Empleador | Certificado medico o cita programada |\n| **Obligacion escolar** *(Ley 2466/2025)* | Tiempo necesario | Empleador | Citacion del centro educativo |\n| **Dia de bicicleta** *(Ley 2466/2025)* | 1 dia/semestre | Empleador | Certificacion uso bicicleta transporte |\n| **Citacion judicial/admin.** | Tiempo necesario | Empleador | Comprobante de citacion |\n\n_Las licencias de maternidad y paternidad requieren que el trabajador este cotizando activamente al sistema de seguridad social._",

            'manejo_incapacidades' => "**1. INCAPACIDAD POR ENFERMEDAD GENERAL (EPS)**\n\n- **Dias 1 al 3:** El empleador paga el **66.67%** del salario base de cotizacion del trabajador.\n- **Dias 4 al 180:** La **EPS** reconoce el **66.67%** del ingreso base de cotizacion.\n- **Dias 181 al 540:** El Fondo de Pension (Colpensiones o AFP) puede reconocer la incapacidad prolongada.\n- **Requisito:** Certificado medico oficial de la EPS o medico tratante.\n\n**2. INCAPACIDAD DE ORIGEN LABORAL (ARL)**\n\n- **Desde el dia 1:** La **ARL** reconoce el **100%** del ingreso base de cotizacion.\n- El empleador debe **reportar el accidente de trabajo a la ARL dentro de los 2 dias habiles** siguientes al evento.\n- Requiere apertura de caso ante la ARL y seguimiento medico especializado.\n\n**3. PROHIBICIONES APLICABLES**\n\n- Queda **prohibido** descontar dias de incapacidad del tiempo de vacaciones.\n- Queda **prohibido** exigir reintegro antes de obtener el alta medica.\n- Queda **prohibido** despedir o presionar la renuncia de un trabajador por su estado de salud o afectacion de salud mental, conforme al Articulo 17 de la Ley 2466 de 2025.",

            'derechos_obligaciones' => "**DERECHOS DEL TRABAJADOR**\n\n1. Recibir el pago oportuno de la incapacidad segun su origen (empleador: dias 1-3 enfermedad general; EPS: dias 4-180; ARL: 100% desde dia 1)\n2. No ser despedido ni presionado a renunciar por causa de enfermedad o afectacion de la salud mental (**Art. 17, Ley 2466/2025**)\n3. Recibir todas las licencias remuneradas establecidas por la ley (maternidad, paternidad, luto, citas medicas, obligaciones escolares, dia de bicicleta)\n4. Reintegrarse a su cargo habitual o a uno de igual o mejor nivel tras el alta medica\n5. Confidencialidad absoluta de su diagnostico medico\n6. Solicitar ajuste razonable del puesto para un reintegro progresivo cuando sea necesario\n7. Proteccion reforzada si tiene condicion de discapacidad (Ley 361/1997 — requiere autorizacion del Ministerio del Trabajo para terminar el contrato)\n\n**OBLIGACIONES DEL EMPLEADOR**\n\n1. Pagar los primeros 3 dias de incapacidad por enfermedad general al 66.67%\n2. Reportar accidentes de trabajo a la ARL dentro de los 2 dias habiles siguientes\n3. Conceder todas las licencias remuneradas establecidas en la ley sin obstaculizarlas\n4. Gestionar los tramites ante EPS y ARL para el reconocimiento oportuno de incapacidades\n5. Mantener el contrato de trabajo durante la incapacidad temporal\n6. No discriminar al trabajador por su estado de salud, incapacidad o licencia\n\n**OBLIGACIONES DEL TRABAJADOR**\n\n1. Informar la incapacidad o necesidad de licencia tan pronto como sea posible al jefe inmediato y a RR.HH.\n2. Presentar el certificado medico o soporte correspondiente dentro de los 2 dias habiles siguientes\n3. Abstenerse de realizar actividades laborales durante el periodo de incapacidad\n4. Seguir el tratamiento medico prescrito y asistir a los controles programados\n5. Presentar el certificado de alta medica al momento del reintegro",

            'procedimiento' => "**Procedimiento de Reporte y Gestion de Incapacidades y Licencias:**\n\n**Paso 1 — Aviso inmediato:**\nEl trabajador informa a su jefe inmediato y al area de RR.HH. el mismo dia de la incapacidad o solicitud de licencia, o al dia habil siguiente en caso de urgencia.\n\n**Paso 2 — Entrega de soporte:**\nEl trabajador presenta el certificado medico o soporte de licencia al area de RR.HH. dentro de los **2 dias habiles** siguientes al inicio de la ausencia.\n\n**Paso 3 — Registro de novedad:**\nRR.HH. registra la novedad en el sistema de nomina, clasificando la incapacidad segun su origen (EPS o ARL) o tipo de licencia.\n\n**Paso 4 — Gestion segun origen:**\n- **EPS:** El empleador asume los dias 1-3. A partir del dia 4, gestiona el reconocimiento ante la EPS.\n- **ARL:** Reportar el accidente de trabajo a la ARL dentro de las **48 horas** siguientes.\n- **Licencia legal:** Verificar requisitos y emitir la licencia correspondiente.\n\n**Paso 5 — Comunicacion interna:**\nRR.HH. informa al area del trabajador sobre la ausencia y la duracion estimada, garantizando la confidencialidad del diagnostico.\n\n**Paso 6 — Reintegro:**\nAl finalizar la incapacidad o licencia, el trabajador presenta el certificado de alta medica. RR.HH. coordina el reintegro laboral, que puede ser progresivo si el medico lo recomienda.\n\n**Paso 7 — Seguimiento:**\nRR.HH. realiza seguimiento a los casos de incapacidades prolongadas (mas de 30 dias) para apoyar el reintegro y activar el programa de rehabilitacion si aplica.",

            'marco_legal' => "La presente politica se fundamenta en la siguiente normatividad:\n\n- **Constitucion Politica de Colombia, Art. 49:** Derecho a la seguridad social y a la atencion en salud.\n\n- **Codigo Sustantivo del Trabajo, Arts. 227-228:** Auxilio monetario por enfermedad no profesional.\n\n- **Codigo Sustantivo del Trabajo, Arts. 236-238:** Licencia de maternidad y proteccion a la maternidad.\n\n- **Ley 100 de 1993:** Sistema General de Seguridad Social en Salud.\n\n- **Decreto 1295 de 1994:** Sistema General de Riesgos Laborales — incapacidades de origen laboral.\n\n- **Ley 361 de 1997:** Mecanismos de integracion social de personas con discapacidad — estabilidad laboral reforzada.\n\n- **Ley 1280 de 2009:** Licencia remunerada de luto (5 dias habiles).\n\n- **Ley 1780 de 2016:** Licencia de paternidad (2 semanas).\n\n- **Decreto 1072 de 2015:** Decreto Unico Reglamentario del Sector Trabajo.\n\n- **Resolucion 0312 de 2019:** Estandares Minimos del Sistema de Gestion de SST, numeral 2.1.1.\n\n- **Ley 2338 de 2023:** Endometriosis — derecho a licencia medica especial.\n\n- **Ley 2466 de 2025 (Reforma Laboral):** Art. 15 — nuevas licencias remuneradas (citas medicas, obligaciones escolares, dia de bicicleta); Art. 17 — prohibicion de despido por enfermedad y proteccion de la salud mental; Art. 45 — contratos temporales de reemplazo.",

            'comunicacion' => "La Politica de Gestion de Incapacidades y Licencias de {$nombreEmpresa} sera:\n\n1. **Comunicada al {$comite}** para su conocimiento, apoyo en difusion y seguimiento.\n\n2. **Publicada** en lugares visibles de la empresa (carteleras, intranet, canales de comunicacion interna).\n\n3. **Incluida** en el proceso de induccion y reinduccion de todos los trabajadores.\n\n4. **Socializada** con jefes inmediatos y personal de nomina/RR.HH. que tienen rol en la gestion de incapacidades y licencias.\n\n5. **Capacitacion anual** sobre derechos, procedimientos y cambios normativos en materia de incapacidades y licencias.\n\n6. **Canal de consultas:** Los trabajadores pueden dirigir sus dudas al area de talento humano o a RR.HH.\n\n7. **Revision y actualizacion:** Esta politica se revisara anualmente o cuando se produzcan cambios normativos que la afecten.\n\n_Todos los trabajadores tienen el deber de conocer esta politica y el derecho de exigir su cumplimiento._"
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
