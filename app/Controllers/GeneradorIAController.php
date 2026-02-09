<?php

namespace App\Controllers;

use App\Services\CronogramaIAService;
use App\Services\PTAGeneratorService;
use App\Services\ProgramaCapacitacionService;
use App\Models\ClientModel;
use App\Models\ClienteContextoSstModel;
use App\Models\IndicadorSSTModel;

/**
 * Controlador para acciones de generación con IA
 * Cronograma de Capacitaciones, PTA e Indicadores
 */
class GeneradorIAController extends BaseController
{
    protected ClientModel $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new ClientModel();
    }

    /**
     * Vista principal del generador IA para un cliente
     */
    public function index(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        // Obtener resúmenes
        $cronogramaService = new CronogramaIAService();
        $ptaService = new PTAGeneratorService();
        $indicadorModel = new IndicadorSSTModel();

        $anio = (int)date('Y');

        $data = [
            'titulo' => 'Generador IA - SG-SST',
            'cliente' => $cliente,
            'estandares' => $estandares,
            'anio' => $anio,
            'resumenCronograma' => $cronogramaService->getResumenCronograma($idCliente, $anio),
            'resumenPTA' => $ptaService->getResumenPTA($idCliente, $anio),
            'verificacionIndicadores' => $indicadorModel->verificarCumplimiento($idCliente)
        ];

        return view('generador_ia/index', $data);
    }

    /**
     * Preview del cronograma que se generaría
     */
    public function previewCronograma(int $idCliente)
    {
        $anio = $this->request->getGet('anio') ?? (int)date('Y');

        $service = new CronogramaIAService();
        $preview = $service->previewCronograma($idCliente, (int)$anio);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera el cronograma de capacitaciones
     */
    public function generarCronograma(int $idCliente)
    {
        $anio = $this->request->getPost('anio') ?? (int)date('Y');

        try {
            $service = new CronogramaIAService();
            $resultado = $service->generarCronograma($idCliente, (int)$anio);

            return $this->response->setJSON([
                'success' => true,
                'message' => "Cronograma generado: {$resultado['creadas']} capacitaciones creadas, {$resultado['existentes']} ya existían",
                'data' => $resultado
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar cronograma: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Preview del PTA que se generaría
     */
    public function previewPTA(int $idCliente)
    {
        $anio = $this->request->getGet('anio') ?? (int)date('Y');

        $service = new PTAGeneratorService();
        $preview = $service->previewPTA($idCliente, (int)$anio);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera el PTA desde el cronograma
     */
    public function generarPTADesdeCronograma(int $idCliente)
    {
        $anio = $this->request->getPost('anio') ?? (int)date('Y');
        // El tipo de servicio viene del módulo/documento que invoca la generación
        $tipoServicio = $this->request->getPost('tipo_servicio') ?? 'Programa de Capacitacion';

        try {
            $service = new PTAGeneratorService();
            $resultado = $service->generarDesdeCronograma($idCliente, (int)$anio, $tipoServicio);

            return $this->response->setJSON([
                'success' => true,
                'message' => "PTA actualizado: {$resultado['actividades_creadas']} actividades de capacitación agregadas",
                'data' => $resultado
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar PTA: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Genera el PTA completo (base + cronograma)
     */
    public function generarPTACompleto(int $idCliente)
    {
        $anio = $this->request->getPost('anio') ?? (int)date('Y');

        try {
            $service = new PTAGeneratorService();
            $resultado = $service->generarPTACompleto($idCliente, (int)$anio);

            return $this->response->setJSON([
                'success' => true,
                'message' => "PTA generado: {$resultado['total_creadas']} actividades creadas ({$resultado['actividades_base_creadas']} base + {$resultado['actividades_capacitacion_creadas']} capacitaciones)",
                'data' => $resultado
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar PTA: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Preview de indicadores sugeridos
     */
    public function previewIndicadores(int $idCliente)
    {
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $indicadorModel = new IndicadorSSTModel();
        $sugerencias = $indicadorModel->generarIndicadoresSugeridos($idCliente, $estandares);

        return $this->response->setJSON([
            'success' => true,
            'data' => $sugerencias
        ]);
    }

    /**
     * Genera indicadores sugeridos
     */
    public function generarIndicadores(int $idCliente)
    {
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        try {
            $indicadorModel = new IndicadorSSTModel();
            $creados = $indicadorModel->crearIndicadoresSugeridos($idCliente, $estandares);

            return $this->response->setJSON([
                'success' => true,
                'message' => "Se crearon {$creados} indicadores sugeridos",
                'creados' => $creados
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar indicadores: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Genera todo el flujo completo: Cronograma → PTA → Indicadores
     */
    public function generarFlujoCompleto(int $idCliente)
    {
        $anio = $this->request->getPost('anio') ?? (int)date('Y');

        $resultado = [
            'cronograma' => null,
            'pta' => null,
            'indicadores' => null,
            'errores' => []
        ];

        // 1. Generar cronograma
        try {
            $cronogramaService = new CronogramaIAService();
            $resultado['cronograma'] = $cronogramaService->generarCronograma($idCliente, (int)$anio);
        } catch (\Exception $e) {
            $resultado['errores'][] = 'Cronograma: ' . $e->getMessage();
        }

        // 2. Agregar capacitaciones del cronograma al PTA
        try {
            $ptaService = new PTAGeneratorService();
            $resultado['pta'] = $ptaService->generarDesdeCronograma($idCliente, (int)$anio);
        } catch (\Exception $e) {
            $resultado['errores'][] = 'PTA: ' . $e->getMessage();
        }

        // 3. Generar indicadores
        try {
            $contextoModel = new ClienteContextoSstModel();
            $contexto = $contextoModel->getByCliente($idCliente);
            $estandares = $contexto['estandares_aplicables'] ?? 7;

            $indicadorModel = new IndicadorSSTModel();
            $resultado['indicadores'] = [
                'creados' => $indicadorModel->crearIndicadoresSugeridos($idCliente, $estandares)
            ];
        } catch (\Exception $e) {
            $resultado['errores'][] = 'Indicadores: ' . $e->getMessage();
        }

        $exito = empty($resultado['errores']);

        return $this->response->setJSON([
            'success' => $exito,
            'message' => $exito
                ? 'Flujo completo generado exitosamente'
                : 'Se generó parcialmente con algunos errores',
            'data' => $resultado
        ]);
    }

    /**
     * Genera el documento del Programa de Capacitacion
     * Requiere: Cronograma, PTA e Indicadores previamente generados
     */
    public function generarProgramaCapacitacion(int $idCliente)
    {
        $anio = $this->request->getPost('anio') ?? (int)date('Y');

        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ]);
        }

        // Verificar que los 3 pasos previos esten completos
        $cronogramaService = new CronogramaIAService();
        $ptaService = new PTAGeneratorService();
        $indicadorModel = new IndicadorSSTModel();

        $resumenCronograma = $cronogramaService->getResumenCronograma($idCliente, $anio);
        $resumenPTA = $ptaService->getResumenPTA($idCliente, $anio);
        $verificacionIndicadores = $indicadorModel->verificarCumplimiento($idCliente);

        $errores = [];
        if ($resumenCronograma['total'] == 0) {
            $errores[] = 'No hay capacitaciones en el cronograma';
        }
        if ($resumenPTA['total'] == 0) {
            $errores[] = 'No hay actividades en el PTA';
        }
        if ($verificacionIndicadores['total'] == 0) {
            $errores[] = 'No hay indicadores configurados';
        }

        if (!empty($errores)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Faltan requisitos: ' . implode(', ', $errores)
            ]);
        }

        try {
            $service = new ProgramaCapacitacionService();
            $resultado = $service->generarDocumento($idCliente, $anio);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Programa de Capacitacion generado exitosamente',
                'data' => $resultado
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar documento: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Resumen del estado actual del cliente
     */
    public function resumen(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;

        $anio = (int)date('Y');

        $cronogramaService = new CronogramaIAService();
        $ptaService = new PTAGeneratorService();
        $indicadorModel = new IndicadorSSTModel();

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'cliente' => $cliente['nombre_cliente'],
                'estandares' => $estandares,
                'anio' => $anio,
                'cronograma' => $cronogramaService->getResumenCronograma($idCliente, $anio),
                'pta' => $ptaService->getResumenPTA($idCliente, $anio),
                'indicadores' => $indicadorModel->verificarCumplimiento($idCliente)
            ]
        ]);
    }

    // =========================================================================
    // PROGRAMA DE PROMOCIÓN Y PREVENCIÓN EN SALUD (3.1.2)
    // =========================================================================

    /**
     * Vista principal del generador de Actividades PyP Salud
     */
    public function pypSalud(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $pypService = new \App\Services\ActividadesPyPSaludService();
        $indicadorModel = new IndicadorSSTModel();

        $anio = (int)date('Y');

        $data = [
            'titulo' => 'Generador IA - Programa PyP Salud',
            'cliente' => $cliente,
            'contexto' => $contexto,
            'anio' => $anio,
            'resumenActividades' => $pypService->getResumenActividades($idCliente, $anio),
            'actividadesExistentes' => $pypService->getActividadesCliente($idCliente, $anio),
            'verificacionIndicadores' => $indicadorModel->verificarCumplimientoPyPSalud($idCliente)
        ];

        return view('generador_ia/pyp_salud', $data);
    }

    /**
     * Preview de las actividades de PyP Salud que se generarían
     */
    public function previewActividadesPyP(int $idCliente)
    {
        $anio = $this->request->getGet('anio') ?? (int)date('Y');
        $instrucciones = $this->request->getGet('instrucciones') ?? '';

        // Obtener contexto del cliente
        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $service = new \App\Services\ActividadesPyPSaludService();
        $preview = $service->previewActividades($idCliente, (int)$anio, $contexto, $instrucciones);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera las actividades de PyP Salud en el PTA
     * Acepta JSON con actividades seleccionadas y meses personalizados
     */
    public function generarActividadesPyP(int $idCliente)
    {
        // Intentar obtener JSON del body
        $json = $this->request->getJSON(true);

        $anio = $json['anio'] ?? $this->request->getPost('anio') ?? (int)date('Y');
        $actividadesSeleccionadas = $json['actividades'] ?? null;

        try {
            $service = new \App\Services\ActividadesPyPSaludService();
            $resultado = $service->generarActividades($idCliente, (int)$anio, $actividadesSeleccionadas);

            return $this->response->setJSON([
                'success' => true,
                'message' => "Actividades generadas: {$resultado['creadas']} nuevas, {$resultado['existentes']} ya existían",
                'data' => $resultado
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar actividades: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Resumen del estado de PyP Salud para un cliente
     */
    public function resumenPyPSalud(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $anio = (int)date('Y');

        $pypService = new \App\Services\ActividadesPyPSaludService();
        $indicadorModel = new IndicadorSSTModel();

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'cliente' => $cliente['nombre_cliente'],
                'anio' => $anio,
                'actividades' => $pypService->getResumenActividades($idCliente, $anio),
                'indicadores' => $indicadorModel->verificarCumplimientoPyPSalud($idCliente)
            ]
        ]);
    }

    /**
     * Vista principal de Indicadores PyP Salud
     */
    public function indicadoresPyPSalud(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->to('/clientes')->with('error', 'Cliente no encontrado');
        }

        $anio = (int)date('Y');

        // Obtener contexto del cliente
        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        // Servicio de indicadores PyP Salud
        $indicadoresService = new \App\Services\IndicadoresPyPSaludService();

        return view('generador_ia/indicadores_pyp_salud', [
            'cliente' => $cliente,
            'anio' => $anio,
            'contexto' => $contexto ?? [],
            'resumenIndicadores' => $indicadoresService->getResumenIndicadores($idCliente),
            'indicadoresExistentes' => $indicadoresService->getIndicadoresCliente($idCliente)
        ]);
    }

    /**
     * Preview de indicadores PyP Salud (AJAX)
     */
    public function previewIndicadoresPyP(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        // Obtener contexto del cliente
        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        $indicadoresService = new \App\Services\IndicadoresPyPSaludService();
        $preview = $indicadoresService->previewIndicadores($idCliente, $contexto);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera indicadores PyP Salud (AJAX POST)
     */
    public function generarIndicadoresPyP(int $idCliente)
    {
        $json = $this->request->getJSON(true);
        $indicadoresSeleccionados = $json['indicadores'] ?? null;

        try {
            $indicadoresService = new \App\Services\IndicadoresPyPSaludService();
            $resultado = $indicadoresService->generarIndicadores($idCliente, $indicadoresSeleccionados);

            return $this->response->setJSON([
                'success' => true,
                'message' => "Indicadores generados: {$resultado['creados']} nuevos, {$resultado['existentes']} ya existian",
                'data' => $resultado
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar indicadores: ' . $e->getMessage()
            ]);
        }
    }

    // =========================================================================
    // MÓDULO 2.2.1 - OBJETIVOS Y METAS DEL SG-SST
    // =========================================================================

    /**
     * Vista principal de Objetivos SG-SST (Parte 1)
     */
    public function objetivosSgsst(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $objetivosService = new \App\Services\ObjetivosSgsstService();
        $indicadoresService = new \App\Services\IndicadoresObjetivosService();

        $anio = (int)date('Y');
        $estandares = $contexto['estandares_aplicables'] ?? 60;

        // Verificar si existe política SST
        $db = \Config\Database::connect();
        $politicaSST = $db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'politica_sst_general')
            ->countAllResults() > 0;

        $data = [
            'titulo' => 'Generador IA - Objetivos SG-SST',
            'cliente' => $cliente,
            'contexto' => $contexto,
            'anio' => $anio,
            'politicaSST' => $politicaSST,
            'resumenObjetivos' => $objetivosService->getResumenObjetivos($idCliente, $anio),
            'objetivosExistentes' => $objetivosService->getObjetivosCliente($idCliente, $anio),
            'verificacionIndicadores' => $indicadoresService->getResumenIndicadores($idCliente)
        ];

        return view('generador_ia/objetivos_sgsst', $data);
    }

    /**
     * Preview de objetivos SG-SST (AJAX)
     */
    public function previewObjetivos(int $idCliente)
    {
        $anio = $this->request->getGet('anio') ?? (int)date('Y');
        $instrucciones = $this->request->getGet('instrucciones') ?? '';

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $service = new \App\Services\ObjetivosSgsstService();
        $preview = $service->previewObjetivos($idCliente, (int)$anio, $contexto, $instrucciones);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera objetivos SG-SST (AJAX POST)
     */
    public function generarObjetivos(int $idCliente)
    {
        $json = $this->request->getJSON(true);
        $anio = $json['anio'] ?? (int)date('Y');
        $objetivosSeleccionados = $json['objetivos'] ?? null;

        if (empty($objetivosSeleccionados)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibieron objetivos para generar'
            ]);
        }

        try {
            $service = new \App\Services\ObjetivosSgsstService();
            $resultado = $service->generarObjetivos($idCliente, (int)$anio, $objetivosSeleccionados);

            return $this->response->setJSON([
                'success' => true,
                'message' => "Objetivos generados: {$resultado['creados']} nuevos, {$resultado['existentes']} ya existian",
                'data' => $resultado
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar objetivos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Elimina un objetivo específico (DELETE)
     */
    public function eliminarObjetivo(int $idCliente, int $idPta)
    {
        try {
            $service = new \App\Services\ObjetivosSgsstService();
            $resultado = $service->eliminarObjetivo($idPta);

            return $this->response->setJSON([
                'success' => $resultado,
                'message' => $resultado ? 'Objetivo eliminado' : 'No se pudo eliminar'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Elimina todos los objetivos del cliente para un año (DELETE)
     */
    public function eliminarTodosObjetivos(int $idCliente)
    {
        $anio = $this->request->getGet('anio') ?? (int)date('Y');

        try {
            $db = \Config\Database::connect();
            $eliminados = $db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('tipo_servicio', 'Objetivos SG-SST')
                ->where('YEAR(fecha_propuesta)', $anio)
                ->delete();

            $affected = $db->affectedRows();

            return $this->response->setJSON([
                'success' => true,
                'message' => "{$affected} objetivos eliminados",
                'data' => ['eliminados' => $affected]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Regenera un objetivo especifico usando IA (POST)
     */
    public function regenerarObjetivo(int $idCliente)
    {
        $json = $this->request->getJSON(true);
        $objetivoActual = $json['objetivo_actual'] ?? null;
        $instrucciones = $json['instrucciones'] ?? '';
        $contextoGeneral = $json['contexto_general'] ?? '';

        if (empty($objetivoActual)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibio el objetivo a regenerar'
            ]);
        }

        try {
            // Obtener contexto del cliente
            $contextoModel = new \App\Models\ClienteContextoSstModel();
            $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

            $userPrompt = $this->construirPromptRegenerarObjetivo($objetivoActual, $instrucciones, $contextoGeneral, $contexto);
            $systemPrompt = 'Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia. Tu tarea es mejorar objetivos del SG-SST siguiendo las instrucciones del usuario. Responde SOLO en formato JSON valido sin markdown.';

            // Llamar a OpenAI usando curl
            $apiKey = env('OPENAI_API_KEY', '');
            if (empty($apiKey)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'API Key de OpenAI no configurada'
                ]);
            }

            $resultado = $this->llamarOpenAIRegenerarObjetivo($systemPrompt, $userPrompt, $apiKey);

            if (!$resultado['success']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $resultado['error'] ?? 'Error al llamar a la IA'
                ]);
            }

            $contenido = $resultado['contenido'];

            // Limpiar y parsear JSON
            $contenido = preg_replace('/```json\s*/', '', $contenido);
            $contenido = preg_replace('/```\s*/', '', $contenido);
            $contenido = trim($contenido);

            $objetivoMejorado = json_decode($contenido, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al procesar respuesta de IA: ' . json_last_error_msg()
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Objetivo regenerado con IA',
                'data' => $objetivoMejorado
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al regenerar objetivo: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Llama a la API de OpenAI usando curl para regenerar objetivo
     */
    protected function llamarOpenAIRegenerarObjetivo(string $systemPrompt, string $userPrompt, string $apiKey): array
    {
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

        return ['success' => false, 'error' => 'Respuesta inesperada de OpenAI'];
    }

    /**
     * Construye el prompt para regenerar un objetivo con IA
     */
    private function construirPromptRegenerarObjetivo(array $objetivoActual, string $instrucciones, string $contextoGeneral, ?array $contexto): string
    {
        $actividadEconomica = $contexto['actividad_economica_principal'] ?? 'No especificada';
        $nivelRiesgo = $contexto['nivel_riesgo_arl'] ?? 'No especificado';

        $prompt = "OBJETIVO ACTUAL A MEJORAR:
- Titulo: {$objetivoActual['objetivo']}
- Descripcion: {$objetivoActual['descripcion']}
- Meta: {$objetivoActual['meta']}
- Indicador sugerido: {$objetivoActual['indicador_sugerido']}
- Responsable: {$objetivoActual['responsable']}
- Ciclo PHVA: {$objetivoActual['phva']}

CONTEXTO DE LA EMPRESA:
- Actividad economica: {$actividadEconomica}
- Nivel de riesgo ARL: {$nivelRiesgo}";

        if (!empty($contextoGeneral)) {
            $prompt .= "\n- Contexto adicional: {$contextoGeneral}";
        }

        $prompt .= "\n\nINSTRUCCIONES DEL USUARIO:
{$instrucciones}

IMPORTANTE: Mejora el objetivo siguiendo las instrucciones. Mantén el formato del SG-SST colombiano. La meta debe ser SMART (especifica, medible, alcanzable, relevante, temporal).

Responde en formato JSON con esta estructura exacta:
{
    \"objetivo\": \"titulo mejorado\",
    \"descripcion\": \"descripcion mejorada\",
    \"meta\": \"meta cuantificable mejorada\",
    \"indicador_sugerido\": \"indicador mejorado\",
    \"responsable\": \"responsable apropiado\",
    \"phva\": \"PLANEAR|HACER|VERIFICAR|ACTUAR\"
}";

        return $prompt;
    }

    /**
     * Vista de Indicadores de Objetivos (Parte 2)
     */
    public function indicadoresObjetivos(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->to('/clientes')->with('error', 'Cliente no encontrado');
        }

        $anio = (int)date('Y');

        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        $indicadoresService = new \App\Services\IndicadoresObjetivosService();
        $estandares = $contexto['estandares_aplicables'] ?? 60;

        return view('generador_ia/indicadores_objetivos', [
            'cliente' => $cliente,
            'anio' => $anio,
            'contexto' => $contexto ?? [],
            'limiteIndicadores' => $indicadoresService->getLimiteIndicadores($estandares),
            'verificacionObjetivos' => $indicadoresService->verificarObjetivosPrevios($idCliente, $anio),
            'resumenIndicadores' => $indicadoresService->getResumenIndicadores($idCliente),
            'indicadoresExistentes' => $indicadoresService->getIndicadoresCliente($idCliente)
        ]);
    }

    /**
     * Preview de indicadores de objetivos (AJAX)
     */
    public function previewIndicadoresObjetivos(int $idCliente)
    {
        $anio = $this->request->getGet('anio') ?? (int)date('Y');

        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        $indicadoresService = new \App\Services\IndicadoresObjetivosService();
        $preview = $indicadoresService->previewIndicadores($idCliente, (int)$anio, $contexto);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera indicadores de objetivos (AJAX POST)
     */
    public function generarIndicadoresObjetivos(int $idCliente)
    {
        $json = $this->request->getJSON(true);
        $anio = $json['anio'] ?? (int)date('Y');
        $indicadoresSeleccionados = $json['indicadores'] ?? null;

        try {
            $indicadoresService = new \App\Services\IndicadoresObjetivosService();
            $resultado = $indicadoresService->generarIndicadores($idCliente, (int)$anio, $indicadoresSeleccionados);

            if (!empty($resultado['errores']) && $resultado['creados'] === 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $resultado['errores'][0] ?? 'Error al generar indicadores'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Indicadores generados: {$resultado['creados']} nuevos, {$resultado['existentes']} ya existian",
                'data' => $resultado
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar indicadores: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Regenera un indicador individual con IA (AJAX POST)
     */
    public function regenerarIndicador(int $idCliente)
    {
        $json = $this->request->getJSON(true);
        $indicadorActual = $json['indicador_actual'] ?? null;
        $instrucciones = $json['instrucciones'] ?? '';
        $tipoIndicador = $json['tipo_indicador'] ?? 'general';

        if (empty($indicadorActual)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibio el indicador a regenerar'
            ]);
        }

        try {
            // Obtener contexto del cliente
            $contextoModel = new \App\Models\ClienteContextoSstModel();
            $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

            $userPrompt = $this->construirPromptRegenerarIndicador($indicadorActual, $instrucciones, $contexto);
            $systemPrompt = 'Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia. Tu tarea es mejorar indicadores del SG-SST siguiendo las instrucciones del usuario. Responde SOLO en formato JSON valido sin markdown.';

            // Verificar API Key
            $apiKey = env('OPENAI_API_KEY', '');
            if (empty($apiKey)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'API Key de OpenAI no configurada'
                ]);
            }

            $resultado = $this->llamarOpenAIRegenerarObjetivo($systemPrompt, $userPrompt, $apiKey);

            if (!$resultado['success']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $resultado['error'] ?? 'Error al llamar a la IA'
                ]);
            }

            $contenido = $resultado['contenido'];

            // Limpiar y parsear JSON
            $contenido = preg_replace('/```json\s*/', '', $contenido);
            $contenido = preg_replace('/```\s*/', '', $contenido);
            $contenido = trim($contenido);

            $indicadorMejorado = json_decode($contenido, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al procesar respuesta de IA: ' . json_last_error_msg()
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Indicador regenerado con IA',
                'data' => $indicadorMejorado
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al regenerar indicador: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Construye el prompt para regenerar un indicador con IA
     */
    private function construirPromptRegenerarIndicador(array $indicadorActual, string $instrucciones, ?array $contexto): string
    {
        $actividadEconomica = $contexto['actividad_economica_principal'] ?? 'No especificada';
        $nivelRiesgo = $contexto['nivel_riesgo_arl'] ?? 'No especificado';

        $prompt = "INDICADOR ACTUAL A MEJORAR:
- Nombre: {$indicadorActual['nombre']}
- Formula: {$indicadorActual['formula']}
- Descripcion: {$indicadorActual['descripcion']}
- Meta: {$indicadorActual['meta']}
- Unidad de medida: {$indicadorActual['unidad']}
- Tipo: {$indicadorActual['tipo']}
- Periodicidad: {$indicadorActual['periodicidad']}";

        if (!empty($indicadorActual['objetivo_asociado'])) {
            $prompt .= "\n- Objetivo asociado: {$indicadorActual['objetivo_asociado']}";
        }

        $prompt .= "\n\nCONTEXTO DE LA EMPRESA:
- Actividad economica: {$actividadEconomica}
- Nivel de riesgo ARL: {$nivelRiesgo}

INSTRUCCIONES DEL USUARIO:
{$instrucciones}

IMPORTANTE: Mejora el indicador siguiendo las instrucciones. Mantén el formato del SG-SST colombiano segun Resolucion 0312/2019. La formula debe ser clara y matematicamente correcta.

Responde en formato JSON con esta estructura exacta:
{
    \"nombre\": \"nombre mejorado del indicador\",
    \"formula\": \"formula matematica mejorada\",
    \"descripcion\": \"descripcion mejorada\",
    \"meta\": \"valor de la meta (ej: 100, 95, 0.5)\",
    \"unidad\": \"unidad de medida (ej: %, dias, casos)\",
    \"tipo\": \"estructura|proceso|resultado\",
    \"periodicidad\": \"mensual|trimestral|semestral|anual\"
}";

        return $prompt;
    }

    // =========================================================================
    // MÓDULO 1.2.1 - PROGRAMA DE CAPACITACIÓN SST
    // =========================================================================

    /**
     * Vista principal del generador de Capacitaciones SST
     */
    public function capacitacionSst(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $capacitacionService = new \App\Services\CapacitacionSSTService();
        $indicadorModel = new IndicadorSSTModel();

        $anio = (int)date('Y');

        $data = [
            'titulo' => 'Generador IA - Capacitaciones SST',
            'cliente' => $cliente,
            'contexto' => $contexto,
            'anio' => $anio,
            'resumenCapacitaciones' => $capacitacionService->getResumenCapacitaciones($idCliente, $anio),
            'capacitacionesExistentes' => $capacitacionService->getCapacitacionesCliente($idCliente, $anio),
            'verificacionIndicadores' => $indicadorModel->verificarCumplimientoCapacitacion($idCliente)
        ];

        return view('generador_ia/capacitacion_sst', $data);
    }

    /**
     * Preview de las capacitaciones SST que se generarian (AJAX)
     */
    public function previewCapacitacionesSst(int $idCliente)
    {
        $anio = $this->request->getGet('anio') ?? (int)date('Y');
        $instrucciones = $this->request->getGet('instrucciones') ?? '';

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $service = new \App\Services\CapacitacionSSTService();
        $preview = $service->previewCapacitaciones($idCliente, (int)$anio, $contexto, $instrucciones);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera las capacitaciones SST en el cronograma (AJAX POST)
     */
    public function generarCapacitacionesSst(int $idCliente)
    {
        $json = $this->request->getJSON(true);

        $anio = $json['anio'] ?? $this->request->getPost('anio') ?? (int)date('Y');
        $capacitacionesSeleccionadas = $json['capacitaciones'] ?? null;

        try {
            $service = new \App\Services\CapacitacionSSTService();
            $resultado = $service->generarCapacitaciones($idCliente, (int)$anio, $capacitacionesSeleccionadas);

            return $this->response->setJSON([
                'success' => true,
                'message' => "Capacitaciones generadas: {$resultado['creadas']} nuevas, {$resultado['existentes']} ya existian",
                'data' => $resultado
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar capacitaciones: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Resumen del estado de Capacitaciones SST para un cliente (AJAX)
     */
    public function resumenCapacitacionSst(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $anio = (int)date('Y');

        $capacitacionService = new \App\Services\CapacitacionSSTService();
        $indicadorModel = new IndicadorSSTModel();

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'cliente' => $cliente['nombre_cliente'],
                'anio' => $anio,
                'capacitaciones' => $capacitacionService->getResumenCapacitaciones($idCliente, $anio),
                'indicadores' => $indicadorModel->verificarCumplimientoCapacitacion($idCliente)
            ]
        ]);
    }

    /**
     * Regenerar capacitacion individual con IA (AJAX POST)
     */
    public function regenerarCapacitacion(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $json = $this->request->getJSON(true);
        $capacitacionActual = $json['capacitacion'] ?? [];
        $instrucciones = $json['instrucciones'] ?? '';
        $contextoGeneral = $json['contexto_general'] ?? '';

        if (empty($instrucciones)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Debe proporcionar instrucciones']);
        }

        // Obtener contexto del cliente
        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        // Llamar a OpenAI para regenerar
        $capacitacionMejorada = $this->regenerarCapacitacionConIA(
            $capacitacionActual,
            $contexto,
            $instrucciones,
            $contextoGeneral
        );

        return $this->response->setJSON([
            'success' => true,
            'data' => $capacitacionMejorada
        ]);
    }

    /**
     * Regenera una capacitacion usando OpenAI
     */
    protected function regenerarCapacitacionConIA(array $capActual, ?array $contexto, string $instrucciones, string $contextoGeneral = ''): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            return $capActual; // Retornar sin cambios si no hay API key
        }

        $actividadEconomica = $contexto['actividad_economica_principal'] ?? 'No especificada';
        $nivelRiesgo = $contexto['nivel_riesgo_arl'] ?? 'No especificado';

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia.
Tu tarea es mejorar una capacitacion segun las instrucciones del consultor.

REGLAS:
1. Mantén la estructura de la capacitacion
2. Adapta el contenido al contexto de la empresa
3. El objetivo debe ser claro y medible
4. Responde SOLO en formato JSON valido

FORMATO DE RESPUESTA (JSON):
{
  \"nombre\": \"Nombre de la capacitacion\",
  \"objetivo\": \"Objetivo de la capacitacion\",
  \"horas\": 2,
  \"perfil_asistentes\": \"TODOS|MIEMBROS_COPASST|TRABAJADORES_RIESGOS_CRITICOS|BRIGADA_EMERGENCIAS\"
}";

        $userPrompt = "CONTEXTO DE LA EMPRESA:
- Actividad economica: {$actividadEconomica}
- Nivel de riesgo ARL: {$nivelRiesgo}

CAPACITACION ACTUAL:
" . json_encode($capActual, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "

INSTRUCCIONES DEL CONSULTOR:
\"{$instrucciones}\"

" . ($contextoGeneral ? "CONTEXTO ADICIONAL:\n{$contextoGeneral}\n\n" : "") . "

Mejora la capacitacion segun las instrucciones. Responde SOLO con el JSON.";

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
        curl_close($ch);

        if ($httpCode !== 200) {
            log_message('error', 'Error regenerando capacitacion: HTTP ' . $httpCode);
            return $capActual;
        }

        $result = json_decode($response, true);
        $contenido = $result['choices'][0]['message']['content'] ?? '';

        // Limpiar JSON
        $contenido = preg_replace('/```json\s*/', '', $contenido);
        $contenido = preg_replace('/```\s*/', '', $contenido);

        $respuesta = json_decode($contenido, true);
        if (!$respuesta) {
            log_message('warning', 'Respuesta IA no valida: ' . $contenido);
            return $capActual;
        }

        // Mezclar con datos originales
        return array_merge($capActual, $respuesta);
    }

    // =========================================================================
    // INDICADORES DE CAPACITACION SST (PARTE 2 DEL MODULO 1.2.1)
    // =========================================================================

    /**
     * Preview de indicadores de capacitacion (AJAX)
     */
    public function previewIndicadoresCapacitacion(int $idCliente)
    {
        $anio = $this->request->getGet('anio') ?? (int)date('Y');
        $instrucciones = $this->request->getGet('instrucciones') ?? '';

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $service = new \App\Services\IndicadoresCapacitacionService();
        $preview = $service->previewIndicadores($idCliente, (int)$anio, $contexto, $instrucciones);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera indicadores de capacitacion (AJAX POST)
     */
    public function generarIndicadoresCapacitacion(int $idCliente)
    {
        $json = $this->request->getJSON(true);
        $anio = $json['anio'] ?? (int)date('Y');
        $indicadoresSeleccionados = $json['indicadores'] ?? null;

        try {
            $service = new \App\Services\IndicadoresCapacitacionService();
            $resultado = $service->generarIndicadores($idCliente, (int)$anio, $indicadoresSeleccionados);

            if (!empty($resultado['errores']) && $resultado['creados'] === 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $resultado['errores'][0] ?? 'Error al generar indicadores'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Indicadores generados: {$resultado['creados']} nuevos, {$resultado['existentes']} ya existian",
                'data' => $resultado
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar indicadores: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Regenera un indicador de capacitacion con IA (AJAX POST)
     */
    public function regenerarIndicadorCapacitacion(int $idCliente)
    {
        $json = $this->request->getJSON(true);
        $indicadorActual = $json['indicador'] ?? null;
        $instrucciones = $json['instrucciones'] ?? '';

        if (empty($indicadorActual)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibio el indicador a regenerar'
            ]);
        }

        if (empty($instrucciones)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debe proporcionar instrucciones para la IA'
            ]);
        }

        try {
            // Obtener contexto del cliente
            $contextoModel = new ClienteContextoSstModel();
            $contexto = $contextoModel->getByCliente($idCliente);

            $indicadorMejorado = $this->regenerarIndicadorConIA($indicadorActual, $contexto, $instrucciones);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Indicador regenerado con IA',
                'data' => $indicadorMejorado
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al regenerar indicador: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Regenera un indicador usando OpenAI
     */
    protected function regenerarIndicadorConIA(array $indActual, ?array $contexto, string $instrucciones): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            return $indActual; // Retornar sin cambios si no hay API key
        }

        $actividadEconomica = $contexto['actividad_economica_principal'] ?? 'No especificada';
        $nivelRiesgo = $contexto['nivel_riesgo_arl'] ?? 'No especificado';

        $systemPrompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia.
Tu tarea es mejorar un indicador del programa de capacitacion segun las instrucciones del consultor.

REGLAS:
1. Manten la estructura del indicador
2. La formula debe ser matematicamente correcta
3. La meta debe ser alcanzable y medible
4. Responde SOLO en formato JSON valido

FORMATO DE RESPUESTA (JSON):
{
  \"nombre\": \"Nombre del indicador\",
  \"formula\": \"Formula matematica del indicador\",
  \"meta\": 100,
  \"unidad\": \"%\",
  \"periodicidad\": \"trimestral|mensual|semestral|anual\",
  \"descripcion\": \"Descripcion del indicador\",
  \"tipo\": \"proceso|resultado|estructura\"
}";

        $userPrompt = "CONTEXTO DE LA EMPRESA:
- Actividad economica: {$actividadEconomica}
- Nivel de riesgo ARL: {$nivelRiesgo}

INDICADOR ACTUAL:
" . json_encode($indActual, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "

INSTRUCCIONES DEL CONSULTOR:
\"{$instrucciones}\"

Mejora el indicador segun las instrucciones. Responde SOLO con el JSON.";

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
        curl_close($ch);

        if ($httpCode !== 200) {
            log_message('error', 'Error regenerando indicador: HTTP ' . $httpCode);
            return $indActual;
        }

        $result = json_decode($response, true);
        $contenido = $result['choices'][0]['message']['content'] ?? '';

        // Limpiar JSON
        $contenido = preg_replace('/```json\s*/', '', $contenido);
        $contenido = preg_replace('/```\s*/', '', $contenido);

        $respuesta = json_decode($contenido, true);
        if (!$respuesta) {
            log_message('warning', 'Respuesta IA no valida: ' . $contenido);
            return $indActual;
        }

        // Mezclar con datos originales
        return array_merge($indActual, $respuesta);
    }
}
