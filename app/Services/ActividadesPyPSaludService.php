<?php

namespace App\Services;

use App\Models\PtaclienteModel;

/**
 * Servicio para generar actividades de Promoción y Prevención en Salud
 * según Resolución 0312/2019 - Estándar 3.1.2
 *
 * Similar a CronogramaIAService pero para actividades de PyP Salud
 */
class ActividadesPyPSaludService
{
    protected PtaclienteModel $ptaModel;

    public function __construct()
    {
        $this->ptaModel = new PtaclienteModel();
    }

    /**
     * Actividades de PyP Salud para cualquier empresa
     * Basadas en normatividad colombiana
     */
    public const ACTIVIDADES_PYP_SALUD = [
        [
            'mes' => 1,  // Enero
            'actividad' => 'Actualización del perfil sociodemográfico',
            'objetivo' => 'Actualizar el diagnóstico de condiciones de salud de los trabajadores',
            'responsable' => 'Responsable SST',
            'phva' => 'PLANEAR',
            'numeral' => '3.1.1'
        ],
        [
            'mes' => 2,  // Febrero
            'actividad' => 'Programación de exámenes médicos ocupacionales periódicos',
            'objetivo' => 'Definir cronograma de exámenes médicos para el año',
            'responsable' => 'Responsable SST',
            'phva' => 'PLANEAR',
            'numeral' => '3.1.4'
        ],
        [
            'mes' => 2,  // Febrero
            'actividad' => 'Campaña de estilos de vida saludables - Alimentación',
            'objetivo' => 'Promover hábitos de alimentación saludable entre los trabajadores',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '3.1.2'
        ],
        [
            'mes' => 3,  // Marzo
            'actividad' => 'Realización de exámenes médicos ocupacionales (Lote 1)',
            'objetivo' => 'Ejecutar exámenes médicos periódicos programados',
            'responsable' => 'IPS Ocupacional',
            'phva' => 'HACER',
            'numeral' => '3.1.4'
        ],
        [
            'mes' => 4,  // Abril
            'actividad' => 'Capacitación en prevención de riesgo cardiovascular',
            'objetivo' => 'Educar a los trabajadores sobre factores de riesgo cardiovascular',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '3.1.2'
        ],
        [
            'mes' => 5,  // Mayo
            'actividad' => 'Jornada de vacunación (según disponibilidad)',
            'objetivo' => 'Promover la vacunación entre los trabajadores',
            'responsable' => 'Responsable SST / EPS',
            'phva' => 'HACER',
            'numeral' => '3.1.2'
        ],
        [
            'mes' => 6,  // Junio
            'actividad' => 'Campaña de salud visual',
            'objetivo' => 'Promover la salud visual y prevenir fatiga ocular',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '3.1.2'
        ],
        [
            'mes' => 6,  // Junio
            'actividad' => 'Seguimiento a casos de salud identificados (Semestre 1)',
            'objetivo' => 'Realizar seguimiento a trabajadores con restricciones o recomendaciones médicas',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '3.1.5'
        ],
        [
            'mes' => 7,  // Julio
            'actividad' => 'Evaluación de indicadores de PyP Salud (Semestre 1)',
            'objetivo' => 'Evaluar cumplimiento de indicadores del programa',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '3.1.2'
        ],
        [
            'mes' => 8,  // Agosto
            'actividad' => 'Capacitación en manejo del estrés y riesgo psicosocial',
            'objetivo' => 'Brindar herramientas para manejo del estrés laboral',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '3.1.2'
        ],
        [
            'mes' => 9,  // Septiembre
            'actividad' => 'Semana de la Salud',
            'objetivo' => 'Realizar jornada integral de promoción de la salud',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '3.1.2'
        ],
        [
            'mes' => 9,  // Septiembre
            'actividad' => 'Realización de exámenes médicos ocupacionales (Lote 2)',
            'objetivo' => 'Ejecutar exámenes médicos periódicos programados',
            'responsable' => 'IPS Ocupacional',
            'phva' => 'HACER',
            'numeral' => '3.1.4'
        ],
        [
            'mes' => 10,  // Octubre
            'actividad' => 'Campaña de prevención de cáncer',
            'objetivo' => 'Sensibilizar sobre detección temprana de cáncer',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '3.1.2'
        ],
        [
            'mes' => 11,  // Noviembre
            'actividad' => 'Capacitación en autocuidado y hábitos saludables',
            'objetivo' => 'Promover el autocuidado como estrategia de prevención',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '3.1.2'
        ],
        [
            'mes' => 12,  // Diciembre
            'actividad' => 'Seguimiento a casos de salud identificados (Semestre 2)',
            'objetivo' => 'Realizar seguimiento a trabajadores con restricciones o recomendaciones médicas',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '3.1.5'
        ],
        [
            'mes' => 12,  // Diciembre
            'actividad' => 'Evaluación anual del Programa de PyP Salud',
            'objetivo' => 'Evaluar cumplimiento de objetivos y definir mejoras para siguiente año',
            'responsable' => 'Responsable SST',
            'phva' => 'ACTUAR',
            'numeral' => '3.1.2'
        ]
    ];

    /**
     * Actividad continua de pausas activas
     */
    public const ACTIVIDAD_PAUSAS_ACTIVAS = [
        'actividad' => 'Implementación de pausas activas',
        'objetivo' => 'Prevenir lesiones osteomusculares mediante pausas activas periódicas',
        'responsable' => 'Responsable SST / Líderes de área',
        'phva' => 'HACER',
        'numeral' => '3.1.2',
        'frecuencia' => 'Diario/Semanal'
    ];

    /**
     * Obtiene el resumen de actividades de PyP Salud para un cliente
     * Usa los mismos criterios que getActividadesCliente() para consistencia
     */
    public function getResumenActividades(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        // Contar actividades existentes de PyP Salud
        // Criterios coherentes con getActividadesCliente()
        $existentes = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->groupStart()
                ->where('tipo_servicio', 'Programa PyP Salud')
                ->orLike('tipo_servicio', 'Promocion', 'both')
                ->orLike('tipo_servicio', 'Prevencion', 'both')
                ->orLike('actividad_plandetrabajo', 'examen medico', 'both')
                ->orLike('actividad_plandetrabajo', 'examenes medicos', 'both')
                ->orLike('actividad_plandetrabajo', 'pausas activas', 'both')
                ->orLike('actividad_plandetrabajo', 'promocion', 'both')
                ->orLike('actividad_plandetrabajo', 'prevencion', 'both')
                ->orLike('actividad_plandetrabajo', 'semana de la salud', 'both')
                ->orLike('actividad_plandetrabajo', 'vacunacion', 'both')
                ->orLike('actividad_plandetrabajo', 'estilos de vida saludables', 'both')
            ->groupEnd()
            ->countAllResults();

        return [
            'existentes' => $existentes,
            'sugeridas' => count(self::ACTIVIDADES_PYP_SALUD),
            'completo' => $existentes >= 5
        ];
    }

    /**
     * Actividades adicionales según peligros identificados
     */
    public const ACTIVIDADES_POR_PELIGRO = [
        'Biomecánico' => [
            ['actividad' => 'Programa de pausas activas y ejercicios de estiramiento', 'objetivo' => 'Prevenir lesiones osteomusculares por posturas y movimientos repetitivos', 'phva' => 'HACER', 'numeral' => '3.1.2'],
            ['actividad' => 'Capacitación en higiene postural y manejo de cargas', 'objetivo' => 'Educar sobre posturas correctas en el trabajo', 'phva' => 'HACER', 'numeral' => '3.1.2'],
        ],
        'Psicosocial' => [
            ['actividad' => 'Taller de manejo del estrés laboral', 'objetivo' => 'Brindar herramientas para gestión del estrés', 'phva' => 'HACER', 'numeral' => '3.1.2'],
            ['actividad' => 'Campaña de salud mental y bienestar emocional', 'objetivo' => 'Promover la salud mental en el trabajo', 'phva' => 'HACER', 'numeral' => '3.1.2'],
            ['actividad' => 'Actividades de integración y clima laboral', 'objetivo' => 'Fortalecer relaciones interpersonales', 'phva' => 'HACER', 'numeral' => '3.1.2'],
        ],
        'Físico' => [
            ['actividad' => 'Jornada de protección auditiva y visual', 'objetivo' => 'Prevenir efectos por exposición a ruido e iluminación', 'phva' => 'HACER', 'numeral' => '3.1.2'],
            ['actividad' => 'Campaña de hidratación y protección solar', 'objetivo' => 'Prevenir efectos por exposición a temperaturas extremas', 'phva' => 'HACER', 'numeral' => '3.1.2'],
        ],
        'Químico' => [
            ['actividad' => 'Capacitación en manejo seguro de sustancias químicas', 'objetivo' => 'Prevenir exposiciones a sustancias peligrosas', 'phva' => 'HACER', 'numeral' => '3.1.2'],
            ['actividad' => 'Jornada de evaluación de salud respiratoria', 'objetivo' => 'Detectar efectos por exposición a químicos', 'phva' => 'VERIFICAR', 'numeral' => '3.1.4'],
        ],
        'Biológico' => [
            ['actividad' => 'Jornada de vacunación ocupacional', 'objetivo' => 'Inmunizar contra agentes biológicos de riesgo', 'phva' => 'HACER', 'numeral' => '3.1.2'],
            ['actividad' => 'Capacitación en prevención de enfermedades infecciosas', 'objetivo' => 'Educar sobre medidas de bioseguridad', 'phva' => 'HACER', 'numeral' => '3.1.2'],
        ],
        'Locativo' => [
            ['actividad' => 'Inspección de condiciones locativas y orden', 'objetivo' => 'Verificar condiciones seguras en áreas de trabajo', 'phva' => 'VERIFICAR', 'numeral' => '3.1.2'],
        ],
        'Mecánico' => [
            ['actividad' => 'Capacitación en prevención de accidentes con máquinas', 'objetivo' => 'Prevenir lesiones por operación de equipos', 'phva' => 'HACER', 'numeral' => '3.1.2'],
        ],
        'Eléctrico' => [
            ['actividad' => 'Capacitación en riesgo eléctrico', 'objetivo' => 'Prevenir accidentes por contacto eléctrico', 'phva' => 'HACER', 'numeral' => '3.1.2'],
        ],
    ];

    /**
     * Preview de las actividades que se generarían
     * Personaliza según contexto del cliente e instrucciones (usa IA si está disponible)
     */
    public function previewActividades(int $idCliente, int $anio, ?array $contexto = null, string $instrucciones = ''): array
    {
        $actividades = [];
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        // 1. Agregar actividades base
        foreach (self::ACTIVIDADES_PYP_SALUD as $idx => $act) {
            $actividades[$idx] = [
                'indice_original' => $idx,
                'mes' => $meses[$act['mes']],
                'mes_num' => $act['mes'],
                'actividad' => $act['actividad'],
                'objetivo' => $act['objetivo'],
                'responsable' => $act['responsable'],
                'phva' => $act['phva'],
                'numeral' => $act['numeral'],
                'fecha_propuesta' => "{$anio}-" . str_pad($act['mes'], 2, '0', STR_PAD_LEFT) . "-15",
                'origen' => 'base'
            ];
        }

        // 2. Agregar actividades según peligros identificados
        if ($contexto && !empty($contexto['peligros_identificados'])) {
            $peligros = json_decode($contexto['peligros_identificados'], true) ?? [];
            $mesActual = 3;

            foreach ($peligros as $peligro) {
                foreach (self::ACTIVIDADES_POR_PELIGRO as $tipoPeligro => $actividadesPeligro) {
                    if (stripos($peligro, $tipoPeligro) !== false) {
                        foreach ($actividadesPeligro as $actPeligro) {
                            $actividades[] = [
                                'mes' => $meses[$mesActual],
                                'mes_num' => $mesActual,
                                'actividad' => $actPeligro['actividad'],
                                'objetivo' => $actPeligro['objetivo'],
                                'responsable' => 'Responsable SST',
                                'phva' => $actPeligro['phva'],
                                'numeral' => $actPeligro['numeral'],
                                'fecha_propuesta' => "{$anio}-" . str_pad($mesActual, 2, '0', STR_PAD_LEFT) . "-15",
                                'origen' => 'peligro',
                                'peligro_relacionado' => $peligro
                            ];
                            $mesActual = ($mesActual % 12) + 1;
                        }
                    }
                }
            }
        }

        $explicacionIA = '';

        // 3. Procesar instrucciones con IA (si hay instrucciones)
        if (!empty($instrucciones)) {
            $resultadoIA = $this->interpretarInstruccionesConIA($instrucciones, $actividades, $contexto, $anio);

            // Aplicar exclusiones
            if (!empty($resultadoIA['excluir'])) {
                foreach ($resultadoIA['excluir'] as $indiceExcluir) {
                    if (isset($actividades[$indiceExcluir])) {
                        $actividades[$indiceExcluir]['excluida'] = true;
                        $actividades[$indiceExcluir]['origen'] = 'excluida_ia';
                    }
                }
                // Filtrar las excluidas
                $actividades = array_filter($actividades, fn($a) => !isset($a['excluida']));
            }

            // Aplicar modificaciones de mes
            if (!empty($resultadoIA['modificar'])) {
                foreach ($resultadoIA['modificar'] as $mod) {
                    $indice = $mod['indice'] ?? -1;
                    if (isset($actividades[$indice]) && isset($mod['nuevo_mes'])) {
                        $nuevoMes = (int)$mod['nuevo_mes'];
                        $actividades[$indice]['mes'] = $meses[$nuevoMes] ?? $actividades[$indice]['mes'];
                        $actividades[$indice]['mes_num'] = $nuevoMes;
                        $actividades[$indice]['fecha_propuesta'] = "{$anio}-" . str_pad($nuevoMes, 2, '0', STR_PAD_LEFT) . "-15";
                        $actividades[$indice]['modificada_por_ia'] = true;
                        if (!empty($mod['razon'])) {
                            $actividades[$indice]['razon_modificacion'] = $mod['razon'];
                        }
                    }
                }
            }

            // Agregar nuevas actividades sugeridas por IA
            if (!empty($resultadoIA['agregar'])) {
                foreach ($resultadoIA['agregar'] as $nueva) {
                    $actividades[] = $nueva;
                }
            }

            $explicacionIA = $resultadoIA['explicacion'] ?? '';
        }

        // Reindexar y ordenar por mes
        $actividades = array_values($actividades);
        usort($actividades, function($a, $b) {
            return $a['mes_num'] <=> $b['mes_num'];
        });

        return [
            'actividades' => $actividades,
            'total' => count($actividades),
            'anio' => $anio,
            'contexto_aplicado' => $contexto ? true : false,
            'instrucciones_procesadas' => !empty($instrucciones),
            'explicacion_ia' => $explicacionIA
        ];
    }

    /**
     * Interpreta instrucciones del usuario usando IA para personalizar actividades
     * Puede filtrar, modificar o agregar actividades según las instrucciones
     */
    protected function interpretarInstruccionesConIA(string $instrucciones, array $actividadesBase, ?array $contexto, int $anio): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            // Fallback a método simple si no hay API key
            return $this->interpretarInstruccionesSimple($instrucciones, $anio);
        }

        // Preparar lista de actividades para la IA
        $actividadesTexto = "";
        foreach ($actividadesBase as $idx => $act) {
            $actividadesTexto .= "{$idx}. [{$act['mes']}] {$act['actividad']} - {$act['objetivo']}\n";
        }

        // Contexto de la empresa
        $contextoTexto = "";
        if ($contexto) {
            $contextoTexto = "CONTEXTO DE LA EMPRESA:\n";
            $contextoTexto .= "- Actividad económica: " . ($contexto['actividad_economica_principal'] ?? 'No especificada') . "\n";
            $contextoTexto .= "- Nivel de riesgo: " . ($contexto['nivel_riesgo_arl'] ?? 'No especificado') . "\n";
            $contextoTexto .= "- Trabajadores: " . ($contexto['total_trabajadores'] ?? 'No especificado') . "\n";
            if (!empty($contexto['peligros_identificados'])) {
                $peligros = json_decode($contexto['peligros_identificados'], true) ?? [];
                $contextoTexto .= "- Peligros identificados: " . implode(', ', $peligros) . "\n";
            }
        }

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia.
Tu tarea es personalizar la lista de actividades de Promoción y Prevención en Salud según las instrucciones del usuario.

REGLAS:
1. Si el usuario dice que NO incluya algo (ej: 'no incluir exámenes médicos'), debes EXCLUIR esas actividades
2. Si menciona periodicidad (ej: 'cada 3 años'), debes ajustar o excluir según corresponda
3. Si pide agregar algo específico, sugiere actividades nuevas
4. Responde SOLO en formato JSON válido

FORMATO DE RESPUESTA (JSON):
{
  \"excluir\": [0, 3, 5],  // índices de actividades a excluir
  \"modificar\": [{\"indice\": 2, \"nuevo_mes\": 6, \"razon\": \"...\"}],  // modificaciones
  \"agregar\": [{\"mes\": 4, \"actividad\": \"...\", \"objetivo\": \"...\", \"phva\": \"HACER\"}],  // nuevas
  \"explicacion\": \"Breve explicación de los cambios\"
}";

        $userPrompt = "AÑO DEL PROGRAMA: {$anio}\n\n";
        $userPrompt .= $contextoTexto . "\n";
        $userPrompt .= "ACTIVIDADES BASE DISPONIBLES:\n{$actividadesTexto}\n";
        $userPrompt .= "INSTRUCCIONES DEL USUARIO:\n\"{$instrucciones}\"\n\n";
        $userPrompt .= "Analiza las instrucciones y genera el JSON de respuesta. Si las instrucciones no requieren cambios, devuelve arrays vacíos.";

        // Llamar a OpenAI
        $response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey);

        if (!$response['success']) {
            log_message('error', 'Error en IA PyP Salud: ' . ($response['error'] ?? 'desconocido'));
            return $this->interpretarInstruccionesSimple($instrucciones, $anio);
        }

        return $this->procesarRespuestaIA($response['contenido'], $actividadesBase, $anio);
    }

    /**
     * Llama a la API de OpenAI
     */
    protected function llamarOpenAI(string $systemPrompt, string $userPrompt, string $apiKey): array
    {
        $data = [
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.3,
            'max_tokens' => 1500
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => "Error de conexión: {$error}"];
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => $result['error']['message'] ?? 'Error HTTP ' . $httpCode];
        }

        if (isset($result['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'contenido' => trim($result['choices'][0]['message']['content'])
            ];
        }

        return ['success' => false, 'error' => 'Respuesta inesperada'];
    }

    /**
     * Procesa la respuesta JSON de la IA
     */
    protected function procesarRespuestaIA(string $contenidoIA, array $actividadesBase, int $anio): array
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        // Limpiar el JSON (puede venir con ```json ... ```)
        $contenidoIA = preg_replace('/```json\s*/', '', $contenidoIA);
        $contenidoIA = preg_replace('/```\s*/', '', $contenidoIA);

        $respuesta = json_decode($contenidoIA, true);
        if (!$respuesta) {
            log_message('warning', 'No se pudo parsear respuesta IA: ' . $contenidoIA);
            return ['cambios' => [], 'explicacion' => 'No se pudieron procesar las instrucciones'];
        }

        $resultado = [
            'excluir' => $respuesta['excluir'] ?? [],
            'modificar' => $respuesta['modificar'] ?? [],
            'agregar' => [],
            'explicacion' => $respuesta['explicacion'] ?? ''
        ];

        // Procesar actividades a agregar
        if (!empty($respuesta['agregar'])) {
            foreach ($respuesta['agregar'] as $nueva) {
                $mes = (int)($nueva['mes'] ?? 6);
                $resultado['agregar'][] = [
                    'mes' => $meses[$mes] ?? 'Junio',
                    'mes_num' => $mes,
                    'actividad' => $nueva['actividad'] ?? 'Actividad personalizada',
                    'objetivo' => $nueva['objetivo'] ?? '',
                    'responsable' => $nueva['responsable'] ?? 'Responsable SST',
                    'phva' => $nueva['phva'] ?? 'HACER',
                    'numeral' => '3.1.2',
                    'fecha_propuesta' => "{$anio}-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-15",
                    'origen' => 'ia',
                    'generado_por_ia' => true
                ];
            }
        }

        return $resultado;
    }

    /**
     * Fallback: Interpretación simple sin IA (keywords)
     */
    protected function interpretarInstruccionesSimple(string $instrucciones, int $anio): array
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $actividades = [];
        $instrLower = strtolower($instrucciones);

        $keywords = [
            'pausas activas' => ['actividad' => 'Programa intensivo de pausas activas', 'objetivo' => 'Implementar pausas activas con mayor frecuencia', 'phva' => 'HACER', 'mes' => 4],
            'salud mental' => ['actividad' => 'Programa de salud mental y bienestar', 'objetivo' => 'Atender necesidades de salud mental', 'phva' => 'HACER', 'mes' => 5],
            'psicosocial' => ['actividad' => 'Intervención en riesgo psicosocial', 'objetivo' => 'Gestionar factores psicosociales', 'phva' => 'HACER', 'mes' => 6],
            'ergonomia' => ['actividad' => 'Evaluación ergonómica de puestos', 'objetivo' => 'Identificar y corregir condiciones ergonómicas', 'phva' => 'VERIFICAR', 'mes' => 4],
        ];

        foreach ($keywords as $keyword => $config) {
            if (strpos($instrLower, $keyword) !== false) {
                $actividades[] = [
                    'mes' => $meses[$config['mes']],
                    'mes_num' => $config['mes'],
                    'actividad' => $config['actividad'],
                    'objetivo' => $config['objetivo'],
                    'responsable' => 'Responsable SST',
                    'phva' => $config['phva'],
                    'numeral' => '3.1.2',
                    'fecha_propuesta' => "{$anio}-" . str_pad($config['mes'], 2, '0', STR_PAD_LEFT) . "-15",
                    'origen' => 'instruccion'
                ];
            }
        }

        return ['agregar' => $actividades, 'excluir' => [], 'modificar' => [], 'explicacion' => ''];
    }

    /**
     * Genera las actividades de PyP Salud en el PTA
     *
     * @param int $idCliente ID del cliente
     * @param int $anio Año para las actividades
     * @param array|null $actividadesSeleccionadas Si se pasa, usa estas actividades personalizadas
     */
    public function generarActividades(int $idCliente, int $anio, ?array $actividadesSeleccionadas = null): array
    {
        $creadas = 0;
        $existentes = 0;
        $errores = [];

        $db = \Config\Database::connect();

        // Usar actividades seleccionadas o las predefinidas
        $actividades = $actividadesSeleccionadas ?? self::ACTIVIDADES_PYP_SALUD;

        foreach ($actividades as $act) {
            // Obtener el mes (puede venir como 'mes_num' del frontend o 'mes' del array constante)
            $mes = $act['mes_num'] ?? $act['mes'];
            $fechaPropuesta = "{$anio}-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-15";

            // Verificar si ya existe una actividad similar
            $existe = $db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('YEAR(fecha_propuesta)', $anio)
                ->like('actividad_plandetrabajo', substr($act['actividad'], 0, 30), 'both')
                ->countAllResults();

            if ($existe > 0) {
                $existentes++;
                continue;
            }

            // Insertar actividad con los nombres de columnas correctos
            try {
                $semana = (int)date('W', strtotime($fechaPropuesta));

                $db->table('tbl_pta_cliente')->insert([
                    'id_cliente' => $idCliente,
                    'tipo_servicio' => 'Programa PyP Salud',
                    'phva_plandetrabajo' => $act['phva'] ?? 'HACER',
                    'numeral_plandetrabajo' => $act['numeral'] ?? '3.1.2',
                    'actividad_plandetrabajo' => $act['actividad'],
                    'responsable_sugerido_plandetrabajo' => $act['responsable'] ?? 'Responsable SST',
                    'fecha_propuesta' => $fechaPropuesta,
                    'estado_actividad' => 'ABIERTA',
                    'porcentaje_avance' => 0,
                    'semana' => $semana
                ]);
                $creadas++;
            } catch (\Exception $e) {
                $errores[] = "Error en '{$act['actividad']}': " . $e->getMessage();
            }
        }

        return [
            'creadas' => $creadas,
            'existentes' => $existentes,
            'errores' => $errores,
            'total' => count($actividades)
        ];
    }

    /**
     * Obtiene las actividades de PyP Salud del PTA de un cliente
     * Criterios coherentes con getResumenActividades()
     */
    public function getActividadesCliente(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        return $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->groupStart()
                ->where('tipo_servicio', 'Programa PyP Salud')
                ->orLike('tipo_servicio', 'Promocion', 'both')
                ->orLike('tipo_servicio', 'Prevencion', 'both')
                ->orLike('actividad_plandetrabajo', 'examen medico', 'both')
                ->orLike('actividad_plandetrabajo', 'examenes medicos', 'both')
                ->orLike('actividad_plandetrabajo', 'pausas activas', 'both')
                ->orLike('actividad_plandetrabajo', 'promocion', 'both')
                ->orLike('actividad_plandetrabajo', 'prevencion', 'both')
                ->orLike('actividad_plandetrabajo', 'semana de la salud', 'both')
                ->orLike('actividad_plandetrabajo', 'vacunacion', 'both')
                ->orLike('actividad_plandetrabajo', 'estilos de vida saludables', 'both')
            ->groupEnd()
            ->orderBy('fecha_propuesta', 'ASC')
            ->get()
            ->getResultArray();
    }
}
