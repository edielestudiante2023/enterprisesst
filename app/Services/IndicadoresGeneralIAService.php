<?php

namespace App\Services;

use App\Models\IndicadorSSTModel;
use App\Models\ClienteContextoSstModel;

/**
 * Servicio para generacion de indicadores SST asistida por IA
 *
 * Reemplaza el Grupo D (sugeridos hardcodeados) y mejora el Grupo C (CRUD manual)
 * con un flujo contextual: analisis de brechas → generacion IA → preview → edicion → guardar
 *
 * NO modifica Grupo A (legales auto-seed) ni Grupo B (servicios por documento)
 */
class IndicadoresGeneralIAService
{
    protected IndicadorSSTModel $indicadorModel;
    protected $db;

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
        $this->db = \Config\Database::connect();
    }

    // ═══════════════════════════════════════════════════════════════
    // 1. ANALISIS DE BRECHAS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Analiza que categorias faltan, cuales tienen pocos indicadores,
     * que indicadores legales no estan cubiertos, y la distribucion por tipo
     */
    public function analizarBrechas(int $idCliente): array
    {
        $existentesPorCat = $this->indicadorModel->getByClienteAgrupadosPorCategoria($idCliente);
        $todosExistentes = $this->indicadorModel->getByCliente($idCliente);

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = (int)($contexto['estandares_aplicables'] ?? 7);

        // Categorias vacias y escasas
        $categoriasVacias = [];
        $categoriasEscasas = [];
        $recomendadoPorCat = $this->getRecomendadoPorCategoria($estandares);

        foreach (IndicadorSSTModel::CATEGORIAS as $key => $info) {
            if ($key === 'otro') continue;
            $count = count($existentesPorCat[$key] ?? []);
            $recomendado = $recomendadoPorCat[$key] ?? 2;

            if ($count === 0) {
                $categoriasVacias[] = $key;
            } elseif ($count < $recomendado) {
                $categoriasEscasas[$key] = [
                    'existentes' => $count,
                    'recomendado' => $recomendado
                ];
            }
        }

        // Indicadores legales faltantes
        $nombresLower = array_map(fn($i) => mb_strtolower($i['nombre_indicador']), $todosExistentes);
        $legalesFaltantes = [];

        foreach (IndicadorSSTModel::INDICADORES_LEGALES as $legal) {
            $encontrado = false;
            foreach ($legal['keywords'] ?? [] as $kw) {
                foreach ($nombresLower as $nombre) {
                    if (mb_stripos($nombre, mb_strtolower($kw)) !== false) {
                        $encontrado = true;
                        break 2;
                    }
                }
            }
            if (!$encontrado) {
                $legalesFaltantes[] = $legal['nombre_indicador'];
            }
        }

        // Distribucion por tipo
        $distribucion = ['estructura' => 0, 'proceso' => 0, 'resultado' => 0];
        foreach ($todosExistentes as $ind) {
            $tipo = $ind['tipo_indicador'] ?? 'proceso';
            if (isset($distribucion[$tipo])) {
                $distribucion[$tipo]++;
            }
        }

        // Recomendacion de tipo
        $totalExistentes = count($todosExistentes);
        $recomendacionTipo = '';
        if ($totalExistentes > 0) {
            if ($distribucion['estructura'] < $totalExistentes * 0.15) {
                $recomendacionTipo = 'Necesita mas indicadores de Estructura (actualmente < 15%)';
            } elseif ($distribucion['resultado'] < $totalExistentes * 0.25) {
                $recomendacionTipo = 'Necesita mas indicadores de Resultado (actualmente < 25%)';
            }
        }

        return [
            'categorias_vacias' => $categoriasVacias,
            'categorias_escasas' => $categoriasEscasas,
            'legales_faltantes' => $legalesFaltantes,
            'distribucion_tipo' => $distribucion,
            'recomendacion_tipo' => $recomendacionTipo,
            'total_existentes' => $totalExistentes,
            'total_recomendado' => $this->calcularTotalRecomendado($estandares),
        ];
    }

    /**
     * Recomendados por categoria segun nivel de estandares
     */
    protected function getRecomendadoPorCategoria(int $estandares): array
    {
        // Categorias core siempre relevantes
        $core = ['accidentalidad', 'pta', 'capacitacion', 'ausentismo', 'riesgos', 'objetivos_sgsst'];
        // Categorias de programa
        $programa = ['pyp_salud', 'induccion', 'inspecciones', 'emergencias', 'vigilancia',
                     'estilos_vida_saludable', 'evaluaciones_medicas_ocupacionales',
                     'pve_biomecanico', 'pve_psicosocial', 'mantenimiento_periodico'];

        $recomendado = [];
        if ($estandares <= 7) {
            foreach ($core as $c) $recomendado[$c] = 2;
            foreach ($programa as $p) $recomendado[$p] = 1;
        } elseif ($estandares <= 21) {
            foreach ($core as $c) $recomendado[$c] = 3;
            foreach ($programa as $p) $recomendado[$p] = 2;
        } else {
            foreach ($core as $c) $recomendado[$c] = 4;
            foreach ($programa as $p) $recomendado[$p] = 3;
        }

        return $recomendado;
    }

    protected function calcularTotalRecomendado(int $estandares): int
    {
        if ($estandares <= 7) return 20;
        if ($estandares <= 21) return 25;
        return 35;
    }

    // ═══════════════════════════════════════════════════════════════
    // 2. CONTEXTO COMPLETO
    // ═══════════════════════════════════════════════════════════════

    /**
     * Agrega datos de multiples fuentes para alimentar la IA y el SweetAlert
     */
    public function obtenerContextoCompleto(int $idCliente): array
    {
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        // Resumen de indicadores por categoria
        $resumenCategorias = $this->indicadorModel->getResumenPorCategoria($idCliente);

        // Resumen PTA por tipo_servicio
        $ptaResumen = $this->db->table('tbl_pta_cliente')
            ->select('tipo_servicio, COUNT(*) as total')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', date('Y'))
            ->groupBy('tipo_servicio')
            ->get()->getResultArray();

        // Brechas
        $brechas = $this->analizarBrechas($idCliente);

        // Peligros
        $peligrosRaw = $contexto['peligros_identificados'] ?? '[]';
        $peligros = is_string($peligrosRaw) ? (json_decode($peligrosRaw, true) ?? []) : $peligrosRaw;

        return [
            'empresa' => $cliente['nombre_cliente'] ?? 'Sin nombre',
            'nit' => $cliente['nit_cliente'] ?? '',
            'actividad_economica' => $contexto['actividad_economica_principal']
                ?? $contexto['sector_economico']
                ?? $cliente['codigo_actividad_economica']
                ?? 'No especificada',
            'nivel_riesgo' => $contexto['nivel_riesgo_arl'] ?? 'No especificado',
            'total_trabajadores' => (int)($contexto['total_trabajadores'] ?? 0),
            'estandares_aplicables' => (int)($contexto['estandares_aplicables'] ?? 7),
            'peligros' => $peligros,
            'tiene_copasst' => (bool)($contexto['tiene_copasst'] ?? false),
            'tiene_vigia_sst' => (bool)($contexto['tiene_vigia_sst'] ?? false),
            'tiene_brigada' => (bool)($contexto['tiene_brigada_emergencias'] ?? false),
            'tiene_comite_convivencia' => (bool)($contexto['tiene_comite_convivencia'] ?? false),
            'observaciones' => $contexto['observaciones_contexto'] ?? '',
            'resumen_categorias' => $resumenCategorias,
            'resumen_pta' => $ptaResumen,
            'brechas' => $brechas,
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // 3. GENERACION IA
    // ═══════════════════════════════════════════════════════════════

    /**
     * Genera sugerencias de indicadores usando OpenAI
     * Fallback a INDICADORES_BASE_GENERAL si no hay API key
     */
    public function generarSugerenciasIA(int $idCliente, array $contexto, string $instrucciones = '', ?array $categoriasObjetivo = null): array
    {
        $apiKey = env('OPENAI_API_KEY', '');

        if (empty($apiKey)) {
            return $this->generarDesdeBase($idCliente, $contexto, $categoriasObjetivo);
        }

        return $this->generarConOpenAI($idCliente, $contexto, $instrucciones, $categoriasObjetivo, $apiKey);
    }

    /**
     * Fallback: genera desde constante base cuando no hay API key
     */
    protected function generarDesdeBase(int $idCliente, array $contexto, ?array $categoriasObjetivo): array
    {
        $base = self::INDICADORES_BASE_GENERAL;

        if (!empty($categoriasObjetivo)) {
            $base = array_filter($base, fn($ind) => in_array($ind['categoria'], $categoriasObjetivo));
            $base = array_values($base);
        }

        // Limitar segun estandares
        $estandares = $contexto['estandares_aplicables'] ?? 7;
        $limite = $estandares <= 7 ? 6 : ($estandares <= 21 ? 8 : 12);

        return array_slice($base, 0, $limite);
    }

    /**
     * Genera indicadores via OpenAI
     */
    protected function generarConOpenAI(int $idCliente, array $contexto, string $instrucciones, ?array $categoriasObjetivo, string $apiKey): array
    {
        $brechas = $contexto['brechas'] ?? [];
        $existentes = $this->indicadorModel->getByCliente($idCliente);
        $nombresExistentes = array_column($existentes, 'nombre_indicador');

        // Cantidad a generar (proporcional a categorias seleccionadas)
        $estandares = $contexto['estandares_aplicables'] ?? 7;
        $baseCantidad = $estandares <= 7 ? 6 : ($estandares <= 21 ? 8 : 12);
        $cantidadGenerar = !empty($categoriasObjetivo)
            ? max(2, min($baseCantidad, count($categoriasObjetivo) * 3))
            : $baseCantidad;

        // Categorias disponibles
        $categoriasDisponibles = [];
        foreach (IndicadorSSTModel::CATEGORIAS as $key => $info) {
            if ($key === 'otro') continue;
            $categoriasDisponibles[$key] = $info['nombre'];
        }

        // Filtrar categorias objetivo
        $categoriasTexto = '';
        if (!empty($categoriasObjetivo)) {
            $filtradas = [];
            foreach ($categoriasObjetivo as $cat) {
                if (isset($categoriasDisponibles[$cat])) {
                    $filtradas[$cat] = $categoriasDisponibles[$cat];
                }
            }
            $categoriasTexto = "GENERAR SOLO PARA ESTAS CATEGORIAS:\n" . json_encode($filtradas, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            $categoriasTexto = "CATEGORIAS DISPONIBLES (usar solo estas keys):\n" . json_encode($categoriasDisponibles, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        // Construir prompt
        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia.
Tu tarea es generar indicadores del SG-SST para una empresa, cumpliendo con el Decreto 1072/2015 y la Resolucion 0312/2019.

REGLAS ESTRICTAS:
1. Cada indicador DEBE tener exactamente estos 14 campos: nombre, tipo, categoria, formula, meta, unidad, periodicidad, phva, descripcion, definicion, interpretacion, origen_datos, cargo_responsable, cargos_conocer_resultado
2. El campo 'tipo' solo puede ser: estructura, proceso, resultado
3. El campo 'categoria' DEBE ser una key exacta de la lista provista (NO inventar categorias)
4. El campo 'periodicidad' solo puede ser: mensual, trimestral, semestral, anual
5. El campo 'phva' solo puede ser: planear, hacer, verificar, actuar
6. Las formulas deben ser matematicamente correctas y calculables
7. Las metas deben ser numericas, realistas y alcanzables
8. NO duplicar indicadores que ya existen (lista provista)
9. Priorizar categorias con brechas identificadas
10. Adaptar al sector economico, nivel de riesgo y peligros de la empresa
11. Responde SOLO en formato JSON array valido, sin markdown ni texto adicional";

        $peligrosTexto = !empty($contexto['peligros']) ? implode(', ', $contexto['peligros']) : 'No identificados';
        $estructuras = [];
        if ($contexto['tiene_copasst'] ?? false) $estructuras[] = 'COPASST';
        if ($contexto['tiene_vigia_sst'] ?? false) $estructuras[] = 'Vigia SST';
        if ($contexto['tiene_brigada'] ?? false) $estructuras[] = 'Brigada de Emergencias';
        if ($contexto['tiene_comite_convivencia'] ?? false) $estructuras[] = 'Comite de Convivencia';
        $estructurasTexto = !empty($estructuras) ? implode(', ', $estructuras) : 'Ninguna registrada';

        // Brechas formateadas
        $brechasTexto = '';
        if (!empty($brechas['categorias_vacias'])) {
            $nombresVacias = array_map(fn($k) => ($categoriasDisponibles[$k] ?? $k), $brechas['categorias_vacias']);
            $brechasTexto .= "- Categorias SIN indicadores: " . implode(', ', $nombresVacias) . "\n";
        }
        if (!empty($brechas['categorias_escasas'])) {
            foreach ($brechas['categorias_escasas'] as $cat => $info) {
                $brechasTexto .= "- {$categoriasDisponibles[$cat]}: tiene {$info['existentes']}, recomendado {$info['recomendado']}\n";
            }
        }
        if (!empty($brechas['legales_faltantes'])) {
            $brechasTexto .= "- Indicadores legales faltantes: " . implode(', ', array_slice($brechas['legales_faltantes'], 0, 5)) . "\n";
        }
        if (!empty($brechas['recomendacion_tipo'])) {
            $brechasTexto .= "- " . $brechas['recomendacion_tipo'] . "\n";
        }

        $userPrompt = "CONTEXTO DE LA EMPRESA:
- Empresa: {$contexto['empresa']}
- Actividad economica: {$contexto['actividad_economica']}
- Nivel de riesgo ARL: {$contexto['nivel_riesgo']}
- Total trabajadores: {$contexto['total_trabajadores']}
- Estandares aplicables: {$contexto['estandares_aplicables']}
- Peligros identificados: {$peligrosTexto}
- Estructuras organizacionales: {$estructurasTexto}
- Observaciones: " . ($contexto['observaciones'] ?: 'Ninguna') . "

INDICADORES EXISTENTES (NO duplicar estos nombres):
" . implode("\n", array_map(fn($n) => "- {$n}", $nombresExistentes)) . "

ANALISIS DE BRECHAS:
{$brechasTexto}

{$categoriasTexto}

" . (!empty($instrucciones) ? "INSTRUCCIONES DEL CONSULTOR:\n{$instrucciones}\n\n" : '') . "
Genera exactamente {$cantidadGenerar} indicadores distribuidos entre las categorias, priorizando las brechas identificadas. Incluye los 5 campos de ficha tecnica para cada uno.

FORMATO DE RESPUESTA (JSON array):
[{\"nombre\":\"...\",\"tipo\":\"...\",\"categoria\":\"key_exacta\",\"formula\":\"...\",\"meta\":100,\"unidad\":\"%\",\"periodicidad\":\"trimestral\",\"phva\":\"verificar\",\"descripcion\":\"...\",\"definicion\":\"...\",\"interpretacion\":\"...\",\"origen_datos\":\"...\",\"cargo_responsable\":\"...\",\"cargos_conocer_resultado\":\"...\"}]";

        $data = [
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.6,
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
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'IndicadoresGeneralIA: Error de conexion: ' . $error);
            return $this->generarDesdeBase($idCliente, $contexto, $categoriasObjetivo);
        }

        if ($httpCode !== 200) {
            $result = json_decode($response, true);
            log_message('error', 'IndicadoresGeneralIA: HTTP ' . $httpCode . ' - ' . ($result['error']['message'] ?? 'Error desconocido'));
            return $this->generarDesdeBase($idCliente, $contexto, $categoriasObjetivo);
        }

        $result = json_decode($response, true);
        $contenido = $result['choices'][0]['message']['content'] ?? '';

        // Limpiar JSON
        $contenido = preg_replace('/```json\s*/', '', $contenido);
        $contenido = preg_replace('/```\s*/', '', $contenido);
        $contenido = trim($contenido);

        $indicadores = json_decode($contenido, true);
        if (!is_array($indicadores)) {
            log_message('warning', 'IndicadoresGeneralIA: Respuesta JSON invalida: ' . substr($contenido, 0, 200));
            return $this->generarDesdeBase($idCliente, $contexto, $categoriasObjetivo);
        }

        // Validar y normalizar cada indicador
        $categoriasValidas = array_keys(IndicadorSSTModel::CATEGORIAS);
        $indicadoresValidos = [];

        foreach ($indicadores as $ind) {
            if (empty($ind['nombre'])) continue;

            // Validar categoria
            if (!in_array($ind['categoria'] ?? '', $categoriasValidas)) {
                $ind['categoria'] = 'otro';
            }

            // Validar tipo
            if (!in_array($ind['tipo'] ?? '', ['estructura', 'proceso', 'resultado'])) {
                $ind['tipo'] = 'proceso';
            }

            // Validar periodicidad
            if (!in_array($ind['periodicidad'] ?? '', ['mensual', 'trimestral', 'semestral', 'anual'])) {
                $ind['periodicidad'] = 'trimestral';
            }

            // Validar phva
            if (!in_array($ind['phva'] ?? '', ['planear', 'hacer', 'verificar', 'actuar'])) {
                $ind['phva'] = 'verificar';
            }

            $indicadoresValidos[] = $ind;
        }

        // Filtro estricto: descartar categorias que no fueron solicitadas
        if (!empty($categoriasObjetivo)) {
            $indicadoresValidos = array_values(array_filter(
                $indicadoresValidos,
                fn($ind) => in_array($ind['categoria'] ?? '', $categoriasObjetivo)
            ));
        }

        return $indicadoresValidos;
    }

    // ═══════════════════════════════════════════════════════════════
    // 4. PREVIEW
    // ═══════════════════════════════════════════════════════════════

    /**
     * Genera preview de indicadores con deteccion de duplicados
     */
    public function previewIndicadores(int $idCliente, string $instrucciones = '', ?array $categoriasObjetivo = null): array
    {
        $contexto = $this->obtenerContextoCompleto($idCliente);
        $indicadores = $this->generarSugerenciasIA($idCliente, $contexto, $instrucciones, $categoriasObjetivo);

        // Deteccion de duplicados: solo contra indicadores de la MISMA categoria, umbral 80%
        $existentes = $this->indicadorModel->getByCliente($idCliente);
        $existentesPorCat = [];
        foreach ($existentes as $ex) {
            $cat = $ex['categoria'] ?? 'otro';
            $existentesPorCat[$cat][] = mb_strtolower($ex['nombre_indicador']);
        }

        foreach ($indicadores as &$ind) {
            $ind['ya_existe'] = false;
            $ind['seleccionado'] = true;
            $nombreLower = mb_strtolower($ind['nombre']);
            $catIndicador = $ind['categoria'] ?? 'otro';

            // Solo comparar contra indicadores de la misma categoria
            $nombresEnCat = $existentesPorCat[$catIndicador] ?? [];
            foreach ($nombresEnCat as $existente) {
                $similaridad = 0;
                similar_text($nombreLower, $existente, $similaridad);
                if ($similaridad > 80) {
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
            'brechas' => $contexto['brechas'],
            'contexto' => [
                'empresa' => $contexto['empresa'],
                'actividad_economica' => $contexto['actividad_economica'],
                'nivel_riesgo' => $contexto['nivel_riesgo'],
                'total_trabajadores' => $contexto['total_trabajadores'],
                'estandares_aplicables' => $contexto['estandares_aplicables'],
            ],
            'generado_con_ia' => !empty(env('OPENAI_API_KEY', '')),
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // 5. GUARDAR SELECCIONADOS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Guarda los indicadores seleccionados por el consultor en BD
     */
    public function guardarIndicadoresSeleccionados(int $idCliente, array $indicadores): array
    {
        $creados = 0;
        $existentes = 0;
        $errores = [];

        foreach ($indicadores as $ind) {
            $nombre = $ind['nombre'] ?? '';
            if (empty($nombre)) continue;

            // Verificar duplicado: nombre exacto en la misma categoria
            $categoria = $ind['categoria'] ?? 'otro';
            $existe = $this->indicadorModel
                ->where('id_cliente', $idCliente)
                ->where('activo', 1)
                ->where('categoria', $categoria)
                ->where('nombre_indicador', $nombre)
                ->countAllResults();

            if ($existe > 0) {
                $existentes++;
                continue;
            }

            try {
                $this->indicadorModel->insert([
                    'id_cliente' => $idCliente,
                    'nombre_indicador' => $nombre,
                    'tipo_indicador' => $ind['tipo'] ?? 'proceso',
                    'categoria' => $ind['categoria'] ?? 'otro',
                    'formula' => $ind['formula'] ?? '',
                    'meta' => $ind['meta'] ?? null,
                    'unidad_medida' => $ind['unidad'] ?? '%',
                    'periodicidad' => $ind['periodicidad'] ?? 'trimestral',
                    'phva' => $ind['phva'] ?? 'verificar',
                    'numeral_resolucion' => $ind['numeral'] ?? null,
                    'observaciones' => $ind['descripcion'] ?? null,
                    'definicion' => $ind['definicion'] ?? null,
                    'interpretacion' => $ind['interpretacion'] ?? null,
                    'origen_datos' => $ind['origen_datos'] ?? null,
                    'cargo_responsable' => $ind['cargo_responsable'] ?? null,
                    'cargos_conocer_resultado' => $ind['cargos_conocer_resultado'] ?? null,
                    'activo' => 1
                ]);
                $creados++;
            } catch (\Exception $e) {
                $errores[] = "Error en '{$nombre}': " . $e->getMessage();
            }
        }

        return [
            'creados' => $creados,
            'existentes' => $existentes,
            'errores' => $errores,
            'total' => count($indicadores)
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // 6. REGENERAR INDIVIDUAL
    // ═══════════════════════════════════════════════════════════════

    /**
     * Regenera un indicador individual con IA
     */
    public function regenerarIndicador(array $indicadorActual, string $instrucciones, ?array $contexto = null): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'API Key no configurada'];
        }

        $actividadEconomica = $contexto['actividad_economica'] ?? 'No especificada';
        $nivelRiesgo = $contexto['nivel_riesgo'] ?? 'No especificado';
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia.
Tu tarea es mejorar un indicador del SG-SST segun las instrucciones del consultor.

REGLAS:
1. Manten la estructura con 14 campos: nombre, tipo, categoria, formula, meta, unidad, periodicidad, phva, descripcion, definicion, interpretacion, origen_datos, cargo_responsable, cargos_conocer_resultado
2. La formula debe ser matematicamente correcta y calculable
3. La meta debe ser numerica, alcanzable y medible
4. Responde SOLO en formato JSON valido, sin markdown
5. Adapta al contexto de la empresa";

        $userPrompt = "CONTEXTO DE LA EMPRESA:
- Actividad economica: {$actividadEconomica}
- Nivel de riesgo ARL: {$nivelRiesgo}
- Estandares aplicables: {$estandares}

INDICADOR ACTUAL:
" . json_encode($indicadorActual, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "

INSTRUCCIONES DEL CONSULTOR:
\"{$instrucciones}\"

Mejora el indicador segun las instrucciones. Responde SOLO con el JSON del indicador mejorado.";

        $data = [
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 800
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
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200) {
            log_message('error', 'IndicadoresGeneralIA regenerar: ' . ($error ?: 'HTTP ' . $httpCode));
            return ['success' => false, 'error' => 'Error al conectar con la IA'];
        }

        $result = json_decode($response, true);
        $contenido = $result['choices'][0]['message']['content'] ?? '';

        $contenido = preg_replace('/```json\s*/', '', $contenido);
        $contenido = preg_replace('/```\s*/', '', $contenido);

        $respuesta = json_decode(trim($contenido), true);
        if (!$respuesta) {
            return ['success' => false, 'error' => 'Respuesta IA invalida'];
        }

        return [
            'success' => true,
            'data' => array_merge($indicadorActual, $respuesta)
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // CONSTANTE: INDICADORES BASE GENERAL (Fallback sin API)
    // ═══════════════════════════════════════════════════════════════

    public const INDICADORES_BASE_GENERAL = [
        // --- Accidentalidad ---
        [
            'nombre' => 'Indice de Frecuencia de Accidentes de Trabajo',
            'tipo' => 'resultado',
            'categoria' => 'accidentalidad',
            'formula' => '(Numero de AT en el periodo / HHT en el periodo) x 200.000',
            'meta' => 0,
            'unidad' => 'IF',
            'periodicidad' => 'mensual',
            'phva' => 'verificar',
            'descripcion' => 'Mide la frecuencia de accidentes de trabajo por horas trabajadas',
            'definicion' => 'Numero de accidentes de trabajo que se presentan por cada 200.000 horas hombre trabajadas en el periodo.',
            'interpretacion' => 'Valores menores indican mejor desempeno. La meta es reducir progresivamente. Comparar con promedios del sector.',
            'origen_datos' => 'Registros FURAT, nomina (horas trabajadas), reportes de accidentalidad',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL'
        ],
        [
            'nombre' => 'Indice de Severidad de Accidentes de Trabajo',
            'tipo' => 'resultado',
            'categoria' => 'accidentalidad',
            'formula' => '(Dias perdidos por AT / HHT en el periodo) x 200.000',
            'meta' => 0,
            'unidad' => 'IS',
            'periodicidad' => 'mensual',
            'phva' => 'verificar',
            'descripcion' => 'Mide la gravedad de los accidentes por dias perdidos',
            'definicion' => 'Numero de dias perdidos por accidentes de trabajo por cada 200.000 horas hombre trabajadas en el periodo.',
            'interpretacion' => 'Valores menores indican menor severidad. Aumentos subitos requieren investigacion inmediata de causas.',
            'origen_datos' => 'Registros FURAT, incapacidades medicas, nomina',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia, ARL'
        ],
        // --- PTA ---
        [
            'nombre' => 'Cumplimiento del Plan de Trabajo Anual',
            'tipo' => 'proceso',
            'categoria' => 'pta',
            'formula' => '(Actividades ejecutadas / Actividades programadas) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'descripcion' => 'Mide el porcentaje de ejecucion del plan de trabajo anual del SG-SST',
            'definicion' => 'Proporcion de actividades del plan de trabajo anual del SG-SST ejecutadas frente a las programadas para el periodo.',
            'interpretacion' => 'El 100% indica cumplimiento total. Valores <80% requieren analisis de causas y reprogramacion.',
            'origen_datos' => 'Plan de trabajo anual SST, actas de seguimiento, registros de ejecucion',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia'
        ],
        // --- Inspecciones ---
        [
            'nombre' => 'Cumplimiento del Programa de Inspecciones',
            'tipo' => 'proceso',
            'categoria' => 'inspecciones',
            'formula' => '(Inspecciones realizadas / Inspecciones programadas) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'descripcion' => 'Mide el cumplimiento del cronograma de inspecciones de seguridad',
            'definicion' => 'Proporcion de inspecciones de seguridad ejecutadas respecto a las programadas en el cronograma.',
            'interpretacion' => 'El 100% indica cumplimiento total del programa. Valores menores requieren reprogramacion.',
            'origen_datos' => 'Cronograma de inspecciones, formatos de inspeccion diligenciados',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia'
        ],
        // --- Emergencias ---
        [
            'nombre' => 'Cumplimiento del Plan de Emergencias',
            'tipo' => 'proceso',
            'categoria' => 'emergencias',
            'formula' => '(Simulacros realizados / Simulacros programados) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'semestral',
            'phva' => 'verificar',
            'descripcion' => 'Mide el cumplimiento de simulacros y actividades del plan de emergencias',
            'definicion' => 'Proporcion de simulacros de emergencia ejecutados frente a los programados en el plan anual.',
            'interpretacion' => 'El 100% indica cumplimiento total. Minimo 1 simulacro anual segun normativa.',
            'origen_datos' => 'Plan de emergencias, actas de simulacros, registros de asistencia',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Brigada de Emergencias, COPASST/Vigia'
        ],
        // --- Riesgos ---
        [
            'nombre' => 'Intervencion de Peligros Identificados',
            'tipo' => 'proceso',
            'categoria' => 'riesgos',
            'formula' => '(Peligros con medidas de intervencion / Total peligros identificados) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'semestral',
            'phva' => 'hacer',
            'descripcion' => 'Mide el porcentaje de peligros que tienen medidas de control implementadas',
            'definicion' => 'Proporcion de peligros de la matriz IPEVR que cuentan con medidas de intervencion efectivas implementadas.',
            'interpretacion' => 'El 100% indica que todos los peligros tienen control. Priorizar peligros con nivel de riesgo alto.',
            'origen_datos' => 'Matriz de identificacion de peligros y valoracion de riesgos (IPEVR)',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia'
        ],
        // --- Ausentismo ---
        [
            'nombre' => 'Indice de Ausentismo por Causa Medica',
            'tipo' => 'resultado',
            'categoria' => 'ausentismo',
            'formula' => '(Dias de ausencia por causa medica / Dias programados de trabajo) x 100',
            'meta' => 3,
            'unidad' => '%',
            'periodicidad' => 'mensual',
            'phva' => 'verificar',
            'descripcion' => 'Mide el porcentaje de dias perdidos por causas medicas',
            'definicion' => 'Proporcion de dias de ausencia laboral por causas medicas (enfermedad comun, AT, EL) frente al total de dias programados.',
            'interpretacion' => 'Valores <=3% son aceptables. Incrementos sostenidos requieren analisis de causalidad y programas preventivos.',
            'origen_datos' => 'Registros de incapacidades, nomina, reportes de ausentismo',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Gestion Humana'
        ],
        // --- Vigilancia ---
        [
            'nombre' => 'Cobertura de Examenes Medicos Ocupacionales',
            'tipo' => 'proceso',
            'categoria' => 'vigilancia',
            'formula' => '(Trabajadores con examen medico vigente / Total trabajadores) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'anual',
            'phva' => 'verificar',
            'descripcion' => 'Mide la cobertura de examenes medicos ocupacionales',
            'definicion' => 'Proporcion de trabajadores que cuentan con evaluacion medica ocupacional vigente (ingreso, periodico, retiro).',
            'interpretacion' => 'El 100% es obligatorio por ley. Cualquier valor menor requiere accion inmediata para completar examenes.',
            'origen_datos' => 'Profesiograma, certificados de aptitud, registros del proveedor de examenes',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia'
        ],
        // --- Objetivos SG-SST ---
        [
            'nombre' => 'Cumplimiento de Objetivos del SG-SST',
            'tipo' => 'resultado',
            'categoria' => 'objetivos_sgsst',
            'formula' => '(Objetivos cumplidos / Total objetivos definidos) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'semestral',
            'phva' => 'verificar',
            'descripcion' => 'Mide el grado de cumplimiento de los objetivos del SG-SST',
            'definicion' => 'Proporcion de objetivos del SG-SST que alcanzaron la meta establecida en el periodo de evaluacion.',
            'interpretacion' => 'El 100% indica logro total de objetivos. Valores menores requieren revision y ajuste de estrategias.',
            'origen_datos' => 'Matriz de objetivos y metas SST, indicadores asociados a cada objetivo',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia'
        ],
        // --- PyP Salud ---
        [
            'nombre' => 'Cobertura del Programa de Promocion y Prevencion',
            'tipo' => 'proceso',
            'categoria' => 'pyp_salud',
            'formula' => '(Trabajadores participantes en actividades PyP / Total trabajadores) x 100',
            'meta' => 80,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'descripcion' => 'Mide la participacion en actividades de promocion y prevencion en salud',
            'definicion' => 'Proporcion de trabajadores que participaron en al menos una actividad del programa de promocion y prevencion en salud.',
            'interpretacion' => 'Valores >=80% indican buena cobertura. Valores menores requieren estrategias de motivacion y sensibilizacion.',
            'origen_datos' => 'Registros de asistencia, actas de actividades PyP, nomina',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia'
        ],
        // --- Induccion ---
        [
            'nombre' => 'Cobertura de Induccion y Reinduccion SST',
            'tipo' => 'proceso',
            'categoria' => 'induccion',
            'formula' => '(Trabajadores con induccion/reinduccion / Total trabajadores que requerian) x 100',
            'meta' => 100,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'descripcion' => 'Mide el porcentaje de trabajadores con induccion o reinduccion en SST',
            'definicion' => 'Proporcion de trabajadores nuevos o que requerian reinduccion que recibieron la formacion de induccion en SST.',
            'interpretacion' => 'El 100% es obligatorio para todo trabajador nuevo. La reinduccion debe hacerse minimo cada ano.',
            'origen_datos' => 'Registros de induccion, actas de reinduccion, listados de personal nuevo',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Gestion Humana'
        ],
        // --- Capacitacion (extra) ---
        [
            'nombre' => 'Eficacia de las Capacitaciones en SST',
            'tipo' => 'resultado',
            'categoria' => 'capacitacion',
            'formula' => '(Promedio evaluaciones post-capacitacion / Puntaje maximo) x 100',
            'meta' => 80,
            'unidad' => '%',
            'periodicidad' => 'trimestral',
            'phva' => 'verificar',
            'descripcion' => 'Mide el aprovechamiento y aprendizaje de los trabajadores en las capacitaciones',
            'definicion' => 'Nivel de aprendizaje de los trabajadores en las capacitaciones de SST, medido mediante evaluaciones post-capacitacion.',
            'interpretacion' => 'Valores >=80% indican buena eficacia. Valores menores sugieren ajustar metodologia o contenidos.',
            'origen_datos' => 'Evaluaciones post-capacitacion, encuestas de satisfaccion',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigia'
        ],
    ];
}
