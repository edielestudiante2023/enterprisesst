<?php

namespace App\Services;

/**
 * Servicio para generar Objetivos del SG-SST
 * Estándar 2.2.1 - Resolución 0312/2019
 *
 * PARTE 1 del módulo de 3 partes:
 * - IA genera sugerencias de objetivos usando contexto COMPLETO del cliente
 * - Consultor refina y selecciona
 * - Se guardan en tbl_pta_cliente con tipo_servicio = 'Objetivos SG-SST'
 */
class ObjetivosSgsstService
{
    protected const TIPO_SERVICIO = 'Objetivos SG-SST';

    /**
     * Límites fijos de objetivos según estándares (Res. 0312/2019)
     */
    public const LIMITES_OBJETIVOS = [
        7 => 3,   // Básico: 3 objetivos
        21 => 4,  // Intermedio: 4 objetivos
        60 => 6   // Avanzado: 6 objetivos
    ];

    /**
     * Obtiene el límite de objetivos según estándares del cliente
     */
    public function getLimiteObjetivos(int $estandares): int
    {
        if ($estandares <= 7) return self::LIMITES_OBJETIVOS[7];
        if ($estandares <= 21) return self::LIMITES_OBJETIVOS[21];
        return self::LIMITES_OBJETIVOS[60];
    }

    /**
     * Obtiene el resumen de objetivos para un cliente
     */
    public function getResumenObjetivos(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        $estandares = 60;
        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        if ($contexto) {
            $estandares = (int)($contexto['estandares_aplicables'] ?? 60);
        }
        $limite = $this->getLimiteObjetivos($estandares);

        $existentes = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('tipo_servicio', self::TIPO_SERVICIO)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->countAllResults();

        return [
            'existentes' => $existentes,
            'limite' => $limite,
            'completo' => $existentes >= 3
        ];
    }

    /**
     * Preview de objetivos generados por IA según contexto completo del cliente
     */
    public function previewObjetivos(int $idCliente, int $anio, ?array $contexto = null, string $instrucciones = ''): array
    {
        $estandares = (int)($contexto['estandares_aplicables'] ?? 60);
        $limite = $this->getLimiteObjetivos($estandares);

        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            log_message('error', 'ObjetivosSgsstService: OPENAI_API_KEY no configurada');
            return [
                'objetivos' => [],
                'total' => 0,
                'limite' => $limite,
                'estandares' => $estandares,
                'anio' => $anio,
                'instrucciones_procesadas' => false,
                'explicacion_ia' => 'Error: API Key de OpenAI no configurada. Configure OPENAI_API_KEY en .env',
                'error' => true
            ];
        }

        // Construir contexto completo del cliente para la IA
        $contextoTexto = $this->construirContextoCompleto($contexto, $idCliente);

        $systemPrompt = $this->construirSystemPromptGenerar($limite);

        $userPrompt = "AÑO: {$anio}\nCANTIDAD EXACTA: {$limite} objetivos\n\n";
        $userPrompt .= $contextoTexto;

        if (!empty($instrucciones)) {
            $userPrompt .= "\n\nINSTRUCCIONES ADICIONALES DEL CONSULTOR:\n\"{$instrucciones}\"";
        }

        $userPrompt .= "\n\nGenera exactamente {$limite} objetivos del SG-SST personalizados para esta empresa. Deben ser especificos para su actividad economica, nivel de riesgo y peligros identificados.";

        $response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey, 0.7);

        if (!$response['success']) {
            log_message('error', 'Error IA Objetivos preview: ' . ($response['error'] ?? 'desconocido'));
            return [
                'objetivos' => [],
                'total' => 0,
                'limite' => $limite,
                'estandares' => $estandares,
                'anio' => $anio,
                'instrucciones_procesadas' => !empty($instrucciones),
                'explicacion_ia' => 'Error al generar objetivos: ' . ($response['error'] ?? 'Error desconocido'),
                'error' => true
            ];
        }

        $resultado = $this->procesarRespuestaIA($response['contenido'], $anio, $limite);

        return [
            'objetivos' => $resultado['objetivos'],
            'total' => count($resultado['objetivos']),
            'limite' => $limite,
            'estandares' => $estandares,
            'anio' => $anio,
            'instrucciones_procesadas' => !empty($instrucciones),
            'explicacion_ia' => $resultado['explicacion'] ?? ''
        ];
    }

    /**
     * Construye el contexto completo del cliente para los prompts de IA
     * Incluye TODOS los campos de tbl_cliente_contexto_sst + datos del cliente
     */
    public function construirContextoCompleto(?array $contexto, int $idCliente): string
    {
        if (!$contexto) {
            return "CONTEXTO DE LA EMPRESA:\n- Sin datos de contexto registrados. Generar objetivos genericos de SST.\n";
        }

        // Obtener nombre del cliente
        $db = \Config\Database::connect();
        $cliente = $db->table('tbl_clientes')->where('id_cliente', $idCliente)->get()->getRowArray();
        $nombreCliente = $cliente['nombre_cliente'] ?? 'Empresa';

        $texto = "CONTEXTO COMPLETO DE LA EMPRESA:\n";
        $texto .= "- Empresa: {$nombreCliente}\n";

        // Clasificación económica
        if (!empty($contexto['actividad_economica_principal'])) {
            $texto .= "- Actividad economica principal: {$contexto['actividad_economica_principal']}\n";
        }
        if (!empty($contexto['sector_economico'])) {
            $texto .= "- Sector economico: {$contexto['sector_economico']}\n";
        }
        if (!empty($contexto['codigo_ciiu_principal'])) {
            $texto .= "- Codigo CIIU principal: {$contexto['codigo_ciiu_principal']}\n";
        }

        // Riesgo
        if (!empty($contexto['nivel_riesgo_arl'])) {
            $texto .= "- Nivel de riesgo ARL: {$contexto['nivel_riesgo_arl']}\n";
        }
        if (!empty($contexto['niveles_riesgo_arl'])) {
            $niveles = json_decode($contexto['niveles_riesgo_arl'], true);
            if (is_array($niveles) && count($niveles) > 1) {
                $texto .= "- Niveles de riesgo multiples: " . implode(', ', $niveles) . "\n";
            }
        }
        if (!empty($contexto['clase_riesgo_cotizacion'])) {
            $texto .= "- Clase de riesgo cotizacion: {$contexto['clase_riesgo_cotizacion']}\n";
        }
        if (!empty($contexto['arl_actual'])) {
            $texto .= "- ARL actual: {$contexto['arl_actual']}\n";
        }

        // Tamaño y estructura
        $texto .= "- Total trabajadores: " . ($contexto['total_trabajadores'] ?? 'No especificado') . "\n";
        if (!empty($contexto['trabajadores_directos'])) {
            $texto .= "  - Directos: {$contexto['trabajadores_directos']}\n";
        }
        if (!empty($contexto['trabajadores_temporales']) && $contexto['trabajadores_temporales'] > 0) {
            $texto .= "  - Temporales: {$contexto['trabajadores_temporales']}\n";
        }
        if (!empty($contexto['contratistas_permanentes']) && $contexto['contratistas_permanentes'] > 0) {
            $texto .= "  - Contratistas permanentes: {$contexto['contratistas_permanentes']}\n";
        }
        if (!empty($contexto['numero_sedes']) && $contexto['numero_sedes'] > 1) {
            $texto .= "- Numero de sedes: {$contexto['numero_sedes']}\n";
        }
        if (!empty($contexto['turnos_trabajo'])) {
            $turnos = json_decode($contexto['turnos_trabajo'], true);
            if (is_array($turnos) && !empty($turnos)) {
                $texto .= "- Turnos de trabajo: " . implode(', ', $turnos) . "\n";
            }
        }

        // Estándares
        $estandares = $contexto['estandares_aplicables'] ?? 60;
        $descripcionNivel = match((int)$estandares) {
            7 => 'Basico (7 estandares)',
            21 => 'Intermedio (21 estandares)',
            default => 'Completo (60 estandares)'
        };
        $texto .= "- Estandares aplicables: {$descripcionNivel}\n";

        // Infraestructura SST
        $infraestructura = [];
        if (!empty($contexto['tiene_copasst'])) $infraestructura[] = 'COPASST';
        if (!empty($contexto['tiene_vigia_sst'])) $infraestructura[] = 'Vigia SST';
        if (!empty($contexto['tiene_comite_convivencia'])) $infraestructura[] = 'Comite de Convivencia';
        if (!empty($contexto['tiene_brigada_emergencias'])) $infraestructura[] = 'Brigada de Emergencias';
        if (!empty($infraestructura)) {
            $texto .= "- Infraestructura SST activa: " . implode(', ', $infraestructura) . "\n";
        }

        $faltantes = [];
        if (empty($contexto['tiene_copasst']) && empty($contexto['tiene_vigia_sst'])) $faltantes[] = 'COPASST/Vigia';
        if (empty($contexto['tiene_comite_convivencia'])) $faltantes[] = 'Comite Convivencia';
        if (empty($contexto['tiene_brigada_emergencias'])) $faltantes[] = 'Brigada Emergencias';
        if (!empty($faltantes)) {
            $texto .= "- Infraestructura SST faltante: " . implode(', ', $faltantes) . "\n";
        }

        // Responsable SST
        if (!empty($contexto['responsable_sgsst_cargo'])) {
            $texto .= "- Responsable SG-SST: {$contexto['responsable_sgsst_cargo']}\n";
        }

        // Peligros identificados
        if (!empty($contexto['peligros_identificados'])) {
            $peligros = json_decode($contexto['peligros_identificados'], true);
            if (is_array($peligros) && !empty($peligros)) {
                $texto .= "\nPELIGROS IDENTIFICADOS EN LA EMPRESA:\n";
                foreach ($peligros as $peligro) {
                    $texto .= "- {$peligro}\n";
                }
            }
        }

        // CONTEXTO Y OBSERVACIONES (campo clave para personalización)
        if (!empty($contexto['observaciones_contexto'])) {
            $texto .= "\nCONTEXTO Y OBSERVACIONES DEL CONSULTOR:\n";
            $texto .= $contexto['observaciones_contexto'] . "\n";
        }

        return $texto;
    }

    /**
     * System prompt para generar objetivos
     */
    protected function construirSystemPromptGenerar(int $limite): string
    {
        return "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia.
Tu tarea es GENERAR objetivos del SG-SST personalizados segun el contexto REAL de la empresa.

REGLAS OBLIGATORIAS:
1. Genera EXACTAMENTE {$limite} objetivos (limite segun Resolucion 0312/2019)
2. Los objetivos DEBEN ser especificos para la actividad economica y peligros de la empresa
3. Cada objetivo debe ser SMART: Especifico, Medible, Alcanzable, Relevante, Temporal
4. Distribuye los objetivos en el ciclo PHVA (al menos 1 PLANEAR, 1 HACER, y si el limite lo permite, incluir VERIFICAR y ACTUAR)
5. Las metas deben ser cuantificables con porcentajes, numeros o plazos concretos
6. Los responsables deben ser cargos reales segun el tamaño de la empresa
7. Si hay peligros identificados, al menos 2 objetivos deben abordarlos directamente
8. Si hay observaciones del consultor, integrar esa informacion en los objetivos
9. NO generar objetivos genericos — deben reflejar la realidad de la empresa
10. Responde SOLO en formato JSON valido sin markdown

FORMATO DE RESPUESTA (JSON):
{
  \"objetivos\": [
    {
      \"objetivo\": \"Titulo conciso del objetivo\",
      \"descripcion\": \"Descripcion detallada vinculada a la realidad de la empresa\",
      \"meta\": \"Meta cuantificable con plazo (ej: Reducir en 15% los incidentes en el primer semestre 2026)\",
      \"indicador_sugerido\": \"Nombre del indicador de medicion\",
      \"phva\": \"PLANEAR|HACER|VERIFICAR|ACTUAR\",
      \"responsable\": \"Cargo especifico del responsable\"
    }
  ],
  \"explicacion\": \"Breve justificacion de por que se eligieron estos objetivos para esta empresa\"
}";
    }

    /**
     * Procesa la respuesta JSON de la IA
     */
    protected function procesarRespuestaIA(string $contenidoIA, int $anio, int $limite): array
    {
        $contenidoIA = preg_replace('/```json\s*/', '', $contenidoIA);
        $contenidoIA = preg_replace('/```\s*/', '', $contenidoIA);
        $contenidoIA = trim($contenidoIA);

        $respuesta = json_decode($contenidoIA, true);
        if (!$respuesta || !isset($respuesta['objetivos'])) {
            log_message('error', 'ObjetivosSgsstService: JSON invalido de IA: ' . substr($contenidoIA, 0, 500));
            return ['objetivos' => [], 'explicacion' => 'Error al procesar respuesta de IA. Intente de nuevo.'];
        }

        $objetivos = [];
        $objetivosIA = array_slice($respuesta['objetivos'], 0, $limite);

        foreach ($objetivosIA as $idx => $obj) {
            $objetivos[] = [
                'indice' => $idx,
                'objetivo' => $obj['objetivo'] ?? 'Objetivo ' . ($idx + 1),
                'descripcion' => $obj['descripcion'] ?? '',
                'meta' => $obj['meta'] ?? '',
                'indicador_sugerido' => $obj['indicador_sugerido'] ?? '',
                'phva' => $obj['phva'] ?? 'PLANEAR',
                'responsable' => $obj['responsable'] ?? 'Responsable SST',
                'numeral' => '2.2.1',
                'fecha_propuesta' => "{$anio}-01-01",
                'origen' => 'ia',
                'generado_por_ia' => true
            ];
        }

        return [
            'objetivos' => $objetivos,
            'explicacion' => $respuesta['explicacion'] ?? ''
        ];
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
            'max_tokens' => 3000
        ];

        log_message('debug', 'ObjetivosSgsstService llamarOpenAI - modelo: ' . $data['model'] . ', temperature: ' . $temperature);

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
            log_message('error', 'ObjetivosSgsstService curl error: ' . $error);
            return ['success' => false, 'error' => "Error de conexion: {$error}"];
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $result['error']['message'] ?? 'Error HTTP ' . $httpCode;
            log_message('error', 'ObjetivosSgsstService OpenAI HTTP ' . $httpCode . ': ' . $errorMsg);
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
     * Genera los objetivos en la BD (tbl_pta_cliente)
     */
    public function generarObjetivos(int $idCliente, int $anio, array $objetivosSeleccionados): array
    {
        $creados = 0;
        $existentes = 0;
        $errores = [];

        $db = \Config\Database::connect();

        foreach ($objetivosSeleccionados as $obj) {
            // Verificar si ya existe un objetivo similar
            $existe = $db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('tipo_servicio', self::TIPO_SERVICIO)
                ->where('YEAR(fecha_propuesta)', $anio)
                ->like('actividad_plandetrabajo', substr($obj['objetivo'], 0, 30), 'both')
                ->countAllResults();

            if ($existe > 0) {
                $existentes++;
                continue;
            }

            try {
                $fechaPropuesta = $obj['fecha_propuesta'] ?? "{$anio}-01-01";
                $semana = (int)date('W', strtotime($fechaPropuesta));

                // Construir descripción completa para la actividad
                $actividadCompleta = $obj['objetivo'];
                if (!empty($obj['descripcion'])) {
                    $actividadCompleta .= " - " . $obj['descripcion'];
                }
                if (!empty($obj['meta'])) {
                    $actividadCompleta .= " | Meta: " . $obj['meta'];
                }

                $db->table('tbl_pta_cliente')->insert([
                    'id_cliente' => $idCliente,
                    'tipo_servicio' => self::TIPO_SERVICIO,
                    'phva_plandetrabajo' => $obj['phva'] ?? 'PLANEAR',
                    'numeral_plandetrabajo' => $obj['numeral'] ?? '2.2.1',
                    'actividad_plandetrabajo' => $actividadCompleta,
                    'responsable_sugerido_plandetrabajo' => $obj['responsable'] ?? 'Responsable SST',
                    'fecha_propuesta' => $fechaPropuesta,
                    'estado_actividad' => 'ABIERTA',
                    'porcentaje_avance' => 0,
                    'semana' => $semana
                ]);
                $creados++;
            } catch (\Exception $e) {
                $errores[] = "Error en '{$obj['objetivo']}': " . $e->getMessage();
            }
        }

        return [
            'creados' => $creados,
            'existentes' => $existentes,
            'errores' => $errores,
            'total' => count($objetivosSeleccionados)
        ];
    }

    /**
     * Obtiene los objetivos existentes del cliente
     */
    public function getObjetivosCliente(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        return $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('tipo_servicio', self::TIPO_SERVICIO)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->orderBy('fecha_propuesta', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Elimina un objetivo específico
     */
    public function eliminarObjetivo(int $idPta): bool
    {
        $db = \Config\Database::connect();

        return $db->table('tbl_pta_cliente')
            ->where('id_ptacliente', $idPta)
            ->where('tipo_servicio', self::TIPO_SERVICIO)
            ->delete();
    }
}
