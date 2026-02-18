<?php

namespace App\Services;

/**
 * Servicio de asistente IA para completar actividades del PTA rápidamente.
 * El consultor escribe una descripción corta y la IA sugiere los campos completos.
 */
class PtaIAService
{
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';
    protected float $temperature = 0.4;
    protected int $maxTokens = 500;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->model = env('OPENAI_MODEL', 'gpt-4o-mini');

        if (empty($this->apiKey)) {
            log_message('error', 'PtaIAService: OPENAI_API_KEY no configurada en .env');
        }
    }

    /**
     * Recibe descripción corta + contexto del cliente y devuelve campos sugeridos para el PTA.
     */
    public function completarActividad(string $descripcion, array $cliente, array $contexto): array
    {
        $razonSocial = $cliente['nombre_cliente'] ?? $cliente['razon_social'] ?? 'La empresa';
        $nivelRiesgo = $contexto['nivel_riesgo_arl'] ?? 'No definido';
        $actividadEconomica = $contexto['sector_economico'] ?? 'No definida';
        $totalTrabajadores = $contexto['total_trabajadores'] ?? 'No definido';
        $estandares = $contexto['estandares_aplicables'] ?? 60;
        $peligros = '';
        if (!empty($contexto['peligros_identificados'])) {
            $arr = is_array($contexto['peligros_identificados'])
                ? $contexto['peligros_identificados']
                : json_decode($contexto['peligros_identificados'], true);
            if (is_array($arr)) {
                $peligros = implode(', ', $arr);
            }
        }

        $anio = date('Y');

        $systemPrompt = "Eres un asistente experto en Seguridad y Salud en el Trabajo (SST) en Colombia.
Tu tarea: dada una descripción breve de actividad y el contexto de una empresa, devuelves un JSON con los campos para el Plan de Trabajo Anual (PTA) según la Resolución 0312 de 2019 y el Decreto 1072 de 2015.

Reglas:
1. El campo 'phva' debe ser exactamente uno de: P, H, V, A (Planear, Hacer, Verificar, Actuar)
2. El campo 'numeral' debe ser el numeral de la Resolución 0312/2019 o artículo del Decreto 1072/2015 que más aplique
3. El campo 'actividad' debe ser una redacción profesional de la actividad (2-3 líneas máximo), específica para la empresa
4. El campo 'responsable_sugerido' debe indicar quién ejecuta (ej: Responsable del SG-SST, Empleador, ARL, Consultor SST, COPASST, Brigada de emergencias, etc.)
5. El campo 'mes_sugerido' debe ser un número 1-12 indicando el mes recomendado para ejecutar en {$anio}
6. Responde ÚNICAMENTE con el JSON, sin explicaciones ni markdown";

        $userPrompt = "EMPRESA: {$razonSocial}
- Actividad económica: {$actividadEconomica}
- Nivel de riesgo ARL: {$nivelRiesgo}
- Total trabajadores: {$totalTrabajadores}
- Estándares aplicables: {$estandares}";

        if ($peligros) {
            $userPrompt .= "\n- Peligros identificados: {$peligros}";
        }

        $userPrompt .= "\n\nACTIVIDAD: \"{$descripcion}\"
AÑO: {$anio}

Responde SOLO con JSON:
{
  \"phva\": \"P|H|V|A\",
  \"numeral\": \"numeral aplicable\",
  \"actividad\": \"descripción profesional\",
  \"responsable_sugerido\": \"responsable\",
  \"mes_sugerido\": 1
}";

        return $this->llamarAPI($systemPrompt, $userPrompt);
    }

    /**
     * Llama a la API de OpenAI y parsea la respuesta JSON.
     */
    protected function llamarAPI(string $systemPrompt, string $userPrompt): array
    {
        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
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
            $errorMsg = $result['error']['message'] ?? 'Error HTTP ' . $httpCode;
            return ['success' => false, 'error' => $errorMsg];
        }

        $contenido = $result['choices'][0]['message']['content'] ?? null;
        if (!$contenido) {
            return ['success' => false, 'error' => 'Respuesta vacía de la API'];
        }

        // Limpiar posibles backticks markdown (```json ... ```)
        $contenido = trim($contenido);
        $contenido = preg_replace('/^```(?:json)?\s*/i', '', $contenido);
        $contenido = preg_replace('/\s*```$/', '', $contenido);

        $campos = json_decode($contenido, true);
        if (!$campos || !isset($campos['phva'])) {
            return ['success' => false, 'error' => 'La IA no devolvió un JSON válido', 'raw' => $contenido];
        }

        return [
            'success' => true,
            'campos' => $campos,
            'tokens' => $result['usage']['total_tokens'] ?? 0
        ];
    }
}
