<?php

namespace App\Services;

use App\Models\PtaclienteModel;

/**
 * Servicio para generar actividades de Mantenimiento Periodico
 * segun Resolucion 0312/2019 - Estandar 4.2.5
 *
 * Mantenimiento periodico de instalaciones, equipos, maquinas, herramientas.
 * Las actividades se personalizan segun el inventario de activos de cada empresa.
 */
class ActividadesMantenimientoPeriodicoService
{
    protected PtaclienteModel $ptaModel;

    public function __construct()
    {
        $this->ptaModel = new PtaclienteModel();
    }

    /**
     * Actividades base de Mantenimiento Periodico
     * Basadas en Decreto 1072/2015 art. 2.2.4.6.24, Resolucion 0312/2019 est. 4.2.5
     */
    public const ACTIVIDADES_MANTENIMIENTO = [
        [
            'mes' => 1,
            'actividad' => 'Levantamiento de inventario de instalaciones, equipos, maquinas y herramientas susceptibles de mantenimiento',
            'objetivo' => 'Identificar y documentar todos los activos de la empresa que requieren mantenimiento periodico, clasificandolos por tipo y criticidad',
            'responsable' => 'Responsable SST',
            'phva' => 'PLANEAR',
            'numeral' => '4.2.5'
        ],
        [
            'mes' => 1,
            'actividad' => 'Elaboracion/actualizacion de fichas tecnicas de equipos y maquinas',
            'objetivo' => 'Documentar las especificaciones tecnicas, manuales del fabricante y requerimientos de mantenimiento de cada activo',
            'responsable' => 'Responsable SST / Mantenimiento',
            'phva' => 'PLANEAR',
            'numeral' => '4.2.5'
        ],
        [
            'mes' => 2,
            'actividad' => 'Elaboracion del cronograma anual de mantenimiento preventivo',
            'objetivo' => 'Programar las intervenciones de mantenimiento preventivo para cada equipo segun recomendaciones del fabricante y analisis de criticidad',
            'responsable' => 'Responsable SST / Mantenimiento',
            'phva' => 'PLANEAR',
            'numeral' => '4.2.5'
        ],
        [
            'mes' => 3,
            'actividad' => 'Inspeccion de seguridad a instalaciones fisicas (primer trimestre)',
            'objetivo' => 'Verificar el estado de instalaciones electricas, hidraulicas, sanitarias, locativas y estructurales, generando informe con hallazgos y recomendaciones',
            'responsable' => 'Responsable SST',
            'phva' => 'HACER',
            'numeral' => '4.2.5'
        ],
        [
            'mes' => 4,
            'actividad' => 'Ejecucion de mantenimiento preventivo programado (primer ciclo)',
            'objetivo' => 'Realizar mantenimiento preventivo a equipos, maquinas y herramientas segun cronograma establecido',
            'responsable' => 'Responsable SST / Mantenimiento',
            'phva' => 'HACER',
            'numeral' => '4.2.5'
        ],
        [
            'mes' => 5,
            'actividad' => 'Inspeccion y mantenimiento de sistemas electricos e iluminacion',
            'objetivo' => 'Verificar el estado de instalaciones electricas, tableros, cableado, interruptores y sistemas de iluminacion; corregir hallazgos',
            'responsable' => 'Responsable SST / Electricista',
            'phva' => 'HACER',
            'numeral' => '4.2.5'
        ],
        [
            'mes' => 6,
            'actividad' => 'Inspeccion de seguridad a instalaciones fisicas (segundo trimestre)',
            'objetivo' => 'Verificar el estado de instalaciones, equipos y areas de trabajo; evaluar cumplimiento de hallazgos previos',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '4.2.5'
        ],
        [
            'mes' => 7,
            'actividad' => 'Revision y calibracion de equipos de medicion y herramientas criticas',
            'objetivo' => 'Verificar la calibracion de instrumentos de medicion y el estado de herramientas criticas para garantizar su funcionamiento seguro',
            'responsable' => 'Responsable SST / Mantenimiento',
            'phva' => 'HACER',
            'numeral' => '4.2.5'
        ],
        [
            'mes' => 8,
            'actividad' => 'Ejecucion de mantenimiento preventivo programado (segundo ciclo)',
            'objetivo' => 'Realizar mantenimiento preventivo semestral a equipos y maquinas segun cronograma; documentar intervenciones',
            'responsable' => 'Responsable SST / Mantenimiento',
            'phva' => 'HACER',
            'numeral' => '4.2.5'
        ],
        [
            'mes' => 9,
            'actividad' => 'Inspeccion de seguridad a instalaciones fisicas (tercer trimestre)',
            'objetivo' => 'Evaluar el estado de instalaciones, equipos de emergencia y senalizacion; seguimiento a acciones correctivas pendientes',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '4.2.5'
        ],
        [
            'mes' => 10,
            'actividad' => 'Verificacion de equipos de emergencia y proteccion contra incendios',
            'objetivo' => 'Inspeccionar extintores, gabinetes contra incendio, sistemas de deteccion, iluminacion de emergencia y senalizacion de evacuacion',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '4.2.5'
        ],
        [
            'mes' => 11,
            'actividad' => 'Evaluacion de indicadores del programa de mantenimiento periodico',
            'objetivo' => 'Medir cumplimiento de indicadores del programa, analizar fallas presentadas y evaluar efectividad del mantenimiento preventivo',
            'responsable' => 'Responsable SST',
            'phva' => 'VERIFICAR',
            'numeral' => '4.2.5'
        ],
        [
            'mes' => 12,
            'actividad' => 'Informe anual del programa de mantenimiento y planificacion siguiente ano',
            'objetivo' => 'Evaluar resultados anuales del programa, documentar lecciones aprendidas y elaborar cronograma de mantenimiento para el proximo ano',
            'responsable' => 'Responsable SST',
            'phva' => 'ACTUAR',
            'numeral' => '4.2.5'
        ]
    ];

    /**
     * Obtiene el resumen de actividades de Mantenimiento Periodico para un cliente
     */
    public function getResumenActividades(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        $existentes = $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->groupStart()
                ->where('tipo_servicio', 'Mantenimiento Periodico')
                ->orLike('tipo_servicio', 'Mantenimiento', 'both')
                ->orLike('actividad_plandetrabajo', 'mantenimiento preventivo', 'both')
                ->orLike('actividad_plandetrabajo', 'mantenimiento correctivo', 'both')
                ->orLike('actividad_plandetrabajo', 'mantenimiento periodico', 'both')
                ->orLike('actividad_plandetrabajo', 'inspeccion de seguridad', 'both')
                ->orLike('actividad_plandetrabajo', 'fichas tecnicas', 'both')
                ->orLike('actividad_plandetrabajo', 'inventario de equipos', 'both')
                ->orLike('actividad_plandetrabajo', 'calibracion', 'both')
            ->groupEnd()
            ->countAllResults();

        return [
            'existentes' => $existentes,
            'sugeridas' => count(self::ACTIVIDADES_MANTENIMIENTO),
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

        // 1. Agregar actividades base
        foreach (self::ACTIVIDADES_MANTENIMIENTO as $idx => $act) {
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

        // 2. Procesar instrucciones con IA (si hay instrucciones o inventario)
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

        // Reindexar y ordenar por mes
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
     * Interpreta instrucciones del usuario (inventario de activos) usando IA
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
            if (!empty($contexto['peligros_identificados'])) {
                $peligros = is_string($contexto['peligros_identificados'])
                    ? (json_decode($contexto['peligros_identificados'], true) ?? [])
                    : $contexto['peligros_identificados'];
                if (!empty($peligros)) {
                    $contextoTexto .= "- Peligros identificados: " . implode(', ', $peligros) . "\n";
                }
            }
            if (!empty($contexto['observaciones_contexto'])) {
                $contextoTexto .= "\nOBSERVACIONES DEL CONSULTOR:\n" . $contexto['observaciones_contexto'] . "\n";
            }
        }

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia, especializado en mantenimiento de instalaciones, equipos, maquinas y herramientas.
Tu tarea es personalizar la lista de actividades de mantenimiento periodico segun el INVENTARIO DE ACTIVOS de la empresa y las instrucciones del usuario.

REGLAS:
1. Si el usuario describe los equipos/activos de la empresa, AGREGA actividades especificas de mantenimiento para esos activos
2. Si la empresa es administrativa (oficinas, computadores, aire acondicionado), ajusta las actividades a ese contexto
3. Si la empresa es industrial (maquinaria pesada, montacargas, compresores), agrega actividades de mantenimiento industrial
4. Si menciona equipos especificos, sugiere actividades de mantenimiento preventivo para cada uno
5. EXCLUYE actividades que no apliquen al tipo de empresa
6. Responde SOLO en formato JSON valido

FORMATO DE RESPUESTA (JSON):
{
  \"excluir\": [0, 3, 5],
  \"modificar\": [{\"indice\": 2, \"nuevo_mes\": 6, \"razon\": \"...\"}],
  \"agregar\": [{\"mes\": 4, \"actividad\": \"...\", \"objetivo\": \"...\", \"phva\": \"HACER\"}],
  \"explicacion\": \"Breve explicacion de los cambios segun el inventario de activos\"
}";

        $userPrompt = "ANO DEL PROGRAMA: {$anio}\n\n";
        $userPrompt .= $contextoTexto . "\n";
        $userPrompt .= "ACTIVIDADES BASE DISPONIBLES:\n{$actividadesTexto}\n";
        $userPrompt .= "INSTRUCCIONES DEL USUARIO / INVENTARIO DE ACTIVOS:\n\"{$instrucciones}\"\n\n";
        $userPrompt .= "Analiza el inventario de activos y las instrucciones, y genera el JSON de respuesta personalizando las actividades de mantenimiento.";

        $response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey);

        if (!$response['success']) {
            log_message('error', 'Error en IA Mantenimiento Periodico: ' . ($response['error'] ?? 'desconocido'));
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
                    'responsable' => $nueva['responsable'] ?? 'Responsable SST',
                    'phva' => $nueva['phva'] ?? 'HACER',
                    'numeral' => '4.2.5',
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
            'montacargas' => ['actividad' => 'Mantenimiento preventivo de montacargas y equipos de elevacion', 'objetivo' => 'Realizar inspeccion y mantenimiento de montacargas segun ficha tecnica del fabricante', 'phva' => 'HACER', 'mes' => 4],
            'compresor' => ['actividad' => 'Mantenimiento de compresores y sistemas neumaticos', 'objetivo' => 'Verificar funcionamiento, presion, valvulas de seguridad y realizar mantenimiento preventivo de compresores', 'phva' => 'HACER', 'mes' => 5],
            'aire acondicionado' => ['actividad' => 'Mantenimiento de sistemas de aire acondicionado y ventilacion', 'objetivo' => 'Realizar limpieza de filtros, recarga de gas y revision de ductos del sistema de climatizacion', 'phva' => 'HACER', 'mes' => 3],
            'caldera' => ['actividad' => 'Inspeccion y mantenimiento de calderas y equipos a presion', 'objetivo' => 'Verificar certificado de operacion, valvulas de seguridad y realizar mantenimiento segun normativa vigente', 'phva' => 'HACER', 'mes' => 4],
            'vehiculo' => ['actividad' => 'Inspeccion preoperacional y mantenimiento de vehiculos', 'objetivo' => 'Verificar condiciones mecanicas de vehiculos, revision tecnico-mecanica vigente y plan de mantenimiento', 'phva' => 'HACER', 'mes' => 3],
            'computador' => ['actividad' => 'Mantenimiento preventivo de equipos de computo y perifericos', 'objetivo' => 'Realizar limpieza, actualizacion de software, revision de ergonomia del puesto de trabajo', 'phva' => 'HACER', 'mes' => 6],
            'ascensor' => ['actividad' => 'Inspeccion y mantenimiento de ascensores y elevadores', 'objetivo' => 'Verificar certificado de inspeccion anual, mantenimiento preventivo y funcionamiento de sistemas de seguridad', 'phva' => 'HACER', 'mes' => 5],
            'puente grua' => ['actividad' => 'Inspeccion y mantenimiento de puentes grua y equipos de izaje', 'objetivo' => 'Verificar certificacion, cables, ganchos, frenos y sistemas de seguridad del equipo de izaje', 'phva' => 'HACER', 'mes' => 4],
        ];

        foreach ($keywords as $keyword => $config) {
            if (strpos($instrLower, $keyword) !== false) {
                $actividades[] = [
                    'mes' => $meses[$config['mes']],
                    'mes_num' => $config['mes'],
                    'actividad' => $config['actividad'],
                    'objetivo' => $config['objetivo'],
                    'responsable' => 'Responsable SST',
                    'phva' => $config['phva'],
                    'numeral' => '4.2.5',
                    'fecha_propuesta' => "{$anio}-" . str_pad($config['mes'], 2, '0', STR_PAD_LEFT) . "-15",
                    'origen' => 'instruccion'
                ];
            }
        }

        return ['agregar' => $actividades, 'excluir' => [], 'modificar' => [], 'explicacion' => ''];
    }

    /**
     * Genera las actividades de Mantenimiento Periodico en el PTA
     */
    public function generarActividades(int $idCliente, int $anio, ?array $actividadesSeleccionadas = null): array
    {
        $creadas = 0;
        $existentes = 0;
        $errores = [];

        $db = \Config\Database::connect();

        $actividades = $actividadesSeleccionadas ?? self::ACTIVIDADES_MANTENIMIENTO;

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
                    'tipo_servicio' => 'Mantenimiento Periodico',
                    'phva_plandetrabajo' => $act['phva'] ?? 'HACER',
                    'numeral_plandetrabajo' => $act['numeral'] ?? '4.2.5',
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
     * Obtiene las actividades de Mantenimiento Periodico del PTA de un cliente
     */
    public function getActividadesCliente(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        return $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->groupStart()
                ->where('tipo_servicio', 'Mantenimiento Periodico')
                ->orLike('tipo_servicio', 'Mantenimiento', 'both')
                ->orLike('actividad_plandetrabajo', 'mantenimiento preventivo', 'both')
                ->orLike('actividad_plandetrabajo', 'mantenimiento correctivo', 'both')
                ->orLike('actividad_plandetrabajo', 'mantenimiento periodico', 'both')
                ->orLike('actividad_plandetrabajo', 'inspeccion de seguridad', 'both')
                ->orLike('actividad_plandetrabajo', 'fichas tecnicas', 'both')
                ->orLike('actividad_plandetrabajo', 'inventario de equipos', 'both')
                ->orLike('actividad_plandetrabajo', 'calibracion', 'both')
            ->groupEnd()
            ->orderBy('fecha_propuesta', 'ASC')
            ->get()
            ->getResultArray();
    }
}
