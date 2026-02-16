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

        $prompt = "¿Qué leyes, decretos, resoluciones y normas vigentes en Colombia debo considerar para elaborar una {$nombreDocumento}?

Necesito un listado completo y detallado. Incluye al menos 8 normas aplicables, cubriendo:
- La norma principal que obliga o regula este tipo de documento
- Normas complementarias del Sistema de Gestión de SST
- Resoluciones y decretos reglamentarios aplicables
- Cualquier norma relacionada indirectamente (protección de datos, acoso laboral, jornadas, modalidades de trabajo, etc.)

Para cada norma indica: nombre completo, año y qué regula específicamente para este tipo de documento.
Formato: lista con viñetas, agrupada por categoría de mayor a menor relevancia.";

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
            // Políticas
            'politica_sst_general'                  => 'Política de Seguridad y Salud en el Trabajo',
            'politica_desconexion_laboral'           => 'Política de Desconexión Laboral',
            'politica_acoso_laboral'                 => 'Política de Prevención del Acoso Laboral',
            'politica_alcohol_drogas'                => 'Política de Prevención del Consumo de Alcohol, Tabaco y Sustancias Psicoactivas',
            'politica_discriminacion'                => 'Política de Prevención de la Discriminación, Maltrato y Violencia',
            'politica_prevencion_emergencias'        => 'Política de Prevención y Respuesta ante Emergencias',
            'politica_violencias_genero'             => 'Política de Prevención del Acoso Sexual y Violencias de Género',
            // Programas
            'programa_capacitacion'                  => 'Programa de Capacitación en SST',
            'programa_induccion_reinduccion'         => 'Programa de Inducción y Reinducción en SG-SST',
            'programa_promocion_prevencion_salud'    => 'Programa de Promoción y Prevención en Salud',
            'programa_estilos_vida_saludable'        => 'Programa de Estilos de Vida Saludable y Entornos Saludables',
            'programa_evaluaciones_medicas_ocupacionales' => 'Programa de Evaluaciones Médicas Ocupacionales',
            'programa_mantenimiento_periodico'       => 'Programa de Mantenimiento Periódico de Instalaciones, Equipos y Herramientas',
            'programa_vigilancia_epidemiologica'     => 'Programa de Vigilancia Epidemiológica',
            'programa_riesgo_psicosocial'            => 'Programa de Riesgo Psicosocial',
            'programa_orden_aseo'                    => 'Programa de Orden y Aseo',
            // PVE
            'pve_riesgo_biomecanico'                 => 'Programa de Vigilancia Epidemiológica de Riesgo Biomecánico',
            'pve_riesgo_psicosocial'                 => 'Programa de Vigilancia Epidemiológica de Riesgo Psicosocial',
            // Planes
            'plan_emergencias'                       => 'Plan de Emergencias y Contingencias',
            'plan_objetivos_metas'                   => 'Plan de Objetivos y Metas del SG-SST',
            // Procedimientos
            'procedimiento_control_documental'       => 'Procedimiento de Control Documental del SG-SST',
            'procedimiento_matriz_legal'             => 'Procedimiento de Identificación de Requisitos Legales',
            'procedimiento_adquisiciones'            => 'Procedimiento de Adquisiciones y Contratación en SST',
            'procedimiento_evaluaciones_medicas'     => 'Procedimiento de Evaluaciones Médicas Ocupacionales',
            'procedimiento_evaluacion_proveedores'   => 'Procedimiento de Evaluación y Selección de Proveedores en SST',
            'procedimiento_gestion_cambio'           => 'Procedimiento de Gestión del Cambio en SST',
            'procedimiento_investigacion_accidentes' => 'Procedimiento de Investigación de Accidentes de Trabajo y Enfermedades Laborales',
            'procedimiento_investigacion_incidentes' => 'Procedimiento de Investigación de Incidentes de Trabajo',
            // Identificaciones y metodologías
            'identificacion_alto_riesgo'             => 'Identificación de Trabajadores de Alto Riesgo y Cotización de Pensión Especial',
            'identificacion_sustancias_cancerigenas' => 'Identificación de Sustancias Cancerígenas y Agentes Causantes de Enfermedad Laboral',
            'metodologia_identificacion_peligros'    => 'Metodología de Identificación de Peligros y Valoración de Riesgos',
            // Otros
            'mecanismos_comunicacion_sgsst'          => 'Mecanismos de Comunicación y Auto Reporte en SG-SST',
            'manual_convivencia_laboral'             => 'Manual de Convivencia Laboral',
        ];

        if (isset($nombres[$tipo])) {
            return $nombres[$tipo];
        }

        // Fallback: convertir snake_case a texto legible
        return ucfirst(str_replace('_', ' ', $tipo));
    }
}
