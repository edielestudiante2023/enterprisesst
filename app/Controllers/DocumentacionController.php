<?php

namespace App\Controllers;

use App\Models\DocDocumentoModel;
use App\Models\DocCarpetaModel;
use App\Models\DocTipoModel;
use App\Models\ClienteEstandaresModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class DocumentacionController extends Controller
{
    protected $documentoModel;
    protected $carpetaModel;
    protected $tipoModel;
    protected $estandaresModel;
    protected $clienteModel;

    public function __construct()
    {
        $this->documentoModel = new DocDocumentoModel();
        $this->carpetaModel = new DocCarpetaModel();
        $this->tipoModel = new DocTipoModel();
        $this->estandaresModel = new ClienteEstandaresModel();
        $this->clienteModel = new ClientModel();
    }

    /**
     * Dashboard principal de documentación
     */
    public function index($idCliente = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Si no se especifica cliente, usar el de sesión o mostrar selector
        if (!$idCliente) {
            $idCliente = session()->get('id_cliente');
        }

        if (!$idCliente) {
            return redirect()->to('/documentacion/seleccionar-cliente');
        }

        $cliente = $this->clienteModel->find($idCliente);
        $carpetas = $this->carpetaModel->getArbolCompleto($idCliente);
        $documentos = $this->documentoModel->getByCliente($idCliente);
        $estadisticas = $this->documentoModel->getEstadisticas($idCliente);
        $cumplimiento = $this->estandaresModel->getResumenCumplimiento($idCliente);

        return view('documentacion/dashboard', [
            'cliente' => $cliente,
            'carpetas' => $carpetas,
            'documentos' => $documentos,
            'estadisticas' => $estadisticas,
            'cumplimiento' => $cumplimiento
        ]);
    }

    /**
     * Selector de cliente
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

        return view('documentacion/seleccionar_cliente', [
            'clientes' => $clientes
        ]);
    }

    /**
     * Instructivo del módulo de documentación SST
     */
    public function instructivo()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        return view('documentacion/instructivo');
    }

    /**
     * Catálogo de plantillas de documentos
     */
    public function plantillas()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $plantillaModel = new \App\Models\DocPlantillaModel();
        $plantillasAgrupadas = $plantillaModel->getAgrupadasPorTipo();
        $tipos = $this->tipoModel->getActivos();

        return view('documentacion/plantillas', [
            'plantillasAgrupadas' => $plantillasAgrupadas,
            'tipos' => $tipos
        ]);
    }

    /**
     * Vista de carpeta específica
     */
    public function carpeta($idCarpeta)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $carpeta = $this->carpetaModel->find($idCarpeta);

        if (!$carpeta) {
            return redirect()->back()->with('error', 'Carpeta no encontrada');
        }

        $ruta = $this->carpetaModel->getRuta($idCarpeta);
        $subcarpetas = $this->carpetaModel->getHijos($idCarpeta);
        $documentos = $this->documentoModel->getByCarpeta($idCarpeta);
        $cliente = $this->clienteModel->find($carpeta['id_cliente']);

        return view('documentacion/carpeta', [
            'carpeta' => $carpeta,
            'ruta' => $ruta,
            'subcarpetas' => $subcarpetas,
            'documentos' => $documentos,
            'cliente' => $cliente
        ]);
    }

    /**
     * Lista de documentos
     */
    public function documentos($idCliente)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $estado = $this->request->getGet('estado');
        $documentos = $this->documentoModel->getByCliente($idCliente, $estado);
        $cliente = $this->clienteModel->find($idCliente);
        $tipos = $this->tipoModel->getActivos();

        return view('documentacion/documentos', [
            'documentos' => $documentos,
            'cliente' => $cliente,
            'tipos' => $tipos,
            'estadoFiltro' => $estado
        ]);
    }

    /**
     * Ver documento
     */
    public function verDocumento($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->documentoModel->getCompleto($idDocumento);

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        $seccionModel = new \App\Models\DocSeccionModel();
        $versionModel = new \App\Models\DocVersionModel();

        $secciones = $seccionModel->getByDocumento($idDocumento);
        $versiones = $versionModel->getByDocumento($idDocumento);
        $progreso = $seccionModel->getProgreso($idDocumento);

        return view('documentacion/ver_documento', [
            'documento' => $documento,
            'secciones' => $secciones,
            'versiones' => $versiones,
            'progreso' => $progreso
        ]);
    }

    /**
     * Buscar documentos
     */
    public function buscar($idCliente)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $termino = $this->request->getGet('q');

        if (empty($termino)) {
            return redirect()->to("/documentacion/{$idCliente}");
        }

        $documentos = $this->documentoModel->buscar($idCliente, $termino);
        $cliente = $this->clienteModel->find($idCliente);

        return view('documentacion/busqueda', [
            'documentos' => $documentos,
            'cliente' => $cliente,
            'termino' => $termino
        ]);
    }

    /**
     * Documentos próximos a revisión
     */
    public function proximosRevision($idCliente)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $dias = $this->request->getGet('dias') ?? 30;
        $documentos = $this->documentoModel->getProximosRevision($idCliente, $dias);
        $cliente = $this->clienteModel->find($idCliente);

        return view('documentacion/proximos_revision', [
            'documentos' => $documentos,
            'cliente' => $cliente,
            'dias' => $dias
        ]);
    }

    /**
     * Genera estructura de carpetas para un cliente (AJAX)
     */
    public function generarEstructura()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $idCliente = $this->request->getPost('id_cliente');
        $anio = $this->request->getPost('anio') ?? date('Y');

        if (!$idCliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente requerido']);
        }

        $idCarpetaRaiz = $this->carpetaModel->generarEstructura($idCliente, $anio);

        if ($idCarpetaRaiz) {
            return $this->response->setJSON([
                'success' => true,
                'id_carpeta_raiz' => $idCarpetaRaiz,
                'message' => "Estructura creada para el año {$anio}"
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Error al crear estructura']);
    }

    /**
     * Obtiene árbol de carpetas (AJAX)
     */
    public function getArbolCarpetas($idCliente)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $arbol = $this->carpetaModel->getArbolCompleto($idCliente);

        return $this->response->setJSON($arbol);
    }
}
