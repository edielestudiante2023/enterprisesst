<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase PoliticaDiscriminacion
 *
 * Implementa la generacion de la Politica de Prevencion de la Discriminacion, Maltrato y Violencia
 * Numeral 2.1.1 de la Resolucion 0312/2019
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class PoliticaDiscriminacion extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'politica_discriminacion';
    }

    public function getNombre(): string
    {
        return 'Politica de Prevencion de la Discriminacion, Maltrato y Violencia';
    }

    public function getDescripcion(): string
    {
        return 'Politica que establece el compromiso de la empresa con la prevencion de la discriminacion, el maltrato y la violencia en el ambiente laboral';
    }

    public function getEstandar(): ?string
    {
        return '2.1.1';
    }

    public function getCodigoDocumento(): string
    {
        return 'POL-DIS';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Declaracion de la Politica', 'key' => 'declaracion'],
            ['numero' => 4, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 5, 'nombre' => 'Principios Rectores', 'key' => 'principios'],
            ['numero' => 6, 'nombre' => 'Conductas Prohibidas', 'key' => 'conductas_prohibidas'],
            ['numero' => 7, 'nombre' => 'Mecanismos de Prevencion', 'key' => 'mecanismos_prevencion'],
            ['numero' => 8, 'nombre' => 'Procedimiento de Denuncia', 'key' => 'procedimiento'],
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
            'objetivo' => "La presente politica tiene como objetivo establecer el compromiso de {$nombreEmpresa} con la prevencion de la discriminacion, el maltrato y la violencia en el ambiente laboral, garantizando la igualdad de trato y oportunidades para todos los trabajadores, conforme al Articulo 13 de la Constitucion Politica y la Ley 1010 de 2006.\n\nEsta politica busca promover un ambiente laboral inclusivo, respetuoso de la diversidad y libre de cualquier forma de discriminacion, maltrato o violencia, reconociendo la dignidad humana como principio fundamental de las relaciones laborales.",

            'alcance' => "La Politica de Prevencion de la Discriminacion, Maltrato y Violencia de {$nombreEmpresa} aplica a:\n\n- Todos los trabajadores sin excepcion, independientemente de su cargo o forma de contratacion\n- Procesos de reclutamiento, seleccion y contratacion\n- Asignacion de funciones, promociones y ascensos\n- Capacitacion y desarrollo profesional\n- Evaluacion del desempeno\n- Remuneracion y beneficios\n- Terminacion de la relacion laboral\n\nTambien aplica a:\n- Contratistas, proveedores y terceros\n- Relaciones con clientes\n- Todas las formas de comunicacion (presencial, escrita, virtual)",

            'declaracion' => "{$nombreEmpresa} declara su compromiso con la construccion de un ambiente laboral inclusivo, diverso y libre de discriminacion, maltrato y violencia.\n\n**Declaramos que:**\n\n- Valoramos y respetamos la diversidad de nuestros trabajadores.\n- Garantizamos igualdad de oportunidades sin distincion alguna.\n- Rechazamos toda forma de discriminacion por motivos de raza, sexo, religion, origen, opinion politica, orientacion sexual, discapacidad, edad o cualquier otra condicion.\n- No toleramos el maltrato ni la violencia en ninguna de sus formas.\n- Promovemos relaciones laborales basadas en el respeto y la dignidad humana.\n- Proporcionamos canales seguros para denunciar conductas discriminatorias.\n- Aplicamos sanciones a quienes incurran en discriminacion, maltrato o violencia.\n\n_Todas las personas merecen ser tratadas con dignidad y respeto en {$nombreEmpresa}._",

            'definiciones' => "**Discriminacion:**\nTodo trato diferenciado injustificado que tenga por objeto o resultado menoscabar o anular el reconocimiento, goce o ejercicio de derechos y libertades de una persona.\n\n**Discriminacion Directa:**\nCuando una persona es tratada de manera menos favorable que otra en situacion comparable, por motivos prohibidos.\n\n**Discriminacion Indirecta:**\nCuando una disposicion, criterio o practica aparentemente neutra pone a personas de cierta condicion en desventaja particular.\n\n**Criterios Sospechosos de Discriminacion (Art. 13 CP):**\nRaza, sexo, origen nacional o familiar, lengua, religion, opinion politica o filosofica, orientacion sexual, identidad de genero, discapacidad, condicion social o economica.\n\n**Maltrato Laboral:**\nTodo acto de violencia contra la integridad fisica o moral, la libertad fisica o sexual, y los bienes de quien se desempene como empleado o trabajador.\n\n**Violencia Laboral:**\nToda accion, omision o comportamiento destinado a provocar, directa o indirectamente, dano fisico, psicologico o moral a un trabajador.\n\n**Inclusion:**\nProceso que asegura que todas las personas tengan las mismas oportunidades y puedan participar plenamente en la vida laboral.\n\n**Diversidad:**\nReconocimiento y valoracion de las diferencias individuales que hacen unica a cada persona.",

            'principios' => "{$nombreEmpresa} fundamenta esta politica en los siguientes principios:\n\n**1. Dignidad Humana:**\nTodas las personas merecen respeto por el solo hecho de ser seres humanos, independientemente de cualquier condicion.\n\n**2. Igualdad:**\nTodas las personas tienen derecho a recibir el mismo trato en igualdad de condiciones.\n\n**3. No Discriminacion:**\nProhibicion de todo trato diferenciado que no tenga justificacion objetiva y razonable.\n\n**4. Diversidad e Inclusion:**\nValorar las diferencias y garantizar la participacion plena de todas las personas.\n\n**5. Respeto:**\nTrato cordial, profesional y considerado hacia todos los companeros de trabajo.\n\n**6. Equidad:**\nReconocer las diferencias para garantizar igualdad real de oportunidades.\n\n**7. Confidencialidad:**\nProteccion de la informacion de quienes denuncien conductas discriminatorias.\n\n**8. Debido Proceso:**\nGarantia de defensa y presuncion de inocencia para los acusados.",

            'conductas_prohibidas' => "Quedan expresamente prohibidas en {$nombreEmpresa} las siguientes conductas:\n\n**Discriminacion por:**\n- Raza, color de piel, etnia o nacionalidad\n- Sexo, genero, orientacion sexual o identidad de genero\n- Religion, creencias o falta de ellas\n- Opinion politica, ideologica o sindical\n- Origen nacional, regional o social\n- Condicion economica o clase social\n- Discapacidad fisica, sensorial, intelectual o psicosocial\n- Edad\n- Estado civil o situacion familiar\n- Apariencia fisica, forma de vestir o tatuajes\n- Condicion de salud, incluyendo VIH/SIDA\n- Embarazo o maternidad/paternidad\n\n**Maltrato y Violencia:**\n- Agresion fisica de cualquier tipo\n- Insultos, gritos, palabras ofensivas o denigrantes\n- Humillaciones publicas o privadas\n- Amenazas, intimidacion o coercion\n- Exclusion deliberada de reuniones o actividades\n- Sobrecarga de trabajo o asignacion de tareas degradantes como castigo\n- Aislamiento social o profesional\n- Difusion de rumores o informacion falsa\n- Destruccion o dano de pertenencias",

            'mecanismos_prevencion' => "{$nombreEmpresa} implementara los siguientes mecanismos de prevencion:\n\n**1. Seleccion sin Discriminacion:**\n- Procesos de seleccion basados en competencias y meritos\n- Entrevistas estandarizadas sin preguntas discriminatorias\n- Formacion a entrevistadores sobre sesgos inconscientes\n\n**2. Capacitacion:**\n- Formacion en diversidad e inclusion para todos los trabajadores\n- Sensibilizacion sobre sesgos inconscientes\n- Talleres de comunicacion respetuosa\n\n**3. Ajustes Razonables:**\n- Adaptaciones para personas con discapacidad\n- Flexibilidad para situaciones particulares\n\n**4. Comite de Convivencia Laboral:**\n- Atencion de casos de discriminacion y maltrato\n- Mediacion de conflictos\n- Seguimiento a compromisos\n\n**5. Monitoreo:**\n- Evaluacion de clima laboral con enfoque de inclusion\n- Indicadores de diversidad en la plantilla\n- Analisis de brechas salariales por genero\n\n**6. Comunicacion Inclusiva:**\n- Lenguaje incluyente en comunicaciones oficiales\n- Accesibilidad de la informacion\n\n**7. Canales de Denuncia:**\n- Multiples canales confidenciales\n- Proteccion a denunciantes",

            'procedimiento' => "**Procedimiento para Denuncia de Discriminacion, Maltrato o Violencia:**\n\n**1. Canales de Denuncia:**\n- Comite de Convivencia Laboral\n- Area de Talento Humano\n- Superior inmediato (si no es el agresor)\n- Correo confidencial de denuncias\n- Buzon anonimo\n\n**2. Recepcion de la Denuncia:**\n- Registro confidencial de los hechos\n- Evaluacion preliminar\n- Determinacion de medidas de proteccion si son necesarias\n\n**3. Investigacion:**\n- Recopilacion de evidencias y testimonios\n- Entrevistas a las partes involucradas\n- Garantia de debido proceso\n- Plazo maximo de 15 dias habiles\n\n**4. Resolucion:**\n- Determinacion de responsabilidad\n- Aplicacion de sanciones segun gravedad\n- Medidas de reparacion a la victima\n- Acciones de no repeticion\n\n**5. Seguimiento:**\n- Verificacion de cumplimiento de medidas\n- Apoyo a la victima\n- Evaluacion de efectividad\n\n**Garantias:**\n- Confidencialidad absoluta\n- Proteccion contra represalias\n- Debido proceso para el acusado\n- Presuncion de inocencia",

            'marco_legal' => "La presente politica se fundamenta en la siguiente normatividad:\n\n- **Constitucion Politica de Colombia:** Articulo 13 sobre igualdad y no discriminacion.\n\n- **Ley 1010 de 2006:** Medidas para prevenir, corregir y sancionar el acoso laboral.\n\n- **Ley 1482 de 2011:** Actos de discriminacion como delito penal.\n\n- **Ley 1752 de 2015:** Modifica la Ley 1482 sobre discriminacion.\n\n- **Ley 361 de 1997:** Mecanismos de integracion social de personas con discapacidad.\n\n- **Ley 1618 de 2013:** Garantia de derechos de personas con discapacidad.\n\n- **Convenio 111 de la OIT:** Discriminacion en materia de empleo y ocupacion.\n\n- **Decreto 1072 de 2015:** Decreto Unico Reglamentario del Sector Trabajo.\n\n- **Resolucion 0312 de 2019:** Estandares Minimos del Sistema de Gestion de SST.\n\n- **Resolucion 652 de 2012:** Comite de Convivencia Laboral.",

            'comunicacion' => "La Politica de Prevencion de la Discriminacion, Maltrato y Violencia sera:\n\n1. **Comunicada al Comite de Convivencia Laboral** para su conocimiento y aplicacion.\n\n2. **Comunicada al {$comite}** para coordinacion de acciones preventivas.\n\n3. **Publicada** en lugares visibles de las instalaciones.\n\n4. **Socializada** mediante capacitacion a todos los trabajadores.\n\n5. **Incluida** en el proceso de induccion y reinduccion.\n\n6. **Informada** a contratistas, proveedores y visitantes.\n\n7. **Difundida** junto con los canales de denuncia disponibles.\n\n8. **Reforzada** mediante campanas periodicas de sensibilizacion sobre diversidad e inclusion.\n\n9. **Revisada anualmente** y actualizada cuando sea necesario.\n\n_Todos los trabajadores de {$nombreEmpresa} tienen el deber de conocer esta politica, respetarla y contribuir a un ambiente laboral libre de discriminacion, maltrato y violencia._"
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
