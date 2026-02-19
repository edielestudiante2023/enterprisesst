<?php

namespace App\Services;

use App\Models\CronogcapacitacionModel;
use App\Models\PtaclienteModel;
use App\Models\ClienteContextoSstModel;
/**
 * Servicio para generar Plan de Trabajo Anual (PTA)
 * Usa IA con contexto completo del cliente para generar actividades base personalizadas
 */
class PTAGeneratorService
{
    protected CronogcapacitacionModel $cronogramaModel;
    protected PtaclienteModel $ptaModel;

    /**
     * Tipos de servicio/documento para el PTA (mapeo de tipos, NO contenido generado)
     */
    public const TIPOS_SERVICIO = [
        'PROGRAMA_CAPACITACION'  => 'Programa de Capacitacion',
        'PROGRAMA_MANTENIMIENTO' => 'Programa de Mantenimiento',
        'PLAN_EMERGENCIAS'       => 'Plan de Emergencias',
        'PROGRAMA_VIGILANCIA'    => 'Programa de Vigilancia Epidemiologica',
        'PROGRAMA_RIESGO_PSICO'  => 'Programa Riesgo Psicosocial',
        'PLAN_TRABAJO_ANUAL'     => 'Plan de Trabajo Anual',
        'GESTION_SST'            => 'Gestion SG-SST',
    ];

    /**
     * Mapeo de tipos de actividad a numerales de la Res. 0312/2019 (referencia legal, NO contenido)
     */
    public const NUMERALES_ACTIVIDAD = [
        'capacitacion' => '2.11.1',
        'induccion' => '1.1.1',
        'copasst' => '1.1.3',
        'comite_convivencia' => '1.1.4',
        'brigada' => '5.1.1',
        'simulacro' => '5.1.2'
    ];

    /**
     * Cantidad de actividades base segun nivel de estandares
     */
    protected const CANTIDAD_ACTIVIDADES = [
        7  => 7,
        21 => 21,
        60 => 50
    ];

    public function __construct()
    {
        $this->cronogramaModel = new CronogcapacitacionModel();
        $this->ptaModel = new PtaclienteModel();
    }

    /**
     * Genera el PTA a partir del cronograma de capacitaciones
     * ESCRIBE a tbl_pta_cliente - NO modificar la logica de insercion
     */
    public function generarDesdeCronograma(int $idCliente, ?int $anio = null, ?string $tipoServicio = null): array
    {
        $anio = $anio ?? (int)date('Y');
        $tipoServicio = $tipoServicio ?? self::TIPOS_SERVICIO['PROGRAMA_CAPACITACION'];

        $cronogramas = $this->cronogramaModel
            ->select('tbl_cronog_capacitacion.*, capacitaciones_sst.capacitacion, capacitaciones_sst.objetivo_capacitacion')
            ->join('capacitaciones_sst', 'capacitaciones_sst.id_capacitacion = tbl_cronog_capacitacion.id_capacitacion', 'left')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_programada)', $anio)
            ->orderBy('fecha_programada', 'ASC')
            ->findAll();

        $resultado = [
            'anio' => $anio,
            'cliente_id' => $idCliente,
            'tipo_servicio' => $tipoServicio,
            'actividades_creadas' => 0,
            'actividades_existentes' => 0,
            'actividades' => []
        ];

        foreach ($cronogramas as $cron) {
            $numeral = $this->determinarNumeral($cron['capacitacion']);

            $existeActividad = $this->verificarExistenciaActividad(
                $idCliente,
                $cron['capacitacion'],
                $cron['fecha_programada']
            );

            if ($existeActividad) {
                $resultado['actividades_existentes']++;
                $resultado['actividades'][] = [
                    'actividad' => $cron['capacitacion'],
                    'estado' => 'existente'
                ];
                continue;
            }

            $semana = (int)date('W', strtotime($cron['fecha_programada']));

            $datosActividad = [
                'id_cliente' => $idCliente,
                'tipo_servicio' => $tipoServicio,
                'phva_plandetrabajo' => 'HACER',
                'numeral_plandetrabajo' => $numeral,
                'actividad_plandetrabajo' => 'Capacitación: ' . $cron['capacitacion'],
                'responsable_sugerido_plandetrabajo' => 'Responsable SST',
                'fecha_propuesta' => $cron['fecha_programada'],
                'estado_actividad' => 'ABIERTA',
                'porcentaje_avance' => 0,
                'semana' => $semana,
                'observaciones' => $cron['objetivo_capacitacion'] ?? ''
            ];

            $this->ptaModel->insert($datosActividad);
            $resultado['actividades_creadas']++;

            $resultado['actividades'][] = [
                'actividad' => $cron['capacitacion'],
                'fecha' => $cron['fecha_programada'],
                'numeral' => $numeral,
                'estado' => 'creada'
            ];
        }

        return $resultado;
    }

    /**
     * Genera PTA completo: actividades base (IA) + capacitaciones (cronograma)
     * ESCRIBE a tbl_pta_cliente - la logica de insercion es identica al original
     *
     * @param array|null $actividadesBase Si se proveen, se usan directamente (del preview). Si null, genera con IA.
     */
    public function generarPTACompleto(int $idCliente, int $anio = null, ?array $actividadesBase = null): array
    {
        $anio = $anio ?? (int)date('Y');

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $resultado = [
            'anio' => $anio,
            'cliente_id' => $idCliente,
            'estandares' => $estandares,
            'actividades_base_creadas' => 0,
            'actividades_capacitacion_creadas' => 0
        ];

        // 1. Obtener actividades base: usar las provistas o generar con IA
        if ($actividadesBase === null) {
            $actividadesBase = $this->generarActividadesBaseConIA($idCliente, $estandares, $anio);
        }

        // 2. Insertar actividades base en tbl_pta_cliente
        foreach ($actividadesBase as $index => $act) {
            $mes = min(12, (int)ceil(($index + 1) * 12 / max(1, count($actividadesBase))));
            $fechaPropuesta = "{$anio}-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-15";

            $existe = $this->verificarExistenciaActividad($idCliente, $act['actividad'], $fechaPropuesta);

            if (!$existe) {
                $semana = (int)date('W', strtotime($fechaPropuesta));

                $this->ptaModel->insert([
                    'id_cliente' => $idCliente,
                    'tipo_servicio' => self::TIPOS_SERVICIO['PLAN_TRABAJO_ANUAL'],
                    'phva_plandetrabajo' => $act['phva'],
                    'numeral_plandetrabajo' => $act['numeral'],
                    'actividad_plandetrabajo' => $act['actividad'],
                    'responsable_sugerido_plandetrabajo' => $act['responsable'],
                    'fecha_propuesta' => $fechaPropuesta,
                    'estado_actividad' => 'ABIERTA',
                    'porcentaje_avance' => 0,
                    'semana' => $semana
                ]);

                $resultado['actividades_base_creadas']++;
            }
        }

        // 3. Agregar actividades desde el cronograma de capacitaciones
        $resultadoCronograma = $this->generarDesdeCronograma($idCliente, $anio, self::TIPOS_SERVICIO['PROGRAMA_CAPACITACION']);
        $resultado['actividades_capacitacion_creadas'] = $resultadoCronograma['actividades_creadas'];

        $resultado['total_creadas'] = $resultado['actividades_base_creadas'] + $resultado['actividades_capacitacion_creadas'];

        return $resultado;
    }

    /**
     * Genera actividades base del PTA usando IA con contexto completo del cliente
     */
    protected function generarActividadesBaseConIA(int $idCliente, int $estandares, int $anio): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            log_message('error', 'PTAGenerator: OPENAI_API_KEY no configurada');
            return $this->generarActividadesBaseFallback($estandares);
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $objetivosService = new \App\Services\ObjetivosSgsstService();
        $contextoTexto = $objetivosService->construirContextoCompleto($contexto, $idCliente);

        $nivel = $estandares <= 7 ? 7 : ($estandares <= 21 ? 21 : 60);
        $cantidad = self::CANTIDAD_ACTIVIDADES[$nivel] ?? 7;

        $systemPrompt = $this->construirSystemPromptPTA();
        $userPrompt = $this->construirUserPromptPTA($contextoTexto, $estandares, $nivel, $cantidad, $anio);

        try {
            $respuesta = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey);
            if ($respuesta) {
                $actividades = $this->procesarRespuestaIA($respuesta, $cantidad);
                if ($actividades) {
                    log_message('info', "PTAGenerator: {$cantidad} actividades generadas con IA para cliente {$idCliente}");
                    return $actividades;
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'PTAGenerator: Error IA: ' . $e->getMessage());
        }

        log_message('warning', "PTAGenerator: Usando fallback para cliente {$idCliente}");
        return $this->generarActividadesBaseFallback($estandares);
    }

    /**
     * System prompt para generar actividades del PTA
     */
    protected function construirSystemPromptPTA(): string
    {
        return <<<'PROMPT'
Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia, especializado en diseñar Planes de Trabajo Anual conforme a la Resolucion 0312 de 2019.

Tu tarea es generar las actividades base del Plan de Trabajo Anual (PTA) para una empresa especifica. Cada actividad debe ser PERSONALIZADA segun la actividad economica, peligros identificados, nivel de riesgo y contexto de la empresa.

REGLAS:
1. Cada actividad DEBE tener un numeral valido de la Resolucion 0312 de 2019.
2. La distribucion PHVA debe ser equilibrada: minimo 25% PLANEAR, 40% HACER, 15% VERIFICAR, 10% ACTUAR.
3. Si hay peligros identificados, incluir actividades especificas para controlarlos.
4. Si hay observaciones del consultor, integrar esas necesidades.
5. Las actividades deben ser concretas y medibles, NO genericas.
6. El responsable debe ser apropiado: Alta Direccion para decisiones estrategicas, Responsable SST para operativas, COPASST/Vigia para verificacion.
7. Incluir siempre: induccion/reinduccion, capacitacion, identificacion de peligros, evaluaciones medicas, investigacion de incidentes.
8. Para niveles 21 y 60: incluir comites, programas de vigilancia, gestion del cambio, auditorias.
9. Los numerales deben corresponder exactamente a la Resolucion 0312/2019 (ej: 1.1.1, 2.4.1, 3.1.4, 4.2.1, etc.)
10. NO inventes numerales. Usa solo los de la Resolucion 0312/2019.

NUMERALES VALIDOS PRINCIPALES (Resolucion 0312/2019):
- 1.1.1 a 1.1.8: Recursos (asignacion, afiliaciones, conformacion comites)
- 1.2.1 a 1.2.3: Capacitacion en SST
- 2.1.1: Politica de SST
- 2.2.1: Objetivos del SG-SST
- 2.3.1: Evaluacion inicial
- 2.4.1: Plan de trabajo anual
- 2.5.1: Archivo y retencion documental
- 2.6.1: Rendicion de cuentas
- 2.7.1: Matriz legal
- 2.8.1: Mecanismos de comunicacion
- 2.9.1: Adquisiciones y contratacion
- 2.10.1: Gestion del cambio
- 2.11.1: Descripcion sociodemografica y diagnostico de condiciones de salud
- 3.1.1 a 3.1.9: Condiciones de salud (evaluaciones medicas, PVEs, perfiles de cargo)
- 3.2.1 a 3.2.3: Registro e investigacion de incidentes y accidentes
- 3.3.1 a 3.3.6: Mecanismos de vigilancia para condiciones de salud
- 4.1.1 a 4.1.4: Identificacion de peligros, evaluacion y valoracion de riesgos
- 4.2.1 a 4.2.6: Medidas de prevencion y control
- 5.1.1 a 5.1.2: Plan de prevencion, preparacion y respuesta ante emergencias
- 6.1.1 a 6.1.4: Indicadores del SG-SST
- 7.1.1 a 7.1.4: Auditoria, revision por la alta direccion, acciones correctivas

FORMATO DE RESPUESTA:
Responde UNICAMENTE con un JSON valido (sin markdown, sin ```json```) con esta estructura:

{
  "actividades": [
    {
      "numeral": "1.1.1",
      "actividad": "Descripcion concreta de la actividad personalizada",
      "phva": "PLANEAR",
      "responsable": "Responsable apropiado"
    }
  ]
}
PROMPT;
    }

    /**
     * Construye el user prompt con contexto del cliente y parametros del PTA
     */
    protected function construirUserPromptPTA(
        string $contextoTexto,
        int $estandares,
        int $nivel,
        int $cantidad,
        int $anio
    ): string {
        $nivelDesc = $nivel <= 7 ? 'BASICO (hasta 10 trabajadores, riesgo I, II o III)' :
                    ($nivel <= 21 ? 'INTERMEDIO (11 a 50 trabajadores, riesgo I, II o III)' :
                    'AVANZADO (mas de 50 trabajadores o riesgo IV y V)');

        $prompt = "AÑO DEL PTA: {$anio}\n";
        $prompt .= "NIVEL DE ESTANDARES: {$nivelDesc} ({$estandares} estandares)\n";
        $prompt .= "CANTIDAD DE ACTIVIDADES A GENERAR: exactamente {$cantidad}\n\n";
        $prompt .= $contextoTexto;
        $prompt .= "\n\nGenera exactamente {$cantidad} actividades del Plan de Trabajo Anual personalizadas para esta empresa. ";
        $prompt .= "Las actividades deben reflejar los peligros, la actividad economica y el contexto especifico de la empresa. ";
        $prompt .= "Incluir distribucion PHVA equilibrada y numerales correctos de la Resolucion 0312/2019.";

        return $prompt;
    }

    /**
     * Llama a la API de OpenAI
     */
    protected function llamarOpenAI(string $systemPrompt, string $userPrompt, string $apiKey): ?string
    {
        $data = [
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 6000,
            'response_format' => ['type' => 'json_object']
        ];

        log_message('info', 'PTAGenerator: Llamando OpenAI para generar actividades PTA');

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_TIMEOUT => 90,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            log_message('error', 'PTAGenerator: cURL error: ' . $curlError);
            return null;
        }

        if ($httpCode !== 200) {
            log_message('error', 'PTAGenerator: HTTP ' . $httpCode . ' - ' . substr($response, 0, 500));
            return null;
        }

        $decoded = json_decode($response, true);
        $content = $decoded['choices'][0]['message']['content'] ?? null;

        if ($content) {
            $tokens = $decoded['usage']['total_tokens'] ?? 0;
            log_message('info', "PTAGenerator: Respuesta recibida ({$tokens} tokens)");
        }

        return $content;
    }

    /**
     * Procesa la respuesta JSON de OpenAI y valida la estructura de actividades
     */
    protected function procesarRespuestaIA(string $respuesta, int $cantidadEsperada): ?array
    {
        $data = json_decode($respuesta, true);
        if (!$data || empty($data['actividades'])) {
            log_message('error', 'PTAGenerator: JSON invalido o sin actividades: ' . substr($respuesta, 0, 200));
            return null;
        }

        $actividades = [];
        $camposRequeridos = ['numeral', 'actividad', 'phva', 'responsable'];
        $phvaValidos = ['PLANEAR', 'HACER', 'VERIFICAR', 'ACTUAR'];

        foreach ($data['actividades'] as $act) {
            // Validar campos requeridos
            $valida = true;
            foreach ($camposRequeridos as $campo) {
                if (empty($act[$campo])) {
                    $valida = false;
                    break;
                }
            }
            if (!$valida) continue;

            // Normalizar PHVA
            $phva = strtoupper(trim($act['phva']));
            if (!in_array($phva, $phvaValidos)) {
                $phva = 'HACER';
            }

            $actividades[] = [
                'numeral' => trim($act['numeral']),
                'actividad' => trim($act['actividad']),
                'phva' => $phva,
                'responsable' => trim($act['responsable'])
            ];
        }

        if (count($actividades) < 3) {
            log_message('error', 'PTAGenerator: Solo se obtuvieron ' . count($actividades) . ' actividades validas');
            return null;
        }

        return $actividades;
    }

    /**
     * Actividades base de fallback cuando la IA no esta disponible
     * Simplificadas por nivel de estandares
     */
    protected function generarActividadesBaseFallback(int $estandares): array
    {
        $nivel = $estandares <= 7 ? 7 : ($estandares <= 21 ? 21 : 60);

        $basicas = [
            ['numeral' => '1.1.1', 'actividad' => 'Asignacion de persona que diseña el SG-SST', 'phva' => 'PLANEAR', 'responsable' => 'Alta Direccion'],
            ['numeral' => '1.1.2', 'actividad' => 'Asignacion de responsabilidades en SST', 'phva' => 'PLANEAR', 'responsable' => 'Alta Direccion'],
            ['numeral' => '1.1.3', 'actividad' => 'Asignacion de recursos para el SG-SST', 'phva' => 'PLANEAR', 'responsable' => 'Alta Direccion'],
            ['numeral' => '1.1.4', 'actividad' => 'Afiliacion al Sistema de Seguridad Social', 'phva' => 'PLANEAR', 'responsable' => 'Recursos Humanos'],
            ['numeral' => '1.1.6', 'actividad' => 'Conformacion y funcionamiento del Vigia SST', 'phva' => 'PLANEAR', 'responsable' => 'Responsable SST'],
            ['numeral' => '1.1.7', 'actividad' => 'Capacitacion del Vigia SST', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
            ['numeral' => '1.2.1', 'actividad' => 'Programa de capacitacion anual en SST', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
        ];

        if ($nivel >= 21) {
            $basicas = array_merge($basicas, [
                ['numeral' => '1.1.5', 'actividad' => 'Identificacion de peligros y evaluacion de riesgos', 'phva' => 'PLANEAR', 'responsable' => 'Responsable SST'],
                ['numeral' => '1.1.8', 'actividad' => 'Conformacion del Comite de Convivencia Laboral', 'phva' => 'PLANEAR', 'responsable' => 'Alta Direccion'],
                ['numeral' => '1.2.2', 'actividad' => 'Induccion y reinduccion en SST', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '2.1.1', 'actividad' => 'Politica de SST documentada', 'phva' => 'PLANEAR', 'responsable' => 'Alta Direccion'],
                ['numeral' => '2.2.1', 'actividad' => 'Objetivos del SG-SST', 'phva' => 'PLANEAR', 'responsable' => 'Responsable SST'],
                ['numeral' => '2.3.1', 'actividad' => 'Evaluacion inicial del SG-SST', 'phva' => 'VERIFICAR', 'responsable' => 'Responsable SST'],
                ['numeral' => '2.4.1', 'actividad' => 'Plan de trabajo anual en SST', 'phva' => 'PLANEAR', 'responsable' => 'Responsable SST'],
                ['numeral' => '2.5.1', 'actividad' => 'Archivo y retencion documental del SG-SST', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '3.1.1', 'actividad' => 'Descripcion sociodemografica', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '3.1.2', 'actividad' => 'Actividades de medicina del trabajo', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '3.1.3', 'actividad' => 'Informacion al medico de los perfiles de cargo', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '3.1.4', 'actividad' => 'Realizacion de evaluaciones medicas', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '3.1.5', 'actividad' => 'Custodia de historias clinicas', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '3.1.6', 'actividad' => 'Restricciones y recomendaciones medicas', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
            ]);
        }

        if ($nivel >= 60) {
            $basicas = array_merge($basicas, [
                ['numeral' => '2.6.1', 'actividad' => 'Rendicion de cuentas sobre el desempeño', 'phva' => 'VERIFICAR', 'responsable' => 'Responsable SST'],
                ['numeral' => '2.7.1', 'actividad' => 'Actualizacion de la matriz legal', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '2.8.1', 'actividad' => 'Mecanismos de comunicacion interna y externa', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '2.9.1', 'actividad' => 'Procedimiento de adquisiciones y contratacion', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '2.10.1', 'actividad' => 'Evaluacion del impacto de cambios (gestion del cambio)', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '3.2.1', 'actividad' => 'Reporte e investigacion de accidentes de trabajo', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '3.2.2', 'actividad' => 'Investigacion de incidentes y enfermedades laborales', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '3.3.1', 'actividad' => 'Medicion de indicadores de ausentismo', 'phva' => 'VERIFICAR', 'responsable' => 'Responsable SST'],
                ['numeral' => '3.3.3', 'actividad' => 'Seguimiento a resultados de examenes medicos', 'phva' => 'VERIFICAR', 'responsable' => 'Responsable SST'],
                ['numeral' => '4.1.1', 'actividad' => 'Metodologia para identificacion de peligros', 'phva' => 'PLANEAR', 'responsable' => 'Responsable SST'],
                ['numeral' => '4.1.2', 'actividad' => 'Identificacion de peligros con participacion de los trabajadores', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '4.1.4', 'actividad' => 'Realizacion de mediciones ambientales', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '4.2.1', 'actividad' => 'Implementacion de medidas de prevencion y control', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '4.2.5', 'actividad' => 'Mantenimiento periodico de instalaciones y equipos', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '5.1.1', 'actividad' => 'Plan de prevencion, preparacion y respuesta ante emergencias', 'phva' => 'PLANEAR', 'responsable' => 'Responsable SST'],
                ['numeral' => '5.1.2', 'actividad' => 'Realizacion de simulacros de emergencia', 'phva' => 'HACER', 'responsable' => 'Responsable SST'],
                ['numeral' => '6.1.1', 'actividad' => 'Definicion de indicadores del SG-SST', 'phva' => 'VERIFICAR', 'responsable' => 'Responsable SST'],
                ['numeral' => '6.1.2', 'actividad' => 'Medicion y analisis de indicadores de estructura', 'phva' => 'VERIFICAR', 'responsable' => 'Responsable SST'],
                ['numeral' => '6.1.3', 'actividad' => 'Medicion y analisis de indicadores de proceso', 'phva' => 'VERIFICAR', 'responsable' => 'Responsable SST'],
                ['numeral' => '6.1.4', 'actividad' => 'Medicion y analisis de indicadores de resultado', 'phva' => 'VERIFICAR', 'responsable' => 'Responsable SST'],
                ['numeral' => '7.1.1', 'actividad' => 'Revision por la alta direccion del SG-SST', 'phva' => 'ACTUAR', 'responsable' => 'Alta Direccion'],
                ['numeral' => '7.1.2', 'actividad' => 'Auditoria anual del SG-SST', 'phva' => 'VERIFICAR', 'responsable' => 'Auditor'],
                ['numeral' => '7.1.3', 'actividad' => 'Planificacion de acciones correctivas y preventivas', 'phva' => 'ACTUAR', 'responsable' => 'Responsable SST'],
                ['numeral' => '7.1.4', 'actividad' => 'Implementacion de acciones de mejora continua', 'phva' => 'ACTUAR', 'responsable' => 'Responsable SST'],
            ]);
        }

        return $basicas;
    }

    /**
     * Obtiene preview del PTA que se generaria (con actividades de IA)
     */
    public function previewPTA(int $idCliente, int $anio = null): array
    {
        $anio = $anio ?? (int)date('Y');

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        // Generar actividades base con IA (o fallback)
        $actividadesBase = $this->generarActividadesBaseConIA($idCliente, $estandares, $anio);

        // Obtener cronogramas para las actividades de capacitacion
        $cronogramas = $this->cronogramaModel
            ->select('tbl_cronog_capacitacion.*, capacitaciones_sst.capacitacion')
            ->join('capacitaciones_sst', 'capacitaciones_sst.id_capacitacion = tbl_cronog_capacitacion.id_capacitacion', 'left')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_programada)', $anio)
            ->orderBy('fecha_programada', 'ASC')
            ->findAll();

        $actividadesCapacitacion = [];
        foreach ($cronogramas as $cron) {
            $actividadesCapacitacion[] = [
                'numeral' => $this->determinarNumeral($cron['capacitacion']),
                'actividad' => 'Capacitación: ' . $cron['capacitacion'],
                'phva' => 'HACER',
                'responsable' => 'Responsable SST',
                'fecha' => $cron['fecha_programada']
            ];
        }

        return [
            'anio' => $anio,
            'estandares' => $estandares,
            'actividades_base' => $actividadesBase,
            'actividades_capacitacion' => $actividadesCapacitacion,
            'total_actividades' => count($actividadesBase) + count($actividadesCapacitacion)
        ];
    }

    /**
     * Determina el numeral de la resolucion segun el tipo de capacitacion
     */
    protected function determinarNumeral(string $nombreCapacitacion): string
    {
        $nombre = strtolower($nombreCapacitacion);

        if (strpos($nombre, 'inducción') !== false || strpos($nombre, 'reinducción') !== false) {
            return '1.2.2';
        }

        if (strpos($nombre, 'copasst') !== false) {
            return '1.1.7';
        }

        if (strpos($nombre, 'convivencia') !== false) {
            return '1.1.8';
        }

        if (strpos($nombre, 'brigada') !== false || strpos($nombre, 'emergencia') !== false) {
            return '5.1.1';
        }

        if (strpos($nombre, 'simulacro') !== false) {
            return '5.1.2';
        }

        if (strpos($nombre, 'vigía') !== false) {
            return '1.1.7';
        }

        if (strpos($nombre, 'riesgo') !== false || strpos($nombre, 'peligro') !== false) {
            return '4.1.2';
        }

        return '2.11.1';
    }

    /**
     * Verifica si ya existe una actividad similar en el PTA
     */
    protected function verificarExistenciaActividad(int $idCliente, string $actividad, string $fecha): bool
    {
        $anio = date('Y', strtotime($fecha));

        $db = \Config\Database::connect();
        $actividadEscapada = $db->escapeLikeString($actividad);
        $existente = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where("actividad_plandetrabajo COLLATE utf8mb4_general_ci LIKE '%{$actividadEscapada}%'", null, false)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->get()
            ->getRowArray();

        return $existente !== null;
    }

    /**
     * Obtiene resumen del PTA existente
     */
    public function getResumenPTA(int $idCliente, ?int $anio = null, ?string $tipoServicio = null): array
    {
        $anio = $anio ?? (int)date('Y');

        $builder = $this->ptaModel
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio);

        if ($tipoServicio !== null) {
            $builder->where('tipo_servicio', $tipoServicio);
        }

        $actividades = $builder
            ->orderBy('fecha_propuesta', 'ASC')
            ->findAll();

        $total = count($actividades);
        $cerradas = 0;
        $enProceso = 0;
        $abiertas = 0;

        $porPhva = [
            'PLANEAR' => 0,
            'HACER' => 0,
            'VERIFICAR' => 0,
            'ACTUAR' => 0
        ];

        foreach ($actividades as $act) {
            $estado = strtoupper($act['estado_actividad'] ?? 'ABIERTA');
            if ($estado === 'CERRADA') {
                $cerradas++;
            } elseif ($estado === 'GESTIONANDO') {
                $enProceso++;
            } else {
                $abiertas++;
            }

            $phva = strtoupper($act['phva_plandetrabajo'] ?? 'HACER');
            if (isset($porPhva[$phva])) {
                $porPhva[$phva]++;
            }
        }

        return [
            'anio' => $anio,
            'total' => $total,
            'cerradas' => $cerradas,
            'en_proceso' => $enProceso,
            'abiertas' => $abiertas,
            'porcentaje_avance' => $total > 0 ? round(($cerradas / $total) * 100) : 0,
            'por_phva' => $porPhva,
            'actividades' => $actividades
        ];
    }
}
