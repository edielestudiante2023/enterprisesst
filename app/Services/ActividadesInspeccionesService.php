<?php

namespace App\Services;

use App\Models\PtaclienteModel;

/**
 * Servicio para generar actividades del Programa de Inspecciones
 * segun Resolucion 0312/2019 - Estandar 4.2.4
 *
 * GOLD STANDARD — Usar como referencia para crear nuevos programas Tipo B (Parte 1).
 * Guia: docs/MODULO_NUMERALES_SGSST/03_MODULO_3_PARTES/ZZ_98_COMO_AGREGAR_PROGRAMA.md
 *
 * Programa de inspecciones a instalaciones, maquinaria o equipos
 * con participacion del COPASST o Vigia SST.
 */
class ActividadesInspeccionesService
{
    protected PtaclienteModel $ptaModel;

    public function __construct()
    {
        $this->ptaModel = new PtaclienteModel();
    }

    /**
     * Actividades del Programa de Inspecciones
     * Basadas en Res. 0312/2019 estandar 4.2.4, Decreto 1072/2015, NTC 4114
     */
    public const ACTIVIDADES_INSPECCIONES = [
        [
            'mes' => 1,
            'actividad' => 'Elaboracion del plan anual de inspecciones (tipos, frecuencia, areas, responsables, formatos)',
            'objetivo' => 'Planificar las inspecciones del ano definiendo tipos de inspeccion, frecuencia, areas a cubrir, responsables y listas de verificacion a utilizar',
            'responsable' => 'Responsable SST',
            'phva' => 'PLANEAR',
            'numeral' => '4.2.4'
        ],
        [
            'mes' => 2,
            'actividad' => 'Capacitacion al COPASST/Vigia SST en metodologia de inspeccion y uso de listas de verificacion',
            'objetivo' => 'Entrenar a los miembros del COPASST o Vigia SST en tecnicas de inspeccion, identificacion de condiciones inseguras y diligenciamiento de formatos',
            'responsable' => 'Responsable SST / ARL',
            'phva' => 'PLANEAR',
            'numeral' => '4.2.4'
        ],
        [
            'mes' => 3,
            'actividad' => 'Inspeccion de instalaciones generales (locativas, electricas, sanitarias, vias de circulacion) con COPASST',
            'objetivo' => 'Identificar condiciones inseguras en instalaciones locativas, sistemas electricos, instalaciones sanitarias y vias de circulacion',
            'responsable' => 'Responsable SST / COPASST',
            'phva' => 'HACER',
            'numeral' => '4.2.4'
        ],
        [
            'mes' => 4,
            'actividad' => 'Inspeccion de maquinaria y equipos (guardas de seguridad, dispositivos de parada, mantenimiento preventivo)',
            'objetivo' => 'Verificar condiciones de seguridad de maquinaria y equipos, estado de guardas, dispositivos de parada de emergencia y cumplimiento del mantenimiento preventivo',
            'responsable' => 'Responsable SST / COPASST',
            'phva' => 'HACER',
            'numeral' => '4.2.4'
        ],
        [
            'mes' => 5,
            'actividad' => 'Inspeccion de equipos de emergencia (extintores, camillas, botiquines, senalizacion, alarmas, rutas de evacuacion)',
            'objetivo' => 'Verificar el estado, ubicacion, senalizacion y vigencia de los equipos de emergencia disponibles en la empresa',
            'responsable' => 'Responsable SST / COPASST / Brigada',
            'phva' => 'HACER',
            'numeral' => '4.2.4'
        ],
        [
            'mes' => 6,
            'actividad' => 'Inspeccion de elementos de proteccion personal (inventario, estado, registros de entrega, uso correcto)',
            'objetivo' => 'Verificar el inventario de EPP, su estado de conservacion, registros de entrega y uso correcto por parte de los trabajadores',
            'responsable' => 'Responsable SST / COPASST',
            'phva' => 'HACER',
            'numeral' => '4.2.4'
        ],
        [
            'mes' => 7,
            'actividad' => 'Inspeccion de areas de almacenamiento, orden y aseo (sustancias quimicas, materiales, residuos)',
            'objetivo' => 'Evaluar condiciones de orden, aseo y almacenamiento seguro de materiales, sustancias quimicas y manejo de residuos',
            'responsable' => 'Responsable SST / COPASST',
            'phva' => 'HACER',
            'numeral' => '4.2.4'
        ],
        [
            'mes' => 8,
            'actividad' => 'Seguimiento a acciones correctivas y preventivas derivadas de inspecciones del primer semestre',
            'objetivo' => 'Verificar el estado de implementacion y eficacia de las acciones correctivas generadas en las inspecciones del primer semestre',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '4.2.4'
        ],
        [
            'mes' => 9,
            'actividad' => 'Segundo ciclo: Inspeccion de instalaciones generales y maquinaria con COPASST (comparar con hallazgos previos)',
            'objetivo' => 'Realizar segundo ciclo de inspecciones a instalaciones y maquinaria, comparando con hallazgos del primer semestre para medir la mejora',
            'responsable' => 'Responsable SST / COPASST',
            'phva' => 'HACER',
            'numeral' => '4.2.4'
        ],
        [
            'mes' => 10,
            'actividad' => 'Segundo ciclo: Inspeccion de equipos de emergencia y EPP (verificar reposiciones y mantenimientos)',
            'objetivo' => 'Verificar el estado de equipos de emergencia y EPP despues de las reposiciones y mantenimientos realizados en el primer semestre',
            'responsable' => 'Responsable SST / COPASST / Brigada',
            'phva' => 'HACER',
            'numeral' => '4.2.4'
        ],
        [
            'mes' => 11,
            'actividad' => 'Evaluacion de indicadores del programa de inspecciones y eficacia de acciones correctivas',
            'objetivo' => 'Medir el cumplimiento de indicadores del programa, analizar tendencias de hallazgos y evaluar la eficacia de las acciones implementadas',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '4.2.4'
        ],
        [
            'mes' => 12,
            'actividad' => 'Informe anual del programa de inspecciones y planificacion del siguiente ano',
            'objetivo' => 'Consolidar resultados anuales del programa, comparar con metas, identificar logros y oportunidades de mejora, y planificar el proximo ano',
            'responsable' => 'Responsable SST',
            'phva' => 'ACTUAR',
            'numeral' => '4.2.4'
        ]
    ];

    /**
     * Obtiene el resumen de actividades de inspecciones para un cliente
     */
    public function getResumenActividades(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        $existentes = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->groupStart()
                ->where('tipo_servicio', 'Programa de Inspecciones')
                ->orLike('tipo_servicio', 'Inspecciones', 'both')
                ->orLike('actividad_plandetrabajo', 'inspeccion', 'both')
                ->orLike('actividad_plandetrabajo', 'instalaciones', 'both')
                ->orLike('actividad_plandetrabajo', 'maquinaria', 'both')
                ->orLike('actividad_plandetrabajo', 'equipos de emergencia', 'both')
                ->orLike('actividad_plandetrabajo', 'condiciones inseguras', 'both')
            ->groupEnd()
            ->countAllResults();

        return [
            'existentes' => $existentes,
            'sugeridas' => count(self::ACTIVIDADES_INSPECCIONES),
            'completo' => $existentes >= 5
        ];
    }

    /**
     * Preview de las actividades que se generarian
     */
    public function previewActividades(int $idCliente, int $anio, ?array $contexto = null, string $instrucciones = ''): array
    {
        $actividades = [];
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        foreach (self::ACTIVIDADES_INSPECCIONES as $idx => $act) {
            $actividades[$idx] = [
                'indice_original' => $idx,
                'mes' => $meses[$act['mes']],
                'mes_num' => $act['mes'],
                'actividad' => $act['actividad'],
                'objetivo' => $act['objetivo'],
                'responsable' => $act['responsable'],
                'phva' => $act['phva'],
                'numeral' => $act['numeral'],
                'fecha_propuesta' => "{$anio}-" . str_pad($act['mes'], 2, '0', STR_PAD_LEFT) . "-15",
                'origen' => 'base'
            ];
        }

        $explicacionIA = '';

        if (!empty($instrucciones)) {
            $resultadoIA = $this->interpretarInstruccionesConIA($instrucciones, $actividades, $contexto, $anio);

            if (!empty($resultadoIA['excluir'])) {
                foreach ($resultadoIA['excluir'] as $indiceExcluir) {
                    if (isset($actividades[$indiceExcluir])) {
                        $actividades[$indiceExcluir]['excluida'] = true;
                    }
                }
                $actividades = array_filter($actividades, fn($a) => !isset($a['excluida']));
            }

            if (!empty($resultadoIA['modificar'])) {
                foreach ($resultadoIA['modificar'] as $mod) {
                    $indice = $mod['indice'] ?? -1;
                    if (isset($actividades[$indice]) && isset($mod['nuevo_mes'])) {
                        $nuevoMes = (int)$mod['nuevo_mes'];
                        $actividades[$indice]['mes'] = $meses[$nuevoMes] ?? $actividades[$indice]['mes'];
                        $actividades[$indice]['mes_num'] = $nuevoMes;
                        $actividades[$indice]['fecha_propuesta'] = "{$anio}-" . str_pad($nuevoMes, 2, '0', STR_PAD_LEFT) . "-15";
                        $actividades[$indice]['modificada_por_ia'] = true;
                    }
                }
            }

            if (!empty($resultadoIA['agregar'])) {
                foreach ($resultadoIA['agregar'] as $nueva) {
                    $actividades[] = $nueva;
                }
            }

            $explicacionIA = $resultadoIA['explicacion'] ?? '';
        }

        $actividades = array_values($actividades);
        usort($actividades, function($a, $b) {
            return $a['mes_num'] <=> $b['mes_num'];
        });

        return [
            'actividades' => $actividades,
            'total' => count($actividades),
            'anio' => $anio,
            'contexto_aplicado' => $contexto ? true : false,
            'instrucciones_procesadas' => !empty($instrucciones),
            'explicacion_ia' => $explicacionIA
        ];
    }

    /**
     * Interpreta instrucciones del usuario usando IA
     */
    protected function interpretarInstruccionesConIA(string $instrucciones, array $actividadesBase, ?array $contexto, int $anio): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            return $this->interpretarInstruccionesSimple($instrucciones, $anio);
        }

        $actividadesTexto = "";
        foreach ($actividadesBase as $idx => $act) {
            $actividadesTexto .= "{$idx}. [{$act['mes']}] {$act['actividad']} - {$act['objetivo']}\n";
        }

        $contextoTexto = "";
        if ($contexto) {
            $contextoTexto = "CONTEXTO DE LA EMPRESA:\n";
            $contextoTexto .= "- Actividad economica: " . ($contexto['actividad_economica_principal'] ?? 'No especificada') . "\n";
            $contextoTexto .= "- Nivel de riesgo: " . ($contexto['nivel_riesgo_arl'] ?? 'No especificado') . "\n";
            $contextoTexto .= "- Trabajadores: " . ($contexto['total_trabajadores'] ?? 'No especificado') . "\n";
            if (!empty($contexto['observaciones_contexto'])) {
                $contextoTexto .= "\nOBSERVACIONES DEL CONSULTOR:\n" . $contexto['observaciones_contexto'] . "\n";
            }
            // Campos ampliados del contexto
            if (!empty($contexto['horario_lunes_viernes'])) $contextoTexto .= "- Horario L-V: {$contexto['horario_lunes_viernes']}\n";
            if (!empty($contexto['descripcion_turnos'])) $contextoTexto .= "- Detalle turnos: {$contexto['descripcion_turnos']}\n";
            if (!empty($contexto['eps_principales'])) $contextoTexto .= "- EPS: {$contexto['eps_principales']}\n";
            if (!empty($contexto['manejo_incapacidades'])) $contextoTexto .= "- Manejo incapacidades: {$contexto['manejo_incapacidades']}\n";
            if (!empty($contexto['epp_por_cargo'])) $contextoTexto .= "- EPP por cargo: {$contexto['epp_por_cargo']}\n";
            if (!empty($contexto['vehiculos_maquinaria'])) $contextoTexto .= "- Vehiculos/maquinaria: {$contexto['vehiculos_maquinaria']}\n";
            if (!empty($contexto['actividades_alto_riesgo'])) {
                $actArr = is_array($contexto['actividades_alto_riesgo']) ? $contexto['actividades_alto_riesgo'] : json_decode($contexto['actividades_alto_riesgo'], true);
                if (is_array($actArr) && !empty($actArr)) $contextoTexto .= "- Actividades alto riesgo: " . implode(', ', $actArr) . "\n";
            }
            if (!empty($contexto['accidentes_ultimo_anio']) && $contexto['accidentes_ultimo_anio'] > 0) $contextoTexto .= "- Accidentes ultimo ano: {$contexto['accidentes_ultimo_anio']}\n";
            if (!empty($contexto['enfermedades_laborales_activas'])) $contextoTexto .= "- Enfermedades laborales: {$contexto['enfermedades_laborales_activas']}\n";
            if (!empty($contexto['numero_pisos']) && $contexto['numero_pisos'] > 1) $contextoTexto .= "- Pisos: {$contexto['numero_pisos']}\n";
            if (!empty($contexto['sustancias_quimicas'])) $contextoTexto .= "- Sustancias quimicas: {$contexto['sustancias_quimicas']}\n";
        }

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia, especialista en inspecciones de seguridad.
Tu tarea es personalizar la lista de actividades del Programa de Inspecciones segun las instrucciones del usuario.

REGLAS:
1. Si el usuario dice que NO incluya algo, EXCLUYE esas actividades
2. Si menciona periodicidad, ajusta segun corresponda
3. Si pide agregar algo especifico, sugiere actividades nuevas
4. La participacion del COPASST/Vigia SST es OBLIGATORIA en inspecciones (estandar 4.2.4)
5. Responde SOLO en formato JSON valido

FORMATO DE RESPUESTA (JSON):
{
  \"excluir\": [0, 3, 5],
  \"modificar\": [{\"indice\": 2, \"nuevo_mes\": 6, \"razon\": \"...\"}],
  \"agregar\": [{\"mes\": 4, \"actividad\": \"...\", \"objetivo\": \"...\", \"phva\": \"HACER\"}],
  \"explicacion\": \"Breve explicacion de los cambios\"
}";

        $userPrompt = "ANO DEL PROGRAMA: {$anio}\n\n";
        $userPrompt .= $contextoTexto . "\n";
        $userPrompt .= "ACTIVIDADES BASE DISPONIBLES:\n{$actividadesTexto}\n";
        $userPrompt .= "INSTRUCCIONES DEL USUARIO:\n\"{$instrucciones}\"\n\n";
        $userPrompt .= "Analiza las instrucciones y genera el JSON de respuesta.";

        $response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey);

        if (!$response['success']) {
            log_message('error', 'Error en IA Inspecciones: ' . ($response['error'] ?? 'desconocido'));
            return $this->interpretarInstruccionesSimple($instrucciones, $anio);
        }

        return $this->procesarRespuestaIA($response['contenido'], $actividadesBase, $anio);
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
    protected function procesarRespuestaIA(string $contenidoIA, array $actividadesBase, int $anio): array
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $contenidoIA = preg_replace('/```json\s*/', '', $contenidoIA);
        $contenidoIA = preg_replace('/```\s*/', '', $contenidoIA);

        $respuesta = json_decode($contenidoIA, true);
        if (!$respuesta) {
            return ['cambios' => [], 'explicacion' => 'No se pudieron procesar las instrucciones'];
        }

        $resultado = [
            'excluir' => $respuesta['excluir'] ?? [],
            'modificar' => $respuesta['modificar'] ?? [],
            'agregar' => [],
            'explicacion' => $respuesta['explicacion'] ?? ''
        ];

        if (!empty($respuesta['agregar'])) {
            foreach ($respuesta['agregar'] as $nueva) {
                $mes = (int)($nueva['mes'] ?? 6);
                $resultado['agregar'][] = [
                    'mes' => $meses[$mes] ?? 'Junio',
                    'mes_num' => $mes,
                    'actividad' => $nueva['actividad'] ?? 'Actividad personalizada',
                    'objetivo' => $nueva['objetivo'] ?? '',
                    'responsable' => $nueva['responsable'] ?? 'Responsable SST / COPASST',
                    'phva' => $nueva['phva'] ?? 'HACER',
                    'numeral' => '4.2.4',
                    'fecha_propuesta' => "{$anio}-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-15",
                    'origen' => 'ia',
                    'generado_por_ia' => true
                ];
            }
        }

        return $resultado;
    }

    /**
     * Fallback: Interpretacion simple sin IA (keywords)
     */
    protected function interpretarInstruccionesSimple(string $instrucciones, int $anio): array
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $actividades = [];
        $instrLower = strtolower($instrucciones);

        $keywords = [
            'trabajo en alturas' => ['actividad' => 'Inspeccion de equipos y sistemas de proteccion contra caidas para trabajo en alturas', 'objetivo' => 'Verificar el estado de arneses, lineas de vida, puntos de anclaje y certificaciones vigentes', 'phva' => 'HACER', 'mes' => 5],
            'espacios confinados' => ['actividad' => 'Inspeccion de condiciones de ingreso a espacios confinados', 'objetivo' => 'Verificar equipos de medicion de atmosferas, ventilacion, permisos de trabajo y procedimientos de rescate', 'phva' => 'HACER', 'mes' => 6],
            'vehiculos' => ['actividad' => 'Inspeccion pre-operacional de vehiculos y equipos moviles', 'objetivo' => 'Verificar condiciones mecanicas, documentacion, kit de carretera y elementos de seguridad de vehiculos', 'phva' => 'HACER', 'mes' => 4],
        ];

        foreach ($keywords as $keyword => $config) {
            if (strpos($instrLower, $keyword) !== false) {
                $actividades[] = [
                    'mes' => $meses[$config['mes']],
                    'mes_num' => $config['mes'],
                    'actividad' => $config['actividad'],
                    'objetivo' => $config['objetivo'],
                    'responsable' => 'Responsable SST / COPASST',
                    'phva' => $config['phva'],
                    'numeral' => '4.2.4',
                    'fecha_propuesta' => "{$anio}-" . str_pad($config['mes'], 2, '0', STR_PAD_LEFT) . "-15",
                    'origen' => 'instruccion'
                ];
            }
        }

        return ['agregar' => $actividades, 'excluir' => [], 'modificar' => [], 'explicacion' => ''];
    }

    /**
     * Genera las actividades de inspecciones en el PTA
     */
    public function generarActividades(int $idCliente, int $anio, ?array $actividadesSeleccionadas = null): array
    {
        $creadas = 0;
        $existentes = 0;
        $errores = [];

        $db = \Config\Database::connect();

        $actividades = $actividadesSeleccionadas ?? self::ACTIVIDADES_INSPECCIONES;

        foreach ($actividades as $act) {
            $mes = $act['mes_num'] ?? $act['mes'];
            $fechaPropuesta = "{$anio}-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-15";

            $existe = $db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('YEAR(fecha_propuesta)', $anio)
                ->like('actividad_plandetrabajo', substr($act['actividad'], 0, 30), 'both')
                ->countAllResults();

            if ($existe > 0) {
                $existentes++;
                continue;
            }

            try {
                $semana = (int)date('W', strtotime($fechaPropuesta));

                $db->table('tbl_pta_cliente')->insert([
                    'id_cliente' => $idCliente,
                    'tipo_servicio' => 'Programa de Inspecciones',
                    'phva_plandetrabajo' => $act['phva'] ?? 'HACER',
                    'numeral_plandetrabajo' => $act['numeral'] ?? '4.2.4',
                    'actividad_plandetrabajo' => $act['actividad'],
                    'responsable_sugerido_plandetrabajo' => $act['responsable'] ?? 'Responsable SST',
                    'fecha_propuesta' => $fechaPropuesta,
                    'estado_actividad' => 'ABIERTA',
                    'porcentaje_avance' => 0,
                    'semana' => $semana
                ]);
                $creadas++;
            } catch (\Exception $e) {
                $errores[] = "Error en '{$act['actividad']}': " . $e->getMessage();
            }
        }

        return [
            'creadas' => $creadas,
            'existentes' => $existentes,
            'errores' => $errores,
            'total' => count($actividades)
        ];
    }

    /**
     * Obtiene las actividades de inspecciones del PTA de un cliente
     */
    public function getActividadesCliente(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        return $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->groupStart()
                ->where('tipo_servicio', 'Programa de Inspecciones')
                ->orLike('tipo_servicio', 'Inspecciones', 'both')
                ->orLike('actividad_plandetrabajo', 'inspeccion', 'both')
                ->orLike('actividad_plandetrabajo', 'instalaciones', 'both')
                ->orLike('actividad_plandetrabajo', 'maquinaria', 'both')
                ->orLike('actividad_plandetrabajo', 'equipos de emergencia', 'both')
                ->orLike('actividad_plandetrabajo', 'condiciones inseguras', 'both')
            ->groupEnd()
            ->orderBy('fecha_propuesta', 'ASC')
            ->get()
            ->getResultArray();
    }
}
