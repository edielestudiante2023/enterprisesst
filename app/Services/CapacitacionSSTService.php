<?php

namespace App\Services;

use App\Models\CronogcapacitacionModel;
use App\Models\CapacitacionModel;
use App\Models\PtaclienteModel;

/**
 * Servicio para generar capacitaciones SST con IA
 * segun Resolucion 0312/2019 - Estandar 1.2.1
 *
 * Genera capacitaciones en:
 * - tbl_cronog_capacitacion (cronograma especifico)
 * - tbl_pta_cliente (plan de trabajo anual)
 */
class CapacitacionSSTService
{
    protected CronogcapacitacionModel $cronogramaModel;
    protected CapacitacionModel $capacitacionModel;
    protected PtaclienteModel $ptaModel;

    /** Constantes para el Plan de Trabajo Anual */
    private const TIPO_SERVICIO_PTA = 'Programa Capacitación SST';
    private const NUMERAL_PTA = '1.2.1';
    private const PHVA_PTA = 'HACER';

    public function __construct()
    {
        $this->cronogramaModel = new CronogcapacitacionModel();
        $this->capacitacionModel = new CapacitacionModel();
        $this->ptaModel = new PtaclienteModel();
    }

    /**
     * Capacitaciones base obligatorias para cualquier empresa
     * Basadas en normatividad colombiana Res. 0312/2019
     */
    public const CAPACITACIONES_BASE = [
        [
            'mes' => 1,
            'nombre' => 'Induccion en Seguridad y Salud en el Trabajo',
            'objetivo' => 'Informar a los trabajadores sobre los peligros y riesgos de su trabajo, las medidas de prevencion y control, y los procedimientos de emergencia',
            'perfil_asistentes' => 'TODOS',
            'horas' => 2
        ],
        [
            'mes' => 2,
            'nombre' => 'Identificacion de peligros y valoracion de riesgos',
            'objetivo' => 'Capacitar en metodologia de identificacion de peligros, evaluacion y valoracion de riesgos laborales',
            'perfil_asistentes' => 'TODOS',
            'horas' => 2
        ],
        [
            'mes' => 3,
            'nombre' => 'Uso correcto de Elementos de Proteccion Personal (EPP)',
            'objetivo' => 'Instruir sobre la seleccion, uso, mantenimiento y almacenamiento adecuado de los EPP',
            'perfil_asistentes' => 'TODOS',
            'horas' => 1
        ],
        [
            'mes' => 4,
            'nombre' => 'Plan de emergencias y primeros auxilios basicos',
            'objetivo' => 'Preparar a los trabajadores para actuar ante situaciones de emergencia y brindar primeros auxilios',
            'perfil_asistentes' => 'TODOS',
            'horas' => 2
        ],
        [
            'mes' => 5,
            'nombre' => 'Prevencion de accidentes de trabajo',
            'objetivo' => 'Sensibilizar sobre las causas de los accidentes y las medidas preventivas en el lugar de trabajo',
            'perfil_asistentes' => 'TODOS',
            'horas' => 1
        ],
        [
            'mes' => 6,
            'nombre' => 'Orden y aseo en el lugar de trabajo (5S)',
            'objetivo' => 'Promover habitos de orden, limpieza y organizacion para prevenir accidentes',
            'perfil_asistentes' => 'TODOS',
            'horas' => 1
        ],
        [
            'mes' => 7,
            'nombre' => 'Capacitacion a integrantes del COPASST/Vigia SST',
            'objetivo' => 'Formar a los miembros del comite sobre sus funciones y responsabilidades segun normatividad',
            'perfil_asistentes' => 'MIEMBROS_COPASST',
            'horas' => 4
        ],
        [
            'mes' => 8,
            'nombre' => 'Reporte e investigacion de incidentes y accidentes',
            'objetivo' => 'Instruir sobre el procedimiento de reporte, registro e investigacion de accidentes e incidentes',
            'perfil_asistentes' => 'TODOS',
            'horas' => 2
        ],
        [
            'mes' => 9,
            'nombre' => 'Prevencion de enfermedades laborales',
            'objetivo' => 'Informar sobre las enfermedades laborales asociadas al trabajo y las medidas de prevencion',
            'perfil_asistentes' => 'TODOS',
            'horas' => 1
        ],
        [
            'mes' => 10,
            'nombre' => 'Manejo seguro de herramientas y equipos',
            'objetivo' => 'Instruir sobre el uso seguro de herramientas, maquinas y equipos de trabajo',
            'perfil_asistentes' => 'TRABAJADORES_RIESGOS_CRITICOS',
            'horas' => 2
        ],
        [
            'mes' => 11,
            'nombre' => 'Simulacro de evacuacion',
            'objetivo' => 'Evaluar la capacidad de respuesta ante emergencias mediante ejercicio practico',
            'perfil_asistentes' => 'TODOS',
            'horas' => 1
        ],
        [
            'mes' => 12,
            'nombre' => 'Reinduccion en SST',
            'objetivo' => 'Actualizar conocimientos en SST y reforzar comportamientos seguros',
            'perfil_asistentes' => 'TODOS',
            'horas' => 2
        ]
    ];

    /**
     * Capacitaciones adicionales segun peligros identificados
     */
    public const CAPACITACIONES_POR_PELIGRO = [
        'Biomecanico' => [
            ['nombre' => 'Higiene postural y manejo de cargas', 'objetivo' => 'Prevenir lesiones osteomusculares por posturas y esfuerzos', 'horas' => 2],
            ['nombre' => 'Prevencion de desordenes musculoesqueleticos', 'objetivo' => 'Identificar y prevenir DME asociados al trabajo', 'horas' => 1],
        ],
        'Psicosocial' => [
            ['nombre' => 'Manejo del estres laboral', 'objetivo' => 'Brindar herramientas para gestionar el estres en el trabajo', 'horas' => 2],
            ['nombre' => 'Prevencion del acoso laboral', 'objetivo' => 'Informar sobre conductas de acoso y mecanismos de prevencion', 'horas' => 1],
        ],
        'Fisico' => [
            ['nombre' => 'Proteccion auditiva y conservacion de la audicion', 'objetivo' => 'Prevenir perdida auditiva por exposicion a ruido', 'horas' => 1],
            ['nombre' => 'Prevencion de efectos por temperaturas extremas', 'objetivo' => 'Proteger contra golpe de calor o hipotermia', 'horas' => 1],
        ],
        'Quimico' => [
            ['nombre' => 'Manejo seguro de sustancias quimicas', 'objetivo' => 'Instruir sobre almacenamiento, manipulacion y disposicion de quimicos', 'horas' => 2],
            ['nombre' => 'Lectura de hojas de seguridad y etiquetado SGA', 'objetivo' => 'Interpretar informacion de seguridad de productos quimicos', 'horas' => 1],
        ],
        'Biologico' => [
            ['nombre' => 'Bioseguridad y prevencion de riesgo biologico', 'objetivo' => 'Prevenir exposicion a agentes biologicos', 'horas' => 2],
            ['nombre' => 'Manejo de residuos peligrosos', 'objetivo' => 'Clasificar y disponer adecuadamente residuos peligrosos', 'horas' => 1],
        ],
        'Electrico' => [
            ['nombre' => 'Prevencion de riesgo electrico', 'objetivo' => 'Identificar peligros electricos y medidas de control', 'horas' => 2],
            ['nombre' => 'Trabajo seguro en instalaciones electricas (RETIE)', 'objetivo' => 'Cumplir normatividad para trabajos electricos', 'horas' => 4],
        ],
        'Mecanico' => [
            ['nombre' => 'Seguridad en maquinas y equipos', 'objetivo' => 'Operar maquinaria de forma segura', 'horas' => 2],
            ['nombre' => 'Bloqueo y etiquetado de energias peligrosas', 'objetivo' => 'Aplicar procedimientos de control de energias', 'horas' => 2],
        ],
        'Locativo' => [
            ['nombre' => 'Prevencion de caidas a nivel', 'objetivo' => 'Evitar caidas por condiciones del piso y orden', 'horas' => 1],
        ],
        'Trabajo en alturas' => [
            ['nombre' => 'Trabajo seguro en alturas - Nivel basico', 'objetivo' => 'Certificar competencias para trabajo en alturas', 'horas' => 8],
            ['nombre' => 'Trabajo seguro en alturas - Nivel avanzado', 'objetivo' => 'Capacitacion para coordinadores de trabajo en alturas', 'horas' => 40],
        ],
        'Publico' => [
            ['nombre' => 'Seguridad vial', 'objetivo' => 'Prevenir accidentes de transito en actividades laborales', 'horas' => 2],
            ['nombre' => 'Autocuidado en espacios publicos', 'objetivo' => 'Comportamientos seguros fuera de las instalaciones', 'horas' => 1],
        ],
    ];

    /**
     * Obtiene el resumen de capacitaciones para un cliente
     */
    public function getResumenCapacitaciones(int $idCliente, int $anio): array
    {
        // Obtener contexto para determinar minimo
        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 60;

        $minimo = $this->getMinimoCapacitaciones($estandares);

        $existentes = $this->cronogramaModel
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_programada)', $anio)
            ->countAllResults();

        return [
            'existentes' => $existentes,
            'minimo' => $minimo,
            'completo' => $existentes >= $minimo
        ];
    }

    /**
     * Determina el minimo de capacitaciones segun estandares
     */
    protected function getMinimoCapacitaciones(int $estandares): int
    {
        if ($estandares <= 7) {
            return 4;  // Trimestral
        } elseif ($estandares <= 21) {
            return 9;  // Casi mensual
        }
        return 13;  // Mensual + extras
    }

    /**
     * Preview de las capacitaciones que se generarian
     */
    public function previewCapacitaciones(int $idCliente, int $anio, ?array $contexto = null, string $instrucciones = ''): array
    {
        $capacitaciones = [];
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        // Obtener estandares para ajustar cantidad
        $estandares = $contexto['estandares_aplicables'] ?? 60;

        // 1. Agregar capacitaciones base (ajustadas segun estandares)
        $capBase = $this->filtrarCapacitacionesPorEstandares(self::CAPACITACIONES_BASE, $estandares);

        foreach ($capBase as $idx => $cap) {
            $capacitaciones[$idx] = [
                'indice_original' => $idx,
                'mes' => $meses[$cap['mes']],
                'mes_num' => $cap['mes'],
                'nombre' => $cap['nombre'],
                'objetivo' => $cap['objetivo'],
                'perfil_asistentes' => $cap['perfil_asistentes'],
                'horas' => $cap['horas'],
                'fecha_programada' => "{$anio}-" . str_pad($cap['mes'], 2, '0', STR_PAD_LEFT) . "-15",
                'origen' => 'base'
            ];
        }

        // 2. Agregar capacitaciones segun peligros identificados
        if ($contexto && !empty($contexto['peligros_identificados'])) {
            $peligros = json_decode($contexto['peligros_identificados'], true) ?? [];
            $mesActual = 3;

            foreach ($peligros as $peligro) {
                foreach (self::CAPACITACIONES_POR_PELIGRO as $tipoPeligro => $capPeligro) {
                    if (stripos($peligro, $tipoPeligro) !== false) {
                        foreach ($capPeligro as $cap) {
                            $capacitaciones[] = [
                                'mes' => $meses[$mesActual],
                                'mes_num' => $mesActual,
                                'nombre' => $cap['nombre'],
                                'objetivo' => $cap['objetivo'],
                                'perfil_asistentes' => 'TRABAJADORES_RIESGOS_CRITICOS',
                                'horas' => $cap['horas'],
                                'fecha_programada' => "{$anio}-" . str_pad($mesActual, 2, '0', STR_PAD_LEFT) . "-15",
                                'origen' => 'peligro',
                                'peligro_relacionado' => $peligro
                            ];
                            $mesActual = ($mesActual % 12) + 1;
                        }
                    }
                }
            }
        }

        $explicacionIA = '';

        // 3. Procesar instrucciones con IA (si hay instrucciones)
        if (!empty($instrucciones)) {
            $resultadoIA = $this->interpretarInstruccionesConIA($instrucciones, $capacitaciones, $contexto, $anio);

            // Aplicar exclusiones
            if (!empty($resultadoIA['excluir'])) {
                foreach ($resultadoIA['excluir'] as $indiceExcluir) {
                    if (isset($capacitaciones[$indiceExcluir])) {
                        $capacitaciones[$indiceExcluir]['excluida'] = true;
                    }
                }
                $capacitaciones = array_filter($capacitaciones, fn($c) => !isset($c['excluida']));
            }

            // Aplicar modificaciones de mes
            if (!empty($resultadoIA['modificar'])) {
                foreach ($resultadoIA['modificar'] as $mod) {
                    $indice = $mod['indice'] ?? -1;
                    if (isset($capacitaciones[$indice]) && isset($mod['nuevo_mes'])) {
                        $nuevoMes = (int)$mod['nuevo_mes'];
                        $capacitaciones[$indice]['mes'] = $meses[$nuevoMes] ?? $capacitaciones[$indice]['mes'];
                        $capacitaciones[$indice]['mes_num'] = $nuevoMes;
                        $capacitaciones[$indice]['fecha_programada'] = "{$anio}-" . str_pad($nuevoMes, 2, '0', STR_PAD_LEFT) . "-15";
                        $capacitaciones[$indice]['modificada_por_ia'] = true;
                        if (!empty($mod['razon'])) {
                            $capacitaciones[$indice]['razon_modificacion'] = $mod['razon'];
                        }
                    }
                }
            }

            // Agregar nuevas capacitaciones sugeridas por IA
            if (!empty($resultadoIA['agregar'])) {
                foreach ($resultadoIA['agregar'] as $nueva) {
                    $capacitaciones[] = $nueva;
                }
            }

            $explicacionIA = $resultadoIA['explicacion'] ?? '';
        }

        // Reindexar y ordenar por mes
        $capacitaciones = array_values($capacitaciones);
        usort($capacitaciones, fn($a, $b) => $a['mes_num'] <=> $b['mes_num']);

        return [
            'capacitaciones' => $capacitaciones,
            'total' => count($capacitaciones),
            'anio' => $anio,
            'contexto_aplicado' => $contexto ? true : false,
            'instrucciones_procesadas' => !empty($instrucciones),
            'explicacion_ia' => $explicacionIA
        ];
    }

    /**
     * Filtra capacitaciones base segun nivel de estandares
     */
    protected function filtrarCapacitacionesPorEstandares(array $capacitaciones, int $estandares): array
    {
        if ($estandares >= 60) {
            return $capacitaciones; // Todas
        }

        // Para 21 estandares: quitar algunas opcionales
        if ($estandares <= 21) {
            $excluir = ['Orden y aseo en el lugar de trabajo (5S)', 'Manejo seguro de herramientas y equipos'];
            $capacitaciones = array_filter($capacitaciones, fn($c) => !in_array($c['nombre'], $excluir));
        }

        // Para 7 estandares: solo las esenciales (trimestral)
        if ($estandares <= 7) {
            $esenciales = [
                'Induccion en Seguridad y Salud en el Trabajo',
                'Plan de emergencias y primeros auxilios basicos',
                'Prevencion de accidentes de trabajo',
                'Reinduccion en SST'
            ];
            $capacitaciones = array_filter($capacitaciones, fn($c) => in_array($c['nombre'], $esenciales));

            // Redistribuir en meses trimestrales
            $mesesTrimestrales = [3, 6, 9, 12];
            $idx = 0;
            foreach ($capacitaciones as &$cap) {
                $cap['mes'] = $mesesTrimestrales[$idx % 4];
                $idx++;
            }
        }

        return array_values($capacitaciones);
    }

    /**
     * Interpreta instrucciones del usuario usando IA
     */
    protected function interpretarInstruccionesConIA(string $instrucciones, array $capacitacionesBase, ?array $contexto, int $anio): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            return $this->interpretarInstruccionesSimple($instrucciones, $anio);
        }

        // Preparar lista de capacitaciones para la IA
        $capacitacionesTexto = "";
        foreach ($capacitacionesBase as $idx => $cap) {
            $capacitacionesTexto .= "{$idx}. [{$cap['mes']}] {$cap['nombre']} - {$cap['objetivo']}\n";
        }

        // Contexto de la empresa
        $contextoTexto = "";
        if ($contexto) {
            $contextoTexto = "CONTEXTO DE LA EMPRESA:\n";
            $contextoTexto .= "- Actividad economica: " . ($contexto['actividad_economica_principal'] ?? 'No especificada') . "\n";
            $contextoTexto .= "- Nivel de riesgo ARL: " . ($contexto['nivel_riesgo_arl'] ?? 'No especificado') . "\n";
            $contextoTexto .= "- Trabajadores: " . ($contexto['total_trabajadores'] ?? 'No especificado') . "\n";
            if (!empty($contexto['peligros_identificados'])) {
                $peligros = json_decode($contexto['peligros_identificados'], true) ?? [];
                $contextoTexto .= "- Peligros identificados: " . implode(', ', $peligros) . "\n";
            }
        }

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia.
Tu tarea es personalizar el cronograma de capacitaciones segun las instrucciones del usuario.

REGLAS:
1. Si el usuario dice que NO incluya algo (ej: 'no incluir trabajo en alturas'), debes EXCLUIR esas capacitaciones
2. Si menciona agregar algo especifico, sugiere capacitaciones nuevas
3. Si menciona cambiar fechas, modifica el mes correspondiente
4. Responde SOLO en formato JSON valido

FORMATO DE RESPUESTA (JSON):
{
  \"excluir\": [0, 3, 5],
  \"modificar\": [{\"indice\": 2, \"nuevo_mes\": 6, \"razon\": \"...\"}],
  \"agregar\": [{\"mes\": 4, \"nombre\": \"...\", \"objetivo\": \"...\", \"horas\": 2, \"perfil_asistentes\": \"TODOS\"}],
  \"explicacion\": \"Breve explicacion de los cambios\"
}";

        $userPrompt = "ANO DEL CRONOGRAMA: {$anio}\n\n";
        $userPrompt .= $contextoTexto . "\n";
        $userPrompt .= "CAPACITACIONES BASE DISPONIBLES:\n{$capacitacionesTexto}\n";
        $userPrompt .= "INSTRUCCIONES DEL USUARIO:\n\"{$instrucciones}\"\n\n";
        $userPrompt .= "Analiza las instrucciones y genera el JSON de respuesta.";

        $response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey);

        if (!$response['success']) {
            log_message('error', 'Error en IA Capacitaciones: ' . ($response['error'] ?? 'desconocido'));
            return $this->interpretarInstruccionesSimple($instrucciones, $anio);
        }

        return $this->procesarRespuestaIA($response['contenido'], $capacitacionesBase, $anio);
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
     * Procesa la respuesta JSON de la IA
     */
    protected function procesarRespuestaIA(string $contenidoIA, array $capacitacionesBase, int $anio): array
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        // Limpiar JSON
        $contenidoIA = preg_replace('/```json\s*/', '', $contenidoIA);
        $contenidoIA = preg_replace('/```\s*/', '', $contenidoIA);

        $respuesta = json_decode($contenidoIA, true);
        if (!$respuesta) {
            log_message('warning', 'No se pudo parsear respuesta IA capacitaciones: ' . $contenidoIA);
            return ['excluir' => [], 'modificar' => [], 'agregar' => [], 'explicacion' => ''];
        }

        $resultado = [
            'excluir' => $respuesta['excluir'] ?? [],
            'modificar' => $respuesta['modificar'] ?? [],
            'agregar' => [],
            'explicacion' => $respuesta['explicacion'] ?? ''
        ];

        // Procesar capacitaciones a agregar
        if (!empty($respuesta['agregar'])) {
            foreach ($respuesta['agregar'] as $nueva) {
                $mes = (int)($nueva['mes'] ?? 6);
                $resultado['agregar'][] = [
                    'mes' => $meses[$mes] ?? 'Junio',
                    'mes_num' => $mes,
                    'nombre' => $nueva['nombre'] ?? 'Capacitacion personalizada',
                    'objetivo' => $nueva['objetivo'] ?? '',
                    'perfil_asistentes' => $nueva['perfil_asistentes'] ?? 'TODOS',
                    'horas' => $nueva['horas'] ?? 1,
                    'fecha_programada' => "{$anio}-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-15",
                    'origen' => 'ia',
                    'generado_por_ia' => true
                ];
            }
        }

        return $resultado;
    }

    /**
     * Fallback: Interpretacion simple sin IA
     */
    protected function interpretarInstruccionesSimple(string $instrucciones, int $anio): array
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $agregar = [];
        $instrLower = strtolower($instrucciones);

        $keywords = [
            'alturas' => ['nombre' => 'Trabajo seguro en alturas', 'objetivo' => 'Certificar competencias para trabajo en alturas segun Res. 4272/2021', 'horas' => 8, 'mes' => 4],
            'quimicos' => ['nombre' => 'Manejo seguro de sustancias quimicas', 'objetivo' => 'Prevenir exposicion a sustancias peligrosas', 'horas' => 2, 'mes' => 5],
            'electrico' => ['nombre' => 'Prevencion de riesgo electrico', 'objetivo' => 'Identificar peligros electricos y medidas de control', 'horas' => 2, 'mes' => 6],
            'primeros auxilios' => ['nombre' => 'Primeros auxilios basicos', 'objetivo' => 'Brindar atencion inicial en emergencias medicas', 'horas' => 4, 'mes' => 3],
            'brigada' => ['nombre' => 'Formacion de brigada de emergencias', 'objetivo' => 'Capacitar brigadistas para respuesta a emergencias', 'horas' => 8, 'mes' => 5],
        ];

        foreach ($keywords as $keyword => $config) {
            if (strpos($instrLower, $keyword) !== false) {
                $agregar[] = [
                    'mes' => $meses[$config['mes']],
                    'mes_num' => $config['mes'],
                    'nombre' => $config['nombre'],
                    'objetivo' => $config['objetivo'],
                    'perfil_asistentes' => 'TODOS',
                    'horas' => $config['horas'],
                    'fecha_programada' => "{$anio}-" . str_pad($config['mes'], 2, '0', STR_PAD_LEFT) . "-15",
                    'origen' => 'instruccion'
                ];
            }
        }

        return ['agregar' => $agregar, 'excluir' => [], 'modificar' => [], 'explicacion' => ''];
    }

    /**
     * Genera las capacitaciones en:
     * - tbl_cronog_capacitacion (cronograma especifico)
     * - tbl_pta_cliente (plan de trabajo anual)
     *
     * Un solo esfuerzo alimenta ambas tablas
     */
    public function generarCapacitaciones(int $idCliente, int $anio, ?array $capacitacionesSeleccionadas = null): array
    {
        $creadas = 0;
        $creadasPta = 0;
        $existentes = 0;
        $errores = [];

        $db = \Config\Database::connect();

        // Usar capacitaciones seleccionadas o las base
        $capacitaciones = $capacitacionesSeleccionadas ?? self::CAPACITACIONES_BASE;

        foreach ($capacitaciones as $cap) {
            $mes = $cap['mes_num'] ?? $cap['mes'];
            $fechaProgramada = "{$anio}-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-15";

            // Verificar si ya existe similar en cronograma
            $existe = $this->cronogramaModel
                ->where('id_cliente', $idCliente)
                ->where('YEAR(fecha_programada)', $anio)
                ->where('id_capacitacion IN (SELECT id_capacitacion FROM capacitaciones_sst WHERE capacitacion LIKE "%' . substr($cap['nombre'], 0, 30) . '%")', null, false)
                ->countAllResults();

            if ($existe > 0) {
                $existentes++;
                continue;
            }

            try {
                // Buscar o crear la capacitacion en el catalogo
                $capacitacionCatalogo = $this->capacitacionModel
                    ->like('capacitacion', substr($cap['nombre'], 0, 30), 'both')
                    ->first();

                if (!$capacitacionCatalogo) {
                    // Crear en catalogo
                    $this->capacitacionModel->insert([
                        'capacitacion' => $cap['nombre'],
                        'objetivo_capacitacion' => $cap['objetivo'],
                        'observaciones' => ''
                    ]);
                    $idCapacitacion = $this->capacitacionModel->getInsertID();
                } else {
                    $idCapacitacion = $capacitacionCatalogo['id_capacitacion'];
                }

                // 1. Insertar en cronograma de capacitaciones
                $this->cronogramaModel->insert([
                    'id_capacitacion' => $idCapacitacion,
                    'id_cliente' => $idCliente,
                    'fecha_programada' => $fechaProgramada,
                    'fecha_de_realizacion' => null,
                    'estado' => 'PROGRAMADA',
                    'perfil_de_asistentes' => $cap['perfil_asistentes'] ?? 'TODOS',
                    'nombre_del_capacitador' => 'CYCLOID TALENT',
                    'horas_de_duracion_de_la_capacitacion' => $cap['horas'] ?? 1,
                    'indicador_de_realizacion_de_la_capacitacion' => 'SIN CALIFICAR',
                    'numero_de_asistentes_a_capacitacion' => 0,
                    'numero_total_de_personas_programadas' => 0,
                    'porcentaje_cobertura' => '0%',
                    'numero_de_personas_evaluadas' => 0,
                    'promedio_de_calificaciones' => 0,
                    'observaciones' => ''
                ]);
                $creadas++;

                // 2. Insertar en Plan de Trabajo Anual (PTA)
                $actividadPta = $this->formatearActividadPTA($cap);

                // Verificar si ya existe en PTA
                $existePta = $this->ptaModel
                    ->where('id_cliente', $idCliente)
                    ->where('numeral_plandetrabajo', self::NUMERAL_PTA)
                    ->like('actividad_plandetrabajo', substr($cap['nombre'], 0, 30), 'both')
                    ->countAllResults();

                if ($existePta === 0) {
                    $this->ptaModel->insert([
                        'id_cliente' => $idCliente,
                        'tipo_servicio' => self::TIPO_SERVICIO_PTA,
                        'phva_plandetrabajo' => self::PHVA_PTA,
                        'numeral_plandetrabajo' => self::NUMERAL_PTA,
                        'actividad_plandetrabajo' => $actividadPta,
                        'responsable_sugerido_plandetrabajo' => 'Responsable SST',
                        'fecha_propuesta' => $fechaProgramada,
                        'fecha_cierre' => null,
                        'responsable_definido_paralaactividad' => null,
                        'estado_actividad' => 'ABIERTA',
                        'porcentaje_avance' => 0,
                        'semana' => $this->calcularSemana($fechaProgramada),
                        'observaciones' => "Capacitacion generada automaticamente - {$anio}"
                    ]);
                    $creadasPta++;
                }

            } catch (\Exception $e) {
                $errores[] = "Error en '{$cap['nombre']}': " . $e->getMessage();
            }
        }

        return [
            'creadas' => $creadas,
            'creadas_pta' => $creadasPta,
            'existentes' => $existentes,
            'errores' => $errores,
            'total' => count($capacitaciones)
        ];
    }

    /**
     * Formatea la descripcion de la actividad para el PTA
     */
    protected function formatearActividadPTA(array $capacitacion): string
    {
        $nombre = $capacitacion['nombre'] ?? 'Capacitacion SST';
        $objetivo = $capacitacion['objetivo'] ?? '';
        $horas = $capacitacion['horas'] ?? 1;
        $perfil = $capacitacion['perfil_asistentes'] ?? 'TODOS';

        return "Capacitacion: {$nombre}. Objetivo: {$objetivo}. Duracion: {$horas}h. Dirigido a: {$perfil}";
    }

    /**
     * Calcula el numero de semana del año para una fecha
     */
    protected function calcularSemana(string $fecha): int
    {
        $dt = new \DateTime($fecha);
        return (int) $dt->format('W');
    }

    /**
     * Obtiene las capacitaciones del cronograma de un cliente
     */
    public function getCapacitacionesCliente(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        return $db->table('tbl_cronog_capacitacion c')
            ->select('c.*, cap.capacitacion as nombre_capacitacion, cap.objetivo_capacitacion')
            ->join('capacitaciones_sst cap', 'cap.id_capacitacion = c.id_capacitacion', 'left')
            ->where('c.id_cliente', $idCliente)
            ->where('YEAR(c.fecha_programada)', $anio)
            ->orderBy('c.fecha_programada', 'ASC')
            ->get()
            ->getResultArray();
    }
}
