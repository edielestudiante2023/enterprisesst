<?php

namespace App\Services;

use App\Models\IndicadorSSTModel;

/**
 * Servicio para generar Indicadores del Programa de Capacitacion SST
 * Estandar 1.2.1 - Resolucion 0312/2019
 *
 * PARTE 2 del modulo de 3 partes:
 * - CONSUME las capacitaciones de Parte 1 (tbl_cronog_capacitacion)
 * - La IA genera 1 indicador por capacitacion, rankeados por criticidad
 * - El consultor elige maximo la mitad
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
     * Maximo de indicadores = mitad de capacitaciones
     * 4 cap -> 2 ind, 8 cap -> 4 ind, 12 cap -> 6 ind
     */
    public function getLimiteIndicadores(int $totalCapacitaciones): int
    {
        return max(2, (int)ceil($totalCapacitaciones / 2));
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

        // Calcular minimo basado en capacitaciones existentes
        $resumenCap = $this->capacitacionService->getResumenCapacitaciones($idCliente, $anio);
        $minimo = $this->getLimiteIndicadores($resumenCap['existentes']);

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
     * Preview de indicadores generados por IA.
     * La IA genera 1 indicador por capacitacion, rankeados por criticidad.
     * Pre-selecciona la mitad mas critica.
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
        $limite = $this->getLimiteIndicadores(count($capacitaciones));

        // Leer indicadores existentes para no repetir
        $existentes = $this->getIndicadoresCliente($idCliente);
        $nombresExistentes = array_map(function($ind) {
            return $ind['nombre_indicador'];
        }, $existentes);

        // Generar con IA
        $resultado = $this->generarConIA($capacitaciones, $nombresExistentes, $limite, $contexto, $instrucciones);

        return [
            'indicadores' => $resultado['indicadores'],
            'total' => count($resultado['indicadores']),
            'limite' => $limite,
            'total_capacitaciones' => count($capacitaciones),
            'explicacion_ia' => $resultado['explicacion'] ?? ''
        ];
    }

    /**
     * La IA genera 1 indicador por capacitacion, rankeados por criticidad.
     * Pre-selecciona los mas criticos (hasta el limite).
     */
    protected function generarConIA(array $capacitaciones, array $indicadoresExistentes, int $limite, ?array $contexto, string $instrucciones = ''): array
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

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia, especializado en disenar indicadores de gestion para programas de capacitacion segun la Resolucion 0312 de 2019.

Tu tarea es GENERAR indicadores para medir el impacto y cumplimiento de las capacitaciones programadas.

REGLAS OBLIGATORIAS:
1. Genera EXACTAMENTE {$totalCap} indicadores (uno por cada capacitacion)
2. Cada indicador debe medir si la capacitacion LOGRO SU OBJETIVO, no solo si se ejecuto
3. Rankea los indicadores por CRITICIDAD: los mas importantes primero
4. Marca como 'recomendado': true los {$limite} indicadores mas criticos (la mitad)
5. Los demas marcalos como 'recomendado': false
6. NO repitas indicadores que ya existen en el sistema
7. Tipos permitidos: 'estructura' (recursos), 'proceso' (ejecucion), 'resultado' (impacto)
8. Periodicidad permitida: 'mensual', 'trimestral', 'semestral', 'anual'
9. Si hay instrucciones del consultor, aplicalas con prioridad
10. Responde SOLO en formato JSON valido

FORMATO DE RESPUESTA (JSON):
{
  \"indicadores\": [
    {
      \"nombre\": \"Nombre del indicador\",
      \"tipo\": \"resultado\",
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
      \"capacitacion_asociada\": \"Nombre de la capacitacion que mide\"
    }
  ],
  \"explicacion\": \"Explicacion del criterio de priorizacion usado\"
}";

        $userPrompt = "CAPACITACIONES PROGRAMADAS ({$totalCap} en total):\n";
        $userPrompt .= $capTexto . "\n";
        $userPrompt .= "MAXIMO RECOMENDADO: {$limite} indicadores (mitad de las capacitaciones)\n\n";

        if (!empty($existentesTexto)) {
            $userPrompt .= $existentesTexto . "\n";
        }

        if (!empty($instrucciones)) {
            $userPrompt .= "INSTRUCCIONES ADICIONALES DEL CONSULTOR:\n\"{$instrucciones}\"\n\n";
        }

        $userPrompt .= "Genera {$totalCap} indicadores (uno por capacitacion), rankeados por criticidad. Marca los {$limite} mas criticos como recomendados.";

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
                'recomendado' => $ind['recomendado'] ?? false,
                'seleccionado' => $ind['recomendado'] ?? false,
                'capacitacion_asociada' => $ind['capacitacion_asociada'] ?? '',
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
            'max_tokens' => 3000
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
            CURLOPT_TIMEOUT => 45,
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
