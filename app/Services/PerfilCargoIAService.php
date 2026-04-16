<?php

namespace App\Services;

/**
 * Servicio IA para el modulo Perfiles de Cargo.
 *
 * 3 capacidades:
 *   - generarObjetivoCargo(): genera el objetivo del cargo a partir de las funciones
 *   - generarIndicadores(): propone 3-5 indicadores a partir de funciones + objetivo
 *   - sugerirFunciones(): propone 8-12 funciones especificas del cargo
 *
 * Usa OpenAI con response_format=json_object para outputs estructurados.
 * Modelo: env('OPENAI_MODEL', 'gpt-4o-mini').
 *
 * Ver: docs/MODULO_PERFILES_CARGO/ARQUITECTURA.md §6
 */
class PerfilCargoIAService
{
    private const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    public function generarObjetivoCargo(string $nombreCargo, array $funciones, string $area = ''): array
    {
        $nombreCargo = trim($nombreCargo);
        $funciones = array_values(array_filter(array_map('trim', $funciones), fn($f) => $f !== ''));
        if ($nombreCargo === '') return ['ok' => false, 'error' => 'nombre_cargo vacio'];
        if (empty($funciones))   return ['ok' => false, 'error' => 'funciones vacias'];

        $system = "Eres un experto en gestion humana y descripcion de cargos en Colombia. "
            . "Respondes UNICAMENTE con JSON valido con una sola clave: {\"objetivo\":\"...\"}. "
            . "El objetivo debe ser un unico parrafo de 40-70 palabras, en espanol profesional, "
            . "sin listas, sin emojis, sin introducciones tipo 'El objetivo de este cargo es...'. "
            . "Debe sintetizar la razon de ser del cargo con enfoque en resultado, no en tareas.";

        $user = "Cargo: {$nombreCargo}\n"
            . ($area !== '' ? "Area: {$area}\n" : '')
            . "Fecha actual: " . date('Y-m-d') . "\n\n"
            . "Funciones del cargo:\n"
            . "- " . implode("\n- ", $funciones) . "\n\n"
            . "Genera el objetivo del cargo. Formato JSON: {\"objetivo\":\"...\"}";

        $resp = $this->llamarOpenAI($system, $user, 500);
        if (!$resp['ok']) return $resp;

        $objetivo = is_string($resp['data']['objetivo'] ?? null) ? trim($resp['data']['objetivo']) : '';
        if ($objetivo === '') return ['ok' => false, 'error' => 'IA no devolvio objetivo'];
        return ['ok' => true, 'objetivo' => $objetivo];
    }

    public function generarIndicadores(string $nombreCargo, array $funciones, string $objetivo = ''): array
    {
        $nombreCargo = trim($nombreCargo);
        $funciones = array_values(array_filter(array_map('trim', $funciones), fn($f) => $f !== ''));
        if ($nombreCargo === '') return ['ok' => false, 'error' => 'nombre_cargo vacio'];
        if (empty($funciones))   return ['ok' => false, 'error' => 'funciones vacias'];

        $system = "Eres un experto en diseno de indicadores de gestion para cargos en Colombia. "
            . "Respondes UNICAMENTE con JSON valido con esta estructura exacta: "
            . "{\"indicadores\":[{\"objetivo_proceso\":\"...\",\"nombre_indicador\":\"...\",\"formula\":\"...\",\"periodicidad\":\"mensual|bimestral|trimestral|semestral|anual\",\"meta\":\"...\",\"ponderacion\":\"...\",\"objetivo_calidad_impacta\":\"...\"}]}. "
            . "Genera entre 3 y 5 indicadores realistas, medibles y alineados con las funciones dadas. "
            . "La formula debe ser un calculo concreto tipo '(A / B) * 100'. "
            . "La meta debe incluir un numero y unidad (ej: '>= 95 %'). "
            . "La ponderacion expresada en porcentaje. "
            . "Sin emojis, sin listas markdown, sin texto fuera del JSON.";

        $user = "Cargo: {$nombreCargo}\n"
            . ($objetivo !== '' ? "Objetivo del cargo: {$objetivo}\n" : '')
            . "Fecha actual: " . date('Y-m-d') . "\n\n"
            . "Funciones del cargo:\n"
            . "- " . implode("\n- ", $funciones) . "\n\n"
            . "Genera 3-5 indicadores en el formato JSON especificado.";

        $resp = $this->llamarOpenAI($system, $user, 1500);
        if (!$resp['ok']) return $resp;

        $indicadores = $resp['data']['indicadores'] ?? null;
        if (!is_array($indicadores) || empty($indicadores)) {
            return ['ok' => false, 'error' => 'IA no devolvio indicadores'];
        }

        // Normalizacion y saneado
        $out = [];
        $periodicidadesValidas = ['mensual','bimestral','trimestral','semestral','anual'];
        foreach ($indicadores as $i => $ind) {
            if (!is_array($ind)) continue;
            $periodicidad = strtolower(trim((string)($ind['periodicidad'] ?? 'mensual')));
            if (!in_array($periodicidad, $periodicidadesValidas, true)) $periodicidad = 'mensual';
            $out[] = [
                'objetivo_proceso'         => trim((string)($ind['objetivo_proceso'] ?? '')),
                'nombre_indicador'         => trim((string)($ind['nombre_indicador'] ?? '')),
                'formula'                  => trim((string)($ind['formula'] ?? '')),
                'periodicidad'             => $periodicidad,
                'meta'                     => trim((string)($ind['meta'] ?? '')),
                'ponderacion'              => trim((string)($ind['ponderacion'] ?? '')),
                'objetivo_calidad_impacta' => trim((string)($ind['objetivo_calidad_impacta'] ?? '')),
                'orden'                    => $i + 1,
            ];
        }

        return ['ok' => true, 'indicadores' => $out];
    }

    public function sugerirFunciones(string $nombreCargo, string $area = '', ?string $objetivo = null): array
    {
        $nombreCargo = trim($nombreCargo);
        if ($nombreCargo === '') return ['ok' => false, 'error' => 'nombre_cargo vacio'];

        $system = "Eres un experto en descripcion de cargos en Colombia. "
            . "Respondes UNICAMENTE con JSON valido con esta estructura: "
            . "{\"funciones\":[\"funcion 1\",\"funcion 2\",...]}. "
            . "Genera entre 8 y 12 funciones especificas del cargo (no generales ni de SST). "
            . "Cada funcion inicia con un verbo en infinitivo (Elaborar, Analizar, Registrar, Gestionar...). "
            . "Cada funcion es una frase concreta de 10-25 palabras. "
            . "No incluyas funciones SST ni de Talento Humano transversales. "
            . "Sin emojis, sin numeracion dentro de la frase, sin markdown.";

        $user = "Cargo: {$nombreCargo}\n"
            . ($area !== '' ? "Area: {$area}\n" : '')
            . ($objetivo ? "Objetivo del cargo: {$objetivo}\n" : '')
            . "Fecha actual: " . date('Y-m-d') . "\n\n"
            . "Genera entre 8 y 12 funciones especificas en el formato JSON especificado.";

        $resp = $this->llamarOpenAI($system, $user, 1000);
        if (!$resp['ok']) return $resp;

        $funciones = $resp['data']['funciones'] ?? null;
        if (!is_array($funciones) || empty($funciones)) {
            return ['ok' => false, 'error' => 'IA no devolvio funciones'];
        }

        $out = [];
        foreach ($funciones as $f) {
            if (is_string($f) && trim($f) !== '') $out[] = trim($f);
        }
        return ['ok' => true, 'funciones' => $out];
    }

    private function llamarOpenAI(string $systemPrompt, string $userPrompt, int $maxTokens = 1000): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            return ['ok' => false, 'error' => 'OPENAI_API_KEY no configurada'];
        }

        $payload = [
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'temperature' => 0.4,
            'max_tokens'  => $maxTokens,
            'response_format' => ['type' => 'json_object'],
        ];

        $ch = curl_init(self::ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 90,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            log_message('error', 'PerfilCargoIA curl: ' . $err);
            return ['ok' => false, 'error' => 'Fallo de red: ' . $err];
        }
        if ($httpCode !== 200) {
            log_message('error', 'PerfilCargoIA http=' . $httpCode . ' body=' . substr((string)$response, 0, 500));
            return ['ok' => false, 'error' => "OpenAI respondio HTTP {$httpCode}"];
        }

        $json = json_decode((string)$response, true);
        $content = $json['choices'][0]['message']['content'] ?? '';
        if ($content === '') {
            return ['ok' => false, 'error' => 'Respuesta IA vacia'];
        }

        $parsed = json_decode($content, true);
        if (!is_array($parsed)) {
            log_message('error', 'PerfilCargoIA content no parseable: ' . substr($content, 0, 500));
            return ['ok' => false, 'error' => 'IA devolvio JSON invalido'];
        }

        return ['ok' => true, 'data' => $parsed];
    }
}
