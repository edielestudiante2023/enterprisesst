<?php

namespace App\Services;

use App\Models\IndicadorSSTModel;
use App\Models\ClienteContextoSstModel;

/**
 * Servicio de Ingenieria Inversa: Indicadores → Actividades PTA
 *
 * Genera actividades para el Plan de Trabajo Anual a partir de los indicadores
 * existentes del cliente, especialmente los que no tienen actividades asociadas.
 *
 * Vive en el modulo de indicadores (/indicadores-sst/{id}), NO es un servicio
 * Parte 1 independiente.
 */
class ActividadesDesdeIndicadoresService
{
    protected IndicadorSSTModel $indicadorModel;
    protected $db;

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
        $this->db = \Config\Database::connect();
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS DINAMICOS — Sin mapeos hardcodeados
    // ═══════════════════════════════════════════════════════════════

    /**
     * Quita tildes/acentos de un string para comparacion insensible a acentos.
     * Asi "Capacitación" y "Capacitacion" se normalizan a lo mismo.
     */
    protected function quitarTildes(string $str): string
    {
        return strtr($str, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ñ' => 'n', 'Ñ' => 'N', 'ü' => 'u', 'Ü' => 'U',
        ]);
    }

    /**
     * Deriva keywords de busqueda DINAMICAMENTE desde IndicadorSSTModel::CATEGORIAS['nombre']
     * y desde el key de la categoria (split por '_').
     *
     * Ejemplo: 'vigilancia' → nombre 'Vigilancia Epidemiológica'
     *   → keywords: ['vigilancia', 'epidemiologica']
     *
     * NO hay ningun mapeo hardcodeado. Si se agrega una nueva categoria a CATEGORIAS,
     * funciona automaticamente.
     */
    protected function derivarKeywordsDesdeCategoria(string $cat): array
    {
        $stopWords = ['de', 'del', 'y', 'en', 'la', 'el', 'los', 'las', 'un', 'una', 'por', 'para', 'al'];

        $keywords = [];

        // Fuente 1: nombre legible de la categoria (ej: 'Vigilancia Epidemiológica')
        $catInfo = IndicadorSSTModel::CATEGORIAS[$cat] ?? null;
        if ($catInfo) {
            $normalizado = $this->quitarTildes(mb_strtolower($catInfo['nombre']));
            $palabras = preg_split('/[\s,\-\/]+/', $normalizado);
            foreach ($palabras as $palabra) {
                $palabra = trim($palabra);
                if (mb_strlen($palabra) >= 3 && !in_array($palabra, $stopWords)) {
                    $keywords[] = $palabra;
                }
            }
        }

        // Fuente 2: el key de la categoria (ej: 'pve_biomecanico' → ['pve', 'biomecanico'])
        $keyParts = explode('_', $cat);
        foreach ($keyParts as $part) {
            $part = mb_strtolower(trim($part));
            if (mb_strlen($part) >= 3 && !in_array($part, $stopWords) && !in_array($part, $keywords)) {
                $keywords[] = $part;
            }
        }

        return array_unique($keywords);
    }

    /**
     * Resuelve el tipo_servicio a usar al INSERTAR una nueva actividad PTA.
     *
     * Estrategia (100% dinamica, sin mapeos hardcodeados):
     * 1. Busca en el PTA existente del cliente un tipo_servicio que matchee la categoria
     *    → si encuentra, REUTILIZA ese string exacto (garantiza consistencia)
     * 2. Si no hay match, usa el 'nombre' de CATEGORIAS como default
     *    → ej: 'Vigilancia Epidemiológica'
     */
    protected function resolverTipoServicio(string $categoriaOrigen, int $idCliente): string
    {
        $keywords = $this->derivarKeywordsDesdeCategoria($categoriaOrigen);
        if (empty($keywords)) {
            return IndicadorSSTModel::CATEGORIAS[$categoriaOrigen]['nombre'] ?? 'Actividades SST General';
        }

        $anio = date('Y');
        $tiposExistentes = $this->db->table('tbl_pta_cliente')
            ->select('tipo_servicio, COUNT(*) as total')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->where('tipo_servicio !=', '')
            ->groupBy('tipo_servicio')
            ->orderBy('total', 'DESC')
            ->get()->getResultArray();

        // Scoring: buscar el tipo_servicio con MAS keywords coincidentes
        // Umbral: con >2 keywords exigir al menos 2 matches (evita falsos positivos)
        $umbralMatch = count($keywords) > 2 ? 2 : 1;
        $mejorTipo = null;
        $mejorScore = 0;

        foreach ($tiposExistentes as $row) {
            $tipoNorm = $this->quitarTildes(mb_strtolower($row['tipo_servicio']));
            $score = 0;
            foreach ($keywords as $kw) {
                if (mb_strpos($tipoNorm, $kw) !== false) {
                    $score++;
                }
            }
            if ($score > $mejorScore) {
                $mejorScore = $score;
                $mejorTipo = $row['tipo_servicio'];
            }
        }

        if ($mejorTipo && $mejorScore >= $umbralMatch) {
            return $mejorTipo; // Reutilizar el string exacto del cliente
        }

        // Fallback: nombre de la categoria segun CATEGORIAS
        $catInfo = IndicadorSSTModel::CATEGORIAS[$categoriaOrigen] ?? null;
        return $catInfo ? $catInfo['nombre'] : 'Actividades SST General';
    }

    /**
     * Normaliza el categoria_origen que viene de la IA.
     * La IA puede retornar el key, el nombre, con tildes, mayusculas, etc.
     * Intenta matchear con: key exacto, lowercase, por nombre, por keyword parcial.
     */
    protected function normalizarCategoriaOrigen(string $valor, array $categoriasValidas, array $lookupPorNombre): string
    {
        // 1. Match exacto por key
        if (in_array($valor, $categoriasValidas)) {
            return $valor;
        }

        // 2. Match por key en lowercase
        $valorLower = mb_strtolower(trim($valor));
        if (in_array($valorLower, $categoriasValidas)) {
            return $valorLower;
        }

        // 3. Match por nombre normalizado (ej: 'Accidentalidad' → 'accidentalidad' → key)
        $valorNorm = $this->quitarTildes($valorLower);
        if (isset($lookupPorNombre[$valorNorm])) {
            return $lookupPorNombre[$valorNorm];
        }

        // 4. Match parcial: buscar si el valor contiene o esta contenido en algun key/nombre
        foreach (IndicadorSSTModel::CATEGORIAS as $key => $info) {
            $nombreNorm = $this->quitarTildes(mb_strtolower($info['nombre']));
            // El valor contiene el nombre o viceversa
            if (mb_strlen($valorNorm) >= 4 && mb_strlen($nombreNorm) >= 4) {
                if (mb_strpos($valorNorm, $nombreNorm) !== false || mb_strpos($nombreNorm, $valorNorm) !== false) {
                    return $key;
                }
            }
            // Buscar por partes del key (ej: 'biomecanico' matchea 'pve_biomecanico')
            foreach (explode('_', $key) as $part) {
                if (mb_strlen($part) >= 4 && mb_strpos($valorNorm, $part) !== false) {
                    return $key;
                }
            }
        }

        // 5. Fallback
        log_message('debug', 'ActividadesDesdeIndicadores: categoria_origen no reconocida: "' . $valor . '"');
        return 'otro';
    }

    // ═══════════════════════════════════════════════════════════════
    // 1. CONTEXTO PARA GENERACION DE ACTIVIDADES
    // ═══════════════════════════════════════════════════════════════

    /**
     * Contexto completo: datos del cliente + indicadores + PTA existente
     */
    public function obtenerContextoParaActividades(int $idCliente): array
    {
        // Reutilizar contexto base del servicio de indicadores
        $svcIndicadores = new IndicadoresGeneralIAService();
        $contextoBase = $svcIndicadores->obtenerContextoCompleto($idCliente);

        // Indicadores agrupados por categoria con detalle
        $indicadores = $this->indicadorModel->getByCliente($idCliente);
        $indicadoresPorCat = [];
        foreach ($indicadores as $ind) {
            $cat = $ind['categoria'] ?? 'otro';
            $indicadoresPorCat[$cat][] = [
                'id' => $ind['id_indicador'],
                'nombre' => $ind['nombre_indicador'],
                'formula' => $ind['formula'] ?? '',
                'tipo' => $ind['tipo_indicador'] ?? 'proceso',
            ];
        }

        // Actividades PTA existentes agrupadas por tipo_servicio
        $anio = date('Y');
        $ptaExistentes = $this->db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->get()->getResultArray();

        $ptaPorTipo = [];
        foreach ($ptaExistentes as $act) {
            $tipo = $act['tipo_servicio'] ?? 'Otros';
            $ptaPorTipo[$tipo][] = $act['actividad_plandetrabajo'];
        }

        // Analizar indicadores sin actividades
        $huerfanos = $this->analizarIndicadoresSinActividades($idCliente, $indicadoresPorCat, $ptaPorTipo);

        return array_merge($contextoBase, [
            'indicadores_por_categoria' => $indicadoresPorCat,
            'pta_por_tipo' => $ptaPorTipo,
            'total_pta' => count($ptaExistentes),
            'indicadores_huerfanos' => $huerfanos,
            'total_indicadores' => count($indicadores),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // 2. ANALISIS DE INDICADORES SIN ACTIVIDADES
    // ═══════════════════════════════════════════════════════════════

    /**
     * Detecta indicadores que no tienen actividades PTA asociadas
     */
    public function analizarIndicadoresSinActividades(int $idCliente, ?array $indicadoresPorCat = null, ?array $ptaPorTipo = null): array
    {
        if ($indicadoresPorCat === null) {
            $indicadores = $this->indicadorModel->getByCliente($idCliente);
            $indicadoresPorCat = [];
            foreach ($indicadores as $ind) {
                $cat = $ind['categoria'] ?? 'otro';
                $indicadoresPorCat[$cat][] = [
                    'id' => $ind['id_indicador'],
                    'nombre' => $ind['nombre_indicador'],
                    'formula' => $ind['formula'] ?? '',
                    'tipo' => $ind['tipo_indicador'] ?? 'proceso',
                ];
            }
        }

        if ($ptaPorTipo === null) {
            $anio = date('Y');
            $ptaExistentes = $this->db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('YEAR(fecha_propuesta)', $anio)
                ->get()->getResultArray();

            $ptaPorTipo = [];
            foreach ($ptaExistentes as $act) {
                $tipo = $act['tipo_servicio'] ?? 'Otros';
                $ptaPorTipo[$tipo][] = $act['actividad_plandetrabajo'];
            }
        }

        $huerfanos = [];

        // Normalizar tipo_servicio del PTA (quitar tildes + lowercase) una sola vez
        $tiposNormalizados = [];
        foreach (array_keys($ptaPorTipo) as $tipo) {
            $tiposNormalizados[$tipo] = $this->quitarTildes(mb_strtolower($tipo));
        }

        foreach ($indicadoresPorCat as $cat => $inds) {
            // Keywords derivados DINAMICAMENTE desde CATEGORIAS['nombre'] + key de categoria
            $keywords = $this->derivarKeywordsDesdeCategoria($cat);
            $tieneActividades = false;

            if (!empty($keywords)) {
                // Matching por SCORING: contar cuantos keywords coinciden con cada tipo_servicio.
                // Umbral: si hay >2 keywords, exigir al menos 2 matches (evita falsos positivos
                // por keywords cortos/genericos como 'sst', 'riesgo'). Con 1-2 keywords, basta 1.
                $umbralMatch = count($keywords) > 2 ? 2 : 1;
                $mejorScore = 0;

                foreach ($tiposNormalizados as $tipoOriginal => $tipoNorm) {
                    $score = 0;
                    foreach ($keywords as $kw) {
                        if (mb_strpos($tipoNorm, $kw) !== false) {
                            $score++;
                        }
                    }
                    $mejorScore = max($mejorScore, $score);
                }

                $tieneActividades = ($mejorScore >= $umbralMatch);
            }

            if (!$tieneActividades) {
                foreach ($inds as $ind) {
                    $huerfanos[] = array_merge($ind, ['categoria' => $cat, 'razon' => 'sin_actividades_pta']);
                }
            }
        }

        return $huerfanos;
    }

    // ═══════════════════════════════════════════════════════════════
    // 3. GENERACION IA DE ACTIVIDADES
    // ═══════════════════════════════════════════════════════════════

    /**
     * Genera sugerencias de actividades via OpenAI o fallback
     */
    public function generarSugerenciasIA(int $idCliente, array $contexto, string $instrucciones = '', ?array $categoriasObjetivo = null): array
    {
        $apiKey = env('OPENAI_API_KEY', '');

        if (empty($apiKey)) {
            return $this->generarDesdeBase($contexto, $categoriasObjetivo);
        }

        return $this->generarConOpenAI($idCliente, $contexto, $instrucciones, $categoriasObjetivo, $apiKey);
    }

    /**
     * Fallback: actividades base cuando no hay API key o la IA retorna 0 actividades.
     *
     * Si las categorias solicitadas NO tienen entries en ACTIVIDADES_BASE_POR_CATEGORIA,
     * genera actividades DINAMICAMENTE desde IndicadorSSTModel::CATEGORIAS (nombre + descripcion).
     * Asi NUNCA retorna vacio, sin importar cuantas categorias nuevas se agreguen.
     */
    protected function generarDesdeBase(array $contexto, ?array $categoriasObjetivo): array
    {
        $base = self::ACTIVIDADES_BASE_POR_CATEGORIA;

        // Determinar categorias objetivo
        $catsObjetivo = $categoriasObjetivo;
        if (empty($catsObjetivo)) {
            $huerfanos = $contexto['indicadores_huerfanos'] ?? [];
            if (!empty($huerfanos)) {
                $catsObjetivo = array_unique(array_column($huerfanos, 'categoria'));
            }
        }

        // Filtrar base por categorias objetivo
        if (!empty($catsObjetivo)) {
            $base = array_filter($base, fn($a) => in_array($a['categoria_origen'], $catsObjetivo));
            $base = array_values($base);
        }

        // Filtrar por huerfanos si hay contexto
        $huerfanos = $contexto['indicadores_huerfanos'] ?? [];
        if (!empty($huerfanos) && !empty($base)) {
            $catsHuerfanas = array_unique(array_column($huerfanos, 'categoria'));
            $filtradas = array_filter($base, fn($a) => in_array($a['categoria_origen'], $catsHuerfanas));
            if (!empty($filtradas)) {
                $base = array_values($filtradas);
            }
        }

        // DINAMICO: Para categorias sin entries en la constante, generar actividades automaticas
        // usando la info de IndicadorSSTModel::CATEGORIAS (nombre, descripcion)
        $catsCubiertas = array_unique(array_column($base, 'categoria_origen'));
        $catsFaltantes = !empty($catsObjetivo)
            ? array_diff($catsObjetivo, $catsCubiertas)
            : [];

        // Si no hay categorias objetivo pero tampoco hay base, usar todas las huerfanas
        if (empty($catsObjetivo) && empty($base) && !empty($huerfanos)) {
            $catsFaltantes = array_unique(array_column($huerfanos, 'categoria'));
        }

        foreach ($catsFaltantes as $catKey) {
            $catInfo = IndicadorSSTModel::CATEGORIAS[$catKey] ?? null;
            if (!$catInfo) continue;

            $nombre = $catInfo['nombre']; // ej: "Evaluaciones Médicas Ocupacionales"

            // Generar 2 actividades genericas adaptadas al nombre de la categoria
            $base[] = [
                'actividad' => "Implementar programa de {$nombre}",
                'descripcion' => "Planificar, ejecutar y hacer seguimiento al programa de {$nombre} segun los requisitos del SG-SST",
                'responsable' => 'Responsable SST',
                'phva' => 'HACER',
                'numeral' => '3.1.1',
                'periodicidad' => 'trimestral',
                'meta' => '100% de cumplimiento del programa',
                'mes_inicio' => 2,
                'categoria_origen' => $catKey
            ];

            $base[] = [
                'actividad' => "Seguimiento a indicadores de {$nombre}",
                'descripcion' => "Medir y analizar los indicadores asociados a {$nombre}, identificar desviaciones y definir acciones correctivas",
                'responsable' => 'Responsable SST',
                'phva' => 'VERIFICAR',
                'numeral' => '6.1.3',
                'periodicidad' => 'trimestral',
                'meta' => 'Indicadores medidos y analizados',
                'mes_inicio' => 4,
                'categoria_origen' => $catKey
            ];
        }

        return array_slice($base, 0, 12);
    }

    /**
     * Genera actividades via OpenAI
     */
    protected function generarConOpenAI(int $idCliente, array $contexto, string $instrucciones, ?array $categoriasObjetivo, string $apiKey): array
    {
        $huerfanos = $contexto['indicadores_huerfanos'] ?? [];
        $ptaPorTipo = $contexto['pta_por_tipo'] ?? [];
        $indicadoresPorCat = $contexto['indicadores_por_categoria'] ?? [];

        // Categorias objetivo: si no se especifican, usar las de indicadores huerfanos
        $catsObjetivo = $categoriasObjetivo;
        if (empty($catsObjetivo) && !empty($huerfanos)) {
            $catsObjetivo = array_unique(array_column($huerfanos, 'categoria'));
        }
        if (empty($catsObjetivo)) {
            $catsObjetivo = array_keys($indicadoresPorCat);
        }

        // Formatear indicadores huerfanos para el prompt
        $huerfanosTexto = '';
        if (!empty($huerfanos)) {
            $huerfanosTexto = "INDICADORES SIN ACTIVIDADES ASOCIADAS (priorizar estos):\n";
            foreach ($huerfanos as $h) {
                $huerfanosTexto .= "- [{$h['categoria']}] {$h['nombre']}: {$h['formula']}\n";
            }
        }

        // Formatear todos los indicadores por categoria para contexto
        $indicadoresTexto = "INDICADORES EXISTENTES DEL CLIENTE:\n";
        foreach ($indicadoresPorCat as $cat => $inds) {
            if (!empty($catsObjetivo) && !in_array($cat, $catsObjetivo)) continue;
            $indicadoresTexto .= "\n[{$cat}] (" . count($inds) . " indicadores):\n";
            foreach ($inds as $ind) {
                $indicadoresTexto .= "  - {$ind['nombre']} ({$ind['tipo']}): {$ind['formula']}\n";
            }
        }

        // Formatear PTA existente para anti-duplicacion
        $ptaTexto = "ACTIVIDADES YA EXISTENTES EN PTA (NO duplicar):\n";
        foreach ($ptaPorTipo as $tipo => $acts) {
            $ptaTexto .= "\n[{$tipo}]:\n";
            foreach (array_slice($acts, 0, 10) as $a) {
                $ptaTexto .= "  - " . substr($a, 0, 80) . "\n";
            }
            if (count($acts) > 10) {
                $ptaTexto .= "  ... y " . (count($acts) - 10) . " mas\n";
            }
        }

        $cantidadGenerar = max(5, min(15, count($catsObjetivo) * 3));

        $peligrosTexto = !empty($contexto['peligros']) ? implode(', ', $contexto['peligros']) : 'No identificados';

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia.
Tu tarea es generar actividades concretas para el Plan de Trabajo Anual (PTA) que hagan medibles los indicadores del cliente.

REGLAS ESTRICTAS:
1. Cada actividad DEBE tener exactamente estos 9 campos: actividad (nombre corto max 80 chars), descripcion (que hacer, max 200 chars), responsable, phva (PLANEAR/HACER/VERIFICAR/ACTUAR en MAYUSCULAS), numeral (numeral Res. 0312/2019), periodicidad (mensual/bimestral/trimestral/semestral/anual), meta (texto corto de meta medible), mes_inicio (1-12), categoria_origen (key exacta de la categoria del indicador que alimenta)
2. Las actividades deben ser CONCRETAS y EJECUTABLES (no genericas)
3. Cada actividad debe contribuir directamente a medir al menos un indicador existente
4. NO duplicar actividades que ya existen en el PTA del cliente
5. Distribuir actividades a lo largo del ano (meses variados)
6. PHVA debe ser en MAYUSCULAS: PLANEAR, HACER, VERIFICAR, ACTUAR
7. Los numerales deben corresponder a la Resolucion 0312/2019
8. Responde SOLO en formato JSON array valido, sin markdown ni texto adicional";

        // Construir descripcion de categorias objetivo con nombre + descripcion de CATEGORIAS
        // para que OpenAI entienda QUE tipo de actividades generar (no solo el key)
        $catsDescripcion = '';
        foreach ($catsObjetivo as $catKey) {
            $catInfo = IndicadorSSTModel::CATEGORIAS[$catKey] ?? null;
            if ($catInfo) {
                $catsDescripcion .= "- {$catKey}: \"{$catInfo['nombre']}\" — {$catInfo['descripcion']}\n";
            } else {
                $catsDescripcion .= "- {$catKey}\n";
            }
        }
        $ejemploKey = $catsObjetivo[0] ?? 'emergencias';

        $observacionesTexto = !empty($contexto['observaciones']) ? "\nOBSERVACIONES DEL CONSULTOR:\n{$contexto['observaciones']}\n" : '';

        $userPrompt = "CONTEXTO DE LA EMPRESA:
- Empresa: {$contexto['empresa']}
- Actividad economica: {$contexto['actividad_economica']}
- Nivel de riesgo ARL: {$contexto['nivel_riesgo']}
- Total trabajadores: {$contexto['total_trabajadores']}
- Estandares aplicables: {$contexto['estandares_aplicables']}
- Peligros identificados: {$peligrosTexto}
{$observacionesTexto}
{$indicadoresTexto}

{$huerfanosTexto}

{$ptaTexto}

" . (!empty($instrucciones) ? "INSTRUCCIONES DEL CONSULTOR:\n{$instrucciones}\n\n" : '') . "CATEGORIAS OBJETIVO — Genera actividades ESPECIFICAS y RELEVANTES para cada una.
Usa UNICAMENTE estas keys exactas en el campo categoria_origen:
{$catsDescripcion}
IMPORTANTE: Las actividades deben ser DIRECTAMENTE RELACIONADAS con la tematica de su categoria.
Por ejemplo, si la categoria es 'evaluaciones_medicas_ocupacionales', genera actividades como programar examenes medicos, seguimiento a recomendaciones medico-laborales, analisis de profesiograma, etc. NO actividades genericas de otras areas.

Genera exactamente {$cantidadGenerar} actividades para el PTA. Distribuye entre TODAS las categorias objetivo.

FORMATO DE RESPUESTA (JSON array):
[{\"actividad\":\"...\",\"descripcion\":\"...\",\"responsable\":\"...\",\"phva\":\"HACER\",\"numeral\":\"3.1.2\",\"periodicidad\":\"trimestral\",\"meta\":\"100% de cumplimiento\",\"mes_inicio\":3,\"categoria_origen\":\"{$ejemploKey}\"}]";

        $data = [
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.5,
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
            log_message('error', 'ActividadesDesdeIndicadores: Error de conexion: ' . $error);
            return $this->generarDesdeBase($contexto, $categoriasObjetivo);
        }

        log_message('debug', 'ActividadesDesdeIndicadores: OpenAI HTTP ' . $httpCode);

        if ($httpCode !== 200) {
            $result = json_decode($response, true);
            log_message('error', 'ActividadesDesdeIndicadores: HTTP ' . $httpCode . ' - ' . ($result['error']['message'] ?? 'Error desconocido'));
            return $this->generarDesdeBase($contexto, $categoriasObjetivo);
        }

        $result = json_decode($response, true);
        $contenido = $result['choices'][0]['message']['content'] ?? '';

        // Limpiar JSON
        $contenido = preg_replace('/```json\s*/', '', $contenido);
        $contenido = preg_replace('/```\s*/', '', $contenido);
        $contenido = trim($contenido);

        $actividades = json_decode($contenido, true);
        if (!is_array($actividades)) {
            log_message('warning', 'ActividadesDesdeIndicadores: Respuesta JSON invalida: ' . substr($contenido, 0, 200));
            return $this->generarDesdeBase($contexto, $categoriasObjetivo);
        }

        log_message('debug', 'ActividadesDesdeIndicadores: OpenAI retorno ' . count($actividades) . ' actividades raw');

        // Validar y normalizar cada actividad
        $categoriasValidas = array_keys(IndicadorSSTModel::CATEGORIAS);

        // Construir lookup reverso: nombre normalizado → key
        $lookupPorNombre = [];
        foreach (IndicadorSSTModel::CATEGORIAS as $key => $info) {
            $nombreNorm = $this->quitarTildes(mb_strtolower($info['nombre']));
            $lookupPorNombre[$nombreNorm] = $key;
        }

        $actividadesValidas = [];

        foreach ($actividades as $act) {
            if (empty($act['actividad'])) continue;

            // Normalizar categoria_origen de la IA (robusta a variantes)
            $act['categoria_origen'] = $this->normalizarCategoriaOrigen(
                $act['categoria_origen'] ?? '', $categoriasValidas, $lookupPorNombre
            );

            // Validar PHVA (normalizar a MAYUSCULAS)
            $phvaValidos = ['PLANEAR', 'HACER', 'VERIFICAR', 'ACTUAR'];
            $act['phva'] = strtoupper($act['phva'] ?? 'HACER');
            if (!in_array($act['phva'], $phvaValidos)) {
                $act['phva'] = 'HACER';
            }

            // Validar periodicidad
            if (!in_array($act['periodicidad'] ?? '', ['mensual', 'bimestral', 'trimestral', 'semestral', 'anual'])) {
                $act['periodicidad'] = 'trimestral';
            }

            // Validar mes_inicio
            $act['mes_inicio'] = max(1, min(12, (int)($act['mes_inicio'] ?? 1)));

            $actividadesValidas[] = $act;
        }

        log_message('debug', 'ActividadesDesdeIndicadores: ' . count($actividadesValidas) . ' actividades post-validacion. Categorias: ' .
            implode(', ', array_unique(array_column($actividadesValidas, 'categoria_origen'))));

        // Filtro estricto: solo categorias objetivo
        if (!empty($categoriasObjetivo)) {
            $antesDelFiltro = count($actividadesValidas);
            $actividadesValidas = array_values(array_filter(
                $actividadesValidas,
                fn($a) => in_array($a['categoria_origen'] ?? '', $categoriasObjetivo)
            ));
            log_message('debug', 'ActividadesDesdeIndicadores: Filtro categorias (' . implode(',', $categoriasObjetivo) .
                '): ' . $antesDelFiltro . ' → ' . count($actividadesValidas));
        }

        // Failsafe: si OpenAI no genero nada util, usar fallback
        if (empty($actividadesValidas)) {
            log_message('warning', 'ActividadesDesdeIndicadores: OpenAI retorno 0 actividades validas, usando fallback');
            return $this->generarDesdeBase($contexto, $categoriasObjetivo);
        }

        return $actividadesValidas;
    }

    // ═══════════════════════════════════════════════════════════════
    // 4. PREVIEW
    // ═══════════════════════════════════════════════════════════════

    /**
     * Genera preview de actividades con deteccion de duplicados
     */
    public function previewActividades(int $idCliente, string $instrucciones = '', ?array $categoriasObjetivo = null): array
    {
        $contexto = $this->obtenerContextoParaActividades($idCliente);
        $actividades = $this->generarSugerenciasIA($idCliente, $contexto, $instrucciones, $categoriasObjetivo);

        // Deteccion de duplicados contra PTA existente
        $anio = date('Y');
        $ptaExistentes = $this->db->table('tbl_pta_cliente')
            ->select('actividad_plandetrabajo')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->get()->getResultArray();

        $nombresExistentes = array_map(
            fn($a) => mb_strtolower($a['actividad_plandetrabajo']),
            $ptaExistentes
        );

        foreach ($actividades as &$act) {
            $act['ya_existe'] = false;
            $act['seleccionado'] = true;
            $nombreLower = mb_strtolower($act['actividad']);

            foreach ($nombresExistentes as $existente) {
                $similaridad = 0;
                similar_text($nombreLower, $existente, $similaridad);
                if ($similaridad > 70) {
                    $act['ya_existe'] = true;
                    $act['seleccionado'] = false;
                    break;
                }
            }
        }
        unset($act);

        // Calcular indicadores cubiertos
        $catsCubiertas = array_unique(array_column($actividades, 'categoria_origen'));
        $indicadoresCubiertos = 0;
        foreach ($catsCubiertas as $cat) {
            $indicadoresCubiertos += count($contexto['indicadores_por_categoria'][$cat] ?? []);
        }

        return [
            'actividades' => $actividades,
            'total' => count($actividades),
            'indicadores_cubiertos' => $indicadoresCubiertos,
            'indicadores_huerfanos' => $contexto['indicadores_huerfanos'],
            'total_indicadores' => $contexto['total_indicadores'],
            'total_pta' => $contexto['total_pta'],
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
    // 5. GUARDAR ACTIVIDADES EN PTA
    // ═══════════════════════════════════════════════════════════════

    /**
     * Guarda las actividades seleccionadas en tbl_pta_cliente.
     * Si la actividad es de categoria 'capacitacion', tambien inserta en tbl_cronog_capacitacion.
     */
    public function guardarActividadesSeleccionadas(int $idCliente, int $anio, array $actividades): array
    {
        $creadas = 0;
        $existentes = 0;
        $cronogramaCreadas = 0;
        $errores = [];

        foreach ($actividades as $act) {
            $nombreActividad = $act['actividad'] ?? '';
            if (empty($nombreActividad)) continue;

            // Verificar duplicado con LIKE primeros 30 chars
            $existe = $this->db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('YEAR(fecha_propuesta)', $anio)
                ->like('actividad_plandetrabajo', substr($nombreActividad, 0, 30), 'both')
                ->countAllResults();

            if ($existe > 0) {
                $existentes++;
                continue;
            }

            try {
                $datoPTA = $this->formatearActividadPTA($act, $idCliente, $anio);
                $this->db->table('tbl_pta_cliente')->insert($datoPTA);
                $creadas++;

                // Si es capacitacion (por categoria O por nombre), tambien insertar en cronograma
                if ($this->esCapacitacion($act)) {
                    $this->insertarEnCronogramaCapacitacion($idCliente, $anio, $act);
                    $cronogramaCreadas++;
                }
            } catch (\Exception $e) {
                $errores[] = "Error en '{$nombreActividad}': " . $e->getMessage();
            }
        }

        return [
            'creadas' => $creadas,
            'existentes' => $existentes,
            'cronograma_creadas' => $cronogramaCreadas,
            'errores' => $errores,
            'total' => count($actividades)
        ];
    }

    /**
     * Detecta si una actividad es una capacitacion, sin importar su categoria_origen.
     * Una actividad como "Capacitación en ergonomía" con categoria evaluaciones_medicas
     * sigue siendo una capacitacion que debe ir al cronograma.
     */
    protected function esCapacitacion(array $actividad): bool
    {
        // 1. Por categoria directa
        if (($actividad['categoria_origen'] ?? '') === 'capacitacion') {
            return true;
        }

        // 2. Por nombre: detectar keywords de capacitacion en el nombre de la actividad
        $nombre = $this->quitarTildes(mb_strtolower($actividad['actividad'] ?? ''));
        $keywords = ['capacitacion', 'formacion', 'entrenamiento', 'taller', 'sensibilizacion', 'charla', 'induccion', 'reinduccion'];

        foreach ($keywords as $kw) {
            if (mb_strpos($nombre, $kw) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Inserta una actividad de capacitacion en tbl_cronog_capacitacion.
     * Busca o crea el registro en el catalogo capacitaciones_sst (patron de CapacitacionSSTService).
     */
    protected function insertarEnCronogramaCapacitacion(int $idCliente, int $anio, array $actividad): void
    {
        $nombreCap = $actividad['actividad'] ?? '';
        $descripcion = $actividad['descripcion'] ?? '';

        // Buscar en catalogo capacitaciones_sst por nombre similar
        $catalogoExistente = $this->db->table('capacitaciones_sst')
            ->like('capacitacion', substr($nombreCap, 0, 30), 'both')
            ->get()->getRowArray();

        if ($catalogoExistente) {
            $idCapacitacion = $catalogoExistente['id_capacitacion'];
        } else {
            // Crear en catalogo
            $this->db->table('capacitaciones_sst')->insert([
                'capacitacion' => $nombreCap,
                'objetivo_capacitacion' => $descripcion,
                'observaciones' => 'Generada desde indicadores'
            ]);
            $idCapacitacion = $this->db->insertID();
        }

        // Verificar que no exista ya en cronograma
        $yaExiste = $this->db->table('tbl_cronog_capacitacion')
            ->where('id_cliente', $idCliente)
            ->where('id_capacitacion', $idCapacitacion)
            ->where('YEAR(fecha_programada)', $anio)
            ->countAllResults();

        if ($yaExiste > 0) return;

        $mesInicio = max(1, min(12, (int)($actividad['mes_inicio'] ?? 1)));
        $fechaProgramada = "{$anio}-" . str_pad($mesInicio, 2, '0', STR_PAD_LEFT) . "-15";

        $this->db->table('tbl_cronog_capacitacion')->insert([
            'id_capacitacion' => $idCapacitacion,
            'id_cliente' => $idCliente,
            'fecha_programada' => $fechaProgramada,
            'fecha_de_realizacion' => null,
            'estado' => 'PROGRAMADA',
            'perfil_de_asistentes' => 'TODOS',
            'nombre_del_capacitador' => 'CYCLOID TALENT',
            'horas_de_duracion_de_la_capacitacion' => 1,
            'indicador_de_realizacion_de_la_capacitacion' => 'SIN CALIFICAR',
            'numero_de_asistentes_a_capacitacion' => 0,
            'numero_total_de_personas_programadas' => 0,
            'porcentaje_cobertura' => '0%',
            'numero_de_personas_evaluadas' => 0,
            'promedio_de_calificaciones' => 0,
            'observaciones' => 'Generada desde indicadores'
        ]);
    }

    /**
     * Formatea una actividad para insertar en tbl_pta_cliente
     */
    protected function formatearActividadPTA(array $actividad, int $idCliente, int $anio): array
    {
        $categoriaOrigen = $actividad['categoria_origen'] ?? 'otro';
        $tipoServicio = $this->resolverTipoServicio($categoriaOrigen, $idCliente);
        $mesInicio = max(1, min(12, (int)($actividad['mes_inicio'] ?? 1)));
        $fechaPropuesta = "{$anio}-" . str_pad($mesInicio, 2, '0', STR_PAD_LEFT) . "-15";
        $semana = (int)date('W', strtotime($fechaPropuesta));

        // Combinar actividad + descripcion para el campo actividad_plandetrabajo
        $textoActividad = $actividad['actividad'];
        if (!empty($actividad['descripcion'])) {
            $textoActividad .= '. ' . $actividad['descripcion'];
        }

        return [
            'id_cliente' => $idCliente,
            'tipo_servicio' => $tipoServicio,
            'phva_plandetrabajo' => strtoupper($actividad['phva'] ?? 'HACER'),
            'numeral_plandetrabajo' => $actividad['numeral'] ?? '',
            'actividad_plandetrabajo' => $textoActividad,
            'responsable_sugerido_plandetrabajo' => $actividad['responsable'] ?? 'Responsable SST',
            'fecha_propuesta' => $fechaPropuesta,
            'estado_actividad' => 'ABIERTA',
            'porcentaje_avance' => 0,
            'semana' => $semana,
            'observaciones' => "Generada desde indicadores - Categoria: {$categoriaOrigen}"
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // 6. REGENERAR ACTIVIDAD INDIVIDUAL
    // ═══════════════════════════════════════════════════════════════

    /**
     * Regenera una actividad individual con IA
     */
    public function regenerarActividad(array $actividadActual, string $instrucciones, ?array $contexto = null): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'API Key no configurada'];
        }

        $actividadEconomica = $contexto['actividad_economica'] ?? 'No especificada';
        $nivelRiesgo = $contexto['nivel_riesgo'] ?? 'No especificado';

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia.
Tu tarea es mejorar una actividad del Plan de Trabajo Anual segun las instrucciones del consultor.

REGLAS:
1. Manten la estructura con 9 campos: actividad, descripcion, responsable, phva, numeral, periodicidad, meta, mes_inicio, categoria_origen
2. PHVA en MAYUSCULAS: PLANEAR, HACER, VERIFICAR, ACTUAR
3. La actividad debe ser concreta y ejecutable
4. Responde SOLO en formato JSON valido, sin markdown
5. Adapta al contexto de la empresa";

        $userPrompt = "CONTEXTO DE LA EMPRESA:
- Actividad economica: {$actividadEconomica}
- Nivel de riesgo ARL: {$nivelRiesgo}

ACTIVIDAD ACTUAL:
" . json_encode($actividadActual, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "

INSTRUCCIONES DEL CONSULTOR:
\"{$instrucciones}\"

Mejora la actividad segun las instrucciones. Responde SOLO con el JSON de la actividad mejorada.";

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
            log_message('error', 'ActividadesDesdeIndicadores regenerar: ' . ($error ?: 'HTTP ' . $httpCode));
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
            'data' => array_merge($actividadActual, $respuesta)
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // CONSTANTE: ACTIVIDADES BASE POR CATEGORIA (Fallback sin API)
    // ═══════════════════════════════════════════════════════════════

    public const ACTIVIDADES_BASE_POR_CATEGORIA = [
        // --- Emergencias ---
        [
            'actividad' => 'Realizar simulacro de evacuacion',
            'descripcion' => 'Ejecutar simulacro de evacuacion con participacion de toda la organizacion y evaluar tiempos de respuesta',
            'responsable' => 'Brigada de Emergencias',
            'phva' => 'HACER',
            'numeral' => '4.1.4',
            'periodicidad' => 'semestral',
            'meta' => '2 simulacros al ano',
            'mes_inicio' => 4,
            'categoria_origen' => 'emergencias'
        ],
        [
            'actividad' => 'Revision y actualizacion del Plan de Emergencias',
            'descripcion' => 'Revisar plan de emergencias, actualizar rutas de evacuacion y directorio de emergencias',
            'responsable' => 'Responsable SST',
            'phva' => 'PLANEAR',
            'numeral' => '4.1.4',
            'periodicidad' => 'anual',
            'meta' => 'Plan actualizado',
            'mes_inicio' => 2,
            'categoria_origen' => 'emergencias'
        ],
        // --- Capacitacion ---
        [
            'actividad' => 'Ejecutar capacitacion en uso de extintores',
            'descripcion' => 'Capacitacion practica en manejo de extintores y equipos de primera respuesta',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '1.2.1',
            'periodicidad' => 'anual',
            'meta' => '100% trabajadores capacitados',
            'mes_inicio' => 5,
            'categoria_origen' => 'capacitacion'
        ],
        // --- Inspecciones ---
        [
            'actividad' => 'Inspeccion de areas de trabajo y equipos',
            'descripcion' => 'Realizar inspeccion planeada de seguridad en todas las areas de trabajo',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '4.1.1',
            'periodicidad' => 'mensual',
            'meta' => '12 inspecciones al ano',
            'mes_inicio' => 1,
            'categoria_origen' => 'inspecciones'
        ],
        // --- Vigilancia ---
        [
            'actividad' => 'Seguimiento a recomendaciones medicas ocupacionales',
            'descripcion' => 'Verificar cumplimiento de recomendaciones de los examenes medicos ocupacionales',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '3.1.5',
            'periodicidad' => 'trimestral',
            'meta' => '100% recomendaciones gestionadas',
            'mes_inicio' => 3,
            'categoria_origen' => 'vigilancia'
        ],
        // --- Riesgos ---
        [
            'actividad' => 'Actualizacion de la Matriz de Peligros y Riesgos',
            'descripcion' => 'Actualizar matriz IPEVR incluyendo nuevos peligros identificados y valoracion de riesgos',
            'responsable' => 'Responsable SST',
            'phva' => 'PLANEAR',
            'numeral' => '4.1.2',
            'periodicidad' => 'anual',
            'meta' => 'Matriz actualizada',
            'mes_inicio' => 2,
            'categoria_origen' => 'riesgos'
        ],
        // --- Accidentalidad ---
        [
            'actividad' => 'Analisis de tendencia de accidentalidad',
            'descripcion' => 'Analizar indicadores de accidentalidad del periodo y definir acciones preventivas',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '4.2.1',
            'periodicidad' => 'trimestral',
            'meta' => 'Reduccion progresiva de AT',
            'mes_inicio' => 3,
            'categoria_origen' => 'accidentalidad'
        ],
        // --- Ausentismo ---
        [
            'actividad' => 'Seguimiento a indicadores de ausentismo laboral',
            'descripcion' => 'Consolidar y analizar datos de ausentismo, identificar causas principales',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '3.1.7',
            'periodicidad' => 'mensual',
            'meta' => 'Indice ausentismo <3%',
            'mes_inicio' => 1,
            'categoria_origen' => 'ausentismo'
        ],
        // --- PyP Salud ---
        [
            'actividad' => 'Jornada de promocion de estilos de vida saludable',
            'descripcion' => 'Realizar jornada de promocion de habitos saludables: nutricion, actividad fisica, manejo del estres',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '3.1.2',
            'periodicidad' => 'trimestral',
            'meta' => '80% participacion',
            'mes_inicio' => 3,
            'categoria_origen' => 'pyp_salud'
        ],
        // --- Objetivos SG-SST ---
        [
            'actividad' => 'Revision de objetivos y metas del SG-SST',
            'descripcion' => 'Evaluar cumplimiento de objetivos SST del periodo y ajustar metas segun resultados',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '2.2.1',
            'periodicidad' => 'semestral',
            'meta' => '100% objetivos evaluados',
            'mes_inicio' => 6,
            'categoria_origen' => 'objetivos_sgsst'
        ],
        // --- PTA ---
        [
            'actividad' => 'Seguimiento al Plan de Trabajo Anual',
            'descripcion' => 'Verificar avance de actividades programadas en el PTA y reprogramar las atrasadas',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '2.4.1',
            'periodicidad' => 'mensual',
            'meta' => '100% actividades en seguimiento',
            'mes_inicio' => 1,
            'categoria_origen' => 'pta'
        ],
        // --- Induccion ---
        [
            'actividad' => 'Realizar induccion SST a personal nuevo',
            'descripcion' => 'Ejecutar programa de induccion en SST para todos los trabajadores nuevos incluyendo peligros y controles',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '1.1.6',
            'periodicidad' => 'mensual',
            'meta' => '100% personal nuevo con induccion',
            'mes_inicio' => 1,
            'categoria_origen' => 'induccion'
        ],
    ];
}
