<?php

namespace App\Services;

use App\Models\IndicadorSSTModel;

/**
 * Servicio para generar indicadores de Estilos de Vida Saludable
 * segun Resolucion 0312/2019 - Estandar 3.1.7
 *
 * PARTE 2 del modulo de 3 partes:
 * - IA genera indicadores personalizados usando contexto COMPLETO del cliente
 * - Consultor revisa y selecciona
 * - Se guardan en tbl_indicadores_sst con categoria = 'estilos_vida_saludable'
 */
class IndicadoresEstilosVidaService
{
    protected IndicadorSSTModel $indicadorModel;
    protected const CATEGORIA = 'estilos_vida_saludable';
    protected const NUMERAL = '3.1.7';
    protected const CANTIDAD_GENERAR = 7;

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
    }

    /**
     * Obtiene el resumen de indicadores de Estilos de Vida para un cliente
     */
    public function getResumenIndicadores(int $idCliente): array
    {
        $indicadores = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', self::CATEGORIA)
                ->orLike('nombre_indicador', 'estilos de vida', 'both')
                ->orLike('nombre_indicador', 'tabaquismo', 'both')
                ->orLike('nombre_indicador', 'alcoholismo', 'both')
                ->orLike('nombre_indicador', 'fumadores', 'both')
                ->orLike('nombre_indicador', 'farmacodependencia', 'both')
                ->orLike('nombre_indicador', 'sustancias psicoactivas', 'both')
            ->groupEnd()
            ->findAll();

        $total = count($indicadores);
        $medidos = 0;
        $cumplen = 0;

        foreach ($indicadores as $ind) {
            if ($ind['cumple_meta'] !== null) {
                $medidos++;
                if ($ind['cumple_meta'] == 1) {
                    $cumplen++;
                }
            }
        }

        return [
            'existentes' => $total,
            'limite' => self::CANTIDAD_GENERAR,
            'medidos' => $medidos,
            'cumplen' => $cumplen,
            'completo' => $total >= 3,
            'minimo' => 3
        ];
    }

    /**
     * Preview de indicadores generados por IA segun contexto completo del cliente
     */
    public function previewIndicadores(int $idCliente, ?array $contexto = null): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            log_message('error', 'IndicadoresEstilosVida: OPENAI_API_KEY no configurada');
            return [
                'indicadores' => [],
                'total' => 0,
                'contexto_aplicado' => false,
                'error' => 'API Key de OpenAI no configurada'
            ];
        }

        $objetivosService = new \App\Services\ObjetivosSgsstService();
        $contextoTexto = $objetivosService->construirContextoCompleto($contexto, $idCliente);

        $systemPrompt = $this->construirSystemPrompt();
        $userPrompt = $contextoTexto;
        $userPrompt .= "\n\nGenera exactamente " . self::CANTIDAD_GENERAR . " indicadores de Estilos de Vida Saludable personalizados para esta empresa.";
        $userPrompt .= "\nDeben ser especificos para su actividad economica, perfil demografico de trabajadores y peligros identificados.";

        $response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey, 0.7);

        if (!$response['success']) {
            log_message('error', 'Error IA Indicadores Estilos Vida: ' . ($response['error'] ?? 'desconocido'));
            return [
                'indicadores' => [],
                'total' => 0,
                'contexto_aplicado' => $contexto ? true : false,
                'error' => 'Error al generar indicadores: ' . ($response['error'] ?? 'Error desconocido')
            ];
        }

        $indicadores = $this->procesarRespuestaIA($response['contenido']);

        $existentes = $this->indicadorModel->getByCliente($idCliente);
        $nombresExistentes = array_map('strtolower', array_column($existentes, 'nombre_indicador'));

        foreach ($indicadores as &$ind) {
            $ind['ya_existe'] = false;
            $nombreLower = strtolower($ind['nombre']);
            foreach ($nombresExistentes as $existente) {
                if (similar_text($nombreLower, $existente) > strlen($nombreLower) * 0.7) {
                    $ind['ya_existe'] = true;
                    $ind['seleccionado'] = false;
                    break;
                }
            }
        }
        unset($ind);

        return [
            'indicadores' => $indicadores,
            'total' => count($indicadores),
            'contexto_aplicado' => $contexto ? true : false,
            'generado_con_ia' => true
        ];
    }

    /**
     * System prompt especializado para Estilos de Vida Saludable
     */
    protected function construirSystemPrompt(): string
    {
        $cantidad = self::CANTIDAD_GENERAR;
        return "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia, especialista en Promocion de la Salud y Estilos de Vida Saludable.
Tu tarea es generar indicadores del Programa de Estilos de Vida Saludable personalizados segun el contexto REAL de la empresa.

NORMATIVIDAD APLICABLE:
- Resolucion 0312/2019 - Estandar 3.1.7
- Resolucion 1075/1992 (Prevencion de tabaquismo, alcoholismo y farmacodependencia)
- Ley 1335/2009 (Espacios libres de humo)
- Decreto 1072/2015 (Capitulo 6 - SG-SST)

REGLAS OBLIGATORIAS:
1. Genera EXACTAMENTE {$cantidad} indicadores
2. Cada indicador DEBE tener estos 14 campos: nombre, tipo, formula, meta, unidad, periodicidad, phva, numeral, descripcion, definicion, interpretacion, origen_datos, cargo_responsable, cargos_conocer_resultado
3. 'tipo' solo puede ser: estructura, proceso, resultado (incluir al menos 2 de proceso y 2 de resultado)
4. 'periodicidad' solo puede ser: mensual, trimestral, semestral, anual
5. Las formulas deben ser matematicamente correctas y calculables
6. Las metas deben ser numericas y realistas
7. Si hay observaciones del consultor sobre habitos de los trabajadores, integrar esa informacion
8. Adaptar los cargos responsables al tamano y estructura de la empresa
9. NO generar indicadores genericos — deben reflejar la realidad de la empresa
10. OBLIGATORIO incluir indicadores sobre los 3 temas de Res. 1075/1992: tabaquismo, alcoholismo y farmacodependencia
11. Temas relevantes: campanas de prevencion, participacion en actividades, reduccion de fumadores, enfermedades cronicas no transmisibles (ECNT), consumo de sustancias psicoactivas (SPA), canalizacion a EPS, satisfaccion del programa
12. Responde SOLO en formato JSON valido sin markdown

FORMATO DE RESPUESTA (JSON array):
[{\"nombre\":\"...\",\"tipo\":\"proceso\",\"formula\":\"...\",\"meta\":90,\"unidad\":\"%\",\"periodicidad\":\"trimestral\",\"phva\":\"verificar\",\"numeral\":\"3.1.7\",\"descripcion\":\"...\",\"definicion\":\"...\",\"interpretacion\":\"...\",\"origen_datos\":\"...\",\"cargo_responsable\":\"...\",\"cargos_conocer_resultado\":\"...\"}]";
    }

    /**
     * Procesa la respuesta JSON de la IA
     */
    protected function procesarRespuestaIA(string $contenidoIA): array
    {
        $contenidoIA = preg_replace('/```json\s*/', '', $contenidoIA);
        $contenidoIA = preg_replace('/```\s*/', '', $contenidoIA);
        $contenidoIA = trim($contenidoIA);

        $respuesta = json_decode($contenidoIA, true);
        if (!is_array($respuesta)) {
            log_message('error', 'IndicadoresEstilosVida: JSON invalido de IA: ' . substr($contenidoIA, 0, 500));
            return [];
        }

        $indicadores = [];
        foreach ($respuesta as $idx => $ind) {
            if (empty($ind['nombre'])) continue;

            $indicadores[] = [
                'indice' => $idx,
                'nombre' => $ind['nombre'],
                'tipo' => in_array($ind['tipo'] ?? '', ['estructura', 'proceso', 'resultado']) ? $ind['tipo'] : 'proceso',
                'formula' => $ind['formula'] ?? '',
                'meta' => $ind['meta'] ?? null,
                'unidad' => $ind['unidad'] ?? '%',
                'periodicidad' => in_array($ind['periodicidad'] ?? '', ['mensual', 'trimestral', 'semestral', 'anual']) ? $ind['periodicidad'] : 'trimestral',
                'phva' => in_array($ind['phva'] ?? '', ['planear', 'hacer', 'verificar', 'actuar']) ? $ind['phva'] : 'verificar',
                'numeral' => $ind['numeral'] ?? self::NUMERAL,
                'descripcion' => $ind['descripcion'] ?? '',
                'definicion' => $ind['definicion'] ?? null,
                'interpretacion' => $ind['interpretacion'] ?? null,
                'origen_datos' => $ind['origen_datos'] ?? null,
                'cargo_responsable' => $ind['cargo_responsable'] ?? null,
                'cargos_conocer_resultado' => $ind['cargos_conocer_resultado'] ?? null,
                'origen' => 'ia',
                'seleccionado' => true
            ];
        }

        return $indicadores;
    }

    /**
     * Llama a la API de OpenAI
     */
    protected function llamarOpenAI(string $systemPrompt, string $userPrompt, string $apiKey, float $temperature = 0.7): array
    {
        $data = [
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => $temperature,
            'max_tokens' => 4000
        ];

        log_message('debug', 'IndicadoresEstilosVida llamarOpenAI - modelo: ' . $data['model'] . ', temperature: ' . $temperature);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'IndicadoresEstilosVida curl error: ' . $error);
            return ['success' => false, 'error' => "Error de conexion: {$error}"];
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $result['error']['message'] ?? 'Error HTTP ' . $httpCode;
            log_message('error', 'IndicadoresEstilosVida OpenAI HTTP ' . $httpCode . ': ' . $errorMsg);
            return ['success' => false, 'error' => $errorMsg];
        }

        if (isset($result['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'contenido' => trim($result['choices'][0]['message']['content'])
            ];
        }

        return ['success' => false, 'error' => 'Respuesta inesperada de OpenAI'];
    }

    /**
     * Genera los indicadores de Estilos de Vida Saludable en BD
     */
    public function generarIndicadores(int $idCliente, ?array $indicadoresSeleccionados = null): array
    {
        if (empty($indicadoresSeleccionados)) {
            return ['creados' => 0, 'existentes' => 0, 'errores' => ['No se proporcionaron indicadores para generar'], 'total' => 0];
        }

        $creados = 0;
        $existentes = 0;
        $errores = [];

        foreach ($indicadoresSeleccionados as $ind) {
            $existe = $this->indicadorModel
                ->where('id_cliente', $idCliente)
                ->where('activo', 1)
                ->like('nombre_indicador', substr($ind['nombre'], 0, 30), 'both')
                ->countAllResults();

            if ($existe > 0) {
                $existentes++;
                continue;
            }

            try {
                $this->indicadorModel->insert([
                    'id_cliente' => $idCliente,
                    'nombre_indicador' => $ind['nombre'],
                    'tipo_indicador' => $ind['tipo'],
                    'categoria' => self::CATEGORIA,
                    'formula' => $ind['formula'],
                    'meta' => $ind['meta'],
                    'unidad_medida' => $ind['unidad'],
                    'periodicidad' => $ind['periodicidad'],
                    'phva' => $ind['phva'],
                    'numeral_resolucion' => $ind['numeral'] ?? self::NUMERAL,
                    'definicion' => $ind['definicion'] ?? null,
                    'interpretacion' => $ind['interpretacion'] ?? null,
                    'origen_datos' => $ind['origen_datos'] ?? null,
                    'cargo_responsable' => $ind['cargo_responsable'] ?? null,
                    'cargos_conocer_resultado' => $ind['cargos_conocer_resultado'] ?? null,
                    'activo' => 1
                ]);
                $creados++;
            } catch (\Exception $e) {
                $errores[] = "Error en '{$ind['nombre']}': " . $e->getMessage();
            }
        }

        return [
            'creados' => $creados,
            'existentes' => $existentes,
            'errores' => $errores,
            'total' => count($indicadoresSeleccionados)
        ];
    }

    /**
     * Obtiene los indicadores de Estilos de Vida de un cliente
     */
    public function getIndicadoresCliente(int $idCliente): array
    {
        return $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', self::CATEGORIA)
                ->orLike('nombre_indicador', 'estilos de vida', 'both')
                ->orLike('nombre_indicador', 'tabaquismo', 'both')
                ->orLike('nombre_indicador', 'alcoholismo', 'both')
                ->orLike('nombre_indicador', 'fumadores', 'both')
                ->orLike('nombre_indicador', 'farmacodependencia', 'both')
                ->orLike('nombre_indicador', 'sustancias psicoactivas', 'both')
            ->groupEnd()
            ->orderBy('tipo_indicador', 'ASC')
            ->orderBy('nombre_indicador', 'ASC')
            ->findAll();
    }
}
