<?php

namespace App\Services;

use App\Models\IndicadorSSTModel;

/**
 * Servicio para generar Indicadores de Objetivos del SG-SST
 * Estandar 2.2.1 - Resolucion 0312/2019
 *
 * PARTE 2 del modulo de 3 partes:
 * - CONSUME los objetivos de Parte 1 (tbl_pta_cliente tipo_servicio='Objetivos SG-SST')
 * - La IA genera indicadores que MIDEN el cumplimiento de cada objetivo
 * - Se guardan en tbl_indicadores_sst con categoria = 'objetivos_sgsst'
 */
class IndicadoresObjetivosService
{
    protected IndicadorSSTModel $indicadorModel;
    protected ObjetivosSgsstService $objetivosService;

    protected const CATEGORIA = 'objetivos_sgsst';
    protected const NUMERAL = '2.2.1';

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
        $this->objetivosService = new ObjetivosSgsstService();
    }

    /**
     * Limite de indicadores segun estandares
     */
    public function getLimiteIndicadores(int $estandares): int
    {
        if ($estandares <= 7) return 5;
        if ($estandares <= 21) return 8;
        return 10;
    }

    /**
     * Resumen de indicadores existentes para un cliente
     */
    public function getResumenIndicadores(int $idCliente, int $anio = 0): array
    {
        $indicadores = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->where('categoria', self::CATEGORIA)
            ->findAll();

        // Calcular minimo basado en objetivos existentes
        if ($anio === 0) $anio = (int)date('Y');
        $objetivos = $this->objetivosService->getObjetivosCliente($idCliente, $anio);
        $minimo = max(3, (int)ceil(count($objetivos) / 2));

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
     * VALIDACION OBLIGATORIA: Verificar que existan objetivos de Parte 1
     */
    public function verificarObjetivosPrevios(int $idCliente, int $anio): array
    {
        $objetivos = $this->objetivosService->getObjetivosCliente($idCliente, $anio);

        return [
            'tiene_objetivos' => count($objetivos) > 0,
            'total_objetivos' => count($objetivos),
            'objetivos' => $objetivos,
            'mensaje' => count($objetivos) > 0
                ? "Se encontraron " . count($objetivos) . " objetivos del SG-SST"
                : 'Debe completar la Parte 1 (Objetivos) antes de generar indicadores'
        ];
    }

    /**
     * Preview de indicadores generados por IA.
     * La IA genera indicadores a partir de los objetivos de Parte 1.
     */
    public function previewIndicadores(int $idCliente, int $anio, ?array $contexto = null, string $instrucciones = ''): array
    {
        // VALIDACION: Verificar objetivos previos
        $verificacion = $this->verificarObjetivosPrevios($idCliente, $anio);
        if (!$verificacion['tiene_objetivos']) {
            return [
                'indicadores' => [],
                'total' => 0,
                'error' => true,
                'mensaje' => $verificacion['mensaje']
            ];
        }

        // Leer objetivos de Parte 1
        $objetivos = $verificacion['objetivos'];
        $estandares = $contexto['estandares_aplicables'] ?? 60;
        $limite = $this->getLimiteIndicadores($estandares);

        // Leer indicadores existentes para no repetir
        $existentes = $this->getIndicadoresCliente($idCliente);
        $nombresExistentes = array_map(function($ind) {
            return $ind['nombre_indicador'];
        }, $existentes);

        // Generar con IA
        $resultado = $this->generarConIA($objetivos, $nombresExistentes, $limite, $contexto, $instrucciones);

        return [
            'indicadores' => $resultado['indicadores'],
            'total' => count($resultado['indicadores']),
            'limite' => $limite,
            'total_objetivos' => count($objetivos),
            'explicacion_ia' => $resultado['explicacion'] ?? ''
        ];
    }

    /**
     * La IA genera indicadores que miden el cumplimiento de los objetivos de Parte 1.
     * Cada indicador esta vinculado a un objetivo especifico.
     */
    protected function generarConIA(array $objetivos, array $indicadoresExistentes, int $limite, ?array $contexto, string $instrucciones = ''): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            throw new \RuntimeException('OPENAI_API_KEY no configurada. La generacion de indicadores requiere la API de OpenAI.');
        }

        // Formatear objetivos de Parte 1 para el prompt
        $objTexto = "";
        foreach ($objetivos as $i => $obj) {
            $partes = explode(' | Meta: ', $obj['actividad_plandetrabajo']);
            $titulo = $partes[0] ?? 'Sin titulo';
            $meta = $partes[1] ?? '';
            $phva = $obj['phva_plandetrabajo'] ?? 'PLANEAR';
            $responsable = $obj['responsable_sugerido_plandetrabajo'] ?? 'Responsable SST';

            $objTexto .= ($i + 1) . ". {$titulo}\n";
            if ($meta) {
                $objTexto .= "   Meta: {$meta}\n";
            }
            $objTexto .= "   PHVA: {$phva} | Responsable: {$responsable}\n";
        }

        // Formatear indicadores existentes
        $existentesTexto = "";
        if (!empty($indicadoresExistentes)) {
            $existentesTexto = "INDICADORES QUE YA EXISTEN (NO REPETIR):\n";
            foreach ($indicadoresExistentes as $nombre) {
                $existentesTexto .= "- {$nombre}\n";
            }
        }

        // Contexto empresa
        $contextoTexto = "";
        if ($contexto) {
            $contextoTexto = "CONTEXTO DE LA EMPRESA:\n";
            $contextoTexto .= "- Actividad economica: " . ($contexto['actividad_economica_principal'] ?? 'No definida') . "\n";
            $contextoTexto .= "- Nivel de riesgo ARL: " . ($contexto['nivel_riesgo_arl'] ?? 'No definido') . "\n";
            $contextoTexto .= "- Total trabajadores: " . ($contexto['total_trabajadores'] ?? 'No definido') . "\n";
            $contextoTexto .= "- Estandares aplicables: " . ($contexto['estandares_aplicables'] ?? 60) . "\n";
        }

        $totalObj = count($objetivos);

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia, especializado en disenar indicadores de gestion segun la Resolucion 0312 de 2019.

Tu tarea es GENERAR indicadores que MIDAN el cumplimiento de los objetivos del SG-SST que te proporcionare.

REGLAS OBLIGATORIAS:
1. Genera entre {$totalObj} y {$limite} indicadores en total
2. Cada indicador DEBE estar vinculado a un objetivo especifico de los proporcionados
3. Un objetivo puede tener 1 o 2 indicadores, pero cada indicador mide UN objetivo
4. Los indicadores deben ser MEDIBLES, CUANTIFICABLES y con formula clara
5. Rankea por CRITICIDAD: los mas importantes primero
6. Marca como 'recomendado': true los indicadores mas criticos (hasta {$limite})
7. NO repitas indicadores que ya existen en el sistema
8. Tipos permitidos: 'estructura' (recursos), 'proceso' (ejecucion), 'resultado' (impacto)
9. Periodicidad permitida: 'mensual', 'trimestral', 'semestral', 'anual'
10. Si hay instrucciones del consultor, aplicalas con prioridad
11. Responde SOLO en formato JSON valido

FORMATO DE RESPUESTA (JSON):
{
  \"indicadores\": [
    {
      \"nombre\": \"Nombre del indicador\",
      \"tipo\": \"resultado\",
      \"formula\": \"(Numerador / Denominador) x 100\",
      \"descripcion\": \"Breve descripcion del indicador en 1-2 oraciones\",
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
      \"objetivo_origen\": \"Titulo del objetivo que mide este indicador\"
    }
  ],
  \"explicacion\": \"Explicacion del criterio de priorizacion usado\"
}";

        $userPrompt = "OBJETIVOS DEL SG-SST ({$totalObj} definidos en Parte 1):\n";
        $userPrompt .= $objTexto . "\n";
        $userPrompt .= "LIMITE DE INDICADORES: maximo {$limite}\n\n";

        if (!empty($contextoTexto)) {
            $userPrompt .= $contextoTexto . "\n";
        }

        if (!empty($existentesTexto)) {
            $userPrompt .= $existentesTexto . "\n";
        }

        if (!empty($instrucciones)) {
            $userPrompt .= "INSTRUCCIONES ADICIONALES DEL CONSULTOR:\n\"{$instrucciones}\"\n\n";
        }

        $userPrompt .= "Genera indicadores que midan el cumplimiento de estos objetivos. Cada indicador debe estar vinculado a un objetivo especifico.";

        $response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey);

        if (!$response['success']) {
            log_message('error', 'Error en IA Indicadores Objetivos: ' . ($response['error'] ?? 'desconocido'));
            throw new \RuntimeException('Error al generar indicadores con IA: ' . ($response['error'] ?? 'desconocido'));
        }

        // Parsear respuesta
        $contenidoIA = $response['contenido'];
        $contenidoIA = preg_replace('/```json\s*/', '', $contenidoIA);
        $contenidoIA = preg_replace('/```\s*/', '', $contenidoIA);

        $respuesta = json_decode($contenidoIA, true);
        if (!$respuesta || empty($respuesta['indicadores'])) {
            log_message('warning', 'No se pudo parsear respuesta IA indicadores objetivos: ' . $contenidoIA);
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
                'descripcion' => $ind['descripcion'] ?? '',
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
                'objetivo_origen' => $ind['objetivo_origen'] ?? '',
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
     * Genera los indicadores de objetivos en la BD
     */
    public function generarIndicadores(int $idCliente, int $anio, ?array $indicadoresSeleccionados = null): array
    {
        // VALIDACION: Verificar objetivos previos
        $verificacion = $this->verificarObjetivosPrevios($idCliente, $anio);
        if (!$verificacion['tiene_objetivos']) {
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
     * Obtiene los indicadores de objetivos de un cliente
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
            return "No hay indicadores configurados para los objetivos del SG-SST.";
        }

        $texto = "Total: " . count($indicadores) . " indicadores\n\n";

        $porTipo = ['resultado' => [], 'proceso' => [], 'estructura' => []];
        foreach ($indicadores as $ind) {
            $tipo = $ind['tipo_indicador'] ?? 'proceso';
            $porTipo[$tipo][] = $ind;
        }

        foreach ($porTipo as $tipo => $inds) {
            if (!empty($inds)) {
                $texto .= strtoupper("INDICADORES DE " . $tipo) . ":\n";
                foreach ($inds as $i => $ind) {
                    $texto .= ($i + 1) . ". {$ind['nombre_indicador']}\n";
                    $texto .= "   - Formula: {$ind['formula']}\n";
                    $texto .= "   - Meta: {$ind['meta']} {$ind['unidad_medida']}\n";
                    $texto .= "   - Periodicidad: {$ind['periodicidad']}\n\n";
                }
            }
        }

        return $texto;
    }
}
