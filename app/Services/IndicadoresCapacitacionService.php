<?php

namespace App\Services;

use App\Models\IndicadorSSTModel;

/**
 * Servicio para generar Indicadores del Programa de Capacitacion SST
 * Estandar 1.2.1 - Resolucion 0312/2019
 *
 * PARTE 2 del modulo de 3 partes:
 * - CONSUME las capacitaciones de Parte 1 (tbl_cronog_capacitacion)
 * - La IA genera indicadores CONSOLIDADOS (globales + por foco/riesgo)
 * - La cantidad depende del nivel de estandares (trabajadores + riesgo ARL)
 * - Se guardan en tbl_indicadores_sst con categoria = 'capacitacion'
 */
class IndicadoresCapacitacionService
{
    protected IndicadorSSTModel $indicadorModel;
    protected CapacitacionSSTService $capacitacionService;

    protected const CATEGORIA = 'capacitacion';
    protected const NUMERAL = '1.2.1';

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
        $this->capacitacionService = new CapacitacionSSTService();
    }

    /**
     * Rango de indicadores consolidados segun Resolucion 0312/2019.
     * Depende del nivel de estandares (trabajadores + riesgo ARL):
     *   7 estandares  (<=10 trab, riesgo I-III)  -> 2-3 indicadores
     *  21 estandares  (11-50 trab, riesgo I-III)  -> 3-5 indicadores
     *  60 estandares  (>50 trab o riesgo IV-V)    -> 5-8 indicadores
     */
    protected const RANGOS_INDICADORES = [
        7  => ['min' => 2, 'max' => 3],
        21 => ['min' => 3, 'max' => 5],
        60 => ['min' => 5, 'max' => 8],
    ];

    public function getRangoIndicadores(?array $contexto = null): array
    {
        $nivel = $this->calcularNivelEstandares($contexto);
        return self::RANGOS_INDICADORES[$nivel] ?? self::RANGOS_INDICADORES[60];
    }

    public function getLimiteIndicadores(?array $contexto = null): int
    {
        return $this->getRangoIndicadores($contexto)['max'];
    }

    public function getMinimoIndicadores(?array $contexto = null): int
    {
        return $this->getRangoIndicadores($contexto)['min'];
    }

    /**
     * Calcula nivel de estandares a partir del contexto del cliente
     */
    protected function calcularNivelEstandares(?array $contexto): int
    {
        if (!$contexto) {
            return 60;
        }
        $trabajadores = (int)($contexto['total_trabajadores'] ?? 0);
        $riesgo = $contexto['nivel_riesgo_arl'] ?? '';

        if ($trabajadores <= 10 && in_array($riesgo, ['I', 'II', 'III'])) {
            return 7;
        } elseif ($trabajadores >= 11 && $trabajadores <= 50 && in_array($riesgo, ['I', 'II', 'III'])) {
            return 21;
        }
        return 60;
    }

    /**
     * Obtiene el resumen de indicadores de capacitacion para un cliente
     */
    public function getResumenIndicadores(int $idCliente, int $anio): array
    {
        $indicadores = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->where('categoria', self::CATEGORIA)
            ->findAll();

        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $minimo = $this->getMinimoIndicadores($contexto);

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
            'medidos' => $medidos,
            'cumplen' => $cumplen,
            'completo' => $total >= $minimo,
            'minimo' => $minimo,
            'total' => $total
        ];
    }

    /**
     * VALIDACION OBLIGATORIA: Verificar que existan capacitaciones de Parte 1
     */
    public function verificarCapacitacionesPrevias(int $idCliente, int $anio): array
    {
        $resumen = $this->capacitacionService->getResumenCapacitaciones($idCliente, $anio);

        return [
            'tiene_capacitaciones' => $resumen['existentes'] > 0,
            'total_capacitaciones' => $resumen['existentes'],
            'minimo_requerido' => $resumen['minimo'],
            'completo' => $resumen['completo'],
            'mensaje' => $resumen['existentes'] > 0
                ? "Se encontraron {$resumen['existentes']} capacitaciones en el cronograma"
                : 'Debe completar la Parte 1 (Capacitaciones) antes de generar indicadores'
        ];
    }

    /**
     * Preview de indicadores consolidados del Programa de Capacitacion.
     * La IA genera indicadores globales y por foco/riesgo (NO 1 por capacitacion).
     * La cantidad depende del nivel de estandares (trabajadores + riesgo ARL).
     */
    public function previewIndicadores(int $idCliente, int $anio, ?array $contexto = null, string $instrucciones = ''): array
    {
        // VALIDACION: Verificar capacitaciones previas
        $verificacion = $this->verificarCapacitacionesPrevias($idCliente, $anio);
        if (!$verificacion['tiene_capacitaciones']) {
            return [
                'indicadores' => [],
                'total' => 0,
                'error' => true,
                'mensaje' => $verificacion['mensaje']
            ];
        }

        // Leer capacitaciones del cronograma (Parte 1)
        $capacitaciones = $this->capacitacionService->getCapacitacionesCliente($idCliente, $anio);
        $rango = $this->getRangoIndicadores($contexto);

        // Leer indicadores existentes para no repetir
        $existentes = $this->getIndicadoresCliente($idCliente);
        $nombresExistentes = array_map(function($ind) {
            return $ind['nombre_indicador'];
        }, $existentes);

        // Generar con IA
        $resultado = $this->generarConIA($capacitaciones, $nombresExistentes, $rango, $contexto, $instrucciones);

        return [
            'indicadores' => $resultado['indicadores'],
            'total' => count($resultado['indicadores']),
            'limite' => $rango['max'],
            'total_capacitaciones' => count($capacitaciones),
            'explicacion_ia' => $resultado['explicacion'] ?? ''
        ];
    }

    /**
     * La IA genera indicadores CONSOLIDADOS del programa de capacitacion:
     * - Indicadores globales (cumplimiento, cobertura, eficacia)
     * - Indicadores por foco/riesgo (agrupando capacitaciones similares)
     * La cantidad depende del nivel de estandares de la empresa.
     */
    protected function generarConIA(array $capacitaciones, array $indicadoresExistentes, array $rango, ?array $contexto, string $instrucciones = ''): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            throw new \RuntimeException('OPENAI_API_KEY no configurada. La generacion de indicadores requiere la API de OpenAI.');
        }

        // Formatear capacitaciones para el prompt
        $capTexto = "";
        foreach ($capacitaciones as $i => $cap) {
            $nombre = $cap['nombre_capacitacion'] ?? $cap['capacitacion'] ?? 'Sin nombre';
            $objetivo = $cap['objetivo_capacitacion'] ?? '';
            $perfil = $cap['perfil_de_asistentes'] ?? 'TODOS';
            $capTexto .= ($i + 1) . ". {$nombre} (Dirigido a: {$perfil})\n";
            if ($objetivo) {
                $capTexto .= "   Objetivo: {$objetivo}\n";
            }
        }

        // Formatear indicadores existentes
        $existentesTexto = "";
        if (!empty($indicadoresExistentes)) {
            $existentesTexto = "INDICADORES QUE YA EXISTEN (NO REPETIR):\n";
            foreach ($indicadoresExistentes as $nombre) {
                $existentesTexto .= "- {$nombre}\n";
            }
        }

        $totalCap = count($capacitaciones);
        $min = $rango['min'];
        $max = $rango['max'];
        $nivel = $this->calcularNivelEstandares($contexto);
        $trabajadores = (int)($contexto['total_trabajadores'] ?? 0);
        $riesgoArl = $contexto['nivel_riesgo_arl'] ?? 'No especificado';

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia, especializado en disenar indicadores de gestion para programas de capacitacion segun la Resolucion 0312 de 2019.

Tu tarea es generar indicadores CONSOLIDADOS para medir el Programa de Capacitacion como un todo. NO generes un indicador por cada capacitacion individual.

CONTEXTO DE LA EMPRESA:
- Trabajadores: {$trabajadores}
- Nivel de riesgo ARL: {$riesgoArl}
- Estandares aplicables: {$nivel} (Resolucion 0312/2019)

ENFOQUE DE INDICADORES:
1. INDICADORES GLOBALES del programa: miden el programa completo (ej: cumplimiento del cronograma, cobertura general, eficacia global de capacitaciones)
2. INDICADORES POR FOCO/RIESGO: agrupan capacitaciones por tematica similar (ej: si hay varias capacitaciones de emergencias, UN solo indicador que las cubra; si hay de riesgo biomecanico, UN indicador para ese foco)

REGLAS OBLIGATORIAS:
1. Genera entre {$min} y {$max} indicadores en total (NO uno por capacitacion)
2. Siempre incluye al menos: cumplimiento del cronograma, cobertura de capacitaciones y eficacia
3. Los indicadores por foco solo cuando haya 2+ capacitaciones del mismo tema/riesgo
4. Cada indicador debe ser MEDIBLE con datos reales del programa
5. Marca todos como 'recomendado': true (ya estan dentro del rango optimo)
6. NO repitas indicadores que ya existen en el sistema
7. SOLO genera indicadores de tipo 'proceso' (ejecucion/cumplimiento) y 'resultado' (impacto/eficacia). Los de 'estructura' ya los crea el aplicativo por defecto, NO los incluyas
8. Periodicidad permitida: 'mensual', 'trimestral', 'semestral', 'anual'
9. Si hay instrucciones del consultor, aplicalas con prioridad
10. Responde SOLO en formato JSON valido

FORMATO DE RESPUESTA (JSON):
{
  \"indicadores\": [
    {
      \"nombre\": \"Nombre del indicador\",
      \"tipo\": \"proceso o resultado\",
      \"formula\": \"(Numerador / Denominador) x 100\",
      \"meta\": 90,
      \"unidad\": \"%\",
      \"periodicidad\": \"trimestral\",
      \"phva\": \"verificar\",
      \"definicion\": \"Que mide este indicador y por que es importante\",
      \"interpretacion\": \"Como leer el resultado y que hacer si no se cumple la meta\",
      \"origen_datos\": \"De donde salen los datos para calcular el indicador\",
      \"cargo_responsable\": \"Quien mide el indicador\",
      \"cargos_conocer_resultado\": \"Quienes deben conocer el resultado\",
      \"recomendado\": true,
      \"foco\": \"global | emergencias | riesgo_biomecanico | riesgo_quimico | etc\"
    }
  ],
  \"explicacion\": \"Criterio de consolidacion y priorizacion usado\"
}";

        $userPrompt = "CAPACITACIONES PROGRAMADAS ({$totalCap} en total):\n";
        $userPrompt .= $capTexto . "\n";
        $userPrompt .= "RANGO DE INDICADORES: entre {$min} y {$max} indicadores consolidados\n\n";

        if (!empty($existentesTexto)) {
            $userPrompt .= $existentesTexto . "\n";
        }

        if (!empty($instrucciones)) {
            $userPrompt .= "INSTRUCCIONES ADICIONALES DEL CONSULTOR:\n\"{$instrucciones}\"\n\n";
        }

        $userPrompt .= "Analiza las {$totalCap} capacitaciones, identifica focos/riesgos comunes, y genera entre {$min} y {$max} indicadores consolidados (globales + por foco). No generes uno por cada capacitacion.";

        $response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey);

        if (!$response['success']) {
            log_message('error', 'Error en IA Indicadores Capacitacion: ' . ($response['error'] ?? 'desconocido'));
            throw new \RuntimeException('Error al generar indicadores con IA: ' . ($response['error'] ?? 'desconocido'));
        }

        // Parsear respuesta
        $contenidoIA = $response['contenido'];
        $contenidoIA = preg_replace('/```json\s*/', '', $contenidoIA);
        $contenidoIA = preg_replace('/```\s*/', '', $contenidoIA);

        $respuesta = json_decode($contenidoIA, true);
        if (!$respuesta || empty($respuesta['indicadores'])) {
            log_message('warning', 'No se pudo parsear respuesta IA indicadores: ' . $contenidoIA);
            throw new \RuntimeException('La IA no genero una respuesta valida. Intente nuevamente.');
        }

        // Formatear indicadores
        $indicadores = [];
        foreach ($respuesta['indicadores'] as $idx => $ind) {
            $indicadores[] = [
                'indice' => $idx,
                'nombre' => $ind['nombre'] ?? 'Indicador SST',
                'tipo' => $ind['tipo'] ?? 'proceso',
                'formula' => $ind['formula'] ?? '',
                'meta' => $ind['meta'] ?? 100,
                'unidad' => $ind['unidad'] ?? '%',
                'periodicidad' => $ind['periodicidad'] ?? 'trimestral',
                'phva' => $ind['phva'] ?? 'verificar',
                'definicion' => $ind['definicion'] ?? '',
                'interpretacion' => $ind['interpretacion'] ?? '',
                'origen_datos' => $ind['origen_datos'] ?? '',
                'cargo_responsable' => $ind['cargo_responsable'] ?? 'Responsable del SG-SST',
                'cargos_conocer_resultado' => $ind['cargos_conocer_resultado'] ?? '',
                'recomendado' => $ind['recomendado'] ?? true,
                'seleccionado' => $ind['recomendado'] ?? true,
                'foco' => $ind['foco'] ?? 'global',
                'origen' => 'ia',
                'generado_por_ia' => true
            ];
        }

        return [
            'indicadores' => $indicadores,
            'explicacion' => $respuesta['explicacion'] ?? ''
        ];
    }

    /**
     * Llama a la API de OpenAI
     */
    protected function llamarOpenAI(string $systemPrompt, string $userPrompt, string $apiKey): array
    {
        $data = [
            'model' => env('OPENAI_MODEL', 'gpt-4o'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.3,
            'max_tokens' => 4000
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
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => "Error de conexion: {$error}"];
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => $result['error']['message'] ?? 'Error HTTP ' . $httpCode];
        }

        if (isset($result['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'contenido' => trim($result['choices'][0]['message']['content'])
            ];
        }

        return ['success' => false, 'error' => 'Respuesta inesperada'];
    }

    /**
     * Genera los indicadores de capacitacion en la BD
     */
    public function generarIndicadores(int $idCliente, int $anio, ?array $indicadoresSeleccionados = null): array
    {
        // VALIDACION: Verificar capacitaciones previas
        $verificacion = $this->verificarCapacitacionesPrevias($idCliente, $anio);
        if (!$verificacion['tiene_capacitaciones']) {
            return [
                'creados' => 0,
                'existentes' => 0,
                'errores' => [$verificacion['mensaje']],
                'total' => 0
            ];
        }

        if (empty($indicadoresSeleccionados)) {
            throw new \RuntimeException('No se recibieron indicadores para generar. Ejecute primero la previsualizacion con IA.');
        }

        $creados = 0;
        $existentes = 0;
        $errores = [];

        foreach ($indicadoresSeleccionados as $ind) {
            $nombreIndicador = $ind['nombre'] ?? $ind['nombre_indicador'] ?? '';
            if (empty($nombreIndicador)) {
                continue;
            }

            // Verificar si ya existe un indicador similar
            $existe = $this->indicadorModel
                ->where('id_cliente', $idCliente)
                ->where('activo', 1)
                ->like('nombre_indicador', substr($nombreIndicador, 0, 30), 'both')
                ->countAllResults();

            if ($existe > 0) {
                $existentes++;
                continue;
            }

            try {
                $this->indicadorModel->insert([
                    'id_cliente' => $idCliente,
                    'nombre_indicador' => $nombreIndicador,
                    'tipo_indicador' => $ind['tipo'] ?? 'proceso',
                    'categoria' => self::CATEGORIA,
                    'formula' => $ind['formula'] ?? '',
                    'meta' => $ind['meta'] ?? 100,
                    'unidad_medida' => $ind['unidad'] ?? '%',
                    'periodicidad' => $ind['periodicidad'] ?? 'trimestral',
                    'phva' => $ind['phva'] ?? 'verificar',
                    'numeral_resolucion' => self::NUMERAL,
                    'definicion' => $ind['definicion'] ?? null,
                    'interpretacion' => $ind['interpretacion'] ?? null,
                    'origen_datos' => $ind['origen_datos'] ?? null,
                    'cargo_responsable' => $ind['cargo_responsable'] ?? null,
                    'cargos_conocer_resultado' => $ind['cargos_conocer_resultado'] ?? null,
                    'activo' => 1
                ]);
                $creados++;
            } catch (\Exception $e) {
                $errores[] = "Error en '{$nombreIndicador}': " . $e->getMessage();
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
     * Obtiene los indicadores de capacitacion de un cliente
     */
    public function getIndicadoresCliente(int $idCliente): array
    {
        return $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->where('categoria', self::CATEGORIA)
            ->orderBy('tipo_indicador', 'ASC')
            ->orderBy('nombre_indicador', 'ASC')
            ->findAll();
    }

    /**
     * Obtiene indicadores formateados para el contexto del documento (Parte 3)
     */
    public function getIndicadoresParaContexto(int $idCliente): string
    {
        $indicadores = $this->getIndicadoresCliente($idCliente);

        if (empty($indicadores)) {
            return "No hay indicadores configurados para el programa de capacitacion.";
        }

        $texto = "Total: " . count($indicadores) . " indicadores de capacitacion\n\n";

        foreach ($indicadores as $i => $ind) {
            $texto .= ($i + 1) . ". {$ind['nombre_indicador']}\n";
            $texto .= "   - Tipo: " . ucfirst($ind['tipo_indicador']) . "\n";
            $texto .= "   - Formula: {$ind['formula']}\n";
            $texto .= "   - Meta: {$ind['meta']} {$ind['unidad_medida']}\n";
            $texto .= "   - Periodicidad: {$ind['periodicidad']}\n\n";
        }

        return $texto;
    }
}
