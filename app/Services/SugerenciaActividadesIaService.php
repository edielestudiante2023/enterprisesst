<?php

namespace App\Services;

/**
 * Servicio IA (Anthropic / Claude) que sugiere actividades para cumplir un
 * estandar minimo (numeral) del SG-SST, leyendo el contexto del cliente.
 *
 * Usa la API de Anthropic Messages (/v1/messages).
 * Config en .env: ANTHROPIC_API_KEY, ANTHROPIC_MODEL (default claude-sonnet-4-6).
 */
class SugerenciaActividadesIaService
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';
    private const ANTHROPIC_VERSION = '2023-06-01';

    /**
     * @param array $cliente   fila de tbl_clientes
     * @param array $contexto  fila de tbl_cliente_contexto_sst
     * @param array $estandar  fila de tbl_estandares_minimos (item, nombre, criterio, modo_de_verificacion, ciclo_phva)
     * @return array ['ok'=>bool, 'actividades'=>array, 'error'=>string]
     */
    public function sugerir(array $cliente, array $contexto, array $estandar): array
    {
        $item     = trim((string) ($estandar['item'] ?? ''));
        $nombre   = trim((string) ($estandar['nombre'] ?? ''));
        if ($item === '' || $nombre === '') {
            return ['ok' => false, 'error' => 'Estandar (numeral) invalido'];
        }

        $nombreCliente = $cliente['nombre_cliente'] ?? '';
        $trabajadores  = $contexto['total_trabajadores'] ?? ($contexto['numero_trabajadores'] ?? 'No especificado');
        $riesgo        = $contexto['nivel_riesgo_arl'] ?? ($contexto['nivel_riesgo'] ?? 'No especificado');
        $actividadEco  = $contexto['actividad_economica_principal'] ?? ($contexto['actividad_economica'] ?? ($contexto['sector_economico'] ?? 'No especificada'));
        $estandares    = $contexto['estandares_aplicables'] ?? 'No especificado';
        $criterio      = trim((string) ($estandar['criterio'] ?? ''));
        $modoVerif     = trim((string) ($estandar['modo_de_verificacion'] ?? ''));

        $system = "Eres un experto en Seguridad y Salud en el Trabajo (SG-SST) en Colombia, "
            . "experto en la Resolucion 0312 de 2019 (estandares minimos) y el Decreto 1072 de 2015. "
            . "Propones actividades CONCRETAS, realistas y verificables para que una empresa cumpla un estandar minimo especifico, "
            . "ajustadas a su contexto (tamano, nivel de riesgo, sector). "
            . "Respondes UNICAMENTE con JSON valido, sin markdown ni texto fuera del JSON, con esta estructura exacta: "
            . "{\"actividades\":[{\"actividad\":\"...\",\"descripcion\":\"...\",\"fuente_legal\":\"...\",\"evidencia\":\"...\"}]}. "
            . "Genera entre 4 y 7 actividades. 'actividad' es un titulo corto y accionable (verbo + objeto). "
            . "'descripcion' explica como hacerlo en 1-2 frases. 'fuente_legal' cita la norma puntual. "
            . "'evidencia' indica el documento/registro que demuestra el cumplimiento. En espanol profesional, sin emojis.";

        $user = "Estandar minimo a cumplir:\n"
            . "- Numeral: {$item}\n"
            . "- Nombre: {$nombre}\n"
            . ($criterio !== '' ? "- Criterio de evaluacion: {$criterio}\n" : '')
            . ($modoVerif !== '' ? "- Modo de verificacion: {$modoVerif}\n" : '')
            . "\nContexto de la empresa:\n"
            . "- Empresa: {$nombreCliente}\n"
            . "- Numero de trabajadores: {$trabajadores}\n"
            . "- Nivel de riesgo ARL: {$riesgo}\n"
            . "- Actividad economica: {$actividadEco}\n"
            . "- Nivel de estandares aplicables: {$estandares}\n"
            . "- Fecha actual: " . date('Y-m-d') . "\n\n"
            . "Propon las actividades para cumplir ESTE numeral, en el formato JSON especificado.";

        return $this->llamarAnthropic($system, $user, 1800);
    }

    private function llamarAnthropic(string $system, string $user, int $maxTokens = 1500): array
    {
        $apiKey = env('ANTHROPIC_API_KEY', '');
        if (empty($apiKey)) {
            return ['ok' => false, 'error' => 'ANTHROPIC_API_KEY no configurada en .env'];
        }
        $model = env('ANTHROPIC_MODEL', 'claude-sonnet-4-6');

        $payload = [
            'model'      => $model,
            'max_tokens' => $maxTokens,
            'system'     => $system,
            'messages'   => [
                ['role' => 'user', 'content' => $user],
            ],
        ];

        $ch = curl_init(self::ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'content-type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: ' . self::ANTHROPIC_VERSION,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 90,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            log_message('error', 'SugerenciaActividadesIA curl: ' . $err);
            return ['ok' => false, 'error' => 'Fallo de red: ' . $err];
        }
        if ($httpCode !== 200) {
            log_message('error', 'SugerenciaActividadesIA http=' . $httpCode . ' body=' . substr((string) $response, 0, 500));
            return ['ok' => false, 'error' => "Anthropic respondio HTTP {$httpCode}"];
        }

        $json = json_decode((string) $response, true);
        $content = $json['content'][0]['text'] ?? '';
        if ($content === '') {
            return ['ok' => false, 'error' => 'Respuesta IA vacia'];
        }

        // Limpiar posibles fences ```json ... ```
        $content = trim($content);
        $content = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $content);

        $parsed = json_decode($content, true);
        if (!is_array($parsed) || empty($parsed['actividades']) || !is_array($parsed['actividades'])) {
            log_message('error', 'SugerenciaActividadesIA JSON invalido: ' . substr($content, 0, 500));
            return ['ok' => false, 'error' => 'La IA no devolvio actividades en el formato esperado'];
        }

        // Normalizar
        $actividades = [];
        foreach ($parsed['actividades'] as $a) {
            $titulo = trim((string) ($a['actividad'] ?? ''));
            if ($titulo === '') {
                continue;
            }
            $actividades[] = [
                'actividad'    => $titulo,
                'descripcion'  => trim((string) ($a['descripcion'] ?? '')),
                'fuente_legal' => trim((string) ($a['fuente_legal'] ?? '')),
                'evidencia'    => trim((string) ($a['evidencia'] ?? '')),
            ];
        }

        if (empty($actividades)) {
            return ['ok' => false, 'error' => 'La IA no devolvio actividades'];
        }

        return ['ok' => true, 'actividades' => $actividades];
    }
}
