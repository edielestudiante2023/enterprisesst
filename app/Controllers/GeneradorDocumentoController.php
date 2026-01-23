<?php

namespace App\Controllers;

use App\Models\DocDocumentoModel;
use App\Models\DocSeccionModel;
use App\Models\DocTipoModel;
use App\Models\DocPlantillaModel;
use App\Models\DocCarpetaModel;
use App\Models\ClienteContextoSstModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class GeneradorDocumentoController extends Controller
{
    protected $documentoModel;
    protected $seccionModel;
    protected $tipoModel;
    protected $plantillaModel;
    protected $carpetaModel;
    protected $contextoModel;
    protected $clienteModel;

    public function __construct()
    {
        $this->documentoModel = new DocDocumentoModel();
        $this->seccionModel = new DocSeccionModel();
        $this->tipoModel = new DocTipoModel();
        $this->plantillaModel = new DocPlantillaModel();
        $this->carpetaModel = new DocCarpetaModel();
        $this->contextoModel = new ClienteContextoSstModel();
        $this->clienteModel = new ClientModel();
    }

    /**
     * Paso 1: Seleccionar tipo de documento
     */
    public function nuevo($idCliente)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $cliente = $this->clienteModel->find($idCliente);
        $tipos = $this->tipoModel->getActivos();
        $plantillasAgrupadas = $this->plantillaModel->getAgrupadasPorTipo();

        return view('documentacion/generador/paso1_tipo', [
            'cliente' => $cliente,
            'tipos' => $tipos,
            'plantillasAgrupadas' => $plantillasAgrupadas
        ]);
    }

    /**
     * Paso 2: Configurar documento
     */
    public function configurar($idCliente)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $idPlantilla = $this->request->getPost('id_plantilla') ?? $this->request->getGet('plantilla');
        $idTipo = $this->request->getPost('id_tipo') ?? $this->request->getGet('tipo');

        if (!$idPlantilla && !$idTipo) {
            return redirect()->back()->with('error', 'Seleccione un tipo de documento');
        }

        $cliente = $this->clienteModel->find($idCliente);
        $contexto = $this->contextoModel->getByCliente($idCliente);
        $carpetas = $this->carpetaModel->getArbolCompleto($idCliente);

        $plantilla = null;
        $tipo = null;
        $estructura = [];

        if ($idPlantilla) {
            $plantilla = $this->plantillaModel->getConTipo($idPlantilla);
            $estructura = $this->plantillaModel->getEstructura($idPlantilla);
            $tipo = $this->tipoModel->find($plantilla['id_tipo']);
        } elseif ($idTipo) {
            $tipo = $this->tipoModel->find($idTipo);
            $estructura = $this->tipoModel->getEstructura($idTipo);
        }

        return view('documentacion/generador/paso2_configurar', [
            'cliente' => $cliente,
            'contexto' => $contexto,
            'carpetas' => $carpetas,
            'plantilla' => $plantilla,
            'tipo' => $tipo,
            'estructura' => $estructura
        ]);
    }

    /**
     * Crear documento y redirigir al editor
     */
    public function crear($idCliente)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $datos = [
            'id_cliente' => $idCliente,
            'id_carpeta' => $this->request->getPost('id_carpeta'),
            'id_tipo' => $this->request->getPost('id_tipo'),
            'id_plantilla' => $this->request->getPost('id_plantilla'),
            'nombre' => $this->request->getPost('nombre'),
            'descripcion' => $this->request->getPost('descripcion'),
            'codigo_tipo' => $this->request->getPost('codigo_tipo'),
            'codigo_tema' => $this->request->getPost('codigo_tema'),
            'creado_por' => session()->get('id_usuario')
        ];

        $idDocumento = $this->documentoModel->crearDocumento($datos);

        if (!$idDocumento) {
            return redirect()->back()->with('error', 'Error al crear documento');
        }

        // Crear secciones basadas en estructura
        $idPlantilla = $datos['id_plantilla'];
        $idTipo = $datos['id_tipo'];

        if ($idPlantilla) {
            $estructura = $this->plantillaModel->getEstructura($idPlantilla);
        } else {
            $estructura = $this->tipoModel->getEstructura($idTipo);
        }

        if (!empty($estructura)) {
            $this->seccionModel->crearDesdeEstructura($idDocumento, $estructura);
        }

        return redirect()->to("/documentacion/editar/{$idDocumento}")
                        ->with('success', 'Documento creado. Ahora puede generar el contenido.');
    }

    /**
     * Editor de documento por secciones
     */
    public function editar($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->documentoModel->getCompleto($idDocumento);

        if (!$documento) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        $secciones = $this->seccionModel->getByDocumento($idDocumento);
        $progreso = $this->seccionModel->getProgreso($idDocumento);
        $contexto = $this->contextoModel->getByCliente($documento['id_cliente']);
        $cliente = $this->clienteModel->find($documento['id_cliente']);

        // Obtener prompts si hay plantilla
        $prompts = [];
        if (!empty($documento['id_plantilla'])) {
            $prompts = $this->plantillaModel->getPrompts($documento['id_plantilla']);
        }

        return view('documentacion/generador/editor', [
            'documento' => $documento,
            'secciones' => $secciones,
            'progreso' => $progreso,
            'contexto' => $contexto,
            'cliente' => $cliente,
            'prompts' => $prompts
        ]);
    }

    /**
     * Editar sección específica
     */
    public function editarSeccion($idDocumento, $numeroSeccion)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->documentoModel->getCompleto($idDocumento);
        $seccion = $this->seccionModel->getSeccion($idDocumento, $numeroSeccion);

        if (!$documento || !$seccion) {
            return redirect()->back()->with('error', 'Sección no encontrada');
        }

        $contexto = $this->contextoModel->getByCliente($documento['id_cliente']);
        $cliente = $this->clienteModel->find($documento['id_cliente']);

        // Obtener prompt para esta sección
        $prompt = null;
        if (!empty($documento['id_plantilla'])) {
            $prompt = $this->plantillaModel->getPromptSeccion($documento['id_plantilla'], $numeroSeccion);
        }

        return view('documentacion/generador/editar_seccion', [
            'documento' => $documento,
            'seccion' => $seccion,
            'contexto' => $contexto,
            'cliente' => $cliente,
            'prompt' => $prompt
        ]);
    }

    /**
     * Guardar contenido de sección (AJAX)
     */
    public function guardarSeccion()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $idSeccion = $this->request->getPost('id_seccion');
        $contenido = $this->request->getPost('contenido');
        $generadoPorIA = $this->request->getPost('generado_por_ia') === '1';
        $prompt = $this->request->getPost('prompt');

        $resultado = $this->seccionModel->actualizarContenido($idSeccion, $contenido, $generadoPorIA, $prompt);

        if ($resultado) {
            // Obtener progreso actualizado
            $seccion = $this->seccionModel->find($idSeccion);
            $progreso = $this->seccionModel->getProgreso($seccion['id_documento']);

            return $this->response->setJSON([
                'success' => true,
                'progreso' => $progreso,
                'message' => 'Sección guardada'
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar']);
    }

    /**
     * Aprobar sección (AJAX)
     */
    public function aprobarSeccion()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $idSeccion = $this->request->getPost('id_seccion');

        if ($this->seccionModel->aprobar($idSeccion)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Sección aprobada']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Error al aprobar']);
    }

    /**
     * Generar contenido con IA (AJAX)
     * Usa OpenAI GPT-4o-mini para generar contenido de secciones
     */
    public function generarConIA()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $idSeccion = $this->request->getPost('id_seccion');
        $contextoAdicional = $this->request->getPost('contexto_adicional');

        $seccion = $this->seccionModel->find($idSeccion);

        if (!$seccion) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sección no encontrada']);
        }

        $documento = $this->documentoModel->getCompleto($seccion['id_documento']);
        $contexto = $this->contextoModel->getByCliente($documento['id_cliente']);
        $cliente = $this->clienteModel->find($documento['id_cliente']);

        // Obtener prompt de la plantilla o usar el específico del servicio
        $promptBase = null;
        if (!empty($documento['id_plantilla'])) {
            $promptBase = $this->plantillaModel->getPromptSeccion(
                $documento['id_plantilla'],
                $seccion['numero_seccion']
            );
        }

        // Si no hay prompt de plantilla, obtener del servicio según tipo de documento
        if (empty($promptBase)) {
            $iaService = new \App\Services\IADocumentacionService();
            $codigoTipo = $documento['tipo_codigo'] ?? '';
            if (empty($codigoTipo)) {
                // Obtener código del tipo
                $tipo = $this->tipoModel->find($documento['id_tipo']);
                $codigoTipo = $tipo['codigo'] ?? 'PRG';
            }
            $promptBase = $iaService->getPromptEspecifico(
                $codigoTipo,
                $seccion['numero_seccion'],
                $seccion['nombre_seccion']
            );
        }

        // Preparar datos para el servicio de IA
        $datosIA = [
            'seccion' => $seccion,
            'documento' => $documento,
            'cliente' => $cliente,
            'contexto' => $contexto,
            'prompt_base' => $promptBase,
            'contexto_adicional' => $contextoAdicional
        ];

        // Llamar al servicio de IA
        $iaService = new \App\Services\IADocumentacionService();
        $resultado = $iaService->generarSeccion($datosIA);

        if ($resultado['success']) {
            // Guardar el contenido generado
            $this->seccionModel->actualizarContenido(
                $idSeccion,
                $resultado['contenido'],
                true, // generado por IA
                $resultado['prompt_usado'] ?? null
            );

            return $this->response->setJSON([
                'success' => true,
                'contenido' => $resultado['contenido'],
                'tokens_usados' => $resultado['tokens_usados'] ?? 0,
                'message' => 'Contenido generado con IA exitosamente'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => $resultado['error'] ?? 'Error al generar contenido con IA'
        ]);
    }

    /**
     * Vista previa del documento completo
     */
    public function vistaPrevia($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $documento = $this->documentoModel->getCompleto($idDocumento);
        $secciones = $this->seccionModel->getByDocumento($idDocumento);
        $cliente = $this->clienteModel->find($documento['id_cliente']);

        return view('documentacion/generador/vista_previa', [
            'documento' => $documento,
            'secciones' => $secciones,
            'cliente' => $cliente
        ]);
    }

    /**
     * Finalizar documento (cambiar a en_revision)
     */
    public function finalizar($idDocumento)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Verificar que todas las secciones estén completas
        $progreso = $this->seccionModel->getProgreso($idDocumento);

        if ($progreso['pendientes'] > 0) {
            return redirect()->back()->with('error', 'Hay secciones pendientes de completar');
        }

        $this->documentoModel->cambiarEstado($idDocumento, 'en_revision');

        return redirect()->to("/documentacion/ver/{$idDocumento}")
                        ->with('success', 'Documento finalizado y enviado a revisión');
    }
}
