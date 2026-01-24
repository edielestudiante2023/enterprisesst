<?php

namespace App\Controllers;

use App\Models\ClienteEstandaresModel;
use App\Models\ClienteContextoSstModel;
use App\Models\ClienteTransicionesModel;
use App\Models\EstandarMinimoModel;
use App\Models\ClientModel;
use App\Services\EstandaresSetupService;
use CodeIgniter\Controller;

class EstandaresClienteController extends Controller
{
    protected $clienteEstandaresModel;
    protected $contextoModel;
    protected $transicionesModel;
    protected $estandarModel;
    protected $clienteModel;
    protected $setupService;

    public function __construct()
    {
        $this->clienteEstandaresModel = new ClienteEstandaresModel();
        $this->contextoModel = new ClienteContextoSstModel();
        $this->transicionesModel = new ClienteTransicionesModel();
        $this->estandarModel = new EstandarMinimoModel();
        $this->clienteModel = new ClientModel();
        $this->setupService = new EstandaresSetupService();
    }

    /**
     * Dashboard de cumplimiento de estándares
     * Auto-configura tablas y datos si es necesario
     */
    public function index($idCliente)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Auto-configurar base de datos si es necesario
        $setupResult = $this->setupService->verificarYConfigurar();

        $cliente = $this->clienteModel->find($idCliente);

        if (!$cliente) {
            return redirect()->to('/estandares')->with('error', 'Cliente no encontrado');
        }

        // Obtener contexto SST del cliente
        $contexto = $this->contextoModel->getByCliente($idCliente);

        // Determinar nivel de estándares (por defecto 60)
        $nivelEstandares = $contexto['estandares_aplicables'] ?? 60;

        // Obtener estándares del cliente (si ya fueron inicializados)
        $estandares = $this->clienteEstandaresModel->getByClienteGroupedPHVA($idCliente);
        $resumen = $this->clienteEstandaresModel->getResumenCumplimiento($idCliente);
        $cumplimientoPonderado = $this->getCumplimientoPonderadoSimple($idCliente);
        $transicionesPendientes = $this->transicionesModel->getPendientes($idCliente);

        return view('estandares/dashboard', [
            'cliente' => $cliente,
            'contexto' => $contexto,
            'estandares' => $estandares,
            'resumen' => $resumen,
            'cumplimientoPonderado' => $cumplimientoPonderado,
            'transicionesPendientes' => $transicionesPendientes,
            'setupResult' => $setupResult
        ]);
    }

    /**
     * Calcula cumplimiento ponderado sin depender de stored procedure
     */
    protected function getCumplimientoPonderadoSimple(int $idCliente): float
    {
        $db = \Config\Database::connect();

        // Calcular usando query directo en lugar de SP
        $query = $db->query("
            SELECT
                SUM(CASE WHEN ce.estado = 'cumple' THEN em.peso_porcentual ELSE 0 END) as peso_cumplido,
                SUM(CASE WHEN ce.estado != 'no_aplica' THEN em.peso_porcentual ELSE 0 END) as peso_total
            FROM tbl_cliente_estandares ce
            JOIN tbl_estandares_minimos em ON em.id_estandar = ce.id_estandar
            WHERE ce.id_cliente = ?
        ", [$idCliente]);

        $result = $query->getRowArray();

        if ($result && $result['peso_total'] > 0) {
            return round(($result['peso_cumplido'] / $result['peso_total']) * 100, 2);
        }

        return 0.0;
    }

    /**
     * Detalle de un estándar específico
     */
    public function detalle($idCliente, $idEstandar)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $cliente = $this->clienteModel->find($idCliente);
        $estandar = $this->estandarModel->find($idEstandar);

        $clienteEstandar = $this->clienteEstandaresModel
            ->where('id_cliente', $idCliente)
            ->where('id_estandar', $idEstandar)
            ->first();

        // Obtener documentos relacionados con este estándar
        $documentoModel = new \App\Models\DocDocumentoModel();
        $documentos = $documentoModel
            ->select('tbl_doc_documentos.*')
            ->join('tbl_doc_estandar_documentos', 'tbl_doc_estandar_documentos.id_documento = tbl_doc_documentos.id_documento')
            ->where('tbl_doc_estandar_documentos.id_estandar', $idEstandar)
            ->where('tbl_doc_documentos.id_cliente', $idCliente)
            ->findAll();

        return view('estandares/detalle', [
            'cliente' => $cliente,
            'estandar' => $estandar,
            'clienteEstandar' => $clienteEstandar,
            'documentos' => $documentos
        ]);
    }

    /**
     * Actualizar estado de cumplimiento (AJAX o POST)
     */
    public function actualizarEstado()
    {
        $idCliente = (int) $this->request->getPost('id_cliente');
        $idEstandar = (int) $this->request->getPost('id_estandar');
        $estado = $this->request->getPost('estado');
        $observaciones = $this->request->getPost('observaciones');
        $calificacionPost = $this->request->getPost('calificacion');
        $fechaCumplimiento = $this->request->getPost('fecha_cumplimiento');

        // Validar datos requeridos
        if (!$idCliente || !$idEstandar || !$estado) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Datos incompletos: cliente=' . $idCliente . ', estandar=' . $idEstandar . ', estado=' . $estado
                ]);
            }
            return redirect()->back()->with('error', 'Datos incompletos');
        }

        // Si no se envía calificación, calcularla automáticamente según Res. 0312/2019
        if ($calificacionPost === null || $calificacionPost === '') {
            $estandar = $this->estandarModel->find($idEstandar);
            $pesoEstandar = (float) ($estandar['peso_porcentual'] ?? 0);
            // Cumple o No Aplica = 100% del peso, otros = 0
            $calificacion = ($estado === 'cumple' || $estado === 'no_aplica') ? $pesoEstandar : 0;
        } else {
            $calificacion = (float) $calificacionPost;
        }

        $resultado = $this->clienteEstandaresModel->actualizarEvaluacion(
            $idCliente,
            $idEstandar,
            $estado,
            $observaciones,
            $calificacion,
            $fechaCumplimiento
        );

        if ($resultado) {
            if ($this->request->isAJAX()) {
                $resumen = $this->clienteEstandaresModel->getResumenCumplimiento($idCliente);
                return $this->response->setJSON([
                    'success' => true,
                    'resumen' => $resumen,
                    'calificacion' => $calificacion,
                    'message' => 'Estado actualizado correctamente'
                ]);
            }
            return redirect()->back()->with('success', 'Evaluación guardada correctamente');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar la evaluación']);
        }
        return redirect()->back()->with('error', 'Error al guardar la evaluación');
    }

    /**
     * Inicializar estándares para un cliente
     * Soporta tanto AJAX como petición normal
     */
    public function inicializar($idCliente)
    {
        if (!session()->get('isLoggedIn')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'No autorizado']);
            }
            return redirect()->to('/login');
        }

        // Obtener contexto para determinar nivel
        $contexto = $this->contextoModel->getByCliente($idCliente);
        $nivelEstandares = $contexto['estandares_aplicables'] ?? 60;

        // Usar el servicio de setup
        $resultado = $this->setupService->inicializarEstandaresCliente($idCliente, $nivelEstandares);

        // Si es AJAX, retornar JSON
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => $resultado['success'],
                'message' => $resultado['mensaje'] ?? ($resultado['success'] ? 'Estándares inicializados' : 'Error'),
                'nivel' => $nivelEstandares
            ]);
        }

        // Si es petición normal, hacer redirect
        if ($resultado['success']) {
            return redirect()->to("/estandares/{$idCliente}")
                            ->with('success', $resultado['mensaje']);
        }

        return redirect()->back()->with('error', $resultado['mensaje'] ?? 'Error al inicializar estándares');
    }

    /**
     * Ver transiciones de nivel
     */
    public function transiciones($idCliente)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $cliente = $this->clienteModel->find($idCliente);
        $transiciones = $this->transicionesModel->getByCliente($idCliente);

        return view('estandares/transiciones', [
            'cliente' => $cliente,
            'transiciones' => $transiciones
        ]);
    }

    /**
     * Aplicar transición de nivel
     */
    public function aplicarTransicion($idTransicion)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $aplicadoPor = session()->get('id_usuario');
        $resultado = $this->transicionesModel->aplicarTransicion($idTransicion, $aplicadoPor);

        if ($resultado) {
            return redirect()->back()->with('success', 'Transición aplicada correctamente');
        }

        return redirect()->back()->with('error', 'Error al aplicar transición');
    }

    /**
     * Detectar cambio de nivel (AJAX)
     */
    public function detectarCambio()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $idCliente = $this->request->getPost('id_cliente');
        $trabajadores = $this->request->getPost('trabajadores');
        $riesgo = $this->request->getPost('riesgo');

        $resultado = $this->transicionesModel->detectarCambio($idCliente, $trabajadores, $riesgo);

        return $this->response->setJSON($resultado);
    }

    /**
     * Lista de estándares pendientes
     */
    public function pendientes($idCliente)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $cliente = $this->clienteModel->find($idCliente);
        $pendientes = $this->clienteEstandaresModel->getPendientes($idCliente);

        return view('estandares/pendientes', [
            'cliente' => $cliente,
            'pendientes' => $pendientes
        ]);
    }

    /**
     * Selector de cliente para ver cumplimiento PHVA
     */
    public function seleccionarCliente()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Obtener todos los clientes activos (sin filtrar por consultor)
        $clientes = $this->clienteModel
            ->where('estado', 'activo')
            ->orderBy('nombre_cliente', 'ASC')
            ->findAll();

        return view('estandares/seleccionar_cliente', [
            'clientes' => $clientes
        ]);
    }

    /**
     * Catálogo de 60 estándares (vista de referencia)
     * Auto-configura tablas y datos si es necesario
     */
    public function catalogo()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Auto-configurar base de datos si es necesario
        $setupResult = $this->setupService->verificarYConfigurar();

        $estandaresAgrupados = $this->estandarModel->getGroupedByCategoria();

        return view('estandares/catalogo', [
            'estandaresAgrupados' => $estandaresAgrupados,
            'setupResult' => $setupResult
        ]);
    }

    /**
     * Exportar reporte de cumplimiento
     */
    public function exportarReporte($idCliente)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $cliente = $this->clienteModel->find($idCliente);
        $contexto = $this->contextoModel->getByCliente($idCliente);
        $estandares = $this->clienteEstandaresModel->getByClienteCompleto($idCliente);
        $resumen = $this->clienteEstandaresModel->getResumenCumplimiento($idCliente);

        // Generar PDF o Excel según parámetro
        $formato = $this->request->getGet('formato') ?? 'pdf';

        // TODO: Implementar generación de PDF/Excel
        return view('estandares/reporte', [
            'cliente' => $cliente,
            'contexto' => $contexto,
            'estandares' => $estandares,
            'resumen' => $resumen
        ]);
    }
}
