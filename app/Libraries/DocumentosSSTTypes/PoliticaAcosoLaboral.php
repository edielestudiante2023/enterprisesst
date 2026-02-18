<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase PoliticaAcosoLaboral
 *
 * Implementa la generacion de la Politica de Prevencion del Acoso Laboral
 * Numeral 2.1.1 de la Resolucion 0312/2019 - Ley 1010 de 2006
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class PoliticaAcosoLaboral extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'politica_acoso_laboral';
    }

    public function getNombre(): string
    {
        return 'Politica de Prevencion del Acoso Laboral';
    }

    public function getDescripcion(): string
    {
        return 'Politica que establece el compromiso de la empresa con la prevencion y sancion del acoso laboral conforme a la Ley 1010 de 2006';
    }

    public function getEstandar(): ?string
    {
        return '2.1.1';
    }

    public function getCodigoDocumento(): string
    {
        return 'POL-ACO';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Declaracion de la Politica', 'key' => 'declaracion'],
            ['numero' => 4, 'nombre' => 'Definiciones y Modalidades de Acoso', 'key' => 'definiciones'],
            ['numero' => 5, 'nombre' => 'Conductas Constitutivas de Acoso Laboral', 'key' => 'conductas'],
            ['numero' => 6, 'nombre' => 'Conductas NO Constitutivas de Acoso Laboral', 'key' => 'conductas_no_acoso'],
            ['numero' => 7, 'nombre' => 'Mecanismos de Prevencion', 'key' => 'mecanismos_prevencion'],
            ['numero' => 8, 'nombre' => 'Procedimiento de Denuncia', 'key' => 'procedimiento_denuncia'],
            ['numero' => 9, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
            ['numero' => 10, 'nombre' => 'Comunicacion y Divulgacion', 'key' => 'comunicacion'],
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
            'objetivo' => "La presente politica tiene como objetivo establecer el compromiso de {$nombreEmpresa} con la prevencion, correccion y sancion del acoso laboral en cualquiera de sus modalidades, conforme a lo establecido en la Ley 1010 de 2006.\n\nEsta politica busca proteger la dignidad de los trabajadores, promover un ambiente laboral sano basado en el respeto mutuo, y establecer mecanismos efectivos para prevenir y atender situaciones de acoso laboral.",

            'alcance' => "La Politica de Prevencion del Acoso Laboral de {$nombreEmpresa} aplica a:\n\n- Todos los trabajadores sin distincion de cargo o nivel jerarquico\n- Directivos, gerentes, jefes y supervisores\n- Trabajadores de planta y temporales\n- Contratistas y personal tercerizado\n- Practicantes y pasantes\n\nCubre todas las relaciones laborales:\n- Verticales (jefe - subordinado)\n- Horizontales (entre companeros)\n- Ascendentes (subordinado - jefe)\n\nAplica en todas las instalaciones, sedes y en cualquier espacio donde se desarrollen actividades laborales.",

            'declaracion' => "{$nombreEmpresa} declara su compromiso con la prevencion del acoso laboral y establece TOLERANCIA CERO frente a cualquier conducta que atente contra la dignidad de los trabajadores.\n\n**Nos comprometemos a:**\n\n- Garantizar un ambiente laboral libre de hostigamiento, humillaciones y maltrato.\n- Respetar la dignidad humana en todas las relaciones laborales.\n- Proporcionar canales seguros y confidenciales para denunciar el acoso.\n- Proteger a los denunciantes de buena fe contra represalias.\n- Aplicar sanciones proporcionales a quienes incurran en conductas de acoso.\n- Apoyar a las victimas de acoso laboral.\n\nEl acoso laboral es inaceptable y sera sancionado conforme a la ley y al Reglamento Interno de Trabajo.",

            'definiciones' => "**Acoso Laboral (Ley 1010/2006, Art. 2):**\nToda conducta persistente y demostrable, ejercida sobre un empleado o trabajador por parte de un empleador, jefe o superior jerarquico, companero de trabajo o subalterno, encaminada a infundir miedo, intimidacion, terror y angustia, a causar perjuicio laboral, generar desmotivacion en el trabajo, o inducir la renuncia del mismo.\n\n**Modalidades de Acoso Laboral:**\n\n1. **Maltrato Laboral:** Todo acto de violencia contra la integridad fisica o moral, expresiones verbales injuriosas o ultrajantes.\n\n2. **Persecucion Laboral:** Conductas reiteradas o arbitrarias de descalificacion, carga excesiva de trabajo o cambios permanentes de horario que puedan producir la renuncia.\n\n3. **Discriminacion Laboral:** Trato diferenciado por razones de raza, genero, edad, origen familiar, religion, preferencia politica o situacion social.\n\n4. **Entorpecimiento Laboral:** Acciones tendientes a obstaculizar el cumplimiento de la labor o hacerla mas gravosa.\n\n5. **Inequidad Laboral:** Asignacion de funciones a menosprecio del trabajador.\n\n6. **Desproteccion Laboral:** Conductas tendientes a poner en riesgo la integridad del trabajador.",

            'conductas' => "Segun el Articulo 7 de la Ley 1010 de 2006, constituyen acoso laboral las siguientes conductas:\n\n1. Agresion fisica, independientemente de sus consecuencias.\n\n2. Expresiones injuriosas o ultrajantes sobre la persona, con uso de palabras soeces o alusiones a raza, genero, origen o preferencia.\n\n3. Comentarios hostiles y humillantes de descalificacion profesional.\n\n4. Amenazas injustificadas de despido expresadas en presencia de companeros.\n\n5. Descalificacion humillante de propuestas u opiniones de trabajo.\n\n6. Burlas sobre la apariencia fisica o la forma de vestir.\n\n7. Alusion publica a hechos pertenecientes a la intimidad de la persona.\n\n8. Imposicion de deberes ostensiblemente extranos a las obligaciones laborales.\n\n9. Exigencia de laborar horarios excesivos respecto a la jornada contratada.\n\n10. Trato notoriamente discriminatorio.\n\n11. Negativa claramente injustificada a otorgar permisos o licencias.\n\n12. Envio de anonimos, llamadas telefonicas o mensajes con contenido injurioso o ultrajante.",

            'conductas_no_acoso' => "Segun el Articulo 8 de la Ley 1010 de 2006, NO constituyen acoso laboral:\n\n1. Las exigencias y ordenes necesarias para mantener la disciplina en los cuerpos que la requieran.\n\n2. Los actos destinados a ejercer la potestad disciplinaria que legalmente corresponde.\n\n3. La formulacion de exigencias razonables de fidelidad laboral o lealtad empresarial.\n\n4. La formulacion de circulares o memorandos de trabajo encaminados a solicitar mayor eficiencia laboral.\n\n5. Las solicitudes de cumplir los deberes extras de colaboracion con la empresa.\n\n6. Las actuaciones administrativas o gestiones encaminadas a dar por terminado el contrato de trabajo con base en causas legales.\n\n7. La solicitud de cumplir deberes establecidos en el contrato de trabajo.\n\n8. La exigencia de cumplir con las obligaciones, deberes y prohibiciones del Reglamento Interno de Trabajo.\n\n_Estas conductas deben ejercerse de manera respetuosa, razonable y con respeto a la dignidad del trabajador._",

            'mecanismos_prevencion' => "{$nombreEmpresa} implementara los siguientes mecanismos de prevencion:\n\n**1. Comite de Convivencia Laboral:**\n- Conformado segun Resoluciones 652 y 1356 de 2012\n- Reuniones ordinarias trimestrales\n- Atencion de quejas y seguimiento a compromisos\n\n**2. Capacitacion y Sensibilizacion:**\n- Formacion sobre la Ley 1010 de 2006\n- Talleres de comunicacion asertiva y resolucion de conflictos\n- Socializacion de la presente politica\n\n**3. Evaluacion del Clima Laboral:**\n- Encuestas periodicas de ambiente laboral\n- Identificacion de factores de riesgo psicosocial\n- Planes de mejora del clima organizacional\n\n**4. Canales de Comunicacion:**\n- Buzon de quejas y sugerencias\n- Linea confidencial de denuncias\n- Atencion directa por Comite de Convivencia\n\n**5. Coordinacion con el {$comite}:**\n- Inclusion del tema en actividades del SG-SST\n- Vigilancia de condiciones que generen riesgos psicosociales",

            'procedimiento_denuncia' => "**Procedimiento para Denunciar Acoso Laboral:**\n\n**1. Presentacion de la Queja:**\nEl trabajador puede presentar su queja ante:\n- Comite de Convivencia Laboral (interno)\n- Inspector del Trabajo (externo)\n- Juez Laboral mediante accion de tutela\n\n**2. Tramite ante Comite de Convivencia:**\n- Recepcion de la queja por escrito o verbal\n- Citacion a las partes involucradas (por separado)\n- Escucha activa de las versiones\n- Busqueda de solucion concertada\n- Formulacion de compromisos y seguimiento\n- Si no hay acuerdo, remision a autoridades competentes\n\n**3. Garantias del Proceso:**\n- Confidencialidad de la denuncia y del denunciante\n- Proteccion contra represalias\n- Debido proceso para el presunto acosador\n- Presuncion de inocencia\n\n**4. Terminos Legales:**\n- Las acciones caducan 6 meses despues de ocurrido el ultimo hecho de acoso (Art. 18, Ley 1010)\n\n**5. Sanciones:**\nDe comprobarse el acoso, se aplicaran las sanciones previstas en la Ley 1010 de 2006 y el Reglamento Interno de Trabajo.",

            'marco_legal' => "La presente politica se fundamenta en la siguiente normatividad:\n\n- **Constitucion Politica de Colombia:** Articulos 1, 13 y 25 sobre dignidad humana, igualdad y derecho al trabajo.\n\n- **Ley 1010 de 2006:** Medidas para prevenir, corregir y sancionar el acoso laboral.\n\n- **Resolucion 652 de 2012:** Conformacion y funcionamiento del Comite de Convivencia Laboral.\n\n- **Resolucion 1356 de 2012:** Modifica parcialmente la Resolucion 652 de 2012.\n\n- **Decreto 1072 de 2015:** Decreto Unico Reglamentario del Sector Trabajo.\n\n- **Resolucion 0312 de 2019:** Estandares Minimos del Sistema de Gestion de SST.\n\n- **Resolucion 2646 de 2008:** Factores de riesgo psicosocial en el trabajo.",

            'comunicacion' => "La Politica de Prevencion del Acoso Laboral sera:\n\n1. **Comunicada al Comite de Convivencia Laboral** para su conocimiento y aplicacion.\n\n2. **Comunicada al {$comite}** para coordinacion de acciones preventivas.\n\n3. **Publicada** en lugares visibles de las instalaciones.\n\n4. **Incluida** en el proceso de induccion y reinduccion de todos los trabajadores.\n\n5. **Incorporada** al Reglamento Interno de Trabajo.\n\n6. **Socializada** mediante capacitaciones de convivencia laboral.\n\n7. **Difundida** con informacion clara sobre canales de denuncia.\n\n8. **Revisada anualmente** y actualizada cuando sea necesario.\n\n_Todos los trabajadores de {$nombreEmpresa} tienen el deber de conocer esta politica, respetarla y denunciar cualquier situacion de acoso laboral._"
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
