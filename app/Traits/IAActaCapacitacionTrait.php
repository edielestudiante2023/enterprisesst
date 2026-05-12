<?php

namespace App\Traits;

/**
 * Trait con la logica de generacion de OBJETIVOS y CONTENIDO/RESUMEN de
 * actas de capacitacion usando OpenAI (gpt-4o-mini).
 *
 * Se usa en ActaCapacitacionController (consultor) y MiembroActaCapacitacionController
 * (miembro de comite).
 */
trait IAActaCapacitacionTrait
{
    /**
     * Llama a OpenAI para generar OBJETIVOS de una capacitacion a partir del tema.
     * Devuelve array ['success' => bool, 'contenido' => string, 'error' => string|null].
     */
    private function generarObjetivosConIA(string $tema): array
    {
        $tema = trim($tema);
        if ($tema === '') {
            return ['success' => false, 'error' => 'El tema es obligatorio'];
        }

        $systemPrompt = "Eres un consultor experto en Seguridad y Salud en el Trabajo (SST) en Colombia. "
            . "Tu tarea es redactar OBJETIVOS de capacitacion claros, especificos y profesionales, "
            . "alineados con el SG-SST (Resolucion 0312 de 2019, Decreto 1072 de 2015). "
            . "Responde SIEMPRE en texto plano, sin markdown ni asteriscos ni numeracion adicional. "
            . "Devuelve 2 o 3 objetivos redactados en un solo parrafo natural, separados por punto y seguido. "
            . "Sin encabezados, sin viñetas, sin texto introductorio. "
            . "Tono profesional pero claro. Maximo 80 palabras en total.";

        $userPrompt = "Tema de la capacitacion: {$tema}\n\nGenera los objetivos.";

        return $this->callOpenAIActaCap($systemPrompt, $userPrompt, 300);
    }

    /**
     * Llama a OpenAI para generar el CONTENIDO/RESUMEN de una capacitacion
     * a partir del tema (y opcional objetivos como contexto).
     */
    private function generarContenidoConIA(string $tema, ?string $objetivos = null): array
    {
        $tema = trim($tema);
        if ($tema === '') {
            return ['success' => false, 'error' => 'El tema es obligatorio'];
        }

        $systemPrompt = "Eres un consultor experto en Seguridad y Salud en el Trabajo (SST) en Colombia. "
            . "Tu tarea es redactar un RESUMEN del CONTENIDO desarrollado en una capacitacion, "
            . "alineado con el SG-SST (Resolucion 0312 de 2019, Decreto 1072 de 2015). "
            . "Responde SIEMPRE en texto plano, sin markdown ni asteriscos ni numeracion. "
            . "Estructura: 1 parrafo de introduccion + 1 parrafo con los 3 o 4 ejes tematicos cubiertos + 1 parrafo de cierre. "
            . "Sin encabezados, sin listas con viñetas, redactado como texto corrido natural. "
            . "Maximo 200 palabras en total. Tono profesional.";

        $contextoObjetivos = '';
        $objetivos = trim((string)$objetivos);
        if ($objetivos !== '') {
            $contextoObjetivos = "\nObjetivos definidos: {$objetivos}";
        }

        $userPrompt = "Tema de la capacitacion: {$tema}{$contextoObjetivos}\n\nGenera el resumen del contenido.";

        return $this->callOpenAIActaCap($systemPrompt, $userPrompt, 600);
    }

    /**
     * Llamada generica a OpenAI Chat Completions API.
     */
    private function callOpenAIActaCap(string $systemPrompt, string $userPrompt, int $maxTokens = 400): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'OPENAI_API_KEY no configurada en el servidor'];
        }

        $data = [
            'model'       => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages'    => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'temperature' => 0.7,
            'max_tokens'  => $maxTokens,
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            log_message('error', 'CURL error en IAActaCap: ' . $err);
            return ['success' => false, 'error' => 'Error de conexion: ' . $err];
        }

        $result = json_decode($response, true);
        if ($httpCode !== 200) {
            return ['success' => false, 'error' => $result['error']['message'] ?? ('Error HTTP ' . $httpCode)];
        }

        if (isset($result['choices'][0]['message']['content'])) {
            return [
                'success'   => true,
                'contenido' => trim($result['choices'][0]['message']['content']),
            ];
        }

        return ['success' => false, 'error' => 'Respuesta inesperada de OpenAI'];
    }
}
