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
            'verificacionIndicadores' => $indicadoresService->getResumenIndicadores($idCliente, $anio)
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
        $modo = $json['modo'] ?? 'mejorar'; // 'mejorar' o 'replantear'

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

            $userPrompt = $this->construirPromptRegenerarObjetivo($objetivoActual, $instrucciones, $contextoGeneral, $contexto, $idCliente, $modo);

            if ($modo === 'replantear') {
                $systemPrompt = 'Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia. Tu tarea es CREAR un objetivo COMPLETAMENTE NUEVO para el SG-SST de esta empresa. IGNORA el objetivo actual — genera uno totalmente diferente usando el contexto de la empresa. Si el usuario escribio algo, usalo como base del nuevo objetivo. Responde SOLO en formato JSON valido sin markdown.';
            } else {
                $systemPrompt = 'Eres un experto en Seguridad y Salud en el Trabajo (SST) de Colombia. Tu tarea es MEJORAR un objetivo existente del SG-SST siguiendo las instrucciones del usuario y el contexto de la empresa. El resultado DEBE ser notablemente diferente al original. Responde SOLO en formato JSON valido sin markdown.';
            }

            log_message('debug', "regenerarObjetivo: modo={$modo}, instrucciones=" . substr($instrucciones, 0, 100));

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
            'temperature' => 0.8,
            'max_tokens' => 1500
        ];

        log_message('debug', 'regenerarObjetivo: Llamando OpenAI modelo=' . $data['model']);

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
            log_message('error', 'regenerarObjetivo curl error: ' . $error);
            return ['success' => false, 'error' => "Error de conexion: {$error}"];
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $result['error']['message'] ?? 'Error HTTP ' . $httpCode;
            log_message('error', 'regenerarObjetivo OpenAI HTTP ' . $httpCode . ': ' . $errorMsg);
            return ['success' => false, 'error' => $errorMsg];
        }

        if (isset($result['choices'][0]['message']['content'])) {
            $contenido = trim($result['choices'][0]['message']['content']);
            log_message('debug', 'regenerarObjetivo respuesta OK: ' . substr($contenido, 0, 200));
            return [
                'success' => true,
                'contenido' => $contenido
            ];
        }

        log_message('error', 'regenerarObjetivo: respuesta inesperada de OpenAI');
        return ['success' => false, 'error' => 'Respuesta inesperada de OpenAI'];
    }

    /**
     * Construye el prompt para regenerar un objetivo con IA
     * Usa contexto completo del cliente via ObjetivosSgsstService
     */
    private function construirPromptRegenerarObjetivo(array $objetivoActual, string $instrucciones, string $contextoGeneral, ?array $contexto, int $idCliente, string $modo = 'mejorar'): string
    {
        $service = new \App\Services\ObjetivosSgsstService();
        $contextoCompleto = $service->construirContextoCompleto($contexto, $idCliente);

        $prompt = '';

        if ($modo === 'replantear') {
            // REPLANTEAR: lienzo en blanco, ignorar objetivo actual
            $prompt .= "MODO: REPLANTEAR — Crear un objetivo COMPLETAMENTE NUEVO desde cero.\n";
            $prompt .= "El objetivo actual sera DESCARTADO. NO lo uses como referencia.\n\n";
            $prompt .= $contextoCompleto;

            if (!empty($contextoGeneral)) {
                $prompt .= "\nINSTRUCCIONES GENERALES DEL CONSULTOR:\n{$contextoGeneral}\n";
            }

            if (!empty(trim($instrucciones))) {
                $prompt .= "\nEL USUARIO QUIERE ESTE OBJETIVO:\n{$instrucciones}\n\n";
                $prompt .= "Usa el texto del usuario como TITULO del nuevo objetivo. Genera descripcion, meta SMART, indicador y responsable coherentes con ese titulo y el contexto de la empresa.\n";
            } else {
                $prompt .= "\nGenera un objetivo NUEVO y DIFERENTE para el SG-SST de esta empresa basado en su contexto, peligros y observaciones. NO repitas temas de objetivos comunes — se creativo y especifico.\n";
            }
        } else {
            // MEJORAR: tomar el actual y mejorarlo con las instrucciones
            $prompt .= "MODO: MEJORAR — Tomar el objetivo actual y mejorarlo.\n\n";
            $prompt .= "OBJETIVO ACTUAL:\n";
            $prompt .= "- Titulo: {$objetivoActual['objetivo']}\n";
            $prompt .= "- Descripcion: {$objetivoActual['descripcion']}\n";
            $prompt .= "- Meta: {$objetivoActual['meta']}\n";
            $prompt .= "- Indicador: {$objetivoActual['indicador_sugerido']}\n";
            $prompt .= "- Responsable: {$objetivoActual['responsable']}\n";
            $prompt .= "- PHVA: {$objetivoActual['phva']}\n\n";

            $prompt .= $contextoCompleto;

            if (!empty($contextoGeneral)) {
                $prompt .= "\nINSTRUCCIONES GENERALES DEL CONSULTOR:\n{$contextoGeneral}\n";
            }

            $prompt .= "\nINSTRUCCIONES DE MEJORA:\n{$instrucciones}\n\n";
            $prompt .= "Mejora el objetivo siguiendo las instrucciones. El resultado DEBE ser notablemente diferente. Vincula a la actividad economica y peligros de la empresa.\n";
        }

        $prompt .= "\nLa meta debe ser SMART con plazo y porcentaje concreto.\n";
        $prompt .= "Responde SOLO en JSON sin markdown:\n";
        $prompt .= "{\"objetivo\":\"titulo\",\"descripcion\":\"desc\",\"meta\":\"meta\",\"indicador_sugerido\":\"indicador\",\"responsable\":\"cargo\",\"phva\":\"PLANEAR|HACER|VERIFICAR|ACTUAR\"}";

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
            'resumenIndicadores' => $indicadoresService->getResumenIndicadores($idCliente, $anio),
            'indicadoresExistentes' => $indicadoresService->getIndicadoresCliente($idCliente)
        ]);
    }

    /**
     * Preview de indicadores de objetivos (AJAX)
     */
    public function previewIndicadoresObjetivos(int $idCliente)
    {
        $anio = $this->request->getGet('anio') ?? (int)date('Y');
        $instrucciones = urldecode($this->request->getGet('instrucciones') ?? '');

        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        try {
            $indicadoresService = new \App\Services\IndicadoresObjetivosService();
            $preview = $indicadoresService->previewIndicadores($idCliente, (int)$anio, $contexto, $instrucciones);

            return $this->response->setJSON([
                'success' => true,
                'data' => $preview
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
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

        try {
            $service = new \App\Services\CapacitacionSSTService();
            $preview = $service->previewCapacitaciones($idCliente, (int)$anio, $contexto, $instrucciones);

            return $this->response->setJSON([
                'success' => true,
                'data' => $preview
            ]);
        } catch (\RuntimeException $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
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

        try {
            $service = new \App\Services\IndicadoresCapacitacionService();
            $preview = $service->previewIndicadores($idCliente, (int)$anio, $contexto, $instrucciones);

            return $this->response->setJSON([
                'success' => true,
                'data' => $preview
            ]);
        } catch (\RuntimeException $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
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

    // =========================================================================
    // MÓDULO 3.1.7 - ESTILOS DE VIDA SALUDABLE Y ENTORNOS SALUDABLES
    // =========================================================================

    /**
     * Vista principal de Estilos de Vida Saludable (Parte 1 - Actividades)
     */
    public function estilosVidaSaludable(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $estilosService = new \App\Services\ActividadesEstilosVidaService();

        $anio = (int)date('Y');

        $data = [
            'titulo' => 'Generador IA - Estilos de Vida Saludable',
            'cliente' => $cliente,
            'contexto' => $contexto,
            'anio' => $anio,
            'resumenActividades' => $estilosService->getResumenActividades($idCliente, $anio),
            'actividadesExistentes' => $estilosService->getActividadesCliente($idCliente, $anio),
        ];

        return view('generador_ia/estilos_vida_saludable', $data);
    }

    /**
     * Preview de las actividades de Estilos de Vida que se generarian (AJAX)
     */
    public function previewActividadesEstilosVida(int $idCliente)
    {
        $anio = $this->request->getGet('anio') ?? (int)date('Y');
        $instrucciones = $this->request->getGet('instrucciones') ?? '';

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $service = new \App\Services\ActividadesEstilosVidaService();
        $preview = $service->previewActividades($idCliente, (int)$anio, $contexto, $instrucciones);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera las actividades de Estilos de Vida en el PTA (AJAX POST)
     */
    public function generarActividadesEstilosVida(int $idCliente)
    {
        $json = $this->request->getJSON(true);

        $anio = $json['anio'] ?? $this->request->getPost('anio') ?? (int)date('Y');
        $actividadesSeleccionadas = $json['actividades'] ?? null;

        try {
            $service = new \App\Services\ActividadesEstilosVidaService();
            $resultado = $service->generarActividades($idCliente, (int)$anio, $actividadesSeleccionadas);

            return $this->response->setJSON([
                'success' => true,
                'message' => "Actividades generadas: {$resultado['creadas']} nuevas, {$resultado['existentes']} ya existian",
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
     * Resumen del estado de Estilos de Vida para un cliente (AJAX)
     */
    public function resumenEstilosVida(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $anio = (int)date('Y');

        $estilosService = new \App\Services\ActividadesEstilosVidaService();
        $indicadoresService = new \App\Services\IndicadoresEstilosVidaService();

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'cliente' => $cliente['nombre_cliente'],
                'anio' => $anio,
                'actividades' => $estilosService->getResumenActividades($idCliente, $anio),
                'indicadores' => $indicadoresService->getResumenIndicadores($idCliente)
            ]
        ]);
    }

    /**
     * Vista principal de Indicadores de Estilos de Vida (Parte 2)
     */
    public function indicadoresEstilosVida(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->to('/clientes')->with('error', 'Cliente no encontrado');
        }

        $anio = (int)date('Y');

        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        $indicadoresService = new \App\Services\IndicadoresEstilosVidaService();

        return view('generador_ia/indicadores_estilos_vida_saludable', [
            'cliente' => $cliente,
            'anio' => $anio,
            'contexto' => $contexto ?? [],
            'resumenIndicadores' => $indicadoresService->getResumenIndicadores($idCliente),
            'indicadoresExistentes' => $indicadoresService->getIndicadoresCliente($idCliente)
        ]);
    }

    /**
     * Preview de indicadores de Estilos de Vida (AJAX)
     */
    public function previewIndicadoresEstilosVida(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        $indicadoresService = new \App\Services\IndicadoresEstilosVidaService();
        $preview = $indicadoresService->previewIndicadores($idCliente, $contexto);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera indicadores de Estilos de Vida (AJAX POST)
     */
    public function generarIndicadoresEstilosVida(int $idCliente)
    {
        $json = $this->request->getJSON(true);
        $indicadoresSeleccionados = $json['indicadores'] ?? null;

        try {
            $indicadoresService = new \App\Services\IndicadoresEstilosVidaService();
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
    // MÓDULO 3.1.4 - EVALUACIONES MEDICAS OCUPACIONALES
    // =========================================================================

    /**
     * Vista principal de Evaluaciones Medicas Ocupacionales (Parte 1 - Actividades)
     */
    public function evaluacionesMedicasOcupacionales(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $emoService = new \App\Services\ActividadesEvaluacionesMedicasService();

        $anio = (int)date('Y');

        $data = [
            'titulo' => 'Generador IA - Evaluaciones Medicas Ocupacionales',
            'cliente' => $cliente,
            'contexto' => $contexto,
            'anio' => $anio,
            'resumenActividades' => $emoService->getResumenActividades($idCliente, $anio),
            'actividadesExistentes' => $emoService->getActividadesCliente($idCliente, $anio),
        ];

        return view('generador_ia/evaluaciones_medicas_ocupacionales', $data);
    }

    /**
     * Preview de las actividades de Evaluaciones Medicas que se generarian (AJAX)
     */
    public function previewActividadesEvaluacionesMedicas(int $idCliente)
    {
        $anio = $this->request->getGet('anio') ?? (int)date('Y');
        $instrucciones = $this->request->getGet('instrucciones') ?? '';

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $service = new \App\Services\ActividadesEvaluacionesMedicasService();
        $preview = $service->previewActividades($idCliente, (int)$anio, $contexto, $instrucciones);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera las actividades de Evaluaciones Medicas en el PTA (AJAX POST)
     */
    public function generarActividadesEvaluacionesMedicas(int $idCliente)
    {
        $json = $this->request->getJSON(true);

        $anio = $json['anio'] ?? $this->request->getPost('anio') ?? (int)date('Y');
        $actividadesSeleccionadas = $json['actividades'] ?? null;

        try {
            $service = new \App\Services\ActividadesEvaluacionesMedicasService();
            $resultado = $service->generarActividades($idCliente, (int)$anio, $actividadesSeleccionadas);

            return $this->response->setJSON([
                'success' => true,
                'message' => "Actividades generadas: {$resultado['creadas']} nuevas, {$resultado['existentes']} ya existian",
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
     * Resumen del estado de Evaluaciones Medicas para un cliente (AJAX)
     */
    public function resumenEvaluacionesMedicas(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $anio = (int)date('Y');

        $emoService = new \App\Services\ActividadesEvaluacionesMedicasService();
        $indicadoresService = new \App\Services\IndicadoresEvaluacionesMedicasService();

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'cliente' => $cliente['nombre_cliente'],
                'anio' => $anio,
                'actividades' => $emoService->getResumenActividades($idCliente, $anio),
                'indicadores' => $indicadoresService->getResumenIndicadores($idCliente)
            ]
        ]);
    }

    /**
     * Vista principal de Indicadores de Evaluaciones Medicas (Parte 2)
     */
    public function indicadoresEvaluacionesMedicas(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->to('/clientes')->with('error', 'Cliente no encontrado');
        }

        $anio = (int)date('Y');

        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        $indicadoresService = new \App\Services\IndicadoresEvaluacionesMedicasService();

        return view('generador_ia/indicadores_evaluaciones_medicas_ocupacionales', [
            'cliente' => $cliente,
            'anio' => $anio,
            'contexto' => $contexto ?? [],
            'resumenIndicadores' => $indicadoresService->getResumenIndicadores($idCliente),
            'indicadoresExistentes' => $indicadoresService->getIndicadoresCliente($idCliente)
        ]);
    }

    /**
     * Preview de indicadores de Evaluaciones Medicas (AJAX)
     */
    public function previewIndicadoresEvaluacionesMedicas(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        $indicadoresService = new \App\Services\IndicadoresEvaluacionesMedicasService();
        $preview = $indicadoresService->previewIndicadores($idCliente, $contexto);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera indicadores de Evaluaciones Medicas (AJAX POST)
     */
    public function generarIndicadoresEvaluacionesMedicas(int $idCliente)
    {
        $json = $this->request->getJSON(true);
        $indicadoresSeleccionados = $json['indicadores'] ?? null;

        try {
            $indicadoresService = new \App\Services\IndicadoresEvaluacionesMedicasService();
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
    // MÓDULO 4.2.5 - MANTENIMIENTO PERIODICO DE INSTALACIONES, EQUIPOS, MAQUINAS
    // =========================================================================

    /**
     * Vista principal de Mantenimiento Periodico (Parte 1 - Actividades)
     */
    public function mantenimientoPeriodico(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $mantenimientoService = new \App\Services\ActividadesMantenimientoPeriodicoService();

        $anio = (int)date('Y');

        $data = [
            'titulo' => 'Generador IA - Mantenimiento Periodico',
            'cliente' => $cliente,
            'contexto' => $contexto,
            'anio' => $anio,
            'resumenActividades' => $mantenimientoService->getResumenActividades($idCliente, $anio),
            'actividadesExistentes' => $mantenimientoService->getActividadesCliente($idCliente, $anio),
        ];

        return view('generador_ia/mantenimiento_periodico', $data);
    }

    /**
     * Preview de actividades de Mantenimiento Periodico (AJAX)
     */
    public function previewActividadesMantenimiento(int $idCliente)
    {
        $anio = $this->request->getGet('anio') ?? (int)date('Y');
        $instrucciones = $this->request->getGet('instrucciones') ?? '';

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $service = new \App\Services\ActividadesMantenimientoPeriodicoService();
        $preview = $service->previewActividades($idCliente, (int)$anio, $contexto, $instrucciones);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera actividades de Mantenimiento Periodico en el PTA (AJAX POST)
     */
    public function generarActividadesMantenimiento(int $idCliente)
    {
        $json = $this->request->getJSON(true);

        $anio = $json['anio'] ?? $this->request->getPost('anio') ?? (int)date('Y');
        $actividadesSeleccionadas = $json['actividades'] ?? null;

        try {
            $service = new \App\Services\ActividadesMantenimientoPeriodicoService();
            $resultado = $service->generarActividades($idCliente, (int)$anio, $actividadesSeleccionadas);

            return $this->response->setJSON([
                'success' => true,
                'message' => "Actividades generadas: {$resultado['creadas']} nuevas, {$resultado['existentes']} ya existian",
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
     * Resumen de Mantenimiento Periodico (AJAX)
     */
    public function resumenMantenimiento(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $anio = (int)date('Y');

        $actividadesService = new \App\Services\ActividadesMantenimientoPeriodicoService();
        $indicadoresService = new \App\Services\IndicadoresMantenimientoPeriodicoService();

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'cliente' => $cliente['nombre_cliente'],
                'anio' => $anio,
                'actividades' => $actividadesService->getResumenActividades($idCliente, $anio),
                'indicadores' => $indicadoresService->getResumenIndicadores($idCliente)
            ]
        ]);
    }

    /**
     * Vista de indicadores de Mantenimiento Periodico (Parte 2)
     */
    public function indicadoresMantenimientoPeriodico(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->to('/clientes')->with('error', 'Cliente no encontrado');
        }

        $anio = (int)date('Y');

        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        $indicadoresService = new \App\Services\IndicadoresMantenimientoPeriodicoService();

        return view('generador_ia/indicadores_mantenimiento_periodico', [
            'cliente' => $cliente,
            'anio' => $anio,
            'contexto' => $contexto ?? [],
            'resumenIndicadores' => $indicadoresService->getResumenIndicadores($idCliente),
            'indicadoresExistentes' => $indicadoresService->getIndicadoresCliente($idCliente)
        ]);
    }

    /**
     * Preview de indicadores de Mantenimiento Periodico (AJAX)
     */
    public function previewIndicadoresMantenimiento(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        $indicadoresService = new \App\Services\IndicadoresMantenimientoPeriodicoService();
        $preview = $indicadoresService->previewIndicadores($idCliente, $contexto);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera indicadores de Mantenimiento Periodico (AJAX POST)
     */
    public function generarIndicadoresMantenimiento(int $idCliente)
    {
        $json = $this->request->getJSON(true);
        $indicadoresSeleccionados = $json['indicadores'] ?? null;

        try {
            $indicadoresService = new \App\Services\IndicadoresMantenimientoPeriodicoService();
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
    // MÓDULO 4.2.3 - PVE RIESGO BIOMECÁNICO
    // =========================================================================

    /**
     * Vista principal de PVE Riesgo Biomecanico (Parte 1 - Actividades)
     */
    public function pveRiesgoBiomecanico(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $pveBioService = new \App\Services\ActividadesPveBiomecanicoService();

        $anio = (int)date('Y');

        $data = [
            'titulo' => 'Generador IA - PVE Riesgo Biomecánico',
            'cliente' => $cliente,
            'contexto' => $contexto,
            'anio' => $anio,
            'resumenActividades' => $pveBioService->getResumenActividades($idCliente, $anio),
            'actividadesExistentes' => $pveBioService->getActividadesCliente($idCliente, $anio),
        ];

        return view('generador_ia/pve_riesgo_biomecanico', $data);
    }

    /**
     * Preview de actividades PVE Biomecanico (AJAX)
     */
    public function previewActividadesPveBiomecanico(int $idCliente)
    {
        $anio = $this->request->getGet('anio') ?? (int)date('Y');
        $instrucciones = $this->request->getGet('instrucciones') ?? '';

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $service = new \App\Services\ActividadesPveBiomecanicoService();
        $preview = $service->previewActividades($idCliente, (int)$anio, $contexto, $instrucciones);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera actividades PVE Biomecanico en el PTA (AJAX POST)
     */
    public function generarActividadesPveBiomecanico(int $idCliente)
    {
        $json = $this->request->getJSON(true);

        $anio = $json['anio'] ?? $this->request->getPost('anio') ?? (int)date('Y');
        $actividadesSeleccionadas = $json['actividades'] ?? null;

        try {
            $service = new \App\Services\ActividadesPveBiomecanicoService();
            $resultado = $service->generarActividades($idCliente, (int)$anio, $actividadesSeleccionadas);

            return $this->response->setJSON([
                'success' => true,
                'message' => "Actividades generadas: {$resultado['creadas']} nuevas, {$resultado['existentes']} ya existian",
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
     * Resumen de PVE Biomecanico (AJAX)
     */
    public function resumenPveBiomecanico(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $anio = (int)date('Y');

        $actividadesService = new \App\Services\ActividadesPveBiomecanicoService();
        $indicadoresService = new \App\Services\IndicadoresPveBiomecanicoService();

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'cliente' => $cliente['nombre_cliente'],
                'anio' => $anio,
                'actividades' => $actividadesService->getResumenActividades($idCliente, $anio),
                'indicadores' => $indicadoresService->getResumenIndicadores($idCliente)
            ]
        ]);
    }

    /**
     * Vista de indicadores PVE Biomecanico (Parte 2)
     */
    public function indicadoresPveBiomecanico(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->to('/clientes')->with('error', 'Cliente no encontrado');
        }

        $anio = (int)date('Y');

        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        $indicadoresService = new \App\Services\IndicadoresPveBiomecanicoService();

        return view('generador_ia/indicadores_pve_biomecanico', [
            'cliente' => $cliente,
            'anio' => $anio,
            'contexto' => $contexto ?? [],
            'resumenIndicadores' => $indicadoresService->getResumenIndicadores($idCliente),
            'indicadoresExistentes' => $indicadoresService->getIndicadoresCliente($idCliente)
        ]);
    }

    /**
     * Preview de indicadores PVE Biomecanico (AJAX)
     */
    public function previewIndicadoresPveBiomecanico(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        $indicadoresService = new \App\Services\IndicadoresPveBiomecanicoService();
        $preview = $indicadoresService->previewIndicadores($idCliente, $contexto);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera indicadores PVE Biomecanico (AJAX POST)
     */
    public function generarIndicadoresPveBiomecanico(int $idCliente)
    {
        $json = $this->request->getJSON(true);
        $indicadoresSeleccionados = $json['indicadores'] ?? null;

        try {
            $indicadoresService = new \App\Services\IndicadoresPveBiomecanicoService();
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
    // MÓDULO 4.2.3 - PVE RIESGO PSICOSOCIAL
    // =========================================================================

    /**
     * Vista principal de PVE Riesgo Psicosocial (Parte 1 - Actividades)
     */
    public function pveRiesgoPsicosocial(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $pvePsiService = new \App\Services\ActividadesPvePsicosocialService();

        $anio = (int)date('Y');

        $data = [
            'titulo' => 'Generador IA - PVE Riesgo Psicosocial',
            'cliente' => $cliente,
            'contexto' => $contexto,
            'anio' => $anio,
            'resumenActividades' => $pvePsiService->getResumenActividades($idCliente, $anio),
            'actividadesExistentes' => $pvePsiService->getActividadesCliente($idCliente, $anio),
        ];

        return view('generador_ia/pve_riesgo_psicosocial', $data);
    }

    /**
     * Preview de actividades PVE Psicosocial (AJAX)
     */
    public function previewActividadesPvePsicosocial(int $idCliente)
    {
        $anio = $this->request->getGet('anio') ?? (int)date('Y');
        $instrucciones = $this->request->getGet('instrucciones') ?? '';

        $contextoModel = new ClienteContextoSstModel();
        $contexto = $contextoModel->getByCliente($idCliente);

        $service = new \App\Services\ActividadesPvePsicosocialService();
        $preview = $service->previewActividades($idCliente, (int)$anio, $contexto, $instrucciones);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera actividades PVE Psicosocial en el PTA (AJAX POST)
     */
    public function generarActividadesPvePsicosocial(int $idCliente)
    {
        $json = $this->request->getJSON(true);

        $anio = $json['anio'] ?? $this->request->getPost('anio') ?? (int)date('Y');
        $actividadesSeleccionadas = $json['actividades'] ?? null;

        try {
            $service = new \App\Services\ActividadesPvePsicosocialService();
            $resultado = $service->generarActividades($idCliente, (int)$anio, $actividadesSeleccionadas);

            return $this->response->setJSON([
                'success' => true,
                'message' => "Actividades generadas: {$resultado['creadas']} nuevas, {$resultado['existentes']} ya existian",
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
     * Resumen de PVE Psicosocial (AJAX)
     */
    public function resumenPvePsicosocial(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $anio = (int)date('Y');

        $actividadesService = new \App\Services\ActividadesPvePsicosocialService();
        $indicadoresService = new \App\Services\IndicadoresPvePsicosocialService();

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'cliente' => $cliente['nombre_cliente'],
                'anio' => $anio,
                'actividades' => $actividadesService->getResumenActividades($idCliente, $anio),
                'indicadores' => $indicadoresService->getResumenIndicadores($idCliente)
            ]
        ]);
    }

    /**
     * Vista de indicadores PVE Psicosocial (Parte 2)
     */
    public function indicadoresPvePsicosocial(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->to('/clientes')->with('error', 'Cliente no encontrado');
        }

        $anio = (int)date('Y');

        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        $indicadoresService = new \App\Services\IndicadoresPvePsicosocialService();

        return view('generador_ia/indicadores_pve_psicosocial', [
            'cliente' => $cliente,
            'anio' => $anio,
            'contexto' => $contexto ?? [],
            'resumenIndicadores' => $indicadoresService->getResumenIndicadores($idCliente),
            'indicadoresExistentes' => $indicadoresService->getIndicadoresCliente($idCliente)
        ]);
    }

    /**
     * Preview de indicadores PVE Psicosocial (AJAX)
     */
    public function previewIndicadoresPvePsicosocial(int $idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado']);
        }

        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();

        $indicadoresService = new \App\Services\IndicadoresPvePsicosocialService();
        $preview = $indicadoresService->previewIndicadores($idCliente, $contexto);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preview
        ]);
    }

    /**
     * Genera indicadores PVE Psicosocial (AJAX POST)
     */
    public function generarIndicadoresPvePsicosocial(int $idCliente)
    {
        $json = $this->request->getJSON(true);
        $indicadoresSeleccionados = $json['indicadores'] ?? null;

        try {
            $indicadoresService = new \App\Services\IndicadoresPvePsicosocialService();
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
}
