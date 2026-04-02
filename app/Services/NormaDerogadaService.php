<?php

namespace App\Services;

use App\Models\NormaDerogadaModel;
use App\Libraries\NormasVigentes;

/**
 * Servicio para parsear y guardar normas derogadas reportadas por consultores.
 * Usa OpenAI para extraer estructura del texto libre.
 */
class NormaDerogadaService
{
    protected string $apiKey;
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
    }

    /**
     * Parsea texto libre con IA y guarda la norma derogada en BD.
     *
     * @param string $textoLibre Lo que escribió el consultor
     * @param string $reportadoPor Nombre del consultor
     * @return array ['success' => bool, 'norma_derogada' => string, 'norma_reemplazo' => string, 'error' => string]
     */
    public function parsearYGuardar(string $textoLibre, string $reportadoPor): array
    {
        // 1. Parsear con IA
        $parsed = $this->parsearConIA($textoLibre);

        if (!$parsed['success']) {
            return $parsed;
        }

        $normaDerogada = $parsed['norma_derogada'];
        $normaReemplazo = $parsed['norma_reemplazo'];

        // 2. Verificar duplicado
        $model = new NormaDerogadaModel();
        if ($model->existeNorma($normaDerogada)) {
            return [
                'success' => false,
                'error' => "La norma '{$normaDerogada}' ya está registrada como derogada."
            ];
        }

        // 3. Guardar
        $model->insert([
            'norma_derogada' => $normaDerogada,
            'norma_reemplazo' => $normaReemplazo,
            'texto_original' => $textoLibre,
            'reportado_por' => $reportadoPor,
            'fecha_reporte' => date('Y-m-d H:i:s'),
            'activo' => 1
        ]);

        // 4. Limpiar cache
        NormasVigentes::limpiarCache();

        return [
            'success' => true,
            'norma_derogada' => $normaDerogada,
            'norma_reemplazo' => $normaReemplazo
        ];
    }

    /**
     * Usa OpenAI para extraer norma derogada y reemplazo del texto libre
     */
    protected function parsearConIA(string $textoLibre): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'OPENAI_API_KEY no configurada'];
        }

        $data = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Eres un asistente que extrae información normativa colombiana. Del texto del usuario, extrae la norma que fue derogada y la norma que la reemplaza. Normaliza los nombres (ej: "la 652 del 2012" → "Resolución 652 de 2012"). Responde SOLO en JSON válido con este formato exacto: {"norma_derogada": "Tipo Número de Año", "norma_reemplazo": "Tipo Número de Año"}. Si el usuario no menciona la norma de reemplazo, coloca "No especificada". Si no puedes identificar la norma derogada, responde: {"error": "No se pudo identificar la norma derogada"}'
                ],
                [
                    'role' => 'user',
                    'content' => $textoLibre
                ]
            ],
            'temperature' => 0.1,
            'max_tokens' => 200
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
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => "Error de conexión: {$curlError}"];
        }

        if ($httpCode !== 200) {
            $result = json_decode($response, true);
            $msg = $result['error']['message'] ?? "Error HTTP {$httpCode}";
            return ['success' => false, 'error' => $msg];
        }

        $result = json_decode($response, true);
        $content = $result['choices'][0]['message']['content'] ?? '';

        // Limpiar posibles backticks de markdown
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        $content = trim($content);

        $parsed = json_decode($content, true);

        if (!$parsed) {
            return ['success' => false, 'error' => 'No se pudo interpretar la respuesta de la IA'];
        }

        if (isset($parsed['error'])) {
            return ['success' => false, 'error' => $parsed['error']];
        }

        if (empty($parsed['norma_derogada'])) {
            return ['success' => false, 'error' => 'No se pudo identificar la norma derogada'];
        }

        return [
            'success' => true,
            'norma_derogada' => $parsed['norma_derogada'],
            'norma_reemplazo' => $parsed['norma_reemplazo'] ?? 'No especificada'
        ];
    }
}
