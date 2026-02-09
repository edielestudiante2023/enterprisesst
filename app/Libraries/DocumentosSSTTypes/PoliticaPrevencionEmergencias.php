<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase PoliticaPrevencionEmergencias
 *
 * Implementa la generación de la Política de Prevención y Respuesta ante Emergencias
 * Numeral 2.1.1 de la Resolución 0312/2019
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class PoliticaPrevencionEmergencias extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'politica_prevencion_emergencias';
    }

    public function getNombre(): string
    {
        return 'Política de Prevención y Respuesta ante Emergencias';
    }

    public function getDescripcion(): string
    {
        return 'Política que establece el compromiso de la empresa con la prevención, preparación y respuesta ante situaciones de emergencia';
    }

    public function getEstandar(): ?string
    {
        return '2.1.1';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Declaración de la Política', 'key' => 'declaracion'],
            ['numero' => 4, 'nombre' => 'Compromisos de la Dirección', 'key' => 'compromisos_direccion'],
            ['numero' => 5, 'nombre' => 'Tipos de Emergencias Contempladas', 'key' => 'tipos_emergencias'],
            ['numero' => 6, 'nombre' => 'Organización para Emergencias', 'key' => 'organizacion_emergencias'],
            ['numero' => 7, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
            ['numero' => 8, 'nombre' => 'Comunicación y Divulgación', 'key' => 'comunicacion'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['consultor_sst', 'representante_legal'];
    }

    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        $prompts = [
            'objetivo' => "Genera el objetivo de la Política de Prevención y Respuesta ante Emergencias.
Debe expresar el compromiso de la organización con:
- Prevención de emergencias mediante identificación de amenazas y vulnerabilidades
- Preparación de recursos humanos y técnicos para responder a emergencias
- Respuesta efectiva y coordinada ante situaciones de emergencia
- Recuperación y continuidad de operaciones post-emergencia
IMPORTANTE: Máximo 2-3 párrafos concisos.",

            'alcance' => "Define el alcance de la Política de Prevención y Respuesta ante Emergencias.
Debe especificar que aplica a:
- Todas las instalaciones de la empresa
- Todos los trabajadores, contratistas y visitantes
- Todas las actividades desarrolladas
- Emergencias internas y externas que puedan afectar la organización
Para empresas de {$estandares} estándares, ajusta el alcance proporcionalmente.",

            'declaracion' => "Genera la declaración formal de la Política de Prevención y Respuesta ante Emergencias.
Debe incluir el compromiso explícito de la alta dirección con:
- Identificación y análisis de amenazas y vulnerabilidades
- Implementación de medidas de prevención y mitigación
- Conformación y capacitación de brigadas de emergencia
- Dotación de equipos y recursos para atención de emergencias
- Realización de simulacros periódicos
- Coordinación con entidades de socorro externas
FORMATO: Redactar en primera persona plural.
TONO: Formal, comprometido, claro.",

            'compromisos_direccion' => "Genera los compromisos específicos de la dirección según Decreto 1072/2015 y Ley 1523/2012.
COMPROMISOS OBLIGATORIOS:
1. Asignar recursos para prevención y atención de emergencias
2. Identificar amenazas y analizar vulnerabilidad de la empresa
3. Establecer procedimientos operativos normalizados (PON) para emergencias
4. Conformar, capacitar y dotar brigadas de emergencia
5. Realizar simulacros al menos una vez al año
6. Garantizar sistemas de alarma y comunicación de emergencias
7. Mantener equipos de primera respuesta en condiciones óptimas
8. Coordinar con entidades de socorro (Bomberos, Cruz Roja, Defensa Civil)
Para {$estandares} estándares, prioriza los más relevantes.",

            'tipos_emergencias' => "Lista los tipos de emergencias contempladas en la política según el análisis de amenazas.
CATEGORÍAS:
**Naturales:**
- Sismos/Terremotos
- Inundaciones
- Tormentas eléctricas
- Vendavales

**Tecnológicas:**
- Incendios
- Explosiones
- Derrames de sustancias peligrosas
- Fallas estructurales
- Fallas eléctricas

**Sociales:**
- Atentados terroristas
- Robos/Asaltos
- Manifestaciones/Disturbios

IMPORTANTE: Personaliza según los peligros identificados y la ubicación geográfica del cliente. No incluyas amenazas que no apliquen a su contexto.",

            'organizacion_emergencias' => "Describe la estructura organizacional para atención de emergencias.
INCLUIR:
- Coordinador de emergencias (generalmente Responsable SST o Gerente)
- Brigada de emergencias y sus grupos (evacuación, primeros auxilios, control de incendios)
- Comité de emergencias (si aplica según tamaño)
- Funciones básicas de cada rol

Para empresas de 7 estándares: estructura simple con coordinador y brigada básica.
Para empresas de 21-60 estándares: estructura más completa con grupos especializados.

IMPORTANTE: Si el contexto indica que tiene_brigada_emergencias = 0, menciona que debe conformarse.",

            'marco_legal' => "Lista el marco normativo aplicable a la Política de Emergencias.
NORMATIVA ESENCIAL:
- Ley 9 de 1979: Código Sanitario Nacional (Art. 93, 114, 116)
- Ley 1523 de 2012: Política Nacional de Gestión del Riesgo de Desastres
- Decreto 1072 de 2015: Art. 2.2.4.6.25 (Prevención, preparación y respuesta)
- Resolución 0312 de 2019: Estándar 5.1.1
- Resolución 2400 de 1979: Disposiciones sobre vivienda, higiene y seguridad
- NFPA (Asociación Nacional de Protección contra el Fuego) - referencia técnica

CANTIDAD según estándares:
- 7 estándares: MÁXIMO 5 normas
- 21 estándares: MÁXIMO 7 normas
- 60 estándares: Hasta 10 normas
FORMATO: Lista con viñetas, NO usar tablas.",

            'comunicacion' => "Define cómo se comunicará y divulgará la política de emergencias.
INCLUIR:
- Comunicación al COPASST/Vigía SST (según estándares del cliente)
- Publicación del Plan de Emergencias en lugares visibles
- Señalización de rutas de evacuación y puntos de encuentro
- Capacitación a todos los trabajadores en procedimientos de evacuación
- Difusión del sistema de alarma y su significado
- Simulacros de evacuación (mínimo 1 al año)
- Comunicación a visitantes y contratistas

IMPORTANTE: Para {$estandares} estándares, usar 'Vigía SST' si son 7 estándares, 'COPASST' si son 21 o más."
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la sección '{$seccionKey}' de la Política de Prevención y Respuesta ante Emergencias.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'LA EMPRESA';
        $ciudad = $cliente['ciudad_cliente'] ?? $contexto['ciudad'] ?? 'la ciudad';
        $comite = $this->getTextoComite($estandares);
        $tieneBrigada = $contexto['tiene_brigada_emergencias'] ?? 0;

        $contenidos = [
            'objetivo' => "La presente política tiene como objetivo establecer el compromiso de {$nombreEmpresa} con la prevención, preparación y respuesta efectiva ante situaciones de emergencia que puedan afectar la vida, la salud de las personas, el medio ambiente y los bienes de la organización.\n\nEsta política busca garantizar que la empresa cuente con los recursos humanos capacitados, equipos adecuados y procedimientos definidos para actuar de manera coordinada ante cualquier evento de emergencia, minimizando sus consecuencias.",

            'alcance' => "La Política de Prevención y Respuesta ante Emergencias de {$nombreEmpresa} aplica a:\n\n- Todas las instalaciones y sedes de la empresa\n- Todos los trabajadores, independientemente de su forma de contratación\n- Contratistas, subcontratistas y proveedores\n- Visitantes y cualquier persona que se encuentre en las instalaciones\n- Todas las actividades desarrolladas dentro y fuera de las instalaciones\n\nConsidera tanto emergencias de origen interno como externo que puedan afectar las operaciones de la empresa.",

            'declaracion' => "{$nombreEmpresa}, consciente de su responsabilidad con la seguridad y salud de todas las personas que se encuentran en sus instalaciones, declara su compromiso con:\n\n**Prevención:** Identificar las amenazas y analizar la vulnerabilidad de la organización para establecer medidas que reduzcan la probabilidad de ocurrencia de emergencias.\n\n**Preparación:** Conformar y capacitar brigadas de emergencia, dotar los equipos necesarios y establecer procedimientos claros de actuación.\n\n**Respuesta:** Actuar de manera coordinada y efectiva ante cualquier situación de emergencia, priorizando la protección de la vida humana.\n\n**Recuperación:** Implementar acciones que permitan el retorno a la normalidad en el menor tiempo posible.\n\nEsta política será revisada anualmente y actualizada cuando cambien las condiciones de riesgo de la organización.",

            'compromisos_direccion' => "La Dirección de {$nombreEmpresa} se compromete a:\n\n1. **Asignar los recursos** necesarios para la implementación del Plan de Prevención, Preparación y Respuesta ante Emergencias.\n\n2. **Identificar y analizar** las amenazas y vulnerabilidades que puedan generar emergencias.\n\n3. **Conformar y mantener** la brigada de emergencias debidamente capacitada y dotada.\n\n4. **Garantizar los equipos** de detección, alarma y control de emergencias en óptimas condiciones.\n\n5. **Realizar simulacros** de evacuación al menos una vez al año.\n\n6. **Coordinar** con las entidades de socorro externas (Bomberos, Cruz Roja, Defensa Civil).\n\n7. **Socializar** el Plan de Emergencias a todos los trabajadores y visitantes.",

            'tipos_emergencias' => "La presente política contempla los siguientes tipos de emergencias:\n\n**Emergencias Naturales:**\n- Sismos y terremotos\n- Inundaciones\n- Tormentas eléctricas\n- Vendavales\n\n**Emergencias Tecnológicas:**\n- Incendios\n- Explosiones\n- Derrames de sustancias (si aplica)\n- Fallas estructurales\n- Fallas en sistemas eléctricos\n\n**Emergencias Sociales:**\n- Amenazas de bomba\n- Robos o asaltos\n- Disturbios civiles\n\n_Los procedimientos específicos para cada tipo de emergencia se encuentran detallados en el Plan de Emergencias de la empresa._",

            'organizacion_emergencias' => "Para la atención de emergencias, {$nombreEmpresa} cuenta con la siguiente estructura:\n\n**Coordinador de Emergencias:**\n- Responsable del SG-SST o persona designada por la Gerencia\n- Dirige y coordina las acciones durante la emergencia\n\n**Brigada de Emergencias:**\n" . ($tieneBrigada ? "- Conformada y capacitada según lo establecido en el Plan de Emergencias" : "- Pendiente de conformación según los requisitos del SG-SST") . "\n- Grupos: Evacuación, Primeros Auxilios, Control de Incendios\n\n**Funciones generales:**\n- Activar la alarma de emergencia\n- Coordinar la evacuación del personal\n- Prestar primeros auxilios si es necesario\n- Controlar conatos de incendio\n- Coordinar con entidades de socorro externas\n\n**{$comite}:**\n- Participar en la revisión del Plan de Emergencias\n- Apoyar en la realización de simulacros",

            'marco_legal' => "La presente política se fundamenta en la siguiente normatividad:\n\n- **Ley 9 de 1979:** Código Sanitario Nacional, artículos 93, 114 y 116 sobre prevención y control de emergencias.\n\n- **Ley 1523 de 2012:** Política Nacional de Gestión del Riesgo de Desastres.\n\n- **Decreto 1072 de 2015:** Artículo 2.2.4.6.25 sobre prevención, preparación y respuesta ante emergencias.\n\n- **Resolución 0312 de 2019:** Estándar 5.1.1 sobre Plan de Prevención, Preparación y Respuesta ante Emergencias.\n\n- **Resolución 2400 de 1979:** Disposiciones sobre vivienda, higiene y seguridad en establecimientos de trabajo.",

            'comunicacion' => "La Política de Prevención y Respuesta ante Emergencias será:\n\n1. **Comunicada al {$comite}** para su conocimiento y apoyo en la difusión.\n\n2. **Socializada** a todos los trabajadores mediante capacitación y entrenamiento.\n\n3. **Publicada** junto con el Plan de Emergencias en lugares visibles.\n\n4. **Incluida** en el proceso de inducción de nuevos trabajadores.\n\n5. **Informada** a contratistas y visitantes al ingresar a las instalaciones.\n\n6. **Validada** mediante la realización de simulacros de evacuación.\n\n7. **Revisada anualmente** o cuando ocurran cambios significativos en las condiciones de riesgo.\n\n_Todos los trabajadores tienen la responsabilidad de conocer las rutas de evacuación, los puntos de encuentro y los procedimientos de emergencia._"
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
