<?php

namespace App\Services;

/**
 * Servicio para generar Objetivos del SG-SST
 * Estándar 2.2.1 - Resolución 0312/2019
 *
 * PARTE 1 del módulo de 3 partes:
 * - IA genera sugerencias de objetivos
 * - Consultor refina y selecciona
 * - Se guardan en tbl_pta_cliente con tipo_servicio = 'Objetivos SG-SST'
 */
class ObjetivosSgsstService
{
    protected const TIPO_SERVICIO = 'Objetivos SG-SST';

    /**
     * Límites fijos de objetivos según estándares
     */
    public const LIMITES_OBJETIVOS = [
        7 => 3,   // Básico: 3 objetivos
        21 => 4,  // Intermedio: 4 objetivos
        60 => 6   // Avanzado: 6 objetivos
    ];

    /**
     * Objetivos base del SG-SST (aplicables a cualquier empresa)
     */
    public const OBJETIVOS_BASE = [
        [
            'objetivo' => 'Reducir la accidentalidad laboral',
            'descripcion' => 'Disminuir la frecuencia y severidad de accidentes de trabajo mediante acciones preventivas',
            'meta' => 'Reducir en un 10% la tasa de accidentalidad respecto al año anterior',
            'indicador_sugerido' => 'Índice de Frecuencia de Accidentes',
            'phva' => 'PLANEAR',
            'responsable' => 'Responsable SST',
            'numeral' => '2.2.1'
        ],
        [
            'objetivo' => 'Prevenir enfermedades laborales',
            'descripcion' => 'Implementar medidas de promoción y prevención para evitar enfermedades de origen laboral',
            'meta' => 'Mantener la tasa de enfermedad laboral por debajo del promedio del sector',
            'indicador_sugerido' => 'Tasa de Enfermedad Laboral',
            'phva' => 'PLANEAR',
            'responsable' => 'Responsable SST',
            'numeral' => '2.2.1'
        ],
        [
            'objetivo' => 'Cumplir los requisitos legales en SST',
            'descripcion' => 'Asegurar el cumplimiento de la normatividad vigente en materia de seguridad y salud en el trabajo',
            'meta' => 'Alcanzar el 100% de cumplimiento en la autoevaluación de estándares mínimos',
            'indicador_sugerido' => 'Porcentaje de Cumplimiento Estándares',
            'phva' => 'PLANEAR',
            'responsable' => 'Responsable SST',
            'numeral' => '2.2.1'
        ],
        [
            'objetivo' => 'Fortalecer la cultura de autocuidado',
            'descripcion' => 'Promover comportamientos seguros y hábitos de autocuidado en todos los trabajadores',
            'meta' => 'Capacitar al 100% del personal en temas de SST según cronograma',
            'indicador_sugerido' => 'Cobertura de Capacitación SST',
            'phva' => 'HACER',
            'responsable' => 'Responsable SST',
            'numeral' => '2.2.1'
        ],
        [
            'objetivo' => 'Gestionar eficazmente los peligros identificados',
            'descripcion' => 'Implementar controles para los peligros prioritarios de la matriz de riesgos',
            'meta' => 'Intervenir el 80% de los peligros con riesgo alto o muy alto',
            'indicador_sugerido' => 'Porcentaje de Peligros Intervenidos',
            'phva' => 'HACER',
            'responsable' => 'Responsable SST',
            'numeral' => '2.2.1'
        ],
        [
            'objetivo' => 'Mejorar la respuesta ante emergencias',
            'descripcion' => 'Fortalecer la capacidad de respuesta de la organización ante situaciones de emergencia',
            'meta' => 'Realizar al menos 2 simulacros de emergencia al año con participación del 90% del personal',
            'indicador_sugerido' => 'Participación en Simulacros',
            'phva' => 'HACER',
            'responsable' => 'Brigada de Emergencias',
            'numeral' => '2.2.1'
        ]
    ];

    /**
     * Obtiene el límite de objetivos según estándares del cliente
     */
    public function getLimiteObjetivos(int $estandares): int
    {
        if ($estandares <= 7) return self::LIMITES_OBJETIVOS[7];
        if ($estandares <= 21) return self::LIMITES_OBJETIVOS[21];
        return self::LIMITES_OBJETIVOS[60];
    }

    /**
     * Obtiene el resumen de objetivos para un cliente
     */
    public function getResumenObjetivos(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        $existentes = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('tipo_servicio', self::TIPO_SERVICIO)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->countAllResults();

        return [
            'existentes' => $existentes,
            'sugeridos' => count(self::OBJETIVOS_BASE),
            'completo' => $existentes >= 3
        ];
    }

    /**
     * Preview de objetivos que se generarían
     * Personaliza según contexto del cliente e instrucciones
     */
    public function previewObjetivos(int $idCliente, int $anio, ?array $contexto = null, string $instrucciones = ''): array
    {
        $estandares = $contexto['estandares_aplicables'] ?? 60;
        $limite = $this->getLimiteObjetivos($estandares);

        // Tomar solo los objetivos que corresponden según el límite
        $objetivosBase = array_slice(self::OBJETIVOS_BASE, 0, $limite);

        $objetivos = [];
        foreach ($objetivosBase as $idx => $obj) {
            $objetivos[] = [
                'indice' => $idx,
                'objetivo' => $obj['objetivo'],
                'descripcion' => $obj['descripcion'],
                'meta' => $obj['meta'],
                'indicador_sugerido' => $obj['indicador_sugerido'],
                'phva' => $obj['phva'],
                'responsable' => $obj['responsable'],
                'numeral' => $obj['numeral'],
                'fecha_propuesta' => "{$anio}-01-01",
                'origen' => 'base'
            ];
        }

        $explicacionIA = '';

        // Si hay instrucciones, usar IA para personalizar
        if (!empty($instrucciones)) {
            $resultadoIA = $this->personalizarConIA($objetivos, $instrucciones, $contexto, $anio, $limite);
            $objetivos = $resultadoIA['objetivos'];
            $explicacionIA = $resultadoIA['explicacion'] ?? '';
        }

        return [
            'objetivos' => $objetivos,
            'total' => count($objetivos),
            'limite' => $limite,
            'estandares' => $estandares,
            'anio' => $anio,
            'instrucciones_procesadas' => !empty($instrucciones),
            'explicacion_ia' => $explicacionIA
        ];
    }

    /**
     * Personaliza objetivos con IA según instrucciones del consultor
     */
    protected function personalizarConIA(array $objetivos, string $instrucciones, ?array $contexto, int $anio, int $limite): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            return ['objetivos' => $objetivos, 'explicacion' => ''];
        }

        $objetivosTexto = "";
        foreach ($objetivos as $idx => $obj) {
            $objetivosTexto .= "{$idx}. {$obj['objetivo']}\n   Meta: {$obj['meta']}\n";
        }

        $contextoTexto = "";
        if ($contexto) {
            $contextoTexto = "CONTEXTO DE LA EMPRESA:\n";
            $contextoTexto .= "- Actividad: " . ($contexto['actividad_economica_principal'] ?? 'No especificada') . "\n";
            $contextoTexto .= "- Nivel riesgo: " . ($contexto['nivel_riesgo_arl'] ?? 'No especificado') . "\n";
            $contextoTexto .= "- Trabajadores: " . ($contexto['total_trabajadores'] ?? 'No especificado') . "\n";
            $contextoTexto .= "- Estándares: " . ($contexto['estandares_aplicables'] ?? 60) . "\n";
        }

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia.
Tu tarea es personalizar los objetivos del SG-SST según las instrucciones del consultor.

REGLAS:
1. Máximo {$limite} objetivos (límite según estándares aplicables)
2. Los objetivos deben ser MEDIBLES, ALCANZABLES y con PLAZO DEFINIDO
3. Cada objetivo debe tener una meta cuantificable
4. Responde SOLO en formato JSON válido

FORMATO DE RESPUESTA (JSON):
{
  \"objetivos\": [
    {
      \"objetivo\": \"Título del objetivo\",
      \"descripcion\": \"Descripción detallada\",
      \"meta\": \"Meta cuantificable\",
      \"indicador_sugerido\": \"Nombre del indicador\",
      \"phva\": \"PLANEAR|HACER|VERIFICAR|ACTUAR\",
      \"responsable\": \"Cargo responsable\"
    }
  ],
  \"explicacion\": \"Breve explicación de los cambios realizados\"
}";

        $userPrompt = "AÑO: {$anio}\nLÍMITE: {$limite} objetivos\n\n";
        $userPrompt .= $contextoTexto . "\n";
        $userPrompt .= "OBJETIVOS BASE:\n{$objetivosTexto}\n";
        $userPrompt .= "INSTRUCCIONES DEL CONSULTOR:\n\"{$instrucciones}\"\n\n";
        $userPrompt .= "Personaliza los objetivos según las instrucciones. Si no hay cambios necesarios, devuelve los objetivos originales.";

        $response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey);

        if (!$response['success']) {
            log_message('error', 'Error IA Objetivos: ' . ($response['error'] ?? 'desconocido'));
            return ['objetivos' => $objetivos, 'explicacion' => ''];
        }

        return $this->procesarRespuestaIA($response['contenido'], $objetivos, $anio, $limite);
    }

    /**
     * Procesa la respuesta JSON de la IA
     */
    protected function procesarRespuestaIA(string $contenidoIA, array $objetivosBase, int $anio, int $limite): array
    {
        $contenidoIA = preg_replace('/```json\s*/', '', $contenidoIA);
        $contenidoIA = preg_replace('/```\s*/', '', $contenidoIA);

        $respuesta = json_decode($contenidoIA, true);
        if (!$respuesta || !isset($respuesta['objetivos'])) {
            return ['objetivos' => $objetivosBase, 'explicacion' => 'No se pudieron procesar las instrucciones'];
        }

        $objetivos = [];
        $objetivosIA = array_slice($respuesta['objetivos'], 0, $limite);

        foreach ($objetivosIA as $idx => $obj) {
            $objetivos[] = [
                'indice' => $idx,
                'objetivo' => $obj['objetivo'] ?? 'Objetivo ' . ($idx + 1),
                'descripcion' => $obj['descripcion'] ?? '',
                'meta' => $obj['meta'] ?? '',
                'indicador_sugerido' => $obj['indicador_sugerido'] ?? '',
                'phva' => $obj['phva'] ?? 'PLANEAR',
                'responsable' => $obj['responsable'] ?? 'Responsable SST',
                'numeral' => '2.2.1',
                'fecha_propuesta' => "{$anio}-01-01",
                'origen' => 'ia',
                'generado_por_ia' => true
            ];
        }

        return [
            'objetivos' => $objetivos,
            'explicacion' => $respuesta['explicacion'] ?? ''
        ];
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
            'max_tokens' => 2000
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
     * Genera los objetivos en la BD (tbl_pta_cliente)
     */
    public function generarObjetivos(int $idCliente, int $anio, array $objetivosSeleccionados): array
    {
        $creados = 0;
        $existentes = 0;
        $errores = [];

        $db = \Config\Database::connect();

        foreach ($objetivosSeleccionados as $obj) {
            // Verificar si ya existe un objetivo similar
            $existe = $db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('tipo_servicio', self::TIPO_SERVICIO)
                ->where('YEAR(fecha_propuesta)', $anio)
                ->like('actividad_plandetrabajo', substr($obj['objetivo'], 0, 30), 'both')
                ->countAllResults();

            if ($existe > 0) {
                $existentes++;
                continue;
            }

            try {
                $fechaPropuesta = $obj['fecha_propuesta'] ?? "{$anio}-01-01";
                $semana = (int)date('W', strtotime($fechaPropuesta));

                // Construir descripción completa para la actividad
                $actividadCompleta = $obj['objetivo'];
                if (!empty($obj['descripcion'])) {
                    $actividadCompleta .= " - " . $obj['descripcion'];
                }
                if (!empty($obj['meta'])) {
                    $actividadCompleta .= " | Meta: " . $obj['meta'];
                }

                $db->table('tbl_pta_cliente')->insert([
                    'id_cliente' => $idCliente,
                    'tipo_servicio' => self::TIPO_SERVICIO,
                    'phva_plandetrabajo' => $obj['phva'] ?? 'PLANEAR',
                    'numeral_plandetrabajo' => $obj['numeral'] ?? '2.2.1',
                    'actividad_plandetrabajo' => $actividadCompleta,
                    'responsable_sugerido_plandetrabajo' => $obj['responsable'] ?? 'Responsable SST',
                    'fecha_propuesta' => $fechaPropuesta,
                    'estado_actividad' => 'ABIERTA',
                    'porcentaje_avance' => 0,
                    'semana' => $semana
                ]);
                $creados++;
            } catch (\Exception $e) {
                $errores[] = "Error en '{$obj['objetivo']}': " . $e->getMessage();
            }
        }

        return [
            'creados' => $creados,
            'existentes' => $existentes,
            'errores' => $errores,
            'total' => count($objetivosSeleccionados)
        ];
    }

    /**
     * Obtiene los objetivos existentes del cliente
     */
    public function getObjetivosCliente(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        return $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('tipo_servicio', self::TIPO_SERVICIO)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->orderBy('fecha_propuesta', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Elimina un objetivo específico
     */
    public function eliminarObjetivo(int $idPta): bool
    {
        $db = \Config\Database::connect();

        return $db->table('tbl_pta_cliente')
            ->where('id_ptacliente', $idPta)
            ->where('tipo_servicio', self::TIPO_SERVICIO)
            ->delete();
    }
}
