<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase PoliticaAcosoSexual
 *
 * Implementa la generacion de la Politica de Prevencion del Acoso Sexual
 * Numeral 2.1.1 de la Resolucion 0312/2019 - Art. 210A Codigo Penal - Ley 1010/2006
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class PoliticaAcosoSexual extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'politica_acoso_sexual';
    }

    public function getNombre(): string
    {
        return 'Politica de Prevencion del Acoso Sexual';
    }

    public function getDescripcion(): string
    {
        return 'Politica que establece el compromiso de la empresa con la prevencion, atencion y sancion del acoso sexual en el ambito laboral conforme al Art. 210A del Codigo Penal y la Ley 1010 de 2006';
    }

    public function getEstandar(): ?string
    {
        return '2.1.1';
    }

    public function getCodigoDocumento(): string
    {
        return 'POL-ASX';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Declaracion de la Politica', 'key' => 'declaracion'],
            ['numero' => 4, 'nombre' => 'Definicion de Acoso Sexual', 'key' => 'definiciones'],
            ['numero' => 5, 'nombre' => 'Conductas Constitutivas de Acoso Sexual', 'key' => 'conductas'],
            ['numero' => 6, 'nombre' => 'Mecanismos de Prevencion', 'key' => 'mecanismos_prevencion'],
            ['numero' => 7, 'nombre' => 'Procedimiento de Denuncia y Atencion', 'key' => 'procedimiento_denuncia'],
            ['numero' => 8, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
            ['numero' => 9, 'nombre' => 'Comunicacion y Divulgacion', 'key' => 'comunicacion'],
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
            'objetivo' => "La presente politica tiene como objetivo establecer el compromiso de {$nombreEmpresa} con la prevencion, atencion y sancion del acoso sexual en el ambito laboral, conforme al Articulo 210A del Codigo Penal colombiano y demas normatividad aplicable.\n\nEsta politica busca garantizar un ambiente laboral seguro y respetuoso, libre de cualquier conducta de naturaleza sexual no deseada que afecte la dignidad e integridad de los trabajadores.",

            'alcance' => "La Politica de Prevencion del Acoso Sexual de {$nombreEmpresa} aplica a:\n\n- Todos los trabajadores sin distincion de genero, orientacion sexual o nivel jerarquico\n- Directivos, gerentes, jefes y supervisores\n- Trabajadores de planta, temporales, contratistas y practicantes\n- Clientes, proveedores y visitantes en las instalaciones de la empresa\n\nCubre todas las situaciones que ocurran:\n- En las instalaciones y sedes de la empresa\n- En actividades laborales fuera de las instalaciones\n- En eventos y actividades de integracion\n- En comunicaciones laborales presenciales, telefonicas o virtuales",

            'declaracion' => "{$nombreEmpresa} declara su compromiso con la construccion de un ambiente laboral libre de acoso sexual y establece **TOLERANCIA CERO** frente a estas conductas.\n\n**Nos comprometemos a:**\n\n- Respetar la dignidad e integridad de todas las personas.\n- Prevenir activamente el acoso sexual en el trabajo.\n- Proporcionar canales seguros y confidenciales para denunciar.\n- Proteger a las victimas y testigos de cualquier represalia.\n- Investigar con diligencia y celeridad las denuncias presentadas.\n- Colaborar con las autoridades competentes cuando sea necesario.\n\nEl acoso sexual es inaceptable y sera sancionado de acuerdo al Reglamento Interno de Trabajo.",

            'definiciones' => "**Acoso Sexual (Ley 2365 de 2024 - Art. 210A Codigo Penal):**\nSe entendera por acoso sexual todo acto de persecucion, hostigamiento o asedio, de caracter o connotacion sexual, lasciva o libidinosa, que se manifieste por relaciones de poder de orden vertical u horizontal, mediadas por la edad, el sexo, el genero, orientacion e identidad sexual, la posicion laboral, social, o economica, que se de una o varias veces en contra de otra persona en el contexto laboral.\n\n**Modalidades de Acoso Sexual:**\n\n1. **Acoso Quid Pro Quo (Chantaje Sexual):** Cuando se condiciona un beneficio laboral (contratacion, ascenso, permanencia en el empleo, buen trato) a cambio de favores o acceso de tipo sexual.\n\n2. **Acoso Ambiental:** Cuando conductas de naturaleza sexual crean un ambiente de trabajo hostil, intimidante, humillante u ofensivo para quien las recibe.\n\n**Consentimiento:**\nEl consentimiento debe ser libre, informado y voluntario. El silencio, la tolerancia por temor o la falta de resistencia NO constituyen consentimiento.\n\n**Diferencia con Acoso Laboral:**\nEl acoso sexual se distingue del acoso laboral (Ley 1010/2006) en que su motivacion tiene naturaleza sexual. Ambas conductas estan prohibidas y son objeto de politicas separadas en {$nombreEmpresa}.",

            'conductas' => "Quedan expresamente prohibidas en {$nombreEmpresa} las siguientes conductas:\n\n1. Insinuaciones, propuestas o presiones de caracter sexual no deseadas.\n2. Comentarios de contenido sexual sobre el cuerpo, apariencia fisica o vestimenta.\n3. Gestos obscenos, miradas lascivas o expresiones de naturaleza sexual.\n4. Contacto fisico innecesario, no solicitado o no consentido.\n5. Envio de mensajes, imagenes, videos o cualquier material de contenido sexual.\n6. Mostrar o compartir material pornografico o de contenido sexual.\n7. Condicionar beneficios laborales a favores o accesos de tipo sexual.\n8. Amenazas o represalias por rechazar propuestas de naturaleza sexual.\n9. Difusion de rumores, comentarios o informacion sobre la vida sexual de companeros.\n10. Invitaciones o citas con propositos sexuales en el contexto laboral.\n\n_Estas conductas constituyen acoso sexual independientemente de si quien las ejerce tiene una posicion jerarquica superior, igual o inferior a quien las recibe._",

            'mecanismos_prevencion' => "{$nombreEmpresa} implementara los siguientes mecanismos de prevencion:\n\n**1. Capacitacion y Sensibilizacion:**\n- Formacion obligatoria sobre la politica y la Ley 2365 de 2024 para todos los trabajadores\n- Informacion sobre el delito de acoso sexual (Art. 210A Codigo Penal)\n- Inclusion del tema en induccion y reinduccion\n\n**2. Canales Internos de Atencion:**\n- Area de Talento Humano o Gerencia (para medidas de proteccion inmediata)\n- Correo electronico o linea confidencial designada\n\n_Nota: El Comite de Convivencia Laboral NO tiene competencia sobre acoso sexual al ser un delito penal. Las denuncias deben presentarse ante las autoridades penales competentes._\n\n**3. Protocolo de Atencion Inmediata:**\n- Recepcion de la situacion con confidencialidad y sin revictimizacion\n- Medidas de proteccion: separacion de espacios, ajuste de horarios si es necesario\n- Orientacion sobre canales de denuncia penal (Fiscalia, Comisaria de Familia)\n\n**4. Coordinacion Institucional:**\n- Coordinacion con el {$comite} para acciones preventivas\n- Colaboracion activa con Fiscalia, Comisaria de Familia e Inspector del Trabajo",

            'procedimiento_denuncia' => "**Procedimiento para Denuncia y Atencion:**\n\n**IMPORTANTE:** El acoso sexual constituye un delito penal (Art. 210A Codigo Penal, Ley 2365 de 2024). El Comite de Convivencia Laboral (CCL) NO tiene competencia para tramitar este tipo de conductas. Las denuncias deben dirigirse directamente a las autoridades competentes.\n\n**1. Canales de Denuncia:**\n\nA. **Internos (para medidas de proteccion inmediata):**\n- Area de Talento Humano o Gerencia\n- Correo confidencial designado por la empresa\n\nB. **Externos (obligatorios para investigacion penal):**\n- Fiscalia General de la Nacion (denuncia penal)\n- Comisaria de Familia (medidas de proteccion)\n- Inspector del Trabajo (si hay dimension laboral adicional)\n\n**2. Atencion Inmediata por parte de la empresa:**\n- Escucha activa, respetuosa y sin juicios ni revictimizacion\n- Garantia de confidencialidad\n- Informacion sobre derechos y canales de denuncia externos\n- Medidas de proteccion: separacion de espacios, ajuste de turnos si es necesario\n\n**3. Colaboracion con Autoridades:**\n{$nombreEmpresa} colaborara activamente con la Fiscalia y demas autoridades en la investigacion del caso y garantizara la no represalia contra la victima o testigos.\n\n**4. Sanciones Disciplinarias Internas:**\nDe comprobarse el acoso sexual, se aplicaran adicionalmente las sanciones de acuerdo al Reglamento Interno de Trabajo, sin perjuicio de las sanciones penales que determine la justicia.\n\n**Garantias:**\n- Confidencialidad durante todo el proceso\n- Proteccion contra represalias para denunciantes y testigos\n- Estabilidad laboral reforzada para la victima durante el proceso",

            'marco_legal' => "La presente politica se fundamenta en la siguiente normatividad:\n\n- **Constitucion Politica de Colombia:** Articulos 1, 13 y 43 sobre dignidad humana, igualdad y derechos.\n\n- **Codigo Penal Colombiano - Art. 210A:** Tipificacion del delito de acoso sexual (pena de 1 a 3 anos de prision).\n\n- **Ley 2365 de 2024:** Modifica el Art. 210A del Codigo Penal, actualizando la definicion de acoso sexual y sus alcances en el contexto laboral.\n\n- **Ley 1257 de 2008:** Sensibilizacion, prevencion y sancion de formas de violencia contra las mujeres.\n\n- **Ley 1719 de 2014:** Acceso a la justicia para victimas de violencia sexual.\n\n- **Ley 1010 de 2006:** Marco de referencia expreso de la Resolucion 3461 de 2025 para la conformacion del Comite de Convivencia Laboral.\n\n- **Resolucion 2646 de 2008:** Articulo 14, medidas preventivas y correctivas frente al acoso laboral. Citada expresamente en la Resolucion 3461 de 2025.\n\n- **Decreto 1072 de 2015:** Decreto Unico Reglamentario del Sector Trabajo.\n\n- **Resolucion 0312 de 2019:** Estandares Minimos del SG-SST.\n\n- **Resolucion 3461 de 2025 - Ministerio del Trabajo:** Vigente desde el 1 de septiembre de 2025. Define la conformacion y funcionamiento del Comite de Convivencia Laboral y deroga expresamente las Resoluciones 652 y 1356 de 2012.",

            'comunicacion' => "La Politica de Prevencion del Acoso Sexual sera:\n\n1. **Comunicada al Comite de Convivencia Laboral** para su aplicacion.\n\n2. **Comunicada al {$comite}** para coordinacion de acciones preventivas.\n\n3. **Publicada** en lugares visibles con informacion de canales de denuncia.\n\n4. **Socializada** mediante capacitacion obligatoria a todos los trabajadores.\n\n5. **Incluida** en el proceso de induccion y reinduccion.\n\n6. **Informada** a contratistas, proveedores y visitantes.\n\n7. **Revisada anualmente** y actualizada cuando sea necesario.\n\n_Todos los trabajadores de {$nombreEmpresa} tienen la responsabilidad de conocer esta politica, respetarla y denunciar cualquier situacion de acoso sexual._"
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
