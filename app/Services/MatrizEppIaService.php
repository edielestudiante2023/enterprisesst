<?php

namespace App\Services;

/**
 * Servicio IA para autocompletar ítems del catálogo maestro EPP.
 *
 * Usa OpenAI (gpt-4o-mini por defecto) con JSON response_format estricto.
 * Configuración desde .env (OPENAI_API_KEY, OPENAI_MODEL).
 */
class MatrizEppIaService
{
    public function autocompletar(string $elemento, string $categoriaNombre = '', string $categoriaTipo = 'EPP'): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            return ['ok' => false, 'error' => 'OPENAI_API_KEY no configurada'];
        }

        $elemento = trim($elemento);
        if ($elemento === '') {
            return ['ok' => false, 'error' => 'elemento vacio'];
        }

        $systemPrompt = "Eres un asistente SST experto en EPP y dotacion laboral en Colombia. "
            . "Respondes UNICAMENTE con un JSON valido que contenga exactamente 5 claves: "
            . "norma, mantenimiento, frecuencia_cambio, motivos_cambio, momentos_uso. "
            . "Cada valor es un string en espanol profesional, sin emojis. "
            . "Cita normas NTC, ANSI, ISO, NIOSH o legales colombianas (CST Art. 230, Res 2400/1979, Dec 1072/2015) cuando apliquen. "
            . "Si no estas seguro de una norma, omitela en vez de inventar.";

        $userPrompt = "Elemento: {$elemento}\n"
            . "Categoria: {$categoriaNombre} ({$categoriaTipo})\n\n"
            . "Completa los 5 campos tecnicos para este elemento de " . ($categoriaTipo === 'DOTACION' ? 'dotacion' : 'proteccion personal') . ". "
            . "Formato obligatorio JSON: {\"norma\":\"...\",\"mantenimiento\":\"...\",\"frecuencia_cambio\":\"...\",\"motivos_cambio\":\"...\",\"momentos_uso\":\"...\"}";

        $data = [
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'temperature' => 0.4,
            'max_tokens'  => 800,
            'response_format' => ['type' => 'json_object'],
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 60,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            log_message('error', 'MatrizEppIa curl: ' . $err);
            return ['ok' => false, 'error' => 'Fallo de red: ' . $err];
        }
        if ($httpCode !== 200) {
            log_message('error', 'MatrizEppIa http=' . $httpCode . ' body=' . substr((string)$response, 0, 500));
            return ['ok' => false, 'error' => "OpenAI respondio HTTP {$httpCode}"];
        }

        $json = json_decode((string)$response, true);
        $content = $json['choices'][0]['message']['content'] ?? '';
        if ($content === '') {
            return ['ok' => false, 'error' => 'Respuesta IA vacia'];
        }

        $parsed = json_decode($content, true);
        if (!is_array($parsed)) {
            log_message('error', 'MatrizEppIa JSON invalido: ' . substr($content, 0, 500));
            return ['ok' => false, 'error' => 'JSON invalido en respuesta'];
        }

        $claves = ['norma', 'mantenimiento', 'frecuencia_cambio', 'motivos_cambio', 'momentos_uso'];
        $out = [];
        foreach ($claves as $k) {
            $out[$k] = is_string($parsed[$k] ?? null) ? trim($parsed[$k]) : '';
        }

        log_message('info', 'MatrizEppIa OK elemento="' . $elemento . '"');
        return ['ok' => true, 'data' => $out];
    }
}
