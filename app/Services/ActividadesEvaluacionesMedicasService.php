<?php

namespace App\Services;

use App\Models\PtaclienteModel;

/**
 * Servicio para generar actividades de Evaluaciones Medicas Ocupacionales
 * segun Resolucion 0312/2019 - Estandar 3.1.4
 *
 * Cubre: evaluaciones medicas segun peligros, frecuencia segun riesgos,
 * comunicacion de resultados y articulacion con PVE.
 */
class ActividadesEvaluacionesMedicasService
{
    protected PtaclienteModel $ptaModel;

    public function __construct()
    {
        $this->ptaModel = new PtaclienteModel();
    }

    /**
     * Actividades de Evaluaciones Medicas Ocupacionales
     * Basadas en Res. 0312/2019 est. 3.1.4, Res. 2346/2007, Decreto 1072/2015
     */
    public const ACTIVIDADES_EVALUACIONES_MEDICAS = [
        [
            'mes' => 1,
            'actividad' => 'Elaboracion o actualizacion del profesiograma por cargos segun peligros identificados',
            'objetivo' => 'Definir los examenes medicos ocupacionales requeridos por cargo de acuerdo con los peligros de la matriz de identificacion de peligros',
            'responsable' => 'Responsable SST / Medico con licencia SST',
            'phva' => 'PLANEAR',
            'numeral' => '3.1.4'
        ],
        [
            'mes' => 2,
            'actividad' => 'Realizacion de evaluaciones medicas ocupacionales de ingreso',
            'objetivo' => 'Determinar la aptitud del trabajador para las funciones del cargo y establecer la linea base de salud segun Res. 2346/2007',
            'responsable' => 'Responsable SST / IPS con licencia SST',
            'phva' => 'HACER',
            'numeral' => '3.1.4'
        ],
        [
            'mes' => 3,
            'actividad' => 'Programacion y ejecucion de evaluaciones medicas periodicas segun profesiograma',
            'objetivo' => 'Realizar evaluaciones periodicas con frecuencia acorde a la magnitud de los riesgos y estado de salud del trabajador',
            'responsable' => 'Responsable SST / IPS con licencia SST',
            'phva' => 'HACER',
            'numeral' => '3.1.4'
        ],
        [
            'mes' => 4,
            'actividad' => 'Comunicacion de resultados de evaluaciones medicas a los trabajadores',
            'objetivo' => 'Entregar certificados de aptitud con recomendaciones a cada trabajador dentro de los 5 dias habiles siguientes (Res. 2346/2007 art. 16)',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '3.1.4'
        ],
        [
            'mes' => 5,
            'actividad' => 'Seguimiento a restricciones y recomendaciones medico-laborales',
            'objetivo' => 'Implementar y verificar el cumplimiento de las restricciones y recomendaciones emitidas por el medico ocupacional',
            'responsable' => 'Responsable SST / Jefes de area',
            'phva' => 'HACER',
            'numeral' => '3.1.4'
        ],
        [
            'mes' => 6,
            'actividad' => 'Actualizacion de la descripcion sociodemografica y diagnostico de condiciones de salud',
            'objetivo' => 'Analizar los resultados de las evaluaciones medicas para actualizar el perfil de salud de la poblacion trabajadora',
            'responsable' => 'Medico con licencia SST',
            'phva' => 'VERIFICAR',
            'numeral' => '3.1.4'
        ],
        [
            'mes' => 7,
            'actividad' => 'Verificacion de frecuencia de evaluaciones segun magnitud de riesgos',
            'objetivo' => 'Revisar que las frecuencias de examenes periodicos correspondan con el nivel de riesgo de cada cargo y los PVE establecidos',
            'responsable' => 'Responsable SST / Medico con licencia SST',
            'phva' => 'VERIFICAR',
            'numeral' => '3.1.4'
        ],
        [
            'mes' => 8,
            'actividad' => 'Realizacion de evaluaciones medicas por cambio de cargo o condiciones laborales',
            'objetivo' => 'Evaluar la aptitud del trabajador cuando se presenten cambios en las condiciones de exposicion a peligros',
            'responsable' => 'Responsable SST / IPS con licencia SST',
            'phva' => 'HACER',
            'numeral' => '3.1.4'
        ],
        [
            'mes' => 9,
            'actividad' => 'Articulacion de resultados medicos con los Programas de Vigilancia Epidemiologica',
            'objetivo' => 'Canalizar hallazgos de evaluaciones medicas hacia los PVE correspondientes y ajustar intervenciones',
            'responsable' => 'Responsable SST / Medico con licencia SST',
            'phva' => 'HACER',
            'numeral' => '3.1.4'
        ],
        [
            'mes' => 10,
            'actividad' => 'Realizacion de evaluaciones medicas de egreso a trabajadores retirados',
            'objetivo' => 'Documentar el estado de salud al finalizar la relacion laboral y establecer posibles enfermedades laborales',
            'responsable' => 'Responsable SST / IPS con licencia SST',
            'phva' => 'HACER',
            'numeral' => '3.1.4'
        ],
        [
            'mes' => 11,
            'actividad' => 'Evaluacion de indicadores del programa de evaluaciones medicas ocupacionales',
            'objetivo' => 'Medir cobertura, cumplimiento de frecuencias, comunicacion de resultados y seguimiento a recomendaciones',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '3.1.4'
        ],
        [
            'mes' => 12,
            'actividad' => 'Informe anual de evaluaciones medicas y planificacion del siguiente periodo',
            'objetivo' => 'Consolidar resultados, analizar tendencias, actualizar profesiograma y programar evaluaciones del proximo ano',
            'responsable' => 'Responsable SST / Medico con licencia SST',
            'phva' => 'ACTUAR',
            'numeral' => '3.1.4'
        ]
    ];

    /**
     * Obtiene el resumen de actividades de Evaluaciones Medicas para un cliente
     */
    public function getResumenActividades(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        $existentes = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->groupStart()
                ->where('tipo_servicio', 'Evaluaciones Medicas Ocupacionales')
                ->orLike('tipo_servicio', 'Evaluaciones Medicas', 'both')
                ->orLike('tipo_servicio', 'Examenes Medicos', 'both')
                ->orLike('actividad_plandetrabajo', 'evaluacion medica', 'both')
                ->orLike('actividad_plandetrabajo', 'examen medico', 'both')
                ->orLike('actividad_plandetrabajo', 'profesiograma', 'both')
                ->orLike('actividad_plandetrabajo', 'aptitud medica', 'both')
                ->orLike('actividad_plandetrabajo', 'evaluaciones periodicas', 'both')
                ->orLike('actividad_plandetrabajo', 'diagnostico condiciones de salud', 'both')
            ->groupEnd()
            ->countAllResults();

        return [
            'existentes' => $existentes,
            'sugeridas' => count(self::ACTIVIDADES_EVALUACIONES_MEDICAS),
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
        foreach (self::ACTIVIDADES_EVALUACIONES_MEDICAS as $idx => $act) {
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
Tu tarea es personalizar la lista de actividades de Evaluaciones Medicas Ocupacionales segun las instrucciones del usuario.

REGLAS:
1. Si el usuario dice que NO incluya algo, EXCLUYE esas actividades
2. Si menciona periodicidad, ajusta segun corresponda
3. Si pide agregar algo especifico, sugiere actividades nuevas
4. Las evaluaciones medicas deben alinearse con peligros identificados y Res. 2346/2007
5. Responde SOLO en formato JSON valido

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
            log_message('error', 'Error en IA Evaluaciones Medicas: ' . ($response['error'] ?? 'desconocido'));
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
                    'numeral' => '3.1.4',
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
            'audiometria' => ['actividad' => 'Audiometria para trabajadores expuestos a ruido', 'objetivo' => 'Evaluar la funcion auditiva de trabajadores expuestos a niveles de ruido ocupacional', 'phva' => 'HACER', 'mes' => 4],
            'espirometria' => ['actividad' => 'Espirometria para trabajadores expuestos a agentes quimicos', 'objetivo' => 'Evaluar la funcion respiratoria de trabajadores expuestos a particulas y agentes quimicos', 'phva' => 'HACER', 'mes' => 5],
            'visiometria' => ['actividad' => 'Visiometria para trabajadores con exposicion visual', 'objetivo' => 'Evaluar la agudeza visual de trabajadores con uso intensivo de pantallas o exposicion a brillo', 'phva' => 'HACER', 'mes' => 3],
            'osteomuscular' => ['actividad' => 'Valoracion osteomuscular para cargos con riesgo biomecanico', 'objetivo' => 'Identificar alteraciones musculoesqueleticas en trabajadores con exposicion a riesgo biomecanico', 'phva' => 'HACER', 'mes' => 6],
            'alturas' => ['actividad' => 'Evaluacion medica para trabajo en alturas', 'objetivo' => 'Verificar aptitud medica de trabajadores que realizan trabajo en alturas segun Res. 1409/2012', 'phva' => 'HACER', 'mes' => 2],
        ];

        foreach ($keywords as $keyword => $config) {
            if (strpos($instrLower, $keyword) !== false) {
                $actividades[] = [
                    'mes' => $meses[$config['mes']],
                    'mes_num' => $config['mes'],
                    'actividad' => $config['actividad'],
                    'objetivo' => $config['objetivo'],
                    'responsable' => 'Responsable SST / IPS',
                    'phva' => $config['phva'],
                    'numeral' => '3.1.4',
                    'fecha_propuesta' => "{$anio}-" . str_pad($config['mes'], 2, '0', STR_PAD_LEFT) . "-15",
                    'origen' => 'instruccion'
                ];
            }
        }

        return ['agregar' => $actividades, 'excluir' => [], 'modificar' => [], 'explicacion' => ''];
    }

    /**
     * Genera las actividades de Evaluaciones Medicas en el PTA
     */
    public function generarActividades(int $idCliente, int $anio, ?array $actividadesSeleccionadas = null): array
    {
        $creadas = 0;
        $existentes = 0;
        $errores = [];

        $db = \Config\Database::connect();

        $actividades = $actividadesSeleccionadas ?? self::ACTIVIDADES_EVALUACIONES_MEDICAS;

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
                    'tipo_servicio' => 'Evaluaciones Medicas Ocupacionales',
                    'phva_plandetrabajo' => $act['phva'] ?? 'HACER',
                    'numeral_plandetrabajo' => $act['numeral'] ?? '3.1.4',
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
     * Obtiene las actividades de Evaluaciones Medicas del PTA de un cliente
     */
    public function getActividadesCliente(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        return $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->groupStart()
                ->where('tipo_servicio', 'Evaluaciones Medicas Ocupacionales')
                ->orLike('tipo_servicio', 'Evaluaciones Medicas', 'both')
                ->orLike('tipo_servicio', 'Examenes Medicos', 'both')
                ->orLike('actividad_plandetrabajo', 'evaluacion medica', 'both')
                ->orLike('actividad_plandetrabajo', 'examen medico', 'both')
                ->orLike('actividad_plandetrabajo', 'profesiograma', 'both')
                ->orLike('actividad_plandetrabajo', 'aptitud medica', 'both')
                ->orLike('actividad_plandetrabajo', 'evaluaciones periodicas', 'both')
                ->orLike('actividad_plandetrabajo', 'diagnostico condiciones de salud', 'both')
            ->groupEnd()
            ->orderBy('fecha_propuesta', 'ASC')
            ->get()
            ->getResultArray();
    }
}
