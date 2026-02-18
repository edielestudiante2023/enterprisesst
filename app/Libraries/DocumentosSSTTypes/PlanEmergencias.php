<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase PlanEmergencias
 *
 * Plan de Prevención, Preparación y Respuesta ante Emergencias - Estándar 5.1.1
 * Basado en Resolución 0312/2019 estándar 5.1.1, Decreto 1072/2015 art. 2.2.4.6.25,
 * Resolución 256/2014 (Brigadas), GTC 45 (análisis de vulnerabilidad).
 *
 * Tipo A (secciones_ia): IA genera contenido usando solo contexto del cliente.
 *
 * @package App\Libraries\DocumentosSSTTypes
 */
class PlanEmergencias extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'plan_emergencias';
    }

    public function getNombre(): string
    {
        return 'Plan de Prevención, Preparación y Respuesta ante Emergencias';
    }

    public function getDescripcion(): string
    {
        return 'Documento que define las acciones de prevención, preparación y respuesta para atender situaciones de emergencia en la empresa. Incluye identificación de amenazas, análisis de vulnerabilidad, organización de brigadas, procedimientos por tipo de emergencia y plan de evacuación. Cumplimiento del estándar 5.1.1 de la Resolución 0312/2019.';
    }

    public function getEstandar(): ?string
    {
        return '5.1.1';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1,  'nombre' => 'Objetivo y Alcance',                    'key' => 'objetivo_alcance'],
            ['numero' => 2,  'nombre' => 'Marco Legal',                            'key' => 'marco_legal'],
            ['numero' => 3,  'nombre' => 'Definiciones',                           'key' => 'definiciones'],
            ['numero' => 4,  'nombre' => 'Identificación de Amenazas',             'key' => 'identificacion_amenazas'],
            ['numero' => 5,  'nombre' => 'Análisis de Vulnerabilidad',             'key' => 'analisis_vulnerabilidad'],
            ['numero' => 6,  'nombre' => 'Organización para Emergencias (Brigadas)', 'key' => 'organizacion_brigadas'],
            ['numero' => 7,  'nombre' => 'Procedimientos de Emergencia',           'key' => 'procedimientos_emergencia'],
            ['numero' => 8,  'nombre' => 'Plan de Evacuación',                     'key' => 'plan_evacuacion'],
            ['numero' => 9,  'nombre' => 'Comunicaciones de Emergencia',           'key' => 'comunicaciones_emergencia'],
            ['numero' => 10, 'nombre' => 'Equipos y Recursos',                     'key' => 'equipos_recursos'],
            ['numero' => 11, 'nombre' => 'Capacitación y Simulacros',             'key' => 'capacitacion_simulacros'],
            ['numero' => 12, 'nombre' => 'Investigación Post-Emergencia',          'key' => 'investigacion_post_emergencia'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['responsable_sst', 'representante_legal'];
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo_alcance' => "Objetivo general: Establecer las acciones de prevención, preparación y respuesta ante emergencias en {$nombreEmpresa}, garantizando la protección de la vida, la integridad física de los trabajadores y la continuidad de las operaciones.\n\nAlcance: Aplica a todas las instalaciones de la empresa, trabajadores directos, contratistas, visitantes y cualquier persona que se encuentre en las áreas de trabajo.",

            'marco_legal' => "Normativa aplicable:\n- Resolución 0312 de 2019: Estándar 5.1.1 (Plan de prevención, preparación y respuesta ante emergencias)\n- Decreto 1072 de 2015: art. 2.2.4.6.25 (Prevención, preparación y respuesta ante emergencias)\n- Ley 1523 de 2012: Política Nacional de Gestión del Riesgo de Desastres\n- Resolución 256 de 2014: Brigadas de emergencias\n- GTC 45: Identificación de peligros y análisis de vulnerabilidad\n- NTC 1410, NTC 3807: Señalización de emergencias\n- Decreto 332 de 2004: Organización del CREPAD",

            'definiciones' => "Amenaza: Peligro latente de un fenómeno con potencial de producir daño.\nVulnerabilidad: Susceptibilidad de sufrir daño ante la presencia de una amenaza.\nEmergencia: Situación que pone en peligro la vida de las personas o el patrimonio.\nBrigada de emergencias: Grupo organizado de trabajadores para atender emergencias.\nEvacuación: Desalojo ordenado de personas de una zona de peligro.\nPunto de encuentro: Lugar seguro fuera del edificio para reunión post-evacuación.\nSimulacro: Ejercicio práctico de los planes de emergencia.\nRespuesta: Acciones llevadas a cabo ante la ocurrencia de una emergencia.",

            'identificacion_amenazas' => "Se identifican las siguientes amenazas en {$nombreEmpresa}:\n\nAmenazas naturales:\n- Sismo/Terremoto: según zona sísmica del municipio\n- Vendaval/Lluvias fuertes: según región geográfica\n\nAmenazas antrópicas:\n- Incendio estructural: por cortocircuitos, materiales inflamables\n- Explosión: presencia de sustancias inflamables o recipientes a presión\n- Derrame de sustancias químicas: según actividad económica\n- Atentado/Amenaza bomba: contexto de seguridad del entorno\n\nAmenazas tecnológicas:\n- Falla de equipos críticos\n- Interrupción de servicios públicos",

            'analisis_vulnerabilidad' => "El análisis de vulnerabilidad evalúa tres elementos:\n\n1. Personas (trabajadores, visitantes): nivel de capacitación, cantidad en el área, condición física\n2. Recursos (materiales, equipos, edificación): estado de la infraestructura, sistemas de protección, materiales de riesgo\n3. Sistemas y procesos (organización, servicios): procedimientos documentados, sistemas de comunicación, redes de apoyo externo\n\nCalificación: Alta (riesgo inaceptable), Media (riesgo tolerable con medidas), Baja (riesgo aceptable)\n\nPrioridad de intervención según resultado del análisis.",

            'organizacion_brigadas' => "La Brigada de Emergencias de {$nombreEmpresa} está conformada por:\n\nCoordinador de emergencias: Responsable de activar el Plan y coordinar la respuesta.\n\nBrigadistas por área:\n- Brigadista contra incendios: Manejo de extintores, evacuación\n- Brigadista de primeros auxilios: Atención básica pre-hospitalaria\n- Brigadista de evacuación y rescate: Guía de evacuación, conteo de personas\n\nLíneas de mando:\n1. Coordinador General de Emergencias\n2. Jefes de Brigada por especialidad\n3. Brigadistas de apoyo\n\nEl {$comite} apoya la verificación del cumplimiento del plan.",

            'procedimientos_emergencia' => "Procedimiento general ante cualquier emergencia:\n1. DETECTAR: Identificar la emergencia y activar la alarma\n2. ALERTAR: Comunicar a todos mediante el sistema de alarma\n3. EVALUAR: El coordinador evalúa la magnitud\n4. ACTUAR: Aplicar el procedimiento específico según tipo\n5. EVACUAR: Si es necesario, desalojar de forma ordenada\n6. AYUDAR: Llamar a servicios de emergencia externos (Bomberos 119, Policía 123, Cruz Roja 132)\n7. RECUPERAR: Evaluación de daños y retorno seguro\n\nProcedimientos específicos:\n- Ante incendio: No usar ascensores, cerrar puertas, evacuar por rutas señalizadas\n- Ante sismo: Alejarse de ventanas, cubrirse bajo escritorios resistentes\n- Ante derrame: Aislar área, ventilar, notificar a brigadista\n- Ante amenaza bomba: Evacuar sin tocar objetos sospechosos",

            'plan_evacuacion' => "Plan de Evacuación de {$nombreEmpresa}:\n\nRutas de evacuación:\n- Señalizadas con flechas fluorescentes y letreros de SALIDA\n- Libres de obstáculos en todo momento\n- Iluminación de emergencia operativa\n\nPuntos de encuentro:\n- Principal: [Definir por la empresa según su ubicación]\n- Alterno: [Definir por la empresa]\n\nProcedimiento de evacuación:\n1. Al activarse la alarma, suspender actividades\n2. Tomar objetos personales mínimos (sin equipos)\n3. Seguir la ruta de evacuación señalizada\n4. Brigadistas guían y cuentan personas\n5. Reunirse en el punto de encuentro\n6. Coordinador verifica lista de personal\n7. Esperar instrucciones del coordinador\n\nTiempo objetivo de evacuación: Máximo 5 minutos",

            'comunicaciones_emergencia' => "Sistema de comunicaciones ante emergencias:\n\nAlarma interna:\n- Señal sonora (sirena/campana) para alertar a todo el personal\n- Protocolo de activación y tipos de señal por emergencia\n\nDirectorio de emergencias:\n- Bomberos: 119\n- Policía: 123\n- Cruz Roja: 132\n- Ambulancia/CRUE: 125\n- ARL de la empresa: [Número de la ARL]\n- EPS de los trabajadores: [Número de la EPS]\n\nComunicación post-emergencia:\n- Coordinador reporta a gerencia y ARL en las primeras 24 horas\n- Reporte a Ministerio del Trabajo si hay accidente grave",

            'equipos_recursos' => "Equipos mínimos requeridos para atención de emergencias:\n\nEquipos contra incendios:\n- Extintores tipo ABC (uno por cada 200 m²)\n- Inspección mensual y recarga anual\n\nKit de primeros auxilios:\n- Botiquín completo por piso/área\n- Revisión mensual del contenido\n\nElementos de evacuación:\n- Camilla rígida\n- Linterna de emergencia\n- Silbatos para brigadistas\n- Chalecos identificadores de brigadistas\n\nSeñalización:\n- Señales de rutas de evacuación\n- Señales de salidas de emergencia\n- Señales de puntos de encuentro\n- Señales de ubicación de equipos",

            'capacitacion_simulacros' => "Plan de capacitación y simulacros:\n\nCapacitaciones:\n- Inducción a nuevos trabajadores: Conocimiento del plan de emergencias\n- Formación de brigadistas: 16 horas mínimo según Resolución 256/2014\n- Actualización anual para todo el personal\n- Primeros auxilios básicos: Para todos los trabajadores\n\nSimulacros:\n- Mínimo un simulacro general al año (por norma)\n- Recomendado: Simulacro parcial semestral\n- Evaluación post-simulacro: tiempo de evacuación, cumplimiento de rutas, conteo de personal\n- Registro de simulacros y lecciones aprendidas\n\nIndicadores:\n- % de brigadistas capacitados meta = 100%\n- Tiempo de evacuación meta ≤ 5 minutos\n- % de extintores en buen estado meta = 100%",

            'investigacion_post_emergencia' => "Procedimiento de investigación y seguimiento post-emergencia:\n\n1. Evaluación inmediata (primeras 24 horas):\n   - Verificar estado de salud de todos los trabajadores\n   - Evaluar daños materiales a instalaciones y equipos\n   - Asegurar el área afectada\n\n2. Investigación (dentro de los 8 días):\n   - Determinar causa raíz de la emergencia\n   - Identificar fallas en el plan de respuesta\n   - Documentar la emergencia (formulario de reporte)\n\n3. Acciones correctivas:\n   - Plan de mejora del sistema de respuesta\n   - Actualización del plan de emergencias si aplica\n   - Reposición de equipos utilizados\n\n4. Reporte a entidades:\n   - A la ARL (accidentes de trabajo/enfermedades)\n   - Al Ministerio del Trabajo (accidentes graves)\n   - A la gerencia (informe ejecutivo)"
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
