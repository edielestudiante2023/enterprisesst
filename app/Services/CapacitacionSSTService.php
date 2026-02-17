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
            return 4;
        } elseif ($estandares <= 21) {
            return 8;
        }
        return 12;
    }

    /**
     * Preview de las capacitaciones que se generarian.
     * La IA genera la lista completa desde cero basandose en el contexto del cliente.
     */
    public function previewCapacitaciones(int $idCliente, int $anio, ?array $contexto = null, string $instrucciones = ''): array
    {
        $resultado = $this->generarConIA($contexto, $anio, $instrucciones);

        return [
            'capacitaciones' => $resultado['capacitaciones'],
            'total' => count($resultado['capacitaciones']),
            'anio' => $anio,
            'contexto_aplicado' => $contexto ? true : false,
            'instrucciones_procesadas' => !empty($instrucciones),
            'explicacion_ia' => $resultado['explicacion'] ?? ''
        ];
    }

    /**
     * La IA genera la lista COMPLETA de capacitaciones desde cero,
     * basandose en el contexto real de la empresa.
     */
    protected function generarConIA(?array $contexto, int $anio, string $instrucciones = ''): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            throw new \RuntimeException('OPENAI_API_KEY no configurada. La generacion de capacitaciones requiere la API de OpenAI.');
        }

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        // Determinar limite segun estandares
        $estandares = (int)($contexto['estandares_aplicables'] ?? 60);
        $maxCapacitaciones = $this->getMinimoCapacitaciones($estandares);

        // Contexto completo de la empresa
        $contextoTexto = "CONTEXTO DE LA EMPRESA:\n";
        if ($contexto) {
            $contextoTexto .= "- Actividad economica: " . ($contexto['actividad_economica_principal'] ?? 'No especificada') . "\n";
            $contextoTexto .= "- Sector economico: " . ($contexto['sector_economico'] ?? 'No especificado') . "\n";
            $contextoTexto .= "- Nivel de riesgo ARL: " . ($contexto['nivel_riesgo_arl'] ?? 'No especificado') . "\n";
            $contextoTexto .= "- Total trabajadores: " . ($contexto['total_trabajadores'] ?? 'No especificado') . "\n";
            $contextoTexto .= "- Estandares aplicables: {$estandares}\n";
            if (!empty($contexto['peligros_identificados'])) {
                $peligros = json_decode($contexto['peligros_identificados'], true) ?? [];
                $contextoTexto .= "- Peligros identificados: " . implode(', ', $peligros) . "\n";
            }
            if (!empty($contexto['observaciones_contexto'])) {
                $contextoTexto .= "\nOBSERVACIONES Y CONTEXTO REAL DE LA EMPRESA:\n";
                $contextoTexto .= $contexto['observaciones_contexto'] . "\n";
            }
        }

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia, especializado en disenar programas de capacitacion pertinentes segun la Resolucion 0312 de 2019.

Tu tarea es GENERAR la lista completa de capacitaciones para el cronograma anual de una empresa, basandote en su contexto real.

ACTORES DEL SG-SST SUSCEPTIBLES DE CAPACITACION:
- TODOS: Poblacion general de trabajadores. Las capacitaciones para TODOS deben centrarse en los riesgos reales identificados en el contexto de la empresa, no en temas genericos
- COPASST_VIGIA: Miembros del COPASST o Vigia SST (funciones, investigacion AT, inspecciones)
- COMITE_CONVIVENCIA: Miembros del Comite de Convivencia Laboral (acoso laboral, resolucion conflictos)
- BRIGADA_EMERGENCIAS: Grupo de brigadistas (primeros auxilios, evacuacion, control incendios)
- OPERATIVOS: Trabajadores expuestos a riesgos criticos segun la operacion de la empresa

REGLAS OBLIGATORIAS:
1. Genera EXACTAMENTE {$maxCapacitaciones} capacitaciones ({$estandares} estandares aplicables)
2. DISTRIBUCION BALANCEADA entre los actores: no todo puede ser para TODOS. El programa debe incluir capacitaciones especificas para COPASST_VIGIA, COMITE_CONVIVENCIA, BRIGADA_EMERGENCIAS y OPERATIVOS segun corresponda
3. Cada capacitacion debe ser RELEVANTE y PERTINENTE para esta empresa especifica, no generica
4. Analiza la actividad economica, el sector, los peligros y las observaciones del consultor
5. Incluye UNA sola induccion en SST al inicio del ano (para TODOS). NO incluyas reinduccion
6. Las demas capacitaciones deben responder a los riesgos reales y al contexto operativo
7. Los objetivos deben ser especificos para el contexto de la empresa, no genericos
8. La duracion por defecto de cada capacitacion es 1 hora. Solo usa mas horas si el tema realmente lo justifica
9. Distribuye las capacitaciones en los 12 meses de forma logica
10. Si hay instrucciones adicionales del consultor, aplicalas con prioridad
11. Responde SOLO en formato JSON valido

FORMATO DE RESPUESTA (JSON):
{
  \"capacitaciones\": [
    {\"mes\": 1, \"nombre\": \"...\", \"objetivo\": \"...\", \"horas\": 1, \"perfil_asistentes\": \"TODOS\"},
    {\"mes\": 3, \"nombre\": \"...\", \"objetivo\": \"...\", \"horas\": 1, \"perfil_asistentes\": \"COPASST_VIGIA\"},
    {\"mes\": 5, \"nombre\": \"...\", \"objetivo\": \"...\", \"horas\": 1, \"perfil_asistentes\": \"BRIGADA_EMERGENCIAS\"}
  ],
  \"explicacion\": \"Explicacion de como se diseno el programa y la distribucion entre actores\"
}";

        $userPrompt = "ANO DEL CRONOGRAMA: {$anio}\n";
        $userPrompt .= "TOTAL DE CAPACITACIONES A GENERAR: {$maxCapacitaciones}\n\n";
        $userPrompt .= $contextoTexto . "\n";
        if (!empty($instrucciones)) {
            $userPrompt .= "INSTRUCCIONES ADICIONALES DEL CONSULTOR:\n\"{$instrucciones}\"\n\n";
        }
        $userPrompt .= "Genera las {$maxCapacitaciones} capacitaciones mas pertinentes para esta empresa.";

        $response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey);

        if (!$response['success']) {
            log_message('error', 'Error en IA Capacitaciones: ' . ($response['error'] ?? 'desconocido'));
            throw new \RuntimeException('Error al generar capacitaciones con IA: ' . ($response['error'] ?? 'desconocido'));
        }

        // Parsear respuesta
        $contenidoIA = $response['contenido'];
        $contenidoIA = preg_replace('/```json\s*/', '', $contenidoIA);
        $contenidoIA = preg_replace('/```\s*/', '', $contenidoIA);

        $respuesta = json_decode($contenidoIA, true);
        if (!$respuesta || empty($respuesta['capacitaciones'])) {
            log_message('warning', 'No se pudo parsear respuesta IA capacitaciones: ' . $contenidoIA);
            throw new \RuntimeException('La IA no genero una respuesta valida. Intente nuevamente.');
        }

        // Formatear capacitaciones
        $capacitaciones = [];
        foreach ($respuesta['capacitaciones'] as $cap) {
            $mes = (int)($cap['mes'] ?? 6);
            $capacitaciones[] = [
                'mes' => $meses[$mes] ?? 'Junio',
                'mes_num' => $mes,
                'nombre' => $cap['nombre'] ?? 'Capacitacion SST',
                'objetivo' => $cap['objetivo'] ?? '',
                'perfil_asistentes' => $cap['perfil_asistentes'] ?? 'TODOS',
                'horas' => (int)($cap['horas'] ?? 1),
                'fecha_programada' => "{$anio}-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-15",
                'origen' => 'ia',
                'generado_por_ia' => true
            ];
        }

        usort($capacitaciones, fn($a, $b) => $a['mes_num'] <=> $b['mes_num']);

        return [
            'capacitaciones' => $capacitaciones,
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
            'max_tokens' => 2500
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

        if (empty($capacitacionesSeleccionadas)) {
            throw new \RuntimeException('No se recibieron capacitaciones para generar. Ejecute primero la previsualizacion con IA.');
        }

        $capacitaciones = $capacitacionesSeleccionadas;

        foreach ($capacitaciones as $cap) {
            $mes = $cap['mes_num'] ?? $cap['mes'];
            $fechaProgramada = "{$anio}-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-15";

            // Verificar si ya existe similar en cronograma
            $existe = $this->cronogramaModel
                ->where('id_cliente', $idCliente)
                ->where('YEAR(fecha_programada)', $anio)
                ->where("id_capacitacion IN (SELECT id_capacitacion FROM capacitaciones_sst WHERE capacitacion LIKE '%" . $db->escapeLikeString(substr($cap['nombre'], 0, 30)) . "%')", null, false)
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
