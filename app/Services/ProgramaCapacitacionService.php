<?php

namespace App\Services;

use App\Models\ClientModel;
use App\Models\ClienteContextoSstModel;
use App\Models\IndicadorSSTModel;
use App\Models\CronogcapacitacionModel;
use App\Models\ResponsableSSTModel;
use Config\Database;

/**
 * Servicio para generar el documento del Programa de Capacitacion
 * Utiliza IA con contexto completo del cliente para personalizar todas las secciones
 */
class ProgramaCapacitacionService
{
    protected $db;
    protected ClientModel $clienteModel;
    protected ClienteContextoSstModel $contextoModel;
    protected IndicadorSSTModel $indicadorModel;
    protected CronogcapacitacionModel $cronogramaModel;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->clienteModel = new ClientModel();
        $this->contextoModel = new ClienteContextoSstModel();
        $this->indicadorModel = new IndicadorSSTModel();
        $this->cronogramaModel = new CronogcapacitacionModel();
    }

    /**
     * Genera el documento del Programa de Capacitacion
     */
    public function generarDocumento(int $idCliente, int $anio): array
    {
        $cliente = $this->clienteModel->find($idCliente);
        $contexto = $this->contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $capacitaciones = $this->cronogramaModel
            ->select('tbl_cronog_capacitacion.*, capacitaciones_sst.capacitacion as tema_capacitacion, capacitaciones_sst.objetivo_capacitacion')
            ->join('capacitaciones_sst', 'capacitaciones_sst.id_capacitacion = tbl_cronog_capacitacion.id_capacitacion', 'left')
            ->where('tbl_cronog_capacitacion.id_cliente', $idCliente)
            ->where('YEAR(tbl_cronog_capacitacion.fecha_programada)', $anio)
            ->orderBy('tbl_cronog_capacitacion.fecha_programada', 'ASC')
            ->findAll();

        $indicadores = $this->indicadorModel->getByCliente($idCliente, true, 'capacitacion');

        $responsableModel = new ResponsableSSTModel();
        $responsables = $responsableModel->getByCliente($idCliente);
        $responsableSST = null;
        foreach ($responsables as $r) {
            if ($r['tipo_rol'] === 'responsable_sgsst' && $r['activo']) {
                $responsableSST = $r;
                break;
            }
        }

        $actividadesPTA = $this->db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->where("(
                actividad_plandetrabajo COLLATE utf8mb4_general_ci LIKE '%capacitacion%'
                OR actividad_plandetrabajo COLLATE utf8mb4_general_ci LIKE '%Induccion%'
                OR actividad_plandetrabajo COLLATE utf8mb4_general_ci LIKE '%formacion%'
            )", null, false)
            ->get()
            ->getResultArray();

        $contenido = $this->generarContenido($cliente, $contexto, $capacitaciones, $indicadores, $responsableSST, $anio, $estandares);

        $documentoId = $this->guardarDocumento($idCliente, $anio, $contenido);

        return [
            'documento_id' => $documentoId,
            'documento_url' => base_url("documentacion/editar/{$documentoId}"),
            'resumen' => [
                'capacitaciones' => count($capacitaciones),
                'actividades_pta' => count($actividadesPTA),
                'indicadores' => count($indicadores)
            ]
        ];
    }

    /**
     * Genera el contenido estructurado del documento usando IA para secciones de texto
     */
    protected function generarContenido(
        array $cliente,
        ?array $contexto,
        array $capacitaciones,
        array $indicadores,
        ?array $responsable,
        int $anio,
        int $estandares
    ): array {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $cronogramaPorMes = [];
        foreach ($capacitaciones as $cap) {
            $mes = !empty($cap['fecha_programada']) ? (int)date('n', strtotime($cap['fecha_programada'])) : 1;
            if (!isset($cronogramaPorMes[$mes])) {
                $cronogramaPorMes[$mes] = [];
            }
            $cronogramaPorMes[$mes][] = $cap;
        }

        // Generar secciones de texto con IA (una sola llamada para todas)
        $seccionesIA = $this->generarSeccionesConIA($cliente, $contexto, $capacitaciones, $responsable, $anio, $estandares);

        return [
            'titulo' => 'PROGRAMA DE CAPACITACION EN SEGURIDAD Y SALUD EN EL TRABAJO',
            'empresa' => [
                'nombre' => $cliente['nombre_cliente'],
                'nit' => $cliente['nit_cliente'],
                'direccion' => $cliente['direccion_cliente'] ?? '',
                'ciudad' => $cliente['ciudad_cliente'] ?? '',
                'actividad_economica' => $contexto['actividad_economica'] ?? '',
                'clase_riesgo' => $contexto['clase_riesgo'] ?? ''
            ],
            'vigencia' => $anio,
            'estandares_aplicables' => $estandares,

            'secciones' => [
                ['titulo' => '1. INTRODUCCION', 'contenido' => $seccionesIA['introduccion']],
                ['titulo' => '2. OBJETIVO GENERAL', 'contenido' => $seccionesIA['objetivo_general']],
                ['titulo' => '3. OBJETIVOS ESPECIFICOS', 'contenido' => $seccionesIA['objetivos_especificos']],
                ['titulo' => '4. ALCANCE', 'contenido' => $seccionesIA['alcance']],
                ['titulo' => '5. MARCO LEGAL', 'contenido' => $seccionesIA['marco_legal']],
                ['titulo' => '6. DEFINICIONES', 'contenido' => $seccionesIA['definiciones']],
                ['titulo' => '7. RESPONSABILIDADES', 'contenido' => $seccionesIA['responsabilidades']],
                ['titulo' => '8. METODOLOGIA', 'contenido' => $seccionesIA['metodologia']],
                [
                    'titulo' => '9. CRONOGRAMA DE CAPACITACIONES',
                    'tipo' => 'tabla',
                    'contenido' => $this->generarTablaCronograma($cronogramaPorMes, $meses, $anio)
                ],
                [
                    'titulo' => '10. INDICADORES',
                    'contenido' => $this->generarSeccionIndicadores($indicadores)
                ],
                ['titulo' => '11. RECURSOS', 'contenido' => $seccionesIA['recursos']],
                ['titulo' => '12. EVALUACION Y SEGUIMIENTO', 'contenido' => $seccionesIA['evaluacion']]
            ],

            'firma' => [
                'responsable' => $responsable ? $responsable['nombre_completo'] : '[RESPONSABLE SG-SST]',
                'cargo' => $responsable ? $responsable['cargo'] : '[CARGO]',
                'licencia' => $responsable ? ($responsable['licencia_sst_numero'] ?? '') : ''
            ],

            'fecha_generacion' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Genera todas las secciones de texto con UNA sola llamada a OpenAI
     * Usa contexto completo del cliente para personalizar cada seccion
     */
    protected function generarSeccionesConIA(
        array $cliente,
        ?array $contexto,
        array $capacitaciones,
        ?array $responsable,
        int $anio,
        int $estandares
    ): array {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            log_message('error', 'ProgramaCapacitacion: OPENAI_API_KEY no configurada');
            return $this->generarSeccionesFallback($cliente, $estandares, $responsable);
        }

        // Reutilizar construirContextoCompleto() de ObjetivosSgsstService
        $objetivosService = new \App\Services\ObjetivosSgsstService();
        $contextoTexto = $objetivosService->construirContextoCompleto($contexto, (int)$cliente['id_cliente']);

        $resumenCapacitaciones = $this->construirResumenCapacitaciones($capacitaciones);
        $systemPrompt = $this->construirSystemPrompt();
        $userPrompt = $this->construirUserPrompt($contextoTexto, $resumenCapacitaciones, $cliente, $responsable, $anio, $estandares);

        try {
            $respuesta = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey);
            if ($respuesta) {
                $secciones = $this->procesarRespuestaIA($respuesta);
                if ($secciones) {
                    log_message('info', 'ProgramaCapacitacion: Secciones generadas con IA para cliente ' . $cliente['id_cliente']);
                    return $secciones;
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'ProgramaCapacitacion: Error IA: ' . $e->getMessage());
        }

        log_message('warning', 'ProgramaCapacitacion: Usando fallback sin IA para cliente ' . $cliente['id_cliente']);
        return $this->generarSeccionesFallback($cliente, $estandares, $responsable);
    }

    /**
     * System prompt especializado para generar Programa de Capacitacion
     */
    protected function construirSystemPrompt(): string
    {
        return <<<'PROMPT'
Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia, especializado en diseñar Programas de Capacitacion conforme a la normatividad colombiana vigente.

Tu tarea es generar las secciones de texto del Programa de Capacitacion para una empresa especifica. Cada seccion debe ser PERSONALIZADA segun la actividad economica, peligros identificados, nivel de riesgo, observaciones del consultor y demas contexto proporcionado.

REGLAS:
1. NO uses texto generico que aplique a cualquier empresa. Cada seccion debe reflejar la realidad de la empresa.
2. Si hay peligros identificados, incorporalos en la introduccion, objetivos y metodologia.
3. Si hay observaciones del consultor, integra esa informacion en las secciones relevantes.
4. El marco legal debe incluir normas generales de SST MAS las especificas del sector economico de la empresa.
5. Las definiciones deben incluir terminos relevantes al sector y peligros especificos.
6. Las responsabilidades deben adaptarse al tamaño de la empresa y su estructura SST (COPASST vs Vigia).
7. La metodologia debe ser practica y adaptada al tipo y tamaño de empresa.
8. Los recursos deben ser realistas para el tamaño y tipo de empresa.
9. La evaluacion debe considerar indicadores relevantes para el contexto.
10. Usa lenguaje formal tecnico de SST colombiano. Escribe con tildes.
11. NO inventes datos. Usa solamente la informacion proporcionada.
12. La introduccion debe tener 2-3 parrafos sustanciales.
13. Los objetivos especificos deben ser minimo 4 y maximo 8, dependiendo del nivel de estandares.

FORMATO DE RESPUESTA:
Responde UNICAMENTE con un JSON valido (sin markdown, sin ```json```, sin comentarios), con estas 10 claves:

{
  "introduccion": "Texto de la introduccion (2-3 parrafos separados con \\n\\n)",
  "objetivo_general": "Un parrafo con el objetivo general personalizado",
  "objetivos_especificos": "Lista con guiones:\\n- Objetivo 1\\n- Objetivo 2\\n...",
  "alcance": "Texto del alcance (1-2 parrafos)",
  "marco_legal": "El presente programa se fundamenta en:\\n\\n- Norma: descripcion\\n- Norma: descripcion\\n...",
  "definiciones": "**Termino:** Definicion.\\n\\n**Termino2:** Definicion2.\\n...",
  "responsabilidades": "**Alta Direccion:**\\n- Item\\n...\\n\\n**Responsable del SG-SST (NOMBRE):**\\n- Item\\n...\\n\\n**COPASST/Vigia:**\\n- Item\\n...\\n\\n**Trabajadores:**\\n- Item\\n...",
  "metodologia": "Texto con subsecciones y listas de actividades concretas",
  "recursos": "**Recursos Humanos:**\\n- Item\\n...\\n\\n**Recursos Fisicos:**\\n- Item\\n...\\n\\n**Recursos Financieros:**\\n- Item\\n...",
  "evaluacion": "Texto de evaluacion y seguimiento con criterios y frecuencia"
}
PROMPT;
    }

    /**
     * Construye el user prompt con contexto completo del cliente
     */
    protected function construirUserPrompt(
        string $contextoTexto,
        string $resumenCapacitaciones,
        array $cliente,
        ?array $responsable,
        int $anio,
        int $estandares
    ): string {
        $nivel = $estandares <= 7 ? 'basico (hasta 10 trabajadores, riesgo I, II o III)' :
                ($estandares <= 21 ? 'intermedio (11 a 50 trabajadores, riesgo I, II o III)' :
                'avanzado (mas de 50 trabajadores o riesgo IV y V)');

        $organo = $estandares <= 10 ? 'Vigia de SST' : 'COPASST';
        $nombreResponsable = $responsable ? $responsable['nombre_completo'] : 'el Responsable del SG-SST';
        $cargoResponsable = $responsable ? ($responsable['cargo'] ?? 'Responsable SG-SST') : 'Responsable SG-SST';

        $prompt = "AÑO DE VIGENCIA: {$anio}\n";
        $prompt .= "NIVEL DE ESTANDARES: {$nivel} ({$estandares} estandares - Resolucion 0312 de 2019)\n";
        $prompt .= "ORGANO DE PARTICIPACION: {$organo}\n";
        $prompt .= "RESPONSABLE SG-SST: {$nombreResponsable} ({$cargoResponsable})\n\n";
        $prompt .= $contextoTexto;

        if (!empty($resumenCapacitaciones)) {
            $prompt .= "\n\nCAPACITACIONES PROGRAMADAS EN EL CRONOGRAMA:\n" . $resumenCapacitaciones;
        }

        $prompt .= "\n\nGenera las 10 secciones del Programa de Capacitacion personalizadas para esta empresa.";

        return $prompt;
    }

    /**
     * Construye resumen de capacitaciones programadas para incluir en el prompt
     */
    protected function construirResumenCapacitaciones(array $capacitaciones): string
    {
        if (empty($capacitaciones)) {
            return 'No hay capacitaciones programadas aun.';
        }

        $resumen = '';
        foreach ($capacitaciones as $cap) {
            $tema = $cap['tema_capacitacion'] ?? $cap['capacitacion'] ?? 'Sin definir';
            $fecha = $cap['fecha_programada'] ?? '';
            $resumen .= "- {$tema}";
            if (!empty($fecha)) {
                $resumen .= " (programada: {$fecha})";
            }
            $resumen .= "\n";
        }

        return $resumen;
    }

    /**
     * Llama a la API de OpenAI con una sola peticion para todas las secciones
     */
    protected function llamarOpenAI(string $systemPrompt, string $userPrompt, string $apiKey): ?string
    {
        $data = [
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 6000,
            'response_format' => ['type' => 'json_object']
        ];

        log_message('info', 'ProgramaCapacitacion: Llamando OpenAI para generar secciones del documento');

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_TIMEOUT => 90,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            log_message('error', 'ProgramaCapacitacion: cURL error: ' . $curlError);
            return null;
        }

        if ($httpCode !== 200) {
            log_message('error', 'ProgramaCapacitacion: HTTP ' . $httpCode . ' - ' . substr($response, 0, 500));
            return null;
        }

        $decoded = json_decode($response, true);
        $content = $decoded['choices'][0]['message']['content'] ?? null;

        if ($content) {
            $tokens = $decoded['usage']['total_tokens'] ?? 0;
            log_message('info', "ProgramaCapacitacion: Respuesta recibida ({$tokens} tokens)");
        }

        return $content;
    }

    /**
     * Procesa la respuesta JSON de OpenAI y valida que todas las secciones esten presentes
     */
    protected function procesarRespuestaIA(string $respuesta): ?array
    {
        $data = json_decode($respuesta, true);
        if (!$data) {
            log_message('error', 'ProgramaCapacitacion: JSON invalido de OpenAI: ' . substr($respuesta, 0, 200));
            return null;
        }

        $seccionesRequeridas = [
            'introduccion', 'objetivo_general', 'objetivos_especificos',
            'alcance', 'marco_legal', 'definiciones', 'responsabilidades',
            'metodologia', 'recursos', 'evaluacion'
        ];

        foreach ($seccionesRequeridas as $seccion) {
            if (empty($data[$seccion])) {
                log_message('error', "ProgramaCapacitacion: Seccion '{$seccion}' vacia o faltante en respuesta IA");
                return null;
            }
        }

        return $data;
    }

    /**
     * Genera secciones basicas de fallback cuando la IA no esta disponible
     */
    protected function generarSeccionesFallback(array $cliente, int $estandares, ?array $responsable): array
    {
        $nombre = $cliente['nombre_cliente'];
        $nivel = $estandares <= 7 ? 'basico (hasta 10 trabajadores, riesgo I, II o III)' :
                ($estandares <= 21 ? 'intermedio (11 a 50 trabajadores, riesgo I, II o III)' :
                'avanzado (mas de 50 trabajadores o riesgo IV y V)');
        $organo = $estandares <= 10 ? 'Vigia de SST' : 'COPASST';
        $nombreResponsable = $responsable ? $responsable['nombre_completo'] : 'el Responsable del SG-SST';

        return [
            'introduccion' => "{$nombre} en cumplimiento de la normatividad legal vigente en materia de Seguridad y Salud en el Trabajo, especificamente la Resolucion 0312 de 2019 que establece los Estandares Minimos del Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST), ha desarrollado el presente Programa de Capacitacion.\n\nLa empresa aplica los estandares de nivel {$nivel}, lo cual determina los requisitos minimos de capacitacion que deben cumplirse.\n\nLa capacitacion es un elemento fundamental del SG-SST que permite a los trabajadores conocer los peligros y riesgos asociados a su labor, asi como las medidas de prevencion y control para evitar accidentes de trabajo y enfermedades laborales.",

            'objetivo_general' => "Desarrollar competencias en Seguridad y Salud en el Trabajo en todos los niveles de {$nombre}, mediante la ejecucion de actividades de formacion y capacitacion que permitan la prevencion de accidentes de trabajo y enfermedades laborales, cumpliendo con los requisitos legales establecidos en la Resolucion 0312 de 2019.",

            'objetivos_especificos' => "- Realizar induccion y reinduccion en SST a todos los trabajadores\n- Capacitar a los trabajadores sobre los peligros y riesgos asociados a sus actividades\n- Formar a los integrantes del {$organo} en sus funciones y responsabilidades\n- Entrenar a los brigadistas de emergencias en prevencion y atencion de situaciones de emergencia",

            'alcance' => "Este programa aplica a todos los trabajadores de {$nombre}, incluyendo trabajadores directos, contratistas, subcontratistas y visitantes que realicen actividades en las instalaciones de la empresa.",

            'marco_legal' => "El presente programa se fundamenta en la siguiente normatividad:\n\n- Ley 9 de 1979: Codigo Sanitario Nacional\n- Resolucion 2400 de 1979: Disposiciones sobre vivienda, higiene y seguridad en los establecimientos de trabajo\n- Decreto 1295 de 1994: Organizacion y administracion del Sistema General de Riesgos Profesionales\n- Ley 1562 de 2012: Sistema de Gestion de Seguridad y Salud en el Trabajo\n- Decreto 1072 de 2015: Decreto Unico Reglamentario del Sector Trabajo (Capitulo 6)\n- Resolucion 0312 de 2019: Estandares Minimos del SG-SST",

            'definiciones' => "**Capacitacion:** Proceso mediante el cual se desarrollan competencias, habilidades y destrezas en los trabajadores.\n\n**Induccion:** Capacitacion inicial que recibe el trabajador al ingresar a la empresa sobre aspectos generales y especificos de SST.\n\n**Reinduccion:** Capacitacion periodica para actualizar conocimientos y reforzar conceptos de SST.\n\n**Entrenamiento:** Proceso de aprendizaje practico que permite desarrollar habilidades especificas.\n\n**Competencia:** Capacidad demostrada para aplicar conocimientos y habilidades.",

            'responsabilidades' => "**Alta Direccion:**\n- Asignar los recursos necesarios para la ejecucion del programa\n- Garantizar la participacion de los trabajadores en las capacitaciones\n\n**Responsable del SG-SST ({$nombreResponsable}):**\n- Planificar y coordinar las actividades de capacitacion\n- Realizar seguimiento al cumplimiento del cronograma\n- Evaluar la efectividad de las capacitaciones\n- Mantener los registros de asistencia y evaluacion\n\n**{$organo}:**\n- Participar en las actividades de capacitacion\n- Proponer temas de capacitacion segun las necesidades identificadas\n- Verificar el cumplimiento del programa\n\n**Trabajadores:**\n- Asistir a las capacitaciones programadas\n- Aplicar los conocimientos adquiridos en su labor diaria\n- Participar activamente en las actividades de formacion",

            'metodologia' => "Las capacitaciones se desarrollaran utilizando las siguientes metodologias:\n\n**Capacitaciones Teoricas:**\n- Presentaciones interactivas\n- Material audiovisual\n- Documentos de apoyo\n\n**Capacitaciones Practicas:**\n- Talleres demostrativos\n- Simulacros\n- Ejercicios practicos en campo\n\n**Evaluacion:**\n- Evaluacion escrita al finalizar cada capacitacion\n- Evaluacion practica cuando aplique\n- Retroalimentacion individual",

            'recursos' => "Para la ejecucion del programa de capacitacion se requieren los siguientes recursos:\n\n**Recursos Humanos:**\n- Responsable del SG-SST\n- Capacitadores internos y/o externos\n- ARL (Administradora de Riesgos Laborales)\n\n**Recursos Fisicos:**\n- Sala de capacitaciones o espacio adecuado\n- Equipos audiovisuales (computador, proyector)\n- Material didactico\n\n**Recursos Financieros:**\n- Presupuesto asignado por la alta direccion para actividades de capacitacion",

            'evaluacion' => "El programa sera evaluado trimestralmente considerando:\n\n- Cumplimiento del cronograma de capacitaciones\n- Cobertura de trabajadores capacitados\n- Resultados de las evaluaciones aplicadas\n- Aplicacion de conocimientos en el trabajo\n\nLos resultados de la evaluacion seran presentados en las reuniones del {$organo} y serviran para realizar ajustes al programa segun las necesidades identificadas."
        ];
    }

    /**
     * Genera la tabla del cronograma de capacitaciones (datos dinamicos de BD)
     */
    protected function generarTablaCronograma(array $cronogramaPorMes, array $meses, int $anio): array
    {
        $filas = [];

        for ($mes = 1; $mes <= 12; $mes++) {
            if (isset($cronogramaPorMes[$mes])) {
                foreach ($cronogramaPorMes[$mes] as $cap) {
                    $filas[] = [
                        'mes' => $meses[$mes],
                        'tema' => $cap['tema_capacitacion'] ?? $cap['capacitacion'] ?? 'Sin definir',
                        'duracion' => ($cap['horas_de_duracion_de_la_capacitacion'] ?? 1) . ' hora(s)',
                        'dirigido_a' => $cap['perfil_de_asistentes'] ?? 'Todos los trabajadores',
                        'responsable' => $cap['nombre_del_capacitador'] ?? 'Responsable SG-SST',
                        'estado' => $cap['estado'] ?? 'Programada'
                    ];
                }
            }
        }

        return [
            'encabezados' => ['Mes', 'Tema', 'Duracion', 'Dirigido a', 'Responsable', 'Estado'],
            'filas' => $filas,
            'anio' => $anio
        ];
    }

    /**
     * Genera la seccion de indicadores (datos dinamicos de BD)
     */
    protected function generarSeccionIndicadores(array $indicadores): string
    {
        if (empty($indicadores)) {
            return "Los indicadores del programa de capacitacion seran definidos por el responsable del SG-SST.";
        }

        $contenido = "El cumplimiento del programa se medira a traves de los siguientes indicadores:\n\n";

        foreach ($indicadores as $ind) {
            $contenido .= "**{$ind['nombre_indicador']}**\n";
            if (!empty($ind['formula'])) {
                $contenido .= "- Formula: {$ind['formula']}\n";
            }
            if (!empty($ind['meta'])) {
                $contenido .= "- Meta: {$ind['meta']}{$ind['unidad_medida']}\n";
            }
            $contenido .= "- Periodicidad: " . ucfirst($ind['periodicidad'] ?? 'trimestral') . "\n\n";
        }

        return $contenido;
    }

    /**
     * Guarda el documento generado en la base de datos
     */
    protected function guardarDocumento(int $idCliente, int $anio, array $contenido): int
    {
        $existeTabla = $this->db->tableExists('tbl_documentos_sst');

        if (!$existeTabla) {
            $this->crearTablaDocumentos();
        }

        $documentoExistente = $this->db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'programa_capacitacion')
            ->where('anio', $anio)
            ->get()
            ->getRowArray();

        $datos = [
            'id_cliente' => $idCliente,
            'tipo_documento' => 'programa_capacitacion',
            'titulo' => 'Programa de Capacitacion en SST',
            'anio' => $anio,
            'contenido' => json_encode($contenido, JSON_UNESCAPED_UNICODE),
            'version' => $documentoExistente ? ((int)$documentoExistente['version'] + 1) : 1,
            'estado' => 'generado',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($documentoExistente) {
            $this->db->table('tbl_documentos_sst')
                ->where('id_documento', $documentoExistente['id_documento'])
                ->update($datos);
            return (int)$documentoExistente['id_documento'];
        } else {
            $datos['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('tbl_documentos_sst')->insert($datos);
            return $this->db->insertID();
        }
    }

    /**
     * Crea la tabla de documentos SST si no existe
     */
    protected function crearTablaDocumentos(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `tbl_documentos_sst` (
            `id_documento` INT(11) NOT NULL AUTO_INCREMENT,
            `id_cliente` INT(11) NOT NULL,
            `tipo_documento` VARCHAR(100) NOT NULL,
            `titulo` VARCHAR(255) NOT NULL,
            `anio` INT(4) NOT NULL,
            `contenido` LONGTEXT NULL,
            `version` INT(11) DEFAULT 1,
            `estado` ENUM('borrador', 'generado', 'aprobado', 'obsoleto') DEFAULT 'borrador',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created_by` INT(11) NULL,
            `updated_by` INT(11) NULL,
            PRIMARY KEY (`id_documento`),
            KEY `idx_cliente` (`id_cliente`),
            KEY `idx_tipo` (`tipo_documento`),
            KEY `idx_anio` (`anio`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->query($sql);
    }
}
