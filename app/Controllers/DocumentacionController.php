<?php

namespace App\Controllers;

use App\Models\DocDocumentoModel;
use App\Models\DocCarpetaModel;
use App\Models\DocTipoModel;
use App\Models\DocPlantillaModel;
use App\Models\ClienteEstandaresModel;
use App\Models\ClienteContextoSstModel;
use App\Models\ClientModel;
use App\Models\ResponsableSSTModel;
use CodeIgniter\Controller;

class DocumentacionController extends Controller
{
    protected $documentoModel;
    protected $carpetaModel;
    protected $tipoModel;
    protected $plantillaModel;
    protected $estandaresModel;
    protected $contextoModel;
    protected $clienteModel;

    public function __construct()
    {
        $this->documentoModel = new DocDocumentoModel();
        $this->carpetaModel = new DocCarpetaModel();
        $this->tipoModel = new DocTipoModel();
        $this->plantillaModel = new DocPlantillaModel();
        $this->estandaresModel = new ClienteEstandaresModel();
        $this->contextoModel = new ClienteContextoSstModel();
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

        // Obtener documentos organizados por estado para las cards
        $documentosPorEstado = $this->getDocumentosPorEstado($idCliente);

        // Obtener árbol de carpetas con documentos y estados IA
        $carpetasConDocs = $this->carpetaModel->getArbolConDocumentosYEstados($idCliente);

        // Obtener verificación de roles obligatorios del SG-SST
        $responsableModel = new ResponsableSSTModel();
        $contexto = $this->contextoModel->getByCliente($idCliente);
        $estandares = $contexto['estandares_aplicables'] ?? 7;
        $verificacionRoles = $responsableModel->verificarRolesObligatorios($idCliente, $estandares);

        return view('documentacion/dashboard', [
            'cliente' => $cliente,
            'carpetas' => $carpetas,
            'carpetasConDocs' => $carpetasConDocs,
            'documentos' => $documentos,
            'estadisticas' => $estadisticas,
            'cumplimiento' => $cumplimiento,
            'documentosPorEstado' => $documentosPorEstado,
            'verificacionRoles' => $verificacionRoles,
            'estandaresAplicables' => $estandares
        ]);
    }

    /**
     * Obtiene plantillas disponibles vs documentos creados por el cliente
     * Organizado por estado: sin_generar, borrador, en_revision, pendiente_firma, aprobado
     * FILTRA según el nivel de estándares del cliente (7, 21 o 60)
     */
    private function getDocumentosPorEstado($idCliente): array
    {
        // Obtener contexto del cliente para saber su nivel
        $contexto = $this->contextoModel->getByCliente($idCliente);
        $nivelCliente = (int)($contexto['estandares_aplicables'] ?? 60);

        // Determinar el campo de filtro según el nivel
        $campoAplica = match($nivelCliente) {
            7 => 'aplica_7',
            21 => 'aplica_21',
            default => 'aplica_60'
        };

        // Obtener plantillas activas FILTRADAS por nivel del cliente
        $plantillasQuery = $this->plantillaModel
            ->select('tbl_doc_plantillas.*, tbl_doc_tipos.nombre as tipo_nombre, tbl_doc_tipos.codigo as tipo_codigo')
            ->join('tbl_doc_tipos', 'tbl_doc_tipos.id_tipo = tbl_doc_plantillas.id_tipo')
            ->where('tbl_doc_plantillas.activo', 1);

        // Solo filtrar si la columna existe (compatibilidad con BD sin migrar)
        $db = \Config\Database::connect();
        if ($db->fieldExists($campoAplica, 'tbl_doc_plantillas')) {
            $plantillasQuery->where("tbl_doc_plantillas.{$campoAplica}", 1);
        }

        $plantillas = $plantillasQuery
            ->orderBy('tbl_doc_tipos.nombre', 'ASC')
            ->orderBy('tbl_doc_plantillas.orden', 'ASC')
            ->findAll();

        // Obtener mapeo de plantillas a carpetas
        $mapeoCarpetas = [];
        $mapeoQuery = $db->query("SELECT codigo_plantilla, codigo_carpeta FROM tbl_doc_plantilla_carpeta");
        if ($mapeoQuery) {
            foreach ($mapeoQuery->getResultArray() as $row) {
                $mapeoCarpetas[$row['codigo_plantilla']] = $row['codigo_carpeta'];
            }
        }

        // Obtener documentos existentes del cliente
        $documentosExistentes = $this->documentoModel
            ->where('id_cliente', $idCliente)
            ->findAll();

        // Indexar documentos por codigo_sugerido de plantilla
        $docsIndexados = [];
        foreach ($documentosExistentes as $doc) {
            // Usar el código del documento para identificar a qué plantilla corresponde
            $codigoBase = $this->extraerCodigoBase($doc['codigo']);
            $docsIndexados[$codigoBase] = $doc;
        }

        // Organizar por estado
        $resultado = [
            'sin_generar' => [],
            'borrador' => [],
            'en_revision' => [],
            'pendiente_firma' => [],
            'aprobado' => [],
            'nivel_cliente' => $nivelCliente
        ];

        foreach ($plantillas as $plantilla) {
            $codigoSugerido = $plantilla['codigo_sugerido'];

            if (isset($docsIndexados[$codigoSugerido])) {
                // El documento existe, clasificar por estado
                $doc = $docsIndexados[$codigoSugerido];
                $doc['plantilla'] = $plantilla;

                $estado = $doc['estado'] ?? 'borrador';
                if (isset($resultado[$estado])) {
                    $resultado[$estado][] = $doc;
                } else {
                    $resultado['borrador'][] = $doc;
                }
            } else {
                // No existe, va a sin_generar
                $resultado['sin_generar'][] = [
                    'plantilla' => $plantilla,
                    'codigo_sugerido' => $codigoSugerido,
                    'nombre' => $plantilla['nombre'],
                    'tipo_nombre' => $plantilla['tipo_nombre'],
                    'tipo_codigo' => $plantilla['tipo_codigo'],
                    'descripcion' => $plantilla['descripcion'],
                    'codigo_carpeta' => $mapeoCarpetas[$codigoSugerido] ?? null
                ];
            }
        }

        // Contar totales
        $resultado['contadores'] = [
            'sin_generar' => count($resultado['sin_generar']),
            'borrador' => count($resultado['borrador']),
            'en_revision' => count($resultado['en_revision']),
            'pendiente_firma' => count($resultado['pendiente_firma']),
            'aprobado' => count($resultado['aprobado']),
            'total_plantillas' => count($plantillas),
            'nivel_cliente' => $nivelCliente
        ];

        return $resultado;
    }

    /**
     * Extrae el código base de un documento (ej: PRG-CAP-001 -> PRG-CAP)
     */
    private function extraerCodigoBase($codigo): string
    {
        // Remover el consecutivo final (ej: -001, -002)
        return preg_replace('/-\d{3}$/', '', $codigo);
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

        // Carpetas tipo raíz o phva redirigen al dashboard del cliente
        if (in_array($carpeta['tipo'], ['raiz', 'phva'])) {
            return redirect()->to('documentacion/' . $carpeta['id_cliente']);
        }

        $ruta = $this->carpetaModel->getRutaCompleta($idCarpeta);
        $subcarpetas = $this->carpetaModel->getHijos($idCarpeta);
        $documentos = $this->documentoModel->getByCarpetaConEstadoIA($idCarpeta);
        $cliente = $this->clienteModel->find($carpeta['id_cliente']);

        // Obtener estadísticas de estado IA de las subcarpetas
        foreach ($subcarpetas as &$sub) {
            $sub['stats'] = $this->documentoModel->getEstadisticasIAPorCarpeta($sub['id_carpeta']);
        }

        // Determinar si esta carpeta tiene fases de dependencia
        $fasesInfo = null;
        $tipoCarpetaFases = $this->determinarTipoCarpetaFases($carpeta);
        $documentoExistente = null;

        if ($tipoCarpetaFases) {
            $fasesService = new \App\Services\FasesDocumentoService();
            $fasesInfo = $fasesService->getResumenFases($cliente['id_cliente'], $tipoCarpetaFases);

            // Verificar si ya existe un documento generado para esta carpeta
            $mapaTipoDocumento = [
                'capacitacion_sst' => 'programa_capacitacion',
                'responsables_sst' => 'asignacion_responsable_sgsst',
            ];
            $tipoDocBuscar = $mapaTipoDocumento[$tipoCarpetaFases] ?? null;
            if ($tipoDocBuscar) {
                $db = \Config\Database::connect();
                $documentoExistente = $db->table('tbl_documentos_sst')
                    ->where('id_cliente', $cliente['id_cliente'])
                    ->where('tipo_documento', $tipoDocBuscar)
                    ->where('anio', date('Y'))
                    ->get()
                    ->getRowArray();
            }
        }

        // Obtener contexto del cliente para nivel de estándares (necesario para 1.1.2)
        $contextoCliente = null;
        if ($tipoCarpetaFases === 'responsabilidades_sgsst') {
            $db = \Config\Database::connect();
            $contextoCliente = $db->table('tbl_cliente_contexto_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->get()
                ->getRowArray();
        }

        // Obtener documentos SST aprobados para mostrar en tabla
        $documentosSSTAprobados = [];
        if (in_array($tipoCarpetaFases, ['capacitacion_sst', 'responsables_sst', 'responsabilidades_sgsst', 'archivo_documental', 'presupuesto_sst', 'afiliacion_srl'])) {
            $db = \Config\Database::connect();
            $queryDocs = $db->table('tbl_documentos_sst')
                ->where('id_cliente', $cliente['id_cliente'])
                ->whereIn('estado', ['borrador', 'generado', 'aprobado', 'firmado', 'pendiente_firma']);

            // Filtrar por tipo de documento segun la carpeta
            if ($tipoCarpetaFases === 'archivo_documental') {
                // 2.5.1: Archivo documental - NO FILTRAR, mostrar TODOS los documentos
                // No aplicamos filtro por tipo_documento
            } elseif ($tipoCarpetaFases === 'responsabilidades_sgsst') {
                // 1.1.2: Buscar los 3 tipos de documentos de responsabilidades
                // Nota: Vigia/Delegado ahora esta combinado en responsabilidades_rep_legal_sgsst
                $queryDocs->whereIn('tipo_documento', [
                    'responsabilidades_rep_legal_sgsst',
                    'responsabilidades_responsable_sgsst',
                    'responsabilidades_trabajadores_sgsst'
                ]);
            } elseif ($tipoCarpetaFases === 'presupuesto_sst') {
                // 1.1.3: Presupuesto SST
                $queryDocs->where('tipo_documento', 'presupuesto_sst');
            } elseif ($tipoCarpetaFases === 'afiliacion_srl') {
                // 1.1.4: Afiliación al Sistema General de Riesgos Laborales
                $queryDocs->where('tipo_documento', 'planilla_afiliacion_srl');
            } elseif (isset($tipoDocBuscar)) {
                $queryDocs->where('tipo_documento', $tipoDocBuscar);
            }

            $documentosSSTAprobados = $queryDocs
                ->orderBy('anio', 'DESC')
                ->orderBy('updated_at', 'DESC')
                ->get()
                ->getResultArray();

            // Agregar conteo de firmas, versión texto y lista de versiones para cada documento
            // También auto-corregir códigos incorrectos (FT-SST-004 -> FT-SST-001)
            foreach ($documentosSSTAprobados as &$docSST) {
                // Auto-corrección de código para presupuesto_sst
                if ($docSST['tipo_documento'] === 'presupuesto_sst' && $docSST['codigo'] !== 'FT-SST-001') {
                    $db->table('tbl_documentos_sst')
                        ->where('id_documento', $docSST['id_documento'])
                        ->update(['codigo' => 'FT-SST-001', 'updated_at' => date('Y-m-d H:i:s')]);
                    $db->table('tbl_doc_versiones_sst')
                        ->where('id_documento', $docSST['id_documento'])
                        ->update(['codigo' => 'FT-SST-001']);
                    $docSST['codigo'] = 'FT-SST-001'; // Actualizar en memoria también
                }

                $firmaStats = $db->table('tbl_doc_firma_solicitudes')
                    ->select("COUNT(*) as total, SUM(CASE WHEN estado = 'firmado' THEN 1 ELSE 0 END) as firmadas")
                    ->where('id_documento', $docSST['id_documento'])
                    ->get()
                    ->getRowArray();
                $docSST['firmas_total'] = (int)($firmaStats['total'] ?? 0);
                $docSST['firmas_firmadas'] = (int)($firmaStats['firmadas'] ?? 0);

                // Obtener todas las versiones del documento
                $versiones = $db->table('tbl_doc_versiones_sst')
                    ->select('id_version, version_texto, tipo_cambio, descripcion_cambio, estado, autorizado_por, fecha_autorizacion, archivo_pdf')
                    ->where('id_documento', $docSST['id_documento'])
                    ->orderBy('id_version', 'DESC')
                    ->get()
                    ->getResultArray();

                // Asignar estado por defecto a versiones que no lo tengan
                foreach ($versiones as $idx => &$ver) {
                    if (empty($ver['estado'])) {
                        // La versión más reciente (primera en el array) es vigente, las demás históricas
                        $ver['estado'] = ($idx === 0) ? 'vigente' : 'historico';
                    }
                }
                unset($ver);

                $docSST['versiones'] = $versiones;
                $docSST['version_texto'] = !empty($versiones) ? $versiones[0]['version_texto'] : ($docSST['version'] . '.0');

                // Obtener enlace PDF de la versión vigente
                $versionVigente = array_filter($versiones, fn($v) => $v['estado'] === 'vigente');
                $versionVigente = reset($versionVigente);
                $docSST['archivo_pdf'] = $versionVigente['archivo_pdf'] ?? null;
            }
            unset($docSST);
        }

        // Determinar qué vista de tipo cargar
        $vistaTipo = $tipoCarpetaFases ?? 'generica';
        $vistaPath = "documentacion/_tipos/{$vistaTipo}";

        // Verificar que la vista existe, si no usar genérica
        if (!is_file(APPPATH . "Views/{$vistaPath}.php")) {
            $vistaPath = 'documentacion/_tipos/generica';
        }

        // Datos comunes para todas las vistas
        $data = [
            'carpeta' => $carpeta,
            'ruta' => $ruta,
            'subcarpetas' => $subcarpetas,
            'documentos' => $documentos,
            'cliente' => $cliente,
            'fasesInfo' => $fasesInfo,
            'tipoCarpetaFases' => $tipoCarpetaFases,
            'documentoExistente' => $documentoExistente,
            'documentosSSTAprobados' => $documentosSSTAprobados,
            'contextoCliente' => $contextoCliente ?? null,
            'vistaContenido' => $vistaPath
        ];

        return view('documentacion/carpeta', $data);
    }

    /**
     * Determina el tipo de carpeta para verificación de fases
     * Basado en el nombre o código de la carpeta
     */
    protected function determinarTipoCarpetaFases(array $carpeta): ?string
    {
        $nombre = strtolower($carpeta['nombre'] ?? '');
        $codigo = strtolower($carpeta['codigo'] ?? '');

        // 1.2.1. Programa Capacitacion PYP (Ciclo Planear)
        if (strpos($nombre, 'capacitaci') !== false ||
            $codigo === '1.2.1') {
            return 'capacitacion_sst';
        }

        // 2.4.1. Plan Anual de Trabajo (Ciclo Planear)
        if ((strpos($nombre, 'plan') !== false && strpos($nombre, 'objetivos') !== false) ||
            $codigo === '2.4.1') {
            return 'plan_trabajo';
        }

        // 1.1.1. Responsable del SG-SST (Ciclo Planear)
        if ($codigo === '1.1.1' ||
            (strpos($nombre, 'responsable') !== false && strpos($nombre, 'responsabilidades') === false)) {
            return 'responsables_sst';
        }

        // 1.1.2. Responsabilidades en el SG-SST (4 documentos separados)
        if ($codigo === '1.1.2' || strpos($nombre, 'responsabilidades') !== false) {
            return 'responsabilidades_sgsst';
        }

        // 1.1.3. Asignación de recursos para el SG-SST (Presupuesto)
        if ($codigo === '1.1.3' ||
            strpos($nombre, 'recursos') !== false ||
            strpos($nombre, 'presupuesto') !== false) {
            return 'presupuesto_sst';
        }

        // 1.1.4. Afiliación al Sistema General de Riesgos Laborales
        if ($codigo === '1.1.4' ||
            strpos($nombre, 'afiliacion') !== false ||
            strpos($nombre, 'riesgos laborales') !== false) {
            return 'afiliacion_srl';
        }

        // 2.5.1. Archivo o retención documental del SG-SST
        // Muestra tabla con TODOS los documentos generados del cliente
        if ($codigo === '2.5.1' ||
            strpos($nombre, 'archivo') !== false ||
            strpos($nombre, 'retencion documental') !== false) {
            return 'archivo_documental';
        }

        return null;
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

        // Obtener cliente para la vista
        $cliente = $this->clienteModel->find($documento['id_cliente']);

        // Verificar si el documento está en una carpeta con fases de dependencia
        $idCarpeta = $documento['id_carpeta'] ?? null;
        $fasesInfo = null;

        if ($idCarpeta) {
            $carpeta = $this->carpetaModel->find($idCarpeta);
            if ($carpeta) {
                $tipoCarpetaFases = $this->determinarTipoCarpetaFases($carpeta);
                if ($tipoCarpetaFases) {
                    $fasesService = new \App\Services\FasesDocumentoService();
                    $fasesInfo = $fasesService->getResumenFases($cliente['id_cliente'], $tipoCarpetaFases);

                    // Si las fases no están completas, redirigir a la carpeta
                    if (!$fasesInfo['puede_generar_documento']) {
                        return redirect()->to("/documentacion/carpeta/{$idCarpeta}")
                            ->with('error', 'Debe completar las fases previas antes de acceder a este documento. Complete: Cronograma → Plan de Trabajo → Indicadores');
                    }
                }
            }
        }

        $seccionModel = new \App\Models\DocSeccionModel();
        $versionModel = new \App\Models\DocVersionModel();

        $secciones = $seccionModel->getByDocumento($idDocumento);
        $versiones = $versionModel->getByDocumento($idDocumento);
        $progreso = $seccionModel->getProgreso($idDocumento);

        return view('documentacion/ver', [
            'documento' => $documento,
            'secciones' => $secciones,
            'versiones' => $versiones,
            'progreso' => $progreso,
            'cliente' => $cliente,
            'fasesInfo' => $fasesInfo
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
     * Las carpetas se crean según el nivel de estándares del cliente
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

        // Obtener nivel de estándares del cliente desde su contexto
        $contextoModel = new \App\Models\ClienteContextoSstModel();
        $contexto = $contextoModel->where('id_cliente', $idCliente)->first();
        $nivelEstandares = $contexto['estandares_aplicables'] ?? 60;

        $idCarpetaRaiz = $this->carpetaModel->generarEstructura($idCliente, $anio, $nivelEstandares);

        if ($idCarpetaRaiz) {
            return $this->response->setJSON([
                'success' => true,
                'id_carpeta_raiz' => $idCarpetaRaiz,
                'message' => "Estructura creada para el año {$anio} ({$nivelEstandares} estándares)"
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
