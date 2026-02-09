<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase PoliticaAlcoholDrogas
 *
 * Implementa la generacion de la Politica de Prevencion del Consumo de Alcohol, Tabaco y SPA
 * Numeral 2.1.1 de la Resolucion 0312/2019
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class PoliticaAlcoholDrogas extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'politica_alcohol_drogas';
    }

    public function getNombre(): string
    {
        return 'Politica de Prevencion del Consumo de Alcohol, Tabaco y Sustancias Psicoactivas';
    }

    public function getDescripcion(): string
    {
        return 'Politica que establece el compromiso de la empresa con la prevencion del consumo de alcohol, tabaco y sustancias psicoactivas en el ambiente laboral';
    }

    public function getEstandar(): ?string
    {
        return '2.1.1';
    }

    public function getCodigoDocumento(): string
    {
        return 'POL-ALC';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Declaracion de la Politica', 'key' => 'declaracion'],
            ['numero' => 4, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 5, 'nombre' => 'Prohibiciones', 'key' => 'prohibiciones'],
            ['numero' => 6, 'nombre' => 'Programa de Prevencion', 'key' => 'programa_prevencion'],
            ['numero' => 7, 'nombre' => 'Sanciones', 'key' => 'sanciones'],
            ['numero' => 8, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
            ['numero' => 9, 'nombre' => 'Comunicacion y Divulgacion', 'key' => 'comunicacion'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['consultor_sst', 'representante_legal'];
    }

    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        $prompts = [
            'objetivo' => "Genera el objetivo de la Politica de Prevencion del Consumo de Alcohol, Tabaco y SPA.
Debe expresar el compromiso de la organizacion con:
- Promover estilos de vida y trabajo saludables
- Prevenir el consumo de sustancias psicoactivas que afecten el desempeno laboral
- Proteger la salud de los trabajadores y el ambiente laboral
- Reducir los riesgos de accidentalidad asociados al consumo
IMPORTANTE: Maximo 2-3 parrafos concisos.",

            'alcance' => "Define el alcance de la Politica de Prevencion del Consumo de Alcohol, Tabaco y SPA.
Debe especificar que aplica a:
- Todos los trabajadores independientemente de su forma de contratacion
- Contratistas y subcontratistas
- Visitantes
- Todas las instalaciones y actividades de la empresa
- Dentro y fuera de jornada laboral cuando represente a la empresa
Para empresas de {$estandares} estandares, ajusta el alcance proporcionalmente.",

            'declaracion' => "Genera la declaracion formal de la Politica de Prevencion del Consumo de Alcohol, Tabaco y SPA.
Debe incluir el compromiso explicito de la alta direccion con:
- Ambiente laboral libre de alcohol, tabaco y sustancias psicoactivas
- Promocion de estilos de vida saludables
- Prevencion del consumo y sus efectos en el trabajo
- Apoyo a trabajadores que presenten problemas de consumo
- Cumplimiento de la normatividad vigente
FORMATO: Redactar en primera persona plural.
TONO: Formal, comprometido, claro.",

            'definiciones' => "Proporciona definiciones claras de los terminos clave.
INCLUIR:
- Alcohol: bebidas que contengan etanol
- Tabaco: productos derivados del tabaco incluyendo cigarrillos electronicos
- Sustancias Psicoactivas (SPA): drogas ilicitas y medicamentos controlados usados sin prescripcion
- Estado de embriaguez: alteracion de capacidades por consumo
- Estado de adiccion: dependencia fisica o psicologica
- Programa de prevencion: actividades de sensibilizacion y educacion
FORMATO: Lista con definiciones claras y concisas.",

            'prohibiciones' => "Lista las prohibiciones especificas relacionadas con alcohol, tabaco y SPA.
PROHIBICIONES:
1. Presentarse a trabajar bajo efectos de alcohol o SPA
2. Consumir alcohol, tabaco o SPA durante la jornada laboral
3. Consumir en las instalaciones de la empresa
4. Portar o distribuir sustancias ilicitas
5. Vender o comercializar alcohol o tabaco en las instalaciones
6. Fumar en areas no autorizadas
7. Ingresar en estado de embriaguez o bajo efecto de SPA
IMPORTANTE: Ajusta segun la legislacion colombiana vigente.",

            'programa_prevencion' => "Describe el programa de prevencion que implementara la empresa.
ACTIVIDADES:
1. Capacitacion y sensibilizacion sobre efectos del consumo
2. Campanas de promocion de estilos de vida saludables
3. Divulgacion de riesgos asociados al consumo
4. Apoyo psicosocial a trabajadores con problemas de consumo
5. Examenes medicos ocupacionales (si aplica segun cargo)
6. Senalizacion de zonas libres de humo
7. Canales de reporte confidencial
Para {$estandares} estandares, ajusta la complejidad del programa.",

            'sanciones' => "Describe las sanciones aplicables por incumplimiento de la politica.
PROCEDIMIENTO:
1. Verificacion de la situacion (no puede ser discriminatorio)
2. Aplicacion del debido proceso segun Reglamento Interno de Trabajo
3. Sanciones progresivas:
   - Primera falta: Llamado de atencion escrito + remision a programa de ayuda
   - Segunda falta: Suspension segun RIT
   - Tercera falta o falta grave: Terminacion del contrato con justa causa
4. Ofrecimiento de apoyo para rehabilitacion
IMPORTANTE: Mencionar que se respetara el debido proceso y la dignidad del trabajador.",

            'marco_legal' => "Lista el marco normativo aplicable a la Politica de Alcohol, Tabaco y SPA.
NORMATIVA ESENCIAL:
- Constitucion Politica de Colombia: Art. 49 (atencion en salud y saneamiento)
- Ley 1566 de 2012: Atencion integral a personas con consumo de SPA
- Resolucion 1075 de 1992: Programas de prevencion del alcoholismo y farmacodependencia
- Decreto 1072 de 2015: Obligaciones del empleador en SST
- Ley 1335 de 2009: Prevencion del consumo de tabaco
- Resolucion 0312 de 2019: Estandares Minimos del SG-SST
FORMATO: Lista con vinetas, NO usar tablas.",

            'comunicacion' => "Define como se comunicara y divulgara la politica.
INCLUIR:
- Comunicacion al COPASST/Vigia SST (segun estandares del cliente)
- Publicacion en lugares visibles
- Inclusion en induccion y reinduccion
- Entrega a contratistas
- Capacitaciones periodicas de sensibilizacion
- Difusion de lineas de ayuda y apoyo
- Revision anual o cuando sea necesario
IMPORTANTE: Para {$estandares} estandares, usar 'Vigia SST' si son 7 estandares, 'COPASST' si son 21 o mas."
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' de la Politica de Prevencion del Consumo de Alcohol, Tabaco y SPA.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'LA EMPRESA';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "La presente politica tiene como objetivo establecer el compromiso de {$nombreEmpresa} con la promocion de estilos de vida y trabajo saludables, previniendo el consumo de alcohol, tabaco y sustancias psicoactivas que puedan afectar la salud de los trabajadores, el ambiente laboral y la productividad de la empresa.\n\nEsta politica busca proteger la integridad fisica y mental de todos los colaboradores, reduciendo los riesgos de accidentalidad laboral asociados al consumo de estas sustancias y fomentando una cultura de autocuidado y bienestar.",

            'alcance' => "La Politica de Prevencion del Consumo de Alcohol, Tabaco y Sustancias Psicoactivas de {$nombreEmpresa} aplica a:\n\n- Todos los trabajadores, independientemente de su forma de contratacion\n- Contratistas, subcontratistas y proveedores\n- Trabajadores en mision\n- Visitantes\n- Practicantes y pasantes\n\nAplica en todas las instalaciones de la empresa, en jornada laboral y en cualquier actividad que se realice en representacion de la organizacion, incluyendo eventos sociales y viajes de trabajo.",

            'declaracion' => "{$nombreEmpresa} se compromete a mantener un ambiente laboral libre del consumo de alcohol, tabaco y sustancias psicoactivas, reconociendo que el consumo de estas sustancias afecta la salud, la seguridad y el bienestar de los trabajadores.\n\n**Nos comprometemos a:**\n\n- Promover estilos de vida saludables y el autocuidado.\n- Implementar programas de prevencion y sensibilizacion.\n- Ofrecer apoyo a los trabajadores que presenten problemas de consumo.\n- Garantizar un ambiente laboral seguro y libre de sustancias.\n- Cumplir con la normatividad legal vigente en esta materia.\n\nEsta politica se aplica sin discriminacion y respetando la dignidad de todos los trabajadores.",

            'definiciones' => "Para efectos de esta politica, se establecen las siguientes definiciones:\n\n**Alcohol:** Toda bebida que contenga alcohol etilico (etanol), incluyendo cerveza, vino, licores y derivados.\n\n**Tabaco:** Productos elaborados total o parcialmente con hojas de tabaco, incluyendo cigarrillos, cigarros, tabaco para pipa y dispositivos electronicos de vapeo.\n\n**Sustancias Psicoactivas (SPA):** Toda sustancia que al ser consumida modifica las funciones del sistema nervioso central, incluyendo drogas ilicitas (marihuana, cocaina, heroina, etc.) y medicamentos controlados usados sin prescripcion medica.\n\n**Estado de Embriaguez:** Alteracion transitoria de las condiciones fisicas y mentales causada por el consumo de alcohol.\n\n**Adiccion:** Dependencia fisica y/o psicologica hacia una sustancia que genera la necesidad compulsiva de consumirla.",

            'prohibiciones' => "Queda expresamente prohibido para todos los trabajadores de {$nombreEmpresa}:\n\n1. **Presentarse a trabajar** bajo los efectos del alcohol o sustancias psicoactivas.\n\n2. **Consumir** alcohol, tabaco o sustancias psicoactivas durante la jornada laboral, incluyendo tiempo de descanso.\n\n3. **Ingresar** a las instalaciones de la empresa en estado de embriaguez o bajo efectos de SPA.\n\n4. **Portar, distribuir o comercializar** alcohol, tabaco o sustancias psicoactivas ilicitas en las instalaciones.\n\n5. **Fumar** en areas cerradas o en zonas no autorizadas, conforme a la Ley 1335 de 2009.\n\n6. **Consumir** alcohol o SPA en actividades laborales fuera de las instalaciones (visitas, capacitaciones, eventos).\n\n7. **Conducir vehiculos** de la empresa bajo efectos de alcohol o SPA.",

            'programa_prevencion' => "{$nombreEmpresa} implementara las siguientes actividades de prevencion:\n\n**Educacion y Sensibilizacion:**\n- Capacitaciones periodicas sobre efectos del consumo de alcohol, tabaco y SPA\n- Material informativo sobre riesgos para la salud y seguridad\n- Campanas de promocion de estilos de vida saludables\n\n**Deteccion y Apoyo:**\n- Examenes medicos ocupacionales segun perfiles de cargo y riesgo\n- Canales confidenciales para reportar situaciones de consumo\n- Remision a programas de apoyo psicosocial\n\n**Ambiente Saludable:**\n- Senalizacion de zonas libres de humo\n- Areas designadas para fumadores (si aplica, en exteriores)\n- Promocion de actividades de bienestar y deporte\n\n**Seguimiento:**\n- Evaluacion periodica del programa de prevencion\n- Indicadores de efectividad de las acciones",

            'sanciones' => "El incumplimiento de esta politica dara lugar a las siguientes acciones, respetando siempre el debido proceso:\n\n**Procedimiento:**\n1. Verificacion objetiva de la situacion\n2. Notificacion al trabajador y garantia del derecho de defensa\n3. Aplicacion de medidas segun gravedad y reincidencia\n\n**Medidas Disciplinarias:**\n- **Primera vez:** Llamado de atencion escrito y remision a programa de apoyo\n- **Reincidencia:** Suspension segun lo establecido en el Reglamento Interno de Trabajo\n- **Falta grave o tercera reincidencia:** Terminacion del contrato con justa causa (Art. 60 CST)\n\n**Apoyo al Trabajador:**\nLa empresa ofrecera orientacion y apoyo para acceder a programas de rehabilitacion, conforme a la Ley 1566 de 2012.\n\n_Las medidas se aplicaran sin discriminacion, respetando la dignidad humana y el debido proceso._",

            'marco_legal' => "La presente politica se fundamenta en la siguiente normatividad:\n\n- **Constitucion Politica de Colombia:** Articulo 49 sobre atencion en salud.\n\n- **Ley 1566 de 2012:** Normas para garantizar atencion integral a personas con consumo de SPA.\n\n- **Resolucion 1075 de 1992:** Obligacion de implementar programas de prevencion del alcoholismo y farmacodependencia.\n\n- **Ley 1335 de 2009:** Disposiciones sobre prevencion del consumo de tabaco y espacios libres de humo.\n\n- **Decreto 1072 de 2015:** Obligaciones del empleador en Seguridad y Salud en el Trabajo.\n\n- **Resolucion 0312 de 2019:** Estandares Minimos del Sistema de Gestion de SST.\n\n- **Codigo Sustantivo del Trabajo:** Articulo 60 sobre prohibiciones a los trabajadores.",

            'comunicacion' => "La Politica de Prevencion del Consumo de Alcohol, Tabaco y SPA sera:\n\n1. **Comunicada al {$comite}** para su conocimiento y apoyo en la difusion.\n\n2. **Publicada** en lugares visibles de las instalaciones.\n\n3. **Incluida** en el proceso de induccion y reinduccion de todos los trabajadores.\n\n4. **Socializada** a contratistas y visitantes.\n\n5. **Reforzada** mediante capacitaciones de sensibilizacion al menos una vez al ano.\n\n6. **Difundida** junto con informacion de lineas de ayuda y apoyo (linea 106, EPS, ARL).\n\n7. **Revisada anualmente** y actualizada cuando sea necesario.\n\n_Todos los trabajadores tienen la responsabilidad de conocer y cumplir esta politica, asi como de reportar situaciones que pongan en riesgo la seguridad._"
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
