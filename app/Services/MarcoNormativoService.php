<?php

namespace App\Services;

use App\Models\MarcoNormativoModel;

/**
 * Servicio para gestionar el marco normativo de documentos SST
 * Módulo: Insumos IA - Pregeneración
 *
 * Usa OpenAI Responses API con web_search_preview para consultar
 * marco normativo vigente con búsqueda web en tiempo real.
 */
class MarcoNormativoService
{
    protected MarcoNormativoModel $model;
    protected string $apiKey;
    protected string $apiUrl = 'https://api.openai.com/v1/responses';

    public function __construct()
    {
        $this->model = new MarcoNormativoModel();
        $this->apiKey = env('OPENAI_API_KEY', '');
    }

    /**
     * Obtener el texto del marco normativo para un tipo de documento
     * Retorna null si no existe registro en BD
     */
    public function obtenerMarcoNormativo(string $tipo): ?string
    {
        $registro = $this->model->getByTipoDocumento($tipo);
        if (!$registro) {
            return null;
        }
        return $registro['marco_normativo_texto'];
    }

    /**
     * Obtener información completa del marco normativo (para UI)
     */
    public function obtenerInfo(string $tipo): array
    {
        $registro = $this->model->getByTipoDocumento($tipo);

        if (!$registro) {
            return [
                'existe'    => false,
                'texto'     => null,
                'fecha'     => null,
                'dias'      => null,
                'vigente'   => false,
                'metodo'    => null,
                'actualizado_por' => null
            ];
        }

        $dias = $this->model->getDiasDesdeActualizacion($tipo);

        return [
            'existe'    => true,
            'texto'     => $registro['marco_normativo_texto'],
            'fecha'     => $registro['fecha_actualizacion'],
            'dias'      => $dias,
            'vigente'   => $dias <= ($registro['vigencia_dias'] ?? 90),
            'metodo'    => $registro['metodo_actualizacion'],
            'actualizado_por' => $registro['actualizado_por'],
            'vigencia_dias'   => $registro['vigencia_dias'] ?? 90
        ];
    }

    /**
     * Consultar marco normativo con IA (Responses API + web_search_preview)
     * Opciones 1, 2 y 3
     */
    public function consultarConIA(string $tipo, string $metodo = 'boton'): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'OPENAI_API_KEY no configurada'];
        }

        $nombreDocumento = $this->getNombreDocumento($tipo);

        $prompt = "Eres un experto en legislación colombiana de Seguridad y Salud en el Trabajo (SST).

Necesito el marco normativo vigente en Colombia aplicable a: {$nombreDocumento}.

INSTRUCCIONES:
1. Busca las normas, decretos, resoluciones y leyes VIGENTES a la fecha actual
2. Incluye SOLO normativa que esté vigente (no derogada)
3. Para cada norma indica: nombre completo, año, y qué regula específicamente para este tipo de documento
4. Ordena de mayor a menor relevancia

FORMATO de respuesta (usar exactamente este formato):
**[Nombre de la norma]**
[Qué regula o establece en relación con este documento]

Ejemplo:
**Decreto 1072 de 2015 - Decreto Único Reglamentario del Sector Trabajo**
Libro 2, Parte 2, Título 4, Capítulo 6: Establece la obligación de implementar el SG-SST y define los requisitos de la política de SST.

NO incluyas explicaciones adicionales, solo la lista de normas con su descripción.";

        $data = [
            'model' => 'gpt-4o',
            'input' => $prompt,
            'tools' => [
                ['type' => 'web_search_preview']
            ],
            'temperature' => 0.3
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_TIMEOUT        => 90,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', "MarcoNormativo - Error cURL: {$error}");
            return ['success' => false, 'error' => "Error de conexión: {$error}"];
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $result['error']['message'] ?? 'Error HTTP ' . $httpCode;
            log_message('error', "MarcoNormativo - Error API: {$errorMsg}");
            return ['success' => false, 'error' => $errorMsg];
        }

        // Responses API devuelve la respuesta en output[].content[].text
        $texto = $this->extraerTextoRespuesta($result);

        if (empty($texto)) {
            return ['success' => false, 'error' => 'Respuesta vacía de la API'];
        }

        // Guardar en BD
        $this->model->guardar($tipo, $texto, $metodo, 'sistema');

        log_message('info', "MarcoNormativo actualizado para '{$tipo}' vía método '{$metodo}'");

        return [
            'success' => true,
            'texto'   => $texto
        ];
    }

    /**
     * Guardar edición manual del consultor (opción 4)
     */
    public function guardarDesdeEdicion(string $tipo, string $texto, string $actualizadoPor = 'consultor'): bool
    {
        return $this->model->guardar($tipo, $texto, 'manual', $actualizadoPor);
    }

    /**
     * Extraer texto de la respuesta de Responses API
     * La estructura es: output[] -> content[] -> text
     */
    protected function extraerTextoRespuesta(array $result): string
    {
        if (!isset($result['output']) || !is_array($result['output'])) {
            // Fallback: intentar formato Chat Completions por si acaso
            if (isset($result['choices'][0]['message']['content'])) {
                return trim($result['choices'][0]['message']['content']);
            }
            return '';
        }

        $textos = [];
        foreach ($result['output'] as $outputItem) {
            if (($outputItem['type'] ?? '') === 'message' && isset($outputItem['content'])) {
                foreach ($outputItem['content'] as $contentItem) {
                    if (($contentItem['type'] ?? '') === 'output_text' && !empty($contentItem['text'])) {
                        $textos[] = $contentItem['text'];
                    }
                }
            }
        }

        return trim(implode("\n", $textos));
    }

    /**
     * Convertir tipo_documento snake_case a nombre legible
     */
    protected function getNombreDocumento(string $tipo): string
    {
        $nombres = [
            'politica_sst_general'              => 'Política de Seguridad y Salud en el Trabajo',
            'programa_capacitacion'              => 'Programa de Capacitación en SST',
            'procedimiento_control_documental'   => 'Procedimiento de Control Documental del SG-SST',
            'identificacion_alto_riesgo'         => 'Identificación de Trabajadores de Alto Riesgo',
            'plan_emergencias'                   => 'Plan de Emergencias y Contingencias',
            'programa_vigilancia_epidemiologica' => 'Programa de Vigilancia Epidemiológica',
            'programa_riesgo_psicosocial'        => 'Programa de Riesgo Psicosocial',
            'programa_orden_aseo'                => 'Programa de Orden y Aseo',
            'programa_estilos_vida_saludable'    => 'Programa de Estilos de Vida Saludable',
        ];

        if (isset($nombres[$tipo])) {
            return $nombres[$tipo];
        }

        // Fallback: convertir snake_case a texto legible
        return ucfirst(str_replace('_', ' ', $tipo));
    }
}
