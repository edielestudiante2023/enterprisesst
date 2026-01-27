<?php

namespace App\Controllers;

use App\Services\CronogramaIAService;
use App\Services\PTAGeneratorService;
use App\Services\ProgramaCapacitacionService;
use App\Models\ClientModel;
use App\Models\ClienteContextoSSTModel;
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

        $contextoModel = new ClienteContextoSSTModel();
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
        $contextoModel = new ClienteContextoSSTModel();
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
        $contextoModel = new ClienteContextoSSTModel();
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
            $contextoModel = new ClienteContextoSSTModel();
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

        $contextoModel = new ClienteContextoSSTModel();
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
}
