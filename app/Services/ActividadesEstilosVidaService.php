<?php

namespace App\Services;

use App\Models\PtaclienteModel;

/**
 * Servicio para generar actividades de Estilos de Vida Saludable
 * segun Resolucion 0312/2019 - Estandar 3.1.7
 *
 * Similar a ActividadesPyPSaludService pero para estilos de vida,
 * controles de tabaquismo, alcoholismo y farmacodependencia.
 */
class ActividadesEstilosVidaService
{
    protected PtaclienteModel $ptaModel;

    public function __construct()
    {
        $this->ptaModel = new PtaclienteModel();
    }

    /**
     * Actividades de Estilos de Vida Saludable para cualquier empresa
     * Basadas en Resolucion 0312/2019 est. 3.1.7, Ley 1335/2009, Ley 1566/2012, Res 1075/1992
     */
    public const ACTIVIDADES_ESTILOS_VIDA = [
        [
            'mes' => 1,
            'actividad' => 'Aplicacion de encuesta de habitos y estilos de vida (linea base)',
            'objetivo' => 'Diagnosticar los habitos alimentarios, actividad fisica, consumo de tabaco, alcohol y sustancias psicoactivas en los trabajadores',
            'responsable' => 'Responsable SST',
            'phva' => 'PLANEAR',
            'numeral' => '3.1.7'
        ],
        [
            'mes' => 2,
            'actividad' => 'Campana de alimentacion saludable y habitos nutricionales',
            'objetivo' => 'Promover habitos de alimentacion saludable entre los trabajadores mediante material educativo y jornadas de sensibilizacion',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '3.1.7'
        ],
        [
            'mes' => 3,
            'actividad' => 'Jornada de tamizaje de salud (tension arterial, glicemia, IMC)',
            'objetivo' => 'Realizar valoracion de factores de riesgo cardiovascular y metabolico para priorizar intervenciones',
            'responsable' => 'Responsable SST / IPS',
            'phva' => 'HACER',
            'numeral' => '3.1.7'
        ],
        [
            'mes' => 3,
            'actividad' => 'Implementacion de pausas activas programadas (minimo 2 veces/dia)',
            'objetivo' => 'Establecer pausas activas regulares para promover actividad fisica durante la jornada laboral',
            'responsable' => 'Responsable SST / Lideres de area',
            'phva' => 'HACER',
            'numeral' => '3.1.7'
        ],
        [
            'mes' => 4,
            'actividad' => 'Campana de prevencion de tabaquismo y ambientes libres de humo (Ley 1335/2009)',
            'objetivo' => 'Sensibilizar sobre los efectos del tabaquismo y garantizar ambientes 100% libres de humo en las instalaciones',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '3.1.7'
        ],
        [
            'mes' => 5,
            'actividad' => 'Dia Mundial Sin Tabaco - Actividades de sensibilizacion (31 de mayo)',
            'objetivo' => 'Conmemorar el Dia Sin Tabaco con campanas de prevencion, apoyo a fumadores y senalizacion de zonas libres de humo',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '3.1.7'
        ],
        [
            'mes' => 6,
            'actividad' => 'Campana de prevencion de alcoholismo (Res. 1075/1992)',
            'objetivo' => 'Desarrollar campanas educativas sobre consumo responsable de alcohol y protocolos de prohibicion durante jornada laboral',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '3.1.7'
        ],
        [
            'mes' => 7,
            'actividad' => 'Taller de manejo del estres y salud mental',
            'objetivo' => 'Brindar herramientas para el manejo del estres laboral y promover la salud mental como componente de entornos saludables',
            'responsable' => 'Responsable SST / ARL',
            'phva' => 'HACER',
            'numeral' => '3.1.7'
        ],
        [
            'mes' => 8,
            'actividad' => 'Campana de prevencion de farmacodependencia (Ley 1566/2012, Ley 30/1986)',
            'objetivo' => 'Sensibilizar sobre riesgos del consumo de sustancias psicoactivas, protocolos de deteccion temprana y canalizacion a EPS',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '3.1.7'
        ],
        [
            'mes' => 9,
            'actividad' => 'Jornada deportiva y recreativa para trabajadores',
            'objetivo' => 'Fomentar la actividad fisica y la integracion a traves de jornadas deportivas y recreativas',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '3.1.7'
        ],
        [
            'mes' => 10,
            'actividad' => 'Campana de prevencion de enfermedades cronicas no transmisibles',
            'objetivo' => 'Promover la deteccion temprana y control de factores de riesgo de enfermedades cronicas (cardiovascular, diabetes)',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '3.1.7'
        ],
        [
            'mes' => 11,
            'actividad' => 'Evaluacion de indicadores del programa y seguimiento a casos',
            'objetivo' => 'Medir cumplimiento de indicadores del programa, revisar casos remitidos a EPS y ajustar actividades',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '3.1.7'
        ],
        [
            'mes' => 12,
            'actividad' => 'Informe anual del Programa de Estilos de Vida Saludable y planificacion siguiente ano',
            'objetivo' => 'Evaluar resultados anuales, comparar con linea base, identificar logros y definir plan de accion para el proximo ano',
            'responsable' => 'Responsable SST',
            'phva' => 'ACTUAR',
            'numeral' => '3.1.7'
        ]
    ];

    /**
     * Obtiene el resumen de actividades de Estilos de Vida para un cliente
     */
    public function getResumenActividades(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        $existentes = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->groupStart()
                ->where('tipo_servicio', 'Estilos de Vida Saludable')
                ->orLike('tipo_servicio', 'Estilos de Vida', 'both')
                ->orLike('tipo_servicio', 'Vida Saludable', 'both')
                ->orLike('actividad_plandetrabajo', 'tabaquismo', 'both')
                ->orLike('actividad_plandetrabajo', 'alcoholismo', 'both')
                ->orLike('actividad_plandetrabajo', 'farmacodependencia', 'both')
                ->orLike('actividad_plandetrabajo', 'estilos de vida', 'both')
                ->orLike('actividad_plandetrabajo', 'entorno saludable', 'both')
                ->orLike('actividad_plandetrabajo', 'habitos saludables', 'both')
                ->orLike('actividad_plandetrabajo', 'sustancias psicoactivas', 'both')
            ->groupEnd()
            ->countAllResults();

        return [
            'existentes' => $existentes,
            'sugeridas' => count(self::ACTIVIDADES_ESTILOS_VIDA),
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

        // 1. Agregar actividades base
        foreach (self::ACTIVIDADES_ESTILOS_VIDA as $idx => $act) {
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

        // 2. Procesar instrucciones con IA (si hay instrucciones)
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
        }

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia.
Tu tarea es personalizar la lista de actividades de Estilos de Vida Saludable y controles de tabaquismo, alcoholismo y farmacodependencia segun las instrucciones del usuario.

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
            log_message('error', 'Error en IA Estilos Vida: ' . ($response['error'] ?? 'desconocido'));
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
                    'numeral' => '3.1.7',
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
            'alcoholimetria' => ['actividad' => 'Pruebas de alcoholimetria para cargos criticos', 'objetivo' => 'Verificar sobriedad en trabajadores de cargos de alto riesgo', 'phva' => 'VERIFICAR', 'mes' => 5],
            'nutricionista' => ['actividad' => 'Charla con nutricionista profesional', 'objetivo' => 'Brindar asesoria nutricional personalizada', 'phva' => 'HACER', 'mes' => 4],
            'gimnasio' => ['actividad' => 'Convenio con gimnasio o espacio deportivo', 'objetivo' => 'Facilitar el acceso a actividad fisica regular', 'phva' => 'HACER', 'mes' => 3],
            'yoga' => ['actividad' => 'Sesiones de yoga y relajacion', 'objetivo' => 'Promover tecnicas de relajacion y manejo del estres', 'phva' => 'HACER', 'mes' => 6],
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
                    'numeral' => '3.1.7',
                    'fecha_propuesta' => "{$anio}-" . str_pad($config['mes'], 2, '0', STR_PAD_LEFT) . "-15",
                    'origen' => 'instruccion'
                ];
            }
        }

        return ['agregar' => $actividades, 'excluir' => [], 'modificar' => [], 'explicacion' => ''];
    }

    /**
     * Genera las actividades de Estilos de Vida Saludable en el PTA
     */
    public function generarActividades(int $idCliente, int $anio, ?array $actividadesSeleccionadas = null): array
    {
        $creadas = 0;
        $existentes = 0;
        $errores = [];

        $db = \Config\Database::connect();

        $actividades = $actividadesSeleccionadas ?? self::ACTIVIDADES_ESTILOS_VIDA;

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
                    'tipo_servicio' => 'Estilos de Vida Saludable',
                    'phva_plandetrabajo' => $act['phva'] ?? 'HACER',
                    'numeral_plandetrabajo' => $act['numeral'] ?? '3.1.7',
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
     * Obtiene las actividades de Estilos de Vida del PTA de un cliente
     */
    public function getActividadesCliente(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        return $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->groupStart()
                ->where('tipo_servicio', 'Estilos de Vida Saludable')
                ->orLike('tipo_servicio', 'Estilos de Vida', 'both')
                ->orLike('tipo_servicio', 'Vida Saludable', 'both')
                ->orLike('actividad_plandetrabajo', 'tabaquismo', 'both')
                ->orLike('actividad_plandetrabajo', 'alcoholismo', 'both')
                ->orLike('actividad_plandetrabajo', 'farmacodependencia', 'both')
                ->orLike('actividad_plandetrabajo', 'estilos de vida', 'both')
                ->orLike('actividad_plandetrabajo', 'entorno saludable', 'both')
                ->orLike('actividad_plandetrabajo', 'habitos saludables', 'both')
                ->orLike('actividad_plandetrabajo', 'sustancias psicoactivas', 'both')
            ->groupEnd()
            ->orderBy('fecha_propuesta', 'ASC')
            ->get()
            ->getResultArray();
    }
}
