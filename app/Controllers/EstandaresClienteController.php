<?php

namespace App\Controllers;

use App\Models\ClienteEstandaresModel;
use App\Models\ClienteContextoSstModel;
use App\Models\ClienteTransicionesModel;
use App\Models\EstandarMinimoModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class EstandaresClienteController extends Controller
{
    protected $clienteEstandaresModel;
    protected $contextoModel;
    protected $transicionesModel;
    protected $estandarModel;
    protected $clienteModel;

    public function __construct()
    {
        $this->clienteEstandaresModel = new ClienteEstandaresModel();
        $this->contextoModel = new ClienteContextoSstModel();
        $this->transicionesModel = new ClienteTransicionesModel();
        $this->estandarModel = new EstandarMinimoModel();
        $this->clienteModel = new ClientModel();
    }

    /**
     * Dashboard de cumplimiento de estándares
     */
    public function index($idCliente)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $cliente = $this->clienteModel->find($idCliente);
        $contexto = $this->contextoModel->getByCliente($idCliente);
        $estandares = $this->clienteEstandaresModel->getByClienteGroupedPHVA($idCliente);
        $resumen = $this->clienteEstandaresModel->getResumenCumplimiento($idCliente);
        $cumplimientoPonderado = $this->clienteEstandaresModel->getCumplimientoPonderado($idCliente);
        $transicionesPendientes = $this->transicionesModel->getPendientes($idCliente);

        return view('estandares/dashboard', [
            'cliente' => $cliente,
            'contexto' => $contexto,
            'estandares' => $estandares,
            'resumen' => $resumen,
            'cumplimientoPonderado' => $cumplimientoPonderado,
            'transicionesPendientes' => $transicionesPendientes
        ]);
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
     * Actualizar estado de cumplimiento (AJAX)
     */
    public function actualizarEstado()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $idCliente = $this->request->getPost('id_cliente');
        $idEstandar = $this->request->getPost('id_estandar');
        $estado = $this->request->getPost('estado');
        $observaciones = $this->request->getPost('observaciones');

        $resultado = $this->clienteEstandaresModel->actualizarEstado(
            $idCliente,
            $idEstandar,
            $estado,
            $observaciones
        );

        if ($resultado) {
            $resumen = $this->clienteEstandaresModel->getResumenCumplimiento($idCliente);

            return $this->response->setJSON([
                'success' => true,
                'resumen' => $resumen,
                'message' => 'Estado actualizado'
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Error al actualizar']);
    }

    /**
     * Inicializar estándares para un cliente
     */
    public function inicializar($idCliente)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $resultado = $this->clienteEstandaresModel->inicializarParaCliente($idCliente);

        if (!empty($resultado)) {
            return redirect()->to("/estandares/{$idCliente}")
                            ->with('success', "Estándares inicializados: {$resultado['total_registros']} registros");
        }

        return redirect()->back()->with('error', 'Error al inicializar estándares');
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
     */
    public function catalogo()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $estandaresAgrupados = $this->estandarModel->getGroupedByCategoria();

        return view('estandares/catalogo', [
            'estandaresAgrupados' => $estandaresAgrupados
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
