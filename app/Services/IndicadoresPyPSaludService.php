<?php

namespace App\Services;

use App\Models\IndicadorSSTModel;

/**
 * Servicio para generar indicadores de Promocion y Prevencion en Salud
 * Usa IA con contexto completo del cliente para personalizar indicadores
 * segun Resolucion 0312/2019 - Estandar 3.1.2
 */
class IndicadoresPyPSaludService
{
    protected IndicadorSSTModel $indicadorModel;

    const CATEGORIA = 'pyp_salud';
    const NUMERAL = '3.1.2';
    const CANTIDAD_GENERAR = 7;

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
    }

    /**
     * Obtiene el resumen de indicadores de PyP Salud para un cliente
     */
    public function getResumenIndicadores(int $idCliente): array
    {
        $indicadores = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', self::CATEGORIA)
                ->orLike('nombre_indicador', 'examen', 'both')
                ->orLike('nombre_indicador', 'enfermedad', 'both')
                ->orLike('nombre_indicador', 'salud', 'both')
                ->orLike('nombre_indicador', 'ausentismo', 'both')
                ->orLike('nombre_indicador', 'PyP', 'both')
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
     * Preview de indicadores generados con IA usando contexto completo del cliente
     */
    public function previewIndicadores(int $idCliente, ?array $contexto = null): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            log_message('error', 'IndicadoresPyPSalud: OPENAI_API_KEY no configurada');
            return [
                'indicadores' => [],
                'total' => 0,
                'error' => 'API key no configurada'
            ];
        }

        // Contexto completo del cliente
        $objetivosService = new \App\Services\ObjetivosSgsstService();
        $contextoTexto = $objetivosService->construirContextoCompleto($contexto, $idCliente);

        $systemPrompt = $this->construirSystemPrompt();
        $userPrompt = $contextoTexto;
        $userPrompt .= "\n\nGenera exactamente " . self::CANTIDAD_GENERAR . " indicadores de Promocion y Prevencion en Salud personalizados para esta empresa.";
        $userPrompt .= "\nSi hay peligros identificados, incluye indicadores especificos para esos peligros (ej: audiometrias para peligro fisico/ruido, DME para biomecanico, riesgo psicosocial, exposicion quimica, etc.)";
        $userPrompt .= "\nLos indicadores deben cubrir: cobertura de examenes medicos, cumplimiento de actividades PyP, ausentismo, enfermedad laboral, y seguimiento a restricciones.";

        $response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey, 0.7);

        if (!$response['success']) {
            log_message('error', 'IndicadoresPyPSalud: Error IA: ' . ($response['error'] ?? 'desconocido'));
            return [
                'indicadores' => [],
                'total' => 0,
                'error' => $response['error'] ?? 'Error al generar indicadores'
            ];
        }

        $indicadores = $this->procesarRespuestaIA($response['contenido']);

        // Marcar los que ya existen
        $existentes = $this->indicadorModel->getByCliente($idCliente);
        $nombresExistentes = array_map('strtolower', array_column($existentes, 'nombre_indicador'));

        foreach ($indicadores as &$ind) {
            $ind['ya_existe'] = false;
            $nombreLower = strtolower($ind['nombre']);
            foreach ($nombresExistentes as $existente) {
                if (similar_text($nombreLower, $existente) > strlen($nombreLower) * 0.7) {
                    $ind['ya_existe'] = true;
                    break;
                }
            }
        }

        return [
            'indicadores' => $indicadores,
            'total' => count($indicadores),
            'contexto_aplicado' => true
        ];
    }

    /**
     * System prompt especializado para indicadores de PyP Salud
     */
    protected function construirSystemPrompt(): string
    {
        return <<<'PROMPT'
Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia, especializado en Promocion y Prevencion en Salud.

Genera indicadores de PyP Salud personalizados segun el contexto de la empresa. Los indicadores deben cumplir con la Resolucion 0312 de 2019, el Decreto 1072 de 2015 y la Resolucion 2346 de 2007.

REGLAS:
1. Cada indicador DEBE tener los campos: nombre, tipo, formula, meta, unidad, periodicidad, phva, numeral, descripcion, definicion, interpretacion, origen_datos, cargo_responsable, cargos_conocer_resultado
2. Los tipos validos son: estructura, proceso, resultado
3. Las periodicidades validas son: mensual, bimestral, trimestral, semestral, anual
4. El phva debe ser: planear, hacer, verificar, actuar
5. Los numerales deben corresponder a la Resolucion 0312/2019 (3.1.1 a 3.1.9 principalmente)
6. Si hay peligros identificados, incluir indicadores especificos para esos peligros:
   - Psicosocial: cobertura bateria riesgo psicosocial (Res. 2764/2022)
   - Biomecanico: incidencia DME, evaluaciones ergonomicas
   - Quimico: examenes especificos por exposicion
   - Fisico/Ruido: cobertura audiometrias
   - Biologico: cobertura esquemas de vacunacion
7. Si hay observaciones del consultor, adaptarlas a los indicadores
8. Los indicadores deben ser MEDIBLES, con formulas concretas y metas numericas
9. Mezclar indicadores de estructura, proceso y resultado

FORMATO DE RESPUESTA:
Responde UNICAMENTE con un JSON valido (sin markdown, sin ```json```):
{
  "indicadores": [
    {
      "nombre": "Nombre del indicador",
      "tipo": "proceso",
      "formula": "Formula matematica clara",
      "meta": 100,
      "unidad": "%",
      "periodicidad": "trimestral",
      "phva": "verificar",
      "numeral": "3.1.4",
      "descripcion": "Descripcion breve",
      "definicion": "Definicion completa del indicador",
      "interpretacion": "Como interpretar los resultados",
      "origen_datos": "Fuentes de datos para el calculo",
      "cargo_responsable": "Cargo del responsable de medir",
      "cargos_conocer_resultado": "Cargos que deben conocer el resultado"
    }
  ]
}
PROMPT;
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
            'max_tokens' => 4000,
            'response_format' => ['type' => 'json_object']
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
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => 'cURL: ' . $curlError, 'contenido' => null];
        }

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'HTTP ' . $httpCode, 'contenido' => null];
        }

        $decoded = json_decode($response, true);
        $content = $decoded['choices'][0]['message']['content'] ?? null;

        if ($content) {
            $tokens = $decoded['usage']['total_tokens'] ?? 0;
            log_message('info', "IndicadoresPyPSalud: Respuesta recibida ({$tokens} tokens)");
        }

        return ['success' => true, 'error' => null, 'contenido' => $content];
    }

    /**
     * Procesa la respuesta JSON de OpenAI y valida los indicadores
     */
    protected function procesarRespuestaIA(?string $contenido): array
    {
        if (empty($contenido)) return [];

        $data = json_decode($contenido, true);
        if (!$data || empty($data['indicadores'])) return [];

        $indicadores = [];
        $camposRequeridos = ['nombre', 'tipo', 'formula', 'meta', 'unidad', 'periodicidad'];
        $tiposValidos = ['estructura', 'proceso', 'resultado'];
        $periodicidadesValidas = ['mensual', 'bimestral', 'trimestral', 'semestral', 'anual'];

        foreach ($data['indicadores'] as $ind) {
            $valido = true;
            foreach ($camposRequeridos as $campo) {
                if (!isset($ind[$campo]) || (is_string($ind[$campo]) && trim($ind[$campo]) === '')) {
                    $valido = false;
                    break;
                }
            }
            if (!$valido) continue;

            $tipo = strtolower(trim($ind['tipo']));
            if (!in_array($tipo, $tiposValidos)) $tipo = 'proceso';

            $periodicidad = strtolower(trim($ind['periodicidad']));
            if (!in_array($periodicidad, $periodicidadesValidas)) $periodicidad = 'trimestral';

            $phva = strtolower(trim($ind['phva'] ?? 'verificar'));

            $indicadores[] = [
                'nombre' => trim($ind['nombre']),
                'tipo' => $tipo,
                'formula' => trim($ind['formula']),
                'meta' => $ind['meta'],
                'unidad' => trim($ind['unidad']),
                'periodicidad' => $periodicidad,
                'phva' => $phva,
                'numeral' => trim($ind['numeral'] ?? self::NUMERAL),
                'descripcion' => trim($ind['descripcion'] ?? ''),
                'definicion' => trim($ind['definicion'] ?? ''),
                'interpretacion' => trim($ind['interpretacion'] ?? ''),
                'origen_datos' => trim($ind['origen_datos'] ?? ''),
                'cargo_responsable' => trim($ind['cargo_responsable'] ?? 'Responsable del SG-SST'),
                'cargos_conocer_resultado' => trim($ind['cargos_conocer_resultado'] ?? ''),
            ];
        }

        return $indicadores;
    }

    /**
     * Genera los indicadores de PyP Salud
     */
    public function generarIndicadores(int $idCliente, ?array $indicadoresSeleccionados = null): array
    {
        if (empty($indicadoresSeleccionados)) {
            return [
                'creados' => 0,
                'existentes' => 0,
                'errores' => ['No se proporcionaron indicadores seleccionados'],
                'total' => 0
            ];
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
     * Obtiene los indicadores de PyP Salud de un cliente
     */
    public function getIndicadoresCliente(int $idCliente): array
    {
        return $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->groupStart()
                ->where('categoria', self::CATEGORIA)
                ->orLike('nombre_indicador', 'examen', 'both')
                ->orLike('nombre_indicador', 'enfermedad', 'both')
                ->orLike('nombre_indicador', 'salud', 'both')
                ->orLike('nombre_indicador', 'ausentismo', 'both')
                ->orLike('nombre_indicador', 'PyP', 'both')
            ->groupEnd()
            ->orderBy('tipo_indicador', 'ASC')
            ->orderBy('nombre_indicador', 'ASC')
            ->findAll();
    }
}
