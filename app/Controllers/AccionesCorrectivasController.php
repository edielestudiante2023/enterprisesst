<?php

namespace App\Controllers;

use App\Models\AccHallazgosModel;
use App\Models\AccAccionesModel;
use App\Models\AccSeguimientosModel;
use App\Models\AccVerificacionesModel;
use App\Models\ClientModel;
use App\Models\UserModel;
use App\Services\AccionesCorrectivasService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controlador para el Módulo de Acciones Correctivas
 * Numerales 7.1.1, 7.1.2, 7.1.3, 7.1.4 - Resolución 0312 de 2019
 */
class AccionesCorrectivasController extends BaseController
{
    protected AccHallazgosModel $hallazgosModel;
    protected AccAccionesModel $accionesModel;
    protected AccSeguimientosModel $seguimientosModel;
    protected AccVerificacionesModel $verificacionesModel;
    protected AccionesCorrectivasService $service;

    public function __construct()
    {
        $this->hallazgosModel = new AccHallazgosModel();
        $this->accionesModel = new AccAccionesModel();
        $this->seguimientosModel = new AccSeguimientosModel();
        $this->verificacionesModel = new AccVerificacionesModel();
        $this->service = new AccionesCorrectivasService();
    }

    /**
     * Dashboard general - Lista de clientes para seleccionar
     */
    public function dashboard()
    {
        $session = session();
        $clienteModel = new ClientModel();

        $clientes = $clienteModel->where('estado', 'activo')->findAll();

        // Agregar resumen de acciones por cliente
        foreach ($clientes as &$cliente) {
            $cliente['resumen_acciones'] = $this->service->getResumenWidget($cliente['id_cliente']);
        }

        return view('acciones_correctivas/dashboard', [
            'clientes' => $clientes,
            'usuario' => [
                'nombre' => $session->get('nombre') ?? $session->get('email'),
                'role' => $session->get('role')
            ]
        ]);
    }

    /**
     * Dashboard de acciones correctivas de un cliente
     */
    public function index(int $idCliente)
    {
        $session = session();
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Obtener datos completos del dashboard
        $dashboardData = $this->service->getDashboardData($idCliente);

        return view('acciones_correctivas/index', [
            'cliente' => $cliente,
            'estadisticas_hallazgos' => $dashboardData['estadisticas_hallazgos'],
            'estadisticas_acciones' => $dashboardData['estadisticas_acciones'],
            'estadisticas_efectividad' => $dashboardData['estadisticas_efectividad'],
            'hallazgos_recientes' => $dashboardData['hallazgos_recientes'],
            'acciones_vencidas' => $dashboardData['acciones_vencidas'],
            'acciones_proximas_vencer' => $dashboardData['acciones_proximas_vencer'],
            'verificaciones_pendientes' => $dashboardData['verificaciones_pendientes'],
            'kpis' => $dashboardData['kpis'],
            'anioActual' => date('Y')
        ]);
    }

    /**
     * Vista filtrada por numeral (para embeber en carpetas de documentación)
     */
    public function porNumeral(int $idCliente, string $numeral)
    {
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Validar numeral
        if (!in_array($numeral, ['7.1.1', '7.1.2', '7.1.3', '7.1.4'])) {
            return redirect()->back()->with('error', 'Numeral no válido');
        }

        $datos = $this->service->getDatosPorNumeral($idCliente, $numeral);

        return view('acciones_correctivas/por_numeral', [
            'cliente' => $cliente,
            'numeral' => $numeral,
            'nombre_numeral' => $datos['nombre_numeral'],
            'descripcion_numeral' => $datos['descripcion_numeral'],
            'hallazgos' => $datos['hallazgos'],
            'acciones' => $datos['acciones'],
            'estadisticas' => $datos['estadisticas'],
            'tipos_origen' => $datos['tipos_origen_numeral'],
            'catalogo_origenes' => $this->service->getCatalogoOrigenes()
        ]);
    }

    // =========================================================================
    // CRUD DE HALLAZGOS
    // =========================================================================

    /**
     * Lista de hallazgos de un cliente
     */
    public function hallazgos(int $idCliente)
    {
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $hallazgos = $this->hallazgosModel->getByCliente($idCliente);
        $catalogo = $this->service->getCatalogoOrigenes();

        return view('acciones_correctivas/hallazgos/index', [
            'cliente' => $cliente,
            'hallazgos' => $hallazgos,
            'catalogo_origenes' => $catalogo
        ]);
    }

    /**
     * Formulario para crear hallazgo
     */
    public function crearHallazgo(int $idCliente, ?string $numeral = null)
    {
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $userModel = new UserModel();
        $usuarios = $userModel->where('estado', 'activo')->findAll();
        $catalogo = $this->service->getCatalogoOrigenes();

        // Si viene numeral, filtrar catálogo
        if ($numeral) {
            $catalogo = array_filter($catalogo, fn($c) => $c['numeral_default'] === $numeral);
        }

        return view('acciones_correctivas/hallazgos/crear', [
            'cliente' => $cliente,
            'usuarios' => $usuarios,
            'catalogo_origenes' => array_values($catalogo),
            'numeral_preseleccionado' => $numeral
        ]);
    }

    /**
     * Guardar hallazgo nuevo
     */
    public function guardarHallazgo(int $idCliente)
    {
        $session = session();
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado')->withInput();
        }

        $data = [
            'id_cliente' => $idCliente,
            'tipo_origen' => $this->request->getPost('tipo_origen'),
            'numeral_asociado' => $this->request->getPost('numeral_asociado'),
            'titulo' => $this->request->getPost('titulo'),
            'descripcion' => $this->request->getPost('descripcion'),
            'area_proceso' => $this->request->getPost('area_proceso'),
            'severidad' => $this->request->getPost('severidad'),
            'fecha_deteccion' => $this->request->getPost('fecha_deteccion'),
            'fecha_limite_accion' => $this->request->getPost('fecha_limite_accion') ?: null,
            'reportado_por' => $session->get('user_id') ?? $session->get('id_usuario'),
            'created_by' => $session->get('user_id') ?? $session->get('id_usuario')
        ];

        // Manejar archivo de evidencia - guardar en public/uploads/{nit_cliente}/
        $archivo = $this->request->getFile('evidencia_inicial');
        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            $nitCliente = $cliente['nit_cliente'];
            $uploadPath = ROOTPATH . 'public/uploads/' . $nitCliente;

            // Crear directorio si no existe
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $nuevoNombre = $archivo->getRandomName();
            $archivo->move($uploadPath, $nuevoNombre);

            // Guardar URL accesible
            $enlaceArchivo = base_url('uploads/' . $nitCliente . '/' . $nuevoNombre);
            $data['evidencia_inicial'] = $enlaceArchivo;

            // Registrar en tbl_reporte para que aparezca en reportList
            $reporteModel = new \App\Models\ReporteModel();
            $reporteModel->save([
                'titulo_reporte' => 'Evidencia Hallazgo: ' . $data['titulo'],
                'id_detailreport' => 1, // Ajustar segun tipo de documento correspondiente
                'id_report_type' => 1,  // Ajustar segun tipo de reporte correspondiente
                'id_cliente' => $idCliente,
                'estado' => 'CERRADO',
                'observaciones' => 'Evidencia inicial del hallazgo - ' . $data['numeral_asociado'],
                'enlace' => $enlaceArchivo,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        $idHallazgo = $this->hallazgosModel->crearHallazgo($data);

        if ($idHallazgo) {
            return redirect()->to("/acciones-correctivas/{$idCliente}/hallazgo/{$idHallazgo}")
                             ->with('success', 'Hallazgo registrado correctamente');
        }

        return redirect()->back()->with('error', 'Error al registrar el hallazgo')->withInput();
    }

    /**
     * Ver detalle de hallazgo
     */
    public function verHallazgo(int $idCliente, int $idHallazgo)
    {
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $hallazgo = $this->hallazgosModel->getConDetalles($idHallazgo);

        if (!$hallazgo || $hallazgo['id_cliente'] != $idCliente) {
            return redirect()->back()->with('error', 'Hallazgo no encontrado');
        }

        $userModel = new UserModel();
        $usuarios = $userModel->where('estado', 'activo')->findAll();

        return view('acciones_correctivas/hallazgos/ver', [
            'cliente' => $cliente,
            'hallazgo' => $hallazgo,
            'usuarios' => $usuarios,
            'metodos_verificacion' => $this->verificacionesModel->getMetodosVerificacion()
        ]);
    }

    // =========================================================================
    // CRUD DE ACCIONES
    // =========================================================================

    /**
     * Formulario para crear acción
     */
    public function crearAccion(int $idCliente, int $idHallazgo)
    {
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);
        $hallazgo = $this->hallazgosModel->find($idHallazgo);

        if (!$cliente || !$hallazgo) {
            return redirect()->back()->with('error', 'Datos no encontrados');
        }

        $userModel = new UserModel();
        $usuarios = $userModel->where('estado', 'activo')->findAll();

        return view('acciones_correctivas/acciones/crear', [
            'cliente' => $cliente,
            'hallazgo' => $hallazgo,
            'usuarios' => $usuarios
        ]);
    }

    /**
     * Guardar acción nueva
     */
    public function guardarAccion(int $idCliente, int $idHallazgo)
    {
        $session = session();

        // Obtener nombre del responsable
        $responsableId = $this->request->getPost('responsable_id');
        $userModel = new UserModel();
        $responsable = $userModel->find($responsableId);

        $data = [
            'id_hallazgo' => $idHallazgo,
            'tipo_accion' => $this->request->getPost('tipo_accion'),
            'clasificacion_temporal' => $this->request->getPost('clasificacion_temporal'),
            'descripcion_accion' => $this->request->getPost('descripcion_accion'),
            'responsable_id' => $responsableId,
            'responsable_nombre' => $responsable['nombre'] ?? null,
            'fecha_asignacion' => date('Y-m-d'),
            'fecha_compromiso' => $this->request->getPost('fecha_compromiso'),
            'recursos_requeridos' => $this->request->getPost('recursos_requeridos'),
            'costo_estimado' => $this->request->getPost('costo_estimado') ?: null,
            'notas' => $this->request->getPost('notas'),
            'estado' => 'asignada',
            'created_by' => $session->get('user_id') ?? $session->get('id_usuario')
        ];

        $idAccion = $this->accionesModel->crearAccion($data);

        if ($idAccion) {
            return redirect()->to("/acciones-correctivas/{$idCliente}/hallazgo/{$idHallazgo}")
                             ->with('success', 'Acción creada correctamente');
        }

        return redirect()->back()->with('error', 'Error al crear la acción')->withInput();
    }

    /**
     * Ver detalle de acción
     */
    public function verAccion(int $idCliente, int $idAccion)
    {
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);

        $accion = $this->accionesModel->getConDetalles($idAccion);

        if (!$cliente || !$accion || $accion['id_cliente'] != $idCliente) {
            return redirect()->back()->with('error', 'Datos no encontrados');
        }

        $userModel = new UserModel();
        $usuarios = $userModel->where('estado', 'activo')->findAll();

        return view('acciones_correctivas/acciones/ver', [
            'cliente' => $cliente,
            'accion' => $accion,
            'usuarios' => $usuarios,
            'timeline' => $this->seguimientosModel->getTimeline($idAccion),
            'metodos_verificacion' => $this->verificacionesModel->getMetodosVerificacion(),
            'transiciones_validas' => AccAccionesModel::TRANSICIONES_VALIDAS[$accion['estado']] ?? []
        ]);
    }

    /**
     * Cambiar estado de acción
     */
    public function cambiarEstadoAccion(int $idCliente, int $idAccion)
    {
        $session = session();
        $nuevoEstado = $this->request->getPost('nuevo_estado');
        $notas = $this->request->getPost('notas');
        $userId = $session->get('user_id') ?? $session->get('id_usuario') ?? 0;
        $urlAccion = "acciones-correctivas/{$idCliente}/accion/{$idAccion}";

        if (!$userId) {
            return redirect()->to($urlAccion)->with('error', 'Sesión no válida');
        }

        if (empty($nuevoEstado)) {
            return redirect()->to($urlAccion)->with('error', 'Debe seleccionar un estado');
        }

        $resultado = $this->accionesModel->cambiarEstado($idAccion, $nuevoEstado, $userId, $notas);

        if ($resultado) {
            return redirect()->to($urlAccion)->with('success', 'Estado actualizado a: ' . ucwords(str_replace('_', ' ', $nuevoEstado)));
        }

        return redirect()->to($urlAccion)->with('error', 'No se pudo cambiar el estado. Verifique que la transición sea válida.');
    }

    // =========================================================================
    // SEGUIMIENTOS Y EVIDENCIAS
    // =========================================================================

    /**
     * Registrar avance de acción
     */
    public function registrarAvance(int $idCliente, int $idAccion)
    {
        $session = session();
        $userId = $session->get('user_id') ?? $session->get('id_usuario') ?? 0;
        $userName = $session->get('nombre') ?? $session->get('nombre_completo') ?? $session->get('email') ?? 'Usuario';
        $urlAccion = "acciones-correctivas/{$idCliente}/accion/{$idAccion}";

        if (!$userId) {
            return redirect()->to($urlAccion)->with('error', 'Sesión no válida');
        }

        $descripcion = $this->request->getPost('descripcion');
        $porcentaje = (int) $this->request->getPost('porcentaje_avance');

        if (empty($descripcion)) {
            return redirect()->to($urlAccion)->with('error', 'La descripción es requerida');
        }

        $resultado = $this->seguimientosModel->registrarAvance($idAccion, $descripcion, $porcentaje, $userId, $userName);

        if ($resultado) {
            return redirect()->to($urlAccion)->with('success', 'Avance registrado correctamente');
        }

        return redirect()->to($urlAccion)->with('error', 'Error al registrar el avance');
    }

    /**
     * Subir evidencia (archivo o hipervínculo)
     */
    public function subirEvidencia(int $idCliente, int $idAccion)
    {
        $session = session();
        $userId = $session->get('user_id') ?? $session->get('id_usuario');
        $userName = $session->get('nombre') ?? $session->get('nombre_completo') ?? 'Usuario';
        $urlAccion = "acciones-correctivas/{$idCliente}/accion/{$idAccion}";

        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);

        if (!$cliente) {
            return redirect()->to($urlAccion)->with('error', 'Cliente no encontrado');
        }

        $descripcion = $this->request->getPost('descripcion');
        $tipoEvidencia = $this->request->getPost('tipo_evidencia') ?? 'archivo';

        $enlaceArchivo = '';
        $nombreArchivo = '';
        $tipoArchivo = '';

        if ($tipoEvidencia === 'enlace') {
            // Hipervínculo externo
            $enlaceArchivo = $this->request->getPost('enlace_url');
            $nombreArchivo = $this->request->getPost('enlace_nombre') ?: 'Enlace externo';
            $tipoArchivo = 'enlace';

            if (empty($enlaceArchivo) || !filter_var($enlaceArchivo, FILTER_VALIDATE_URL)) {
                return redirect()->to($urlAccion)->with('error', 'URL no válida');
            }
        } else {
            // Archivo físico
            $archivo = $this->request->getFile('archivo');

            if (!$archivo || !$archivo->isValid()) {
                return redirect()->to($urlAccion)->with('error', 'Archivo no válido');
            }

            // Guardar archivo en public/uploads/{nit_cliente}/
            $nitCliente = $cliente['nit_cliente'];
            $uploadPath = ROOTPATH . 'public/uploads/' . $nitCliente;

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $nuevoNombre = $archivo->getRandomName();
            $nombreArchivo = $archivo->getClientName();
            $tipoArchivo = $archivo->getClientMimeType();
            $archivo->move($uploadPath, $nuevoNombre);

            // URL accesible
            $enlaceArchivo = base_url('uploads/' . $nitCliente . '/' . $nuevoNombre);
        }

        $archivoData = [
            'ruta' => $enlaceArchivo,
            'nombre' => $nombreArchivo,
            'tipo' => $tipoArchivo
        ];

        $resultado = $this->seguimientosModel->registrarEvidencia($idAccion, $descripcion, $archivoData, $userId, $userName);

        if ($resultado) {
            // Registrar en tbl_reporte para reportList
            $accion = $this->accionesModel->find($idAccion);
            $hallazgo = $this->hallazgosModel->find($accion['id_hallazgo'] ?? 0);

            $reporteModel = new \App\Models\ReporteModel();
            $reporteModel->save([
                'titulo_reporte' => 'Evidencia Accion #' . $idAccion . ': ' . substr($descripcion, 0, 50),
                'id_detailreport' => 1,
                'id_report_type' => 1,
                'id_cliente' => $idCliente,
                'estado' => 'CERRADO',
                'observaciones' => 'Evidencia de accion correctiva - ' . ($hallazgo['numeral_asociado'] ?? ''),
                'enlace' => $enlaceArchivo,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $tipoMsg = $tipoEvidencia === 'enlace' ? 'Enlace guardado' : 'Evidencia subida';
            return redirect()->to("acciones-correctivas/{$idCliente}/accion/{$idAccion}")->with('success', $tipoMsg . ' correctamente');
        }

        return redirect()->to("acciones-correctivas/{$idCliente}/accion/{$idAccion}")->with('error', 'Error al guardar la evidencia');
    }

    /**
     * Registrar comentario
     */
    public function registrarComentario(int $idCliente, int $idAccion)
    {
        $session = session();
        $userId = $session->get('user_id') ?? $session->get('id_usuario') ?? 0;
        $userName = $session->get('nombre') ?? $session->get('nombre_completo') ?? $session->get('email') ?? 'Usuario';
        $urlAccion = "acciones-correctivas/{$idCliente}/accion/{$idAccion}";

        if (!$userId) {
            return redirect()->to($urlAccion)->with('error', 'Sesión no válida');
        }

        $comentario = $this->request->getPost('comentario');

        if (empty($comentario)) {
            return redirect()->to($urlAccion)->with('error', 'El comentario no puede estar vacío');
        }

        $resultado = $this->seguimientosModel->registrarComentario($idAccion, $comentario, $userId, $userName);

        if ($resultado) {
            return redirect()->to($urlAccion)->with('success', 'Comentario registrado correctamente');
        }

        return redirect()->to($urlAccion)->with('error', 'Error al registrar el comentario');
    }

    // =========================================================================
    // VERIFICACIÓN DE EFECTIVIDAD
    // =========================================================================

    /**
     * Registrar verificación de efectividad
     */
    public function registrarVerificacion(int $idCliente, int $idAccion)
    {
        $session = session();
        $userId = $session->get('user_id') ?? $session->get('id_usuario');
        $userName = $session->get('nombre');

        $data = [
            'id_accion' => $idAccion,
            'metodo_verificacion' => $this->request->getPost('metodo_verificacion'),
            'resultado' => $this->request->getPost('resultado'),
            'observaciones' => $this->request->getPost('observaciones'),
            'fecha_verificacion' => $this->request->getPost('fecha_verificacion') ?: date('Y-m-d'),
            'fecha_proxima_verificacion' => $this->request->getPost('fecha_proxima_verificacion') ?: null,
            'verificado_por' => $userId,
            'verificado_por_nombre' => $userName
        ];

        // Manejar archivo de evidencia de verificación
        $archivo = $this->request->getFile('evidencia_verificacion');
        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            $nuevoNombre = $archivo->getRandomName();
            $archivo->move(WRITEPATH . 'uploads/acciones_correctivas/verificaciones', $nuevoNombre);
            $data['evidencia_verificacion'] = 'uploads/acciones_correctivas/verificaciones/' . $nuevoNombre;
        }

        $idVerificacion = $this->verificacionesModel->registrarVerificacion($data);

        if ($idVerificacion) {
            $mensaje = 'Verificación registrada correctamente';

            // Si no es efectiva y se solicita crear nueva acción
            if ($data['resultado'] === 'no_efectiva' && $this->request->getPost('crear_nueva_accion')) {
                $nuevaAccion = $this->verificacionesModel->crearAccionDesdeVerificacion($idVerificacion, $userId);
                if ($nuevaAccion) {
                    $mensaje .= '. Se creó nueva acción correctiva #' . $nuevaAccion;
                }
            }

            return redirect()->back()->with('success', $mensaje);
        }

        return redirect()->back()->with('error', 'Error al registrar la verificación');
    }

    // =========================================================================
    // ANÁLISIS DE CAUSA RAÍZ CON IA
    // =========================================================================

    /**
     * Vista para análisis de causa raíz con IA
     */
    public function analisisCausaRaiz(int $idCliente, int $idAccion)
    {
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);

        $accion = $this->accionesModel->getConDetalles($idAccion);

        if (!$cliente || !$accion) {
            return redirect()->back()->with('error', 'Datos no encontrados');
        }

        $hallazgo = $this->hallazgosModel->find($accion['id_hallazgo']);

        // Obtener historial del análisis si existe
        $historialDialogo = [];
        if (!empty($accion['analisis_causa_raiz'])) {
            $historialDialogo = is_array($accion['analisis_causa_raiz'])
                ? $accion['analisis_causa_raiz']
                : json_decode($accion['analisis_causa_raiz'], true) ?? [];
        }

        return view('acciones_correctivas/acciones/analisis_causa_raiz', [
            'cliente' => $cliente,
            'accion' => $accion,
            'hallazgo' => $hallazgo,
            'historial_dialogo' => $historialDialogo,
            'causa_identificada' => $accion['causa_raiz_identificada'] ?? null
        ]);
    }

    /**
     * Procesar turno del análisis de causa raíz con IA (AJAX)
     */
    public function procesarAnalisisIA(int $idCliente, int $idAccion): ResponseInterface
    {
        $accion = $this->accionesModel->find($idAccion);
        $hallazgo = $this->hallazgosModel->find($accion['id_hallazgo']);

        if (!$accion || !$hallazgo) {
            return $this->response->setJSON(['error' => 'Datos no encontrados'])->setStatusCode(404);
        }

        // Obtener historial actual
        $historialDialogo = [];
        if (!empty($accion['analisis_causa_raiz'])) {
            $historialDialogo = json_decode($accion['analisis_causa_raiz'], true) ?? [];
        }

        // Agregar respuesta del usuario
        $respuestaUsuario = $this->request->getPost('respuesta_usuario');
        if ($respuestaUsuario) {
            $historialDialogo[] = [
                'rol' => 'usuario',
                'mensaje' => $respuestaUsuario,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }

        // Generar prompt para la IA
        $prompt = $this->service->generarPromptAnalisisCausaRaiz($hallazgo, $historialDialogo);

        // Llamar a la IA (usando el servicio existente de IA)
        try {
            $iaService = new \App\Services\IADocumentacionService();
            $respuestaIA = $iaService->generarContenido($prompt, 500);

            // Agregar respuesta de la IA al historial
            $historialDialogo[] = [
                'rol' => 'ia',
                'mensaje' => $respuestaIA,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Verificar si se identificó la causa raíz
            $causaIdentificada = $this->service->evaluarCausaRaizIdentificada($historialDialogo);

            // Guardar en la base de datos
            $this->accionesModel->guardarAnalisisCausaRaiz(
                $idAccion,
                $historialDialogo,
                $causaIdentificada ? $respuestaIA : null
            );

            return $this->response->setJSON([
                'success' => true,
                'respuesta_ia' => $respuestaIA,
                'historial' => $historialDialogo,
                'causa_identificada' => $causaIdentificada
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en análisis IA: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Error al procesar con IA: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Guardar causa raíz identificada manualmente
     */
    public function guardarCausaRaiz(int $idCliente, int $idAccion)
    {
        $causaRaiz = $this->request->getPost('causa_raiz_identificada');

        $resultado = $this->accionesModel->update($idAccion, [
            'causa_raiz_identificada' => $causaRaiz
        ]);

        if ($resultado) {
            return redirect()->back()->with('success', 'Causa raíz guardada correctamente');
        }

        return redirect()->back()->with('error', 'Error al guardar la causa raíz');
    }

    // =========================================================================
    // REPORTES
    // =========================================================================

    /**
     * Generar reporte PDF de acciones
     */
    public function reportePDF(int $idCliente)
    {
        $datos = $this->service->generarReporteAuditoria($idCliente);

        return view('acciones_correctivas/reportes/pdf', $datos);
    }

    /**
     * Exportar matriz de acciones a Excel (formato HTML para descarga)
     */
    public function exportarExcel(int $idCliente)
    {
        $clienteModel = new ClientModel();
        $cliente = $clienteModel->find($idCliente);

        $acciones = $this->accionesModel->getByCliente($idCliente);

        $this->response->setHeader('Content-Type', 'application/vnd.ms-excel');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="acciones_correctivas_' . $cliente['nit_cliente'] . '_' . date('Y-m-d') . '.xls"');

        return view('acciones_correctivas/reportes/excel', [
            'cliente' => $cliente,
            'acciones' => $acciones
        ]);
    }

    // =========================================================================
    // API ENDPOINTS (para AJAX)
    // =========================================================================

    /**
     * Obtener estadísticas (AJAX)
     */
    public function apiEstadisticas(int $idCliente): ResponseInterface
    {
        $anio = $this->request->getGet('anio') ?? date('Y');

        return $this->response->setJSON([
            'hallazgos' => $this->hallazgosModel->getEstadisticas($idCliente, (int)$anio),
            'acciones' => $this->accionesModel->getEstadisticas($idCliente, (int)$anio),
            'efectividad' => $this->verificacionesModel->getEstadisticasEfectividad($idCliente, (int)$anio),
            'kpis' => $this->service->calcularKPIs($idCliente, (int)$anio)
        ]);
    }

    /**
     * Obtener hallazgos filtrados (AJAX)
     */
    public function apiHallazgos(int $idCliente): ResponseInterface
    {
        $numeral = $this->request->getGet('numeral');
        $estado = $this->request->getGet('estado');

        $hallazgos = $this->hallazgosModel->getByCliente($idCliente, $numeral, $estado);

        return $this->response->setJSON($hallazgos);
    }

    /**
     * Obtener acciones filtradas (AJAX)
     */
    public function apiAcciones(int $idCliente): ResponseInterface
    {
        $estado = $this->request->getGet('estado');
        $tipo = $this->request->getGet('tipo');

        $acciones = $this->accionesModel->getByCliente($idCliente, $estado, $tipo);

        return $this->response->setJSON($acciones);
    }

    /**
     * Descargar archivo de evidencia de seguimiento
     */
    public function descargarEvidencia(int $idSeguimiento)
    {
        $seguimiento = $this->seguimientosModel->find($idSeguimiento);

        if (!$seguimiento || empty($seguimiento['archivo_adjunto'])) {
            return redirect()->back()->with('error', 'Archivo no encontrado');
        }

        $rutaArchivo = WRITEPATH . $seguimiento['archivo_adjunto'];

        if (!file_exists($rutaArchivo)) {
            return redirect()->back()->with('error', 'Archivo no encontrado en el servidor');
        }

        return $this->response->download($rutaArchivo, null)->setFileName($seguimiento['nombre_archivo']);
    }

    /**
     * Descargar evidencia inicial del hallazgo
     */
    public function descargarEvidenciaHallazgo(int $idCliente, int $idHallazgo)
    {
        $hallazgo = $this->hallazgosModel->find($idHallazgo);

        if (!$hallazgo || empty($hallazgo['evidencia_inicial'])) {
            return redirect()->back()->with('error', 'Archivo no encontrado');
        }

        // Verificar que el hallazgo pertenece al cliente
        if ($hallazgo['id_cliente'] != $idCliente) {
            return redirect()->back()->with('error', 'Acceso no autorizado');
        }

        $rutaArchivo = WRITEPATH . $hallazgo['evidencia_inicial'];

        if (!file_exists($rutaArchivo)) {
            return redirect()->back()->with('error', 'Archivo no encontrado en el servidor');
        }

        // Obtener nombre del archivo desde la ruta
        $nombreArchivo = basename($hallazgo['evidencia_inicial']);

        return $this->response->download($rutaArchivo, null)->setFileName($nombreArchivo);
    }
}
