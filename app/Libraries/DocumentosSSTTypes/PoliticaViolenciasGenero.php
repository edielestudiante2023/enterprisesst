<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase PoliticaViolenciasGenero
 *
 * Implementa la generacion de la Politica de Prevencion del Acoso Sexual y Violencias de Genero
 * Numeral 2.1.1 de la Resolucion 0312/2019 - Ley 1257 de 2008
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class PoliticaViolenciasGenero extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'politica_violencias_genero';
    }

    public function getNombre(): string
    {
        return 'Politica de Prevencion del Acoso Sexual y Violencias de Genero';
    }

    public function getDescripcion(): string
    {
        return 'Politica que establece el compromiso de la empresa con la prevencion del acoso sexual y violencias basadas en genero en el ambito laboral';
    }

    public function getEstandar(): ?string
    {
        return '2.1.1';
    }

    public function getCodigoDocumento(): string
    {
        return 'POL-VGE';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Declaracion de la Politica', 'key' => 'declaracion'],
            ['numero' => 4, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 5, 'nombre' => 'Conductas Prohibidas', 'key' => 'conductas_prohibidas'],
            ['numero' => 6, 'nombre' => 'Mecanismos de Prevencion', 'key' => 'mecanismos_prevencion'],
            ['numero' => 7, 'nombre' => 'Procedimiento de Denuncia y Atencion', 'key' => 'procedimiento'],
            ['numero' => 8, 'nombre' => 'Sanciones', 'key' => 'sanciones'],
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
            'objetivo' => "La presente politica tiene como objetivo establecer el compromiso de {$nombreEmpresa} con la prevencion, atencion y sancion del acoso sexual y las violencias basadas en genero en el ambito laboral, conforme a la Ley 1257 de 2008 y demas normatividad aplicable.\n\nEsta politica busca garantizar un ambiente laboral seguro, respetuoso y libre de cualquier forma de violencia o discriminacion por razon de genero, protegiendo la dignidad, integridad y derechos de todos los trabajadores.",

            'alcance' => "La Politica de Prevencion del Acoso Sexual y Violencias de Genero de {$nombreEmpresa} aplica a:\n\n- Todos los trabajadores sin distincion de genero, orientacion sexual o identidad de genero\n- Todos los niveles jerarquicos y formas de contratacion\n- Contratistas, proveedores y terceros que interactuen con la empresa\n- Clientes y visitantes\n\nCubre todas las situaciones que ocurran:\n- En las instalaciones de la empresa\n- En actividades laborales fuera de las instalaciones\n- En eventos sociales o de integracion\n- En comunicaciones laborales (presenciales, telefonicas, virtuales, redes sociales)\n- En cualquier contexto relacionado con la actividad laboral",

            'declaracion' => "{$nombreEmpresa} declara su compromiso con la construccion de un ambiente laboral libre de acoso sexual y violencias de genero, y establece **TOLERANCIA CERO** frente a estas conductas.\n\n**Nos comprometemos a:**\n\n- Respetar la dignidad e integridad de todas las personas.\n- Garantizar igualdad de trato y oportunidades sin distincion de genero.\n- Prevenir activamente el acoso sexual y las violencias de genero.\n- Proporcionar canales seguros y confidenciales para denunciar.\n- Proteger a las victimas y testigos de cualquier represalia.\n- Investigar con diligencia y celeridad las denuncias.\n- Aplicar sanciones ejemplares a quienes incurran en estas conductas.\n- Colaborar con las autoridades competentes.\n\n_El acoso sexual y las violencias de genero son inaceptables y seran sancionados con todo el rigor de la ley y del Reglamento Interno de Trabajo._",

            'definiciones' => "**Violencia de Genero (Ley 1257/2008):**\nCualquier accion u omision que le cause muerte, dano o sufrimiento fisico, sexual, psicologico, economico o patrimonial a una persona por su condicion de genero.\n\n**Acoso Sexual:**\nToda conducta de naturaleza sexual, no deseada por quien la recibe, que afecta la dignidad de la persona en el contexto laboral. Incluye:\n\n- **Acoso Quid Pro Quo:** Cuando se condiciona un beneficio laboral (contratacion, ascenso, permanencia) a cambio de favores sexuales.\n\n- **Acoso Ambiental:** Cuando las conductas de naturaleza sexual crean un ambiente de trabajo hostil, intimidante u ofensivo.\n\n**Violencia Fisica:** Riesgo o disminucion de la integridad corporal de una persona.\n\n**Violencia Psicologica:** Accion u omision destinada a degradar o controlar las acciones, comportamientos, creencias y decisiones de otra persona.\n\n**Consentimiento:** Manifestacion libre, informada y voluntaria de aceptar una conducta. El silencio o la falta de resistencia NO constituyen consentimiento.",

            'conductas_prohibidas' => "Quedan expresamente prohibidas en {$nombreEmpresa} las siguientes conductas:\n\n**Acoso Sexual:**\n1. Insinuaciones, propuestas o presiones de caracter sexual no deseadas.\n2. Comentarios de contenido sexual sobre el cuerpo, apariencia o vestimenta.\n3. Gestos obscenos, miradas lascivas o silbidos.\n4. Contacto fisico innecesario, no solicitado o no consentido.\n5. Envio de mensajes, imagenes, videos o contenido de caracter sexual.\n6. Mostrar material pornografico o de contenido sexual.\n7. Condicionar beneficios laborales a favores sexuales.\n8. Amenazas o represalias por rechazar propuestas sexuales.\n9. Difusion de rumores sobre la vida sexual de companeros.\n\n**Violencias de Genero:**\n1. Comentarios denigrantes, insultos o burlas por razon de genero.\n2. Exclusion de reuniones o actividades por genero.\n3. Asignacion de tareas basadas en estereotipos de genero.\n4. Bromas, chistes o comentarios sexistas, misoginos o machistas.\n5. Cuestionamiento de capacidades profesionales por genero.\n6. Violencia fisica o amenazas.\n7. Intimidacion o acoso por orientacion sexual o identidad de genero.",

            'mecanismos_prevencion' => "{$nombreEmpresa} implementara los siguientes mecanismos de prevencion:\n\n**1. Capacitacion y Sensibilizacion:**\n- Formacion obligatoria sobre la politica para todos los trabajadores\n- Talleres de igualdad de genero y respeto en el trabajo\n- Inclusion del tema en induccion y reinduccion\n\n**2. Canales de Denuncia:**\n- Comite de Convivencia Laboral\n- Correo electronico confidencial\n- Buzon de denuncias anonimas\n- Linea telefonica de atencion\n\n**3. Protocolo de Atencion:**\n- Procedimiento claro para recepcion y tramite de denuncias\n- Medidas de proteccion inmediata para la victima\n- Acompanamiento psicologico si es requerido\n\n**4. Monitoreo:**\n- Evaluacion de clima laboral con enfoque de genero\n- Seguimiento a casos reportados\n- Indicadores de gestion de la politica\n\n**5. Coordinacion:**\n- Articulacion con el Comite de Convivencia Laboral\n- Coordinacion con el {$comite}\n- Colaboracion con autoridades cuando sea necesario",

            'procedimiento' => "**Procedimiento para Denuncia y Atencion:**\n\n**1. Presentacion de la Denuncia:**\nLa persona afectada puede denunciar ante:\n- Comite de Convivencia Laboral\n- Area de Talento Humano\n- Correo confidencial designado\n- Superior inmediato (si no es el agresor)\n\n**2. Atencion Inmediata:**\n- Escucha activa, respetuosa, sin juicios ni revictimizacion\n- Garantia de confidencialidad\n- Informacion sobre derechos y opciones\n- Medidas de proteccion si hay riesgo (separacion de puestos, cambio de horario)\n\n**3. Investigacion:**\n- Recopilacion de evidencias y testimonios\n- Entrevista a las partes con garantia de debido proceso\n- Preservacion de confidencialidad\n- Plazo maximo de 15 dias habiles\n\n**4. Remision a Autoridades (cuando aplique):**\n- Fiscalia General de la Nacion (delitos)\n- Comisaria de Familia\n- Inspector del Trabajo\n\n**5. Seguimiento:**\n- Acompanamiento a la victima\n- Verificacion de cumplimiento de medidas\n- Prevencion de represalias\n\n**Garantias:**\n- Confidencialidad absoluta\n- Proteccion contra represalias\n- Debido proceso para el acusado\n- No revictimizacion",

            'sanciones' => "**Sanciones por Acoso Sexual y Violencias de Genero:**\n\n**Sanciones Disciplinarias Internas:**\nSegun la gravedad y circunstancias del caso:\n1. Llamado de atencion escrito con compromiso de cambio de conducta\n2. Suspension del contrato segun Reglamento Interno de Trabajo\n3. Terminacion del contrato con justa causa (Art. 62 CST)\n\n**Agravantes:**\n- Posicion de autoridad o jerarquia sobre la victima\n- Reincidencia en la conducta\n- Multiples victimas\n- Amenazas o represalias contra denunciantes\n- Uso de medios electronicos o redes sociales\n\n**Sanciones Penales:**\nEl acoso sexual es delito en Colombia (Art. 210A Codigo Penal):\n- Prision de 1 a 3 anos\n- Agravantes cuando hay relacion laboral de subordinacion\n\n**Proteccion al Denunciante:**\n- Prohibicion de despido o desmejora como represalia\n- Estabilidad laboral reforzada durante el proceso\n\n**Colaboracion con Autoridades:**\n{$nombreEmpresa} colaborara activamente con las autoridades competentes en la investigacion de casos que constituyan delito.",

            'marco_legal' => "La presente politica se fundamenta en la siguiente normatividad:\n\n- **Constitucion Politica de Colombia:** Articulos 13 y 43 sobre igualdad y derechos de la mujer.\n\n- **Ley 1257 de 2008:** Sensibilizacion, prevencion y sancion de formas de violencia contra las mujeres.\n\n- **Ley 1010 de 2006:** Acoso laboral (complementaria).\n\n- **Ley 1761 de 2015 (Rosa Elvira Cely):** Tipificacion del feminicidio.\n\n- **Ley 1719 de 2014:** Acceso a la justicia para victimas de violencia sexual.\n\n- **Codigo Penal Colombiano:** Articulo 210A - Acoso Sexual.\n\n- **Decreto 4463 de 2011:** Igualdad salarial entre mujeres y hombres.\n\n- **Resolucion 652 de 2012:** Comite de Convivencia Laboral.\n\n- **Decreto 1072 de 2015:** Decreto Unico Reglamentario del Sector Trabajo.\n\n- **Resolucion 0312 de 2019:** Estandares Minimos del SG-SST.",

            'comunicacion' => "La Politica de Prevencion del Acoso Sexual y Violencias de Genero sera:\n\n1. **Comunicada al Comite de Convivencia Laboral** para su conocimiento y aplicacion.\n\n2. **Comunicada al {$comite}** para coordinacion de acciones preventivas.\n\n3. **Publicada** en lugares visibles con informacion de canales de denuncia.\n\n4. **Socializada** mediante capacitacion obligatoria a todos los trabajadores.\n\n5. **Incluida** en el proceso de induccion y reinduccion.\n\n6. **Informada** a contratistas, proveedores y visitantes.\n\n7. **Difundida** con lineas de ayuda externas:\n   - Linea 155 (Orientacion a mujeres victimas de violencia)\n   - Linea 122 (Fiscalia)\n   - Comisarias de Familia\n\n8. **Revisada anualmente** y actualizada cuando sea necesario.\n\n_Todos los trabajadores de {$nombreEmpresa} tienen la responsabilidad de conocer esta politica, respetarla y denunciar cualquier situacion de acoso sexual o violencia de genero._"
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
