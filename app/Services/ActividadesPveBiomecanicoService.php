<?php

namespace App\Services;

use App\Models\PtaclienteModel;

/**
 * Servicio para generar actividades del PVE de Riesgo Biomecanico
 * segun Resolucion 0312/2019 - Estandar 4.2.3
 *
 * Programa de Vigilancia Epidemiologica orientado a la prevencion
 * de Desordenes Musculoesqueleticos (DME) por riesgo biomecanico.
 */
class ActividadesPveBiomecanicoService
{
    protected PtaclienteModel $ptaModel;

    public function __construct()
    {
        $this->ptaModel = new PtaclienteModel();
    }

    /**
     * Actividades del PVE de Riesgo Biomecanico
     * Basadas en Res. 0312/2019, Gatiso DME (Min. Proteccion Social), Res. 2844/2007
     */
    public const ACTIVIDADES_PVE_BIOMECANICO = [
        [
            'mes' => 1,
            'actividad' => 'Aplicacion de encuesta de sintomatologia osteomuscular (Cuestionario Nordico) como linea base',
            'objetivo' => 'Diagnosticar la prevalencia de sintomas osteomusculares en los trabajadores para priorizar intervenciones',
            'responsable' => 'Responsable SST / IPS',
            'phva' => 'PLANEAR',
            'numeral' => '4.2.3'
        ],
        [
            'mes' => 2,
            'actividad' => 'Analisis de puestos de trabajo con mayor exposicion a riesgo biomecanico',
            'objetivo' => 'Identificar y evaluar los factores de riesgo biomecanico (posturas, movimientos repetitivos, manipulacion de cargas) en puestos criticos',
            'responsable' => 'Responsable SST / Ergonomo',
            'phva' => 'PLANEAR',
            'numeral' => '4.2.3'
        ],
        [
            'mes' => 3,
            'actividad' => 'Capacitacion en higiene postural y mecanica corporal',
            'objetivo' => 'Educar a los trabajadores sobre posturas correctas, tecnicas de levantamiento de cargas y mecanica corporal para prevenir DME',
            'responsable' => 'Responsable SST / ARL',
            'phva' => 'HACER',
            'numeral' => '4.2.3'
        ],
        [
            'mes' => 3,
            'actividad' => 'Implementacion de programa de pausas activas con enfasis osteomuscular (minimo 2 veces/dia)',
            'objetivo' => 'Establecer pausas activas con ejercicios de estiramiento y fortalecimiento muscular para reducir la fatiga biomecanica',
            'responsable' => 'Responsable SST / Lideres de area',
            'phva' => 'HACER',
            'numeral' => '4.2.3'
        ],
        [
            'mes' => 4,
            'actividad' => 'Inspeccion ergonomica de puestos de trabajo (mobiliario, herramientas, ayudas mecanicas)',
            'objetivo' => 'Verificar condiciones ergonomicas de los puestos de trabajo e identificar necesidades de ajuste o adecuacion',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '4.2.3'
        ],
        [
            'mes' => 5,
            'actividad' => 'Implementacion de controles de ingenieria y administrativos en puestos criticos',
            'objetivo' => 'Ejecutar las recomendaciones ergonomicas derivadas de la evaluacion de puestos de trabajo (ajuste mobiliario, rotacion de tareas, ayudas mecanicas)',
            'responsable' => 'Responsable SST / Administracion',
            'phva' => 'HACER',
            'numeral' => '4.2.3'
        ],
        [
            'mes' => 6,
            'actividad' => 'Programa de gimnasia laboral y ejercicios de fortalecimiento muscular',
            'objetivo' => 'Implementar rutinas de ejercicios orientados al fortalecimiento de grupos musculares mas afectados segun la actividad laboral',
            'responsable' => 'Responsable SST / Fisioterapeuta',
            'phva' => 'HACER',
            'numeral' => '4.2.3'
        ],
        [
            'mes' => 7,
            'actividad' => 'Capacitacion en manejo manual de cargas y uso de ayudas mecanicas',
            'objetivo' => 'Entrenar a los trabajadores en tecnicas seguras de manipulacion manual de cargas y uso correcto de ayudas mecanicas disponibles',
            'responsable' => 'Responsable SST / ARL',
            'phva' => 'HACER',
            'numeral' => '4.2.3'
        ],
        [
            'mes' => 8,
            'actividad' => 'Seguimiento a casos de sintomatologia osteomuscular y remision a EPS',
            'objetivo' => 'Realizar seguimiento a trabajadores con sintomatologia osteomuscular, verificar asistencia a controles medicos y remitir casos criticos a EPS',
            'responsable' => 'Responsable SST / Medico Ocupacional',
            'phva' => 'HACER',
            'numeral' => '4.2.3'
        ],
        [
            'mes' => 9,
            'actividad' => 'Aplicacion de segunda encuesta de sintomatologia osteomuscular (seguimiento)',
            'objetivo' => 'Evaluar la evolucion de la sintomatologia osteomuscular y medir el impacto de las intervenciones realizadas',
            'responsable' => 'Responsable SST / IPS',
            'phva' => 'VERIFICAR',
            'numeral' => '4.2.3'
        ],
        [
            'mes' => 10,
            'actividad' => 'Evaluacion de indicadores del PVE Biomecanico y ajuste de intervenciones',
            'objetivo' => 'Medir cumplimiento de indicadores del PVE, analizar tendencias de morbilidad osteomuscular y ajustar el programa',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '4.2.3'
        ],
        [
            'mes' => 12,
            'actividad' => 'Informe anual del PVE de Riesgo Biomecanico y planificacion siguiente ano',
            'objetivo' => 'Evaluar resultados anuales del PVE, comparar con linea base, identificar logros y definir plan de accion para el proximo ano',
            'responsable' => 'Responsable SST',
            'phva' => 'ACTUAR',
            'numeral' => '4.2.3'
        ]
    ];

    /**
     * Obtiene el resumen de actividades del PVE Biomecanico para un cliente
     */
    public function getResumenActividades(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        $existentes = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->groupStart()
                ->where('tipo_servicio', 'PVE Riesgo Biomecanico')
                ->orLike('tipo_servicio', 'Biomecanico', 'both')
                ->orLike('tipo_servicio', 'Biomec', 'both')
                ->orLike('actividad_plandetrabajo', 'osteomuscular', 'both')
                ->orLike('actividad_plandetrabajo', 'ergonomic', 'both')
                ->orLike('actividad_plandetrabajo', 'biomecanico', 'both')
                ->orLike('actividad_plandetrabajo', 'higiene postural', 'both')
                ->orLike('actividad_plandetrabajo', 'pausas activas', 'both')
                ->orLike('actividad_plandetrabajo', 'manejo de cargas', 'both')
            ->groupEnd()
            ->countAllResults();

        return [
            'existentes' => $existentes,
            'sugeridas' => count(self::ACTIVIDADES_PVE_BIOMECANICO),
            'completo' => $existentes >= 5
        ];
    }

    /**
     * Preview de las actividades que se generarian
     */
    public function previewActividades(int $idCliente, int $anio, ?array $contexto = null, string $instrucciones = ''): array
    {
        $actividades = [];
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        foreach (self::ACTIVIDADES_PVE_BIOMECANICO as $idx => $act) {
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

        $explicacionIA = '';

        if (!empty($instrucciones)) {
            $resultadoIA = $this->interpretarInstruccionesConIA($instrucciones, $actividades, $contexto, $anio);

            if (!empty($resultadoIA['excluir'])) {
                foreach ($resultadoIA['excluir'] as $indiceExcluir) {
                    if (isset($actividades[$indiceExcluir])) {
                        $actividades[$indiceExcluir]['excluida'] = true;
                    }
                }
                $actividades = array_filter($actividades, fn($a) => !isset($a['excluida']));
            }

            if (!empty($resultadoIA['modificar'])) {
                foreach ($resultadoIA['modificar'] as $mod) {
                    $indice = $mod['indice'] ?? -1;
                    if (isset($actividades[$indice]) && isset($mod['nuevo_mes'])) {
                        $nuevoMes = (int)$mod['nuevo_mes'];
                        $actividades[$indice]['mes'] = $meses[$nuevoMes] ?? $actividades[$indice]['mes'];
                        $actividades[$indice]['mes_num'] = $nuevoMes;
                        $actividades[$indice]['fecha_propuesta'] = "{$anio}-" . str_pad($nuevoMes, 2, '0', STR_PAD_LEFT) . "-15";
                        $actividades[$indice]['modificada_por_ia'] = true;
                    }
                }
            }

            if (!empty($resultadoIA['agregar'])) {
                foreach ($resultadoIA['agregar'] as $nueva) {
                    $actividades[] = $nueva;
                }
            }

            $explicacionIA = $resultadoIA['explicacion'] ?? '';
        }

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
     * Interpreta instrucciones del usuario usando IA
     */
    protected function interpretarInstruccionesConIA(string $instrucciones, array $actividadesBase, ?array $contexto, int $anio): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            return $this->interpretarInstruccionesSimple($instrucciones, $anio);
        }

        $actividadesTexto = "";
        foreach ($actividadesBase as $idx => $act) {
            $actividadesTexto .= "{$idx}. [{$act['mes']}] {$act['actividad']} - {$act['objetivo']}\n";
        }

        $contextoTexto = "";
        if ($contexto) {
            $contextoTexto = "CONTEXTO DE LA EMPRESA:\n";
            $contextoTexto .= "- Actividad economica: " . ($contexto['actividad_economica_principal'] ?? 'No especificada') . "\n";
            $contextoTexto .= "- Nivel de riesgo: " . ($contexto['nivel_riesgo_arl'] ?? 'No especificado') . "\n";
            $contextoTexto .= "- Trabajadores: " . ($contexto['total_trabajadores'] ?? 'No especificado') . "\n";
            if (!empty($contexto['peligros_identificados'])) {
                $peligros = is_string($contexto['peligros_identificados'])
                    ? (json_decode($contexto['peligros_identificados'], true) ?? [])
                    : $contexto['peligros_identificados'];
                if (!empty($peligros)) {
                    $contextoTexto .= "- Peligros identificados: " . implode(', ', $peligros) . "\n";
                }
            }
            if (!empty($contexto['observaciones_contexto'])) {
                $contextoTexto .= "\nOBSERVACIONES DEL CONSULTOR:\n" . $contexto['observaciones_contexto'] . "\n";
            }
        }

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia, especialista en ergonomia y riesgo biomecanico.
Tu tarea es personalizar la lista de actividades del PVE de Riesgo Biomecanico segun las instrucciones del usuario.

REGLAS:
1. Si el usuario dice que NO incluya algo, EXCLUYE esas actividades
2. Si menciona periodicidad, ajusta segun corresponda
3. Si pide agregar algo especifico, sugiere actividades nuevas
4. Responde SOLO en formato JSON valido

FORMATO DE RESPUESTA (JSON):
{
  \"excluir\": [0, 3, 5],
  \"modificar\": [{\"indice\": 2, \"nuevo_mes\": 6, \"razon\": \"...\"}],
  \"agregar\": [{\"mes\": 4, \"actividad\": \"...\", \"objetivo\": \"...\", \"phva\": \"HACER\"}],
  \"explicacion\": \"Breve explicacion de los cambios\"
}";

        $userPrompt = "ANO DEL PROGRAMA: {$anio}\n\n";
        $userPrompt .= $contextoTexto . "\n";
        $userPrompt .= "ACTIVIDADES BASE DISPONIBLES:\n{$actividadesTexto}\n";
        $userPrompt .= "INSTRUCCIONES DEL USUARIO:\n\"{$instrucciones}\"\n\n";
        $userPrompt .= "Analiza las instrucciones y genera el JSON de respuesta.";

        $response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey);

        if (!$response['success']) {
            log_message('error', 'Error en IA PVE Biomecanico: ' . ($response['error'] ?? 'desconocido'));
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
            return ['success' => false, 'error' => "Error de conexion: {$error}"];
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

        $contenidoIA = preg_replace('/```json\s*/', '', $contenidoIA);
        $contenidoIA = preg_replace('/```\s*/', '', $contenidoIA);

        $respuesta = json_decode($contenidoIA, true);
        if (!$respuesta) {
            return ['cambios' => [], 'explicacion' => 'No se pudieron procesar las instrucciones'];
        }

        $resultado = [
            'excluir' => $respuesta['excluir'] ?? [],
            'modificar' => $respuesta['modificar'] ?? [],
            'agregar' => [],
            'explicacion' => $respuesta['explicacion'] ?? ''
        ];

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
                    'numeral' => '4.2.3',
                    'fecha_propuesta' => "{$anio}-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-15",
                    'origen' => 'ia',
                    'generado_por_ia' => true
                ];
            }
        }

        return $resultado;
    }

    /**
     * Fallback: Interpretacion simple sin IA (keywords)
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
            'fisioterapia' => ['actividad' => 'Sesiones de fisioterapia preventiva para trabajadores con sintomatologia', 'objetivo' => 'Brindar atencion fisioterapeutica a trabajadores con sintomas osteomusculares', 'phva' => 'HACER', 'mes' => 5],
            'silla ergonomica' => ['actividad' => 'Dotacion de sillas ergonomicas para puestos administrativos', 'objetivo' => 'Mejorar las condiciones ergonomicas del mobiliario de oficina', 'phva' => 'HACER', 'mes' => 4],
            'monitor' => ['actividad' => 'Ajuste de altura de monitores y pantallas de visualizacion', 'objetivo' => 'Adecuar la posicion de monitores segun parametros ergonomicos', 'phva' => 'HACER', 'mes' => 3],
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
                    'numeral' => '4.2.3',
                    'fecha_propuesta' => "{$anio}-" . str_pad($config['mes'], 2, '0', STR_PAD_LEFT) . "-15",
                    'origen' => 'instruccion'
                ];
            }
        }

        return ['agregar' => $actividades, 'excluir' => [], 'modificar' => [], 'explicacion' => ''];
    }

    /**
     * Genera las actividades del PVE Biomecanico en el PTA
     */
    public function generarActividades(int $idCliente, int $anio, ?array $actividadesSeleccionadas = null): array
    {
        $creadas = 0;
        $existentes = 0;
        $errores = [];

        $db = \Config\Database::connect();

        $actividades = $actividadesSeleccionadas ?? self::ACTIVIDADES_PVE_BIOMECANICO;

        foreach ($actividades as $act) {
            $mes = $act['mes_num'] ?? $act['mes'];
            $fechaPropuesta = "{$anio}-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-15";

            $existe = $db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('YEAR(fecha_propuesta)', $anio)
                ->like('actividad_plandetrabajo', substr($act['actividad'], 0, 30), 'both')
                ->countAllResults();

            if ($existe > 0) {
                $existentes++;
                continue;
            }

            try {
                $semana = (int)date('W', strtotime($fechaPropuesta));

                $db->table('tbl_pta_cliente')->insert([
                    'id_cliente' => $idCliente,
                    'tipo_servicio' => 'PVE Riesgo Biomecanico',
                    'phva_plandetrabajo' => $act['phva'] ?? 'HACER',
                    'numeral_plandetrabajo' => $act['numeral'] ?? '4.2.3',
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
     * Obtiene las actividades del PVE Biomecanico del PTA de un cliente
     */
    public function getActividadesCliente(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        return $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->groupStart()
                ->where('tipo_servicio', 'PVE Riesgo Biomecanico')
                ->orLike('tipo_servicio', 'Biomecanico', 'both')
                ->orLike('tipo_servicio', 'Biomec', 'both')
                ->orLike('actividad_plandetrabajo', 'osteomuscular', 'both')
                ->orLike('actividad_plandetrabajo', 'ergonomic', 'both')
                ->orLike('actividad_plandetrabajo', 'biomecanico', 'both')
                ->orLike('actividad_plandetrabajo', 'higiene postural', 'both')
                ->orLike('actividad_plandetrabajo', 'manejo de cargas', 'both')
            ->groupEnd()
            ->orderBy('fecha_propuesta', 'ASC')
            ->get()
            ->getResultArray();
    }
}
