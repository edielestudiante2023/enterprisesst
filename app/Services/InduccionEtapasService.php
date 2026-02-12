<?php

namespace App\Services;

use App\Models\InduccionEtapasModel;
use App\Models\ClienteContextoSstModel;
use App\Models\PtaclienteModel;
use App\Models\IndicadorSSTModel;

/**
 * Servicio para generar las etapas del programa de inducción y reinducción
 *
 * Genera las 5 etapas con temas personalizados según:
 * - Peligros identificados del cliente
 * - Órganos de participación (COPASST, Vigía, Brigada)
 * - Nivel de estándares aplicables
 * - Actividad económica y sector
 *
 * Estándar: 1.2.2 Resolución 0312/2019
 */
class InduccionEtapasService
{
    protected InduccionEtapasModel $etapasModel;
    protected ClienteContextoSstModel $contextoModel;
    protected PtaclienteModel $ptaModel;
    protected IndicadorSSTModel $indicadorModel;

    public function __construct()
    {
        $this->etapasModel = new InduccionEtapasModel();
        $this->contextoModel = new ClienteContextoSstModel();
        $this->ptaModel = new PtaclienteModel();
        $this->indicadorModel = new IndicadorSSTModel();
    }

    /**
     * Genera las etapas de inducción para un cliente
     *
     * @param int $idCliente
     * @param int|null $anio
     * @param array|null $etapasSeleccionadas Array con keys de etapas a generar (1-5), null = todas
     * @return array Resultado con etapas generadas
     */
    public function generarEtapas(int $idCliente, ?int $anio = null, ?array $etapasSeleccionadas = null): array
    {
        $anio = $anio ?? (int)date('Y');

        // Obtener contexto del cliente
        $contexto = $this->contextoModel->getByCliente($idCliente);
        if (!$contexto) {
            return [
                'success' => false,
                'error' => 'No se encontró el contexto del cliente. Configure primero el contexto SST.'
            ];
        }

        // Obtener datos del cliente
        $clienteModel = new \App\Models\ClientModel();
        $cliente = $clienteModel->find($idCliente);
        if (!$cliente) {
            return [
                'success' => false,
                'error' => 'Cliente no encontrado'
            ];
        }

        // Determinar qué etapas generar
        $etapasAGenerar = [];
        if ($etapasSeleccionadas === null) {
            // Si no se especifica, generar todas
            $etapasAGenerar = array_keys(InduccionEtapasModel::ETAPAS);
        } else {
            // Filtrar solo las etapas seleccionadas (que tienen 'incluir' = 1)
            foreach ($etapasSeleccionadas as $numero => $config) {
                if (!empty($config['incluir'])) {
                    $etapasAGenerar[] = (int)$numero;
                }
            }
        }

        if (empty($etapasAGenerar)) {
            return [
                'success' => false,
                'error' => 'Debe seleccionar al menos una etapa'
            ];
        }

        // Eliminar etapas anteriores del mismo año (para regenerar)
        $this->etapasModel->eliminarPorClienteAnio($idCliente, $anio);

        // Decodificar peligros identificados
        $peligros = $this->decodificarPeligros($contexto['peligros_identificados'] ?? '[]');

        // Generar cada etapa seleccionada
        $etapasGeneradas = [];
        $ordenNuevo = 1;

        foreach (InduccionEtapasModel::ETAPAS as $numero => $nombre) {
            // Solo generar si está en la lista de seleccionadas
            if (!in_array($numero, $etapasAGenerar)) {
                continue;
            }

            $temas = $this->generarTemasEtapa($numero, $contexto, $peligros, $cliente);

            $etapaData = [
                'id_cliente' => $idCliente,
                'numero_etapa' => $ordenNuevo, // Usar orden secuencial
                'nombre_etapa' => $nombre,
                'descripcion_etapa' => $this->getDescripcionEtapa($numero),
                'temas' => json_encode($temas, JSON_UNESCAPED_UNICODE),
                'duracion_estimada_minutos' => $this->calcularDuracion($temas),
                'responsable_sugerido' => $this->getResponsableSugerido($numero, $contexto),
                'recursos_requeridos' => $this->getRecursos($numero),
                'es_personalizado' => $this->tieneTemasPersonalizados($temas) ? 1 : 0,
                'anio' => $anio,
                'estado' => 'borrador'
            ];

            $idEtapa = $this->etapasModel->insert($etapaData);
            if ($idEtapa) {
                $etapaData['id_etapa'] = $idEtapa;
                $etapaData['temas_decodificados'] = $temas;
                $etapaData['etapa_original'] = $numero; // Guardar referencia a etapa original
                $etapasGeneradas[] = $etapaData;
                $ordenNuevo++;
            }
        }

        return [
            'success' => true,
            'etapas' => $etapasGeneradas,
            'total_etapas' => count($etapasGeneradas),
            'total_temas' => array_sum(array_map(fn($e) => count($e['temas_decodificados']), $etapasGeneradas)),
            'anio' => $anio,
            'mensaje' => 'Se generaron ' . count($etapasGeneradas) . ' etapas de inducción'
        ];
    }

    /**
     * Genera los temas para una etapa específica
     */
    protected function generarTemasEtapa(int $numeroEtapa, array $contexto, array $peligros, array $cliente): array
    {
        // Obtener temas base
        $temasBase = InduccionEtapasModel::TEMAS_BASE[$numeroEtapa] ?? [];

        // Agregar flag de origen
        foreach ($temasBase as &$tema) {
            $tema['es_personalizado'] = false;
            $tema['origen'] = 'base';
        }

        // Agregar temas personalizados según la etapa
        $temasPersonalizados = [];

        switch ($numeroEtapa) {
            case 2: // Etapa SST - aquí se agregan los temas según peligros y órganos
                $temasPersonalizados = $this->generarTemasSST($contexto, $peligros);
                break;

            case 4: // Etapa Recorrido - personalizar según sedes
                $temasPersonalizados = $this->generarTemasRecorrido($contexto);
                break;

            case 5: // Etapa Entrenamiento - personalizar según peligros del cargo
                $temasPersonalizados = $this->generarTemasEntrenamiento($peligros);
                break;
        }

        return array_merge($temasBase, $temasPersonalizados);
    }

    /**
     * Genera temas de SST según peligros y órganos de participación
     */
    protected function generarTemasSST(array $contexto, array $peligros): array
    {
        $temas = [];

        // Agregar tema según COPASST o Vigía
        if (!empty($contexto['tiene_copasst']) && $contexto['tiene_copasst']) {
            $temas[] = [
                'nombre' => InduccionEtapasModel::TEMAS_CONDICIONALES['tiene_copasst']['nombre'],
                'descripcion' => InduccionEtapasModel::TEMAS_CONDICIONALES['tiene_copasst']['descripcion'],
                'es_personalizado' => true,
                'origen' => 'organo_participacion'
            ];
        } elseif (!empty($contexto['tiene_vigia_sst']) && $contexto['tiene_vigia_sst']) {
            $temas[] = [
                'nombre' => InduccionEtapasModel::TEMAS_CONDICIONALES['tiene_vigia_sst']['nombre'],
                'descripcion' => InduccionEtapasModel::TEMAS_CONDICIONALES['tiene_vigia_sst']['descripcion'],
                'es_personalizado' => true,
                'origen' => 'organo_participacion'
            ];
        }

        // Agregar tema de Comité de Convivencia
        if (!empty($contexto['tiene_comite_convivencia']) && $contexto['tiene_comite_convivencia']) {
            $temas[] = [
                'nombre' => InduccionEtapasModel::TEMAS_CONDICIONALES['tiene_comite_convivencia']['nombre'],
                'descripcion' => InduccionEtapasModel::TEMAS_CONDICIONALES['tiene_comite_convivencia']['descripcion'],
                'es_personalizado' => true,
                'origen' => 'organo_participacion'
            ];
        }

        // Agregar tema de Brigada de Emergencias
        if (!empty($contexto['tiene_brigada_emergencias']) && $contexto['tiene_brigada_emergencias']) {
            $temas[] = [
                'nombre' => InduccionEtapasModel::TEMAS_CONDICIONALES['tiene_brigada_emergencias']['nombre'],
                'descripcion' => InduccionEtapasModel::TEMAS_CONDICIONALES['tiene_brigada_emergencias']['descripcion'],
                'es_personalizado' => true,
                'origen' => 'organo_participacion'
            ];
        }

        // Agregar temas según peligros identificados
        foreach ($peligros as $peligro) {
            $peligroKey = $this->normalizarPeligro($peligro);
            if (isset(InduccionEtapasModel::TEMAS_POR_PELIGRO[$peligroKey])) {
                $temaPeligro = InduccionEtapasModel::TEMAS_POR_PELIGRO[$peligroKey];
                // Evitar duplicados
                $yaExiste = false;
                foreach ($temas as $t) {
                    if ($t['nombre'] === $temaPeligro['nombre']) {
                        $yaExiste = true;
                        break;
                    }
                }
                if (!$yaExiste) {
                    $temas[] = [
                        'nombre' => $temaPeligro['nombre'],
                        'descripcion' => $temaPeligro['descripcion'],
                        'es_personalizado' => true,
                        'origen' => 'peligro_identificado',
                        'peligro_origen' => $peligro
                    ];
                }
            }
        }

        // Agregar tema de verificación de recomendaciones médicas (siempre al final de SST)
        $temas[] = [
            'nombre' => 'Verificar recomendaciones en exámenes de ingreso',
            'descripcion' => 'Revisión de recomendaciones médicas ocupacionales del examen de ingreso',
            'es_personalizado' => false,
            'origen' => 'base'
        ];

        return $temas;
    }

    /**
     * Genera temas de recorrido según sedes
     */
    protected function generarTemasRecorrido(array $contexto): array
    {
        $temas = [];

        $numSedes = $contexto['numero_sedes'] ?? 1;
        if ($numSedes > 1) {
            $temas[] = [
                'nombre' => 'Identificación de sede asignada',
                'descripcion' => "La empresa cuenta con {$numSedes} sedes. El recorrido se realiza en la sede donde el trabajador prestará sus servicios.",
                'es_personalizado' => true,
                'origen' => 'estructura_empresa'
            ];
        }

        return $temas;
    }

    /**
     * Genera temas de entrenamiento según peligros
     */
    protected function generarTemasEntrenamiento(array $peligros): array
    {
        $temas = [];

        // Si hay peligros de alto riesgo, agregar temas específicos
        $peligrosAltoRiesgo = ['trabajo en alturas', 'espacios confinados', 'eléctrico', 'químico'];
        $tieneAltoRiesgo = false;

        foreach ($peligros as $peligro) {
            $peligroNorm = $this->normalizarPeligro($peligro);
            if (in_array($peligroNorm, $peligrosAltoRiesgo)) {
                $tieneAltoRiesgo = true;
                break;
            }
        }

        if ($tieneAltoRiesgo) {
            $temas[] = [
                'nombre' => 'Permisos de trabajo y AST',
                'descripcion' => 'Análisis de Seguridad en el Trabajo y permisos para tareas de alto riesgo',
                'es_personalizado' => true,
                'origen' => 'alto_riesgo'
            ];
        }

        return $temas;
    }

    /**
     * Decodifica los peligros del JSON
     */
    protected function decodificarPeligros(string $peligrosJson): array
    {
        if (empty($peligrosJson)) {
            return [];
        }

        $decoded = json_decode($peligrosJson, true);
        if (!is_array($decoded)) {
            return [];
        }

        // Aplanar el array si viene anidado por categorías
        $peligros = [];
        foreach ($decoded as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $p) {
                    if (is_string($p)) {
                        $peligros[] = $p;
                    }
                }
            } elseif (is_string($value)) {
                $peligros[] = $value;
            }
        }

        return array_unique($peligros);
    }

    /**
     * Normaliza el nombre del peligro para buscar en el mapeo
     */
    protected function normalizarPeligro(string $peligro): string
    {
        $peligro = strtolower(trim($peligro));

        // Mapeo de variantes
        $mapeo = [
            'alturas' => 'trabajo en alturas',
            'altura' => 'trabajo en alturas',
            'trabajo en altura' => 'trabajo en alturas',
            'espacio confinado' => 'espacios confinados',
            'electrico' => 'eléctrico',
            'riesgo electrico' => 'eléctrico',
            'quimico' => 'químico',
            'riesgo quimico' => 'químico',
            'sustancias quimicas' => 'químico',
            'mecanico' => 'mecánico',
            'riesgo mecanico' => 'mecánico',
            'maquinas' => 'mecánico',
            'ergonomico' => 'biomecánico',
            'ergonomia' => 'biomecánico',
            'postura' => 'biomecánico',
            'manipulacion de cargas' => 'biomecánico',
            'biologico' => 'biológico',
            'riesgo biologico' => 'biológico',
            'ruido ocupacional' => 'ruido',
            'radiacion' => 'radiaciones',
            'temperaturas' => 'temperaturas extremas',
            'calor' => 'temperaturas extremas',
            'frio' => 'temperaturas extremas',
        ];

        return $mapeo[$peligro] ?? $peligro;
    }

    /**
     * Obtiene la descripción de una etapa
     */
    protected function getDescripcionEtapa(int $numero): string
    {
        $descripciones = [
            1 => 'Presentación general de la empresa, su historia, misión, visión, valores y estructura organizacional.',
            2 => 'Capacitación en el Sistema de Gestión de Seguridad y Salud en el Trabajo, políticas, peligros y controles.',
            3 => 'Información sobre el reglamento interno, condiciones laborales, horarios y prestaciones.',
            4 => 'Recorrido por las instalaciones, identificación de áreas, rutas de evacuación y puntos de encuentro.',
            5 => 'Entrenamiento específico en el puesto de trabajo, funciones, procedimientos y uso de EPP.'
        ];

        return $descripciones[$numero] ?? '';
    }

    /**
     * Obtiene el responsable sugerido para una etapa
     */
    protected function getResponsableSugerido(int $numero, array $contexto): string
    {
        $responsables = [
            1 => 'Gestión Humana / Recursos Humanos',
            2 => $contexto['responsable_sgsst_nombre'] ?? 'Responsable del SG-SST',
            3 => 'Gestión Humana / Recursos Humanos',
            4 => 'Jefe Inmediato / Responsable del área',
            5 => 'Jefe Inmediato / Responsable del área'
        ];

        return $responsables[$numero] ?? 'Por definir';
    }

    /**
     * Obtiene los recursos requeridos para una etapa
     */
    protected function getRecursos(int $numero): string
    {
        $recursos = [
            1 => 'Presentación digital, video institucional, manual de bienvenida',
            2 => 'Documentos del SG-SST, matriz de peligros, plan de emergencias, EPP de muestra',
            3 => 'Reglamento interno de trabajo, contrato laboral, manual de funciones',
            4 => 'Plano de instalaciones, señalización de rutas, puntos de encuentro',
            5 => 'Manual del cargo, procedimientos operativos, herramientas del puesto, EPP'
        ];

        return $recursos[$numero] ?? '';
    }

    /**
     * Calcula la duración estimada según cantidad de temas
     */
    protected function calcularDuracion(array $temas): int
    {
        // Base de 15 minutos por tema
        $duracion = count($temas) * 15;

        // Mínimo 30 minutos, máximo 120 minutos por etapa
        return max(30, min(120, $duracion));
    }

    /**
     * Verifica si hay temas personalizados
     */
    protected function tieneTemasPersonalizados(array $temas): bool
    {
        foreach ($temas as $tema) {
            if (!empty($tema['es_personalizado']) && $tema['es_personalizado']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Prepara las actividades propuestas para el PTA (sin insertar)
     * Usa IA para consolidar todos los temas en máximo 4 actividades prácticas
     */
    public function prepararActividadesPTA(int $idCliente, ?int $anio = null): array
    {
        $anio = $anio ?? (int)date('Y');

        // Obtener etapas aprobadas
        $etapas = $this->etapasModel->getEtapasAprobadas($idCliente, $anio);

        if (empty($etapas)) {
            return [
                'success' => false,
                'error' => 'No hay etapas de inducción aprobadas. Primero genere y apruebe las etapas.',
                'etapas' => [],
                'actividades' => []
            ];
        }

        // Recopilar todos los temas de todas las etapas
        $todosTemas = [];
        $etapasInfo = [];

        foreach ($etapas as $etapa) {
            $temas = $this->etapasModel->getTemasDecodificados($etapa);
            $etapasInfo[$etapa['numero_etapa']] = [
                'nombre' => $etapa['nombre_etapa'],
                'responsable' => $etapa['responsable_sugerido']
            ];

            foreach ($temas as $tema) {
                $todosTemas[] = [
                    'etapa' => $etapa['numero_etapa'],
                    'nombre_etapa' => $etapa['nombre_etapa'],
                    'tema' => $tema['nombre'],
                    'descripcion' => $tema['descripcion'] ?? ''
                ];
            }
        }

        // Usar IA para consolidar en máximo 4 actividades
        $actividades = $this->consolidarTemasConIA($todosTemas, $etapasInfo, $anio);

        // Si la IA falla, usar método de fallback
        if (empty($actividades)) {
            $actividades = $this->consolidarTemasFallback($todosTemas, $etapasInfo, $anio);
        }

        return [
            'success' => true,
            'etapas' => $etapas,
            'actividades' => $actividades,
            'total' => count($actividades),
            'total_temas_originales' => count($todosTemas),
            'anio' => $anio,
            'consolidado_con_ia' => !empty($actividades) && isset($actividades[0]['generado_por_ia'])
        ];
    }

    /**
     * Usa OpenAI para consolidar muchos temas en máximo 4 actividades prácticas
     */
    protected function consolidarTemasConIA(array $temas, array $etapasInfo, int $anio): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            log_message('warning', 'No hay API key de OpenAI configurada para consolidar temas de inducción');
            return [];
        }

        // Preparar lista de temas para la IA
        $temasTexto = "";
        foreach ($temas as $idx => $tema) {
            $temasTexto .= ($idx + 1) . ". [{$tema['nombre_etapa']}] {$tema['tema']}\n";
        }

        // Obtener responsable más común
        $responsableDefault = 'Responsable del SG-SST';
        foreach ($etapasInfo as $info) {
            if (!empty($info['responsable'])) {
                $responsableDefault = $info['responsable'];
                break;
            }
        }

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia.
Tu tarea es consolidar una lista de temas de un programa de inducción en MÁXIMO 4 ACTIVIDADES prácticas para el Plan de Trabajo Anual.

REGLAS IMPORTANTES:
1. Debes generar MÁXIMO 4 actividades (pueden ser 3 o 4, nunca más)
2. Cada actividad debe CONSOLIDAR múltiples temas relacionados
3. Las actividades deben ser prácticas y medibles
4. Distribuir las fechas a lo largo del año {$anio}
5. Responde SOLO en formato JSON válido

FORMATO DE RESPUESTA (JSON):
{
  \"actividades\": [
    {
      \"actividad\": \"Nombre de la actividad consolidada\",
      \"descripcion\": \"Breve descripción que mencione los temas que incluye\",
      \"responsable\": \"Responsable sugerido\",
      \"mes\": 2,
      \"temas_consolidados\": [1, 2, 3]
    }
  ],
  \"explicacion\": \"Breve explicación de cómo se consolidaron los temas\"
}

Ejemplo de consolidación:
- En lugar de crear 10 actividades separadas para cada tema de SST, crea UNA actividad \"Jornada de Inducción en SG-SST\" que incluya: política SST, peligros y riesgos, controles, EPP, etc.";

        $userPrompt = "AÑO DEL PROGRAMA: {$anio}
TOTAL DE TEMAS: " . count($temas) . "

LISTADO DE TEMAS A CONSOLIDAR:
{$temasTexto}

RESPONSABLE SUGERIDO: {$responsableDefault}

Consolida estos " . count($temas) . " temas en MÁXIMO 4 ACTIVIDADES prácticas para el Plan de Trabajo Anual.
Las actividades deben cubrir todos los temas de manera consolidada e inteligente.";

        $response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey);

        if (!$response['success']) {
            log_message('error', 'Error al consolidar temas con IA: ' . ($response['error'] ?? 'desconocido'));
            return [];
        }

        return $this->procesarRespuestaConsolidacion($response['contenido'], $temas, $responsableDefault, $anio);
    }

    /**
     * Llama a la API de OpenAI
     */
    protected function llamarOpenAI(string $systemPrompt, string $userPrompt, string $apiKey): array
    {
        $data = [
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.3,
            'max_tokens' => 1500
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

        if ($error) {
            return ['success' => false, 'error' => "Error de conexión: {$error}"];
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
     * Procesa la respuesta JSON de la IA para consolidación
     */
    protected function procesarRespuestaConsolidacion(string $contenidoIA, array $temasOriginales, string $responsableDefault, int $anio): array
    {
        // Limpiar el JSON (puede venir con ```json ... ```)
        $contenidoIA = preg_replace('/```json\s*/', '', $contenidoIA);
        $contenidoIA = preg_replace('/```\s*/', '', $contenidoIA);

        $respuesta = json_decode($contenidoIA, true);
        if (!$respuesta || empty($respuesta['actividades'])) {
            log_message('warning', 'No se pudo parsear respuesta IA consolidación: ' . $contenidoIA);
            return [];
        }

        $actividades = [];
        $numeroEtapa = 1;

        foreach ($respuesta['actividades'] as $actIA) {
            $mes = (int)($actIA['mes'] ?? ($numeroEtapa * 3)); // Distribuir trimestralmente si no hay mes
            $mes = max(1, min(12, $mes)); // Asegurar mes válido

            $fecha = date('Y-m-d', strtotime("{$anio}-{$mes}-15"));

            // Construir descripción con temas consolidados
            $descripcion = $actIA['descripcion'] ?? '';
            if (!empty($actIA['temas_consolidados']) && is_array($actIA['temas_consolidados'])) {
                $temasIncluidos = [];
                foreach ($actIA['temas_consolidados'] as $idx) {
                    if (isset($temasOriginales[$idx - 1])) {
                        $temasIncluidos[] = $temasOriginales[$idx - 1]['tema'];
                    }
                }
                if (!empty($temasIncluidos) && empty($descripcion)) {
                    $descripcion = 'Incluye: ' . implode(', ', array_slice($temasIncluidos, 0, 5));
                    if (count($temasIncluidos) > 5) {
                        $descripcion .= ' y ' . (count($temasIncluidos) - 5) . ' temas más';
                    }
                }
            }

            $actividades[] = [
                'numero_etapa' => $numeroEtapa,
                'actividad' => $actIA['actividad'] ?? "Actividad de Inducción {$numeroEtapa}",
                'descripcion' => $descripcion,
                'responsable' => $actIA['responsable'] ?? $responsableDefault,
                'fecha' => $fecha,
                'phva' => 'HACER',
                'generado_por_ia' => true,
                'temas_consolidados' => $actIA['temas_consolidados'] ?? []
            ];

            $numeroEtapa++;
        }

        return $actividades;
    }

    /**
     * Método de fallback: consolida temas sin IA en 4 actividades
     */
    protected function consolidarTemasFallback(array $temas, array $etapasInfo, int $anio): array
    {
        // Agrupar temas por tipo/etapa
        $grupos = [
            'empresa' => [],
            'sst' => [],
            'laboral' => [],
            'practico' => []
        ];

        foreach ($temas as $tema) {
            $etapa = $tema['etapa'];
            if ($etapa == 1) {
                $grupos['empresa'][] = $tema;
            } elseif ($etapa == 2) {
                $grupos['sst'][] = $tema;
            } elseif ($etapa == 3) {
                $grupos['laboral'][] = $tema;
            } else {
                $grupos['practico'][] = $tema;
            }
        }

        // Obtener responsable
        $responsable = 'Responsable del SG-SST';
        foreach ($etapasInfo as $info) {
            if (!empty($info['responsable'])) {
                $responsable = $info['responsable'];
                break;
            }
        }

        $actividades = [];
        $actividadesConfig = [
            'empresa' => [
                'nombre' => 'Jornada de Inducción Corporativa',
                'mes' => 2
            ],
            'sst' => [
                'nombre' => 'Jornada de Inducción en Seguridad y Salud en el Trabajo',
                'mes' => 3
            ],
            'laboral' => [
                'nombre' => 'Inducción en Aspectos Laborales y Normativos',
                'mes' => 4
            ],
            'practico' => [
                'nombre' => 'Inducción Práctica y Entrenamiento en el Puesto',
                'mes' => 5
            ]
        ];

        $numero = 1;
        foreach ($grupos as $tipo => $temasGrupo) {
            if (empty($temasGrupo)) continue;

            $config = $actividadesConfig[$tipo];
            $temasNombres = array_map(fn($t) => $t['tema'], $temasGrupo);
            $descripcion = 'Incluye: ' . implode(', ', array_slice($temasNombres, 0, 4));
            if (count($temasNombres) > 4) {
                $descripcion .= ' y ' . (count($temasNombres) - 4) . ' temas más';
            }

            $actividades[] = [
                'numero_etapa' => $numero,
                'actividad' => $config['nombre'],
                'descripcion' => $descripcion,
                'responsable' => $responsable,
                'fecha' => date('Y-m-d', strtotime("{$anio}-{$config['mes']}-15")),
                'phva' => 'HACER',
                'generado_por_ia' => false,
                'temas_consolidados' => array_keys($temasGrupo)
            ];
            $numero++;
        }

        return $actividades;
    }

    /**
     * Envía las actividades seleccionadas al PTA
     */
    public function enviarActividadesPTA(int $idCliente, array $actividades, int $anio): array
    {
        $actividadesCreadas = 0;

        foreach ($actividades as $index => $actividad) {
            // Solo procesar las que tienen el checkbox marcado
            if (empty($actividad['incluir'])) {
                continue;
            }

            $actividadData = [
                'id_cliente' => $idCliente,
                'tipo_servicio' => 'Programa Induccion y Reinduccion',
                'phva_plandetrabajo' => $actividad['phva'] ?? 'HACER',
                'numeral_plandetrabajo' => '1.2.2',
                'actividad_plandetrabajo' => $actividad['actividad'],
                'responsable_sugerido_plandetrabajo' => $actividad['responsable'],
                'fecha_propuesta' => $actividad['fecha'],
                'estado_actividad' => 'ABIERTA',
                'porcentaje_avance' => 0,
                'observaciones' => $actividad['descripcion'] ?? ''
            ];

            // Verificar si ya existe
            $existe = $this->ptaModel
                ->where('id_cliente', $idCliente)
                ->where('YEAR(fecha_propuesta)', $anio)
                ->where('actividad_plandetrabajo', $actividad['actividad'])
                ->countAllResults();

            if ($existe === 0) {
                $this->ptaModel->insert($actividadData);
                $actividadesCreadas++;
            }
        }

        return [
            'success' => true,
            'actividades_creadas' => $actividadesCreadas,
            'mensaje' => "Se agregaron {$actividadesCreadas} actividades al Plan de Trabajo Anual"
        ];
    }

    /**
     * Prepara los indicadores propuestos usando IA basándose en las actividades del PTA
     * Para mostrar en vista de revisión antes de enviar
     */
    public function prepararIndicadores(int $idCliente): array
    {
        $anio = (int)date('Y');

        // Obtener las actividades de inducción del PTA
        $db = \Config\Database::connect();
        $actividades = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->groupStart()
                ->like('tipo_servicio', 'Induccion', 'both', true, true)
                ->orLike('actividad_plandetrabajo', 'Induccion', 'both', true, true)
                ->orLike('actividad_plandetrabajo', 'Reinduccion', 'both', true, true)
            ->groupEnd()
            ->get()
            ->getResultArray();

        // Generar indicadores con IA si hay actividades
        $indicadores = [];
        $generadoConIA = false;

        if (!empty($actividades)) {
            $indicadores = $this->generarIndicadoresConIA($actividades, $idCliente);
            $generadoConIA = !empty($indicadores);
        }

        // Fallback: usar indicadores base si la IA falla o no hay actividades
        if (empty($indicadores)) {
            $indicadores = $this->getIndicadoresBase();
        }

        // Marcar los que ya existen
        foreach ($indicadores as &$indicador) {
            $existe = $this->indicadorModel
                ->where('id_cliente', $idCliente)
                ->where('nombre_indicador', $indicador['nombre'])
                ->countAllResults();
            $indicador['ya_existe'] = $existe > 0;
        }

        return [
            'success' => true,
            'indicadores' => $indicadores,
            'total' => count($indicadores),
            'actividades_pta' => count($actividades),
            'generado_con_ia' => $generadoConIA
        ];
    }

    /**
     * Genera indicadores usando IA basándose en las actividades del PTA
     */
    protected function generarIndicadoresConIA(array $actividades, int $idCliente): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            log_message('warning', 'No hay API key de OpenAI configurada para generar indicadores de inducción');
            return [];
        }

        // Preparar lista de actividades para la IA
        $actividadesTexto = "";
        foreach ($actividades as $idx => $act) {
            $actividadesTexto .= ($idx + 1) . ". {$act['actividad_plandetrabajo']}";
            if (!empty($act['observaciones'])) {
                $actividadesTexto .= " - {$act['observaciones']}";
            }
            $actividadesTexto .= "\n";
        }

        // Obtener contexto del cliente
        $contexto = $this->contextoModel->getByCliente($idCliente);
        $numTrabajadores = $contexto['numero_trabajadores'] ?? 'No especificado';

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia, especializado en indicadores de gestión según la Resolución 0312 de 2019.

Tu tarea es generar indicadores ESPECÍFICOS y MEDIBLES para un programa de inducción y reinducción, basándote en las actividades reales del Plan de Trabajo Anual.

REGLAS IMPORTANTES:
1. Debes generar entre 3 y 5 indicadores
2. Los indicadores deben ser ESPECÍFICOS para las actividades proporcionadas
3. Cada indicador debe tener una fórmula clara y medible
4. Usar tipos: 'estructura' (recursos), 'proceso' (ejecución), 'resultado' (impacto)
5. Las metas deben ser realistas (normalmente 80-100%)
6. Periodicidad: mensual, trimestral, semestral o anual según corresponda
7. Responde SOLO en formato JSON válido

FORMATO DE RESPUESTA (JSON):
{
  \"indicadores\": [
    {
      \"nombre\": \"Nombre del indicador\",
      \"tipo\": \"proceso\",
      \"formula\": \"(Numerador / Denominador) x 100\",
      \"meta\": 100,
      \"unidad\": \"%\",
      \"periodicidad\": \"mensual\",
      \"justificacion\": \"Breve justificación de por qué este indicador es relevante\"
    }
  ]
}

INDICADORES SUGERIDOS PARA INDUCCIÓN (puedes adaptarlos según las actividades):
- Cobertura de inducción (% trabajadores nuevos con inducción)
- Cumplimiento del programa (actividades ejecutadas vs programadas)
- Oportunidad de inducción (% inducciones antes del inicio de labores)
- Efectividad de la inducción (% aprobación en evaluación post-inducción)
- Reinducción anual (% trabajadores con reinducción al día)";

        $numActividades = count($actividades);
        $userPrompt = "DATOS DEL CLIENTE:
- Número de trabajadores: {$numTrabajadores}

ACTIVIDADES DE INDUCCIÓN EN EL PTA ({$numActividades} actividades):
{$actividadesTexto}

Genera indicadores ESPECÍFICOS para medir el cumplimiento de estas actividades de inducción.
Los indicadores deben permitir evaluar si el programa de inducción se está ejecutando correctamente.";

        $response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey);

        if (!$response['success']) {
            log_message('error', 'Error al generar indicadores con IA: ' . ($response['error'] ?? 'desconocido'));
            return [];
        }

        return $this->procesarRespuestaIndicadores($response['contenido']);
    }

    /**
     * Procesa la respuesta JSON de la IA para indicadores
     */
    protected function procesarRespuestaIndicadores(string $contenidoIA): array
    {
        // Limpiar el JSON (puede venir con ```json ... ```)
        $contenidoIA = preg_replace('/```json\s*/', '', $contenidoIA);
        $contenidoIA = preg_replace('/```\s*/', '', $contenidoIA);

        $respuesta = json_decode($contenidoIA, true);
        if (!$respuesta || empty($respuesta['indicadores'])) {
            log_message('warning', 'No se pudo parsear respuesta IA indicadores: ' . $contenidoIA);
            return [];
        }

        $indicadores = [];
        foreach ($respuesta['indicadores'] as $indIA) {
            $indicadores[] = [
                'nombre' => $indIA['nombre'] ?? 'Indicador sin nombre',
                'tipo' => $indIA['tipo'] ?? 'proceso',
                'categoria' => 'induccion',
                'formula' => $indIA['formula'] ?? '',
                'meta' => (int)($indIA['meta'] ?? 100),
                'unidad' => $indIA['unidad'] ?? '%',
                'periodicidad' => $indIA['periodicidad'] ?? 'mensual',
                'numeral' => '1.2.2',
                'phva' => 'VERIFICAR',
                'justificacion' => $indIA['justificacion'] ?? '',
                'generado_por_ia' => true
            ];
        }

        return $indicadores;
    }

    /**
     * Retorna los indicadores base (fallback si la IA falla)
     */
    protected function getIndicadoresBase(): array
    {
        return [
            [
                'nombre' => 'Cobertura de Inducción',
                'tipo' => 'proceso',
                'categoria' => 'induccion',
                'formula' => '(Trabajadores con inducción completa / Total trabajadores nuevos) x 100',
                'meta' => 100,
                'unidad' => '%',
                'periodicidad' => 'mensual',
                'numeral' => '1.2.2',
                'phva' => 'VERIFICAR',
                'generado_por_ia' => false,
                'definicion' => 'Mide la proporcion de trabajadores nuevos que completaron todas las etapas del programa de induccion antes de iniciar sus funciones.',
                'interpretacion' => 'El 100% indica que todos los trabajadores nuevos recibieron induccion completa. Es obligatorio segun Art. 2.2.4.6.11 D.1072/2015.',
                'origen_datos' => 'Registros de induccion, formato de asistencia firmado, evaluacion post-induccion',
                'cargo_responsable' => 'Responsable del SG-SST',
                'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, COPASST/Vigia'
            ],
            [
                'nombre' => 'Cumplimiento del Programa de Inducción',
                'tipo' => 'proceso',
                'categoria' => 'induccion',
                'formula' => '(Temas de inducción ejecutados / Temas programados) x 100',
                'meta' => 100,
                'unidad' => '%',
                'periodicidad' => 'trimestral',
                'numeral' => '1.2.2',
                'phva' => 'VERIFICAR',
                'generado_por_ia' => false,
                'definicion' => 'Mide el porcentaje de temas del programa de induccion y reinduccion que fueron efectivamente ejecutados respecto al total programado.',
                'interpretacion' => 'El 100% indica ejecucion completa del programa. Valores menores requieren reprogramacion de temas pendientes antes de que el trabajador inicie labores.',
                'origen_datos' => 'Programa de induccion aprobado, registros de ejecucion por etapa, evaluaciones',
                'cargo_responsable' => 'Responsable del SG-SST',
                'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos, COPASST/Vigia'
            ],
            [
                'nombre' => 'Oportunidad de Inducción',
                'tipo' => 'proceso',
                'categoria' => 'induccion',
                'formula' => '(Inducciones realizadas en el primer día / Total inducciones) x 100',
                'meta' => 90,
                'unidad' => '%',
                'periodicidad' => 'mensual',
                'numeral' => '1.2.2',
                'phva' => 'VERIFICAR',
                'generado_por_ia' => false,
                'definicion' => 'Mide el porcentaje de inducciones que se realizaron el primer dia de vinculacion del trabajador, antes de que inicie sus funciones.',
                'interpretacion' => 'Un 90% o mas indica buena oportunidad. La induccion debe realizarse antes del inicio de labores para garantizar conocimiento de peligros y controles.',
                'origen_datos' => 'Registros de induccion (fecha vs fecha de ingreso), nomina (fecha de vinculacion)',
                'cargo_responsable' => 'Responsable del SG-SST',
                'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, Recursos Humanos'
            ]
        ];
    }

    /**
     * Ajusta un indicador específico usando IA basándose en el feedback del usuario
     */
    public function ajustarIndicadorConIA(array $indicador, string $feedback, int $idCliente): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            return [
                'success' => false,
                'error' => 'No hay API key de OpenAI configurada'
            ];
        }

        // Obtener contexto del cliente
        $contexto = $this->contextoModel->getByCliente($idCliente);
        $numTrabajadores = $contexto['numero_trabajadores'] ?? 'No especificado';

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia.
Tu tarea es AJUSTAR un indicador existente basándote en el feedback del usuario.

REGLAS:
1. Mantén el indicador relevante para inducción y reinducción (numeral 1.2.2)
2. La fórmula debe ser clara y medible
3. Las metas deben ser realistas (normalmente 80-100%)
4. Responde SOLO en formato JSON válido
5. Incluye una breve explicación del ajuste realizado

FORMATO DE RESPUESTA (JSON):
{
  \"indicador\": {
    \"nombre\": \"Nombre ajustado\",
    \"formula\": \"Fórmula ajustada\",
    \"meta\": 80,
    \"periodicidad\": \"mensual\"
  },
  \"explicacion\": \"Breve explicación de los cambios realizados\"
}

IMPORTANTE: Solo modifica lo que el usuario solicita. Si solo pide cambiar la meta, no cambies la fórmula ni el nombre.";

        $userPrompt = "INDICADOR ACTUAL:
- Nombre: {$indicador['nombre']}
- Fórmula: {$indicador['formula']}
- Meta: {$indicador['meta']}%
- Periodicidad: {$indicador['periodicidad']}
- Tipo: {$indicador['tipo']}

DATOS DEL CLIENTE:
- Número de trabajadores: {$numTrabajadores}

SOLICITUD DEL USUARIO:
{$feedback}

Ajusta el indicador según la solicitud del usuario.";

        $response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey);

        if (!$response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Error al procesar con IA'
            ];
        }

        // Parsear respuesta
        $contenidoIA = $response['contenido'];
        $contenidoIA = preg_replace('/```json\s*/', '', $contenidoIA);
        $contenidoIA = preg_replace('/```\s*/', '', $contenidoIA);

        $respuesta = json_decode($contenidoIA, true);
        if (!$respuesta || empty($respuesta['indicador'])) {
            return [
                'success' => false,
                'error' => 'No se pudo procesar la respuesta de la IA'
            ];
        }

        return [
            'success' => true,
            'indicador' => $respuesta['indicador'],
            'explicacion' => $respuesta['explicacion'] ?? 'Indicador ajustado correctamente'
        ];
    }

    /**
     * Envía los indicadores seleccionados al módulo de indicadores
     */
    public function enviarIndicadores(int $idCliente, array $indicadores): array
    {
        $indicadoresCreados = 0;

        foreach ($indicadores as $indicador) {
            // Solo procesar los que tienen el checkbox marcado
            if (empty($indicador['incluir'])) {
                continue;
            }

            // Verificar si ya existe
            $existe = $this->indicadorModel
                ->where('id_cliente', $idCliente)
                ->where('nombre_indicador', $indicador['nombre'])
                ->countAllResults();

            if ($existe === 0) {
                $indicadorData = [
                    'id_cliente' => $idCliente,
                    'nombre_indicador' => $indicador['nombre'],
                    'tipo_indicador' => $indicador['tipo'],
                    'categoria' => 'induccion',
                    'formula' => $indicador['formula'],
                    'meta' => $indicador['meta'],
                    'unidad_medida' => $indicador['unidad'],
                    'periodicidad' => $indicador['periodicidad'],
                    'numeral_resolucion' => $indicador['numeral'],
                    'phva' => $indicador['phva'],
                    'definicion' => $indicador['definicion'] ?? null,
                    'interpretacion' => $indicador['interpretacion'] ?? null,
                    'origen_datos' => $indicador['origen_datos'] ?? null,
                    'cargo_responsable' => $indicador['cargo_responsable'] ?? null,
                    'cargos_conocer_resultado' => $indicador['cargos_conocer_resultado'] ?? null,
                    'activo' => 1
                ];
                $this->indicadorModel->insert($indicadorData);
                $indicadoresCreados++;
            }
        }

        return [
            'success' => true,
            'indicadores_creados' => $indicadoresCreados,
            'mensaje' => "Se agregaron {$indicadoresCreados} indicadores"
        ];
    }

    /**
     * @deprecated Usar prepararActividadesPTA() + enviarActividadesPTA() en su lugar
     */
    public function generarPTA(int $idCliente, ?int $anio = null): array
    {
        $preparacion = $this->prepararActividadesPTA($idCliente, $anio);
        if (!$preparacion['success']) {
            return $preparacion;
        }

        // Marcar todas como incluidas para insertar
        $actividades = [];
        foreach ($preparacion['actividades'] as $act) {
            $act['incluir'] = 1;
            $actividades[] = $act;
        }

        return $this->enviarActividadesPTA($idCliente, $actividades, $preparacion['anio']);
    }

    /**
     * @deprecated Usar prepararIndicadores() + enviarIndicadores() en su lugar
     */
    public function generarIndicadores(int $idCliente): array
    {
        $preparacion = $this->prepararIndicadores($idCliente);
        if (!$preparacion['success']) {
            return $preparacion;
        }

        // Marcar todos como incluidos para insertar
        $indicadores = [];
        foreach ($preparacion['indicadores'] as $ind) {
            $ind['incluir'] = 1;
            $indicadores[] = $ind;
        }

        return $this->enviarIndicadores($idCliente, $indicadores);
    }

    /**
     * Obtiene las etapas de un cliente
     */
    public function getEtapas(int $idCliente, ?int $anio = null): array
    {
        $anio = $anio ?? (int)date('Y');
        $etapas = $this->etapasModel->getEtapasByClienteAnio($idCliente, $anio);

        // Decodificar temas de cada etapa
        foreach ($etapas as &$etapa) {
            $etapa['temas_decodificados'] = $this->etapasModel->getTemasDecodificados($etapa);
            $etapa['cantidad_temas'] = count($etapa['temas_decodificados']);
        }

        return $etapas;
    }

    /**
     * Aprueba todas las etapas de un cliente
     */
    public function aprobarTodas(int $idCliente, int $anio, int $aprobadoPor): bool
    {
        return $this->etapasModel->aprobarTodas($idCliente, $anio, $aprobadoPor);
    }
}
